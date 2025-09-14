<?php

namespace Tests\AI;

use App\Services\TextProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TextProcessingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_process_arabic_text()
    {
        $textProcessor = new TextProcessingService;
        $arabicText = 'هذا نص عربي للاختبار';

        $result = $textProcessor->process($arabicText);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tokens', $result);
        $this->assertArrayHasKey('language', $result);
        $this->assertEquals('ar', $result['language']);
    }

    #[Test]
    public function can_extract_keywords()
    {
        $textProcessor = new TextProcessingService;
        $text = 'لابتوب ديل عالي الأداء بسعر مناسب';

        $keywords = $textProcessor->extractKeywords($text);

        $this->assertIsArray($keywords);
        $this->assertContains('لابتوب', $keywords);
        $this->assertContains('ديل', $keywords);
    }

    #[Test]
    public function can_detect_sentiment()
    {
        $textProcessor = new TextProcessingService;

        $positiveText = 'منتج رائع وممتاز';
        $negativeText = 'منتج سيء ومخيب للآمال';
        $neutralText = 'منتج عادي';

        $positiveSentiment = $textProcessor->detectSentiment($positiveText);
        $negativeSentiment = $textProcessor->detectSentiment($negativeText);
        $neutralSentiment = $textProcessor->detectSentiment($neutralText);

        $this->assertEquals('positive', $positiveSentiment);
        $this->assertEquals('negative', $negativeSentiment);
        $this->assertEquals('neutral', $neutralSentiment);
    }

    #[Test]
    public function can_remove_stop_words()
    {
        $textProcessor = new TextProcessingService;
        $text = 'هذا المنتج هو الأفضل في السوق';

        $processedText = $textProcessor->removeStopWords($text);

        $this->assertStringNotContainsString('هذا', $processedText);
        $this->assertStringNotContainsString('هو', $processedText);
        $this->assertStringContainsString('منتج', $processedText);
    }

    #[Test]
    public function can_normalize_text()
    {
        $textProcessor = new TextProcessingService;
        $text = 'هذا   نص   به   مسافات   كثيرة';

        $normalizedText = $textProcessor->normalize($text);

        $this->assertStringNotContainsString('  ', $normalizedText);
        $this->assertStringContainsString('هذا نص به مسافات كثيرة', $normalizedText);
    }

    #[Test]
    public function can_handle_mixed_languages()
    {
        $textProcessor = new TextProcessingService;
        $mixedText = 'هذا iPhone جديد بسعر $1000';

        $result = $textProcessor->process($mixedText);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tokens', $result);
        $this->assertContains('iPhone', $result['tokens']);
    }

    #[Test]
    public function can_extract_entities()
    {
        $textProcessor = new TextProcessingService;
        $text = 'شركة سامسونج تنتج هواتف جالاكسي';

        $entities = $textProcessor->extractEntities($text);

        $this->assertIsArray($entities);
        $this->assertArrayHasKey('organizations', $entities);
        $this->assertArrayHasKey('products', $entities);
        $this->assertContains('سامسونج', $entities['organizations']);
    }

    #[Test]
    public function can_summarize_text()
    {
        $textProcessor = new TextProcessingService;
        $longText = 'هذا منتج رائع جداً. إنه مصنوع من مواد عالية الجودة. السعر مناسب جداً. أنصح بشرائه. الجودة ممتازة. التوصيل سريع.';

        $summary = $textProcessor->summarize($longText, 2);

        $this->assertIsString($summary);
        $this->assertLessThan(strlen($longText), strlen($summary));
    }
}
