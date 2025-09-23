<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->paginate(12);

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    public function show(string $slug): View
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $products = $category
            ->products()
            ->where('is_active', true)
            ->latest()
            ->paginate(12);

        return view('categories.show', [
            'category' => $category,
            'products' => $products,
        ]);
    }
}
