<?php
declare(strict_types=1);

/**
 * Standalone No-DB Test Suite for Finance Preservation Contracts:
 * - FinancePaymentModel::getNetCashPaidTotal query construction & aggregation
 * - App\Controllers\Finance::refundInvoicePayment
 * - App\Controllers\Finance::voidInvoicePayment
 *
 * Requirements:
 * - NO database connection, NO framework/CI bootstrap, NO PHPUnit discovery
 * - Reflection newInstanceWithoutConstructor on App\Controllers\Finance
 * - Narrow doubles for DB, models, logger, request, response
 */

// --------------------------------------------------------------------------
// 1. Framework & Global Stubs
// --------------------------------------------------------------------------

namespace CodeIgniter {
    abstract class Controller {
        protected $request;
        protected $response;
        protected $logger;
    }
}

namespace App\Controllers {
    abstract class BaseController extends \CodeIgniter\Controller {
        protected function normalizePostedDateOnly($field): ?string {
            return null;
        }
    }
}

namespace App\Models {
    if (!class_exists(TenantAwareModel::class, false)) {
        abstract class TenantAwareModel {
            public $builderMock;
            public function select(string $select, ?bool $escape = null): self {
                $this->builderMock->select($select, $escape);
                return $this;
            }
            public function where($key, $value = null): self {
                $this->builderMock->where($key, $value);
                return $this;
            }
            public function get() { return $this->builderMock->get(); }
            public function first(): ?array {
                return $this->builderMock->get()->getRowArray();
            }
        }
    }
}

namespace App\Libraries {
    class ApiRuntimeContext { public static function getUserId(): int { return 7; } }
}

namespace {
    // Global mocks
    $GLOBALS['__mock_session_data'] = ['active_clinic_id' => 15];
    $GLOBALS['__mock_settings_data'] = ['currency' => 'ILS'];
    $GLOBALS['__mock_logs'] = [];

    if (!function_exists('session')) {
        function session() {
            return new class {
                public function get(string $key) {
                    return $GLOBALS['__mock_session_data'][$key] ?? null;
                }
            };
        }
    }

    if (!function_exists('csrf_hash')) {
        function csrf_hash(): string {
            return 'mock_csrf_token_hash';
        }
    }

    if (!function_exists('settings')) {
        function settings() {
            return new class {
                public function get(string $key, $default = null) {
                    return $GLOBALS['__mock_settings_data'][$key] ?? $default;
                }
                public function getCurrencySymbol(): string {
                    return '₪';
                }
            };
        }
    }

    if (!function_exists('log_message')) {
        function log_message(string $level, string $message, array $context = []): void {
            $GLOBALS['__mock_logs'][] = ['level' => $level, 'message' => $message];
        }
    }

    // Require actual classes
    require_once __DIR__ . '/../app/Models/FinancePaymentModel.php';
    require_once __DIR__ . '/../app/Controllers/Finance.php';
    require_once __DIR__ . '/../app/Services/FinanceTreasuryService.php';

    // ----------------------------------------------------------------------
    // 2. Test Doubles
    // ----------------------------------------------------------------------

    class MockQueryResult {
        private array $row;
        public function __construct(array $row) { $this->row = $row; }
        public function getRowArray(): ?array { return $this->row ?: null; }
    }

    class MockDatabase {
        public int $transDepth = 0;
        public array $transHistory = [];
        public bool $transStatusReturn = true;
        public ?bool $transStatusAfterBegin = null;
        public bool $transCommitReturn = true;
        public bool $transBeginReturn = true;
        public ?array $parentRow = null;
        public ?\Throwable $queryException = null;
        public string $prefix = '';

        public function prefixTable(string $table): string {
            return $this->prefix . $table;
        }

        public function escapeIdentifiers($item) {
            if (is_array($item)) {
                return array_map([$this, 'escapeIdentifiers'], $item);
            }
            return '`' . str_replace('`', '``', (string) $item) . '`';
        }

        public function transBegin(): bool {
            $this->transHistory[] = 'BEGIN';
            if ($this->transBeginReturn) $this->transDepth++;
            return $this->transBeginReturn;
        }

