<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\ProductRepository;
use App\Services\CacheService;
use App\Services\FactoryConfigurationService;
use App\Services\PriceSearchService;
use App\Services\ProductService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PriceSearchService::class, function ($app) {
            return new PriceSearchService($app->make(\Illuminate\Contracts\Validation\Factory::class));
        });

        // Register ProductService and its dependencies
        $this->app->singleton(CacheService::class);
        $this->app->singleton(ProductRepository::class);
        $this->app->singleton(ProductService::class, function ($app) {
            return new ProductService(
                $app->make(ProductRepository::class),
                $app->make(CacheService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureFactoryNaming();

        // Disable Telescope completely to avoid database issues
        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }
    }

    /**
     * Configure factory naming convention.
     */
    private function configureFactoryNaming(): void
    {
        $factoryConfigService = new FactoryConfigurationService;
        $factoryConfigService->configureNaming();
    }
}
