<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class FileSecurityService
{
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
        'pdf', 'doc', 'docx', 'txt', 'rtf',
        'xls', 'xlsx', 'csv',
        'zip', 'rar', '7z',
    ];

    private const DANGEROUS_EXTENSIONS = [
        'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js',
        'jar', 'php', 'asp', 'aspx', 'jsp', 'py', 'rb', 'pl',
        'sh', 'ps1', 'psm1', 'psd1', 'ps1xml', 'psc1', 'psc2',
    ];

    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

    // private const SCAN_RESULT_CACHE_PREFIX = 'file_scan:';

    /**
     * Scan uploaded file for security threats.
     */
    /**
     * @return array<string, mixed>
     */
    public function scanFile(UploadedFile $file): array
    {
        $results = [
            'is_safe' => true,
            'threats' => [],
            'warnings' => [],
            'file_info' => $this->getFileInfo($file),
        ];

        // Check file extension
        $extensionCheck = $this->checkFileExtension($file);
        if (! $extensionCheck['is_safe']) {
            $results['is_safe'] = false;
            $threats = is_array($extensionCheck['threats'] ?? null) ? $extensionCheck['threats'] : [];
            $results['threats'] = array_merge($results['threats'], $threats);
        }

        // Check file size
        $sizeCheck = $this->checkFileSize($file);
        if (! $sizeCheck['is_safe']) {
            $results['is_safe'] = false;
            $threats = is_array($sizeCheck['threats'] ?? null) ? $sizeCheck['threats'] : [];
            $results['threats'] = array_merge($results['threats'], $threats);
        }

        // Check file content
        $contentCheck = $this->checkFileContent($file);
        if (! $contentCheck['is_safe']) {
            $results['is_safe'] = false;
            $threats = is_array($contentCheck['threats'] ?? null) ? $contentCheck['threats'] : [];
            $results['threats'] = array_merge($results['threats'], $threats);
        }

        // Check for malware signatures
        $malwareCheck = $this->checkMalwareSignatures($file);
        if (! $malwareCheck['is_safe']) {
            $results['is_safe'] = false;
            $threats = is_array($malwareCheck['threats'] ?? null) ? $malwareCheck['threats'] : [];
            $results['threats'] = array_merge($results['threats'], $threats);
        }

        // Check file headers
        $headerCheck = $this->checkFileHeaders($file);
        if (! $headerCheck['is_safe']) {
            $results['is_safe'] = false;
            $threats = is_array($headerCheck['threats'] ?? null) ? $headerCheck['threats'] : [];
            $results['threats'] = array_merge($results['threats'], $threats);
        }

        // Log scan results
        $this->logScanResults($file, $results);

        return $results;
    }

    /**
     * Check file extension.
     */
    /**
     * @return array<string, mixed>
     */
    private function checkFileExtension(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $results = ['is_safe' => true, 'threats' => []];

        // Check for dangerous extensions
        if (in_array($extension, self::DANGEROUS_EXTENSIONS)) {
            $results['is_safe'] = false;
            $results['threats'][] = "File extension '{$extension}' is potentially dangerous";
        }

        // Check if extension is allowed
        if (! in_array($extension, self::ALLOWED_EXTENSIONS)) {
            $results['is_safe'] = false;
            $results['threats'][] = "File extension '{$extension}' is not allowed";
        }

        return $results;
    }

    /**
     * Check file size.
     */
    /**
     * @return array<string, mixed>
     */
    private function checkFileSize(UploadedFile $file): array
    {
        $results = ['is_safe' => true, 'threats' => []];

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            $results['is_safe'] = false;
            $results['threats'][] = "File size ({$file->getSize()} bytes) exceeds maximum allowed size (".self::MAX_FILE_SIZE.' bytes)';
        }

        return $results;
    }

    /**
     * Check file content.
     */
    /**
     * @return array<string, mixed>
     */
    private function checkFileContent(UploadedFile $file): array
    {
        $results = ['is_safe' => true, 'threats' => []];

        try {
            $content = file_get_contents($file->getPathname());

            // Check for suspicious patterns
            $suspiciousPatterns = [
                '/<script[^>]*>.*?<\/script>/is' => 'JavaScript code detected',
                '/<iframe[^>]*>.*?<\/iframe>/is' => 'Iframe detected',
                '/<object[^>]*>.*?<\/object>/is' => 'Object tag detected',
                '/<embed[^>]*>.*?<\/embed>/is' => 'Embed tag detected',
                '/eval\s*\(/i' => 'Eval function detected',
                '/exec\s*\(/i' => 'Exec function detected',
                '/system\s*\(/i' => 'System function detected',
                '/shell_exec\s*\(/i' => 'Shell exec function detected',
                '/passthru\s*\(/i' => 'Passthru function detected',
                '/proc_open\s*\(/i' => 'Proc open function detected',
            ];

            foreach ($suspiciousPatterns as $pattern => $description) {
                if ($content !== false && preg_match($pattern, $content)) {
                    $results['is_safe'] = false;
                    $results['threats'][] = $description;
                }
            }

            // Check for binary content in text files
            if ($this->isTextFile($file) && $content !== false && $this->containsBinaryContent($content)) {
                $results['is_safe'] = false;
                $results['threats'][] = 'Binary content detected in text file';
            }
        } catch (\Exception $e) {
            $results['is_safe'] = false;
            $results['threats'][] = 'Error reading file content: '.$e->getMessage();
        }

        return $results;
    }

    /**
     * Check for malware signatures.
     */
    /**
     * @return array<string, mixed>
     */
    private function checkMalwareSignatures(UploadedFile $file): array
    {
        $results = ['is_safe' => true, 'threats' => []];

        try {
            $content = file_get_contents($file->getPathname());

            // Simple malware signatures (in real implementation, use proper antivirus)
            $malwareSignatures = [
                'eval(base64_decode(' => 'Base64 encoded PHP code',
                'eval(gzinflate(' => 'Gzip compressed PHP code',
                'eval(gzuncompress(' => 'Gzip uncompressed PHP code',
                'eval(str_rot13(' => 'ROT13 encoded PHP code',
                'eval(hex2bin(' => 'Hex encoded PHP code',
                'eval(chr(' => 'Character encoded PHP code',
                'eval(ord(' => 'Character encoded PHP code',
                'eval(pack(' => 'Packed PHP code',
                'eval(unpack(' => 'Unpacked PHP code',
                'eval(convert_uudecode(' => 'UU encoded PHP code',
                'eval(convert_uuencode(' => 'UU encoded PHP code',
                'eval(quoted_printable_decode(' => 'Quoted printable decoded PHP code',
                'eval(quoted_printable_encode(' => 'Quoted printable encoded PHP code',
            ];

            foreach ($malwareSignatures as $signature => $description) {
                if ($content !== false && str_contains($content, $signature)) {
                    $results['is_safe'] = false;
                    $results['threats'][] = "Malware signature detected: {$description}";
                }
            }
        } catch (\Exception $e) {
            $results['is_safe'] = false;
            $results['threats'][] = 'Error scanning for malware: '.$e->getMessage();
        }

        return $results;
    }

    /**
     * Check file headers.
     */
    /**
     * @return array<string, mixed>
     */
    private function checkFileHeaders(UploadedFile $file): array
    {
        $results = ['is_safe' => true, 'threats' => []];

        try {
            $handle = fopen($file->getPathname(), 'rb');
            if (! $handle) {
                $results['is_safe'] = false;
                $results['threats'][] = 'Cannot open file for header analysis';

                return $results;
            }

            $header = fread($handle, 1024);
            fclose($handle);

            // Check for suspicious file headers
            $suspiciousHeaders = [
                'MZ' => 'Executable file detected',
                'PK' => 'Archive file detected',
                'Rar!' => 'RAR archive detected',
                '7z' => '7-Zip archive detected',
                '<?php' => 'PHP file detected',
                '<script' => 'JavaScript file detected',
                '<html' => 'HTML file detected',
                '<xml' => 'XML file detected',
            ];

            foreach ($suspiciousHeaders as $headerSignature => $description) {
                if ($header !== false && str_starts_with($header, $headerSignature)) {
                    $results['is_safe'] = false;
                    $results['threats'][] = $description;
                }
            }
        } catch (\Exception $e) {
            $results['is_safe'] = false;
            $results['threats'][] = 'Error checking file headers: '.$e->getMessage();
        }

        return $results;
    }

    /**
     * Get file information.
     */
    /**
     * @return array<string, mixed>
     */
    private function getFileInfo(UploadedFile $file): array
    {
        return [
            'original_name' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_at' => now()->toISOString(),
        ];
    }

    /**
     * Check if file is text file.
     */
    private function isTextFile(UploadedFile $file): bool
    {
        $textExtensions = ['txt', 'csv', 'json', 'xml', 'html', 'css', 'js', 'php', 'py', 'rb', 'pl'];

        return in_array(strtolower($file->getClientOriginalExtension()), $textExtensions);
    }

    /**
     * Check if content contains binary data.
     */
    private function containsBinaryContent(string $content): bool
    {
        $binaryThreshold = 0.3; // 30% binary characters
        $binaryCount = 0;
        $totalCount = strlen($content);

        for ($i = 0; $i < $totalCount; $i++) {
            $char = $content[$i];
            $ord = ord($char);

            // Check for null bytes and control characters
            if ($ord < 32 && $ord !== 9 && $ord !== 10 && $ord !== 13) {
                $binaryCount++;
            }
        }

        return ($binaryCount / $totalCount) > $binaryThreshold;
    }

    /**
     * Log scan results.
     */
    /**
     * @param  array<string, mixed>  $results
     */
    private function logScanResults(UploadedFile $file, array $results): void
    {
        Log::info('File security scan completed', [
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'is_safe' => $results['is_safe'],
            'threats_count' => is_array($results['threats'] ?? null) ? count($results['threats']) : 0,
            'threats' => $results['threats'],
        ]);
    }

    /**
     * Get allowed file extensions.
     */
    /**
     * @return list<string>
     */
    public function getAllowedExtensions(): array
    {
        return self::ALLOWED_EXTENSIONS;
    }

    /**
     * Get dangerous file extensions.
     */
    /**
     * @return list<string>
     */
    public function getDangerousExtensions(): array
    {
        return self::DANGEROUS_EXTENSIONS;
    }

    /**
     * Get maximum file size.
     */
    public function getMaxFileSize(): int
    {
        return self::MAX_FILE_SIZE;
    }

    /**
     * Get file security statistics.
     */
    /**
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        return [
            'allowed_extensions' => self::ALLOWED_EXTENSIONS,
            'dangerous_extensions' => self::DANGEROUS_EXTENSIONS,
            'max_file_size' => self::MAX_FILE_SIZE,
            'max_file_size_mb' => self::MAX_FILE_SIZE / (1024 * 1024),
        ];
    }
}
