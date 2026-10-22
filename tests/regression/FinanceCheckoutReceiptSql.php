<?php
declare(strict_types=1);
$bootstrap = getopt('', ['bootstrap:'])['bootstrap'] ?? '';
if (PHP_SAPI !== 'cli' || !is_file($bootstrap)) { exit(2); }
require $bootstrap;
$identity = $db->query('SELECT DATABASE() db, @@port port, @@datadir datadir')->getRowArray();
if ($db->hostname !== '127.0.0.1' || $identity['db'] !== 'm2c_finance_acceptance' || (int) $identity['port'] !== 3307
    || !str_contains(str_replace('\\', '/', $identity['datadir']), 'denta-m2c-20261021/disposable-mariadb/data')) {
    throw new RuntimeException('Owned disposable target required.');
}
$checks = 0;
function receiptAssert(bool $ok, string $message): void { global $checks; if (!$ok) { throw new RuntimeException($message); } ++$checks; }
function receiptDeny(callable $fn, string $message): void { try { $fn(); } catch (Throwable $e) { receiptAssert(true, $message); return; } throw new RuntimeException($message); }
$prefixes = [];
foreach ([true, false] as $withFx) {
    $prefix = 'checkout_receipt_' . bin2hex(random_bytes(5)) . '_'; $prefixes[] = $prefix;
    $cfg = config('Database')->default; $cfg['DBPrefix'] = $prefix;
    $test = Config\Database::connect($cfg, false); $test->initialize();
    $test->query('CREATE TABLE `' . $prefix . 'finances` (id INT UNSIGNED PRIMARY KEY, clinic_id INT UNSIGNED NOT NULL, transaction_id VARCHAR(30), transaction_type VARCHAR(20), amount DECIMAL(12,2), discount_amount DECIMAL(12,2), tax_amount DECIMAL(12,2), total_amount DECIMAL(12,2), amount_paid DECIMAL(12,2), balance_due DECIMAL(12,2), payment_status VARCHAR(20), paid_date DATE NULL, due_date DATE NULL, updated_at DATETIME NULL) ENGINE=InnoDB');
    $fxSql = $withFx ? ', currency CHAR(3), amount_original DECIMAL(12,2), exchange_rate DECIMAL(12,6)' : '';
    $test->query('CREATE TABLE `' . $prefix . 'finance_payments` (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, clinic_id INT UNSIGNED NOT NULL, finance_id INT UNSIGNED NOT NULL, amount DECIMAL(12,2) NOT NULL, type VARCHAR(20), payment_method VARCHAR(20), liquidity_account VARCHAR(30), payment_date DATE, notes TEXT NULL, created_by INT UNSIGNED NOT NULL, created_at DATETIME NULL, updated_at DATETIME NULL' . $fxSql . ', FOREIGN KEY(finance_id) REFERENCES `' . $prefix . 'finances`(id)) ENGINE=InnoDB');
    $test->query('CREATE TABLE `' . $prefix . 'activity_logs` (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, finance_id INT UNSIGNED, action VARCHAR(30), description TEXT) ENGINE=InnoDB');
    foreach ([1, 2] as $id) {
        $test->table('finances')->insert(['id' => $id, 'clinic_id' => $id, 'transaction_id' => 'SYNTH-' . $id, 'transaction_type' => 'invoice', 'amount' => 100, 'discount_amount' => 0, 'tax_amount' => 0, 'total_amount' => 100, 'amount_paid' => 0, 'balance_due' => 100, 'payment_status' => 'pending']);
    }
    $controller = (new ReflectionClass(App\Controllers\Finance::class))->newInstanceWithoutConstructor();
    $assign = static function (string $property, mixed $value) use ($controller): void { (new ReflectionProperty($controller, $property))->setValue($controller, $value); };
    $request = service('request'); $request->setGlobal('post', ['payment_method' => 'cash', 'liquidity_account' => 'safe_cash']);
    $assign('request', $request); $assign('db', $test);
    $assign('financePaymentModel', new App\Models\FinancePaymentModel($test));
    $assign('financeSettlementService', new App\Services\FinanceSettlementService($test));
    $assign('financeTreasuryService', new App\Services\FinanceTreasuryService());
    $logger = new class($test) {
        public bool $fail = false;
        public function __construct(private $db) {}
        public function logFinanceActivity($action, $id, $description): bool { return !$this->fail && (bool) $this->db->table('activity_logs')->insert(['finance_id' => $id, 'action' => $action, 'description' => $description]); }
    };
    $assign('activityLogger', $logger);
    $method = new ReflectionMethod($controller, 'recordLockedFinancePayment');
    $settlement = ['amount_base' => 40.0, 'amount_original' => 10.0, 'currency' => 'USD', 'exchange_rate' => 4.0, 'payment_method' => 'cash', 'payment_date' => '2026-10-22', 'notes' => 'Synthetic checkout'];
    $row = $test->table('finances')->where('id', 1)->get()->getRowArray();
    receiptDeny(fn() => $method->invoke($controller, 1, $row, $settlement, 1), 'Unowned transaction rejected');
    $test->transBegin(); $test->transBegin(); receiptDeny(fn() => $method->invoke($controller, 1, $row, $settlement, 1), 'Nested transaction rejected'); $test->transRollback(); $test->transRollback();
    $test->transBegin();
    receiptDeny(fn() => $method->invoke($controller, 2, $row, $settlement, 1), 'Foreign clinic parent rejected');
    receiptDeny(fn() => $method->invoke($controller, 1, $row, $settlement, 0), 'Missing actor rejected');
    $row = $test->query('SELECT * FROM `' . $prefix . 'finances` WHERE clinic_id=1 AND id=1 FOR UPDATE')->getRowArray();
    $paymentId = $method->invoke($controller, 1, $row, $settlement, 1);
    receiptAssert($test->transCommit() && $paymentId > 0, 'Shared receipt path commits');
    $payment = $test->table('finance_payments')->where('id', $paymentId)->get()->getRowArray();
    receiptAssert((float) $payment['amount'] === 40.0 && $payment['liquidity_account'] === 'safe_cash' && $payment['payment_date'] === '2026-10-22', 'Amount, treasury account and date retained');
    receiptAssert(!$withFx || ($payment['currency'] === 'USD' && (float) $payment['amount_original'] === 10.0 && (float) $payment['exchange_rate'] === 4.0), 'FX provenance persisted when supported');
    receiptAssert($withFx || !array_key_exists('currency', $payment), 'Legacy schema needs no FX columns');
    $updated = $test->table('finances')->where('id', 1)->get()->getRowArray();
    receiptAssert((float) $updated['amount_paid'] === 40.0 && (float) $updated['balance_due'] === 60.0 && $updated['payment_status'] === 'partial', 'Partial receipt synchronized');
    $paymentsBefore = $test->table('finance_payments')->orderBy('id')->get()->getResultArray();
    $logger->fail = true; $test->transBegin();
    $row = $test->query('SELECT * FROM `' . $prefix . 'finances` WHERE clinic_id=1 AND id=1 FOR UPDATE')->getRowArray();
    receiptDeny(fn() => $method->invoke($controller, 1, $row, $settlement, 1), 'Audit failure aborts receipt'); $test->transRollback();
    receiptAssert($paymentsBefore === $test->table('finance_payments')->orderBy('id')->get()->getResultArray(), 'Rollback preserves payment history verbatim');
    receiptAssert($updated === $test->table('finances')->where('id', 1)->get()->getRowArray(), 'Rollback preserves cached invoice position');
    $logger->fail = false; $settlement['amount_base'] = 60.0; $settlement['amount_original'] = 15.0; $test->transBegin();
    $row = $test->query('SELECT * FROM `' . $prefix . 'finances` WHERE clinic_id=1 AND id=1 FOR UPDATE')->getRowArray();
    $method->invoke($controller, 1, $row, $settlement, 1); receiptAssert($test->transCommit(), 'Final receipt commits');
    $updated = $test->table('finances')->where('id', 1)->get()->getRowArray();
    receiptAssert((float) $updated['amount_paid'] === 100.0 && (float) $updated['balance_due'] === 0.0 && $updated['payment_status'] === 'paid', 'Full receipt synchronized');
    receiptAssert($test->table('activity_logs')->countAllResults() === 2, 'Only committed receipts audited');
    receiptAssert($test->table('finances')->where('id', 2)->get()->getRowArray()['payment_status'] === 'pending', 'Other clinic remains unchanged');
}
echo json_encode(['status' => 'PASS', 'checks' => $checks, 'fixture_prefixes' => $prefixes, 'fixtures_retained' => true, 'scope' => 'Actual private Finance controller shared receipt helper, real FinancePaymentModel and settlement SQL in full/no-FX schemas; audit adapter is a transactional synthetic test double. Not routed checkout authorization or full invoice-creation certification.']), PHP_EOL;
