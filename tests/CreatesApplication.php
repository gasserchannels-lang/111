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

        return $app;
    }
}
