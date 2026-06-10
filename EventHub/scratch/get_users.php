<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$profiles = DB::table('profiles')->get();
echo json_encode($profiles, JSON_PRETTY_PRINT);
