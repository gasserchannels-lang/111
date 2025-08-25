<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\PriceAlert;
use App\Models\Wishlist;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_has_many_price_alerts()
    {
        $user = User::factory()
            ->has(PriceAlert::factory()->count(3))
            ->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->priceAlerts);
        $this->assertCount(3, $user->priceAlerts);
    }

    /** @test */
    public function user_has_many_wishlists()
    {
        $user = User::factory()
            ->has(Wishlist::factory()->count(2))
            ->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->wishlists);
        $this->assertCount(2, $user->wishlists);
    }

    /** @test */
    public function user_has_many_reviews()
    {
        $user = User::factory()
            ->has(Review::factory()->count(5))
            ->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->reviews);
        $this->assertCount(5, $user->reviews);
    }
}
