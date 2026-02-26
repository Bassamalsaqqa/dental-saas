<?php

namespace App\Models;

class ProviderPayoutModel extends TenantAwareModel
{
    protected $table = 'provider_payouts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'finance_id',
        'patient_id',
        'provider_type',
        'provider_user_id',
        'provider_name_snapshot',
        'split_mode',
        'split_value',
        'gross_amount',
        'provider_share_amount',
        'clinic_share_amount',
        'provider_paid_amount',
        'provider_balance_due',
        'status',
        'currency',
        'notes',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'finance_id' => 'required|integer',
        'patient_id' => 'required|integer',
        'provider_type' => 'required|in_list[user,guest]',
        'split_mode' => 'required|in_list[percent,fixed]',
        'split_value' => 'required|decimal|greater_than_equal_to[0]',
        'gross_amount' => 'required|decimal|greater_than_equal_to[0]',
        'provider_share_amount' => 'required|decimal|greater_than_equal_to[0]',
        'clinic_share_amount' => 'required|decimal|greater_than_equal_to[0]',
        'provider_paid_amount' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'provider_balance_due' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'status' => 'required|in_list[unpaid,partial,paid,void]',
    ];

    public function getByFinanceId(int $clinicId, int $financeId): ?array
    {
        return $this->where('clinic_id', $clinicId)
            ->where('finance_id', $financeId)
            ->first();
    }

    public function getStatsByClinic(int $clinicId, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDateTime = !empty($startDate) ? ($startDate . ' 00:00:00') : null;
        $endDateTime = !empty($endDate) ? ($endDate . ' 23:59:59') : null;

        // Accrual view: filter by underlying finance transaction date.
        $accrualBuilder = $this->db->table('provider_payouts pp')
            ->join('finances f', 'f.id = pp.finance_id AND f.clinic_id = pp.clinic_id', 'left')
            ->where('pp.clinic_id', $clinicId);

        if ($startDateTime !== null) {
            $accrualBuilder->where('f.created_at >=', $startDateTime);
        }
        if ($endDateTime !== null) {
            $accrualBuilder->where('f.created_at <=', $endDateTime);
        }

        $row = $accrualBuilder
            ->select('
                SUM(pp.provider_share_amount) AS total_provider_share,
                SUM(pp.provider_paid_amount) AS total_provider_paid,
                SUM(pp.provider_balance_due) AS total_provider_balance,
                SUM(pp.clinic_share_amount) AS total_clinic_share,
                SUM(pp.gross_amount) AS total_gross_amount
            ')
            ->get()
            ->getRowArray();

        // Cash view: filter by payout payment date.
        $paidCashBuilder = $this->db->table('provider_payout_payments ppp')
            ->join('provider_payouts pp', 'pp.id = ppp.provider_payout_id', 'inner')
            ->where('pp.clinic_id', $clinicId);

        if (!empty($startDate)) {
            $paidCashBuilder->where('ppp.paid_date >=', $startDate);
        }
        if (!empty($endDate)) {
            $paidCashBuilder->where('ppp.paid_date <=', $endDate);
        }

        $paidCash = $paidCashBuilder
            ->selectSum('ppp.amount', 'total_paid_cash')
            ->get()
            ->getRowArray();

        return [
            'total_provider_share' => floatval($row['total_provider_share'] ?? 0),
            'total_provider_paid' => floatval($row['total_provider_paid'] ?? 0),
            'total_provider_paid_cash' => floatval($paidCash['total_paid_cash'] ?? 0),
            'total_provider_balance' => floatval($row['total_provider_balance'] ?? 0),
            'total_clinic_share' => floatval($row['total_clinic_share'] ?? 0),
            'total_gross_amount' => floatval($row['total_gross_amount'] ?? 0),
        ];
    }

    public function getDataTableByClinic(int $clinicId, int $start, int $length, string $search = '', string $statusFilter = ''): array
    {
        $start = max(0, $start);
        $length = max(1, min($length, 200));

        $base = $this->db->table('provider_payouts pp')
            ->select('
                pp.*,
                f.transaction_id,
                p.first_name AS patient_first_name,
                p.last_name AS patient_last_name,
                u.first_name AS provider_first_name,
                u.last_name AS provider_last_name
            ')
            ->join('finances f', 'f.id = pp.finance_id', 'left')
            ->join('patients p', 'p.id = pp.patient_id', 'left')
            ->join('users u', 'u.id = pp.provider_user_id', 'left')
            ->where('pp.clinic_id', $clinicId);

        if ($statusFilter !== '' && in_array($statusFilter, ['unpaid', 'partial', 'paid', 'void'], true)) {
            $base->where('pp.status', $statusFilter);
        }

        if ($search !== '') {
            $base->groupStart()
                ->like('f.transaction_id', $search)
                ->orLike('pp.provider_name_snapshot', $search)
                ->orLike('p.first_name', $search)
                ->orLike('p.last_name', $search)
                ->orLike('u.first_name', $search)
                ->orLike('u.last_name', $search)
                ->groupEnd();
        }

        $recordsTotal = (int) $this->where('clinic_id', $clinicId)->countAllResults();
        $recordsFiltered = (int) (clone $base)->countAllResults(false);

        $rows = $base
            ->orderBy('pp.id', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows' => $rows,
        ];
    }

    public function getStatusSummaryByClinic(int $clinicId, ?string $startDate = null, ?string $endDate = null): array
    {
        $builder = $this->db->table('provider_payouts pp')
            ->join('finances f', 'f.id = pp.finance_id AND f.clinic_id = pp.clinic_id', 'left')
            ->where('pp.clinic_id', $clinicId);

        if (!empty($startDate)) {
            $builder->where('f.created_at >=', $startDate . ' 00:00:00');
        }
        if (!empty($endDate)) {
            $builder->where('f.created_at <=', $endDate . ' 23:59:59');
        }

        $rows = $builder
            ->select('pp.status, COUNT(*) AS payout_count, SUM(pp.provider_share_amount) AS provider_share_total, SUM(pp.provider_balance_due) AS balance_due_total')
            ->groupBy('pp.status')
            ->get()
            ->getResultArray();

        return array_map(static function (array $row): array {
            return [
                'status' => (string) ($row['status'] ?? 'unknown'),
                'payout_count' => (int) ($row['payout_count'] ?? 0),
                'provider_share_total' => (float) ($row['provider_share_total'] ?? 0),
                'balance_due_total' => (float) ($row['balance_due_total'] ?? 0),
            ];
        }, $rows);
    }

    public function getProviderBreakdownByClinic(int $clinicId, ?string $startDate = null, ?string $endDate = null, int $limit = 10): array
    {
        $limit = max(1, min($limit, 100));

        $builder = $this->db->table('provider_payouts pp')
            ->join('finances f', 'f.id = pp.finance_id AND f.clinic_id = pp.clinic_id', 'left')
            ->where('pp.clinic_id', $clinicId);

        if (!empty($startDate)) {
            $builder->where('f.created_at >=', $startDate . ' 00:00:00');
        }
        if (!empty($endDate)) {
            $builder->where('f.created_at <=', $endDate . ' 23:59:59');
        }

        return $builder
            ->select('COALESCE(NULLIF(pp.provider_name_snapshot, \'\'), CONCAT(\'User #\', COALESCE(pp.provider_user_id, 0))) AS provider_label, COUNT(*) AS payout_count, SUM(pp.gross_amount) AS gross_amount_total, SUM(pp.provider_share_amount) AS provider_share_total, SUM(pp.provider_paid_amount) AS provider_paid_total, SUM(pp.provider_balance_due) AS provider_balance_total', false)
            ->groupBy('pp.provider_name_snapshot, pp.provider_user_id')
            ->orderBy('provider_share_total', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getReportRowsByClinic(int $clinicId, ?string $startDate = null, ?string $endDate = null, int $limit = 250): array
    {
        $limit = max(1, min($limit, 1000));

        $builder = $this->db->table('provider_payouts pp')
            ->select('pp.id, pp.status, pp.provider_type, pp.provider_user_id, pp.provider_name_snapshot, pp.gross_amount, pp.provider_share_amount, pp.provider_paid_amount, pp.provider_balance_due, pp.created_at, f.transaction_id, p.patient_id AS patient_number, p.first_name AS patient_first_name, p.last_name AS patient_last_name')
            ->join('finances f', 'f.id = pp.finance_id AND f.clinic_id = pp.clinic_id', 'left')
            ->join('patients p', 'p.id = pp.patient_id AND p.clinic_id = pp.clinic_id', 'left')
            ->where('pp.clinic_id', $clinicId);

        if (!empty($startDate)) {
            $builder->where('f.created_at >=', $startDate . ' 00:00:00');
        }
        if (!empty($endDate)) {
            $builder->where('f.created_at <=', $endDate . ' 23:59:59');
        }

        $rows = $builder
            ->orderBy('pp.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $row['provider_label'] = !empty($row['provider_name_snapshot'])
                ? (string) $row['provider_name_snapshot']
                : ('User #' . (string) ($row['provider_user_id'] ?? '0'));
            $row['patient_name'] = trim((string) (($row['patient_first_name'] ?? '') . ' ' . ($row['patient_last_name'] ?? '')));
        }
        unset($row);

        return $rows;
    }
}
