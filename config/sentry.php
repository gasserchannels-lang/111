<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),
    'release' => env('SENTRY_RELEASE'),
    'environment' => env('APP_ENV', 'production'),
    'sample_rate' => (float) env('SENTRY_SAMPLE_RATE', 0.1),
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.1),
    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.1),
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),
    'max_breadcrumbs' => (int) env('SENTRY_MAX_BREADCRUMBS', 50),
    'attach_stacktrace' => env('SENTRY_ATTACH_STACKTRACE', true),
    'ignore_exceptions' => [
        'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
        'Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException',
    ],
    'ignore_transactions' => [
        'GET /health',
        'GET /ping',
    ],
    // 'before_send' => function (\Sentry\Event $event, ?\Sentry\EventHint $hint): ?\Sentry\Event {
    //     return $event;
    // },
    // 'before_send_transaction' => function (\Sentry\Event $event, ?\Sentry\EventHint $hint): ?\Sentry\Event {
    //     return $event;
    // },
    'tags' => [
        'environment' => env('APP_ENV', 'production'),
        'version' => env('APP_VERSION', '1.0.0'),
    ],
    'context_lines' => (int) env('SENTRY_CONTEXT_LINES', 5),
    'max_value_length' => (int) env('SENTRY_MAX_VALUE_LENGTH', 1024),
    'http_proxy' => env('SENTRY_HTTP_PROXY'),
    'http_timeout' => (float) env('SENTRY_HTTP_TIMEOUT', 2.0),
    'http_connect_timeout' => (float) env('SENTRY_HTTP_CONNECT_TIMEOUT', 2.0),
    'capture_silenced_errors' => env('SENTRY_CAPTURE_SILENCED_ERRORS', false),
    'error_types' => (int) env('SENTRY_ERROR_TYPES', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED),
    'max_request_body_size' => env('SENTRY_MAX_REQUEST_BODY_SIZE', 'medium'),
    'class_serializers' => [
        // Add custom class serializers here
    ],
];
