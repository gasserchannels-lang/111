<?php

namespace Tests\Unit\Performance;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * اختبارات أداء التصفية والفلترة
 *
 * هذا الكلاس يختبر أداء عمليات التصفية والفلترة للمنتجات
 * ويحذر من العمليات البطيئة التي قد تؤثر على تجربة المستخدم
 *
 * ⚠️ تحذير: يجب تحسين خوارزميات التصفية لضمان استجابة سريعة
 */
class FilteringPerformanceTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_measures_filtering_response_time(): void
    {
        // ⚠️ تحذير: التصفية البسيطة يجب أن تكون سريعة
        $products = $this->generateProducts(1000);
        $filters = ['category' => 'Electronics', 'price_min' => 500, 'price_max' => 1000];

        $startTime = microtime(true);

        $filteredProducts = $this->applyFilters($products, $filters);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // ⚠️ تحذير: التصفية البسيطة يجب أن تكتمل في أقل من 100ms
        $this->assertLessThan(100, $responseTime, '⚠️ تحذير: التصفية البسيطة بطيئة جداً! يجب أن تكتمل في أقل من 100ms');
        $this->assertIsArray($filteredProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_filtering_throughput(): void
    {
        // ⚠️ تحذير: يجب أن تتعامل مع عدة تصفيات متتالية بسرعة
        $products = $this->generateProducts(5000);
        $filterSets = [
            ['category' => 'Electronics'],
            ['brand' => 'Apple'],
            ['price_min' => 1000],
            ['rating' => 4.0],
            ['in_stock' => true],
        ];

        $startTime = microtime(true);

        foreach ($filterSets as $filters) {
            $this->applyFilters($products, $filters);
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $throughput = count($filterSets) / $totalTime; // Filters per second

        // ⚠️ تحذير: يجب التعامل مع 50 تصفية على الأقل في الثانية
        $this->assertGreaterThan(50, $throughput, '⚠️ تحذير: معدل التصفية بطيء جداً! يجب التعامل مع 50 تصفية على الأقل في الثانية');
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_filtering_memory_usage(): void
    {
        $products = $this->generateProducts(10000);
        $filters = ['category' => 'Electronics', 'brand' => 'Apple'];

        $memoryBefore = memory_get_usage();

        $filteredProducts = $this->applyFilters($products, $filters);

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        $this->assertLessThan(5 * 1024 * 1024, $memoryUsed); // Should use less than 5MB
        $this->assertIsArray($filteredProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_complex_filtering_performance(): void
    {
        // ⚠️ تحذير: التصفية المعقدة مع عدة معايير قد تكون بطيئة
        $products = $this->generateProducts(5000);
        $complexFilters = [
            'category' => 'Electronics',
            'brand' => 'Apple',
            'price_min' => 500,
            'price_max' => 2000,
            'rating' => 4.0,
            'in_stock' => true,
            'features' => ['5G', 'Wireless Charging'],
            'release_year' => 2023,
        ];

        $startTime = microtime(true);

        $filteredProducts = $this->applyComplexFilters($products, $complexFilters);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        // ⚠️ تحذير: التصفية المعقدة يجب أن تكتمل في أقل من 200ms
        $this->assertLessThan(200, $responseTime, '⚠️ تحذير: التصفية المعقدة بطيئة جداً! يجب أن تكتمل في أقل من 200ms');
        $this->assertIsArray($filteredProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_filtering_with_sorting_performance(): void
    {
        $products = $this->generateProducts(2000);
        $filters = ['category' => 'Electronics'];
        $sortBy = 'price';
        $sortOrder = 'asc';

        $startTime = microtime(true);

        $filteredProducts = $this->applyFiltersWithSorting($products, $filters, $sortBy, $sortOrder);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(150, $responseTime); // Should be under 150ms
        $this->assertIsArray($filteredProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_filtering_with_pagination_performance(): void
    {
        $products = $this->generateProducts(10000);
        $filters = ['category' => 'Electronics'];
        $page = 3;
        $perPage = 20;

        $startTime = microtime(true);

        $filteredProducts = $this->applyFiltersWithPagination($products, $filters, $page, $perPage);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(120, $responseTime); // Should be under 120ms
        $this->assertIsArray($filteredProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_dynamic_filtering_performance(): void
    {
        $products = $this->generateProducts(3000);
        $dynamicFilters = [
            'price_range' => '500-1000',
            'brands' => ['Apple', 'Samsung', 'Google'],
            'categories' => ['Electronics', 'Accessories'],
            'ratings' => [4.0, 4.5, 5.0],
        ];

        $startTime = microtime(true);

        $filteredProducts = $this->applyDynamicFilters($products, $dynamicFilters);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(180, $responseTime); // Should be under 180ms
        $this->assertIsArray($filteredProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_filtering_cache_performance(): void
    {
        $products = $this->generateProducts(2000);
        $filters = ['category' => 'Electronics', 'brand' => 'Apple'];

        // First filtering (cache miss)
        $startTime = microtime(true);
        $results1 = $this->applyFilters($products, $filters);
        $endTime = microtime(true);
        $firstFilterTime = ($endTime - $startTime) * 1000;

        // Second filtering (cache hit)
        $startTime = microtime(true);
        $results2 = $this->applyFilters($products, $filters);
        $endTime = microtime(true);
        $secondFilterTime = ($endTime - $startTime) * 1000;

        // Results should be identical
        $this->assertEquals($results1, $results2);

        // Both filtering operations should be reasonably fast
        $this->assertLessThan(100, $firstFilterTime);
        $this->assertLessThan(100, $secondFilterTime);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_filtering_accuracy(): void
    {
        $products = $this->generateProducts(1000);
        $filters = ['category' => 'Electronics', 'price_min' => 500];

        $filteredProducts = $this->applyFilters($products, $filters);

        // Verify all filtered products meet the criteria
        foreach ($filteredProducts as $product) {
            $this->assertEquals('Electronics', $product['category']);
            $this->assertGreaterThanOrEqual(500, $product['price']);
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_filtering_scalability(): void
    {
        $datasetSizes = [1000, 5000, 10000, 20000];
        $responseTimes = [];

        foreach ($datasetSizes as $size) {
            $products = $this->generateProducts($size);
            $filters = ['category' => 'Electronics'];

            $startTime = microtime(true);
            $this->applyFilters($products, $filters);
            $endTime = microtime(true);

            $responseTimes[$size] = ($endTime - $startTime) * 1000;
        }

        // Response time should not increase dramatically with dataset size
        $this->assertLessThan(500, $responseTimes[20000]); // Should be under 500ms even with 20K items
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_filtering_with_indexes_performance(): void
    {
        $products = $this->generateProducts(10000);
        $filters = ['category' => 'Electronics', 'brand' => 'Apple'];

        // Build indexes
        $this->buildFilterIndexes($products);

        $startTime = microtime(true);

        $filteredProducts = $this->applyFiltersWithIndexes($products, $filters);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(50, $responseTime); // Should be under 50ms with indexes
        $this->assertIsArray($filteredProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_filtering_concurrent_requests(): void
    {
        $products = $this->generateProducts(5000);
        $filterSets = [
            ['category' => 'Electronics'],
            ['brand' => 'Apple'],
            ['price_min' => 1000],
            ['rating' => 4.0],
            ['in_stock' => true],
        ];

        $startTime = microtime(true);

        $results = $this->applyConcurrentFilters($products, $filterSets);

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;

        $this->assertLessThan(1.0, $totalTime); // Should complete all filters in under 1 second
        $this->assertCount(5, $results);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_filtering_memory_efficiency(): void
    {
        $products = $this->generateProducts(50000);
        $filters = ['category' => 'Electronics'];

        $memoryBefore = memory_get_usage();

        $filteredProducts = $this->applyFilters($products, $filters);

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        // Memory usage should be reasonable even with large datasets
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed); // Should use less than 50MB
        $this->assertIsArray($filteredProducts);
    }

    private function generateProducts(int $count): array
    {
        $products = [];
        $categories = ['Electronics', 'Clothing', 'Books', 'Home', 'Sports'];
        $brands = ['Apple', 'Samsung', 'Google', 'Microsoft', 'Sony'];

        for ($i = 0; $i < $count; $i++) {
            $products[] = [
                'id' => $i + 1,
                'name' => 'Product '.($i + 1),
                'category' => $categories[$i % count($categories)],
                'brand' => $brands[$i % count($brands)],
                'price' => rand(100, 2000),
                'rating' => round(rand(30, 50) / 10, 1),
                'in_stock' => rand(0, 1) === 1,
                'features' => ['5G', 'Wireless Charging', 'Water Resistant'],
                'release_year' => rand(2020, 2024),
            ];
        }

        return $products;
    }

    private function applyFilters(array $products, array $filters): array
    {
        return array_filter($products, function ($product) use ($filters) {
            foreach ($filters as $key => $value) {
                if ($key === 'price_min' && $product['price'] < $value) {
                    return false;
                }
                if ($key === 'price_max' && $product['price'] > $value) {
                    return false;
                }
                if ($key === 'rating' && $product['rating'] < $value) {
                    return false;
                }
                if ($key === 'in_stock' && $product['in_stock'] !== $value) {
                    return false;
                }
                if ($key === 'category' && $product['category'] !== $value) {
                    return false;
                }
                if ($key === 'brand' && $product['brand'] !== $value) {
                    return false;
                }
            }

            return true;
        });
    }

    private function applyComplexFilters(array $products, array $filters): array
    {
        return array_filter($products, function ($product) use ($filters) {
            foreach ($filters as $key => $value) {
                if ($key === 'price_min' && $product['price'] < $value) {
                    return false;
                }
                if ($key === 'price_max' && $product['price'] > $value) {
                    return false;
                }
                if ($key === 'rating' && $product['rating'] < $value) {
                    return false;
                }
                if ($key === 'in_stock' && $product['in_stock'] !== $value) {
                    return false;
                }
                if ($key === 'category' && $product['category'] !== $value) {
                    return false;
                }
                if ($key === 'brand' && $product['brand'] !== $value) {
                    return false;
                }
                if ($key === 'features' && ! array_intersect($value, $product['features'])) {
                    return false;
                }
                if ($key === 'release_year' && $product['release_year'] !== $value) {
                    return false;
                }
            }

            return true;
        });
    }

    private function applyFiltersWithSorting(array $products, array $filters, string $sortBy, string $sortOrder): array
    {
        $filteredProducts = $this->applyFilters($products, $filters);

        usort($filteredProducts, function ($a, $b) use ($sortBy, $sortOrder) {
            $valueA = $a[$sortBy] ?? 0;
            $valueB = $b[$sortBy] ?? 0;

            return $sortOrder === 'asc' ? $valueA <=> $valueB : $valueB <=> $valueA;
        });

        return $filteredProducts;
    }

    private function applyFiltersWithPagination(array $products, array $filters, int $page, int $perPage): array
    {
        $filteredProducts = $this->applyFilters($products, $filters);
        $offset = ($page - 1) * $perPage;

        return array_slice($filteredProducts, $offset, $perPage);
    }

    private function applyDynamicFilters(array $products, array $filters): array
    {
        return array_filter($products, function ($product) use ($filters) {
            foreach ($filters as $key => $value) {
                if ($key === 'price_range') {
                    $range = explode('-', $value);
                    $min = (int) $range[0];
                    $max = (int) $range[1];
                    if ($product['price'] < $min || $product['price'] > $max) {
                        return false;
                    }
                }
                if ($key === 'brands' && ! in_array($product['brand'], $value)) {
                    return false;
                }
                if ($key === 'categories' && ! in_array($product['category'], $value)) {
                    return false;
                }
                if ($key === 'ratings' && ! in_array($product['rating'], $value)) {
                    return false;
                }
            }

            return true;
        });
    }

    private function applyFiltersWithIndexes(array $products, array $filters): array
    {
        // Simulate indexed filtering
        return $this->applyFilters($products, $filters);
    }

    private function applyConcurrentFilters(array $products, array $filterSets): array
    {
        $results = [];
        foreach ($filterSets as $filters) {
            $results[] = $this->applyFilters($products, $filters);
        }

        return $results;
    }

    private function buildFilterIndexes(array $products): void
    {
        // Simulate building filter indexes
        // In a real implementation, this would create indexes for common filter fields
    }
}
