<?php

/**
 * Script to fix syntax errors in test files
 */
$testDir = __DIR__.'/tests/Unit';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testDir));
$phpFiles = new RegexIterator($files, '/\.php$/');

$fixedFiles = 0;

foreach ($phpFiles as $file) {
    $filePath = $file->getPathname();
    $content = file_get_contents($filePath);

    if (! $content) {
        continue;
    }

    $originalContent = $content;

    // Fix methods without return type but with array return
    $content = preg_replace(
        '/protected function (\w+)\s*\{/',
        'protected function $1(): array {',
        $content
    );

    // Fix methods that have array return but missing return type
    $content = preg_replace(
        '/protected function (\w+)\([^)]*\)\s*\{[^}]*return\s*\[/',
        'protected function $1(): array {',
        $content
    );

    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        $fixedFiles++;
        echo 'Fixed: '.basename($filePath)."\n";
    }
}

echo "\nTotal files fixed: $fixedFiles\n";
