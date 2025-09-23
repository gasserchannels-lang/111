<?php

$content = file_get_contents('phpstan_errors.txt');
$lines = explode("\n", $content);
$files = [];
$currentFile = '';
$errorCount = 0;

foreach ($lines as $line) {
    if (preg_match('/^  Line   (.+\.php)/', trim($line), $matches)) {
        if ($currentFile && $errorCount > 0) {
            $files[$currentFile] = $errorCount;
        }
        $currentFile = $matches[1];
        $errorCount = 0;
    } elseif (preg_match('/^  :[0-9]+/', $line)) {
        $errorCount++;
    }
}

if ($currentFile && $errorCount > 0) {
    $files[$currentFile] = $errorCount;
}

arsort($files);
echo "=== FILES WITH ERRORS (Sorted by Error Count) ===\n";
foreach ($files as $file => $count) {
    echo "$file: $count errors\n";
}

echo "\n=== TOTAL FILES WITH ERRORS: ".count($files)." ===\n";
echo '=== TOTAL ERRORS: '.array_sum($files)." ===\n";
