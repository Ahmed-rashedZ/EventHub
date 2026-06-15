<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Profile;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Notifications\SystemNotification;
use App\Mail\AssistantAccountCreated;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Injected OTP service — handles code generation, storage, email, verification.
     */
    private OtpService $otp;

    public function __construct(OtpService $otp)
    {
        $this->otp = $otp;
    }


public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        'role' => 'nullable|string|in:Attendee,Assistant',
    ], [
        'email.unique' => 'Email Address is already taken',
    ]);

    $role = $request->role ?? 'Attendee';

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $role,
    ]);

    // Create profile for Assistants with is_available = false by default
    if ($role === 'Assistant') {
        Profile::create([
            'user_id' => $user->id,
            'profile_type' => 'individual',
            'is_available' => false,
        ]);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user' => $user->load(['profile.contacts', 'documents']),
        'token' => $token
    ]);
}

public function registerPartner(Request $request)
{
    // Base validation rules (shared by both roles)
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        'role' => 'required|string|in:Event Manager,Sponsor,Company',
        'company_type' => 'nullable|required_if:role,Company|string|max:100',
        'doc_commercial_register' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'doc_tax_number' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ];

    // Manager requires 2 additional documents
    if ($request->role === 'Event Manager') {
        $rules['doc_articles_of_association'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
        $rules['doc_practice_license'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
    }

    // Custom Arabic error messages
    $messages = [
        'doc_commercial_register.required' => 'Commercial Register is required',
        'doc_commercial_register.file' => 'The file must be of a valid type',
        'doc_commercial_register.mimes' => 'The file must be of type: pdf, jpg, jpeg, png',
        'doc_commercial_register.max' => 'Files must not exceed 5 MB',

        'doc_tax_number.required' => 'Tax Number Certificate is required',
        'doc_tax_number.file' => 'The file must be of a valid type',
        'doc_tax_number.mimes' => 'The file must be of type: pdf, jpg, jpeg, png',
        'doc_tax_number.max' => 'Files must not exceed 5 MB',

        'doc_articles_of_association.required' => 'Articles of Association is required',
        'doc_articles_of_association.file' => 'The file must be of a valid type',
        'doc_articles_of_association.mimes' => 'The file must be of type: pdf, jpg, jpeg, png',
        'doc_articles_of_association.max' => 'Files must not exceed 5 MB',

        'doc_practice_license.required' => 'Practice License is required',
        'doc_practice_license.file' => 'The file must be of a valid type',
        'doc_practice_license.mimes' => 'The file must be of type: pdf, jpg, jpeg, png',
        'doc_practice_license.max' => 'Files must not exceed 5 MB',

        'email.unique' => 'Email Address is already taken',
        'account.suspended' => 'Your account has been suspended. Please contact support',
    ];

    $request->validate($rules, $messages);

    // Build user data (core auth only — documents go to user_documents table)
    $userData = [
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role,
        'verification_status' => 'pending',
    ];

    $user = User::create($userData);

    // Store documents in normalized user_documents table
    $docEntries = [
        'commercial_register' => $request->file('doc_commercial_register')->store('verifications'),
        'tax_number'          => $request->file('doc_tax_number')->store('verifications'),
    ];

    if ($request->role === 'Event Manager') {
        $docEntries['articles_of_association'] = $request->file('doc_articles_of_association')->store('verifications');
        $docEntries['practice_license']        = $request->file('doc_practice_license')->store('verifications');
    }

    foreach ($docEntries as $docType => $filePath) {
        \App\Models\UserDocument::create([
            'user_id'       => $user->id,
            'document_type' => $docType,
            'file_path'     => $filePath,
            'status'        => 'pending',
        ]);
    }

    if (in_array($request->role, ['Sponsor', 'Company'])) {
        Profile::create([
            'user_id' => $user->id,
            'profile_type' => 'company',
            'company_type' => $request->company_type,
            'company_type_slug' => $request->input('company_type_slug') ?? null,
            'is_available' => true,
        ]);
    }

    // ── Notify all Admins about new partner registration ──
    $roleLabel = match($request->role) {
        'Event Manager' => 'Event Manager',
        'Sponsor' => 'Sponsor',
        'Company' => 'Company',
        default => $request->role,
    };
    $admins = User::where('role', 'Admin')->get();
    foreach ($admins as $admin) {
        $admin->notify(new SystemNotification(
            'New Partner Registration 🎉',
            "A new {$roleLabel} \"{{$user->name}}\" has registered and is waiting for verification.",
            'verification',
            '🛡️',
            '/admin/verifications'
        ));
    }

    return response()->json([
        'user' => $user->load(['profile', 'documents']),
        'message' => 'Registration successful. Your account is pending verification and review by the administration.'
    ]);
}
    public function login(Request $request)
{
    $credentials = $request->only('email', 'password');
    $platform = $request->input('platform', 'mobile'); // Default to mobile if not specified

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    /** @var \App\Models\User $user */
    $user = Auth::user();

    if (!$user->is_active) {
        Auth::logout();
        return response()->json(['message' => 'Your account has been suspended. Please contact support'], 403);
    }

    // ── Platform-Based Role Restrictions ──
    $mobileRoles = ['Attendee', 'Assistant'];
    $webRoles = ['Admin', 'Event Manager', 'Sponsor', 'Company'];

    if ($platform === 'web' && in_array($user->role, $mobileRoles)) {
        Auth::logout();
        return response()->json([
            'message' => 'This account is for the mobile app only. You cannot log in via the web.',
            'error_code' => 'MOBILE_ONLY_ACCOUNT'
        ], 403);
    }

    if ($platform === 'mobile' && in_array($user->role, $webRoles)) {
        Auth::logout();
        return response()->json([
            'message' => 'Web accounts cannot log in via the mobile app.',
            'error_code' => 'WEB_ONLY_ACCOUNT'
        ], 403);
    }

    // ── Partner Verification Check ──
    // Unverified partners (Event Manager, Sponsor, Company) are allowed to log in 
    // so they can access the /pending-verification page and re-upload their documents.
    // Their access to the dashboards is secured and blocked by EnsurePartnerVerified middleware.
    /*
    $partnerRoles = ['Event Manager', 'Sponsor', 'Company'];
    if (in_array($user->role, $partnerRoles) && $user->verification_status !== 'verified') {
        $status = $user->verification_status;
        Auth::logout();
        
        $msg = 'حسابك لا يزال قيد المراجعة والتحقق. سيتم تفعيل الدخول بمجرد اعتماد وثائقك من قبل الإدارة.';
        if ($status === 'rejected') {
            $msg = 'تم رفض وثائق التحقق الخاصة بك. يرجى مراجعة قسم التحقق لمعرفة الأسباب أو التواصل مع الدعم.';
        }
        
        return response()->json([
            'message' => $msg,
            'verification_status' => $status
        ], 403);
    }
    */

    $token = $user->createToken('auth_token')->plainTextToken;

    if ($user->role === 'Assistant') {
        $user->loadCount('attendanceLogs');
    }

    return response()->json([
        'user' => $user->load(['profile.contacts', 'documents']),
        'token' => $token,
    ]);
}
public function updateProfile(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $request->user()->id,
        'password' => 'nullable|string|min:8',
        'bio' => 'nullable|string',
        'logo' => 'nullable|image|max:2048', // 2MB max image
        'company_description' => 'nullable|string',
        'company_type' => 'nullable|string|max:100',
        'is_available' => 'nullable|boolean',
        'contacts' => 'nullable|string', // Will interpret as JSON array [{"type":"phone", "value":"..."}]
        'interests' => 'nullable|array',
        'interests.*' => 'string',
    ]);

    $user = $request->user();
    $user->name = $request->name;
    $user->email = $request->email;
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }
    if ($request->has('interests')) {
        $user->interests = $request->interests;
    }
    $user->save();

    // Upsert the unified profile based on the role
    $profileType = $user->role === 'Sponsor' ? 'company' : 'individual';
    
    $profilePayload = [
        'profile_type' => $profileType,
        'bio' => $request->bio,
        'company_description' => $request->company_description,
        'company_type' => $request->company_type,
        'company_type_slug' => $request->input('company_type_slug') ?? null,
    ];
    
    // Sponsors and Companies can toggle their availability
    if ($request->has('is_available') && in_array($user->role, ['Sponsor', 'Company'])) {
        $profilePayload['is_available'] = $request->boolean('is_available');
    }

    // Handle File Upload if exists
    if ($request->hasFile('logo')) {
        $path = $request->file('logo')->store('profiles', 'public');
        $profilePayload['logo'] = $path;
    }

    $profile = $user->profile()->updateOrCreate(
        ['user_id' => $user->id],
        $profilePayload
    );

    // Sync Contacts if they were passed
    if ($request->filled('contacts')) {
        $contactsData = json_decode($request->contacts, true) ?? [];
        $profile->contacts()->delete(); // drop old ones
        
        foreach ($contactsData as $contact) {
            if (!empty($contact['type']) && !empty($contact['value'])) {
                $profile->contacts()->create([
                    'type' => $contact['type'],
                    'value' => $contact['value']
                ]);
            }
        }
    }

    // Reload the user with their profile so the frontend gets the latest data
    $query = User::with(['profile.contacts', 'documents']);
    if ($user->role === 'Assistant') {
        $query->withCount('attendanceLogs');
    }
    $user = $query->find($user->id);

    return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
}

