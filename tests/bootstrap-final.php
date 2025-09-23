<?php

use Illuminate\Contracts\Console\Kernel;

// Disable all error reporting for error handler warnings
// Don't change error reporting to avoid PHPUnit detection

// Don't set global error handlers to avoid PHPUnit warnings
// Let PHPUnit handle error reporting naturally

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

return $app;
