<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $languageCode = $this->determineLanguageCode($request);
        App::setLocale($languageCode);

        $response = $next($request);

        $response->headers->set('Content-Language', $languageCode);

        return $response;
    }

    /**
     * Determine the language code from various sources.
     */
    private function determineLanguageCode(Request $request): string
    {
        $langCode = null;

        if (Auth::check() && ($setting = Auth::user()->localeSetting) && ($language = $setting->language)) {
            $langCode = $language->code;
        } elseif (Session::has('locale_language')) {
            $langCode = Session::get('locale_language');
        } else {
            $browserLangCode = substr($request->server('HTTP_ACCEPT_LANGUAGE', ''), 0, 2);
            if ($language = Language::where('code', $browserLangCode)->first()) {
                $langCode = $language->code;
            }
        }

        return $langCode ?? Language::where('is_default', true)->first()?->code ?? 'en';
    }
}
