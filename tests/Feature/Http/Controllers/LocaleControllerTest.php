<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocaleControllerTest extends TestCase
{
    #[Test]
    public function can_switch_language(): void
    {
        $response = $this->post('/locale/language', ['language' => 'ar']);
        $this->assertTrue($response->isRedirection() || $response->isSuccessful());
    }
}