public function getProfile(Request $request)
{
    $user = $request->user();
    
    // Ensure Sponsor/Company has a profile so is_available exists
    if (in_array($user->role, ['Sponsor', 'Company']) && !$user->profile) {
        Profile::create([
            'user_id' => $user->id,
            'profile_type' => 'company',
            'is_available' => true
        ]);
        $user->load(['profile', 'documents']);
    } else {
        $user->load(['profile.contacts', 'documents']);
    }

    if ($user->role === 'Assistant') {
        $user->loadCount('attendanceLogs');
    }

    return response()->json([
        'user' => $user
    ]);
}

public function updateAvailability(Request $request)
{
    $request->validate([
        'is_available' => 'required|boolean'
    ]);

    $user = $request->user();
    if (!in_array($user->role, ['Sponsor', 'Company'])) {
        return response()->json(['message' => 'Only sponsors and companies can toggle availability'], 403);
    }

    $isAvailable = $request->boolean('is_available');

    $profile = Profile::updateOrCreate(
        ['user_id' => $user->id],
        [
            'is_available' => $isAvailable,
            'profile_type' => 'company'
        ]
    );

    $profile->refresh();

    return response()->json([
        'message' => 'Availability updated',
        'is_available' => (bool) $profile->is_available,
        'user' => $user->fresh(['profile.contacts', 'documents'])
    ]);
}

