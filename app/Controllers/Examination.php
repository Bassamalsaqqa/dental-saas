<?php

namespace App\Controllers;

use App\Models\ExaminationModel;
use App\Models\PatientModel;
use App\Models\OdontogramModel;
use App\Models\TreatmentModel;
use App\Models\TreatmentContractModel;
use App\Models\InventoryModel;
use App\Models\ClinicUserModel;
use App\Libraries\TreatmentCatalog;
use App\Services\ActivityLogger;
use App\Services\ClinicalAppointmentContextService;
use App\Services\ClinicalDocumentationQueueService;
use App\Services\ExaminationNoteGovernanceService;
use App\Services\MoneyTrailService;
use App\Services\OdontogramSnapshotService;
use App\Services\PlanGuard;
use App\Services\WhatsAppWorkflowService;
use DomainException;

class Examination extends BaseController
{
    protected $examinationModel;
    protected $patientModel;
    protected $odontogramModel;
    protected $treatmentModel;
    protected $contractModel;
    protected $activityLogger;
    protected $storageService;
    protected $db;
    protected WhatsAppWorkflowService $whatsAppWorkflowService;
    protected OdontogramSnapshotService $odontogramSnapshotService;
    protected ClinicalAppointmentContextService $appointmentContextService;
    protected ExaminationNoteGovernanceService $noteGovernanceService;

    public function __construct()
    {
        $this->examinationModel = new ExaminationModel();
        $this->patientModel = new PatientModel();
        $this->odontogramModel = new OdontogramModel();
        $this->treatmentModel = new TreatmentModel();
        $this->contractModel = new TreatmentContractModel();
        $this->activityLogger = new ActivityLogger();
        $this->storageService = new \App\Services\StorageService();
        $this->db = \Config\Database::connect();
        $this->whatsAppWorkflowService = new WhatsAppWorkflowService();
        $this->odontogramSnapshotService = new OdontogramSnapshotService();
        $this->appointmentContextService = new ClinicalAppointmentContextService($this->db);
        $this->noteGovernanceService = new ExaminationNoteGovernanceService($this->db);
    }

    public function index()
    {
        $clinicId = (int) session()->get('active_clinic_id');
        $documentationFilter = (string) $this->request->getGet('documentation');
        if (!in_array($documentationFilter, ['', 'ready_to_sign', 'overdue_draft', 'signed'], true)) {
            $documentationFilter = '';
        }
        $data = [
            'title' => 'Examination Management',
            'loadSelect2' => true,
            'documentation_filter' => $documentationFilter,
            'documentation_queue' => $clinicId > 0
                ? (new ClinicalDocumentationQueueService($this->db))->summary($clinicId)
                : ['ready_to_sign_count' => 0, 'overdue_draft_count' => 0],
        ];

        // Ensure user data is included
        $userData = $this->getUserDataForView();
        $data = array_merge($data, $userData);

        return $this->view('examination/index', $data);
    }

