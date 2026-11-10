<?php
/**
 * Developer Sandbox Database Bootstrap Tool (CONFINED EXECUTOR - REFUSED UNTIL AUTHORIZED)
 *
 * Implements a fail-closed, repeatable, customer-free synthetic bootstrap design
 * exclusively for an incoming developer's local exploratory sandbox database.
 *
 * POLICY INVARIANTS:
 *  - Target database must be strictly democa_dental_saas_dev on 127.0.0.1:3307.
 *  - Port 3306 (business MySQL) is strictly refused; port 3307 is mandatory.
 *  - Target database must be strictly disjoint from business DB (democa_dental_saas).
 *  - Target database must be strictly disjoint from automated test DB (democa_dental_saas_test).
 *  - Refuses all recovery, reference, negative, and system schemas (denta_rec_*, mysql, etc.).
 *  - Refuses non-local hosts (loopback only: 127.0.0.1, localhost, ::1).
 *  - In Version 1, reset/overwrite is strictly REMOVED. Target MUST be genuinely empty.
 *  - This tool runs in PLAN-ONLY mode. Live execution, check-only, and reset are refused.
 *  - Preflight is NOT executed during plan generation; database readiness is NOT verified.
 *  - Zero customer data, zero production backups, zero live credentials used.
 *  - Dormant optional features (dynamic scheduling, invoice corrections, etc.) remain unapplied.
 *  - Outbound messaging (SMS, WhatsApp, SMTP) is unconditionally disabled/mocked.
 */

declare(strict_types=1);

namespace Scripts;

require_once __DIR__ . '/DeveloperSandboxPreflight.php';
require_once __DIR__ . '/DeveloperSandboxExecutor.php';

use Scripts\DeveloperSandboxPreflight;
use Scripts\DeveloperSandboxExecutor;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Error: CLI execution is required.\n");
    exit(1);
}

// 1. Strict CLI argument parsing and validation
$rawArgs = array_slice($argv, 1);
$targetDb = DeveloperSandboxPreflight::APPROVED_DEFAULT_SANDBOX_DB;
$mode = DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE;

foreach ($rawArgs as $arg) {
    if ($arg === '--help' || $arg === '-h') {
        echo "Usage: php scripts/DeveloperSandboxBootstrap.php [options]\n\n";
        echo "Options:\n";
        echo "  --dry-run             Inspect and output structured bootstrap execution plan (default, plan-only).\n";
        echo "  --mode=<mode>         Execution mode: 'production-baseline' (default, 77 migrations / 68 tables)\n";
        echo "                        or 'all-source' (85 migrations / 81 tables with dormant features OFF).\n";
        echo "  --target-db=<name>    Target sandbox database name (must equal " . DeveloperSandboxPreflight::APPROVED_DEFAULT_SANDBOX_DB . ").\n";
        echo "  --help                Show this help message.\n\n";
        echo "Policy Limits:\n";
        echo "  --apply               REFUSED. Live execution requires explicit Codex authorization.\n";
        echo "  --reset               REFUSED. Reset is removed from version 1; requires empty target schema.\n";
        echo "  --check-only          REFUSED. Offline mode only; live connection checks are not permitted.\n\n";
        exit(0);
    }

    if ($arg === '--apply') {
        fwrite(STDERR, "CRITICAL REFUSAL: Execution (--apply) is not supported in this stage. Plan-only mode is enforced until Codex authorization.\n");
        exit(1);
    }

    if ($arg === '--reset') {
        fwrite(STDERR, "CRITICAL REFUSAL: Database reset (--reset) has been removed from version 1 policy. Bootstrap requires a genuinely empty target schema.\n");
        exit(1);
    }

    if ($arg === '--check-only') {
        fwrite(STDERR, "CRITICAL REFUSAL: Connection check (--check-only) is not supported offline. No live database connections are permitted during planning.\n");
        exit(1);
    }

    if ($arg === '--dry-run') {
        continue;
    }

    if (str_starts_with($arg, '--mode=')) {
        $val = substr($arg, strlen('--mode='));
        if (!in_array($val, DeveloperSandboxExecutor::SUPPORTED_MODES, true)) {
            fwrite(STDERR, "CRITICAL REFUSAL: Unsupported mode '{$val}'. Supported modes: " . implode(', ', DeveloperSandboxExecutor::SUPPORTED_MODES) . ".\n");
            exit(1);
        }
        $mode = $val;
        continue;
    }

    if (str_starts_with($arg, '--target-db=')) {
        $val = substr($arg, strlen('--target-db='));
        if ($val !== DeveloperSandboxPreflight::APPROVED_DEFAULT_SANDBOX_DB) {
            fwrite(STDERR, "CRITICAL REFUSAL: Target database '{$val}' is strictly forbidden. The only approved developer sandbox is '" . DeveloperSandboxPreflight::APPROVED_DEFAULT_SANDBOX_DB . "'.\n");
            exit(1);
        }
        $targetDb = $val;
        continue;
    }

    fwrite(STDERR, "CRITICAL REFUSAL: Unknown or conflicting argument '{$arg}'. See --help for allowed options.\n");
    exit(1);
}

