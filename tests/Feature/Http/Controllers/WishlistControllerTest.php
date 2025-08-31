<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function wishlist_route_requires_authentication(): void
    {
        $response = $this->get('/wishlist');
        $response->assertRedirect('/login');
    }
}
