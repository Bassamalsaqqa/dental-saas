<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class ClinicalDocumentationQueueService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= \Config\Database::connect();
    }

    public function summary(int $clinicId, int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        $today = date('Y-m-d');

        $readyBuilder = $this->baseQuery($clinicId)
            ->where('examinations.status', 'completed')
            ->where('examination_note_signatures.id IS NULL', null, false);
        $overdueBuilder = $this->baseQuery($clinicId)
            ->whereIn('examinations.status', ['pending', 'in_progress'])
            ->where('examinations.examination_date <', $today);

        return [
            'ready_to_sign_count' => (clone $readyBuilder)->countAllResults(),
            'overdue_draft_count' => (clone $overdueBuilder)->countAllResults(),
            'ready_to_sign' => $this->items($readyBuilder, $limit),
            'overdue_drafts' => $this->items($overdueBuilder, $limit),
        ];
    }

    private function baseQuery(int $clinicId)
    {
        return $this->db->table('examinations')
            ->join('patients', 'patients.id = examinations.patient_id AND patients.clinic_id = examinations.clinic_id')
            ->join(
                'examination_note_signatures',
                'examination_note_signatures.examination_id = examinations.id AND examination_note_signatures.clinic_id = examinations.clinic_id',
                'left'
            )
            ->where('examinations.clinic_id', $clinicId);
    }

    private function items($builder, int $limit): array
    {
        return $builder
            ->select('examinations.id, examinations.examination_id, examinations.examination_date, examinations.status, examinations.examination_type, examinations.patient_id, patients.first_name, patients.last_name, patients.patient_id AS patient_number')
            ->orderBy('examinations.examination_date', 'ASC')
            ->orderBy('examinations.id', 'ASC')
            ->limit($limit)
            ->get()->getResultArray();
    }
}
