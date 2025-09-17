<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceAlert;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

/**
 * اختبارات تحكم تنبيهات الأسعار
 *
 * هذا الكلاس يختبر وظائف تحكم تنبيهات الأسعار
 * ويحذر من مشاكل الأمان والتحقق من البيانات
 *
 * ⚠️ تحذير: يجب التأكد من صحة البيانات والأمان في تنبيهات الأسعار
 */

class PriceAlertControllerTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private User $anotherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    }

    private function createProduct(): Product
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        return Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
    }

    // region -------------------- 1. View Lifecycle (Index & Show) --------------------

    #[Test]
    #[CoversNothing]
    public function index_displays_only_user_price_alerts(): void
    {
        // ⚠️ تحذير: يجب عرض تنبيهات الأسعار للمستخدم الحالي فقط
        $product = $this->createProduct();

        PriceAlert::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id, 'target_price' => 100.00, 'repeat_alert' => true, 'is_active' => true]);
        PriceAlert::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id, 'target_price' => 200.00, 'repeat_alert' => false, 'is_active' => true]);
        PriceAlert::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id, 'target_price' => 300.00, 'repeat_alert' => true, 'is_active' => true]);
        PriceAlert::factory()->create(['user_id' => $this->anotherUser->id, 'product_id' => $product->id, 'target_price' => 150.00, 'repeat_alert' => true, 'is_active' => true]);
        PriceAlert::factory()->create(['user_id' => $this->anotherUser->id, 'product_id' => $product->id, 'target_price' => 250.00, 'repeat_alert' => false, 'is_active' => true]);

        $this->actingAs($this->user)
            ->get(route('price-alerts.index'))
            ->assertOk()
            ->assertViewIs('price-alerts.index')
            ->assertViewHas('priceAlerts', fn($priceAlerts): bool => $priceAlerts->count() === 3);
    }

    #[Test]
    #[CoversNothing]
    public function index_displays_empty_list_when_no_alerts_exist(): void
    {
        $this->actingAs($this->user)
            ->get(route('price-alerts.index'))
            ->assertOk()
            ->assertSee('You have no active price alerts');
    }

    #[Test]
    #[CoversNothing]
    public function show_displays_correct_price_alert_for_owner(): void
    {
        $product = $this->createProduct();
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id, 'target_price' => 100.00, 'repeat_alert' => true, 'is_active' => true]);

        $this->actingAs($this->user)
            ->get(route('price-alerts.show', $priceAlert))
            ->assertStatus(200)
            ->assertViewIs('price-alerts.show')
            ->assertSee($priceAlert->product->name);
    }

    #[Test]
    #[CoversNothing]
    public function show_returns_403_for_another_user(): void
    {
        $product = $this->createProduct();
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id, 'target_price' => 100.00, 'repeat_alert' => true, 'is_active' => true]);

        $this->actingAs($this->anotherUser)
            ->get(route('price-alerts.show', $priceAlert))
            ->assertForbidden();
    }

    #[Test]
    #[CoversNothing]
    public function show_returns_404_for_non_existing_alert(): void
    {
        $this->actingAs($this->user)
            ->get(route('price-alerts.show', 999))
            ->assertNotFound();
    }

    // endregion

    // region -------------------- 2. Create Lifecycle (Create & Store) --------------------

    #[Test]
    #[CoversNothing]
    public function an_authenticated_user_can_create_a_price_alert(): void
    {
        // ⚠️ تحذير: يجب التحقق من صحة بيانات تنبيه السعر قبل الحفظ
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);

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

    #[Test]
    #[CoversNothing]
    public function creating_a_price_alert_fails_with_invalid_data(): void
    {
        // ⚠️ تحذير: يجب رفض البيانات غير الصحيحة في تنبيهات الأسعار
        $this->actingAs($this->user)
            ->post(route('price-alerts.store'), ['product_id' => 999])
            ->assertSessionHasErrors(['product_id', 'target_price']);
    }

    #[Test]
    #[CoversNothing]
    public function it_fails_to_store_a_price_alert_with_non_numeric_target_price(): void
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('price-alerts.store'), [
                'product_id' => $product->id,
                'target_price' => 'not-a-number',
            ])
            ->assertSessionHasErrors(['target_price']);
    }

    #[Test]
    #[CoversNothing]
    public function it_fails_to_store_a_price_alert_with_non_existing_product_id(): void
    {
        $this->actingAs($this->user)
            ->post(route('price-alerts.store'), [
                'product_id' => 9999,
                'target_price' => 150,
            ])
            ->assertSessionHasErrors(['product_id']);
    }

    // endregion

    // region -------------------- 3. Update Lifecycle (Edit & Update) --------------------

    #[Test]
    #[CoversNothing]
    public function an_authenticated_user_can_update_their_price_alert(): void
    {
        $product = $this->createProduct();
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id, 'target_price' => 100.00, 'repeat_alert' => true, 'is_active' => true]);

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

    #[Test]
    #[CoversNothing]
    public function a_user_cannot_update_another_users_price_alert(): void
    {
        $product = $this->createProduct();
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id, 'target_price' => 100.00, 'repeat_alert' => true, 'is_active' => true]);

        $this->actingAs($this->anotherUser)
            ->put(route('price-alerts.update', $priceAlert), ['target_price' => 400])
            ->assertForbidden();
    }

    #[Test]
    #[CoversNothing]
    public function update_returns_404_for_non_existing_alert(): void
    {
        $this->actingAs($this->user)
            ->put(route('price-alerts.update', 999), ['target_price' => 400])
            ->assertNotFound();
    }

    #[Test]
    #[CoversNothing]
    public function it_fails_to_update_a_price_alert_with_empty_target_price(): void
    {
        $product = $this->createProduct();
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id, 'target_price' => 100.00, 'repeat_alert' => true, 'is_active' => true]);

        $this->actingAs($this->user)
            ->put(route('price-alerts.update', $priceAlert), ['target_price' => ''])
            ->assertSessionHasErrors(['target_price']);
    }

    // endregion

    // region -------------------- 4. Management Lifecycle (Toggle & Destroy) --------------------

    #[Test]
    #[CoversNothing]
    public function a_user_can_toggle_their_price_alert_status(): void
    {
        $product = $this->createProduct();
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id, 'target_price' => 100.00, 'repeat_alert' => true, 'is_active' => true]);

        $this->startSession();

        $this->actingAs($this->user)
            ->patch(route('price-alerts.toggle', $priceAlert))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('price_alerts', [
            'id' => $priceAlert->id,
            'is_active' => false,
        ]);
    }

    #[Test]
    #[CoversNothing]
    public function it_cannot_toggle_another_users_price_alert(): void
    {
        $product = $this->createProduct();
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->anotherUser->id, 'product_id' => $product->id, 'target_price' => 100.00, 'repeat_alert' => true, 'is_active' => true]);

        $this->startSession();

        $this->actingAs($this->user)
            ->patch(route('price-alerts.toggle', $priceAlert))
            ->assertForbidden();
    }

    #[Test]
    #[CoversNothing]
    public function a_user_can_delete_their_price_alert(): void
    {
        $product = $this->createProduct();
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id, 'target_price' => 100.00, 'repeat_alert' => true, 'is_active' => true]);

        $this->actingAs($this->user)
            ->delete(route('price-alerts.destroy', $priceAlert))
            ->assertRedirect(route('price-alerts.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('price_alerts', ['id' => $priceAlert->id]);
    }

    #[Test]
    #[CoversNothing]
    public function a_user_cannot_delete_another_users_price_alert(): void
    {
        $product = $this->createProduct();
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id, 'target_price' => 100.00, 'repeat_alert' => true, 'is_active' => true]);

        $this->actingAs($this->anotherUser)
            ->delete(route('price-alerts.destroy', $priceAlert))
            ->assertForbidden();
    }

    #[Test]
    #[CoversNothing]
    public function destroy_returns_404_for_non_existing_alert(): void
    {
        $this->startSession();

        $this->actingAs($this->user)
            ->delete(route('price-alerts.destroy', 999))
            ->assertNotFound();
    }

    // endregion

    // region -------------------- 5. Guest Security Tests --------------------

    #[Test]
    #[CoversNothing]
    #[DataProvider('guestRoutesProvider')]
    #[Group('security')]
    public function a_guest_is_redirected_to_login_from_all_protected_routes(string $method, string $routeName): void
    {
        // Use a mock ID instead of creating a real record
        $priceAlertId = 1;

        $route = route($routeName, $priceAlertId);

        $this->{$method}($route)->assertRedirect(route('login'));
    }

    public static function guestRoutesProvider(): array
    {
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
