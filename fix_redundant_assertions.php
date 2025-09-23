<?php

$file = 'tests/Unit/Recommendations/CrossSellRecommendationTest.php';
$content = file_get_contents($file);

// Remove redundant assertIsArray calls
$content = preg_replace('/\s*\$this->assertIsArray\(\$[^)]+\);\s*\n/', '', $content);

// Remove redundant assertTrue(true) calls
$content = preg_replace('/\s*\$this->assertTrue\(true\);\s*\n/', '', $content);

// Remove redundant strict comparisons
$content = preg_replace('/\s*\$this->assertTrue\(\d+ === \d+\);\s*\n/', '', $content);

// Remove redundant assertIsFloat calls
$content = preg_replace('/\s*\$this->assertIsFloat\(\$[^)]+\);\s*\n/', '', $content);

file_put_contents($file, $content);
echo "Fixed redundant assertions in $file\n";
