<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'Event Manager')->first();
Auth::login($user);

$events = App\Models\Event::with('venue')
    ->where('created_by', $user->id)
    ->orderBy('created_at', 'desc')
    ->get();

echo json_encode($events);
