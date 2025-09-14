<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/**
 * Mockery Debug Test
 *
 * This test verifies that Mockery is working correctly and there are no conflicts
 * with Laravel's Console Output or other components.
 */
class MockeryDebugTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test basic Mockery functionality
     *
     * @return void
     */
    public function test_basic_mockery_functionality()
    {
        // Test basic mock creation
        $mock = $this->mock('stdClass');
        $mock->shouldReceive('testMethod')
            ->once()
            ->andReturn('test result');

        $result = $mock->testMethod();
        $this->assertEquals('test result', $result);

        // Verify expectations were met
        $this->assertMockeryExpectations();
    }

    /**
     * Test Mockery with Laravel service
     *
     * @return void
     */
    public function test_mockery_with_laravel_service()
    {
        // Mock a simple service
        $service = $this->mock('App\Services\TestService');
        $service->shouldReceive('process')
            ->once()
            ->with('test data')
            ->andReturn('processed data');

        $result = $service->process('test data');
        $this->assertEquals('processed data', $result);

        $this->assertMockeryExpectations();
    }

    /**
     * Test Mockery spy functionality
     *
     * @return void
     */
    public function test_mockery_spy_functionality()
    {
        $spy = $this->spy('stdClass');
        $spy->testMethod('test');

        $spy->shouldHaveReceived('testMethod')
            ->once()
            ->with('test');
    }

    /**
     * Test Mockery partial mock
     *
     * @return void
     */
    public function test_mockery_partial_mock()
    {
        $object = new \stdClass;
        $object->realMethod = function () {
            return 'real result';
        };
        $object->mockMethod = function () {
            return 'original result';
        };

        $partialMock = $this->partialMock($object);
        $partialMock->shouldReceive('mockMethod')
            ->once()
            ->andReturn('mocked result');

        $this->assertEquals('mocked result', $partialMock->mockMethod());
        $this->assertMockeryExpectations();
    }

    /**
     * Test Mockery with multiple expectations
     *
     * @return void
     */
    public function test_mockery_multiple_expectations()
    {
        $mock = $this->mock('stdClass');

        $mock->shouldReceive('method1')
            ->once()
            ->andReturn('result1');

        $mock->shouldReceive('method2')
            ->twice()
            ->andReturn('result2');

        $this->assertEquals('result1', $mock->method1());
        $this->assertEquals('result2', $mock->method2());
        $this->assertEquals('result2', $mock->method2());

        $this->assertMockeryExpectations();
    }

    /**
     * Test Mockery cleanup between tests
     *
     * @return void
     */
    public function test_mockery_cleanup_between_tests()
    {
        // This test should not be affected by previous tests
        $mock = $this->mock('stdClass');
        $mock->shouldReceive('cleanupTest')
            ->once()
            ->andReturn('cleanup successful');

        $result = $mock->cleanupTest();
        $this->assertEquals('cleanup successful', $result);

        $this->assertMockeryExpectations();
    }

    /**
     * Test Mockery with Laravel Console Output (potential conflict source)
     *
     * @return void
     */
    public function test_mockery_with_console_output()
    {
        // Test mocking a console command
        $command = $this->mock('Illuminate\Console\Command');
        $command->shouldReceive('info')
            ->once()
            ->with('Test message');

        $command->info('Test message');

        $this->assertMockeryExpectations();
    }

    /**
     * Test Mockery with Laravel events
     *
     * @return void
     */
    public function test_mockery_with_laravel_events()
    {
        $event = $this->mock('Illuminate\Events\Dispatcher');
        $event->shouldReceive('dispatch')
            ->once()
            ->with('test.event', Mockery::type('array'));

        $event->dispatch('test.event', ['data' => 'test']);

        $this->assertMockeryExpectations();
    }

    /**
     * Test Mockery error handling
     *
     * @return void
     */
    public function test_mockery_error_handling()
    {
        $mock = $this->mock('stdClass');
        $mock->shouldReceive('errorMethod')
            ->once()
            ->andThrow(new \Exception('Test error'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test error');

        $mock->errorMethod();
    }

    /**
     * Test Mockery with database operations
     *
     * @return void
     */
    public function test_mockery_with_database_operations()
    {
        // Mock a model
        $model = $this->mock('App\Models\User');
        $model->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn((object) ['id' => 1, 'name' => 'Test User']);

        $user = $model->find(1);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('Test User', $user->name);

        $this->assertMockeryExpectations();
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
        $mock1 = $this->mock('stdClass');
        $mock2 = $this->mock('stdClass');

        $mock1->shouldReceive('method1')->once()->andReturn('mock1');
        $mock2->shouldReceive('method2')->once()->andReturn('mock2');

        $this->assertEquals('mock1', $mock1->method1());
        $this->assertEquals('mock2', $mock2->method2());

        $this->assertMockeryExpectations();
    }
}
