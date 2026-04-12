<?php

namespace App\Services;

use App\Models\OdontogramModel;
use App\Models\PatientOdontogramSnapshotModel;

class OdontogramSnapshotService
{
    private OdontogramModel $odontogramModel;
    private PatientOdontogramSnapshotModel $snapshotModel;

    public function __construct()
    {
        $this->odontogramModel = new OdontogramModel();
        $this->snapshotModel = new PatientOdontogramSnapshotModel();
    }

    public function capturePatientBaselineSnapshot(
        int $clinicId,
        int $patientId,
        string $sourceType,
        ?int $sourceId = null,
        ?int $createdBy = null,
        ?string $label = null
    ): ?int {
        $rows = $this->odontogramModel
            ->where('clinic_id', $clinicId)
            ->getOdontogramByPatient($patientId);

        $normalizedRows = [];
        foreach ($rows as $row) {
            if (!isset($row['tooth_number'])) {
                continue;
            }

            $normalizedRows[] = [
                'tooth_number' => (int) $row['tooth_number'],
                'tooth_position' => $row['tooth_position'] ?? null,
                'condition_type' => $row['condition_type'] ?? 'healthy',
                'condition_description' => $row['condition_description'] ?? '',
                'treatment_notes' => $row['treatment_notes'] ?? '',
                'treatment_date' => $row['treatment_date'] ?? null,
                'treatment_status' => $row['treatment_status'] ?? null,
            ];
        }

        if (empty($normalizedRows)) {
            return null;
        }

        $snapshotId = $this->snapshotModel->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'label' => $label,
            'snapshot_json' => json_encode($normalizedRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'snapshot_svg' => null,
            'created_by' => $createdBy,
        ], true);

        return $snapshotId ?: null;
    }
}
