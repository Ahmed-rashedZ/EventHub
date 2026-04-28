<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Profile;
use App\Models\User;
use App\Mail\PasswordResetCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Notifications\SystemNotification;

class AuthController extends Controller
{


public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        'role' => 'nullable|string|in:User', // Only allow 'User' role via public registration
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'User' // Force 'User' role for public registration
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user' => $user->load(['profile.contacts']),
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
        'role' => 'required|string|in:Event Manager,Sponsor',
        'doc_commercial_register' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'doc_tax_number' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ];

    // Manager requires 2 additional documents
    if ($request->role === 'Event Manager') {
        $rules['doc_articles_of_association'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
        $rules['doc_practice_license'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
    }

    $request->validate($rules);

    // Build user data
    $userData = [
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role,
        'verification_status' => 'pending',
        'doc_commercial_register' => $request->file('doc_commercial_register')->store('verifications'),
        'doc_tax_number' => $request->file('doc_tax_number')->store('verifications'),
        'doc_commercial_register_status' => 'pending',
        'doc_tax_number_status' => 'pending',
    ];

    // Add Manager-only documents
    if ($request->role === 'Event Manager') {
        $userData['doc_articles_of_association'] = $request->file('doc_articles_of_association')->store('verifications');
        $userData['doc_practice_license'] = $request->file('doc_practice_license')->store('verifications');
        $userData['doc_articles_of_association_status'] = 'pending';
        $userData['doc_practice_license_status'] = 'pending';
    }

    $user = User::create($userData);

    if ($request->role === 'Sponsor') {
        Profile::create([
            'user_id' => $user->id,
            'profile_type' => 'company',
            'is_available' => true,
        ]);
    }

    // ── Notify all Admins about new partner registration ──
    $roleLabel = $request->role === 'Event Manager' ? 'Event Manager' : 'Sponsor';
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

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user' => $user->load(['profile']),
        'token' => $token,
        'message' => 'Registration successful. Your account is pending verification.'
    ]);
}
    public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    /** @var \App\Models\User $user */
    $user = Auth::user();

    if (!$user->is_active) {
        Auth::logout();
        return response()->json(['message' => 'Your account has been suspended. Please contact support.'], 403);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user' => $user->load(['profile.contacts']),
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
        'is_available' => 'nullable|boolean',
        'contacts' => 'nullable|string', // Will interpret as JSON array [{"type":"phone", "value":"..."}]
    ]);

    $user = $request->user();
    $user->name = $request->name;
    $user->email = $request->email;
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }
    $user->save();

    // Upsert the unified profile based on the role
    $profileType = $user->role === 'Sponsor' ? 'company' : 'individual';
    
    $profilePayload = [
        'profile_type' => $profileType,
        'bio' => $request->bio,
        'company_description' => $request->company_description,
    ];
    
    // Only Sponsors can toggle their availability (persist exact boolean from request)
    if ($request->has('is_available') && $user->role === 'Sponsor') {
        $profilePayload['is_available'] = $request->boolean('is_available');
    }

    // Handle File Upload if exists
    if ($request->hasFile('logo')) {
        $path = $request->file('logo')->store('profiles', 'public');
        $profilePayload['logo'] = "/storage/" . $path;
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
    $user = User::with(['profile.contacts'])->find($user->id);

    return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
}

public function getProfile(Request $request)
{
    $user = $request->user();
    
    // Ensure Sponsor has a profile so is_available exists
    if ($user->role === 'Sponsor' && !$user->profile) {
        Profile::create([
            'user_id' => $user->id,
            'profile_type' => 'company',
            'is_available' => true // Default to true as per migration
        ]);
        $user->load('profile');
    } else {
        $user->load(['profile.contacts']);
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
    if ($user->role !== 'Sponsor') {
        return response()->json(['message' => 'Only sponsors can toggle availability'], 403);
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
        'user' => $user->fresh(['profile.contacts'])
    ]);
}

public function getPublicProfile($id)
{
    $user = User::with(['profile.contacts'])->findOrFail($id);
    
    if ($user->role === 'Event Manager') {
        $avg = \App\Models\Event::where('created_by', $user->id)->get()->avg('average_rating');
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
        $query = $user->sponsoredEvents()->with('venue')->orderBy('start_time', 'desc');
        
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
        $allowedRoles = ['User', 'Admin'];
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

    return response()->json(['message' => 'User created successfully', 'user' => $user->load('profile')], 201);
}

public function getAssistants(Request $request)
{
    $authUser = $request->user();

    if ($authUser->role !== 'Event Manager') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $assistants = User::where('role', 'Assistant')
                      ->whereHas('event', function ($query) use ($authUser) {
                          $query->where('created_by', $authUser->id);
                      })
                      ->with('event:id,title')
                      ->get(['id', 'name', 'email', 'event_id']);

    return response()->json($assistants);
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
 * Send a 6-digit OTP code to the user's email for password reset.
 */
public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $user = User::where('email', $request->email)->first();

    // Always return success (security: don't reveal if email exists)
    if (!$user) {
        return response()->json(['message' => 'If this email is registered, a reset code has been sent.']);
    }

    // Delete any existing codes for this email
    DB::table('password_reset_codes')->where('email', $request->email)->delete();

    // Generate 6-digit code
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Store the code
    DB::table('password_reset_codes')->insert([
        'email'      => $request->email,
        'code'       => $code,
        'attempts'   => 0,
        'created_at' => Carbon::now(),
    ]);

    // Send the email (silently fail in dev if mail isn't configured)
    try {
        Mail::to($request->email)->send(new PasswordResetCode($code, $user->name));
    } catch (\Exception $e) {
        // Mail not configured — that's fine in dev mode
    }

    $response = ['message' => 'If this email is registered, a reset code has been sent.'];

    // In debug/dev mode, include the code in the response so the user can see it
    if (config('app.debug')) {
        $response['debug_code'] = $code;
    }

    return response()->json($response);
}

/**
 * Verify the OTP code and reset the password.
 */
public function resetPassword(Request $request)
{
    $request->validate([
        'email'                 => 'required|email',
        'code'                  => 'required|string|size:6',
        'password'              => 'required|string|min:8|confirmed',
    ]);

    $record = DB::table('password_reset_codes')
        ->where('email', $request->email)
        ->first();

    if (!$record) {
        return response()->json(['message' => 'No reset code found. Please request a new one.'], 422);
    }

    // Check expiry (5 minutes)
    if (Carbon::parse($record->created_at)->addMinutes(5)->isPast()) {
        DB::table('password_reset_codes')->where('email', $request->email)->delete();
        return response()->json(['message' => 'Reset code has expired. Please request a new one.'], 422);
    }

    // Check max attempts (3)
    if ($record->attempts >= 3) {
        DB::table('password_reset_codes')->where('email', $request->email)->delete();
        return response()->json(['message' => 'Too many failed attempts. Please request a new code.'], 422);
    }

    // Verify code
    if ($record->code !== $request->code) {
        DB::table('password_reset_codes')
            ->where('email', $request->email)
            ->increment('attempts');

        $remaining = 3 - ($record->attempts + 1);
        return response()->json([
            'message' => "Invalid code. {$remaining} attempt(s) remaining."
        ], 422);
    }

    // Code is valid — reset password
    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    $user->password = Hash::make($request->password);
    $user->save();

    // Revoke all tokens (logout from all devices)
    $user->tokens()->delete();

    // Clean up the code
    DB::table('password_reset_codes')->where('email', $request->email)->delete();

    return response()->json(['message' => 'Password reset successfully. You can now log in.']);
}
}
