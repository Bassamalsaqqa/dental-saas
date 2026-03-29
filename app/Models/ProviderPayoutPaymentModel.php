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
}
