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

    // region Authorization Tests (الأهم)

    public function test_user_cannot_access_another_users_price_alert()
    {
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->anotherUser->id]);

        $this->actingAs($this->user)->get(route('price-alerts.show', $priceAlert))->assertStatus(403);
        $this->actingAs($this->user)->get(route('price-alerts.edit', $priceAlert))->assertStatus(403);
        $this->actingAs($this->user)->put(route('price-alerts.update', $priceAlert), [])->assertStatus(403);
        $this->actingAs($this->user)->delete(route('price-alerts.destroy', $priceAlert))->assertStatus(403);
        $this->actingAs($this->user)->patch(route('price-alerts.toggle', $priceAlert))->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login()
    {
        $this->get(route('price-alerts.index'))->assertRedirect('/login');
        $this->get(route('price-alerts.create'))->assertRedirect('/login');
        $this->post(route('price-alerts.store'))->assertRedirect('/login');
    }

    // endregion

    // region Index
    public function test_index_displays_only_user_price_alerts()
    {
        PriceAlert::factory()->count(3)->create(['user_id' => $this->user->id]);
        PriceAlert::factory()->count(2)->create(['user_id' => $this->anotherUser->id]);

        $response = $this->actingAs($this->user) // قمنا بتخزين الاستجابة في متغير
            ->get(route('price-alerts.index'));

        $response->dump(); // ✅✅ هذا هو السطر التشخيصي الذي سيطبع لنا الخطأ

        $response->assertOk()
            ->assertViewIs('price-alerts.index')
            ->assertViewHas('priceAlerts', function ($priceAlerts) {
                return $priceAlerts->count() === 3;
            });
    }
    // endregion

    // region Create
    public function test_create_page_is_displayed_correctly()
    {
        $product = Product::factory()->create();

        $this->actingAs($this->user)
            ->get(route('price-alerts.create', ['product_id' => $product->id]))
            ->assertOk()
            ->assertViewIs('price-alerts.create')
            ->assertViewHas('product', $product);
    }
    // endregion

    // region Store
    public function test_user_can_create_a_price_alert()
    {
        $product = Product::factory()->create();
        $alertData = [
            'product_id' => $product->id,
            'target_price' => 100.50,
            'repeat_alert' => true,
        ];

        $this->actingAs($this->user)
            ->post(route('price-alerts.store'), $alertData)
            ->assertRedirect(route('price-alerts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('price_alerts', [
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'target_price' => 100.50,
            'repeat_alert' => true,
        ]);
    }

    public function test_store_validates_request_data()
    {
        $this->actingAs($this->user)
            ->post(route('price-alerts.store'), [])
            ->assertSessionHasErrors(['product_id', 'target_price']);
    }
    // endregion

    // region Show
    public function test_show_displays_correct_price_alert()
    {
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->get(route('price-alerts.show', $priceAlert))
            ->assertOk()
            ->assertViewIs('price-alerts.show')
            ->assertViewHas('priceAlert', $priceAlert);
    }
    // endregion

    // region Edit
    public function test_edit_displays_correct_price_alert()
    {
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->get(route('price-alerts.edit', $priceAlert))
            ->assertOk()
            ->assertViewIs('price-alerts.edit')
            ->assertViewHas('priceAlert', $priceAlert);
    }
    // endregion

    // region Update
    public function test_user_can_update_their_price_alert()
    {
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id]);
        $updateData = [
            'target_price' => 150.75,
            'repeat_alert' => true,
        ];

        $this->actingAs($this->user)
            ->put(route('price-alerts.update', $priceAlert), $updateData)
            ->assertRedirect(route('price-alerts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('price_alerts', [
            'id' => $priceAlert->id,
            'target_price' => 150.75,
            'repeat_alert' => true,
        ]);
    }
    // endregion

    // region Destroy
    public function test_user_can_delete_their_price_alert()
    {
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->delete(route('price-alerts.destroy', $priceAlert))
            ->assertRedirect(route('price-alerts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('price_alerts', ['id' => $priceAlert->id]);
    }
    // endregion

    // region Toggle
    public function test_user_can_toggle_alert_status()
    {
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id, 'is_active' => true]);

        $this->actingAs($this->user)
            ->from(route('price-alerts.index'))
            ->patch(route('price-alerts.toggle', $priceAlert))
            ->assertRedirect(route('price-alerts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('price_alerts', [
            'id' => $priceAlert->id,
            'is_active' => false,
        ]);
    }
    // endregion
}
