<?php

namespace App\Services;

use App\Libraries\ApiRuntimeContext;
use App\Models\FinanceModel;
use CodeIgniter\Database\BaseConnection;

class FinanceValidationException extends \InvalidArgumentException
{
    private array $errors = [];

    public function __construct(string|array $errors = '', int $code = 0, ?\Throwable $previous = null)
    {
        if (is_array($errors)) {
            $this->errors = $errors;
            parent::__construct(json_encode($errors), $code, $previous);
        } else {
            $this->errors = ['error' => $errors];
            parent::__construct($errors, $code, $previous);
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

class FinanceConflictException extends \DomainException {}
class FinanceNotFoundException extends \DomainException {}
class FinanceUnauthorizedException extends \RuntimeException {}
class FinanceForbiddenException extends \RuntimeException {}

class FinanceApiMutationService
{
    public const ALLOWED_PAYLOAD_FIELDS = [
        // Safe metadata fields
        'internal_note',
        'notes',

        // History-sensitive fields (editable ONLY when genuinely unpaid/unlinked)
        'amount',
        'discount_amount',
        'tax_percentage',
        'currency',
        'amount_original',
        'exchange_rate',
        'patient_id',
        'examination_id',
        'supplier_id',
        'expense_category',
        'payment_method',
        'liquidity_account',
        'transaction_date',
        'counterparty_name',
        'due_date',
        'description',
        'service_details',
        'service_type',

        // Computed / immutable fields (accepted ONLY if identical roundtrip; direct mutation rejected)
        'transaction_type',
        'source_entity_type',
        'source_entity_id',
        'transaction_id',
        'clinic_id',
        'id',
        'created_by',
        'created_at',
        'updated_at',
        'tax_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'paid_date',
        'payment_status',
    ];

    public const HISTORY_SENSITIVE_FIELDS = InvoiceLifecyclePolicy::EXPENSE_HISTORY_SENSITIVE_FIELDS;

    /** Existing direct payment/refund metadata editing remains compatible; expense history is stricter. */
    public const NON_EXPENSE_METADATA_FIELDS = [
        'counterparty_name', 'due_date', 'description', 'service_details', 'service_type',
    ];

    public const STRICT_IMMUTABLE_FIELDS = [
        'transaction_type',
        'source_entity_type',
        'source_entity_id',
        'transaction_id',
        'clinic_id',
        'id',
        'created_by',
        'created_at',
    ];

    public const COMPUTED_FIELDS = [
        'tax_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'paid_date',
        'payment_status',
    ];

    private ?string $internalNote = null;
    private BaseConnection $db;
    private FinanceModel $model;
    private FinanceTreasuryService $treasury;
    private array $tableExistsCache = [];

    public function __construct(
        ?BaseConnection $db = null,
        ?FinanceModel $model = null,
        ?FinanceTreasuryService $treasury = null
    ) {
        if ($model !== null) {
            $this->model = $model;
            $this->db = $db ?? $model->db;
        } else {
            $this->db = $db ?? \Config\Database::connect();
            $this->model = new FinanceModel($this->db);
        }
        $this->treasury = $treasury ?? new FinanceTreasuryService();
    }

    private function table(string $name): string
    {
        return $this->db->escapeIdentifiers($this->db->prefixTable($name));
    }

    private function hasTable(string $table): bool
    {
        return $this->tableExistsCache[$table] ??= $this->db->tableExists($table);
    }

    private function begin(): void
    {
        if ((int) $this->db->transDepth !== 0 || $this->db->transStatus() === false || !$this->db->transBegin()) {
            throw new \RuntimeException('Unable to start finance transaction.');
        }
    }

    private function commit(): void
    {
        if ($this->db->transStatus() === false || !$this->db->transCommit()) {
            throw new \RuntimeException('Unable to commit finance transaction.');
        }
    }

    private function sameValue(string $field, mixed $new, mixed $old): bool
    {
        $numeric = ['amount', 'discount_amount', 'tax_percentage', 'amount_original', 'exchange_rate',
            'tax_amount', 'total_amount', 'amount_paid', 'balance_due', 'id', 'clinic_id', 'created_by',
            'patient_id', 'examination_id', 'supplier_id', 'source_entity_id'];
        if ($new === null || $new === '') {
            return $old === null || $old === '';
        }
        if (in_array($field, $numeric, true)) {
            return is_numeric($new) && is_numeric($old) && is_finite((float) $new)
                && (float) $new === (float) $old;
        }
        return (string) $new === (string) ($old ?? '');
    }

    /** Override only in isolated fault-injection tests. */
    protected function recordAudit(int $clinicId, int $actorId, int $id, array $existing, array $finalRow): void
    {
        $auditSuccess = $this->db->table('activity_logs')->insert([
                'clinic_id'   => $clinicId,
                'user_id'     => $actorId,
                'action'      => 'update',
                'entity_type' => 'finance',
                'entity_id'   => $id,
                'description' => $this->internalNote !== null ? 'Internal invoice note: ' . $this->internalNote : 'Finance record updated; history preserved.',
                'metadata'    => json_encode([
                    'internal_note' => $this->internalNote,
                    'finance_before' => $existing,
                    'finance_after'  => $finalRow,
                ], JSON_THROW_ON_ERROR),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

        if (!$auditSuccess) {
                throw new \RuntimeException('Unable to record finance update audit log.');
            }

    }

    public function updateFinance(int $clinicId, int $id, array $payload, ?int $actorId = null, bool $browserContext = false): array
    {
        $this->internalNote = null;
        if ($clinicId <= 0) {
            throw new FinanceForbiddenException('Tenant context required.');
        }

        $actorId = $actorId ?? (int) (ApiRuntimeContext::getUserId() ?? 0);
        if ($actorId <= 0) {
            throw new FinanceUnauthorizedException('Authentication required.');
        }

        if ($id <= 0) {
            throw new FinanceValidationException(['id' => 'Invalid transaction ID.']);
        }

        if ((!$browserContext && (ApiRuntimeContext::getClinicId() !== $clinicId || ApiRuntimeContext::getUserId() !== $actorId))
            || ($browserContext && (ApiRuntimeContext::getClinicId() !== null || ApiRuntimeContext::getUserId() !== null
                || (int) session()->get('active_clinic_id') !== $clinicId || (int) session()->get('user_id') !== $actorId))) {
            throw new FinanceForbiddenException('Request context does not match the active actor and clinic.');
        }
        foreach ($payload as $field => $value) {
            if (!is_string($field) || is_bool($value) || ($value !== null && !is_string($value) && !is_int($value) && !is_float($value))) {
                throw new FinanceValidationException(['payload' => 'Fields must contain scalar values or null.']);
            }
        }
        if (array_key_exists('internal_note', $payload)) {
            $this->internalNote = InvoiceLifecyclePolicy::validateInternalNote($payload['internal_note']);
            unset($payload['internal_note']);
        }
        $unknownFields = array_diff(array_keys($payload), self::ALLOWED_PAYLOAD_FIELDS);
        if (!empty($unknownFields)) {
            throw new FinanceValidationException([
                'unknown_fields' => 'Unknown fields provided: ' . implode(', ', $unknownFields)
            ]);
        }

        $this->begin();
        try {
            $existing = $this->db->query(
                'SELECT * FROM ' . $this->table('finances') . ' WHERE clinic_id = ? AND id = ? FOR UPDATE',
                [$clinicId, $id]
            )->getRowArray();

            if (!$existing) {
                throw new FinanceNotFoundException('Invoice not found');
            }

            (new InvoiceReminderService($this->db))->assertNoProcessingForMutation($clinicId, $id);

            // Preserve explicit FX inputs before removing identical roundtrip fields.
            $fxInput = array_intersect_key($payload, array_flip(['amount', 'currency', 'amount_original', 'exchange_rate']));

            $forbidden = array_merge(self::STRICT_IMMUTABLE_FIELDS, self::COMPUTED_FIELDS, ['updated_at']);
            foreach ($payload as $field => $value) {
                if (in_array($field, $forbidden, true)) {
                    if (!$this->sameValue($field, $value, $existing[$field] ?? null)) {
                        throw new FinanceConflictException('Field ' . $field . ' cannot be changed directly. Use the existing correction or settlement workflow.');
                    }
                    unset($payload[$field]);
                } elseif ($this->sameValue($field, $value, $existing[$field] ?? null)) {
                    // Never revalue historical records for identical full-object roundtrips.
                    unset($payload[$field]);
                }
            }
            if ($payload === []) {
                if ($this->internalNote !== null) { $this->recordAudit($clinicId, $actorId, $id, $existing, $existing); }
                $this->commit();
                return $existing;
            }

            InvoiceLifecyclePolicy::assertCanUpdate($existing, array_keys($payload));

            $historyFields = self::HISTORY_SENSITIVE_FIELDS;
            if (in_array((string) ($existing['transaction_type'] ?? ''), ['payment', 'refund'], true)) {
                $historyFields = array_diff($historyFields, self::NON_EXPENSE_METADATA_FIELDS);
            }
            $isHistorySensitiveChange = array_intersect(array_keys($payload), $historyFields) !== [];

            if ($isHistorySensitiveChange) {
                (new ExpenseHistoryGuard($this->db))->assertChangedFieldsAllowed($clinicId, $existing, $payload);
            }

            if ($isHistorySensitiveChange) {
            // 4. Validate patient, examination, supplier references
            $type = (string) ($existing['transaction_type'] ?? '');
            $targetPatientId = array_key_exists('patient_id', $payload)
                ? ($payload['patient_id'] !== '' && $payload['patient_id'] !== null ? $payload['patient_id'] : null)
                : ($existing['patient_id'] ?? null);

            if ($type !== 'expense') {
                if ($targetPatientId === null || !is_scalar($targetPatientId) || !preg_match('/^[1-9][0-9]*$/D', (string) $targetPatientId)) {
                    throw new FinanceValidationException(['patient_id' => 'Patient is required.']);
                }
                $targetPatientId = (int) $targetPatientId;
                if ($this->hasTable('patients')) {
                    $patient = $this->db->query(
                        'SELECT id FROM ' . $this->table('patients') . ' WHERE clinic_id = ? AND id = ? FOR UPDATE',
                        [$clinicId, $targetPatientId]
                    )->getRowArray();
                    if (!$patient) {
                        throw new FinanceValidationException(['patient_id' => 'Patient does not belong to the active clinic.']);
                    }
                }
            } else {
                if ($targetPatientId !== null) {
                    if (!is_scalar($targetPatientId) || !preg_match('/^[1-9][0-9]*$/D', (string) $targetPatientId)) {
                        throw new FinanceValidationException(['patient_id' => 'Invalid patient selection.']);
                    }
                    $targetPatientId = (int) $targetPatientId;
                    if ($this->hasTable('patients')) {
                        $patient = $this->db->query(
                            'SELECT id FROM ' . $this->table('patients') . ' WHERE clinic_id = ? AND id = ? FOR UPDATE',
                            [$clinicId, $targetPatientId]
                        )->getRowArray();
                        if (!$patient) {
                            throw new FinanceValidationException(['patient_id' => 'Patient does not belong to the active clinic.']);
                        }
                    }
                }
            }

            $targetExamId = array_key_exists('examination_id', $payload)
                ? ($payload['examination_id'] !== '' && $payload['examination_id'] !== null ? $payload['examination_id'] : null)
                : ($existing['examination_id'] ?? null);

            if ($targetExamId !== null) {
                if (!is_scalar($targetExamId) || !preg_match('/^[1-9][0-9]*$/D', (string) $targetExamId)) {
                    throw new FinanceValidationException(['examination_id' => 'Invalid examination selection.']);
                }
                $targetExamId = (int) $targetExamId;
                if ($this->hasTable('examinations')) {
                    $exam = $this->db->query(
                        'SELECT id, patient_id FROM ' . $this->table('examinations') . ' WHERE clinic_id = ? AND id = ? FOR UPDATE',
                        [$clinicId, $targetExamId]
                    )->getRowArray();
                    if (!$exam) {
                        throw new FinanceValidationException(['examination_id' => 'Examination does not belong to the active clinic.']);
                    }
                    if ($targetPatientId !== null && (int) ($exam['patient_id'] ?? 0) !== $targetPatientId) {
                        throw new FinanceValidationException(['examination_id' => 'Selected examination does not belong to the selected patient.']);
                    }
                }
            }

            if (array_key_exists('supplier_id', $payload) && $payload['supplier_id'] !== '' && $payload['supplier_id'] !== null) {
                if (!is_scalar($payload['supplier_id']) || !preg_match('/^[1-9][0-9]*$/D', (string) $payload['supplier_id'])) {
                    throw new FinanceValidationException(['supplier_id' => 'Invalid supplier selection.']);
                }
                $targetSupplierId = (int) $payload['supplier_id'];
                if ($this->hasTable('suppliers')) {
                    $supplier = $this->db->query(
                        'SELECT id FROM ' . $this->table('suppliers') . ' WHERE clinic_id = ? AND id = ? FOR UPDATE',
                        [$clinicId, $targetSupplierId]
                    )->getRowArray();
                    if (!$supplier) {
                        throw new FinanceValidationException(['supplier_id' => 'Supplier does not belong to the active clinic.']);
                    }
                }
            }

            }

            $type = (string) ($existing['transaction_type'] ?? '');
            if (array_intersect(array_keys($payload), ['amount', 'currency', 'amount_original', 'exchange_rate']) !== []) {
            // 5. FX, currencies, and amounts
            $currentCurrency = (string) ($existing['currency'] ?? 'ILS');
            $currentRate = (float) ($existing['exchange_rate'] ?? 1.0);
            $currentAmountOriginal = (float) ($existing['amount_original'] ?? ($existing['amount'] ?? 0));

            $hasCurrency = array_key_exists('currency', $fxInput);
            $hasAmountOriginal = array_key_exists('amount_original', $fxInput);
            $hasExchangeRate = array_key_exists('exchange_rate', $fxInput);
            $hasAmount = array_key_exists('amount', $fxInput);

            if ($hasCurrency) {
                $newCurrency = (string) $fxInput['currency'];
                if (!in_array($newCurrency, ['USD', 'EUR', 'GBP', 'ILS', 'JOD'], true)) {
                    throw new FinanceValidationException(['currency' => 'Please select a valid currency.']);
                }
                if ($newCurrency !== $currentCurrency) {
                    if (!$hasAmountOriginal || !$hasExchangeRate) {
                        throw new FinanceValidationException([
                            'currency' => 'Changing currency requires specifying amount_original and exchange_rate.'
                        ]);
                    }
                }
            }

            if ($hasAmountOriginal) {
                if (!is_numeric($fxInput['amount_original']) || !is_finite((float) $fxInput['amount_original']) || (float) $fxInput['amount_original'] <= 0) {
                    throw new FinanceValidationException(['amount_original' => 'Amount must be a finite positive number.']);
                }
            }

            if ($hasExchangeRate) {
                if (!is_numeric($fxInput['exchange_rate']) || !is_finite((float) $fxInput['exchange_rate']) || (float) $fxInput['exchange_rate'] <= 0) {
                    throw new FinanceValidationException(['exchange_rate' => 'Exchange rate must be a finite positive number.']);
                }
            }

            if ($hasAmount) {
                if (!is_numeric($fxInput['amount']) || !is_finite((float) $fxInput['amount']) || (float) $fxInput['amount'] <= 0) {
                    throw new FinanceValidationException(['amount' => 'Amount must be a finite positive number.']);
                }
            }

            $effRate = $hasExchangeRate ? round((float) $fxInput['exchange_rate'], 6) : $currentRate;
            $effAmountOrig = $hasAmountOriginal ? round((float) $fxInput['amount_original'], 2) : $currentAmountOriginal;

            if ($effRate <= 0 || !is_finite($effRate) || $effAmountOrig <= 0 || !is_finite($effAmountOrig)) {
                throw new FinanceValidationException(['amount' => 'Amount and exchange rate must be finite positive numbers.']);
            }

            if ($hasAmountOriginal || $hasExchangeRate) {
                $derivedAmount = round($effAmountOrig * $effRate, 2);
                if ($hasAmount) {
                    $explicitAmount = round((float) $fxInput['amount'], 2);
                    if ($explicitAmount !== $derivedAmount) {
                        throw new FinanceValidationException([
                            'amount' => 'Explicit amount conflicts with derived amount from original amount and exchange rate.'
                        ]);
                    }
                }
                $payload['amount'] = $derivedAmount;
                $payload['amount_original'] = $effAmountOrig;
                $payload['exchange_rate'] = $effRate;
            } elseif ($hasAmount) {
                $newAmount = round((float) $fxInput['amount'], 2);
                $payload['amount'] = $newAmount;
                if ($effRate > 0) {
                    $payload['amount_original'] = round($newAmount / $effRate, 2);
                } else {
                    $payload['amount_original'] = $newAmount;
                    $payload['exchange_rate'] = 1.0;
                }
            }

            }

            // 6. Discounts & taxes
            $effAmount = array_key_exists('amount', $payload) ? (float) $payload['amount'] : (float) ($existing['amount'] ?? 0);

            if (array_key_exists('discount_amount', $payload)) {
                if ($payload['discount_amount'] !== null && $payload['discount_amount'] !== '') {
                    if (!is_numeric($payload['discount_amount']) || !is_finite((float) $payload['discount_amount']) || (float) $payload['discount_amount'] < 0) {
                        throw new FinanceValidationException(['discount_amount' => 'Discount amount must be a non-negative finite number.']);
                    }
                    $disc = round((float) $payload['discount_amount'], 2);
                    if ($disc > $effAmount) {
                        throw new FinanceValidationException(['discount_amount' => 'Discount amount cannot exceed amount.']);
                    }
                    $payload['discount_amount'] = $disc;
                } else {
                    $payload['discount_amount'] = 0;
                }
            }

            if (array_key_exists('tax_percentage', $payload)) {
                if ($payload['tax_percentage'] !== null && $payload['tax_percentage'] !== '') {
                    if (!is_numeric($payload['tax_percentage']) || !is_finite((float) $payload['tax_percentage']) || (float) $payload['tax_percentage'] < 0 || (float) $payload['tax_percentage'] > 100) {
                        throw new FinanceValidationException(['tax_percentage' => 'Tax percentage must be a finite number between 0 and 100.']);
                    }
                    $payload['tax_percentage'] = round((float) $payload['tax_percentage'], 2);
                } else {
                    $payload['tax_percentage'] = 0;
                }
            }

            // 7. Payment method & liquidity account
            if (array_key_exists('payment_method', $payload)) {
                if (!in_array($payload['payment_method'], ['cash', 'card', 'bank_transfer', 'check', 'insurance', 'other'], true)) {
                    throw new FinanceValidationException(['payment_method' => 'Please select a valid payment method.']);
                }
            }

            if (array_intersect(array_keys($payload), ['payment_method', 'liquidity_account']) !== []) {
                $method = $payload['payment_method'] ?? ($existing['payment_method'] ?? 'cash');
                $account = $payload['liquidity_account'] ?? ($existing['liquidity_account'] ?? '');
                $direction = ($type === 'expense' || $type === 'refund') ? 'out' : 'in';
                if ($account === '') {
                    $account = $this->treasury->defaultLiquidityAccount($method, $direction, $type);
                }
                if (!$this->treasury->isCompatibleLiquidityAccount($method, $direction, (string) $account, $type)) {
                    throw new FinanceValidationException(['liquidity_account' => 'Selected account does not match the payment method.']);
                }
                $payload['liquidity_account'] = $account;
            }

            // 8. Dates and enums
            if (array_key_exists('transaction_date', $payload) && $payload['transaction_date'] !== null && $payload['transaction_date'] !== '') {
                $d = (string) $payload['transaction_date'];
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $d) || !checkdate((int) substr($d, 5, 2), (int) substr($d, 8, 2), (int) substr($d, 0, 4))) {
                    throw new FinanceValidationException(['transaction_date' => 'Please enter a valid transaction date.']);
                }
            }

            if (array_key_exists('due_date', $payload) && $payload['due_date'] !== null && $payload['due_date'] !== '') {
                $d = (string) $payload['due_date'];
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $d) || !checkdate((int) substr($d, 5, 2), (int) substr($d, 8, 2), (int) substr($d, 0, 4))) {
                    throw new FinanceValidationException(['due_date' => 'Please enter a valid due date.']);
                }
            }

            if (array_key_exists('service_type', $payload) && $payload['service_type'] !== null && $payload['service_type'] !== '') {
                if (!in_array($payload['service_type'], ['consultation', 'treatment', 'medication', 'procedure', 'other'], true)) {
                    throw new FinanceValidationException(['service_type' => 'Please select a valid service type.']);
                }
            }

            if (array_key_exists('expense_category', $payload) && $payload['expense_category'] !== null && $payload['expense_category'] !== '') {
                $allowedCats = ['rent', 'utilities', 'salaries', 'laboratory', 'supplies', 'equipment', 'maintenance', 'software', 'marketing', 'taxes', 'fees', 'inventory_purchase', 'inventory_cogs', 'miscellaneous'];
                if (!in_array($payload['expense_category'], $allowedCats, true)) {
                    throw new FinanceValidationException(['expense_category' => 'Please select a valid expense category.']);
                }
            }

            // 9. Scalar validation for textual fields
            foreach (['description', 'notes', 'counterparty_name', 'service_details'] as $strField) {
                if (array_key_exists($strField, $payload)) {
                    if ($payload[$strField] !== null && !is_scalar($payload[$strField])) {
                        throw new FinanceValidationException([$strField => "Field '{$strField}' must be a scalar string or null."]);
                    }
                }
            }

            // 10. Prepare clean update payload
            $forbiddenUpdateKeys = array_merge(self::STRICT_IMMUTABLE_FIELDS, self::COMPUTED_FIELDS, ['updated_at']);
            $cleanUpdate = [];
            foreach ($payload as $k => $v) {
                if (!in_array($k, $forbiddenUpdateKeys, true)) {
                    $cleanUpdate[$k] = $v;
                }
            }

            if (empty($cleanUpdate)) {
                $cleanUpdate = ['updated_at' => date('Y-m-d H:i:s')];
            }

            if (!$this->model->where('clinic_id', $clinicId)->update($id, $cleanUpdate)) {
                $errors = $this->model->errors();
                throw new FinanceValidationException($errors !== [] ? $errors : 'Validation failed.');
            }

            $updatedRow = $this->db->query(
                'SELECT * FROM ' . $this->table('finances') . ' WHERE clinic_id = ? AND id = ? FOR UPDATE',
                [$clinicId, $id]
            )->getRowArray();

            if (!$updatedRow) {
                throw new FinanceNotFoundException('Invoice not found after update.');
            }

            if (array_intersect(array_keys($cleanUpdate), ['amount', 'discount_amount', 'tax_percentage']) !== []) {
                $newTotal = round((float) ($updatedRow['total_amount'] ?? 0), 2);
                if (!$this->db->table('finances')->where('clinic_id', $clinicId)->where('id', $id)
                    ->update(['balance_due' => $newTotal])) {
                    throw new \RuntimeException('Unable to persist outstanding balance.');
                }
            }

            $finalRow = $this->db->query(
                'SELECT * FROM ' . $this->table('finances') . ' WHERE clinic_id = ? AND id = ? FOR UPDATE',
                [$clinicId, $id]
            )->getRowArray();

            $this->recordAudit($clinicId, $actorId, $id, $existing, $finalRow);

            $this->commit();
            return $finalRow;
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
