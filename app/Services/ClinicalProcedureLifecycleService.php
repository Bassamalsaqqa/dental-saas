<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DateTimeImmutable;
use DomainException;
use RuntimeException;

final class ClinicalProcedureLifecycleService
{
    private const TRANSITIONS = [
        'proposed' => ['presented', 'accepted', 'rejected', 'referred', 'cancelled'],
        'presented' => ['accepted', 'rejected', 'referred', 'cancelled'],
        'accepted' => ['scheduled', 'in_progress', 'referred', 'cancelled'],
        'scheduled' => ['in_progress', 'referred', 'cancelled'],
        'in_progress' => ['completed', 'referred', 'cancelled'],
        'completed' => [],
        'rejected' => [],
        'referred' => [],
        'cancelled' => [],
    ];

    private const TIMESTAMP_FIELDS = [
        'proposed' => 'proposed_at',
        'presented' => 'presented_at',
        'accepted' => 'accepted_at',
        'rejected' => 'rejected_at',
        'scheduled' => 'scheduled_at',
        'in_progress' => 'started_at',
        'completed' => 'completed_at',
        'referred' => 'referred_at',
        'cancelled' => 'cancelled_at',
    ];

    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= Database::connect();
    }

    public function allowedNext(string $status): array
    {
        return self::TRANSITIONS[$status] ?? [];
    }

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, $this->allowedNext($from), true);
    }

    public function isTerminal(string $status): bool
    {
        return array_key_exists($status, self::TRANSITIONS)
            && self::TRANSITIONS[$status] === [];
    }

    public function create(
        int $clinicId,
        array $input,
        int $actorId,
        ?DateTimeImmutable $now = null
    ): array {
        if ($clinicId <= 0 || $actorId <= 0) {
            throw new DomainException('Clinic and actor are required.');
        }

        $patientId = (int) ($input['patient_id'] ?? 0);
        $procedureName = trim((string) ($input['procedure_name'] ?? ''));
        if ($patientId <= 0 || $procedureName === '') {
            throw new DomainException('Patient and procedure name are required.');
        }

        $this->findRelated($clinicId, 'patients', $patientId);

        $links = [
            'examination_id' => ['examinations', 'examination'],
            'condition_odontogram_id' => ['odontograms', 'odontogram condition'],
            'treatment_id' => ['treatments', 'treatment'],
            'appointment_id' => ['appointments', 'appointment'],
            'contract_id' => ['treatment_contracts', 'contract'],
            'clinical_charge_line_id' => ['clinical_charge_lines', 'charge line'],
        ];

        foreach ($links as $field => [$table, $label]) {
            $id = (int) ($input[$field] ?? 0);
            if ($id > 0) {
                $this->assertPatientLink($clinicId, $table, $id, $patientId, $label);
            }
        }

        $contractItemId = (int) ($input['contract_item_id'] ?? 0);
        if ($contractItemId > 0) {
            $item = $this->findRelated($clinicId, 'treatment_contract_items', $contractItemId);
            $contractId = (int) ($input['contract_id'] ?? $item['contract_id'] ?? 0);
            if ($contractId <= 0 || $contractId !== (int) ($item['contract_id'] ?? 0)) {
                throw new DomainException('Contract item does not match the selected contract.');
            }
            $this->assertPatientLink($clinicId, 'treatment_contracts', $contractId, $patientId, 'contract');
            $input['contract_id'] = $contractId;
        }

        $providerType = (string) ($input['provider_type'] ?? 'none');
        $providerUserId = (int) ($input['provider_user_id'] ?? 0);
        $providerName = trim((string) ($input['provider_name_snapshot'] ?? ''));
        if (!in_array($providerType, ['user', 'guest', 'none'], true)) {
            throw new DomainException('Invalid provider type.');
        }
        if ($providerType === 'user') {
            $membership = $this->db->table('clinic_users')
                ->where('clinic_id', $clinicId)
                ->where('user_id', $providerUserId)
                ->where('status', 'active')
                ->get()
                ->getRowArray();
            if (!$membership) {
                throw new DomainException('Provider is not active in the selected clinic.');
            }
        } elseif ($providerType === 'guest' && $providerName === '') {
            throw new DomainException('Guest provider name is required.');
        } else {
            $providerUserId = 0;
            if ($providerType === 'none') {
                $providerName = '';
            }
        }

        $now ??= new DateTimeImmutable();
        $timestamp = $now->format('Y-m-d H:i:s');
        $row = [
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'examination_id' => $this->nullableId($input['examination_id'] ?? null),
            'condition_odontogram_id' => $this->nullableId($input['condition_odontogram_id'] ?? null),
            'treatment_id' => $this->nullableId($input['treatment_id'] ?? null),
            'appointment_id' => $this->nullableId($input['appointment_id'] ?? null),
            'contract_id' => $this->nullableId($input['contract_id'] ?? null),
            'contract_item_id' => $this->nullableId($input['contract_item_id'] ?? null),
            'clinical_charge_line_id' => $this->nullableId($input['clinical_charge_line_id'] ?? null),
            'provider_type' => $providerType,
            'provider_user_id' => $providerUserId > 0 ? $providerUserId : null,
            'provider_name_snapshot' => $providerName !== '' ? $providerName : null,
            'procedure_code' => $this->nullableText($input['procedure_code'] ?? null, 80),
            'procedure_name' => mb_substr($procedureName, 0, 255),
            'tooth_numbers' => $this->nullableText($input['tooth_numbers'] ?? null, 120),
            'surfaces' => $this->nullableText($input['surfaces'] ?? null, 80),
            'status' => 'proposed',
            'proposed_at' => $timestamp,
            'created_by' => $actorId,
            'updated_by' => $actorId,
            'status_changed_by' => $actorId,
            'status_changed_at' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];

        $this->db->transStart();
        $this->db->table('clinical_procedures')->insert($row);
        $procedureId = (int) $this->db->insertID();
        $this->db->table('clinical_procedure_status_history')->insert([
            'clinic_id' => $clinicId,
            'procedure_id' => $procedureId,
            'from_status' => null,
            'to_status' => 'proposed',
            'reason' => null,
            'changed_by' => $actorId,
            'created_at' => $timestamp,
        ]);
        $this->db->transComplete();

        if (!$this->db->transStatus() || $procedureId <= 0) {
            throw new RuntimeException('Procedure could not be created.');
        }

        return $this->db->table('clinical_procedures')
            ->where('clinic_id', $clinicId)
            ->where('id', $procedureId)
            ->get()
            ->getRowArray();
    }

    public function transition(
        int $clinicId,
        int $procedureId,
        string $toStatus,
        int $actorId,
        ?string $reason = null,
        ?DateTimeImmutable $now = null
    ): array {
        if ($clinicId <= 0 || $procedureId <= 0 || $actorId <= 0) {
            throw new DomainException('Clinic, procedure, and actor are required.');
        }

        $procedure = $this->db->table('clinical_procedures')
            ->where('clinic_id', $clinicId)
            ->where('id', $procedureId)
            ->get()
            ->getRowArray();

        if (!$procedure) {
            throw new DomainException('Procedure not found in the active clinic.');
        }

        $fromStatus = (string) ($procedure['status'] ?? '');
        if (!$this->canTransition($fromStatus, $toStatus)) {
            throw new DomainException("Invalid procedure transition: {$fromStatus} -> {$toStatus}.");
        }

        $reason = trim((string) $reason);
        if (in_array($toStatus, ['rejected', 'referred', 'cancelled'], true) && $reason === '') {
            throw new DomainException('A reason is required for rejected, referred, or cancelled procedures.');
        }

        $now ??= new DateTimeImmutable();
        $timestamp = $now->format('Y-m-d H:i:s');
        $update = [
            'status' => $toStatus,
            'status_reason' => $reason !== '' ? $reason : null,
            'status_changed_by' => $actorId,
            'status_changed_at' => $timestamp,
            'updated_by' => $actorId,
            'updated_at' => $timestamp,
            self::TIMESTAMP_FIELDS[$toStatus] => $timestamp,
        ];

        $this->db->transStart();
        $this->db->table('clinical_procedures')
            ->where('clinic_id', $clinicId)
            ->where('id', $procedureId)
            ->where('status', $fromStatus)
            ->update($update);

        if ($this->db->affectedRows() !== 1) {
            $this->db->transRollback();
            throw new RuntimeException('Procedure changed concurrently; reload and try again.');
        }

        $this->db->table('clinical_procedure_status_history')->insert([
            'clinic_id' => $clinicId,
            'procedure_id' => $procedureId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason' => $reason !== '' ? $reason : null,
            'changed_by' => $actorId,
            'created_at' => $timestamp,
        ]);
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new RuntimeException('Procedure transition could not be saved.');
        }

        return $this->db->table('clinical_procedures')
            ->where('clinic_id', $clinicId)
            ->where('id', $procedureId)
            ->get()
            ->getRowArray();
    }

    private function assertPatientLink(
        int $clinicId,
        string $table,
        int $id,
        int $patientId,
        string $label
    ): void {
        $row = $this->findRelated($clinicId, $table, $id);
        if ((int) ($row['patient_id'] ?? 0) !== $patientId) {
            throw new DomainException(ucfirst($label) . ' does not belong to the selected patient.');
        }
    }

    private function findRelated(int $clinicId, string $table, int $id): array
    {
        $row = $this->db->table($table)
            ->where('clinic_id', $clinicId)
            ->where('id', $id)
            ->get()
            ->getRowArray();
        if (!$row) {
            throw new DomainException('Related record was not found in the active clinic.');
        }

        return $row;
    }

    private function nullableId(mixed $value): ?int
    {
        $id = (int) $value;
        return $id > 0 ? $id : null;
    }

    private function nullableText(mixed $value, int $limit): ?string
    {
        $value = trim((string) $value);
        return $value !== '' ? mb_substr($value, 0, $limit) : null;
    }
}