        public function transRollback(): bool {
            $this->transDepth--;
            $this->transHistory[] = 'ROLLBACK';
            return true;
        }

        public function transCommit(): bool {
            if ($this->transCommitReturn) $this->transDepth--;
            $this->transHistory[] = 'COMMIT';
            return $this->transCommitReturn;
        }

        public function transStatus(): bool {
            if ($this->transStatusAfterBegin !== null && in_array('BEGIN', $this->transHistory, true)) {
                return $this->transStatusAfterBegin;
            }
            return $this->transStatusReturn;
        }

        public function fieldExists(string $field, string $table): bool { return false; }

        public function query(string $sql, array $binds = []): MockQueryResult {
            $this->transHistory[] = ['QUERY' => $sql, 'BINDS' => $binds];
            if ($this->queryException !== null) {
                throw $this->queryException;
            }
            return new MockQueryResult($this->parentRow ?? []);
        }
    }

    class MockRequest {
        public array $postData = [];
        public function getPost(?string $key = null) {
            if ($key === null) return $this->postData;
            return $this->postData[$key] ?? null;
        }
    }

    class MockResponse {
        public int $statusCode = 200;
        public ?array $jsonData = null;

        public function setStatusCode(int $code): self {
            $this->statusCode = $code;
            return $this;
        }

        public function setJSON(array $data): self {
            $this->jsonData = $data;
            return $this;
        }
    }

    class MockActivityLogger {
        public array $logs = [];
        public bool $returnSuccess = true;

        public function logFinanceActivity(string $action, int $financeId, string $message): bool {
            $this->logs[] = ['action' => $action, 'id' => $financeId, 'message' => $message];
            return $this->returnSuccess;
        }
    }

    class MockFinanceModelDouble {
        public ?array $row = null;
        public bool $updateReturn = true;
        public array $updates = [];

        public function findByClinic(int $clinicId, int $id): ?array {
            if ($this->row && ($this->row['clinic_id'] ?? 0) === $clinicId && ($this->row['id'] ?? 0) === $id) {
                return $this->row + ['total_amount' => 100.0];
            }
            return null;
        }

        public function setValidationRules(array $rules): void {}
        public function update(int $id, array $data): bool {
            $this->updates[] = ['id' => $id, 'data' => $data];
            return $this->updateReturn;
        }
    }

    class MockFinancePaymentModelDouble {
        public float $netCashPaidTotal = 0.0;
        public float $netSettledAmount = 0.0;
        public float $relatedAdjustments = 0.0;
        public ?array $paymentRow = null;
        public bool $insertReturn = true;
        public array $insertedRecords = [];
        public array $whereConditions = [];
        public array $cashCalls = [];

        public function getNetCashPaidTotal(int $clinicId, int $financeId): float {
            $this->cashCalls[] = [$clinicId, $financeId];
            return $this->netCashPaidTotal;
        }

        public function getNetSettledAmount(int $clinicId, int $financeId): float {
            return $this->netSettledAmount;
        }

        public function getRelatedAdjustmentsTotal(int $clinicId, int $paymentId): float {
            return $this->relatedAdjustments;
        }

        public function where(string $key, $value): self {
            $this->whereConditions[$key] = $value;
            return $this;
        }

        public function find(int $id): ?array {
            if (($this->whereConditions['clinic_id'] ?? null) !== 15 || ($this->whereConditions['finance_id'] ?? null) !== 204) throw new \RuntimeException('Receipt lookup missing scope');
            if ($this->paymentRow && ($this->paymentRow['id'] ?? 0) === $id) {
                return $this->paymentRow;
            }
            return null;
        }

        public function insert(array $data): bool {
            $this->insertedRecords[] = $data;
            return $this->insertReturn;
        }
    }

    // Builder double for Model unit query tests
    class MockBuilder {
        public ?string $selectExpression = null;
        public ?bool $selectEscape = null;
        public array $whereClauses = [];
        public ?array $resultRow = null;

