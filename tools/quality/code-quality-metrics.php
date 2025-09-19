<?php

/**
 * Code Quality Metrics Tool
 * Calculates various code quality metrics
 */
class CodeQualityMetrics
{
    private array $metrics = [];

    public function analyzeFile(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return ['error' => 'File not found'];
        }

        $content = file_get_contents($filePath);
        $tokens = token_get_all($content);

        return [
            'file' => $filePath,
            'lines_of_code' => $this->countLinesOfCode($content),
            'cyclomatic_complexity' => $this->calculateCyclomaticComplexity($tokens),
            'maintainability_index' => $this->calculateMaintainabilityIndex($content),
            'code_duplication' => $this->detectCodeDuplication($content),
            'method_count' => $this->countMethods($tokens),
            'class_count' => $this->countClasses($tokens),
            'comment_ratio' => $this->calculateCommentRatio($content),
        ];
    }

    private function countLinesOfCode(string $content): array
    {
        $lines = explode("\n", $content);
        $totalLines = count($lines);
        $codeLines = 0;
        $commentLines = 0;
        $blankLines = 0;

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            if (empty($trimmedLine)) {
                $blankLines++;
            } elseif (preg_match('/^\s*(\/\/|\/\*|\*|#)/', $trimmedLine)) {
                $commentLines++;
            } else {
                $codeLines++;
            }
        }

        return [
            'total' => $totalLines,
            'code' => $codeLines,
            'comments' => $commentLines,
            'blank' => $blankLines,
        ];
    }

    private function calculateCyclomaticComplexity(array $tokens): int
    {
        $complexity = 1; // Base complexity
        $complexityKeywords = [
            T_IF,
            T_ELSEIF,
            T_ELSE,
            T_SWITCH,
            T_CASE,
            T_DEFAULT,
            T_FOR,
            T_FOREACH,
            T_WHILE,
            T_DO,
            T_TRY,
            T_CATCH,
        ];

        foreach ($tokens as $token) {
            if (is_array($token) && in_array($token[0], $complexityKeywords)) {
                $complexity++;
            }
        }

        return $complexity;
    }

    private function calculateMaintainabilityIndex(string $content): float
    {
        $loc = $this->countLinesOfCode($content);
        $tokens = token_get_all($content);
        $complexity = $this->calculateCyclomaticComplexity($tokens);

        // Simplified maintainability index calculation
        $volume = $loc['code'] * log($loc['code'] + 1, 2);
        $maintainabilityIndex = max(0, (171 - 5.2 * log($volume) - 0.23 * $complexity - 16.2 * log($loc['code'])) * 100 / 171);

        return round($maintainabilityIndex, 2);
    }

    private function detectCodeDuplication(string $content): array
    {
        $lines = explode("\n", $content);
        $duplicates = [];
        $lineHashes = [];

        foreach ($lines as $lineNumber => $line) {
            $trimmedLine = trim($line);
            if (! empty($trimmedLine) && ! preg_match('/^\s*(\/\/|\/\*|\*)/', $trimmedLine)) {
                $hash = md5($trimmedLine);

                if (isset($lineHashes[$hash])) {
                    $duplicates[] = [
                        'line1' => $lineHashes[$hash],
                        'line2' => $lineNumber + 1,
                        'content' => $trimmedLine,
                    ];
                } else {
                    $lineHashes[$hash] = $lineNumber + 1;
                }
            }
        }

        return [
            'duplicated_lines' => count($duplicates),
            'duplicates' => $duplicates,
        ];
    }

    private function countMethods(array $tokens): int
    {
        $methodCount = 0;

        foreach ($tokens as $i => $token) {
            if (is_array($token) && $token[0] === T_FUNCTION) {
                $methodCount++;
            }
        }

        return $methodCount;
    }

    private function countClasses(array $tokens): int
    {
        $classCount = 0;

        foreach ($tokens as $token) {
            if (is_array($token) && $token[0] === T_CLASS) {
                $classCount++;
            }
        }

        return $classCount;
    }

    private function calculateCommentRatio(string $content): float
    {
        $loc = $this->countLinesOfCode($content);

        if ($loc['code'] + $loc['comments'] === 0) {
            return 0;
        }

        return round(($loc['comments'] / ($loc['code'] + $loc['comments'])) * 100, 2);
    }

    public function analyzeDirectory(string $directory): array
    {
        $results = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $results[] = $this->analyzeFile($file->getPathname());
            }
        }

        return $this->generateSummaryReport($results);
    }

    private function generateSummaryReport(array $results): array
    {
        $summary = [
            'total_files' => count($results),
            'total_lines' => 0,
            'total_code_lines' => 0,
            'total_comment_lines' => 0,
            'average_complexity' => 0,
            'average_maintainability' => 0,
            'total_methods' => 0,
            'total_classes' => 0,
            'files' => $results,
        ];

        $totalComplexity = 0;
        $totalMaintainability = 0;

        foreach ($results as $result) {
            if (! isset($result['error'])) {
                $summary['total_lines'] += $result['lines_of_code']['total'];
                $summary['total_code_lines'] += $result['lines_of_code']['code'];
                $summary['total_comment_lines'] += $result['lines_of_code']['comments'];
                $summary['total_methods'] += $result['method_count'];
                $summary['total_classes'] += $result['class_count'];
                $totalComplexity += $result['cyclomatic_complexity'];
                $totalMaintainability += $result['maintainability_index'];
            }
        }

        if (count($results) > 0) {
            $summary['average_complexity'] = round($totalComplexity / count($results), 2);
            $summary['average_maintainability'] = round($totalMaintainability / count($results), 2);
        }

        return $summary;
    }

    public function exportMetricsToCSV(array $metrics, string $outputPath): bool
    {
        $csvData = "File,Total Lines,Code Lines,Comment Lines,Cyclomatic Complexity,Maintainability Index,Methods,Classes,Comment Ratio\n";

        foreach ($metrics['files'] as $file) {
            if (! isset($file['error'])) {
                $csvData .= sprintf(
                    "%s,%d,%d,%d,%d,%.2f,%d,%d,%.2f\n",
                    $file['file'],
                    $file['lines_of_code']['total'],
                    $file['lines_of_code']['code'],
                    $file['lines_of_code']['comments'],
                    $file['cyclomatic_complexity'],
                    $file['maintainability_index'],
                    $file['method_count'],
                    $file['class_count'],
                    $file['comment_ratio']
                );
            }
        }

        return file_put_contents($outputPath, $csvData) !== false;
    }
}
