<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Guest Routes
Route::middleware('web.guest')->group(function () {
    Route::view('/login', 'login');
    Route::view('/register', 'register');
});

// Authenticated Routes
Route::middleware('web.auth')->group(function () {
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/information', [ProfileController::class, 'updateInformation'])->name('profile.update.info');
    Route::put('/profile/security', [ProfileController::class, 'updateSecurity'])->name('profile.update.security');
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
    Route::view('/user-profile', 'user-profile');

    // Admin Routes
    Route::middleware('web.auth:Admin')->prefix('admin')->group(function () {
        Route::view('/dashboard', 'admin.dashboard');
        Route::view('/events', 'admin.events');
        Route::view('/users', 'admin.users');
        Route::view('/venues', 'admin.venues');
    });

    // Manager Routes
    Route::middleware('web.auth:Event Manager')->prefix('manager')->group(function () {
        Route::view('/assistants', 'manager.assistants');
        Route::view('/attendance', 'manager.attendance');
        Route::view('/dashboard', 'manager.dashboard');
        Route::view('/events', 'manager.events');
        Route::view('/sponsorship', 'manager.sponsorship');
    });

    // Sponsor Routes
    Route::middleware('web.auth:Sponsor')->prefix('sponsor')->group(function () {
        Route::view('/dashboard', 'sponsor.dashboard');
        Route::view('/requests', 'sponsor.requests');
        Route::view('/events', 'sponsor.events');
    });

});

// DEMO ROUTE for Profile and Branding System
Route::get('/demo-branding', function () {
    // 1. Create Event Manager
    $manager = \App\Models\User::firstOrCreate(
        ['email' => 'manager@demo.com'],
        ['name' => 'John Event Manager', 'password' => bcrypt('password'), 'role' => 'Event Manager']
    );

    // 2. Create an Event
    $event = \App\Models\Event::firstOrCreate(
        ['title' => 'Global Tech Summit 2026'],
        [
            'description' => 'The ultimate technology conference for innovators.',
            'location' => 'Dubai World Trade Centre',
            'venue_id' => 1, // Assumes venue id 1 exists or is nullable, wait venue_id is constrained. Let\'s create a venue.
            'created_by' => $manager->id,
            'start_time' => now()->addDays(30),
            'end_time' => now()->addDays(32),
            'capacity' => 5000,
            'status' => 'approved',
        ]
    );

    // Patch venue_id if needed
    if (!$event->venue_id) {
        $venue = \App\Models\Venue::firstOrCreate(['name' => 'Dubai World Trade Centre'], ['location' => 'Dubai', 'capacity' => 5000]);
        $event->update(['venue_id' => $venue->id]);
    }

    // 3. Create Sponsors and Profiles
    $sponsorData = [
        ['name' => 'Acme Corp', 'tier' => 'diamond', 'logo' => 'https://ui-avatars.com/api/?name=Acme+Corp&background=0D8ABC&color=fff&size=200', 'bio' => 'Leading the way in tech solutions.'],
        ['name' => 'Global Soft', 'tier' => 'gold', 'logo' => 'https://ui-avatars.com/api/?name=Global+Soft&background=Eab308&color=fff&size=200', 'bio' => 'Empowering digital transformation.'],
        ['name' => 'NetSys', 'tier' => 'silver', 'logo' => 'https://ui-avatars.com/api/?name=NetSys&background=9ca3af&color=fff&size=200', 'bio' => 'Infrastructure for the modern web.'],
        ['name' => 'Local Startup', 'tier' => 'bronze', 'logo' => 'https://ui-avatars.com/api/?name=Local+Startup&background=f97316&color=fff&size=200', 'bio' => 'Innovating locally, thinking globally.']
    ];

    foreach ($sponsorData as $data) {
        $user = \App\Models\User::firstOrCreate(
            ['email' => strtolower(str_replace(' ', '', $data['name'])) . '@sponsor.com'],
            ['name' => $data['name'], 'password' => bcrypt('password'), 'role' => 'Sponsor']
        );

        \App\Models\Profile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'profile_type' => 'company',
                'company_name' => $data['name'],
                'logo' => $data['logo'],
                'bio' => $data['bio'],
                'is_approved' => true
            ]
        );

        // Attach to event if not attached
        if (!$event->sponsors()->where('sponsor_id', $user->id)->exists()) {
            $event->sponsors()->attach($user->id, [
                'tier' => $data['tier'],
                'contribution_amount' => rand(1000, 10000)
            ]);
        }
    }

    $sponsors = $event->sponsorsWithProfiles();

    return view('demo-branding', compact('event', 'sponsors'));
});
