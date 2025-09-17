<?php

namespace Tests\Unit\DataQuality;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DataRelevanceTest extends TestCase
{
    #[Test]
    public function it_measures_data_relevance_score(): void
    {
        $data = [
            'title' => 'iPhone 15 Pro Max 256GB',
            'description' => 'Latest iPhone with advanced camera system',
            'category' => 'Electronics',
            'brand' => 'Apple',
            'price' => 1199.00
        ];

        $searchQuery = 'iPhone 15 Pro Max';

        $relevanceScore = $this->calculateRelevanceScore($data, $searchQuery);

        $this->assertGreaterThan(0.8, $relevanceScore);
    }

    #[Test]
    public function it_identifies_irrelevant_data(): void
    {
        $data = [
            'title' => 'Samsung Galaxy S24',
            'description' => 'Android smartphone with great camera',
            'category' => 'Electronics',
            'brand' => 'Samsung',
            'price' => 999.00
        ];

        $searchQuery = 'iPhone accessories';

        $isRelevant = $this->isDataRelevant($data, $searchQuery);

        $this->assertFalse($isRelevant);
    }

    #[Test]
    public function it_calculates_semantic_similarity(): void
    {
        $text1 = 'smartphone mobile phone';
        $text2 = 'cell phone mobile device';

        $similarity = $this->calculateSemanticSimilarity($text1, $text2);

        $this->assertGreaterThan(0.7, $similarity);
    }

    #[Test]
    public function it_validates_category_relevance(): void
    {
        $product = [
            'name' => 'MacBook Pro',
            'category' => 'Laptops',
            'subcategory' => 'Professional Laptops'
        ];

        $searchCategory = 'Electronics > Computers > Laptops';

        $isRelevant = $this->isCategoryRelevant($product, $searchCategory);

        $this->assertTrue($isRelevant);
    }

    #[Test]
    public function it_measures_temporal_relevance(): void
    {
        $data = [
            'title' => 'iPhone 15 Pro',
            'release_date' => '2024-09-15',
            'current_date' => '2024-10-15'
        ];

        $temporalRelevance = $this->calculateTemporalRelevance($data);

        $this->assertGreaterThan(0.9, $temporalRelevance);
    }

    #[Test]
    public function it_identifies_outdated_information(): void
    {
        $data = [
            'title' => 'iPhone 12 Pro',
            'release_date' => '2020-10-13',
            'current_date' => '2024-10-15'
        ];

        $isOutdated = $this->isInformationOutdated($data, 2); // 2 years threshold

        $this->assertTrue($isOutdated);
    }

    #[Test]
    public function it_calculates_user_interest_relevance(): void
    {
        $userProfile = [
            'interests' => ['Electronics', 'Gaming', 'Photography'],
            'purchase_history' => ['iPhone', 'Gaming Laptop', 'Camera']
        ];

        $product = [
            'category' => 'Electronics',
            'subcategory' => 'Smartphones',
            'brand' => 'Apple'
        ];

        $relevance = $this->calculateUserInterestRelevance($userProfile, $product);

        $this->assertGreaterThan(0.7, $relevance);
    }

    #[Test]
    public function it_validates_geographic_relevance(): void
    {
        $product = [
            'name' => 'Product A',
            'available_regions' => ['US', 'CA', 'UK'],
            'shipping_zones' => ['North America', 'Europe']
        ];

        $userLocation = 'US';

        $isGeographicallyRelevant = $this->isGeographicallyRelevant($product, $userLocation);

        $this->assertTrue($isGeographicallyRelevant);
    }

    #[Test]
    public function it_calculates_price_relevance(): void
    {
        $product = [
            'price' => 999.00,
            'category' => 'Electronics'
        ];

        $userBudget = 1000.00;
        $categoryPriceRange = ['min' => 500, 'max' => 1500];

        $priceRelevance = $this->calculatePriceRelevance($product, $userBudget, $categoryPriceRange);

        $this->assertGreaterThan(0.8, $priceRelevance);
    }

