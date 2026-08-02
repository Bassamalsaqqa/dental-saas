<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use DomainException;

class ExaminationNoteGovernanceService
{
    private const SNAPSHOT_FIELDS = [
        'examination_id', 'patient_id', 'appointment_id', 'examination_date', 'examination_type',
        'chief_complaint', 'history_of_present_illness', 'medical_history', 'dental_history',
        'health_update_medical_changes', 'health_update_medication_changes',
        'health_update_allergies_reactions', 'health_update_pregnancy',
        'health_update_current_symptoms', 'health_update_details',
        'clinical_findings', 'diagnosis', 'prognosis', 'recommendations', 'examination_notes', 'status',
        'created_by', 'created_at', 'updated_at',
    ];

    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= \Config\Database::connect();
    }

    public function state(int $clinicId, int $examinationId): array
    {
        $signature = $this->db->table('examination_note_signatures')
            ->where('clinic_id', $clinicId)
            ->where('examination_id', $examinationId)
            ->get()->getRowArray();

        $addenda = [];
        if ($signature) {
            $addenda = $this->db->table('examination_note_addenda')
                ->where('clinic_id', $clinicId)
                ->where('examination_id', $examinationId)
                ->where('signature_id', $signature['id'])
                ->orderBy('created_at', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()->getResultArray();
        }

        return ['signed' => $signature !== null, 'signature' => $signature, 'addenda' => $addenda];
    }

    public function isSigned(int $clinicId, int $examinationId): bool
    {
        return $this->db->table('examination_note_signatures')
            ->where('clinic_id', $clinicId)
            ->where('examination_id', $examinationId)
            ->countAllResults() > 0;
    }

    public function sign(int $clinicId, int $examinationId, int $actorUserId, string $actorName): array
    {
        $this->db->transBegin();
        try {
            $examination = $this->db->table('examinations')
                ->where('clinic_id', $clinicId)
                ->where('id', $examinationId)
                ->get()->getRowArray();
            if (!$examination) {
                throw new DomainException('Examination not found.');
            }
            if (($examination['status'] ?? '') !== 'completed') {
                throw new DomainException('Complete the examination before signing the clinical note.');
            }
            if ($this->isSigned($clinicId, $examinationId)) {
                throw new DomainException('This clinical note is already signed.');
            }

            $snapshot = [];
            foreach (self::SNAPSHOT_FIELDS as $field) {
                $snapshot[$field] = $examination[$field] ?? null;
            }
            $snapshotJson = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $signedAt = date('Y-m-d H:i:s');
            $contentHash = hash('sha256', $clinicId . '|' . $examinationId . '|' . $actorUserId . '|' . $signedAt . '|' . $snapshotJson);

            $this->db->table('examination_note_signatures')->insert([
                'clinic_id' => $clinicId,
                'examination_id' => $examinationId,
                'patient_id' => (int) $examination['patient_id'],
                'signed_by_user_id' => $actorUserId,
                'signed_by_name' => trim($actorName),
                'signed_at' => $signedAt,
                'attestation_version' => 'v1',
                'snapshot_json' => $snapshotJson,
                'content_hash' => $contentHash,
                'created_at' => $signedAt,
            ]);

            if ($this->db->transStatus() === false) {
                throw new DomainException('Unable to sign the clinical note.');
            }
            $this->db->transCommit();
            return $this->state($clinicId, $examinationId);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($e instanceof DomainException) {
                throw $e;
            }
            throw new DomainException('Unable to sign the clinical note.', 0, $e);
        }
    }

    public function addAddendum(
        int $clinicId,
        int $examinationId,
        int $actorUserId,
        string $actorName,
        string $reason,
        string $body
    ): array {
        $reason = trim($reason);
        $body = trim($body);
        if ($reason === '' || mb_strlen($reason) > 255) {
            throw new DomainException('Addendum reason is required and must not exceed 255 characters.');
        }
        if (mb_strlen($body) < 3 || mb_strlen($body) > 10000) {
            throw new DomainException('Addendum text must be between 3 and 10000 characters.');
        }

        $this->db->transBegin();
        try {
            $state = $this->state($clinicId, $examinationId);
            if (!$state['signed']) {
                throw new DomainException('Sign the clinical note before adding an addendum.');
            }
            $signature = $state['signature'];
            $previousHash = !empty($state['addenda'])
                ? (string) end($state['addenda'])['content_hash']
                : (string) $signature['content_hash'];
            $createdAt = date('Y-m-d H:i:s');
            $contentHash = hash('sha256', $previousHash . '|' . $clinicId . '|' . $examinationId . '|' . $actorUserId . '|' . $createdAt . '|' . $reason . '|' . $body);

            $this->db->table('examination_note_addenda')->insert([
                'clinic_id' => $clinicId,
                'examination_id' => $examinationId,
                'signature_id' => (int) $signature['id'],
                'added_by_user_id' => $actorUserId,
                'added_by_name' => trim($actorName),
                'reason' => $reason,
                'body' => $body,
                'previous_hash' => $previousHash,
                'content_hash' => $contentHash,
                'created_at' => $createdAt,
            ]);

            if ($this->db->transStatus() === false) {
                throw new DomainException('Unable to add the clinical note addendum.');
            }
            $this->db->transCommit();
            return $this->state($clinicId, $examinationId);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($e instanceof DomainException) {
                throw $e;
            }
            throw new DomainException('Unable to add the clinical note addendum.', 0, $e);
        }
    }
}
