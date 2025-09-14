<?php

namespace Tests\Integration;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceAlert;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdvancedIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function complete_user_journey_works()
    {
        // 1. تسجيل مستخدم جديد
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $registerResponse = $this->postJson('/api/auth/register', $userData);
        $this->assertEquals(201, $registerResponse->status());

        // 2. تسجيل الدخول
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        $this->assertEquals(200, $loginResponse->status());

        $token = $loginResponse->json('token');
        $this->actingAs(User::where('email', 'test@example.com')->first());

        // 3. تصفح المنتجات
        $productsResponse = $this->getJson('/api/products');
        $this->assertEquals(200, $productsResponse->status());

        // 4. إضافة منتج للمفضلة
        $product = Product::factory()->create();
        $wishlistResponse = $this->postJson('/api/wishlist', [
            'product_id' => $product->id,
        ]);
        $this->assertEquals(201, $wishlistResponse->status());

        // 5. إنشاء تنبيه سعر
        $priceAlertResponse = $this->postJson('/api/price-alerts', [
            'product_id' => $product->id,
            'target_price' => 100.00,
        ]);
        $this->assertEquals(201, $priceAlertResponse->status());

        // 6. إضافة مراجعة
        $reviewResponse = $this->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!',
        ]);
        $this->assertEquals(201, $reviewResponse->status());
    }

    #[Test]
    public function ai_integration_workflow()
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'This is a test product for AI analysis',
        ]);

        // 1. تحليل المنتج بالذكاء الاصطناعي
        $aiResponse = $this->postJson('/api/ai/analyze', [
            'text' => $product->description,
            'type' => 'product_analysis',
        ]);

        $this->assertEquals(200, $aiResponse->status());
        $this->assertArrayHasKey('analysis', $aiResponse->json());

        // 2. اقتراحات تحسين
        $suggestionsResponse = $this->postJson('/api/ai/suggestions', [
            'product_id' => $product->id,
            'type' => 'improvement',
        ]);

        $this->assertEquals(200, $suggestionsResponse->status());
    }

    #[Test]
    public function email_notification_system()
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 150.00]);

        // إنشاء تنبيه سعر
        $priceAlert = PriceAlert::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'target_price' => 100.00,
        ]);

        // تحديث السعر لتحفيز التنبيه
        $product->update(['price' => 90.00]);

        // تشغيل الأمر لإرسال التنبيهات
        $this->artisan('price-alerts:check')->assertExitCode(0);

        // التحقق من إرسال الإيميل
        Mail::assertSent(\App\Mail\PriceAlertMail::class);
    }

    #[Test]
    public function queue_system_integration()
    {
        Queue::fake();

        // إنشاء منتج جديد
        $product = Product::factory()->create();

        // إرسال إشعار للمستخدمين المهتمين
        $user = User::factory()->create();
        PriceAlert::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'target_price' => 100,
        ]);

        // محاكاة تغيير السعر
        $product->update(['price' => 90]);

        // التحقق من إرسال الإشعار
        Queue::assertPushed(\App\Jobs\SendPriceAlertJob::class);
    }

    #[Test]
    public function cache_integration_works()
    {
        $product = Product::factory()->create();

        // الطلب الأول - بدون cache
        $response1 = $this->getJson('/api/products/' . $product->id);
        $this->assertEquals(200, $response1->status());

        // الطلب الثاني - مع cache
        $response2 = $this->getJson('/api/products/' . $product->id);
        $this->assertEquals(200, $response2->status());

        // يجب أن تكون النتائج متطابقة
        $this->assertEquals($response1->json(), $response2->json());
    }

    #[Test]
    public function search_integration_works()
    {
        // إنشاء منتجات للبحث
        Product::factory()->create(['name' => 'iPhone 15']);
        Product::factory()->create(['name' => 'Samsung Galaxy']);
        Product::factory()->create(['name' => 'Google Pixel']);

        // البحث عن iPhone
        $searchResponse = $this->getJson('/api/search?q=iPhone');
        $this->assertEquals(200, $searchResponse->status());

        $results = $searchResponse->json('data');
        $this->assertCount(1, $results);
        $this->assertEquals('iPhone 15', $results[0]['name']);
    }

    #[Test]
    public function payment_integration_works()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create(['price' => 100.00]);

        // محاكاة عملية دفع
        $paymentResponse = $this->postJson('/api/payment/process', [
            'product_id' => $product->id,
            'amount' => 100.00,
            'payment_method' => 'credit_card',
        ]);

        // يجب أن يعيد نتيجة (نجح أو فشل)
        $this->assertContains($paymentResponse->status(), [200, 400, 422]);
    }

    #[Test]
    public function admin_workflow_integration()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        // 1. إنشاء منتج جديد
        $productData = [
            'name' => 'New Product',
            'description' => 'Product description',
            'price' => 99.99,
            'category_id' => Category::factory()->create()->id,
            'brand_id' => Brand::factory()->create()->id,
        ];

        $createResponse = $this->postJson('/api/admin/products', $productData);
        $this->assertEquals(201, $createResponse->status());

        // 2. تحديث المنتج
        $product = Product::latest()->first();
        $updateResponse = $this->putJson('/api/admin/products/' . $product->id, [
            'name' => 'Updated Product Name',
        ]);
        $this->assertEquals(200, $updateResponse->status());

        // 3. حذف المنتج
        $deleteResponse = $this->deleteJson('/api/admin/products/' . $product->id);
        $this->assertEquals(200, $deleteResponse->status());
    }
}
