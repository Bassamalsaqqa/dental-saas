<?php

namespace App\Models;

class ProviderPayoutPaymentModel extends TenantAwareModel
{
    protected $table = 'provider_payout_payments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'provider_payout_id',
        'amount',
        'payment_method',
        'liquidity_account',
        'paid_date',
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
        'provider_payout_id' => 'required|integer',
        'amount' => 'required|decimal|greater_than[0]',
        'payment_method' => 'required|in_list[cash,card,bank_transfer,check,other]',
        'liquidity_account' => 'permit_empty|in_list[safe_cash,bank,undeposited_checks]',
        'paid_date' => 'required|valid_date',
    ];

    public function getPaidTotal(int $clinicId, int $payoutId): float
    {
        $row = $this->selectSum('amount', 'total_amount')
            ->where('clinic_id', $clinicId)
            ->where('provider_payout_id', $payoutId)
            ->get()
            ->getRow();

        return $row ? floatval($row->total_amount ?? 0) : 0.0;
    }

    public function getRowsByProviderUserId(int $clinicId, int $userId, int $limit = 50): array
    {
        $limit = max(1, min($limit, 200));

        $rows = $this->db->table('provider_payout_payments ppp')
            ->select("
                ppp.id,
                ppp.provider_payout_id,
                ppp.amount,
                ppp.payment_method,
                ppp.liquidity_account,
                ppp.paid_date,
                ppp.notes,
                pp.finance_id,
                pp.currency,
                pp.provider_type,
                f.transaction_id,
                f.transaction_type,
                p.first_name AS patient_first_name,
                p.last_name AS patient_last_name,
                p.patient_id AS patient_number,
                p.gender AS patient_gender
            ")
            ->join('provider_payouts pp', 'pp.id = ppp.provider_payout_id AND pp.clinic_id = ppp.clinic_id', 'inner')
            ->join('finances f', 'f.id = pp.finance_id AND f.clinic_id = pp.clinic_id', 'left')
            ->join('patients p', 'p.id = pp.patient_id AND p.clinic_id = pp.clinic_id', 'left')
            ->where('ppp.clinic_id', $clinicId)
            ->where('pp.provider_type', 'user')
            ->where('pp.provider_user_id', $userId)
            ->orderBy('ppp.paid_date', 'DESC')
            ->orderBy('ppp.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $row['patient_name'] = trim((string) (($row['patient_first_name'] ?? '') . ' ' . ($row['patient_last_name'] ?? '')));
        }
        unset($row);

        return $rows;
    }
}
