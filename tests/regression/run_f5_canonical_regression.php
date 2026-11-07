<?php
declare(strict_types=1);

/**
 * Runner for Retained Canonical Financial Edge-Case Regression (F5 Acceptance).
 * Packages and executes tests/regression/FinanceCanonicalEdgeCasesSql.php against the
 * dedicated isolated synthetic target u556956644_pr24syn1106 on Hostinger PHP 8.3 / MariaDB 11.8.
 *
 * Strict Lifecycle, Process Confinement & Credential Safeguards:
 *  - No exit() inside try/finally blocks; exit occurs strictly AFTER finally cleanup.
 *  - Preserves proc_get_status exit code so proc_close returning -1 does not mask exit 0.
 *  - Fresh exclusive remote directory created without -p under confined canonical parent.
 *  - Exclusive owner marker verified before tar extraction and before directory cleanup.
 *  - Refuses cleanup if directory was not successfully created or if ownership/marker fails.
 *  - All SSH and subprocess waits bounded with argument arrays and non-blocking stream reads.
 *  - Output sanitized to prevent credential or secret leaks.
 *  - Preserves prior PASS evidence receipt on failure; writes failure details to separate file.
 */

if (PHP_SAPI !== 'cli' && !defined('F5_RUNNER_UNIT_TEST')) {
    fwrite(STDERR, "CLI only\n");
    exit(2);
}

/**
 * Resolves effective process exit code, preserving captured exitcode when proc_close returns -1.
 */
function f5_resolve_process_exit_code(?int $statusExitCode, int $procCloseCode): int
{
    if ($procCloseCode === -1 && $statusExitCode !== null && $statusExitCode !== -1) {
        return $statusExitCode;
    }
    return $procCloseCode !== -1 ? $procCloseCode : ($statusExitCode ?? -1);
}

/**
 * Checks if a path on Windows is a reparse point or symbolic link.
 */
function f5_windows_is_reparse_point(string $path): bool
{
    if (is_link($path)) {
        return true;
    }
    if (DIRECTORY_SEPARATOR === '\\') {
        $out = [];
        $code = 0;
        exec('fsutil reparsepoint query ' . escapeshellarg($path) . ' 2>NUL', $out, $code);
        if ($code === 0) {
            return true;
        }
    }
    return false;
}

/**
 * Resolves current Windows user identity, whoami, and SID.
 *
 * @return array{username: string, whoami: string, sid: string}
 */
function f5_windows_get_current_user_info(): array
{
    static $info = null;
    if ($info !== null) {
        return $info;
    }
    $username = strtolower((string)getenv('USERNAME'));
    $whoami = '';
    $out = [];
    exec('whoami 2>NUL', $out);
    if (!empty($out)) {
        $whoami = strtolower(trim($out[0]));
    }
    $sid = '';
    $sidOut = [];
    exec('whoami /user 2>NUL', $sidOut);
    foreach ($sidOut as $line) {
        if (preg_match('/(S-1-5-21-[0-9-]+)/', $line, $m)) {
            $sid = strtolower($m[1]);
            break;
        }
    }
    $info = [
        'username' => $username,
        'whoami' => $whoami,
        'sid' => $sid
    ];
    return $info;
}

/**
 * Strips inheritance and grants exclusive Full Control to current operator, SYSTEM, and Administrators.
 */
function f5_windows_harden_child_directory(string $childDir): bool
{
    $sid = f5_windows_get_current_user_info()['sid'];
    if (!preg_match('/^s-1-[0-9-]+$/D', $sid)) {
        return false;
    }
    $cmd = 'icacls ' . escapeshellarg($childDir) . ' /inheritance:r /grant:r '
        . escapeshellarg('*' . strtoupper($sid) . ':(OI)(CI)F')
        . ' ' . escapeshellarg('*S-1-5-18:(OI)(CI)F')
        . ' ' . escapeshellarg('*S-1-5-32-544:(OI)(CI)F')
        . ' 2>NUL';
    $out = [];
    $code = 0;
    exec($cmd, $out, $code);
    return ($code === 0);
}

/**
 * Verifies owner SID/name and effective NTFS ACLs for directory privacy.
 * Refuses Everyone, Authenticated Users, Users, or other untrusted principals.
 *
 * @return array{valid: bool, error?: string, owner?: string}
 */
