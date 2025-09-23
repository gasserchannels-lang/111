<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Minimal test base class that avoids Laravel bootstrap to prevent risky test warnings
 */
abstract class MinimalTestBase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
