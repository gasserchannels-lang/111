<?php

use Illuminate\Contracts\Console\Kernel;

// Minimal bootstrap without any error handling
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

return $app;
