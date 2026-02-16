<?php

namespace App\Controllers;

use App\Models\FinanceModel;
use App\Models\FinancePaymentModel;
use App\Models\FileAttachmentModel;
use App\Models\PatientModel;
use App\Models\ExaminationModel;
use App\Services\ActivityLogger;
use App\Libraries\ApiRuntimeContext;

class Finance extends BaseController
{
    protected $financeModel;
    protected $financePaymentModel;
    protected $attachmentModel;
    protected $patientModel;
    protected $examinationModel;
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

        $returnTo = $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'finance']);
        if (!$returnTo && $patientId) {
            $returnTo = 'patient/' . $patientId;
        }

        $data = [
            'title' => 'New Financial Transaction',
            'patients' => $selectedPatient ? [$selectedPatient] : [], // S4-02f: No bulk preload
            'examinations' => [], // S4-02f: No bulk preload
            'validation' => \Config\Services::validation(),
            'loadSelect2' => true,
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => ['ILS', 'USD', 'JOD', 'EUR', 'GBP'],
            'selected_patient_id' => $patientId,
            'return_to' => $returnTo
        ];

        return $this->view('finance/create', $data);
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
        $dueDate = $this->request->getPost('due_date');
        $paidDate = $this->request->getPost('paid_date');
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

            // Get patient name for the activity log
            $patient = $this->patientModel->find($financeData['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';

            // Log the finance creation activity
            $this->activityLogger->logFinanceActivity(
                'create',
                $financeId,
                "New {$financeData['transaction_type']} of " . settings()->getCurrencySymbol() . number_format($totalAmount, 2) . " created for {$patientName}"
            );

            $returnTo = $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['patient', 'finance']);
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
            $errors = $this->financeModel->errors();
            log_message('error', 'Finance creation failed: ' . json_encode($errors));
            log_message('error', 'Finance data that failed: ' . json_encode($financeData));
            return redirect()->back()->withInput()->with('error', 'Failed to create financial transaction. Please check your input and try again.');
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
            'title' => 'Financial Transaction Details - ' . $finance['transaction_id'],
            'finance' => $finance,
            'patient' => $patient,
            'examination' => $examination,
            'payments' => $payments,
            'paid_total' => $paidTotal,
            'base_currency' => settings()->get('currency', 'ILS'),
            'check_attachment' => $this->attachmentModel
                ->where('clinic_id', $clinicId)
                ->where('entity_type', 'finance')
                ->where('entity_id', $finance['id'])
                ->where('purpose', 'check_scan')
                ->first(),
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'finance'])
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

        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($finance['patient_id']);
        $examination = $finance['examination_id'] ? $this->examinationModel->where('clinic_id', $clinicId)->find($finance['examination_id']) : null;

        $data = [
            'title' => 'Edit Financial Transaction - ' . $finance['transaction_id'],
            'finance' => $finance,
            'patients' => $patient ? [$patient] : [],
            'examinations' => $examination ? [$examination] : [],
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
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'finance'])
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
        $requestedType = (string) $this->request->getPost('transaction_type');
        $currentType = (string) ($finance['transaction_type'] ?? '');
        if ($requestedType !== $currentType) {
            return redirect()->back()->withInput()->with('error', 'Transaction type cannot be changed after creation.');
        }

        $amountPaid = floatval($finance['amount_paid'] ?? 0);
        $paymentStatus = (string) ($finance['payment_status'] ?? 'pending');
        $dueDate = $this->request->getPost('due_date');
        $paidDate = $this->request->getPost('paid_date');
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
            // Get patient name for the activity log (scoped)
            $patient = $this->patientModel->where('clinic_id', $clinicId)->find($financeData['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';

            // Log the finance update activity
            $this->activityLogger->logFinanceActivity(
                'update',
                $id,
                "Financial transaction updated for {$patientName} - {$financeData['transaction_type']} of " . settings()->getCurrencySymbol() . number_format($financeData['amount'], 2)
            );

            $returnTo = $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['patient', 'finance']);
            if ($returnTo) {
                return redirect()->to(base_url($returnTo))->with('success', 'Financial transaction has been updated successfully!');
            }

            return redirect()->to(base_url('finance/' . $id))->with('success', 'Financial transaction has been updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update financial transaction. Please check your input and try again.');
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
                        ['patient', 'finance']
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

        $data = [
            'title' => 'Financial Reports',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'stats' => $this->financeModel->getFinanceStatsByClinic($clinicId, $startDate, $endDate),
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
                $data[] = [
                    'id' => intval($finance['id'] ?? 0),
                    'transaction_id' => $finance['transaction_id'] ?? ('TXN-' . str_pad($finance['id'], 6, '0', STR_PAD_LEFT)),
                    'patient_name' => trim(($finance['first_name'] ?? '') . ' ' . ($finance['last_name'] ?? '')),
                    'phone' => $finance['phone'] ?? '',
                    'transaction_type' => $finance['transaction_type'] ?? '',
                    'amount' => $amount,
                    'amount_formatted' => formatCurrency($amount),
                    'payment_status' => $finance['payment_status'] ?? '',
                    'description' => $finance['description'] ?? '',
                    'created_at' => $finance['created_at'] ?? '',
                    'created_at_formatted' => formatDateTime($finance['created_at'] ?? ''),
                    'patient_id' => intval($finance['patient_id'] ?? 0)
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
            'Transaction Type',
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
                $row['transaction_type'],
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
