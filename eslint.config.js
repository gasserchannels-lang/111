import js from '@eslint/js';

export default [
    js.configs.recommended,
    {
        files: ['resources/js/**/*.js'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: {
                console: 'readonly',
                process: 'readonly',
                Buffer: 'readonly',
                __dirname: 'readonly',
                __filename: 'readonly',
                module: 'readonly',
                require: 'readonly',
                exports: 'readonly',
                global: 'readonly',
                window: 'readonly',
                document: 'readonly',
                localStorage: 'readonly',
                sessionStorage: 'readonly',
                navigator: 'readonly',
                fetch: 'readonly',
                screen: 'readonly',
                performance: 'readonly',
                $: 'readonly',
                jQuery: 'readonly',
                d3: 'readonly',
                fastdom: 'readonly',
                hljs: 'readonly',
                Telescope: 'readonly',
                SwaggerUIBundle: 'readonly',
                SwaggerUIStandalonePreset: 'readonly',
                Sfdump: 'readonly',
                PhpDebugBar: 'readonly',
                Livewire: 'readonly',
                Alpine: 'readonly',
            },
        },
        rules: {
            indent: ['error', 4],
            'linebreak-style': ['error', 'unix'],
            quotes: ['error', 'single'],
            semi: ['error', 'always'],
            'no-unused-vars': 'warn',
            'no-console': 'warn',
            'no-debugger': 'error',
        },
    },
    {
        files: ['public/sw.js'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: {
                self: 'readonly',
                console: 'readonly',
                caches: 'readonly',
                URL: 'readonly',
                location: 'readonly',
                fetch: 'readonly',
                Response: 'readonly',
                clients: 'readonly',
            },
        },
    },
    {
        ignores: [
            // Dependencies
            'node_modules/',
            'vendor/',

            // Build files
            'public/build/',
            'public/hot/',
            'public/storage/',

            // Laravel specific
            'bootstrap/cache/',
            'storage/',

            // Compiled assets
            'public/css/',
            'public/js/',
            'public/mix-manifest.json',

            // Coverage
            'coverage/',
            '.nyc_output/',

            // Cache
            'storage/framework/cache/',
            'storage/framework/sessions/',
            'storage/framework/views/',

            // Logs
            'storage/logs/',

            // IDE
            '.vscode/',
            '.idea/',

            // OS
            '.DS_Store',
            'Thumbs.db',

            // Temporary files
            '*.tmp',
            '*.temp',
            '*.log',

            // PHP files
            '*.php',
            '*.blade.php',

            // Configuration files
            '*.config.js',
            '*.config.ts',
            'vite.config.js',
            'vite.config.ts',

            // Test files
            'tests/',
            '*.test.js',
            '*.test.ts',
            '*.spec.js',
            '*.spec.ts',

            // Documentation
            'docs/',
            '*.md',

            // Artifacts
            'artifacts/',

            // Git
            '.git/',

            // Environment
            '.env*',

            // Composer
            'composer.lock',
            'composer.phar',

            // NPM
            'package-lock.json',
            'yarn.lock',

            // PHP
            '.phpunit.result.cache',
            '.php_cs.cache',
            '.php-cs-fixer.cache',
        ],
    },
];
