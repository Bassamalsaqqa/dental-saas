<?php

namespace App\Models;

use CodeIgniter\Model;

class AppointmentModel extends Model
{
    use \App\Traits\TenantTrait;

    protected $table = 'appointments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'patient_id',
        'appointment_id',
        'appointment_date',
        'appointment_time',
        'duration',
        'appointment_type',
        'status',
        'notes',
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
        'appointment_date' => 'required|valid_date',
        'appointment_time' => 'required',
        'duration' => 'required|integer|greater_than[0]',
        'appointment_type' => 'required|in_list[consultation,treatment,follow_up,emergency,cleaning,checkup]',
        'status' => 'required|in_list[scheduled,confirmed,completed,cancelled,no_show]'
    ];

    protected $validationMessages = [
        'patient_id' => [
            'required' => 'Patient is required',
            'integer' => 'Invalid patient selection'
        ],
        'appointment_date' => [
            'required' => 'Appointment date is required',
            'valid_date' => 'Please enter a valid date'
        ],
        'appointment_time' => [
            'required' => 'Appointment time is required'
        ],
        'duration' => [
            'required' => 'Duration is required',
            'integer' => 'Duration must be a valid number',
            'greater_than' => 'Duration must be greater than 0'
        ],
        'appointment_type' => [
            'required' => 'Appointment type is required',
            'in_list' => 'Please select a valid appointment type'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Please select a valid status'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $beforeInsert = ['generateAppointmentId', 'setClinicId'];
    protected $beforeUpdate = [];

    protected function generateAppointmentId(array $data)
    {
        if (!isset($data['data']['appointment_id'])) {
            $data['data']['appointment_id'] = 'APT' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        }
        return $data;
    }

    public function getAppointmentWithPatient($appointmentId)
    {
        return $this->select('appointments.*, patients.first_name, patients.last_name, patients.patient_id as patient_number, patients.phone, patients.email')
            ->join('patients', 'patients.id = appointments.patient_id')
            ->where('appointments.id', $appointmentId)
            ->first();
    }

    public function getAppointmentsByDate($date)
    {
        return $this->select('appointments.*, patients.first_name, patients.last_name, patients.patient_id as patient_number, patients.phone, patients.email')
            ->join('patients', 'patients.id = appointments.patient_id')
            ->where('DATE(appointment_date)', $date)
            ->orderBy('appointment_time', 'ASC')
            ->findAll();
    }

    public function getAllAppointments($limit = 10, $offset = 0, $search = '', $status = '')
    {
        $query = $this->select('appointments.*, patients.first_name, patients.last_name, patients.patient_id as patient_number, patients.phone, patients.email')
            ->join('patients', 'patients.id = appointments.patient_id');
        
        // Apply search filter
        if (!empty($search)) {
            $query->groupStart()
                ->like('patients.first_name', $search)
                ->orLike('patients.last_name', $search)
                ->orLike('patients.phone', $search)
                ->orLike('patients.email', $search)
                ->orLike('appointments.appointment_type', $search)
                ->orLike('appointments.notes', $search)
                ->groupEnd();
        }
        
        // Apply status filter
        if (!empty($status)) {
            $query->where('appointments.status', $status);
        }
        
        return $query->orderBy('appointment_date', 'DESC')
            ->orderBy('appointment_time', 'ASC')
            ->limit($limit, $offset)
            ->findAll();
    }

    public function getAllAppointmentsCount($search = '', $status = '')
    {
        $query = $this->select('COUNT(*) as total')
            ->join('patients', 'patients.id = appointments.patient_id');
        
        // Apply search filter
        if (!empty($search)) {
            $query->groupStart()
                ->like('patients.first_name', $search)
                ->orLike('patients.last_name', $search)
                ->orLike('patients.phone', $search)
                ->orLike('patients.email', $search)
                ->orLike('appointments.appointment_type', $search)
                ->orLike('appointments.notes', $search)
                ->groupEnd();
        }
        
        // Apply status filter
        if (!empty($status)) {
            $query->where('appointments.status', $status);
        }
        
        $result = $query->get()->getRow();
        return $result ? $result->total : 0;
    }

    public function getAppointmentsByPatient($patientId)
    {
        return $this->where('patient_id', $patientId)
            ->orderBy('appointment_date', 'DESC')
            ->orderBy('appointment_time', 'DESC')
            ->findAll();
    }

    public function getUpcomingAppointments($limit = 10)
    {
        try {
            return $this->select('appointments.*, patients.first_name, patients.last_name, patients.patient_id as patient_number, patients.phone, patients.email')
                ->join('patients', 'patients.id = appointments.patient_id')
                ->where('appointments.appointment_date >=', date('Y-m-d'))
                ->where('appointments.status', 'scheduled')
                ->orderBy('appointments.appointment_date', 'ASC')
                ->orderBy('appointments.appointment_time', 'ASC')
                ->limit($limit)
                ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Upcoming appointments error: ' . $e->getMessage());
            return [];
        }
    }

    public function getExaminationsByDateRange($startDate, $endDate)
    {
        return $this->select('appointments.*, patients.first_name, patients.last_name, patients.patient_id as patient_number, patients.phone, patients.email')
            ->join('patients', 'patients.id = appointments.patient_id')
            ->where('appointment_date >=', $startDate)
            ->where('appointment_date <=', $endDate)
            ->orderBy('appointment_date', 'ASC')
            ->orderBy('appointment_time', 'ASC')
            ->findAll();
    }

    public function getAppointmentStats()
    {
        try {
            $builder = $this->db->table('appointments');
            
            return [
                'total_appointments' => $builder->countAllResults(false),
                'today_appointments' => $builder->where('DATE(appointment_date)', date('Y-m-d'))->countAllResults(false),
                'upcoming_appointments' => $builder->where('appointment_date >=', date('Y-m-d'))->where('status', 'scheduled')->countAllResults(false),
                'completed_appointments' => $builder->where('status', 'completed')->countAllResults(false),
                'cancelled_appointments' => $builder->where('status', 'cancelled')->countAllResults(false)
            ];
        } catch (\Exception $e) {
            log_message('error', 'Appointment stats error: ' . $e->getMessage());
            return [
                'total_appointments' => 0,
                'today_appointments' => 0,
                'upcoming_appointments' => 0,
                'completed_appointments' => 0,
                'cancelled_appointments' => 0
            ];
        }
    }

    public function checkTimeSlotAvailability($date, $time, $duration, $excludeId = null)
    {
        $builder = $this->db->table('appointments');
        $builder->where('DATE(appointment_date)', $date);
        $builder->where('status !=', 'cancelled');
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        $appointments = $builder->get()->getResultArray();
        
        $requestedStart = strtotime($time);
        $requestedEnd = $requestedStart + ($duration * 60);
        
        foreach ($appointments as $appointment) {
            $appointmentStart = strtotime($appointment['appointment_time']);
            $appointmentEnd = $appointmentStart + ($appointment['duration'] * 60);
            
            if (($requestedStart < $appointmentEnd) && ($requestedEnd > $appointmentStart)) {
                return false;
            }
        }
        
        return true;
    }
}
