<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('role', 'Event Manager')->first();
if (!$user) {
    echo "No event manager found.";
    exit;
}

$event = \App\Models\Event::where('status', 'approved')->first();
if (!$event) {
    echo "No approved event found. Please create an approved event first via standard UI.";
    exit;
}

// Ensure the user actually owns the event for testing
$event->created_by = $user->id;
$event->save();

// Re-fetch event to be safe
$event = \App\Models\Event::find($event->id);

$u1 = \App\Models\User::inRandomOrder()->first();
$u2 = \App\Models\User::inRandomOrder()->skip(1)->first();

if ($u1) {
    \App\Models\Rating::updateOrCreate(
        ['event_id' => $event->id, 'user_id' => $u1->id],
        ['rating' => 4]
    );
}

if ($u2) {
    \App\Models\Rating::updateOrCreate(
        ['event_id' => $event->id, 'user_id' => $u2->id],
        ['rating' => 5]
    );
}

echo "Created test ratings for event " . $event->title . "\n";
echo "Event's new average " . $event->fresh()->average_rating . "\n";
