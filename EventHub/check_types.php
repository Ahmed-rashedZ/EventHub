<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;

foreach (Event::with('schedule')->get() as $e) {
    echo "Event ID: {$e->id}, Title: {$e->title}\n";
    if ($e->schedule) {
        echo "  - internal_schedule type: " . gettype($e->schedule->internal_schedule) . "\n";
        echo "  - external_schedule type: " . gettype($e->schedule->external_schedule) . "\n";
        echo "  - published_schedule type: " . gettype($e->schedule->published_schedule) . "\n";
        echo "  - published_schedule value: " . json_encode($e->schedule->published_schedule) . "\n";
    } else {
        echo "  - No schedule row\n";
    }
}
