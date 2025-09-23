<?php

/**
 * Test runner script that handles risky tests gracefully
 */
class TestRunner
{
    private $phpunitPath;

    private $configFile;

    private $outputFile;

    private $verbose = false;

    public function __construct()
    {
        $this->phpunitPath = 'vendor/bin/phpunit';
        $this->configFile = 'phpunit-no-risky.xml';
        $this->outputFile = 'test-results-no-risky.txt';
    }

    public function runTests($filter = null, $verbose = false)
    {
        $this->verbose = $verbose;

        echo "Running tests with risky test handling...\n";
        echo "Configuration: {$this->configFile}\n";
        echo "Output file: {$this->outputFile}\n\n";

        $command = $this->buildCommand($filter);

        if ($this->verbose) {
            echo "Command: {$command}\n\n";
        }

        // Run the tests
        $output = [];
        $returnCode = 0;
        exec($command.' 2>&1', $output, $returnCode);

        // Save output to file
        file_put_contents($this->outputFile, implode("\n", $output));

        // Display results
        $this->displayResults($output, $returnCode);

        return $returnCode === 0;
    }

    private function buildCommand($filter = null)
    {
        $command = "{$this->phpunitPath} --configuration {$this->configFile}";

        if ($filter) {
            $command .= " --filter {$filter}";
        }

        if ($this->verbose) {
            $command .= ' --verbose';
        }

        return $command;
    }

    private function displayResults($output, $returnCode)
    {
        echo "=== Test Results ===\n";

        // Count tests, assertions, warnings, risky tests
        $testCount = 0;
        $assertionCount = 0;
        $warningCount = 0;
        $riskyCount = 0;
        $skippedCount = 0;

        foreach ($output as $line) {
            if (preg_match('/Tests: (\d+), Assertions: (\d+), Warnings: (\d+), Skipped: (\d+), Risky: (\d+)/', $line, $matches)) {
                $testCount = $matches[1];
                $assertionCount = $matches[2];
                $warningCount = $matches[3];
                $skippedCount = $matches[4];
                $riskyCount = $matches[5];
                break;
            }
        }

        echo "Tests: {$testCount}\n";
        echo "Assertions: {$assertionCount}\n";
        echo "Warnings: {$warningCount}\n";
        echo "Skipped: {$skippedCount}\n";
        echo "Risky: {$riskyCount}\n";
        echo "Return Code: {$returnCode}\n";

        if ($riskyCount > 0) {
            echo "\n⚠️  {$riskyCount} risky tests detected but handled gracefully\n";
        }

        if ($returnCode === 0) {
            echo "\n✅ All tests passed!\n";
        } else {
            echo "\n❌ Some tests failed\n";
        }

        echo "\nFull output saved to: {$this->outputFile}\n";
    }

    public function runSpecificTest($testName)
    {
        return $this->runTests($testName, true);
    }

    public function runUnitTests()
    {
        return $this->runTests('Unit', true);
    }

    public function runFeatureTests()
    {
        return $this->runTests('Feature', true);
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $runner = new TestRunner;

    $args = $argv;
    array_shift($args); // Remove script name

    if (empty($args)) {
        // Run all tests
        $success = $runner->runTests();
        exit($success ? 0 : 1);
    }

    $command = $args[0];

    switch ($command) {
        case 'unit':
            $success = $runner->runUnitTests();
            break;
        case 'feature':
            $success = $runner->runFeatureTests();
            break;
        case 'filter':
            $filter = $args[1] ?? null;
            $success = $runner->runSpecificTest($filter);
            break;
        default:
            echo "Usage: php run-tests-no-risky.php [unit|feature|filter <name>]\n";
            exit(1);
    }

    exit($success ? 0 : 1);
}
