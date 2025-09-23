<?php

$file = 'tests/Unit/Recommendations/CrossSellRecommendationTest.php';
$content = file_get_contents($file);

// Remove redundant assertIsArray calls on variables that are already arrays
$patterns = [
    '/\s*\$this->assertIsArray\(\$recommendations\);\s*\n/' => '',
    '/\s*\$this->assertIsArray\(\$recommendation\);\s*\n/' => '',
    '/\s*\$this->assertTrue\(true\);\s*\n/' => '',
    '/\s*\$this->assertTrue\(\d+ === \d+\);\s*\n/' => '',
    '/\s*\$this->assertIsFloat\(\$[^)]+\);\s*\n/' => '',
];

foreach ($patterns as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

file_put_contents($file, $content);
echo "Fixed redundant assertions in $file\n";
