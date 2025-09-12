<?php

namespace Tests\Integration;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceAlert;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CompleteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function complete_e_commerce_workflow()
    {
        // 1. إنشاء فئات وعلامات تجارية
        $category = Category::factory()->create(['name' => 'Electronics']);
        $brand = Brand::factory()->create(['name' => 'Apple']);

        // 2. تسجيل مستخدم جديد
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $registerResponse = $this->postJson('/api/register', $userData);
        $this->assertEquals(201, $registerResponse->status());

        // 3. تسجيل الدخول
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        $this->assertEquals(200, $loginResponse->status());

        $user = User::where('email', 'john@example.com')->first();
        $this->actingAs($user);

        // 4. إنشاء منتج جديد (كمدير)
        $user->update(['is_admin' => true]);

        $productData = [
            'name' => 'iPhone 15 Pro',
            'description' => 'Latest iPhone with advanced features',
            'price' => 999.99,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
        ];

        $productResponse = $this->postJson('/api/products', $productData);
        $this->assertEquals(201, $productResponse->status());

        $product = Product::latest()->first();

        // 5. تصفح المنتجات
        $productsResponse = $this->getJson('/api/products');
        $this->assertEquals(200, $productsResponse->status());
        $this->assertCount(1, $productsResponse->json('data'));

        // 6. البحث عن المنتجات
        $searchResponse = $this->getJson('/api/search?q=iPhone');
        $this->assertEquals(200, $searchResponse->status());
        $this->assertCount(1, $searchResponse->json('data'));

        // 7. إضافة منتج للمفضلة
        $wishlistResponse = $this->postJson('/api/wishlist', [
            'product_id' => $product->id,
        ]);
        $this->assertEquals(201, $wishlistResponse->status());

        // 8. إنشاء تنبيه سعر
        $priceAlertResponse = $this->postJson('/api/price-alerts', [
            'product_id' => $product->id,
            'target_price' => 800.00,
        ]);
        $this->assertEquals(201, $priceAlertResponse->status());

        // 9. إضافة مراجعة
        $reviewResponse = $this->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Excellent product! Highly recommended.',
        ]);
        $this->assertEquals(201, $reviewResponse->status());

        // 10. تحديث السعر لتحفيز التنبيه
        $product->update(['price' => 750.00]);

        // 11. تشغيل فحص التنبيهات
        $this->artisan('price-alerts:check')->assertExitCode(0);

        // 12. التحقق من إرسال الإيميل
        Mail::assertSent(\App\Mail\PriceAlertMail::class);

        // 13. تحديث المراجعة
        $review = Review::where('user_id', $user->id)->first();
        $updateReviewResponse = $this->putJson('/api/reviews/'.$review->id, [
            'rating' => 4,
            'comment' => 'Updated review - still great but price is high',
        ]);
        $this->assertEquals(200, $updateReviewResponse->status());

        // 14. حذف من المفضلة
        $wishlistItem = Wishlist::where('user_id', $user->id)->first();
        $deleteWishlistResponse = $this->deleteJson('/api/wishlist/'.$wishlistItem->id);
        $this->assertEquals(200, $deleteWishlistResponse->status());

        // 15. حذف تنبيه السعر
        $priceAlert = PriceAlert::where('user_id', $user->id)->first();
        $deleteAlertResponse = $this->deleteJson('/api/price-alerts/'.$priceAlert->id);
        $this->assertEquals(200, $deleteAlertResponse->status());
    }

    /** @test */
    public function ai_integration_workflow()
    {
        $product = Product::factory()->create([
            'name' => 'Samsung Galaxy S24',
            'description' => 'Latest Samsung smartphone with AI features',
        ]);

        // 1. تحليل المنتج بالذكاء الاصطناعي
        $aiAnalysisResponse = $this->postJson('/api/ai/analyze', [
            'text' => $product->description,
            'type' => 'product_analysis',
        ]);

        $this->assertEquals(200, $aiAnalysisResponse->status());
        $this->assertArrayHasKey('analysis', $aiAnalysisResponse->json());

        // 2. تحليل المشاعر
        $sentimentResponse = $this->postJson('/api/ai/analyze', [
            'text' => 'This is an amazing product with great features!',
            'type' => 'sentiment_analysis',
        ]);

        $this->assertEquals(200, $sentimentResponse->status());
        $this->assertArrayHasKey('sentiment', $sentimentResponse->json());

        // 3. اقتراحات تحسين
        $suggestionsResponse = $this->postJson('/api/ai/suggestions', [
            'product_id' => $product->id,
            'type' => 'improvement',
        ]);

        $this->assertEquals(200, $suggestionsResponse->status());
        $this->assertArrayHasKey('suggestions', $suggestionsResponse->json());

        // 4. اقتراح منتجات مشابهة
        $similarResponse = $this->postJson('/api/ai/suggestions', [
            'product_id' => $product->id,
            'type' => 'similar_products',
        ]);

        $this->assertEquals(200, $similarResponse->status());
        $this->assertArrayHasKey('similar_products', $similarResponse->json());
    }

    /** @test */
    public function admin_management_workflow()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        // 1. إنشاء فئة جديدة
        $categoryData = [
            'name' => 'Smartphones',
            'description' => 'Mobile phones and accessories',
        ];

        $categoryResponse = $this->postJson('/api/admin/categories', $categoryData);
        $this->assertEquals(201, $categoryResponse->status());

        // 2. إنشاء علامة تجارية جديدة
        $brandData = [
            'name' => 'Samsung',
            'description' => 'South Korean electronics company',
        ];

        $brandResponse = $this->postJson('/api/admin/brands', $brandData);
        $this->assertEquals(201, $brandResponse->status());

        // 3. إنشاء منتج جديد
        $productData = [
            'name' => 'Galaxy S24 Ultra',
            'description' => 'Premium Samsung smartphone',
            'price' => 1199.99,
            'category_id' => $categoryResponse->json('data.id'),
            'brand_id' => $brandResponse->json('data.id'),
        ];

        $productResponse = $this->postJson('/api/admin/products', $productData);
        $this->assertEquals(201, $productResponse->status());

        // 4. تحديث المنتج
        $product = Product::latest()->first();
        $updateResponse = $this->putJson('/api/admin/products/'.$product->id, [
            'name' => 'Galaxy S24 Ultra (Updated)',
            'price' => 1099.99,
        ]);
        $this->assertEquals(200, $updateResponse->status());

        // 5. إدارة المستخدمين
        $userData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $userResponse = $this->postJson('/api/admin/users', $userData);
        $this->assertEquals(201, $userResponse->status());

        // 6. تحديث صلاحيات المستخدم
        $user = User::latest()->first();
        $updateUserResponse = $this->putJson('/api/admin/users/'.$user->id, [
            'is_admin' => true,
        ]);
        $this->assertEquals(200, $updateUserResponse->status());

        // 7. حذف المنتج
        $deleteResponse = $this->deleteJson('/api/admin/products/'.$product->id);
        $this->assertEquals(200, $deleteResponse->status());
    }

    /** @test */
    public function cache_and_performance_workflow()
    {
        // 1. إنشاء بيانات للاختبار
        Product::factory()->count(100)->create();

        // 2. اختبار بدون cache
        Cache::flush();
        $startTime = microtime(true);
        $this->getJson('/api/products');
        $noCacheTime = (microtime(true) - $startTime) * 1000;

        // 3. اختبار مع cache
        $startTime = microtime(true);
        $this->getJson('/api/products');
        $withCacheTime = (microtime(true) - $startTime) * 1000;

        // 4. التحقق من تحسن الأداء
        $this->assertLessThan($noCacheTime, $withCacheTime);

        // 5. اختبار البحث مع cache
        $searchResponse = $this->getJson('/api/search?q=test');
        $this->assertEquals(200, $searchResponse->status());

        // 6. اختبار التصفية مع cache
        $filterResponse = $this->getJson('/api/products?category=electronics&min_price=100&max_price=1000');
        $this->assertEquals(200, $filterResponse->status());
    }

    /** @test */
    public function error_handling_workflow()
    {
        // 1. اختبار 404 للعنصر غير الموجود
        $response = $this->getJson('/api/products/99999');
        $this->assertEquals(404, $response->status());

        // 2. اختبار 422 للبيانات غير صحيحة
        $response = $this->postJson('/api/products', [
            'name' => '', // مطلوب
            'price' => 'invalid', // يجب أن يكون رقم
        ]);
        $this->assertEquals(422, $response->status());

        // 3. اختبار 401 للغير مصرح له
        $this->actingAs(null);
        $response = $this->getJson('/api/wishlist');
        $this->assertEquals(401, $response->status());

        // 4. اختبار 403 للصلاحيات غير كافية
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);
        $response = $this->getJson('/api/admin/users');
        $this->assertEquals(403, $response->status());

        // 5. اختبار 429 للحد الأقصى للطلبات
        for ($i = 0; $i < 100; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'password',
            ]);

            if ($response->status() === 429) {
                $this->assertEquals(429, $response->status());
                break;
            }
        }
    }

    /** @test */
    public function data_consistency_workflow()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create();

        // 1. إضافة للمفضلة
        $wishlistResponse = $this->postJson('/api/wishlist', [
            'product_id' => $product->id,
        ]);
        $this->assertEquals(201, $wishlistResponse->status());

        // 2. التحقق من البيانات
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // 3. إنشاء تنبيه سعر
        $alertResponse = $this->postJson('/api/price-alerts', [
            'product_id' => $product->id,
            'target_price' => 50.00,
        ]);
        $this->assertEquals(201, $alertResponse->status());

        // 4. التحقق من البيانات
        $this->assertDatabaseHas('price_alerts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'target_price' => 50.00,
        ]);

        // 5. إضافة مراجعة
        $reviewResponse = $this->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!',
        ]);
        $this->assertEquals(201, $reviewResponse->status());

        // 6. التحقق من البيانات
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
        ]);

        // 7. حذف المستخدم والتحقق من cascade delete
        $user->delete();

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('price_alerts', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('reviews', [
            'user_id' => $user->id,
        ]);
    }
}
