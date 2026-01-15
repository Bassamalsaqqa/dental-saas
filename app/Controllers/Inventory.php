<?php

namespace App\Controllers;

use App\Models\InventoryModel;
use App\Services\ActivityLogger;

class Inventory extends BaseController
{
    protected $inventoryModel;
    protected $activityLogger;
    protected $storageService;
    protected $db;

    public function __construct()
    {
        $this->inventoryModel = new InventoryModel();
        $this->activityLogger = new ActivityLogger();
        $this->storageService = new \App\Services\StorageService();
        $this->db = \Config\Database::connect();
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
            ];

            // Check if inventory table exists first
            $tables = $this->db->listTables();
            if (!in_array('inventory', $tables)) {
                $data['inventory'] = [];
                $data['available_categories'] = [];
                $data['total_items'] = 0;
                $data['low_stock_items'] = 0;
                $data['out_of_stock_items'] = 0;
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
                $data['low_stock_items'] = $this->inventoryModel->where('clinic_id', $clinicId)->where('quantity <=', 'min_quantity')->countAllResults();
                $data['out_of_stock_items'] = $this->inventoryModel->where('clinic_id', $clinicId)->where('quantity', 0)->countAllResults();
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
                'low_stock_items' => 0,
                'out_of_stock_items' => 0,
                'error' => 'Unable to load inventory data. Please check the database connection.'
            ];

            // Ensure user data is still available in error state
            $userData = $this->getUserDataForView();
            $data = array_merge($data, $userData);

