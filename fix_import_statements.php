<?php

/**
 * Script to fix import statements in test files
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

    // Fix import statements
    $content = preg_replace('/use PHPUnit\\\\Framework\\\\TestCase;/', 'use Tests\Unit\BaseTest;', $content);
    $content = preg_replace('/use Tests\\\\TestCase;/', 'use Tests\Unit\BaseTest;', $content);

    // Only write if content changed
    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            echo "Fixed imports: $file\n";
            $fixedCount++;
        }
    }
}

echo "\nFixed $fixedCount files\n";
