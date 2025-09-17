<?php

/**
 * Code Dependency Analyzer Tool
 */

class CodeDependencyAnalyzer
{
    public function analyzeDependencies(string $directory): array
    {
        $dependencies = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                $fileDependencies = $this->extractDependencies($content);
                $dependencies[$file->getPathname()] = $fileDependencies;
            }
        }

        return $dependencies;
    }

    private function extractDependencies(string $content): array
    {
        $dependencies = [];

        // Extract use statements
        if (preg_match_all('/use\s+([^;]+);/', $content, $matches)) {
            $dependencies['uses'] = $matches[1];
        }

        // Extract extends
        if (preg_match('/class\s+\w+\s+extends\s+(\w+)/', $content, $matches)) {
            $dependencies['extends'] = $matches[1];
        }

        // Extract implements
        if (preg_match('/implements\s+([^{]+)/', $content, $matches)) {
            $dependencies['implements'] = array_map('trim', explode(',', $matches[1]));
        }

        return $dependencies;
    }
}