            return $this->view('inventory/index', $data);
        }
    }

    public function create()
    {
        $data = [
            'title' => 'Add New Item',
            'categories' => $this->getCategories(),
            'suppliers' => $this->getSuppliers(),
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
            'purchase_date' => 'permit_empty|valid_date',
            'expiry_date' => 'permit_empty|valid_date',
            'location' => 'permit_empty|max_length[100]',
            'notes' => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $inventoryData = [
            'item_name' => $this->request->getPost('item_name'),
            'category' => $this->request->getPost('category'),
            'description' => $this->request->getPost('description'),
            'quantity' => $this->request->getPost('quantity'),
            'min_quantity' => $this->request->getPost('min_quantity'),
            'unit_price' => $this->request->getPost('unit_price'),
            'unit' => $this->request->getPost('unit') ?: 'pieces',
            'supplier' => $this->request->getPost('supplier'),
            'purchase_date' => $this->request->getPost('purchase_date') ?: null,
            'expiry_date' => $this->request->getPost('expiry_date') ?: null,
            'location' => $this->request->getPost('location'),
            'notes' => $this->request->getPost('notes'),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->inventoryModel->insert($inventoryData)) {
            $inventoryId = $this->inventoryModel->getInsertID();
            
            // Log the inventory creation activity
            $this->activityLogger->logInventoryActivity(
                'create',
                $inventoryId,
                "New inventory item '{$inventoryData['item_name']}' added - Category: {$inventoryData['category']}, Quantity: {$inventoryData['quantity']}, Price: " . settings()->getCurrencySymbol() . number_format($inventoryData['unit_price'], 2)
            );
            
            return redirect()->to(base_url('inventory'))->with('success', 'Item added successfully!');
        } else {
            $errors = $this->inventoryModel->errors();
            log_message('error', 'Inventory creation failed: ' . json_encode($errors));
            log_message('error', 'Inventory data that failed: ' . json_encode($inventoryData));
            return redirect()->back()->withInput()->with('error', 'Failed to add item: ' . implode(', ', $errors));
        }
    }

    public function show($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $item = $this->inventoryModel->where('clinic_id', $clinicId)->find($id);
        
        if (!$item) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Item not found');
        }

        $data = [
            'title' => 'Item Details',
            'item' => $item,
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
            'purchase_date' => 'permit_empty|valid_date',
            'expiry_date' => 'permit_empty|valid_date',
            'location' => 'permit_empty|max_length[100]',
            'notes' => 'permit_empty|max_length[1000]',
            'status' => 'required|in_list[active,inactive,discontinued]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $inventoryData = [
            'item_name' => $this->request->getPost('item_name'),
            'category' => $this->request->getPost('category'),
            'description' => $this->request->getPost('description'),
            'quantity' => $this->request->getPost('quantity'),
            'min_quantity' => $this->request->getPost('min_quantity'),
            'unit_price' => $this->request->getPost('unit_price'),
            'unit' => $this->request->getPost('unit') ?: 'pieces',
            'supplier' => $this->request->getPost('supplier'),
            'purchase_date' => $this->request->getPost('purchase_date') ?: null,
            'expiry_date' => $this->request->getPost('expiry_date') ?: null,
            'location' => $this->request->getPost('location'),
            'notes' => $this->request->getPost('notes'),
            'status' => $this->request->getPost('status'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->inventoryModel->update($id, $inventoryData)) {
            // Log the inventory update activity
            $this->activityLogger->logInventoryActivity(
                'update',
                $id,
                "Inventory item '{$inventoryData['item_name']}' updated - Category: {$inventoryData['category']}, Quantity: {$inventoryData['quantity']}, Status: {$inventoryData['status']}"
            );
            
            return redirect()->to(base_url('inventory'))->with('success', 'Item updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update item. Please try again.');
        }
    }

    public function delete($id)
    {
        try {
            $this->response->setContentType('application/json');
            
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $item = $this->inventoryModel->where('clinic_id', $clinicId)->find($id);
            
            if (!$item) {
                return $this->response->setJSON(['success' => false, 'message' => 'Item not found']);
            }

            if ($this->inventoryModel->delete($id)) {
                // Log the inventory deletion activity
                $this->activityLogger->logInventoryActivity(
                    'delete',
                    $id,
                    "Inventory item '{$item['item_name']}' deleted - Category: {$item['category']}, Quantity was: {$item['quantity']}"
                );
                
                log_message('info', "Inventory item {$id} deleted successfully");
                return $this->response->setJSON(['success' => true, 'message' => 'Item deleted successfully']);
            } else {
                log_message('error', "Failed to delete inventory item {$id}");
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete item']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Inventory delete error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'An error occurred while deleting the item']);
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
            'items' => $this->inventoryModel->where('clinic_id', $clinicId)->where('quantity <=', 'min_quantity')->findAll(),
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
        $totalValueData = $this->inventoryModel->select('SUM(quantity * unit_price) as total_value')->where('status', 'active')->first();
        $totalValue = $totalValueData['total_value'] ?? 0;

        $stats = [
            'total_items' => $this->inventoryModel->countAllResults(),
            'active_items' => $this->inventoryModel->where('status', 'active')->countAllResults(),
            'low_stock_items' => $this->inventoryModel->where('quantity <=', 'min_quantity')->where('status', 'active')->countAllResults(),
            'out_of_stock_items' => $this->inventoryModel->where('quantity', 0)->where('status', 'active')->countAllResults(),
            'expired_items' => $this->inventoryModel->where('expiry_date <', date('Y-m-d'))->where('status', 'active')->countAllResults(),
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
        $rules = [
            'treatment_id' => 'permit_empty|integer',
            'usage_date' => 'required|valid_date',
            'items' => 'required',
            'notes' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $treatmentId = $this->request->getPost('treatment_id') ?: null;
        $usageDate = $this->request->getPost('usage_date');
        $notes = $this->request->getPost('notes');
        $items = $this->request->getPost('items');
        
        // Debug: Log the submitted data
        log_message('info', 'Usage form data: ' . json_encode([
            'treatment_id' => $treatmentId,
            'usage_date' => $usageDate,
            'notes' => $notes,
            'items' => $items
        ]));

        if (empty($items) || !is_array($items)) {
            return redirect()->back()->withInput()->with('error', 'Please select at least one item to record usage.');
        }

        $usageData = [];
        $totalCost = 0;

        // Validate and prepare usage data
        foreach ($items as $item) {
            if (empty($item['item_id']) || empty($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                return redirect()->back()->withInput()->with('error', 'Please provide valid quantities for all selected items.');
            }

            $inventoryItem = $this->inventoryModel->find($item['item_id']);
            if (!$inventoryItem) {
                return redirect()->back()->withInput()->with('error', 'Invalid inventory item selected.');
            }

            if ($inventoryItem['quantity'] < $item['quantity']) {
                return redirect()->back()->withInput()->with('error', "Insufficient stock for {$inventoryItem['item_name']}. Available: {$inventoryItem['quantity']}, Requested: {$item['quantity']}");
            }

            $usageData[] = [
                'item_id' => $item['item_id'],
                'item_name' => $inventoryItem['item_name'],
                'quantity_used' => $item['quantity'],
                'unit_cost' => $inventoryItem['unit_price'],
                'total_cost' => $inventoryItem['unit_price'] * $item['quantity']
            ];

            $totalCost += $inventoryItem['unit_price'] * $item['quantity'];
        }

        // Record usage and update inventory
        $usageLog = [
            'treatment_id' => $treatmentId,
            'usage_date' => $usageDate,
            'items_used' => json_encode($usageData),
            'total_cost' => $totalCost,
            'notes' => $notes,
            'recorded_by' => $this->getCurrentUser()->id,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Insert usage log
        try {
            log_message('info', 'Usage log data: ' . json_encode($usageLog));
            
            $usageModel = new \App\Models\InventoryUsageModel();
            $result = $usageModel->insert($usageLog);
            
            if (!$result) {
                $errors = $usageModel->errors();
                log_message('error', 'Usage insert failed: ' . json_encode($errors));
                return redirect()->back()->withInput()->with('error', 'Failed to record usage: ' . implode(', ', $errors));
            }
            
            $usageId = $usageModel->getInsertID();
            log_message('info', 'Usage record inserted successfully with ID: ' . $usageId);
            
            // Log the inventory usage activity
            $itemNames = array_column($usageData, 'item_name');
            $itemCount = count($usageData);
            $totalItemsUsed = array_sum(array_column($usageData, 'quantity_used'));
            
            $description = "Inventory usage recorded - {$itemCount} item(s): " . implode(', ', $itemNames) . " (Total: {$totalItemsUsed} units, Cost: " . settings()->getCurrencySymbol() . number_format($totalCost, 2) . ")";
            
            if ($treatmentId) {
                // Get treatment info for the description
                $treatmentModel = new \App\Models\TreatmentModel();
                $treatment = $treatmentModel->find($treatmentId);
                if ($treatment) {
                    $patientModel = new \App\Models\PatientModel();
                    $patient = $patientModel->find($treatment['patient_id']);
                    $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
                    $description = "Inventory usage recorded for {$patientName} - {$itemCount} item(s): " . implode(', ', $itemNames) . " (Total: {$totalItemsUsed} units, Cost: " . settings()->getCurrencySymbol() . number_format($totalCost, 2) . ")";
                }
            }
            
            $this->activityLogger->logInventoryActivity(
                'usage',
                $usageId,
                $description
            );
            
        } catch (\Exception $e) {
            log_message('error', 'Usage insert exception: ' . $e->getMessage());
            
            // If table doesn't exist, show helpful message
            if (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), "Table") !== false) {
                return redirect()->back()->withInput()->with('error', 'Database table not found. Please run the inventory_usage_table.sql script first to create the required table.');
            }
            
            return redirect()->back()->withInput()->with('error', 'Database error: ' . $e->getMessage());
        }

        // Update inventory quantities
        foreach ($usageData as $usage) {
            $newQuantity = $this->inventoryModel->find($usage['item_id'])['quantity'] - $usage['quantity_used'];
            $this->inventoryModel->update($usage['item_id'], [
                'quantity' => $newQuantity,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        return redirect()->to(base_url('inventory/usage-history'))->with('success', 'Inventory usage recorded successfully!');
    }

    public function usageHistory()
    {
        $usageModel = new \App\Models\InventoryUsageModel();
        $usageHistory = $usageModel->join('treatments', 'treatments.id = inventory_usage.treatment_id')
            ->join('patients', 'patients.id = treatments.patient_id')
            ->join('users', 'users.id = inventory_usage.recorded_by')
            ->select('inventory_usage.*, treatments.treatment_name, treatments.treatment_id, patients.first_name, patients.last_name, users.first_name as recorded_by_name')
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
            ->select('inventory_usage.*, treatments.treatment_name, treatments.treatment_id, patients.first_name, patients.last_name, users.first_name as recorded_by_name')
            ->where('inventory_usage.id', $id)
            ->where('treatments.clinic_id', $clinicId) // Scope via treatment
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
            ->select('inventory_usage.*, treatments.treatment_name, treatments.treatment_id, patients.first_name, patients.last_name, users.first_name as recorded_by_name')
            ->where('inventory_usage.id', $id)
            ->where('treatments.clinic_id', $clinicId) // Scope
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
        $this->storageService->storeExport(
            $html, 
            $fileName, 
            'text/html', 
            $clinicId, 
            'inventory_usage', 
            $id, 
            'usage_print'
        );

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
        
        // Build query
        $usageModel = new \App\Models\InventoryUsageModel();
        
        $query = $usageModel->join('treatments', 'treatments.id = inventory_usage.treatment_id', 'left') // Use LEFT join to keep records without treatment?
            ->join('patients', 'patients.id = treatments.patient_id', 'left')
            ->join('users', 'users.id = inventory_usage.recorded_by', 'left')
            ->select('inventory_usage.*, treatments.treatment_name, treatments.treatment_id, patients.first_name, patients.last_name, users.first_name as recorded_by_name')
            ->where('treatments.clinic_id', $clinicId); // Enforce clinic scope. If treatment is null, this will exclude the row.
        
        // Apply search filter
        if (!empty($searchValue)) {
            $query->groupStart()
                ->like('treatments.treatment_id', $searchValue)
                ->orLike('treatments.treatment_name', $searchValue)
                ->orLike('patients.first_name', $searchValue)
                ->orLike('patients.last_name', $searchValue)
                ->orLike('users.first_name', $searchValue)
                ->groupEnd();
        }
        
        // Get total count (scoped)
        $totalRecords = $usageModel->join('treatments', 'treatments.id = inventory_usage.treatment_id', 'left')->where('treatments.clinic_id', $clinicId)->countAllResults(false);
        
        // Get filtered count
        $filteredRecords = $query->countAllResults(false);
        
        // Get data
        $data = $query->orderBy($orderColumnName, $orderDir)
            ->limit($length, $start)
            ->findAll();
        
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
            
            $formattedData[] = [
                $this->formatUsageDate($usage['usage_date'], $usage['created_at']),
                $usage['first_name'] . ' ' . $usage['last_name'],
                $usage['treatment_name'] . '<br><small class="text-gray-500">' . $usage['treatment_id'] . '</small>',
                $itemsText,
                '<span class="font-bold text-green-600">$' . number_format($usage['total_cost'], 2) . '</span>',
                $usage['recorded_by_name'],
                $this->getUsageActionButtons($usage['id'])
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
                'id',
                'item_name',
                'category',
                'quantity',
                'min_quantity',
                'unit_price',
                'supplier',
                'expiry_date'
            ];
            
            $orderColumnName = $columns[$orderColumn] ?? 'id';
            
            // Get total records count for low stock items (scoped)
            // Use model builder to avoid raw query
            $totalRecords = $this->inventoryModel->where('clinic_id', $clinicId)->where('quantity <=', 'min_quantity')->countAllResults();
            
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
            $builder = $this->inventoryModel->where('clinic_id', $clinicId)->where('quantity <=', 'min_quantity');
            
            // Apply search filter
            if (!empty($searchValue)) {
                $builder->groupStart()
                    ->like('item_name', $searchValue)
                    ->orLike('category', $searchValue)
                    ->orLike('supplier', $searchValue)
                    ->groupEnd();
            }
            
            // Get filtered count
            $filteredRecords = $builder->countAllResults(false);
            
            // Get data with ordering and pagination
            $items = $builder->orderBy($orderColumnName, $orderDir)
                ->limit($length, $start)
                ->findAll();
            
            // Format data for DataTables (array format)
            $data = [];
            foreach ($items as $item) {
                $stockLevel = $this->getStockLevel($item['quantity'], $item['min_quantity']);
                
                $data[] = [
                    intval($item['id'] ?? 0), // ID (hidden)
                    $item['item_name'] ?? '', // Item Name
                    $item['category'] ?? '', // Category
                    '<span class="' . $stockLevel['class'] . '">' . $item['quantity'] . '</span>', // Current Stock
                    $item['min_quantity'] ?? 0, // Minimum Required
                    '$' . number_format($item['unit_price'] ?? 0, 2), // Unit Price
                    $item['supplier'] ?? '', // Supplier
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
            'active' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>',
            'inactive' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>',
            'expired' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Expired</span>'
        ];
        return $badges[$status] ?? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
    }

    private function getActionButtons($id)
    {
        return '
            <div class="flex space-x-2">
                <a href="' . base_url() . 'inventory/' . $id . '" class="text-blue-600 hover:text-blue-900" title="View Details">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="' . base_url() . 'inventory/' . $id . '/edit" class="text-green-600 hover:text-green-900" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                <button onclick="confirmDelete(\''. base_url() . 'inventory/' . $id . '\', \'Are you sure you want to delete this inventory item? This action cannot be undone.\')" class="text-red-600 hover:text-red-900" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        ';
    }

    private function getUsageActionButtons($id)
    {
        return '
            <div class="flex space-x-2">
                <button onclick="viewUsageDetails(' . $id . ')" class="text-blue-600 hover:text-blue-900" title="View Details">
                    <i class="fas fa-eye"></i>
                </button>
                <button onclick="printUsageRecord(' . $id . ')" class="text-green-600 hover:text-green-900" title="Print Record">
                    <i class="fas fa-print"></i>
                </button>
            </div>
        ';
    }

    private function getLowStockActionButtons($id)
    {
        return '
            <div class="flex space-x-2">
                <a href="' . base_url() . 'inventory/' . $id . '" class="text-blue-600 hover:text-blue-900" title="View Details">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="' . base_url() . 'inventory/' . $id . '/edit" class="text-green-600 hover:text-green-900" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                <button onclick="adjustStock(' . $id . ')" class="text-purple-600 hover:text-purple-900" title="Adjust Stock">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        ';
    }

    private function formatUsageDate($usageDate, $createdAt)
    {
        return '
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-lg">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">' . date('M j, Y', strtotime($usageDate)) . '</div>
                    <div class="text-sm text-gray-500">' . date('g:i A', strtotime($createdAt)) . '</div>
                </div>
            </div>
        ';
    }

    private function getStockLevel($quantity, $minQuantity)
    {
        if ($quantity <= 0) {
            return ['class' => 'text-red-600 font-bold', 'level' => 'Out of Stock'];
        } elseif ($quantity <= $minQuantity) {
            return ['class' => 'text-red-600 font-bold', 'level' => 'Critical'];
        } elseif ($quantity <= ($minQuantity * 1.5)) {
            return ['class' => 'text-yellow-600 font-bold', 'level' => 'Low'];
        } else {
            return ['class' => 'text-green-600 font-bold', 'level' => 'Good'];
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
}