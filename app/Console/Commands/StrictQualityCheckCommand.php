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

    private StrictQualityAgent $agent;

    public function __construct(StrictQualityAgent $agent)
    {
        parent::__construct();
        $this->agent = $agent;
    }

    public function handle()
    {
        $this->info('🤖 بدء تشغيل نظام ضمان الجودة الصارم بالذكاء الاصطناعي');
        $this->info('==================================================');

        $stage = $this->option('stage');
        $autoFix = $this->option('fix');
        $generateReport = $this->option('report');

        if ($stage) {
            $this->runSingleStage($stage);
        } else {
            $this->runAllStages($autoFix, $generateReport);
        }
    }

    private function runAllStages(bool $autoFix, bool $generateReport): void
    {
        $this->info('🚀 تشغيل جميع المراحل...');

        if ($autoFix) {
            $this->info('🔧 محاولة الإصلاح التلقائي...');
            $fixes = $this->agent->autoFixIssues();

            foreach ($fixes as $type => $message) {
                $this->info("✅ {$message}");
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
            exit(1);
        } else {
            $this->info('🎉 تم تحقيق جميع معايير الجودة بنجاح!');
        }
    }

    private function runSingleStage(string $stageId): void
    {
        $this->info("🎯 تشغيل المرحلة: {$stageId}");

        // This would need to be implemented in the agent
        $this->warn('تشغيل مرحلة واحدة غير متاح حالياً');
    }

    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('📊 نتائج المراحل:');
        $this->info('==================');

        $table = [];
        foreach ($results['stages'] as $stageId => $result) {
            $status = $result['success'] ? '✅ نجح' : '❌ فشل';
            $duration = $result['duration'].'s';

            $table[] = [
                $stageId,
                $status,
                $duration,
                count($result['errors']),
            ];
        }

        $this->table(['المرحلة', 'الحالة', 'المدة', 'الأخطاء'], $table);

        $this->newLine();
        $this->info('📈 الإحصائيات:');
        $this->info('- إجمالي المراحل: '.count($results['stages']));
        $this->info('- المراحل الناجحة: '.count(array_filter($results['stages'], fn ($r) => $r['success'])));
        $this->info('- المراحل الفاشلة: '.count(array_filter($results['stages'], fn ($r) => ! $r['success'])));
        $this->info('- إجمالي الأخطاء: '.count($results['errors']));

        if (! empty($results['errors'])) {
            $this->newLine();
            $this->error('🚨 الأخطاء المكتشفة:');
            foreach ($results['errors'] as $stageId => $error) {
                $this->error("- {$stageId}: {$error}");
            }
        }

        if (! empty($results['fixes'])) {
            $this->newLine();
            $this->info('🔧 الإصلاحات المطبقة:');
            foreach ($results['fixes'] as $type => $message) {
                $this->info("- {$type}: {$message}");
            }
        }
    }

    private function generateDetailedReport(array $results): void
    {
        $this->info('📋 إنشاء تقرير مفصل...');

        $reportPath = storage_path('logs/detailed-quality-report.md');
        $content = $this->generateMarkdownReport($results);

        file_put_contents($reportPath, $content);

        $this->info("📁 التقرير المفصل: {$reportPath}");
    }

    private function generateMarkdownReport(array $results): string
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $overallStatus = $results['overall_success'] ? '✅ نجح' : '❌ فشل';

        $content = "# 🤖 تقرير ضمان الجودة الصارم - {$timestamp}\n\n";
        $content .= "## 📊 ملخص النتائج\n\n";
        $content .= "- **الحالة العامة**: {$overallStatus}\n";
        $content .= '- **إجمالي المراحل**: '.count($results['stages'])."\n";
        $content .= '- **المراحل الناجحة**: '.count(array_filter($results['stages'], fn ($r) => $r['success']))."\n";
        $content .= '- **المراحل الفاشلة**: '.count(array_filter($results['stages'], fn ($r) => ! $r['success']))."\n";
        $content .= '- **إجمالي الأخطاء**: '.count($results['errors'])."\n\n";

        $content .= "## 📋 تفاصيل المراحل\n\n";
        foreach ($results['stages'] as $stageId => $result) {
            $status = $result['success'] ? '✅' : '❌';
            $content .= "### {$status} {$stageId}\n";
            $content .= "- **المدة**: {$result['duration']}s\n";
            $content .= '- **الأخطاء**: '.count($result['errors'])."\n";

            if (! empty($result['errors'])) {
                $content .= "- **تفاصيل الأخطاء**:\n";
                foreach ($result['errors'] as $error) {
                    $content .= "  - {$error}\n";
                }
            }
            $content .= "\n";
        }

        if (! empty($results['fixes'])) {
            $content .= "## 🔧 الإصلاحات المطبقة\n\n";
            foreach ($results['fixes'] as $type => $message) {
                $content .= "- **{$type}**: {$message}\n";
            }
            $content .= "\n";
        }

        $content .= "## 🎯 التوصيات\n\n";
        if ($results['overall_success']) {
            $content .= "✅ جميع المراحل نجحت - المشروع جاهز للنشر\n";
        } else {
            $content .= "❌ يلزم إصلاح المشاكل التالية قبل المتابعة:\n";
            foreach ($results['errors'] as $stageId => $error) {
                $content .= "- إصلاح مشاكل {$stageId}: {$error}\n";
            }
        }

        return $content;
    }
}
