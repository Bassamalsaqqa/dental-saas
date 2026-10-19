<?php

declare(strict_types=1);

/**
 * Root-executable real-MariaDB synthetic test helper for FinanceCreationGuard concurrency contracts.
 * Executes exact 6 real model concurrency cases:
 * 1. patient / payment (creator first)
 * 2. patient / payment (deleter first)
 * 3. examination / refund (creator first)
 * 4. examination / refund (deleter first)
 * 5. clinic / expense (creator first)
 * 6. clinic / expense (deleter first)
 * Validates synthetic identity, private bootstrap, and failure modes. Never accesses production.
 */
if (PHP_SAPI !== 'cli') { exit(2); }

$bootstrap = $argv[1] ?? '';
$privateRoot = '/home/u556956644/.denta-stage-20261017';
if (!str_starts_with($bootstrap, $privateRoot . '/') || !is_file($bootstrap)
    || is_link($bootstrap) || realpath($bootstrap) !== $bootstrap) {
    throw new RuntimeException('Explicit canonical private bootstrap required.');
}

require $bootstrap;

if (realpath($private) !== $privateRoot || !str_starts_with(realpath(HOMEPATH) ?: '', $privateRoot . '/remediation-20261018-')
    || $db->database !== 'u556956644_dentstage1017' || $db->username !== 'u556956644_dstage1017'
    || !in_array($db->hostname, ['127.0.0.1', 'localhost'], true) || (int) $db->port !== 3306
    || $db->DBPrefix !== '' || (int) $db->transDepth !== 0) {
    throw new RuntimeException('Private synthetic connection required.');
}

$identity = $db->query('SELECT guid, domain, synthetic_only FROM _denta_stage_identity WHERE id=1')->getRowArray();
if (($identity['guid'] ?? '') !== 'b60809432ec1d915b0b975aef8484e43'
    || ($identity['domain'] ?? '') !== 'dentastaging.palsync.net' || (int) ($identity['synthetic_only'] ?? 0) !== 1) {
    throw new RuntimeException('Synthetic identity differs.');
}

foreach ($db->query('SHOW GRANTS')->getResultArray() as $grantRow) {
    $grant = str_replace('\\_', '_', (string) reset($grantRow));
    if (str_contains($grant, 'WITH GRANT OPTION')
        || (str_contains($grant, ' ON *.* ') && !str_starts_with($grant, 'GRANT USAGE ON *.* '))
        || (preg_match('/ ON `([^`]+)`\./', $grant, $match) && $match[1] !== $db->database)) {
        throw new RuntimeException('Excessive synthetic grants.');
    }
}

if (!class_exists(\App\Services\FinanceCreationGuard::class)) {
    throw new RuntimeException('FinanceCreationGuard class is absent.');
}
if (!method_exists(\App\Models\FinanceModel::class, 'lockFinanceCreationIdentity')) {
    throw new RuntimeException('FinanceModel lockFinanceCreationIdentity callback is absent.');
}
if (!method_exists(\App\Models\FinanceModel::class, 'lockInvoiceCreationIdentity')) {
    throw new RuntimeException('FinanceModel lockInvoiceCreationIdentity callback is absent.');
}

$db->query('SET SESSION innodb_lock_wait_timeout=8');

function raceBarrierWait(string $path, int $seconds = 10): void
{
    $deadline = microtime(true) + $seconds;
    do {
        clearstatcache(true, $path);
        if (is_file($path)) { return; }
        usleep(10000);
    } while (microtime(true) < $deadline);
    throw new RuntimeException('Barrier timeout: ' . basename($path));
}

function raceSetContext(int $clinicId): void
{
    session()->set(['active_clinic_id' => $clinicId, 'user_id' => 1, 'global_mode' => false]);
    \App\Libraries\ApiRuntimeContext::setClinicId($clinicId);
    \App\Libraries\ApiRuntimeContext::setUserId(1);
}

