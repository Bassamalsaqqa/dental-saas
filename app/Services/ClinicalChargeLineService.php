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

    public function getInvoiceableForPatient(int $clinicId, int $patientId): array
    {
        return $this->chargeLineModel
            ->where('clinic_id', $clinicId)
            ->where('patient_id', $patientId)
            ->whereNotIn('status', ['invoiced', 'voided'])
            ->orderBy('service_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function getRowsForFinance(int $clinicId, int $financeId): array
    {
        return $this->chargeLineModel
            ->select('clinical_charge_lines.*, patients.patient_id AS patient_number, patients.first_name, patients.last_name, examinations.examination_id AS examination_number, treatments.treatment_id AS treatment_number, treatments.treatment_name, users.first_name AS provider_first_name, users.last_name AS provider_last_name')
            ->join('patients', 'patients.id = clinical_charge_lines.patient_id AND patients.clinic_id = clinical_charge_lines.clinic_id', 'left')
            ->join('examinations', 'examinations.id = clinical_charge_lines.examination_id AND examinations.clinic_id = clinical_charge_lines.clinic_id', 'left')
            ->join('treatments', 'treatments.id = clinical_charge_lines.treatment_id AND treatments.clinic_id = clinical_charge_lines.clinic_id', 'left')
            ->join('users', 'users.id = clinical_charge_lines.provider_user_id', 'left')
            ->where('clinical_charge_lines.clinic_id', $clinicId)
            ->where('clinical_charge_lines.finance_id', $financeId)
            ->orderBy('clinical_charge_lines.service_date', 'ASC')
            ->orderBy('clinical_charge_lines.id', 'ASC')
            ->findAll();
    }

    public function prepareInvoiceSelection(int $clinicId, int $patientId, array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
        if ($ids === []) {
            return ['success' => true, 'rows' => [], 'total' => 0.0, 'currency' => null, 'service_details' => '', 'examination_id' => null];
        }

        $rows = $this->chargeLineModel
            ->where('clinic_id', $clinicId)
            ->where('patient_id', $patientId)
            ->whereIn('id', $ids)
            ->whereNotIn('status', ['invoiced', 'voided'])
            ->findAll();

        if (count($rows) !== count($ids)) {
            return ['success' => false, 'message' => 'One or more selected clinical charge lines are no longer available.'];
        }

        $currencies = array_values(array_unique(array_map(static fn (array $row): string => (string) ($row['currency'] ?? 'ILS'), $rows)));
        if (count($currencies) !== 1) {
            return ['success' => false, 'message' => 'Selected clinical charge lines must use the same currency.'];
        }

        $total = 0.0;
        $details = [];
        $examinationIds = [];
        foreach ($rows as $row) {
            $amount = max(0, (float) ($row['net_production_amount'] ?? 0));
            $total += $amount;
            $label = (string) ($row['procedure_name'] ?? 'Clinical charge');
            $details[] = $label . ' - ' . number_format($amount, 2) . ' ' . $currencies[0];
            if (!empty($row['examination_id'])) {
                $examinationIds[] = (int) $row['examination_id'];
            }
        }

        if ($total <= 0) {
            return ['success' => false, 'message' => 'Selected clinical charge lines must have a billable amount.'];
        }

        $examinationIds = array_values(array_unique($examinationIds));

        return [
            'success' => true,
            'rows' => $rows,
            'total' => round($total, 2),
            'currency' => $currencies[0],
            'service_details' => implode("\n", $details),
            'examination_id' => count($examinationIds) === 1 ? $examinationIds[0] : null,
        ];
    }

    /** The invoice creator owns the transaction; a stale selection must roll it back. */
    public function markInvoiced(int $clinicId, array $ids, int $financeId, ?array $expectedRows = null): bool
    {
        $db = $this->chargeLineModel->db;
        if ((int) $db->transDepth !== 1 || $db->transStatus() === false || $clinicId <= 0 || $financeId <= 0) {
            throw new \DomainException('Clinical charge claiming requires one healthy invoice transaction.');
        }
        foreach ($ids as $id) {
            if ((!is_int($id) && !is_string($id)) || !preg_match('/^[1-9][0-9]*$/D', (string) $id)
                || strlen((string) $id) > 10 || (float) $id > 2147483647) {
                return false;
            }
        }
        $ids = array_values(array_unique(array_map('intval', $ids)));
        sort($ids, SORT_NUMERIC);
        if ($ids === []) { return true; }
        $table = static fn (string $name): string => $db->escapeIdentifiers($db->prefixTable($name));
        $invoice = $db->query('SELECT id, clinic_id, patient_id, transaction_type FROM ' . $table('finances')
            . ' WHERE clinic_id = ? AND id = ? FOR UPDATE NOWAIT', [$clinicId, $financeId]);
        if ($invoice === false || !($invoice = $invoice->getRowArray())
            || $invoice['transaction_type'] !== 'invoice' || (int) $invoice['patient_id'] <= 0) {
            return false;
        }
        $rows = $db->query('SELECT * FROM ' . $table('clinical_charge_lines')
            . ' WHERE clinic_id = ? AND id IN (' . implode(',', array_fill(0, count($ids), '?')) . ') ORDER BY id FOR UPDATE NOWAIT',
            array_merge([$clinicId], $ids));
        if ($rows === false) { return false; }
        $rows = $rows->getResultArray();
        if (count($rows) !== count($ids)) { return false; }
        $expectedById = [];
        if ($expectedRows !== null) {
            foreach ($expectedRows as $expected) {
                if (!is_array($expected) || !isset($expected['id']) || isset($expectedById[(int) $expected['id']])) { return false; }
                $expectedById[(int) $expected['id']] = $expected;
            }
            if (count($expectedById) !== count($ids)) { return false; }
        }
        foreach ($rows as $row) {
            if ((int) $row['patient_id'] !== (int) $invoice['patient_id']
                || !in_array($row['status'], ['planned', 'accepted', 'completed'], true)
                || !empty($row['finance_id'])) { return false; }
            if ($expectedRows !== null) {
                $expected = $expectedById[(int) $row['id']] ?? null;
                if ($expected === null) { return false; }
                ksort($row); ksort($expected);
                if ($row !== $expected) { return false; }
            }
        }
        return (bool) $db->table('clinical_charge_lines')->where('clinic_id', $clinicId)
            ->where('patient_id', (int) $invoice['patient_id'])->whereIn('id', $ids)
            ->whereIn('status', ['planned', 'accepted', 'completed'])->where('finance_id', null)
            ->update(['finance_id' => $financeId, 'status' => 'invoiced', 'updated_at' => date('Y-m-d H:i:s')])
            && $db->affectedRows() === count($ids);
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

        $appointmentId = array_key_exists('appointment_id', $payload)
            ? (!empty($payload['appointment_id']) ? (int) $payload['appointment_id'] : null)
            : (!empty($existing['appointment_id']) ? (int) $existing['appointment_id'] : null);
        try {
            (new ClinicalAppointmentContextService())->resolve(
                $clinicId,
                (int) ($treatment['patient_id'] ?? 0),
                $appointmentId
            );
        } catch (\DomainException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        $data = [
            'clinic_id' => $clinicId,
            'patient_id' => (int) ($treatment['patient_id'] ?? 0),
            'appointment_id' => $appointmentId,
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