    public function create()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to create an examination.');
        }

        $patientId = $this->request->getGet('patient_id');
        $selectedPatient = null;
        
        if ($patientId) {
            $selectedPatient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
            if (!$selectedPatient) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
            }
        }

        try {
            $appointmentContext = $selectedPatient
                ? $this->appointmentContextService->resolve((int) $clinicId, (int) $selectedPatient['id'], $this->request->getGet('appointment_id'))
                : null;
        } catch (DomainException $e) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException($e->getMessage());
        }

        $returnTo = $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'examination', 'examinations']);
        if (!$returnTo && !empty($selectedPatient['id'])) {
            $returnTo = 'patient/' . (int) $selectedPatient['id'];
        }
        
        $data = [
            'title' => 'New Examination',
            'patients' => $selectedPatient ? [$selectedPatient] : [], // S4-02f: No bulk preload
            'selected_patient_id' => $patientId,
            'selected_patient' => $selectedPatient,
            'selected_appointment_id' => $appointmentContext['id'] ?? null,
            'validation' => \Config\Services::validation(),
            'loadSelect2' => true,
            'return_to' => $returnTo
        ];

        // Ensure user data is included
        $userData = $this->getUserDataForView();
        $data = array_merge($data, $userData);

        return $this->view('examination/create', $data);
    }

    public function store()
    {
        $clinicId = (int) session()->get('active_clinic_id');
        if ($clinicId <= 0) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic.');
        }

        $rules = [
            'patient_id' => 'required|integer',
            'examination_date' => 'required|valid_date',
            'examination_type' => 'required|in_list[initial,periodic,emergency,follow_up]',
            'chief_complaint' => 'required|min_length[10]|max_length[500]',
            'appointment_id' => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Get examination date (database field is date, not datetime)
        $examinationDate = $this->request->getPost('examination_date');

        $currentUser = $this->getCurrentUser();
        $currentUserId = $currentUser ? (int) $currentUser->id : (session()->get('user_id') ? (int) session()->get('user_id') : null);

        try {
            $appointmentContext = $this->appointmentContextService->resolve(
                $clinicId,
                (int) $this->request->getPost('patient_id'),
                $this->request->getPost('appointment_id')
            );
        } catch (DomainException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        $boolField = static function ($value): int {
            return in_array($value, ['1', 1, true, 'true', 'on', 'yes'], true) ? 1 : 0;
        };
        $textField = static function ($value): ?string {
            $value = trim((string) $value);
            return $value === '' ? null : $value;
        };

        $healthMedicalChanges = $boolField($this->request->getPost('health_update_medical_changes'));
        $healthMedicationChanges = $boolField($this->request->getPost('health_update_medication_changes'));
        $healthAllergiesReactions = $boolField($this->request->getPost('health_update_allergies_reactions'));
        $healthPregnancy = $boolField($this->request->getPost('health_update_pregnancy'));
        $healthCurrentSymptoms = $boolField($this->request->getPost('health_update_current_symptoms'));
        $healthUpdateDetails = ($healthMedicalChanges || $healthMedicationChanges || $healthAllergiesReactions || $healthPregnancy || $healthCurrentSymptoms)
            ? $textField($this->request->getPost('health_update_details'))
            : null;

        $legacyMedicalHistoryLines = [];
        if ($healthMedicalChanges) {
            $legacyMedicalHistoryLines[] = 'Medical changes since last visit';
        }
        if ($healthMedicationChanges) {
            $legacyMedicalHistoryLines[] = 'Started or stopped medications';
        }
        if ($healthAllergiesReactions) {
            $legacyMedicalHistoryLines[] = 'New allergies or reactions';
        }
        if ($healthPregnancy) {
            $legacyMedicalHistoryLines[] = 'Pregnant / possibly pregnant';
        }
        if ($healthCurrentSymptoms) {
            $legacyMedicalHistoryLines[] = 'Current pain / swelling / bleeding';
        }
        if ($healthUpdateDetails) {
            $legacyMedicalHistoryLines[] = 'Details: ' . $healthUpdateDetails;
        }
        $legacyMedicalHistory = !empty($legacyMedicalHistoryLines) ? implode("\n", $legacyMedicalHistoryLines) : null;

        $examinationData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'appointment_id' => $appointmentContext['id'] ?? null,
            'examination_date' => $examinationDate,
            'examination_type' => $this->request->getPost('examination_type'),
            'chief_complaint' => $this->request->getPost('chief_complaint'),
            'history_of_present_illness' => $this->request->getPost('history_of_present_illness'),
            'medical_history' => $legacyMedicalHistory,
            'health_update_medical_changes' => $healthMedicalChanges,
            'health_update_medication_changes' => $healthMedicationChanges,
            'health_update_allergies_reactions' => $healthAllergiesReactions,
            'health_update_pregnancy' => $healthPregnancy,
            'health_update_current_symptoms' => $healthCurrentSymptoms,
            'health_update_details' => $healthUpdateDetails,
            'dental_history' => $this->request->getPost('dental_history'),
            'clinical_findings' => $this->request->getPost('clinical_findings'),
            'diagnosis' => $this->request->getPost('diagnosis'),
            'prognosis' => $this->request->getPost('prognosis'),
            'recommendations' => $this->request->getPost('recommendations'),
            'examination_notes' => $this->request->getPost('examination_notes'),
            'status' => 'pending',
            'created_by' => $currentUserId
        ];

        // Log the data being inserted for debugging
        log_message('info', 'Examination data being inserted: ' . json_encode($examinationData));
        
        if ($examinationId = $this->examinationModel->insert($examinationData)) {
            // Get patient name for the activity log
            $patient = $this->patientModel->find($examinationData['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Log the examination creation activity
            $this->activityLogger->logExaminationActivity(
                'create',
                $examinationId,
                "New {$examinationData['examination_type']} examination created for {$patientName} - Chief Complaint: " . substr($examinationData['chief_complaint'], 0, 50) . "..."
            );
            
            if ($this->request->getPost('after_save') === 'odontogram') {
                return $this->redirectAfterWorkflow(
                    'odontogram/' . (int) $examinationData['patient_id'] . '?examination_id=' . $examinationId . '&return_to=examination/' . $examinationId,
                    ['patient', 'patients', 'examination', 'examinations'],
                    null,
                    false
                )
                    ->with('success', 'Examination created successfully');
            }

            $returnTo = $this->resolveCreateReturnTo((int) $examinationData['patient_id'], $this->request->getPost('return_to'));
            if ($returnTo) {
                return $this->redirectAfterWorkflow($returnTo, ['patient', 'patients', 'examination', 'examinations'])
                    ->with('success', 'Examination created successfully');
            }

            log_message('info', 'Examination created successfully with ID: ' . $examinationId);
            return $this->redirectAfterWorkflow('examination/' . $examinationId, ['patient', 'patients', 'examination', 'examinations'])
                ->with('success', 'Examination created successfully');
        } else {
            // Log the error for debugging
            $errors = $this->examinationModel->errors();
            log_message('error', 'Examination creation failed: ' . json_encode($errors));
            log_message('error', 'Examination data that failed: ' . json_encode($examinationData));
            return redirect()->back()->withInput()->with('error', 'Failed to create examination: ' . implode(', ', $errors));
        }
    }

    public function show($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic.');
        }

        $examination = $this->examinationModel->select('examinations.*, patients.first_name, patients.last_name, patients.gender, patients.patient_id as patient_number, users.first_name as doctor_first_name, users.last_name as doctor_last_name')
            ->join('patients', 'patients.id = examinations.patient_id')
            ->join('users', 'users.id = examinations.created_by', 'left')
            ->where('examinations.clinic_id', $clinicId)
            ->where('patients.clinic_id', $clinicId)
            ->where('examinations.id', $id)
            ->first();
        
        if (!$examination) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Examination not found');
        }

        $nextAppointment = (new \App\Models\AppointmentModel())->getNextAppointmentForPatient($clinicId, $examination['patient_id']);

        $latestContract = $this->contractModel
            ->where('clinic_id', $clinicId)
            ->where('patient_id', $examination['patient_id'])
            ->where('examination_id', $examination['id'])
            ->orderBy('created_at', 'DESC')
            ->first();
        $planGuard = new PlanGuard();
        $signatureEnabled = $planGuard->isFeatureEnabled($clinicId, 'contracts.signature.enabled', false);
        $inventoryItems = (new InventoryModel())->where('status', 'active')
            ->where('clinic_id', $clinicId)
            ->where('quantity >', 0)
            ->orderBy('item_name', 'ASC')
            ->findAll();
        $selectedTreatmentPatient = [
            'id' => (int) $examination['patient_id'],
            'patient_id' => $examination['patient_number'] ?? '',
            'first_name' => $examination['first_name'] ?? '',
            'last_name' => $examination['last_name'] ?? '',
            'gender' => $examination['gender'] ?? '',
        ];

        $moneyTrail = (new MoneyTrailService())->forExamination((int) $clinicId, (int) $examination['id']);
        $invoiceableChargeLines = array_values(array_filter(
            $moneyTrail['charge_lines'] ?? [],
            static fn(array $row): bool => !in_array(strtolower((string) ($row['status'] ?? 'planned')), ['invoiced', 'voided'], true)
        ));

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

        $data = [
            'title' => 'Examination Details - ' . $examination['examination_id'],
            'examination' => $examination,
            'next_appointment' => $nextAppointment,
            'latest_contract' => $latestContract,
            'money_trail' => $moneyTrail,
            'examination_invoiceable_charge_lines' => $invoiceableChargeLines,
            'examination_finance_base_currency' => settings()->get('currency', 'ILS'),
            'examination_finance_currency_options' => ['ILS', 'USD', 'JOD', 'EUR', 'GBP'],
            'examination_finance_liquidity_options' => $liquidityOptions,
            'signature_enabled' => $signatureEnabled,
            'odontogram' => $this->odontogramModel->where('clinic_id', $clinicId)->getEffectiveOdontogramForExamination((int) $examination['patient_id'], (int) $examination['id']),
            'odontogram_stats' => $this->odontogramModel->getOdontogramStats((int) $examination['patient_id'], (int) $examination['id']),
            'treatments' => $this->treatmentModel->where('clinic_id', $clinicId)->getTreatmentsByExamination($id),
            'condition_types' => $this->odontogramModel->getConditionTypes(),
            'tooth_positions' => $this->odontogramModel->getToothPositions(),
            'loadSelect2' => true,
            'treatment_types' => TreatmentCatalog::forSelection(),
            'inventory_items' => $inventoryItems,
            'clinic_providers' => (new ClinicUserModel())->getActiveUserDirectoryByClinic((int) $clinicId),
            'selected_treatment_patient' => $selectedTreatmentPatient,
            'selected_examination_id' => (int) $examination['id'],
            'selected_appointment_patient' => $selectedTreatmentPatient,
            'appointment_chair_options' => service('settings')->getBookingChairNames(),
            'appointment_type_prefill' => 'follow_up',
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'examination', 'examinations']),
            'clinical_note_state' => $this->noteGovernanceService->state((int) $clinicId, (int) $id),
        ];

        return $this->view('examination/show', $data);
    }

    private function resolveCreateReturnTo(int $patientId, $returnToRaw): ?string
    {
        $returnTo = $this->sanitizeInternalReturnTo((string) $returnToRaw, ['patient', 'patients', 'examination', 'examinations']);
        if (!$returnTo) {
            return null;
        }

        if (preg_match('#^(patients|examination|examinations)(\\?|$)#', $returnTo)) {
            return $returnTo;
        }

        if (preg_match('#^patient/(\\d+)$#', $returnTo, $matches)) {
            return ((int) $matches[1] === $patientId) ? $returnTo : null;
        }

        return null;
    }

    public function edit($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to edit examinations.');
        }

        // S4-02f-FIX2: Use scoped query
        $examination = $this->examinationModel->where('clinic_id', $clinicId)->find($id);
        
        if (!$examination) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Examination not found');
        }

        if ($this->noteGovernanceService->isSigned((int) $clinicId, (int) $id)) {
            return redirect()->to(base_url('examination/' . (int) $id))
                ->with('error', lang('Examination.signed_note_edit_blocked'));
        }

        $previousStatus = (string) ($examination['status'] ?? '');

        // Only load the specific patient for this examination (scoped)
        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($examination['patient_id']);

        $data = [
            'title' => 'Edit Examination - ' . ($examination['examination_id'] ?? $examination['id']),
            'examination' => $examination,
            'patients' => $patient ? [$patient] : [], // Only emit current patient
            'selected_patient_id' => $patient['id'] ?? null,
            'selected_patient' => $patient,
            'validation' => \Config\Services::validation(),
            'loadSelect2' => true,
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'examination', 'examinations'])
        ];

        return $this->view('examination/edit', $data);
    }

    public function update($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic.');
        }

        $examination = $this->examinationModel->where('clinic_id', $clinicId)->find($id);
        
        if (!$examination) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Examination not found');
        }

        if ($this->noteGovernanceService->isSigned((int) $clinicId, (int) $id)) {
            return redirect()->to(base_url('examination/' . (int) $id))
                ->with('error', lang('Examination.signed_note_edit_blocked'));
        }

        $rules = [
            'patient_id' => 'required|integer',
            'examination_date' => 'required|valid_date',
            'examination_type' => 'required|in_list[initial,periodic,emergency,follow_up]',
            'chief_complaint' => 'required|min_length[10]|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $boolField = static function ($value): int {
            return in_array($value, ['1', 1, true, 'true', 'on', 'yes'], true) ? 1 : 0;
        };
        $textField = static function ($value): ?string {
            $value = trim((string) $value);
            return $value === '' ? null : $value;
        };

        $healthMedicalChanges = $boolField($this->request->getPost('health_update_medical_changes'));
        $healthMedicationChanges = $boolField($this->request->getPost('health_update_medication_changes'));
        $healthAllergiesReactions = $boolField($this->request->getPost('health_update_allergies_reactions'));
        $healthPregnancy = $boolField($this->request->getPost('health_update_pregnancy'));
        $healthCurrentSymptoms = $boolField($this->request->getPost('health_update_current_symptoms'));
        $healthUpdateDetails = ($healthMedicalChanges || $healthMedicationChanges || $healthAllergiesReactions || $healthPregnancy || $healthCurrentSymptoms)
            ? $textField($this->request->getPost('health_update_details'))
            : null;

        $legacyMedicalHistoryLines = [];
        if ($healthMedicalChanges) $legacyMedicalHistoryLines[] = 'Medical changes since last visit';
        if ($healthMedicationChanges) $legacyMedicalHistoryLines[] = 'Started or stopped medications';
        if ($healthAllergiesReactions) $legacyMedicalHistoryLines[] = 'New allergies or reactions';
        if ($healthPregnancy) $legacyMedicalHistoryLines[] = 'Pregnant / possibly pregnant';
        if ($healthCurrentSymptoms) $legacyMedicalHistoryLines[] = 'Current pain / swelling / bleeding';
        if ($healthUpdateDetails) $legacyMedicalHistoryLines[] = 'Details: ' . $healthUpdateDetails;
        $legacyMedicalHistory = !empty($legacyMedicalHistoryLines) ? implode("\n", $legacyMedicalHistoryLines) : null;

        $examinationData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'examination_date' => $this->request->getPost('examination_date'),
            'examination_type' => $this->request->getPost('examination_type'),
            'chief_complaint' => $this->request->getPost('chief_complaint'),
            'history_of_present_illness' => $this->request->getPost('history_of_present_illness'),
            'medical_history' => $legacyMedicalHistory,
            'health_update_medical_changes' => $healthMedicalChanges,
            'health_update_medication_changes' => $healthMedicationChanges,
            'health_update_allergies_reactions' => $healthAllergiesReactions,
            'health_update_pregnancy' => $healthPregnancy,
            'health_update_current_symptoms' => $healthCurrentSymptoms,
            'health_update_details' => $healthUpdateDetails,
            'dental_history' => $this->request->getPost('dental_history'),
            'clinical_findings' => $this->request->getPost('clinical_findings'),
            'diagnosis' => $this->request->getPost('diagnosis'),
            'prognosis' => $this->request->getPost('prognosis'),
            'recommendations' => $this->request->getPost('recommendations'),
            'examination_notes' => $this->request->getPost('examination_notes'),
            'status' => $this->request->getPost('status')
        ];

        if ($this->examinationModel->update($id, $examinationData)) {
            // Get patient name for the activity log (scoped)
            $patient = $this->patientModel->where('clinic_id', $clinicId)->find($examinationData['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Log the examination update activity
            $this->activityLogger->logExaminationActivity(
                'update',
                $id,
                "Examination updated for {$patientName} - {$examinationData['examination_type']} - Status: {$examinationData['status']}"
            );

            if ($previousStatus !== 'completed' && (string) ($examinationData['status'] ?? '') === 'completed') {
                $actorUserId = session()->get('user_id') ? (int) session()->get('user_id') : null;
                $this->odontogramSnapshotService->capturePatientBaselineSnapshot(
                    (int) $clinicId,
                    (int) $examination['patient_id'],
                    'examination',
                    (int) $id,
                    $actorUserId,
                    'Examination ' . ($examination['examination_id'] ?? $id)
                );

                try {
                    $this->whatsAppWorkflowService->queueFollowUpForCompletedExamination((int) $clinicId, (int) $id, $actorUserId);
                } catch (\Throwable $e) {
                    log_message('warning', 'WhatsApp follow-up enqueue failed for examination_id={examination_id}: {message}', [
                        'examination_id' => $id,
                        'message' => substr($e->getMessage(), 0, 255),
                    ]);
                }
            }

            $returnTo = $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['patient', 'patients', 'examination', 'examinations']);
            if ($this->request->getPost('after_save') === 'odontogram') {
                return $this->redirectAfterWorkflow(
                    'odontogram/' . (int) $examinationData['patient_id'] . '?examination_id=' . $id . '&return_to=examination/' . $id,
                    ['patient', 'patients', 'examination', 'examinations'],
                    null,
                    false
                )
                    ->with('success', 'Examination updated successfully');
            }
            if ($returnTo) {
                return $this->redirectAfterWorkflow($returnTo, ['patient', 'patients', 'examination', 'examinations'])
                    ->with('success', 'Examination updated successfully');
            }
            
            return $this->redirectAfterWorkflow('examination/' . $id, ['patient', 'patients', 'examination', 'examinations'])
                ->with('success', 'Examination updated successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update examination');
        }
    }

    public function updateToothCondition()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $patientId = $this->request->getPost('patient_id');
        $examinationId = $this->request->getPost('examination_id');
        $toothNumber = $this->request->getPost('tooth_number');
        $conditionType = $this->request->getPost('condition_type');
        $conditionDescription = $this->request->getPost('condition_description');
        $treatmentNotes = $this->request->getPost('treatment_notes');

        // Verify patient ownership
        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
        if (!$patient) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Invalid patient context']);
        }

        $currentUser = $this->getCurrentUser();
        $currentUserId = $currentUser ? (int) $currentUser->id : (session()->get('user_id') ? (int) session()->get('user_id') : null);

        $conditionData = [
            'clinic_id' => $clinicId,
            'examination_id' => $examinationId,
            'condition_type' => $conditionType,
            'condition_description' => $conditionDescription,
            'treatment_notes' => $treatmentNotes,
            'treatment_date' => date('Y-m-d'),
            'treatment_status' => 'completed',
            'created_by' => $currentUserId
        ];

        if ($this->odontogramModel->updateToothCondition($patientId, $toothNumber, $conditionData)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Tooth condition updated successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to update tooth condition']);
        }
    }

    public function getExaminationData($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        // Scoped check
        $examination = $this->examinationModel->where('clinic_id', $clinicId)->find($id);
        
        if (!$examination) {
            return $this->response->setJSON(['error' => 'Examination not found']);
        }

        return $this->response->setJSON($examination);
    }

    public function getExaminationsByDate()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $date = $this->request->getGet('date');
        
        if (empty($date)) {
            $date = date('Y-m-d');
        }

        // Scoped query
        $examinations = $this->examinationModel->getExaminationsByDateRange($clinicId, $date, $date);
        
        return $this->response->setJSON($examinations);
    }

    public function complete($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }
            return redirect()->to('/clinic/select');
        }

        $examination = $this->examinationModel->where('clinic_id', $clinicId)->find($id);
        
        if (!$examination) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Examination not found']);
            }
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Examination not found');
        }

        if ($this->noteGovernanceService->isSigned((int) $clinicId, (int) $id)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => lang('Examination.signed_note_edit_blocked'),
                ]);
            }

            return redirect()->to(base_url('examination/' . (int) $id))
                ->with('error', lang('Examination.signed_note_edit_blocked'));
        }

        $updateData = [ 
            'status' => 'completed',
            'examination_notes' => $this->request->getPost('examination_notes')
        ];

        if ($this->examinationModel->update($id, $updateData)) {
            $actorUserId = session()->get('user_id') ? (int) session()->get('user_id') : null;
            $this->odontogramSnapshotService->capturePatientBaselineSnapshot(
                (int) $clinicId,
                (int) $examination['patient_id'],
                'examination',
                (int) $id,
                $actorUserId,
                'Examination ' . ($examination['examination_id'] ?? $id)
            );
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Examination completed successfully']);
            }

            $returnTo = $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['patient', 'patients', 'examination', 'examinations']);
            if ($returnTo) {
                return redirect()->to(base_url($returnTo))->with('success', 'Examination completed successfully');
            }

            return redirect()->to(base_url('examination/' . $id))->with('success', 'Examination completed successfully');
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to complete examination']);
            }

            return redirect()->back()->with('error', 'Failed to complete examination');
        }
    }

    public function signClinicalNote($id)
    {
        $clinicId = (int) session()->get('active_clinic_id');
        $actor = $this->getCurrentUser();
        $actorId = $actor ? (int) $actor->id : 0;
        if ($clinicId <= 0 || $actorId <= 0) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Clinical note context not found.');
        }
        if ($this->request->getPost('attest') !== '1') {
            return redirect()->back()->with('error', lang('Examination.sign_attestation_required'));
        }

        try {
            $actorName = trim((string) ($actor->first_name ?? '') . ' ' . (string) ($actor->last_name ?? ''));
            $this->noteGovernanceService->sign($clinicId, (int) $id, $actorId, $actorName ?: ('User #' . $actorId));
            $this->activityLogger->logExaminationActivity('sign_note', (int) $id, 'Clinical note signed and locked.');
            return redirect()->to(base_url('examination/' . (int) $id))->with('success', lang('Examination.sign_success'));
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function addClinicalNoteAddendum($id)
    {
        $clinicId = (int) session()->get('active_clinic_id');
        $actor = $this->getCurrentUser();
        $actorId = $actor ? (int) $actor->id : 0;
        if ($clinicId <= 0 || $actorId <= 0) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Clinical note context not found.');
        }

        try {
            $actorName = trim((string) ($actor->first_name ?? '') . ' ' . (string) ($actor->last_name ?? ''));
            $this->noteGovernanceService->addAddendum(
                $clinicId,
                (int) $id,
                $actorId,
                $actorName ?: ('User #' . $actorId),
                (string) $this->request->getPost('reason'),
                (string) $this->request->getPost('body')
            );
            $this->activityLogger->logExaminationActivity('add_note_addendum', (int) $id, 'Addendum appended to signed clinical note.');
            return redirect()->to(base_url('examination/' . (int) $id))->with('success', lang('Examination.addendum_success'));
        } catch (DomainException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function getExaminationsData()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $request = $this->request;
            
            // DataTables parameters with defaults
            $draw = intval($request->getPost('draw') ?? 1);
            $start = intval($request->getPost('start') ?? 0);
            $length = intval($request->getPost('length') ?? 10);
            $documentationFilter = (string) ($request->getPost('documentation_filter') ?? '');
            if (!in_array($documentationFilter, ['', 'ready_to_sign', 'overdue_draft', 'signed'], true)) {
                $documentationFilter = '';
            }
            
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
            
            // Column mapping for ordering
            $columns = [
                'examinations.id',
                'patients.first_name',
                'examinations.examination_date',
                'examinations.examination_type',
                'examinations.status'
            ];
            
            $orderColumnName = $columns[$orderColumn] ?? 'examinations.id';
            
            // Get total records count (scoped)
            $totalRecords = $this->examinationModel->countExaminationsByClinic($clinicId);
            
            // If no examinations exist, return empty result
            if ($totalRecords == 0) {
                return $this->response->setJSON([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]);
            }
            
            // Get filtered count
            $filteredRecords = $this->examinationModel->countExaminationsByClinic($clinicId, $searchValue, $documentationFilter);
            
            // Get data (scoped)
            $examinations = $this->examinationModel->getExaminationsByClinic($clinicId, $length, $start, $searchValue, $orderColumnName, $orderDir, $documentationFilter);
            
            // Format data
            $data = [];
            foreach ($examinations as $examination) {
                $data[] = [
                    'examination_id' => $examination['examination_id'] ?? '',
                    'patient_name' => trim(($examination['first_name'] ?? '') . ' ' . ($examination['last_name'] ?? '')),
                    'patient_number' => $examination['patient_number'] ?? '',
                    'gender' => $examination['gender'] ?? 'other',
                    'examination_date' => $examination['examination_date'] ?? '',
                    'examination_date_formatted' => formatDate($examination['examination_date'] ?? ''),
                    'examination_time_formatted' => '',
                    'examination_type' => $examination['examination_type'] ?? '',
                    'status' => $examination['status'] ?? '',
                    'chief_complaint' => $examination['chief_complaint'] ?? '',
                    'id' => intval($examination['id'] ?? 0),
                    'patient_id' => intval($examination['patient_id'] ?? 0),
                    'documentation_state' => !empty($examination['note_signature_id'])
                        ? 'signed'
                        : (($examination['status'] ?? '') === 'completed'
                            ? 'ready_to_sign'
                            : ((in_array(($examination['status'] ?? ''), ['pending', 'in_progress'], true) && ($examination['examination_date'] ?? '') < date('Y-m-d')) ? 'overdue_draft' : 'draft')),
                ];
            }
            
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'DataTables error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading data: ' . $e->getMessage()
            ]);
        }
    }

    public function getExaminationStats()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $stats = $this->examinationModel->getExaminationStatsByClinic($clinicId);
        return $this->response->setJSON($stats);
    }

    public function testDataTables()
    {
        // Simple test to verify the endpoint works
        return $this->response->setJSON([
            'draw' => 1,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'message' => 'DataTables endpoint is working'
        ]);
    }

    public function debugDataTables()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $debugData = $this->examinationModel->getDebugDataByClinic($clinicId);
            
            return $this->response->setJSON([
                'success' => true,
                'debug' => $debugData
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function createSampleData()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            // Check if we have patients first
            $patientCount = $this->patientModel->where('clinic_id', $clinicId)->countAllResults();
            if ($patientCount == 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No patients found. Please create patients first.'
                ]);
            }
            
            // Get first few patients
            $patients = $this->patientModel->where('clinic_id', $clinicId)->limit(3)->findAll();
            
            // Create sample examinations
            $sampleExaminations = [];
            $currentUser = $this->getCurrentUser();
            $currentUserId = $currentUser ? (int) $currentUser->id : (session()->get('user_id') ? (int) session()->get('user_id') : null);

            foreach ($patients as $index => $patient) {
                $sampleExaminations[] = [
                    'patient_id' => $patient['id'],
                    'examination_id' => 'EXM' . date('Ymd') . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'examination_date' => date('Y-m-d H:i:s', strtotime('-' . $index . ' days')),
                    'examination_type' => ['initial', 'periodic', 'emergency'][$index % 3],
                    'chief_complaint' => 'Sample complaint ' . ($index + 1),
                    'status' => ['pending', 'completed', 'in_progress'][$index % 3],
                    'created_by' => $currentUserId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            
            $this->examinationModel->insertBatchByClinic($clinicId, $sampleExaminations);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Sample examination data created successfully',
                'count' => count($sampleExaminations)
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error creating sample data: ' . $e->getMessage()
            ]);
        }
    }

    public function print($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic.');
        }

        $examination = $this->examinationModel->select('examinations.*, patients.first_name, patients.last_name, patients.patient_id as patient_number, users.first_name as doctor_first_name, users.last_name as doctor_last_name')
            ->join('patients', 'patients.id = examinations.patient_id')
            ->join('users', 'users.id = examinations.created_by', 'left')
            ->where('examinations.clinic_id', $clinicId)
            ->where('patients.clinic_id', $clinicId)
            ->where('examinations.id', $id)
            ->first();
        
        if (!$examination) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Examination not found');
        }

        $latestContract = $this->contractModel
            ->where('clinic_id', $clinicId)
            ->where('patient_id', $examination['patient_id'])
            ->where('examination_id', $examination['id'])
            ->orderBy('created_at', 'DESC')
            ->first();

        $data = [
            'title' => 'Examination Report - ' . ($examination['examination_id'] ?? $examination['id']),
            'examination' => $examination,
            'latest_contract' => $latestContract,
            'odontogram' => $this->odontogramModel
                ->where('clinic_id', $clinicId)
                ->getEffectiveOdontogramForExamination((int) $examination['patient_id'], (int) $examination['id']),
            'treatments' => $this->treatmentModel->where('clinic_id', $clinicId)->getTreatmentsByExamination($id),
            'clinic' => settings()->getClinicInfo(),
            'clinical_note_state' => $this->noteGovernanceService->state((int) $clinicId, (int) $id),
        ];

        $html = view('examination/print', $data);
        
        // Persist artifact
        $fileName = 'exam_' . ($examination['examination_id'] ?? $examination['id']) . '.html';
        $this->storageService->storeExport(
            $html, 
            $fileName, 
            'text/html', 
            $clinicId, 
            'examination', 
            $id, 
            'exam_print'
        );

        return $this->response
            ->setHeader('Content-Type', 'text/html')
            ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->setBody($html);
    }

    public function duplicate($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic.');
        }

        $examination = $this->examinationModel->where('clinic_id', $clinicId)->find($id);
        
        if (!$examination) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Examination not found');
        }

        // Remove the original ID and examination_id to create a new record
        unset($examination['id']);
        unset($examination['examination_id']);
        unset($examination['created_at']);
        unset($examination['updated_at']);
        unset($examination['deleted_at']);
        
        // Update the examination date to today
        $examination['examination_date'] = date('Y-m-d');
        $examination['status'] = 'pending';
        
        // Insert the duplicated examination
        if ($newExaminationId = $this->examinationModel->insert($examination)) {
            return redirect()->to(base_url('examination/' . $newExaminationId))->with('success', 'Examination duplicated successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to duplicate examination');
        }
    }

    public function delete($id)
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $examination = $this->examinationModel->where('clinic_id', $clinicId)->find($id);
            if (!$examination) {
                return $this->response->setJSON(['success' => false, 'message' => 'Examination not found']);
            }

            if ($this->noteGovernanceService->isSigned((int) $clinicId, (int) $id)) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => lang('Examination.signed_note_delete_blocked'),
                ]);
            }

            $guard = new \App\Services\FinancialParentDeletionGuard($this->examinationModel->db);
            $guard->deleteExamination((int) $clinicId, (int) $id, function () use ($id) {
                $deleted = $this->examinationModel->delete($id);
                if (!$deleted || $this->examinationModel->db->affectedRows() !== 1) {
                    $errors = $this->examinationModel->errors();
                    $msg = !empty($errors) ? ('Failed to delete examination: ' . implode(', ', $errors)) : 'Failed to delete examination';
                    throw new \RuntimeException($msg);
                }
                return true;
            });

            return $this->response->setJSON(['success' => true, 'message' => 'Examination deleted successfully']);
        } catch (\App\Services\FinancialParentDeletionException $e) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'Cannot delete examination with linked financial records',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Exception in delete examination - ID: ' . $id . ', Error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function calendar()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to view calendar.');
        }

        $data = [
            'title' => 'Examination Calendar'
        ];

        return $this->view('examination/calendar', $data);
    }

    public function getCalendarEvents()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $this->response->setContentType('application/json');
            
            $start = $this->request->getGet('start');
            $end = $this->request->getGet('end');

            if (empty($start) || empty($end)) {
                return $this->response->setJSON([
                    'error' => 'Start and end dates are required',
                    'events' => []
                ]);
            }

            log_message('debug', 'Examination calendar events request - Start: ' . $start . ', End: ' . $end);

            // Get examinations within the date range (scoped)
            $examinations = $this->examinationModel->getExaminationsByDateRange($clinicId, $start, $end);
            
            log_message('debug', 'Found ' . count($examinations) . ' examinations for calendar');
            
            $events = [];
            foreach ($examinations as $examination) {
                $patientName = $examination['patient_name'] ?? trim(($examination['first_name'] ?? '') . ' ' . ($examination['last_name'] ?? ''));
                if ($patientName === '') {
                    $patientName = 'Unknown Patient';
                }

                $events[] = [
                    'id' => $examination['id'],
                    'title' => $patientName . ' - Examination',
                    'start' => $examination['examination_date'],
                    'end' => $examination['examination_date'],
                    'allDay' => true,
                    'status' => $examination['status'] ?? 'scheduled',
                    'patient_id' => $examination['patient_id'],
                    'examination_type' => $examination['examination_type'] ?? 'General',
                    'duration' => 30
                ];
            }

            log_message('debug', 'Returning ' . count($events) . ' examination calendar events');
            
            return $this->response->setJSON($events);
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting examination calendar events: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'An error occurred while fetching calendar events',
                'events' => []
            ]);
        }
    }
}
