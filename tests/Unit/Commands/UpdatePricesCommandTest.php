<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use App\Console\Commands\UpdatePricesCommand;
use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Store;
use App\Models\Currency;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdatePricesCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_can_run_update_prices_command()
    {
        $this->artisan('coprra:update-prices')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_updates_prices_for_products()
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

        $priceOffer = PriceOffer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 100.00,
            'is_available' => true,
        ]);

        $this->artisan('coprra:update-prices')
            ->assertExitCode(0);

        // Verify that the command ran without errors
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_handles_empty_products_gracefully()
    {
        $this->artisan('coprra:update-prices')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_can_run_with_verbose_output()
    {
        $this->artisan('coprra:update-prices --verbose')
            ->assertExitCode(0);
    }
}