        public function select(string $select, ?bool $escape = null): self {
            $this->selectExpression = $select;
            $this->selectEscape = $escape;
            return $this;
        }

        public function where($key, $value = null): self {
            $this->whereClauses[] = [$key, $value];
            return $this;
        }

        public function get() {
            return new class($this->resultRow) {
                private ?array $row;
                public function __construct(?array $row) { $this->row = $row; }
                public function getRowArray(): ?array { return $this->row; }
                public function getRow(): ?object { return $this->row === null ? null : (object) $this->row; }
            };
        }
    }

    // ----------------------------------------------------------------------
    // 3. Harness Setup
    // ----------------------------------------------------------------------

    function createControllerHarness(): array {
        $ref = new ReflectionClass(\App\Controllers\Finance::class);
        $controller = $ref->newInstanceWithoutConstructor();

        $db = new MockDatabase();
        $request = new MockRequest();
        $response = new MockResponse();
        $logger = new MockActivityLogger();
        $financeModel = new MockFinanceModelDouble();
        $financePaymentModel = new MockFinancePaymentModelDouble();

        // Inject properties via reflection
        $props = [
            'db' => $db,
            'request' => $request,
            'response' => $response,
            'activityLogger' => $logger,
            'financeModel' => $financeModel,
            'financePaymentModel' => $financePaymentModel,
            'financeTreasuryService' => new \App\Services\FinanceTreasuryService(),
        ];

        foreach ($props as $name => $value) {
            if ($ref->hasProperty($name)) {
                $p = $ref->getProperty($name);
                $p->setAccessible(true);
                $p->setValue($controller, $value);
            }
        }

        return [$controller, $db, $request, $response, $logger, $financeModel, $financePaymentModel];
    }

    // ----------------------------------------------------------------------
    // 4. Execution & Assertions
    // ----------------------------------------------------------------------

    $passed = 0;
    $failed = 0;

    function test_assert(bool $condition, string $description, ?string $failureDetail = null): void {
        global $passed, $failed;
        if ($condition) {
            echo "  [PASS] {$description}\n";
            $passed++;
        } else {
            echo "  [FAIL] {$description}" . ($failureDetail ? " -> {$failureDetail}" : "") . "\n";
            $failed++;
        }
    }

    echo "=====================================================================\n";
    echo "Running Finance Preservation Controller & Model Contract Tests\n";
    echo "=====================================================================\n\n";

    // ----------------------------------------------------------------------
    // SECTION A: FinancePaymentModel::getNetCashPaidTotal Query Assembly
    // ----------------------------------------------------------------------
    echo "--- Section A: FinancePaymentModel::getNetCashPaidTotal SQL Contract ---\n";
    $builder = new MockBuilder();
    $builder->resultRow = ['total_amount' => '120.50'];

    $model = new \App\Models\FinancePaymentModel();
    $model->builderMock = $builder;

    $result = $model->getNetCashPaidTotal(15, 204);

    test_assert(
        strpos($builder->selectExpression, "WHEN type = 'payment' THEN amount") !== false
        && strpos($builder->selectExpression, "WHEN type IN ('refund','void') THEN -amount") !== false
        && strpos($builder->selectExpression, 'adjustment') === false,
        "SQL SUM expression correctly excludes adjustments and subtracts refunds/voids"
    );
    test_assert($builder->selectEscape === false, "Select expression passed with escape = false");
    test_assert($builder->whereClauses === [['clinic_id', 15], ['finance_id', 204]], "Scoped by clinic_id = 15 and finance_id = 204");
    test_assert($result === 120.50, "Parsed float result returned (120.50)");

    // Clamping test
    $builder->resultRow = ['total_amount' => '-50.00'];
    $clampedResult = $model->getNetCashPaidTotal(15, 204);
    test_assert($clampedResult === 0.0, "Negative net aggregate clamped to 0.0");

    echo "\n";

    // ----------------------------------------------------------------------
    // SECTION B: refundInvoicePayment Tests
    // ----------------------------------------------------------------------
    echo "--- Section B: App\Controllers\Finance::refundInvoicePayment ---\n";

