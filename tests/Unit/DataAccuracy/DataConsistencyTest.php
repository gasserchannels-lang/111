<?php

namespace Tests\Unit\DataAccuracy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DataConsistencyTest extends TestCase
{
    #[Test]
    public function it_validates_data_consistency_across_sources(): void
    {
        $source1Data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00],
        ];

        $source2Data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00],
        ];

        $this->assertTrue($this->validateDataConsistencyAcrossSources($source1Data, $source2Data));
    }

    #[Test]
    public function it_detects_data_inconsistencies_across_sources(): void
    {
        $source1Data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00],
        ];

        $source2Data = [
            ['id' => 1, 'name' => 'Product A', 'price' => 105.00], // Different price
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00],
        ];

        $this->assertFalse($this->validateDataConsistencyAcrossSources($source1Data, $source2Data));
    }

    #[Test]
    public function it_validates_currency_consistency(): void
    {
        $consistentData = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00, 'currency' => 'USD'],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00, 'currency' => 'USD'],
        ];

        $inconsistentData = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00, 'currency' => 'USD'],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00, 'currency' => 'EUR'],
        ];

        $this->assertTrue($this->validateCurrencyConsistency($consistentData));
        $this->assertFalse($this->validateCurrencyConsistency($inconsistentData));
    }

    #[Test]
    public function it_validates_date_format_consistency(): void
    {
        $consistentDates = [
            ['id' => 1, 'created_at' => '2024-01-15 10:30:00'],
            ['id' => 2, 'created_at' => '2024-01-16 14:45:00'],
        ];

        $inconsistentDates = [
            ['id' => 1, 'created_at' => '2024-01-15 10:30:00'],
            ['id' => 2, 'created_at' => '15/01/2024 14:45:00'], // Different format
        ];

        $this->assertTrue($this->validateDateFormatConsistency($consistentDates, 'created_at'));
        $this->assertFalse($this->validateDateFormatConsistency($inconsistentDates, 'created_at'));
    }

    #[Test]
    public function it_validates_naming_convention_consistency(): void
    {
        $consistentNames = [
            ['id' => 1, 'product_name' => 'Laptop Computer'],
            ['id' => 2, 'product_name' => 'Wireless Mouse'],
            ['id' => 3, 'product_name' => 'USB Cable'],
        ];

        $inconsistentNames = [
            ['id' => 1, 'product_name' => 'Laptop Computer'],
            ['id' => 2, 'product_name' => 'wireless_mouse'], // Different separator
            ['id' => 3, 'product_name' => 'USB123CABLE'], // Different format with numbers
        ];

        $this->assertTrue($this->validateNamingConventionConsistency($consistentNames, 'product_name'));
        $this->assertFalse($this->validateNamingConventionConsistency($inconsistentNames, 'product_name'));
    }

    #[Test]
    public function it_validates_data_type_consistency(): void
    {
        $consistentTypes = [
            ['id' => 1, 'price' => 100.00, 'quantity' => 5],
            ['id' => 2, 'price' => 200.50, 'quantity' => 10],
        ];

        $inconsistentTypes = [
            ['id' => 1, 'price' => 100.00, 'quantity' => 5],
            ['id' => 2, 'price' => '200.50', 'quantity' => '10'], // String instead of numeric
        ];

        $this->assertTrue($this->validateDataTypeConsistency($consistentTypes));
        $this->assertFalse($this->validateDataTypeConsistency($inconsistentTypes));
    }

    #[Test]
    public function it_validates_reference_data_consistency(): void
    {
        $referenceData = [
            'categories' => [
                ['id' => 1, 'name' => 'Electronics'],
                ['id' => 2, 'name' => 'Clothing'],
            ],
            'statuses' => [
                ['id' => 1, 'name' => 'Active'],
                ['id' => 2, 'name' => 'Inactive'],
            ],
        ];

        $transactionData = [
            ['id' => 1, 'category_id' => 1, 'status_id' => 1],
            ['id' => 2, 'category_id' => 2, 'status_id' => 1],
        ];

        $this->assertTrue($this->validateReferenceDataConsistency($referenceData, $transactionData));
    }

    #[Test]
    public function it_validates_calculation_consistency(): void
    {
        $consistentCalculations = [
            ['id' => 1, 'price' => 100.00, 'tax_rate' => 0.10, 'tax_amount' => 10.00, 'total' => 110.00],
            ['id' => 2, 'price' => 200.00, 'tax_rate' => 0.15, 'tax_amount' => 30.00, 'total' => 230.00],
        ];

        $inconsistentCalculations = [
            ['id' => 1, 'price' => 100.00, 'tax_rate' => 0.10, 'tax_amount' => 10.00, 'total' => 110.00],
            ['id' => 2, 'price' => 200.00, 'tax_rate' => 0.15, 'tax_amount' => 30.00, 'total' => 250.00], // Wrong total
        ];

        $this->assertTrue($this->validateCalculationConsistency($consistentCalculations));
        $this->assertFalse($this->validateCalculationConsistency($inconsistentCalculations));
    }

    #[Test]
    public function it_validates_enum_value_consistency(): void
    {
        $validEnumValues = ['Active', 'Inactive', 'Pending', 'Cancelled'];

        $consistentEnumData = [
            ['id' => 1, 'status' => 'Active'],
            ['id' => 2, 'status' => 'Inactive'],
            ['id' => 3, 'status' => 'Pending'],
        ];

        $inconsistentEnumData = [
            ['id' => 1, 'status' => 'Active'],
            ['id' => 2, 'status' => 'Inactive'],
            ['id' => 3, 'status' => 'InvalidStatus'], // Not in valid values
        ];

        $this->assertTrue($this->validateEnumValueConsistency($consistentEnumData, 'status', $validEnumValues));
        $this->assertFalse($this->validateEnumValueConsistency($inconsistentEnumData, 'status', $validEnumValues));
    }

    #[Test]
    public function it_validates_relationship_consistency(): void
    {
        $parentData = [
            ['id' => 1, 'name' => 'Category A'],
            ['id' => 2, 'name' => 'Category B'],
        ];

        $childData = [
            ['id' => 1, 'name' => 'Product 1', 'category_id' => 1],
            ['id' => 2, 'name' => 'Product 2', 'category_id' => 2],
            ['id' => 3, 'name' => 'Product 3', 'category_id' => 1],
        ];

        $this->assertTrue($this->validateRelationshipConsistency($parentData, $childData, 'id', 'category_id'));
    }

    #[Test]
    public function it_validates_temporal_consistency(): void
    {
        $temporalData = [
            ['id' => 1, 'created_at' => '2024-01-15 10:00:00', 'updated_at' => '2024-01-15 11:00:00'],
            ['id' => 2, 'created_at' => '2024-01-16 09:00:00', 'updated_at' => '2024-01-16 10:00:00'],
        ];

        $this->assertTrue($this->validateTemporalConsistency($temporalData));
    }

    #[Test]
    public function it_detects_temporal_inconsistencies(): void
    {
        $inconsistentTemporalData = [
            ['id' => 1, 'created_at' => '2024-01-15 10:00:00', 'updated_at' => '2024-01-15 09:00:00'], // updated before created
            ['id' => 2, 'created_at' => '2024-01-16 09:00:00', 'updated_at' => '2024-01-16 10:00:00'],
        ];

        $this->assertFalse($this->validateTemporalConsistency($inconsistentTemporalData));
    }

    #[Test]
    public function it_validates_business_rule_consistency(): void
    {
        $businessRules = [
            'min_order_amount' => 50.00,
            'max_discount_percentage' => 20.0,
            'required_fields' => ['name', 'email', 'phone'],
        ];

        $consistentData = [
            ['id' => 1, 'order_amount' => 100.00, 'discount_percentage' => 10.0, 'name' => 'John', 'email' => 'john@example.com', 'phone' => '123-456-7890'],
            ['id' => 2, 'order_amount' => 75.00, 'discount_percentage' => 15.0, 'name' => 'Jane', 'email' => 'jane@example.com', 'phone' => '987-654-3210'],
        ];

        $inconsistentData = [
            ['id' => 1, 'order_amount' => 25.00, 'discount_percentage' => 10.0, 'name' => 'John', 'email' => 'john@example.com', 'phone' => '123-456-7890'], // Below min order
            ['id' => 2, 'order_amount' => 75.00, 'discount_percentage' => 25.0, 'name' => 'Jane', 'email' => 'jane@example.com', 'phone' => '987-654-3210'], // Exceeds max discount
        ];

        $this->assertTrue($this->validateBusinessRuleConsistency($consistentData, $businessRules));
        $this->assertFalse($this->validateBusinessRuleConsistency($inconsistentData, $businessRules));
    }

    #[Test]
    public function it_validates_data_version_consistency(): void
    {
        $versionedData = [
            ['id' => 1, 'version' => 1, 'data' => 'Initial version'],
            ['id' => 1, 'version' => 2, 'data' => 'Updated version'],
            ['id' => 1, 'version' => 3, 'data' => 'Latest version'],
        ];

        $this->assertTrue($this->validateDataVersionConsistency($versionedData));
    }

    #[Test]
    public function it_validates_cross_table_consistency(): void
    {
        $orders = [
            ['id' => 1, 'customer_id' => 1, 'total_amount' => 150.00],
            ['id' => 2, 'customer_id' => 2, 'total_amount' => 200.00],
        ];

        $orderItems = [
            ['id' => 1, 'order_id' => 1, 'quantity' => 2, 'unit_price' => 50.00, 'total_price' => 100.00],
            ['id' => 2, 'order_id' => 1, 'quantity' => 1, 'unit_price' => 50.00, 'total_price' => 50.00],
            ['id' => 3, 'order_id' => 2, 'quantity' => 1, 'unit_price' => 200.00, 'total_price' => 200.00],
        ];

        $this->assertTrue($this->validateCrossTableConsistency($orders, $orderItems));
    }

    #[Test]
    public function it_validates_data_synchronization_consistency(): void
    {
        $masterData = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00, 'last_sync' => '2024-01-15 10:00:00'],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00, 'last_sync' => '2024-01-15 10:00:00'],
        ];

        $replicaData = [
            ['id' => 1, 'name' => 'Product A', 'price' => 100.00, 'last_sync' => '2024-01-15 10:00:00'],
            ['id' => 2, 'name' => 'Product B', 'price' => 200.00, 'last_sync' => '2024-01-15 10:00:00'],
        ];

        $this->assertTrue($this->validateDataSynchronizationConsistency($masterData, $replicaData));
    }

    private function validateDataConsistencyAcrossSources(array $source1Data, array $source2Data): bool
    {
        if (count($source1Data) !== count($source2Data)) {
            return false;
        }

        foreach ($source1Data as $index => $record1) {
            if (! isset($source2Data[$index])) {
                return false;
            }

            $record2 = $source2Data[$index];

            foreach ($record1 as $key => $value) {
                if (! array_key_exists($key, $record2) || $record2[$key] !== $value) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateCurrencyConsistency(array $data): bool
    {
        $currencies = array_column($data, 'currency');

        return count(array_unique($currencies)) === 1;
    }

    private function validateDateFormatConsistency(array $data, string $dateField): bool
    {
        $expectedFormat = 'Y-m-d H:i:s';

        foreach ($data as $record) {
            if (isset($record[$dateField])) {
                $date = \DateTime::createFromFormat($expectedFormat, $record[$dateField]);
                if (! $date || $date->format($expectedFormat) !== $record[$dateField]) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateNamingConventionConsistency(array $data, string $nameField): bool
    {
        $patterns = [];

        foreach ($data as $record) {
            if (isset($record[$nameField])) {
                $name = $record[$nameField];
                $pattern = $this->extractNamingPattern($name);
                $patterns[] = $pattern;
            }
        }

        return count(array_unique($patterns)) === 1;
    }

    private function extractNamingPattern(string $name): string
    {
        // Extract pattern based on case, spaces, and special characters
        // Normalize the pattern to focus on structure rather than exact length
        $pattern = preg_replace('/[a-zA-Z]+/', 'W', $name); // Replace words with W
        $pattern = preg_replace('/[0-9]+/', 'N', $pattern); // Replace numbers with N
        $pattern = preg_replace('/[^WN]/', 'S', $pattern); // Replace other chars with S

        return $pattern;
    }

    private function validateDataTypeConsistency(array $data): bool
    {
        $fieldTypes = [];

        foreach ($data as $record) {
            foreach ($record as $field => $value) {
                if (! isset($fieldTypes[$field])) {
                    $fieldTypes[$field] = gettype($value);
                } elseif ($fieldTypes[$field] !== gettype($value)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateReferenceDataConsistency(array $referenceData, array $transactionData): bool
    {
        foreach ($transactionData as $record) {
            if (isset($record['category_id'])) {
                $categoryIds = array_column($referenceData['categories'], 'id');
                if (! in_array($record['category_id'], $categoryIds)) {
                    return false;
                }
            }

            if (isset($record['status_id'])) {
                $statusIds = array_column($referenceData['statuses'], 'id');
                if (! in_array($record['status_id'], $statusIds)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateCalculationConsistency(array $data): bool
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

    private function validateEnumValueConsistency(array $data, string $field, array $validValues): bool
    {
        foreach ($data as $record) {
            if (isset($record[$field]) && ! in_array($record[$field], $validValues)) {
                return false;
            }
        }

        return true;
    }

    private function validateRelationshipConsistency(array $parentData, array $childData, string $parentKey, string $foreignKey): bool
    {
        $parentIds = array_column($parentData, $parentKey);

        foreach ($childData as $record) {
            if (isset($record[$foreignKey]) && ! in_array($record[$foreignKey], $parentIds)) {
                return false;
            }
        }

        return true;
    }

    private function validateTemporalConsistency(array $data): bool
    {
        foreach ($data as $record) {
            if (isset($record['created_at'], $record['updated_at'])) {
                $createdAt = strtotime($record['created_at']);
                $updatedAt = strtotime($record['updated_at']);

                if ($updatedAt < $createdAt) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateBusinessRuleConsistency(array $data, array $businessRules): bool
    {
        foreach ($data as $record) {
            // Check minimum order amount
            if (isset($businessRules['min_order_amount']) && isset($record['order_amount'])) {
                if ($record['order_amount'] < $businessRules['min_order_amount']) {
                    return false;
                }
            }

            // Check maximum discount percentage
            if (isset($businessRules['max_discount_percentage']) && isset($record['discount_percentage'])) {
                if ($record['discount_percentage'] > $businessRules['max_discount_percentage']) {
                    return false;
                }
            }

            // Check required fields
            if (isset($businessRules['required_fields'])) {
                foreach ($businessRules['required_fields'] as $field) {
                    if (! isset($record[$field]) || empty($record[$field])) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function validateDataVersionConsistency(array $data): bool
    {
        $versions = array_column($data, 'version');
        sort($versions);

        // Check if versions are sequential
        for ($i = 0; $i < count($versions) - 1; $i++) {
            if ($versions[$i + 1] - $versions[$i] !== 1) {
                return false;
            }
        }

        return true;
    }

    private function validateCrossTableConsistency(array $orders, array $orderItems): bool
    {
        foreach ($orders as $order) {
            $orderId = $order['id'];
            $orderItemsForOrder = array_filter($orderItems, fn ($item) => $item['order_id'] === $orderId);

            $calculatedTotal = 0;
            foreach ($orderItemsForOrder as $item) {
                $calculatedTotal += $item['total_price'];
            }

            if (abs($order['total_amount'] - $calculatedTotal) > 0.01) {
                return false;
            }
        }

        return true;
    }

    private function validateDataSynchronizationConsistency(array $masterData, array $replicaData): bool
    {
        if (count($masterData) !== count($replicaData)) {
            return false;
        }

        foreach ($masterData as $index => $masterRecord) {
            if (! isset($replicaData[$index])) {
                return false;
            }

            $replicaRecord = $replicaData[$index];

            // Compare all fields except last_sync
            foreach ($masterRecord as $key => $value) {
                if ($key !== 'last_sync' && (! array_key_exists($key, $replicaRecord) || $replicaRecord[$key] !== $value)) {
                    return false;
                }
            }
        }

        return true;
    }
}
