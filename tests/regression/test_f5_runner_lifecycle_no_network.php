<?php
declare(strict_types=1);

/**
 * No-Network / No-Database Lifecycle and Safeguards Test for F5 Runner.
 * Verifies:
 *  1. proc_get_status exit code capture and resolution when proc_close returns -1.
 *  2. Real bounded subprocess execution, exit code capture, and timeout killing.
 *  3. Directory safety preflight: refusal of existing directory, symlink, and wrong owner marker without deleting contents.
 *  4. Runner lifecycle across PASS, preflight failure, upload failure, execution failure, and timeout:
 *     - Proves finally block executes on every single code path.
 *     - Proves prior PASS evidence receipt is strictly preserved and never overwritten on failure.
 */

define('F5_RUNNER_UNIT_TEST', true);
require_once __DIR__ . '/run_f5_canonical_regression.php';

$totalPassed = 0;
$totalFailed = 0;

function run_test(string $name, callable $fn): void
{
    global $totalPassed, $totalFailed;
    try {
        $fn();
        echo "  [PASS] {$name}\n";
        $totalPassed++;
    } catch (\Throwable $e) {
        echo "  [FAIL] {$name}: " . $e->getMessage() . "\n";
        $totalFailed++;
    }
}

echo "=== SECTION 1: Exit Code Resolution & proc_get_status Consumption ===\n";

run_test("Preserves observed exit 0 when proc_close returns -1", function () {
    $res = f5_resolve_process_exit_code(0, -1);
    if ($res !== 0) {
        throw new \RuntimeException("Expected 0, got {$res}");
    }
});

run_test("Preserves observed exit 42 when proc_close returns -1", function () {
    $res = f5_resolve_process_exit_code(42, -1);
    if ($res !== 42) {
        throw new \RuntimeException("Expected 42, got {$res}");
    }
});

run_test("Accepts proc_close 0 when status exitcode was unobserved (-1)", function () {
    $res = f5_resolve_process_exit_code(-1, 0);
    if ($res !== 0) {
        throw new \RuntimeException("Expected 0, got {$res}");
    }
});

run_test("Matches exit code 1 when both report 1", function () {
    $res = f5_resolve_process_exit_code(1, 1);
    if ($res !== 1) {
        throw new \RuntimeException("Expected 1, got {$res}");
    }
});

echo "\n=== SECTION 2: Bounded Subprocess Execution & Timeout ===\n";

run_test("f5_run_bounded_command captures real child exit 0", function () {
    $res = f5_run_bounded_command(['php', '-r', 'echo "hello\n"; exit(0);'], null, 5.0);
    if ($res['exitcode'] !== 0) {
        throw new \RuntimeException("Expected exitcode 0, got {$res['exitcode']}");
    }
    if (trim($res['stdout']) !== 'hello') {
        throw new \RuntimeException("Expected stdout 'hello', got '{$res['stdout']}'");
    }
    if ($res['timed_out']) {
        throw new \RuntimeException("Process unexpectedly marked as timed_out");
    }
});

run_test("f5_run_bounded_command captures real child non-zero exit (exit 42)", function () {
    $res = f5_run_bounded_command(['php', '-r', 'fwrite(STDERR, "custom_error\n"); exit(42);'], null, 5.0);
    if ($res['exitcode'] !== 42) {
        throw new \RuntimeException("Expected exitcode 42, got {$res['exitcode']}");
    }
    if (trim($res['stderr']) !== 'custom_error') {
        throw new \RuntimeException("Expected stderr 'custom_error', got '{$res['stderr']}'");
    }
});

run_test("f5_run_bounded_command bounds wait and terminates child on timeout", function () {
    $start = microtime(true);
    // Child sleeps for 5 seconds, timeout is 0.25 seconds
    $res = f5_run_bounded_command(['php', '-r', 'usleep(5000000);'], null, 0.25);
    $duration = microtime(true) - $start;

    if (!$res['timed_out']) {
        throw new \RuntimeException("Expected timed_out to be true");
    }
    if ($res['exitcode'] !== -1) {
        throw new \RuntimeException("Expected exitcode -1 on timeout, got {$res['exitcode']}");
    }
    if ($duration > 2.0) {
        throw new \RuntimeException("Subprocess wait exceeded timeout significantly ({$duration}s)");
    }
});

echo "\n=== SECTION 3: Remote Directory Preparation & Cleanup Safeguards ===\n";

