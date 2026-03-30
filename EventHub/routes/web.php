<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Guest Routes
Route::middleware('web.guest')->group(function () {
    Route::view('/login', 'login');
    Route::view('/register', 'register');
});

// Authenticated Routes
Route::middleware('web.auth')->group(function () {
    
    // Any authenticated user can access their profile
    Route::view('/profile', 'profile');

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
    });

});
