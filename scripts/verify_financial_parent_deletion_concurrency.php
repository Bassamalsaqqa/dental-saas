<?php

declare(strict_types=1);

/** Reviewer-owned real FinanceModel creation-versus-parent-deletion race. Never sends notifications. */
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
if (!method_exists(\App\Models\FinanceModel::class, 'lockInvoiceCreationIdentity')) {
    throw new RuntimeException('Reviewed invoice creation lock callback is absent.');
}
$db->query('SET SESSION innodb_lock_wait_timeout=8');
function parentDeletionRaceWait(string $path, int $seconds = 10): void
{
    $deadline = microtime(true) + $seconds;
    do {
        clearstatcache(true, $path);
        if (is_file($path)) { return; }
        usleep(10000);
    } while (microtime(true) < $deadline);
    throw new RuntimeException('Barrier timeout: ' . basename($path));
}
function parentDeletionRaceContext(int $clinicId): void
{
    session()->set(['active_clinic_id' => $clinicId, 'user_id' => 1, 'global_mode' => false]);
    \App\Libraries\ApiRuntimeContext::setClinicId($clinicId);
    \App\Libraries\ApiRuntimeContext::setUserId(1);
}
if (($argv[2] ?? '') === 'worker') {
    $manifest = $argv[3] ?? ''; $slot = $argv[4] ?? '';
    if (!preg_match('#^' . preg_quote($privateRoot, '#') . '/financial-parent-race-S0R_[a-f0-9]{10}\.json$#D', $manifest)
        || is_link($manifest) || realpath($manifest) !== $manifest || !in_array($slot, ['create', 'delete'], true)) {
        throw new RuntimeException('Worker manifest rejected.');
    }
    $fixture = json_decode(file_get_contents($manifest), true, 512, JSON_THROW_ON_ERROR);
    foreach (['clinic_id', 'patient_id', 'examination_id'] as $key) {
        if (!is_int($fixture[$key] ?? null) || $fixture[$key] <= 0) { throw new RuntimeException('Invalid new-owned parent.'); }
    }
    if (!preg_match('/^S0R_[a-f0-9]{10}$/D', $fixture['run'] ?? '')) { throw new RuntimeException('Run identity rejected.'); }
    parentDeletionRaceContext($fixture['clinic_id']);
    try {
        if ($slot === 'create') {
            $model = new class($db, $manifest) extends \App\Models\FinanceModel {
                public function __construct($db, private string $barrier) { parent::__construct($db); }
                protected function lockInvoiceCreationIdentity(array $data): array
                {
                    $data = parent::lockInvoiceCreationIdentity($data);
                    if ((int) $this->db->transDepth !== 1) { throw new RuntimeException('Creator transaction not owned.'); }
                    file_put_contents($this->barrier . '.parents-locked', 'clinic/patient/examination locked before insert', LOCK_EX);
                    parentDeletionRaceWait($this->barrier . '.release');
                    return $data;
                }
            };
            if (!$db->transBegin()) { throw new RuntimeException('Creator transaction failed.'); }
            try {
                $id = $model->insert(['clinic_id' => $fixture['clinic_id'], 'patient_id' => $fixture['patient_id'],
                    'examination_id' => $fixture['examination_id'], 'transaction_id' => $fixture['run'],
                    'transaction_type' => 'invoice', 'amount' => 100, 'amount_original' => 100,
                    'currency' => 'ILS', 'exchange_rate' => 1, 'payment_method' => 'cash',
                    'liquidity_account' => 'safe_cash', 'payment_status' => 'pending', 'service_type' => 'other',
                    'discount_amount' => 0, 'tax_percentage' => 0, 'amount_paid' => 0, 'balance_due' => 100,
                    'transaction_date' => '2095-08-01', 'created_by' => 1]);
                if (!$id || !$db->transStatus()) { throw new RuntimeException('Real model insert failed: ' . json_encode($model->errors())); }
                if (!$db->transCommit()) { throw new RuntimeException('Creator commit failed.'); }
                $result = ['invoice_id' => (int) $id, 'committed' => true];
            } catch (Throwable $error) {
                if ((int) $db->transDepth > 0) { $db->transRollback(); }
                throw $error;
            }
        } else {
            $callbackCalled = false;
            file_put_contents($manifest . '.delete-entered', 'entering owned delete', LOCK_EX);
            try {
                (new \App\Services\FinancialParentDeletionGuard($db))->deletePatient($fixture['clinic_id'], $fixture['patient_id'],
                    static function () use (&$callbackCalled): bool { $callbackCalled = true; return true; });
                throw new RuntimeException('Parent deletion unexpectedly accepted.');
            } catch (\App\Services\FinancialParentHasHistoryException $expected) {
                if ($callbackCalled) { throw new RuntimeException('Blocked deletion called mutation callback.'); }
                $result = ['postcommit_finance_history_rejected' => true, 'deletion_callback_called' => false];
            }
        }
        if ((int) $db->transDepth !== 0 || !$db->transStatus()) { throw new RuntimeException('Worker transaction leaked.'); }
        file_put_contents($manifest . '.' . $slot . '.json', json_encode(['status' => 'PASS', 'result' => $result], JSON_THROW_ON_ERROR), LOCK_EX);
    } catch (Throwable $error) {
        file_put_contents($manifest . '.' . $slot . '.json', json_encode(['status' => 'FAIL', 'message' => $error->getMessage()], JSON_THROW_ON_ERROR), LOCK_EX);
        exit(1);
    }
    exit(0);
}
$run = 'S0R_' . bin2hex(random_bytes(5));
$uuid = bin2hex(random_bytes(16));
if (!$db->table('clinics')->insert(['name' => $run, 'slug' => strtolower($run), 'status' => 'active',
    'uuid' => substr($uuid, 0, 8) . '-' . substr($uuid, 8, 4) . '-' . substr($uuid, 12, 4) . '-' . substr($uuid, 16, 4) . '-' . substr($uuid, 20)])) {
    throw new RuntimeException('New synthetic clinic insert failed.');
}
$clinicId = (int) $db->insertID();
if (!$db->table('patients')->insert(['clinic_id' => $clinicId, 'patient_id' => $run, 'first_name' => 'Synthetic',
    'last_name' => 'ParentRace', 'phone' => '0000000000', 'date_of_birth' => '1990-01-01', 'gender' => 'other'])) {
    throw new RuntimeException('New synthetic patient insert failed.');
}
$patientId = (int) $db->insertID();
if (!$db->table('examinations')->insert(['clinic_id' => $clinicId, 'patient_id' => $patientId,
    'examination_id' => $run, 'examination_date' => '2095-08-01', 'examination_type' => 'initial', 'chief_complaint' => 'Synthetic race'])) {
    throw new RuntimeException('New synthetic examination insert failed.');
}
$examinationId = (int) $db->insertID();
$before = [];
foreach (['clinics' => $clinicId, 'patients' => $patientId, 'examinations' => $examinationId] as $table => $id) {
    $before[$table] = $db->table($table)->where('id', $id)->get()->getRowArray();
}
$fixture = ['run' => $run, 'clinic_id' => $clinicId, 'patient_id' => $patientId, 'examination_id' => $examinationId];
$manifest = $privateRoot . '/financial-parent-race-' . $run . '.json';
$handle = fopen($manifest, 'x');
if ($handle === false) { throw new RuntimeException('Manifest collision.'); }
fwrite($handle, json_encode($fixture, JSON_THROW_ON_ERROR)); fclose($handle); chmod($manifest, 0600);
$processes = [];
try {
    foreach (['create', 'delete'] as $slot) {
        $pipes = [];
        $process = proc_open([PHP_BINARY, __FILE__, $bootstrap, 'worker', $manifest, $slot],
            [0 => ['pipe', 'r'], 1 => ['file', $manifest . '.' . $slot . '.stdout', 'a'],
                2 => ['file', $manifest . '.' . $slot . '.stderr', 'a']], $pipes, $root);
        if (!is_resource($process)) { throw new RuntimeException('Worker launch failed.'); }
        fclose($pipes[0]); $processes[$slot] = $process;
        if ($slot === 'create') { parentDeletionRaceWait($manifest . '.parents-locked'); }
    }
    parentDeletionRaceWait($manifest . '.delete-entered');
    usleep(250000); clearstatcache();
    if (is_file($manifest . '.delete.json')) { throw new RuntimeException('Delete completed without waiting for creator parent locks.'); }
    file_put_contents($manifest . '.release', 'release creator', LOCK_EX);
    $results = [];
    foreach (array_keys($processes) as $slot) {
        $exit = proc_close($processes[$slot]); unset($processes[$slot]);
        if ($exit !== 0 || !is_file($manifest . '.' . $slot . '.json')) { throw new RuntimeException('Worker failed: ' . $slot); }
        $results[$slot] = json_decode(file_get_contents($manifest . '.' . $slot . '.json'), true, 512, JSON_THROW_ON_ERROR);
        if (($results[$slot]['status'] ?? '') !== 'PASS') { throw new RuntimeException('Worker result failed: ' . $slot); }
    }
    foreach (['clinics' => $clinicId, 'patients' => $patientId, 'examinations' => $examinationId] as $table => $id) {
        if ($db->table($table)->where('id', $id)->get()->getRowArray() !== $before[$table]) {
            throw new RuntimeException('New-owned parent unexpectedly changed: ' . $table);
        }
    }
    $invoiceId = $results['create']['result']['invoice_id'];
    $invoice = $db->table('finances')->where('id', $invoiceId)->get()->getRowArray();
    if (!$invoice || (int) $invoice['clinic_id'] !== $clinicId || (int) $invoice['patient_id'] !== $patientId
        || (int) $invoice['examination_id'] !== $examinationId || $invoice['transaction_id'] !== $run
        || (float) $invoice['total_amount'] !== 100.0 || (float) $invoice['balance_due'] !== 100.0) {
        throw new RuntimeException('Committed model invoice or parent identity differs.');
    }
    echo json_encode(['status' => 'PASS', 'run' => $run, 'synthetic_only' => true,
        'real_model_callback_locked_before_insert' => true, 'delete_waited_for_creator_commit' => true,
        'postcommit_history_rejected' => true, 'parents_preserved_exactly' => true,
        'retained_new_fixture_ids' => $fixture + ['invoice_id' => $invoiceId], 'workers' => $results], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
} finally {
    if (!is_file($manifest . '.release')) { file_put_contents($manifest . '.release', 'release creator', LOCK_EX); }
    foreach ($processes as $process) { proc_terminate($process); proc_close($process); }
}
