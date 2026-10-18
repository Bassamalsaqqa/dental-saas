<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Reusable clinic-scoped checked transaction settlement service.
 * Enforces atomic parent-level row locking in ascending order,
 * current locking ledger reads, strict liquidity account and FX provenance,
 * and legacy cached payment compatibility.
 */
class FinanceSettlementService
{
    private FinanceTreasuryService $treasury;

    public function __construct(private BaseConnection $db, ?FinanceTreasuryService $treasury = null)
    {
        $this->treasury = $treasury ?? new FinanceTreasuryService();
    }

    private function table(string $name): string
    {
        return $this->db->escapeIdentifiers($this->db->prefixTable($name));
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

    protected function audit(int $clinicId, int $actorId, string $action, int $financeId, string $description, array $metadata): void
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
        return $this->db->query(
            'SELECT * FROM ' . $this->table('finances') . ' WHERE clinic_id = ? AND id = ? FOR UPDATE',
            [$clinicId, $financeId]
        )->getRowArray();
    }

    /**
     * Current locking read of ledger entries (never a stale repeatable-read snapshot).
     */
    public function lockSettlementPayments(int $clinicId, int $financeId): array
    {
        return $this->db->query(
            'SELECT id, amount, type, currency, amount_original, exchange_rate, payment_method, liquidity_account, payment_date FROM '
            . $this->table('finance_payments')
            . ' WHERE clinic_id = ? AND finance_id = ? ORDER BY id FOR UPDATE',
            [$clinicId, $financeId]
        )->getResultArray();
    }

    /**
     * Resolves the authoritative current locked ledger state for a parent finance record.
     *
     * @return array{total_amount: float, ledger_settled: float, remaining_base: float, base_settled_before: float, has_ledger: bool}
     * @throws \DomainException on invalid ledger entries or ambiguous unledgered legacy cache
     */
    public function getLockedSettlementState(int $clinicId, array $row): array
    {
        $id = (int) $row['id'];
        $totalAmount = (float) ($row['total_amount'] ?? (
            (float) ($row['amount'] ?? 0) - (float) ($row['discount_amount'] ?? 0) + (float) ($row['tax_amount'] ?? 0)
        ));
        $totalAmount = round($totalAmount, 2);
        if (!is_finite($totalAmount) || $totalAmount < 0) {
            throw new \DomainException('Transaction #' . $id . ' total amount requires review.');
        }

        $cachedPaid = round((float) ($row['amount_paid'] ?? 0), 2);
        $cachedStatus = (string) ($row['payment_status'] ?? 'pending');
        if (!is_finite($cachedPaid) || $cachedPaid < 0) {
            throw new \DomainException('Transaction #' . $id . ' cached paid amount requires review.');
        }

        $payments = $this->lockSettlementPayments($clinicId, $id);

        if (count($payments) > 0) {
            $ledgerSettled = 0.0;
            foreach ($payments as $p) {
                $pAmt = $p['amount'] ?? null;
                if (!is_numeric($pAmt) || !is_finite((float) $pAmt) || (float) $pAmt < 0) {
                    throw new \DomainException('Transaction #' . $id . ' has invalid ledger amount requiring review.');
                }
                $pType = (string) ($p['type'] ?? '');
                if (!in_array($pType, ['payment', 'adjustment', 'refund', 'void'], true)) {
                    throw new \DomainException('Transaction #' . $id . ' has unsupported ledger type requiring review.');
                }

                $amt = (float) $pAmt;
                if ($pType === 'payment' || $pType === 'adjustment') {
                    $ledgerSettled += $amt;
                } else {
                    $ledgerSettled -= $amt;
                }
            }

            $ledgerSettled = round($ledgerSettled, 2);
            if ($ledgerSettled < 0) {
                throw new \DomainException('Transaction #' . $id . ' has net negative ledger settlement requiring review.');
            }
            if ($ledgerSettled > $totalAmount) {
                throw new \DomainException('Transaction #' . $id . ' ledger settlement exceeds total amount requiring review.');
            }

            $remainingBase = round(max(0.0, $totalAmount - $ledgerSettled), 2);

            return [
                'total_amount' => $totalAmount,
                'ledger_settled' => $ledgerSettled,
                'remaining_base' => $remainingBase,
                'base_settled_before' => $ledgerSettled,
                'has_ledger' => true,
            ];
        }

        // No ledger entries exist in finance_payments
        if ($cachedPaid >= $totalAmount && $cachedStatus === 'paid') {
            return [
                'total_amount' => $totalAmount,
                'ledger_settled' => $cachedPaid,
                'remaining_base' => 0.0,
                'base_settled_before' => $cachedPaid,
                'has_ledger' => false,
            ];
        }

        if ($cachedPaid == 0.0 && in_array($cachedStatus, ['pending', 'overdue', 'unpaid'], true)) {
            return [
                'total_amount' => $totalAmount,
                'ledger_settled' => 0.0,
                'remaining_base' => $totalAmount,
                'base_settled_before' => 0.0,
                'has_ledger' => false,
            ];
        }

        throw new \DomainException('Transaction #' . $id . ' has unledgered legacy payment history requiring manual reconciliation.');
    }

