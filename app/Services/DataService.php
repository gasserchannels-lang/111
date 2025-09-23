<?php

namespace App\Services;

class DataService
{
    /**
     * @return array<string, mixed>
     */
    public function getComplexData(string $key = ''): array
    {
        return [
            'users' => [],
            'meta' => [
                'total' => 0,
            ],
        ];
    }
}
