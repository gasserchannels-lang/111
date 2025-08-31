<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Http\Middleware\LocaleMiddleware;
use Illuminate\Http\Request;
use Tests\TestCase;

class LocaleMiddlewareTest extends TestCase
{
    /** @test */
    public function middleware_handles_request_without_error()
    {
        $request = Request::create('/test');
        $request->setLaravelSession(app('session.store'));
        $middleware = new LocaleMiddleware;
        $response = $middleware->handle($request, fn($req) => response('OK'));
        $this->assertEquals('OK', $response->getContent());
    }
}
