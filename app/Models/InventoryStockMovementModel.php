<?php

namespace App\Models;

class InventoryStockMovementModel extends TenantAwareModel
{
    protected $table = 'inventory_stock_movements';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'clinic_id',
        'inventory_id',
        'movement_type',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        'unit_cost',
        'total_cost',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $validationRules = [
        'inventory_id' => 'required|integer',
        'movement_type' => 'required|max_length[50]',
        'quantity_change' => 'required|integer',
        'quantity_before' => 'required|integer|greater_than_equal_to[0]',
        'quantity_after' => 'required|integer|greater_than_equal_to[0]',
        'unit_cost' => 'permit_empty|decimal',
        'total_cost' => 'permit_empty|decimal',
        'reference_type' => 'permit_empty|max_length[50]',
        'reference_id' => 'permit_empty|integer',
        'notes' => 'permit_empty|max_length[255]',
    ];

    public function getRecentByInventory(int $clinicId, int $inventoryId, int $limit = 50): array
    {
        return $this->select('inventory_stock_movements.*, users.first_name as actor_first_name, users.last_name as actor_last_name')
            ->join('users', 'users.id = inventory_stock_movements.created_by', 'left')
            ->where('inventory_stock_movements.clinic_id', $clinicId)
            ->where('inventory_stock_movements.inventory_id', $inventoryId)
            ->orderBy('inventory_stock_movements.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
