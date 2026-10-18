<?php
declare(strict_types=1);

/**
 * Guarded Standalone Real-MariaDB Synthetic Test Harness for PR F2
 * Safe Bulk Finance Settlement (FIN-08) & Server Amount Guards (FIN-09/10).
 *
 * CRITICAL SAFETY RULES:
 * 1. Requires explicit injected bootstrap via CLI: --bootstrap=/path/to/bootstrap.php
 * 2. Absolutely no .env reading, no default database guessing.
 * 3. Enforces strict environment assertions:
 *    - Database name: exact u556956644_dentstage1017
 *    - Database user: exact u556956644_dstage1017
 *    - CLI private root: exact private remediation baseline
 *    - Marker GUID: exact b60809432ec1d915b0b975aef8484e43
 * 4. Zero DROP, TRUNCATE, or table-wide DELETE operations.
 * 5. Inserts only clearly marked synthetic fixtures with unique test run prefix.
 * 6. Actual production FinanceSettlementService and Finance::markAsPaid are invoked.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Error: CLI execution only.\n");
    exit(1);
}

// Parse CLI options
$rawOptions = getopt('', [
    'bootstrap:',
    'mode::',
    'worker-id::',
    'test-type::',
    'barrier-file::',
    'target-ids::',
    'result-file::',
]);

$bootstrapPath = $rawOptions['bootstrap'] ?? null;
if (!$bootstrapPath || !is_string($bootstrapPath) || !file_exists($bootstrapPath)) {
    fwrite(STDERR, "====================================================================\n");
    fwrite(STDERR, "PR F2 GUARDED MARIADB TEST HARNESS: EXPLICIT BOOTSTRAP REQUIRED\n");
    fwrite(STDERR, "====================================================================\n");
    fwrite(STDERR, "Usage: php scripts/verify_bulk_settlement_mariadb_harness.php --bootstrap=/path/to/stage_bootstrap.php\n");
    fwrite(STDERR, "Safety policy prohibits default DB connections or reading local .env.\n");
    fwrite(STDERR, "Root must provide the private synthetic stage bootstrap file.\n");
    exit(1);
}

// Include injected private bootstrap
require_once $bootstrapPath;

use CodeIgniter\Database\BaseConnection;
use App\Services\FinanceSettlementService;
use App\Services\FinanceTreasuryService;

// Verify $db connection injected by bootstrap
if (!isset($db) || !($db instanceof BaseConnection)) {
    fwrite(STDERR, "Assertion Error: Injected bootstrap must provide CodeIgniter BaseConnection \$db.\n");
    exit(1);
}

// Security Assertion 1: Database identity exact u556956644_dentstage1017
$dbName = (string) $db->database;
if ($dbName !== 'u556956644_dentstage1017') {
    fwrite(STDERR, "Security Assertion Failed: Expected DB u556956644_dentstage1017, got '{$dbName}'. Execution aborted.\n");
    exit(1);
}

// Security Assertion 2: Database user exact u556956644_dstage1017
$dbUser = (string) $db->username;
if ($dbUser !== 'u556956644_dstage1017') {
    fwrite(STDERR, "Security Assertion Failed: Expected DB user u556956644_dstage1017, got '{$dbUser}'. Execution aborted.\n");
    exit(1);
}

// Security Assertion 3: exact canonical private root, outside all web roots.
$injectedRoot = isset($root) ? (string) $root : '';
$expectedRoot = '/home/u556956644/.denta-stage-20261017/remediation-20261018-base97';
$canonicalRoot = realpath($injectedRoot);
if ($canonicalRoot !== $expectedRoot || $injectedRoot !== $expectedRoot || is_link($injectedRoot)
    || !defined('ROOTPATH') || realpath(ROOTPATH) !== $expectedRoot) {
    fwrite(STDERR, "Security Assertion Failed: Exact private source root required. Execution aborted.\n");
    exit(1);
}

// Security Assertion 4: Marker GUID exact b60809432ec1d915b0b975aef8484e43
$injectedGuid = isset($c['guid']) ? (string) $c['guid'] : (isset($c['marker_guid']) ? (string) $c['marker_guid'] : (isset($guid) ? (string) $guid : (isset($marker_guid) ? (string) $marker_guid : '')));
if ($injectedGuid !== 'b60809432ec1d915b0b975aef8484e43') {
    fwrite(STDERR, "Security Assertion Failed: Injected marker GUID mismatch. Execution aborted.\n");
    exit(1);
}

// Security Assertion 5: Stage DB Identity Table Assertion
$identityTable = '_denta_stage_identity';
try {
    $identityRow = $db->query("SELECT guid, domain, synthetic_only FROM {$identityTable} WHERE id = 1")->getRowArray();
    $dbGuid = $identityRow['guid'] ?? null;
    if ($dbGuid !== 'b60809432ec1d915b0b975aef8484e43'
        || ($identityRow['domain'] ?? '') !== 'dentastaging.palsync.net'
        || (int) ($identityRow['synthetic_only'] ?? 0) !== 1) {
        fwrite(STDERR, "Security Assertion Failed: Stage DB table '{$identityTable}' GUID mismatch ('{$dbGuid}'). Execution aborted.\n");
        exit(1);
    }
} catch (\Throwable $e) {
    fwrite(STDERR, "Security Assertion Failed: Could not verify '{$identityTable}' in database: " . $e->getMessage() . "\n");
    exit(1);
}

echo "=== PR F2: MariaDB Synthetic Finance Settlement Harness ===\n";
echo "Verified DB: {$dbName} as {$dbUser}\n";
echo "Verified Root: {$canonicalRoot}\n";
echo "Verified Marker GUID: {$injectedGuid}\n";

// Audit failure injection seam for rollback testing
class AuditFailingFinanceSettlementService extends FinanceSettlementService
{
    protected function audit(int $clinicId, int $actorId, string $action, int $financeId, string $description, array $metadata): void
    {
        throw new \RuntimeException('Simulated audit persistence failure for rollback verification.');
    }
}

$mode = $rawOptions['mode'] ?? 'parent';
$treasury = new FinanceTreasuryService();

// Worker mode execution
if ($mode === 'worker') {
    $workerId = (int) ($rawOptions['worker-id'] ?? 1);
    $barrierFile = (string) ($rawOptions['barrier-file'] ?? '');
    $targetIdsRaw = (string) ($rawOptions['target-ids'] ?? '');
    $testType = (string) ($rawOptions['test-type'] ?? 'bulk');
    $resultFile = (string) ($rawOptions['result-file'] ?? '');

    $targetIds = array_map('intval', explode(',', $targetIdsRaw));

    // Barrier wait: wait until barrier file is released with 10s bounded timeout
    if ($barrierFile !== '' && file_exists($barrierFile)) {
        $barrierStart = microtime(true);
        while (file_exists($barrierFile)) {
            if ((microtime(true) - $barrierStart) > 10.0) {
                throw new \RuntimeException('Worker timed out waiting for barrier release after 10s.');
            }
            usleep(10000); // 10ms poll
        }
    }

    $resultData = ['worker' => $workerId, 'success' => false, 'error' => null, 'settled' => 0, 'skipped' => 0];
    try {
        if ($testType === 'individual') {
            // Instantiate App\Controllers\Finance via Reflection without constructor
            $refClass = new \ReflectionClass(\App\Controllers\Finance::class);
            $controller = $refClass->newInstanceWithoutConstructor();

            // Set session active_clinic_id and user_id for tenant context
            $session = \Config\Services::session();
            $session->set([
                'active_clinic_id' => 1,
                'user_id' => 1,
            ]);

            helper(['security', 'url']);

            $financeModel = new \App\Models\FinanceModel($db);
            $settlementService = new FinanceSettlementService($db, $treasury);
            $response = \Config\Services::response();

            $setProp = function (object $obj, string $prop, $val): void {
                $ref = new \ReflectionProperty($obj, $prop);
                $ref->setAccessible(true);
                $ref->setValue($obj, $val);
            };

            $setProp($controller, 'db', $db);
            $setProp($controller, 'financeModel', $financeModel);
            $setProp($controller, 'financeSettlementService', $settlementService);
            $setProp($controller, 'response', $response);

            $settledCount = 0;
            $skippedCount = 0;
            foreach ($targetIds as $tid) {
                if ($tid <= 0) {
                    continue;
                }
                $resp = $controller->markAsPaid($tid);
                $rawBody = (string) $resp->getBody();
                $body = json_decode($rawBody, true);
                if (!empty($body['success'])) {
                    if (str_contains($body['message'] ?? '', 'already fully paid')) {
                        $skippedCount++;
                    } else {
                        $settledCount++;
                    }
                } else {
                    throw new \RuntimeException($body['message'] ?? 'markAsPaid returned failure');
                }
            }
            $resultData['settled'] = $settledCount;
            $resultData['skipped'] = $skippedCount;
        } else {
            $service = new FinanceSettlementService($db, $treasury);
            $res = $service->bulkSettle(1, $targetIds, 1);
            $resultData['settled'] = $res['settled_count'];
            $resultData['skipped'] = $res['skipped_count'];
        }
        $resultData['success'] = true;
    } catch (\Throwable $e) {
        $resultData['error'] = $e->getMessage();
    }

    if ($resultFile !== '') {
        file_put_contents($resultFile, json_encode($resultData));
    }
    exit($resultData['success'] ? 0 : 1);
}

// Parent mode execution
$clinicA = 1;
$clinicB = 2;
$actorId = 1;
$runPrefix = 'STG_F2_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));

echo "Test run prefix: {$runPrefix}\n";

// Resolve STG_ patient ownership in Stage Clinic 1
$patientRow = $db->query("SELECT id FROM patients WHERE clinic_id = ? AND LEFT(patient_id, 4) = 'STG_' ORDER BY id LIMIT 1", [$clinicA])->getRowArray();
if (!$patientRow) {
    throw new \RuntimeException('Explicit synthetic patient fixture missing; refusing patient fallback.');
}
$patientId = (int) $patientRow['id'];
echo "Using patient ID: {$patientId} for clinic {$clinicA}\n\n";

function insertFixture(BaseConnection $db, array $data): int
{
    // Production transaction_id is VARCHAR(20); retain the run token in description.
    $data['transaction_id'] = 'F2_' . substr(hash('sha256', (string) $data['transaction_id']), 0, 17);
    if (!$db->table('finances')->insert($data) || $db->affectedRows() !== 1) {
        throw new \RuntimeException('Unable to insert owned synthetic finance fixture.');
    }
    return (int) $db->insertID();
}

$passedTests = 0;
$totalTests = 0;

function assertCondition(bool $condition, string $description): void
{
    global $passedTests, $totalTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo "  [PASS] {$description}\n";
    } else {
        echo "  [FAIL] {$description}\n";
        throw new \RuntimeException("Test failure: {$description}");
    }
}

// -----------------------------------------------------------------------------
// Case 1: Standard bulk settlement of Invoice & Expense with trusted treasury defaults
// -----------------------------------------------------------------------------
echo "[Case 1] Standard Invoice + Expense settlement with treasury defaults\n";
$invId = insertFixture($db, [
    'clinic_id' => $clinicA,
    'patient_id' => $patientId,
    'transaction_id' => $runPrefix . '_INV_1',
    'transaction_type' => 'invoice',
    'amount' => 100.00,
    'amount_original' => 100.00,
    'exchange_rate' => 1.0,
    'currency' => 'ILS',
    'total_amount' => 100.00,
    'discount_amount' => 0.00,
    'tax_percentage' => 0.00,
    'tax_amount' => 0.00,
    'amount_paid' => 0.00,
    'balance_due' => 100.00,
    'payment_method' => 'cash',
    'payment_status' => 'pending',
    'description' => "{$runPrefix} Invoice 1",
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

$expId = insertFixture($db, [
    'clinic_id' => $clinicA,
    'patient_id' => null,
    'transaction_id' => $runPrefix . '_EXP_1',
    'transaction_type' => 'expense',
    'amount' => 50.00,
    'amount_original' => 50.00,
    'exchange_rate' => 1.0,
    'currency' => 'ILS',
    'total_amount' => 50.00,
    'discount_amount' => 0.00,
    'tax_percentage' => 0.00,
    'tax_amount' => 0.00,
    'amount_paid' => 0.00,
    'balance_due' => 50.00,
    'payment_method' => 'card',
    'payment_status' => 'pending',
    'expense_category' => 'supplies',
    'counterparty_name' => 'Supplier X',
    'description' => "{$runPrefix} Expense 1",
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

$service = new FinanceSettlementService($db, $treasury);
$res = $service->bulkSettle($clinicA, [$invId, $expId], $actorId);

assertCondition($res['settled_count'] === 2 && $res['skipped_count'] === 0, 'Both documents reported settled');

// Verify invoice ledger row
$invPayment = $db->query("SELECT * FROM finance_payments WHERE clinic_id = ? AND finance_id = ?", [$clinicA, $invId])->getRowArray();
assertCondition($invPayment !== null, 'Invoice payment ledger row inserted');
assertCondition((float) $invPayment['amount'] === 100.00, 'Invoice payment amount is 100.00');
assertCondition($invPayment['liquidity_account'] === 'safe_cash', 'Cash invoice defaulted to safe_cash');

// Verify expense ledger row
$expPayment = $db->query("SELECT * FROM finance_payments WHERE clinic_id = ? AND finance_id = ?", [$clinicA, $expId])->getRowArray();
assertCondition($expPayment !== null, 'Expense payment ledger row inserted');
assertCondition((float) $expPayment['amount'] === 50.00, 'Expense payment amount is 50.00');
assertCondition($expPayment['liquidity_account'] === 'bank', 'Card expense defaulted to bank');

// Verify parent statuses
$invRow = $db->query("SELECT * FROM finances WHERE clinic_id = ? AND id = ?", [$clinicA, $invId])->getRowArray();
assertCondition((float) $invRow['amount_paid'] === 100.00 && (float) $invRow['balance_due'] === 0.00 && $invRow['payment_status'] === 'paid', 'Invoice marked paid with 0 balance');

$expRow = $db->query("SELECT * FROM finances WHERE clinic_id = ? AND id = ?", [$clinicA, $expId])->getRowArray();
assertCondition((float) $expRow['amount_paid'] === 50.00 && (float) $expRow['balance_due'] === 0.00 && $expRow['payment_status'] === 'paid', 'Expense marked paid with 0 balance');

// -----------------------------------------------------------------------------
// Case 2: Exact Repeat Idempotency
// -----------------------------------------------------------------------------
echo "\n[Case 2] Exact Repeat Idempotency\n";
$repeatRes = $service->bulkSettle($clinicA, [$invId, $expId], $actorId);
assertCondition($repeatRes['settled_count'] === 0 && $repeatRes['skipped_count'] === 2, 'Repeated bulkSettle idempotently skips settled rows');

$paymentCount = (int) $db->query("SELECT COUNT(*) AS c FROM finance_payments WHERE clinic_id = ? AND finance_id IN (?, ?)", [$clinicA, $invId, $expId])->getRow()->c;
assertCondition($paymentCount === 2, 'No duplicate payments recorded on repeat');

// -----------------------------------------------------------------------------
// Case 3: Mixed Foreign Clinic / Missing Selection Atomicity
// -----------------------------------------------------------------------------
echo "\n[Case 3] Mixed Foreign Clinic / Missing Selection Atomicity\n";
$validRowId = insertFixture($db, [
    'clinic_id' => $clinicA,
    'patient_id' => $patientId,
    'transaction_id' => $runPrefix . '_MIXED_VALID',
    'transaction_type' => 'invoice',
    'amount' => 75.00,
    'total_amount' => 75.00,
    'amount_paid' => 0.00,
    'balance_due' => 75.00,
    'payment_status' => 'pending',
    'description' => "{$runPrefix} Mixed Valid",
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

$foreignRowId = insertFixture($db, [
    'clinic_id' => $clinicB,
    'patient_id' => null,
    'transaction_id' => $runPrefix . '_FOREIGN',
    'transaction_type' => 'invoice',
    'amount' => 75.00,
    'total_amount' => 75.00,
    'amount_paid' => 0.00,
    'balance_due' => 75.00,
    'payment_status' => 'pending',
    'description' => "{$runPrefix} Foreign",
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

$rejectedForeign = false;
try {
    $service->bulkSettle($clinicA, [$validRowId, $foreignRowId], $actorId);
} catch (\DomainException $e) {
    $rejectedForeign = str_contains($e->getMessage(), 'not found');
}
assertCondition($rejectedForeign, 'Foreign clinic transaction selection rejected atomically');

$validAfter = $db->query("SELECT * FROM finances WHERE clinic_id = ? AND id = ?", [$clinicA, $validRowId])->getRowArray();
assertCondition((float) $validAfter['amount_paid'] === 0.00 && $validAfter['payment_status'] === 'pending', 'Valid row remains untouched after atomic rollback');

// -----------------------------------------------------------------------------
// Case 4: Injection Audit Failure Rollback via Subclass Seam
// -----------------------------------------------------------------------------
echo "\n[Case 4] Injection Audit Failure Rollback via Subclass Seam\n";
$auditFailRowId = insertFixture($db, [
    'clinic_id' => $clinicA,
    'patient_id' => $patientId,
    'transaction_id' => $runPrefix . '_AUDIT_FAIL',
    'transaction_type' => 'invoice',
    'amount' => 80.00,
    'total_amount' => 80.00,
    'amount_paid' => 0.00,
    'balance_due' => 80.00,
    'payment_status' => 'pending',
    'description' => "{$runPrefix} Audit Fail",
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

$auditFailingService = new AuditFailingFinanceSettlementService($db, $treasury);
$auditFailed = false;
try {
    $auditFailingService->bulkSettle($clinicA, [$auditFailRowId], $actorId);
} catch (\RuntimeException $e) {
    $auditFailed = str_contains($e->getMessage(), 'Simulated audit persistence failure');
}
assertCondition($auditFailed, 'Audit failure caught as expected');

$failPayment = $db->query("SELECT * FROM finance_payments WHERE clinic_id = ? AND finance_id = ?", [$clinicA, $auditFailRowId])->getRowArray();
assertCondition($failPayment === null, 'Payment insertion rolled back completely upon audit failure');

$failParent = $db->query("SELECT * FROM finances WHERE clinic_id = ? AND id = ?", [$clinicA, $auditFailRowId])->getRowArray();
assertCondition((float) $failParent['amount_paid'] === 0.00 && $failParent['payment_status'] === 'pending', 'Parent status untouched upon audit failure');

// -----------------------------------------------------------------------------
// Case 5: Legacy Full Payment Compatibility (No Ledger Entries)
// -----------------------------------------------------------------------------
echo "\n[Case 5] Legacy Full Payment Compatibility (No Ledger Entries)\n";
$legacyFullId = insertFixture($db, [
    'clinic_id' => $clinicA,
    'patient_id' => $patientId,
    'transaction_id' => $runPrefix . '_LEGACY_FULL',
    'transaction_type' => 'invoice',
    'amount' => 120.00,
    'total_amount' => 120.00,
    'amount_paid' => 120.00,
    'balance_due' => 0.00,
    'payment_status' => 'paid',
    'description' => "{$runPrefix} Legacy Full",
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

$legacyRes = $service->bulkSettle($clinicA, [$legacyFullId], $actorId);
assertCondition($legacyRes['settled_count'] === 0 && $legacyRes['skipped_count'] === 1, 'Legacy fully paid invoice idempotently skips without duplicate payment');

$legacyPayments = (int) $db->query("SELECT COUNT(*) AS c FROM finance_payments WHERE clinic_id = ? AND finance_id = ?", [$clinicA, $legacyFullId])->getRow()->c;
assertCondition($legacyPayments === 0, 'Zero payments inserted for legacy full payment');

// -----------------------------------------------------------------------------
// Case 6: Legacy Ambiguous Partial Payment Rejection (No Ledger Entries)
// -----------------------------------------------------------------------------
echo "\n[Case 6] Legacy Ambiguous Partial Payment Rejection\n";
$legacyPartialId = insertFixture($db, [
    'clinic_id' => $clinicA,
    'patient_id' => $patientId,
    'transaction_id' => $runPrefix . '_LEGACY_PARTIAL',
    'transaction_type' => 'invoice',
    'amount' => 100.00,
    'total_amount' => 100.00,
    'amount_paid' => 40.00,
    'balance_due' => 60.00,
    'payment_status' => 'partial',
    'description' => "{$runPrefix} Legacy Partial",
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

$rejectedAmbiguous = false;
try {
    $service->bulkSettle($clinicA, [$legacyPartialId], $actorId);
} catch (\DomainException $e) {
    $rejectedAmbiguous = str_contains($e->getMessage(), 'unledgered legacy payment history requiring manual reconciliation');
}
assertCondition($rejectedAmbiguous, 'Unledgered partial legacy record rejected with clear reconciliation error');

// -----------------------------------------------------------------------------
// Case 7: Signed Ledger Precedence After Prior Refund
// -----------------------------------------------------------------------------
echo "\n[Case 7] Signed Ledger Precedence After Prior Refund\n";
$refundDocId = insertFixture($db, [
    'clinic_id' => $clinicA,
    'patient_id' => $patientId,
    'transaction_id' => $runPrefix . '_REFUNDED',
    'transaction_type' => 'invoice',
    'amount' => 100.00,
    'total_amount' => 100.00,
    'amount_paid' => 100.00, // Stale cached value
    'balance_due' => 0.00,
    'payment_status' => 'paid', // Stale cached status
    'description' => "{$runPrefix} Refunded Doc",
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

// Prior ledger: payment 100.00, refund 40.00 -> net ledger = 60.00, remaining = 40.00
$db->table('finance_payments')->insert([
    'clinic_id' => $clinicA,
    'finance_id' => $refundDocId,
    'amount' => 100.00,
    'type' => 'payment',
    'payment_method' => 'cash',
    'liquidity_account' => 'safe_cash',
    'currency' => 'ILS',
    'amount_original' => 100.00,
    'exchange_rate' => 1.0,
    'payment_date' => date('Y-m-d'),
    'created_by' => $actorId,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

$db->table('finance_payments')->insert([
    'clinic_id' => $clinicA,
    'finance_id' => $refundDocId,
    'amount' => 40.00,
    'type' => 'refund',
    'payment_method' => 'cash',
    'liquidity_account' => 'safe_cash',
    'currency' => 'ILS',
    'amount_original' => 40.00,
    'exchange_rate' => 1.0,
    'payment_date' => date('Y-m-d'),
    'created_by' => $actorId,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

// Ledger is authoritative: remaining is 40.00; cached status 'paid' must NOT cause skip!
$refundSettleRes = $service->bulkSettle($clinicA, [$refundDocId], $actorId);
assertCondition($refundSettleRes['settled_count'] === 1 && $refundSettleRes['skipped_count'] === 0, 'Settlement correctly settled remaining 40.00 balance rather than skipping');

$finalRefundDoc = $db->query("SELECT * FROM finances WHERE clinic_id = ? AND id = ?", [$clinicA, $refundDocId])->getRowArray();
assertCondition((float) $finalRefundDoc['amount_paid'] === 100.00 && (float) $finalRefundDoc['balance_due'] === 0.00 && $finalRefundDoc['payment_status'] === 'paid', 'Doc is now fully settled');

// -----------------------------------------------------------------------------
// Case 8: Concurrency Coordination (Overlapping Bulk vs Bulk)
// -----------------------------------------------------------------------------
echo "\n[Case 8] Concurrency Coordination (Overlapping Bulk vs Bulk)\n";
$cRow1 = insertFixture($db, [
    'clinic_id' => $clinicA,
    'patient_id' => $patientId,
    'transaction_id' => $runPrefix . '_CONCUR_1',
    'transaction_type' => 'invoice',
    'amount' => 100.00,
    'total_amount' => 100.00,
    'amount_paid' => 0.00,
    'balance_due' => 100.00,
    'payment_status' => 'pending',
    'description' => "{$runPrefix} Concur 1",
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

$cRow2 = insertFixture($db, [
    'clinic_id' => $clinicA,
    'patient_id' => $patientId,
    'transaction_id' => $runPrefix . '_CONCUR_2',
    'transaction_type' => 'invoice',
    'amount' => 200.00,
    'total_amount' => 200.00,
    'amount_paid' => 0.00,
    'balance_due' => 200.00,
    'payment_status' => 'pending',
    'description' => "{$runPrefix} Concur 2",
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

// Coordinate two workers with reversed ID order to verify ascending parent lock order serializes cleanly
$barrierFile = sys_get_temp_dir() . '/f2_barrier_' . uniqid() . '.lock';
$resultFile1 = sys_get_temp_dir() . '/f2_res_1_' . uniqid() . '.json';
$resultFile2 = sys_get_temp_dir() . '/f2_res_2_' . uniqid() . '.json';

file_put_contents($barrierFile, 'lock');

$selfScript = __FILE__;
$cmd1 = escapeshellcmd(PHP_BINARY) . " " . escapeshellarg($selfScript)
    . " --bootstrap=" . escapeshellarg($bootstrapPath)
    . " --mode=worker --worker-id=1 --test-type=bulk --barrier-file=" . escapeshellarg($barrierFile)
    . " --target-ids={$cRow1},{$cRow2} --result-file=" . escapeshellarg($resultFile1);

$cmd2 = escapeshellcmd(PHP_BINARY) . " " . escapeshellarg($selfScript)
    . " --bootstrap=" . escapeshellarg($bootstrapPath)
    . " --mode=worker --worker-id=2 --test-type=bulk --barrier-file=" . escapeshellarg($barrierFile)
    . " --target-ids={$cRow2},{$cRow1} --result-file=" . escapeshellarg($resultFile2);

$p1 = proc_open($cmd1, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes1);
$p2 = proc_open($cmd2, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes2);

// Release barrier so workers start simultaneously
usleep(50000); // 50ms to let both processes start and enter barrier wait
@unlink($barrierFile);

$out1 = stream_get_contents($pipes1[1]);
$err1 = stream_get_contents($pipes1[2]);
fclose($pipes1[0]); fclose($pipes1[1]); fclose($pipes1[2]);
$status1 = proc_close($p1);

$out2 = stream_get_contents($pipes2[1]);
$err2 = stream_get_contents($pipes2[2]);
fclose($pipes2[0]); fclose($pipes2[1]); fclose($pipes2[2]);
$status2 = proc_close($p2);

$res1 = file_exists($resultFile1) ? json_decode((string) file_get_contents($resultFile1), true) : null;
$res2 = file_exists($resultFile2) ? json_decode((string) file_get_contents($resultFile2), true) : null;
@unlink($resultFile1);
@unlink($resultFile2);

assertCondition($status1 === 0 && $status2 === 0 && ($res1['success'] ?? false) && ($res2['success'] ?? false), 'Both concurrent workers exited cleanly (no deadlocks)');
$totalSettledConcur = ($res1['settled'] ?? 0) + ($res2['settled'] ?? 0);
assertCondition($totalSettledConcur === 2, "Concurrent workers settled exactly 2 items in total (got {$totalSettledConcur}) without duplicate payment");

$pCountRow1 = (int) $db->query("SELECT COUNT(*) AS c FROM finance_payments WHERE clinic_id = ? AND finance_id = ?", [$clinicA, $cRow1])->getRow()->c;
$pCountRow2 = (int) $db->query("SELECT COUNT(*) AS c FROM finance_payments WHERE clinic_id = ? AND finance_id = ?", [$clinicA, $cRow2])->getRow()->c;
assertCondition($pCountRow1 === 1 && $pCountRow2 === 1, 'Exactly 1 ledger settlement entry per row recorded');

// -----------------------------------------------------------------------------
// Case 9: Concurrency Coordination (Bulk Worker vs Controller markAsPaid Worker)
// -----------------------------------------------------------------------------
echo "\n[Case 9] Concurrency Coordination (Bulk Worker vs Controller markAsPaid Worker)\n";
$cRow3 = insertFixture($db, [
    'clinic_id' => $clinicA,
    'patient_id' => $patientId,
    'transaction_id' => $runPrefix . '_CONCUR_3',
    'transaction_type' => 'invoice',
    'amount' => 150.00,
    'total_amount' => 150.00,
    'amount_paid' => 0.00,
    'balance_due' => 150.00,
    'payment_status' => 'pending',
    'description' => "{$runPrefix} Concur 3",
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

$cRow4 = insertFixture($db, [
    'clinic_id' => $clinicA,
    'patient_id' => null,
    'transaction_id' => $runPrefix . '_CONCUR_4',
    'transaction_type' => 'expense',
    'amount' => 60.00,
    'total_amount' => 60.00,
    'amount_paid' => 0.00,
    'balance_due' => 60.00,
    'payment_method' => 'card',
    'payment_status' => 'pending',
    'expense_category' => 'supplies',
    'counterparty_name' => 'Supplier Race',
    'description' => "{$runPrefix} Concur 4",
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

$barrierFile2 = sys_get_temp_dir() . '/f2_barrier2_' . uniqid() . '.lock';
$resultFile3 = sys_get_temp_dir() . '/f2_res_3_' . uniqid() . '.json';
$resultFile4 = sys_get_temp_dir() . '/f2_res_4_' . uniqid() . '.json';

file_put_contents($barrierFile2, 'lock');

$cmd3 = escapeshellcmd(PHP_BINARY) . " " . escapeshellarg($selfScript)
    . " --bootstrap=" . escapeshellarg($bootstrapPath)
    . " --mode=worker --worker-id=3 --test-type=bulk --barrier-file=" . escapeshellarg($barrierFile2)
    . " --target-ids={$cRow3},{$cRow4} --result-file=" . escapeshellarg($resultFile3);

$cmd4 = escapeshellcmd(PHP_BINARY) . " " . escapeshellarg($selfScript)
    . " --bootstrap=" . escapeshellarg($bootstrapPath)
    . " --mode=worker --worker-id=4 --test-type=individual --barrier-file=" . escapeshellarg($barrierFile2)
    . " --target-ids={$cRow4},{$cRow3} --result-file=" . escapeshellarg($resultFile4);

$p3 = proc_open($cmd3, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes3);
$p4 = proc_open($cmd4, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes4);

usleep(50000); // 50ms to let both processes start and enter barrier wait
@unlink($barrierFile2);

$out3 = stream_get_contents($pipes3[1]);
$err3 = stream_get_contents($pipes3[2]);
fclose($pipes3[0]); fclose($pipes3[1]); fclose($pipes3[2]);
$status3 = proc_close($p3);

$out4 = stream_get_contents($pipes4[1]);
$err4 = stream_get_contents($pipes4[2]);
fclose($pipes4[0]); fclose($pipes4[1]); fclose($pipes4[2]);
$status4 = proc_close($p4);

$res3 = file_exists($resultFile3) ? json_decode((string) file_get_contents($resultFile3), true) : null;
$res4 = file_exists($resultFile4) ? json_decode((string) file_get_contents($resultFile4), true) : null;
@unlink($resultFile3);
@unlink($resultFile4);

assertCondition($status3 === 0 && $status4 === 0 && ($res3['success'] ?? false) && ($res4['success'] ?? false), 'Both concurrent bulk vs markAsPaid workers exited cleanly (no deadlocks)');
$totalSettledConcur2 = ($res3['settled'] ?? 0) + ($res4['settled'] ?? 0);
assertCondition($totalSettledConcur2 === 2, "Concurrent bulk vs individual workers settled exactly 2 items in total (got {$totalSettledConcur2}) without duplicate payment");

$pCountRow3 = (int) $db->query("SELECT COUNT(*) AS c FROM finance_payments WHERE clinic_id = ? AND finance_id = ?", [$clinicA, $cRow3])->getRow()->c;
$pCountRow4 = (int) $db->query("SELECT COUNT(*) AS c FROM finance_payments WHERE clinic_id = ? AND finance_id = ?", [$clinicA, $cRow4])->getRow()->c;
assertCondition($pCountRow3 === 1 && $pCountRow4 === 1, 'Exactly 1 ledger settlement entry per row recorded across bulk & markAsPaid');

echo "\n=======================================================\n";
echo "SUMMARY: All {$totalTests} synthetic tests passed successfully!\n";
echo "=======================================================\n";
