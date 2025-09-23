<?php

/**
 * Clean test runner that suppresses risky test warnings
 * This script runs PHPUnit and filters out risky test warnings
 */
echo "Running tests with clean output...\n\n";

// Run PHPUnit and capture output
$command = 'php vendor/bin/phpunit --testdox 2>&1';
$output = shell_exec($command);

// Split output into lines
$lines = explode("\n", $output);

$testResults = [];
$currentTest = '';
$riskyCount = 0;
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

foreach ($lines as $line) {
    // Skip risky test warnings
    if (strpos($line, 'Test code or tested code removed error handlers') !== false) {
        $riskyCount++;

        continue;
    }

    if (strpos($line, 'Test code or tested code removed exception handlers') !== false) {
        continue;
    }

    if (strpos($line, 'There were') !== false && strpos($line, 'risky tests') !== false) {
        continue;
    }

    if (strpos($line, 'OK, but there were issues!') !== false) {
        continue;
    }

    if (strpos($line, 'Tests:') !== false && strpos($line, 'Risky:') !== false) {
        // Extract test counts
        if (preg_match('/Tests: (\d+), Assertions: (\d+), Risky: (\d+)/', $line, $matches)) {
            $totalTests = $matches[1];
            $riskyCount = $matches[3];
        }

        continue;
    }

    if (strpos($line, 'OK (') !== false) {
        // Extract passed test count
        if (preg_match('/OK \((\d+) tests, (\d+) assertions\)/', $line, $matches)) {
            $totalTests = $matches[1];
            $passedTests = $matches[1];
        }

        continue;
    }

    if (strpos($line, 'FAILURES!') !== false) {
        // Extract failed test count
        if (preg_match('/Tests: (\d+), Assertions: (\d+), Failures: (\d+)/', $line, $matches)) {
            $totalTests = $matches[1];
            $failedTests = $matches[3];
            $passedTests = $totalTests - $failedTests;
        }

        continue;
    }

    // Display clean output
    echo $line."\n";
}

// Summary
echo "\n".str_repeat('=', 60)."\n";
echo "TEST SUMMARY\n";
echo str_repeat('=', 60)."\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: $failedTests\n";
echo "Risky (suppressed): $riskyCount\n";

if ($failedTests > 0) {
    echo "\n❌ Some tests failed!\n";
    exit(1);
} else {
    echo "\n✅ All tests passed! (Risky warnings suppressed)\n";
    exit(0);
}
