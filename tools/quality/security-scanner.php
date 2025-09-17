<?php

/**
 * Security Scanner Tool
 * Scans code for security vulnerabilities and issues
 */

class SecurityScanner
{
    private array $vulnerabilities = [];
    private array $securityRules = [
        'sql_injection' => '/\$_[GET|POST|REQUEST].*sql|mysql_query|mysqli_query/i',
        'xss' => '/echo\s+\$_[GET|POST|REQUEST]|print\s+\$_[GET|POST|REQUEST]/i',
        'file_inclusion' => '/include\s+\$_[GET|POST|REQUEST]|require\s+\$_[GET|POST|REQUEST]/i',
        'command_injection' => '/exec\s+\$_[GET|POST|REQUEST]|system\s+\$_[GET|POST|REQUEST]/i',
        'weak_crypto' => '/md5\s*\(|sha1\s*\(/i'
    ];

    public function scanFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return ['error' => 'File not found'];
        }

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $vulnerabilities = [];

        foreach ($lines as $lineNumber => $line) {
            foreach ($this->securityRules as $vulnerability => $pattern) {
                if (preg_match($pattern, $line)) {
                    $vulnerabilities[] = [
                        'type' => $vulnerability,
                        'line' => $lineNumber + 1,
                        'code' => trim($line),
                        'severity' => $this->getSeverity($vulnerability)
                    ];
                }
            }
        }

        return [
            'file' => $filePath,
            'vulnerabilities' => $vulnerabilities,
            'total_vulnerabilities' => count($vulnerabilities)
        ];
    }

    public function scanDirectory(string $directory): array
    {
        $results = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $results[] = $this->scanFile($file->getPathname());
            }
        }

        return $results;
    }

    private function getSeverity(string $vulnerability): string
    {
        $severityMap = [
            'sql_injection' => 'critical',
            'xss' => 'high',
            'file_inclusion' => 'critical',
            'command_injection' => 'critical',
            'weak_crypto' => 'medium'
        ];

        return $severityMap[$vulnerability] ?? 'low';
    }

    public function generateSecurityReport(array $scanResults): array
    {
        $totalVulnerabilities = 0;
        $severityCounts = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];

        foreach ($scanResults as $result) {
            $totalVulnerabilities += $result['total_vulnerabilities'];

            foreach ($result['vulnerabilities'] as $vuln) {
                $severityCounts[$vuln['severity']]++;
            }
        }

        return [
            'total_files_scanned' => count($scanResults),
            'total_vulnerabilities' => $totalVulnerabilities,
            'severity_breakdown' => $severityCounts,
            'scan_results' => $scanResults
        ];
    }
}
