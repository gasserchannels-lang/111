<?php

echo "Updating all test files to use SafeTestBase...\n";

// Get all test files
$testFiles = array_merge(
    glob('tests/Unit/**/*Test.php'),
    glob('tests/Feature/**/*Test.php')
);

$updatedFiles = 0;

foreach ($testFiles as $file) {
    echo "Processing: $file\n";

    $content = file_get_contents($file);
    $originalContent = $content;

    // Replace various base classes with SafeTestBase
    $replacements = [
        'use Tests\Unit\BaseTest;' => 'use Tests\SafeTestBase;',
        'extends BaseTest' => 'extends SafeTestBase',
        'use Tests\TestCase;' => 'use Tests\SafeTestBase;',
        'extends TestCase' => 'extends SafeTestBase',
        'use Tests\SafeLaravelTest;' => 'use Tests\SafeTestBase;',
        'extends SafeLaravelTest' => 'extends SafeTestBase',
    ];

    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $updatedFiles++;
        echo "  ✓ Updated\n";
    } else {
        echo "  - No changes needed\n";
    }
}

echo "\nUpdated $updatedFiles test files.\n";
echo "All test files have been updated to use SafeTestBase!\n";
