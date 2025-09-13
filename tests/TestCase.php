<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // إعدادات قاعدة البيانات للاختبارات المحلية
        $this->app['config']->set('database.default', 'testing');
        $this->app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        // إزالة إعدادات MySQL لضمان عدم استخدامها
        $this->app['config']->set('database.connections.mysql', null);

        // تعيين الاتصال الافتراضي
        DB::setDefaultConnection('testing');

        // إعدادات الاختبارات
        $this->app['config']->set('mail.default', 'log');
        $this->app['config']->set('cache.default', 'array');
        $this->app['config']->set('session.driver', 'array');
        $this->app['config']->set('queue.default', 'sync');

        // إعدادات التطبيق
        $this->app['config']->set('app.key', env('APP_KEY', 'base64:mAkbpuXF7OVTRIDCIMkD8+xw6xVi7pge9CFImeqZaxE='));
        $this->app['config']->set('app.env', 'testing');
        $this->app['config']->set('app.debug', true);

        // تعطيل Telescope في الاختبارات
        $this->app['config']->set('telescope.enabled', false);

        // تهيئة قاعدة البيانات بدون RefreshDatabase
        try {
            $this->artisan('migrate:fresh', ['--database' => 'testing', '--force' => true]);
        } catch (\Exception $e) {
            // تجاهل أخطاء VACUUM في SQLite
            if (strpos($e->getMessage(), 'cannot VACUUM from within a transaction') === false) {
                throw $e;
            }
        }

        // تعيين الاتصال الافتراضي مرة أخرى للتأكد
        DB::setDefaultConnection('testing');

        // تهيئة Model resolver
        \Illuminate\Database\Eloquent\Model::setConnectionResolver($this->app['db']);
    }
}
