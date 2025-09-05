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
        $this->rateLimiter->for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    public function configureRoutes(): void
    {
        $this->router->middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        $this->router->middleware('web')
            ->group(base_path('routes/web.php'));
    }
}
