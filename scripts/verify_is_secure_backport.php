<?php

declare(strict_types=1);

/**
 * Standalone Security Regression Test Suite for IncomingRequest::isSecure()
 *
 * Exercises the GHSA-7wmf-pw8j-mc78 / CVE-2026-63220 security backport
 * adapted from CodeIgniter 4.7.5 for the CodeIgniter 4.6.3 request abstraction.
 *
 * Requirements & Constraints:
 * - NO database connection or DB classes.
 * - NO CodeIgniter framework boot / service initialization.
 * - Minimal independent PSR-4 autoloader for required system/HTTP classes.
 * - Exercises actual IncomingRequest using Reflection (without invoking constructor).
 * - Fails closed on untrusted, absent, malformed, or mismatched proxy/remote inputs.
 * - Zero network calls or HTTP sends.
 */

// 1. Independent PSR-4 autoloader for CodeIgniter\ system classes only
spl_autoload_register(static function (string $class): void {
    $prefix = 'CodeIgniter\\';
    if (str_starts_with($class, $prefix)) {
        $relativeClass = substr($class, strlen($prefix));
        $file = __DIR__ . '/../system/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

use CodeIgniter\HTTP\IncomingRequest;

/**
 * Test harness helper to create an IncomingRequest instance via Reflection
 * without invoking the CodeIgniter constructor or bootstrapping framework services.
 *
 * @param array<string, string>|null $proxyIPs
 * @param array<string, string>      $headers
 * @param array<string, mixed>|null  $serverGlobals Explicit server array for $request->setGlobal('server', ...)
 */
function createTestRequest(
    ?array $proxyIPs = null,
    ?string $remoteAddr = null,
    array $headers = [],
    ?array $serverGlobals = null
): IncomingRequest {
    $refClass = new ReflectionClass(IncomingRequest::class);
    /** @var IncomingRequest $request */
    $request = $refClass->newInstanceWithoutConstructor();

    // Configure proxyIPs via App config mock
    $config = new stdClass();
    if ($proxyIPs !== null) {
        $config->proxyIPs = $proxyIPs;
    }
    $refConfig = new ReflectionProperty($request, 'config');
    $refConfig->setAccessible(true);
    $refConfig->setValue($request, $config);

    // Initialize headers collections
    $refHeaders = new ReflectionProperty($request, 'headers');
    $refHeaders->setAccessible(true);
    $refHeaders->setValue($request, []);

    $refHeaderMap = new ReflectionProperty($request, 'headerMap');
    $refHeaderMap->setAccessible(true);
    $refHeaderMap->setValue($request, []);

    // Setup server globals via setGlobal
    if ($serverGlobals !== null) {
        $request->setGlobal('server', $serverGlobals);
    } else {
        $server = [];
        if ($remoteAddr !== null) {
            $server['REMOTE_ADDR'] = $remoteAddr;
        }
        $request->setGlobal('server', $server);
    }

    // Populate headers
    foreach ($headers as $name => $value) {
        $request->setHeader($name, $value);
    }

    return $request;
}

// Regression Test Runner
$passed = 0;
$failed = 0;
$tests  = [];

function runTest(string $description, callable $test): void
{
    global $passed, $failed, $tests;
    try {
        $test();
        $passed++;
        $tests[] = ['status' => 'PASS', 'desc' => $description];
        echo "  [PASS] {$description}\n";
    } catch (Throwable $e) {
        $failed++;
        $tests[] = ['status' => 'FAIL', 'desc' => $description, 'error' => $e->getMessage()];
        echo "  [FAIL] {$description}: {$e->getMessage()}\n";
    }
}

function assertTrue(bool $condition, string $message = 'Expected true, got false'): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function assertFalse(bool $condition, string $message = 'Expected false, got true'): void
{
    if ($condition) {
        throw new RuntimeException($message);
    }
}

echo "=== Running Standalone Security Regression Suite: IncomingRequest::isSecure() ===\n\n";

// Group 1: Native HTTPS handling (legacy nonempty semantics)
echo "Group 1: Native HTTPS handling (legacy nonempty semantics)\n";
runTest('Native HTTPS with $_SERVER[HTTPS]=on returns true regardless of proxy or headers', static function (): void {
    $saved = $_SERVER;
    try {
        $_SERVER['HTTPS'] = 'on';
        $request = createTestRequest(proxyIPs: [], remoteAddr: '198.51.100.1');
        assertTrue($request->isSecure(), 'Expected isSecure() to be true for HTTPS=on');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Native HTTPS with $_SERVER[HTTPS]=1 returns true', static function (): void {
    $saved = $_SERVER;
    try {
        $_SERVER['HTTPS'] = '1';
        $request = createTestRequest(proxyIPs: [], remoteAddr: '198.51.100.1');
        assertTrue($request->isSecure(), 'Expected isSecure() to be true for HTTPS=1');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Native HTTPS with $_SERVER[HTTPS]=off returns false without trusted forwarded header', static function (): void {
    $saved = $_SERVER;
    try {
        $_SERVER['HTTPS'] = 'off';
        $request = createTestRequest(proxyIPs: [], remoteAddr: '198.51.100.1');
        assertFalse($request->isSecure(), 'Expected isSecure() to be false for HTTPS=off');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Native HTTPS with $_SERVER[HTTPS]=empty string returns false without trusted forwarded header', static function (): void {
    $saved = $_SERVER;
    try {
        $_SERVER['HTTPS'] = '';
        $request = createTestRequest(proxyIPs: [], remoteAddr: '198.51.100.1');
        assertFalse($request->isSecure(), 'Expected isSecure() to be false for HTTPS=""');
    } finally {
        $_SERVER = $saved;
    }
});

// Group 2: Spoofed headers with untrusted or absent proxy
echo "\nGroup 2: Spoofed headers with untrusted or absent proxy\n";
runTest('Untrusted client spoofing X-Forwarded-Proto=https is rejected (fails closed)', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: '198.51.100.25', // Direct client, not the proxy
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'Untrusted client spoofing X-Forwarded-Proto must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Untrusted client spoofing Front-End-Https=on is rejected (fails closed)', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: '198.51.100.25',
            headers: ['Front-End-Https' => 'on']
        );
        assertFalse($request->isSecure(), 'Untrusted client spoofing Front-End-Https must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Absent proxy configuration (empty array) rejects forwarded HTTPS headers', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: [], // No trusted proxies configured
            remoteAddr: '10.0.0.1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'Empty proxyIPs must return false for forwarded headers');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Absent proxy configuration (null config) rejects forwarded HTTPS headers', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: null, // Null proxyIPs
            remoteAddr: '10.0.0.1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'Null proxyIPs must return false for forwarded headers');
    } finally {
        $_SERVER = $saved;
    }
});

// Group 3: Trusted exact proxy matching
echo "\nGroup 3: Trusted exact proxy matching\n";
runTest('Trusted exact proxy IP with X-Forwarded-Proto=https returns true', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertTrue($request->isSecure(), 'Trusted exact proxy with https proto must return true');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Exact proxy IP mismatch with X-Forwarded-Proto=https returns false', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.2',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'Mismatched proxy IP must return false');
    } finally {
        $_SERVER = $saved;
    }
});

// Group 4: Trusted IPv4 CIDR subnet matching & partial-byte /25
echo "\nGroup 4: Trusted IPv4 CIDR subnet matching & partial-byte /25\n";
runTest('Trusted IPv4 /16 CIDR matches address inside subnet', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.0/16' => 'X-Forwarded-For'],
            remoteAddr: '10.0.254.12',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertTrue($request->isSecure(), 'IPv4 address inside CIDR subnet must return true');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Trusted IPv4 /24 CIDR rejects address outside subnet', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['192.168.1.0/24' => 'X-Forwarded-For'],
            remoteAddr: '192.168.2.1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'IPv4 address outside CIDR subnet must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Trusted IPv4 /32 CIDR matches exact address only', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['192.168.1.50/32' => 'X-Forwarded-For'],
            remoteAddr: '192.168.1.50',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertTrue($request->isSecure(), 'IPv4 /32 CIDR must match exact address');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Partial-byte IPv4 /25 matches address in lower half (192.168.1.127)', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['192.168.1.0/25' => 'X-Forwarded-For'],
            remoteAddr: '192.168.1.127',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertTrue($request->isSecure(), 'IPv4 /25 must match 192.168.1.127 (top bit 0)');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Partial-byte IPv4 /25 rejects address in upper half (192.168.1.128)', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['192.168.1.0/25' => 'X-Forwarded-For'],
            remoteAddr: '192.168.1.128',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'IPv4 /25 must reject 192.168.1.128 (top bit 1)');
    } finally {
        $_SERVER = $saved;
    }
});

