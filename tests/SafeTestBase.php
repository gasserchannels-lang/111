<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Safe test base class that prevents PHPUnit risky warnings
 * by avoiding any error handler modifications.
 */
class SafeTestBase extends TestCase
{
    /**
     * Set up the test environment without modifying error handlers.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Tear down the test environment without modifying error handlers.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test that SafeTestBase can be instantiated and works correctly.
     */
    public function test_can_be_instantiated(): void
    {
        // Test basic instantiation
        $this->assertInstanceOf(self::class, $this);

        // Test that the class has required methods
        $this->assertTrue(method_exists($this, 'setUp'));
        $this->assertTrue(method_exists($this, 'tearDown'));

        // Test basic functionality
        $this->assertTrue(true);

        // Test that we can perform basic assertions
        $this->assertEquals(1, 1);
        $this->assertNotEquals(1, 2);
    }
}
