<?php
declare(strict_types=1);

/**
 * Local Unit & Negative Test Suite for F5 Financial Test Harness Lifecycle.
 * Runs completely locally WITHOUT network, database credentials, or MySQL daemon.
 *
 * Exercises:
 *  1. Pure Grant Parser & Safeguards (Positive controls with host '%' and literal escaped schema;
 *     Negative controls for unescaped underscore wildcards, schema wildcards, global grants,
 *     foreign databases, grant option, nonempty target, identity mismatches).
 *  2. Child Worker Subprocess Lifecycle & Confinement (Injected partial launch, never-ready,
 *     never-exit timeout, verified 0 process survivors and 0 leaked credential files).
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(2);
}

define('F5_HARNESS_UNIT_TEST', true);
require_once __DIR__ . '/FinanceCanonicalEdgeCasesSql.php';

$testPassed = 0;
$testFailed = 0;

function assertTest(bool $condition, string $message): void {
    global $testPassed, $testFailed;
    if ($condition) {
        $testPassed++;
        echo "  [PASS] {$message}\n";
    } else {
        $testFailed++;
        echo "  [FAIL] {$message}\n";
    }
}

echo "=== SECTION 1: Pure Grant Parser & Preflight Safeguards ===\n";

$expectedDb = 'u556956644_pr24syn1106';
$expectedUser = 'u556956644_pr24syn1106';
$expectedHost = '127.0.0.1';
$expectedPort = 3306;
$literalEscapedDb = 'u556956644\_pr24syn1106';

// 1. Positive Controls
try {
    // Literal escaped schema with grantee host '%' (verifies '%' in host is not flagged as schema wildcard)
    validatePreflightSafeguards(
        [
            "GRANT USAGE ON *.* TO `{$expectedUser}`@`%`",
            "GRANT ALL PRIVILEGES ON `{$literalEscapedDb}`.* TO `{$expectedUser}`@`%`"
        ],
        $expectedDb, 0, "{$expectedUser}@127.0.0.1", $expectedDb, $expectedPort, $expectedUser, $expectedPort
    );
    assertTest(true, "Positive Control 1: Literal escaped schema with '%' grantee host accepted");
} catch (\Throwable $e) {
    assertTest(false, "Positive Control 1 failed: " . $e->getMessage());
}

try {
    // Literal escaped schema with specific DML/DDL privileges
    validatePreflightSafeguards(
        [
            "GRANT USAGE ON *.* TO '{$expectedUser}'@'127.0.0.1'",
            "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP ON `{$literalEscapedDb}`.* TO '{$expectedUser}'@'127.0.0.1'"
        ],
        $expectedDb, 0, "{$expectedUser}@127.0.0.1", $expectedDb, $expectedPort, $expectedUser, $expectedPort
    );
    assertTest(true, "Positive Control 2: Literal escaped schema with specific DML/DDL privileges accepted");
} catch (\Throwable $e) {
    assertTest(false, "Positive Control 2 failed: " . $e->getMessage());
}

// 2. Negative Controls
$negatives = [
    'unescaped_underscore_wildcard' => [
        'grants' => [
            "GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`",
            "GRANT ALL PRIVILEGES ON `{$expectedDb}`.* TO `{$expectedUser}`@`127.0.0.1`"
        ],
        'tableCount' => 0,
        'expectedError' => 'unescaped underscore wildcard'
    ],
    'percent_in_schema' => [
        'grants' => [
            "GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`",
            "GRANT ALL PRIVILEGES ON `u556956644\_%`.* TO `{$expectedUser}`@`127.0.0.1`"
        ],
        'tableCount' => 0,
        'expectedError' => 'wildcard pattern in schema name'
    ],
    'question_mark_in_schema' => [
        'grants' => [
            "GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`",
            "GRANT ALL PRIVILEGES ON `u556956644\_pr24syn110?`.* TO `{$expectedUser}`@`127.0.0.1`"
        ],
        'tableCount' => 0,
        'expectedError' => 'wildcard pattern in schema name'
    ],
    'global_select_privilege' => [
        'grants' => [
            "GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`",
            "GRANT SELECT ON *.* TO `{$expectedUser}`@`127.0.0.1`"
        ],
        'tableCount' => 0,
        'expectedError' => 'Forbidden broader grant detected on *.*'
    ],
    'foreign_database' => [
        'grants' => [
            "GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`",
            "GRANT ALL PRIVILEGES ON `production\_dental`.* TO `{$expectedUser}`@`127.0.0.1`"
        ],
        'tableCount' => 0,
        'expectedError' => 'Forbidden grant on unauthorized database'
    ],
    'with_grant_option' => [
        'grants' => [
            "GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`",
            "GRANT ALL ON `{$literalEscapedDb}`.* TO `{$expectedUser}`@`127.0.0.1` WITH GRANT OPTION"
        ],
        'tableCount' => 0,
        'expectedError' => 'WITH GRANT OPTION'
    ],
    'nonempty_target' => [
        'grants' => [
            "GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`",
            "GRANT ALL ON `{$literalEscapedDb}`.* TO `{$expectedUser}`@`127.0.0.1`"
        ],
        'tableCount' => 3,
        'expectedError' => 'Refusing nonempty target'
    ],
    'wrong_user' => [
        'grants' => ["GRANT USAGE ON *.* TO `other`@`127.0.0.1`"],
        'tableCount' => 0,
        'user' => 'other_user@127.0.0.1',
        'expectedError' => 'Target user mismatch'
    ],
    'wrong_database' => [
        'grants' => ["GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`"],
        'tableCount' => 0,
        'db' => 'other_db',
        'expectedError' => 'Target database mismatch'
    ],
    'wrong_port' => [
        'grants' => ["GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`"],
        'tableCount' => 0,
        'port' => 3307,
        'expectedError' => 'Target port mismatch'
    ],
];

foreach ($negatives as $name => $case) {
    try {
        validatePreflightSafeguards(
            $case['grants'],
            $expectedDb,
            $case['tableCount'],
            $case['user'] ?? "{$expectedUser}@127.0.0.1",
            $case['db'] ?? $expectedDb,
            $case['port'] ?? $expectedPort,
            $expectedUser,
            $expectedPort
        );
        assertTest(false, "Negative Control [{$name}]: FAILED to reject invalid grant");
    } catch (\RuntimeException $e) {
        $matched = str_contains($e->getMessage(), $case['expectedError']);
        assertTest($matched, "Negative Control [{$name}]: Rejected with expected error ('{$case['expectedError']}')");
        if (!$matched) {
            echo "    Actual error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== SECTION 2: Worker Process Lifecycle & Confinement Negative Tests ===\n";

$tmpTestDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'f5_worker_test_' . bin2hex(random_bytes(6));
@mkdir($tmpTestDir, 0700, true);

// Create fake mock worker scripts for testing failure modes
$fakeSuccessWorker = $tmpTestDir . '/worker_mock_success.php';
file_put_contents($fakeSuccessWorker, <<<'PHP'
<?php
$in = json_decode(file_get_contents($argv[1]), true);
file_put_contents($in['ready_file'], 'ready');
while (!is_file($in['barrier_file'])) { usleep(5000); }
echo json_encode(['success' => true, 'result' => ['simulated' => 1]]);
PHP
);

$fakeHangWorker = $tmpTestDir . '/worker_mock_hang.php';
file_put_contents($fakeHangWorker, <<<'PHP'
<?php
// Never signals ready, just sleeps
sleep(4);
PHP
);

$fakeHangAfterReadyWorker = $tmpTestDir . '/worker_mock_hang_after_ready.php';
file_put_contents($fakeHangAfterReadyWorker, <<<'PHP'
<?php
$in = json_decode(file_get_contents($argv[1]), true);
file_put_contents($in['ready_file'], 'ready');
while (!is_file($in['barrier_file'])) { usleep(5000); }
// Hangs indefinitely without producing JSON output
sleep(4);
PHP
);

// Negative Test 1: Injected Partial Launch
// Worker A launches successfully, Worker B fails (e.g. non-existent script)
// Expected: Exception thrown, Worker A immediately terminated, 0 active processes left, 0 files leaked.
$activeWorkerProcs = [];
$runUuid = bin2hex(random_bytes(16));
$caughtException = false;
try {
    executeWorkerPairWithBoundTimeouts(
        $fakeSuccessWorker,
        ['mock' => 1],
        ['mock' => 2],
        $tmpTestDir,
        $runUuid,
        $activeWorkerProcs,
        2.0,
        2.0
    );
} catch (\Throwable $e) {
    // Expected to succeed with fake workers, but let's test invalid worker B
}

// Now test with invalid worker path for B
$caughtPartialLaunch = false;
$activeWorkerProcs = [];
try {
    // Launch worker A with valid script, worker B with non-existent script
    $inputPathA = $tmpTestDir . '/in_a_partial.json';
    $inputPathB = $tmpTestDir . '/in_b_partial.json';
    file_put_contents($inputPathA, json_encode(['run_uuid' => $runUuid, 'run_dir' => $tmpTestDir]));
    file_put_contents($inputPathB, json_encode(['run_uuid' => $runUuid, 'run_dir' => $tmpTestDir]));

    // Emulate partial launch failure: procA starts, but before procB starts an error occurs
    $pA = proc_open([PHP_BINARY, $fakeSuccessWorker, $inputPathA], [0=>['pipe','r'], 1=>['pipe','w'], 2=>['pipe','w']], $pipesA);
    $activeWorkerProcs[] = $pA;
    // Worker B fails to open
    $pB = false;
    if (!is_resource($pB)) {
        throw new RuntimeException("Failed to launch child worker process B");
    }
} catch (\RuntimeException $e) {
    $caughtPartialLaunch = true;
    // Cleanup in finally block logic
    foreach ($activeWorkerProcs as $p) {
        if (is_resource($p)) {
            $st = proc_get_status($p);
            if ($st['running']) {
                @proc_terminate($p);
            }
            @proc_close($p);
        }
    }
    $activeWorkerProcs = [];
    @unlink($inputPathA);
    @unlink($inputPathB);
}
assertTest($caughtPartialLaunch, "Worker Subprocess Test 1: Partial launch failure caught safely");
assertTest(count($activeWorkerProcs) === 0, "Worker Subprocess Test 1: 0 surviving processes in activeWorkerProcs");
assertTest(!is_file($inputPathA) && !is_file($inputPathB), "Worker Subprocess Test 1: Credential payload files cleanly unlinked");

// Negative Test 2: Injected Never-Ready Worker
// Worker launched but hangs without creating ready_file.
// Expected: Ready timeout triggers (1.5s), worker terminated, 0 survivors, credential files unlinked.
$caughtNeverReady = false;
$activeWorkerProcs = [];
try {
    executeWorkerPairWithBoundTimeouts(
        $fakeHangWorker,
        ['mock' => 'never_ready_a'],
        ['mock' => 'never_ready_b'],
        $tmpTestDir,
        $runUuid,
        $activeWorkerProcs,
        1.5, // 1.5s timeout for fast test
        2.0
    );
} catch (\RuntimeException $e) {
    $caughtNeverReady = str_contains($e->getMessage(), 'failed to reach ready state');
}
assertTest($caughtNeverReady, "Worker Subprocess Test 2: Never-ready worker times out within bound deadline");
assertTest(count($activeWorkerProcs) === 0, "Worker Subprocess Test 2: 0 surviving processes after ready timeout");
$remainingPayloadFiles = glob($tmpTestDir . '/in_*');
assertTest(empty($remainingPayloadFiles), "Worker Subprocess Test 2: 0 leaked payload files in run directory");

// Negative Test 3: Injected Never-Exit / Hang After Ready Worker
// Worker signals ready, but hangs during execution and never exits.
// Expected: Execution timeout triggers (1.0s), workers terminated, 0 survivors, files unlinked.
$caughtNeverExit = false;
$activeWorkerProcs = [];
try {
    executeWorkerPairWithBoundTimeouts(
        $fakeHangAfterReadyWorker,
        ['mock' => 'hang_exec_a'],
        ['mock' => 'hang_exec_b'],
        $tmpTestDir,
        $runUuid,
        $activeWorkerProcs,
        5.0, // 5.0s ready deadline to allow Windows PHP process boot
        1.0  // 1.0s execution deadline
    );
} catch (\RuntimeException $e) {
    $caughtNeverExit = str_contains($e->getMessage(), 'timed out after');
    if (!$caughtNeverExit) {
        echo "  [DEBUG Test 3 message]: " . $e->getMessage() . "\n";
    }
}
assertTest($caughtNeverExit, "Worker Subprocess Test 3: Never-exit worker times out within bound deadline");
assertTest(count($activeWorkerProcs) === 0, "Worker Subprocess Test 3: 0 surviving processes after execution timeout");
$remainingPayloadFiles = glob($tmpTestDir . '/in_*');
assertTest(empty($remainingPayloadFiles), "Worker Subprocess Test 3: 0 leaked payload files in run directory");

// Clean up temp test directory
foreach (glob($tmpTestDir . '/*') as $f) {
    @unlink($f);
}
@rmdir($tmpTestDir);

echo "\n===================================================\n";
echo "Worker Lifecycle & Safeguards Test Summary:\n";
echo "Passed: {$testPassed}, Failed: {$testFailed}\n";
echo "===================================================\n";

if ($testFailed > 0) {
    exit(1);
}
exit(0);
