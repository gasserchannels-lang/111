<?php

/**
 * Script to fix method visibility issues in test files
 */
$testDir = __DIR__.'/tests';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testDir));
$phpFiles = new RegexIterator($files, '/Test\.php$/');

$conflicts = [
    'deployCanary',
    'monitorMetrics',
    'deployToProduction',
    'deployBlueGreen',
    'switchTraffic',
    'rollbackDeployment',
    'handleGradualRollout',
    'deployToCloud',
    'handleEnvironmentConfiguration',
    'handleSecretManagement',
    'validateConfiguration',
    'deployToDevelopment',
    'deployWithDocker',
    'configureEnvironment',
    'provisionInfrastructure',
    'manageConfiguration',
    'setupMonitoring',
    'deployToKubernetes',
    'handleBlueGreenDeployment',
    'handleCanaryDeployment',
    'handleRollingDeployment',
    'handleFeatureFlagDeployment',
    'handleDatabaseMigrationDeployment',
    'handleConfigurationDeployment',
    'handleAssetDeployment',
    'handleContainerDeployment',
    'handleKubernetesDeployment',
    'handleServerlessDeployment',
    'handleCdnDeployment',
    'handleSslCertificateDeployment',
    'handleMonitoringDeployment',
    'handleSecurityDeployment',
    'handleBackupDeployment',
    'handleRollbackDeployment',
    'handleHealthCheckDeployment',
    'handleLoadBalancerDeployment',
    'handleDnsDeployment',
    'handleEnvironmentVariablesDeployment',
    'handleDatabaseDeployment',
    'handleCacheDeployment',
    'handleQueueDeployment',
    'handleLoggingDeployment',
    'handleMetricsDeployment',
    'handleTracingDeployment',
    'handleCiCdDeployment',
    'rollbackProcess',
    'handleZeroDowntime',
    'handleHealthChecks',
    'deployToStaging',
    'deployToTesting',
    'calculateCompletenessMetrics',
    'calculateAccuracyMetrics',
    'calculateConsistencyMetrics',
    'calculateTimelinessMetrics',
    'calculateValidityMetrics',
    'calculateUniquenessMetrics',
    'calculateIntegrityMetrics',
    'calculateOverallQualityScore',
    'generateQualityReport',
    'calculateFieldLevelMetrics',
    'calculateTrendMetrics',
    'calculateBenchmarkMetrics',
];

$fixedFiles = 0;
$totalChanges = 0;

foreach ($phpFiles as $file) {
    $filePath = $file->getPathname();
    $content = file_get_contents($filePath);
    $originalContent = $content;

    // Skip if not a test file
    if (strpos($filePath, 'Test.php') === false) {
        continue;
    }

    $changed = false;

    // Fix private methods that conflict with base TestCase
    foreach ($conflicts as $method) {
        $pattern = '/private function '.preg_quote($method).'\s*\(/';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, 'protected function '.$method.'(', $content);
            $changed = true;
            $totalChanges++;
        }
    }

    if ($changed) {
        file_put_contents($filePath, $content);
        $fixedFiles++;
        echo 'Fixed: '.basename($filePath)."\n";
    }
}

echo "\nSummary:\n";
echo "Files fixed: $fixedFiles\n";
echo "Total changes: $totalChanges\n";
echo "Done!\n";
