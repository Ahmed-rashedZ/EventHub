<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Event;

$titles = [
    'الـ Exhibition الثالث',
    'الـ Exhibition الثالث ',
    ' Exhibition الثالث',
    'المعرض الثالث'
];

foreach ($titles as $title) {
    $event = Event::where('title', 'like', '%' . $title . '%')->first();
    if ($event) {
        echo "FOUND ID: " . $event->id . " | TITLE: " . $event->title . "\n";
        echo "Exhibitors: " . $event->exhibitors->count() . "\n";
        foreach ($event->exhibitors as $ex) {
             echo " - " . ($ex->company->profile->company_name ?? $ex->company->name) . " (Logo: " . ($ex->company->profile->logo ?? 'None') . ")\n";
        }
        break;
    }
}