function raceCreateFixture($db, string $prefix): array
{
    $run = $prefix . '_' . bin2hex(random_bytes(4));
    $uuid = bin2hex(random_bytes(16));
    $formattedUuid = substr($uuid, 0, 8) . '-' . substr($uuid, 8, 4) . '-' . substr($uuid, 12, 4) . '-' . substr($uuid, 16, 4) . '-' . substr($uuid, 20);

    if (!$db->table('clinics')->insert(['name' => $run, 'slug' => strtolower($run), 'status' => 'active', 'uuid' => $formattedUuid])) {
        throw new RuntimeException('Synthetic clinic insert failed.');
    }
    $clinicId = (int) $db->insertID();

    if (!$db->table('patients')->insert([
        'clinic_id' => $clinicId, 'patient_id' => $run, 'first_name' => 'Synthetic',
        'last_name' => 'Race', 'phone' => '0000000000', 'date_of_birth' => '1990-01-01', 'gender' => 'other',
    ])) {
        throw new RuntimeException('Synthetic patient insert failed.');
    }
    $patientId = (int) $db->insertID();

    if (!$db->table('examinations')->insert([
        'clinic_id' => $clinicId, 'patient_id' => $patientId, 'examination_id' => $run,
        'examination_date' => '2095-08-01', 'examination_type' => 'initial', 'chief_complaint' => 'Synthetic race',
    ])) {
        throw new RuntimeException('Synthetic examination insert failed.');
    }
    $examinationId = (int) $db->insertID();

    return [
        'run' => $run,
        'clinic_id' => $clinicId,
        'patient_id' => $patientId,
        'examination_id' => $examinationId,
    ];
}

