<?php

namespace Tests\Unit;

use Mockery;
use Tests\TestCase;

/**
 * Simple Mockery Test
 *
 * This test focuses on basic Mockery functionality without complex Laravel integrations
 * to verify that Mockery is working correctly.
 */
class SimpleMockeryTest extends TestCase
{
    /**
     * Test basic Mockery functionality
     *
     * @return void
     */
    public function test_basic_mockery_works()
    {
        // Test basic mock creation
        $mock = Mockery::mock('stdClass');
        $mock->shouldReceive('testMethod')
            ->once()
            ->andReturn('test result');

        $result = $mock->testMethod();
        $this->assertEquals('test result', $result);
    }

    /**
     * Test Mockery with simple class
     *
     * @return void
     */
    public function test_mockery_with_simple_class()
    {
        // Create a simple test class
        $testClass = new class
        {
            public function getName()
            {
                return 'Original Name';
            }
        };

        // Mock the class
        $mock = Mockery::mock($testClass);
        $mock->shouldReceive('getName')
            ->once()
            ->andReturn('Mocked Name');

        $result = $mock->getName();
        $this->assertEquals('Mocked Name', $result);
    }

    /**
     * Test Mockery spy functionality
     *
     * @return void
     */
    public function test_mockery_spy_works()
    {
        $spy = Mockery::spy('stdClass');
        $spy->testMethod('test');

        $spy->shouldHaveReceived('testMethod')
            ->once()
            ->with('test');
    }

    /**
     * Test Mockery with multiple expectations
     *
     * @return void
     */
    public function test_mockery_multiple_expectations()
    {
        $mock = Mockery::mock('stdClass');

        $mock->shouldReceive('method1')
            ->once()
            ->andReturn('result1');

        $mock->shouldReceive('method2')
            ->twice()
            ->andReturn('result2');

        $this->assertEquals('result1', $mock->method1());
        $this->assertEquals('result2', $mock->method2());
        $this->assertEquals('result2', $mock->method2());
    }

    /**
     * Test Mockery cleanup between tests
     *
     * @return void
     */
    public function test_mockery_cleanup_works()
    {
        // This test should not be affected by previous tests
        $mock = Mockery::mock('stdClass');
        $mock->shouldReceive('cleanupTest')
            ->once()
            ->andReturn('cleanup successful');

        $result = $mock->cleanupTest();
        $this->assertEquals('cleanup successful', $result);
    }

    /**
     * Test Mockery error handling
     *
     * @return void
     */
    public function test_mockery_error_handling()
    {
        $mock = Mockery::mock('stdClass');
        $mock->shouldReceive('errorMethod')
            ->once()
            ->andThrow(new \Exception('Test error'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test error');

        $mock->errorMethod();
    }

    /**
     * Test Mockery container isolation
     *
     * @return void
     */
    public function test_mockery_container_isolation()
    {
        // Test that Mockery container is properly isolated
        $container = Mockery::getContainer();
        $this->assertNotNull($container);

        // Test that we can create multiple mocks without conflicts
        $mock1 = Mockery::mock('stdClass');
        $mock2 = Mockery::mock('stdClass');

        $mock1->shouldReceive('method1')->once()->andReturn('mock1');
        $mock2->shouldReceive('method2')->once()->andReturn('mock2');

        $this->assertEquals('mock1', $mock1->method1());
        $this->assertEquals('mock2', $mock2->method2());
    }
}