echo "====================================================================\n";
echo " DEVELOPER SANDBOX APPLICATION BOOTSTRAP (PLAN ONLY)\n";
echo " Target Database:    {$targetDb} (Proposal only, unverified)\n";
echo " Mode:               {$mode}\n";
echo " Preflight:          NOT EXECUTED\n";
echo " Database Readiness: NOT VERIFIED\n";
echo " Execution Status:   PLAN ONLY (Refuses live execution without explicit authorization)\n";
echo "====================================================================\n\n";

// Source-derived planning metadata only; none of these classes opens a connection.
require_once dirname(__DIR__) . '/app/Config/Permissions.php';
$permissionDefinitions = \App\Config\Permissions::getDefaultPermissions();
$roleDefinitions = \App\Config\Permissions::getDefaultRoles();

$executor = new DeveloperSandboxExecutor();
$migrationPlan = $executor->resolveMigrationPlan($mode);

$includedMigrations = [];
foreach ($migrationPlan as $fileName => $details) {
    $includedMigrations[$fileName] = hash_file('sha256', $details['path']);
}

$excludedMigrations = DeveloperSandboxExecutor::EXCLUDED_MIGRATIONS;

// Structured execution plan
$bootstrapPlan = [
    'plan_metadata' => [
        'target_database' => $targetDb,
        'approved_default' => DeveloperSandboxPreflight::APPROVED_DEFAULT_SANDBOX_DB,
        'approved_port' => 3307,
        'forbidden_port' => 3306,
        'forbidden_databases' => DeveloperSandboxPreflight::FORBIDDEN_DATABASES,
        'system_schemas' => DeveloperSandboxPreflight::SYSTEM_SCHEMAS,
        'execution_mode' => $mode,
        'preflight_status' => 'NOT_EXECUTED',
        'database_readiness' => 'NOT_VERIFIED',
        'execution_status' => 'PLAN_ONLY_AWAITING_CODEX_AUTHORIZATION',
    ],
    'preflight_safeguards' => [
        'loopback_only' => ['127.0.0.1', 'localhost', '::1'],
        'driver_allowed' => ['MySQLi'],
        'port_requirement' => 'Port 3307 strictly required; port 3306 (business MySQL) strictly refused',
        'business_db_refusal' => 'democa_dental_saas and authoritative business config are strictly forbidden',
        'automated_test_db_refusal' => 'democa_dental_saas_test is strictly forbidden to preserve test disjointness',
        'system_schemas_refusal' => 'mysql, information_schema, performance_schema, sys are strictly forbidden',
        'recovery_patterns_refusal' => 'denta_rec_*, denta_ref_*, denta_neg_* are strictly forbidden',
        'ownership_marker_table' => DeveloperSandboxPreflight::OWNERSHIP_MARKER_TABLE,
        'target_emptiness_policy' => 'Requires genuinely empty target schema (0 tables, 0 views, 0 routines, 0 events). Reset/overwrite is strictly refused in Version 1',
    ],
    'schema_execution_stages' => [
        'stage_1_target_emptiness_and_marker' => [
            'description' => 'Verify target schema is genuinely empty (0 tables, 0 views, 0 routines, 0 events), then create _dev_sandbox_marker ownership record',
            'marker_table' => DeveloperSandboxPreflight::OWNERSHIP_MARKER_TABLE,
            'task_owner' => DeveloperSandboxPreflight::TASK_OWNER_SIGNATURE,
            'reset_permitted' => false,
        ],
        'stage_2_legacy_foundation' => [
            'description' => 'Apply 18 legacy foundation tables via LegacyBaseSchema (CREATE TABLE IF NOT EXISTS)',
            'source' => 'tests/_support/Database/LegacyBaseSchema.php',
            'tables_count' => 18,
            'tables' => [
                'users', 'groups', 'users_groups', 'login_attempts', 'patients',
                'appointments', 'finances', 'examinations', 'prescriptions', 'settings',
                'treatments', 'odontograms', 'inventory', 'inventory_usage',
                'appointment_summary', 'financial_summary', 'patient_examination_summary',
                'permission_audit_log',
            ],
        ],
        'stage_3_canonical_migrations' => [
            'description' => 'Source-derived migration execution sequence matched against recorded preflight history',
            'selected_mode' => $mode,
            'recorded_production_history_rows' => 79,
            'current_source_included_count' => count($includedMigrations),
            'included_source_sha256' => $includedMigrations,
            'excluded_not_recorded_applied' => $excludedMigrations,
            'historical_discrepancies' => [
                'discrepancy_1_renamed' => 'Recorded AddClinicScopeToRoles corresponds to current source 2026-01-28-130000_AddClinicIdToRoles.php (class AddClinicIdToRoles). Historical equivalence requires exact historical-source comparison and generated-schema verification; forge addForeignKey called after addColumn is not by itself proof that a foreign key is emitted. Any unresolved historical effect remains explicit.',
                'discrepancy_2_duplicate' => 'CreateApiTokensTable occurred twice in recorded production history due to an API B1 operational retry. Fresh bootstrap applies it once to create api_tokens.',
            ],
            'table_accounting' => [
                'production_baseline_app_tables' => '18 foundation + 49 migration + 1 tracking = 68 application tables',
                'production_baseline_total_tables' => '68 application tables + 1 marker (_dev_sandbox_marker) = 69 total sandbox tables',
                'all_source_mode_app_tables' => '18 foundation + 62 migration + 1 tracking = 81 application tables (13 dormant tables)',
                'all_source_mode_total_tables' => '81 application tables + 1 marker (_dev_sandbox_marker) = 82 total sandbox tables',
                'table_parity_limitations' => 'Table counts remain unverified until actual trial execution and do NOT certify exact column/index/foreign-key compatibility with production or prove historical migration equivalence.',
            ],
            'writer_connection_isolation' => 'Pin every migration/model/service connection to the sandbox before framework boot via scripts/DeveloperSandboxConnectionBootstrap.php without editing existing .env.',
        ],
        'stage_4_user_initialization_and_rbac' => [
            'description' => 'Create User 1 (superadmin) FIRST to satisfy created_by foreign/audit constraints, then sync permissions and roles from Config\\Permissions matching exact schema (name, module, action, description, category, is_system; NO display_name). Uses dynamic private bcrypt hash; global_mode is session state rather than a user DB column.',
            'canonical_order' => [
                'step_1' => 'Insert User 1 (superadmin) into users table with active=1 and dynamic private bcrypt hash (no hardcoded credentials)',
                'step_2' => 'Synchronize source-defined permissions matching exact database schema',
                'step_3' => 'Synchronize source-defined system roles writing created_by=1',
                'step_4' => 'Populate role_permissions linking roles and permissions',
                'step_5' => 'Assign User 1 to super_admin role in user_roles table using explicit database IDs resolved by canonical slug',
            ],
            'dynamic_rbac_source' => 'App\\Config\\Permissions',
            'derived_permissions_count' => array_sum(array_map('count', $permissionDefinitions)),
            'derived_roles_count' => count($roleDefinitions),
            'derived_roles' => array_keys($roleDefinitions),
        ],
        'stage_5_synthetic_tenants_and_plans' => [
            'description' => 'Seed 2 customer-free synthetic clinics and baseline plan FIRST so clinic foreign key dependencies exist before clinic settings',
            'clinics' => [
                ['id' => 1, 'name' => 'Alpha Dental Practice', 'slug' => 'alpha-dental', 'status' => 'active'],
                ['id' => 2, 'name' => 'Beta Dental Clinic', 'slug' => 'beta-dental', 'status' => 'active'],
            ],
            'plan' => [
                'id' => 1,
                'name' => 'Developer Baseline Plan',
                'status' => 'active',
                'features_json' => [
                    'contracts' => ['enabled' => true, 'signature' => ['enabled' => true]],
                    'attachments' => ['enabled' => true],
                    'kiosk' => ['enabled' => true],
                    'exports' => ['enabled' => true],
                    'notifications' => [
                        'sms' => ['enabled' => false],
                        'whatsapp' => ['enabled' => false],
                        'email' => ['enabled' => false],
                    ],
                ],
                'note' => 'all_features_enabled:true is strictly REMOVED. Entitlements explicitly gated; outbound messaging channels OFF',
            ],
            'subscriptions' => [
                ['clinic_id' => 1, 'plan_id' => 1, 'status' => 'active'],
                ['clinic_id' => 2, 'plan_id' => 1, 'status' => 'active'],
            ],
        ],
        'stage_6_canonical_settings' => [
            'description' => 'Seed canonical global platform defaults (clinic_id=NULL) and clinic-scoped operational settings (clinic_id=1) matching source contracts (Clinic 1 exists from Stage 5)',
            'global_settings' => [
                'default_timezone' => 'Asia/Jerusalem',
                'default_currency' => 'ILS',
                'default_currency_position' => 'after',
                'default_date_format' => 'd/m/Y',
                'default_time_format' => '12',
                'default_appointment_duration' => 30,
                'default_booking_chair_max' => 3,
                'app_name' => 'DentaCare Developer Sandbox',
            ],
            'clinic_settings' => [
                'timezone' => 'Asia/Jerusalem',
                'currency' => 'ILS',
                'currency_position' => 'after',
                'booking_chair_count' => 1,
                'booking_chair_names' => ['Doctor 1'],
                'dynamic_doctor_scheduling_enabled' => 0, // Strict invariant: dynamic scheduling OFF
            ],
            'values_classification' => 'Deliberately chosen synthetic fixture values, not production/customer defaults',
        ],
        'stage_7_synthetic_personas' => [
            'description' => 'Seed 5 synthetic developer test personas with reserved RFC 2606 .test domains, private credentials descriptor, and resolved role IDs in clinic_users (no guessed IDs or fallbacks)',
            'personas' => [
                [
                    'id' => 1,
                    'username' => 'superadmin',
                    'email' => 'admin@sandbox.test',
                    'role' => 'super_admin',
                    'global_mode' => false,
                    'note' => 'Global mode is transient session state requiring explicit POST /controlplane/enter confirmation flow; NOT seeded true in DB',
                ],
                [
                    'id' => 2,
                    'username' => 'manager',
                    'email' => 'manager@alpha.test',
                    'role' => 'practice_manager',
                    'clinic_id' => 1,
                ],
                [
                    'id' => 3,
                    'username' => 'doctor',
                    'email' => 'doctor@alpha.test',
                    'role' => 'doctor',
                    'clinic_id' => 1,
                    'note' => 'Legacy booking chair mapping only; dynamic doctor scheduling OFF; provider profile not activated',
                ],
                [
                    'id' => 4,
                    'username' => 'reception',
                    'email' => 'reception@alpha.test',
                    'role' => 'receptionist',
                    'clinic_id' => 1,
                ],
                [
                    'id' => 5,
                    'username' => 'foreign_staff',
                    'email' => 'staff@beta.test',
                    'role' => 'receptionist',
                    'clinic_id' => 2,
                    'note' => 'Tenant isolation boundary verification persona in foreign clinic context',
                ],
            ],
        ],
        'stage_8_outbound_safeguards' => [
            'description' => 'Enforceable fail-closed development transport design with multi-layer lockdown',
            'child_process_environment' => 'email.protocol=smtp, email.SMTPHost="", email.SMTPPort=0, email.mailPath="/dev/null" injected before boot',
            'plan_guard_gates' => 'Plan features notifications.sms.enabled, notifications.whatsapp.enabled, notifications.email.enabled all set to false',
            'channel_records' => 'clinic_notification_channels configured with disabled dummy credentials (is_active=0, enabled_by_superadmin=0)',
            'background_workers' => 'No delivery workers, queue listeners, or cron jobs executed during bootstrap',
            'network_dispatch' => 'Zero SMTP, Twilio, or WhatsApp network calls possible',
        ],
    ],
];

echo json_encode($bootstrapPlan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
echo "NOTE: Zero database mutations were performed. This plan is submitted for Codex review.\n";