    // Test B1: Tenant Context Missing
    [$c, $db, $req, $res] = createControllerHarness();
    $GLOBALS['__mock_session_data']['active_clinic_id'] = null;
    $c->refundInvoicePayment(204);
    test_assert($res->statusCode === 403, "B1: Tenant context missing returns 403");
    $GLOBALS['__mock_session_data']['active_clinic_id'] = 15;

    // Test B2: Invoice not found -> Rollback & success=false
    [$c, $db, $req, $res] = createControllerHarness();
    $db->parentRow = null;
    $c->refundInvoicePayment(204);
    test_assert(in_array('ROLLBACK', $db->transHistory, true), "B2: Invoice not found rolls back transaction");
    test_assert($res->jsonData['success'] === false, "B2: Invoice not found returns success=false");

    // Test B3: Write-Off Only / Cash 0 Rejection
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fpm->netCashPaidTotal = 0.0; // Settled via credit/write-off only
    $c->refundInvoicePayment(204);
    test_assert(in_array('ROLLBACK', $db->transHistory, true), "B3: Zero collected cash rolls back transaction");
    test_assert(strpos($res->jsonData['message'] ?? '', 'No refundable cash balance') !== false, "B3: Returns 'No refundable cash balance' message");

    // Test B4: Cash-Only Ceiling Exceeded -> Generic error response & no append
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fpm->netCashPaidTotal = 50.0;
    $req->postData = ['amount' => '75.00']; // Exceeds 50.0 cash ceiling
    $c->refundInvoicePayment(204);
    test_assert(in_array('ROLLBACK', $db->transHistory, true), "B4: Amount exceeding cash ceiling rolls back transaction");
    test_assert($res->jsonData['message'] === 'Unable to record the refund. Please try again.', "B4: Returns generic error message");
    test_assert(count($fpm->insertedRecords) === 0, "B4: No record appended on ceiling error");

    // Test B5: Selected Receipt Cannot Bypass Global Cash
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fpm->netCashPaidTotal = 30.0; // Global cash is only 30 (e.g., prior unlinked refund)
    $fpm->paymentRow = ['id' => 99, 'amount' => 100.0, 'type' => 'payment'];
    $fpm->relatedAdjustments = 0.0; // Receipt itself has 100 remaining
    $req->postData = ['amount' => '50.00', 'related_payment_id' => 99]; // Within receipt (50 < 100), but exceeds global cash (50 > 30)
    $c->refundInvoicePayment(204);
    test_assert(in_array('ROLLBACK', $db->transHistory, true), "B5: Receipt exceeding global cash rolls back transaction");
    test_assert($res->jsonData['success'] === false, "B5: Global cash ceiling prevents double-reversal bypass");

    // Test B6: Insert Failure -> Rollback
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fm->row = $db->parentRow;
    $fpm->netCashPaidTotal = 100.0;
    $fpm->insertReturn = false; // Insert fails
    $req->postData = ['amount' => '50.00'];
    $c->refundInvoicePayment(204);
    test_assert(in_array('ROLLBACK', $db->transHistory, true), "B6: Payment insert failure rolls back transaction");
    test_assert($res->jsonData['message'] === 'Failed to record refund.', "B6: Returns failure message");

    // Test B7: Settlement Status Sync Failure -> Rollback
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fm->row = $db->parentRow;
    $fm->updateReturn = false; // Finance row status update fails
    $fpm->netCashPaidTotal = 100.0;
    $req->postData = ['amount' => '50.00'];
    $c->refundInvoicePayment(204);
    test_assert(in_array('ROLLBACK', $db->transHistory, true), "B7: Settlement sync failure rolls back transaction");
    test_assert(strpos($res->jsonData['message'] ?? '', 'Failed to update invoice settlement status') !== false, "B7: Returns sync failure message");

    // Test B8: Commit Failure -> Rollback
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fm->row = $db->parentRow;
    $fpm->netCashPaidTotal = 100.0;
    $db->transCommitReturn = false; // Commit fails
    $req->postData = ['amount' => '50.00'];
    $c->refundInvoicePayment(204);
    test_assert(in_array('ROLLBACK', $db->transHistory, true), "B8: transCommit failure rolls back transaction");
    test_assert($res->jsonData['message'] === 'Unable to record the refund. Please try again.', "B8: Commit failure returns generic message");

