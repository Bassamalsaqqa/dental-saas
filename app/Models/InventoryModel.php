<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends TenantAwareModel
{
    protected $table = 'inventory';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false; // Temporarily disabled until database is updated
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'item_name',
        'category',
        'description',
        'quantity',
        'min_quantity',
        'unit_price',
        'unit',
        'supplier',
        'purchase_date',
        'expiry_date',
        'location',
        'notes',
        'status',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'item_name' => 'required|min_length[2]|max_length[100]',
        'category' => 'required|in_list[medication,equipment,supplies,consumables,other]',
        'description' => 'permit_empty|max_length[500]',
        'quantity' => 'required|integer|greater_than_equal_to[0]',
        'min_quantity' => 'required|integer|greater_than_equal_to[0]',
        'unit_price' => 'required|decimal|greater_than_equal_to[0]',
        'unit' => 'permit_empty|in_list[pieces,boxes,bottles,tubes,packs,sets]',
        'supplier' => 'permit_empty|max_length[100]',
        'purchase_date' => 'permit_empty|valid_date',
        'expiry_date' => 'permit_empty|valid_date',
        'location' => 'permit_empty|max_length[100]',
        'notes' => 'permit_empty|max_length[500]',
        'status' => 'required|in_list[active,inactive,discontinued]',
    ];

    protected $validationMessages = [
        'item_name' => [
            'required' => 'Item name is required',
            'min_length' => 'Item name must be at least 2 characters long',
            'max_length' => 'Item name cannot exceed 100 characters'
        ],
        'category' => [
            'required' => 'Category is required',
            'in_list' => 'Invalid category'
        ],
        'quantity' => [
            'required' => 'Quantity is required',
            'integer' => 'Quantity must be a valid number',
            'greater_than_equal_to' => 'Quantity cannot be negative'
        ],
        'min_quantity' => [
            'required' => 'Minimum quantity is required',
            'integer' => 'Minimum quantity must be a valid number',
            'greater_than_equal_to' => 'Minimum quantity cannot be negative'
        ],
        'unit_price' => [
            'required' => 'Unit price is required',
            'decimal' => 'Unit price must be a valid decimal number',
            'greater_than_equal_to' => 'Unit price cannot be negative'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Invalid status'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['setClinicId'];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function countInventoryByClinic($clinicId, $searchValue = null)
    {
        $builder = $this->where('clinic_id', $clinicId);

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('item_name', $searchValue)
                ->orLike('category', $searchValue)
                ->orLike('supplier', $searchValue)
                ->orLike('description', $searchValue)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function getInventoryByClinic($clinicId, $limit = 10, $offset = 0, $searchValue = null, $orderColumn = 'id', $orderDir = 'asc')
    {
        $builder = $this->where('clinic_id', $clinicId);

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('item_name', $searchValue)
                ->orLike('category', $searchValue)
                ->orLike('supplier', $searchValue)
                ->orLike('description', $searchValue)
                ->groupEnd();
        }

        return $builder->orderBy($orderColumn, $orderDir)
            ->limit($limit, $offset)
            ->findAll();
    }

    public function searchInventoryByClinic($clinicId, $searchTerm = null, $limit = 20, $category = null)
    {
        $builder = $this->where('clinic_id', $clinicId)
                        ->where('quantity >', 0);

        if ($category) {
            $builder->where('category', $category);
        }

        if (!empty($searchTerm)) {
            $builder->groupStart()
                ->like('item_name', $searchTerm)
                ->orLike('description', $searchTerm)
                ->orLike('category', $searchTerm)
                ->orLike('supplier', $searchTerm)
            ->groupEnd();
        }

        return $builder->orderBy('item_name', 'ASC')
            ->limit($limit)
            ->findAll();
    }

    public function getInventoryWithStats()
    {
        return $this->select('inventory.*, 
                            CASE 
                                WHEN quantity = 0 THEN "out_of_stock"
                                WHEN quantity <= min_quantity THEN "low_stock"
                                WHEN expiry_date < CURDATE() THEN "expired"
                                ELSE "in_stock"
                            END as stock_status')
                    ->orderBy('item_name', 'ASC')
                    ->findAll();
    }

    public function getLowStockItems()
    {
        return $this->where('quantity <=', 'min_quantity')
                    ->where('status', 'active')
                    ->orderBy('quantity', 'ASC')
                    ->findAll();
    }

    public function getOutOfStockItems()
    {
        return $this->where('quantity', 0)
                    ->where('status', 'active')
                    ->orderBy('item_name', 'ASC')
                    ->findAll();
    }

    public function getExpiredItems()
    {
        return $this->where('expiry_date <', date('Y-m-d'))
                    ->where('status', 'active')
                    ->orderBy('expiry_date', 'ASC')
                    ->findAll();
    }

    public function getExpiringItems($days = 30)
    {
        $expiryDate = date('Y-m-d', strtotime("+{$days} days"));
        return $this->where('expiry_date <=', $expiryDate)
                    ->where('expiry_date >=', date('Y-m-d'))
                    ->where('status', 'active')
                    ->orderBy('expiry_date', 'ASC')
                    ->findAll();
    }

    public function getInventoryStats()
    {
        return [
            'total_items' => $this->countAllResults(),
            'active_items' => $this->where('status', 'active')->countAllResults(),
            'low_stock_items' => $this->where('quantity <=', 'min_quantity')->where('status', 'active')->countAllResults(),
            'out_of_stock_items' => $this->where('quantity', 0)->where('status', 'active')->countAllResults(),
            'expired_items' => $this->where('expiry_date <', date('Y-m-d'))->where('status', 'active')->countAllResults(),
            'total_value' => $this->selectSum('quantity * unit_price')->where('status', 'active')->first()['quantity * unit_price'] ?? 0,
        ];
    }

    public function getCategoryStats()
    {
        return $this->select('category, COUNT(*) as count, SUM(quantity * unit_price) as total_value')
                    ->where('status', 'active')
                    ->groupBy('category')
                    ->orderBy('count', 'DESC')
                    ->findAll();
    }

    public function getSupplierStats()
    {
        return $this->select('supplier, COUNT(*) as count, SUM(quantity * unit_price) as total_value')
                    ->where('status', 'active')
                    ->where('supplier IS NOT NULL')
                    ->groupBy('supplier')
                    ->orderBy('count', 'DESC')
                    ->findAll();
    }

    public function searchInventory($searchTerm)
    {
        return $this->groupStart()
                    ->like('item_name', $searchTerm)
                    ->orLike('description', $searchTerm)
                    ->orLike('category', $searchTerm)
                    ->orLike('supplier', $searchTerm)
                    ->orLike('location', $searchTerm)
                    ->groupEnd()
                    ->orderBy('item_name', 'ASC')
                    ->findAll();
    }

    public function getInventoryByCategory($category)
    {
        return $this->where('category', $category)
                    ->where('status', 'active')
                    ->orderBy('item_name', 'ASC')
                    ->findAll();
    }

    public function getInventoryByLocation($location)
    {
        return $this->where('location', $location)
                    ->where('status', 'active')
                    ->orderBy('item_name', 'ASC')
                    ->findAll();
    }

    public function adjustQuantity($id, $adjustment, $type = 'add')
    {
        $item = $this->find($id);
        if (!$item) {
            return false;
        }

        $newQuantity = $type === 'add' 
            ? $item['quantity'] + $adjustment 
            : $item['quantity'] - $adjustment;

        if ($newQuantity < 0) {
            return false;
        }

        return $this->update($id, ['quantity' => $newQuantity]);
    }

    public function getMonthlyInventoryValue($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        // This would need to be implemented based on your inventory tracking system
        // For now, return current month's data
        return [
            'month' => date('M Y'),
            'value' => $this->selectSum('quantity * unit_price')->where('status', 'active')->first()['quantity * unit_price'] ?? 0
        ];
    }

    public function getTopSellingItems($limit = 10)
    {
        // This would need to be implemented based on your sales/usage tracking
        // For now, return items with lowest quantity (assuming they're being used)
        return $this->where('status', 'active')
                    ->orderBy('quantity', 'ASC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getInventoryAlerts()
    {
        $alerts = [];

        // Low stock alerts
        $lowStock = $this->where('quantity <=', 'min_quantity')
                         ->where('status', 'active')
                         ->findAll();
        
        foreach ($lowStock as $item) {
            $alerts[] = [
                'type' => 'low_stock',
                'item' => $item['item_name'],
                'current_quantity' => $item['quantity'],
                'min_quantity' => $item['min_quantity'],
                'message' => "Low stock alert: {$item['item_name']} has {$item['quantity']} units remaining (minimum: {$item['min_quantity']})"
            ];
        }

        // Expired items alerts
        $expired = $this->where('expiry_date <', date('Y-m-d'))
                        ->where('status', 'active')
                        ->findAll();
        
        foreach ($expired as $item) {
            $alerts[] = [
                'type' => 'expired',
                'item' => $item['item_name'],
                'expiry_date' => $item['expiry_date'],
                'message' => "Expired item: {$item['item_name']} expired on {$item['expiry_date']}"
            ];
        }

        // Expiring soon alerts
        $expiringSoon = $this->where('expiry_date <=', date('Y-m-d', strtotime('+30 days')))
                             ->where('expiry_date >=', date('Y-m-d'))
                             ->where('status', 'active')
                             ->findAll();
        
        foreach ($expiringSoon as $item) {
            $alerts[] = [
                'type' => 'expiring_soon',
                'item' => $item['item_name'],
                'expiry_date' => $item['expiry_date'],
                'message' => "Expiring soon: {$item['item_name']} expires on {$item['expiry_date']}"
            ];
        }

        return $alerts;
    }
}
