<?php

namespace App\Models;

use CodeIgniter\Model;

class PrescriptionModel extends Model
{
    protected $table = 'prescriptions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'prescription_id',
        'patient_id',
        'medication_name',
        'dosage',
        'frequency',
        'duration',
        'instructions',
        'prescribed_date',
        'expiry_date',
        'status',
        'notes',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'patient_id' => 'required|integer',
        'medication_name' => 'required|min_length[2]',
        'dosage' => 'permit_empty',
        'frequency' => 'permit_empty',
        'duration' => 'permit_empty',
        'instructions' => 'permit_empty|min_length[10]',
        'prescribed_date' => 'required|valid_date',
        'expiry_date' => 'permit_empty|valid_date',
        'status' => 'required|in_list[active,expired,cancelled,pending]',
    ];

    protected $validationMessages = [
        'patient_id' => [
            'required' => 'Patient is required',
            'integer' => 'Invalid patient selection'
        ],
        'medication_name' => [
            'required' => 'Medication data is required',
            'min_length' => 'Medication data must be valid'
        ],
        'instructions' => [
            'min_length' => 'Instructions must be at least 10 characters long if provided'
        ],
        'prescribed_date' => [
            'required' => 'Prescribed date is required',
            'valid_date' => 'Invalid date format'
        ],
        'expiry_date' => [
            'valid_date' => 'Invalid date format'
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
    protected $beforeInsert = ['generatePrescriptionId'];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    protected function generatePrescriptionId(array $data)
    {
        if (!isset($data['data']['prescription_id'])) {
            // Generate a unique prescription ID: RX-{YYYYMMDD}-{UniqueHash}
            $data['data']['prescription_id'] = 'RX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        }
        return $data;
    }

    public function getPrescriptionsWithPatientInfo()
    {
        return $this->select('prescriptions.*, patients.first_name, patients.last_name, patients.phone, patients.email')
                    ->join('patients', 'patients.id = prescriptions.patient_id')
                    ->orderBy('prescriptions.created_at', 'DESC')
                    ->findAll();
    }

    public function getPrescriptionWithPatientInfo($id)
    {
        return $this->select('prescriptions.*, patients.first_name, patients.last_name, patients.phone, patients.email, patients.date_of_birth, patients.gender')
                    ->join('patients', 'patients.id = prescriptions.patient_id')
                    ->where('prescriptions.id', $id)
                    ->first();
    }

    public function getPrescriptionsByPatient($patientId)
    {
        return $this->where('patient_id', $patientId)
                    ->orderBy('prescribed_date', 'DESC')
                    ->findAll();
    }

    public function getActivePrescriptions()
    {
        return $this->where('status', 'active')
                    ->where('expiry_date >', date('Y-m-d'))
                    ->orderBy('prescribed_date', 'DESC')
                    ->findAll();
    }

    public function getExpiredPrescriptions()
    {
        return $this->where('expiry_date <', date('Y-m-d'))
                    ->where('status', 'active')
                    ->orderBy('expiry_date', 'DESC')
                    ->findAll();
    }

    public function getExpiringPrescriptions($days = 7)
    {
        $expiryDate = date('Y-m-d', strtotime("+{$days} days"));
        return $this->where('expiry_date <=', $expiryDate)
                    ->where('expiry_date >=', date('Y-m-d'))
                    ->where('status', 'active')
                    ->orderBy('expiry_date', 'ASC')
                    ->findAll();
    }

    public function getPrescriptionStats()
    {
        return [
            'total' => $this->countAllResults(),
            'active' => $this->where('status', 'active')->countAllResults(),
            'expired' => $this->where('status', 'expired')->countAllResults(),
            'cancelled' => $this->where('status', 'cancelled')->countAllResults(),
        ];
    }

    public function getMedicationStats()
    {
        return $this->select('medication_name, COUNT(*) as count')
                    ->groupBy('medication_name')
                    ->orderBy('count', 'DESC')
                    ->findAll();
    }

    public function getMonthlyPrescriptions($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        return $this->select('MONTH(prescribed_date) as month, COUNT(*) as count')
                    ->where('YEAR(prescribed_date)', $year)
                    ->groupBy('MONTH(prescribed_date)')
                    ->orderBy('month', 'ASC')
                    ->findAll();
    }

    public function searchPrescriptions($searchTerm)
    {
        return $this->select('prescriptions.*, patients.first_name, patients.last_name')
                    ->join('patients', 'patients.id = prescriptions.patient_id')
                    ->groupStart()
                        ->like('prescriptions.medication_name', $searchTerm)
                        ->orLike('prescriptions.instructions', $searchTerm)
                        ->orLike('patients.first_name', $searchTerm)
                        ->orLike('patients.last_name', $searchTerm)
                    ->groupEnd()
                    ->orderBy('prescriptions.created_at', 'DESC')
                    ->findAll();
    }

    public function getPrescriptionsByDateRange($startDate, $endDate)
    {
        return $this->where('prescribed_date >=', $startDate)
                    ->where('prescribed_date <=', $endDate)
                    ->orderBy('prescribed_date', 'DESC')
                    ->findAll();
    }

    public function updateExpiredStatus()
    {
        $today = date('Y-m-d');
        return $this->where('expiry_date <', $today)
                    ->where('status', 'active')
                    ->set('status', 'expired')
                    ->update();
    }

    public function getPrescriptionHistory($patientId, $limit = 10)
    {
        return $this->where('patient_id', $patientId)
                    ->orderBy('prescribed_date', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}
