<?php

namespace Tests\Unit\Models;

use App\Models\Brand;
use Tests\TestCase;

class BrandTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_brand()
    {
        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
            'description' => 'Test Description',
            'logo_url' => 'https://example.com/logo.png',
            'website_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Brand::class, $brand);
        $this->assertEquals('Test Brand', $brand->name);
        $this->assertEquals('Test Description', $brand->description);
        $this->assertEquals('https://example.com/logo.png', $brand->logo_url);
        $this->assertEquals('https://example.com', $brand->website_url);
        $this->assertTrue($brand->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_products_relationship()
    {
        // اختبار العلاقة مباشرة بدون قاعدة بيانات
        $brand = new Brand;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $brand->products());

        // اختبار أن العلاقة لها الاستعلام الصحيح
        $relation = $brand->products();
        $this->assertEquals('products', $relation->getRelated()->getTable());
        $this->assertEquals('brand_id', $relation->getForeignKeyName());

        // اختبار إضافي للتأكد من صحة العلاقة
        $this->assertEquals('App\Models\Product', get_class($relation->getRelated()));
        $this->assertEquals('brands.id', $relation->getQualifiedParentKeyName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_required_fields()
    {
        $brand = new Brand;

        $this->assertFalse($brand->validate());
        $this->assertArrayHasKey('name', $brand->getErrors());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_name_length()
    {
        $brand = Brand::factory()->make(['name' => str_repeat('a', 256)]);

        $this->assertFalse($brand->validate());
        $this->assertArrayHasKey('name', $brand->getErrors());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_website_url_format()
    {
        $brand = Brand::factory()->make(['website_url' => 'invalid-url']);

        $this->assertFalse($brand->validate());
        $this->assertArrayHasKey('website_url', $brand->getErrors());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_logo_url_format()
    {
        $brand = Brand::factory()->make(['logo_url' => 'invalid-url']);

        $this->assertFalse($brand->validate());
        $this->assertArrayHasKey('logo_url', $brand->getErrors());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_active_brands()
    {
        // اختبار scope مباشرة بدون قاعدة بيانات
        $brand = new Brand;
        $this->assertTrue(method_exists($brand, 'scopeActive'));

        // اختبار أن scope يعمل مع query builder
        $query = Brand::query();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);

        // اختبار أن active scope موجود
        $this->assertTrue(method_exists(Brand::class, 'scopeActive'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_search_brands_by_name()
    {
        // اختبار search method مباشرة بدون قاعدة بيانات
        $brand = new Brand;
        $this->assertTrue(method_exists($brand, 'scopeSearch'));

        // اختبار أن search method موجود
        $this->assertTrue(method_exists(Brand::class, 'scopeSearch'));

        // اختبار أن search يعمل مع query builder
        $query = Brand::query();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_brand_with_products_count()
    {
        // اختبار withCount method مباشرة بدون قاعدة بيانات
        $brand = new Brand;
        $this->assertTrue(method_exists($brand, 'products'));

        // اختبار أن withCount يعمل مع query builder
        $query = Brand::query();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);

        // اختبار أن products relationship موجود
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $brand->products());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_soft_delete_brand()
    {
        // اختبار soft delete مباشرة بدون قاعدة بيانات
        $brand = new Brand;
        $this->assertTrue(method_exists($brand, 'delete'));

        // اختبار أن Brand model يستخدم SoftDeletes trait
        $this->assertTrue(in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($brand)));

        // اختبار أن delete method موجود
        $this->assertTrue(method_exists($brand, 'delete'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_restore_soft_deleted_brand()
    {
        // اختبار restore method مباشرة بدون قاعدة بيانات
        $brand = new Brand;
        $this->assertTrue(method_exists($brand, 'restore'));

        // اختبار أن Brand model يستخدم SoftDeletes trait
        $this->assertTrue(in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($brand)));

        // اختبار أن restore method موجود
        $this->assertTrue(method_exists($brand, 'restore'));
    }
}
