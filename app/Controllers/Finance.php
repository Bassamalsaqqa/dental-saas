<?php

namespace App\Controllers;

use App\Models\FinanceModel;
use App\Models\FinancePaymentModel;
use App\Models\FileAttachmentModel;
use App\Models\PatientModel;
use App\Models\ExaminationModel;
use App\Models\ClinicUserModel;
use App\Models\ProviderPayoutModel;
use App\Models\ProviderPayoutPaymentModel;
use App\Models\FinanceLiquidityTransferModel;
use App\Models\SupplierModel;
use App\Models\UserModel;
use App\Services\ActivityLogger;
use App\Services\ClinicalChargeLineService;
use App\Services\FinanceTreasuryService;
use App\Services\PlanGuard;
use App\Services\WhatsAppWorkflowService;
use App\Libraries\ApiRuntimeContext;

class Finance extends BaseController
{
    protected $financeModel;
    protected $financePaymentModel;
    protected $attachmentModel;
    protected $patientModel;
    protected $examinationModel;
    protected $clinicUserModel;
    protected $providerPayoutModel;
    protected $providerPayoutPaymentModel;
    protected $financeLiquidityTransferModel;
    protected $supplierModel;
    protected $userModel;
    protected $activityLogger;
    protected $storageService;
    protected $financeTreasuryService;
    protected $planGuard;
    protected $db;
    protected ClinicalChargeLineService $chargeLineService;
    protected WhatsAppWorkflowService $whatsAppWorkflowService;

    public function __construct()
    {
        $this->financeModel = new FinanceModel();
        $this->financePaymentModel = new FinancePaymentModel();
        $this->attachmentModel = new FileAttachmentModel();
        $this->patientModel = new PatientModel();
        $this->examinationModel = new ExaminationModel();
        $this->clinicUserModel = new ClinicUserModel();
        $this->providerPayoutModel = new ProviderPayoutModel();
        $this->providerPayoutPaymentModel = new ProviderPayoutPaymentModel();
        $this->financeLiquidityTransferModel = new FinanceLiquidityTransferModel();
        $this->supplierModel = new SupplierModel();
        $this->userModel = new UserModel();
        $this->activityLogger = new ActivityLogger();
        $this->storageService = new \App\Services\StorageService();
        $this->financeTreasuryService = new FinanceTreasuryService();
        $this->planGuard = new PlanGuard();
        $this->db = \Config\Database::connect();
        $this->chargeLineService = new ClinicalChargeLineService();
        $this->whatsAppWorkflowService = new WhatsAppWorkflowService();
    }

    private function canAccessFinanceReports(int $clinicId): bool
    {
        return $this->planGuard->isFeatureEnabled($clinicId, 'finance_reports.enabled', false)
            || $this->planGuard->isFeatureEnabled($clinicId, 'reports', false);
    }

    private function requireFinanceReportsFeature(int $clinicId): void
    {
        if (!$this->canAccessFinanceReports($clinicId)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Access Denied: Finance reports are not available in the current plan.');
        }
    }

    private function resolveActorUserId(): int
    {
        $apiUserId = ApiRuntimeContext::getUserId();
        if ($apiUserId) {
            return (int) $apiUserId;
        }
        return (int) (session()->get('user_id') ?? 0);
    }

    private function getClinicProviders(int $clinicId): array
    {
        return $this->clinicUserModel->getActiveUserDirectoryByClinic($clinicId);
    }

    private function getExpenseCategoryOptions(): array
    {
        $defaults = [
            'rent' => 'Rent',
            'utilities' => 'Utilities',
            'salaries' => 'Salaries',
            'laboratory' => 'Laboratory',
            'supplies' => 'Supplies',
            'equipment' => 'Equipment',
            'maintenance' => 'Maintenance',
            'software' => 'Software',
            'marketing' => 'Marketing',
            'taxes' => 'Taxes',
            'fees' => 'Fees',
            'inventory_purchase' => 'Inventory Purchase',
            'inventory_cogs' => 'Inventory COGS',
            'miscellaneous' => 'Miscellaneous',
        ];

        foreach ($defaults as $key => $fallback) {
            $translated = lang('Finance.expenseCategory.' . $key);
            $defaults[$key] = is_string($translated) && $translated !== 'Finance.expenseCategory.' . $key
                ? $translated
                : $fallback;
        }

        return $defaults;
    }

    private function getFinanceAttachmentPurposeCodeMap(): array
    {
        return [
            'attachment' => 'ATTACHMENT',
            'check_scan' => 'CHECKSCAN',
        ];
    }

    private function financeDocumentDateExpr(string $alias = 'f'): string
    {
        return "COALESCE({$alias}.transaction_date, {$alias}.paid_date, DATE({$alias}.created_at))";
    }

    private function resolveFinanceTransactionDate(?string $preferredDate, ?string $paidDate = null): string
    {
        return $preferredDate ?: ($paidDate ?: date('Y-m-d'));
    }

    private function generateFinanceArchiveName(array $finance, int $clinicId, string $purpose, $file): string
    {
        $purposeMap = $this->getFinanceAttachmentPurposeCodeMap();
        $purposeCode = $purposeMap[$purpose] ?? strtoupper(preg_replace('/[^A-Z0-9]+/', '', str_replace('_', '', strtoupper($purpose))));
        $reportDate = !empty($finance['transaction_date'])
            ? (string) $finance['transaction_date']
            : (!empty($finance['paid_date']) ? (string) $finance['paid_date'] : date('Y-m-d'));
        $dateSegment = preg_replace('/[^0-9]/', '', $reportDate) ?: date('Ymd');
        $transactionId = strtoupper(trim((string) ($finance['transaction_id'] ?? '')));
        if ($transactionId === '') {
            $transactionId = 'FIN' . (int) ($finance['id'] ?? 0);
        }

        $prefix = preg_replace('/[^A-Z0-9_-]+/', '', $transactionId . '_' . $dateSegment . '_' . $purposeCode);
        $sequenceQuery = $this->attachmentModel
            ->where('clinic_id', $clinicId)
            ->where('entity_type', 'finance')
            ->where('entity_id', (int) ($finance['id'] ?? 0))
            ->where('purpose', $purpose)
            ->like('original_name', $prefix, 'after')
            ->countAllResults();
        $sequence = str_pad((string) ($sequenceQuery + 1), 3, '0', STR_PAD_LEFT);

        $extension = strtolower((string) $file->getClientExtension());
        if ($extension === '') {
            $extension = strtolower((string) pathinfo($file->getClientName(), PATHINFO_EXTENSION));
        }

        return $prefix . '_' . $sequence . ($extension !== '' ? '.' . $extension : '');
    }

    private function getFinanceCurrencyOptions(): array
    {
        return ['ILS', 'USD', 'JOD', 'EUR', 'GBP'];
    }

    private function getLiquidityAccountOptions(): array
    {
        $fallbacks = [
            'safe_cash' => 'Safe Cash',
            'bank' => 'Bank',
            'undeposited_checks' => 'Checks on Hand',
        ];

        foreach ($fallbacks as $key => $fallback) {
            $translated = lang('Finance.liquidityAccount.' . $key);
            $fallbacks[$key] = is_string($translated) && $translated !== 'Finance.liquidityAccount.' . $key
                ? $translated
                : $fallback;
        }

        return $fallbacks;
    }

    private function normalizeLiquidityAccountInput(string $paymentMethod, string $direction, ?string $transactionType = null, ?string $fieldName = 'liquidity_account'): ?string
    {
        $requested = $fieldName ? (string) ($this->request->getPost($fieldName) ?? '') : '';
        if ($requested !== '' && !$this->financeTreasuryService->isCompatibleLiquidityAccount($paymentMethod, $direction, $requested, $transactionType)) {
            throw new \RuntimeException('Selected liquidity account does not match the payment method.');
        }

        $account = $this->financeTreasuryService->normalizeLiquidityAccount($paymentMethod, $direction, $requested, $transactionType);
        if ($account !== null && !$this->financeTreasuryService->isValidLiquidityAccount($account)) {
            throw new \RuntimeException('Invalid liquidity account.');
        }

        return $account;
    }

    private function getCheckOutgoingLiquidityOptions(): array
    {
        return [
            'bank' => $this->getLiquidityAccountOptions()['bank'],
            'undeposited_checks' => $this->getLiquidityAccountOptions()['undeposited_checks'],
        ];
    }

    private function resolveDirectFinanceLiquidityAccount(string $transactionType, string $paymentMethod): ?string
    {
        $transactionType = strtolower(trim($transactionType));
        if ($transactionType === 'invoice' || $transactionType === 'adjustment') {
            return null;
        }

        $direction = $transactionType === 'refund' ? 'out' : 'in';

        return $this->normalizeLiquidityAccountInput($paymentMethod, $direction, $transactionType);
    }

    private function resolveSettlementLiquidityAccount(array $finance, string $settlementType, ?array $relatedPayment = null): ?string
    {
        $paymentMethod = (string) ($this->request->getPost('payment_method') ?: ($relatedPayment['payment_method'] ?? ($finance['payment_method'] ?? 'cash')));
        $settlementType = strtolower(trim($settlementType));
        $financeType = strtolower((string) ($finance['transaction_type'] ?? 'invoice'));

        if ($settlementType === 'adjustment') {
            return null;
        }

        if ($settlementType === 'payment') {
            $direction = $financeType === 'expense' ? 'out' : 'in';
        } else {
            $direction = 'out';
        }

        if ($relatedPayment && !empty($relatedPayment['liquidity_account'])) {
            return (string) $relatedPayment['liquidity_account'];
        }

        return $this->normalizeLiquidityAccountInput($paymentMethod, $direction, $financeType);
    }

    private function resolveExpenseOutgoingLiquidityAccount(string $paymentMethod): ?string
    {
        return $this->normalizeLiquidityAccountInput($paymentMethod, 'out', 'expense', 'liquidity_account');
    }

    private function supportsFinancePaymentExchangeFields(): bool
    {
        return $this->db->fieldExists('currency', 'finance_payments')
            && $this->db->fieldExists('amount_original', 'finance_payments')
            && $this->db->fieldExists('exchange_rate', 'finance_payments');
    }

    private function normalizeSettlementAmount(float $amountOriginal, float $exchangeRate): float
    {
        return round($amountOriginal * $exchangeRate, 2);
    }

    private function normalizeFinanceSettlementInput(array $finance, ?float $maxBaseAmount = null): array
    {
        $baseCurrency = (string) settings()->get('currency', 'ILS');
        $currency = strtoupper((string) ($this->request->getPost('currency') ?: $baseCurrency));
        if (!in_array($currency, $this->getFinanceCurrencyOptions(), true)) {
            throw new \RuntimeException('Invalid settlement currency.');
        }

        $rawAmountOriginal = $this->request->getPost('amount_original');
        if ($rawAmountOriginal === null || $rawAmountOriginal === '') {
            $rawAmountOriginal = $this->request->getPost('amount');
        }
        $amountOriginal = round(floatval($rawAmountOriginal ?? 0), 2);
        if ($amountOriginal <= 0) {
            throw new \RuntimeException('Settlement amount must be greater than 0.');
        }

        $exchangeRate = floatval($this->request->getPost('exchange_rate') ?? 0);
        if ($currency === $baseCurrency) {
            $exchangeRate = 1.0;
        } elseif ($exchangeRate <= 0) {
            throw new \RuntimeException('Exchange rate is required for non-default currency.');
        }

        $amountBase = $this->normalizeSettlementAmount($amountOriginal, $exchangeRate);
        if ($amountBase <= 0) {
            throw new \RuntimeException('Settlement amount must be greater than 0.');
        }

        if ($maxBaseAmount !== null && $amountBase - $maxBaseAmount > 0.0001) {
            throw new \RuntimeException('Settlement exceeds remaining balance.');
        }

        return [
            'currency' => $currency,
            'amount_original' => $amountOriginal,
            'exchange_rate' => $exchangeRate,
            'amount_base' => $amountBase,
            'payment_method' => (string) ($this->request->getPost('payment_method') ?: ($finance['payment_method'] ?? 'cash')),
            'payment_date' => $this->normalizePostedDateOnly('payment_date') ?: date('Y-m-d'),
            'notes' => $this->request->getPost('notes'),
        ];
    }

    private function normalizeInvoiceSettlementInput(array $finance, ?float $maxBaseAmount = null): array
    {
        return $this->normalizeFinanceSettlementInput($finance, $maxBaseAmount);
    }

    private function buildExpensePaymentState(float $totalAmount, ?float $lockedSettledAmount = null, ?string $existingPaidDate = null): array
    {
        $today = date('Y-m-d');
        $requestedStatus = strtolower((string) ($this->request->getPost('payment_status') ?? 'paid'));
        if (!in_array($requestedStatus, ['pending', 'partial', 'paid'], true)) {
            $requestedStatus = 'paid';
        }

        $rawAmountPaid = $this->request->getPost('amount_paid');
        $amountPaid = ($rawAmountPaid === null || $rawAmountPaid === '')
            ? null
            : round(floatval($rawAmountPaid), 2);

        if ($lockedSettledAmount !== null) {
            $amountPaid = round(max(0, $lockedSettledAmount), 2);
        } else {
            if ($requestedStatus === 'paid') {
                $amountPaid = $totalAmount;
            } elseif ($requestedStatus === 'pending') {
                $amountPaid = round(max(0, $amountPaid ?? 0), 2);
            } else {
                if ($amountPaid === null || $amountPaid <= 0) {
                    throw new \RuntimeException('Partial expenses must include an amount already paid.');
                }
            }
        }

        if ($amountPaid === null) {
            $amountPaid = 0.0;
        }

        if ($amountPaid < 0) {
            throw new \RuntimeException('Amount paid cannot be negative.');
        }
        if ($amountPaid - $totalAmount > 0.0001) {
            throw new \RuntimeException('Amount paid cannot exceed the total expense amount.');
        }

        $dueDate = $this->normalizePostedDateOnly('due_date');
        $paidDate = $this->normalizePostedDateOnly('paid_date') ?: $existingPaidDate;
        if ($amountPaid > 0 && empty($paidDate)) {
            $paidDate = $today;
        }
        if ($amountPaid <= 0) {
            $paidDate = null;
        }

        $balanceDue = round(max(0, $totalAmount - $amountPaid), 2);
        if ($balanceDue <= 0.0001) {
            return [
                'payment_status' => 'paid',
                'amount_paid' => round($totalAmount, 2),
                'balance_due' => 0.0,
                'due_date' => null,
                'paid_date' => $paidDate ?: $today,
            ];
        }

        if (empty($dueDate)) {
            $status = $amountPaid > 0 ? 'partial' : 'pending';
        } else {
            $status = strtotime($dueDate) < strtotime($today)
                ? 'overdue'
                : ($amountPaid > 0 ? 'partial' : 'pending');
        }

        return [
            'payment_status' => $status,
            'amount_paid' => round($amountPaid, 2),
            'balance_due' => $balanceDue,
            'due_date' => $dueDate,
            'paid_date' => $paidDate,
        ];
    }

    private function validateOwnedFinanceReferences(int $clinicId, ?int $patientId, ?int $examinationId): ?string
    {
        if ($patientId !== null && $patientId > 0) {
            $patient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
            if (!$patient) {
                return 'Selected patient does not belong to the active clinic.';
            }
        }

        if ($examinationId !== null && $examinationId > 0) {
            $exam = $this->examinationModel->where('clinic_id', $clinicId)->find($examinationId);
            if (!$exam) {
                return 'Selected examination does not belong to the active clinic.';
            }
        }

        return null;
    }

    private function buildExpenseViewData(array $finance = null): array
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        $hasSettlementEntries = ($clinicId > 0 && !empty($finance['id']))
            ? $this->financePaymentModel->hasSettlementEntries($clinicId, (int) $finance['id'])
            : false;
        $settledTotal = ($clinicId > 0 && !empty($finance['id']))
            ? $this->financePaymentModel->getNetSettledAmount($clinicId, (int) $finance['id'])
            : 0.0;

