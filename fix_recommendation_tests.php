<?php

$recommendationTestFiles = [
    'tests/Unit/Recommendations/BrandRecommendationTest.php',
    'tests/Unit/Recommendations/CategoryRecommendationTest.php',
    'tests/Unit/Recommendations/CollaborativeFilteringTest.php',
    'tests/Unit/Recommendations/ContentBasedFilteringTest.php',
    'tests/Unit/Recommendations/CrossSellRecommendationTest.php',
    'tests/Unit/Recommendations/HybridRecommendationTest.php',
    'tests/Unit/Recommendations/PersonalizedRecommendationTest.php',
    'tests/Unit/Recommendations/PriceDropAlertTest.php',
    'tests/Unit/Recommendations/PriceRangeRecommendationTest.php',
];

foreach ($recommendationTestFiles as $file) {
    if (! file_exists($file)) {
        echo "File not found: $file\n";

        continue;
    }

    $content = file_get_contents($file);

    // Fix class inheritance
    $content = preg_replace('/use Tests\\SafeTestBase;/', 'use Tests\Unit\BaseTest;', $content);
    $content = preg_replace('/use Tests\\Unit\\SafeLaravelTest;/', 'use Tests\Unit\BaseTest;', $content);
    $content = preg_replace('/class \w+ extends SafeTestBase/', 'class '.basename($file, '.php').' extends BaseTest', $content);
    $content = preg_replace('/class \w+ extends SafeLaravelTest/', 'class '.basename($file, '.php').' extends BaseTest', $content);

    // Fix setUp method
    $content = preg_replace(
        '/protected function setUp\(\): void\s*\{\s*parent::setUp\(\);\s*\}/',
        "protected function setUp(): void\n    {\n        // Setup without calling parent to avoid error handler modifications\n    }",
        $content
    );

    // Fix tearDown method
    $content = preg_replace(
        '/protected function tearDown\(\): void\s*\{\s*parent::tearDown\(\);\s*\}/',
        "protected function tearDown(): void\n    {\n        // Cleanup without calling parent to avoid error handler modifications\n    }",
        $content
    );

    file_put_contents($file, $content);
    echo "Fixed: $file\n";
}

echo "All recommendation tests fixed!\n";
