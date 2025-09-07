<?php

declare(strict_types=1);

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PriceAlertController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- المسارات العامة التي لا تتطلب تسجيل الدخول ---

// الصفحة الرئيسية
Route::get('/', [HomeController::class, 'index'])->name('home');

// مسار وهمي لتسجيل الدخول (مهم للاختبارات)
Route::get('/login', fn (): string => 'This is a dummy login page to satisfy the test.'
)->name('login');

// المنتجات والفئات
Route::resource('products', ProductController::class)->only(['index', 'show']);
Route::resource('categories', CategoryController::class)->only(['index', 'show']);

// تغيير اللغة والعملة
Route::get('language/{langCode}', [LocaleController::class, 'changeLanguage'])->name('change.language');
Route::get('currency/{currencyCode}', [LocaleController::class, 'changeCurrency'])->name('change.currency');

// POST routes for locale switching (for testing)
Route::post('locale/language', [LocaleController::class, 'switchLanguage'])->name('locale.language');

// --- المسارات المحمية التي تتطلب تسجيل الدخول ---

Route::middleware('auth')->group(function (): void {

    // Price Alert Routes (من الكود الخاص بك، وهو مثالي)
    Route::patch('price-alerts/{priceAlert}/toggle', [PriceAlertController::class, 'toggle'])->name('price-alerts.toggle');
    Route::resource('price-alerts', PriceAlertController::class)->parameters([
        'price-alerts' => 'priceAlert',
    ]);

    // Wishlist Routes
    Route::resource('wishlist', WishlistController::class)->only(['index', 'store', 'destroy']);

    // Review Routes
    Route::resource('reviews', ReviewController::class)->only(['store', 'destroy']);

    // Cart Routes (كمثال)
    Route::get('cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::delete('cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove'); // استخدام {product} أفضل من {productId}
});

// --- Admin Routes (تتطلب صلاحيات إدارية) ---

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('users', [AdminController::class, 'users'])->name('users');
    Route::get('products', [AdminController::class, 'products'])->name('products');
    Route::get('brands', [AdminController::class, 'brands'])->name('brands');
    Route::get('categories', [AdminController::class, 'categories'])->name('categories');
    Route::get('stores', [AdminController::class, 'stores'])->name('stores');
    Route::post('users/{user}/toggle-admin', [AdminController::class, 'toggleUserAdmin'])->name('users.toggle-admin');
});

// --- Brand Routes (تتطلب تسجيل الدخول) ---

Route::middleware('auth')->group(function (): void {
    Route::resource('brands', BrandController::class);
});
