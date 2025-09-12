<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Models\Currency;
use App\Models\Language;
use App\Models\User;
use App\Models\UserLocaleSetting;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LocaleMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Use the smart factory state to create a specific, default language
        Language::factory()->english()->default()->create();

        Route::get('/test-locale', fn () => response()->json(['locale' => app()->getLocale()]))->middleware('locale');
    }

    public function test_middleware_sets_default_locale_if_nothing_is_provided(): void
    {
        $this->getJson('/test-locale')
            ->assertStatus(200)
            ->assertJson(['locale' => 'en']);
    }

    public function test_middleware_uses_session_values(): void
    {
        $language = Language::factory()->create(['code' => 'ar', 'native_name' => 'العربية']);

        $this->withSession(['locale_language' => 'ar'])
            ->getJson('/test-locale')
            ->assertStatus(200)
            ->assertJson(['locale' => 'ar']);
    }

    public function test_middleware_uses_authenticated_user_settings(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create(['code' => 'fr', 'native_name' => 'Français']);
        $currency = Currency::factory()->create();

        UserLocaleSetting::factory()->create([
            'user_id' => $user->id,
            'language_id' => $language->id,
            'currency_id' => $currency->id,
        ]);

        $this->actingAs($user)
            ->getJson('/test-locale')
            ->assertStatus(200)
            ->assertJson(['locale' => 'fr']);
    }
}