// Group 5: Trusted IPv6 CIDR, partial-byte /65, & compressed/expanded equivalence
echo "\nGroup 5: Trusted IPv6 CIDR, partial-byte /65, & compressed/expanded equivalence\n";
runTest('Trusted IPv6 /48 CIDR matches address inside subnet', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['2001:db8:abcd::/48' => 'X-Forwarded-For'],
            remoteAddr: '2001:db8:abcd:0012:0000:0000:0000:0001',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertTrue($request->isSecure(), 'IPv6 address inside /48 subnet must return true');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Trusted IPv6 /48 CIDR rejects address outside subnet', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['2001:db8:abcd::/48' => 'X-Forwarded-For'],
            remoteAddr: '2001:db8:ffff:0012::1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'IPv6 address outside /48 subnet must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Partial-byte IPv6 /65 matches address with 65th bit = 0 (2001:db8:0:0:7fff::1)', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['2001:db8::/65' => 'X-Forwarded-For'],
            remoteAddr: '2001:db8:0:0:7fff::1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertTrue($request->isSecure(), 'IPv6 /65 must match when 65th bit is 0');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Partial-byte IPv6 /65 rejects address with 65th bit = 1 (2001:db8:0:0:8000::1)', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['2001:db8::/65' => 'X-Forwarded-For'],
            remoteAddr: '2001:db8:0:0:8000::1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'IPv6 /65 must reject when 65th bit is 1');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Compressed proxy entry matches expanded remote IPv6 address', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['2001:db8::1' => 'X-Forwarded-For'],
            remoteAddr: '2001:0db8:0000:0000:0000:0000:0000:0001',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertTrue($request->isSecure(), 'Compressed proxy entry must match expanded remote address');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Expanded proxy entry matches compressed remote IPv6 address', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['2001:0db8:0000:0000:0000:0000:0000:0000/64' => 'X-Forwarded-For'],
            remoteAddr: '2001:db8::1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertTrue($request->isSecure(), 'Expanded proxy entry must match compressed remote address');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Trusted exact IPv6 loopback (::1) returns true', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['::1' => 'X-Forwarded-For'],
            remoteAddr: '::1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertTrue($request->isSecure(), 'IPv6 loopback exact match must return true');
    } finally {
        $_SERVER = $saved;
    }
});

