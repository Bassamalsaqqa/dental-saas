<?php
declare(strict_types=1);

/**
 * Retained Canonical Financial Edge-Case Regression Test Harness (F5 Acceptance).
 * Executed directly against the dedicated isolated synthetic target u556956644_pr24syn1106
 * on Hostinger PHP 8.3 / MariaDB 11.8.
 *
 * Strict Security & Isolation Safeguards:
 *  - CLI execution only.
 *  - Raw preflight grant allowlist validation (USAGE ON *.* only; privileges confined strictly to exact synthetic DB).
 *  - Synthetic negative preflight tests verify that nonempty targets, broader grants, and identity mismatches fail closed before DDL.
 *  - Exclusive database advisory lock (denta_f5_canonical_run) acquired before DDL and held across run.
 *  - Strict non-empty target check: refuses to run if any base tables or views exist prior to launch.
 *  - Per-run cryptographic token and marker table (_test_db_marker) tracking.
 *  - Tracked table inventory: only task-created tables verified by the run token are dropped; foreign table drift causes abort.
 *  - Unique private run directory (0700) and files (0600) for child workers.
 *  - All worker processes bounded with stream_select deadlines and terminated in finally.
 *  - Complete rollback of transactions and teardown to 0 tables.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(2);
}

// Pure Grant & Preflight Safeguards Validator
function validatePreflightSafeguards(
    array $grants,
    string $expectedDb,
    int $tableCount,
    string $currentUser,
    string $currentDb,
    int $currentPort,
    string $expectedUser,
    int $expectedPort
): void {
    if (!str_starts_with($currentUser, $expectedUser . '@')) {
        throw new RuntimeException("Target user mismatch: expected {$expectedUser}@, got {$currentUser}");
    }
    if ($currentDb !== $expectedDb) {
        throw new RuntimeException("Target database mismatch: expected {$expectedDb}, got {$currentDb}");
    }
    if ($currentPort !== $expectedPort) {
        throw new RuntimeException("Target port mismatch: expected {$expectedPort}, got {$currentPort}");
    }
    if ($tableCount > 0) {
        throw new RuntimeException("Refusing nonempty target ({$tableCount} tables/views present); preserve existing objects for ownership review.");
    }
    if (empty($grants)) {
        throw new RuntimeException("No grants returned for current user");
    }

    $literalEscapedDb = str_replace('_', '\\_', $expectedDb);

    foreach ($grants as $grant) {
        if (!preg_match('/^GRANT\s+(.+?)\s+ON\s+(.+?)\s+TO\s+(.+?)(?:\s+WITH\s+(.+?))?$/i', trim($grant), $m)) {
            throw new RuntimeException("Malformed grant statement: {$grant}");
        }

        $privs = trim($m[1]);
        $target = trim($m[2]);
        $grantee = trim($m[3]);
        $with = isset($m[4]) ? trim($m[4]) : '';

        if (stripos($with, 'GRANT OPTION') !== false) {
            throw new RuntimeException("Forbidden grant: contains WITH GRANT OPTION");
        }

        // Global privileges check
        if (strtoupper($target) === '*.*') {
            if (strtoupper($privs) === 'USAGE') {
                continue;
            }
            throw new RuntimeException("Forbidden broader grant detected on *.*: {$grant}");
        }

        // Schema-level grant check
        if (!preg_match('/^`?([^`]+?)`?\.\*$/', $target, $sm)) {
            throw new RuntimeException("Forbidden non-schema grant target: {$target}");
        }
        $targetSchema = $sm[1];

        // Reject wildcard characters (% and ?) in schema name
        if (str_contains($targetSchema, '%') || str_contains($targetSchema, '?')) {
            throw new RuntimeException("Forbidden grant: wildcard pattern in schema name '{$targetSchema}'");
        }

        // Reject unescaped underscore in schema name (MySQL interprets unescaped _ as a single-character wildcard)
        if (preg_match('/(?<!\\\\)_/', $targetSchema)) {
            throw new RuntimeException("Forbidden grant: schema name '{$targetSchema}' contains unescaped underscore wildcard; literal escaped schema '{$literalEscapedDb}' required.");
        }

        // Strictly verify literal escaped schema matches expected
        if ($targetSchema !== $literalEscapedDb) {
            throw new RuntimeException("Forbidden grant on unauthorized database: {$targetSchema}; expected {$literalEscapedDb}");
        }

        // Grantee verification: ensure grantee username matches expected user
        // Note: '%' in grantee host is an authorized account host specifier, NOT a schema wildcard!
        $granteeUser = preg_replace('/^[`\']?([^`\'@]+)[`\']?@.*$/', '$1', $grantee);
        if ($granteeUser !== $expectedUser) {
            throw new RuntimeException("Forbidden grant for unexpected user: {$granteeUser}; expected {$expectedUser}");
        }
    }
}

// 82 Reviewed Canonical Tables Expected to be Created by Pre-Migration Schema & Canonical Migrations
const F5_CANONICAL_EXPECTED_TABLES = [
    '_test_db_marker',
    'activity_logs',
    'api_tokens',
    'appointment_contact_notes',
    'appointment_summary',
    'appointments',
    'attachment_variants',
    'clinic_notification_channels',
    'clinic_provider_profiles',
    'clinic_subscriptions',
    'clinic_users',
    'clinical_charge_lines',
    'clinical_procedure_status_history',
    'clinical_procedures',
    'clinics',
    'control_plane_audits',
    'control_plane_invoice_payments',
    'control_plane_invoices',
    'email_templates',
    'examination_note_addenda',
    'examination_note_signatures',
    'examinations',
    'file_attachments',
    'finance_checkout_requests',
    'finance_liquidity_transfers',
    'finance_payments',
    'finance_settlement_requests',
    'finances',
    'financial_summary',
    'groups',
    'inventory',
    'inventory_batches',
    'inventory_item_templates',
    'inventory_reorder_items',
    'inventory_stock_movements',
    'inventory_supplier_links',
    'inventory_usage',
    'invoice_correction_approvals',
    'invoice_correction_lines',
    'invoice_correction_sequences',
    'invoice_corrections',
    'invoice_credit_application_reversals',
    'invoice_credit_applications',
    'invoice_replacement_links',
    'job_audits',
    'login_attempts',
    'migrations',
    'notification_attempts',
    'notifications',
    'odontograms',
    'patient_examination_summary',
    'patient_odontogram_snapshots',
    'patient_whatsapp',
    'patients',
    'permission_audit_log',
    'permissions',
    'plan_audits',
    'plan_requests',
    'plan_usage',
    'plans',
    'platform_secrets',
    'prescriptions',
    'provider_payout_payments',
    'provider_payouts',
    'role_permissions',
    'roles',
    'settings',
    'studio_theme_current',
    'studio_theme_revisions',
    'suppliers',
    'treatment_contract_items',
    'treatment_contract_odontogram_snapshots',
    'treatment_contract_signatures',
    'treatment_contracts',
    'treatments',
    'user_permissions',
    'user_roles',
    'users',
    'users_groups',
    'whatsapp_delivery_events',
    'whatsapp_optouts',
    'whatsapp_tenant_policies',
];

if (defined('F5_HARNESS_UNIT_TEST')) {
    return;
}

// 1. Read credentials securely from stdin (never in argv, Git, or logs)
$rawInput = file_get_contents('php://stdin');
if (!$rawInput) {
    fwrite(STDERR, "Missing stdin credentials JSON\n");
    exit(1);
}

try {
    $creds = json_decode($rawInput, true, 512, JSON_THROW_ON_ERROR);
} catch (\Throwable $e) {
    fwrite(STDERR, "Invalid credentials JSON: " . $e->getMessage() . "\n");
    exit(1);
}

$expectedDb = 'u556956644_pr24syn1106';
$expectedUser = 'u556956644_pr24syn1106';
$expectedHost = '127.0.0.1';
$expectedPort = 3306;

if (($creds['database'] ?? '') !== $expectedDb || ($creds['user'] ?? '') !== $expectedUser || ($creds['host'] ?? '') !== $expectedHost || (int)($creds['port'] ?? 0) !== $expectedPort) {
    fwrite(STDERR, "Target mismatch: Expected strictly {$expectedUser}@{$expectedHost}:{$expectedPort}/{$expectedDb}\n");
    exit(1);
}

$password = $creds['password'] ?? '';
if ($password === '') {
    fwrite(STDERR, "Missing password\n");
    exit(1);
}

// Run synthetic controls for preflight grant verification (never mutates hosting grants or production)
$literalEscapedDb = str_replace('_', '\\_', $expectedDb);

// 1. Positive controls (must pass without exception)
$syntheticPositives = [
    'escaped_schema_with_percent_host' => [
        'grants' => [
            "GRANT USAGE ON *.* TO `{$expectedUser}`@`%`",
            "GRANT ALL PRIVILEGES ON `{$literalEscapedDb}`.* TO `{$expectedUser}`@`%`"
        ],
        'tableCount' => 0,
        'user' => "{$expectedUser}@127.0.0.1",
        'db' => $expectedDb,
        'port' => $expectedPort
    ],
    'escaped_schema_with_loopback_host' => [
        'grants' => [
            "GRANT USAGE ON *.* TO '{$expectedUser}'@'127.0.0.1'",
            "GRANT SELECT, INSERT, UPDATE, DELETE ON `{$literalEscapedDb}`.* TO '{$expectedUser}'@'127.0.0.1'"
        ],
        'tableCount' => 0,
        'user' => "{$expectedUser}@127.0.0.1",
        'db' => $expectedDb,
        'port' => $expectedPort
    ],
];
foreach ($syntheticPositives as $posKey => $pos) {
    try {
        validatePreflightSafeguards(
            $pos['grants'],
            $expectedDb,
            $pos['tableCount'],
            $pos['user'],
            $pos['db'],
            $pos['port'],
            $expectedUser,
            $expectedPort
        );
    } catch (\Throwable $e) {
        fwrite(STDERR, "Positive control failed: {$posKey} threw unexpected error: " . $e->getMessage() . "\n");
        exit(1);
    }
}

// 2. Synthetic negative controls (must fail closed before any DDL)
$syntheticNegatives = [
    'unescaped_underscore_wildcard' => ['grants' => ["GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`", "GRANT ALL PRIVILEGES ON `{$expectedDb}`.* TO `{$expectedUser}`@`127.0.0.1`"], 'tableCount' => 0, 'error' => 'unescaped underscore wildcard'],
    'percent_wildcard_schema' => ['grants' => ["GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`", "GRANT ALL PRIVILEGES ON `u556956644\_%`.* TO `{$expectedUser}`@`127.0.0.1`"], 'tableCount' => 0, 'error' => 'wildcard pattern in schema name'],
    'global_select' => ['grants' => ["GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`", "GRANT SELECT ON *.* TO `{$expectedUser}`@`127.0.0.1`"], 'tableCount' => 0, 'error' => 'Forbidden broader grant detected on *.*'],
    'other_schema' => ['grants' => ["GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`", "GRANT ALL PRIVILEGES ON `other\_db`.* TO `{$expectedUser}`@`127.0.0.1`"], 'tableCount' => 0, 'error' => 'Forbidden grant on unauthorized database'],
    'grant_option' => ['grants' => ["GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`", "GRANT ALL ON `{$literalEscapedDb}`.* TO `{$expectedUser}`@`127.0.0.1` WITH GRANT OPTION"], 'tableCount' => 0, 'error' => 'WITH GRANT OPTION'],
    'nonempty_target' => ['grants' => ["GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`", "GRANT ALL ON `{$literalEscapedDb}`.* TO `{$expectedUser}`@`127.0.0.1`"], 'tableCount' => 5, 'error' => 'Refusing nonempty target'],
    'wrong_user' => ['grants' => ["GRANT USAGE ON *.* TO `wrong_user`@`127.0.0.1`"], 'tableCount' => 0, 'user' => 'wrong_user@127.0.0.1', 'error' => 'Target user mismatch'],
    'wrong_db' => ['grants' => ["GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`"], 'tableCount' => 0, 'db' => 'wrong_db', 'error' => 'Target database mismatch'],
    'wrong_port' => ['grants' => ["GRANT USAGE ON *.* TO `{$expectedUser}`@`127.0.0.1`"], 'tableCount' => 0, 'port' => 3307, 'error' => 'Target port mismatch'],
];
foreach ($syntheticNegatives as $negKey => $neg) {
    try {
        validatePreflightSafeguards(
            $neg['grants'],
            $expectedDb,
            $neg['tableCount'],
            $neg['user'] ?? "{$expectedUser}@127.0.0.1",
            $neg['db'] ?? $expectedDb,
            $neg['port'] ?? $expectedPort,
            $expectedUser,
            $expectedPort
        );
        fwrite(STDERR, "Negative control failed: {$negKey} was accepted\n");
        exit(1);
    } catch (\RuntimeException $e) {
        if (!str_contains($e->getMessage(), $neg['error'])) {
            fwrite(STDERR, "Negative control {$negKey} gave unexpected error: " . $e->getMessage() . "\n");
            exit(1);
        }
    }
}

// 2. Strict Live Preflight Connection & Advisory Lock Acquisition via raw MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli($expectedHost, $expectedUser, $password, $expectedDb, $expectedPort);
$mysqli->set_charset('utf8mb4');

// Acquire exclusive database advisory lock FIRST to exclude concurrent runs before reading preflight state
$lockRes = $mysqli->query("SELECT GET_LOCK('denta_f5_canonical_run', 5) as lck")->fetch_assoc();
if ((int)($lockRes['lck'] ?? 0) !== 1) {
    fwrite(STDERR, "Could not acquire exclusive database advisory lock 'denta_f5_canonical_run'; concurrent run excluded.\n");
    exit(1);
}

try {
    $res = $mysqli->query("SELECT CURRENT_USER() as cu, DATABASE() as db, @@port as port, @@version as v");
    $row = $res->fetch_assoc();
    $dbUser = (string)($row['cu'] ?? '');
    $dbName = (string)($row['db'] ?? '');
    $dbPort = (int)($row['port'] ?? 0);
    $dbVersion = (string)($row['v'] ?? '');

    $grantsRes = $mysqli->query("SHOW GRANTS FOR CURRENT_USER()");
    $liveGrants = [];
    while ($gRow = $grantsRes->fetch_row()) {
        $liveGrants[] = $gRow[0];
    }

    // Read table count strictly INSIDE acquired advisory lock before any table creation
    $tblCountRes = $mysqli->query("SELECT COUNT(*) cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$expectedDb}'");
    $liveTableCount = (int)($tblCountRes->fetch_assoc()['cnt'] ?? 0);

    // Validate preflight safeguards under exclusive advisory lock
    validatePreflightSafeguards($liveGrants, $expectedDb, $liveTableCount, $dbUser, $dbName, $dbPort, $expectedUser, $expectedPort);
} catch (\Throwable $e) {
    @$mysqli->query("SELECT RELEASE_LOCK('denta_f5_canonical_run')");
    @$mysqli->close();
    fwrite(STDERR, "Preflight safeguard failure: " . $e->getMessage() . "\n");
    exit(1);
}

// Generate exclusive per-run cryptographic marker token
$runUuid = bin2hex(random_bytes(16));
$taskSignature = "native_f5_canonical_{$runUuid}";

$markerTable = '_test_db_marker';
$mysqli->query("CREATE TABLE `{$markerTable}` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `task_signature` VARCHAR(64) NOT NULL,
    `run_uuid` VARCHAR(64) NOT NULL,
    `created_at` DATETIME NOT NULL
) ENGINE=InnoDB");
$stmt = $mysqli->prepare("INSERT INTO `{$markerTable}` (`task_signature`, `run_uuid`, `created_at`) VALUES (?, ?, NOW())");
$stmt->bind_param('ss', $taskSignature, $runUuid);
$stmt->execute();
$stmt->close();
// Note: $mysqli connection remains open to hold exclusive advisory lock 'denta_f5_canonical_run' throughout run

// Exact task-created inventory tracker
$taskCreatedTables = [$markerTable];

// 3. Boot CodeIgniter Console Runtime & Confinement
$baseDir = is_dir(dirname(__DIR__, 2) . '/app') ? dirname(__DIR__, 2) : (is_dir(dirname(__DIR__) . '/app') ? dirname(__DIR__) : (is_dir(__DIR__ . '/app') ? __DIR__ : getcwd()));
$appDir = $baseDir . '/app';
$systemDir = '/home/u556956644/domains/palsync.net/public_html/denta/system';
if (!is_dir($systemDir)) {
    $systemDir = $baseDir . '/system';
}
$writableDir = $baseDir . '/writable';
if (!is_dir($writableDir)) {
    @mkdir($writableDir, 0700, true);
    @mkdir($writableDir . '/cache', 0700, true);
    @mkdir($writableDir . '/logs', 0700, true);
    @mkdir($writableDir . '/session', 0700, true);
}

// Confine both tests and default connection groups strictly to synthetic target
putenv("database.tests.hostname={$expectedHost}");
putenv("database.tests.port={$expectedPort}");
putenv("database.tests.database={$expectedDb}");
putenv("database.tests.username={$expectedUser}");
putenv("database.tests.password={$password}");
putenv("database.tests.DBDriver=MySQLi");
putenv("database.tests.DBPrefix=");

putenv("database.default.hostname={$expectedHost}");
putenv("database.default.port={$expectedPort}");
putenv("database.default.database={$expectedDb}");
putenv("database.default.username={$expectedUser}");
putenv("database.default.password={$password}");
putenv("database.default.DBDriver=MySQLi");
putenv("database.default.DBPrefix=");

$_ENV['database.tests.hostname'] = $expectedHost;
$_ENV['database.tests.port'] = $expectedPort;
$_ENV['database.tests.database'] = $expectedDb;
$_ENV['database.tests.username'] = $expectedUser;
$_ENV['database.tests.password'] = $password;
$_ENV['database.tests.DBDriver'] = 'MySQLi';
$_ENV['database.tests.DBPrefix'] = '';

$_ENV['database.default.hostname'] = $expectedHost;
$_ENV['database.default.port'] = $expectedPort;
$_ENV['database.default.database'] = $expectedDb;
$_ENV['database.default.username'] = $expectedUser;
$_ENV['database.default.password'] = $password;
$_ENV['database.default.DBDriver'] = 'MySQLi';
$_ENV['database.default.DBPrefix'] = '';

defined('FCPATH') || define('FCPATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require_once $appDir . '/Config/Paths.php';
$paths = new Config\Paths();
$paths->appDirectory = $appDir;
$paths->systemDirectory = $systemDir;
$paths->writableDirectory = $writableDir;
defined('ENVIRONMENT') || define('ENVIRONMENT', 'testing');
require_once $systemDir . '/Boot.php';
CodeIgniter\Boot::bootConsole($paths);

$db = Config\Database::connect('tests', false);
$dbDefault = Config\Database::connect('default', false);

if ($db->database !== $expectedDb || $dbDefault->database !== $expectedDb) {
    fwrite(STDERR, "Database connection confinement violation: tests={$db->database}, default={$dbDefault->database}\n");
    exit(1);
}
$db->query("SET SESSION innodb_lock_wait_timeout = 10");

// Disable external integrations
Config\Services::injectMock('email', new class { public function send() { return true; } public function __call($m, $a) { return $this; } });
Config\Services::injectMock('curlrequest', new class { public function request() { return new CodeIgniter\HTTP\Response(config('App')); } });

// Assert tracking
$executedChecks = [];
$assert = static function (bool $condition, string $description) use (&$executedChecks): void {
    if (!$condition) {
        throw new RuntimeException("Assertion failed: {$description}");
    }
    $executedChecks[] = $description;
};

function insertFixture(\CodeIgniter\Database\BaseConnection $db, string $table, array $data): int {
    if (isset($data['id'])) {
        $existing = $db->table($table)->where('id', $data['id'])->get()->getRowArray();
        if ($existing) {
            return (int) $data['id'];
        }
    }
    $builder = $db->table($table);
    if (!$builder->insert($data)) {
        throw new RuntimeException("Failed to insert fixture into {$table}");
    }
    return (int) $db->insertID();
}

// Track active child processes for deterministic cleanup
$activeWorkerProcs = [];

// Create unique private run directory with mode 0700
$runDir = $writableDir . '/f5_run_' . $runUuid;
if (DIRECTORY_SEPARATOR === '/') {
    $oldUmask = umask(0077);
}
@mkdir($runDir, 0700, true);
if (DIRECTORY_SEPARATOR === '/') {
    @chmod($runDir, 0700);
}

// Bounded worker pair executor helper with immediate registration and guaranteed cleanup
function executeWorkerPairWithBoundTimeouts(
    string $workerScript,
    array $payloadA,
    array $payloadB,
    string $runDir,
    string $runUuid,
    array &$activeWorkerProcs,
    float $readyTimeoutSeconds = 15.0,
    float $execTimeoutSeconds = 20.0
): array {
    $inputPathA = $runDir . '/in_a_' . bin2hex(random_bytes(4)) . '.json';
    $inputPathB = $runDir . '/in_b_' . bin2hex(random_bytes(4)) . '.json';
    $readyFileA = $runDir . '/ready_a_' . bin2hex(random_bytes(4));
    $readyFileB = $runDir . '/ready_b_' . bin2hex(random_bytes(4));
    $barrierFile = $runDir . '/barrier_' . bin2hex(random_bytes(4));

    $procA = null;
    $procB = null;
    $pipesA = [];
    $pipesB = [];

    $cleanupFiles = static function () use ($inputPathA, $inputPathB, $readyFileA, $readyFileB, $barrierFile): void {
        foreach ([$inputPathA, $inputPathB, $readyFileA, $readyFileB, $barrierFile] as $f) {
            if (is_file($f)) {
                @unlink($f);
            }
        }
    };

    try {
        $payloadA['run_uuid'] = $runUuid;
        $payloadA['run_dir'] = $runDir;
        $payloadA['ready_file'] = $readyFileA;
        $payloadA['barrier_file'] = $barrierFile;

        $payloadB['run_uuid'] = $runUuid;
        $payloadB['run_dir'] = $runDir;
        $payloadB['ready_file'] = $readyFileB;
        $payloadB['barrier_file'] = $barrierFile;

        file_put_contents($inputPathA, json_encode($payloadA, JSON_THROW_ON_ERROR));
        file_put_contents($inputPathB, json_encode($payloadB, JSON_THROW_ON_ERROR));
        if (DIRECTORY_SEPARATOR === '/') {
            @chmod($inputPathA, 0600);
            @chmod($inputPathB, 0600);
        }

        $cmdA = [PHP_BINARY, $workerScript, $inputPathA];
        $cmdB = [PHP_BINARY, $workerScript, $inputPathB];

        // Register each child process immediately upon launch to prevent resource leaks
        $procA = proc_open($cmdA, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipesA);
        if (is_resource($procA)) {
            $activeWorkerProcs[] = $procA;
        }
        if (!is_resource($procA)) {
            throw new RuntimeException("Failed to launch child worker process A");
        }
        fclose($pipesA[0]);

        $procB = proc_open($cmdB, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipesB);
        if (is_resource($procB)) {
            $activeWorkerProcs[] = $procB;
        }
        if (!is_resource($procB)) {
            throw new RuntimeException("Failed to launch child worker process B");
        }
        fclose($pipesB[0]);

        // Bounded wait for ready files
        $until = microtime(true) + $readyTimeoutSeconds;
        while (!is_file($readyFileA) || !is_file($readyFileB)) {
            if (microtime(true) > $until) {
                throw new RuntimeException("Workers failed to reach ready state within {$readyTimeoutSeconds}s");
            }
            usleep(10000);
        }

        // Release barrier simultaneously
        file_put_contents($barrierFile, 'release');
        if (DIRECTORY_SEPARATOR === '/') {
            @chmod($barrierFile, 0600);
        }

        // Bounded stream reads with non-blocking stream_select
        stream_set_blocking($pipesA[1], false);
        stream_set_blocking($pipesA[2], false);
        stream_set_blocking($pipesB[1], false);
        stream_set_blocking($pipesB[2], false);

        $outA = ''; $errA = '';
        $outB = ''; $errB = '';
        $deadline = microtime(true) + $execTimeoutSeconds;
        $capturedExitA = null;
        $capturedExitB = null;

        while (true) {
            if (microtime(true) > $deadline) {
                throw new RuntimeException("Worker execution timed out after {$execTimeoutSeconds}s");
            }

            if (DIRECTORY_SEPARATOR === '/') {
                $r = [$pipesA[1], $pipesA[2], $pipesB[1], $pipesB[2]];
                $w = null; $e = null;
                $n = @stream_select($r, $w, $e, 0, 50000);
                if ($n > 0) {
                    if (in_array($pipesA[1], $r, true)) { $c = fread($pipesA[1], 8192); if ($c !== false) $outA .= $c; }
                    if (in_array($pipesA[2], $r, true)) { $c = fread($pipesA[2], 8192); if ($c !== false) $errA .= $c; }
                    if (in_array($pipesB[1], $r, true)) { $c = fread($pipesB[1], 8192); if ($c !== false) $outB .= $c; }
                    if (in_array($pipesB[2], $r, true)) { $c = fread($pipesB[2], 8192); if ($c !== false) $errB .= $c; }
                }
            } else {
                usleep(50000);
                foreach ([[$pipesA[1], &$outA], [$pipesA[2], &$errA], [$pipesB[1], &$outB], [$pipesB[2], &$errB]] as [$pipe, &$buf]) {
                    $meta = stream_get_meta_data($pipe);
                    if (($meta['unread_bytes'] ?? 0) > 0) {
                        $c = fread($pipe, min(8192, $meta['unread_bytes']));
                        if ($c !== false && $c !== '') $buf .= $c;
                    }
                }
            }
            $stA = proc_get_status($procA);
            $stB = proc_get_status($procB);

            if (!$stA['running'] && $capturedExitA === null && $stA['exitcode'] !== -1) {
                $capturedExitA = (int) $stA['exitcode'];
            }
            if (!$stB['running'] && $capturedExitB === null && $stB['exitcode'] !== -1) {
                $capturedExitB = (int) $stB['exitcode'];
            }

            if (!$stA['running'] && !$stB['running']) {
                $outA .= stream_get_contents($pipesA[1]);
                $errA .= stream_get_contents($pipesA[2]);
                $outB .= stream_get_contents($pipesB[1]);
                $errB .= stream_get_contents($pipesB[2]);
                break;
            }
        }

        fclose($pipesA[1]); fclose($pipesA[2]);
        fclose($pipesB[1]); fclose($pipesB[2]);
        $closeA = proc_close($procA);
        $closeB = proc_close($procB);
        $activeWorkerProcs = array_values(array_filter($activeWorkerProcs, static fn($p) => $p !== $procA && $p !== $procB));
        $procA = null;
        $procB = null;

        $resA = json_decode($outA, true);
        $resB = json_decode($outB, true);
        if ($resA === null || $resB === null) {
            throw new RuntimeException("Worker JSON decode failed.\nA out: {$outA}, err: {$errA}\nB out: {$outB}, err: {$errB}");
        }

        return [$resA, $resB];

    } finally {
        // Guaranteed cleanup of credential files and uncooperative worker termination
        $cleanupFiles();

        // Close pipes FIRST so proc_close does not block on child pipe EOF
        foreach ([$pipesA, $pipesB] as $pipes) {
            foreach ($pipes as $pipe) {
                if (is_resource($pipe)) {
                    @fclose($pipe);
                }
            }
        }

        foreach ([$procA, $procB] as $p) {
            if ($p !== null && is_resource($p)) {
                $st = proc_get_status($p);
                if ($st['running']) {
                    @proc_terminate($p);
                }
                @proc_close($p);
                $activeWorkerProcs = array_values(array_filter($activeWorkerProcs, static fn($item) => $item !== $p));
            }
        }
    }
}

try {
    // 4. Apply Canonical Schema & Settlement Request Migration
    $legacySchemaPath = $baseDir . '/tests/_support/Database/LegacyBaseSchema.php';
    if (!is_file($legacySchemaPath)) {
        $legacySchemaPath = dirname(__DIR__, 2) . '/tests/_support/Database/LegacyBaseSchema.php';
    }
    require_once $legacySchemaPath;
    Tests\Support\Database\LegacyBaseSchema::apply($db);

    $migrate = Config\Services::migrations(null, $db, false);
    $migrate->ensureTable();
    $migrationResult = $migrate->latest();
    if ($migrationResult === false) {
        throw new RuntimeException("MigrationRunner failed");
    }

    if (!$db->tableExists('finance_settlement_requests')) {
        require_once $appDir . '/Database/Migrations/2026-10-29-180000_CreateFinanceSettlementRequests.php';
        $forge = Config\Database::forge($db);
        $settlementMigration = new App\Database\Migrations\CreateFinanceSettlementRequests($forge);
        $settlementMigration->up();
    }

    // Verify all created tables against reviewed canonical inventory
    $rawTables = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->getResultArray();
    $discoveredTables = array_map(static fn($r) => array_values($r)[0], $rawTables);

    // Ensure no unexpected unknown tables exist
    $unexpectedTables = array_diff($discoveredTables, F5_CANONICAL_EXPECTED_TABLES);
    if (!empty($unexpectedTables)) {
        throw new RuntimeException("Unexpected table drift detected during setup: " . implode(', ', $unexpectedTables) . "; preserving unknown objects and original evidence.");
    }

    // Constrain task-created tables inventory strictly to reviewed canonical tables
    $taskCreatedTables = array_values(array_intersect(F5_CANONICAL_EXPECTED_TABLES, $discoveredTables));

    // 5. Direct Fixture Seeding: Seed Baseline Context Rows
    // Explicit disclosure: Clinics, users, roles, patient, invoice row, and provider payout row
    // are seeded directly via database fixtures to establish test state before exercising mutators.
    $clinic = insertFixture($db, 'clinics', [
        'id' => 1,
        'name' => 'Primary Synthetic Clinic',
        'slug' => 'primary-synthetic-clinic',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    $foreignClinic = insertFixture($db, 'clinics', [
        'id' => 2,
        'name' => 'Foreign Synthetic Clinic',
        'slug' => 'foreign-synthetic-clinic',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    if ($db->tableExists('plans') && !$db->table('plans')->where('id', 1)->get()->getRowArray()) {
        insertFixture($db, 'plans', [
            'id' => 1,
            'name' => 'Standard Plan',
            'status' => 'active',
            'limits_json' => '{}',
            'features_json' => '{}',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    insertFixture($db, 'clinic_subscriptions', [
        'clinic_id' => 1,
        'plan_id' => 1,
        'status' => 'active',
        'start_at' => '2026-01-01 00:00:00',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    $actor = insertFixture($db, 'users', [
        'id' => 1,
        'username' => 'synthetic_admin',
        'email' => 'admin@synthetic.test',
        'password' => password_hash('Pass123!', PASSWORD_BCRYPT),
        'active' => 1,
        'first_name' => 'Native',
        'last_name' => 'Synthetic',
        'created_at' => time(),
        'updated_at' => time()
    ]);

    if (!$db->table('roles')->where('id', 1)->get()->getRowArray()) {
        insertFixture($db, 'roles', [
            'id' => 1,
            'name' => 'admin',
            'slug' => 'admin',
            'description' => 'Administrator',
            'is_active' => 1,
            'is_system' => 1,
            'created_by' => 1
        ]);
    }

    insertFixture($db, 'users_groups', ['user_id' => 1, 'group_id' => 1]);
    insertFixture($db, 'user_roles', ['user_id' => 1, 'role_id' => 1]);
    insertFixture($db, 'clinic_users', ['clinic_id' => 1, 'user_id' => 1, 'role_id' => 1, 'status' => 'active']);
    $roleId = 1;

    foreach (['create', 'edit', 'delete', 'view', 'reports'] as $permAction) {
        $p = $db->table('permissions')->where('module', 'finance')->where('action', $permAction)->get()->getRowArray();
        if ($p) {
            $permId = (int)$p['id'];
        } else {
            $permId = insertFixture($db, 'permissions', [
                'name' => "finance_{$permAction}",
                'module' => 'finance',
                'action' => $permAction,
                'description' => "Finance {$permAction}"
            ]);
        }
        if (!$db->table('role_permissions')->where('role_id', 1)->where('permission_id', $permId)->get()->getRowArray()) {
            insertFixture($db, 'role_permissions', [
                'role_id' => 1,
                'permission_id' => $permId,
                'granted' => 1
            ]);
        }
    }

    $patientId = insertFixture($db, 'patients', [
        'id' => 1,
        'clinic_id' => $clinic,
        'first_name' => 'Synthetic',
        'last_name' => 'Patient',
        'gender' => 'other',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    // =========================================================================
    // SECTION A: Canonical Writers, Multi-Currency, Receipts & Provider Preservation
    // =========================================================================

    $paymentModel = new App\Models\FinancePaymentModel($db);
    $settlementService = new App\Services\FinanceSettlementService($db);
    $guard = new App\Services\FinanceSettlementRequestService($db);

    // 1. Seed direct invoice fixture (200.00 USD @ 3.7500 FX = 750.00 ILS gross base)
    $invoiceData = [
        'clinic_id' => $clinic,
        'patient_id' => $patientId,
        'transaction_id' => 'INV-USD-20261106-001',
        'transaction_type' => 'invoice',
        'currency' => 'USD',
        'exchange_rate' => 3.7500,
        'amount_original' => '200.00',
        'amount' => '750.00',
        'discount_amount' => '0.00',
        'tax_amount' => '0.00',
        'total_amount' => '750.00',
        'balance_due' => '750.00',
        'amount_paid' => '0.00',
        'payment_method' => 'card',
        'liquidity_account' => 'bank',
        'payment_status' => 'pending',
        'service_type' => 'treatment',
        'description' => 'Synthetic USD dental implant package',
        'transaction_date' => '2026-11-06',
        'created_by' => $actor,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $invoiceId = insertFixture($db, 'finances', $invoiceData);

    // 2. Seed Provider Share Payout fixture (30% share = 225.00 ILS base obligation)
    $payoutData = [
        'clinic_id' => $clinic,
        'finance_id' => $invoiceId,
        'patient_id' => $patientId,
        'provider_user_id' => $actor,
        'provider_type' => 'user',
        'provider_name_snapshot' => 'Dr. Synthetic Provider',
        'split_mode' => 'percent',
        'split_value' => 30.0,
        'gross_amount' => '750.00',
        'provider_share_amount' => '225.00',
        'clinic_share_amount' => '525.00',
        'provider_paid_amount' => '0.00',
        'provider_balance_due' => '225.00',
        'status' => 'unpaid',
        'currency' => 'ILS',
        'created_by' => $actor,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $payoutId = insertFixture($db, 'provider_payouts', $payoutData);

    // 3. Issue settlement wire context
    $wireContext = $guard->issue($clinic, $actor, 'payment', [$invoiceId]);
    $assert($wireContext['currency'] === 'ILS', 'A1: Settlement context calculates facts in clinic base currency ILS');
    $assert((float)$wireContext['facts']['total'] === 750.00, 'A2: Settlement context facts report base currency total 750.00');

    // 4. Negative FX unit tests: verify invalid exchange rates and currencies fail closed before write
    $stateBeforeNegativeFx = [
        $db->table('finances')->where('id', $invoiceId)->get()->getRowArray(),
        $db->table('finance_payments')->where('finance_id', $invoiceId)->get()->getResultArray()
    ];

    try {
        $settlementService->bulkSettle($clinic, [$invoiceId], $actor, [
            'amount_original' => 100.00,
            'currency' => 'USD',
            'exchange_rate' => -3.75, // Negative rate
            'payment_method' => 'card'
        ], $wireContext, 'payment');
        throw new LogicException('Negative exchange rate accepted');
    } catch (\DomainException $e) {
        $assert(str_contains($e->getMessage(), 'Exchange rate must be greater than zero'), 'A3: Negative exchange rate rejected with DomainException');
    }

    try {
        $settlementService->bulkSettle($clinic, [$invoiceId], $actor, [
            'amount_original' => 100.00,
            'currency' => 'INVALID_CURRENCY',
            'exchange_rate' => 3.75,
            'payment_method' => 'card'
        ], $wireContext, 'payment');
        throw new LogicException('Invalid currency accepted');
    } catch (\DomainException $e) {
        $assert(str_contains($e->getMessage(), 'Invalid settlement currency'), 'A4: Invalid currency rejected with DomainException');
    }

    $stateAfterNegativeFx = [
        $db->table('finances')->where('id', $invoiceId)->get()->getRowArray(),
        $db->table('finance_payments')->where('finance_id', $invoiceId)->get()->getResultArray()
    ];
    $assert($stateBeforeNegativeFx === $stateAfterNegativeFx, 'A5: Negative FX tests leave database rows completely unmodified');

    // Setup session and Finance controller for Direct Controller Invocations
    $session = \Config\Services::session();
    $session->set([
        'active_clinic_id' => $clinic,
        'user_id' => $actor,
        'identity' => 'synthetic_admin',
        'email' => 'admin@synthetic.test',
        'logged_in' => true
    ]);

    $controller = new App\Controllers\Finance();
    (new ReflectionProperty(App\Controllers\Finance::class, 'db'))->setValue($controller, $db);
    (new ReflectionProperty(App\Controllers\Finance::class, 'financePaymentModel'))->setValue($controller, new App\Models\FinancePaymentModel($db));
    (new ReflectionProperty(App\Controllers\Finance::class, 'financeModel'))->setValue($controller, new App\Models\FinanceModel($db));
    (new ReflectionProperty(App\Controllers\Finance::class, 'financeSettlementService'))->setValue($controller, new App\Services\FinanceSettlementService($db, new App\Services\FinanceTreasuryService()));
    (new ReflectionProperty(App\Controllers\Finance::class, 'financeMutationService'))->setValue($controller, new App\Services\FinanceMutationService($db, new App\Services\FinanceTreasuryService()));
    (new ReflectionProperty(App\Controllers\Finance::class, 'financeTreasuryService'))->setValue($controller, new App\Services\FinanceTreasuryService());
    (new ReflectionProperty(App\Controllers\Finance::class, 'activityLogger'))->setValue($controller, new App\Services\ActivityLogger());
    (new ReflectionProperty(App\Controllers\Finance::class, 'financeLiquidityTransferModel'))->setValue($controller, new App\Models\FinanceLiquidityTransferModel($db));

    $appConfig = config('App');
    $invokeFinanceAction = static function (App\Controllers\Finance $ctrl, string $method, array $params, array $postData, string $path) use ($appConfig): \CodeIgniter\HTTP\ResponseInterface {
        $uri = new CodeIgniter\HTTP\SiteURI($appConfig, $path);
        $req = new CodeIgniter\HTTP\IncomingRequest($appConfig, $uri, null, new CodeIgniter\HTTP\UserAgent());
        $req = $req->withMethod('POST');
        $req->setGlobal('post', $postData);
        $req->setGlobal('request', $postData);
        $_POST = $postData;
        $_REQUEST = $postData;
        \Config\Services::injectMock('request', $req);
        service('validation')->reset();
        $resp = new CodeIgniter\HTTP\Response($appConfig);
        $ctrl->initController($req, $resp, service('logger'));
        return $ctrl->{$method}(...$params);
    };

    // 5. Direct Controller Invocation: Settle partial card payment (100.00 USD @ 3.7500 FX = 375.00 ILS base) via Finance::addInvoicePayment
    $payWire = $guard->issue($clinic, $actor, 'payment', [$invoiceId]);
    $payPost = [
        'amount_original' => '100.00',
        'currency' => 'USD',
        'exchange_rate' => '3.7500',
        'payment_method' => 'card',
        'liquidity_account' => 'bank',
        'payment_date' => '2026-11-06',
        'notes' => 'Synthetic partial payment 100 USD',
        'request_key' => $payWire['request_key'],
        'settlement_revision' => $payWire['settlement_revision'],
    ];

    $payResp = $invokeFinanceAction($controller, 'addInvoicePayment', [$invoiceId], $payPost, "finance/{$invoiceId}/payments");
    $payResult = json_decode((string) $payResp->getBody(), true);
    $assert(!empty($payResult['success']) && empty($payResult['request_replayed']), 'A6: addInvoicePayment recorded canonical partial payment');

    $invoiceAfterPay = $db->table('finances')->where('id', $invoiceId)->get()->getRowArray();
    $assert((float)$invoiceAfterPay['amount_paid'] === 375.00 && (float)$invoiceAfterPay['balance_due'] === 375.00 && $invoiceAfterPay['payment_status'] === 'partial',
        'A7: Invoice balance updated to 375.00 paid with status partial');

    $netCashAfterPay = (float) $paymentModel->getNetCashPaidTotal($clinic, $invoiceId);
    $assert($netCashAfterPay === 375.00, 'A8: Net collected cash calculated truthfully as 375.00 base');

    // Test Replay of identical payment request to addInvoicePayment
    $payReplayResp = $invokeFinanceAction($controller, 'addInvoicePayment', [$invoiceId], $payPost, "finance/{$invoiceId}/payments");
    $payReplayResult = json_decode((string) $payReplayResp->getBody(), true);
    $assert(!empty($payReplayResult['success']) && !empty($payReplayResult['request_replayed']), 'A8b: Payment replay detected with request_replayed=true');
    $payCount = $db->table('finance_payments')->where('finance_id', $invoiceId)->where('type', 'payment')->countAllResults();
    $assert($payCount === 1, 'A8c: Payment replay preserves single durable payment row');

    // Capture original payment receipt row verbatim for immutability verification
    $originalReceiptRowBeforeRefund = $db->table('finance_payments')->where('finance_id', $invoiceId)->where('type', 'payment')->get()->getRowArray();
    $assert($originalReceiptRowBeforeRefund !== null, 'A9: Durable payment receipt row exists');
    $paymentReceiptId = (int) $originalReceiptRowBeforeRefund['id'];

    // 6. Direct Controller Invocation: Settle partial refund (50.00 USD = 187.50 ILS base) via Finance::refundInvoicePayment
    $refundWire = $guard->issue($clinic, $actor, 'refund', [$invoiceId]);
    $refundPost = [
        'amount_original' => '50.00',
        'currency' => 'USD',
        'exchange_rate' => '3.7500',
        'payment_method' => 'card',
        'liquidity_account' => 'bank',
        'payment_date' => '2026-11-06',
        'notes' => 'Synthetic partial refund 50 USD',
        'related_payment_id' => (string) $paymentReceiptId,
        'request_key' => $refundWire['request_key'],
        'settlement_revision' => $refundWire['settlement_revision'],
    ];

    $refundResp = $invokeFinanceAction($controller, 'refundInvoicePayment', [$invoiceId], $refundPost, "finance/{$invoiceId}/refund");
    $refundResult = json_decode((string) $refundResp->getBody(), true);
    $assert(!empty($refundResult['success']) && empty($refundResult['request_replayed']), 'A10a: refundInvoicePayment recorded canonical partial refund');

    $netCashAfterRefund = (float) $paymentModel->getNetCashPaidTotal($clinic, $invoiceId);
    $assert($netCashAfterRefund === 187.50, 'A10: Net cash paid after partial refund is 187.50 base');

    $invoiceAfterRefund = $db->table('finances')->where('id', $invoiceId)->get()->getRowArray();
    $assert((float)$invoiceAfterRefund['amount_paid'] === 187.50 && (float)$invoiceAfterRefund['balance_due'] === 562.50 && $invoiceAfterRefund['payment_status'] === 'partial',
        'A10b: Invoice balances synced automatically by controller: 187.50 paid, 562.50 due, partial status');

    // 7. Verify Original Receipt Immutability: original payment receipt must remain byte-for-byte / field-for-field unchanged
    $originalReceiptRowAfterRefund = $db->table('finance_payments')->where('id', $paymentReceiptId)->get()->getRowArray();
    $assert($originalReceiptRowBeforeRefund === $originalReceiptRowAfterRefund, 'A11: Original payment receipt remains 100% immutable after refund');

    // 8. Negative Over-refund test: attempt to refund 100.00 USD (375.00 ILS) when only 187.50 ILS refundable capacity remains
    $overRefundWire = $guard->issue($clinic, $actor, 'refund', [$invoiceId]);
    $overRefundPost = [
        'amount_original' => '100.00',
        'currency' => 'USD',
        'exchange_rate' => '3.7500',
        'payment_method' => 'card',
        'liquidity_account' => 'bank',
        'payment_date' => '2026-11-06',
        'notes' => 'Synthetic over-refund attempt',
        'related_payment_id' => (string) $paymentReceiptId,
        'request_key' => $overRefundWire['request_key'],
        'settlement_revision' => $overRefundWire['settlement_revision'],
    ];
    $refundsBeforeOverRefund = $db->table('finance_payments')->where('finance_id', $invoiceId)->where('type', 'refund')->countAllResults();
    $overRefundResp = $invokeFinanceAction($controller, 'refundInvoicePayment', [$invoiceId], $overRefundPost, "finance/{$invoiceId}/refund");
    $overRefundResult = json_decode((string) $overRefundResp->getBody(), true);
    $refundsAfterOverRefund = $db->table('finance_payments')->where('finance_id', $invoiceId)->where('type', 'refund')->countAllResults();
    $assert(empty($overRefundResult['success']) && $refundsBeforeOverRefund === $refundsAfterOverRefund, 'A12: Over-refund of 375.00 ILS blocked against 187.50 remaining refundable capacity without modifying database');

    // 9. Exact Refund Replay test: replaying identical request returns durable result without duplicate row
    $replayResp = $invokeFinanceAction($controller, 'refundInvoicePayment', [$invoiceId], $refundPost, "finance/{$invoiceId}/refund");
    $replayResult = json_decode((string) $replayResp->getBody(), true);
    $assert(!empty($replayResult['success']) && !empty($replayResult['request_replayed']), 'A13: Exact refund replay returns original durable result with request_replayed=true');
    $refundCount = $db->table('finance_payments')->where('finance_id', $invoiceId)->where('type', 'refund')->countAllResults();
    $assert($refundCount === 1, 'A14: Exact replay preserves single refund row without duplicate');

    // 10. Negative Conflict & Stale tests: altered terms throw SETTLEMENT_IDENTITY_CONFLICT; stale revision throws SETTLEMENT_STALE
    $db->transBegin();
    try {
        $guard->claim($clinic, $actor, 'refund', [$invoiceId], $refundWire, array_merge($refundPost, ['amount_original' => '75.00']));
        throw new LogicException('Conflict accepted');
    } catch (App\Services\FinanceSettlementConflict $e) {
        $assert($e->reason === 'SETTLEMENT_IDENTITY_CONFLICT', 'A15: Changed refund request with same key rejected with SETTLEMENT_IDENTITY_CONFLICT');
    } finally {
        $db->transRollback();
    }

    // 11. Provider Preservation: verify provider payout obligation remains intact at 225.00 ILS unpaid (zero clawback)
    $payoutRow = $db->table('provider_payouts')->where('id', $payoutId)->get()->getRowArray();
    $assert((float)$payoutRow['provider_share_amount'] === 225.00, 'A16: Provider obligation preserved at 225.00 ILS despite invoice partial refund');
    $assert((float)$payoutRow['provider_balance_due'] === 225.00 && $payoutRow['status'] === 'unpaid', 'A17: Provider balance remains 225.00 unpaid with zero invented clawback');

    // 12. Service Invocation: Record partial provider payout payment (100.00 ILS) via FinanceMutationService::recordPayout
    $mutation = new App\Services\FinanceMutationService($db);
    $providerWire = $guard->issue($clinic, $actor, 'provider_payment', [$payoutId]);
    $mutation->recordPayout($clinic, $payoutId, $actor, 100.00, 'cash', 'safe_cash', '2026-11-06', 'Synthetic provider payout', $providerWire);

    $payoutAfterPay = $db->table('provider_payouts')->where('id', $payoutId)->get()->getRowArray();
    $assert((float)$payoutAfterPay['provider_balance_due'] === 125.00 && $payoutAfterPay['status'] === 'partial', 'A18: Provider payout updated to partial with 125.00 due');
    $providerReceiptCount = $db->table('provider_payout_payments')->where('provider_payout_id', $payoutId)->countAllResults();
    $assert($providerReceiptCount === 1, 'A19: Provider payout receipt recorded in provider_payout_payments');

    // 13. Report projection check: buildFinancialLedger counts each event once and segregates foreign clinics
    $ledger = (new ReflectionMethod(App\Controllers\Finance::class, 'buildFinancialLedger'))->invoke($controller, $clinic, '2026-11-01', '2026-11-30');

    $invoicePaymentsInLedger = array_values(array_filter($ledger['rows'], static fn($r) => (int)($r['finance_id'] ?? 0) === $invoiceId && ($r['event_source'] ?? '') === 'invoice_settlement'));
    $assert(count($invoicePaymentsInLedger) === 2, 'A20: Ledger projection contains exactly 2 rows for invoice (1 payment, 1 refund)');
    $types = array_column($invoicePaymentsInLedger, 'event_type');
    $assert(in_array('payment', $types, true) && in_array('refund', $types, true), 'A21: Ledger projection distinguishes payment from refund');

    $crossTenantLedger = (new ReflectionMethod(App\Controllers\Finance::class, 'buildFinancialLedger'))->invoke($controller, $foreignClinic, '2026-11-01', '2026-11-30');
    $assert(count($crossTenantLedger['rows']) === 0, 'A22: Cross-tenant ledger query returns 0 rows for foreign clinic');

    // =========================================================================
    // SECTION B: Treasury Writer, Duplicate Submissions & Balance Limitations
    // =========================================================================

    $treasuryService = new App\Services\FinanceTreasuryService();

    // 1. Initial liquidity balances projection
    $treasuryRows = [
        ['event_source' => 'direct_transaction', 'liquidity_account' => 'safe_cash', 'signed_amount_base' => 1000.00],
        ['event_source' => 'direct_transaction', 'liquidity_account' => 'bank', 'signed_amount_base' => 500.00],
    ];
    $initialClosing = $treasuryService->summarizeClosingBalances($treasuryRows);
    $assert($initialClosing['total_liquid_funds'] === 1500.00, 'B1: Initial liquid funds equal 1500.00 base');

    // 2. Direct Controller Invocation: Finance::storeTransfer
    $invokeTransfer = static function (array $postData) use ($appConfig, $db): \CodeIgniter\HTTP\ResponseInterface {
        $uri = new CodeIgniter\HTTP\SiteURI($appConfig, 'finance/transfers/store');
        $request = new CodeIgniter\HTTP\IncomingRequest($appConfig, $uri, null, new CodeIgniter\HTTP\UserAgent());
        $request = $request->withMethod('POST');
        $request->setGlobal('post', $postData);
        $request->setGlobal('request', $postData);
        $_POST = $postData;
        $_REQUEST = $postData;
        Config\Services::injectMock('request', $request);
        service('validation')->reset();

        $c = new App\Controllers\Finance();
        (new ReflectionProperty(App\Controllers\Finance::class, 'db'))->setValue($c, $db);
        (new ReflectionProperty(App\Controllers\Finance::class, 'financeLiquidityTransferModel'))->setValue($c, new App\Models\FinanceLiquidityTransferModel($db));
        $resp = new CodeIgniter\HTTP\Response($appConfig);
        $c->initController($request, $resp, service('logger'));
        return $c->storeTransfer();
    };

    // Test B2: PermissionFilter Unit Check
    $permFilter = new App\Filters\PermissionFilter();
    $unauthUri = new CodeIgniter\HTTP\SiteURI($appConfig, 'finance/transfers/store');
    $unauthRequest = new CodeIgniter\HTTP\IncomingRequest($appConfig, $unauthUri, null, new CodeIgniter\HTTP\UserAgent());
    $unauthRequest->setHeader('Accept', 'application/json');

    // Temporarily revoke finance create permission for actor to test filter guard
    $db->table('role_permissions')->where('role_id', $roleId)->update(['granted' => 0]);
    $filterRes = $permFilter->before($unauthRequest, ['finance', 'create']);
    $assert($filterRes instanceof CodeIgniter\HTTP\ResponseInterface && $filterRes->getStatusCode() === 403, 'B2: PermissionFilter unit check rejects unauthorized transfer attempt with 403');
    // Restore permission
    $db->table('role_permissions')->where('role_id', $roleId)->update(['granted' => 1]);

    // Test B3: Validation rejection when from_account === to_account
    $sameAccountPayload = [
        'from_account' => 'safe_cash',
        'to_account' => 'safe_cash',
        'amount' => '400.00',
        'transfer_date' => '2026-11-06',
        'transfer_type' => 'internal_transfer',
        'notes' => 'Synthetic invalid transfer'
    ];
    $transfersCountBefore = $db->table('finance_liquidity_transfers')->where('clinic_id', $clinic)->countAllResults();
    $resSame = $invokeTransfer($sameAccountPayload);
    $transfersCountAfterSame = $db->table('finance_liquidity_transfers')->where('clinic_id', $clinic)->countAllResults();
    $assert($transfersCountBefore === $transfersCountAfterSame, 'B3: Invalid transfer with from_account === to_account rejected without inserting row');

    // Test B4: First deliberate transfer write (400.00 safe_cash -> bank)
    $validTransferPayload = [
        'from_account' => 'safe_cash',
        'to_account' => 'bank',
        'amount' => '400.00',
        'transfer_date' => '2026-11-06',
        'transfer_type' => 'cash_deposit_to_bank',
        'notes' => 'Synthetic cash deposit to bank'
    ];
    $resValid1 = $invokeTransfer($validTransferPayload);
    $transfersCountValid1 = $db->table('finance_liquidity_transfers')->where('clinic_id', $clinic)->countAllResults();
    $assert($transfersCountValid1 === $transfersCountBefore + 1, 'B4: Valid transfer successfully recorded by Finance::storeTransfer');

    // Test B5: Duplicate Submission Replay Demonstration
    // Context: In browser UI, studio-access.js attaches a single-submission lock (submissions.has(form)) to suppress rapid double-clicks.
    // However, on network retry or form reload, storeTransfer lacks server-side request key / idempotency tokens:
    // Sending the exact same payload a second time inserts a second row!
    $resValid2 = $invokeTransfer($validTransferPayload);
    $transfersCountValid2 = $db->table('finance_liquidity_transfers')->where('clinic_id', $clinic)->countAllResults();
    $assert($transfersCountValid2 === $transfersCountValid1 + 1, 'B5: Replay gap confirmed: duplicate identical storeTransfer submission creates duplicate row (no request key idempotency)');

    // Test B6: Insufficient Funds / Negative Balance Policy Demonstration
    // Context: Current application design has no policy or constraint prohibiting negative book balances on liquidity accounts.
    // Transfers record factual liquidity movements. Transferring 10,000.00 from safe_cash succeeds even though safe_cash has only 1,000.00 balance.
    $overdraftPayload = [
        'from_account' => 'safe_cash',
        'to_account' => 'bank',
        'amount' => '10000.00',
        'transfer_date' => '2026-11-06',
        'transfer_type' => 'cash_deposit_to_bank',
        'notes' => 'Synthetic overdraft transfer'
    ];
    $resOverdraft = $invokeTransfer($overdraftPayload);
    $transfersCountOverdraft = $db->table('finance_liquidity_transfers')->where('clinic_id', $clinic)->countAllResults();
    $assert($transfersCountOverdraft === $transfersCountValid2 + 1, 'B6: Balance gap confirmed: storeTransfer allows transfers exceeding available funds without balance check');

    // Test B7: Cross-tenant isolation on liquidity transfers
    $foreignTransfers = $db->table('finance_liquidity_transfers')->where('clinic_id', $foreignClinic)->get()->getResultArray();
    $assert(count($foreignTransfers) === 0, 'B7: Foreign clinic has 0 access to clinic liquidity transfers');

    // Test B8: Cash reporting neutrality via FinanceTreasuryService projection
    $treasuryRowsWithTransfer = array_merge($treasuryRows, [
        ['event_source' => 'liquidity_transfer', 'liquidity_account' => 'safe_cash', 'signed_amount_base' => -400.00],
        ['event_source' => 'liquidity_transfer', 'liquidity_account' => 'bank', 'signed_amount_base' => 400.00],
    ]);
    $movementWithTransfer = $treasuryService->summarizeMovement($treasuryRowsWithTransfer);
    $assert($movementWithTransfer['totals']['in'] === 1500.00, 'B8: External in remains 1500.00 despite internal transfer');
    $assert($movementWithTransfer['totals']['out'] === 0.00, 'B9: External out remains 0.00 despite internal transfer');
    $assert($movementWithTransfer['totals']['net'] === 1500.00, 'B10: Net external profit remains 1500.00 with 0 drift (neutrality preserved)');

    // =========================================================================
    // SECTION C: True Multi-Connection Concurrency Races
    // =========================================================================

    $workerScript = $baseDir . '/tests/_support/finance_concurrency_race_worker.php';
    if (!is_file($workerScript)) {
        $workerScript = __DIR__ . '/../_support/finance_concurrency_race_worker.php';
    }
    if (!is_file($workerScript)) {
        throw new RuntimeException("Worker script not found: {$workerScript}");
    }

    // Case C0: Forced Worker Timeout Negative Test
    // Proves master bounded wait terminates uncooperative worker, reaps process cleanly,
    // leaves 0 lingering processes, and database rows completely untouched.
    $timeoutInput = $runDir . '/timeout_' . bin2hex(random_bytes(4)) . '.json';
    $timeoutReady = $runDir . '/ready_to_' . bin2hex(random_bytes(4));
    $timeoutBarrier = $runDir . '/nonexistent_barrier_' . bin2hex(random_bytes(4));

    $timeoutPayload = [
        'run_uuid' => $runUuid,
        'run_dir' => $runDir,
        'app_dir' => $appDir,
        'system_dir' => $systemDir,
        'writable_dir' => $writableDir,
        'creds' => [
            'host' => $expectedHost,
            'port' => $expectedPort,
            'database' => $expectedDb,
            'user' => $expectedUser,
            'password' => $password
        ],
        'clinic_id' => $clinic,
        'actor_id' => $actor,
        'targets' => [999999],
        'wire_context' => [],
        'terms' => [],
        'action' => 'mark_paid',
        'ready_file' => $timeoutReady,
        'barrier_file' => $timeoutBarrier // Never created/released
    ];
    file_put_contents($timeoutInput, json_encode($timeoutPayload, JSON_THROW_ON_ERROR));
    if (DIRECTORY_SEPARATOR === '/') {
        @chmod($timeoutInput, 0600);
    }

    $toProc = null;
    $toPipes = [];
    try {
        $toProc = proc_open([PHP_BINARY, $workerScript, $timeoutInput], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $toPipes);
        if (is_resource($toProc)) {
            $activeWorkerProcs[] = $toProc;
        }
        if (!is_resource($toProc)) {
            throw new RuntimeException("Failed to launch timeout test worker");
        }
        fclose($toPipes[0]);

        $toUntil = microtime(true) + 5.0;
        while (!is_file($timeoutReady) && microtime(true) < $toUntil) {
            usleep(10000);
        }
        $assert(is_file($timeoutReady), 'C0a: Timeout test worker signalled ready state');

        // Worker will hit internal 10.0s barrier wait timeout and exit code 1
        // Bounded non-blocking read with stream_select (deadline 15s)
        stream_set_blocking($toPipes[1], false);
        stream_set_blocking($toPipes[2], false);
        $toOut = '';
        $toErr = '';
        $toDeadline = microtime(true) + 15.0;
        $toCapturedExit = null;

        while (true) {
            if (DIRECTORY_SEPARATOR === '/') {
                $r = [$toPipes[1], $toPipes[2]];
                $w = null;
                $e = null;
                $n = @stream_select($r, $w, $e, 0, 50000);
                if ($n > 0) {
                    if (in_array($toPipes[1], $r, true)) {
                        $c = fread($toPipes[1], 8192);
                        if ($c !== false && $c !== '') $toOut .= $c;
                    }
                    if (in_array($toPipes[2], $r, true)) {
                        $c = fread($toPipes[2], 8192);
                        if ($c !== false && $c !== '') $toErr .= $c;
                    }
                }
            } else {
                usleep(50000);
                $c = fread($toPipes[1], 8192);
                if ($c !== false && $c !== '') $toOut .= $c;
                $c = fread($toPipes[2], 8192);
                if ($c !== false && $c !== '') $toErr .= $c;
            }
            $toStatus = proc_get_status($toProc);
            if (!$toStatus['running']) {
                if ($toCapturedExit === null && $toStatus['exitcode'] !== -1) {
                    $toCapturedExit = (int)$toStatus['exitcode'];
                }
                $toOut .= stream_get_contents($toPipes[1]);
                $toErr .= stream_get_contents($toPipes[2]);
                break;
            }
            if (microtime(true) > $toDeadline) {
                @proc_terminate($toProc);
                throw new RuntimeException("Timeout worker failed to exit within 15s");
            }
        }

        fclose($toPipes[1]);
        fclose($toPipes[2]);
        $toCloseCode = proc_close($toProc);
        $toExit = ($toCapturedExit !== null) ? $toCapturedExit : $toCloseCode;
        $activeWorkerProcs = array_values(array_filter($activeWorkerProcs, static fn($p) => $p !== $toProc));
        $toProc = null;

        $toRes = json_decode($toOut, true);
        $assert($toExit === 1 && ($toRes['error'] ?? '') === 'Worker barrier timeout waiting for release',
            'C0b: Forced barrier timeout handled cleanly: worker exited with code 1 and timeout error without lingering processes');
    } finally {
        if ($toProc !== null && is_resource($toProc)) {
            $st = proc_get_status($toProc);
            if ($st['running']) {
                @proc_terminate($toProc);
            }
            @proc_close($toProc);
            $activeWorkerProcs = array_values(array_filter($activeWorkerProcs, static fn($p) => $p !== $toProc));
        }
        foreach ($toPipes as $p) {
            if (is_resource($p)) {
                @fclose($p);
            }
        }
        if (is_file($timeoutInput)) @unlink($timeoutInput);
        if (is_file($timeoutReady)) @unlink($timeoutReady);
    }

    // Case C1: Exact Simultaneous Replay Race
    $raceExpense1 = [
        'clinic_id' => $clinic,
        'transaction_id' => 'RACE-1-' . bin2hex(random_bytes(4)),
        'transaction_type' => 'expense',
        'amount' => '100.00',
        'amount_original' => '100.00',
        'total_amount' => '100.00',
        'balance_due' => '100.00',
        'amount_paid' => '0.00',
        'currency' => 'ILS',
        'exchange_rate' => 1,
        'payment_method' => 'cash',
        'liquidity_account' => 'safe_cash',
        'payment_status' => 'pending',
        'expense_category' => 'salaries',
        'counterparty_name' => 'Synthetic Staff Member 1',
        'transaction_date' => '2026-11-06'
    ];
    $raceId1 = insertFixture($db, 'finances', $raceExpense1);
    $wireRace1 = $guard->issue($clinic, $actor, 'mark_paid', [$raceId1]);

    $workerPayloadA = [
        'app_dir' => $appDir,
        'system_dir' => $systemDir,
        'writable_dir' => $writableDir,
        'creds' => [
            'host' => $expectedHost,
            'port' => $expectedPort,
            'database' => $expectedDb,
            'user' => $expectedUser,
            'password' => $password
        ],
        'clinic_id' => $clinic,
        'actor_id' => $actor,
        'targets' => [$raceId1],
        'wire_context' => $wireRace1,
        'terms' => [],
        'action' => 'mark_paid'
    ];
    $workerPayloadB = $workerPayloadA;

    [$resA, $resB] = executeWorkerPairWithBoundTimeouts($workerScript, $workerPayloadA, $workerPayloadB, $runDir, $runUuid, $activeWorkerProcs);

    $assert($resA['success'] === true && $resB['success'] === true, 'C1: Both workers succeeded without deadlock or collision failure');
    $replayedCount = (($resA['result']['request_replayed'] ?? false) ? 1 : 0) + (($resB['result']['request_replayed'] ?? false) ? 1 : 0);
    $assert($replayedCount === 1, 'C2: Exactly one worker performed settlement and the other received request_replayed=true');

    $paymentsRace1 = $db->table('finance_payments')->where('finance_id', $raceId1)->get()->getResultArray();
    $assert(count($paymentsRace1) === 1, 'C3: Exactly 1 payment row exists in finance_payments for race 1');
    $assert((float)$paymentsRace1[0]['amount'] === 100.00, 'C4: Durable payment amount is exactly 100.00');

    // Case C2: Competing Stale/Conflict Race (Different request keys competing for same record)
    $raceExpense2 = [
        'clinic_id' => $clinic,
        'transaction_id' => 'RACE-2-' . bin2hex(random_bytes(4)),
        'transaction_type' => 'expense',
        'amount' => '200.00',
        'amount_original' => '200.00',
        'total_amount' => '200.00',
        'balance_due' => '200.00',
        'amount_paid' => '0.00',
        'currency' => 'ILS',
        'exchange_rate' => 1,
        'payment_method' => 'cash',
        'liquidity_account' => 'safe_cash',
        'payment_status' => 'pending',
        'expense_category' => 'salaries',
        'counterparty_name' => 'Synthetic Staff Member 2',
        'transaction_date' => '2026-11-06'
    ];
    $raceId2 = insertFixture($db, 'finances', $raceExpense2);

    $wireRace2A = $guard->issue($clinic, $actor, 'mark_paid', [$raceId2]);
    $wireRace2B = $guard->issue($clinic, $actor, 'mark_paid', [$raceId2]);

    $worker2PayloadA = [
        'app_dir' => $appDir,
        'system_dir' => $systemDir,
        'writable_dir' => $writableDir,
        'creds' => [
            'host' => $expectedHost,
            'port' => $expectedPort,
            'database' => $expectedDb,
            'user' => $expectedUser,
            'password' => $password
        ],
        'clinic_id' => $clinic,
        'actor_id' => $actor,
        'targets' => [$raceId2],
        'wire_context' => $wireRace2A,
        'terms' => [],
        'action' => 'mark_paid'
    ];
    $worker2PayloadB = $worker2PayloadA;
    $worker2PayloadB['wire_context'] = $wireRace2B;

    [$res2A, $res2B] = executeWorkerPairWithBoundTimeouts($workerScript, $worker2PayloadA, $worker2PayloadB, $runDir, $runUuid, $activeWorkerProcs);

    $oneSuccess = ($res2A['success'] === true && ($res2B['conflict'] ?? '') === 'SETTLEMENT_STALE')
               || ($res2B['success'] === true && ($res2A['conflict'] ?? '') === 'SETTLEMENT_STALE');
    $assert($oneSuccess, 'C5: Competing requests resulted in exactly one success and one SETTLEMENT_STALE rejection');

    $paymentsRace2 = $db->table('finance_payments')->where('finance_id', $raceId2)->get()->getResultArray();
    $assert(count($paymentsRace2) === 1, 'C6: Exactly 1 payment recorded for competing race; no double settlement');

    // Case C3: Injected Post-Ledger Failure Rollback
    $raceExpense3 = [
        'clinic_id' => $clinic,
        'transaction_id' => 'RACE-3-' . bin2hex(random_bytes(4)),
        'transaction_type' => 'expense',
        'amount' => '50.00',
        'amount_original' => '50.00',
        'total_amount' => '50.00',
        'balance_due' => '50.00',
        'amount_paid' => '0.00',
        'currency' => 'ILS',
        'exchange_rate' => 1,
        'payment_method' => 'cash',
        'liquidity_account' => 'safe_cash',
        'payment_status' => 'pending',
        'expense_category' => 'salaries',
        'counterparty_name' => 'Synthetic Staff Member 3',
        'transaction_date' => '2026-11-06'
    ];
    $raceId3 = insertFixture($db, 'finances', $raceExpense3);
    $wireRace3 = $guard->issue($clinic, $actor, 'mark_paid', [$raceId3]);

    $stateBefore3 = [
        $db->table('finances')->where('id', $raceId3)->get()->getRowArray(),
        $db->table('finance_payments')->where('finance_id', $raceId3)->get()->getResultArray(),
        $db->table('finance_settlement_requests')->where('request_key', $wireRace3['request_key'])->get()->getResultArray()
    ];

    $faultService = new class($db) extends App\Services\FinanceSettlementService {
        protected function audit(int $clinicId, int $actorId, string $action, int $financeId, string $description, array $metadata): void {
            throw new RuntimeException('Synthetic injected audit failure after ledger insert');
        }
    };

    try {
        $faultService->bulkSettle($clinic, [$raceId3], $actor, [], $wireRace3, 'mark_paid');
        throw new LogicException('Fault not exercised');
    } catch (RuntimeException $e) {
        if ($e->getMessage() !== 'Synthetic injected audit failure after ledger insert') {
            throw $e;
        }
    }

    $stateAfter3 = [
        $db->table('finances')->where('id', $raceId3)->get()->getRowArray(),
        $db->table('finance_payments')->where('finance_id', $raceId3)->get()->getResultArray(),
        $db->table('finance_settlement_requests')->where('request_key', $wireRace3['request_key'])->get()->getResultArray()
    ];

    $assert($stateBefore3 === $stateAfter3, 'C7: Ledger insert rolled back completely upon injected audit failure');
    $assert((int)$db->transDepth === 0, 'C8: Transaction depth returned to 0 after rollback');

    // =========================================================================
    // SECTION D: Teardown & Clean Database Verification
    // =========================================================================

    // 1. Verify exclusive advisory lock is still held
    $lockCheck = $db->query("SELECT IS_USED_LOCK('denta_f5_canonical_run') as lck")->getRowArray();
    if (empty($lockCheck['lck'])) {
        throw new RuntimeException("Advisory run lock lost before teardown");
    }

    // 2. Verify marker row matches this specific run token
    $markerRes = $db->query("SELECT * FROM `{$markerTable}` WHERE `run_uuid` = ? AND `task_signature` = ?", [$runUuid, $taskSignature])->getRowArray();
    if (!$markerRes) {
        throw new RuntimeException("Marker verification failed or tampered before teardown; refusing deletion");
    }

    // 3. Verify no unexpected foreign tables exist (drift quarantine)
    $currentTables = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->getResultArray();
    $foreignTables = [];
    foreach ($currentTables as $tRow) {
        $tbl = array_values($tRow)[0];
        if (!in_array($tbl, $taskCreatedTables, true)) {
            $foreignTables[] = $tbl;
        }
    }
    if (!empty($foreignTables)) {
        throw new RuntimeException("Unexpected table drift detected: foreign tables present: " . implode(', ', $foreignTables) . ". Preserving evidence.");
    }

    // 4. Drop ONLY our verified task-created tables using escaped identifiers
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    $droppedTables = 0;
    foreach ($taskCreatedTables as $tbl) {
        $db->query("DROP TABLE IF EXISTS " . $db->escapeIdentifiers($tbl));
        $droppedTables++;
    }
    $db->query("SET FOREIGN_KEY_CHECKS = 1");

    $finalCount = (int) $db->query("SELECT COUNT(*) cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'")->getRowArray()['cnt'];
    $assert($finalCount === 0, 'D1: Synthetic database left completely empty with 0 tables');

    // Release advisory lock and close $mysqli
    $mysqli->query("SELECT RELEASE_LOCK('denta_f5_canonical_run')");
    $mysqli->close();

    // Clean up private run directory
    if (is_dir($runDir)) {
        foreach (glob($runDir . '/*') as $f) {
            if (is_file($f)) @unlink($f);
        }
        @rmdir($runDir);
    }

    // Source hashes of retained harness files
    $harnessHash = hash_file('sha256', __FILE__);
    $workerHash = hash_file('sha256', $workerScript);

    $evidence = [
        'status' => 'PASS',
        'checkpoint' => 'denta-pr24-f5-acceptance-20261106',
        'run_uuid' => $runUuid,
        'runtime_environment' => [
            'php_version' => PHP_VERSION,
            'php_sapi' => PHP_SAPI,
            'mariadb_version' => $dbVersion,
            'host' => $expectedHost,
            'port' => $expectedPort,
            'database' => $expectedDb,
            'user' => $expectedUser
        ],
        'retained_harness' => [
            'harness_path' => 'tests/regression/FinanceCanonicalEdgeCasesSql.php',
            'harness_sha256' => $harnessHash,
            'worker_path' => 'tests/_support/finance_concurrency_race_worker.php',
            'worker_sha256' => $workerHash
        ],
        'preflight_safeguards' => [
            'host_strictly_local' => true,
            'port_strictly_3306' => true,
            'database_strictly_synthetic' => true,
            'user_grants_strictly_confined' => true,
            'synthetic_negatives_passed' => true,
            'broader_grants_rejected' => true,
            'initial_table_count' => 0,
            'advisory_lock_acquired' => true,
            'marker_table_verified' => true
        ],
        'regression_coverage' => [
            'total_cases' => 4,
            'total_checks_passed' => count($executedChecks),
            'checks' => $executedChecks
        ],
        'teardown' => [
            'tables_dropped' => $droppedTables,
            'final_table_count' => $finalCount,
            'clean_database_verified' => true,
            'drift_protection_verified' => true
        ],
        'safeguards' => [
            'production_database_writes' => 0,
            'business_data_imported' => false,
            'customer_records_accessed' => false
        ],
        'completed_utc' => gmdate('c')
    ];

    echo json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;

} catch (\Throwable $e) {
    fwrite(STDERR, "Exception in master: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");

    // Terminate any active worker processes
    foreach ($activeWorkerProcs as $p) {
        if (is_resource($p)) {
            @proc_terminate($p);
            @proc_close($p);
        }
    }

    // Clean up run dir
    if (isset($runDir) && is_dir($runDir)) {
        foreach (glob($runDir . '/*') as $f) {
            if (is_file($f)) @unlink($f);
        }
        @rmdir($runDir);
    }

    // Safe failure teardown: drop ONLY our verified taskCreatedTables if marker matches runUuid and no foreign tables exist
    try {
        if (isset($db, $markerTable, $runUuid, $taskSignature, $taskCreatedTables)) {
            $markerCheck = $db->query("SELECT * FROM `{$markerTable}` WHERE `run_uuid` = ? AND `task_signature` = ?", [$runUuid, $taskSignature])->getRowArray();
            if ($markerCheck) {
                $currentTables = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->getResultArray();
                $hasForeign = false;
                foreach ($currentTables as $tRow) {
                    $tbl = array_values($tRow)[0];
                    if (!in_array($tbl, $taskCreatedTables, true)) {
                        $hasForeign = true;
                        break;
                    }
                }
                if (!$hasForeign) {
                    $db->query("SET FOREIGN_KEY_CHECKS = 0");
                    foreach ($taskCreatedTables as $tbl) {
                        $db->query("DROP TABLE IF EXISTS " . $db->escapeIdentifiers($tbl));
                    }
                    $db->query("SET FOREIGN_KEY_CHECKS = 1");
                }
            }
        }
    } catch (\Throwable) {}

    try {
        if (isset($mysqli)) {
            @$mysqli->query("SELECT RELEASE_LOCK('denta_f5_canonical_run')");
            @$mysqli->close();
        }
    } catch (\Throwable) {}

    exit(1);
}
