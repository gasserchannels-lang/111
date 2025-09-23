<?php

use Illuminate\Contracts\Console\Kernel;

// Create the application
$app = require_once __DIR__.'/../bootstrap/app.php';

// Bootstrap the application
$app->make(Kernel::class)->bootstrap();

// Configure database for testing
config(['database.default' => 'testing']);
config(['database.connections.testing.database' => ':memory:']);
config(['database.connections.testing.driver' => 'sqlite']);
config(['database.connections.testing.prefix' => '']);
config(['database.connections.testing.foreign_key_constraints' => true]);

return $app;
