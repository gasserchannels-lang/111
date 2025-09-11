<?php

namespace App\Services;

final class TestAnalysisServiceFactory
{
    public function create(): TestAnalysisService
    {
        return new TestAnalysisService;
    }
}
