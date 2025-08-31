<?php

declare(strict_types=1);

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
        $languageCode = config('app.fallback_locale', 'en'); // قيمة افتراضية قوية

        try {
            // 1. تحقق من المستخدم المسجل (مع التحقق من وجود العلاقة)
            if (Auth::check() && optional(Auth::user()->localeSetting)->language) {
                $languageCode = Auth::user()->localeSetting->language->code;
            }
            // 2. تحقق من الجلسة
            elseif (Session::has('locale_language')) {
                $languageCode = Session::get('locale_language');
            }
            // 3. تحقق من لغة المتصفح (مع التحقق من وجود اللغة في قاعدة البيانات)
            else {
                $browserLangCode = substr($request->server('HTTP_ACCEPT_LANGUAGE', ''), 0, 2);
                $dbLangCode = Language::where('code', $browserLangCode)->value('code');
                if ($dbLangCode) {
                    $languageCode = $dbLangCode;
                } else {
                    // إذا لم تكن لغة المتصفح مدعومة، استخدم اللغة الافتراضية من قاعدة البيانات
                    $defaultDbLang = Language::where('is_default', true)->value('code');
                    if ($defaultDbLang) {
                        $languageCode = $defaultDbLang;
                    }
                }
            }
        } catch (\Exception $e) {
            // في حالة حدوث أي خطأ غير متوقع، اعتمد على القيمة الافتراضية الآمنة
            // هذا يمنع ظهور خطأ 500
        }

        App::setLocale($languageCode);

        return $next($request);
    }
}