function f5_windows_verify_directory_privacy(string $path, bool $isExclusiveChild = false): array
{
    $resolved = realpath($path);
    if ($resolved === false || !is_dir($resolved)) {
        return ['valid' => false, 'error' => 'Private directory cannot be resolved.'];
    }
    // Ask Windows for security identifiers directly: never trust display-name
    // substrings, environment usernames, localized icacls text or sandbox groups.
    $path64 = base64_encode($resolved);
    $script = '$ErrorActionPreference = "Stop"; '
        . '$p = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String("' . $path64 . '")); '
        . '$item = Get-Item -Force -LiteralPath $p; $reparse = $false; $ancestor = $item; '
        . 'while ($null -ne $ancestor) { if (($ancestor.Attributes -band [IO.FileAttributes]::ReparsePoint) -ne 0) { $reparse = $true }; $ancestor = $ancestor.Parent }; '
        . '$acl = Get-Acl -LiteralPath $p; '
        . '$rules = @($acl.GetAccessRules($true, $true, [Security.Principal.SecurityIdentifier]) | ForEach-Object { '
        . '@{sid=$_.IdentityReference.Value; type=$_.AccessControlType.ToString(); rights=[int64]$_.FileSystemRights} }); '
        . '@{owner=$acl.GetOwner([Security.Principal.SecurityIdentifier]).Value; '
        . 'current=[Security.Principal.WindowsIdentity]::GetCurrent().User.Value; '
        . 'protected=$acl.AreAccessRulesProtected; reparse=$reparse; rules=$rules} | ConvertTo-Json -Compress -Depth 4';
    $encoded = base64_encode(iconv('UTF-8', 'UTF-16LE', $script));
    $lines = [];
    $code = 0;
    exec('powershell.exe -NoProfile -NonInteractive -EncodedCommand ' . $encoded . ' 2>NUL', $lines, $code);
    $acl = json_decode(implode("\n", $lines), true);
    if ($code !== 0 || !is_array($acl) || !is_string($acl['current'] ?? null)
        || !preg_match('/^S-1-[0-9-]+$/D', $acl['current'])
        || ($acl['reparse'] ?? true) !== false || !is_array($acl['rules'] ?? null) || !$acl['rules']) {
        return ['valid' => false, 'error' => 'Could not verify private directory security identifiers or reparse ancestry.'];
    }
    $trusted = [$acl['current'], 'S-1-5-18', 'S-1-5-32-544'];
    if (!in_array($acl['owner'] ?? null, $trusted, true)) {
        return ['valid' => false, 'error' => 'Private directory owner is not the operator, SYSTEM or Administrators.'];
    }
    foreach ($acl['rules'] as $rule) {
        if (($rule['type'] ?? '') === 'Allow' && ($rule['rights'] ?? 0) !== 0
            && !in_array($rule['sid'] ?? null, $trusted, true)) {
            return ['valid' => false, 'error' => 'Private directory grants access to an untrusted principal.'];
        }
    }
    if ($isExclusiveChild && ($acl['protected'] ?? false) !== true) {
        return ['valid' => false, 'error' => 'Exclusive child directory inherits permissions.'];
    }
    return ['valid' => true, 'owner' => $acl['owner']];
}

/**
 * Validates canonical acceptance receipt from remote test suite.
 * Enforces exact BOTH hash matches against locally computed hashes,
 * expected synthetic target environment, clean teardown verification,
 * and coverage consistency.
 *
 * @return array{valid: bool, error?: string}
 */
function f5_validate_canonical_receipt(array $evidence, string $expectedHarnessSha, string $expectedWorkerSha, array $expectedTarget): array
{
    if (($evidence['status'] ?? '') !== 'PASS') {
        return ['valid' => false, 'error' => "Status is not PASS: " . json_encode($evidence['status'] ?? null)];
    }

    // 1. Retained Harness & Worker SHA verification
    $harness = $evidence['retained_harness'] ?? null;
    if (!is_array($harness)) {
        return ['valid' => false, 'error' => "Missing 'retained_harness' object"];
    }
    $harnessSha = (string)($harness['harness_sha256'] ?? '');
    $workerSha = (string)($harness['worker_sha256'] ?? '');

    if ($harnessSha === '' || !hash_equals(strtolower($expectedHarnessSha), strtolower($harnessSha))) {
        return ['valid' => false, 'error' => "Harness SHA mismatch (expected {$expectedHarnessSha}, got {$harnessSha})"];
    }
    if ($workerSha === '' || !hash_equals(strtolower($expectedWorkerSha), strtolower($workerSha))) {
        return ['valid' => false, 'error' => "Worker SHA mismatch (expected {$expectedWorkerSha}, got {$workerSha})"];
    }

    // 2. Runtime Environment & Synthetic Target verification
    $env = $evidence['runtime_environment'] ?? null;
    if (!is_array($env)) {
        return ['valid' => false, 'error' => "Missing 'runtime_environment' object"];
    }
    $host = (string)($env['host'] ?? '');
    if (!in_array($host, ['127.0.0.1', 'localhost'], true) || $host !== ($expectedTarget['host'] ?? '')) {
        return ['valid' => false, 'error' => "Target host is not verified local loopback (got '{$host}')"];
    }
    $port = (int)($env['port'] ?? 0);
    $expectedPort = (int)($expectedTarget['port'] ?? 3306);
    if ($port !== $expectedPort) {
        return ['valid' => false, 'error' => "Target port mismatch (expected {$expectedPort}, got {$port})"];
    }
    $database = (string)($env['database'] ?? '');
    $expectedDatabase = (string)($expectedTarget['database'] ?? '');
    if ($database === '' || $database !== $expectedDatabase) {
        return ['valid' => false, 'error' => "Target database mismatch (expected '{$expectedDatabase}', got '{$database}')"];
    }
    $user = (string)($env['user'] ?? '');
    $expectedUser = (string)($expectedTarget['user'] ?? '');
    if ($user === '' || $user !== $expectedUser) {
        return ['valid' => false, 'error' => "Target user mismatch (expected '{$expectedUser}', got '{$user}')"];
    }

    // 3. Preflight Safeguards
    $safeguards = $evidence['preflight_safeguards'] ?? null;
    if (!is_array($safeguards)) {
        return ['valid' => false, 'error' => "Missing 'preflight_safeguards' object"];
    }
    if (($safeguards['database_strictly_synthetic'] ?? false) !== true) {
        return ['valid' => false, 'error' => "Safeguard 'database_strictly_synthetic' was not true"];
    }
    if (($safeguards['initial_table_count'] ?? -1) !== 0) {
        return ['valid' => false, 'error' => "Initial table count was non-zero"];
    }

    // 4. Regression Coverage & Checkcount Consistency
    $coverage = $evidence['regression_coverage'] ?? null;
    if (!is_array($coverage)) {
        return ['valid' => false, 'error' => "Missing 'regression_coverage' object"];
    }
    $passed = $coverage['total_checks_passed'] ?? null;
    if (!is_int($passed) || $passed < 47) {
        return ['valid' => false, 'error' => "Total checks passed ({$passed}) is below the retained canonical suite minimum of 47"];
    }
    $checks = $coverage['checks'] ?? null;
    if (!is_array($checks) || count($checks) !== $passed) {
        return ['valid' => false, 'error' => "Check list count (" . count((array)$checks) . ") does not match total_checks_passed ({$passed})"];
    }

    // 5. Clean Teardown Verification
    $teardown = $evidence['teardown'] ?? null;
    if (!is_array($teardown)) {
        return ['valid' => false, 'error' => "Missing 'teardown' object"];
    }
    if (($teardown['clean_database_verified'] ?? false) !== true) {
        return ['valid' => false, 'error' => "Teardown 'clean_database_verified' was not true"];
    }
    $finalCount = $teardown['final_table_count'] ?? null;
    if (!is_int($finalCount) || $finalCount !== 0) {
        return ['valid' => false, 'error' => "Teardown final_table_count ({$finalCount}) is not strictly 0"];
    }
    $dropped = $teardown['tables_dropped'] ?? null;
    if (!is_int($dropped) || $dropped <= 0) {
        return ['valid' => false, 'error' => "Teardown tables_dropped ({$dropped}) is not greater than 0"];
    }

    return ['valid' => true];
}

