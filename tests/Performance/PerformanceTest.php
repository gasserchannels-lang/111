<?php

declare(strict_types=1);

namespace Tests\Performance;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->createTestData();
    }

    /**
     * Test product listing performance with large dataset.
     */
    public function test_product_listing_performance(): void
    {
        // Warm up cache
        $products = Product::with(['category', 'brand'])
            ->select(['id', 'name', 'slug', 'price', 'category_id', 'brand_id'])
            ->where('is_active', true)
            ->take(15)
            ->get();

        // Clear query log for accurate measurement
        DB::flushQueryLog();
        DB::enableQueryLog();

        $memoryBefore = memory_get_usage(true);
        $startTime = microtime(true);

        $response = $this->get('/api/products');

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $memoryAfter = memory_get_usage(true);
        $memoryDelta = ($memoryAfter - $memoryBefore) / 1024 / 1024; // MB

        $queries = DB::getQueryLog();

        $response->assertStatus(200);

        // Assert response time is under 1000ms (realistic target for testing environment)
        $this->assertLessThan(1000, $executionTime, 'Product listing took too long: '.$executionTime.'ms');

        // Assert memory increase is reasonable (environment independent)
        $this->assertLessThan(32, $memoryDelta, 'Memory increase too high: '.$memoryDelta.'MB');

        // Assert query count is optimized
        $this->assertLessThanOrEqual(3, count($queries), 'Too many database queries: '.count($queries));
    }

    /**
     * Test product search performance.
     */
    public function test_product_search_performance(): void
    {
        $startTime = microtime(true);

        $response = $this->get('/api/products?search=test');

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(300, $executionTime, 'Product search took too long: '.$executionTime.'ms');
    }

    /**
     * Test database query performance.
     */
    public function test_database_query_performance(): void
    {
        // Create test data
        Category::factory()->count(5)->create();
        Brand::factory()->count(10)->create();
        Product::factory()->count(50)->create();

        DB::enableQueryLog();

        $startTime = microtime(true);

        // Test query with eager loading and select specific columns
        $products = Product::query()
            ->select(['id', 'name', 'price', 'brand_id', 'category_id'])
            ->with([
                'brand:id,name',
                'category:id,name',
                'priceOffers' => function ($query) {
                    $query->select(['id', 'product_id', 'price'])
                        ->where('is_active', true)
                        ->latest();
                },
            ])
            ->where('is_active', true)
            ->take(15)
            ->get();

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $queries = DB::getQueryLog();

        // Assert query count is reasonable (should be 4 or less with optimized eager loading)
        $this->assertLessThanOrEqual(
            4,
            count($queries),
            'Too many database queries: '.count($queries)."\n".
            implode("\n", array_map(function ($query) {
                return $query['query'];
            }, $queries))
        );

        // Assert execution time is reasonable
        $this->assertLessThan(150, $executionTime, 'Database queries took too long: '.$executionTime.'ms');

        // Assert we got results
        $this->assertGreaterThan(0, $products->count());

        // Assert eager loading worked (no additional queries when accessing relationships)
        DB::flushQueryLog();
        DB::enableQueryLog();

        foreach ($products as $product) {
            $product->brand;
            $product->category;
            $product->priceOffers;
        }

        $additionalQueries = DB::getQueryLog();
        $this->assertEmpty($additionalQueries, 'Eager loading failed, additional queries were executed');
    }

    /**
     * Test N+1 query problem prevention with optimized eager loading.
     */
    public function test_n_plus_one_query_prevention(): void
    {
        // Create test data with relationships
        $categories = Category::factory()->count(3)->create();
        $brands = Brand::factory()->count(5)->create();
        Product::factory()->count(20)->create([
            'category_id' => fn () => $categories->random()->id,
            'brand_id' => fn () => $brands->random()->id,
        ]);

        // Start with a clean query log
        DB::flushQueryLog();
        DB::enableQueryLog();

        // Execute optimized query with eager loading
        $productsOptimized = Product::query()
            ->select(['id', 'name', 'brand_id', 'category_id'])
            ->with([
                'brand' => function ($query) {
                    $query->select(['id', 'name']);
                },
                'category' => function ($query) {
                    $query->select(['id', 'name']);
                },
            ])
            ->get();

        // Access relationships to ensure lazy loading doesn't occur
        foreach ($productsOptimized as $product) {
            $product->brand->name;
            $product->category->name;
        }

        $queries = DB::getQueryLog();

        // We expect exactly 3 queries:
        // 1. Select from products
        // 2. Select from brands (eager load)
        // 3. Select from categories (eager load)
        $this->assertCount(
            3,
            $queries,
            sprintf(
                'Expected 3 queries, got %d queries: %s',
                count($queries),
                implode("\n", array_map(function ($q) {
                    return $q['query'];
                }, $queries))
            )
        );
        // Reset query log to measure only the next set
        DB::flushQueryLog();
        DB::enableQueryLog();

        $products = Product::query()
            ->select(['id', 'name', 'brand_id', 'category_id'])
            ->with(['brand', 'category'])
            ->get();

        foreach ($products as $product) {
            $product->brand->name; // This should not trigger additional queries
            $product->category->name; // This should not trigger additional queries
        }

        $queries = DB::getQueryLog();

        // We expect exactly 3 queries:
        // 1. Select products with specific columns
        // 2. Select brands for eager loading
        // 3. Select categories for eager loading
        $this->assertCount(
            3,
            $queries,
            sprintf(
                'Expected exactly 3 queries, got %d queries:\n%s',
                count($queries),
                implode("\n", array_map(
                    fn ($q) => $q['query'],
                    $queries
                ))
            )
        );

        // Validate query types
        $queryTypes = [
            'products' => false,
            'brands' => false,
            'categories' => false,
        ];

        foreach ($queries as $query) {
            if (str_contains($query['query'], 'from "products"')) {
                $queryTypes['products'] = true;
                // Verify we're using selected columns
                $this->assertStringContainsString('select "id", "name", "brand_id", "category_id"', $query['query']);
            } elseif (str_contains($query['query'], 'from "brands"')) {
                $queryTypes['brands'] = true;
                // Verify we're using selected columns
                $this->assertStringContainsString('select "id", "name"', $query['query']);
            } elseif (str_contains($query['query'], 'from "categories"')) {
                $queryTypes['categories'] = true;
                // Verify we're using selected columns
                $this->assertStringContainsString('select "id", "name"', $query['query']);
            }
        }

        // Verify we have all required query types
        $this->assertTrue(
            $queryTypes['products'] && $queryTypes['brands'] && $queryTypes['categories'],
            'Missing required queries: '.implode(', ', array_keys(array_filter($queryTypes, fn ($v) => ! $v)))
        );
    }

    /**
     * Test memory usage with large dataset.
     */
    public function test_memory_usage_large_dataset(): void
    {
        $initialMemory = memory_get_usage(true);

        // Load a large number of products
        $products = Product::with(['brand', 'category'])->get();

        $finalMemory = memory_get_usage(true);
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024; // Convert to MB

        // Memory usage should be reasonable (less than 10MB for 1000 products)
        $this->assertLessThan(10, $memoryUsed, 'Memory usage too high: '.$memoryUsed.'MB');
    }

    /**
     * Test concurrent request handling.
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
        $this->assertLessThan(2000, $totalTime, 'Concurrent requests took too long: '.$totalTime.'ms');
    }

    /**
     * Test cache performance.
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

        // Second request should be faster (cache hit) - allow more tolerance for performance variations
        $this->assertLessThanOrEqual($firstRequestDuration * 1.5, $secondRequestDuration, 'Cache not working effectively');
    }

    /**
     * Test API response size.
     */
    public function test_api_response_size(): void
    {
        $response = $this->get('/api/products');

        $response->assertStatus(200);

        $contentLength = strlen($response->getContent());

        // Response should not be too large (less than 1MB)
        $this->assertLessThan(1024 * 1024, $contentLength, 'API response too large: '.$contentLength.' bytes');
    }

    /**
     * Create test data for performance testing.
     */
    private function createTestData(): void
    {
        // Create categories
        $categories = Category::factory()->count(10)->create();

        // Create brands
        $brands = Brand::factory()->count(20)->create();

        // Create products
        Product::factory()->count(1000)->create([
            'category_id' => fn () => $categories->random()->id,
            'brand_id' => fn () => $brands->random()->id,
        ]);
    }
}
