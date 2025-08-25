<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\PriceAlert;
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

    public function test_index_displays_only_user_price_alerts()
    {
        // المصنع سيقوم تلقائيًا بإنشاء منتجات لهذه التنبيهات
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

    // region Show
    public function test_show_displays_correct_price_alert()
    {
        // الإعداد: المصنع سينشئ تلقائيًا منتجًا مرتبطًا بهذا التنبيه
        $priceAlert = PriceAlert::factory()->create(['user_id' => $this->user->id]);

        // التنفيذ والتأكيد
        $this->actingAs($this->user) // سجل الدخول كمستخدم للتنبيه
            ->get(route('price-alerts.show', $priceAlert))
            ->assertStatus(200) // تحقق من أن الصفحة تعمل بنجاح
            ->assertViewIs('price-alerts.show') // تحقق من عرض الواجهة الصحيحة
            ->assertSee($priceAlert->product->name); // تحقق من ظهور اسم المنتج في الصفحة
    }
    // endregion

    // ... (باقي الاختبارات تبقى كما هي) ...
}
