<?php

/**
 * Code Documentation Checker Tool
 * Analyzes PHP code documentation coverage and quality
 */
class CodeDocumentationChecker
{
    private array $documentationRules = [
        'class_documentation' => true,
        'method_documentation' => true,
        'property_documentation' => true,
        'parameter_documentation' => true,
        'return_documentation' => true,
    ];

    public function checkFile(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return ['error' => 'File not found'];
        }

        $content = file_get_contents($filePath);
        $tokens = token_get_all($content);

        return [
            'file' => $filePath,
            'classes' => $this->checkClasses($tokens),
            'methods' => $this->checkMethods($tokens),
            'properties' => $this->checkProperties($tokens),
            'documentation_coverage' => $this->calculateDocumentationCoverage($tokens),
        ];
    }

    private function checkClasses(array $tokens): array
    {
        $classes = [];

        for ($i = 0; $i < count($tokens); $i++) {
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_CLASS) {
                $className = $this->getNextIdentifier($tokens, $i);
                $hasDocumentation = $this->hasDocumentationBefore($tokens, $i);

                $classes[] = [
                    'name' => $className,
                    'line' => $tokens[$i][2],
                    'has_documentation' => $hasDocumentation,
                    'documentation_quality' => $this->assessDocumentationQuality($tokens, $i),
                ];
            }
        }

        return $classes;
    }

    private function checkMethods(array $tokens): array
    {
        $methods = [];

        for ($i = 0; $i < count($tokens); $i++) {
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_FUNCTION) {
                $methodName = $this->getNextIdentifier($tokens, $i);
                $hasDocumentation = $this->hasDocumentationBefore($tokens, $i);
                $parameters = $this->extractParameters($tokens, $i);

                $methods[] = [
                    'name' => $methodName,
                    'line' => $tokens[$i][2],
                    'has_documentation' => $hasDocumentation,
                    'parameters' => $parameters,
                    'has_return_documentation' => $this->hasReturnDocumentation($tokens, $i),
                    'documentation_quality' => $this->assessDocumentationQuality($tokens, $i),
                ];
            }
        }

        return $methods;
    }

    private function checkProperties(array $tokens): array
    {
        $properties = [];

        for ($i = 0; $i < count($tokens); $i++) {
            if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_PUBLIC, T_PRIVATE, T_PROTECTED])) {
                // Look for variable declaration after visibility modifier
                for ($j = $i + 1; $j < min($i + 10, count($tokens)); $j++) {
                    if (is_array($tokens[$j]) && $tokens[$j][0] === T_VARIABLE) {
                        $propertyName = $tokens[$j][1];
                        $hasDocumentation = $this->hasDocumentationBefore($tokens, $i);

                        $properties[] = [
                            'name' => $propertyName,
                            'line' => $tokens[$j][2],
                            'visibility' => $tokens[$i][1],
                            'has_documentation' => $hasDocumentation,
                            'documentation_quality' => $this->assessDocumentationQuality($tokens, $i),
                        ];
                        break;
                    }
                }
            }
        }

        return $properties;
    }

    private function getNextIdentifier(array $tokens, int $startIndex): string
    {
        for ($i = $startIndex + 1; $i < count($tokens); $i++) {
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_STRING) {
                return $tokens[$i][1];
            }
        }

        return '';
    }

    private function hasDocumentationBefore(array $tokens, int $index): bool
    {
        // Look for DocBlock comment before the element
        for ($i = $index - 1; $i >= max(0, $index - 10); $i--) {
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_DOC_COMMENT) {
                return true;
            }
            // Stop if we hit another significant token
            if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_CLASS, T_FUNCTION, T_VARIABLE])) {
                break;
            }
        }

        return false;
    }

    private function extractParameters(array $tokens, int $functionIndex): array
    {
        $parameters = [];
        $inParams = false;
        $currentParam = '';

        for ($i = $functionIndex; $i < count($tokens); $i++) {
            if (! $inParams && is_string($tokens[$i]) && $tokens[$i] === '(') {
                $inParams = true;

                continue;
            }

            if ($inParams) {
                if (is_string($tokens[$i]) && $tokens[$i] === ')') {
                    if (! empty($currentParam)) {
                        $parameters[] = trim($currentParam);
                    }
                    break;
                }

                if (is_array($tokens[$i]) && $tokens[$i][0] === T_VARIABLE) {
                    $currentParam = $tokens[$i][1];
                } elseif (is_string($tokens[$i]) && $tokens[$i] === ',') {
                    if (! empty($currentParam)) {
                        $parameters[] = trim($currentParam);
                        $currentParam = '';
                    }
                }
            }
        }

        return $parameters;
    }

    private function hasReturnDocumentation(array $tokens, int $functionIndex): bool
    {
        // Look for @return tag in DocBlock before function
        for ($i = $functionIndex - 1; $i >= max(0, $functionIndex - 10); $i--) {
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_DOC_COMMENT) {
                return strpos($tokens[$i][1], '@return') !== false;
            }
        }

        return false;
    }

    private function assessDocumentationQuality(array $tokens, int $index): array
    {
        $quality = [
            'score' => 0,
            'issues' => [],
        ];

        // Find the documentation comment
        $docComment = null;
        for ($i = $index - 1; $i >= max(0, $index - 10); $i--) {
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_DOC_COMMENT) {
                $docComment = $tokens[$i][1];
                break;
            }
        }

        if (! $docComment) {
            $quality['issues'][] = 'No documentation found';

            return $quality;
        }

        // Check for description
        if (preg_match('/\/\*\*\s*\n\s*\*\s*([^@\n]+)/', $docComment, $matches)) {
            $quality['score'] += 30;
        } else {
            $quality['issues'][] = 'Missing description';
        }

        // Check for @param tags
        $paramCount = preg_match_all('/@param\s+\w+\s+\$\w+/', $docComment);
        if ($paramCount > 0) {
            $quality['score'] += 20;
        }

        // Check for @return tag
        if (strpos($docComment, '@return') !== false) {
            $quality['score'] += 20;
        }

        // Check for @throws tag
        if (strpos($docComment, '@throws') !== false) {
            $quality['score'] += 10;
        }

        // Check for @author tag
        if (strpos($docComment, '@author') !== false) {
            $quality['score'] += 10;
        }

        // Check for @since tag
        if (strpos($docComment, '@since') !== false) {
            $quality['score'] += 10;
        }

        return $quality;
    }

    private function calculateDocumentationCoverage(array $tokens): array
    {
        $classes = $this->checkClasses($tokens);
        $methods = $this->checkMethods($tokens);
        $properties = $this->checkProperties($tokens);

        $totalElements = count($classes) + count($methods) + count($properties);
        $documentedElements = 0;

        foreach ($classes as $class) {
            if ($class['has_documentation']) {
                $documentedElements++;
            }
        }

        foreach ($methods as $method) {
            if ($method['has_documentation']) {
                $documentedElements++;
            }
        }

        foreach ($properties as $property) {
            if ($property['has_documentation']) {
                $documentedElements++;
            }
        }

        $coverage = $totalElements > 0 ? ($documentedElements / $totalElements) * 100 : 0;

        return [
            'total_elements' => $totalElements,
            'documented_elements' => $documentedElements,
            'coverage_percentage' => round($coverage, 2),
            'breakdown' => [
                'classes' => [
                    'total' => count($classes),
                    'documented' => count(array_filter($classes, fn ($c) => $c['has_documentation'])),
                ],
                'methods' => [
                    'total' => count($methods),
                    'documented' => count(array_filter($methods, fn ($m) => $m['has_documentation'])),
                ],
                'properties' => [
                    'total' => count($properties),
                    'documented' => count(array_filter($properties, fn ($p) => $p['has_documentation'])),
                ],
            ],
        ];
    }

    public function checkDirectory(string $directory): array
    {
        $results = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $results[] = $this->checkFile($file->getPathname());
            }
        }

        return $this->generateDocumentationReport($results);
    }

    private function generateDocumentationReport(array $results): array
    {
        $totalCoverage = 0;
        $totalFiles = 0;
        $overallStats = [
            'classes' => ['total' => 0, 'documented' => 0],
            'methods' => ['total' => 0, 'documented' => 0],
            'properties' => ['total' => 0, 'documented' => 0],
        ];

        foreach ($results as $result) {
            if (! isset($result['error'])) {
                $totalCoverage += $result['documentation_coverage']['coverage_percentage'];
                $totalFiles++;

                foreach (['classes', 'methods', 'properties'] as $type) {
                    $overallStats[$type]['total'] += $result['documentation_coverage']['breakdown'][$type]['total'];
                    $overallStats[$type]['documented'] += $result['documentation_coverage']['breakdown'][$type]['documented'];
                }
            }
        }

        $averageCoverage = $totalFiles > 0 ? $totalCoverage / $totalFiles : 0;

        return [
            'total_files_checked' => $totalFiles,
            'average_coverage' => round($averageCoverage, 2),
            'overall_statistics' => $overallStats,
            'files' => $results,
        ];
    }

    public function generateDocumentationTemplate(string $elementType, array $elementInfo): string
    {
        switch ($elementType) {
            case 'class':
                return "/**\n * Class description\n *\n * @author Your Name\n * @since 1.0.0\n */";

            case 'method':
                $template = "/**\n * Method description\n *\n";

                if (! empty($elementInfo['parameters'])) {
                    foreach ($elementInfo['parameters'] as $param) {
                        $template .= " * @param mixed {$param} Parameter description\n";
                    }
                }

                $template .= " * @return mixed Return description\n";
                $template .= " * @throws Exception When error occurs\n */";

                return $template;

            case 'property':
                return "/**\n * Property description\n *\n * @var mixed\n */";

            default:
                return "/**\n * Description\n */";
        }
    }
}
