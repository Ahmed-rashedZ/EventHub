<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
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
    public function login(Request $request)
{
    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $user = Auth::user();

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user' => $user->load(['profile.contacts']),
        'token' => $token
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
        'company_name' => 'nullable|string',
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
        'company_name' => $request->company_name,
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
        \App\Models\Profile::create([
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

    $profile = \App\Models\Profile::updateOrCreate(
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
    
    return response()->json([
        'user' => $user
    ]);
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
        $allowedRoles = ['Event Manager', 'Sponsor', 'User'];
        if (!in_array($request->role, $allowedRoles)) {
            return response()->json(['message' => 'Invalid role creation for Admin'], 403);
        }
    } elseif ($authUser->role === 'Event Manager') {
        // Manager can ONLY create Assistants for their own events
        if ($request->role !== 'Assistant') {
            return response()->json(['message' => 'Event Managers can only create Assistants'], 403);
        }
        if (!$request->event_id) {
            return response()->json(['message' => 'Event ID is required for Assistant creation'], 422);
        }
        
        $event = \App\Models\Event::where('id', $request->event_id)
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
        \App\Models\Profile::create([
            'user_id' => $user->id,
            'profile_type' => 'company',
            'is_available' => true,
        ]);
    }

    return response()->json(['message' => 'User created successfully', 'user' => $user->load('profile')], 201);
}

public function logout(Request $request)
{
    $request->user()->tokens()->delete();

    return response()->json(['message' => 'Logged out']);
}
}
