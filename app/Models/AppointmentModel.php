<?php

namespace App\Models;

use CodeIgniter\Model;

class AppointmentModel extends TenantAwareModel
{
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
        'chair_name',
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
        'chair_name' => 'required|max_length[100]',
        'status' => 'required|in_list[scheduled,confirmed,arrived,ready,in_chair,checkout,completed,cancelled,no_show]'
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
        'chair_name' => [
            'required' => 'Chair/room is required',
            'max_length' => 'Chair/room name is too long'
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

    public function getAppointmentsByClinic($clinicId, $limit = 10, $offset = 0, $search = '', $status = '')
    {
        $query = $this->select('appointments.*, patients.first_name, patients.last_name, patients.gender, patients.patient_id as patient_number, patients.phone, patients.email')
            ->join('patients', 'patients.id = appointments.patient_id')
            ->where('appointments.clinic_id', $clinicId)
            ->where('patients.clinic_id', $clinicId); // Extra join guard
        
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

    public function countAppointmentsByClinic($clinicId, $search = '', $status = '')
    {
        $query = $this->select('COUNT(*) as total')
            ->join('patients', 'patients.id = appointments.patient_id')
            ->where('appointments.clinic_id', $clinicId)
            ->where('patients.clinic_id', $clinicId); // Extra join guard
        
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

    public function getAppointmentStatsByClinic($clinicId)
    {
        try {
            $base = $this->db->table('appointments')
                ->where('clinic_id', $clinicId);

            return [
                'total_appointments' => (clone $base)->countAllResults(),
                'today_appointments' => (clone $base)->where('DATE(appointment_date)', date('Y-m-d'))->countAllResults(),
                'upcoming_appointments' => (clone $base)->where('appointment_date >=', date('Y-m-d'))->where('status', 'scheduled')->countAllResults(),
                'completed_appointments' => (clone $base)->where('status', 'completed')->countAllResults(),
                'cancelled_appointments' => (clone $base)->where('status', 'cancelled')->countAllResults()
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

    public function getUpcomingAppointmentsByClinic($clinicId, $limit = 10)
    {
        try {
            return $this->select('appointments.*, patients.first_name, patients.last_name, patients.gender, patients.patient_id as patient_number, patients.phone, patients.email')
                ->join('patients', 'patients.id = appointments.patient_id')
                ->where('appointments.clinic_id', $clinicId)
                ->where('patients.clinic_id', $clinicId)
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

    public function getAppointmentsByDateByClinic($clinicId, $date)
    {
        return $this->select('appointments.*, patients.first_name, patients.last_name, patients.gender, patients.patient_id as patient_number, patients.phone, patients.email')
            ->join('patients', 'patients.id = appointments.patient_id')
            ->where('appointments.clinic_id', $clinicId)
            ->where('patients.clinic_id', $clinicId) // Join guard
            ->where('DATE(appointment_date)', $date)
            ->orderBy('appointment_time', 'ASC')
            ->findAll();
    }

    public function getCalendarAppointmentsByClinic($clinicId, $startDate, $endDate)
    {
        return $this->select('appointments.*, patients.first_name, patients.last_name, patients.gender, patients.patient_id as patient_number, patients.phone, patients.email, users.first_name as creator_first_name, users.last_name as creator_last_name')
            ->join('patients', 'patients.id = appointments.patient_id')
            ->join('users', 'users.id = appointments.created_by', 'left')
            ->where('appointments.clinic_id', $clinicId)
            ->where('patients.clinic_id', $clinicId) // Join guard
            ->where('appointment_date >=', $startDate)
            ->where('appointment_date <=', $endDate)
            ->orderBy('appointment_date', 'ASC')
            ->orderBy('appointment_time', 'ASC')
            ->findAll();
    }

    public function getAppointmentWithPatient($appointmentId, $clinicId = null)
    {
        $builder = $this->select('appointments.*, patients.first_name, patients.last_name, patients.gender, patients.patient_id as patient_number, patients.phone, patients.email, users.first_name as creator_first_name, users.last_name as creator_last_name')
            ->join('patients', 'patients.id = appointments.patient_id')
            ->join('users', 'users.id = appointments.created_by', 'left')
            ->where('appointments.id', $appointmentId);

        if ($clinicId) {
            $builder->where('appointments.clinic_id', $clinicId);
        }

        return $builder->first();
    }

    public function getAppointmentsByDate($date)
    {
        return $this->select('appointments.*, patients.first_name, patients.last_name, patients.gender, patients.patient_id as patient_number, patients.phone, patients.email')
            ->join('patients', 'patients.id = appointments.patient_id')
            ->where('DATE(appointment_date)', $date)
            ->orderBy('appointment_time', 'ASC')
            ->findAll();
    }

    public function getAllAppointments($limit = 10, $offset = 0, $search = '', $status = '')
    {
        $query = $this->select('appointments.*, patients.first_name, patients.last_name, patients.gender, patients.patient_id as patient_number, patients.phone, patients.email')
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

    public function getNextAppointmentForPatient($clinicId, $patientId)
    {
        return $this->select('appointments.*')
            ->where('appointments.clinic_id', $clinicId)
            ->where('appointments.patient_id', $patientId)
            ->where('appointments.appointment_date >=', date('Y-m-d'))
            ->whereIn('appointments.status', ['scheduled', 'confirmed'])
            ->orderBy('appointments.appointment_date', 'ASC')
            ->orderBy('appointments.appointment_time', 'ASC')
            ->first();
    }

    public function getUpcomingAppointments($limit = 10)
    {
        try {
            return $this->select('appointments.*, patients.first_name, patients.last_name, patients.gender, patients.patient_id as patient_number, patients.phone, patients.email')
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
        return $this->select('appointments.*, patients.first_name, patients.last_name, patients.gender, patients.patient_id as patient_number, patients.phone, patients.email')
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
            $base = $this->db->table('appointments');

            return [
                'total_appointments' => (clone $base)->countAllResults(),
                'today_appointments' => (clone $base)->where('DATE(appointment_date)', date('Y-m-d'))->countAllResults(),
                'upcoming_appointments' => (clone $base)->where('appointment_date >=', date('Y-m-d'))->where('status', 'scheduled')->countAllResults(),
                'completed_appointments' => (clone $base)->where('status', 'completed')->countAllResults(),
                'cancelled_appointments' => (clone $base)->where('status', 'cancelled')->countAllResults()
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

    public function checkTimeSlotAvailability($date, $time, $duration, $excludeId = null, $chairName = null)
    {
        $clinicId = session()->get('active_clinic_id');
        $cleanChairName = trim((string) $chairName);
        $chairCount = ($cleanChairName !== '') ? 1 : max(1, (int) service('settings')->getBookingChairCount());
        $overlapCount = $this->countOverlappingAppointments($clinicId, $date, $time, $duration, $excludeId, $cleanChairName);
        return $overlapCount < $chairCount;
    }

    public function getSlotRemainingCapacity($date, $time, $duration, $excludeId = null, $chairName = null)
    {
        $clinicId = session()->get('active_clinic_id');
        $cleanChairName = trim((string) $chairName);
        $chairCount = ($cleanChairName !== '') ? 1 : max(1, (int) service('settings')->getBookingChairCount());
        $overlapCount = $this->countOverlappingAppointments($clinicId, $date, $time, $duration, $excludeId, $cleanChairName);

        return max(0, $chairCount - $overlapCount);
    }

    private function countOverlappingAppointments($clinicId, $date, $time, $duration, $excludeId = null, $chairName = '')
    {
        if (!$clinicId) {
            return PHP_INT_MAX;
        }

        $builder = $this->db->table('appointments');
        $builder->where('clinic_id', $clinicId);
        $builder->where('DATE(appointment_date)', $date);
        $builder->where('status !=', 'cancelled');
        if ($chairName !== '') {
            $builder->where('chair_name', $chairName);
        }

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        $appointments = $builder->get()->getResultArray();

        $requestedStart = strtotime($time);
        $requestedEnd = $requestedStart + ($duration * 60);
        $overlapCount = 0;

        foreach ($appointments as $appointment) {
            $appointmentStart = strtotime($appointment['appointment_time']);
            $appointmentEnd = $appointmentStart + ((int) $appointment['duration'] * 60);

            if (($requestedStart < $appointmentEnd) && ($requestedEnd > $appointmentStart)) {
                $overlapCount++;
            }
        }

        return $overlapCount;
    }
}
