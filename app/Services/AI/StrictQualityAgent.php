<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class StrictQualityAgent
{
    /** @var array<string, mixed> */
    private array $stages = [];

    /** @var array<string, mixed> */
    private array $results = [];

    /** @var array<string, mixed> */
    private array $errors = [];

    /** @var array<string, mixed> */
    private array $fixes = [];

    public function __construct()
    {
        $this->initializeStages();
    }

    /**
     * Initialize all quality control stages.
     */
    private function initializeStages(): void
    {
        $this->stages = [
            'syntax_check' => [
                'name' => 'فحص صحة الكود',
                'command' => 'php -l',
                'files' => $this->getPhpFiles(),
                'strict' => true,
                'required' => true,
            ],
            'phpstan_analysis' => [
                'name' => 'التحليل الثابت المتقدم',
                'command' => './vendor/bin/phpstan analyse --memory-limit=1G --configuration=phpstan.strict.neon',
                'strict' => true,
                'required' => true,
            ],
            'phpmd_quality' => [
                'name' => 'فحص جودة الكود',
                'command' => './vendor/bin/phpmd app xml phpmd.strict.xml',
                'strict' => true,
                'required' => true,
            ],
            'pint_formatting' => [
                'name' => 'فحص تنسيق الكود',
                'command' => './vendor/bin/pint --test --config=pint.strict.json',
                'strict' => true,
                'required' => true,
            ],
            'composer_audit' => [
                'name' => 'فحص أمان التبعيات',
                'command' => 'composer audit',
                'strict' => true,
                'required' => true,
            ],
            'unit_tests' => [
                'name' => 'اختبارات الوحدة',
                'command' => 'php artisan test tests/Unit/ --configuration=phpunit.strict.xml',
                'strict' => true,
                'required' => true,
            ],
            'feature_tests' => [
                'name' => 'اختبارات الميزات',
                'command' => 'php artisan test tests/Feature/ --configuration=phpunit.strict.xml',
                'strict' => true,
                'required' => true,
            ],
            'ai_tests' => [
                'name' => 'اختبارات الذكاء الاصطناعي',
                'command' => 'php artisan test tests/AI/ --configuration=phpunit.strict.xml',
                'strict' => true,
                'required' => true,
            ],
            'security_tests' => [
                'name' => 'اختبارات الأمان',
                'command' => 'php artisan test tests/Security/ --configuration=phpunit.strict.xml',
                'strict' => true,
                'required' => true,
            ],
            'performance_tests' => [
                'name' => 'اختبارات الأداء',
                'command' => 'php artisan test tests/Performance/ --configuration=phpunit.strict.xml',
                'strict' => true,
                'required' => true,
            ],
            'integration_tests' => [
                'name' => 'اختبارات التكامل',
                'command' => 'php artisan test tests/Integration/ --configuration=phpunit.strict.xml',
                'strict' => true,
                'required' => true,
            ],
            'e2e_tests' => [
                'name' => 'اختبارات تجربة المستخدم',
                'command' => 'php artisan dusk --configuration=phpunit.strict.xml',
                'strict' => true,
                'required' => true,
            ],
            'link_checker' => [
                'name' => 'فحص الروابط',
                'command' => 'php artisan links:check --all',
                'strict' => true,
                'required' => true,
            ],
        ];
    }

    /**
     * Execute all quality control stages.
     *
     * @return array<string, mixed>
     */
    public function executeAllStages(): array
    {
        $this->log('🚀 بدء تنفيذ جميع مراحل ضمان الجودة...');

        $overallSuccess = true;

        foreach ($this->stages as $stageId => $stage) {
            if (is_array($stage)) {
                $stageName = is_string($stage['name'] ?? null) ? $stage['name'] : '';
                $this->log('📋 تنفيذ المرحلة: '.$stageName);

                $result = $this->executeStage($stageId, $stage);
                $this->results[$stageId] = $result;

                if (! ($result['success'] ?? false)) {
                    $overallSuccess = false;
                    $this->log('❌ فشل في المرحلة: '.($stage['name'] ?? ''));

                    if ($stage['strict'] ?? false) {
                        $this->log('🛑 توقف العملية بسبب فشل مرحلة صارمة');
                        break;
                    }
                } else {
                    $this->log('✅ نجح في المرحلة: '.($stage['name'] ?? ''));
                }
            }
        }

        $this->generateFinalReport($overallSuccess);

        return [
            'overall_success' => $overallSuccess,
            'stages' => $this->results,
            'errors' => $this->errors,
            'fixes' => $this->fixes,
        ];
    }

    /**
     * Execute a single stage.
     *
     * @param  array<string, mixed>  $stage
     * @return array<string, mixed>
     */
    private function executeStage(string $stageId, array $stage): array
    {
        try {
            $startTime = microtime(true);

            if (isset($stage['files'])) {
                $result = $this->executeFileBasedStage($stage);
            } else {
                $result = $this->executeCommandStage($stage);
            }

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'errors' => $result['errors'],
                'duration' => $duration,
                'timestamp' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            $this->errors[$stageId] = $e->getMessage();

            return [
                'success' => false,
                'output' => '',
                'errors' => [$e->getMessage()],
                'duration' => 0,
                'timestamp' => now()->toISOString(),
            ];
        }
    }

    /**
     * Execute file-based stage (like syntax check).
     *
     * @param  array<string, mixed>  $stage
     * @return array<string, mixed>
     */
    private function executeFileBasedStage(array $stage): array
    {
        $errors = [];
        $success = true;

        $files = $stage['files'] ?? [];
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_string($file)) {
                    $command = (is_string($stage['command'] ?? null) ? $stage['command'] : '').' '.$file;
                    $result = Process::run($command);

                    if (! $result->successful()) {
                        $errors[] = 'خطأ في الملف '.$file.': '.$result->errorOutput();
                        $success = false;
                    }
                }
            }
        }

        return [
            'success' => $success,
            'output' => $success ? 'جميع الملفات صحيحة' : 'تم العثور على أخطاء',
            'errors' => $errors,
        ];
    }

    /**
     * Execute command-based stage.
     *
     * @param  array<string, mixed>  $stage
     * @return array<string, mixed>
     */
    private function executeCommandStage(array $stage): array
    {
        $command = is_string($stage['command'] ?? null) ? $stage['command'] : '';
        $result = Process::run($command);

        return [
            'success' => $result->successful(),
            'output' => $result->output(),
            'errors' => $result->successful() ? [] : [$result->errorOutput()],
        ];
    }

    /**
     * Get all PHP files in the project.
     *
     * @return array<string, mixed>
     */
    private function getPhpFiles(): array
    {
        $files = [];
        $directories = ['app', 'config', 'database', 'routes', 'tests'];

        foreach ($directories as $dir) {
            if (File::exists($dir)) {
                $phpFiles = File::allFiles($dir);
                foreach ($phpFiles as $file) {
                    if ($file->getExtension() === 'php') {
                        $files[] = $file->getPathname();
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Auto-fix issues when possible.
     *
     * @return array<string, mixed>
     */
    public function autoFixIssues(): array
    {
        $this->log('🔧 بدء الإصلاح التلقائي للمشاكل...');

        $fixes = [];

        // Fix code formatting
        $this->log('🎨 إصلاح تنسيق الكود...');
        $result = Process::run('./vendor/bin/pint --config=pint.strict.json');
        if ($result->successful()) {
            $fixes['formatting'] = 'تم إصلاح تنسيق الكود';
        }

        // Fix composer issues
        $this->log('📦 إصلاح مشاكل التبعيات...');
        $result = Process::run('composer install --no-dev --optimize-autoloader');
        if ($result->successful()) {
            $fixes['dependencies'] = 'تم إصلاح التبعيات';
        }

        // Clear caches
        $this->log('🗑️ مسح الذاكرة المؤقتة...');
        $commands = [
            'php artisan config:clear',
            'php artisan cache:clear',
            'php artisan route:clear',
            'php artisan view:clear',
        ];

        foreach ($commands as $command) {
            Process::run($command);
        }

        $fixes['caches'] = 'تم مسح الذاكرة المؤقتة';

        $this->fixes = $fixes;

        return $fixes;
    }

    /**
     * Generate comprehensive report.
     */
    private function generateFinalReport(bool $overallSuccess): void
    {
        $report = [
            'timestamp' => now()->toISOString(),
            'overall_success' => $overallSuccess,
            'total_stages' => count($this->stages),
            'successful_stages' => count(array_filter($this->results, function ($r) {
                return is_array($r) && ($r['success'] ?? false) === true;
            })),
            'failed_stages' => count(array_filter($this->results, function ($r) {
                return is_array($r) && ($r['success'] ?? false) !== true;
            })),
            'stages_details' => $this->results,
            'errors' => $this->errors,
            'fixes' => $this->fixes,
        ];

        $reportPath = storage_path('logs/ai-quality-report.json');
        $jsonContent = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($jsonContent !== false) {
            File::put($reportPath, $jsonContent);
        }

        $this->log("📊 تم إنشاء التقرير الشامل: {$reportPath}");
    }

    /**
     * Log messages.
     */
    private function log(string $message): void
    {
        Log::info($message);
        echo $message.PHP_EOL;
    }

    /**
     * Get stage status.
     *
     * @return array<string, mixed>|null
     */
    public function getStageStatus(string $stageId): ?array
    {
        return $this->results[$stageId] ?? null;
    }

    /**
     * Get all results.
     *
     * @return array<string, mixed>
     */
    public function getAllResults(): array
    {
        return $this->results;
    }

    /**
     * Get errors summary.
     *
     * @return array<string, mixed>
     */
    public function getErrorsSummary(): array
    {
        return [
            'total_errors' => count($this->errors),
            'errors_by_stage' => $this->errors,
            'critical_errors' => array_filter($this->errors, fn ($error) => is_string($error) && str_contains($error, 'Fatal')),
        ];
    }
}
