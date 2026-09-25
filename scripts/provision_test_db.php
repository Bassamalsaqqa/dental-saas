<?php
declare(strict_types=1);

/**
 * Isolated Test Database Provisioner
 *
 * Implements a fail-closed, repeatable, documented provisioning and reset path
 * ONLY for explicitly disposable local test databases.
 *
 * Strict Isolation Safeguards:
 *  - Verifies exact dotted `database.tests.*` configuration.
 *  - Refuses non-local hosts (only 127.0.0.1, localhost, ::1 allowed).
 *  - Refuses default business database (`democa_dental_saas`) or any non-test target.
 *  - Refuses alternate connection overrides (failover, DSN, subdriver).
 *  - Refuses destructive operations unless explicitly requested via --reset.
 *  - Refuses reset or non-empty provisioning if database provenance/ownership cannot be verified.
 *  - --check-only is strictly read-only and will never create an absent database or mutate schemas.
 *  - Executes canonical migrations via CodeIgniter's MigrationRunner.
 *  - Seeds only deterministic synthetic reference fixtures (zero PHI, zero business records).
 *
 * Usage:
 *   php scripts/provision_test_db.php [--check-only] [--reset] [--help]
 *
 * Options:
 *   --check-only  Verify connection, isolation, and disposable target eligibility without mutations.
 *   --reset       Drop existing tables in the task-owned disposable test database and re-provision from scratch.
 *   --help        Display this help message.
 */

$repoRoot = dirname(__DIR__);
require_once __DIR__ . '/TestDatabasePreflight.php';

use Scripts\TestDatabasePreflight;

// 1. Parse CLI options and reject unknown flags
$allowedFlags = ['--check-only', '--reset', '--help'];
$cliArgs = array_slice($argv, 1);
$options = [
    'check-only' => false,
    'reset' => false,
];

foreach ($cliArgs as $arg) {
    if (!in_array($arg, $allowedFlags, true)) {
        fwrite(STDERR, "Error: Unknown option '{$arg}'. Supported options: " . implode(', ', $allowedFlags) . "\n");
        exit(1);
    }
    if ($arg === '--help') {
        echo "Usage: php scripts/provision_test_db.php [--check-only] [--reset] [--help]\n";
        exit(0);
    }
    if ($arg === '--check-only') {
        $options['check-only'] = true;
    }
    if ($arg === '--reset') {
        $options['reset'] = true;
    }
}

// 2. Set exact dotted environment keys for testing group
TestDatabasePreflight::enforceEnvironment();

// 3. Bootstrap CodeIgniter testing environment
require_once $repoRoot . '/vendor/autoload.php';
require_once $repoRoot . '/system/Test/bootstrap.php';

$dbConfig = new \Config\Database();
$testsConfig = $dbConfig->tests ?? [];
$businessConfig = $dbConfig->default ?? [];

// 4. Preflight Isolation Validation BEFORE Connection (Fail-Closed)
$configErrors = TestDatabasePreflight::validateConfigBeforeConnection($testsConfig, $businessConfig);
if (!empty($configErrors)) {
    fwrite(STDERR, "\n========================================================\n");
    fwrite(STDERR, " FAIL-CLOSED PREFLIGHT CONFIGURATION ERROR\n");
    fwrite(STDERR, "========================================================\n");
    foreach ($configErrors as $err) {
        fwrite(STDERR, " [!] " . $err . "\n");
    }
    fwrite(STDERR, "Aborting execution immediately WITHOUT attempting any connection.\n\n");
    exit(1);
}

$targetDb = TestDatabasePreflight::APPROVED_DISPOSABLE_TEST_DB;
$host = (string) ($testsConfig['hostname'] ?? '127.0.0.1');
$port = (int) ($testsConfig['port'] ?? 3306);
$user = (string) ($testsConfig['username'] ?? 'root');
$pass = (string) ($testsConfig['password'] ?? '');

$businessDb = (string) ($businessConfig['database'] ?? '');
$resolvedTestDb = (string) ($testsConfig['database'] ?? '');
$testsHost = (string) ($testsConfig['hostname'] ?? '');
$testsPort = (int) ($testsConfig['port'] ?? 3306);
$testsDriver = (string) ($testsConfig['DBDriver'] ?? '');

// 5. Inspect database existence on local server safely without selecting it
try {
    $dbExists = TestDatabasePreflight::checkDatabaseExistsOnServer($host, $user, $pass, $port, $targetDb);
} catch (\Throwable $e) {
    fwrite(STDERR, "Preflight check failed: " . $e->getMessage() . "\n");
    exit(1);
}