// ------------------------------------------------------------------------------------------------
// WORKER MODE
// ------------------------------------------------------------------------------------------------
if (($argv[2] ?? '') === 'worker') {
    $manifest = $argv[3] ?? '';
    $role = $argv[4] ?? ''; // 'creator_first', 'deleter_after_creator', 'deleter_first', 'creator_after_deleter'
    $target = $argv[5] ?? ''; // 'patient_payment', 'exam_refund', 'clinic_expense'

    if (!preg_match('#^' . preg_quote($privateRoot, '#') . '/finance-creation-race-S0R_(CF|DF)_[a-f0-9]{8}\.json$#D', $manifest)
        || !is_file($manifest) || is_link($manifest) || realpath($manifest) !== $manifest
        || !in_array($role, ['creator_first', 'deleter_after_creator', 'deleter_first', 'creator_after_deleter'], true)
        || !in_array($target, ['patient_payment', 'exam_refund', 'clinic_expense'], true)) {
        throw new RuntimeException('Worker manifest rejected.');
    }

    $fixture = json_decode(file_get_contents($manifest), true, 512, JSON_THROW_ON_ERROR);
    if (!preg_match('/^S0R_(CF|DF)_[a-f0-9]{8}$/D', $fixture['run'] ?? '')
        || basename($manifest) !== 'finance-creation-race-' . $fixture['run'] . '.json') {
        throw new RuntimeException('Owned run identity differs.');
    }
    foreach (['clinic_id', 'patient_id', 'examination_id'] as $key) {
        if (!is_int($fixture[$key] ?? null) || $fixture[$key] <= 0) {
            throw new RuntimeException('Owned synthetic parent ID required.');
        }
    }
    $clinicId = $fixture['clinic_id'];
    $patientId = $fixture['patient_id'];
    $examId = $fixture['examination_id'];
    $clinic = $db->table('clinics')->where('id', $clinicId)->get()->getRowArray();
    $patient = $db->table('patients')->where('id', $patientId)->where('clinic_id', $clinicId)->get()->getRowArray();
    $exam = $db->table('examinations')->where('id', $examId)->where('clinic_id', $clinicId)->where('patient_id', $patientId)->get()->getRowArray();
    if (($clinic['name'] ?? '') !== $fixture['run'] || ($patient['patient_id'] ?? '') !== $fixture['run']
        || ($exam['examination_id'] ?? '') !== $fixture['run']) {
        throw new RuntimeException('Deletion targets are not owned by this synthetic run.');
    }

    raceSetContext($clinicId);

    $txnMap = [
        'patient_payment' => ['type' => 'payment', 'entity' => 'patient', 'use_exam' => false],
        'exam_refund'     => ['type' => 'refund',  'entity' => 'exam',    'use_exam' => true],
        'clinic_expense'  => ['type' => 'expense', 'entity' => 'clinic',  'use_exam' => false],
    ];
    $spec = $txnMap[$target] ?? throw new RuntimeException('Unknown target: ' . $target);

    $barrierPrefix = $manifest . '.' . $target . '.' . $role;

    try {
        if ($role === 'creator_first') {
            // Subclass FinanceModel to hook lockFinanceCreationIdentity
            $model = new class($db, $barrierPrefix) extends \App\Models\FinanceModel {
                public function __construct($db, private string $barrier) { parent::__construct($db); }
                protected function lockFinanceCreationIdentity(array $data): array
                {
                    $data = parent::lockFinanceCreationIdentity($data);
                    if ((int) $this->db->transDepth !== 1) {
                        throw new RuntimeException('Creator transaction not owned.');
                    }
                    file_put_contents($this->barrier . '.locked', 'parents locked', LOCK_EX);
                    raceBarrierWait($this->barrier . '.release');
                    return $data;
                }
            };

            if (!$db->transBegin()) { throw new RuntimeException('Creator transBegin failed.'); }
            try {
                $payload = [
                    'clinic_id' => $clinicId,
                    'patient_id' => $patientId,
                    'examination_id' => $spec['use_exam'] ? $examId : null,
                    'transaction_id' => $fixture['run'] . '_cf',
                    'transaction_type' => $spec['type'],
                    'amount' => 50,
                    'amount_original' => 50,
                    'currency' => 'ILS',
                    'exchange_rate' => 1.0,
                    'payment_method' => 'cash',
                    'liquidity_account' => 'safe_cash',
                    'payment_status' => 'paid',
                    'service_type' => 'other',
                    'discount_amount' => 0,
                    'tax_percentage' => 0,
                    'amount_paid' => 50,
                    'balance_due' => 0,
                    'transaction_date' => '2095-08-01',
                    'created_by' => 1,
                ];
                if ($spec['type'] === 'expense') {
                    $payload['expense_category'] = 'miscellaneous';
                    $payload['counterparty_name'] = 'Synthetic Supplier';
                }

                $id = $model->insert($payload);
                if (!$id || !$db->transStatus()) {
                    throw new RuntimeException('Creator insert failed: ' . json_encode($model->errors()));
                }
                if (!$db->transCommit()) { throw new RuntimeException('Creator commit failed.'); }
                $result = ['creator_id' => (int) $id, 'committed' => true];
            } catch (\Throwable $err) {
                if ((int) $db->transDepth > 0) { $db->transRollback(); }
                throw $err;
            }
        } elseif ($role === 'deleter_after_creator') {
            file_put_contents($barrierPrefix . '.entered', 'deleter entered', LOCK_EX);
            $guard = new \App\Services\FinancialParentDeletionGuard($db);
            $mutationCalled = false;
            try {
                if ($spec['entity'] === 'patient') {
                    $guard->deletePatient($clinicId, $patientId, static function () use (&$mutationCalled) { $mutationCalled = true; return true; });
                } elseif ($spec['entity'] === 'exam') {
                    $guard->deleteExamination($clinicId, $examId, static function () use (&$mutationCalled) { $mutationCalled = true; return true; });
                } else {
                    $guard->deleteClinic($clinicId, static function () use (&$mutationCalled) { $mutationCalled = true; return true; });
                }
                throw new RuntimeException('Deleter unexpectedly succeeded against committed finance history.');
            } catch (\App\Services\FinancialParentHasHistoryException) {
                if ($mutationCalled) { throw new RuntimeException('Blocked deletion executed mutation callback.'); }
                $result = ['history_rejected' => true, 'mutation_called' => false];
            }
        } elseif ($role === 'deleter_first') {
            $guard = new \App\Services\FinancialParentDeletionGuard($db);
            $mutationExecuted = false;

            $deleteOp = static function () use ($db, $spec, $clinicId, $patientId, $examId, $barrierPrefix, &$mutationExecuted): bool {
                file_put_contents($barrierPrefix . '.holding-lock', 'deleter holding lock inside mutation', LOCK_EX);
                raceBarrierWait($barrierPrefix . '.release');
                // Clean up children if clinic deletion requires FK ordering
                if ($spec['entity'] === 'clinic') {
                    $db->table('examinations')->where('id', $examId)->delete();
                    $db->table('patients')->where('id', $patientId)->delete();
                    $db->table('clinics')->where('id', $clinicId)->delete();
                } elseif ($spec['entity'] === 'patient') {
                    $db->table('examinations')->where('id', $examId)->delete();
                    $db->table('patients')->where('id', $patientId)->delete();
                } else {
                    $db->table('examinations')->where('id', $examId)->delete();
                }
                $mutationExecuted = true;
                return true;
            };

            if ($spec['entity'] === 'patient') {
                $guard->deletePatient($clinicId, $patientId, $deleteOp);
            } elseif ($spec['entity'] === 'exam') {
                $guard->deleteExamination($clinicId, $examId, $deleteOp);
            } else {
                $guard->deleteClinic($clinicId, $deleteOp);
            }

            if (!$mutationExecuted) { throw new RuntimeException('Deleter mutation did not execute.'); }
            $result = ['deleter_completed' => true];
        } elseif ($role === 'creator_after_deleter') {
            file_put_contents($barrierPrefix . '.entered', 'creator entered', LOCK_EX);
            $model = new \App\Models\FinanceModel($db);
            if (!$db->transBegin()) { throw new RuntimeException('Creator transBegin failed.'); }
            try {
                $payload = [
                    'clinic_id' => $clinicId,
                    'patient_id' => $patientId,
                    'examination_id' => $spec['use_exam'] ? $examId : null,
                    'transaction_id' => $fixture['run'] . '_cd',
                    'transaction_type' => $spec['type'],
                    'amount' => 50,
                    'amount_original' => 50,
                    'currency' => 'ILS',
                    'exchange_rate' => 1.0,
                    'payment_method' => 'cash',
                    'liquidity_account' => 'safe_cash',
                    'payment_status' => 'paid',
                    'service_type' => 'other',
                    'discount_amount' => 0,
                    'tax_percentage' => 0,
                    'amount_paid' => 50,
                    'balance_due' => 0,
                    'transaction_date' => '2095-08-01',
                    'created_by' => 1,
                ];
                if ($spec['type'] === 'expense') {
                    $payload['expense_category'] = 'miscellaneous';
                    $payload['counterparty_name'] = 'Synthetic Supplier';
                }

                $model->insert($payload);
                throw new RuntimeException('Creator insert unexpectedly succeeded against deleted parent.');
            } catch (\App\Services\FinanceCreationParentException $expected) {
                if ((int) $db->transDepth > 0) { $db->transRollback(); }
                // Verify no orphan row was inserted
                $orphan = $db->table('finances')->where('transaction_id', $fixture['run'] . '_cd')->get()->getRowArray();
                if ($orphan) { throw new RuntimeException('Orphan finance row was persisted.'); }
                $result = ['missing_parent_rejected' => true, 'orphan_prevented' => true];
            }
        } else {
            throw new RuntimeException('Unknown role: ' . $role);
        }

        if ((int) $db->transDepth !== 0 || !$db->transStatus()) {
            throw new RuntimeException('Worker transaction leaked or unhealthy.');
        }

        file_put_contents(
            $barrierPrefix . '.json',
            json_encode(['status' => 'PASS', 'result' => $result], JSON_THROW_ON_ERROR),
            LOCK_EX
        );
    } catch (\Throwable $error) {
        file_put_contents(
            $barrierPrefix . '.json',
            json_encode(['status' => 'FAIL', 'message' => $error->getMessage()], JSON_THROW_ON_ERROR),
            LOCK_EX
        );
        exit(1);
    }
    exit(0);
}

