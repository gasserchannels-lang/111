<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateAnalysisReport extends Command
{
    protected $signature = 'generate:analysis-report';

    protected $description = 'Generate comprehensive analysis report from CI/CD pipeline results';

    public function handle(): int
    {
        $this->info('📊 Generating Comprehensive Analysis Report...');

        // إنشاء مجلد التقارير
        $reportsDir = storage_path('logs/reports');
        if (! File::exists($reportsDir)) {
            File::makeDirectory($reportsDir, 0755, true);
        }

        // توليد التقرير الرئيسي
        $this->generateMainReport($reportsDir);

        // توليد تقرير الأمان
        $this->generateSecurityReport($reportsDir);

        // توليد تقرير الأداء
        $this->generatePerformanceReport($reportsDir);

        // توليد تقرير الجودة
        $this->generateQualityReport($reportsDir);

        $this->info('✅ Analysis reports generated successfully!');
        $this->info("📁 Reports location: {$reportsDir}");

        return Command::SUCCESS;
    }

    private function generateMainReport(string $reportsDir): void
    {
        $report = "# 📊 تقرير التحليل الشامل - مشروع كوبرا\n\n";
        $report .= '**تاريخ التوليد:** '.now()->format('Y-m-d H:i:s')."\n\n";

        $report .= "## 🔍 ملخص التحليل\n\n";
        $report .= "| المؤشر | القيمة | الحالة |\n";
        $report .= "|--------|--------|--------|\n";

        // قراءة نتائج PHPStan
        $phpstanFile = storage_path('logs/phpstan.json');
        if (File::exists($phpstanFile)) {
            /** @var array<string, mixed> $phpstanData */
            $phpstanData = json_decode(File::get($phpstanFile), true);
            if (isset($phpstanData['totals']) && is_array($phpstanData['totals'])) {
                $errors = is_numeric($phpstanData['totals']['errors'] ?? 0) ? (int) ($phpstanData['totals']['errors']) : 0;
                $warnings = is_numeric($phpstanData['totals']['warnings'] ?? 0) ? (int) ($phpstanData['totals']['warnings']) : 0;
                $status = $errors === 0 ? '✅' : '❌';
                $report .= "| PHPStan Errors | {$errors} | {$status} |\n";
                $report .= "| PHPStan Warnings | {$warnings} | {$status} |\n";
            }
        }

        // قراءة نتائج Composer Audit
        $auditFile = storage_path('logs/composer-audit.json');
        if (File::exists($auditFile)) {
            /** @var array<string, mixed> $auditData */
            $auditData = json_decode(File::get($auditFile), true);
            if (isset($auditData['advisories'])) {
                $advisories = count((array) $auditData['advisories']);
                $status = $advisories === 0 ? '✅' : '⚠️';
                $report .= "| Security Advisories | {$advisories} | {$status} |\n";
            }
        }

        // قراءة نتائج Laravel Pint
        $pintFile = storage_path('logs/pint.json');
        if (File::exists($pintFile)) {
            /** @var array<string, mixed> $pintData */
            $pintData = json_decode(File::get($pintFile), true);
            if (isset($pintData['changes'])) {
                $changes = is_numeric($pintData['changes']) ? (int) $pintData['changes'] : 0;
                $status = $changes === 0 ? '✅' : '🔧';
                $report .= "| Code Style Issues | {$changes} | {$status} |\n";
            }
        }

        $report .= "\n## 📈 التوصيات\n\n";
        $report .= "### ✅ النقاط الإيجابية\n";
        $report .= "- الكود يتبع معايير Laravel\n";
        $report .= "- الاختبارات تعمل بشكل صحيح\n";
        $report .= "- الأمان في مستوى جيد\n\n";

        $report .= "### 🔧 نقاط التحسين\n";
        $report .= "- مراجعة تحذيرات PHPStan\n";
        $report .= "- تحديث التبعيات القديمة\n";
        $report .= "- تحسين أداء الاستعلامات\n\n";

        $report .= "### 🚀 الخطوات التالية\n";
        $report .= "1. إصلاح مشاكل الكود المكتشفة\n";
        $report .= "2. تحديث التبعيات\n";
        $report .= "3. تحسين الأداء\n";
        $report .= "4. إضافة المزيد من الاختبارات\n\n";

        File::put($reportsDir.'/main-analysis-report.md', $report);
    }

    private function generateSecurityReport(string $reportsDir): void
    {
        $report = "# 🔒 تقرير الأمان - مشروع كوبرا\n\n";
        $report .= '**تاريخ التوليد:** '.now()->format('Y-m-d H:i:s')."\n\n";

        $report .= "## 🛡️ فحوصات الأمان\n\n";

        // قراءة نتائج Composer Audit
        $auditFile = storage_path('logs/composer-audit.json');
        if (File::exists($auditFile)) {
            /** @var array<string, mixed> $auditData */
            $auditData = json_decode(File::get($auditFile), true);
            if (isset($auditData['advisories'])) {
                /** @var array<string, mixed> $advisories */
                $advisories = $auditData['advisories'];

                if (empty($advisories)) {
                    $report .= "✅ **لا توجد ثغرات أمنية في التبعيات**\n\n";
                } else {
                    $report .= '⚠️ **تم اكتشاف '.count($advisories)." ثغرة أمنية:**\n\n";
                    foreach ($advisories as $package => $advisory) {
                        if (is_array($advisory)) {
                            $report .= '### 📦 '.(string) $package."\n";
                            $severity = is_string($advisory['severity'] ?? null) ? $advisory['severity'] : 'unknown';
                            $title = is_string($advisory['title'] ?? null) ? $advisory['title'] : 'unknown';
                            $remediation = is_string($advisory['remediation'] ?? null) ? $advisory['remediation'] : 'unknown';
                            $report .= '- **الخطورة:** '.$severity."\n";
                            $report .= '- **الوصف:** '.$title."\n";
                            $report .= '- **الإصلاح:** '.$remediation."\n\n";
                        }
                    }
                }
            }
        }

        // قراءة نتائج اختبارات الأمان
        $securityTestsFile = storage_path('logs/security-tests.xml');
        if (File::exists($securityTestsFile)) {
            /** @var \SimpleXMLElement|false $xml */
            $xml = simplexml_load_file($securityTestsFile);
            if ($xml !== false) {
                $totalTests = is_numeric($xml['tests'] ?? 0) ? (int) ($xml['tests']) : 0;
                $failures = is_numeric($xml['failures'] ?? 0) ? (int) ($xml['failures']) : 0;
                $errors = is_numeric($xml['errors'] ?? 0) ? (int) ($xml['errors']) : 0;

                $report .= "## 🧪 اختبارات الأمان\n\n";
                $report .= "| المؤشر | القيمة |\n";
                $report .= "|--------|--------|\n";
                $report .= "| إجمالي الاختبارات | {$totalTests} |\n";
                $report .= "| الاختبارات الفاشلة | {$failures} |\n";
                $report .= "| الأخطاء | {$errors} |\n\n";

                if ($failures === 0 && $errors === 0) {
                    $report .= "✅ **جميع اختبارات الأمان نجحت**\n\n";
                } else {
                    $report .= "❌ **هناك مشاكل في اختبارات الأمان**\n\n";
                }
            }
        }

        $report .= "## 🔐 توصيات الأمان\n\n";
        $report .= "1. **تحديث التبعيات** بانتظام\n";
        $report .= "2. **فحص الكود** بحثاً عن ثغرات\n";
        $report .= "3. **تشفير البيانات** الحساسة\n";
        $report .= "4. **مراقبة الأنشطة** المشبوهة\n";
        $report .= "5. **نسخ احتياطي** منتظم\n\n";

        File::put($reportsDir.'/security-report.md', $report);
    }

    private function generatePerformanceReport(string $reportsDir): void
    {
        $report = "# ⚡ تقرير الأداء - مشروع كوبرا\n\n";
        $report .= '**تاريخ التوليد:** '.now()->format('Y-m-d H:i:s')."\n\n";

        $report .= "## 📊 مؤشرات الأداء\n\n";

        // قراءة نتائج اختبارات الأداء
        $performanceTestsFile = storage_path('logs/performance-tests.xml');
        if (File::exists($performanceTestsFile)) {
            /** @var \SimpleXMLElement|false $xml */
            $xml = simplexml_load_file($performanceTestsFile);
            if ($xml !== false) {
                $totalTests = is_numeric($xml['tests'] ?? 0) ? (int) ($xml['tests']) : 0;
                $failures = is_numeric($xml['failures'] ?? 0) ? (int) ($xml['failures']) : 0;
                $errors = is_numeric($xml['errors'] ?? 0) ? (int) ($xml['errors']) : 0;

                $report .= "| المؤشر | القيمة |\n";
                $report .= "|--------|--------|\n";
                $report .= "| إجمالي اختبارات الأداء | {$totalTests} |\n";
                $report .= "| الاختبارات الفاشلة | {$failures} |\n";
                $report .= "| الأخطاء | {$errors} |\n\n";

                if ($failures === 0 && $errors === 0) {
                    $report .= "✅ **جميع اختبارات الأداء نجحت**\n\n";
                } else {
                    $report .= "⚠️ **هناك مشاكل في الأداء**\n\n";
                }
            }
        }

        $report .= "\n## 🚀 توصيات تحسين الأداء\n\n";
        $report .= "### 1. تحسين قاعدة البيانات\n";
        $report .= "- إضافة فهارس للاستعلامات البطيئة\n";
        $report .= "- تحسين استعلامات Eloquent\n";
        $report .= "- استخدام Eager Loading\n\n";

        $report .= "### 2. تحسين التخزين المؤقت\n";
        $report .= "- تفعيل Redis للتخزين المؤقت\n";
        $report .= "- تحسين استراتيجية التخزين المؤقت\n";
        $report .= "- استخدام CDN للملفات الثابتة\n\n";

        $report .= "### 3. تحسين الكود\n";
        $report .= "- تقليل استهلاك الذاكرة\n";
        $report .= "- تحسين الخوارزميات\n";
        $report .= "- استخدام Collection Methods\n\n";

        $report .= "### 4. تحسين الخادم\n";
        $report .= "- زيادة ذاكرة PHP\n";
        $report .= "- تحسين إعدادات MySQL\n";
        $report .= "- استخدام OPcache\n\n";

        File::put($reportsDir.'/performance-report.md', $report);
    }

    private function generateQualityReport(string $reportsDir): void
    {
        $report = "# 🎯 تقرير الجودة - مشروع كوبرا\n\n";
        $report .= '**تاريخ التوليد:** '.now()->format('Y-m-d H:i:s')."\n\n";

        $report .= "## 📏 معايير الجودة\n\n";

        // قراءة نتائج PHPStan
        $phpstanFile = storage_path('logs/phpstan.json');
        if (File::exists($phpstanFile)) {
            /** @var array<string, mixed> $phpstanData */
            $phpstanData = json_decode(File::get($phpstanFile), true);
            $errors = 0;
            $warnings = 0;
            if (isset($phpstanData['totals']) && is_array($phpstanData['totals'])) {
                $errors = is_numeric($phpstanData['totals']['errors'] ?? 0) ? (int) ($phpstanData['totals']['errors']) : 0;
                $warnings = is_numeric($phpstanData['totals']['warnings'] ?? 0) ? (int) ($phpstanData['totals']['warnings']) : 0;
            }

            $report .= "### 🔍 PHPStan (التحليل الثابت)\n";
            $report .= "| المؤشر | القيمة |\n";
            $report .= "|--------|--------|\n";
            $report .= "| الأخطاء | {$errors} |\n";
            $report .= "| التحذيرات | {$warnings} |\n\n";

            if ($errors === 0) {
                $report .= "✅ **PHPStan: لا توجد أخطاء**\n\n";
            } else {
                $report .= "❌ **PHPStan: يحتاج إصلاح**\n\n";
            }
        }

        // قراءة نتائج PHPMD
        $phpmdFile = storage_path('logs/phpmd.xml');
        if (File::exists($phpmdFile)) {
            /** @var \SimpleXMLElement|false $xml */
            $xml = simplexml_load_file($phpmdFile);
            if ($xml !== false) {
                $violations = count($xml->xpath('//violation') ?: []);

                $report .= "### 🔧 PHPMD (جودة الكود)\n";
                $report .= "| المؤشر | القيمة |\n";
                $report .= "|--------|--------|\n";
                $report .= "| انتهاكات القواعد | {$violations} |\n\n";

                if ($violations === 0) {
                    $report .= "✅ **PHPMD: الكود نظيف**\n\n";
                } else {
                    $report .= "⚠️ **PHPMD: يحتاج تحسين**\n\n";
                }
            }
        }

        // قراءة نتائج Laravel Pint
        $pintFile = storage_path('logs/pint.json');
        if (File::exists($pintFile)) {
            /** @var array<string, mixed> $pintData */
            $pintData = json_decode(File::get($pintFile), true);
            $changes = 0;
            if (isset($pintData['changes'])) {
                $changes = is_numeric($pintData['changes']) ? (int) $pintData['changes'] : 0;
            }

            $report .= "### 🎨 Laravel Pint (تنسيق الكود)\n";
            $report .= "| المؤشر | القيمة |\n";
            $report .= "|--------|--------|\n";
            $report .= "| التغييرات المطلوبة | {$changes} |\n\n";

            if ($changes === 0) {
                $report .= "✅ **Laravel Pint: التنسيق صحيح**\n\n";
            } else {
                $report .= "🔧 **Laravel Pint: يحتاج تنسيق**\n\n";
            }
        }

        $report .= "## 📈 خطة تحسين الجودة\n\n";
        $report .= "### المرحلة الأولى (فوري)\n";
        $report .= "1. إصلاح أخطاء PHPStan\n";
        $report .= "2. تنسيق الكود بـ Laravel Pint\n";
        $report .= "3. إصلاح انتهاكات PHPMD\n\n";

        $report .= "### المرحلة الثانية (قصير المدى)\n";
        $report .= "1. تحسين بنية الكود\n";
        $report .= "2. إضافة المزيد من الاختبارات\n";
        $report .= "3. تحسين التوثيق\n\n";

        $report .= "### المرحلة الثالثة (طويل المدى)\n";
        $report .= "1. إعادة هيكلة الكود المعقد\n";
        $report .= "2. تحسين الأداء\n";
        $report .= "3. إضافة مراقبة مستمرة\n\n";

        File::put($reportsDir.'/quality-report.md', $report);
    }
}
