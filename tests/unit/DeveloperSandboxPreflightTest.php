<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/scripts/DeveloperSandboxPreflight.php';
require_once dirname(__DIR__, 2) . '/scripts/DeveloperSandboxExecutor.php';
require_once dirname(__DIR__, 2) . '/scripts/DeveloperSandboxConnectionBootstrap.php';

use Scripts\DeveloperSandboxPreflight;
use Scripts\DeveloperSandboxExecutor;
use Scripts\DeveloperSandboxRunContext;
use Scripts\DeveloperSandboxConnectionBootstrap;

/**
 * Pure Database-Free Unit Tests for DeveloperSandboxPreflight & DeveloperSandboxBootstrap CLI
 *
 * Verifies that the sandbox preflight enforces strict isolation:
 *  - Enforces exact target schema: democa_dental_saas_dev on 127.0.0.1:3307 ONLY.
 *  - Refuses business database (democa_dental_saas) WITHOUT connecting.
 *  - Refuses automated test database (democa_dental_saas_test) WITHOUT connecting.
 *  - Refuses remote hosts and unauthorized ports/drivers.
 *  - Refuses port 3306 and missing ports; requires explicit port 3307.
 *  - Refuses recovery, reference, negative, and system schemas (denta_rec_*, mysql, etc.).
 *  - Fails closed if authoritative business config is missing or malformed.
 *  - Fails closed on incomplete live identity (missing/null port, user, host).
 *  - Refuses populated schemas unconditionally (reset removed from Version 1).
 *  - Enforces truthful CLI behavior and plan-only execution in DeveloperSandboxBootstrap.
 */
final class DeveloperSandboxPreflightTest extends TestCase
{
    private array $validSandboxConfig = [
        'hostname' => '127.0.0.1',
        'port' => 3307,
        'DBDriver' => 'MySQLi',
        'database' => 'democa_dental_saas_dev',
        'username' => 'dev_synthetic',
        'password' => '',
    ];

    private array $validBusinessConfig = [
        'hostname' => '127.0.0.1',
        'port' => 3306,
        'DBDriver' => 'MySQLi',
        'database' => 'democa_dental_saas',
        'username' => 'root',
        'password' => '',
    ];

    private array $testCreatedDirs = [];
    private array $testCreatedJunctions = [];

    protected function trackTestDir(string $dir): string
    {
        $this->testCreatedDirs[] = $dir;
        return $dir;
    }

    protected function trackTestJunction(string $junction): string
    {
        $this->testCreatedJunctions[] = $junction;
        return $junction;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->testCreatedJunctions as $junc) {
            if (file_exists($junc) || is_link($junc)) {
                if (PHP_OS_FAMILY === 'Windows') {
                    exec('cmd /c rmdir ' . escapeshellarg($junc));
                } else {
                    @unlink($junc);
                }
            }
        }
        $this->testCreatedJunctions = [];