// ------------------------------------------------------------------------------------------------
// ROOT / MAIN COORDINATOR MODE
// ------------------------------------------------------------------------------------------------

// 1. In-process Transaction Ownership & Guard Rejections Test
$setupFixture = raceCreateFixture($db, 'S0R_SETUP');
raceSetContext($setupFixture['clinic_id']);
$testModel = new \App\Models\FinanceModel($db);

// Failure test 1: Insert without active transaction must fail closed
$txBypassCaught = false;
try {
    $testModel->insert([
        'clinic_id' => $setupFixture['clinic_id'],
        'patient_id' => $setupFixture['patient_id'],
        'transaction_type' => 'expense',
        'expense_category' => 'miscellaneous',
        'counterparty_name' => 'Test',
        'amount' => 10,
        'amount_original' => 10,
        'currency' => 'ILS',
        'exchange_rate' => 1.0,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'transaction_date' => '2095-08-01',
    ]);
} catch (\DomainException $e) {
    if (str_contains($e->getMessage(), 'active transaction')) {
        $txBypassCaught = true;
    }
}
if (!$txBypassCaught) {
    throw new RuntimeException('Direct insert without transaction was unexpectedly allowed.');
}

// Failure test 2: Context clinic mismatch must fail closed
$contextMismatchCaught = false;
if (!$db->transBegin()) { throw new RuntimeException('Setup transBegin failed.'); }
try {
    $testModel->insert([
        'clinic_id' => 999999, // Mismatched clinic
        'patient_id' => $setupFixture['patient_id'],
        'transaction_type' => 'payment',
        'amount' => 10,
        'amount_original' => 10,
        'currency' => 'ILS',
        'exchange_rate' => 1.0,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'transaction_date' => '2095-08-01',
    ]);
} catch (\DomainException $e) {
    if (str_contains($e->getMessage(), 'active context')) {
        $contextMismatchCaught = true;
    }
} finally {
    if ((int) $db->transDepth > 0) { $db->transRollback(); }
}
if (!$contextMismatchCaught) {
    throw new RuntimeException('Insert with mismatched clinic context was unexpectedly allowed.');
}