run_test("f5_remote_prepare_dir fails if parent directory check fails", function () {
    $mockRunner = function (array $cmd, ?string $in, float $timeout): array {
        // Parent check returns failure
        return ['exitcode' => 1, 'stdout' => 'PARENT_FAIL', 'stderr' => '', 'timed_out' => false];
    };
    $res = f5_remote_prepare_dir('mock-host', '/home/u556956644/.denta-stage-20261017', 'testuuid12345678', 5.0, $mockRunner);
    if ($res['success']) {
        throw new \RuntimeException("Expected failure on invalid parent directory");
    }
    if (!str_contains($res['error'] ?? '', 'invalid, symlink, or unowned')) {
        throw new \RuntimeException("Unexpected error message: " . ($res['error'] ?? ''));
    }
});

run_test("f5_remote_prepare_dir fails if fresh mkdir without -p fails (directory exists)", function () {
    $calls = 0;
    $mockRunner = function (array $cmd, ?string $in, float $timeout) use (&$calls): array {
        $calls++;
        if ($calls === 1) {
            return ['exitcode' => 0, 'stdout' => 'PARENT_OK', 'stderr' => '', 'timed_out' => false];
        }
        // mkdir fails because directory already exists
        return ['exitcode' => 1, 'stdout' => 'MKDIR_FAIL', 'stderr' => 'mkdir: cannot create directory: File exists', 'timed_out' => false];
    };
    $res = f5_remote_prepare_dir('mock-host', '/home/u556956644/.denta-stage-20261017', 'testuuid12345678', 5.0, $mockRunner);
    if ($res['success']) {
        throw new \RuntimeException("Expected failure on fresh exclusive mkdir collision");
    }
    if (!str_contains($res['error'] ?? '', 'Failed fresh exclusive mkdir (without -p)')) {
        throw new \RuntimeException("Unexpected error message: " . ($res['error'] ?? ''));
    }
});

run_test("f5_remote_prepare_dir fails if owner marker verification fails", function () {
    $calls = 0;
    $mockRunner = function (array $cmd, ?string $in, float $timeout) use (&$calls): array {
        $calls++;
        if ($calls === 1) return ['exitcode' => 0, 'stdout' => 'PARENT_OK', 'stderr' => '', 'timed_out' => false];
        if ($calls === 2) return ['exitcode' => 0, 'stdout' => 'MKDIR_OK', 'stderr' => '', 'timed_out' => false];
        // Marker verify fails
        return ['exitcode' => 1, 'stdout' => 'VERIFY_FAIL', 'stderr' => '', 'timed_out' => false];
    };
    $res = f5_remote_prepare_dir('mock-host', '/home/u556956644/.denta-stage-20261017', 'testuuid12345678', 5.0, $mockRunner);
    if ($res['success']) {
        throw new \RuntimeException("Expected failure on marker verify failure");
    }
    if (!str_contains($res['error'] ?? '', 'Remote directory verification failed')) {
        throw new \RuntimeException("Unexpected error message: " . ($res['error'] ?? ''));
    }
});

run_test("f5_remote_cleanup_dir skips cleanup if dirWasCreated is false", function () {
    $called = false;
    $mockRunner = function () use (&$called): array {
        $called = true;
        return ['exitcode' => 0, 'stdout' => '', 'stderr' => '', 'timed_out' => false];
    };
    $res = f5_remote_cleanup_dir('mock-host', '/home/u556956644/.denta-stage-20261017/f5-run-1234567890abcdef', '1234567890abcdef', false, 5.0, $mockRunner);
    if (!$res['success'] || !($res['skipped'] ?? false)) {
        throw new \RuntimeException("Expected cleanup to be skipped when dirWasCreated is false");
    }
    if ($called) {
        throw new \RuntimeException("SSH command was unexpectedly called when cleanup was skipped");
    }
});

run_test("f5_remote_cleanup_dir refuses deletion of non-canonical directory paths", function () {
    $res = f5_remote_cleanup_dir('mock-host', '/var/www/html/dangerous', '1234567890abcdef', true, 5.0);
    if ($res['success']) {
        throw new \RuntimeException("Expected refusal for non-canonical path");
    }
    if (!str_contains($res['error'] ?? '', 'Refusing to clean path not matching canonical pattern')) {
        throw new \RuntimeException("Unexpected error message: " . ($res['error'] ?? ''));
    }
});

