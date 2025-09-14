<?php

namespace Tests\Unit\Models;

use App\Models\PriceAlert;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceAlertTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_price_alert()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $priceAlert = PriceAlert::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'target_price' => 100.00,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(PriceAlert::class, $priceAlert);
        $this->assertEquals(100.00, $priceAlert->target_price);
        $this->assertTrue($priceAlert->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_user_relationship()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $priceAlert = PriceAlert::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(User::class, $priceAlert->user);
        $this->assertEquals($user->id, $priceAlert->user->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_product_relationship()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $priceAlert = PriceAlert::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(Product::class, $priceAlert->product);
        $this->assertEquals($product->id, $priceAlert->product->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_required_fields()
    {
        $priceAlert = new PriceAlert;

        $this->assertFalse($priceAlert->validate());
        $this->assertArrayHasKey('user_id', $priceAlert->getErrors());
        $this->assertArrayHasKey('product_id', $priceAlert->getErrors());
        $this->assertArrayHasKey('target_price', $priceAlert->getErrors());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_target_price_is_numeric()
    {
        $priceAlert = PriceAlert::factory()->make(['target_price' => 'invalid']);

        $this->assertFalse($priceAlert->validate());
        $this->assertArrayHasKey('target_price', $priceAlert->getErrors());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_target_price_is_positive()
    {
        $priceAlert = PriceAlert::factory()->make(['target_price' => -10.00]);

        $this->assertFalse($priceAlert->validate());
        $this->assertArrayHasKey('target_price', $priceAlert->getErrors());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_active_alerts()
    {
        PriceAlert::factory()->create(['is_active' => true]);
        PriceAlert::factory()->create(['is_active' => false]);

        $activeAlerts = PriceAlert::active()->get();

        $this->assertCount(1, $activeAlerts);
        $this->assertTrue($activeAlerts->first()->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_alerts_for_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        PriceAlert::factory()->create(['user_id' => $user1->id]);
        PriceAlert::factory()->create(['user_id' => $user2->id]);

        $user1Alerts = PriceAlert::forUser($user1->id)->get();

        $this->assertCount(1, $user1Alerts);
        $this->assertEquals($user1->id, $user1Alerts->first()->user_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_alerts_for_product()
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        PriceAlert::factory()->create(['product_id' => $product1->id]);
        PriceAlert::factory()->create(['product_id' => $product2->id]);

        $product1Alerts = PriceAlert::forProduct($product1->id)->get();

        $this->assertCount(1, $product1Alerts);
        $this->assertEquals($product1->id, $product1Alerts->first()->product_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_soft_delete_price_alert()
    {
        $priceAlert = PriceAlert::factory()->create();

        $priceAlert->delete();

        $this->assertSoftDeleted('price_alerts', ['id' => $priceAlert->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_restore_soft_deleted_price_alert()
    {
        $priceAlert = PriceAlert::factory()->create();
        $priceAlert->delete();

        $priceAlert->restore();

        $this->assertDatabaseHas('price_alerts', [
            'id' => $priceAlert->id,
            'deleted_at' => null,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_check_if_price_target_reached()
    {
        $priceAlert = PriceAlert::factory()->create(['target_price' => 100.00]);

        $this->assertTrue($priceAlert->isPriceTargetReached(90.00));
        $this->assertFalse($priceAlert->isPriceTargetReached(110.00));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_activate_alert()
    {
        $priceAlert = PriceAlert::factory()->create(['is_active' => false]);

        $priceAlert->activate();

        $this->assertTrue($priceAlert->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_deactivate_alert()
    {
        $priceAlert = PriceAlert::factory()->create(['is_active' => true]);

        $priceAlert->deactivate();

        $this->assertFalse($priceAlert->is_active);
    }
}
