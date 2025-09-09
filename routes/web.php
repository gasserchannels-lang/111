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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- المسارات العامة التي لا تتطلب تسجيل الدخول ---

// الصفحة الرئيسية
// Health check route
Route::get('/health', function () {
    $status = [
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
        'environment' => config('app.env'),
    ];

    // Check database connection
    try {
        DB::connection()->getPdo();
        $status['database'] = 'connected';
    } catch (Exception $e) {
        $status['database'] = 'disconnected';
        $status['status'] = 'unhealthy';
    }

    // Check cache
    try {
        Cache::put('health_check', 'ok', 60);
        $status['cache'] = Cache::get('health_check') === 'ok' ? 'working' : 'not_working';
    } catch (Exception $e) {
        $status['cache'] = 'not_working';
        $status['status'] = 'unhealthy';
    }

    // Check storage
    $status['storage'] = is_writable(storage_path()) ? 'writable' : 'not_writable';

    $httpStatus = $status['status'] === 'healthy' ? 200 : 503;
    
    return response()->json($status, $httpStatus);
});

Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Logout route removed for production

// المنتجات والفئات
Route::resource('products', ProductController::class)->only(['index', 'show']);
Route::resource('categories', CategoryController::class)->only(['index', 'show']);

// تغيير اللغة والعملة
Route::get('language/{langCode}', [LocaleController::class, 'changeLanguage'])->name('change.language');
Route::get('currency/{currencyCode}', [LocaleController::class, 'changeCurrency'])->name('change.currency');

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
    Route::resource('wishlist', WishlistController::class)->only(['index', 'store', 'destroy']);
    Route::post('wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

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