// Strict reference parsing and nullable expense references use the real guarded connection.
$edgeBase = ['clinic_id' => $setupFixture['clinic_id'], 'patient_id' => $setupFixture['patient_id'],
    'examination_id' => null, 'transaction_type' => 'expense'];
$invalidReferences = [
    ['clinic_id' => '1e3'], ['patient_id' => '1e3'], ['patient_id' => '2147483648'],
    ['examination_id' => '1e3'], ['patient_id' => null, 'examination_id' => $setupFixture['examination_id']],
];
foreach ($invalidReferences as $changes) {
    if (!$db->transBegin()) { throw new RuntimeException('Reference edge transaction failed.'); }
    $rejected = false;
    try {
        (new \App\Services\FinanceCreationGuard($db))->validateAndLock(
            array_replace($edgeBase, $changes), $setupFixture['clinic_id']);
    } catch (\App\Services\FinanceCreationException) { $rejected = true; }
    finally { $db->transRollback(); }
    if (!$rejected || (int) $db->transDepth !== 0 || !$db->transStatus()) {
        throw new RuntimeException('Malformed or incomplete parent reference was not rejected.');
    }
}
if (!$db->transBegin()) { throw new RuntimeException('Nullable reference transaction failed.'); }
try {
    $normalized = (new \App\Services\FinanceCreationGuard($db))->validateAndLock(
        array_replace($edgeBase, ['patient_id' => '', 'examination_id' => '']), $setupFixture['clinic_id']);
    if ($normalized['patient_id'] !== null || $normalized['examination_id'] !== null) {
        throw new RuntimeException('Optional empty references were not normalized to null.');
    }
} finally { $db->transRollback(); }

// Bulk model entrypoints do not execute per-record callbacks; explicitly reject both.
$batchPayload = ['clinic_id' => $setupFixture['clinic_id'], 'patient_id' => $setupFixture['patient_id'],
    'transaction_id' => $setupFixture['run'] . '_batch', 'transaction_type' => 'expense',
    'amount' => 10, 'currency' => 'ILS', 'payment_method' => 'cash', 'payment_status' => 'pending',
    'service_type' => 'other', 'expense_category' => 'miscellaneous'];
