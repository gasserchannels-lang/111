<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InputSanitizerService
{
    private Str $strHelper;

    private Log $logHelper;

    public function __construct()
    {
        $this->strHelper = new Str;
        $this->logHelper = new Log;
    }

    /**
     * HTML tags that are allowed.
     *
     * @var array<string>
     */
    private array $allowedHtmlTags = [
        'p',
        'br',
        'b',
        'i',
        'u',
        'em',
        'strong',
        'a',
        'ul',
        'ol',
        'li',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'blockquote',
        'pre',
        'code',
    ];

    /**
     * Attributes that are allowed on HTML tags.
     *
     * @var array<string>
     */
    private array $allowedAttributes = [
        'href',
        'title',
        'alt',
        'class',
        'id',
        'name',
        'rel',
        'target',
    ];

    /**
     * Sanitize a string input.
     */
    public function sanitizeString(string $input): string
    {
        // Remove invisible characters
        $input = preg_replace('/[\x00-\x1F\x7F]/u', '', $input);

        // Convert special characters to HTML entities
        return htmlspecialchars($input ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitize an array input recursively.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    /**
     * @param  array<mixed, mixed>  $input
     * @return array<string, mixed>
     */
    public function sanitizeArray(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $input[$key] = $this->sanitizeString($value);
            }
        }

        return $input;
    }

    /**
     * Sanitize HTML content.
     */
    public function sanitizeHtml(string $html): string
    {
        try {
            $dom = new DOMDocument;
            $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            $this->removeDisallowedTags($dom);
            $this->removeDisallowedAttributes($dom);

            $result = $dom->saveHTML();

            return $result !== false ? $result : strip_tags($html);
        } catch (\Exception $e) {
            $this->logHelper->error('HTML sanitization failed', [
                'error' => $e->getMessage(),
                'html' => $html,
            ]);

            // If sanitization fails, strip all HTML
            return strip_tags($html);
        }
    }

    /**
     * Remove any tags that are not in the allowedHtmlTags list.
     */
    private function removeDisallowedTags(DOMDocument $dom): void
    {
        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query('//*');

        if ($nodes) {
            foreach ($nodes as $node) {
                if (! in_array(strtolower($node->nodeName), $this->allowedHtmlTags)) {
                    $node->parentNode?->removeChild($node);
                }
            }
        }
    }

    /**
     * Remove any attributes that are not in the allowedAttributes list.
     */
    private function removeDisallowedAttributes(DOMDocument $dom): void
    {
        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query('//*[@*]');

        if ($nodes) {
            foreach ($nodes as $node) {
                if ($node->hasAttributes()) {
                    foreach ($node->attributes as $attr) {
                        if (in_array(strtolower($attr->nodeName), $this->allowedAttributes)) {
                            continue;
                        }
                        if (! $node instanceof \DOMElement) {
                            continue;
                        }
                        $node->removeAttribute($attr->nodeName);
                    }
                }
            }
        }
    }

    /**
     * Sanitize SQL input to prevent injection.
     */
    public function sanitizeSqlInput(string $input): string
    {
        // Remove common SQL injection patterns
        $patterns = [
            '/union\s+select/i',
            '/union\s+all\s+select/i',
            '/into\s+outfile/i',
            '/load_file/i',
            '/unhex/i',
            '/hex/i',
            '/char\s*\(/i',
            '/cast\s*\(/i',
            '/convert\s*\(/i',
            '/group\s+by/i',
            '/having/i',
            '/sleep\s*\(/i',
            '/benchmark\s*\(/i',
        ];

        $input = preg_replace($patterns, '', $input);

        // Escape special characters
        return addslashes($input ?? '');
    }

    /**
     * Validate and sanitize file upload.
     *
     * @param  array<string, mixed>  $file
     * @return array<string, mixed>|null
     */
    public function sanitizeFileUpload(array $file): ?array
    {
        if (! $this->validateFileStructure($file)) {
            return null;
        }

        if (! $this->validateUploadError($file)) {
            return null;
        }

        $safeName = $this->getSanitizedFilename($file);
        $mimeType = $this->getValidatedMimeType($file);

        if ($mimeType === null) {
            return null;
        }

        return $this->buildSanitizedFileArray($file, $safeName, $mimeType);
    }

    /**
     * Validate file structure.
     *
     * @param  array<string, mixed>  $file
     */
    private function validateFileStructure(array $file): bool
    {
        return isset($file['name']) &&
            isset($file['type']) &&
            isset($file['tmp_name']) &&
            isset($file['error']) &&
            isset($file['size']);
    }

    /**
     * Validate upload error.
     *
     * @param  array<string, mixed>  $file
     */
    private function validateUploadError(array $file): bool
    {
        return $file['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Get sanitized filename.
     *
     * @param  array<string, mixed>  $file
     */
    private function getSanitizedFilename(array $file): string
    {
        $filename = is_string($file['name']) ? $file['name'] : '';

        return $this->sanitizeFileName($filename);
    }

    /**
     * Get validated MIME type.
     *
     * @param  array<string, mixed>  $file
     */
    private function getValidatedMimeType(array $file): ?string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return null;
        }

        $tmpName = is_string($file['tmp_name']) ? $file['tmp_name'] : '';
        $mimeType = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        if ($mimeType === false || ! $this->isAllowedMimeType($mimeType)) {
            return null;
        }

        return $mimeType;
    }

    /**
     * Build sanitized file array.
     *
     * @param  array<string, mixed>  $file
     * @return array<string, mixed>
     */
    private function buildSanitizedFileArray(array $file, string $safeName, string $mimeType): array
    {
        return [
            'name' => $safeName,
            'type' => $mimeType,
            'tmp_name' => $file['tmp_name'],
            'error' => $file['error'],
            'size' => $file['size'],
        ];
    }

    /**
     * Sanitize filename.
     */
    private function sanitizeFileName(string $filename): string
    {
        // Remove any directory components
        $filename = basename($filename);

        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

        // Ensure safe extension
        $extension = strtolower(pathinfo($filename ?? '', PATHINFO_EXTENSION));
        if (! $this->isAllowedExtension($extension)) {
            $filename = $this->strHelper->slug(pathinfo($filename ?? '', PATHINFO_FILENAME)).'.txt';
        }

        return $filename ?? '';
    }

    /**
     * Check if file extension is allowed.
     */
    private function isAllowedExtension(string $extension): bool
    {
        $allowedExtensions = [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'txt',
            'csv',
            'zip',
        ];

        return in_array(strtolower($extension), $allowedExtensions);
    }

    /**
     * Check if mime type is allowed.
     */
    private function isAllowedMimeType(string $mimeType): bool
    {
        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv',
            'application/zip',
        ];

        return in_array(strtolower($mimeType), $allowedMimeTypes);
    }
}
