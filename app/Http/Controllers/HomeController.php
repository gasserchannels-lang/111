<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // جلب المنتجات المميزة
        $featuredProducts = Product::where('is_active', true)
            ->with(['category', 'brand'])
            ->latest()
            ->take(8)
            ->get();

        // جلب الفئات النشطة
        $categories = Category::where('is_active', true)
            ->withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(6)
            ->get();

        // جلب الماركات النشطة
        $brands = Brand::where('is_active', true)
            ->withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(8)
            ->get();

        return view('welcome', compact('featuredProducts', 'categories', 'brands'));
    }
}
