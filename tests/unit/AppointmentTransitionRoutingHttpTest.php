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
     * Direct Model SQL test: verifies row locking (FOR UPDATE) and conditional update atomicity via AppointmentModel.
     * Connection 1 holds the exclusive lock via AppointmentModel::findForUpdateByClinic while Connection 2 attempts
     * findForUpdateByClinic with a short timeout; physical lock contention in InnoDB triggers error 1205.
     * Connection 1 then completes its conditional update via AppointmentModel::conditionalStatusUpdate and audit
     * write atomically.
     */
    public function testDirectModelSqlRowLockingAndContentionEvidence(): void
    {
        $appointmentId = $this->createAppointment('scheduled');

        // Connect two independent unshared connections using the validated 'tests' configuration
        $db1 = \Config\Database::connect('tests', false);
        $db2 = \Config\Database::connect('tests', false);

        $model1 = new AppointmentModel($db1);
        $model2 = new AppointmentModel($db2);

        // 1. Connection 1 begins transaction and acquires exclusive row lock via AppointmentModel
        $db1->transBegin();
        $res1 = $model1->findForUpdateByClinic((int) $this->clinic, $appointmentId);
        self::assertNotNull($res1);
        self::assertSame('scheduled', $res1['status']);

        // 2. Connection 2 sets a 1-second lock wait timeout and attempts FOR UPDATE on the same row via AppointmentModel.
        // Because Connection 1 holds the row lock, Connection 2 will experience real InnoDB lock contention and timeout.
        $db2->query("SET innodb_lock_wait_timeout = 1");
        $db2->transException(true);
        $db2->transBegin();

        $lockWaitTimeoutOccurred = false;
        try {
            $model2->findForUpdateByClinic((int) $this->clinic, $appointmentId);
        } catch (\Throwable $e) {
            $dbErr = $db2->error();
            $isDriver1205 = ($dbErr['code'] ?? 0) === 1205
                || (stripos($dbErr['message'] ?? '', '1205') !== false || stripos($dbErr['message'] ?? '', 'Lock wait timeout') !== false);
            $isWrapped1205 = str_contains($e->getMessage(), '1205')
                || stripos($e->getMessage(), 'Lock wait timeout') !== false;

            if ($isDriver1205 || $isWrapped1205) {
                $lockWaitTimeoutOccurred = true;
            }
        }
        self::assertTrue($lockWaitTimeoutOccurred, 'Connection 2 must experience real InnoDB lock wait timeout while Connection 1 holds FOR UPDATE lock via AppointmentModel');

        // Roll back Connection 2's failed transaction
        $db2->transRollback();

        // 3. Connection 1 updates status conditionally via AppointmentModel from 'scheduled' to 'arrived'
        $updated = $model1->conditionalStatusUpdate((int) $this->clinic, $appointmentId, 'scheduled', 'arrived');
        self::assertTrue($updated, 'Connection 1 conditionalStatusUpdate must succeed');

        // Insert audit log in Connection 1's transaction
        $now = date('Y-m-d H:i:s');
        $desc = "Appointment visit stage changed from scheduled to arrived";
        $db1->table('activity_logs')->insert([
            'user_id' => $this->actor,
            'action' => 'status',
            'entity_type' => 'appointment',
            'entity_id' => $appointmentId,
            'description' => $desc,
            'created_at' => $now,
        ]);

        // Commit Connection 1
        $db1->transCommit();

        // 4. Connection 2 now reads the row with FOR UPDATE in a new transaction via AppointmentModel
        $db2->transBegin();
        $res2 = $model2->findForUpdateByClinic((int) $this->clinic, $appointmentId);
        self::assertNotNull($res2);
        self::assertSame('arrived', $res2['status'], 'Connection 2 must see updated committed status via AppointmentModel');

        // Roll back Connection 2 read transaction
        $db2->transRollback();

        // Close unshared connections
        $db1->close();
        $db2->close();

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
     * Bounded multi-worker concurrency verification: simultaneous production-controller transition attempts.
     * Note: These workers invoke Appointment::transition directly in separate CLI processes under
     * isolated MariaDB transaction isolation; this verifies production-controller SQL concurrency and
     * transactional row-level conflict detection, NOT routed HTTP auth/CSRF filter execution.
     * (Separate routed HTTP cases through PhaseBFixture test routes, filters, RBAC, and CSRF).
     *
     * Two independent worker processes invoke transition with identical expected_status='scheduled'.
     * Exactly one worker must yield HTTP 200 and the other HTTP 409 Conflict with canonical allowed_statuses.
     */
    public function testTwoConcurrentWorkersYieldOne200AndOne409(): void
    {
        $appointmentId = $this->createAppointment('scheduled');

        $repoRoot = dirname(__DIR__, 2);
        $workerDir = sys_get_temp_dir() . '/dental_concurrency_' . bin2hex(random_bytes(8));
        if (!mkdir($workerDir, 0700, true)) {
            throw new \RuntimeException('Failed to create private worker directory');
        }
        if (fileowner($workerDir) !== getmyuid() || is_link($workerDir)) {
            throw new \RuntimeException('Insecure worker directory detected');
        }
        chmod($workerDir, 0700);

        $writableDir = $workerDir . '/writable';
        mkdir($writableDir . '/cache', 0700, true);
        mkdir($writableDir . '/logs', 0700, true);
        mkdir($writableDir . '/session', 0700, true);

        $workerScript = $workerDir . '/worker.php';
        $scriptContent = <<<'PHP'
<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$repoRoot = $argv[1] ?? '';
$payloadPath = $argv[2] ?? '';

if ($repoRoot === '' || $payloadPath === '' || !file_exists($payloadPath)) {
    fwrite(STDERR, "Invalid worker invocation\n");
    exit(1);
}

if (fileowner($payloadPath) !== getmyuid() || is_link($payloadPath)) {
    fwrite(STDERR, "Insecure payload file\n");
    exit(1);
}

$payload = json_decode((string) file_get_contents($payloadPath), true, 512, JSON_THROW_ON_ERROR);
@unlink($payloadPath);

$appointmentId = (int) ($payload['appointment_id'] ?? 0);
$sessionData = (array) ($payload['session'] ?? []);
$readyFile = (string) ($payload['ready_file'] ?? '');
$barrierFile = (string) ($payload['barrier_file'] ?? '');
$resultFile = (string) ($payload['result_file'] ?? '');

if ($appointmentId <= 0 || empty($sessionData) || $readyFile === '' || $barrierFile === '' || $resultFile === '') {
    fwrite(STDERR, "Invalid payload contents\n");
    exit(1);
}

require_once $repoRoot . '/system/Test/bootstrap.php';
require_once $repoRoot . '/scripts/TestRunnerConnectionBootstrap.php';

session()->set($sessionData);

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

// Signal worker ready
file_put_contents($readyFile, (string) getmypid());
chmod($readyFile, 0600);

// Wait for synchronization barrier (bounded 5.0s timeout)
$waitStart = microtime(true);
while (!file_exists($barrierFile)) {
    if ((microtime(true) - $waitStart) > 5.0) {
        fwrite(STDERR, "Worker timeout waiting for barrier\n");
        exit(2);
    }
    usleep(200);
}

try {
    $res = $controller->transition($appointmentId);
    $statusCode = $res->getStatusCode();
    $body = json_decode((string) $res->getBody(), true);
    file_put_contents($resultFile, json_encode([
        'status_code' => $statusCode,
        'body' => $body,
        'pid' => getmypid(),
    ], JSON_THROW_ON_ERROR));
    chmod($resultFile, 0600);
} catch (\Throwable $e) {
    file_put_contents($resultFile, json_encode([
        'status_code' => 500,
        'error' => $e->getMessage(),
        'pid' => getmypid(),
    ], JSON_THROW_ON_ERROR));
    chmod($resultFile, 0600);
}
exit(0);
PHP;

        file_put_contents($workerScript, $scriptContent);
        chmod($workerScript, 0700);

        $readyFile1 = $workerDir . '/worker1.ready';
        $readyFile2 = $workerDir . '/worker2.ready';
        $barrierFile = $workerDir . '/barrier.flag';
        $resultFile1 = $workerDir . '/result1.json';
        $resultFile2 = $workerDir . '/result2.json';
        $payloadFile1 = $workerDir . '/payload1.json';
        $payloadFile2 = $workerDir . '/payload2.json';

        $sessionData = session()->get();
        file_put_contents($payloadFile1, json_encode([
            'appointment_id' => $appointmentId,
            'session' => $sessionData,
            'ready_file' => $readyFile1,
            'barrier_file' => $barrierFile,
            'result_file' => $resultFile1,
        ], JSON_THROW_ON_ERROR));
        chmod($payloadFile1, 0600);

        file_put_contents($payloadFile2, json_encode([
            'appointment_id' => $appointmentId,
            'session' => $sessionData,
            'ready_file' => $readyFile2,
            'barrier_file' => $barrierFile,
            'result_file' => $resultFile2,
        ], JSON_THROW_ON_ERROR));
        chmod($payloadFile2, 0600);

        $testsConfig = (new \Config\Database())->tests;
        $connectionKeys = ['hostname','port','database','username','password','DBDriver','DBPrefix'];
        $connectionPayload = json_encode(array_intersect_key($testsConfig, array_flip($connectionKeys)), JSON_THROW_ON_ERROR);

        $childEnv = array_merge(getenv(), [
            'WRITEPATH' => $writableDir . DIRECTORY_SEPARATOR,
            'DENTA_VERIFIED_TEST_CONNECTION' => $connectionPayload,
        ]);

        $cmd1 = [PHP_BINARY, $workerScript, $repoRoot, $payloadFile1];
        $cmd2 = [PHP_BINARY, $workerScript, $repoRoot, $payloadFile2];

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc1 = null;
        $proc2 = null;
        $pipes1 = [];
        $pipes2 = [];

        try {
            $proc1 = proc_open($cmd1, $descriptors, $pipes1, $repoRoot, $childEnv);
            $proc2 = proc_open($cmd2, $descriptors, $pipes2, $repoRoot, $childEnv);

            self::assertIsResource($proc1, 'Failed to launch worker 1');
            self::assertIsResource($proc2, 'Failed to launch worker 2');

            // Wait for checked two-worker ready acknowledgments with bounded timeout
            $readyStart = microtime(true);
            $bothReady = false;
            while ((microtime(true) - $readyStart) < 5.0) {
                if (file_exists($readyFile1) && file_exists($readyFile2)) {
                    $bothReady = true;
                    break;
                }
                usleep(1000); // 1ms
            }
            self::assertTrue($bothReady, 'Both concurrency workers must acknowledge ready within 5.0s timeout');

            // Release barrier simultaneously
            touch($barrierFile);

            // Drain stdout and stderr concurrently without blocking
            stream_set_blocking($pipes1[1], false);
            stream_set_blocking($pipes1[2], false);
            stream_set_blocking($pipes2[1], false);
            stream_set_blocking($pipes2[2], false);

            $stdout1 = '';
            $stderr1 = '';
            $stdout2 = '';
            $stderr2 = '';

            $drainStart = microtime(true);
            while ((microtime(true) - $drainStart) < 10.0) {
                $allClosed = true;
                foreach ([$pipes1[1], $pipes1[2], $pipes2[1], $pipes2[2]] as $pipe) {
                    if (is_resource($pipe) && !feof($pipe)) {
                        $allClosed = false;
                        break;
                    }
                }
                if ($allClosed) {
                    break;
                }

                $read = array_values(array_filter([$pipes1[1], $pipes1[2], $pipes2[1], $pipes2[2]], static fn($p) => is_resource($p) && !feof($p)));
                if (empty($read)) {
                    break;
                }
                $write = null;
                $except = null;
                $numChanged = @stream_select($read, $write, $except, 0, 50000);
                if ($numChanged > 0) {
                    foreach ($read as $r) {
                        $chunk = fread($r, 8192);
                        if ($chunk !== false && $chunk !== '') {
                            if ($r === $pipes1[1]) $stdout1 .= $chunk;
                            elseif ($r === $pipes1[2]) $stderr1 .= $chunk;
                            elseif ($r === $pipes2[1]) $stdout2 .= $chunk;
                            elseif ($r === $pipes2[2]) $stderr2 .= $chunk;
                        }
                    }
                }
            }

            // Close all pipes before calling proc_close
            foreach ([$pipes1, $pipes2] as $pipeSet) {
                foreach ($pipeSet as $p) {
                    if (is_resource($p)) {
                        fclose($p);
                    }
                }
            }

            $exitCode1 = proc_close($proc1);
            $proc1 = null;
            $exitCode2 = proc_close($proc2);
            $proc2 = null;

            self::assertSame(0, $exitCode1, 'Worker 1 must exit with code 0. Stderr: ' . $stderr1);
            self::assertSame(0, $exitCode2, 'Worker 2 must exit with code 0. Stderr: ' . $stderr2);

            self::assertFileExists($resultFile1, 'Result file 1 missing');
            self::assertFileExists($resultFile2, 'Result file 2 missing');

            $out1 = json_decode((string) file_get_contents($resultFile1), true, 512, JSON_THROW_ON_ERROR);
            $out2 = json_decode((string) file_get_contents($resultFile2), true, 512, JSON_THROW_ON_ERROR);

            $statusCodes = [$out1['status_code'], $out2['status_code']];
            sort($statusCodes);

            self::assertSame([200, 409], $statusCodes, 'Concurrent execution must yield exactly one 200 and one 409');

            $conflictResponse = ($out1['status_code'] === 409) ? $out1['body'] : $out2['body'];
            self::assertSame('CONFLICT', $conflictResponse['error']);
            self::assertSame('arrived', $conflictResponse['current_status']);
            self::assertSame(['ready', 'in_chair', 'cancelled'], $conflictResponse['allowed_statuses'], 'Allowed statuses from arrived must match canonical stage transitions');

            // Verify DB final state and single audit log
            $finalAppt = $this->db->table('appointments')->where('id', $appointmentId)->get()->getRowArray();
            self::assertSame('arrived', $finalAppt['status']);

            $auditCount = $this->db->table('activity_logs')
                ->where('entity_type', 'appointment')
                ->where('entity_id', $appointmentId)
                ->where('action', 'status')
                ->countAllResults();
            self::assertSame(1, $auditCount, 'Exactly 1 audit log must be committed across both workers');
        } finally {
            if (is_resource($proc1)) {
                proc_terminate($proc1, 9);
                proc_close($proc1);
            }
            if (is_resource($proc2)) {
                proc_terminate($proc2, 9);
                proc_close($proc2);
            }
            if (is_dir($workerDir)) {
                $it = new \RecursiveDirectoryIterator($workerDir, \FilesystemIterator::SKIP_DOTS);
                $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($files as $file) {
                    if ($file->isDir()) {
                        @rmdir($file->getRealPath());
                    } else {
                        @unlink($file->getRealPath());
                    }
                }
                @rmdir($workerDir);
            }
        }
    }

    /**
     * JS-disabled fallback: standard non-AJAX POST returns HTTP 302 redirect to return_to dashboard.
     */
    public function testNonAjaxTransitionRedirectsToDashboard(): void
    {
        $appointmentId = $this->createAppointment('scheduled');

        $security = service('security');
        $config = config('Security');
        $hash = $security->getHash();

        $res = $this->withSession(session()->get())
            ->withHeaders([
                'Cookie' => $config->cookieName . '=' . $hash,
                'X-CSRF-TOKEN' => $hash,
            ])
            ->post('appointment/' . $appointmentId . '/transition', [
                'status' => 'arrived',
                'expected_status' => 'scheduled',
                'return_to' => 'dashboard',
                $config->tokenName => $hash,
            ]);

        $res->assertStatus(302);
        $res->assertRedirectTo(base_url('dashboard'));

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
