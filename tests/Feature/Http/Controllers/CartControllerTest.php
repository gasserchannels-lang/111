<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cart_requires_authentication()
    {
        $response = $this->get('/cart');
        $response->assertRedirect('/login');
    }
}
