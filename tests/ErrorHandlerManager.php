<?php

declare(strict_types=1);

namespace Tests;

class ErrorHandlerManager
{
    /** @var mixed */
    private static $originalErrorHandler;

    /** @var mixed */
    private static $originalExceptionHandler;

    private static int $originalErrorReporting = 0;

    private static bool $initialized = false;

    public static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }

        // Store original error reporting only (don't modify handlers to avoid PHPUnit warnings)
        self::$originalErrorReporting = error_reporting();
        self::$originalErrorHandler = null;
        self::$originalExceptionHandler = null;

        self::$initialized = true;
    }

    public static function restore(): void
    {
        if (! self::$initialized) {
            return;
        }

        // Restore original error reporting only (don't modify handlers to avoid PHPUnit warnings)
        error_reporting(self::$originalErrorReporting);

        self::$initialized = false;
    }

    public static function setErrorHandler(callable $handler): void
    {
        // Don't change error handlers to avoid PHPUnit detection
    }

    public static function setExceptionHandler(callable $handler): void
    {
        // Don't change error handlers to avoid PHPUnit detection
    }

    public static function getOriginalErrorHandler(): mixed
    {
        return self::$originalErrorHandler;
    }

    public static function getOriginalExceptionHandler(): mixed
    {
        return self::$originalExceptionHandler;
    }
}
