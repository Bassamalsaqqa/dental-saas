<?php

namespace App\Services;

use App\Models\FinanceModel;
use App\Services\FinanceCreationGuard;

class FinanceLedgerService
{
    private FinanceModel $financeModel;

    public function __construct()
    {
        $this->financeModel = new FinanceModel();
    }

    /**
     * Record (or verify idempotent matching) a finance expense row representing inventory usage COGS.
     * Existing COGS row is immutable; exact semantic payload match returns existing ID unchanged.
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
            'exchange_rate' => 1.0,
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
            'discount_amount' => 0.0,
            'tax_percentage' => 0.0,
            'tax_amount' => 0.0,
            'amount_paid' => $totalCost,
            'balance_due' => 0.0,
            'due_date' => null,
            'paid_date' => $usageDate,
            'notes' => $notes !== '' ? ('Auto-posted from inventory usage. ' . $notes) : 'Auto-posted from inventory usage.',
            'created_by' => $actorUserId ?: null,
        ];

        $db = $this->financeModel->db;
        $hasCallerTx = ((int) $db->transDepth >= 1);
        $ownsTx = false;

        if ($hasCallerTx) {
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Cannot join unhealthy caller transaction.');
            }
        } else {
            if ((int) $db->transDepth !== 0 || $db->transStatus() === false) {
                throw new \RuntimeException('Cannot start COGS transaction in unhealthy context.');
            }
            if (!$db->transBegin()) {
                throw new \RuntimeException('Failed to start COGS transaction.');
            }
            $ownsTx = true;
        }

        try {
            // Reusable parent lock order BEFORE querying or locking finance:
            // Clinic row FOR UPDATE -> Patient row FOR UPDATE (if patientId present)
            $guard = new FinanceCreationGuard($db);
            $activeClinicId = (int) (\App\Libraries\ApiRuntimeContext::getClinicId() ?: session()->get('active_clinic_id') ?: 0);
            $payload = $guard->validateAndLock($payload, $activeClinicId);

            // Lookup source with FOR UPDATE NOWAIT (no waiting on existing finance row after clinic lock)
            $existingQuery = $db->query(
                'SELECT * FROM ' . $db->escapeIdentifiers($db->prefixTable('finances'))
                . ' WHERE clinic_id = ? AND transaction_type = ? AND source_entity_type = ? AND source_entity_id = ? ORDER BY id LIMIT 2 FOR UPDATE NOWAIT',
                [$clinicId, 'expense', 'inventory_usage', $usageId]
            );
            if ($existingQuery === false) {
                $sqlErrorCode = (int) ($db->error()['code'] ?? 0);
                throw new \RuntimeException('COGS source is busy or unavailable. Roll back and retry the operation.', $sqlErrorCode);
            }
            $existingRows = $existingQuery->getResultArray();
            if (count($existingRows) > 1) {
                throw new \DomainException('Multiple COGS source records require explicit review; history was preserved.');
            }
            $existing = $existingRows[0] ?? null;

            if ($existing) {
                // Existing COGS row is immutable. Verify exact semantic payload match.
                $this->assertCogsPayloadMatchesExisting($existing, $payload);
                if ($ownsTx) {
                    if (!$db->transStatus() || !$db->transCommit()) {
                        throw new \RuntimeException('Unable to commit COGS transaction.');
                    }
                    $ownsTx = false;
                }
                return (int) $existing['id'];
            }

            if (!$this->financeModel->insert($payload)) {
                $errors = $this->financeModel->errors();
                throw new \RuntimeException('Failed to post inventory COGS expense: ' . implode(', ', $errors));
            }

            $id = (int) $this->financeModel->getInsertID();

            if ($ownsTx) {
                if (!$db->transStatus() || !$db->transCommit()) {
                    throw new \RuntimeException('Unable to commit inventory COGS expense creation.');
                }
                $ownsTx = false;
            }

            return $id;
        } catch (\Throwable $e) {
            if ($ownsTx && (int) $db->transDepth > 0) {
                $db->transRollback();
            }
            throw $e;
        }
    }

    private function assertCogsPayloadMatchesExisting(array $existing, array $payload): void
    {
        $floatClose = static fn (mixed $a, mixed $b): bool => abs((float) $a - (float) $b) < 0.0001;

        if (!$floatClose($existing['amount'] ?? 0, $payload['amount'] ?? 0)
            || !$floatClose($existing['amount_original'] ?? 0, $payload['amount_original'] ?? 0)
            || (string) ($existing['currency'] ?? '') !== (string) ($payload['currency'] ?? '')
            || !$floatClose($existing['exchange_rate'] ?? 0, $payload['exchange_rate'] ?? 0)
            || !$floatClose($existing['tax_percentage'] ?? 0, $payload['tax_percentage'] ?? 0)
            || !$floatClose($existing['tax_amount'] ?? 0, $payload['tax_amount'] ?? 0)
            || !$floatClose($existing['discount_amount'] ?? 0, $payload['discount_amount'] ?? 0)
            || ((int) ($existing['patient_id'] ?? 0) ?: null) !== ((int) ($payload['patient_id'] ?? 0) ?: null)
            || ((int) ($existing['examination_id'] ?? 0) ?: null) !== ((int) ($payload['examination_id'] ?? 0) ?: null)
            || (string) ($existing['source_entity_type'] ?? '') !== (string) ($payload['source_entity_type'] ?? '')
            || (int) ($existing['source_entity_id'] ?? 0) !== (int) ($payload['source_entity_id'] ?? 0)
            || (string) ($existing['expense_category'] ?? '') !== (string) ($payload['expense_category'] ?? '')
            || trim((string) ($existing['counterparty_name'] ?? '')) !== trim((string) ($payload['counterparty_name'] ?? ''))
            || trim((string) ($existing['description'] ?? '')) !== trim((string) ($payload['description'] ?? ''))
            || (string) ($existing['service_type'] ?? '') !== (string) ($payload['service_type'] ?? '')
            || trim((string) ($existing['notes'] ?? '')) !== trim((string) ($payload['notes'] ?? ''))
            || ((string) ($existing['due_date'] ?? '') ?: null) !== ((string) ($payload['due_date'] ?? '') ?: null)) {
            throw new \DomainException('Existing COGS expense row is immutable; differing payload requires explicit correction.');
        }

        $exDetails = json_decode((string) ($existing['service_details'] ?? ''), true);
        $plDetails = json_decode((string) ($payload['service_details'] ?? ''), true);
        if (is_array($exDetails) && is_array($plDetails)) {
            if ($exDetails !== $plDetails) {
                throw new \DomainException('Existing COGS expense row is immutable; differing payload requires explicit correction.');
            }
        } elseif ((string) ($existing['service_details'] ?? '') !== (string) ($payload['service_details'] ?? '')) {
            throw new \DomainException('Existing COGS expense row is immutable; differing payload requires explicit correction.');
        }
    }

    private function countItems(string $itemsJson): int
    {
        $items = json_decode($itemsJson, true);
        return is_array($items) ? count($items) : 0;
    }
}
