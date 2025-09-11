<?php

declare(strict_types=1);

namespace App\Providers;

use App\View\Composers\AppComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register view composers
        View::composer('*', AppComposer::class);

        // Register specific view composers
        View::composer(['layouts.app', 'layouts.admin'], function ($view) {
            $view->with('user', auth()->user());
        });

        View::composer(['products.*', 'categories.*', 'brands.*'], function ($view) {
            $view->with('breadcrumbs', $this->getBreadcrumbs());
        });
    }

    /**
     * Get breadcrumbs for current page.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('home')],
        ];

        $route = request()->route();

        if ($route) {
            $routeName = $route->getName();

            switch ($routeName) {
                case 'products.show':
                    $product = $route->parameter('product');
                    $breadcrumbs[] = ['name' => 'Products', 'url' => route('products.index')];
                    if ($product && isset($product->name)) {
                        $breadcrumbs[] = ['name' => $product->name, 'url' => null];
                    }
                    break;

                case 'categories.show':
                    $category = $route->parameter('category');
                    $breadcrumbs[] = ['name' => 'Categories', 'url' => route('categories.index')];
                    if ($category && isset($category->name)) {
                        $breadcrumbs[] = ['name' => $category->name, 'url' => null];
                    }
                    break;

                case 'brands.show':
                    $brand = $route->parameter('brand');
                    $breadcrumbs[] = ['name' => 'Brands', 'url' => route('brands.index')];
                    if ($brand && isset($brand->name)) {
                        $breadcrumbs[] = ['name' => $brand->name, 'url' => null];
                    }
                    break;
            }
        }

        return $breadcrumbs;
    }
}
