<?php

declare(strict_types=1);

/**
 * CLI-server router for isolated browser navigation acceptance.
 *
 * Security & Invariant Safeguards:
 * 1. SAPI: Strictly enforces PHP_SAPI === 'cli-server'. Unconditionally rejects Apache/FPM.
 * 2. Binding: Strictly binds to loopback 127.0.0.1:57527.
 * 3. Origin: Strictly rejects unexpected HTTP_HOST headers; locks app.baseURL to fixed 127.0.0.1:57527.
 * 4. Run Token: Requires ephemeral per-run proof of ownership (DENTA_TEST_RUN_TOKEN header or cookie).
 * 5. Preflight: Web-side independent validation of test database isolation, disjointness from business DB,
 *    and _test_db_marker ownership before any application controller boots or writes.
 * 6. Asset Confinement: Strictly confines static files to public/ directory with extension whitelist.
 * 7. Substitutions:
 *    - TestNormalizedView: Normalizes Windows backslashes in view paths for CI4 testing.
 *    - TestSession: Ensures native PHP sessions under testing environment.
 *    - TestDummyToolbar: Suppresses DebugToolbar /?debugbar injection which corrupts _ci_previous_url.
 */

if (PHP_SAPI !== 'cli-server') {
    header('HTTP/1.1 403 Forbidden');
    echo 'Forbidden: cli-server execution only.';
    exit(1);
}

$remote = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remote, ['127.0.0.1', '::1', 'localhost'], true)) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Forbidden: local loopback only.';
    exit(1);
}

$serverPort = (string) ($_SERVER['SERVER_PORT'] ?? '');
if ($serverPort !== '57527') {
    http_response_code(400);
    echo 'Bad Request: fixed port 57527 required.';
    exit(1);
}
$hostHeader = $_SERVER['HTTP_HOST'] ?? '';
if (!in_array($hostHeader, ["127.0.0.1:{$serverPort}", "localhost:{$serverPort}"], true)) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Bad Request: unexpected host header.';
    exit(1);
}

$rawUri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = (string) parse_url($rawUri, PHP_URL_PATH);
$repoRoot = dirname(__DIR__, 2);
$publicDir = realpath($repoRoot . '/public');

// Static Asset Confinement Check
if ($publicDir !== false && $uri !== '/') {
    $filePath = realpath($publicDir . urldecode($uri));
    $allowedExts = ['css', 'js', 'png', 'jpg', 'jpeg', 'svg', 'woff', 'woff2', 'ico'];
    $ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));

    if ($filePath && is_file($filePath) && str_starts_with($filePath, $publicDir . DIRECTORY_SEPARATOR) && in_array($ext, $allowedExts, true)) {
        return false; // PHP cli-server handles static file directly
    }
}

// Enforce Ephemeral Per-Run Token on Application Requests
$expectedToken = getenv('DENTA_TEST_RUN_TOKEN');
if (!$expectedToken) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Internal Error: DENTA_TEST_RUN_TOKEN not configured in server environment.';
    exit(1);
}

$receivedToken = $_SERVER['HTTP_X_TEST_RUN_TOKEN'] ?? ($_COOKIE['ci_test_run_token'] ?? '');
if (!hash_equals($expectedToken, (string) $receivedToken)) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Forbidden: invalid or missing run ownership token.';
    exit(1);
}

$runId = getenv('DENTA_TEST_RUN_ID');
if (!is_string($runId) || !preg_match('/^[a-f0-9]{16}$/', $runId)) {
    http_response_code(500); echo 'Owned run identifier missing.'; exit(1);
}

// Issue session token cookie if header was provided, to maintain browser context across redirects
if (!empty($_SERVER['HTTP_X_TEST_RUN_TOKEN'])) {
    header('Set-Cookie: ci_test_run_token=' . urlencode($expectedToken) . '; Path=/; SameSite=Lax; HttpOnly', false);
}

// Redacted request trace
error_log(sprintf('[TEST HTTP] %s %s', $_SERVER['REQUEST_METHOD'] ?? '', $uri));

// Enforce Environment Variables for Testing
putenv('CI_ENVIRONMENT=testing');
$_ENV['CI_ENVIRONMENT'] = 'testing';
$_SERVER['CI_ENVIRONMENT'] = 'testing';

defined('TESTPATH') || define('TESTPATH', realpath($repoRoot . '/tests') . DIRECTORY_SEPARATOR);
defined('SUPPORTPATH') || define('SUPPORTPATH', realpath($repoRoot . '/tests/_support') . DIRECTORY_SEPARATOR);

putenv('app.forceGlobalSecureRequests=false');
$_ENV['app.forceGlobalSecureRequests'] = 'false';
$_SERVER['app.forceGlobalSecureRequests'] = 'false';

putenv('cookie.secure=false');
$_ENV['cookie.secure'] = 'false';
$_SERVER['cookie.secure'] = 'false';

$_SERVER['HTTPS'] = 'off';
$_SERVER['SERVER_PORT'] = '57527';

