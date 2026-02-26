<?php

namespace App\Services;

use App\Models\FinanceModel;

class FinanceLedgerService
{
    private FinanceModel $financeModel;

    public function __construct()
    {
        $this->financeModel = new FinanceModel();
    }

    /**
     * Record (or update) a finance expense row representing inventory usage COGS.
     * This is idempotent per inventory_usage row via source_entity_type/source_entity_id.
     */
    public function upsertInventoryUsageCogsExpense(
        int $clinicId,
        int $usageId,
        array $usageRow,
        ?int $patientId,
        ?int $actorUserId,
        array $context = []
    ): ?int {
        $totalCost = round((float) ($usageRow['total_cost'] ?? 0), 2);
        if ($totalCost <= 0) {
            return null;
        }

        $usageDate = (string) ($usageRow['usage_date'] ?? date('Y-m-d'));
        $notes = trim((string) ($usageRow['notes'] ?? ''));
        $treatmentId = isset($usageRow['treatment_id']) ? (int) $usageRow['treatment_id'] : null;

        $counterparty = 'Inventory Usage';
        if (!empty($context['treatment_label'])) {
            $counterparty = 'Inventory Usage - ' . (string) $context['treatment_label'];
        } elseif ($treatmentId) {
            $counterparty = 'Inventory Usage - Treatment #' . $treatmentId;
        }

        $description = 'COGS from inventory usage';
        if (!empty($context['patient_name'])) {
            $description .= ' for ' . (string) $context['patient_name'];
        }

        $payload = [
            'clinic_id' => $clinicId,
            'patient_id' => $patientId ?: null,
            'examination_id' => null,
            'transaction_type' => 'expense',
            'amount' => $totalCost,
            'amount_original' => $totalCost,
            'exchange_rate' => 1,
            'currency' => settings()->get('currency', 'ILS'),
            'payment_method' => 'other',
            'payment_status' => 'paid',
            'description' => $description,
            'service_type' => 'other',
            'expense_category' => 'inventory_cogs',
            'counterparty_name' => $counterparty,
            'source_entity_type' => 'inventory_usage',
            'source_entity_id' => $usageId,
            'service_details' => json_encode([
                'usage_id' => $usageId,
                'treatment_id' => $treatmentId,
                'items_count' => $this->countItems((string) ($usageRow['items_used'] ?? '[]')),
            ]),
            'discount_amount' => 0,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'amount_paid' => $totalCost,
            'balance_due' => 0,
            'due_date' => null,
            'paid_date' => $usageDate,
            'notes' => $notes !== '' ? ('Auto-posted from inventory usage. ' . $notes) : 'Auto-posted from inventory usage.',
            'created_by' => $actorUserId ?: null,
        ];

        $existing = $this->financeModel
            ->where('clinic_id', $clinicId)
            ->where('transaction_type', 'expense')
            ->where('source_entity_type', 'inventory_usage')
            ->where('source_entity_id', $usageId)
            ->first();

        if ($existing) {
            $this->financeModel->setValidationRules([]);
            $this->financeModel->update((int) $existing['id'], $payload);
            return (int) $existing['id'];
        }

        if (!$this->financeModel->insert($payload)) {
            $errors = $this->financeModel->errors();
            throw new \RuntimeException('Failed to post inventory COGS expense: ' . implode(', ', $errors));
        }

        return (int) $this->financeModel->getInsertID();
    }

    private function countItems(string $itemsJson): int
    {
        $items = json_decode($itemsJson, true);
        return is_array($items) ? count($items) : 0;
    }
}

