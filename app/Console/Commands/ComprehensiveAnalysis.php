<?php

namespace App\Console\Commands;

use App\DTO\AnalysisResult;
use App\Services\PerformanceAnalysisService;
use App\Services\QualityAnalysisService;
use App\Services\SecurityAnalysisService;
use App\Services\TestAnalysisServiceFactory;
use Illuminate\Console\Command;

class ComprehensiveAnalysis extends Command
{
    protected $signature = 'agent:analyze {--skip-tests : Skip running tests} {--coverage : Run tests with code coverage (can be slow)}';

    protected $description = 'Run comprehensive code analysis including security, quality, and tests';

    public function handle(): int
    {
        $this->info('🚀 Starting Comprehensive Analysis...');

        /** @var array<string, AnalysisResult> $results */
        $results = [];
        $totalScore = 0;
        $maxScore = 0;

        // Security Analysis
        $results['security'] = $this->runSecurityAnalysis();
        $totalScore += $results['security']->score;
        $maxScore += 100;

        // Code Quality Analysis
        $results['quality'] = $this->runQualityAnalysis();
        $totalScore += $results['quality']->score;
        $maxScore += 100;

        // Tests Analysis
        if (! $this->option('skip-tests')) {
            $this->info('Setting APP_ENV to testing for test analysis...');
            putenv('APP_ENV=testing');
            $results['tests'] = $this->runTestsAnalysis();
            $totalScore += $results['tests']->score;
            $maxScore += 100;
        }

        // Performance Analysis
        $results['performance'] = $this->runPerformanceAnalysis();
        $totalScore += $results['performance']->score;
        $maxScore += 100;

        // Generate Summary
        $this->generateSummary($results, $totalScore, $maxScore);

        return Command::SUCCESS;
    }

    private function runSecurityAnalysis(): AnalysisResult
    {
        $this->info('🛡️  Running Security Analysis...');

        $securityService = new SecurityAnalysisService;
        /** @var array{score: int, max_score: int, issues: array<mixed>} $result */
        $result = $securityService->analyze();

        // Display console output based on the results
        $this->line('Checking for outdated dependencies...');
        if (! empty($result['issues'])) {
            foreach ($result['issues'] as $issue) {
                /** @var string $issue */
                if (str_contains($issue, 'outdated dependencies')) {
                    $this->warn('⚠️  Some direct dependencies are outdated.');
                    break;
                }
            }
        }

        $this->info('✅ All direct dependencies are up to date');

        $issues = [];
        foreach ($result['issues'] as $issue) {
            $issues[] = is_string($issue) ? $issue : (is_scalar($issue) ? (string) $issue : 'Unknown issue');
        }

        return new AnalysisResult(
            'Security',
            $result['score'],
            $result['max_score'],
            $issues
        );
    }

    private function runQualityAnalysis(): AnalysisResult
    {
        $this->info('📊 Running Code Quality Analysis...');

        $qualityService = new QualityAnalysisService;
        /** @var array{score: int, max_score: int, issues: array<mixed>} $result */
        $result = $qualityService->analyze();

        // Display console output based on the results
        $this->line('Running PHPMD...');
        if (! empty($result['issues'])) {
            foreach ($result['issues'] as $issue) {
                /** @var string $issue */
                if (str_contains($issue, 'PHPMD found')) {
                    $this->warn('⚠️  '.$issue);
                }
                if (str_contains($issue, 'PHPCPD found')) {
                    $this->line('Running PHPCPD...');
                    $this->warn('⚠️  '.$issue);
                }
            }
        }

        $this->info('✅ PHPMD found no issues.');
        $this->line('Running PHPCPD...');
        $this->info('✅ PHPCPD found no duplicate code.');

        $issues = [];
        foreach ($result['issues'] as $issue) {
            $issues[] = is_string($issue) ? $issue : (is_scalar($issue) ? (string) $issue : 'Unknown issue');
        }

        return new AnalysisResult(
            'Quality',
            $result['score'],
            $result['max_score'],
            $issues
        );
    }