        return [
            'title' => $finance ? ('Edit Expense - ' . ($finance['transaction_id'] ?? '')) : 'New Expense',
            'finance' => $finance,
            'validation' => \Config\Services::validation(),
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => $this->getFinanceCurrencyOptions(),
            'liquidity_account_options' => $this->getLiquidityAccountOptions(),
            'check_outgoing_liquidity_options' => $this->getCheckOutgoingLiquidityOptions(),
            'expense_category_options' => $this->getExpenseCategoryOptions(),
            'supplier_directory' => $clinicId > 0 ? $this->supplierModel->getActiveOptionsByClinic($clinicId) : [],
            'selected_supplier_id' => (int) ($this->request->getGet('supplier_id') ?? $this->request->getPost('supplier_id') ?? ($finance['supplier_id'] ?? 0)),
            'expense_attachment' => ($clinicId > 0 && !empty($finance['id']))
                ? $this->attachmentModel
                    ->where('clinic_id', $clinicId)
                    ->where('entity_type', 'finance')
                    ->where('entity_id', $finance['id'])
                    ->where('purpose', 'attachment')
                    ->first()
                : null,
            'expense_has_settlement_entries' => $hasSettlementEntries,
            'expense_settled_total' => $settledTotal,
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to') ?? $this->request->getPost('return_to'), ['finance', 'finance/payables', 'inventory/suppliers']),
        ];
    }

    private function resolveExpenseSupplierSelection(int $clinicId): array
    {
        $supplierIdRaw = $this->request->getPost('supplier_id');
        $supplierId = is_numeric($supplierIdRaw) && (int) $supplierIdRaw > 0 ? (int) $supplierIdRaw : null;
        $counterpartyName = trim((string) $this->request->getPost('counterparty_name'));

        if (!$supplierId) {
            return [null, $counterpartyName];
        }

        $supplier = $this->supplierModel->where('clinic_id', $clinicId)->find($supplierId);
        if (!$supplier) {
            throw new \RuntimeException('Selected supplier was not found in this clinic.');
        }

        return [$supplierId, (string) ($supplier['name'] ?? $counterpartyName)];
    }

    private function requestArrayValue(string $key, int $index, $default = null)
    {
        $values = $this->request->getPost($key);
        if (!is_array($values)) {
            return $default;
        }
        return array_key_exists($index, $values) ? $values[$index] : $default;
    }

    private function normalizeProviderSplitPayloads(int $clinicId, float $grossAmount): array
    {
        $enabled = filter_var($this->request->getPost('provider_split_enabled'), FILTER_VALIDATE_BOOLEAN);
        if (!$enabled) {
            return [];
        }

        $types = $this->request->getPost('provider_type');
        if (!is_array($types) || empty($types)) {
            throw new \RuntimeException('Add at least one clinical provider allocation.');
        }

        $payloads = [];
        $seenProviders = [];
        $totalProviderShare = 0.0;

        foreach (array_values($types) as $index => $providerTypeRaw) {
            $providerType = strtolower(trim((string) $providerTypeRaw));
            if (!in_array($providerType, ['user', 'guest'], true)) {
                throw new \RuntimeException('Please select a valid provider type.');
            }

            $providerUserId = null;
            $providerNameSnapshot = null;
            if ($providerType === 'user') {
                $providerUserId = intval($this->requestArrayValue('provider_user_id', $index, 0));
                if ($providerUserId <= 0) {
                    throw new \RuntimeException('Please select a provider user.');
                }
                $membership = $this->clinicUserModel->findActiveMembershipByClinicAndUser($clinicId, $providerUserId);
                if (!$membership) {
                    throw new \RuntimeException('Selected provider user is not active in this clinic.');
                }
                $providerKey = 'user:' . $providerUserId;
            } else {
                $providerNameSnapshot = trim((string) $this->requestArrayValue('provider_name_snapshot', $index, ''));
                if ($providerNameSnapshot === '') {
                    throw new \RuntimeException('Please enter guest provider name.');
                }
                $providerKey = 'guest:' . mb_strtolower($providerNameSnapshot);
            }

            if (isset($seenProviders[$providerKey])) {
                throw new \RuntimeException('Each clinical provider can only appear once per transaction.');
            }
            $seenProviders[$providerKey] = true;

            $splitMode = strtolower((string) $this->requestArrayValue('provider_split_mode', $index, 'percent'));
            if (!in_array($splitMode, ['percent', 'fixed'], true)) {
                throw new \RuntimeException('Please select a valid split mode.');
            }

            $splitValue = floatval($this->requestArrayValue('provider_split_value', $index, 0));
            if ($splitValue < 0) {
                throw new \RuntimeException('Provider split value must be 0 or greater.');
            }
            if ($splitMode === 'percent' && $splitValue > 100) {
                throw new \RuntimeException('Provider split percentage cannot exceed 100.');
            }

            $providerShareAmount = $splitMode === 'percent'
                ? round($grossAmount * ($splitValue / 100), 2)
                : min(round($splitValue, 2), round($grossAmount, 2));
            $providerShareAmount = max(0, min($providerShareAmount, round($grossAmount, 2)));
            $totalProviderShare += $providerShareAmount;

            $markPaidNow = ((string) $this->requestArrayValue('provider_mark_paid_now', $index, '0')) === '1';
            $providerPaidAmount = 0.0;
            $providerPaymentMethod = null;
            $providerPaidDate = null;
            if ($markPaidNow && $providerShareAmount > 0) {
                $providerPaidAmount = floatval($this->requestArrayValue('provider_paid_amount', $index, $providerShareAmount));
                $providerPaidAmount = max(0, min(round($providerPaidAmount, 2), $providerShareAmount));
                if ($providerPaidAmount > 0) {
                    $providerPaymentMethod = (string) ($this->requestArrayValue('provider_paid_method', $index, 'cash') ?: 'cash');
                    if (!in_array($providerPaymentMethod, ['cash', 'card', 'bank_transfer', 'check', 'other'], true)) {
                        $providerPaymentMethod = 'cash';
                    }
                    $postedDate = $this->requestArrayValue('provider_paid_date', $index, null);
                    $providerPaidDate = (string) ($this->normalizeDateOnly($postedDate) ?: date('Y-m-d'));
                }
            }

            $payloads[] = [
                'id' => intval($this->requestArrayValue('provider_payout_id', $index, 0)) ?: null,
                'provider_type' => $providerType,
                'provider_user_id' => $providerUserId ?: null,
                'provider_name_snapshot' => $providerNameSnapshot,
                'split_mode' => $splitMode,
                'split_value' => $splitValue,
                'gross_amount' => round($grossAmount, 2),
                'provider_share_amount' => round($providerShareAmount, 2),
                'clinic_share_amount' => 0.0,
                'provider_paid_amount' => round($providerPaidAmount, 2),
                'provider_balance_due' => round(max(0, $providerShareAmount - $providerPaidAmount), 2),
                'status' => $providerShareAmount <= 0 ? 'paid' : (($providerPaidAmount >= $providerShareAmount) ? 'paid' : ($providerPaidAmount > 0 ? 'partial' : 'unpaid')),
                'currency' => settings()->get('currency', 'ILS'),
                'notes' => trim((string) $this->requestArrayValue('provider_split_notes', $index, '')),
                'provider_payment_method' => $providerPaymentMethod,
                'provider_paid_date' => $providerPaidDate,
            ];
        }

        if ($totalProviderShare - round($grossAmount, 2) > 0.0001) {
            throw new \RuntimeException('Total clinical provider compensation cannot exceed the collected amount.');
        }

        $clinicShareAmount = round(max(0, $grossAmount - $totalProviderShare), 2);
        foreach ($payloads as &$payload) {
            $payload['clinic_share_amount'] = $clinicShareAmount;
        }
        unset($payload);

        return $payloads;
    }

    private function upsertProviderPayouts(int $clinicId, int $financeId, int $patientId, float $grossAmount): void
    {
        $payloads = $this->normalizeProviderSplitPayloads($clinicId, $grossAmount);
        $existingRows = $this->providerPayoutModel->getRowsByFinanceId($clinicId, $financeId);
        $existingById = [];
        foreach ($existingRows as $row) {
            $existingById[(int) $row['id']] = $row;
        }

        if (empty($payloads)) {
            foreach ($existingRows as $existing) {
                $paidTotal = $this->providerPayoutPaymentModel->getPaidTotal($clinicId, (int) $existing['id']);
                if ($paidTotal > 0) {
                    throw new \RuntimeException('Cannot disable clinical provider compensation after payout payments are recorded.');
                }
            }
            foreach ($existingRows as $existing) {
                $this->providerPayoutModel->delete((int) $existing['id']);
            }
            return;
        }

        $keptIds = [];
        foreach ($payloads as $payload) {
            $payoutData = [
                'finance_id' => $financeId,
                'patient_id' => $patientId,
                'provider_type' => $payload['provider_type'],
                'provider_user_id' => $payload['provider_user_id'],
                'provider_name_snapshot' => $payload['provider_name_snapshot'],
                'split_mode' => $payload['split_mode'],
                'split_value' => $payload['split_value'],
                'gross_amount' => $payload['gross_amount'],
                'provider_share_amount' => $payload['provider_share_amount'],
                'clinic_share_amount' => $payload['clinic_share_amount'],
                'provider_paid_amount' => $payload['provider_paid_amount'],
                'provider_balance_due' => $payload['provider_balance_due'],
                'status' => $payload['status'],
                'currency' => $payload['currency'],
                'notes' => $payload['notes'],
                'created_by' => $this->resolveActorUserId(),
            ];

            $payoutId = null;
            if (!empty($payload['id'])) {
                $payoutId = (int) $payload['id'];
                $existing = $existingById[$payoutId] ?? null;
                if (!$existing) {
                    throw new \RuntimeException('A provider allocation could not be matched for update.');
                }

                $paidTotal = $this->providerPayoutPaymentModel->getPaidTotal($clinicId, $payoutId);
                if ($paidTotal > $payload['provider_share_amount']) {
                    throw new \RuntimeException('Provider share cannot be lower than payout already recorded.');
                }

                $additionalPaymentAmount = round((float) ($payload['provider_paid_amount'] ?? 0), 2);
                $remainingBeforeInlinePayment = round(max(0, $payload['provider_share_amount'] - $paidTotal), 2);
                if ($additionalPaymentAmount > $remainingBeforeInlinePayment) {
                    throw new \RuntimeException('Provider payment cannot exceed the remaining provider balance.');
                }

                if ($additionalPaymentAmount > 0) {
                    $inlineMethod = $payload['provider_payment_method'] ?? 'cash';
                    $this->providerPayoutPaymentModel->insert([
                        'provider_payout_id' => $payoutId,
                        'amount' => $additionalPaymentAmount,
                        'payment_method' => $inlineMethod,
                        'liquidity_account' => $this->normalizeLiquidityAccountInput($inlineMethod, 'out', 'provider_payout'),
                        'paid_date' => $payload['provider_paid_date'] ?? date('Y-m-d'),
                        'notes' => 'Paid while updating transaction',
                        'created_by' => $this->resolveActorUserId(),
                    ]);

                    $paidTotal += $additionalPaymentAmount;
                }

                $payoutData['provider_paid_amount'] = round($paidTotal, 2);
                $payoutData['provider_balance_due'] = round(max(0, $payload['provider_share_amount'] - $paidTotal), 2);
                $payoutData['status'] = $payoutData['provider_balance_due'] <= 0 ? 'paid' : ($paidTotal > 0 ? 'partial' : 'unpaid');
                $this->providerPayoutModel->update($payoutId, $payoutData);
            } else {
                $this->providerPayoutModel->insert($payoutData);
                $payoutId = (int) $this->providerPayoutModel->getInsertID();
                if ($payload['provider_paid_amount'] > 0) {
                    $inlineMethod = $payload['provider_payment_method'] ?? 'cash';
                    $this->providerPayoutPaymentModel->insert([
                        'provider_payout_id' => $payoutId,
                        'amount' => $payload['provider_paid_amount'],
                        'payment_method' => $inlineMethod,
                        'liquidity_account' => $this->normalizeLiquidityAccountInput($inlineMethod, 'out', 'provider_payout'),
                        'paid_date' => $payload['provider_paid_date'] ?? date('Y-m-d'),
                        'notes' => 'Paid at transaction creation',
                        'created_by' => $this->resolveActorUserId(),
                    ]);
                }
            }

            $keptIds[] = $payoutId;
            $this->syncProviderPayoutStatus($clinicId, $payoutId);
        }

        foreach ($existingRows as $existing) {
            $existingId = (int) $existing['id'];
            if (in_array($existingId, $keptIds, true)) {
                continue;
            }
            $paidTotal = $this->providerPayoutPaymentModel->getPaidTotal($clinicId, $existingId);
            if ($paidTotal > 0) {
                throw new \RuntimeException('Cannot remove a provider allocation after payout payments are recorded.');
            }
            $this->providerPayoutModel->delete($existingId);
        }
    }

    private function syncProviderPayoutStatus(int $clinicId, int $payoutId): void
    {
        $payout = $this->providerPayoutModel->findByClinic($clinicId, $payoutId);
        if (!$payout) {
            return;
        }

        $totalPaid = $this->providerPayoutPaymentModel->getPaidTotal($clinicId, $payoutId);
        $share = floatval($payout['provider_share_amount'] ?? 0);
        $balance = max(0, round($share - $totalPaid, 2));

        $status = 'unpaid';
        if ($share <= 0 || $balance <= 0) {
            $status = 'paid';
        } elseif ($totalPaid > 0) {
            $status = 'partial';
        }

        $this->providerPayoutModel->setValidationRules([]);
        $this->providerPayoutModel->update($payoutId, [
            'provider_paid_amount' => round($totalPaid, 2),
            'provider_balance_due' => $balance,
            'status' => $status,
        ]);
    }

    public function index()
    {
        $clinicId = session()->get('active_clinic_id');
        $expenseViewData = $this->buildExpenseViewData();

        $data = [
            'title' => 'Finance Management',
            'finances' => [], // Data will be loaded via AJAX
            'stats' => $clinicId ? $this->financeModel->getFinanceStatsByClinic($clinicId) : [],
            'can_access_finance_reports' => $clinicId ? $this->canAccessFinanceReports((int) $clinicId) : false,
            'disableDataTables' => true, // Disable DataTables scripts for custom implementation
            'currency_position' => settings()->get('currency_position', 'before'),
            'currency_symbol' => settings()->getCurrencySymbol(),
            'patients' => [],
            'clinic_providers' => $clinicId ? $this->getClinicProviders((int) $clinicId) : [],
            'validation' => session()->getFlashdata('validation') ?: \Config\Services::validation(),
            'loadSelect2' => true,
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => $this->getFinanceCurrencyOptions(),
            'liquidity_account_options' => $this->getLiquidityAccountOptions(),
            'check_outgoing_liquidity_options' => $this->getCheckOutgoingLiquidityOptions(),
            'expense_category_options' => $expenseViewData['expense_category_options'] ?? [],
            'supplier_directory' => $expenseViewData['supplier_directory'] ?? [],
            'selected_supplier_id' => $expenseViewData['selected_supplier_id'] ?? 0,
        ];

        return $this->view('finance/index', $data);
    }

    public function create()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to create a transaction.');
        }

        $patientId = $this->request->getGet('patient_id');
        $patientId = is_numeric($patientId) ? (int)$patientId : null;
        $selectedPatient = null;
        if ($patientId) {
            $selectedPatient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
            if (!$selectedPatient) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
            }
        }

        $returnTo = $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'finance', 'examination', 'treatment', 'treatments']);
        if (!$returnTo && $patientId) {
            $returnTo = 'patient/' . $patientId;
        }
        $transactionTypePreset = (string) ($this->request->getGet('transaction_type') ?? '');
        if (!in_array($transactionTypePreset, ['payment', 'invoice', 'refund', 'adjustment'], true)) {
            $transactionTypePreset = '';
        }
        $selectedChargeLineIds = $this->normalizeClinicalChargeLineIdList($this->request->getGet('clinical_charge_line_ids') ?? []);

        $data = [
            'title' => 'New Financial Transaction',
            'patients' => $selectedPatient ? [$selectedPatient] : [], // S4-02f: No bulk preload
            'examinations' => [], // S4-02f: No bulk preload
            'clinic_providers' => $this->getClinicProviders((int) $clinicId),
            'validation' => \Config\Services::validation(),
            'loadSelect2' => true,
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => $this->getFinanceCurrencyOptions(),
            'liquidity_account_options' => $this->getLiquidityAccountOptions(),
            'check_outgoing_liquidity_options' => $this->getCheckOutgoingLiquidityOptions(),
            'clinical_charge_lines' => $patientId ? $this->chargeLineService->getInvoiceableForPatient((int) $clinicId, (int) $patientId) : [],
            'selected_clinical_charge_line_ids' => $selectedChargeLineIds,
            'transaction_type_preset' => $transactionTypePreset,
            'selected_patient_id' => $patientId,
            'return_to' => $returnTo
        ];

        return $this->view('finance/create', $data);
    }

    public function createExpense()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to create an expense.');
        }

        return $this->view('finance/expense_create', $this->buildExpenseViewData());
    }

    public function storeExpense()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $rules = [
            'counterparty_name' => 'required|max_length[150]',
            'supplier_id' => 'permit_empty|integer',
            'expense_category' => 'required|in_list[' . implode(',', array_keys($this->getExpenseCategoryOptions())) . ']',
            'amount_original' => 'required|decimal|greater_than[0]',
            'currency' => 'required|in_list[ILS,USD,JOD,EUR,GBP]',
            'payment_method' => 'required|in_list[cash,card,bank_transfer,check,insurance,other]',
            'payment_status' => 'permit_empty|in_list[pending,partial,paid]',
            'amount_paid' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'tax_percentage' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
            'transaction_date' => 'permit_empty|valid_date',
            'paid_date' => 'permit_empty|valid_date',
            'due_date' => 'permit_empty|valid_date',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $amountOriginal = floatval($this->request->getPost('amount_original'));
        try {
            [$supplierId, $counterpartyName] = $this->resolveExpenseSupplierSelection((int) $clinicId);
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
        $currency = $this->request->getPost('currency') ?: settings()->get('currency', 'ILS');
        $baseCurrency = settings()->get('currency', 'ILS');
        $exchangeRate = floatval($this->request->getPost('exchange_rate') ?? 0);
        if ($currency === $baseCurrency) {
            $exchangeRate = $exchangeRate > 0 ? $exchangeRate : 1.0;
        } elseif ($exchangeRate <= 0) {
            return redirect()->back()->withInput()->with('error', 'Exchange rate is required for non-default currency.');
        }

        $amount = $amountOriginal * $exchangeRate;
        $discountAmount = floatval($this->request->getPost('discount_amount') ?? 0);
        $taxPercentage = floatval($this->request->getPost('tax_percentage') ?? 0);
        $taxableBase = max(0, $amount - $discountAmount);
        $taxAmount = $taxableBase * ($taxPercentage / 100);
        $totalAmount = $amount - $discountAmount + $taxAmount;
        $expensePaymentState = $this->buildExpensePaymentState($totalAmount);
        $transactionDate = $this->resolveFinanceTransactionDate(
            $this->normalizePostedDateOnly('transaction_date'),
            $expensePaymentState['paid_date'] ?: date('Y-m-d')
        );

        $financeData = [
            'patient_id' => null,
            'examination_id' => null,
            'transaction_type' => 'expense',
            'amount' => $amount,
            'currency' => $currency,
            'amount_original' => $amountOriginal,
            'exchange_rate' => $exchangeRate,
            'payment_method' => $this->request->getPost('payment_method'),
            'liquidity_account' => null,
            'payment_status' => $expensePaymentState['payment_status'],
            'description' => $this->request->getPost('description'),
            'service_type' => 'other',
            'expense_category' => $this->request->getPost('expense_category'),
            'counterparty_name' => $counterpartyName,
            'supplier_id' => $supplierId,
            'service_details' => $this->request->getPost('service_details'),
            'discount_amount' => $discountAmount,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'amount_paid' => $expensePaymentState['amount_paid'],
            'balance_due' => $expensePaymentState['balance_due'],
            'due_date' => $expensePaymentState['due_date'],
            'paid_date' => $expensePaymentState['paid_date'],
            'transaction_date' => $transactionDate,
            'notes' => $this->request->getPost('notes'),
            'created_by' => $this->resolveActorUserId(),
        ];

        try {
            $this->db->transBegin();
            if (!$this->financeModel->insert($financeData)) {
                $this->db->transRollback();
                return redirect()->back()->withInput()->with('error', implode(', ', $this->financeModel->errors()));
            }

            $financeId = (int) $this->financeModel->getInsertID();
            if (($expensePaymentState['amount_paid'] ?? 0) > 0) {
                $paymentData = [
                    'finance_id' => $financeId,
                    'amount' => (float) $expensePaymentState['amount_paid'],
                    'type' => 'payment',
                    'payment_method' => $financeData['payment_method'],
                    'liquidity_account' => $this->resolveExpenseOutgoingLiquidityAccount((string) $financeData['payment_method']),
                    'payment_date' => $expensePaymentState['paid_date'] ?: date('Y-m-d'),
                    'notes' => 'Initial expense settlement',
                    'created_by' => $this->resolveActorUserId(),
                ];
                if ($this->supportsFinancePaymentExchangeFields()) {
                    $paymentData['currency'] = $financeData['currency'];
                    $paymentData['amount_original'] = round(((float) $expensePaymentState['amount_paid']) / max(0.0001, (float) $financeData['exchange_rate']), 2);
                    $paymentData['exchange_rate'] = $financeData['exchange_rate'];
                }
                if (!$this->financePaymentModel->insert($paymentData)) {
                    $this->db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Failed to record the initial expense payment.');
                }
            }

            $attachmentFile = $this->request->getFile('attachment');
            if ($attachmentFile && $attachmentFile->isValid() && $attachmentFile->getName() !== '') {
                $validation = $this->validateFinanceAttachment($attachmentFile);
                if ($validation !== null) {
                    $this->db->transRollback();
                    return redirect()->back()->withInput()->with('error', $validation);
                }
                $financeRecord = $this->financeModel->find($financeId) ?: ['id' => $financeId, 'paid_date' => ($expensePaymentState['paid_date'] ?: date('Y-m-d'))];
                $archiveName = $this->generateFinanceArchiveName($financeRecord, (int) $clinicId, 'attachment', $attachmentFile);
                $this->attachmentModel->supersedePrevious($clinicId, 'finance', $financeId, 'attachment');
                $this->storageService->store($attachmentFile, $clinicId, 'finance', $financeId, 'attachment', [
                    'display_name' => $archiveName,
                ]);
            }
            $this->syncFinanceSettlementStatus($financeId, (int) $clinicId);
            $this->activityLogger->logFinanceActivity(
                'create',
                $financeId,
                "New expense of " . settings()->getCurrencySymbol() . number_format($totalAmount, 2) . " recorded for " . ($financeData['counterparty_name'] ?: 'counterparty')
            );

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Failed to create expense.');
            }
            $this->db->transCommit();

            $returnTo = $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['finance', 'finance/payables', 'inventory/suppliers']);
            return $this->redirectAfterWorkflow($returnTo ?: 'finance/' . $financeId, ['finance', 'finance/payables', 'inventory/suppliers'])
                ->with('success', 'Expense has been recorded successfully.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Expense create failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function store()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $selectedChargeLineIds = $this->selectedClinicalChargeLineIds();
        $usingClinicalChargeLines = $this->request->getPost('transaction_type') === 'invoice' && $selectedChargeLineIds !== [];
        $rules = [
            'patient_id' => 'required|integer',
            'transaction_type' => 'required|in_list[payment,invoice,refund,adjustment]',
            'amount_original' => ($usingClinicalChargeLines ? 'permit_empty' : 'required|decimal|greater_than[0]'),
            'currency' => ($usingClinicalChargeLines ? 'permit_empty|in_list[ILS,USD,JOD,EUR,GBP]' : 'required|in_list[ILS,USD,JOD,EUR,GBP]'),
            'payment_method' => 'required|in_list[cash,card,bank_transfer,check,insurance,other]',
            'service_type' => 'required|in_list[consultation,treatment,medication,procedure,other]',
            'tax_percentage' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
            'transaction_date' => 'permit_empty|valid_date',
        ];

        if (!$this->validate($rules)) {
            if ($this->request->getPost('source_modal') === 'finance_income_quick_create') {
                return redirect()->to(base_url('finance'))
                    ->withInput()
                    ->with('validation', $this->validator)
                    ->with('error', 'Please review the highlighted fields.');
            }
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $patientId = intval($this->request->getPost('patient_id') ?? 0);
        $examId = intval($this->request->getPost('examination_id') ?? 0);
        $ownershipError = $this->validateOwnedFinanceReferences((int) $clinicId, $patientId ?: null, $examId ?: null);
        if ($ownershipError !== null) {
            return redirect()->back()->withInput()->with('error', $ownershipError);
        }

        $clinicalChargeSelection = ['success' => true, 'rows' => [], 'total' => 0.0, 'currency' => null, 'service_details' => '', 'examination_id' => null];
        if ($usingClinicalChargeLines) {
            $clinicalChargeSelection = $this->chargeLineService->prepareInvoiceSelection((int) $clinicId, $patientId, $selectedChargeLineIds);
            if (!$clinicalChargeSelection['success']) {
                return redirect()->back()->withInput()->with('error', $clinicalChargeSelection['message']);
            }
        }

        // Calculate total amount
        $amountOriginal = $usingClinicalChargeLines
            ? (float) $clinicalChargeSelection['total']
            : floatval($this->request->getPost('amount_original'));
        $currency = $usingClinicalChargeLines
            ? (string) $clinicalChargeSelection['currency']
            : ($this->request->getPost('currency') ?: settings()->get('currency', 'ILS'));
        $baseCurrency = settings()->get('currency', 'ILS');
        $exchangeRate = floatval($this->request->getPost('exchange_rate') ?? 0);
        if ($currency === $baseCurrency) {
            $exchangeRate = $exchangeRate > 0 ? $exchangeRate : 1.0;
        } elseif ($exchangeRate <= 0) {
            return redirect()->back()->withInput()->with('error', 'Exchange rate is required for non-default currency.');
        }

        $amount = $amountOriginal * $exchangeRate;
        $discountAmount = floatval($this->request->getPost('discount_amount') ?? 0);
        $taxPercentage = floatval($this->request->getPost('tax_percentage') ?? 0);
        $taxableBase = max(0, $amount - $discountAmount);
        $taxAmount = $taxableBase * ($taxPercentage / 100);
        $totalAmount = $amount - $discountAmount + $taxAmount;
        $transactionType = $this->request->getPost('transaction_type');
        $dueDate = $this->normalizePostedDateOnly('due_date');
        $paidDate = $this->normalizePostedDateOnly('paid_date');
        $today = date('Y-m-d');
        $transactionDate = $this->resolveFinanceTransactionDate($this->normalizePostedDateOnly('transaction_date'), $paidDate ?: $today);

        $amountPaid = 0;
        $balanceDue = 0;
        $paymentStatus = 'pending';
        if ($transactionType === 'invoice') {
            $amountPaid = 0;
            $balanceDue = $totalAmount;
            $paymentStatus = (!empty($dueDate) && strtotime($dueDate) < strtotime($today)) ? 'overdue' : 'pending';
            $paidDate = null;
        } else {
            $amountPaid = $totalAmount;
            $balanceDue = 0;
            $paymentStatus = 'paid';
            $paidDate = $paidDate ?: $today;
            $dueDate = null;
        }

        $financeData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'examination_id' => $usingClinicalChargeLines && !empty($clinicalChargeSelection['examination_id'])
                ? (int) $clinicalChargeSelection['examination_id']
                : $this->request->getPost('examination_id'),
            'transaction_type' => $transactionType,
            'amount' => $amount,
            'currency' => $currency,
            'amount_original' => $amountOriginal,
            'exchange_rate' => $exchangeRate,
            'payment_method' => $this->request->getPost('payment_method'),
            'liquidity_account' => $this->resolveDirectFinanceLiquidityAccount((string) $transactionType, (string) $this->request->getPost('payment_method')),
            'payment_status' => $paymentStatus,
            'description' => $this->request->getPost('description'),
            'service_type' => $usingClinicalChargeLines ? 'treatment' : $this->request->getPost('service_type'),
            'service_details' => $usingClinicalChargeLines
                ? trim((string) $this->request->getPost('service_details') . "\n" . (string) $clinicalChargeSelection['service_details'])
                : $this->request->getPost('service_details'),
            'discount_amount' => $discountAmount,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'amount_paid' => $amountPaid,
            'balance_due' => $balanceDue,
            'due_date' => $dueDate,
            'paid_date' => $paidDate,
            'transaction_date' => $transactionDate,
            'notes' => $this->request->getPost('notes'),
            'created_by' => $this->resolveActorUserId()
        ];

        try {
            $this->db->transBegin();

            if ($this->financeModel->insert($financeData)) {
            $financeId = $this->financeModel->getInsertID();

            $checkFile = $this->request->getFile('check_scan');
            if ($checkFile && $checkFile->isValid() && ($this->request->getPost('payment_method') === 'check')) {
                $validation = $this->validateCheckScan($checkFile);
                if ($validation !== null) {
                    return redirect()->back()->withInput()->with('error', $validation);
                }
                $financeRecord = $this->financeModel->find($financeId) ?: ['id' => $financeId, 'paid_date' => $paidDate];
                $archiveName = $this->generateFinanceArchiveName($financeRecord, (int) $clinicId, 'check_scan', $checkFile);
                $this->attachmentModel->supersedePrevious($clinicId, 'finance', $financeId, 'check_scan');
                $this->storageService->store($checkFile, $clinicId, 'finance', $financeId, 'check_scan', [
                    'display_name' => $archiveName,
                ]);
            }

            if ($transactionType === 'invoice') {
                $this->syncInvoicePaymentStatus($financeId, session()->get('active_clinic_id'));
            }

            if ($usingClinicalChargeLines && !$this->chargeLineService->markInvoiced((int) $clinicId, $selectedChargeLineIds, (int) $financeId)) {
                $this->db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Failed to link selected clinical charge lines to this invoice.');
            }

            $this->upsertProviderPayouts(
                (int) $clinicId,
                (int) $financeId,
                (int) $financeData['patient_id'],
                (float) $totalAmount
            );

            // Get patient name for the activity log
            $patient = $this->patientModel->find($financeData['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';

            // Log the finance creation activity
            $this->activityLogger->logFinanceActivity(
                'create',
                $financeId,
                "New {$financeData['transaction_type']} of " . settings()->getCurrencySymbol() . number_format($totalAmount, 2) . " created for {$patientName}"
            );

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Failed to create financial transaction.');
            }
            $this->db->transCommit();

            if ($transactionType === 'invoice' && $balanceDue > 0) {
                try {
                    $this->whatsAppWorkflowService->queueInvoicePaymentReminder((int) $clinicId, (int) $financeId, $this->resolveActorUserId(), 'created');
                } catch (\Throwable $e) {
                    log_message('warning', 'WhatsApp invoice reminder enqueue failed for finance_id={finance_id}: {message}', [
                        'finance_id' => $financeId,
                        'message' => substr($e->getMessage(), 0, 255),
                    ]);
                }
            }

            $returnTo = $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['patient', 'patients', 'finance', 'examination', 'treatment', 'treatments']);
            if ($returnTo !== null && !empty($financeData['patient_id'])) {
                if (preg_match('#^patient/(\\d+)$#', $returnTo, $matches) && (int) $matches[1] !== (int) $financeData['patient_id']) {
                    $returnTo = null;
                }
            }
            if ($returnTo !== null) {
                return $this->redirectAfterWorkflow($returnTo, ['patient', 'patients', 'finance', 'examination', 'treatment', 'treatments'])
                    ->with('success', 'Financial transaction has been created successfully!');
            }

                return $this->redirectAfterWorkflow('finance', ['patient', 'patients', 'finance', 'examination', 'treatment', 'treatments'], null, false)
                    ->with('success', 'Financial transaction has been created successfully!');
            } else {
                $this->db->transRollback();
                $errors = $this->financeModel->errors();
                log_message('error', 'Finance creation failed: ' . json_encode($errors));
                log_message('error', 'Finance data that failed: ' . json_encode($financeData));
                return redirect()->back()->withInput()->with('error', 'Failed to create financial transaction. Please check your input and try again.');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Finance create failed with provider split: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    private function selectedClinicalChargeLineIds(): array
    {
        return $this->normalizeClinicalChargeLineIdList($this->request->getPost('clinical_charge_line_ids') ?? []);
    }

    private function normalizeClinicalChargeLineIdList($ids): array
    {
        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
    }

    public function show($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $finance = $this->financeModel
            ->select('finances.*, users.first_name as creator_first_name, users.last_name as creator_last_name')
            ->join('users', 'users.id = finances.created_by', 'left')
            ->where('finances.clinic_id', $clinicId)
            ->where('finances.id', $id)
            ->first();

        if (!$finance) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Financial transaction not found');
        }

        $patient = !empty($finance['patient_id'])
            ? $this->patientModel->where('clinic_id', $clinicId)->find($finance['patient_id'])
            : null;
        $supplier = !empty($finance['supplier_id'])
            ? $this->supplierModel->where('clinic_id', $clinicId)->find((int) $finance['supplier_id'])
            : null;
        $examination = !empty($finance['examination_id'])
            ? $this->examinationModel->where('clinic_id', $clinicId)->find($finance['examination_id'])
            : null;
        $payments = [];
        $paidTotal = 0;
        $financeGrossAmount = floatval($finance['total_amount'] ?? ($finance['amount'] - $finance['discount_amount'] + $finance['tax_amount']));
        $providerPayouts = $this->providerPayoutModel->getRowsByFinanceId((int) $clinicId, (int) $id);
        $providerPayoutSummary = [
            'gross_amount' => $financeGrossAmount,
            'provider_share_amount' => 0.0,
            'provider_paid_amount' => 0.0,
            'provider_balance_due' => 0.0,
            'clinic_share_amount' => 0.0,
        ];
        foreach ($providerPayouts as &$providerPayout) {
            if (!empty($providerPayout['provider_user_id'])) {
                $providerUser = $this->userModel
                    ->select('first_name, last_name')
                    ->find((int) $providerPayout['provider_user_id']);
                if ($providerUser) {
                    $providerPayout['provider_first_name'] = $providerUser['first_name'] ?? '';
                    $providerPayout['provider_last_name'] = $providerUser['last_name'] ?? '';
                }
            }
            $providerPayout['payments'] = $this->providerPayoutPaymentModel
                ->where('clinic_id', $clinicId)
                ->where('provider_payout_id', $providerPayout['id'])
                ->orderBy('paid_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->findAll();

            $providerPayoutSummary['provider_share_amount'] += (float) ($providerPayout['provider_share_amount'] ?? 0);
            $providerPayoutSummary['provider_paid_amount'] += (float) ($providerPayout['provider_paid_amount'] ?? 0);
            $providerPayoutSummary['provider_balance_due'] += (float) ($providerPayout['provider_balance_due'] ?? 0);
        }
        unset($providerPayout);
        $providerPayoutSummary['clinic_share_amount'] = max(0, round($providerPayoutSummary['gross_amount'] - $providerPayoutSummary['provider_share_amount'], 2));

        if (in_array(($finance['transaction_type'] ?? ''), ['invoice', 'expense'], true)) {
            $payments = $this->financePaymentModel
                ->select('finance_payments.*, users.first_name as creator_first_name, users.last_name as creator_last_name')
                ->join('users', 'users.id = finance_payments.created_by', 'left')
                ->where('finance_payments.clinic_id', $clinicId)
                ->where('finance_payments.finance_id', $finance['id'])
                ->orderBy('payment_date', 'DESC')
                ->orderBy('finance_payments.id', 'DESC')
                ->findAll();
            $paidTotal = $this->financePaymentModel->getNetSettledAmount($clinicId, $finance['id']);
        }

        $data = [
            'title' => 'Financial Transaction Details - ' . $finance['transaction_id'],
            'finance' => $finance,
            'patient' => $patient,
            'supplier' => $supplier,
            'examination' => $examination,
            'payments' => $payments,
            'paid_total' => $paidTotal,
            'billed_clinical_charge_lines' => $this->chargeLineService->getRowsForFinance((int) $clinicId, (int) $finance['id']),
            'provider_payouts' => $providerPayouts,
            'provider_payout_summary' => $providerPayoutSummary,
            'provider_payout_payment_history' => $this->getProviderPayoutPaymentHistoryByFinanceId((int) $clinicId, (int) $finance['id']),
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => $this->getFinanceCurrencyOptions(),
            'liquidity_account_options' => $this->getLiquidityAccountOptions(),
            'check_outgoing_liquidity_options' => $this->getCheckOutgoingLiquidityOptions(),
            'supports_payment_exchange_fields' => $this->supportsFinancePaymentExchangeFields(),
            'check_attachment' => $this->attachmentModel
                ->where('clinic_id', $clinicId)
                ->where('entity_type', 'finance')
                ->where('entity_id', $finance['id'])
                ->where('purpose', 'check_scan')
                ->first(),
            'finance_attachment' => $this->attachmentModel
                ->where('clinic_id', $clinicId)
                ->where('entity_type', 'finance')
                ->where('entity_id', $finance['id'])
                ->where('purpose', 'attachment')
                ->first(),
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'finance', 'inventory/suppliers'])
        ];

        return $this->view('finance/show', $data);
    }

    private function getProviderPayoutPaymentHistoryByFinanceId(int $clinicId, int $financeId): array
    {
        $rows = $this->db->table('provider_payout_payments ppp')
            ->select("
                ppp.id,
                ppp.provider_payout_id,
                ppp.amount,
                ppp.payment_method,
                ppp.liquidity_account,
                ppp.paid_date,
                ppp.notes,
                ppp.created_at,
                ppp.created_by,
                u.first_name AS creator_first_name,
                u.last_name AS creator_last_name
            ")
            ->join('provider_payouts pp', 'pp.id = ppp.provider_payout_id AND pp.clinic_id = ppp.clinic_id', 'inner')
            ->join('users u', 'u.id = ppp.created_by', 'left')
            ->where('ppp.clinic_id', $clinicId)
            ->where('pp.finance_id', $financeId)
            ->orderBy('ppp.paid_date', 'DESC')
            ->orderBy('ppp.id', 'DESC')
            ->get()
            ->getResultArray();

        $history = [];
        foreach ($rows as $row) {
            $creatorName = trim((string) (($row['creator_first_name'] ?? '') . ' ' . ($row['creator_last_name'] ?? '')));
            $row['created_by_label'] = $creatorName !== '' ? $creatorName : null;
            $history[(int) ($row['provider_payout_id'] ?? 0)][] = $row;
        }

        return $history;
    }

    public function edit($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to edit transactions.');
        }

        $finance = $this->financeModel
            ->select('finances.*, users.first_name as creator_first_name, users.last_name as creator_last_name')
            ->join('users', 'users.id = finances.created_by', 'left')
            ->where('finances.clinic_id', $clinicId)
            ->where('finances.id', $id)
            ->first();

        if (!$finance) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Financial transaction not found');
        }

        // S4-02f: Enforce clinic ownership
        if ($finance['clinic_id'] != $clinicId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Financial transaction not found in current clinic');
        }

        if (($finance['transaction_type'] ?? '') === 'expense') {
            $data = $this->buildExpenseViewData($finance);
            $data['return_to'] = $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['finance', 'finance/payables', 'inventory/suppliers']);
            return $this->view('finance/expense_edit', $data);
        }

        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($finance['patient_id']);
        $examination = $finance['examination_id'] ? $this->examinationModel->where('clinic_id', $clinicId)->find($finance['examination_id']) : null;

        $data = [
            'title' => 'Edit Financial Transaction - ' . $finance['transaction_id'],
            'finance' => $finance,
            'patients' => $patient ? [$patient] : [],
            'examinations' => $examination ? [$examination] : [],
            'clinic_providers' => $this->getClinicProviders((int) $clinicId),
            'provider_payouts' => $this->providerPayoutModel->getRowsByFinanceId((int) $clinicId, (int) $id),
            'validation' => \Config\Services::validation(),
            'loadSelect2' => true,
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => $this->getFinanceCurrencyOptions(),
            'liquidity_account_options' => $this->getLiquidityAccountOptions(),
            'check_outgoing_liquidity_options' => $this->getCheckOutgoingLiquidityOptions(),
            'check_attachment' => $this->attachmentModel
                ->where('clinic_id', $clinicId)
                ->where('entity_type', 'finance')
                ->where('entity_id', $finance['id'])
                ->where('purpose', 'check_scan')
                ->first(),
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'finance'])
        ];

        return $this->view('finance/edit', $data);
    }

    public function update($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $finance = $this->financeModel->findByClinic($clinicId, $id);

        if (!$finance) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Financial transaction not found');
        }

        $rules = [
            'patient_id' => 'required|integer',
            'transaction_type' => 'required|in_list[payment,invoice,refund,adjustment]',
            'amount_original' => 'required|decimal|greater_than[0]',
            'currency' => 'required|in_list[ILS,USD,JOD,EUR,GBP]',
            'payment_method' => 'required|in_list[cash,card,bank_transfer,check,insurance,other]',
            'service_type' => 'required|in_list[consultation,treatment,medication,procedure,other]',
            'tax_percentage' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
            'transaction_date' => 'permit_empty|valid_date',
        ];

        if (($finance['transaction_type'] ?? '') === 'expense') {
            return $this->updateExpense($id, $finance, (int) $clinicId);
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $patientId = intval($this->request->getPost('patient_id') ?? 0);
        $examId = intval($this->request->getPost('examination_id') ?? 0);
        $ownershipError = $this->validateOwnedFinanceReferences((int) $clinicId, $patientId ?: null, $examId ?: null);
        if ($ownershipError !== null) {
            return redirect()->back()->withInput()->with('error', $ownershipError);
        }

        $amountOriginal = floatval($this->request->getPost('amount_original'));
        $currency = $this->request->getPost('currency') ?: settings()->get('currency', 'ILS');
        $baseCurrency = settings()->get('currency', 'ILS');
        $exchangeRate = floatval($this->request->getPost('exchange_rate') ?? 0);
        if ($currency === $baseCurrency) {
            $exchangeRate = $exchangeRate > 0 ? $exchangeRate : 1.0;
        } elseif ($exchangeRate <= 0) {
            return redirect()->back()->withInput()->with('error', 'Exchange rate is required for non-default currency.');
        }

        $amount = $amountOriginal * $exchangeRate;
        $discountAmount = floatval($this->request->getPost('discount_amount') ?? 0);
        $taxPercentage = floatval($this->request->getPost('tax_percentage') ?? 0);
        $taxableBase = max(0, $amount - $discountAmount);
        $taxAmount = $taxableBase * ($taxPercentage / 100);
        $totalAmount = $amount - $discountAmount + $taxAmount;
        $requestedType = (string) $this->request->getPost('transaction_type');
        $currentType = (string) ($finance['transaction_type'] ?? '');
        if ($requestedType !== $currentType) {
            return redirect()->back()->withInput()->with('error', 'Transaction type cannot be changed after creation.');
        }

        $amountPaid = floatval($finance['amount_paid'] ?? 0);
        $paymentStatus = (string) ($finance['payment_status'] ?? 'pending');
        $dueDate = $this->normalizePostedDateOnly('due_date');
        $paidDate = $this->normalizePostedDateOnly('paid_date');
        $transactionDate = $this->resolveFinanceTransactionDate(
            $this->normalizePostedDateOnly('transaction_date'),
            $paidDate ?: ($finance['paid_date'] ?? null)
        );
        if ($currentType !== 'invoice') {
            $amountPaid = $totalAmount;
            $paymentStatus = 'paid';
            $paidDate = $paidDate ?: date('Y-m-d');
            $dueDate = null;
            $transactionDate = $this->resolveFinanceTransactionDate($transactionDate, $paidDate);
        }

        if ($currentType === 'invoice' && $amountPaid > $totalAmount) {
            return redirect()->back()->withInput()->with('error', 'Invoice total cannot be less than the amount already paid.');
        }

        $financeData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'examination_id' => $this->request->getPost('examination_id'),
            'transaction_type' => $currentType,
            'amount' => $amount,
            'currency' => $currency,
            'amount_original' => $amountOriginal,
            'exchange_rate' => $exchangeRate,
            'payment_method' => $this->request->getPost('payment_method'),
            'liquidity_account' => $this->resolveDirectFinanceLiquidityAccount((string) $currentType, (string) $this->request->getPost('payment_method')),
            'payment_status' => $paymentStatus,
            'description' => $this->request->getPost('description'),
            'service_type' => $this->request->getPost('service_type'),
            'service_details' => $this->request->getPost('service_details'),
            'discount_amount' => $discountAmount,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'amount_paid' => $amountPaid,
            'balance_due' => max(0, $totalAmount - $amountPaid),
            'due_date' => $dueDate,
            'paid_date' => $paidDate,
            'transaction_date' => $transactionDate,
            'notes' => $this->request->getPost('notes')
        ];

        try {
            $this->db->transBegin();

            if ($this->financeModel->update($id, $financeData)) {
                $checkFile = $this->request->getFile('check_scan');
                if ($checkFile && $checkFile->isValid() && ($this->request->getPost('payment_method') === 'check')) {
                    $validation = $this->validateCheckScan($checkFile);
                    if ($validation !== null) {
                        return redirect()->back()->withInput()->with('error', $validation);
                    }
                    $financeRecord = $this->financeModel->find($id) ?: ['id' => $id, 'paid_date' => $paidDate];
                    $archiveName = $this->generateFinanceArchiveName($financeRecord, (int) $clinicId, 'check_scan', $checkFile);
                    $this->attachmentModel->supersedePrevious($clinicId, 'finance', $id, 'check_scan');
                    $this->storageService->store($checkFile, $clinicId, 'finance', $id, 'check_scan', [
                        'display_name' => $archiveName,
                    ]);
                }

                if ($financeData['transaction_type'] === 'invoice') {
                    $this->syncInvoicePaymentStatus($id, $clinicId);
                }

                $this->upsertProviderPayouts(
                    (int) $clinicId,
                    (int) $id,
                    (int) $financeData['patient_id'],
                    (float) $totalAmount
                );

                // Get patient name for the activity log (scoped)
                $patient = $this->patientModel->where('clinic_id', $clinicId)->find($financeData['patient_id']);
                $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';

                // Log the finance update activity
                $this->activityLogger->logFinanceActivity(
                    'update',
                    $id,
                    "Financial transaction updated for {$patientName} - {$financeData['transaction_type']} of " . settings()->getCurrencySymbol() . number_format($financeData['amount'], 2)
                );

                if ($this->db->transStatus() === false) {
                    $this->db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Failed to update financial transaction.');
                }
                $this->db->transCommit();

                $returnTo = $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['patient', 'patients', 'finance']);
                if ($returnTo) {
                    return $this->redirectAfterWorkflow($returnTo, ['patient', 'patients', 'finance'])
                        ->with('success', 'Financial transaction has been updated successfully!');
                }

                return $this->redirectAfterWorkflow('finance/' . $id, ['patient', 'patients', 'finance'])
                    ->with('success', 'Financial transaction has been updated successfully!');
            }

            $this->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to update financial transaction. Please check your input and try again.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Finance update failed with provider split: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    private function updateExpense(int $id, array $finance, int $clinicId)
    {
        $rules = [
            'counterparty_name' => 'required|max_length[150]',
            'supplier_id' => 'permit_empty|integer',
            'expense_category' => 'required|in_list[' . implode(',', array_keys($this->getExpenseCategoryOptions())) . ']',
            'amount_original' => 'required|decimal|greater_than[0]',
            'currency' => 'required|in_list[ILS,USD,JOD,EUR,GBP]',
            'payment_method' => 'required|in_list[cash,card,bank_transfer,check,insurance,other]',
            'payment_status' => 'permit_empty|in_list[pending,partial,paid]',
            'amount_paid' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'tax_percentage' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
            'transaction_date' => 'permit_empty|valid_date',
            'paid_date' => 'permit_empty|valid_date',
            'due_date' => 'permit_empty|valid_date',
        ];
        if (!$this->validate($rules)) {
            if ($this->request->getPost('source_modal') === 'finance_expense_quick_create') {
                return redirect()->to(base_url('finance'))
                    ->withInput()
                    ->with('validation', $this->validator)
                    ->with('error', 'Please review the highlighted fields.');
            }
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $amountOriginal = floatval($this->request->getPost('amount_original'));
        try {
            [$supplierId, $counterpartyName] = $this->resolveExpenseSupplierSelection((int) $clinicId);
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
        $currency = $this->request->getPost('currency') ?: settings()->get('currency', 'ILS');
        $baseCurrency = settings()->get('currency', 'ILS');
        $exchangeRate = floatval($this->request->getPost('exchange_rate') ?? 0);
        if ($currency === $baseCurrency) {
            $exchangeRate = $exchangeRate > 0 ? $exchangeRate : 1.0;
        } elseif ($exchangeRate <= 0) {
            return redirect()->back()->withInput()->with('error', 'Exchange rate is required for non-default currency.');
        }

        $amount = $amountOriginal * $exchangeRate;
        $discountAmount = floatval($this->request->getPost('discount_amount') ?? 0);
        $taxPercentage = floatval($this->request->getPost('tax_percentage') ?? 0);
        $taxableBase = max(0, $amount - $discountAmount);
        $taxAmount = $taxableBase * ($taxPercentage / 100);
        $totalAmount = $amount - $discountAmount + $taxAmount;
        $existingSettledAmount = $this->financePaymentModel->getNetSettledAmount($clinicId, $id);
        $hasSettlementEntries = $this->financePaymentModel->hasSettlementEntries($clinicId, $id);
        if ($hasSettlementEntries && $existingSettledAmount - $totalAmount > 0.0001) {
            return redirect()->back()->withInput()->with('error', 'Expense total cannot be less than the amount already settled.');
        }

        $expensePaymentState = $this->buildExpensePaymentState(
            $totalAmount,
            $hasSettlementEntries ? $existingSettledAmount : null,
            $finance['paid_date'] ?? null
        );
        $transactionDate = $this->resolveFinanceTransactionDate(
            $this->normalizePostedDateOnly('transaction_date'),
            $expensePaymentState['paid_date'] ?: ($finance['paid_date'] ?? null)
        );

        $update = [
            'patient_id' => null,
            'examination_id' => null,
            'transaction_type' => 'expense',
            'amount' => $amount,
            'currency' => $currency,
            'amount_original' => $amountOriginal,
            'exchange_rate' => $exchangeRate,
            'payment_method' => $this->request->getPost('payment_method'),
            'liquidity_account' => null,
            'payment_status' => $expensePaymentState['payment_status'],
            'description' => $this->request->getPost('description'),
            'service_type' => 'other',
            'expense_category' => $this->request->getPost('expense_category'),
            'counterparty_name' => $counterpartyName,
            'supplier_id' => $supplierId,
            'service_details' => $this->request->getPost('service_details'),
            'discount_amount' => $discountAmount,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'amount_paid' => $expensePaymentState['amount_paid'],
            'balance_due' => $expensePaymentState['balance_due'],
            'due_date' => $expensePaymentState['due_date'],
            'paid_date' => $expensePaymentState['paid_date'],
            'transaction_date' => $transactionDate,
            'notes' => $this->request->getPost('notes'),
        ];

        try {
            $this->db->transBegin();

            if (!$this->financeModel->update($id, $update)) {
                $this->db->transRollback();
                return redirect()->back()->withInput()->with('error', implode(', ', $this->financeModel->errors()));
            }

            if (!$hasSettlementEntries) {
                $this->financePaymentModel->where('clinic_id', $clinicId)
                    ->where('finance_id', $id)
                    ->delete();

                if (($expensePaymentState['amount_paid'] ?? 0) > 0) {
                    $paymentData = [
                        'finance_id' => $id,
                        'amount' => (float) $expensePaymentState['amount_paid'],
                        'type' => 'payment',
                        'payment_method' => $update['payment_method'],
                        'liquidity_account' => $this->resolveExpenseOutgoingLiquidityAccount((string) $update['payment_method']),
                        'payment_date' => $expensePaymentState['paid_date'] ?: date('Y-m-d'),
                        'notes' => 'Initial expense settlement',
                        'created_by' => $this->resolveActorUserId(),
                    ];
                    if ($this->supportsFinancePaymentExchangeFields()) {
                        $paymentData['currency'] = $update['currency'];
                        $paymentData['amount_original'] = round(((float) $expensePaymentState['amount_paid']) / max(0.0001, (float) $update['exchange_rate']), 2);
                        $paymentData['exchange_rate'] = $update['exchange_rate'];
                    }
                    if (!$this->financePaymentModel->insert($paymentData)) {
                        $this->db->transRollback();
                        return redirect()->back()->withInput()->with('error', 'Failed to record the initial expense payment.');
                    }
                }
            }

            $attachmentFile = $this->request->getFile('attachment');
            if ($attachmentFile && $attachmentFile->isValid() && $attachmentFile->getName() !== '') {
                $validation = $this->validateFinanceAttachment($attachmentFile);
                if ($validation !== null) {
                    $this->db->transRollback();
                    return redirect()->back()->withInput()->with('error', $validation);
                }
                $financeRecord = $this->financeModel->find($id) ?: ['id' => $id, 'paid_date' => ($expensePaymentState['paid_date'] ?: date('Y-m-d'))];
                $archiveName = $this->generateFinanceArchiveName($financeRecord, (int) $clinicId, 'attachment', $attachmentFile);
                $this->attachmentModel->supersedePrevious($clinicId, 'finance', $id, 'attachment');
                $this->storageService->store($attachmentFile, $clinicId, 'finance', $id, 'attachment', [
                    'display_name' => $archiveName,
                ]);
            }

            $this->syncFinanceSettlementStatus($id, $clinicId);

            $this->activityLogger->logFinanceActivity(
                'update',
                $id,
                "Expense transaction updated for " . ($update['counterparty_name'] ?: 'counterparty')
            );

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Failed to update expense.');
            }
            $this->db->transCommit();

            $returnTo = $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['finance', 'finance/payables', 'inventory/suppliers']);
            return $this->redirectAfterWorkflow($returnTo ?: 'finance/' . $id, ['finance', 'finance/payables', 'inventory/suppliers'])
                ->with('success', 'Expense updated successfully.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Expense update failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
                }
                return redirect()->to('/clinic/select');
            }

            $finance = $this->financeModel->findByClinic($clinicId, $id);

            if (!$finance) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Financial transaction not found']);
                } else {
                    return redirect()->back()->with('error', 'Financial transaction not found');
                }
            }

            if ($this->financeModel->delete($id)) {
                // Get patient name for the activity log (scoped)
                $patient = $this->patientModel->where('clinic_id', $clinicId)->find($finance['patient_id']);
                $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';

                // Calculate total amount for the activity log
                $totalAmount = floatval($finance['amount']) - floatval($finance['discount_amount'] ?? 0) + floatval($finance['tax_amount'] ?? 0);

                // Log the finance deletion activity
                $this->activityLogger->logFinanceActivity(
                    'delete',
                    $id,
                    "Financial transaction deleted for {$patientName} - {$finance['transaction_type']} of " . settings()->getCurrencySymbol() . number_format($totalAmount, 2)
                );

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => true, 'message' => 'Financial transaction has been deleted successfully!']);
                } else {
                    $returnTo = $this->sanitizeInternalReturnTo(
                        (string) ($this->request->getPost('return_to') ?: $this->request->getGet('return_to')),
                        ['patient', 'patients', 'finance']
                    );
                    if ($returnTo) {
                        return redirect()->to(base_url($returnTo))->with('success', 'Financial transaction has been deleted successfully!');
                    }
                    return redirect()->to(base_url('finance'))->with('success', 'Financial transaction has been deleted successfully!');
                }
            } else {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete transaction. Please try again.']);
                } else {
                    return redirect()->back()->with('error', 'Failed to delete transaction. Please try again.');
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception in delete - ID: ' . $id . ', Error: ' . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
            } else {
                return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
            }
        }
    }

    public function reports()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }
        $this->requireFinanceReportsFeature((int) $clinicId);

        $startDate = $this->normalizeDateOnly($this->request->getGet('start_date')) ?? date('Y-m-01');
        $endDate = $this->normalizeDateOnly($this->request->getGet('end_date')) ?? date('Y-m-d');
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $ledger = $this->buildFinancialLedger((int) $clinicId, $startDate, $endDate);
        $stats = $this->summarizeCashReport($ledger['rows']);
        $monthlyRevenue = $this->buildMonthlyCollectionsSeries($ledger['rows'], $startDate, $endDate);
        $arAgingDetail = $this->getHistoricalAccountsReceivableAgingDetail((int) $clinicId, $endDate, 100);
        $arAgingSummary = $this->summarizeAccountsReceivableBuckets($arAgingDetail, $endDate);
        $apAgingDetail = $this->financeModel->getAccountsPayableAgingDetailByClinic((int) $clinicId, $endDate, 100);
        $apAgingSummary = $this->financeModel->getAccountsPayableAgingSummaryByClinic((int) $clinicId, $endDate);
        $providerPayoutStats = $this->providerPayoutModel->getStatsByClinic((int) $clinicId, null, $endDate);
        $treasuryMovement = $this->financeTreasuryService->summarizeMovement($ledger['treasury_rows']);
        $treasuryClosing = $this->financeTreasuryService->summarizeClosingBalances(
            $this->buildFinancialLedger((int) $clinicId, null, $endDate)['treasury_rows']
        );

        $moneyIn = (float) ($stats['gross_collections'] ?? 0);
        $moneyOut = (float) (($stats['refunds_total'] ?? 0) + ($stats['expenses_paid'] ?? 0) + ($stats['provider_paid_cash_total'] ?? 0));
        $customersOweUs = (float) ($arAgingSummary['total_balance_due'] ?? 0);
        $expensePayables = (float) ($apAgingSummary['total_balance_due'] ?? 0);
        $providerPayables = (float) ($providerPayoutStats['total_provider_balance'] ?? 0);
        $weOweOthers = $expensePayables + $providerPayables;
        $netPosition = $moneyIn - $moneyOut + $customersOweUs - $weOweOthers;

        $data = [
            'title' => 'Financial Reports',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'stats' => $stats,
            'treasury_movement' => $treasuryMovement,
            'treasury_closing' => $treasuryClosing,
            'liquidity_account_labels' => $this->getLiquidityAccountOptions(),
            'monthly_revenue_labels' => $monthlyRevenue['labels'],
            'monthly_revenue_values' => $monthlyRevenue['values'],
            'ledger_rows' => $ledger['rows'],
            'ledger_summary' => $ledger['summary'],
            'ar_aging_summary' => $arAgingSummary,
            'ar_aging_detail' => $arAgingDetail,
            'ap_aging_summary' => $apAgingSummary,
            'ap_aging_detail' => $apAgingDetail,
            'owner_summary' => [
                'money_in' => round($moneyIn, 2),
                'money_out' => round($moneyOut, 2),
                'customers_owe_us' => round($customersOweUs, 2),
                'we_owe_others' => round($weOweOthers, 2),
                'expense_payables' => round($expensePayables, 2),
                'provider_payables' => round($providerPayables, 2),
                'net_position' => round($netPosition, 2),
                'as_of_date' => $endDate,
            ],
            'currency_position' => settings()->get('currency_position', 'before'),
            'currency_symbol' => settings()->getCurrencySymbol()
        ];

        return $this->view('finance/reports', $data);
    }

    public function transfers()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $transfers = $this->financeLiquidityTransferModel
            ->where('clinic_id', (int) $clinicId)
            ->orderBy('transfer_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll(100);

        return $this->view('finance/transfers', [
            'title' => 'Treasury Transfers',
            'transfers' => $transfers,
            'can_access_finance_reports' => $this->canAccessFinanceReports((int) $clinicId),
            'liquidity_account_options' => $this->getLiquidityAccountOptions(),
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function payables()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $asOfDate = $this->normalizeDateOnly($this->request->getGet('as_of')) ?? date('Y-m-d');
        $rows = $this->financeModel->getAccountsPayableAgingDetailByClinic((int) $clinicId, $asOfDate, 500);
        $agingSummary = $this->financeModel->getAccountsPayableAgingSummaryByClinic((int) $clinicId, $asOfDate);

        $summary = [
            'open_count' => count($rows),
            'open_balance' => 0.0,
            'document_total' => 0.0,
            'overdue_count' => 0,
            'overdue_balance' => 0.0,
        ];

        foreach ($rows as $row) {
            $balance = (float) ($row['balance_due'] ?? 0);
            $summary['open_balance'] += $balance;
            $summary['document_total'] += (float) ($row['total_amount'] ?? 0);
            if ((int) ($row['days_past_due'] ?? 0) > 0) {
                $summary['overdue_count']++;
                $summary['overdue_balance'] += $balance;
            }
        }

        return $this->view('finance/payables', [
            'title' => 'Supplier Payables',
            'as_of_date' => $asOfDate,
            'rows' => $rows,
            'summary' => $summary,
            'aging_summary' => $agingSummary,
            'can_access_finance_reports' => $this->canAccessFinanceReports((int) $clinicId),
            'liquidity_account_options' => $this->getLiquidityAccountOptions(),
            'currency_symbol' => settings()->getCurrencySymbol(),
            'currency_position' => settings()->get('currency_position', 'before'),
        ]);
    }

    public function storeTransfer()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $rules = [
            'from_account' => 'required|in_list[safe_cash,bank,undeposited_checks]',
            'to_account' => 'required|in_list[safe_cash,bank,undeposited_checks]',
            'amount' => 'required|decimal|greater_than[0]',
            'transfer_date' => 'required|valid_date',
            'transfer_type' => 'required|in_list[cash_deposit_to_bank,bank_withdrawal_to_safe,check_deposit_to_bank,internal_transfer]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $from = (string) $this->request->getPost('from_account');
        $to = (string) $this->request->getPost('to_account');
        if ($from === $to) {
            return redirect()->back()->withInput()->with('error', 'Transfer source and destination must be different.');
        }

        $payload = [
            'clinic_id' => (int) $clinicId,
            'from_account' => $from,
            'to_account' => $to,
            'amount' => round((float) ($this->request->getPost('amount') ?? 0), 2),
            'transfer_date' => (string) ($this->normalizePostedDateOnly('transfer_date') ?: date('Y-m-d')),
            'transfer_type' => (string) $this->request->getPost('transfer_type'),
            'notes' => trim((string) ($this->request->getPost('notes') ?? '')),
            'created_by' => $this->resolveActorUserId(),
        ];

        if (!$this->financeLiquidityTransferModel->insert($payload)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->financeLiquidityTransferModel->errors()));
        }

        return redirect()->to(base_url('finance/transfers'))->with('success', 'Treasury transfer recorded successfully.');
    }

    private function buildFinancialLedger(int $clinicId, ?string $startDate, string $endDate): array
    {
        $baseCurrency = settings()->get('currency', 'ILS');
        $directReportDateExpr = "CASE WHEN f.transaction_type = 'expense' THEN COALESCE(f.paid_date, f.transaction_date, DATE(f.created_at)) ELSE " . $this->financeDocumentDateExpr('f') . " END";
        $financeAmountExpr = $this->db->fieldExists('total_amount', 'finances')
            ? 'f.total_amount'
            : '(f.amount - COALESCE(f.discount_amount, 0) + COALESCE(f.tax_amount, 0))';
        $rows = [];

        $financeRowsBuilder = $this->db->table('finances f')
            ->select("
                f.id,
                f.transaction_id,
                f.transaction_type,
                f.payment_method,
                f.liquidity_account,
                f.payment_status,
                f.description,
                f.created_at,
                f.paid_date,
                f.transaction_date,
                f.currency,
                f.exchange_rate,
                f.amount_original,
                f.amount_paid,
                f.counterparty_name,
                f.expense_category,
                f.amount,
                f.discount_amount,
                f.tax_amount,
                (
                    SELECT COUNT(*)
                    FROM finance_payments fp_exists
                    WHERE fp_exists.clinic_id = f.clinic_id
                      AND fp_exists.finance_id = f.id
                ) AS settlement_count,
                {$financeAmountExpr} AS amount_base,
                p.first_name AS patient_first_name,
                p.last_name AS patient_last_name,
                p.patient_id AS patient_number
            ")
            ->join('patients p', 'p.id = f.patient_id AND p.clinic_id = f.clinic_id', 'left')
            ->where('f.clinic_id', $clinicId)
            ->whereIn('f.transaction_type', ['payment', 'refund', 'expense']);
        if ($startDate !== null && $startDate !== '') {
            $financeRowsBuilder = $financeRowsBuilder->where($directReportDateExpr . ' >= ' . $this->db->escape($startDate), null, false);
        }
        $financeRows = $financeRowsBuilder
            ->where($directReportDateExpr . ' <= ' . $this->db->escape($endDate), null, false)
            ->orderBy($directReportDateExpr, 'DESC', false)
            ->orderBy('f.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($financeRows as $row) {
            $amountBase = isset($row['amount_base']) ? floatval($row['amount_base']) : 0.0;
            $type = (string) ($row['transaction_type'] ?? '');
            if ($type === 'expense') {
                $settlementCount = (int) ($row['settlement_count'] ?? 0);
                $legacyPaidAmount = round(floatval($row['amount_paid'] ?? 0), 2);
                if ($settlementCount > 0 || $legacyPaidAmount <= 0) {
                    continue;
                }
                $amountBase = min($amountBase, $legacyPaidAmount);
            }
            $signedAmount = in_array($type, ['refund', 'expense'], true) ? -$amountBase : $amountBase;
            $patientName = trim((string) (($row['patient_first_name'] ?? '') . ' ' . ($row['patient_last_name'] ?? '')));
            $reportDate = $type === 'expense'
                ? (!empty($row['paid_date']) ? (string) $row['paid_date'] : (!empty($row['transaction_date']) ? (string) $row['transaction_date'] : substr((string) ($row['created_at'] ?? ''), 0, 10)))
                : (!empty($row['transaction_date']) ? (string) $row['transaction_date'] : (!empty($row['paid_date']) ? (string) $row['paid_date'] : substr((string) ($row['created_at'] ?? ''), 0, 10)));

            $rows[] = [
                'finance_id' => (int) ($row['id'] ?? 0),
                'provider_payout_id' => null,
                'event_source' => 'direct_transaction',
                'source_label' => 'Direct transaction',
                'event_type' => $type,
                'event_at' => $reportDate . ' 00:00:00',
                'report_date' => $reportDate,
                'created_at' => (string) ($row['created_at'] ?? ''),
                'reference' => (string) ($row['transaction_id'] ?? ('F-' . $row['id'])),
                'patient_label' => $patientName !== '' ? $patientName : null,
                'patient_number' => $row['patient_number'] ?? null,
                'counterparty' => $row['counterparty_name'] ?? null,
                'payment_method' => $row['payment_method'] ?? null,
                'liquidity_account' => $row['liquidity_account'] ?? null,
                'payment_status' => $row['payment_status'] ?? null,
                'notes' => $row['description'] ?? null,
                'category' => $row['expense_category'] ?? null,
                'currency' => $row['currency'] ?: $baseCurrency,
                'exchange_rate' => floatval($row['exchange_rate'] ?? 1),
                'amount_original' => isset($row['amount_original']) ? floatval($row['amount_original']) : null,
                'amount_base' => $amountBase,
                'signed_amount_base' => $signedAmount,
            ];
        }

        $paymentFxSelect = $this->supportsFinancePaymentExchangeFields()
            ? "
                fp.currency AS settlement_currency,
                fp.amount_original AS settlement_amount_original,
                fp.exchange_rate AS settlement_exchange_rate,
            "
            : "
                NULL AS settlement_currency,
                NULL AS settlement_amount_original,
                NULL AS settlement_exchange_rate,
            ";

        $invoicePaymentRowsBuilder = $this->db->table('finance_payments fp')
            ->select("
                fp.id,
                fp.finance_id,
                fp.type,
                fp.payment_method,
                fp.liquidity_account,
                fp.payment_date,
                fp.notes,
                fp.amount,
                f.transaction_type AS finance_transaction_type,
                f.counterparty_name,
                {$paymentFxSelect}
                f.transaction_id AS invoice_transaction_id,
                f.currency,
                p.first_name AS patient_first_name,
                p.last_name AS patient_last_name,
                p.patient_id AS patient_number
            ")
            ->join('finances f', 'f.id = fp.finance_id AND f.clinic_id = fp.clinic_id', 'inner')
            ->join('patients p', 'p.id = f.patient_id AND p.clinic_id = f.clinic_id', 'left')
            ->where('fp.clinic_id', $clinicId);
        if ($startDate !== null && $startDate !== '') {
            $invoicePaymentRowsBuilder = $invoicePaymentRowsBuilder->where('fp.payment_date >=', $startDate);
        }
        $invoicePaymentRows = $invoicePaymentRowsBuilder
            ->where('fp.payment_date <=', $endDate)
            ->whereIn('f.transaction_type', ['invoice', 'expense'])
            ->whereIn('fp.type', ['payment', 'refund', 'void', 'adjustment'])
            ->orderBy('fp.payment_date', 'DESC')
            ->orderBy('fp.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($invoicePaymentRows as $row) {
            $amountBase = floatval($row['amount'] ?? 0);
            $type = (string) ($row['type'] ?? '');
            $financeTransactionType = (string) ($row['finance_transaction_type'] ?? 'invoice');
            $patientName = trim((string) (($row['patient_first_name'] ?? '') . ' ' . ($row['patient_last_name'] ?? '')));

            if ($financeTransactionType === 'expense') {
                $signedAmount = in_array($type, ['refund', 'void'], true) ? $amountBase : -$amountBase;
                $normalizedType = 'expense';
                $eventSource = 'expense_settlement';
                $reference = (string) (($row['invoice_transaction_id'] ?? 'Expense') . ' / EXP-' . ($row['id'] ?? ''));
                $counterparty = $row['counterparty_name'] ?? null;
                $patientLabel = null;
                $patientNumber = null;
            } else {
                $signedAmount = in_array($type, ['refund', 'void'], true) ? -$amountBase : $amountBase;
                $normalizedType = $type === 'void' ? 'void' : $type;
                $eventSource = 'invoice_settlement';
                $reference = (string) (($row['invoice_transaction_id'] ?? 'Invoice') . ' / PMT-' . ($row['id'] ?? ''));
                $counterparty = null;
                $patientLabel = $patientName !== '' ? $patientName : null;
                $patientNumber = $row['patient_number'] ?? null;
            }

            $rows[] = [
                'finance_id' => (int) ($row['finance_id'] ?? 0),
                'provider_payout_id' => null,
                'event_source' => $eventSource,
                'source_label' => $financeTransactionType === 'expense' ? 'Expense settlement' : 'Invoice settlement',
                'event_type' => $normalizedType,
                'event_at' => ((string) ($row['payment_date'] ?? '')) . ' 00:00:00',
                'report_date' => (string) ($row['payment_date'] ?? ''),
                'created_at' => ((string) ($row['payment_date'] ?? '')) . ' 00:00:00',
                'reference' => $reference,
                'patient_label' => $patientLabel,
                'patient_number' => $patientNumber,
                'counterparty' => $counterparty,
                'payment_method' => $row['payment_method'] ?? null,
                'liquidity_account' => $row['liquidity_account'] ?? null,
                'payment_status' => null,
                'notes' => $row['notes'] ?? null,
                'category' => null,
                'currency' => $row['settlement_currency'] ?: ($row['currency'] ?: $baseCurrency),
                'exchange_rate' => floatval($row['settlement_exchange_rate'] ?? 1),
                'amount_original' => isset($row['settlement_amount_original']) ? floatval($row['settlement_amount_original']) : null,
                'amount_base' => $amountBase,
                'signed_amount_base' => $signedAmount,
            ];
        }

        $payoutPaymentRowsBuilder = $this->db->table('provider_payout_payments ppp')
            ->select("
                ppp.id,
                ppp.provider_payout_id,
                ppp.amount,
                ppp.payment_method,
                ppp.liquidity_account,
                ppp.paid_date,
                ppp.notes,
                pp.provider_user_id,
                pp.provider_name_snapshot,
                f.transaction_id,
                p.first_name AS patient_first_name,
                p.last_name AS patient_last_name,
                p.patient_id AS patient_number,
                u.first_name AS provider_first_name,
                u.last_name AS provider_last_name
            ")
            ->join('provider_payouts pp', 'pp.id = ppp.provider_payout_id AND pp.clinic_id = ppp.clinic_id', 'inner')
            ->join('finances f', 'f.id = pp.finance_id AND f.clinic_id = pp.clinic_id', 'left')
            ->join('patients p', 'p.id = pp.patient_id AND p.clinic_id = pp.clinic_id', 'left')
            ->join('users u', 'u.id = pp.provider_user_id', 'left')
            ->where('ppp.clinic_id', $clinicId);
        if ($startDate !== null && $startDate !== '') {
            $payoutPaymentRowsBuilder = $payoutPaymentRowsBuilder->where('ppp.paid_date >=', $startDate);
        }
        $payoutPaymentRows = $payoutPaymentRowsBuilder
            ->where('ppp.paid_date <=', $endDate)
            ->orderBy('ppp.paid_date', 'DESC')
            ->orderBy('ppp.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($payoutPaymentRows as $row) {
            $providerLabel = trim((string) (($row['provider_first_name'] ?? '') . ' ' . ($row['provider_last_name'] ?? '')));
            if ($providerLabel === '') {
                $providerLabel = (string) ($row['provider_name_snapshot'] ?? ('Provider #' . (string) ($row['provider_user_id'] ?? '0')));
            }
            $patientName = trim((string) (($row['patient_first_name'] ?? '') . ' ' . ($row['patient_last_name'] ?? '')));
            $amountBase = floatval($row['amount'] ?? 0);

            $rows[] = [
                'finance_id' => (int) ($row['finance_id'] ?? 0),
                'provider_payout_id' => (int) ($row['provider_payout_id'] ?? 0),
                'event_source' => 'provider_payout_payment',
                'source_label' => 'Provider payout',
                'event_type' => 'provider_payout_payment',
                'event_at' => ((string) ($row['paid_date'] ?? '')) . ' 00:00:00',
                'report_date' => (string) ($row['paid_date'] ?? ''),
                'created_at' => ((string) ($row['paid_date'] ?? '')) . ' 00:00:00',
                'reference' => (string) (($row['transaction_id'] ?? 'Finance') . ' / PYP-' . ($row['id'] ?? '')),
                'patient_label' => $patientName !== '' ? $patientName : null,
                'patient_number' => $row['patient_number'] ?? null,
                'counterparty' => $providerLabel,
                'payment_method' => $row['payment_method'] ?? null,
                'liquidity_account' => $row['liquidity_account'] ?? null,
                'payment_status' => null,
                'notes' => $row['notes'] ?? 'Provider payout cash payment',
                'category' => 'provider_payout_cash',
                'currency' => $baseCurrency,
                'exchange_rate' => 1.0,
                'amount_original' => null,
                'amount_base' => $amountBase,
                'signed_amount_base' => -$amountBase,
            ];
        }

        $transferRowsBuilder = $this->db->table('finance_liquidity_transfers ft')
            ->select('ft.*')
            ->where('ft.clinic_id', $clinicId);
        if ($startDate !== null && $startDate !== '') {
            $transferRowsBuilder = $transferRowsBuilder->where('ft.transfer_date >=', $startDate);
        }
        $transferRows = $transferRowsBuilder
            ->where('ft.transfer_date <=', $endDate)
            ->orderBy('ft.transfer_date', 'DESC')
            ->orderBy('ft.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($transferRows as $row) {
            $amountBase = floatval($row['amount'] ?? 0);
            $transferDate = (string) ($row['transfer_date'] ?? '');
            $reference = 'TRF-' . (string) ($row['id'] ?? '0');
            $routeReference = strtoupper((string) ($row['from_account'] ?? '')) . ' -> ' . strtoupper((string) ($row['to_account'] ?? ''));
            $label = ucfirst(str_replace('_', ' ', (string) ($row['transfer_type'] ?? 'internal_transfer')));

            $rows[] = [
                'finance_id' => !empty($row['related_finance_id']) ? (int) $row['related_finance_id'] : 0,
                'provider_payout_id' => null,
                'event_source' => 'liquidity_transfer',
                'source_label' => 'Liquidity transfer',
                'event_type' => 'transfer_out',
                'event_at' => $transferDate . ' 00:00:00',
                'report_date' => $transferDate,
                'created_at' => (string) ($row['created_at'] ?? ($transferDate . ' 00:00:00')),
                'reference' => $reference,
                'patient_label' => null,
                'patient_number' => null,
                'counterparty' => $routeReference,
                'payment_method' => null,
                'liquidity_account' => $row['from_account'] ?? null,
                'payment_status' => null,
                'notes' => trim($label . ' / ' . (string) ($row['notes'] ?? '')),
                'category' => 'transfer',
                'currency' => $baseCurrency,
                'exchange_rate' => 1.0,
                'amount_original' => $amountBase,
                'amount_base' => $amountBase,
                'signed_amount_base' => -$amountBase,
            ];

            $rows[] = [
                'finance_id' => !empty($row['related_finance_id']) ? (int) $row['related_finance_id'] : 0,
                'provider_payout_id' => null,
                'event_source' => 'liquidity_transfer',
                'source_label' => 'Liquidity transfer',
                'event_type' => 'transfer_in',
                'event_at' => $transferDate . ' 00:00:00',
                'report_date' => $transferDate,
                'created_at' => (string) ($row['created_at'] ?? ($transferDate . ' 00:00:00')),
                'reference' => $reference,
                'patient_label' => null,
                'patient_number' => null,
                'counterparty' => $routeReference,
                'payment_method' => null,
                'liquidity_account' => $row['to_account'] ?? null,
                'payment_status' => null,
                'notes' => trim($label . ' / ' . (string) ($row['notes'] ?? '')),
                'category' => 'transfer',
                'currency' => $baseCurrency,
                'exchange_rate' => 1.0,
                'amount_original' => $amountBase,
                'amount_base' => $amountBase,
                'signed_amount_base' => $amountBase,
            ];
        }

        usort($rows, static function (array $a, array $b): int {
            return strcmp((string) ($b['event_at'] ?? ''), (string) ($a['event_at'] ?? ''));
        });

        $inflow = 0.0;
        $outflow = 0.0;
        $net = 0.0;
        foreach ($rows as $row) {
            $signed = floatval($row['signed_amount_base'] ?? 0);
            $net += $signed;
            if ($signed >= 0) {
                $inflow += $signed;
            } else {
                $outflow += abs($signed);
            }
        }

        $treasuryRows = array_values(array_filter($rows, static fn(array $row): bool => !empty($row['liquidity_account'])));

        return [
            'rows' => $rows,
            'treasury_rows' => $treasuryRows,
            'summary' => [
                'count' => count($rows),
                'inflow_total' => $inflow,
                'outflow_total' => $outflow,
                'net_total' => $net,
            ],
        ];
    }

    private function summarizeCashReport(array $rows): array
    {
        $stats = [
            'gross_collections' => 0.0,
            'refunds_total' => 0.0,
            'expenses_paid' => 0.0,
            'provider_paid_cash_total' => 0.0,
            'net_clinic_cash' => 0.0,
            'safe_cash_in' => 0.0,
            'safe_cash_out' => 0.0,
            'safe_cash_net' => 0.0,
            'total_transactions' => count($rows),
            'average_transaction' => 0.0,
            'transaction_summary' => [],
        ];

        $summary = [];

        foreach ($rows as $row) {
            $type = (string) ($row['event_type'] ?? '');
            $source = (string) ($row['event_source'] ?? '');
            $amountBase = floatval($row['amount_base'] ?? 0);
            $paymentMethod = strtolower((string) ($row['payment_method'] ?? ''));

            if ($type === 'payment') {
                $stats['gross_collections'] += $amountBase;
            } elseif (in_array($type, ['refund', 'void'], true)) {
                $stats['refunds_total'] += $amountBase;
            } elseif ($type === 'expense') {
                $stats['expenses_paid'] += $amountBase;
            } elseif ($source === 'provider_payout_payment') {
                $stats['provider_paid_cash_total'] += $amountBase;
            }

            if ($paymentMethod === 'cash') {
                if ($type === 'payment') {
                    $stats['safe_cash_in'] += $amountBase;
                } elseif (in_array($type, ['refund', 'void', 'expense'], true) || $source === 'provider_payout_payment') {
                    $stats['safe_cash_out'] += $amountBase;
                }
            }

            $summaryKey = $source === 'provider_payout_payment' ? 'provider_payout_payment' : $type;
            if (!isset($summary[$summaryKey])) {
                $summary[$summaryKey] = [
                    'transaction_type' => $summaryKey,
                    'count' => 0,
                    'total_amount' => 0.0,
                    'average_amount' => 0.0,
                ];
            }

            $summary[$summaryKey]['count']++;
            $summary[$summaryKey]['total_amount'] += abs(floatval($row['signed_amount_base'] ?? 0));
        }

        foreach ($summary as &$row) {
            $row['total_amount'] = round($row['total_amount'], 2);
            $row['average_amount'] = $row['count'] > 0 ? round($row['total_amount'] / $row['count'], 2) : 0.0;
        }
        unset($row);

        usort($summary, static fn(array $a, array $b): int => strcmp((string) $a['transaction_type'], (string) $b['transaction_type']));

        $stats['gross_collections'] = round($stats['gross_collections'], 2);
        $stats['refunds_total'] = round($stats['refunds_total'], 2);
        $stats['expenses_paid'] = round($stats['expenses_paid'], 2);
        $stats['provider_paid_cash_total'] = round($stats['provider_paid_cash_total'], 2);
        $stats['safe_cash_in'] = round($stats['safe_cash_in'], 2);
        $stats['safe_cash_out'] = round($stats['safe_cash_out'], 2);
        $stats['safe_cash_net'] = round($stats['safe_cash_in'] - $stats['safe_cash_out'], 2);
        $stats['net_clinic_cash'] = round(
            $stats['gross_collections'] - $stats['refunds_total'] - $stats['expenses_paid'] - $stats['provider_paid_cash_total'],
            2
        );
        $stats['average_transaction'] = $stats['total_transactions'] > 0
            ? round(($stats['gross_collections'] + $stats['refunds_total'] + $stats['expenses_paid'] + $stats['provider_paid_cash_total']) / $stats['total_transactions'], 2)
            : 0.0;
        $stats['transaction_summary'] = $summary;

        return $stats;
    }

    private function buildMonthlyCollectionsSeries(array $rows, string $startDate, string $endDate): array
    {
        $start = new \DateTime(date('Y-m-01', strtotime($startDate)));
        $end = new \DateTime(date('Y-m-01', strtotime($endDate)));
        $end->modify('first day of next month');
        $period = new \DatePeriod($start, new \DateInterval('P1M'), $end);

        $totals = [];
        foreach ($rows as $row) {
            if ((string) ($row['event_type'] ?? '') !== 'payment') {
                continue;
            }
            $reportDate = (string) ($row['report_date'] ?? '');
            if ($reportDate === '') {
                continue;
            }

            $monthKey = date('Y-m', strtotime($reportDate));
            $totals[$monthKey] = ($totals[$monthKey] ?? 0) + floatval($row['amount_base'] ?? 0);
        }

        $labels = [];
        $values = [];
        foreach ($period as $month) {
            $key = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $values[] = round(floatval($totals[$key] ?? 0), 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function getHistoricalAccountsReceivableAgingDetail(int $clinicId, string $asOfDate, int $limit = 100): array
    {
        $limit = max(1, min($limit, 10000));
        $asOfDateTime = $asOfDate . ' 23:59:59';
        $sumField = $this->db->fieldExists('total_amount', 'finances')
            ? 'f.total_amount'
            : '(f.amount - COALESCE(f.discount_amount, 0) + COALESCE(f.tax_amount, 0))';

        $rows = $this->db->table('finances f')
            ->select("
                f.id,
                f.transaction_id,
                {$sumField} AS total_amount,
                f.due_date,
                f.transaction_date,
                f.created_at,
                p.first_name,
                p.last_name,
                p.patient_id AS patient_number,
                COALESCE((
                    SELECT SUM(
                        CASE
                            WHEN fp.type IN ('payment','adjustment') THEN fp.amount
                            WHEN fp.type IN ('refund','void') THEN -fp.amount
                            ELSE 0
                        END
                    )
                    FROM finance_payments fp
                    WHERE fp.clinic_id = f.clinic_id
                      AND fp.finance_id = f.id
                      AND fp.payment_date <= " . $this->db->escape($asOfDate) . "
                ), 0) AS paid_total
            ", false)
            ->join('patients p', 'p.id = f.patient_id AND p.clinic_id = f.clinic_id', 'left')
            ->where('f.clinic_id', $clinicId)
            ->where('f.transaction_type', 'invoice')
            ->where('f.created_at <=', $asOfDateTime)
            ->having('total_amount - paid_total >', 0, false)
            ->orderBy('f.due_date', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $totalAmount = floatval($row['total_amount'] ?? 0);
            $paidTotal = floatval($row['paid_total'] ?? 0);
            $balanceDue = max(0, $totalAmount - $paidTotal);
            $dueDate = !empty($row['due_date'])
                ? (string) $row['due_date']
                : (!empty($row['transaction_date']) ? (string) $row['transaction_date'] : substr((string) ($row['created_at'] ?? $asOfDate), 0, 10));
            $daysPastDue = (int) floor((strtotime($asOfDate) - strtotime($dueDate)) / 86400);
            $row['due_date'] = $dueDate;
            $row['balance_due'] = round($balanceDue, 2);
            $row['days_past_due'] = max(0, $daysPastDue);
            $row['aging_bucket'] = $this->mapAgingBucket($daysPastDue);
        }
        unset($row);

        return $rows;
    }

    private function summarizeAccountsReceivableBuckets(array $rows, string $asOfDate): array
    {
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
            if (!array_key_exists($bucket, $summary)) {
                $bucket = 'current';
            }

            $summary[$bucket] += $balance;
            $summary['total_balance_due'] += $balance;
            $summary['invoice_count']++;
        }

        return $summary;
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

    public function getFinanceStats()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $startDate = $startDate ?: '2000-01-01';
        $endDate = $endDate ?: date('Y-m-d');

        $ledger = $this->buildFinancialLedger((int) $clinicId, $startDate, $endDate);
        $stats = $this->summarizeCashReport($ledger['rows']);
        $openInvoices = $this->getHistoricalAccountsReceivableAgingDetail((int) $clinicId, $endDate, 500);
        $openPayables = $this->financeModel->getAccountsPayableAgingDetailByClinic((int) $clinicId, $endDate, 500);
        $openInvoicesBalance = 0.0;
        foreach ($openInvoices as $invoice) {
            $openInvoicesBalance += floatval($invoice['balance_due'] ?? 0);
        }
        $openPayablesBalance = 0.0;
        foreach ($openPayables as $payable) {
            $openPayablesBalance += floatval($payable['balance_due'] ?? 0);
        }

        $response = [
            'gross_collections' => $stats['gross_collections'],
            'gross_collections_formatted' => formatCurrency($stats['gross_collections']),
            'provider_paid_cash_total' => $stats['provider_paid_cash_total'],
            'provider_paid_cash_total_formatted' => formatCurrency($stats['provider_paid_cash_total']),
            'expenses_paid' => $stats['expenses_paid'],
            'expenses_paid_formatted' => formatCurrency($stats['expenses_paid']),
            'net_clinic_cash' => $stats['net_clinic_cash'],
            'net_clinic_cash_formatted' => formatCurrency($stats['net_clinic_cash']),
            'outstanding_invoices' => count($openInvoices),
            'outstanding_invoices_balance' => round($openInvoicesBalance, 2),
            'outstanding_invoices_balance_formatted' => formatCurrency($openInvoicesBalance),
            'outstanding_payables' => count($openPayables),
            'outstanding_payables_balance' => round($openPayablesBalance, 2),
            'outstanding_payables_balance_formatted' => formatCurrency($openPayablesBalance),
            'total_transactions' => $stats['total_transactions'],
        ];

        return $this->response->setJSON($response);
    }

    public function getMonthlyRevenue()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $year = $this->request->getGet('year') ?? date('Y');

        $revenue = $this->financeModel->getMonthlyRevenueByClinic($clinicId, $year);

        return $this->response->setJSON($revenue);
    }

    public function providerPayouts()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $payoutStats = $this->providerPayoutModel->getStatsByClinic((int) $clinicId);
        $data = [
            'title' => 'Provider Payouts',
            'stats' => $payoutStats,
            'can_access_finance_reports' => $this->canAccessFinanceReports((int) $clinicId),
            'currency_symbol' => settings()->getCurrencySymbol(),
            'currency_position' => settings()->get('currency_position', 'before'),
        ];

        return $this->view('finance/payouts', $data);
    }

    public function getProviderPayoutsData()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $draw = intval($this->request->getPost('draw') ?? 1);
            $start = intval($this->request->getPost('start') ?? 0);
            $length = intval($this->request->getPost('length') ?? 10);
            $search = trim((string) (($this->request->getPost('search')['value'] ?? '')));
            $statusFilter = trim((string) ($this->request->getPost('status') ?? ''));

            $resultSet = $this->providerPayoutModel->getDataTableByClinic(
                (int) $clinicId,
                $start,
                $length,
                $search,
                $statusFilter
            );
            $recordsTotal = (int) ($resultSet['recordsTotal'] ?? 0);
            $recordsFiltered = (int) ($resultSet['recordsFiltered'] ?? 0);
            $rows = $resultSet['rows'] ?? [];

            $data = [];
            foreach ($rows as $row) {
                $providerName = trim((string) ($row['provider_first_name'] ?? '') . ' ' . (string) ($row['provider_last_name'] ?? ''));
                if ($providerName === '') {
                    $providerName = (string) ($row['provider_name_snapshot'] ?? 'Guest Provider');
                }
                $patientName = trim((string) ($row['patient_first_name'] ?? '') . ' ' . (string) ($row['patient_last_name'] ?? ''));

                $data[] = [
                    'id' => (int) $row['id'],
                    'finance_id' => (int) ($row['finance_id'] ?? 0),
                    'transaction_id' => $row['transaction_id'] ?? '-',
                    'transaction_type' => $row['transaction_type'] ?? null,
                    'patient_name' => $patientName,
                    'patient_gender' => $row['patient_gender'] ?? null,
                    'provider_name' => $providerName,
                    'provider_type' => $row['provider_type'],
                    'gross_amount' => floatval($row['gross_amount'] ?? 0),
                    'provider_share_amount' => floatval($row['provider_share_amount'] ?? 0),
                    'clinic_share_amount' => floatval($row['clinic_share_amount'] ?? 0),
                    'provider_paid_amount' => floatval($row['provider_paid_amount'] ?? 0),
                    'provider_balance_due' => floatval($row['provider_balance_due'] ?? 0),
                    'status' => $row['status'] ?? 'unpaid',
                    'created_at' => $row['created_at'] ?? null,
                    'created_at_formatted' => !empty($row['created_at']) ? formatDateTime($row['created_at']) : '-',
                ];
            }

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Provider payouts data error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function addProviderPayoutPayment($id)
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $payout = $this->providerPayoutModel->findByClinic((int) $clinicId, (int) $id);
            if (!$payout || ($payout['status'] ?? '') === 'void') {
                return $this->response->setJSON(['success' => false, 'message' => 'Provider payout not found.']);
            }

            $amount = floatval($this->request->getPost('amount') ?? 0);
            $method = (string) ($this->request->getPost('payment_method') ?? 'cash');
            $paidDate = (string) ($this->normalizePostedDateOnly('paid_date') ?: date('Y-m-d'));
            $notes = trim((string) ($this->request->getPost('notes') ?? ''));

            if ($amount <= 0) {
                return $this->response->setJSON(['success' => false, 'message' => 'Payment amount must be greater than 0.']);
            }
            if (!in_array($method, ['cash', 'card', 'bank_transfer', 'check', 'other'], true)) {
                $method = 'cash';
            }

            $remaining = floatval($payout['provider_balance_due'] ?? 0);
            if ($amount > $remaining) {
                return $this->response->setJSON(['success' => false, 'message' => 'Payment exceeds provider balance due.']);
            }

            $this->providerPayoutPaymentModel->insert([
                'provider_payout_id' => (int) $id,
                'amount' => round($amount, 2),
                'payment_method' => $method,
                'liquidity_account' => $this->normalizeLiquidityAccountInput($method, 'out', 'provider_payout'),
                'paid_date' => $paidDate,
                'notes' => $notes,
                'created_by' => $this->resolveActorUserId(),
            ]);

            $this->syncProviderPayoutStatus((int) $clinicId, (int) $id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Provider payout payment recorded successfully.',
                'csrf_token' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Provider payout payment error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
                'csrf_token' => csrf_hash(),
            ]);
        }
    }

    public function markProviderPayoutPaid($id)
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $payout = $this->providerPayoutModel->findByClinic((int) $clinicId, (int) $id);
            if (!$payout || ($payout['status'] ?? '') === 'void') {
                return $this->response->setJSON(['success' => false, 'message' => 'Provider payout not found.']);
            }

            $remaining = floatval($payout['provider_balance_due'] ?? 0);
            if ($remaining <= 0) {
                return $this->response->setJSON(['success' => true, 'message' => 'Provider payout is already fully paid.', 'csrf_token' => csrf_hash()]);
            }

            $method = (string) ($this->request->getPost('payment_method') ?? 'cash');
            if (!in_array($method, ['cash', 'card', 'bank_transfer', 'check', 'other'], true)) {
                $method = 'cash';
            }

            $this->providerPayoutPaymentModel->insert([
                'provider_payout_id' => (int) $id,
                'amount' => round($remaining, 2),
                'payment_method' => $method,
                'liquidity_account' => $this->normalizeLiquidityAccountInput($method, 'out', 'provider_payout'),
                'paid_date' => date('Y-m-d'),
                'notes' => 'Marked fully paid',
                'created_by' => $this->resolveActorUserId(),
            ]);

            $this->syncProviderPayoutStatus((int) $clinicId, (int) $id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Provider payout marked as paid.',
                'csrf_token' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Mark provider payout paid error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
                'csrf_token' => csrf_hash(),
            ]);
        }
    }

    public function markAsPaid($id)
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $finance = $this->financeModel->findByClinic($clinicId, $id);

            if (!$finance) {
                return $this->response->setJSON(['success' => false, 'message' => 'Financial transaction not found']);
            }

            if (in_array(($finance['transaction_type'] ?? ''), ['invoice', 'expense'], true)) {
                $totalAmount = floatval($finance['total_amount'] ?? ($finance['amount'] - $finance['discount_amount'] + $finance['tax_amount']));
                $settledTotal = $this->financePaymentModel->getNetSettledAmount($clinicId, (int) $finance['id']);
                $remaining = max(0, $totalAmount - $settledTotal);

                if ($remaining <= 0) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => ($finance['transaction_type'] === 'expense') ? 'Expense already fully paid.' : 'Invoice already fully paid.',
                        'csrf_token' => csrf_hash()
                    ]);
                }

                $paymentData = [
                    'finance_id' => $finance['id'],
                    'amount' => $remaining,
                    'type' => 'payment',
                    'payment_method' => $finance['payment_method'] ?? 'cash',
                    'liquidity_account' => $this->resolveSettlementLiquidityAccount($finance, 'payment'),
                    'payment_date' => date('Y-m-d'),
                    'notes' => ($finance['transaction_type'] === 'expense') ? 'Full payment via Mark as Paid' : 'Full payment via Mark as Paid',
                    'created_by' => $this->resolveActorUserId()
                ];
                if ($this->supportsFinancePaymentExchangeFields()) {
                    $rate = max(0.0001, (float) ($finance['exchange_rate'] ?? 1));
                    $paymentData['currency'] = $finance['currency'] ?? settings()->get('currency', 'ILS');
                    $paymentData['amount_original'] = round($remaining / $rate, 2);
                    $paymentData['exchange_rate'] = $rate;
                }

                $this->financePaymentModel->insert($paymentData);
                $this->syncFinanceSettlementStatus((int) $finance['id'], (int) $clinicId);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => ($finance['transaction_type'] === 'expense') ? 'Expense marked as paid.' : 'Invoice marked as paid.',
                    'csrf_token' => csrf_hash()
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Only invoices and expenses support settlement tracking.',
                'csrf_token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Exception in markAsPaid - ID: ' . $id . ', Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    public function addInvoicePayment($id)
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $finance = $this->financeModel->findByClinic($clinicId, $id);
            if (!$finance || !in_array(($finance['transaction_type'] ?? ''), ['invoice', 'expense'], true)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transaction not found',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $totalAmount = floatval($finance['total_amount'] ?? ($finance['amount'] - $finance['discount_amount'] + $finance['tax_amount']));
            $settledTotal = $this->financePaymentModel->getNetSettledAmount($clinicId, (int) $finance['id']);
            $remaining = max(0, $totalAmount - $settledTotal);
            $settlement = $this->normalizeFinanceSettlementInput($finance, $remaining);

            $paymentData = [
                'finance_id' => $finance['id'],
                'amount' => $settlement['amount_base'],
                'type' => 'payment',
                'payment_method' => $settlement['payment_method'],
                'liquidity_account' => $this->resolveSettlementLiquidityAccount($finance, 'payment'),
                'payment_date' => $settlement['payment_date'],
                'notes' => $settlement['notes'],
                'created_by' => $this->resolveActorUserId()
            ];
            if ($this->supportsFinancePaymentExchangeFields()) {
                $paymentData['currency'] = $settlement['currency'];
                $paymentData['amount_original'] = $settlement['amount_original'];
                $paymentData['exchange_rate'] = $settlement['exchange_rate'];
            }

            if (!$this->financePaymentModel->insert($paymentData)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to add payment.',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $this->syncFinanceSettlementStatus((int) $finance['id'], (int) $clinicId);

            return $this->response->setJSON([
                'success' => true,
                'message' => (($finance['transaction_type'] ?? '') === 'expense') ? 'Expense payment recorded successfully.' : 'Payment added successfully.',
                'csrf_token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Invoice payment error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    public function refundInvoicePayment($id)
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $finance = $this->financeModel->findByClinic($clinicId, $id);
            if (!$finance || $finance['transaction_type'] !== 'invoice') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invoice not found',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $netPaid = $this->financePaymentModel->getPaidTotalByInvoice($clinicId, $finance['id']);
            $settlement = $this->normalizeInvoiceSettlementInput($finance, $netPaid);
            $amount = $settlement['amount_base'];

            $relatedPaymentId = (int) ($this->request->getPost('related_payment_id') ?? 0);
            if ($relatedPaymentId) {
                $payment = $this->financePaymentModel
                    ->where('clinic_id', $clinicId)
                    ->where('finance_id', $finance['id'])
                    ->find($relatedPaymentId);

                if (!$payment || ($payment['type'] ?? 'payment') !== 'payment') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Related payment not found.',
                        'csrf_token' => csrf_hash()
                    ]);
                }

                $refunded = $this->financePaymentModel->getRelatedAdjustmentsTotal($clinicId, $relatedPaymentId);
                $remaining = max(0, floatval($payment['amount']) - $refunded);
                if ($amount > $remaining) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Refund exceeds remaining amount for the selected payment.',
                        'csrf_token' => csrf_hash()
                    ]);
                }
            }

            $refundData = [
                'finance_id' => $finance['id'],
                'amount' => $settlement['amount_base'],
                'type' => 'refund',
                'payment_method' => $settlement['payment_method'],
                'liquidity_account' => $this->resolveSettlementLiquidityAccount($finance, 'refund', $payment ?? null),
                'payment_date' => $settlement['payment_date'],
                'notes' => $settlement['notes'],
                'related_payment_id' => $relatedPaymentId ?: null,
                'created_by' => $this->resolveActorUserId()
            ];
            if ($this->supportsFinancePaymentExchangeFields()) {
                $refundData['currency'] = $settlement['currency'];
                $refundData['amount_original'] = $settlement['amount_original'];
                $refundData['exchange_rate'] = $settlement['exchange_rate'];
            }

            if (!$this->financePaymentModel->insert($refundData)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to record refund.',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $this->syncInvoicePaymentStatus($finance['id'], $clinicId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Refund recorded successfully.',
                'csrf_token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Invoice refund error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    public function voidInvoicePayment($id, $paymentId)
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $finance = $this->financeModel->findByClinic($clinicId, $id);
            if (!$finance || $finance['transaction_type'] !== 'invoice') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invoice not found',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $payment = $this->financePaymentModel
                ->where('clinic_id', $clinicId)
                ->where('finance_id', $finance['id'])
                ->find($paymentId);

            if (!$payment || ($payment['type'] ?? 'payment') !== 'payment') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment not found.',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $refunded = $this->financePaymentModel->getRelatedAdjustmentsTotal($clinicId, $paymentId);
            $remaining = max(0, floatval($payment['amount']) - $refunded);
            if ($remaining <= 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment already refunded or voided.',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $voidData = [
                'finance_id' => $finance['id'],
                'amount' => $remaining,
                'type' => 'void',
                'payment_method' => $payment['payment_method'] ?? ($finance['payment_method'] ?? 'cash'),
                'liquidity_account' => $this->resolveSettlementLiquidityAccount($finance, 'void', $payment),
                'payment_date' => date('Y-m-d'),
                'notes' => $this->request->getPost('notes') ?? 'Payment voided',
                'related_payment_id' => $paymentId,
                'created_by' => $this->resolveActorUserId()
            ];
            if ($this->supportsFinancePaymentExchangeFields()) {
                $voidRate = floatval($payment['exchange_rate'] ?? 1);
                $voidRate = $voidRate > 0 ? $voidRate : 1;
                $voidData['currency'] = $payment['currency'] ?? settings()->get('currency', 'ILS');
                $voidData['exchange_rate'] = $voidRate;
                $voidData['amount_original'] = round($remaining / $voidRate, 2);
            }

            if (!$this->financePaymentModel->insert($voidData)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to void payment.',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $this->syncInvoicePaymentStatus($finance['id'], $clinicId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Payment voided successfully.',
                'csrf_token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Invoice void error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    public function closeInvoiceBalance($id)
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $finance = $this->financeModel->findByClinic($clinicId, $id);
            if (!$finance || $finance['transaction_type'] !== 'invoice') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invoice not found',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $totalAmount = floatval($finance['total_amount'] ?? ($finance['amount'] - $finance['discount_amount'] + $finance['tax_amount']));
            $paidTotal = $this->financePaymentModel->getPaidTotalByInvoice($clinicId, $finance['id']);
            $remaining = max(0, $totalAmount - $paidTotal);

            if ($remaining <= 0) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Invoice is already closed.',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $reason = trim((string) $this->request->getPost('reason'));
            $notes = 'Balance closed via write-off/credit';
            if ($reason !== '') {
                $notes .= ': ' . $reason;
            }

            $adjustmentData = [
                'finance_id' => $finance['id'],
                'amount' => $remaining,
                'type' => 'adjustment',
                'payment_method' => $finance['payment_method'] ?? 'other',
                'liquidity_account' => null,
                'payment_date' => date('Y-m-d'),
                'notes' => $notes,
                'created_by' => $this->resolveActorUserId(),
            ];
            if ($this->supportsFinancePaymentExchangeFields()) {
                $adjustmentData['currency'] = settings()->get('currency', 'ILS');
                $adjustmentData['amount_original'] = $remaining;
                $adjustmentData['exchange_rate'] = 1;
            }

            if (!$this->financePaymentModel->insert($adjustmentData)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to close invoice balance.',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $this->syncInvoicePaymentStatus($finance['id'], $clinicId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Invoice balance closed successfully.',
                'csrf_token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Invoice close-balance error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    private function syncFinanceSettlementStatus(int $financeId, int $clinicId): void
    {
        $finance = $this->financeModel->findByClinic($clinicId, $financeId);
        if (!$finance || !in_array(($finance['transaction_type'] ?? ''), ['invoice', 'expense'], true)) {
            return;
        }

        $totalAmount = floatval($finance['total_amount'] ?? ($finance['amount'] - $finance['discount_amount'] + $finance['tax_amount']));
        $settledTotal = $this->financePaymentModel->getNetSettledAmount($clinicId, (int) $finance['id']);
        $balanceDue = max(0, $totalAmount - $settledTotal);

        $status = 'pending';
        if ($balanceDue <= 0) {
            $status = 'paid';
        } elseif (!empty($finance['due_date']) && strtotime($finance['due_date']) < strtotime(date('Y-m-d'))) {
            $status = 'overdue';
        } elseif ($settledTotal > 0 && $balanceDue > 0) {
            $status = 'partial';
        }

        $updateData = [
            'amount_paid' => $settledTotal,
            'balance_due' => $balanceDue,
            'payment_status' => $status,
        ];

        if ($status === 'paid' && empty($finance['paid_date'])) {
            $updateData['paid_date'] = date('Y-m-d');
        }

        $this->financeModel->setValidationRules([]);
        $this->financeModel->update($finance['id'], $updateData);
    }

    private function syncInvoicePaymentStatus($invoiceId, $clinicId)
    {
        $this->syncFinanceSettlementStatus((int) $invoiceId, (int) $clinicId);
    }

    public function generateInvoice($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $finance = $this->financeModel->findByClinic($clinicId, $id);

        if (!$finance) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Financial transaction not found');
        }

        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($finance['patient_id']);
        $examination = $finance['examination_id'] ? $this->examinationModel->where('clinic_id', $clinicId)->find($finance['examination_id']) : null;
        $payments = [];
        $paidTotal = 0;
        if ($finance['transaction_type'] === 'invoice') {
            $payments = $this->financePaymentModel
                ->select('finance_payments.*, users.first_name as creator_first_name, users.last_name as creator_last_name')
                ->join('users', 'users.id = finance_payments.created_by', 'left')
                ->where('finance_payments.clinic_id', $clinicId)
                ->where('finance_payments.finance_id', $finance['id'])
                ->orderBy('payment_date', 'DESC')
                ->orderBy('finance_payments.id', 'DESC')
                ->findAll();
            $paidTotal = $this->financePaymentModel->getPaidTotalByInvoice($clinicId, $finance['id']);
        }

        $data = [
            'title' => 'Invoice - ' . $finance['transaction_id'],
            'finance' => $finance,
            'patient' => $patient,
            'examination' => $examination,
            'payments' => $payments,
            'paid_total' => $paidTotal,
            'clinic' => settings()->getClinicInfo(),
            'base_currency' => settings()->get('currency', 'ILS'),
            'check_attachment' => $this->attachmentModel
                ->where('clinic_id', $clinicId)
                ->where('entity_type', 'finance')
                ->where('entity_id', $finance['id'])
                ->where('purpose', 'check_scan')
                ->first()
        ];

        $html = view('finance/invoice', $data);
        
        // Persist artifact (best-effort; do not block invoice view on plan limits)
        $fileName = 'invoice_' . $finance['transaction_id'] . '.html';
        try {
            $this->storageService->storeExport(
                $html, 
                $fileName, 
                'text/html', 
                $clinicId, 
                'finance', 
                $id, 
                'invoice_print'
            );
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            log_message('warning', 'Finance invoice export blocked: ' . $e->getMessage());
        } catch (\Exception $e) {
            log_message('error', 'Finance invoice export failed: ' . $e->getMessage());
        }

        // Stream the response (standard print behavior)
        return $this->response
            ->setHeader('Content-Type', 'text/html')
            ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->setBody($html);
    }

    public function testFinancesData()
    {
        try {
            // S4-02c: Fail closed if clinic context is missing
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'TENANT_CONTEXT_REQUIRED'
                ]);
            }

            // Test database connection
            $financeCount = $this->financeModel->countFinancesByClinic($clinicId);

            // Get a sample finance record
            $sampleFinance = $this->financeModel->getFinancesByClinic($clinicId, 1);

            return $this->response->setJSON([
                'success' => true,
                'finance_count' => $financeCount,
                'sample_finance' => $sampleFinance,
                'message' => 'Database connection successful'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Database connection failed'
            ]);
        }
    }

    public function getFinancesData()
    {
        try {
            $request = $this->request;

            // S4-02c: Fail closed if clinic context is missing
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            // DataTables parameters with defaults
            $draw = intval($request->getPost('draw') ?? 1);
            $start = intval($request->getPost('start') ?? 0);
            $length = intval($request->getPost('length') ?? 10);

            // Handle search parameter
            $searchParam = $request->getPost('search');
            $searchValue = '';
            if (is_array($searchParam) && isset($searchParam['value'])) {
                $searchValue = trim($searchParam['value']);
            }

            // Handle order parameter
            $orderParam = $request->getPost('order');
            $orderColumn = 0;
            $orderDir = 'desc';
            if (is_array($orderParam) && isset($orderParam[0])) {
                $orderColumn = intval($orderParam[0]['column'] ?? 0);
                $orderDir = $orderParam[0]['dir'] ?? 'desc';
            }

            // Column mapping for ordering
            $columns = [
                'finances.id',
                'patients.first_name',
                'finances.transaction_type',
                'finances.total_amount',
                'finances.payment_status',
                'finances.created_at'
            ];

            $orderColumnName = $columns[$orderColumn] ?? 'finances.id';
            $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? strtolower($orderDir) : 'desc';

            // Get total records count (scoped)
            $totalRecords = $this->financeModel->countFinancesByClinic($clinicId);

            // If no finances exist, return empty result
            if ($totalRecords == 0) {
                return $this->response->setJSON([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]);
            }

            // Get filtered count
            $filteredRecords = $this->financeModel->countFinancesByClinic($clinicId, $searchValue);

            // Get data
            $finances = $this->financeModel->getFinancesByClinic($clinicId, $length, $start, $searchValue, $orderColumnName, $orderDir);

            // Format data
            $data = [];
            foreach ($finances as $finance) {
                $amount = floatval($finance['total_amount'] ?? ($finance['amount'] - ($finance['discount_amount'] ?? 0) + ($finance['tax_amount'] ?? 0)));
                $type = (string) ($finance['transaction_type'] ?? '');
                $isOutflow = in_array($type, ['refund', 'expense'], true);
                $signedAmount = $isOutflow ? -$amount : $amount;
                $originalCurrency = (string) ($finance['currency'] ?? settings()->get('currency', 'ILS'));
                $baseCurrency = (string) settings()->get('currency', 'ILS');
                $amountOriginal = floatval($finance['amount_original'] ?? $finance['amount'] ?? $amount);
                $exchangeRate = floatval($finance['exchange_rate'] ?? 1);
                $hasOriginalCurrencyDelta = strtoupper($originalCurrency) !== strtoupper($baseCurrency) || abs($exchangeRate - 1.0) > 0.000001;
                $displayName = trim(($finance['first_name'] ?? '') . ' ' . ($finance['last_name'] ?? ''));
                if ($displayName === '') {
                    $displayName = (string) ($finance['counterparty_name'] ?? 'General');
                }
                $data[] = [
                    'id' => intval($finance['id'] ?? 0),
                    'transaction_id' => $finance['transaction_id'] ?? ('TXN-' . str_pad($finance['id'], 6, '0', STR_PAD_LEFT)),
                    'patient_name' => $displayName,
                    'phone' => $finance['phone'] ?? '',
                    'transaction_type' => $finance['transaction_type'] ?? '',
                    'amount' => $signedAmount,
                    'amount_formatted' => ($signedAmount < 0 ? '-' : '') . formatCurrency(abs($signedAmount)),
                    'amount_original' => $amountOriginal,
                    'amount_original_currency' => $originalCurrency,
                    'amount_original_formatted' => number_format($amountOriginal, 2) . ' ' . $originalCurrency,
                    'exchange_rate' => $exchangeRate,
                    'has_original_currency_delta' => $hasOriginalCurrencyDelta,
                    'payment_status' => $finance['payment_status'] ?? '',
                    'description' => $finance['description'] ?? '',
                    'created_at' => $finance['created_at'] ?? '',
                    'created_at_formatted' => formatDateTime($finance['created_at'] ?? ''),
                    'patient_id' => intval($finance['patient_id'] ?? 0),
                    'patient_number' => $finance['patient_number'] ?? '',
                    'gender' => $finance['gender'] ?? 'other',
                    'counterparty_name' => $finance['counterparty_name'] ?? '',
                    'expense_category' => $finance['expense_category'] ?? '',
                    'subject_type' => ($type === 'expense') ? 'counterparty' : 'patient',
                ];
            }

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Finance DataTables error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading data: ' . $e->getMessage()
            ]);
        }
    }

    public function export()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return redirect()->to('/clinic/select');
            }
            $this->requireFinanceReportsFeature((int) $clinicId);

            $format = $this->request->getGet('format') ?? 'csv';
            $startDate = $this->request->getGet('start_date');
            $endDate = $this->request->getGet('end_date');
            $status = $this->request->getGet('status');
            $ids = $this->request->getGet('ids');
            $includeAllEvents = filter_var($this->request->getGet('include_all_events'), FILTER_VALIDATE_BOOLEAN);

            if ($includeAllEvents && $startDate && $endDate) {
                $ledger = $this->buildFinancialLedger((int) $clinicId, (string) $startDate, (string) $endDate);
                $content = $this->generateLedgerCSVContent($ledger['rows'], (string) $startDate, (string) $endDate);
                $mimeType = 'text/csv';
                $fileName = 'financial_cash_report_' . $startDate . '_to_' . $endDate . '_' . date('H-i-s') . '.csv';

                $attachment = $this->storageService->storeExport(
                    $content,
                    $fileName,
                    $mimeType,
                    $clinicId,
                    'finance',
                    0,
                    'finance_ledger_export'
                );

                return redirect()->to(base_url('file/download/' . $attachment['id']));
            }

            // Build query
            $query = $this->financeModel->forClinic($clinicId)
                ->select('finances.*, patients.first_name, patients.last_name')
                ->join('patients', 'patients.id = finances.patient_id', 'left');

            if ($startDate && $endDate) {
                $query->where('finances.transaction_date >=', $startDate)
                    ->where('finances.transaction_date <=', $endDate);
            }

            if ($status) {
                $query->where('finances.payment_status', $status);
            }

            if ($ids) {
                $idArray = explode(',', $ids);
                $query->whereIn('finances.id', $idArray);
            }

            $data = $query->findAll();

            $content = '';
            $mimeType = 'text/csv';
              $fileName = 'finance_document_export_' . date('Y-m-d_H-i-s') . '.csv';

              if ($format === 'csv') {
                  $content = $this->generateCSVContent($data, (string) ($startDate ?? ''), (string) ($endDate ?? ''));
              } else {
                  $content = $this->generateCSVContent($data, (string) ($startDate ?? ''), (string) ($endDate ?? '')); // Fallback
              }

            // Persist artifact
            $attachment = $this->storageService->storeExport(
                $content, 
                $fileName, 
                $mimeType, 
                $clinicId, 
                'finance', 
                0, // Global export
                'finance_export'
            );

            return redirect()->to(base_url('file/download/' . $attachment['id']));

        } catch (\Exception $e) {
            log_message('error', 'Export error: ' . $e->getMessage());
            return redirect()->to(base_url('finance'))->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    private function generateCSVContent($data, string $startDate = '', string $endDate = '')
    {
        $output = fopen('php://temp', 'r+');
        $baseCurrency = settings()->get('currency', 'ILS');

        fputcsv($output, ['Export Type', 'Finance document export']);
        fputcsv($output, ['Report Basis', 'Document basis']);
        fputcsv($output, ['Business Date Rule', 'Transactions are grouped by transaction date. Paid and due dates are included as separate columns when available.']);
        if ($startDate !== '' || $endDate !== '') {
            fputcsv($output, ['Date Range', trim($startDate . ' to ' . $endDate)]);
        }
        fputcsv($output, []);

        // CSV headers
        fputcsv($output, [
            'ID',
            'Transaction ID',
            'Patient Name',
            'Counterparty',
            'Transaction Type',
            'Expense Category',
            'Original Amount',
            'Original Currency',
            'Exchange Rate',
            'Amount (' . $baseCurrency . ')',
            'Payment Method',
            'Liquidity Account',
            'Payment Status',
            'Transaction Date',
            'Due Date',
            'Paid Date',
            'Service Type',
            'Description',
            'Recorded At'
          ]);

        // CSV data
        foreach ($data as $row) {
            $amount = floatval($row['total_amount'] ?? ($row['amount'] - ($row['discount_amount'] ?? 0) + ($row['tax_amount'] ?? 0)));
            fputcsv($output, [
                $row['id'],
                $row['transaction_id'] ?? $row['id'],
                ($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''),
                $row['counterparty_name'] ?? '',
                $row['transaction_type'],
                $row['expense_category'] ?? '',
                $row['amount_original'] ?? $row['amount'],
                $row['currency'] ?? $baseCurrency,
                $row['exchange_rate'] ?? 1,
                $amount,
                $row['payment_method'],
                $row['liquidity_account'] ?? '',
                $row['payment_status'],
                $row['transaction_date'] ?? '',
                $row['due_date'] ?? '',
                $row['paid_date'] ?? '',
                $row['service_type'],
                $row['description'],
                $row['created_at']
            ]);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return "\xEF\xBB\xBF" . $content;
    }

    private function generateLedgerCSVContent(array $rows, string $startDate = '', string $endDate = ''): string
    {
        $output = fopen('php://temp', 'r+');

        fputcsv($output, ['Export Type', 'Finance cash report']);
        fputcsv($output, ['Report Basis', 'Cash basis']);
        fputcsv($output, ['Business Date Rule', 'Payments, refunds, expenses, invoice settlements, and provider payouts are grouped by settlement date.']);
        if ($startDate !== '' || $endDate !== '') {
            fputcsv($output, ['Date Range', trim($startDate . ' to ' . $endDate)]);
        }
        fputcsv($output, []);

        fputcsv($output, [
            'Report Date',
            'Recorded At',
            'Source',
            'Type',
            'Reference',
            'Patient Name',
            'Patient Number',
            'Counterparty',
            'Payment Method',
            'Liquidity Account',
            'Payment Status',
            'Category',
            'Notes',
            'Currency',
            'Exchange Rate',
            'Original Amount',
            'Base Amount',
            'Signed Base Amount',
        ]);

        foreach ($rows as $row) {
            fputcsv($output, [
                $row['report_date'] ?? '',
                $row['created_at'] ?? '',
                $row['event_source'] ?? '',
                $row['event_type'] ?? '',
                $row['reference'] ?? '',
                $row['patient_label'] ?? '',
                $row['patient_number'] ?? '',
                $row['counterparty'] ?? '',
                $row['payment_method'] ?? '',
                $row['liquidity_account'] ?? '',
                $row['payment_status'] ?? '',
                $row['category'] ?? '',
                $row['notes'] ?? '',
                $row['currency'] ?? '',
                $row['exchange_rate'] ?? '',
                $row['amount_original'] ?? '',
                $row['amount_base'] ?? '',
                $row['signed_amount_base'] ?? '',
            ]);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return "\xEF\xBB\xBF" . $content;
    }

    private function exportToCSV($data)
    {
        // Deprecated - using generateCSVContent + persistence
    }

    private function exportToExcel($data)
    {
        // Deprecated - using generateCSVContent + persistence
    }

    private function validateCheckScan($file): ?string
    {
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ];

        $extension = strtolower((string) $file->getExtension());
        if (!in_array($extension, $allowedExts, true)) {
            return 'Unsupported check scan file type.';
        }

        $mime = (string) $file->getMimeType();
        if (!in_array($mime, $allowedMimes, true)) {
            return 'Unsupported check scan file format.';
        }

        return null;
    }

    private function validateFinanceAttachment($file): ?string
    {
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ];

        $extension = strtolower((string) $file->getExtension());
        if (!in_array($extension, $allowedExts, true)) {
            return 'Unsupported attachment file type.';
        }

        $mime = (string) $file->getMimeType();
        if (!in_array($mime, $allowedMimes, true)) {
            return 'Unsupported attachment file format.';
        }

        return null;
    }

    public function bulkMarkAsPaid()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $input = json_decode($this->request->getBody(), true);
            $ids = $input['ids'] ?? [];

            if (empty($ids)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No transactions selected']);
            }

            $finances = $this->financeModel
                ->where('clinic_id', $clinicId)
                ->whereIn('id', $ids)
                ->findAll();

            if (empty($finances)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No valid transactions found']);
            }

            $updated = 0;
            $skipped = 0;
            $today = date('Y-m-d');

            foreach ($finances as $finance) {
                $financeId = (int) ($finance['id'] ?? 0);
                $totalAmount = floatval($finance['total_amount'] ?? ($finance['amount'] - ($finance['discount_amount'] ?? 0) + ($finance['tax_amount'] ?? 0)));

                if (($finance['transaction_type'] ?? '') === 'invoice') {
                    $paidTotal = $this->financePaymentModel->getPaidTotalByInvoice($clinicId, $financeId);
                    $remaining = max(0, $totalAmount - $paidTotal);

                    if ($remaining <= 0) {
                        $skipped++;
                        continue;
                    }

                    $paymentData = [
                        'finance_id' => $financeId,
                        'amount' => $remaining,
                        'type' => 'payment',
                        'payment_method' => $finance['payment_method'] ?? 'cash',
                        'payment_date' => $today,
                        'notes' => 'Bulk mark as paid',
                        'created_by' => $this->resolveActorUserId()
                    ];
                    if ($this->supportsFinancePaymentExchangeFields()) {
                        $baseCurrency = settings()->get('currency', 'ILS');
                        $paymentData['currency'] = $baseCurrency;
                        $paymentData['amount_original'] = $remaining;
                        $paymentData['exchange_rate'] = 1;
                    }

                    $this->financePaymentModel->insert($paymentData);
                    $this->syncInvoicePaymentStatus($financeId, $clinicId);
                    $updated++;
                    continue;
                }

                $this->financeModel->setValidationRules([]);
                if ($this->financeModel->update($financeId, [
                    'payment_status' => 'paid',
                    'paid_date' => $today,
                    'amount_paid' => $totalAmount,
                    'balance_due' => 0
                ])) {
                    $updated++;
                }
            }

            $message = $updated . ' transaction(s) marked as paid successfully';
            if ($skipped > 0) {
                $message .= ', ' . $skipped . ' already settled';
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Bulk mark as paid error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function bulkDelete()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $input = json_decode($this->request->getBody(), true);
            $ids = $input['ids'] ?? [];

            if (empty($ids)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No transactions selected']);
            }

            $this->financeModel->whereIn('id', $ids)->where('clinic_id', $clinicId)->delete();

            return $this->response->setJSON([
                'success' => true,
                'message' => count($ids) . ' transactions deleted successfully'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Bulk delete error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
}
