<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$events = \App\Models\Event::all();
echo "Total events: " . $events->count() . "\n";
foreach ($events as $e) {
    echo "ID: {$e->id} | Title: {$e->title} | Status: {$e->status} | Start: {$e->start_time} | End: {$e->end_time} | TimeStatus: {$e->time_status} | Published: " . ($e->is_published ? 'Yes' : 'No') . "\n";
}
