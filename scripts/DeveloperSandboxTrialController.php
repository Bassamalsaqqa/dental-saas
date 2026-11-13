<?php
/**
 * Developer Sandbox Synthetic Bootstrap Trial Controller & Verifier (VERIFIER-ONLY)
 *
 * Implements an isolated, auditable, portable verifier for the developer sandbox.
 * Enforces:
 *  - Native incremental migration batch validation (batch >= 1, not forced to 1)
 *  - Exact migration source set validation against immutable reviewed manifest (7427b4a4)
 *  - Dynamic RBAC derivation from App\Config\Permissions (zero hardcoding, duplicates/extras/mapping checks)
 *  - Defined application tables validation (18 foundation + 50 migrated [or 58 all-source] + 1 marker)
 *  - Fail-closed outbound messaging lockdown validation (delegated to shared production validator)
 *  - Canonical settings verification (no invented keys, verified against Executor canonical settings)
 *  - Schema constraints, CHECK TABLE fail-closed integrity, and FK orphan verification
 *
 * POLICY INVARIANTS:
 *  - Pure verifier only: NO daemon orchestration, NO child process spawning, NO destructive teardowns.
 *  - Live execution is strictly FORBIDDEN until supervisor review.
 *  - Returns structured diagnostics with zero credential risk.
 */

declare(strict_types=1);

namespace Scripts;

require_once __DIR__ . '/DeveloperSandboxPreflight.php';
require_once __DIR__ . '/DeveloperSandboxExecutor.php';
require_once dirname(__DIR__) . '/tests/_support/Database/LegacyBaseSchema.php';
require_once dirname(__DIR__) . '/app/Config/Permissions.php';

use Scripts\DeveloperSandboxPreflight;
use Scripts\DeveloperSandboxExecutor;
use Tests\Support\Database\LegacyBaseSchema;
use App\Config\Permissions;

final class DeveloperSandboxTrialController
{
    public const DEFAULT_PORT = 3307;
    public const DEFAULT_SCHEMA = DeveloperSandboxPreflight::APPROVED_DEFAULT_SANDBOX_DB;
    public const DEFAULT_MODE = DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE;
    public const MODE_ALL_SOURCE = DeveloperSandboxExecutor::MODE_ALL_SOURCE;
    public const EXPECTED_MIGRATION_COUNT_BASELINE = 77;
    public const EXPECTED_MIGRATION_COUNT_ALL_SOURCE = 85;

    /**
     * DB-FREE VERIFIER: Verifies migration history rows.
     * Handles native CodeIgniter incremental batch numbers (batch >= 1)
     * and verifies exact source set against immutable reviewed manifest.
     *
     * @param array<int, array<string, mixed>> $rows
     * @param int $expectedCount
     * @param string $mode
     * @return array{valid: bool, count: int, batches: array<int, int>, errors: string[]}
     */
    public static function verifyMigrationBatches(
        array $rows,
        int $expectedCount = self::EXPECTED_MIGRATION_COUNT_BASELINE,
        string $mode = self::DEFAULT_MODE
    ): array {
        $errors = [];
        $batches = [];
        $count = count($rows);

        if ($count !== $expectedCount) {
            $errors[] = "Migration count mismatch: expected exactly {$expectedCount}, found {$count}.";
        }

        $manifest = DeveloperSandboxExecutor::REVIEWED_MIGRATION_MANIFEST;
        $seenVersions = [];
        $seenClasses = [];

        foreach ($rows as $index => $row) {
            $id = $row['id'] ?? null;
            $version = (string) ($row['version'] ?? '');
            $class = (string) ($row['class'] ?? '');
            $batch = $row['batch'] ?? null;

            if (!is_numeric($id) || (int) $id <= 0) {
                $errors[] = "Row [{$index}]: invalid or non-positive migration id.";
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}-\d{6}$/', $version)) {
                $errors[] = "Row [{$index}] (id {$id}): invalid migration version format '{$version}'.";
            }

            if (!str_starts_with($class, 'App\\Database\\Migrations\\')) {
                $errors[] = "Row [{$index}] (id {$id}): unexpected class namespace '{$class}'.";
            }

            if (!is_numeric($batch) || (int) $batch < 1) {
                $errors[] = "Row [{$index}] (id {$id}): invalid batch '{$batch}'; must be an integer >= 1.";
            } else {
                $b = (int) $batch;
                $batches[$b] = ($batches[$b] ?? 0) + 1;
            }

            // Check for duplicates
            if (isset($seenVersions[$version])) {
                $errors[] = "Duplicate migration version '{$version}' detected at row [{$index}].";
            }
            $seenVersions[$version] = true;

            if (isset($seenClasses[$class])) {
                $errors[] = "Duplicate migration class '{$class}' detected at row [{$index}].";
            }
            $seenClasses[$class] = true;

            // Verify version exists in immutable manifest
            $versionFoundInManifest = false;
            foreach (array_keys($manifest) as $filename) {
                if (str_starts_with($filename, $version . '_')) {
                    $versionFoundInManifest = true;
                    break;
                }
            }
            if (!$versionFoundInManifest) {
                $errors[] = "Row [{$index}] (id {$id}): version '{$version}' is not in the reviewed migration manifest.";
            }
        }

