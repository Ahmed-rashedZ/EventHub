<?php
function extractDocxText($filename) {
    $zip = new ZipArchive();
    if ($zip->open($filename)) {
        $content = $zip->getFromName('word/document.xml');
        $zip->close();
        if ($content !== false) {
            $content = str_replace('</w:p>', " \n ", $content);
            $content = strip_tags($content);
            return trim($content);
        }
    }
    return '';
}

$docxFile = 'C:\Users\Ahmed\Desktop\EventHub\EventHub\storage\app\public\agreements\agreement_35_v2.docx';
if (file_exists($docxFile)) {
    echo extractDocxText($docxFile);
} else {
    echo "File not found";
}
