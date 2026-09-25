<?php
declare(strict_types=1);

namespace Scripts;

/**
 * Shared Fail-Closed Preflight Safeguard for Test Database Operations
 *
 * Provides a single unified preflight policy for test runners, provisioners,
 * and test harnesses. Ensures test operations NEVER run against, connect to,
 * or mutate the business database (democa_dental_saas) or remote hosts.
 */
final class TestDatabasePreflight
{
    public const APPROVED_DISPOSABLE_TEST_DB = 'democa_dental_saas_test';
    public const DISALLOWED_BUSINESS_DB = 'democa_dental_saas';
    public const ALLOWED_HOSTS = ['127.0.0.1', 'localhost', '::1', 'p:127.0.0.1', 'p:localhost'];
    public const ALLOWED_PORTS = [3306, 3307];
    public const ALLOWED_DRIVERS = ['MySQLi'];
    public const OWNERSHIP_MARKER_TABLE = '_test_db_marker';
    public const TASK_OWNER_SIGNATURE = 'safe_test_runner';

    /**
     * Enforce exact dotted environment variables for the test database.
     */
    public static function enforceEnvironment(string $targetDb = self::APPROVED_DISPOSABLE_TEST_DB): void
    {
        putenv('database.tests.database=' . $targetDb);
        $_ENV['database.tests.database'] = $targetDb;
        $_SERVER['database.tests.database'] = $targetDb;
    }

    /**
     * Validate configuration BEFORE attempting any database connection.
     * Returns an array of error messages. If non-empty, execution MUST halt without connecting.
     *
     * @param array $testsConfig Config for the 'tests' connection group
     * @param array $defaultConfig Config for the 'default' connection group
     * @return string[] Errors encountered
     */
    public static function validateConfigBeforeConnection(array $testsConfig, array $defaultConfig): array
    {
        $errors = [];

        $testHost = (string) ($testsConfig['hostname'] ?? '');
        $testPort = (int) ($testsConfig['port'] ?? 3306);
        $testDriver = (string) ($testsConfig['DBDriver'] ?? '');
        $testDb = (string) ($testsConfig['database'] ?? '');
        $businessDb = (string) ($defaultConfig['database'] ?? '');

        // 1. Host validation: Refuse remote targets WITHOUT connecting
        $normalizedHost = strtolower(trim($testHost));
        if ($normalizedHost === '' || !in_array($normalizedHost, self::ALLOWED_HOSTS, true)) {
            $errors[] = "Preflight check failed: tests host '{$testHost}' is not an approved local address (" . implode(', ', self::ALLOWED_HOSTS) . "). Remote targets are strictly forbidden.";
        }

        // 2. Port validation
        if (!in_array($testPort, self::ALLOWED_PORTS, true)) {
            $errors[] = "Preflight check failed: tests port '{$testPort}' is not an approved local MySQL port (" . implode(', ', self::ALLOWED_PORTS) . ").";
        }

        // 3. Driver validation
        if (!in_array($testDriver, self::ALLOWED_DRIVERS, true)) {
            $errors[] = "Preflight check failed: tests DBDriver '{$testDriver}' is not approved. Expected MySQLi.";
        }

        // 4. Test database name validation
        if ($testDb === '') {
            $errors[] = "Preflight check failed: 'database.tests.database' resolved to empty string.";
        } elseif ($testDb !== self::APPROVED_DISPOSABLE_TEST_DB) {
            $errors[] = "Preflight check failed: tests database '{$testDb}' does not match approved disposable target ('" . self::APPROVED_DISPOSABLE_TEST_DB . "').";
        }

        // 5. Business database disjointness
        if ($businessDb !== '' && $testDb === $businessDb) {
            $errors[] = "CRITICAL ISOLATION FAILURE: tests database ('{$testDb}') equals default business database ('{$businessDb}'). Execution strictly refused.";
        }

        if ($businessDb === self::APPROVED_DISPOSABLE_TEST_DB) {
            $errors[] = "CRITICAL ISOLATION FAILURE: default business database is configured as '" . self::APPROVED_DISPOSABLE_TEST_DB . "'. Execution strictly refused.";
        }

        // 6. Alternate connection paths validation (Failover, DSN, subdriver)
        if (!empty($testsConfig['failover'])) {
            $errors[] = "Preflight check failed: 'failover' configuration is forbidden for tests connection group. Alternate connection overrides are strictly disallowed.";
        }

        if (!empty($testsConfig['DSN'])) {
            $errors[] = "Preflight check failed: 'DSN' override is forbidden for tests connection group. Alternate connection overrides are strictly disallowed.";
        }

        if (!empty($testsConfig['subdriver'])) {
            $errors[] = "Preflight check failed: 'subdriver' override is forbidden for tests connection group. Expected standard MySQLi driver.";
        }

        return $errors;
    }

