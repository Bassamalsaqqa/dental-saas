<?php
declare(strict_types=1);

namespace Scripts;

/**
 * Fail-Closed Preflight Safeguard for Developer Sandbox Database Operations
 *
 * Enforces strict isolation for the incoming developer's exploratory sandbox environment:
 *  - Enforces EXACT target schema: democa_dental_saas_dev on 127.0.0.1:3307 ONLY.
 *  - Refuses business database (democa_dental_saas and authoritative configured business DB) unconditionally.
 *  - Refuses automated test database (democa_dental_saas_test) unconditionally to prevent collision.
 *  - Refuses all recovery, reference, and negative schemas (denta_rec_*, denta_ref_*, denta_neg_*).
 *  - Refuses system databases (mysql, information_schema, performance_schema, sys).
 *  - Refuses remote hosts (loopback only: 127.0.0.1, localhost, ::1).
 *  - Refuses port 3306 and missing ports; requires explicit port 3307.
 *  - Refuses non-MySQLi drivers, alternate failover, DSN, socket, or subdriver overrides.
 *  - Fails closed on incomplete live identity (missing/null port, host, user, or db).
 *  - Version 1 requires a GENUINELY EMPTY target database (zero tables/views).
 *  - Reset/overwrite of populated schemas is strictly refused pending separate recovery design.
 */
final class DeveloperSandboxPreflight
{
    public const APPROVED_DEFAULT_SANDBOX_DB = 'democa_dental_saas_dev';
    public const DISALLOWED_BUSINESS_DB = 'democa_dental_saas';
    public const DISALLOWED_TEST_DB = 'democa_dental_saas_test';

    public const FORBIDDEN_DATABASES = [
        self::DISALLOWED_BUSINESS_DB,
        self::DISALLOWED_TEST_DB,
    ];

    public const SYSTEM_SCHEMAS = [
        'mysql',
        'information_schema',
        'performance_schema',
        'sys',
    ];

    public const FORBIDDEN_NAME_PATTERNS = [
        '/^denta_rec_/i',
        '/^denta_ref_/i',
        '/^denta_neg_/i',
    ];

    public const ALLOWED_HOSTS = ['127.0.0.1', 'localhost', '::1', 'p:127.0.0.1', 'p:localhost'];
    public const ALLOWED_PORTS = [3307];
    public const ALLOWED_DRIVERS = ['MySQLi'];
    public const OWNERSHIP_MARKER_TABLE = '_dev_sandbox_marker';
    public const TASK_OWNER_SIGNATURE = 'developer_sandbox_bootstrap';

