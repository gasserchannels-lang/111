<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $latestProducts = Product::query()
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();

        return view('home', [
            'latestProducts' => $latestProducts,
        ]);
    }
}
