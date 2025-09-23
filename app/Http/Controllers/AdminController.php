<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display the admin dashboard.
     */
    public function dashboard(): View
    {
        $stats = [
            'users' => User::count(),
            'products' => Product::count(),
            'brands' => Brand::count(),
            'categories' => Category::count(),
            'stores' => Store::count(),
        ];

        $recentUsers = User::latest()->take(5)->get();
        $recentProducts = Product::with(['brand', 'category'])->latest()->take(5)->get();

        return view('admin.dashboard', ['stats' => $stats, 'recentUsers' => $recentUsers, 'recentProducts' => $recentProducts]);
    }

    /**
     * Display the users management page.
     */
    public function users(): View
    {
        $users = User::with('localeSetting')->paginate(20);

        return view('admin.users', ['users' => $users]);
    }

    /**
     * Display the products management page.
     */
    public function products(): View
    {
        $products = Product::with(['brand', 'category', 'priceOffers'])
            ->paginate(20);

        return view('admin.products', ['products' => $products]);
    }

    /**
     * Display the brands management page.
     */
    public function brands(): View
    {
        $brands = Brand::with('products')->paginate(20);

        return view('admin.brands', ['brands' => $brands]);
    }

    /**
     * Display the categories management page.
     */
    public function categories(): View
    {
        $categories = Category::with('products')->paginate(20);

        return view('admin.categories', ['categories' => $categories]);
    }

    /**
     * Display the stores management page.
     */
    public function stores(): View
    {
        $stores = Store::with('currency')->paginate(20);

        return view('admin.stores', ['stores' => $stores]);
    }

    /**
     * Toggle user admin status.
     */
    public function toggleUserAdmin(\App\Models\User $user): \Illuminate\Http\RedirectResponse
    {
        $user->update(['is_admin' => ! $user->is_admin]);

        return redirect()->back()
            ->with('success', 'User admin status updated successfully.');
    }
}
