<?php

namespace App\Services;

class FlexibleService
{
    public function process(mixed ...$args): mixed
    {
        return $args;
    }
}
