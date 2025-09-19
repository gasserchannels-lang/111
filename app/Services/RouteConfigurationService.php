<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Routing\Registrar as Router;
use Illuminate\Http\Request;

class RouteConfigurationService
{
    public function __construct(private readonly RateLimiter $rateLimiter, private readonly Router $router) {}

    public function configureRateLimiting(): void
    {
        // API rate limiting - more permissive for testing
        $this->rateLimiter->for('api', fn (Request $request) => Limit::perMinute(100)->by($request->user()?->id ?: $request->ip()));

        // Public rate limiting (for unauthenticated users) - more permissive for testing
        $this->rateLimiter->for('public', fn (Request $request) => Limit::perMinute(50)->by($request->ip()));

        // Authenticated rate limiting
        $this->rateLimiter->for('authenticated', fn (Request $request) => Limit::perMinute(200)->by($request->user()?->id ?: $request->ip()));

        // Admin rate limiting
        $this->rateLimiter->for('admin', fn (Request $request) => Limit::perMinute(500)->by($request->user()?->id ?: $request->ip()));

        // Auth rate limiting (for login attempts)
        $this->rateLimiter->for('auth', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        // AI rate limiting
        $this->rateLimiter->for('ai', fn (Request $request) => Limit::perMinute(50)->by($request->user()?->id ?: $request->ip()));
    }

    public function configureRoutes(): void
    {
        $this->router->group(['middleware' => 'api', 'prefix' => 'api'], function (): void {
            require base_path('routes/api.php');
        });

        $this->router->group(['middleware' => 'web'], function (): void {
            require base_path('routes/web.php');
        });
    }
}
