<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\PatientModel;
use App\Services\ActivityLogger;

class Appointment extends BaseController
{
    protected $appointmentModel;
    protected $patientModel;
    protected $activityLogger;

    public function __construct() 
    {
        $this->appointmentModel = new AppointmentModel();
        $this->patientModel = new PatientModel();
        $this->activityLogger = new ActivityLogger();
    }

    public function index()
    {
        // S4-02d: Fail closed if clinic context is missing
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to view appointments.');
        }

        $view = $this->request->getGet('view') ?? '';
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        $status = $this->request->getGet('status') ?? '';
        $search = $this->request->getGet('search') ?? '';
        
        // Get appointments with filters
        if ($view === 'all') {
            // Get all appointments with pagination (scoped)
            $page = $this->request->getGet('page') ?? 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            
            $appointments = $this->appointmentModel->getAppointmentsByClinic($clinicId, $limit, $offset, $search, $status);
            $totalCount = $this->appointmentModel->countAppointmentsByClinic($clinicId, $search, $status);
            $hasMore = ($offset + $limit) < $totalCount;
        } else {
            // Get appointments for specific date (scoped)
            $appointments = $this->appointmentModel->getAppointmentsByDateByClinic($clinicId, $date);
            
            $totalCount = count($appointments);
            $hasMore = false;
        }
        
        // Apply client-side filters only for daily view (server-side filtering for all view)
        if ($view !== 'all') {
            // Apply status filter for daily view
            if (!empty($status)) {
                $appointments = array_filter($appointments, function($appointment) use ($status) {
                    return $appointment['status'] === $status;
                });
            }
            
            // Apply search filter for daily view
            if (!empty($search)) {
                $search = strtolower($search);
                $appointments = array_filter($appointments, function($appointment) use ($search) {
                    return strpos(strtolower($appointment['patient_name'] ?? ''), $search) !== false ||
                           strpos(strtolower($appointment['appointment_type'] ?? ''), $search) !== false ||
                           strpos(strtolower($appointment['notes'] ?? ''), $search) !== false;
                });
            }
        }
        
        // Format dates and times for display
        foreach ($appointments as &$appointment) {
            if (isset($appointment['appointment_date'])) {
                $appointment['appointment_date_formatted'] = formatDate($appointment['appointment_date']);
            }
            if (isset($appointment['appointment_time'])) {
                $appointment['appointment_time_formatted'] = formatTime($appointment['appointment_time']);
            }
            if (isset($appointment['created_at'])) {
                $appointment['created_at_formatted'] = formatDateTime($appointment['created_at']);
            }
        }
        
        $data = [
            'title' => 'Appointment Management',
            'appointments' => $appointments,
            'view_mode' => $view,
            'selected_date' => $date,
            'selected_date_formatted' => formatDate($date),
            'selected_status' => $status,
            'search_term' => $search,
            'stats' => $this->appointmentModel->getAppointmentStatsByClinic($clinicId),
            'pagination' => [
                'total_count' => $totalCount,
                'has_more' => $hasMore,
                'current_page' => $view === 'all' ? ($this->request->getGet('page') ?? 1) : 1,
                'limit' => $view === 'all' ? 10 : null
            ]
        ];

        // Ensure user data is included
        $userData = $this->getUserDataForView();
        $data = array_merge($data, $userData);

