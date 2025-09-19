<?php

namespace Tests\Unit\Recommendations;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UpsellRecommendationTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_recommends_higher_tier_products(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => 'Basic Laptop',
            'category' => 'Electronics',
            'price' => 599.99,
            'tier' => 'basic',
        ];

        $recommendations = $this->getUpsellRecommendations($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));
        $this->assertArrayHasKey('product_id', $recommendations[0]);
        $this->assertArrayHasKey('name', $recommendations[0]);
        $this->assertArrayHasKey('price', $recommendations[0]);
        $this->assertArrayHasKey('upsell_ratio', $recommendations[0]);
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_premium_versions(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => 'Standard iPhone',
            'category' => 'Electronics',
            'price' => 699.99,
            'model' => 'standard',
        ];

        $recommendations = $this->getPremiumVersions($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products are premium versions
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->isPremiumVersion($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_more_features(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => 'Basic Camera',
            'category' => 'Electronics',
            'price' => 299.99,
            'features' => ['Basic Zoom', 'Auto Focus'],
        ];

        $recommendations = $this->getFeatureRichProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have more features
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasMoreFeatures($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_better_specifications(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => '8GB RAM Laptop',
            'category' => 'Electronics',
            'price' => 799.99,
            'specifications' => ['RAM' => '8GB', 'Storage' => '256GB', 'CPU' => 'i5'],
        ];

        $recommendations = $this->getBetterSpecProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have better specifications
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasBetterSpecs($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_extended_warranty(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => 'Laptop with 1 Year Warranty',
            'category' => 'Electronics',
            'price' => 999.99,
            'warranty' => '1 year',
        ];

        $recommendations = $this->getExtendedWarrantyProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have extended warranty
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasExtendedWarranty($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_premium_materials(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => 'Plastic Headphones',
            'category' => 'Electronics',
            'price' => 49.99,
            'material' => 'plastic',
        ];

        $recommendations = $this->getPremiumMaterialProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have premium materials
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasPremiumMaterial($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_better_brand(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => 'Generic Laptop',
            'category' => 'Electronics',
            'price' => 499.99,
            'brand' => 'Generic',
        ];

        $recommendations = $this->getBetterBrandProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have better brand
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasBetterBrand($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_higher_capacity(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => '256GB SSD',
            'category' => 'Electronics',
            'price' => 79.99,
            'capacity' => '256GB',
        ];

        $recommendations = $this->getHigherCapacityProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have higher capacity
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasHigherCapacity($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_better_performance(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => 'Mid-Range GPU',
            'category' => 'Electronics',
            'price' => 299.99,
            'performance_score' => 75,
        ];

        $recommendations = $this->getBetterPerformanceProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have better performance
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasBetterPerformance($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_more_storage(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => '128GB Phone',
            'category' => 'Electronics',
            'price' => 599.99,
            'storage' => '128GB',
        ];

        $recommendations = $this->getMoreStorageProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have more storage
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasMoreStorage($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_better_display(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => 'HD Laptop',
            'category' => 'Electronics',
            'price' => 699.99,
            'display' => 'HD',
        ];

        $recommendations = $this->getBetterDisplayProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have better display
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasBetterDisplay($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_better_connectivity(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => 'WiFi 5 Router',
            'category' => 'Electronics',
            'price' => 99.99,
            'connectivity' => 'WiFi 5',
        ];

        $recommendations = $this->getBetterConnectivityProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have better connectivity
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasBetterConnectivity($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_better_battery_life(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => '8 Hour Laptop',
            'category' => 'Electronics',
            'price' => 799.99,
            'battery_life' => '8 hours',
        ];

        $recommendations = $this->getBetterBatteryProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have better battery life
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasBetterBatteryLife($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_better_camera(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => '12MP Phone',
            'category' => 'Electronics',
            'price' => 499.99,
            'camera' => '12MP',
        ];

        $recommendations = $this->getBetterCameraProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have better camera
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasBetterCamera($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_better_audio(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => 'Basic Headphones',
            'category' => 'Electronics',
            'price' => 29.99,
            'audio_quality' => 'basic',
        ];

        $recommendations = $this->getBetterAudioProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have better audio
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasBetterAudio($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_better_security(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => 'Basic Security Software',
            'category' => 'Software',
            'price' => 29.99,
            'security_features' => ['Basic Antivirus'],
        ];

        $recommendations = $this->getBetterSecurityProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have better security
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasBetterSecurity($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_with_better_support(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => 'Basic Support Plan',
            'category' => 'Service',
            'price' => 99.99,
            'support_level' => 'basic',
        ];

        $recommendations = $this->getBetterSupportProducts($currentProduct);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check that recommended products have better support
        foreach ($recommendations as $recommendation) {
            $this->assertTrue($this->hasBetterSupport($currentProduct, $recommendation));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_upsell_potential(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => 'Basic Laptop',
            'category' => 'Electronics',
            'price' => 599.99,
        ];

        $recommendedProduct = [
            'id' => 2,
            'name' => 'Premium Laptop',
            'category' => 'Electronics',
            'price' => 1299.99,
        ];

        $upsellPotential = $this->calculateUpsellPotential($currentProduct, $recommendedProduct);

        $this->assertIsFloat($upsellPotential);
        $this->assertGreaterThan(0, $upsellPotential);
    }

    #[Test]
    #[CoversNothing]
    public function it_generates_upsell_report(): void
    {
        $currentProduct = [
            'id' => 1,
            'name' => 'Basic Laptop',
            'category' => 'Electronics',
            'price' => 599.99,
        ];

        $report = $this->generateUpsellReport($currentProduct);

        $this->assertArrayHasKey('product_id', $report);
        $this->assertArrayHasKey('recommendations', $report);
        $this->assertArrayHasKey('total_recommendations', $report);
        $this->assertArrayHasKey('average_upsell_ratio', $report);
        $this->assertArrayHasKey('generated_at', $report);
    }

    private function getUpsellRecommendations(array $currentProduct): array
    {
        $tier = $currentProduct['tier'] ?? 'basic';
        $price = $currentProduct['price'];

        $upsellProducts = [
            'basic' => [
                ['name' => 'Professional Laptop', 'price' => $price * 1.5, 'tier' => 'professional'],
                ['name' => 'Enterprise Laptop', 'price' => $price * 2.0, 'tier' => 'enterprise'],
            ],
            'standard' => [
                ['name' => 'Premium iPhone', 'price' => $price * 1.3, 'model' => 'premium'],
                ['name' => 'Pro iPhone', 'price' => $price * 1.6, 'model' => 'pro'],
            ],
        ];

        $recommendations = [];
        $products = $upsellProducts[$tier] ?? [];

        foreach ($products as $product) {
            $recommendations[] = [
                'product_id' => rand(100, 999),
                'name' => $product['name'],
                'category' => $currentProduct['category'],
                'price' => $product['price'],
                'upsell_ratio' => $product['price'] / $price,
                'reason' => 'Higher tier product',
            ];
        }

        return $recommendations;
    }

    private function getPremiumVersions(array $currentProduct): array
    {
        return [
            [
                'product_id' => 2,
                'name' => 'Premium iPhone Pro',
                'category' => 'Electronics',
                'price' => $currentProduct['price'] * 1.4,
                'model' => 'premium',
                'is_premium' => true,
            ],
        ];
    }

    private function getFeatureRichProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 3,
                'name' => 'Advanced Camera',
                'category' => 'Electronics',
                'price' => $currentProduct['price'] * 1.8,
                'features' => ['4K Video', 'Night Mode', 'Optical Zoom', 'Image Stabilization'],
                'feature_count' => 4,
            ],
        ];
    }

    private function getBetterSpecProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 4,
                'name' => '16GB RAM Laptop',
                'category' => 'Electronics',
                'price' => $currentProduct['price'] * 1.3,
                'specifications' => ['RAM' => '16GB', 'Storage' => '512GB', 'CPU' => 'i7'],
                'is_better_spec' => true,
            ],
        ];
    }

    private function getExtendedWarrantyProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 5,
                'name' => 'Laptop with 3 Year Warranty',
                'category' => 'Electronics',
                'price' => $currentProduct['price'] * 1.2,
                'warranty' => '3 years',
                'has_extended_warranty' => true,
            ],
        ];
    }

    private function getPremiumMaterialProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 6,
                'name' => 'Aluminum Headphones',
                'category' => 'Electronics',
                'price' => $currentProduct['price'] * 2.0,
                'material' => 'aluminum',
                'is_premium_material' => true,
            ],
        ];
    }

    private function getBetterBrandProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 7,
                'name' => 'Apple MacBook',
                'category' => 'Electronics',
                'price' => $currentProduct['price'] * 2.5,
                'brand' => 'Apple',
                'is_better_brand' => true,
            ],
        ];
    }

    private function getHigherCapacityProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 8,
                'name' => '1TB SSD',
                'category' => 'Electronics',
                'price' => $currentProduct['price'] * 2.0,
                'capacity' => '1TB',
                'has_higher_capacity' => true,
            ],
        ];
    }

    private function getBetterPerformanceProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 9,
                'name' => 'High-End GPU',
                'category' => 'Electronics',
                'price' => $currentProduct['price'] * 2.5,
                'performance_score' => 95,
                'has_better_performance' => true,
            ],
        ];
    }

    private function getMoreStorageProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 10,
                'name' => '512GB Phone',
                'category' => 'Electronics',
                'price' => $currentProduct['price'] * 1.5,
                'storage' => '512GB',
                'has_more_storage' => true,
            ],
        ];
    }

    private function getBetterDisplayProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 11,
                'name' => '4K Laptop',
                'category' => 'Electronics',
                'price' => $currentProduct['price'] * 1.4,
                'display' => '4K',
                'has_better_display' => true,
            ],
        ];
    }

    private function getBetterConnectivityProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 12,
                'name' => 'WiFi 6 Router',
                'category' => 'Electronics',
                'price' => $currentProduct['price'] * 1.5,
                'connectivity' => 'WiFi 6',
                'has_better_connectivity' => true,
            ],
        ];
    }

    private function getBetterBatteryProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 13,
                'name' => '16 Hour Laptop',
                'category' => 'Electronics',
                'price' => $currentProduct['price'] * 1.3,
                'battery_life' => '16 hours',
                'has_better_battery' => true,
            ],
        ];
    }

    private function getBetterCameraProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 14,
                'name' => '48MP Phone',
                'category' => 'Electronics',
                'price' => $currentProduct['price'] * 1.6,
                'camera' => '48MP',
                'has_better_camera' => true,
            ],
        ];
    }

    private function getBetterAudioProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 15,
                'name' => 'Premium Headphones',
                'category' => 'Electronics',
                'price' => $currentProduct['price'] * 3.0,
                'audio_quality' => 'premium',
                'has_better_audio' => true,
            ],
        ];
    }

    private function getBetterSecurityProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 16,
                'name' => 'Advanced Security Suite',
                'category' => 'Software',
                'price' => $currentProduct['price'] * 2.0,
                'security_features' => ['Advanced Antivirus', 'Firewall', 'VPN', 'Identity Protection'],
                'has_better_security' => true,
            ],
        ];
    }

    private function getBetterSupportProducts(array $currentProduct): array
    {
        return [
            [
                'product_id' => 17,
                'name' => 'Premium Support Plan',
                'category' => 'Service',
                'price' => $currentProduct['price'] * 1.5,
                'support_level' => 'premium',
                'has_better_support' => true,
            ],
        ];
    }

    private function calculateUpsellPotential(array $currentProduct, array $recommendedProduct): float
    {
        $currentPrice = $currentProduct['price'];
        $recommendedPrice = $recommendedProduct['price'];

        return ($recommendedPrice - $currentPrice) / $currentPrice;
    }

    private function generateUpsellReport(array $currentProduct): array
    {
        $recommendations = $this->getUpsellRecommendations($currentProduct);
        $totalRecommendations = count($recommendations);
        $averageUpsellRatio = array_sum(array_column($recommendations, 'upsell_ratio')) / $totalRecommendations;

        return [
            'product_id' => $currentProduct['id'],
            'recommendations' => $recommendations,
            'total_recommendations' => $totalRecommendations,
            'average_upsell_ratio' => $averageUpsellRatio,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    // Helper methods for validation
    private function isPremiumVersion(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['is_premium'] ?? false;
    }

    private function hasMoreFeatures(array $currentProduct, array $recommendedProduct): bool
    {
        $currentFeatures = $currentProduct['features'] ?? [];
        $recommendedFeatures = $recommendedProduct['features'] ?? [];

        return count($recommendedFeatures) > count($currentFeatures);
    }

    private function hasBetterSpecs(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['is_better_spec'] ?? false;
    }

    private function hasExtendedWarranty(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['has_extended_warranty'] ?? false;
    }

    private function hasPremiumMaterial(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['is_premium_material'] ?? false;
    }

    private function hasBetterBrand(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['is_better_brand'] ?? false;
    }

    private function hasHigherCapacity(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['has_higher_capacity'] ?? false;
    }

    private function hasBetterPerformance(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['has_better_performance'] ?? false;
    }

    private function hasMoreStorage(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['has_more_storage'] ?? false;
    }

    private function hasBetterDisplay(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['has_better_display'] ?? false;
    }

    private function hasBetterConnectivity(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['has_better_connectivity'] ?? false;
    }

    private function hasBetterBatteryLife(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['has_better_battery'] ?? false;
    }

    private function hasBetterCamera(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['has_better_camera'] ?? false;
    }

    private function hasBetterAudio(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['has_better_audio'] ?? false;
    }

    private function hasBetterSecurity(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['has_better_security'] ?? false;
    }

    private function hasBetterSupport(array $currentProduct, array $recommendedProduct): bool
    {
        return $recommendedProduct['has_better_support'] ?? false;
    }
}
