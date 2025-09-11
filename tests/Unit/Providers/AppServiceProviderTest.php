<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Providers\AppServiceProvider;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_be_instantiated()
    {
        $provider = new AppServiceProvider(app());
        $this->assertInstanceOf(AppServiceProvider::class, $provider);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_register_method()
    {
        $provider = new AppServiceProvider(app());
        $this->assertTrue(method_exists($provider, 'register'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_boot_method()
    {
        $provider = new AppServiceProvider(app());
        $this->assertTrue(method_exists($provider, 'boot'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_register_services()
    {
        $app = app();
        $provider = new AppServiceProvider($app);

        // This should not throw any exceptions
        $provider->register();
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_boot_services()
    {
        $app = app();
        $provider = new AppServiceProvider($app);

        // This should not throw any exceptions
        $provider->boot();
        $this->assertTrue(true);
    }
}
