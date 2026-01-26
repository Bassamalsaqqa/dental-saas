<?php

namespace App\Models;

use CodeIgniter\Model;

class ControlPlaneInvoiceModel extends Model
{
    protected $table = 'control_plane_invoices';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'clinic_id',
        'invoice_number',
        'status',
        'issue_date',
        'due_date',
        'paid_at',
        'currency',
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'total_amount',
        'items_json',
        'notes',
        'private_notes',
        'created_by',
    ];

    public function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $prefix = "SAAS-{$year}-";
        $sequence = $this->like('invoice_number', $prefix . '%', 'after')->countAllResults() + 1;
        return sprintf('%s%04d', $prefix, $sequence);
    }

    public function getInvoicesWithClinic(array $filters = []): array
    {
        $builder = $this->select('control_plane_invoices.*, clinics.name as clinic_name, clinics.domain as clinic_domain')
                        ->join('clinics', 'clinics.id = control_plane_invoices.clinic_id', 'left')
                        ->orderBy('issue_date', 'DESC');

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['clinic_id'])) {
            $builder->where('clinic_id', $filters['clinic_id']);
        }

        if (!empty($filters['from'])) {
            $builder->where('issue_date >=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $builder->where('issue_date <=', $filters['to']);
        }

        return $builder->findAll();
    }

    public function calculateTotals(array $items, float $taxPercentage = 0): array
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $quantity = isset($item['quantity']) ? max(0, (float) $item['quantity']) : 1;
            $unitPrice = isset($item['unit_price']) ? max(0, (float) $item['unit_price']) : 0;
            $subtotal += $quantity * $unitPrice;
        }
        $taxAmount = round($subtotal * max(0, $taxPercentage) / 100, 2);
        $total = round($subtotal + $taxAmount, 2);
        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => $taxAmount,
            'total_amount' => $total,
        ];
    }
}
