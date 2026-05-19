<?php

namespace App\Controllers;

use App\Models\InventoryModel;
use App\Models\InventoryItemTemplateModel;
use App\Models\InventoryBatchModel;
use App\Models\InventoryReorderItemModel;
use App\Models\InventorySupplierLinkModel;
use App\Models\InventoryStockMovementModel;
use App\Models\SupplierModel;
use App\Services\ActivityLogger;
use App\Services\InventoryMovementService;
use App\Services\InventoryUsageService;

class Inventory extends BaseController
{
    protected $inventoryModel;
    protected $activityLogger;
    protected $storageService;
    protected $db;
    protected $supplierModel;
    protected $supplierLinkModel;
    protected $reorderItemModel;
    protected $batchModel;

    public function __construct()
    {
        $this->inventoryModel = new InventoryModel();
        $this->activityLogger = new ActivityLogger();
        $this->storageService = new \App\Services\StorageService();
        $this->db = \Config\Database::connect();
        $this->supplierModel = new SupplierModel();
        $this->supplierLinkModel = new InventorySupplierLinkModel();
        $this->reorderItemModel = new InventoryReorderItemModel();
        $this->batchModel = new InventoryBatchModel();
    }

    public function index()
    {
        try {
            // Check if user is logged in
            if (!$this->isLoggedIn()) {
                return redirect()->to('/auth/login');
            }

            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return redirect()->to('/clinic/select');
            }

            $data = [
                'title' => 'Inventory Management',
                'categories' => $this->getCategories(),
                'suppliers' => $this->getSuppliers(),
                'supplier_directory' => $this->supplierModel->getActiveOptionsByClinic((int) $clinicId),
                'currencies' => ['ILS', 'USD', 'EUR', 'GBP', 'JOD'],
                'loadSelect2' => true,
            ];

            // Check if inventory table exists first
            $tables = $this->db->listTables();
            if (!in_array('inventory', $tables)) {
                $data['inventory'] = [];
                $data['available_categories'] = [];
                $data['total_items'] = 0;
                $data['low_stock_items'] = 0;
                $data['out_of_stock_items'] = 0;
                $data['open_reorder_count'] = 0;
                $data['error'] = 'Inventory table not found';
            } else {
                // Get all inventory items (scoped)
                $inventory = $this->inventoryModel->where('clinic_id', $clinicId)->findAll();
                
                // Get unique categories from existing items (scoped)
                $availableCategories = $this->inventoryModel->select('category')
                    ->distinct()
                    ->where('category IS NOT NULL')
                    ->where('clinic_id', $clinicId)
                    ->findAll();
                
                $data['inventory'] = $inventory;
                $data['available_categories'] = $availableCategories;
                $data['total_items'] = $this->inventoryModel->where('clinic_id', $clinicId)->countAllResults();
                $data['in_stock_items'] = $this->inventoryModel->where('clinic_id', $clinicId)->where('status', 'active')->where('quantity >', 0)->countAllResults();
                $data['low_stock_items'] = $this->inventoryModel->where('clinic_id', $clinicId)->where('quantity <= min_quantity', null, false)->countAllResults();
                $data['out_of_stock_items'] = $this->inventoryModel->where('clinic_id', $clinicId)->where('quantity', 0)->countAllResults();
                $data['open_reorder_count'] = $this->reorderItemModel->where('clinic_id', $clinicId)->whereIn('status', ['draft', 'ordered'])->countAllResults();
            }

            // Explicitly add user data to ensure it's available
            $userData = $this->getUserDataForView();
            $data = array_merge($data, $userData);

            return $this->view('inventory/index', $data);
        } catch (\Exception $e) {
            // Log the error
            log_message('error', 'Inventory index error: ' . $e->getMessage());
            
            // Return a simplified version with user data
            $data = [
                'title' => 'Inventory Management',
                'inventory' => [],
                'available_categories' => [],
                'total_items' => 0,
                'in_stock_items' => 0,
                'low_stock_items' => 0,
                'out_of_stock_items' => 0,
                'open_reorder_count' => 0,
                'categories' => $this->getCategories(),
                'suppliers' => $this->getSuppliers(),
                'supplier_directory' => [],
                'currencies' => ['ILS', 'USD', 'EUR', 'GBP', 'JOD'],
                'loadSelect2' => true,
                'error' => 'Unable to load inventory data. Please check the database connection.'
            ];

            // Ensure user data is still available in error state
            $userData = $this->getUserDataForView();
            $data = array_merge($data, $userData);

            return $this->view('inventory/index', $data);
        }
    }

    public function templates()
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $category = trim((string) $this->request->getGet('category'));
        $q = trim((string) $this->request->getGet('q'));
        $templateModel = new InventoryItemTemplateModel();

        $templates = $templateModel->getCatalog($category !== '' ? $category : null, $q !== '' ? $q : null);

        return $this->view('inventory/templates', [
            'title' => 'Inventory Starter Templates',
            'templates' => $templates,
            'categories' => $this->getCategories(),
            'selected_category' => $category,
            'search_query' => $q,
        ]);
    }

    public function importTemplates()
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return redirect()->back()->with('error', 'Clinic context is required.');
        }

        $templateIds = $this->request->getPost('template_ids');
        if (!is_array($templateIds) || empty($templateIds)) {
            return redirect()->back()->with('error', 'Select at least one template to import.');
        }

        $ids = array_values(array_unique(array_filter(array_map(static function ($v) {
            return is_numeric($v) ? (int) $v : 0;
        }, $templateIds))));

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No valid templates selected.');
        }

        $templateModel = new InventoryItemTemplateModel();
        $templates = $templateModel->whereIn('id', $ids)->where('is_active', 1)->findAll();
        if (empty($templates)) {
            return redirect()->back()->with('error', 'Selected templates were not found.');
        }

        $currentUserId = (int) (session()->get('user_id') ?? 0);
        $imported = 0;
        $skipped = 0;

        foreach ($templates as $tpl) {
            $exists = $this->inventoryModel
                ->where('clinic_id', $clinicId)
                ->where('item_name', $tpl['name'])
                ->where('category', $tpl['category'])
                ->first();
            if ($exists) {
                $skipped++;
                continue;
            }

            $payload = [
                'clinic_id' => $clinicId,
                'item_name' => $tpl['name'],
                'category' => $tpl['category'],
                'description' => $tpl['description'] ?? null,
                'quantity' => 0,
                'min_quantity' => (int) ($tpl['default_min_quantity'] ?? 0),
                'unit_price' => (float) ($tpl['default_unit_price'] ?? 0),
                'unit' => $tpl['unit'] ?: 'pieces',
                'supplier' => null,
                'supplier_id' => null,
                'purchase_date' => null,
                'expiry_date' => null,
                'location' => null,
                'notes' => 'Imported from starter template catalog.',
                'status' => 'active',
                'created_by' => $currentUserId ?: null,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->inventoryModel->insert($payload)) {
                $inventoryId = (int) $this->inventoryModel->getInsertID();
                $imported++;
                $this->activityLogger->logInventoryActivity(
                    'create',
                    $inventoryId,
                    "Inventory item '{$payload['item_name']}' imported from starter template catalog."
                );
            }
        }

        return redirect()->to(base_url('inventory'))->with(
            'success',
            "Template import complete. Imported: {$imported}" . ($skipped > 0 ? ", skipped duplicates: {$skipped}" : '')
        );
    }

    public function create()
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        $data = [
            'title' => 'Add New Item',
            'categories' => $this->getCategories(),
            'suppliers' => $this->getSuppliers(),
            'supplier_directory' => $clinicId ? $this->supplierModel->getActiveOptionsByClinic($clinicId) : [],
            'currencies' => ['ILS', 'USD', 'EUR', 'GBP', 'JOD'],
            'loadSelect2' => true,
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['inventory']),
        ];

        return $this->view('inventory/create', $data);
    }

    public function store()
    {
        $rules = [
            'item_name' => 'required|min_length[2]|max_length[100]',
            'category' => 'required|in_list[medication,equipment,supplies,consumables,other]',
            'description' => 'permit_empty|max_length[500]',
            'quantity' => 'required|integer|greater_than_equal_to[0]',
            'min_quantity' => 'required|integer|greater_than_equal_to[0]',
            'unit_price' => 'required|decimal|greater_than_equal_to[0]',
            'unit' => 'permit_empty|in_list[pieces,boxes,bottles,tubes,packs,sets]',
            'supplier' => 'permit_empty|max_length[100]',
            'supplier_id' => 'permit_empty|integer',
            'purchase_date' => 'permit_empty|valid_date',
            'expiry_date' => 'permit_empty|valid_date',
            'location' => 'permit_empty|max_length[100]',
            'notes' => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            if ($this->request->getPost('source_modal') === 'inventory_quick_create') {
                $returnTo = $this->workflowReturnTo(['inventory']) ?? 'inventory';

                return redirect()->to(base_url($returnTo))
                    ->withInput()
                    ->with('errors', $this->validator->getErrors())
                    ->with('error', 'Please review the highlighted fields.');
            }

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $currentUser = $this->getCurrentUser();
        $currentUserId = $currentUser ? (int) $currentUser->id : (session()->get('user_id') ? (int) session()->get('user_id') : null);
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        [$supplierId, $supplierName] = $this->resolveSupplierSelection($clinicId);

        $inventoryData = [
            'item_name' => $this->request->getPost('item_name'),
            'category' => $this->request->getPost('category'),
            'description' => $this->request->getPost('description'),
            'quantity' => $this->request->getPost('quantity'),
            'min_quantity' => $this->request->getPost('min_quantity'),
            'unit_price' => $this->request->getPost('unit_price'),
            'unit' => $this->request->getPost('unit') ?: 'pieces',
            'supplier' => $supplierName,
            'supplier_id' => $supplierId,
            'purchase_date' => $this->request->getPost('purchase_date') ?: null,
            'expiry_date' => $this->request->getPost('expiry_date') ?: null,
            'location' => $this->request->getPost('location'),
            'notes' => $this->request->getPost('notes'),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $currentUserId,
        ];

        if ($this->inventoryModel->insert($inventoryData)) {
            $inventoryId = $this->inventoryModel->getInsertID();
            $this->upsertSupplierLinkFromRequest($clinicId, (int) $inventoryId, $supplierId, $currentUserId);
            $initialQty = (int) ($inventoryData['quantity'] ?? 0);
            if ($initialQty > 0) {
                $this->recordStockMovementSafe(
                    $clinicId,
                    (int) $inventoryId,
                    'initial_stock',
                    $initialQty,
                    0,
                    $initialQty,
                    (float) ($inventoryData['unit_price'] ?? 0),
                    'inventory_item',
                    (int) $inventoryId,
                    'Initial quantity on item creation',
                    $currentUserId
                );
            }
            
            // Log the inventory creation activity
            $this->activityLogger->logInventoryActivity(
                'create',
                $inventoryId,
                "New inventory item '{$inventoryData['item_name']}' added - Category: {$inventoryData['category']}, Quantity: {$inventoryData['quantity']}, Price: " . settings()->getCurrencySymbol() . number_format($inventoryData['unit_price'], 2)
            );
            
            $returnTo = $this->workflowReturnTo(['inventory']) ?? 'inventory';

            return $this->redirectAfterWorkflow($returnTo, ['inventory'])->with('success', 'Item added successfully!');
        } else {
            $errors = $this->inventoryModel->errors();
            log_message('error', 'Inventory creation failed: ' . json_encode($errors));
            log_message('error', 'Inventory data that failed: ' . json_encode($inventoryData));
            if ($this->request->getPost('source_modal') === 'inventory_quick_create') {
                $returnTo = $this->workflowReturnTo(['inventory']) ?? 'inventory';

                return redirect()->to(base_url($returnTo))
                    ->withInput()
                    ->with('error', 'Failed to add item: ' . implode(', ', $errors));
            }

            return redirect()->back()->withInput()->with('error', 'Failed to add item: ' . implode(', ', $errors));
        }
    }

    public function show($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $item = $this->inventoryModel
            ->select('inventory.*, users.first_name as creator_first_name, users.last_name as creator_last_name')
            ->join('users', 'users.id = inventory.created_by', 'left')
            ->where('inventory.clinic_id', $clinicId)
            ->where('inventory.id', $id)
            ->first();
        
        if (!$item) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Item not found');
        }

        $data = [
            'title' => 'Item Details',
            'item' => $item,
            'movements' => (new InventoryStockMovementModel())->getRecentByInventory((int) $clinicId, (int) $id, 100),
            'supplier_links' => $this->supplierLinkModel->getByInventory((int) $clinicId, (int) $id),
            'supplier_directory' => $this->supplierModel->getActiveOptionsByClinic((int) $clinicId),
            'reorder_items' => $this->reorderItemModel->where('clinic_id', (int) $clinicId)->where('inventory_id', (int) $id)->orderBy('created_at', 'DESC')->findAll(),
            'batches' => $this->batchModel->getByInventory((int) $clinicId, (int) $id, 50),
            'currencies' => ['ILS', 'USD', 'EUR', 'GBP', 'JOD'],
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['inventory']),
        ];

        return $this->view('inventory/show', $data);
    }

    public function edit($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to edit inventory.');
        }

        $item = $this->inventoryModel->where('clinic_id', $clinicId)->find($id);
        
        if (!$item) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Item not found');
        }

        $data = [
            'title' => 'Edit Item',
            'item' => $item,
            'categories' => $this->getCategories(),
            'suppliers' => $this->getSuppliers(),
            'supplier_directory' => $this->supplierModel->getActiveOptionsByClinic((int) $clinicId),
            'supplier_links' => $this->supplierLinkModel->getByInventory((int) $clinicId, (int) $id),
            'preferred_supplier_link' => $this->supplierLinkModel->getPreferredForInventory((int) $clinicId, (int) $id),
            'currencies' => ['ILS', 'USD', 'EUR', 'GBP', 'JOD'],
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['inventory']),
        ];

        return $this->view('inventory/edit', $data);
    }

    public function update($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $item = $this->inventoryModel->where('clinic_id', $clinicId)->find($id);
        
        if (!$item) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Item not found');
        }

        $rules = [
            'item_name' => 'required|min_length[2]|max_length[100]',
            'category' => 'required|in_list[medication,equipment,supplies,consumables,other]',
            'description' => 'permit_empty|max_length[500]',
            'quantity' => 'required|integer|greater_than_equal_to[0]',
            'min_quantity' => 'required|integer|greater_than_equal_to[0]',
            'unit_price' => 'required|decimal|greater_than_equal_to[0]',
            'unit' => 'permit_empty|in_list[pieces,boxes,bottles,tubes,packs,sets]',
            'supplier' => 'permit_empty|max_length[100]',
            'supplier_id' => 'permit_empty|integer',
            'purchase_date' => 'permit_empty|valid_date',
            'expiry_date' => 'permit_empty|valid_date',
            'location' => 'permit_empty|max_length[100]',
            'notes' => 'permit_empty|max_length[1000]',
            'status' => 'required|in_list[active,inactive,discontinued]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        [$supplierId, $supplierName] = $this->resolveSupplierSelection((int) $clinicId);

        $inventoryData = [
            'item_name' => $this->request->getPost('item_name'),
            'category' => $this->request->getPost('category'),
            'description' => $this->request->getPost('description'),
            'quantity' => $this->request->getPost('quantity'),
            'min_quantity' => $this->request->getPost('min_quantity'),
            'unit_price' => $this->request->getPost('unit_price'),
            'unit' => $this->request->getPost('unit') ?: 'pieces',
            'supplier' => $supplierName,
            'supplier_id' => $supplierId,
            'purchase_date' => $this->request->getPost('purchase_date') ?: null,
            'expiry_date' => $this->request->getPost('expiry_date') ?: null,
            'location' => $this->request->getPost('location'),
            'notes' => $this->request->getPost('notes'),
            'status' => $this->request->getPost('status'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->inventoryModel->update($id, $inventoryData)) {
            $this->upsertSupplierLinkFromRequest((int) $clinicId, (int) $id, $supplierId, (int) (session()->get('user_id') ?? 0));
            // Log the inventory update activity
            $this->activityLogger->logInventoryActivity(
                'update',
                $id,
                "Inventory item '{$inventoryData['item_name']}' updated - Category: {$inventoryData['category']}, Quantity: {$inventoryData['quantity']}, Status: {$inventoryData['status']}"
            );
            
            return $this->redirectAfterWorkflow('inventory/' . (int) $id, ['inventory'])->with('success', 'Item updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update item. Please try again.');
        }
    }

    public function delete($id)
    {
        try {
            $acceptHeader = (string) $this->request->getHeaderLine('Accept');
            $expectsJson = $this->request->isAJAX() || stripos($acceptHeader, 'application/json') !== false;
            
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                if ($expectsJson) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
                }

                return redirect()->to(base_url('inventory'))->with('error', 'Clinic context is required.');
            }

            $item = $this->inventoryModel->where('clinic_id', $clinicId)->find($id);
            
            if (!$item) {
                if ($expectsJson) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Item not found']);
                }

                return redirect()->to(base_url('inventory'))->with('error', 'Item not found.');
            }

            if ($this->inventoryModel->delete($id)) {
                // Log the inventory deletion activity
                $this->activityLogger->logInventoryActivity(
                    'delete',
                    $id,
                    "Inventory item '{$item['item_name']}' deleted - Category: {$item['category']}, Quantity was: {$item['quantity']}"
                );
                
                log_message('info', "Inventory item {$id} deleted successfully");

                if ($expectsJson) {
                    return $this->response->setJSON(['success' => true, 'message' => 'Item deleted successfully']);
                }

                return redirect()->to(base_url('inventory'))->with('success', 'Item deleted successfully.');
            } else {
                log_message('error', "Failed to delete inventory item {$id}");
                if ($expectsJson) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete item']);
                }

                return redirect()->to(base_url('inventory'))->with('error', 'Failed to delete item.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Inventory delete error: ' . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'An error occurred while deleting the item']);
            }

            return redirect()->to(base_url('inventory'))->with('error', 'An error occurred while deleting the item.');
        }
    }

    public function adjust($id)
    {
        $this->response->setContentType('application/json');
        
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $item = $this->inventoryModel->where('clinic_id', $clinicId)->find($id);
        
        if (!$item) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item not found']);
        }

        // Get data from JSON input
        $input = json_decode($this->request->getBody(), true);
        $adjustment = $input['adjustment'] ?? null;
        $reason = $input['reason'] ?? 'Manual adjustment';
        $type = $input['type'] ?? 'add'; // 'add' or 'subtract'

        if (!is_numeric($adjustment) || $adjustment <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid adjustment amount']);
        }

        $newQuantity = $type === 'add' 
            ? $item['quantity'] + $adjustment 
            : $item['quantity'] - $adjustment;

        if ($newQuantity < 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Insufficient quantity for adjustment']);
        }

        $updateData = [
            'quantity' => $newQuantity,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->inventoryModel->update($id, $updateData)) {
            // Log the adjustment
            $this->logAdjustment($id, $adjustment, $type, $reason);
            $quantityChange = $type === 'add' ? (int) $adjustment : (int) -$adjustment;
            $this->recordStockMovementSafe(
                (int) $clinicId,
                (int) $id,
                $type === 'add' ? 'manual_adjust_in' : 'manual_adjust_out',
                $quantityChange,
                (int) $item['quantity'],
                (int) $newQuantity,
                isset($item['unit_price']) ? (float) $item['unit_price'] : null,
                'inventory_adjustment',
                (int) $id,
                $reason,
                (int) ($this->getCurrentUser()->id ?? 0)
            );
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Inventory adjusted successfully',
                'new_quantity' => $newQuantity
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to adjust inventory']);
        }
    }

    public function lowStock()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $data = [
            'title' => 'Low Stock Items',
            'items' => $this->inventoryModel->where('clinic_id', $clinicId)->where('quantity <= min_quantity', null, false)->findAll(),
            'open_reorder_count' => $this->reorderItemModel->where('clinic_id', (int) $clinicId)->whereIn('status', ['draft', 'ordered'])->countAllResults(),
        ];

        return $this->view('inventory/low_stock', $data);
    }

    public function expired()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $data = [
            'title' => 'Expired Items',
            'items' => $this->inventoryModel->where('clinic_id', $clinicId)->where('expiry_date <', date('Y-m-d'))->findAll(),
        ];

        return $this->view('inventory/expired', $data);
    }

    public function getInventoryStats()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $totalValueData = $this->inventoryModel->select('SUM(quantity * unit_price) as total_value')
            ->where('clinic_id', $clinicId)
            ->where('status', 'active')
            ->first();
        $totalValue = $totalValueData['total_value'] ?? 0;

        $stats = [
            'total_items' => $this->inventoryModel->where('clinic_id', $clinicId)->countAllResults(),
            'active_items' => $this->inventoryModel->where('clinic_id', $clinicId)->where('status', 'active')->countAllResults(),
            'low_stock_items' => $this->inventoryModel->where('clinic_id', $clinicId)->where('quantity <= min_quantity', null, false)->where('status', 'active')->countAllResults(),
            'out_of_stock_items' => $this->inventoryModel->where('clinic_id', $clinicId)->where('quantity', 0)->where('status', 'active')->countAllResults(),
            'expired_items' => $this->inventoryModel->where('clinic_id', $clinicId)->where('expiry_date <', date('Y-m-d'))->where('status', 'active')->countAllResults(),
            'total_value' => $totalValue,
        ];

        return $this->response->setJSON($stats);
    }

    private function getCategories()
    {
        return [
            'medication' => 'Medication',
            'equipment' => 'Equipment',
            'supplies' => 'Supplies',
            'consumables' => 'Consumables',
            'other' => 'Other',
        ];
    }

    private function getSuppliers()
    {
        return [
            'dental_supply_co' => 'Dental Supply Co.',
            'medical_equipment_ltd' => 'Medical Equipment Ltd.',
            'pharmaceutical_corp' => 'Pharmaceutical Corp.',
            'local_supplier' => 'Local Supplier',
            'other' => 'Other',
        ];
    }

    public function showSupplier($id)
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $supplier = $this->supplierModel->where('clinic_id', $clinicId)->find((int) $id);
        if (!$supplier) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Supplier not found');
        }

        $financeModel = new \App\Models\FinanceModel();
        $paymentModel = new \App\Models\FinancePaymentModel();

        $expenses = $financeModel
            ->where('clinic_id', $clinicId)
            ->where('transaction_type', 'expense')
            ->where('supplier_id', (int) $id)
            ->orderBy('COALESCE(transaction_date, paid_date, DATE(created_at))', 'DESC', false)
            ->orderBy('id', 'DESC')
            ->findAll();

        $totalBills = 0.0;
        $totalPaid = 0.0;
        $openPayables = 0.0;
        $transactionCount = count($expenses);
        $lastPaymentDate = null;
        $paymentRows = $this->db->table('finance_payments fp')
            ->select("
                fp.id,
                fp.finance_id,
                fp.amount,
                fp.payment_method,
                fp.liquidity_account,
                fp.payment_date,
                fp.notes,
                f.transaction_id,
                f.transaction_date,
                f.expense_category
            ")
            ->join('finances f', 'f.id = fp.finance_id AND f.clinic_id = fp.clinic_id', 'inner')
            ->where('fp.clinic_id', $clinicId)
            ->where('f.transaction_type', 'expense')
            ->where('f.supplier_id', (int) $id)
            ->where('fp.type', 'payment')
            ->orderBy('fp.payment_date', 'DESC')
            ->orderBy('fp.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($expenses as &$expense) {
            $settled = $paymentModel->getNetSettledAmount($clinicId, (int) $expense['id']);
            $expense['settled_total'] = $settled;
            $expense['open_balance'] = max(0, round((float) ($expense['amount'] ?? 0) - $settled, 2));
            $expense['can_record_payment'] = $expense['open_balance'] > 0.0001;

            $totalBills += (float) ($expense['amount'] ?? 0);
            $totalPaid += $settled;
            $openPayables += $expense['open_balance'];

            $lastPayment = $paymentModel
                ->where('clinic_id', $clinicId)
                ->where('finance_id', (int) $expense['id'])
                ->orderBy('payment_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->first();
            if (!empty($lastPayment['payment_date']) && ($lastPaymentDate === null || $lastPayment['payment_date'] > $lastPaymentDate)) {
                $lastPaymentDate = $lastPayment['payment_date'];
            }
        }
        unset($expense);

        return $this->view('inventory/supplier_show', [
            'title' => 'Supplier Details',
            'supplier' => $supplier,
            'expenses' => $expenses,
            'summary' => [
                'total_bills' => round($totalBills, 2),
                'total_paid' => round($totalPaid, 2),
                'open_payables' => round($openPayables, 2),
                'transaction_count' => $transactionCount,
                'last_payment_date' => $lastPaymentDate,
                'payment_count' => count($paymentRows),
            ],
            'payment_rows' => $paymentRows,
            'currency_symbol' => settings()->getCurrencySymbol(),
            'currency_position' => settings()->get('currency_position', 'before'),
        ]);
    }


    public function quickCreateSupplier()
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($name === '' || mb_strlen($name) < 2) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Supplier name is required.']);
        }

        $existing = $this->supplierModel->findByClinicAndName($clinicId, $name);
        if ($existing) {
            return $this->response->setJSON([
                'success' => true,
                'supplier' => [
                    'id' => (int) $existing['id'],
                    'name' => (string) $existing['name'],
                ],
                'duplicate' => true,
            ]);
        }

        $payload = [
            'clinic_id' => $clinicId,
            'name' => $name,
            'contact_person' => trim((string) $this->request->getPost('contact_person')) ?: null,
            'phone' => trim((string) $this->request->getPost('phone')) ?: null,
            'email' => trim((string) $this->request->getPost('email')) ?: null,
            'whatsapp' => trim((string) $this->request->getPost('whatsapp')) ?: null,
            'is_active' => 1,
            'created_by' => (int) (session()->get('user_id') ?? 0),
        ];

        if (!$this->supplierModel->insert($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => implode(', ', $this->supplierModel->errors())]);
        }

        return $this->response->setJSON([
            'success' => true,
            'supplier' => [
                'id' => (int) $this->supplierModel->getInsertID(),
                'name' => $name,
            ],
        ]);
    }

    public function upsertSupplierLink($inventoryId)
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return redirect()->back()->with('error', 'Clinic context is required.');
        }

        $item = $this->inventoryModel->where('clinic_id', $clinicId)->find($inventoryId);
        if (!$item) {
            return redirect()->back()->with('error', 'Inventory item not found.');
        }

        $supplierId = (int) ($this->request->getPost('link_supplier_id') ?? 0);
        if ($supplierId <= 0) {
            return redirect()->back()->with('error', 'Supplier is required.');
        }

        $supplier = $this->supplierModel->where('clinic_id', $clinicId)->find($supplierId);
        if (!$supplier) {
            return redirect()->back()->with('error', 'Supplier not found in this clinic.');
        }

        $isPreferred = $this->request->getPost('is_preferred') ? 1 : 0;
        if ($isPreferred) {
            $this->supplierLinkModel->clearPreferredForInventory($clinicId, (int) $inventoryId);
        }

        $payload = [
            'clinic_id' => $clinicId,
            'inventory_id' => (int) $inventoryId,
            'supplier_id' => $supplierId,
            'supplier_sku' => trim((string) $this->request->getPost('supplier_sku')) ?: null,
            'pack_size_label' => trim((string) $this->request->getPost('pack_size_label')) ?: null,
            'preferred_order_qty' => $this->request->getPost('preferred_order_qty') !== '' ? (int) $this->request->getPost('preferred_order_qty') : null,
            'last_unit_price' => $this->request->getPost('last_unit_price') !== '' ? (float) $this->request->getPost('last_unit_price') : null,
            'currency' => strtoupper(trim((string) ($this->request->getPost('link_currency') ?: 'ILS'))),
            'is_preferred' => $isPreferred,
            'notes' => trim((string) $this->request->getPost('link_notes')) ?: null,
            'created_by' => (int) (session()->get('user_id') ?? 0),
        ];

        $existing = $this->supplierLinkModel->findLink($clinicId, (int) $inventoryId, $supplierId);
        $ok = $existing
            ? $this->supplierLinkModel->update((int) $existing['id'], $payload)
            : $this->supplierLinkModel->insert($payload);

        if (!$ok) {
            return redirect()->back()->with('error', 'Failed to save supplier mapping.');
        }

        return redirect()->back()->with('success', 'Supplier mapping saved.');
    }

    public function deleteSupplierLink($inventoryId, $linkId)
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return redirect()->back()->with('error', 'Clinic context is required.');
        }

        $item = $this->inventoryModel->where('clinic_id', $clinicId)->find($inventoryId);
        if (!$item) {
            return redirect()->back()->with('error', 'Inventory item not found.');
        }

        $link = $this->supplierLinkModel->where('clinic_id', $clinicId)
            ->where('inventory_id', (int) $inventoryId)
            ->find((int) $linkId);
        if (!$link) {
            return redirect()->back()->with('error', 'Supplier mapping not found.');
        }

        $this->supplierLinkModel->delete((int) $linkId);

        return redirect()->back()->with('success', 'Supplier mapping removed.');
    }

    public function reorderList()
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $items = $this->reorderItemModel->getOpenByClinic($clinicId);

        return $this->view('inventory/reorder_list', [
            'title' => 'Reorder List',
            'reorder_items' => $items,
        ]);
    }

    public function markReorderSupplierContacted($id)
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return redirect()->back()->with('error', 'Clinic context is required.');
        }

        $row = $this->reorderItemModel->where('clinic_id', $clinicId)->find((int) $id);
        if (!$row) {
            return redirect()->back()->with('error', 'Reorder item not found.');
        }

        $method = strtolower(trim((string) $this->request->getPost('method')));
        if (!in_array($method, ['phone', 'email', 'whatsapp', 'other'], true)) {
            $method = 'other';
        }

        $this->reorderItemModel->update((int) $id, [
            'supplier_contacted_at' => date('Y-m-d H:i:s'),
            'supplier_contact_method' => $method,
            'supplier_contacted_by' => (int) (session()->get('user_id') ?? 0) ?: null,
        ]);

        return redirect()->back()->with('success', 'Supplier contact logged.');
    }

    public function addToReorder()
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $inventoryId = (int) ($this->request->getPost('inventory_id') ?? 0);
        $item = $this->inventoryModel->where('clinic_id', $clinicId)->find($inventoryId);
        if (!$item) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Inventory item not found']);
        }

        $preferredLink = $this->supplierLinkModel->getPreferredForInventory($clinicId, $inventoryId);
        $supplierId = $preferredLink ? (int) ($preferredLink['supplier_id'] ?? 0) : null;
        $suggestedQty = $preferredLink && !empty($preferredLink['preferred_order_qty'])
            ? (int) $preferredLink['preferred_order_qty']
            : max(1, ((int) ($item['min_quantity'] ?? 0)) - ((int) ($item['quantity'] ?? 0)));
        if ($suggestedQty <= 0) {
            $suggestedQty = max(1, (int) ($item['min_quantity'] ?? 1));
        }

        $requestedQty = $this->request->getPost('requested_qty');
        $requestedQty = ($requestedQty !== null && $requestedQty !== '') ? (int) $requestedQty : $suggestedQty;
        if ($requestedQty <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Requested quantity must be greater than zero']);
        }

        $existing = $this->reorderItemModel->findExistingDraft($clinicId, $inventoryId, $supplierId ?: null);
        if ($existing) {
            $newQty = (int) $existing['requested_qty'] + $requestedQty;
            $this->reorderItemModel->update((int) $existing['id'], [
                'requested_qty' => $newQty,
                'notes' => trim((string) ($this->request->getPost('notes') ?? $existing['notes'])) ?: null,
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Updated existing draft reorder line.',
                'reorder_item_id' => (int) $existing['id'],
                'requested_qty' => $newQty,
            ]);
        }

        $payload = [
            'clinic_id' => $clinicId,
            'inventory_id' => $inventoryId,
            'supplier_id' => $supplierId ?: null,
            'requested_qty' => $requestedQty,
            'status' => 'draft',
            'notes' => trim((string) $this->request->getPost('notes')) ?: null,
            'created_by' => (int) (session()->get('user_id') ?? 0),
        ];

        if (!$this->reorderItemModel->insert($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Failed to add to reorder list']);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Added to reorder list.',
            'reorder_item_id' => (int) $this->reorderItemModel->getInsertID(),
            'requested_qty' => $requestedQty,
        ]);
    }

    public function updateReorderItemStatus($id)
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return redirect()->back()->with('error', 'Clinic context is required.');
        }

        $row = $this->reorderItemModel->where('clinic_id', $clinicId)->find((int) $id);
        if (!$row) {
            return redirect()->back()->with('error', 'Reorder item not found.');
        }

        $status = (string) $this->request->getPost('status');
        if (!in_array($status, ['draft', 'ordered', 'received', 'cancelled'], true)) {
            return redirect()->back()->with('error', 'Invalid reorder status.');
        }

        $update = ['status' => $status];
        if ($status === 'ordered') {
            $update['ordered_at'] = date('Y-m-d H:i:s');
        }
        if ($this->request->getPost('requested_qty') !== null && $this->request->getPost('requested_qty') !== '') {
            $qty = (int) $this->request->getPost('requested_qty');
            if ($qty > 0) {
                $update['requested_qty'] = $qty;
            }
        }
        if ($this->request->getPost('notes') !== null) {
            $update['notes'] = trim((string) $this->request->getPost('notes')) ?: null;
        }

        $this->reorderItemModel->update((int) $id, $update);
        return redirect()->back()->with('success', 'Reorder line updated.');
    }

    public function deleteReorderItem($id)
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return redirect()->back()->with('error', 'Clinic context is required.');
        }
        $row = $this->reorderItemModel->where('clinic_id', $clinicId)->find((int) $id);
        if (!$row) {
            return redirect()->back()->with('error', 'Reorder item not found.');
        }
        $this->reorderItemModel->delete((int) $id);
        return redirect()->back()->with('success', 'Reorder line removed.');
    }

    public function movements()
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $filters = [
            'movement_type' => trim((string) $this->request->getGet('movement_type')),
            'inventory_id' => (int) ($this->request->getGet('inventory_id') ?? 0),
            'date_from' => $this->normalizeDateOnly((string) $this->request->getGet('date_from')),
            'date_to' => $this->normalizeDateOnly((string) $this->request->getGet('date_to')),
            'q' => trim((string) $this->request->getGet('q')),
            'limit' => (int) ($this->request->getGet('limit') ?? 100),
        ];

        $movements = (new InventoryStockMovementModel())->getLedgerByClinic($clinicId, $filters);

        return $this->view('inventory/movements', [
            'title' => 'Inventory Movement Ledger',
            'movements' => $movements,
            'filters' => $filters,
            'inventory_options' => $this->inventoryModel->where('clinic_id', $clinicId)->orderBy('item_name', 'ASC')->findAll(),
            'movement_types' => [
                '' => 'All Types',
                'initial_stock' => 'Initial Stock',
                'receive_stock' => 'Receive Stock',
                'manual_adjust_in' => 'Manual Adjust In',
                'manual_adjust_out' => 'Manual Adjust Out',
                'usage_consume' => 'Usage Consume',
            ],
        ]);
    }

    public function receiveStock($id)
    {
        $this->response->setContentType('application/json');
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $item = $this->inventoryModel->where('clinic_id', $clinicId)->find($id);
        if (!$item) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item not found']);
        }

        $input = json_decode($this->request->getBody(), true);
        $quantity = (int) ($input['quantity'] ?? 0);
        $unitCost = isset($input['unit_cost']) && $input['unit_cost'] !== '' ? (float) $input['unit_cost'] : (float) ($item['unit_price'] ?? 0);
        $reason = trim((string) ($input['reason'] ?? 'Stock received'));
        $lotNumber = trim((string) ($input['lot_number'] ?? ''));
        $batchExpiry = $this->normalizeDateOnly((string) ($input['batch_expiry_date'] ?? ''));
        $batchCurrency = $this->sanitizeSupplierLinkCurrency((string) ($input['batch_currency'] ?? 'ILS'));

        if ($quantity <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Quantity must be greater than 0']);
        }
        if ($unitCost < 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unit cost cannot be negative']);
        }
        if (($input['batch_expiry_date'] ?? '') !== '' && !$batchExpiry) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid batch expiry date']);
        }

        $beforeQty = (int) ($item['quantity'] ?? 0);
        $afterQty = $beforeQty + $quantity;
        $updateData = [
            'quantity' => $afterQty,
            'unit_price' => $unitCost, // latest cost snapshot
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (!$this->inventoryModel->update($id, $updateData)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to receive stock']);
        }

        $this->activityLogger->logInventoryActivity(
            'receive_stock',
            (int) $id,
            "Stock received for '{$item['item_name']}' - Qty: {$quantity}, Unit Cost: " . settings()->getCurrencySymbol() . number_format($unitCost, 2) . ($reason !== '' ? ", Ref: {$reason}" : '')
        );

        $movementId = $this->recordStockMovementSafe(
            $clinicId,
            (int) $id,
            'receive_stock',
            $quantity,
            $beforeQty,
            $afterQty,
            $unitCost,
            'inventory_receive',
            (int) $id,
            $reason,
            (int) (session()->get('user_id') ?? 0)
        );
        $movementId = (int) ($movementId ?? 0);

        $batchId = null;
        if ($lotNumber !== '' || $batchExpiry) {
            $batchPayload = [
                'clinic_id' => $clinicId,
                'inventory_id' => (int) $id,
                'lot_number' => $lotNumber !== '' ? $lotNumber : null,
                'expiry_date' => $batchExpiry ?: null,
                'received_qty' => $quantity,
                'remaining_qty' => $quantity,
                'unit_cost' => $unitCost,
                'currency' => $batchCurrency,
                'source_movement_id' => $movementId ?: null,
                'notes' => $reason !== '' ? $reason : null,
                'created_by' => (int) (session()->get('user_id') ?? 0) ?: null,
            ];
            if ($this->batchModel->insert($batchPayload)) {
                $batchId = (int) $this->batchModel->getInsertID();
                if ($movementId > 0) {
                    (new InventoryStockMovementModel())->update($movementId, ['batch_id' => $batchId]);
                }
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Stock received successfully',
            'new_quantity' => $afterQty,
            'batch_id' => $batchId,
        ]);
    }

    public function usage()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        // Get all active inventory items (scoped)
        $inventoryItems = $this->inventoryModel->where('status', 'active')
            ->where('clinic_id', $clinicId)
            ->where('quantity >', 0)
            ->orderBy('item_name', 'ASC')
            ->findAll();
        
        // Get all active treatments for dropdown
        $treatmentModel = new \App\Models\TreatmentModel();
        
        // Get treatments with patients (scoped)
        $treatments = $treatmentModel->join('patients', 'patients.id = treatments.patient_id')
            ->select('treatments.*, patients.first_name, patients.last_name')
            ->where('treatments.clinic_id', $clinicId) // Scope treatments
            ->orderBy('treatments.start_date', 'DESC')
            ->findAll();
        
        $data = [
            'title' => 'Inventory Usage',
            'inventory_items' => $inventoryItems,
            'treatments' => $treatments,
            'validation' => \Config\Services::validation()
        ];

        return $this->view('inventory/usage', $data);
    }

    public function recordUsage()
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $rules = [
            'treatment_id' => 'permit_empty|integer',
            'usage_date' => 'required|valid_date',
            'items' => 'required',
            'notes' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $result = (new InventoryUsageService())->record(
                $clinicId,
                [
                    'treatment_id' => $this->request->getPost('treatment_id') ?: null,
                    'usage_date' => $this->request->getPost('usage_date'),
                    'notes' => $this->request->getPost('notes'),
                    'items' => $this->request->getPost('items'),
                ],
                (int) ($this->getCurrentUser()->id ?? 0)
            );

            if (!$result['success']) {
                return redirect()->back()->withInput()->with('error', (string) ($result['message'] ?? 'Failed to record inventory usage.'));
            }

            $usageData = (array) ($result['usage_data'] ?? []);
            $itemNames = array_column($usageData, 'item_name');
            $itemCount = count($usageData);
            $totalItemsUsed = array_sum(array_column($usageData, 'quantity_used'));
            $totalCost = (float) ($result['total_cost'] ?? 0);

            $description = "Inventory usage recorded - {$itemCount} item(s): " . implode(', ', $itemNames) . " (Total: {$totalItemsUsed} units, Cost: " . settings()->getCurrencySymbol() . number_format($totalCost, 2) . ")";
            if (!empty($result['patient_name'])) {
                $description = "Inventory usage recorded for {$result['patient_name']} - {$itemCount} item(s): " . implode(', ', $itemNames) . " (Total: {$totalItemsUsed} units, Cost: " . settings()->getCurrencySymbol() . number_format($totalCost, 2) . ")";
            }

            $this->activityLogger->logInventoryActivity('usage', (int) ($result['usage_id'] ?? 0), $description);
        } catch (\Exception $e) {
            log_message('error', 'Usage insert exception: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Database error: ' . $e->getMessage());
        }

        return redirect()->to(base_url('inventory/usage-history'))->with('success', 'Inventory usage recorded successfully!');
    }

    public function usageHistory()
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $usageModel = new \App\Models\InventoryUsageModel();
        $usageHistory = $usageModel->join('treatments', 'treatments.id = inventory_usage.treatment_id', 'left')
            ->join('patients', 'patients.id = treatments.patient_id', 'left')
            ->join('users', 'users.id = inventory_usage.recorded_by', 'left')
            ->join(
                'finances f',
                "f.clinic_id = inventory_usage.clinic_id
                AND f.source_entity_type = 'inventory_usage'
                AND f.source_entity_id = inventory_usage.id
                AND f.transaction_type = 'expense'",
                'left'
            )
            ->select('inventory_usage.*, treatments.treatment_name, treatments.treatment_id, patients.first_name, patients.last_name, users.first_name as recorded_by_name, f.id as finance_entry_id')
            ->where('inventory_usage.clinic_id', $clinicId)
            ->orderBy('inventory_usage.created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Usage History',
            'usage_history' => $usageHistory
        ];

        return $this->view('inventory/usage_history', $data);
    }

    public function usageDetails($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $usageModel = new \App\Models\InventoryUsageModel();
        $usage = $usageModel->join('treatments', 'treatments.id = inventory_usage.treatment_id', 'left')
            ->join('patients', 'patients.id = treatments.patient_id', 'left')
            ->join('users', 'users.id = inventory_usage.recorded_by', 'left')
            ->join(
                'finances f',
                "f.clinic_id = inventory_usage.clinic_id
                AND f.source_entity_type = 'inventory_usage'
                AND f.source_entity_id = inventory_usage.id
                AND f.transaction_type = 'expense'",
                'left'
            )
            ->select('inventory_usage.*, treatments.treatment_name, treatments.treatment_id, patients.first_name, patients.last_name, users.first_name as recorded_by_name, f.id as finance_entry_id')
            ->where('inventory_usage.id', $id)
            ->where('inventory_usage.clinic_id', $clinicId)
            ->first();

        if (!$usage) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Usage record not found'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'usage' => $usage
        ]);
    }

    public function usagePrint($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $usageModel = new \App\Models\InventoryUsageModel();
        $usage = $usageModel->join('treatments', 'treatments.id = inventory_usage.treatment_id', 'left')
            ->join('patients', 'patients.id = treatments.patient_id', 'left')
            ->join('users', 'users.id = inventory_usage.recorded_by', 'left')
            ->join(
                'finances f',
                "f.clinic_id = inventory_usage.clinic_id
                AND f.source_entity_type = 'inventory_usage'
                AND f.source_entity_id = inventory_usage.id
                AND f.transaction_type = 'expense'",
                'left'
            )
            ->select('inventory_usage.*, treatments.treatment_name, treatments.treatment_id, patients.first_name, patients.last_name, users.first_name as recorded_by_name, f.id as finance_entry_id')
            ->where('inventory_usage.id', $id)
            ->where('inventory_usage.clinic_id', $clinicId)
            ->first();

        if (!$usage) {
            return redirect()->to(base_url('inventory/usage-history'))->with('error', 'Usage record not found');
        }

        $data = [
            'title' => 'Usage Record',
            'usage' => $usage
        ];

        $html = view('inventory/usage_print', $data);
        
        // Persist artifact
        $fileName = 'inventory_usage_' . $id . '.html';
        try {
            $this->storageService->storeExport(
                $html,
                $fileName,
                'text/html',
                $clinicId,
                'inventory_usage',
                $id,
                'usage_print'
            );
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            log_message('warning', 'Inventory usage print export blocked: ' . $e->getMessage());
        } catch (\Exception $e) {
            log_message('error', 'Inventory usage print export failed: ' . $e->getMessage());
        }

        return $this->response
            ->setHeader('Content-Type', 'text/html')
            ->setBody($html);
    }

    public function getInventoryData()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $request = $this->request;
            
            // Debug logging
            log_message('info', 'getInventoryData called');
            
            // DataTables parameters with defaults - try both GET and POST
            $draw = intval($request->getPost('draw') ?? $request->getGet('draw') ?? 1);
            $start = intval($request->getPost('start') ?? $request->getGet('start') ?? 0);
            $length = intval($request->getPost('length') ?? $request->getGet('length') ?? 10);
            
            // Handle search parameter - try both GET and POST
            $searchParam = $request->getPost('search') ?? $request->getGet('search');
            $searchValue = '';
            if (is_array($searchParam) && isset($searchParam['value'])) {
                $searchValue = trim($searchParam['value']);
            }
            
            // Handle order parameter - try both GET and POST
            $orderParam = $request->getPost('order') ?? $request->getGet('order');
            $orderColumn = 0;
            $orderDir = 'asc';
            if (is_array($orderParam) && isset($orderParam[0])) {
                $orderColumn = intval($orderParam[0]['column'] ?? 0);
                $orderDir = $orderParam[0]['dir'] ?? 'asc';
            }
            
            // Column mapping for ordering
            $columns = [
                'id',
                'item_name',
                'category',
                'quantity',
                'unit_price',
                'supplier',
                'expiry_date',
                'status'
            ];
            
            $orderColumnName = $columns[$orderColumn] ?? 'id';
            
            // Get total records count (scoped)
            $totalRecords = $this->inventoryModel->countInventoryByClinic($clinicId);
            
            // If no inventory items exist, return empty result with proper structure
            if ($totalRecords == 0) {
                return $this->response->setJSON([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'message' => 'No inventory items found. Add some items to see them here.'
                ]);
            }
            
            // Get filtered count
            $filteredRecords = $this->inventoryModel->countInventoryByClinic($clinicId, $searchValue);
            
            // Get data with ordering and pagination
            $items = $this->inventoryModel->getInventoryByClinic($clinicId, $length, $start, $searchValue, $orderColumnName, $orderDir);
            
            // Format data for DataTables (array format)
            $data = [];
            foreach ($items as $item) {
                // Simple currency formatting without helper function
                $unitPrice = '$' . number_format($item['unit_price'] ?? 0, 2);
                
                // Simple date formatting without helper function
                $expiryDate = 'N/A';
                if (!empty($item['expiry_date'])) {
                    try {
                        $date = new \DateTime($item['expiry_date']);
                        $expiryDate = $date->format('Y-m-d');
                    } catch (\Exception $e) {
                        $expiryDate = $item['expiry_date'];
                    }
                }
                
                $data[] = [
                    intval($item['id'] ?? 0), // ID (hidden)
                    $item['item_name'] ?? '', // Item Name
                    $item['category'] ?? '', // Category
                    $item['quantity'] . ' ' . ($item['unit'] ?: 'pieces'), // Quantity
                    $unitPrice, // Unit Price
                    $item['supplier'] ?? '', // Supplier
                    $expiryDate, // Expiry Date
                    $this->getStatusBadge($item['status'] ?? 'active'), // Status
                    $this->getActionButtons($item['id']) // Actions
                ];
            }
            
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Inventory DataTables error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading data: ' . $e->getMessage(),
                'debug_info' => [
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine()
                ]
            ]);
        }
    }

    public function getUsageHistoryData()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $request = $this->request;
            
            // DataTables parameters with defaults
            $draw = intval($request->getPost('draw') ?? 1);
            $start = intval($request->getPost('start') ?? 0);
            $length = intval($request->getPost('length') ?? 10);
            
            // Handle search parameter
            $searchParam = $request->getPost('search');
            $searchValue = '';
            if (is_array($searchParam) && isset($searchParam['value'])) {
                $searchValue = trim($searchParam['value']);
            }
            
            // Handle order parameter
            $orderParam = $request->getPost('order');
            $orderColumn = 0;
            $orderDir = 'desc';
            if (is_array($orderParam) && isset($orderParam[0])) {
                $orderColumn = intval($orderParam[0]['column'] ?? 0);
                $orderDir = $orderParam[0]['dir'] ?? 'desc';
            }
        
        // Column mapping
        $columns = ['inventory_usage.id', 'inventory_usage.usage_date', 'patients.first_name', 'treatments.treatment_name', 'inventory_usage.total_cost', 'users.first_name', 'inventory_usage.created_at'];
        $orderColumnName = $columns[$orderColumn] ?? 'inventory_usage.created_at';
        
        $usageModel = new \App\Models\InventoryUsageModel();
        
        $baseBuilder = $usageModel->builder()
            ->join('treatments', 'treatments.id = inventory_usage.treatment_id', 'left')
            ->join('patients', 'patients.id = treatments.patient_id', 'left')
            ->join('users', 'users.id = inventory_usage.recorded_by', 'left')
            ->join(
                'finances f',
                "f.clinic_id = inventory_usage.clinic_id
                AND f.source_entity_type = 'inventory_usage'
                AND f.source_entity_id = inventory_usage.id
                AND f.transaction_type = 'expense'",
                'left'
            )
            ->select('inventory_usage.*, treatments.treatment_name, treatments.treatment_id, patients.first_name, patients.last_name, users.first_name as recorded_by_name, f.id as finance_entry_id')
            ->where('inventory_usage.clinic_id', $clinicId);
        
        // Apply search filter
        if (!empty($searchValue)) {
            $baseBuilder->groupStart()
                ->like('treatments.treatment_id', $searchValue)
                ->orLike('treatments.treatment_name', $searchValue)
                ->orLike('patients.first_name', $searchValue)
                ->orLike('patients.last_name', $searchValue)
                ->orLike('users.first_name', $searchValue)
                ->groupEnd();
        }
        
        // Get total count (scoped, no joins needed)
        $totalRecords = $usageModel->where('inventory_usage.clinic_id', $clinicId)->countAllResults();
        
        // Get filtered count
        $countBuilder = clone $baseBuilder;
        $filteredRecords = $countBuilder->countAllResults();
        
        // Get data
        $data = $baseBuilder->orderBy($orderColumnName, $orderDir)
            ->limit($length, $start)
            ->get()
            ->getResultArray();
        
        // Format data for DataTables
        $formattedData = [];
        foreach ($data as $usage) {
            $items = json_decode($usage['items_used'], true);
            $itemsText = '';
            if (is_array($items) && !empty($items)) {
                $itemNames = array_slice(array_column($items, 'item_name'), 0, 2);
                $itemsText = implode(', ', $itemNames);
                if (count($items) > 2) {
                    $itemsText .= ' +' . (count($items) - 2) . ' more';
                }
            }
            
            $patientName = trim(($usage['first_name'] ?? '') . ' ' . ($usage['last_name'] ?? ''));
            $patientName = $patientName !== '' ? $patientName : 'Unknown Patient';
            $treatmentName = $usage['treatment_name'] ?? 'N/A';
            $treatmentId = $usage['treatment_id'] ?? 'N/A';
            $totalCost = isset($usage['total_cost']) ? (float) $usage['total_cost'] : 0;
            $formattedData[] = [
                $this->formatUsageDate($usage['usage_date'] ?? null, $usage['created_at'] ?? null),
                $patientName,
                $treatmentName . '<br><small class="text-gray-500">' . $treatmentId . '</small>',
                $itemsText,
                '<span class="font-bold text-green-600">$' . number_format($totalCost, 2) . '</span>',
                $usage['recorded_by_name'] ?? '-',
                $this->getUsageActionButtons($usage['id'], isset($usage['finance_entry_id']) ? (int) $usage['finance_entry_id'] : null)
            ];
        }
        
        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData
        ]);
        
        } catch (\Exception $e) {
            log_message('error', 'Usage History DataTables error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading data: ' . $e->getMessage()
            ]);
        }
    }

    public function getLowStockData()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $request = $this->request;
            
            // DataTables parameters with defaults
            $draw = intval($request->getPost('draw') ?? 1);
            $start = intval($request->getPost('start') ?? 0);
            $length = intval($request->getPost('length') ?? 10);
            
            // Handle search parameter
            $searchParam = $request->getPost('search');
            $searchValue = '';
            if (is_array($searchParam) && isset($searchParam['value'])) {
                $searchValue = trim($searchParam['value']);
            }
            
            // Handle order parameter
            $orderParam = $request->getPost('order');
            $orderColumn = 0;
            $orderDir = 'asc';
            if (is_array($orderParam) && isset($orderParam[0])) {
                $orderColumn = intval($orderParam[0]['column'] ?? 0);
                $orderDir = $orderParam[0]['dir'] ?? 'asc';
            }
        
            // Column mapping for ordering
            $columns = [
                'inventory.id',
                'inventory.item_name',
                'inventory.category',
                'inventory.quantity',
                'inventory.min_quantity',
                'inventory.unit_price',
                'supplier_display_name',
                'inventory.expiry_date'
            ];
            
            $orderColumnName = $columns[$orderColumn] ?? 'id';
            
            // Get total records count for low stock items (scoped)
            // Use model builder to avoid raw query
            $totalRecords = $this->inventoryModel->where('clinic_id', $clinicId)->where('quantity <= min_quantity', null, false)->countAllResults();
            
            // If no low stock items exist, return empty result
            if ($totalRecords == 0) {
                return $this->response->setJSON([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]);
            }
            
            // Build query for low stock items
            $builder = $this->inventoryModel->builder();
            $builder->select('inventory.*, COALESCE(ps.name, inventory.supplier) as supplier_display_name, isl.supplier_sku as preferred_supplier_sku, isl.pack_size_label as preferred_pack_size_label, ps.phone as supplier_phone, ps.email as supplier_email, ps.whatsapp as supplier_whatsapp')
                ->join('inventory_supplier_links isl', 'isl.clinic_id = inventory.clinic_id AND isl.inventory_id = inventory.id AND isl.is_preferred = 1', 'left')
                ->join('suppliers ps', 'ps.id = isl.supplier_id AND ps.clinic_id = inventory.clinic_id', 'left')
                ->where('inventory.clinic_id', $clinicId)
                ->where('inventory.quantity <= inventory.min_quantity', null, false);
            
            // Apply search filter
            if (!empty($searchValue)) {
                $builder->groupStart()
                    ->like('item_name', $searchValue)
                    ->orLike('category', $searchValue)
                    ->orLike('supplier', $searchValue)
                    ->orLike('ps.name', $searchValue)
                    ->orLike('isl.supplier_sku', $searchValue)
                    ->groupEnd();
            }
            
            // Get filtered count
            $filteredRecords = $builder->countAllResults(false);
            
            // Get data with ordering and pagination
            $items = $builder->orderBy($orderColumnName, $orderDir)
                ->limit($length, $start)
                ->get()
                ->getResultArray();
            
            // Format data for DataTables (array format)
            $data = [];
            foreach ($items as $item) {
                $stockLevel = $this->getStockLevel($item['quantity'], $item['min_quantity']);
                
                $supplierParts = [];
                $supplierName = (string) ($item['supplier_display_name'] ?? '');
                if ($supplierName !== '') {
                    $supplierParts[] = '<div class="font-medium text-slate-900">' . esc($supplierName) . '</div>';
                } else {
                    $supplierParts[] = '<div class="text-slate-500">N/A</div>';
                }
                $meta = [];
                if (!empty($item['preferred_supplier_sku'])) {
                    $meta[] = 'SKU: ' . esc((string) $item['preferred_supplier_sku']);
                }
                if (!empty($item['preferred_pack_size_label'])) {
                    $meta[] = 'Pack: ' . esc((string) $item['preferred_pack_size_label']);
                }
                if (!empty($meta)) {
                    $supplierParts[] = '<div class="text-xs text-slate-500">' . implode(' • ', $meta) . '</div>';
                }
                $contactButtons = [];
                if (!empty($item['supplier_phone'])) {
                    $contactButtons[] = '<a href="tel:' . esc((string) $item['supplier_phone']) . '" class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-[11px]">Call</a>';
                }
                if (!empty($item['supplier_email'])) {
                    $contactButtons[] = '<a href="mailto:' . esc((string) $item['supplier_email']) . '" class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-[11px]">Email</a>';
                }
                if (!empty($item['supplier_whatsapp'])) {
                    $wa = preg_replace('/\D+/', '', (string) $item['supplier_whatsapp']);
                    $contactButtons[] = '<a href="https://wa.me/' . esc($wa) . '" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 text-[11px]">WhatsApp</a>';
                }
                if (!empty($contactButtons)) {
                    $supplierParts[] = '<div class="flex flex-wrap gap-1 mt-1">' . implode('', $contactButtons) . '</div>';
                }

                $data[] = [
                    intval($item['id'] ?? 0), // ID (hidden)
                    $item['item_name'] ?? '', // Item Name
                    $item['category'] ?? '', // Category
                    '<span class="' . $stockLevel['class'] . '">' . $item['quantity'] . '</span>', // Current Stock
                    $item['min_quantity'] ?? 0, // Minimum Required
                    '$' . number_format($item['unit_price'] ?? 0, 2), // Unit Price
                    implode('', $supplierParts), // Supplier
                    $item['expiry_date'] ? date('M j, Y', strtotime($item['expiry_date'])) : 'N/A', // Expiry Date
                    $this->getLowStockActionButtons($item['id']) // Actions
                ];
            }
            
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Low Stock DataTables error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading data: ' . $e->getMessage()
            ]);
        }
    }

    private function getStatusBadge($status)
    {
        $badges = [
            'active' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">' . esc(lang('Inventory.statusActive')) . '</span>',
            'inactive' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">' . esc(lang('Inventory.statusInactive')) . '</span>',
            'expired' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">' . esc(lang('Inventory.expiredLabel')) . '</span>',
        ];
        return $badges[$status] ?? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">' . esc(lang('Inventory.unknown')) . '</span>';
    }

    private function getActionButtons($id)
    {
        return '
            <div class="flex space-x-2">
                <a href="' . base_url() . 'inventory/' . $id . '" class="text-blue-600 hover:text-blue-900" title="' . esc(lang('Inventory.viewDetails')) . '">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="' . base_url() . 'inventory/' . $id . '/edit" class="text-green-600 hover:text-green-900" title="' . esc(lang('Inventory.edit')) . '">
                    <i class="fas fa-edit"></i>
                </a>
                <button onclick="confirmDelete(\''. base_url() . 'inventory/' . $id . '\', \'' . esc(lang('Inventory.deleteInventoryConfirm')) . '\')" class="text-red-600 hover:text-red-900" title="' . esc(lang('Inventory.delete')) . '">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        ';
    }

    private function getUsageActionButtons($id, ?int $financeEntryId = null)
    {
        $financeButton = '';
        if ($financeEntryId) {
            $financeButton = '
                <a href="' . base_url() . 'finance/' . $financeEntryId . '" class="text-rose-600 hover:text-rose-900" title="' . esc(lang('Inventory.viewFinanceEntry')) . '">
                    <i class="fas fa-file-invoice-dollar"></i>
                </a>
            ';
        }

        return '
            <div class="flex space-x-2">
                <button onclick="viewUsageDetails(' . $id . ')" class="text-blue-600 hover:text-blue-900" title="' . esc(lang('Inventory.viewDetails')) . '">
                    <i class="fas fa-eye"></i>
                </button>
                <button onclick="printUsageRecord(' . $id . ')" class="text-green-600 hover:text-green-900" title="' . esc(lang('Inventory.printRecord')) . '">
                    <i class="fas fa-print"></i>
                </button>
                ' . $financeButton . '
            </div>
        ';
    }

    private function getLowStockActionButtons($id)
    {
        return '
            <div class="flex space-x-2">
                <a href="' . base_url() . 'inventory/' . $id . '" class="text-blue-600 hover:text-blue-900" title="' . esc(lang('Inventory.viewDetails')) . '">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="' . base_url() . 'inventory/' . $id . '/edit" class="text-green-600 hover:text-green-900" title="' . esc(lang('Inventory.edit')) . '">
                    <i class="fas fa-edit"></i>
                </a>
                <button type="button" onclick="addToReorderList(' . (int) $id . ')" class="text-amber-600 hover:text-amber-900" title="' . esc(lang('Inventory.addToReorderList')) . '">
                    <i class="fas fa-cart-plus"></i>
                </button>
                <button onclick="adjustStock(' . $id . ')" class="text-purple-600 hover:text-purple-900" title="' . esc(lang('Inventory.adjustStock')) . '">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        ';
    }

    private function formatUsageDate($usageDate, $createdAt)
    {
        $usageLabel = 'N/A';
        $timeLabel = '';
        if (!empty($usageDate)) {
            $usageLabel = date('M j, Y', strtotime($usageDate));
        }
        if (!empty($createdAt)) {
            $timeLabel = date('g:i A', strtotime($createdAt));
        }

        return '
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-lg">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">' . $usageLabel . '</div>
                    <div class="text-sm text-gray-500">' . ($timeLabel ?: '—') . '</div>
                </div>
            </div>
        ';
    }

    private function getStockLevel($quantity, $minQuantity)
    {
        if ($quantity <= 0) {
            return ['class' => 'text-red-600 font-bold', 'level' => lang('Inventory.outOfStock')];
        } elseif ($quantity <= $minQuantity) {
            return ['class' => 'text-red-600 font-bold', 'level' => lang('Inventory.critical')];
        } elseif ($quantity <= ($minQuantity * 1.5)) {
            return ['class' => 'text-yellow-600 font-bold', 'level' => lang('Inventory.low')];
        } else {
            return ['class' => 'text-green-600 font-bold', 'level' => lang('Inventory.good')];
        }
    }

    private function logAdjustment($itemId, $adjustment, $type, $reason)
    {
        // Get item details for the activity log
        $item = $this->inventoryModel->find($itemId);
        if (!$item) return false;
        
        $action = $type === 'add' ? 'stock_add' : 'stock_subtract';
        $description = "Inventory stock {$type}ed for '{$item['item_name']}' - Amount: {$adjustment} units, Reason: {$reason}";
        
        // Log the inventory adjustment activity
        $this->activityLogger->logInventoryActivity(
            $action,
            $itemId,
            $description
        );
        
        return true;
    }

    protected function getCurrentUser()
    {
        // Get current user from session or authentication
        $session = \Config\Services::session();
        $userId = $session->get('user_id');
        
        if ($userId) {
            return (object)['id' => $userId];
        }
        
        // Fallback to a default user ID if no session
        return (object)['id' => 1];
    }

    private function upsertSupplierLinkFromRequest(int $clinicId, int $inventoryId, ?int $supplierId, ?int $createdBy = null): void
    {
        if (!$clinicId || !$inventoryId || !$supplierId) {
            return;
        }

        $supplier = $this->supplierModel->where('clinic_id', $clinicId)->find($supplierId);
        if (!$supplier) {
            return;
        }

        $preferredQtyRaw = $this->request->getPost('preferred_order_qty');
        $lastUnitPriceRaw = $this->request->getPost('supplier_last_unit_price');
        $payload = [
            'clinic_id' => $clinicId,
            'inventory_id' => $inventoryId,
            'supplier_id' => $supplierId,
            'supplier_sku' => trim((string) $this->request->getPost('supplier_sku')) ?: null,
            'pack_size_label' => trim((string) $this->request->getPost('pack_size_label')) ?: null,
            'preferred_order_qty' => ($preferredQtyRaw !== null && $preferredQtyRaw !== '') ? max(1, (int) $preferredQtyRaw) : null,
            'last_unit_price' => ($lastUnitPriceRaw !== null && $lastUnitPriceRaw !== '') ? max(0, (float) $lastUnitPriceRaw) : null,
            'currency' => $this->sanitizeSupplierLinkCurrency((string) $this->request->getPost('supplier_price_currency')),
            'is_preferred' => 1,
            'notes' => trim((string) $this->request->getPost('supplier_link_notes')) ?: null,
            'created_by' => $createdBy ?: ((int) (session()->get('user_id') ?? 0) ?: null),
        ];

        $this->supplierLinkModel->clearPreferredForInventory($clinicId, $inventoryId);
        $existing = $this->supplierLinkModel->findLink($clinicId, $inventoryId, $supplierId);
        if ($existing) {
            $this->supplierLinkModel->update((int) $existing['id'], $payload);
        } else {
            $this->supplierLinkModel->insert($payload);
        }
    }

    private function resolveSupplierSelection(int $clinicId): array
    {
        $supplierIdRaw = $this->request->getPost('supplier_id');
        $supplierId = is_numeric($supplierIdRaw) && (int) $supplierIdRaw > 0 ? (int) $supplierIdRaw : null;
        $supplierText = trim((string) $this->request->getPost('supplier'));
        if ($supplierText === 'other') {
            $supplierText = '';
        }

        if ($supplierId) {
            $supplier = $this->supplierModel->where('clinic_id', $clinicId)->find($supplierId);
            if ($supplier) {
                return [$supplierId, (string) $supplier['name']];
            }
            return [null, $supplierText !== '' ? $supplierText : null];
        }

        return [null, $supplierText !== '' ? $supplierText : null];
    }

    private function sanitizeSupplierLinkCurrency(string $currency): string
    {
        $currency = strtoupper(trim($currency));
        if ($currency === '') {
            return 'ILS';
        }
        return preg_match('/^[A-Z]{3,10}$/', $currency) ? $currency : 'ILS';
    }

    private function recordStockMovementSafe(
        int $clinicId,
        int $inventoryId,
        string $movementType,
        int $quantityChange,
        int $quantityBefore,
        int $quantityAfter,
        ?float $unitCost = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $createdBy = null,
        ?int $batchId = null
    ): ?int {
        try {
            return (new InventoryMovementService())->record(
                $clinicId,
                $inventoryId,
                $movementType,
                $quantityChange,
                $quantityBefore,
                $quantityAfter,
                $unitCost,
                $referenceType,
                $referenceId,
                $notes,
                $createdBy,
                $batchId
            );
        } catch (\Throwable $e) {
            log_message('error', 'Inventory movement logging failed: ' . $e->getMessage());
            return null;
        }
    }
}
