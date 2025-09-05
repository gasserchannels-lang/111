<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\FactoryConfigurationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        $this->configureFactoryNaming();
    }

    /**
     * Configure factory naming convention
     */
    private function configureFactoryNaming(): void
    {
        $factoryConfigService = new FactoryConfigurationService;
        $factoryConfigService->configureNaming();
    }
}
