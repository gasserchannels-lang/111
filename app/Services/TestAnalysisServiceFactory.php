<?php

namespace App\Services;

class TestAnalysisServiceFactory
{
    public function create(): TestAnalysisService
    {
        return new TestAnalysisService;
    }
}
