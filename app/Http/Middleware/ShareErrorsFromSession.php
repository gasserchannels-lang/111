<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShareErrorsFromSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->hasSession()) {
            $errors = $request->session()->get('errors');
            if ($errors) {
                view()->share('errors', $errors);
            }
        }

        return $response;
    }
}
