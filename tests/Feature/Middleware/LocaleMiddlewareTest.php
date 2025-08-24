<?php

namespace Tests\Feature\Middleware;

use App\Models\Language;
use App\Models\User;
use App\Models\UserLocaleSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LocaleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a default language for fallback
        Language::factory()->create(['code' => 'en', 'is_default' => true]);

        // Define a dummy route for testing middleware
        Route::get('/test-locale', function () {
            return response()->json(['locale' => app()->getLocale()]);
        })->middleware('locale');
    }

    public function test_middleware_sets_default_locale_if_nothing_is_provided()
    {
        $this->getJson('/test-locale')
            ->assertStatus(200)
            ->assertJson(['locale' => 'en']);
    }

    public function test_middleware_uses_session_values()
    {
        Language::factory()->create(['code' => 'ar']);

        $this->withSession(['locale_language' => 'ar'])
            ->getJson('/test-locale')
            ->assertStatus(200)
            ->assertJson(['locale' => 'ar']);
    }

    public function test_middleware_uses_authenticated_user_settings()
    {
        $user = User::factory()->create();
        $language = Language::factory()->create(['code' => 'fr']);
        UserLocaleSetting::factory()->create([
            'user_id' => $user->id,
            'language_id' => $language->id,
        ]);

        $this->actingAs($user)
            ->getJson('/test-locale')
            ->assertStatus(200)
            ->assertJson(['locale' => 'fr']);
    }
}
