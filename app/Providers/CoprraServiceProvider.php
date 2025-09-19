<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class CoprraServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge COPRRA configuration
        $this->mergeConfigFrom(
            config_path('coprra.php'),
            'coprra'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share global variables with all views
        View::composer('*', function ($view): void {
            $view->with([
                'coprraName' => config('coprra.name'),
                'coprraVersion' => config('coprra.version'),
                'defaultCurrency' => config('coprra.default_currency'),
                'defaultLanguage' => config('coprra.default_language'),
            ]);
        });

        // Register custom Blade directives
        $this->registerBladeDirectives();
    }

    /**
     * Register custom Blade directives.
     */
    private function registerBladeDirectives(): void
    {
        // Currency formatting directive
        Blade::directive('currency', fn ($expression): string => "<?php echo number_format($expression, 2); ?>");

        // Price comparison directive
        Blade::directive('pricecompare', fn ($expression): string => "<?php echo App\\Helpers\\PriceHelper::formatPrice($expression); ?>");

        // Language direction directive
        Blade::directive('rtl', fn (): string => "<?php echo in_array(app()->getLocale(), ['ar', 'ur', 'fa']) ? 'rtl' : 'ltr'; ?>");
    }
}