        return [
            'valid' => empty($errors),
            'count' => $count,
            'batches' => $batches,
            'errors' => $errors,
        ];
    }

    /**
     * DB-FREE VERIFIER: Verifies application tables against defined variables.
     * Enforces exact set match: no missing, no extras, no duplicates.
     *
     * @param string[] $existingTables
     * @param string $mode
     * @return array{valid: bool, count: int, expected_count: int, missing: string[], extra: string[], errors: string[]}
     */
    public static function verifyAppTables(array $existingTables, string $mode = self::DEFAULT_MODE): array
    {
        $errors = [];
        $normalized = array_map('strtolower', $existingTables);
        $existingMap = [];
        foreach ($normalized as $t) {
            if (isset($existingMap[$t])) {
                $errors[] = "Duplicate table '{$t}' detected in database.";
            }
            $existingMap[$t] = true;
        }

        $expectedCount = ($mode === DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE)
            ? DeveloperSandboxExecutor::EXPECTED_TOTAL_TABLES_BASELINE
            : DeveloperSandboxExecutor::EXPECTED_TOTAL_TABLES_ALL_SOURCE;

        // Defined table variables: 18 foundation tables from LegacyBaseSchema
        $foundationTables = [
            'users', 'groups', 'users_groups', 'login_attempts',
            'patients', 'appointments', 'finances', 'examinations',
            'prescriptions', 'settings', 'treatments', 'odontograms',
            'inventory', 'inventory_usage', 'appointment_summary',
            'financial_summary', 'patient_examination_summary', 'permission_audit_log',
        ];

        // Core migrated application tables (49 baseline migrations + migrations table = 50)
        $coreMigratedTables = [
            'migrations',
            'activity_logs', 'api_tokens', 'clinic_notification_channels',
            'clinic_subscriptions', 'clinic_users', 'clinical_charge_lines',
            'clinical_procedure_status_history', 'clinical_procedures', 'clinics',
            'control_plane_audits', 'control_plane_invoice_payments', 'control_plane_invoices',
            'email_templates', 'examination_note_addenda', 'examination_note_signatures',
            'file_attachments', 'finance_liquidity_transfers', 'finance_payments',
            'finance_settlement_requests', 'inventory_batches', 'inventory_item_templates',
            'inventory_reorder_items', 'inventory_stock_movements', 'inventory_supplier_links',
            'job_audits', 'notification_attempts', 'notifications',
            'patient_odontogram_snapshots', 'patient_whatsapp', 'permissions',
            'plan_audits', 'plan_requests', 'plan_usage', 'plans',
            'platform_secrets', 'provider_payout_payments', 'provider_payouts',
            'role_permissions', 'roles', 'suppliers',
            'treatment_contract_items', 'treatment_contract_odontogram_snapshots',
            'treatment_contract_signatures', 'treatment_contracts',
            'user_permissions', 'user_roles', 'whatsapp_delivery_events',
            'whatsapp_optouts', 'whatsapp_tenant_policies',
        ];

        // Optional tables in ALL_SOURCE mode (8 tables)
        $allSourceOptionalTables = [
            'appointment_contact_notes', 'attachment_variants', 'doctor_schedules',
            'examination_note_governance', 'finance_checkout_requests',
            'invoice_correction_ledger', 'patient_whatsapp_scoped', 'studio_theme_history',
        ];

        $markerTable = '_dev_sandbox_marker';

        $expectedList = array_merge($foundationTables, $coreMigratedTables, [$markerTable]);
        if ($mode === self::MODE_ALL_SOURCE) {
            $expectedList = array_merge($expectedList, $allSourceOptionalTables);
        }

        $expectedMap = array_flip(array_map('strtolower', $expectedList));

        $missing = [];
        foreach ($expectedList as $requiredTable) {
            if (!isset($existingMap[strtolower($requiredTable)])) {
                $missing[] = $requiredTable;
                $errors[] = "Required table '{$requiredTable}' is missing from database.";
            }
        }

        $extra = [];
        foreach ($normalized as $table) {
            if (!isset($expectedMap[$table])) {
                $extra[] = $table;
                $errors[] = "Unexpected extra table '{$table}' found in database.";
            }
        }

        $actualCount = count($existingTables);
        if ($actualCount !== $expectedCount) {
            $errors[] = "Table count mismatch: expected {$expectedCount} tables (mode: {$mode}), found {$actualCount}.";
        }

        return [
            'valid' => empty($errors),
            'count' => $actualCount,
            'expected_count' => $expectedCount,
            'missing' => $missing,
            'extra' => $extra,
            'errors' => $errors,
        ];
    }

    /**
     * DB-FREE VERIFIER: Dynamically verifies RBAC permissions, roles, and role_permissions mappings
     * against App\Config\Permissions. Zero hardcoded totals.
     * Enforces:
     *  - Exact permission name and count match
     *  - No duplicate permissions, no unapproved extra permissions
     *  - Exact role slug and count match
     *  - No duplicate roles, no unapproved extra roles
     *  - Exact role-to-permission mapping and grant state (if role_permissions provided)
     *
     * @param array<int, array<string, mixed>> $dbPermissions
     * @param array<int, array<string, mixed>> $dbRoles
     * @param array<int, array<string, mixed>>|null $dbRolePermissions Optional role_permissions mappings
     * @return array{valid: bool, permissions_count: int, roles_count: int, role_permissions_checked: bool, errors: string[]}
     */
    public static function verifyRbacDynamic(
        array $dbPermissions,
        array $dbRoles,
        ?array $dbRolePermissions = null
    ): array {
        $errors = [];

        // 1. Derive expected permissions from App\Config\Permissions
        $defPermissions = Permissions::getDefaultPermissions();
        $expectedPermNames = [];
        foreach ($defPermissions as $module => $actions) {
            foreach ($actions as $action => $desc) {
                $expectedPermNames[$module . '.' . $action] = [
                    'module' => $module,
                    'action' => $action,
                ];
            }
        }

        $existingPermMap = [];
        $permIdToName = [];
        foreach ($dbPermissions as $index => $perm) {
            $name = (string) ($perm['name'] ?? '');
            $id = $perm['id'] ?? null;
            if ($name === '') {
                $errors[] = "Permission at index [{$index}] has empty name.";
                continue;
            }
            if (isset($existingPermMap[$name])) {
                $errors[] = "Duplicate permission '{$name}' detected in permissions table.";
            }
            $existingPermMap[$name] = $perm;
            if ($id !== null) {
                $permIdToName[$id] = $name;
            }
        }

        foreach ($expectedPermNames as $expectedName => $spec) {
            if (!isset($existingPermMap[$expectedName])) {
                $errors[] = "Dynamic permission '{$expectedName}' missing from permissions table.";
            }
        }

        foreach (array_keys($existingPermMap) as $existingName) {
            if (!isset($expectedPermNames[$existingName])) {
                $errors[] = "Unexpected extra permission '{$existingName}' found in permissions table.";
            }
        }

        if (count($dbPermissions) !== count($expectedPermNames)) {
            $errors[] = "Permission count mismatch: expected " . count($expectedPermNames) . ", found " . count($dbPermissions) . ".";
        }

        // 2. Derive expected roles from App\Config\Permissions
        $defRoles = Permissions::getDefaultRoles();
        $expectedRoleSlugs = array_keys($defRoles);

        $existingRoleSlugs = [];
        $roleIdToSlug = [];
        foreach ($dbRoles as $index => $role) {
            $slug = (string) ($role['slug'] ?? ($role['name'] ?? ''));
            $id = $role['id'] ?? null;
            if ($slug === '') {
                $errors[] = "Role at index [{$index}] has empty slug.";
                continue;
            }
            if (isset($existingRoleSlugs[$slug])) {
                $errors[] = "Duplicate role slug '{$slug}' detected in roles table.";
            }
            $existingRoleSlugs[$slug] = true;
            if ($id !== null) {
                $roleIdToSlug[$id] = $slug;
            }
        }

        foreach ($expectedRoleSlugs as $roleSlug) {
            if (!isset($existingRoleSlugs[$roleSlug])) {
                $errors[] = "Dynamic role '{$roleSlug}' missing from roles table.";
            }
        }

        foreach (array_keys($existingRoleSlugs) as $existingSlug) {
            if (!in_array($existingSlug, $expectedRoleSlugs, true)) {
                $errors[] = "Unexpected extra role '{$existingSlug}' found in roles table.";
            }
        }

        if (count($dbRoles) !== count($expectedRoleSlugs)) {
            $errors[] = "Role count mismatch: expected " . count($expectedRoleSlugs) . ", found " . count($dbRoles) . ".";
        }

        // 3. Optional role_permissions mapping verification
        $rolePermissionsChecked = false;
        if ($dbRolePermissions !== null) {
            $rolePermissionsChecked = true;
            $roleGrantedPerms = [];
            $seenPairs = [];

            foreach ($dbRolePermissions as $idx => $rp) {
                $roleId = $rp['role_id'] ?? null;
                $permId = $rp['permission_id'] ?? null;
                $granted = $rp['granted'] ?? null;

                $roleSlug = $roleIdToSlug[$roleId] ?? (string) $roleId;
                $permName = $permIdToName[$permId] ?? (string) $permId;

                $pairKey = $roleSlug . '::' . $permName;
                if (isset($seenPairs[$pairKey])) {
                    $errors[] = "Duplicate role_permission mapping ({$pairKey}) detected at index [{$idx}].";
                }
                $seenPairs[$pairKey] = true;

                if ((int) $granted !== 1) {
                    $errors[] = "Role permission mapping ({$pairKey}) has invalid granted value '{$granted}' (must be 1).";
                }

                $roleGrantedPerms[$roleSlug][$permName] = true;
            }

            foreach ($defRoles as $roleSlug => $roleSpec) {
                $assigned = $roleSpec['permissions'];
                $grantedMap = $roleGrantedPerms[$roleSlug] ?? [];

                if ($assigned === '*') {
                    // All permissions must be granted
                    foreach (array_keys($expectedPermNames) as $pName) {
                        if (!isset($grantedMap[$pName])) {
                            $errors[] = "Wildcard role '{$roleSlug}' missing granted permission '{$pName}'.";
                        }
                    }
                } elseif (is_array($assigned)) {
                    $expectedForRole = [];
                    foreach ($assigned as $m => $actions) {
                        foreach ($actions as $a) {
                            $expectedForRole[$m . '.' . $a] = true;
                        }
                    }

                    foreach (array_keys($expectedForRole) as $expectedP) {
                        if (!isset($grantedMap[$expectedP])) {
                            $errors[] = "Role '{$roleSlug}' missing granted permission '{$expectedP}'.";
                        }
                    }

                    foreach (array_keys($grantedMap) as $grantedP) {
                        if (!isset($expectedForRole[$grantedP])) {
                            $errors[] = "Role '{$roleSlug}' granted unexpected permission '{$grantedP}'.";
                        }
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'permissions_count' => count($dbPermissions),
            'roles_count' => count($dbRoles),
            'role_permissions_checked' => $rolePermissionsChecked,
            'errors' => $errors,
        ];
    }

    /**
     * DB-FREE VERIFIER: Verifies canonical settings against Executor canonical definition.
     * Enforces:
     *  - Clinic settings: timezone, currency, currency_position, booking_chair_count,
     *    booking_chair_names, dynamic_doctor_scheduling_enabled (strictly 0).
     *  - ZERO invented keys (such as currency_symbol).
     *
     * @param array<string, mixed> $settings Map of setting_key => setting_value
     * @param string $scope 'clinic' (default) or 'global' or 'all'
     * @return array{valid: bool, settings_count: int, scope: string, errors: string[]}
     */
    public static function verifyCanonicalSettings(array $settings, string $scope = 'clinic'): array
    {
        $errors = [];
        $executor = new DeveloperSandboxExecutor();
        $canonicalDef = $executor->getCanonicalSettingsDefinition();

        $requiredKeys = [];
        if ($scope === 'clinic' || $scope === 'all') {
            $requiredKeys = array_merge($requiredKeys, array_keys($canonicalDef['clinic']));
        }
        if ($scope === 'global' || $scope === 'all') {
            $requiredKeys = array_merge($requiredKeys, array_keys($canonicalDef['global']));
        }

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $settings)) {
                $errors[] = "Required setting '{$key}' (scope: {$scope}) is missing from settings.";
            } else {
                $val = $settings[$key];
                if ($val === null || (is_string($val) && trim($val) === '')) {
                    $errors[] = "Required setting '{$key}' (scope: {$scope}) is empty.";
                }
            }
        }

        // Strict invariant: dynamic scheduling must be OFF (0)
        if (array_key_exists('dynamic_doctor_scheduling_enabled', $settings)) {
            $dynVal = $settings['dynamic_doctor_scheduling_enabled'];
            if ((int) $dynVal !== 0) {
                $errors[] = "Strict invariant violation: dynamic_doctor_scheduling_enabled must be 0 (found " . var_export($dynVal, true) . ").";
            }
        }

        // Enforce zero invented / extra settings keys (e.g. currency_symbol)
        foreach (array_keys($settings) as $actualKey) {
            if (!in_array($actualKey, $requiredKeys, true)) {
                $errors[] = "Unexpected invented/extra setting key '{$actualKey}' (scope: {$scope}) found in settings.";
            }
        }

        return [
            'valid' => empty($errors),
            'settings_count' => count($settings),
            'scope' => $scope,
            'errors' => $errors,
        ];
    }

    /**
     * Shared pure validator: delegates to DeveloperSandboxExecutor::verifyOutboundLockdown.
     * Enforces EACH channel (sms, whatsapp, email) present as an array with enabled strictly boolean false.
     *
     * @param array<string, mixed>|null $planRow
     * @param array<string, mixed> $envSettings
     * @return array{valid: bool, errors: string[]}
     */
    public static function verifyOutboundLockdown(?array $planRow, array $envSettings): array
    {
        return DeveloperSandboxExecutor::verifyOutboundLockdown($planRow, $envSettings);
    }

    /**
     * DB-FREE VERIFIER: Verifies foreign key orphan queries.
     * Fails closed if query failed (negative/non-numeric count) or orphans found (> 0).
     *
     * @param array<string, int|mixed> $orphanCounts Map of relationship_label => orphan_count
     * @return array{valid: bool, relationships_checked: int, orphans_found: int, errors: string[]}
     */
    public static function verifyForeignKeyOrphans(array $orphanCounts): array
    {
        $errors = [];
        $totalOrphans = 0;

        if (empty($orphanCounts)) {
            $errors[] = "No foreign key relationships provided for orphan verification.";
        }

        foreach ($orphanCounts as $rel => $count) {
            if (!is_numeric($count) || (int) $count < 0) {
                $errors[] = "FK orphan query for '{$rel}' failed or returned invalid count: " . var_export($count, true);
                continue;
            }
            $c = (int) $count;
            if ($c > 0) {
                $totalOrphans += $c;
                $errors[] = "Foreign key integrity violation: found {$c} orphaned records in '{$rel}'.";
            }
        }

        return [
            'valid' => empty($errors),
            'relationships_checked' => count($orphanCounts),
            'orphans_found' => $totalOrphans,
            'errors' => $errors,
        ];
    }

    /**
     * DB-FREE VERIFIER: Verifies CHECK TABLE results across tables.
     * Fails closed if query returned false/null or if table health is not OK.
     *
     * @param array<string, array<string, mixed>|string|false|null> $checkResults Map of table => result row or msg_text
     * @return array{valid: bool, tables_checked: int, errors: string[]}
     */
    public static function verifyTableCheckIntegrity(array $checkResults): array
    {
        $errors = [];

        if (empty($checkResults)) {
            $errors[] = "No table check results provided for integrity verification.";
        }

        foreach ($checkResults as $table => $result) {
            if ($result === false || $result === null) {
                $errors[] = "Table `{$table}` CHECK TABLE query failed (returned false/null).";
                continue;
            }

            $msgText = '';
            if (is_array($result)) {
                $msgText = (string) ($result['Msg_text'] ?? ($result['msg_text'] ?? ''));
            } else {
                $msgText = (string) $result;
            }

            $msgType = is_array($result) ? strtolower((string) ($result['Msg_type'] ?? ($result['msg_type'] ?? 'status'))) : 'status';
            $lower = strtolower($msgText);
            if ($msgType === 'error') {
                $errors[] = "Table `{$table}` CHECK TABLE reported error type: {$msgText}";
            } elseif (!str_contains($lower, 'ok') && !str_contains($lower, 'already up to date')) {
                $errors[] = "Table `{$table}` CHECK TABLE reported unhealthy status: {$msgText}";
            }
        }

        return [
            'valid' => empty($errors),
            'tables_checked' => count($checkResults),
            'errors' => $errors,
        ];
    }

    /**
     * DB-FREE VERIFIER: Verifies physical foreign key constraint structure against required relationship definitions.
     * Fails closed if foreign key constraints are missing or reference wrong targets.
     *
     * @param array<int, array<string, mixed>> $existingFks List of existing foreign keys (e.g. from information_schema)
     * @param array<int, array{table: string, column: string, referenced_table: string, referenced_column: string}> $expectedFks
     * @return array{valid: bool, constraints_checked: int, missing: array<int, array{table: string, column: string, referenced_table: string, referenced_column: string}>, errors: string[]}
     */
    public static function verifyForeignKeyStructure(array $existingFks, array $expectedFks): array
    {
        $errors = [];
        $missing = [];

        if (empty($expectedFks)) {
            $errors[] = "No expected foreign key relationships provided for structure verification.";
        }

        // Build normalized map of existing foreign keys: table.column -> referenced_table.referenced_column
        $existingMap = [];
        foreach ($existingFks as $fk) {
            $t = strtolower((string) ($fk['TABLE_NAME'] ?? ($fk['table'] ?? '')));
            $c = strtolower((string) ($fk['COLUMN_NAME'] ?? ($fk['column'] ?? '')));
            $rt = strtolower((string) ($fk['REFERENCED_TABLE_NAME'] ?? ($fk['referenced_table'] ?? '')));
            $rc = strtolower((string) ($fk['REFERENCED_COLUMN_NAME'] ?? ($fk['referenced_column'] ?? '')));
            if ($t !== '' && $c !== '') {
                $existingMap["{$t}.{$c}"] = "{$rt}.{$rc}";
            }
        }

        foreach ($expectedFks as $exp) {
            $t = strtolower($exp['table'] ?? '');
            $c = strtolower($exp['column'] ?? '');
            $rt = strtolower($exp['referenced_table'] ?? '');
            $rc = strtolower($exp['referenced_column'] ?? '');
            $key = "{$t}.{$c}";
            $target = "{$rt}.{$rc}";

            if (!isset($existingMap[$key])) {
                $missing[] = $exp;
                $errors[] = "Foreign key constraint on '{$key}' referencing '{$target}' is missing from schema.";
            } elseif ($existingMap[$key] !== $target) {
                $errors[] = "Foreign key constraint on '{$key}' references '{$existingMap[$key]}', expected '{$target}'.";
            }
        }

        return [
            'valid' => empty($errors),
            'constraints_checked' => count($expectedFks),
            'missing' => $missing,
            'errors' => $errors,
        ];
    }

    /**
     * Verifies that the migration directory matches the immutable reviewed manifest.
     *
     * @param string $migrationDir
     * @return array{valid: bool, reviewed_commit: string, total_reviewed_files: int, errors: string[]}
     */
    public static function verifyImmutableReviewedManifest(string $migrationDir = ''): array
    {
        return DeveloperSandboxExecutor::verifyMigrationManifestAgainstReviewed($migrationDir);
    }
}

