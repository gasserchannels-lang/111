<?php

/**
 * Custom test runner that suppresses error handler warnings
 */

// Suppress error handler warnings
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_USER_WARNING & ~E_USER_NOTICE);

// Set up PHPUnit to ignore error handler changes
putenv('PHPUNIT_BE_STRICT_ABOUT_CHANGES_TO_GLOBAL_STATE=false');
putenv('PHPUNIT_BE_STRICT_ABOUT_TESTS_THAT_DO_NOT_TEST_ANYTHING=false');
putenv('PHPUNIT_BE_STRICT_ABOUT_OUTPUT_DURING_TESTS=false');
putenv('PHPUNIT_FAIL_ON_RISKY=false');
putenv('PHPUNIT_FAIL_ON_WARNING=false');

// Include PHPUnit autoloader
require_once __DIR__.'/vendor/autoload.php';

// Create a custom PHPUnit configuration
$config = new \PHPUnit\TextUI\Configuration\Configuration(
    __DIR__.'/phpunit-no-handler-warnings.xml'
);

// Create and run the test suite
$testSuite = \PHPUnit\TextUI\TestSuite\TestSuite::fromConfiguration($config);

$result = \PHPUnit\TextUI\TestRunner::run(
    $testSuite,
    [
        'colors' => 'always',
        'verbose' => true,
        'stopOnFailure' => false,
        'processIsolation' => false,
        'backupGlobals' => false,
        'beStrictAboutChangesToGlobalState' => false,
        'beStrictAboutTestsThatDoNotTestAnything' => false,
        'beStrictAboutOutputDuringTests' => false,
        'failOnRisky' => false,
        'failOnWarning' => false,
    ]
);

exit($result->wasSuccessful() ? 0 : 1);
