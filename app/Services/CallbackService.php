<?php

namespace App\Services;

class CallbackService
{
    public function processWithCallback(callable $callback): mixed
    {
        return $callback();
    }
}
