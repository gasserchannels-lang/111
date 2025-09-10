<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubstituteBindings
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();

        if ($route) {
            $parameters = $route->parameters();
            foreach ($parameters as $key => $value) {
                if (is_string($value) && is_numeric($value)) {
                    $route->setParameter($key, (int)$value);
                }
            }
        }

        return $next($request);
    }
}
