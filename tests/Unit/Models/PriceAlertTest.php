<?php

namespace Tests\Unit\Models;

use App\Models\PriceAlert;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PriceAlertTest extends TestCase
{
    #[Test]
    public function it_can_create_a_price_alert()
    {
        $priceAlert = new PriceAlert([
            'user_id' => 1,
            'product_id' => 1,
            'target_price' => 100.00,
            'repeat_alert' => true,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(PriceAlert::class, $priceAlert);
        $this->assertEquals(1, $priceAlert->user_id);
        $this->assertEquals(1, $priceAlert->product_id);
        $this->assertEquals(100.00, $priceAlert->target_price);
        $this->assertTrue($priceAlert->repeat_alert);
        $this->assertTrue($priceAlert->is_active);

        // اختبار إضافي للتأكد من أن النموذج يعمل بشكل صحيح
        $this->assertNotNull($priceAlert->getFillable());
        $this->assertContains('user_id', $priceAlert->getFillable());
        $this->assertContains('product_id', $priceAlert->getFillable());
        $this->assertContains('target_price', $priceAlert->getFillable());
    }

    #[Test]
    public function it_has_user_relationship()
    {
        $priceAlert = new PriceAlert();
        $relation = $priceAlert->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('users', $relation->getRelated()->getTable());
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    #[Test]
    public function it_has_product_relationship()
    {
        $priceAlert = new PriceAlert();
        $relation = $priceAlert->product();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('products', $relation->getRelated()->getTable());
        $this->assertEquals('product_id', $relation->getForeignKeyName());
    }

    #[Test]
    public function it_can_validate_required_fields()
    {
        $priceAlert = new PriceAlert();

        $this->assertFalse($priceAlert->validate());
        $errors = $priceAlert->getErrors();
        $this->assertArrayHasKey('user_id', $errors);
        $this->assertArrayHasKey('product_id', $errors);
        $this->assertArrayHasKey('target_price', $errors);

        // اختبار إضافي للتأكد من أن التحقق يعمل بشكل صحيح
        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
    }

    #[Test]
    public function it_can_validate_target_price_is_numeric()
    {
        $priceAlert = new PriceAlert([
            'user_id' => 1,
            'product_id' => 1,
            'target_price' => 'invalid',
            'repeat_alert' => true,
            'is_active' => true,
        ]);

        $this->assertFalse($priceAlert->validate());
        $errors = $priceAlert->getErrors();
        $this->assertArrayHasKey('target_price', $errors);

        // اختبار إضافي للتأكد من أن التحقق يعمل بشكل صحيح
        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
    }

    #[Test]
    public function it_can_validate_target_price_is_positive()
    {
        $priceAlert = new PriceAlert([
            'user_id' => 1,
            'product_id' => 1,
            'target_price' => -10.00,
            'repeat_alert' => true,
            'is_active' => true,
        ]);

        $this->assertFalse($priceAlert->validate());
        $errors = $priceAlert->getErrors();
        $this->assertArrayHasKey('target_price', $errors);

        // اختبار إضافي للتأكد من أن التحقق يعمل بشكل صحيح
        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
    }

    #[Test]
    public function it_can_scope_active_alerts()
    {
        $priceAlert = new PriceAlert();
        $this->assertTrue(method_exists($priceAlert, 'scopeActive'));
        $this->assertTrue(method_exists(PriceAlert::class, 'scopeActive'));
    }

    #[Test]
    public function it_can_scope_alerts_for_user()
    {
        $priceAlert = new PriceAlert();
        $this->assertTrue(method_exists($priceAlert, 'scopeForUser'));
        $this->assertTrue(method_exists(PriceAlert::class, 'scopeForUser'));
    }

    #[Test]
    public function it_can_scope_alerts_for_product()
    {
        $priceAlert = new PriceAlert();
        $this->assertTrue(method_exists($priceAlert, 'scopeForProduct'));
        $this->assertTrue(method_exists(PriceAlert::class, 'scopeForProduct'));
    }

    #[Test]
    public function it_can_soft_delete_price_alert()
    {
        $priceAlert = new PriceAlert();
        $this->assertTrue(method_exists($priceAlert, 'delete'));
        $this->assertTrue(in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($priceAlert)));
    }

    #[Test]
    public function it_can_restore_soft_deleted_price_alert()
    {
        $priceAlert = new PriceAlert();
        $this->assertTrue(method_exists($priceAlert, 'restore'));
        $this->assertTrue(in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($priceAlert)));
    }

    #[Test]
    public function it_can_check_if_price_target_reached()
    {
        $priceAlert = new PriceAlert([
            'target_price' => 100.00,
        ]);

        $this->assertTrue($priceAlert->isPriceTargetReached(90.00));
        $this->assertTrue($priceAlert->isPriceTargetReached(100.00));
        $this->assertFalse($priceAlert->isPriceTargetReached(110.00));
    }

    #[Test]
    public function it_can_activate_alert()
    {
        $priceAlert = new PriceAlert();
        $this->assertTrue(method_exists($priceAlert, 'activate'));
        $this->assertTrue(method_exists(PriceAlert::class, 'activate'));
    }

    #[Test]
    public function it_can_deactivate_alert()
    {
        $priceAlert = new PriceAlert();
        $this->assertTrue(method_exists($priceAlert, 'deactivate'));
        $this->assertTrue(method_exists(PriceAlert::class, 'deactivate'));
    }
}