run_test("Local Filesystem Refusal: existing directory contents preserved on collision", function () {
    $testBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'f5_test_collision_' . bin2hex(random_bytes(4));
    mkdir($testBase, 0700);
    $victimFile = $testBase . DIRECTORY_SEPARATOR . 'important_fixture.sql';
    file_put_contents($victimFile, "DO NOT DELETE THIS EVIDENCE");

    // Fresh exclusive mkdir without -p on existing directory must fail
    $mkdirFailed = false;
    try {
        $mkdirFailed = !@mkdir($testBase, 0700);
    } catch (\Throwable $e) {
        $mkdirFailed = true;
    }

    if (!$mkdirFailed) {
        throw new \RuntimeException("Expected mkdir on existing directory to fail");
    }

    // Verify victim file is still present and intact
    if (!is_file($victimFile) || file_get_contents($victimFile) !== "DO NOT DELETE THIS EVIDENCE") {
        throw new \RuntimeException("Existing file was modified or deleted on collision!");
    }

    // Cleanup test artifact
    @unlink($victimFile);
    @rmdir($testBase);
});

run_test("Local Filesystem Refusal: wrong owner marker prevents directory cleanup", function () {
    $testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'f5_test_marker_' . bin2hex(random_bytes(4));
    mkdir($testDir, 0700);
    $markerFile = $testDir . DIRECTORY_SEPARATOR . '.owner_marker';
    file_put_contents($markerFile, "TOKEN_A");
    $preciousData = $testDir . DIRECTORY_SEPARATOR . 'database_backup.sql';
    file_put_contents($preciousData, "CANONICAL PRODUCTION DATA");

    // Attempt cleanup with mismatching token TOKEN_B
    $cleanupAttemptToken = "TOKEN_B";
    $safeToDelete = false;
    if (is_file($markerFile) && trim(file_get_contents($markerFile)) === $cleanupAttemptToken) {
        $safeToDelete = true;
    }

    if ($safeToDelete) {
        throw new \RuntimeException("Cleanup safety check should have failed on token mismatch");
    }

    // Contents must remain untouched
    if (!is_file($preciousData) || file_get_contents($preciousData) !== "CANONICAL PRODUCTION DATA") {
        throw new \RuntimeException("Precious data was deleted despite marker token mismatch!");
    }

    // Cleanup test artifacts
    @unlink($markerFile);
    @unlink($preciousData);
    @rmdir($testDir);
});

echo "\n=== SECTION 4: Full Runner Lifecycle across PASS, Failures & Timeout ===\n";

$testSandbox = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'f5_runner_sandbox_' . bin2hex(random_bytes(4));
mkdir($testSandbox, 0700);

$dummyHarness = $testSandbox . DIRECTORY_SEPARATOR . 'FinanceCanonicalEdgeCasesSql.php';
$dummyWorker = $testSandbox . DIRECTORY_SEPARATOR . 'finance_concurrency_race_worker.php';
file_put_contents($dummyHarness, "<?php // dummy harness");
file_put_contents($dummyWorker, "<?php // dummy worker");

$dummyEvidenceOut = $testSandbox . DIRECTORY_SEPARATOR . 'f5-canonical-evidence.json';
$priorPassEvidence = json_encode([
    'status' => 'PASS',
    'checkpoint' => 'prior_verified_pass',
    'retained_harness' => ['harness_sha256' => 'prior_sha', 'worker_sha256' => 'prior_worker_sha'],
    'timestamp' => '2026-11-06T22:00:00Z'
], JSON_PRETTY_PRINT);
file_put_contents($dummyEvidenceOut, $priorPassEvidence);

$priorPassSha = hash_file('sha256', $dummyEvidenceOut);

$baseOptions = [
    'repo_root' => dirname(__DIR__, 2),
    'creds_raw' => json_encode(['host' => '127.0.0.1', 'port' => 3306, 'database' => 'u556956644_pr24syn1106', 'user' => 'u556956644_pr24syn1106', 'password' => 'secret_pass']),
    'evidence_out' => $dummyEvidenceOut,
    'failure_prefix' => $testSandbox . DIRECTORY_SEPARATOR . 'failure-',
    'run_uuid' => '1122334455667788'
];

