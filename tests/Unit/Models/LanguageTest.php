<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Currency;
use App\Models\Language;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_language()
    {
        $language = Language::create([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertInstanceOf(Language::class, $language);
        $this->assertEquals('en', $language->code);
        $this->assertEquals('English', $language->name);
        $this->assertEquals('English', $language->native_name);
        $this->assertEquals('ltr', $language->direction);
        $this->assertTrue($language->is_active);
        $this->assertEquals(1, $language->sort_order);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_language_with_factory()
    {
        $language = Language::factory()->create();

        $this->assertInstanceOf(Language::class, $language);
        $this->assertNotNull($language->code);
        $this->assertNotNull($language->name);
        $this->assertNotNull($language->native_name);
        $this->assertNotNull($language->direction);
        $this->assertNotNull($language->is_active);
        $this->assertNotNull($language->sort_order);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_check_if_rtl()
    {
        $rtlLanguage = Language::factory()->create(['direction' => 'rtl']);
        $ltrLanguage = Language::factory()->create(['direction' => 'ltr']);

        $this->assertTrue($rtlLanguage->isRtl());
        $this->assertFalse($ltrLanguage->isRtl());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_find_by_code()
    {
        $language = Language::factory()->create(['code' => 'ar']);

        $foundLanguage = Language::findByCode('ar');

        $this->assertInstanceOf(Language::class, $foundLanguage);
        $this->assertEquals($language->id, $foundLanguage->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_null_when_code_not_found()
    {
        $foundLanguage = Language::findByCode('nonexistent');

        $this->assertNull($foundLanguage);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_active_languages()
    {
        Language::factory()->create(['is_active' => true]);
        Language::factory()->create(['is_active' => false]);

        $activeLanguages = Language::active()->get();

        $this->assertCount(1, $activeLanguages);
        $this->assertTrue($activeLanguages->first()->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_ordered_languages()
    {
        Language::factory()->create(['sort_order' => 3, 'name' => 'C']);
        Language::factory()->create(['sort_order' => 1, 'name' => 'A']);
        Language::factory()->create(['sort_order' => 2, 'name' => 'B']);

        $orderedLanguages = Language::ordered()->get();

        $this->assertEquals('A', $orderedLanguages->first()->name);
        $this->assertEquals('B', $orderedLanguages->skip(1)->first()->name);
        $this->assertEquals('C', $orderedLanguages->last()->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_have_currencies_relationship()
    {
        $language = Language::factory()->create();
        $currency = Currency::factory()->create();

        $language->currencies()->attach($currency->id, ['is_default' => true]);

        $this->assertTrue($language->currencies->contains($currency));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_default_currency()
    {
        $language = Language::factory()->create();
        $currency = Currency::factory()->create();

        $language->currencies()->attach($currency->id, ['is_default' => true]);

        $defaultCurrency = $language->defaultCurrency();

        $this->assertInstanceOf(Currency::class, $defaultCurrency);
        $this->assertEquals($currency->id, $defaultCurrency->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_null_when_no_default_currency()
    {
        $language = Language::factory()->create();

        $defaultCurrency = $language->defaultCurrency();

        $this->assertNull($defaultCurrency);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_have_user_locale_settings()
    {
        $language = Language::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $language->userLocaleSettings());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_english_language()
    {
        $language = Language::factory()->english()->create();

        $this->assertEquals('en', $language->code);
        $this->assertEquals('English', $language->name);
        $this->assertEquals('English', $language->native_name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_default_language()
    {
        $language = Language::factory()->default()->create();

        $this->assertTrue($language->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_set_direction()
    {
        $language = Language::factory()->create(['direction' => 'rtl']);

        $this->assertEquals('rtl', $language->direction);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_set_sort_order()
    {
        $language = Language::factory()->create(['sort_order' => 10]);

        $this->assertEquals(10, $language->sort_order);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_toggle_active_status()
    {
        $language = Language::factory()->create(['is_active' => false]);

        $language->update(['is_active' => true]);

        $this->assertTrue($language->fresh()->is_active);
    }
}
