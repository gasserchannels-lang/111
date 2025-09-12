<?php

namespace Tests\Unit\Database;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceAlert;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdvancedDatabaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function database_constraints_work()
    {
        // اختبار unique constraint
        User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(QueryException::class);
        User::factory()->create(['email' => 'test@example.com']);
    }

    /** @test */
    public function foreign_key_constraints_work()
    {
        $product = Product::factory()->create();

        // محاولة إنشاء wishlist مع product_id غير موجود
        $this->expectException(QueryException::class);
        Wishlist::create([
            'user_id' => User::factory()->create()->id,
            'product_id' => 99999,
        ]);
    }

    /** @test */
    public function database_indexes_work()
    {
        // إنشاء بيانات للاختبار
        Product::factory()->count(1000)->create();

        $startTime = microtime(true);

        // استعلام يستخدم index
        $products = Product::where('is_active', true)
            ->where('price', '>', 100)
            ->get();

        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(100, $queryTime); // يجب أن يكون سريعاً
        \Log::info("Indexed query time: {$queryTime}ms");
    }

    /** @test */
    public function database_transactions_work()
    {
        DB::beginTransaction();

        try {
            $user = User::factory()->create();
            $product = Product::factory()->create();

            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);

            PriceAlert::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'target_price' => 50.00,
            ]);

            DB::commit();

            // التحقق من حفظ البيانات
            $this->assertDatabaseHas('wishlists', [
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            throw $e;
        }
    }

    /** @test */
    public function database_rollback_works()
    {
        DB::beginTransaction();

        try {
            $user = User::factory()->create();

            // محاولة إنشاء بيانات غير صحيحة
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => 99999, // غير موجود
            ]);

        } catch (\Exception $e) {
            DB::rollback();
        }

        // التحقق من عدم حفظ البيانات
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function database_relationships_work()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        // اختبار العلاقات
        $this->assertEquals($category->id, $product->category->id);
        $this->assertEquals($brand->id, $product->brand->id);

        // اختبار العلاقات العكسية
        $this->assertTrue($category->products->contains($product));
        $this->assertTrue($brand->products->contains($product));
    }

    /** @test */
    public function database_cascading_deletes_work()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // إنشاء بيانات مرتبطة
        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $priceAlert = PriceAlert::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'target_price' => 50.00,
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!',
        ]);

        // حذف المستخدم
        $user->delete();

        // التحقق من حذف البيانات المرتبطة
        $this->assertDatabaseMissing('wishlists', ['id' => $wishlist->id]);
        $this->assertDatabaseMissing('price_alerts', ['id' => $priceAlert->id]);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /** @test */
    public function database_soft_deletes_work()
    {
        $product = Product::factory()->create();

        // حذف ناعم
        $product->delete();

        // التحقق من عدم الظهور في الاستعلامات العادية
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
            'deleted_at' => null,
        ]);

        // التحقق من الظهور في الاستعلامات مع withTrashed
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);
    }

    /** @test */
    public function database_migrations_work()
    {
        // التحقق من وجود الجداول
        $tables = [
            'users', 'products', 'categories', 'brands',
            'wishlists', 'price_alerts', 'reviews',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(DB::getSchemaBuilder()->hasTable($table));
        }
    }

    /** @test */
    public function database_seeders_work()
    {
        $this->seed();

        // التحقق من وجود البيانات المبدئية
        $this->assertDatabaseHas('categories', ['name' => 'Electronics']);
        $this->assertDatabaseHas('brands', ['name' => 'Apple']);
    }

    /** @test */
    public function database_performance_with_large_datasets()
    {
        // إنشاء 10000 منتج
        Product::factory()->count(10000)->create();

        $startTime = microtime(true);

        // استعلام معقد
        $products = Product::with(['category', 'brand', 'reviews'])
            ->where('is_active', true)
            ->where('price', '>', 100)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(1000, $queryTime); // أقل من ثانية
        $this->assertCount(100, $products);

        \Log::info("Large dataset query time: {$queryTime}ms");
    }
}
