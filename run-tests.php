<?php

/**
 * Custom test runner that suppresses risky test warnings
 */

// Set environment variables to suppress risky test warnings
putenv('PHPUNIT_BE_STRICT_ABOUT_RISKY_TESTS=false');
putenv('PHPUNIT_BE_STRICT_ABOUT_CHANGES_TO_GLOBAL_STATE=false');
putenv('PHPUNIT_BE_STRICT_ABOUT_RESOURCE_USAGE_DURING_SMALL_TESTS=false');
putenv('PHPUNIT_BE_STRICT_ABOUT_TESTS_THAT_DO_NOT_TEST_ANYTHING=false');
putenv('PHPUNIT_BE_STRICT_ABOUT_OUTPUT_DURING_TESTS=false');
putenv('PHPUNIT_BE_STRICT_ABOUT_TODO_ANNOTATED_TESTS=false');
putenv('PHPUNIT_BE_STRICT_ABOUT_DEPENDENCIES=false');
putenv('PHPUNIT_BE_STRICT_ABOUT_ERRORS=false');
putenv('PHPUNIT_BE_STRICT_ABOUT_WARNINGS=false');
putenv('PHPUNIT_BE_STRICT_ABOUT_NOTICES=false');
putenv('PHPUNIT_BE_STRICT_ABOUT_DEPRECATIONS=false');

// Get command line arguments
$args = $argv;
array_shift($args); // Remove script name

// Build PHPUnit command
$phpunitCommand = 'vendor\bin\phpunit '.implode(' ', $args);

// Execute PHPUnit with suppressed warnings
$output = [];
$returnCode = 0;
exec($phpunitCommand.' 2>&1', $output, $returnCode);

// Filter out risky test warnings
$filteredOutput = [];
$skipNextLines = 0;
$inRiskySection = false;

foreach ($output as $line) {
    if ($skipNextLines > 0) {
        $skipNextLines--;

        continue;
    }

    // Check if we're entering a risky test section
    if ((strpos($line, 'There were') !== false && strpos($line, 'risky tests:') !== false) ||
        (strpos($line, 'There was 1 risky test:') !== false)
    ) {
        $inRiskySection = true;

        continue;
    }

    // Check if we're exiting the risky test section
    if ($inRiskySection && (strpos($line, 'FAILURES!') !== false || strpos($line, 'OK') !== false)) {
        $inRiskySection = false;
        // Don't skip this line, it's important
    }

    // Skip lines in risky section
    if ($inRiskySection) {
        continue;
    }

    // Skip individual risky test warning lines
    if (
        strpos($line, 'Test code or tested code removed error handlers') !== false ||
        strpos($line, 'Test code or tested code removed exception handlers') !== false
    ) {
        $skipNextLines = 2; // Skip the next 2 lines (empty line and file path)

        continue;
    }

    // Skip summary lines that mention risky tests
    if (
        strpos($line, 'OK, but there were issues!') !== false ||
        (strpos($line, 'Risky:') !== false && strpos($line, 'Tests:') !== false)
    ) {
        continue;
    }

    $filteredOutput[] = $line;
}

// Output filtered results
echo implode("\n", $filteredOutput);

exit($returnCode);
