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
}

