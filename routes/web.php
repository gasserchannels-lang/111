<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AIControlPanelController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HealthController;
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
// Health check route (controller for route:cache compatibility)
Route::get('/health', [HealthController::class, 'index']);

Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function () {
    // Handle login logic here
    return redirect()->intended('/');
})->name('login.post');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', function (Illuminate\Http\Request $request) {
    // Validate the request
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    // Create the user
    $user = \App\Models\User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
    ]);

    return redirect()->route('login')->with('status', 'Registration successful!');
})->name('register.post');

Route::post('/logout', function () {
    // Handle logout logic here
    return redirect()->route('home');
})->name('logout');

Route::get('/password/reset', function () {
    // @phpstan-ignore-next-line
    return view('auth.passwords.email');
})->name('password.request');

Route::post('/password/email', function () {
    // Handle password reset email logic here
    return back()->with('status', 'Password reset link sent!');
})->name('password.email');

Route::get('/password/reset/{token}', function ($token) {
    // @phpstan-ignore-next-line
    return view('auth.passwords.reset', ['token' => $token]);
})->name('password.reset');

Route::post('/password/reset', function () {
    // Handle password reset logic here
    return redirect()->route('login')->with('status', 'Password reset successfully!');
})->name('password.update');

// Email verification routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function () {
    // Handle email verification logic here
    return redirect()->route('home')->with('status', 'Email verified successfully!');
})->middleware(['auth', 'signed'])->name('verification.verify');

// Password confirmation route
Route::get('/user/confirm-password', function () {
    return view('auth.confirm-password');
})->middleware('auth')->name('password.confirm');

// Additional password reset routes for testing
Route::post('/forgot-password', function () {
    return back()->with('status', 'Password reset link sent!');
})->name('forgot-password');

Route::put('/user/password', function () {
    return back()->with('status', 'Password updated successfully!');
})->name('user.password.update');

// المنتجات والفئات
Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('products/{slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');

// تغيير اللغة والعملة
Route::get('language/{langCode}', [LocaleController::class, 'changeLanguage'])->name('change.language');
Route::get('currency/{currencyCode}', [LocaleController::class, 'changeCurrency'])->name('change.currency');

// Contact page
Route::get('contact', function () {
    return view('contact');
})->name('contact');

// Locale switching route
Route::post('locale/language', [LocaleController::class, 'switchLanguage'])->name('locale.language');

// --- المسارات المحمية التي تتطلب تسجيل الدخول ---

Route::middleware('auth')->group(function (): void {
    // Price Alert Routes (من الكود الخاص بك، وهو مثالي)
    Route::patch('price-alerts/{priceAlert}/toggle', [PriceAlertController::class, 'toggle'])->name('price-alerts.toggle');
    Route::resource('price-alerts', PriceAlertController::class)->parameters([
        'price-alerts' => 'priceAlert',
    ]);

    // Wishlist Routes
    Route::post('wishlist/add', [WishlistController::class, 'store'])->name('wishlist.add');
    Route::delete('wishlist/remove', [WishlistController::class, 'remove'])->name('wishlist.remove');
    Route::delete('wishlist/clear', [WishlistController::class, 'clear'])->name('wishlist.clear');
    Route::post('wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::resource('wishlist', WishlistController::class)->only(['index', 'destroy']);

    // Review Routes
    Route::resource('reviews', ReviewController::class)->only(['store', 'update', 'destroy']);

    // Cart Routes
    Route::get('cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::post('cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('cart/remove/{itemId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('cart/clear', [CartController::class, 'clear'])->name('cart.clear');
});

// --- Admin Routes (تتطلب صلاحيات إدارية) ---

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('users', [AdminController::class, 'users'])->name('users');
    Route::get('products', [AdminController::class, 'products'])->name('products');
    Route::get('brands', [AdminController::class, 'brands'])->name('brands');
    Route::get('categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::get('stores', [AdminController::class, 'stores'])->name('stores');
    Route::post('users/{user}/toggle-admin', [AdminController::class, 'toggleUserAdmin'])->name('users.toggle-admin');

    // AI Control Panel Routes
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/', [AIControlPanelController::class, 'index'])->name('index');
        Route::post('/analyze-text', [AIControlPanelController::class, 'analyzeText'])->name('analyze-text');
        Route::post('/classify-product', [AIControlPanelController::class, 'classifyProduct'])->name('classify-product');
        Route::post('/recommendations', [AIControlPanelController::class, 'generateRecommendations'])->name('recommendations');
        Route::post('/analyze-image', [AIControlPanelController::class, 'analyzeImage'])->name('analyze-image');
        Route::get('/status', [AIControlPanelController::class, 'getStatus'])->name('status');
    });
});

// --- Brand Routes (تتطلب تسجيل الدخول) ---

Route::middleware('auth')->group(function (): void {
    Route::resource('brands', BrandController::class);
});
