<?php

namespace App\Controllers;

use App\Models\InventoryUsageModel;
use App\Models\InventoryModel;
use App\Models\TreatmentModel;
use App\Models\PatientModel;

class InventoryUsage extends BaseController
{
    protected $inventoryUsageModel;
    protected $inventoryModel;
    protected $treatmentModel;
    protected $patientModel;

    public function __construct()
    {
        $this->inventoryUsageModel = new InventoryUsageModel();
        $this->inventoryModel = new InventoryModel();
        $this->treatmentModel = new TreatmentModel();
        $this->patientModel = new PatientModel();
    }

    public function index()
    {
        try {
            $data = [
                'title' => 'Inventory Usage',
                'usage_records' => $this->inventoryUsageModel
                    ->join('inventory', 'inventory.id = inventory_usage.inventory_id')
                    ->join('patients', 'patients.id = inventory_usage.patient_id')
                    ->join('treatments', 'treatments.id = inventory_usage.treatment_id', 'left')
                    ->select('inventory_usage.*, inventory.item_name, inventory.category, patients.first_name, patients.last_name, treatments.treatment_name')
                    ->orderBy('usage_date', 'DESC')
                    ->findAll(),
                'stats' => $this->getUsageStats()
            ];

            return view('inventory_usage/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Inventory usage index error: ' . $e->getMessage());
            
            $data = [
                'title' => 'Inventory Usage',
                'usage_records' => [],
                'stats' => ['total_usage' => 0, 'total_cost' => 0, 'total_quantity' => 0],
                'error' => 'Unable to load usage data. Please check the database connection.'
            ];

            return view('inventory_usage/index', $data);
        }
    }