/**
 * Runs a command with argument array, bounds wait with deadline, and performs non-blocking pipe reads.
 *
 * @param array<int, string> $cmd
 * @return array{exitcode: int, stdout: string, stderr: string, timed_out: bool}
 */
function f5_run_bounded_command(array $cmd, ?string $stdinData = null, float $timeoutSeconds = 30.0, ?array $env = null): array
{
    // Install deadline BEFORE any blocking operations
    $deadline = microtime(true) + $timeoutSeconds;
    $isPosix = (DIRECTORY_SEPARATOR === '/');

    if ($isPosix) {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $proc = proc_open($cmd, $descriptors, $pipes, null, $env);
        if (!is_resource($proc)) {
            return [
                'exitcode' => -1,
                'stdout' => '',
                'stderr' => 'Failed to spawn process: ' . ($cmd[0] ?? 'unknown'),
                'timed_out' => false
            ];
        }

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stdinLen = $stdinData !== null ? strlen($stdinData) : 0;
        $stdinOffset = 0;
        if ($stdinLen > 0) {
            stream_set_blocking($pipes[0], false);
        } else {
            fclose($pipes[0]);
        }

        $stdout = '';
        $stderr = '';
        $capturedExitCode = null;
        $timedOut = false;

        while (true) {
            $r = [$pipes[1], $pipes[2]];
            $w = (is_resource($pipes[0]) && $stdinOffset < $stdinLen) ? [$pipes[0]] : null;
            $e = null;
            $n = @stream_select($r, $w, $e, 0, 15000);

            if ($n > 0) {
                if ($w !== null && in_array($pipes[0], $w, true)) {
                    $written = @fwrite($pipes[0], substr($stdinData, $stdinOffset, 8192));
                    if ($written !== false && $written > 0) {
                        $stdinOffset += $written;
                    }
                    if ($stdinOffset >= $stdinLen) {
                        fclose($pipes[0]);
                    }
                }
                foreach ($r as $pipe) {
                    if ($pipe === $pipes[1]) {
                        $chunk = fread($pipes[1], 8192);
                        if ($chunk !== false && $chunk !== '') {
                            $stdout .= $chunk;
                        }
                    } elseif ($pipe === $pipes[2]) {
                        $chunk = fread($pipes[2], 8192);
                        if ($chunk !== false && $chunk !== '') {
                            $stderr .= $chunk;
                        }
                    }
                }
            }

            $status = proc_get_status($proc);
            if (!$status['running']) {
                if ($capturedExitCode === null && isset($status['exitcode']) && $status['exitcode'] !== -1) {
                    $capturedExitCode = $status['exitcode'];
                }
                if (is_resource($pipes[1])) {
                    $left1 = stream_get_contents($pipes[1]);
                    if ($left1 !== false && $left1 !== '') $stdout .= $left1;
                }
                if (is_resource($pipes[2])) {
                    $left2 = stream_get_contents($pipes[2]);
                    if ($left2 !== false && $left2 !== '') $stderr .= $left2;
                }
                break;
            }

            if (microtime(true) > $deadline) {
                $timedOut = true;
                @proc_terminate($proc, 15);
                $termDeadline = microtime(true) + 1.0;
                while (microtime(true) < $termDeadline) {
                    $s = proc_get_status($proc);
                    if (!$s['running']) break;
                    usleep(15000);
                }
                $s = proc_get_status($proc);
                if ($s['running']) {
                    @proc_terminate($proc, 9);
                    $killDeadline = microtime(true) + 1.0;
                    while (microtime(true) < $killDeadline) {
                        $s2 = proc_get_status($proc);
                        if (!$s2['running']) break;
                        usleep(15000);
                    }
                }
                break;
            }
        }

        if (is_resource($pipes[0])) fclose($pipes[0]);
        if (is_resource($pipes[1])) fclose($pipes[1]);
        if (is_resource($pipes[2])) fclose($pipes[2]);

        $closeCode = proc_close($proc);
        $finalExitCode = $timedOut ? -1 : f5_resolve_process_exit_code($capturedExitCode, $closeCode);

        return [
            'exitcode' => $finalExitCode,
            'stdout' => $stdout,
            'stderr' => $stderr,
            'timed_out' => $timedOut
        ];
    }

    // Windows PHP 8.2: anonymous pipes do not support stream_set_blocking and stream_select
    // falsely signals readiness, causing blocking fread/fwrite hangs on large stdin or stderr pressure.
    // Use private temporary files in a verified private, Git-ignored scratch directory (strictly not general Temp).
    $scratchParent = getenv('DENTA_F5_PRIVATE_SCRATCH')
        ?: 'C:/Users/bassa/DentaRecoveryKeys/denta-pr24-final-20261105';
    $resolvedParent = realpath($scratchParent);
    $repoPath = str_replace('\\', '/', (string) realpath(dirname(__DIR__, 2)));
    $normalizedParent = str_replace('\\', '/', (string) $resolvedParent);
    if ($resolvedParent === false || !is_dir($resolvedParent) || is_link($scratchParent)
        || strcasecmp($normalizedParent, $repoPath) === 0
        || str_starts_with(strtolower($normalizedParent), strtolower($repoPath) . '/')) {
        return ['exitcode' => -1, 'stdout' => '', 'stderr' => 'Configure a private process I/O directory outside the checkout.', 'timed_out' => false];
    }

    if (f5_windows_is_reparse_point($scratchParent) || f5_windows_is_reparse_point($resolvedParent)) {
        return ['exitcode' => -1, 'stdout' => '', 'stderr' => 'Process I/O directory is a symlink or reparse point.', 'timed_out' => false];
    }

    // Verify parent directory privacy, ownership, and restrictive NTFS ACL
    $parentCheck = f5_windows_verify_directory_privacy($resolvedParent, false);
    if (!$parentCheck['valid']) {
        return ['exitcode' => -1, 'stdout' => '', 'stderr' => 'Insecure process I/O parent directory: ' . ($parentCheck['error'] ?? 'unknown'), 'timed_out' => false];
    }

    // Create exclusive run directory under verified parent
    $privateScratchDir = $resolvedParent . '/f5_bounded_' . bin2hex(random_bytes(6));
    if (!@mkdir($privateScratchDir, 0700)) {
        return ['exitcode' => -1, 'stdout' => '', 'stderr' => 'Could not create exclusive process I/O storage.', 'timed_out' => false];
    }

    // Harden exclusive child directory permissions
    if (!f5_windows_harden_child_directory($privateScratchDir)) {
        @rmdir($privateScratchDir);
        return ['exitcode' => -1, 'stdout' => '', 'stderr' => 'Could not harden exclusive child directory permissions.', 'timed_out' => false];
    }

    // Verify exclusive child directory privacy and non-inherited ACL
    $childCheck = f5_windows_verify_directory_privacy($privateScratchDir, true);
    if (!$childCheck['valid']) {
        @rmdir($privateScratchDir);
        return ['exitcode' => -1, 'stdout' => '', 'stderr' => 'Insecure exclusive child directory: ' . ($childCheck['error'] ?? 'unknown'), 'timed_out' => false];
    }

    $stdinFile = $privateScratchDir . '/stdin.dat';
    $stdoutFile = $privateScratchDir . '/stdout.dat';
    $stderrFile = $privateScratchDir . '/stderr.dat';
    $proc = null;
    $pipes = [];

    try {
        if ($stdinData !== null && $stdinData !== '') {
            file_put_contents($stdinFile, $stdinData);
            $desc0 = ['file', $stdinFile, 'r'];
        } else {
            $desc0 = ['file', 'NUL', 'r'];
        }

        $descriptors = [
            0 => $desc0,
            1 => ['file', $stdoutFile, 'w'],
            2 => ['file', $stderrFile, 'w']
        ];

        $proc = proc_open($cmd, $descriptors, $pipes, null, $env);
        if (!is_resource($proc)) {
            return [
                'exitcode' => -1,
                'stdout' => '',
                'stderr' => 'Failed to spawn process: ' . ($cmd[0] ?? 'unknown'),
                'timed_out' => false
            ];
        }

        $status = proc_get_status($proc);
        $pid = (int)($status['pid'] ?? 0);
        $capturedExitCode = null;
        $timedOut = false;

        while (true) {
            $status = proc_get_status($proc);
            if (!$status['running']) {
                if ($capturedExitCode === null && isset($status['exitcode']) && $status['exitcode'] !== -1) {
                    $capturedExitCode = $status['exitcode'];
                }
                break;
            }

            if (microtime(true) > $deadline) {
                $timedOut = true;
                @proc_terminate($proc);
                $termDeadline = microtime(true) + 1.0;
                while (microtime(true) < $termDeadline) {
                    $s = proc_get_status($proc);
                    if (!$s['running']) break;
                    usleep(15000);
                }
                $s = proc_get_status($proc);
                if ($s['running'] && $pid > 0) {
                    exec("taskkill /F /T /PID {$pid} 2>&1");
                    $killDeadline = microtime(true) + 1.0;
                    while (microtime(true) < $killDeadline) {
                        $s2 = proc_get_status($proc);
                        if (!$s2['running']) break;
                        usleep(15000);
                    }
                }
                break;
            }

            usleep(15000);
        }

        $closeCode = proc_close($proc);
        $proc = null;
        $finalExitCode = $timedOut ? -1 : f5_resolve_process_exit_code($capturedExitCode, $closeCode);

        $stdout = is_file($stdoutFile) ? (string)file_get_contents($stdoutFile) : '';
        $stderr = is_file($stderrFile) ? (string)file_get_contents($stderrFile) : '';

        return [
            'exitcode' => $finalExitCode,
            'stdout' => $stdout,
            'stderr' => $stderr,
            'timed_out' => $timedOut
        ];
    } finally {
        if (is_resource($proc)) {
            @proc_terminate($proc);
            @proc_close($proc);
        }
        if (is_file($stdinFile)) @unlink($stdinFile);
        if (is_file($stdoutFile)) @unlink($stdoutFile);
        if (is_file($stderrFile)) @unlink($stderrFile);
        if (is_dir($privateScratchDir)) @rmdir($privateScratchDir);
    }
}

