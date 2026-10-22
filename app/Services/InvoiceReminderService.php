<?php
namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/** Parent finance lock always precedes targeted queue locks. No locks span transport. */
final class InvoiceReminderService
{
    private array $tables = [];
    public function __construct(private BaseConnection $db) {}
    private function table(string $name): string { return $this->db->escapeIdentifiers($this->db->prefixTable($name)); }
    private function hasTable(string $name): bool { return $this->tables[$name] ??= $this->db->tableExists($name); }
    private function query(string $sql, array $params = [])
    {
        $result = $this->db->query($sql, $params);
        if ($result === false) { throw new \RuntimeException('Unable to verify invoice reminder.'); }
        return $result;
    }
    private function requireTransaction(): void
    {
        if ((int) $this->db->transDepth < 1 || $this->db->transStatus() === false) {
            throw new \RuntimeException('Invoice reminder mutation requires the parent transaction.');
        }
    }
    private function payload(array $row): array
    {
        $data = json_decode((string) ($row['payload_json'] ?? ''), true);
        return is_array($data) ? $data : [];
    }
    public function isPaymentReminder(array $row): bool
    {
        $payload = $this->payload($row);
        $key = $payload['workflow_key'] ?? null;
        return (is_string($key) && str_starts_with($key, 'payment_reminder'))
            || ($payload['workflow_trigger'] ?? null) === 'payment_reminder';
    }
    private function financeId(array $row): ?int
    {
        $key = $this->payload($row)['workflow_key'] ?? null;
        if (!is_string($key) || !preg_match('/^payment_reminder:([1-9][0-9]*):[^\r\n]+$/D', $key, $match)
            || strlen($match[1]) > 10 || (float) $match[1] > 4294967295) { return null; }
        return (int) $match[1];
    }
    /** Nonlocking discovery avoids locking historical rows scanned by JSON predicates. */
    private function lockMatching(int $clinicId, int $financeId): array
    {
        $this->requireTransaction();
        if (!$this->hasTable('notifications')) { return []; }
        $sql = 'SELECT id FROM ' . $this->table('notifications') . " WHERE clinic_id = ? AND SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(CASE WHEN JSON_VALID(payload_json) THEN payload_json ELSE '{}' END, '$.workflow_key')), ':', 2) = ? ORDER BY id";
        $ids = $this->query($sql, [$clinicId, 'payment_reminder:' . $financeId])->getResultArray();
        $rows = [];
        foreach ($ids as $id) {
            $row = $this->query('SELECT id, clinic_id, status, payload_json FROM ' . $this->table('notifications') . ' WHERE id = ? AND clinic_id = ? FOR UPDATE', [(int) $id['id'], $clinicId])->getRowArray();
            // Current row, not the discovery snapshot, determines authority.
            if ($row && $this->financeId($row) === $financeId) { $rows[] = $row; }
        }
        return $rows;
    }
    /** Caller owns the locked finance row. Other queue states do not block ordinary edits. */
    public function assertNoProcessingForMutation(int $clinicId, int $financeId): void
    {
        foreach ($this->lockMatching($clinicId, $financeId) as $row) {
            if ($row['status'] === 'processing') { throw new \DomainException('Invoice reminder is processing. Retry this correction after delivery finishes.'); }
        }
    }
    /** Same transaction as delete/audit; retains payload, attempts, and queue rows. */
    public function reconcileForDeletion(int $clinicId, int $financeId): array
    {
        $rows = $this->lockMatching($clinicId, $financeId);
        $cancelled = [];
        foreach ($rows as $row) {
            if (!in_array($row['status'], ['pending', 'failed', 'blocked', 'rejected'], true)) {
                throw new \DomainException('Invoice reminder delivery history prevents deleting this invoice.');
            }
            if ($this->hasTable('notification_attempts') && $this->query('SELECT id FROM ' . $this->table('notification_attempts') . ' WHERE notification_id = ? AND clinic_id = ? ORDER BY id LIMIT 1 FOR UPDATE', [(int) $row['id'], $clinicId])->getRowArray()) {
                throw new \DomainException('Invoice reminder attempt history prevents deleting this invoice.');
            }
        }
        foreach ($rows as $row) {
            if (!$this->db->table('notifications')->where('id', $row['id'])->where('clinic_id', $clinicId)->update([
                'status' => 'rejected', 'failure_reason' => 'INVOICE_DELETED', 'locked_at' => null, 'locked_by' => null,
            ])) { throw new \RuntimeException('Unable to reconcile invoice reminder.'); }
            $cancelled[] = (int) $row['id'];
        }
        return ['cancelled_count' => count($cancelled), 'notification_ids' => $cancelled];
    }
    private function eligible(array $note, array $finance, int $clinicId): bool
    {
        if (($finance['transaction_type'] ?? '') !== 'invoice'
            || !in_array($finance['payment_status'] ?? '', ['pending', 'partial', 'overdue'], true)
            || (int) ($finance['clinic_id'] ?? 0) !== $clinicId
            || !is_numeric($finance['balance_due'] ?? null) || !is_finite((float) $finance['balance_due'])
            || round((float) $finance['balance_due'], 2) <= 0
            || ($note['recipient_type'] ?? '') !== 'patient'
            || (int) ($note['recipient_id'] ?? 0) !== (int) ($finance['patient_id'] ?? 0)) { return false; }
        $patient = $this->query('SELECT id FROM ' . $this->table('patients') . ' WHERE id = ? AND clinic_id = ?', [(int) $finance['patient_id'], $clinicId])->getRowArray();
        if (!$patient) { return false; }
        $vars = $this->payload($note)['template_vars'] ?? null;
        if (!is_array($vars)) { return false; }
        try {
            $formatter = new InvoiceReminderFormatter($this->db);
            $expectedAmount = $formatter->formatAmount($clinicId, (float) $finance['balance_due']);
            $expectedDueDate = $formatter->formatDueDate($clinicId, !empty($finance['due_date']) ? (string) $finance['due_date'] : null);
        } catch (\Throwable $e) {
            return false;
        }
        return ($vars['amount'] ?? null) === $expectedAmount
            && ($vars['due_date'] ?? null) === $expectedDueDate
            && ($vars['invoice_number'] ?? null) === (string) ($finance['transaction_id'] ?? ('INV-' . $finance['id']));
    }
    /** Returns the exact claimed current row, or false. Caller must not own a transaction. */
    public function claim(array $discovered, int $clinicId, string $dispatcherId, string $channelType, int $staleMinutes): array|false
    {
        if ((int) $this->db->transDepth !== 0 || $this->db->transStatus() === false || !$this->db->transBegin()) {
            throw new \RuntimeException('Unable to own invoice reminder claim transaction.');
        }
        try {
            $financeId = $this->financeId($discovered);
            $finance = $financeId === null ? null : $this->query('SELECT id, clinic_id, patient_id, transaction_type, payment_status, balance_due, due_date, transaction_id FROM ' . $this->table('finances') . ' WHERE clinic_id = ? AND id = ? FOR UPDATE', [$clinicId, $financeId])->getRowArray();
            if ($finance !== null && ($finance['payment_status'] ?? '') !== 'cancelled') {
                $service = new InvoiceCorrectionService($this->db);
                if (($finance['transaction_type'] ?? '') === 'invoice' && $service->hasEffects($clinicId, (int) $finance['id'], true)) {
                    $position = $service->position($clinicId, (int) $finance['id'], true);
                    $finance['balance_due'] = $position['remaining_base'];
                    $finance['payment_status'] = (float) $position['remaining_base'] <= 0 ? 'paid' : 'pending';
                }
            }
            $note = $this->query('SELECT *, (locked_at IS NULL OR locked_at < DATE_SUB(NOW(), INTERVAL ' . max(1, $staleMinutes) . ' MINUTE)) AS claim_lock_available FROM ' . $this->table('notifications') . ' WHERE id = ? AND clinic_id = ? AND channel_type = ? FOR UPDATE', [(int) $discovered['id'], $clinicId, $channelType])->getRowArray();
            $claimed = false;
            if ($note && in_array($note['status'], ['pending', 'processing'], true) && (int) $note['claim_lock_available'] === 1
                && ($note['status'] !== 'processing' || !empty($note['locked_at']))) {
                if ($financeId === null || !$finance || $this->financeId($note) !== $financeId || !$this->eligible($note, $finance, $clinicId)) {
                    $update = ['status' => 'rejected', 'failure_reason' => 'INVOICE_REMINDER_STALE_OR_INVALID', 'locked_at' => null, 'locked_by' => null];
                } else {
                    $update = ['status' => 'processing', 'failure_reason' => null, 'locked_at' => (string) $this->query('SELECT NOW() AS claim_now')->getRowArray()['claim_now'], 'locked_by' => $dispatcherId];
                    $claimed = array_merge($note, $update);
                    unset($claimed['claim_lock_available']);
                }
                if (!$this->db->table('notifications')->where('id', $note['id'])->where('clinic_id', $clinicId)->update($update) || $this->db->affectedRows() !== 1) {
                    throw new \RuntimeException('Unable to update invoice reminder claim.');
                }
            }
            if ($this->db->transStatus() === false || !$this->db->transCommit()) { throw new \RuntimeException('Unable to commit invoice reminder claim.'); }
            return $claimed;
        } catch (\Throwable $e) { $this->db->transRollback(); throw $e; }
    }
}
