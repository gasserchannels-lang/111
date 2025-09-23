<?php

// Read all errors from the file
$errorFile = 'all_errors.txt';
$lines = file($errorFile, FILE_IGNORE_NEW_LINES);

$fileErrors = [];
$totalErrors = 0;

foreach ($lines as $line) {
    if (strpos($line, '.php:') !== false) {
        $totalErrors++;
        $parts = explode('.php:', $line);
        if (count($parts) >= 2) {
            $filePath = $parts[0].'.php';
            $file = basename($filePath);
            if (strpos($file, '.php') !== false) {
                $fileErrors[$file] = ($fileErrors[$file] ?? 0) + 1;
            }
        }
    }
}

// Sort by error count (descending)
arsort($fileErrors);

echo "=== قائمة شاملة لجميع الملفات التي تحتوي على أخطاء ===\n";
echo "إجمالي الأخطاء: $totalErrors\n";
echo 'عدد الملفات: '.count($fileErrors)."\n\n";

$todoItems = [];
$counter = 1;

foreach ($fileErrors as $file => $count) {
    echo sprintf("%d. %-60s %d أخطاء\n", $counter, $file, $count);

    // Create TODO item
    $todoItems[] = [
        'id' => 'fix_'.strtolower(str_replace(['.php', 'Test'], ['', '_test'], $file)),
        'content' => "إصلاح $file - $count أخطاء",
        'status' => 'pending',
    ];

    $counter++;
}

echo "\n=== قائمة To-Do JSON ===\n";
echo json_encode($todoItems, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
