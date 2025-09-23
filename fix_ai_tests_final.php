<?php

/**
 * Script to fix AI test files with undefined variables - Final version
 */
$filePath = __DIR__.'/tests/Unit/AI/AIModelTrainingTest.php';

if (! file_exists($filePath)) {
    echo "File not found: $filePath\n";
    exit(1);
}

$content = file_get_contents($filePath);

// Fix specific issues
$fixes = [
    // Fix return statements
    '/return \[\];/' => 'return $models;',
    '/return \[\];/' => 'return $data;',
    '/return \[\];/' => 'return $clusters;',
    '/return \[\];/' => 'return $classifications;',
    '/return \[\];/' => 'return $input;',
    '/return \[\];/' => 'return $image;',
    '/return \[\];/' => 'return $weights;',
    '/return \[\];/' => 'return $biases;',
    '/return \[\];/' => 'return $layer;',
    '/return \[\];/' => 'return $batch;',
    '/return \[\];/' => 'return $kernel;',
    '/return \[\];/' => 'return $featureMap;',
    '/return \[\];/' => 'return $sequence;',

    // Fix array assignments
    '/\[\]\[\] = /' => '$models[] = ',
    '/\[\]\[\] = /' => '$data[] = ',
    '/\[\]\[\] = /' => '$clusters[] = ',
    '/\[\]\[\] = /' => '$classifications[] = ',
    '/\[\]\[\] = /' => '$input[] = ',
    '/\[\]\[\] = /' => '$image[] = ',
    '/\[\]\[\] = /' => '$weights[] = ',
    '/\[\]\[\] = /' => '$biases[] = ',
    '/\[\]\[\] = /' => '$layer[] = ',
    '/\[\]\[\] = /' => '$batch[] = ',
    '/\[\]\[\] = /' => '$kernel[] = ',
    '/\[\]\[\] = /' => '$featureMap[] = ',
    '/\[\]\[\] = /' => '$sequence[] = ',

    // Fix foreach loops
    '/foreach \(\[\] as /' => 'foreach ($data as ',
    '/foreach \(\[\] as /' => 'foreach ($clusters as ',
    '/foreach \(\[\] as /' => 'foreach ($classifications as ',
    '/foreach \(\[\] as /' => 'foreach ($input as ',
    '/foreach \(\[\] as /' => 'foreach ($image as ',
    '/foreach \(\[\] as /' => 'foreach ($weights as ',
    '/foreach \(\[\] as /' => 'foreach ($biases as ',
    '/foreach \(\[\] as /' => 'foreach ($layer as ',
    '/foreach \(\[\] as /' => 'foreach ($batch as ',
    '/foreach \(\[\] as /' => 'foreach ($kernel as ',
    '/foreach \(\[\] as /' => 'foreach ($featureMap as ',
    '/foreach \(\[\] as /' => 'foreach ($sequence as ',

    // Fix empty() calls
    '/empty\(\[\]\)/' => 'empty($data)',
    '/empty\(\[\]\)/' => 'empty($clusters)',
    '/empty\(\[\]\)/' => 'empty($classifications)',
    '/empty\(\[\]\)/' => 'empty($input)',
    '/empty\(\[\]\)/' => 'empty($image)',
    '/empty\(\[\]\)/' => 'empty($weights)',
    '/empty\(\[\]\)/' => 'empty($biases)',
    '/empty\(\[\]\)/' => 'empty($layer)',
    '/empty\(\[\]\)/' => 'empty($batch)',
    '/empty\(\[\]\)/' => 'empty($kernel)',
    '/empty\(\[\]\)/' => 'empty($featureMap)',
    '/empty\(\[\]\)/' => 'empty($sequence)',

    // Fix count() calls
    '/count\(\[\]\)/' => 'count($data)',
    '/count\(\[\]\)/' => 'count($clusters)',
    '/count\(\[\]\)/' => 'count($classifications)',
    '/count\(\[\]\)/' => 'count($input)',
    '/count\(\[\]\)/' => 'count($image)',
    '/count\(\[\]\)/' => 'count($weights)',
    '/count\(\[\]\)/' => 'count($biases)',
    '/count\(\[\]\)/' => 'count($layer)',
    '/count\(\[\]\)/' => 'count($batch)',
    '/count\(\[\]\)/' => 'count($kernel)',
    '/count\(\[\]\)/' => 'count($featureMap)',
    '/count\(\[\]\)/' => 'count($sequence)',
];

foreach ($fixes as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Write the fixed content back
file_put_contents($filePath, $content);

echo 'Fixed AI test file: '.basename($filePath)."\n";
