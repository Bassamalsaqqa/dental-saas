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
 *  - Enforces anchored grant allowlist: permits only USAGE ON *.* and approved privileges on exact sandbox.
 *  - Version 1 requires a GENUINELY EMPTY target database (zero tables, views, routines, events).
 *  - All metadata query failures refuse eligibility.
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
            $livePort = isset($row['srv_port']) && is_numeric($row['srv_port']) ? (int) $row['srv_port'] : 0;
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
     * Validate user grants on the live connection using an anchored allowlist.
     * Permits ONLY:
     *  - USAGE ON *.* (standard MySQL connection privilege)
     *  - Approved privileges on the exact sandbox schema (democa_dental_saas_dev)
     *
     * Strictly REJECTS:
     *  - Global privileges on *.* other than USAGE (e.g. ALL PRIVILEGES, SELECT, PROCESS)
     *  - WITH GRANT OPTION
     *  - PROXY or role assignments
     *  - Grants on foreign/business/test/recovery schemas
     *  - Wildcard schema patterns (e.g. unescaped _ or %)
     *  - Unknown, unparseable, or empty/failed result sets
     *
     * @param mixed $db Live database connection object
     * @param string $targetDb Expected target sandbox database name
     * @return string[] Errors encountered
     */
    public static function validateUserGrants($db, string $targetDb = self::APPROVED_DEFAULT_SANDBOX_DB): array
    {
        $errors = [];

        if ($targetDb !== self::APPROVED_DEFAULT_SANDBOX_DB) {
            return ["CRITICAL LIVE IDENTITY FAILURE: Unapproved target '{$targetDb}' specified for grant validation. Overrides forbidden."];
        }

        try {
            $query = $db->query('SHOW GRANTS FOR CURRENT_USER()');
            if (!$query || !method_exists($query, 'getResultArray')) {
                return ["CRITICAL LIVE IDENTITY FAILURE: Failed to inspect user grants (query returned null or invalid result)."];
            }

            $rows = $query->getResultArray();
            if (!is_array($rows) || empty($rows)) {
                return ["CRITICAL LIVE IDENTITY FAILURE: SHOW GRANTS returned zero records for current user."];
            }

            $grantStatements = [];
            foreach ($rows as $row) {
                if (is_array($row)) {
                    foreach ($row as $colVal) {
                        if (is_string($colVal) && trim($colVal) !== '') {
                            $grantStatements[] = trim($colVal);
                        }
                    }
                }
            }

            if (empty($grantStatements)) {
                return ["CRITICAL LIVE IDENTITY FAILURE: Unable to parse grant statements from server response."];
            }

            $hasSandboxGrant = false;

            foreach ($grantStatements as $stmt) {
                // 1. Strictly reject WITH GRANT OPTION anywhere
                if (preg_match('/WITH\s+GRANT\s+OPTION/i', $stmt)) {
                    $errors[] = "CRITICAL ISOLATION FAILURE: Grant statement includes WITH GRANT OPTION. Administrative delegation is strictly forbidden.";
                }

                // 2. Strictly reject PROXY grants
                if (preg_match('/\bPROXY\b/i', $stmt)) {
                    $errors[] = "CRITICAL ISOLATION FAILURE: Grant statement includes PROXY privilege. Role delegation is strictly forbidden.";
                }

                // 3. Global *.* grants: ONLY USAGE is permitted
                if (preg_match('/ON\s+\*\.\*/i', $stmt)) {
                    if (!preg_match('/^GRANT\s+USAGE\s+ON\s+\*\.\*/i', $stmt)) {
                        $errors[] = "CRITICAL ISOLATION FAILURE: User holds excessive global privileges on *.*. Only USAGE ON *.* is permitted.";
                    }
                    continue;
                }

                // 4. Schema-level grants: Match exact target schema
                if (preg_match('/ON\s+(`?)([^`\s.]+)\1\.\*/i', $stmt, $mSchema)) {
                    $rawSchema = $mSchema[2];
                    // Normalize MariaDB/MySQL escaped underscore: democa\_dental\_saas\_dev -> democa_dental_saas_dev
                    $normalizedSchema = str_replace(['\\_', '\\%'], ['_', '%'], $rawSchema);

                    // Strictly reject wildcard characters in schema name (% or unescaped _)
                    if (str_contains($rawSchema, '%') || preg_match('/(?<!\\\\)_/', $rawSchema)) {
                        $errors[] = "CRITICAL ISOLATION FAILURE: Grant schema contains unescaped wildcard pattern characters ('%' or unescaped '_'). In MariaDB grants, unescaped underscores act as single-character wildcards. Grant must be strictly escaped (e.g. `democa\_dental\_saas\_dev`.*) to guarantee confinement.";
                    }

                    if ($normalizedSchema !== self::APPROVED_DEFAULT_SANDBOX_DB) {
                        $errors[] = "CRITICAL ISOLATION FAILURE: User holds grants on unauthorized schema '{$normalizedSchema}'. Only the approved developer sandbox is permitted.";
                    } else {
                        $hasSandboxGrant = true;
                    }
                    continue;
                }

                // 5. Any other grant statement format is unknown and rejected
                $errors[] = "CRITICAL ISOLATION FAILURE: Unrecognized or unanchored grant statement encountered. Failing closed.";
            }

            if (!$hasSandboxGrant) {
                $errors[] = "CRITICAL ISOLATION FAILURE: User holds no privileges on the target sandbox database '" . self::APPROVED_DEFAULT_SANDBOX_DB . "'.";
            }
        } catch (\Throwable $e) {
            // Sanitize: Never expose raw statement text or SQL error in output
            $errors[] = "CRITICAL LIVE IDENTITY FAILURE: Exception while inspecting user grants (sanitized: " . get_class($e) . ").";
        }

        return $errors;
    }

    /**
     * Verify target eligibility for bootstrap.
     *
     * In Version 1, reset/overwrite is strictly REMOVED.
     * Bootstrap requires a GENUINELY EMPTY target database (zero tables, views, routines, events).
     * Populated schemas are refused unconditionally.
     * All metadata query failures (false, null, wrong shape, exceptions) strictly refuse eligibility.
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
            if (!is_array($tables)) {
                return ['allowed' => false, 'reason' => 'Table inventory unavailable; target emptiness is not verified.'];
            }
            $tableCount = count($tables);

            // 1. Check for views
            try {
                $viewQuery = $db->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
                if (!$viewQuery || !method_exists($viewQuery, 'getResultArray')) {
                    return ['allowed' => false, 'reason' => 'View inventory unavailable; target emptiness is not verified.'];
                }
                $viewRows = $viewQuery->getResultArray();
                if (!is_array($viewRows)) {
                    return ['allowed' => false, 'reason' => 'View inventory returned invalid shape; target emptiness is not verified.'];
                }
                $viewCount = count($viewRows);
            } catch (\Throwable $error) {
                return ['allowed' => false, 'reason' => 'View inventory unavailable; target emptiness is not verified.'];
            }

            // 2. Check for stored procedures and functions
            try {
                $procQuery = $db->query("SHOW PROCEDURE STATUS WHERE Db = '{$targetDb}'");
                if (!$procQuery || !method_exists($procQuery, 'getResultArray')) {
                    return ['allowed' => false, 'reason' => 'Stored procedure inventory unavailable; target emptiness is not verified.'];
                }
                $procRows = $procQuery->getResultArray();
                if (!is_array($procRows)) {
                    return ['allowed' => false, 'reason' => 'Stored procedure inventory returned invalid shape; target emptiness is not verified.'];
                }

                $funcQuery = $db->query("SHOW FUNCTION STATUS WHERE Db = '{$targetDb}'");
                if (!$funcQuery || !method_exists($funcQuery, 'getResultArray')) {
                    return ['allowed' => false, 'reason' => 'Stored function inventory unavailable; target emptiness is not verified.'];
                }
                $funcRows = $funcQuery->getResultArray();
                if (!is_array($funcRows)) {
                    return ['allowed' => false, 'reason' => 'Stored function inventory returned invalid shape; target emptiness is not verified.'];
                }

                $routineCount = count($procRows) + count($funcRows);
            } catch (\Throwable $error) {
                return ['allowed' => false, 'reason' => 'Routine inventory unavailable; target emptiness is not verified.'];
            }

            // 3. Check for scheduled events
            try {
                $eventQuery = $db->query("SHOW EVENTS FROM `{$targetDb}`");
                if (!$eventQuery || !method_exists($eventQuery, 'getResultArray')) {
                    return ['allowed' => false, 'reason' => 'Scheduled event inventory unavailable; target emptiness is not verified.'];
                }
                $eventRows = $eventQuery->getResultArray();
                if (!is_array($eventRows)) {
                    return ['allowed' => false, 'reason' => 'Scheduled event inventory returned invalid shape; target emptiness is not verified.'];
                }
                $eventCount = count($eventRows);
            } catch (\Throwable $error) {
                return ['allowed' => false, 'reason' => 'Scheduled event inventory unavailable; target emptiness is not verified.'];
            }

            if ($tableCount === 0 && $viewCount === 0 && $routineCount === 0 && $eventCount === 0) {
                return [
                    'allowed' => true,
                    'reason' => "Target database '{$targetDb}' is completely empty (0 tables, 0 views, 0 routines, 0 events). Safe to initialize.",
                ];
            }

            $details = [];
            if ($tableCount > 0) $details[] = "{$tableCount} table(s)";
            if ($viewCount > 0) $details[] = "{$viewCount} view(s)";
            if ($routineCount > 0) $details[] = "{$routineCount} routine(s)";
            if ($eventCount > 0) $details[] = "{$eventCount} event(s)";
            $detailsStr = implode(', ', $details);

            return [
                'allowed' => false,
                'reason' => "Target database '{$targetDb}' is NOT empty (contains {$detailsStr}). Reset/overwrite is strictly refused in Version 1. Bootstrap requires a genuinely empty target schema.",
            ];
        } catch (\Throwable $e) {
            return [
                'allowed' => false,
                'reason' => "Failed to inspect target database tables (sanitized: " . get_class($e) . "). Refusing bootstrap.",
            ];
        }
    }
}
