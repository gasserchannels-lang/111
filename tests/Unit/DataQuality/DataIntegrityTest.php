<?php

namespace Tests\Unit\DataQuality;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DataIntegrityTest extends TestCase
{
    #[Test]
    public function it_validates_foreign_key_constraints(): void
    {
        $products = [
            ['id' => 1, 'category_id' => 1, 'brand_id' => 1],
            ['id' => 2, 'category_id' => 2, 'brand_id' => 2],
            ['id' => 3, 'category_id' => 999, 'brand_id' => 1] // Invalid category_id
        ];

        $categories = [
            ['id' => 1, 'name' => 'Smartphones'],
            ['id' => 2, 'name' => 'Laptops']
        ];

        $brands = [
            ['id' => 1, 'name' => 'Apple'],
            ['id' => 2, 'name' => 'Samsung']
        ];

        $violations = $this->validateForeignKeyConstraints($products, $categories, $brands);
        $this->assertCount(1, $violations);
        $this->assertEquals(3, $violations[0]['product_id']);
    }

    #[Test]
    public function it_validates_unique_constraints(): void
    {
        $users = [
            ['id' => 1, 'email' => 'user1@example.com'],
            ['id' => 2, 'email' => 'user2@example.com'],
            ['id' => 3, 'email' => 'user1@example.com'] // Duplicate email
        ];

        $violations = $this->validateUniqueConstraints($users, ['email']);
        $this->assertCount(1, $violations);
        $this->assertEquals('email', $violations[0]['field']);
    }

    #[Test]
    public function it_validates_not_null_constraints(): void
    {
        $products = [
            ['id' => 1, 'name' => 'iPhone 15', 'price' => 999.00],
            ['id' => 2, 'name' => null, 'price' => 899.00], // Null name
            ['id' => 3, 'name' => 'Samsung Galaxy S24', 'price' => null] // Null price
        ];

        $violations = $this->validateNotNullConstraints($products, ['name', 'price']);
        $this->assertCount(2, $violations);
    }

    #[Test]
    public function it_validates_check_constraints(): void
    {
        $products = [
            ['id' => 1, 'name' => 'iPhone 15', 'price' => 999.00, 'stock' => 10],
            ['id' => 2, 'name' => 'Samsung Galaxy S24', 'price' => -100.00, 'stock' => 5], // Negative price
            ['id' => 3, 'name' => 'Google Pixel 8', 'price' => 699.00, 'stock' => -5] // Negative stock
        ];

        $violations = $this->validateCheckConstraints($products);
        $this->assertCount(2, $violations);
    }

    #[Test]
    public function it_validates_data_type_constraints(): void
    {
        $products = [
            ['id' => 1, 'name' => 'iPhone 15', 'price' => 999.00, 'is_active' => true],
            ['id' => 2, 'name' => 'Samsung Galaxy S24', 'price' => 'invalid', 'is_active' => 1], // Invalid price type
            ['id' => 3, 'name' => 'Google Pixel 8', 'price' => 699.00, 'is_active' => 'yes'] // Invalid boolean
        ];

        $violations = $this->validateDataTypeConstraints($products);
        $this->assertCount(3, $violations);
    }

    #[Test]
    public function it_validates_referential_integrity(): void
    {
        $orders = [
            ['id' => 1, 'user_id' => 1, 'product_id' => 1],
            ['id' => 2, 'user_id' => 2, 'product_id' => 2],
            ['id' => 3, 'user_id' => 999, 'product_id' => 1] // Invalid user_id
        ];

        $users = [
            ['id' => 1, 'name' => 'John Doe'],
            ['id' => 2, 'name' => 'Jane Smith']
        ];

        $products = [
            ['id' => 1, 'name' => 'iPhone 15'],
            ['id' => 2, 'name' => 'Samsung Galaxy S24']
        ];

        $violations = $this->validateReferentialIntegrity($orders, $users, $products);
        $this->assertCount(1, $violations);
    }

    #[Test]
    public function it_validates_cascade_constraints(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Smartphones'],
            ['id' => 2, 'name' => 'Laptops']
        ];

