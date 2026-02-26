<?php

namespace App\Controllers;

use App\Models\TreatmentModel;
use App\Models\PatientModel;
use App\Models\InventoryModel;
use App\Models\InventoryUsageModel;
use App\Libraries\TreatmentCatalog;
use App\Services\ActivityLogger;
use App\Services\FinanceLedgerService;
use App\Services\StorageService;

class Treatment extends BaseController
{
    protected $treatmentModel;
    protected $patientModel;
    protected $inventoryModel;
    protected $activityLogger;
    protected $db;
    protected $storageService;

    public function __construct()
    {
        $this->treatmentModel = new TreatmentModel();
        $this->patientModel = new PatientModel();
        $this->inventoryModel = new InventoryModel();
        $this->activityLogger = new ActivityLogger();
        $this->db = \Config\Database::connect();
        $this->storageService = new StorageService();
    }

    public function index()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return redirect()->to('/clinic/select');
            }

            // Get filter parameters from URL
            $search = $this->request->getGet('search') ?? '';
            $statusFilter = $this->request->getGet('status') ?? '';
            $typeFilter = $this->request->getGet('type') ?? '';
            
            // Get treatments with patient information (scoped)
            $query = $this->treatmentModel
                ->select('treatments.*, patients.first_name, patients.last_name, patients.phone')
                ->join('patients', 'patients.id = treatments.patient_id', 'left')
                ->where('treatments.clinic_id', $clinicId);
            
            // Apply filters if provided
            if (!empty($search)) {
                $query->groupStart()
                    ->like('patients.first_name', $search)
                    ->orLike('patients.last_name', $search)
                    ->orLike('patients.phone', $search)
                    ->orLike('treatments.treatment_type', $search)
                    ->orLike('treatments.status', $search)
                    ->orLike('treatments.treatment_description', $search)
                    ->groupEnd();
            }
            
            if (!empty($statusFilter)) {
                $query->where('treatments.status', $statusFilter);
            }
            
            if (!empty($typeFilter)) {
                $query->where('treatments.treatment_type', $typeFilter);
            }
            
            $treatments = $query->orderBy('treatments.created_at', 'DESC')->findAll();

            // Debug logging
            log_message('debug', 'Treatments loaded: ' . count($treatments ?? []));

            $data = [
                'title' => 'Treatment Management',
                'treatments' => $treatments ?? [],
                'total_treatments' => $this->treatmentModel->where('clinic_id', $clinicId)->countAllResults(),
                'active_treatments' => $this->treatmentModel->where('clinic_id', $clinicId)->where('status', 'active')->countAllResults(),
                'completed_treatments' => $this->treatmentModel->where('clinic_id', $clinicId)->where('status', 'completed')->countAllResults(),
                'search_term' => $search,
                'selected_status' => $statusFilter,
                'selected_type' => $typeFilter,
            ];

            return $this->view('treatment/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Treatment index error: ' . $e->getMessage());
            
            $data = [
                'title' => 'Treatment Management',
                'treatments' => [],
                'total_treatments' => 0,
                'active_treatments' => 0,
                'completed_treatments' => 0,
                'error' => 'Unable to load treatment data. Please check the database connection.'
            ];

            return $this->view('treatment/index', $data);
        }
    }

    public function getTreatmentsData()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $request = $this->request;
            
            // DataTables parameters with defaults
            $draw = intval($request->getVar('draw') ?? 1);
            $start = intval($request->getVar('start') ?? 0);
            $length = intval($request->getVar('length') ?? 10);
            
            // Handle search parameter
            $searchParam = $request->getVar('search');
            $searchValue = '';
            if (is_array($searchParam) && isset($searchParam['value'])) {
                $searchValue = trim($searchParam['value']);
            }
            
            // Handle custom filters
            $statusFilter = $request->getVar('status_filter') ?? $request->getVar('status') ?? '';
            $typeFilter = $request->getVar('type_filter') ?? $request->getVar('type') ?? '';
            
            // Handle order parameter
            $orderParam = $request->getVar('order');
            $orderColumn = 0;
            $orderDir = 'desc';
            if (is_array($orderParam) && isset($orderParam[0])) {
                $orderColumn = intval($orderParam[0]['column'] ?? 0);
                $orderDir = $orderParam[0]['dir'] ?? 'desc';
            }
            
            // Column mapping for ordering
            $columns = [
                'patients.first_name',
                'treatments.treatment_type',
                'treatments.cost',
                'treatments.status',
                'treatments.start_date'
            ];
            
            $orderColumnName = $columns[$orderColumn] ?? 'treatments.start_date';
            
            // Get total records count (scoped)
            $totalRecords = $this->treatmentModel->countTreatmentsByClinic($clinicId, null, $statusFilter, $typeFilter);
            
            // If no treatments exist, return empty result
            if ($totalRecords == 0) {
                return $this->response->setJSON([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]);
            }
            
            // Get filtered count
            $filteredRecords = $this->treatmentModel->countTreatmentsByClinic($clinicId, $searchValue, $statusFilter, $typeFilter);
            
            // Get data with ordering and pagination
            $treatments = $this->treatmentModel->getTreatmentsByClinic($clinicId, $length, $start, $searchValue, $statusFilter, $typeFilter, $orderColumnName, $orderDir);
            
            // Format data for DataTables
            $data = [];
            foreach ($treatments as $treatment) {
                $data[] = [
                    'id' => intval($treatment['id'] ?? 0),
                    'patient_name' => trim(($treatment['first_name'] ?? '') . ' ' . ($treatment['last_name'] ?? '')),
                    'phone' => $treatment['phone'] ?? '',
                    'treatment_name' => $this->getTreatmentName($treatment['treatment_type'] ?? ''),
                    'treatment_type' => $treatment['treatment_type'] ?? '',
                    'tooth_number' => $treatment['tooth_number'] ?? '',
                    'description' => $treatment['treatment_description'] ?? '',
                    'cost' => $treatment['cost'] ?? '',
                    'status' => $treatment['status'] ?? '',
                    'start_date' => $treatment['start_date'] ?? '',
                    'start_date_formatted' => formatDate($treatment['start_date'] ?? ''),
                    'end_date' => $treatment['end_date'] ?? '',
                    'end_date_formatted' => formatDate($treatment['end_date'] ?? ''),
                    'created_at' => $treatment['created_at'] ?? '',
                    'created_at_formatted' => formatDateTime($treatment['created_at'] ?? ''),
                    'patient_id' => intval($treatment['patient_id'] ?? 0)
                ];
            }
            
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Treatment DataTables error: ' . $e->getMessage());
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
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to create a treatment.');
        }

        $examinationId = $this->request->getGet('examination_id');
        $examinationId = is_numeric($examinationId) ? (int)$examinationId : null;
        $selectedPatient = null;
        if ($examinationId) {
            $exam = (new \App\Models\ExaminationModel())->where('clinic_id', $clinicId)->find($examinationId);
            if ($exam) {
                $selectedPatient = $this->patientModel->where('clinic_id', $clinicId)->find($exam['patient_id']);
            } else {
                return redirect()->to('/treatment')->with('error', 'Examination not found.');
            }
        } else {
            $patientId = $this->request->getGet('patient_id');
            $patientId = is_numeric($patientId) ? (int)$patientId : null;
            if ($patientId) {
                $selectedPatient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
                if (!$selectedPatient) {
                    throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
                }
            }
        }

        $returnTo = $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'examination', 'treatment', 'treatments']);
        if (!$returnTo) {
            if ($examinationId) {
                $returnTo = 'examination/' . $examinationId;
            } elseif (!empty($selectedPatient['id'])) {
                $returnTo = 'patient/' . (int) $selectedPatient['id'];
            }
        }

        $inventoryItems = $this->inventoryModel->where('status', 'active')
            ->where('clinic_id', $clinicId)
            ->where('quantity >', 0)
            ->orderBy('item_name', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Create New Treatment',
            'patients' => $selectedPatient ? [$selectedPatient] : [], // S4-02f: No bulk preload
            'treatment_types' => $this->getTreatmentTypes(),
            'validation' => null, // Initialize validation as null
            'loadSelect2' => true,
            'selected_examination_id' => $examinationId,
            'inventory_items' => $inventoryItems,
            'return_to' => $returnTo
        ];

        return $this->view('treatment/create', $data);
    }

    public function store()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic.');
        }

        $rules = [
            'patient_id' => 'required|integer',
            'treatment_type' => TreatmentCatalog::validationRule(),
            'tooth_number' => 'permit_empty|string|max_length[10]',
            'description' => 'required|min_length[10]',
            'cost' => 'required|decimal|greater_than[0]',
            'start_date' => 'required|valid_date',
            'estimated_duration' => 'required|integer|greater_than[0]',
            'examination_id' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            $examId = $this->request->getPost('examination_id');
            $examId = is_numeric($examId) ? (int)$examId : null;
            $selectedPatient = null;
            if ($examId) {
                $exam = (new \App\Models\ExaminationModel())->where('clinic_id', $clinicId)->find($examId);
                if ($exam) {
                    $selectedPatient = $this->patientModel->where('clinic_id', $clinicId)->find($exam['patient_id']);
                }
            } else {
                $patientId = $this->request->getPost('patient_id');
                $patientId = is_numeric($patientId) ? (int)$patientId : null;
                if ($patientId) {
                    $selectedPatient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
                }
            }
            $inventoryItems = $this->inventoryModel->where('status', 'active')
                ->where('clinic_id', $clinicId)
                ->where('quantity >', 0)
                ->orderBy('item_name', 'ASC')
                ->findAll();

            $data = [
                'title' => 'Create New Treatment',
                'patients' => $selectedPatient ? [$selectedPatient] : [], 
                'treatment_types' => $this->getTreatmentTypes(),
                'validation' => $this->validator,
                'loadSelect2' => true,
                'selected_examination_id' => $examId,
                'inventory_items' => $inventoryItems,
                'return_to' => $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['patient', 'patients', 'examination', 'treatment', 'treatments'])
            ];
            return $this->view('treatment/create', $data);
        }

        $examinationId = $this->request->getPost('examination_id');
        $examinationId = is_numeric($examinationId) ? (int)$examinationId : null;
        if ($examinationId) {
            $exam = (new \App\Models\ExaminationModel())->where('clinic_id', $clinicId)->find($examinationId);
            if (!$exam || (int)$exam['patient_id'] !== (int)$this->request->getPost('patient_id')) {
                return redirect()->back()->withInput()->with('error', 'Invalid examination selection.');
            }
        }

        $currentUser = $this->getCurrentUser();
        $currentUserId = $currentUser ? (int) $currentUser->id : (session()->get('user_id') ? (int) session()->get('user_id') : null);

        $treatmentData = [
            'clinic_id' => $clinicId,
            'patient_id' => $this->request->getPost('patient_id'),
            'examination_id' => $examinationId,
            'treatment_name' => $this->getTreatmentName($this->request->getPost('treatment_type')),
            'treatment_type' => $this->request->getPost('treatment_type'),
            'tooth_number' => $this->request->getPost('tooth_number') ?: null,
            'treatment_description' => $this->request->getPost('description'),
            'treatment_notes' => $this->request->getPost('treatment_notes') ?: null,
            'cost' => $this->request->getPost('cost'),
            'start_date' => $this->request->getPost('start_date'),
            'estimated_duration' => $this->request->getPost('estimated_duration'),
            'status' => 'active',
            'created_by' => $currentUserId,
        ];

        if ($this->treatmentModel->insert($treatmentData)) {
            $treatmentId = $this->treatmentModel->getInsertID();
            
            // Get patient name for the activity log (scoped)
            $patient = $this->patientModel->where('clinic_id', $clinicId)->find($treatmentData['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Log the treatment creation activity
            $this->activityLogger->logTreatmentActivity(
                'create',
                $treatmentId,
                "New {$treatmentData['treatment_type']} treatment created for {$patientName} - Cost: " . settings()->getCurrencySymbol() . number_format($treatmentData['cost'], 2)
            );
            
            $usageResult = $this->recordInventoryUsage($clinicId, $treatmentId);
            if ($usageResult && !$usageResult['success']) {
                return redirect()
                    ->to(base_url('treatment/' . $treatmentId . '/edit'))
                    ->with('error', $usageResult['message']);
            }

            $returnTo = $this->resolveCreateReturnTo(
                (int) $treatmentData['patient_id'],
                $examinationId,
                $this->request->getPost('return_to')
            );
            if ($returnTo) {
                return redirect()->to(base_url($returnTo))->with('success', 'Treatment has been created successfully!');
            }

            if ($examinationId) {
                return redirect()->to('/examination/' . $examinationId)->with('success', 'Treatment has been created successfully!');
            }
            return redirect()->to('/treatment')->with('success', 'Treatment has been created successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to create treatment. Please check your input and try again.');
        }
    }

    public function show($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic.');
        }

        $treatment = $this->treatmentModel->getTreatmentWithPatientInfoByClinic($clinicId, $id);
        
        if (!$treatment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Treatment not found');
        }

        $usageModel = new InventoryUsageModel();
        $usageRecords = $usageModel->where('clinic_id', $clinicId)
            ->where('treatment_id', $id)
            ->orderBy('usage_date', 'DESC')
            ->findAll();
        $totalUsageCost = 0.0;
        foreach ($usageRecords as $usage) {
            $totalUsageCost += (float) ($usage['total_cost'] ?? 0);
        }

        $data = [
            'title' => 'Treatment Details',
            'treatment' => $treatment,
            'usage_records' => $usageRecords,
            'usage_total_cost' => $totalUsageCost,
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'examination', 'treatment', 'treatments']),
        ];

        return $this->view('treatment/show', $data);
    }

    private function resolveCreateReturnTo(int $patientId, ?int $examinationId, $returnToRaw): ?string
    {
        $returnTo = $this->sanitizeInternalReturnTo((string) $returnToRaw, ['patient', 'patients', 'examination', 'treatment', 'treatments']);
        if (!$returnTo) {
            return null;
        }

        if (preg_match('#^(patients|treatment|treatments)(\\?|$)#', $returnTo)) {
            return $returnTo;
        }

        if (preg_match('#^patient/(\\d+)$#', $returnTo, $matches)) {
            return ((int) $matches[1] === $patientId) ? $returnTo : null;
        }

        if (preg_match('#^examination/(\\d+)$#', $returnTo, $matches)) {
            if (!$examinationId) {
                return null;
            }
            return ((int) $matches[1] === $examinationId) ? $returnTo : null;
        }

        return null;
    }

    public function edit($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to edit treatments.');
        }

        $treatment = $this->treatmentModel->where('clinic_id', $clinicId)->find($id);
        
        if (!$treatment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Treatment not found');
        }

        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($treatment['patient_id']);

        $inventoryItems = $this->inventoryModel->where('status', 'active')
            ->where('clinic_id', $clinicId)
            ->where('quantity >', 0)
            ->orderBy('item_name', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Edit Treatment',
            'treatment' => $treatment,
            'patients' => $patient ? [$patient] : [], // Only emit current patient
            'treatment_types' => $this->getTreatmentTypes($treatment['treatment_type'] ?? null),
            'validation' => null,
            'loadSelect2' => true,
            'inventory_items' => $inventoryItems,
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['patient', 'patients', 'examination', 'treatment', 'treatments'])
        ];

        return $this->view('treatment/edit', $data);
    }

    public function update($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $treatment = $this->treatmentModel->where('clinic_id', $clinicId)->find($id);
        
        if (!$treatment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Treatment not found');
        }

        $rules = [
            'patient_id' => 'required|integer',
            'treatment_type' => TreatmentCatalog::validationRule(),
            'tooth_number' => 'permit_empty|string|max_length[10]',
            'description' => 'required|min_length[10]',
            'cost' => 'required|decimal|greater_than[0]',
            'start_date' => 'required|valid_date',
            'estimated_duration' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            $inventoryItems = $this->inventoryModel->where('status', 'active')
                ->where('clinic_id', $clinicId)
                ->where('quantity >', 0)
                ->orderBy('item_name', 'ASC')
                ->findAll();

            $data = [
                'title' => 'Edit Treatment',
                'treatment' => $treatment,
                'patients' => $this->patientModel->where('clinic_id', $clinicId)->findAll(),
                'treatment_types' => $this->getTreatmentTypes($treatment['treatment_type'] ?? null),
                'validation' => $this->validator,
                'inventory_items' => $inventoryItems,
                'return_to' => $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['patient', 'patients', 'examination', 'treatment', 'treatments'])
            ];
            return $this->view('treatment/edit', $data);
        }

        $treatmentData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'treatment_name' => $this->getTreatmentName($this->request->getPost('treatment_type')),
            'treatment_type' => $this->request->getPost('treatment_type'),
            'tooth_number' => $this->request->getPost('tooth_number') ?: null,
            'treatment_description' => $this->request->getPost('description'),
            'treatment_notes' => $this->request->getPost('treatment_notes') ?: null,
            'cost' => $this->request->getPost('cost'),
            'start_date' => $this->request->getPost('start_date'),
            'estimated_duration' => $this->request->getPost('estimated_duration'),
            // Don't set updated_at - it's auto-generated by the database
        ];

        if ($this->treatmentModel->update($id, $treatmentData)) {
            // Get patient name for the activity log (scoped)
            $patient = $this->patientModel->where('clinic_id', $clinicId)->find($treatmentData['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Log the treatment update activity
            $this->activityLogger->logTreatmentActivity(
                'update',
                $id,
                "Treatment updated for {$patientName} - {$treatmentData['treatment_type']} - Cost: " . settings()->getCurrencySymbol() . number_format($treatmentData['cost'], 2)
            );
            
            $usageResult = $this->recordInventoryUsage($clinicId, $id);
            if ($usageResult && !$usageResult['success']) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', $usageResult['message']);
            }

            $returnTo = $this->sanitizeInternalReturnTo($this->request->getPost('return_to'), ['patient', 'patients', 'examination', 'treatment', 'treatments']);
            if ($returnTo) {
                return redirect()->to(base_url($returnTo))->with('success', 'Treatment has been updated successfully!');
            }

            return redirect()->to('/treatment')->with('success', 'Treatment has been updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update treatment. Please check your input and try again.');
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

        $treatment = $this->treatmentModel->where('clinic_id', $clinicId)->find($id);
        
        if (!$treatment) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Treatment not found']);
            } else {
                return redirect()->back()->with('error', 'Treatment not found');
            }
        }

        if ($this->treatmentModel->delete($id)) {
            // Get patient name for the activity log (scoped)
            $patient = $this->patientModel->where('clinic_id', $clinicId)->find($treatment['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Log the treatment deletion activity
            $this->activityLogger->logTreatmentActivity(
                'delete',
                $id,
                "Treatment deleted for {$patientName} - {$treatment['treatment_type']} - Cost was: " . settings()->getCurrencySymbol() . number_format($treatment['cost'], 2)
            );
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Treatment has been deleted successfully!']);
            } else {
                $returnTo = $this->sanitizeInternalReturnTo(
                    (string) ($this->request->getPost('return_to') ?: $this->request->getGet('return_to')),
                    ['patient', 'patients', 'examination', 'treatment', 'treatments']
                );
                if ($returnTo) {
                    return redirect()->to(base_url($returnTo))->with('success', 'Treatment has been deleted successfully!');
                }
                return redirect()->back()->with('success', 'Treatment has been deleted successfully!');
            }
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete treatment. Please try again.']);
            } else {
                return redirect()->back()->with('error', 'Failed to delete treatment. Please try again.');
            }
        }
    }

    public function complete($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $treatment = $this->treatmentModel->where('clinic_id', $clinicId)->find($id);
        
        if (!$treatment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Treatment not found']);
        }

        $updateData = [
            'status' => 'completed',
            'completion_date' => date('Y-m-d H:i:s'),
            'end_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->treatmentModel->update($id, $updateData)) {
            // Get patient name for the activity log (scoped)
            $patient = $this->patientModel->where('clinic_id', $clinicId)->find($treatment['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Log the treatment completion activity
            $this->activityLogger->logTreatmentActivity(
                'complete',
                $id,
                "Treatment completed for {$patientName} - {$treatment['treatment_type']} - Cost: " . settings()->getCurrencySymbol() . number_format($treatment['cost'], 2)
            );
            
            return $this->response->setJSON(['success' => true, 'message' => 'Treatment marked as completed']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to complete treatment']);
        }
    }

    public function getTreatmentStats()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $stats = $this->treatmentModel->getTreatmentStatsByClinic($clinicId);

        return $this->response->setJSON($stats);
    }

    public function print($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic.');
        }

        $treatment = $this->treatmentModel->getTreatmentWithPatientInfoByClinic($clinicId, $id);

        if (!$treatment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Treatment not found');
        }

        $data = [
            'title' => 'Treatment Report - ' . ($treatment['treatment_id'] ?? $treatment['id']),
            'treatment' => $treatment,
            'clinic' => settings()->getClinicInfo()
        ];

        $html = view('treatment/print', $data);

        $fileName = 'treatment_' . str_pad((string) $treatment['id'], 6, '0', STR_PAD_LEFT) . '.html';
        try {
            $this->storageService->storeExport(
                $html,
                $fileName,
                'text/html',
                $clinicId,
                'treatment',
                $id,
                'treatment_print'
            );
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            log_message('warning', 'Treatment print export blocked: ' . $e->getMessage());
        } catch (\Exception $e) {
            log_message('error', 'Treatment print export failed: ' . $e->getMessage());
        }

        return $this->response
            ->setHeader('Content-Type', 'text/html')
            ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->setBody($html);
    }

    private function recordInventoryUsage(int $clinicId, int $treatmentId): ?array
    {
        $enabled = $this->request->getPost('usage_enabled');
        if (!$enabled) {
            return null;
        }

        $usageDate = $this->request->getPost('usage_date');
        $notes = $this->request->getPost('usage_notes');
        $items = $this->request->getPost('usage_items');
        $items = is_array($items) ? $items : [];

        if (!$usageDate) {
            return ['success' => false, 'message' => 'Usage date is required when recording inventory usage.'];
        }

        if (empty($items)) {
            return ['success' => false, 'message' => 'Please select at least one inventory item to record usage.'];
        }

        $usageData = [];
        $totalCost = 0.0;

        foreach ($items as $item) {
            $itemId = $item['item_id'] ?? null;
            $quantity = $item['quantity'] ?? null;
            if (!$itemId || !is_numeric($quantity) || (int) $quantity <= 0) {
                return ['success' => false, 'message' => 'Please provide valid quantities for all selected items.'];
            }

            $inventoryItem = $this->inventoryModel->where('clinic_id', $clinicId)->find($itemId);
            if (!$inventoryItem) {
                return ['success' => false, 'message' => 'Invalid inventory item selected.'];
            }

            if ((int) $inventoryItem['quantity'] < (int) $quantity) {
                return [
                    'success' => false,
                    'message' => "Insufficient stock for {$inventoryItem['item_name']}. Available: {$inventoryItem['quantity']}, Requested: {$quantity}"
                ];
            }

            $usageData[] = [
                'item_id' => $itemId,
                'item_name' => $inventoryItem['item_name'],
                'quantity_used' => (int) $quantity,
                'unit_cost' => (float) $inventoryItem['unit_price'],
                'total_cost' => (float) $inventoryItem['unit_price'] * (int) $quantity
            ];

            $totalCost += (float) $inventoryItem['unit_price'] * (int) $quantity;
        }

        $usageModel = new InventoryUsageModel();
        $usageId = $usageModel->insert([
            'treatment_id' => $treatmentId,
            'usage_date' => $usageDate,
            'items_used' => json_encode($usageData),
            'total_cost' => $totalCost,
            'notes' => $notes,
            'recorded_by' => session()->get('user_id') ?? 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if (!$usageId) {
            return ['success' => false, 'message' => 'Failed to record inventory usage.'];
        }

        $autoPostInventoryCogs = (int) settings()->get('finance_auto_post_inventory_cogs', 1) === 1;
        if ($autoPostInventoryCogs) {
        try {
            $treatment = $this->treatmentModel->where('clinic_id', $clinicId)->find($treatmentId);
            $patientId = !empty($treatment['patient_id']) ? (int) $treatment['patient_id'] : null;
            $patientName = null;
            $treatmentLabel = null;
            if ($treatment) {
                $treatmentLabel = (string) ($treatment['treatment_id'] ?? ('#' . $treatment['id']));
                if ($patientId) {
                    $patient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
                    if ($patient) {
                        $patientName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''));
                    }
                }
            }

            (new FinanceLedgerService())->upsertInventoryUsageCogsExpense(
                $clinicId,
                (int) $usageId,
                [
                    'treatment_id' => $treatmentId,
                    'usage_date' => $usageDate,
                    'items_used' => json_encode($usageData),
                    'total_cost' => $totalCost,
                    'notes' => $notes,
                ],
                $patientId,
                (int) (session()->get('user_id') ?? 0),
                [
                    'patient_name' => $patientName,
                    'treatment_label' => $treatmentLabel,
                ]
            );
        } catch (\Throwable $financePostError) {
            log_message('error', 'Treatment inventory usage finance posting failed: ' . $financePostError->getMessage());
        }
        }

        foreach ($usageData as $usage) {
            $inventoryItem = $this->inventoryModel->where('clinic_id', $clinicId)->find($usage['item_id']);
            if ($inventoryItem) {
                $newQuantity = (int) $inventoryItem['quantity'] - (int) $usage['quantity_used'];
                $this->inventoryModel->update($usage['item_id'], [
                    'quantity' => $newQuantity,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        return ['success' => true, 'message' => 'Inventory usage recorded.'];
    }

    private function getTreatmentTypes(?string $currentType = null)
    {
        $types = TreatmentCatalog::forSelection();

        if ($currentType !== null && $currentType !== '' && !isset($types[$currentType])) {
            $allTypes = TreatmentCatalog::all();
            if (isset($allTypes[$currentType])) {
                $types[$currentType] = $allTypes[$currentType];
            }
        }

        return $types;
    }

    private function getTreatmentName($treatmentType)
    {
        return TreatmentCatalog::nameFor((string) $treatmentType);
    }
}
