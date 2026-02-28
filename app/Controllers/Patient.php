<?php

namespace App\Controllers;

use App\Models\PatientModel;
use App\Models\PatientWhatsAppModel;
use App\Models\WhatsAppOptoutModel;
use App\Models\ExaminationModel;
use App\Models\AppointmentModel;
use App\Models\FinanceModel;
use App\Models\OdontogramModel;
use App\Models\TreatmentModel;
use App\Models\PrescriptionModel;
use App\Models\FileAttachmentModel;
use App\Models\TreatmentContractModel;
use App\Services\ActivityLogger;
use App\Services\PlanGuard;

class Patient extends BaseController
{
    protected $patientModel;
    protected $examinationModel;
    protected $appointmentModel;
    protected $financeModel;
    protected $odontogramModel;
    protected $treatmentModel;
    protected $prescriptionModel;
    protected $attachmentModel;
    protected $contractModel;
    protected $activityLogger;
    protected $storageService;
    protected $planGuard;
    protected $patientWhatsAppModel;
    protected $whatsAppOptoutModel;

    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->examinationModel = new ExaminationModel();
        $this->appointmentModel = new AppointmentModel();
        $this->financeModel = new FinanceModel();
        $this->odontogramModel = new OdontogramModel();
        $this->treatmentModel = new TreatmentModel();
        $this->prescriptionModel = new PrescriptionModel();
        $this->attachmentModel = new FileAttachmentModel();
        $this->contractModel = new TreatmentContractModel();
        $this->activityLogger = new ActivityLogger();
        $this->storageService = service('storage');
        $this->planGuard = new PlanGuard();
        $this->patientWhatsAppModel = new PatientWhatsAppModel();
        $this->whatsAppOptoutModel = new WhatsAppOptoutModel();
    }

    public function index()
    {
        // Handle AJAX requests for server-side processing
        if ($this->request->isAJAX()) {
            // Log the AJAX request
            log_message('debug', 'AJAX request detected in index() method');
            return $this->getPatientsData();
        }

        $data = [
            'title' => 'Patient Management',
            'pageTitle' => 'Patients',
            'patients' => [],
            'pager' => null,
            'loadSelect2' => false  // Don't load Select2 on patient index page
        ];

        return $this->view('patient/index', $data); 
    }

    /**
     * Simple test method to check if data is available
     */
    public function testSimple()
    {
        try {
            log_message('debug', 'Patient testSimple method called');
            
            // Get all patients without any filters
            $patients = $this->patientModel->findAll();
            log_message('debug', 'Patient testSimple - Found ' . count($patients) . ' patients');
            
            if (empty($patients)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No patients found',
                    'count' => 0
                ]);
            }
            
            // Return first patient as test
            $firstPatient = $patients[0];
            log_message('debug', 'Patient testSimple - First patient: ' . json_encode($firstPatient));
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Patients found',
                'count' => count($patients),
                'first_patient' => $firstPatient
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Patient testSimple error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get patients data for server-side processing
     */
    public function getData()
    {
        try {
            log_message('debug', 'Patient getData method called');
            $request = $this->request;
            
            // S4-02a: Fail closed if clinic context is missing
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            // DataTables parameters
            $draw = intval($request->getPost('draw') ?? 1);
            $start = intval($request->getPost('start') ?? 0);
            $length = intval($request->getPost('length') ?? 10);
            
            // Handle search parameter
            $searchParam = $request->getPost('search');
            $searchValue = '';
            if (is_array($searchParam) && isset($searchParam['value'])) {
                $searchValue = trim($searchParam['value']);
            }
            
            // Handle order parameter
            $orderParam = $request->getPost('order');
            $orderColumn = 0;
            $orderDir = 'desc';
            if (is_array($orderParam) && isset($orderParam[0])) {
                $orderColumn = intval($orderParam[0]['column'] ?? 0);
                $orderDir = $orderParam[0]['dir'] ?? 'desc';
            }
            
            // Base builder scoped to clinic
            $baseBuilder = $this->patientModel->forClinic($clinicId);

            // Get total records count scoped to clinic
            $totalRecords = $baseBuilder->countAllResults(false);
            log_message('debug', 'Patient getData - Total records (scoped): ' . $totalRecords);
            
            // If no patients exist, return empty result
            if ($totalRecords == 0) {
                log_message('debug', 'Patient getData - No records found, returning empty data');
                return $this->response->setJSON([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]);
            }
            
            // Build query
            $baseBuilder = $this->patientModel->forClinic($clinicId);

            // Apply search filter
            if (!empty($searchValue)) {
                $baseBuilder->groupStart()
                    ->like('first_name', $searchValue)
                    ->orLike('last_name', $searchValue)
                    ->orLike('email', $searchValue)
                    ->orLike('phone', $searchValue)
                    ->orLike('patient_id', $searchValue)
                    ->orLike('address', $searchValue)
                    ->orLike('city', $searchValue)
                    ->groupEnd();
            }

            // Get filtered count
            $filteredBuilder = clone $baseBuilder;
            $filteredRecords = $filteredBuilder->countAllResults(false);
            log_message('debug', 'Patient getData - Filtered records: ' . $filteredRecords);

            // Get data with ordering and pagination
            $dataBuilder = clone $filteredBuilder;
            $patients = $dataBuilder->orderBy('created_at', $orderDir)
                ->limit($length, $start)
                ->get()
                ->getResultArray();
            
            log_message('debug', 'Patient getData - Retrieved patients count: ' . count($patients));
            
            // Format data
            $data = [];
            foreach ($patients as $patient) {
                // Get examination count for this patient
                // NOTE: Examinations table should also be scoped, but for now we follow patient link
                $examinationCount = $this->examinationModel->where('patient_id', $patient['id'])->countAllResults(false);
                
                // Format dates according to settings
                $createdAt = $patient['created_at'] ?? '';
                $dateOfBirth = $patient['date_of_birth'] ?? '';
                
                $data[] = [
                    'id' => intval($patient['id'] ?? 0),
                    'patient_id' => $patient['patient_id'] ?? '',
                    'name' => trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')),
                    'email' => $patient['email'] ?? '',
                    'phone' => $patient['phone'] ?? '',
                    'status' => $patient['status'] ?? 'active',
                    'examinations' => $examinationCount,
                    'date_of_birth' => $dateOfBirth ? formatDate($dateOfBirth) : '',
                    'created_at' => $createdAt ? formatDateTime($createdAt) : ''
                ];
            }
            
            $response = [
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ];
            
            log_message('debug', 'Patient getData - Final response: ' . json_encode($response));
            
            return $this->response->setJSON($response);
            
        } catch (\Exception $e) {
            log_message('error', 'Patient DataTables error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading data: ' . $e->getMessage()
            ]);
        }
    }

    public function create()
    {
        $returnTo = $this->sanitizeInternalReturnTo(
            $this->request->getGet('return_to'),
            ['dashboard', 'patient', 'patients', 'appointment', 'appointments', 'examination', 'examinations']
        );

        $data = [
            'title' => 'Add New Patient',
            'validation' => \Config\Services::validation(),
            'return_to' => $returnTo
        ];

        return $this->view('patient/create', $data);
    }

    public function store()
    {
        $clinicId = session()->get('active_clinic_id');
        
        // P5-10: Plan Quota Enforcement (Patients)
        $planGuard = new \App\Services\PlanGuard();
        $db = \Config\Database::connect();
        
        $db->transStart();
        try {
            // P5-21: Plan Quota Enforcement (Patients)
            // Use 'patients_active_max' explicitly
            $planGuard->assertQuota($clinicId, 'patients_active_max', 1);
            
            // Set content type to JSON for AJAX requests
            if ($this->request->isAJAX()) {
                $this->response->setContentType('application/json');
            }

            $rules = [
                'first_name' => 'required|min_length[2]|max_length[50]',
                'last_name' => 'required|min_length[2]|max_length[50]',
                'email' => 'permit_empty|valid_email|is_unique[patients.email]',
                'phone' => 'required|min_length[10]|max_length[15]',
                'date_of_birth' => 'required|valid_date',
                'gender' => 'required|in_list[male,female,other]'
            ];
            
            if (!$this->validate($rules)) {
                $db->transRollback();
                // Check if this is an AJAX request
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $this->validator->getErrors()
                    ]);
                }
                return redirect()->back()->withInput()->with('validation', $this->validator);
            }

            $email = trim((string) $this->request->getPost('email'));
            if ($email === '') {
                $email = null;
            }

            $currentUser = $this->getCurrentUser();
            $currentUserId = $currentUser ? (int) $currentUser->id : (session()->get('user_id') ? (int) session()->get('user_id') : null);

            $boolField = static function ($value): int {
                return in_array($value, ['1', 1, true, 'true', 'on', 'yes'], true) ? 1 : 0;
            };
            $textField = static function ($value): ?string {
                $value = trim((string) $value);
                return $value === '' ? null : $value;
            };

            $alertHasAllergies = $boolField($this->request->getPost('alert_has_allergies'));
            $alertTakingMeds = $boolField($this->request->getPost('alert_taking_medications'));
            $alertBloodThinners = $boolField($this->request->getPost('alert_blood_thinners'));
            $alertDiabetes = $boolField($this->request->getPost('alert_diabetes'));
            $alertHighBloodPressure = $boolField($this->request->getPost('alert_high_blood_pressure'));
            $alertHeartCondition = $boolField($this->request->getPost('alert_heart_condition'));
            $alertPregnant = $boolField($this->request->getPost('alert_pregnant'));
            $alertAnesthesiaProblem = $boolField($this->request->getPost('alert_anesthesia_problem'));

            $alertAllergiesDetails = $alertHasAllergies ? $textField($this->request->getPost('alert_allergies_details')) : null;
            $alertMedicationsDetails = $alertTakingMeds ? $textField($this->request->getPost('alert_medications_details')) : null;
            $alertBloodThinnersDetails = $alertBloodThinners ? $textField($this->request->getPost('alert_blood_thinners_details')) : null;
            $alertDiabetesDetails = $alertDiabetes ? $textField($this->request->getPost('alert_diabetes_details')) : null;
            $alertBloodPressureDetails = $alertHighBloodPressure ? $textField($this->request->getPost('alert_blood_pressure_details')) : null;
            $alertHeartConditionDetails = $alertHeartCondition ? $textField($this->request->getPost('alert_heart_condition_details')) : null;
            $alertPregnancyDetails = $alertPregnant ? $textField($this->request->getPost('alert_pregnancy_details')) : null;
            $alertAnesthesiaProblemDetails = $alertAnesthesiaProblem ? $textField($this->request->getPost('alert_anesthesia_problem_details')) : null;

            // Backward-compatible summary text for legacy views that still render medical_history/allergies.
            $legacyMedicalHistoryLines = [];
            if ($alertTakingMeds) {
                $legacyMedicalHistoryLines[] = 'Taking medications' . ($alertMedicationsDetails ? ': ' . $alertMedicationsDetails : '');
            }
            if ($alertBloodThinners) {
                $legacyMedicalHistoryLines[] = 'Taking blood thinners' . ($alertBloodThinnersDetails ? ': ' . $alertBloodThinnersDetails : '');
            }
            if ($alertDiabetes) {
                $legacyMedicalHistoryLines[] = 'Diabetes' . ($alertDiabetesDetails ? ': ' . $alertDiabetesDetails : '');
            }
            if ($alertHighBloodPressure) {
                $legacyMedicalHistoryLines[] = 'High blood pressure' . ($alertBloodPressureDetails ? ': ' . $alertBloodPressureDetails : '');
            }
            if ($alertHeartCondition) {
                $legacyMedicalHistoryLines[] = 'Heart condition / surgery history' . ($alertHeartConditionDetails ? ': ' . $alertHeartConditionDetails : '');
            }
            if ($alertPregnant) {
                $legacyMedicalHistoryLines[] = 'Pregnant / possibly pregnant' . ($alertPregnancyDetails ? ': ' . $alertPregnancyDetails : '');
            }
            if ($alertAnesthesiaProblem) {
                $legacyMedicalHistoryLines[] = 'Local anesthesia / sedation issue' . ($alertAnesthesiaProblemDetails ? ': ' . $alertAnesthesiaProblemDetails : '');
            }

            $legacyMedicalHistory = !empty($legacyMedicalHistoryLines) ? implode("\n", $legacyMedicalHistoryLines) : null;
            $legacyAllergies = $alertHasAllergies ? ($alertAllergiesDetails ?? 'Yes') : null;

            $patientData = [
                'first_name' => $this->request->getPost('first_name'),
                'last_name' => $this->request->getPost('last_name'),
                'email' => $email,
                'phone' => $this->request->getPost('phone'),
                'date_of_birth' => $this->normalizePostedDateOnly('date_of_birth'),
                'gender' => $this->request->getPost('gender'),
                'address' => $this->request->getPost('address'),
                'city' => $this->request->getPost('city'),
                'state' => $this->request->getPost('state'),
                'zip_code' => $this->request->getPost('zip_code'),
                'country' => $this->request->getPost('country'),
                'emergency_contact_name' => $this->request->getPost('emergency_contact_name'),
                'emergency_contact_phone' => $this->request->getPost('emergency_contact_phone'),
                'medical_history' => $legacyMedicalHistory,
                'allergies' => $legacyAllergies,
                'alert_has_allergies' => $alertHasAllergies,
                'alert_allergies_details' => $alertAllergiesDetails,
                'alert_taking_medications' => $alertTakingMeds,
                'alert_medications_details' => $alertMedicationsDetails,
                'alert_blood_thinners' => $alertBloodThinners,
                'alert_blood_thinners_details' => $alertBloodThinnersDetails,
                'alert_diabetes' => $alertDiabetes,
                'alert_diabetes_details' => $alertDiabetesDetails,
                'alert_high_blood_pressure' => $alertHighBloodPressure,
                'alert_blood_pressure_details' => $alertBloodPressureDetails,
                'alert_heart_condition' => $alertHeartCondition,
                'alert_heart_condition_details' => $alertHeartConditionDetails,
                'alert_pregnant' => $alertPregnant,
                'alert_pregnancy_details' => $alertPregnancyDetails,
                'alert_anesthesia_problem' => $alertAnesthesiaProblem,
                'alert_anesthesia_problem_details' => $alertAnesthesiaProblemDetails,
                'insurance_provider' => $this->request->getPost('insurance_provider'),
                'insurance_number' => $this->request->getPost('insurance_number'),
                'notes' => $this->request->getPost('notes'),
                'status' => 'active',
                'created_by' => $currentUserId
            ];

            if ($this->patientModel->insert($patientData)) {
                $patientId = $this->patientModel->getInsertID();
                
                // Log the patient creation activity
                $this->activityLogger->logPatientActivity(
                    'create',
                    $patientId,
                    "New patient '{$patientData['first_name']} {$patientData['last_name']}' registered successfully"
                );
                
                $db->transCommit();
                // Check if this is an AJAX request
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Patient added successfully',
                        'patient_id' => $patientId
                    ]);
                }

                $returnTo = $this->sanitizeInternalReturnTo(
                    $this->request->getPost('return_to'),
                    ['dashboard', 'patient', 'patients', 'appointment', 'appointments', 'examination', 'examinations']
                );

                $postSubmitAction = (string) $this->request->getPost('post_submit_action');
                if ($postSubmitAction === 'save_and_book') {
                    if ($this->hasPermission('appointments', 'create')) {
                        return redirect()->to(base_url('appointment/create?patient_id=' . (int) $patientId . '&return_to=patient/' . (int) $patientId))
                            ->with('success', 'Patient has been added successfully! Continue by booking an appointment.');
                    }

                    $fallbackRedirect = $returnTo ? base_url($returnTo) : '/patient';
                    return redirect()->to($fallbackRedirect)
                        ->with('success', 'Patient has been added successfully! Appointment booking was skipped because you do not have appointment create permission.');
                }

                if ($returnTo) {
                    return redirect()->to(base_url($returnTo))->with('success', 'Patient has been added successfully!');
                }
                return redirect()->to('/patient')->with('success', 'Patient has been added successfully!');
            } else {
                $db->transRollback();
                // Check if this is an AJAX request
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to add patient. Please try again.',
                        'db_errors' => $this->patientModel->errors()
                    ]);
                }
                return redirect()->back()->withInput()->with('error', 'Failed to add patient. Please check your input and try again.');
            }
        } catch (\Exception $e) {
            $db->transRollback();

            // P5-21b: Rethrow PageNotFoundException to preserve 404 concealment
            if ($e instanceof \CodeIgniter\Exceptions\PageNotFoundException) {
                throw $e;
            }

            // Log other errors
            log_message('error', 'Patient creation error: ' . $e->getMessage());
            
            // Check if this is an AJAX request
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'An error occurred while adding the patient: ' . $e->getMessage()
                ]);
            }
            return redirect()->back()->withInput()->with('error', 'An error occurred while adding the patient');
        }
    }

    public function show($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $patient = $this->patientModel
            ->select('patients.*, users.first_name as creator_first_name, users.last_name as creator_last_name')
            ->join('users', 'users.id = patients.created_by', 'left')
            ->where('patients.clinic_id', $clinicId)
            ->where('patients.id', $id)
            ->first();

        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $attachmentsEnabled = $this->planGuard->isFeatureEnabled($clinicId, 'attachments.enabled', false);
        $contractsEnabled = $this->planGuard->isFeatureEnabled($clinicId, 'contracts.enabled', false);
        $attachments = [];
        $storageUsageBytes = 0;
        $storageLimitGb = $this->planGuard->getLimitValue($clinicId, 'storage_gb', 0);
        $maxAttachmentMb = $this->planGuard->getLimitValue($clinicId, 'attachments_max_file_mb', 5);
        $contracts = [];

        if ((int) $maxAttachmentMb === -1) {
            $maxAttachmentMb = null;
        } elseif (!is_numeric($maxAttachmentMb) || (int) $maxAttachmentMb <= 0) {
            $maxAttachmentMb = 5;
        }

        if ($attachmentsEnabled) {
            $attachments = $this->attachmentModel
                ->where('entity_type', 'patient')
                ->where('entity_id', $id)
                ->where('purpose', 'attachment')
                ->orderBy('created_at', 'DESC')
                ->findAll();

            $storageUsageBytes = $this->attachmentModel->getTotalSizeByClinic($clinicId);
        }

        if ($contractsEnabled) {
            $contracts = $this->contractModel
                ->where('patient_id', $id)
                ->orderBy('created_at', 'DESC')
                ->findAll();
        }

        $examinations = $this->examinationModel
            ->select('examinations.*, users.first_name as doctor_first_name, users.last_name as doctor_last_name')
            ->join('users', 'users.id = examinations.created_by', 'left')
            ->where('examinations.clinic_id', $clinicId)
            ->where('examinations.patient_id', $id)
            ->orderBy('examinations.examination_date', 'DESC')
            ->findAll();

        $appointments = $this->appointmentModel
            ->select('appointments.*, users.first_name as doctor_first_name, users.last_name as doctor_last_name')
            ->join('users', 'users.id = appointments.created_by', 'left')
            ->where('appointments.clinic_id', $clinicId)
            ->where('appointments.patient_id', $id)
            ->orderBy('appointments.appointment_date', 'DESC')
            ->orderBy('appointments.appointment_time', 'ASC')
            ->findAll();

        $treatments = $this->treatmentModel
            ->select('treatments.*, users.first_name as doctor_first_name, users.last_name as doctor_last_name')
            ->join('users', 'users.id = treatments.created_by', 'left')
            ->where('treatments.clinic_id', $clinicId)
            ->where('treatments.patient_id', $id)
            ->orderBy('treatments.start_date', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Patient Details - ' . $patient['first_name'] . ' ' . $patient['last_name'],
            'patient' => $patient,
            'examinations' => $examinations,
            'appointments' => $appointments,
            'finances' => $this->financeModel->getFinanceByPatient($id),
            'odontogram' => $this->odontogramModel->getOdontogramByPatient($id),
            'odontogram_stats' => $this->odontogramModel->getOdontogramStats($id),
            'treatments' => $treatments,
            'prescriptions' => $this->prescriptionModel->getPrescriptionsByPatient($id),
            'attachments_enabled' => $attachmentsEnabled,
            'attachments' => $attachments,
            'attachments_storage_usage_bytes' => $storageUsageBytes,
            'attachments_storage_limit_gb' => $storageLimitGb,
            'attachments_max_file_mb' => $maxAttachmentMb,
            'contracts_enabled' => $contractsEnabled,
            'contracts' => $contracts,
            'patient_whatsapp' => $this->patientWhatsAppModel->findByPatient((int) $clinicId, (int) $id),
            'patient_whatsapp_optout' => $this->whatsAppOptoutModel
                ->where('clinic_id', $clinicId)
                ->where('patient_id', $id)
                ->orderBy('id', 'DESC')
                ->first(),
            'return_to' => $this->sanitizeInternalReturnTo(
                $this->request->getGet('return_to'),
                ['patient', 'patients', 'dashboard', 'appointment', 'appointments', 'examination', 'examinations']
            )
        ];

        return $this->view('patient/show', $data);
    }

    public function saveWhatsAppIdentity($patientId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request.',
            ]);
        }

        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'TENANT_CONTEXT_REQUIRED',
            ]);
        }

        $patientId = (int) $patientId;
        $patient = $this->patientModel->findByClinic($clinicId, $patientId);
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $waNumberRaw = trim((string) $this->request->getPost('wa_number'));
        $waNumber = $this->normalizeWhatsAppNumber($waNumberRaw);

        if ($waNumber === null) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Enter a valid WhatsApp number in international format (example: +970599999999).',
            ]);
        }

        $ok = $this->patientWhatsAppModel->upsertForPatient($clinicId, $patientId, [
            'wa_number' => $waNumber,
            'resolution_status' => 'pending',
            'verified_at' => null,
            'last_error_code' => null,
        ]);

        if (!$ok) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save WhatsApp number.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'WhatsApp number saved. Verification status reset to pending.',
            'wa_number' => $waNumber,
        ]);
    }

    private function normalizeWhatsAppNumber(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $hasPlus = str_starts_with($raw, '+');
        $digits = preg_replace('/\D+/', '', $raw);
        if (!is_string($digits) || $digits === '') {
            return null;
        }

        if (strlen($digits) < 8 || strlen($digits) > 15) {
            return null;
        }

        // Keep a consistent E.164-like representation in UI/storage.
        return ($hasPlus ? '+' : '+') . $digits;
    }

    public function uploadAttachment($patientId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $patient = $this->patientModel->findByClinic($clinicId, $patientId);
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $this->planGuard->assertFeature($clinicId, 'attachments.enabled', [
            'entity_type' => 'patient',
            'entity_id' => $patientId
        ]);

        $files = $this->request->getFileMultiple('attachment');
        if (empty($files)) {
            $single = $this->request->getFile('attachment');
            $files = $single ? [$single] : [];
        }

        if (empty($files)) {
            return $this->failAttachmentUpload('No valid file uploaded.');
        }

        $totalSize = 0;
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                return $this->failAttachmentUpload('One or more files are invalid.');
            }
            $validation = $this->validateAttachmentFile($file, $clinicId);
            if (!$validation['ok']) {
                return $this->failAttachmentUpload($validation['message']);
            }
            $totalSize += (int) $file->getSize();
        }

        $this->planGuard->assertQuota($clinicId, 'storage_gb', $totalSize, [
            'entity_type' => 'patient',
            'entity_id' => $patientId,
        ]);

        $category = trim((string) $this->request->getPost('category'));
        $notes = trim((string) $this->request->getPost('notes'));

        try {
            $uploaded = [];
            foreach ($files as $file) {
                $attachment = $this->storageService->store($file, $clinicId, 'patient', $patientId, 'attachment');

                if (!empty($category) || !empty($notes)) {
                    $this->attachmentModel->update($attachment['id'], [
                        'category' => $category ?: null,
                        'notes' => $notes ?: null
                    ]);
                }

                $this->activityLogger->logPatientActivity(
                    'attachment_upload',
                    $patientId,
                    "Attachment '{$attachment['original_name']}' uploaded"
                );

                $uploaded[] = $attachment;
            }

            $count = count($uploaded);
            $message = $count === 1 ? 'Attachment uploaded successfully.' : "{$count} attachments uploaded successfully.";

            return $this->handleAttachmentSuccess($message);
        } catch (\Exception $e) {
            if ($e instanceof \CodeIgniter\Exceptions\PageNotFoundException) {
                throw $e;
            }

            log_message('error', 'Attachment upload failed: ' . $e->getMessage());
            return $this->failAttachmentUpload('Upload failed. Please try again.');
        }
    }

    public function deleteAttachment($patientId, $attachmentId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $patient = $this->patientModel->findByClinic($clinicId, $patientId);
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $this->planGuard->assertFeature($clinicId, 'attachments.enabled', [
            'entity_type' => 'patient',
            'entity_id' => $patientId
        ]);

        $attachment = $this->attachmentModel
            ->where('entity_type', 'patient')
            ->where('entity_id', $patientId)
            ->where('purpose', 'attachment')
            ->find($attachmentId);

        if (!$attachment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Attachment not found');
        }

        $this->storageService->delete($attachmentId, $clinicId);

        $this->activityLogger->logPatientActivity(
            'attachment_delete',
            $patientId,
            "Attachment '{$attachment['original_name']}' deleted"
        );

        return $this->handleAttachmentSuccess('Attachment deleted.');
    }

    private function validateAttachmentFile($file, int $clinicId): array
    {
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'xlsx', 'zip'];
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
            'application/x-zip-compressed'
        ];

        $extension = strtolower((string) $file->getExtension());
        if (!in_array($extension, $allowedExts, true)) {
            return ['ok' => false, 'message' => 'Unsupported file type.'];
        }

        $mime = (string) $file->getMimeType();
        if (!in_array($mime, $allowedMimes, true)) {
            // Allow docx/xlsx that present as zip
            if (!in_array($extension, ['docx', 'xlsx', 'zip'], true) || !in_array($mime, ['application/zip', 'application/x-zip-compressed'], true)) {
                return ['ok' => false, 'message' => 'Unsupported file format.'];
            }
        }

        $limitMb = $this->planGuard->getLimitValue($clinicId, 'attachments_max_file_mb', 5);
        if ((int) $limitMb === -1) {
            return ['ok' => true];
        }

        if (!is_numeric($limitMb) || (int) $limitMb <= 0) {
            $limitMb = 5;
        }

        $maxBytes = (int) $limitMb * 1024 * 1024;
        $size = (int) $file->getSize();
        if ($size > $maxBytes) {
            return ['ok' => false, 'message' => 'File exceeds the maximum allowed size.'];
        }

        return ['ok' => true];
    }

    private function handleAttachmentSuccess(string $message)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => $message]);
        }

        return redirect()->back()
            ->with('success', $message)
            ->with('attachments_flash', '1');
    }

    private function failAttachmentUpload(string $message)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => $message]);
        }

        return redirect()->back()
            ->with('error', $message)
            ->with('attachments_flash', '1');
    }

    public function edit($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $patient = $this->patientModel->findByClinic($clinicId, $id);
        
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $data = [
            'title' => 'Edit Patient - ' . $patient['first_name'] . ' ' . $patient['last_name'],
            'patient' => $patient,
            'validation' => \Config\Services::validation(),
            'return_to' => $this->sanitizeInternalReturnTo(
                $this->request->getGet('return_to'),
                ['patient', 'patients', 'dashboard', 'appointment', 'appointments', 'examination', 'examinations']
            )
        ];

        return $this->view('patient/edit', $data);
    }

    public function update($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $patient = $this->patientModel->findByClinic($clinicId, $id);
        
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => 'permit_empty|valid_email',
            'phone' => 'required|min_length[10]|max_length[15]',
            'date_of_birth' => 'required|valid_date',
            'gender' => 'required|in_list[male,female,other]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Check email uniqueness manually for updates
        $email = trim((string) $this->request->getPost('email'));
        if (!empty($email)) {
            $existingPatient = $this->patientModel->where('email', $email)
                                                ->where('clinic_id', $clinicId)
                                                ->where('id !=', $id)
                                                ->first();
            if ($existingPatient) {
                return redirect()->back()->withInput()->with('error', 'This email is already registered to another patient in this clinic');
            }
        }

        if ($email === '') {
            $email = null;
        }

        $boolField = static function ($value): int {
            return in_array($value, ['1', 1, true, 'true', 'on', 'yes'], true) ? 1 : 0;
        };
        $textField = static function ($value): ?string {
            $value = trim((string) $value);
            return $value === '' ? null : $value;
        };

        $alertHasAllergies = $boolField($this->request->getPost('alert_has_allergies'));
        $alertTakingMeds = $boolField($this->request->getPost('alert_taking_medications'));
        $alertBloodThinners = $boolField($this->request->getPost('alert_blood_thinners'));
        $alertDiabetes = $boolField($this->request->getPost('alert_diabetes'));
        $alertHighBloodPressure = $boolField($this->request->getPost('alert_high_blood_pressure'));
        $alertHeartCondition = $boolField($this->request->getPost('alert_heart_condition'));
        $alertPregnant = $boolField($this->request->getPost('alert_pregnant'));
        $alertAnesthesiaProblem = $boolField($this->request->getPost('alert_anesthesia_problem'));

        $alertAllergiesDetails = $alertHasAllergies ? $textField($this->request->getPost('alert_allergies_details')) : null;
        $alertMedicationsDetails = $alertTakingMeds ? $textField($this->request->getPost('alert_medications_details')) : null;
        $alertBloodThinnersDetails = $alertBloodThinners ? $textField($this->request->getPost('alert_blood_thinners_details')) : null;
        $alertDiabetesDetails = $alertDiabetes ? $textField($this->request->getPost('alert_diabetes_details')) : null;
        $alertBloodPressureDetails = $alertHighBloodPressure ? $textField($this->request->getPost('alert_blood_pressure_details')) : null;
        $alertHeartConditionDetails = $alertHeartCondition ? $textField($this->request->getPost('alert_heart_condition_details')) : null;
        $alertPregnancyDetails = $alertPregnant ? $textField($this->request->getPost('alert_pregnancy_details')) : null;
        $alertAnesthesiaProblemDetails = $alertAnesthesiaProblem ? $textField($this->request->getPost('alert_anesthesia_problem_details')) : null;

        $legacyMedicalHistoryLines = [];
        if ($alertTakingMeds) {
            $legacyMedicalHistoryLines[] = 'Taking medications' . ($alertMedicationsDetails ? ': ' . $alertMedicationsDetails : '');
        }
        if ($alertBloodThinners) {
            $legacyMedicalHistoryLines[] = 'Taking blood thinners' . ($alertBloodThinnersDetails ? ': ' . $alertBloodThinnersDetails : '');
        }
        if ($alertDiabetes) {
            $legacyMedicalHistoryLines[] = 'Diabetes' . ($alertDiabetesDetails ? ': ' . $alertDiabetesDetails : '');
        }
        if ($alertHighBloodPressure) {
            $legacyMedicalHistoryLines[] = 'High blood pressure' . ($alertBloodPressureDetails ? ': ' . $alertBloodPressureDetails : '');
        }
        if ($alertHeartCondition) {
            $legacyMedicalHistoryLines[] = 'Heart condition / surgery history' . ($alertHeartConditionDetails ? ': ' . $alertHeartConditionDetails : '');
        }
        if ($alertPregnant) {
            $legacyMedicalHistoryLines[] = 'Pregnant / possibly pregnant' . ($alertPregnancyDetails ? ': ' . $alertPregnancyDetails : '');
        }
        if ($alertAnesthesiaProblem) {
            $legacyMedicalHistoryLines[] = 'Local anesthesia / sedation issue' . ($alertAnesthesiaProblemDetails ? ': ' . $alertAnesthesiaProblemDetails : '');
        }

        $legacyMedicalHistory = !empty($legacyMedicalHistoryLines) ? implode("\n", $legacyMedicalHistoryLines) : null;
        $legacyAllergies = $alertHasAllergies ? ($alertAllergiesDetails ?? 'Yes') : null;

        $patientData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $email,
            'phone' => $this->request->getPost('phone'),
            'date_of_birth' => $this->normalizePostedDateOnly('date_of_birth'),
            'gender' => $this->request->getPost('gender'),
            'address' => $this->request->getPost('address'),
            'city' => $this->request->getPost('city'),
            'state' => $this->request->getPost('state'),
            'zip_code' => $this->request->getPost('zip_code'),
            'country' => $this->request->getPost('country'),
            'emergency_contact_name' => $this->request->getPost('emergency_contact_name'),
            'emergency_contact_phone' => $this->request->getPost('emergency_contact_phone'),
            'medical_history' => $legacyMedicalHistory,
            'allergies' => $legacyAllergies,
            'alert_has_allergies' => $alertHasAllergies,
            'alert_allergies_details' => $alertAllergiesDetails,
            'alert_taking_medications' => $alertTakingMeds,
            'alert_medications_details' => $alertMedicationsDetails,
            'alert_blood_thinners' => $alertBloodThinners,
            'alert_blood_thinners_details' => $alertBloodThinnersDetails,
            'alert_diabetes' => $alertDiabetes,
            'alert_diabetes_details' => $alertDiabetesDetails,
            'alert_high_blood_pressure' => $alertHighBloodPressure,
            'alert_blood_pressure_details' => $alertBloodPressureDetails,
            'alert_heart_condition' => $alertHeartCondition,
            'alert_heart_condition_details' => $alertHeartConditionDetails,
            'alert_pregnant' => $alertPregnant,
            'alert_pregnancy_details' => $alertPregnancyDetails,
            'alert_anesthesia_problem' => $alertAnesthesiaProblem,
            'alert_anesthesia_problem_details' => $alertAnesthesiaProblemDetails,
            'notes' => $this->request->getPost('notes')
        ];

        if ($this->patientModel->update($id, $patientData)) {
            log_message('info', "Patient {$id} updated successfully");
            
            // Log the patient update activity
            $this->activityLogger->logPatientActivity(
                'update',
                $id,
                "Patient '{$patientData['first_name']} {$patientData['last_name']}' information updated"
            );

            $returnTo = $this->sanitizeInternalReturnTo(
                $this->request->getPost('return_to'),
                ['patient', 'patients', 'dashboard', 'appointment', 'appointments', 'examination', 'examinations']
            );
            if ($returnTo) {
                return redirect()->to(base_url($returnTo))->with('success', 'Patient information has been updated successfully!');
            }
            
            return redirect()->to('/patient/' . $id)->with('success', 'Patient information has been updated successfully!');
        } else {
            $errors = $this->patientModel->errors();
            log_message('error', 'Patient update failed: ' . json_encode($errors));
            log_message('error', 'Patient data that failed: ' . json_encode($patientData));
            return redirect()->back()->withInput()->with('error', 'Failed to update patient information. Please check your input and try again.');
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

        $patient = $this->patientModel->findByClinic($clinicId, $id);
        
        if (!$patient) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Patient not found']);
            }
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        if ($this->patientModel->delete($id)) {
            // Log the patient deletion activity
            $this->activityLogger->logPatientActivity(
                'delete',
                $id,
                "Patient '{$patient['first_name']} {$patient['last_name']}' deleted from the system"
            );
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true]);
            }
            return redirect()->to('/patient')->with('success', 'Patient has been deleted successfully!');
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to delete patient']);
            }
            return redirect()->back()->with('error', 'Failed to delete patient. Please try again.');
        }
    }

    public function search()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
             return redirect()->to('/clinic/select');
        }

        $searchTerm = $this->request->getGet('q');
        
        if (empty($searchTerm)) {
            return redirect()->to('/patient');
        }

        $data = [
            'title' => 'Search Results for: ' . $searchTerm,
            'patients' => $this->patientModel->searchPatientsByClinic($clinicId, $searchTerm),
            'search_term' => $searchTerm
        ];

        return $this->view('patient/search', $data);
    }

    public function getPatientData($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $patient = $this->patientModel->findByClinic($clinicId, $id);
        
        if (!$patient) {
            return $this->response->setJSON(['error' => 'Patient not found']);
        }

        return $this->response->setJSON($patient);
    }

    public function testAjax()
    {
        $this->response->setContentType('application/json');
        return $this->response->setJSON([
            'success' => true,
            'message' => 'AJAX test successful',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Test DataTables endpoint
     */
    public function testDataTables()
    {
        try {
            $this->response->setContentType('application/json');
            
            // Simple test data
            $data = [
                'draw' => intval($this->request->getGet('draw') ?? 1),
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
                'data' => [
                    [
                        'patient_id' => 'TEST001',
                        'name' => 'Test Patient',
                        'email' => 'test@example.com',
                        'phone' => '123-456-7890',
                        'age' => 30,
                        'status' => 'Active',
                        'examinations' => 0,
                        'last_visit' => 'Never',
                        'created_at' => date('M j, Y'),
                        'actions' => 1
                    ]
                ]
            ];
            
            return $this->response->setJSON($data);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }


    /**
     * Test endpoint to verify DataTables endpoint is working
     */
    public function testEndpoint()
    {
        $this->response->setContentType('application/json');
        
        // Test if we can get patients from database
        try {
            $patients = $this->patientModel->limit(2)->find();
            $totalPatients = $this->patientModel->countAllResults(false);
            
            $data = [];
            foreach ($patients as $patient) {
                $age = 0;
                if (!empty($patient['date_of_birth']) && $patient['date_of_birth'] !== '0000-00-00') {
                    try {
                        $dob = new \DateTime($patient['date_of_birth']);
                        $now = new \DateTime();
                        $age = $now->diff($dob)->y;
                    } catch (\Exception $e) {
                        $age = 0;
                    }
                }
                
                $data[] = [
                    'patient_id' => $patient['patient_id'] ?? 'N/A',
                    'name' => trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')),
                    'email' => $patient['email'] ?: 'N/A',
                    'phone' => $patient['phone'] ?: 'N/A',
                    'status' => ucfirst($patient['status'] ?? 'unknown'),
                    'examinations' => 0,
                    'actions' => $patient['id'] ?? 0
                ];
            }
            
            $response = [
                'draw' => 1,
                'recordsTotal' => $totalPatients,
                'recordsFiltered' => count($data),
                'data' => $data,
                'debug' => [
                    'total_patients' => $totalPatients,
                    'returned_count' => count($data),
                    'raw_patients' => $patients
                ]
            ];
            
        } catch (\Exception $e) {
            $response = [
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ];
        }
        
        return $this->response->setJSON($response);
    }

    /**
     * Test database connection and data retrieval
     */
    public function testDatabase()
    {
        try {
            $this->response->setContentType('application/json');
            
            // Test basic query
            $patients = $this->patientModel->select('id, patient_id, first_name, last_name, email, phone, status, created_at')
                ->where('deleted_at', null)
                ->limit(5)
                ->find();
            
            $response = [
                'success' => true,
                'message' => 'Database connection successful',
                'patient_count' => count($patients),
                'patients' => $patients,
                'timestamp' => date('Y-m-d H:i:s') 
            ];
            
            return $this->response->setJSON($response);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Get patient statistics for dashboard
     */
    public function getStatistics()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $this->response->setContentType('application/json');
            
            $stats = $this->patientModel->getPatientStatsByClinic($clinicId);
            
            return $this->response->setJSON($stats);
            
        } catch (\Exception $e) {
            log_message('error', 'Patient statistics error: ' . $e->getMessage());
            return $this->response->setJSON([
                'total_patients' => 0,
                'active_patients' => 0,
                'new_patients' => 0,
                'monthly_patients' => 0,
                'error' => $e->getMessage()
            ]);
        }
    }


}
