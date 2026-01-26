<?php

namespace App\Models;

use CodeIgniter\Model;

class ControlPlaneInvoicePaymentModel extends Model
{
    protected $table = 'control_plane_invoice_payments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'invoice_id',
        'amount',
        'payment_method',
        'status',
        'transaction_reference',
        'notes',
        'paid_at',
        'created_by',
    ];

    public function getPaymentsForInvoice(int $invoiceId): array
    {
        return $this->where('invoice_id', $invoiceId)
                    ->orderBy('paid_at', 'DESC')
                    ->findAll();
    }
}
