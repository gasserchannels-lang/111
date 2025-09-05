<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('categories.index');
    }

    public function show(string $slug): View
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();

        $products = $category
            ->products()
            ->where('is_active', true)
            ->latest()
            ->paginate(12);

        return view('category-show', [
            'category' => $category,
            'products' => $products,
        ]);
    }
}
