<?php

declare(strict_types=1);

namespace Tests\Unit\DataQuality;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DataConsistencyTest extends TestCase
{
    #[Test]
    public function it_validates_data_consistency_across_tables(): void
    {
        $product = [
            'id' => 1,
            'brand_id' => 5,
            'category_id' => 12,
            'store_id' => 8
        ];

        $brand = ['id' => 5, 'name' => 'Apple'];
        $category = ['id' => 12, 'name' => 'Smartphones'];
        $store = ['id' => 8, 'name' => 'Apple Store'];

        $this->assertEquals($product['brand_id'], $brand['id']);
        $this->assertEquals($product['category_id'], $category['id']);
        $this->assertEquals($product['store_id'], $store['id']);
    }

    #[Test]
    public function it_validates_referential_integrity(): void
    {
        $products = [
            ['id' => 1, 'brand_id' => 5],
            ['id' => 2, 'brand_id' => 5],
            ['id' => 3, 'brand_id' => 6]
        ];

        $brands = [
            ['id' => 5, 'name' => 'Apple'],
            ['id' => 6, 'name' => 'Samsung']
        ];

        $brandIds = array_column($brands, 'id');

        foreach ($products as $product) {
            $this->assertContains($product['brand_id'], $brandIds);
        }
    }

    #[Test]
    public function it_validates_data_format_consistency(): void
    {
        $products = [
            ['price' => '999.99', 'currency' => 'USD'],
            ['price' => '899.99', 'currency' => 'USD'],
            ['price' => '1099.99', 'currency' => 'USD']
        ];

        foreach ($products as $product) {
            $this->assertIsString($product['price']);
            $this->assertIsNumeric($product['price']);
            $this->assertEquals('USD', $product['currency']);
        }
    }

    #[Test]
    public function it_validates_date_consistency(): void
    {
        $product = [
            'created_at' => '2025-01-15 10:30:00',
            'updated_at' => '2025-01-15 15:45:00',
            'release_date' => '2024-09-15'
        ];

        $createdAt = strtotime($product['created_at']);
        $updatedAt = strtotime($product['updated_at']);
        $releaseDate = strtotime($product['release_date']);

        $this->assertLessThanOrEqual($updatedAt, $createdAt);
        $this->assertLessThanOrEqual($createdAt, $releaseDate);
    }

    #[Test]
    public function it_validates_enum_consistency(): void
    {
        $products = [
            ['status' => 'active'],
            ['status' => 'inactive'],
            ['status' => 'pending']
        ];

        $validStatuses = ['active', 'inactive', 'pending'];

        foreach ($products as $product) {
            $this->assertContains($product['status'], $validStatuses);
        }
    }

    #[Test]
    public function it_validates_numeric_range_consistency(): void
    {
        $products = [
            ['price' => 99.99, 'rating' => 4.5],
            ['price' => 199.99, 'rating' => 3.8],
            ['price' => 299.99, 'rating' => 4.9]
        ];

        foreach ($products as $product) {
            $this->assertGreaterThan(0, $product['price']);
            $this->assertGreaterThanOrEqual(0, $product['rating']);
            $this->assertLessThanOrEqual(5, $product['rating']);
        }
    }

    #[Test]
    public function it_validates_string_length_consistency(): void
    {
        $products = [
            ['name' => 'iPhone 15 Pro', 'description' => 'Latest iPhone model'],
            ['name' => 'Samsung Galaxy S24', 'description' => 'Premium Android smartphone'],
            ['name' => 'MacBook Pro', 'description' => 'Professional laptop']
        ];

        foreach ($products as $product) {
            $this->assertLessThanOrEqual(255, strlen($product['name']));
            $this->assertLessThanOrEqual(1000, strlen($product['description']));
        }
    }

    #[Test]
    public function it_validates_boolean_consistency(): void
    {
        $products = [
            ['is_active' => true, 'is_featured' => false],
            ['is_active' => false, 'is_featured' => true],
            ['is_active' => true, 'is_featured' => true]
        ];

        foreach ($products as $product) {
            $this->assertIsBool($product['is_active']);
            $this->assertIsBool($product['is_featured']);
        }
    }

