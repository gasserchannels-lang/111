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

    public function __construct(private readonly ContinuousQualityMonitor $monitor)
    {
        parent::__construct();
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
        }

        if ($pid !== 0) {
            // Parent process
            $this->info("✅ تم تشغيل العملية الخلفية برقم: {$pid}");
            $this->info('استخدم: kill '.$pid.' لإيقاف العملية');
            return;
        }
        // Child process
        $this->runMonitoringLoop($interval);
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

        /** @var array<string, mixed> $results */
        $results = $this->monitor->performQualityCheck();

        $this->displayResults($results);

        $overallHealthValue = $results['overall_health'] ?? 0;
        $overallHealth = is_numeric($overallHealthValue) ? (float) $overallHealthValue : 0.0;
        if ($overallHealth < 80) {
            $this->error('⚠️ تحذير: صحة الجودة منخفضة ('.$overallHealth.'%)');
        } else {
            $this->info('✅ صحة الجودة جيدة ('.$overallHealth.'%)');
        }
    }

    /**
     * @param  array<string, mixed>  $results
     */
    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('📊 نتائج فحص الجودة:');
        $this->info('=====================');

        $table = [];
        if (isset($results['rules']) && is_array($results['rules'])) {
            foreach ($results['rules'] as $result) {
                /** @var array<string, mixed> $result */
                $healthScore = $result['health_score'] ?? 0;
                $status = (is_numeric($healthScore) ? (float) $healthScore : 0.0) >= 80 ? '✅' : '❌';
                $duration = $result['duration'] ?? 0;
                $table[] = [
                    is_string($result['name'] ?? null) ? $result['name'] : 'Unknown',
                    $status,
                    (is_numeric($healthScore) ? (float) $healthScore : 0.0).'%',
                    (is_numeric($duration) ? (float) $duration : 0.0).'s',
                ];
            }
        }

        $this->table(['القاعدة', 'الحالة', 'النقاط', 'المدة'], $table);

        $alerts = $this->monitor->getAlertsSummary();
        if (isset($alerts['total']) && $alerts['total'] > 0) {
            $this->newLine();
            $total = is_numeric($alerts['total']) ? (int) $alerts['total'] : 0;
            $critical = is_numeric($alerts['critical'] ?? null) ? (int) $alerts['critical'] : 0;
            $warnings = is_numeric($alerts['warnings'] ?? null) ? (int) $alerts['warnings'] : 0;
            $this->warn('🚨 التنبيهات: '.$total.' (حرجة: '.$critical.', تحذيرات: '.$warnings.')');
        }
    }
}
