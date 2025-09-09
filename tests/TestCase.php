<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // إعدادات قاعدة البيانات للاختبارات
        $this->app['config']->set('database.default', 'testing');
        $this->app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        // إعدادات التطبيق
        $this->app['config']->set('app.key', 'base64:testkey123456789012345678901234567890123456789012345678901234567890=');
        $this->app['config']->set('app.env', 'testing');
        $this->app['config']->set('app.debug', true);

        // تهيئة قاعدة البيانات
        $this->artisan('migrate', ['--database' => 'testing']);

        // تعيين الاتصال الافتراضي
        DB::setDefaultConnection('testing');

        // تهيئة Model resolver
        \Illuminate\Database\Eloquent\Model::setConnectionResolver($this->app['db']);
    }
}
