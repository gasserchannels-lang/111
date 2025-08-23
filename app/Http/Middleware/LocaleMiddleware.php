<?php

namespace App\Http\Middleware;

// ... other use statements ...
use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    // ... handle method ...

    /**
     * Determine the language code from various sources.
     */
    private function determineLanguageCode(Request $request): string
    {
        $langCode = null;

        // 1. User preferences (if logged in)
        if (Auth::check() && ($setting = Auth::user()->localeSetting) && ($language = $setting->language)) {
            $langCode = $language->code;
        }
        // 2. Session preferences
        elseif (Session::has('locale_language')) {
            $langCode = Session::get('locale_language');
        }
        // 3. Browser's Accept-Language header
        else {
            $browserLangCode = substr($request->server('HTTP_ACCEPT_LANGUAGE', ''), 0, 2);
            if ($language = Language::where('code', $browserLangCode)->first()) {
                $langCode = $language->code;
            }
        }

        // 4. Default language or fallback
        return $langCode ?? Language::where('is_default', true)->first()?->code ?? 'en';
    }

    // ... other methods ...
}
