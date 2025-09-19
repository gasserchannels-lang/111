<?php

namespace Tests\Unit\DataAccuracy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DataIntegrityTest extends TestCase
{
    #[Test]
    public function it_validates_referential_integrity(): void
    {
        $users = [
            ['id' => 1, 'name' => 'John Doe'],
            ['id' => 2, 'name' => 'Jane Smith'],
            ['id' => 3, 'name' => 'Bob Johnson'],
        ];

        $orders = [
            ['id' => 1, 'user_id' => 1, 'total' => 100.00],
            ['id' => 2, 'user_id' => 2, 'total' => 150.00],
            ['id' => 3, 'user_id' => 1, 'total' => 75.00],
        ];

        $this->assertTrue($this->validateReferentialIntegrity($users, $orders, 'id', 'user_id'));
    }

    #[Test]
    public function it_detects_referential_integrity_violations(): void
    {
        $users = [
            ['id' => 1, 'name' => 'John Doe'],
            ['id' => 2, 'name' => 'Jane Smith'],
        ];

        $orders = [
            ['id' => 1, 'user_id' => 1, 'total' => 100.00],
            ['id' => 2, 'user_id' => 3, 'total' => 150.00], // user_id 3 doesn't exist
            ['id' => 3, 'user_id' => 2, 'total' => 75.00],
        ];

        $this->assertFalse($this->validateReferentialIntegrity($users, $orders, 'id', 'user_id'));
    }

    #[Test]
    public function it_validates_data_consistency_across_tables(): void
    {
        $products = [
            ['id' => 1, 'name' => 'Laptop', 'price' => 999.99],
            ['id' => 2, 'name' => 'Mouse', 'price' => 29.99],
        ];

        $inventory = [
            ['product_id' => 1, 'quantity' => 10],
            ['product_id' => 2, 'quantity' => 50],
        ];

        $this->assertTrue($this->validateDataConsistency($products, $inventory));
    }

    #[Test]
    public function it_validates_primary_key_uniqueness(): void
    {
        $validData = [
            ['id' => 1, 'name' => 'Product 1'],
            ['id' => 2, 'name' => 'Product 2'],
            ['id' => 3, 'name' => 'Product 3'],
        ];

        $invalidData = [
            ['id' => 1, 'name' => 'Product 1'],
            ['id' => 2, 'name' => 'Product 2'],
            ['id' => 1, 'name' => 'Product 3'], // Duplicate ID
        ];

        $this->assertTrue($this->validatePrimaryKeyUniqueness($validData, 'id'));
        $this->assertFalse($this->validatePrimaryKeyUniqueness($invalidData, 'id'));
    }

    #[Test]
    public function it_validates_foreign_key_constraints(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Electronics'],
            ['id' => 2, 'name' => 'Clothing'],
        ];

        $products = [
            ['id' => 1, 'name' => 'Laptop', 'category_id' => 1],
            ['id' => 2, 'name' => 'Shirt', 'category_id' => 2],
            ['id' => 3, 'name' => 'Phone', 'category_id' => 1],
        ];

