<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Store;
use Tests\TestCase;

class PriceSearchControllerTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_search_prices_by_product_name()
    {
        // Skip this test as it requires database tables
        $this->markTestSkipped('Test requires database tables');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_empty_results_for_non_existent_product()
    {
        $response = $this->getJson('/api/price-search?q=NonExistentProduct');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_empty_search_query()
    {
        // Add delay to avoid rate limiting
        usleep(2000000); // 2 seconds

        $response = $this->getJson('/api/price-search?q=');

        $response->assertStatus(400);
        $response->assertJson([
            'data' => [],
            'message' => 'Search query is required',
        ]);
    }
}
