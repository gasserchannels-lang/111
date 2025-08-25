<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function home_page_loads_successfully()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function home_page_contains_expected_content()بالتأكيد. لقد قمت بتجميع وتنظيم كل الأكواد التي ناقشناها في رد واحد شامل، مع وضع اسم الملف ومساره قبل كل كتلة كود لتسهيل عملية النسخ واللصق.

هذا هو دليلك الكامل لتنفيذ الترقية الشاملة لجودة مشروعك.

---

### **ملفات الاختبار (Tests)**

#### **`tests/Feature/Http/Controllers/HomeControllerTest.php`**
```php
<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function home_page_loads_successfully()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function home_page_contains_expected_content()
    {
        $response = $this->get('/');
        $response->assertSee('COPRRA'); // يمكنك تعديل هذا النص ليتوافق مع المحتوى الفعلي لصفحتك
    }
}
