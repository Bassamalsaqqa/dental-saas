<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;

final class TreatmentProcedureIntegrationService
{
    public function __construct(
        private ?BaseConnection $db = null,
        private ?ClinicalProcedureLifecycleService $lifecycle = null
    ) {
        $this->db ??= Database::connect();
        $this->lifecycle ??= new ClinicalProcedureLifecycleService($this->db);
    }

    public function getForTreatment(int $clinicId, int $treatmentId): ?array
    {
        $row = $this->db->table('clinical_procedures')
            ->where('clinic_id', $clinicId)
            ->where('treatment_id', $treatmentId)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function createForTreatment(
        int $clinicId,
        array $treatment,
        string $intent,
        int $actorId,
        ?array $chargeLine = null,
        ?int $appointmentId = null
    ): array {
        $this->assertTreatmentContext($clinicId, $treatment, $actorId);
        if (!in_array($intent, ['planned', 'start_now'], true)) {
            throw new DomainException('Invalid treatment lifecycle intent.');
        }

        $existing = $this->getForTreatment($clinicId, (int) ($treatment['id'] ?? 0));
        if ($existing) {
            throw new DomainException('This treatment is already linked to a procedure.');
        }

        $providerType = (string) ($chargeLine['provider_type'] ?? 'none');
        $procedure = $this->lifecycle->create($clinicId, [
            'patient_id' => (int) ($treatment['patient_id'] ?? 0),
            'appointment_id' => $appointmentId,
            'examination_id' => !empty($treatment['examination_id']) ? (int) $treatment['examination_id'] : null,
            'treatment_id' => (int) ($treatment['id'] ?? 0),
            'clinical_charge_line_id' => !empty($chargeLine['id']) ? (int) $chargeLine['id'] : null,
            'provider_type' => $providerType,
            'provider_user_id' => !empty($chargeLine['provider_user_id']) ? (int) $chargeLine['provider_user_id'] : null,
            'provider_name_snapshot' => (string) ($chargeLine['provider_name_snapshot'] ?? ''),
            'procedure_code' => (string) ($chargeLine['procedure_code'] ?? ''),
            'procedure_name' => (string) ($treatment['treatment_name'] ?? $chargeLine['procedure_name'] ?? 'Treatment'),
            'tooth_numbers' => (string) ($treatment['tooth_numbers'] ?? $treatment['tooth_number'] ?? ''),
        ], $actorId);

        if ($intent === 'start_now') {
            $procedure = $this->lifecycle->transition($clinicId, (int) $procedure['id'], 'accepted', $actorId);
            $procedure = $this->lifecycle->transition($clinicId, (int) $procedure['id'], 'in_progress', $actorId);
        }

        return $procedure;
    }

    public function linkChargeLine(
        int $clinicId,
        int $treatmentId,
        ?array $chargeLine,
        int $actorId
    ): ?array {
        if ($clinicId <= 0 || $treatmentId <= 0 || $actorId <= 0) {
            throw new DomainException('Clinic, treatment, and actor are required.');
        }

        $procedure = $this->getForTreatment($clinicId, $treatmentId);
        if (!$procedure || empty($chargeLine['id'])) {
            return $procedure;
        }

        if ((int) ($chargeLine['clinic_id'] ?? 0) !== $clinicId
            || (int) ($chargeLine['patient_id'] ?? 0) !== (int) $procedure['patient_id']
            || (int) ($chargeLine['treatment_id'] ?? 0) !== $treatmentId) {
            throw new DomainException('Charge line does not match the linked treatment procedure.');
        }

        $currentChargeId = (int) ($procedure['clinical_charge_line_id'] ?? 0);
        $chargeLineId = (int) $chargeLine['id'];
        if ($currentChargeId > 0 && $currentChargeId !== $chargeLineId) {
            throw new DomainException('The procedure is already linked to another charge line.');
        }

        if ($currentChargeId === 0) {
            $this->db->table('clinical_procedures')
                ->where('clinic_id', $clinicId)
                ->where('id', (int) $procedure['id'])
                ->where('treatment_id', $treatmentId)
                ->update([
                    'clinical_charge_line_id' => $chargeLineId,
                    'provider_type' => (string) ($chargeLine['provider_type'] ?? 'none'),
                    'provider_user_id' => !empty($chargeLine['provider_user_id']) ? (int) $chargeLine['provider_user_id'] : null,
                    'provider_name_snapshot' => !empty($chargeLine['provider_name_snapshot']) ? (string) $chargeLine['provider_name_snapshot'] : null,
                    'updated_by' => $actorId,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }

        return $this->getForTreatment($clinicId, $treatmentId);
    }

    public function syncTreatment(int $clinicId, array $treatment, int $actorId): ?array
    {
        $this->assertTreatmentContext($clinicId, $treatment, $actorId);
        $treatmentId = (int) ($treatment['id'] ?? 0);
        $procedure = $this->getForTreatment($clinicId, $treatmentId);
        if (!$procedure) {
            return null;
        }

        if ((int) ($treatment['clinic_id'] ?? 0) !== $clinicId
            || (int) ($treatment['patient_id'] ?? 0) !== (int) $procedure['patient_id']) {
            throw new DomainException('A linked treatment procedure cannot be moved to another patient or clinic.');
        }

        $this->db->table('clinical_procedures')
            ->where('clinic_id', $clinicId)
            ->where('id', (int) $procedure['id'])
            ->where('treatment_id', $treatmentId)
            ->update([
                'examination_id' => !empty($treatment['examination_id']) ? (int) $treatment['examination_id'] : null,
                'procedure_name' => (string) ($treatment['treatment_name'] ?? $procedure['procedure_name']),
                'tooth_numbers' => !empty($treatment['tooth_number']) ? (string) $treatment['tooth_number'] : null,
                'updated_by' => $actorId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $this->getForTreatment($clinicId, $treatmentId);
    }

    public function completeForTreatment(int $clinicId, int $treatmentId, int $actorId): ?array
    {
        if ($clinicId <= 0 || $treatmentId <= 0 || $actorId <= 0) {
            throw new DomainException('Clinic, treatment, and actor are required.');
        }

        $procedure = $this->getForTreatment($clinicId, $treatmentId);
        if (!$procedure) {
            return null;
        }

        $status = (string) $procedure['status'];
        if ($status === 'completed') {
            return $procedure;
        }
        if (in_array($status, ['rejected', 'referred', 'cancelled'], true)) {
            throw new DomainException('A closed procedure cannot be completed.');
        }

        if (in_array($status, ['proposed', 'presented'], true)) {
            $procedure = $this->lifecycle->transition($clinicId, (int) $procedure['id'], 'accepted', $actorId);
            $status = 'accepted';
        }
        if (in_array($status, ['accepted', 'scheduled'], true)) {
            $procedure = $this->lifecycle->transition($clinicId, (int) $procedure['id'], 'in_progress', $actorId);
        }

        return $this->lifecycle->transition($clinicId, (int) $procedure['id'], 'completed', $actorId);
    }

    private function assertTreatmentContext(int $clinicId, array $treatment, int $actorId): void
    {
        if ($clinicId <= 0
            || $actorId <= 0
            || (int) ($treatment['id'] ?? 0) <= 0
            || (int) ($treatment['clinic_id'] ?? 0) !== $clinicId
            || (int) ($treatment['patient_id'] ?? 0) <= 0) {
            throw new DomainException('Invalid treatment procedure context.');
        }
    }
}
