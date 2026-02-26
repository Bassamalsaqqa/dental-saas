<?php

namespace App\Services;

use App\Models\InventoryStockMovementModel;

class InventoryMovementService
{
    private InventoryStockMovementModel $movementModel;

    public function __construct()
    {
        $this->movementModel = new InventoryStockMovementModel();
    }

    public function record(
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
        ?int $createdBy = null
    ): int {
        $unitCost = $unitCost !== null ? round($unitCost, 2) : null;
        $totalCost = $unitCost !== null ? round(abs($quantityChange) * $unitCost, 2) : null;

        $payload = [
            'clinic_id' => $clinicId,
            'inventory_id' => $inventoryId,
            'movement_type' => $movementType,
            'quantity_change' => $quantityChange,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'created_by' => $createdBy,
        ];

        if (!$this->movementModel->insert($payload)) {
            throw new \RuntimeException('Failed to record inventory movement: ' . implode(', ', $this->movementModel->errors()));
        }

        return (int) $this->movementModel->getInsertID();
    }
}

