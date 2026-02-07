<?php

namespace App\Models;

class FinancePaymentModel extends TenantAwareModel
{
    protected $table = 'finance_payments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'finance_id',
        'amount',
        'payment_method',
        'payment_date',
        'notes',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['setClinicId'];

    public function getPaymentsByInvoice($clinicId, $financeId)
    {
        return $this->where('clinic_id', $clinicId)
            ->where('finance_id', $financeId)
            ->orderBy('payment_date', 'DESC')
            ->findAll();
    }

    public function getPaidTotalByInvoice($clinicId, $financeId)
    {
        $row = $this->selectSum('amount', 'total_amount')
            ->where('clinic_id', $clinicId)
            ->where('finance_id', $financeId)
            ->get()
            ->getRow();

        return $row ? floatval($row->total_amount ?? 0) : 0.0;
    }
}
