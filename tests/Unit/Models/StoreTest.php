<?php

namespace Tests\Unit\Models;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_store()
    {
        $store = Store::factory()->create([
            'name' => 'Test Store',
            'description' => 'Test Description',
            'website_url' => 'https://example.com',
            'logo_url' => 'https://example.com/logo.png',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Store::class, $store);
        $this->assertEquals('Test Store', $store->name);
        $this->assertEquals('Test Description', $store->description);
        $this->assertEquals('https://example.com', $store->website_url);
        $this->assertEquals('https://example.com/logo.png', $store->logo_url);
        $this->assertTrue($store->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_products_relationship()
    {
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $store->products);
        $this->assertCount(1, $store->products);
        $this->assertTrue($store->products->contains($product));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_required_fields()
    {
        $store = new Store;

        $this->assertFalse($store->validate());
        $this->assertArrayHasKey('name', $store->getErrors());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_name_length()
    {
        $store = Store::factory()->make(['name' => str_repeat('a', 256)]);

        $this->assertFalse($store->validate());
        $this->assertArrayHasKey('name', $store->getErrors());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_website_url_format()
    {
        $store = Store::factory()->make(['website_url' => 'invalid-url']);

        $this->assertFalse($store->validate());
        $this->assertArrayHasKey('website_url', $store->getErrors());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_logo_url_format()
    {
        $store = Store::factory()->make(['logo_url' => 'invalid-url']);

        $this->assertFalse($store->validate());
        $this->assertArrayHasKey('logo_url', $store->getErrors());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_active_stores()
    {
        Store::factory()->create(['is_active' => true]);
        Store::factory()->create(['is_active' => false]);

        $activeStores = Store::active()->get();

        $this->assertCount(1, $activeStores);
        $this->assertTrue($activeStores->first()->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_search_stores_by_name()
    {
        Store::factory()->create(['name' => 'Amazon']);
        Store::factory()->create(['name' => 'eBay']);
        Store::factory()->create(['name' => 'Walmart']);

        $results = Store::search('Amazon')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Amazon', $results->first()->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_store_with_products_count()
    {
        $store = Store::factory()->create();
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            Product::factory()->create([
                'store_id' => $store->id,
                'brand_id' => $brand->id,
                'category_id' => $category->id,
            ]);
        }

        $storeWithCount = Store::withCount('products')->find($store->id);

        $this->assertEquals(3, $storeWithCount->products_count);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_soft_delete_store()
    {
        $store = Store::factory()->create();

        $store->delete();

        $this->assertSoftDeleted('stores', ['id' => $store->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_restore_soft_deleted_store()
    {
        $store = Store::factory()->create();
        $store->delete();

        $store->restore();

        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'deleted_at' => null,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_generate_affiliate_url()
    {
        $store = Store::factory()->create([
            'website_url' => 'https://example.com',
            'affiliate_base_url' => 'https://affiliate.example.com?code={AFFILIATE_CODE}&url={URL}',
            'affiliate_code' => 'AFF123',
        ]);

        $affiliateUrl = $store->generateAffiliateUrl('https://example.com/product');

        $this->assertStringContainsString('AFF123', $affiliateUrl);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_original_url_when_no_affiliate_code()
    {
        $store = Store::factory()->create([
            'website_url' => 'https://example.com',
            'affiliate_code' => null,
        ]);

        $originalUrl = 'https://example.com/product';
        $affiliateUrl = $store->generateAffiliateUrl($originalUrl);

        $this->assertEquals($originalUrl, $affiliateUrl);
    }
}
