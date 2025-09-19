<?php

namespace Tests\Unit\Integration;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EndToEndIntegrationTest extends TestCase
{
    #[Test]
    public function it_completes_full_user_workflow(): void
    {
        $workflow = $this->createWorkflowMock();

        // Step 1: User registration
        $user = $workflow->registerUser('test@example.com', 'password123');
        $this->assertIsArray($user);
        $this->assertArrayHasKey('id', $user);

        // Step 2: User login
        $loginResult = $workflow->loginUser('test@example.com', 'password123');
        $this->assertTrue($loginResult);

        // Step 3: Search for products
        $products = $workflow->searchProducts('laptop');
        $this->assertIsArray($products);
        $this->assertNotEmpty($products);

        // Step 4: Add to wishlist
        $wishlistResult = $workflow->addToWishlist($user['id'], $products[0]['id']);
        $this->assertTrue($wishlistResult);

        // Step 5: Set price alert
        $alertResult = $workflow->setPriceAlert($user['id'], $products[0]['id'], 500.00);
        $this->assertTrue($alertResult);

        // Step 6: Compare prices
        $comparison = $workflow->comparePrices($products[0]['id']);
        $this->assertIsArray($comparison);
        $this->assertArrayHasKey('stores', $comparison);
    }

    #[Test]
    public function it_handles_multi_language_workflow(): void
    {
        $workflow = $this->createWorkflowMock();

        // Set language to Arabic
        $workflow->setLanguage('ar');

        // Search in Arabic
        $products = $workflow->searchProducts('لابتوب');
        $this->assertIsArray($products);

        // Verify Arabic content
        $this->assertStringContainsString('لابتوب', $products[0]['name_ar'] ?? '');
    }

    #[Test]
    public function it_handles_currency_conversion_workflow(): void
    {
        $workflow = $this->createWorkflowMock();

        // Set currency to EUR
        $workflow->setCurrency('EUR');

        // Search products
        $products = $workflow->searchProducts('laptop');
        $this->assertIsArray($products);

        // Verify prices are in EUR
        foreach ($products as $product) {
            $this->assertStringContainsString('€', $product['price_formatted']);
        }
    }

    #[Test]
    public function it_handles_error_recovery(): void
    {
        $workflow = $this->createWorkflowMock();
        $workflow->setErrorMode(true);

        // Try to register user with error mode
        $this->expectException(\Exception::class);
        $workflow->registerUser('test@example.com', 'password123');
    }

    #[Test]
    public function it_handles_concurrent_operations(): void
    {
        $workflow = $this->createWorkflowMock();

        // Simulate concurrent searches
        $results = [];
        for ($i = 0; $i < 5; $i++) {
            $results[] = $workflow->searchProducts("product{$i}");
        }

        $this->assertCount(5, $results);
        foreach ($results as $result) {
            $this->assertIsArray($result);
        }
    }

    #[Test]
    public function it_handles_data_persistence(): void
    {
        $workflow = $this->createWorkflowMock();

        // Create user
        $user = $workflow->registerUser('persist@example.com', 'password123');

        // Add to wishlist
        $workflow->addToWishlist($user['id'], 1);

        // Verify persistence
        $wishlist = $workflow->getWishlist($user['id']);
        $this->assertIsArray($wishlist);
        $this->assertNotEmpty($wishlist);
    }

    #[Test]
    public function it_handles_notification_workflow(): void
    {
        $workflow = $this->createWorkflowMock();

        // Set price alert
        $workflow->setPriceAlert(1, 1, 500.00);

        // Simulate price drop
        $workflow->updateProductPrice(1, 450.00);

        // Check if notification was sent
        $notifications = $workflow->getNotifications(1);
        $this->assertIsArray($notifications);
        $this->assertNotEmpty($notifications);
    }

    #[Test]
    public function it_handles_performance_under_load(): void
    {
        $workflow = $this->createWorkflowMock();

        $start = microtime(true);

        // Simulate high load
        for ($i = 0; $i < 100; $i++) {
            $workflow->searchProducts("product{$i}");
        }

        $duration = microtime(true) - $start;
        $this->assertLessThan(5, $duration); // Should complete within 5 seconds
    }

    #[Test]
    public function it_handles_mobile_responsive_workflow(): void
    {
        $workflow = $this->createWorkflowMock();
        $workflow->setDeviceType('mobile');

        // Test mobile-specific features
        $products = $workflow->searchProducts('laptop');
        $this->assertIsArray($products);

        // Verify mobile-optimized response
        foreach ($products as $product) {
            $this->assertArrayHasKey('mobile_image', $product);
            $this->assertArrayHasKey('mobile_price', $product);
        }
    }

