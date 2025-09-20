<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

class LinkCheckerCommand extends Command
{
    protected $signature = 'links:check {--external : Check external links} {--internal : Check internal links} {--all : Check all links}';

    protected $description = 'Check all links for broken URLs and accessibility issues';

    /** @var array<array<string, string|int|null>> */
    private array $brokenLinks = [];

    /** @var array<array<string, string|int|null>> */
    private array $workingLinks = [];

    private int $totalChecked = 0;

    public function handle(): int
    {
        $this->info('🔗 Starting comprehensive link checking...');

        if ($this->option('all') || $this->option('internal')) {
            $this->checkInternalLinks();
        }

        if ($this->option('all') || $this->option('external')) {
            $this->checkExternalLinks();
        }

        $this->generateReport();

        return $this->brokenLinks ? 1 : 0;
    }

    private function checkInternalLinks(): void
    {
        $this->info('🔍 Checking internal links...');

        /** @var \Illuminate\Routing\RouteCollection $routes */
        $routes = Route::getRoutes();
        $progressBar = $this->output->createProgressBar(count((array) $routes));

        foreach ((array) $routes as $route) {
            if ($route->methods()[0] === 'GET' && ! str_contains((string) $route->uri(), '{')) {
                // @phpstan-ignore-next-line
                $this->checkLink(url($route->uri()), $route->getName());
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }

    private function checkExternalLinks(): void
    {
        $this->info('🌐 Checking external links...');

        /** @var array<array<string, string>> $externalLinks */
        $externalLinks = $this->getExternalLinks();
        $progressBar = $this->output->createProgressBar(count($externalLinks));

        foreach ($externalLinks as $link) {
            $this->checkLink($link['url'], $link['name']);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }

    private function checkLink(string $url, ?string $name = null): void
    {
        $this->totalChecked++;

        try {
            $response = Http::timeout(10)->get($url);
            $status = $response->status();

            if ($status >= 400) {
                $this->brokenLinks[] = [
                    'url' => $url,
                    'name' => $name,
                    'status' => $status,
                    'type' => $this->getLinkType($url),
                ];
            } else {
                $this->workingLinks[] = [
                    'url' => $url,
                    'name' => $name,
                    'status' => $status,
                    'type' => $this->getLinkType($url),
                ];
            }
        } catch (\Exception $e) {
            $this->brokenLinks[] = [
                'url' => $url,
                'name' => $name,
                'status' => 'ERROR',
                'error' => $e->getMessage(),
                'type' => $this->getLinkType($url),
            ];
        }
    }

    private function getLinkType(string $url): string
    {
        if (str_starts_with($url, url('/'))) {
            return 'internal';
        }

        return 'external';
    }

    /** @return array<array<string, string>> */
    private function getExternalLinks(): array
    {
        // Get external links from various sources
        $links = [];

        // From configuration
        $configLinks = config('app.external_links', []);
        if (is_array($configLinks)) {
            foreach ($configLinks as $name => $url) {
                if (is_string($name) && is_string($url)) {
                    $links[] = ['name' => $name, 'url' => $url];
                }
            }
        }

        // From database (if any)
        // You can add database queries here to get external links

        // From static files
        /** @var array<array<string, string>> $staticLinks */
        $staticLinks = $this->getLinksFromStaticFiles();

        return array_merge($links, $staticLinks);
    }

    /** @return array<array<string, string>> */
    private function getLinksFromStaticFiles(): array
    {
        $links = [];

        // Check views for external links
        $viewFiles = File::allFiles(resource_path('views'));
        foreach ($viewFiles as $file) {
            $content = File::get($file->getPathname());
            preg_match_all('/href=["\'](https?:\/\/[^"\']+)["\']/', $content, $matches);
            foreach ($matches[1] as $url) {
                $links[] = ['name' => 'View Link', 'url' => $url];
            }
        }

        return $links;
    }

    private function generateReport(): void
    {
        $this->newLine();
        $this->info('📊 Link Check Report');
        $this->info('==================');

        $this->info("Total links checked: {$this->totalChecked}");
        $this->info('Working links: ' . count($this->workingLinks));
        $this->info('Broken links: ' . count($this->brokenLinks));

        if ($this->brokenLinks !== []) {
            $this->newLine();
            $this->error('❌ Broken Links Found:');
            $this->newLine();

            foreach ($this->brokenLinks as $link) {
                $this->line("URL: {$link['url']}");
                if ($link['name']) {
                    $this->line("Name: {$link['name']}");
                }
                $this->line("Status: {$link['status']}");
                if (isset($link['error'])) {
                    $this->line("Error: {$link['error']}");
                }
                $this->line("Type: {$link['type']}");
                $this->line('---');
            }
        } else {
            $this->newLine();
            $this->info('✅ All links are working correctly!');
        }

        // Save report to file
        $this->saveReportToFile();
    }

    private function saveReportToFile(): void
    {
        $report = [
            'timestamp' => now()->toISOString(),
            'total_checked' => $this->totalChecked,
            'working_links' => count($this->workingLinks),
            'broken_links' => count($this->brokenLinks),
            'broken_links_details' => $this->brokenLinks,
            'working_links_details' => $this->workingLinks,
        ];

        $reportPath = storage_path('logs/link-check-report.json');
        File::put($reportPath, (string) json_encode($report, JSON_PRETTY_PRINT));

        $this->info("📁 Report saved to: {$reportPath}");
    }
}