foreach (['insertBatch', 'updateBatch'] as $method) {
    $caught = false;
    try {
        $batchModel = new \App\Models\FinanceModel($db);
        if ($method === 'insertBatch') { $batchModel->insertBatch([$batchPayload]); }
        else { $batchModel->updateBatch([['id' => 2147483647, 'description' => 'Rejected synthetic bulk mutation']], 'id'); }
    } catch (\DomainException $expected) {
        $caught = str_contains($expected->getMessage(), 'batch writes');
    }
    if (!$caught || (int) $db->transDepth !== 0 || !$db->transStatus()) {
        throw new RuntimeException('Unsupported model batch writer did not fail closed: ' . $method);
    }
}

// 2. Execute EXACT 6 Concurrency Cases with fresh new fixture per case
$caseResults = [];
$targets = ['patient_payment', 'exam_refund', 'clinic_expense'];

foreach ($targets as $target) {
    // --- Creator First Case ---
    $cfFixture = raceCreateFixture($db, 'S0R_CF');
    $cfManifest = $privateRoot . '/finance-creation-race-' . $cfFixture['run'] . '.json';
    $handle = fopen($cfManifest, 'x');
    if ($handle === false) { throw new RuntimeException('Manifest collision.'); }
    fwrite($handle, json_encode($cfFixture, JSON_THROW_ON_ERROR)); fclose($handle);
    chmod($cfManifest, 0600);

    $cfProcs = [];
    $cfReleaseFile = $cfManifest . '.' . $target . '.creator_first.release';
    try {
        // 1. Launch creator
        $pipes = [];
        $cfProcs['creator'] = proc_open(
            [PHP_BINARY, __FILE__, $bootstrap, 'worker', $cfManifest, 'creator_first', $target],
            [0 => ['pipe', 'r'], 1 => ['file', $cfManifest . '.cf_creator.out', 'a'], 2 => ['file', $cfManifest . '.cf_creator.err', 'a']],
            $pipes
        );
        fclose($pipes[0]);
        raceBarrierWait($cfManifest . '.' . $target . '.creator_first.locked');

        // 2. Launch deleter (must wait on lock)
        $cfProcs['deleter'] = proc_open(
            [PHP_BINARY, __FILE__, $bootstrap, 'worker', $cfManifest, 'deleter_after_creator', $target],
            [0 => ['pipe', 'r'], 1 => ['file', $cfManifest . '.cf_deleter.out', 'a'], 2 => ['file', $cfManifest . '.cf_deleter.err', 'a']],
            $pipes
        );
        fclose($pipes[0]);
        raceBarrierWait($cfManifest . '.' . $target . '.deleter_after_creator.entered');

        usleep(250000); // 250ms delay verifying deleter is waiting on creator's parent lock
        clearstatcache();
        if (is_file($cfManifest . '.' . $target . '.deleter_after_creator.json')) {
            throw new RuntimeException('Deleter completed without waiting for creator parent lock: ' . $target);
        }

        // 3. Release creator
        file_put_contents($cfReleaseFile, 'release creator', LOCK_EX);

        foreach ($cfProcs as $roleKey => $proc) {
            $exit = proc_close($proc);
            unset($cfProcs[$roleKey]);
            if ($exit !== 0) { throw new RuntimeException("Worker failed in creator-first {$target}: {$roleKey}"); }
        }

        $cfCreatorRes = json_decode(file_get_contents($cfManifest . '.' . $target . '.creator_first.json'), true, 512, JSON_THROW_ON_ERROR);
        $cfDeleterRes = json_decode(file_get_contents($cfManifest . '.' . $target . '.deleter_after_creator.json'), true, 512, JSON_THROW_ON_ERROR);

        if (($cfCreatorRes['status'] ?? '') !== 'PASS' || ($cfDeleterRes['status'] ?? '') !== 'PASS') {
            throw new RuntimeException("Result failure in creator-first {$target}");
        }
        $caseResults[$target . '_creator_first'] = [
            'status' => 'PASS',
            'creator' => $cfCreatorRes['result'],
            'deleter' => $cfDeleterRes['result'],
        ];
    } finally {
        if (!is_file($cfReleaseFile)) { file_put_contents($cfReleaseFile, 'release', LOCK_EX); }
        foreach ($cfProcs as $proc) { if (is_resource($proc)) { proc_terminate($proc); proc_close($proc); } }
    }

    // --- Deleter First Case ---
    $dfFixture = raceCreateFixture($db, 'S0R_DF');
    $dfManifest = $privateRoot . '/finance-creation-race-' . $dfFixture['run'] . '.json';
    $handle = fopen($dfManifest, 'x');
    if ($handle === false) { throw new RuntimeException('Manifest collision.'); }
    fwrite($handle, json_encode($dfFixture, JSON_THROW_ON_ERROR)); fclose($handle);
    chmod($dfManifest, 0600);

    $dfProcs = [];
    $dfReleaseFile = $dfManifest . '.' . $target . '.deleter_first.release';
    try {
        // 1. Launch deleter (holds lock inside mutation callback)
        $pipes = [];
        $dfProcs['deleter'] = proc_open(
            [PHP_BINARY, __FILE__, $bootstrap, 'worker', $dfManifest, 'deleter_first', $target],
            [0 => ['pipe', 'r'], 1 => ['file', $dfManifest . '.df_deleter.out', 'a'], 2 => ['file', $dfManifest . '.df_deleter.err', 'a']],
            $pipes
        );
        fclose($pipes[0]);
        raceBarrierWait($dfManifest . '.' . $target . '.deleter_first.holding-lock');

        // 2. Launch creator (must wait on deleter's lock)
        $dfProcs['creator'] = proc_open(
            [PHP_BINARY, __FILE__, $bootstrap, 'worker', $dfManifest, 'creator_after_deleter', $target],
            [0 => ['pipe', 'r'], 1 => ['file', $dfManifest . '.df_creator.out', 'a'], 2 => ['file', $dfManifest . '.df_creator.err', 'a']],
            $pipes
        );
        fclose($pipes[0]);
        raceBarrierWait($dfManifest . '.' . $target . '.creator_after_deleter.entered');

        usleep(250000); // 250ms delay verifying creator is waiting on deleter's lock
        clearstatcache();
        if (is_file($dfManifest . '.' . $target . '.creator_after_deleter.json')) {
            throw new RuntimeException('Creator completed without waiting for deleter lock: ' . $target);
        }

        // 3. Release deleter to complete deletion
        file_put_contents($dfReleaseFile, 'release deleter', LOCK_EX);

        foreach ($dfProcs as $roleKey => $proc) {
            $exit = proc_close($proc);
            unset($dfProcs[$roleKey]);
            if ($exit !== 0) { throw new RuntimeException("Worker failed in deleter-first {$target}: {$roleKey}"); }
        }

        $dfDeleterRes = json_decode(file_get_contents($dfManifest . '.' . $target . '.deleter_first.json'), true, 512, JSON_THROW_ON_ERROR);
        $dfCreatorRes = json_decode(file_get_contents($dfManifest . '.' . $target . '.creator_after_deleter.json'), true, 512, JSON_THROW_ON_ERROR);

        if (($dfDeleterRes['status'] ?? '') !== 'PASS' || ($dfCreatorRes['status'] ?? '') !== 'PASS') {
            throw new RuntimeException("Result failure in deleter-first {$target}");
        }
        $caseResults[$target . '_deleter_first'] = [
            'status' => 'PASS',
            'deleter' => $dfDeleterRes['result'],
            'creator' => $dfCreatorRes['result'],
        ];
    } finally {
        if (!is_file($dfReleaseFile)) { file_put_contents($dfReleaseFile, 'release', LOCK_EX); }
        foreach ($dfProcs as $proc) { if (is_resource($proc)) { proc_terminate($proc); proc_close($proc); } }
    }
}

echo json_encode([
    'status' => 'PASS',
    'synthetic_only' => true,
    'total_cases_verified' => count($caseResults),
    'cases' => $caseResults,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