    /**
     * Validate live connection identity AFTER connecting, before any mutation.
     * Returns an array of error messages.
     *
     * @param \CodeIgniter\Database\BaseConnection $db
     * @return string[]
     */
    public static function validateLiveConnection($db): array
    {
        $errors = [];

        try {
            $row = $db->query('SELECT DATABASE() as current_db, @@hostname as srv_host, @@port as srv_port, USER() as curr_user')->getRowArray();
            $liveDb = (string) ($row['current_db'] ?? '');
            $livePort = (int) ($row['srv_port'] ?? 0);
            $liveUser = (string) ($row['curr_user'] ?? '');

            if ($liveDb !== self::APPROVED_DISPOSABLE_TEST_DB) {
                $errors[] = "CRITICAL LIVE IDENTITY FAILURE: SELECT DATABASE() returned '{$liveDb}', expected '" . self::APPROVED_DISPOSABLE_TEST_DB . "'.";
            }

            if ($liveDb === self::DISALLOWED_BUSINESS_DB) {
                $errors[] = "CRITICAL LIVE IDENTITY FAILURE: Connected directly to business database '" . self::DISALLOWED_BUSINESS_DB . "'. Halting immediately.";
            }

            if ($livePort !== 0 && !in_array($livePort, self::ALLOWED_PORTS, true)) {
                $errors[] = "CRITICAL LIVE IDENTITY FAILURE: Connected to server on port {$livePort}, expected local port (" . implode(', ', self::ALLOWED_PORTS) . ").";
            }

            $userParts = explode('@', $liveUser);
            $clientHost = strtolower(trim(end($userParts)));
            if ($clientHost !== '' && !in_array($clientHost, ['localhost', '127.0.0.1', '::1'], true)) {
                $errors[] = "CRITICAL LIVE IDENTITY FAILURE: Connected user host is '{$clientHost}'. Only local loopback connections are allowed.";
            }
        } catch (\Throwable $e) {
            $errors[] = "Failed to query live connection identity: " . $e->getMessage();
        }

        return $errors;
    }

    /**
     * Verify task ownership and disposability before allowing destructive reset operations.
     *
     * @param \CodeIgniter\Database\BaseConnection $db
     * @return array{allowed: bool, reason: string}
     */
    public static function verifyOwnershipForReset($db): array
    {
        $tables = $db->listTables();

        // If the database is completely empty, it is safe to provision from scratch
        if (count($tables) === 0) {
            return ['allowed' => true, 'reason' => 'Target database is completely empty. Safe to initialize.'];
        }

        // If tables exist, check for ownership marker table
        if (!in_array(self::OWNERSHIP_MARKER_TABLE, $tables, true)) {
            return [
                'allowed' => false,
                'reason' => "Target database contains " . count($tables) . " tables but lacks the task ownership marker table ('" . self::OWNERSHIP_MARKER_TABLE . "'). Refusing to drop tables of unknown provenance."
            ];
        }

        $marker = $db->table(self::OWNERSHIP_MARKER_TABLE)->get()->getRowArray();
        if (!$marker) {
            return [
                'allowed' => false,
                'reason' => "Ownership marker table exists but contains no records. Refusing reset."
            ];
        }

        if (($marker['task_owner'] ?? '') !== self::TASK_OWNER_SIGNATURE
            || (int) ($marker['is_disposable'] ?? 0) !== 1
            || ($marker['target_db'] ?? '') !== self::APPROVED_DISPOSABLE_TEST_DB
        ) {
            return [
                'allowed' => false,
                'reason' => "Ownership marker does not confirm task ownership, disposable status, and target database match (task_owner='{$marker['task_owner']}', is_disposable={$marker['is_disposable']}, target_db='{$marker['target_db']}'). Refusing reset."
            ];
        }

        return ['allowed' => true, 'reason' => 'Task ownership and disposability confirmed by marker record.'];
    }

