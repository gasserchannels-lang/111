<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_user()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_check_if_user_is_admin()
    {
        $adminUser = User::factory()->create(['is_admin' => true]);
        $regularUser = User::factory()->create(['is_admin' => false]);

        $this->assertTrue($adminUser->isAdmin());
        $this->assertFalse($regularUser->isAdmin());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_wishlist_relationship()
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->wishlists());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_reviews_relationship()
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->reviews());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_price_alerts_relationship()
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->priceAlerts());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_locale_setting_relationship()
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $user->localeSetting());
    }
}
