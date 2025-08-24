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
    public function handle(Request $request, Closure $next): Response
    {
        $languageCode = $this->determineLanguageCode($request);
        App::setLocale($languageCode);

        $response = $next($request);
        $response->headers->set('Content-Language', $languageCode);

        return $response;
    }

    private function determineLanguageCode(Request $request): string
    {
        // 1. User preference (if logged in and setting exists)
        if (Auth::check() && optional(Auth::user()->localeSetting)->language) {
            return Auth::user()->localeSetting->language->code;
        }

        // 2. Session preference
        if (Session::has('locale_language')) {
            return Session::get('locale_language');
        }

        // 3. Browser's Accept-Language header
        $browserLangCode = substr($request->server('HTTP_ACCEPT_LANGUAGE', ''), 0, 2);
        if ($language = Language::where('code', $browserLangCode)->first()) {
            return $language->code;
        }

        // 4. Fallback to default language in DB or 'en'
        return Language::where('is_default', true)->value('code') ?? 'en';
    }
}