    /**
     * Settles selected financial entries atomically in ascending ID order.
     *
     * @param int   $clinicId
     * @param array $ids
     * @param int   $actorId
     * @param array $overrides Optional overrides: payment_method, liquidity_account, currency, exchange_rate, payment_date, notes
     * @return array{settled_count: int, skipped_count: int}
     *
     * @throws \DomainException on validation / business rule violations
     * @throws \RuntimeException on transaction or persistence failures
     */
    public function bulkSettle(int $clinicId, array $ids, int $actorId, array $overrides = []): array
    {
        if ($clinicId <= 0 || $actorId <= 0) {
            throw new \DomainException('Invalid request context.');
        }

        if (empty($ids) || count($ids) > 500) {
            throw new \DomainException('Select between 1 and 500 financial entries.');
        }

        foreach ($ids as $id) {
            if (!is_int($id) && (!is_string($id) || !preg_match('/^[1-9][0-9]*$/D', $id))) {
                throw new \DomainException('Invalid financial entry selection.');
            }
            if ((string) (int) $id !== (string) $id || (int) $id <= 0 || (int) $id > 2147483647) {
                throw new \DomainException('Invalid financial entry selection.');
            }
        }

        $sanitizedIds = array_values(array_unique(array_map('intval', $ids)));
        sort($sanitizedIds, SORT_NUMERIC);

        if (empty($sanitizedIds)) {
            throw new \DomainException('No valid transactions found.');
        }

        // Validate explicit overrides if provided without silent casting
        $methodOverride = null;
        if (array_key_exists('payment_method', $overrides) && $overrides['payment_method'] !== null && $overrides['payment_method'] !== '') {
            if (!is_string($overrides['payment_method'])) {
                throw new \DomainException('Invalid payment method.');
            }
            $methodOverride = strtolower(trim($overrides['payment_method']));
            if (!in_array($methodOverride, ['cash', 'card', 'bank_transfer', 'check', 'insurance', 'other'], true)) {
                throw new \DomainException('Invalid payment method.');
            }
        }

        $accountOverride = null;
        if (array_key_exists('liquidity_account', $overrides) && $overrides['liquidity_account'] !== null && $overrides['liquidity_account'] !== '') {
            if (!is_string($overrides['liquidity_account'])) {
                throw new \DomainException('Invalid liquidity account.');
            }
            $accountOverride = strtolower(trim($overrides['liquidity_account']));
            if (!$this->treasury->isValidLiquidityAccount($accountOverride)) {
                throw new \DomainException('Invalid liquidity account.');
            }
        }

        $currencyOverride = null;
        if (array_key_exists('currency', $overrides) && $overrides['currency'] !== null && $overrides['currency'] !== '') {
            if (!is_string($overrides['currency'])) {
                throw new \DomainException('Invalid settlement currency.');
            }
            $currencyOverride = strtoupper(trim($overrides['currency']));
            if (!in_array($currencyOverride, ['ILS', 'USD', 'EUR', 'GBP', 'JOD'], true)) {
                throw new \DomainException('Invalid settlement currency.');
            }
        }

        $fxOverride = null;
        if (array_key_exists('exchange_rate', $overrides) && $overrides['exchange_rate'] !== null && $overrides['exchange_rate'] !== '') {
            if (!is_numeric($overrides['exchange_rate'])) {
                throw new \DomainException('Exchange rate must be a valid positive number.');
            }
            $fxVal = (float) $overrides['exchange_rate'];
            if (!is_finite($fxVal) || $fxVal <= 0) {
                throw new \DomainException('Exchange rate must be greater than zero.');
            }
            $fxOverride = round($fxVal, 6);
            if ($fxOverride <= 0 || $fxOverride > 999999.999999) {
                throw new \DomainException('Exchange rate out of allowable range.');
            }
        }

        $paymentDate = date('Y-m-d');
        if (array_key_exists('payment_date', $overrides) && $overrides['payment_date'] !== null && $overrides['payment_date'] !== '') {
            if (!is_string($overrides['payment_date'])) {
                throw new \DomainException('Invalid payment date.');
            }
            $paymentDate = trim($overrides['payment_date']);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $paymentDate)
                || !checkdate((int) substr($paymentDate, 5, 2), (int) substr($paymentDate, 8, 2), (int) substr($paymentDate, 0, 4))) {
                throw new \DomainException('Invalid payment date.');
            }
        }

        $notes = 'Bulk mark as paid';
        if (array_key_exists('notes', $overrides) && $overrides['notes'] !== null) {
            if (!is_string($overrides['notes'])) {
                throw new \DomainException('Invalid notes.');
            }
            $trimmedNotes = trim($overrides['notes']);
            if ($trimmedNotes !== '') {
                $notes = $trimmedNotes;
            }
        }

        $this->begin();
        try {
            // Lock ALL selected owned parent rows in ascending ID order before any ledger reads
            $rows = [];
            foreach ($sanitizedIds as $id) {
                $row = $this->lockFinance($clinicId, $id);
                if (!$row) {
                    throw new \DomainException('Financial transaction not found in this clinic.');
                }
                $type = (string) ($row['transaction_type'] ?? '');
                if (!in_array($type, ['invoice', 'expense'], true)) {
                    throw new \DomainException('Only invoices and expenses support settlement tracking.');
                }
                $status = (string) ($row['payment_status'] ?? '');
                if ($status === 'cancelled') {
                    throw new \DomainException('Cannot settle cancelled transaction #' . $id . '.');
                }
                $rows[] = $row;
            }

            $settledCount = 0;
            $skippedCount = 0;
            $baseCurrency = (string) settings()->get('currency', 'ILS');

            foreach ($rows as $row) {
                $id = (int) $row['id'];
                $type = (string) ($row['transaction_type'] ?? 'invoice');
                $direction = ($type === 'expense') ? 'out' : 'in';

                $state = $this->getLockedSettlementState($clinicId, $row);
                $remainingBase = $state['remaining_base'];
                $totalAmount = $state['total_amount'];
                $baseSettledBeforeThis = $state['base_settled_before'];

                // Zero-total clean documents or already settled documents idempotently skip without inserting amount 0 or audit
                if ($remainingBase <= 0.0) {
                    $skippedCount++;
                    continue;
                }

                // Resolve payment method
                $method = $methodOverride ?? strtolower(trim((string) ($row['payment_method'] ?? 'cash')));
                if (!in_array($method, ['cash', 'card', 'bank_transfer', 'check', 'insurance', 'other'], true)) {
                    throw new \DomainException('Unsupported payment method for transaction #' . $id . '.');
                }

                // Resolve liquidity account preserving valid per-document account if compatible
                if ($accountOverride !== null) {
                    $account = $accountOverride;
                    if (!$this->treasury->isCompatibleLiquidityAccount($method, $direction, $account, $type)) {
                        throw new \DomainException('Selected liquidity account does not match payment method on transaction #' . $id . '.');
                    }
                } else {
                    $existingAccount = !empty($row['liquidity_account']) ? strtolower(trim((string) $row['liquidity_account'])) : null;
                    if ($existingAccount !== null && $this->treasury->isCompatibleLiquidityAccount($method, $direction, $existingAccount, $type)) {
                        $account = $existingAccount;
                    } else {
                        $account = $this->treasury->defaultLiquidityAccount($method, $direction, $type);
                    }
                }

                if (!$this->treasury->isValidLiquidityAccount($account)) {
                    throw new \DomainException('Unable to resolve valid liquidity account for transaction #' . $id . '.');
                }

                // Resolve currency and FX provenance
                $docCurrency = strtoupper(trim((string) ($row['currency'] ?? $baseCurrency)));
                $currency = $currencyOverride ?? $docCurrency;
                if (!in_array($currency, ['ILS', 'USD', 'EUR', 'GBP', 'JOD'], true)) {
                    throw new \DomainException('Invalid currency for transaction #' . $id . '.');
                }

                $deltaBase = 0.0;
                if ($currency === $baseCurrency) {
                    $rate = 1.0;
                    $amountBase = $remainingBase;
                    $amountOriginal = $remainingBase;
                } else {
                    if ($currencyOverride !== null && $currencyOverride !== $docCurrency && $fxOverride === null) {
                        throw new \DomainException('Exchange rate is required when changing currency to ' . $currency . ' for transaction #' . $id . '.');
                    }

                    $rawRate = $fxOverride ?? ($row['exchange_rate'] ?? null);
                    if (!is_numeric($rawRate) || !is_finite((float) $rawRate) || (float) $rawRate <= 0) {
                        throw new \DomainException('Exchange rate must be greater than zero for foreign currency transaction #' . $id . '.');
                    }

                    $rate = round((float) $rawRate, 6);
                    if ($rate <= 0 || $rate > 999999.999999) {
                        throw new \DomainException('Exchange rate out of allowable range for transaction #' . $id . '.');
                    }

                    $amountBase = $remainingBase;
                    $amountOriginal = round($remainingBase / $rate, 2);
                    if ($amountOriginal <= 0.0) {
                        throw new \DomainException('Settlement amount in ' . $currency . ' rounds to zero for transaction #' . $id . '.');
                    }
                    if ($amountOriginal > 999999999.99) {
                        throw new \DomainException('Settlement amount in ' . $currency . ' exceeds allowable range for transaction #' . $id . '.');
                    }

                    // Honest bounded rounding policy: verify minor-unit rounding delta
                    $reconciledBase = round($amountOriginal * $rate, 2);
                    $deltaBase = round($reconciledBase - $amountBase, 2);
                    $maxToleratedDelta = max(0.05, round($rate * 0.01 + 0.01, 2));
                    if (abs($deltaBase) > $maxToleratedDelta) {
                        throw new \DomainException('Settlement currency conversion rounding delta exceeds allowable tolerance for transaction #' . $id . '.');
                    }
                }

                // Record ledger row
                $paymentPayload = [
                    'clinic_id' => $clinicId,
                    'finance_id' => $id,
                    'amount' => $amountBase,
                    'currency' => $currency,
                    'amount_original' => $amountOriginal,
                    'exchange_rate' => $rate,
                    'type' => 'payment',
                    'payment_method' => $method,
                    'liquidity_account' => $account,
                    'payment_date' => $paymentDate,
                    'notes' => $notes,
                    'created_by' => $actorId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                if (!$this->db->table('finance_payments')->insert($paymentPayload) || $this->db->affectedRows() !== 1) {
                    throw new \RuntimeException('Failed to record settlement payment for transaction #' . $id . '.');
                }

                // Synchronize parent status
                $newPaid = round($baseSettledBeforeThis + $amountBase, 2);
                $newBalance = round(max(0.0, $totalAmount - $newPaid), 2);
                $newStatus = $newBalance <= 0.0 ? 'paid' : ($newPaid > 0.0 ? 'partial' : 'pending');

                $parentUpdate = [
                    'amount_paid' => $newPaid,
                    'balance_due' => $newBalance,
                    'payment_status' => $newStatus,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                if ($newStatus === 'paid' && empty($row['paid_date'])) {
                    $parentUpdate['paid_date'] = $paymentDate;
                }

                if (!$this->db->table('finances')->where('clinic_id', $clinicId)->where('id', $id)->update($parentUpdate)) {
                    throw new \RuntimeException('Failed to update settlement status for transaction #' . $id . '.');
                }

                // Record checked audit entry with honest rounding delta
                $this->audit($clinicId, $actorId, 'payment', $id, 'Bulk payment of ' . number_format($amountBase, 2) . ' recorded via mark as paid for ' . ($row['transaction_id'] ?? $id), [
                    'amount' => $amountBase,
                    'currency' => $currency,
                    'amount_original' => $amountOriginal,
                    'exchange_rate' => $rate,
                    'rounding_delta_base' => $deltaBase,
                    'payment_method' => $method,
                    'liquidity_account' => $account,
                    'paid_total' => $newPaid,
                    'balance_due' => $newBalance,
                    'payment_status' => $newStatus,
                ]);

                $settledCount++;
            }

            $this->commit();
            return [
                'settled_count' => $settledCount,
                'skipped_count' => $skippedCount,
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
