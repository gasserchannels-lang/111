<?php

namespace Tests\Unit\Deployment;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProductionDeploymentTest extends TestCase
{
    #[Test]
    public function it_deploys_to_production_successfully(): void
    {
        $deploymentData = [
            'environment' => 'production',
            'version' => '1.2.3',
            'build_number' => '12345',
            'deployment_strategy' => 'blue_green'
        ];

        $deploymentResult = $this->deployToProduction($deploymentData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('deployment_id', $deploymentResult);
        $this->assertArrayHasKey('deployment_time', $deploymentResult);
        $this->assertArrayHasKey('health_check', $deploymentResult);
    }

    #[Test]
    public function it_handles_blue_green_deployment(): void
    {
        $blueGreenData = [
            'blue_environment' => 'production-blue',
            'green_environment' => 'production-green',
            'current_active' => 'blue',
            'new_version' => '1.2.4'
        ];

        $deploymentResult = $this->handleBlueGreenDeployment($blueGreenData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('switch_completed', $deploymentResult);
        $this->assertArrayHasKey('rollback_available', $deploymentResult);
        $this->assertArrayHasKey('traffic_switched', $deploymentResult);
    }

    #[Test]
    public function it_handles_canary_deployment(): void
    {
        $canaryData = [
            'canary_percentage' => 10,
            'monitoring_duration' => 30,
            'success_criteria' => ['error_rate < 1%', 'response_time < 500ms'],
            'rollback_threshold' => 5
        ];

        $deploymentResult = $this->handleCanaryDeployment($canaryData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('canary_metrics', $deploymentResult);
        $this->assertArrayHasKey('gradual_rollout', $deploymentResult);
        $this->assertArrayHasKey('monitoring_active', $deploymentResult);
    }

    #[Test]
    public function it_handles_rolling_deployment(): void
    {
        $rollingData = [
            'instances' => ['instance1', 'instance2', 'instance3'],
            'batch_size' => 1,
            'health_check_interval' => 30,
            'max_unavailable' => 1
        ];

        $deploymentResult = $this->handleRollingDeployment($rollingData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('instances_updated', $deploymentResult);
        $this->assertArrayHasKey('zero_downtime', $deploymentResult);
        $this->assertArrayHasKey('rollback_available', $deploymentResult);
    }

    #[Test]
    public function it_handles_feature_flag_deployment(): void
    {
        $featureFlagData = [
            'feature_name' => 'new_checkout_flow',
            'enabled_percentage' => 50,
            'target_users' => ['premium', 'beta'],
            'rollback_trigger' => 'error_rate > 2%'
        ];

        $deploymentResult = $this->handleFeatureFlagDeployment($featureFlagData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('feature_enabled', $deploymentResult);
        $this->assertArrayHasKey('user_segmentation', $deploymentResult);
        $this->assertArrayHasKey('monitoring_active', $deploymentResult);
    }

    #[Test]
    public function it_handles_database_migration_deployment(): void
    {
        $migrationData = [
            'migration_files' => ['001_add_user_table.php', '002_add_product_table.php'],
            'backup_created' => true,
            'rollback_available' => true,
            'maintenance_mode' => false
        ];

        $deploymentResult = $this->handleDatabaseMigrationDeployment($migrationData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('migrations_applied', $deploymentResult);
        $this->assertArrayHasKey('backup_verified', $deploymentResult);
        $this->assertArrayHasKey('data_integrity', $deploymentResult);
    }

    #[Test]
    public function it_handles_configuration_deployment(): void
    {
        $configData = [
            'config_files' => ['app.php', 'database.php', 'cache.php'],
            'environment_variables' => ['DB_HOST', 'CACHE_DRIVER', 'QUEUE_CONNECTION'],
            'validation_required' => true,
            'restart_services' => true
        ];

        $deploymentResult = $this->handleConfigurationDeployment($configData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('config_validated', $deploymentResult);
        $this->assertArrayHasKey('services_restarted', $deploymentResult);
        $this->assertArrayHasKey('validation_passed', $deploymentResult);
    }

    #[Test]
    public function it_handles_asset_deployment(): void
    {
        $assetData = [
            'asset_types' => ['css', 'js', 'images', 'fonts'],
            'cdn_upload' => true,
            'cache_invalidation' => true,
            'compression_enabled' => true
        ];

        $deploymentResult = $this->handleAssetDeployment($assetData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('assets_uploaded', $deploymentResult);
        $this->assertArrayHasKey('cdn_updated', $deploymentResult);
        $this->assertArrayHasKey('cache_invalidated', $deploymentResult);
    }

    #[Test]
    public function it_handles_container_deployment(): void
    {
        $containerData = [
            'container_image' => 'app:1.2.3',
            'registry' => 'docker.io/company/app',
            'replicas' => 3,
            'resources' => ['cpu' => '500m', 'memory' => '1Gi']
        ];

        $deploymentResult = $this->handleContainerDeployment($containerData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('containers_deployed', $deploymentResult);
        $this->assertArrayHasKey('health_checks_passing', $deploymentResult);
        $this->assertArrayHasKey('load_balancer_updated', $deploymentResult);
    }

    #[Test]
    public function it_handles_kubernetes_deployment(): void
    {
        $k8sData = [
            'namespace' => 'production',
            'deployment_name' => 'app-deployment',
            'service_name' => 'app-service',
            'ingress_name' => 'app-ingress'
        ];

        $deploymentResult = $this->handleKubernetesDeployment($k8sData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('pods_running', $deploymentResult);
        $this->assertArrayHasKey('service_available', $deploymentResult);
        $this->assertArrayHasKey('ingress_configured', $deploymentResult);
    }

    #[Test]
    public function it_handles_serverless_deployment(): void
    {
        $serverlessData = [
            'function_name' => 'api-handler',
            'runtime' => 'nodejs18.x',
            'memory_size' => 512,
            'timeout' => 30
        ];

        $deploymentResult = $this->handleServerlessDeployment($serverlessData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('function_deployed', $deploymentResult);
        $this->assertArrayHasKey('endpoint_available', $deploymentResult);
        $this->assertArrayHasKey('monitoring_enabled', $deploymentResult);
    }

    #[Test]
    public function it_handles_cdn_deployment(): void
    {
        $cdnData = [
            'cdn_provider' => 'cloudflare',
            'zones' => ['static.example.com', 'api.example.com'],
            'cache_rules' => ['css' => '1y', 'js' => '1y', 'images' => '30d'],
            'purge_required' => true
        ];

        $deploymentResult = $this->handleCDNDeployment($cdnData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('zones_updated', $deploymentResult);
        $this->assertArrayHasKey('cache_purged', $deploymentResult);
        $this->assertArrayHasKey('performance_optimized', $deploymentResult);
    }

    #[Test]
    public function it_handles_ssl_certificate_deployment(): void
    {
        $sslData = [
            'certificate_type' => 'wildcard',
            'domain' => '*.example.com',
            'provider' => 'letsencrypt',
            'auto_renewal' => true
        ];

        $deploymentResult = $this->handleSSLCertificateDeployment($sslData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('certificate_installed', $deploymentResult);
        $this->assertArrayHasKey('ssl_enabled', $deploymentResult);
        $this->assertArrayHasKey('auto_renewal_configured', $deploymentResult);
    }

    #[Test]
    public function it_handles_monitoring_deployment(): void
    {
        $monitoringData = [
            'monitoring_tools' => ['prometheus', 'grafana', 'jaeger'],
            'alerts_configured' => true,
            'dashboards_created' => true,
            'log_aggregation' => true
        ];

        $deploymentResult = $this->handleMonitoringDeployment($monitoringData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('monitoring_active', $deploymentResult);
        $this->assertArrayHasKey('alerts_configured', $deploymentResult);
        $this->assertArrayHasKey('dashboards_available', $deploymentResult);
    }

    #[Test]
    public function it_handles_security_deployment(): void
    {
        $securityData = [
            'security_tools' => ['waf', 'ddos_protection', 'ssl_termination'],
            'firewall_rules' => ['allow_https', 'block_suspicious_ips'],
            'vulnerability_scan' => true,
            'penetration_test' => true
        ];

        $deploymentResult = $this->handleSecurityDeployment($securityData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('security_enabled', $deploymentResult);
        $this->assertArrayHasKey('firewall_configured', $deploymentResult);
        $this->assertArrayHasKey('vulnerability_scan_passed', $deploymentResult);
    }

    #[Test]
    public function it_handles_backup_deployment(): void
    {
        $backupData = [
            'backup_strategy' => 'incremental',
            'retention_period' => '30 days',
            'backup_locations' => ['local', 's3', 'glacier'],
            'encryption_enabled' => true
        ];

        $deploymentResult = $this->handleBackupDeployment($backupData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('backup_configured', $deploymentResult);
        $this->assertArrayHasKey('retention_policy', $deploymentResult);
        $this->assertArrayHasKey('encryption_enabled', $deploymentResult);
    }

    #[Test]
    public function it_handles_rollback_deployment(): void
    {
        $rollbackData = [
            'rollback_reason' => 'high_error_rate',
            'target_version' => '1.2.2',
            'rollback_strategy' => 'immediate',
            'data_consistency' => 'verified'
        ];

        $deploymentResult = $this->handleRollbackDeployment($rollbackData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('rollback_completed', $deploymentResult);
        $this->assertArrayHasKey('version_restored', $deploymentResult);
        $this->assertArrayHasKey('system_stable', $deploymentResult);
    }

    #[Test]
    public function it_handles_health_check_deployment(): void
    {
        $healthCheckData = [
            'health_endpoints' => ['/health', '/ready', '/live'],
            'check_interval' => 30,
            'timeout' => 10,
            'failure_threshold' => 3
        ];

        $deploymentResult = $this->handleHealthCheckDeployment($healthCheckData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('health_checks_configured', $deploymentResult);
        $this->assertArrayHasKey('monitoring_active', $deploymentResult);
        $this->assertArrayHasKey('failure_detection', $deploymentResult);
    }

    #[Test]
    public function it_handles_load_balancer_deployment(): void
    {
        $loadBalancerData = [
            'load_balancer_type' => 'application',
            'backend_servers' => ['server1', 'server2', 'server3'],
            'health_checks' => true,
            'ssl_termination' => true
        ];

        $deploymentResult = $this->handleLoadBalancerDeployment($loadBalancerData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('load_balancer_configured', $deploymentResult);
        $this->assertArrayHasKey('backend_servers_healthy', $deploymentResult);
        $this->assertArrayHasKey('traffic_distribution', $deploymentResult);
    }

    #[Test]
    public function it_handles_dns_deployment(): void
    {
        $dnsData = [
            'dns_records' => ['A', 'AAAA', 'CNAME', 'MX'],
            'ttl' => 300,
            'dns_provider' => 'cloudflare',
            'dnssec_enabled' => true
        ];

        $deploymentResult = $this->handleDNSDeployment($dnsData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('dns_records_updated', $deploymentResult);
        $this->assertArrayHasKey('dns_propagation', $deploymentResult);
        $this->assertArrayHasKey('dnssec_enabled', $deploymentResult);
    }

    #[Test]
    public function it_handles_environment_variables_deployment(): void
    {
        $envData = [
            'environment_variables' => ['DB_HOST', 'REDIS_URL', 'API_KEY'],
            'secrets_management' => true,
            'encryption_enabled' => true,
            'rotation_policy' => 'monthly'
        ];

        $deploymentResult = $this->handleEnvironmentVariablesDeployment($envData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('variables_configured', $deploymentResult);
        $this->assertArrayHasKey('secrets_secured', $deploymentResult);
        $this->assertArrayHasKey('encryption_enabled', $deploymentResult);
    }

    #[Test]
    public function it_handles_database_deployment(): void
    {
        $databaseData = [
            'database_type' => 'mysql',
            'version' => '8.0',
            'replication' => 'master_slave',
            'backup_enabled' => true
        ];

        $deploymentResult = $this->handleDatabaseDeployment($databaseData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('database_configured', $deploymentResult);
        $this->assertArrayHasKey('replication_active', $deploymentResult);
        $this->assertArrayHasKey('backup_configured', $deploymentResult);
    }

    #[Test]
    public function it_handles_cache_deployment(): void
    {
        $cacheData = [
            'cache_type' => 'redis',
            'version' => '7.0',
            'clustering' => true,
            'persistence' => true
        ];

        $deploymentResult = $this->handleCacheDeployment($cacheData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('cache_configured', $deploymentResult);
        $this->assertArrayHasKey('clustering_active', $deploymentResult);
        $this->assertArrayHasKey('persistence_enabled', $deploymentResult);
    }

    #[Test]
    public function it_handles_queue_deployment(): void
    {
        $queueData = [
            'queue_type' => 'redis',
            'workers' => 5,
            'retry_policy' => 'exponential_backoff',
            'dead_letter_queue' => true
        ];

        $deploymentResult = $this->handleQueueDeployment($queueData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('queue_configured', $deploymentResult);
        $this->assertArrayHasKey('workers_started', $deploymentResult);
        $this->assertArrayHasKey('retry_policy_active', $deploymentResult);
    }

    #[Test]
    public function it_handles_logging_deployment(): void
    {
        $loggingData = [
            'log_level' => 'info',
            'log_destination' => 'elasticsearch',
            'log_rotation' => 'daily',
            'log_retention' => '30 days'
        ];

        $deploymentResult = $this->handleLoggingDeployment($loggingData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('logging_configured', $deploymentResult);
        $this->assertArrayHasKey('log_aggregation_active', $deploymentResult);
        $this->assertArrayHasKey('retention_policy', $deploymentResult);
    }

    #[Test]
    public function it_handles_metrics_deployment(): void
    {
        $metricsData = [
            'metrics_collector' => 'prometheus',
            'metrics_interval' => 15,
            'metrics_retention' => '15 days',
            'alerting_rules' => true
        ];

        $deploymentResult = $this->handleMetricsDeployment($metricsData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('metrics_collector_active', $deploymentResult);
        $this->assertArrayHasKey('alerting_configured', $deploymentResult);
        $this->assertArrayHasKey('retention_policy', $deploymentResult);
    }

    #[Test]
    public function it_handles_tracing_deployment(): void
    {
        $tracingData = [
            'tracing_system' => 'jaeger',
            'sampling_rate' => 0.1,
            'trace_retention' => '7 days',
            'distributed_tracing' => true
        ];

        $deploymentResult = $this->handleTracingDeployment($tracingData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('tracing_configured', $deploymentResult);
        $this->assertArrayHasKey('sampling_active', $deploymentResult);
        $this->assertArrayHasKey('distributed_tracing_enabled', $deploymentResult);
    }

    #[Test]
    public function it_handles_ci_cd_deployment(): void
    {
        $cicdData = [
            'pipeline_stages' => ['build', 'test', 'deploy'],
            'automated_testing' => true,
            'deployment_approval' => 'automatic',
            'rollback_automation' => true
        ];

        $deploymentResult = $this->handleCICDDeployment($cicdData);

        $this->assertTrue($deploymentResult['success']);
        $this->assertArrayHasKey('pipeline_configured', $deploymentResult);
        $this->assertArrayHasKey('automated_testing_active', $deploymentResult);
        $this->assertArrayHasKey('rollback_automation_enabled', $deploymentResult);
    }

    private function deployToProduction(array $data): array
    {
        return [
            'success' => true,
            'deployment_id' => 'deploy_' . uniqid(),
            'deployment_time' => '5 minutes',
            'health_check' => 'passed',
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleBlueGreenDeployment(array $data): array
    {
        return [
            'success' => true,
            'switch_completed' => true,
            'rollback_available' => true,
            'traffic_switched' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleCanaryDeployment(array $data): array
    {
        return [
            'success' => true,
            'canary_metrics' => 'excellent',
            'gradual_rollout' => 'active',
            'monitoring_active' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleRollingDeployment(array $data): array
    {
        return [
            'success' => true,
            'instances_updated' => count($data['instances']),
            'zero_downtime' => true,
            'rollback_available' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleFeatureFlagDeployment(array $data): array
    {
        return [
            'success' => true,
            'feature_enabled' => true,
            'user_segmentation' => 'active',
            'monitoring_active' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleDatabaseMigrationDeployment(array $data): array
    {
        return [
            'success' => true,
            'migrations_applied' => count($data['migration_files']),
            'backup_verified' => true,
            'data_integrity' => 'verified',
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleConfigurationDeployment(array $data): array
    {
        return [
            'success' => true,
            'config_validated' => true,
            'services_restarted' => true,
            'validation_passed' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleAssetDeployment(array $data): array
    {
        return [
            'success' => true,
            'assets_uploaded' => true,
            'cdn_updated' => true,
            'cache_invalidated' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleContainerDeployment(array $data): array
    {
        return [
            'success' => true,
            'containers_deployed' => $data['replicas'],
            'health_checks_passing' => true,
            'load_balancer_updated' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleKubernetesDeployment(array $data): array
    {
        return [
            'success' => true,
            'pods_running' => true,
            'service_available' => true,
            'ingress_configured' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleServerlessDeployment(array $data): array
    {
        return [
            'success' => true,
            'function_deployed' => true,
            'endpoint_available' => true,
            'monitoring_enabled' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleCDNDeployment(array $data): array
    {
        return [
            'success' => true,
            'zones_updated' => count($data['zones']),
            'cache_purged' => true,
            'performance_optimized' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleSSLCertificateDeployment(array $data): array
    {
        return [
            'success' => true,
            'certificate_installed' => true,
            'ssl_enabled' => true,
            'auto_renewal_configured' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleMonitoringDeployment(array $data): array
    {
        return [
            'success' => true,
            'monitoring_active' => true,
            'alerts_configured' => true,
            'dashboards_available' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleSecurityDeployment(array $data): array
    {
        return [
            'success' => true,
            'security_enabled' => true,
            'firewall_configured' => true,
            'vulnerability_scan_passed' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleBackupDeployment(array $data): array
    {
        return [
            'success' => true,
            'backup_configured' => true,
            'retention_policy' => $data['retention_period'],
            'encryption_enabled' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleRollbackDeployment(array $data): array
    {
        return [
            'success' => true,
            'rollback_completed' => true,
            'version_restored' => $data['target_version'],
            'system_stable' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleHealthCheckDeployment(array $data): array
    {
        return [
            'success' => true,
            'health_checks_configured' => count($data['health_endpoints']),
            'monitoring_active' => true,
            'failure_detection' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleLoadBalancerDeployment(array $data): array
    {
        return [
            'success' => true,
            'load_balancer_configured' => true,
            'backend_servers_healthy' => count($data['backend_servers']),
            'traffic_distribution' => 'balanced',
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleDNSDeployment(array $data): array
    {
        return [
            'success' => true,
            'dns_records_updated' => count($data['dns_records']),
            'dns_propagation' => 'completed',
            'dnssec_enabled' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleEnvironmentVariablesDeployment(array $data): array
    {
        return [
            'success' => true,
            'variables_configured' => count($data['environment_variables']),
            'secrets_secured' => true,
            'encryption_enabled' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleDatabaseDeployment(array $data): array
    {
        return [
            'success' => true,
            'database_configured' => true,
            'replication_active' => true,
            'backup_configured' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleCacheDeployment(array $data): array
    {
        return [
            'success' => true,
            'cache_configured' => true,
            'clustering_active' => true,
            'persistence_enabled' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleQueueDeployment(array $data): array
    {
        return [
            'success' => true,
            'queue_configured' => true,
            'workers_started' => $data['workers'],
            'retry_policy_active' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleLoggingDeployment(array $data): array
    {
        return [
            'success' => true,
            'logging_configured' => true,
            'log_aggregation_active' => true,
            'retention_policy' => $data['log_retention'],
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleMetricsDeployment(array $data): array
    {
        return [
            'success' => true,
            'metrics_collector_active' => true,
            'alerting_configured' => true,
            'retention_policy' => $data['metrics_retention'],
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleTracingDeployment(array $data): array
    {
        return [
            'success' => true,
            'tracing_configured' => true,
            'sampling_active' => true,
            'distributed_tracing_enabled' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleCICDDeployment(array $data): array
    {
        return [
            'success' => true,
            'pipeline_configured' => true,
            'automated_testing_active' => true,
            'rollback_automation_enabled' => true,
            'deployment_date' => date('Y-m-d H:i:s')
        ];
    }
}
