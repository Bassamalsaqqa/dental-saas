<?php
declare(strict_types=1);
/** Actual Config\App construction and framework trust matcher; no app boot/DB/network. */
if (PHP_SAPI !== 'cli') { exit(2); }
spl_autoload_register(static function (string $class): void {
    foreach (['CodeIgniter\\' => '/../../system/', 'Config\\' => '/../../app/Config/'] as $prefix => $root) {
        if (str_starts_with($class, $prefix)) {
            $file = __DIR__ . $root . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) { require_once $file; }
        }
    }
});
function env(string $key, $default = null) { return $GLOBALS['syntheticEnv'][$key] ?? $default; }
function service(string $name) {
    $GLOBALS['serviceCalls'][] = $name;
    if ($name !== 'superglobals') { throw new RuntimeException('Unexpected service / possible config construction cycle: ' . $name); }
    return $GLOBALS['syntheticGlobals'];
}
// Disable unrelated registrar/environment discovery, retaining actual BaseConfig and App constructors.
CodeIgniter\Config\BaseConfig::$override = false;
class SyntheticProxyApp extends Config\App {
    public function __construct(array $proxies) { $this->proxyIPs = $proxies; parent::__construct(); }
}
$direct = ['HTTP_HOST' => 'localhost:57113', 'REMOTE_ADDR' => '203.0.113.50', 'SERVER_PORT' => '80', 'SCRIPT_NAME' => '/index.php'];
$forwarded = ['HTTP_X_FORWARDED_HOST' => 'tunnel.example:8443', 'HTTP_X_FORWARDED_PROTO' => 'https'];
$cases = [
    ['plain local port', [], $direct, null, 'http://localhost:57113/'],
    ['untrusted forwarded host/proto ignored', [], $direct + $forwarded, null, 'http://localhost:57113/'],
    ['configured local fallback preserved safely', [], $direct + $forwarded, 'http://localhost:8080/', 'http://localhost:57113/'],
    ['untrusted claimed proxy in XFF ignored', ['10.0.0.2' => 'X-Forwarded-For'], $direct + $forwarded + ['HTTP_X_FORWARDED_FOR' => '10.0.0.2'], null, 'http://localhost:57113/'],
    ['trusted exact peer preserves tunnel', ['203.0.113.50' => 'X-Forwarded-For'], $direct + $forwarded, null, 'https://tunnel.example:8443/'],
    ['trusted IPv4 CIDR', ['203.0.113.0/24' => 'X-Forwarded-For'], $direct + $forwarded, null, 'https://tunnel.example:8443/'],
    ['trusted IPv6 CIDR', ['2001:db8::/32' => 'X-Forwarded-For'], array_replace($direct + $forwarded, ['REMOTE_ADDR' => '2001:db8::2']), null, 'https://tunnel.example:8443/'],
    ['trusted IPv6 authority', ['203.0.113.50' => 'X-Forwarded-For'], array_replace($direct + $forwarded, ['HTTP_X_FORWARDED_HOST' => '[2001:db8::10]:8443']), null, 'https://[2001:db8::10]:8443/'],
    ['trusted first forwarded authority', ['203.0.113.50' => 'X-Forwarded-For'], array_replace($direct + $forwarded, ['HTTP_X_FORWARDED_HOST' => 'first.example, second.example']), null, 'https://first.example/'],
    ['configured production URL remains pinned', [], $direct + $forwarded, 'https://denta.example/clinic/', 'https://denta.example/clinic/'],
    ['configured production URL pinned for trusted proxy too', ['203.0.113.50' => 'X-Forwarded-For'], $direct + $forwarded, 'https://denta.example/clinic/', 'https://denta.example/clinic/'],
    ['native TLS preserved', [], array_replace($direct + $forwarded, ['HTTPS' => 'on']), null, 'https://localhost:57113/'],
    ['native port443 preserved', [], array_replace($direct + $forwarded, ['SERVER_PORT' => '443']), null, 'https://localhost:57113/'],
    ['CLI no peer or headers', [], [], null, 'http://localhost/'],
    ['CLI configured URL', [], [], 'https://denta.example/', 'https://denta.example/'],
    ['subdirectory preserved', [], array_replace($direct, ['SCRIPT_NAME' => '/dental-saas/index.php']), null, 'http://localhost:57113/dental-saas/'],
];
foreach (["evil.example\r\nInjected: yes", 'user@evil.example', 'user:pass@evil.example', 'evil.example/path', 'evil.example?query=x', 'evil.example#fragment', 'evil.example\\path', 'https://evil.example', 'evil.example:0', 'evil.example:65536', '[not-ipv6]:443', 'bad host', '-bad.example', 'evil.example:abc'] as $bad) {
    $cases[] = ['invalid forwarded authority rejected: ' . json_encode($bad), ['203.0.113.50' => 'X-Forwarded-For'], array_replace($direct + $forwarded, ['HTTP_X_FORWARDED_HOST' => $bad]), null, 'https://localhost:57113/'];
}
$cases[] = ['invalid direct authority rejected', [], array_replace($direct, ['HTTP_HOST' => 'evil.example/path']), null, 'http://localhost/'];
$serverBefore = $_SERVER; $calls = [];
try {
    foreach ($cases as [$label, $proxies, $server, $configured, $expected]) {
        $_SERVER = $server;
        $GLOBALS['syntheticEnv'] = ['app.baseURL' => $configured];
        $GLOBALS['syntheticGlobals'] = new CodeIgniter\Superglobals($server, [], [], [], [], []);
        $GLOBALS['serviceCalls'] = [];
        $app = new SyntheticProxyApp($proxies);
        if ($app->baseURL !== $expected) { throw new RuntimeException($label . ': ' . $app->baseURL . ' != ' . $expected); }
        $calls = array_merge($calls, $GLOBALS['serviceCalls']);
    }
} finally { $_SERVER = $serverBefore; }
echo json_encode(['status' => 'PASS', 'checks' => count($cases), 'services_used' => array_values(array_unique($calls)),
    'scope' => 'Actual App/BaseConfig constructors with actual canonical Request proxy matcher and URI creation; unrelated registrar discovery disabled. No shared request/App lookup, database, network or deployment configuration changes.']), PHP_EOL;
