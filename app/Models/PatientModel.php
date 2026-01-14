<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientModel extends Model
{
    use \App\Traits\TenantTrait;

    protected $table = 'patients';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'patient_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'emergency_contact_name',
        'emergency_contact_phone',
        'medical_history',
        'allergies',
        'insurance_provider',
        'insurance_number',
        'notes',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'first_name' => 'required|min_length[2]|max_length[50]',
        'last_name' => 'required|min_length[2]|max_length[50]',
        'email' => 'permit_empty|valid_email',
        'phone' => 'required|min_length[10]|max_length[15]',
        'date_of_birth' => 'required|valid_date',
        'gender' => 'required|in_list[male,female,other]'
    ];

    protected $validationMessages = [
        'first_name' => [
            'required' => 'First name is required',
            'min_length' => 'First name must be at least 2 characters long',
            'max_length' => 'First name cannot exceed 50 characters'
        ],
        'last_name' => [
            'required' => 'Last name is required',
            'min_length' => 'Last name must be at least 2 characters long',
            'max_length' => 'Last name cannot exceed 50 characters'
        ],
        'email' => [
            'valid_email' => 'Please enter a valid email address',
            'is_unique' => 'This email is already registered'
        ],
        'phone' => [
            'required' => 'Phone number is required',
            'min_length' => 'Phone number must be at least 10 digits',
            'max_length' => 'Phone number cannot exceed 15 digits'
        ],
        'date_of_birth' => [
            'required' => 'Date of birth is required',
            'valid_date' => 'Please enter a valid date'
        ],
        'gender' => [
            'required' => 'Gender is required',
            'in_list' => 'Please select a valid gender'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $beforeInsert = ['generatePatientId', 'setClinicId'];
    protected $beforeUpdate = [];

    protected function generatePatientId(array $data)
    {
        if (!isset($data['data']['patient_id'])) {
            $data['data']['patient_id'] = 'PAT' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }
        return $data;
    }

    public function getPatientByPatientId($patientId)
    {
        return $this->where('patient_id', $patientId)->first();
    }

    public function searchPatientsByClinic($clinicId, $searchTerm = null, $limit = 20)
    {
        $builder = $this->where('clinic_id', $clinicId)
                        ->where('status', 'active');

        if (!empty($searchTerm)) {
            $builder->groupStart()
                ->like('first_name', $searchTerm)
                ->orLike('last_name', $searchTerm)
                ->orLike('email', $searchTerm)
                ->orLike('phone', $searchTerm)
                ->orLike('patient_id', $searchTerm)
                ->groupEnd();
        }

        return $builder->orderBy('first_name', 'ASC')
                       ->limit($limit)
                       ->findAll();
    }

    public function searchPatients($searchTerm)
    {
        return $this->groupStart()
            ->like('first_name', $searchTerm)
            ->orLike('last_name', $searchTerm)
            ->orLike('email', $searchTerm)
            ->orLike('phone', $searchTerm)
            ->orLike('patient_id', $searchTerm)
            ->groupEnd()
            ->findAll();
    }

    public function getPatientsWithStats()
    {
        return $this->select('patients.*, 
                             COUNT(examinations.id) as total_examinations,
                             TIMESTAMPDIFF(YEAR, patients.date_of_birth, CURDATE()) as age,
                             (SELECT MAX(a.appointment_date) FROM appointments a WHERE a.patient_id = patients.id AND a.deleted_at IS NULL) as last_visit')
            ->join('examinations', 'examinations.patient_id = patients.id', 'left')
            ->where('patients.deleted_at', null)
            ->groupBy('patients.id')
            ->paginate(10);
    }
}
