<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PriceAlertController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// المسار الوهمي لتسجيل الدخول
Route::get('/login', function () {
    return 'This is a dummy login page to satisfy the test.';
})->name('login');

// Locale Routes
Route::get('language/{langCode}', [LocaleController::class, 'changeLanguage'])->name('change.language');
Route::get('currency/{currencyCode}', [LocaleController::class, 'changeCurrency'])->name('change.currency');

// Price Alert Routes
Route::middleware('auth')->group(function () {
    // ✅✅✅ التعديل: تعريف المسار المحدد 'toggle' أولاً ✅✅✅
    Route::patch('price-alerts/{price_alert}/toggle', [PriceAlertController::class, 'toggle'])->name('price-alerts.toggle');

    // ✅✅✅ التعديل: استخدام 'price_alert' لتوحيد الأسماء ✅✅✅
    Route::resource('price-alerts', PriceAlertController::class)->parameters([
        'price-alerts' => 'price_alert',
    ]);
});
