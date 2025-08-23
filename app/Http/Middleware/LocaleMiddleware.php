<?php

namespace App\Http\Middleware;

use App\Models\Currency;
use App\Models\Language;
use App\Models\UserLocaleSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $languageCode = $this->determineLanguageCode($request);
        $currencyCode = $this->determineCurrencyCode();

        // Set locale and currency for the current request
        Session::put('locale_language', $languageCode);
        Session::put('locale_currency', $currencyCode);
        App::setLocale($languageCode);

        // Store/Update user settings if logged in
        if (Auth::check()) {
            $this->updateUserLocaleSettings($languageCode, $currencyCode);
        }

        return $next($request);
    }

    /**
     * Determine the language code from various sources.
     */
    private function determineLanguageCode(Request $request): string
    {
        // 1. User preferences (if logged in)
        if (Auth::check() && ($setting = Auth::user()->localeSetting) && ($language = $setting->language)) {
            return $language->code;
        }

        // 2. Session preferences
        if (Session::has('locale_language')) {
            return Session::get('locale_language');
        }

        // 3. Browser's Accept-Language header
        $browserLangCode = substr($request->server('HTTP_ACCEPT_LANGUAGE', ''), 0, 2);
        if ($language = Language::where('code', $browserLangCode)->first()) {
            return $language->code;
        }

        // 4. Default language
        $defaultLanguage = Language::where('is_default', true)->first();

        return $defaultLanguage ? $defaultLanguage->code : 'en'; // Fallback
    }

    /**
     * Determine the currency code from various sources.
     */
    private function determineCurrencyCode(): string
    {
        // 1. User preferences (if logged in)
        if (Auth::check() && ($setting = Auth::user()->localeSetting) && ($currency = $setting->currency)) {
            return $currency->code;
        }

        // 2. Session preferences
        if (Session::has('locale_currency')) {
            return Session::get('locale_currency');
        }

        // 3. Default currency
        $defaultCurrency = Currency::where('is_default', true)->first();

        return $defaultCurrency ? $defaultCurrency->code : 'USD'; // Fallback
    }

    /**
     * Update the user's locale settings in the database.
     */
    private function updateUserLocaleSettings(string $languageCode, string $currencyCode): void
    {
        $user = Auth::user();
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
}
