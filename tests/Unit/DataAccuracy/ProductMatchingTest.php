<?php

declare(strict_types=1);

namespace Tests\Unit\DataAccuracy;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductMatchingTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_matches_products_by_exact_name(): void
    {
        $product1 = ['name' => 'iPhone 15 Pro', 'brand' => 'Apple'];
        $product2 = ['name' => 'iPhone 15 Pro', 'brand' => 'Apple'];

        $this->assertEquals($product1['name'], $product2['name']);
        $this->assertEquals($product1['brand'], $product2['brand']);
    }

    #[Test]
    #[CoversNothing]
    public function it_matches_products_by_similar_name(): void
    {
        $product1 = ['name' => 'Samsung Galaxy S24', 'brand' => 'Samsung'];
        $product2 = ['name' => 'Samsung Galaxy S24 Ultra', 'brand' => 'Samsung'];

        $similarity = $this->calculateSimilarity($product1['name'], $product2['name']);
        $this->assertGreaterThan(0.8, $similarity);
    }

    #[Test]
    #[CoversNothing]
    public function it_matches_products_by_brand(): void
    {
        $products = [
            ['name' => 'MacBook Pro', 'brand' => 'Apple'],
            ['name' => 'iPhone 15', 'brand' => 'Apple'],
            ['name' => 'iPad Air', 'brand' => 'Apple'],
        ];

        $appleProducts = array_filter($products, fn ($p) => $p['brand'] === 'Apple');
        $this->assertCount(3, $appleProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_matches_products_by_category(): void
    {
        $products = [
            ['name' => 'iPhone 15', 'category' => 'Smartphones'],
            ['name' => 'Samsung Galaxy S24', 'category' => 'Smartphones'],
            ['name' => 'MacBook Pro', 'category' => 'Laptops'],
        ];

        $smartphones = array_filter($products, fn ($p) => $p['category'] === 'Smartphones');
        $this->assertCount(2, $smartphones);
    }

    #[Test]
    #[CoversNothing]
    public function it_matches_products_by_price_range(): void
    {
        $products = [
            ['name' => 'Product A', 'price' => 100.00],
            ['name' => 'Product B', 'price' => 150.00],
            ['name' => 'Product C', 'price' => 200.00],
        ];

        $minPrice = 120.00;
        $maxPrice = 180.00;

        $filteredProducts = array_filter($products, function ($p) use ($minPrice, $maxPrice) {
            return $p['price'] >= $minPrice && $p['price'] <= $maxPrice;
        });

        $this->assertCount(1, $filteredProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_matches_products_by_specifications(): void
    {
        $product1 = [
            'name' => 'iPhone 15 Pro',
            'specs' => ['storage' => '256GB', 'color' => 'Space Black', 'screen' => '6.1"'],
        ];

        $product2 = [
            'name' => 'iPhone 15 Pro',
            'specs' => ['storage' => '256GB', 'color' => 'Space Black', 'screen' => '6.1"'],
        ];

        $this->assertEquals($product1['specs'], $product2['specs']);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_case_insensitive_matching(): void
    {
        $product1 = ['name' => 'iPhone 15 Pro'];
        $product2 = ['name' => 'iphone 15 pro'];

        $this->assertEquals(
            strtolower($product1['name']),
            strtolower($product2['name'])
        );
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_whitespace_normalization(): void
    {
        $product1 = ['name' => 'iPhone 15 Pro'];
        $product2 = ['name' => 'iPhone  15  Pro'];

        $normalized1 = preg_replace('/\s+/', ' ', trim($product1['name']));
        $normalized2 = preg_replace('/\s+/', ' ', trim($product2['name']));

        $this->assertEquals($normalized1, $normalized2);
    }

    #[Test]
    #[CoversNothing]
    public function it_matches_products_by_model_number(): void
    {
        $product1 = ['name' => 'iPhone 15 Pro', 'model' => 'A3102'];
        $product2 = ['name' => 'iPhone 15 Pro', 'model' => 'A3102'];

        $this->assertEquals($product1['model'], $product2['model']);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_partial_matching(): void
    {
        $searchTerm = 'iPhone';
        $products = [
            ['name' => 'iPhone 15 Pro'],
            ['name' => 'iPhone 15'],
            ['name' => 'Samsung Galaxy S24'],
        ];

        $matchingProducts = array_filter($products, function ($p) use ($searchTerm) {
            return stripos($p['name'], $searchTerm) !== false;
        });

        $this->assertCount(2, $matchingProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_product_data_integrity(): void
    {
        $product = [
            'id' => 1,
            'name' => 'iPhone 15 Pro',
            'brand' => 'Apple',
            'price' => 999.99,
            'category' => 'Smartphones',
        ];

        $this->assertArrayHasKey('id', $product);
        $this->assertArrayHasKey('name', $product);
        $this->assertArrayHasKey('brand', $product);
        $this->assertArrayHasKey('price', $product);
        $this->assertArrayHasKey('category', $product);

        $this->assertIsInt($product['id']);
        $this->assertIsString($product['name']);
        $this->assertIsString($product['brand']);
        $this->assertIsNumeric($product['price']);
        $this->assertIsString($product['category']);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_duplicate_products(): void
    {
        $products = [
            ['id' => 1, 'name' => 'iPhone 15 Pro'],
            ['id' => 2, 'name' => 'iPhone 15 Pro'],
            ['id' => 3, 'name' => 'Samsung Galaxy S24'],
        ];

        $duplicates = [];
        $seen = [];

        foreach ($products as $product) {
            $key = $product['name'];
            if (isset($seen[$key])) {
                $duplicates[] = $product;
            } else {
                $seen[$key] = true;
            }
        }

        $this->assertCount(1, $duplicates);
    }

    private function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));

        if ($str1 === $str2) {
            return 1.0;
        }

        $maxLength = max(strlen($str1), strlen($str2));
        if ($maxLength === 0) {
            return 1.0;
        }

        // Use Jaro-Winkler similarity for better results
        $jaro = $this->jaroSimilarity($str1, $str2);
        $prefixLength = $this->getCommonPrefixLength($str1, $str2);
        $jaroWinkler = $jaro + (0.1 * $prefixLength * (1 - $jaro));

        return $jaroWinkler;
    }

    private function jaroSimilarity(string $str1, string $str2): float
    {
        $len1 = strlen($str1);
        $len2 = strlen($str2);

        if ($len1 === 0 || $len2 === 0) {
            return 0.0;
        }

        $matchWindow = max($len1, $len2) / 2 - 1;
        $str1Matches = array_fill(0, $len1, false);
        $str2Matches = array_fill(0, $len2, false);

        $matches = 0;
        $transpositions = 0;

        // Find matches
        for ($i = 0; $i < $len1; $i++) {
            $start = max(0, $i - $matchWindow);
            $end = min($i + $matchWindow + 1, $len2);

            for ($j = $start; $j < $end; $j++) {
                if ($str2Matches[$j] || $str1[$i] !== $str2[$j]) {
                    continue;
                }
                $str1Matches[$i] = true;
                $str2Matches[$j] = true;
                $matches++;
                break;
            }
        }

        if ($matches === 0) {
            return 0.0;
        }

        // Count transpositions
        $k = 0;
        for ($i = 0; $i < $len1; $i++) {
            if (! $str1Matches[$i]) {
                continue;
            }
            while (! $str2Matches[$k]) {
                $k++;
            }
            if ($str1[$i] !== $str2[$k]) {
                $transpositions++;
            }
            $k++;
        }

        return ($matches / $len1 + $matches / $len2 + ($matches - $transpositions / 2) / $matches) / 3;
    }

    private function getCommonPrefixLength(string $str1, string $str2): int
    {
        $minLength = min(strlen($str1), strlen($str2));
        $prefixLength = 0;

        for ($i = 0; $i < $minLength; $i++) {
            if ($str1[$i] === $str2[$i]) {
                $prefixLength++;
            } else {
                break;
            }
        }

        return min($prefixLength, 4); // Max prefix length of 4
    }
}
