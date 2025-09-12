<?php

namespace Tests\Performance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CachePerformanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cache_operations_perform_within_acceptable_time()
    {
        $startTime = microtime(true);

        // Test cache operations
        Cache::put('test_key', 'test_value', 60);
        $value = Cache::get('test_key');
        Cache::forget('test_key');

        $endTime = microtime(true);
        $operationTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(50, $operationTime); // Should complete within 50ms
    }

    /** @test */
    public function cache_remember_improves_performance()
    {
        // First call without cache
        $startTime = microtime(true);
        $result1 = $this->get('/api/expensive-operation');
        $firstCallTime = (microtime(true) - $startTime) * 1000;

        // Second call with cache
        $startTime = microtime(true);
        $result2 = $this->get('/api/expensive-operation');
        $secondCallTime = (microtime(true) - $startTime) * 1000;

        // Cached call should be faster
        $this->assertLessThan($firstCallTime, $secondCallTime);
    }

    /** @test */
    public function cache_handles_high_frequency_operations()
    {
        $startTime = microtime(true);

        // Perform many cache operations
        for ($i = 0; $i < 1000; $i++) {
            Cache::put("key_{$i}", "value_{$i}", 60);
        }

        $endTime = microtime(true);
        $operationTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(1000, $operationTime); // Should complete within 1 second
    }

    /** @test */
    public function cache_retrieval_is_fast()
    {
        // Store data in cache
        Cache::put('test_key', 'test_value', 60);

        $startTime = microtime(true);

        // Retrieve data multiple times
        for ($i = 0; $i < 100; $i++) {
            $value = Cache::get('test_key');
        }

        $endTime = microtime(true);
        $operationTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(100, $operationTime); // Should complete within 100ms
    }

    /** @test */
    public function cache_expiration_works_correctly()
    {
        // Store data with short expiration
        Cache::put('expiring_key', 'expiring_value', 1);

        // Data should be available immediately
        $this->assertEquals('expiring_value', Cache::get('expiring_key'));

        // Wait for expiration
        sleep(2);

        // Data should be expired
        $this->assertNull(Cache::get('expiring_key'));
    }

    /** @test */
    public function cache_clearing_performs_within_acceptable_time()
    {
        // Store multiple cache entries
        for ($i = 0; $i < 100; $i++) {
            Cache::put("key_{$i}", "value_{$i}", 60);
        }

        $startTime = microtime(true);

        // Clear all cache
        Cache::flush();

        $endTime = microtime(true);
        $operationTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(200, $operationTime); // Should complete within 200ms
    }

    /** @test */
    public function cache_handles_concurrent_operations()
    {
        $startTime = microtime(true);

        // Simulate concurrent cache operations
        $operations = [];
        for ($i = 0; $i < 10; $i++) {
            $operations[] = Cache::put("concurrent_key_{$i}", "concurrent_value_{$i}", 60);
        }

        $endTime = microtime(true);
        $operationTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(500, $operationTime); // Should complete within 500ms
    }

    /** @test */
    public function cache_memory_usage_is_efficient()
    {
        $initialMemory = memory_get_usage(true);

        // Store large amount of data in cache
        for ($i = 0; $i < 1000; $i++) {
            Cache::put("large_key_{$i}", str_repeat('x', 1000), 60);
        }

        $finalMemory = memory_get_usage(true);
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024; // Convert to MB

        $this->assertLessThan(50, $memoryUsed); // Should use less than 50MB
    }

    /** @test */
    public function cache_tags_work_efficiently()
    {
        $startTime = microtime(true);

        // Store data with tags
        Cache::tags(['products', 'electronics'])->put('product_1', 'laptop', 60);
        Cache::tags(['products', 'clothing'])->put('product_2', 'shirt', 60);
        Cache::tags(['users'])->put('user_1', 'john', 60);

        // Clear by tag
        Cache::tags(['products'])->flush();

        $endTime = microtime(true);
        $operationTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(100, $operationTime); // Should complete within 100ms

        // Verify tag-based clearing worked
        $this->assertNull(Cache::get('product_1'));
        $this->assertNull(Cache::get('product_2'));
        $this->assertEquals('john', Cache::get('user_1'));
    }

    /** @test */
    public function cache_serialization_performs_well()
    {
        $complexData = [
            'products' => \App\Models\Product::factory()->count(100)->make()->toArray(),
            'users' => \App\Models\User::factory()->count(50)->make()->toArray(),
            'metadata' => [
                'timestamp' => now(),
                'version' => '1.0.0',
                'features' => ['search', 'filter', 'sort'],
            ],
        ];

        $startTime = microtime(true);

        // Store complex data
        Cache::put('complex_data', $complexData, 60);

        // Retrieve complex data
        $retrievedData = Cache::get('complex_data');

        $endTime = microtime(true);
        $operationTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(200, $operationTime); // Should complete within 200ms
        $this->assertEquals($complexData, $retrievedData);
    }

    /** @test */
    public function cache_hit_ratio_is_acceptable()
    {
        $cacheHits = 0;
        $totalRequests = 100;

        // Store some data in cache
        Cache::put('frequent_key', 'frequent_value', 60);

        // Simulate requests
        for ($i = 0; $i < $totalRequests; $i++) {
            $value = Cache::get('frequent_key');
            if ($value !== null) {
                $cacheHits++;
            }
        }

        $hitRatio = $cacheHits / $totalRequests;
        $this->assertGreaterThan(0.9, $hitRatio); // Should have at least 90% hit ratio
    }
}
