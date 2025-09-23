<?php

/**
 * Comprehensive script to fix all AI test files
 */
$filePath = __DIR__.'/tests/Unit/AI/AIModelTrainingTest.php';

if (! file_exists($filePath)) {
    echo "File not found: $filePath\n";
    exit(1);
}

$content = file_get_contents($filePath);

// Fix all array assignment issues
$content = preg_replace('/\[\]\[\] = /', '$data[] = ', $content);
$content = preg_replace('/\[\]\[\] = /', '$models[] = ', $content);
$content = preg_replace('/\[\]\[\] = /', '$clusters[] = ', $content);
$content = preg_replace('/\[\]\[\] = /', '$classifications[] = ', $content);
$content = preg_replace('/\[\]\[\] = /', '$input[] = ', $content);
$content = preg_replace('/\[\]\[\] = /', '$image[] = ', $content);
$content = preg_replace('/\[\]\[\] = /', '$weights[] = ', $content);
$content = preg_replace('/\[\]\[\] = /', '$biases[] = ', $content);
$content = preg_replace('/\[\]\[\] = /', '$layer[] = ', $content);
$content = preg_replace('/\[\]\[\] = /', '$batch[] = ', $content);
$content = preg_replace('/\[\]\[\] = /', '$kernel[] = ', $content);
$content = preg_replace('/\[\]\[\] = /', '$featureMap[] = ', $content);
$content = preg_replace('/\[\]\[\] = /', '$sequence[] = ', $content);

// Fix all return statements
$content = preg_replace('/return \[\];/', 'return $data;', $content);
$content = preg_replace('/return \[\];/', 'return $models;', $content);
$content = preg_replace('/return \[\];/', 'return $clusters;', $content);
$content = preg_replace('/return \[\];/', 'return $classifications;', $content);
$content = preg_replace('/return \[\];/', 'return $input;', $content);
$content = preg_replace('/return \[\];/', 'return $image;', $content);
$content = preg_replace('/return \[\];/', 'return $weights;', $content);
$content = preg_replace('/return \[\];/', 'return $biases;', $content);
$content = preg_replace('/return \[\];/', 'return $layer;', $content);
$content = preg_replace('/return \[\];/', 'return $batch;', $content);
$content = preg_replace('/return \[\];/', 'return $kernel;', $content);
$content = preg_replace('/return \[\];/', 'return $featureMap;', $content);
$content = preg_replace('/return \[\];/', 'return $sequence;', $content);

// Fix all foreach loops
$content = preg_replace('/foreach \(\[\] as /', 'foreach ($data as ', $content);
$content = preg_replace('/foreach \(\[\] as /', 'foreach ($clusters as ', $content);
$content = preg_replace('/foreach \(\[\] as /', 'foreach ($classifications as ', $content);
$content = preg_replace('/foreach \(\[\] as /', 'foreach ($input as ', $content);
$content = preg_replace('/foreach \(\[\] as /', 'foreach ($image as ', $content);
$content = preg_replace('/foreach \(\[\] as /', 'foreach ($weights as ', $content);
$content = preg_replace('/foreach \(\[\] as /', 'foreach ($biases as ', $content);
$content = preg_replace('/foreach \(\[\] as /', 'foreach ($layer as ', $content);
$content = preg_replace('/foreach \(\[\] as /', 'foreach ($batch as ', $content);
$content = preg_replace('/foreach \(\[\] as /', 'foreach ($kernel as ', $content);
$content = preg_replace('/foreach \(\[\] as /', 'foreach ($featureMap as ', $content);
$content = preg_replace('/foreach \(\[\] as /', 'foreach ($sequence as ', $content);

// Fix all empty() calls
$content = preg_replace('/empty\(\[\]\)/', 'empty($data)', $content);
$content = preg_replace('/empty\(\[\]\)/', 'empty($clusters)', $content);
$content = preg_replace('/empty\(\[\]\)/', 'empty($classifications)', $content);
$content = preg_replace('/empty\(\[\]\)/', 'empty($input)', $content);
$content = preg_replace('/empty\(\[\]\)/', 'empty($image)', $content);
$content = preg_replace('/empty\(\[\]\)/', 'empty($weights)', $content);
$content = preg_replace('/empty\(\[\]\)/', 'empty($biases)', $content);
$content = preg_replace('/empty\(\[\]\)/', 'empty($layer)', $content);
$content = preg_replace('/empty\(\[\]\)/', 'empty($batch)', $content);
$content = preg_replace('/empty\(\[\]\)/', 'empty($kernel)', $content);
$content = preg_replace('/empty\(\[\]\)/', 'empty($featureMap)', $content);
$content = preg_replace('/empty\(\[\]\)/', 'empty($sequence)', $content);

// Fix all count() calls
$content = preg_replace('/count\(\[\]\)/', 'count($data)', $content);
$content = preg_replace('/count\(\[\]\)/', 'count($clusters)', $content);
$content = preg_replace('/count\(\[\]\)/', 'count($classifications)', $content);
$content = preg_replace('/count\(\[\]\)/', 'count($input)', $content);
$content = preg_replace('/count\(\[\]\)/', 'count($image)', $content);
$content = preg_replace('/count\(\[\]\)/', 'count($weights)', $content);
$content = preg_replace('/count\(\[\]\)/', 'count($biases)', $content);
$content = preg_replace('/count\(\[\]\)/', 'count($layer)', $content);
$content = preg_replace('/count\(\[\]\)/', 'count($batch)', $content);
$content = preg_replace('/count\(\[\]\)/', 'count($kernel)', $content);
$content = preg_replace('/count\(\[\]\)/', 'count($featureMap)', $content);
$content = preg_replace('/count\(\[\]\)/', 'count($sequence)', $content);

// Write the fixed content back
file_put_contents($filePath, $content);

echo 'Fixed AI test file: '.basename($filePath)."\n";