public function getPublicProfile($id)
{
    $user = User::with(['profile.contacts', 'documents'])->findOrFail($id);
    
    if ($user->role === 'Event Manager') {
        $avg = \App\Models\Rating::whereHas('event', function ($q) use ($user) {
            $q->where('created_by', $user->id);
        })->avg('rating');
        $user->manager_average_rating = $avg ? round($avg, 1) : 0;
    }
    
    return response()->json([
        'user' => $user
    ]);
}

public function getPortfolio(Request $request, $id)
{
    $user = User::findOrFail($id);
    
    if ($user->role === 'Event Manager') {
        $query = \App\Models\Event::where('created_by', $user->id)
                        ->with('venue')
                        ->withAvg('ratings', 'rating')
                        ->orderBy('start_time', 'desc');

        $authUser = $request->user();
        if (!$authUser || ($authUser->role !== 'Admin' && $authUser->id !== $user->id)) {
            $query->where('status', 'approved');
        }

        $events = $query->get();

        return response()->json([
            'events' => $events
        ]);
    } elseif ($user->role === 'Sponsor') {
        $query = $user->sponsoredEvents()->with('venue')->withAvg('ratings', 'rating')->orderBy('start_time', 'desc');
        
        $authUser = $request->user();
        if (!$authUser || ($authUser->role !== 'Admin' && $authUser->id !== $user->id)) {
            $query->where('events.status', 'approved');
        }

        $events = $query->get();

        return response()->json([
            'events' => $events
        ]);
    }

    return response()->json(['message' => 'Not an event manager or sponsor'], 400);
}

public function getAvailableSponsors(Request $request)
{
    // Fetch users with the Sponsor role who have a profile with is_available = true
    $sponsors = User::where('role', 'Sponsor')
        ->whereHas('profile', function($q) {
            $q->available();
        })
        ->with(['profile.contacts'])
        ->get();

    return response()->json($sponsors);
}