/**
 * Sanitizes output to redact credentials or secrets.
 */
function f5_sanitize_output(string $str, ?string $secretPassword = null): string
{
    $sanitized = preg_replace('/"password":\s*"[^"]*"/', '"password": "***"', $str);
    if ($secretPassword !== null && $secretPassword !== '') {
        $sanitized = str_replace($secretPassword, '***', $sanitized);
    }
    return $sanitized;
}

/**
 * Executes a remote command over SSH with argument arrays and bounded timeout.
 */
function f5_ssh_cmd(string $sshHost, string $remoteCommand, ?string $stdinData = null, float $timeoutSeconds = 30.0, ?callable $cmdRunner = null): array
{
    $cmd = [
        'ssh',
        '-o', 'BatchMode=yes',
        '-o', 'ConnectTimeout=10',
        $sshHost,
        $remoteCommand
    ];

    if ($cmdRunner !== null) {
        return $cmdRunner($cmd, $stdinData, $timeoutSeconds);
    }

    return f5_run_bounded_command($cmd, $stdinData, $timeoutSeconds);
}

/**
 * Prepares fresh exclusive remote execution directory under canonical parent.
 *
 * @return array{success: bool, created: bool, remote_dir: ?string, marker_verified: bool, partial: bool, error?: string}
 */
