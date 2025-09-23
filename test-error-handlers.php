<?php

/**
 * Test script to run a few failing tests and check if error handler warnings are resolved
 */
echo "Running tests to check error handler warnings...\n";

// Run a few specific failing tests
$testFiles = [
    'tests/Unit/Deployment/RollbackTest.php',
    'tests/Unit/Factories/FactoriesTest.php',
    'tests/Unit/Helpers/PriceHelperTest.php',
];

foreach ($testFiles as $testFile) {
    echo "\nTesting: $testFile\n";
    echo str_repeat('-', 50)."\n";

    $command = "php vendor/bin/phpunit --configuration phpunit.xml $testFile 2>&1";
    $output = shell_exec($command);

    // Check if error handler warnings are present
    if (strpos($output, 'removed error handlers other than its own') !== false) {
        echo "❌ Error handler warnings still present\n";
    } else {
        echo "✅ No error handler warnings detected\n";
    }

    // Show last few lines of output
    $lines = explode("\n", trim($output));
    $lastLines = array_slice($lines, -5);
    echo "Last output lines:\n".implode("\n", $lastLines)."\n";
}

echo "\nTest completed.\n";
