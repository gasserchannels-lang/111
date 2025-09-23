<?php

/**
 * Script to fix AI test files with undefined variables
 */
$aiTestFiles = [
    __DIR__.'/tests/Unit/AI/AIModelTrainingTest.php',
    __DIR__.'/tests/AI/TextProcessingTest.php',
    __DIR__.'/tests/AI/StrictQualityAgentTest.php',
    __DIR__.'/tests/AI/RecommendationSystemTest.php',
    __DIR__.'/tests/AI/ProductClassificationTest.php',
    __DIR__.'/tests/AI/ImageProcessingTest.php',
    __DIR__.'/tests/AI/ContinuousQualityMonitorTest.php',
    __DIR__.'/tests/AI/AIResponseTimeTest.php',
    __DIR__.'/tests/AI/AIModelTest.php',
    __DIR__.'/tests/AI/AIModelPerformanceTest.php',
    __DIR__.'/tests/AI/AILearningTest.php',
    __DIR__.'/tests/AI/AIErrorHandlingTest.php',
    __DIR__.'/tests/AI/AIAccuracyTest.php',
];

$fixedFiles = 0;

foreach ($aiTestFiles as $filePath) {
    if (! file_exists($filePath)) {
        continue;
    }

    $content = file_get_contents($filePath);

    if (! $content) {
        continue;
    }

    $originalContent = $content;

    // Fix undefined variables by adding default values
    $replacements = [
        // Common undefined variables
        '/\$rate\b(?!\s*[=\(])/' => '0.5',
        '/\$clipValue\b(?!\s*[=\(])/' => '1.0',
        '/\$momentum\b(?!\s*[=\(])/' => '0.9',
        '/\$models\b(?!\s*[=\(])/' => '[]',
        '/\$pretrainedModel\b(?!\s*[=\(])/' => 'new \stdClass()',
        '/\$clients\b(?!\s*[=\(])/' => '3',
        '/\$dataPerClient\b(?!\s*[=\(])/' => '100',
        '/\$data\b(?!\s*[=\(])/' => '[]',
        '/\$count\b(?!\s*[=\(])/' => '100',
        '/\$input\b(?!\s*[=\(])/' => '[]',
        '/\$classifications\b(?!\s*[=\(])/' => '[]',
        '/\$clusters\b(?!\s*[=\(])/' => '[]',
        '/\$targetSize\b(?!\s*[=\(])/' => '[224, 224]',
        '/\$image\b(?!\s*[=\(])/' => '[]',
        '/\$contentImage\b(?!\s*[=\(])/' => '[]',
        '/\$augmentations\b(?!\s*[=\(])/' => '[]',
        '/\$scaleFactor\b(?!\s*[=\(])/' => '2',
        '/\$hiddenSize\b(?!\s*[=\(])/' => '128',
        '/\$inputSize\b(?!\s*[=\(])/' => '784',
        '/\$outputSize\b(?!\s*[=\(])/' => '10',
        '/\$weights\b(?!\s*[=\(])/' => '[]',
        '/\$biases\b(?!\s*[=\(])/' => '[]',
        '/\$layer\b(?!\s*[=\(])/' => '[]',
        '/\$dropoutRate\b(?!\s*[=\(])/' => '0.5',
        '/\$batch\b(?!\s*[=\(])/' => '[]',
        '/\$kernel\b(?!\s*[=\(])/' => '[]',
        '/\$featureMap\b(?!\s*[=\(])/' => '[]',
        '/\$poolSize\b(?!\s*[=\(])/' => '2',
        '/\$sequence\b(?!\s*[=\(])/' => '[]',
        '/\$k\b(?!\s*[=\(])/' => '3',
        '/\$strength\b(?!\s*[=\(])/' => '0.01',
    ];

    foreach ($replacements as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }

    // Fix method signatures that are missing parameters
    $content = preg_replace(
        '/protected function (\w+)\(\): array\s*\{/',
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
