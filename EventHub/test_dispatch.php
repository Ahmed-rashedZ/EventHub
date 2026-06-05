<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$eventId = 87; // Inspect Event 87

// Authenticate as Event Manager
$user = \App\Models\User::where('role', 'Event Manager')->first();
if ($user) {
    Auth::login($user);
}

$request1 = Request::create("/api/events/$eventId", "GET");
$response1 = app()->handle($request1);

echo "Event Response Body:\n";
echo json_encode(json_decode($response1->getContent(), true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
