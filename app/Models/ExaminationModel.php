<?php

namespace App\Models;

use CodeIgniter\Model;

class ExaminationModel extends TenantAwareModel
{
    protected $table = 'examinations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'patient_id',
        'appointment_id',
        'examination_id',
        'examination_date',
        'examination_type',
        'chief_complaint',
        'history_of_present_illness',
        'medical_history',
        'health_update_medical_changes',
        'health_update_medication_changes',
        'health_update_allergies_reactions',
        'health_update_pregnancy',
        'health_update_current_symptoms',
        'health_update_details',
        'dental_history',
        'clinical_findings',
        'diagnosis',
        'prognosis',
        'recommendations',
        'examination_notes',
        'status',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'patient_id' => 'required|integer',
        'appointment_id' => 'permit_empty|integer',
        'examination_date' => 'required|valid_date',
        'examination_type' => 'required|in_list[initial,periodic,emergency,follow_up]',
        'chief_complaint' => 'required|min_length[10]|max_length[500]'
    ];

    protected $validationMessages = [
        'patient_id' => [
            'required' => 'Patient is required',
            'integer' => 'Invalid patient selection'
        ],
        'examination_date' => [
            'required' => 'Examination date is required',
            'valid_date' => 'Please enter a valid date'
        ],
        'examination_type' => [
            'required' => 'Examination type is required',
            'in_list' => 'Please select a valid examination type'
        ],
        'chief_complaint' => [
            'required' => 'Chief complaint is required',
            'min_length' => 'Chief complaint must be at least 10 characters',
            'max_length' => 'Chief complaint cannot exceed 500 characters'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $beforeInsert = ['generateExaminationId', 'setClinicId'];
    protected $beforeUpdate = ['enforceTenantScope', 'preventSignedClinicalNoteMutation'];
    protected $beforeDelete = ['enforceTenantScope', 'preventSignedClinicalNoteMutation'];

    protected function generateExaminationId(array $data)
    {
        if (!isset($data['data']['examination_id'])) {
            $data['data']['examination_id'] = 'EXM' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        }
        return $data;
    }

    protected function preventSignedClinicalNoteMutation(array $data): array
    {
        if (!$this->db->tableExists('examination_note_signatures')) {
            return $data;
        }

        $ids = $data['id'] ?? [];
        $ids = is_array($ids) ? $ids : [$ids];
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if ($ids === []) {
            return $data;
        }

        $signed = $this->db->table('examination_note_signatures')
            ->whereIn('examination_id', $ids)
            ->countAllResults() > 0;

        if ($signed) {
            throw new \DomainException('Signed clinical notes are immutable. Append an addendum instead.');
        }

        return $data;
    }

    public function searchExaminationsByClinic($clinicId, $searchTerm = null, $limit = 20, $status = 'completed', $patientId = null)
    {
        $builder = $this->select('examinations.*, patients.first_name, patients.last_name, patients.patient_id')
            ->join('patients', 'patients.id = examinations.patient_id')
            ->where('examinations.clinic_id', $clinicId)
            ->where('patients.clinic_id', $clinicId); // Join guard

        if (!empty($status)) {
            $builder->where('examinations.status', $status);
        }

        if (!empty($patientId)) {
            $builder->where('examinations.patient_id', $patientId);
        }

        if (!empty($searchTerm)) {
            $builder->groupStart()
                ->like('examinations.examination_id', $searchTerm)
                ->orLike('patients.first_name', $searchTerm)
                ->orLike('patients.last_name', $searchTerm)
                ->orLike('patients.patient_id', $searchTerm)
                ->orLike('examinations.chief_complaint', $searchTerm)
            ->groupEnd();
        }

        return $builder->orderBy('examinations.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getExaminationsByClinic($clinicId, $limit = 10, $offset = 0, $search = '', $orderColumn = 'examinations.id', $orderDir = 'desc')
    {
        $builder = $this->select('examinations.*, patients.first_name, patients.last_name, patients.gender, patients.patient_id as patient_number')
            ->join('patients', 'patients.id = examinations.patient_id')
            ->where('examinations.clinic_id', $clinicId)
            ->where('patients.clinic_id', $clinicId); // Join guard

        if (!empty($search)) {
            $builder->groupStart()
                ->like('examinations.examination_id', $search)
                ->orLike('patients.first_name', $search)
                ->orLike('patients.last_name', $search)
                ->orLike('patients.patient_id', $search)
                ->orLike('examinations.examination_type', $search)
                ->orLike('examinations.status', $search)
                ->orLike('examinations.chief_complaint', $search)
                ->groupEnd();
        }

        return $builder->orderBy($orderColumn, $orderDir)
            ->limit($limit, $offset)
            ->findAll();
    }

    public function countExaminationsByClinic($clinicId, $search = '')
    {
        $builder = $this->select('COUNT(*) as total')
            ->join('patients', 'patients.id = examinations.patient_id')
            ->where('examinations.clinic_id', $clinicId)
            ->where('patients.clinic_id', $clinicId); // Join guard

        if (!empty($search)) {
            $builder->groupStart()
                ->like('examinations.examination_id', $search)
                ->orLike('patients.first_name', $search)
                ->orLike('patients.last_name', $search)
                ->orLike('patients.patient_id', $search)
                ->orLike('examinations.examination_type', $search)
                ->orLike('examinations.status', $search)
                ->orLike('examinations.chief_complaint', $search)
                ->groupEnd();
        }

        $result = $builder->get()->getRow();
        return $result ? $result->total : 0;
    }

    public function getExaminationStatsByClinic($clinicId)
    {
        try {
            $base = $this->db->table('examinations')
                ->where('clinic_id', $clinicId);

            return [
                'total_examinations' => (clone $base)->countAllResults(),
                'pending_examinations' => (clone $base)->where('status', 'pending')->countAllResults(),
                'completed_examinations' => (clone $base)->where('status', 'completed')->countAllResults(),
                'today_examinations' => (clone $base)->where('status', 'completed')->where('DATE(examination_date)', date('Y-m-d'))->countAllResults(),
                'emergency_examinations' => (clone $base)->where('examination_type', 'emergency')->countAllResults()
            ];
        } catch (\Exception $e) {
            log_message('error', 'Examination stats error: ' . $e->getMessage());
            return [
                'total_examinations' => 0,
                'pending_examinations' => 0,
                'completed_examinations' => 0,
                'today_examinations' => 0,
                'emergency_examinations' => 0
            ];
        }
    }

    public function getRecentExaminationsByClinic($clinicId, $limit = 10)
    {
        try {
            return $this->select('examinations.*, patients.first_name, patients.last_name, patients.patient_id as patient_number')
                ->join('patients', 'patients.id = examinations.patient_id')
                ->where('examinations.clinic_id', $clinicId)
                ->where('patients.clinic_id', $clinicId)
                ->orderBy('examination_date', 'DESC')
                ->limit($limit)
                ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Recent examinations error: ' . $e->getMessage());
            return [];
        }
    }

    public function insertBatchByClinic($clinicId, array $data)
    {
        foreach ($data as &$row) {
            $row['clinic_id'] = $clinicId;
        }
        return $this->db->table('examinations')->insertBatch($data);
    }

    public function getDebugDataByClinic($clinicId)
    {
        $patientModel = new \App\Models\PatientModel();
        
        $examinationCount = $this->where('clinic_id', $clinicId)->countAllResults();
        $patientCount = $patientModel->where('clinic_id', $clinicId)->countAllResults();
        
        $joinQuery = $this->select('examinations.*, patients.first_name, patients.last_name')
            ->join('patients', 'patients.id = examinations.patient_id', 'left')
            ->where('examinations.clinic_id', $clinicId)
            ->where('patients.clinic_id', $clinicId)
            ->limit(1)
            ->findAll();

        return [
            'counts' => [
                'examinations' => $examinationCount,
                'patients' => $patientCount
            ],
            'join_test' => $joinQuery
        ];
    }

    public function getExaminationWithPatient($examinationId)
    {
        return $this->select('examinations.*, patients.first_name, patients.last_name, patients.patient_id as patient_number')
            ->join('patients', 'patients.id = examinations.patient_id')
            ->where('examinations.id', $examinationId)
            ->first();
    }

    public function getExaminationsByPatient($patientId)
    {
        return $this->where('patient_id', $patientId)
            ->orderBy('examination_date', 'DESC')
            ->findAll();
    }

    public function getRecentExaminations($limit = 10)
    {
        try {
            return $this->select('examinations.*, patients.first_name, patients.last_name, patients.patient_id as patient_number')
                ->join('patients', 'patients.id = examinations.patient_id')
                ->orderBy('examination_date', 'DESC')
                ->limit($limit)
                ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Recent examinations error: ' . $e->getMessage());
            return [];
        }
    }

    public function getExaminationsByDateRange($clinicId, $startDate, $endDate)
    {
        return $this->select(
            "examinations.*, patients.first_name, patients.last_name, patients.patient_id as patient_number, " .
            "CONCAT(patients.first_name, ' ', patients.last_name) AS patient_name"
        )
            ->join('patients', 'patients.id = examinations.patient_id', 'left')
            ->where('examinations.clinic_id', $clinicId)
            ->where('patients.clinic_id', $clinicId)
            ->where('examinations.examination_date >=', $startDate)
            ->where('examinations.examination_date <=', $endDate)
            ->orderBy('examinations.examination_date', 'DESC')
            ->findAll();
    }

    public function getExaminationStats()
    {
        try {
            $builder = $this->db->table('examinations');
            
            return [
                'total_examinations' => $builder->countAllResults(false),
                'pending_examinations' => $builder->where('status', 'pending')->countAllResults(false),
                'completed_examinations' => $builder->where('status', 'completed')->countAllResults(false),
                'today_examinations' => $builder->where('status', 'completed')->where('DATE(examination_date)', date('Y-m-d'))->countAllResults(false),
                'emergency_examinations' => $builder->where('examination_type', 'emergency')->countAllResults(false)
            ];
        } catch (\Exception $e) {
            log_message('error', 'Examination stats error: ' . $e->getMessage());
            return [
                'total_examinations' => 0,
                'pending_examinations' => 0,
                'completed_examinations' => 0,
                'today_examinations' => 0,
                'emergency_examinations' => 0
            ];
        }
    }
}
