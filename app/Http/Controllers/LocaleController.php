<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Language;
use App\Models\UserLocaleSetting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function changeLanguage($langCode)
    {
        $language = Language::where('code', $langCode)->first();

        if ($language) {
            Session::put('locale_language', $langCode);
            App::setLocale($langCode);

            if (auth()->check()) {
                $user = auth()->user();
                $userLocale = UserLocaleSetting::firstOrNew(['user_id' => $user->id]);
                $userLocale->language_id = $language->id;
                // إذا لم يكن هناك عملة محددة، استخدم العملة الافتراضية للغة الجديدة
                if (! $userLocale->currency_id) {
                    $defaultCurrency = $language->currencies()->wherePivot('is_default', true)->first();
                    if ($defaultCurrency) {
                        $userLocale->currency_id = $defaultCurrency->id;
                        Session::put('locale_currency', $defaultCurrency->code);
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
            Session::put('locale_currency', $currencyCode);

            if (auth()->check()) {
                $user = auth()->user();
                $userLocale = UserLocaleSetting::firstOrNew(['user_id' => $user->id]);
                $userLocale->currency_id = $currency->id;
                // إذا لم تكن هناك لغة محددة، استخدم اللغة الافتراضية
                if (! $userLocale->language_id) {
                    $defaultLanguage = Language::where('is_default', true)->first();
                    if ($defaultLanguage) {
                        $userLocale->language_id = $defaultLanguage->id;
                        Session::put('locale_language', $defaultLanguage->code);
                        App::setLocale($defaultLanguage->code);
                    }
                }
                $userLocale->save();
            }
        }

        return back();
    }
}