$fixedOrigin = 'http://127.0.0.1:57527/';
putenv('app.baseURL=' . $fixedOrigin);
$_ENV['app.baseURL'] = $fixedOrigin;
$_SERVER['app.baseURL'] = $fixedOrigin;

// Load Verified Test Connection from Environment
require_once $repoRoot . '/scripts/TestDatabasePreflight.php';
require_once $repoRoot . '/vendor/autoload.php';

use Scripts\TestDatabasePreflight;

require_once __DIR__ . '/PatientNavigationConnection.php';
try {
    $conn = \Tests\Support\PatientNavigationConnection::load();
    \Tests\Support\PatientNavigationConnection::isolateWritable($runId);
}
catch (\Throwable $error) {
    http_response_code(500);
    echo 'Test connection configuration rejected.';
    exit(1);
}

TestDatabasePreflight::enforceEnvironment(TestDatabasePreflight::APPROVED_DISPOSABLE_TEST_DB);

foreach ($conn as $key => $value) {
    $name = 'database.tests.' . $key;
    $valStr = (string) $value;
    $encoded = $key === 'DBPrefix' ? $valStr : ($valStr === '' ? 'empty' : ($valStr === '0' ? 0 : $valStr));
    putenv($name . '=' . $encoded);
    $_ENV[$name] = $encoded;
    $_SERVER[$name] = $encoded;
}
putenv('database.defaultGroup=tests');
$_ENV['database.defaultGroup'] = 'tests';
$_SERVER['database.defaultGroup'] = 'tests';

// Hook into pre_system for web-side preflight and testing adaptations
\CodeIgniter\Events\Events::on('pre_system', static function (): void {
    // 1. Independent Web-Side Preflight Check Before Any Controller or Service Boots
    $dbConfig = new \Config\Database();
    $cfgErrors = \Scripts\TestDatabasePreflight::validateConfigBeforeConnection($dbConfig->tests, $dbConfig->default);
    if (!empty($cfgErrors) || $dbConfig->defaultGroup !== 'tests') {
        header('HTTP/1.1 500 Internal Server Error');
        echo 'Preflight Error: ' . implode('; ', $cfgErrors ?: ['defaultGroup must be tests']);
        exit(1);
    }

    $db = \Config\Database::connect('tests');
    $liveErrors = \Scripts\TestDatabasePreflight::validateLiveConnection($db);
    if (!empty($liveErrors)) {
        header('HTTP/1.1 500 Internal Server Error');
        echo 'Preflight Live Error: ' . implode('; ', $liveErrors);
        exit(1);
    }

    $ownership = \Scripts\TestDatabasePreflight::verifyOwnershipForReset($db);
    if (!$db->tableExists(\Scripts\TestDatabasePreflight::OWNERSHIP_MARKER_TABLE) || empty($ownership['allowed'])) {
        header('HTTP/1.1 500 Internal Server Error');
        echo 'Preflight Marker Error: ' . ($ownership['reason'] ?? 'missing marker');
        exit(1);
    }
    header('X-Test-Run-Id: ' . getenv('DENTA_TEST_RUN_ID'));
    if (!class_exists('TestNormalizedView', false)) {
        class TestNormalizedView extends \CodeIgniter\View\View
        {
            public function render(string $view, ?array $options = null, ?bool $saveData = null): string
            {
                $normalized = str_replace('\\', '/', $view);
                return parent::render($normalized, $options, $saveData);
            }
        }
    }

    if (!class_exists('TestSession', false)) {
        class TestSession extends \CodeIgniter\Session\Session
        {
            protected function startSession()
            {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
            }
        }
    }

    if (!class_exists('TestDummyToolbar', false)) {
        class TestDummyToolbar
        {
            public function respond() {}
            public function run() {}
            public function prepare() {}
        }
    }

    $paths = new \Config\Paths();
    $logger = \Config\Services::logger();

    \Config\Services::injectMock(
        'renderer',
        new TestNormalizedView(
            config(\Config\View::class),
            $paths->viewDirectory,
            \Config\Services::locator(),
            CI_DEBUG,
            $logger
        )
    );

    $sessionConfig = config(\Config\Session::class);
    $driverName = $sessionConfig->driver;
    if ($driverName === \CodeIgniter\Session\Handlers\DatabaseHandler::class) {
        $driverName = \CodeIgniter\Session\Handlers\Database\MySQLiHandler::class;
    }
    $ip = \Config\Services::request()->getIPAddress();
    $driver = new $driverName($sessionConfig, $ip);
    $driver->setLogger($logger);
    $session = new TestSession($driver, $sessionConfig);
    $session->setLogger($logger);
    if (session_status() === PHP_SESSION_NONE) {
        $session->start();
    }

    \Config\Services::injectMock('session', $session);
    \Config\Services::injectMock('toolbar', new TestDummyToolbar());
});

// Optional slow request delay simulation for Case 4
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && str_contains($rawUri, '/patient/store') && !empty($_SERVER['HTTP_X_TEST_SLOW_POST'])) {
    usleep(1200000);
}

// Route through CodeIgniter front controller
require $publicDir . '/index.php';