// 6. Handle --check-only mode (STRICTLY READ-ONLY, NEVER CREATES DB)
if ($options['check-only']) {
    echo "========================================================\n";
    echo " TEST DATABASE PREFLIGHT ISOLATION: PASSED\n";
    echo "========================================================\n";
    echo " Default Business Database: {$businessDb}\n";
    echo " Configured Test Database: {$resolvedTestDb}\n";
    echo " Target Host:              {$testsHost}:{$testsPort}\n";
    echo " Driver:                   {$testsDriver}\n";

    try {
        $checkResult = TestDatabasePreflight::orchestrateCheckOnly(
            fn(string $dbName) => $dbExists,
            function() {
                $db = \Config\Database::connect('tests');
                $errors = TestDatabasePreflight::validateLiveConnection($db);
                return ['liveDb' => $db->getDatabase(), 'errors' => $errors];
            },
            $targetDb
        );
    } catch (\Throwable $e) {
        fwrite(STDERR, "Preflight check failed: " . $e->getMessage() . "\n");
        exit(1);
    }

    if (!$checkResult['exists']) {
        echo " Target Database Status:   ABSENT / UNPROVISIONED (does not exist on server)\n";
        echo " Isolation Status:         VERIFIED (Isolated disposable target eligible for provisioning)\n";
        echo "========================================================\n";
        echo "Check-only mode requested. Target database is absent; no database created. No mutations performed.\n";
        exit(0);
    }

    if (!empty($checkResult['errors'])) {
        fwrite(STDERR, "\n========================================================\n");
        fwrite(STDERR, " FAIL-CLOSED LIVE IDENTITY ERROR\n");
        fwrite(STDERR, "========================================================\n");
        foreach ($checkResult['errors'] as $err) {
            fwrite(STDERR, " [!] " . $err . "\n");
        }
        fwrite(STDERR, "Aborting execution without running provisioning or mutating data.\n\n");
        exit(1);
    }

    echo " Live Database:            {$checkResult['liveDb']}\n";
    echo " Target Database Status:   EXISTS\n";
    echo " Isolation Status:         VERIFIED (Isolated disposable target)\n";
    echo "========================================================\n";
    echo "Check-only mode requested. Target database exists and is verified. No mutations performed.\n";
    exit(0);
}