public function getAvailableCompanies(Request $request)
{
    $companies = User::where('role', 'Company')
        ->where('verification_status', 'verified')
        ->whereHas('profile', function ($q) use ($request) {
            $q->available();

            if ($request->filled('event_id')) {
                $event = Event::find($request->event_id);
                if ($event?->company_category_slug) {
                    $q->where('company_type_slug', $event->company_category_slug);
                }
            }
        })
        ->with(['profile.contacts'])
        ->get();

    return response()->json($companies);
}

public function createUser(Request $request)
{
    $authUser = $request->user();

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        'role' => 'required|string',
        'event_id' => 'nullable|exists:events,id',
    ]);

    // Role constraints
    if ($authUser->role === 'Admin') {
        $allowedRoles = ['Attendee', 'Admin'];
        if (!in_array($request->role, $allowedRoles)) {
            return response()->json(['message' => 'Invalid role creation for Admin. Event Managers and Sponsors must self-register for verification.'], 403);
        }
    } elseif ($authUser->role === 'Event Manager') {
        // Manager can ONLY create Assistants for their own events
        if ($request->role !== 'Assistant') {
            return response()->json(['message' => 'Event Managers can only create Assistants'], 403);
        }
        if (!$request->event_id) {
            return response()->json(['message' => 'Event ID is required for Assistant creation'], 422);
        }
        
        $event = Event::where('id', $request->event_id)
                      ->where('created_by', $authUser->id)
                      ->first();
        if (!$event) {
            return response()->json(['message' => 'Unauthorized or invalid event for this Assistant'], 403);
        }
    } else {
        return response()->json(['message' => 'Unauthorized to create users'], 403);
    }

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role,
        'event_id' => $request->event_id,
    ]);

    if ($request->role === 'Sponsor') {
        Profile::create([
            'user_id' => $user->id,
            'profile_type' => 'company',
            'is_available' => true,
        ]);
    }

    // ── Send Email to Assistant ──
    if ($request->role === 'Assistant' && $event) {
        try {
            Mail::to($user->email)->send(new AssistantAccountCreated(
                $user->name,
                $user->email,
                $request->password, // Send raw password before hash
                $event->title
            ));
        } catch (\Exception $e) {
            // Log error or ignore if mail fails to avoid blocking user creation
        }
    }

    return response()->json(['message' => 'User created successfully', 'user' => $user->load(['profile', 'documents'])], 201);
}

public function getAssistants(Request $request)
{
    $authUser = $request->user();

    if ($authUser->role !== 'Event Manager') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $assistants = User::where('role', 'Assistant')
        ->where(function($query) use ($authUser) {
            $query->whereHas('event', function ($q) use ($authUser) {
                $q->where('created_by', $authUser->id);
            })->orWhereHas('attendanceLogs.ticket.event', function ($q) use ($authUser) {
                $q->where('created_by', $authUser->id);
            });
        })
        ->with(['event:id,title', 'attendanceLogs.ticket:id,event_id'])
        ->get(['id', 'name', 'email', 'event_id', 'is_active', 'phone']);

    // Map the results to include past_event_ids and scan counts
    $assistants = $assistants->map(function ($assistant) {
        $eventScans = [];
        foreach ($assistant->attendanceLogs as $log) {
            if ($log->ticket && $log->ticket->event_id) {
                $eId = $log->ticket->event_id;
                if (!isset($eventScans[$eId])) {
                    $eventScans[$eId] = 0;
                }
                $eventScans[$eId]++;
            }
        }

        // Remove the heavy relation before returning
        unset($assistant->attendanceLogs);
        $assistant->event_scans = $eventScans;
        $assistant->past_event_ids = array_keys($eventScans);
        
        return $assistant;
    });

    return response()->json($assistants);
}

public function patchAssistantStatus(Request $request, $id)
{
    $authUser = $request->user();
    if ($authUser->role !== 'Event Manager') return response()->json(['message' => 'Unauthorized'], 403);

    $assistant = User::where('id', $id)
                     ->where('role', 'Assistant')
                     ->whereHas('event', function ($query) use ($authUser) {
                         $query->where('created_by', $authUser->id);
                     })
                     ->first();

    if (!$assistant) return response()->json(['message' => 'Assistant not found'], 404);

    $assistant->is_active = !$assistant->is_active;
    $assistant->save();

    if (!$assistant->is_active) {
        $assistant->tokens()->delete();
    }

    return response()->json([
        'message' => 'Assistant status updated',
        'is_active' => $assistant->is_active
    ]);
}

