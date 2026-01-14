<?php

namespace App\Models;

use CodeIgniter\Model;

class FinanceModel extends Model
{
    use \App\Traits\TenantTrait;

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
        'service_details',
        'discount_amount',
        'tax_amount',
        'total_amount',
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
        'patient_id' => 'required|integer',
        'transaction_type' => 'required|in_list[payment,invoice,refund,adjustment]',
        'amount' => 'required|decimal|greater_than[0]',
        'currency' => 'in_list[USD,EUR,GBP,BDT,ILS]',
        'payment_method' => 'required|in_list[cash,card,bank_transfer,check,other]',
        'payment_status' => 'required|in_list[pending,paid,partial,overdue,cancelled]',
        'service_type' => 'required|in_list[consultation,treatment,medication,procedure,other]',
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
            $prefix = strtoupper(substr($data['data']['transaction_type'], 0, 3));
            $data['data']['transaction_id'] = $prefix . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }
        return $data;
    }

    protected function calculateTotals(array $data)
    {
        if (isset($data['data']['amount'])) {
            $amount = floatval($data['data']['amount']);
            $discount = floatval($data['data']['discount_amount'] ?? 0);
            $tax = floatval($data['data']['tax_amount'] ?? 0);

            $total = $amount - $discount + $tax;

            // Only set total_amount if the field exists in the table
            if ($this->db->fieldExists('total_amount', 'finances')) {
                $data['data']['total_amount'] = $total;
            }
        }
        return $data;
    }

    public function getFinanceByPatient($patientId)
    {
        return $this->select('finances.*, patients.first_name, patients.last_name, patients.patient_id as patient_number')
            ->join('patients', 'patients.id = finances.patient_id')
            ->where('finances.patient_id', $patientId)
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

            $totalRevenue = $builder->selectSum($sumField, 'total_amount')->where('transaction_type', 'payment')->get()->getRow()->total_amount ?? 0;
            $totalInvoices = $builder->selectSum($sumField, 'total_amount')->where('transaction_type', 'invoice')->get()->getRow()->total_amount ?? 0;
            $pendingPayments = $builder->selectSum($sumField, 'total_amount')->where('payment_status', 'pending')->get()->getRow()->total_amount ?? 0;
            $overduePayments = $builder->selectSum($sumField, 'total_amount')->where('payment_status', 'overdue')->get()->getRow()->total_amount ?? 0;

            return [
                'total_revenue' => floatval($totalRevenue),
                'total_invoices' => floatval($totalInvoices),
                'pending_payments' => floatval($pendingPayments),
                'overdue_payments' => floatval($overduePayments),
                'net_income' => floatval($totalRevenue) - floatval($totalInvoices)
            ];
        } catch (\Exception $e) {
            log_message('error', 'Finance stats error: ' . $e->getMessage());
            return [
                'total_revenue' => 0,
                'total_invoices' => 0,
                'pending_payments' => 0,
                'overdue_payments' => 0,
                'net_income' => 0
            ];
        }
    }

    public function getPaymentMethodsStats($startDate = null, $endDate = null)
    {
        $builder = $this->db->table('finances');

        if ($startDate) {
            $builder->where('created_at >=', $startDate);
        }
        if ($endDate) {
            $builder->where('created_at <=', $endDate);
        }

        $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';

        return $builder->select('payment_method, SUM(' . $sumField . ') as total_amount, COUNT(*) as transaction_count')
            ->where('transaction_type', 'payment')
            ->groupBy('payment_method')
            ->get()
            ->getResultArray();
    }

    public function getMonthlyRevenue($year = null)
    {
        try {
            $year = $year ?? date('Y');

            $sumField = $this->db->fieldExists('total_amount', 'finances') ? 'total_amount' : '(amount - discount_amount + tax_amount)';

            return $this->select('MONTH(created_at) as month, SUM(' . $sumField . ') as total_amount')
                ->where('YEAR(created_at)', $year)
                ->where('transaction_type', 'payment')
                ->groupBy('MONTH(created_at)')
                ->orderBy('month', 'ASC')
                ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Monthly revenue error: ' . $e->getMessage());
            return [];
        }
    }

    public function getOverduePayments()
    {
        return $this->select('finances.*, patients.first_name, patients.last_name, patients.patient_id as patient_number')
            ->join('patients', 'patients.id = finances.patient_id')
            ->where('finances.payment_status', 'overdue')
            ->where('finances.due_date <', date('Y-m-d'))
            ->orderBy('due_date', 'ASC')
            ->findAll();
    }

    public function getServiceTypeStats($startDate = null, $endDate = null)
    {
        $builder = $this->db->table('finances');

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
