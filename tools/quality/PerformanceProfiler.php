<?php

namespace Tools\Quality;

class PerformanceProfiler
{
    private array $profiles = [];

    private array $benchmarks = [];

    private float $startTime;

    private int $startMemory;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
    }

    public function startProfile(string $name): void
    {
        $this->profiles[$name] = [
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(),
            'peak_memory' => memory_get_peak_usage(),
        ];
    }

    public function endProfile(string $name): array
    {
        if (! isset($this->profiles[$name])) {
            throw new \InvalidArgumentException("Profile '{$name}' not found");
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        $peakMemory = memory_get_peak_usage();

        $profile = [
            'name' => $name,
            'execution_time' => $endTime - $this->profiles[$name]['start_time'],
            'memory_usage' => $endMemory - $this->profiles[$name]['start_memory'],
            'peak_memory' => $peakMemory,
            'start_time' => $this->profiles[$name]['start_time'],
            'end_time' => $endTime,
            'start_memory' => $this->profiles[$name]['start_memory'],
            'end_memory' => $endMemory,
        ];

        $this->benchmarks[$name] = $profile;
        unset($this->profiles[$name]);

        return $profile;
    }

    public function profileFunction(callable $function, string $name, ...$args): array
    {
        $this->startProfile($name);
        $result = $function(...$args);
        $profile = $this->endProfile($name);
        $profile['result'] = $result;

        return $profile;
    }

    public function profileMethod(object $object, string $method, string $name, ...$args): array
    {
        $this->startProfile($name);
        $result = $object->$method(...$args);
        $profile = $this->endProfile($name);
        $profile['result'] = $result;

        return $profile;
    }

    public function profileDatabaseQuery(string $query, callable $executor, string $name): array
    {
        $this->startProfile($name);
        $result = $executor($query);
        $profile = $this->endProfile($name);
        $profile['query'] = $query;
        $profile['result'] = $result;

        return $profile;
    }

    public function profileApiCall(string $url, callable $executor, string $name): array
    {
        $this->startProfile($name);
        $result = $executor($url);
        $profile = $this->endProfile($name);
        $profile['url'] = $url;
        $profile['result'] = $result;

        return $profile;
    }

    public function profileFileOperation(string $filePath, callable $operation, string $name): array
    {
        $this->startProfile($name);
        $result = $operation($filePath);
        $profile = $this->endProfile($name);
        $profile['file_path'] = $filePath;
        $profile['result'] = $result;

        return $profile;
    }

    public function getBenchmarks(): array
    {
        return $this->benchmarks;
    }

    public function getTotalExecutionTime(): float
    {
        return microtime(true) - $this->startTime;
    }

    public function getTotalMemoryUsage(): int
    {
        return memory_get_usage() - $this->startMemory;
    }

    public function getPeakMemoryUsage(): int
    {
        return memory_get_peak_usage();
    }

    public function getAverageExecutionTime(): float
    {
        if (empty($this->benchmarks)) {
            return 0;
        }

        $totalTime = 0;
        foreach ($this->benchmarks as $benchmark) {
            $totalTime += $benchmark['execution_time'];
        }

        return $totalTime / count($this->benchmarks);
    }

    public function getAverageMemoryUsage(): float
    {
        if (empty($this->benchmarks)) {
            return 0;
        }

        $totalMemory = 0;
        foreach ($this->benchmarks as $benchmark) {
            $totalMemory += $benchmark['memory_usage'];
        }

        return $totalMemory / count($this->benchmarks);
    }

    public function getSlowestOperations(int $limit = 10): array
    {
        $benchmarks = $this->benchmarks;
        usort($benchmarks, function ($a, $b) {
            return $b['execution_time'] <=> $a['execution_time'];
        });

        return array_slice($benchmarks, 0, $limit);
    }

    public function getMemoryIntensiveOperations(int $limit = 10): array
    {
        $benchmarks = $this->benchmarks;
        usort($benchmarks, function ($a, $b) {
            return $b['memory_usage'] <=> $a['memory_usage'];
        });

        return array_slice($benchmarks, 0, $limit);
    }

    public function getPerformanceReport(): string
    {
        $report = "# Performance Profiling Report\n\n";
        $report .= 'Generated on: '.date('Y-m-d H:i:s')."\n\n";

        $report .= "## Summary\n";
        $report .= '- Total execution time: '.number_format($this->getTotalExecutionTime(), 4)." seconds\n";
        $report .= '- Total memory usage: '.$this->formatBytes($this->getTotalMemoryUsage())."\n";
        $report .= '- Peak memory usage: '.$this->formatBytes($this->getPeakMemoryUsage())."\n";
        $report .= '- Average execution time: '.number_format($this->getAverageExecutionTime(), 4)." seconds\n";
        $report .= '- Average memory usage: '.$this->formatBytes($this->getAverageMemoryUsage())."\n\n";

        $report .= "## Slowest Operations\n";
        $slowest = $this->getSlowestOperations(5);
        foreach ($slowest as $operation) {
            $report .= "- **{$operation['name']}**: ".number_format($operation['execution_time'], 4)." seconds\n";
        }

        $report .= "\n## Memory Intensive Operations\n";
        $memoryIntensive = $this->getMemoryIntensiveOperations(5);
        foreach ($memoryIntensive as $operation) {
            $report .= "- **{$operation['name']}**: ".$this->formatBytes($operation['memory_usage'])."\n";
        }

        $report .= "\n## Detailed Benchmarks\n";
        foreach ($this->benchmarks as $name => $benchmark) {
            $report .= "### {$name}\n";
            $report .= '- Execution time: '.number_format($benchmark['execution_time'], 4)." seconds\n";
            $report .= '- Memory usage: '.$this->formatBytes($benchmark['memory_usage'])."\n";
            $report .= '- Peak memory: '.$this->formatBytes($benchmark['peak_memory'])."\n";
            $report .= '- Start time: '.date('Y-m-d H:i:s', $benchmark['start_time'])."\n";
            $report .= '- End time: '.date('Y-m-d H:i:s', $benchmark['end_time'])."\n\n";
        }

        return $report;
    }

    public function exportToJson(): string
    {
        return json_encode([
            'summary' => [
                'total_execution_time' => $this->getTotalExecutionTime(),
                'total_memory_usage' => $this->getTotalMemoryUsage(),
                'peak_memory_usage' => $this->getPeakMemoryUsage(),
                'average_execution_time' => $this->getAverageExecutionTime(),
                'average_memory_usage' => $this->getAverageMemoryUsage(),
                'total_operations' => count($this->benchmarks),
            ],
            'benchmarks' => $this->benchmarks,
            'slowest_operations' => $this->getSlowestOperations(),
            'memory_intensive_operations' => $this->getMemoryIntensiveOperations(),
            'generated_at' => date('Y-m-d H:i:s'),
        ], JSON_PRETTY_PRINT);
    }

    public function exportToCsv(): string
    {
        $csv = "Name,Execution Time,Memory Usage,Peak Memory,Start Time,End Time\n";

        foreach ($this->benchmarks as $benchmark) {
            $csv .= sprintf(
                "%s,%f,%d,%d,%s,%s\n",
                $benchmark['name'],
                $benchmark['execution_time'],
                $benchmark['memory_usage'],
                $benchmark['peak_memory'],
                date('Y-m-d H:i:s', $benchmark['start_time']),
                date('Y-m-d H:i:s', $benchmark['end_time'])
            );
        }

        return $csv;
    }

    public function analyzePerformance(): array
    {
        $analysis = [
            'performance_score' => $this->calculatePerformanceScore(),
            'bottlenecks' => $this->identifyBottlenecks(),
            'recommendations' => $this->generateRecommendations(),
            'optimization_opportunities' => $this->findOptimizationOpportunities(),
            'memory_leaks' => $this->detectMemoryLeaks(),
            'execution_efficiency' => $this->calculateExecutionEfficiency(),
        ];

        return $analysis;
    }

    private function calculatePerformanceScore(): float
    {
        $score = 100;

        // Penalize slow operations
        foreach ($this->benchmarks as $benchmark) {
            if ($benchmark['execution_time'] > 1.0) {
                $score -= 10;
            } elseif ($benchmark['execution_time'] > 0.5) {
                $score -= 5;
            }
        }

        // Penalize high memory usage
        $totalMemory = $this->getTotalMemoryUsage();
        if ($totalMemory > 100 * 1024 * 1024) { // 100MB
            $score -= 20;
        } elseif ($totalMemory > 50 * 1024 * 1024) { // 50MB
            $score -= 10;
        }

        return max(0, $score);
    }

    private function identifyBottlenecks(): array
    {
        $bottlenecks = [];

        foreach ($this->benchmarks as $name => $benchmark) {
            if ($benchmark['execution_time'] > 1.0) {
                $bottlenecks[] = [
                    'operation' => $name,
                    'type' => 'execution_time',
                    'value' => $benchmark['execution_time'],
                    'severity' => 'high',
                ];
            }

            if ($benchmark['memory_usage'] > 10 * 1024 * 1024) { // 10MB
                $bottlenecks[] = [
                    'operation' => $name,
                    'type' => 'memory_usage',
                    'value' => $benchmark['memory_usage'],
                    'severity' => 'high',
                ];
            }
        }

        return $bottlenecks;
    }

    private function generateRecommendations(): array
    {
        $recommendations = [];

        $slowest = $this->getSlowestOperations(3);
        foreach ($slowest as $operation) {
            $recommendations[] = "Optimize '{$operation['name']}' - currently taking ".number_format($operation['execution_time'], 4).' seconds';
        }

        $memoryIntensive = $this->getMemoryIntensiveOperations(3);
        foreach ($memoryIntensive as $operation) {
            $recommendations[] = "Reduce memory usage in '{$operation['name']}' - currently using ".$this->formatBytes($operation['memory_usage']);
        }

        if ($this->getTotalMemoryUsage() > 100 * 1024 * 1024) {
            $recommendations[] = 'Consider implementing memory optimization strategies';
        }

        if ($this->getTotalExecutionTime() > 10) {
            $recommendations[] = 'Consider implementing caching mechanisms';
        }

        return $recommendations;
    }

    private function findOptimizationOpportunities(): array
    {
        $opportunities = [];

        foreach ($this->benchmarks as $name => $benchmark) {
            if ($benchmark['execution_time'] > 0.1) {
                $opportunities[] = [
                    'operation' => $name,
                    'type' => 'execution_time',
                    'current_value' => $benchmark['execution_time'],
                    'potential_improvement' => 'Consider caching or algorithm optimization',
                ];
            }

            if ($benchmark['memory_usage'] > 1024 * 1024) { // 1MB
                $opportunities[] = [
                    'operation' => $name,
                    'type' => 'memory_usage',
                    'current_value' => $benchmark['memory_usage'],
                    'potential_improvement' => 'Consider memory-efficient data structures',
                ];
            }
        }

        return $opportunities;
    }

    private function detectMemoryLeaks(): array
    {
        $leaks = [];

        // Simple memory leak detection based on memory growth
        $previousMemory = 0;
        foreach ($this->benchmarks as $name => $benchmark) {
            if ($previousMemory > 0 && $benchmark['memory_usage'] > $previousMemory * 1.5) {
                $leaks[] = [
                    'operation' => $name,
                    'memory_growth' => $benchmark['memory_usage'] - $previousMemory,
                    'severity' => 'medium',
                ];
            }
            $previousMemory = $benchmark['memory_usage'];
        }

        return $leaks;
    }

    private function calculateExecutionEfficiency(): float
    {
        $totalTime = $this->getTotalExecutionTime();
        $totalOperations = count($this->benchmarks);

        if ($totalOperations === 0) {
            return 0;
        }

        // Efficiency based on operations per second
        return $totalOperations / $totalTime;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }
}