// 7. Explicit Creation of absent target database (Provisioning path only)
if (!$dbExists) {
    echo "Target database '{$targetDb}' is absent. Creating database explicitly...\n";
    $rawMysqli = @new \mysqli($host, $user, $pass, '', $port);
    if ($rawMysqli->connect_errno) {
        fwrite(STDERR, "Provisioning failed: Unable to connect to local MySQL server: " . $rawMysqli->connect_error . "\n");
        exit(1);
    }
    if (!$rawMysqli->query("CREATE DATABASE `{$targetDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        $createErr = $rawMysqli->error;
        $rawMysqli->close();
        fwrite(STDERR, "Provisioning failed: Could not create database '{$targetDb}': {$createErr}\n");
        exit(1);
    }
    $rawMysqli->close();
    echo "Created database '{$targetDb}'.\n";
}

// 8. Connect to tests database group
try {
    $db = \Config\Database::connect('tests');
} catch (\Throwable $e) {
    fwrite(STDERR, "Preflight check failed: Unable to connect to tests database: " . $e->getMessage() . "\n");
    exit(1);
}

// 9. Validate Live Connection Identity
$liveErrors = TestDatabasePreflight::validateLiveConnection($db);
if (!empty($liveErrors)) {
    fwrite(STDERR, "\n========================================================\n");
    fwrite(STDERR, " FAIL-CLOSED LIVE IDENTITY ERROR\n");
    fwrite(STDERR, "========================================================\n");
    foreach ($liveErrors as $err) {
        fwrite(STDERR, " [!] " . $err . "\n");
    }
    fwrite(STDERR, "Aborting execution without running provisioning or mutating data.\n\n");
    exit(1);
}

echo "========================================================\n";
echo " TEST DATABASE PREFLIGHT ISOLATION: PASSED\n";
echo "========================================================\n";
echo " Default Business Database: {$businessDb}\n";
echo " Configured Test Database: {$resolvedTestDb}\n";
echo " Target Host:              {$testsHost}:{$testsPort}\n";
echo " Driver:                   {$testsDriver}\n";
echo " Live Database:            {$db->getDatabase()}\n";
echo " Isolation Status:         VERIFIED (Isolated disposable target)\n";
echo "========================================================\n";

// 10. Handle Reset vs Normal Provisioning
$tables = $db->listTables();

if ($options['reset']) {
    echo "\nReset requested: validating ownership and disposability before dropping...\n";
    $ownership = TestDatabasePreflight::verifyOwnershipForReset($db);
    if (!$ownership['allowed']) {
        fwrite(STDERR, "REFUSAL: Destructive reset refused. Reason: {$ownership['reason']}\n");
        exit(1);
    }

    echo "Dropping all existing tables and views in {$resolvedTestDb}...\n";
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    $rawTables = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->getResultArray();
    foreach ($rawTables as $row) {
        $t = array_values($row)[0];
        $db->query("DROP TABLE IF EXISTS `{$t}`");
    }
    $rawViews = $db->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'")->getResultArray();
    foreach ($rawViews as $row) {
        $v = array_values($row)[0];
        $db->query("DROP VIEW IF EXISTS `{$v}`");
    }
    $db->query("SET FOREIGN_KEY_CHECKS = 1");
    $db->resetDataCache();
} else {
    // Normal provisioning: refuse unowned non-empty database
    if (!empty($tables)) {
        $ownership = TestDatabasePreflight::verifyOwnershipForReset($db);
        if (!$ownership['allowed']) {
            fwrite(STDERR, "REFUSAL: Target database is non-empty and unowned. Reason: {$ownership['reason']}\n");
            exit(1);
        }
    }
}

// 11. Ensure Task Ownership Marker Exists with Checked Write
$db->query("CREATE TABLE IF NOT EXISTS `_test_db_marker` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `task_owner` VARCHAR(100) NOT NULL,
    `is_disposable` TINYINT(1) NOT NULL DEFAULT 1,
    `target_db` VARCHAR(100) NOT NULL,
    `created_at` DATETIME NOT NULL
)");
if ($db->table('_test_db_marker')->countAllResults() === 0) {
    $markerInserted = $db->table('_test_db_marker')->insert([
        'task_owner' => TestDatabasePreflight::TASK_OWNER_SIGNATURE,
        'is_disposable' => 1,
        'target_db' => TestDatabasePreflight::APPROVED_DISPOSABLE_TEST_DB,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    if (!$markerInserted) {
        fwrite(STDERR, "CRITICAL ERROR: Failed to insert ownership marker into _test_db_marker.\n");
        exit(1);
    }
}

// 12. Load Minimal Legacy Pre-Migration Foundation (CREATE TABLE IF NOT EXISTS)
require_once $repoRoot . '/tests/_support/Database/LegacyBaseSchema.php';
echo "Applying minimal pre-migration foundation (IF NOT EXISTS)...\n";
\Tests\Support\Database\LegacyBaseSchema::apply($db);

// 13. Execute Canonical Migrations via MigrationRunner with Checked Return
echo "Executing canonical migrations via CodeIgniter MigrationRunner...\n";
$migrate = \Config\Services::migrations(null, $db, false);
$migrate->ensureTable();

try {
    $migrationResult = $migrate->latest();
    if ($migrationResult === false) {
        fwrite(STDERR, "CRITICAL ERROR: MigrationRunner::latest() returned false.\n");
        exit(1);
    }
    echo "Canonical migrations complete.\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "CRITICAL ERROR: Migration failed: " . $e->getMessage() . "\n");
    fwrite(STDERR, "File: " . $e->getFile() . ":" . $e->getLine() . "\n");
    exit(1);
}

// 14. Seed Deterministic Synthetic Test Fixtures with Checked Writes
echo "Seeding deterministic synthetic test fixtures...\n";

function safeFixtureInsert($db, string $table, array $data): void {
    $res = $db->table($table)->insert($data);
    if (!$res) {
        fwrite(STDERR, "CRITICAL ERROR: Failed to seed fixture record into table '{$table}'.\n");
        exit(1);
    }
}

// Groups
$groups = [
    ['id' => 1, 'name' => 'admin', 'description' => 'Administrator'],
    ['id' => 2, 'name' => 'members', 'description' => 'General User'],
    ['id' => 3, 'name' => 'doctor', 'description' => 'Doctor'],
    ['id' => 4, 'name' => 'receptionist', 'description' => 'Receptionist'],
];
foreach ($groups as $g) {
    if ($db->table('groups')->where('id', $g['id'])->countAllResults() === 0) {
        safeFixtureInsert($db, 'groups', $g);
    }
}

// Roles
$roles = [
    ['id' => 1, 'name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Full Control Plane Access', 'is_active' => 1, 'is_system' => 1, 'created_by' => 1],
    ['id' => 2, 'name' => 'Doctor', 'slug' => 'doctor', 'description' => 'Medical Practitioner', 'is_active' => 1, 'is_system' => 1, 'created_by' => 1],
    ['id' => 3, 'name' => 'Practice Manager', 'slug' => 'practice_manager', 'description' => 'Clinic Operations Manager', 'is_active' => 1, 'is_system' => 1, 'created_by' => 1],
    ['id' => 4, 'name' => 'Receptionist', 'slug' => 'receptionist', 'description' => 'Front Desk Staff', 'is_active' => 1, 'is_system' => 1, 'created_by' => 1],
];
foreach ($roles as $r) {
    if ($db->table('roles')->where('id', $r['id'])->countAllResults() === 0) {
        safeFixtureInsert($db, 'roles', $r);
    }
}

// Synthetic Clinic 1
if ($db->table('clinics')->where('id', 1)->countAllResults() === 0) {
    safeFixtureInsert($db, 'clinics', [
        'id' => 1,
        'name' => 'Synthetic Test Clinic',
        'slug' => 'synthetic-test-clinic',
        'email' => 'clinic@example.test',
        'phone' => '+15555550100',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}

// Synthetic Full-Access Plan 1
if ($db->table('plans')->where('id', 1)->countAllResults() === 0) {
    safeFixtureInsert($db, 'plans', [
        'id' => 1,
        'name' => 'Synthetic Full Access Plan',
        'status' => 'active',
        'features_json' => json_encode([
            'kiosk' => ['enabled' => true],
            'exports' => ['enabled' => true],
            'finance' => true,
            'reports' => true,
            'contracts' => ['enabled' => true, 'signature' => ['enabled' => true]],
            'inventory' => true,
            'claims_hub' => true,
            'attachments' => ['enabled' => true],
            'appointments' => true,
            'audit_exports' => true,
            'notifications' => ['sms' => ['enabled' => true], 'email' => ['enabled' => true], 'whatsapp' => ['enabled' => true]],
            'chairside_notes' => true,
            'email_templates' => true,
        ]),
        'limits_json' => json_encode([
            'price' => 0,
            'exports' => -1,
            'duration' => 'monthly',
            'users_max' => -1,
            'storage_gb' => 5,
            'trial_days' => 0,
            'description' => 'Synthetic Test Plan',
            'notifications_email' => -1,
            'patients_active_max' => -1,
            'attachments_max_file_mb' => 5,
        ]),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
}

// Synthetic Clinic Subscription
if ($db->table('clinic_subscriptions')->where('clinic_id', 1)->countAllResults() === 0) {
    safeFixtureInsert($db, 'clinic_subscriptions', [
        'clinic_id' => 1,
        'plan_id' => 1,
        'status' => 'active',
        'start_at' => '2026-01-01 00:00:00',
        'end_at' => null,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
}

// Synthetic User 1
if ($db->table('users')->where('id', 1)->countAllResults() === 0) {
    safeFixtureInsert($db, 'users', [
        'id' => 1,
        'username' => 'testadmin',
        'email' => 'admin@example.test',
        'password' => password_hash('TestPass123!', PASSWORD_BCRYPT),
        'active' => 1,
        'first_name' => 'Test',
        'last_name' => 'Admin',
        'created_on' => time(),
        'created_at' => time(),
        'updated_at' => time(),
    ]);
}
if ($db->table('users_groups')->where('user_id', 1)->where('group_id', 1)->countAllResults() === 0) {
    safeFixtureInsert($db, 'users_groups', ['user_id' => 1, 'group_id' => 1]);
}
if ($db->table('user_roles')->where('user_id', 1)->where('role_id', 1)->countAllResults() === 0) {
    safeFixtureInsert($db, 'user_roles', ['user_id' => 1, 'role_id' => 1]);
}
if ($db->table('clinic_users')->where('clinic_id', 1)->where('user_id', 1)->countAllResults() === 0) {
    safeFixtureInsert($db, 'clinic_users', [
        'clinic_id' => 1,
        'user_id' => 1,
        'role_id' => 1,
        'status' => 'active',
    ]);
}

// Synthetic Patient 1
if ($db->table('patients')->where('id', 1)->countAllResults() === 0) {
    safeFixtureInsert($db, 'patients', [
        'id' => 1,
        'clinic_id' => 1,
        'patient_id' => 'P-TEST-001',
        'first_name' => 'Synthetic',
        'last_name' => 'Patient',
        'email' => 'patient@example.test',
        'phone' => '+15555550101',
        'date_of_birth' => '1990-01-01',
        'gender' => 'female',
        'status' => 'active',
    ]);
}

// Platform Secrets
$secretModel = new \App\Models\PlatformSecretModel();
$secretModel->setSecret('wa_app_secret', 'phpunit-test-app-secret', null, 'test seed');
$secretModel->setSecret('wa_phone_number_id', '100000000000003', null, 'test seed');
$secretModel->setSecret('wa_access_token', 'synthetic_test_token', null, 'test seed');

// Synthetic WhatsApp Channel
$encrypter = \Config\Services::encrypter();
$waConfig = [
    'phone_number_id' => '100000000000003',
    'access_token' => 'synthetic_test_token',
    'waba_id' => '100000000000002',
    'app_id' => '100000000000001',
    'app_secret' => 'phpunit-test-app-secret',
    'verify_token' => 'phpunit-verify-token',
    'status' => 'connected',
];
$encryptedConfig = base64_encode($encrypter->encrypt(json_encode($waConfig)));

if ($db->table('clinic_notification_channels')->where('clinic_id', 1)->where('channel_type', 'whatsapp')->countAllResults() === 0) {
    safeFixtureInsert($db, 'clinic_notification_channels', [
        'clinic_id' => 1,
        'channel_type' => 'whatsapp',
        'enabled_by_superadmin' => 1,
        'configured_by_clinic' => 1,
        'validated' => 1,
        'provider_type' => 'meta_cloud',
        'config_encrypted' => $encryptedConfig,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}

// Global settings defaults
$settings = [
    'system_name' => 'DentaPlus Test',
    'system_email' => 'system@example.test',
    'system_phone' => '+15555550100',
    'default_language' => 'en',
    'default_currency' => 'USD',
    'timezone' => 'UTC',
    'wa_provider' => 'meta_cloud',
];
foreach ($settings as $k => $v) {
    if ($db->table('settings')->where('setting_key', $k)->where('clinic_id IS NULL', null, false)->countAllResults() === 0) {
        safeFixtureInsert($db, 'settings', [
            'clinic_id' => null,
            'setting_key' => $k,
            'setting_value' => $v,
            'setting_type' => 'string',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

// Sync permissions via PermissionSyncService
$syncRes = (new \App\Services\PermissionSyncService())->fullSync();
if ($syncRes === false) {
    fwrite(STDERR, "CRITICAL ERROR: PermissionSyncService::fullSync() returned false.\n");
    exit(1);
}

$db->resetDataCache();

// 15. Post-Provisioning Verification
$postTables = $db->listTables();
$tableCount = count($postTables);
$migrationCount = $db->table('migrations')->countAllResults();

// Check critical business tables for 0 records (excluding synthetic patient 1)
$zeroRecordTables = [
    'appointments', 'finances', 'examinations', 'treatments',
    'odontograms', 'prescriptions', 'activity_logs', 'job_audits',
    'control_plane_audits', 'control_plane_invoices', 'control_plane_invoice_payments'
];

$businessViolations = [];
foreach ($zeroRecordTables as $zt) {
    if (in_array($zt, $postTables, true)) {
        $cnt = $db->table($zt)->countAllResults();
        if ($cnt > 0) {
            $businessViolations[$zt] = $cnt;
        }
    }
}

echo "========================================================\n";
echo " PROVISIONING VERIFICATION RESULTS\n";
echo "========================================================\n";
echo " Total Tables in Schema:   {$tableCount}\n";
echo " Recorded Migrations:      {$migrationCount}\n";

if (!empty($businessViolations)) {
    fwrite(STDERR, "[CRITICAL ERROR] Business data detected in test database:\n");
    foreach ($businessViolations as $tbl => $cnt) {
        fwrite(STDERR, "  - {$tbl}: {$cnt} rows\n");
    }
    exit(1);
} else {
    echo " Business Table Hygiene:   PASSED (0 rows across all business transaction tables)\n";
}

echo " Task Ownership Marker:    CONFIRMED (_test_db_marker)\n";
echo " Reference Fixtures:       CONFIRMED (synthetic clinic, user, patient, plan, channel, secrets, permissions)\n";
echo " Status:                   SUCCESSFULLY PROVISIONED & VERIFIED\n";
echo "========================================================\n";
exit(0);