// CLI entry point (VERIFIER-ONLY)
if (PHP_SAPI === 'cli' && isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    $cliArgs = array_slice($argv, 1);
    $action = $cliArgs[0] ?? '--help';

    if ($action === '--help' || $action === '-h') {
        echo "DeveloperSandboxTrialController - Pure Sandbox Verifier Utility (VERIFIER-ONLY)\n\n";
        echo "Usage: php scripts/DeveloperSandboxTrialController.php [action]\n\n";
        echo "Actions:\n";
        echo "  --verify-manifest     Verify app migrations against immutable reviewed manifest (7427b4a4)\n";
        echo "  --verify-sources      Verify pinned sources (LegacyBaseSchema, Worker, Preflight)\n";
        echo "  --version             Show verifier version and policy status\n";
        echo "  --help                Show this help message\n\n";
        echo "NOTE: Live database execution is FORBIDDEN. Controller is verifier-only.\n";
        exit(0);
    }

    if ($action === '--version') {
        echo "DeveloperSandboxTrialController v2.0 (VERIFIER-ONLY)\n";
        echo "Reviewed Commit Pin: " . DeveloperSandboxExecutor::REVIEWED_FEATURE_COMMIT . "\n";
        echo "Status: Live execution refused until supervisor review.\n";
        exit(0);
    }

    if ($action === '--verify-manifest') {
        $res = DeveloperSandboxTrialController::verifyImmutableReviewedManifest();
        echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        exit($res['valid'] ? 0 : 1);
    }

    if ($action === '--verify-sources') {
        $res = DeveloperSandboxExecutor::verifyReviewedSourcesAgainstPinned();
        echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        exit($res['valid'] ? 0 : 1);
    }

    if ($action === '--run-trial') {
        fwrite(STDERR, "CRITICAL REFUSAL: Live execution is forbidden. Controller is verifier-only; database daemon orchestration is prohibited until supervisor review.\n");
        exit(1);
    }

    fwrite(STDERR, "Unknown action '{$action}'. Run with --help for usage.\n");
    exit(1);
}
