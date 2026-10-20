<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\ApiRuntimeContext;

class FinanceModel extends TenantAwareModel
{
    protected $table = 'finances';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'patient_id',
        'examination_id',
        'transaction_id',
        'transaction_type',
        'amount',
        'currency',
        'payment_method',
        'liquidity_account',
        'payment_status',
        'description',
        'service_type',
        'expense_category',
        'counterparty_name',
        'supplier_id',
        'source_entity_type',
        'source_entity_id',
        'service_details',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total_amount',
        'amount_original',
        'exchange_rate',
        'amount_paid',
        'balance_due',
        'due_date',
        'paid_date',
        'transaction_date',
        'notes',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'patient_id' => 'permit_empty|integer',
        'transaction_type' => 'required|in_list[payment,invoice,refund,adjustment,expense]',
        'amount' => 'required|decimal|greater_than[0]',
        'currency' => 'in_list[USD,EUR,GBP,ILS,JOD]',
        'payment_method' => 'required|in_list[cash,card,bank_transfer,check,insurance,other]',
        'liquidity_account' => 'permit_empty|in_list[safe_cash,bank,undeposited_checks]',
        'payment_status' => 'required|in_list[pending,paid,partial,overdue,cancelled]',
        'service_type' => 'permit_empty|in_list[consultation,treatment,medication,procedure,other]',
        'expense_category' => 'permit_empty|in_list[rent,utilities,salaries,laboratory,supplies,equipment,maintenance,software,marketing,taxes,fees,inventory_purchase,inventory_cogs,miscellaneous]',
        'counterparty_name' => 'permit_empty|max_length[150]',
        'supplier_id' => 'permit_empty|integer',
        'source_entity_type' => 'permit_empty|max_length[50]',
        'source_entity_id' => 'permit_empty|integer',
        'paid_date' => 'permit_empty|valid_date',
        'transaction_date' => 'permit_empty|valid_date',
    ];

    protected $validationMessages = [
        'patient_id' => [
            'required' => 'Patient is required',
            'integer' => 'Invalid patient selection'
        ],
        'transaction_type' => [
            'required' => 'Transaction type is required',
            'in_list' => 'Please select a valid transaction type'
        ],
        'amount' => [
            'required' => 'Amount is required',
            'decimal' => 'Amount must be a valid decimal number',
            'greater_than' => 'Amount must be greater than 0'
        ],
        'currency' => [
            'required' => 'Currency is required',
            'in_list' => 'Please select a valid currency'
        ],
        'payment_method' => [
            'required' => 'Payment method is required',
            'in_list' => 'Please select a valid payment method'
        ],
        'payment_status' => [
            'required' => 'Payment status is required',
            'in_list' => 'Please select a valid payment status'
        ],
        'service_type' => [
            'required' => 'Service type is required',
            'in_list' => 'Please select a valid service type'
        ],
        'expense_category' => [
            'in_list' => 'Please select a valid expense category',
        ],
        'paid_date' => [
            'valid_date' => 'Please enter a valid date'
        ],
        'transaction_date' => [
            'valid_date' => 'Please enter a valid transaction date'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $beforeInsert = ['generateTransactionId', 'calculateTotals', 'setClinicId', 'lockFinanceCreationIdentity'];
    protected $beforeUpdate = ['enforceTenantScope', 'protectInvoiceDocument', 'protectExpenseHistory', 'calculateTotals'];
    protected $beforeDelete = ['enforceTenantScope', 'preventUnmanagedFinanceDeletion'];
    protected $beforeInsertBatch = ['preventUnmanagedFinanceBatchMutation'];
    protected $beforeUpdateBatch = ['preventUnmanagedFinanceBatchMutation'];

    protected function preventUnmanagedFinanceBatchMutation(array $data): array
    {
        throw new \DomainException('Finance batch writes bypass per-record parent and history guards and are unsupported.');
    }

    /** Physical deletion is exclusively performed by the audited, locked mutation service. */
    protected function preventUnmanagedFinanceDeletion(array $data): array
    {
        throw new \DomainException('Direct finance model deletion is unsupported. Use the audited financial mutation service.');
    }

    protected function lockFinanceCreationIdentity(array $data): array
    {
        $row = $data['data'] ?? [];
        if (($row['transaction_type'] ?? '') === 'invoice') {
            return $this->lockInvoiceCreationIdentity($data);
        }
        $data['data'] = (new \App\Services\FinanceCreationGuard($this->db))->validateAndLock(
            $row,
            $this->resolveActiveClinicId(),
            false
        );
        return $data;
    }

    protected function lockInvoiceCreationIdentity(array $data): array
    {
        $row = $data['data'] ?? [];
        if (($row['transaction_type'] ?? '') !== 'invoice') { return $data; }
        $data['data'] = (new \App\Services\FinanceCreationGuard($this->db))->validateAndLock(
            $row,
            $this->resolveActiveClinicId(),
            true
        );
        return $data;
    }

    protected function protectExpenseHistory(array $data): array
    {
        $sensitive = \App\Services\InvoiceLifecyclePolicy::EXPENSE_HISTORY_SENSITIVE_FIELDS;
        $touched = array_intersect(array_keys($data['data'] ?? []), $sensitive);
        if ($touched === []) {
            return $data;
        }

        $clinicId = $this->resolveActiveClinicId();

        if ((int) $this->db->transDepth < 1 || !$this->db->transStatus()) {
            throw new \DomainException('Expense history edits require an active transaction.');
        }

        $ids = $data['id'] ?? null;
        $ids = is_array($ids) ? $ids : [$ids];
        if (count($ids) !== 1 || (!is_int(reset($ids)) && !is_string(reset($ids)))
            || !preg_match('/^[1-9][0-9]*$/D', (string) reset($ids))
            || strlen((string) reset($ids)) > 10 || (float) reset($ids) > 2147483647) {
            throw new \DomainException('Explicit single expense target is required.');
        }
        $id = (int) reset($ids);

        $row = $this->db->query(
            'SELECT * FROM ' . $this->db->escapeIdentifiers($this->db->prefixTable('finances'))
            . ' WHERE clinic_id = ? AND id = ? FOR UPDATE',
            [$clinicId, $id]
        )->getRowArray();

        if (!$row) {
            throw new \DomainException('Expense history target does not belong to the active clinic.');
        }
        if (($row['transaction_type'] ?? '') !== 'expense') {
            return $data;
        }

        (new \App\Services\ExpenseHistoryGuard($this->db))->assertChangedFieldsAllowed($clinicId, $row, $data['data']);

        // An unchanged form must not recompute legacy totals from historical basis.
        $basisFields = ['amount', 'discount_amount', 'tax_percentage'];
        $basisChanged = false;
        foreach ($basisFields as $field) {
            if (array_key_exists($field, $data['data'])
                && !\App\Services\InvoiceLifecyclePolicy::sameValue($field, $data['data'][$field], $row[$field] ?? null)) {
                $basisChanged = true;
            }
        }
        if (!$basisChanged) {
            foreach (array_merge($basisFields, ['tax_amount', 'total_amount']) as $field) {
                unset($data['data'][$field]);
            }
        }
        return $data;
    }

    protected function protectInvoiceDocument(array $data): array
    {
        $protected = array_merge(\App\Services\InvoiceLifecyclePolicy::STRICT_IMMUTABLE_FIELDS,
            \App\Services\InvoiceLifecyclePolicy::INVOICE_PROTECTED_BASIS_FIELDS, ['tax_amount', 'total_amount']);
        $touched = array_intersect(array_keys($data['data'] ?? []), $protected);
        if ($touched === []) { return $data; }
        $ids = $data['id'] ?? null;
        $ids = is_array($ids) ? $ids : [$ids];
        if ($ids === [] || count($ids) > 500) { throw new \DomainException('Explicit invoice targets are required.'); }
        foreach ($ids as $id) {
            if ((!is_int($id) && !is_string($id)) || !preg_match('/^[1-9][0-9]*$/D', (string) $id)) { throw new \DomainException('Invalid invoice target.'); }
        }
        $ids = array_values(array_unique(array_map('intval', $ids)));
        sort($ids, SORT_NUMERIC);
        $clinicId = $this->resolveActiveClinicId();
        $invoiceSeen = false;
        foreach ($ids as $id) {
            $row = $this->db->query('SELECT * FROM ' . $this->db->escapeIdentifiers($this->db->prefixTable('finances'))
                . ' WHERE id = ? FOR UPDATE', [$id])->getRowArray();
            if (!$row) { continue; }
            if (($row['transaction_type'] ?? '') !== 'invoice') {
                if (($data['data']['transaction_type'] ?? '') === 'invoice') {
                    throw new \DomainException('New invoices must use the audited creation workflow.');
                }
                continue;
            }
            if ((int) $row['clinic_id'] !== $clinicId) {
                throw new \DomainException('Invoice document mutation does not match the active clinic.');
            }
            if ((int) $this->db->transDepth < 1 || !$this->db->transStatus()) { throw new \DomainException('Invoice document edits require an active transaction.'); }
            $invoiceSeen = true;
            foreach ($touched as $field) {
                if (!\App\Services\InvoiceLifecyclePolicy::sameValue($field, $data['data'][$field], $row[$field] ?? null)) {
                    throw new \DomainException('Persisted invoice document values are protected; issuance is not proven unissued.');
                }
            }
        }
        if ($invoiceSeen) {
            if (count($ids) !== 1) { throw new \DomainException('Invoice document mass updates are not supported.'); }
            // Identical roundtrips must not recompute historical monetary values.
            foreach ($touched as $field) { unset($data['data'][$field]); }
        }
        return $data;
    }

    private function documentDateExpr(string $alias = 'finances'): string
    {
        return "COALESCE({$alias}.transaction_date, {$alias}.paid_date, DATE({$alias}.created_at))";
    }

    private function resolveReportingClinicId($clinicId): int
    {
        // An explicit invalid ID fails closed; Bearer context takes precedence over cookies.
        return (int) ($clinicId ?? ApiRuntimeContext::getClinicId() ?? session()->get('active_clinic_id') ?? 0);
    }

    private function invoicePaymentReceiptsBuilder(int $clinicId, ?string $startDate = null, ?string $endDate = null)
    {
        // Settlement amount is already persisted in the clinic base currency.
        $builder = $this->db->table('finance_payments fp')
            ->join('finances f', 'f.id = fp.finance_id AND f.clinic_id = fp.clinic_id', 'inner')
            ->where('fp.clinic_id', $clinicId)
            ->where('f.transaction_type', 'invoice')
            ->whereIn('fp.type', ['payment', 'refund', 'void']);

        if ($startDate !== null && $startDate !== '') {
            $builder->where('fp.payment_date >=', $startDate);
        }
        if ($endDate !== null && $endDate !== '') {
            $builder->where('fp.payment_date <=', $endDate);
        }

        return $builder;
    }

    protected function generateTransactionId(array $data)
    {
        if (!isset($data['data']['transaction_id'])) {
            $type = (string) ($data['data']['transaction_type'] ?? 'txn');
            $prefix = strtoupper(substr($type, 0, 3));
            $data['data']['transaction_id'] = $prefix . date('YmdHis') . str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT);
        }
        return $data;
    }

    /**
     * Guards monetary basis inputs and calculates tax and total amounts.
     * Recomputes totals only when monetary basis (amount, discount_amount, tax_percentage) is touched.
     * Merges persisted original basis on partial updates, preserving legacy values on status-only updates.
     */
    protected function calculateTotals(array $data)
    {
        $hasAmount = array_key_exists('amount', $data['data'] ?? []);
        $hasDiscount = array_key_exists('discount_amount', $data['data'] ?? []);
        $hasTaxPercent = array_key_exists('tax_percentage', $data['data'] ?? []);

        // Status-only updates must preserve legacy amounts untouched
        if (!$hasAmount && !$hasDiscount && !$hasTaxPercent) {
            return $data;
        }

        $isUpdate = array_key_exists('id', $data);

        if (!$isUpdate) {
            // Insertion flow: basis values come from provided payload or standard defaults
            $rawAmount = $data['data']['amount'] ?? null;
            if ($rawAmount === null) {
                return $data;
            }
            $rawDiscount = $data['data']['discount_amount'] ?? 0;
            $rawTax = $data['data']['tax_percentage'] ?? 0;

            [$amount, $discount, $taxPercent, $taxAmount, $totalAmount] = $this->computeGuardedTotals($rawAmount, $rawDiscount, $rawTax);

            $data['data']['amount'] = $amount;
            if ($hasDiscount) {
                $data['data']['discount_amount'] = $discount;
            }
            if ($hasTaxPercent) {
                $data['data']['tax_percentage'] = $taxPercent;
            }
            $data['data']['tax_amount'] = $taxAmount;
            if ($this->hasTotalAmountField()) {
                $data['data']['total_amount'] = $totalAmount;
            }

            return $data;
        }

        // Update flow: target ID is required for monetary basis updates
        $rawId = $data['id'] ?? null;
        if ($rawId === null || $rawId === [] || $rawId === '') {
            throw new \InvalidArgumentException('Ambiguous mass update: target transaction ID is required to calculate totals.');
        }

        $targetIds = is_array($rawId) ? $rawId : [$rawId];
        $sanitizedIds = [];
        foreach ($targetIds as $tid) {
            if (!is_scalar($tid) || !preg_match('/^[1-9][0-9]*$/D', (string) $tid)) {
                throw new \InvalidArgumentException('Invalid target transaction ID for monetary update.');
            }
            $sanitizedIds[] = (int) $tid;
        }
        $sanitizedIds = array_values(array_unique($sanitizedIds));

        // If updating multiple rows, ambiguous mass recalculation must be rejected
        if (count($sanitizedIds) > 1) {
            $persistedRows = $this->fetchTargetRowsForTotals($sanitizedIds);
            if (count($persistedRows) !== count($sanitizedIds)) {
                throw new \InvalidArgumentException('One or more financial transactions not found for basis recalculation.');
            }
            $firstComputed = null;
            foreach ($persistedRows as $pRow) {
                $rAmount = $hasAmount ? $data['data']['amount'] : ($pRow['amount'] ?? 0);
                $rDiscount = $hasDiscount ? $data['data']['discount_amount'] : ($pRow['discount_amount'] ?? 0);
                $rTax = $hasTaxPercent ? $data['data']['tax_percentage'] : ($pRow['tax_percentage'] ?? 0);

                $computed = $this->computeGuardedTotals($rAmount, $rDiscount, $rTax);
                if ($firstComputed === null) {
                    $firstComputed = $computed;
                } elseif ($firstComputed[3] !== $computed[3] || $firstComputed[4] !== $computed[4]) {
                    throw new \InvalidArgumentException('Ambiguous mass update: updating monetary basis for multiple rows with different amounts is not supported.');
                }
            }

            $data['data']['tax_amount'] = $firstComputed[3];
            if ($this->hasTotalAmountField()) {
                $data['data']['total_amount'] = $firstComputed[4];
            }
            return $data;
        }

        // Single target update
        $targetId = $sanitizedIds[0];
        $persisted = null;
        if (!$hasAmount || !$hasDiscount || !$hasTaxPercent) {
            $persisted = $this->fetchTargetRowForTotals($targetId);
            if (!$persisted) {
                throw new \InvalidArgumentException('Financial transaction not found for basis recalculation.');
            }
        }

        $rawAmount = $hasAmount ? $data['data']['amount'] : ($persisted['amount'] ?? 0);
        $rawDiscount = $hasDiscount ? $data['data']['discount_amount'] : ($persisted['discount_amount'] ?? 0);
        $rawTax = $hasTaxPercent ? $data['data']['tax_percentage'] : ($persisted['tax_percentage'] ?? 0);

        [$amount, $discount, $taxPercent, $taxAmount, $totalAmount] = $this->computeGuardedTotals($rawAmount, $rawDiscount, $rawTax);

        if ($hasAmount) {
            $data['data']['amount'] = $amount;
        }
        if ($hasDiscount) {
            $data['data']['discount_amount'] = $discount;
        }
        if ($hasTaxPercent) {
            $data['data']['tax_percentage'] = $taxPercent;
        }
        $data['data']['tax_amount'] = $taxAmount;
        if ($this->hasTotalAmountField()) {
            $data['data']['total_amount'] = $totalAmount;
        }

        return $data;
    }

    /**
     * Validates and rounds monetary basis inputs and calculates tax and total amounts.
     * Rejects malformed/nonfinite/negative inputs, discounts exceeding amount, and invalid tax percentages.
     *
     * @return array{0: float, 1: float, 2: float, 3: float, 4: float}
     */
    public function computeGuardedTotals(mixed $rawAmount, mixed $rawDiscount, mixed $rawTax): array
    {
        if (!is_numeric($rawAmount) || !is_finite((float) $rawAmount) || (float) $rawAmount < 0) {
            throw new \InvalidArgumentException('Amount must be a non-negative finite number.');
        }

        if (!is_numeric($rawDiscount) || !is_finite((float) $rawDiscount) || (float) $rawDiscount < 0) {
            throw new \InvalidArgumentException('Discount amount must be a non-negative finite number.');
        }

        if (!is_numeric($rawTax) || !is_finite((float) $rawTax) || (float) $rawTax < 0 || (float) $rawTax > 100) {
            throw new \InvalidArgumentException('Tax percentage must be a finite number between 0 and 100.');
        }

        $amount = round((float) $rawAmount, 2);
        $discount = round((float) $rawDiscount, 2);
        $taxPercent = round((float) $rawTax, 2);

        if ($discount > $amount) {
            throw new \InvalidArgumentException('Discount amount cannot exceed amount.');
        }

        $taxableBase = round($amount - $discount, 2);
        $taxAmount = round($taxableBase * ($taxPercent / 100.0), 2);
        $totalAmount = round($taxableBase + $taxAmount, 2);

        return [$amount, $discount, $taxPercent, $taxAmount, $totalAmount];
    }

    public function hasTotalAmountField(): bool
    {
        try {
            return $this->db->fieldExists('total_amount', 'finances');
        } catch (\Throwable $e) {
            return true;
        }
    }

    protected function resolveActiveClinicId(): int
    {
        $clinicId = \App\Libraries\ApiRuntimeContext::getClinicId();
        if (!$clinicId && function_exists('session')) {
            try {
                $clinicId = session()->get('active_clinic_id');
            } catch (\Throwable $e) {
                $clinicId = null;
            }
        }
        if (!$clinicId) {
            log_message('error', "TENANT_CONTEXT_MISSING: Active clinic is required for tenant-scoped operations on {$this->table}.");
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Tenant context required.");
        }
        return (int) $clinicId;
    }

    protected function fetchTargetRowForTotals(int $id): ?array
    {
        try {
            $clinicId = $this->resolveActiveClinicId();
            return $this->db->table($this->table)
                ->select('id, amount, discount_amount, tax_percentage, tax_amount, total_amount')
                ->where('clinic_id', $clinicId)
                ->where('id', $id)
                ->get()
                ->getRowArray();
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function fetchTargetRowsForTotals(array $ids): array
    {
        try {
            $clinicId = $this->resolveActiveClinicId();
            return $this->db->table($this->table)
                ->select('id, amount, discount_amount, tax_percentage, tax_amount, total_amount')
                ->where('clinic_id', $clinicId)
                ->whereIn('id', $ids)
                ->get()
                ->getResultArray();
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get finances by clinic for DataTables
     */
    public function getFinancesByClinic($clinicId, $limit = 10, $offset = 0, $search = '', $orderColumn = 'finances.id', $orderDir = 'desc')
    {
        $builder = $this->select('finances.*, patients.first_name, patients.last_name, patients.phone, patients.gender, patients.patient_id as patient_number')
            ->join('patients', 'patients.id = finances.patient_id AND patients.clinic_id = finances.clinic_id', 'left')
            ->where('finances.clinic_id', $clinicId);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('finances.id', $search)
                ->orLike('patients.first_name', $search)
                ->orLike('patients.last_name', $search)
                ->orLike('patients.phone', $search)
                ->orLike('finances.description', $search)
                ->orLike('finances.transaction_type', $search)
                ->orLike('finances.payment_status', $search)
                ->groupEnd();
        }

        return $builder->orderBy($orderColumn, $orderDir)
            ->limit($limit, $offset)
            ->find();
    }

    /**
     * Count finances by clinic for DataTables
     */
    public function countFinancesByClinic($clinicId, $search = '')
    {
        $builder = $this->select('finances.id')
            ->join('patients', 'patients.id = finances.patient_id AND patients.clinic_id = finances.clinic_id', 'left')
            ->where('finances.clinic_id', $clinicId);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('finances.id', $search)
                ->orLike('patients.first_name', $search)
                ->orLike('patients.last_name', $search)
                ->orLike('patients.phone', $search)
                ->orLike('finances.description', $search)
                ->orLike('finances.transaction_type', $search)
                ->orLike('finances.payment_status', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function getFinanceStatsByClinic($clinicId, $startDate = null, $endDate = null)
    {
        try {
            $clinicId = (int) $clinicId;
            if ($clinicId <= 0) {
                return [
                    'total_revenue' => 0,
                    'total_invoices' => 0,
                    'pending_payments' => 0,
                    'pending_amount' => 0,
                    'overdue_payments' => 0,
                    'monthly_revenue' => 0,
                    'outstanding_invoices' => 0,
                    'total_expenses' => 0,
                    'net_income' => 0,
                    'total_transactions' => 0,
                    'average_transaction' => 0,
                    'transaction_summary' => []
                ];
            }

            $builder = $this->db->table('finances')->where('clinic_id', $clinicId);

            if ($startDate) {
                $builder->where($this->documentDateExpr('finances') . ' >= ' . $this->db->escape($startDate), null, false);
            }
            if ($endDate) {
                $builder->where($this->documentDateExpr('finances') . ' <= ' . $this->db->escape($endDate), null, false);
            }

            $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';
            $balanceField = $this->db->fieldExists('balance_due', 'finances') ? 'balance_due' : '(' . $sumField . ' - COALESCE(amount_paid, 0))';

            $standalonePayments = (clone $builder)->selectSum($sumField, 'total_amount')->where('transaction_type', 'payment')->get()->getRow()->total_amount ?? 0;
            $totalInvoices = (clone $builder)->selectSum($sumField, 'total_amount')->where('transaction_type', 'invoice')->get()->getRow()->total_amount ?? 0;
            $refunds = (clone $builder)->selectSum($sumField, 'total_amount')->where('transaction_type', 'refund')->get()->getRow()->total_amount ?? 0;
            $totalTransactions = (clone $builder)->countAllResults();

            $invoicePayments = $this->invoicePaymentReceiptsBuilder($clinicId, $startDate, $endDate)
                ->select("SUM(CASE WHEN fp.type = 'payment' THEN fp.amount WHEN fp.type IN ('refund','void') THEN -fp.amount ELSE 0 END) AS total_amount", false)
                ->get()
                ->getRow()
                ->total_amount ?? 0;

            $pendingPayments = (clone $builder)
                ->selectSum($balanceField, 'total_amount')
                ->where('transaction_type', 'invoice')
                ->whereIn('payment_status', ['pending', 'partial', 'overdue'])
                ->get()
                ->getRow()
                ->total_amount ?? 0;

            $overduePayments = (clone $builder)
                ->selectSum($balanceField, 'total_amount')
                ->where('transaction_type', 'invoice')
                ->where('due_date <', date('Y-m-d'))
                ->where($balanceField . ' >', 0, false)
                ->get()
                ->getRow()
                ->total_amount ?? 0;

            $outstandingInvoices = (clone $builder)
                ->where('transaction_type', 'invoice')
                ->where($balanceField . ' >', 0, false)
                ->countAllResults();

            $monthStart = date('Y-m-01');
            $monthEnd = date('Y-m-t');
            $monthlyStandalonePayments = $this->db->table('finances')
                ->selectSum($sumField, 'total_amount')
                ->where('clinic_id', $clinicId)
                ->where('transaction_type', 'payment')
                ->where($this->documentDateExpr('finances') . ' >= ' . $this->db->escape($monthStart), null, false)
                ->where($this->documentDateExpr('finances') . ' <= ' . $this->db->escape($monthEnd), null, false)
                ->get()
                ->getRow()
                ->total_amount ?? 0;
            $monthlyStandaloneRefunds = $this->db->table('finances')
                ->selectSum($sumField, 'total_amount')
                ->where('clinic_id', $clinicId)
                ->where('transaction_type', 'refund')
                ->where($this->documentDateExpr('finances') . ' >= ' . $this->db->escape($monthStart), null, false)
                ->where($this->documentDateExpr('finances') . ' <= ' . $this->db->escape($monthEnd), null, false)
                ->get()
                ->getRow()
                ->total_amount ?? 0;
            $monthlyInvoicePayments = $this->invoicePaymentReceiptsBuilder($clinicId, $monthStart, $monthEnd)
                ->select("SUM(CASE WHEN fp.type = 'payment' THEN fp.amount WHEN fp.type IN ('refund','void') THEN -fp.amount ELSE 0 END) AS total_amount", false)
                ->get()
                ->getRow()
                ->total_amount ?? 0;
            $monthlyRevenue = floatval($monthlyStandalonePayments) - floatval($monthlyStandaloneRefunds) + floatval($monthlyInvoicePayments);

            $summaryBuilder = $this->db->table('finances')
                ->select('transaction_type, COUNT(*) as count, SUM(' . $sumField . ') as total_amount')
                ->where('clinic_id', $clinicId);
            if ($startDate) {
                $summaryBuilder->where($this->documentDateExpr('finances') . ' >= ' . $this->db->escape($startDate), null, false);
            }
            if ($endDate) {
                $summaryBuilder->where($this->documentDateExpr('finances') . ' <= ' . $this->db->escape($endDate), null, false);
            }
            $summaryRows = $summaryBuilder->groupBy('transaction_type')->get()->getResultArray();
            $transactionSummary = [];
            foreach ($summaryRows as $row) {
                $count = intval($row['count'] ?? 0);
                $total = floatval($row['total_amount'] ?? 0);
                $transactionSummary[] = [
                    'transaction_type' => $row['transaction_type'],
                    'count' => $count,
                    'total_amount' => $total,
                    'average_amount' => $count > 0 ? ($total / $count) : 0,
                ];
            }

            // Signed cash expense settlements (payment - refund/void)
            $expenseSettlementsBuilder = $this->db->table('finance_payments fp')
                ->join('finances f', 'f.id = fp.finance_id AND f.clinic_id = fp.clinic_id', 'inner')
                ->where('fp.clinic_id', $clinicId)
                ->where('f.transaction_type', 'expense')
                ->whereIn('fp.type', ['payment', 'refund', 'void']);
            if ($startDate) {
                $expenseSettlementsBuilder->where('fp.payment_date >=', $startDate);
            }
            if ($endDate) {
                $expenseSettlementsBuilder->where('fp.payment_date <=', $endDate);
            }
            $expenseSettlements = $expenseSettlementsBuilder
                ->select("SUM(CASE WHEN fp.type = 'payment' THEN fp.amount WHEN fp.type IN ('refund', 'void') THEN -fp.amount ELSE 0 END) AS total_amount", false)
                ->get()
                ->getRow()
                ->total_amount ?? 0;

            // Legacy paid/partial expense fallback only if no ledger exists and category not inventory_cogs
            $legacyExpenseBuilder = $this->db->table('finances f')
                ->where('f.clinic_id', $clinicId)
                ->where('f.transaction_type', 'expense')
                ->groupStart()
                    ->where('f.expense_category', null)
                    ->orWhere('f.expense_category !=', 'inventory_cogs')
                ->groupEnd()
                ->where('COALESCE(f.amount_paid, 0) >', 0)
                ->where('NOT EXISTS (SELECT 1 FROM finance_payments fp WHERE fp.clinic_id = f.clinic_id AND fp.finance_id = f.id)', null, false);
            $expenseDateExpr = "COALESCE(f.paid_date, f.transaction_date, DATE(f.created_at))";
            if ($startDate) {
                $legacyExpenseBuilder->where($expenseDateExpr . ' >= ' . $this->db->escape($startDate), null, false);
            }
            if ($endDate) {
                $legacyExpenseBuilder->where($expenseDateExpr . ' <= ' . $this->db->escape($endDate), null, false);
            }
            $legacyExpensePaidExpr = "LEAST(" . $sumField . ", COALESCE(f.amount_paid, 0))";
            $legacyExpenses = $legacyExpenseBuilder
                ->select("SUM({$legacyExpensePaidExpr}) AS total_amount", false)
                ->get()
                ->getRow()
                ->total_amount ?? 0;

            $expenses = floatval($expenseSettlements) + floatval($legacyExpenses);
            $totalRevenue = floatval($standalonePayments) + floatval($invoicePayments) - floatval($refunds);
            $netIncome = $totalRevenue - floatval($expenses);
            $averageTransaction = $totalTransactions > 0 ? ($netIncome / $totalTransactions) : 0;

            return [
                'total_revenue' => $totalRevenue,
                'total_invoices' => floatval($totalInvoices),
                'pending_payments' => floatval($pendingPayments),
                // Legacy alias used by finance/reports view
                'pending_amount' => floatval($pendingPayments),
                'overdue_payments' => floatval($overduePayments),
                'monthly_revenue' => $monthlyRevenue,
                'outstanding_invoices' => intval($outstandingInvoices),
                'total_expenses' => floatval($expenses),
                'net_income' => $netIncome,
                'total_transactions' => intval($totalTransactions),
                'average_transaction' => $averageTransaction,
                'transaction_summary' => $transactionSummary
            ];
        } catch (\Exception $e) {
            log_message('error', 'Finance stats error: ' . $e->getMessage());
            return [
                'total_revenue' => 0,
                'total_invoices' => 0,
                'pending_payments' => 0,
                'pending_amount' => 0,
                'overdue_payments' => 0,
                'monthly_revenue' => 0,
                'outstanding_invoices' => 0,
                'total_expenses' => 0,
                'net_income' => 0,
                'total_transactions' => 0,
                'average_transaction' => 0,
                'transaction_summary' => []
            ];
        }
    }

    public function getMonthlyRevenueByClinic($clinicId, $year = null)
    {
        try {
            $clinicId = (int) $clinicId;
            if ($clinicId <= 0) {
                return [];
            }
            $year = $year ?? date('Y');
            $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';

            $dateExpr = $this->documentDateExpr('finances');
            $standalone = $this->select("MONTH({$dateExpr}) as month, SUM(" . $sumField . ') as total_amount', false)
                ->where('clinic_id', $clinicId)
                ->where("YEAR({$dateExpr})", $year, false)
                ->where('transaction_type', 'payment')
                ->groupBy("MONTH({$dateExpr})", false)
                ->orderBy('month', 'ASC')
                ->findAll();

            $invoicePayments = $this->invoicePaymentReceiptsBuilder($clinicId)
                ->select("MONTH(fp.payment_date) as month, SUM(CASE WHEN fp.type = 'payment' THEN fp.amount WHEN fp.type IN ('refund','void') THEN -fp.amount ELSE 0 END) as total_amount", false)
                ->where('YEAR(fp.payment_date)', $year)
                ->groupBy('MONTH(fp.payment_date)')
                ->orderBy('month', 'ASC')
                ->get()
                ->getResultArray();

            $refunds = $this->db->table('finances')
                ->select("MONTH({$dateExpr}) as month, SUM(" . $sumField . ') as total_amount', false)
                ->where('clinic_id', $clinicId)
                ->where("YEAR({$dateExpr})", $year, false)
                ->where('transaction_type', 'refund')
                ->groupBy("MONTH({$dateExpr})", false)
                ->orderBy('month', 'ASC')
                ->get()
                ->getResultArray();

            $merged = [];
            foreach ($standalone as $row) {
                $month = (int) $row['month'];
                $merged[$month] = ($merged[$month] ?? 0) + floatval($row['total_amount'] ?? 0);
            }
            foreach ($invoicePayments as $row) {
                $month = (int) $row['month'];
                $merged[$month] = ($merged[$month] ?? 0) + floatval($row['total_amount'] ?? 0);
            }
            foreach ($refunds as $row) {
                $month = (int) $row['month'];
                $merged[$month] = ($merged[$month] ?? 0) - floatval($row['total_amount'] ?? 0);
            }

            ksort($merged);
            $result = [];
            foreach ($merged as $month => $total) {
                $result[] = [
                    'month' => $month,
                    'total_amount' => $total,
                ];
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Monthly revenue error: ' . $e->getMessage());
            return [];
        }
    }

    public function getMonthlyRevenueByClinicRange(int $clinicId, string $startDate, string $endDate): array
    {
        try {
            $clinicId = (int) $clinicId;
            if ($clinicId <= 0) {
                return [];
            }
            $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';
            $dateExpr = $this->documentDateExpr('finances');

            $standalone = $this->db->table('finances')
                ->select("DATE_FORMAT({$dateExpr}, '%Y-%m') AS month_key, SUM({$sumField}) AS total_amount", false)
                ->where('clinic_id', $clinicId)
                ->where('transaction_type', 'payment')
                ->where($dateExpr . ' >= ' . $this->db->escape($startDate), null, false)
                ->where($dateExpr . ' <= ' . $this->db->escape($endDate), null, false)
                ->groupBy("DATE_FORMAT({$dateExpr}, '%Y-%m')", false)
                ->get()
                ->getResultArray();

            $invoicePayments = $this->invoicePaymentReceiptsBuilder($clinicId, $startDate, $endDate)
                ->select("DATE_FORMAT(fp.payment_date, '%Y-%m') AS month_key, SUM(CASE WHEN fp.type = 'payment' THEN fp.amount WHEN fp.type IN ('refund','void') THEN -fp.amount ELSE 0 END) AS total_amount", false)
                ->groupBy("DATE_FORMAT(fp.payment_date, '%Y-%m')", false)
                ->get()
                ->getResultArray();

            $refunds = $this->db->table('finances')
                ->select("DATE_FORMAT({$dateExpr}, '%Y-%m') AS month_key, SUM({$sumField}) AS total_amount", false)
                ->where('clinic_id', $clinicId)
                ->where('transaction_type', 'refund')
                ->where($dateExpr . ' >= ' . $this->db->escape($startDate), null, false)
                ->where($dateExpr . ' <= ' . $this->db->escape($endDate), null, false)
                ->groupBy("DATE_FORMAT({$dateExpr}, '%Y-%m')", false)
                ->get()
                ->getResultArray();

            $merged = [];
            foreach ($standalone as $row) {
                $key = (string) ($row['month_key'] ?? '');
                if ($key === '') {
                    continue;
                }
                $merged[$key] = ($merged[$key] ?? 0) + floatval($row['total_amount'] ?? 0);
            }
            foreach ($invoicePayments as $row) {
                $key = (string) ($row['month_key'] ?? '');
                if ($key === '') {
                    continue;
                }
                $merged[$key] = ($merged[$key] ?? 0) + floatval($row['total_amount'] ?? 0);
            }
            foreach ($refunds as $row) {
                $key = (string) ($row['month_key'] ?? '');
                if ($key === '') {
                    continue;
                }
                $merged[$key] = ($merged[$key] ?? 0) - floatval($row['total_amount'] ?? 0);
            }

            $start = new \DateTime(date('Y-m-01', strtotime($startDate)));
            $end = new \DateTime(date('Y-m-01', strtotime($endDate)));
            $end->modify('first day of next month');
            $period = new \DatePeriod($start, new \DateInterval('P1M'), $end);

            $result = [];
            foreach ($period as $month) {
                $key = $month->format('Y-m');
                $result[] = [
                    'month_key' => $key,
                    'month' => $month->format('M Y'),
                    'total_amount' => round(floatval($merged[$key] ?? 0), 2),
                ];
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Monthly revenue range error: ' . $e->getMessage());
            return [];
        }
    }

    public function getFinanceByPatient($patientId)
    {
        return $this->select('finances.*, patients.first_name, patients.last_name, patients.patient_id as patient_number')
            ->join('patients', 'patients.id = finances.patient_id')
            ->where('finances.patient_id', $patientId)
            ->where('patients.clinic_id = finances.clinic_id') // Join guard
            ->groupStart()
                ->where('finances.expense_category', null)
                ->orWhere('finances.expense_category !=', 'inventory_cogs')
            ->groupEnd()
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getFinanceByExamination($examinationId)
    {
        return $this->where('examination_id', $examinationId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getFinanceStats($startDate = null, $endDate = null, $clinicId = null)
    {
        try {
            $resolvedClinicId = $this->resolveReportingClinicId($clinicId);
            if ($resolvedClinicId <= 0) {
                log_message('warning', 'FinanceModel::getFinanceStats called without clinic context. Returning empty summary.');
                return [
                    'total_revenue' => 0,
                    'total_invoices' => 0,
                    'pending_payments' => 0,
                    'pending_amount' => 0,
                    'overdue_payments' => 0,
                    'total_expenses' => 0,
                    'net_income' => 0,
                    'total_transactions' => 0,
                    'average_transaction' => 0,
                    'transaction_summary' => []
                ];
            }
            return $this->getFinanceStatsByClinic($resolvedClinicId, $startDate, $endDate);
        } catch (\Exception $e) {
            log_message('error', 'Finance stats error: ' . $e->getMessage());
            return [
                'total_revenue' => 0,
                'total_invoices' => 0,
                'pending_payments' => 0,
                'pending_amount' => 0,
                'overdue_payments' => 0,
                'total_expenses' => 0,
                'net_income' => 0,
                'total_transactions' => 0,
                'average_transaction' => 0,
                'transaction_summary' => []
            ];
        }
    }

    public function getPaymentMethodsStats($startDate = null, $endDate = null, $clinicId = null)
    {
        $clinicId = $this->resolveReportingClinicId($clinicId);
        if ($clinicId <= 0) {
            return [];
        }

        $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';

        $standaloneBuilder = $this->db->table('finances')
            ->select("payment_method, SUM(CASE WHEN transaction_type = 'payment' THEN {$sumField} WHEN transaction_type = 'refund' THEN -{$sumField} ELSE 0 END) as total_amount, COUNT(*) as transaction_count", false)
            ->where('clinic_id', $clinicId)
            ->whereIn('transaction_type', ['payment', 'refund']);
        if ($startDate) {
            $standaloneBuilder->where($this->documentDateExpr('finances') . ' >= ' . $this->db->escape($startDate), null, false);
        }
        if ($endDate) {
            $standaloneBuilder->where($this->documentDateExpr('finances') . ' <= ' . $this->db->escape($endDate), null, false);
        }
        $standalone = $standaloneBuilder->groupBy('payment_method')->get()->getResultArray();

        $invoicePayments = $this->invoicePaymentReceiptsBuilder($clinicId, $startDate, $endDate)
            ->select("fp.payment_method, SUM(CASE WHEN fp.type = 'payment' THEN fp.amount WHEN fp.type IN ('refund','void') THEN -fp.amount ELSE 0 END) as total_amount, COUNT(*) as transaction_count", false)
            ->groupBy('fp.payment_method')
            ->get()
            ->getResultArray();

        $merged = [];
        foreach ($standalone as $row) {
            $method = $row['payment_method'] ?? 'unknown';
            $merged[$method] = [
                'payment_method' => $method,
                'total_amount' => floatval($row['total_amount'] ?? 0),
                'transaction_count' => intval($row['transaction_count'] ?? 0),
            ];
        }
        foreach ($invoicePayments as $row) {
            $method = $row['payment_method'] ?? 'unknown';
            if (!isset($merged[$method])) {
                $merged[$method] = [
                    'payment_method' => $method,
                    'total_amount' => 0,
                    'transaction_count' => 0,
                ];
            }
            $merged[$method]['total_amount'] += floatval($row['total_amount'] ?? 0);
            $merged[$method]['transaction_count'] += intval($row['transaction_count'] ?? 0);
        }

        return array_values($merged);
    }

    public function getAccountsReceivableAgingSummaryByClinic(int $clinicId, ?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?: date('Y-m-d');
        $rows = $this->getAccountsReceivableAgingDetailByClinic($clinicId, $asOfDate, 10000);

        $summary = [
            'current' => 0.0,
            'days_1_30' => 0.0,
            'days_31_60' => 0.0,
            'days_61_90' => 0.0,
            'days_91_plus' => 0.0,
            'total_balance_due' => 0.0,
            'invoice_count' => 0,
            'as_of_date' => $asOfDate,
        ];

        foreach ($rows as $row) {
            $balance = (float) ($row['balance_due'] ?? 0);
            $bucket = (string) ($row['aging_bucket'] ?? 'current');
            if (!isset($summary[$bucket])) {
                $bucket = 'current';
            }
            $summary[$bucket] += $balance;
            $summary['total_balance_due'] += $balance;
            $summary['invoice_count']++;
        }

        return $summary;
    }

    public function getAccountsReceivableAgingDetailByClinic(int $clinicId, ?string $asOfDate = null, int $limit = 100): array
    {
        $asOfDate = $asOfDate ?: date('Y-m-d');
        $limit = max(1, min($limit, 10000));

        $rows = $this->db->table('finances')
            ->select('finances.id, finances.patient_id, finances.transaction_id, finances.total_amount, finances.balance_due, finances.payment_status, finances.due_date, finances.transaction_date, finances.created_at, patients.first_name, patients.last_name, patients.patient_id as patient_number')
            ->join('patients', 'patients.id = finances.patient_id AND patients.clinic_id = finances.clinic_id', 'left')
            ->where('finances.clinic_id', $clinicId)
            ->where('finances.transaction_type', 'invoice')
            ->where('finances.balance_due >', 0)
            ->whereNotIn('finances.payment_status', ['cancelled'])
            ->orderBy('finances.due_date', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $dueDate = !empty($row['due_date']) ? (string) $row['due_date'] : (string) ($row['transaction_date'] ?? substr((string) ($row['created_at'] ?? $asOfDate), 0, 10));
            $daysPastDue = (int) floor((strtotime($asOfDate) - strtotime($dueDate)) / 86400);
            $row['days_past_due'] = max(0, $daysPastDue);
            $row['aging_bucket'] = $this->mapAgingBucket($daysPastDue);
        }
        unset($row);

        return $rows;
    }

    public function getAccountsPayableAgingSummaryByClinic(int $clinicId, ?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?: date('Y-m-d');
        $rows = $this->getAccountsPayableAgingDetailByClinic($clinicId, $asOfDate, 10000);

        $summary = [
            'current' => 0.0,
            'days_1_30' => 0.0,
            'days_31_60' => 0.0,
            'days_61_90' => 0.0,
            'days_91_plus' => 0.0,
            'total_balance_due' => 0.0,
            'expense_count' => 0,
            'as_of_date' => $asOfDate,
        ];

        foreach ($rows as $row) {
            $balance = (float) ($row['balance_due'] ?? 0);
            $bucket = (string) ($row['aging_bucket'] ?? 'current');
            if (!isset($summary[$bucket])) {
                $bucket = 'current';
            }
            $summary[$bucket] += $balance;
            $summary['total_balance_due'] += $balance;
            $summary['expense_count']++;
        }

        return $summary;
    }

    public function getAccountsPayableAgingDetailByClinic(int $clinicId, ?string $asOfDate = null, int $limit = 100): array
    {
        $asOfDate = $asOfDate ?: date('Y-m-d');
        $limit = max(1, min($limit, 10000));
        $sumField = $this->db->fieldExists('total_amount', 'finances')
            ? 'finances.total_amount'
            : '(finances.amount - COALESCE(finances.discount_amount, 0) + COALESCE(finances.tax_amount, 0))';

        $rows = $this->db->table('finances')
            ->select("
                finances.id,
                finances.transaction_id,
                {$sumField} AS total_amount,
                finances.balance_due,
                finances.payment_status,
                finances.supplier_id,
                suppliers.name AS supplier_name,
                suppliers.contact_person AS supplier_contact_person,
                suppliers.phone AS supplier_phone,
                suppliers.email AS supplier_email,
                finances.counterparty_name,
                finances.expense_category,
                finances.due_date,
                finances.transaction_date,
                finances.created_at
            ", false)
            ->join('suppliers', 'suppliers.id = finances.supplier_id AND suppliers.clinic_id = finances.clinic_id', 'left')
            ->where('finances.clinic_id', $clinicId)
            ->where('finances.transaction_type', 'expense')
            ->where('finances.balance_due >', 0)
            ->whereNotIn('finances.payment_status', ['cancelled'])
            ->orderBy('finances.due_date', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $dueDate = !empty($row['due_date']) ? (string) $row['due_date'] : (string) ($row['transaction_date'] ?? substr((string) ($row['created_at'] ?? $asOfDate), 0, 10));
            $daysPastDue = (int) floor((strtotime($asOfDate) - strtotime($dueDate)) / 86400);
            $row['days_past_due'] = max(0, $daysPastDue);
            $row['aging_bucket'] = $this->mapAgingBucket($daysPastDue);
        }
        unset($row);

        return $rows;
    }

    private function mapAgingBucket(int $daysPastDue): string
    {
        if ($daysPastDue <= 0) {
            return 'current';
        }
        if ($daysPastDue <= 30) {
            return 'days_1_30';
        }
        if ($daysPastDue <= 60) {
            return 'days_31_60';
        }
        if ($daysPastDue <= 90) {
            return 'days_61_90';
        }
        return 'days_91_plus';
    }

    public function getMonthlyRevenue($year = null, $clinicId = null)
    {
        try {
            $clinicId = $this->resolveReportingClinicId($clinicId);
            if ($clinicId <= 0) {
                return [];
            }
            $year = $year ?? date('Y');

            $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';

            $dateExpr = $this->documentDateExpr('finances');
            $standalone = $this->select("MONTH({$dateExpr}) as month, SUM(" . $sumField . ') as total_amount', false)
                ->where('clinic_id', $clinicId)
                ->where("YEAR({$dateExpr})", $year, false)
                ->where('transaction_type', 'payment')
                ->groupBy("MONTH({$dateExpr})", false)
                ->orderBy('month', 'ASC')
                ->findAll();

            $invoicePayments = $this->invoicePaymentReceiptsBuilder($clinicId)
                ->select("MONTH(fp.payment_date) as month, SUM(CASE WHEN fp.type = 'payment' THEN fp.amount WHEN fp.type IN ('refund','void') THEN -fp.amount ELSE 0 END) as total_amount", false)
                ->where('YEAR(fp.payment_date)', $year)
                ->groupBy('MONTH(fp.payment_date)')
                ->orderBy('month', 'ASC')
                ->get()
                ->getResultArray();

            $refunds = $this->db->table('finances')
                ->select("MONTH({$dateExpr}) as month, SUM(" . $sumField . ') as total_amount', false)
                ->where('clinic_id', $clinicId)
                ->where("YEAR({$dateExpr})", $year, false)
                ->where('transaction_type', 'refund')
                ->groupBy("MONTH({$dateExpr})", false)
                ->orderBy('month', 'ASC')
                ->get()
                ->getResultArray();

            $merged = [];
            foreach ($standalone as $row) {
                $month = (int) $row['month'];
                $merged[$month] = ($merged[$month] ?? 0) + floatval($row['total_amount'] ?? 0);
            }
            foreach ($invoicePayments as $row) {
                $month = (int) $row['month'];
                $merged[$month] = ($merged[$month] ?? 0) + floatval($row['total_amount'] ?? 0);
            }
            foreach ($refunds as $row) {
                $month = (int) $row['month'];
                $merged[$month] = ($merged[$month] ?? 0) - floatval($row['total_amount'] ?? 0);
            }

            ksort($merged);
            $result = [];
            foreach ($merged as $month => $total) {
                $result[] = [
                    'month' => $month,
                    'total_amount' => $total,
                ];
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Monthly revenue error: ' . $e->getMessage());
            return [];
        }
    }

    public function getOverduePayments($clinicId = null)
    {
        $builder = $this->select('finances.*, patients.first_name, patients.last_name, patients.patient_id as patient_number')
            ->join('patients', 'patients.id = finances.patient_id AND patients.clinic_id = finances.clinic_id')
            ->where('finances.transaction_type', 'invoice')
            ->where('finances.due_date <', date('Y-m-d'))
            ->where('finances.balance_due >', 0);

        if ($clinicId) {
            $builder->where('finances.clinic_id', $clinicId)
                ->where('patients.clinic_id', $clinicId);
        }

        return $builder->orderBy('due_date', 'ASC')->findAll();
    }

    public function getServiceTypeStats($startDate = null, $endDate = null, $clinicId = null)
    {
        $builder = $this->db->table('finances');

        if ($clinicId) {
            $builder->where('clinic_id', $clinicId);
        }
        if ($startDate) {
            $builder->where('created_at >=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('created_at <=', $endDate . ' 23:59:59');
        }

        $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';

        return $builder->select('service_type, SUM(' . $sumField . ') as total_amount, COUNT(*) as transaction_count')
            ->where('transaction_type', 'payment')
            ->groupBy('service_type')
            ->get()
            ->getResultArray();
    }
}
