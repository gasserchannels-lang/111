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
        // Check if search parameters are present
        $query = request()->get('search', '');
        $filters = request()->only(['category', 'brand', 'sort', 'order']);

        if (! empty($query) || ! empty(array_filter($filters))) {
            // Use search method if parameters are present
            return $this->search();
        }

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

        return view('products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function search(): View
    {
        $query = request()->get('search', '');
        $filters = request()->only(['category', 'brand', 'sort', 'order']);

        $queryString = is_string($query) ? $query : '';

        // Convert sort and order parameters to sort_by format expected by repository
        if (isset($filters['sort']) && isset($filters['order'])) {
            $sortBy = $filters['sort'].'_'.$filters['order'];
            $filters['sort_by'] = $sortBy;
            unset($filters['sort'], $filters['order']);
        }

        // Convert category and brand to category_id and brand_id
        if (isset($filters['category'])) {
            $filters['category_id'] = $filters['category'];
            unset($filters['category']);
        }
        if (isset($filters['brand'])) {
            $filters['brand_id'] = $filters['brand'];
            unset($filters['brand']);
        }

        $products = $this->productService->searchProducts($queryString, $filters);

        return view('products.index', [
            'products' => $products,
        ]);
    }
}
