<?php
declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

require_once dirname(__DIR__, 2) . '/scripts/TestDatabasePreflight.php';

use Scripts\TestDatabasePreflight;

/**
 * Unit Tests for Test Database Preflight Safeguard Policy
 *
 * Verifies that TestDatabasePreflight fails closed, refuses remote hosts,
 * refuses non-MySQLi drivers, refuses invalid ports, refuses business database
 * collisions WITHOUT connecting, and enforces ownership markers for resets.
 */
final class SafeTestIsolationPreflightTest extends CIUnitTestCase
{
    private array $validTestsConfig = [
        'hostname' => '127.0.0.1',
        'port' => 3306,
        'DBDriver' => 'MySQLi',
        'database' => 'democa_dental_saas_test',
        'username' => 'root',
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

    public function testPreflightPassesForApprovedConfiguration(): void
    {
        $errors = TestDatabasePreflight::validateConfigBeforeConnection(
            $this->validTestsConfig,
            $this->validBusinessConfig
        );

        $this->assertEmpty($errors, 'Approved configuration should produce zero preflight errors.');
    }

    public function testPreflightRefusesRemoteHostWithoutConnecting(): void
    {
        $dangerousConfig = $this->validTestsConfig;
        $dangerousConfig['hostname'] = '192.168.1.100';

        $errors = TestDatabasePreflight::validateConfigBeforeConnection(
            $dangerousConfig,
            $this->validBusinessConfig
        );

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Remote targets are strictly forbidden', $errors[0]);
    }

    public function testPreflightRefusesPublicDomainWithoutConnecting(): void
    {
        $dangerousConfig = $this->validTestsConfig;
        $dangerousConfig['hostname'] = 'db.production.example.com';

        $errors = TestDatabasePreflight::validateConfigBeforeConnection(
            $dangerousConfig,
            $this->validBusinessConfig
        );

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Remote targets are strictly forbidden', $errors[0]);
    }

    public function testPreflightRefusesNonApprovedPortWithoutConnecting(): void
    {
        $badPortConfig = $this->validTestsConfig;
        $badPortConfig['port'] = 5432;

        $errors = TestDatabasePreflight::validateConfigBeforeConnection(
            $badPortConfig,
            $this->validBusinessConfig
        );

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('not an approved local MySQL port', $errors[0]);
    }

    public function testPreflightRefusesNonMySQLiDriverWithoutConnecting(): void
    {
        $badDriverConfig = $this->validTestsConfig;
        $badDriverConfig['DBDriver'] = 'Postgre';

        $errors = TestDatabasePreflight::validateConfigBeforeConnection(
            $badDriverConfig,
            $this->validBusinessConfig
        );

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Expected MySQLi', $errors[0]);
    }

    public function testPreflightRefusesBusinessDatabaseCollisionWithoutConnecting(): void
    {
        $collisionConfig = $this->validTestsConfig;
        $collisionConfig['database'] = 'democa_dental_saas';

        $errors = TestDatabasePreflight::validateConfigBeforeConnection(
            $collisionConfig,
            $this->validBusinessConfig
        );

        $this->assertNotEmpty($errors);
        $joined = implode('; ', $errors);
        $this->assertStringContainsString('CRITICAL ISOLATION FAILURE', $joined);
        $this->assertStringContainsString('equals default business database', $joined);
    }

    public function testPreflightRefusesWhenBusinessDatabaseMatchesTestDatabaseWithoutConnecting(): void
    {
        $badBusinessConfig = $this->validBusinessConfig;
        $badBusinessConfig['database'] = 'democa_dental_saas_test';

        $errors = TestDatabasePreflight::validateConfigBeforeConnection(
            $this->validTestsConfig,
            $badBusinessConfig
        );

        $this->assertNotEmpty($errors);
        $joined = implode('; ', $errors);
        $this->assertStringContainsString('CRITICAL ISOLATION FAILURE', $joined);
        $this->assertStringContainsString('default business database is configured as', $joined);
    }

    public function testPreflightRefusesNonApprovedTestDatabaseNameWithoutConnecting(): void
    {
        $unapprovedDbConfig = $this->validTestsConfig;
        $unapprovedDbConfig['database'] = 'other_disposable_db';

        $errors = TestDatabasePreflight::validateConfigBeforeConnection(
            $unapprovedDbConfig,
            $this->validBusinessConfig
        );

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('does not match approved disposable target', $errors[0]);
    }

    public function testPreflightRefusesFailoverWithoutConnecting(): void
    {
        $dangerousConfig = $this->validTestsConfig;
        $dangerousConfig['failover'] = [
            [
                'hostname' => 'db.example.invalid',
                'database' => 'democa_dental_saas_test',
                'port' => 3306,
            ],
        ];

        $errors = TestDatabasePreflight::validateConfigBeforeConnection(
            $dangerousConfig,
            $this->validBusinessConfig
        );

        $this->assertNotEmpty($errors);
        $joined = implode('; ', $errors);
        $this->assertStringContainsString("'failover' configuration is forbidden", $joined);
    }

    public function testPreflightRefusesDsnOverrideWithoutConnecting(): void
    {
        $dangerousConfig = $this->validTestsConfig;
        $dangerousConfig['DSN'] = 'mysql:host=remote.example.com;dbname=democa_dental_saas_test';

        $errors = TestDatabasePreflight::validateConfigBeforeConnection(
            $dangerousConfig,
            $this->validBusinessConfig
        );

        $this->assertNotEmpty($errors);
        $joined = implode('; ', $errors);
        $this->assertStringContainsString("'DSN' override is forbidden", $joined);
    }

    public function testPreflightRefusesSubdriverOverrideWithoutConnecting(): void
    {
        $dangerousConfig = $this->validTestsConfig;
        $dangerousConfig['subdriver'] = 'some_subdriver';

        $errors = TestDatabasePreflight::validateConfigBeforeConnection(
            $dangerousConfig,
            $this->validBusinessConfig
        );

        $this->assertNotEmpty($errors);
        $joined = implode('; ', $errors);
        $this->assertStringContainsString("'subdriver' override is forbidden", $joined);
    }

    public function testPreflightPassesWhenAlternateConnectionKeysAreEmpty(): void
    {
        $cleanConfig = $this->validTestsConfig;
        $cleanConfig['failover'] = [];
        $cleanConfig['DSN'] = '';
        $cleanConfig['subdriver'] = '';

        $errors = TestDatabasePreflight::validateConfigBeforeConnection(
            $cleanConfig,
            $this->validBusinessConfig
        );

        $this->assertEmpty($errors, 'Empty failover, DSN, and subdriver should produce no preflight errors.');
    }

    public function testVerifyOwnershipForResetRefusesWhenMarkerTableMissing(): void
    {
        $mockDb = new class {
            public function listTables(): array
            {
                return ['users', 'patients', 'appointments'];
            }
        };

        $result = TestDatabasePreflight::verifyOwnershipForReset($mockDb);
        $this->assertFalse($result['allowed']);
        $this->assertStringContainsString('lacks the task ownership marker table', $result['reason']);
    }

    public function testVerifyOwnershipForResetAllowsEmptyDatabase(): void
    {
        $mockDb = new class {
            public function listTables(): array
            {
                return [];
            }
        };

        $result = TestDatabasePreflight::verifyOwnershipForReset($mockDb);
        $this->assertTrue($result['allowed']);
        $this->assertStringContainsString('Target database is completely empty', $result['reason']);
    }

    public function testVerifyOwnershipForResetRefusesEmptyMarkerTable(): void
    {
        $mockDb = new class {
            public function listTables(): array
            {
                return ['_test_db_marker', 'users'];
            }
            public function table(string $table)
            {
                return new class {
                    public function get()
                    {
                        return new class {
                            public function getRowArray(): ?array
                            {
                                return null;
                            }
                        };
                    }
                };
            }
        };

        $result = TestDatabasePreflight::verifyOwnershipForReset($mockDb);
        $this->assertFalse($result['allowed']);
        $this->assertStringContainsString('contains no records', $result['reason']);
    }

    public function testVerifyOwnershipForResetRefusesWrongTaskOwner(): void
    {
        $mockDb = $this->createMockDbWithMarker([
            'task_owner' => 'unauthorized_actor',
            'is_disposable' => 1,
            'target_db' => 'democa_dental_saas_test',
        ]);

        $result = TestDatabasePreflight::verifyOwnershipForReset($mockDb);
        $this->assertFalse($result['allowed']);
        $this->assertStringContainsString('task_owner', $result['reason']);
    }

    public function testVerifyOwnershipForResetRefusesNonDisposableMarker(): void
    {
        $mockDb = $this->createMockDbWithMarker([
            'task_owner' => 'safe_test_runner',
            'is_disposable' => 0,
            'target_db' => 'democa_dental_saas_test',
        ]);

        $result = TestDatabasePreflight::verifyOwnershipForReset($mockDb);
        $this->assertFalse($result['allowed']);
        $this->assertStringContainsString('is_disposable', $result['reason']);
    }

    public function testVerifyOwnershipForResetRefusesTargetDbMismatch(): void
    {
        $mockDb = $this->createMockDbWithMarker([
            'task_owner' => 'safe_test_runner',
            'is_disposable' => 1,
            'target_db' => 'democa_dental_saas', // business DB name in test marker
        ]);

        $result = TestDatabasePreflight::verifyOwnershipForReset($mockDb);
        $this->assertFalse($result['allowed']);
        $this->assertStringContainsString('target_db', $result['reason']);
    }

    public function testVerifyOwnershipForResetPassesValidMarker(): void
    {
        $mockDb = $this->createMockDbWithMarker([
            'task_owner' => 'safe_test_runner',
            'is_disposable' => 1,
            'target_db' => 'democa_dental_saas_test',
        ]);

        $result = TestDatabasePreflight::verifyOwnershipForReset($mockDb);
        $this->assertTrue($result['allowed']);
        $this->assertStringContainsString('confirmed by marker record', $result['reason']);
    }

    public function testInjectedFailureExitsNonzeroWithStderrMessage(): void
    {
        $scriptPath = dirname(__DIR__, 2) . '/scripts/verify_isolation_and_canary.php';
        $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($scriptPath) . ' --test-injected-failure';

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptors, $pipes);
        $this->assertIsResource($process, 'Failed to launch verify_isolation_and_canary process.');

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        $this->assertSame(1, $exitCode, 'Expected nonzero exit code (1) for injected failure.');
        $this->assertStringContainsString('[VERIFICATION FAILURE]', $stderr);
        $this->assertStringContainsString('Deliberately injected test failure for exit status verification', $stderr);
    }

