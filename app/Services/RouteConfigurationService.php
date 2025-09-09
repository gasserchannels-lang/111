<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Routing\Registrar as Router;
use Illuminate\Http\Request;

class RouteConfigurationService
{
    private RateLimiter $rateLimiter;

    private Router $router;

    public function __construct(RateLimiter $rateLimiter, Router $router)
    {
        $this->rateLimiter = $rateLimiter;
        $this->router = $router;
    }

    public function configureRateLimiting(): void
    {
        // API rate limiting
        $this->rateLimiter->for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Public rate limiting (for unauthenticated users)
        $this->rateLimiter->for('public', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Authenticated rate limiting
        $this->rateLimiter->for('authenticated', function (Request $request) {
            return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });

        // Admin rate limiting
        $this->rateLimiter->for('admin', function (Request $request) {
            return Limit::perMinute(200)->by($request->user()?->id ?: $request->ip());
        });

        // Auth rate limiting (for login attempts)
        $this->rateLimiter->for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }

    public function configureRoutes(): void
    {
        $this->router->group(['middleware' => 'api', 'prefix' => 'api'], function () {
            require base_path('routes/api.php');
        });

        $this->router->group(['middleware' => 'web'], function () {
            require base_path('routes/web.php');
        });
    }
}
