<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseTransactions;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Bind silent mocks for console input and output to prevent interactive prompts during tests
        $this->app->bind(\Symfony\Component\Console\Input\InputInterface::class, function ($app) {
            $mock = \Mockery::mock(\Symfony\Component\Console\Input\InputInterface::class);
            $mock->shouldReceive('isInteractive')->andReturn(false);
            $mock->shouldReceive('hasArgument')->andReturn(false);
            $mock->shouldReceive('getArgument')->andReturn(null);
            $mock->shouldReceive('hasOption')->andReturn(false);
            $mock->shouldReceive('getOption')->andReturn(null);

            return $mock;
        });

        $this->app->bind(\Symfony\Component\Console\Output\OutputInterface::class, function ($app) {
            $mock = \Mockery::mock(\Symfony\Component\Console\Output\OutputInterface::class);
            $mock->shouldReceive('write')->andReturn(null);
            $mock->shouldReceive('writeln')->andReturn(null);

            return $mock;
        });

        $this->app->bind(\Symfony\Component\Console\Style\OutputStyle::class, function ($app) {
            $mock = \Mockery::mock(\Symfony\Component\Console\Style\SymfonyStyle::class);
            $mock->shouldReceive('askQuestion')->andReturn(true);
            $mock->shouldReceive('confirm')->andReturn(true);
            $mock->shouldReceive('writeln')->andReturn(null);
            $mock->shouldReceive('write')->andReturn(null);

            return $mock;
        });

        // إعداد قاعدة البيانات للاختبارات
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        // إعداد اتصال sqlite_testing و testing
        $this->app['config']->set('database.connections.sqlite_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        $this->app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        // تشغيل migrations يدوياً لجميع الاتصالات
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
                '--database' => 'sqlite',
                '--force' => true,
            ]);

            \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
                '--database' => 'sqlite_testing',
                '--force' => true,
            ]);

            \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
                '--database' => 'testing',
                '--force' => true,
            ]);
        } catch (\Exception $e) {
            // تجاهل الأخطاء في migrations للاختبارات
        }
    }

    /**
     * Tear down the test environment.
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Close Mockery to prevent mock state from leaking between tests.
        \Mockery::close();
    }

    /**
     * Call protected/private method of a class.
     *
     * @param  object  &$object  Instantiated object that we will run method on.
     * @param  string  $methodName  Method name to call
     * @param  array  $parameters  Array of parameters to pass into method.
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
