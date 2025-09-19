<?php

namespace Tools\Quality;

class SecurityScanner
{
    private array $vulnerabilities = [];

    private array $securityRules = [];

    private array $scanResults = [];

    public function __construct()
    {
        $this->initializeSecurityRules();
    }

    public function scanFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $tokens = token_get_all($content);

        $vulnerabilities = [];

        // Scan for SQL injection vulnerabilities
        $vulnerabilities = array_merge($vulnerabilities, $this->scanSQLInjection($tokens, $filePath));

        // Scan for XSS vulnerabilities
        $vulnerabilities = array_merge($vulnerabilities, $this->scanXSS($tokens, $filePath));

        // Scan for CSRF vulnerabilities
        $vulnerabilities = array_merge($vulnerabilities, $this->scanCSRF($tokens, $filePath));

        // Scan for authentication vulnerabilities
        $vulnerabilities = array_merge($vulnerabilities, $this->scanAuthentication($tokens, $filePath));

        // Scan for authorization vulnerabilities
        $vulnerabilities = array_merge($vulnerabilities, $this->scanAuthorization($tokens, $filePath));

        // Scan for file upload vulnerabilities
        $vulnerabilities = array_merge($vulnerabilities, $this->scanFileUpload($tokens, $filePath));

        // Scan for path traversal vulnerabilities
        $vulnerabilities = array_merge($vulnerabilities, $this->scanPathTraversal($tokens, $filePath));

        // Scan for command injection vulnerabilities
        $vulnerabilities = array_merge($vulnerabilities, $this->scanCommandInjection($tokens, $filePath));

        // Scan for insecure deserialization
        $vulnerabilities = array_merge($vulnerabilities, $this->scanInsecureDeserialization($tokens, $filePath));

        // Scan for weak cryptography
        $vulnerabilities = array_merge($vulnerabilities, $this->scanWeakCryptography($tokens, $filePath));

        // Scan for information disclosure
        $vulnerabilities = array_merge($vulnerabilities, $this->scanInformationDisclosure($tokens, $filePath));

        // Scan for insecure redirects
        $vulnerabilities = array_merge($vulnerabilities, $this->scanInsecureRedirects($tokens, $filePath));

        // Scan for session management issues
        $vulnerabilities = array_merge($vulnerabilities, $this->scanSessionManagement($tokens, $filePath));

        // Scan for input validation issues
        $vulnerabilities = array_merge($vulnerabilities, $this->scanInputValidation($tokens, $filePath));

        // Scan for error handling issues
        $vulnerabilities = array_merge($vulnerabilities, $this->scanErrorHandling($tokens, $filePath));

        // Scan for logging and monitoring issues
        $vulnerabilities = array_merge($vulnerabilities, $this->scanLoggingMonitoring($tokens, $filePath));

        $this->scanResults[$filePath] = $vulnerabilities;

