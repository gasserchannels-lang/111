<?php

/**
 * Script to fix architecture test files by removing error handler manipulation
 */
$architectureTestFiles = [
    'tests/Unit/Architecture/CQRSArchitectureTest.php',
    'tests/Unit/Architecture/DatabaseArchitectureTest.php',
    'tests/Unit/Architecture/DomainDrivenDesignTest.php',
    'tests/Unit/Architecture/EventDrivenArchitectureTest.php',
    'tests/Unit/Architecture/EventSourcingTest.php',
    'tests/Unit/Architecture/HexagonalArchitectureTest.php',
];

foreach ($architectureTestFiles as $file) {
    if (! file_exists($file)) {
        echo "File not found: $file\n";

        continue;
    }

    $content = file_get_contents($file);

    // Replace the class declaration and error handler manipulation
    $content = preg_replace('/use Tests\\\\TestCase;/', 'use Tests\Unit\BaseTest;', $content);
    $content = preg_replace('/extends TestCase/', 'extends BaseTest', $content);

    // Remove error handler manipulation from setUp method
    $content = preg_replace(
        '/protected function setUp\(\): void\s*\{\s*parent::setUp\(\);\s*.*?\}/s',
        'protected function setUp(): void
    {
        parent::setUp();
    }',
        $content
    );

    // Remove error handler manipulation from tearDown method
    $content = preg_replace(
        '/protected function tearDown\(\): void\s*\{\s*.*?parent::tearDown\(\);\s*\}/s',
        'protected function tearDown(): void
    {
        parent::tearDown();
    }',
        $content
    );

    // Remove error handler properties
    $content = preg_replace('/\s*\/\*\* @var callable\|\|null \*\/\s*protected \$originalErrorHandler;\s*/', '', $content);
    $content = preg_replace('/\s*\/\*\* @var \\\\Illuminate\\\\Contracts\\\\Debug\\\\ExceptionHandler\|\|null \*\/\s*protected \$originalExceptionHandler;\s*/', '', $content);

    file_put_contents($file, $content);
    echo "Fixed: $file\n";
}

echo "All architecture test files have been fixed.\n";
