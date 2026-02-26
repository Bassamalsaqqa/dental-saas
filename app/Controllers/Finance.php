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
        return [
            'title' => $finance ? ('Edit Expense - ' . ($finance['transaction_id'] ?? '')) : 'New Expense',
            'finance' => $finance,
            'validation' => \Config\Services::validation(),
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => ['ILS', 'USD', 'JOD', 'EUR', 'GBP'],
            'expense_category_options' => $this->getExpenseCategoryOptions(),
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
            'currency_options' => ['ILS', 'USD', 'JOD', 'EUR', 'GBP'],
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
            'check_attachment' => $this->attachmentModel
                ->where('clinic_id', $clinicId)
                ->where('entity_type', 'finance')
                ->where('entity_id', $finance['id'])
                ->where('purpose', 'check_scan')
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
            'currency_options' => ['ILS', 'USD', 'JOD', 'EUR', 'GBP'],
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
            if (!$this->financeModel->update($id, $update)) {
                return redirect()->back()->withInput()->with('error', implode(', ', $this->financeModel->errors()));
            }

            $this->activityLogger->logFinanceActivity(
                'update',
                $id,
                "Expense transaction updated for " . ($update['counterparty_name'] ?: 'counterparty')
            );

            $returnTo = $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['finance']);
            return redirect()->to($returnTo ? base_url($returnTo) : base_url('finance/' . $id))
                ->with('success', 'Expense updated successfully.');
        } catch (\Throwable $e) {
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

        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');

        $stats = $this->financeModel->getFinanceStatsByClinic($clinicId, $startDate, $endDate);
        $providerStats = $this->providerPayoutModel->getStatsByClinic((int) $clinicId, $startDate, $endDate);
        $grossCollections = floatval($stats['total_revenue'] ?? 0);
        $providerShare = floatval($providerStats['total_provider_share'] ?? 0); // accrual
        $providerPaidCash = floatval($providerStats['total_provider_paid_cash'] ?? 0); // cash basis
        $providerPayable = floatval($providerStats['total_provider_balance'] ?? 0);
        $stats['gross_collections'] = $grossCollections;
        $stats['provider_share_total'] = $providerShare;
        $stats['provider_paid_cash_total'] = $providerPaidCash;
        $stats['provider_payable_outstanding'] = $providerPayable;
        $stats['net_clinic_revenue'] = $grossCollections - $providerPaidCash;
        $stats['net_clinic_revenue_accrual'] = $grossCollections - $providerShare;

        $data = [
            'title' => 'Financial Reports',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'stats' => $stats,
            'provider_payout_stats' => $providerStats,
            'monthly_revenue' => $this->financeModel->getMonthlyRevenueByClinic($clinicId),
            'payment_methods' => $this->financeModel->getPaymentMethodsStats($startDate, $endDate, $clinicId),
            'service_types' => $this->financeModel->getServiceTypeStats($startDate, $endDate, $clinicId),
            'overdue_payments' => $this->financeModel->getOverduePayments($clinicId),
            'currency_position' => settings()->get('currency_position', 'before'),
            'currency_symbol' => settings()->getCurrencySymbol()
        ];

        return $this->view('finance/reports', $data);
    }

    public function getFinanceStats()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $stats = $this->financeModel->getFinanceStatsByClinic($clinicId, $startDate, $endDate);
        $providerStats = $this->providerPayoutModel->getStatsByClinic((int) $clinicId, $startDate, $endDate);

        $grossCollections = floatval($stats['total_revenue'] ?? 0);
        $providerShare = floatval($providerStats['total_provider_share'] ?? 0); // accrual
        $providerPaidCash = floatval($providerStats['total_provider_paid_cash'] ?? 0); // cash basis
        $providerBalance = floatval($providerStats['total_provider_balance'] ?? 0);
        $netClinicRevenue = $grossCollections - $providerPaidCash;
        $netClinicRevenueAccrual = $grossCollections - $providerShare;

        $stats['gross_collections'] = $grossCollections;
        $stats['provider_share_total'] = $providerShare;
        $stats['provider_paid_cash_total'] = $providerPaidCash;
        $stats['provider_payable_outstanding'] = $providerBalance;
        $stats['net_clinic_revenue'] = $netClinicRevenue;
        $stats['net_clinic_revenue_accrual'] = $netClinicRevenueAccrual;

        // Format currency values
        if (isset($stats['total_revenue'])) {
            $stats['total_revenue_formatted'] = formatCurrency($stats['total_revenue']);
        }
        if (isset($stats['pending_payments'])) {
            $stats['pending_payments_formatted'] = formatCurrency($stats['pending_payments']);
        }
        if (isset($stats['monthly_revenue'])) {
            $stats['monthly_revenue_formatted'] = formatCurrency($stats['monthly_revenue']);
        }
        $stats['gross_collections_formatted'] = formatCurrency($grossCollections);
        $stats['provider_share_total_formatted'] = formatCurrency($providerShare);
        $stats['provider_paid_cash_total_formatted'] = formatCurrency($providerPaidCash);
        $stats['provider_payable_outstanding_formatted'] = formatCurrency($providerBalance);
        $stats['net_clinic_revenue_formatted'] = formatCurrency($netClinicRevenue);
        $stats['net_clinic_revenue_accrual_formatted'] = formatCurrency($netClinicRevenueAccrual);
        if (isset($stats['total_transactions'])) {
            $stats['total_transactions_formatted'] = formatCurrency($stats['total_transactions']);
        }

        return $this->response->setJSON($stats);
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

            $amount = floatval($this->request->getPost('amount'));
            if ($amount <= 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment amount must be greater than 0.',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $totalAmount = floatval($finance['total_amount'] ?? ($finance['amount'] - $finance['discount_amount'] + $finance['tax_amount']));
            $paidTotal = $this->financePaymentModel->getPaidTotalByInvoice($clinicId, $finance['id']);
            $remaining = max(0, $totalAmount - $paidTotal);

            if ($amount > $remaining) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment exceeds remaining balance.',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $paymentData = [
                'finance_id' => $finance['id'],
                'amount' => $amount,
                'type' => 'payment',
                'payment_method' => $this->request->getPost('payment_method') ?: ($finance['payment_method'] ?? 'cash'),
                'payment_date' => $this->request->getPost('payment_date') ?? date('Y-m-d'),
                'notes' => $this->request->getPost('notes'),
                'created_by' => $this->resolveActorUserId()
            ];

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

            $amount = floatval($this->request->getPost('amount'));
            if ($amount <= 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Refund amount must be greater than 0.',
                    'csrf_token' => csrf_hash()
                ]);
            }

            $netPaid = $this->financePaymentModel->getPaidTotalByInvoice($clinicId, $finance['id']);
            if ($amount > $netPaid) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Refund exceeds paid amount.',
                    'csrf_token' => csrf_hash()
                ]);
            }

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
                'amount' => $amount,
                'type' => 'refund',
                'payment_method' => $this->request->getPost('payment_method') ?: ($finance['payment_method'] ?? 'cash'),
                'payment_date' => $this->request->getPost('payment_date') ?? date('Y-m-d'),
                'notes' => $this->request->getPost('notes'),
                'related_payment_id' => $relatedPaymentId ?: null,
                'created_by' => $this->resolveActorUserId()
            ];

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

            if (!$this->financePaymentModel->insert([
                'finance_id' => $finance['id'],
                'amount' => $remaining,
                'type' => 'adjustment',
                'payment_method' => $finance['payment_method'] ?? 'other',
                'payment_date' => date('Y-m-d'),
                'notes' => $notes,
                'created_by' => $this->resolveActorUserId()
            ])) {
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
            $fileName = 'financial_data_' . date('Y-m-d_H-i-s') . '.csv';

            if ($format === 'csv') {
                $content = $this->generateCSVContent($data);
            } else {
                $content = $this->generateCSVContent($data); // Fallback
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

    private function generateCSVContent($data)
    {
        $output = fopen('php://temp', 'r+');
        $baseCurrency = settings()->get('currency', 'ILS');

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
            'Created Date'
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

        return $content;
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

                    $this->financePaymentModel->insert([
                        'finance_id' => $financeId,
                        'amount' => $remaining,
                        'type' => 'payment',
                        'payment_method' => $finance['payment_method'] ?? 'cash',
                        'payment_date' => $today,
                        'notes' => 'Bulk mark as paid',
                        'created_by' => $this->resolveActorUserId()
                    ]);
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
