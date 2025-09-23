<?php

echo "Updating test files to use correct namespace...\n";

$files = glob('tests/Unit/**/*Test.php');
$updated = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;

    $content = str_replace('use Tests\Unit\SafeLaravelTest;', 'use Tests\SafeLaravelTest;', $content);

    if ($content !== $original) {
        file_put_contents($file, $content);
        $updated++;
    }
}

echo "Updated $updated files.\n";
