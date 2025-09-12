<?php

namespace Tests\Performance;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function homepage_load_time_benchmark()
    {
        $startTime = microtime(true);

        $response = $this->get('/');

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertEquals(200, $response->status());
        $this->assertLessThan(2000, $loadTime); // أقل من ثانيتين

        \Log::info("Homepage load time: {$loadTime}ms");

        // تقييم الأداء
        if ($loadTime < 500) {
            \Log::info('Homepage performance: EXCELLENT');
        } elseif ($loadTime < 1000) {
            \Log::info('Homepage performance: GOOD');
        } elseif ($loadTime < 2000) {
            \Log::info('Homepage performance: ACCEPTABLE');
        } else {
            \Log::info('Homepage performance: NEEDS IMPROVEMENT');
        }
    }

    /** @test */
    public function database_query_performance_benchmark()
    {
        // إنشاء بيانات اختبار
        Product::factory()->count(1000)->create();
        Category::factory()->count(10)->create();
        Brand::factory()->count(20)->create();

        $queries = [
            'simple_select' => function () {
                return Product::where('is_active', true)->get();
            },
            'complex_join' => function () {
                return Product::with(['category', 'brand', 'reviews'])
                    ->where('price', '>', 100)
                    ->where('is_active', true)
                    ->orderBy('created_at', 'desc')
                    ->get();
            },
            'aggregate_query' => function () {
                return Product::selectRaw('category_id, COUNT(*) as count, AVG(price) as avg_price')
                    ->groupBy('category_id')
                    ->get();
            },
            'pagination_query' => function () {
                return Product::with(['category', 'brand'])
                    ->paginate(20);
            },
        ];

        foreach ($queries as $name => $query) {
            $startTime = microtime(true);
            $result = $query();
            $endTime = microtime(true);
            $queryTime = ($endTime - $startTime) * 1000;

            \Log::info("Query '{$name}' time: {$queryTime}ms");

            // تقييم الأداء
            if ($queryTime < 100) {
                \Log::info("Query '{$name}' performance: EXCELLENT");
            } elseif ($queryTime < 500) {
                \Log::info("Query '{$name}' performance: GOOD");
            } elseif ($queryTime < 1000) {
                \Log::info("Query '{$name}' performance: ACCEPTABLE");
            } else {
                \Log::info("Query '{$name}' performance: NEEDS IMPROVEMENT");
            }
        }
    }

    /** @test */
    public function memory_usage_benchmark()
    {
        $initialMemory = memory_get_usage(true);

        // تشغيل عمليات متعددة
        $products = [];
        for ($i = 0; $i < 100; $i++) {
            $product = Product::factory()->create();
            $product->load('category', 'brand', 'reviews');
            $products[] = $product;
        }

        $finalMemory = memory_get_usage(true);
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024; // بالميجابايت

        \Log::info("Memory usage: {$memoryUsed}MB");

        // تقييم استخدام الذاكرة
        if ($memoryUsed < 10) {
            \Log::info('Memory usage: EXCELLENT');
        } elseif ($memoryUsed < 25) {
            \Log::info('Memory usage: GOOD');
        } elseif ($memoryUsed < 50) {
            \Log::info('Memory usage: ACCEPTABLE');
        } else {
            \Log::info('Memory usage: NEEDS IMPROVEMENT');
        }
    }

    /** @test */
    public function concurrent_users_benchmark()
    {
        $concurrentUsers = [5, 10, 20, 50];

        foreach ($concurrentUsers as $userCount) {
            $responses = [];
            $startTime = microtime(true);

            // محاكاة مستخدمين متزامنين
            for ($i = 0; $i < $userCount; $i++) {
                $responses[] = $this->getJson('/api/products');
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;
            $avgTime = $totalTime / $userCount;

            \Log::info("Concurrent users: {$userCount}, Total time: {$totalTime}ms, Avg time: {$avgTime}ms");

            // تقييم الأداء
            if ($avgTime < 100) {
                \Log::info("Concurrent performance with {$userCount} users: EXCELLENT");
            } elseif ($avgTime < 500) {
                \Log::info("Concurrent performance with {$userCount} users: GOOD");
            } elseif ($avgTime < 1000) {
                \Log::info("Concurrent performance with {$userCount} users: ACCEPTABLE");
            } else {
                \Log::info("Concurrent performance with {$userCount} users: NEEDS IMPROVEMENT");
            }
        }
    }

    /** @test */
    public function cache_performance_benchmark()
    {
        Cache::flush();

        // اختبار بدون cache
        $startTime = microtime(true);
        $this->getJson('/api/products');
        $noCacheTime = (microtime(true) - $startTime) * 1000;

        // اختبار مع cache
        $startTime = microtime(true);
        $this->getJson('/api/products');
        $withCacheTime = (microtime(true) - $startTime) * 1000;

        $improvement = (($noCacheTime - $withCacheTime) / $noCacheTime) * 100;

        \Log::info("No cache: {$noCacheTime}ms, With cache: {$withCacheTime}ms, Improvement: {$improvement}%");

        // تقييم Cache
        if ($improvement > 50) {
            \Log::info('Cache performance: EXCELLENT');
        } elseif ($improvement > 25) {
            \Log::info('Cache performance: GOOD');
        } elseif ($improvement > 10) {
            \Log::info('Cache performance: ACCEPTABLE');
        } else {
            \Log::info('Cache performance: NEEDS IMPROVEMENT');
        }
    }

    /** @test */
    public function api_response_time_benchmark()
    {
        $endpoints = [
            '/api/products',
            '/api/categories',
            '/api/brands',
            '/api/search?q=test',
            '/api/products/1',
            '/api/categories/1/products',
        ];

        foreach ($endpoints as $endpoint) {
            $startTime = microtime(true);
            $response = $this->getJson($endpoint);
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            \Log::info("API {$endpoint} response time: {$responseTime}ms");

            // تقييم الأداء
            if ($responseTime < 100) {
                \Log::info("API {$endpoint} performance: EXCELLENT");
            } elseif ($responseTime < 500) {
                \Log::info("API {$endpoint} performance: GOOD");
            } elseif ($responseTime < 1000) {
                \Log::info("API {$endpoint} performance: ACCEPTABLE");
            } else {
                \Log::info("API {$endpoint} performance: NEEDS IMPROVEMENT");
            }
        }
    }

    /** @test */
    public function file_upload_performance_benchmark()
    {
        $fileSizes = [1024, 10240, 102400, 1048576]; // 1KB, 10KB, 100KB, 1MB

        foreach ($fileSizes as $size) {
            $testFile = base64_encode(str_repeat('A', $size));

            $startTime = microtime(true);
            $response = $this->postJson('/api/upload', [
                'file' => $testFile,
                'filename' => 'test.txt',
            ]);
            $endTime = microtime(true);
            $uploadTime = ($endTime - $startTime) * 1000;

            $throughput = ($size / 1024) / ($uploadTime / 1000); // KB/s

            \Log::info("File size: {$size} bytes, Upload time: {$uploadTime}ms, Throughput: {$throughput} KB/s");
        }
    }

    /** @test */
    public function database_connection_pool_benchmark()
    {
        $connections = [];
        $startTime = microtime(true);

        // إنشاء اتصالات متعددة
        for ($i = 0; $i < 50; $i++) {
            $connections[] = DB::connection()->getPdo();
        }

        $endTime = microtime(true);
        $connectionTime = ($endTime - $startTime) * 1000;
        $avgConnectionTime = $connectionTime / 50;

        \Log::info("Database connection pool: {$connectionTime}ms total, {$avgConnectionTime}ms average");

        // تقييم الأداء
        if ($avgConnectionTime < 10) {
            \Log::info('Database connection performance: EXCELLENT');
        } elseif ($avgConnectionTime < 50) {
            \Log::info('Database connection performance: GOOD');
        } elseif ($avgConnectionTime < 100) {
            \Log::info('Database connection performance: ACCEPTABLE');
        } else {
            \Log::info('Database connection performance: NEEDS IMPROVEMENT');
        }
    }

    /** @test */
    public function overall_performance_score()
    {
        $scores = [];

        // قياس الأداء
        $homepageTime = $this->measureHomepageLoadTime();
        $apiTime = $this->measureApiResponseTime();
        $dbTime = $this->measureDatabaseQueryTime();
        $memoryUsage = $this->measureMemoryUsage();

        // حساب النقاط
        $scores['homepage'] = $this->calculateScore($homepageTime, [500, 1000, 2000]);
        $scores['api'] = $this->calculateScore($apiTime, [100, 500, 1000]);
        $scores['database'] = $this->calculateScore($dbTime, [100, 500, 1000]);
        $scores['memory'] = $this->calculateScore($memoryUsage, [10, 25, 50]);

        $overallScore = array_sum($scores) / count($scores);

        \Log::info('Performance Scores:');
        \Log::info("Homepage: {$scores['homepage']}/100");
        \Log::info("API: {$scores['api']}/100");
        \Log::info("Database: {$scores['database']}/100");
        \Log::info("Memory: {$scores['memory']}/100");
        \Log::info("Overall Score: {$overallScore}/100");

        if ($overallScore >= 80) {
            \Log::info('Overall Performance: EXCELLENT');
        } elseif ($overallScore >= 60) {
            \Log::info('Overall Performance: GOOD');
        } elseif ($overallScore >= 40) {
            \Log::info('Overall Performance: ACCEPTABLE');
        } else {
            \Log::info('Overall Performance: NEEDS IMPROVEMENT');
        }
    }

    private function measureHomepageLoadTime()
    {
        $startTime = microtime(true);
        $this->get('/');

        return (microtime(true) - $startTime) * 1000;
    }

    private function measureApiResponseTime()
    {
        $startTime = microtime(true);
        $this->getJson('/api/products');

        return (microtime(true) - $startTime) * 1000;
    }

    private function measureDatabaseQueryTime()
    {
        $startTime = microtime(true);
        Product::with(['category', 'brand'])->get();

        return (microtime(true) - $startTime) * 1000;
    }

    private function measureMemoryUsage()
    {
        $initialMemory = memory_get_usage(true);
        Product::factory()->count(100)->create();
        $finalMemory = memory_get_usage(true);

        return ($finalMemory - $initialMemory) / 1024 / 1024;
    }

    private function calculateScore($value, $thresholds)
    {
        if ($value <= $thresholds[0]) {
            return 100;
        }
        if ($value <= $thresholds[1]) {
            return 80;
        }
        if ($value <= $thresholds[2]) {
            return 60;
        }

        return 40;
    }
}
