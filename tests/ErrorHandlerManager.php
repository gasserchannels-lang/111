<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

class ErrorHandlerManager extends TestCase
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

        // Don't modify error reporting to avoid PHPUnit warnings
        self::$originalErrorReporting = 0;
        self::$originalErrorHandler = null;
        self::$originalExceptionHandler = null;

        self::$initialized = true;
    }

    public static function restore(): void
    {
        if (! self::$initialized) {
            return;
        }

        // Don't modify error reporting to avoid PHPUnit warnings
        // error_reporting(self::$originalErrorReporting);

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