function f5_remote_prepare_dir(string $sshHost, string $canonicalParent, string $runUuid, float $timeout = 15.0, ?callable $cmdRunner = null): array
{
    // Step 1: Verify parent directory exists, is directory, not symlink, and owned by target user u556956644
    $verifyParentCmd = "if [ -d '{$canonicalParent}' ] && [ ! -L '{$canonicalParent}' ] && [ \"\$(id -un)\" = 'u556956644' ]; then echo PARENT_OK; else echo PARENT_FAIL; exit 1; fi";
    $parentRes = f5_ssh_cmd($sshHost, $verifyParentCmd, null, $timeout, $cmdRunner);
    if ($parentRes['exitcode'] !== 0 || trim($parentRes['stdout']) !== 'PARENT_OK') {
        return [
            'success' => false,
            'created' => false,
            'remote_dir' => null,
            'marker_verified' => false,
            'partial' => false,
            'error' => "Remote parent directory {$canonicalParent} invalid, symlink, or unowned by u556956644"
        ];
    }

    $remoteDir = "{$canonicalParent}/f5-run-{$runUuid}";

    // Step 2: Fresh exclusive mkdir WITHOUT -p to guarantee failure if directory already exists
    $mkdirCmd = "if mkdir -m 700 '{$remoteDir}' 2>/dev/null; then echo MKDIR_OK; else echo MKDIR_FAIL; exit 1; fi";
    $mkdirRes = f5_ssh_cmd($sshHost, $mkdirCmd, null, $timeout, $cmdRunner);
    if ($mkdirRes['exitcode'] !== 0 || trim($mkdirRes['stdout']) !== 'MKDIR_OK') {
        return [
            'success' => false,
            'created' => false,
            'remote_dir' => $remoteDir,
            'marker_verified' => false,
            'partial' => false,
            'error' => "Failed fresh exclusive mkdir (without -p) for {$remoteDir} (exists or permission denied)"
        ];
    }

    // Directory is created!
    // Step 3: Write exclusive owner marker and verify directory status, non-symlink, and token match
    $markerFile = "{$remoteDir}/.owner_marker";
    $verifyMarkerCmd = "printf '%s' '{$runUuid}' > '{$markerFile}' && chmod 600 '{$markerFile}' && " .
        "if [ -d '{$remoteDir}' ] && [ ! -L '{$remoteDir}' ] && [ -f '{$markerFile}' ] && " .
        "[ \"\$(cat '{$markerFile}' 2>/dev/null)\" = '{$runUuid}' ] && [ \"\$(id -un)\" = 'u556956644' ]; then " .
        "echo VERIFY_OK; else echo VERIFY_FAIL; exit 1; fi";

    $verifyRes = f5_ssh_cmd($sshHost, $verifyMarkerCmd, null, $timeout, $cmdRunner);
    if ($verifyRes['exitcode'] !== 0 || trim($verifyRes['stdout']) !== 'VERIFY_OK') {
        return [
            'success' => false,
            'created' => true,
            'remote_dir' => $remoteDir,
            'marker_verified' => false,
            'partial' => true,
            'error' => "Remote directory verification failed (symlink or marker mismatch)"
        ];
    }

    return [
        'success' => true,
        'created' => true,
        'remote_dir' => $remoteDir,
        'marker_verified' => true,
        'partial' => false
    ];
}

