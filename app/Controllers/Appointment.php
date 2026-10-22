<?php

namespace App\Controllers;

use App\Services\TreatmentProcedureIntegrationService;

use App\Models\AppointmentModel;
use App\Models\ClinicUserModel;
use App\Models\InventoryModel;
use App\Models\PatientModel;
use App\Libraries\TreatmentCatalog;
use App\Services\ActivityLogger;
use App\Services\AppointmentCheckoutService;
use App\Services\AppointmentCheckoutCompletionService;
use App\Services\AppointmentVisitStageService;
use App\Services\WhatsAppWorkflowService;
use App\Services\ProviderDirectoryService;
use App\Services\AppointmentSchedulingService;

class Appointment extends BaseController
{
    protected $appointmentModel;
    protected $patientModel;
    protected $activityLogger;
    protected $storageService;
    protected WhatsAppWorkflowService $whatsAppWorkflowService;
    protected AppointmentVisitStageService $visitStageService;
    protected AppointmentCheckoutService $checkoutService;
    protected AppointmentCheckoutCompletionService $checkoutCompletionService;
    protected ProviderDirectoryService $providerDirectoryService;
    protected AppointmentSchedulingService $schedulingService;
    protected $db;

    public function __construct() 
    {
        $this->appointmentModel = new AppointmentModel();
        $this->db = \Config\Database::connect();
        $this->patientModel = new PatientModel();
        $this->activityLogger = new ActivityLogger();
        $this->storageService = new \App\Services\StorageService();
        $this->whatsAppWorkflowService = new WhatsAppWorkflowService();
        $this->visitStageService = new AppointmentVisitStageService();
        $this->checkoutService = new AppointmentCheckoutService();
        $this->checkoutCompletionService = new AppointmentCheckoutCompletionService($this->checkoutService);
        $this->providerDirectoryService = new ProviderDirectoryService($this->db);
        $this->schedulingService = new AppointmentSchedulingService($this->db);
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
        
        $dayboardAppointments = $view === 'all' ? [] : $appointments;

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
            'dayboard_appointments' => $dayboardAppointments,
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
            'booking_context' => $this->providerDirectoryService->getBookingContext((int) $clinicId),
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
        $clinicalProcedureId = $this->request->getGet('clinical_procedure_id');
        $clinicalProcedureId = is_numeric($clinicalProcedureId) ? (int) $clinicalProcedureId : null;

        $appointmentType = $this->request->getGet('appointment_type');
        $allowedTypes = ['consultation', 'treatment', 'follow_up', 'emergency', 'cleaning', 'checkup'];
        if (!in_array($appointmentType, $allowedTypes, true)) {
            $appointmentType = null;
        }

        $rawDate = $this->request->getGet('appointment_date') ?? $this->request->getGet('date');
        $requestedDate = is_string($rawDate) ? $this->normalizeDateOnly($rawDate) : null;
        $providerPrefill = \App\Services\ClinicSchedulingSettings::positiveInt($this->request->getGet('provider_user_id'));
        if ($providerPrefill && !$this->providerDirectoryService->findBookable((int)$clinicId,$providerPrefill)) $providerPrefill=null;

        $returnTo = $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'examination', 'appointment', 'appointments', 'treatment', 'treatments']);
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
            'clinical_procedure_id' => $clinicalProcedureId,
            'appointment_type_prefill' => $appointmentType,
            'chair_options' => $this->getChairOptions(),
            'booking_context' => $this->providerDirectoryService->getBookingContext((int) $clinicId),
            'requested_date' => $requestedDate,
            'provider_id_prefill' => $providerPrefill ?? '',
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

        $dynamicEnabled = $this->providerDirectoryService->isEnabled((int) $clinicId);
        $rules = [
            'patient_id' => 'required|integer',
            'appointment_date' => 'required|valid_date',
            'appointment_time' => 'required',
            'duration' => 'required|integer|greater_than[0]',
            'appointment_type' => 'required|in_list[consultation,treatment,follow_up,emergency,cleaning,checkup]',
            'chair_name' => $dynamicEnabled ? 'permit_empty|max_length[100]' : 'required|max_length[100]',
            'provider_user_id' => $dynamicEnabled ? 'required|integer' : 'permit_empty|integer',
            'clinical_procedure_id' => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Please review the highlighted fields and try again.',
                    'errors'  => $this->validator->getErrors(),
                ]);
            }
            if ($this->request->getPost('source_modal') === 'appointment_quick_create') {
                $modalReturnTo = $this->sanitizeInternalReturnTo(
                    $this->request->getPost('return_to'),
                    ['patient', 'patients', 'examination', 'appointment', 'appointments', 'treatment', 'treatments']
                ) ?: 'appointment';
                return redirect()->to(base_url($modalReturnTo))
                    ->withInput()
                    ->with('validation', $this->validator)
                    ->with('error', 'Please review the highlighted fields and try again.');
            }
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $currentUser = $this->getCurrentUser();
        $currentUserId = $currentUser ? (int) $currentUser->id : (session()->get('user_id') ? (int) session()->get('user_id') : 0);

        $appointmentData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'appointment_date' => $this->normalizePostedDateOnly('appointment_date'),
            'appointment_time' => $this->request->getPost('appointment_time'),
            'duration' => $this->request->getPost('duration'),
            'appointment_type' => $this->request->getPost('appointment_type'),
            'chair_name' => trim((string) $this->request->getPost('chair_name')),
            'provider_user_id' => $this->request->getPost('provider_user_id'),
            'notes' => $this->request->getPost('notes'),
            'clinical_procedure_id' => $this->request->getPost('clinical_procedure_id'),
        ];

        $result = $this->schedulingService->createForClinic((int) $clinicId, $appointmentData, (int) $currentUserId, 'session');

        if (!$result['success']) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $result['message'],
                    'code'    => $result['code'] ?? 'ERROR',
                ]);
            }
            if ($this->request->getPost('source_modal') === 'appointment_quick_create') {
                $modalReturnTo = $this->sanitizeInternalReturnTo(
                    $this->request->getPost('return_to'),
                    ['patient', 'patients', 'examination', 'appointment', 'appointments', 'treatment', 'treatments']
                ) ?: 'appointment';
                return redirect()->to(base_url($modalReturnTo))
                    ->withInput()
                    ->with('error', $result['message']);
            }
            return redirect()->back()->withInput()->with('error', $result['message']);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'     => true,
                'message'     => 'Appointment created successfully',
                'id'          => $result['id'] ?? null,
                'appointment' => $result['appointment'] ?? null,
            ]);
        }

        $returnTo = $this->resolveCreateReturnTo((int) $appointmentData['patient_id'], $this->request->getPost('examination_id'), $this->request->getPost('return_to'));
        if ($returnTo) {
            return $this->redirectAfterWorkflow($returnTo, ['patient', 'patients', 'examination', 'appointment', 'appointments', 'treatment', 'treatments'])
                ->with('success', 'Appointment created successfully');
        }

        $examinationId = $this->request->getPost('examination_id');
        if (is_numeric($examinationId)) {
            return $this->redirectAfterWorkflow('examination/' . (int) $examinationId, ['patient', 'patients', 'examination', 'appointment', 'appointments'])
                ->with('success', 'Follow-up appointment scheduled successfully');
        }

        return $this->redirectAfterWorkflow('appointment', ['patient', 'patients', 'examination', 'appointment', 'appointments'])
            ->with('success', 'Appointment created successfully');
    }

    private function resolveCreateReturnTo(int $patientId, $examinationIdRaw, $returnToRaw): ?string
    {
        $returnTo = $this->sanitizeInternalReturnTo((string) $returnToRaw, ['patient', 'patients', 'examination', 'appointment', 'appointments', 'treatment', 'treatments']);
        if (!$returnTo) {
            return null;
        }

        if (preg_match('#^(patients|appointment|appointments|treatment|treatments)(\\?|$)#', $returnTo)) {
            return $returnTo;
        }

        if (preg_match('#^treatments?/(\\d+)$#', $returnTo)) {
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
        $checkoutCompletion = $this->checkoutCompletionService->assessReview($checkout);
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

        if (config('ClinicOperations')->manualAppointmentContacts) {
            $this->response->setHeader('Cache-Control', 'no-store, private')->setHeader('Pragma', 'no-cache');
        }
        $contactBeforeRaw = $this->request->getGet('contact_before_id');
        $contactBefore = $contactBeforeRaw === null
            ? null
            : \App\Services\ClinicSchedulingSettings::positiveInt($contactBeforeRaw);
        if ($contactBeforeRaw !== null && $contactBefore === null) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid contact history cursor');
        }
        try {
            $contactHistory = $this->schedulingService->contactHistory(
                (int) $clinicId,
                (int) $appointment['id'],
                (int) $this->ionAuth->getUserId(),
                $contactBefore
            );
        } catch (\DomainException $e) {
            return $this->response->setStatusCode($e->getMessage() === 'SCHEMA_NOT_READY' ? 503 : 403)
                ->setHeader('Cache-Control', 'no-store, private')
                ->setBody(esc(lang('AppointmentContact.failed')));
        }
        $data = [
            'title' => 'Appointment Details - ' . $appointment['appointment_id'],
            'appointment' => $appointment,
            'contact_history' => $contactHistory,
            'contact_history_enabled' => config('ClinicOperations')->manualAppointmentContacts,
            'contact_request_key' => bin2hex(random_bytes(24)),
            'appointment_context_patient' => $selectedPatient,
            'appointment_linked_examinations' => $linkedExaminations,
            'appointment_linked_treatments' => $linkedTreatments,
            'appointment_checkout' => $checkout,
            'appointment_checkout_completion' => $checkoutCompletion,
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

    public function contactAttempt($id)
    {
        $this->response->setHeader('Cache-Control', 'no-store, private');
        $clinicId = (int)(session()->get('active_clinic_id') ?? 0);
        $actorId = (int)($this->ionAuth->getUserId() ?? 0);
        $appointmentId = \App\Services\ClinicSchedulingSettings::positiveInt($id);
        if ($clinicId <= 0 || $actorId <= 0 || $appointmentId === null) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false,'message' => lang('AppointmentContact.failed')]);
        }
        $result = $this->schedulingService->appendContactNote($clinicId, $appointmentId, $actorId, ['note' => $this->request->getPost('note'),'request_key' => $this->request->getPost('request_key')]);
        $key = ($result['success'] ?? false) ? 'saved' : (($result['code'] ?? '') === 'REPLAY_CONFLICT' ? 'conflict' : 'failed');
        if ($this->request->isAJAX()) {
            $code = $result['code'] ?? '';
            $status = ($result['success'] ?? false) ? 200 : match($code) {
                'FORBIDDEN' => 403,'NOT_FOUND' => 404,'INVALID_INPUT' => 422,'SCHEMA_NOT_READY' => 503,default => 409
            };
            return $this->response->setStatusCode($status)->setJSON(['success' => (bool)($result['success'] ?? false),'message' => lang('AppointmentContact.'.$key)]);
        }
        $redirect = redirect()->to(base_url('appointment/'.$appointmentId).'#contact-history');
        if (!($result['success'] ?? false)) {
            $note = $this->request->getPost('note');
            $requestKey = $this->request->getPost('request_key');
            if (is_string($note) && preg_match('//u', $note) === 1 && mb_strlen($note) <= 2000 && is_string($requestKey) && preg_match('/^[a-f0-9]{32,64}$/D', $requestKey) === 1) {
                session()->setFlashdata('appointment_contact_retry', ['clinic_id' => $clinicId,'actor_id' => $actorId,'appointment_id' => $appointmentId,'note' => $note,'request_key' => $requestKey]);
            }
        }
        return $redirect->with(($result['success'] ?? false) ? 'success' : 'error', lang('AppointmentContact.'.$key));
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
            'slot_edit_context' => (new \App\Services\AppointmentSlotEditContext($this->db))->issue((int)$clinicId,(int)$appointment['id']),
            'patients' => [$patient], // Only emit the current patient as option
            'validation' => \Config\Services::validation(),
            'loadSelect2' => true,
            'chair_options' => $this->getChairOptions(),
            'booking_context' => $this->providerDirectoryService->getBookingContext((int) $clinicId),
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

        $dynamicEnabled = $this->providerDirectoryService->isEnabled((int) $clinicId);
        $rules = [
            'patient_id' => 'required|integer',
            'appointment_date' => 'required|valid_date',
            'appointment_time' => 'required',
            'duration' => 'required|integer|greater_than[0]',
            'appointment_type' => 'required|in_list[consultation,treatment,follow_up,emergency,cleaning,checkup]',
            'chair_name' => $dynamicEnabled ? 'permit_empty|max_length[100]' : 'required|max_length[100]',
            'provider_user_id' => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Please review the highlighted fields and try again.',
                    'errors'  => $this->validator->getErrors(),
                ]);
            }
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $appointmentData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'appointment_date' => $this->normalizePostedDateOnly('appointment_date'),
            'appointment_time' => $this->request->getPost('appointment_time'),
            'duration' => $this->request->getPost('duration'),
            'appointment_type' => $this->request->getPost('appointment_type'),
            'chair_name' => trim((string) $this->request->getPost('chair_name')),
            'provider_user_id' => $this->request->getPost('provider_user_id'),
            'status' => (string) ($existingAppointment['status'] ?? 'scheduled'),
            'notes' => $this->request->getPost('notes'),
        ];

        if (!array_key_exists('provider_user_id',(array)$this->request->getPost())) unset($appointmentData['provider_user_id']);

        $currentUser = $this->getCurrentUser();
        $actorUserId = $currentUser ? (int) $currentUser->id : (session()->get('user_id') ? (int) session()->get('user_id') : 0);

        $result = $this->schedulingService->updateForClinic((int) $clinicId, (int) $id, $appointmentData, $actorUserId, 'session');

        if (!$result['success']) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $result['message'],
                    'code'    => $result['code'] ?? 'ERROR',
                ]);
            }
            return redirect()->back()->withInput()->with('error', $result['message']);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'     => true,
                'message'     => 'Appointment updated successfully',
                'id'          => $id,
                'appointment' => $result['appointment'] ?? null,
            ]);
        }

        $returnTo = $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['patient', 'patients', 'examination', 'appointment', 'appointments']);
        if ($returnTo) {
            return $this->redirectAfterWorkflow($returnTo, ['patient', 'patients', 'examination', 'appointment', 'appointments'])
                ->with('success', 'Appointment updated successfully');
        }

        return $this->redirectAfterWorkflow('appointment/' . $id, ['patient', 'patients', 'examination', 'appointment', 'appointments'])
            ->with('success', 'Appointment updated successfully');
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

        $completionDecision = null;
        if ($targetStage === 'completed') {
            $assessment = $this->checkoutCompletionService->assess((int) $clinicId, (int) $id);
            try {
                $completionDecision = $this->checkoutCompletionService->authorize(
                    $assessment,
                    in_array($this->request->getPost('checkout_override'), ['1', 1, true, 'on'], true),
                    $this->request->getPost('checkout_override_reason')
                );
            } catch (\DomainException $e) {
                if (!$this->request->isAJAX()) {
                    return redirect()->back()
                        ->withInput()
                        ->with('checkout_completion_issues', $assessment['issue_codes'])
                        ->with('error', $e->getMessage());
                }
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'checkout_issues' => $assessment['issue_codes'],
                    'requires_override' => true,
                ]);
            }
        }

        if (!$this->appointmentModel->update($id, ['status' => $targetStage])) {
            if (!$this->request->isAJAX()) {
                return redirect()->back()->with('error', 'Failed to update visit stage');
            }
            return $this->response->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => 'Failed to update visit stage']);
        }

        $activityDescription = "Appointment visit stage changed from {$currentStage} to {$targetStage}";
        if (!empty($completionDecision['override'])) {
            $activityDescription .= '; checkout acknowledged ['
                . implode(', ', $completionDecision['issue_codes'])
                . ']; reason: ' . $completionDecision['reason'];
        }
        $this->activityLogger->logAppointmentActivity(
            'status',
            (int) $id,
            $activityDescription
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
        if (!\App\Services\ClinicSchedulingSettings::validDate($date)) return $this->response->setStatusCode(422)->setJSON(['error'=>'Invalid date.']);
        $duration = $this->request->getGet('duration') ?? getAppointmentDuration();
        $excludeId = $this->request->getGet('exclude_id');
        $rawChair = $this->request->getGet('chair_name');
        $chairName = is_string($rawChair) ? trim($rawChair) : '';
        $editContextToken = $this->request->getGet('edit_context');
        $providerUserId = $this->request->getGet('provider_user_id');

        if (empty($date)) {
            return $this->response->setJSON(['error' => 'Date is required']);
        }

        $dynamicEnabled = $this->providerDirectoryService->isEnabled((int) $clinicId);
        if ($dynamicEnabled) {
            if (empty($providerUserId) && $chairName === '') {
                return $this->response->setJSON(['error' => 'Doctor is required']);
            }
        } else {
            if ($chairName === '') {
                return $this->response->setJSON(['error' => 'Doctor is required']);
            }
        }

        $duration = \App\Services\ClinicSchedulingSettings::positiveInt($duration,480);
        $providerUserId = $providerUserId === null || $providerUserId === '' ? null : \App\Services\ClinicSchedulingSettings::positiveInt($providerUserId);
        $excludeId = $excludeId === null || $excludeId === '' ? null : \App\Services\ClinicSchedulingSettings::positiveInt($excludeId);
        if (!$duration || ($dynamicEnabled && !$providerUserId)) return $this->response->setStatusCode(422)->setJSON(['error'=>'Invalid scheduling selection.']);
        $timeSlots = $this->schedulingService->availableSlots(
            (int) $clinicId,
            $date,
            (int) $duration,
            !empty($providerUserId) ? (int) $providerUserId : null,
            !empty($excludeId) ? (int) $excludeId : null,
            $chairName !== '' ? $chairName : null,
            is_string($editContextToken) ? $editContextToken : null
        );

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

        try {
            $workingHours = (new \App\Services\ClinicSchedulingSettings($this->db))->hours((int) $clinicId);
        } catch (\Throwable $e) {
            $workingHours = ['start' => '09:00', 'end' => '17:00', 'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']];
        }

        $selectedDate = $this->request->getGet('date') ?? (new \DateTimeImmutable('now',(new \App\Services\ClinicSchedulingSettings($this->db))->timezone((int)$clinicId)))->format('Y-m-d');
        if (!\App\Services\ClinicSchedulingSettings::validDate($selectedDate)) $selectedDate=date('Y-m-d');
        $data = [
            'title' => 'Appointment Calendar',
            'selected_date' => $selectedDate,
            'dayboard_appointments' => $this->appointmentModel->getAppointmentsByDateByClinic($clinicId,$selectedDate),
            'booking_context' => $this->providerDirectoryService->getBookingContext((int)$clinicId),
            'stats' => $this->appointmentModel->getAppointmentStatsByClinic($clinicId),
            'workingHours' => $workingHours,
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
                    'provider_user_id' => $appointment['provider_user_id'] ?? null,
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
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId > 0) {
            try {
                return (new \App\Services\ClinicSchedulingSettings($this->db))->chairs($clinicId);
            } catch (\Throwable $e) {}
        }
        return ['Doctor 1'];
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
