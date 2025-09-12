<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\Wishlist;
use Tests\TestCase;

class WishlistControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_display_wishlist_index()
    {
        $response = $this->get('/wishlist');

        $response->assertStatus(200);
        $response->assertViewIs('wishlist.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_authentication_to_view_wishlist()
    {
        auth()->logout();

        $response = $this->get('/wishlist');

        $response->assertRedirect('/login');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_add_product_to_wishlist()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->post('/wishlist', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'added',
            'message' => 'Product added to wishlist successfully!',
        ]);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => auth()->id(),
            'product_id' => $product->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_authentication_to_add_to_wishlist()
    {
        auth()->logout();

        $response = $this->post('/wishlist', [
            'product_id' => 1,
        ]);

        $response->assertRedirect('/login');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_product_id_when_adding()
    {
        $response = $this->post('/wishlist', []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['product_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_product_exists_when_adding()
    {
        $response = $this->post('/wishlist', [
            'product_id' => 999999,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['product_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_exists_status_when_product_already_in_wishlist()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        // Add product to wishlist first time
        $this->post('/wishlist', ['product_id' => $product->id]);

        // Try to add same product again
        $response = $this->post('/wishlist', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'exists',
            'message' => 'Product is already in your wishlist.',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_remove_product_from_wishlist()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $wishlist = Wishlist::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
        ]);

        $response = $this->delete("/wishlist/{$wishlist->id}", [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'removed',
            'message' => 'Product removed from wishlist.',
        ]);

        $this->assertSoftDeleted('wishlists', [
            'id' => $wishlist->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_authentication_to_remove_from_wishlist()
    {
        auth()->logout();

        $response = $this->delete('/wishlist/1', [
            'product_id' => 1,
        ]);

        $response->assertRedirect('/login');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_not_found_when_removing_non_existent_wishlist_item()
    {
        $response = $this->delete('/wishlist/999999', [
            'product_id' => 999999,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['product_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_toggle_product_in_wishlist_add()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->post('/wishlist/toggle', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'added',
            'in_wishlist' => true,
        ]);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => auth()->id(),
            'product_id' => $product->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_toggle_product_in_wishlist_remove()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $wishlist = Wishlist::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
        ]);

        $response = $this->post('/wishlist/toggle', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'removed',
            'in_wishlist' => false,
        ]);

        $this->assertSoftDeleted('wishlists', [
            'id' => $wishlist->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_authentication_to_toggle_wishlist()
    {
        auth()->logout();

        $response = $this->post('/wishlist/toggle', [
            'product_id' => 1,
        ]);

        $response->assertRedirect('/login');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_product_id_when_toggling()
    {
        $response = $this->post('/wishlist/toggle', []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['product_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_loads_product_relationship_in_index()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        Wishlist::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
        ]);

        $response = $this->get('/wishlist');

        $response->assertStatus(200);
        $response->assertViewHas('wishlistItems');

        $wishlistItems = $response->viewData('wishlistItems');
        $this->assertCount(1, $wishlistItems);
        $this->assertNotNull($wishlistItems->first()->product);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_only_shows_current_user_wishlist_items()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product1 = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $product2 = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $otherUser = User::factory()->create();

        // Add product for current user
        Wishlist::create([
            'user_id' => auth()->id(),
            'product_id' => $product1->id,
        ]);

        // Add product for other user
        Wishlist::create([
            'user_id' => $otherUser->id,
            'product_id' => $product2->id,
        ]);

        $response = $this->get('/wishlist');

        $response->assertStatus(200);
        $wishlistItems = $response->viewData('wishlistItems');
        $this->assertCount(1, $wishlistItems);
        $this->assertEquals($product1->id, $wishlistItems->first()->product_id);
    }
}
