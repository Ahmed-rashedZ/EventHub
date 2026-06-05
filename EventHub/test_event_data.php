<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get any event from the database
$event = \App\Models\Event::with('schedule')->first();

if (!$event) {
    echo "No events found in database!\n";
    exit;
}

echo "Event ID: " . $event->id . "\n";
echo "Title: " . $event->title . "\n";
echo "Venue ID: " . ($event->venue_id ?? 'NULL') . "\n";
echo "Is Published: " . ($event->is_published ? 'Yes' : 'No') . "\n";

echo "\n--- Raw Database Values from event_schedules ---\n";
$sched = DB::table('event_schedules')->where('event_id', $event->id)->first();
if ($sched) {
    echo "Internal Schedule Raw: " . var_export($sched->internal_schedule, true) . "\n";
    echo "External Schedule Raw: " . var_export($sched->external_schedule, true) . "\n";
    echo "Published Schedule Raw: " . var_export($sched->published_schedule, true) . "\n";
    echo "Agenda Raw: " . var_export($sched->agenda, true) . "\n";
} else {
    echo "No event_schedules record found!\n";
}

echo "\n--- Model Attributes (via Eloquent accessors/casts) ---\n";
echo "Internal Schedule: " . var_export($event->internal_schedule, true) . "\n";
echo "External Schedule: " . var_export($event->external_schedule, true) . "\n";
echo "Published Schedule: " . var_export($event->published_schedule, true) . "\n";
echo "Agenda: " . var_export($event->agenda, true) . "\n";

echo "\n--- JSON Encoded Event ---\n";
echo json_encode($event, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