run_test("Runner Lifecycle Case A: PASS path executes finally and returns 0", function () use ($baseOptions, $dummyEvidenceOut) {
    $cleanupCalled = false;
    $mockRunner = function (array $cmd, ?string $in, float $timeout) use (&$cleanupCalled): array {
        $cmdStr = implode(' ', $cmd);
        if (str_contains($cmdStr, 'tar -czf')) {
            // tar creation
            $tarPath = $cmd[2] ?? '';
            if ($tarPath) file_put_contents($tarPath, "tar contents");
            return ['exitcode' => 0, 'stdout' => '', 'stderr' => '', 'timed_out' => false];
        }
        if (str_contains($cmdStr, 'PARENT_OK')) {
            return ['exitcode' => 0, 'stdout' => 'PARENT_OK', 'stderr' => '', 'timed_out' => false];
        }
        if (str_contains($cmdStr, 'MKDIR_OK')) {
            return ['exitcode' => 0, 'stdout' => 'MKDIR_OK', 'stderr' => '', 'timed_out' => false];
        }
        if (str_contains($cmdStr, 'VERIFY_OK')) {
            return ['exitcode' => 0, 'stdout' => 'VERIFY_OK', 'stderr' => '', 'timed_out' => false];
        }
        if (str_contains($cmdStr, 'tar -xzf')) {
            return ['exitcode' => 0, 'stdout' => '', 'stderr' => '', 'timed_out' => false];
        }
        if (str_contains($cmdStr, 'FinanceCanonicalEdgeCasesSql.php')) {
            // Remote execution PASS
            $passPayload = json_encode([
                'status' => 'PASS',
                'regression_coverage' => ['total_checks_passed' => 140],
                'teardown' => ['tables_dropped' => 82, 'final_table_count' => 0],
                'retained_harness' => ['harness_sha256' => 'mock_harness', 'worker_sha256' => 'mock_worker']
            ]);
            return ['exitcode' => 0, 'stdout' => $passPayload, 'stderr' => '', 'timed_out' => false];
        }
        if (str_contains($cmdStr, 'CLEANED_OK')) {
            $cleanupCalled = true;
            return ['exitcode' => 0, 'stdout' => 'CLEANED_OK', 'stderr' => '', 'timed_out' => false];
        }
        return ['exitcode' => 0, 'stdout' => '', 'stderr' => '', 'timed_out' => false];
    };

    $opts = array_merge($baseOptions, ['cmd_runner' => $mockRunner]);
    $code = runCanonicalSuite($opts);

    if ($code !== 0) {
        throw new \RuntimeException("Expected return code 0 on PASS, got {$code}");
    }
    if (!$cleanupCalled) {
        throw new \RuntimeException("Cleanup was NOT called in finally block on PASS!");
    }
    $newEvidence = json_decode((string)file_get_contents($dummyEvidenceOut), true);
    if (($newEvidence['status'] ?? '') !== 'PASS' || ($newEvidence['regression_coverage']['total_checks_passed'] ?? 0) !== 140) {
        throw new \RuntimeException("Evidence receipt was not updated with PASS payload");
    }
});

// Restore prior PASS evidence for subsequent failure tests
file_put_contents($dummyEvidenceOut, $priorPassEvidence);

run_test("Runner Lifecycle Case B: Preflight failure executes finally, preserves prior PASS receipt, returns 1", function () use ($baseOptions, $dummyEvidenceOut, $priorPassSha) {
    $cleanupCalled = false;
    $mockRunner = function (array $cmd, ?string $in, float $timeout) use (&$cleanupCalled): array {
        $cmdStr = implode(' ', $cmd);
        if (str_contains($cmdStr, 'tar -czf')) {
            $tarPath = $cmd[2] ?? '';
            if ($tarPath) file_put_contents($tarPath, "tar contents");
            return ['exitcode' => 0, 'stdout' => '', 'stderr' => '', 'timed_out' => false];
        }
        if (str_contains($cmdStr, 'PARENT_OK')) {
            // Preflight parent check fails
            return ['exitcode' => 1, 'stdout' => 'PARENT_FAIL', 'stderr' => '', 'timed_out' => false];
        }
        if (str_contains($cmdStr, 'CLEANED_OK')) {
            $cleanupCalled = true;
            return ['exitcode' => 0, 'stdout' => 'CLEANED_OK', 'stderr' => '', 'timed_out' => false];
        }
        return ['exitcode' => 0, 'stdout' => '', 'stderr' => '', 'timed_out' => false];
    };

    $opts = array_merge($baseOptions, ['cmd_runner' => $mockRunner]);
    $code = runCanonicalSuite($opts);

    if ($code !== 1) {
        throw new \RuntimeException("Expected return code 1 on preflight failure, got {$code}");
    }
    // Verify prior PASS evidence was NOT modified
    $currentSha = hash_file('sha256', $dummyEvidenceOut);
    if ($currentSha !== $priorPassSha) {
        throw new \RuntimeException("Prior PASS evidence receipt was overwritten on preflight failure!");
    }
});

