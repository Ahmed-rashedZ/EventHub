<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    DB::connection()->getPdo();
    echo "SUCCESS: Connected to database '" . DB::connection()->getDatabaseName() . "' successfully!\n";
} catch (\Exception $e) {
    echo "ERROR: Could not connect to database!\n";
    echo $e->getMessage() . "\n";
}