        return $this->view('appointment/index', $data);
    }

    public function loadMoreAppointments()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $page = $this->request->getGet('page') ?? 1;
            $search = $this->request->getGet('search') ?? '';
            $status = $this->request->getGet('status') ?? '';
            
            $limit = 10;
            $offset = ($page - 1) * $limit;
            
            $appointments = $this->appointmentModel->getAppointmentsByClinic($clinicId, $limit, $offset, $search, $status);
            $totalCount = $this->appointmentModel->countAppointmentsByClinic($clinicId, $search, $status);
            $hasMore = ($offset + $limit) < $totalCount;
            
            // Format dates and times for display
            foreach ($appointments as &$appointment) {
                if (isset($appointment['appointment_date'])) {
                    $appointment['appointment_date_formatted'] = formatDate($appointment['appointment_date']);
                }
                if (isset($appointment['appointment_time'])) {
                    $appointment['appointment_time_formatted'] = formatTime($appointment['appointment_time']);
                }
                if (isset($appointment['created_at'])) {
                    $appointment['created_at_formatted'] = formatDateTime($appointment['created_at']);
                }
            }
            
            return $this->response->setJSON([
                'success' => true,
                'appointments' => $appointments,
                'pagination' => [
                    'total_count' => $totalCount,
                    'has_more' => $hasMore,
                    'current_page' => $page,
                    'limit' => $limit
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Load more appointments error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Failed to load appointments'
            ]);
        }
    }

    public function create()
    {
        $data = [
            'title' => 'New Appointment',
            'patients' => $this->patientModel->where('status', 'active')->findAll(),
            'validation' => \Config\Services::validation()
        ];

        // Ensure user data is included
        $userData = $this->getUserDataForView();
        $data = array_merge($data, $userData);

        return $this->view('appointment/create', $data);
    }

    public function store()
    {
        $rules = [
            'patient_id' => 'required|integer',
            'appointment_date' => 'required|valid_date',
            'appointment_time' => 'required',
            'duration' => 'required|integer|greater_than[0]',
            'appointment_type' => 'required|in_list[consultation,treatment,follow_up,emergency,cleaning,checkup]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $appointmentData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'appointment_date' => $this->request->getPost('appointment_date'),
            'appointment_time' => $this->request->getPost('appointment_time'),
            'duration' => $this->request->getPost('duration'),
            'appointment_type' => $this->request->getPost('appointment_type'),
            'status' => 'scheduled',
            'notes' => $this->request->getPost('notes'),
            'created_by' => 1
        ];

        // Check if appointment is within working hours
        $workingHours = getWorkingHours();
        $dayOfWeek = strtolower(date('l', strtotime($appointmentData['appointment_date'])));
        
        if (!in_array($dayOfWeek, $workingHours['days'])) {
            return redirect()->back()->withInput()->with('error', 'Appointments cannot be scheduled on non-working days');
        }
        
        $appointmentTime = strtotime($appointmentData['appointment_time']);
        $startTime = strtotime($workingHours['start']);
        $endTime = strtotime($workingHours['end']);
        
        if ($appointmentTime < $startTime || $appointmentTime >= $endTime) {
            return redirect()->back()->withInput()->with('error', 'Appointments can only be scheduled during working hours (' . formatTime($workingHours['start']) . ' - ' . formatTime($workingHours['end']) . ')');
        }

        // Check for time slot availability
        if (!$this->appointmentModel->checkTimeSlotAvailability(
            $appointmentData['appointment_date'],
            $appointmentData['appointment_time'],
            $appointmentData['duration']
        )) {
            return redirect()->back()->withInput()->with('error', 'Time slot is not available');
        }

        if ($this->appointmentModel->insert($appointmentData)) {
            $appointmentId = $this->appointmentModel->getInsertID();
            
            // Get patient name for the activity log
            $patient = $this->patientModel->find($appointmentData['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Log the appointment creation activity
            $this->activityLogger->logAppointmentActivity(
                'create',
                $appointmentId,
                "New {$appointmentData['appointment_type']} appointment scheduled for {$patientName} on " . formatDate($appointmentData['appointment_date']) . " at " . formatTime($appointmentData['appointment_time'])
            );
            
            return redirect()->to('/appointment')->with('success', 'Appointment created successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to create appointment');
        }
    }

    public function show($id)
    {
        $appointment = $this->appointmentModel->getAppointmentWithPatient($id);
        
        if (!$appointment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Appointment not found');
        }

        // Format dates and times for display
        if (isset($appointment['appointment_date'])) {
            $appointment['appointment_date_formatted'] = formatDate($appointment['appointment_date']);
        }
        if (isset($appointment['appointment_time'])) {
            $appointment['appointment_time_formatted'] = formatTime($appointment['appointment_time']);
        }
        if (isset($appointment['created_at'])) {
            $appointment['created_at_formatted'] = formatDateTime($appointment['created_at']);
        }

        $data = [
            'title' => 'Appointment Details - ' . $appointment['appointment_id'],
            'appointment' => $appointment
        ];

        return $this->view('appointment/show', $data);
    }

    public function edit($id)
    {
        $appointment = $this->appointmentModel->getAppointmentWithPatient($id);
        
        if (!$appointment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Appointment not found');
        }

        $data = [
            'title' => 'Edit Appointment - ' . $appointment['appointment_id'],
            'appointment' => $appointment,
            'patients' => $this->patientModel->where('status', 'active')->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return $this->view('appointment/edit', $data);
    }

    public function update($id)
    {
        $appointment = $this->appointmentModel->find($id);
        
        if (!$appointment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Appointment not found');
        }

        $rules = [
            'patient_id' => 'required|integer',
            'appointment_date' => 'required|valid_date',
            'appointment_time' => 'required',
            'duration' => 'required|integer|greater_than[0]',
            'appointment_type' => 'required|in_list[consultation,treatment,follow_up,emergency,cleaning,checkup]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $appointmentData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'appointment_date' => $this->request->getPost('appointment_date'),
            'appointment_time' => $this->request->getPost('appointment_time'),
            'duration' => $this->request->getPost('duration'),
            'appointment_type' => $this->request->getPost('appointment_type'),
            'status' => $this->request->getPost('status'),
            'notes' => $this->request->getPost('notes')
        ];

        // Check if appointment is within working hours
        $workingHours = getWorkingHours();
        $dayOfWeek = strtolower(date('l', strtotime($appointmentData['appointment_date'])));
        
        if (!in_array($dayOfWeek, $workingHours['days'])) {
            return redirect()->back()->withInput()->with('error', 'Appointments cannot be scheduled on non-working days');
        }
        
        $appointmentTime = strtotime($appointmentData['appointment_time']);
        $startTime = strtotime($workingHours['start']);
        $endTime = strtotime($workingHours['end']);
        
        if ($appointmentTime < $startTime || $appointmentTime >= $endTime) {
            return redirect()->back()->withInput()->with('error', 'Appointments can only be scheduled during working hours (' . formatTime($workingHours['start']) . ' - ' . formatTime($workingHours['end']) . ')');
        }

        // Check for time slot availability (excluding current appointment)
        if (!$this->appointmentModel->checkTimeSlotAvailability(
            $appointmentData['appointment_date'],
            $appointmentData['appointment_time'],
            $appointmentData['duration'],
            $id
        )) {
            return redirect()->back()->withInput()->with('error', 'Time slot is not available');
        }

        if ($this->appointmentModel->update($id, $appointmentData)) {
            // Get patient name for the activity log
            $patient = $this->patientModel->find($appointmentData['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Log the appointment update activity
            $this->activityLogger->logAppointmentActivity(
                'update',
                $id,
                "Appointment updated for {$patientName} - {$appointmentData['appointment_type']} on " . formatDate($appointmentData['appointment_date']) . " at " . formatTime($appointmentData['appointment_time'])
            );
            
            return redirect()->to('/appointment/' . $id)->with('success', 'Appointment updated successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update appointment');
        }
    }

    public function delete($id)
    {
        $appointment = $this->appointmentModel->find($id);
        
        if (!$appointment) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found']);
            }
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Appointment not found');
        }

        if ($this->appointmentModel->delete($id)) {
            // Get patient name for the activity log
            $patient = $this->patientModel->find($appointment['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Log the appointment deletion activity
            $this->activityLogger->logAppointmentActivity(
                'delete',
                $id,
                "Appointment deleted for {$patientName} - {$appointment['appointment_type']} scheduled for " . formatDate($appointment['appointment_date']) . " at " . formatTime($appointment['appointment_time'])
            );
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Appointment deleted successfully']);
            }
            return redirect()->to(base_url('appointment'))->with('success', 'Appointment deleted successfully');
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete appointment']);
            }
            return redirect()->back()->with('error', 'Failed to delete appointment');
        }
    }

    public function confirm($id)
    {
        $appointment = $this->appointmentModel->find($id);
        
        if (!$appointment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found']);
        }

        $updateData = ['status' => 'confirmed'];

        if ($this->appointmentModel->update($id, $updateData)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Appointment confirmed']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to confirm appointment']);
        }
    }

    public function complete($id)
    {
        $appointment = $this->appointmentModel->find($id);
        
        if (!$appointment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found']);
        }

        $updateData = ['status' => 'completed'];

        if ($this->appointmentModel->update($id, $updateData)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Appointment completed']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to complete appointment']);
        }
    }

    public function cancel($id)
    {
        $appointment = $this->appointmentModel->find($id);
        
        if (!$appointment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found']);
        }

        $updateData = ['status' => 'cancelled'];

        if ($this->appointmentModel->update($id, $updateData)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Appointment cancelled']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to cancel appointment']);
        }
    }

    public function getAvailableTimeSlots()
    {
        $date = $this->request->getGet('date');
        $duration = $this->request->getGet('duration') ?? getAppointmentDuration();
        $excludeId = $this->request->getGet('exclude_id'); // For editing appointments

        if (empty($date)) {
            return $this->response->setJSON(['error' => 'Date is required']);
        }

        // Get working hours from settings
        $workingHours = getWorkingHours();
        
        // Check if the requested date is a working day
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        if (!in_array($dayOfWeek, $workingHours['days'])) {
            return $this->response->setJSON(['error' => 'Selected date is not a working day']);
        }

        // Log the request for debugging
        log_message('info', 'Getting available time slots for date: ' . $date . ', duration: ' . $duration . ', exclude_id: ' . $excludeId);

        $timeSlots = [];
        $startTime = strtotime($workingHours['start']);
        $endTime = strtotime($workingHours['end']);
        $slotDuration = $duration * 60; // Convert to seconds

        for ($time = $startTime; $time < $endTime; $time += $slotDuration) {
            $timeSlot = date('H:i', $time);
            
            if ($this->appointmentModel->checkTimeSlotAvailability($date, $timeSlot, $duration, $excludeId)) {
                $timeSlots[] = [
                    'value' => $timeSlot,
                    'display' => formatTime($timeSlot)
                ];
            }
        }

        // Log the result for debugging
        log_message('info', 'Available time slots: ' . json_encode($timeSlots));

        return $this->response->setJSON($timeSlots);
    }

    public function print($id)
    {
        $appointment = $this->appointmentModel->getAppointmentWithPatient($id);
        
        if (!$appointment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Appointment not found');
        }

        $data = [
            'appointment' => $appointment,
            'clinic' => settings()->getClinicInfo()
        ];

        return $this->view('appointment/print', $data);
    }

    public function calendar()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to view calendar.');
        }

        $data = [
            'title' => 'Appointment Calendar',
            'stats' => $this->appointmentModel->getAppointmentStatsByClinic($clinicId),
            'workingHours' => getWorkingHours()
        ];

        return $this->view('appointment/calendar', $data);
    }

    public function getCalendarEvents()
    {
        try {
            $this->response->setContentType('application/json');
            
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $start = $this->request->getGet('start');
            $end = $this->request->getGet('end');

            if (empty($start) || empty($end)) {
                return $this->response->setJSON([
                    'error' => 'Start and end dates are required',
                    'events' => []
                ]);
            }

            log_message('debug', 'Calendar events request - Start: ' . $start . ', End: ' . $end);

            $appointments = $this->appointmentModel->getCalendarAppointmentsByClinic($clinicId, $start, $end);
            
            log_message('debug', 'Found ' . count($appointments) . ' appointments for calendar');
            
            $events = [];
            foreach ($appointments as $appointment) {
                // Format appointment type for display
                $appointmentType = ucwords(str_replace('_', ' ', $appointment['appointment_type'] ?? 'consultation'));
                
                $events[] = [
                    'id' => $appointment['id'],
                    'title' => $appointment['first_name'] . ' ' . $appointment['last_name'] . ' - ' . $appointmentType,
                    'start' => $appointment['appointment_date'] . 'T' . $appointment['appointment_time'],
                    'end' => $appointment['appointment_date'] . 'T' . date('H:i', strtotime($appointment['appointment_time'] . ' +' . ($appointment['duration'] ?? 30) . ' minutes')),
                    'status' => $appointment['status'] ?? 'scheduled',
                    'patient_id' => $appointment['patient_id'],
                    'appointment_type' => $appointmentType,
                    'duration' => $appointment['duration'] ?? 30
                ];
            }

            log_message('debug', 'Returning ' . count($events) . ' calendar events');
            
            return $this->response->setJSON($events);
            
        } catch (\Exception $e) {
            log_message('error', 'Calendar events error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'error' => 'Failed to load calendar events: ' . $e->getMessage(),
                'events' => []
            ]);
        }
    }
}
