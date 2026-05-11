<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Assuming agreement_35 from the PDF names we saw earlier
$neg = App\Models\AgreementNegotiation::where('sponsorship_request_id', 35)->first();

if (!$neg) {
    echo "Negotiation not found for 35\n";
} else {
    $lastVersion = $neg->versions()->whereNotNull('file_path')->orderByDesc('version_number')->first();
    echo "Last version ID: " . ($lastVersion->id ?? 'NULL') . "\n";
    echo "File path: " . ($lastVersion->file_path ?? 'NULL') . "\n";
    
    $exists = \Illuminate\Support\Facades\Storage::disk('public')->exists($lastVersion->file_path ?? '');
    echo "File exists in storage? " . ($exists ? 'Yes' : 'No') . "\n";
}
