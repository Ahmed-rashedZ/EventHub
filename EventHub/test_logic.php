<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$negotiation = App\Models\AgreementNegotiation::where('sponsorship_request_id', 38)->first();

$lastVersion = $negotiation->versions()->whereNotNull('file_path')->where('file_path', '!=', '')->orderByDesc('version_number')->first();
echo "lastVersion->file_path = " . ($lastVersion ? $lastVersion->file_path : 'NULL') . "\n";

$exists = \Illuminate\Support\Facades\Storage::disk('public')->exists($lastVersion->file_path);
echo "exists() = " . ($exists ? 'true' : 'false') . "\n";

$uploadedFilePath = storage_path('app/public/' . $lastVersion->file_path);
$ext = strtolower(pathinfo($uploadedFilePath, PATHINFO_EXTENSION));
echo "ext = {$ext}\n";
