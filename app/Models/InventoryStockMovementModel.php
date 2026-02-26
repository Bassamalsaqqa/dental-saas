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
        'batch_id',
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
        'batch_id' => 'permit_empty|integer',
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
        return $this->select('inventory_stock_movements.*, users.first_name as actor_first_name, users.last_name as actor_last_name, inventory_batches.lot_number as batch_lot_number, inventory_batches.expiry_date as batch_expiry_date')
            ->join('users', 'users.id = inventory_stock_movements.created_by', 'left')
            ->join('inventory_batches', 'inventory_batches.id = inventory_stock_movements.batch_id AND inventory_batches.clinic_id = inventory_stock_movements.clinic_id', 'left')
            ->where('inventory_stock_movements.clinic_id', $clinicId)
            ->where('inventory_stock_movements.inventory_id', $inventoryId)
            ->orderBy('inventory_stock_movements.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getLedgerByClinic(int $clinicId, array $filters = []): array
    {
        $limit = max(1, min(200, (int) ($filters['limit'] ?? 50)));
        $builder = $this->select('inventory_stock_movements.*, inventory.item_name, inventory.category, inventory.unit, users.first_name as actor_first_name, users.last_name as actor_last_name, inventory_batches.lot_number as batch_lot_number, inventory_batches.expiry_date as batch_expiry_date')
            ->join('inventory', 'inventory.id = inventory_stock_movements.inventory_id AND inventory.clinic_id = inventory_stock_movements.clinic_id', 'left')
            ->join('users', 'users.id = inventory_stock_movements.created_by', 'left')
            ->join('inventory_batches', 'inventory_batches.id = inventory_stock_movements.batch_id AND inventory_batches.clinic_id = inventory_stock_movements.clinic_id', 'left')
            ->where('inventory_stock_movements.clinic_id', $clinicId);

        if (!empty($filters['movement_type'])) {
            $builder->where('inventory_stock_movements.movement_type', (string) $filters['movement_type']);
        }
        if (!empty($filters['inventory_id'])) {
            $builder->where('inventory_stock_movements.inventory_id', (int) $filters['inventory_id']);
        }
        if (!empty($filters['date_from'])) {
            $builder->where('DATE(inventory_stock_movements.created_at) >=', (string) $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder->where('DATE(inventory_stock_movements.created_at) <=', (string) $filters['date_to']);
        }
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $builder->groupStart()
                ->like('inventory.item_name', $q)
                ->orLike('inventory.category', $q)
                ->orLike('inventory_stock_movements.notes', $q)
                ->orLike('inventory_stock_movements.reference_type', $q)
                ->orLike('inventory_batches.lot_number', $q)
                ->groupEnd();
        }

        return $builder->orderBy('inventory_stock_movements.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