    public function testInjectedFailureAfterMarkerDropExecutesCleanupAndRestoresUsableTarget(): void
    {
        $scriptPath = dirname(__DIR__, 2) . '/scripts/verify_isolation_and_canary.php';
        $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($scriptPath) . ' --test-injected-failure-after-marker-drop';

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptors, $pipes);
        $this->assertIsResource($process, 'Failed to launch verify_isolation_and_canary process.');

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        // 1. Process must exit nonzero
        $this->assertSame(1, $exitCode, 'Expected exit code 1 when assertion fails after marker removal.');

        // 2. Failure diagnostic must be printed to STDERR
        $this->assertStringContainsString('[VERIFICATION FAILURE]', $stderr);
        $this->assertStringContainsString('Deliberately injected test failure after marker drop to verify cleanup execution', $stderr);

        // 3. Cleanup must have executed in finally block and restored marker
        $this->assertStringContainsString('STEP 6: RESTORE CLEAN DISPOSABLE TEST DATABASE (CLEANUP)', $stdout);
        $this->assertStringContainsString('CLEANUP SUCCESS: Task ownership marker and baseline schema restored!', $stdout);

        // 4. Verify target database is now in a confirmed usable and provisioned state
        $checkScript = dirname(__DIR__, 2) . '/scripts/provision_test_db.php';
        $checkCmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($checkScript) . ' --check-only';
        $checkProcess = proc_open($checkCmd, $descriptors, $checkPipes);
        $this->assertIsResource($checkProcess);
        fclose($checkPipes[0]);
        $checkStdout = stream_get_contents($checkPipes[1]);
        fclose($checkPipes[1]);
        $checkStderr = stream_get_contents($checkPipes[2]);
        fclose($checkPipes[2]);
        $checkExit = proc_close($checkProcess);

