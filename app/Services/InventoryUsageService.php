<?php

namespace App\Services;

use App\Models\InventoryModel;
use App\Models\InventoryUsageModel;
use App\Models\PatientModel;
use App\Models\TreatmentModel;
use Config\Database;

class InventoryUsageService
{
    private InventoryModel $inventoryModel;
    private InventoryUsageModel $usageModel;
    private TreatmentModel $treatmentModel;
    private PatientModel $patientModel;
    private InventoryMovementService $movementService;
    private FinanceLedgerService $financeLedgerService;
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->inventoryModel = new InventoryModel();
        $this->usageModel = new InventoryUsageModel();
        $this->treatmentModel = new TreatmentModel();
        $this->patientModel = new PatientModel();
        $this->movementService = new InventoryMovementService();
        $this->financeLedgerService = new FinanceLedgerService();
        $this->db = Database::connect();
    }

    public function record(
        int $clinicId,
        array $payload,
        ?int $actorUserId,
        array $options = []
    ): array {
        if ($clinicId <= 0) {
            return ['success' => false, 'message' => 'Clinic context is required.'];
        }

        $usageDate = (string) ($payload['usage_date'] ?? '');
        $notes = trim((string) ($payload['notes'] ?? ''));
        $treatmentId = !empty($payload['treatment_id']) ? (int) $payload['treatment_id'] : null;
        $items = $this->normalizeItems($payload['items'] ?? []);
        $movementNote = trim((string) ($options['movement_note'] ?? 'Inventory usage recorded'));

        if ($usageDate === '') {
            return ['success' => false, 'message' => 'Usage date is required.'];
        }

        if (empty($items)) {
            return ['success' => false, 'message' => 'Please select at least one inventory item to record usage.'];
        }

        $treatment = null;
        if ($treatmentId) {
            $treatment = $this->treatmentModel->where('clinic_id', $clinicId)->find($treatmentId);
            if (!$treatment) {
                return ['success' => false, 'message' => 'Treatment not found in this clinic.'];
            }
        }

        $usageData = [];
        $totalCost = 0.0;
        foreach ($items as $item) {
            $inventoryItem = $this->inventoryModel->where('clinic_id', $clinicId)->find($item['item_id']);
            if (!$inventoryItem) {
                return ['success' => false, 'message' => 'Invalid inventory item selected.'];
            }

            if ((int) ($inventoryItem['quantity'] ?? 0) < $item['quantity']) {
                return [
                    'success' => false,
                    'message' => "Insufficient stock for {$inventoryItem['item_name']}. Available: {$inventoryItem['quantity']}, Requested: {$item['quantity']}",
                ];
            }

            $lineTotal = round((float) ($inventoryItem['unit_price'] ?? 0) * $item['quantity'], 2);
            $usageData[] = [
                'item_id' => (int) $item['item_id'],
                'item_name' => (string) $inventoryItem['item_name'],
                'quantity_used' => (int) $item['quantity'],
                'unit_cost' => (float) ($inventoryItem['unit_price'] ?? 0),
                'total_cost' => $lineTotal,
            ];
            $totalCost += $lineTotal;
        }
        $totalCost = round($totalCost, 2);

        $usageId = 0;
        $this->db->transStart();

        $usageInsert = [
            'clinic_id' => $clinicId,
            'treatment_id' => $treatmentId,
            'usage_date' => $usageDate,
            'items_used' => json_encode($usageData),
            'total_cost' => $totalCost,
            'notes' => $notes !== '' ? $notes : null,
            'recorded_by' => $actorUserId ?: 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if (!$this->usageModel->insert($usageInsert)) {
            $this->db->transRollback();
            return ['success' => false, 'message' => 'Failed to record usage: ' . implode(', ', $this->usageModel->errors())];
        }

        $usageId = (int) $this->usageModel->getInsertID();

        foreach ($usageData as $usage) {
            $inventoryRow = $this->inventoryModel->where('clinic_id', $clinicId)->find((int) $usage['item_id']);
            if (!$inventoryRow) {
                $this->db->transRollback();
                return ['success' => false, 'message' => 'Inventory item disappeared during usage recording.'];
            }

            $beforeQty = (int) ($inventoryRow['quantity'] ?? 0);
            $afterQty = $beforeQty - (int) $usage['quantity_used'];
            if ($afterQty < 0) {
                $this->db->transRollback();
                return ['success' => false, 'message' => "Insufficient stock for {$inventoryRow['item_name']}."];
            }

            if (!$this->inventoryModel->update((int) $usage['item_id'], [
                'quantity' => $afterQty,
                'updated_at' => date('Y-m-d H:i:s'),
            ])) {
                $this->db->transRollback();
                return ['success' => false, 'message' => 'Failed to update inventory balances.'];
            }

            try {
                $this->movementService->record(
                    $clinicId,
                    (int) $usage['item_id'],
                    'usage_consume',
                    (int) -$usage['quantity_used'],
                    $beforeQty,
                    $afterQty,
                    isset($usage['unit_cost']) ? (float) $usage['unit_cost'] : null,
                    'inventory_usage',
                    $usageId,
                    $movementNote,
                    $actorUserId
                );
            } catch (\Throwable $e) {
                $this->db->transRollback();
                return ['success' => false, 'message' => 'Failed to record stock movement.'];
            }
        }

        $this->db->transComplete();
        if (!$this->db->transStatus()) {
            return ['success' => false, 'message' => 'Inventory usage transaction failed.'];
        }

        $patientId = null;
        $patientName = null;
        $treatmentLabel = null;
        if ($treatment) {
            $patientId = !empty($treatment['patient_id']) ? (int) $treatment['patient_id'] : null;
            $treatmentLabel = (string) ($treatment['treatment_id'] ?? ('#' . $treatment['id']));
            if ($patientId) {
                $patient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
                if ($patient) {
                    $patientName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''));
                }
            }
        }

        if ((int) settings()->get('finance_auto_post_inventory_cogs', 1) === 1) {
            try {
                $this->financeLedgerService->upsertInventoryUsageCogsExpense(
                    $clinicId,
                    $usageId,
                    [
                        'treatment_id' => $treatmentId,
                        'usage_date' => $usageDate,
                        'items_used' => json_encode($usageData),
                        'total_cost' => $totalCost,
                        'notes' => $notes,
                    ],
                    $patientId,
                    $actorUserId,
                    [
                        'patient_name' => $patientName,
                        'treatment_label' => $treatmentLabel,
                    ]
                );
            } catch (\Throwable $financePostError) {
                log_message('error', 'Inventory usage finance posting failed: ' . $financePostError->getMessage());
            }
        }

        return [
            'success' => true,
            'usage_id' => $usageId,
            'usage_data' => $usageData,
            'total_cost' => $totalCost,
            'patient_name' => $patientName,
            'treatment_label' => $treatmentLabel,
            'message' => 'Inventory usage recorded.',
        ];
    }

    private function normalizeItems($items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $normalized = [];
        foreach ($items as $item) {
            $itemId = is_array($item) ? (int) ($item['item_id'] ?? 0) : 0;
            $quantity = is_array($item) ? (int) ($item['quantity'] ?? 0) : 0;
            if ($itemId <= 0 || $quantity <= 0) {
                continue;
            }
            $normalized[] = [
                'item_id' => $itemId,
                'quantity' => $quantity,
            ];
        }

        return $normalized;
    }
}
