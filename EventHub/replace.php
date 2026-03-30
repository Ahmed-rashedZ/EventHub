<?php

$dir = new RecursiveDirectoryIterator(__DIR__ . '/resources/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.blade\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach($files as $file) {
    if (strpos($file[0], 'welcome.blade.php') !== false) continue; // skip welcome

    $content = file_get_contents($file[0]);

    // Replace href="/something.html" with href="/something"
    $content = preg_replace('/href="\/([a-zA-Z0-9_\-\/]+)\.html"/', 'href="/$1"', $content);

    // Replace href="something.html" with href="something"
    $content = preg_replace('/href="([a-zA-Z0-9_\-]+)\.html"/', 'href="/$1"', $content);

    // Replace window.location.href = 'something.html' or '/something.html'
    $content = preg_replace('/(window\.location\.href\s*=\s*[\'"])\/?([a-zA-Z0-9_\-\/]+)\.html([\'"])/', '$1/$2$3', $content);

    file_put_contents($file[0], $content);
}

// Now update public/js/auth.js
$authJs = __DIR__ . '/public/js/auth.js';
if (file_exists($authJs)) {
    $content = file_get_contents($authJs);
    $content = preg_replace('/\'\/([a-zA-Z0-9_\-\/]+)\.html\'/', "'/$1'", $content);
    // Add logic to delete cookie on logout
    // Search for localStorage.removeItem('token') or similar
    if (strpos($content, 'auth_token=;') === false) {
        $content = str_replace(
            "localStorage.removeItem('token');", 
            "localStorage.removeItem('token');\n        document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';", 
            $content
        );
        $content = str_replace(
            "localStorage.clear();", 
            "localStorage.clear();\n        document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';", 
            $content
        );
    }
    file_put_contents($authJs, $content);
}

// Now update public/js/api.js (if need be)
$apiJs = __DIR__ . '/public/js/api.js';
if (file_exists($apiJs)) {
    $content = file_get_contents($apiJs);
    if (strpos($content, 'auth_token=;') === false) {
        $content = str_replace(
            "localStorage.removeItem('token');", 
            "localStorage.removeItem('token');\n        document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';", 
            $content
        );
    }
    file_put_contents($apiJs, $content);
}

echo "Replacements done.";
