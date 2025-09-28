<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->get('lang')
            ?? Session::get('locale')
            ?? $request->header('Accept-Language')
            ?? config('app.locale');

        // Extract language code from Accept-Language header
        if (str_contains($locale, ',')) {
            $locale = explode(',', $locale)[0];
        }
        if (str_contains($locale, '-')) {
            $locale = explode('-', $locale)[0];
        }

        // Validate supported locales
        $supportedLocales = ['en', 'ar', 'fr', 'es', 'de'];
        if (! in_array($locale, $supportedLocales)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        return $next($request);
    }
}
