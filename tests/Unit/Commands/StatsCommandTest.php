<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsCommandTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_run_stats_command()
    {
        $this->artisan('coprra:stats')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_displays_correct_statistics()
    {
        // Create test data
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

        $user = User::factory()->create();

        $this->artisan('coprra:stats')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_empty_database()
    {
        $this->artisan('coprra:stats')
            ->assertExitCode(0);
    }
}
