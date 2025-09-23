<?php

use Illuminate\Contracts\Console\Kernel;

// Don't change error handlers to avoid PHPUnit detection

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

return $app;
