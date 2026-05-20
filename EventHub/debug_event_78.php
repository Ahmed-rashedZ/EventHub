<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Event;

$event = Event::with('exhibitors.company.profile')->find(78);
if (!$event) { echo "Event not found"; exit; }

echo "Event: " . $event->title . "\n";
echo "Exhibitors Count: " . $event->exhibitors->count() . "\n";

foreach ($event->exhibitors as $ex) {
    $company = $ex->company;
    $profile = $company ? $company->profile : null;
    echo "- Company: " . ($company->name ?? 'N/A') . "\n";
    echo "  Profile: " . ($profile ? 'Yes' : 'No') . "\n";
    echo "  Logo: " . ($profile->logo ?? 'N/A') . "\n";
    echo "  Status: " . $ex->status . "\n";
}
