<?php

namespace Tests;

/**
 * Safe Laravel test base that preserves error handlers
 * to prevent PHPUnit risky test warnings
 */
abstract class SafeLaravelTest extends TestCase
{
    use \Tests\DatabaseSetup;

    /** @var callable|null */
    private static $safeOriginalErrorHandler;

    /** @var callable|null */
    private static $safeOriginalExceptionHandler;

    private static int $safeOriginalErrorReporting = 0;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Store original handlers before any Laravel bootstrap
        self::$safeOriginalErrorHandler = set_error_handler(null);
        self::$safeOriginalExceptionHandler = set_exception_handler(null);
        self::$safeOriginalErrorReporting = error_reporting();
    }

    public static function tearDownAfterClass(): void
    {
        // Restore original handlers
        if (self::$safeOriginalErrorHandler !== null) {
            set_error_handler(self::$safeOriginalErrorHandler);
        }
        if (self::$safeOriginalExceptionHandler !== null) {
            set_exception_handler(self::$safeOriginalExceptionHandler);
        }
        error_reporting(self::$safeOriginalErrorReporting);

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    protected function tearDown(): void
    {
        // Rollback any remaining transactions
        while (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }

        // Clean up Mockery
        if (class_exists(\Mockery::class)) {
            \Mockery::close();
        }

        parent::tearDown();
    }
}
