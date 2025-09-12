<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class E2ETest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test complete user journey from homepage to product purchase.
     */
    public function test_complete_user_journey()
    {
        $this->browse(function (Browser $browser) {
            // 1. Visit homepage
            $browser->visit('/')
                ->assertSee('كوبرا')
                ->assertSee('مقارنة الأسعار');

            // 2. Search for a product
            $browser->type('search', 'iPhone')
                ->press('البحث')
                ->waitForText('نتائج البحث')
                ->assertSee('iPhone');

            // 3. Click on a product
            $browser->clickLink('عرض التفاصيل')
                ->assertSee('السعر')
                ->assertSee('المتاجر');

            // 4. Add to wishlist
            $browser->press('إضافة للمفضلة')
                ->assertSee('تمت الإضافة للمفضلة');

            // 5. Navigate to wishlist
            $browser->visit('/wishlist')
                ->assertSee('قائمة المفضلة')
                ->assertSee('iPhone');

            // 6. Set price alert
            $browser->press('تنبيه السعر')
                ->type('alert_price', '1000')
                ->press('حفظ التنبيه')
                ->assertSee('تم إنشاء التنبيه');
        });
    }

    /**
     * Test responsive design on different screen sizes.
     */
    public function test_responsive_design()
    {
        $this->browse(function (Browser $browser) {
            // Desktop view
            $browser->resize(1920, 1080)
                ->visit('/')
                ->assertSee('كوبرا');

            // Tablet view
            $browser->resize(768, 1024)
                ->visit('/')
                ->assertSee('كوبرا');

            // Mobile view
            $browser->resize(375, 667)
                ->visit('/')
                ->assertSee('كوبرا');
        });
    }

    /**
     * Test form submissions and validations.
     */
    public function test_form_validations()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/contact')
                ->press('إرسال')
                ->assertSee('يرجى ملء جميع الحقول المطلوبة')
                ->type('name', 'أحمد محمد')
                ->type('email', 'invalid-email')
                ->press('إرسال')
                ->assertSee('يرجى إدخال بريد إلكتروني صحيح')
                ->type('email', 'ahmed@example.com')
                ->type('message', 'رسالة تجريبية')
                ->press('إرسال')
                ->assertSee('تم إرسال الرسالة بنجاح');
        });
    }

    /**
     * Test navigation and menu functionality.
     */
    public function test_navigation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->clickLink('المنتجات')
                ->assertPathIs('/products')
                ->clickLink('الفئات')
                ->assertPathIs('/categories')
                ->clickLink('المتاجر')
                ->assertPathIs('/stores')
                ->clickLink('اتصل بنا')
                ->assertPathIs('/contact');
        });
    }

    /**
     * Test language switching.
     */
    public function test_language_switching()
    {
        $this->browse(function (Browser $browser) {
            // Switch to English
            $browser->visit('/')
                ->clickLink('English')
                ->assertSee('Price Comparison')
                ->assertSee('Search');

            // Switch back to Arabic
            $browser->clickLink('العربية')
                ->assertSee('مقارنة الأسعار')
                ->assertSee('البحث');
        });
    }

    /**
     * Test user authentication flow.
     */
    public function test_user_authentication()
    {
        $this->browse(function (Browser $browser) {
            // Test registration
            $browser->visit('/register')
                ->type('name', 'أحمد محمد')
                ->type('email', 'ahmed@example.com')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->press('إنشاء حساب')
                ->assertSee('تم إنشاء الحساب بنجاح');

            // Test login
            $browser->visit('/login')
                ->type('email', 'ahmed@example.com')
                ->type('password', 'password123')
                ->press('تسجيل الدخول')
                ->assertSee('مرحباً أحمد');
        });
    }
}
