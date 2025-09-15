<?php

namespace Tests\Performance;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdvancedPerformanceTest extends TestCase
{
    

    #[Test]
    public function page_load_speed_is_acceptable()
    {
        $startTime = microtime(true);

        $response = $this->get('/');

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000; // بالميلي ثانية

        $this->assertEquals(200, $response->status());
        $this->assertLessThan(2000, $loadTime); // أقل من ثانيتين

        \Log::info("Homepage load time: {$loadTime}ms");
    }

    #[Test]
    public function database_query_performance()
    {
        // إنشاء بيانات اختبار
        $brands = Brand::factory()->count(10)->create();
        $categories = Category::factory()->count(10)->create();
        $stores = Store::factory()->count(10)->create();

        for ($i = 0; $i < 1000; $i++) {
            Product::factory()->create([
                'brand_id' => $brands->random()->id,
                'category_id' => $categories->random()->id,
            ]);
        }

        $startTime = microtime(true);

        // استعلام معقد
        $products = Product::with(['category', 'brand', 'reviews'])
            ->where('price', '>', 100)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(500, $queryTime); // أقل من 500ms
        $this->assertCount(20, $products->items());

        \Log::info("Complex query time: {$queryTime}ms");
    }

    #[Test]
    public function memory_usage_is_reasonable()
    {
        $initialMemory = memory_get_usage(true);

        // تشغيل عمليات متعددة
        $brands = Brand::factory()->count(10)->create();
        $categories = Category::factory()->count(10)->create();

        for ($i = 0; $i < 100; $i++) {
            $product = Product::factory()->create([
                'brand_id' => $brands->random()->id,
                'category_id' => $categories->random()->id,
            ]);
            $product->load('category', 'brand');
        }

        $finalMemory = memory_get_usage(true);
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024; // بالميجابايت

        $this->assertLessThan(50, $memoryUsed); // أقل من 50MB

        \Log::info("Memory usage: {$memoryUsed}MB");
    }

    #[Test]
    public function concurrent_users_handling()
    {
        $responses = [];
        $startTime = microtime(true);

        // محاكاة 10 مستخدمين متزامنين
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->get('/api/products');
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;

        // جميع الطلبات يجب أن تنجح
        foreach ($responses as $response) {
            $this->assertContains($response->status(), [200, 429]);
        }

        $this->assertLessThan(5000, $totalTime); // أقل من 5 ثوان

        \Log::info("Concurrent users test time: {$totalTime}ms");
    }

    #[Test]
    public function cache_performance()
    {
        // اختبار بدون cache
        Cache::flush();
        $startTime = microtime(true);

        $this->get('/api/products');

        $noCacheTime = (microtime(true) - $startTime) * 1000;

        // اختبار مع cache
        $startTime = microtime(true);

        $this->get('/api/products');

        $withCacheTime = (microtime(true) - $startTime) * 1000;

        // Cache يجب أن يكون أسرع
        $this->assertLessThan($noCacheTime, $withCacheTime);

        \Log::info("No cache: {$noCacheTime}ms, With cache: {$withCacheTime}ms");
    }

    #[Test]
    public function api_response_time()
    {
        $endpoints = [
            '/api/products',
            '/api/categories',
            '/api/brands',
            '/api/search?q=test',
        ];

        foreach ($endpoints as $endpoint) {
            $startTime = microtime(true);

            $response = $this->get($endpoint);

            $responseTime = (microtime(true) - $startTime) * 1000;

            $this->assertTrue(in_array($response->status(), [200, 404, 422]));
            $this->assertLessThan(1000, $responseTime); // أقل من ثانية

            \Log::info("API {$endpoint} response time: {$responseTime}ms");
        }
    }

    #[Test]
    public function database_connection_pooling()
    {
        $connections = [];
        $startTime = microtime(true);

        // إنشاء عدة اتصالات متزامنة
        for ($i = 0; $i < 20; $i++) {
            $connections[] = DB::connection()->getPdo();
        }

        $endTime = microtime(true);
        $connectionTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(1000, $connectionTime); // أقل من ثانية

        \Log::info("Database connection time: {$connectionTime}ms");
    }

    #[Test]
    public function file_upload_performance()
    {
        $testFile = base64_encode('Test file content for performance testing');

        $startTime = microtime(true);

        $response = $this->postJson('/api/upload', [
            'file' => $testFile,
            'filename' => 'test.txt',
        ]);

        $uploadTime = (microtime(true) - $startTime) * 1000;

        $this->assertLessThan(2000, $uploadTime); // أقل من ثانيتين

        \Log::info("File upload time: {$uploadTime}ms");
    }
}