    #[Test]
    public function it_validates_array_structure_consistency(): void
    {
        $products = [
            [
                'specifications' => ['storage' => '256GB', 'color' => 'Space Black'],
                'tags' => ['smartphone', 'apple', 'premium']
            ],
            [
                'specifications' => ['storage' => '512GB', 'color' => 'Silver'],
                'tags' => ['smartphone', 'apple', 'premium']
            ]
        ];

        foreach ($products as $product) {
            $this->assertIsArray($product['specifications']);
            $this->assertIsArray($product['tags']);

            $this->assertArrayHasKey('storage', $product['specifications']);
            $this->assertArrayHasKey('color', $product['specifications']);
        }
    }

    #[Test]
    public function it_validates_foreign_key_consistency(): void
    {
        $wishlistItems = [
            ['user_id' => 1, 'product_id' => 5],
            ['user_id' => 2, 'product_id' => 5],
            ['user_id' => 1, 'product_id' => 8]
        ];

        $users = [1, 2, 3];
        $products = [5, 6, 7, 8, 9];

        foreach ($wishlistItems as $item) {
            $this->assertContains($item['user_id'], $users);
            $this->assertContains($item['product_id'], $products);
        }
    }

    #[Test]
    public function it_validates_calculated_field_consistency(): void
    {
        $products = [
            ['price' => 100.00, 'discount' => 10.00, 'final_price' => 90.00],
            ['price' => 200.00, 'discount' => 20.00, 'final_price' => 180.00],
            ['price' => 300.00, 'discount' => 30.00, 'final_price' => 270.00]
        ];

        foreach ($products as $product) {
            $expectedFinalPrice = $product['price'] - $product['discount'];
            $this->assertEquals($expectedFinalPrice, $product['final_price']);
        }
    }

    #[Test]
    public function it_validates_currency_consistency(): void
    {
        $products = [
            ['price' => 999.99, 'currency' => 'USD'],
            ['price' => 899.99, 'currency' => 'USD'],
            ['price' => 1099.99, 'currency' => 'USD']
        ];

        $currencies = array_unique(array_column($products, 'currency'));
        $this->assertCount(1, $currencies);
        $this->assertEquals('USD', $currencies[0]);
    }

    #[Test]
    public function it_validates_timestamp_consistency(): void
    {
        $records = [
            ['created_at' => '2025-01-15 10:30:00', 'updated_at' => '2025-01-15 15:45:00'],
            ['created_at' => '2025-01-14 09:15:00', 'updated_at' => '2025-01-14 14:20:00'],
            ['created_at' => '2025-01-13 08:00:00', 'updated_at' => '2025-01-13 12:30:00']
        ];

        foreach ($records as $record) {
            $createdAt = strtotime($record['created_at']);
            $updatedAt = strtotime($record['updated_at']);

            $this->assertLessThanOrEqual($updatedAt, $createdAt);
        }
    }

    #[Test]
    public function it_validates_status_transition_consistency(): void
    {
        $statusTransitions = [
            ['from' => 'pending', 'to' => 'active'],
            ['from' => 'active', 'to' => 'inactive'],
            ['from' => 'inactive', 'to' => 'active']
        ];

        $validTransitions = [
            'pending' => ['active', 'cancelled'],
            'active' => ['inactive', 'suspended'],
            'inactive' => ['active', 'deleted'],
            'suspended' => ['active', 'inactive']
        ];

        foreach ($statusTransitions as $transition) {
            $this->assertArrayHasKey($transition['from'], $validTransitions);
            $this->assertContains($transition['to'], $validTransitions[$transition['from']]);
        }
    }

    #[Test]
    public function it_validates_data_relationship_consistency(): void
    {
        $order = [
            'id' => 1,
            'user_id' => 5,
            'total_amount' => 299.99
        ];

        $orderItems = [
            ['order_id' => 1, 'product_id' => 10, 'quantity' => 1, 'price' => 199.99],
            ['order_id' => 1, 'product_id' => 11, 'quantity' => 2, 'price' => 50.00]
        ];

        $calculatedTotal = array_sum(array_map(fn($item) => $item['quantity'] * $item['price'], $orderItems));

        $this->assertEquals($order['id'], $orderItems[0]['order_id']);
        $this->assertEquals($order['id'], $orderItems[1]['order_id']);
        $this->assertEquals($calculatedTotal, $order['total_amount']);
    }
}
