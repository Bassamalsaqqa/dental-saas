<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

final class AcceptedTreatmentQueueService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= Database::connect();
    }

    public function summary(int $clinicId, int $limit = 5): array
    {
        if ($clinicId <= 0) {
            return ['count' => 0, 'items' => []];
        }

        $base = $this->db->table('clinical_procedures cp')
            ->where('cp.clinic_id', $clinicId)
            ->where('cp.status', 'accepted')
            ->where('cp.scheduled_appointment_id', null)
            ->where('cp.treatment_id IS NOT NULL', null, false);
        $count = (clone $base)->countAllResults();
        $items = $base
            ->select('cp.id AS procedure_id, cp.treatment_id, cp.patient_id, cp.procedure_name, cp.tooth_numbers, cp.accepted_at, p.first_name, p.last_name, p.patient_id AS patient_number')
            ->join('patients p', 'p.id = cp.patient_id AND p.clinic_id = cp.clinic_id', 'inner')
            ->orderBy('cp.accepted_at', 'ASC')
            ->limit(max(1, $limit))
            ->get()
            ->getResultArray();

        return ['count' => $count, 'items' => $items];
    }
}
