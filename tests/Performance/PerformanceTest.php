<?php

declare(strict_types=1);

namespace Tests\Performance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->createTestData();
    }

    /**
     * Test product listing performance with large dataset
     */
    public function test_product_listing_performance(): void
    {
        $startTime = microtime(true);
        
        $response = $this->get('/api/products');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        $response->assertStatus(200);
        
        // Assert response time is under 500ms
        $this->assertLessThan(500, $executionTime, 'Product listing took too long: ' . $executionTime . 'ms');
        
        // Assert memory usage is reasonable
        $memoryUsage = memory_get_usage(true) / 1024 / 1024; // Convert to MB
        $this->assertLessThan(50, $memoryUsage, 'Memory usage too high: ' . $memoryUsage . 'MB');
    }

    /**
     * Test product search performance
     */
    public function test_product_search_performance(): void
    {
        $startTime = microtime(true);
        
        $response = $this->get('/api/products?search=test');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(300, $executionTime, 'Product search took too long: ' . $executionTime . 'ms');
    }

    /**
     * Test database query performance
     */
    public function test_database_query_performance(): void
    {
        DB::enableQueryLog();
        
        $startTime = microtime(true);
        
        $products = Product::with(['brand', 'category', 'priceOffers'])
            ->where('is_active', true)
            ->paginate(15);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        $queries = DB::getQueryLog();
        
        // Assert query count is reasonable (should be 3 or less with eager loading)
        $this->assertLessThanOrEqual(3, count($queries), 'Too many database queries: ' . count($queries));
        
        // Assert execution time is reasonable
        $this->assertLessThan(100, $executionTime, 'Database queries took too long: ' . $executionTime . 'ms');
        
        // Assert we got results
        $this->assertGreaterThan(0, $products->count());
    }

    /**
     * Test N+1 query problem prevention
     */
    public function test_n_plus_one_query_prevention(): void
    {
        DB::enableQueryLog();
        
        // This should use eager loading to prevent N+1 queries
        $products = Product::with(['brand', 'category'])->get();
        
        foreach ($products as $product) {
            $product->brand->name; // This should not trigger additional queries
            $product->category->name; // This should not trigger additional queries
        }
        
        $queries = DB::getQueryLog();
        
        // Should only have 3 queries: products, brands, categories
        $this->assertLessThanOrEqual(3, count($queries), 'N+1 query problem detected: ' . count($queries) . ' queries');
    }

    /**
     * Test memory usage with large dataset
     */
    public function test_memory_usage_large_dataset(): void
    {
        $initialMemory = memory_get_usage(true);
        
        // Load a large number of products
        $products = Product::with(['brand', 'category'])->get();
        
        $finalMemory = memory_get_usage(true);
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024; // Convert to MB
        
        // Memory usage should be reasonable (less than 10MB for 1000 products)
        $this->assertLessThan(10, $memoryUsed, 'Memory usage too high: ' . $memoryUsed . 'MB');
    }

    /**
     * Test concurrent request handling
     */
    public function test_concurrent_requests(): void
    {
        $startTime = microtime(true);
        
        // Simulate concurrent requests
        $responses = [];
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->get('/api/products');
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        
        // All responses should be successful
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }
        
        // Total time should be reasonable (less than 2 seconds for 10 requests)
        $this->assertLessThan(2000, $totalTime, 'Concurrent requests took too long: ' . $totalTime . 'ms');
    }

    /**
     * Test cache performance
     */
    public function test_cache_performance(): void
    {
        // Clear cache first
        cache()->flush();
        
        $startTime = microtime(true);
        
        // First request (cache miss)
        $response1 = $this->get('/api/products');
        
        $firstRequestTime = microtime(true);
        
        // Second request (cache hit)
        $response2 = $this->get('/api/products');
        
        $endTime = microtime(true);
        
        $firstRequestDuration = ($firstRequestTime - $startTime) * 1000;
        $secondRequestDuration = ($endTime - $firstRequestTime) * 1000;
        
        $response1->assertStatus(200);
        $response2->assertStatus(200);
        
        // Second request should be faster (cache hit)
        $this->assertLessThan($firstRequestDuration, $secondRequestDuration, 'Cache not working effectively');
    }

    /**
     * Test API response size
     */
    public function test_api_response_size(): void
    {
        $response = $this->get('/api/products');
        
        $response->assertStatus(200);
        
        $contentLength = strlen($response->getContent());
        
        // Response should not be too large (less than 1MB)
        $this->assertLessThan(1024 * 1024, $contentLength, 'API response too large: ' . $contentLength . ' bytes');
    }

    /**
     * Create test data for performance testing
     */
    private function createTestData(): void
    {
        // Create categories
        $categories = Category::factory()->count(10)->create();
        
        // Create brands
        $brands = Brand::factory()->count(20)->create();
        
        // Create products
        Product::factory()->count(1000)->create([
            'category_id' => fn() => $categories->random()->id,
            'brand_id' => fn() => $brands->random()->id,
        ]);
    }
}
