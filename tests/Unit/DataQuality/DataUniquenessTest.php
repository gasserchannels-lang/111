<?php

namespace Tests\Unit\DataQuality;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DataUniquenessTest extends TestCase
{
    #[Test]
    public function it_detects_duplicate_records(): void
    {
        $records = [
            ['id' => 1, 'name' => 'Product A', 'sku' => 'SKU001'],
            ['id' => 2, 'name' => 'Product B', 'sku' => 'SKU002'],
            ['id' => 3, 'name' => 'Product A', 'sku' => 'SKU001'], // Duplicate
            ['id' => 4, 'name' => 'Product C', 'sku' => 'SKU003']
        ];

        $duplicates = $this->findDuplicateRecords($records, ['name', 'sku']);

        $this->assertCount(1, $duplicates);
        $this->assertEquals(3, $duplicates[0]['id']);
    }

    #[Test]
    public function it_validates_unique_constraints(): void
    {
        $data = [
            'email' => 'user@example.com',
            'username' => 'user123',
            'phone' => '+1234567890'
        ];

        $existingData = [
            ['email' => 'other@example.com', 'username' => 'other', 'phone' => '+0987654321'],
            ['email' => 'user@example.com', 'username' => 'different', 'phone' => '+1111111111'] // Email duplicate
        ];

        $isUnique = $this->validateUniqueConstraints($data, $existingData, ['email']);

        $this->assertFalse($isUnique);
    }

    #[Test]
    public function it_calculates_uniqueness_percentage(): void
    {
        $records = [
            ['id' => 1, 'value' => 'A'],
            ['id' => 2, 'value' => 'B'],
            ['id' => 3, 'value' => 'A'], // Duplicate
            ['id' => 4, 'value' => 'C'],
            ['id' => 5, 'value' => 'B']  // Duplicate
        ];

        $uniquenessPercentage = $this->calculateUniquenessPercentage($records, 'value');

        $this->assertEquals(60.0, $uniquenessPercentage); // 3 unique out of 5
    }

    #[Test]
    public function it_detects_near_duplicates(): void
    {
        $records = [
            ['name' => 'iPhone 15 Pro'],
            ['name' => 'iPhone 15 Pro Max'],
            ['name' => 'Samsung Galaxy S24'],
            ['name' => 'iPhone 15 Pro '] // Near duplicate with extra space
        ];

        $nearDuplicates = $this->findNearDuplicates($records, 'name', 0.8);

        $this->assertCount(1, $nearDuplicates); // iPhone 15 Pro and iPhone 15 Pro Max
    }

    #[Test]
    public function it_validates_primary_key_uniqueness(): void
    {
        $records = [
            ['id' => 1, 'name' => 'Product A'],
            ['id' => 2, 'name' => 'Product B'],
            ['id' => 1, 'name' => 'Product C'], // Duplicate ID
            ['id' => 3, 'name' => 'Product D']
        ];

        $isUnique = $this->validatePrimaryKeyUniqueness($records, 'id');

        $this->assertFalse($isUnique);
    }

    #[Test]
    public function it_handles_case_insensitive_uniqueness(): void
    {
        $records = [
            ['email' => 'user@example.com'],
            ['email' => 'USER@EXAMPLE.COM'], // Case insensitive duplicate
            ['email' => 'admin@example.com']
        ];

        $duplicates = $this->findCaseInsensitiveDuplicates($records, 'email');

        $this->assertCount(1, $duplicates);
    }

    #[Test]
    public function it_detects_fuzzy_duplicates(): void
    {
        $records = [
            ['address' => '123 Main Street, New York, NY'],
            ['address' => '123 Main St, New York, NY'], // Fuzzy duplicate
            ['address' => '456 Oak Avenue, Los Angeles, CA']
        ];

        $fuzzyDuplicates = $this->findFuzzyDuplicates($records, 'address', 0.7);

        $this->assertCount(1, $fuzzyDuplicates);
    }

    #[Test]
    public function it_validates_composite_key_uniqueness(): void
    {
        $records = [
            ['user_id' => 1, 'product_id' => 100, 'quantity' => 2],
            ['user_id' => 1, 'product_id' => 101, 'quantity' => 1],
            ['user_id' => 1, 'product_id' => 100, 'quantity' => 3], // Duplicate composite key
            ['user_id' => 2, 'product_id' => 100, 'quantity' => 1]
        ];

        $isUnique = $this->validateCompositeKeyUniqueness($records, ['user_id', 'product_id']);

        $this->assertFalse($isUnique);
    }

