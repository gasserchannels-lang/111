<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\View\View;

class ProductController extends Controller
{
    private readonly ProductService $productService;

    public function __construct(?ProductService $productService = null)
    {
        $this->productService = $productService ?? app(ProductService::class);
    }

    public function index(): View
    {
        $products = $this->productService->getPaginatedProducts();

        return view('products.index', [
            'products' => $products,
        ]);
    }

    public function show(string $slug): View
    {
        $product = $this->productService->getBySlug($slug);

        if (! $product instanceof \App\Models\Product) {
            abort(404);
        }

        $relatedProducts = $this->productService->getRelatedProducts($product);

        return view('product-show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
