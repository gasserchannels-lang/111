<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StressTest extends TestCase
{
    

    #[Test]
    public function system_handles_high_concurrent_users()
    {
        $responses = [];
        $startTime = microtime(true);

        // Simulate 50 concurrent users
        for ($i = 0; $i < 50; $i++) {
            $responses[] = $this->getJson('/api/products');
        }

        $endTime = microtime(true);
        $responseTime = $endTime - $startTime;

        // All requests should succeed
        foreach ($responses as $response) {
            $this->assertNotEquals(500, $response->status());
        }

        // Response time should be reasonable (less than 10 seconds)
        $this->assertLessThan(10, $responseTime);
    }

    #[Test]
    public function system_handles_high_request_volume()
    {
        $successfulRequests = 0;
        $startTime = microtime(true);

        // Send 200 requests in quick succession
        for ($i = 0; $i < 200; $i++) {
            $response = $this->getJson('/api/products');
            if ($response->status() !== 500) {
                $successfulRequests++;
            }
        }

        $endTime = microtime(true);
        $responseTime = $endTime - $startTime;

        // At least 90% of requests should succeed
        $successRate = $successfulRequests / 200;
        $this->assertGreaterThan(0.9, $successRate);

        // Response time should be reasonable
        $this->assertLessThan(30, $responseTime);
    }

    #[Test]
    public function system_handles_mixed_request_types()
    {
        $responses = [];

        // Mix of GET, POST, PUT, DELETE requests
        for ($i = 0; $i < 20; $i++) {
            $responses[] = $this->getJson('/api/products');
            $responses[] = $this->postJson('/api/products', [
                'name' => 'Stress Test Product '.$i,
                'price' => 100 + $i,
            ]);
            $responses[] = $this->putJson('/api/products/1', [
                'name' => 'Updated Product '.$i,
            ]);
            $responses[] = $this->deleteJson('/api/products/1');
        }

        // All requests should not return 500 errors
        foreach ($responses as $response) {
            $this->assertNotEquals(500, $response->status());
        }
    }

    #[Test]
    public function system_handles_database_load()
    {
        $startTime = microtime(true);

        // Perform heavy database operations
        for ($i = 0; $i < 100; $i++) {
            $this->getJson('/api/products?search=test&page='.$i);
            $this->getJson('/api/categories?with=products');
            $this->getJson('/api/brands?with=products');
        }

        $endTime = microtime(true);
        $responseTime = $endTime - $startTime;

        // Response time should be reasonable
        $this->assertLessThan(60, $responseTime);
    }

    #[Test]
    public function system_handles_file_upload_load()
    {
        $responses = [];

        // Simulate multiple file uploads
        for ($i = 0; $i < 20; $i++) {
            $responses[] = $this->postJson('/api/upload', [
                'file' => 'stress-test-file-'.$i.'.txt',
            ]);
        }

        // Most requests should succeed
        $successfulUploads = 0;
        foreach ($responses as $response) {
            if ($response->status() !== 500) {
                $successfulUploads++;
            }
        }

        $this->assertGreaterThan(15, $successfulUploads);
    }

    #[Test]
    public function system_handles_memory_pressure()
    {
        $initialMemory = memory_get_usage(true);

        // Perform memory-intensive operations
        for ($i = 0; $i < 50; $i++) {
            $this->getJson('/api/products?with=all');
            $this->postJson('/api/products', [
                'name' => 'Memory Test Product '.$i,
                'description' => str_repeat('A', 1000),
                'price' => 100 + $i,
            ]);
        }

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory increase should be reasonable (less than 50MB)
        $this->assertLessThan(50 * 1024 * 1024, $memoryIncrease);
    }

    #[Test]
    public function system_handles_error_conditions_under_load()
    {
        $responses = [];

        // Send requests that might cause errors
        for ($i = 0; $i < 50; $i++) {
            $responses[] = $this->getJson('/api/products/'.$i);
            $responses[] = $this->postJson('/api/products', []);
            $responses[] = $this->putJson('/api/products/'.$i, []);
        }

        // System should handle errors gracefully
        $errorCount = 0;
        foreach ($responses as $response) {
            if ($response->status() >= 500) {
                $errorCount++;
            }
        }

        // Error rate should be low (less than 20%)
        $errorRate = $errorCount / count($responses);
        $this->assertLessThan(0.2, $errorRate);
    }

    #[Test]
    public function system_handles_authentication_load()
    {
        $responses = [];

        // Simulate multiple authentication attempts
        for ($i = 0; $i < 30; $i++) {
            $responses[] = $this->postJson('/api/login', [
                'email' => 'test'.$i.'@example.com',
                'password' => 'password',
            ]);
        }

        // System should handle authentication load
        $successfulAuths = 0;
        foreach ($responses as $response) {
            if ($response->status() !== 500) {
                $successfulAuths++;
            }
        }

        $this->assertGreaterThan(25, $successfulAuths);
    }
}
