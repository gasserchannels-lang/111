<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\UserLocaleSetting;
use App\Models\Language;
use App\Models\Currency;

class LocaleController extends Controller
{
    public function changeLanguage(Request $request, $langCode)
    {
        $language = Language::where("code", $langCode)->first();
        if ($language) {
            Session::put("locale_language", $langCode);
            App::setLocale($langCode);

            if (auth()->check()) {
                $user = auth()->user();
                $userLocale = UserLocaleSetting::firstOrNew(["user_id" => $user->id]);
                $userLocale->language_id = $language->id;
                $userLocale->save();
            }
        }
        return redirect()->back();
    }

    public function changeCurrency(Request $request, $currencyCode)
    {
        $currency = Currency::where("code", $currencyCode)->first();
        if ($currency) {
            Session::put("locale_currency", $currencyCode);

            if (auth()->check()) {
                $user = auth()->user();
                $userLocale = UserLocaleSetting::firstOrNew(["user_id" => $user->id]);
                $userLocale->currency_id = $currency->id;
                $userLocale->save();
            }
        }
        return redirect()->back();
    }
}


