<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\PriceAlert;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceAlertControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $anotherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
    }

    // ... (الاختبارات الأخرى تبقى كما هي) ...

    public function test_index_displays_only_user_price_alerts()
    {
        PriceAlert::factory()->count(3)->create(['user_id' => $this->user->id]);
        PriceAlert::factory()->count(2)->create(['user_id' => $this->anotherUser->id]);

        $this->actingAs($this->user)
            ->get(route('price-alerts.index'))
            ->assertOk()
            ->assertViewIs('price-alerts.index')
            ->assertViewHas('priceAlerts', function ($priceAlerts) {
                return $priceAlerts->count() === 3;
            });
    }

    // ... (الاختبارات الأخرى تبقى كما هي) ...

    // region Show
    public function test_show_displays_correct_price_alert()
    {
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user) // قمنا بتخزين الاستجابة في متغير
            ->get(route('price-alerts.show', $priceAlert));

        $response->dump(); // ✅✅ هذا هو السطر التشخيصي في المكان الصحيح

        $response->assertOk()
            ->assertViewIs('price-alerts.show')
            ->assertViewHas('priceAlert', $priceAlert);
    }
    // endregion

    // ... (باقي الاختبارات تبقى كما هي) ...
}
