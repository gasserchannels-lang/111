<?php

namespace Tests\Feature\Middleware;

use App\Models\Currency;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LocaleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Define a test route that uses the middleware
        Route::get('/_test/locale', function () {
            return response()->json([
                'language' => app()->getLocale(),
                'currency' => session('locale_currency'),
            ]);
        })->middleware('locale');

        // Create default language and currency
        Language::factory()->create(['code' => 'en', 'is_default' => true]);
        Currency::factory()->create(['code' => 'USD', 'is_default' => true]);
    }

    public function test_middleware_sets_default_locale_if_nothing_is_provided()
    {
        $this->get('/_test/locale')
            ->assertOk()
            ->assertJson([
                'language' => 'en',
                'currency' => 'USD',
            ]);
    }

    public function test_middleware_uses_session_values()
    {
        Language::factory()->create(['code' => 'fr']);
        Currency::factory()->create(['code' => 'EUR']);

        $this->withSession(['locale_language' => 'fr', 'locale_currency' => 'EUR'])
            ->get('/_test/locale')
            ->assertOk()
            ->assertJson([
                'language' => 'fr',
                'currency' => 'EUR',
            ]);
    }

    public function test_middleware_uses_authenticated_user_settings()
    {
        $user = User::factory()->create();
        $language = Language::factory()->create(['code' => 'ar']);
        $currency = Currency::factory()->create(['code' => 'SAR']);
        $user->localeSetting()->create([
            'language_id' => $language->id,
            'currency_id' => $currency->id,
        ]);

        $this->actingAs($user)
            ->get('/_test/locale')
            ->assertOk()
            ->assertJson([
                'language' => 'ar',
                'currency' => 'SAR',
            ]);
    }
}