    // Test B9: Successful Refund Under Checked Lock
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fm->row = $db->parentRow;
    $fpm->netCashPaidTotal = 100.0;
    $req->postData = ['amount' => '40.00'];
    $c->refundInvoicePayment(204);
    test_assert($res->jsonData['success'] === true, "B9: Valid refund succeeds");
    test_assert(in_array('COMMIT', $db->transHistory, true), "B9: Valid refund commits transaction");
    test_assert(count($fpm->insertedRecords) === 1 && $fpm->insertedRecords[0]['type'] === 'refund', "B9: Refund record inserted with type='refund'");

    echo "\n";

    // ----------------------------------------------------------------------
    // SECTION C: voidInvoicePayment Tests
    // ----------------------------------------------------------------------
    echo "--- Section C: App\Controllers\Finance::voidInvoicePayment ---\n";

    // Test C1: Foreign / Missing Receipt
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fpm->paymentRow = null; // Receipt not found in clinic/finance
    $c->voidInvoicePayment(204, 999);
    test_assert(in_array('ROLLBACK', $db->transHistory, true), "C1: Missing/foreign payment rolls back transaction");
    test_assert($res->jsonData['message'] === 'Payment not found.', "C1: Returns 'Payment not found.'");

    // Test C2: Already Voided Receipt (receipt remainder <= 0)
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fpm->paymentRow = ['id' => 88, 'amount' => 50.0, 'type' => 'payment'];
    $fpm->relatedAdjustments = 50.0; // Fully refunded/voided already
    $c->voidInvoicePayment(204, 88);
    test_assert(in_array('ROLLBACK', $db->transHistory, true), "C2: Already refunded/voided receipt rolls back transaction");
    test_assert(strpos($res->jsonData['message'] ?? '', 'already refunded or voided') !== false, "C2: Returns already refunded message");

    // Test C3: Void Limited to BOTH Remaining (min(receiptRemaining, globalCashRemaining))
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fm->row = $db->parentRow;
    $fpm->paymentRow = ['id' => 88, 'amount' => 100.0, 'type' => 'payment'];
    $fpm->relatedAdjustments = 0.0; // Receipt remaining is 100
    $fpm->netCashPaidTotal = 35.0; // Global remaining cash is only 35 (e.g. prior unlinked refund)
    $c->voidInvoicePayment(204, 88);
    test_assert($res->jsonData['success'] === true, "C3: Void succeeds with clamped amount");
    test_assert($db->transHistory[1]['QUERY'] === "SELECT * FROM `finances` WHERE clinic_id = ? AND id = ? FOR UPDATE", "C3: Void quotes empty-prefix table identifier");
    test_assert($db->transHistory[1]['BINDS'] === [15, 204], "C3: Void binds clinic and invoice independently from identifier");
    test_assert(
        count($fpm->insertedRecords) === 1 && $fpm->insertedRecords[0]['amount'] === 35.0,
        "C3: Void amount capped at min(receipt=100, globalCash=35) -> 35.00"
    );

    // Test C4: Zero Remaining Global Cash -> Rejection
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fpm->paymentRow = ['id' => 88, 'amount' => 100.0, 'type' => 'payment'];
    $fpm->relatedAdjustments = 0.0;
    $fpm->netCashPaidTotal = 0.0; // No collected cash remains
    $c->voidInvoicePayment(204, 88);
    test_assert(in_array('ROLLBACK', $db->transHistory, true), "C4: Zero global cash rolls back transaction");
    test_assert(strpos($res->jsonData['message'] ?? '', 'No remaining collected cash') !== false, "C4: Returns no collected cash message");

    // Test C5: Insert Failure on Void -> Rollback
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fm->row = $db->parentRow;
    $fpm->paymentRow = ['id' => 88, 'amount' => 50.0, 'type' => 'payment'];
    $fpm->netCashPaidTotal = 50.0;
    $fpm->insertReturn = false; // Void insert fails
    $c->voidInvoicePayment(204, 88);
    test_assert(in_array('ROLLBACK', $db->transHistory, true), "C5: Void insert failure rolls back transaction");

