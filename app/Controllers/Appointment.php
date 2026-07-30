<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\ClinicUserModel;
use App\Models\InventoryModel;
use App\Models\PatientModel;
use App\Libraries\TreatmentCatalog;
use App\Services\ActivityLogger;
use App\Services\AppointmentCheckoutService;
use App\Services\AppointmentVisitStageService;
use App\Services\WhatsAppWorkflowService;

class Appointment extends BaseController
{
    protected $appointmentModel;
    protected $patientModel;
    protected $activityLogger;
    protected $storageService;
    protected WhatsAppWorkflowService $whatsAppWorkflowService;
    protected AppointmentVisitStageService $visitStageService;
    protected AppointmentCheckoutService $checkoutService;

    public function __construct() 
    {
        $this->appointmentModel = new AppointmentModel();
        $this->patientModel = new PatientModel();
        $this->activityLogger = new ActivityLogger();
        $this->storageService = new \App\Services\StorageService();
        $this->whatsAppWorkflowService = new WhatsAppWorkflowService();
        $this->visitStageService = new AppointmentVisitStageService();
        $this->checkoutService = new AppointmentCheckoutService();
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
        $validation = session('validation');
        if (!$validation instanceof \CodeIgniter\Validation\Validation) {
            $validation = \Config\Services::validation();
        }

        $quickCreatePatient = null;
        if (old('source_modal') === 'appointment_quick_create') {
            $oldPatientId = old('patient_id');
            if (is_numeric($oldPatientId)) {
                $quickCreatePatient = $this->patientModel->where('clinic_id', $clinicId)->find((int) $oldPatientId);
            }
        }
        
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
            'validation' => $validation,
            'loadSelect2' => true,
            'quick_create_patient' => $quickCreatePatient,
            'chair_options' => $this->getChairOptions(),
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
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to schedule an appointment.');
        }

