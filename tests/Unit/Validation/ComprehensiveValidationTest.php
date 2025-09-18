<?php

namespace Tests\Unit\Validation;

use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ComprehensiveValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('database.default', 'sqlite_testing');
    }

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
            'brand_id' => 'invalid', // غير صحيح
            'is_active' => 'yes', // غير boolean
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertCount(5, $validator->errors());
    }

    #[Test]
    public function user_validation_rules_work()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ];

        // اختبار البيانات الصحيحة
        $validData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // اختبار البيانات غير الصحيحة
        $invalidData = [
            'name' => '', // مطلوب
            'email' => 'invalid-email', // غير صحيح
            'password' => '123', // قصير جداً
            'password_confirmation' => 'different', // غير متطابق
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function review_validation_rules_work()
    {
        $rules = [
            'product_id' => 'required|integer|min:1',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:500',
        ];

        // اختبار البيانات الصحيحة
        $validData = [
            'product_id' => 1,
            'rating' => 5,
            'comment' => 'Great product!',
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // اختبار البيانات غير الصحيحة
        $invalidData = [
            'product_id' => 0, // غير صحيح
            'rating' => 6, // خارج النطاق
            'comment' => '', // مطلوب
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function price_alert_validation_rules_work()
    {
        $rules = [
            'product_id' => 'required|integer|min:1',
            'target_price' => 'required|numeric|min:0',
        ];

        // اختبار البيانات الصحيحة
        $validData = [
            'product_id' => 1,
            'target_price' => 50.00,
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // اختبار البيانات غير الصحيحة
        $invalidData = [
            'product_id' => 'invalid', // غير صحيح
            'target_price' => -10, // سالب
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function custom_validation_rules_work()
    {
        // اختبار قاعدة التحقق المخصصة للبريد الإلكتروني
        $rules = ['email' => 'required|email|unique:users,email'];

        $validator = Validator::make(['email' => 'test@example.com'], $rules);
        $this->assertTrue($validator->passes());

        // اختبار قاعدة التحقق المخصصة لكلمة المرور
        $rules = ['password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'];

        $validator = Validator::make(['password' => 'Password123'], $rules);
        $this->assertTrue($validator->passes());

        $validator = Validator::make(['password' => 'password'], $rules);
        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function validation_error_messages_are_meaningful()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ];

        $data = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
        ];

        $validator = Validator::make($data, $rules);
        $validator->fails();

        $errors = $validator->errors();

        $this->assertTrue($errors->has('name'));
        $this->assertTrue($errors->has('email'));
        $this->assertTrue($errors->has('password'));

        $this->assertStringContainsString('required', $errors->first('name'));
        $this->assertStringContainsString('email', $errors->first('email'));
        $this->assertStringContainsString('8', $errors->first('password'));
    }

    #[Test]
    public function validation_works_with_file_uploads()
    {
        $rules = [
            'filename' => 'required|string|max:255',
            'filetype' => 'required|in:image,document',
        ];

        // محاكاة رفع ملف صحيح
        $validData = [
            'filename' => 'test.jpg',
            'filetype' => 'image',
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // محاكاة رفع ملف غير صحيح
        $invalidData = [
            'filename' => '', // مطلوب
            'filetype' => 'video', // غير مسموح
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function validation_works_with_arrays()
    {
        $rules = [
            'tags' => 'required|array|min:1|max:10',
            'tags.*' => 'string|max:50',
            'prices' => 'array',
            'prices.*' => 'numeric|min:0',
        ];

        // اختبار البيانات الصحيحة
        $validData = [
            'tags' => ['electronics', 'smartphone', 'apple'],
            'prices' => [99.99, 199.99, 299.99],
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // اختبار البيانات غير الصحيحة
        $invalidData = [
            'tags' => [], // فارغ
            'prices' => ['invalid', -10, 100], // قيم غير صحيحة
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function validation_works_with_conditional_rules()
    {
        $rules = [
            'type' => 'required|in:product,service',
            'price' => 'required_if:type,product|numeric|min:0',
            'duration' => 'required_if:type,service|integer|min:1',
        ];

        // اختبار منتج
        $productData = [
            'type' => 'product',
            'price' => 99.99,
        ];

        $validator = Validator::make($productData, $rules);
        $this->assertTrue($validator->passes());

        // اختبار خدمة
        $serviceData = [
            'type' => 'service',
            'duration' => 30,
        ];

        $validator = Validator::make($serviceData, $rules);
        $this->assertTrue($validator->passes());

        // اختبار بيانات غير صحيحة
        $invalidData = [
            'type' => 'product',
            'duration' => 30, // يجب أن يكون price بدلاً من duration
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
    }
}
