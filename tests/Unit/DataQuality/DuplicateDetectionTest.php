<?php

namespace Tests\Unit\DataQuality;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;

class DuplicateDetectionTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_detects_exact_duplicates(): void
    {
        $products = [
            ['id' => 1, 'name' => 'iPhone 15', 'price' => 999.00],
            ['id' => 2, 'name' => 'Samsung Galaxy S24', 'price' => 899.00],
            ['id' => 3, 'name' => 'iPhone 15', 'price' => 999.00], // Duplicate
            ['id' => 4, 'name' => 'Google Pixel 8', 'price' => 699.00]
        ];

        $duplicates = $this->detectExactDuplicates($products);
        $this->assertCount(1, $duplicates);
        $this->assertEquals('iPhone 15', $duplicates[0]['name']);
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_similar_duplicates(): void
    {
        $products = [
            ['id' => 1, 'name' => 'iPhone 15 Pro', 'price' => 999.00],
            ['id' => 2, 'name' => 'iPhone 15 Pro Max', 'price' => 1099.00],
            ['id' => 3, 'name' => 'Samsung Galaxy S24', 'price' => 899.00],
            ['id' => 4, 'name' => 'Samsung Galaxy S24 Ultra', 'price' => 999.00]
        ];

        $similarDuplicates = $this->detectSimilarDuplicates($products, 0.8);
        $this->assertTrue(count($similarDuplicates) >= 1);
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_price_duplicates(): void
    {
        $products = [
            ['id' => 1, 'name' => 'iPhone 15', 'price' => 999.00],
            ['id' => 2, 'name' => 'Samsung Galaxy S24', 'price' => 999.00], // Same price
            ['id' => 3, 'name' => 'Google Pixel 8', 'price' => 699.00],
            ['id' => 4, 'name' => 'OnePlus 12', 'price' => 999.00] // Same price
        ];

        $priceDuplicates = $this->detectPriceDuplicates($products);
        $this->assertCount(2, $priceDuplicates);
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_url_duplicates(): void
    {
        $products = [
            ['id' => 1, 'name' => 'iPhone 15', 'url' => 'https://amazon.com/iphone15'],
            ['id' => 2, 'name' => 'Samsung Galaxy S24', 'url' => 'https://amazon.com/galaxy-s24'],
            ['id' => 3, 'name' => 'iPhone 15 Pro', 'url' => 'https://amazon.com/iphone15'], // Duplicate URL
            ['id' => 4, 'name' => 'Google Pixel 8', 'url' => 'https://amazon.com/pixel8']
        ];

        $urlDuplicates = $this->detectUrlDuplicates($products);
        $this->assertCount(1, $urlDuplicates);
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_image_duplicates(): void
    {
        $products = [
            ['id' => 1, 'name' => 'iPhone 15', 'image' => 'iphone15.jpg'],
            ['id' => 2, 'name' => 'Samsung Galaxy S24', 'image' => 'galaxy-s24.jpg'],
            ['id' => 3, 'name' => 'iPhone 15 Pro', 'image' => 'iphone15.jpg'], // Duplicate image
            ['id' => 4, 'name' => 'Google Pixel 8', 'image' => 'pixel8.jpg']
        ];

        $imageDuplicates = $this->detectImageDuplicates($products);
        $this->assertCount(1, $imageDuplicates);
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_description_duplicates(): void
    {
        $products = [
            ['id' => 1, 'name' => 'iPhone 15', 'description' => 'Latest iPhone with advanced features'],
            ['id' => 2, 'name' => 'Samsung Galaxy S24', 'description' => 'Latest Samsung phone with great camera'],
            ['id' => 3, 'name' => 'iPhone 15 Pro', 'description' => 'Latest iPhone with advanced features'], // Duplicate description
            ['id' => 4, 'name' => 'Google Pixel 8', 'description' => 'Google phone with AI features']
        ];

        $descriptionDuplicates = $this->detectDescriptionDuplicates($products);
        $this->assertCount(1, $descriptionDuplicates);
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_category_duplicates(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Smartphones', 'slug' => 'smartphones'],
            ['id' => 2, 'name' => 'Laptops', 'slug' => 'laptops'],
            ['id' => 3, 'name' => 'Smartphones', 'slug' => 'smartphones'], // Duplicate
            ['id' => 4, 'name' => 'Tablets', 'slug' => 'tablets']
        ];

        $categoryDuplicates = $this->detectCategoryDuplicates($categories);
        $this->assertCount(1, $categoryDuplicates);
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_brand_duplicates(): void
    {
        $brands = [
            ['id' => 1, 'name' => 'Apple', 'slug' => 'apple'],
            ['id' => 2, 'name' => 'Samsung', 'slug' => 'samsung'],
            ['id' => 3, 'name' => 'Apple Inc.', 'slug' => 'apple-inc'], // Similar to Apple
            ['id' => 4, 'name' => 'Google', 'slug' => 'google']
        ];

        $brandDuplicates = $this->detectBrandDuplicates($brands);
        $this->assertTrue(count($brandDuplicates) >= 1);
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_store_duplicates(): void
    {
        $stores = [
            ['id' => 1, 'name' => 'Amazon', 'url' => 'https://amazon.com'],
            ['id' => 2, 'name' => 'eBay', 'url' => 'https://ebay.com'],
            ['id' => 3, 'name' => 'Amazon.com', 'url' => 'https://amazon.com'], // Duplicate
            ['id' => 4, 'name' => 'Best Buy', 'url' => 'https://bestbuy.com']
        ];

        $storeDuplicates = $this->detectStoreDuplicates($stores);
        $this->assertCount(1, $storeDuplicates);
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_user_duplicates(): void
    {
        $users = [
            ['id' => 1, 'email' => 'user1@example.com', 'name' => 'John Doe'],
            ['id' => 2, 'email' => 'user2@example.com', 'name' => 'Jane Smith'],
            ['id' => 3, 'email' => 'user1@example.com', 'name' => 'John Doe'], // Duplicate email
            ['id' => 4, 'email' => 'user3@example.com', 'name' => 'Bob Johnson']
        ];

        $userDuplicates = $this->detectUserDuplicates($users);
        $this->assertCount(1, $userDuplicates);
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_duplicate_percentage(): void
    {
        $products = [
            ['id' => 1, 'name' => 'iPhone 15', 'price' => 999.00],
            ['id' => 2, 'name' => 'Samsung Galaxy S24', 'price' => 899.00],
            ['id' => 3, 'name' => 'iPhone 15', 'price' => 999.00], // Duplicate
            ['id' => 4, 'name' => 'Google Pixel 8', 'price' => 699.00]
        ];

        $duplicatePercentage = $this->calculateDuplicatePercentage($products);
        $this->assertEquals(25.0, $duplicatePercentage); // 1 out of 4 is duplicate
    }

    private function detectExactDuplicates(array $items): array
    {
        $seen = [];
        $duplicates = [];

        foreach ($items as $item) {
            // Create a key based on name and price for product duplicates
            $key = ($item['name'] ?? '') . '|' . ($item['price'] ?? '');
            if (isset($seen[$key])) {
                $duplicates[] = $item;
            } else {
                $seen[$key] = true;
            }
        }

        return $duplicates;
    }

    private function detectSimilarDuplicates(array $items, float $threshold): array
    {
        $similarDuplicates = [];

        for ($i = 0; $i < count($items); $i++) {
            for ($j = $i + 1; $j < count($items); $j++) {
                $similarity = $this->calculateSimilarity($items[$i], $items[$j]);
                if ($similarity >= $threshold) {
                    $similarDuplicates[] = [
                        'item1' => $items[$i],
                        'item2' => $items[$j],
                        'similarity' => $similarity
                    ];
                }
            }
        }

        return $similarDuplicates;
    }

    private function detectPriceDuplicates(array $items): array
    {
        $priceGroups = [];

        foreach ($items as $item) {
            $price = $item['price'];
            if (!isset($priceGroups[$price])) {
                $priceGroups[$price] = [];
            }
            $priceGroups[$price][] = $item;
        }

        $duplicates = [];
        foreach ($priceGroups as $price => $items) {
            if (count($items) > 1) {
                // Only add the duplicate items (skip the first one)
                $duplicates = array_merge($duplicates, array_slice($items, 1));
            }
        }

        return $duplicates;
    }

    private function detectUrlDuplicates(array $items): array
    {
        $urlGroups = [];

        foreach ($items as $item) {
            $url = $item['url'] ?? '';
            if (!isset($urlGroups[$url])) {
                $urlGroups[$url] = [];
            }
            $urlGroups[$url][] = $item;
        }

        $duplicates = [];
        foreach ($urlGroups as $url => $items) {
            if (count($items) > 1) {
                // Only add the duplicate items (skip the first one)
                $duplicates = array_merge($duplicates, array_slice($items, 1));
            }
        }

        return $duplicates;
    }

    private function detectImageDuplicates(array $items): array
    {
        $imageGroups = [];

        foreach ($items as $item) {
            $image = $item['image'] ?? '';
            if (!isset($imageGroups[$image])) {
                $imageGroups[$image] = [];
            }
            $imageGroups[$image][] = $item;
        }

        $duplicates = [];
        foreach ($imageGroups as $image => $items) {
            if (count($items) > 1) {
                // Only add the duplicate items (skip the first one)
                $duplicates = array_merge($duplicates, array_slice($items, 1));
            }
        }

        return $duplicates;
    }

    private function detectDescriptionDuplicates(array $items): array
    {
        $descriptionGroups = [];

        foreach ($items as $item) {
            $description = $item['description'] ?? '';
            if (!isset($descriptionGroups[$description])) {
                $descriptionGroups[$description] = [];
            }
            $descriptionGroups[$description][] = $item;
        }

        $duplicates = [];
        foreach ($descriptionGroups as $description => $items) {
            if (count($items) > 1) {
                // Only add the duplicate items (skip the first one)
                $duplicates = array_merge($duplicates, array_slice($items, 1));
            }
        }

        return $duplicates;
    }

    private function detectCategoryDuplicates(array $categories): array
    {
        $nameGroups = [];

        foreach ($categories as $category) {
            $name = strtolower($category['name']);
            if (!isset($nameGroups[$name])) {
                $nameGroups[$name] = [];
            }
            $nameGroups[$name][] = $category;
        }

        $duplicates = [];
        foreach ($nameGroups as $name => $categories) {
            if (count($categories) > 1) {
                // Only add the duplicate items (skip the first one)
                $duplicates = array_merge($duplicates, array_slice($categories, 1));
            }
        }

        return $duplicates;
    }

    private function detectBrandDuplicates(array $brands): array
    {
        $similarDuplicates = [];

        for ($i = 0; $i < count($brands); $i++) {
            for ($j = $i + 1; $j < count($brands); $j++) {
                $similarity = $this->calculateStringSimilarity($brands[$i]['name'], $brands[$j]['name']);
                if ($similarity >= 0.8) {
                    $similarDuplicates[] = [
                        'brand1' => $brands[$i],
                        'brand2' => $brands[$j],
                        'similarity' => $similarity
                    ];
                }
            }
        }

        return $similarDuplicates;
    }

    private function detectStoreDuplicates(array $stores): array
    {
        $urlGroups = [];

        foreach ($stores as $store) {
            $url = $store['url'] ?? '';
            if (!isset($urlGroups[$url])) {
                $urlGroups[$url] = [];
            }
            $urlGroups[$url][] = $store;
        }

        $duplicates = [];
        foreach ($urlGroups as $url => $stores) {
            if (count($stores) > 1) {
                // Only add the duplicate items (skip the first one)
                $duplicates = array_merge($duplicates, array_slice($stores, 1));
            }
        }

        return $duplicates;
    }

    private function detectUserDuplicates(array $users): array
    {
        $emailGroups = [];

        foreach ($users as $user) {
            $email = strtolower($user['email']);
            if (!isset($emailGroups[$email])) {
                $emailGroups[$email] = [];
            }
            $emailGroups[$email][] = $user;
        }

        $duplicates = [];
        foreach ($emailGroups as $email => $users) {
            if (count($users) > 1) {
                // Only add the duplicate items (skip the first one)
                $duplicates = array_merge($duplicates, array_slice($users, 1));
            }
        }

        return $duplicates;
    }

    private function calculateDuplicatePercentage(array $items): float
    {
        $duplicates = $this->detectExactDuplicates($items);
        return (count($duplicates) / count($items)) * 100;
    }

    private function calculateSimilarity(array $item1, array $item2): float
    {
        $name1 = $item1['name'] ?? '';
        $name2 = $item2['name'] ?? '';

        return $this->calculateStringSimilarity($name1, $name2);
    }

    private function calculateStringSimilarity(string $str1, string $str2): float
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

        $matchWindow = intval(max($len1, $len2) / 2 - 1);
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
            if (!$str1Matches[$i]) {
                continue;
            }
            while (!$str2Matches[$k]) {
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
