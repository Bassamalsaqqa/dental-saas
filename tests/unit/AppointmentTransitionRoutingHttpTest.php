<?php

declare(strict_types=1);

namespace Tests\Unit;

require_once dirname(__DIR__) . '/_support/PhaseBFixture.php';
require_once dirname(__DIR__, 2) . '/scripts/TestDatabasePreflight.php';

use App\Controllers\Appointment;
use App\Models\AppointmentModel;
use App\Models\ActivityLogModel;
use App\Services\ActivityLogger;
use App\Services\AppointmentVisitStageService;
use Tests\Support\PhaseBFixture;

/**
 * Real registered web routes and SQL concurrency test suite for Appointment::transition.
 * Executed only against the isolated synthetic target on port 3307 via safe_test_runner.php.
 */
final class AppointmentTransitionRoutingHttpTest extends PhaseBFixture
{
    protected function setUp(): void
    {
        \Config\Services::resetSingle('settings');
        $config = new \Config\Database();
        $errors = \Scripts\TestDatabasePreflight::validateConfigBeforeConnection($config->tests, $config->default);
        if ($errors || $config->defaultGroup !== 'tests') {
            throw new \RuntimeException('Isolated tests configuration required.');
        }
        $db = \Config\Database::connect('tests');
        $liveErrors = \Scripts\TestDatabasePreflight::validateLiveConnection($db);
        if ($liveErrors) {
            throw new \RuntimeException(implode('; ', $liveErrors) . ' [configured tests port ' . (string) $config->tests['port'] . ']');
        }
        $ownership = \Scripts\TestDatabasePreflight::verifyOwnershipForReset($db);
        if (!$db->tableExists('_test_db_marker') || !$ownership['allowed']) {
            throw new \RuntimeException($ownership['reason']);
        }

        parent::setUp();
        $this->setting('currency', 'ILS');
        $this->login();
    }

    protected function tearDown(): void
    {
        if (isset($this->clinic)) {
            foreach (['activity_logs', 'appointment_status_history', 'treatments', 'examinations', 'appointments'] as $table) {
                if ($this->db->tableExists($table) && $this->db->fieldExists('clinic_id', $table)) {
                    $this->db->table($table)->whereIn('clinic_id', [$this->clinic, $this->otherClinic])->delete();
                }
            }
        }
        parent::tearDown();
    }

