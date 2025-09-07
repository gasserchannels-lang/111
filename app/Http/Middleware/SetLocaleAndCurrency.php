<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Language;
use App\Models\Currency;

class SetLocaleAndCurrency
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set language/locale
        $this->setLocale($request);
        
        // Set currency
        $this->setCurrency($request);
        
        return $next($request);
    }

    /**
     * Set application locale based on user preference, session, or default
     */
    private function setLocale(Request $request): void
    {
        $locale = null;

        // 1. Check if user is authenticated and has language preference
        if ($request->user() && $request->user()->language) {
            $locale = $request->user()->language;
        }

        // 2. Check session
        if (!$locale && Session::has('locale')) {
            $locale = Session::get('locale');
        }

        // 3. Check URL parameter
        if (!$locale && $request->has('lang')) {
            $locale = $request->get('lang');
        }

        // 4. Check Accept-Language header
        if (!$locale) {
            $locale = $request->getPreferredLanguage(['en', 'ar', 'es', 'fr', 'de']);
        }

        // 5. Use default from config
        if (!$locale) {
            $locale = config('coprra.default_language', 'en');
        }

        // Validate locale exists in our database
        $validLocale = Language::where('code', $locale)
            ->where('is_active', true)
            ->first();

        if ($validLocale) {
            App::setLocale($validLocale->code);
            Session::put('locale', $validLocale->code);
        } else {
            // Fallback to default
            $defaultLanguage = Language::where('is_default', true)->first();
            if ($defaultLanguage) {
                App::setLocale($defaultLanguage->code);
                Session::put('locale', $defaultLanguage->code);
            }
        }
    }

    /**
     * Set currency based on user preference, session, or default
     */
    private function setCurrency(Request $request): void
    {
        $currency = null;

        // 1. Check if user is authenticated and has currency preference
        if ($request->user() && $request->user()->currency) {
            $currency = $request->user()->currency;
        }

        // 2. Check session
        if (!$currency && Session::has('currency')) {
            $currency = Session::get('currency');
        }

        // 3. Check URL parameter
        if (!$currency && $request->has('currency')) {
            $currency = $request->get('currency');
        }

        // 4. Use default from config
        if (!$currency) {
            $currency = config('coprra.default_currency', 'USD');
        }

        // Validate currency exists in our database
        $validCurrency = Currency::where('code', $currency)
            ->where('is_active', true)
            ->first();

        if ($validCurrency) {
            Session::put('currency', $validCurrency->code);
            config(['coprra.current_currency' => $validCurrency->code]);
        } else {
            // Fallback to default
            $defaultCurrency = Currency::where('is_default', true)->first();
            if ($defaultCurrency) {
                Session::put('currency', $defaultCurrency->code);
                config(['coprra.current_currency' => $defaultCurrency->code]);
            }
        }
    }
}
