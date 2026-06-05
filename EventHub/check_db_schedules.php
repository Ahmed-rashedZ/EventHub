<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$schedules = DB::table('event_schedules')->get();

echo "Checking " . count($schedules) . " schedules...\n";

foreach ($schedules as $sched) {
    echo "Event ID: {$sched->event_id}\n";
    
    $fields = ['internal_schedule', 'external_schedule', 'published_schedule', 'agenda'];
    foreach ($fields as $field) {
        $val = $sched->$field;
        if ($val === null) {
            echo "  {$field}: NULL\n";
        } else {
            echo "  {$field} (raw): " . substr($val, 0, 100) . "\n";
            // Check if it is a JSON string of a JSON string (double-encoded)
            $decoded = json_decode($val, true);
            if (is_string($decoded)) {
                echo "  WARNING: {$field} is double-encoded! Decoded value is a string: {$decoded}\n";
            }
        }
    }
}
echo "Done!\n";
