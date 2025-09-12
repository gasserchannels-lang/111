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
        // Start a session to get a valid CSRF token
        $this->startSession();

        $response = $this->post('/locale/language', [
            'language' => 'ar',
            '_token' => csrf_token(),
        ]);

        $this->assertTrue($response->isRedirection() || $response->isSuccessful());
    }
}
