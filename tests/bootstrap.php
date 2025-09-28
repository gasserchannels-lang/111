<?php

use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/../bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

return $app;
