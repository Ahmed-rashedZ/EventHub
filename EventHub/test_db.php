<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = DB::table('agreement_versions')->orderByDesc('id')->limit(20)->get(['id', 'negotiation_id', 'version_number', 'action', 'file_path']);

foreach ($rows as $row) {
    echo "ID: {$row->id} | Neg: {$row->negotiation_id} | Ver: {$row->version_number} | Action: {$row->action} | File: '" . $row->file_path . "'\n";
}
