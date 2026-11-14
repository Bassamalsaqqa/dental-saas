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
     * Enforces:
     *  - Exact per-mode filename version and class identity set derived from reviewed manifest
     *  - Native CodeIgniter incremental batch numbers (strict positive integer, batch >= 1)
     *  - Strict positive integer migration id (rejects floats/strings like 1.5)
     *  - Excludes dormant 8 migrations for baseline mode
     *  - Rejects arbitrary fake classes and duplicate entries
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

        $allowedModes = [DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, self::MODE_ALL_SOURCE];
        if (!in_array($mode, $allowedModes, true)) {
            return [
                'valid' => false,
                'count' => $count,
                'batches' => [],
                'errors' => ["Unknown migration verification mode '{$mode}'. Fail closed."],
            ];
        }

        $manifest = DeveloperSandboxExecutor::REVIEWED_MIGRATION_MANIFEST;
        $excluded = ($mode === DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE)
            ? array_flip(DeveloperSandboxExecutor::EXCLUDED_MIGRATIONS)
            : [];

        $allowedMigrations = [];
        foreach (array_keys($manifest) as $filename) {
            if (isset($excluded[$filename])) {
                continue;
            }
            if (preg_match('/^(\d{4}-\d{2}-\d{2}-\d{6})_(.+)\.php$/', $filename, $m)) {
                $allowedMigrations[$m[1]] = [
                    'class' => 'App\\Database\\Migrations\\' . $m[2],
                    'filename' => $filename,
                ];
            }
        }

        $expectedTargetCount = count($allowedMigrations);
        if ($expectedCount !== $expectedTargetCount) {
            $errors[] = "Specified expected migration count mismatch for mode '{$mode}': expected {$expectedTargetCount}, specified {$expectedCount}.";
        }
        if ($count !== $expectedTargetCount) {
            $errors[] = "Migration count mismatch: expected exactly {$expectedTargetCount} for mode '{$mode}', found {$count}.";
        }

        $seenVersions = [];
        $seenClasses = [];
        $seenIds = [];

        foreach ($rows as $index => $row) {
            $id = $row['id'] ?? null;
            $version = (string) ($row['version'] ?? '');
            $class = (string) ($row['class'] ?? '');
            $batch = $row['batch'] ?? null;

            // Strict positive integer validation: reject floats (1.5), non-numeric, or <= 0
            if ((!is_int($id) && (!is_string($id) || !ctype_digit($id))) || (int) $id <= 0) {
                $errors[] = "Row [{$index}]: invalid or non-positive migration id " . var_export($id, true) . "; must be a strict positive integer.";
            } else {
                $idInt = (int) $id;
                if (isset($seenIds[$idInt])) {
                    $errors[] = "Duplicate migration id '{$idInt}' detected at row [{$index}].";
                }
                $seenIds[$idInt] = true;
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}-\d{6}$/', $version)) {
                $errors[] = "Row [{$index}] (id " . var_export($id, true) . "): invalid migration version format '{$version}'.";
            }

            if (!is_string($class) || !str_starts_with($class, 'App\\Database\\Migrations\\')) {
                $errors[] = "Row [{$index}] (id " . var_export($id, true) . "): unexpected class namespace '{$class}'.";
            }

            // Strict positive integer batch validation: reject floats (1.5), non-numeric, or < 1
            if ((!is_int($batch) && (!is_string($batch) || !ctype_digit($batch))) || (int) $batch < 1) {
                $errors[] = "Row [{$index}] (id " . var_export($id, true) . "): invalid batch '{$batch}'; must be an integer >= 1.";
            } else {
                $b = (int) $batch;
                $batches[$b] = ($batches[$b] ?? 0) + 1;
            }

            // Duplicate version check
            if (isset($seenVersions[$version])) {
                $errors[] = "Duplicate migration version '{$version}' detected at row [{$index}].";
            }
            $seenVersions[$version] = true;

            // Duplicate class check
            if (isset($seenClasses[$class])) {
                $errors[] = "Duplicate migration class '{$class}' detected at row [{$index}].";
            }
            $seenClasses[$class] = true;

            // Verify version and exact class identity against allowed per-mode set derived from reviewed manifest
            if (!isset($allowedMigrations[$version])) {
                $errors[] = "Row [{$index}] (id " . var_export($id, true) . "): version '{$version}' is not in the reviewed migration manifest for mode '{$mode}'.";
            } else {
                $expectedClass = $allowedMigrations[$version]['class'];
                if ($class !== $expectedClass) {
                    $errors[] = "Row [{$index}] (id " . var_export($id, true) . "): class mismatch for version '{$version}': expected '{$expectedClass}', found '{$class}'.";
                }
            }
        }

        // Verify no allowed migration is missing
        foreach (array_keys($allowedMigrations) as $expVer) {
            if (!isset($seenVersions[$expVer])) {
                $errors[] = "Required migration version '{$expVer}' missing for mode '{$mode}'.";
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
     * Enforces exact set match for MODE_PRODUCTION_BASELINE: no missing, no extras, no duplicates.
     * Explicitly refuses MODE_ALL_SOURCE with NOT_IMPLEMENTED until reviewed source-derived schema exists.
     *
     * @param string[] $existingTables
     * @param string $mode
     * @return array{valid: bool, status: string, count: int, expected_count: int, missing: string[], extra: string[], errors: string[]}
     */
    public static function verifyAppTables(array $existingTables, string $mode = self::DEFAULT_MODE): array
    {
        $errors = [];
        $actualCount = count($existingTables);

        if ($mode === self::MODE_ALL_SOURCE) {
            return [
                'valid' => false,
                'status' => 'NOT_IMPLEMENTED',
                'count' => $actualCount,
                'expected_count' => DeveloperSandboxExecutor::EXPECTED_TOTAL_TABLES_ALL_SOURCE,
                'missing' => [],
                'extra' => [],
                'errors' => ["MODE_ALL_SOURCE table verification is NOT_IMPLEMENTED: actual source-derived reviewed expected schema for dormant migrations has not been verified."],
            ];
        }

        if ($mode !== DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE) {
            return [
                'valid' => false,
                'status' => 'FAILED',
                'count' => $actualCount,
                'expected_count' => 0,
                'missing' => [],
                'extra' => [],
                'errors' => ["Unknown table verification mode '{$mode}'. Fail closed."],
            ];
        }

        $normalized = array_map('strtolower', $existingTables);
        $existingMap = [];
        foreach ($normalized as $t) {
            if (isset($existingMap[$t])) {
                $errors[] = "Duplicate table '{$t}' detected in database.";
            }
            $existingMap[$t] = true;
        }

        $expectedCount = DeveloperSandboxExecutor::EXPECTED_TOTAL_TABLES_BASELINE; // 69

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

        $markerTable = '_dev_sandbox_marker';

        $expectedList = array_merge($foundationTables, $coreMigratedTables, [$markerTable]);
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

        if ($actualCount !== $expectedCount) {
            $errors[] = "Table count mismatch: expected {$expectedCount} tables (mode: {$mode}), found {$actualCount}.";
        }

        return [
            'valid' => empty($errors),
            'status' => empty($errors) ? 'VERIFIED' : 'FAILED',
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
     *  - Exact permission name, module, action, and count match
     *  - No duplicate permissions, no unapproved extra permissions
     *  - Exact role slug and count match
     *  - Role scope validation: system roles must have null clinic_id
     *  - No duplicate roles, no unapproved extra roles
     *  - Exact role-to-permission mapping and grant state (if role_permissions provided)
     *  - Rejects orphan/unknown role_id and permission_id references in mappings
     *  - Rejects extra non-system permissions for wildcard roles
     *  - Explicitly flags partial result when role_permissions mappings are omitted
     *
     * @param array<int, array<string, mixed>> $dbPermissions
     * @param array<int, array<string, mixed>> $dbRoles
     * @param array<int, array<string, mixed>>|null $dbRolePermissions Optional role_permissions mappings
     * @return array{valid: bool, status: string, is_partial: bool, permissions_count: int, roles_count: int, role_permissions_checked: bool, errors: string[]}
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
            $module = isset($perm['module']) ? (string) $perm['module'] : '';
            $action = isset($perm['action']) ? (string) $perm['action'] : '';
            $canonicalKey = ($module !== '' && $action !== '') ? "{$module}.{$action}" : $name;
            $id = $perm['id'] ?? null;
            if ($name === '') {
                $errors[] = "Permission at index [{$index}] has empty name.";
                continue;
            }
            if (isset($existingPermMap[$canonicalKey])) {
                $errors[] = "Duplicate permission '{$canonicalKey}' detected in permissions table.";
            }
            $existingPermMap[$canonicalKey] = $perm;
            if ($id !== null) {
                $permIdToName[$id] = $canonicalKey;
            }

            // Verify permission module/action if provided
            if (isset($perm['module'])) {
                $expectedMod = $expectedPermNames[$name]['module'] ?? ($expectedPermNames[$canonicalKey]['module'] ?? null);
                if ($expectedMod !== null && $perm['module'] !== $expectedMod) {
                    $errors[] = "Permission '{$name}' at index [{$index}] has unexpected module '{$perm['module']}', expected '{$expectedMod}'.";
                }
            }
            if (isset($perm['action'])) {
                $expectedAct = $expectedPermNames[$name]['action'] ?? ($expectedPermNames[$canonicalKey]['action'] ?? null);
                if ($expectedAct !== null && $perm['action'] !== $expectedAct) {
                    $errors[] = "Permission '{$name}' at index [{$index}] has unexpected action '{$perm['action']}', expected '{$expectedAct}'.";
                }
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

            // Verify role scope / clinic: system default roles must not be clinic-scoped
            if (array_key_exists('clinic_id', $role) && $role['clinic_id'] !== null && $role['clinic_id'] !== 0 && $role['clinic_id'] !== '') {
                $errors[] = "Dynamic role '{$slug}' at index [{$index}] has invalid clinic_id scope " . var_export($role['clinic_id'], true) . "; system default roles must have null clinic_id.";
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
        $isPartial = ($dbRolePermissions === null);

        if ($dbRolePermissions !== null) {
            $rolePermissionsChecked = true;
            $roleGrantedPerms = [];
            $seenPairs = [];

            foreach ($dbRolePermissions as $idx => $rp) {
                $roleId = $rp['role_id'] ?? null;
                $permId = $rp['permission_id'] ?? null;
                $granted = $rp['granted'] ?? null;

                // Rejects orphan / unknown role_id and permission_id
                if ($roleId === null || !isset($roleIdToSlug[$roleId])) {
                    $errors[] = "Role permission mapping at index [{$idx}] references unknown or orphaned role_id " . var_export($roleId, true) . ".";
                    continue;
                }
                if ($permId === null || !isset($permIdToName[$permId])) {
                    $errors[] = "Role permission mapping at index [{$idx}] references unknown or orphaned permission_id " . var_export($permId, true) . ".";
                    continue;
                }

                $roleSlug = $roleIdToSlug[$roleId];
                $permName = $permIdToName[$permId];

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
                    // Rejects extra non-system permissions granted to wildcard role
                    foreach (array_keys($grantedMap) as $grantedP) {
                        if (!isset($expectedPermNames[$grantedP])) {
                            $errors[] = "Wildcard role '{$roleSlug}' granted unexpected non-system permission '{$grantedP}'.";
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
            'status' => empty($errors) ? ($isPartial ? 'PARTIAL' : 'VERIFIED') : 'FAILED',
            'is_partial' => $isPartial,
            'permissions_count' => count($dbPermissions),
            'roles_count' => count($dbRoles),
            'role_permissions_checked' => $rolePermissionsChecked,
            'errors' => $errors,
        ];
    }

    /**
     * Legitimate control plane global settings seeded by database migrations
     * (e.g. 2026-02-09-110000_AddControlPlaneSettingsDefaults).
     * These are valid schema settings, distinct from invented arbitrary keys.
     *
     * @var array<string, bool>
     */
    public const VALID_MIGRATION_GLOBAL_SETTINGS = [
        'platform_name' => true,
        'platform_support_email' => true,
        'platform_support_phone' => true,
        'platform_support_whatsapp_primary' => true,
        'platform_support_whatsapp_secondary' => true,
        'platform_support_website' => true,
        'retention_mode' => true,
        'retention_last_n' => true,
        'retention_days' => true,
        'control_plane_audit_retention_days' => true,
        'default_working_hours_start' => true,
        'default_working_hours_end' => true,
        'default_working_days' => true,
    ];

    /**
     * DB-FREE VERIFIER: Verifies canonical settings against Executor canonical definition.
     * Enforces:
     *  - Scope fail-closed validation ('clinic', 'global', or 'all')
     *  - Exact canonical VALUES (not merely key presence or loose coercion allowing junk)
     *  - Dynamic scheduling strictly 0 (rejects 'junk' or non-zero values)
     *  - ZERO invented keys (such as currency_symbol).
     *
     * @param array<string, mixed> $settings Map of setting_key => setting_value
     * @param string $scope 'clinic' (default) or 'global' or 'all'
     * @return array{valid: bool, settings_count: int, scope: string, errors: string[]}
     */
    public static function verifyCanonicalSettings(array $settings, string $scope = 'clinic'): array
    {
        $errors = [];
        $allowedScopes = ['clinic', 'global', 'all'];
        if (!in_array($scope, $allowedScopes, true)) {
            return [
                'valid' => false,
                'settings_count' => count($settings),
                'scope' => $scope,
                'errors' => ["Unknown settings verification scope '{$scope}'; must be one of: " . implode(', ', $allowedScopes) . ". Fail closed."],
            ];
        }

        $executor = new DeveloperSandboxExecutor();
        $canonicalDef = $executor->getCanonicalSettingsDefinition();

        $expectedDefs = [];
        if ($scope === 'clinic' || $scope === 'all') {
            $expectedDefs = array_merge($expectedDefs, $canonicalDef['clinic']);
        }
        if ($scope === 'global' || $scope === 'all') {
            $expectedDefs = array_merge($expectedDefs, $canonicalDef['global']);
        }

        // Validate presence and actual canonical values
        foreach ($expectedDefs as $key => $expectedVal) {
            if (!array_key_exists($key, $settings)) {
                $errors[] = "Required setting '{$key}' (scope: {$scope}) is missing from settings.";
                continue;
            }

            $actualVal = $settings[$key];

            // Strict invariant: dynamic doctor scheduling must be strictly 0
            if ($key === 'dynamic_doctor_scheduling_enabled') {
                if ($actualVal !== 0 && $actualVal !== '0') {
                    $errors[] = "Strict invariant violation: dynamic_doctor_scheduling_enabled must be 0 (found " . var_export($actualVal, true) . ").";
                }
                continue;
            }

            if (is_int($expectedVal)) {
                if ((!is_int($actualVal) && (!is_string($actualVal) || !ctype_digit($actualVal))) || (int) $actualVal !== $expectedVal) {
                    $errors[] = "Setting '{$key}' value mismatch: expected integer {$expectedVal}, found " . var_export($actualVal, true) . ".";
                }
            } elseif (is_array($expectedVal)) {
                if (is_string($actualVal)) {
                    $decoded = json_decode($actualVal, true);
                    if ($decoded !== $expectedVal) {
                        $errors[] = "Setting '{$key}' JSON value mismatch: expected " . json_encode($expectedVal) . ", found " . var_export($actualVal, true) . ".";
                    }
                } elseif (is_array($actualVal)) {
                    if ($actualVal !== $expectedVal) {
                        $errors[] = "Setting '{$key}' array value mismatch: expected " . json_encode($expectedVal) . ", found " . json_encode($actualVal) . ".";
                    }
                } else {
                    $errors[] = "Setting '{$key}' must be an array or JSON string, found " . var_export($actualVal, true) . ".";
                }
            } elseif (is_string($expectedVal)) {
                if (!is_string($actualVal) || (string) $actualVal !== $expectedVal) {
                    $errors[] = "Setting '{$key}' value mismatch: expected '{$expectedVal}', found " . var_export($actualVal, true) . ".";
                }
            }
        }

        // Enforce zero invented / extra settings keys (allowing legitimate migration schema defaults for global scope)
        $allowedExtraKeys = [];
        if ($scope === 'global' || $scope === 'all') {
            $allowedExtraKeys = self::VALID_MIGRATION_GLOBAL_SETTINGS;
        }

        foreach (array_keys($settings) as $actualKey) {
            if (!array_key_exists($actualKey, $expectedDefs) && !isset($allowedExtraKeys[$actualKey])) {
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
     * Enforces:
     *  - Status via exact accepted normalized values ('ok', 'table is already up to date')
     *  - Expected Msg_type must be 'status' (normalized)
     *  - Fails closed on query false/null, missing fields, or any non-clean status (e.g. 'not ok', 'corrupt', 'error')
     *
     * @param array<string, array<string, mixed>> $checkResults Map of table => result row
     * @return array{valid: bool, tables_checked: int, errors: string[]}
     */
    public static function verifyTableCheckIntegrity(array $checkResults): array
    {
        $errors = [];

        if (empty($checkResults)) {
            $errors[] = "No table check results provided for integrity verification.";
        }

        $acceptedStatusTexts = ['ok', 'table is already up to date'];

        foreach ($checkResults as $table => $result) {
            if ($result === false || $result === null) {
                $errors[] = "Table `{$table}` CHECK TABLE query failed (returned false/null).";
                continue;
            }

            if (is_array($result)) {
                $rawType = null;
                foreach (['Msg_type', 'msg_type', 'MSG_TYPE'] as $typeKey) {
                    if (array_key_exists($typeKey, $result)) {
                        $rawType = $result[$typeKey];
                        break;
                    }
                }
                if ($rawType === null || trim((string) $rawType) === '') {
                    $errors[] = "Table `{$table}` CHECK TABLE result is missing Msg_type.";
                    continue;
                }
                $msgType = (string) $rawType;

                $rawText = null;
                foreach (['Msg_text', 'msg_text', 'MSG_TEXT'] as $textKey) {
                    if (array_key_exists($textKey, $result)) {
                        $rawText = $result[$textKey];
                        break;
                    }
                }
                if ($rawText === null || trim((string) $rawText) === '') {
                    $errors[] = "Table `{$table}` CHECK TABLE result is missing Msg_text.";
                    continue;
                }
                $msgText = (string) $rawText;

                $normType = strtolower(trim($msgType));
                $normText = strtolower(trim($msgText));

                if ($normType === 'error') {
                    $errors[] = "Table `{$table}` CHECK TABLE reported error type: {$msgText}";
                } elseif ($normType !== 'status') {
                    $errors[] = "Table `{$table}` CHECK TABLE reported non-status Msg_type '{$msgType}': {$msgText}";
                } elseif (!in_array($normText, $acceptedStatusTexts, true)) {
                    $errors[] = "Table `{$table}` CHECK TABLE reported unhealthy status: {$msgText}";
                }
            } else {
                $msgText = (string) $result;
                $normText = strtolower(trim($msgText));
                if (!in_array($normText, $acceptedStatusTexts, true)) {
                    $errors[] = "Table `{$table}` CHECK TABLE reported unhealthy status: {$msgText}";
                } else {
                    $errors[] = "Table `{$table}` CHECK TABLE result is missing Msg_type.";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'tables_checked' => count($checkResults),
            'errors' => $errors,
        ];
    }

    /**
     * DB-FREE VERIFIER (PARTIAL): Verifies foreign key constraints against a provided target relationship list.
     * Preserves composite constraint column order and identities.
     *
     * LIMITATION NOTICE:
     *  - This validator is PARTIAL and does NOT certify exhaustive production constraint parity
     *    until a complete reviewed production foreign key manifest exists.
     *  - Does not certify runtime database parity.
     *
     * @param array<int, array<string, mixed>> $existingFks List of existing foreign keys (e.g. from information_schema)
     * @param array<int, array{table: string, column: string, referenced_table: string, referenced_column: string}> $expectedFks
     * @return array{valid: bool, status: string, is_partial: bool, exhaustive: bool, constraints_checked: int, missing: array<int, array{table: string, column: string, referenced_table: string, referenced_column: string}>, errors: string[]}
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
            'status' => empty($errors) ? 'PARTIAL' : 'FAILED',
            'is_partial' => true,
            'exhaustive' => false,
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

    // Strict offline CLI: exactly one argument required; reject missing or extras
    if (count($cliArgs) !== 1) {
        fwrite(STDERR, "CRITICAL USAGE ERROR: DeveloperSandboxTrialController offline CLI requires exactly one argument, " . count($cliArgs) . " provided.\n");
        fwrite(STDERR, "Usage: php scripts/DeveloperSandboxTrialController.php [--help|--version|--verify-manifest|--verify-sources]\n");
        exit(1);
    }

    $action = $cliArgs[0];

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
