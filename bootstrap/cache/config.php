<?php return array (
  'concurrency' => 
  array (
    'default' => 'process',
  ),
  'ai' => 
  array (
    'api_key' => NULL,
    'base_url' => 'https://api.openai.com/v1',
    'timeout' => 30,
    'max_tokens' => 2000,
    'temperature' => 0.5,
    'models' => 
    array (
      'text' => 'gpt-3.5-turbo',
      'image' => 'gpt-4-vision-preview',
      'embedding' => 'text-embedding-ada-002',
    ),
    'cache' => 
    array (
      'enabled' => true,
      'ttl' => 3600,
      'prefix' => 'ai_',
    ),
    'rate_limit' => 
    array (
      'enabled' => true,
      'max_requests' => 100,
      'per_minutes' => 60,
    ),
    'fallback' => 
    array (
      'enabled' => true,
      'default_responses' => 
      array (
        'product_classification' => 'غير محدد',
        'sentiment' => 'محايد',
        'recommendations' => 
        array (
        ),
      ),
    ),
  ),
  'app' => 
  array (
    'name' => 'Coprra',
    'env' => 'testing',
    'debug' => false,
    'url' => 'https://coprra.com',
    'frontend_url' => 'http://localhost:3000',
    'asset_url' => NULL,
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'cipher' => 'aes-256-cbc',
    'key' => 'base64:6SBUiXBfKiA7xBY7kp5FHRwt4vNryceJS20tI1BAooQ=',
    'previous_keys' => 
    array (
    ),
    'maintenance' => 
    array (
      'driver' => 'file',
    ),
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Cookie\\CookieServiceProvider',
      6 => 'Illuminate\\Database\\DatabaseServiceProvider',
      7 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      8 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      9 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      10 => 'Illuminate\\Hashing\\HashServiceProvider',
      11 => 'Illuminate\\Mail\\MailServiceProvider',
      12 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      13 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      14 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      15 => 'Illuminate\\Queue\\QueueServiceProvider',
      16 => 'Illuminate\\Redis\\RedisServiceProvider',
      17 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      18 => 'Illuminate\\Session\\SessionServiceProvider',
      19 => 'Illuminate\\Translation\\TranslationServiceProvider',
      20 => 'Illuminate\\Validation\\ValidationServiceProvider',
      21 => 'Illuminate\\View\\ViewServiceProvider',
      22 => 'App\\Providers\\AppServiceProvider',
      23 => 'App\\Providers\\AuthServiceProvider',
      24 => 'App\\Providers\\EventServiceProvider',
      25 => 'App\\Providers\\RouteServiceProvider',
      26 => 'App\\Providers\\CoprraServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Arr' => 'Illuminate\\Support\\Arr',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'Date' => 'Illuminate\\Support\\Facades\\Date',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Http' => 'Illuminate\\Support\\Facades\\Http',
      'Js' => 'Illuminate\\Support\\Js',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Process' => 'Illuminate\\Support\\Facades\\Process',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'RateLimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'Str' => 'Illuminate\\Support\\Str',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
    ),
    'cdn_url' => 'https://coprra.com.cdn.hstgr.net',
    'php_memory_limit' => '2048M',
    'php_max_execution_time' => 360,
    'php_upload_max_filesize' => '2048M',
    'php_post_max_size' => '2048M',
    'deployment' => 
    array (
      'ssh_host' => NULL,
      'ssh_port' => NULL,
      'ssh_username' => NULL,
      'ssh_path' => NULL,
      'backup_enabled' => true,
      'backup_path' => NULL,
    ),
  ),
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'sanctum' => 
      array (
        'driver' => 'sanctum',
        'provider' => NULL,
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\User',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
      ),
    ),
    'password_timeout' => 10800,
  ),
  'broadcasting' => 
  array (
    'default' => 'null',
    'connections' => 
    array (
      'reverb' => 
      array (
        'driver' => 'reverb',
        'key' => NULL,
        'secret' => NULL,
        'app_id' => NULL,
        'options' => 
        array (
          'host' => NULL,
          'port' => 443,
          'scheme' => 'https',
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'pusher' => 
      array (
        'driver' => 'pusher',
        'key' => NULL,
        'secret' => NULL,
        'app_id' => NULL,
        'options' => 
        array (
          'cluster' => NULL,
          'host' => 'api-mt1.pusher.com',
          'port' => 443,
          'scheme' => 'https',
          'encrypted' => true,
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'ably' => 
      array (
        'driver' => 'ably',
        'key' => NULL,
      ),
      'log' => 
      array (
        'driver' => 'log',
      ),
      'null' => 
      array (
        'driver' => 'null',
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
      ),
    ),
  ),
  'cache' => 
  array (
    'default' => 'array',
    'stores' => 
    array (
      'array' => 
      array (
        'driver' => 'array',
        'serialize' => false,
      ),
      'session' => 
      array (
        'driver' => 'session',
        'key' => '_cache',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'cache',
        'connection' => NULL,
        'lock_connection' => NULL,
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => '/var/www/html/storage/framework/cache/data',
        'lock_path' => '/var/www/html/storage/framework/cache/data',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'persistent_id' => NULL,
        'sasl' => 
        array (
          0 => NULL,
          1 => NULL,
        ),
        'options' => 
        array (
        ),
        'servers' => 
        array (
          0 => 
          array (
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
      ),
      'dynamodb' => 
      array (
        'driver' => 'dynamodb',
        'key' => NULL,
        'secret' => NULL,
        'region' => 'us-east-1',
        'table' => 'cache',
        'endpoint' => NULL,
      ),
      'octane' => 
      array (
        'driver' => 'octane',
      ),
      'apc' => 
      array (
        'driver' => 'apc',
      ),
    ),
    'prefix' => 'laravel_cache_',
  ),
  'coprra' => 
  array (
    'name' => 'COPRRA',
    'version' => '1.0.0',
    'description' => 'Advanced Price Comparison Platform',
    'default_currency' => 'USD',
    'default_language' => 'en',
    'price_comparison' => 
    array (
      'cache_duration' => 3600,
      'max_stores_per_product' => 10,
      'price_update_interval' => 6,
    ),
    'search' => 
    array (
      'max_results' => 50,
      'min_query_length' => 2,
      'enable_autocomplete' => true,
    ),
    'user_preferences' => 
    array (
      'default_condition' => 'new',
      'max_price_range' => 10000,
      'min_price_range' => 1,
    ),
    'exchange_rates' => 
    array (
      'USD' => 1.0,
      'EUR' => 0.85,
      'GBP' => 0.73,
      'JPY' => 110.0,
      'SAR' => 3.75,
      'AED' => 3.67,
      'EGP' => 30.9,
    ),
    'pagination' => 
    array (
      'default_items_per_page' => 20,
      'max_wishlist_items' => 100,
      'max_price_alerts' => 50,
    ),
    'api' => 
    array (
      'rate_limit' => 100,
      'version' => 'v1',
      'enable_docs' => true,
    ),
    'media' => 
    array (
      'max_image_size' => 2048,
      'allowed_image_types' => 
      array (
        0 => 'jpg',
        1 => 'jpeg',
        2 => 'png',
        3 => 'webp',
      ),
      'default_product_image' => '/images/default-product.png',
      'default_store_logo' => '/images/default-store.png',
    ),
    'analytics' => 
    array (
      'google_analytics_id' => NULL,
      'track_user_behavior' => true,
      'track_price_clicks' => true,
    ),
    'security' => 
    array (
      'enable_2fa' => false,
      'password_min_length' => 8,
      'session_timeout' => 120,
    ),
    'performance' => 
    array (
      'enable_query_caching' => true,
      'enable_view_caching' => true,
      'enable_compression' => true,
    ),
  ),
  'cors' => 
  array (
    'paths' => 
    array (
      0 => 'api/*',
      1 => 'sanctum/csrf-cookie',
    ),
    'allowed_methods' => 
    array (
      0 => '*',
    ),
    'allowed_origins' => 
    array (
      0 => 'http://localhost',
    ),
    'allowed_origins_patterns' => 
    array (
    ),
    'allowed_headers' => 
    array (
      0 => '*',
    ),
    'exposed_headers' => 
    array (
    ),
    'max_age' => 0,
    'supports_credentials' => false,
  ),
  'database' => 
  array (
    'default' => 'sqlite',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'url' => NULL,
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'url' => NULL,
        'host' => 'localhost',
        'port' => '3306',
        'database' => ':memory:',
        'username' => NULL,
        'password' => NULL,
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'mariadb' => 
      array (
        'driver' => 'mariadb',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => ':memory:',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'pgsql' => 
      array (
        'driver' => 'pgsql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '5432',
        'database' => ':memory:',
        'username' => NULL,
        'password' => NULL,
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'search_path' => 'public',
        'sslmode' => 'prefer',
      ),
      'sqlsrv' => 
      array (
        'driver' => 'sqlsrv',
        'url' => NULL,
        'host' => 'localhost',
        'port' => '1433',
        'database' => ':memory:',
        'username' => NULL,
        'password' => NULL,
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
      ),
      'testing' => 
      array (
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
      ),
      'sqlite_testing' => 
      array (
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
      ),
    ),
    'migrations' => 'migrations',
    'redis' => 
    array (
      'client' => 'phpredis',
      'options' => 
      array (
        'cluster' => 'redis',
        'prefix' => 'laravel_database_',
      ),
      'default' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '0',
      ),
      'cache' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '1',
      ),
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'local',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => '/var/www/html/storage/app',
        'throw' => false,
      ),
      'public' => 
      array (
        'driver' => 'local',
        'root' => '/var/www/html/storage/app/public',
        'url' => '/storage',
        'visibility' => 'public',
        'throw' => false,
      ),
      's3' => 
      array (
        'driver' => 's3',
        'key' => NULL,
        'secret' => NULL,
        'region' => NULL,
        'bucket' => NULL,
        'url' => NULL,
        'endpoint' => NULL,
        'use_path_style_endpoint' => false,
        'throw' => false,
      ),
    ),
    'links' => 
    array (
      '/var/www/html/public/storage' => '/var/www/html/storage/app/public',
    ),
  ),
  'hashing' => 
  array (
    'driver' => 'bcrypt',
    'bcrypt' => 
    array (
      'rounds' => 12,
      'verify' => true,
    ),
    'argon' => 
    array (
      'memory' => 65536,
      'threads' => 1,
      'time' => 4,
      'verify' => true,
    ),
    'rehash_on_login' => true,
  ),
  'hostinger' => 
  array (
    'server' => 
    array (
      'ip' => '45.87.81.218',
      'name' => 'nl-srv-web480.main-hosting.eu',
      'location' => 'Netherlands',
      'resources' => 
      array (
        'cpu' => 2,
        'ram' => '2GB',
        'storage' => '20GB SSD',
      ),
    ),
    'ssh' => 
    array (
      'host' => '45.87.81.218',
      'port' => 65002,
      'username' => 'u990109832',
      'path' => '/home/u990109832/public_html',
    ),
    'ftp' => 
    array (
      'host' => '45.87.81.218',
      'username' => 'u990109832',
      'path' => 'public_html',
    ),
    'database' => 
    array (
      'host' => 'localhost',
      'port' => 3306,
      'database' => NULL,
      'username' => NULL,
      'password' => NULL,
    ),
    'mail' => 
    array (
      'host' => 'mail.hostinger.com',
      'port' => 587,
      'encryption' => 'tls',
      'username' => NULL,
      'password' => NULL,
    ),
    'ssl' => 
    array (
      'type' => 'Lifetime SSL (Google)',
      'status' => 'active',
      'created' => '2025-07-28',
      'expires' => 'never',
    ),
    'dns' => 
    array (
      'nameservers' => 
      array (
        0 => 'ns1.dns-parking.com',
        1 => 'ns2.dns-parking.com',
      ),
      'records' => 
      array (
        'a' => 
        array (
          '@' => 'coprra.com.cdn.hstgr.net',
          'ftp' => '45.87.81.218',
        ),
        'cname' => 
        array (
          'www' => 'www.coprra.com.cdn.hstgr.net',
          'autodiscover' => 'autodiscover.mail.hostinger.com',
          'autoconfig' => 'autoconfig.mail.hostinger.com',
        ),
        'mx' => 
        array (
          'mx1.hostinger.com' => 5,
          'mx2.hostinger.com' => 10,
        ),
        'txt' => 
        array (
          'spf' => 'v=spf1 include:_spf.mail.hostinger.com ~all',
          'dmarc' => 'v=DMARC1; p=none',
        ),
      ),
    ),
    'php' => 
    array (
      'version' => '8.2.28',
      'memory_limit' => '2048M',
      'max_execution_time' => 360,
      'upload_max_filesize' => '2048M',
      'post_max_size' => '2048M',
      'opcache' => 
      array (
        'enabled' => true,
        'memory_consumption' => '256M',
        'max_accelerated_files' => 16229,
        'jit' => 'tracing',
      ),
    ),
    'cdn' => 
    array (
      'url' => 'https://coprra.com.cdn.hstgr.net',
      'enabled' => true,
      'type' => 'Hostinger CDN',
      'function' => 'تسريع تحميل الملفات الثابتة',
    ),
    'backup' => 
    array (
      'enabled' => true,
      'path' => '/home/u990109832/backups',
      'retention_days' => 30,
    ),
  ),
  'l5-swagger' => 
  array (
    'default' => 'default',
    'documentations' => 
    array (
      'default' => 
      array (
        'api' => 
        array (
          'title' => 'L5 Swagger UI',
        ),
        'routes' => 
        array (
          'api' => 'api/documentation',
        ),
        'paths' => 
        array (
          'use_absolute_path' => true,
          'swagger_ui_assets_path' => 'vendor/swagger-api/swagger-ui/dist/',
          'docs_json' => 'api-docs.json',
          'docs_yaml' => 'api-docs.yaml',
          'format_to_use_for_docs' => 'json',
          'annotations' => 
          array (
            0 => '/var/www/html/app',
          ),
        ),
      ),
    ),
    'defaults' => 
    array (
      'routes' => 
      array (
        'docs' => 'docs',
        'oauth2_callback' => 'api/oauth2-callback',
        'middleware' => 
        array (
          'api' => 
          array (
          ),
          'asset' => 
          array (
          ),
          'docs' => 
          array (
          ),
          'oauth2_callback' => 
          array (
          ),
        ),
        'group_options' => 
        array (
        ),
      ),
      'paths' => 
      array (
        'docs' => '/var/www/html/storage/api-docs',
        'views' => '/var/www/html/resources/views/vendor/l5-swagger',
        'base' => NULL,
        'excludes' => 
        array (
        ),
      ),
      'scanOptions' => 
      array (
        'default_processors_configuration' => 
        array (
        ),
        'analyser' => NULL,
        'analysis' => NULL,
        'processors' => 
        array (
        ),
        'pattern' => NULL,
        'exclude' => 
        array (
        ),
        'open_api_spec_version' => '3.0.0',
      ),
      'securityDefinitions' => 
      array (
        'securitySchemes' => 
        array (
        ),
        'security' => 
        array (
          0 => 
          array (
          ),
        ),
      ),
      'generate_always' => false,
      'generate_yaml_copy' => false,
      'proxy' => false,
      'additional_config_url' => NULL,
      'operations_sort' => NULL,
      'validator_url' => NULL,
      'ui' => 
      array (
        'display' => 
        array (
          'dark_mode' => false,
          'doc_expansion' => 'none',
          'filter' => true,
        ),
        'authorization' => 
        array (
          'persist_authorization' => false,
          'oauth2' => 
          array (
            'use_pkce_with_authorization_code_grant' => false,
          ),
        ),
      ),
      'constants' => 
      array (
        'L5_SWAGGER_CONST_HOST' => 'http://my-default-host.com',
      ),
    ),
  ),
  'logging' => 
  array (
    'default' => 'stack',
    'deprecations' => 
    array (
      'channel' => 'null',
      'trace' => false,
    ),
    'channels' => 
    array (
      'stack' => 
      array (
        'driver' => 'stack',
        'channels' => 
        array (
          0 => 'single',
        ),
        'ignore_exceptions' => false,
      ),
      'single' => 
      array (
        'driver' => 'single',
        'path' => '/var/www/html/storage/logs/laravel.log',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'daily' => 
      array (
        'driver' => 'daily',
        'path' => '/var/www/html/storage/logs/laravel.log',
        'level' => 'debug',
        'days' => 14,
        'replace_placeholders' => true,
      ),
      'slack' => 
      array (
        'driver' => 'slack',
        'url' => NULL,
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'critical',
        'replace_placeholders' => true,
      ),
      'papertrail' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\SyslogUdpHandler',
        'handler_with' => 
        array (
          'host' => NULL,
          'port' => NULL,
          'connectionString' => 'tls://:',
        ),
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'stderr' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\StreamHandler',
        'formatter' => NULL,
        'with' => 
        array (
          'stream' => 'php://stderr',
        ),
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'syslog' => 
      array (
        'driver' => 'syslog',
        'level' => 'debug',
        'facility' => 8,
        'replace_placeholders' => true,
      ),
      'errorlog' => 
      array (
        'driver' => 'errorlog',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'null' => 
      array (
        'driver' => 'monolog',
        'handler' => 'Monolog\\Handler\\NullHandler',
      ),
      'emergency' => 
      array (
        'path' => '/var/www/html/storage/logs/laravel.log',
      ),
    ),
  ),
  'mail' => 
  array (
    'default' => 'smtp',
    'mailers' => 
    array (
      'smtp' => 
      array (
        'transport' => 'smtp',
        'url' => NULL,
        'host' => 'mail.hostinger.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => NULL,
        'password' => NULL,
        'timeout' => NULL,
        'local_domain' => NULL,
      ),
      'ses' => 
      array (
        'transport' => 'ses',
      ),
      'postmark' => 
      array (
        'transport' => 'postmark',
      ),
      'resend' => 
      array (
        'transport' => 'resend',
      ),
      'sendmail' => 
      array (
        'transport' => 'sendmail',
        'path' => '/usr/sbin/sendmail -bs -i',
      ),
      'log' => 
      array (
        'transport' => 'log',
        'channel' => NULL,
      ),
      'array' => 
      array (
        'transport' => 'array',
      ),
      'failover' => 
      array (
        'transport' => 'failover',
        'mailers' => 
        array (
          0 => 'smtp',
          1 => 'log',
        ),
      ),
      'roundrobin' => 
      array (
        'transport' => 'roundrobin',
        'mailers' => 
        array (
          0 => 'ses',
          1 => 'postmark',
        ),
      ),
      'mailgun' => 
      array (
        'transport' => 'mailgun',
      ),
    ),
    'from' => 
    array (
      'address' => 'contact@coprra.com',
      'name' => 'COPRRA',
    ),
    'markdown' => 
    array (
      'theme' => 'default',
      'paths' => 
      array (
        0 => '/var/www/html/resources/views/vendor/mail',
      ),
    ),
  ),
  'monitoring' => 
  array (
    'enabled' => true,
    'performance' => 
    array (
      'enabled' => true,
      'slow_query_threshold' => 1000,
      'memory_threshold' => 128,
      'execution_time_threshold' => 5,
    ),
    'errors' => 
    array (
      'enabled' => true,
      'report_level' => 'error',
      'max_errors_per_minute' => 100,
    ),
    'database' => 
    array (
      'enabled' => true,
      'slow_query_log' => true,
      'connection_monitoring' => true,
      'query_count_threshold' => 100,
    ),
    'cache' => 
    array (
      'enabled' => true,
      'hit_rate_threshold' => 0.8,
      'miss_rate_threshold' => 0.2,
    ),
    'queue' => 
    array (
      'enabled' => true,
      'failed_job_threshold' => 10,
      'queue_size_threshold' => 1000,
      'processing_time_threshold' => 300,
    ),
    'api' => 
    array (
      'enabled' => true,
      'rate_limit_threshold' => 1000,
      'response_time_threshold' => 2000,
      'error_rate_threshold' => 0.05,
    ),
    'storage' => 
    array (
      'enabled' => true,
      'disk_usage_threshold' => 80,
      'log_size_threshold' => 100,
      'temp_file_threshold' => 50,
    ),
    'alerts' => 
    array (
      'enabled' => true,
      'channels' => 
      array (
        'email' => 
        array (
          'enabled' => true,
          'recipients' => 
          array (
            0 => '',
          ),
        ),
        'slack' => 
        array (
          'enabled' => false,
          'webhook_url' => NULL,
          'channel' => '#alerts',
        ),
        'webhook' => 
        array (
          'enabled' => false,
          'url' => NULL,
        ),
      ),
    ),
    'metrics' => 
    array (
      'enabled' => true,
      'storage' => 'database',
      'retention_days' => 30,
      'collection_interval' => 60,
    ),
    'health_checks' => 
    array (
      'enabled' => true,
      'endpoint' => '/health',
      'checks' => 
      array (
        'database' => true,
        'cache' => true,
        'storage' => true,
        'queue' => true,
      ),
    ),
  ),
  'permission' => 
  array (
    'models' => 
    array (
      'permission' => 'Spatie\\Permission\\Models\\Permission',
      'role' => 'Spatie\\Permission\\Models\\Role',
    ),
    'table_names' => 
    array (
      'roles' => 'roles',
      'permissions' => 'permissions',
      'model_has_permissions' => 'model_has_permissions',
      'model_has_roles' => 'model_has_roles',
      'role_has_permissions' => 'role_has_permissions',
    ),
    'column_names' => 
    array (
      'role_pivot_key' => NULL,
      'permission_pivot_key' => NULL,
      'model_morph_key' => 'model_id',
      'team_foreign_key' => 'team_id',
    ),
    'register_permission_check_method' => true,
    'register_octane_reset_listener' => false,
    'events_enabled' => false,
    'teams' => false,
    'team_resolver' => 'Spatie\\Permission\\DefaultTeamResolver',
    'use_passport_client_credentials' => false,
    'display_permission_in_exception' => false,
    'display_role_in_exception' => false,
    'enable_wildcard_permission' => false,
    'cache' => 
    array (
      'expiration_time' => 
      \DateInterval::__set_state(array(
         'from_string' => true,
         'date_string' => '24 hours',
      )),
      'key' => 'spatie.permission.cache',
      'store' => 'default',
    ),
  ),
  'queue' => 
  array (
    'default' => 'sync',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
      ),
      'beanstalkd' => 
      array (
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => 0,
        'after_commit' => false,
      ),
      'sqs' => 
      array (
        'driver' => 'sqs',
        'key' => NULL,
        'secret' => NULL,
        'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
        'queue' => 'default',
        'suffix' => NULL,
        'region' => 'us-east-1',
        'after_commit' => false,
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => NULL,
        'after_commit' => false,
      ),
    ),
    'batching' => 
    array (
      'database' => 'sqlite',
      'table' => 'job_batches',
    ),
    'failed' => 
    array (
      'driver' => 'database-uuids',
      'database' => 'sqlite',
      'table' => 'failed_jobs',
    ),
  ),
  'sanctum' => 
  array (
    'stateful' => 
    array (
      0 => 'localhost',
      1 => 'localhost:3000',
      2 => '127.0.0.1',
      3 => '127.0.0.1:8000',
      4 => '::1',
      5 => '',
      6 => 'coprra.com',
    ),
    'guard' => 
    array (
      0 => 'web',
    ),
    'expiration' => NULL,
    'token_prefix' => '',
    'middleware' => 
    array (
      'verify_csrf_token' => 'App\\Http\\Middleware\\VerifyCsrfToken',
      'encrypt_cookies' => 'App\\Http\\Middleware\\EncryptCookies',
    ),
  ),
  'security' => 
  array (
    'headers' => 
    array (
      'X-Frame-Options' => 'SAMEORIGIN',
      'X-XSS-Protection' => '1; mode=block',
      'X-Content-Type-Options' => 'nosniff',
      'Referrer-Policy' => 'strict-origin-when-cross-origin',
      'Content-Security-Policy' => 'default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\'; style-src \'self\' \'unsafe-inline\';',
      'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), interest-cohort=()',
      'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    ),
    'passwords' => 
    array (
      'min_length' => 12,
      'require_numbers' => true,
      'require_symbols' => true,
      'require_uppercase' => true,
      'require_lowercase' => true,
      'prevent_common_passwords' => true,
      'max_attempts' => 5,
      'lockout_time' => 15,
    ),
    'uploads' => 
    array (
      'max_size' => 10240,
      'allowed_extensions' => 
      array (
        0 => 'jpg',
        1 => 'jpeg',
        2 => 'png',
        3 => 'gif',
        4 => 'pdf',
        5 => 'doc',
        6 => 'docx',
        7 => 'xls',
        8 => 'xlsx',
        9 => 'txt',
        10 => 'csv',
        11 => 'zip',
      ),
      'scan_for_viruses' => true,
      'sanitize_filenames' => true,
      'validate_mime_types' => true,
    ),
    'api' => 
    array (
      'throttle' => 
      array (
        'max_attempts' => 60,
        'decay_minutes' => 1,
      ),
      'require_https' => true,
      'token_expiration' => 60,
      'refresh_token_expiration' => 20160,
    ),
    'rate_limiting' => 
    array (
      'login' => 
      array (
        'max_attempts' => 5,
        'decay_minutes' => 15,
      ),
      'register' => 
      array (
        'max_attempts' => 3,
        'decay_minutes' => 60,
      ),
      'password_reset' => 
      array (
        'max_attempts' => 3,
        'decay_minutes' => 60,
      ),
      'api_requests' => 
      array (
        'max_attempts' => 1000,
        'decay_minutes' => 60,
      ),
      'ai_requests' => 
      array (
        'max_attempts' => 100,
        'decay_minutes' => 60,
      ),
    ),
    'scanning' => 
    array (
      'enabled' => true,
      'scan_uploads' => true,
      'scan_dependencies' => true,
      'notify_on_vulnerabilities' => true,
    ),
    'authentication' => 
    array (
      'require_2fa' => false,
      'session_lifetime' => 120,
      'remember_me_lifetime' => 20160,
      'password_history' => 5,
      'password_expiry' => 90,
    ),
  ),
  'sentry' => 
  array (
    'dsn' => NULL,
    'release' => NULL,
    'environment' => 'testing',
    'sample_rate' => 0.1,
    'traces_sample_rate' => 0.1,
    'profiles_sample_rate' => 0.1,
    'send_default_pii' => false,
    'max_breadcrumbs' => 50,
    'attach_stacktrace' => true,
    'ignore_exceptions' => 
    array (
      0 => 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException',
      1 => 'Symfony\\Component\\HttpKernel\\Exception\\MethodNotAllowedHttpException',
    ),
    'ignore_transactions' => 
    array (
      0 => 'GET /health',
      1 => 'GET /ping',
    ),
    'tags' => 
    array (
      'environment' => 'testing',
      'version' => '1.0.0',
    ),
    'context_lines' => 5,
    'max_value_length' => 1024,
    'http_proxy' => NULL,
    'http_timeout' => 2.0,
    'http_connect_timeout' => 2.0,
    'capture_silenced_errors' => false,
    'error_types' => 22519,
    'max_request_body_size' => 'medium',
    'class_serializers' => 
    array (
    ),
  ),
  'services' => 
  array (
    'postmark' => 
    array (
      'token' => NULL,
    ),
    'resend' => 
    array (
      'key' => NULL,
    ),
    'ses' => 
    array (
      'key' => NULL,
      'secret' => NULL,
      'region' => 'us-east-1',
    ),
    'slack' => 
    array (
      'notifications' => 
      array (
        'bot_user_oauth_token' => NULL,
        'channel' => NULL,
      ),
    ),
    'mailgun' => 
    array (
      'domain' => NULL,
      'secret' => NULL,
      'endpoint' => 'api.mailgun.net',
      'scheme' => 'https',
    ),
  ),
  'session' => 
  array (
    'driver' => 'array',
    'lifetime' => 1440,
    'expire_on_close' => false,
    'encrypt' => true,
    'files' => '/var/www/html/storage/framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'laravel_session',
    'path' => '/',
    'domain' => NULL,
    'secure' => true,
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
  ),
  'telescope' => 
  array (
    'enabled' => true,
    'domain' => NULL,
    'path' => 'telescope',
    'driver' => 'database',
    'storage' => 
    array (
      'database' => 
      array (
        'connection' => 'testing',
        'chunk' => 1000,
      ),
    ),
    'queue' => 
    array (
      'connection' => NULL,
      'queue' => NULL,
      'delay' => 10,
    ),
    'middleware' => 
    array (
      0 => 'web',
      1 => 'Laravel\\Telescope\\Http\\Middleware\\Authorize',
    ),
    'only_paths' => 
    array (
    ),
    'ignore_paths' => 
    array (
      0 => 'livewire*',
      1 => 'nova-api*',
      2 => 'pulse*',
    ),
    'ignore_commands' => 
    array (
    ),
    'watchers' => 
    array (
      'Laravel\\Telescope\\Watchers\\BatchWatcher' => true,
      'Laravel\\Telescope\\Watchers\\CacheWatcher' => 
      array (
        'enabled' => true,
        'hidden' => 
        array (
        ),
        'ignore' => 
        array (
        ),
      ),
      'Laravel\\Telescope\\Watchers\\ClientRequestWatcher' => true,
      'Laravel\\Telescope\\Watchers\\CommandWatcher' => 
      array (
        'enabled' => true,
        'ignore' => 
        array (
        ),
      ),
      'Laravel\\Telescope\\Watchers\\DumpWatcher' => 
      array (
        'enabled' => true,
        'always' => false,
      ),
      'Laravel\\Telescope\\Watchers\\EventWatcher' => 
      array (
        'enabled' => true,
        'ignore' => 
        array (
        ),
      ),
      'Laravel\\Telescope\\Watchers\\ExceptionWatcher' => true,
      'Laravel\\Telescope\\Watchers\\GateWatcher' => 
      array (
        'enabled' => true,
        'ignore_abilities' => 
        array (
        ),
        'ignore_packages' => true,
        'ignore_paths' => 
        array (
        ),
      ),
      'Laravel\\Telescope\\Watchers\\JobWatcher' => true,
      'Laravel\\Telescope\\Watchers\\LogWatcher' => 
      array (
        'enabled' => true,
        'level' => 'error',
      ),
      'Laravel\\Telescope\\Watchers\\MailWatcher' => true,
      'Laravel\\Telescope\\Watchers\\ModelWatcher' => 
      array (
        'enabled' => true,
        'events' => 
        array (
          0 => 'eloquent.*',
        ),
        'hydrations' => true,
      ),
      'Laravel\\Telescope\\Watchers\\NotificationWatcher' => true,
      'Laravel\\Telescope\\Watchers\\QueryWatcher' => 
      array (
        'enabled' => true,
        'ignore_packages' => true,
        'ignore_paths' => 
        array (
        ),
        'slow' => 100,
      ),
      'Laravel\\Telescope\\Watchers\\RedisWatcher' => true,
      'Laravel\\Telescope\\Watchers\\RequestWatcher' => 
      array (
        'enabled' => true,
        'size_limit' => 64,
        'ignore_http_methods' => 
        array (
        ),
        'ignore_status_codes' => 
        array (
        ),
      ),
      'Laravel\\Telescope\\Watchers\\ScheduleWatcher' => true,
      'Laravel\\Telescope\\Watchers\\ViewWatcher' => true,
    ),
  ),
  'testing' => 
  array (
    'database' => 
    array (
      'default' => 'sqlite',
      'connections' => 
      array (
        'sqlite' => 
        array (
          'driver' => 'sqlite',
          'database' => ':memory:',
          'prefix' => '',
          'foreign_key_constraints' => true,
        ),
      ),
    ),
    'mail' => 
    array (
      'default' => 'log',
      'mailers' => 
      array (
        'log' => 
        array (
          'transport' => 'log',
          'channel' => NULL,
        ),
      ),
    ),
    'cache' => 
    array (
      'default' => 'array',
      'stores' => 
      array (
        'array' => 
        array (
          'driver' => 'array',
          'serialize' => false,
        ),
      ),
    ),
    'session' => 
    array (
      'driver' => 'array',
    ),
    'queue' => 
    array (
      'default' => 'sync',
    ),
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => '/var/www/html/resources/views',
    ),
    'compiled' => '/var/www/html/storage/framework/views',
    'cache' => true,
  ),
  'debugbar' => 
  array (
    'enabled' => NULL,
    'hide_empty_tabs' => true,
    'except' => 
    array (
      0 => 'telescope*',
      1 => 'horizon*',
    ),
    'storage' => 
    array (
      'enabled' => true,
      'open' => NULL,
      'driver' => 'file',
      'path' => '/var/www/html/storage/debugbar',
      'connection' => NULL,
      'provider' => '',
      'hostname' => '127.0.0.1',
      'port' => 2304,
    ),
    'editor' => 'phpstorm',
    'remote_sites_path' => NULL,
    'local_sites_path' => NULL,
    'include_vendors' => true,
    'capture_ajax' => true,
    'add_ajax_timing' => false,
    'ajax_handler_auto_show' => true,
    'ajax_handler_enable_tab' => true,
    'defer_datasets' => false,
    'error_handler' => false,
    'clockwork' => false,
    'collectors' => 
    array (
      'phpinfo' => false,
      'messages' => true,
      'time' => true,
      'memory' => true,
      'exceptions' => true,
      'log' => true,
      'db' => true,
      'views' => true,
      'route' => false,
      'auth' => false,
      'gate' => true,
      'session' => false,
      'symfony_request' => true,
      'mail' => true,
      'laravel' => true,
      'events' => false,
      'default_request' => false,
      'logs' => false,
      'files' => false,
      'config' => false,
      'cache' => false,
      'models' => true,
      'livewire' => true,
      'jobs' => false,
      'pennant' => false,
    ),
    'options' => 
    array (
      'time' => 
      array (
        'memory_usage' => false,
      ),
      'messages' => 
      array (
        'trace' => true,
        'capture_dumps' => false,
      ),
      'memory' => 
      array (
        'reset_peak' => false,
        'with_baseline' => false,
        'precision' => 0,
      ),
      'auth' => 
      array (
        'show_name' => true,
        'show_guards' => true,
      ),
      'gate' => 
      array (
        'trace' => false,
      ),
      'db' => 
      array (
        'with_params' => true,
        'exclude_paths' => 
        array (
        ),
        'backtrace' => true,
        'backtrace_exclude_paths' => 
        array (
        ),
        'timeline' => false,
        'duration_background' => true,
        'explain' => 
        array (
          'enabled' => false,
        ),
        'hints' => false,
        'show_copy' => true,
        'slow_threshold' => false,
        'memory_usage' => false,
        'soft_limit' => 100,
        'hard_limit' => 500,
      ),
      'mail' => 
      array (
        'timeline' => true,
        'show_body' => true,
      ),
      'views' => 
      array (
        'timeline' => true,
        'data' => false,
        'group' => 50,
        'inertia_pages' => 'js/Pages',
        'exclude_paths' => 
        array (
          0 => 'vendor/filament',
        ),
      ),
      'route' => 
      array (
        'label' => true,
      ),
      'session' => 
      array (
        'hiddens' => 
        array (
        ),
      ),
      'symfony_request' => 
      array (
        'label' => true,
        'hiddens' => 
        array (
        ),
      ),
      'events' => 
      array (
        'data' => false,
        'excluded' => 
        array (
        ),
      ),
      'logs' => 
      array (
        'file' => NULL,
      ),
      'cache' => 
      array (
        'values' => true,
      ),
    ),
    'inject' => true,
    'route_prefix' => '_debugbar',
    'route_middleware' => 
    array (
    ),
    'route_domain' => NULL,
    'theme' => 'auto',
    'debug_backtrace_limit' => 50,
  ),
  'blade-heroicons' => 
  array (
    'prefix' => 'heroicon',
    'fallback' => '',
    'class' => '',
    'attributes' => 
    array (
    ),
  ),
  'blade-icons' => 
  array (
    'sets' => 
    array (
    ),
    'class' => '',
    'attributes' => 
    array (
    ),
    'fallback' => '',
    'components' => 
    array (
      'disabled' => false,
      'default' => 'icon',
    ),
  ),
  'shopping_cart' => 
  array (
    'format_numbers' => false,
    'decimals' => 0,
    'dec_point' => '.',
    'thousands_sep' => ',',
    'storage' => NULL,
    'events' => NULL,
  ),
  'clockwork' => 
  array (
    'enable' => NULL,
    'features' => 
    array (
      'cache' => 
      array (
        'enabled' => true,
        'collect_queries' => true,
        'collect_values' => false,
      ),
      'database' => 
      array (
        'enabled' => true,
        'collect_queries' => true,
        'collect_models_actions' => true,
        'collect_models_retrieved' => false,
        'slow_threshold' => NULL,
        'slow_only' => false,
        'detect_duplicate_queries' => false,
      ),
      'events' => 
      array (
        'enabled' => true,
        'ignored_events' => 
        array (
        ),
      ),
      'http_requests' => 
      array (
        'enabled' => true,
        'collect_data' => true,
        'collect_raw_data' => false,
      ),
      'log' => 
      array (
        'enabled' => true,
      ),
      'notifications' => 
      array (
        'enabled' => true,
      ),
      'performance' => 
      array (
        'client_metrics' => true,
      ),
      'queue' => 
      array (
        'enabled' => true,
      ),
      'redis' => 
      array (
        'enabled' => true,
      ),
      'routes' => 
      array (
        'enabled' => false,
        'only_namespaces' => 
        array (
          0 => 'App',
        ),
      ),
      'views' => 
      array (
        'enabled' => true,
        'collect_data' => false,
        'use_twig_profiler' => false,
      ),
    ),
    'web' => true,
    'toolbar' => true,
    'requests' => 
    array (
      'on_demand' => false,
      'errors_only' => false,
      'slow_threshold' => NULL,
      'slow_only' => false,
      'sample' => false,
      'except' => 
      array (
        0 => '/horizon/.*',
        1 => '/telescope/.*',
        2 => '/_tt/.*',
        3 => '/_debugbar/.*',
      ),
      'only' => 
      array (
      ),
      'except_preflight' => true,
    ),
    'artisan' => 
    array (
      'collect' => false,
      'except' => 
      array (
      ),
      'only' => 
      array (
      ),
      'collect_output' => false,
      'except_laravel_commands' => true,
    ),
    'queue' => 
    array (
      'collect' => false,
      'except' => 
      array (
      ),
      'only' => 
      array (
      ),
    ),
    'tests' => 
    array (
      'collect' => false,
      'except' => 
      array (
      ),
    ),
    'collect_data_always' => false,
    'storage' => 'files',
    'storage_files_path' => '/var/www/html/storage/clockwork',
    'storage_files_compress' => false,
    'storage_sql_database' => '/var/www/html/storage/clockwork.sqlite',
    'storage_sql_table' => 'clockwork',
    'storage_redis' => 'default',
    'storage_redis_prefix' => 'clockwork',
    'storage_expiration' => 10080,
    'authentication' => false,
    'authentication_password' => 'VerySecretPassword',
    'stack_traces' => 
    array (
      'enabled' => true,
      'limit' => 10,
      'skip_vendors' => 
      array (
      ),
      'skip_namespaces' => 
      array (
      ),
      'skip_classes' => 
      array (
      ),
    ),
    'serialization_depth' => 10,
    'serialization_blackbox' => 
    array (
      0 => 'Illuminate\\Container\\Container',
      1 => 'Illuminate\\Foundation\\Application',
      2 => 'Laravel\\Lumen\\Application',
    ),
    'register_helpers' => true,
    'headers' => 
    array (
    ),
    'server_timing' => 10,
  ),
  'livewire' => 
  array (
    'class_namespace' => 'App\\Livewire',
    'view_path' => '/var/www/html/resources/views/livewire',
    'layout' => 'components.layouts.app',
    'lazy_placeholder' => NULL,
    'temporary_file_upload' => 
    array (
      'disk' => NULL,
      'rules' => NULL,
      'directory' => NULL,
      'middleware' => NULL,
      'preview_mimes' => 
      array (
        0 => 'png',
        1 => 'gif',
        2 => 'bmp',
        3 => 'svg',
        4 => 'wav',
        5 => 'mp4',
        6 => 'mov',
        7 => 'avi',
        8 => 'wmv',
        9 => 'mp3',
        10 => 'm4a',
        11 => 'jpg',
        12 => 'jpeg',
        13 => 'mpga',
        14 => 'webp',
        15 => 'wma',
      ),
      'max_upload_time' => 5,
      'cleanup' => true,
    ),
    'render_on_redirect' => false,
    'legacy_model_binding' => false,
    'inject_assets' => true,
    'navigate' => 
    array (
      'show_progress_bar' => true,
      'progress_bar_color' => '#2299dd',
    ),
    'inject_morph_markers' => true,
    'pagination_theme' => 'tailwind',
  ),
  'activitylog' => 
  array (
    'enabled' => true,
    'delete_records_older_than_days' => 365,
    'default_log_name' => 'default',
    'default_auth_driver' => NULL,
    'subject_returns_soft_deleted_models' => false,
    'activity_model' => 'Spatie\\Activitylog\\Models\\Activity',
    'table_name' => 'activity_log',
    'database_connection' => NULL,
  ),
  'backup' => 
  array (
    'backup' => 
    array (
      'name' => 'laravel-backup',
      'source' => 
      array (
        'files' => 
        array (
          'include' => 
          array (
            0 => '/var/www/html',
          ),
          'exclude' => 
          array (
            0 => '/var/www/html/vendor',
            1 => '/var/www/html/node_modules',
          ),
          'follow_links' => false,
          'ignore_unreadable_directories' => false,
          'relative_path' => NULL,
        ),
        'databases' => 
        array (
          0 => 'sqlite',
        ),
      ),
      'database_dump_compressor' => NULL,
      'database_dump_file_timestamp_format' => NULL,
      'database_dump_filename_base' => 'database',
      'database_dump_file_extension' => '',
      'destination' => 
      array (
        'compression_method' => -1,
        'compression_level' => 9,
        'filename_prefix' => '',
        'disks' => 
        array (
          0 => 'local',
        ),
      ),
      'temporary_directory' => '/var/www/html/storage/app/backup-temp',
      'password' => NULL,
      'encryption' => 'default',
      'tries' => 1,
      'retry_delay' => 0,
    ),
    'notifications' => 
    array (
      'notifications' => 
      array (
        'Spatie\\Backup\\Notifications\\Notifications\\BackupHasFailedNotification' => 
        array (
          0 => 'mail',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\UnhealthyBackupWasFoundNotification' => 
        array (
          0 => 'mail',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\CleanupHasFailedNotification' => 
        array (
          0 => 'mail',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\BackupWasSuccessfulNotification' => 
        array (
          0 => 'mail',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\HealthyBackupWasFoundNotification' => 
        array (
          0 => 'mail',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\CleanupWasSuccessfulNotification' => 
        array (
          0 => 'mail',
        ),
      ),
      'notifiable' => 'Spatie\\Backup\\Notifications\\Notifiable',
      'mail' => 
      array (
        'to' => 'your@example.com',
        'from' => 
        array (
          'address' => 'hello@example.com',
          'name' => 'Example',
        ),
      ),
      'slack' => 
      array (
        'webhook_url' => '',
        'channel' => NULL,
        'username' => NULL,
        'icon' => NULL,
      ),
      'discord' => 
      array (
        'webhook_url' => '',
        'username' => '',
        'avatar_url' => '',
      ),
    ),
    'monitor_backups' => 
    array (
      0 => 
      array (
        'name' => 'laravel-backup',
        'disks' => 
        array (
          0 => 'local',
        ),
        'health_checks' => 
        array (
          'Spatie\\Backup\\Tasks\\Monitor\\HealthChecks\\MaximumAgeInDays' => 1,
          'Spatie\\Backup\\Tasks\\Monitor\\HealthChecks\\MaximumStorageInMegabytes' => 5000,
        ),
      ),
    ),
    'cleanup' => 
    array (
      'strategy' => 'Spatie\\Backup\\Tasks\\Cleanup\\Strategies\\DefaultStrategy',
      'default_strategy' => 
      array (
        'keep_all_backups_for_days' => 7,
        'keep_daily_backups_for_days' => 16,
        'keep_weekly_backups_for_weeks' => 8,
        'keep_monthly_backups_for_months' => 4,
        'keep_yearly_backups_for_years' => 2,
        'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
      ),
      'tries' => 1,
      'retry_delay' => 0,
    ),
  ),
  'query-builder' => 
  array (
    'parameters' => 
    array (
      'include' => 'include',
      'filter' => 'filter',
      'sort' => 'sort',
      'fields' => 'fields',
      'append' => 'append',
    ),
    'count_suffix' => 'Count',
    'exists_suffix' => 'Exists',
    'disable_invalid_filter_query_exception' => false,
    'disable_invalid_sort_query_exception' => false,
    'disable_invalid_includes_query_exception' => false,
    'convert_relation_names_to_snake_case_plural' => true,
    'convert_relation_table_name_strategy' => false,
    'convert_field_names_to_snake_case' => false,
  ),
  'ide-helper' => 
  array (
    'filename' => '_ide_helper.php',
    'models_filename' => '_ide_helper_models.php',
    'meta_filename' => '.phpstorm.meta.php',
    'include_fluent' => false,
    'include_factory_builders' => false,
    'write_model_magic_where' => true,
    'write_model_external_builder_methods' => true,
    'write_model_relation_count_properties' => true,
    'write_model_relation_exists_properties' => false,
    'write_eloquent_model_mixins' => false,
    'include_helpers' => false,
    'helper_files' => 
    array (
      0 => '/var/www/html/vendor/laravel/framework/src/Illuminate/Support/helpers.php',
      1 => '/var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/helpers.php',
    ),
    'model_locations' => 
    array (
      0 => 'app',
    ),
    'ignored_models' => 
    array (
    ),
    'model_hooks' => 
    array (
    ),
    'extra' => 
    array (
      'Eloquent' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'Illuminate\\Database\\Query\\Builder',
      ),
      'Session' => 
      array (
        0 => 'Illuminate\\Session\\Store',
      ),
    ),
    'magic' => 
    array (
    ),
    'interfaces' => 
    array (
    ),
    'model_camel_case_properties' => false,
    'type_overrides' => 
    array (
      'integer' => 'int',
      'boolean' => 'bool',
    ),
    'include_class_docblocks' => false,
    'force_fqn' => false,
    'use_generics_annotations' => true,
    'macro_default_return_types' => 
    array (
      'Illuminate\\Http\\Client\\Factory' => 'Illuminate\\Http\\Client\\PendingRequest',
    ),
    'additional_relation_types' => 
    array (
    ),
    'additional_relation_return_types' => 
    array (
    ),
    'enforce_nullable_relationships' => true,
    'post_migrate' => 
    array (
    ),
  ),
  'tinker' => 
  array (
    'commands' => 
    array (
    ),
    'alias' => 
    array (
    ),
    'dont_alias' => 
    array (
      0 => 'App\\Nova',
    ),
  ),
);
