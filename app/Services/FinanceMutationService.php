<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/** Serializes history-sensitive finance mutations without changing the schema. */
class FinanceMutationService
{
    private FinanceTreasuryService $treasury;
    private array $tableExistsCache = [];

    public function __construct(private BaseConnection $db, ?FinanceTreasuryService $treasury = null)
    {
        $this->treasury = $treasury ?? new FinanceTreasuryService();
    }

    private function table(string $name): string
    {
        return $this->db->escapeIdentifiers($this->db->prefixTable($name));
    }

    private function hasTable(string $table): bool
    {
        return $this->tableExistsCache[$table] ??= $this->db->tableExists($table);
    }

    private function begin(): void
    {
        if ((int) $this->db->transDepth !== 0 || $this->db->transStatus() === false || !$this->db->transBegin()) {
            throw new \RuntimeException('Unable to start finance transaction.');
        }
    }

    private function commit(): void
    {
        if ($this->db->transStatus() === false || !$this->db->transCommit()) {
            throw new \RuntimeException('Unable to commit finance transaction.');
        }
    }

    private function audit(int $clinicId, int $actorId, string $action, int $financeId, string $description, array $metadata): void
    {
        if ($actorId <= 0 || !$this->db->table('activity_logs')->insert([
            'clinic_id' => $clinicId,
            'user_id' => $actorId,
            'action' => $action,
            'entity_type' => 'finance',
            'entity_id' => $financeId,
            'description' => $description,
            'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at' => date('Y-m-d H:i:s'),
        ])) {
            throw new \RuntimeException('Unable to record finance audit.');
        }
    }

    public function lockFinance(int $clinicId, int $financeId): ?array
    {
        return $this->db->query('SELECT * FROM ' . $this->table('finances') . ' WHERE clinic_id = ? AND id = ? FOR UPDATE', [$clinicId, $financeId])->getRowArray();
    }

    /** Caller must already own the finance parent lock and transaction. */
    public function lockPayouts(int $clinicId, int $financeId): array
    {
        if ((int) $this->db->transDepth < 1) {
            throw new \RuntimeException('Payout mutation requires an active transaction.');
        }
        return $this->db->query('SELECT * FROM ' . $this->table('provider_payouts') . ' WHERE clinic_id = ? AND finance_id = ? ORDER BY id FOR UPDATE', [$clinicId, $financeId])->getResultArray();
    }

    /** A current locking read, rather than a potentially stale repeatable-read snapshot. */
    public function payoutPayments(int $clinicId, int $payoutId): array
    {
        return $this->db->query('SELECT id, amount FROM ' . $this->table('provider_payout_payments') . ' WHERE clinic_id = ? AND provider_payout_id = ? ORDER BY id FOR UPDATE', [$clinicId, $payoutId])->getResultArray();
    }

    private function hasLinkedRow(string $table, string $column, int $clinicId, int $financeId): bool
    {
        if (!$this->hasTable($table)) {
            return false;
        }
        return $this->db->query('SELECT id FROM ' . $this->table($table) . ' WHERE clinic_id = ? AND ' . $this->db->escapeIdentifiers($column) . ' = ? LIMIT 1 FOR UPDATE', [$clinicId, $financeId])->getRowArray() !== null;
    }

    private function hasLinkedAttachment(int $clinicId, int $financeId): bool
    {
        if (!$this->hasTable('file_attachments')) {
            return false;
        }
        $sql = 'SELECT id FROM ' . $this->table('file_attachments') . ' WHERE clinic_id = ? AND entity_type = \'finance\' AND entity_id = ?';
        $sql .= ' LIMIT 1 FOR UPDATE';
        return $this->db->query($sql, [$clinicId, $financeId])->getRowArray() !== null;
    }

