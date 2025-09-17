<?php

namespace Tests\Unit\DataQuality;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DataValidationTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_validates_required_field_presence(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00, 'description' => 'A great product'],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00, 'description' => 'Another great product']
        ];

        $requiredFields = ['id', 'name', 'price', 'description'];
        $this->assertTrue($this->validateRequiredFieldPresence($data, $requiredFields));
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_missing_required_fields(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00], // Missing description
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00, 'description' => 'Another great product']
        ];

        $requiredFields = ['id', 'name', 'price', 'description'];
        $this->assertFalse($this->validateRequiredFieldPresence($data, $requiredFields));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_data_type_constraints(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00, 'active' => true],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.50, 'active' => false]
        ];

        $typeConstraints = [
            'id' => 'integer',
            'name' => 'string',
            'price' => 'float',
            'active' => 'boolean'
        ];

        $this->assertTrue($this->validateDataTypeConstraints($data, $typeConstraints));
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_data_type_violations(): void
    {
        $data = [
            ['id' => '1', 'name' => 'Product A', 'price' => 100.00, 'active' => true], // ID should be integer
            ['id' => 2, 'name' => 'Product B', 'price' => '200.50', 'active' => false] // Price should be float
        ];

        $typeConstraints = [
            'id' => 'integer',
            'name' => 'string',
            'price' => 'float',
            'active' => 'boolean'
        ];

        $this->assertFalse($this->validateDataTypeConstraints($data, $typeConstraints));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_range_constraints(): void
    {
        $data = [
            ['id' => 1, 'age' => 25, 'score' => 85, 'percentage' => 75.5],
            ['id' => 2, 'age' => 30, 'score' => 92, 'percentage' => 88.0]
        ];

        $rangeConstraints = [
            'age' => ['min' => 18, 'max' => 65],
            'score' => ['min' => 0, 'max' => 100],
            'percentage' => ['min' => 0.0, 'max' => 100.0]
        ];

        $this->assertTrue($this->validateRangeConstraints($data, $rangeConstraints));
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_range_constraint_violations(): void
    {
        $data = [
            ['id' => 1, 'age' => 15, 'score' => 85, 'percentage' => 75.5], // Age below minimum
            ['id' => 2, 'age' => 30, 'score' => 105, 'percentage' => 88.0] // Score above maximum
        ];

        $rangeConstraints = [
            'age' => ['min' => 18, 'max' => 65],
            'score' => ['min' => 0, 'max' => 100],
            'percentage' => ['min' => 0.0, 'max' => 100.0]
        ];

        $this->assertFalse($this->validateRangeConstraints($data, $rangeConstraints));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_format_constraints(): void
    {
        $data = [
            ['id' => 1, 'email' => 'user@example.com', 'phone' => '+1-555-123-4567', 'url' => 'https://example.com'],
            ['id' => 2, 'email' => 'admin@test.org', 'phone' => '+44-20-7946-0958', 'url' => 'http://test.com']
        ];

        $formatConstraints = [
            'email' => 'email',
            'phone' => 'phone',
            'url' => 'url'
        ];

        $this->assertTrue($this->validateFormatConstraints($data, $formatConstraints));
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_format_constraint_violations(): void
    {
        $data = [
            ['id' => 1, 'email' => 'invalid-email', 'phone' => '123', 'url' => 'not-a-url'],
            ['id' => 2, 'email' => 'admin@test.org', 'phone' => '+44-20-7946-0958', 'url' => 'http://test.com']
        ];

        $formatConstraints = [
            'email' => 'email',
            'phone' => 'phone',
            'url' => 'url'
        ];

        $this->assertFalse($this->validateFormatConstraints($data, $formatConstraints));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_length_constraints(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Product A', 'description' => 'A short description'],
            ['id' => 2, 'name' => 'Product B', 'description' => 'A longer description that meets requirements']
        ];

        $lengthConstraints = [
            'name' => ['min' => 3, 'max' => 50],
            'description' => ['min' => 10, 'max' => 500]
        ];

        $this->assertTrue($this->validateLengthConstraints($data, $lengthConstraints));
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_length_constraint_violations(): void
    {
        $data = [
            ['id' => 1, 'name' => 'AB', 'description' => 'A short description'], // Name too short
            ['id' => 2, 'name' => 'Product B', 'description' => 'Short'] // Description too short
        ];

        $lengthConstraints = [
            'name' => ['min' => 3, 'max' => 50],
            'description' => ['min' => 10, 'max' => 500]
        ];

        $this->assertFalse($this->validateLengthConstraints($data, $lengthConstraints));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_enum_constraints(): void
    {
        $data = [
            ['id' => 1, 'status' => 'active', 'category' => 'electronics'],
            ['id' => 2, 'status' => 'inactive', 'category' => 'clothing']
        ];

        $enumConstraints = [
            'status' => ['active', 'inactive', 'pending'],
            'category' => ['electronics', 'clothing', 'books', 'home']
        ];

        $this->assertTrue($this->validateEnumConstraints($data, $enumConstraints));
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_enum_constraint_violations(): void
    {
        $data = [
            ['id' => 1, 'status' => 'active', 'category' => 'electronics'],
            ['id' => 2, 'status' => 'invalid_status', 'category' => 'clothing'] // Invalid status
        ];

        $enumConstraints = [
            'status' => ['active', 'inactive', 'pending'],
            'category' => ['electronics', 'clothing', 'books', 'home']
        ];

        $this->assertFalse($this->validateEnumConstraints($data, $enumConstraints));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_uniqueness_constraints(): void
    {
        $data = [
            ['id' => 1, 'email' => 'user1@example.com', 'username' => 'user1'],
            ['id' => 2, 'email' => 'user2@example.com', 'username' => 'user2']
        ];

        $uniqueFields = ['email', 'username'];
        $this->assertTrue($this->validateUniquenessConstraints($data, $uniqueFields));
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_uniqueness_constraint_violations(): void
    {
        $data = [
            ['id' => 1, 'email' => 'user@example.com', 'username' => 'user1'],
            ['id' => 2, 'email' => 'user@example.com', 'username' => 'user2'] // Duplicate email
        ];

        $uniqueFields = ['email', 'username'];
        $this->assertFalse($this->validateUniquenessConstraints($data, $uniqueFields));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_referential_integrity_constraints(): void
    {
        $parentData = [
            ['id' => 1, 'name' => 'Category A'],
            ['id' => 2, 'name' => 'Category B']
        ];

        $childData = [
            ['id' => 1, 'name' => 'Product 1', 'category_id' => 1],
            ['id' => 2, 'name' => 'Product 2', 'category_id' => 2]
        ];

        $this->assertTrue($this->validateReferentialIntegrityConstraints($parentData, $childData, 'id', 'category_id'));
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_referential_integrity_violations(): void
    {
        $parentData = [
            ['id' => 1, 'name' => 'Category A'],
            ['id' => 2, 'name' => 'Category B']
        ];

        $childData = [
            ['id' => 1, 'name' => 'Product 1', 'category_id' => 1],
            ['id' => 2, 'name' => 'Product 2', 'category_id' => 3] // category_id 3 doesn't exist
        ];

        $this->assertFalse($this->validateReferentialIntegrityConstraints($parentData, $childData, 'id', 'category_id'));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_business_rule_constraints(): void
    {
        $data = [
            ['id' => 1, 'order_amount' => 100.00, 'discount_percentage' => 10.0, 'customer_type' => 'premium'],
            ['id' => 2, 'order_amount' => 200.00, 'discount_percentage' => 15.0, 'customer_type' => 'regular']
        ];

        $businessRules = [
            'min_order_amount' => 50.00,
            'max_discount_percentage' => 20.0,
            'premium_discount_limit' => 25.0
        ];

        $this->assertTrue($this->validateBusinessRuleConstraints($data, $businessRules));
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_business_rule_violations(): void
    {
        $data = [
            ['id' => 1, 'order_amount' => 25.00, 'discount_percentage' => 10.0, 'customer_type' => 'premium'], // Below min order
            ['id' => 2, 'order_amount' => 200.00, 'discount_percentage' => 30.0, 'customer_type' => 'regular'] // Exceeds max discount
        ];

        $businessRules = [
            'min_order_amount' => 50.00,
            'max_discount_percentage' => 20.0,
            'premium_discount_limit' => 25.0
        ];

        $this->assertFalse($this->validateBusinessRuleConstraints($data, $businessRules));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_cross_field_constraints(): void
    {
        $data = [
            ['id' => 1, 'start_date' => '2024-01-01', 'end_date' => '2024-01-31', 'start_time' => '09:00', 'end_time' => '17:00'],
            ['id' => 2, 'start_date' => '2024-02-01', 'end_date' => '2024-02-29', 'start_time' => '10:00', 'end_time' => '18:00']
        ];

        $this->assertTrue($this->validateCrossFieldConstraints($data));
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_cross_field_constraint_violations(): void
    {
        $data = [
            ['id' => 1, 'start_date' => '2024-01-01', 'end_date' => '2024-01-31', 'start_time' => '09:00', 'end_time' => '17:00'],
            ['id' => 2, 'start_date' => '2024-02-01', 'end_date' => '2024-01-15', 'start_time' => '10:00', 'end_time' => '18:00'] // end_date before start_date
        ];

        $this->assertFalse($this->validateCrossFieldConstraints($data));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_data_consistency_constraints(): void
    {
        $data = [
            ['id' => 1, 'price' => 100.00, 'tax_rate' => 0.10, 'tax_amount' => 10.00, 'total' => 110.00],
            ['id' => 2, 'price' => 200.00, 'tax_rate' => 0.15, 'tax_amount' => 30.00, 'total' => 230.00]
        ];

        $this->assertTrue($this->validateDataConsistencyConstraints($data));
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_data_consistency_violations(): void
    {
        $data = [
            ['id' => 1, 'price' => 100.00, 'tax_rate' => 0.10, 'tax_amount' => 10.00, 'total' => 110.00],
            ['id' => 2, 'price' => 200.00, 'tax_rate' => 0.15, 'tax_amount' => 30.00, 'total' => 250.00] // Wrong total
        ];

        $this->assertFalse($this->validateDataConsistencyConstraints($data));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_null_constraints(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Product A', 'description' => 'A great product', 'optional_field' => null],
            ['id' => 2, 'name' => 'Product B', 'description' => 'Another great product', 'optional_field' => 'Some value']
        ];

        $nullableFields = ['optional_field'];
        $this->assertTrue($this->validateNullConstraints($data, $nullableFields));
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_null_constraint_violations(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Product A', 'description' => null, 'optional_field' => null], // description should not be null
            ['id' => 2, 'name' => 'Product B', 'description' => 'Another great product', 'optional_field' => 'Some value']
        ];

        $nullableFields = ['optional_field'];
        $this->assertFalse($this->validateNullConstraints($data, $nullableFields));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_pattern_constraints(): void
    {
        $data = [
            ['id' => 1, 'sku' => 'SKU-123456', 'product_code' => 'PROD-ABC-001'],
            ['id' => 2, 'sku' => 'SKU-789012', 'product_code' => 'PROD-XYZ-002']
        ];

        $patternConstraints = [
            'sku' => '/^SKU-\d{6}$/',
            'product_code' => '/^PROD-[A-Z]{3}-\d{3}$/'
        ];

        $this->assertTrue($this->validatePatternConstraints($data, $patternConstraints));
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_pattern_constraint_violations(): void
    {
        $data = [
            ['id' => 1, 'sku' => 'SKU-123456', 'product_code' => 'PROD-ABC-001'],
            ['id' => 2, 'sku' => 'INVALID-SKU', 'product_code' => 'PROD-XYZ-002'] // Invalid SKU pattern
        ];

        $patternConstraints = [
            'sku' => '/^SKU-\d{6}$/',
            'product_code' => '/^PROD-[A-Z]{3}-\d{3}$/'
        ];

        $this->assertFalse($this->validatePatternConstraints($data, $patternConstraints));
    }

    private function validateRequiredFieldPresence(array $data, array $requiredFields): bool
    {
        foreach ($data as $record) {
            foreach ($requiredFields as $field) {
                if (!array_key_exists($field, $record) || $record[$field] === null || $record[$field] === '') {
                    return false;
                }
            }
        }
        return true;
    }

    private function validateDataTypeConstraints(array $data, array $typeConstraints): bool
    {
        foreach ($data as $record) {
            foreach ($typeConstraints as $field => $expectedType) {
                if (!array_key_exists($field, $record)) {
                    continue;
                }

                $actualType = gettype($record[$field]);
                if ($expectedType === 'integer' && $actualType !== 'integer') {
                    return false;
                }
                if ($expectedType === 'string' && $actualType !== 'string') {
                    return false;
                }
                if ($expectedType === 'float' && $actualType !== 'double') {
                    return false;
                }
                if ($expectedType === 'boolean' && $actualType !== 'boolean') {
                    return false;
                }
            }
        }
        return true;
    }

    private function validateRangeConstraints(array $data, array $rangeConstraints): bool
    {
        foreach ($data as $record) {
            foreach ($rangeConstraints as $field => $constraints) {
                if (!array_key_exists($field, $record)) {
                    continue;
                }

                $value = $record[$field];

                if (isset($constraints['min']) && $value < $constraints['min']) {
                    return false;
                }

                if (isset($constraints['max']) && $value > $constraints['max']) {
                    return false;
                }
            }
        }
        return true;
    }

    private function validateFormatConstraints(array $data, array $formatConstraints): bool
    {
        foreach ($data as $record) {
            foreach ($formatConstraints as $field => $format) {
                if (!array_key_exists($field, $record)) {
                    continue;
                }

                $value = $record[$field];

                switch ($format) {
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            return false;
                        }
                        break;
                    case 'phone':
                        if (!preg_match('/^[\+]?[1-9][\d]{0,15}$/', preg_replace('/[^\d+]/', '', $value))) {
                            return false;
                        }
                        break;
                    case 'url':
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            return false;
                        }
                        break;
                }
            }
        }
        return true;
    }

    private function validateLengthConstraints(array $data, array $lengthConstraints): bool
    {
        foreach ($data as $record) {
            foreach ($lengthConstraints as $field => $constraints) {
                if (!array_key_exists($field, $record)) {
                    continue;
                }

                $length = strlen($record[$field]);

                if (isset($constraints['min']) && $length < $constraints['min']) {
                    return false;
                }

                if (isset($constraints['max']) && $length > $constraints['max']) {
                    return false;
                }
            }
        }
        return true;
    }

    private function validateEnumConstraints(array $data, array $enumConstraints): bool
    {
        foreach ($data as $record) {
            foreach ($enumConstraints as $field => $allowedValues) {
                if (!array_key_exists($field, $record)) {
                    continue;
                }

                if (!in_array($record[$field], $allowedValues)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function validateUniquenessConstraints(array $data, array $uniqueFields): bool
    {
        foreach ($uniqueFields as $field) {
            $values = array_column($data, $field);
            if (count($values) !== count(array_unique($values))) {
                return false;
            }
        }
        return true;
    }

    private function validateReferentialIntegrityConstraints(array $parentData, array $childData, string $parentKey, string $foreignKey): bool
    {
        $parentIds = array_column($parentData, $parentKey);

        foreach ($childData as $record) {
            if (!in_array($record[$foreignKey], $parentIds)) {
                return false;
            }
        }

        return true;
    }

    private function validateBusinessRuleConstraints(array $data, array $businessRules): bool
    {
        foreach ($data as $record) {
            if (isset($businessRules['min_order_amount']) && $record['order_amount'] < $businessRules['min_order_amount']) {
                return false;
            }

            if (isset($businessRules['max_discount_percentage']) && $record['discount_percentage'] > $businessRules['max_discount_percentage']) {
                return false;
            }

            if (isset($businessRules['premium_discount_limit']) && $record['customer_type'] === 'premium' && $record['discount_percentage'] > $businessRules['premium_discount_limit']) {
                return false;
            }
        }
        return true;
    }

    private function validateCrossFieldConstraints(array $data): bool
    {
        foreach ($data as $record) {
            if (isset($record['start_date'], $record['end_date'])) {
                if (strtotime($record['end_date']) < strtotime($record['start_date'])) {
                    return false;
                }
            }

            if (isset($record['start_time'], $record['end_time'])) {
                if (strtotime($record['end_time']) < strtotime($record['start_time'])) {
                    return false;
                }
            }
        }
        return true;
    }

    private function validateDataConsistencyConstraints(array $data): bool
    {
        foreach ($data as $record) {
            if (isset($record['price'], $record['tax_rate'], $record['tax_amount'], $record['total'])) {
                $expectedTaxAmount = $record['price'] * $record['tax_rate'];
                $expectedTotal = $record['price'] + $expectedTaxAmount;

                if (abs($record['tax_amount'] - $expectedTaxAmount) > 0.01) {
                    return false;
                }

                if (abs($record['total'] - $expectedTotal) > 0.01) {
                    return false;
                }
            }
        }
        return true;
    }

    private function validateNullConstraints(array $data, array $nullableFields): bool
    {
        foreach ($data as $record) {
            foreach ($record as $field => $value) {
                if (!in_array($field, $nullableFields) && ($value === null || $value === '')) {
                    return false;
                }
            }
        }
        return true;
    }

    private function validatePatternConstraints(array $data, array $patternConstraints): bool
    {
        foreach ($data as $record) {
            foreach ($patternConstraints as $field => $pattern) {
                if (!array_key_exists($field, $record)) {
                    continue;
                }

                if (!preg_match($pattern, $record[$field])) {
                    return false;
                }
            }
        }
        return true;
    }
}