    // Test C6: Audit Failure -> Rollback
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fm->row = $db->parentRow;
    $fpm->paymentRow = ['id' => 88, 'amount' => 50.0, 'type' => 'payment'];
    $fpm->netCashPaidTotal = 50.0;
    $logger->returnSuccess = false; // Audit fails
    $c->voidInvoicePayment(204, 88);
    test_assert(in_array('ROLLBACK', $db->transHistory, true), "C6: Audit logging failure triggers rollback");
    test_assert($res->jsonData['message'] === 'Unable to void the payment. Please try again.', "C6: Returns generic message on audit failure");

    // Test C7: Commit Failure on Void -> Rollback
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fm->row = $db->parentRow;
    $fpm->paymentRow = ['id' => 88, 'amount' => 50.0, 'type' => 'payment'];
    $fpm->netCashPaidTotal = 50.0;
    $db->transCommitReturn = false; // transCommit returns false
    $c->voidInvoicePayment(204, 88);
    test_assert(in_array('ROLLBACK', $db->transHistory, true), "C7: transCommit failure rolls back transaction");
    test_assert($res->jsonData['message'] === 'Unable to void the payment. Please try again.', "C7: Commit failure returns generic message");

    // ----------------------------------------------------------------------
    // SECTION D: Boundary Fail-Closed Iteration & Transaction Lifecycle
    // ----------------------------------------------------------------------
    echo "\n--- Section D: Transaction Failure Boundaries & Generic Responses ---\n";

