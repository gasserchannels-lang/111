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

    // region -------------------- 1. View Lifecycle (Index & Show) --------------------
    // ... (هذا القسم يبقى كما هو بدون تغيير) ...
    /** @test */
    public function index_displays_only_user_price_alerts()
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

    /** @test */
    public function show_displays_correct_price_alert_for_owner()
    {
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->get(route('price-alerts.show', $priceAlert))
            ->assertStatus(200)
            ->assertViewIs('price-alerts.show')
            ->assertSee($priceAlert->product->name);
    }

    /** @test */
    public function show_returns_403_for_another_user()
    {
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->anotherUser)
            ->get(route('price-alerts.show', $priceAlert))
            ->assertForbidden();
    }
    // endregion

    // region -------------------- 2. Create Lifecycle (Create & Store) --------------------
    // ... (هذا القسم يبقى كما هو بدون تغيير) ...
    /** @test */
    public function an_authenticated_user_can_create_a_price_alert()
    {
        $product = Product::factory()->create();

        $this->actingAs($this->user)
            ->post(route('price-alerts.store'), [
                'product_id' => $product->id,
                'target_price' => 200.50,
                'repeat_alert' => true,
            ])
            ->assertRedirect(route('price-alerts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('price_alerts', [
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'target_price' => 200.50,
        ]);
    }

    /** @test */
    public function creating_a_price_alert_fails_with_invalid_data()
    {
        $this->actingAs($this->user)
            ->post(route('price-alerts.store'), ['product_id' => 999])
            ->assertSessionHasErrors(['product_id', 'target_price']);
    }
    // endregion

    // region -------------------- 3. Update Lifecycle (Edit & Update) --------------------
    // ... (هذا القسم يبقى كما هو بدون تغيير) ...
    /** @test */
    public function an_authenticated_user_can_update_their_price_alert()
    {
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->get(route('price-alerts.edit', $priceAlert))
            ->assertOk()
            ->assertSee($priceAlert->target_price);

        $this->actingAs($this->user)
            ->put(route('price-alerts.update', $priceAlert), [
                'target_price' => 350.75,
                'repeat_alert' => false,
            ])
            ->assertRedirect(route('price-alerts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('price_alerts', [
            'id' => $priceAlert->id,
            'target_price' => 350.75,
            'repeat_alert' => false,
        ]);
    }

    /** @test */
    public function a_user_cannot_update_another_users_price_alert()
    {
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->anotherUser)
            ->put(route('price-alerts.update', $priceAlert), ['target_price' => 400])
            ->assertForbidden();
    }
    // endregion

    // region -------------------- 4. Management Lifecycle (Toggle & Destroy) --------------------
    // ... (هذا القسم يبقى كما هو بدون تغيير) ...
    /** @test */
    public function a_user_can_toggle_their_price_alert_status()
    {
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id, 'is_active' => true]);

        $this->actingAs($this->user)
            ->patch(route('price-alerts.toggle', $priceAlert))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('price_alerts', [
            'id' => $priceAlert->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function a_user_can_delete_their_price_alert()
    {
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->delete(route('price-alerts.destroy', $priceAlert))
            ->assertRedirect(route('price-alerts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('price_alerts', ['id' => $priceAlert->id]);
    }

    /** @test */
    public function a_user_cannot_delete_another_users_price_alert()
    {
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->anotherUser)
            ->delete(route('price-alerts.destroy', $priceAlert))
            ->assertForbidden();
    }
    // endregion

    // region -------------------- 5. Guest Security Tests --------------------

    /**
     * @test
     *
     * @dataProvider guestRoutesProvider
     *
     * @group security
     */
    public function a_guest_is_redirected_to_login_from_all_protected_routes($method, $routeName)
    {
        // هذا الاختبار الشامل يختبر كل المسارات المحمية مرة واحدة
        // نستخدم createQuietly لتجنب تشغيل أي أحداث (events) إذا كانت موجودة
        $priceAlert = PriceAlert::factory()->createQuietly();

        // بناء المسار الديناميكي
        $route = route($routeName, $priceAlert);

        // تنفيذ الطلب (get, post, put, patch, delete) والتأكد من إعادة التوجيه
        $this->{$method}($route)->assertRedirect(route('login'));
    }

    public static function guestRoutesProvider(): array
    {
        // هذه القائمة تغطي كل المسارات التي يجب أن تكون محمية للزوار
        return [
            'Guest cannot view index' => ['get',    'price-alerts.index'],
            'Guest cannot view create' => ['get',    'price-alerts.create'],
            'Guest cannot view show' => ['get',    'price-alerts.show'],
            'Guest cannot view edit' => ['get',    'price-alerts.edit'],
            'Guest cannot store' => ['post',   'price-alerts.store'],
            'Guest cannot update' => ['put',    'price-alerts.update'],
            'Guest cannot toggle' => ['patch',  'price-alerts.toggle'],
            'Guest cannot destroy' => ['delete', 'price-alerts.destroy'],
        ];
    }

    // endregion
}
