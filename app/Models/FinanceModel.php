<?php

namespace App\Models;

use CodeIgniter\Model;

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
        'payment_status',
        'description',
        'service_type',
        'expense_category',
        'counterparty_name',
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
        'payment_status' => 'required|in_list[pending,paid,partial,overdue,cancelled]',
        'service_type' => 'permit_empty|in_list[consultation,treatment,medication,procedure,other]',
        'expense_category' => 'permit_empty|in_list[rent,utilities,salaries,laboratory,supplies,equipment,maintenance,software,marketing,taxes,fees,inventory_purchase,miscellaneous]',
        'counterparty_name' => 'permit_empty|max_length[150]',
        'paid_date' => 'permit_empty|valid_date'
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
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $beforeInsert = ['generateTransactionId', 'calculateTotals', 'setClinicId'];
    protected $beforeUpdate = ['calculateTotals'];

    protected function generateTransactionId(array $data)
    {
        if (!isset($data['data']['transaction_id'])) {
            $type = (string) ($data['data']['transaction_type'] ?? 'txn');
            $prefix = strtoupper(substr($type, 0, 3));
            $data['data']['transaction_id'] = $prefix . date('YmdHis') . str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT);
        }
        return $data;
    }

    protected function calculateTotals(array $data)
    {
        if (isset($data['data']['amount'])) {
            $amount = floatval($data['data']['amount']);
            $discount = floatval($data['data']['discount_amount'] ?? 0);
            $taxPercent = floatval($data['data']['tax_percentage'] ?? 0);
            $taxableBase = max(0, $amount - $discount);
            $tax = $taxableBase * ($taxPercent / 100);

            $data['data']['tax_amount'] = $tax;
            $total = $amount - $discount + $tax;

            // Only set total_amount if the field exists in the table
            if ($this->db->fieldExists('total_amount', 'finances')) {
                $data['data']['total_amount'] = $total;
            }
        }
        return $data;
    }

    /**
     * Get finances by clinic for DataTables
     */
    public function getFinancesByClinic($clinicId, $limit = 10, $offset = 0, $search = '', $orderColumn = 'finances.id', $orderDir = 'desc')
    {
        $builder = $this->select('finances.*, patients.first_name, patients.last_name, patients.phone')
            ->join('patients', 'patients.id = finances.patient_id', 'left')
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
            ->join('patients', 'patients.id = finances.patient_id', 'left')
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
            $startDateTime = $startDate ? ($startDate . ' 00:00:00') : null;
            $endDateTime = $endDate ? ($endDate . ' 23:59:59') : null;

            $builder = $this->db->table('finances')->where('clinic_id', $clinicId);

            if ($startDateTime) {
                $builder->where('created_at >=', $startDateTime);
            }
            if ($endDateTime) {
                $builder->where('created_at <=', $endDateTime);
            }

            $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';
            $balanceField = $this->db->fieldExists('balance_due', 'finances') ? 'balance_due' : '(' . $sumField . ' - COALESCE(amount_paid, 0))';

            $standalonePayments = (clone $builder)->selectSum($sumField, 'total_amount')->where('transaction_type', 'payment')->get()->getRow()->total_amount ?? 0;
            $totalInvoices = (clone $builder)->selectSum($sumField, 'total_amount')->where('transaction_type', 'invoice')->get()->getRow()->total_amount ?? 0;
            $refunds = (clone $builder)->selectSum($sumField, 'total_amount')->where('transaction_type', 'refund')->get()->getRow()->total_amount ?? 0;
            $totalTransactions = (clone $builder)->countAllResults();

            $paymentBuilder = $this->db->table('finance_payments')->where('clinic_id', $clinicId);
            if ($startDate) {
                $paymentBuilder->where('payment_date >=', $startDate);
            }
            if ($endDate) {
                $paymentBuilder->where('payment_date <=', $endDate);
            }
            $invoicePayments = $paymentBuilder
                ->select("SUM(CASE WHEN type = 'payment' THEN amount WHEN type IN ('refund','void') THEN -amount ELSE 0 END) AS total_amount", false)
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

            $monthStart = date('Y-m-01 00:00:00');
            $monthEnd = date('Y-m-t 23:59:59');
            $monthlyRevenue = $this->db->table('finances')
                ->selectSum($sumField, 'total_amount')
                ->where('clinic_id', $clinicId)
                ->where('transaction_type', 'payment')
                ->where('created_at >=', $monthStart)
                ->where('created_at <=', $monthEnd)
                ->get()
                ->getRow()
                ->total_amount ?? 0;
            $monthlyInvoicePayments = $this->db->table('finance_payments')
                ->select("SUM(CASE WHEN type = 'payment' THEN amount WHEN type IN ('refund','void') THEN -amount ELSE 0 END) AS total_amount", false)
                ->where('clinic_id', $clinicId)
                ->where('payment_date >=', date('Y-m-01'))
                ->where('payment_date <=', date('Y-m-t'))
                ->get()
                ->getRow()
                ->total_amount ?? 0;

            $summaryBuilder = $this->db->table('finances')
                ->select('transaction_type, COUNT(*) as count, SUM(' . $sumField . ') as total_amount')
                ->where('clinic_id', $clinicId);
            if ($startDateTime) {
                $summaryBuilder->where('created_at >=', $startDateTime);
            }
            if ($endDateTime) {
                $summaryBuilder->where('created_at <=', $endDateTime);
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

            $expenses = (clone $builder)->selectSum($sumField, 'total_amount')->where('transaction_type', 'expense')->get()->getRow()->total_amount ?? 0;
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
                'monthly_revenue' => floatval($monthlyRevenue) + floatval($monthlyInvoicePayments),
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
            $year = $year ?? date('Y');
            $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';

            $standalone = $this->select('MONTH(created_at) as month, SUM(' . $sumField . ') as total_amount')
                ->where('clinic_id', $clinicId)
                ->where('YEAR(created_at)', $year)
                ->where('transaction_type', 'payment')
                ->groupBy('MONTH(created_at)')
                ->orderBy('month', 'ASC')
                ->findAll();

            $invoicePayments = $this->db->table('finance_payments')
                ->select("MONTH(payment_date) as month, SUM(CASE WHEN type = 'payment' THEN amount WHEN type IN ('refund','void') THEN -amount ELSE 0 END) as total_amount", false)
                ->where('clinic_id', $clinicId)
                ->where('YEAR(payment_date)', $year)
                ->groupBy('MONTH(payment_date)')
                ->orderBy('month', 'ASC')
                ->get()
                ->getResultArray();

            $refunds = $this->db->table('finances')
                ->select('MONTH(created_at) as month, SUM(' . $sumField . ') as total_amount')
                ->where('clinic_id', $clinicId)
                ->where('YEAR(created_at)', $year)
                ->where('transaction_type', 'refund')
                ->groupBy('MONTH(created_at)')
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
            $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';
            $startDateTime = $startDate . ' 00:00:00';
            $endDateTime = $endDate . ' 23:59:59';

            $standalone = $this->db->table('finances')
                ->select("DATE_FORMAT(created_at, '%Y-%m') AS month_key, SUM({$sumField}) AS total_amount", false)
                ->where('clinic_id', $clinicId)
                ->where('transaction_type', 'payment')
                ->where('created_at >=', $startDateTime)
                ->where('created_at <=', $endDateTime)
                ->groupBy("DATE_FORMAT(created_at, '%Y-%m')", false)
                ->get()
                ->getResultArray();

            $invoicePayments = $this->db->table('finance_payments')
                ->select("DATE_FORMAT(payment_date, '%Y-%m') AS month_key, SUM(CASE WHEN type = 'payment' THEN amount WHEN type IN ('refund','void') THEN -amount ELSE 0 END) AS total_amount", false)
                ->where('clinic_id', $clinicId)
                ->where('payment_date >=', $startDate)
                ->where('payment_date <=', $endDate)
                ->groupBy("DATE_FORMAT(payment_date, '%Y-%m')", false)
                ->get()
                ->getResultArray();

            $refunds = $this->db->table('finances')
                ->select("DATE_FORMAT(created_at, '%Y-%m') AS month_key, SUM({$sumField}) AS total_amount", false)
                ->where('clinic_id', $clinicId)
                ->where('transaction_type', 'refund')
                ->where('created_at >=', $startDateTime)
                ->where('created_at <=', $endDateTime)
                ->groupBy("DATE_FORMAT(created_at, '%Y-%m')", false)
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
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getFinanceByExamination($examinationId)
    {
        return $this->where('examination_id', $examinationId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getFinanceStats($startDate = null, $endDate = null)
    {
        try {
            $builder = $this->db->table('finances');

            if ($startDate) {
                $builder->where('created_at >=', $startDate);
            }
            if ($endDate) {
                $builder->where('created_at <=', $endDate);
            }

            $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';

            $balanceField = $this->db->fieldExists('balance_due', 'finances') ? 'balance_due' : '(' . $sumField . ' - COALESCE(amount_paid, 0))';

            $standalonePayments = $builder->selectSum($sumField, 'total_amount')->where('transaction_type', 'payment')->get()->getRow()->total_amount ?? 0;
            $totalInvoices = $builder->selectSum($sumField, 'total_amount')->where('transaction_type', 'invoice')->get()->getRow()->total_amount ?? 0;
            $refunds = $builder->selectSum($sumField, 'total_amount')->where('transaction_type', 'refund')->get()->getRow()->total_amount ?? 0;
            $totalTransactions = (clone $builder)->countAllResults();

            $paymentBuilder = $this->db->table('finance_payments');
            if ($startDate) {
                $paymentBuilder->where('payment_date >=', $startDate);
            }
            if ($endDate) {
                $paymentBuilder->where('payment_date <=', $endDate);
            }
            $invoicePayments = $paymentBuilder
                ->select("SUM(CASE WHEN type = 'payment' THEN amount WHEN type IN ('refund','void') THEN -amount ELSE 0 END) AS total_amount", false)
                ->get()
                ->getRow()
                ->total_amount ?? 0;

            $pendingPayments = $builder->selectSum($balanceField, 'total_amount')
                ->where('transaction_type', 'invoice')
                ->whereIn('payment_status', ['pending', 'partial', 'overdue'])
                ->get()
                ->getRow()
                ->total_amount ?? 0;
            $overduePayments = $builder->selectSum($balanceField, 'total_amount')
                ->where('transaction_type', 'invoice')
                ->where('due_date <', date('Y-m-d'))
                ->where($balanceField . ' >', 0, false)
                ->get()
                ->getRow()
                ->total_amount ?? 0;

            $summaryBuilder = $this->db->table('finances')
                ->select('transaction_type, COUNT(*) as count, SUM(' . $sumField . ') as total_amount');
            if ($startDate) {
                $summaryBuilder->where('created_at >=', $startDate);
            }
            if ($endDate) {
                $summaryBuilder->where('created_at <=', $endDate);
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

            $expenses = (clone $builder)->selectSum($sumField, 'total_amount')->where('transaction_type', 'expense')->get()->getRow()->total_amount ?? 0;
            $totalRevenue = floatval($standalonePayments) + floatval($invoicePayments) - floatval($refunds);
            $netIncome = $totalRevenue - floatval($expenses);
            $averageTransaction = $totalTransactions > 0 ? ($netIncome / $totalTransactions) : 0;

            return [
                'total_revenue' => $totalRevenue,
                'total_invoices' => floatval($totalInvoices),
                'pending_payments' => floatval($pendingPayments),
                'pending_amount' => floatval($pendingPayments),
                'overdue_payments' => floatval($overduePayments),
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
        $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';

        $standaloneBuilder = $this->db->table('finances')
            ->select("payment_method, SUM(CASE WHEN transaction_type = 'payment' THEN {$sumField} WHEN transaction_type = 'refund' THEN -{$sumField} ELSE 0 END) as total_amount, COUNT(*) as transaction_count", false)
            ->whereIn('transaction_type', ['payment', 'refund']);
        if ($clinicId) {
            $standaloneBuilder->where('clinic_id', $clinicId);
        }
        if ($startDate) {
            $standaloneBuilder->where('created_at >=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $standaloneBuilder->where('created_at <=', $endDate . ' 23:59:59');
        }
        $standalone = $standaloneBuilder->groupBy('payment_method')->get()->getResultArray();

        $invoiceBuilder = $this->db->table('finance_payments')
            ->select("payment_method, SUM(CASE WHEN type = 'payment' THEN amount WHEN type IN ('refund','void') THEN -amount ELSE 0 END) as total_amount, COUNT(*) as transaction_count", false);
        if ($clinicId) {
            $invoiceBuilder->where('clinic_id', $clinicId);
        }
        if ($startDate) {
            $invoiceBuilder->where('payment_date >=', $startDate);
        }
        if ($endDate) {
            $invoiceBuilder->where('payment_date <=', $endDate);
        }
        $invoicePayments = $invoiceBuilder->groupBy('payment_method')->get()->getResultArray();

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
            ->select('finances.id, finances.patient_id, finances.transaction_id, finances.total_amount, finances.balance_due, finances.payment_status, finances.due_date, finances.created_at, patients.first_name, patients.last_name, patients.patient_id as patient_number')
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
            $dueDate = !empty($row['due_date']) ? (string) $row['due_date'] : substr((string) ($row['created_at'] ?? $asOfDate), 0, 10);
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
            $year = $year ?? date('Y');

            $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';

            $standaloneBuilder = $this->select('MONTH(created_at) as month, SUM(' . $sumField . ') as total_amount')
                ->where('YEAR(created_at)', $year)
                ->where('transaction_type', 'payment');
            if ($clinicId) {
                $standaloneBuilder->where('clinic_id', $clinicId);
            }
            $standalone = $standaloneBuilder->groupBy('MONTH(created_at)')
                ->orderBy('month', 'ASC')
                ->findAll();

            $invoiceBuilder = $this->db->table('finance_payments')
                ->select('MONTH(payment_date) as month, SUM(amount) as total_amount')
                ->where('YEAR(payment_date)', $year);
            if ($clinicId) {
                $invoiceBuilder->where('clinic_id', $clinicId);
            }
            $invoicePayments = $invoiceBuilder->groupBy('MONTH(payment_date)')
                ->orderBy('month', 'ASC')
                ->get()
                ->getResultArray();

            $refundBuilder = $this->db->table('finances')
                ->select('MONTH(created_at) as month, SUM(' . $sumField . ') as total_amount')
                ->where('YEAR(created_at)', $year)
                ->where('transaction_type', 'refund');
            if ($clinicId) {
                $refundBuilder->where('clinic_id', $clinicId);
            }
            $refunds = $refundBuilder->groupBy('MONTH(created_at)')
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
            ->join('patients', 'patients.id = finances.patient_id')
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
            $builder->where('created_at >=', $startDate);
        }
        if ($endDate) {
            $builder->where('created_at <=', $endDate);
        }

        $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';

        return $builder->select('service_type, SUM(' . $sumField . ') as total_amount, COUNT(*) as transaction_count')
            ->where('transaction_type', 'payment')
            ->groupBy('service_type')
            ->get()
            ->getResultArray();
    }
}
