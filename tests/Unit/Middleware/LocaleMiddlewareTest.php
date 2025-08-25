<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\LocaleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LocaleMiddlewareTest extends TestCase
{
    /** @test */
    public function middleware_sets_app_locale_from_session()
    {
        Session::put('locale', 'ar');
        $request = Request::create('/');
        $request->setLaravelSession(session());

        $middleware = new LocaleMiddleware();
        
        $middleware->handle($request, function ($req) {
            $this->assertEquals('ar', app()->getLocale());
        });
    }

    /** @test */
    public function middleware_defaults_to_config_locale_if_session_is_not_set()
    {
        $request = Request::create('/');
        $request->setLaravelSession(session());

        $middleware = new LocaleMiddleware();
        
        $middleware->handle($request, function ($req) {
            $this->assertEquals(config('app.locale'), app()->getLocale());
        });
    }
}
