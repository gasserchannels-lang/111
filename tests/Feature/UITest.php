<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class UITest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test homepage loads correctly
     */
    public function test_homepage_loads(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('COPRRA');
        $response->assertSee('price comparison');
    }

    /**
     * Test products page loads correctly
     */
    public function test_products_page_loads(): void
    {
        $response = $this->get('/products');
        
        $response->assertStatus(200);
        $response->assertSee('Products');
    }

    /**
     * Test product detail page loads correctly
     */
    public function test_product_detail_page_loads(): void
    {
        $product = Product::factory()->create();
        
        $response = $this->get("/products/{$product->id}");
        
        $response->assertStatus(200);
        $response->assertSee($product->name);
    }

    /**
     * Test categories page loads correctly
     */
    public function test_categories_page_loads(): void
    {
        $response = $this->get('/categories');
        
        $response->assertStatus(200);
        $response->assertSee('Categories');
    }

    /**
     * Test brands page loads correctly
     */
    public function test_brands_page_loads(): void
    {
        $response = $this->get('/brands');
        
        $response->assertStatus(200);
        $response->assertSee('Brands');
    }

    /**
     * Test search functionality
     */
    public function test_search_functionality(): void
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        
        $response = $this->get('/products?search=Test');
        
        $response->assertStatus(200);
        $response->assertSee('Test Product');
    }

    /**
     * Test filtering by category
     */
    public function test_filter_by_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        $response = $this->get("/products?category_id={$category->id}");
        
        $response->assertStatus(200);
        $response->assertSee($product->name);
    }

    /**
     * Test filtering by brand
     */
    public function test_filter_by_brand(): void
    {
        $brand = Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id]);
        
        $response = $this->get("/products?brand_id={$brand->id}");
        
        $response->assertStatus(200);
        $response->assertSee($product->name);
    }

    /**
     * Test price range filtering
     */
    public function test_price_range_filtering(): void
    {
        $product = Product::factory()->create(['price' => 100.00]);
        
        $response = $this->get('/products?min_price=50&max_price=150');
        
        $response->assertStatus(200);
        $response->assertSee($product->name);
    }

    /**
     * Test sorting functionality
     */
    public function test_sorting_functionality(): void
    {
        Product::factory()->create(['name' => 'Apple Product', 'price' => 200.00]);
        Product::factory()->create(['name' => 'Banana Product', 'price' => 100.00]);
        
        // Test sorting by name
        $response = $this->get('/products?sort=name');
        $response->assertStatus(200);
        
        // Test sorting by price
        $response = $this->get('/products?sort=price');
        $response->assertStatus(200);
    }

    /**
     * Test pagination
     */
    public function test_pagination(): void
    {
        // Create more products than the default page size
        Product::factory()->count(25)->create();
        
        $response = $this->get('/products');
        
        $response->assertStatus(200);
        $response->assertSee('Next');
        $response->assertSee('Previous');
    }

    /**
     * Test responsive design elements
     */
    public function test_responsive_design_elements(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for responsive meta tag
        $response->assertSee('viewport');
        
        // Check for mobile-friendly elements
        $response->assertSee('mobile');
    }

    /**
     * Test accessibility features
     */
    public function test_accessibility_features(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for alt attributes on images
        $response->assertSee('alt=');
        
        // Check for proper heading structure
        $response->assertSee('<h1');
        $response->assertSee('<h2');
    }

    /**
     * Test error pages
     */
    public function test_404_page(): void
    {
        $response = $this->get('/non-existent-page');
        
        $response->assertStatus(404);
    }

    /**
     * Test admin pages require authentication
     */
    public function test_admin_pages_require_authentication(): void
    {
        $response = $this->get('/admin');
        
        $response->assertRedirect('/login');
    }

    /**
     * Test authenticated user can access admin
     */
    public function test_authenticated_user_can_access_admin(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        
        $response = $this->actingAs($user)->get('/admin');
        
        $response->assertStatus(200);
    }

    /**
     * Test wishlist functionality
     */
    public function test_wishlist_functionality(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $response = $this->actingAs($user)->post('/wishlist/toggle', [
            'product_id' => $product->id
        ]);
        
        $response->assertStatus(200);
    }

    /**
     * Test price alert functionality
     */
    public function test_price_alert_functionality(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $response = $this->actingAs($user)->post('/price-alerts', [
            'product_id' => $product->id,
            'target_price' => 50.00
        ]);
        
        $response->assertStatus(200);
    }

    /**
     * Test review functionality
     */
    public function test_review_functionality(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $response = $this->actingAs($user)->post('/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'content' => 'Great product!'
        ]);
        
        $response->assertStatus(200);
    }

    /**
     * Test API endpoints return JSON
     */
    public function test_api_endpoints_return_json(): void
    {
        $response = $this->get('/api/products');
        
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');
    }

    /**
     * Test health check endpoint
     */
    public function test_health_check_endpoint(): void
    {
        $response = $this->get('/health');
        
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'healthy'
        ]);
    }
}