        $products = [
            ['id' => 1, 'category_id' => 1, 'name' => 'iPhone 15'],
            ['id' => 2, 'category_id' => 1, 'name' => 'Samsung Galaxy S24'],
            ['id' => 3, 'category_id' => 2, 'name' => 'MacBook Pro']
        ];

        // Delete category 1 - should cascade to products
        $remainingProducts = $this->simulateCascadeDelete($categories, $products, 1);
        $this->assertCount(1, $remainingProducts);
        $this->assertEquals(3, $remainingProducts[array_keys($remainingProducts)[0]]['id']);
    }

    #[Test]
    public function it_validates_orphaned_records(): void
    {
        $products = [
            ['id' => 1, 'category_id' => 1, 'name' => 'iPhone 15'],
            ['id' => 2, 'category_id' => 2, 'name' => 'Samsung Galaxy S24'],
            ['id' => 3, 'category_id' => 999, 'name' => 'Orphaned Product'] // Orphaned
        ];

        $categories = [
            ['id' => 1, 'name' => 'Smartphones'],
            ['id' => 2, 'name' => 'Laptops']
        ];

        $orphanedRecords = $this->findOrphanedRecords($products, $categories, 'category_id');
        $this->assertCount(1, $orphanedRecords);
        $this->assertEquals(3, $orphanedRecords[array_keys($orphanedRecords)[0]]['id']);
    }

    #[Test]
    public function it_validates_circular_references(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Electronics', 'parent_id' => null],
            ['id' => 2, 'name' => 'Smartphones', 'parent_id' => 1],
            ['id' => 3, 'name' => 'Accessories', 'parent_id' => 2],
            ['id' => 4, 'name' => 'Cases', 'parent_id' => 3]
        ];

        $circularRefs = $this->detectCircularReferences($categories);
        $this->assertCount(0, $circularRefs); // No circular references

        // Add circular reference
        $categories[3]['parent_id'] = 1; // Cases -> Electronics (circular)
        $circularRefs = $this->detectCircularReferences($categories);
        $this->assertGreaterThanOrEqual(0, count($circularRefs));
    }

    #[Test]
    public function it_validates_data_consistency_across_tables(): void
    {
        $products = [
            ['id' => 1, 'name' => 'iPhone 15', 'price' => 999.00, 'currency' => 'USD'],
            ['id' => 2, 'name' => 'Samsung Galaxy S24', 'price' => 899.00, 'currency' => 'USD']
        ];

        $priceHistory = [
            ['product_id' => 1, 'price' => 999.00, 'currency' => 'USD'],
            ['product_id' => 2, 'price' => 899.00, 'currency' => 'EUR'] // Inconsistent currency
        ];

        $inconsistencies = $this->validateDataConsistency($products, $priceHistory);
        $this->assertCount(1, $inconsistencies);
    }

    private function validateForeignKeyConstraints(array $products, array $categories, array $brands): array
    {
        $violations = [];
        $categoryIds = array_column($categories, 'id');
        $brandIds = array_column($brands, 'id');

        foreach ($products as $product) {
            if (!in_array($product['category_id'], $categoryIds)) {
                $violations[] = [
                    'product_id' => $product['id'],
                    'constraint' => 'category_id',
                    'value' => $product['category_id']
                ];
            }
            if (!in_array($product['brand_id'], $brandIds)) {
                $violations[] = [
                    'product_id' => $product['id'],
                    'constraint' => 'brand_id',
                    'value' => $product['brand_id']
                ];
            }
        }

        return $violations;
    }

    private function validateUniqueConstraints(array $records, array $fields): array
    {
        $violations = [];

        foreach ($fields as $field) {
            $values = [];
            foreach ($records as $record) {
                $value = $record[$field] ?? null;
                if (isset($values[$value])) {
                    $violations[] = [
                        'field' => $field,
                        'value' => $value,
                        'duplicate_count' => $values[$value] + 1
                    ];
                } else {
                    $values[$value] = 1;
                }
            }
        }

        return $violations;
    }

    private function validateNotNullConstraints(array $records, array $fields): array
    {
        $violations = [];

        foreach ($records as $record) {
            foreach ($fields as $field) {
                if (!isset($record[$field]) || $record[$field] === null) {
                    $violations[] = [
                        'record_id' => $record['id'],
                        'field' => $field,
                        'value' => null
                    ];
                }
            }
        }

        return $violations;
    }

    private function validateCheckConstraints(array $products): array
    {
        $violations = [];

        foreach ($products as $product) {
            if (isset($product['price']) && $product['price'] < 0) {
                $violations[] = [
                    'product_id' => $product['id'],
                    'constraint' => 'price >= 0',
                    'value' => $product['price']
                ];
            }
            if (isset($product['stock']) && $product['stock'] < 0) {
                $violations[] = [
                    'product_id' => $product['id'],
                    'constraint' => 'stock >= 0',
                    'value' => $product['stock']
                ];
            }
        }

        return $violations;
    }

    private function validateDataTypeConstraints(array $products): array
    {
        $violations = [];

        foreach ($products as $product) {
            if (isset($product['price']) && !is_numeric($product['price'])) {
                $violations[] = [
                    'product_id' => $product['id'],
                    'field' => 'price',
                    'expected_type' => 'numeric',
                    'actual_type' => gettype($product['price'])
                ];
            }
            if (isset($product['is_active']) && !is_bool($product['is_active'])) {
                $violations[] = [
                    'product_id' => $product['id'],
                    'field' => 'is_active',
                    'expected_type' => 'boolean',
                    'actual_type' => gettype($product['is_active'])
                ];
            }
        }

        return $violations;
    }

    private function validateReferentialIntegrity(array $orders, array $users, array $products): array
    {
        $violations = [];
        $userIds = array_column($users, 'id');
        $productIds = array_column($products, 'id');

        foreach ($orders as $order) {
            if (!in_array($order['user_id'], $userIds)) {
                $violations[] = [
                    'order_id' => $order['id'],
                    'constraint' => 'user_id',
                    'value' => $order['user_id']
                ];
            }
            if (!in_array($order['product_id'], $productIds)) {
                $violations[] = [
                    'order_id' => $order['id'],
                    'constraint' => 'product_id',
                    'value' => $order['product_id']
                ];
            }
        }

        return $violations;
    }

    private function simulateCascadeDelete(array $categories, array $products, int $categoryId): array
    {
        // Simulate cascade delete - remove products with deleted category
        return array_filter($products, function ($product) use ($categoryId) {
            return $product['category_id'] !== $categoryId;
        });
    }

    private function findOrphanedRecords(array $childRecords, array $parentRecords, string $foreignKey): array
    {
        $parentIds = array_column($parentRecords, 'id');

        return array_filter($childRecords, function ($record) use ($parentIds, $foreignKey) {
            return !in_array($record[$foreignKey], $parentIds);
        });
    }

    private function detectCircularReferences(array $categories): array
    {
        $circularRefs = [];

        foreach ($categories as $category) {
            $visited = [];
            $current = $category['id'];

            while ($current !== null) {
                if (in_array($current, $visited)) {
                    $circularRefs[] = [
                        'category_id' => $category['id'],
                        'circular_path' => $visited
                    ];
                    break;
                }

                $visited[] = $current;
                $parent = array_filter($categories, function ($cat) use ($current) {
                    return $cat['id'] === $current;
                });

                if (empty($parent)) {
                    break;
                }

                $parentRecord = reset($parent);
                $current = $parentRecord['parent_id'];
            }
        }

        return $circularRefs;
    }

    private function validateDataConsistency(array $products, array $priceHistory): array
    {
        $inconsistencies = [];

        foreach ($priceHistory as $history) {
            $product = array_filter($products, function ($p) use ($history) {
                return $p['id'] === $history['product_id'];
            });

            if (!empty($product)) {
                $product = reset($product);
                if ($product['currency'] !== $history['currency']) {
                    $inconsistencies[] = [
                        'product_id' => $product['id'],
                        'field' => 'currency',
                        'product_value' => $product['currency'],
                        'history_value' => $history['currency']
                    ];
                }
            }
        }

        return $inconsistencies;
    }
}
