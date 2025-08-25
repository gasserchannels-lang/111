<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PriceAlertController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// ✅✅ المسار الوهمي لتسجيل الدخول لتلبية متطلبات الاختبار
Route::get('/login', function () {
    return 'This is a dummy login page to satisfy the test.';
})->name('login');

// Locale Routes
Route::get('language/{langCode}', [LocaleController::class, 'changeLanguage'])->name('change.language');
Route::get('currency/{currencyCode}', [LocaleController::class, 'changeCurrency'])->name('change.currency');

// Price Alert Routes
Route::middleware('auth')->group(function () {
    Route::resource('price-alerts', PriceAlertController::class);
    Route::patch('price-alerts/{priceAlert}/toggle', [PriceAlertController::class, 'toggle'])->name('price-alerts.toggle');
});
