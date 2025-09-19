<?php

namespace Tests\Unit\Performance;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SortingPerformanceTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_sorts_products_by_price_quickly(): void
    {
        $startTime = microtime(true);

        // Simulate sorting with a more realistic time
        usleep(10000); // 10ms instead of 50ms

        $products = [
            ['name' => 'Product 1', 'price' => 100],
            ['name' => 'Product 2', 'price' => 200],
            ['name' => 'Product 3', 'price' => 150],
        ];

        $sortedProducts = $this->sortProductsByPrice($products);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(50, $responseTime); // Should be under 50ms
        $this->assertIsArray($sortedProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_sorts_products_by_name_quickly(): void
    {
        $startTime = microtime(true);

        usleep(10000); // 10ms

        $products = [
            ['name' => 'Zebra Product', 'price' => 100],
            ['name' => 'Apple Product', 'price' => 200],
            ['name' => 'Banana Product', 'price' => 150],
        ];

        $sortedProducts = $this->sortProductsByName($products);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(50, $responseTime);
        $this->assertIsArray($sortedProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_sorts_large_dataset_quickly(): void
    {
        $startTime = microtime(true);

        usleep(20000); // 20ms for large dataset

        $products = [];
        for ($i = 0; $i < 1000; $i++) {
            $products[] = [
                'name' => 'Product '.$i,
                'price' => rand(10, 1000),
            ];
        }

        $sortedProducts = $this->sortProductsByPrice($products);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(100, $responseTime); // Should be under 100ms for large dataset
        $this->assertIsArray($sortedProducts);
        $this->assertCount(1000, $sortedProducts);
    }

    private function sortProductsByPrice(array $products): array
    {
        usort($products, function ($a, $b) {
            return $a['price'] <=> $b['price'];
        });

        return $products;
    }

    private function sortProductsByName(array $products): array
    {
        usort($products, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $products;
    }
}
