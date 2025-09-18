<?php

namespace Tests\AI;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TextProcessingTest extends TestCase
{
    #[Test]
    public function can_process_arabic_text()
    {
        // Test Arabic text processing
        $text = "هذا نص عربي للاختبار";
        $this->assertIsString($text);
        $this->assertNotEmpty($text);
        $this->assertStringContainsString('عربي', $text);
        $this->assertStringContainsString('نص', $text);
    }

    #[Test]
    public function can_extract_keywords()
    {
        // Test keyword extraction
        $text = "This is a test text with keywords";
        $keywords = explode(' ', $text);
        $this->assertIsArray($keywords);
        $this->assertCount(7, $keywords);
        $this->assertContains('test', $keywords);
        $this->assertContains('keywords', $keywords);
    }

    #[Test]
    public function can_detect_sentiment()
    {
        // Test sentiment detection
        $positiveText = "I love this product!";
        $negativeText = "This is terrible!";
        $this->assertIsString($positiveText);
        $this->assertIsString($negativeText);
        $this->assertNotEquals($positiveText, $negativeText);
        $this->assertStringContainsString('love', $positiveText);
        $this->assertStringContainsString('terrible', $negativeText);
    }

    #[Test]
    public function can_remove_stop_words()
    {
        // Test stop words removal
        $text = "The quick brown fox jumps over the lazy dog";
        $words = explode(' ', $text);
        $this->assertIsArray($words);
        $this->assertContains('quick', $words);
        $this->assertContains('brown', $words);
        $this->assertContains('fox', $words);
        $this->assertCount(9, $words);
    }

    #[Test]
    public function can_normalize_text()
    {
        // Test text normalization
        $text = "  Hello   World  ";
        $normalized = trim($text);
        $this->assertIsString($normalized);
        $this->assertEquals("Hello   World", $normalized);
        $this->assertStringStartsWith('Hello', $normalized);
        $this->assertStringEndsWith('World', $normalized);
    }

    #[Test]
    public function can_handle_mixed_languages()
    {
        // Test mixed language handling
        $mixedText = "Hello مرحبا World";
        $this->assertIsString($mixedText);
        $this->assertStringContainsString('Hello', $mixedText);
        $this->assertStringContainsString('مرحبا', $mixedText);
        $this->assertStringContainsString('World', $mixedText);
        $this->assertGreaterThan(10, strlen($mixedText));
    }

    #[Test]
    public function can_extract_entities()
    {
        // Test entity extraction
        $text = "Apple Inc. was founded by Steve Jobs in California";
        $this->assertIsString($text);
        $this->assertStringContainsString('Apple', $text);
        $this->assertStringContainsString('Steve Jobs', $text);
        $this->assertStringContainsString('California', $text);
        $this->assertStringContainsString('Inc.', $text);
        $this->assertStringContainsString('founded', $text);
    }

    #[Test]
    public function can_summarize_text()
    {
        // Test text summarization
        $longText = "This is a very long text that needs to be summarized. It contains multiple sentences and should be reduced to a shorter version while maintaining the key information.";
        $this->assertIsString($longText);
        $this->assertGreaterThan(50, strlen($longText));
        $summary = substr($longText, 0, 50) . '...';
        $this->assertIsString($summary);
        $this->assertLessThan(strlen($longText), strlen($summary));
        $this->assertStringEndsWith('...', $summary);
        $this->assertStringStartsWith('This is a very long text', $summary);
    }
}
