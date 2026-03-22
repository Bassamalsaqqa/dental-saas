<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * @internal
 */
final class AppBaseUrlDetectionTest extends CIUnitTestCase
{
    private array $envBackup = [];
    private array $serverBackup = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->envBackup = [
            'app.baseURL' => $_ENV['app.baseURL'] ?? null,
        ];

        $this->serverBackup = [
            'HTTPS'                  => $_SERVER['HTTPS'] ?? null,
            'HTTP_HOST'              => $_SERVER['HTTP_HOST'] ?? null,
            'HTTP_X_FORWARDED_HOST'  => $_SERVER['HTTP_X_FORWARDED_HOST'] ?? null,
            'HTTP_X_FORWARDED_PROTO' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null,
            'SCRIPT_NAME'            => $_SERVER['SCRIPT_NAME'] ?? null,
            'SERVER_PORT'            => $_SERVER['SERVER_PORT'] ?? null,
        ];
    }

    protected function tearDown(): void
    {
        foreach ($this->envBackup as $key => $value) {
            if ($value === null) {
                unset($_ENV[$key]);
                putenv($key);
            } else {
                $_ENV[$key] = $value;
                putenv($key . '=' . $value);
            }
        }

        foreach ($this->serverBackup as $key => $value) {
            if ($value === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $value;
            }
        }

        parent::tearDown();
    }

    public function testLocalConfiguredBaseUrlFallsBackToForwardedTunnelHost(): void
    {
        $_ENV['app.baseURL']                = 'https://localhost/dental-saas/';
        putenv('app.baseURL=https://localhost/dental-saas/');
        $_SERVER['HTTP_HOST']               = 'localhost';
        $_SERVER['HTTP_X_FORWARDED_HOST']   = 'demo.trycloudflare.com';
        $_SERVER['HTTP_X_FORWARDED_PROTO']  = 'https';
        $_SERVER['SCRIPT_NAME']             = '/dental-saas/index.php';
        $_SERVER['SERVER_PORT']             = '443';

        $config = new App();

        $this->assertSame('https://demo.trycloudflare.com/dental-saas/', $config->baseURL);
    }

    public function testExplicitProductionBaseUrlStillWins(): void
    {
        $_ENV['app.baseURL']                = 'https://app.example.com/';
        putenv('app.baseURL=https://app.example.com/');
        $_SERVER['HTTP_HOST']               = 'localhost';
        $_SERVER['HTTP_X_FORWARDED_HOST']   = 'demo.trycloudflare.com';
        $_SERVER['HTTP_X_FORWARDED_PROTO']  = 'https';
        $_SERVER['SCRIPT_NAME']             = '/index.php';
        $_SERVER['SERVER_PORT']             = '443';

        $config = new App();

        $this->assertSame('https://app.example.com/', $config->baseURL);
    }
}
