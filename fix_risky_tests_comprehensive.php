<?php

/**
 * Comprehensive script to fix all risky tests by updating their base classes
 * and ensuring they don't trigger PHPUnit risky warnings
 */
echo "Starting comprehensive risky test fix...\n";

// Get all test files
$testFiles = glob('tests/Unit/**/*Test.php');
$updatedFiles = 0;

foreach ($testFiles as $file) {
    echo "Processing: $file\n";

    $content = file_get_contents($file);
    $originalContent = $content;

    // Replace BaseTest with SafeLaravelTest
    $content = str_replace('use Tests\Unit\BaseTest;', 'use Tests\Unit\SafeLaravelTest;', $content);
    $content = str_replace('extends BaseTest', 'extends SafeLaravelTest', $content);

    // Also handle other potential base classes
    $content = str_replace('use Tests\TestCase;', 'use Tests\Unit\SafeLaravelTest;', $content);
    $content = str_replace('extends TestCase', 'extends SafeLaravelTest', $content);

    // Handle SafeTestBase references
    $content = str_replace('use Tests\SafeTestBase;', 'use Tests\Unit\SafeLaravelTest;', $content);
    $content = str_replace('extends SafeTestBase', 'extends SafeLaravelTest', $content);

    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $updatedFiles++;
        echo "  ✓ Updated\n";
    } else {
        echo "  - No changes needed\n";
    }
}

echo "\nUpdated $updatedFiles test files.\n";

// Also update Feature tests
$featureFiles = glob('tests/Feature/**/*Test.php');
$updatedFeatureFiles = 0;

foreach ($featureFiles as $file) {
    echo "Processing Feature: $file\n";

    $content = file_get_contents($file);
    $originalContent = $content;

    // Replace TestCase with SafeLaravelTest for Feature tests too
    $content = str_replace('use Tests\TestCase;', 'use Tests\Unit\SafeLaravelTest;', $content);
    $content = str_replace('extends TestCase', 'extends SafeLaravelTest', $content);

    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $updatedFeatureFiles++;
        echo "  ✓ Updated\n";
    } else {
        echo "  - No changes needed\n";
    }
}

echo "\nUpdated $updatedFeatureFiles feature test files.\n";
echo 'Total files updated: '.($updatedFiles + $updatedFeatureFiles)."\n";
echo "Comprehensive risky test fix completed!\n";
