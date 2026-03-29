<?php

namespace App\Models;

class FinanceLiquidityTransferModel extends TenantAwareModel
{
    protected $table = 'finance_liquidity_transfers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'from_account',
        'to_account',
        'amount',
        'transfer_date',
        'transfer_type',
        'notes',
        'related_finance_id',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'from_account' => 'required|in_list[safe_cash,bank,undeposited_checks]',
        'to_account' => 'required|in_list[safe_cash,bank,undeposited_checks]',
        'amount' => 'required|decimal|greater_than[0]',
        'transfer_date' => 'required|valid_date',
        'transfer_type' => 'required|in_list[cash_deposit_to_bank,bank_withdrawal_to_safe,check_deposit_to_bank,internal_transfer]',
    ];
}
