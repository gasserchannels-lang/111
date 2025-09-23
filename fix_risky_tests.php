<?php

/**
 * Script to fix risky tests by updating test classes to use proper error handler management
 */
class RiskyTestFixer
{
    private $testDirectory;

    private $baseTestCase = 'Tests\\TestCase';

    private $filesProcessed = 0;

    private $filesUpdated = 0;

    public function __construct($testDirectory = 'tests')
    {
        $this->testDirectory = $testDirectory;
    }

    public function fixAllTests()
    {
        echo "Starting risky test fixes...\n";
        echo "Base TestCase: {$this->baseTestCase}\n";
        echo "Test Directory: {$this->testDirectory}\n\n";

        $this->processDirectory($this->testDirectory);

        echo "\n=== Summary ===\n";
        echo "Files processed: {$this->filesProcessed}\n";
        echo "Files updated: {$this->filesUpdated}\n";
        echo "Done!\n";
    }

    private function processDirectory($directory)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $this->processTestFile($file->getPathname());
            }
        }
    }

    private function processTestFile($filePath)
    {
        $this->filesProcessed++;
        $content = file_get_contents($filePath);
        $originalContent = $content;

        // Skip if not a test file
        if (! str_contains($filePath, 'Test.php')) {
            return;
        }

        echo 'Processing: '.basename($filePath)."\n";

        // Fix namespace and use statements
        $content = $this->fixNamespaceAndImports($content);

        // Fix class declaration
        $content = $this->fixClassDeclaration($content);

        // Add proper setUp and tearDown methods
        $content = $this->addErrorHandlerMethods($content);

        // Fix any direct error handler calls
        $content = $this->fixErrorHandlerCalls($content);

        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $this->filesUpdated++;
            echo "  ✓ Updated\n";
        } else {
            echo "  - No changes needed\n";
        }
    }

    private function fixNamespaceAndImports($content)
    {
        // Ensure proper namespace
        if (! str_contains($content, 'namespace Tests;') && ! str_contains($content, 'namespace Tests\\')) {
            $content = str_replace('<?php', "<?php\n\nnamespace Tests;", $content);
        }

        // Add use statement for base TestCase if not present
        if (! str_contains($content, 'use Tests\\TestCase;') && ! str_contains($content, 'extends TestCase')) {
            $content = str_replace(
                'namespace Tests;',
                "namespace Tests;\n\nuse Tests\\TestCase;",
                $content
            );
        }

        return $content;
    }

    private function fixClassDeclaration($content)
    {
        // Fix class declarations to extend our base TestCase
        $patterns = [
            '/class\s+(\w+Test)\s+extends\s+PHPUnit\\\\Framework\\\\TestCase/',
            '/class\s+(\w+Test)\s+extends\s+TestCase/',
        ];

        foreach ($patterns as $pattern) {
            $content = preg_replace($pattern, 'class $1 extends TestCase', $content);
        }

        return $content;
    }

    private function addErrorHandlerMethods($content)
    {
        // Check if setUp method exists
        if (! str_contains($content, 'protected function setUp(): void')) {
            $content = $this->addSetUpMethod($content);
        }

        // Check if tearDown method exists
        if (! str_contains($content, 'protected function tearDown(): void')) {
            $content = $this->addTearDownMethod($content);
        }

        return $content;
    }

    private function addSetUpMethod($content)
    {
        $setUpMethod = '
    protected function setUp(): void
    {
        parent::setUp();
        // Error handlers are managed by the base TestCase
    }';

        // Find the last closing brace of the class and add before it
        $lastBrace = strrpos($content, '}');
        if ($lastBrace !== false) {
            $content = substr_replace($content, $setUpMethod."\n", $lastBrace, 0);
        }

        return $content;
    }

    private function addTearDownMethod($content)
    {
        $tearDownMethod = '
    protected function tearDown(): void
    {
        // Error handlers are restored by the base TestCase
        parent::tearDown();
    }';

        // Find the last closing brace of the class and add before it
        $lastBrace = strrpos($content, '}');
        if ($lastBrace !== false) {
            $content = substr_replace($content, $tearDownMethod."\n", $lastBrace, 0);
        }

        return $content;
    }

    private function fixErrorHandlerCalls($content)
    {
        // Replace direct error handler calls with safe versions
        $replacements = [
            'set_error_handler(' => '$this->setTestErrorHandler(',
            'set_exception_handler(' => '$this->setTestExceptionHandler(',
            'restore_error_handler();' => '$this->restoreOriginalErrorHandlers();',
            'restore_exception_handler();' => '$this->restoreOriginalErrorHandlers();',
        ];

        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }
}

// Run the fixer
$fixer = new RiskyTestFixer;
$fixer->fixAllTests();
