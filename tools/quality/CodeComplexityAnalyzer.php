<?php

namespace Tools\Quality;

class CodeComplexityAnalyzer
{
    private array $complexityMetrics = [];
    private array $thresholds = [
        'cyclomatic' => 10,
        'cognitive' => 15,
        'maintainability' => 70,
        'technical_debt' => 30
    ];

    public function analyzeFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $tokens = token_get_all($content);

        $metrics = [
            'file_path' => $filePath,
            'cyclomatic_complexity' => $this->calculateCyclomaticComplexity($tokens),
            'cognitive_complexity' => $this->calculateCognitiveComplexity($tokens),
            'maintainability_index' => $this->calculateMaintainabilityIndex($tokens),
            'technical_debt' => $this->calculateTechnicalDebt($tokens),
            'lines_of_code' => $this->countLinesOfCode($content),
            'comment_density' => $this->calculateCommentDensity($content),
            'duplication_percentage' => $this->calculateDuplicationPercentage($content),
            'analysis_date' => date('Y-m-d H:i:s')
        ];

        $this->complexityMetrics[$filePath] = $metrics;

        return $metrics;
    }

    public function analyzeDirectory(string $directory): array
    {
        $results = [];
        $files = $this->getPhpFiles($directory);

        foreach ($files as $file) {
            $results[$file] = $this->analyzeFile($file);
        }

        return [
            'directory' => $directory,
            'total_files' => count($files),
            'average_complexity' => $this->calculateAverageComplexity($results),
            'complexity_distribution' => $this->getComplexityDistribution($results),
            'files_above_threshold' => $this->getFilesAboveThreshold($results),
            'recommendations' => $this->generateRecommendations($results),
            'analysis_date' => date('Y-m-d H:i:s')
        ];
    }

    public function generateReport(array $results): string
    {
        $report = "# Code Complexity Analysis Report\n\n";
        $report .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";

        $report .= "## Summary\n";
        $report .= "- Total files analyzed: " . count($results) . "\n";
        $report .= "- Average cyclomatic complexity: " . $this->calculateAverageComplexity($results) . "\n";
        $report .= "- Files above threshold: " . count($this->getFilesAboveThreshold($results)) . "\n\n";

        $report .= "## Detailed Results\n";
        foreach ($results as $file => $metrics) {
            $report .= "### " . $file . "\n";
            $report .= "- Cyclomatic Complexity: " . $metrics['cyclomatic_complexity'] . "\n";
            $report .= "- Cognitive Complexity: " . $metrics['cognitive_complexity'] . "\n";
            $report .= "- Maintainability Index: " . $metrics['maintainability_index'] . "\n";
            $report .= "- Technical Debt: " . $metrics['technical_debt'] . " minutes\n";
            $report .= "- Lines of Code: " . $metrics['lines_of_code'] . "\n";
            $report .= "- Comment Density: " . $metrics['comment_density'] . "%\n\n";
        }

        return $report;
    }

    private function calculateCyclomaticComplexity(array $tokens): int
    {
        $complexity = 1; // Base complexity

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $tokenType = $token[0];
                $tokenValue = $token[1];

                // Increase complexity for decision points
                if (in_array($tokenType, [T_IF, T_ELSEIF, T_WHILE, T_FOR, T_FOREACH, T_CASE, T_CATCH, T_AND, T_OR, T_QUESTION])) {
                    $complexity++;
                }

                // Increase complexity for logical operators
                if (in_array($tokenValue, ['&&', '||', '?', ':', 'and', 'or'])) {
                    $complexity++;
                }
            }
        }

        return $complexity;
    }

    private function calculateCognitiveComplexity(array $tokens): int
    {
        $complexity = 0;
        $nestingLevel = 0;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $tokenType = $token[0];
                $tokenValue = $token[1];

                // Increase complexity for control structures
                if (in_array($tokenType, [T_IF, T_ELSEIF, T_WHILE, T_FOR, T_FOREACH, T_CASE, T_CATCH])) {
                    $complexity += 1 + $nestingLevel;
                    $nestingLevel++;
                }

                // Increase complexity for logical operators
                if (in_array($tokenValue, ['&&', '||', 'and', 'or'])) {
                    $complexity++;
                }

                // Decrease nesting level for closing braces
                if ($tokenType === T_CLOSE_CURLY_BRACE) {
                    $nestingLevel = max(0, $nestingLevel - 1);
                }
            }
        }

        return $complexity;
    }

    private function calculateMaintainabilityIndex(array $tokens): float
    {
        $halsteadVolume = $this->calculateHalsteadVolume($tokens);
        $cyclomaticComplexity = $this->calculateCyclomaticComplexity($tokens);
        $linesOfCode = $this->countLinesOfCode(implode('', array_column($tokens, 1)));

        // Simplified maintainability index calculation
        $maintainabilityIndex = 171 - 5.2 * log($halsteadVolume) - 0.23 * $cyclomaticComplexity - 16.2 * log($linesOfCode);

        return max(0, min(100, $maintainabilityIndex));
    }

    private function calculateTechnicalDebt(array $tokens): float
    {
        $cyclomaticComplexity = $this->calculateCyclomaticComplexity($tokens);
        $cognitiveComplexity = $this->calculateCognitiveComplexity($tokens);
        $linesOfCode = $this->countLinesOfCode(implode('', array_column($tokens, 1)));

        // Technical debt in minutes (simplified calculation)
        $debt = ($cyclomaticComplexity - $this->thresholds['cyclomatic']) * 5;
        $debt += ($cognitiveComplexity - $this->thresholds['cognitive']) * 3;
        $debt += max(0, $linesOfCode - 100) * 0.1;

        return max(0, $debt);
    }

    private function calculateHalsteadVolume(array $tokens): float
    {
        $operators = [];
        $operands = [];

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $tokenType = $token[0];
                $tokenValue = $token[1];

                if (in_array($tokenType, [T_PLUS, T_MINUS, T_MUL, T_DIV, T_MOD, T_AND, T_OR, T_XOR, T_NOT, T_IS_EQUAL, T_IS_NOT_EQUAL, T_IS_SMALLER_OR_EQUAL, T_IS_GREATER_OR_EQUAL, T_IS_SMALLER, T_IS_GREATER, T_BOOLEAN_AND, T_BOOLEAN_OR, T_BOOLEAN_XOR, T_LOGICAL_AND, T_LOGICAL_OR, T_LOGICAL_XOR, T_LOGICAL_NOT, T_SL, T_SR, T_INC, T_DEC, T_CONCAT, T_ASSIGN, T_PLUS_EQUAL, T_MINUS_EQUAL, T_MUL_EQUAL, T_DIV_EQUAL, T_MOD_EQUAL, T_AND_EQUAL, T_OR_EQUAL, T_XOR_EQUAL, T_SL_EQUAL, T_SR_EQUAL, T_CONCAT_EQUAL])) {
                    $operators[$tokenValue] = ($operators[$tokenValue] ?? 0) + 1;
                } elseif (in_array($tokenType, [T_VARIABLE, T_STRING, T_CONSTANT_ENCAPSED_STRING, T_LNUMBER, T_DNUMBER])) {
                    $operands[$tokenValue] = ($operands[$tokenValue] ?? 0) + 1;
                }
            }
        }

        $n1 = count($operators); // Number of distinct operators
        $n2 = count($operands); // Number of distinct operands
        $N1 = array_sum($operators); // Total number of operators
        $N2 = array_sum($operands); // Total number of operands

        $N = $N1 + $N2; // Program length
        $n = $n1 + $n2; // Vocabulary size

        return $N * log2($n);
    }

    private function countLinesOfCode(string $content): int
    {
        $lines = explode("\n", $content);
        $codeLines = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && !preg_match('/^\s*\/\//', $line) && !preg_match('/^\s*\/\*/', $line) && !preg_match('/^\s*\*/', $line)) {
                $codeLines++;
            }
        }

        return $codeLines;
    }

    private function calculateCommentDensity(string $content): float
    {
        $lines = explode("\n", $content);
        $totalLines = count($lines);
        $commentLines = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^\s*\/\//', $line) || preg_match('/^\s*\/\*/', $line) || preg_match('/^\s*\*/', $line)) {
                $commentLines++;
            }
        }

        return $totalLines > 0 ? ($commentLines / $totalLines) * 100 : 0;
    }

    private function calculateDuplicationPercentage(string $content): float
    {
        // Simplified duplication detection
        $lines = explode("\n", $content);
        $lineCounts = array_count_values($lines);
        $duplicatedLines = 0;

        foreach ($lineCounts as $count) {
            if ($count > 1) {
                $duplicatedLines += $count - 1;
            }
        }

        return count($lines) > 0 ? ($duplicatedLines / count($lines)) * 100 : 0;
    }

    private function getPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function calculateAverageComplexity(array $results): float
    {
        if (empty($results)) {
            return 0;
        }

        $totalComplexity = 0;
        foreach ($results as $metrics) {
            $totalComplexity += $metrics['cyclomatic_complexity'];
        }

        return $totalComplexity / count($results);
    }

    private function getComplexityDistribution(array $results): array
    {
        $distribution = [
            'low' => 0,      // 1-5
            'medium' => 0,   // 6-10
            'high' => 0,     // 11-20
            'very_high' => 0 // 21+
        ];

        foreach ($results as $metrics) {
            $complexity = $metrics['cyclomatic_complexity'];
            if ($complexity <= 5) {
                $distribution['low']++;
            } elseif ($complexity <= 10) {
                $distribution['medium']++;
            } elseif ($complexity <= 20) {
                $distribution['high']++;
            } else {
                $distribution['very_high']++;
            }
        }

        return $distribution;
    }

    private function getFilesAboveThreshold(array $results): array
    {
        $filesAboveThreshold = [];

        foreach ($results as $file => $metrics) {
            if (
                $metrics['cyclomatic_complexity'] > $this->thresholds['cyclomatic'] ||
                $metrics['cognitive_complexity'] > $this->thresholds['cognitive'] ||
                $metrics['maintainability_index'] < $this->thresholds['maintainability']
            ) {
                $filesAboveThreshold[] = $file;
            }
        }

        return $filesAboveThreshold;
    }

    private function generateRecommendations(array $results): array
    {
        $recommendations = [];

        foreach ($results as $file => $metrics) {
            $fileRecommendations = [];

            if ($metrics['cyclomatic_complexity'] > $this->thresholds['cyclomatic']) {
                $fileRecommendations[] = 'Consider breaking down complex functions into smaller, more manageable pieces';
            }

            if ($metrics['cognitive_complexity'] > $this->thresholds['cognitive']) {
                $fileRecommendations[] = 'Simplify nested conditions and reduce cognitive load';
            }

            if ($metrics['maintainability_index'] < $this->thresholds['maintainability']) {
                $fileRecommendations[] = 'Improve code structure and add more comments';
            }

            if ($metrics['comment_density'] < 20) {
                $fileRecommendations[] = 'Add more comments to improve code documentation';
            }

            if ($metrics['duplication_percentage'] > 10) {
                $fileRecommendations[] = 'Refactor duplicated code into reusable functions';
            }

            if (!empty($fileRecommendations)) {
                $recommendations[$file] = $fileRecommendations;
            }
        }

        return $recommendations;
    }
}
