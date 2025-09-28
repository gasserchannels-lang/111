<?php

namespace App\Console\Commands;

use App\Services\ProcessService;
use Illuminate\Console\Command;

class AgentProposeFixCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agent:propose-fix {--type=style : The type of issue to fix (e.g., style, analysis)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Propose automated fixes via Pull Request for different types of issues';

    /**
     * Create a new command instance.
     */
    public function __construct(/**
     * The process service instance.
     */
        private readonly ProcessService $processService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');

        $this->info("🚀 Starting Agent Propose Fix process for type: {$type}");

        // Generate unique branch name with timestamp
        $timestamp = now()->format('Y-m-d-H-i-s');
        $branchName = "fix/{$type}-fixes-{$timestamp}";

        $this->info("📝 Generated branch name: {$branchName}");

        // Step 1: Create and switch to new branch
        if (! $this->createBranch($branchName)) {
            return 1;
        }

        // Step 2: Run the appropriate fixer based on type
        $fixResult = $this->runFixer($type ?? 'style');
        if ($fixResult === false) {
            return 1;
        }

        // Step 3: Stage all changes
        if (! $this->stageChanges()) {
            return 1;
        }

        // Step 4: Commit changes
        $this->commitChanges($type ?? 'style');

        // Step 5: Push the new branch to remote
        if (! $this->pushBranch($branchName)) {
            return 1;
        }

        // Step 6: Create Pull Request
        if (! $this->createPullRequest($branchName, $type ?? 'style')) {
            return 1;
        }

        $this->info('🎉 Agent Propose Fix process completed successfully!');
        $this->info("✅ Branch '{$branchName}' has been pushed and Pull Request created.");

        return 0;
    }

    /**
     * Create and switch to a new branch.
     */
    private function createBranch(string $branchName): bool
    {
        $this->info('🌿 Creating and switching to new branch...');
        $checkoutResult = $this->processService->run("git checkout -b {$branchName}");

        if ($checkoutResult->failed()) {
            $this->error('❌ Failed to create branch: '.$checkoutResult->getErrorOutput());

            return false;
        }

        $this->info('✅ Branch created successfully');
        $this->info('Git output: '.$checkoutResult->getOutput());

        return true;
    }

    /**
     * Run the appropriate fixer based on the type.
     */
    private function runFixer(string $type): bool
    {
        return match ($type) {
            'style' => $this->runStyleFixer(),
            'analysis' => $this->runAnalysisFixer(),
            default => $this->handleUnsupportedType($type)
        };
    }

    /**
     * Run Laravel Pint for style fixes.
     */
    private function runStyleFixer(): bool
    {
        $this->info('🎨 Running Laravel Pint code style fixer...');
        $pintPath = implode(DIRECTORY_SEPARATOR, ['.', 'vendor', 'bin', 'pint']);
        $pintResult = $this->processService->run($pintPath);

        if ($pintResult->failed()) {
            $this->warn('⚠️ Pint encountered issues: '.$pintResult->getErrorOutput());
            $this->info('Pint output: '.$pintResult->getOutput());

            return true;
        }

        $this->info('✅ Pint completed successfully');
        $this->info('Pint output: '.$pintResult->getOutput());

        return true;
    }

    /**
     * Run PHPStan to generate baseline for static analysis fixes.
     */
    private function runAnalysisFixer(): bool
    {
        $this->info('🔍 Running PHPStan to generate a baseline...');

        // Define the path to the baseline file
        $baselinePath = 'phpstan-baseline.neon';

        // Check if baseline file exists, create empty one if not
        if (! file_exists($baselinePath)) {
            $this->info('INFO: Baseline file not found. Creating an empty one.');
            file_put_contents($baselinePath, '');
        }

        // Define OS-agnostic path for PHPStan
        $phpstanPath = implode(DIRECTORY_SEPARATOR, ['.', 'vendor', 'bin', 'phpstan']);

        // Run PHPStan with --generate-baseline and increased memory limit
        $phpstanResult = $this->processService->run(['php', '-d', 'memory_limit=512M', $phpstanPath, 'analyse', '--generate-baseline']);

        if ($phpstanResult->failed()) {
            $this->error('❌ PHPStan baseline generation failed: '.$phpstanResult->getErrorOutput());
            $this->error('PHPStan output: '.$phpstanResult->getOutput());

            return false;
        }

        $this->info('✅ PHPStan baseline generated successfully');
        $this->info('PHPStan output: '.$phpstanResult->getOutput());

        return true;
    }

