<?php
declare(strict_types=1);
/** Actual framework IP resolution + actual private audit method; no boot, DB, network or writes. */
namespace Config {
    class Services {
        public static $syntheticRequest;
        public static function request() { return self::$syntheticRequest; }
    }
}
namespace App\Models {
    // Only isolates ORM construction; the production method below is loaded unchanged.
    class TenantAwareModel {}
}
namespace {
    if (PHP_SAPI !== 'cli') { exit(2); }
    spl_autoload_register(static function (string $class): void {
        if (str_starts_with($class, 'CodeIgniter\\')) {
            $file = __DIR__ . '/../../system/' . str_replace('\\', '/', substr($class, strlen('CodeIgniter\\'))) . '.php';
            if (is_file($file)) { require_once $file; }
        }
    });
    function service(string $name) {
        static $globals;
        if ($name !== 'superglobals') { throw new \RuntimeException('Unexpected service: ' . $name); }
        return $globals ??= new \CodeIgniter\Superglobals();
    }
    require __DIR__ . '/../../app/Models/ActivityLogModel.php';
    $model = (new \ReflectionClass(\App\Models\ActivityLogModel::class))->newInstanceWithoutConstructor();
    $method = new \ReflectionMethod($model, 'getClientIP');
    $method->setAccessible(true);
    $cases = [
        ['untrusted Client-IP ignored', [], '198.51.100.20', ['Client-IP' => '8.8.8.8'], '198.51.100.20'],
        ['untrusted XFF ignored', [], '198.51.100.20', ['X-Forwarded-For' => '1.1.1.1'], '198.51.100.20'],
        ['untrusted forged chain ignored', ['10.0.0.2' => 'X-Forwarded-For'], '198.51.100.20', ['X-Forwarded-For' => '1.1.1.1, 10.0.0.2'], '198.51.100.20'],
        ['trusted exact peer chain accepted', ['10.0.0.2' => 'X-Forwarded-For'], '10.0.0.2', ['X-Forwarded-For' => '1.1.1.1, 10.0.0.1', 'Client-IP' => '8.8.8.8'], '1.1.1.1'],
        ['trusted subnet chain accepted', ['10.0.0.0/24' => 'X-Forwarded-For'], '10.0.0.2', ['X-Forwarded-For' => '203.0.113.99, 10.0.0.1'], '203.0.113.99'],
        ['only configured header used', ['10.0.0.2' => 'X-Real-IP'], '10.0.0.2', ['X-Forwarded-For' => '8.8.8.8', 'X-Real-IP' => '203.0.113.12'], '203.0.113.12'],
        ['malformed forwarded IP falls back to peer', ['10.0.0.2' => 'X-Forwarded-For'], '10.0.0.2', ['X-Forwarded-For' => 'not-an-ip, 1.1.1.1'], '10.0.0.2'],
        ['private direct peer retained', [], '192.168.1.20', ['Client-IP' => '8.8.8.8'], '192.168.1.20'],
        ['IPv6 trusted subnet', ['2001:db8::/32' => 'X-Forwarded-For'], '2001:db8::2', ['X-Forwarded-For' => '2001:db9::9, 2001:db8::1'], '2001:db9::9'],
        ['invalid peer not rescued by spoofed header', [], 'not-an-ip', ['Client-IP' => '8.8.8.8'], '0.0.0.0'],
        ['missing peer fails closed', [], null, ['X-Forwarded-For' => '1.1.1.1'], '0.0.0.0'],
    ];
    $serverBefore = $_SERVER;
    try {
        foreach ($cases as [$label, $proxies, $peer, $headers, $expected]) {
            $request = (new \ReflectionClass(\CodeIgniter\HTTP\IncomingRequest::class))->newInstanceWithoutConstructor();
            $config = (object) ['proxyIPs' => $proxies];
            $property = new \ReflectionProperty($request, 'config');
            $property->setAccessible(true); $property->setValue($request, $config);
            $request->setGlobal('server', $peer === null ? [] : ['REMOTE_ADDR' => $peer]);
            foreach ($headers as $key => $value) { $request->setHeader($key, $value); }
            // Poison the raw globals too. Audit attribution must follow the request's cached peer.
            $_SERVER = ['REMOTE_ADDR' => '8.8.4.4', 'HTTP_CLIENT_IP' => '8.8.8.8', 'HTTP_X_FORWARDED_FOR' => '1.1.1.1'];
            \Config\Services::$syntheticRequest = $request;
            $actual = $method->invoke($model);
            if ($actual !== $expected) { throw new \RuntimeException($label . ': ' . $actual . ' != ' . $expected); }
        }
        $request = (new \ReflectionClass(\CodeIgniter\HTTP\CLIRequest::class))->newInstanceWithoutConstructor();
        $property = new \ReflectionProperty($request, 'config');
        $property->setAccessible(true); $property->setValue($request, (object) ['proxyIPs' => []]);
        $request->setGlobal('server', []);
        \Config\Services::$syntheticRequest = $request;
        if ($method->invoke($model) !== '0.0.0.0') { throw new \RuntimeException('Canonical CLI no-peer attribution'); }
    } finally { $_SERVER = $serverBefore; }
    echo json_encode(['status' => 'PASS', 'checks' => count($cases) + 1,
        'scope' => 'Actual IncomingRequest/CLIRequest canonical trusted-proxy IP resolution through actual ActivityLogModel method; no historical rows read or rewritten.']), PHP_EOL;
}
