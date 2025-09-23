<?php

/**
 * Script to fix test method names to start with 'test_'
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

    // Fix test method names - add 'test_' prefix to methods that start with 'it_'
    $content = preg_replace('/public function it_/', 'public function test_it_', $content);

    // Only write if content changed
    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            echo "Fixed test methods: $file\n";
            $fixedCount++;
        }
    }
}

echo "\nFixed $fixedCount files\n";
