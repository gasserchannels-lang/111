<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

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
        $this->app['config']->set('database.default', 'sqlite_testing');
        $this->app['config']->set('database.connections.sqlite_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        // تشغيل migrations للاختبارات بدون تأكيد
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--database' => 'sqlite_testing',
            '--force' => true,
        ]);
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
