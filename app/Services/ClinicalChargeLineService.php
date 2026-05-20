<?php

namespace App\Services;

use App\Models\ClinicalChargeLineModel;

class ClinicalChargeLineService
{
    private ClinicalChargeLineModel $chargeLineModel;

    public function __construct(?ClinicalChargeLineModel $chargeLineModel = null)
    {
        $this->chargeLineModel = $chargeLineModel ?? new ClinicalChargeLineModel();
    }

    public function getActiveForTreatment(int $clinicId, int $treatmentId): ?array
    {
        return $this->chargeLineModel
            ->where('clinic_id', $clinicId)
            ->where('treatment_id', $treatmentId)
            ->where('source_entity_type', 'treatment')
            ->where('status !=', 'voided')
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function syncTreatmentChargeLine(int $clinicId, array $treatment, array $payload, ?int $userId = null): array
    {
        $treatmentId = (int) ($treatment['id'] ?? 0);
        if ($treatmentId <= 0 || (int) ($treatment['clinic_id'] ?? 0) !== $clinicId) {
            return ['success' => false, 'message' => 'Invalid treatment context for charge capture.'];
        }

        $existing = $this->getActiveForTreatment($clinicId, $treatmentId);
        $enabled = $this->truthy($payload['charge_enabled'] ?? null);

        if (!$enabled) {
            if ($existing && !in_array((string) ($existing['status'] ?? ''), ['invoiced', 'voided'], true)) {
                $this->chargeLineModel->update((int) $existing['id'], [
                    'status' => 'voided',
                    'voided_by' => $userId,
                    'voided_at' => date('Y-m-d H:i:s'),
                ]);
            }

            return ['success' => true, 'line' => null];
        }

        if ($existing && (string) ($existing['status'] ?? '') === 'invoiced') {
            return [
                'success' => false,
                'message' => 'This treatment charge is already invoiced. Use the finance workflow for adjustments.',
            ];
        }

        $providerType = (string) ($payload['charge_provider_type'] ?? 'none');
        if (!in_array($providerType, ['none', 'user', 'guest'], true)) {
            $providerType = 'none';
        }

        $providerUserId = null;
        $providerSnapshot = trim((string) ($payload['charge_provider_name_snapshot'] ?? ''));
        if ($providerType === 'user') {
            $providerUserId = (int) ($payload['charge_provider_user_id'] ?? 0) ?: null;
            $providerSnapshot = null;
        } elseif ($providerType === 'guest' && $providerSnapshot === '') {
            return ['success' => false, 'message' => 'Outside provider name is required when outside provider is selected.'];
        } elseif ($providerType === 'none') {
            $providerSnapshot = null;
        }

        $status = (string) ($payload['charge_status'] ?? 'planned');
        if (!in_array($status, ['planned', 'accepted', 'completed'], true)) {
            $status = ((string) ($treatment['status'] ?? '') === 'completed') ? 'completed' : 'planned';
        }

        $data = [
            'clinic_id' => $clinicId,
            'patient_id' => (int) ($treatment['patient_id'] ?? 0),
            'examination_id' => !empty($treatment['examination_id']) ? (int) $treatment['examination_id'] : null,
            'treatment_id' => $treatmentId,
            'source_entity_type' => 'treatment',
            'source_entity_id' => $treatmentId,
            'provider_type' => $providerType,
            'provider_user_id' => $providerUserId,
            'provider_name_snapshot' => $providerSnapshot,
            'procedure_name' => trim((string) ($payload['charge_procedure_name'] ?? '')) ?: (string) ($treatment['treatment_name'] ?? 'Treatment charge'),
            'tooth_numbers' => !empty($treatment['tooth_number']) ? (string) $treatment['tooth_number'] : null,
            'quantity' => $this->decimal($payload['charge_quantity'] ?? 1, 1),
            'unit_price' => $this->decimal($payload['charge_unit_price'] ?? 0, 0),
            'discount_amount' => $this->decimal($payload['charge_discount_amount'] ?? 0, 0),
            'tax_amount' => $this->decimal($payload['charge_tax_amount'] ?? 0, 0),
            'currency' => (string) ($payload['charge_currency'] ?? 'ILS'),
            'service_date' => !empty($treatment['start_date']) ? (string) $treatment['start_date'] : null,
            'charge_date' => date('Y-m-d'),
            'status' => $status,
            'notes' => trim((string) ($payload['charge_notes'] ?? '')) ?: null,
            'created_by' => $existing ? ($existing['created_by'] ?? $userId) : $userId,
        ];

        if ($existing) {
            $ok = $this->chargeLineModel->update((int) $existing['id'], $data);
            $lineId = (int) $existing['id'];
        } else {
            $ok = (bool) $this->chargeLineModel->insert($data);
            $lineId = (int) $this->chargeLineModel->getInsertID();
        }

        if (!$ok) {
            $errors = $this->chargeLineModel->errors();
            return [
                'success' => false,
                'message' => $errors ? implode(' ', array_values($errors)) : 'Failed to save treatment charge line.',
            ];
        }

        return [
            'success' => true,
            'line' => $this->chargeLineModel->where('clinic_id', $clinicId)->find($lineId),
        ];
    }

    private function truthy($value): bool
    {
        return in_array($value, [1, '1', true, 'true', 'on', 'yes'], true);
    }

    private function decimal($value, float $fallback): string
    {
        if ($value === null || $value === '') {
            return number_format($fallback, 2, '.', '');
        }

        return number_format(max(0, (float) $value), 2, '.', '');
    }
}
