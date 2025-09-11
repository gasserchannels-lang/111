<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface FileSecurityService
{
    /**
     * Scan uploaded file for security threats.
     *
     * @return array<string, mixed>
     */
    public function scanFile(UploadedFile $file): array;

    /**
     * Get allowed file extensions.
     *
     * @return list<string>
     */
    public function getAllowedExtensions(): array;

    /**
     * Get dangerous file extensions.
     *
     * @return list<string>
     */
    public function getDangerousExtensions(): array;

    /**
     * Get maximum file size.
     */
    public function getMaxFileSize(): int;

    /**
     * Get file security statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array;
}
