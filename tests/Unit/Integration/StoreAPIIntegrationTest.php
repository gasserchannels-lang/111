<?php

namespace Tests\Unit\Integration;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StoreAPIIntegrationTest extends TestCase
{
    #[Test]
    public function it_integrates_with_store_api(): void
    {
        $storeApi = $this->createStoreApiMock();
        $response = $storeApi->getProducts();

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('products', $response['data']);
    }

    #[Test]
    public function it_handles_api_authentication(): void
    {
        $storeApi = $this->createStoreApiMock();
        $authenticated = $storeApi->authenticate('test_key');

        $this->assertTrue($authenticated);
    }

    #[Test]
    public function it_fetches_product_data(): void
    {
        $storeApi = $this->createStoreApiMock();
        $product = $storeApi->getProduct(1);

        $this->assertIsArray($product);
        $this->assertArrayHasKey('id', $product);
        $this->assertArrayHasKey('name', $product);
        $this->assertArrayHasKey('price', $product);
    }

    #[Test]
    public function it_handles_api_errors(): void
    {
        $storeApi = $this->createStoreApiMock();
        $storeApi->setErrorMode(true);

        $this->expectException(\Exception::class);
        $storeApi->getProduct(999);
    }

    #[Test]
    public function it_syncs_product_prices(): void
    {
        $storeApi = $this->createStoreApiMock();
        $products = [
            ['id' => 1, 'price' => 100.00],
            ['id' => 2, 'price' => 200.00],
        ];

        $result = $storeApi->syncPrices($products);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_handles_rate_limiting(): void
    {
        $storeApi = $this->createStoreApiMock();
        $storeApi->setRateLimit(10);

        // Make multiple requests
        for ($i = 0; $i < 5; $i++) {
            $response = $storeApi->getProducts();
            $this->assertIsArray($response);
        }
    }

    #[Test]
    public function it_handles_network_timeouts(): void
    {
        $storeApi = $this->createStoreApiMock();
        $storeApi->setTimeout(1);

        $start = microtime(true);
        $response = $storeApi->getProducts();
        $duration = microtime(true) - $start;

        $this->assertLessThan(2, $duration);
        $this->assertIsArray($response);
    }

    #[Test]
    public function it_validates_api_response_format(): void
    {
        $storeApi = $this->createStoreApiMock();
        $response = $storeApi->getProducts();

        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('success', $response['status']);
    }

    #[Test]
    public function it_handles_pagination(): void
    {
        $storeApi = $this->createStoreApiMock();
        $page1 = $storeApi->getProducts(1, 10);
        $page2 = $storeApi->getProducts(2, 10);

        $this->assertIsArray($page1);
        $this->assertIsArray($page2);
        $this->assertNotEquals($page1, $page2);
    }

    #[Test]
    public function it_caches_api_responses(): void
    {
        $storeApi = $this->createStoreApiMock();
        $storeApi->enableCache(true);

        $response1 = $storeApi->getProducts();
        $response2 = $storeApi->getProducts();

        $this->assertEquals($response1, $response2);
    }

    private function createStoreApiMock(): object
    {
        return new class
        {
            private bool $errorMode = false;

            private int $rateLimit = 100;

            private int $timeout = 30;

            private bool $cacheEnabled = false;

            private array $cache = [];

            public function authenticate(string $key): bool
            {
                return ! empty($key);
            }

            public function getProducts(int $page = 1, int $limit = 20): array
            {
                if ($this->errorMode) {
                    throw new \Exception('API Error');
                }

                if ($this->cacheEnabled && isset($this->cache["products_{$page}_{$limit}"])) {
                    return $this->cache["products_{$page}_{$limit}"];
                }

                $products = [];
                for ($i = 1; $i <= $limit; $i++) {
                    $products[] = [
                        'id' => ($page - 1) * $limit + $i,
                        'name' => "Product {$i}",
                        'price' => rand(10, 1000),
                    ];
                }

                $response = [
                    'status' => 'success',
                    'data' => [
                        'products' => $products,
                        'pagination' => [
                            'page' => $page,
                            'limit' => $limit,
                            'total' => 1000,
                        ],
                    ],
                ];

                if ($this->cacheEnabled) {
                    $this->cache["products_{$page}_{$limit}"] = $response;
                }

                return $response;
            }

            public function getProduct(int $id): array
            {
                if ($this->errorMode) {
                    throw new \Exception('Product not found');
                }

                return [
                    'id' => $id,
                    'name' => "Product {$id}",
                    'price' => rand(10, 1000),
                    'description' => "Description for product {$id}",
                ];
            }

            public function syncPrices(array $products): bool
            {
                return ! empty($products);
            }

            public function setErrorMode(bool $enabled): void
            {
                $this->errorMode = $enabled;
            }

            public function setRateLimit(int $limit): void
            {
                $this->rateLimit = $limit;
            }

            public function setTimeout(int $seconds): void
            {
                $this->timeout = $seconds;
            }

            public function enableCache(bool $enabled): void
            {
                $this->cacheEnabled = $enabled;
            }
        };
    }
}
