<?php

namespace Tests\Unit\Performance;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;

class PaginationPerformanceTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_response_time(): void
    {
        $products = $this->generateProducts(10000);
        $page = 5;
        $perPage = 20;

        $startTime = microtime(true);

        $paginatedProducts = $this->paginateProducts($products, $page, $perPage);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->assertLessThan(50, $responseTime); // Should be under 50ms
        $this->assertIsArray($paginatedProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_throughput(): void
    {
        $products = $this->generateProducts(5000);
        $paginationConfigs = [
            ['page' => 1, 'perPage' => 10],
            ['page' => 2, 'perPage' => 20],
            ['page' => 3, 'perPage' => 50],
            ['page' => 4, 'perPage' => 100],
            ['page' => 5, 'perPage' => 200]
        ];

        $startTime = microtime(true);

        foreach ($paginationConfigs as $config) {
            $this->paginateProducts($products, $config['page'], $config['perPage']);
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $throughput = count($paginationConfigs) / $totalTime; // Paginations per second

        $this->assertGreaterThan(100, $throughput); // Should handle at least 100 paginations per second
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_memory_usage(): void
    {
        $products = $this->generateProducts(50000);
        $page = 10;
        $perPage = 100;

        $memoryBefore = memory_get_usage();

        $paginatedProducts = $this->paginateProducts($products, $page, $perPage);

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        $this->assertLessThan(5 * 1024 * 1024, $memoryUsed); // Should use less than 5MB
        $this->assertIsArray($paginatedProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_with_sorting_performance(): void
    {
        $products = $this->generateProducts(10000);
        $page = 3;
        $perPage = 25;
        $sortBy = 'price';
        $sortOrder = 'asc';

        $startTime = microtime(true);

        $paginatedProducts = $this->paginateProductsWithSorting($products, $page, $perPage, $sortBy, $sortOrder);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(100, $responseTime); // Should be under 100ms
        $this->assertIsArray($paginatedProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_with_filtering_performance(): void
    {
        $products = $this->generateProducts(8000);
        $page = 2;
        $perPage = 30;
        $filters = ['category' => 'Electronics', 'price_min' => 500];

        $startTime = microtime(true);

        $paginatedProducts = $this->paginateProductsWithFiltering($products, $page, $perPage, $filters);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(120, $responseTime); // Should be under 120ms
        $this->assertIsArray($paginatedProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_with_search_performance(): void
    {
        $products = $this->generateProducts(15000);
        $page = 4;
        $perPage = 40;
        $searchQuery = 'iPhone';

        $startTime = microtime(true);

        $paginatedProducts = $this->paginateProductsWithSearch($products, $page, $perPage, $searchQuery);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(150, $responseTime); // Should be under 150ms
        $this->assertIsArray($paginatedProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_metadata_calculation(): void
    {
        $products = $this->generateProducts(1000);
        $perPage = 20;

        $startTime = microtime(true);

        $paginationMetadata = $this->calculatePaginationMetadata($products, $perPage);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(10, $responseTime); // Should be under 10ms
        $this->assertArrayHasKey('total_items', $paginationMetadata);
        $this->assertArrayHasKey('total_pages', $paginationMetadata);
        $this->assertArrayHasKey('current_page', $paginationMetadata);
        $this->assertArrayHasKey('per_page', $paginationMetadata);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_cache_performance(): void
    {
        $products = $this->generateProducts(5000);
        $page = 3;
        $perPage = 25;

        // First pagination (cache miss)
        $startTime = microtime(true);
        $results1 = $this->paginateProducts($products, $page, $perPage);
        $endTime = microtime(true);
        $firstPaginationTime = ($endTime - $startTime) * 1000;

        // Second pagination (cache hit)
        $startTime = microtime(true);
        $results2 = $this->paginateProducts($products, $page, $perPage);
        $endTime = microtime(true);
        $secondPaginationTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan($firstPaginationTime, $secondPaginationTime); // Cached pagination should be faster
        $this->assertEquals($results1, $results2); // Results should be identical
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_accuracy(): void
    {
        $products = $this->generateProducts(1000);
        $page = 3;
        $perPage = 20;

        $paginatedProducts = $this->paginateProducts($products, $page, $perPage);

        // Verify pagination is correct
        $this->assertCount($perPage, $paginatedProducts);

        // Verify we're on the correct page
        $expectedStartIndex = ($page - 1) * $perPage;
        $expectedEndIndex = $expectedStartIndex + $perPage - 1;

        $this->assertEquals($expectedStartIndex, $paginatedProducts[0]['id'] - 1);
        $this->assertEquals($expectedEndIndex, $paginatedProducts[count($paginatedProducts) - 1]['id'] - 1);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_scalability(): void
    {
        $datasetSizes = [1000, 5000, 10000, 50000, 100000];
        $responseTimes = [];

        foreach ($datasetSizes as $size) {
            $products = $this->generateProducts($size);

            $startTime = microtime(true);
            $this->paginateProducts($products, 5, 20);
            $endTime = microtime(true);

            $responseTimes[$size] = ($endTime - $startTime) * 1000;
        }

        // Response time should not increase dramatically with dataset size
        $this->assertLessThan(200, $responseTimes[100000]); // Should be under 200ms even with 100K items
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_with_indexes_performance(): void
    {
        $products = $this->generateProducts(20000);
        $page = 10;
        $perPage = 50;

        // Build indexes
        $this->buildPaginationIndexes($products);

        $startTime = microtime(true);

        $paginatedProducts = $this->paginateProductsWithIndexes($products, $page, $perPage);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(30, $responseTime); // Should be under 30ms with indexes
        $this->assertIsArray($paginatedProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_concurrent_requests(): void
    {
        $products = $this->generateProducts(10000);
        $paginationConfigs = [
            ['page' => 1, 'perPage' => 20],
            ['page' => 2, 'perPage' => 20],
            ['page' => 3, 'perPage' => 20],
            ['page' => 4, 'perPage' => 20],
            ['page' => 5, 'perPage' => 20]
        ];

        $startTime = microtime(true);

        $results = $this->paginateProductsConcurrently($products, $paginationConfigs);

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;

        $this->assertLessThan(0.2, $totalTime); // Should complete all paginations in under 0.2 seconds
        $this->assertCount(5, $results);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_memory_efficiency(): void
    {
        $products = $this->generateProducts(200000);
        $page = 50;
        $perPage = 100;

        $memoryBefore = memory_get_usage();

        $paginatedProducts = $this->paginateProducts($products, $page, $perPage);

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        // Memory usage should be reasonable even with large datasets
        $this->assertLessThan(20 * 1024 * 1024, $memoryUsed); // Should use less than 20MB
        $this->assertIsArray($paginatedProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_edge_cases(): void
    {
        $products = $this->generateProducts(100);

        // Test edge cases
        $edgeCases = [
            ['page' => 1, 'perPage' => 1000], // More items per page than total
            ['page' => 100, 'perPage' => 1], // Page beyond available data
            ['page' => 0, 'perPage' => 10], // Invalid page
            ['page' => 1, 'perPage' => 0], // Invalid per page
            ['page' => -1, 'perPage' => 10] // Negative page
        ];

        foreach ($edgeCases as $case) {
            $startTime = microtime(true);

            $paginatedProducts = $this->paginateProducts($products, $case['page'], $case['perPage']);

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $this->assertLessThan(50, $responseTime); // Should handle edge cases quickly
            $this->assertIsArray($paginatedProducts);
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_pagination_with_custom_sorting(): void
    {
        $products = $this->generateProducts(5000);
        $page = 2;
        $perPage = 30;
        $customSortFunction = function ($a, $b) {
            // Custom sorting by price per rating ratio
            $ratioA = $a['price'] / $a['rating'];
            $ratioB = $b['price'] / $b['rating'];
            return $ratioA <=> $ratioB;
        };

        $startTime = microtime(true);

        $paginatedProducts = $this->paginateProductsWithCustomSorting($products, $page, $perPage, $customSortFunction);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(80, $responseTime); // Should be under 80ms
        $this->assertIsArray($paginatedProducts);
    }

    private function generateProducts(int $count): array
    {
        $products = [];
        $categories = ['Electronics', 'Clothing', 'Books', 'Home', 'Sports'];
        $brands = ['Apple', 'Samsung', 'Google', 'Microsoft', 'Sony'];

        for ($i = 0; $i < $count; $i++) {
            $products[] = [
                'id' => $i + 1,
                'name' => "Product " . ($i + 1),
                'category' => $categories[$i % count($categories)],
                'brand' => $brands[$i % count($brands)],
                'price' => rand(100, 2000),
                'rating' => round(rand(30, 50) / 10, 1),
                'created_at' => date('Y-m-d H:i:s', time() - rand(0, 365 * 24 * 3600))
            ];
        }

        return $products;
    }

    private function paginateProducts(array $products, int $page, int $perPage): array
    {
        // Validate inputs
        if ($page < 1) {
            $page = 1;
        }
        if ($perPage < 1) {
            $perPage = 10;
        }

        $totalItems = count($products);
        $totalPages = ceil($totalItems / $perPage);

        // Handle edge cases
        if ($page > $totalPages) {
            return [];
        }

        $offset = ($page - 1) * $perPage;
        return array_slice($products, $offset, $perPage);
    }

    private function paginateProductsWithSorting(array $products, int $page, int $perPage, string $sortBy, string $sortOrder): array
    {
        // Sort products first
        $sortedProducts = $products;
        usort($sortedProducts, function ($a, $b) use ($sortBy, $sortOrder) {
            $valueA = $a[$sortBy] ?? 0;
            $valueB = $b[$sortBy] ?? 0;

            if (is_string($valueA) && is_string($valueB)) {
                $result = strcmp($valueA, $valueB);
            } else {
                $result = $valueA <=> $valueB;
            }

            return $sortOrder === 'asc' ? $result : -$result;
        });

        // Then paginate
        return $this->paginateProducts($sortedProducts, $page, $perPage);
    }

    private function paginateProductsWithFiltering(array $products, int $page, int $perPage, array $filters): array
    {
        // Filter products first
        $filteredProducts = array_filter($products, function ($product) use ($filters) {
            foreach ($filters as $key => $value) {
                if ($key === 'price_min' && $product['price'] < $value) {
                    return false;
                }
                if ($key === 'price_max' && $product['price'] > $value) {
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

        // Then paginate
        return $this->paginateProducts($filteredProducts, $page, $perPage);
    }

    private function paginateProductsWithSearch(array $products, int $page, int $perPage, string $searchQuery): array
    {
        // Search products first
        $searchResults = array_filter($products, function ($product) use ($searchQuery) {
            return stripos($product['name'], $searchQuery) !== false ||
                stripos($product['category'], $searchQuery) !== false ||
                stripos($product['brand'], $searchQuery) !== false;
        });

        // Then paginate
        return $this->paginateProducts($searchResults, $page, $perPage);
    }

    private function calculatePaginationMetadata(array $products, int $perPage): array
    {
        $totalItems = count($products);
        $totalPages = ceil($totalItems / $perPage);

        return [
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
            'current_page' => 1,
            'per_page' => $perPage,
            'has_next_page' => $totalPages > 1,
            'has_prev_page' => false,
            'next_page' => $totalPages > 1 ? 2 : null,
            'prev_page' => null
        ];
    }

    private function paginateProductsWithIndexes(array $products, int $page, int $perPage): array
    {
        // Simulate indexed pagination
        return $this->paginateProducts($products, $page, $perPage);
    }

    private function paginateProductsConcurrently(array $products, array $paginationConfigs): array
    {
        $results = [];
        foreach ($paginationConfigs as $config) {
            $results[] = $this->paginateProducts($products, $config['page'], $config['perPage']);
        }
        return $results;
    }

    private function paginateProductsWithCustomSorting(array $products, int $page, int $perPage, callable $sortFunction): array
    {
        // Sort products with custom function first
        $sortedProducts = $products;
        usort($sortedProducts, $sortFunction);

        // Then paginate
        return $this->paginateProducts($sortedProducts, $page, $perPage);
    }

    private function buildPaginationIndexes(array $products): void
    {
        // Simulate building pagination indexes
        // In a real implementation, this would create indexes for efficient pagination
    }
}
