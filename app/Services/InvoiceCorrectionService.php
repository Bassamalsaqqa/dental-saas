<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/** Append-only operational corrections. No invoice basis, clinical, or payout mutation. */
class InvoiceCorrectionService
{
    private BaseConnection $db;
    /** Test clock seam only; no request input can set it. */
    public $clock = null;
    private function instant(): \DateTimeImmutable
    {
        $instant = $this->clock ? ($this->clock)() : new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        if (!$instant instanceof \DateTimeImmutable) { throw new \RuntimeException('Invalid operational correction clock.'); }
        return $instant;
    }
    private function businessNow(int $clinic): \DateTimeImmutable
    {
        return $this->instant()->setTimezone((new ClinicSchedulingSettings($this->db))->timezone($clinic));
    }

    public function __construct(?BaseConnection $db = null) { $this->db = $db ?? Database::connect(); }
    private function table(string $table): string { return $this->db->escapeIdentifiers($this->db->prefixTable($table)); }
    private function rows(string $table, array $where, bool $locked = false, ?string $asOf = null, string $date = 'created_at'): array
    {
        $params = []; $clauses = [];
        foreach ($where as $key => $value) { $clauses[] = $this->db->escapeIdentifiers($key) . ' = ?'; $params[] = $value; }
        if ($asOf !== null) { $clauses[] = $this->db->escapeIdentifiers($date) . ' <= ?'; $params[] = $asOf; }
        $query = $this->db->query('SELECT * FROM ' . $this->table($table) . ' WHERE ' . implode(' AND ', $clauses) . ' ORDER BY id' . ($locked ? ' FOR UPDATE NOWAIT' : ''), $params);
        if ($query === false) { throw new \RuntimeException('Financial state is busy or unavailable; roll back and retry.'); }
        return $query->getResultArray();
    }
    private function invoice(int $clinic, int $id, bool $locked = false): array
    {
        if ($clinic <= 0 || $id <= 0) { throw new \DomainException('Invalid invoice context.'); }
        $row = $this->rows('finances', ['clinic_id' => $clinic, 'id' => $id], $locked)[0] ?? null;
        if (!$row || ($row['transaction_type'] ?? '') !== 'invoice' || (int) ($row['patient_id'] ?? 0) <= 0) {
            throw new \DomainException('Invoice is not available in this clinic.');
        }
        return $row;
    }
    public function supportsSchema(): bool
    {
        foreach (['invoice_corrections', 'invoice_correction_lines', 'invoice_credit_applications', 'invoice_correction_approvals', 'invoice_correction_sequences', 'invoice_replacement_links', 'invoice_credit_application_reversals'] as $table) {
            if (!$this->db->tableExists($table)) { return false; }
        }
        return true;
    }
    public function hasEffects(int $clinic, int $invoice): bool
    {
        if (!$this->supportsSchema()) { return false; }
        foreach ([['invoice_corrections','finance_id'],['invoice_credit_applications','source_finance_id'],['invoice_credit_applications','target_finance_id']] as [$table,$column]) {
            if ($this->db->table($table)->select('id')->where('clinic_id',$clinic)->where($column,$invoice)->limit(1)->get()->getRowArray() !== null) { return true; }
        }
        return false;
    }
    public function assertEnabled(): void
    {
        $config = config('InvoiceCorrections');
        if (!$config || $config->enabled !== true || $config->documentMode !== 'operational' || !$this->supportsSchema()) {
            throw new \DomainException('Operational invoice corrections are not enabled.');
        }
    }
    private function authorize(int $clinic, int $actor, string $action): void
    {
        $permissions = new PermissionService($this->db);
        $contextActor = \App\Libraries\ApiRuntimeContext::getUserId();
        if ($contextActor === null) { $contextActor = session()->get('user_id'); }
        if ($actor <= 0 || (int) $contextActor !== $actor || $permissions->resolveCurrentClinicId() !== $clinic) { throw new \DomainException('Invalid authorized financial context.'); }
        $this->assertCurrentRolePermission($clinic, $actor, $action);
    }
    private function assertCurrentRolePermission(int $clinic, int $actor, string $action): void
    {
        $actions = $action === 'edit' ? ['edit', 'update'] : [$action];
        $sql = 'SELECT cu.id,cu.role_id FROM '.$this->table('clinic_users').' cu JOIN '.$this->table('users').' u ON u.id=cu.user_id JOIN '.$this->table('clinics').' c ON c.id=cu.clinic_id JOIN '.$this->table('roles').' r ON r.id=cu.role_id JOIN '.$this->table('role_permissions').' rp ON rp.role_id=r.id JOIN '.$this->table('permissions').' p ON p.id=rp.permission_id WHERE cu.clinic_id=? AND cu.user_id=? AND cu.status=? AND u.active=1 AND c.status=? AND r.is_active=1 AND r.slug<>? AND (r.clinic_id IS NULL OR r.clinic_id=cu.clinic_id) AND rp.granted=1 AND p.module=? AND p.action IN ('.implode(',',array_fill(0,count($actions),'?')).') ORDER BY cu.id,p.id'.((int)$this->db->transDepth>0?' FOR UPDATE NOWAIT':'');
        $q=$this->db->query($sql,array_merge([$clinic,$actor,'active','active','super_admin','finance'],$actions));
        if($q===false || !$q->getRowArray())throw new \DomainException('Current clinic permission is unavailable or financial authority changed.');
    }