        return [
            'file_path' => $filePath,
            'vulnerabilities' => $vulnerabilities,
            'vulnerability_count' => count($vulnerabilities),
            'severity_distribution' => $this->getSeverityDistribution($vulnerabilities),
            'scan_date' => date('Y-m-d H:i:s'),
        ];
    }

    public function scanDirectory(string $directory): array
    {
        $results = [];
        $files = $this->getPhpFiles($directory);

        foreach ($files as $file) {
            $results[$file] = $this->scanFile($file);
        }

        return [
            'directory' => $directory,
            'total_files' => count($files),
            'total_vulnerabilities' => array_sum(array_column($results, 'vulnerability_count')),
            'files_with_vulnerabilities' => count(array_filter($results, fn ($r) => $r['vulnerability_count'] > 0)),
            'severity_summary' => $this->getSeveritySummary($results),
            'vulnerability_types' => $this->getVulnerabilityTypes($results),
            'recommendations' => $this->generateSecurityRecommendations($results),
            'scan_date' => date('Y-m-d H:i:s'),
        ];
    }

    public function generateSecurityReport(array $results): string
    {
        $report = "# Security Scan Report\n\n";
        $report .= 'Generated on: '.date('Y-m-d H:i:s')."\n\n";

        $report .= "## Summary\n";
        $report .= '- Total files scanned: '.count($results)."\n";
        $report .= '- Total vulnerabilities found: '.array_sum(array_column($results, 'vulnerability_count'))."\n";
        $report .= '- Files with vulnerabilities: '.count(array_filter($results, fn ($r) => $r['vulnerability_count'] > 0))."\n\n";

        $report .= "## Vulnerability Types\n";
        $types = $this->getVulnerabilityTypes($results);
        foreach ($types as $type => $count) {
            $report .= "- {$type}: {$count}\n";
        }

        $report .= "\n## Detailed Results\n";
        foreach ($results as $file => $result) {
            if ($result['vulnerability_count'] > 0) {
                $report .= "### {$file}\n";
                $report .= "Vulnerabilities found: {$result['vulnerability_count']}\n\n";

                foreach ($result['vulnerabilities'] as $vulnerability) {
                    $report .= "#### {$vulnerability['type']} (Severity: {$vulnerability['severity']})\n";
                    $report .= "- Line: {$vulnerability['line']}\n";
                    $report .= "- Description: {$vulnerability['description']}\n";
                    $report .= "- Recommendation: {$vulnerability['recommendation']}\n\n";
                }
            }
        }

        return $report;
    }

    private function scanSQLInjection(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for direct SQL queries with user input
                if (preg_match('/SELECT|INSERT|UPDATE|DELETE/i', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'SQL Injection',
                        'severity' => 'high',
                        'line' => $line,
                        'description' => 'Potential SQL injection vulnerability detected',
                        'recommendation' => 'Use prepared statements or parameterized queries',
                        'file' => $filePath,
                    ];
                }

                // Check for concatenation with user input
                if (preg_match('/\$_(GET|POST|REQUEST|COOKIE|SESSION)/', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'SQL Injection',
                        'severity' => 'high',
                        'line' => $line,
                        'description' => 'User input used in SQL query without proper sanitization',
                        'recommendation' => 'Sanitize and validate user input before using in SQL queries',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanXSS(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for echo/print without escaping
                if (preg_match('/echo|print/i', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'XSS',
                        'severity' => 'medium',
                        'line' => $line,
                        'description' => 'Output without proper escaping detected',
                        'recommendation' => 'Use htmlspecialchars() or similar escaping functions',
                        'file' => $filePath,
                    ];
                }

                // Check for direct output of user input
                if (preg_match('/\$_(GET|POST|REQUEST|COOKIE|SESSION)/', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'XSS',
                        'severity' => 'high',
                        'line' => $line,
                        'description' => 'User input output without escaping',
                        'recommendation' => 'Always escape user input before output',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanCSRF(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for forms without CSRF tokens
                if (preg_match('/<form/i', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'CSRF',
                        'severity' => 'medium',
                        'line' => $line,
                        'description' => 'Form without CSRF protection detected',
                        'recommendation' => 'Implement CSRF tokens in all forms',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanAuthentication(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for weak password hashing
                if (preg_match('/md5|sha1/i', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'Weak Authentication',
                        'severity' => 'high',
                        'line' => $line,
                        'description' => 'Weak password hashing algorithm detected',
                        'recommendation' => 'Use password_hash() with strong algorithms',
                        'file' => $filePath,
                    ];
                }

                // Check for hardcoded credentials
                if (preg_match('/password\s*=\s*[\'"]/', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'Hardcoded Credentials',
                        'severity' => 'critical',
                        'line' => $line,
                        'description' => 'Hardcoded password detected',
                        'recommendation' => 'Use environment variables for sensitive data',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanAuthorization(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for missing authorization checks
                if (preg_match('/admin|user|role/i', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'Authorization',
                        'severity' => 'medium',
                        'line' => $line,
                        'description' => 'Potential authorization bypass',
                        'recommendation' => 'Implement proper authorization checks',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanFileUpload(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for file upload without validation
                if (preg_match('/\$_FILES/i', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'File Upload',
                        'severity' => 'high',
                        'line' => $line,
                        'description' => 'File upload without proper validation',
                        'recommendation' => 'Validate file types, size, and content',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanPathTraversal(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for path traversal patterns
                if (preg_match('/\.\.\//', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'Path Traversal',
                        'severity' => 'high',
                        'line' => $line,
                        'description' => 'Potential path traversal vulnerability',
                        'recommendation' => 'Validate and sanitize file paths',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanCommandInjection(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for command execution functions
                if (preg_match('/exec|system|shell_exec|passthru|eval/i', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'Command Injection',
                        'severity' => 'critical',
                        'line' => $line,
                        'description' => 'Dangerous command execution function detected',
                        'recommendation' => 'Avoid using dangerous functions or sanitize input',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanInsecureDeserialization(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for unserialize function
                if (preg_match('/unserialize/i', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'Insecure Deserialization',
                        'severity' => 'high',
                        'line' => $line,
                        'description' => 'Insecure deserialization detected',
                        'recommendation' => 'Use safe deserialization methods or validate data',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanWeakCryptography(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for weak encryption
                if (preg_match('/mcrypt|DES|RC4/i', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'Weak Cryptography',
                        'severity' => 'high',
                        'line' => $line,
                        'description' => 'Weak encryption algorithm detected',
                        'recommendation' => 'Use strong encryption algorithms like AES-256',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanInformationDisclosure(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for error disclosure
                if (preg_match('/error_reporting|display_errors|ini_set/i', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'Information Disclosure',
                        'severity' => 'medium',
                        'line' => $line,
                        'description' => 'Potential error information disclosure',
                        'recommendation' => 'Disable error display in production',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanInsecureRedirects(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for redirects without validation
                if (preg_match('/header.*Location/i', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'Insecure Redirect',
                        'severity' => 'medium',
                        'line' => $line,
                        'description' => 'Redirect without validation detected',
                        'recommendation' => 'Validate redirect URLs before redirecting',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanSessionManagement(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for session security issues
                if (preg_match('/session_start|session_regenerate_id/i', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'Session Management',
                        'severity' => 'medium',
                        'line' => $line,
                        'description' => 'Session management without security measures',
                        'recommendation' => 'Implement secure session management',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanInputValidation(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for missing input validation
                if (preg_match('/\$_(GET|POST|REQUEST|COOKIE|SESSION)/', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'Input Validation',
                        'severity' => 'medium',
                        'line' => $line,
                        'description' => 'User input without validation',
                        'recommendation' => 'Implement proper input validation',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanErrorHandling(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for error handling issues
                if (preg_match('/try|catch|throw/i', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'Error Handling',
                        'severity' => 'low',
                        'line' => $line,
                        'description' => 'Error handling without proper logging',
                        'recommendation' => 'Implement proper error logging and handling',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function scanLoggingMonitoring(array $tokens, string $filePath): array
    {
        $vulnerabilities = [];
        $line = 1;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $line = $token[2];
                $tokenValue = $token[1];

                // Check for missing logging
                if (preg_match('/login|logout|admin|sensitive/i', $tokenValue)) {
                    $vulnerabilities[] = [
                        'type' => 'Logging & Monitoring',
                        'severity' => 'low',
                        'line' => $line,
                        'description' => 'Sensitive operation without logging',
                        'recommendation' => 'Implement comprehensive logging and monitoring',
                        'file' => $filePath,
                    ];
                }
            }
        }

        return $vulnerabilities;
    }

    private function initializeSecurityRules(): void
    {
        $this->securityRules = [
            'sql_injection' => [
                'patterns' => ['SELECT.*\$', 'INSERT.*\$', 'UPDATE.*\$', 'DELETE.*\$'],
                'severity' => 'high',
            ],
            'xss' => [
                'patterns' => ['echo.*\$', 'print.*\$'],
                'severity' => 'medium',
            ],
            'csrf' => [
                'patterns' => ['<form', 'POST.*\$'],
                'severity' => 'medium',
            ],
            'authentication' => [
                'patterns' => ['md5', 'sha1', 'password.*='],
                'severity' => 'high',
            ],
            'authorization' => [
                'patterns' => ['admin', 'user', 'role'],
                'severity' => 'medium',
            ],
            'file_upload' => [
                'patterns' => ['\$_FILES'],
                'severity' => 'high',
            ],
            'path_traversal' => [
                'patterns' => ['\.\.\/'],
                'severity' => 'high',
            ],
            'command_injection' => [
                'patterns' => ['exec', 'system', 'shell_exec', 'passthru', 'eval'],
                'severity' => 'critical',
            ],
            'insecure_deserialization' => [
                'patterns' => ['unserialize'],
                'severity' => 'high',
            ],
            'weak_cryptography' => [
                'patterns' => ['mcrypt', 'DES', 'RC4'],
                'severity' => 'high',
            ],
            'information_disclosure' => [
                'patterns' => ['error_reporting', 'display_errors', 'ini_set'],
                'severity' => 'medium',
            ],
            'insecure_redirect' => [
                'patterns' => ['header.*Location'],
                'severity' => 'medium',
            ],
            'session_management' => [
                'patterns' => ['session_start', 'session_regenerate_id'],
                'severity' => 'medium',
            ],
            'input_validation' => [
                'patterns' => ['\$_GET', '\$_POST', '\$_REQUEST', '\$_COOKIE', '\$_SESSION'],
                'severity' => 'medium',
            ],
            'error_handling' => [
                'patterns' => ['try', 'catch', 'throw'],
                'severity' => 'low',
            ],
            'logging_monitoring' => [
                'patterns' => ['login', 'logout', 'admin', 'sensitive'],
                'severity' => 'low',
            ],
        ];
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

    private function getSeverityDistribution(array $vulnerabilities): array
    {
        $distribution = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ];

        foreach ($vulnerabilities as $vulnerability) {
            $severity = strtolower($vulnerability['severity']);
            if (isset($distribution[$severity])) {
                $distribution[$severity]++;
            }
        }

        return $distribution;
    }

    private function getSeveritySummary(array $results): array
    {
        $summary = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ];

        foreach ($results as $result) {
            foreach ($result['vulnerabilities'] as $vulnerability) {
                $severity = strtolower($vulnerability['severity']);
                if (isset($summary[$severity])) {
                    $summary[$severity]++;
                }
            }
        }

        return $summary;
    }

    private function getVulnerabilityTypes(array $results): array
    {
        $types = [];

        foreach ($results as $result) {
            foreach ($result['vulnerabilities'] as $vulnerability) {
                $type = $vulnerability['type'];
                $types[$type] = ($types[$type] ?? 0) + 1;
            }
        }

        return $types;
    }

    private function generateSecurityRecommendations(array $results): array
    {
        $recommendations = [];

        $vulnerabilityTypes = $this->getVulnerabilityTypes($results);

        foreach ($vulnerabilityTypes as $type => $count) {
            switch ($type) {
                case 'SQL Injection':
                    $recommendations[] = 'Implement prepared statements for all database queries';
                    break;
                case 'XSS':
                    $recommendations[] = 'Use htmlspecialchars() or similar escaping functions for all output';
                    break;
                case 'CSRF':
                    $recommendations[] = 'Implement CSRF tokens in all forms';
                    break;
                case 'Weak Authentication':
                    $recommendations[] = 'Use password_hash() with strong algorithms for password hashing';
                    break;
                case 'File Upload':
                    $recommendations[] = 'Implement proper file validation and storage outside web root';
                    break;
                case 'Command Injection':
                    $recommendations[] = 'Avoid using dangerous functions or sanitize input properly';
                    break;
                case 'Insecure Deserialization':
                    $recommendations[] = 'Use safe deserialization methods or validate data';
                    break;
                case 'Weak Cryptography':
                    $recommendations[] = 'Use strong encryption algorithms like AES-256';
                    break;
                case 'Information Disclosure':
                    $recommendations[] = 'Disable error display in production environment';
                    break;
                case 'Insecure Redirect':
                    $recommendations[] = 'Validate redirect URLs before redirecting';
                    break;
                case 'Session Management':
                    $recommendations[] = 'Implement secure session management with proper configuration';
                    break;
                case 'Input Validation':
                    $recommendations[] = 'Implement comprehensive input validation and sanitization';
                    break;
                case 'Error Handling':
                    $recommendations[] = 'Implement proper error logging and handling';
                    break;
                case 'Logging & Monitoring':
                    $recommendations[] = 'Implement comprehensive logging and monitoring for security events';
                    break;
            }
        }

        return array_unique($recommendations);
    }
}
