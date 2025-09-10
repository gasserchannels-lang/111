<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Providers\CoprraServiceProvider;
use Tests\TestCase;

class CoprraServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_instantiated()
    {
        $provider = new CoprraServiceProvider(app());
        $this->assertInstanceOf(CoprraServiceProvider::class, $provider);
    }

    /**
     * @test
     */
    public function it_has_register_method()
    {
        $provider = new CoprraServiceProvider(app());
        $this->assertTrue(method_exists($provider, 'register'));
    }

    /**
     * @test
     */
    public function it_has_boot_method()
    {
        $provider = new CoprraServiceProvider(app());
        $this->assertTrue(method_exists($provider, 'boot'));
    }

    /**
     * @test
     */
    public function it_can_register_services()
    {
        $app = app();
        $provider = new CoprraServiceProvider($app);

        // This should not throw any exceptions
        $provider->register();
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_can_boot_services()
    {
        $app = app();
        $provider = new CoprraServiceProvider($app);

        // This should not throw any exceptions
        $provider->boot();
        $this->assertTrue(true);
    }
}
