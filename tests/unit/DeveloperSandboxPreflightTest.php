<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/scripts/DeveloperSandboxPreflight.php';

use Scripts\DeveloperSandboxPreflight;

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
}