    /** All-or-nothing deletion of genuinely unused records, with a retained audit snapshot. */
    public function deleteUnused(int $clinicId, array $ids, int $actorId): int
    {
        if ($clinicId <= 0 || $actorId <= 0 || $ids === [] || count($ids) > 500) {
            throw new \DomainException('Select between 1 and 500 unused financial entries.');
        }
        foreach ($ids as $id) {
            if (!is_scalar($id) || !preg_match('/^[1-9][0-9]*$/D', (string) $id)) {
                throw new \DomainException('Invalid financial entry selection.');
            }
        }
        $ids = array_values(array_unique(array_map('intval', $ids)));
        sort($ids, SORT_NUMERIC);
        $this->begin();
        try {
            $rows = [];
            foreach ($ids as $id) {
                $row = $this->lockFinance($clinicId, $id);
                if (!$row) {
                    throw new \DomainException('Financial entry not found in this clinic.');
                }
                $rows[] = $row;
            }
            foreach ($rows as $row) {
                $id = (int) $row['id'];
                $type = (string) ($row['transaction_type'] ?? '');
                $status = (string) ($row['payment_status'] ?? '');

                $isDraftInvoice = ($type === 'invoice' && in_array($status, ['pending', 'overdue'], true));
                $isDraftExpense = ($type === 'expense' && $status === 'pending');

                if (!$isDraftInvoice && !$isDraftExpense
                    || (float) ($row['amount_paid'] ?? 0) != 0.0
                    || !empty($row['paid_date'])
                    || !empty($row['source_entity_type'])
                    || !empty($row['source_entity_id'])
                    || !empty($row['examination_id'])
                    || $this->hasLinkedRow('finance_payments', 'finance_id', $clinicId, $id)
                    || $this->hasLinkedRow('provider_payouts', 'finance_id', $clinicId, $id)
                    || $this->hasLinkedRow('clinical_charge_lines', 'finance_id', $clinicId, $id)
                    || $this->hasLinkedRow('finance_liquidity_transfers', 'related_finance_id', $clinicId, $id)
                    || $this->hasLinkedAttachment($clinicId, $id)) {
                    throw new \DomainException('This entry has financial or clinical history and cannot be deleted. Use the existing reversal or correction workflow.');
                }
            }
            foreach ($rows as $row) {
                $id = (int) $row['id'];
                $this->audit($clinicId, $actorId, 'delete', $id, 'Unused financial entry deleted; history preserved in audit.', ['finance_before' => $row]);
                if (!$this->db->table('finances')->where('clinic_id', $clinicId)->where('id', $id)->delete() || $this->db->affectedRows() !== 1) {
                    throw new \RuntimeException('Unable to delete unused financial entry.');
                }
            }
            $this->commit();
            return count($rows);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /** null amount means pay the current remaining balance, never a cached balance. */
    public function recordPayout(
        int $clinicId,
        int $payoutId,
        int $actorId,
        ?float $amount,
        string $method,
        ?string $account,
        string $paidDate,
        string $notes
    ): array {
        if ($clinicId <= 0 || $actorId <= 0) {
            throw new \DomainException('Invalid request context.');
        }

        if ($amount !== null && (!is_finite($amount) || $amount <= 0 || round($amount, 2) <= 0)) {
            throw new \DomainException('Payment amount must be greater than zero.');
        }

        $method = strtolower(trim($method));
        if (!in_array($method, ['cash', 'card', 'bank_transfer', 'check', 'other'], true)) {
            throw new \DomainException('Invalid payout payment method.');
        }

        $account = trim((string) $account);
        if ($account !== '') {
            if (!$this->treasury->isCompatibleLiquidityAccount($method, 'out', $account, 'provider_payout')) {
                throw new \DomainException('Incompatible liquidity account for provider payout payment.');
            }
        } else {
            $account = $this->treasury->normalizeLiquidityAccount($method, 'out', null, 'provider_payout');
            if ($account === null || !$this->treasury->isValidLiquidityAccount($account)) {
                throw new \DomainException('Unable to resolve liquidity account for provider payout payment.');
            }
        }

        $paidDate = trim($paidDate);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $paidDate)
            || !checkdate((int) substr($paidDate, 5, 2), (int) substr($paidDate, 8, 2), (int) substr($paidDate, 0, 4))) {
            throw new \DomainException('Invalid payout payment date.');
        }

        // Discovery is not authority: revalidate under parent -> payout locks below.
        $initial = $this->db->table('provider_payouts')->where('clinic_id', $clinicId)->where('id', $payoutId)->get()->getRowArray();
        if (!$initial) {
            throw new \DomainException('Provider payout not found.');
        }

        $financeId = (int) ($initial['finance_id'] ?? 0);
        if ($financeId <= 0) {
            throw new \DomainException('Provider payout not found.');
        }

        $this->begin();
        try {
            if (!$this->lockFinance($clinicId, $financeId)) {
                throw new \DomainException('Provider payout not found.');
            }

            $payout = null;
            foreach ($this->lockPayouts($clinicId, $financeId) as $row) {
                if ((int) $row['id'] === $payoutId) {
                    $payout = $row;
                    break;
                }
            }

            if (!$payout || ($payout['status'] ?? '') === 'void') {
                throw new \DomainException('Provider payout not found.');
            }

            $payments = $this->payoutPayments($clinicId, $payoutId);
            $ledgerPaid = round(array_sum(array_column($payments, 'amount')), 2);
            $cachedPaid = round((float) ($payout['provider_paid_amount'] ?? 0), 2);
            $share = round((float) ($payout['provider_share_amount'] ?? 0), 2);

            if (!is_finite($ledgerPaid) || !is_finite($cachedPaid) || !is_finite($share) || $ledgerPaid < 0 || $cachedPaid < 0 || $share < 0) {
                throw new \DomainException('Provider payout history requires review.');
            }

            // Guard cached legacy paid amount exceeding ledger so cannot doublepay legacy unledgered payouts.
            $effectivePaid = round(max($ledgerPaid, $cachedPaid), 2);
            if ($effectivePaid > $share) {
                throw new \DomainException('Provider payout history requires review.');
            }

            $remaining = round(max(0, $share - $effectivePaid), 2);

            if ($remaining <= 0 && $amount === null) {
                $this->commit();
                return ['already_paid' => true, 'amount' => 0.0];
            }

            $amount = $amount === null ? $remaining : round($amount, 2);
            if ($amount <= 0 || $amount > $remaining) {
                throw new \DomainException('Payment exceeds provider balance due.');
            }

            if (!$this->db->table('provider_payout_payments')->insert([
                'clinic_id' => $clinicId,
                'provider_payout_id' => $payoutId,
                'amount' => $amount,
                'payment_method' => $method,
                'liquidity_account' => $account,
                'paid_date' => $paidDate,
                'notes' => $notes,
                'created_by' => $actorId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]) || $this->db->affectedRows() !== 1) {
                throw new \RuntimeException('Unable to record provider payment.');
            }

            $newPaid = round($effectivePaid + $amount, 2);
            $balance = round(max(0, $share - $newPaid), 2);
            $newStatus = $balance <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid');

            if (!$this->db->table('provider_payouts')->where('clinic_id', $clinicId)->where('id', $payoutId)->update([
                'provider_paid_amount' => $newPaid,
                'provider_balance_due' => $balance,
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s'),
            ])) {
                throw new \RuntimeException('Unable to update provider payment status.');
            }

            $this->audit($clinicId, $actorId, 'payment', $financeId, 'Provider payout payment recorded.', [
                'provider_payout_id' => $payoutId,
                'amount' => $amount,
                'paid_total' => $newPaid,
                'balance_due' => $balance,
                'status' => $newStatus,
            ]);

            $this->commit();
            return ['already_paid' => false, 'amount' => $amount];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
