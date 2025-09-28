<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RTLMiddleware
{
    private array $rtlLanguages = ['ar', 'he', 'fa', 'ur'];

    public function handle(Request $request, Closure $next)
    {
        $locale = app()->getLocale();

        if (in_array($locale, $this->rtlLanguages)) {
            view()->share('isRTL', true);
            view()->share('textDirection', 'rtl');
        } else {
            view()->share('isRTL', false);
            view()->share('textDirection', 'ltr');
        }

        return $next($request);
    }
}
