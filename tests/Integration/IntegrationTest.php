<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceAlert;
use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test complete product workflow.
     */
    public function test_complete_product_workflow(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create category and brand
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $store = Store::factory()->create();

        // Create a product
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        // Create price offers
        PriceOffer::factory()->count(3)->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
        ]);

        // Create reviews
        Review::factory()->count(5)->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        // Test product listing
        $response = $this->get('/api/products');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'price',
                    'category',
                    'brand',
                    'price_offers',
                ],
            ],
        ]);

        // Test product detail
        $response = $this->get("/api/products/{$product->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'price',
            'category',
            'brand',
            'price_offers',
            'reviews',
        ]);
    }

    /**
     * Test user interaction workflow.
     */
    public function test_user_interaction_workflow(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Test adding to wishlist
        $response = $this->actingAs($user)->post('/wishlist/toggle', [
            'product_id' => $product->id,
        ]);
        $response->assertStatus(200);

        // Verify wishlist item was created
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // Test creating price alert
        $response = $this->actingAs($user)->post('/price-alerts', [
            'product_id' => $product->id,
            'target_price' => 50.00,
        ]);
        $response->assertStatus(302);

        // Verify price alert was created
        $this->assertDatabaseHas('price_alerts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'target_price' => 50.00,
        ]);

        // Test creating review
        $response = $this->actingAs($user)->post('/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Great Product',
            'content' => 'Great product!',
        ]);
        $response->assertStatus(302);

        // Verify review was created
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'content' => 'Great product!',
        ]);
    }

    /**
     * Test search and filtering integration.
     */
    public function test_search_and_filtering_integration(): void
    {
        // Create test data
        $category = Category::factory()->create(['name' => 'Electronics']);
        $brand = Brand::factory()->create(['name' => 'Apple']);

        $product1 = Product::factory()->create([
            'name' => 'iPhone 15',
            'price' => 999.99,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        $product2 = Product::factory()->create([
            'name' => 'Samsung Galaxy',
            'price' => 799.99,
            'category_id' => $category->id,
        ]);

        // Test search by name
        $response = $this->get('/api/products?search=iPhone');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');

        // Test filter by category
        $response = $this->get("/api/products?category_id={$category->id}");
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');

        // Test filter by brand
        $response = $this->get("/api/products?brand_id={$brand->id}");
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');

        // Test price range filter
        $response = $this->get('/api/products?min_price=800&max_price=1000');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');

        // Test combined filters
        $response = $this->get("/api/products?category_id={$category->id}&brand_id={$brand->id}&search=iPhone");
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    /**
     * Test database relationships integrity.
     */
    public function test_database_relationships_integrity(): void
    {
        // Create test data
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $store = Store::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        // Test product relationships
        $this->assertEquals($category->id, $product->category->id);
        $this->assertEquals($brand->id, $product->brand->id);

        // Create related data
        $priceOffer = PriceOffer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
        ]);

        $review = Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $wishlist = Wishlist::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $priceAlert = PriceAlert::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        // Test relationships
        $this->assertEquals($product->id, $priceOffer->product->id);
        $this->assertEquals($product->id, $review->product->id);
        $this->assertEquals($product->id, $wishlist->product->id);
        $this->assertEquals($product->id, $priceAlert->product->id);

        // Test cascade deletes
        $product->delete();

        $this->assertDatabaseMissing('price_offers', ['id' => $priceOffer->id]);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertDatabaseMissing('wishlists', ['id' => $wishlist->id]);
        $this->assertDatabaseMissing('price_alerts', ['id' => $priceAlert->id]);
    }

    /**
     * Test API authentication flow.
     */
    public function test_api_authentication_flow(): void
    {
        $user = User::factory()->create();

        // Test unauthenticated request
        $response = $this->get('/api/user');
        $response->assertStatus(401);

        // Test authenticated request
        $response = $this->actingAs($user)->get('/api/user');
        $response->assertStatus(200);
        $response->assertJson(['id' => $user->id]);
    }

    /**
     * Test data consistency across operations.
     */
    public function test_data_consistency_across_operations(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100.00]);

        // Create price alert
        $priceAlert = PriceAlert::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'target_price' => 80.00,
        ]);

        // Update product price
        $product->update(['price' => 75.00]);

        // Check if price alert should be triggered
        $this->assertTrue($product->price <= $priceAlert->target_price);

        // Test price history
        $priceOffers = PriceOffer::factory()->count(3)->create([
            'product_id' => $product->id,
            'price' => 100.00,
        ]);

        $this->assertCount(3, $product->priceOffers);
    }

    /**
     * Test error handling and recovery.
     */
    public function test_error_handling_and_recovery(): void
    {
        // Test invalid product ID
        $response = $this->get('/api/products/99999');
        $response->assertStatus(404);

        // Test invalid category ID
        $response = $this->get('/api/products?category_id=99999');
        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');

        // Test malformed request
        $response = $this->post('/api/products', [
            'name' => '', // Invalid empty name
            'price' => 'invalid', // Invalid price
        ]);
        $response->assertStatus(422);
    }

    /**
     * Test performance under load.
     */
    public function test_performance_under_load(): void
    {
        // Create large dataset
        $categories = Category::factory()->count(10)->create();
        $brands = Brand::factory()->count(20)->create();
        $products = Product::factory()->count(100)->create([
            'category_id' => fn () => $categories->random()->id,
            'brand_id' => fn () => $brands->random()->id,
        ]);

        $startTime = microtime(true);

        // Perform multiple operations
        for ($i = 0; $i < 10; $i++) {
            $response = $this->get('/api/products');
            $response->assertStatus(200);
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;

        // Should complete within reasonable time
        $this->assertLessThan(5000, $totalTime, 'Performance test failed: '.$totalTime.'ms');
    }
}
