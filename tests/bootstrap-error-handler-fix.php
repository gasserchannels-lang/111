<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

// Don't modify error handlers at all during bootstrap
// Let PHPUnit handle error handler management

// Set up environment variables
putenv('APP_ENV=testing');
putenv('APP_DEBUG=true');
putenv('APP_KEY=base64:2fl+K+k3WPMO+QrdnDWEbVjQo7EjXQYWmYNgayUk5P0=');
putenv('DB_CONNECTION=testing');
putenv('DB_DATABASE=:memory:');
putenv('DB_HOST=');
putenv('DB_PORT=');
putenv('DB_USERNAME=');
putenv('DB_PASSWORD=');

// Create the application
$app = require __DIR__.'/../bootstrap/app.php';

// Bootstrap the application
$app->make(Kernel::class)->bootstrap();

// Configure database for testing
config(['database.default' => 'testing']);
config(['database.connections.testing.database' => ':memory:']);
config(['database.connections.testing.driver' => 'sqlite']);
config(['database.connections.testing.prefix' => '']);
config(['database.connections.testing.foreign_key_constraints' => true]);

// Set up error reporting for testing
// Don't change error reporting to avoid PHPUnit detection
// Removed ini_set calls to prevent risky test warnings

return $app;
