<?php
namespace App\Http\Middleware;
use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
class LocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $languageCode = $this->determineLanguageCode($request);
            App::setLocale($languageCode);
        } catch (\Exception $e) {
            Log::error('LocaleMiddleware failed: ' . $e->getMessage());
            App::setLocale(config('app.fallback_locale', 'en'));
        }
        return $next($request);
    }
    private function determineLanguageCode(Request $request): string
    {
        if (Auth::check() && optional(Auth::user()->localeSetting)->language) {
            return Auth::user()->localeSetting->language->code;
        }
        if (Session::has('locale_language')) {
            return Session::get('locale_language');
        }
        $browserLangCode = substr($request->server('HTTP_ACCEPT_LANGUAGE', ''), 0, 2);
        if ($language = Language::where('code', $browserLangCode)->first()) {
            return $language->code;
        }
        return Language::where('is_default', true)->value('code') ?? config('app.fallback_locale', 'en');
    }
}
