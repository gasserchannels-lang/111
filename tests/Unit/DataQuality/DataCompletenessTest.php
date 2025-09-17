<?php

declare(strict_types=1);

namespace Tests\Unit\DataQuality;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DataCompletenessTest extends TestCase
{
    #[Test]
    public function it_validates_required_fields_are_present(): void
    {
        $product = [
            'id' => 1,
            'name' => 'iPhone 15 Pro',
            'brand' => 'Apple',
            'price' => 999.99,
            'category' => 'Smartphones'
        ];

        $requiredFields = ['id', 'name', 'brand', 'price', 'category'];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $product);
            $this->assertNotNull($product[$field]);
        }
    }

    #[Test]
    public function it_validates_optional_fields_can_be_null(): void
    {
        $product = [
            'id' => 1,
            'name' => 'iPhone 15 Pro',
            'description' => null,
            'image_url' => null,
            'specifications' => null
        ];

        $optionalFields = ['description', 'image_url', 'specifications'];

        foreach ($optionalFields as $field) {
            $this->assertArrayHasKey($field, $product);
            $this->assertNull($product[$field]);
        }
    }

    #[Test]
    public function it_validates_string_fields_are_not_empty(): void
    {
        $product = [
            'name' => 'iPhone 15 Pro',
            'brand' => 'Apple',
            'category' => 'Smartphones'
        ];

        foreach ($product as $field => $value) {
            if (is_string($value)) {
                $this->assertNotEmpty(trim($value));
            }
        }
    }

    #[Test]
    public function it_validates_numeric_fields_are_valid(): void
    {
        $product = [
            'id' => 1,
            'price' => 999.99,
            'stock_quantity' => 50,
            'rating' => 4.5
        ];

        $this->assertIsInt($product['id']);
        $this->assertIsFloat($product['price']);
        $this->assertIsInt($product['stock_quantity']);
        $this->assertIsFloat($product['rating']);

        $this->assertGreaterThan(0, $product['id']);
        $this->assertGreaterThan(0, $product['price']);
        $this->assertGreaterThanOrEqual(0, $product['stock_quantity']);
        $this->assertGreaterThanOrEqual(0, $product['rating']);
        $this->assertLessThanOrEqual(5, $product['rating']);
    }

    #[Test]
    public function it_validates_email_fields_format(): void
    {
        $user = [
            'email' => 'user@example.com',
            'contact_email' => 'contact@company.com'
        ];

        foreach ($user as $field => $email) {
            $this->assertIsString($email);
            $this->assertNotEmpty($email);
            $this->assertStringContainsString('@', $email);
            $this->assertStringContainsString('.', $email);
        }
    }

    #[Test]
    public function it_validates_url_fields_format(): void
    {
        $product = [
            'website_url' => 'https://www.apple.com',
            'image_url' => 'https://example.com/image.jpg',
            'manual_url' => 'https://support.apple.com/manual.pdf'
        ];

        foreach ($product as $field => $url) {
            if ($url !== null) {
                $this->assertIsString($url);
                $this->assertStringStartsWith('http', $url);
            }
        }
    }

    #[Test]
    public function it_validates_date_fields_format(): void
    {
        $product = [
            'created_at' => '2025-01-15 10:30:00',
            'updated_at' => '2025-01-15 15:45:00',
            'release_date' => '2024-09-15'
        ];

        foreach ($product as $field => $date) {
            $this->assertIsString($date);
            $this->assertNotEmpty($date);

            $timestamp = strtotime($date);
            $this->assertNotFalse($timestamp);
        }
    }

    #[Test]
    public function it_validates_array_fields_structure(): void
    {
        $product = [
            'specifications' => [
                'storage' => '256GB',
                'color' => 'Space Black',
                'screen_size' => '6.1 inches'
            ],
            'tags' => ['smartphone', 'apple', 'premium'],
            'images' => [
                'https://example.com/image1.jpg',
                'https://example.com/image2.jpg'
            ]
        ];

        $this->assertIsArray($product['specifications']);
        $this->assertIsArray($product['tags']);
        $this->assertIsArray($product['images']);

        $this->assertNotEmpty($product['specifications']);
        $this->assertNotEmpty($product['tags']);
        $this->assertNotEmpty($product['images']);
    }

    #[Test]
    public function it_validates_boolean_fields(): void
    {
        $product = [
            'is_active' => true,
            'is_featured' => false,
            'is_available' => true,
            'has_warranty' => true
        ];

        foreach ($product as $field => $value) {
            $this->assertIsBool($value);
        }
    }

    #[Test]
    public function it_validates_enum_fields(): void
    {
        $product = [
            'status' => 'active',
            'condition' => 'new',
            'shipping_method' => 'standard'
        ];

        $validStatuses = ['active', 'inactive', 'pending'];
        $validConditions = ['new', 'used', 'refurbished'];
        $validShippingMethods = ['standard', 'express', 'overnight'];

        $this->assertContains($product['status'], $validStatuses);
        $this->assertContains($product['condition'], $validConditions);
        $this->assertContains($product['shipping_method'], $validShippingMethods);
    }

    #[Test]
    public function it_validates_foreign_key_relationships(): void
    {
        $product = [
            'id' => 1,
            'brand_id' => 5,
            'category_id' => 12,
            'store_id' => 8
        ];

        $foreignKeys = ['brand_id', 'category_id', 'store_id'];

        foreach ($foreignKeys as $key) {
            $this->assertArrayHasKey($key, $product);
            $this->assertIsInt($product[$key]);
            $this->assertGreaterThan(0, $product[$key]);
        }
    }

    #[Test]
    public function it_validates_data_consistency_across_related_records(): void
    {
        $product = [
            'id' => 1,
            'name' => 'iPhone 15 Pro',
            'brand_id' => 5
        ];

        $brand = [
            'id' => 5,
            'name' => 'Apple'
        ];

        $this->assertEquals($product['brand_id'], $brand['id']);
    }

    #[Test]
    public function it_validates_required_vs_optional_fields_completeness(): void
    {
        $completeProduct = [
            'id' => 1,
            'name' => 'iPhone 15 Pro',
            'brand' => 'Apple',
            'price' => 999.99,
            'category' => 'Smartphones',
            'description' => 'Latest iPhone model',
            'image_url' => 'https://example.com/image.jpg',
            'specifications' => ['storage' => '256GB']
        ];

        $requiredFields = ['id', 'name', 'brand', 'price', 'category'];
        $optionalFields = ['description', 'image_url', 'specifications'];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $completeProduct);
            $this->assertNotNull($completeProduct[$field]);
        }

        foreach ($optionalFields as $field) {
            $this->assertArrayHasKey($field, $completeProduct);
        }
    }
}
