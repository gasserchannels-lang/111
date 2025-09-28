<?php

declare(strict_types=1);

namespace App\Contracts;

interface FileSecurityService
{
    /**
     * Get file security statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array;
}
