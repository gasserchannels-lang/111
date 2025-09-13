<?php

namespace App\Console\Commands;

use App\Services\AI\ContinuousQualityMonitor;
use Illuminate\Console\Command;

class StartQualityMonitoringCommand extends Command
{
    protected $signature = 'ai:monitor-quality
                            {--interval=300 : Check interval in seconds}
                            {--daemon : Run as daemon process}';

    protected $description = 'Start continuous AI-powered quality monitoring';

    private ContinuousQualityMonitor $monitor;

    public function __construct(ContinuousQualityMonitor $monitor)
    {
        parent::__construct();
        $this->monitor = $monitor;
    }

    public function handle(): int
    {
        $interval = (int) $this->option('interval');
        $daemon = $this->option('daemon');

        $this->info('🔍 بدء المراقبة المستمرة للجودة بالذكاء الاصطناعي');
        $this->info("⏱️ فترة الفحص: {$interval} ثانية");

        if ($daemon) {
            $this->info('👻 تشغيل كعملية خلفية...');
            $this->runAsDaemon($interval);
        } else {
            $this->runInteractive($interval);
        }

        return Command::SUCCESS;
    }

    private function runInteractive(int $interval): void
    {
        $this->info('🚀 بدء المراقبة التفاعلية...');
        $this->info('اضغط Ctrl+C للإيقاف');

        // @phpstan-ignore-next-line
        while (true) {
            $this->performQualityCheck();
            sleep($interval);
        }
    }

    private function runAsDaemon(int $interval): void
    {
        $this->info('👻 تشغيل كعملية خلفية...');

        // Fork process
        $pid = pcntl_fork();

        if ($pid == -1) {
            $this->error('فشل في إنشاء العملية الخلفية');

            return;
        } elseif ($pid) {
            // Parent process
            $this->info("✅ تم تشغيل العملية الخلفية برقم: {$pid}");
            $this->info('استخدم: kill '.$pid.' لإيقاف العملية');

            return;
        } else {
            // Child process
            $this->runMonitoringLoop($interval);
        }
    }

    private function runMonitoringLoop(int $interval): void
    {
        // @phpstan-ignore-next-line
        while (true) {
            $this->performQualityCheck();
            sleep($interval);
        }
    }

    private function performQualityCheck(): void
    {
        $this->info('🔍 إجراء فحص الجودة...');

        $results = $this->monitor->performQualityCheck();

        $this->displayResults($results);

        if ($results['overall_health'] < 80) {
            $this->error("⚠️ تحذير: صحة الجودة منخفضة ({$results['overall_health']}%)");
        } else {
            $this->info("✅ صحة الجودة جيدة ({$results['overall_health']}%)");
        }
    }

    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('📊 نتائج فحص الجودة:');
        $this->info('=====================');

        $table = [];
        foreach ($results['rules'] as $ruleId => $result) {
            $status = $result['health_score'] >= 80 ? '✅' : '❌';
            $table[] = [
                $result['name'],
                $status,
                $result['health_score'].'%',
                $result['duration'].'s',
            ];
        }

        $this->table(['القاعدة', 'الحالة', 'النقاط', 'المدة'], $table);

        $alerts = $this->monitor->getAlertsSummary();
        if ($alerts['total'] > 0) {
            $this->newLine();
            $this->warn("🚨 التنبيهات: {$alerts['total']} (حرجة: {$alerts['critical']}, تحذيرات: {$alerts['warnings']})");
        }
    }
}
