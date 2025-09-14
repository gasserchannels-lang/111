<?php

/**
 * Strict Tests Runner - تشغيل الاختبارات الصارمة
 *
 * سكريبت لتشغيل اختبارات صارمة مع أعلى مستوى من الدقة
 */
echo "=== Strict Tests Runner ===\n\n";

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

// 1. Run strict Mockery tests
echo "1. Running Strict Mockery Tests:\n";
echo "----------------------------------------\n";
runCommand('php artisan test --configuration=phpunit.strict.xml tests/Unit/StrictMockeryTest.php', 'Strict Mockery Tests');

// 2. Run isolated strict tests
echo "2. Running Isolated Strict Tests:\n";
echo "----------------------------------------\n";
runCommand('php artisan test --process-isolation tests/Unit/IsolatedStrictTest.php', 'Isolated Strict Tests');

// 3. Run simple Mockery tests (baseline)
echo "3. Running Simple Mockery Tests (Baseline):\n";
echo "----------------------------------------\n";
runCommand('php artisan test tests/Unit/SimpleMockeryTest.php', 'Simple Mockery Tests');

// 4. Run tests with strict mode
echo "4. Running Tests with Strict Mode:\n";
echo "----------------------------------------\n";
runCommand('php artisan test --configuration=phpunit.strict.xml --filter=test_strict', 'Strict Mode Tests');

// 5. Run tests with coverage
echo "5. Running Tests with Coverage:\n";
echo "----------------------------------------\n";
runCommand('php artisan test --configuration=phpunit.strict.xml --coverage', 'Coverage Tests');

// 6. Run tests with detailed output
echo "6. Running Tests with Detailed Output:\n";
echo "----------------------------------------\n";
runCommand('php artisan test --configuration=phpunit.strict.xml --verbose', 'Detailed Output Tests');

// 7. Run tests with stop on failure
echo "7. Running Tests with Stop on Failure:\n";
echo "----------------------------------------\n";
runCommand('php artisan test --configuration=phpunit.strict.xml --stop-on-failure', 'Stop on Failure Tests');

// 8. Generate comprehensive report
echo "8. Generating Comprehensive Report:\n";
echo "----------------------------------------\n";

$reportCommands = [
    'php artisan test --configuration=phpunit.strict.xml --log-junit=storage/logs/junit-strict.xml' => 'JUnit XML Report',
    'php artisan test --configuration=phpunit.strict.xml --coverage-html=storage/logs/coverage-strict' => 'HTML Coverage Report',
    'php artisan test --configuration=phpunit.strict.xml --coverage-clover=storage/logs/coverage-strict.xml' => 'Clover Coverage Report',
];

foreach ($reportCommands as $command => $description) {
    runCommand($command, $description);
}

echo "=== Strict Tests Complete ===\n";
echo "\nSummary:\n";
echo "- Strict Mockery Tests: اختبارات Mockery صارمة\n";
echo "- Isolated Strict Tests: اختبارات صارمة معزولة\n";
echo "- Simple Mockery Tests: اختبارات Mockery أساسية\n";
echo "- Strict Mode Tests: اختبارات مع وضع صارم\n";
echo "- Coverage Tests: اختبارات مع تغطية شاملة\n";
echo "\nFeatures:\n";
echo "✓ Strict expectations validation\n";
echo "✓ Exact parameter matching\n";
echo "✓ Process isolation for console tests\n";
echo "✓ Comprehensive error handling\n";
echo "✓ Detailed coverage reports\n";
echo "✓ Timeout enforcement\n";
echo "✓ Memory limit monitoring\n";
echo "\nRecommendations:\n";
echo "1. Use StrictMockeryTest.php for comprehensive Mockery testing\n";
echo "2. Use IsolatedStrictTest.php for console-related testing\n";
echo "3. Use phpunit.strict.xml for strict validation\n";
echo "4. Monitor memory usage and timeouts\n";
echo "5. Review coverage reports for gaps\n";
