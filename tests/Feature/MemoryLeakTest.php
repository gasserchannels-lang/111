<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MemoryLeakTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function memory_usage_does_not_increase_significantly()
    {
        $initialMemory = memory_get_usage(true);

        // Perform multiple operations
        for ($i = 0; $i < 100; $i++) {
            $this->getJson('/api/products');
            $this->getJson('/api/categories');
            $this->getJson('/api/brands');
        }

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory increase should be less than 10MB
        $this->assertLessThan(10 * 1024 * 1024, $memoryIncrease);
    }

    #[Test]
    public function database_queries_do_not_cause_memory_leaks()
    {
        $initialMemory = memory_get_usage(true);

        // Perform database operations
        for ($i = 0; $i < 50; $i++) {
            $this->getJson('/api/products');
            $this->postJson('/api/products', [
                'name' => 'Test Product '.$i,
                'price' => 100 + $i,
            ]);
        }

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory increase should be reasonable
        $this->assertLessThan(5 * 1024 * 1024, $memoryIncrease);
    }

    #[Test]
    public function file_operations_do_not_cause_memory_leaks()
    {
        $initialMemory = memory_get_usage(true);

        // Perform file operations
        for ($i = 0; $i < 20; $i++) {
            $this->postJson('/api/upload', [
                'file' => 'test-file-'.$i.'.txt',
            ]);
        }

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory increase should be reasonable
        $this->assertLessThan(3 * 1024 * 1024, $memoryIncrease);
    }

    #[Test]
    public function cache_operations_do_not_cause_memory_leaks()
    {
        $initialMemory = memory_get_usage(true);

        // Perform cache operations
        for ($i = 0; $i < 100; $i++) {
            $this->getJson('/api/cached-data');
            $this->postJson('/api/cache-data', [
                'key' => 'test-key-'.$i,
                'value' => 'test-value-'.$i,
            ]);
        }

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory increase should be reasonable
        $this->assertLessThan(2 * 1024 * 1024, $memoryIncrease);
    }

    #[Test]
    public function memory_usage_returns_to_normal_after_operations()
    {
        $initialMemory = memory_get_usage(true);

        // Perform heavy operations
        for ($i = 0; $i < 200; $i++) {
            $this->getJson('/api/products');
            $this->postJson('/api/products', [
                'name' => 'Heavy Product '.$i,
                'price' => 1000 + $i,
            ]);
        }

        // Force garbage collection
        gc_collect_cycles();

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory should return close to initial level
        $this->assertLessThan(1 * 1024 * 1024, $memoryIncrease);
    }

    #[Test]
    public function concurrent_requests_do_not_cause_memory_issues()
    {
        $initialMemory = memory_get_usage(true);

        // Simulate concurrent requests
        $responses = [];
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->getJson('/api/products');
        }

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory increase should be reasonable
        $this->assertLessThan(2 * 1024 * 1024, $memoryIncrease);
    }
}