    /**
     * Validate sandbox configuration BEFORE attempting any database connection.
     * Returns an array of error messages. If non-empty, connection MUST be refused.
     *
     * @param array $sandboxConfig Config for the target sandbox connection group
     * @param array $businessConfig Config for the default business connection group
     * @param string $targetDb Expected target sandbox database name (must strictly equal APPROVED_DEFAULT_SANDBOX_DB)
     * @return string[] Errors encountered
     */
    public static function validateConfigBeforeConnection(
        array $sandboxConfig,
        array $businessConfig,
        string $targetDb = self::APPROVED_DEFAULT_SANDBOX_DB
    ): array {
        $errors = [];

        // 1. Strict Target Database Confinement:
        // Only democa_dental_saas_dev is approved in this task.
        // Caller-specified targetDb overrides that differ from the approved name MUST be rejected immediately.
        if ($targetDb !== self::APPROVED_DEFAULT_SANDBOX_DB) {
            $errors[] = "Preflight check failed: target database '{$targetDb}' is not the approved sandbox schema ('" . self::APPROVED_DEFAULT_SANDBOX_DB . "'). Arbitrary target overrides are strictly forbidden.";
        }

        $host = (string) ($sandboxConfig['hostname'] ?? '');
        $hasPort = array_key_exists('port', $sandboxConfig) && $sandboxConfig['port'] !== null && $sandboxConfig['port'] !== '';
        $rawPort = $hasPort ? $sandboxConfig['port'] : null;
        $port = is_int($rawPort) || (is_string($rawPort) && ctype_digit($rawPort))
            ? (int) $rawPort : null;
        $driver = (string) ($sandboxConfig['DBDriver'] ?? '');
        $configuredDb = (string) ($sandboxConfig['database'] ?? '');

        // 2. Authoritative Business Config Disjointness:
        // Never let a missing comparison configuration silently imply safety.
        if (empty($businessConfig) || !isset($businessConfig['database']) || trim((string) $businessConfig['database']) === '') {
            $errors[] = "Preflight check failed: authoritative business database configuration is missing or malformed; cannot verify isolation disjointness.";
            $businessDb = '';
        } else {
            $businessDb = (string) $businessConfig['database'];
        }

        // 3. Host validation: Refuse remote targets WITHOUT connecting
        $normalizedHost = strtolower(trim($host));
        if ($normalizedHost === '' || !in_array($normalizedHost, self::ALLOWED_HOSTS, true)) {
            $errors[] = "Preflight check failed: sandbox host '{$host}' is not an approved local loopback address (" . implode(', ', self::ALLOWED_HOSTS) . "). Remote targets are strictly forbidden.";
        }

        // 4. Port validation: Port 3307 ONLY. Reject 3306 and missing port.
        if ($port === null) {
            $errors[] = "Preflight check failed: sandbox port is missing. Port 3307 must be explicitly configured; defaulting is not permitted.";
        } elseif (!in_array($port, self::ALLOWED_PORTS, true)) {
            $errors[] = "Preflight check failed: sandbox port '{$port}' is not approved. Only port 3307 is approved for the developer sandbox; port 3306 (business MySQL) is strictly refused.";
        }

        // 5. Driver validation
        if (!in_array($driver, self::ALLOWED_DRIVERS, true)) {
            $errors[] = "Preflight check failed: sandbox DBDriver '{$driver}' is not approved. Expected MySQLi.";
        }

        // 6. Target database name validation
        if ($configuredDb === '') {
            $errors[] = "Preflight check failed: sandbox database name resolved to empty string.";
        } elseif ($configuredDb !== self::APPROVED_DEFAULT_SANDBOX_DB) {
            $errors[] = "Preflight check failed: configured database '{$configuredDb}' is not the approved sandbox schema ('" . self::APPROVED_DEFAULT_SANDBOX_DB . "').";
        }

        // 7. Reject forbidden names and patterns
        if (in_array(strtolower($configuredDb), self::SYSTEM_SCHEMAS, true)) {
            $errors[] = "CRITICAL ISOLATION FAILURE: Configured target '{$configuredDb}' is a protected MySQL system schema. Execution strictly refused.";
        }

        foreach (self::FORBIDDEN_NAME_PATTERNS as $pattern) {
            if (preg_match($pattern, $configuredDb)) {
                $errors[] = "CRITICAL ISOLATION FAILURE: Target database '{$configuredDb}' matches protected recovery/reference pattern '{$pattern}'. Execution strictly refused.";
            }
        }

        // 8. Strict rejection of business database (both constant and authoritative config)
        if ($configuredDb === self::DISALLOWED_BUSINESS_DB) {
            $errors[] = "CRITICAL ISOLATION FAILURE: Target database is the production business database ('" . self::DISALLOWED_BUSINESS_DB . "'). Execution strictly refused.";
        }

        if ($businessDb !== '' && $configuredDb === $businessDb) {
            $errors[] = "CRITICAL ISOLATION FAILURE: Sandbox database ('{$configuredDb}') matches authoritative business database ('{$businessDb}'). Execution strictly refused.";
        }

        // 9. Strict rejection of automated test database (preserves democa_dental_saas_test disjointness)
        if ($configuredDb === self::DISALLOWED_TEST_DB) {
            $errors[] = "CRITICAL ISOLATION FAILURE: Target database is the automated test database ('" . self::DISALLOWED_TEST_DB . "'). Sandbox operations must remain strictly disjoint from automated test targets.";
        }

        // 10. Alternate connection overrides validation
        if (!empty($sandboxConfig['failover'])) {
            $errors[] = "Preflight check failed: 'failover' configuration is forbidden for sandbox connection. Alternate overrides are disallowed.";
        }

        if (!empty($sandboxConfig['DSN'])) {
            $errors[] = "Preflight check failed: 'DSN' override is forbidden for sandbox connection. Direct host/port configuration is required.";
        }

        if (!empty($sandboxConfig['subdriver'])) {
            $errors[] = "Preflight check failed: 'subdriver' override is forbidden for sandbox connection. Expected standard MySQLi driver.";
        }

        if (!empty($sandboxConfig['socket'])) {
            $errors[] = "Preflight check failed: 'socket' override is forbidden for sandbox connection. Explicit TCP loopback connection on port 3307 is required.";
        }

        return $errors;
    }

