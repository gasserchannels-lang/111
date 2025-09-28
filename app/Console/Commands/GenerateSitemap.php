<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate XML sitemap for SEO optimization';

    public function handle()
    {
        $this->info('Generating sitemap...');

        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        // Homepage
        $sitemap .= $this->addUrl(url('/'), '1.0', 'daily');

        // Products
        Product::chunk(100, function ($products) use (&$sitemap) {
            foreach ($products as $product) {
                $sitemap .= $this->addUrl(
                    route('products.show', $product->id),
                    '0.8',
                    'weekly'
                );
            }
        });

        // Categories
        Category::chunk(100, function ($categories) use (&$sitemap) {
            foreach ($categories as $category) {
                $sitemap .= $this->addUrl(
                    route('categories.show', $category->id),
                    '0.7',
                    'weekly'
                );
            }
        });

        // Brands
        Brand::chunk(100, function ($brands) use (&$sitemap) {
            foreach ($brands as $brand) {
                $sitemap .= $this->addUrl(
                    route('brands.show', $brand->id),
                    '0.6',
                    'monthly'
                );
            }
        });

        $sitemap .= '</urlset>';

        File::put(public_path('sitemap.xml'), $sitemap);

        $this->info('Sitemap generated successfully at public/sitemap.xml');
    }

    private function addUrl(string $url, string $priority, string $changefreq): string
    {
        return "  <url>\n".
            "    <loc>{$url}</loc>\n".
            "    <priority>{$priority}</priority>\n".
            "    <changefreq>{$changefreq}</changefreq>\n".
            '    <lastmod>'.now()->toISOString()."</lastmod>\n".
            "  </url>\n";
    }
}
