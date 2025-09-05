<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Language;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    private Guard $auth;

    private Session $session;

    private Application $app;

    public function __construct(Guard $auth, Session $session, Application $app)
    {
        $this->auth = $auth;
        $this->session = $session;
        $this->app = $app;
    }

    public function switchLanguage(Request $request)
    {
        $request->validate([
            'language' => 'required|string|in:en,ar,fr,es,de', // Add supported locales
        ]);

        $locale = $request->input('language');

        // Set the locale in session
        $this->session->put('locale', $locale);
        $this->app->setLocale($locale);

        return redirect()->back();
    }

    public function changeLanguage($languageCode)
    {
        $language = Language::where('code', $languageCode)->first();

        if ($language) {
            $this->session->put('locale_language', $languageCode);
            $this->app->setLocale($languageCode);

            if ($this->auth->check()) {
                $user = $this->auth->user();
                $userLocale = $user->localeSetting()->firstOrNew();
                $userLocale->language_id = $language->id;
                // إذا لم يكن هناك عملة محددة، استخدم العملة الافتراضية للغة الجديدة
                if (! $userLocale->currency_id) {
                    $defaultCurrency = $language->currencies()->wherePivot('is_default', true)->first();
                    if ($defaultCurrency) {
                        $userLocale->currency_id = $defaultCurrency->id;
                        $this->session->put('locale_currency', $defaultCurrency->code);
                    }
                }
                $userLocale->save();
            }
        }

        return back();
    }

    // تم حذف المتغير غير المستخدم من هنا
    public function changeCurrency($currencyCode)
    {
        $currency = Currency::where('code', $currencyCode)->first();

        if ($currency) {
            $this->session->put('locale_currency', $currencyCode);

            if ($this->auth->check()) {
                $user = $this->auth->user();
                $userLocale = $user->localeSetting()->firstOrNew();
                $userLocale->currency_id = $currency->id;
                // إذا لم تكن هناك لغة محددة، استخدم اللغة الافتراضية
                if (! $userLocale->language_id) {
                    $defaultLanguage = Language::where('is_default', true)->first();
                    if ($defaultLanguage) {
                        $userLocale->language_id = $defaultLanguage->id;
                        $this->session->put('locale_language', $defaultLanguage->code);
                        $this->app->setLocale($defaultLanguage->code);
                    }
                }
                $userLocale->save();
            }
        }

        return back();
    }
}
