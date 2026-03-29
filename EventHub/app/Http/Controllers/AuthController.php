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
        'user' => $user,
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
        'user' => $user,
        'token' => $token
    ]);
}
public function updateProfile(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $request->user()->id,
        'password' => 'nullable|string|min:8',
    ]);

    $user = clone $request->user();
    $user->name = $request->name;
    $user->email = $request->email;
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }
    $user->save();

    return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
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

    return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
}

public function logout(Request $request)
{
    $request->user()->tokens()->delete();

    return response()->json(['message' => 'Logged out']);
}
}
