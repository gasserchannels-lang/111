<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    public function __construct(
        private Guard $auth,
        private Session $session,
        private Application $app
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $languageCode = config('app.fallback_locale', 'en');

        try {
            $languageCode = $this->determineLanguage($request);
        } catch (\Exception) {
            // في حالة حدوث أي خطأ غير متوقع، اعتمد على القيمة الافتراضية الآمنة
        }

        $this->app->setLocale($languageCode);

        return $next($request);
    }

    private function determineLanguage(Request $request): string
    {
        // 1. تحقق من المستخدم المسجل
        if ($this->auth->check() && optional($this->auth->user()->localeSetting)->language) {
            return $this->auth->user()->localeSetting->language->code;
        }

        // 2. تحقق من الجلسة
        if ($this->session->has('locale_language')) {
            return $this->session->get('locale_language');
        }

        // 3. تحقق من لغة المتصفح
        return $this->getBrowserLanguage($request);
    }

    private function getBrowserLanguage(Request $request): string
    {
        $browserLangCode = substr($request->server('HTTP_ACCEPT_LANGUAGE', ''), 0, 2);
        $dbLangCode = Language::where('code', $browserLangCode)->value('code');

        if ($dbLangCode) {
            return $dbLangCode;
        }

        // إذا لم تكن لغة المتصفح مدعومة، استخدم اللغة الافتراضية من قاعدة البيانات
        $defaultDbLang = Language::where('is_default', true)->value('code');

        return $defaultDbLang ?: config('app.fallback_locale', 'en');
    }
}
