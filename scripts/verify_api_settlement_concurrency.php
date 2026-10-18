<?php
declare(strict_types=1);
/** Reviewer-owned real-SQL settlement versus API rewrite race. Synthetic staging only. */
if (PHP_SAPI !== 'cli') exit(2);
$bootstrap = $argv[1] ?? '';
$privateExpected = '/home/u556956644/.denta-stage-20261017';
if (!is_string($bootstrap) || !str_starts_with($bootstrap, $privateExpected . '/')
    || !is_file($bootstrap) || is_link($bootstrap) || realpath($bootstrap) !== $bootstrap) {
    throw new RuntimeException('Canonical guarded private bootstrap required.');
}
require $bootstrap;
if ($private !== $privateExpected || realpath($private) !== $private
    || !defined('HOMEPATH') || !str_starts_with((string) realpath(HOMEPATH), $private . '/remediation-20261018-')
    || $db->database !== 'u556956644_dentstage1017'
    || $db->username !== 'u556956644_dstage1017' || $db->hostname !== '127.0.0.1'
    || (int) $db->port !== 3306 || $db->getPrefix() !== '' || (int) $db->transDepth !== 0) {
    throw new RuntimeException('Private synthetic connection rejected.');
}
$identity = $db->query('SELECT guid,domain,synthetic_only FROM _denta_stage_identity WHERE id=1')->getRowArray();
if (($identity['guid'] ?? '') !== 'b60809432ec1d915b0b975aef8484e43'
    || ($identity['domain'] ?? '') !== 'dentastaging.palsync.net' || (int) ($identity['synthetic_only'] ?? 0) !== 1) {
    throw new RuntimeException('Synthetic marker rejected.');
}
foreach ($db->query('SHOW GRANTS')->getResultArray() as $row) {
    $grant = str_replace('\\_', '_', (string) reset($row));
    if (str_contains($grant, 'WITH GRANT OPTION')
        || (str_contains($grant, ' ON *.* ') && !str_starts_with($grant, 'GRANT USAGE ON *.* '))
        || (preg_match('/ ON `([^`]+)`\./', $grant, $match) && $match[1] !== $db->database)) {
        throw new RuntimeException('Excessive synthetic grants.');
    }
}
$db->query('SET SESSION innodb_lock_wait_timeout=5');
session()->set(['active_clinic_id' => 1, 'user_id' => 1, 'global_mode' => false]);
\App\Libraries\ApiRuntimeContext::setClinicId(1);
\App\Libraries\ApiRuntimeContext::setUserId(1);
function apiRaceWait(string $path, int $seconds = 8): void {
    $end = microtime(true) + $seconds;
    do { clearstatcache(true, $path); if (is_file($path)) return; usleep(10000); } while (microtime(true) < $end);
    throw new RuntimeException('Race barrier timeout: ' . basename($path));
}
if (($argv[2] ?? '') === 'worker') {
    $path = $argv[3] ?? ''; $slot = $argv[4] ?? '';
    if (!is_string($path) || dirname($path) !== $private
        || !preg_match('/^api-settlement-review-race-A2C_[a-f0-9]{10}\.json$/D', basename($path))
        || !is_file($path) || is_link($path) || !in_array($slot, ['settle', 'api'], true)) {
        throw new RuntimeException('Race manifest rejected.');
    }
    $manifest = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    try {
        $fid = (int) ($manifest['finance_id'] ?? 0);
        if ($fid <= 0) throw new RuntimeException('Fixture ID rejected.');
        if ($slot === 'settle') {
            $service = new class($db, $path) extends \App\Services\FinanceSettlementService {
                public function __construct($db, private string $barrier) { parent::__construct($db); }
                public function lockFinance(int $clinicId, int $financeId): ?array {
                    $row = parent::lockFinance($clinicId, $financeId);
                    if (!$row) throw new RuntimeException('Expected parent missing.');
                    file_put_contents($this->barrier . '.parent-locked', 'locked', LOCK_EX);
                    apiRaceWait($this->barrier . '.release');
                    return $row;
                }
            };
            $result = $service->bulkSettle(1, [$fid], 1);
            if ($result !== ['settled_count' => 1, 'skipped_count' => 0]) throw new RuntimeException('Settlement failed.');
        } else {
            file_put_contents($path . '.api-entered', 'entering API update', LOCK_EX);
            $started = microtime(true); $rejected = false;
            try {
                (new \App\Services\FinanceApiMutationService($db, new \App\Models\FinanceModel($db)))
                    ->updateFinance(1, $fid, ['amount' => 200, 'amount_original' => 200], 1);
            } catch (\App\Services\FinanceConflictException $error) {
                $rejected = true; $reason = $error->getMessage();
            }
            $elapsedMs = round((microtime(true) - $started) * 1000, 2);
            if (!$rejected) throw new RuntimeException('API rewrote the settled monetary basis.');
            if ($elapsedMs < 200) throw new RuntimeException('API did not demonstrate waiting for the parent lock.');
            $result = ['rejected' => true, 'reason' => $reason, 'elapsed_ms' => $elapsedMs];
        }
        if ((int) $db->transDepth !== 0 || $db->transStatus() === false) throw new RuntimeException('Worker transaction leaked.');
        file_put_contents($path . '.' . $slot . '.json', json_encode(['status' => 'PASS', 'result' => $result], JSON_THROW_ON_ERROR), LOCK_EX);
    } catch (Throwable $error) {
        file_put_contents($path . '.' . $slot . '.json', json_encode(['status' => 'FAIL', 'message' => $error->getMessage()], JSON_THROW_ON_ERROR), LOCK_EX);
        exit(1);
    }
    exit(0);
}
$run = 'A2C_' . bin2hex(random_bytes(5));
$patient = $db->table('patients')->select('id')->where('clinic_id', 1)->like('patient_id', 'STG_', 'after')->get()->getRowArray();
if (!$patient) throw new RuntimeException('Synthetic patient missing.');
$attemptsBefore = (int) $db->table('notification_attempts')->countAllResults();
$invoice = [
    'clinic_id' => 1, 'patient_id' => (int) $patient['id'], 'transaction_id' => $run,
    'transaction_type' => 'invoice', 'amount' => 100, 'total_amount' => 100, 'amount_original' => 100,
    'currency' => 'ILS', 'exchange_rate' => 1, 'payment_method' => 'cash', 'liquidity_account' => 'safe_cash',
    'payment_status' => 'pending', 'description' => $run, 'service_type' => 'other',
    'discount_amount' => 0, 'tax_percentage' => 0, 'tax_amount' => 0, 'amount_paid' => 0, 'balance_due' => 100,
    'transaction_date' => '2097-07-02', 'due_date' => '2097-07-10', 'created_by' => 1,
    'created_at' => '2097-07-02 12:00:00', 'updated_at' => '2097-07-02 12:00:00',
];
if (!$db->table('finances')->insert($invoice)) throw new RuntimeException('Synthetic fixture insert failed.');
$fid = (int) $db->insertID();
$path = $private . '/api-settlement-review-race-' . $run . '.json';
if (file_exists($path)) throw new RuntimeException('Race manifest collision.');
file_put_contents($path, json_encode(['finance_id' => $fid], JSON_THROW_ON_ERROR), LOCK_EX); chmod($path, 0600);
$processes = [];
try {
    foreach (['settle', 'api'] as $slot) {
        $pipes = [];
        $process = proc_open([PHP_BINARY, __FILE__, $bootstrap, 'worker', $path, $slot],
            [0 => ['pipe', 'r'], 1 => ['file', $path . '.' . $slot . '.stdout', 'a'], 2 => ['file', $path . '.' . $slot . '.stderr', 'a']], $pipes, $root);
        if (!is_resource($process)) throw new RuntimeException('Worker launch failed.');
        fclose($pipes[0]); $processes[$slot] = $process;
        if ($slot === 'settle') apiRaceWait($path . '.parent-locked');
    }
    apiRaceWait($path . '.api-entered'); usleep(250000); clearstatcache();
    if (is_file($path . '.api.json')) throw new RuntimeException('API completed while settlement held the parent lock.');
    file_put_contents($path . '.release', 'release', LOCK_EX);
    $results = [];
    foreach ($processes as $slot => $process) {
        $code = proc_close($process); unset($processes[$slot]);
        if ($code !== 0 || !is_file($path . '.' . $slot . '.json')) throw new RuntimeException('Worker failed: ' . $slot);
        $results[$slot] = json_decode(file_get_contents($path . '.' . $slot . '.json'), true, 512, JSON_THROW_ON_ERROR);
        if ($results[$slot]['status'] !== 'PASS') throw new RuntimeException('Worker result failed: ' . $slot);
    }
    $parent = $db->table('finances')->where('clinic_id', 1)->where('id', $fid)->get()->getRowArray();
    foreach (['amount', 'total_amount', 'amount_original', 'amount_paid'] as $field) {
        if ((float) ($parent[$field] ?? -1) !== 100.0) throw new RuntimeException('Final monetary basis changed: ' . $field);
    }
    if ($parent['payment_status'] !== 'paid' || (float) $parent['balance_due'] !== 0.0 || $parent['description'] !== $run) {
        throw new RuntimeException('Settled parent state or original metadata changed.');
    }
    $payments = $db->table('finance_payments')->where('clinic_id', 1)->where('finance_id', $fid)->get()->getResultArray();
    if (count($payments) !== 1 || (float) $payments[0]['amount'] !== 100.0
        || $payments[0]['type'] !== 'payment' || $payments[0]['liquidity_account'] !== 'safe_cash') {
        throw new RuntimeException('Settlement ledger history changed.');
    }
    $audits = $db->table('activity_logs')->where('clinic_id', 1)->where('entity_type', 'finance')->where('entity_id', $fid)->get()->getResultArray();
    if (count($audits) !== 1 || $audits[0]['action'] !== 'payment') throw new RuntimeException('Settlement audit changed or rejected API was audited as successful.');
    $meta = json_decode($audits[0]['metadata'], true, 512, JSON_THROW_ON_ERROR);
    if ((float) ($meta['amount'] ?? -1) !== 100.0 || $meta['liquidity_account'] !== 'safe_cash') throw new RuntimeException('Settlement audit provenance changed.');
    if ((int) $db->table('notification_attempts')->countAllResults() !== $attemptsBefore) throw new RuntimeException('Outbound attempt unexpectedly created.');
    echo json_encode(['status' => 'PASS', 'synthetic_only' => true, 'run' => $run, 'retained_finance_id' => $fid,
        'api_waited_for_settlement_parent_lock' => true, 'postcommit_api_rejected' => true,
        'parent_100_paid' => true, 'ledger_count' => 1, 'audit_preserved' => true, 'no_outbound_attempts' => true,
        'workers' => $results], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
} finally {
    if (!is_file($path . '.release')) file_put_contents($path . '.release', 'release', LOCK_EX);
    foreach ($processes as $process) { proc_terminate($process); proc_close($process); }
}