        $this->assertTrue($this->validateForeignKeyConstraints($categories, $products, 'id', 'category_id'));
    }

    #[Test]
    public function it_detects_orphaned_records(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Electronics'],
            ['id' => 2, 'name' => 'Clothing'],
        ];

        $products = [
            ['id' => 1, 'name' => 'Laptop', 'category_id' => 1],
            ['id' => 2, 'name' => 'Shirt', 'category_id' => 2],
            ['id' => 3, 'name' => 'Phone', 'category_id' => 3], // category_id 3 doesn't exist
        ];

        $orphanedRecords = $this->findOrphanedRecords($categories, $products, 'id', 'category_id');
        $this->assertCount(1, $orphanedRecords);
        $this->assertEquals(3, $orphanedRecords[0]['id']);
    }

    #[Test]
    public function it_validates_data_completeness(): void
    {
        $completeData = [
            ['id' => 1, 'name' => 'Product 1', 'price' => 100.00, 'description' => 'A great product'],
            ['id' => 2, 'name' => 'Product 2', 'price' => 200.00, 'description' => 'Another great product'],
        ];

        $incompleteData = [
            ['id' => 1, 'name' => 'Product 1', 'price' => 100.00], // Missing description
            ['id' => 2, 'name' => 'Product 2', 'price' => 200.00, 'description' => 'Another great product'],
        ];

        $requiredFields = ['id', 'name', 'price', 'description'];

        $this->assertTrue($this->validateDataCompleteness($completeData, $requiredFields));
        $this->assertFalse($this->validateDataCompleteness($incompleteData, $requiredFields));
    }

    #[Test]
    public function it_validates_data_accuracy(): void
    {
        $accurateData = [
            ['id' => 1, 'name' => 'Laptop', 'price' => 999.99, 'weight' => 2.5],
            ['id' => 2, 'name' => 'Mouse', 'price' => 29.99, 'weight' => 0.1],
        ];

        $inaccurateData = [
            ['id' => 1, 'name' => 'Laptop', 'price' => -999.99, 'weight' => 2.5], // Negative price
            ['id' => 2, 'name' => 'Mouse', 'price' => 29.99, 'weight' => -0.1], // Negative weight
        ];

        $this->assertTrue($this->validateDataAccuracy($accurateData));
        $this->assertFalse($this->validateDataAccuracy($inaccurateData));
    }

    #[Test]
    public function it_validates_data_timeliness(): void
    {
        $timelyData = [
            ['id' => 1, 'name' => 'Product 1', 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => 2, 'name' => 'Product 2', 'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))],
        ];

        $staleData = [
            ['id' => 1, 'name' => 'Product 1', 'updated_at' => date('Y-m-d H:i:s', strtotime('-1 year'))],
            ['id' => 2, 'name' => 'Product 2', 'updated_at' => date('Y-m-d H:i:s', strtotime('-6 months'))],
        ];

        $maxAge = 30; // days

        $this->assertTrue($this->validateDataTimeliness($timelyData, $maxAge));
        $this->assertFalse($this->validateDataTimeliness($staleData, $maxAge));
    }

    #[Test]
    public function it_validates_data_consistency_rules(): void
    {
        $consistentData = [
            ['id' => 1, 'name' => 'Product 1', 'price' => 100.00, 'discount' => 10.00, 'final_price' => 90.00],
            ['id' => 2, 'name' => 'Product 2', 'price' => 200.00, 'discount' => 20.00, 'final_price' => 180.00],
        ];

        $inconsistentData = [
            ['id' => 1, 'name' => 'Product 1', 'price' => 100.00, 'discount' => 10.00, 'final_price' => 95.00], // Wrong calculation
            ['id' => 2, 'name' => 'Product 2', 'price' => 200.00, 'discount' => 20.00, 'final_price' => 180.00],
        ];

        $this->assertTrue($this->validateDataConsistencyRules($consistentData));
        $this->assertFalse($this->validateDataConsistencyRules($inconsistentData));
    }

    #[Test]
    public function it_validates_data_relationships(): void
    {
        $users = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
        ];

        $orders = [
            ['id' => 1, 'user_id' => 1, 'total' => 100.00, 'status' => 'completed'],
            ['id' => 2, 'user_id' => 2, 'total' => 150.00, 'status' => 'pending'],
        ];

        $orderItems = [
            ['id' => 1, 'order_id' => 1, 'product_id' => 1, 'quantity' => 2, 'price' => 50.00],
            ['id' => 2, 'order_id' => 2, 'product_id' => 2, 'quantity' => 1, 'price' => 150.00],
        ];

        $this->assertTrue($this->validateDataRelationships($users, $orders, $orderItems));
    }

    #[Test]
    public function it_validates_data_constraints(): void
    {
        $validData = [
            ['id' => 1, 'age' => 25, 'email' => 'user@example.com', 'score' => 85],
            ['id' => 2, 'age' => 30, 'email' => 'user2@example.com', 'score' => 92],
        ];

        $invalidData = [
            ['id' => 1, 'age' => 150, 'email' => 'invalid-email', 'score' => 150], // Invalid age and email
            ['id' => 2, 'age' => 30, 'email' => 'user2@example.com', 'score' => 92],
        ];

        $constraints = [
            'age' => ['min' => 0, 'max' => 120],
            'email' => 'email',
            'score' => ['min' => 0, 'max' => 100],
        ];

        $this->assertTrue($this->validateDataConstraints($validData, $constraints));
        $this->assertFalse($this->validateDataConstraints($invalidData, $constraints));
    }

    #[Test]
    public function it_validates_data_audit_trail(): void
    {
        $auditData = [
            [
                'id' => 1,
                'action' => 'CREATE',
                'table_name' => 'products',
                'record_id' => 1,
                'old_values' => null,
                'new_values' => '{"name": "Product 1", "price": 100.00}',
                'user_id' => 1,
                'timestamp' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 2,
                'action' => 'UPDATE',
                'table_name' => 'products',
                'record_id' => 1,
                'old_values' => '{"name": "Product 1", "price": 100.00}',
                'new_values' => '{"name": "Product 1", "price": 120.00}',
                'user_id' => 1,
                'timestamp' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->assertTrue($this->validateDataAuditTrail($auditData));
    }

    #[Test]
    public function it_validates_data_backup_integrity(): void
    {
        $originalData = [
            ['id' => 1, 'name' => 'Product 1', 'price' => 100.00],
            ['id' => 2, 'name' => 'Product 2', 'price' => 200.00],
        ];

        $backupData = [
            ['id' => 1, 'name' => 'Product 1', 'price' => 100.00],
            ['id' => 2, 'name' => 'Product 2', 'price' => 200.00],
        ];

        $this->assertTrue($this->validateDataBackupIntegrity($originalData, $backupData));
    }

    #[Test]
    public function it_validates_data_encryption_integrity(): void
    {
        $sensitiveData = [
            ['id' => 1, 'name' => 'John Doe', 'ssn' => '123-45-6789'],
            ['id' => 2, 'name' => 'Jane Smith', 'ssn' => '987-65-4321'],
        ];

        $encryptedData = $this->encryptSensitiveData($sensitiveData);
        $decryptedData = $this->decryptSensitiveData($encryptedData);

        $this->assertTrue($this->validateDataEncryptionIntegrity($sensitiveData, $decryptedData));
    }

    private function validateReferentialIntegrity(array $parentData, array $childData, string $parentKey, string $foreignKey): bool
    {
        $parentIds = array_column($parentData, $parentKey);

        foreach ($childData as $child) {
            if (! in_array($child[$foreignKey], $parentIds)) {
                return false;
            }
        }

        return true;
    }

    private function validateDataConsistency(array $table1, array $table2): bool
    {
        // Check if all foreign keys in table2 exist in table1
        $table1Ids = array_column($table1, 'id');
        $table2ForeignKeys = array_column($table2, 'product_id');

        foreach ($table2ForeignKeys as $foreignKey) {
            if (! in_array($foreignKey, $table1Ids)) {
                return false;
            }
        }

        return true;
    }

    private function validatePrimaryKeyUniqueness(array $data, string $keyField): bool
    {
        $values = array_column($data, $keyField);

        return count($values) === count(array_unique($values));
    }

    private function validateForeignKeyConstraints(array $parentData, array $childData, string $parentKey, string $foreignKey): bool
    {
        $parentIds = array_column($parentData, $parentKey);
        $childForeignKeys = array_column($childData, $foreignKey);

        foreach ($childForeignKeys as $foreignKeyValue) {
            if (! in_array($foreignKeyValue, $parentIds)) {
                return false;
            }
        }

        return true;
    }

    private function findOrphanedRecords(array $parentData, array $childData, string $parentKey, string $foreignKey): array
    {
        $parentIds = array_column($parentData, $parentKey);
        $orphanedRecords = [];

        foreach ($childData as $child) {
            if (! in_array($child[$foreignKey], $parentIds)) {
                $orphanedRecords[] = $child;
            }
        }

        return $orphanedRecords;
    }

    private function validateDataCompleteness(array $data, array $requiredFields): bool
    {
        foreach ($data as $record) {
            foreach ($requiredFields as $field) {
                if (! array_key_exists($field, $record) || empty($record[$field])) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateDataAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            if (isset($record['price']) && $record['price'] < 0) {
                return false;
            }
            if (isset($record['weight']) && $record['weight'] < 0) {
                return false;
            }
        }

        return true;
    }

    private function validateDataTimeliness(array $data, int $maxAgeDays): bool
    {
        $maxAge = $maxAgeDays * 24 * 60 * 60; // Convert to seconds

        foreach ($data as $record) {
            if (isset($record['updated_at'])) {
                $lastUpdate = strtotime($record['updated_at']);
                if (time() - $lastUpdate > $maxAge) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateDataConsistencyRules(array $data): bool
    {
        foreach ($data as $record) {
            if (isset($record['price'], $record['discount'], $record['final_price'])) {
                $expectedFinalPrice = $record['price'] - $record['discount'];
                if (abs($record['final_price'] - $expectedFinalPrice) > 0.01) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateDataRelationships(array $users, array $orders, array $orderItems): bool
    {
        // Validate user-order relationships
        $userIds = array_column($users, 'id');
        $orderUserIds = array_column($orders, 'user_id');

        foreach ($orderUserIds as $userId) {
            if (! in_array($userId, $userIds)) {
                return false;
            }
        }

        // Validate order-orderItem relationships
        $orderIds = array_column($orders, 'id');
        $orderItemOrderIds = array_column($orderItems, 'order_id');

        foreach ($orderItemOrderIds as $orderId) {
            if (! in_array($orderId, $orderIds)) {
                return false;
            }
        }

        return true;
    }

    private function validateDataConstraints(array $data, array $constraints): bool
    {
        foreach ($data as $record) {
            foreach ($constraints as $field => $constraint) {
                if (! isset($record[$field])) {
                    continue;
                }

                $value = $record[$field];

                if (is_array($constraint)) {
                    if (isset($constraint['min']) && $value < $constraint['min']) {
                        return false;
                    }
                    if (isset($constraint['max']) && $value > $constraint['max']) {
                        return false;
                    }
                } elseif ($constraint === 'email') {
                    if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function validateDataAuditTrail(array $auditData): bool
    {
        $requiredFields = ['id', 'action', 'table_name', 'record_id', 'user_id', 'timestamp'];

        foreach ($auditData as $record) {
            foreach ($requiredFields as $field) {
                if (! array_key_exists($field, $record) || empty($record[$field])) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateDataBackupIntegrity(array $originalData, array $backupData): bool
    {
        if (count($originalData) !== count($backupData)) {
            return false;
        }

        foreach ($originalData as $index => $originalRecord) {
            if (! isset($backupData[$index])) {
                return false;
            }

            $backupRecord = $backupData[$index];

            foreach ($originalRecord as $key => $value) {
                if (! array_key_exists($key, $backupRecord) || $backupRecord[$key] !== $value) {
                    return false;
                }
            }
        }

        return true;
    }

    private function encryptSensitiveData(array $data): array
    {
        $encryptedData = [];

        foreach ($data as $record) {
            $encryptedRecord = $record;
            if (isset($record['ssn'])) {
                $encryptedRecord['ssn'] = base64_encode($record['ssn']);
            }
            $encryptedData[] = $encryptedRecord;
        }

        return $encryptedData;
    }

    private function decryptSensitiveData(array $encryptedData): array
    {
        $decryptedData = [];

        foreach ($encryptedData as $record) {
            $decryptedRecord = $record;
            if (isset($record['ssn'])) {
                $decryptedRecord['ssn'] = base64_decode($record['ssn']);
            }
            $decryptedData[] = $decryptedRecord;
        }

        return $decryptedData;
    }

    private function validateDataEncryptionIntegrity(array $originalData, array $decryptedData): bool
    {
        if (count($originalData) !== count($decryptedData)) {
            return false;
        }

        foreach ($originalData as $index => $originalRecord) {
            if (! isset($decryptedData[$index])) {
                return false;
            }

            $decryptedRecord = $decryptedData[$index];

            foreach ($originalRecord as $key => $value) {
                if (! array_key_exists($key, $decryptedRecord) || $decryptedRecord[$key] !== $value) {
                    return false;
                }
            }
        }

        return true;
    }
}
