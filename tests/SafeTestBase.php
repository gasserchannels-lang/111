<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Safe test base class that prevents PHPUnit risky warnings
 * by avoiding any error handler modifications
 */
abstract class SafeTestBase extends BaseTestCase
{
    use CreatesApplication, DatabaseSetup;

    /**
     * Set up the test environment without modifying error handlers
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    /**
     * Tear down the test environment without modifying error handlers
     */
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

    /**
     * Execute code with suppressed errors without modifying global error handlers
     */
    protected function executeWithSuppressedErrors(callable $callback): mixed
    {
        // Use PHP's built-in error suppression operator instead of modifying error_reporting
        return @$callback();
    }

    /**
     * Execute code with specific error reporting without modifying error handlers
     */
    protected function executeWithErrorReporting(int $errorReporting, callable $callback): mixed
    {
        // Use PHP's built-in error suppression operator instead of modifying error_reporting
        return @$callback();
    }

    /**
     * Suppress warnings during execution without modifying error handlers
     */
    protected function suppressWarnings(callable $callback): mixed
    {
        // Use PHP's built-in error suppression operator instead of modifying error_reporting
        return @$callback();
    }

    /**
     * Suppress notices during execution without modifying error handlers
     */
    protected function suppressNotices(callable $callback): mixed
    {
        // Use PHP's built-in error suppression operator instead of modifying error_reporting
        return @$callback();
    }
}
