<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class HostingerTest extends TestCase
{
    /**
     * Test database connection to Hostinger.
     */
    public function test_database_connection()
    {
        $this->assertDatabaseHas('migrations', []);
    }

    /**
     * Test mail configuration.
     */
    public function test_mail_configuration()
    {
        $this->assertEquals('mailpit', config('mail.mailers.smtp.host'));
        $this->assertEquals(1025, config('mail.mailers.smtp.port'));
        $this->assertEquals(null, config('mail.mailers.smtp.encryption'));
    }

    /**
     * Test SSL configuration.
     */
    public function test_ssl_configuration()
    {
        $this->assertEquals('http://localhost', config('app.url'));
        $this->assertFalse(config('session.secure'));
        $this->assertTrue(config('session.http_only'));
    }

    /**
     * Test CDN configuration.
     */
    public function test_cdn_configuration()
    {
        $this->assertEquals('https://coprra.com.cdn.hstgr.net', config('app.cdn_url'));
        $this->assertTrue(config('hostinger.cdn.enabled'));
        $this->assertEquals('Hostinger CDN', config('hostinger.cdn.type'));
    }

    /**
     * Test PHP configuration.
     */
    public function test_php_configuration()
    {
        $this->assertEquals('2048M', config('app.php_memory_limit'));
        $this->assertEquals(360, config('app.php_max_execution_time'));
        $this->assertEquals('2048M', config('app.php_upload_max_filesize'));
    }

    /**
     * Test deployment configuration.
     */
    public function test_deployment_configuration()
    {
        $this->assertNull(config('app.deployment.ssh_host'));
        $this->assertNull(config('app.deployment.ssh_port'));
        $this->assertNull(config('app.deployment.ssh_username'));
    }

    /**
     * Test cache configuration.
     */
    public function test_cache_configuration()
    {
        Cache::put('test_key', 'test_value', 60);
        $this->assertEquals('test_value', Cache::get('test_key'));
    }

    /**
     * Test session configuration.
     */
    public function test_session_configuration()
    {
        $this->assertEquals(120, config('session.lifetime')); // Local development setting
        $this->assertFalse(config('session.secure')); // Local development setting
        $this->assertTrue(config('session.http_only'));
        $this->assertEquals('lax', config('session.same_site'));
    }
}
