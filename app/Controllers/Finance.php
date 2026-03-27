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
use App\Models\UserModel;
use App\Services\ActivityLogger;
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
    protected $userModel;
    protected $activityLogger;
    protected $storageService;
    protected $db;
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
        $this->userModel = new UserModel();
        $this->activityLogger = new ActivityLogger();
        $this->storageService = new \App\Services\StorageService();
        $this->db = \Config\Database::connect();
        $this->whatsAppWorkflowService = new WhatsAppWorkflowService();
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
        return [
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
    }

    private function getFinanceCurrencyOptions(): array
    {
        return ['ILS', 'USD', 'JOD', 'EUR', 'GBP'];
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

    private function normalizeInvoiceSettlementInput(array $finance, ?float $maxBaseAmount = null): array
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

        return [
            'title' => $finance ? ('Edit Expense - ' . ($finance['transaction_id'] ?? '')) : 'New Expense',
            'finance' => $finance,
            'validation' => \Config\Services::validation(),
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => $this->getFinanceCurrencyOptions(),
            'expense_category_options' => $this->getExpenseCategoryOptions(),
            'expense_attachment' => ($clinicId > 0 && !empty($finance['id']))
                ? $this->attachmentModel
                    ->where('clinic_id', $clinicId)
                    ->where('entity_type', 'finance')
                    ->where('entity_id', $finance['id'])
                    ->where('purpose', 'attachment')
                    ->first()
                : null,
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to') ?? $this->request->getPost('return_to'), ['finance']),
        ];
    }

    private function normalizeProviderSplitPayload(int $clinicId, float $grossAmount): ?array
    {
        $enabled = filter_var($this->request->getPost('provider_split_enabled'), FILTER_VALIDATE_BOOLEAN);
        if (!$enabled) {
            return null;
        }

        $providerType = strtolower((string) $this->request->getPost('provider_type'));
        if (!in_array($providerType, ['user', 'guest'], true)) {
            throw new \RuntimeException('Please select a valid provider type.');
        }

        $providerUserId = null;
        $providerNameSnapshot = null;
        if ($providerType === 'user') {
            $providerUserId = intval($this->request->getPost('provider_user_id') ?? 0);
            if ($providerUserId <= 0) {
                throw new \RuntimeException('Please select a provider user.');
            }

            $membership = $this->clinicUserModel->findActiveMembershipByClinicAndUser($clinicId, $providerUserId);
            if (!$membership) {
                throw new \RuntimeException('Selected provider user is not active in this clinic.');
            }
        } else {
            $providerNameSnapshot = trim((string) $this->request->getPost('provider_name_snapshot'));
            if ($providerNameSnapshot === '') {
                throw new \RuntimeException('Please enter guest provider name.');
            }
        }

        $splitMode = strtolower((string) $this->request->getPost('provider_split_mode'));
        if (!in_array($splitMode, ['percent', 'fixed'], true)) {
            throw new \RuntimeException('Please select a valid split mode.');
        }

        $splitValue = floatval($this->request->getPost('provider_split_value') ?? 0);
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
        $clinicShareAmount = round($grossAmount - $providerShareAmount, 2);

        $markPaidNow = filter_var($this->request->getPost('provider_mark_paid_now'), FILTER_VALIDATE_BOOLEAN);
        $providerPaidAmount = 0.0;
        $providerPaymentMethod = null;
        $providerPaidDate = null;
        $providerPaymentNotes = null;

        if ($markPaidNow && $providerShareAmount > 0) {
            $providerPaidAmount = floatval($this->request->getPost('provider_paid_amount') ?? $providerShareAmount);
            $providerPaidAmount = max(0, min(round($providerPaidAmount, 2), $providerShareAmount));
            if ($providerPaidAmount > 0) {
                $providerPaymentMethod = (string) ($this->request->getPost('provider_paid_method') ?: 'cash');
                if (!in_array($providerPaymentMethod, ['cash', 'card', 'bank_transfer', 'check', 'other'], true)) {
                    $providerPaymentMethod = 'cash';
                }
                $providerPaidDate = (string) ($this->normalizePostedDateOnly('provider_paid_date') ?: date('Y-m-d'));
                $providerPaymentNotes = trim((string) $this->request->getPost('provider_paid_notes'));
            }
        }

        return [
            'provider_type' => $providerType,
            'provider_user_id' => $providerUserId ?: null,
            'provider_name_snapshot' => $providerNameSnapshot,
            'split_mode' => $splitMode,
            'split_value' => $splitValue,
            'gross_amount' => round($grossAmount, 2),
            'provider_share_amount' => round($providerShareAmount, 2),
            'clinic_share_amount' => round($clinicShareAmount, 2),
            'provider_paid_amount' => round($providerPaidAmount, 2),
            'provider_balance_due' => round(max(0, $providerShareAmount - $providerPaidAmount), 2),
            'status' => $providerShareAmount <= 0 ? 'paid' : (($providerPaidAmount >= $providerShareAmount) ? 'paid' : ($providerPaidAmount > 0 ? 'partial' : 'unpaid')),
            'currency' => settings()->get('currency', 'ILS'),
            'notes' => trim((string) $this->request->getPost('provider_split_notes')),
            'provider_payment_method' => $providerPaymentMethod,
            'provider_paid_date' => $providerPaidDate,
            'provider_payment_notes' => $providerPaymentNotes,
        ];
    }

    private function upsertProviderPayout(int $clinicId, int $financeId, int $patientId, float $grossAmount): void
    {
        $payload = $this->normalizeProviderSplitPayload($clinicId, $grossAmount);
        $existing = $this->providerPayoutModel->getByFinanceId($clinicId, $financeId);

        if ($payload === null) {
            if ($existing) {
                $paidTotal = $this->providerPayoutPaymentModel->getPaidTotal($clinicId, (int) $existing['id']);
                if ($paidTotal > 0) {
                    throw new \RuntimeException('Cannot disable provider split after payout payments are recorded.');
                }
                $this->providerPayoutModel->delete($existing['id']);
            }
            return;
        }

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

        if ($existing) {
            $paidTotal = $this->providerPayoutPaymentModel->getPaidTotal($clinicId, (int) $existing['id']);
            if ($paidTotal > $payload['provider_share_amount']) {
                throw new \RuntimeException('Provider share cannot be lower than payout already recorded.');
            }
            $payoutData['provider_paid_amount'] = round($paidTotal, 2);
            $payoutData['provider_balance_due'] = round(max(0, $payload['provider_share_amount'] - $paidTotal), 2);
            $payoutData['status'] = $payoutData['provider_balance_due'] <= 0 ? 'paid' : ($paidTotal > 0 ? 'partial' : 'unpaid');
            $this->providerPayoutModel->update((int) $existing['id'], $payoutData);
            $payoutId = (int) $existing['id'];
        } else {
            $this->providerPayoutModel->insert($payoutData);
            $payoutId = (int) $this->providerPayoutModel->getInsertID();
        }

        if ($payload['provider_paid_amount'] > 0) {
            $alreadyPaid = $this->providerPayoutPaymentModel->getPaidTotal($clinicId, $payoutId);
            if ($alreadyPaid <= 0) {
                $this->providerPayoutPaymentModel->insert([
                    'provider_payout_id' => $payoutId,
                    'amount' => $payload['provider_paid_amount'],
                    'payment_method' => $payload['provider_payment_method'] ?? 'cash',
                    'paid_date' => $payload['provider_paid_date'] ?? date('Y-m-d'),
                    'notes' => $payload['provider_payment_notes'] ?: 'Paid at transaction creation',
                    'created_by' => $this->resolveActorUserId(),
                ]);
            }
        }

        $this->syncProviderPayoutStatus($clinicId, $payoutId);
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
        $data = [
            'title' => 'Finance Management',
            'finances' => [], // Data will be loaded via AJAX
            'stats' => $clinicId ? $this->financeModel->getFinanceStatsByClinic($clinicId) : [],
            'disableDataTables' => true, // Disable DataTables scripts for custom implementation
            'currency_position' => settings()->get('currency_position', 'before'),
            'currency_symbol' => settings()->getCurrencySymbol()
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

        $returnTo = $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'finance']);
        if (!$returnTo && $patientId) {
            $returnTo = 'patient/' . $patientId;
        }

        $data = [
            'title' => 'New Financial Transaction',
            'patients' => $selectedPatient ? [$selectedPatient] : [], // S4-02f: No bulk preload
            'examinations' => [], // S4-02f: No bulk preload
            'clinic_providers' => $this->getClinicProviders((int) $clinicId),
            'validation' => \Config\Services::validation(),
            'loadSelect2' => true,
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => $this->getFinanceCurrencyOptions(),
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
            'expense_category' => 'required|in_list[' . implode(',', array_keys($this->getExpenseCategoryOptions())) . ']',
            'amount_original' => 'required|decimal|greater_than[0]',
            'currency' => 'required|in_list[ILS,USD,JOD,EUR,GBP]',
            'payment_method' => 'required|in_list[cash,card,bank_transfer,check,insurance,other]',
            'tax_percentage' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
            'paid_date' => 'permit_empty|valid_date',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
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
        $paidDate = $this->normalizePostedDateOnly('paid_date') ?: date('Y-m-d');

        $financeData = [
            'patient_id' => null,
            'examination_id' => null,
            'transaction_type' => 'expense',
            'amount' => $amount,
            'currency' => $currency,
            'amount_original' => $amountOriginal,
            'exchange_rate' => $exchangeRate,
            'payment_method' => $this->request->getPost('payment_method'),
            'payment_status' => 'paid',
            'description' => $this->request->getPost('description'),
            'service_type' => 'other',
            'expense_category' => $this->request->getPost('expense_category'),
            'counterparty_name' => $this->request->getPost('counterparty_name'),
            'service_details' => $this->request->getPost('service_details'),
            'discount_amount' => $discountAmount,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'amount_paid' => $totalAmount,
            'balance_due' => 0,
            'due_date' => null,
            'paid_date' => $paidDate,
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
            $attachmentFile = $this->request->getFile('attachment');
            if ($attachmentFile && $attachmentFile->isValid() && $attachmentFile->getName() !== '') {
                $validation = $this->validateFinanceAttachment($attachmentFile);
                if ($validation !== null) {
                    $this->db->transRollback();
                    return redirect()->back()->withInput()->with('error', $validation);
                }
                $this->attachmentModel->supersedePrevious($clinicId, 'finance', $financeId, 'attachment');
                $this->storageService->store($attachmentFile, $clinicId, 'finance', $financeId, 'attachment');
            }
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

            return redirect()->to(base_url('finance/' . $financeId))->with('success', 'Expense has been recorded successfully.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Expense create failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function store()
    {
        $clinicId = session()->get('active_clinic_id');
        $rules = [
            'patient_id' => 'required|integer',
            'transaction_type' => 'required|in_list[payment,invoice,refund,adjustment]',
            'amount_original' => 'required|decimal|greater_than[0]',
            'currency' => 'required|in_list[ILS,USD,JOD,EUR,GBP]',
            'payment_method' => 'required|in_list[cash,card,bank_transfer,check,insurance,other]',
            'service_type' => 'required|in_list[consultation,treatment,medication,procedure,other]',
            'tax_percentage' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $patientId = intval($this->request->getPost('patient_id') ?? 0);
        $examId = intval($this->request->getPost('examination_id') ?? 0);
        $ownershipError = $this->validateOwnedFinanceReferences((int) $clinicId, $patientId ?: null, $examId ?: null);
        if ($ownershipError !== null) {
            return redirect()->back()->withInput()->with('error', $ownershipError);
        }

        // Calculate total amount
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
        $transactionType = $this->request->getPost('transaction_type');
        $dueDate = $this->normalizePostedDateOnly('due_date');
        $paidDate = $this->normalizePostedDateOnly('paid_date');
        $today = date('Y-m-d');

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
            'examination_id' => $this->request->getPost('examination_id'),
            'transaction_type' => $transactionType,
            'amount' => $amount,
            'currency' => $currency,
            'amount_original' => $amountOriginal,
            'exchange_rate' => $exchangeRate,
            'payment_method' => $this->request->getPost('payment_method'),
            'payment_status' => $paymentStatus,
            'description' => $this->request->getPost('description'),
            'service_type' => $this->request->getPost('service_type'),
            'service_details' => $this->request->getPost('service_details'),
            'discount_amount' => $discountAmount,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'amount_paid' => $amountPaid,
            'balance_due' => $balanceDue,
            'due_date' => $dueDate,
            'paid_date' => $paidDate,
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
                $this->attachmentModel->supersedePrevious($clinicId, 'finance', $financeId, 'check_scan');
                $this->storageService->store($checkFile, $clinicId, 'finance', $financeId, 'check_scan');
            }

            if ($transactionType === 'invoice') {
                $this->syncInvoicePaymentStatus($financeId, session()->get('active_clinic_id'));
            }

            $this->upsertProviderPayout(
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

            $returnTo = $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['patient', 'patients', 'finance']);
            if ($returnTo !== null && !empty($financeData['patient_id'])) {
                if (preg_match('#^patient/(\\d+)$#', $returnTo, $matches) && (int) $matches[1] !== (int) $financeData['patient_id']) {
                    $returnTo = null;
                }
            }
            if ($returnTo !== null) {
                return redirect()->to(base_url($returnTo))->with('success', 'Financial transaction has been created successfully!');
            }

                return redirect()->to(base_url('finance'))->with('success', 'Financial transaction has been created successfully!');
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
        $examination = !empty($finance['examination_id'])
            ? $this->examinationModel->where('clinic_id', $clinicId)->find($finance['examination_id'])
            : null;
        $payments = [];
        $paidTotal = 0;
        $providerPayout = $this->providerPayoutModel->getByFinanceId((int) $clinicId, (int) $id);
        $providerPayoutPayments = [];
        if (!empty($providerPayout)) {
            if (!empty($providerPayout['provider_user_id'])) {
                $providerUser = $this->userModel
                    ->select('first_name, last_name')
                    ->find((int) $providerPayout['provider_user_id']);
                if ($providerUser) {
                    $providerPayout['provider_first_name'] = $providerUser['first_name'] ?? '';
                    $providerPayout['provider_last_name'] = $providerUser['last_name'] ?? '';
                }
            }
            $providerPayoutPayments = $this->providerPayoutPaymentModel
                ->where('clinic_id', $clinicId)
                ->where('provider_payout_id', $providerPayout['id'])
                ->orderBy('paid_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->findAll();
        }

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
            'title' => 'Financial Transaction Details - ' . $finance['transaction_id'],
            'finance' => $finance,
            'patient' => $patient,
            'examination' => $examination,
            'payments' => $payments,
            'paid_total' => $paidTotal,
            'provider_payout' => $providerPayout,
            'provider_payout_payments' => $providerPayoutPayments,
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => $this->getFinanceCurrencyOptions(),
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
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'finance'])
        ];

        return $this->view('finance/show', $data);
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
            $data['return_to'] = $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['finance']);
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
            'provider_payout' => $this->providerPayoutModel->getByFinanceId((int) $clinicId, (int) $id),
            'validation' => \Config\Services::validation(),
            'loadSelect2' => true,
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => $this->getFinanceCurrencyOptions(),
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
            'tax_percentage' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]'
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
        if ($currentType !== 'invoice') {
            $amountPaid = $totalAmount;
            $paymentStatus = 'paid';
            $paidDate = $paidDate ?: date('Y-m-d');
            $dueDate = null;
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
                    $this->attachmentModel->supersedePrevious($clinicId, 'finance', $id, 'check_scan');
                    $this->storageService->store($checkFile, $clinicId, 'finance', $id, 'check_scan');
                }

                if ($financeData['transaction_type'] === 'invoice') {
                    $this->syncInvoicePaymentStatus($id, $clinicId);
                }

                $this->upsertProviderPayout(
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
                    return redirect()->to(base_url($returnTo))->with('success', 'Financial transaction has been updated successfully!');
                }

                return redirect()->to(base_url('finance/' . $id))->with('success', 'Financial transaction has been updated successfully!');
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
            'expense_category' => 'required|in_list[' . implode(',', array_keys($this->getExpenseCategoryOptions())) . ']',
            'amount_original' => 'required|decimal|greater_than[0]',
            'currency' => 'required|in_list[ILS,USD,JOD,EUR,GBP]',
            'payment_method' => 'required|in_list[cash,card,bank_transfer,check,insurance,other]',
            'tax_percentage' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
            'paid_date' => 'permit_empty|valid_date',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
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
        $paidDate = $this->normalizePostedDateOnly('paid_date') ?: ($finance['paid_date'] ?? date('Y-m-d'));

        $update = [
            'patient_id' => null,
            'examination_id' => null,
            'transaction_type' => 'expense',
            'amount' => $amount,
            'currency' => $currency,
            'amount_original' => $amountOriginal,
            'exchange_rate' => $exchangeRate,
            'payment_method' => $this->request->getPost('payment_method'),
            'payment_status' => 'paid',
            'description' => $this->request->getPost('description'),
            'service_type' => 'other',
            'expense_category' => $this->request->getPost('expense_category'),
            'counterparty_name' => $this->request->getPost('counterparty_name'),
            'service_details' => $this->request->getPost('service_details'),
            'discount_amount' => $discountAmount,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'amount_paid' => $totalAmount,
            'balance_due' => 0,
            'due_date' => null,
            'paid_date' => $paidDate,
            'notes' => $this->request->getPost('notes'),
        ];

        try {
            $this->db->transBegin();

            if (!$this->financeModel->update($id, $update)) {
                $this->db->transRollback();
                return redirect()->back()->withInput()->with('error', implode(', ', $this->financeModel->errors()));
            }

            $attachmentFile = $this->request->getFile('attachment');
            if ($attachmentFile && $attachmentFile->isValid() && $attachmentFile->getName() !== '') {
                $validation = $this->validateFinanceAttachment($attachmentFile);
                if ($validation !== null) {
                    $this->db->transRollback();
                    return redirect()->back()->withInput()->with('error', $validation);
                }
                $this->attachmentModel->supersedePrevious($clinicId, 'finance', $id, 'attachment');
                $this->storageService->store($attachmentFile, $clinicId, 'finance', $id, 'attachment');
            }

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

            $returnTo = $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['finance']);
            return redirect()->to($returnTo ? base_url($returnTo) : base_url('finance/' . $id))
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
        $providerPayoutStats = $this->providerPayoutModel->getStatsByClinic((int) $clinicId, null, $endDate);

        $moneyIn = (float) ($stats['gross_collections'] ?? 0);
        $moneyOut = (float) (($stats['refunds_total'] ?? 0) + ($stats['expenses_paid'] ?? 0) + ($stats['provider_paid_cash_total'] ?? 0));
        $customersOweUs = (float) ($arAgingSummary['total_balance_due'] ?? 0);
        $weOweOthers = (float) ($providerPayoutStats['total_provider_balance'] ?? 0);
        $netPosition = $moneyIn - $moneyOut + $customersOweUs - $weOweOthers;

        $data = [
            'title' => 'Financial Reports',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'stats' => $stats,
            'monthly_revenue_labels' => $monthlyRevenue['labels'],
            'monthly_revenue_values' => $monthlyRevenue['values'],
            'ledger_rows' => $ledger['rows'],
            'ledger_summary' => $ledger['summary'],
            'ar_aging_summary' => $arAgingSummary,
            'ar_aging_detail' => $arAgingDetail,
            'owner_summary' => [
                'money_in' => round($moneyIn, 2),
                'money_out' => round($moneyOut, 2),
                'customers_owe_us' => round($customersOweUs, 2),
                'we_owe_others' => round($weOweOthers, 2),
                'net_position' => round($netPosition, 2),
                'as_of_date' => $endDate,
            ],
            'currency_position' => settings()->get('currency_position', 'before'),
            'currency_symbol' => settings()->getCurrencySymbol()
        ];

        return $this->view('finance/reports', $data);
    }

    private function buildFinancialLedger(int $clinicId, string $startDate, string $endDate): array
    {
        $baseCurrency = settings()->get('currency', 'ILS');
        $financeAmountExpr = $this->db->fieldExists('total_amount', 'finances')
            ? 'f.total_amount'
            : '(f.amount - COALESCE(f.discount_amount, 0) + COALESCE(f.tax_amount, 0))';
        $rows = [];

        $financeRows = $this->db->table('finances f')
            ->select("
                f.id,
                f.transaction_id,
                f.transaction_type,
                f.payment_method,
                f.payment_status,
                f.description,
                f.created_at,
                f.paid_date,
                f.currency,
                f.exchange_rate,
                f.amount_original,
                f.counterparty_name,
                f.expense_category,
                f.amount,
                f.discount_amount,
                f.tax_amount,
                {$financeAmountExpr} AS amount_base,
                p.first_name AS patient_first_name,
                p.last_name AS patient_last_name,
                p.patient_id AS patient_number
            ")
            ->join('patients p', 'p.id = f.patient_id AND p.clinic_id = f.clinic_id', 'left')
            ->where('f.clinic_id', $clinicId)
            ->whereIn('f.transaction_type', ['payment', 'refund', 'expense'])
            ->where('COALESCE(f.paid_date, DATE(f.created_at)) >= ' . $this->db->escape($startDate), null, false)
            ->where('COALESCE(f.paid_date, DATE(f.created_at)) <= ' . $this->db->escape($endDate), null, false)
            ->orderBy('COALESCE(f.paid_date, DATE(f.created_at))', 'DESC', false)
            ->orderBy('f.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($financeRows as $row) {
            $amountBase = isset($row['amount_base']) ? floatval($row['amount_base']) : 0.0;
            $type = (string) ($row['transaction_type'] ?? '');
            $signedAmount = in_array($type, ['refund', 'expense'], true) ? -$amountBase : $amountBase;
            $patientName = trim((string) (($row['patient_first_name'] ?? '') . ' ' . ($row['patient_last_name'] ?? '')));
            $reportDate = !empty($row['paid_date']) ? (string) $row['paid_date'] : substr((string) ($row['created_at'] ?? ''), 0, 10);

            $rows[] = [
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

        $invoicePaymentRows = $this->db->table('finance_payments fp')
            ->select("
                fp.id,
                fp.type,
                fp.payment_method,
                fp.payment_date,
                fp.notes,
                fp.amount,
                {$paymentFxSelect}
                f.transaction_id AS invoice_transaction_id,
                f.currency,
                p.first_name AS patient_first_name,
                p.last_name AS patient_last_name,
                p.patient_id AS patient_number
            ")
            ->join('finances f', 'f.id = fp.finance_id AND f.clinic_id = fp.clinic_id', 'inner')
            ->join('patients p', 'p.id = f.patient_id AND p.clinic_id = f.clinic_id', 'left')
            ->where('fp.clinic_id', $clinicId)
            ->where('fp.payment_date >=', $startDate)
            ->where('fp.payment_date <=', $endDate)
            ->whereIn('fp.type', ['payment', 'refund', 'void'])
            ->orderBy('fp.payment_date', 'DESC')
            ->orderBy('fp.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($invoicePaymentRows as $row) {
            $amountBase = floatval($row['amount'] ?? 0);
            $type = (string) ($row['type'] ?? '');
            $signedAmount = in_array($type, ['refund', 'void'], true) ? -$amountBase : $amountBase;
            $patientName = trim((string) (($row['patient_first_name'] ?? '') . ' ' . ($row['patient_last_name'] ?? '')));
            $normalizedType = $type === 'void' ? 'void' : $type;

            $rows[] = [
                'event_source' => 'invoice_settlement',
                'source_label' => 'Invoice settlement',
                'event_type' => $normalizedType,
                'event_at' => ((string) ($row['payment_date'] ?? '')) . ' 00:00:00',
                'report_date' => (string) ($row['payment_date'] ?? ''),
                'created_at' => ((string) ($row['payment_date'] ?? '')) . ' 00:00:00',
                'reference' => (string) (($row['invoice_transaction_id'] ?? 'Invoice') . ' / PMT-' . ($row['id'] ?? '')),
                'patient_label' => $patientName !== '' ? $patientName : null,
                'patient_number' => $row['patient_number'] ?? null,
                'counterparty' => null,
                'payment_method' => $row['payment_method'] ?? null,
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

        $payoutPaymentRows = $this->db->table('provider_payout_payments ppp')
            ->select("
                ppp.id,
                ppp.amount,
                ppp.payment_method,
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
            ->where('ppp.clinic_id', $clinicId)
            ->where('ppp.paid_date >=', $startDate)
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

        return [
            'rows' => $rows,
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
            $dueDate = !empty($row['due_date']) ? (string) $row['due_date'] : substr((string) ($row['created_at'] ?? $asOfDate), 0, 10);
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
        $openInvoicesBalance = 0.0;
        foreach ($openInvoices as $invoice) {
            $openInvoicesBalance += floatval($invoice['balance_due'] ?? 0);
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
                    'patient_name' => $patientName,
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

            if ($finance['transaction_type'] === 'invoice') {
                $totalAmount = floatval($finance['total_amount'] ?? ($finance['amount'] - $finance['discount_amount'] + $finance['tax_amount']));
                $paidTotal = $this->financePaymentModel->getPaidTotalByInvoice($clinicId, $finance['id']);
                $remaining = max(0, $totalAmount - $paidTotal);

                if ($remaining <= 0) {
                    return $this->response->setJSON(['success' => true, 'message' => 'Invoice already fully paid.']);
                }

                $paymentData = [
                    'finance_id' => $finance['id'],
                    'amount' => $remaining,
                    'type' => 'payment',
                    'payment_method' => $finance['payment_method'] ?? 'cash',
                    'payment_date' => date('Y-m-d'),
                    'notes' => 'Full payment via Mark as Paid',
                    'created_by' => $this->resolveActorUserId()
                ];

                $this->financePaymentModel->insert($paymentData);
                $this->syncInvoicePaymentStatus($finance['id'], $clinicId);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Invoice marked as paid.',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $updateData = [
                'payment_status' => 'paid',
                'paid_date' => date('Y-m-d'),
                'amount_paid' => floatval($finance['total_amount'] ?? ($finance['amount'] - $finance['discount_amount'] + $finance['tax_amount'])),
                'balance_due' => 0
            ];

            $this->financeModel->setValidationRules([]);
            $result = $this->financeModel->update($id, $updateData);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Transaction marked as paid',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $errors = $this->financeModel->errors();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update transaction: ' . implode(', ', $errors),
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
            $settlement = $this->normalizeInvoiceSettlementInput($finance, $remaining);

            $paymentData = [
                'finance_id' => $finance['id'],
                'amount' => $settlement['amount_base'],
                'type' => 'payment',
                'payment_method' => $settlement['payment_method'],
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

            $this->syncInvoicePaymentStatus($finance['id'], $clinicId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Payment added successfully.',
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

    private function syncInvoicePaymentStatus($invoiceId, $clinicId)
    {
        $finance = $this->financeModel->findByClinic($clinicId, $invoiceId);
        if (!$finance || $finance['transaction_type'] !== 'invoice') {
            return;
        }

        $totalAmount = floatval($finance['total_amount'] ?? ($finance['amount'] - $finance['discount_amount'] + $finance['tax_amount']));
        $paidTotal = $this->financePaymentModel->getPaidTotalByInvoice($clinicId, $finance['id']);
        $balanceDue = max(0, $totalAmount - $paidTotal);

        $status = 'pending';
        if ($balanceDue <= 0) {
            $status = 'paid';
        } elseif (!empty($finance['due_date']) && strtotime($finance['due_date']) < strtotime(date('Y-m-d'))) {
            $status = 'overdue';
        } elseif ($paidTotal > 0 && $balanceDue > 0) {
            $status = 'partial';
        }

        $updateData = [
            'amount_paid' => $paidTotal,
            'balance_due' => $balanceDue,
            'payment_status' => $status,
        ];

        if ($status === 'paid' && empty($finance['paid_date'])) {
            $updateData['paid_date'] = date('Y-m-d');
        }

        $this->financeModel->setValidationRules([]);
        $this->financeModel->update($finance['id'], $updateData);
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
                $query->where('finances.created_at >=', $startDate . ' 00:00:00')
                    ->where('finances.created_at <=', $endDate . ' 23:59:59');
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
              $fileName = 'finance_raw_transactions_' . date('Y-m-d_H-i-s') . '.csv';

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

        fputcsv($output, ['Export Type', 'Raw finance transaction export']);
        fputcsv($output, ['Report Basis', 'Operational raw transaction list']);
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
            'Payment Status',
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
                $row['payment_status'],
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