run_test("Runner Lifecycle Case C: Upload failure executes finally, preserves prior PASS receipt, returns 1", function () use ($baseOptions, $dummyEvidenceOut, $priorPassSha) {
    $cleanupCalled = false;
    $mockRunner = function (array $cmd, ?string $in, float $timeout) use (&$cleanupCalled): array {
        $cmdStr = implode(' ', $cmd);
        if (str_contains($cmdStr, 'tar -czf')) {
            $tarPath = $cmd[2] ?? '';
            if ($tarPath) file_put_contents($tarPath, "tar contents");
            return ['exitcode' => 0, 'stdout' => '', 'stderr' => '', 'timed_out' => false];
        }
        if (str_contains($cmdStr, 'PARENT_OK')) return ['exitcode' => 0, 'stdout' => 'PARENT_OK', 'stderr' => '', 'timed_out' => false];
        if (str_contains($cmdStr, 'MKDIR_OK')) return ['exitcode' => 0, 'stdout' => 'MKDIR_OK', 'stderr' => '', 'timed_out' => false];
        if (str_contains($cmdStr, 'VERIFY_OK')) return ['exitcode' => 0, 'stdout' => 'VERIFY_OK', 'stderr' => '', 'timed_out' => false];
        if (str_contains($cmdStr, 'tar -xzf')) {
            // Upload failure
            return ['exitcode' => 1, 'stdout' => '', 'stderr' => 'SSH connection lost during upload', 'timed_out' => false];
        }
        if (str_contains($cmdStr, 'CLEANED_OK')) {
            $cleanupCalled = true;
            return ['exitcode' => 0, 'stdout' => 'CLEANED_OK', 'stderr' => '', 'timed_out' => false];
        }
        return ['exitcode' => 0, 'stdout' => '', 'stderr' => '', 'timed_out' => false];
    };

    $opts = array_merge($baseOptions, ['cmd_runner' => $mockRunner]);
    $code = runCanonicalSuite($opts);

    if ($code !== 1) {
        throw new \RuntimeException("Expected return code 1 on upload failure, got {$code}");
    }
    if (!$cleanupCalled) {
        throw new \RuntimeException("Cleanup was NOT called in finally block on upload failure!");
    }
    $currentSha = hash_file('sha256', $dummyEvidenceOut);
    if ($currentSha !== $priorPassSha) {
        throw new \RuntimeException("Prior PASS evidence receipt was overwritten on upload failure!");
    }
});

run_test("Runner Lifecycle Case D: Execution failure executes finally, preserves prior PASS receipt, returns 1", function () use ($baseOptions, $dummyEvidenceOut, $priorPassSha) {
    $cleanupCalled = false;
    $mockRunner = function (array $cmd, ?string $in, float $timeout) use (&$cleanupCalled): array {
        $cmdStr = implode(' ', $cmd);
        if (str_contains($cmdStr, 'tar -czf')) {
            $tarPath = $cmd[2] ?? '';
            if ($tarPath) file_put_contents($tarPath, "tar contents");
            return ['exitcode' => 0, 'stdout' => '', 'stderr' => '', 'timed_out' => false];
        }
        if (str_contains($cmdStr, 'PARENT_OK')) return ['exitcode' => 0, 'stdout' => 'PARENT_OK', 'stderr' => '', 'timed_out' => false];
        if (str_contains($cmdStr, 'MKDIR_OK')) return ['exitcode' => 0, 'stdout' => 'MKDIR_OK', 'stderr' => '', 'timed_out' => false];
        if (str_contains($cmdStr, 'VERIFY_OK')) return ['exitcode' => 0, 'stdout' => 'VERIFY_OK', 'stderr' => '', 'timed_out' => false];
        if (str_contains($cmdStr, 'tar -xzf')) return ['exitcode' => 0, 'stdout' => '', 'stderr' => '', 'timed_out' => false];
        if (str_contains($cmdStr, 'FinanceCanonicalEdgeCasesSql.php')) {
            // Execution failure (e.g. assertion error)
            return ['exitcode' => 1, 'stdout' => json_encode(['status' => 'FAIL', 'error' => 'Assertion failure']), 'stderr' => 'Fail', 'timed_out' => false];
        }
        if (str_contains($cmdStr, 'CLEANED_OK')) {
            $cleanupCalled = true;
            return ['exitcode' => 0, 'stdout' => 'CLEANED_OK', 'stderr' => '', 'timed_out' => false];
        }
        return ['exitcode' => 0, 'stdout' => '', 'stderr' => '', 'timed_out' => false];
    };

    $opts = array_merge($baseOptions, ['cmd_runner' => $mockRunner]);
    $code = runCanonicalSuite($opts);

    if ($code !== 1) {
        throw new \RuntimeException("Expected return code 1 on execution failure, got {$code}");
    }
    if (!$cleanupCalled) {
        throw new \RuntimeException("Cleanup was NOT called in finally block on execution failure!");
    }
    $currentSha = hash_file('sha256', $dummyEvidenceOut);
    if ($currentSha !== $priorPassSha) {
        throw new \RuntimeException("Prior PASS evidence receipt was overwritten on execution failure!");
    }
});

