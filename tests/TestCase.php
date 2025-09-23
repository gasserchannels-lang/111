<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseSetup;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    /**
     * Tear down the test environment.
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
}
