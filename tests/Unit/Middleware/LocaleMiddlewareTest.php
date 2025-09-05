<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Http\Middleware\LocaleMiddleware;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocaleMiddlewareTest extends TestCase
{
    #[Test]
    public function middleware_handles_request_without_error(): void
    {
        // Create mock objects for the middleware dependencies
        $authMock = $this->createMock(Guard::class);
        $sessionMock = $this->createMock(Session::class);
        $appMock = $this->createMock(Application::class);

        // Configure the mocks with expected behavior
        $authMock->method('check')->willReturn(false);
        $sessionMock->method('has')->willReturn(false);
        $appMock->expects($this->once())->method('setLocale');

        $request = Request::create('/test');
        $request->setLaravelSession(app('session.store'));

        // Instantiate the middleware with the mock dependencies
        $middleware = new LocaleMiddleware($authMock, $sessionMock, $appMock);

        $response = $middleware->handle($request, fn ($req) => response('OK'));
        $this->assertEquals('OK', $response->getContent());
    }
}
