<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\SponsorshipController;
use App\Http\Controllers\AgreementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\AssistantAnalyticsController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\ExhibitionController;
use App\Http\Controllers\ExhibitionInventoryController;
use App\Http\Controllers\CompanyAnalyticsController;

// ─── Public routes ────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/partner', [AuthController::class, 'registerPartner']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('/password/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/password/reset',  [AuthController::class, 'resetPassword']);

// Public event list (approved only)
Route::get('/events',       [EventController::class, 'index']);
Route::get('/events/{id}',  [EventController::class, 'show']);
Route::get('/events/{id}/reviews', [EventController::class, 'reviews']);
Route::get('/categories', [EventController::class, 'categories']);

// Public venue list
Route::get('/venues', [VenueController::class, 'index']);
Route::get('/venues/{id}/bookings', [VenueController::class, 'bookings']);

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
    Route::get('/assistants/{id}/history', [AssistantAnalyticsController::class, 'getHistory']);
    Route::get('/assistants/{id}/stats', [AssistantAnalyticsController::class, 'getStats']);
    Route::patch('/assistants/{id}/status', [AuthController::class, 'patchAssistantStatus']); // For Managers
    Route::put('/assistants/{id}', [AuthController::class, 'updateAssistant']); // For Managers
    Route::delete('/assistants/{id}', [AuthController::class, 'deleteAssistant']); // For Managers
    Route::get('/sponsors/available', [AuthController::class, 'getAvailableSponsors']);
    Route::get('/companies/available', [AuthController::class, 'getAvailableCompanies']);

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
    Route::patch('/events/{id}/toggle-applications', [EventController::class, 'toggleApplications']);
    Route::patch('/events/{id}/toggle-exhibitor-registration', [EventController::class, 'toggleExhibitorRegistration']);
    Route::post('/events/{id}/rate',          [EventController::class, 'rate']);
    Route::delete('/events/{id}/rate',        [EventController::class, 'deleteRating']);
    Route::put('/events/{id}/review',         [EventController::class, 'sendReview']);
    Route::post('/events/{id}/update-pending', [EventController::class, 'updatePending']);
    Route::delete('/events/{id}',             [EventController::class, 'destroy']);
    Route::put('/events/{id}/agenda',         [EventController::class, 'updateAgenda']);
    Route::get('/events/{id}/download-document/{type}', [EventController::class, 'downloadDocument']);
    Route::post('/events/{id}/request-cancellation', [EventController::class, 'requestCancellation']);
    Route::put('/events/{id}/approve-cancellation',  [EventController::class, 'approveCancellation']);
    Route::put('/events/{id}/reject-cancellation',   [EventController::class, 'rejectCancellation']);
    Route::patch('/events/{id}/toggle-tickets', [EventController::class, 'toggleTickets']);
    Route::put('/events/{id}/published-schedule', [EventController::class, 'updatePublishedSchedule']);
    Route::patch('/events/{id}/capacity', [EventController::class, 'updateCapacity']);
    Route::post('/events/predict-attendance', [EventController::class, 'predictAttendance']);
    Route::post('/events/generate-description', [EventController::class, 'generateDescription']);

    // ── Venues (Admin) ──
    Route::post('/venues',        [VenueController::class, 'store']);
    Route::put('/venues/{id}',    [VenueController::class, 'update']);
    Route::delete('/venues/{id}', [VenueController::class, 'destroy']);

    // ── Venue Maintenance (Admin) ──
    Route::get('/venues/{id}/maintenance',              [VenueController::class, 'getMaintenancePeriods']);
    Route::post('/venues/{id}/maintenance',             [VenueController::class, 'addMaintenancePeriod']);
    Route::delete('/venues/{id}/maintenance/{periodId}', [VenueController::class, 'deleteMaintenancePeriod']);

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

    // ── Exhibition ──
    Route::post('/exhibition',                  [ExhibitionController::class, 'store']);
    Route::get('/exhibition',                   [ExhibitionController::class, 'index']);
    Route::get('/exhibition/{id}',              [ExhibitionController::class, 'show']);
    Route::put('/exhibition/{id}',              [ExhibitionController::class, 'update']);
    Route::patch('/exhibition/{id}/booth',      [ExhibitionController::class, 'assignBooth']);
    
    // ── Exhibition Inventory ──
    Route::get('/exhibition/inventory/{eventId}', [ExhibitionInventoryController::class, 'index']);
    Route::post('/exhibition/inventory/{eventId}/zones', [ExhibitionInventoryController::class, 'storeZone']);
    Route::delete('/exhibition/inventory/zones/{id}', [ExhibitionInventoryController::class, 'destroyZone']);
    Route::post('/exhibition/inventory/zones/{zoneId}/booths', [ExhibitionInventoryController::class, 'storeBooth']);
    Route::post('/exhibition/inventory/zones/{zoneId}/booths/batch', [ExhibitionInventoryController::class, 'batchGenerateBooths']);
    Route::put('/exhibition/inventory/booths/{id}', [ExhibitionInventoryController::class, 'updateBooth']);
    Route::delete('/exhibition/inventory/booths/{id}', [ExhibitionInventoryController::class, 'destroyBooth']);

    Route::patch('/exhibition/{id}/rank',       [ExhibitionController::class, 'updateRank']);

    // ── Agreements ──
    Route::post('/agreements/{id}/generate',       [AgreementController::class, 'generate']);
    Route::get('/agreements/{id}',                 [AgreementController::class, 'show']);
    Route::get('/agreements/{id}/download/{version?}', [AgreementController::class, 'download']);
    Route::get('/agreements/{id}/download-final',  [AgreementController::class, 'downloadFinal']);
    Route::post('/agreements/{id}/upload',         [AgreementController::class, 'upload']);
    Route::put('/agreements/{id}/respond',         [AgreementController::class, 'respond']);
    Route::put('/agreements/{id}/cancel',          [AgreementController::class, 'cancel']);

    // ── Notifications ──
    Route::get('/notifications',              [NotificationController::class, 'index']);
    Route::put('/notifications/read-all',     [NotificationController::class, 'markAllRead']);
    Route::put('/notifications/{id}/read',    [NotificationController::class, 'markRead']);
    Route::post('/fcm-token',                 [NotificationController::class, 'saveFcmToken']);

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
    Route::get('/verifications/my-documents', [VerificationController::class, 'myDocuments']);

    // ── Manager Invitation System (New) ──
    Route::get('/manager/available-assistants',   [AssistantController::class, 'getAvailableAssistants']);
    Route::post('/manager/invite-assistant',      [AssistantController::class, 'sendInvitation']);
    Route::get('/manager/invitations',            [AssistantController::class, 'getManagerInvitations']);
    Route::delete('/manager/invitations/{id}',    [AssistantController::class, 'cancelInvitation']);

    // ── Assistant (Self-Service) Routes ──
    Route::get('/assistant/requests',               [AssistantController::class, 'getRequests']);
    Route::post('/assistant/requests/{id}/respond',  [AssistantController::class, 'respondToRequest']);
    Route::match(['put', 'patch'], '/assistant/availability', [AssistantController::class, 'toggleAvailability']);
    Route::get('/assistant/work',                    [AssistantController::class, 'getAcceptedEvents']);
    Route::get('/assistant/work/{id}',               [AssistantController::class, 'getEventWorkDetails']);
    Route::get('/assistant/history',                 [AssistantController::class, 'getHistory']);
    Route::get('/assistant/history/{id}/stats',      [AssistantController::class, 'getEventStats']);

    // ── Company Analytics ──
    Route::get('/company/analytics',           [CompanyAnalyticsController::class, 'overview']);
    Route::get('/company/exhibitions/browse',  [CompanyAnalyticsController::class, 'browseExhibitions']);
    Route::get('/company/exhibitions',         [CompanyAnalyticsController::class, 'myExhibitions']);
});

// ── Storage Proxy for CORS (Flutter Web Fix) ──
Route::get('/storage-proxy/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath) || is_dir($fullPath)) abort(404);
    return response()->file($fullPath, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        'Access-Control-Allow-Headers' => '*',
    ]);
})->where('path', '.*');