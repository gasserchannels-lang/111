<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Language;
use Illuminate\View\View;

final class AppComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Share common data with all views
        $view->with([
            'app_name' => is_string(config('app.name')) ? config('app.name') : 'COPRRA',
            'app_version' => is_string(config('app.version')) ? config('app.version') : '1.0.0',
            'current_year' => now()->year,
            'languages' => $this->getLanguages(),
            'categories' => $this->getCategories(),
            'brands' => $this->getBrands(),
            'is_rtl' => $this->isRTL(),
        ]);
    }

    /**
     * Get active languages.
     *
     * @return list<array<string, string|int|bool>>
     */
    private function getLanguages(): array
    {
        $result = cache()->remember('languages', 3600, fn (): array => Language::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($language): array => [
                'code' => is_string($language->code) ? $language->code : '',
                'name' => is_string($language->name) ? $language->name : '',
                'native_name' => is_string($language->native_name) ? $language->native_name : '',
                'direction' => is_string($language->direction) ? $language->direction : 'ltr',
                'is_current' => app()->getLocale() === $language->code,
            ])
            ->toArray());

        if (is_array($result)) {
            /** @var list<array<string, string|int|bool>> $mappedResult */
            $mappedResult = array_values(array_map(fn ($item): array => is_array($item) ? $item : [], $result));

            return $mappedResult;
        }

        return [];
    }

    /**
     * Get active categories.
     *
     * @return list<array<string, string|int|bool>>
     */
    private function getCategories(): array
    {
        $result = cache()->remember('categories_menu', 1800, fn (): array => Category::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($category): array => [
                'id' => is_numeric($category->id) ? (int) $category->id : 0,
                'name' => is_string($category->name) ? $category->name : '',
                'slug' => is_string($category->slug) ? $category->slug : '',
                'url' => is_string($category->slug) ? route('categories.show', $category->slug) : '',
            ])
            ->toArray());

        if (is_array($result)) {
            /** @var list<array<string, string|int|bool>> $mappedResult */
            $mappedResult = array_values(array_map(fn ($item): array => is_array($item) ? $item : [], $result));

            return $mappedResult;
        }

        return [];
    }

    /**
     * Get active brands.
     *
     * @return list<array<string, string|int|bool|null>>
     */
    private function getBrands(): array
    {
        $result = cache()->remember('brands_menu', 1800, fn (): array => Brand::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($brand): array => [
                'id' => is_numeric($brand->id) ? (int) $brand->id : 0,
                'name' => is_string($brand->name) ? $brand->name : '',
                'slug' => is_string($brand->slug) ? $brand->slug : '',
                'logo' => is_string($brand->logo_url) ? $brand->logo_url : null,
                'url' => is_string($brand->slug) ? route('brands.show', $brand->slug) : '',
            ])
            ->toArray());

        if (is_array($result)) {
            /** @var list<array<string, string|int|bool|null>> $mappedResult */
            $mappedResult = array_values(array_map(fn ($item): array => is_array($item) ? $item : [], $result));

            return $mappedResult;
        }

        return [];
    }

    /**
     * Check if current locale is RTL.
     */
    private function isRTL(): bool
    {
        $rtlLocales = ['ar', 'ur', 'fa', 'he'];
        $currentLocale = app()->getLocale();

        return is_string($currentLocale) && in_array($currentLocale, $rtlLocales);
    }
}
