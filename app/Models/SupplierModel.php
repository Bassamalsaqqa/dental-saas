<?php

namespace App\Models;

class SupplierModel extends TenantAwareModel
{
    protected $table = 'suppliers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'clinic_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'whatsapp',
        'address',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[150]',
        'contact_person' => 'permit_empty|max_length[150]',
        'phone' => 'permit_empty|max_length[50]',
        'email' => 'permit_empty|valid_email|max_length[150]',
        'whatsapp' => 'permit_empty|max_length[50]',
        'address' => 'permit_empty|max_length[255]',
        'notes' => 'permit_empty',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];

    public function getActiveOptionsByClinic(int $clinicId): array
    {
        return $this->where('clinic_id', $clinicId)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function findByClinicAndName(int $clinicId, string $name): ?array
    {
        return $this->where('clinic_id', $clinicId)
            ->where('name', trim($name))
            ->first();
    }

    public function getDirectoryDataByClinic(int $clinicId, string $search = ''): array
    {
        $financeSummary = $this->db->table('finances')
            ->select('
                supplier_id,
                COUNT(*) AS bill_count,
                SUM(COALESCE(amount, 0)) AS bill_total,
                SUM(GREATEST(COALESCE(amount, 0) - COALESCE(amount_paid, 0), 0)) AS open_balance,
                MAX(COALESCE(transaction_date, paid_date, DATE(created_at))) AS last_activity
            ', false)
            ->where('clinic_id', $clinicId)
            ->where('transaction_type', 'expense')
            ->where('supplier_id IS NOT NULL', null, false)
            ->groupBy('supplier_id')
            ->getCompiledSelect();

        $inventorySummary = $this->db->table('inventory')
            ->select('supplier_id, COUNT(*) AS item_count', false)
            ->where('clinic_id', $clinicId)
            ->where('supplier_id IS NOT NULL', null, false)
            ->groupBy('supplier_id')
            ->getCompiledSelect();

        $linkSummary = $this->db->table('inventory_supplier_links')
            ->select('supplier_id, COUNT(DISTINCT inventory_id) AS linked_item_count', false)
            ->where('clinic_id', $clinicId)
            ->groupBy('supplier_id')
            ->getCompiledSelect();

        $builder = $this->db->table('suppliers s')
            ->select('
                s.*,
                COALESCE(fs.bill_count, 0) AS bill_count,
                COALESCE(fs.bill_total, 0) AS bill_total,
                COALESCE(fs.open_balance, 0) AS open_balance,
                fs.last_activity,
                COALESCE(inv.item_count, 0) + COALESCE(links.linked_item_count, 0) AS item_count
            ', false)
            ->join("({$financeSummary}) fs", 'fs.supplier_id = s.id', 'left', false)
            ->join("({$inventorySummary}) inv", 'inv.supplier_id = s.id', 'left', false)
            ->join("({$linkSummary}) links", 'links.supplier_id = s.id', 'left', false)
            ->where('s.clinic_id', $clinicId)
            ->orderBy('s.name', 'ASC');

        if ($search !== '') {
            $builder->groupStart()
                ->like('s.name', $search)
                ->orLike('s.contact_person', $search)
                ->orLike('s.phone', $search)
                ->orLike('s.whatsapp', $search)
                ->orLike('s.email', $search)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function getDirectoryStatsByClinic(int $clinicId): array
    {
        $total = $this->where('clinic_id', $clinicId)->countAllResults();
        $active = $this->where('clinic_id', $clinicId)->where('is_active', 1)->countAllResults();

        return ['total' => $total, 'active' => $active];
    }
}