    public function create()
    {
        $data = [
            'title' => 'Add Inventory Usage',
            'inventory_items' => $this->inventoryModel->where('status', 'active')->orderBy('item_name', 'ASC')->findAll(),
            'treatments' => $this->treatmentModel->where('status', 'active')->orderBy('treatment_name', 'ASC')->findAll(),
            'patients' => $this->patientModel->orderBy('first_name', 'ASC')->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return view('inventory_usage/create', $data);
    }

    public function store()
    {
        $rules = [
            'inventory_id' => 'required|integer',
            'patient_id' => 'required|integer',
            'quantity_used' => 'required|decimal|greater_than[0]',
            'usage_date' => 'required|valid_date',
            'notes' => 'permit_empty|max_length[500]',
            'treatment_id' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Get inventory item details
        $inventoryItem = $this->inventoryModel->find($this->request->getPost('inventory_id'));
        if (!$inventoryItem) {
            return redirect()->back()->withInput()->with('error', 'Inventory item not found.');
        }

        // Check if sufficient stock is available
        $quantityUsed = floatval($this->request->getPost('quantity_used'));
        if ($inventoryItem['quantity'] < $quantityUsed) {
            return redirect()->back()->withInput()->with('error', 'Insufficient stock. Available: ' . $inventoryItem['quantity'] . ' ' . ($inventoryItem['unit'] ?? 'pieces'));
        }

        // Calculate costs
        $unitCost = floatval($inventoryItem['unit_price']);
        $totalCost = $quantityUsed * $unitCost;

        // Prepare usage data
        $usageData = [
            'inventory_id' => $this->request->getPost('inventory_id'),
            'treatment_id' => $this->request->getPost('treatment_id') ?: null,
            'patient_id' => $this->request->getPost('patient_id'),
            'item_name' => $inventoryItem['item_name'],
            'quantity_used' => $quantityUsed,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'usage_date' => $this->request->getPost('usage_date'),
            'used_by' => session()->get('user_id'),
            'notes' => $this->request->getPost('notes') ?: '',
            'status' => 'completed'
        ];

        // Start transaction
        $this->db->transStart();

        try {
            // Insert usage record
            if (!$this->inventoryUsageModel->insert($usageData)) {
                throw new \Exception('Failed to create usage record');
            }

            // Deduct inventory
            if (!$this->inventoryUsageModel->deductInventory($inventoryItem['id'], $quantityUsed)) {
                throw new \Exception('Failed to deduct inventory');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return redirect()->to(base_url('inventory-usage'))->with('success', 'Inventory usage recorded successfully!');

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Inventory usage creation failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to record inventory usage: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $usage = $this->inventoryUsageModel
            ->join('inventory', 'inventory.id = inventory_usage.inventory_id')
            ->join('patients', 'patients.id = inventory_usage.patient_id')
            ->join('treatments', 'treatments.id = inventory_usage.treatment_id', 'left')
            ->select('inventory_usage.*, inventory.item_name, inventory.category, inventory.unit, patients.first_name, patients.last_name, treatments.treatment_name')
            ->where('inventory_usage.id', $id)
            ->first();

        if (!$usage) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Usage record not found');
        }

        $data = [
            'title' => 'Usage Details',
            'usage' => $usage
        ];

        return view('inventory_usage/show', $data);
    }

    public function edit($id)
    {
        $usage = $this->inventoryUsageModel->find($id);
        
        if (!$usage) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Usage record not found');
        }

        $data = [
            'title' => 'Edit Inventory Usage',
            'usage' => $usage,
            'inventory_items' => $this->inventoryModel->where('status', 'active')->orderBy('item_name', 'ASC')->findAll(),
            'treatments' => $this->treatmentModel->where('status', 'active')->orderBy('treatment_name', 'ASC')->findAll(),
            'patients' => $this->patientModel->orderBy('first_name', 'ASC')->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return view('inventory_usage/edit', $data);
    }

    public function update($id)
    {
        $usage = $this->inventoryUsageModel->find($id);
        
        if (!$usage) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Usage record not found');
        }

        $rules = [
            'inventory_id' => 'required|integer',
            'patient_id' => 'required|integer',
            'quantity_used' => 'required|decimal|greater_than[0]',
            'usage_date' => 'required|valid_date',
            'notes' => 'permit_empty|max_length[500]',
            'treatment_id' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Get inventory item details
        $inventoryItem = $this->inventoryModel->find($this->request->getPost('inventory_id'));
        if (!$inventoryItem) {
            return redirect()->back()->withInput()->with('error', 'Inventory item not found.');
        }

        $newQuantityUsed = floatval($this->request->getPost('quantity_used'));
        $oldQuantityUsed = floatval($usage['quantity_used']);
        $quantityDifference = $newQuantityUsed - $oldQuantityUsed;

        // Check if sufficient stock is available for the difference
        if ($quantityDifference > 0 && $inventoryItem['quantity'] < $quantityDifference) {
            return redirect()->back()->withInput()->with('error', 'Insufficient stock. Available: ' . $inventoryItem['quantity'] . ' ' . ($inventoryItem['unit'] ?? 'pieces'));
        }

        // Calculate costs
        $unitCost = floatval($inventoryItem['unit_price']);
        $totalCost = $newQuantityUsed * $unitCost;

        // Prepare update data
        $updateData = [
            'inventory_id' => $this->request->getPost('inventory_id'),
            'treatment_id' => $this->request->getPost('treatment_id') ?: null,
            'patient_id' => $this->request->getPost('patient_id'),
            'item_name' => $inventoryItem['item_name'],
            'quantity_used' => $newQuantityUsed,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'usage_date' => $this->request->getPost('usage_date'),
            'notes' => $this->request->getPost('notes') ?: ''
        ];

        // Start transaction
        $this->db->transStart();

        try {
            // Update usage record
            if (!$this->inventoryUsageModel->update($id, $updateData)) {
                throw new \Exception('Failed to update usage record');
            }

            // Adjust inventory based on quantity difference
            if ($quantityDifference != 0) {
                if ($quantityDifference > 0) {
                    // Deduct additional quantity
                    if (!$this->inventoryUsageModel->deductInventory($inventoryItem['id'], $quantityDifference)) {
                        throw new \Exception('Failed to deduct additional inventory');
                    }
                } else {
                    // Restore quantity
                    if (!$this->inventoryUsageModel->restoreInventory($inventoryItem['id'], abs($quantityDifference))) {
                        throw new \Exception('Failed to restore inventory');
                    }
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return redirect()->to(base_url('inventory-usage'))->with('success', 'Inventory usage updated successfully!');

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Inventory usage update failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to update inventory usage: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $this->response->setContentType('application/json');
            
            $usage = $this->inventoryUsageModel->find($id);
            
            if (!$usage) {
                return $this->response->setJSON(['success' => false, 'message' => 'Usage record not found']);
            }

            // Start transaction
            $this->db->transStart();

            try {
                // Restore inventory
                if (!$this->inventoryUsageModel->restoreInventory($usage['inventory_id'], $usage['quantity_used'])) {
                    throw new \Exception('Failed to restore inventory');
                }

                // Delete usage record
                if (!$this->inventoryUsageModel->delete($id)) {
                    throw new \Exception('Failed to delete usage record');
                }

                $this->db->transComplete();

                if ($this->db->transStatus() === false) {
                    throw new \Exception('Transaction failed');
                }

                return $this->response->setJSON(['success' => true, 'message' => 'Usage record deleted successfully']);

            } catch (\Exception $e) {
                $this->db->transRollback();
                throw $e;
            }

        } catch (\Exception $e) {
            log_message('error', 'Inventory usage delete error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'An error occurred while deleting the usage record']);
        }
    }

    public function getInventoryItem($id)
    {
        $this->response->setContentType('application/json');
        
        $item = $this->inventoryModel->find($id);
        
        if (!$item) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item not found']);
        }

        return $this->response->setJSON([
            'success' => true,
            'item' => [
                'id' => $item['id'],
                'name' => $item['item_name'],
                'category' => $item['category'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?? 'pieces',
                'unit_price' => $item['unit_price']
            ]
        ]);
    }

    private function getUsageStats()
    {
        return $this->inventoryUsageModel->getUsageStats();
    }
}
