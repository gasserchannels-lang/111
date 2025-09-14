<?php

namespace Tests\AI;

use App\Services\ProductClassificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductClassificationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_classify_electronics()
    {
        $classifier = new ProductClassificationService;
        $productData = [
            'name' => 'لابتوب ديل',
            'description' => 'جهاز كمبيوتر محمول عالي الأداء',
            'price' => 5000,
        ];

        $category = $classifier->classify($productData);

        $this->assertEquals('إلكترونيات', $category);
    }

    #[Test]
    public function can_classify_clothing()
    {
        $classifier = new ProductClassificationService;
        $productData = [
            'name' => 'قميص قطني',
            'description' => 'قميص رجالي قطني 100%',
            'price' => 150,
        ];

        $category = $classifier->classify($productData);

        $this->assertEquals('ملابس', $category);
    }

    #[Test]
    public function can_classify_books()
    {
        $classifier = new ProductClassificationService;
        $productData = [
            'name' => 'كتاب البرمجة',
            'description' => 'كتاب تعليم البرمجة للمبتدئين',
            'price' => 80,
        ];

        $category = $classifier->classify($productData);

        $this->assertEquals('كتب', $category);
    }

    #[Test]
    public function can_classify_home_garden()
    {
        $classifier = new ProductClassificationService;
        $productData = [
            'name' => 'مقعد خشبي',
            'description' => 'مقعد خشبي للحديقة',
            'price' => 300,
        ];

        $category = $classifier->classify($productData);

        $this->assertEquals('منزل وحديقة', $category);
    }

    #[Test]
    public function can_classify_sports()
    {
        $classifier = new ProductClassificationService;
        $productData = [
            'name' => 'كرة قدم',
            'description' => 'كرة قدم رسمية للعبة',
            'price' => 120,
        ];

        $category = $classifier->classify($productData);

        $this->assertEquals('رياضة', $category);
    }

    #[Test]
    public function classification_confidence_is_high()
    {
        $classifier = new ProductClassificationService;
        $productData = [
            'name' => 'هاتف آيفون',
            'description' => 'هاتف ذكي من أبل',
            'price' => 4000,
        ];

        $result = $classifier->classifyWithConfidence($productData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('category', $result);
        $this->assertArrayHasKey('confidence', $result);
        $this->assertGreaterThan(0.8, $result['confidence']);
    }

    #[Test]
    public function can_handle_ambiguous_products()
    {
        $classifier = new ProductClassificationService;
        $productData = [
            'name' => 'ساعة ذكية',
            'description' => 'ساعة ذكية للرياضة',
            'price' => 800,
        ];

        $result = $classifier->classifyWithConfidence($productData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('category', $result);
        $this->assertArrayHasKey('confidence', $result);
        $this->assertGreaterThan(0.5, $result['confidence']);
    }

    #[Test]
    public function can_suggest_subcategories()
    {
        $classifier = new ProductClassificationService;
        $productData = [
            'name' => 'لابتوب للألعاب',
            'description' => 'لابتوب مخصص للألعاب عالية الأداء',
            'price' => 8000,
        ];

        $result = $classifier->classifyWithSubcategories($productData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('category', $result);
        $this->assertArrayHasKey('subcategories', $result);
        $this->assertContains('ألعاب', $result['subcategories']);
    }
}