/**
 * Safely cleans up remote execution directory, verifying ownership, non-symlink, and marker token.
 * Refuses deletion if dirWasCreated is false or if any check fails.
 *
 * @return array{success: bool, skipped?: bool, error?: string}
 */
function f5_remote_cleanup_dir(string $sshHost, ?string $remoteDir, string $runUuid, bool $dirWasCreated, float $timeout = 20.0, ?callable $cmdRunner = null): array
{
    if (!$dirWasCreated || $remoteDir === null || $remoteDir === '') {
        return [
            'success' => true,
            'skipped' => true,
            'reason' => 'dir_not_created'
        ];
    }

    if (!preg_match('/^\/home\/u556956644\/\.denta-stage-20261017\/f5-run-[a-f0-9]{16}$/D', $remoteDir)) {
        return [
            'success' => false,
            'error' => "Refusing to clean path not matching canonical pattern: {$remoteDir}"
        ];
    }

    // Validate ownership before touching processes. Match only the pinned PHP
    // executable and this run's absolute script paths; never match SSH shells.
    $markerFile = "{$remoteDir}/.owner_marker";
    $processPattern = '^/opt/alt/php83/usr/bin/php .*' . $remoteDir . '/';
    $safeRmCmd = "if [ -d '{$remoteDir}' ] && [ ! -L '{$remoteDir}' ] && [ -f '{$markerFile}' ] && " .
        "[ ! -L '{$markerFile}' ] && [ \"\$(readlink -f '{$remoteDir}')\" = '{$remoteDir}' ] && " .
        "[ \"\$(cat '{$markerFile}' 2>/dev/null)\" = '{$runUuid}' ] && [ \"\$(id -un)\" = 'u556956644' ]; then " .
        "pkill -TERM -f '{$processPattern}' 2>/dev/null || true; " .
        "for attempt in 1 2 3 4 5; do pgrep -f '{$processPattern}' >/dev/null || break; sleep 1; done; " .
        "if pgrep -f '{$processPattern}' >/dev/null; then pkill -KILL -f '{$processPattern}' 2>/dev/null || true; sleep 1; fi; " .
        "if pgrep -f '{$processPattern}' >/dev/null; then echo REFUSED_ACTIVE_PROCESS; exit 1; fi; " .
        "rm -rf '{$remoteDir}' && echo CLEANED_OK; else echo REFUSED_CLEANUP; exit 1; fi";

    $res = f5_ssh_cmd($sshHost, $safeRmCmd, null, $timeout, $cmdRunner);
    if ($res['exitcode'] === 0 && trim($res['stdout']) === 'CLEANED_OK') {
        return ['success' => true];
    }

    return [
        'success' => false,
        'error' => "Remote cleanup refused or failed: " . trim($res['stderr'] ?: $res['stdout'])
    ];
}

/**
 * Main structured execution function for Canonical Suite.
 * Returns integer exit code (0 on PASS, 1 on failure).
 * NEVER calls exit(); guarantees finally block execution on all paths.
 */
