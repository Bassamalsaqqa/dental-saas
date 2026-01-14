<?php

namespace App\Models;

use CodeIgniter\Model;

class TreatmentModel extends Model
{
    use \App\Traits\TenantTrait;

    protected $table = 'treatments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'patient_id',
        'examination_id',
        'treatment_id',
        'treatment_name',
        'treatment_type',
        'tooth_number',
        'tooth_numbers',
        'treatment_description',
        'treatment_notes',
        'materials_used',
        'cost',
        'status',
        'start_date',
        'estimated_duration',
        'completion_date',
        'end_date',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'patient_id' => 'required|integer',
        'treatment_type' => 'required|in_list[cleaning,extraction,filling,crown,root_canal,orthodontic,implant,other]',
        'tooth_number' => 'permit_empty|string|max_length[10]',
        'treatment_description' => 'required|min_length[10]',
        'cost' => 'required|decimal|greater_than[0]',
        'start_date' => 'required|valid_date',
        'estimated_duration' => 'required|integer|greater_than[0]',
        'status' => 'required|in_list[active,completed,cancelled,on_hold]',
    ];

    protected $validationMessages = [
        'patient_id' => [
            'required' => 'Patient is required',
            'integer' => 'Invalid patient selection'
        ],
        'treatment_type' => [
            'required' => 'Treatment type is required',
            'in_list' => 'Invalid treatment type'
        ],
        'treatment_description' => [
            'required' => 'Description is required',
            'min_length' => 'Description must be at least 10 characters long'
        ],
        'cost' => [
            'required' => 'Cost is required',
            'decimal' => 'Cost must be a valid decimal number',
            'greater_than' => 'Cost must be greater than 0'
        ],
        'start_date' => [
            'required' => 'Start date is required',
            'valid_date' => 'Invalid date format'
        ],
        'estimated_duration' => [
            'required' => 'Estimated duration is required',
            'integer' => 'Duration must be a valid number',
            'greater_than' => 'Duration must be greater than 0'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Invalid status'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['generateTreatmentId', 'setClinicId'];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function searchTreatmentsByClinic($clinicId, $searchTerm = null, $limit = 20, $status = null)
    {
        $builder = $this->select('treatments.*, patients.first_name, patients.last_name, patients.patient_id')
            ->join('patients', 'patients.id = treatments.patient_id')
            ->where('treatments.clinic_id', $clinicId)
            ->where('patients.clinic_id', $clinicId); // Join guard

        if ($status) {
            $builder->where('treatments.status', $status);
        }

        if (!empty($searchTerm)) {
            $builder->groupStart()
                ->like('treatments.treatment_type', $searchTerm)
                ->orLike('treatments.description', $searchTerm)
                ->orLike('patients.first_name', $searchTerm)
                ->orLike('patients.last_name', $searchTerm)
                ->orLike('patients.patient_id', $searchTerm)
            ->groupEnd();
        }

        return $builder->orderBy('treatments.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getTreatmentsWithPatientInfo()
    {
        return $this->select('treatments.*, patients.first_name, patients.last_name, patients.phone, patients.email')
                    ->join('patients', 'patients.id = treatments.patient_id')
                    ->orderBy('treatments.created_at', 'DESC')
                    ->findAll();
    }

    public function getTreatmentWithPatientInfo($id)
    {
        return $this->select('treatments.*, patients.first_name, patients.last_name, patients.phone, patients.email, patients.date_of_birth, patients.gender')
                    ->join('patients', 'patients.id = treatments.patient_id')
                    ->where('treatments.id', $id)
                    ->first();
    }

    public function getTreatmentsByPatient($patientId)
    {
        return $this->where('patient_id', $patientId)
                    ->orderBy('start_date', 'DESC')
                    ->findAll();
    }

    public function getActiveTreatments()
    {
        return $this->where('status', 'active')
                    ->orderBy('start_date', 'ASC')
                    ->findAll();
    }

    public function getCompletedTreatments($startDate = null, $endDate = null)
    {
        $query = $this->where('status', 'completed');
        
        if ($startDate) {
            $query->where('completion_date >=', $startDate);
        }
        
        if ($endDate) {
            $query->where('completion_date <=', $endDate);
        }
        
        return $query->orderBy('completion_date', 'DESC')->findAll();
    }

    public function getTreatmentStats()
    {
        try {
            return [
                'total' => $this->countAllResults(),
                'active' => $this->where('status', 'active')->countAllResults(),
                'completed' => $this->where('status', 'completed')->countAllResults(),
                'cancelled' => $this->where('status', 'cancelled')->countAllResults(),
                'on_hold' => $this->where('status', 'on_hold')->countAllResults(),
                'active_treatments' => $this->where('status', 'active')->countAllResults(),
                'completed_treatments' => $this->where('status', 'completed')->countAllResults(),
            ];
        } catch (\Exception $e) {
            log_message('error', 'Treatment stats error: ' . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'on_hold' => 0,
                'active_treatments' => 0,
                'completed_treatments' => 0,
            ];
        }
    }

    public function getTreatmentTypesStats()
    {
        return $this->select('treatment_type, COUNT(*) as count')
                    ->groupBy('treatment_type')
                    ->findAll();
    }

    public function getTreatmentsByType()
    {
        return $this->select('treatment_type, COUNT(*) as count')
                    ->groupBy('treatment_type')
                    ->findAll();
    }

    public function getMonthlyTreatments($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        return $this->select('MONTH(start_date) as month, COUNT(*) as count')
                    ->where('YEAR(start_date)', $year)
                    ->groupBy('MONTH(start_date)')
                    ->orderBy('month', 'ASC')
                    ->findAll();
    }

    public function getRevenueByTreatmentType($startDate = null, $endDate = null)
    {
        $query = $this->select('treatment_type, SUM(cost) as total_revenue, COUNT(*) as count')
                     ->where('status', 'completed');
        
        if ($startDate) {
            $query->where('completion_date >=', $startDate);
        }
        
        if ($endDate) {
            $query->where('completion_date <=', $endDate);
        }
        
        return $query->groupBy('treatment_type')->findAll();
    }

    public function getOverdueTreatments()
    {
        $today = date('Y-m-d');
        return $this->where('status', 'active')
                    ->where('start_date <', $today)
                    ->where('estimated_duration >', 0)
                    ->findAll();
    }

    public function searchTreatments($searchTerm)
    {
        return $this->select('treatments.*, patients.first_name, patients.last_name')
                    ->join('patients', 'patients.id = treatments.patient_id')
                    ->groupStart()
                        ->like('treatments.description', $searchTerm)
                        ->orLike('treatments.treatment_type', $searchTerm)
                        ->orLike('patients.first_name', $searchTerm)
                        ->orLike('patients.last_name', $searchTerm)
                    ->groupEnd()
                    ->orderBy('treatments.created_at', 'DESC')
                    ->findAll();
    }

    public function getTreatmentsByExamination($examinationId)
    {
        return $this->where('examination_id', $examinationId)
                    ->orderBy('start_date', 'ASC')
                    ->findAll();
    }
    
    protected function generateTreatmentId(array $data)
    {
        if (!isset($data['data']['treatment_id'])) {
            $data['data']['treatment_id'] = 'TRT' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        }
        return $data;
    }
}