    private function createAppointment(string $status = 'scheduled', ?int $clinicId = null): int
    {
        $targetClinic = $clinicId ?? $this->clinic;
        $apptCode = 'APT-' . bin2hex(random_bytes(4));
        $data = [
            'clinic_id' => $targetClinic,
            'patient_id' => $this->patient,
            'provider_user_id' => $this->providers[0],
            'appointment_id' => $apptCode,
            'appointment_date' => date('Y-m-d'),
            'appointment_time' => '10:00:00',
            'status' => $status,
            'appointment_type' => 'consultation',
            'duration' => 30,
            'notes' => 'Synthetic test appointment',
            'created_by' => $this->actor,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->table('appointments')->insert($data);
        return (int) $this->db->insertID();
    }

    private function postTransition(int $appointmentId, array $data, ?array $sessionData = null)
    {
        $security = service('security');
        $config = config('Security');
        $hash = $security->getHash();
        $activeSession = $sessionData ?? session()->get();

        return $this->withSession($activeSession)
            ->withHeaders([
                'Cookie' => $config->cookieName . '=' . $hash,
                'X-CSRF-TOKEN' => $hash,
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->post('appointment/' . $appointmentId . '/transition', $data + [$config->tokenName => $hash]);
    }

    /**
     * Direct Model SQL test: verifies row locking (FOR UPDATE) and conditional update atomicity.
     * Connection 1 holds the exclusive lock while Connection 2 attempts FOR UPDATE with a short timeout;
     * physical lock contention in InnoDB triggers error 1205 (Lock wait timeout exceeded).
     * Connection 1 then completes its conditional update and audit write atomically.
     */
    public function testDirectModelSqlRowLockingAndContentionEvidence(): void
    {
        $appointmentId = $this->createAppointment('scheduled');

        $testsConfig = (new \Config\Database())->tests;
        $host = (string) ($testsConfig['hostname'] ?? '127.0.0.1');
        $port = (int) ($testsConfig['port'] ?? 3307);
        $user = (string) ($testsConfig['username'] ?? 'democa_test');
        $pass = (string) ($testsConfig['password'] ?? '');
        $database = (string) ($testsConfig['database'] ?? 'democa_dental_saas_test');

        // Connection 1
        $conn1 = new \mysqli($host, $user, $pass, $database, $port);
        self::assertSame(0, $conn1->connect_errno, 'Connection 1 failed');

        // Connection 2
        $conn2 = new \mysqli($host, $user, $pass, $database, $port);
        self::assertSame(0, $conn2->connect_errno, 'Connection 2 failed');

        // 1. Connection 1 begins transaction and acquires exclusive row lock
        $conn1->begin_transaction();
        $stmt1 = $conn1->prepare("SELECT id, clinic_id, patient_id, status FROM appointments WHERE id = ? AND clinic_id = ? FOR UPDATE");
        $stmt1->bind_param('ii', $appointmentId, $this->clinic);
        $stmt1->execute();
        $res1 = $stmt1->get_result()->fetch_assoc();
        self::assertNotNull($res1);
        self::assertSame('scheduled', $res1['status']);

        // 2. Connection 2 sets a 1-second lock wait timeout and attempts FOR UPDATE on the same row.
        // Because Connection 1 holds the row lock, Connection 2 will experience real InnoDB lock contention and timeout!
        $conn2->begin_transaction();
        $conn2->query("SET innodb_lock_wait_timeout = 1");

        $lockWaitTimeoutOccurred = false;
        try {
            $stmt2 = $conn2->prepare("SELECT id, clinic_id, patient_id, status FROM appointments WHERE id = ? AND clinic_id = ? FOR UPDATE");
            $stmt2->bind_param('ii', $appointmentId, $this->clinic);
            $stmt2->execute();
        } catch (\mysqli_sql_exception $e) {
            // Error 1205 = ER_LOCK_WAIT_TIMEOUT
            if ($e->getCode() === 1205 || str_contains($e->getMessage(), 'Lock wait timeout')) {
                $lockWaitTimeoutOccurred = true;
            }
        }
        self::assertTrue($lockWaitTimeoutOccurred, 'Connection 2 must experience real InnoDB lock wait timeout while Connection 1 holds FOR UPDATE lock');

        // Roll back Connection 2's failed transaction
        $conn2->rollback();

        // 3. Connection 1 updates status conditionally from 'scheduled' to 'arrived'
        $now = date('Y-m-d H:i:s');
        $upStmt = $conn1->prepare("UPDATE appointments SET status = ?, updated_at = ? WHERE id = ? AND clinic_id = ? AND status = ?");
        $targetStage = 'arrived';
        $expectedStage = 'scheduled';
        $upStmt->bind_param('ssiis', $targetStage, $now, $appointmentId, $this->clinic, $expectedStage);
        $upStmt->execute();
        self::assertSame(1, $upStmt->affected_rows, 'Connection 1 conditional update must affect exactly 1 row');

        // Insert audit log in Connection 1's transaction
        $desc = "Appointment visit stage changed from scheduled to arrived";
        $auditStmt = $conn1->prepare("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, created_at) VALUES (?, 'status', 'appointment', ?, ?, ?)");
        $auditStmt->bind_param('iiss', $this->actor, $appointmentId, $desc, $now);
        $auditStmt->execute();
        self::assertSame(1, $auditStmt->affected_rows, 'Audit log must be inserted in active transaction');

        // Commit Connection 1
        $conn1->commit();

        // 4. Connection 2 now reads the row with FOR UPDATE in a new transaction
        $conn2->begin_transaction();
        $stmt2 = $conn2->prepare("SELECT id, clinic_id, patient_id, status FROM appointments WHERE id = ? AND clinic_id = ? FOR UPDATE");
        $stmt2->bind_param('ii', $appointmentId, $this->clinic);
        $stmt2->execute();
        $res2 = $stmt2->get_result()->fetch_assoc();
        self::assertNotNull($res2);
        self::assertSame('arrived', $res2['status'], 'Connection 2 must see updated committed status');

        // Connection 2 sees status is 'arrived', which does NOT match its expected 'scheduled' -> rollback
        $conn2->rollback();

        // Close connections
        $conn1->close();
        $conn2->close();

        // Verify database state: status is 'arrived' and exactly 1 audit log exists
        $finalAppt = $this->db->table('appointments')->where('id', $appointmentId)->get()->getRowArray();
        self::assertSame('arrived', $finalAppt['status']);

        $auditCount = $this->db->table('activity_logs')
            ->where('entity_type', 'appointment')
            ->where('entity_id', $appointmentId)
            ->where('action', 'status')
            ->countAllResults();
        self::assertSame(1, $auditCount, 'Exactly 1 audit log record must exist');
    }

    /**
     * Bounded multi-worker concurrency verification over the canonical route:
     * Two independent worker processes invoke the route with identical expected_status='scheduled'.
     * Exactly one worker must yield HTTP 200 and the other HTTP 409 Conflict with allowed_statuses.
     */
    public function testTwoConcurrentWorkersYieldOne200AndOne409(): void
    {
        $appointmentId = $this->createAppointment('scheduled');

        $harnessDir = '/home/t3dev/.test-harness-reception-eab47c4e';
        $workerScript = $harnessDir . '/test_concurrent_worker.php';
        $harnessPreload = $harnessDir . '/preload.php';

        $sessionData = session()->get();
        $testsConfig = (new \Config\Database())->tests;
        $connectionKeys = ['hostname','port','database','username','password','DBDriver','DBPrefix'];
        $connectionPayload = json_encode(array_intersect_key($testsConfig, array_flip($connectionKeys)), JSON_THROW_ON_ERROR);

        // Generate worker script that boots CI4 in a separate process and calls Appointment::transition
        $scriptContent = <<<'PHP'
<?php
declare(strict_types=1);

$repoRoot = $argv[1];
$appointmentId = (int) $argv[2];
$sessionJson = $argv[3];
$barrierFile = $argv[4];
$resultFile = $argv[5];
$harnessPreload = $argv[6];

require_once $harnessPreload;
require_once $repoRoot . '/system/Test/bootstrap.php';
require_once $repoRoot . '/scripts/TestRunnerConnectionBootstrap.php';

$session = json_decode($sessionJson, true);
session()->set($session);

$_POST = [
    'status' => 'arrived',
    'expected_status' => 'scheduled',
    'return_to' => 'dashboard',
];

$request = \Config\Services::request();
$request->setMethod('POST');
$request->setHeader('X-Requested-With', 'XMLHttpRequest');
$request->setGlobal('post', $_POST);

$response = \Config\Services::response();

$controller = new \App\Controllers\Appointment();
$controller->initController($request, $response, \Config\Services::logger());

// Wait for synchronization barrier
$start = microtime(true);
while (!file_exists($barrierFile) && (microtime(true) - $start) < 5.0) {
    usleep(1000);
}

try {
    $res = $controller->transition($appointmentId);
    $statusCode = $res->getStatusCode();
    $body = json_decode($res->getBody(), true);
    file_put_contents($resultFile, json_encode([
        'status_code' => $statusCode,
        'body' => $body,
        'pid' => getmypid(),
    ]));
} catch (\Throwable $e) {
    file_put_contents($resultFile, json_encode([
        'status_code' => 500,
        'error' => $e->getMessage(),
        'pid' => getmypid(),
    ]));
}
PHP;

        file_put_contents($workerScript, $scriptContent);
        chmod($workerScript, 0700);

        $barrierFile = $harnessDir . '/barrier_' . bin2hex(random_bytes(6)) . '.flag';
        $resFile1 = $harnessDir . '/res1_' . bin2hex(random_bytes(6)) . '.json';
        $resFile2 = $harnessDir . '/res2_' . bin2hex(random_bytes(6)) . '.json';

        $cmd1 = [
            PHP_BINARY,
            $workerScript,
            dirname(__DIR__, 2),
            (string) $appointmentId,
            json_encode($sessionData),
            $barrierFile,
            $resFile1,
            $harnessPreload
        ];

        $cmd2 = [
            PHP_BINARY,
            $workerScript,
            dirname(__DIR__, 2),
            (string) $appointmentId,
            json_encode($sessionData),
            $barrierFile,
            $resFile2,
            $harnessPreload
        ];

        $env = array_merge(getenv(), [
            'WRITEPATH' => $harnessDir . '/writable/',
            'database.tests.hostname' => '127.0.0.1',
            'database.tests.port' => '3307',
            'database.tests.database' => 'democa_dental_saas_test',
            'database.tests.username' => (string) ($testsConfig['username'] ?? 'democa_test'),
            'database.tests.password' => (string) ($testsConfig['password'] ?? ''),
            'database.tests.DBDriver' => 'MySQLi',
            'DENTA_VERIFIED_TEST_CONNECTION' => $connectionPayload,
        ]);

        $proc1 = proc_open($cmd1, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes1, dirname(__DIR__, 2), $env);
        $proc2 = proc_open($cmd2, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes2, dirname(__DIR__, 2), $env);

        self::assertIsResource($proc1);
        self::assertIsResource($proc2);

        // Small sleep to ensure both processes are spawned and waiting on barrier
        usleep(50000); // 50ms
        // Release barrier simultaneously
        touch($barrierFile);

        proc_close($proc1);
        proc_close($proc2);

        self::assertFileExists($resFile1);
        self::assertFileExists($resFile2);

        $out1 = json_decode(file_get_contents($resFile1), true);
        $out2 = json_decode(file_get_contents($resFile2), true);

        // Clean up harness files
        @unlink($workerScript);
        @unlink($barrierFile);
        @unlink($resFile1);
        @unlink($resFile2);

        $statusCodes = [$out1['status_code'], $out2['status_code']];
        sort($statusCodes);

        self::assertSame([200, 409], $statusCodes, 'Concurrent execution must yield exactly one 200 and one 409');

        $conflictResponse = ($out1['status_code'] === 409) ? $out1['body'] : $out2['body'];
        self::assertSame('CONFLICT', $conflictResponse['error']);
        self::assertSame('arrived', $conflictResponse['current_status']);
        self::assertContains('ready', $conflictResponse['allowed_statuses']);

        // Verify DB final state and single audit log
        $finalAppt = $this->db->table('appointments')->where('id', $appointmentId)->get()->getRowArray();
        self::assertSame('arrived', $finalAppt['status']);

        $auditCount = $this->db->table('activity_logs')
            ->where('entity_type', 'appointment')
            ->where('entity_id', $appointmentId)
            ->where('action', 'status')
            ->countAllResults();
        self::assertSame(1, $auditCount, 'Exactly 1 audit log must be committed across both workers');
    }

    /**
     * Independent session replay test:
     * After status has transitioned to 'arrived', a subsequent/replay request with expected_status='scheduled'
     * returns HTTP 409 Conflict without writing duplicate audit records.
     */
    public function testIndependentSessionReplayReturns409(): void
    {
        $appointmentId = $this->createAppointment('scheduled');

        // First transition -> 200
        $res1 = $this->postTransition($appointmentId, [
            'status' => 'arrived',
            'expected_status' => 'scheduled',
            'return_to' => 'dashboard',
        ]);
        $res1->assertStatus(200);
        $res1->assertJSONFragment(['success' => true, 'status' => 'arrived']);

        // Replay request from an independent session with stale expected_status -> 409
        $independentSession = [
            'user_id' => $this->actor,
            'identity' => $this->email,
            'active_clinic_id' => $this->clinic,
            'global_mode' => false,
            'ui_locale' => 'en',
            'is_impersonating' => false,
        ];
        $res2 = $this->postTransition($appointmentId, [
            'status' => 'arrived',
            'expected_status' => 'scheduled',
            'return_to' => 'dashboard',
        ], $independentSession);

        $res2->assertStatus(409);
        $res2->assertJSONFragment([
            'success' => false,
            'error' => 'CONFLICT',
            'current_status' => 'arrived',
        ]);

        $auditCount = $this->db->table('activity_logs')
            ->where('entity_type', 'appointment')
            ->where('entity_id', $appointmentId)
            ->where('action', 'status')
            ->countAllResults();
        self::assertSame(1, $auditCount, 'Replay must not create duplicate audit record');
    }

    /**
     * Invalid transition: scheduled directly to ready (skipping arrived) returns HTTP 422.
     */
    public function testInvalidTransitionReturns422(): void
    {
        $appointmentId = $this->createAppointment('scheduled');

        $res = $this->postTransition($appointmentId, [
            'status' => 'ready',
            'expected_status' => 'scheduled',
            'return_to' => 'dashboard',
        ]);

        $res->assertStatus(422);
        $res->assertJSONFragment([
            'success' => false,
            'message' => 'This visit stage change is not allowed.',
            'current_status' => 'scheduled',
        ]);

        $appt = $this->db->table('appointments')->where('id', $appointmentId)->get()->getRowArray();
        self::assertSame('scheduled', $appt['status']);

        $auditCount = $this->db->table('activity_logs')
            ->where('entity_type', 'appointment')
            ->where('entity_id', $appointmentId)
            ->countAllResults();
        self::assertSame(0, $auditCount, 'Invalid transition must write 0 audit logs');
    }

    /**
     * Missing tenant context:
     * - Direct controller call without active clinic returns 403 TENANT_CONTEXT_REQUIRED.
     * - Routed HTTP call without active clinic returns 422 tenant_context_missing via filter.
     * Both fail-closed without writing any records.
     */
    public function testTenantMissingContextReturnsDenial(): void
    {
        $appointmentId = $this->createAppointment('scheduled');

        // 1. Direct controller method verification: returns 403 TENANT_CONTEXT_REQUIRED
        session()->remove('active_clinic_id');
        $req = \Config\Services::request();
        $req->setMethod('POST');
        $req->setHeader('X-Requested-With', 'XMLHttpRequest');
        $req->setGlobal('post', ['status' => 'arrived', 'expected_status' => 'scheduled']);
        $resp = \Config\Services::response();

        $controller = new Appointment();
        $controller->initController($req, $resp, \Config\Services::logger());
        $directRes = $controller->transition($appointmentId);
        self::assertSame(403, $directRes->getStatusCode());
        $body = json_decode((string) $directRes->getBody(), true);
        self::assertSame('TENANT_CONTEXT_REQUIRED', $body['error'] ?? null);

        // 2. Routed HTTP verification: filter rejects with 422
        $security = service('security');
        $config = config('Security');
        $hash = $security->getHash();
        $res = $this->withSession(session()->get())
            ->withHeaders([
                'Cookie' => $config->cookieName . '=' . $hash,
                'X-CSRF-TOKEN' => $hash,
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->post('appointment/' . $appointmentId . '/transition', [
                'status' => 'arrived',
                'expected_status' => 'scheduled',
                $config->tokenName => $hash,
            ]);
        self::assertSame(422, $res->response()->getStatusCode());

        $appt = $this->db->table('appointments')->where('id', $appointmentId)->get()->getRowArray();
        self::assertSame('scheduled', $appt['status']);
    }

    /**
     * Foreign appointment: appointment in another clinic returns HTTP 404.
     */
    public function testForeignAppointmentReturns404(): void
    {
        $foreignId = $this->createAppointment('scheduled', $this->otherClinic);

        $res = $this->postTransition($foreignId, [
            'status' => 'arrived',
            'expected_status' => 'scheduled',
        ]);

        $res->assertStatus(404);
        $res->assertJSONFragment(['message' => 'Appointment not found']);

        $foreignAppt = $this->db->table('appointments')->where('id', $foreignId)->get()->getRowArray();
        self::assertSame('scheduled', $foreignAppt['status']);
    }

    /**
     * Malformed target or expected arrays return HTTP 422 without writing.
     */
    public function testMalformedTargetAndExpectedArraysReturn422(): void
    {
        $appointmentId = $this->createAppointment('scheduled');

        // Array target status
        $res1 = $this->postTransition($appointmentId, [
            'status' => ['arrived'],
            'expected_status' => 'scheduled',
        ]);
        $res1->assertStatus(422);

        // Array expected status
        $res2 = $this->postTransition($appointmentId, [
            'status' => 'arrived',
            'expected_status' => ['scheduled'],
        ]);
        $res2->assertStatus(422);

        $appt = $this->db->table('appointments')->where('id', $appointmentId)->get()->getRowArray();
        self::assertSame('scheduled', $appt['status']);
    }

    /**
     * Valid legacy caller: omitting expected_status succeeds with HTTP 200 and audit write.
     */
    public function testValidLegacyCallerWithoutExpectedStatus(): void
    {
        $appointmentId = $this->createAppointment('scheduled');

        $res = $this->postTransition($appointmentId, [
            'status' => 'arrived',
            'return_to' => 'dashboard',
        ]);

        $res->assertStatus(200);
        $res->assertJSONFragment(['success' => true, 'status' => 'arrived']);

        $appt = $this->db->table('appointments')->where('id', $appointmentId)->get()->getRowArray();
        self::assertSame('arrived', $appt['status']);

        $auditCount = $this->db->table('activity_logs')
            ->where('entity_type', 'appointment')
            ->where('entity_id', $appointmentId)
            ->where('action', 'status')
            ->countAllResults();
        self::assertSame(1, $auditCount);
    }

    /**
     * CSRF rejection and no-write: missing or invalid CSRF token rejected with SecurityException, 0 writes.
     */
    public function testCsrfRejectionAndNoWrite(): void
    {
        $appointmentId = $this->createAppointment('scheduled');

        $this->expectException(\CodeIgniter\Security\Exceptions\SecurityException::class);
        try {
            $this->withSession(session()->get())
                ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                ->post('appointment/' . $appointmentId . '/transition', [
                    'status' => 'arrived',
                    'expected_status' => 'scheduled',
                    'csrf_test_name' => 'invalid-token-123',
                ]);
        } finally {
            $appt = $this->db->table('appointments')->where('id', $appointmentId)->get()->getRowArray();
            self::assertSame('scheduled', $appt['status']);

            $auditCount = $this->db->table('activity_logs')
                ->where('entity_type', 'appointment')
                ->where('entity_id', $appointmentId)
                ->countAllResults();
            self::assertSame(0, $auditCount);
        }
    }

    /**
     * Completion guard: outstanding checkout issues require explicit checkout_override.
     */
    public function testCompletionGuardRequiresCheckoutOverride(): void
    {
        $appointmentId = $this->createAppointment('checkout');

        // Insert pending examination and active treatment for this patient/appointment to generate an issue
        $this->db->table('examinations')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => $this->patient,
            'appointment_id' => $appointmentId,
            'examination_id' => 'EX-' . bin2hex(random_bytes(4)),
            'examination_date' => date('Y-m-d'),
            'examination_type' => 'initial',
            'chief_complaint' => 'Unfinished examination',
            'status' => 'pending',
            'created_by' => $this->actor,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $examId = (int) $this->db->insertID();

        $this->db->table('treatments')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => $this->patient,
            'examination_id' => $examId,
            'treatment_id' => 'TR-' . bin2hex(random_bytes(4)),
            'treatment_name' => 'Root canal',
            'treatment_type' => 'endodontic',
            'status' => 'active',
            'cost' => '150.00',
            'created_by' => $this->actor,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $res = $this->postTransition($appointmentId, [
            'status' => 'completed',
            'expected_status' => 'checkout',
            'return_to' => 'dashboard',
        ]);

        $res->assertStatus(422);
        $res->assertJSONFragment([
            'success' => false,
            'requires_override' => true,
        ]);

        $appt = $this->db->table('appointments')->where('id', $appointmentId)->get()->getRowArray();
        self::assertSame('checkout', $appt['status']);

        $auditCount = $this->db->table('activity_logs')
            ->where('entity_type', 'appointment')
            ->where('entity_id', $appointmentId)
            ->countAllResults();
        self::assertSame(0, $auditCount);
    }

    /**
     * Completion guard and long override metadata preservation without shortening loss:
     * Overriding checkout with long reason preserves description (<=255) and full metadata JSON.
     */
    public function testCompletionWithLongOverrideMetadataPreservation(): void
    {
        $appointmentId = $this->createAppointment('checkout');

        // Insert pending examination and active treatment to require override
        $this->db->table('examinations')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => $this->patient,
            'appointment_id' => $appointmentId,
            'examination_id' => 'EX-' . bin2hex(random_bytes(4)),
            'examination_date' => date('Y-m-d'),
            'examination_type' => 'initial',
            'chief_complaint' => 'Unfinished examination',
            'status' => 'pending',
            'created_by' => $this->actor,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $examId = (int) $this->db->insertID();

        $this->db->table('treatments')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => $this->patient,
            'examination_id' => $examId,
            'treatment_id' => 'TR-' . bin2hex(random_bytes(4)),
            'treatment_name' => 'Root canal',
            'treatment_type' => 'endodontic',
            'status' => 'active',
            'cost' => '150.00',
            'created_by' => $this->actor,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $longReason = str_repeat('Clinical authorization confirmed under supervisor protocol #99824. ', 4);
        self::assertGreaterThan(255, mb_strlen($longReason));
        self::assertLessThanOrEqual(500, mb_strlen($longReason));

        $res = $this->postTransition($appointmentId, [
            'status' => 'completed',
            'expected_status' => 'checkout',
            'checkout_override' => '1',
            'checkout_override_reason' => $longReason,
            'return_to' => 'dashboard',
        ]);

        $res->assertStatus(200);
        $res->assertJSONFragment(['success' => true, 'status' => 'completed']);

        $appt = $this->db->table('appointments')->where('id', $appointmentId)->get()->getRowArray();
        self::assertSame('completed', $appt['status']);

        $audit = $this->db->table('activity_logs')
            ->where('entity_type', 'appointment')
            ->where('entity_id', $appointmentId)
            ->where('action', 'status')
            ->get()
            ->getRowArray();

        self::assertNotNull($audit);
        self::assertLessThanOrEqual(255, mb_strlen($audit['description']));
        self::assertStringEndsWith('...', $audit['description']);

        self::assertNotEmpty($audit['metadata']);
        $meta = json_decode($audit['metadata'], true);
        self::assertIsArray($meta);
        self::assertTrue($meta['checkout_override']);
        self::assertSame(trim($longReason), $meta['reason'], 'Full long reason must be preserved without loss in metadata JSON');
    }

    /**
     * Actual audit failure rollback:
     * When activity_logs insert fails, the active database transaction must roll back completely,
     * ensuring the appointment status is NOT updated and zero partial state persists.
     */
    public function testActualAuditFailureRollsBackAppointmentUpdate(): void
    {
        $appointmentId = $this->createAppointment('scheduled');

        $triggerName = 'trg_test_audit_failure_' . bin2hex(random_bytes(4));
        $this->db->query("CREATE TRIGGER {$triggerName} BEFORE INSERT ON activity_logs FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SYNTHETIC_AUDIT_LOG_FAILURE'; END;");

        try {
            $res = $this->postTransition($appointmentId, [
                'status' => 'arrived',
                'expected_status' => 'scheduled',
                'return_to' => 'dashboard',
            ]);

            $res->assertStatus(500);
            $res->assertJSONFragment([
                'success' => false,
                'message' => 'Failed to record audit log for visit stage update.',
            ]);

            // Verify database state: status remains 'scheduled' (rolled back)
            $appt = $this->db->table('appointments')->where('id', $appointmentId)->get()->getRowArray();
            self::assertSame('scheduled', $appt['status'], 'Appointment status must remain scheduled after audit rollback');

            $auditCount = $this->db->table('activity_logs')
                ->where('entity_type', 'appointment')
                ->where('entity_id', $appointmentId)
                ->countAllResults();
            self::assertSame(0, $auditCount, '0 audit logs must exist');
        } finally {
            $this->db->query("DROP TRIGGER IF EXISTS {$triggerName}");
        }
    }
}
