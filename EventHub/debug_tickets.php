<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = Schema::getColumnListing('tickets');
echo "Columns in tickets table: " . implode(', ', $columns) . "\n";

$ticket = DB::table('tickets')->latest()->first();
if ($ticket) {
    echo "Latest ticket data: " . json_encode($ticket) . "\n";
} else {
    echo "No tickets found.\n";
}
