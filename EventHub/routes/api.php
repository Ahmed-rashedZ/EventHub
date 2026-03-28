<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});


// عرض events
Route::get('/events', [EventController::class, 'index']);

// إنشاء event (Event Manager فقط)
Route::middleware(['auth:sanctum'])->post('/events', [EventController::class, 'store']);

// موافقة (Admin فقط)
Route::middleware(['auth:sanctum'])->put('/events/{id}/approve', [EventController::class, 'approve']);