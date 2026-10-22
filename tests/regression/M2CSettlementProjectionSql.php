<?php
declare(strict_types=1);
$options = getopt('', ['bootstrap:']);
$bootstrap = $options['bootstrap'] ?? '';
if (PHP_SAPI !== 'cli' || !is_file($bootstrap)) { exit(2); }
require $bootstrap;
$identity = $db->query('SELECT DATABASE() db, @@port port, @@datadir datadir, CURRENT_USER() actor')->getRowArray();
if ($db->hostname !== '127.0.0.1' || $identity['db'] !== 'm2c_synthetic_acceptance' || (int) $identity['port'] !== 3307
    || !str_contains(str_replace('\\', '/', $identity['datadir']), 'denta-m2c-20261021/disposable-mariadb/data')
    || !str_starts_with($identity['actor'], 'm2c_test@')) { throw new RuntimeException('Disposable identity required.'); }
$before = $db->table('finances')->orderBy('id')->get()->getResultArray();
$corrections = new App\Services\InvoiceCorrectionService($db);
$settlements = new App\Services\FinanceSettlementService($db);
$n = 0; $invoices = 0;
if (!$db->transBegin()) { throw new RuntimeException('Transaction unavailable.'); }
try {
    foreach ($before as $row) {
        $clinic = (int) $row['clinic_id']; $id = (int) $row['id'];
        if ($row['transaction_type'] !== 'invoice' || !$corrections->hasEffects($clinic, $id)) { continue; }
        $locked = $settlements->lockFinance($clinic, $id);
        $expected = $corrections->position($clinic, $id, true);
        if (($locked['payment_status'] ?? '') === 'cancelled') {
            try {
                $settlements->getLockedSettlementState($clinic, $locked);
                throw new RuntimeException('Cancelled invoice accepted for settlement.');
            } catch (DomainException $e) { $n++; }
            continue;
        }
        $actual = $settlements->getLockedSettlementState($clinic, $locked);
        foreach (['remaining_base', 'ledger_settled', 'base_settled_before', 'patient_credit_base', 'refundable_base'] as $key) {
            if ($actual[$key] !== $expected[$key]) { throw new RuntimeException('Settlement adapter mismatch: ' . $key); }
            $n++;
        }
        $invoices++;
    }
} finally { $db->transRollback(); }
if ($invoices < 3) { throw new RuntimeException('Insufficient retained correction fixtures.'); }
if ($before !== $db->table('finances')->orderBy('id')->get()->getResultArray()) { throw new RuntimeException('Invoice history changed.'); }
$n++;
echo json_encode(['status' => 'PASS', 'checks' => $n, 'invoices' => $invoices, 'read_only' => true,
    'scope' => 'Actual locked settlement state adapter only; not full receipt/refund/treasury writer acceptance']), PHP_EOL;
