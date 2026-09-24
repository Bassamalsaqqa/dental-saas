<?php
declare(strict_types=1);

/**
 * Safe Test Runner with Preflight Isolation Safeguard
 *
 * Enforces fail-closed test database isolation before running PHPUnit.
 * Ensures tests NEVER run against or mutate the business database (democa_dental_saas).
 *
 * Usage:
 *   php scripts/safe_test_runner.php [--check-only] [phpunit arguments...]
 *
 * Examples:
 *   php scripts/safe_test_runner.php --check-only
 *   php scripts/safe_test_runner.php --no-coverage --testdox tests/unit/TenantBackupContainmentTest.php
 *   php scripts/safe_test_runner.php --no-coverage tests/unit
 */

const APPROVED_DISPOSABLE_TEST_DB = 'democa_dental_saas_test';

// 1. Ensure exact dotted environment variable is set in process environment
putenv('database.tests.database=' . APPROVED_DISPOSABLE_TEST_DB);
$_ENV['database.tests.database'] = APPROVED_DISPOSABLE_TEST_DB;
$_SERVER['database.tests.database'] = APPROVED_DISPOSABLE_TEST_DB;

// Also set in Windows process environment via .NET if on Windows CLI
if (PHP_OS_FAMILY === 'Windows') {
    // Exact dotted key is already in putenv/$_ENV
}

// 2. Bootstrap testing environment to inspect resolved configuration
$repoRoot = dirname(__DIR__);
require_once $repoRoot . '/vendor/autoload.php';
require_once $repoRoot . '/system/Test/bootstrap.php';

$dbConfig = new Config\Database();
$businessDb = (string) ($dbConfig->default['database'] ?? '');
$resolvedTestDb = (string) ($dbConfig->tests['database'] ?? '');

// 3. Preflight Isolation Check (Fail-Closed)
$errors = [];

if ($resolvedTestDb === '') {
    $errors[] = "Preflight check failed: 'database.tests.database' resolved to empty string.";
}

if ($businessDb !== '' && $resolvedTestDb === $businessDb) {
    $errors[] = "CRITICAL ISOLATION FAILURE: tests database ('{$resolvedTestDb}') equals default business database ('{$businessDb}'). Refusing to execute.";
}

if ($resolvedTestDb !== APPROVED_DISPOSABLE_TEST_DB) {
    $errors[] = "Preflight check failed: tests database ('{$resolvedTestDb}') does not match approved disposable test database ('" . APPROVED_DISPOSABLE_TEST_DB . "').";
}

// 4. Live Connection & SELECT DATABASE() verification
try {
    $db = \Config\Database::connect('tests');
    $row = $db->query('SELECT DATABASE() AS current_db, @@hostname AS host_name')->getRowArray();
    $liveDb = (string) ($row['current_db'] ?? '');
    $liveHost = (string) ($row['host_name'] ?? '');

    if ($liveDb !== APPROVED_DISPOSABLE_TEST_DB) {
        $errors[] = "CRITICAL LIVE QUERY FAILURE: Live SELECT DATABASE() returned '{$liveDb}', expected '" . APPROVED_DISPOSABLE_TEST_DB . "'.";
    }

    if ($businessDb !== '' && $liveDb === $businessDb) {
        $errors[] = "CRITICAL LIVE QUERY FAILURE: Live SELECT DATABASE() matches business database '{$businessDb}'!";
    }
} catch (\Throwable $e) {
    $errors[] = "Failed to connect to tests database connection: " . $e->getMessage();
}

if (!empty($errors)) {
    fwrite(STDERR, "\n========================================================\n");
    fwrite(STDERR, " FAIL-CLOSED PREFLIGHT ISOLATION ERROR\n");
    fwrite(STDERR, "========================================================\n");
    foreach ($errors as $err) {
        fwrite(STDERR, " [!] " . $err . "\n");
    }
    fwrite(STDERR, "Aborting execution without running tests or mutating data.\n\n");
    exit(1);
}

// Check for --check-only argument
$cliArgs = array_slice($argv, 1);
$checkOnly = false;
$filteredArgs = [];

foreach ($cliArgs as $arg) {
    if ($arg === '--check-only') {
        $checkOnly = true;
    } else {
        $filteredArgs[] = $arg;
    }
}

if ($checkOnly) {
    echo "========================================================\n";
    echo " TEST DATABASE PREFLIGHT ISOLATION: PASSED\n";
    echo "========================================================\n";
    echo " Default Business Database: {$businessDb}\n";
    echo " Configured Test Database: {$resolvedTestDb}\n";
    echo " Live SELECT DATABASE():    {$liveDb} (host: {$liveHost})\n";
    echo " Isolation Status:         VERIFIED (Isolated disposable target)\n";
    echo "========================================================\n";
    exit(0);
}

// 5. Forward execution to PHPUnit with the verified environment
$phpunitBin = $repoRoot . '/vendor/bin/phpunit';
if (PHP_OS_FAMILY === 'Windows' && file_exists($repoRoot . '/vendor/bin/phpunit.bat')) {
    $phpunitBin = $repoRoot . '/vendor/bin/phpunit.bat';
}

$escapedArgs = array_map('escapeshellarg', $filteredArgs);
$cmd = escapeshellarg($phpunitBin) . ' ' . implode(' ', $escapedArgs);

$descriptors = [
    0 => STDIN,
    1 => STDOUT,
    2 => STDERR,
];

// Ensure full environment (including PATH) and dotted env var are explicitly passed into the child process env
$procEnv = [];
foreach (array_merge(getenv(), $_SERVER) as $k => $v) {
    if (is_scalar($v)) {
        $procEnv[(string) $k] = (string) $v;
    }
}
$procEnv['database.tests.database'] = APPROVED_DISPOSABLE_TEST_DB;

$process = proc_open($cmd, $descriptors, $pipes, $repoRoot, $procEnv);

if (!is_resource($process)) {
    fwrite(STDERR, "Failed to launch PHPUnit process.\n");
    exit(1);
}

$exitCode = proc_close($process);
exit($exitCode);