    /**
     * Validate live connection identity AFTER connecting, before any mutation.
     * Fails closed if any identity field is missing, null, or unexpected.
     *
     * @param mixed $db Live database connection object
     * @param string $targetDb Expected target sandbox database name
     * @return string[] Errors encountered
     */
    public static function validateLiveConnection($db, string $targetDb = self::APPROVED_DEFAULT_SANDBOX_DB): array
    {
        $errors = [];

        try {
            $queryResult = $db->query('SELECT DATABASE() as current_db, @@hostname as srv_host, @@port as srv_port, USER() as curr_user');
            $row = $queryResult ? $queryResult->getRowArray() : null;

            if (!is_array($row)) {
                return ["CRITICAL LIVE IDENTITY FAILURE: Server query for connection identity returned null or empty result. Halting."];
            }

            // 1. Validate current_db (MUST be present, non-empty, and match approved target)
            $liveDb = isset($row['current_db']) && is_string($row['current_db']) ? trim($row['current_db']) : '';
            if ($liveDb === '') {
                $errors[] = "CRITICAL LIVE IDENTITY FAILURE: SELECT DATABASE() returned empty or null. Exact sandbox database context is required.";
            } elseif ($liveDb !== self::APPROVED_DEFAULT_SANDBOX_DB) {
                $errors[] = "CRITICAL LIVE IDENTITY FAILURE: SELECT DATABASE() returned '{$liveDb}', expected approved sandbox '" . self::APPROVED_DEFAULT_SANDBOX_DB . "'.";
            }

            if ($liveDb === self::DISALLOWED_BUSINESS_DB) {
                $errors[] = "CRITICAL LIVE IDENTITY FAILURE: Connected directly to business database '" . self::DISALLOWED_BUSINESS_DB . "'. Halting immediately.";
            }

            if ($liveDb === self::DISALLOWED_TEST_DB) {
                $errors[] = "CRITICAL LIVE IDENTITY FAILURE: Connected to automated test database '" . self::DISALLOWED_TEST_DB . "'. Halting immediately.";
            }

            foreach (self::FORBIDDEN_NAME_PATTERNS as $pattern) {
                if ($liveDb !== '' && preg_match($pattern, $liveDb)) {
                    $errors[] = "CRITICAL LIVE IDENTITY FAILURE: Connected database '{$liveDb}' matches protected pattern '{$pattern}'. Halting immediately.";
                }
            }

            // 2. Validate srv_port (MUST be present, integer, and strictly equal 3307)
            $rawPort = $row['srv_port'] ?? null;
            $livePort = is_int($rawPort) || (is_string($rawPort) && ctype_digit($rawPort))
                ? (int) $rawPort : 0;
            if ($livePort === 0) {
                $errors[] = "CRITICAL LIVE IDENTITY FAILURE: Server @@port identity is missing or zero. Refusing connection of unknown port.";
            } elseif (!in_array($livePort, self::ALLOWED_PORTS, true)) {
                $errors[] = "CRITICAL LIVE IDENTITY FAILURE: Connected to server on port {$livePort}, expected sandbox port (" . implode(', ', self::ALLOWED_PORTS) . "). Port 3306 is strictly forbidden.";
            }

            // 3. Validate srv_host (MUST be present and non-empty)
            $liveHost = isset($row['srv_host']) && is_string($row['srv_host']) ? trim($row['srv_host']) : '';
            if ($liveHost === '') {
                $errors[] = "CRITICAL LIVE IDENTITY FAILURE: Server @@hostname identity is missing or empty.";
            }

            // 4. Validate curr_user (MUST be present, non-empty, and resolve to local loopback host)
            $liveUser = isset($row['curr_user']) && is_string($row['curr_user']) ? trim($row['curr_user']) : '';
            if ($liveUser === '') {
                $errors[] = "CRITICAL LIVE IDENTITY FAILURE: USER() identity is missing or empty. Refusing unauthenticated or anonymous connection.";
            } else {
                $userParts = explode('@', $liveUser);
                if (count($userParts) < 2) {
                    $errors[] = "CRITICAL LIVE IDENTITY FAILURE: USER() returned malformed identity '{$liveUser}'. Expected user@host format.";
                } else {
                    $clientHost = strtolower(trim(end($userParts)));
                    if (!in_array($clientHost, ['localhost', '127.0.0.1', '::1'], true)) {
                        $errors[] = "CRITICAL LIVE IDENTITY FAILURE: Connected user host is '{$clientHost}'. Only local loopback connections are allowed.";
                    }
                }
            }
        } catch (\Throwable $e) {
            // Sanitize exception: never expose raw connection credentials or DSNs in output
            $errors[] = "CRITICAL LIVE IDENTITY FAILURE: Exception while querying live connection identity (sanitized: " . get_class($e) . ").";
        }

        return $errors;
    }