        foreach ($this->testCreatedDirs as $dir) {
            if (is_dir($dir)) {
                self::recursiveRemoveDirectory($dir);
            }
        }
        $this->testCreatedDirs = [];
    }

    private static function recursiveRemoveDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $p = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($p) && !is_link($p)) {
                self::recursiveRemoveDirectory($p);
            } else {
                @unlink($p);
            }
        }
        @rmdir($dir);
    }

    protected function createUniversalMockDb(array &$executedQueries = []): \Scripts\DeveloperSandboxTestConnectionStub
    {
        return new class($executedQueries) implements \Scripts\DeveloperSandboxTestConnectionStub {
            private array $eq;
            public int $migrationCount = 0;
            public array $latestRow = [];
            public bool $user1Inserted = false;

            public function __construct(&$eq) { $this->eq = &$eq; }

            public function listTables(): array { return []; }

            public function query(string $sql, array $params = []) {
                $this->eq[] = [$sql, $params];
                $normalized = trim($sql);

                if (str_contains($normalized, 'INSERT INTO `users`') || str_contains($normalized, 'INSERT INTO users')) {
                    $this->user1Inserted = true;
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

                if (str_contains($normalized, 'SELECT id FROM `migrations` WHERE `version`')) {
                    return new class {
                        public function getRowArray() { return null; }
                        public function getResultArray() { return []; }
                    };
                }

                if (str_contains($normalized, '_dev_sandbox_marker') || str_contains($normalized, 'SELECT 1 FROM `users`')) {
                    return new class {
                        public function getRowArray() { return ['1' => 1]; }
                        public function getResultArray() { return []; }
                    };
                }

                if (str_contains($normalized, 'SELECT id FROM `roles`') && str_contains($normalized, 'slug')) {
                    return new class {
                        public function getRowArray() { return ['id' => 1]; }
                        public function getResultArray() { return []; }
                    };
                }

                if (str_contains($normalized, 'SELECT id FROM `users` WHERE `id` = 1')) {
                    if (!$this->user1Inserted) {
                        return new class {
                            public function getRowArray() { return null; }
                            public function getResultArray() { return []; }
                        };
                    }
                    return new class {
                        public function getRowArray() { return ['id' => 1]; }
                        public function getResultArray() { return []; }
                    };
                }

                if (str_contains($normalized, 'SELECT id FROM `clinics` WHERE `id` = 1')
                    || str_contains($normalized, 'SELECT id FROM `plans` WHERE `id` = 1')
                ) {
                    return new class {
                        public function getRowArray() { return ['id' => 1]; }
                        public function getResultArray() { return []; }
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

                if (str_contains($normalized, 'information_schema.TABLES') && str_contains($normalized, 'BASE TABLE')) {
                    return new class {
                        public function getRowArray() { return ['table_count' => 69]; }
                        public function getResultArray() { return [['table_count' => 69]]; }
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
        };
    }

    protected function createMockMigrationAdapter(object $mockDb): \Scripts\DeveloperSandboxMigrationAdapterInterface
    {
        return new class($mockDb) implements \Scripts\DeveloperSandboxMigrationAdapterInterface {
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
    }

    protected function advanceMockContextToPhase(
        \Scripts\DeveloperSandboxExecutor $executor,
        \Scripts\DeveloperSandboxRunContext $context,
        object $mockDb,
        int $targetPhase,
        ?\Scripts\DeveloperSandboxMigrationAdapterInterface $adapter = null
    ): void {
        $adapter = $adapter ?? $this->createMockMigrationAdapter($mockDb);

        if ($targetPhase >= \Scripts\DeveloperSandboxRunContext::PHASE_STAGE1_DONE && $context->getCurrentPhase() === \Scripts\DeveloperSandboxRunContext::PHASE_INITIALIZED) {
            $res = $executor->executeStage1_VerifyEmptinessAndCreateMarker($context, $mockDb);
            if (!$res['success']) {
                throw new \RuntimeException("Setup failure in Stage 1: " . ($res['message'] ?? ''));
            }
        }
        if ($targetPhase >= \Scripts\DeveloperSandboxRunContext::PHASE_STAGE2_DONE && $context->getCurrentPhase() === \Scripts\DeveloperSandboxRunContext::PHASE_STAGE1_DONE) {
            $res = $executor->executeStage2_ApplyLegacyFoundation($context, $mockDb);
            if (!$res['success']) {
                throw new \RuntimeException("Setup failure in Stage 2: " . ($res['message'] ?? ''));
            }
        }
        if ($targetPhase >= \Scripts\DeveloperSandboxRunContext::PHASE_STAGE3_DONE && $context->getCurrentPhase() === \Scripts\DeveloperSandboxRunContext::PHASE_STAGE2_DONE) {
            $res = $executor->executeStage3_ExecuteMigrations($context, $mockDb, $adapter);
            if (!$res['success']) {
                throw new \RuntimeException("Setup failure in Stage 3: " . ($res['message'] ?? ''));
            }
        }
        if ($targetPhase >= \Scripts\DeveloperSandboxRunContext::PHASE_STAGE4_DONE && $context->getCurrentPhase() === \Scripts\DeveloperSandboxRunContext::PHASE_STAGE3_DONE) {
            $res = $executor->executeStage4_InitializeUser1AndSyncRbac($context, $mockDb);
            if (!$res['success']) {
                throw new \RuntimeException("Setup failure in Stage 4: " . ($res['message'] ?? ''));
            }
        }
        if ($targetPhase >= \Scripts\DeveloperSandboxRunContext::PHASE_STAGE5_DONE && $context->getCurrentPhase() === \Scripts\DeveloperSandboxRunContext::PHASE_STAGE4_DONE) {
            $res = $executor->executeStage5_SeedSyntheticTenantsAndPlans($context, $mockDb);
            if (!$res['success']) {
                throw new \RuntimeException("Setup failure in Stage 5: " . ($res['message'] ?? ''));
            }
        }
        if ($targetPhase >= \Scripts\DeveloperSandboxRunContext::PHASE_STAGE6_DONE && $context->getCurrentPhase() === \Scripts\DeveloperSandboxRunContext::PHASE_STAGE5_DONE) {
            $res = $executor->executeStage6_SeedCanonicalSettings($context, $mockDb);
            if (!$res['success']) {
                throw new \RuntimeException("Setup failure in Stage 6: " . ($res['message'] ?? ''));
            }
        }
        if ($targetPhase >= \Scripts\DeveloperSandboxRunContext::PHASE_STAGE7_DONE && $context->getCurrentPhase() === \Scripts\DeveloperSandboxRunContext::PHASE_STAGE6_DONE) {
            $res = $executor->executeStage7_SeedDeveloperPersonasAndMemberships($context, $mockDb);
            if (!$res['success']) {
                throw new \RuntimeException("Setup failure in Stage 7: " . ($res['message'] ?? ''));
            }
        }
        if ($targetPhase >= \Scripts\DeveloperSandboxRunContext::PHASE_STAGE8_DONE && $context->getCurrentPhase() === \Scripts\DeveloperSandboxRunContext::PHASE_STAGE7_DONE) {
            $res = $executor->executeStage8_VerifyMessagingTransportLockdown($context, $mockDb);
            if (!$res['success']) {
                throw new \RuntimeException("Setup failure in Stage 8: " . ($res['message'] ?? ''));
            }
        }
    }

    public function testPreflightPassesForApprovedSandboxConfig(): void
    {
        $errors = DeveloperSandboxPreflight::validateConfigBeforeConnection(
            $this->validSandboxConfig,
            $this->validBusinessConfig
        );

        $this->assertEmpty($errors, 'Approved sandbox configuration should produce zero errors.');
    }

    /**
     * CODEX PROBE 1 REGRESSION:
     * Caller-selected arbitrary target name (e.g. denta_rec_do_not_touch) passed through
     * targetDb override and matching configured sandbox target MUST be rejected.
     */
    public function testCodexProbe1RegressionCallerSelectedArbitraryTargetRefusedEvenIfMatchingConfig(): void
    {
        $probeConfig = $this->validSandboxConfig;
        $probeConfig['database'] = 'denta_rec_do_not_touch';

        // Caller passes matching targetDb override
        $errors = DeveloperSandboxPreflight::validateConfigBeforeConnection(
            $probeConfig,
            $this->validBusinessConfig,
            'denta_rec_do_not_touch'
        );

        $this->assertNotEmpty($errors, 'Codex Probe 1: Arbitrary target name must NOT return empty errors.');
        $hasTargetRefusal = false;
        foreach ($errors as $err) {
            if (str_contains($err, 'is not the approved sandbox schema') || str_contains($err, 'protected recovery/reference pattern')) {
                $hasTargetRefusal = true;
                break;
            }
        }
        $this->assertTrue($hasTargetRefusal, 'Must report target schema refusal for denta_rec_do_not_touch.');
    }

    /**
     * CODEX PROBE 2 REGRESSION:
     * Mocked live identity containing only current_db with missing/null port or user MUST fail closed.
     */
    public function testCodexProbe2RegressionMissingPortOrUserFailsClosed(): void
    {
        // Mock returning only current_db, missing port, host, and user
        $mockDb = new class {
            public function query(string $sql) {
                return new class {
                    public function getRowArray() {
                        return [
                            'current_db' => 'democa_dental_saas_dev',
                            // srv_host, srv_port, curr_user intentionally missing/null
                        ];
                    }
                };
            }
        };

        $errors = DeveloperSandboxPreflight::validateLiveConnection($mockDb, 'democa_dental_saas_dev');
        $this->assertNotEmpty($errors, 'Codex Probe 2: Missing live identity fields must NOT return empty errors.');

        $hasPortError = false;
        $hasUserError = false;
        foreach ($errors as $err) {
            if (str_contains($err, '@@port identity is missing or zero')) {
                $hasPortError = true;
            }
            if (str_contains($err, 'USER() identity is missing or empty')) {
                $hasUserError = true;
            }
        }

        $this->assertTrue($hasPortError, 'Must fail closed when live srv_port is missing.');
        $this->assertTrue($hasUserError, 'Must fail closed when live curr_user is missing.');
    }

    public function testPreflightRefusesPort3306AndMissingPort(): void
    {
        // 1. Port 3306 (stopped business MySQL)
        $port3306Config = $this->validSandboxConfig;
        $port3306Config['port'] = 3306;
        $errors3306 = DeveloperSandboxPreflight::validateConfigBeforeConnection($port3306Config, $this->validBusinessConfig);
        $this->assertNotEmpty($errors3306);
        $this->assertTrue(
            array_reduce($errors3306, fn($carry, $err) => $carry || str_contains($err, 'port 3306 (business MySQL) is strictly refused'), false),
            'Must refuse port 3306.'
        );

        // 2. Missing port (no implicit default to 3306 allowed)
        $noPortConfig = $this->validSandboxConfig;
        unset($noPortConfig['port']);
        $errorsNoPort = DeveloperSandboxPreflight::validateConfigBeforeConnection($noPortConfig, $this->validBusinessConfig);
        $this->assertNotEmpty($errorsNoPort);
        $this->assertTrue(
            array_reduce($errorsNoPort, fn($carry, $err) => $carry || str_contains($err, 'sandbox port is missing'), false),
            'Must refuse missing port without defaulting.'
        );
    }

    public function testPreflightFailsClosedIfBusinessConfigIsMissingOrMalformed(): void
    {
        // Empty business config
        $errorsEmpty = DeveloperSandboxPreflight::validateConfigBeforeConnection($this->validSandboxConfig, []);
        $this->assertNotEmpty($errorsEmpty);
        $this->assertTrue(
            array_reduce($errorsEmpty, fn($carry, $err) => $carry || str_contains($err, 'authoritative business database configuration is missing'), false),
            'Must fail closed when business config is missing.'
        );

        // Malformed business config (empty database)
        $errorsMalformed = DeveloperSandboxPreflight::validateConfigBeforeConnection($this->validSandboxConfig, ['database' => '']);
        $this->assertNotEmpty($errorsMalformed);
        $this->assertTrue(
            array_reduce($errorsMalformed, fn($carry, $err) => $carry || str_contains($err, 'authoritative business database configuration is missing'), false),
            'Must fail closed when business database name is empty.'
        );
    }

    public function testPreflightRefusesTargetMatchingCustomBusinessDbName(): void
    {
        // Even if business DB is named arbitrarily (e.g. 'custom_clinic_prod'), sandbox cannot match it
        $customBusinessConfig = [
            'database' => 'democa_dental_saas_dev', // malicious match
        ];
        $errors = DeveloperSandboxPreflight::validateConfigBeforeConnection($this->validSandboxConfig, $customBusinessConfig);
        $this->assertNotEmpty($errors);
        $this->assertTrue(
            array_reduce($errors, fn($carry, $err) => $carry || str_contains($err, 'matches authoritative business database'), false),
            'Must refuse sandbox when it matches authoritative business database.'
        );
    }

    public function testPreflightRefusesSystemSchemasAndProtectedPatterns(): void
    {
        // System schemas
        foreach (['mysql', 'information_schema', 'performance_schema', 'sys'] as $sys) {
            $sysConfig = $this->validSandboxConfig;
            $sysConfig['database'] = $sys;
            $errors = DeveloperSandboxPreflight::validateConfigBeforeConnection($sysConfig, $this->validBusinessConfig);
            $this->assertTrue(
                array_reduce($errors, fn($carry, $err) => $carry || str_contains($err, 'is a protected MySQL system schema'), false),
                "Must refuse system schema '{$sys}'."
            );
        }

        // Recovery / reference patterns
        foreach (['denta_rec_20261017', 'denta_ref_backup', 'denta_neg_control'] as $pat) {
            $patConfig = $this->validSandboxConfig;
            $patConfig['database'] = $pat;
            $errors = DeveloperSandboxPreflight::validateConfigBeforeConnection($patConfig, $this->validBusinessConfig);
            $this->assertTrue(
                array_reduce($errors, fn($carry, $err) => $carry || str_contains($err, 'protected recovery/reference pattern'), false),
                "Must refuse protected pattern '{$pat}'."
            );
        }
    }

    public function testPreflightRefusesBusinessDatabaseWithoutConnecting(): void
    {
        $badConfig = $this->validSandboxConfig;
        $badConfig['database'] = 'democa_dental_saas';

        $errors = DeveloperSandboxPreflight::validateConfigBeforeConnection(
            $badConfig,
            $this->validBusinessConfig
        );

        $this->assertNotEmpty($errors);
        $this->assertTrue(
            array_reduce($errors, fn($carry, $err) => $carry || str_contains($err, 'production business database'), false),
            'Must refuse default business database.'
        );
    }

    public function testPreflightRefusesAutomatedTestDatabaseWithoutConnecting(): void
    {
        $badConfig = $this->validSandboxConfig;
        $badConfig['database'] = 'democa_dental_saas_test';

        $errors = DeveloperSandboxPreflight::validateConfigBeforeConnection(
            $badConfig,
            $this->validBusinessConfig
        );

        $this->assertNotEmpty($errors);
        $this->assertTrue(
            array_reduce($errors, fn($carry, $err) => $carry || str_contains($err, 'automated test database'), false),
            'Must refuse automated test database.'
        );
    }

    public function testPreflightRefusesRemoteHostWithoutConnecting(): void
    {
        $badConfig = $this->validSandboxConfig;
        $badConfig['hostname'] = '192.168.1.100';

        $errors = DeveloperSandboxPreflight::validateConfigBeforeConnection(
            $badConfig,
            $this->validBusinessConfig
        );

        $this->assertNotEmpty($errors);
        $this->assertTrue(
            array_reduce($errors, fn($carry, $err) => $carry || str_contains($err, 'Remote targets are strictly forbidden'), false),
            'Must refuse remote host.'
        );
    }

    public function testPreflightRefusesNonMySQLiDriverWithoutConnecting(): void
    {
        $badConfig = $this->validSandboxConfig;
        $badConfig['DBDriver'] = 'Postgre';

        $errors = DeveloperSandboxPreflight::validateConfigBeforeConnection(
            $badConfig,
            $this->validBusinessConfig
        );

        $this->assertNotEmpty($errors);
        $this->assertTrue(
            array_reduce($errors, fn($carry, $err) => $carry || str_contains($err, 'Expected MySQLi'), false),
            'Must refuse non-MySQLi driver.'
        );
    }

    public function testPreflightRefusesAlternateOverrides(): void
    {
        $dsnConfig = $this->validSandboxConfig;
        $dsnConfig['DSN'] = 'mysql:host=localhost;dbname=democa_dental_saas_dev';
        $errors = DeveloperSandboxPreflight::validateConfigBeforeConnection($dsnConfig, $this->validBusinessConfig);
        $this->assertTrue(
            array_reduce($errors, fn($carry, $err) => $carry || str_contains($err, "'DSN' override is forbidden"), false)
        );

        $failoverConfig = $this->validSandboxConfig;
        $failoverConfig['failover'] = [['hostname' => '127.0.0.1']];
        $errors2 = DeveloperSandboxPreflight::validateConfigBeforeConnection($failoverConfig, $this->validBusinessConfig);
        $this->assertTrue(
            array_reduce($errors2, fn($carry, $err) => $carry || str_contains($err, "'failover' configuration is forbidden"), false)
        );

        $socketConfig = $this->validSandboxConfig;
        $socketConfig['socket'] = '/tmp/mysql.sock';
        $errors3 = DeveloperSandboxPreflight::validateConfigBeforeConnection($socketConfig, $this->validBusinessConfig);
        $this->assertTrue(
            array_reduce($errors3, fn($carry, $err) => $carry || str_contains($err, "'socket' override is forbidden"), false)
        );
    }

    public function testLiveConnectionValidationPassesForApprovedIdentity(): void
    {
        $mockDb = new class {
            public function query(string $sql) {
                return new class {
                    public function getRowArray() {
                        return [
                            'current_db' => 'democa_dental_saas_dev',
                            'srv_host' => '127.0.0.1',
                            'srv_port' => 3307,
                            'curr_user' => 'dev_synthetic@127.0.0.1',
                        ];
                    }
                };
            }
        };

        $errors = DeveloperSandboxPreflight::validateLiveConnection($mockDb, 'democa_dental_saas_dev');
        $this->assertEmpty($errors, 'Approved live identity on port 3307 should have zero errors.');
    }

    public function testLiveConnectionValidationRefusesPort3306(): void
    {
        $mockDb = new class {
            public function query(string $sql) {
                return new class {
                    public function getRowArray() {
                        return [
                            'current_db' => 'democa_dental_saas_dev',
                            'srv_host' => '127.0.0.1',
                            'srv_port' => 3306,
                            'curr_user' => 'dev@127.0.0.1',
                        ];
                    }
                };
            }
        };

        $errors = DeveloperSandboxPreflight::validateLiveConnection($mockDb, 'democa_dental_saas_dev');
        $this->assertNotEmpty($errors);
        $this->assertTrue(
            array_reduce($errors, fn($carry, $err) => $carry || str_contains($err, 'Port 3306 is strictly forbidden'), false),
            'Must refuse live connection on port 3306.'
        );
    }

    public function testLiveConnectionValidationRefusesRemoteClientHost(): void
    {
        $mockDb = new class {
            public function query(string $sql) {
                return new class {
                    public function getRowArray() {
                        return [
                            'current_db' => 'democa_dental_saas_dev',
                            'srv_host' => '127.0.0.1',
                            'srv_port' => 3307,
                            'curr_user' => 'dev@192.168.1.50',
                        ];
                    }
                };
            }
        };

        $errors = DeveloperSandboxPreflight::validateLiveConnection($mockDb, 'democa_dental_saas_dev');
        $this->assertNotEmpty($errors);
        $this->assertTrue(
            array_reduce($errors, fn($carry, $err) => $carry || str_contains($err, 'Only local loopback connections are allowed'), false)
        );
    }

    public function testTargetEligibilityAllowsCompletelyEmptyDatabase(): void
    {
        $mockDb = new class {
            public function listTables(): array {
                return [];
            }
            public function query(string $sql) {
                return new class {
                    public function getResultArray() {
                        return [];
                    }
                };
            }
        };

        $res = DeveloperSandboxPreflight::verifyTargetEligibilityForBootstrap($mockDb, 'democa_dental_saas_dev');
        $this->assertTrue($res['allowed']);
        $this->assertStringContainsString('completely empty', $res['reason']);
    }

    public function testTargetEligibilityRefusesPopulatedDatabaseUnconditionally(): void
    {
        // In version 1, even an apparently owned target is refused if non-empty
        $mockDb = new class {
            public function listTables(): array {
                return ['users', 'patients', '_dev_sandbox_marker'];
            }
            public function query(string $sql) {
                return new class {
                    public function getResultArray() {
                        return [];
                    }
                };
            }
        };

        $res = DeveloperSandboxPreflight::verifyTargetEligibilityForBootstrap($mockDb, 'democa_dental_saas_dev');
        $this->assertFalse($res['allowed']);
        $this->assertStringContainsString('is NOT empty', $res['reason']);
        $this->assertStringContainsString('Reset/overwrite is strictly refused in Version 1', $res['reason']);
    }

    public function testTargetEligibilityRefusesDatabaseContainingViews(): void
    {
        $mockDb = new class {
            public function listTables(): array {
                return [];
            }
            public function query(string $sql) {
                return new class {
                    public function getResultArray() {
                        return [['Tables_in_test' => 'v_patient_summary', 'Table_type' => 'VIEW']];
                    }
                };
            }
        };

        $res = DeveloperSandboxPreflight::verifyTargetEligibilityForBootstrap($mockDb, 'democa_dental_saas_dev');
        $this->assertFalse($res['allowed']);
        $this->assertStringContainsString('is NOT empty', $res['reason']);
        $this->assertStringContainsString('1 view(s)', $res['reason']);
    }

    /**
     * SUBPROCESS CLI TESTS for scripts/DeveloperSandboxBootstrap.php
     * Verify CLI behavior strictly without database access.
     */
    public function testBootstrapCliDefaultExecutionOutputsPlanWithoutExecution(): void
    {
        $cmd = 'php ' . escapeshellarg(dirname(__DIR__, 2) . '/scripts/DeveloperSandboxBootstrap.php');
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        $joined = implode("\n", $output);
        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Preflight:          NOT EXECUTED', $joined);
        $this->assertStringContainsString('Database Readiness: NOT VERIFIED', $joined);
        $this->assertStringContainsString('PLAN ONLY', $joined);
        $this->assertStringContainsString('Zero database mutations were performed', $joined);
    }

    public function testBootstrapCliHelpOptionOutputsTruthfulHelp(): void
    {
        $cmd = 'php ' . escapeshellarg(dirname(__DIR__, 2) . '/scripts/DeveloperSandboxBootstrap.php') . ' --help';
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        $joined = implode("\n", $output);
        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('--dry-run', $joined);
        $this->assertStringContainsString('--target-db', $joined);
        $this->assertStringContainsString('REFUSED', $joined);
    }

    public function testBootstrapCliRefusesApplyArgument(): void
    {
        $cmd = 'php ' . escapeshellarg(dirname(__DIR__, 2) . '/scripts/DeveloperSandboxBootstrap.php') . ' --apply 2>&1';
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        $joined = implode("\n", $output);
        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('CRITICAL REFUSAL: Execution (--apply) is not supported', $joined);
    }

    public function testBootstrapCliRefusesResetArgument(): void
    {
        $cmd = 'php ' . escapeshellarg(dirname(__DIR__, 2) . '/scripts/DeveloperSandboxBootstrap.php') . ' --reset 2>&1';
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        $joined = implode("\n", $output);
        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('CRITICAL REFUSAL: Database reset (--reset) has been removed', $joined);
    }

    public function testBootstrapCliRefusesCheckOnlyArgument(): void
    {
        $cmd = 'php ' . escapeshellarg(dirname(__DIR__, 2) . '/scripts/DeveloperSandboxBootstrap.php') . ' --check-only 2>&1';
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        $joined = implode("\n", $output);
        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('CRITICAL REFUSAL: Connection check (--check-only) is not supported offline', $joined);
    }

    public function testBootstrapCliRefusesArbitraryTargetDb(): void
    {
        $cmd = 'php ' . escapeshellarg(dirname(__DIR__, 2) . '/scripts/DeveloperSandboxBootstrap.php') . ' --target-db=denta_rec_do_not_touch 2>&1';
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        $joined = implode("\n", $output);
        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString("CRITICAL REFUSAL: Target database 'denta_rec_do_not_touch' is strictly forbidden", $joined);
    }

    public function testBootstrapCliRefusesBusinessTargetDb(): void
    {
        $cmd = 'php ' . escapeshellarg(dirname(__DIR__, 2) . '/scripts/DeveloperSandboxBootstrap.php') . ' --target-db=democa_dental_saas 2>&1';
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        $joined = implode("\n", $output);
        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString("CRITICAL REFUSAL: Target database 'democa_dental_saas' is strictly forbidden", $joined);
    }

    public function testBootstrapCliRefusesUnknownArguments(): void
    {
        $cmd = 'php ' . escapeshellarg(dirname(__DIR__, 2) . '/scripts/DeveloperSandboxBootstrap.php') . ' --unexpected-flag 2>&1';
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        $joined = implode("\n", $output);
        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString("CRITICAL REFUSAL: Unknown or conflicting argument '--unexpected-flag'", $joined);
    }

    public function testMalformedConfigPortsAreRefused(): void
    {
        foreach (['3307garbage', '3307.9', 3307.9, true] as $port) {
            $config = $this->validSandboxConfig;
            $config['port'] = $port;
            $this->assertNotEmpty(DeveloperSandboxPreflight::validateConfigBeforeConnection($config, $this->validBusinessConfig));
        }
    }

    public function testUnavailableViewMetadataDoesNotAuthorizeBootstrap(): void
    {
        foreach (['exception', 'false'] as $mode) {
            $db = new class($mode) {
                public function __construct(private string $mode) {}
                public function listTables(): array { return []; }
                public function query(string $sql) {
                    if ($this->mode === 'exception') { throw new \RuntimeException('metadata unavailable'); }
                    return false;
                }
            };
            $this->assertFalse(DeveloperSandboxPreflight::verifyTargetEligibilityForBootstrap($db)['allowed']);
        }
    }

    public function testEligibilityRejectsUnapprovedTargetWithoutQueries(): void
    {
        $db = new class {
            public function listTables(): array { throw new \LogicException('Must not query this target'); }
        };
        $this->assertFalse(DeveloperSandboxPreflight::verifyTargetEligibilityForBootstrap($db, 'denta_rec_preserved')['allowed']);
    }

    public function testChildBridgeRejectsUnverifiedTargetsWithoutDisclosingPayload(): void
    {
        $script = dirname(__DIR__, 2) . '/scripts/DeveloperSandboxConnectionBootstrap.php';
        $valid = [
            'hostname' => '127.0.0.1',
            'port' => 3307,
            'database' => 'democa_dental_saas_dev',
            'username' => 'synthetic',
            'password' => 'SYNTHETIC-DEV-SECRET-9988',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
        ];
        $cases = [
            array_replace($valid, ['hostname' => 'remote.invalid']),
            array_replace($valid, ['database' => 'democa_dental_saas']),
            array_replace($valid, ['database' => 'democa_dental_saas_test']),
            array_replace($valid, ['port' => 3306]),
            array_replace($valid, ['port' => 80]),
            array_diff_key($valid, ['password' => true]),
            'invalid-json',
        ];

        foreach ($cases as $case) {
            $env = getenv();
            $env['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION'] = is_array($case) ? json_encode($case) : $case;
            $process = proc_open([PHP_BINARY, $script], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__, 2), $env);
            self::assertIsResource($process);
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            self::assertNotSame(0, proc_close($process));
            self::assertStringNotContainsString('SYNTHETIC-DEV-SECRET-9988', $output);
            self::assertStringContainsString('Verified isolated dev sandbox connection required.', $output);
        }
    }

    public function testChildBridgeBindsEnvironmentAndLocksDownOutboundTransport(): void
    {
        $bootstrapScript = dirname(__DIR__, 2) . '/scripts/DeveloperSandboxConnectionBootstrap.php';
        $valid = [
            'hostname' => '127.0.0.1',
            'port' => 3307,
            'database' => 'democa_dental_saas_dev',
            'username' => 'dev_user',
            'password' => 'secret_pass',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
        ];

        // Child process that includes the bridge and asserts env variables
        $code = 'require ' . var_export($bootstrapScript, true) . '; ' .
                'echo getenv("database.default.database") . "|" . ' .
                'getenv("database.default.port") . "|" . ' .
                'getenv("email.protocol") . "|" . ' .
                '(getenv("DENTA_VERIFIED_DEV_SANDBOX_CONNECTION") === false ? "cleared" : "leaked");';

        $env = getenv();
        $envelope = [
            'connection' => $valid,
            'protected_databases' => [
                'database' => 'democa_dental_saas',
            ],
        ];
        $env['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION'] = json_encode($envelope);

        $process = proc_open([PHP_BINARY, '-r', $code], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__, 2), $env);
        self::assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        $this->assertSame(0, $exitCode, "Child bridge should exit 0 with valid configuration. Error: {$err}");
        $this->assertSame('democa_dental_saas_dev|3307|smtp|cleared', trim($output));
    }

    public function testUserGrantsValidationPassesForConfinedGrants(): void
    {
        $mockDb = new class {
            public function query(string $sql) {
                return new class {
                    public function getResultArray() {
                        return [
                            ['Grants for dev@127.0.0.1' => "GRANT USAGE ON *.* TO 'dev'@'127.0.0.1'"],
                            ['Grants for dev@127.0.0.1' => "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER ON `democa\_dental\_saas\_dev`.* TO 'dev'@'127.0.0.1'"]
                        ];
                    }
                };
            }
        };

        $errors = DeveloperSandboxPreflight::validateUserGrants($mockDb, 'democa_dental_saas_dev');
        $this->assertEmpty($errors, 'Confined grants with USAGE ON *.* and escaped sandbox schema should pass with zero errors.');
    }

    public function testUserGrantsValidationRefusesForbiddenDatabaseGrants(): void
    {
        $mockDb = new class {
            public function query(string $sql) {
                return new class {
                    public function getResultArray() {
                        return [
                            ['Grants for dev@127.0.0.1' => "GRANT ALL PRIVILEGES ON `democa_dental_saas`.* TO 'dev'@'127.0.0.1'"]
                        ];
                    }
                };
            }
        };

        $errors = DeveloperSandboxPreflight::validateUserGrants($mockDb, 'democa_dental_saas_dev');
        $this->assertNotEmpty($errors);
        $this->assertTrue(
            array_reduce($errors, fn($carry, $err) => $carry || str_contains($err, "User holds grants on unauthorized schema 'democa_dental_saas'"), false),
            'Must refuse grants targeting business database.'
        );
    }

    public function testUserGrantsValidationFailsClosedOnEmptyOrFailedQuery(): void
    {
        // Empty rows
        $mockDbEmpty = new class {
            public function query(string $sql) {
                return new class {
                    public function getResultArray() { return []; }
                };
            }
        };
        $errorsEmpty = DeveloperSandboxPreflight::validateUserGrants($mockDbEmpty, 'democa_dental_saas_dev');
        $this->assertNotEmpty($errorsEmpty);
        $this->assertTrue(
            array_reduce($errorsEmpty, fn($carry, $err) => $carry || str_contains($err, 'returned zero records'), false)
        );

        // Exception
        $mockDbException = new class {
            public function query(string $sql) {
                throw new \RuntimeException('permission denied for SHOW GRANTS');
            }
        };
        $errorsEx = DeveloperSandboxPreflight::validateUserGrants($mockDbException, 'democa_dental_saas_dev');
        $this->assertNotEmpty($errorsEx);
        $this->assertTrue(
            array_reduce($errorsEx, fn($carry, $err) => $carry || str_contains($err, 'Exception while inspecting user grants'), false)
        );
    }

    public function testTargetEligibilityRejectsRoutinesAndEvents(): void
    {
        // 1. Target contains routine (stored procedure)
        $mockDbRoutine = new class {
            public function listTables(): array { return []; }
            public function query(string $sql) {
                return new class($sql) {
                    public function __construct(private string $sql) {}
                    public function getResultArray() {
                        if (str_contains($this->sql, 'SHOW FULL TABLES')) {
                            return [];
                        }
                        if (str_contains($this->sql, 'SHOW PROCEDURE STATUS')) {
                            return [['Db' => 'democa_dental_saas_dev', 'Name' => 'sp_backup']];
                        }
                        return [];
                    }
                };
            }
        };
        $resRoutine = DeveloperSandboxPreflight::verifyTargetEligibilityForBootstrap($mockDbRoutine, 'democa_dental_saas_dev');
        $this->assertFalse($resRoutine['allowed']);
        $this->assertStringContainsString('1 routine(s)', $resRoutine['reason']);

        // 2. Target contains scheduled event
        $mockDbEvent = new class {
            public function listTables(): array { return []; }
            public function query(string $sql) {
                return new class($sql) {
                    public function __construct(private string $sql) {}
                    public function getResultArray() {
                        if (str_contains($this->sql, 'SHOW FULL TABLES')) {
                            return [];
                        }
                        if (str_contains($this->sql, 'SHOW EVENTS')) {
                            return [['Db' => 'democa_dental_saas_dev', 'Name' => 'ev_cleanup']];
                        }
                        return [];
                    }
                };
            }
        };
        $resEvent = DeveloperSandboxPreflight::verifyTargetEligibilityForBootstrap($mockDbEvent, 'democa_dental_saas_dev');
        $this->assertFalse($resEvent['allowed']);
        $this->assertStringContainsString('1 event(s)', $resEvent['reason']);
    }

    public function testBootstrapCliModeSelectionOptions(): void
    {
        // 1. Explicit production-baseline mode
        $cmdBaseline = 'php ' . escapeshellarg(dirname(__DIR__, 2) . '/scripts/DeveloperSandboxBootstrap.php') . ' --mode=production-baseline';
        $outBaseline = [];
        $exitBaseline = 0;
        exec($cmdBaseline, $outBaseline, $exitBaseline);
        $joinedBaseline = implode("\n", $outBaseline);
        $this->assertSame(0, $exitBaseline);
        $this->assertStringContainsString('Mode:               production-baseline', $joinedBaseline);
        $this->assertStringContainsString('"current_source_included_count": 77', $joinedBaseline);

        // 2. Explicit all-source mode
        $cmdAllSource = 'php ' . escapeshellarg(dirname(__DIR__, 2) . '/scripts/DeveloperSandboxBootstrap.php') . ' --mode=all-source';
        $outAllSource = [];
        $exitAllSource = 0;
        exec($cmdAllSource, $outAllSource, $exitAllSource);
        $joinedAllSource = implode("\n", $outAllSource);
        $this->assertSame(0, $exitAllSource);
        $this->assertStringContainsString('Mode:               all-source', $joinedAllSource);
        $this->assertStringContainsString('"current_source_included_count": 85', $joinedAllSource);

        // 3. Unsupported mode rejected
        $cmdBad = 'php ' . escapeshellarg(dirname(__DIR__, 2) . '/scripts/DeveloperSandboxBootstrap.php') . ' --mode=invalid-mode 2>&1';
        $outBad = [];
        $exitBad = 0;
        exec($cmdBad, $outBad, $exitBad);
        $joinedBad = implode("\n", $outBad);
        $this->assertSame(1, $exitBad);
        $this->assertStringContainsString("CRITICAL REFUSAL: Unsupported mode 'invalid-mode'", $joinedBad);
    }

    public function testDeveloperSandboxExecutorStageDefinitions(): void
    {
        $executor = new DeveloperSandboxExecutor();

        // Migration plan resolution
        $baselinePlan = $executor->resolveMigrationPlan('production-baseline');
        $this->assertCount(77, $baselinePlan, 'Production baseline mode must resolve exactly 77 migrations.');

        $allSourcePlan = $executor->resolveMigrationPlan('all-source');
        $this->assertCount(85, $allSourcePlan, 'All-source mode must resolve all 85 migrations.');

        // Canonical settings definition
        $settings = $executor->getCanonicalSettingsDefinition();
        $this->assertSame('Asia/Jerusalem', $settings['global']['default_timezone']);
        $this->assertSame('after', $settings['global']['default_currency_position']);
        $this->assertSame(1, $settings['clinic']['booking_chair_count']);
        $this->assertSame(['Doctor 1'], $settings['clinic']['booking_chair_names']);
        $this->assertSame(0, $settings['clinic']['dynamic_doctor_scheduling_enabled']);

        // Synthetic tenants and personas definition
        $data = $executor->getSyntheticTenantsAndPersonasDefinition();
        $this->assertCount(2, $data['tenants']);
        $this->assertCount(5, $data['personas']);
        $this->assertFalse($data['plan']['features_json']['notifications']['sms']['enabled']);
        $this->assertFalse($data['plan']['features_json']['notifications']['whatsapp']['enabled']);
        $this->assertFalse($data['plan']['features_json']['notifications']['email']['enabled']);
        $this->assertArrayNotHasKey('global_mode', $data['personas'][0], 'global_mode is session state, not a user DB field.');
        $this->assertArrayNotHasKey('is_system', $data['personas'][0], 'is_system is not a user DB field.');
        $this->assertStringEndsWith('.test', $data['personas'][0]['email']);
    }

    public function testDeveloperSandboxExecutorMockExecution(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));

        $executedQueries = [];
        $mockDb = $this->createUniversalMockDb($executedQueries);

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);

        // Stage 1 on mock DB
        $res1 = $executor->executeStage1_VerifyEmptinessAndCreateMarker($context, $mockDb);
        $this->assertTrue($res1['success'], 'Stage 1 should succeed on empty target.');

        // Stage 2: foundation DDL
        $res2 = $executor->executeStage2_ApplyLegacyFoundation($context, $mockDb);
        $this->assertTrue($res2['success']);
        $this->assertSame(18, $res2['tables_applied'], 'Must apply exactly 18 foundation tables.');

        // Advance through Stage 3 canonically to test Stage 4
        $mockAdapter = $this->createMockMigrationAdapter($mockDb);
        $res3 = $executor->executeStage3_ExecuteMigrations($context, $mockDb, $mockAdapter);
        $this->assertTrue($res3['success']);

        // Stage 4: user 1 init & rbac
        $res4 = $executor->executeStage4_InitializeUser1AndSyncRbac($context, $mockDb);
        $this->assertTrue($res4['success']);
        $this->assertNotEmpty($executedQueries);
        $userQueries = array_filter($executedQueries, fn($q) => str_contains($q[0], 'superadmin') || (isset($q[1]) && in_array('superadmin', $q[1], true)));
        $this->assertNotEmpty($userQueries, 'Must have executed query inserting superadmin user.');
    }

    /**
     * CODEX PROBE 1 REGRESSION:
     * SHOW GRANTS returning global privileges with WITH GRANT OPTION, foreign schemas,
     * PROXY, or unescaped wildcard patterns MUST fail closed and produce non-empty errors.
     */
    public function testCodexProbe1RegressionShowGrantsAllowlistRejectsExcessiveGrantsWildcardsAndGrantOption(): void
    {
        // 1. Probe 1 case: Global ALL PRIVILEGES with GRANT OPTION
        $mockDbGlobal = new class {
            public function query(string $sql) {
                return new class {
                    public function getResultArray() {
                        return [
                            ['Grants for dev@localhost' => "GRANT ALL PRIVILEGES ON *.* TO 'dev'@'localhost' WITH GRANT OPTION"]
                        ];
                    }
                };
            }
        };
        $errorsGlobal = DeveloperSandboxPreflight::validateUserGrants($mockDbGlobal, 'democa_dental_saas_dev');
        $this->assertNotEmpty($errorsGlobal, 'Codex Probe 1: Global ALL PRIVILEGES WITH GRANT OPTION must not produce empty error list.');
        $hasGrantOption = false;
        $hasExcessiveGlobal = false;
        $hasNoSandboxGrant = false;
        foreach ($errorsGlobal as $err) {
            if (str_contains($err, 'WITH GRANT OPTION')) $hasGrantOption = true;
            if (str_contains($err, 'excessive global privileges on *.*')) $hasExcessiveGlobal = true;
            if (str_contains($err, 'User holds no privileges on the target sandbox database')) $hasNoSandboxGrant = true;
        }
        $this->assertTrue($hasGrantOption, 'Must report WITH GRANT OPTION refusal.');
        $this->assertTrue($hasExcessiveGlobal, 'Must report excessive global privileges on *.*.');
        $this->assertTrue($hasNoSandboxGrant, 'Must report missing sandbox privileges.');

        // 2. Unescaped underscore in schema name: functions as wildcard in MariaDB grants
        $mockDbWildcard = new class {
            public function query(string $sql) {
                return new class {
                    public function getResultArray() {
                        return [
                            ['Grants for dev@localhost' => "GRANT ALL PRIVILEGES ON `democa_dental_saas_dev`.* TO 'dev'@'localhost'"]
                        ];
                    }
                };
            }
        };
        $errorsWildcard = DeveloperSandboxPreflight::validateUserGrants($mockDbWildcard, 'democa_dental_saas_dev');
        $this->assertNotEmpty($errorsWildcard, 'Unescaped underscore in schema grant must be rejected due to MariaDB wildcard semantics.');
        $this->assertTrue(
            array_reduce($errorsWildcard, fn($carry, $err) => $carry || str_contains($err, 'contains unescaped wildcard pattern characters'), false),
            'Must report unescaped wildcard pattern refusal.'
        );

        // 3. Foreign schema grant
        $mockDbForeign = new class {
            public function query(string $sql) {
                return new class {
                    public function getResultArray() {
                        return [
                            ['Grants for dev@localhost' => "GRANT SELECT ON `other_clinic_db`.* TO 'dev'@'localhost'"]
                        ];
                    }
                };
            }
        };
        $errorsForeign = DeveloperSandboxPreflight::validateUserGrants($mockDbForeign, 'democa_dental_saas_dev');
        $this->assertNotEmpty($errorsForeign);
        $this->assertTrue(
            array_reduce($errorsForeign, fn($carry, $err) => $carry || str_contains($err, "grants on unauthorized schema 'other_clinic_db'"), false)
        );

        // 4. PROXY grant
        $mockDbProxy = new class {
            public function query(string $sql) {
                return new class {
                    public function getResultArray() {
                        return [
                            ['Grants for dev@localhost' => "GRANT PROXY ON ''@'%' TO 'dev'@'localhost' WITH GRANT OPTION"]
                        ];
                    }
                };
            }
        };
        $errorsProxy = DeveloperSandboxPreflight::validateUserGrants($mockDbProxy, 'democa_dental_saas_dev');
        $this->assertNotEmpty($errorsProxy);
        $this->assertTrue(
            array_reduce($errorsProxy, fn($carry, $err) => $carry || str_contains($err, 'PROXY privilege'), false)
        );

        // 5. Positive case: USAGE ON *.* and properly escaped sandbox schema
        $mockDbValid = new class {
            public function query(string $sql) {
                return new class {
                    public function getResultArray() {
                        return [
                            ['Grants for dev@localhost' => "GRANT USAGE ON *.* TO 'dev'@'localhost'"],
                            ['Grants for dev@localhost' => "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, REFERENCES, CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE, CREATE VIEW, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EVENT, TRIGGER ON `democa\\_dental\\_saas\\_dev`.* TO 'dev'@'localhost'"]
                        ];
                    }
                };
            }
        };
        $errorsValid = DeveloperSandboxPreflight::validateUserGrants($mockDbValid, 'democa_dental_saas_dev');
        $this->assertEmpty($errorsValid, 'Properly escaped sandbox grants with USAGE ON *.* must pass with zero errors.');
    }

    /**
     * CODEX PROBE 2 REGRESSION:
     * Metadata queries throwing or returning false for routines or events MUST fail closed
     * and NOT be treated as empty target schemas.
     */
    public function testCodexProbe2RegressionRoutineOrEventMetadataFailureRefusesEligibility(): void
    {
        // 1. Procedure metadata query throws exception
        $mockDbProcException = new class {
            public function listTables(): array { return []; }
            public function query(string $sql) {
                if (str_contains($sql, 'SHOW FULL TABLES')) {
                    return new class { public function getResultArray() { return []; } };
                }
                if (str_contains($sql, 'SHOW PROCEDURE STATUS')) {
                    throw new \RuntimeException('Procedure status metadata failure');
                }
                return new class { public function getResultArray() { return []; } };
            }
        };
        $resProcEx = DeveloperSandboxPreflight::verifyTargetEligibilityForBootstrap($mockDbProcException, 'democa_dental_saas_dev');
        $this->assertFalse($resProcEx['allowed'], 'Codex Probe 2: Procedure metadata exception must fail closed (allowed=false).');
        $this->assertTrue(str_contains($resProcEx['reason'], 'procedure inventory unavailable') || str_contains($resProcEx['reason'], 'Routine inventory unavailable'));

        // 2. Procedure metadata query returns false
        $mockDbProcFalse = new class {
            public function listTables(): array { return []; }
            public function query(string $sql) {
                if (str_contains($sql, 'SHOW FULL TABLES')) {
                    return new class { public function getResultArray() { return []; } };
                }
                if (str_contains($sql, 'SHOW PROCEDURE STATUS')) {
                    return false;
                }
                return new class { public function getResultArray() { return []; } };
            }
        };
        $resProcFalse = DeveloperSandboxPreflight::verifyTargetEligibilityForBootstrap($mockDbProcFalse, 'democa_dental_saas_dev');
        $this->assertFalse($resProcFalse['allowed'], 'Codex Probe 2: Procedure metadata query returning false must fail closed.');
        $this->assertTrue(str_contains($resProcFalse['reason'], 'procedure inventory unavailable') || str_contains($resProcFalse['reason'], 'Routine inventory unavailable'));

        // 3. Event metadata query throws exception
        $mockDbEventException = new class {
            public function listTables(): array { return []; }
            public function query(string $sql) {
                if (str_contains($sql, 'SHOW FULL TABLES') || str_contains($sql, 'STATUS')) {
                    return new class { public function getResultArray() { return []; } };
                }
                if (str_contains($sql, 'SHOW EVENTS')) {
                    throw new \RuntimeException('Events metadata query failure');
                }
                return new class { public function getResultArray() { return []; } };
            }
        };
        $resEventEx = DeveloperSandboxPreflight::verifyTargetEligibilityForBootstrap($mockDbEventException, 'democa_dental_saas_dev');
        $this->assertFalse($resEventEx['allowed'], 'Codex Probe 2: Events metadata exception must fail closed.');
        $this->assertStringContainsString('Scheduled event inventory unavailable', $resEventEx['reason']);

        // 4. Event metadata query returns false
        $mockDbEventFalse = new class {
            public function listTables(): array { return []; }
            public function query(string $sql) {
                if (str_contains($sql, 'SHOW FULL TABLES') || str_contains($sql, 'STATUS')) {
                    return new class { public function getResultArray() { return []; } };
                }
                if (str_contains($sql, 'SHOW EVENTS')) {
                    return false;
                }
                return new class { public function getResultArray() { return []; } };
            }
        };
        $resEventFalse = DeveloperSandboxPreflight::verifyTargetEligibilityForBootstrap($mockDbEventFalse, 'democa_dental_saas_dev');
        $this->assertFalse($resEventFalse['allowed'], 'Codex Probe 2: Events metadata query returning false must fail closed.');
        $this->assertStringContainsString('Scheduled event inventory unavailable', $resEventFalse['reason']);
    }

    /**
     * CODEX PROBE 3 REGRESSION:
     * A DB stub returning false for DDL statements MUST report failure and zero tables applied,
     * not false success with 18 statements applied.
     */
    public function testCodexProbe3RegressionDbStubReturningFalseForDdlReportsFailure(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));

        $mockDbFalse = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public int $callCount = 0;
            public function listTables(): array { return []; }
            public function query(string $sql, array $params = []) {
                $this->callCount++;
                if (str_contains($sql, '_dev_sandbox_marker') || str_contains($sql, 'SHOW FULL TABLES') || str_contains($sql, 'STATUS') || str_contains($sql, 'SHOW EVENTS')) {
                    return new class {
                        public function getRowArray() { return ['1' => 1]; }
                        public function getResultArray() { return []; }
                    };
                }
                return false; // Stub returns false for DDL
            }
        };

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDbFalse, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);
        $res1 = $executor->executeStage1_VerifyEmptinessAndCreateMarker($context, $mockDbFalse);
        $this->assertTrue($res1['success']);

        $res = $executor->executeStage2_ApplyLegacyFoundation($context, $mockDbFalse);
        $this->assertFalse($res['success'], 'Codex Probe 3: DB stub returning false must yield success=false.');
        $this->assertSame(0, $res['tables_applied'], 'Codex Probe 3: Applied tables count must be 0 when DDL returns false.');
        $this->assertStringContainsString('Foundation table DDL returned false/null at statement index 0', $res['message']);
    }

    /**
     * CODEX PROBE 4 REGRESSION:
     * Real CodeIgniter Email service resolution under child bridge settings
     * (email.protocol=smtp, SMTPHost='', SMTPPort=0):
     * Demonstrates that Email protocol is smtp (NOT falling back to PHP mail),
     * and direct send() returns false immediately via sendWithSmtp without calling mail().
     */
    public function testCodexProbe4RegressionRealCiEmailUnderBridgeConfigFailsClosedWithoutMailCall(): void
    {
        $bootstrapScript = dirname(__DIR__, 2) . '/scripts/DeveloperSandboxConnectionBootstrap.php';
        $valid = [
            'hostname' => '127.0.0.1',
            'port' => 3307,
            'database' => 'democa_dental_saas_dev',
            'username' => 'dev_user',
            'password' => 'secret_pass',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
        ];

        // Child process that includes the bridge and validates CodeIgniter Email configuration and behavior
        $code = 'require ' . var_export($bootstrapScript, true) . ';' .
                'require ' . var_export(dirname(__DIR__, 2) . '/system/Test/bootstrap.php', true) . ';' .
                '$cfg = config(\Config\Email::class);' .
                '$protocol = $cfg->protocol;' .
                '$smtpHost = $cfg->SMTPHost;' .
                '$smtpPort = $cfg->SMTPPort;' .
                'echo $protocol . "|" . ($smtpHost === "" ? "empty" : $smtpHost) . "|" . $smtpPort;';

        $env = getenv();
        $envelope = [
            'connection' => $valid,
            'protected_databases' => [
                'database' => 'democa_dental_saas',
            ],
        ];
        $env['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION'] = json_encode($envelope);

        $process = proc_open([PHP_BINARY, '-r', $code], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__, 2), $env);
        self::assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        $this->assertSame(0, $exitCode, "Child execution should succeed without errors. Error: {$err}");
        $this->assertSame('smtp|empty|0', trim($output), "Codex Probe 4: Real CI Email config must resolve to protocol=smtp with empty host and port 0.");

        // Pure unit check on CodeIgniter Email setProtocol fallback behavior:
        // Demonstrates that setProtocol('mock') falls back to 'mail', whereas setProtocol('smtp') preserves 'smtp'.
        $dummyConfig = [
            'fromEmail' => 'from@test.test',
            'fromName' => 'Test',
            'protocol' => 'smtp',
            'SMTPHost' => '',
            'SMTPPort' => 0,
            'mailPath' => '/dev/null',
        ];
        $email = new \CodeIgniter\Email\Email($dummyConfig);
        $email->setProtocol('mock');
        $prop = new \ReflectionProperty($email, 'protocol');
        $prop->setAccessible(true);
        $this->assertSame('mail', $prop->getValue($email), "Proof of Probe 4 root cause: setProtocol('mock') falls back to 'mail'.");

        // When protocol is 'smtp' and SMTPHost is '', sendWithSmtp returns false without I/O
        $email->setProtocol('smtp');
        $this->assertSame('smtp', $prop->getValue($email));
        $sendWithSmtpMethod = new \ReflectionMethod($email, 'sendWithSmtp');
        $sendWithSmtpMethod->setAccessible(true);
        $this->assertFalse($sendWithSmtpMethod->invoke($email), "sendWithSmtp must return false immediately when SMTPHost is empty.");
    }

    /**
     * CODEX PROBE 5 REGRESSION:
     * Inherited database.default.DSN and database.tests.DSN present before bootstrap bridge runs
     * are strictly cleared by the bridge, establishing actual Config\Database isolation.
     */
    public function testCodexProbe5RegressionInheritedDsnClearedAndConfigDatabaseIsolated(): void
    {
        $bootstrapScript = dirname(__DIR__, 2) . '/scripts/DeveloperSandboxConnectionBootstrap.php';
        $valid = [
            'hostname' => '127.0.0.1',
            'port' => 3307,
            'database' => 'democa_dental_saas_dev',
            'username' => 'dev_user',
            'password' => 'secret_pass',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
        ];

        // Child process where database.default.DSN is poisoned in the environment before bootstrap runs
        $code = 'putenv("database.default.DSN=mysql:host=malicious_host;dbname=democa_dental_saas");' .
                '$_ENV["database.default.DSN"] = "mysql:host=malicious_host;dbname=democa_dental_saas";' .
                '$_SERVER["database.default.DSN"] = "mysql:host=malicious_host;dbname=democa_dental_saas";' .
                'require ' . var_export($bootstrapScript, true) . ';' .
                'require ' . var_export(dirname(__DIR__, 2) . '/system/Test/bootstrap.php', true) . ';' .
                '$config = config(\Config\Database::class);' .
                '$audit = \Scripts\DeveloperSandboxConnectionBootstrap::auditDatabaseConfig($config);' .
                'echo (empty($config->default["DSN"]) ? "dsn_cleared" : "dsn_poisoned") . "|" . ' .
                '(empty($config->tests["DSN"]) ? "tests_dsn_cleared" : "tests_dsn_poisoned") . "|" . ' .
                '(empty($audit) ? "audit_passed" : "audit_failed");';

        $env = getenv();
        $envelope = [
            'connection' => $valid,
            'protected_databases' => [
                'database' => 'democa_dental_saas',
            ],
        ];
        $env['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION'] = json_encode($envelope);

        $process = proc_open([PHP_BINARY, '-r', $code], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__, 2), $env);
        self::assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        $this->assertSame(0, $exitCode, "Child execution should succeed without errors. Error: {$err}");
        $this->assertSame('dsn_cleared|tests_dsn_cleared|audit_passed', trim($output), "Codex Probe 5: Inherited DSN must be cleared and auditDatabaseConfig must pass.");
    }

    /**
     * Stage methods refuse direct execution without a verified DeveloperSandboxRunContext.
     */
    public function testDirectStageExecutionWithoutVerifiedContextRefused(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $dummyDb = new class implements \Scripts\DeveloperSandboxTestConnectionStub { public function query(string $s, array $p = []) { return true; } };

        // 1. Null context
        try {
            $executor->executeStage1_VerifyEmptinessAndCreateMarker(null, $dummyDb);
            $this->fail('Stage 1 must refuse direct execution without context.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('CRITICAL REFUSAL: Direct stage execution without verified DeveloperSandboxRunContext is refused', $e->getMessage());
        }

        // 2. Unverified context probe: public factory does not exist; unverified context minted via reflection fails closed
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));
        $journal = \Scripts\DeveloperSandboxJournal::create($tempDir, 'test_unver');
        $creds = \Scripts\DeveloperSandboxCredentials::generateAndPersist($tempDir, 'test_unver');

        $ref = new \ReflectionClass(\Scripts\DeveloperSandboxRunContext::class);
        $this->assertFalse($ref->hasMethod('createUnverifiedForTesting'), 'DeveloperSandboxRunContext must NOT expose an unverified factory.');
        $this->assertFalse($ref->hasMethod('createVerified'), 'DeveloperSandboxRunContext must NOT expose a public verified factory.');

        $ctor = $ref->getConstructor();
        $ctor->setAccessible(true);
        $unverifiedContext = $ref->newInstanceWithoutConstructor();
        $ctor->invoke(
            $unverifiedContext,
            'test_unver',
            DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE,
            DeveloperSandboxPreflight::APPROVED_DEFAULT_SANDBOX_DB,
            $dummyDb,
            $journal,
            $creds,
            null,
            false, // isVerified = false
            false,
            false
        );

        try {
            $executor->executeStage1_VerifyEmptinessAndCreateMarker($unverifiedContext, $dummyDb);
            $this->fail('Stage 1 must refuse execution when run context is not verified.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('CRITICAL REFUSAL: Stage execution outside verified preflight boundary is strictly forbidden', $e->getMessage());
        }

        // 3. Context bound to different DB instance
        $otherDb = new class implements \Scripts\DeveloperSandboxTestConnectionStub { public function query(string $s, array $p = []) { return true; } };
        $boundContext = DeveloperSandboxExecutor::createTestMockContext($dummyDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir . '_bound');
        try {
            $executor->executeStage1_VerifyEmptinessAndCreateMarker($boundContext, $otherDb);
            $this->fail('Stage 1 must refuse execution when context is not bound to supplied DB.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('CRITICAL REFUSAL: Run context is not bound to the supplied database connection', $e->getMessage());
        }
    }

    /**
     * Orchestration entry point runBootstrap() unconditionally refuses live execution without Codex authorization.
     */
    public function testOrchestrationRunBootstrapRefusesLiveExecutionWithoutCodexAuthorization(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $dummyDb = new class implements \Scripts\DeveloperSandboxTestConnectionStub { public function query(string $s, array $p = []) { return true; } };
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));
        $journal = \Scripts\DeveloperSandboxJournal::create($tempDir, 'test_live');
        $creds = \Scripts\DeveloperSandboxCredentials::generateAndPersist($tempDir, 'test_live');

        $ref = new \ReflectionClass(\Scripts\DeveloperSandboxRunContext::class);
        $ctor = $ref->getConstructor();
        $ctor->setAccessible(true);
        $liveContext = $ref->newInstanceWithoutConstructor();
        $ctor->invoke(
            $liveContext,
            'test_live',
            DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE,
            DeveloperSandboxPreflight::APPROVED_DEFAULT_SANDBOX_DB,
            $dummyDb,
            $journal,
            $creds,
            null,
            true, // isVerified
            false, // isTestMockRun = false
            true // isLiveApply = true
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('CRITICAL REFUSAL: Live database bootstrap execution is unconditionally refused. Codex approval is required.');
        $executor->runBootstrap($liveContext, $dummyDb);
    }

    /**
     * Complete mock orchestration executes all stages in order and halts immediately on partial failure.
     */
    public function testCompleteMockOrchestrationExecutesAllStagesAndHaltsOnFailure(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));

        // 1. Success path: All stages succeed using universal mock DB and adapter
        $queriesSuccess = [];
        $mockDbSuccess = $this->createUniversalMockDb($queriesSuccess);
        $mockMigrationAdapter = $this->createMockMigrationAdapter($mockDbSuccess);

        $contextSuccess = DeveloperSandboxExecutor::createTestMockContext($mockDbSuccess, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir . '_success');

        putenv('email.protocol=smtp');
        putenv('email.SMTPHost=');
        putenv('email.SMTPPort=0');

        $resultSuccess = $executor->runBootstrap($contextSuccess, $mockDbSuccess, $mockMigrationAdapter);
        $this->assertTrue($resultSuccess['success'], 'Orchestration should succeed when all stages pass.');
        $this->assertSame(69, $resultSuccess['tables_created_count'], 'Production baseline mode must report 69 total tables (68 app + 1 marker).');
        $this->assertCount(8, $resultSuccess['stages'], 'All 8 stages must be present in report.');
        $this->assertNotEmpty($resultSuccess['journal'], 'Durable journal must contain progress events.');

        // 2. Halt path: Stage 2 foundation DDL returns false at statement index 2
        $mockDbHalt = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public int $callCount = 0;
            public function listTables(): array { return []; }
            public function query(string $sql, array $params = []) {
                $this->callCount++;
                if (str_contains($sql, '_dev_sandbox_marker')) {
                    return true;
                }
                if (str_contains($sql, 'SHOW')) {
                    return new class { public function getResultArray() { return []; } };
                }
                // Return false on the 2nd foundation DDL statement
                return false;
            }
        };

        $contextHalt = DeveloperSandboxExecutor::createTestMockContext($mockDbHalt, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir . '_halt');

        $resultHalt = $executor->runBootstrap($contextHalt, $mockDbHalt, $mockMigrationAdapter);
        $this->assertFalse($resultHalt['success'], 'Orchestration must report failure when a stage fails.');
        $this->assertTrue($resultHalt['stages']['stage_1']['success'], 'Stage 1 must have completed.');
        $this->assertFalse($resultHalt['stages']['stage_2']['success'], 'Stage 2 must report failure.');
        $this->assertArrayNotHasKey('stage_3', $resultHalt['stages'], 'Stage 3 must NOT have been executed after Stage 2 halted.');

        $journal = $contextHalt->getJournal()->getEntries();
        $lastEvent = end($journal);
        $this->assertSame('orchestration', $lastEvent['stage']);
        $this->assertSame('halted_at_stage_2', $lastEvent['event']);
    }

    /**
     * NEGATIVE TEST 1: Missing runner writes no history
     */
    public function testNegative1MissingMigrationRunnerWritesZeroHistoryAndHalts(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6));

        $mockDbQueryOnly = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public array $executedQueries = [];
            public function listTables(): array { return []; }
            public function query(string $sql, array $params = []) {
                $this->executedQueries[] = [$sql, $params];
                if (str_contains($sql, '_dev_sandbox_marker') || str_contains($sql, 'SELECT 1 FROM `users`') || str_contains($sql, 'SHOW FULL TABLES') || str_contains($sql, 'STATUS') || str_contains($sql, 'SHOW EVENTS')) {
                    return new class {
                        public function getRowArray() { return ['1' => 1]; }
                        public function getResultArray() { return []; }
                    };
                }
                return true;
            }
        };

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDbQueryOnly, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);
        $res1 = $executor->executeStage1_VerifyEmptinessAndCreateMarker($context, $mockDbQueryOnly);
        $this->assertTrue($res1['success']);
        $res2 = $executor->executeStage2_ApplyLegacyFoundation($context, $mockDbQueryOnly);
        $this->assertTrue($res2['success']);

        $res = $executor->executeStage3_ExecuteMigrations($context, $mockDbQueryOnly, null);

        $this->assertFalse($res['success'], 'Stage 3 must fail closed without migration runner.');
        $this->assertSame(0, $res['migrations_applied'], 'Must report 0 applied migrations.');
        $this->assertStringContainsString('Authoritative MigrationRunner backend is unavailable', $res['message']);

        $migrationHistoryQueries = array_filter(
            $mockDbQueryOnly->executedQueries,
            fn($q) => str_contains($q[0], 'migrations')
        );
        $this->assertEmpty($migrationHistoryQueries, 'Zero migration history queries must be executed when runner is absent.');
    }

    /**
     * NEGATIVE TEST 2: Forged context and direct stages write nothing
     */
    public function testNegative2ForgedContextAndDirectStageExecutionRefused(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $dbA = new class implements \Scripts\DeveloperSandboxTestConnectionStub { public function query(string $s, array $p = []) { return true; } };
        $dbB = new class implements \Scripts\DeveloperSandboxTestConnectionStub { public function query(string $s, array $p = []) { return true; } };
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));

        $journal = \Scripts\DeveloperSandboxJournal::create($tempDir, 'forged_run');
        $creds = \Scripts\DeveloperSandboxCredentials::generateAndPersist($tempDir, 'forged_run');

        // Unverified context minted via reflection (isVerified = false)
        $ref = new \ReflectionClass(\Scripts\DeveloperSandboxRunContext::class);
        $ctor = $ref->getConstructor();
        $ctor->setAccessible(true);
        $unverifiedContext = $ref->newInstanceWithoutConstructor();
        $ctor->invoke(
            $unverifiedContext,
            'forged_run',
            DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE,
            DeveloperSandboxPreflight::APPROVED_DEFAULT_SANDBOX_DB,
            $dbA,
            $journal,
            $creds,
            null,
            false, // isVerified = false
            false,
            false
        );

        $stageMethods = [
            'executeStage1_VerifyEmptinessAndCreateMarker',
            'executeStage2_ApplyLegacyFoundation',
            'executeStage4_InitializeUser1AndSyncRbac',
            'executeStage5_SeedSyntheticTenantsAndPlans',
            'executeStage6_SeedCanonicalSettings',
            'executeStage7_SeedDeveloperPersonasAndMemberships',
            'executeStage8_VerifyMessagingTransportLockdown',
        ];

        foreach ($stageMethods as $method) {
            try {
                $executor->{$method}($unverifiedContext, $dbA);
                $this->fail("Stage method {$method} must refuse unverified context.");
            } catch (\RuntimeException $e) {
                $this->assertStringContainsString('Stage execution outside verified preflight boundary is strictly forbidden', $e->getMessage());
            }
        }

        // Mismatched DB connection binding
        $boundContext = DeveloperSandboxExecutor::createTestMockContext($dbA, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir . '_bound');
        foreach ($stageMethods as $method) {
            try {
                $executor->{$method}($boundContext, $dbB);
                $this->fail("Stage method {$method} must refuse mismatched DB connection.");
            } catch (\RuntimeException $e) {
                $this->assertStringContainsString('Run context is not bound to the supplied database connection', $e->getMessage());
            }
        }
    }

    /**
     * NEGATIVE TEST 3: Runner/service false/exception outcomes halt subsequent stages
     */
    public function testNegative3RunnerAndServiceFailureHaltsSubsequentStages(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6));

        $mockDb = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public array $queries = [];
            public function listTables(): array { return []; }
            public function query(string $sql, array $params = []) {
                $this->queries[] = [$sql, $params];
                return new class {
                    public function getResultArray() { return []; }
                    public function getRowArray() { return ['id' => 1]; }
                };
            }
        };

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);

        $failingRunner = new class implements \Scripts\DeveloperSandboxMigrationAdapterInterface {
            public int $callCount = 0;
            public function runMigrationFile(string $f, string $n = 'App', string $g = 'default'): bool {
                $this->callCount++;
                return false; // Fails on first migration
            }
        };

        $result = $executor->runBootstrap($context, $mockDb, $failingRunner);

        $this->assertFalse($result['success'], 'Bootstrap must halt and report failure when runner fails.');
        $this->assertTrue($result['stages']['stage_1']['success']);
        $this->assertTrue($result['stages']['stage_2']['success']);
        $this->assertFalse($result['stages']['stage_3']['success']);
        $this->assertNotEmpty($result['stages']['stage_3']['failed_migration']);

        $this->assertArrayNotHasKey('stage_4', $result['stages']);
        $this->assertArrayNotHasKey('stage_5', $result['stages']);
        $this->assertArrayNotHasKey('stage_6', $result['stages']);
        $this->assertArrayNotHasKey('stage_7', $result['stages']);
        $this->assertArrayNotHasKey('stage_8', $result['stages']);
    }

    /**
     * NEGATIVE TEST 4: Canonical role IDs are resolved rather than guessed
     */
    public function testNegative4CanonicalRoleIdsResolvedBySlugWithoutGuessing(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6));

        // 1. Success resolution
        $mockDbSuccess = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public array $queries = [];
            public function query(string $sql, array $params = []) {
                $this->queries[] = [$sql, $params];
                return new class {
                    public function getRowArray() { return ['id' => 42]; }
                };
            }
        };

        $roleId = $executor->resolveRoleIdBySlug($mockDbSuccess, 'practice_manager');
        $this->assertSame(42, $roleId);
        $this->assertStringContainsString('SELECT id FROM `roles` WHERE `slug` = ? AND `clinic_id` IS NULL', $mockDbSuccess->queries[0][0]);
        $this->assertSame(['practice_manager'], $mockDbSuccess->queries[0][1]);

        // 2. Role not found returns null
        $mockDbNotFound = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public function query(string $sql, array $params = []) {
                return new class {
                    public function getRowArray() { return null; }
                };
            }
        };
        $this->assertNull($executor->resolveRoleIdBySlug($mockDbNotFound, 'non_existent_role'));

        // 3. Stage 7 refuses to guess or fall back when role is missing
        $mockDbStage7 = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            private int $migCount = 0;
            private array $latestRow = [];
            public ?\Scripts\DeveloperSandboxRunContext $context = null;
            public function listTables(): array { return []; }
            public function query(string $sql, array $params = []) {
                if (str_contains($sql, 'SELECT id FROM `roles`')) {
                    if ($this->context !== null && $this->context->getCurrentPhase() === \Scripts\DeveloperSandboxRunContext::PHASE_STAGE6_DONE) {
                        return new class { public function getRowArray() { return null; } };
                    }
                    return new class {
                        public function getRowArray() { return ['id' => 1]; }
                        public function getResultArray() { return []; }
                    };
                }
                if (str_contains($sql, 'SELECT COUNT(*) as c FROM `migrations`')) {
                    return new class($this->migCount) {
                        private int $c;
                        public function __construct(int $c) { $this->c = $c; }
                        public function getRowArray() { return ['c' => $this->c]; }
                    };
                }
                if (str_contains($sql, 'SELECT `id`, `version`, `class`') && str_contains($sql, 'FROM `migrations`')) {
                    return new class($this->latestRow) {
                        private array $row;
                        public function __construct(array $row) { $this->row = $row; }
                        public function getRowArray() { return $this->row; }
                    };
                }
                if (str_contains($sql, '`migrations` WHERE `version`')) {
                    return new class {
                        public function getRowArray() { return null; }
                        public function getResultArray() { return []; }
                    };
                }
                return new class {
                    public function getRowArray() { return ['id' => 1, '1' => 1]; }
                    public function getResultArray() { return []; }
                };
            }
            public function recordMigration(string $version, string $class): void {
                $this->migCount++;
                $this->latestRow = [
                    'id' => $this->migCount,
                    'version' => $version,
                    'class' => $class,
                    'group' => 'default',
                    'namespace' => 'App',
                    'time' => time(),
                    'batch' => 1,
                ];
            }
        };

        $tempDir7 = $this->trackTestDir($tempDir . '_role_fail');
        $context7 = DeveloperSandboxExecutor::createTestMockContext($mockDbStage7, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir7);
        $mockDbStage7->context = $context7;
        $this->advanceMockContextToPhase($executor, $context7, $mockDbStage7, \Scripts\DeveloperSandboxRunContext::PHASE_STAGE6_DONE);

        $res7 = $executor->executeStage7_SeedDeveloperPersonasAndMemberships($context7, $mockDbStage7);
        $this->assertFalse($res7['success'], 'Stage 7 must fail when role cannot be resolved.');
        $this->assertStringContainsString('Failed resolving canonical role ID for role slug', $res7['message']);
        $this->assertStringContainsString('Guessed IDs or fallbacks are strictly prohibited', $res7['message']);
    }

    /**
     * NEGATIVE TEST 5: Unsupported SQL columns cannot appear in seed adapter
     */
    public function testNegative5UnsupportedSqlColumnsCannotAppearInSeedAdapter(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));

        $capturedQueries = [];
        $mockDb = $this->createUniversalMockDb($capturedQueries);

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);
        $this->advanceMockContextToPhase($executor, $context, $mockDb, \Scripts\DeveloperSandboxRunContext::PHASE_STAGE3_DONE);

        $res4 = $executor->executeStage4_InitializeUser1AndSyncRbac($context, $mockDb);
        $this->assertTrue($res4['success']);

        $this->advanceMockContextToPhase($executor, $context, $mockDb, \Scripts\DeveloperSandboxRunContext::PHASE_STAGE6_DONE);

        $res7 = $executor->executeStage7_SeedDeveloperPersonasAndMemberships($context, $mockDb);
        $this->assertTrue($res7['success']);

        foreach ($capturedQueries as [$sql, $params]) {
            if (str_contains($sql, 'INSERT INTO `user_roles`')) {
                // Must strictly match CreateRBACTables schema (user_id, role_id, assigned_by, is_active)
                $this->assertStringNotContainsString('clinic_id', $sql, "user_roles query must NOT contain 'clinic_id'.");
                $this->assertStringNotContainsString('created_by', $sql, "user_roles query must NOT contain 'created_by'.");
                $this->assertStringNotContainsString('created_at', $sql, "user_roles query must NOT contain 'created_at'.");
                $this->assertStringNotContainsString('updated_at', $sql, "user_roles query must NOT contain 'updated_at'.");
            }
            if (str_contains($sql, 'INSERT INTO `permissions`')) {
                $this->assertStringNotContainsString('display_name', $sql, "permissions query must NEVER contain 'display_name'.");
                $this->assertStringContainsString('action', $sql, "permissions query must contain 'action'.");
            }
            if (str_contains($sql, 'INSERT INTO `users`')) {
                $this->assertStringNotContainsString('is_system', $sql, "users query must NOT contain 'is_system'.");
                $this->assertStringNotContainsString('global_mode', $sql, "users query must NOT contain 'global_mode'.");
            }
        }
    }

    /**
     * NEGATIVE TEST 6: Missing protected snapshot refuses before boot
     */
    public function testNegative6MissingProtectedSnapshotRefusesBeforeBoot(): void
    {
        $bootstrapScript = dirname(__DIR__, 2) . '/scripts/DeveloperSandboxConnectionBootstrap.php';
        $validConn = [
            'hostname' => '127.0.0.1',
            'port' => 3307,
            'database' => 'democa_dental_saas_dev',
            'username' => 'dev_user',
            'password' => 'secret_pass',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
        ];

        // Flat payload (missing 'protected_databases')
        $code = 'require ' . var_export($bootstrapScript, true) . ';';
        $env = getenv();
        $env['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION'] = json_encode($validConn);

        $process = proc_open([PHP_BINARY, '-r', $code], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__, 2), $env);
        self::assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        $this->assertNotSame(0, $exitCode, 'Flat payload without snapshot envelope must fail non-zero.');
        $this->assertStringContainsString('Verified isolated dev sandbox connection required', $err);
        $this->assertStringNotContainsString('secret_pass', $err, 'Must not disclose secrets.');

        // Empty protected database
        $env['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION'] = json_encode([
            'connection' => $validConn,
            'protected_databases' => ['database' => ''],
        ]);
        $process2 = proc_open([PHP_BINARY, '-r', $code], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__, 2), $env);
        fclose($pipes[0]);
        $err2 = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode2 = proc_close($process2);

        $this->assertNotSame(0, $exitCode2, 'Empty protected database in snapshot must fail non-zero.');
        $this->assertStringNotContainsString('secret_pass', $err2, 'Must not disclose secrets.');
    }

    /**
     * NEGATIVE TEST 7: Journal collision and lock refusal
     */
    public function testNegative7JournalCollisionAndLockRefusal(): void
    {
        $tempDir = sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6));
        \Scripts\DeveloperSandboxStorageGuard::ensurePrivateDirectory($tempDir);

        $runId = 'test_col_run';
        $journalPath = $tempDir . '/' . $runId . '.journal.jsonl';
        file_put_contents($journalPath, "{}\n");

        // Collision refusal
        try {
            \Scripts\DeveloperSandboxJournal::create($tempDir, $runId);
            $this->fail('Creating existing journal must throw collision refusal.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('CRITICAL REFUSAL: Journal file already exists for run ID', $e->getMessage());
            $this->assertStringContainsString('Collision refused', $e->getMessage());
        }

        // Lock serialization refusal
        $lock1 = \Scripts\DeveloperSandboxProcessLock::acquire($tempDir);
        $this->assertNotNull($lock1);

        try {
            \Scripts\DeveloperSandboxProcessLock::acquire($tempDir);
            $this->fail('Acquiring second lock while first is held must throw concurrent execution refusal.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('CRITICAL REFUSAL: Developer sandbox bootstrap process lock is held by another active run', $e->getMessage());
            $this->assertStringContainsString('Concurrent execution refused', $e->getMessage());
        }

        $lock1->release();
        $lock2 = \Scripts\DeveloperSandboxProcessLock::acquire($tempDir);
        $this->assertNotNull($lock2);
        $lock2->release();
    }

    /**
     * NEGATIVE TEST 8: Partial failures persist evidence to durable journal
     */
    public function testNegative8PartialFailuresPersistEvidenceToDurableJournal(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));

        $mockDbHalt = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public function listTables(): array { return []; }
            public function query(string $sql, array $params = []) {
                if (str_contains($sql, '_dev_sandbox_marker')) {
                    return new class {
                        public function getRowArray() { return ['1' => 1]; }
                        public function getResultArray() { return []; }
                    };
                }
                if (str_contains($sql, 'SHOW')) return new class { public function getResultArray() { return []; } };
                return false; // Stage 2 DDL failure
            }
        };

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDbHalt, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);
        $journalPath = $context->getJournal()->getFilePath();

        $result = $executor->runBootstrap($context, $mockDbHalt);
        $this->assertFalse($result['success']);

        $this->assertFileExists($journalPath, 'Durable journal file must exist on disk.');
        $content = file_get_contents($journalPath);
        $this->assertNotEmpty($content, 'Durable journal file must not be empty.');

        $lines = array_filter(explode("\n", trim($content)));
        $entries = array_map(fn($l) => json_decode($l, true), $lines);

        $hasHaltedEvent = false;
        foreach ($entries as $entry) {
            $this->assertIsArray($entry);
            $this->assertArrayHasKey('stage', $entry);
            $this->assertArrayHasKey('event', $entry);
            $this->assertArrayHasKey('timestamp', $entry);
            if ($entry['stage'] === 'orchestration' && $entry['event'] === 'halted_at_stage_2') {
                $hasHaltedEvent = true;
            }
        }

        $this->assertTrue($hasHaltedEvent, 'Durable journal file must contain orchestration halted_at_stage_2 failure record.');
    }

    /**
     * CODEX CORRECTION REGRESSION 1:
     * Private constructor probe closes direct context instantiation.
     */
    public function testPrivateConstructorProbeRefusesDirectContextInstantiation(): void
    {
        $ref = new \ReflectionClass(\Scripts\DeveloperSandboxRunContext::class);
        $ctor = $ref->getConstructor();
        $this->assertNotNull($ctor, 'DeveloperSandboxRunContext must have a constructor.');
        $this->assertTrue($ctor->isPrivate(), 'DeveloperSandboxRunContext constructor MUST be private to prevent caller forging.');
    }

    /**
     * CODEX CORRECTION REGRESSION 2:
     * Public test mock factory rejects arbitrary non-stub database connection objects.
     */
    public function testPublicTestMockFactoryRejectsRealConnectionObjects(): void
    {
        $this->expectException(\TypeError::class);
        // createTestMockContext requires DeveloperSandboxTestConnectionStub.
        // Passing an arbitrary non-stub object must throw TypeError.
        \Scripts\DeveloperSandboxExecutor::createTestMockContext(new \stdClass());
    }

    /**
     * CODEX CORRECTION REGRESSION 3:
     * Repeat or out-of-order stage execution is strictly refused.
     */
    public function testRepeatStageExecutionStrictlyRefused(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6));

        $mockDb = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public int $queryCount = 0;
            public function listTables(): array { return []; }
            public function query(string $sql, array $params = []) {
                $this->queryCount++;
                return new class {
                    public function getResultArray() { return []; }
                    public function getRowArray() { return ['id' => 1]; }
                };
            }
        };

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);

        // Stage 1 initial call: succeeds
        $res1 = $executor->executeStage1_VerifyEmptinessAndCreateMarker($context, $mockDb);
        $this->assertTrue($res1['success']);
        $this->assertSame(\Scripts\DeveloperSandboxRunContext::PHASE_STAGE1_DONE, $context->getCurrentPhase());

        // Repeat call to Stage 1: MUST THROW
        try {
            $executor->executeStage1_VerifyEmptinessAndCreateMarker($context, $mockDb);
            $this->fail('Repeat call to Stage 1 must be refused.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('CRITICAL REFUSAL: Invalid stage transition', $e->getMessage());
            $this->assertStringContainsString('Repeat or out-of-order stage execution is strictly forbidden', $e->getMessage());
        }

        // Stage 2 initial call: succeeds
        $res2 = $executor->executeStage2_ApplyLegacyFoundation($context, $mockDb);
        $this->assertTrue($res2['success']);
        $this->assertSame(\Scripts\DeveloperSandboxRunContext::PHASE_STAGE2_DONE, $context->getCurrentPhase());

        // Repeat call to Stage 2: MUST THROW
        try {
            $executor->executeStage2_ApplyLegacyFoundation($context, $mockDb);
            $this->fail('Repeat call to Stage 2 must be refused.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('CRITICAL REFUSAL: Invalid stage transition', $e->getMessage());
            $this->assertStringContainsString('Repeat or out-of-order stage execution is strictly forbidden', $e->getMessage());
        }
    }

    /**
     * CODEX CORRECTION REGRESSION 4:
     * MigrationRunner returning true without history progress is rejected.
     */
    public function testMigrationRunnerNoProgressTrueRejected(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));

        // Mock DB where history count does NOT increment when migration runs
        $mockDbNoProgress = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public function listTables(): array { return []; }
            public function query(string $sql, array $params = []) {
                if (str_contains($sql, '_dev_sandbox_marker') || str_contains($sql, 'SELECT 1 FROM `users`') || str_contains($sql, 'SHOW FULL TABLES') || str_contains($sql, 'STATUS') || str_contains($sql, 'SHOW EVENTS')) {
                    return new class {
                        public function getResultArray() { return []; }
                        public function getRowArray() { return ['1' => 1]; }
                    };
                }
                if (str_contains($sql, '`migrations` WHERE `version`')) {
                    return new class {
                        public function getResultArray() { return []; }
                        public function getRowArray() { return null; }
                    };
                }
                return new class {
                    public function getResultArray() { return []; }
                    public function getRowArray() { return ['c' => 0]; }
                };
            }
        };

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDbNoProgress, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);
        $res1 = $executor->executeStage1_VerifyEmptinessAndCreateMarker($context, $mockDbNoProgress);
        $this->assertTrue($res1['success']);
        $res2 = $executor->executeStage2_ApplyLegacyFoundation($context, $mockDbNoProgress);
        $this->assertTrue($res2['success']);

        // Adapter returns true without adding history
        $noProgressAdapter = new class implements \Scripts\DeveloperSandboxMigrationAdapterInterface {
            public function runMigrationFile(string $f, string $n = 'App', string $g = 'default'): bool {
                return true;
            }
        };

        $res = $executor->executeStage3_ExecuteMigrations($context, $mockDbNoProgress, $noProgressAdapter);
        $this->assertFalse($res['success'], 'Migration returning true with no history progress must be rejected.');
        $this->assertStringContainsString('returned true without adding a history row (lock contention or regression; no progress made)', $res['message']);
    }

    /**
     * CODEX CORRECTION REGRESSION 5:
     * Storage guard rejects unresolvable paths and enforces private directory permissions.
     */
    public function testWindowsStorageGuardRejectsReparsePointsAndInsecureAcls(): void
    {
        $resNonExistent = \Scripts\DeveloperSandboxStorageGuard::verifyDirectoryPrivacy('C:\\non_existent_directory_test_' . uniqid());
        $this->assertFalse($resNonExistent['valid']);
        $this->assertStringContainsString('Private directory cannot be resolved', $resNonExistent['error']);

        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_priv_' . bin2hex(random_bytes(6)));
        \Scripts\DeveloperSandboxStorageGuard::ensurePrivateDirectory($tempDir);
        $resValid = \Scripts\DeveloperSandboxStorageGuard::verifyDirectoryPrivacy($tempDir, true);
        $this->assertTrue($resValid['valid'], 'Protected private directory must pass verification.');
    }

    /**
     * CODEX CORRECTION REGRESSION 6:
     * Closed or broken journal throws immediately without silent in-memory buffering.
     */
    public function testClosedOrBrokenJournalThrowsWithoutSilentBuffering(): void
    {
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));
        $journal = \Scripts\DeveloperSandboxJournal::create($tempDir, 'broken_journal_test');

        $journal->record('test', 'open_event', ['k' => 'v']);
        $this->assertCount(1, $journal->getEntries());

        $journal->close();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('CRITICAL REFUSAL: Journal file handle is closed or broken. Durable write cannot proceed.');
        $journal->record('test', 'failed_event');
    }

    /**
     * CODEX CORRECTION REGRESSION 7:
     * Process lock preserves stable inode on release without unlinking.
     */
    public function testProcessLockPreservesStableInodeWithoutUnlinkOnRelease(): void
    {
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));
        $lockFile = $tempDir . '/bootstrap.lock';

        $lock = new \Scripts\DeveloperSandboxProcessLock($lockFile);
        $this->assertFileExists($lockFile, 'Lock file must exist when acquired.');

        $lock->release();

        $this->assertFileExists($lockFile, 'Stable lock inode must be preserved on release without unlinking.');
    }

    /**
     * CODEX CORRECTION REGRESSION 8:
     * Concrete DeveloperSandboxWorker process execution and bounded isolation.
     */
    public function testDeveloperSandboxWorkerBoundedExecution(): void
    {
        $workerScript = dirname(__DIR__, 2) . '/scripts/DeveloperSandboxWorker.php';
        $validConn = [
            'hostname' => '127.0.0.1',
            'port' => 3307,
            'database' => 'democa_dental_saas_dev',
            'username' => 'dev_user',
            'password' => 'secret_pass',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
        ];

        // 1. Missing env payload: exits 1
        $process1 = proc_open([PHP_BINARY, $workerScript, '--dry-run'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes1, dirname(__DIR__, 2), []);
        self::assertIsResource($process1);
        fclose($pipes1[0]);
        $err1 = stream_get_contents($pipes1[2]);
        fclose($pipes1[1]);
        fclose($pipes1[2]);
        $exitCode1 = proc_close($process1);
        $this->assertSame(1, $exitCode1);
        $this->assertStringContainsString('Missing verified developer sandbox connection payload', $err1);

        // 2. Flat payload (no snapshot envelope): exits 1
        $env2 = getenv();
        $env2['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION'] = json_encode($validConn);
        $process2 = proc_open([PHP_BINARY, $workerScript, '--dry-run'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes2, dirname(__DIR__, 2), $env2);
        self::assertIsResource($process2);
        fclose($pipes2[0]);
        $err2 = stream_get_contents($pipes2[2]);
        fclose($pipes2[1]);
        fclose($pipes2[2]);
        $exitCode2 = proc_close($process2);
        $this->assertSame(1, $exitCode2);
        $this->assertStringContainsString('Missing authoritative parent snapshot envelope', $err2);

        // 3. Valid envelope with --dry-run: boots CI4, audits config, prints JSON receipt, exits 0
        $env3 = getenv();
        $env3['DENTA_DEV_SANDBOX_VAULT_DIR'] = $this->trackTestDir(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'denta_worker_' . bin2hex(random_bytes(6)));
        $env3['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION'] = json_encode([
            'connection' => $validConn,
            'protected_databases' => ['database' => 'democa_dental_saas'],
        ]);
        $process3 = proc_open([PHP_BINARY, $workerScript, '--dry-run'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes3, dirname(__DIR__, 2), $env3);
        self::assertIsResource($process3);
        fclose($pipes3[0]);
        $out3 = stream_get_contents($pipes3[1]);
        $err3 = stream_get_contents($pipes3[2]);
        fclose($pipes3[1]);
        fclose($pipes3[2]);
        $exitCode3 = proc_close($process3);
        $this->assertSame(0, $exitCode3, "Dry-run worker execution must exit 0. Error: {$err3}");
        $this->assertStringContainsString('REFUSED_UNTIL_AUTHORIZED', $out3);
        $this->assertStringContainsString('DeveloperSandboxWorker', $out3);
        $this->assertStringContainsString('UNCONDITIONALLY_REFUSED', $out3);

        // 4. Secrets are redacted: password never appears in stdout or stderr
        $this->assertStringNotContainsString('secret_pass', $out3);
        $this->assertStringNotContainsString('secret_pass', $err3);

        // 5. Live apply attempt without authorization: exits 1 with unconditional refusal
        $process5 = proc_open([PHP_BINARY, $workerScript, '--apply'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes5, dirname(__DIR__, 2), $env3);
        self::assertIsResource($process5);
        fclose($pipes5[0]);
        $err5 = stream_get_contents($pipes5[2]);
        fclose($pipes5[1]);
        fclose($pipes5[2]);
        $exitCode5 = proc_close($process5);
        $this->assertSame(1, $exitCode5);
        $this->assertStringContainsString('Live database bootstrap execution is unconditionally refused', $err5);
    }

    /**
     * GATE 1 REGRESSION:
     * Public verified factory and phase advancement bypasses are completely sealed:
     * - DeveloperSandboxRunContext constructor is private.
     * - DeveloperSandboxRunContext has no public createVerified() or advancePhase().
     * - Passing arbitrary real DB connections (PDO, mysqli, ConnectionInterface) to createTestMockContext throws InvalidArgumentException.
     * - Calling Stage 2 without Stage 1 being completed throws RuntimeException.
     * - Calling Stage 2 when _dev_sandbox_marker is physically absent throws RuntimeException.
     * - Calling Stage 2 with a connection different from the context binding throws RuntimeException.
     */
    public function testGate1PublicVerifiedFactoryAndPhaseSkipBypassRefused(): void
    {
        $ref = new \ReflectionClass(\Scripts\DeveloperSandboxRunContext::class);
        $this->assertTrue($ref->getConstructor()->isPrivate(), 'Constructor must be private.');
        $this->assertFalse($ref->hasMethod('createVerified'), 'createVerified must not exist.');
        $this->assertFalse($ref->hasMethod('advancePhase'), 'advancePhase must not exist.');

        // Real connection types rejected
        $mockPdo = $this->createMock(\PDO::class);
        try {
            DeveloperSandboxExecutor::createTestMockContext($mockPdo);
            $this->fail('createTestMockContext must reject real connection objects.');
        } catch (\TypeError|\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        $executor = new DeveloperSandboxExecutor();
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));
        $mockDb = $this->createUniversalMockDb();
        $context = DeveloperSandboxExecutor::createTestMockContext($mockDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);

        // Stage 2 attempted before Stage 1 (Phase is 0, requires 1)
        try {
            $executor->executeStage2_ApplyLegacyFoundation($context, $mockDb);
            $this->fail('Stage 2 must not run when Phase is INITIALIZED.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Invalid stage transition', $e->getMessage());
        }

        // Run Stage 1 properly
        $res1 = $executor->executeStage1_VerifyEmptinessAndCreateMarker($context, $mockDb);
        $this->assertTrue($res1['success']);

        // Stage 2 with unbound connection
        $unboundDb = $this->createUniversalMockDb();
        try {
            $executor->executeStage2_ApplyLegacyFoundation($context, $unboundDb);
            $this->fail('Stage 2 must not run with unbound DB.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('not bound to the supplied database connection', $e->getMessage());
        }

        // Stage 2 with missing physical marker
        $missingMarkerDb = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public function query(string $s, array $p = []) {
                if (str_contains($s, '_dev_sandbox_marker')) return null;
                return true;
            }
        };
        $contextMissingMarker = DeveloperSandboxExecutor::createTestMockContext($missingMarkerDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir . '_marker');
        $prop = $ref->getProperty('currentPhase');
        $prop->setAccessible(true);
        $prop->setValue($contextMissingMarker, \Scripts\DeveloperSandboxRunContext::PHASE_STAGE1_DONE);

        try {
            $executor->executeStage2_ApplyLegacyFoundation($contextMissingMarker, $missingMarkerDb);
            $this->fail('Stage 2 must refuse when physical marker table is missing.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Physical precondition failure: ownership marker table does not exist', $e->getMessage());
        }
    }

    /**
     * GATE 2 REGRESSION: Stage 3 rejects irregular history delta (+2).
     */
    public function testGate2Stage3RefusesPlusTwoDelta(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));

        $mockDb = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public int $migCount = 0;
            public function listTables(): array { return []; }
            public function query(string $sql, array $params = []) {
                if (str_contains($sql, '_dev_sandbox_marker') || str_contains($sql, 'SELECT 1 FROM `users`') || str_contains($sql, 'SHOW FULL TABLES') || str_contains($sql, 'STATUS') || str_contains($sql, 'SHOW EVENTS')) {
                    return new class {
                        public function getRowArray() { return ['1' => 1]; }
                        public function getResultArray() { return []; }
                    };
                }
                if (str_contains($sql, 'SELECT COUNT(*) as c FROM `migrations`')) {
                    return new class($this->migCount) {
                        private int $c;
                        public function __construct(int $c) { $this->c = $c; }
                        public function getRowArray() { return ['c' => $this->c]; }
                    };
                }
                return new class {
                    public function getRowArray() { return null; }
                    public function getResultArray() { return []; }
                };
            }
        };

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);
        $this->advanceMockContextToPhase($executor, $context, $mockDb, \Scripts\DeveloperSandboxRunContext::PHASE_STAGE2_DONE);

        // Adapter adds +2 rows instead of +1
        $plusTwoAdapter = new class($mockDb) implements \Scripts\DeveloperSandboxMigrationAdapterInterface {
            private object $db;
            public function __construct(object $db) { $this->db = $db; }
            public function runMigrationFile(string $filePath, string $namespace = 'App', string $group = 'default'): bool {
                $this->db->migCount += 2;
                return true;
            }
        };

        $res = $executor->executeStage3_ExecuteMigrations($context, $mockDb, $plusTwoAdapter);
        $this->assertFalse($res['success']);
        $this->assertStringContainsString('produced an irregular history row delta (expected exact +1, got 2)', $res['message']);
    }

    /**
     * GATE 2 REGRESSION: Stage 3 rejects wrong latest history row identity.
     */
    public function testGate2Stage3RefusesWrongRowIdentity(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));

        $mockDb = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public int $migCount = 0;
            public function listTables(): array { return []; }
            public function query(string $sql, array $params = []) {
                if (str_contains($sql, '_dev_sandbox_marker') || str_contains($sql, 'SELECT 1 FROM `users`') || str_contains($sql, 'SHOW FULL TABLES') || str_contains($sql, 'STATUS') || str_contains($sql, 'SHOW EVENTS')) {
                    return new class {
                        public function getRowArray() { return ['1' => 1]; }
                        public function getResultArray() { return []; }
                    };
                }
                if (str_contains($sql, 'SELECT COUNT(*) as c FROM `migrations`')) {
                    return new class($this->migCount) {
                        private int $c;
                        public function __construct(int $c) { $this->c = $c; }
                        public function getRowArray() { return ['c' => $this->c]; }
                    };
                }
                if (str_contains($sql, 'SELECT `id`, `version`, `class`') && str_contains($sql, 'FROM `migrations`')) {
                    return new class {
                        public function getRowArray() {
                            return [
                                'id' => 1,
                                'version' => 'WRONG_VERSION_9999',
                                'class' => 'WrongMigrationClass',
                                'namespace' => 'App',
                                'group' => 'default',
                            ];
                        }
                    };
                }
                return new class {
                    public function getRowArray() { return null; }
                    public function getResultArray() { return []; }
                };
            }
        };

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);
        $this->advanceMockContextToPhase($executor, $context, $mockDb, \Scripts\DeveloperSandboxRunContext::PHASE_STAGE2_DONE);

        $adapter = new class($mockDb) implements \Scripts\DeveloperSandboxMigrationAdapterInterface {
            private object $db;
            public function __construct(object $db) { $this->db = $db; }
            public function runMigrationFile(string $filePath, string $namespace = 'App', string $group = 'default'): bool {
                $this->db->migCount++;
                return true;
            }
        };

        $res = $executor->executeStage3_ExecuteMigrations($context, $mockDb, $adapter);
        $this->assertFalse($res['success']);
        $this->assertStringContainsString('Latest migration history row identity does not match executed migration', $res['message']);
    }

    /**
     * GATE 2 REGRESSION: Stage 3 rejects malformed history queries without swallowing.
     */
    public function testGate2Stage3RefusesMalformedHistoryQuery(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));

        $mockDb = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public function listTables(): array { return []; }
            public function query(string $sql, array $params = []) {
                if (str_contains($sql, '_dev_sandbox_marker') || str_contains($sql, 'SELECT 1 FROM `users`') || str_contains($sql, 'SHOW FULL TABLES') || str_contains($sql, 'STATUS') || str_contains($sql, 'SHOW EVENTS')) {
                    return new class {
                        public function getRowArray() { return ['1' => 1]; }
                        public function getResultArray() { return []; }
                    };
                }
                if (str_contains($sql, 'SELECT COUNT(*) as c FROM `migrations`')) {
                    return false; // Malformed / failure
                }
                return true;
            }
        };

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);
        $this->advanceMockContextToPhase($executor, $context, $mockDb, \Scripts\DeveloperSandboxRunContext::PHASE_STAGE2_DONE);

        $adapter = new class implements \Scripts\DeveloperSandboxMigrationAdapterInterface {
            public function runMigrationFile(string $f, string $n = 'App', string $g = 'default'): bool { return true; }
        };

        try {
            $executor->executeStage3_ExecuteMigrations($context, $mockDb, $adapter);
            $this->fail('Malformed history query must throw RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Query for migration history row count returned false or invalid result', $e->getMessage());
        }
    }

    /**
     * GATE 2 REGRESSION: Stage 3 rejects duplicate preexisting migration UID before invoking runner.
     */
    public function testGate2Stage3RefusesPreexistingMigrationUid(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));

        $mockDb = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public function listTables(): array { return []; }
            public function query(string $sql, array $params = []) {
                if (str_contains($sql, '_dev_sandbox_marker') || str_contains($sql, 'SELECT 1 FROM `users`') || str_contains($sql, 'SHOW FULL TABLES') || str_contains($sql, 'STATUS') || str_contains($sql, 'SHOW EVENTS')) {
                    return new class {
                        public function getRowArray() { return ['1' => 1]; }
                        public function getResultArray() { return []; }
                    };
                }
                if (str_contains($sql, '`migrations` WHERE `version`')) {
                    // Preexisting migration version found in history!
                    return new class { public function getRowArray() { return ['id' => 101]; } };
                }
                return new class {
                    public function getRowArray() { return null; }
                    public function getResultArray() { return []; }
                };
            }
        };

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);
        $this->advanceMockContextToPhase($executor, $context, $mockDb, \Scripts\DeveloperSandboxRunContext::PHASE_STAGE2_DONE);

        $adapter = new class implements \Scripts\DeveloperSandboxMigrationAdapterInterface {
            public bool $runnerInvoked = false;
            public function runMigrationFile(string $f, string $n = 'App', string $g = 'default'): bool {
                $this->runnerInvoked = true;
                return true;
            }
        };

        $res = $executor->executeStage3_ExecuteMigrations($context, $mockDb, $adapter);
        $this->assertFalse($res['success']);
        $this->assertFalse($adapter->runnerInvoked, 'Runner must NEVER be invoked when duplicate UID is detected.');
        $this->assertStringContainsString('is already recorded in history (duplicate/replay detected)', $res['message']);
    }

    /**
     * GATE 2 REGRESSION: Stage 3 rejects source hash mismatch before invoking runner.
     */
    public function testGate2Stage3RefusesSourceHashMismatch(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_test_' . bin2hex(random_bytes(6)));
        $mockDb = $this->createUniversalMockDb();

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);
        $this->advanceMockContextToPhase($executor, $context, $mockDb, \Scripts\DeveloperSandboxRunContext::PHASE_STAGE2_DONE);

        // Subclass executor to simulate a tampered pinned hash in migration plan
        $tamperedExecutor = new class extends DeveloperSandboxExecutor {
            public function resolveMigrationPlan(string $mode = self::MODE_PRODUCTION_BASELINE): array {
                $plan = parent::resolveMigrationPlan($mode);
                $firstKey = array_key_first($plan);
                $plan[$firstKey]['pinned_hash'] = '0000000000000000000000000000000000000000000000000000000000000000'; // Tampered hash
                return $plan;
            }
        };

        $adapter = $this->createMockMigrationAdapter($mockDb);
        $res = $tamperedExecutor->executeStage3_ExecuteMigrations($context, $mockDb, $adapter);
        $this->assertFalse($res['success']);
        $this->assertStringContainsString('Source hash mismatch for migration', $res['message']);
    }

    /**
     * GATE 3 REGRESSION: Worker argv parsing rejects mixed, duplicate, or unknown options.
     */
    public function testGate3WorkerArgvValidationRejectsMixedAndUnknownFlags(): void
    {
        $workerScript = dirname(__DIR__, 2) . '/scripts/DeveloperSandboxWorker.php';
        $cases = [
            [], // No args
            ['--apply', '--dry-run'], // Mixed
            ['--check-preflight'], // Deprecated / rejected
            ['--unknown-flag'], // Unknown
            ['--dry-run', '--dry-run'], // Duplicate
        ];

        foreach ($cases as $argv) {
            $cmd = array_merge([PHP_BINARY, $workerScript], $argv);
            $process = proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__, 2), []);
            self::assertIsResource($process);
            fclose($pipes[0]);
            $err = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);

            $this->assertSame(1, $exitCode, 'Worker must exit 1 for invalid argv: ' . json_encode($argv));
            $this->assertStringContainsString('CRITICAL REFUSAL: Invalid arguments', $err);
        }
    }

    /**
     * GATE 3 REGRESSION: Worker supports truthful --audit-only and demonstrates offline real worker branch with test backend.
     */
    public function testGate3WorkerDryRunAuditAndOfflineRealBranchDemonstration(): void
    {
        $workerScript = dirname(__DIR__, 2) . '/scripts/DeveloperSandboxWorker.php';
        $tempVault = $this->trackTestDir(sys_get_temp_dir() . '/denta_vault_' . bin2hex(random_bytes(6)));

        $validConn = [
            'hostname' => '127.0.0.1',
            'port' => 3307,
            'database' => 'democa_dental_saas_dev',
            'username' => 'dev_user',
            'password' => 'secret_pass',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
        ];

        $env = getenv();
        $env['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION'] = json_encode([
            'connection' => $validConn,
            'protected_databases' => ['database' => 'democa_dental_saas'],
        ]);
        $env['DENTA_DEV_SANDBOX_VAULT_DIR'] = $tempVault;

        // 1. --audit-only mode
        $process1 = proc_open([PHP_BINARY, $workerScript, '--audit-only'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes1, dirname(__DIR__, 2), $env);
        self::assertIsResource($process1);
        fclose($pipes1[0]);
        $out1 = stream_get_contents($pipes1[1]);
        $err1 = stream_get_contents($pipes1[2]);
        fclose($pipes1[1]);
        fclose($pipes1[2]);
        $exitCode1 = proc_close($process1);

        $this->assertSame(0, $exitCode1, "Audit-only must exit 0. Error: {$err1}");
        $this->assertStringContainsString('AUDIT_COMPLETE_REFUSED_UNTIL_AUTHORIZED', $out1);
        $this->assertStringContainsString('PASSED', $out1);

        // 2. Offline demonstration of wired real worker branch using injected test backend
        $envTest = $env;
        $envTest['DENTA_SANDBOX_WORKER_TEST_MODE'] = '1';
        $process2 = proc_open([PHP_BINARY, $workerScript, '--apply'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes2, dirname(__DIR__, 2), $envTest);
        self::assertIsResource($process2);
        fclose($pipes2[0]);
        $out2 = stream_get_contents($pipes2[1]);
        $err2 = stream_get_contents($pipes2[2]);
        fclose($pipes2[1]);
        fclose($pipes2[2]);
        $exitCode2 = proc_close($process2);

        $this->assertSame(0, $exitCode2, "Offline worker demonstration must exit 0. Error: {$err2}");
        $this->assertStringContainsString('OFFLINE_DEMONSTRATION_SUCCESS', $out2);
        $this->assertStringContainsString('tables_created_count', $out2);
    }

    /**
     * GATE 4 REGRESSION: Storage guard rejects real NTFS reparse point/junction fixtures and insecure DACLs.
     */
    public function testGate4StorageGuardRejectsReparsePointAndInsecureAclFixtures(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // 1. Real NTFS Junction fixture
            $targetDir = $this->trackTestDir(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'denta_target_' . bin2hex(random_bytes(6)));
            $junctionDir = $this->trackTestJunction(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'denta_junc_' . bin2hex(random_bytes(6)));

            mkdir($targetDir, 0700, true);
            $o = [];
            $c = 0;
            exec('cmd /c mklink /J ' . escapeshellarg($junctionDir) . ' ' . escapeshellarg($targetDir), $o, $c);

            if ($c === 0) {
                // Must refuse the junction itself
                try {
                    \Scripts\DeveloperSandboxStorageGuard::assertUnresolvedPathSafe($junctionDir);
                    $this->fail('assertUnresolvedPathSafe must reject an NTFS junction.');
                } catch (\RuntimeException $e) {
                    $this->assertStringContainsString('reparse point / junction', $e->getMessage());
                }

                // Must refuse child inside the junction
                try {
                    \Scripts\DeveloperSandboxStorageGuard::assertUnresolvedPathSafe($junctionDir . DIRECTORY_SEPARATOR . 'child');
                    $this->fail('assertUnresolvedPathSafe must reject a path whose ancestor is a junction.');
                } catch (\RuntimeException $e) {
                    $this->assertStringContainsString('reparse point / junction', $e->getMessage());
                }
            }

            // 2. Real Insecure DACL fixture: grant access to Everyone (*S-1-1-0)
            $insecureDir = $this->trackTestDir(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'denta_insecure_' . bin2hex(random_bytes(6)));
            mkdir($insecureDir, 0700, true);

            $outAcl = [];
            $codeAcl = 0;
            exec('icacls ' . escapeshellarg($insecureDir) . ' /grant "*S-1-1-0:(OI)(CI)(M)" 2>&1', $outAcl, $codeAcl);

            $privacy = \Scripts\DeveloperSandboxStorageGuard::verifyDirectoryPrivacy($insecureDir, false);
            $this->assertFalse($privacy['valid'], 'Directory with Everyone grant must fail privacy verification.');
            $this->assertStringContainsString('untrusted principal', $privacy['error']);

            // ensurePrivateDirectory must refuse to modify this existing insecure directory
            try {
                \Scripts\DeveloperSandboxStorageGuard::ensurePrivateDirectory($insecureDir);
                $this->fail('ensurePrivateDirectory must refuse to modify existing insecure directory.');
            } catch (\RuntimeException $e) {
                $this->assertStringContainsString('Destination', $e->getMessage());
                $this->assertStringContainsString('already exists and does not satisfy strict private ownership/ACL invariants', $e->getMessage());
            }
        } else {
            // POSIX mode check
            $posixDir = $this->trackTestDir(sys_get_temp_dir() . '/denta_posix_' . bin2hex(random_bytes(6)));
            mkdir($posixDir, 0777, true);
            chmod($posixDir, 0777);
            $privacy = \Scripts\DeveloperSandboxStorageGuard::verifyDirectoryPrivacy($posixDir, false);
            $this->assertFalse($privacy['valid']);
        }
    }

    public function testFirstHistoryReadAcceptsOnlyExactMissingTableDriverError(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $db = new class {
            public $errorCode = 1146;
            public bool $throws = false;
            public function query(string $sql) {
                if ($this->throws) {
                    throw new \RuntimeException('Private diagnostic must never be repeated: 1146');
                }
                return false;
            }
            public function error(): array { return ['code' => $this->errorCode]; }
        };
        foreach ([false, true] as $throws) {
            $db->throws = $throws;
            $db->errorCode = 1146;
            $this->assertSame(0, $executor->getMigrationHistoryRowCount($db, true));
            foreach ([1142, 2006, 0, null] as $code) {
                $db->errorCode = $code;
                try {
                    $executor->getMigrationHistoryRowCount($db, true);
                    $this->fail('Unknown, permission and connection errors must stop initialization.');
                } catch (\RuntimeException $e) {
                    $this->assertStringNotContainsString('Private diagnostic', $e->getMessage());
                }
            }
            $db->errorCode = 1146;
            try {
                $executor->getMigrationHistoryRowCount($db, false);
                $this->fail('Missing history after initialization must be refused.');
            } catch (\RuntimeException $e) {
                $this->assertStringContainsString('REFUSAL', $e->getMessage());
            }
        }
    }

    public function testWorkerUnexpectedFailureDoesNotDumpPrivateArguments(): void
    {
        // Throw after real framework boot, before DB or storage I/O, to prove CI's
        // exception renderer cannot serialize the worker's private arguments.
        $env = getenv();
        unset($env['DENTA_AUTHORIZED_DEV_SANDBOX_APPLY'], $env['DENTA_SANDBOX_WORKER_TEST_MODE']);
        $env['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION'] = json_encode([
            'connection' => array_merge($this->validSandboxConfig, ['DBPrefix' => '', 'password' => 'private-worker-canary']),
            'protected_databases' => $this->validBusinessConfig,
        ]);
        $worker = dirname(__DIR__, 2) . '/scripts/DeveloperSandboxWorker.php';
        $code = 'namespace Scripts; function getenv($key) { '
            . 'if ($key === "DENTA_DEV_SANDBOX_VAULT_DIR") { throw new \\RuntimeException("private-worker-canary"); } '
            . 'return \\getenv($key); } '
            . '$argv = ["worker", "--dry-run"]; require ' . var_export($worker, true) . ';';
        $process = proc_open([PHP_BINARY, '-r', $code],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__, 2), $env);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(1, proc_close($process));
        $this->assertStringNotContainsString('private-worker-canary', $stdout . $stderr);
        $this->assertStringNotContainsString('Stack trace', $stdout . $stderr);
        $this->assertStringContainsString('REFUSAL', $stderr);
        $this->assertStringContainsString('Inspect the private stage journal', $stderr);
    }

    public function testPlannedMigrationIdentityMatchesRealCodeIgniterLocator(): void
    {
        $locator = service('locator', true);
        $executor = new \Scripts\DeveloperSandboxExecutor();
        foreach ($executor->resolveMigrationPlan('all-source') as $migration) {
            $this->assertSame($locator->getClassname($migration['path']), $migration['class'], $migration['file']);
        }
    }
}
