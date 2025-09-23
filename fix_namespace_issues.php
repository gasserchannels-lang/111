<?php

/**
 * Script to fix namespace issues in test files
 */

// Get all test files
$testFiles = glob('tests/Unit/**/*Test.php');

$fixedCount = 0;

foreach ($testFiles as $file) {
    if (! file_exists($file)) {
        continue;
    }

    $content = file_get_contents($file);
    $originalContent = $content;

    // Fix namespace issues
    $content = preg_replace('/use Tests\\\\TestCase;\s*class (\w+) extends TestCase/', 'use Tests\Unit\BaseTest;

class $1 extends BaseTest', $content);

    // Only write if content changed
    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            echo "Fixed namespace: $file\n";
            $fixedCount++;
        }
    }
}

echo "\nFixed $fixedCount files\n";
