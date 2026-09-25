<?php
declare(strict_types=1);

/**
 * Safe Test Runner with Preflight Isolation Safeguard
 *
 * Enforces fail-closed test database isolation before running PHPUnit.
 * Ensures tests NEVER run against or mutate the business database (democa_dental_saas).
 *
 * Usage:
 *   php scripts/safe_test_runner.php [--check-only] [--provision] [phpunit arguments...]
 *
 * Examples:
 *   php scripts/safe_test_runner.php --check-only
 *   php scripts/safe_test_runner.php --provision
 *   php scripts/safe_test_runner.php --no-coverage --testdox tests/unit/TenantBackupContainmentTest.php
 *   php scripts/safe_test_runner.php --no-coverage tests/unit
 */

$repoRoot = dirname(__DIR__);
require_once __DIR__ . '/TestDatabasePreflight.php';

use Scripts\TestDatabasePreflight;

// 1. Enforce exact dotted environment variables for the test database
TestDatabasePreflight::enforceEnvironment();

// 2. Parse safe_test_runner specific flags
$cliArgs = array_slice($argv, 1);
$checkOnly = false;
$provision = false;
$filteredArgs = [];

foreach ($cliArgs as $arg) {
    if ($arg === '--check-only') {
        $checkOnly = true;
    } elseif ($arg === '--provision') {
        $provision = true;
    } else {
        $filteredArgs[] = $arg;
    }
}

// 3. Bootstrap testing environment to inspect resolved configuration
require_once $repoRoot . '/vendor/autoload.php';
require_once $repoRoot . '/system/Test/bootstrap.php';

$dbConfig = new \Config\Database();
$testsConfig = $dbConfig->tests ?? [];
$businessConfig = $dbConfig->default ?? [];

// 4. Preflight Isolation Check BEFORE Connection (Fail-Closed)
$configErrors = TestDatabasePreflight::validateConfigBeforeConnection($testsConfig, $businessConfig);
if (!empty($configErrors)) {
    fwrite(STDERR, "\n========================================================\n");
    fwrite(STDERR, " FAIL-CLOSED PREFLIGHT ISOLATION ERROR\n");
    fwrite(STDERR, "========================================================\n");
    foreach ($configErrors as $err) {
        fwrite(STDERR, " [!] " . $err . "\n");
    }
    fwrite(STDERR, "Aborting execution immediately WITHOUT attempting any connection.\n\n");
    exit(1);
}

$businessDb = (string) ($businessConfig['database'] ?? '');
$resolvedTestDb = (string) ($testsConfig['database'] ?? '');
$testsHost = (string) ($testsConfig['hostname'] ?? '');
$testsPort = (int) ($testsConfig['port'] ?? 3306);
$testsDriver = (string) ($testsConfig['DBDriver'] ?? '');
$targetDb = TestDatabasePreflight::APPROVED_DISPOSABLE_TEST_DB;

// 5. Handle --check-only mode (Strictly read-only, checks existence without mutation)
if ($checkOnly) {
    echo "========================================================\n";
    echo " TEST DATABASE PREFLIGHT ISOLATION: PASSED\n";
    echo "========================================================\n";
    echo " Default Business Database: {$businessDb}\n";
    echo " Configured Test Database: {$resolvedTestDb}\n";
    echo " Target Host:              {$testsHost}:{$testsPort}\n";
    echo " Driver:                   {$testsDriver}\n";

    $host = (string) ($testsConfig['hostname'] ?? '127.0.0.1');
    $port = (int) ($testsConfig['port'] ?? 3306);
    $user = (string) ($testsConfig['username'] ?? 'root');
    $pass = (string) ($testsConfig['password'] ?? '');

    try {
        $checkResult = TestDatabasePreflight::orchestrateCheckOnly(
            fn(string $dbName) => TestDatabasePreflight::checkDatabaseExistsOnServer($host, $user, $pass, $port, $dbName),
            function() {
                $db = \Config\Database::connect('tests');
                $errors = TestDatabasePreflight::validateLiveConnection($db);
                return ['liveDb' => $db->getDatabase(), 'errors' => $errors];
            },
            $targetDb
        );
    } catch (\Throwable $e) {
        fwrite(STDERR, "Preflight check failed: " . $e->getMessage() . "\n");
        exit(1);
    }

    if (!$checkResult['exists']) {
        echo " Target Database Status:   ABSENT / UNPROVISIONED (does not exist on server)\n";
        echo " Isolation Status:         VERIFIED (Isolated disposable target eligible for provisioning)\n";
        echo "========================================================\n";
        echo "Check-only mode requested. Target database is absent; no database created. No mutations performed.\n";
        exit(0);
    }

    if (!empty($checkResult['errors'])) {
        fwrite(STDERR, "\n========================================================\n");
        fwrite(STDERR, " FAIL-CLOSED LIVE IDENTITY ERROR\n");
        fwrite(STDERR, "========================================================\n");
        foreach ($checkResult['errors'] as $err) {
            fwrite(STDERR, " [!] " . $err . "\n");
        }
        fwrite(STDERR, "Aborting execution without running tests or mutating data.\n\n");
        exit(1);
    }

    echo " Live Database:            {$checkResult['liveDb']}\n";
    echo " Target Database Status:   EXISTS\n";
    echo " Isolation Status:         VERIFIED (Isolated disposable target)\n";
    echo "========================================================\n";
    echo "Check-only mode requested. Target database exists and is verified. No mutations performed.\n";
    exit(0);
}

// 6 & 7. Orchestrate pre-test sequence (provisioning before connecting when requested)
try {
    $runnerResult = TestDatabasePreflight::orchestrateRunnerPreTest(
        $provision,
        function() {
            echo "Provisioning test database prior to test execution...\n";
            $provisionScript = __DIR__ . '/provision_test_db.php';
            passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($provisionScript), $provisionExit);
            return $provisionExit;
        },
        function() {
            return \Config\Database::connect('tests');
        },
        function($db) {
            return TestDatabasePreflight::validateLiveConnection($db);
        }
    );
    $db = $runnerResult['db'];
} catch (\Throwable $e) {
    fwrite(STDERR, "Pre-test execution failed: " . $e->getMessage() . "\n");
    exit(1);
}

// 8. Forward execution to PHPUnit with the verified environment
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

// Ensure full environment and dotted env var are explicitly passed into the child process env
$procEnv = [];
foreach (array_merge(getenv(), $_SERVER) as $k => $v) {
    if (is_scalar($v)) {
        $procEnv[(string) $k] = (string) $v;
    }
}
$procEnv['database.tests.database'] = TestDatabasePreflight::APPROVED_DISPOSABLE_TEST_DB;

$process = proc_open($cmd, $descriptors, $pipes, $repoRoot, $procEnv);

if (!is_resource($process)) {
    fwrite(STDERR, "Failed to launch PHPUnit process.\n");
    exit(1);
}

$exitCode = proc_close($process);
exit($exitCode);
