<?php

namespace App\Controllers;

use App\Models\FinanceModel;
use App\Models\PatientModel;
use App\Models\ExaminationModel;
use App\Services\ActivityLogger;

class Finance extends BaseController
{
    protected $financeModel;
    protected $patientModel;
    protected $examinationModel;
    protected $activityLogger;
    protected $db;

    public function __construct()
    {
        $this->financeModel = new FinanceModel();
        $this->patientModel = new PatientModel();
        $this->examinationModel = new ExaminationModel();
        $this->activityLogger = new ActivityLogger();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data = [
            'title' => 'Finance Management',
            'finances' => [], // Data will be loaded via AJAX
            'stats' => $this->financeModel->getFinanceStats(),
            'disableDataTables' => true, // Disable DataTables scripts for custom implementation
            'currency_position' => settings()->get('currency_position', 'before'),
            'currency_symbol' => settings()->getCurrencySymbol()
        ];

        return $this->view('finance/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'New Financial Transaction',
            'patients' => $this->patientModel->where('status', 'active')->findAll(),
            'examinations' => $this->examinationModel->where('status', 'completed')->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return $this->view('finance/create', $data);
    }

    public function store()
    {
        $rules = [
            'patient_id' => 'required|integer',
            'transaction_type' => 'required|in_list[payment,invoice,refund,adjustment]',
            'amount' => 'required|decimal|greater_than[0]',
            'payment_method' => 'required|in_list[cash,card,bank_transfer,check,other]',
            'service_type' => 'required|in_list[consultation,treatment,medication,procedure,other]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Calculate total amount
        $amount = floatval($this->request->getPost('amount'));
        $discountAmount = floatval($this->request->getPost('discount_amount') ?? 0);
        $taxAmount = floatval($this->request->getPost('tax_amount') ?? 0);
        $totalAmount = $amount - $discountAmount + $taxAmount;

        $financeData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'examination_id' => $this->request->getPost('examination_id'),
            'transaction_type' => $this->request->getPost('transaction_type'),
            'amount' => $amount,
            'currency' => settings()->get('currency', 'USD'),
            'payment_method' => $this->request->getPost('payment_method'),
            'payment_status' => $this->request->getPost('payment_status') ?? 'pending',
            'description' => $this->request->getPost('description'),
            'service_type' => $this->request->getPost('service_type'),
            'service_details' => $this->request->getPost('service_details'),
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'due_date' => $this->request->getPost('due_date'),
            'paid_date' => $this->request->getPost('paid_date'),
            'notes' => $this->request->getPost('notes'),
            'created_by' => 1
        ];

        if ($this->financeModel->insert($financeData)) {
            $financeId = $this->financeModel->getInsertID();

            // Get patient name for the activity log
            $patient = $this->patientModel->find($financeData['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';

            // Log the finance creation activity
            $this->activityLogger->logFinanceActivity(
                'create',
                $financeId,
                "New {$financeData['transaction_type']} of " . settings()->getCurrencySymbol() . number_format($totalAmount, 2) . " created for {$patientName}"
            );

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
        $finance = $this->financeModel->find($id);

        if (!$finance) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Financial transaction not found');
        }

        $patient = $this->patientModel->find($finance['patient_id']);
        $examination = $finance['examination_id'] ? $this->examinationModel->find($finance['examination_id']) : null;

        $data = [
            'title' => 'Financial Transaction Details - ' . $finance['transaction_id'],
            'finance' => $finance,
            'patient' => $patient,
            'examination' => $examination
        ];

        return $this->view('finance/show', $data);
    }

    public function edit($id)
    {
        $finance = $this->financeModel->find($id);

        if (!$finance) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Financial transaction not found');
        }

        $data = [
            'title' => 'Edit Financial Transaction - ' . $finance['transaction_id'],
            'finance' => $finance,
            'patients' => $this->patientModel->where('status', 'active')->findAll(),
            'examinations' => $this->examinationModel->where('status', 'completed')->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return $this->view('finance/edit', $data);
    }

    public function update($id)
    {
        $finance = $this->financeModel->find($id);

        if (!$finance) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Financial transaction not found');
        }

        $rules = [
            'patient_id' => 'required|integer',
            'transaction_type' => 'required|in_list[payment,invoice,refund,adjustment]',
            'amount' => 'required|decimal|greater_than[0]',
            'payment_method' => 'required|in_list[cash,card,bank_transfer,check,other]',
            'service_type' => 'required|in_list[consultation,treatment,medication,procedure,other]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $financeData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'examination_id' => $this->request->getPost('examination_id'),
            'transaction_type' => $this->request->getPost('transaction_type'),
            'amount' => $this->request->getPost('amount'),
            'currency' => settings()->get('currency', 'USD'),
            'payment_method' => $this->request->getPost('payment_method'),
            'payment_status' => $this->request->getPost('payment_status'),
            'description' => $this->request->getPost('description'),
            'service_type' => $this->request->getPost('service_type'),
            'service_details' => $this->request->getPost('service_details'),
            'discount_amount' => $this->request->getPost('discount_amount') ?? 0,
            'tax_amount' => $this->request->getPost('tax_amount') ?? 0,
            'due_date' => $this->request->getPost('due_date'),
            'paid_date' => $this->request->getPost('paid_date'),
            'notes' => $this->request->getPost('notes')
        ];

        if ($this->financeModel->update($id, $financeData)) {
            // Get patient name for the activity log
            $patient = $this->patientModel->find($financeData['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';

            // Log the finance update activity
            $this->activityLogger->logFinanceActivity(
                'update',
                $id,
                "Financial transaction updated for {$patientName} - {$financeData['transaction_type']} of " . settings()->getCurrencySymbol() . number_format($financeData['amount'], 2)
            );

            return redirect()->to(base_url('finance/' . $id))->with('success', 'Financial transaction has been updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update financial transaction. Please check your input and try again.');
        }
    }

    public function delete($id)
    {
        try {
            $finance = $this->financeModel->find($id);

            if (!$finance) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Financial transaction not found']);
                } else {
                    return redirect()->back()->with('error', 'Financial transaction not found');
                }
            }

            if ($this->financeModel->delete($id)) {
                // Get patient name for the activity log
                $patient = $this->patientModel->find($finance['patient_id']);
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
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');

        $data = [
            'title' => 'Financial Reports',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'stats' => $this->financeModel->getFinanceStats($startDate, $endDate),
            'monthly_revenue' => $this->financeModel->getMonthlyRevenue(),
            'payment_methods' => $this->financeModel->getPaymentMethodsStats($startDate, $endDate),
            'service_types' => $this->financeModel->getServiceTypeStats($startDate, $endDate),
            'overdue_payments' => $this->financeModel->getOverduePayments(),
            'currency_position' => settings()->get('currency_position', 'before'),
            'currency_symbol' => settings()->getCurrencySymbol()
        ];

        return $this->view('finance/reports', $data);
    }

    public function getFinanceStats()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $stats = $this->financeModel->getFinanceStats($startDate, $endDate);

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
        $year = $this->request->getGet('year') ?? date('Y');

        $revenue = $this->financeModel->getMonthlyRevenue($year);

        return $this->response->setJSON($revenue);
    }

    public function markAsPaid($id)
    {
        try {
            $finance = $this->financeModel->find($id);

            if (!$finance) {
                return $this->response->setJSON(['success' => false, 'message' => 'Financial transaction not found']);
            }

            $updateData = [
                'payment_status' => 'paid',
                'paid_date' => date('Y-m-d')
            ];

            // Add debugging
            log_message('info', 'Marking transaction as paid - ID: ' . $id . ', Data: ' . json_encode($updateData));

            // Skip validation for this specific update
            $this->financeModel->setValidationRules([]);
            $result = $this->financeModel->update($id, $updateData);

            if ($result) {
                log_message('info', 'Transaction marked as paid successfully - ID: ' . $id);
                return $this->response->setJSON(['success' => true, 'message' => 'Transaction marked as paid']);
            } else {
                $errors = $this->financeModel->errors();
                log_message('error', 'Failed to update transaction - ID: ' . $id . ', Errors: ' . json_encode($errors));
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to update transaction: ' . implode(', ', $errors)]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception in markAsPaid - ID: ' . $id . ', Error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function generateInvoice($id)
    {
        $finance = $this->financeModel->find($id);

        if (!$finance) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Financial transaction not found');
        }

        $patient = $this->patientModel->find($finance['patient_id']);
        $examination = $finance['examination_id'] ? $this->examinationModel->find($finance['examination_id']) : null;

        $data = [
            'title' => 'Invoice - ' . $finance['transaction_id'],
            'finance' => $finance,
            'patient' => $patient,
            'examination' => $examination,
            'clinic' => settings()->getClinicInfo()
        ];

        return $this->view('finance/invoice', $data);
    }

    public function testFinancesData()
    {
        try {
            // Test database connection
            $tables = $this->db->listTables();

            // Test finance table (check both possible names)
            $financeTableName = in_array('finances', $tables) ? 'finances' : 'finance';
            $financeCount = $this->db->table($financeTableName)->countAllResults();

            // Get a sample finance record
            $sampleFinance = $this->db->table($financeTableName)->limit(1)->get()->getRowArray();

            return $this->response->setJSON([
                'success' => true,
                'tables' => $tables,
                'finance_table_name' => $financeTableName,
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
                'finances.amount',
                'finances.payment_status',
                'finances.created_at'
            ];

            $orderColumnName = $columns[$orderColumn] ?? 'finances.id';

            // Log the request for debugging
            log_message('debug', 'Finance DataTables Request - Draw: ' . $draw . ', Start: ' . $start . ', Length: ' . $length . ', Search: ' . $searchValue . ', OrderColumn: ' . $orderColumnName . ', OrderDir: ' . $orderDir);

            // Validate sort order
            $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? strtolower($orderDir) : 'desc';

            // Check if tables exist
            $tables = $this->db->listTables();
            log_message('debug', 'Available tables: ' . implode(', ', $tables));

            // Check for both possible table names
            $financeTableExists = in_array('finances', $tables) || in_array('finance', $tables);
            $patientsTableExists = in_array('patients', $tables);

            if (!$financeTableExists || !$patientsTableExists) {
                log_message('error', 'Required tables missing. Finance table: ' . ($financeTableExists ? 'exists' : 'missing') . ', Patients: ' . ($patientsTableExists ? 'exists' : 'missing'));
                return $this->response->setJSON([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Required tables do not exist'
                ]);
            }

            // Determine the correct table name
            $financeTableName = in_array('finances', $tables) ? 'finances' : 'finance';

            // Get total records count
            $totalRecords = $this->db->table($financeTableName)->countAllResults();

            // If no finances exist, return empty result
            if ($totalRecords == 0) {
                return $this->response->setJSON([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]);
            }

            // Build query
            $query = $this->db->table($financeTableName)
                ->select($financeTableName . '.*, patients.first_name, patients.last_name, patients.phone')
                ->join('patients', 'patients.id = ' . $financeTableName . '.patient_id', 'left');

            // Apply search filter
            if (!empty($searchValue)) {
                $query->groupStart()
                    ->like($financeTableName . '.id', $searchValue)
                    ->orLike('patients.first_name', $searchValue)
                    ->orLike('patients.last_name', $searchValue)
                    ->orLike('patients.phone', $searchValue)
                    ->orLike($financeTableName . '.description', $searchValue)
                    ->orLike($financeTableName . '.transaction_type', $searchValue)
                    ->orLike($financeTableName . '.payment_status', $searchValue)
                    ->groupEnd();
            }

            // Get filtered count
            $filteredRecords = $query->countAllResults(false);

            // Get data with ordering and pagination
            $finances = $query->orderBy($orderColumnName, $orderDir)
                ->limit($length, $start)
                ->get()
                ->getResultArray();

            // Format data
            $data = [];
            foreach ($finances as $finance) {
                // Log the raw finance data for debugging
                log_message('debug', 'Raw finance data: ' . json_encode($finance));

                $amount = floatval($finance['amount'] ?? 0);
                $data[] = [
                    'id' => intval($finance['id'] ?? 0),
                    'transaction_id' => 'TXN-' . str_pad($finance['id'], 6, '0', STR_PAD_LEFT),
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

            // Calculate pagination
            $totalPages = ceil($filteredRecords / $length);

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Finance DataTables error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'draw' => $draw,
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
            $format = $this->request->getGet('format') ?? 'csv';
            $startDate = $this->request->getGet('start_date');
            $endDate = $this->request->getGet('end_date');
            $status = $this->request->getGet('status');
            $ids = $this->request->getGet('ids');

            // Build query
            $query = $this->financeModel->select('finances.*, patients.first_name, patients.last_name')
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

            if ($format === 'csv') {
                $this->exportToCSV($data);
            } else {
                $this->exportToExcel($data);
            }
        } catch (\Exception $e) {
            log_message('error', 'Export error: ' . $e->getMessage());
            return redirect()->to(base_url('finance'))->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    private function exportToCSV($data)
    {
        $filename = 'financial_data_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, [
            'ID',
            'Transaction ID',
            'Patient Name',
            'Transaction Type',
            'Amount',
            'Currency',
            'Payment Method',
            'Payment Status',
            'Service Type',
            'Description',
            'Created Date'
        ]);

        // CSV data
        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['transaction_id'] ?? $row['id'],
                ($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''),
                $row['transaction_type'],
                $row['amount'],
                $row['currency'],
                $row['payment_method'],
                $row['payment_status'],
                $row['service_type'],
                $row['description'],
                $row['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    private function exportToExcel($data)
    {
        // For now, redirect to CSV export
        // In a real application, you would use a library like PhpSpreadsheet
        $this->exportToCSV($data);
    }

    public function bulkMarkAsPaid()
    {
        try {
            $input = json_decode($this->request->getBody(), true);
            $ids = $input['ids'] ?? [];

            if (empty($ids)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No transactions selected']);
            }

            $updateData = [
                'payment_status' => 'paid',
                'paid_date' => date('Y-m-d'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->financeModel->whereIn('id', $ids)->set($updateData)->update();

            return $this->response->setJSON([
                'success' => true,
                'message' => count($ids) . ' transactions marked as paid successfully'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Bulk mark as paid error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function bulkDelete()
    {
        try {
            $input = json_decode($this->request->getBody(), true);
            $ids = $input['ids'] ?? [];

            if (empty($ids)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No transactions selected']);
            }

            $this->financeModel->whereIn('id', $ids)->delete();

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
