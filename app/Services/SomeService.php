<?php

namespace App\Services;

class SomeService
{
    /**
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public function processData(mixed $data): array
    {
        return is_array($data) ? $data : [];
    }
}
