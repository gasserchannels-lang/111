<?php

namespace App\Services;

class CounterService
{
    private int $count = 0;

    public function increment(): self
    {
        $this->count++;

        return $this;
    }

    public function reset(): self
    {
        $this->count = 0;

        return $this;
    }
}
