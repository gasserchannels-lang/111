<?php

/**
 * Alternative Tests Runner
 *
 * This script runs alternative tests to avoid Console Output conflicts
 * while maintaining comprehensive test coverage.
 */
echo "=== Alternative Tests Runner ===\n\n";

// Function to run command and display output
function runCommand($command, $description)
{
    echo "{$description}...\n";
    echo "Command: {$command}\n";

    $output = [];
    $returnCode = 0;
    exec($command.' 2>&1', $output, $returnCode);

    if ($returnCode === 0) {
        echo "✓ Success\n";
        if (! empty($output)) {
            echo 'Output: '.implode("\n", $output)."\n";
        }
    } else {
        echo "✗ Failed (Exit code: {$returnCode})\n";
        if (! empty($output)) {
            echo 'Error: '.implode("\n", $output)."\n";
        }
    }
    echo "\n";
}

// 1. Run simple Mockery tests (no console conflicts)
echo "1. Running Simple Mockery Tests:\n";
echo "----------------------------------------\n";
runCommand('php artisan test tests/Unit/SimpleMockeryTest.php', 'Simple Mockery Tests');

// 2. Run alternative Mockery tests (using different mocking approaches)
echo "2. Running Alternative Mockery Tests:\n";
echo "----------------------------------------\n";
runCommand('php artisan test tests/Unit/AlternativeMockeryTest.php', 'Alternative Mockery Tests');

// 3. Run process isolation tests (for console mocking)
echo "3. Running Process Isolation Tests:\n";
echo "----------------------------------------\n";
runCommand('php artisan test --configuration=phpunit.isolation.xml tests/Unit/ProcessIsolationTest.php', 'Process Isolation Tests');

// 4. Run specific test suites to avoid conflicts
echo "4. Running Safe Test Suites:\n";
echo "----------------------------------------\n";

$safeTestSuites = [
    'Feature' => 'Feature tests (usually safer)',
    'Unit' => 'Unit tests (excluding console tests)',
];

foreach ($safeTestSuites as $suite => $description) {
    runCommand("php artisan test --testsuite={$suite} --exclude-group=console", $description);
}

// 5. Run tests with specific filters
echo "5. Running Filtered Tests:\n";
echo "----------------------------------------\n";

$testFilters = [
    '--filter=test_basic_mockery' => 'Basic Mockery tests only',
    '--filter=test_mock_database' => 'Database mocking tests only',
    '--filter=test_mock_cache' => 'Cache mocking tests only',
];

foreach ($testFilters as $filter => $description) {
    runCommand("php artisan test {$filter}", $description);
}

// 6. Generate test report
echo "6. Generating Test Report:\n";
echo "----------------------------------------\n";

$reportCommands = [
    'php artisan test --log-junit=storage/logs/alternative-tests.xml' => 'JUnit XML report',
    'php artisan test --coverage-html=storage/logs/coverage-alternative' => 'HTML coverage report',
];

foreach ($reportCommands as $command => $description) {
    runCommand($command, $description);
}

echo "=== Alternative Tests Complete ===\n";
echo "\nSummary:\n";
echo "- Simple Mockery tests: Basic functionality verification\n";
echo "- Alternative Mockery tests: Different mocking approaches\n";
echo "- Process isolation tests: Console mocking with isolation\n";
echo "- Safe test suites: Avoiding problematic tests\n";
echo "- Filtered tests: Running specific test patterns\n";
echo "\nRecommendations:\n";
echo "1. Use SimpleMockeryTest.php for basic Mockery functionality\n";
echo "2. Use AlternativeMockeryTest.php for complex mocking scenarios\n";
echo "3. Use ProcessIsolationTest.php with --process-isolation for console mocking\n";
echo "4. Avoid running all tests together to prevent conflicts\n";
echo "5. Use specific test filters to run only what you need\n";