// Group 6: IPv4 /0 and IPv6 /0 family restriction
echo "\nGroup 6: IPv4 /0 and IPv6 /0 family restriction\n";
runTest('IPv4 0.0.0.0/0 matches any IPv4 remote address', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['0.0.0.0/0' => 'X-Forwarded-For'],
            remoteAddr: '198.51.100.42',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertTrue($request->isSecure(), '0.0.0.0/0 must match any IPv4 address');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('IPv4 0.0.0.0/0 strictly rejects IPv6 remote address (family restriction)', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['0.0.0.0/0' => 'X-Forwarded-For'],
            remoteAddr: '2001:db8::1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), '0.0.0.0/0 must NOT match IPv6 address');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('IPv6 ::/0 matches any IPv6 remote address', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['::/0' => 'X-Forwarded-For'],
            remoteAddr: '2001:db8:dead:beef::1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertTrue($request->isSecure(), '::/0 must match any IPv6 address');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('IPv6 ::/0 strictly rejects IPv4 remote address (family restriction)', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['::/0' => 'X-Forwarded-For'],
            remoteAddr: '198.51.100.42',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), '::/0 must NOT match IPv4 address');
    } finally {
        $_SERVER = $saved;
    }
});

// Group 7: Missing remote address handling & cache isolation from $_SERVER
echo "\nGroup 7: Missing remote address handling & cache isolation from \$_SERVER\n";
runTest('Missing REMOTE_ADDR (null in request state) fails closed and returns false', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: null,
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'Missing REMOTE_ADDR must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Empty string REMOTE_ADDR fails closed and returns false', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: '',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'Empty REMOTE_ADDR must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Cached REMOTE_ADDR null versus conflicting trusted $_SERVER[REMOTE_ADDR] proves NO raw fallback', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        // $_SERVER holds the trusted proxy IP, simulating an un-sanitized global
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';

        // But the request instance has an explicit server array where REMOTE_ADDR is missing/null
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: null,
            headers: ['X-Forwarded-Proto' => 'https'],
            serverGlobals: ['REMOTE_ADDR' => null]
        );

        // isFromTrustedProxy() MUST use getServer('REMOTE_ADDR') alone and NOT fall back to $_SERVER['REMOTE_ADDR']
        assertFalse($request->isSecure(), 'Request with cached null REMOTE_ADDR must not fall back to raw $_SERVER[REMOTE_ADDR]');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Cached REMOTE_ADDR untrusted versus conflicting trusted $_SERVER[REMOTE_ADDR] uses cached value', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1'; // trusted proxy in $_SERVER

        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: null,
            headers: ['X-Forwarded-Proto' => 'https'],
            serverGlobals: ['REMOTE_ADDR' => '198.51.100.99'] // untrusted in request state
        );

        assertFalse($request->isSecure(), 'Request must use its own cached REMOTE_ADDR (198.51.100.99) and reject');
    } finally {
        $_SERVER = $saved;
    }
});

