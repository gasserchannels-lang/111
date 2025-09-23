<?php

$content = file_get_contents('phpstan_errors.txt');
$lines = explode("\n", $content);
$files = [];
$currentFile = '';
$errorCount = 0;

echo "=== DEBUGGING FILE PARSING ===\n";
foreach ($lines as $i => $line) {
    if (strpos($line, 'Line') !== false && strpos($line, '.php') !== false) {
        echo 'Found file line '.($i + 1).': '.$line."\n";
        $currentFile = trim(str_replace('Line   ', '', $line));
        $files[$currentFile] = 0;
    } elseif (preg_match('/^  :[0-9]+/', $line)) {
        $errorCount++;
        if ($currentFile) {
            $files[$currentFile] = $errorCount;
        }
    }
}

echo "\n=== FILES FOUND ===\n";
foreach ($files as $file => $count) {
    echo "$file: $count errors\n";
}
