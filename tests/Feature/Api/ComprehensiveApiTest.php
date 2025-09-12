<?php

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceAlert;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function products_api_endpoints_work()
    {
        // GET /api/products
        $response = $this->getJson('/api/products');
        $this->assertEquals(200, $response->status());
        $this->assertArrayHasKey('data', $response->json());

        // GET /api/products/{id}
        $product = Product::factory()->create();
        $response = $this->getJson('/api/products/'.$product->id);
        $this->assertEquals(200, $response->status());
        $this->assertEquals($product->id, $response->json('data.id'));

        // POST /api/products (admin only)
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'api');

        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category_id' => Category::factory()->create()->id,
            'brand_id' => Brand::factory()->create()->id,
        ];

        $response = $this->postJson('/api/products', $productData);
        $this->assertEquals(201, $response->status());
    }

    /** @test */
    public function categories_api_endpoints_work()
    {
        // GET /api/categories
        $response = $this->getJson('/api/categories');
        $this->assertEquals(200, $response->status());

        // GET /api/categories/{id}/products
        $category = Category::factory()->create();
        $response = $this->getJson('/api/categories/'.$category->id.'/products');
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function brands_api_endpoints_work()
    {
        // GET /api/brands
        $response = $this->getJson('/api/brands');
        $this->assertEquals(200, $response->status());

        // GET /api/brands/{id}/products
        $brand = Brand::factory()->create();
        $response = $this->getJson('/api/brands/'.$brand->id.'/products');
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function wishlist_api_endpoints_work()
    {
        $product = Product::factory()->create();

        // POST /api/wishlist
        $response = $this->postJson('/api/wishlist', [
            'product_id' => $product->id,
        ]);
        $this->assertEquals(201, $response->status());

        // GET /api/wishlist
        $response = $this->getJson('/api/wishlist');
        $this->assertEquals(200, $response->status());
        $this->assertCount(1, $response->json('data'));

        // DELETE /api/wishlist/{id}
        $wishlistItem = Wishlist::where('user_id', $this->user->id)
            ->where('product_id', $product->id)
            ->first();

        $response = $this->deleteJson('/api/wishlist/'.$wishlistItem->id);
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function price_alerts_api_endpoints_work()
    {
        $product = Product::factory()->create();

        // POST /api/price-alerts
        $response = $this->postJson('/api/price-alerts', [
            'product_id' => $product->id,
            'target_price' => 50.00,
        ]);
        $this->assertEquals(201, $response->status());

        // GET /api/price-alerts
        $response = $this->getJson('/api/price-alerts');
        $this->assertEquals(200, $response->status());
        $this->assertCount(1, $response->json('data'));

        // DELETE /api/price-alerts/{id}
        $alert = PriceAlert::where('user_id', $this->user->id)->first();
        $response = $this->deleteJson('/api/price-alerts/'.$alert->id);
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function reviews_api_endpoints_work()
    {
        $product = Product::factory()->create();

        // POST /api/reviews
        $response = $this->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!',
        ]);
        $this->assertEquals(201, $response->status());

        // GET /api/products/{id}/reviews
        $response = $this->getJson('/api/products/'.$product->id.'/reviews');
        $this->assertEquals(200, $response->status());
        $this->assertCount(1, $response->json('data'));

        // PUT /api/reviews/{id}
        $review = Review::where('user_id', $this->user->id)->first();
        $response = $this->putJson('/api/reviews/'.$review->id, [
            'rating' => 4,
            'comment' => 'Updated review',
        ]);
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function search_api_works()
    {
        // إنشاء منتجات للبحث
        Product::factory()->create(['name' => 'iPhone 15 Pro']);
        Product::factory()->create(['name' => 'Samsung Galaxy S24']);
        Product::factory()->create(['name' => 'Google Pixel 8']);

        // البحث النصي
        $response = $this->getJson('/api/search?q=iPhone');
        $this->assertEquals(200, $response->status());
        $this->assertCount(1, $response->json('data'));

        // البحث بالفلترة
        $response = $this->getJson('/api/search?q=phone&category=electronics&min_price=100&max_price=1000');
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function ai_api_endpoints_work()
    {
        // تحليل النص
        $response = $this->postJson('/api/ai/analyze', [
            'text' => 'This is a great product',
            'type' => 'sentiment_analysis',
        ]);
        $this->assertEquals(200, $response->status());
        $this->assertArrayHasKey('analysis', $response->json());

        // اقتراحات المنتجات
        $response = $this->postJson('/api/ai/suggestions', [
            'product_id' => Product::factory()->create()->id,
            'type' => 'similar_products',
        ]);
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function api_pagination_works()
    {
        // إنشاء 25 منتج
        Product::factory()->count(25)->create();

        // الصفحة الأولى
        $response = $this->getJson('/api/products?page=1&per_page=10');
        $this->assertEquals(200, $response->status());
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('total'));

        // الصفحة الثانية
        $response = $this->getJson('/api/products?page=2&per_page=10');
        $this->assertEquals(200, $response->status());
        $this->assertCount(10, $response->json('data'));
    }

    /** @test */
    public function api_error_handling_works()
    {
        // 404 للعنصر غير الموجود
        $response = $this->getJson('/api/products/99999');
        $this->assertEquals(404, $response->status());

        // 422 للبيانات غير صحيحة
        $response = $this->postJson('/api/wishlist', [
            'product_id' => 'invalid',
        ]);
        $this->assertEquals(422, $response->status());

        // 401 للغير مصرح له
        $this->actingAs(null, 'api');
        $response = $this->getJson('/api/wishlist');
        $this->assertEquals(401, $response->status());
    }

    /** @test */
    public function api_response_format_is_consistent()
    {
        $response = $this->getJson('/api/products');
        $data = $response->json();

        // التحقق من تنسيق الاستجابة
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('links', $data);

        // التحقق من تنسيق البيانات
        if (! empty($data['data'])) {
            $product = $data['data'][0];
            $this->assertArrayHasKey('id', $product);
            $this->assertArrayHasKey('name', $product);
            $this->assertArrayHasKey('price', $product);
        }
    }
}
