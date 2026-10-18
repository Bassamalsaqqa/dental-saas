<?php
declare(strict_types=1);

/**
 * Standalone Real-SQL Synthetic Test Script for PR #11:
 * Optional FX Schema Compatibility in FinanceSettlementService.
 *
 * SAFETY INVARIANTS:
 * 1. CLI execution only.
 * 2. Strict synthetic-DB identity validation BEFORE any DB writes:
 *    - Rejects production/business database ('democa_dental_saas') immediately.
 *    - Requires exact private root, database/user, host/port, marker GUID/domain, and restricted grants.
 * 3. Uses connection-local session-private TEMPORARY table clones with a random CI DBPrefix.
 * 4. Never drops, truncates, or modifies base tables; cleanup in finally targets checked
 *    random-prefixed temporary tables exclusively.
 * 5. Asserts full-FX and no-FX settlement:
 *    - Preserves account, amount, and type.
 *    - Exact insert payload and checked audit metadata.
 *    - Idempotent second call.
 *    - Cross-clinic rejection.
 *    - Rollback after forced audit failure.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Error: CLI execution only.\n");
    exit(1);
}

// 1. Resolve $db: either already defined by includer, or via --bootstrap CLI option
if (!isset($db) || !($db instanceof \CodeIgniter\Database\BaseConnection)) {
    $rawOptions = getopt('', ['bootstrap::']);
    $bootstrapPath = $rawOptions['bootstrap'] ?? null;
    if ($bootstrapPath && is_string($bootstrapPath) && file_exists($bootstrapPath)) {
        require_once $bootstrapPath;
    }
}

if (!isset($db) || !($db instanceof \CodeIgniter\Database\BaseConnection)) {
    fwrite(STDERR, "====================================================================\n");
    fwrite(STDERR, "OPTIONAL FX SCHEMA COMPATIBILITY TEST: EXPLICIT BOOTSTRAP REQUIRED\n");
    fwrite(STDERR, "====================================================================\n");
    fwrite(STDERR, "Usage: php scripts/verify_optional_fx_schema_compatibility.php --bootstrap=/path/to/bootstrap.php\n");
    fwrite(STDERR, "Or include from a harness that exposes BaseConnection \$db.\n");
    exit(1);
}

use CodeIgniter\Database\BaseConnection;
use App\Services\FinanceSettlementService;
use App\Services\FinanceTreasuryService;

// Fail closed before any DDL: this runner is tied to the independently guarded private staging copy.
$expectedRoot = '/home/u556956644/.denta-stage-20261017/remediation-20261018-base97';
if (!defined('HOMEPATH') || realpath(HOMEPATH) !== $expectedRoot
    || realpath($expectedRoot) !== $expectedRoot || is_link($expectedRoot)
    || $db->database !== 'u556956644_dentstage1017'
    || $db->username !== 'u556956644_dstage1017'
    || $db->hostname !== '127.0.0.1' || (int) $db->port !== 3306
    || $db->getPrefix() !== '' || (int) $db->transDepth !== 0) {
    throw new \RuntimeException('Unverified private synthetic staging connection or root.');
}
$identity = $db->query('SELECT guid, domain, synthetic_only FROM _denta_stage_identity WHERE id = 1')->getRowArray();
if (($identity['guid'] ?? '') !== 'b60809432ec1d915b0b975aef8484e43'
    || ($identity['domain'] ?? '') !== 'dentastaging.palsync.net'
    || (int) ($identity['synthetic_only'] ?? 0) !== 1) {
    throw new \RuntimeException('Synthetic stage marker mismatch.');
}
foreach ($db->query('SHOW GRANTS')->getResultArray() as $row) {
    $grant = str_replace('\\_', '_', (string) reset($row));
    if (str_contains($grant, 'WITH GRANT OPTION')
        || (str_contains($grant, ' ON *.* ') && !str_starts_with($grant, 'GRANT USAGE ON *.* '))
        || (preg_match('/ ON `([^`]+)`\./', $grant, $match) && $match[1] !== $db->database)) {
        throw new \RuntimeException('Excessive staging grants.');
    }
}
$dbName = (string) $db->database;
// Prime the real application settings while the original prefix is still active.
helper('settings');
session()->set(['active_clinic_id' => 1, 'user_id' => 1, 'global_mode' => false]);
if (settings()->get('currency', 'ILS') !== 'ILS') {
    throw new \RuntimeException('Fixture requires the established ILS synthetic base currency.');
}

echo "=== PR #11: Standalone Real-SQL Optional FX Compatibility Test ===\n";
echo "Verified Synthetic Target DB: {$dbName}\n";

// Audit failure injection subclass for transaction rollback verification
class AuditFailingFinanceSettlementService extends FinanceSettlementService
{
    protected function audit(int $clinicId, int $actorId, string $action, int $financeId, string $description, array $metadata): void
    {
        throw new \RuntimeException('Simulated audit persistence failure for rollback verification.');
    }
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
        throw new \RuntimeException("Assertion failed: {$description}");
    }
}

$originalPrefix = $db->getPrefix();
$randomSuffix = bin2hex(random_bytes(4));
$randomPrefix = 'tmp_fx_' . $randomSuffix . '_';

$baseFinances = $originalPrefix . 'finances';
$baseFinancePayments = $originalPrefix . 'finance_payments';
$baseActivityLogs = $originalPrefix . 'activity_logs';

$tempFinances = $randomPrefix . 'finances';
$tempFinancePayments = $randomPrefix . 'finance_payments';
$tempActivityLogs = $randomPrefix . 'activity_logs';

$treasury = new FinanceTreasuryService();

try {
    echo "\n[Step 1] Creating session-private temporary table clones with prefix '{$randomPrefix}'...\n";

    // 1. Clone finances
    $db->query("CREATE TEMPORARY TABLE `{$tempFinances}` LIKE `{$baseFinances}`");
    // Ensure liquidity_account exists on temporary finances clone
    if (!$db->fieldExists('liquidity_account', $tempFinances)) {
        $db->query("ALTER TABLE `{$tempFinances}` ADD COLUMN liquidity_account VARCHAR(40) NULL AFTER payment_method");
    }

    // 2. Clone finance_payments
    $db->query("CREATE TEMPORARY TABLE `{$tempFinancePayments}` LIKE `{$baseFinancePayments}`");
    // Ensure liquidity_account and FX fields exist on temporary payments clone for full-FX phase
    if (!$db->fieldExists('liquidity_account', $tempFinancePayments)) {
        $db->query("ALTER TABLE `{$tempFinancePayments}` ADD COLUMN liquidity_account VARCHAR(40) NULL AFTER payment_method");
    }
    if (!$db->fieldExists('currency', $tempFinancePayments)) {
        $db->query("ALTER TABLE `{$tempFinancePayments}` ADD COLUMN currency VARCHAR(10) NULL AFTER amount");
    }
    if (!$db->fieldExists('amount_original', $tempFinancePayments)) {
        $db->query("ALTER TABLE `{$tempFinancePayments}` ADD COLUMN amount_original DECIMAL(15,2) NULL AFTER currency");
    }
    if (!$db->fieldExists('exchange_rate', $tempFinancePayments)) {
        $db->query("ALTER TABLE `{$tempFinancePayments}` ADD COLUMN exchange_rate DECIMAL(15,6) NULL AFTER amount_original");
    }

    // 3. Clone activity_logs
    if ($db->tableExists($baseActivityLogs)) {
        $db->query("CREATE TEMPORARY TABLE `{$tempActivityLogs}` LIKE `{$baseActivityLogs}`");
    } else {
        $db->query("CREATE TEMPORARY TABLE `{$tempActivityLogs}` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            clinic_id INT NOT NULL,
            user_id INT NOT NULL,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(50) NOT NULL,
            entity_id INT NOT NULL,
            description TEXT NULL,
            metadata JSON NULL,
            created_at DATETIME NULL
        )");
    }

    // Switch connection prefix to session-temporary tables
    $db->setPrefix($randomPrefix);
    $db->resetDataCache();

    $service = new FinanceSettlementService($db, $treasury);
    $failingService = new AuditFailingFinanceSettlementService($db, $treasury);

    // =========================================================================
    // SCENARIO A: Full-FX Supported Schema
    // =========================================================================
    echo "\n[Scenario A: Full-FX Schema Settlement]\n";

    assertCondition(
        $service->supportsFinancePaymentExchangeFields() === true,
        'Full-FX schema correctly recognized by supportsFinancePaymentExchangeFields()'
    );

    // Insert Invoice #101 in Clinic 1
    $db->table('finances')->insert([
        'id' => 101,
        'clinic_id' => 1,
        'transaction_id' => 'INV-FULLFX-101',
        'transaction_type' => 'invoice',
        'amount' => 350.00,
        'discount_amount' => 0.00,
        'tax_amount' => 0.00,
        'total_amount' => 350.00,
        'amount_paid' => 0.00,
        'balance_due' => 350.00,
        'payment_status' => 'pending',
        'payment_method' => 'cash',
        'liquidity_account' => 'safe_cash',
        'currency' => 'USD',
        'exchange_rate' => 3.500000,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);

    // Settle Invoice #101
    $resA = $service->bulkSettle(1, [101], 1);
    assertCondition($resA['settled_count'] === 1 && $resA['skipped_count'] === 0, 'Scenario A: Settle succeeded (1 settled, 0 skipped)');

    // Verify parent status updated
    $parentA = $db->table('finances')->where('id', 101)->get()->getRowArray();
    assertCondition(
        $parentA['payment_status'] === 'paid' && (float)$parentA['amount_paid'] === 350.00 && (float)$parentA['balance_due'] === 0.00,
        'Scenario A: Parent finance synchronized to paid with 0 balance due'
    );

    // Verify payment row
    $paymentsA = $db->table('finance_payments')->where('finance_id', 101)->get()->getResultArray();
    assertCondition(count($paymentsA) === 1, 'Scenario A: Exactly 1 payment row created');
    $payRowA = $paymentsA[0];
    assertCondition(
        (float)$payRowA['amount'] === 350.00
        && $payRowA['type'] === 'payment'
        && $payRowA['payment_method'] === 'cash'
        && $payRowA['liquidity_account'] === 'safe_cash'
        && $payRowA['currency'] === 'USD'
        && (float)$payRowA['amount_original'] === 100.00
        && (float)$payRowA['exchange_rate'] === 3.500000,
        'Scenario A: Payment row preserved account, amount, type, and persisted FX fields (currency, rate, original)'
    );

    // Verify audit log metadata
    $auditLogsA = $db->table('activity_logs')->where('entity_id', 101)->get()->getResultArray();
    assertCondition(count($auditLogsA) === 1, 'Scenario A: Activity log recorded');
    $auditMetaA = json_decode((string)$auditLogsA[0]['metadata'], true);
    assertCondition(
        $auditMetaA['currency'] === 'USD'
        && (float)$auditMetaA['amount_original'] === 100.00
        && (float)$auditMetaA['exchange_rate'] === 3.500000
        && $auditMetaA['liquidity_account'] === 'safe_cash'
        && (float)$auditMetaA['amount'] === 350.00,
        'Scenario A: Audit log metadata contains exact FX fields, liquidity account, and amount'
    );

    // Idempotent second call
    $resA2 = $service->bulkSettle(1, [101], 1);
    assertCondition($resA2['settled_count'] === 0 && $resA2['skipped_count'] === 1, 'Scenario A: Idempotent second call returned 0 settled, 1 skipped');
    $countA2 = $db->table('finance_payments')->where('finance_id', 101)->countAllResults();
    assertCondition($countA2 === 1, 'Scenario A: No duplicate payment rows inserted on second call');

    // Cross-clinic rejection
    $db->table('finances')->insert([
        'id' => 102,
        'clinic_id' => 2,
        'transaction_id' => 'INV-FULLFX-102-CLINIC2',
        'transaction_type' => 'invoice',
        'total_amount' => 150.00,
        'amount_paid' => 0.00,
        'balance_due' => 150.00,
        'payment_status' => 'pending',
        'payment_method' => 'cash',
        'liquidity_account' => 'safe_cash',
        'currency' => 'ILS',
    ]);

    $crossClinicCaught = false;
    try {
        $service->bulkSettle(1, [102], 1);
    } catch (\DomainException $e) {
        $crossClinicCaught = true;
    }
    assertCondition($crossClinicCaught, 'Scenario A: Cross-clinic settlement attempt rejected with DomainException');

    // Rollback after forced audit failure
    $db->table('finances')->insert([
        'id' => 103,
        'clinic_id' => 1,
        'transaction_id' => 'INV-FULLFX-103-FAIL',
        'transaction_type' => 'invoice',
        'total_amount' => 300.00,
        'amount_paid' => 0.00,
        'balance_due' => 300.00,
        'payment_status' => 'pending',
        'payment_method' => 'cash',
        'liquidity_account' => 'safe_cash',
        'currency' => 'ILS',
    ]);

    $auditFailCaught = false;
    try {
        $failingService->bulkSettle(1, [103], 1);
    } catch (\RuntimeException $e) {
        $auditFailCaught = true;
    }
    assertCondition($auditFailCaught, 'Scenario A: Audit failure triggered RuntimeException');

    $parentA3 = $db->table('finances')->where('id', 103)->get()->getRowArray();
    $paymentsA3 = $db->table('finance_payments')->where('finance_id', 103)->get()->getResultArray();
    assertCondition(
        $parentA3['payment_status'] === 'pending'
        && (float)$parentA3['amount_paid'] === 0.00
        && count($paymentsA3) === 0,
        'Scenario A: Rollback confirmed - parent row pending and no payment rows inserted after audit failure'
    );

    // =========================================================================
    // SCENARIO B: No-FX Legacy Schema (Columns Dropped from Temporary Table)
    // =========================================================================
    echo "\n[Scenario B: No-FX Legacy Schema Settlement]\n";

    // Drop optional FX columns on the temporary clone
    $db->query("ALTER TABLE `{$tempFinancePayments}` DROP COLUMN `currency`, DROP COLUMN `amount_original`, DROP COLUMN `exchange_rate`");
    $db->resetDataCache();

    assertCondition(
        $service->supportsFinancePaymentExchangeFields() === false,
        'No-FX legacy schema correctly recognized by supportsFinancePaymentExchangeFields() as false'
    );

    // Test lockSettlementPayments on legacy schema with no FX columns
    $lockedRead = $service->lockSettlementPayments(1, 101);
    assertCondition(count($lockedRead) === 1, 'Scenario B: lockSettlementPayments succeeded on legacy schema without FX columns');
    assertCondition(
        isset($lockedRead[0]['id'], $lockedRead[0]['amount'], $lockedRead[0]['type'])
        && !array_key_exists('currency', $lockedRead[0])
        && !array_key_exists('amount_original', $lockedRead[0])
        && !array_key_exists('exchange_rate', $lockedRead[0]),
        'Scenario B: lockSettlementPayments returns only id, amount, and type'
    );

    // Insert Expense #201 in Clinic 1
    $db->table('finances')->insert([
        'id' => 201,
        'clinic_id' => 1,
        'transaction_id' => 'EXP-NOFX-201',
        'transaction_type' => 'expense',
        'amount' => 450.00,
        'discount_amount' => 0.00,
        'tax_amount' => 0.00,
        'total_amount' => 450.00,
        'amount_paid' => 0.00,
        'balance_due' => 450.00,
        'payment_status' => 'pending',
        'payment_method' => 'card',
        'liquidity_account' => 'bank',
        'currency' => 'ILS',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);

    // Settle Expense #201 on legacy schema
    $resB = $service->bulkSettle(1, [201], 1);
    assertCondition($resB['settled_count'] === 1 && $resB['skipped_count'] === 0, 'Scenario B: Settle succeeded on legacy table (1 settled, 0 skipped)');

    // Verify parent status updated
    $parentB = $db->table('finances')->where('id', 201)->get()->getRowArray();
    assertCondition(
        $parentB['payment_status'] === 'paid' && (float)$parentB['amount_paid'] === 450.00 && (float)$parentB['balance_due'] === 0.00,
        'Scenario B: Parent expense synchronized to paid with 0 balance due'
    );

    // Verify payment row on legacy table
    $paymentsB = $db->table('finance_payments')->where('finance_id', 201)->get()->getResultArray();
    assertCondition(count($paymentsB) === 1, 'Scenario B: Exactly 1 payment row created');
    $payRowB = $paymentsB[0];
    assertCondition(
        (float)$payRowB['amount'] === 450.00
        && $payRowB['type'] === 'payment'
        && $payRowB['payment_method'] === 'card'
        && $payRowB['liquidity_account'] === 'bank'
        && !array_key_exists('currency', $payRowB)
        && !array_key_exists('amount_original', $payRowB)
        && !array_key_exists('exchange_rate', $payRowB),
        'Scenario B: Payment row preserved liquidity_account, amount, type; excluded FX columns from insert'
    );

    // Verify audit log metadata on legacy schema
    $auditLogsB = $db->table('activity_logs')->where('entity_id', 201)->get()->getResultArray();
    assertCondition(count($auditLogsB) === 1, 'Scenario B: Activity log recorded');
    $auditMetaB = json_decode((string)$auditLogsB[0]['metadata'], true);
    assertCondition(
        $auditMetaB['currency'] === 'ILS'
        && (float)$auditMetaB['amount_original'] === 450.00
        && (float)$auditMetaB['exchange_rate'] === 1.000000
        && $auditMetaB['liquidity_account'] === 'bank'
        && (float)$auditMetaB['amount'] === 450.00,
        'Scenario B: Audit log metadata STILL retained currency, rate, original, and liquidity account'
    );

    // Idempotent second call on legacy schema
    $resB2 = $service->bulkSettle(1, [201], 1);
    assertCondition($resB2['settled_count'] === 0 && $resB2['skipped_count'] === 1, 'Scenario B: Idempotent second call returned 0 settled, 1 skipped');
    $countB2 = $db->table('finance_payments')->where('finance_id', 201)->countAllResults();
    assertCondition($countB2 === 1, 'Scenario B: No duplicate payment rows inserted on second call');

    // Cross-clinic rejection on legacy schema
    $db->table('finances')->insert([
        'id' => 202,
        'clinic_id' => 2,
        'transaction_id' => 'EXP-NOFX-202-CLINIC2',
        'transaction_type' => 'expense',
        'total_amount' => 50.00,
        'amount_paid' => 0.00,
        'balance_due' => 50.00,
        'payment_status' => 'pending',
        'payment_method' => 'card',
        'liquidity_account' => 'bank',
        'currency' => 'ILS',
    ]);

    $crossClinicCaughtB = false;
    try {
        $service->bulkSettle(1, [202], 1);
    } catch (\DomainException $e) {
        $crossClinicCaughtB = true;
    }
    assertCondition($crossClinicCaughtB, 'Scenario B: Cross-clinic settlement attempt rejected with DomainException');

    // Rollback after forced audit failure on legacy schema
    $db->table('finances')->insert([
        'id' => 203,
        'clinic_id' => 1,
        'transaction_id' => 'EXP-NOFX-203-FAIL',
        'transaction_type' => 'expense',
        'total_amount' => 500.00,
        'amount_paid' => 0.00,
        'balance_due' => 500.00,
        'payment_status' => 'pending',
        'payment_method' => 'card',
        'liquidity_account' => 'bank',
        'currency' => 'ILS',
    ]);

    $auditFailCaughtB = false;
    try {
        $failingService->bulkSettle(1, [203], 1);
    } catch (\RuntimeException $e) {
        $auditFailCaughtB = true;
    }
    assertCondition($auditFailCaughtB, 'Scenario B: Audit failure triggered RuntimeException');

    $parentB3 = $db->table('finances')->where('id', 203)->get()->getRowArray();
    $paymentsB3 = $db->table('finance_payments')->where('finance_id', 203)->get()->getResultArray();
    assertCondition(
        $parentB3['payment_status'] === 'pending'
        && (float)$parentB3['amount_paid'] === 0.00
        && count($paymentsB3) === 0,
        'Scenario B: Rollback confirmed - parent row pending and no payment rows inserted after audit failure'
    );

    echo "\n====================================================================\n";
    echo "PR #11 OPTIONAL FX SCHEMA COMPATIBILITY VERIFICATION: ALL PASS\n";
    echo "Passed {$passedTests} / {$totalTests} assertions successfully.\n";
    echo "====================================================================\n";

} finally {
    echo "\n[Cleanup] Restoring original prefix and cleaning temporary tables...\n";
    if (isset($db) && $db instanceof BaseConnection) {
        $db->setPrefix($originalPrefix);
        $db->resetDataCache();

        // Safety assertion: Only drop temporary tables that strictly match $randomPrefix
        foreach ([$tempFinances, $tempFinancePayments, $tempActivityLogs] as $tmpTable) {
            if (isset($randomPrefix) && $randomPrefix !== '' && str_starts_with($tmpTable, $randomPrefix)) {
                $db->query("DROP TEMPORARY TABLE IF EXISTS `{$tmpTable}`");
            }
        }
    }
    echo "[Cleanup] Complete. Base tables untouched.\n";
}

exit(0);