    #[Test]
    public function it_calculates_data_diversity_score(): void
    {
        $data = [
            ['category' => 'Electronics'],
            ['category' => 'Clothing'],
            ['category' => 'Electronics'],
            ['category' => 'Books'],
            ['category' => 'Electronics']
        ];

        $diversityScore = $this->calculateDataDiversityScore($data, 'category');

        $this->assertGreaterThan(0.5, $diversityScore);
    }

    #[Test]
    public function it_handles_empty_and_null_values(): void
    {
        $records = [
            ['name' => 'Product A', 'description' => 'Description A'],
            ['name' => '', 'description' => 'Description B'], // Empty name
            ['name' => null, 'description' => 'Description C'], // Null name
            ['name' => 'Product D', 'description' => 'Description D']
        ];

        $duplicates = $this->findDuplicateRecords($records, ['name']);

        $this->assertCount(1, $duplicates); // Empty and null are considered duplicates
    }

    private function findDuplicateRecords(array $records, array $fields): array
    {
        $seen = [];
        $duplicates = [];

        foreach ($records as $record) {
            $key = '';
            foreach ($fields as $field) {
                $key .= ($record[$field] ?? '') . '|';
            }

            if (isset($seen[$key])) {
                $duplicates[] = $record;
            } else {
                $seen[$key] = true;
            }
        }

        return $duplicates;
    }

    private function validateUniqueConstraints(array $data, array $existingData, array $fields): bool
    {
        foreach ($existingData as $existing) {
            $isDuplicate = true;
            foreach ($fields as $field) {
                if (($data[$field] ?? '') !== ($existing[$field] ?? '')) {
                    $isDuplicate = false;
                    break;
                }
            }
            if ($isDuplicate) {
                return false;
            }
        }

        return true;
    }

    private function calculateUniquenessPercentage(array $records, string $field): float
    {
        $values = array_column($records, $field);
        $uniqueValues = array_unique($values);

        return (count($uniqueValues) / count($values)) * 100;
    }

    private function findNearDuplicates(array $records, string $field, float $threshold): array
    {
        $nearDuplicates = [];

        for ($i = 0; $i < count($records); $i++) {
            for ($j = $i + 1; $j < count($records); $j++) {
                $similarity = $this->calculateStringSimilarity(
                    $records[$i][$field],
                    $records[$j][$field]
                );

                if ($similarity >= $threshold) {
                    $nearDuplicates[] = [$records[$i], $records[$j]];
                }
            }
        }

        return $nearDuplicates;
    }

    private function validatePrimaryKeyUniqueness(array $records, string $keyField): bool
    {
        $keys = array_column($records, $keyField);
        return count($keys) === count(array_unique($keys));
    }

    private function findCaseInsensitiveDuplicates(array $records, string $field): array
    {
        $seen = [];
        $duplicates = [];

        foreach ($records as $record) {
            $normalizedValue = strtolower($record[$field] ?? '');

            if (isset($seen[$normalizedValue])) {
                $duplicates[] = $record;
            } else {
                $seen[$normalizedValue] = true;
            }
        }

        return $duplicates;
    }

    private function findFuzzyDuplicates(array $records, string $field, float $threshold): array
    {
        $fuzzyDuplicates = [];

        for ($i = 0; $i < count($records); $i++) {
            for ($j = $i + 1; $j < count($records); $j++) {
                $similarity = $this->calculateStringSimilarity(
                    $records[$i][$field],
                    $records[$j][$field]
                );

                if ($similarity >= $threshold) {
                    $fuzzyDuplicates[] = [$records[$i], $records[$j]];
                }
            }
        }

        return $fuzzyDuplicates;
    }

    private function validateCompositeKeyUniqueness(array $records, array $keyFields): bool
    {
        $seen = [];

        foreach ($records as $record) {
            $key = '';
            foreach ($keyFields as $field) {
                $key .= ($record[$field] ?? '') . '|';
            }

            if (isset($seen[$key])) {
                return false;
            }

            $seen[$key] = true;
        }

        return true;
    }

    private function calculateDataDiversityScore(array $data, string $field): float
    {
        $values = array_column($data, $field);
        $uniqueValues = array_unique($values);
        $totalValues = count($values);

        if ($totalValues === 0) {
            return 0;
        }

        return count($uniqueValues) / $totalValues;
    }

    private function calculateStringSimilarity(string $str1, string $str2): float
    {
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));

        if ($str1 === $str2) {
            return 1.0;
        }

        $len1 = strlen($str1);
        $len2 = strlen($str2);

        if ($len1 === 0 || $len2 === 0) {
            return 0.0;
        }

        $maxLen = max($len1, $len2);
        $distance = levenshtein($str1, $str2);

        return 1 - ($distance / $maxLen);
    }
}
