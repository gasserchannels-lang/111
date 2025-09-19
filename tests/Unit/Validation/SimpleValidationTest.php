<?php

namespace Tests\Unit\Validation;

use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SimpleValidationTest extends TestCase
{
    #[Test]
    public function product_validation_rules_work()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|min:1',
            'brand_id' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ];

        // اختبار البيانات الصحيحة
        $validData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category_id' => 1,
            'brand_id' => 1,
            'is_active' => true,
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // اختبار البيانات غير الصحيحة
        $invalidData = [
            'name' => '', // مطلوب
            'description' => str_repeat('a', 1001), // طويل جداً
            'price' => -10, // سالب
            'category_id' => 99999, // غير موجود
            'brand_id' => 0, // غير صحيح
            'is_active' => 'invalid', // ليس boolean
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertFalse($validator->passes());
        // Debug: Check actual error count
        $errors = $validator->errors();
        $this->assertGreaterThanOrEqual(5, $errors->count(), 'Should have at least 5 validation errors');
    }

    #[Test]
    public function user_validation_rules_work()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
        ];

        // اختبار البيانات الصحيحة
        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'phone' => '+1234567890',
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // اختبار البيانات غير الصحيحة
        $invalidData = [
            'name' => '', // مطلوب
            'email' => 'invalid-email', // ليس email صحيح
            'password' => '123', // قصير جداً
            'phone' => str_repeat('1', 25), // طويل جداً
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertFalse($validator->passes());
        $this->assertCount(4, $validator->errors());
    }

    #[Test]
    public function review_validation_rules_work()
    {
        // Create test data first
        \DB::table('users')->insert([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('products')->insert([
            'id' => 1,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rules = [
            'product_id' => 'required|integer|exists:products,id',
            'user_id' => 'required|integer|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];

        // اختبار البيانات الصحيحة
        $validData = [
            'product_id' => 1,
            'user_id' => 1,
            'rating' => 5,
            'comment' => 'Great product!',
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // اختبار البيانات غير الصحيحة
        $invalidData = [
            'product_id' => 0, // غير صحيح
            'user_id' => -1, // سالب
            'rating' => 6, // خارج النطاق
            'comment' => str_repeat('a', 1001), // طويل جداً
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertFalse($validator->passes());
        $this->assertCount(4, $validator->errors());
    }
}
