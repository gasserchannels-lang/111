<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Force migrations for testing environment
        if (app()->environment('testing')) {
            $this->artisan('migrate:fresh', ['--env' => 'testing']);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