    // Both real controller methods must fail closed at every transaction boundary.
    foreach (['refundInvoicePayment', 'voidInvoicePayment'] as $method) {
        foreach (['begin', 'insert', 'update', 'audit', 'status', 'commit'] as $failure) {
            [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
            $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
            $fm->row = $db->parentRow;
            $fpm->paymentRow = ['id' => 88, 'amount' => 100.0, 'type' => 'payment'];
            $fpm->netCashPaidTotal = 100.0;
            $req->postData = ['amount' => '40.00'];

            if ($failure === 'begin') $db->transBeginReturn = false;
            if ($failure === 'insert') $fpm->insertReturn = false;
            if ($failure === 'update') $fm->updateReturn = false;
            if ($failure === 'audit') $logger->returnSuccess = false;
            if ($failure === 'status') $db->transStatusAfterBegin = false; // Poison status AFTER begin
            if ($failure === 'commit') $db->transCommitReturn = false;

            $method === 'refundInvoicePayment' ? $c->$method(204) : $c->$method(204, 88);

            test_assert(($res->jsonData['success'] ?? null) === false, "$method $failure failure cannot return success");
            test_assert($db->transDepth === 0, "$method $failure failure leaves no mock transaction open");
            test_assert(!in_array('COMMIT', $db->transHistory, true) || $failure === 'commit', "$method $failure failure cannot commit");
            if ($failure === 'begin') test_assert(count($fpm->insertedRecords) === 0 && count($db->transHistory) === 1, "$method failed begin performs no query or append");
            if ($failure === 'audit') {
                $expectedGeneric = ($method === 'refundInvoicePayment') ? 'Unable to record the refund. Please try again.' : 'Unable to void the payment. Please try again.';
                test_assert(count($logger->logs) === 1 && $res->jsonData['success'] === false && $res->jsonData['message'] === $expectedGeneric, "$method audit failure invokes audit logger and returns generic failure response");
            }
            if ($failure === 'update') test_assert(count($fm->updates) === 1 && count($logger->logs) === 0, "$method update failure stops before audit");
        }
    }

    foreach ([['id' => 88, 'amount' => 20.0, 'type' => 'payment'], null, ['id' => 88, 'amount' => 100.0, 'type' => 'adjustment']] as $receipt) {
        [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
        $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
        $fm->row = $db->parentRow;
        $fpm->netCashPaidTotal = 100.0;
        $fpm->paymentRow = $receipt;
        $req->postData = ['amount' => '40.00', 'related_payment_id' => 88];
        $c->refundInvoicePayment(204);
        test_assert(($res->jsonData['success'] ?? null) === false && count($fpm->insertedRecords) === 0, 'Refund rejects exhausted, foreign/missing, or non-payment receipt before append');
        test_assert($fpm->whereConditions === ['clinic_id' => 15, 'finance_id' => 204], 'Actual receipt lookup assembles clinic and invoice predicates');
    }

    // ----------------------------------------------------------------------
    // SECTION E: Pre-Transaction Guards, Prefix Quoting & Secret Path Sanitization
    // ----------------------------------------------------------------------
    echo "\n--- Section E: Pre-Transaction Guards, Prefix Quoting & Sanitization ---\n";

    // E1: Outer depth 1 rejection (must remain depth 1, zero begin/query/rollback/append/audit)
    foreach (['refundInvoicePayment', 'voidInvoicePayment'] as $method) {
        [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
        $db->transDepth = 1;
        $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
        $fpm->paymentRow = ['id' => 88, 'amount' => 100.0, 'type' => 'payment'];
        $fpm->netCashPaidTotal = 100.0;
        $req->postData = ['amount' => '40.00'];

        $method === 'refundInvoicePayment' ? $c->$method(204) : $c->$method(204, 88);

        $expectedGeneric = ($method === 'refundInvoicePayment') ? 'Unable to record the refund. Please try again.' : 'Unable to void the payment. Please try again.';
        test_assert($res->jsonData['success'] === false, "$method rejects when transDepth !== 0");
        test_assert($db->transDepth === 1, "$method preserves caller transDepth at 1");
        test_assert(count($db->transHistory) === 0, "$method has zero begin/query/rollback on outer transDepth 1");
        test_assert(count($fpm->insertedRecords) === 0, "$method does not append record on outer transDepth 1");
        test_assert(count($logger->logs) === 0, "$method does not write audit log on outer transDepth 1");
        test_assert($res->jsonData['message'] === $expectedGeneric, "$method returns generic message on outer depth 1");
    }

    // E2: Poisoned transStatus at depth 0 rejection (depth unchanged 0, zero begin/query/rollback/append/audit)
    foreach (['refundInvoicePayment', 'voidInvoicePayment'] as $method) {
        [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
        $db->transDepth = 0;
        $db->transStatusReturn = false;
        $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
        $fpm->paymentRow = ['id' => 88, 'amount' => 100.0, 'type' => 'payment'];
        $fpm->netCashPaidTotal = 100.0;
        $req->postData = ['amount' => '40.00'];

        $method === 'refundInvoicePayment' ? $c->$method(204) : $c->$method(204, 88);

        $expectedGeneric = ($method === 'refundInvoicePayment') ? 'Unable to record the refund. Please try again.' : 'Unable to void the payment. Please try again.';
        test_assert($res->jsonData['success'] === false, "$method rejects when transStatus === false at depth 0");
        test_assert($db->transDepth === 0, "$method maintains transDepth 0");
        test_assert(count($db->transHistory) === 0, "$method has zero begin/query/rollback on poisoned status");
        test_assert(count($fpm->insertedRecords) === 0, "$method does not append record on poisoned status");
        test_assert(count($logger->logs) === 0, "$method does not write audit log on poisoned status");
        test_assert($res->jsonData['message'] === $expectedGeneric, "$method returns generic message on poisoned status");
    }

    // E3: Actual lock query uses quoted identifier with default (empty) prefix and 'ci_' prefix
    // E3a: Default empty prefix
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->prefix = '';
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fm->row = $db->parentRow;
    $fpm->netCashPaidTotal = 100.0;
    $req->postData = ['amount' => '40.00'];
    $c->refundInvoicePayment(204);
    test_assert($db->transHistory[1]['QUERY'] === "SELECT * FROM `finances` WHERE clinic_id = ? AND id = ? FOR UPDATE", "Lock query quotes default table identifier: `finances`");
    test_assert($db->transHistory[1]['BINDS'] === [15, 204], "Lock query binds [clinic_id, id] -> [15, 204]");

    // E3b: Non-empty 'ci_' prefix on refund
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->prefix = 'ci_';
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fm->row = $db->parentRow;
    $fpm->netCashPaidTotal = 100.0;
    $req->postData = ['amount' => '40.00'];
    $c->refundInvoicePayment(204);
    test_assert($db->transHistory[1]['QUERY'] === "SELECT * FROM `ci_finances` WHERE clinic_id = ? AND id = ? FOR UPDATE", "Lock query quotes prefixed table identifier: `ci_finances`");
    test_assert($db->transHistory[1]['BINDS'] === [15, 204], "Lock query binds [clinic_id, id] with 'ci_' prefix");

    // E3c: Non-empty 'ci_' prefix on void
    [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
    $db->prefix = 'ci_';
    $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
    $fm->row = $db->parentRow;
    $fpm->paymentRow = ['id' => 88, 'amount' => 50.0, 'type' => 'payment'];
    $fpm->netCashPaidTotal = 50.0;
    $c->voidInvoicePayment(204, 88);
    test_assert($db->transHistory[1]['QUERY'] === "SELECT * FROM `ci_finances` WHERE clinic_id = ? AND id = ? FOR UPDATE", "Void lock query quotes prefixed table identifier: `ci_finances`");
    test_assert($db->transHistory[1]['BINDS'] === [15, 204], "Void lock query binds [clinic_id, id] with 'ci_' prefix");

    // E4: Fake query error with secret private path must be absent in public JSON and present in server log
    foreach (['refundInvoicePayment', 'voidInvoicePayment'] as $method) {
        $GLOBALS['__mock_logs'] = [];
        [$c, $db, $req, $res, $logger, $fm, $fpm] = createControllerHarness();
        $db->parentRow = ['id' => 204, 'clinic_id' => 15, 'transaction_type' => 'invoice', 'transaction_id' => 'INV-204'];
        $privateSecretPath = '/etc/secret/db_credentials_' . uniqid() . '.env';
        $db->queryException = new \RuntimeException("SQLSTATE[HY000]: Error connecting to {$privateSecretPath}");
        $fpm->paymentRow = ['id' => 88, 'amount' => 100.0, 'type' => 'payment'];
        $fpm->netCashPaidTotal = 100.0;
        $req->postData = ['amount' => '40.00'];

        $method === 'refundInvoicePayment' ? $c->$method(204) : $c->$method(204, 88);

        $expectedGeneric = ($method === 'refundInvoicePayment') ? 'Unable to record the refund. Please try again.' : 'Unable to void the payment. Please try again.';
        test_assert($res->jsonData['success'] === false, "$method on SQL error returns success=false");
        test_assert(!str_contains($res->jsonData['message'], 'secret'), "$method client message does NOT contain 'secret'");
        test_assert(!str_contains($res->jsonData['message'], $privateSecretPath), "$method client message does NOT leak private path");
        test_assert($res->jsonData['message'] === $expectedGeneric, "$method client message is generic sanitized string");

        $foundInLog = false;
        foreach ($GLOBALS['__mock_logs'] as $logEntry) {
            if (str_contains($logEntry['message'], $privateSecretPath)) {
                $foundInLog = true;
                break;
            }
        }
        test_assert($foundInLog, "$method logs internal exception detail containing private path to server log");
        test_assert($db->transDepth === 0 && in_array('ROLLBACK', $db->transHistory, true), "$method query exception rolls back its owned transaction");
        test_assert(count($fpm->insertedRecords) === 0 && count($logger->logs) === 0, "$method query exception stops before append or audit");
    }

    echo "\n=====================================================================\n";
    echo "Results: {$passed} Passed, {$failed} Failed\n";
    echo "NOTE: Standalone test doubles only. Live SQL atomicity and concurrency\n";
    echo "certification requires real multi-worker stage execution on port 3307.\n";
    echo "=====================================================================\n";

    if ($failed > 0) {
        exit(1);
    }
}