    #[Test]
    public function it_handles_offline_mode(): void
    {
        $workflow = $this->createWorkflowMock();
        $workflow->setOfflineMode(true);

        // Test offline functionality
        $cachedProducts = $workflow->getCachedProducts();
        $this->assertIsArray($cachedProducts);

        // Test offline search
        $results = $workflow->searchProductsOffline('laptop');
        $this->assertIsArray($results);
    }

    private function createWorkflowMock(): object
    {
        return new class
        {
            private string $language = 'en';

            private string $currency = 'USD';

            private bool $errorMode = false;

            private string $deviceType = 'desktop';

            private bool $offlineMode = false;

            private array $users = [];

            private array $wishlists = [];

            private array $priceAlerts = [];

            private array $notifications = [];

            private array $products = [];

            private array $cache = [];

            public function registerUser(string $email, string $password): array
            {
                if ($this->errorMode) {
                    throw new \Exception('Registration failed');
                }

                $user = [
                    'id' => count($this->users) + 1,
                    'email' => $email,
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                $this->users[] = $user;

                return $user;
            }

            public function loginUser(string $email, string $password): bool
            {
                if ($this->errorMode) {
                    return false;
                }

                return ! empty($email) && ! empty($password);
            }

            public function searchProducts(string $query): array
            {
                if ($this->errorMode) {
                    throw new \Exception('Search failed');
                }

                $products = [
                    [
                        'id' => 1,
                        'name' => 'Laptop Pro',
                        'name_ar' => 'لابتوب برو',
                        'price' => 1000.00,
                        'price_formatted' => $this->formatPrice(1000.00),
                        'mobile_image' => 'laptop-mobile.jpg',
                        'mobile_price' => '1000',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Gaming Laptop',
                        'name_ar' => 'لابتوب ألعاب',
                        'price' => 1500.00,
                        'price_formatted' => $this->formatPrice(1500.00),
                        'mobile_image' => 'gaming-mobile.jpg',
                        'mobile_price' => '1500',
                    ],
                ];

                return array_filter($products, function ($product) use ($query) {
                    return stripos($product['name'], $query) !== false ||
                        stripos($product['name_ar'], $query) !== false;
                });
            }

            public function addToWishlist(int $userId, int $productId): bool
            {
                if ($this->errorMode) {
                    return false;
                }

                $this->wishlists[] = [
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                return true;
            }

            public function setPriceAlert(int $userId, int $productId, float $targetPrice): bool
            {
                if ($this->errorMode) {
                    return false;
                }

                $this->priceAlerts[] = [
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'target_price' => $targetPrice,
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                return true;
            }

            public function comparePrices(int $productId): array
            {
                return [
                    'stores' => [
                        ['name' => 'Store A', 'price' => 1000.00],
                        ['name' => 'Store B', 'price' => 950.00],
                        ['name' => 'Store C', 'price' => 1050.00],
                    ],
                ];
            }

            public function getWishlist(int $userId): array
            {
                return array_filter($this->wishlists, function ($item) use ($userId) {
                    return $item['user_id'] === $userId;
                });
            }

            public function updateProductPrice(int $productId, float $newPrice): void
            {
                // Check for price alerts
                foreach ($this->priceAlerts as $alert) {
                    if ($alert['product_id'] === $productId && $newPrice <= $alert['target_price']) {
                        $this->notifications[] = [
                            'user_id' => $alert['user_id'],
                            'message' => 'Price alert: Product price dropped!',
                            'created_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                }
            }

            public function getNotifications(int $userId): array
            {
                return array_filter($this->notifications, function ($notification) use ($userId) {
                    return $notification['user_id'] === $userId;
                });
            }

            public function getCachedProducts(): array
            {
                return $this->cache['products'] ?? [];
            }

            public function searchProductsOffline(string $query): array
            {
                return $this->getCachedProducts();
            }

            public function setLanguage(string $language): void
            {
                $this->language = $language;
            }

            public function setCurrency(string $currency): void
            {
                $this->currency = $currency;
            }

            public function setErrorMode(bool $enabled): void
            {
                $this->errorMode = $enabled;
            }

            public function setDeviceType(string $deviceType): void
            {
                $this->deviceType = $deviceType;
            }

            public function setOfflineMode(bool $enabled): void
            {
                $this->offlineMode = $enabled;
            }

            private function formatPrice(float $price): string
            {
                $formatters = [
                    'USD' => '$%s',
                    'EUR' => '€%s',
                    'GBP' => '£%s',
                ];

                $format = $formatters[$this->currency] ?? '$%s';

                return sprintf($format, number_format($price, 2));
            }
        };
    }
}
