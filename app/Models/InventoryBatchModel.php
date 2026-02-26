<?php

namespace App\Models;

class InventoryBatchModel extends TenantAwareModel
{
    protected $table = 'inventory_batches';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'clinic_id',
        'inventory_id',
        'lot_number',
        'expiry_date',
        'received_qty',
        'remaining_qty',
        'unit_cost',
        'currency',
        'source_movement_id',
        'notes',
        'created_by',
    ];

    protected $validationRules = [
        'inventory_id' => 'required|integer',
        'lot_number' => 'permit_empty|max_length[100]',
        'expiry_date' => 'permit_empty|valid_date',
        'received_qty' => 'required|integer|greater_than[0]',
        'remaining_qty' => 'required|integer|greater_than_equal_to[0]',
        'unit_cost' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'currency' => 'permit_empty|max_length[10]',
        'source_movement_id' => 'permit_empty|integer',
        'notes' => 'permit_empty|max_length[255]',
    ];

    public function getByInventory(int $clinicId, int $inventoryId, int $limit = 25): array
    {
        return $this->where('clinic_id', $clinicId)
            ->where('inventory_id', $inventoryId)
            ->orderBy('expiry_date IS NULL', 'ASC', false)
            ->orderBy('expiry_date', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}

