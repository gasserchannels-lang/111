<?php

/**
 * Performance Profiler Tool
 * Analyzes application performance and identifies bottlenecks
 */
class PerformanceProfiler
{
    private array $profiles = [];

    private float $startTime;

    private array $memoryUsage = [];

    public function startProfiling(): void
    {
        $this->startTime = microtime(true);
        $this->memoryUsage['start'] = memory_get_usage(true);
    }

    public function endProfiling(): array
    {
        $endTime = microtime(true);
        $this->memoryUsage['end'] = memory_get_usage(true);

        return [
            'execution_time' => $endTime - $this->startTime,
            'memory_usage' => $this->memoryUsage['end'] - $this->memoryUsage['start'],
            'peak_memory' => memory_get_peak_usage(true),
        ];
    }

    public function profileFunction(callable $function, string $name): array
    {
        $start = microtime(true);
        $memStart = memory_get_usage(true);

        $result = $function();

        $end = microtime(true);
        $memEnd = memory_get_usage(true);

        $profile = [
            'name' => $name,
            'execution_time' => $end - $start,
            'memory_usage' => $memEnd - $memStart,
            'result' => $result,
        ];

        $this->profiles[] = $profile;

        return $profile;
    }

    public function getProfiles(): array
    {
        return $this->profiles;
    }

    public function generateReport(): array
    {
        $totalTime = array_sum(array_column($this->profiles, 'execution_time'));
        $totalMemory = array_sum(array_column($this->profiles, 'memory_usage'));

        return [
            'total_profiles' => count($this->profiles),
            'total_execution_time' => $totalTime,
            'total_memory_usage' => $totalMemory,
            'profiles' => $this->profiles,
        ];
    }
}
