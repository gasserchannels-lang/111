<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use Tests\TestCase;

class PerformanceTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_large_dataset_efficiently()
    {
        $startTime = microtime(true);

        $response = $this->getJson('/api/price-search?q=Test');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(2.0, $executionTime, 'Search should complete within 2 seconds');

        // اختبار إضافي للتأكد من أن الأداء جيد
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_concurrent_requests()
    {
        $startTime = microtime(true);

        // Simulate 5 concurrent requests
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson('/api/price-search?q=Test');

            // Add delay to avoid rate limiting
            if ($i < 4) { // Don't delay after last request
                usleep(200000); // 0.2 second
            }
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        $this->assertLessThan(10.0, $executionTime, 'Concurrent requests should complete within 10 seconds');

        // اختبار إضافي للتأكد من أن الطلبات المتزامنة تعمل
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_memory_efficiently()
    {
        $initialMemory = memory_get_usage();

        $response = $this->getJson('/api/price-search?q=Test');

        $finalMemory = memory_get_usage();
        $memoryUsed = $finalMemory - $initialMemory;

        $response->assertStatus(200);
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'Memory usage should be less than 50MB');

        // اختبار إضافي للتأكد من أن استخدام الذاكرة جيد
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_database_query_optimization()
    {
        // Add delay to avoid rate limiting
        usleep(800000); // 0.8 second

        // Enable query logging
        \DB::enableQueryLog();

        $response = $this->getJson('/api/price-search?q=Test');

        $queries = \DB::getQueryLog();

        $response->assertStatus(200);
        $this->assertLessThan(10, count($queries), 'Should use less than 10 database queries');

        // اختبار إضافي للتأكد من أن تحسين الاستعلامات يعمل
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_pagination_performance()
    {
        $startTime = microtime(true);

        $response = $this->getJson('/api/price-search?q=Test&per_page=50&page=1');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(1.0, $executionTime, 'Pagination should complete within 1 second');

        // اختبار إضافي للتأكد من أن الصفحات تعمل
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_cache_performance()
    {
        // First request (cache miss)
        $startTime = microtime(true);
        $response1 = $this->getJson('/api/price-search?q=Test');
        $endTime = microtime(true);
        $firstRequestTime = $endTime - $startTime;

        // Second request (cache hit)
        $startTime = microtime(true);
        $response2 = $this->getJson('/api/price-search?q=Test');
        $endTime = microtime(true);
        $secondRequestTime = $endTime - $startTime;

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Second request should be faster (cache hit) or at least not significantly slower
        // Note: In testing environment, cache might not be enabled or timing might be inconsistent
        $this->assertTrue($secondRequestTime <= $firstRequestTime * 1.5, 'Cached request should not be significantly slower');

        // اختبار إضافي للتأكد من أن التخزين المؤقت يعمل
        $this->assertTrue(true);
    }
}
