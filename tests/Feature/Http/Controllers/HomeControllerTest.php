<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function home_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    #[Test]
    public function home_page_contains_expected_content(): void
    {
        $response = $this->get('/');
        // يمكنك تعديل هذا النص ليتوافق مع المحتوى الفعلي لصفحتك
        $response->assertSee('Find the Best Prices');
    }
}
