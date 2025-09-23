<?php

/**
 * Comprehensive script to fix all test files by replacing TestCase with BaseTest
 */

// Get all test files that extend TestCase
$testFiles = glob('tests/Unit/**/*Test.php');

$fixedCount = 0;
$errorCount = 0;

foreach ($testFiles as $file) {
    if (! file_exists($file)) {
        echo "File not found: $file\n";

        continue;
    }

    $content = file_get_contents($file);
    $originalContent = $content;

    // Skip if already using BaseTest
    if (strpos($content, 'use Tests\Unit\BaseTest;') !== false) {
        continue;
    }

    // Replace the class declaration
    $content = preg_replace('/use Tests\\\\TestCase;/', 'use Tests\Unit\BaseTest;', $content);
    $content = preg_replace('/extends TestCase/', 'extends BaseTest', $content);

    // Remove all error handler related properties
    $content = preg_replace('/\s*\/\*\* @var callable\|\|null \*\/\s*protected \$originalErrorHandler;\s*/', '', $content);
    $content = preg_replace('/\s*\/\*\* @var \\\\Illuminate\\\\Contracts\\\\Debug\\\\ExceptionHandler\|\|null \*\/\s*protected \$originalExceptionHandler;\s*/', '', $content);
    $content = preg_replace('/\s*\/\*\* @var callable\|\|null \*\/\s*private \$originalErrorHandler;\s*/', '', $content);
    $content = preg_replace('/\s*\/\*\* @var \\\\Illuminate\\\\Contracts\\\\Debug\\\\ExceptionHandler\|\|null \*\/\s*private \$originalExceptionHandler;\s*/', '', $content);
    $content = preg_replace('/\s*protected \$originalErrorHandler;\s*/', '', $content);
    $content = preg_replace('/\s*protected \$originalExceptionHandler;\s*/', '', $content);
    $content = preg_replace('/\s*private \$originalErrorHandler;\s*/', '', $content);
    $content = preg_replace('/\s*private \$originalExceptionHandler;\s*/', '', $content);

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

    // Remove orphaned error handler code
    $content = preg_replace('/\s*throw new \\\\ErrorException\(\$message, 0, \$severity, \$file, \$line\);\s*\}\);\s*/', '', $content);
    $content = preg_replace('/\s*\$this->setTestExceptionHandler\(function\(\$exception\) \{\s*throw \$exception;\s*\}\);\s*/', '', $content);
    $content = preg_replace('/\s*set_error_handler\(function\(\$severity, \$message, \$file, \$line\) \{\s*.*?\}\);\s*/', '', $content);
    $content = preg_replace('/\s*set_exception_handler\(function\(\$exception\) \{\s*.*?\}\);\s*/', '', $content);

    // Fix any double closing braces
    $content = preg_replace('/\}\s*\}\s*protected function tearDown/', '    }

    protected function tearDown', $content);

    // Only write if content changed
    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            echo "Fixed: $file\n";
            $fixedCount++;
        } else {
            echo "Error writing: $file\n";
            $errorCount++;
        }
    }
}

echo "\nSummary:\n";
echo "Files fixed: $fixedCount\n";
echo "Errors: $errorCount\n";
echo 'Total files processed: '.count($testFiles)."\n";
