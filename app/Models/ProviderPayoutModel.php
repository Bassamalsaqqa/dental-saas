<?php

namespace App\Models;

use App\Services\ProviderCurrencySummary;

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

    private function financeDocumentDateExpr(string $alias = 'f'): string
    {
        return "COALESCE({$alias}.transaction_date, {$alias}.paid_date, DATE({$alias}.created_at))";
    }

    public function getByFinanceId(int $clinicId, int $financeId): ?array
    {
        return $this->where('clinic_id', $clinicId)
            ->where('finance_id', $financeId)
            ->orderBy('id', 'ASC')
            ->first();
    }

    public function getRowsByFinanceId(int $clinicId, int $financeId): array
    {
        return $this->where('clinic_id', $clinicId)
            ->where('finance_id', $financeId)
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function getStatsByClinic(int $clinicId, ?string $startDate = null, ?string $endDate = null): array
    {
        $accrualBuilder = $this->db->table('provider_payouts pp')
            ->join('finances f', 'f.id = pp.finance_id AND f.clinic_id = pp.clinic_id', 'left')
            ->where('pp.clinic_id', $clinicId);

        if (!empty($startDate)) {
            $accrualBuilder->where($this->financeDocumentDateExpr('f') . ' >= ' . $this->db->escape($startDate), null, false);
        }
        if (!empty($endDate)) {
            $accrualBuilder->where($this->financeDocumentDateExpr('f') . ' <= ' . $this->db->escape($endDate), null, false);
        }

        $allocationRows = (clone $accrualBuilder)->select("pp.finance_id, pp.currency,
            MAX(pp.gross_amount) AS gross_amount, COUNT(DISTINCT pp.gross_amount) AS gross_variant_count,
            SUM(pp.provider_share_amount) AS provider_share_amount,
            SUM(pp.provider_paid_amount) AS provider_paid_amount,
            SUM(CASE WHEN pp.status='void' THEN 0 ELSE pp.provider_balance_due END) AS provider_balance_due,
            COUNT(*) AS allocation_count, 'partial' AS status", false)
            ->groupBy('pp.finance_id, pp.currency')->get()->getResultArray();

        // Cash view: filter by payout payment date.
        $paidCashBuilder = $this->db->table('provider_payout_payments ppp')
            ->join('provider_payouts pp', 'pp.id = ppp.provider_payout_id AND pp.clinic_id = ppp.clinic_id', 'inner')
            ->where('pp.clinic_id', $clinicId);

        if (!empty($startDate)) {
            $paidCashBuilder->where('ppp.paid_date >=', $startDate);
        }
        if (!empty($endDate)) {
            $paidCashBuilder->where('ppp.paid_date <=', $endDate);
        }

        $cashRows = $paidCashBuilder->select('SUM(ppp.amount) AS amount, pp.currency')->groupBy('pp.currency')->get()->getResultArray();
        return ProviderCurrencySummary::summarize($allocationRows, $cashRows);
    }

    public function getDataTableByClinic(int $clinicId, int $start, int $length, string $search = '', string $statusFilter = ''): array
    {
        $start = max(0, $start);
        $length = max(1, min($length, 200));

        $base = $this->db->table('provider_payouts pp')
            ->select('
                pp.*,
                f.transaction_id,
                f.transaction_type,
                p.first_name AS patient_first_name,
                p.last_name AS patient_last_name,
                p.gender AS patient_gender,
                u.first_name AS provider_first_name,
                u.last_name AS provider_last_name
            ')
            ->join('finances f', 'f.id = pp.finance_id AND f.clinic_id = pp.clinic_id', 'left')
            ->join('patients p', 'p.id = pp.patient_id AND p.clinic_id = pp.clinic_id', 'left')
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
            $builder->where($this->financeDocumentDateExpr('f') . ' >= ' . $this->db->escape($startDate), null, false);
        }
        if (!empty($endDate)) {
            $builder->where($this->financeDocumentDateExpr('f') . ' <= ' . $this->db->escape($endDate), null, false);
        }

        $rows = $builder
            ->select('pp.currency, pp.status, COUNT(*) AS payout_count, SUM(pp.provider_share_amount) AS provider_share_total, SUM(CASE WHEN pp.status = \'void\' THEN 0 ELSE pp.provider_balance_due END) AS balance_due_total')
            ->groupBy('pp.status, pp.currency')
            ->get()
            ->getResultArray();

        return array_map(static function (array $row): array {
            return [
                'status' => (string) ($row['status'] ?? 'unknown'),
                'currency' => ProviderCurrencySummary::currency($row['currency'] ?? null),
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
            ->join('users u', 'u.id = pp.provider_user_id', 'left')
            ->where('pp.clinic_id', $clinicId);

        if (!empty($startDate)) {
            $builder->where($this->financeDocumentDateExpr('f') . ' >= ' . $this->db->escape($startDate), null, false);
        }
        if (!empty($endDate)) {
            $builder->where($this->financeDocumentDateExpr('f') . ' <= ' . $this->db->escape($endDate), null, false);
        }

        return $builder
            ->select("
                COALESCE(
                    NULLIF(TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))), ''),
                    NULLIF(pp.provider_name_snapshot, ''),
                    CONCAT('User #', COALESCE(pp.provider_user_id, 0))
                ) AS provider_label,
                pp.currency,
                COUNT(*) AS payout_count,
                SUM(pp.gross_amount) AS gross_amount_total,
                SUM(pp.provider_share_amount) AS provider_share_total,
                SUM(pp.provider_paid_amount) AS provider_paid_total,
                SUM(CASE WHEN pp.status = 'void' THEN 0 ELSE pp.provider_balance_due END) AS provider_balance_total
            ", false)
            ->groupBy('pp.provider_name_snapshot, pp.provider_user_id, pp.currency')
            ->orderBy('provider_share_total', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getReportRowsByClinic(int $clinicId, ?string $startDate = null, ?string $endDate = null, int $limit = 250): array
    {
        $limit = max(1, min($limit, 1000));

        $builder = $this->db->table('provider_payouts pp')
            ->select('pp.currency, pp.id, pp.status, pp.provider_type, pp.provider_user_id, pp.provider_name_snapshot, pp.gross_amount, pp.provider_share_amount, pp.provider_paid_amount, pp.provider_balance_due, pp.created_at, f.transaction_id, p.patient_id AS patient_number, p.first_name AS patient_first_name, p.last_name AS patient_last_name, u.first_name AS provider_first_name, u.last_name AS provider_last_name')
            ->join('finances f', 'f.id = pp.finance_id AND f.clinic_id = pp.clinic_id', 'left')
            ->join('patients p', 'p.id = pp.patient_id AND p.clinic_id = pp.clinic_id', 'left')
            ->join('users u', 'u.id = pp.provider_user_id', 'left')
            ->where('pp.clinic_id', $clinicId);

        if (!empty($startDate)) {
            $builder->where($this->financeDocumentDateExpr('f') . ' >= ' . $this->db->escape($startDate), null, false);
        }
        if (!empty($endDate)) {
            $builder->where($this->financeDocumentDateExpr('f') . ' <= ' . $this->db->escape($endDate), null, false);
        }

        $rows = $builder
            ->orderBy('pp.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $providerName = trim((string) (($row['provider_first_name'] ?? '') . ' ' . ($row['provider_last_name'] ?? '')));
            $row['provider_label'] = $providerName !== ''
                ? $providerName
                : (!empty($row['provider_name_snapshot'])
                    ? (string) $row['provider_name_snapshot']
                    : ('User #' . (string) ($row['provider_user_id'] ?? '0')));
            $row['patient_name'] = trim((string) (($row['patient_first_name'] ?? '') . ' ' . ($row['patient_last_name'] ?? '')));
        }
        unset($row);

        return $rows;
    }

    public function getSummaryByProviderUserId(int $clinicId, int $userId): array
    {
        $rows = $this->db->table('provider_payouts')->select("finance_id, currency, SUM(gross_amount) AS gross_amount,
            SUM(provider_share_amount) AS provider_share_amount, SUM(provider_paid_amount) AS provider_paid_amount,
            SUM(CASE WHEN status='void' THEN 0 ELSE provider_balance_due END) AS provider_balance_due,
            COUNT(*) AS allocation_count, 'partial' AS status", false)->where('clinic_id', $clinicId)
            ->where('provider_type', 'user')->where('provider_user_id', $userId)->groupBy('finance_id, currency')->get()->getResultArray();
        return ProviderCurrencySummary::summarize($rows);
    }

    public function getRowsByProviderUserId(int $clinicId, int $userId, int $limit = 25): array
    {
        $limit = max(1, min($limit, 100));

        $paymentsSubquery = $this->db->table('provider_payout_payments')
            ->select('provider_payout_id, COUNT(*) AS payment_count, MAX(paid_date) AS last_paid_date')
            ->where('clinic_id', $clinicId)
            ->groupBy('provider_payout_id')
            ->getCompiledSelect();

        $rows = $this->db->table('provider_payouts pp')
            ->select("
                pp.id,
                pp.currency,
                pp.finance_id,
                pp.status,
                pp.gross_amount,
                pp.provider_share_amount,
                pp.provider_paid_amount,
                pp.provider_balance_due,
                pp.created_at,
                f.transaction_id,
                {$this->financeDocumentDateExpr('f')} AS business_date,
                p.patient_id AS patient_number,
                p.first_name AS patient_first_name,
                p.last_name AS patient_last_name,
                p.gender AS patient_gender,
                f.transaction_type,
                (
                    SELECT COUNT(*)
                    FROM clinical_charge_lines ccl
                    WHERE ccl.clinic_id = pp.clinic_id
                      AND ccl.finance_id = pp.finance_id
                      AND ccl.provider_user_id = pp.provider_user_id
                      AND ccl.status != 'voided'
                ) AS clinical_line_count,
                (
                    SELECT COUNT(DISTINCT ccl.treatment_id)
                    FROM clinical_charge_lines ccl
                    WHERE ccl.clinic_id = pp.clinic_id
                      AND ccl.finance_id = pp.finance_id
                      AND ccl.provider_user_id = pp.provider_user_id
                      AND ccl.treatment_id IS NOT NULL
                      AND ccl.status != 'voided'
                ) AS treatment_count,
                (
                    SELECT COUNT(DISTINCT ccl.examination_id)
                    FROM clinical_charge_lines ccl
                    WHERE ccl.clinic_id = pp.clinic_id
                      AND ccl.finance_id = pp.finance_id
                      AND ccl.provider_user_id = pp.provider_user_id
                      AND ccl.examination_id IS NOT NULL
                      AND ccl.status != 'voided'
                ) AS examination_count,
                (
                    SELECT GROUP_CONCAT(DISTINCT ccl.procedure_name ORDER BY ccl.id SEPARATOR ', ')
                    FROM clinical_charge_lines ccl
                    WHERE ccl.clinic_id = pp.clinic_id
                      AND ccl.finance_id = pp.finance_id
                      AND ccl.provider_user_id = pp.provider_user_id
                      AND ccl.status != 'voided'
                ) AS clinical_procedures,
                pay.payment_count,
                pay.last_paid_date
            ", false)
            ->join('finances f', 'f.id = pp.finance_id AND f.clinic_id = pp.clinic_id', 'left')
            ->join('patients p', 'p.id = pp.patient_id AND p.clinic_id = pp.clinic_id', 'left')
            ->join("({$paymentsSubquery}) pay", 'pay.provider_payout_id = pp.id', 'left', false)
            ->where('pp.clinic_id', $clinicId)
            ->where('pp.provider_type', 'user')
            ->where('pp.provider_user_id', $userId)
            ->orderBy('business_date', 'DESC')
            ->orderBy('pp.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $row['patient_name'] = trim((string) (($row['patient_first_name'] ?? '') . ' ' . ($row['patient_last_name'] ?? '')));
            $row['payment_count'] = (int) ($row['payment_count'] ?? 0);
            $row['clinical_line_count'] = (int) ($row['clinical_line_count'] ?? 0);
            $row['treatment_count'] = (int) ($row['treatment_count'] ?? 0);
            $row['examination_count'] = (int) ($row['examination_count'] ?? 0);
        }
        unset($row);

        return $rows;
    }

    public function getDirectoryProvidersByClinic(int $clinicId, string $search = ''): array
    {
        $providerKey = "CASE WHEN pp.provider_type='user' AND pp.provider_user_id IS NOT NULL THEN CONCAT('user:', pp.provider_user_id) ELSE CONCAT('guest:', LOWER(TRIM(COALESCE(NULLIF(pp.provider_name_snapshot,''),'Guest Provider')))) END";
        $rows = $this->db->table('provider_payouts pp')->select("pp.finance_id, pp.currency, pp.provider_type,
            MAX(pp.provider_user_id) AS provider_user_id, MAX(pp.provider_name_snapshot) AS provider_name_snapshot,
            SUM(pp.gross_amount) AS gross_amount, SUM(pp.provider_share_amount) AS provider_share_amount,
            SUM(pp.provider_paid_amount) AS provider_paid_amount,
            SUM(CASE WHEN pp.status='void' THEN 0 ELSE pp.provider_balance_due END) AS provider_balance_due,
            COUNT(*) AS allocation_count, 'partial' AS status, MAX(pp.created_at) AS created_at,
            MAX(u.first_name) AS first_name, MAX(u.last_name) AS last_name, MAX(u.phone) AS phone, MAX(u.email) AS email", false)
            ->join('users u', 'u.id=pp.provider_user_id', 'left')->where('pp.clinic_id', $clinicId)
            ->groupBy($providerKey, false)->groupBy('pp.provider_type, pp.finance_id, pp.currency')->get()->getResultArray();
        $groups = [];
        foreach ($rows as $row) {
            $key = $row['provider_type'] === 'user' && $row['provider_user_id']
                ? 'user:' . $row['provider_user_id'] : 'guest:' . strtolower(trim($row['provider_name_snapshot'] ?: 'Guest Provider'));
            $groups[$key][] = $row;
        }
        $result = [];
        foreach ($groups as $key => $allocations) {
            $identity = $allocations[0];
            if ($search !== '' && stripos(implode(' ', [$identity['provider_name_snapshot'], $identity['first_name'], $identity['last_name'], $identity['phone'], $identity['email']]), $search) === false) { continue; }
            $summary = ProviderCurrencySummary::summarize($allocations);
            $result[] = $summary + ['directory_key' => $key, 'latest_payout_date' => max(array_column($allocations, 'created_at'))] + $identity;
        }
        return $result;
    }

    public function getDirectoryProviderCount(int $clinicId): int
    {
        $providerKey = "CASE
            WHEN provider_type = 'user' AND provider_user_id IS NOT NULL THEN CONCAT('user:', provider_user_id)
            ELSE CONCAT('guest:', LOWER(TRIM(COALESCE(NULLIF(provider_name_snapshot, ''), 'Guest Provider'))))
        END";

        $row = $this->db->table('provider_payouts')
            ->select("COUNT(DISTINCT {$providerKey}) AS cnt", false)
            ->where('clinic_id', $clinicId)
            ->get()
            ->getRowArray();

        return (int) ($row['cnt'] ?? 0);
    }

    public function getDirectoryProviderStats(int $clinicId): array
    {
        return $this->getStatsByClinic($clinicId) + ['total' => $this->getDirectoryProviderCount($clinicId)];
    }
}
