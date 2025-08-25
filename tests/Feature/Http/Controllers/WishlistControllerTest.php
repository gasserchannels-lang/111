<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function wishlist_route_requires_authentication()
    {
        $response = $this->get('/wishlist');
        $response->assertRedirect('/login');
    }
}