        $patientId = $this->request->getGet('patient_id');
        $selectedPatient = null;
        if ($patientId) {
            $selectedPatient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
            if (!$selectedPatient) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
            }
        }

        $examinationId = $this->request->getGet('examination_id');
        $examinationId = is_numeric($examinationId) ? (int)$examinationId : null;

        $appointmentType = $this->request->getGet('appointment_type');
        $allowedTypes = ['consultation', 'treatment', 'follow_up', 'emergency', 'cleaning', 'checkup'];
        if (!in_array($appointmentType, $allowedTypes, true)) {
            $appointmentType = null;
        }

        $requestedDate = $this->normalizeDateOnly((string) $this->request->getGet('date'));

        $returnTo = $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'examination', 'appointment', 'appointments']);
        if (!$returnTo) {
            if ($examinationId) {
                $returnTo = 'examination/' . $examinationId;
            } elseif (!empty($selectedPatient['id'])) {
                $returnTo = 'patient/' . (int) $selectedPatient['id'];
            }
        }

        $data = [
            'title' => 'New Appointment',
            'patients' => [], // S4-02e-FIX: Do not preload patient list server-side. Autocomplete will handle it.
            'validation' => \Config\Services::validation(),
            'loadSelect2' => true,
            'selected_patient' => $selectedPatient,
            'examination_id' => $examinationId,
            'appointment_type_prefill' => $appointmentType,
            'chair_options' => $this->getChairOptions(),
            'requested_date' => $requestedDate,
            'return_to' => $returnTo
        ];

        // Ensure user data is included
        $userData = $this->getUserDataForView();
        $data = array_merge($data, $userData);

        return $this->view('appointment/create', $data);
    }

    public function store()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to schedule an appointment.');
        }

        $rules = [
            'patient_id' => 'required|integer',
            'appointment_date' => 'required|valid_date',
            'appointment_time' => 'required',
            'duration' => 'required|integer|greater_than[0]',
            'appointment_type' => 'required|in_list[consultation,treatment,follow_up,emergency,cleaning,checkup]',
            'chair_name' => 'required|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            if ($this->request->getPost('source_modal') === 'appointment_quick_create') {
                $modalReturnTo = $this->sanitizeInternalReturnTo(
                    $this->request->getPost('return_to'),
                    ['patient', 'patients', 'examination', 'appointment', 'appointments']
                ) ?: 'appointment';
                return redirect()->to(base_url($modalReturnTo))
                    ->withInput()
                    ->with('validation', $this->validator)
                    ->with('error', 'Please review the highlighted fields and try again.');
            }
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $currentUser = $this->getCurrentUser();
        $currentUserId = $currentUser ? (int) $currentUser->id : (session()->get('user_id') ? (int) session()->get('user_id') : null);

        $appointmentData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'appointment_date' => $this->normalizePostedDateOnly('appointment_date'),
            'appointment_time' => $this->request->getPost('appointment_time'),
            'duration' => $this->request->getPost('duration'),
            'appointment_type' => $this->request->getPost('appointment_type'),
            'chair_name' => trim((string) $this->request->getPost('chair_name')),
            'status' => 'scheduled',
            'notes' => $this->request->getPost('notes'),
            'created_by' => $currentUserId
        ];

        $patient = $this->patientModel->where('clinic_id', $clinicId)->find((int) $appointmentData['patient_id']);
        if (!$patient) {
            return redirect()->back()->withInput()->with('error', 'Selected patient was not found in this clinic');
        }

        // Check if appointment is within working hours
        $workingHours = getWorkingHours();
        $dayOfWeek = strtolower(date('l', strtotime($appointmentData['appointment_date'])));
        
        if (!in_array($dayOfWeek, $workingHours['days'])) {
            return redirect()->back()->withInput()->with('error', 'Appointments cannot be scheduled on non-working days');
        }

        if (!$this->isAppointmentWithinWorkingHours(
            $appointmentData['appointment_date'],
            $appointmentData['appointment_time'],
            (int) $appointmentData['duration'],
            $workingHours
        )) {
            return redirect()->back()->withInput()->with('error', 'Appointments can only be scheduled during working hours (' . formatTime($workingHours['start']) . ' - ' . formatTime($workingHours['end']) . ')');
        }

        // Check for time slot availability
        if (!$this->appointmentModel->checkTimeSlotAvailability(
            $appointmentData['appointment_date'],
            $appointmentData['appointment_time'],
            $appointmentData['duration'],
            null,
            $appointmentData['chair_name']
        )) {
            return redirect()->back()->withInput()->with('error', 'The selected doctor is not available for this time slot');
        }

        if ($this->appointmentModel->insert($appointmentData)) {
            $appointmentId = $this->appointmentModel->getInsertID();
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Log the appointment creation activity
            $this->activityLogger->logAppointmentActivity(
                'create',
                $appointmentId,
                "New {$appointmentData['appointment_type']} appointment scheduled for {$patientName} on " . formatDate($appointmentData['appointment_date']) . " at " . formatTime($appointmentData['appointment_time'])
            );

            try {
                $this->whatsAppWorkflowService->queueAppointmentConfirmation((int) $clinicId, (int) $appointmentId, $currentUserId);
            } catch (\Throwable $e) {
                log_message('warning', 'WhatsApp appointment confirmation enqueue failed for appointment_id={appointment_id}: {message}', [
                    'appointment_id' => $appointmentId,
                    'message' => substr($e->getMessage(), 0, 255),
                ]);
            }
            
            $returnTo = $this->resolveCreateReturnTo((int) $appointmentData['patient_id'], $this->request->getPost('examination_id'), $this->request->getPost('return_to'));
            if ($returnTo) {
                return $this->redirectAfterWorkflow($returnTo, ['patient', 'patients', 'examination', 'appointment', 'appointments'])
                    ->with('success', 'Appointment created successfully');
            }

            $examinationId = $this->request->getPost('examination_id');
            if (is_numeric($examinationId)) {
                return $this->redirectAfterWorkflow('examination/' . (int) $examinationId, ['patient', 'patients', 'examination', 'appointment', 'appointments'])
                    ->with('success', 'Follow-up appointment scheduled successfully');
            }

            return $this->redirectAfterWorkflow('appointment', ['patient', 'patients', 'examination', 'appointment', 'appointments'])
                ->with('success', 'Appointment created successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to create appointment');
        }
    }

    private function resolveCreateReturnTo(int $patientId, $examinationIdRaw, $returnToRaw): ?string
    {
        $returnTo = $this->sanitizeInternalReturnTo((string) $returnToRaw, ['patient', 'patients', 'examination', 'appointment', 'appointments']);
        if (!$returnTo) {
            return null;
        }

        if (preg_match('#^(patients|appointment|appointments)(\\?|$)#', $returnTo)) {
            return $returnTo;
        }

        if (preg_match('#^patient/(\\d+)$#', $returnTo, $matches)) {
            return ((int) $matches[1] === $patientId) ? $returnTo : null;
        }

        if (preg_match('#^examination/(\\d+)$#', $returnTo, $matches)) {
            if (!is_numeric($examinationIdRaw)) {
                return null;
            }
            return ((int) $matches[1] === (int) $examinationIdRaw) ? $returnTo : null;
        }

        return null;
    }

    public function show($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic.');
        }

        $appointment = $this->appointmentModel->getAppointmentWithPatient($id, $clinicId);
        
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

        $returnTo = $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'examination', 'appointment', 'appointments']);
        $returnToContext = 'appointment/' . (int) $appointment['id'];
        $patientId = (int) ($appointment['patient_id'] ?? 0);

        $selectedPatient = [
            'id' => $patientId,
            'patient_id' => $appointment['patient_number'] ?? '',
            'first_name' => $appointment['first_name'] ?? '',
            'last_name' => $appointment['last_name'] ?? '',
            'gender' => $appointment['gender'] ?? '',
            'phone' => $appointment['phone'] ?? '',
        ];

        $checkout = $this->checkoutService->review((int) $clinicId, (int) $appointment['id']);
        $linkedExaminations = $checkout['examinations'];
        $linkedTreatments = $checkout['treatments'];
        $linkedExaminationIds = array_values(array_filter(array_map(
            static fn(array $row): int => (int) ($row['id'] ?? 0),
            $linkedExaminations
        )));

        $liquidityOptions = [
            'safe_cash' => 'Safe Cash',
            'bank' => 'Bank',
            'undeposited_checks' => 'Checks on Hand',
        ];
        foreach ($liquidityOptions as $key => $fallback) {
            $translated = lang('Finance.liquidityAccount.' . $key);
            $liquidityOptions[$key] = is_string($translated) && $translated !== 'Finance.liquidityAccount.' . $key
                ? $translated
                : $fallback;
        }

        $inventoryItems = (new InventoryModel())->where('status', 'active')
            ->where('clinic_id', $clinicId)
            ->where('quantity >', 0)
            ->orderBy('item_name', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Appointment Details - ' . $appointment['appointment_id'],
            'appointment' => $appointment,
            'appointment_context_patient' => $selectedPatient,
            'appointment_linked_examinations' => $linkedExaminations,
            'appointment_linked_treatments' => $linkedTreatments,
            'appointment_checkout' => $checkout,
            'appointment_money_summary' => $checkout['summary'],
            'appointment_invoiceable_charge_lines' => $checkout['invoiceable_charge_lines'],
            'appointment_finance_base_currency' => settings()->get('currency', 'ILS'),
            'appointment_finance_currency_options' => ['ILS', 'USD', 'JOD', 'EUR', 'GBP'],
            'appointment_finance_liquidity_options' => $liquidityOptions,
            'treatment_types' => TreatmentCatalog::forSelection(),
            'inventory_items' => $inventoryItems,
            'clinic_providers' => (new ClinicUserModel())->getActiveUserDirectoryByClinic((int) $clinicId),
            'selected_examination_id' => $linkedExaminationIds[0] ?? null,
            'appointment_chair_options' => $this->getChairOptions(),
            'appointment_allowed_visit_stages' => $this->visitStageService->allowedNext((string) ($appointment['status'] ?? 'scheduled')),
            'appointment_visit_is_closed' => $this->visitStageService->isTerminal((string) ($appointment['status'] ?? 'scheduled')),
            'return_to_context' => $returnToContext,
            'return_to' => $returnTo
        ];

        return $this->view('appointment/show', $data);
    }

    public function edit($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to edit appointments.');
        }

        $appointment = $this->appointmentModel->getAppointmentWithPatient($id, $clinicId);
        
        if (!$appointment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Appointment not found');
        }

        // S4-02e-FIX: Only load the specific patient for this appointment
        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($appointment['patient_id']);
        if (!$patient) {
             throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found in current clinic');
        }

        $data = [
            'title' => 'Edit Appointment - ' . $appointment['appointment_id'],
            'appointment' => $appointment,
            'patients' => [$patient], // Only emit the current patient as option
            'validation' => \Config\Services::validation(),
            'loadSelect2' => true,
            'chair_options' => $this->getChairOptions(),
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'examination', 'appointment', 'appointments'])
        ];

        return $this->view('appointment/edit', $data);
    }

    public function update($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $existingAppointment = $this->appointmentModel->findByClinic($clinicId, $id);
        if (!$existingAppointment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Appointment not found');
        }

        $appointment = $this->appointmentModel->findByClinic($clinicId, $id);
        
        if (!$appointment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Appointment not found');
        }

        $rules = [
            'patient_id' => 'required|integer',
            'appointment_date' => 'required|valid_date',
            'appointment_time' => 'required',
            'duration' => 'required|integer|greater_than[0]',
            'appointment_type' => 'required|in_list[consultation,treatment,follow_up,emergency,cleaning,checkup]',
            'chair_name' => 'required|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $appointmentData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'appointment_date' => $this->normalizePostedDateOnly('appointment_date'),
            'appointment_time' => $this->request->getPost('appointment_time'),
            'duration' => $this->request->getPost('duration'),
            'appointment_type' => $this->request->getPost('appointment_type'),
            'chair_name' => trim((string) $this->request->getPost('chair_name')),
            'status' => (string) ($existingAppointment['status'] ?? 'scheduled'),
            'notes' => $this->request->getPost('notes')
        ];

        $patient = $this->patientModel->where('clinic_id', $clinicId)->find((int) $appointmentData['patient_id']);
        if (!$patient) {
            return redirect()->back()->withInput()->with('error', 'Selected patient was not found in this clinic');
        }

        // Check if appointment is within working hours
        $workingHours = getWorkingHours();
        $dayOfWeek = strtolower(date('l', strtotime($appointmentData['appointment_date'])));
        
        if (!in_array($dayOfWeek, $workingHours['days'])) {
            return redirect()->back()->withInput()->with('error', 'Appointments cannot be scheduled on non-working days');
        }

        if (!$this->isAppointmentWithinWorkingHours(
            $appointmentData['appointment_date'],
            $appointmentData['appointment_time'],
            (int) $appointmentData['duration'],
            $workingHours
        )) {
            return redirect()->back()->withInput()->with('error', 'Appointments can only be scheduled during working hours (' . formatTime($workingHours['start']) . ' - ' . formatTime($workingHours['end']) . ')');
        }

        // Check for time slot availability (excluding current appointment)
        if (!$this->appointmentModel->checkTimeSlotAvailability(
            $appointmentData['appointment_date'],
            $appointmentData['appointment_time'],
            $appointmentData['duration'],
            $id,
            $appointmentData['chair_name']
        )) {
            return redirect()->back()->withInput()->with('error', 'The selected doctor is not available for this time slot');
        }

        if ($this->appointmentModel->update($id, $appointmentData)) {
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Log the appointment update activity
            $this->activityLogger->logAppointmentActivity(
                'update',
                $id,
                "Appointment updated for {$patientName} - {$appointmentData['appointment_type']} on " . formatDate($appointmentData['appointment_date']) . " at " . formatTime($appointmentData['appointment_time'])
            );

            try {
                $actorUserId = session()->get('user_id') ? (int) session()->get('user_id') : null;
                $this->whatsAppWorkflowService->queueAppointmentConfirmation((int) $clinicId, (int) $id, $actorUserId);
            } catch (\Throwable $e) {
                log_message('warning', 'WhatsApp appointment workflow enqueue failed for appointment_id={appointment_id}: {message}', [
                    'appointment_id' => $id,
                    'message' => substr($e->getMessage(), 0, 255),
                ]);
            }

            $returnTo = $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['patient', 'patients', 'examination', 'appointment', 'appointments']);
            if ($returnTo) {
                return $this->redirectAfterWorkflow($returnTo, ['patient', 'patients', 'examination', 'appointment', 'appointments'])
                    ->with('success', 'Appointment updated successfully');
            }
            
            return $this->redirectAfterWorkflow('appointment/' . $id, ['patient', 'patients', 'examination', 'appointment', 'appointments'])
                ->with('success', 'Appointment updated successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update appointment');
        }
    }

    public function delete($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }
            return redirect()->to('/clinic/select');
        }

        $appointment = $this->appointmentModel->findByClinic($clinicId, $id);
        
        if (!$appointment) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Appointment not found']);
            }
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Appointment not found');
        }

        if ($this->appointmentModel->delete($id)) {
            // Get patient name for the activity log
            $patient = $this->patientModel->where('clinic_id', $clinicId)->find($appointment['patient_id']);
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

            $returnTo = $this->sanitizeInternalReturnTo(
                (string) ($this->request->getPost('return_to') ?: $this->request->getGet('return_to')),
                ['patient', 'patients', 'examination', 'appointment', 'appointments']
            );
            if ($returnTo) {
                return redirect()->to(base_url($returnTo))->with('success', 'Appointment deleted successfully');
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
        return $this->transitionAppointment($id, 'confirmed');
    }

    public function complete($id)
    {
        return $this->transitionAppointment($id, 'completed');
    }

    public function cancel($id)
    {
        return $this->transitionAppointment($id, 'cancelled');
    }

    public function transition($id)
    {
        return $this->transitionAppointment($id, (string) $this->request->getPost('status'));
    }

    private function transitionAppointment($id, string $targetStage)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $appointment = $this->appointmentModel->findByClinic($clinicId, $id);
        
        if (!$appointment) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Appointment not found']);
        }

        $currentStage = (string) ($appointment['status'] ?? 'scheduled');
        if (!$this->visitStageService->canTransition($currentStage, $targetStage)) {
            if (!$this->request->isAJAX()) {
                return redirect()->back()->with('error', 'This visit stage change is not allowed.');
            }
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'This visit stage change is not allowed.',
                'current_status' => $currentStage,
                'allowed_statuses' => $this->visitStageService->allowedNext($currentStage),
            ]);
        }

        if (!$this->appointmentModel->update($id, ['status' => $targetStage])) {
            if (!$this->request->isAJAX()) {
                return redirect()->back()->with('error', 'Failed to update visit stage');
            }
            return $this->response->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => 'Failed to update visit stage']);
        }

        $this->activityLogger->logAppointmentActivity(
            'status',
            (int) $id,
            "Appointment visit stage changed from {$currentStage} to {$targetStage}"
        );

        if ($targetStage === 'no_show') {
            try {
                $actorUserId = session()->get('user_id') ? (int) session()->get('user_id') : null;
                $this->whatsAppWorkflowService->queueNoShowFollowUp((int) $clinicId, (int) $id, $actorUserId);
            } catch (\Throwable $e) {
                log_message('warning', 'WhatsApp no-show follow-up enqueue failed for appointment_id={appointment_id}: {message}', [
                    'appointment_id' => $id,
                    'message' => substr($e->getMessage(), 0, 255),
                ]);
            }
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Visit stage updated',
                'status' => $targetStage,
            ]);
        }

        $returnTo = $this->sanitizeInternalReturnTo(
            (string) $this->request->getPost('return_to'),
            ['patient', 'patients', 'examination', 'appointment', 'appointments']
        );

        return redirect()
            ->to(base_url($returnTo ?: 'appointment/' . (int) $id))
            ->with('success', 'Visit stage updated');
    }

    public function getAvailableTimeSlots()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $date = $this->request->getGet('date');
        $duration = $this->request->getGet('duration') ?? getAppointmentDuration();
        $excludeId = $this->request->getGet('exclude_id'); // For editing appointments
        $chairName = trim((string) $this->request->getGet('chair_name'));

        if (empty($date)) {
            return $this->response->setJSON(['error' => 'Date is required']);
        }

        if ($chairName === '') {
            return $this->response->setJSON(['error' => 'Doctor is required']);
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
        $slotDuration = max(1, (int) $duration) * 60; // Convert to seconds
        $slotIncrement = $this->getSchedulingIncrementMinutes() * 60;
        $chairCount = ($chairName !== '') ? 1 : max(1, (int) service('settings')->getBookingChairCount());

        for ($time = $startTime; ($time + $slotDuration) <= $endTime; $time += $slotIncrement) {
            $timeSlot = date('H:i', $time);

            $remainingCapacity = $this->appointmentModel->getSlotRemainingCapacity($date, $timeSlot, $duration, $excludeId, $chairName);
            if ($remainingCapacity > 0) {
                $timeSlots[] = [
                    'value' => $timeSlot,
                    'display' => formatTime($timeSlot),
                    'remaining_capacity' => $remainingCapacity,
                    'chair_capacity' => $chairCount,
                    'chair_name' => $chairName
                ];
            }
        }

        // Log the result for debugging
        log_message('info', 'Available time slots: ' . json_encode($timeSlots));

        return $this->response->setJSON($timeSlots);
    }

    public function print($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic.');
        }

        $appointment = $this->appointmentModel->getAppointmentWithPatient($id, $clinicId);
        
        if (!$appointment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Appointment not found');
        }

        $data = [
            'appointment' => $appointment,
            'clinic' => settings()->getClinicInfo()
        ];

        $html = view('appointment/print', $data);
        
        // Persist artifact (best-effort; do not block print on plan limits)
        $fileName = 'appointment_' . ($appointment['appointment_id'] ?? $id) . '.html';
        try {
            $this->storageService->storeExport(
                $html, 
                $fileName, 
                'text/html', 
                $clinicId, 
                'appointment', 
                $id, 
                'appointment_print'
            );
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            log_message('warning', 'Appointment print export blocked: ' . $e->getMessage());
        } catch (\Exception $e) {
            log_message('error', 'Appointment print export failed: ' . $e->getMessage());
        }

        return $this->response
            ->setHeader('Content-Type', 'text/html')
            ->setBody($html);
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
            'workingHours' => getWorkingHours(),
            'loadSelect2' => true,
            'chair_options' => $this->getChairOptions(),
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
                $doctorLabel = trim((string) ($appointment['chair_name'] ?? ''));
                $titleParts = [
                    trim((string) (($appointment['first_name'] ?? '') . ' ' . ($appointment['last_name'] ?? ''))),
                    $appointmentType,
                ];
                if ($doctorLabel !== '') {
                    $titleParts[] = $doctorLabel;
                }
                
                $events[] = [
                    'id' => $appointment['id'],
                    'title' => implode(' - ', array_filter($titleParts, static fn($value) => $value !== '')),
                    'start' => $appointment['appointment_date'] . 'T' . $appointment['appointment_time'],
                    'end' => $appointment['appointment_date'] . 'T' . date('H:i', strtotime($appointment['appointment_time'] . ' +' . ($appointment['duration'] ?? 30) . ' minutes')),
                    'status' => $appointment['status'] ?? 'scheduled',
                    'patient_id' => $appointment['patient_id'],
                    'appointment_type' => $appointmentType,
                    'duration' => $appointment['duration'] ?? 30,
                    'doctor_name' => $doctorLabel,
                    'booked_by' => trim((string) (($appointment['creator_first_name'] ?? '') . ' ' . ($appointment['creator_last_name'] ?? ''))),
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

    private function getChairOptions(): array
    {
        return service('settings')->getBookingChairNames();
    }

    private function isAppointmentWithinWorkingHours(string $date, string $time, int $duration, array $workingHours): bool
    {
        if ($duration <= 0) {
            return false;
        }

        $appointmentStart = strtotime($date . ' ' . $time);
        $workingStart = strtotime($date . ' ' . ($workingHours['start'] ?? '09:00'));
        $workingEnd = strtotime($date . ' ' . ($workingHours['end'] ?? '17:00'));
        $appointmentEnd = $appointmentStart + ($duration * 60);

        return $appointmentStart >= $workingStart && $appointmentEnd <= $workingEnd;
    }

    private function getSchedulingIncrementMinutes(): int
    {
        return 15;
    }
}
