<?php
/**
 * Developer Sandbox Bounded Worker Process (REFUSED UNTIL AUTHORIZED)
 *
 * Implements the concrete parent/worker execution entry point for the developer sandbox database.
 * Performs strict argv parsing, private payload intake, preboot binding, supported transport lockdown,
 * framework boot, database configuration audit, storage security verification, and executes either
 * offline configuration audit or the complete wired real worker branch behind the explicit authorization gate.
 *
 * POLICY INVARIANTS:
 *  - CLI-only execution.
 *  - Strict argv validation: requires exactly one of [--dry-run, --audit-only, --apply].
 *  - Rejects mixed, unknown, duplicate, or misleading options before any side effects.
 *  - Reads private connection credentials strictly from DENTA_VERIFIED_DEV_SANDBOX_CONNECTION.
 *  - Requires authoritative parent snapshot envelope (connection + protected_databases).
 *  - Clears raw payload from environment immediately in finally block.
 *  - Blanks alternative routes (DSN, failover, socket, subdriver) before framework boot.
 *  - Injects fail-closed email transport lockdown (protocol=smtp, SMTPHost='', SMTPPort=0).
 *  - Audits instantiated Config\Database.
 *  - Storage privacy check must pass; failed/missing storage check fails closed.
 *  - Live execution is UNCONDITIONALLY REFUSED awaiting explicit Codex authorization (DENTA_AUTHORIZED_DEV_SANDBOX_APPLY=1).
 *  - Real worker branch is fully wired and usable by normal verified contexts once authorized.
 *  - Demonstrates call chain offline via injected test backend stub when DENTA_SANDBOX_WORKER_TEST_MODE=1.
 */

declare(strict_types=1);

namespace Scripts;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Error: CLI execution is required.\n");
    exit(1);
}

// 1. Strict argv validation BEFORE any framework boot or side effects
$validModes = ['--dry-run', '--audit-only', '--apply'];
$cliArgs = array_slice($argv, 1);
if (count($cliArgs) !== 1 || !in_array($cliArgs[0], $validModes, true)) {
    fwrite(STDERR, "CRITICAL REFUSAL: Invalid arguments. Exactly one of [--dry-run, --audit-only, --apply] is required. Mixed, duplicate, or unknown options are rejected.\n");
    exit(1);
}
$executionMode = $cliArgs[0];

use Scripts\DeveloperSandboxPreflight;
use Scripts\DeveloperSandboxExecutor;
use Scripts\DeveloperSandboxStorageGuard;
use Scripts\DeveloperSandboxTestConnectionStub;
use Scripts\DeveloperSandboxMigrationAdapterInterface;
use Scripts\CodeIgniterMigrationRunnerAdapter;
use Scripts\DeveloperSandboxConnectionBootstrap;