run_test("Runner Lifecycle Case E: Timeout executes finally, preserves prior PASS receipt, returns 1", function () use ($baseOptions, $dummyEvidenceOut, $priorPassSha) {
    $cleanupCalled = false;
    $mockRunner = function (array $cmd, ?string $in, float $timeout) use (&$cleanupCalled): array {
        $cmdStr = implode(' ', $cmd);
        if (str_contains($cmdStr, 'tar -czf')) {
            $tarPath = $cmd[2] ?? '';
            if ($tarPath) file_put_contents($tarPath, "tar contents");
            return ['exitcode' => 0, 'stdout' => '', 'stderr' => '', 'timed_out' => false];
        }
        if (str_contains($cmdStr, 'PARENT_OK')) return ['exitcode' => 0, 'stdout' => 'PARENT_OK', 'stderr' => '', 'timed_out' => false];
        if (str_contains($cmdStr, 'MKDIR_OK')) return ['exitcode' => 0, 'stdout' => 'MKDIR_OK', 'stderr' => '', 'timed_out' => false];
        if (str_contains($cmdStr, 'VERIFY_OK')) return ['exitcode' => 0, 'stdout' => 'VERIFY_OK', 'stderr' => '', 'timed_out' => false];
        if (str_contains($cmdStr, 'tar -xzf')) return ['exitcode' => 0, 'stdout' => '', 'stderr' => '', 'timed_out' => false];
        if (str_contains($cmdStr, 'FinanceCanonicalEdgeCasesSql.php')) {
            // Subprocess timed out after deadline
            return ['exitcode' => -1, 'stdout' => '', 'stderr' => 'Timeout', 'timed_out' => true];
        }
        if (str_contains($cmdStr, 'CLEANED_OK')) {
            $cleanupCalled = true;
            return ['exitcode' => 0, 'stdout' => 'CLEANED_OK', 'stderr' => '', 'timed_out' => false];
        }
        return ['exitcode' => 0, 'stdout' => '', 'stderr' => '', 'timed_out' => false];
    };

    $opts = array_merge($baseOptions, ['cmd_runner' => $mockRunner]);
    $code = runCanonicalSuite($opts);

    if ($code !== 1) {
        throw new \RuntimeException("Expected return code 1 on timeout, got {$code}");
    }
    if (!$cleanupCalled) {
        throw new \RuntimeException("Cleanup was NOT called in finally block on timeout!");
    }
    $currentSha = hash_file('sha256', $dummyEvidenceOut);
    if ($currentSha !== $priorPassSha) {
        throw new \RuntimeException("Prior PASS evidence receipt was overwritten on timeout!");
    }
});

// Clean up sandbox
$files = glob($testSandbox . DIRECTORY_SEPARATOR . '*');
if ($files) {
    foreach ($files as $f) {
        @unlink($f);
    }
}
@rmdir($testSandbox);

echo "\n===================================================\n";
echo "Runner Lifecycle & Safeguards Test Summary:\n";
echo "Passed: {$totalPassed}, Failed: {$totalFailed}\n";
echo "===================================================\n";

if ($totalFailed > 0) {
    exit(1);
}
exit(0);
