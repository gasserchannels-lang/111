<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Language;
use Illuminate\View\View;

class AppComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Share common data with all views
        $view->with([
            'app_name' => config('app.name', 'COPRRA'),
            'app_version' => config('app.version', '1.0.0'),
            'current_year' => now()->year,
            'languages' => $this->getLanguages(),
            'categories' => $this->getCategories(),
            'brands' => $this->getBrands(),
            'is_rtl' => $this->isRTL(),
        ]);
    }

    /**
     * Get active languages.
     */
    private function getLanguages(): array
    {
        return cache()->remember('languages', 3600, function () {
            return Language::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($language) {
                    return [
                        'code' => $language->code,
                        'name' => $language->name,
                        'native_name' => $language->native_name,
                        'direction' => $language->direction,
                        'is_current' => app()->getLocale() === $language->code,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get active categories.
     */
    private function getCategories(): array
    {
        return cache()->remember('categories_menu', 1800, function () {
            return Category::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'url' => route('categories.show', $category->slug),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get active brands.
     */
    private function getBrands(): array
    {
        return cache()->remember('brands_menu', 1800, function () {
            return Brand::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(function ($brand) {
                    return [
                        'id' => $brand->id,
                        'name' => $brand->name,
                        'slug' => $brand->slug,
                        'logo' => $brand->logo,
                        'url' => route('brands.show', $brand->slug),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Check if current locale is RTL.
     */
    private function isRTL(): bool
    {
        $rtlLocales = ['ar', 'ur', 'fa', 'he'];

        return in_array(app()->getLocale(), $rtlLocales);
    }
}