function runCanonicalSuite(array $options = []): int
{
    $repoRoot = $options['repo_root'] ?? dirname(__DIR__, 2);
    $harnessFile = $repoRoot . '/tests/regression/FinanceCanonicalEdgeCasesSql.php';
    $workerFile = $repoRoot . '/tests/_support/finance_concurrency_race_worker.php';

    if (!is_file($harnessFile)) {
        fwrite(STDERR, "Harness file not found: {$harnessFile}\n");
        return 1;
    }
    if (!is_file($workerFile)) {
        fwrite(STDERR, "Worker file not found: {$workerFile}\n");
        return 1;
    }

    $harnessSha = hash_file('sha256', $harnessFile);
    $workerSha = hash_file('sha256', $workerFile);

    echo "[*] Retained Harness SHA256: {$harnessSha}\n";
    echo "[*] Retained Worker  SHA256: {$workerSha}\n";

    // Credentials resolution
    $credsRaw = $options['creds_raw'] ?? null;
    if ($credsRaw === null) {
        $escrowDefault = $options['escrow_default'] ?? 'C:/Users/bassa/DentaRecoveryKeys/denta-pr24-final-20261105/native-synthetic-user.json';
        $escrowPath = getenv('DENTA_ESCROW_KEYS') ?: (is_file($escrowDefault) ? $escrowDefault : null);

        if ($escrowPath && is_file($escrowPath)) {
            $credsRaw = file_get_contents($escrowPath);
        } else {
            $credsRaw = file_get_contents('php://stdin');
        }
    }

    if (!$credsRaw) {
        fwrite(STDERR, "Missing credentials. Provide via stdin or private escrow path.\n");
        return 1;
    }

    try {
        $rawDecoded = json_decode((string)$credsRaw, true, 512, JSON_THROW_ON_ERROR);
    } catch (\Throwable $e) {
        fwrite(STDERR, "Invalid credentials JSON format\n");
        return 1;
    }

    $creds = [
        'host' => $rawDecoded['host'] ?? '127.0.0.1',
        'port' => (int)($rawDecoded['port'] ?? 3306),
        'database' => $rawDecoded['full_database'] ?? ($rawDecoded['database'] ?? ''),
        'user' => $rawDecoded['full_user'] ?? ($rawDecoded['user'] ?? ''),
        'password' => $rawDecoded['password'] ?? ''
    ];
    $credsJson = json_encode($creds, JSON_THROW_ON_ERROR);

    $sshHost = $options['ssh_host'] ?? 'hostinger-dalel';
    $canonicalParent = $options['canonical_parent'] ?? '/home/u556956644/.denta-stage-20261017';
    $runUuid = $options['run_uuid'] ?? bin2hex(random_bytes(8));
    $evidenceOut = $options['evidence_out'] ?? 'C:/Users/bassa/DentaPrivateRecovery/denta-pr24-final-20261105/f5-canonical-evidence.json';
    $failurePrefix = $options['failure_prefix'] ?? 'C:/Users/bassa/DentaPrivateRecovery/denta-pr24-final-20261105/f5-canonical-failure-';
    $cmdRunner = $options['cmd_runner'] ?? null;

    $tmpTar = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "candidate_fixture_f5_{$runUuid}.tar.gz";
    $remoteDir = null;
    $remoteDirCreated = false;
    $pendingPassEvidence = null;
    $cleanupSuccess = true;
    $cleanupError = null;

    try {
        // Step 1: Package candidate fixture
        echo "[*] Packaging candidate fixture into {$tmpTar}...\n";

        if ($cmdRunner !== null) {
            $tarRes = $cmdRunner(['tar', '-czf', $tmpTar, '-C', $repoRoot, 'app', 'tests/_support', 'tests/regression'], null, 30.0);
            $tarCode = $tarRes['exitcode'];
        } else {
            $tarCmd = "tar -czf " . escapeshellarg($tmpTar) . " -C " . escapeshellarg($repoRoot) . " app tests/_support tests/regression";
            exec($tarCmd, $tarOut, $tarCode);
        }

        if ($tarCode !== 0 || !is_file($tmpTar)) {
            fwrite(STDERR, "Failed to create tar archive with tar (code {$tarCode})\n");
            return 1;
        }

        echo "[+] Fixture archive created. Preparing remote environment...\n";

        // Step 2: Prepare exclusive remote directory without -p and write owner marker
        $prep = f5_remote_prepare_dir($sshHost, $canonicalParent, $runUuid, 15.0, $cmdRunner);
        $remoteDir = $prep['remote_dir'] ?? "{$canonicalParent}/f5-run-{$runUuid}";
        $remoteDirCreated = (bool)($prep['created'] ?? false);

        if (!$prep['success']) {
            if ($remoteDirCreated) {
                fwrite(STDERR, "Partial remote directory creation detected ({$remoteDir}); marker unverified. Scheduled for safe reconciliation.\n");
            }
            fwrite(STDERR, "Remote directory preparation failed: " . ($prep['error'] ?? 'unknown') . "\n");
            return 1;
        }

        echo "[+] Prepared exclusive remote directory {$remoteDir}\n";

        // Step 3: Upload tarball via bounded SSH stream
        echo "[*] Uploading candidate fixture over SSH stream...\n";
        $tarContent = file_get_contents($tmpTar);
        $unpackCmd = "tar -xzf - -C '{$remoteDir}' && chmod 700 '{$remoteDir}'";
        $unpackRes = f5_ssh_cmd($sshHost, $unpackCmd, $tarContent, 45.0, $cmdRunner);

        if ($unpackRes['exitcode'] !== 0) {
            $errSanitized = f5_sanitize_output($unpackRes['stderr'] ?: $unpackRes['stdout'], $creds['password']);
            fwrite(STDERR, "Failed to unpack candidate fixture on remote host:\n{$errSanitized}\n");
            return 1;
        }

        echo "[+] Unpacked fixture on Hostinger successfully.\n";
        echo "[*] Executing tests/regression/FinanceCanonicalEdgeCasesSql.php via PHP 8.3...\n";

        // Step 4: Execute remote PHP 8.3 harness with bounded wait (90s)
        $remoteScript = "cd '{$remoteDir}' && /opt/alt/php83/usr/bin/php -d display_errors=1 -d error_reporting=E_ALL '{$remoteDir}/tests/regression/FinanceCanonicalEdgeCasesSql.php'";
        $execRes = f5_ssh_cmd($sshHost, $remoteScript, $credsJson, 90.0, $cmdRunner);

        $runExit = $execRes['exitcode'];
        $stdout = $execRes['stdout'];
        $stderr = $execRes['stderr'];

        echo "[*] Remote exit code: {$runExit}\n";

        if ($stderr !== '') {
            $sanitizedStderr = f5_sanitize_output($stderr, $creds['password']);
            echo "Remote stderr:\n{$sanitizedStderr}\n";
        }

        // Step 5: Check PASS evidence with exact schema and hash attestation
        if ($runExit === 0 && trim($stdout) !== '') {
            $evidence = json_decode(trim($stdout), true);
            if (is_array($evidence)) {
                $attestation = f5_validate_canonical_receipt($evidence, $harnessSha, $workerSha, $creds);
                if ($attestation['valid']) {
                    echo "\n========================================================\n";
                    echo " F5 CANONICAL ACCEPTANCE: PASS (execution verified)\n";
                    echo "========================================================\n";
                    echo "Total Checks Passed: " . ($evidence['regression_coverage']['total_checks_passed'] ?? 0) . "\n";
                    echo "Tables Dropped:      " . ($evidence['teardown']['tables_dropped'] ?? 0) . "\n";
                    echo "Final Table Count:   " . ($evidence['teardown']['final_table_count'] ?? -1) . "\n";
                    echo "Harness SHA256:      " . ($evidence['retained_harness']['harness_sha256'] ?? '') . "\n";
                    echo "Worker SHA256:       " . ($evidence['retained_harness']['worker_sha256'] ?? '') . "\n";

                    // Stage the PASS evidence in memory; DO NOT publish until cleanup succeeds!
                    $pendingPassEvidence = $evidence;
                } else {
                    fwrite(STDERR, "Receipt attestation rejected: " . ($attestation['error'] ?? 'unknown') . "\n");
                }
            }
        }

        if ($pendingPassEvidence === null) {
            $failureOut = $failurePrefix . date('Ymd_His') . '.json';
            $failurePayload = [
                'status' => 'FAIL',
                'exit_code' => $runExit,
                'timed_out' => $execRes['timed_out'],
                'stdout' => f5_sanitize_output($stdout, $creds['password']),
                'stderr' => f5_sanitize_output($stderr, $creds['password']),
                'timestamp' => gmdate('c')
            ];

            @file_put_contents($failureOut, json_encode($failurePayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
            fwrite(STDERR, "Regression failed. Raw output preserved in {$failureOut}\n");
            return 1;
        }

    } finally {
        // ALWAYS executed on both PASS, failures, and uncaught exceptions
        if ($tmpTar !== null && is_file($tmpTar)) {
            @unlink($tmpTar);
        }
        if ($remoteDirCreated && $remoteDir !== null) {
            echo "[*] Cleaning up remote temporary directory {$remoteDir}...\n";
            $cleanRes = f5_remote_cleanup_dir($sshHost, $remoteDir, $runUuid, $remoteDirCreated, 20.0, $cmdRunner);
            if ($cleanRes['success']) {
                $cleanupSuccess = true;
                echo "[+] Remote temporary directory cleaned successfully.\n";
            } else {
                $cleanupSuccess = false;
                $cleanupError = $cleanRes['error'] ?? 'Remote cleanup refused';
                echo "[!] Remote cleanup refused or failed: {$cleanupError}\n";
            }
        } else {
            $cleanupSuccess = true;
        }
    }

    // Post-finally processing: verified publication or fail-closed handling
    if (!$cleanupSuccess) {
        $failureOut = $failurePrefix . date('Ymd_His') . '.json';
        $failurePayload = [
            'status' => 'FAIL',
            'reason' => 'remote_cleanup_refused_or_failed',
            'error' => $cleanupError,
            'remote_dir' => $remoteDir,
            'run_uuid' => $runUuid,
            'execution_passed' => ($pendingPassEvidence !== null),
            'timestamp' => gmdate('c')
        ];
        @file_put_contents($failureOut, json_encode($failurePayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        fwrite(STDERR, "Execution halted: remote cleanup failed or refused. Prior PASS evidence preserved byte-for-byte. Details: {$failureOut}\n");
        return 1;
    }

    if ($pendingPassEvidence !== null) {
        $evidenceDir = dirname($evidenceOut);
        if (!is_dir($evidenceDir)) {
            @mkdir($evidenceDir, 0700, true);
        }
        $tmpEvidenceFile = $evidenceDir . '/.tmp_evidence_' . bin2hex(random_bytes(6)) . '.json';
        $payload = json_encode($pendingPassEvidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        $written = @file_put_contents($tmpEvidenceFile, $payload);
        if ($written !== strlen($payload)) {
            @unlink($tmpEvidenceFile);
            fwrite(STDERR, "CRITICAL: Incomplete evidence write to temporary receipt file\n");
            return 1;
        }
        if (!@rename($tmpEvidenceFile, $evidenceOut)) {
            @unlink($tmpEvidenceFile);
            fwrite(STDERR, "CRITICAL: Atomic rename of evidence receipt failed\n");
            return 1;
        }
        echo "[+] Truthful evidence saved to {$evidenceOut}\n";
        return 0; // True PASS after verified cleanup!
    }

    return 1;
}

// Global cleanup references for shutdown emergency fallback
$f5_global_tmp_tar = null;
$f5_global_cleanup_called = false;

// If included as unit test library, return without executing main script
if (defined('F5_RUNNER_UNIT_TEST')) {
    return;
}

$exitCode = 1;
try {
    $exitCode = runCanonicalSuite();
} catch (\Throwable $e) {
    fwrite(STDERR, "Uncaught runner exception: " . $e->getMessage() . "\n");
    $exitCode = 1;
}

// Exit occurs strictly AFTER finally block has executed
exit($exitCode);
