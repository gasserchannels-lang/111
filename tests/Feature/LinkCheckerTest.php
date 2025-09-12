<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LinkCheckerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test all internal links are working
     */
    public function test_internal_links_are_working()
    {
        $routes = Route::getRoutes();
        $brokenLinks = [];

        foreach ($routes as $route) {
            if ($route->methods()[0] === 'GET' && ! str_contains($route->uri(), '{')) {
                $url = url($route->uri());
                $response = Http::timeout(10)->get($url);

                if ($response->status() >= 400) {
                    $brokenLinks[] = [
                        'url' => $url,
                        'status' => $response->status(),
                        'route' => $route->getName(),
                    ];
                }
            }
        }

        $this->assertEmpty($brokenLinks, 'Found broken internal links: '.json_encode($brokenLinks));
    }

    /**
     * Test main navigation links
     */
    public function test_main_navigation_links()
    {
        $navigationLinks = [
            '/' => 'Homepage',
            '/products' => 'Products',
            '/categories' => 'Categories',
            '/stores' => 'Stores',
            '/about' => 'About',
            '/contact' => 'Contact',
        ];

        foreach ($navigationLinks as $url => $name) {
            $response = $this->get($url);
            $this->assertLessThan(400, $response->status(), "Navigation link '{$name}' ({$url}) is broken");
        }
    }

    /**
     * Test API endpoints are accessible
     */
    public function test_api_endpoints_are_accessible()
    {
        $apiEndpoints = [
            '/api/products' => 'Products API',
            '/api/categories' => 'Categories API',
            '/api/stores' => 'Stores API',
            '/api/search' => 'Search API',
        ];

        foreach ($apiEndpoints as $endpoint => $name) {
            $response = $this->get($endpoint);
            $this->assertLessThan(500, $response->status(), "API endpoint '{$name}' ({$endpoint}) is broken");
        }
    }

    /**
     * Test static assets are accessible
     */
    public function test_static_assets_are_accessible()
    {
        $staticAssets = [
            '/css/app.css' => 'Main CSS',
            '/js/app.js' => 'Main JS',
            '/favicon.ico' => 'Favicon',
        ];

        foreach ($staticAssets as $asset => $name) {
            $response = $this->get($asset);
            $this->assertLessThan(400, $response->status(), "Static asset '{$name}' ({$asset}) is broken");
        }
    }

    /**
     * Test external links (if any) are working
     */
    public function test_external_links_are_working()
    {
        $externalLinks = [
            'https://laravel.com' => 'Laravel Documentation',
            'https://github.com' => 'GitHub',
        ];

        foreach ($externalLinks as $url => $name) {
            $response = Http::timeout(10)->get($url);
            $this->assertLessThan(400, $response->status(), "External link '{$name}' ({$url}) is broken");
        }
    }

    /**
     * Test image links are working
     */
    public function test_image_links_are_working()
    {
        $imagePaths = [
            '/images/logo.png' => 'Logo',
            '/images/placeholder.jpg' => 'Placeholder Image',
        ];

        foreach ($imagePaths as $path => $name) {
            if (file_exists(public_path($path))) {
                $response = $this->get($path);
                $this->assertLessThan(400, $response->status(), "Image '{$name}' ({$path}) is broken");
            }
        }
    }

    /**
     * Test redirects are working correctly
     */
    public function test_redirects_are_working()
    {
        $redirects = [
            '/admin' => '/admin/dashboard',
            '/old-page' => '/new-page',
        ];

        foreach ($redirects as $from => $to) {
            $response = $this->get($from);
            if ($response->status() === 301 || $response->status() === 302) {
                $this->assertEquals($to, $response->headers->get('Location'));
            }
        }
    }

    /**
     * Test sitemap is accessible
     */
    public function test_sitemap_is_accessible()
    {
        $response = $this->get('/sitemap.xml');
        $this->assertLessThan(400, $response->status(), 'Sitemap is not accessible');

        if ($response->status() === 200) {
            $this->assertStringContainsString('<?xml', $response->content());
        }
    }

    /**
     * Test robots.txt is accessible
     */
    public function test_robots_txt_is_accessible()
    {
        $response = $this->get('/robots.txt');
        $this->assertLessThan(400, $response->status(), 'robots.txt is not accessible');
    }

    /**
     * Test all form action URLs are working
     */
    public function test_form_action_urls_are_working()
    {
        $formActions = [
            '/login' => 'Login Form',
            '/register' => 'Register Form',
            '/contact' => 'Contact Form',
            '/search' => 'Search Form',
        ];

        foreach ($formActions as $action => $name) {
            $response = $this->get($action);
            $this->assertLessThan(400, $response->status(), "Form action '{$name}' ({$action}) is broken");
        }
    }

    /**
     * Test pagination links are working
     */
    public function test_pagination_links_are_working()
    {
        // Test products pagination
        $response = $this->get('/products?page=1');
        $this->assertLessThan(400, $response->status(), 'Products pagination is broken');

        // Test categories pagination
        $response = $this->get('/categories?page=1');
        $this->assertLessThan(400, $response->status(), 'Categories pagination is broken');
    }
}