    /**
     * Verify target eligibility for bootstrap.
     *
     * In Version 1, reset/overwrite is strictly REMOVED.
     * Bootstrap requires a GENUINELY EMPTY target database (zero tables and zero views).
     * Populated schemas are refused unconditionally.
     *
     * @param mixed $db Live database connection object
     * @param string $targetDb Expected target sandbox database name
     * @return array{allowed: bool, reason: string}
     */
    public static function verifyTargetEligibilityForBootstrap($db, string $targetDb = self::APPROVED_DEFAULT_SANDBOX_DB): array
    {
        if ($targetDb !== self::APPROVED_DEFAULT_SANDBOX_DB) {
            return ['allowed' => false, 'reason' => 'Unapproved sandbox target; no metadata queries performed.'];
        }
        try {
            $tables = $db->listTables();
            $tableCount = count($tables);

            // Also check for views if query is available
            $viewCount = 0;
            try {
                $viewQuery = $db->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
                if (!$viewQuery || !method_exists($viewQuery, 'getResultArray')) {
                    throw new \RuntimeException('View metadata unavailable');
                }
                if ($viewQuery) {
                    $viewRows = $viewQuery->getResultArray();
                    $viewCount = count($viewRows);
                }
            } catch (\Throwable $error) {
                return ['allowed' => false, 'reason' => 'View inventory unavailable; target emptiness is not verified.'];
            }

            if ($tableCount === 0 && $viewCount === 0) {
                return [
                    'allowed' => true,
                    'reason' => "Target database '{$targetDb}' is completely empty (0 tables, 0 views). Safe to initialize.",
                ];
            }

            return [
                'allowed' => false,
                'reason' => "Target database '{$targetDb}' is NOT empty (contains {$tableCount} table(s) and {$viewCount} view(s)). Reset/overwrite is strictly refused in Version 1. Bootstrap requires a genuinely empty target schema.",
            ];
        } catch (\Throwable $e) {
            return [
                'allowed' => false,
                'reason' => "Failed to inspect target database tables (sanitized: " . get_class($e) . "). Refusing bootstrap.",
            ];
        }
    }
}
