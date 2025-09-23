<?php

/**
 * Comprehensive script to fix all architecture test files
 */
$architectureTestFiles = [
    'tests/Unit/Architecture/APIGatewayTest.php',
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

    // Replace the class declaration
    $content = preg_replace('/use Tests\\\\TestCase;/', 'use Tests\Unit\BaseTest;', $content);
    $content = preg_replace('/extends TestCase/', 'extends BaseTest', $content);

    // Remove all error handler related properties
    $content = preg_replace('/\s*\/\*\* @var callable\|\|null \*\/\s*protected \$originalErrorHandler;\s*/', '', $content);
    $content = preg_replace('/\s*\/\*\* @var \\\\Illuminate\\\\Contracts\\\\Debug\\\\ExceptionHandler\|\|null \*\/\s*protected \$originalExceptionHandler;\s*/', '', $content);

    // Clean up setUp method - remove all error handler code and fix syntax
    $content = preg_replace(
        '/protected function setUp\(\): void\s*\{\s*parent::setUp\(\);\s*.*?\}/s',
        'protected function setUp(): void
    {
        parent::setUp();
    }',
        $content
    );

    // Clean up tearDown method - remove all error handler code and fix syntax
    $content = preg_replace(
        '/protected function tearDown\(\): void\s*\{\s*.*?parent::tearDown\(\);\s*\}/s',
        'protected function tearDown(): void
    {
        parent::tearDown();
    }',
        $content
    );

    // Remove any remaining error handler calls in test methods
    $content = preg_replace('/@set_error_handler\(null\);\s*/', '', $content);
    $content = preg_replace('/@set_exception_handler\(null\);\s*/', '', $content);
    $content = preg_replace('/set_error_handler\(null\);\s*/', '', $content);
    $content = preg_replace('/set_exception_handler\(null\);\s*/', '', $content);

    // Clean up any orphaned code
    $content = preg_replace('/\s*if \(\$this->originalErrorHandler !== null\) \{\s*set_error_handler\(\$this->originalErrorHandler\);\s*\}\s*/', '', $content);
    $content = preg_replace('/\s*if \(\$this->originalExceptionHandler !== null\) \{\s*set_exception_handler\(\$this->originalExceptionHandler\);\s*\}\s*/', '', $content);

    // Fix any double closing braces
    $content = preg_replace('/\}\s*\}\s*protected function tearDown/', '    }

    protected function tearDown', $content);

    file_put_contents($file, $content);
    echo "Fixed: $file\n";
}

echo "All architecture test files have been fixed.\n";
