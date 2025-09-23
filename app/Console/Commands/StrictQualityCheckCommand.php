<?php

namespace App\Console\Commands;

use App\Services\AI\StrictQualityAgent;
use Illuminate\Console\Command;

class StrictQualityCheckCommand extends Command
{
    protected $signature = 'ai:quality-check
                            {--stage= : Run specific stage only}
                            {--fix : Auto-fix issues when possible}
                            {--report : Generate detailed report}';

    protected $description = 'Run AI-powered strict quality control with 100% success requirement';

    public function __construct(private readonly StrictQualityAgent $agent)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🤖 بدء تشغيل نظام ضمان الجودة الصارم بالذكاء الاصطناعي');
        $this->info('==================================================');

        $stage = $this->option('stage');
        $autoFix = $this->option('fix');
        $generateReport = $this->option('report');

        if ($stage) {
            $this->runSingleStage($stage);

            return 0;
        }
        $exitCode = $this->runAllStages($autoFix, $generateReport);

        return $exitCode;
    }

    private function runAllStages(bool $autoFix, bool $generateReport): int
    {
        $this->info('🚀 تشغيل جميع المراحل...');

        if ($autoFix) {
            $this->info('🔧 محاولة الإصلاح التلقائي...');
            $fixes = $this->agent->autoFixIssues();

            foreach ($fixes as $message) {
                $this->info('✅ '.(is_string($message) ? $message : ''));
            }
        }

        $results = $this->agent->executeAllStages();

        $this->displayResults($results);

        if ($generateReport) {
            $this->generateDetailedReport($results);
        }

        if (! $results['overall_success']) {
            $this->error('❌ فشل في تحقيق معايير الجودة المطلوبة');
            $this->error('🛑 العملية متوقفة - يلزم إصلاح المشاكل أولاً');

            return 1;
        }
        $this->info('🎉 تم تحقيق جميع معايير الجودة بنجاح!');

        return 0;
    }

    private function runSingleStage(string $stageId): void
    {
        $this->info("🎯 تشغيل المرحلة: {$stageId}");

        // This would need to be implemented in the agent
        $this->warn('تشغيل مرحلة واحدة غير متاح حالياً');
    }

    /**
     * @param  array<string, mixed>  $results
     */
    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('📊 نتائج المراحل:');
        $this->info('==================');

        $table = [];
        if (isset($results['stages']) && is_array($results['stages'])) {
            foreach ($results['stages'] as $stageId => $result) {
                if (is_array($result)) {
                    $success = $result['success'] ?? false;
                    $duration = $result['duration'] ?? 0;
                    $errors = $result['errors'] ?? [];

                    $status = $success ? '✅ نجح' : '❌ فشل';
                    $durationStr = (is_numeric($duration) ? (float) $duration : 0.0).'s';

                    $table[] = [
                        (string) $stageId,
                        $status,
                        $durationStr,
                        is_array($errors) ? count($errors) : 0,
                    ];
                }
            }
        }

        $this->table(['المرحلة', 'الحالة', 'المدة', 'الأخطاء'], $table);

        $this->newLine();
        $this->info('📈 الإحصائيات:');
        $stages = $results['stages'] ?? [];
        $errors = $results['errors'] ?? [];
        $this->info('- إجمالي المراحل: '.(is_array($stages) ? count($stages) : 0));
        $this->info('- المراحل الناجحة: '.(is_array($stages) ? count(array_filter($stages, fn ($r): bool => is_array($r) && ($r['success'] ?? false))) : 0));
        $this->info('- المراحل الفاشلة: '.(is_array($stages) ? count(array_filter($stages, fn ($r): bool => is_array($r) && ! ($r['success'] ?? false))) : 0));
        $this->info('- إجمالي الأخطاء: '.(is_array($errors) ? count($errors) : 0));

        if (! empty($errors) && is_array($errors)) {
            $this->newLine();
            $this->error('🚨 الأخطاء المكتشفة:');
            foreach ($errors as $stageId => $error) {
                $this->error("- {$stageId}: ".(is_string($error) ? $error : ''));
            }
        }

        $fixes = $results['fixes'] ?? [];
        if (! empty($fixes) && is_array($fixes)) {
            $this->newLine();
            $this->info('🔧 الإصلاحات المطبقة:');
            foreach ($fixes as $type => $message) {
                $this->info("- {$type}: ".(is_string($message) ? $message : ''));
            }
        }
    }

    /**
     * @param  array<string, mixed>  $results
     */
    private function generateDetailedReport(array $results): void
    {
        $this->info('📋 إنشاء تقرير مفصل...');

        $reportPath = storage_path('logs/detailed-quality-report.md');
        $content = $this->generateMarkdownReport($results);

        file_put_contents($reportPath, $content);

        $this->info("📁 التقرير المفصل: {$reportPath}");
    }

    /**
     * @param  array<string, mixed>  $results
     */
    private function generateMarkdownReport(array $results): string
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $overallSuccess = $results['overall_success'] ?? false;
        $overallStatus = $overallSuccess ? '✅ نجح' : '❌ فشل';
        $stages = $results['stages'] ?? [];
        $errors = $results['errors'] ?? [];

        $content = "# 🤖 تقرير ضمان الجودة الصارم - {$timestamp}\n\n";
        $content .= "## 📊 ملخص النتائج\n\n";
        $content .= "- **الحالة العامة**: {$overallStatus}\n";
        $content .= '- **إجمالي المراحل**: '.(is_array($stages) ? count($stages) : 0)."\n";
        $content .= '- **المراحل الناجحة**: '.(is_array($stages) ? count(array_filter($stages, fn ($r): bool => is_array($r) && ($r['success'] ?? false))) : 0)."\n";
        $content .= '- **المراحل الفاشلة**: '.(is_array($stages) ? count(array_filter($stages, fn ($r): bool => is_array($r) && ! ($r['success'] ?? false))) : 0)."\n";
        $content .= '- **إجمالي الأخطاء**: '.(is_array($errors) ? count($errors) : 0)."\n\n";

        $content .= "## 📋 تفاصيل المراحل\n\n";
        if (is_array($stages)) {
            foreach ($stages as $stageId => $result) {
                if (is_array($result)) {
                    $success = $result['success'] ?? false;
                    $duration = $result['duration'] ?? 0;
                    $resultErrors = $result['errors'] ?? [];

                    $status = $success ? '✅' : '❌';
                    $content .= "### {$status} {$stageId}\n";
                    $content .= '- **المدة**: '.(is_numeric($duration) ? (float) $duration : 0.0)."s\n";
                    $content .= '- **الأخطاء**: '.(is_array($resultErrors) ? count($resultErrors) : 0)."\n";

                    if (! empty($resultErrors) && is_array($resultErrors)) {
                        $content .= "- **تفاصيل الأخطاء**:\n";
                        foreach ($resultErrors as $error) {
                            $content .= '  - '.(is_string($error) ? $error : '')."\n";
                        }
                    }
                    $content .= "\n";
                }
            }
        }

        $fixes = $results['fixes'] ?? [];
        if (! empty($fixes) && is_array($fixes)) {
            $content .= "## 🔧 الإصلاحات المطبقة\n\n";
            foreach ($fixes as $type => $message) {
                $content .= "- **{$type}**: ".(is_string($message) ? $message : '')."\n";
            }
            $content .= "\n";
        }

        $content .= "## 🎯 التوصيات\n\n";
        if ($overallSuccess) {
            $content .= "✅ جميع المراحل نجحت - المشروع جاهز للنشر\n";
        } else {
            $content .= "❌ يلزم إصلاح المشاكل التالية قبل المتابعة:\n";
            if (is_array($errors)) {
                foreach ($errors as $stageId => $error) {
                    $content .= "- إصلاح مشاكل {$stageId}: ".(is_string($error) ? $error : '')."\n";
                }
            }
        }

        return $content;
    }
}
