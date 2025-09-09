<?php

namespace Tests\Unit\Services;

use App\Services\ProcessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_process_service_instance()
    {
        $service = new ProcessService();

        $this->assertInstanceOf(ProcessService::class, $service);
    }

    /** @test */
    public function it_can_process_data()
    {
        $service = new ProcessService();
        $data = ['test' => 'data'];

        $result = $service->process($data);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('processed', $result);
        $this->assertTrue($result['processed']);
    }

    /** @test */
    public function it_can_validate_data()
    {
        $service = new ProcessService();
        $validData = ['name' => 'Test', 'email' => 'test@example.com'];

        $result = $service->validate($validData);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_validate_invalid_data()
    {
        $service = new ProcessService();
        $invalidData = ['name' => '', 'email' => 'invalid-email'];

        $result = $service->validate($invalidData);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_get_validation_errors()
    {
        $service = new ProcessService();
        $invalidData = ['name' => '', 'email' => 'invalid-email'];

        $service->validate($invalidData);
        $errors = $service->getErrors();

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
    }

    /** @test */
    public function it_can_clean_data()
    {
        $service = new ProcessService();
        $dirtyData = ['name' => '  Test  ', 'email' => 'TEST@EXAMPLE.COM'];

        $result = $service->clean($dirtyData);

        $this->assertEquals('Test', $result['name']);
        $this->assertEquals('test@example.com', $result['email']);
    }

    /** @test */
    public function it_can_transform_data()
    {
        $service = new ProcessService();
        $data = ['name' => 'test', 'value' => 100];

        $result = $service->transform($data);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('value', $result);
    }

    /** @test */
    public function it_can_handle_processing_errors()
    {
        $service = new ProcessService();
        $invalidData = null;

        $result = $service->process($invalidData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertTrue($result['error']);
    }

    /** @test */
    public function it_can_get_processing_status()
    {
        $service = new ProcessService();

        $status = $service->getStatus();

        $this->assertIsString($status);
        $this->assertNotEmpty($status);
    }

    /** @test */
    public function it_can_reset_service()
    {
        $service = new ProcessService();
        $service->process(['test' => 'data']);

        $service->reset();

        $this->assertEmpty($service->getErrors());
    }

    /** @test */
    public function it_can_get_processing_metrics()
    {
        $service = new ProcessService();
        $service->process(['test' => 'data']);

        $metrics = $service->getMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('processed_count', $metrics);
        $this->assertArrayHasKey('error_count', $metrics);
    }
}
