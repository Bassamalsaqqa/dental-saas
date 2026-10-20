<?php
namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;

/** A real database contention error, never a general invalid-payload failure. */
final class WorkflowClaimContentionException extends \RuntimeException {}

/** Targeted non-invoice workflow claim. Locks end before transport; no transport freeze is promised. */
final class ClinicWorkflowClaimService
{
    private const TRIGGERS = ['appointment_confirmation', 'appointment_reminder', 'no_show_follow_up', 'follow_up'];
    public function __construct(private BaseConnection $db, private ?DateTimeImmutable $now = null) {}
    private function payload(array $row): array
    {
        $payload = json_decode((string) ($row['payload_json'] ?? ''), true);
        return is_array($payload) ? $payload : [];
    }
    public function handles(array $row): bool
    {
        if (($row['channel_type'] ?? '') !== 'whatsapp') { return false; }
        $payload = $this->payload($row);
        foreach (self::TRIGGERS as $trigger) {
            if (($payload['workflow_trigger'] ?? '') === $trigger || str_starts_with((string) ($payload['workflow_key'] ?? ''), $trigger . ':')) { return true; }
        }
        return false;
    }
    private function query(string $sql, array $params = [])
    {
        try { $result = $this->db->query($sql, $params); }
        catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $code = (int) ($this->db->error()['code'] ?? 0);
            if (in_array($code, [1205, 1213, 3572], true)) { throw new WorkflowClaimContentionException('Workflow source is busy.', $code, $e); }
            throw $e;
        }
        if ($result === false) {
            $code = (int) ($this->db->error()['code'] ?? 0);
            if (in_array($code, [1205, 1213, 3572], true)) { throw new WorkflowClaimContentionException('Workflow source is busy.', $code); }
            throw new \RuntimeException('Unable to verify workflow claim.', $code);
        }
        return $result;
    }
    private function table(string $table): string { return $this->db->escapeIdentifiers($this->db->prefixTable($table)); }

    public static function sourceMatches(array $note, array $payload, array $source, array $patient, int $clinicId, ClinicWorkflowFormatter $formatter, ?DateTimeImmutable $now = null, int $leadMinutes = 1440): bool
    {
        $trigger = $payload['workflow_trigger'] ?? '';
        if (!in_array($trigger, self::TRIGGERS, true) || (int) ($note['clinic_id'] ?? 0) !== $clinicId
            || (int) ($source['clinic_id'] ?? 0) !== $clinicId || (int) ($patient['clinic_id'] ?? 0) !== $clinicId
            || ($note['recipient_type'] ?? '') !== 'patient' || (int) ($note['recipient_id'] ?? 0) !== (int) ($source['patient_id'] ?? 0)
            || (int) ($patient['id'] ?? 0) !== (int) ($source['patient_id'] ?? 0)) { return false; }
        if ($trigger === 'follow_up') {
            return ($source['status'] ?? '') === 'completed' && ($payload['workflow_key'] ?? '') === 'follow_up:' . $source['id'];
        }
        $status = $trigger === 'no_show_follow_up' ? 'no_show' : 'scheduled';
        if ($trigger === 'no_show_follow_up') {
            $prefix = $trigger . ':' . $source['id'] . ':';
            $key = $payload['workflow_key'] ?? '';
            if (!is_string($key) || !str_starts_with($key, $prefix)) { return false; }
            $enqueueDay = substr($key, strlen($prefix));
            try { $formatter->date($enqueueDay); }
            catch (\DomainException $e) { return false; }
            // The enqueue day is idempotency metadata, never an implicit message expiry.
            if ($enqueueDay > $formatter->currentDate($now)) { return false; }
        } else {
            $key = $trigger . ':' . $source['id'] . ':' . $source['appointment_date'] . ':' . $source['appointment_time'];
        }
        return ($source['status'] ?? '') === $status && ($payload['workflow_key'] ?? '') === $key
            && ($trigger !== 'appointment_reminder' || $formatter->reminderEligible($source, $leadMinutes, $now));
    }

    public function claim(array $discovered, int $clinicId, string $dispatcherId, string $channelType, int $staleMinutes): array|false
    {
        if ((int) $this->db->transDepth !== 0 || $this->db->transStatus() === false || !$this->db->transBegin()) {
            throw new \RuntimeException('Workflow claim must own its transaction.');
        }
        try {
            $clinic = $this->query('SELECT id, name FROM ' . $this->table('clinics') . ' WHERE id = ? FOR UPDATE NOWAIT', [$clinicId])->getRowArray();
            $hint = $this->payload($discovered);
            $trigger = $hint['workflow_trigger'] ?? '';
            $id = null;
            if (is_string($trigger) && in_array($trigger, self::TRIGGERS, true)
                && preg_match('/^' . preg_quote($trigger, '/') . ':([1-9][0-9]{0,9})(?::[^\r\n]+)?$/D', (string) ($hint['workflow_key'] ?? ''), $match)
                && (float) $match[1] <= 4294967295) { $id = (int) $match[1]; }
            $isExam = $trigger === 'follow_up';
            $columns = $isExam ? 'id, clinic_id, patient_id, examination_date, status' : 'id, clinic_id, patient_id, appointment_date, appointment_time, status';
            // Discovery is nonlocking. Canonical parent order is clinic -> patient -> source -> queue.
            // NOWAIT avoids waiting behind legacy child-first writers while holding clinic.
            $hintSource = $id === null ? null : $this->query('SELECT id, patient_id FROM ' . $this->table($isExam ? 'examinations' : 'appointments') . ' WHERE id = ? AND clinic_id = ?', [$id, $clinicId])->getRowArray();
            $patient = !$hintSource ? null : $this->query('SELECT id, clinic_id, first_name, last_name FROM ' . $this->table('patients') . ' WHERE id = ? AND clinic_id = ? FOR UPDATE NOWAIT', [(int) $hintSource['patient_id'], $clinicId])->getRowArray();
            $source = !$hintSource ? null : $this->query('SELECT ' . $columns . ' FROM ' . $this->table($isExam ? 'examinations' : 'appointments') . ' WHERE id = ? AND clinic_id = ? FOR UPDATE NOWAIT', [$id, $clinicId])->getRowArray();
            // Current locked identity, not the discovery snapshot, is authoritative.
            if ($source && (int) $source['patient_id'] !== (int) $hintSource['patient_id']) { $source = null; }
            $note = $this->query('SELECT *, (locked_at IS NULL OR locked_at < DATE_SUB(NOW(), INTERVAL ' . max(1, $staleMinutes) . ' MINUTE)) AS claim_lock_available FROM ' . $this->table('notifications') . ' WHERE id = ? AND clinic_id = ? AND channel_type = ? FOR UPDATE NOWAIT', [(int) $discovered['id'], $clinicId, $channelType])->getRowArray();
            $claimed = false;
            if ($note && in_array($note['status'], ['pending', 'processing'], true) && (int) $note['claim_lock_available'] === 1
                && ($note['status'] !== 'processing' || !empty($note['locked_at']))) {
                $valid = false;
                try {
                    $payload = $this->payload($note);
                    $formatter = ClinicWorkflowFormatter::forClinic($this->db, $clinicId);
                    $policy = $this->db->table('whatsapp_tenant_policies')->select('reminder_lead_time_minutes')->where('clinic_id', $clinicId)->get()->getRowArray();
                    $valid = $clinic && $source && $patient && ($payload['workflow_key'] ?? null) === ($hint['workflow_key'] ?? null)
                        && ($payload['workflow_trigger'] ?? null) === $trigger
                        && self::sourceMatches($note, $payload, $source, $patient, $clinicId, $formatter, $this->now, (int) ($policy['reminder_lead_time_minutes'] ?? 1440));
                    if ($valid) {
                        $clinicVars = ['clinic_name' => '', 'clinic_phone' => '', 'clinic_address' => ''];
                        $rows = $this->db->table('settings')->select('setting_key, setting_value')->where('clinic_id', $clinicId)->whereIn('setting_key', array_keys($clinicVars))->get()->getResultArray();
                        foreach ($rows as $row) { $clinicVars[$row['setting_key']] = trim((string) $row['setting_value']); }
                        if ($clinicVars['clinic_name'] === '') { $clinicVars['clinic_name'] = trim((string) $clinic['name']); }
                        $vars = $isExam ? $formatter->examinationVars($patient, $source, $clinicVars) : $formatter->appointmentVars($patient, $source, $clinicVars);
                        $config = (new WhatsAppTemplateRegistryService())->getGlobalTriggerTemplates()[$trigger] ?? [];
                        $valid = WorkflowTemplatePayload::matches($payload, $config, $vars);
                    }
                } catch (\DomainException $e) { $valid = false; }
                $update = $valid ? ['status' => 'processing', 'failure_reason' => null, 'locked_at' => (string) $this->query('SELECT NOW() AS claim_now')->getRowArray()['claim_now'], 'locked_by' => $dispatcherId]
                    : ['status' => 'rejected', 'failure_reason' => 'WORKFLOW_STALE_OR_INVALID', 'locked_at' => null, 'locked_by' => null];
                if (!$this->db->table('notifications')->where('id', $note['id'])->where('clinic_id', $clinicId)->update($update) || $this->db->affectedRows() !== 1) { throw new \RuntimeException('Unable to update workflow claim.'); }
                if ($valid) { $claimed = array_merge($note, $update); unset($claimed['claim_lock_available']); }
            }
            if ($this->db->transStatus() === false || !$this->db->transCommit()) { throw new \RuntimeException('Unable to commit workflow claim.'); }
            return $claimed;
        } catch (WorkflowClaimContentionException $e) {
            // This service owns this transaction. Only known retryable lock errors may recover it.
            if (!$this->db->transRollback() || (int) $this->db->transDepth !== 0) { throw new \RuntimeException('Unable to roll back contested workflow claim.', 0, $e); }
            $this->db->resetTransStatus();
            return false;
        } catch (\Throwable $e) { $this->db->transRollback(); throw $e; }
    }
}
