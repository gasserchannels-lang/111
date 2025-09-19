<?php

namespace Tests\Unit\Validation;

use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BasicValidationTest extends TestCase
{
    #[Test]
    public function basic_string_validation_works()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ];

        // اختبار البيانات الصحيحة
        $validData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // اختبار البيانات غير الصحيحة
        $invalidData = [
            'name' => '', // مطلوب
            'description' => str_repeat('a', 1001), // طويل جداً
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertFalse($validator->passes());
        $this->assertCount(2, $validator->errors());
    }

    #[Test]
    public function numeric_validation_works()
    {
        $rules = [
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ];

        // اختبار البيانات الصحيحة
        $validData = [
            'price' => 99.99,
            'quantity' => 5,
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // اختبار البيانات غير الصحيحة
        $invalidData = [
            'price' => -10, // سالب
            'quantity' => 0, // أقل من 1
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertFalse($validator->passes());
        $this->assertCount(2, $validator->errors());
    }

    #[Test]
    public function email_validation_works()
    {
        $rules = [
            'email' => 'required|email',
        ];

        // اختبار البيانات الصحيحة
        $validData = [
            'email' => 'john@example.com',
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // اختبار البيانات غير الصحيحة
        $invalidData = [
            'email' => 'invalid-email',
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertFalse($validator->passes());
        $this->assertCount(1, $validator->errors());
    }

    #[Test]
    public function boolean_validation_works()
    {
        $rules = [
            'is_active' => 'boolean',
        ];

        // اختبار البيانات الصحيحة
        $validData = [
            'is_active' => true,
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // اختبار البيانات غير الصحيحة
        $invalidData = [
            'is_active' => 'invalid',
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertFalse($validator->passes());
        $this->assertCount(1, $validator->errors());
    }

    #[Test]
    public function array_validation_works()
    {
        $rules = [
            'tags' => 'required|array|min:1',
            'tags.*' => 'string|max:50',
        ];

        // اختبار البيانات الصحيحة
        $validData = [
            'tags' => ['tag1', 'tag2', 'tag3'],
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // اختبار البيانات غير الصحيحة
        $invalidData = [
            'tags' => [], // فارغ
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertFalse($validator->passes());
        $this->assertCount(1, $validator->errors());
    }
}
