<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    public function __construct(
        private readonly Guard $auth,
        private readonly Session $session
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $languageCode = config('app.fallback_locale', 'en');

        try {
            $languageCode = $this->determineLanguage($request);
        } catch (\Exception) {
            // في حالة حدوث أي خطأ غير متوقع، اعتمد على القيمة الافتراضية الآمنة
        }

        if (is_string($languageCode)) {
            app()->setLocale($languageCode);
        }

        return $next($request);
    }

    private function determineLanguage(Request $request): string
    {
        // 1. تحقق من المستخدم المسجل
        if ($this->auth->check()) {
            $user = $this->auth->user();
            if ($user && $user->localeSetting && $user->localeSetting->language) {
                $language = $user->localeSetting->language;

                return is_string($language->code) ? $language->code : 'en';
            }
        }

        // 2. تحقق من الجلسة
        if ($this->session->has('locale_language')) {
            $sessionLang = $this->session->get('locale_language');
            if (is_string($sessionLang)) {
                return $sessionLang;
            }
        }

        // 3. تحقق من لغة المتصفح
        return $this->getBrowserLanguage($request);
    }

    private function getBrowserLanguage(Request $request): string
    {
        $acceptLanguage = $request->server('HTTP_ACCEPT_LANGUAGE', '');
        $browserLangCode = is_string($acceptLanguage) ? substr($acceptLanguage, 0, 2) : '';
        $dbLangCode = Language::where('code', $browserLangCode)->value('code');

        if (is_string($dbLangCode)) {
            return $dbLangCode;
        }

        // إذا لم تكن لغة المتصفح مدعومة، استخدم اللغة الافتراضية من قاعدة البيانات
        $defaultDbLang = Language::where('is_default', true)->value('code');

        return is_string($defaultDbLang) ? $defaultDbLang : (is_string(config('app.fallback_locale')) ? config('app.fallback_locale') : 'en');
    }
}
