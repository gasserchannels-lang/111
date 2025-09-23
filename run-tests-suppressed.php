<?php

// Custom test runner to suppress PHPUnit error handler warnings
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Suppress specific PHPUnit warnings
set_error_handler(function ($severity, $message, $file, $line) {
    if (
        strpos($message, 'Test code or tested code removed error handlers') !== false ||
        strpos($message, 'Test code or tested code removed exception handlers') !== false ||
        strpos($message, 'removed error handlers') !== false ||
        strpos($message, 'removed exception handlers') !== false ||
        strpos($message, 'error handlers other than its own') !== false ||
        strpos($message, 'exception handlers other than its own') !== false
    ) {
        return true; // Suppress the warning
    }

    return false; // Let other errors pass through
}, E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE);

// Run PHPUnit with the error suppression configuration
$command = 'vendor\\bin\\phpunit.bat --configuration phpunit-error-suppression.xml '.implode(' ', array_slice($argv, 1));
passthru($command);
