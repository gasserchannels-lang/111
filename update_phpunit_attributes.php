<?php

/**
 * Script to update PHPUnit @test annotations to PHP 8 attributes
 */
$testDir = __DIR__.'/tests';
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($testDir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$updatedFiles = 0;
$totalMethods = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $originalContent = $content;

        // Add use statement if not present
        if (
            strpos($content, 'use PHPUnit\Framework\Attributes\Test;') === false &&
            strpos($content, '/** @test */') !== false
        ) {
            $content = preg_replace(
                '/(use [^;]+;)(\s*)(class)/',
                "$1\nuse PHPUnit\Framework\Attributes\Test;$2$3",
                $content
            );
        }

        // Replace /** @test */ with #[Test]
        $content = preg_replace('/\/\*\* @test \*\/\s*\n\s*/', "#[Test]\n    ", $content);

        if ($content !== $originalContent) {
            file_put_contents($file->getPathname(), $content);
            $updatedFiles++;

            // Count methods updated
            $methodCount = substr_count($originalContent, '/** @test */');
            $totalMethods += $methodCount;

            echo 'Updated: '.$file->getPathname()." ($methodCount methods)\n";
        }
    }
}

echo "\n=== Summary ===\n";
echo "Files updated: $updatedFiles\n";
echo "Total methods updated: $totalMethods\n";
echo "PHPUnit attributes update completed!\n";
