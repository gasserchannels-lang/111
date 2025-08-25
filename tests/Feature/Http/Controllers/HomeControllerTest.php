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
        // يمكنك تعديل هذا النص ليتوافق مع المحتوى الفعلي لصفحتك
        $response->assertSee('COPRRA'); 
    }
}
