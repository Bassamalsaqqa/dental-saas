<?php
declare(strict_types=1);

namespace Scripts;

/**
 * Isolated Child Process Connection Bridge for Developer Sandbox
 *
 * Guarantees that EVERY CodeIgniter 4 database connection (default, tests, models,
 * migrations, seeders, and services) binds strictly to the verified developer sandbox
 * target (127.0.0.1:3307/democa_dental_saas_dev) BEFORE framework boot, WITHOUT
 * editing or requiring modifications to the Owner's existing .env file.
 *
 * POLICY INVARIANTS:
 *  - CLI-only execution.
 *  - Reads private connection credentials strictly from DENTA_VERIFIED_DEV_SANDBOX_CONNECTION.
 *  - Authoritative parent snapshot (containing connection + protected_databases) is strictly required.
 *  - Flat payloads, missing snapshot, or unverified targets are rejected immediately.
 *  - Never discloses raw connection payloads, credentials, or DSNs in exceptions or output.
 *  - Clears raw credentials payload from environment in finally block (on both success and failure).
 *  - Clears/blanks inherited alternative routes (DSN, failover, socket, subdriver) in BOTH default and tests groups.
 *  - Locks down outbound email transport with supported fail-closed configuration (email.protocol=smtp, SMTPHost='', SMTPPort=0, mailPath='/dev/null').
 *  - Provides auditDatabaseConfig() helper to audit instantiated Config\Database values.
 */

if (PHP_SAPI !== 'cli') {
    exit(2);
}

require_once __DIR__ . '/DeveloperSandboxPreflight.php';

final class DeveloperSandboxConnectionBootstrap
{
    /**
     * Audit an instantiated Config\Database instance to verify that neither
     * default nor tests connection groups are poisoned by inherited DSN,
     * failover, socket, subdriver, or unauthorized host/database/port.
     *
     * @param mixed $config Instantiated Config\Database object
     * @return string[] Errors encountered
     */
    public static function auditDatabaseConfig($config): array
    {
        $errors = [];

        foreach (['default', 'tests'] as $group) {
            $groupConfig = $config->{$group} ?? null;
            if (!is_array($groupConfig)) {
                $errors[] = "CRITICAL ISOLATION AUDIT: Database group '{$group}' is missing or malformed.";
                continue;
            }

            $db = (string) ($groupConfig['database'] ?? '');
            if ($db !== \Scripts\DeveloperSandboxPreflight::APPROVED_DEFAULT_SANDBOX_DB) {
                $errors[] = "CRITICAL ISOLATION AUDIT: Database group '{$group}' database is '{$db}', expected '" . \Scripts\DeveloperSandboxPreflight::APPROVED_DEFAULT_SANDBOX_DB . "'.";
            }

            $port = (int) ($groupConfig['port'] ?? 0);
            if ($port !== 3307) {
                $errors[] = "CRITICAL ISOLATION AUDIT: Database group '{$group}' port is {$port}, expected 3307.";
            }

            $host = strtolower((string) ($groupConfig['hostname'] ?? ''));
            if (!in_array($host, ['127.0.0.1', 'localhost', '::1', 'p:127.0.0.1', 'p:localhost'], true)) {
                $errors[] = "CRITICAL ISOLATION AUDIT: Database group '{$group}' hostname '{$host}' is not local loopback.";
            }

            if (!empty($groupConfig['DSN'])) {
                $errors[] = "CRITICAL ISOLATION AUDIT: Database group '{$group}' has non-empty DSN override.";
            }

            if (!empty($groupConfig['failover'])) {
                $errors[] = "CRITICAL ISOLATION AUDIT: Database group '{$group}' has non-empty failover configuration.";
            }

            if (!empty($groupConfig['socket'])) {
                $errors[] = "CRITICAL ISOLATION AUDIT: Database group '{$group}' has non-empty socket configuration.";
            }

            if (!empty($groupConfig['subdriver'])) {
                $errors[] = "CRITICAL ISOLATION AUDIT: Database group '{$group}' has non-empty subdriver configuration.";
            }
        }

        return $errors;
    }
}

// Procedural bridge execution for child process
$isDirectCli = isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__);
if (!$isDirectCli && getenv('DENTA_VERIFIED_DEV_SANDBOX_CONNECTION') === false) {
    return;
}

try {
    try {
        $rawPayload = (string) getenv('DENTA_VERIFIED_DEV_SANDBOX_CONNECTION');
        $payload = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);
    } catch (\Throwable $error) {
        $payload = null;
    }

    // Authoritative parent snapshot envelope is strictly required.
    // Flat payloads or missing/empty protected_databases are refused without guessing or defaulting.
    if (!is_array($payload)
        || !isset($payload['connection']) || !is_array($payload['connection'])
        || !isset($payload['protected_databases']) || !is_array($payload['protected_databases'])
        || empty($payload['protected_databases']['database']) || !is_string($payload['protected_databases']['database'])
        || trim($payload['protected_databases']['database']) === ''
    ) {
        throw new \RuntimeException('Verified isolated dev sandbox connection required.');
    }

    $connection = $payload['connection'];
    $protectedDbConfig = $payload['protected_databases'];

    $allowedKeys = ['hostname', 'port', 'database', 'username', 'password', 'DBDriver', 'DBPrefix'];

    if (!is_array($connection)
        || array_diff(array_keys($connection), $allowedKeys)
        || array_diff($allowedKeys, array_keys($connection))
        || array_filter($connection, static fn($value) => !is_string($value) && !is_int($value))
        || \Scripts\DeveloperSandboxPreflight::validateConfigBeforeConnection($connection, $protectedDbConfig)
    ) {
        // Fail closed without disclosing the input payload or credentials
        throw new \RuntimeException('Verified isolated dev sandbox connection required.');
    }

    // 1. Clear inherited alternative connection routes in BOTH default and tests groups
    $alternativeRoutes = ['DSN', 'failover', 'subdriver', 'socket', 'failover.0.hostname'];
    foreach (['database.default.', 'database.tests.'] as $prefix) {
        foreach ($alternativeRoutes as $alt) {
            $altEnvKey = $prefix . $alt;
            putenv($altEnvKey . '=');
            $_ENV[$altEnvKey] = '';
            $_SERVER[$altEnvKey] = '';
        }
    }

    // 2. Bind connection parameters for both 'default' and 'tests' connection groups
    foreach ($connection as $key => $value) {
        $valueStr = (string) $value;
        $encoded = $key === 'DBPrefix' ? $valueStr : ($valueStr === '' ? 'empty' : ($valueStr === '0' ? 0 : $valueStr));

        $defaultEnvKey = 'database.default.' . $key;
        putenv($defaultEnvKey . '=' . $encoded);
        $_ENV[$defaultEnvKey] = $encoded;
        $_SERVER[$defaultEnvKey] = $encoded;

        $testsEnvKey = 'database.tests.' . $key;
        putenv($testsEnvKey . '=' . $encoded);
        $_ENV[$testsEnvKey] = $encoded;
        $_SERVER[$testsEnvKey] = $encoded;
    }

    // 3. Supported fail-closed email transport lockdown in the child process environment
    // Setting protocol=smtp with SMTPHost='' ensures CI4 Email::sendWithSmtp() returns false immediately
    // without ever invoking PHP mail(), popen(), or opening sockets.
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

} finally {
    // Clear the raw connection payload on ALL exit paths (both success and failure)
    putenv('DENTA_VERIFIED_DEV_SANDBOX_CONNECTION');
    unset($_ENV['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION'], $_SERVER['DENTA_VERIFIED_DEV_SANDBOX_CONNECTION']);
}
