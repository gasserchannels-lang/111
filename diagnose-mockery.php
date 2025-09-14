<?php

/**
 * Mockery Conflict Diagnostic Script
 *
 * This script analyzes the Laravel project for potential Mockery conflicts
 * and provides detailed recommendations for resolution.
 */
echo "=== Mockery Conflict Diagnostic Script ===\n\n";

// 1. Check library versions
echo "1. Checking Library Versions:\n";
echo "----------------------------------------\n";

$composerLock = json_decode(file_get_contents('composer.lock'), true);
$packages = $composerLock['packages'] ?? [];

$keyPackages = [
    'mockery/mockery',
    'phpunit/phpunit',
    'laravel/framework',
    'nunomaduro/collision',
];

foreach ($keyPackages as $package) {
    foreach ($packages as $pkg) {
        if ($pkg['name'] === $package) {
            echo "✓ {$package}: {$pkg['version']}\n";
            break;
        }
    }
}

echo "\n";

// 2. Find Mockery usage in tests
echo "2. Analyzing Mockery Usage in Tests:\n";
echo "----------------------------------------\n";

$testFiles = glob('tests/**/*Test.php');
$mockeryUsage = [];
$consoleOutputMocking = [];

foreach ($testFiles as $file) {
    $content = file_get_contents($file);

    if (strpos($content, 'Mockery') !== false || strpos($content, 'mock(') !== false) {
        $mockeryUsage[] = $file;
    }

    // Check for Console Output mocking (potential conflict source)
    if (preg_match('/mock.*Console.*Output|Console.*Output.*mock/i', $content)) {
        $consoleOutputMocking[] = $file;
    }
}

echo 'Files using Mockery: '.count($mockeryUsage)."\n";
foreach ($mockeryUsage as $file) {
    echo "  - {$file}\n";
}

if (! empty($consoleOutputMocking)) {
    echo "\n⚠️  Files with potential Console Output mocking conflicts:\n";
    foreach ($consoleOutputMocking as $file) {
        echo "  - {$file}\n";
    }
}

echo "\n";

// 3. Check Service Providers
echo "3. Checking Service Providers:\n";
echo "----------------------------------------\n";

$providers = glob('app/Providers/*.php');
$testProviders = [];

foreach ($providers as $provider) {
    $content = file_get_contents($provider);
    if (strpos($content, 'Mockery') !== false || strpos($content, 'testing') !== false) {
        $testProviders[] = $provider;
    }
}

if (! empty($testProviders)) {
    echo "⚠️  Service Providers with potential Mockery usage:\n";
    foreach ($testProviders as $provider) {
        echo "  - {$provider}\n";
    }
} else {
    echo "✓ No Service Providers with Mockery usage found\n";
}

echo "\n";

// 4. Check TestCase configuration
echo "4. Checking TestCase Configuration:\n";
echo "----------------------------------------\n";

$testCaseFile = 'tests/TestCase.php';
if (file_exists($testCaseFile)) {
    $content = file_get_contents($testCaseFile);

    $checks = [
        'Mockery::resetContainer() in setUp' => strpos($content, 'Mockery::resetContainer()') !== false,
        'Mockery::close() in tearDown' => strpos($content, 'Mockery::close()') !== false,
        'Exception handling in tearDown' => strpos($content, 'try') !== false && strpos($content, 'catch') !== false,
        'Proper parent calls' => strpos($content, 'parent::setUp()') !== false && strpos($content, 'parent::tearDown()') !== false,
    ];

    foreach ($checks as $check => $status) {
        echo ($status ? '✓' : '✗')." {$check}\n";
    }
} else {
    echo "✗ TestCase.php not found\n";
}

echo "\n";

// 5. Check PHPUnit configuration
echo "5. Checking PHPUnit Configuration:\n";
echo "----------------------------------------\n";

$phpunitFile = 'phpunit.xml';
if (file_exists($phpunitFile)) {
    $content = file_get_contents($phpunitFile);

    $checks = [
        'Bootstrap file specified' => strpos($content, 'bootstrap=') !== false,
        'Mockery TestListener' => strpos($content, 'Mockery\\Adapter\\Phpunit\\TestListener') !== false,
        'Process isolation disabled' => strpos($content, 'processIsolation="false"') !== false,
        'Backup globals disabled' => strpos($content, 'backupGlobals="false"') !== false,
    ];

    foreach ($checks as $check => $status) {
        echo ($status ? '✓' : '✗')." {$check}\n";
    }
} else {
    echo "✗ phpunit.xml not found\n";
}

echo "\n";

// 6. Check bootstrap.php
echo "6. Checking Bootstrap Configuration:\n";
echo "----------------------------------------\n";

$bootstrapFile = 'tests/bootstrap.php';
if (file_exists($bootstrapFile)) {
    $content = file_get_contents($bootstrapFile);

    $checks = [
        'Mockery reset in bootstrap' => strpos($content, 'Mockery::resetContainer()') !== false,
        'Composer autoload included' => strpos($content, 'vendor/autoload.php') !== false,
        'Laravel app bootstrap' => strpos($content, 'bootstrap/app.php') !== false,
    ];

    foreach ($checks as $check => $status) {
        echo ($status ? '✓' : '✗')." {$check}\n";
    }
} else {
    echo "✗ tests/bootstrap.php not found\n";
}

echo "\n";

// 7. Recommendations
echo "7. Recommendations:\n";
echo "----------------------------------------\n";

$recommendations = [];

if (count($consoleOutputMocking) > 0) {
    $recommendations[] = 'Review files with Console Output mocking - these are likely sources of conflicts';
}

if (count($mockeryUsage) > 10) {
    $recommendations[] = "Consider using processIsolation='true' in phpunit.xml for large test suites";
}

if (! file_exists('tests/bootstrap.php')) {
    $recommendations[] = 'Create tests/bootstrap.php with Mockery initialization';
}

$recommendations[] = "Run 'composer clear-all' to clear all caches";
$recommendations[] = "Delete vendor/ directory and run 'composer install'";
$recommendations[] = 'Run tests individually to identify specific failing tests';

foreach ($recommendations as $i => $rec) {
    echo ($i + 1).". {$rec}\n";
}

echo "\n";

// 8. Generate test command suggestions
echo "8. Suggested Test Commands:\n";
echo "----------------------------------------\n";

echo "Individual test files:\n";
foreach (array_slice($mockeryUsage, 0, 5) as $file) {
    $relativeFile = str_replace('tests/', '', $file);
    echo "  php artisan test {$relativeFile}\n";
}

echo "\nTest suites:\n";
echo "  php artisan test --testsuite=Unit\n";
echo "  php artisan test --testsuite=Feature\n";
echo "  php artisan test --process-isolation\n";

echo "\n=== Diagnostic Complete ===\n";