        $this->assertSame(0, $checkExit, "Post-cleanup check-only must succeed with exit 0: {$checkStderr}");
        $this->assertStringContainsString('Target Database Status:   EXISTS', $checkStdout);
        $this->assertStringContainsString('Isolation Status:         VERIFIED', $checkStdout);
    }

    public function testOrchestrateCheckOnlyOnAbsentTargetDoesNotMutateAndReportsAbsent(): void
    {
        $liveConnectorCalled = false;
        $writerCalled = false;

        $result = TestDatabasePreflight::orchestrateCheckOnly(
            fn(string $targetDb) => false, // simulates absent database
            function() use (&$liveConnectorCalled) {
                $liveConnectorCalled = true;
                return ['liveDb' => 'democa_dental_saas_test', 'errors' => []];
            },
            'democa_dental_saas_test',
            function() use (&$writerCalled) {
                $writerCalled = true;
            }
        );

        $this->assertSame('ABSENT / UNPROVISIONED', $result['status']);
        $this->assertFalse($result['exists']);
        $this->assertNull($result['liveDb']);
        $this->assertEmpty($result['errors']);
        $this->assertFalse($liveConnectorCalled, 'Live connector must NOT be called on absent target in check-only mode.');
        $this->assertFalse($writerCalled, 'Writer/creator must NOT be called on absent target in check-only mode.');
    }

    public function testOrchestrateCheckOnlyPropagatesLiveConnectionFailureWhenTargetExists(): void
    {
        $result = TestDatabasePreflight::orchestrateCheckOnly(
            fn(string $targetDb) => true, // simulates existing database
            function() {
                return [
                    'liveDb' => 'democa_dental_saas_test',
                    'errors' => ['CRITICAL LIVE IDENTITY FAILURE: Simulated user host mismatch.'],
                ];
            },
            'democa_dental_saas_test'
        );

        $this->assertSame('EXISTS', $result['status']);
        $this->assertTrue($result['exists']);
        $this->assertSame('democa_dental_saas_test', $result['liveDb']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Simulated user host mismatch', $result['errors'][0]);
    }

    public function testOrchestrateRunnerPreTestExecutesProvisionBeforeConnect(): void
    {
        $executionOrder = [];

        $result = TestDatabasePreflight::orchestrateRunnerPreTest(
            true, // provision requested
            function() use (&$executionOrder) {
                $executionOrder[] = 'provision';
                return 0; // success
            },
            function() use (&$executionOrder) {
                $this->assertContains('provision', $executionOrder, 'Provisioning must execute BEFORE target connection!');
                $executionOrder[] = 'connect';
                return new \stdClass();
            },
            function($db) use (&$executionOrder) {
                $this->assertContains('connect', $executionOrder, 'Connection must execute BEFORE live validation!');
                $executionOrder[] = 'validate';
                return []; // zero errors
            }
        );

        $this->assertTrue($result['success']);
        $this->assertSame(['provision', 'connect', 'validate'], $result['sequence']);
        $this->assertSame(['provision', 'connect', 'validate'], $executionOrder);
    }

    public function testOrchestrateRunnerPreTestPropagatesProvisioningFailureWithoutConnecting(): void
    {
        $targetConnectorCalled = false;
        $liveValidatorCalled = false;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Provisioning failed with exit code 42. Aborting tests.');

        TestDatabasePreflight::orchestrateRunnerPreTest(
            true, // provision requested
            fn() => 42, // failing exit code
            function() use (&$targetConnectorCalled) {
                $targetConnectorCalled = true;
                return new \stdClass();
            },
            function($db) use (&$liveValidatorCalled) {
                $liveValidatorCalled = true;
                return [];
            }
        );

        $this->assertFalse($targetConnectorCalled, 'Target connector must NEVER be called if provisioning failed.');
        $this->assertFalse($liveValidatorCalled, 'Live validator must NEVER be called if provisioning failed.');
    }

    public function testOrchestrateRunnerPreTestWithoutProvisionConnectsDirectly(): void
    {
        $provisionerCalled = false;
        $executionOrder = [];

        $result = TestDatabasePreflight::orchestrateRunnerPreTest(
            false, // no provision requested
            function() use (&$provisionerCalled) {
                $provisionerCalled = true;
                return 0;
            },
            function() use (&$executionOrder) {
                $executionOrder[] = 'connect';
                return new \stdClass();
            },
            function($db) use (&$executionOrder) {
                $executionOrder[] = 'validate';
                return [];
            }
        );

        $this->assertTrue($result['success']);
        $this->assertFalse($provisionerCalled, 'Provisioner must NOT be called when provision requested is false.');
        $this->assertSame(['connect', 'validate'], $result['sequence']);
        $this->assertSame(['connect', 'validate'], $executionOrder);
    }

    private function createMockDbWithMarker(array $markerData): object
    {
        return new class($markerData) {
            private array $marker;
            public function __construct(array $marker)
            {
                $this->marker = $marker;
            }
            public function listTables(): array
            {
                return ['_test_db_marker', 'users'];
            }
            public function table(string $table)
            {
                $marker = $this->marker;
                return new class($marker) {
                    private array $marker;
                    public function __construct(array $marker)
                    {
                        $this->marker = $marker;
                    }
                    public function get()
                    {
                        $marker = $this->marker;
                        return new class($marker) {
                            private array $marker;
                            public function __construct(array $marker)
                            {
                                $this->marker = $marker;
                            }
                            public function getRowArray(): ?array
                            {
                                return $this->marker;
                            }
                        };
                    }
                };
            }
        };
    }
}
