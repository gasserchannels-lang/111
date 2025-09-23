<?php

use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

return $app;
