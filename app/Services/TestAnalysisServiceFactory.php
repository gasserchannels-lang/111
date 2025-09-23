<?php

declare(strict_types=1);

namespace App\Services;

final class TestAnalysisServiceFactory
{
    public function create(): TestAnalysisService
    {
        return new TestAnalysisService;
    }
}
