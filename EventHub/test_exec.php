<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fullDocxPath = storage_path('app/public/agreements/agreement_35_v2.docx');
$fullPdfPath = storage_path('app/public/test_exec.pdf');

$psCommand = 'powershell.exe -ExecutionPolicy Bypass -WindowStyle Hidden -Command "$word = New-Object -ComObject Word.Application; $word.Visible = $false; $doc = $word.Documents.Open(\'' . str_replace('/', '\\', $fullDocxPath) . '\'); $doc.SaveAs([ref] \'' . str_replace('/', '\\', $fullPdfPath) . '\', [ref] 17); $doc.Close(); $word.Quit();"';
echo "Command: $psCommand\n";

exec($psCommand, $output, $returnVar);

echo "Return Var: $returnVar\n";
echo "Output: " . print_r($output, true) . "\n";
echo "File Exists: " . (file_exists($fullPdfPath) ? 'Yes' : 'No') . "\n";
