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
        'type',
        'payment_method',
        'payment_date',
        'notes',
        'related_payment_id',
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
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public function getPaidTotalByInvoice($clinicId, $financeId)
    {
        $row = $this->select("SUM(CASE WHEN type = 'payment' THEN amount WHEN type IN ('refund','void') THEN -amount ELSE 0 END) AS total_amount", false)
            ->where('clinic_id', $clinicId)
            ->where('finance_id', $financeId)
            ->get()
            ->getRow();

        return $row ? floatval($row->total_amount ?? 0) : 0.0;
    }

    public function getRelatedAdjustmentsTotal(int $clinicId, int $paymentId): float
    {
        $row = $this->selectSum('amount', 'total_amount')
            ->where('clinic_id', $clinicId)
            ->where('related_payment_id', $paymentId)
            ->whereIn('type', ['refund', 'void'])
            ->get()
            ->getRow();

        return $row ? floatval($row->total_amount ?? 0) : 0.0;
    }
}
