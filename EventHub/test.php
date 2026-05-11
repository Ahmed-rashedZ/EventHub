<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$phpWord = \PhpOffice\PhpWord\IOFactory::load(storage_path('app/public/agreements/agreement_35_v2.docx'));
$writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
$writer->save(storage_path('app/public/test.html'));
echo "OK\n";
