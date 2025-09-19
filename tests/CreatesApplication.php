<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        // تعيين APP_KEY قبل إنشاء التطبيق
        if (! env('APP_KEY')) {
            putenv('APP_KEY=base64:mAkbpuXF7OVTRIDCIMkD8+xw6xVi7pge9CFImeqZaxE=');
        }

        // Force the test database connection BEFORE creating the application
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        putenv('DB_HOST=');
        putenv('DB_PORT=');
        putenv('DB_USERNAME=');
        putenv('DB_PASSWORD=');

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Force the test database connection AFTER bootstrap
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        config(['database.connections.sqlite.driver' => 'sqlite']);
        config(['database.connections.sqlite.prefix' => '']);
        config(['database.connections.sqlite.foreign_key_constraints' => true]);

        // Clear any cached configuration
        if (method_exists($app, 'make')) {
            try {
                $app->make(\Illuminate\Contracts\Console\Kernel::class)->call('config:clear');
            } catch (\Exception $e) {
                // Ignore if config:clear fails
            }
        }

        // Ensure database is properly configured for testing
        if ($app->environment('testing')) {
            $app->useDatabasePath(':memory:');
        }

        // Bind silent mocks for console input and output to prevent interactive prompts during tests
        $app->bind(\Symfony\Component\Console\Input\InputInterface::class, function ($app) {
            $mock = \Mockery::mock(\Symfony\Component\Console\Input\InputInterface::class);
            $mock->shouldReceive('isInteractive')->andReturn(false);
            $mock->shouldReceive('hasArgument')->andReturn(false);
            $mock->shouldReceive('getArgument')->andReturn(null);
            $mock->shouldReceive('hasOption')->andReturn(false);
            $mock->shouldReceive('getOption')->andReturn(null);

            return $mock;
        });

        // Use a lenient mock for the output style to ignore unexpected calls like askQuestion
        $app->bind(\Symfony\Component\Console\Style\OutputStyle::class, function ($app) {
            $mock = \Mockery::mock(\Symfony\Component\Console\Style\SymfonyStyle::class);
            $mock->shouldReceive('askQuestion')->andReturn(true);
            $mock->shouldReceive('confirm')->andReturn(true);
            $mock->shouldReceive('ask')->andReturn('test');
            $mock->shouldReceive('choice')->andReturn('test');
            $mock->shouldIgnoreMissing();

            return $mock;
        });

        return $app;
    }
}