// Group 8: Invalid CIDR mask, numeric-list config & protocol mismatch
echo "\nGroup 8: Invalid CIDR mask, numeric-list config & protocol mismatch (fail-closed)\n";
runTest('Numeric-list invalid proxy configuration fails closed', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        // Misconfiguration: numeric array list ['10.0.0.1'] instead of map ['10.0.0.1' => 'X-Forwarded-For']
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1'], // key 0 => '10.0.0.1'
            remoteAddr: '10.0.0.1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'Numeric-list proxyIPs must fail closed');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Negative CIDR mask fails closed and returns false', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.0/-1' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'Negative CIDR mask must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Double-slash CIDR mask fails closed and returns false', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.0/24/12' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'Double-slash CIDR mask must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Non-numeric CIDR mask fails closed and returns false', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.0/abc' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'Non-numeric CIDR mask must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Empty CIDR mask (/ trailing) fails closed and returns false', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.0/' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'Empty trailing CIDR mask must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('IPv4 CIDR mask out of range (> 32) fails closed and returns false', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1/33' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'IPv4 mask > 32 must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('IPv6 CIDR mask out of range (> 128) fails closed and returns false', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['2001:db8::/129' => 'X-Forwarded-For'],
            remoteAddr: '2001:db8::1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'IPv6 mask > 128 must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Protocol mismatch (IPv4 remote against IPv6 proxy) fails closed', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['2001:db8::/32' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'IPv4 remote with IPv6 proxy must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Protocol mismatch (IPv6 remote against IPv4 proxy) fails closed', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.0/8' => 'X-Forwarded-For'],
            remoteAddr: '2001:db8::1',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'IPv6 remote with IPv4 proxy must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Malformed IP address in remoteAddr fails closed', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.0/8' => 'X-Forwarded-For'],
            remoteAddr: 'not.an.ip.address',
            headers: ['X-Forwarded-Proto' => 'https']
        );
        assertFalse($request->isSecure(), 'Malformed IP string in remoteAddr must return false');
    } finally {
        $_SERVER = $saved;
    }
});

// Group 9: Forwarded off, empty, 0 cases
echo "\nGroup 9: Forwarded off, empty, 0 cases\n";
runTest('Trusted proxy with X-Forwarded-Proto=http returns false', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.1',
            headers: ['X-Forwarded-Proto' => 'http']
        );
        assertFalse($request->isSecure(), 'X-Forwarded-Proto=http must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Trusted proxy with Front-End-Https=off returns false', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.1',
            headers: ['Front-End-Https' => 'off']
        );
        assertFalse($request->isSecure(), 'Front-End-Https=off must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Trusted proxy with Front-End-Https=OFF (uppercase) returns false', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.1',
            headers: ['Front-End-Https' => 'OFF']
        );
        assertFalse($request->isSecure(), 'Front-End-Https=OFF must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Trusted proxy with Front-End-Https=empty string returns false', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.1',
            headers: ['Front-End-Https' => '']
        );
        assertFalse($request->isSecure(), 'Front-End-Https="" must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Trusted proxy with Front-End-Https=0 returns false', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.1',
            headers: ['Front-End-Https' => '0']
        );
        assertFalse($request->isSecure(), 'Front-End-Https="0" must return false');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Trusted proxy with Front-End-Https=on returns true', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.1',
            headers: ['Front-End-Https' => 'on']
        );
        assertTrue($request->isSecure(), 'Front-End-Https="on" from trusted proxy must return true');
    } finally {
        $_SERVER = $saved;
    }
});

runTest('Trusted proxy with Front-End-Https=1 returns true', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: '10.0.0.1',
            headers: ['Front-End-Https' => '1']
        );
        assertTrue($request->isSecure(), 'Front-End-Https="1" from trusted proxy must return true');
    } finally {
        $_SERVER = $saved;
    }
});

// Group 10: Client IP spoofing resistance (getIPAddress must NOT be used for proxy trust)
echo "\nGroup 10: Client IP spoofing resistance\n";
runTest('Attacker setting X-Forwarded-For to trusted proxy IP does not bypass proxy verification', static function (): void {
    $saved = $_SERVER;
    try {
        unset($_SERVER['HTTPS']);
        $request = createTestRequest(
            proxyIPs: ['10.0.0.1' => 'X-Forwarded-For'],
            remoteAddr: '198.51.100.5', // Direct connection from attacker
            headers: [
                'X-Forwarded-For'   => '10.0.0.1', // Attacker claims to be the trusted proxy
                'X-Forwarded-Proto' => 'https',
            ]
        );
        // Even though getIPAddress() may inspect X-Forwarded-For, isSecure() MUST check actual REMOTE_ADDR
        assertFalse($request->isSecure(), 'Spoofed X-Forwarded-For must NOT grant trusted proxy status');
    } finally {
        $_SERVER = $saved;
    }
});

// Summary
echo "\n=== Security Regression Summary ===\n";
echo "Total Tests: " . ($passed + $failed) . "\n";
echo "Passed:      {$passed}\n";
echo "Failed:      {$failed}\n";

if ($failed > 0) {
    echo "\nFAILED: Regression suite encountered failures.\n";
    exit(1);
}

echo "\nSUCCESS: All security regression assertions passed.\n";
exit(0);
