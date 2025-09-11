<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class TestAnalysisService
{
    private bool $coverageEnabled;

    public function __construct(bool $coverageEnabled = false)
    {
        $this->coverageEnabled = $coverageEnabled;
    }

    public static function withoutCoverage(): self
    {
        return new self(false);
    }

    public static function withCoverage(): self
    {
        return new self(true);
    }

    /**
     * Run comprehensive test analysis.
     *
     * @return array<string, mixed>
     */
    public function analyze(): array
    {
        $score = 0;
        $issues = [];

        try {
            $command = $this->buildTestCommand();
            $process = $this->runTestProcess($command);
            $output = $process->getOutput();

            $score += $this->analyzeTestResults($process, $output, $issues);
            $score += $this->analyzeCoverage($output, $issues);
        } catch (ProcessFailedException $e) {
            $this->handleTestProcessException($e, $issues);
        } catch (\Exception $e) {
            $issues[] = 'Test analysis failed: '.$e->getMessage();
        }

        return [
            'score' => min(100, $score),
            'max_score' => 100,
            'issues' => $issues,
            'category' => 'Testing',
        ];
    }

    /**
     * Build test command.
     *
     * @return array<string>
     */
    private function buildTestCommand(): array
    {
        $command = ['./vendor/bin/pest'];
        if ($this->coverageEnabled) {
            $command[] = '--coverage';
        }

        return $command;
    }

    /**
     * Run test process.
     *
     * @param  array<string>  $command
     */
    private function runTestProcess(array $command): Process
    {
        $process = new Process($command);
        $process->setTimeout(1800); // Increased timeout to 30 mins for coverage
        $process->run();

        return $process;
    }

    /**
     * Analyze test results.
     *
     * @param  array<string>  $issues
     */
    private function analyzeTestResults(Process $process, string $output, array &$issues): int
    {
        if (! $process->isSuccessful()) {
            $issues[] = 'Some tests failed or encountered errors.';

            return 0;
        }

        if (preg_match('/Tests:\s+.*?(\d+)\s+passed/', $output)) {
            return 70;
        }

        return 0;
    }

    /**
     * Analyze coverage.
     *
     * @param  array<string>  $issues
     */
    private function analyzeCoverage(string $output, array &$issues): int
    {
        if (! $this->coverageEnabled) {
            return 0;
        }

        if (preg_match('/Lines:\s+(\d+\.\d+)%/', $output, $matches)) {
            $coverage = (float) $matches[1];

            return (int) (($coverage / 100) * 30);
        }

        $issues[] = 'Code coverage information not available';

        return 0;
    }

    /**
     * Handle test process exception.
     *
     * @param  array<string>  $issues
     */
    private function handleTestProcessException(ProcessFailedException $exception, array &$issues): void
    {
        $process = $exception->getProcess();
        if ($process->getTimeout() > 0 && $process->getStartTime() > 0) {
            $elapsed = microtime(true) - $process->getStartTime();
            if ($elapsed >= $process->getTimeout()) {
                $issues[] = 'Test analysis failed: The process exceeded the timeout.';

                return;
            }
        }

        $issues[] = 'Test analysis failed with an error.';
    }
}
