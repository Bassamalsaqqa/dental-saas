<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Guard enforcing history preservation and tenant isolation on expense entries.
 * Shared by both the audited API mutation service and model lifecycle callbacks.
 * Contract: Caller must already own an active transaction and parent row lock.
 */
class ExpenseHistoryGuard
{
    private BaseConnection $db;
    private array $tableExistsCache = [];

    public function __construct(BaseConnection $db)
    {
        $this->db = $db;
    }

    private function table(string $name): string
    {
        return $this->db->escapeIdentifiers($this->db->prefixTable($name));
    }

    private function hasTable(string $table): bool
    {
        return $this->tableExistsCache[$table] ??= $this->db->tableExists($table);
    }

    private function hasLinkedRow(string $table, string $column, int $clinicId, int $financeId): bool
    {
        if (!$this->hasTable($table)) {
            return false;
        }
        $sql = 'SELECT id FROM ' . $this->table($table) . ' WHERE clinic_id = ? AND ' . $this->db->escapeIdentifiers($column) . ' = ? ORDER BY id LIMIT 1 FOR UPDATE';
        return $this->db->query($sql, [$clinicId, $financeId])->getRowArray() !== null;
    }

    private function hasLinkedAttachment(int $clinicId, int $financeId): bool
    {
        if (!$this->hasTable('file_attachments')) {
            return false;
        }
        $sql = 'SELECT id FROM ' . $this->table('file_attachments') . ' WHERE clinic_id = ? AND entity_type = \'finance\' AND entity_id = ? ORDER BY id LIMIT 1 FOR UPDATE';
        return $this->db->query($sql, [$clinicId, $financeId])->getRowArray() !== null;
    }

    /**
     * Asserts whether the proposed field mutations are permitted for an expense entry.
     *
     * @param int $clinicId Active clinic scope
     * @param array $lockedExpense Persisted, pre-locked database row of the finance entry
     * @param array $payload Proposed mutation fields
     * @throws \DomainException|\RuntimeException
     */
    public function assertChangedFieldsAllowed(int $clinicId, array $lockedExpense, array $payload): void
    {
        // 1. Fail closed on invalid transaction boundary
        if ((int) $this->db->transDepth < 1 || !$this->db->transStatus()) {
            throw new \RuntimeException('ExpenseHistoryGuard requires caller to own an active database transaction.');
        }

        // 2. Fail closed on missing/invalid expense ID
        $id = (int) ($lockedExpense['id'] ?? 0);
        if ($id <= 0) {
            throw new \DomainException('Invalid expense ID.');
        }

        // 3. Fail closed on clinic mismatch
        $recordClinicId = (int) ($lockedExpense['clinic_id'] ?? 0);
        if ($clinicId <= 0 || $recordClinicId <= 0 || $recordClinicId !== $clinicId) {
            throw new \DomainException('Tenant context mismatch or missing clinic ID.');
        }

        // 4. Fail closed on non-expense
        $type = (string) ($lockedExpense['transaction_type'] ?? '');
        if ($type !== 'expense') {
            throw new \DomainException('This entry has financial or payment status and cannot be modified. An authorized audited correction is required.');
        }

        // 5. Compare changed canonical history fields via InvoiceLifecyclePolicy::sameValue
        $hasActualHistoryChange = false;
        foreach (InvoiceLifecyclePolicy::EXPENSE_HISTORY_SENSITIVE_FIELDS as $field) {
            if (array_key_exists($field, $payload)) {
                if (!InvoiceLifecyclePolicy::sameValue($field, $payload[$field], $lockedExpense[$field] ?? null)) {
                    $hasActualHistoryChange = true;
                    break;
                }
            }
        }

        // Harmless return for identical paid/linked roundtrips (or payloads without history changes)
        if (!$hasActualHistoryChange) {
            return;
        }

        // 6. Require pending status
        $status = (string) ($lockedExpense['payment_status'] ?? '');
        if ($status !== 'pending') {
            throw new \DomainException('This entry has financial or payment status and cannot be modified. An authorized audited correction is required.');
        }

        // 7. Require amount_paid zero and no paid_date
        $cachedPaid = round((float) ($lockedExpense['amount_paid'] ?? 0), 2);
        if ($cachedPaid != 0.0 || !empty($lockedExpense['paid_date'])) {
            throw new \DomainException('This entry has payment history and cannot be modified.');
        }

        // 8. Require no source_entity_type/id/examination link
        if (!empty($lockedExpense['source_entity_type']) || !empty($lockedExpense['source_entity_id']) || !empty($lockedExpense['examination_id'])) {
            throw new \DomainException('This entry is linked to clinical history and cannot be modified.');
        }

        // 9. Scoped child locking reads exact finance_id and primary ID / order limit
        if ($this->hasLinkedRow('finance_payments', 'finance_id', $clinicId, $id)) {
            throw new \DomainException('This entry has linked payments and cannot be modified.');
        }
        if ($this->hasLinkedRow('provider_payouts', 'finance_id', $clinicId, $id)) {
            throw new \DomainException('This entry has linked provider payouts and cannot be modified.');
        }
        if ($this->hasLinkedRow('clinical_charge_lines', 'finance_id', $clinicId, $id)) {
            throw new \DomainException('This entry has linked clinical charges and cannot be modified.');
        }
        if ($this->hasLinkedRow('finance_liquidity_transfers', 'related_finance_id', $clinicId, $id)) {
            throw new \DomainException('This entry has linked liquidity transfers and cannot be modified.');
        }
        if ($this->hasLinkedAttachment($clinicId, $id)) {
            throw new \DomainException('This entry has attached files and cannot be modified.');
        }
    }
}
