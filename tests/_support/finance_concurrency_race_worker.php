<?php
declare(strict_types=1);

/**
 * Concurrency Race Worker for Canonical Financial Settlement Races.
 * Executed in isolated child processes for multi-connection concurrency testing.
 *
 * Strict Isolation & Security Safeguards:
 *  - CLI execution only.
 *  - Validates private input path, run ownership, and restrictive file modes.
 *  - Enforces local loopback target strictly (127.0.0.1:3306).
 *  - Enforces authorized synthetic database strictly (u556956644_pr24syn1106).
 *  - Rejects arbitrary bootstrap path fallbacks.
 *  - Deterministic barrier synchronization before lock acquisition with bounded waits.
 *  - Bounded lock wait timeouts and complete rollback in finally.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(2);
}

$inputFile = $argv[1] ?? '';
if (!is_string($inputFile) || $inputFile === '' || !is_file($inputFile)) {
    fwrite(STDERR, "Worker input file not found or invalid: {$inputFile}\n");
    exit(2);
}

$realInputPath = realpath($inputFile);
if ($realInputPath === false || is_link($inputFile)) {
    fwrite(STDERR, "Worker input file cannot be a symlink or unresolved path\n");
    exit(2);
}

// On POSIX systems, ensure file is not group/world readable
if (DIRECTORY_SEPARATOR === '/') {
    $perms = fileperms($realInputPath) & 0777;
    if (($perms & 0077) !== 0) {
        fwrite(STDERR, "Worker input file permissions too open ({$perms}); expected 0600\n");
        exit(2);
    }
}

$rawInput = file_get_contents($realInputPath);
if (!$rawInput) {
    fwrite(STDERR, "Worker input file is empty\n");
    exit(2);
}

try {
    $input = json_decode($rawInput, true, 512, JSON_THROW_ON_ERROR);
} catch (\Throwable $e) {
    fwrite(STDERR, "Invalid worker input JSON: " . $e->getMessage() . "\n");
    exit(2);
}

$runUuid = $input['run_uuid'] ?? '';
$runDir = $input['run_dir'] ?? '';
if (!is_string($runUuid) || !preg_match('/^[a-f0-9]{32}$/D', $runUuid)) {
    fwrite(STDERR, "Invalid or missing worker run_uuid\n");
    exit(2);
}
if (!is_string($runDir) || !is_dir($runDir) || !str_starts_with($realInputPath, realpath($runDir))) {
    fwrite(STDERR, "Worker input file must reside within validated private run_dir\n");
    exit(2);
}

$appDir = $input['app_dir'] ?? '';
$systemDir = $input['system_dir'] ?? '';
$writableDir = $input['writable_dir'] ?? '';
$creds = $input['creds'] ?? [];

$expectedDb = 'u556956644_pr24syn1106';
$expectedUser = 'u556956644_pr24syn1106';
$expectedHost = '127.0.0.1';
$expectedPort = 3306;

if (($creds['database'] ?? '') !== $expectedDb || ($creds['user'] ?? '') !== $expectedUser || ($creds['host'] ?? '') !== $expectedHost || (int)($creds['port'] ?? 0) !== $expectedPort) {
    fwrite(STDERR, "Worker target mismatch. Expected strictly {$expectedUser}@{$expectedHost}:{$expectedPort}/{$expectedDb}\n");
    exit(2);
}

if (!is_string($systemDir) || !is_dir($systemDir)) {
    fwrite(STDERR, "Worker systemDir invalid or missing: {$systemDir}\n");
    exit(2);
}

// Set environment for database connection across both test and default groups
putenv("database.tests.hostname={$creds['host']}");
putenv("database.tests.port={$creds['port']}");
putenv("database.tests.database={$creds['database']}");
putenv("database.tests.username={$creds['user']}");
putenv("database.tests.password={$creds['password']}");
putenv("database.tests.DBDriver=MySQLi");
putenv("database.tests.DBPrefix=");

putenv("database.default.hostname={$creds['host']}");
putenv("database.default.port={$creds['port']}");
putenv("database.default.database={$creds['database']}");
putenv("database.default.username={$creds['user']}");
putenv("database.default.password={$creds['password']}");
putenv("database.default.DBDriver=MySQLi");
putenv("database.default.DBPrefix=");

$_ENV['database.tests.hostname'] = $creds['host'];
$_ENV['database.tests.port'] = $creds['port'];
$_ENV['database.tests.database'] = $creds['database'];
$_ENV['database.tests.username'] = $creds['user'];
$_ENV['database.tests.password'] = $creds['password'];
$_ENV['database.tests.DBDriver'] = 'MySQLi';
$_ENV['database.tests.DBPrefix'] = '';

$_ENV['database.default.hostname'] = $creds['host'];
$_ENV['database.default.port'] = $creds['port'];
$_ENV['database.default.database'] = $creds['database'];
$_ENV['database.default.username'] = $creds['user'];
$_ENV['database.default.password'] = $creds['password'];
$_ENV['database.default.DBDriver'] = 'MySQLi';
$_ENV['database.default.DBPrefix'] = '';

defined('FCPATH') || define('FCPATH', dirname($appDir) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require_once $appDir . '/Config/Paths.php';
$paths = new Config\Paths();
$paths->appDirectory = $appDir;
$paths->systemDirectory = $systemDir;
$paths->writableDirectory = $writableDir;
defined('ENVIRONMENT') || define('ENVIRONMENT', 'testing');
require_once $systemDir . '/Boot.php';
CodeIgniter\Boot::bootConsole($paths);

$db = Config\Database::connect('tests', false);
if ($db->database !== $expectedDb) {
    fwrite(STDERR, "Worker database connection mismatch: {$db->database}\n");
    exit(2);
}
$db->query("SET SESSION innodb_lock_wait_timeout = 8");

// Signal worker ready state
$readyFile = $input['ready_file'] ?? '';
if (!is_string($readyFile) || $readyFile === '') {
    fwrite(STDERR, "Worker ready_file parameter missing\n");
    exit(2);
}
file_put_contents($readyFile, 'ready');
if (DIRECTORY_SEPARATOR === '/') {
    @chmod($readyFile, 0600);
}

// Wait for master barrier release (strictly bounded wait of max 10.0 seconds)
$barrierFile = $input['barrier_file'] ?? '';
if (!is_string($barrierFile) || $barrierFile === '') {
    fwrite(STDERR, "Worker barrier_file parameter missing\n");
    exit(2);
}
$until = microtime(true) + 10.0;
while (!is_file($barrierFile)) {
    if (microtime(true) > $until) {
        echo json_encode(['success' => false, 'error' => 'Worker barrier timeout waiting for release']);
        exit(1);
    }
    usleep(10000); // 10ms
}

// Execute requested race action under lock
try {
    $service = new App\Services\FinanceSettlementService($db);
    $clinic = (int) $input['clinic_id'];
    $actor = (int) $input['actor_id'];
    $targets = $input['targets'];
    $wire = $input['wire_context'];
    $terms = $input['terms'] ?? [];
    $action = $input['action'] ?? 'mark_paid';

    $res = $service->bulkSettle($clinic, $targets, $actor, $terms, $wire, $action);
    echo json_encode(['success' => true, 'result' => $res]);
} catch (App\Services\FinanceSettlementConflict $e) {
    echo json_encode(['success' => false, 'conflict' => $e->reason, 'code' => $e->getCode()]);
} catch (\Throwable $e) {
    echo json_encode(['success' => false, 'error' => get_class($e) . ': ' . $e->getMessage()]);
} finally {
    while ($db->transDepth > 0) {
        $db->transRollback();
    }
    $db->close();
}
exit(0);