    private function begin(): void
    {
        if ((int) $this->db->transDepth !== 0 || !$this->db->transStatus() || !$this->db->transBegin()) { throw new \RuntimeException('Correction requires a healthy owned transaction.'); }
    }
    private function transaction(callable $work): array
    {
        $this->begin();
        try {
            $result = $work();
            if (!$this->db->transStatus() || !$this->db->transCommit()) { throw new \RuntimeException('Unable to commit invoice correction.'); }
            return $result;
        } catch (\Throwable $error) { $this->db->transRollback(); throw $error; }
    }
    /** Child-first writers exist: never block acquiring clinic/parents after finance. */
    private function lockInvoices(int $clinic, array $ids): array
    {
        sort($ids, SORT_NUMERIC); $result = [];
        // Clinic first, followed by sorted invoices using NOWAIT: no lock-cycle wait.
        $q = $this->db->query('SELECT id FROM ' . $this->table('clinics') . ' WHERE id = ? FOR UPDATE NOWAIT', [$clinic]);
        if ($q === false || !$q->getRowArray()) { throw new \DomainException('Clinic is busy or unavailable.'); }
        foreach (array_unique($ids) as $id) {
            $row = $this->invoice($clinic, $id, true);
            foreach (['patients' => 'patient_id', 'examinations' => 'examination_id'] as $table => $field) {
                if (empty($row[$field])) { continue; }
                $parent = $this->rows($table, ['clinic_id' => $clinic, 'id' => (int) $row[$field]], true)[0] ?? null;
                if (!$parent || ($table === 'examinations' && (int) $parent['patient_id'] !== (int) $row['patient_id'])) { throw new \DomainException('Invoice parent is unavailable.'); }
            }
            $result[$id] = $row;
        }
        return $result;
    }
    private function sumRows(array $rows, string $field): int
    {
        $total = 0;
        foreach ($rows as $row) { $total = InvoiceCorrectionMoney::sum($total, InvoiceCorrectionMoney::nonNegative($row[$field])); }
        return $total;
    }
    public function position(int $clinic, int $invoice, bool $locked = false, ?string $asOf = null): array
    {
        if ($locked && ((int) $this->db->transDepth < 1 || !$this->db->transStatus())) { throw new \RuntimeException('Locked position requires a healthy transaction.'); }
        if ($asOf !== null && !preg_match('/^\d{4}-\d{2}-\d{2}(?: \d{2}:\d{2}:\d{2})?$/D', $asOf)) { throw new \DomainException('Invalid position date.'); }
        if ($asOf !== null && strlen($asOf) === 10) { $asOf .= ' 23:59:59'; }
        $row = $this->invoice($clinic, $invoice, $locked);
        $gross = isset($row['total_amount']) ? InvoiceCorrectionMoney::nonNegative($row['total_amount']) : InvoiceCorrectionMoney::sum(InvoiceCorrectionMoney::nonNegative($row['amount']), -InvoiceCorrectionMoney::nonNegative($row['discount_amount'] ?? 0), InvoiceCorrectionMoney::nonNegative($row['tax_amount'] ?? 0));
        $payments = $this->rows('finance_payments', ['clinic_id' => $clinic, 'finance_id' => $invoice], $locked, $asOf, 'payment_date');
        $cash = 0; $adjustments = 0;
        foreach ($payments as $payment) {
            $amount = InvoiceCorrectionMoney::nonNegative($payment['amount']);
            switch ($payment['type']) {
                case 'payment': $cash = InvoiceCorrectionMoney::sum($cash, $amount); break;
                case 'refund': case 'void': $cash = InvoiceCorrectionMoney::sum($cash, -$amount); break;
                case 'adjustment': $adjustments = InvoiceCorrectionMoney::sum($adjustments, $amount); break;
                default: throw new \DomainException('Unsupported settlement history requires review.');
            }
        }
        if ($cash < 0 || InvoiceCorrectionMoney::sum($cash, $adjustments) > $gross) { throw new \DomainException('Inconsistent settlement history requires reconciliation.'); }
        $legacy = false;
        if (!$payments && $asOf === null) {
            $cached = InvoiceCorrectionMoney::nonNegative($row['amount_paid'] ?? 0);
            if ($cached > 0 && $cached >= $gross && ($row['payment_status'] ?? '') === 'paid') { $adjustments = $cached; $legacy = true; }
            elseif ($cached > 0 || !in_array($row['payment_status'] ?? '', ['pending', 'overdue', 'unpaid'], true)) { throw new \DomainException('Unledgered historical settlement requires reconciliation.'); }
        }
        $corrections = $outgoing = $incoming = [];
        if ($this->supportsSchema()) {
            $corrections = $this->rows('invoice_corrections', ['clinic_id' => $clinic, 'finance_id' => $invoice], $locked, $asOf, 'issued_at');
            $outgoing = $this->rows('invoice_credit_applications', ['clinic_id' => $clinic, 'source_finance_id' => $invoice], $locked, $asOf);
            $incoming = $this->rows('invoice_credit_applications', ['clinic_id' => $clinic, 'target_finance_id' => $invoice], $locked, $asOf);
        }
        $outgoing = $this->activeApplications($clinic, $outgoing, $locked, $asOf);
        $incoming = $this->activeApplications($clinic, $incoming, $locked, $asOf);
        $position = InvoiceCorrectionPosition::calculate($gross, $this->sumRows($corrections, 'amount_base'), $cash, $adjustments, $this->sumRows($outgoing, 'amount_base'), $this->sumRows($incoming, 'amount_base'));
        foreach (['total_amount' => 'gross', 'ledger_settled' => 'effective_settlement', 'remaining_base' => 'outstanding', 'base_settled_before' => 'effective_settlement', 'credited_base' => 'credits', 'incoming_base' => 'incoming_allocations', 'outgoing_base' => 'outgoing_allocations', 'patient_credit_base' => 'patient_credit', 'net_cash_base' => 'cash_net'] as $alias => $source) {
            $position[$alias] = $position[$source]; $position[$alias . '_cents'] = $position[$source . '_cents'];
        }
        // Historical amount_paid remains cash plus noncash settlement; applications are separate.
        $position['ledger_settled_cents'] = InvoiceCorrectionMoney::sum($cash, $adjustments);
        $position['ledger_settled'] = InvoiceCorrectionMoney::decimal($position['ledger_settled_cents']);
        $position['base_settled_before_cents'] = $position['ledger_settled_cents'];
        $position['base_settled_before'] = $position['ledger_settled'];
        $position['refundable_base_cents'] = max(0, $cash - $position['outgoing_allocations_cents']);
        $position['refundable_base'] = InvoiceCorrectionMoney::decimal($position['refundable_base_cents']);
        return $position + ['has_ledger' => count($payments) > 0, 'legacy_unproven_cash' => $legacy, 'original' => $row, 'payments' => $payments, 'corrections' => $corrections, 'outgoing' => $outgoing, 'incoming' => $incoming];
    }
    private function payload(array $input): array
    {
        $amount = InvoiceCorrectionMoney::nonNegative($input['amount_original'] ?? null);
        $reason = $input['reason'] ?? null; $key = $input['idempotency_key'] ?? null;
        if ($amount <= 0 || !is_string($reason) || !preg_match('//u', $reason) || trim($reason) === '' || mb_strlen($reason) > 2000 || preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f]/', $reason)
            || !is_string($key) || !preg_match('/^[A-Za-z0-9._:-]{16,100}$/D', $key)) { throw new \DomainException('A positive amount, reason and unique request identity are required.'); }
        $lines = $input['lines'] ?? [];
        if (!is_array($lines) || count($lines) > 200) { throw new \DomainException('Invalid correction lines.'); }
        return ['amount_original' => InvoiceCorrectionMoney::decimal($amount), 'reason' => trim($reason), 'idempotency_key' => $key, 'lines' => $lines];
    }
    private function hash(array $value): string { return hash('sha256', json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION)); }
    private function rate(array $row): string
    {
        $currency = (string) ($row['currency'] ?? ''); $rate = (string) ($row['exchange_rate'] ?? '');
        if (!preg_match('/^[A-Z]{3}$/D', $currency) || !preg_match('/^[0-9]{1,8}(?:\.[0-9]{1,8})?$/D', $rate) || (float) $rate <= 0) { throw new \DomainException('Historical currency or exchange rate requires reconciliation.'); }
        return $rate;
    }
    private function toBase(int $original, string $rate): int
    {
        $parts = explode('.', $rate); $factor = (int) $parts[0] * 100000000 + (int) str_pad($parts[1] ?? '', 8, '0');
        if ($factor <= 0 || $original > intdiv(PHP_INT_MAX - 50000000, $factor)) { throw new \DomainException('Exchange amount exceeds supported exact range.'); }
        $base = intdiv($original * $factor + 50000000, 100000000);
        if ($base <= 0 || $base > InvoiceCorrectionMoney::MAX_CENTS) { throw new \DomainException('Credit is below base precision or outside supported range.'); }
        return $base;
    }
    private function state(int $clinic, int $invoice, bool $locked): array
    {
        $position = $this->position($clinic, $invoice, $locked);
        $providers = $this->db->tableExists('provider_payouts') ? $this->rows('provider_payouts', ['clinic_id' => $clinic, 'finance_id' => $invoice], $locked) : [];
        $payments = [];
        foreach ($providers as $provider) {
            foreach ($this->rows('provider_payout_payments', ['clinic_id' => $clinic, 'provider_payout_id' => $provider['id']], $locked) as $payment) { $payments[] = $payment; }
        }
        $clinical = $this->db->tableExists('clinical_charge_lines') ? $this->rows('clinical_charge_lines', ['clinic_id' => $clinic, 'finance_id' => $invoice], $locked) : [];
        return ['position' => $position, 'providers' => $providers, 'provider_payments' => $payments, 'clinical_lines' => $clinical];
    }
    private function previewState(int $clinic, int $invoice, array $payload, bool $locked): array
    {
        $state = $this->state($clinic, $invoice, $locked); $row = $state['position']['original'];
        $base = $this->toBase(InvoiceCorrectionMoney::cents($payload['amount_original']), $this->rate($row));
        if ($base > $state['position']['gross_cents'] - $state['position']['credits_cents']) { throw new \DomainException('Credit exceeds the original uncredited amount.'); }
        $rateParts = explode('.', $this->rate($row));
        $factor = (int) $rateParts[0] * 100000000 + (int) str_pad($rateParts[1] ?? '', 8, '0');
        $gross = $state['position']['gross_cents'];
        if ($gross > intdiv(PHP_INT_MAX - intdiv($factor, 2), 100000000)) { throw new \DomainException('Historical original amount is outside the exact supported range.'); }
        $grossOriginal = intdiv($gross * 100000000 + intdiv($factor, 2), $factor);
        // This operational gross allocation does not infer a tax rebate or alter historical FX.
        $priorOriginal = $this->sumRows($state['position']['corrections'], 'amount_original');
        if (InvoiceCorrectionMoney::cents($payload['amount_original']) > $grossOriginal - $priorOriginal) { throw new \DomainException('Credit exceeds uncredited original-currency gross.'); }

        $lineOriginal = 0; $lineBase = 0;
        foreach ($payload['lines'] as $line) {
            if (!is_array($line)) { throw new \DomainException('Invalid correction line.'); }
            $original = InvoiceCorrectionMoney::nonNegative($line['amount_original'] ?? null);
            if ($original <= 0 || !is_string($line['description'] ?? null) || trim($line['description']) === '' || mb_strlen($line['description']) > 2000 || !preg_match('//u', $line['description'])) { throw new \DomainException('Correction lines require a description and positive amount.'); }
            $lineOriginal = InvoiceCorrectionMoney::sum($lineOriginal, $original); $lineBase = InvoiceCorrectionMoney::sum($lineBase, $this->toBase($original, $this->rate($row)));
            if (!empty($line['clinical_charge_line_id'])) {
                $matches = array_filter($state['clinical_lines'], static fn ($clinical) => (int) $clinical['id'] === (int) $line['clinical_charge_line_id']);
                if (count($matches) !== 1) { throw new \DomainException('Clinical line does not belong to the original invoice.'); }
            }
        }
        if ($payload['lines'] && ($lineOriginal !== InvoiceCorrectionMoney::cents($payload['amount_original']) || $lineBase !== $base)) { throw new \DomainException('Correction lines do not exactly match the document amount and historical exchange rate.'); }
        $before = $state['position'];
        $projected = InvoiceCorrectionPosition::calculate($before['gross_cents'], InvoiceCorrectionMoney::sum($before['credits_cents'], $base), $before['cash_net_cents'], $before['noncash_adjustments_cents'], $before['outgoing_allocations_cents'], $before['incoming_allocations_cents']);
        return ['projected_position' => $projected, 'invoice' => $row, 'position' => $state['position'], 'source_hash' => $this->hash($state), 'request_hash' => $this->hash($payload), 'provider_impact' => ['allocations' => $state['providers'], 'payments' => $state['provider_payments'], 'policy' => 'preserve_all_existing_obligations'], 'requires_manager_approval' => count($state['providers']) > 0, 'amount_base' => InvoiceCorrectionMoney::decimal($base), 'clinical_lines' => $state['clinical_lines']];
    }
    public function preview(int $clinic, int $invoice, int $actor, array $input): array
    {
        $this->assertEnabled(); $this->authorize($clinic, $actor, 'edit');
        return $this->transaction(function () use ($clinic, $invoice, $input) { $this->lockInvoices($clinic, [$invoice]); $this->authorize($clinic, (int) (\App\Libraries\ApiRuntimeContext::getUserId() ?? session()->get('user_id')), 'edit'); return $this->previewState($clinic, $invoice, $this->payload($input), true); });
    }
    private function insert(string $table, array $row): int
    {
        if (!$this->db->table($table)->insert($row)) { throw new \RuntimeException('Unable to persist correction history.'); }
        return (int) $this->db->insertID();
    }
    private function audit(int $clinic, int $actor, string $action, int $invoice, array $metadata): void
    {
        $this->insert('activity_logs', ['clinic_id' => $clinic, 'user_id' => $actor, 'action' => $action, 'entity_type' => 'finance', 'entity_id' => $invoice, 'description' => 'Operational invoice correction', 'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), 'created_at' => $this->businessNow($clinic)->format('Y-m-d H:i:s')]);
    }
    private function assertSource(array $input, string $actual): void
    {
        if (!is_string($input['source_hash'] ?? null) || !hash_equals($actual, $input['source_hash'])) { throw new \DomainException('Financial state changed. Refresh the preview and obtain any required approval again.'); }
    }
    public function approve(int $clinic, int $invoice, int $actor, array $input): array
    {
        $this->assertEnabled(); $this->authorize($clinic, $actor, 'approve_corrections');
        return $this->transaction(function () use ($clinic, $invoice, $actor, $input) {
            $this->lockInvoices($clinic, [$invoice]); $this->authorize($clinic, $actor, 'approve_corrections'); $preview = $this->previewState($clinic, $invoice, $this->payload($input), true); $this->assertSource($input, $preview['source_hash']);
            if (!$preview['requires_manager_approval']) { throw new \DomainException('This correction does not require provider approval.'); }
            $id = $this->insert('invoice_correction_approvals', ['clinic_id' => $clinic, 'finance_id' => $invoice, 'patient_id' => $preview['invoice']['patient_id'], 'manager_id' => $actor, 'request_hash' => $preview['request_hash'], 'source_hash' => $preview['source_hash'], 'created_at' => $this->businessNow($clinic)->format('Y-m-d H:i:s'), 'expires_at' => $this->instant()->setTimezone(new \DateTimeZone('UTC'))->modify('+15 minutes')->format('Y-m-d H:i:s')]);
            $this->audit($clinic, $actor, 'invoice_correction_approved', $invoice, ['approval_id' => $id, 'source_hash' => $preview['source_hash'], 'request_hash' => $preview['request_hash']]);
            return ['approval_id' => $id, 'source_hash' => $preview['source_hash']];
        });
    }
    private function nextNumber(int $clinic): string
    {
        $year = (int) $this->businessNow($clinic)->format('Y');
        $sql = 'INSERT INTO ' . $this->table('invoice_correction_sequences') . ' (clinic_id, sequence_year, next_value) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE next_value = next_value';
        if ($this->db->query($sql, [$clinic, $year]) === false) { throw new \RuntimeException('Unable to lock operational document sequence.'); }
        $q = $this->db->query('SELECT next_value FROM ' . $this->table('invoice_correction_sequences') . ' WHERE clinic_id = ? AND sequence_year = ? FOR UPDATE', [$clinic, $year]);
        $sequence = $q === false ? null : $q->getRowArray(); $number = (int) ($sequence['next_value'] ?? 0);
        if ($number <= 0 || $number >= PHP_INT_MAX || !$this->db->table('invoice_correction_sequences')->where('clinic_id', $clinic)->where('sequence_year', $year)->update(['next_value' => $number + 1])) { throw new \RuntimeException('Operational document sequence is unavailable.'); }
        return 'OCN-' . $year . '-' . str_pad((string) $number, 8, '0', STR_PAD_LEFT);
    }
    public function issue(int $clinic, int $invoice, int $actor, array $input): array
    {
        $this->assertEnabled(); $this->authorize($clinic, $actor, 'edit'); $this->authorize($clinic, $actor, 'create'); $payload = $this->payload($input);
        return $this->transaction(function () use ($clinic, $invoice, $actor, $input, $payload) {
            $this->lockInvoices($clinic, [$invoice]); $this->authorize($clinic, $actor, 'edit'); $this->authorize($clinic, $actor, 'create');
            $existing = $this->rows('invoice_corrections', ['clinic_id' => $clinic, 'idempotency_key' => $payload['idempotency_key']], true)[0] ?? null;
            if ($existing) {
                if ((int) $existing['finance_id'] !== $invoice || !hash_equals($existing['request_hash'], $this->hash($payload))) { throw new \DomainException('This request identity was already used for a different correction.'); }
                return ['id' => (int) $existing['id'], 'document_number' => $existing['document_number'], 'position' => $this->position($clinic, $invoice, true), 'idempotent' => true];
            }
            $preview = $this->previewState($clinic, $invoice, $payload, true); $this->assertSource($input, $preview['source_hash']); $approvedBy = null; $approvalId = null;
            if ($preview['requires_manager_approval']) {
                $approvalId = filter_var($input['approval_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $approval = $approvalId ? ($this->rows('invoice_correction_approvals', ['clinic_id' => $clinic, 'id' => $approvalId, 'finance_id' => $invoice], true)[0] ?? null) : null;
                if (!$approval || $approval['expires_at'] < $this->instant()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s') || !hash_equals($approval['source_hash'], $preview['source_hash']) || !hash_equals($approval['request_hash'], $preview['request_hash'])) { throw new \DomainException('Current manager approval is required for this provider-linked correction.'); }
                $approvedBy = (int) $approval['manager_id'];
                $this->assertCurrentRolePermission($clinic, $approvedBy, 'approve_corrections');
            }
            $now = $this->businessNow($clinic)->format('Y-m-d H:i:s'); $number = $this->nextNumber($clinic); $row = $preview['invoice'];
            $id = $this->insert('invoice_corrections', ['clinic_id' => $clinic, 'finance_id' => $invoice, 'patient_id' => $row['patient_id'], 'document_number' => $number, 'amount_base' => $preview['amount_base'], 'amount_original' => $payload['amount_original'], 'currency' => $row['currency'], 'exchange_rate' => $this->rate($row), 'reason' => $payload['reason'], 'idempotency_key' => $payload['idempotency_key'], 'request_hash' => $preview['request_hash'], 'source_hash' => $preview['source_hash'], 'snapshot_json' => json_encode(['invoice' => $row, 'provider_impact' => $preview['provider_impact'], 'position_before' => $preview['position'], 'clinical_lines' => $preview['clinical_lines'], 'document_mode' => 'operational', 'tax_notice' => 'Not a validated fiscal credit note'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), 'document_mode' => 'operational', 'created_by' => $actor, 'approved_by' => $approvedBy, 'approval_id' => $approvalId, 'issued_at' => $now, 'created_at' => $now]);
            $lines = $payload['lines'] ?: [['description' => $payload['reason'], 'amount_original' => $payload['amount_original']]];
            foreach ($lines as $line) { $this->insert('invoice_correction_lines', ['clinic_id' => $clinic, 'correction_id' => $id, 'clinical_charge_line_id' => empty($line['clinical_charge_line_id']) ? null : (int) $line['clinical_charge_line_id'], 'description' => trim($line['description']), 'amount_original' => InvoiceCorrectionMoney::decimal(InvoiceCorrectionMoney::cents($line['amount_original'])), 'amount_base' => InvoiceCorrectionMoney::decimal($this->toBase(InvoiceCorrectionMoney::cents($line['amount_original']), $this->rate($row))), 'tax_base' => '0.00', 'tax_original' => '0.00', 'created_at' => $now]); }
            $this->audit($clinic, $actor, 'invoice_correction_issued', $invoice, ['correction_id' => $id, 'document_number' => $number, 'request_hash' => $preview['request_hash'], 'provider_obligations_preserved' => true]);
            return ['id' => $id, 'document_number' => $number, 'position' => $this->position($clinic, $invoice, true), 'idempotent' => false];
        });
    }
    public function listForInvoice(int $clinic, int $invoice): array
    {
        $this->invoice($clinic, $invoice); return $this->supportsSchema() ? $this->rows('invoice_corrections', ['clinic_id' => $clinic, 'finance_id' => $invoice]) : [];
    }
    public function getDocument(int $clinic, int $correction): array
    {
        $row = $this->supportsSchema() ? ($this->rows('invoice_corrections', ['clinic_id' => $clinic, 'id' => $correction])[0] ?? null) : null;
        if (!$row) { throw new \DomainException('Correction document not available.'); }
        $this->invoice($clinic, (int) $row['finance_id']);
        $lines = $this->rows('invoice_correction_lines', ['clinic_id' => $clinic, 'correction_id' => $correction]);
        $snapshot = json_decode($row['snapshot_json'], true, 512, JSON_THROW_ON_ERROR);
        $state = $this->state($clinic, (int) $row['finance_id'], false);
        $applications = $this->rows('invoice_credit_applications', ['clinic_id' => $clinic, 'correction_id' => $correction]);
        foreach ($applications as &$application) { $application['return_history'] = $this->rows('invoice_credit_application_reversals', ['clinic_id' => $clinic, 'application_id' => (int) $application['id']]); }
        unset($application);
        return array_merge($row, ['correction' => $row, 'lines' => $lines, 'snapshot' => $snapshot,
            'invoice' => $snapshot['invoice'], 'position' => $state['position'], 'source_hash' => $this->hash($state),
            'applications' => $applications]);
    }
    public function linkReplacement(int $clinic, int $source, int $target, int $actor): array
    {
        $this->assertEnabled(); $this->authorize($clinic, $actor, 'edit'); $this->authorize($clinic, $actor, 'create');
        if ($source === $target) { throw new \DomainException('A replacement must be a separate invoice.'); }
        return $this->transaction(function () use ($clinic, $source, $target, $actor) {
            $rows = $this->lockInvoices($clinic, [$source, $target]); $this->authorize($clinic, $actor, 'edit'); $this->authorize($clinic, $actor, 'create'); $this->assertMatching($rows[$source], $rows[$target]);
            $existing = $this->rows('invoice_replacement_links', ['clinic_id' => $clinic, 'target_finance_id' => $target], true)[0] ?? null;
            if ($existing) { if ((int) $existing['source_finance_id'] !== $source) { throw new \DomainException('Replacement already belongs to another original invoice.'); } return $existing; }
            $id = $this->insert('invoice_replacement_links', ['clinic_id' => $clinic, 'source_finance_id' => $source, 'target_finance_id' => $target, 'patient_id' => $rows[$source]['patient_id'], 'currency' => $rows[$source]['currency'], 'created_by' => $actor, 'created_at' => $this->businessNow($clinic)->format('Y-m-d H:i:s')]);
            $this->audit($clinic, $actor, 'invoice_replacement_linked', $source, ['replacement_link_id' => $id, 'target_finance_id' => $target]);
            return ['id' => $id, 'target_finance_id' => $target];
        });
    }
    private function assertMatching(array $source, array $target): void
    {
        if ((int) $source['clinic_id'] !== (int) $target['clinic_id'] || (int) $source['patient_id'] !== (int) $target['patient_id'] || ($source['currency'] ?? '') !== ($target['currency'] ?? '')) { throw new \DomainException('Replacement must belong to the same clinic, patient and currency.'); }
        if ($this->rate($source) !== $this->rate($target) && (float) $this->rate($source) !== (float) $this->rate($target)) { throw new \DomainException('Different historical exchange rates require a separately approved exchange workflow.'); }
    }
    public function allocate(int $clinic, int $correctionId, int $actor, array $input): array
    {
        $this->assertEnabled(); $this->authorize($clinic, $actor, 'edit'); $this->authorize($clinic, $actor, 'create');
        $correction = $this->getDocument($clinic, $correctionId); $source = (int) $correction['finance_id'];
        $target = filter_var($input['target_finance_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $amount = InvoiceCorrectionMoney::nonNegative($input['amount_original'] ?? null); $key = $input['idempotency_key'] ?? null;
        if (!$target || $source === $target || $amount <= 0 || !is_string($key) || !preg_match('/^[A-Za-z0-9._:-]{16,100}$/D', $key)) { throw new \DomainException('A valid replacement, positive amount and unique application identity are required.'); }
        $payload = ['correction_id' => $correctionId, 'target_finance_id' => $target, 'amount_original' => InvoiceCorrectionMoney::decimal($amount), 'idempotency_key' => $key]; $hash = $this->hash($payload);
        return $this->transaction(function () use ($clinic, $source, $target, $actor, $input, $key, $amount, $hash, $correctionId) {
            $rows = $this->lockInvoices($clinic, [$source, $target]); $this->authorize($clinic, $actor, 'edit'); $this->authorize($clinic, $actor, 'create'); $this->assertMatching($rows[$source], $rows[$target]);
            $existing = $this->rows('invoice_credit_applications', ['clinic_id' => $clinic, 'idempotency_key' => $key], true)[0] ?? null;
            if ($existing) { if (!hash_equals($existing['request_hash'], $hash)) { throw new \DomainException('Application request identity was already used.'); } return $existing + ['idempotent' => true]; }
            $correction = $this->getDocument($clinic, $correctionId);
            $sourceState = $this->state($clinic, $source, true); $targetPosition = $this->position($clinic, $target, true);
            $this->assertSource($input, $this->hash($sourceState));
            $links = $this->rows('invoice_replacement_links', ['clinic_id' => $clinic, 'source_finance_id' => $source, 'target_finance_id' => $target], true);
            if (count($links) !== 1) { throw new \DomainException('Explicit replacement provenance is required before applying credit.'); }
            $base = $this->toBase($amount, $this->rate($rows[$source]));
            $used = $this->activeApplications($clinic, $this->rows('invoice_credit_applications', ['clinic_id' => $clinic, 'correction_id' => $correctionId], true), true);
            if ($base > $sourceState['position']['refundable_base_cents']) { throw new \DomainException('Incoming credit funding must be explicitly returned before onward application.'); }
            if ($base > $sourceState['position']['patient_credit_base_cents'] || $base > $targetPosition['remaining_base_cents'] || $amount > InvoiceCorrectionMoney::cents($correction['amount_original']) - $this->sumRows($used, 'amount_original') || $base > InvoiceCorrectionMoney::cents($correction['amount_base']) - $this->sumRows($used, 'amount_base')) { throw new \DomainException('Application exceeds available cash-backed credit or replacement balance.'); }
            $id = $this->insert('invoice_credit_applications', ['clinic_id' => $clinic, 'correction_id' => $correctionId, 'source_finance_id' => $source, 'target_finance_id' => $target, 'patient_id' => $rows[$source]['patient_id'], 'currency' => $rows[$source]['currency'], 'amount_base' => InvoiceCorrectionMoney::decimal($base), 'amount_original' => InvoiceCorrectionMoney::decimal($amount), 'exchange_rate' => $this->rate($rows[$source]), 'idempotency_key' => $key, 'request_hash' => $hash, 'created_by' => $actor, 'created_at' => $this->businessNow($clinic)->format('Y-m-d H:i:s')]);
            $this->audit($clinic, $actor, 'invoice_credit_applied', $source, ['application_id' => $id, 'target_finance_id' => $target, 'amount_base' => InvoiceCorrectionMoney::decimal($base)]);
            return ['id' => $id, 'position' => $this->position($clinic, $source, true), 'target_position' => $this->position($clinic, $target, true), 'idempotent' => false];
        });
    }
    /** Immutable reversals affect application availability only from their recorded date. */
    private function activeApplications(int $clinic, array $applications, bool $locked = false, ?string $asOf = null): array
    {
        $active = [];
        foreach ($applications as $application) {
            $reversals = $this->rows('invoice_credit_application_reversals', ['clinic_id' => $clinic, 'application_id' => (int) $application['id']], $locked, $asOf);
            if (count($reversals) > 1) { throw new \DomainException('Ambiguous application reversal history.'); }
            if (!$reversals) { $active[] = $application; }
        }
        return $active;
    }
    public function reverseApplication(int $clinic, int $applicationId, int $actor, array $input): array
    {
        $this->assertEnabled(); $this->authorize($clinic, $actor, 'edit'); $this->authorize($clinic, $actor, 'create');
        $key = $input['idempotency_key'] ?? ''; $reason = $input['reason'] ?? '';
        if (!is_string($key) || !preg_match('/^[A-Za-z0-9._:-]{16,100}$/D', $key) || !is_string($reason) || !preg_match('//u', $reason) || trim($reason) === '' || mb_strlen($reason) > 2000) { throw new \DomainException('Explicit return reason and request identity required.'); }
        $application = $this->rows('invoice_credit_applications', ['clinic_id' => $clinic, 'id' => $applicationId])[0] ?? null;
        if (!$application) { throw new \DomainException('Application not found in clinic.'); }
        $source = (int) $application['source_finance_id']; $target = (int) $application['target_finance_id'];
        $hash = $this->hash(['application_id' => $applicationId, 'reason' => trim($reason), 'idempotency_key' => $key]);
        return $this->transaction(function () use ($clinic, $source, $target, $actor, $key, $reason, $hash, $applicationId) {
            $this->lockInvoices($clinic, [$source, $target]); $this->authorize($clinic, $actor, 'edit'); $this->authorize($clinic, $actor, 'create');
            $existing = $this->rows('invoice_credit_application_reversals', ['clinic_id' => $clinic, 'idempotency_key' => $key], true)[0] ?? null;
            if ($existing) { if (!hash_equals($existing['request_hash'], $hash)) { throw new \DomainException('Return request identity conflict.'); } return $existing + ['idempotent' => true]; }
            $application = $this->rows('invoice_credit_applications', ['clinic_id' => $clinic, 'id' => $applicationId], true)[0] ?? null;
            if (!$application || !$this->activeApplications($clinic, [$application], true)) { throw new \DomainException('Application already returned or unavailable.'); }
            $amount = InvoiceCorrectionMoney::cents($application['amount_base']);
            $targetPosition = $this->position($clinic, $target, true);
            if ($amount > $targetPosition['incoming_base_cents']) { throw new \DomainException('Application funding is unavailable for explicit return.'); }
            // Full return reopens target AR; never changes clinical records or creates cash.
            $id = $this->insert('invoice_credit_application_reversals', ['clinic_id' => $clinic, 'application_id' => $applicationId, 'reason' => trim($reason), 'idempotency_key' => $key, 'request_hash' => $hash, 'created_by' => $actor, 'created_at' => $this->businessNow($clinic)->format('Y-m-d H:i:s')]);
            $this->audit($clinic, $actor, 'invoice_credit_application_returned', $source, ['reversal_id' => $id, 'application_id' => $applicationId, 'target_finance_id' => $target, 'amount_base' => $application['amount_base']]);
            return ['id' => $id, 'position' => $this->position($clinic, $source, true), 'target_position' => $this->position($clinic, $target, true), 'idempotent' => false];
        });
    }
}
