<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    #[Test]
    public function cart_requires_authentication(): void
    {
        $response = $this->get('/cart');
        $response->assertRedirect('/login');
    }
}