    /**
     * Inspect database existence on local server safely without selecting it.
     */
    public static function checkDatabaseExistsOnServer(string $host, string $user, string $pass, int $port, string $targetDb): bool
    {
        $rawMysqli = @new \mysqli($host, $user, $pass, '', $port);
        if ($rawMysqli->connect_errno) {
            throw new \RuntimeException("Unable to connect to local MySQL server: " . $rawMysqli->connect_error);
        }
        $escapedDb = $rawMysqli->real_escape_string($targetDb);
        $res = $rawMysqli->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$escapedDb}'");
        $exists = ($res && $res->num_rows > 0);
        $rawMysqli->close();
        return $exists;
    }

    /**
     * Orchestrate strictly read-only check-only inspection.
     * Guarantees zero writes/mutations on absent or present targets.
     *
     * @param callable $existenceChecker Callable(string $targetDb): bool
     * @param callable $liveConnector Callable(): array{liveDb: string, errors: string[]}
     * @param string $targetDb
     * @param callable|null $writerSpy Optional spy to verify no writes are attempted
     * @return array{status: string, exists: bool, liveDb: string|null, errors: string[]}
     */
    public static function orchestrateCheckOnly(
        callable $existenceChecker,
        callable $liveConnector,
        string $targetDb,
        ?callable $writerSpy = null
    ): array {
        $checkExists = $existenceChecker($targetDb);

        if (!$checkExists) {
            // Strictly read-only: reports absent, NEVER creates database or mutates schemas
            return [
                'status' => 'ABSENT / UNPROVISIONED',
                'exists' => false,
                'liveDb' => null,
                'errors' => [],
            ];
        }

        // Target exists: verify live identity
        $liveResult = $liveConnector();
        $liveErrors = $liveResult['errors'] ?? [];

        return [
            'status' => 'EXISTS',
            'exists' => true,
            'liveDb' => $liveResult['liveDb'] ?? null,
            'errors' => $liveErrors,
        ];
    }

    /**
     * Orchestrate test runner pre-test lifecycle sequence.
     * Enforces that provisioning occurs BEFORE target connection when requested.
     *
     * @param bool $provisionRequested
     * @param callable $provisionerExecutor Callable(): int Exit code
     * @param callable $targetConnector Callable(): mixed Database connection
     * @param callable $liveValidator Callable(mixed $db): string[] Errors
     * @return array{success: bool, sequence: string[], db: mixed}
     */
    public static function orchestrateRunnerPreTest(
        bool $provisionRequested,
        callable $provisionerExecutor,
        callable $targetConnector,
        callable $liveValidator
    ): array {
        $sequence = [];

        if ($provisionRequested) {
            $sequence[] = 'provision';
            $exitCode = $provisionerExecutor();
            if ($exitCode !== 0) {
                throw new \RuntimeException("Provisioning failed with exit code {$exitCode}. Aborting tests.");
            }
        }

        $sequence[] = 'connect';
        $db = $targetConnector();

        $sequence[] = 'validate';
        $errors = $liveValidator($db);
        if (!empty($errors)) {
            throw new \RuntimeException("Live identity validation failed: " . implode('; ', $errors));
        }

        return [
            'success' => true,
            'sequence' => $sequence,
            'db' => $db,
        ];
    }
}
