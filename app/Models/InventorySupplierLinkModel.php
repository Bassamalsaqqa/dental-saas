<?php

namespace App\Models;

class InventorySupplierLinkModel extends TenantAwareModel
{
    protected $table = 'inventory_supplier_links';
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
        'supplier_sku',
        'pack_size_label',
        'preferred_order_qty',
        'last_unit_price',
        'currency',
        'is_preferred',
        'notes',
        'created_by',
    ];

    protected $validationRules = [
        'inventory_id' => 'required|integer',
        'supplier_id' => 'required|integer',
        'supplier_sku' => 'permit_empty|max_length[100]',
        'pack_size_label' => 'permit_empty|max_length[100]',
        'preferred_order_qty' => 'permit_empty|integer|greater_than[0]',
        'last_unit_price' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'currency' => 'permit_empty|max_length[10]',
        'is_preferred' => 'permit_empty|in_list[0,1]',
        'notes' => 'permit_empty|max_length[255]',
    ];

    public function getByInventory(int $clinicId, int $inventoryId): array
    {
        return $this->select('inventory_supplier_links.*, suppliers.name as supplier_name, suppliers.phone, suppliers.email, suppliers.whatsapp, suppliers.contact_person')
            ->join('suppliers', 'suppliers.id = inventory_supplier_links.supplier_id AND suppliers.clinic_id = inventory_supplier_links.clinic_id', 'left')
            ->where('inventory_supplier_links.clinic_id', $clinicId)
            ->where('inventory_supplier_links.inventory_id', $inventoryId)
            ->orderBy('inventory_supplier_links.is_preferred', 'DESC')
            ->orderBy('suppliers.name', 'ASC')
            ->findAll();
    }

    public function findLink(int $clinicId, int $inventoryId, int $supplierId): ?array
    {
        return $this->where('clinic_id', $clinicId)
            ->where('inventory_id', $inventoryId)
            ->where('supplier_id', $supplierId)
            ->first();
    }

    public function clearPreferredForInventory(int $clinicId, int $inventoryId): void
    {
        $this->where('clinic_id', $clinicId)
            ->where('inventory_id', $inventoryId)
            ->set(['is_preferred' => 0, 'updated_at' => date('Y-m-d H:i:s')])
            ->update();
    }

    public function getPreferredForInventory(int $clinicId, int $inventoryId): ?array
    {
        return $this->select('inventory_supplier_links.*, suppliers.name as supplier_name, suppliers.phone, suppliers.email, suppliers.whatsapp, suppliers.contact_person')
            ->join('suppliers', 'suppliers.id = inventory_supplier_links.supplier_id AND suppliers.clinic_id = inventory_supplier_links.clinic_id', 'left')
            ->where('inventory_supplier_links.clinic_id', $clinicId)
            ->where('inventory_supplier_links.inventory_id', $inventoryId)
            ->where('inventory_supplier_links.is_preferred', 1)
            ->first();
    }
}
