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

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

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
            return \Mockery::mock(\Symfony\Component\Console\Style\SymfonyStyle::class)->shouldIgnoreMissing();
        });

        return $app;
    }
}