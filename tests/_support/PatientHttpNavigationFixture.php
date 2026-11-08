<?php

declare(strict_types=1);

// CLI-only synthetic fixture manager for real browser HTTP navigation tests.
// Operates strictly against preflight-verified isolated synthetic target.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden: CLI execution only.\n";
    exit(2);
}

$repoRoot = dirname(__DIR__, 2);
require_once $repoRoot . '/scripts/TestDatabasePreflight.php';
require_once $repoRoot . '/vendor/autoload.php';

use Scripts\TestDatabasePreflight;

// Parse action, run_id, receipt_path
$action = $argv[1] ?? '';
$runId = $argv[2] ?? '';
$receiptPath = $argv[3] ?? '';

if ($action === '' || !in_array($action, ['provision', 'count', 'teardown'], true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid or missing action. Allowed: provision, count, teardown.']);
    exit(1);
}

if ($runId === '' || !preg_match('/^[a-f0-9]{8,64}$/', $runId)) {
    echo json_encode(['success' => false, 'error' => 'Invalid or missing run_id (must be 8-64 hex chars).']);
    exit(1);
}

if ($receiptPath === '' || !str_ends_with($receiptPath, '.json')) {
    echo json_encode(['success' => false, 'error' => 'Invalid or missing receipt_path.']);
    exit(1);
}

if (in_array($action, ['count', 'teardown'], true)) {
    if (!is_file($receiptPath)) {
        echo json_encode(['success' => false, 'error' => 'Receipt file not found for ' . $action]);
        exit(1);
    }
    $receipt = json_decode((string) file_get_contents($receiptPath), true);
    if (!is_array($receipt) || ($receipt['run_id'] ?? '') !== $runId) {
        echo json_encode(['success' => false, 'error' => 'Receipt run_id mismatch.']);
        exit(1);
    }
}

$expectedReceipt = $repoRoot . '/writable/tmp/test_fixture_' . $runId . '.json';
if (str_replace('\\', '/', $receiptPath) !== str_replace('\\', '/', $expectedReceipt)) {
    echo json_encode(['success' => false, 'error' => 'Receipt path outside the owned harness location.']);
    exit(1);
}
$receiptDir = dirname($expectedReceipt);
if (!is_dir($receiptDir) && !mkdir($receiptDir, 0700, true) && !is_dir($receiptDir)) exit(1);
if (is_link($receiptDir) || is_link($receiptPath) || strcasecmp(str_replace('\\', '/', (string) realpath($receiptDir)), str_replace('\\', '/', $receiptDir)) !== 0) {
    echo json_encode(['success' => false, 'error' => 'Receipt path redirection refused.']);
    exit(1);
}

require_once __DIR__ . '/PatientNavigationConnection.php';
try {
    $connection = \Tests\Support\PatientNavigationConnection::load();
    \Tests\Support\PatientNavigationConnection::isolateWritable($runId);
}
catch (\Throwable $error) {
    echo json_encode(['success' => false, 'error' => $error->getMessage()]);
    exit(1);
}


// Set environment for testing
TestDatabasePreflight::enforceEnvironment(TestDatabasePreflight::APPROVED_DISPOSABLE_TEST_DB);
foreach ($connection as $key => $value) {
    $name = 'database.tests.' . $key;
    $valStr = (string) $value;
    $encoded = $key === 'DBPrefix' ? $valStr : ($valStr === '' ? 'empty' : ($valStr === '0' ? 0 : $valStr));
    putenv($name . '=' . $encoded);
    $_ENV[$name] = $encoded;
    $_SERVER[$name] = $encoded;
}
putenv('database.defaultGroup=tests');
$_ENV['database.defaultGroup'] = 'tests';
$_SERVER['database.defaultGroup'] = 'tests';

require_once $repoRoot . '/system/Test/bootstrap.php';

$dbConfig = new \Config\Database();
$cfgErrors = TestDatabasePreflight::validateConfigBeforeConnection($dbConfig->tests, $dbConfig->default);
if (!empty($cfgErrors) || $dbConfig->defaultGroup !== 'tests') {
    echo json_encode(['success' => false, 'error' => 'Config preflight failed: ' . implode('; ', $cfgErrors ?: ['defaultGroup must be tests'])]);
    exit(1);
}

$db = \Config\Database::connect('tests');
$liveErrors = TestDatabasePreflight::validateLiveConnection($db);
if (!empty($liveErrors)) {
    echo json_encode(['success' => false, 'error' => 'Live preflight failed: ' . implode('; ', $liveErrors)]);
    exit(1);
}

$ownership = TestDatabasePreflight::verifyOwnershipForReset($db);
if (!$db->tableExists(TestDatabasePreflight::OWNERSHIP_MARKER_TABLE) || empty($ownership['allowed'])) {
    echo json_encode(['success' => false, 'error' => 'Marker verification failed: ' . ($ownership['reason'] ?? 'missing marker')]);
    exit(1);
}

// -------------------------------------------------------------
// COUNT
// -------------------------------------------------------------
if ($action === 'count') {
    if (!is_file($receiptPath)) {
        echo json_encode(['success' => false, 'error' => 'Receipt file does not exist.']);
        exit(1);
    }
    $receipt = json_decode((string) file_get_contents($receiptPath), true);
    if (!is_array($receipt) || ($receipt['run_id'] ?? '') !== $runId) {
        echo json_encode(['success' => false, 'error' => 'Receipt run_id mismatch.']);
        exit(1);
    }
    $clinicId = (int) ($receipt['clinic_id'] ?? 0);
    if ($clinicId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid clinic_id in receipt.']);
        exit(1);
    }
    $clinic = $db->table('clinics')->where('id', $clinicId)->where('slug', 'browser-nav-' . $runId)->get()->getRowArray();
    if (!$clinic) {
        echo json_encode(['success' => false, 'error' => 'Clinic does not belong to run_id.']);
        exit(1);
    }
    $count = $db->table('patients')->where('clinic_id', $clinicId)->countAllResults();
    echo json_encode(['success' => true, 'patient_count' => $count]);
    exit(0);
}

// -------------------------------------------------------------
// TEARDOWN
// -------------------------------------------------------------
if ($action === 'teardown') {
    if (!is_file($receiptPath)) {
        echo json_encode(['success' => false, 'error' => 'Receipt file not found for teardown.']);
        exit(1);
    }
    $receipt = json_decode((string) file_get_contents($receiptPath), true);
    if (!is_array($receipt) || ($receipt['run_id'] ?? '') !== $runId) {
        echo json_encode(['success' => false, 'error' => 'Receipt run_id mismatch during teardown.']);
        exit(1);
    }

    $clinicId = (int) ($receipt['clinic_id'] ?? 0);
    $userId = (int) ($receipt['user_id'] ?? 0);
    $planId = (int) ($receipt['plan_id'] ?? 0);
    $roleId = (int) ($receipt['role_id'] ?? 0);

    $clinic = $db->table('clinics')->where('id', $clinicId)->where('slug', 'browser-nav-' . $runId)->get()->getRowArray();
    $user = $db->table('users')->where('id', $userId)->where('email', 'pnav_' . $runId . '@example.test')->get()->getRowArray();
    $plan = $db->table('plans')->where('id', $planId)->where('name', 'Browser Nav Plan ' . $runId)->get()->getRowArray();
    $role = $db->table('roles')->where('id', $roleId)->where('slug', 'browser-nav-role-' . $runId)->get()->getRowArray();

    if (!$clinic && !$user && !$plan && !$role) {
        // A killed/failed provision may leave its pre-commit receipt after automatic DB rollback.
        if (!unlink($receiptPath)) exit(1);
        echo json_encode(['success' => true, 'cleaned' => true, 'rolled_back' => true]);
        exit(0);
    }
    $foreignPlanUse = $db->table('clinic_subscriptions')->where('plan_id', $planId)->where('clinic_id !=', $clinicId)->countAllResults();
    $foreignRoleUse = $db->table('clinic_users')->where('role_id', $roleId)->where('clinic_id !=', $clinicId)->countAllResults();
    $foreignUserUse = $db->table('clinic_users')->where('user_id', $userId)->where('clinic_id !=', $clinicId)->countAllResults();
    if (!$clinic || !$user || !$plan || !$role || (int) $role['clinic_id'] !== $clinicId || $foreignPlanUse || $foreignRoleUse || $foreignUserUse) {
        echo json_encode(['success' => false, 'error' => 'Entity verification failed: entities do not match run_id.']);
        exit(1);
    }

    $db->transBegin();
    try {
        $db->table('patients')->where('clinic_id', $clinicId)->delete();
        $db->table('appointments')->where('clinic_id', $clinicId)->delete();
        $db->table('clinic_subscriptions')->where('clinic_id', $clinicId)->delete();
        $db->table('clinic_users')->where('clinic_id', $clinicId)->delete();
        $db->table('role_permissions')->where('role_id', $roleId)->delete();
        $db->table('roles')->where('id', $roleId)->where('clinic_id', $clinicId)->delete();
        $db->table('plans')->where('id', $planId)->delete();
        $db->table('clinics')->where('id', $clinicId)->delete();
        $db->table('activity_logs')->where('user_id', $userId)->delete();
        $db->table('users')->where('id', $userId)->delete();

        if (!$db->transStatus() || !$db->transCommit()) throw new \RuntimeException('Teardown transaction failed.');
    } catch (\Throwable $e) {
        $db->transRollback();
        echo json_encode(['success' => false, 'error' => 'Teardown transaction failed: ' . $e->getMessage()]);
        exit(1);
    }

    $remPatients = $db->table('patients')->where('clinic_id', $clinicId)->countAllResults();
    $remClinics = $db->table('clinics')->where('id', $clinicId)->countAllResults();
    $remUsers = $db->table('users')->where('id', $userId)->countAllResults();
    $remRoles = $db->table('roles')->where('id', $roleId)->countAllResults();
    $remPlans = $db->table('plans')->where('id', $planId)->countAllResults();

    if ($remPatients !== 0 || $remClinics !== 0 || $remUsers !== 0 || $remRoles !== 0 || $remPlans !== 0) {
        echo json_encode(['success' => false, 'error' => 'Teardown verification failed: residual rows detected.']);
        exit(1);
    }

    if (!unlink($receiptPath)) {
        echo json_encode(['success' => false, 'error' => 'Receipt removal failed after database cleanup.']);
        exit(1);
    }
    echo json_encode(['success' => true, 'cleaned' => true]);
    exit(0);
}

// -------------------------------------------------------------
// PROVISION
// -------------------------------------------------------------
if (is_file($receiptPath)) {
    echo json_encode(['success' => false, 'error' => 'Receipt file already exists for run_id.']);
    exit(1);
}

$email = 'pnav_' . $runId . '@example.test';
$rawPassword = 'NavTestPassword!123';
$ionAuthModel = new \App\Models\IonAuthModel();
$passwordHash = $ionAuthModel->hashPassword($rawPassword, $email);
$now = date('Y-m-d H:i:s');

$permRequirements = [
    ['patients', 'create'],
    ['patients', 'view'],
    ['appointments', 'create'],
];
$permIds = [];
foreach ($permRequirements as [$mod, $act]) {
    $row = $db->table('permissions')->select('id')->where('module', $mod)->where('action', $act)->get()->getRowArray();
    if (!$row) {
        echo json_encode(['success' => false, 'error' => "Prerequisite canonical permission missing: {$mod}:{$act}"]);
        exit(1);
    }
    $permIds[] = (int) $row['id'];
}

$db->transBegin();
try {
    // 1. Create user
    $db->table('users')->insert([
        'ip_address' => '127.0.0.1',
        'username' => $email,
        'password' => $passwordHash,
        'email' => $email,
        'active' => 1,
        'first_name' => 'BrowserNav',
        'last_name' => 'Tester',
        'created_on' => time(),
        'last_login' => time(),
    ]);
    $userId = (int) $db->insertID();

    // 2. Create clinic
    $db->table('clinics')->insert([
        'name' => 'Browser Nav Clinic ' . $runId,
        'slug' => 'browser-nav-' . $runId,
        'uuid' => $runId . '-browser-test',
        'status' => 'active',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $clinicId = (int) $db->insertID();

    // 3. Create plan with quota
    $db->table('plans')->insert([
        'name' => 'Browser Nav Plan ' . $runId,
        'status' => 'active',
        'features_json' => json_encode([]),
        'limits_json' => json_encode(['patients_active_max' => 1000]),
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $planId = (int) $db->insertID();

    // 4. Create subscription
    $db->table('clinic_subscriptions')->insert([
        'clinic_id' => $clinicId,
        'plan_id' => $planId,
        'status' => 'active',
        'start_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'end_at' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // 5. Create clinic-scoped role
    $roleSlug = 'browser-nav-role-' . $runId;
    $db->table('roles')->insert([
        'name' => $roleSlug,
        'slug' => $roleSlug,
        'description' => 'Browser Nav test role',
        'is_active' => 1,
        'is_system' => 0,
        'is_doctor' => 0,
        'created_by' => $userId,
        'created_at' => $now,
        'updated_at' => $now,
        'is_medical' => 0,
        'clinic_id' => $clinicId,
    ]);
    $roleId = (int) $db->insertID();

    // 6. Assign canonical permissions to clinic role
    $rolePermIds = [];
    foreach ($permIds as $permId) {
        $db->table('role_permissions')->insert([
            'role_id' => $roleId,
            'permission_id' => $permId,
            'granted' => 1,
        ]);
        $rolePermIds[] = (int) $db->insertID();
    }

    // 7. Create active clinic membership
    $db->table('clinic_users')->insert([
        'clinic_id' => $clinicId,
        'user_id' => $userId,
        'role_id' => $roleId,
        'status' => 'active',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $membershipId = (int) $db->insertID();

    $receiptData = [
        'run_id' => $runId,
        'created_at' => $now,
        'clinic_id' => $clinicId,
        'user_id' => $userId,
        'plan_id' => $planId,
        'role_id' => $roleId,
        'membership_id' => $membershipId,
        'role_permission_ids' => $rolePermIds,
        'email' => $email,
    ];

    $encodedReceipt = json_encode($receiptData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    $receiptStream = fopen($receiptPath, 'x');
    if ($receiptStream === false) throw new \RuntimeException('Exclusive receipt creation failed.');
    try {
        chmod($receiptPath, 0600);
        if (fwrite($receiptStream, $encodedReceipt) !== strlen($encodedReceipt) || !fflush($receiptStream)) {
            throw new \RuntimeException('Receipt write failed; transaction will roll back.');
        }
        if (function_exists('fsync') && !fsync($receiptStream)) throw new \RuntimeException('Receipt flush failed.');
    } finally { fclose($receiptStream); }
    if (!$db->transStatus() || !$db->transCommit()) throw new \RuntimeException('Fixture transaction failed.');
} catch (\Throwable $e) {
    $db->transRollback();
    echo json_encode(['success' => false, 'error' => 'Provisioning failed: ' . $e->getMessage()]);
    exit(1);
}

echo json_encode([
    'success' => true,
    'run_id' => $runId,
    'user_id' => $userId,
    'clinic_id' => $clinicId,
    'email' => $email,
    'password' => $rawPassword,
]);
exit(0);
