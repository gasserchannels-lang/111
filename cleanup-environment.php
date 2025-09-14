<?php

/**
 * Environment Cleanup Script for Mockery Conflicts
 *
 * This script performs comprehensive cleanup of the Laravel environment
 * to resolve Mockery conflicts and ensure clean test execution.
 */
echo "=== Laravel Environment Cleanup Script ===\n\n";

// Function to run command and display output
function runCommand($command, $description)
{
    echo "{$description}...\n";
    echo "Command: {$command}\n";

    $output = [];
    $returnCode = 0;
    exec($command.' 2>&1', $output, $returnCode);

    if ($returnCode === 0) {
        echo "✓ Success\n";
        if (! empty($output)) {
            echo 'Output: '.implode("\n", $output)."\n";
        }
    } else {
        echo "✗ Failed (Exit code: {$returnCode})\n";
        if (! empty($output)) {
            echo 'Error: '.implode("\n", $output)."\n";
        }
    }
    echo "\n";
}

// Function to delete directory recursively
function deleteDirectory($dir)
{
    if (! is_dir($dir)) {
        return true;
    }

    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir.DIRECTORY_SEPARATOR.$file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }

    return rmdir($dir);
}

// 1. Clear Laravel caches
echo "1. Clearing Laravel Caches:\n";
echo "----------------------------------------\n";

$cacheCommands = [
    'php artisan cache:clear' => 'Application cache',
    'php artisan config:clear' => 'Configuration cache',
    'php artisan route:clear' => 'Route cache',
    'php artisan view:clear' => 'View cache',
    'php artisan event:clear' => 'Event cache',
    'php artisan queue:clear' => 'Queue cache',
    'php artisan optimize:clear' => 'All optimization caches',
];

foreach ($cacheCommands as $command => $description) {
    runCommand($command, $description);
}

// 2. Clear Composer cache
echo "2. Clearing Composer Cache:\n";
echo "----------------------------------------\n";

runCommand('composer clear-cache', 'Composer cache');

// 3. Clear PHPUnit cache
echo "3. Clearing PHPUnit Cache:\n";
echo "----------------------------------------\n";

$phpunitCacheFiles = [
    'storage/logs/junit.xml',
    'storage/logs/coverage',
    '.phpunit.result.cache',
    'phpunit.cache',
];

foreach ($phpunitCacheFiles as $file) {
    if (file_exists($file)) {
        if (is_dir($file)) {
            deleteDirectory($file);
            echo "✓ Deleted directory: {$file}\n";
        } else {
            unlink($file);
            echo "✓ Deleted file: {$file}\n";
        }
    } else {
        echo "- File not found: {$file}\n";
    }
}

echo "\n";

// 4. Clear storage directories
echo "4. Clearing Storage Directories:\n";
echo "----------------------------------------\n";

$storageDirs = [
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
];

foreach ($storageDirs as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir.'/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "✓ Cleared directory: {$dir}\n";
    } else {
        echo "- Directory not found: {$dir}\n";
    }
}

echo "\n";

// 5. Clear bootstrap cache
echo "5. Clearing Bootstrap Cache:\n";
echo "----------------------------------------\n";

$bootstrapFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/routes.php',
    'bootstrap/cache/events.php',
];

foreach ($bootstrapFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "✓ Deleted: {$file}\n";
    } else {
        echo "- File not found: {$file}\n";
    }
}

echo "\n";

// 6. Regenerate autoload files
echo "6. Regenerating Autoload Files:\n";
echo "----------------------------------------\n";

runCommand('composer dump-autoload --optimize', 'Composer autoload');

// 7. Clear vendor directory (optional - commented out for safety)
echo "7. Optional: Clear Vendor Directory:\n";
echo "----------------------------------------\n";
echo "⚠️  WARNING: This will delete the vendor directory and require reinstallation.\n";
echo "Uncomment the following lines if you want to perform a complete cleanup:\n";
echo "// deleteDirectory('vendor');\n";
echo "// runCommand('composer install --no-dev --optimize-autoloader', 'Reinstall packages');\n";

// Uncomment these lines if you want to perform complete cleanup
// deleteDirectory('vendor');
// runCommand('composer install --no-dev --optimize-autoloader', 'Reinstall packages');

echo "\n";

// 8. Set proper permissions
echo "8. Setting Proper Permissions:\n";
echo "----------------------------------------\n";

$permissionCommands = [
    'chmod -R 755 storage' => 'Storage directory permissions',
    'chmod -R 755 bootstrap/cache' => 'Bootstrap cache permissions',
];

foreach ($permissionCommands as $command => $description) {
    runCommand($command, $description);
}

// 9. Final verification
echo "9. Final Verification:\n";
echo "----------------------------------------\n";

$verificationCommands = [
    'php artisan --version' => 'Laravel version',
    'php artisan config:cache' => 'Configuration cache test',
    'php artisan route:cache' => 'Route cache test',
];

foreach ($verificationCommands as $command => $description) {
    runCommand($command, $description);
}

echo "\n=== Cleanup Complete ===\n";
echo "\nNext steps:\n";
echo "1. Run 'php artisan test' to verify tests work correctly\n";
echo "2. Run 'php diagnose-mockery.php' to check for remaining issues\n";
echo "3. If issues persist, consider deleting vendor/ and running 'composer install'\n";