// Catch failures before CI's exception renderer can serialize connection/context
// arguments (which contain private credentials). Keep failure output credential-free.
try {
require_once __DIR__ . '/DeveloperSandboxPreflight.php';
require_once __DIR__ . '/DeveloperSandboxExecutor.php';

// 2. Private intake from environment
$rawPayload = getenv('DENTA_VERIFIED_DEV_SANDBOX_CONNECTION');
if ($rawPayload === false || trim((string) $rawPayload) === '') {
    fwrite(STDERR, "CRITICAL REFUSAL: Missing verified developer sandbox connection payload in environment.\n");
    exit(1);
}

try {
    $payload = json_decode((string) $rawPayload, true, 512, JSON_THROW_ON_ERROR);
} catch (\Throwable $e) {
    putenv('DENTA_VERIFIED_DEV_SANDBOX_CONNECTION');
    unset($_ENV['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION'], $_SERVER['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION']);
    fwrite(STDERR, "CRITICAL REFUSAL: Malformed JSON payload in environment.\n");
    exit(1);
} finally {
    // Redact raw payload from environment immediately on all exit paths
    putenv('DENTA_VERIFIED_DEV_SANDBOX_CONNECTION');
    unset($_ENV['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION'], $_SERVER['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION']);
}

// 3. Validate authoritative parent snapshot envelope
if (!is_array($payload)
    || empty($payload['connection']) || !is_array($payload['connection'])
    || empty($payload['protected_databases']) || !is_array($payload['protected_databases'])
    || empty($payload['protected_databases']['database']) || !is_string($payload['protected_databases']['database'])
    || trim($payload['protected_databases']['database']) === ''
) {
    fwrite(STDERR, "CRITICAL REFUSAL: Missing authoritative parent snapshot envelope or protected database configuration.\n");
    exit(1);
}

require_once __DIR__ . '/DeveloperSandboxConnectionBootstrap.php';

$connConfig = $payload['connection'];
$protectedDbConfig = $payload['protected_databases'];

// 4. Pre-flight config validation BEFORE connection
$configErrors = DeveloperSandboxPreflight::validateConfigBeforeConnection($connConfig, $protectedDbConfig);
if (!empty($configErrors)) {
    fwrite(STDERR, "CRITICAL REFUSAL: Preflight config validation failed:\n" . implode("\n", $configErrors) . "\n");
    exit(1);
}

// 5. Preboot binding: clear alternative routes and bind connection parameters
$alternativeRoutes = ['DSN', 'failover', 'subdriver', 'socket', 'failover.0.hostname'];
foreach (['database.default.', 'database.tests.'] as $prefix) {
    foreach ($alternativeRoutes as $alt) {
        $altKey = $prefix . $alt;
        putenv($altKey . '=');
        $_ENV[$altKey] = '';
        $_SERVER[$altKey] = '';
    }
}

foreach ($connConfig as $key => $val) {
    $valStr = (string) $val;
    $encoded = $key === 'DBPrefix' ? $valStr : ($valStr === '' ? 'empty' : ($valStr === '0' ? 0 : $valStr));
    foreach (['database.default.', 'database.tests.'] as $prefix) {
        $k = $prefix . $key;
        putenv($k . '=' . $encoded);
        $_ENV[$k] = $encoded;
        $_SERVER[$k] = $encoded;
    }
}

// 6. Fail-closed email transport lockdown
putenv('email.protocol=smtp');
$_ENV['email.protocol'] = 'smtp';
$_SERVER['email.protocol'] = 'smtp';
putenv('email.SMTPHost=');
$_ENV['email.SMTPHost'] = '';
$_SERVER['email.SMTPHost'] = '';
putenv('email.SMTPPort=0');
$_ENV['email.SMTPPort'] = '0';
$_SERVER['email.SMTPPort'] = '0';
putenv('email.mailPath=/dev/null');
$_ENV['email.mailPath'] = '/dev/null';
$_SERVER['email.mailPath'] = '/dev/null';

// 7. Boot CodeIgniter test bootstrap
require_once dirname(__DIR__) . '/system/Test/bootstrap.php';

// 8. Audit instantiated database configuration
$dbConfig = config(\Config\Database::class);
$auditErrors = DeveloperSandboxConnectionBootstrap::auditDatabaseConfig($dbConfig);
if (!empty($auditErrors)) {
    fwrite(STDERR, "CRITICAL REFUSAL: Database configuration audit failed:\n" . implode("\n", $auditErrors) . "\n");
    exit(1);
}

// 9. Storage Security Guard Verification
$storageDir = getenv('DENTA_DEV_SANDBOX_VAULT_DIR') ?: DeveloperSandboxStorageGuard::getDefaultStorageDirectory();
try {
    DeveloperSandboxStorageGuard::ensurePrivateDirectory($storageDir);
    $privacyCheck = DeveloperSandboxStorageGuard::verifyDirectoryPrivacy($storageDir, true);
    if (!$privacyCheck['valid']) {
        fwrite(STDERR, "CRITICAL REFUSAL: Storage privacy check failed: " . ($privacyCheck['error'] ?? 'unknown error') . "\n");
        exit(1);
    }
} catch (\Throwable $e) {
    fwrite(STDERR, "CRITICAL REFUSAL: Storage security guard failed: " . $e->getMessage() . "\n");
    exit(1);
}

// 10. Offline Audit Mode (--dry-run or --audit-only)
if ($executionMode === '--dry-run' || $executionMode === '--audit-only') {
    $response = [
        'status' => 'AUDIT_COMPLETE_REFUSED_UNTIL_AUTHORIZED',
        'worker' => 'DeveloperSandboxWorker',
        'mode' => $executionMode,
        'target_database' => DeveloperSandboxPreflight::APPROVED_DEFAULT_SANDBOX_DB,
        'port' => 3307,
        'preboot_audit' => 'PASSED',
        'outbound_transport' => 'LOCKED_DOWN',
        'storage_privacy' => 'PASSED',
        'live_execution' => 'UNCONDITIONALLY_REFUSED',
        'note' => 'Preboot configuration audited offline. Live execution requires explicit authorization gate.',
    ];
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    exit(0);
}

/**
 * Worker Test Stub for offline call-chain demonstration
 */
class DeveloperSandboxWorkerTestStub implements DeveloperSandboxTestConnectionStub
{
    private int $migrationCount = 0;
    private array $latestRow = [];

    public function listTables(): array
    {
        return [];
    }

    public function query(string $sql, array $params = [])
    {
        $normalized = trim($sql);

        if (str_contains($normalized, 'DATABASE()') || str_contains($normalized, 'CURRENT_USER()')) {
            return new class {
                public function getRowArray() {
                    return [
                        'current_db' => 'democa_dental_saas_dev',
                        'current_user' => 'democa_dev@127.0.0.1',
                        'server_ip' => '127.0.0.1',
                        'server_port' => '3307',
                    ];
                }
            };
        }

        if (str_contains($normalized, 'SHOW GRANTS')) {
            return new class {
                public function getResultArray() {
                    return [
                        ['Grants for democa_dev@127.0.0.1' => "GRANT ALL PRIVILEGES ON `democa_dental_saas_dev`.* TO 'democa_dev'@'127.0.0.1'"]
                    ];
                }
            };
        }

        if (str_contains($normalized, 'information_schema.tables')) {
            return new class {
                public function getResultArray() { return []; }
            };
        }

        if (str_contains($normalized, 'SELECT COUNT(*) as c FROM `migrations`')) {
            $c = $this->migrationCount;
            return new class($c) {
                private int $c;
                public function __construct(int $c) { $this->c = $c; }
                public function getRowArray() { return ['c' => $this->c]; }
            };
        }

        if (str_contains($normalized, 'SELECT `id`, `version`, `class`') && str_contains($normalized, 'FROM `migrations`')) {
            $row = $this->latestRow;
            return new class($row) {
                private array $row;
                public function __construct(array $row) { $this->row = $row; }
                public function getRowArray() { return $this->row; }
            };
        }

        if (str_contains($normalized, 'SELECT 1 FROM `_dev_sandbox_marker`') || str_contains($normalized, 'SELECT 1 FROM `users`')) {
            return new class {
                public function getRowArray() { return ['1' => 1]; }
            };
        }

        if (str_contains($normalized, 'SELECT id FROM `roles`') && str_contains($normalized, 'slug')) {
            return new class {
                public function getRowArray() { return ['id' => 1]; }
            };
        }

        if (str_contains($normalized, 'SELECT id FROM `users` WHERE `id` = 1')
            || str_contains($normalized, 'SELECT id FROM `clinics` WHERE `id` = 1')
            || str_contains($normalized, 'SELECT id FROM `plans` WHERE `id` = 1')
        ) {
            return new class {
                public function getRowArray() { return ['id' => 1]; }
            };
        }

        if (str_contains($normalized, 'SELECT features_json FROM plans WHERE id = 1')) {
            return new class {
                public function getRowArray() {
                    return [
                        'features_json' => json_encode([
                            'notifications' => [
                                'sms' => ['enabled' => false],
                                'whatsapp' => ['enabled' => false],
                                'email' => ['enabled' => false],
                            ],
                        ]),
                    ];
                }
            };
        }

        return new class {
            public function getRowArray() { return null; }
            public function getResultArray() { return []; }
        };
    }

    public function recordMigration(string $version, string $class): void
    {
        $this->migrationCount++;
        $this->latestRow = [
            'id' => $this->migrationCount,
            'version' => $version,
            'class' => $class,
            'group' => 'default',
            'namespace' => 'App',
            'time' => time(),
            'batch' => 1,
        ];
    }
}

// 11. Concrete Wired Worker Execution Branch (--apply)
// Behind explicit still-closed authorization gate
$authorized = getenv('DENTA_AUTHORIZED_DEV_SANDBOX_APPLY') === '1';
$testBackendMode = getenv('DENTA_SANDBOX_WORKER_TEST_MODE') === '1';

if (!$authorized && !$testBackendMode) {
    fwrite(STDERR, "CRITICAL REFUSAL: Live database bootstrap execution is unconditionally refused. Codex approval (DENTA_AUTHORIZED_DEV_SANDBOX_APPLY=1) is required.\n");
    exit(1);
}

if ($testBackendMode) {
    // Offline call-chain demonstration using injected test backend stub
    $db = new DeveloperSandboxWorkerTestStub();
    $context = DeveloperSandboxExecutor::createTestMockContext($db, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $storageDir);

    $migrationAdapter = new class($db) implements DeveloperSandboxMigrationAdapterInterface {
        private object $db;
        public function __construct(object $db) { $this->db = $db; }
        public function runMigrationFile(string $filePath, string $namespace = 'App', string $group = 'default'): bool {
            $baseName = basename($filePath);
            $version = substr($baseName, 0, (int) strpos($baseName, '_'));
            $content = file_get_contents($filePath) ?: '';
            preg_match('/class\s+([a-zA-Z0-9_]+)\s+extends/i', $content, $m);
            $class = 'App\\Database\\Migrations\\' . ($m[1] ?? 'unknown');
            if (method_exists($this->db, 'recordMigration')) {
                $this->db->recordMigration($version, $class);
            }
            return true;
        }
    };

    $executor = new DeveloperSandboxExecutor();
    $report = $executor->runBootstrap($context, $db, $migrationAdapter);

    $response = [
        'status' => 'OFFLINE_DEMONSTRATION_SUCCESS',
        'worker' => 'DeveloperSandboxWorker',
        'mode' => '--apply',
        'execution_report' => $report,
    ];
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    exit(0);
}

// Live authorized path (when DENTA_AUTHORIZED_DEV_SANDBOX_APPLY=1)
// Connects through verified path, initializes verified context, constructs runner, and executes runBootstrap
$db = \Config\Database::connect('default');
$context = DeveloperSandboxExecutor::initializeVerifiedContext(
    $db,
    $connConfig,
    $payload,
    DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE,
    false, // isTestMockRun = false (normal verified context)
    true,  // isLiveApply = true
    $storageDir
);

$migrationAdapter = new CodeIgniterMigrationRunnerAdapter(null, $db);
$executor = new DeveloperSandboxExecutor();
$results = $executor->runBootstrap($context, $db, $migrationAdapter);

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
exit($results['success'] ? 0 : 1);
} catch (\Throwable $failure) {
    putenv('DENTA_VERIFIED_DEV_SANDBOX_CONNECTION');
    unset($_ENV['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION'], $_SERVER['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION']);
    if (isset($context)) {
        try {
            $context->recordJournal('orchestration', 'worker_exception', ['exception_class' => get_class($failure)]);
        } catch (\Throwable $journalFailure) {
            // Never let a secondary reporting error invoke the framework renderer.
        }
    }
    fwrite(STDERR, "CRITICAL REFUSAL: Developer sandbox worker failed. Inspect the private stage journal; execution was not completed.\n");
    exit(1);
}