    /**
     * Handle unsupported fix types.
     */
    private function handleUnsupportedType(string $type): bool
    {
        $this->error("❌ The fix type '{$type}' is not yet supported.");
        $this->info('Supported types: style, analysis');

        return false;
    }

    /**
     * Stage all changes.
     */
    private function stageChanges(): bool
    {
        $this->info('📦 Staging all changes...');
        $addResult = $this->processService->run('git add .');

        if ($addResult->failed()) {
            $this->error('❌ Failed to stage changes: '.$addResult->getErrorOutput());

            return false;
        }

        $this->info('✅ Changes staged successfully');
        $this->info('Git add output: '.$addResult->getOutput());

        return true;
    }

    /**
     * Commit changes with dynamic message based on type.
     */
    private function commitChanges(string $type): void
    {
        $commitMessage = $this->getCommitMessage($type);

        $this->info('💾 Committing changes...');
        $commitResult = $this->processService->run("git commit -m \"{$commitMessage}\"");

        if ($commitResult->failed()) {
            $this->warn('⚠️ No changes to commit or commit failed: '.$commitResult->getErrorOutput());
            $this->info('Git commit output: '.$commitResult->getOutput());

            return;
        }

        $this->info('✅ Changes committed successfully');
        $this->info('Git commit output: '.$commitResult->getOutput());
    }

    /**
     * Push branch to remote.
     */
    private function pushBranch(string $branchName): bool
    {
        $this->info('🚀 Pushing branch to remote repository...');
        $pushResult = $this->processService->run("git push --set-upstream origin {$branchName}");

        if ($pushResult->failed()) {
            $this->error('❌ Failed to push branch: '.$pushResult->getErrorOutput());

            return false;
        }

        $this->info('✅ Branch pushed successfully');
        $this->info('Git push output: '.$pushResult->getOutput());

        return true;
    }

    /**
     * Create Pull Request with dynamic title and body.
     */
    private function createPullRequest(string $branchName, string $type): bool
    {
        $prTitle = $this->getPullRequestTitle($type);
        $prBody = $this->getPullRequestBody($type);

        $this->info('🔗 Creating Pull Request...');
        $prResult = $this->processService->run([
            'gh', 'pr', 'create',
            '--base', 'main',
            '--head', $branchName,
            '--title', $prTitle,
            '--body', $prBody,
        ]);

        if ($prResult->failed()) {
            $this->error('❌ Failed to create Pull Request: '.$prResult->getErrorOutput());
            $this->warn('⚠️ Branch was pushed successfully, but PR creation failed.');
            $this->warn('You can manually create the PR at: https://github.com/your-repo/compare/main...'.$branchName);

            return false;
        }

        $this->info('✅ Pull Request created successfully');
        $this->info('PR output: '.$prResult->getOutput());

        return true;
    }

    /**
     * Get commit message based on type.
     */
    private function getCommitMessage(string $type): string
    {
        return match ($type) {
            'style' => 'style: Apply automated code style fixes',
            'analysis' => 'refactor: Generate PHPStan baseline',
            default => "fix: Apply automated {$type} fixes"
        };
    }

    /**
     * Get Pull Request title based on type.
     */
    private function getPullRequestTitle(string $type): string
    {
        return match ($type) {
            'style' => 'Automated Style Fixes',
            'analysis' => 'Automated Static Analysis Fixes: PHPStan Baseline',
            default => "Automated {$type} Fixes"
        };
    }

    /**
     * Get Pull Request body based on type.
     */
    private function getPullRequestBody(string $type): string
    {
        return match ($type) {
            'style' => 'This PR was automatically generated by the AI agent to apply code style fixes found by Laravel Pint.',
            'analysis' => 'This PR was automatically generated by the AI agent to create a PHPStan baseline file for static analysis.',
            default => "This PR was automatically generated by the AI agent to apply {$type} fixes."
        };
    }
}