public function updateAssistant(Request $request, $id)
{
    $authUser = $request->user();
    if ($authUser->role !== 'Event Manager') return response()->json(['message' => 'Unauthorized'], 403);

    $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|unique:users,email,' . $id,
        'event_id' => 'sometimes|required|exists:events,id',
        'password' => 'sometimes|required|string|min:8',
    ]);

    $assistant = User::where('id', $id)
                     ->where('role', 'Assistant')
                     ->whereHas('event', function ($query) use ($authUser) {
                         $query->where('created_by', $authUser->id);
                     })
                     ->first();

    if (!$assistant) return response()->json(['message' => 'Assistant not found'], 404);

    if ($request->has('name')) $assistant->name = $request->name;
    if ($request->has('email')) $assistant->email = $request->email;
    if ($request->has('event_id')) {
        // Verify new event is also owned by this manager
        $event = Event::where('id', $request->event_id)->where('created_by', $authUser->id)->first();
        if (!$event) return response()->json(['message' => 'Invalid event assignment'], 403);
        $assistant->event_id = $request->event_id;
    } else {
        $event = $assistant->event; // fallback to current event for email
    }
    
    if ($request->has('password')) $assistant->password = Hash::make($request->password);

    $assistant->save();

    // Send email notification
    try {
        \Illuminate\Support\Facades\Mail::to($assistant->email)->send(new \App\Mail\AssistantAccountUpdated(
            $assistant->name,
            $assistant->email,
            $request->password, // send raw password if changed, otherwise null
            $event ? $event->title : 'Unknown Event'
        ));
    } catch (\Exception $e) {
        // Ignore mail errors so we don't break the response
    }

    return response()->json(['message' => 'Assistant updated successfully', 'assistant' => $assistant]);
}

public function deleteAssistant(Request $request, $id)
{
    $authUser = $request->user();

    if ($authUser->role !== 'Event Manager') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $assistant = User::where('id', $id)
                     ->where('role', 'Assistant')
                     ->whereHas('event', function ($query) use ($authUser) {
                         $query->where('created_by', $authUser->id);
                     })
                     ->first();

    if (!$assistant) {
        return response()->json(['message' => 'Assistant not found or unauthorized'], 404);
    }

    $assistant->delete();

    return response()->json(['message' => 'Assistant deleted successfully']);
}

public function logout(Request $request)
{
    $request->user()->tokens()->delete();

    return response()->json(['message' => 'Logged out']);
}

/**
 * Step 1: Send a 6-digit OTP code to the user's email.
 */
public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $result = $this->otp->send($request->email);
    $status = $result['status'] ?? 200;

    return response()->json(['message' => $result['message']], $status);
}

/**
 * Step 2: Verify the OTP code only.
 * Returns a one-time reset_token for use in step 3.
 */
public function verifyCode(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'code'  => 'required|string|size:6',
    ]);

    $result = $this->otp->verify($request->email, $request->code);

    if (!$result['success']) {
        return response()->json(
            ['message' => $result['message']],
            $result['status'] ?? 422
        );
    }

    return response()->json([
        'message'     => $result['message'],
        'reset_token' => $result['reset_token'],
    ]);
}

/**
 * Step 3: Reset password using the reset_token from step 2.
 */
public function resetPassword(Request $request)
{
    $request->validate([
        'email'       => 'required|email',
        'reset_token' => 'required|string',
        'password'    => 'required|string|min:8|confirmed',
    ]);

    // ── Validate reset token ──
    $result = $this->otp->validateResetToken($request->email, $request->reset_token);

    if (!$result['success']) {
        return response()->json(
            ['message' => $result['message']],
            $result['status'] ?? 422
        );
    }

    // ── Token valid — reset the password ──
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    $user->password = Hash::make($request->password);
    $user->save();

    // Revoke all tokens (force logout from all devices)
    $user->tokens()->delete();

    return response()->json(['message' => 'Password reset successfully. You can now log in.']);
}
}

