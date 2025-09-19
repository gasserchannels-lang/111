<?php

/**
 * Code Performance Analyzer Tool
 */
class CodePerformanceAnalyzer
{
    public function analyzePerformance(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $tokens = token_get_all($content);

        return [
            'file' => $filePath,
            'performance_issues' => $this->detectPerformanceIssues($tokens),
            'optimization_suggestions' => $this->generateOptimizationSuggestions($tokens),
        ];
    }

    private function detectPerformanceIssues(array $tokens): array
    {
        $issues = [];

        foreach ($tokens as $i => $token) {
            if (is_array($token)) {
                // Check for potential performance issues
                if ($token[0] === T_STRING) {
                    // Check for inefficient functions
                    if (in_array($token[1], ['array_merge', 'in_array', 'array_search'])) {
                        $issues[] = [
                            'type' => 'inefficient_function',
                            'function' => $token[1],
                            'line' => $token[2],
                            'suggestion' => $this->getFunctionSuggestion($token[1]),
                        ];
                    }
                }
            }
        }

        return $issues;
    }

    private function getFunctionSuggestion(string $function): string
    {
        $suggestions = [
            'array_merge' => 'Consider using array_push or the + operator for better performance',
            'in_array' => 'Consider using isset() with array_flip() for large arrays',
            'array_search' => 'Consider using array_keys() or array_flip() for better performance',
        ];

        return $suggestions[$function] ?? 'Consider optimizing this function call';
    }

    private function generateOptimizationSuggestions(array $tokens): array
    {
        return [
            'Use caching for expensive operations',
            'Consider lazy loading for large datasets',
            'Optimize database queries',
            'Use appropriate data structures',
        ];
    }
}
