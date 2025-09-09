<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface FileSecurityServiceInterface
{
    /**
     * Scan uploaded file for security threats
     */
    public function scanFile(UploadedFile $file): array;

    /**
     * Get allowed file extensions
     */
    public function getAllowedExtensions(): array;

    /**
     * Get dangerous file extensions
     */
    public function getDangerousExtensions(): array;

    /**
     * Get maximum file size
     */
    public function getMaxFileSize(): int;

    /**
     * Get file security statistics
     */
    public function getStatistics(): array;
}
