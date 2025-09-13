/* eslint-env node */
/* global module */
module.exports = {
    env: {
        browser: true,
        es2021: true,
        node: true,
        serviceworker: true,
    },
    extends: ['eslint:recommended'],
    parserOptions: {
        ecmaVersion: 'latest',
        sourceType: 'module',
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
    globals: {
        process: 'readonly',
        Buffer: 'readonly',
        __dirname: 'readonly',
        __filename: 'readonly',
        module: 'readonly',
        require: 'readonly',
        exports: 'readonly',
        global: 'readonly',
        document: 'readonly',
        localStorage: 'readonly',
        self: 'readonly',
        caches: 'readonly',
        URL: 'readonly',
        location: 'readonly',
        fetch: 'readonly',
        Response: 'readonly',
        clients: 'readonly',
    },
};
