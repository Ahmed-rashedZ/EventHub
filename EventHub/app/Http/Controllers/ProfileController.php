<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display the specified user's public profile.
     */
    public function show(User $user)
    {
        return view('profile.show', compact('user'));
    }

    /**
     * Show the form for editing the authenticated user's profile.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the authenticated user's profile information.
     */
    public function updateInformation(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'phones' => 'nullable|array',
            'phones.*' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'social_links' => 'nullable|array',
            'social_links.*' => 'nullable|url',
        ]);

        $data = $request->only(['name', 'contact_email', 'bio']);
        $phonesList = array_map('trim', $request->input('phones', []));
        $data['phone'] = implode(', ', array_filter($phonesList));
        $data['social_links'] = $request->input('social_links', []);

        if ($request->hasFile('image')) {
            $oldImage = $user->image ?? $user->avatar;
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            $path = $request->file('image')->store('avatars', 'public');
            $data['image'] = $path;
            $data['avatar'] = $path; // Fallback mapping
        } elseif ($request->hasFile('avatar')) {
             if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $user->update($data);

        if ($user->role === 'Sponsor') {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'profile_type' => 'company',
                    'bio' => $request->input('bio'),
                ]
            );
        } else {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'profile_type' => 'individual',
                    'bio' => $request->input('bio'),
                ]
            );
        }

        return redirect()->back()->with('success', 'Profile information updated successfully!');
    }

    /**
     * Update the authenticated user's security settings.
     */
    public function updateSecurity(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $rules = [
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ];

        $request->validate($rules);

        $data = ['email' => $request->input('email')];

        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Security settings updated successfully!');
    }
}
