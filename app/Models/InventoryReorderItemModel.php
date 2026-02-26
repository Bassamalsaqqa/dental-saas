<?php

namespace App\Models;

class InventoryReorderItemModel extends TenantAwareModel
{
    protected $table = 'inventory_reorder_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'clinic_id',
        'inventory_id',
        'supplier_id',
        'requested_qty',
        'status',
        'notes',
        'created_by',
        'ordered_at',
    ];

    protected $validationRules = [
        'inventory_id' => 'required|integer',
        'supplier_id' => 'permit_empty|integer',
        'requested_qty' => 'required|integer|greater_than[0]',
        'status' => 'required|in_list[draft,ordered,received,cancelled]',
        'notes' => 'permit_empty|max_length[255]',
    ];

    public function getOpenByClinic(int $clinicId): array
    {
        return $this->select('inventory_reorder_items.*, inventory.item_name, inventory.category, inventory.quantity as current_qty, inventory.min_quantity, inventory.unit, suppliers.name as supplier_name, suppliers.phone as supplier_phone, suppliers.email as supplier_email, suppliers.whatsapp as supplier_whatsapp, suppliers.contact_person as supplier_contact')
            ->join('inventory', 'inventory.id = inventory_reorder_items.inventory_id AND inventory.clinic_id = inventory_reorder_items.clinic_id', 'left')
            ->join('suppliers', 'suppliers.id = inventory_reorder_items.supplier_id AND suppliers.clinic_id = inventory_reorder_items.clinic_id', 'left')
            ->where('inventory_reorder_items.clinic_id', $clinicId)
            ->whereIn('inventory_reorder_items.status', ['draft', 'ordered'])
            ->orderBy('inventory_reorder_items.status', 'ASC')
            ->orderBy('suppliers.name', 'ASC')
            ->orderBy('inventory.item_name', 'ASC')
            ->findAll();
    }

    public function findExistingDraft(int $clinicId, int $inventoryId, ?int $supplierId): ?array
    {
        $builder = $this->where('clinic_id', $clinicId)
            ->where('inventory_id', $inventoryId)
            ->where('status', 'draft');
        if ($supplierId) {
            $builder->where('supplier_id', $supplierId);
        } else {
            $builder->where('supplier_id', null);
        }

        return $builder->first();
    }
}
