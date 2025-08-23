<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\UserLocaleSetting;
use App\Models\Language;
use App\Models\Currency;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $languageCode = null;
        $currencyCode = null;

        // 1. User preferences (if logged in)
        if (auth()->check()) {
            $userLocale = auth()->user()->localeSetting;
            if ($userLocale) {
                $languageCode = $userLocale->language ? $userLocale->language->code : null;
                $currencyCode = $userLocale->currency ? $userLocale->currency->code : null;
            }
        }

        // 2. Session preferences (for guests or if user preferences are not set)
        if (!$languageCode && Session::has('locale_language')) {
            $languageCode = Session::get('locale_language');
        }
        if (!$currencyCode && Session::has('locale_currency')) {
            $currencyCode = Session::get('locale_currency');
        }

        // 3. Detect from browser (Accept-Language header) - simplified for example
        if (!$languageCode) {
            $browserLang = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
            $language = Language::where('code', $browserLang)->first();
            if ($language) {
                $languageCode = $language->code;
            }
        }

        // 4. Default if nothing found
        if (!$languageCode) {
            $defaultLanguage = Language::where('is_default', true)->first();
            $languageCode = $defaultLanguage ? $defaultLanguage->code : 'en'; // Fallback to 'en'
        }
        if (!$currencyCode) {
            $defaultCurrency = Currency::where('is_default', true)->first();
            $currencyCode = $defaultCurrency ? $defaultCurrency->code : 'USD'; // Fallback to 'USD'
        }

        // Set locale and currency in session for consistency
        Session::put('locale_language', $languageCode);
        Session::put('locale_currency', $currencyCode);

        // Set Laravel App locale
        App::setLocale($languageCode);

        // Store/Update user locale settings if logged in
        if (auth()->check()) {
            $user = auth()->user();
            $language = Language::where('code', $languageCode)->first();
            $currency = Currency::where('code', $currencyCode)->first();

            if ($language && $currency) {
                UserLocaleSetting::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'language_id' => $language->id,
                        'currency_id' => $currency->id,
                    ]
                );
            }
        }

        return $next($request);
    }
}


