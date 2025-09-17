<?php

/**
 * Code Coverage Analyzer Tool
 * Analyzes test coverage for PHP code
 */

class CodeCoverageAnalyzer
{
    private array $coverageData = [];
    private array $files = [];

    public function startCoverage(): void
    {
        if (extension_loaded('xdebug')) {
            xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
        }
    }

    public function stopCoverage(): array
    {
        if (extension_loaded('xdebug')) {
            $this->coverageData = xdebug_get_code_coverage();
            xdebug_stop_code_coverage();
        }

        return $this->analyzeCoverage();
    }

    private function analyzeCoverage(): array
    {
        $analysis = [
            'total_files' => 0,
            'covered_files' => 0,
            'total_lines' => 0,
            'covered_lines' => 0,
            'coverage_percentage' => 0,
            'files' => []
        ];

        foreach ($this->coverageData as $file => $lines) {
            $totalLines = count($lines);
            $coveredLines = count(array_filter($lines, function ($line) {
                return $line > 0;
            }));

            $fileAnalysis = [
                'file' => $file,
                'total_lines' => $totalLines,
                'covered_lines' => $coveredLines,
                'coverage_percentage' => $totalLines > 0 ? ($coveredLines / $totalLines) * 100 : 0,
                'uncovered_lines' => array_keys(array_filter($lines, function ($line) {
                    return $line === -1;
                }))
            ];

            $analysis['files'][] = $fileAnalysis;
            $analysis['total_files']++;
            $analysis['total_lines'] += $totalLines;
            $analysis['covered_lines'] += $coveredLines;

            if ($coveredLines > 0) {
                $analysis['covered_files']++;
            }
        }

        $analysis['coverage_percentage'] = $analysis['total_lines'] > 0
            ? ($analysis['covered_lines'] / $analysis['total_lines']) * 100
            : 0;

        return $analysis;
    }

    public function generateCoverageReport(): array
    {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'coverage_data' => $this->coverageData,
            'analysis' => $this->analyzeCoverage()
        ];
    }

    public function getCoverageForFile(string $filePath): ?array
    {
        foreach ($this->coverageData as $file => $lines) {
            if (str_contains($file, $filePath)) {
                return [
                    'file' => $file,
                    'lines' => $lines,
                    'coverage' => $this->calculateFileCoverage($lines)
                ];
            }
        }

        return null;
    }

    private function calculateFileCoverage(array $lines): float
    {
        $totalLines = count($lines);
        $coveredLines = count(array_filter($lines, function ($line) {
            return $line > 0;
        }));

        return $totalLines > 0 ? ($coveredLines / $totalLines) * 100 : 0;
    }

    public function exportCoverageToHTML(string $outputPath): bool
    {
        $analysis = $this->analyzeCoverage();
        $html = $this->generateHTMLReport($analysis);

        return file_put_contents($outputPath, $html) !== false;
    }

    private function generateHTMLReport(array $analysis): string
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Code Coverage Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .summary { background: #f0f0f0; padding: 15px; margin-bottom: 20px; }
        .file { margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; }
        .high-coverage { background-color: #d4edda; }
        .medium-coverage { background-color: #fff3cd; }
        .low-coverage { background-color: #f8d7da; }
    </style>
</head>
<body>
    <h1>Code Coverage Report</h1>
    <div class="summary">
        <h2>Summary</h2>
        <p>Total Files: ' . $analysis['total_files'] . '</p>
        <p>Covered Files: ' . $analysis['covered_files'] . '</p>
        <p>Total Lines: ' . $analysis['total_lines'] . '</p>
        <p>Covered Lines: ' . $analysis['covered_lines'] . '</p>
        <p>Coverage: ' . number_format($analysis['coverage_percentage'], 2) . '%</p>
    </div>
    <h2>File Details</h2>';

        foreach ($analysis['files'] as $file) {
            $coverageClass = $file['coverage_percentage'] >= 80 ? 'high-coverage' : ($file['coverage_percentage'] >= 60 ? 'medium-coverage' : 'low-coverage');

            $html .= '<div class="file ' . $coverageClass . '">
                <h3>' . basename($file['file']) . '</h3>
                <p>Coverage: ' . number_format($file['coverage_percentage'], 2) . '%</p>
                <p>Lines: ' . $file['covered_lines'] . '/' . $file['total_lines'] . '</p>
            </div>';
        }

        $html .= '</body></html>';

        return $html;
    }
}
