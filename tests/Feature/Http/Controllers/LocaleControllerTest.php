<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_switch_language()
    {
        $response = $this->get(route('change.language', 'ar'));
        $response->assertRedirect();
        $response->assertSessionHas('locale', 'ar');
    }

    /** @test */
    public function can_switch_currency()
    {
        $response = $this->get(route('change.currency', 'EGP'));
        $response->assertRedirect();
        $response->assertSessionHas('currency', 'EGP');
    }
}
