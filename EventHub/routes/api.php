<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\SponsorshipController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\VerificationController;

// ─── Public routes ────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/partner', [AuthController::class, 'registerPartner']);
Route::post('/login',    [AuthController::class, 'login']);

// Public event list (approved only)
Route::get('/events',       [EventController::class, 'index']);
Route::get('/events/{id}',  [EventController::class, 'show']);
Route::get('/events/{id}/reviews', [EventController::class, 'reviews']);

// Public venue list
Route::get('/venues', [VenueController::class, 'index']);

// ─── Authenticated routes ──────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Profile & Role Management
    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::get('/profile/{id}', [AuthController::class, 'getPublicProfile']);
    Route::get('/profile/{id}/portfolio', [AuthController::class, 'getPortfolio']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::patch('/profile/availability', [AuthController::class, 'updateAvailability']);
    Route::post('/users', [AuthController::class, 'createUser']); // For Admins and Managers
    Route::get('/assistants', [AuthController::class, 'getAssistants']); // For Managers
    Route::delete('/assistants/{id}', [AuthController::class, 'deleteAssistant']); // For Managers
    Route::get('/sponsors/available', [AuthController::class, 'getAvailableSponsors']);

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // ── Events ──
    Route::post('/events',                    [EventController::class, 'store']);
    Route::put('/events/{id}/approve',        [EventController::class, 'approve']);
    Route::put('/events/{id}/reject',         [EventController::class, 'reject']);
    Route::get('/events/list/pending',        [EventController::class, 'pending']);
    Route::get('/events/list/my',             [EventController::class, 'myEvents']);
    Route::get('/events/list/all',            [EventController::class, 'all']);
    Route::patch('/events/{id}/toggle-sponsorship', [EventController::class, 'toggleSponsorship']);
    Route::post('/events/{id}/rate',          [EventController::class, 'rate']);

    // ── Venues (Admin) ──
    Route::post('/venues',        [VenueController::class, 'store']);
    Route::put('/venues/{id}',    [VenueController::class, 'update']);
    Route::delete('/venues/{id}', [VenueController::class, 'destroy']);

    // ── Tickets ──
    Route::post('/tickets',     [TicketController::class, 'store']);
    Route::get('/my-tickets',   [TicketController::class, 'myTickets']);

    // ── Check-in ──
    Route::post('/checkin',                         [CheckinController::class, 'checkin']);
    Route::get('/checkin/event/{id}',               [CheckinController::class, 'eventParticipants']);

    // ── Sponsorship ──
    Route::post('/sponsorship',       [SponsorshipController::class, 'store']);
    Route::get('/sponsorship',        [SponsorshipController::class, 'index']);
    Route::put('/sponsorship/{id}',   [SponsorshipController::class, 'update']);
    Route::patch('/sponsorship/{id}/tier', [SponsorshipController::class, 'updateTier']);

    // ── Notifications ──
    Route::get('/notifications',              [NotificationController::class, 'index']);
    Route::put('/notifications/read-all',     [NotificationController::class, 'markAllRead']);
    Route::put('/notifications/{id}/read',    [NotificationController::class, 'markRead']);

    // ── Analytics ──
    Route::get('/analytics/system',       [AnalyticsController::class, 'system']);
    Route::get('/analytics/manager',      [AnalyticsController::class, 'managerOverview']);
    Route::get('/analytics/event/{id}',   [AnalyticsController::class, 'event']);
    Route::get('/analytics/users',        [AnalyticsController::class, 'users']);
    Route::patch('/analytics/users/{id}/status', [AnalyticsController::class, 'toggleStatus']);
    Route::delete('/analytics/users/{id}',[AnalyticsController::class, 'deleteUser']);

    // ── Verifications (Admin) ──
    Route::get('/verifications/pending', [VerificationController::class, 'pendingRequests']);
    Route::put('/verifications/{id}/review', [VerificationController::class, 'reviewDocuments']);
    Route::put('/verifications/{id}/reject', [VerificationController::class, 'reject']);
    Route::get('/verifications/{id}/document/{type}', [VerificationController::class, 'downloadDocument']);
    
    // ── Resubmit (For Partners) ──
    Route::post('/verifications/reupload', [VerificationController::class, 'reuploadDocument']);
});