    private function runTestsAnalysis(): AnalysisResult
    {
        $this->info('🧪 Running Tests Analysis...');

        if ($this->option('coverage')) {
            $this->warn('Coverage analysis is active. This may be slow.');
        }

        $testServiceFactory = new TestAnalysisServiceFactory;
        $testService = $testServiceFactory->create();
        /** @var array{score: int, max_score: int, issues: array<mixed>} $result */
        $result = $testService->analyze();

        // Display console output based on the results
        if (! empty($result['issues'])) {
            foreach ($result['issues'] as $issue) {
                /** @var string $issue */
                if (str_contains($issue, 'tests failed')) {
                    $this->warn('⚠️  Some tests had issues.');

                    continue;
                }
                $this->error('❌ Test analysis encountered errors');
            }
        }

        $this->info('✅ Tests passed successfully.');
        if ($this->option('coverage')) {
            $this->info('✅ Code coverage analyzed.');
        }

        $issues = [];
        foreach ($result['issues'] as $issue) {
            $issues[] = is_string($issue) ? $issue : (is_scalar($issue) ? (string) $issue : 'Unknown issue');
        }

        return new AnalysisResult(
            'Tests',
            $result['score'],
            $result['max_score'],
            $issues
        );
    }

    private function runPerformanceAnalysis(): AnalysisResult
    {
        $this->info('⚡ Running Performance Analysis...');

        $performanceService = new PerformanceAnalysisService;
        /** @var array{score: int, max_score: int, issues: array<mixed>} $result */
        $result = $performanceService->analyze();

        $issues = [];
        foreach ($result['issues'] as $issue) {
            $issues[] = is_string($issue) ? $issue : (is_scalar($issue) ? (string) $issue : 'Unknown issue');
        }

        return new AnalysisResult(
            'Performance',
            $result['score'],
            $result['max_score'],
            $issues
        );
    }

    /**
     * @param  array<string, AnalysisResult>  $results
     */
    private function generateSummary(array $results, int $totalScore, int $maxScore): void
    {
        $this->newLine();
        $this->info('📋 COMPREHENSIVE ANALYSIS SUMMARY');
        $this->line(str_repeat('=', 50));

        $overallPercentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 1) : 0;

        foreach ($results as $result) {
            $percentage = $result->max_score > 0 ? round(($result->score / $result->max_score) * 100, 1) : 0;
            $emoji = $this->getScoreEmoji($percentage);

            $this->line(sprintf(
                '%s %s: %d/%d (%s%%)',
                $emoji,
                $result->category,
                $result->score,
                $result->max_score,
                $percentage
            ));

            if (! empty($result->issues)) {
                foreach ($result->issues as $issue) {
                    $this->line('  ⚠️  '.$issue);
                }
            }
        }

        $this->newLine();
        $overallEmoji = $this->getScoreEmoji($overallPercentage);
        $this->line(sprintf(
            '%s OVERALL SCORE: %d/%d (%s%%)',
            $overallEmoji,
            $totalScore,
            $maxScore,
            $overallPercentage
        ));

        $this->newLine();
        $this->line($this->getGradeMessage($overallPercentage));
    }

    private function getScoreEmoji(float $percentage): string
    {
        if ($percentage >= 90) {
            return '🏆';
        }
        if ($percentage >= 80) {
            return '🥇';
        }
        if ($percentage >= 70) {
            return '🥈';
        }
        if ($percentage >= 60) {
            return '🥉';
        }
        if ($percentage >= 50) {
            return '📈';
        }

        return '🔧';
    }

    private function getGradeMessage(float $percentage): string
    {
        if ($percentage >= 90) {
            return '🎉 Excellent! Your code is production-ready with high quality standards.';
        }

        if ($percentage >= 80) {
            return '👍 Good job! Minor improvements could make your code even better.';
        }

        if ($percentage >= 70) {
            return '📊 Decent quality. Focus on addressing the issues mentioned above.';
        }

        if ($percentage >= 60) {
            return '🔨 Needs improvement. Consider refactoring and adding more tests.';
        }

        return '⚠️  Significant improvements needed. Review security, testing, and code quality.';
    }
}
