<?php

declare(strict_types=1);

namespace App\Services;

class TestAnalysisServiceFactory
{
    private TestAnalysisService $testAnalysisService;

    public function __construct()
    {
        $this->testAnalysisService = new TestAnalysisService(false);
    }

    public function createBasic(): TestAnalysisService
    {
        return $this->testAnalysisService;
    }

    public function createWithCoverage(): TestAnalysisService
    {
        $this->testAnalysisService = new TestAnalysisService(true);

        return $this->testAnalysisService;
    }
}
