<?php

$errorFile = 'phpstan_errors.txt';
$lines = file($errorFile, FILE_IGNORE_NEW_LINES);

$fileErrors = [];

foreach ($lines as $line) {
    if (strpos($line, ':') !== false) {
        $parts = explode(':', $line);
        if (count($parts) >= 2) {
            $filePath = $parts[0];
            $file = basename($filePath);
            if (strpos($file, '.php') !== false) {
                $fileErrors[$file] = ($fileErrors[$file] ?? 0) + 1;
            }
        }
    }
}

// Sort by error count (descending)
arsort($fileErrors);

echo "Files with errors (sorted by error count):\n";
echo "==========================================\n";

foreach ($fileErrors as $file => $count) {
    echo sprintf("%-60s %d errors\n", $file, $count);
}

echo "\nTotal files with errors: ".count($fileErrors)."\n";
echo 'Total errors: '.array_sum($fileErrors)."\n";
