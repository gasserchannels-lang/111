<?php

/**
 * Code Style Checker Tool
 * Checks PHP code against coding standards
 */

class CodeStyleChecker
{
    private array $rules = [];
    private array $violations = [];

    public function __construct()
    {
        $this->initializeRules();
    }

    private function initializeRules(): void
    {
        $this->rules = [
            'line_length' => ['max' => 120],
            'indentation' => ['type' => 'spaces', 'size' => 4],
            'brace_style' => 'allman', // or 'k&r'
            'naming_convention' => [
                'class' => 'PascalCase',
                'method' => 'camelCase',
                'variable' => 'camelCase',
                'constant' => 'UPPER_CASE'
            ]
        ];
    }

    public function checkFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return ['error' => 'File not found'];
        }

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $violations = [];

        foreach ($lines as $lineNumber => $line) {
            $lineViolations = $this->checkLine($line, $lineNumber + 1);
            $violations = array_merge($violations, $lineViolations);
        }

        $tokenViolations = $this->checkTokens($content);
        $violations = array_merge($violations, $tokenViolations);

        return [
            'file' => $filePath,
            'violations' => $violations,
            'total_violations' => count($violations)
        ];
    }

    private function checkLine(string $line, int $lineNumber): array
    {
        $violations = [];

        // Check line length
        if (strlen($line) > $this->rules['line_length']['max']) {
            $violations[] = [
                'line' => $lineNumber,
                'type' => 'line_length',
                'message' => 'Line exceeds maximum length of ' . $this->rules['line_length']['max'] . ' characters',
                'severity' => 'warning'
            ];
        }

        // Check indentation
        if (preg_match('/^\t/', $line) && $this->rules['indentation']['type'] === 'spaces') {
            $violations[] = [
                'line' => $lineNumber,
                'type' => 'indentation',
                'message' => 'Use spaces for indentation, not tabs',
                'severity' => 'error'
            ];
        }

        // Check trailing whitespace
        if (preg_match('/\s+$/', $line)) {
            $violations[] = [
                'line' => $lineNumber,
                'type' => 'trailing_whitespace',
                'message' => 'Trailing whitespace found',
                'severity' => 'warning'
            ];
        }

        return $violations;
    }

    private function checkTokens(string $content): array
    {
        $violations = [];
        $tokens = token_get_all($content);

        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];

            if (is_array($token)) {
                $violations = array_merge($violations, $this->checkToken($token, $i, $tokens));
            }
        }

        return $violations;
    }

    private function checkToken(array $token, int $index, array $allTokens): array
    {
        $violations = [];
        $tokenType = $token[0];
        $tokenValue = $token[1];
        $lineNumber = $token[2];

        // Check class naming convention
        if ($tokenType === T_CLASS && isset($allTokens[$index + 2])) {
            $className = $allTokens[$index + 2][1];
            if (!$this->isPascalCase($className)) {
                $violations[] = [
                    'line' => $lineNumber,
                    'type' => 'naming_convention',
                    'message' => 'Class name should be in PascalCase',
                    'severity' => 'error'
                ];
            }
        }

        // Check method naming convention
        if ($tokenType === T_FUNCTION && isset($allTokens[$index + 2])) {
            $methodName = $allTokens[$index + 2][1];
            if (!$this->isCamelCase($methodName) && !$this->isSnakeCase($methodName)) {
                $violations[] = [
                    'line' => $lineNumber,
                    'type' => 'naming_convention',
                    'message' => 'Method name should be in camelCase',
                    'severity' => 'error'
                ];
            }
        }

        // Check variable naming convention
        if ($tokenType === T_VARIABLE) {
            $variableName = substr($tokenValue, 1); // Remove $
            if (!$this->isCamelCase($variableName) && !$this->isSnakeCase($variableName)) {
                $violations[] = [
                    'line' => $lineNumber,
                    'type' => 'naming_convention',
                    'message' => 'Variable name should be in camelCase or snake_case',
                    'severity' => 'warning'
                ];
            }
        }

        return $violations;
    }

    private function isPascalCase(string $string): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $string);
    }

    private function isCamelCase(string $string): bool
    {
        return preg_match('/^[a-z][a-zA-Z0-9]*$/', $string);
    }

    private function isSnakeCase(string $string): bool
    {
        return preg_match('/^[a-z][a-z0-9_]*$/', $string);
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

        return $this->generateStyleReport($results);
    }

    private function generateStyleReport(array $results): array
    {
        $totalViolations = 0;
        $severityCounts = ['error' => 0, 'warning' => 0];
        $violationTypes = [];

        foreach ($results as $result) {
            if (!isset($result['error'])) {
                $totalViolations += $result['total_violations'];

                foreach ($result['violations'] as $violation) {
                    $severityCounts[$violation['severity']]++;

                    if (!isset($violationTypes[$violation['type']])) {
                        $violationTypes[$violation['type']] = 0;
                    }
                    $violationTypes[$violation['type']]++;
                }
            }
        }

        return [
            'total_files_checked' => count($results),
            'total_violations' => $totalViolations,
            'severity_breakdown' => $severityCounts,
            'violation_types' => $violationTypes,
            'files' => $results
        ];
    }

    public function fixFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $fixedLines = [];
        $fixCount = 0;

        foreach ($lines as $line) {
            $fixedLine = $line;

            // Fix trailing whitespace
            if (preg_match('/\s+$/', $line)) {
                $fixedLine = rtrim($line);
                $fixCount++;
            }

            // Convert tabs to spaces
            if ($this->rules['indentation']['type'] === 'spaces') {
                $tabsReplaced = substr_count($fixedLine, "\t");
                if ($tabsReplaced > 0) {
                    $fixedLine = str_replace("\t", str_repeat(' ', $this->rules['indentation']['size']), $fixedLine);
                    $fixCount += $tabsReplaced;
                }
            }

            $fixedLines[] = $fixedLine;
        }

        $fixedContent = implode("\n", $fixedLines);
        $success = file_put_contents($filePath, $fixedContent);

        return [
            'file' => $filePath,
            'fixes_applied' => $fixCount,
            'success' => $success !== false
        ];
    }

    public function setRules(array $rules): void
    {
        $this->rules = array_merge($this->rules, $rules);
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