    #[Test]
    public function it_measures_overall_data_relevance(): void
    {
        $data = [
            'title' => 'iPhone 15 Pro Max',
            'category' => 'Electronics',
            'price' => 1199.00,
            'brand' => 'Apple',
            'release_date' => '2024-09-15'
        ];

        $criteria = [
            'search_query' => 'iPhone 15',
            'user_interests' => ['Electronics', 'Apple'],
            'budget' => 1200.00,
            'location' => 'US'
        ];

        $overallRelevance = $this->calculateOverallRelevance($data, $criteria);

        $this->assertGreaterThan(0.8, $overallRelevance);
    }

    private function calculateRelevanceScore(array $data, string $query): float
    {
        $queryWords = explode(' ', strtolower($query));
        $dataText = strtolower($data['title'] . ' ' . $data['description']);

        $matches = 0;
        foreach ($queryWords as $word) {
            if (strpos($dataText, $word) !== false) {
                $matches++;
            }
        }

        return $matches / count($queryWords);
    }

    private function isDataRelevant(array $data, string $query): bool
    {
        $relevanceScore = $this->calculateRelevanceScore($data, $query);
        return $relevanceScore > 0.3; // 30% threshold
    }

    private function calculateSemanticSimilarity(string $text1, string $text2): float
    {
        $words1 = explode(' ', strtolower($text1));
        $words2 = explode(' ', strtolower($text2));

        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));

        return count($intersection) / count($union);
    }

    private function isCategoryRelevant(array $product, string $searchCategory): bool
    {
        $productCategory = strtolower($product['category']);
        $searchCategory = strtolower($searchCategory);

        return strpos($searchCategory, $productCategory) !== false;
    }

    private function calculateTemporalRelevance(array $data): float
    {
        $releaseDate = new \DateTime($data['release_date']);
        $currentDate = new \DateTime($data['current_date']);

        $daysSinceRelease = $currentDate->diff($releaseDate)->days;
        $maxRelevanceDays = 365; // 1 year

        return max(0, 1 - ($daysSinceRelease / $maxRelevanceDays));
    }

    private function isInformationOutdated(array $data, int $thresholdYears): bool
    {
        $releaseDate = new \DateTime($data['release_date']);
        $currentDate = new \DateTime($data['current_date']);

        $yearsSinceRelease = $currentDate->diff($releaseDate)->y;

        return $yearsSinceRelease > $thresholdYears;
    }

    private function calculateUserInterestRelevance(array $userProfile, array $product): float
    {
        $matches = 0;
        $totalChecks = 0;

        if (in_array($product['category'], $userProfile['interests'])) {
            $matches++;
        }
        $totalChecks++;

        if (in_array($product['brand'], $userProfile['purchase_history'])) {
            $matches++;
        }
        $totalChecks++;

        return $totalChecks > 0 ? $matches / $totalChecks : 0;
    }

    private function isGeographicallyRelevant(array $product, string $userLocation): bool
    {
        return in_array($userLocation, $product['available_regions']);
    }

    private function calculatePriceRelevance(array $product, float $userBudget, array $priceRange): float
    {
        $price = $product['price'];

        if ($price > $userBudget) {
            return 0; // Over budget
        }

        if ($price < $priceRange['min'] || $price > $priceRange['max']) {
            return 0.5; // Outside normal range
        }

        return 1.0; // Within budget and normal range
    }

    private function calculateOverallRelevance(array $data, array $criteria): float
    {
        $scores = [];

        // Title relevance
        $scores[] = $this->calculateRelevanceScore($data, $criteria['search_query']);

        // Category relevance
        $scores[] = $this->isCategoryRelevant($data, $criteria['user_interests'][0]) ? 1.0 : 0.0;

        // Price relevance
        $scores[] = $this->calculatePriceRelevance($data, $criteria['budget'], ['min' => 0, 'max' => 2000]);

        return array_sum($scores) / count($scores);
    }
}
