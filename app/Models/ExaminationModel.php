<?php

namespace App\Models;

use CodeIgniter\Model;

class ExaminationModel extends Model
{
    protected $table = 'examinations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'patient_id',
        'examination_id',
        'examination_date',
        'examination_type',
        'chief_complaint',
        'history_of_present_illness',
        'medical_history',
        'dental_history',
        'clinical_findings',
        'diagnosis',
        'treatment_plan',
        'prognosis',
        'recommendations',
        'next_appointment',
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

    protected $beforeInsert = ['generateExaminationId'];
    protected $beforeUpdate = [];

    protected function generateExaminationId(array $data)
    {
        if (!isset($data['data']['examination_id'])) {
            $data['data']['examination_id'] = 'EXM' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        }
        return $data;
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

    public function getExaminationsByDateRange($startDate, $endDate)
    {
        return $this->select('examinations.*, patients.first_name, patients.last_name, patients.patient_id as patient_number')
            ->join('patients', 'patients.id = examinations.patient_id')
            ->where('examination_date >=', $startDate)
            ->where('examination_date <=', $endDate)
            ->orderBy('examination_date', 'DESC')
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
