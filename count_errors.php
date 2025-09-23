<?php

$errorFile = 'phpstan_errors.txt';
$lines = file($errorFile, FILE_IGNORE_NEW_LINES);

$fileErrors = [];

foreach ($lines as $line) {
    if (preg_match('/^C:.*\\\\([^:]+):/', $line, $matches) || preg_match('/^C:.*\/([^:]+):/', $line, $matches)) {
        $file = $matches[1];
        $fileErrors[$file] = ($fileErrors[$file] ?? 0) + 1;
    }
}

// Sort by error count (descending)
arsort($fileErrors);

echo "Files with errors (sorted by error count):\n";
echo "==========================================\n";

foreach ($fileErrors as $file => $count) {
    echo sprintf("%-80s %d errors\n", $file, $count);
}

echo "\nTotal files with errors: ".count($fileErrors)."\n";
echo 'Total errors: '.array_sum($fileErrors)."\n";
