<?php

namespace App\Controllers;

use App\Models\TreatmentModel;
use App\Models\PatientModel;
use App\Services\ActivityLogger;

class Treatment extends BaseController
{
    protected $treatmentModel;
    protected $patientModel;
    protected $activityLogger;
    protected $db;

    public function __construct()
    {
        $this->treatmentModel = new TreatmentModel();
        $this->patientModel = new PatientModel();
        $this->activityLogger = new ActivityLogger();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        try {
            // Get filter parameters from URL
            $search = $this->request->getGet('search') ?? '';
            $statusFilter = $this->request->getGet('status') ?? '';
            $typeFilter = $this->request->getGet('type') ?? '';
            
            // Get treatments with patient information
            $query = $this->treatmentModel
                ->select('treatments.*, patients.first_name, patients.last_name, patients.phone')
                ->join('patients', 'patients.id = treatments.patient_id', 'left');
            
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
                'total_treatments' => $this->treatmentModel->countAllResults(),
                'active_treatments' => $this->treatmentModel->where('status', 'active')->countAllResults(),
                'completed_treatments' => $this->treatmentModel->where('status', 'completed')->countAllResults(),
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
            $request = $this->request;
            
            // DataTables parameters with defaults
            $draw = intval($request->getPost('draw') ?? 1);
            $start = intval($request->getPost('start') ?? 0);
            $length = intval($request->getPost('length') ?? 10);
            
            // Handle search parameter
            $searchParam = $request->getPost('search');
            $searchValue = '';
            if (is_array($searchParam) && isset($searchParam['value'])) {
                $searchValue = trim($searchParam['value']);
            }
            
            // Handle custom filters
            $statusFilter = $request->getPost('status_filter') ?? $request->getGet('status_filter') ?? '';
            $typeFilter = $request->getPost('type_filter') ?? $request->getGet('type_filter') ?? '';
            
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
                'patients.first_name',
                'treatments.treatment_type',
                'treatments.cost',
                'treatments.status',
                'treatments.start_date'
            ];
            
            $orderColumnName = $columns[$orderColumn] ?? 'treatments.start_date';
            
            // Check if tables exist first
            $tables = $this->db->listTables();
            if (!in_array('treatments', $tables) || !in_array('patients', $tables)) {
                return $this->response->setJSON([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Required tables do not exist'
                ]);
            }
            
            // Get total records count
            $totalRecords = $this->db->table('treatments')->countAllResults();
            
            // If no treatments exist, return empty result
            if ($totalRecords == 0) {
                return $this->response->setJSON([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]);
            }
            
            // Build query
            $query = $this->db->table('treatments')
                ->select('treatments.*, patients.first_name, patients.last_name, patients.phone')
                ->join('patients', 'patients.id = treatments.patient_id', 'left');
            
            // Apply search filter
            if (!empty($searchValue)) {
                $query->groupStart()
                    ->like('patients.first_name', $searchValue)
                    ->orLike('patients.last_name', $searchValue)
                    ->orLike('patients.phone', $searchValue)
                    ->orLike('treatments.treatment_type', $searchValue)
                    ->orLike('treatments.status', $searchValue)
                    ->orLike('treatments.treatment_description', $searchValue)
                    ->groupEnd();
            }
            
            // Apply status filter
            if (!empty($statusFilter)) {
                $query->where('treatments.status', $statusFilter);
            }
            
            // Apply type filter
            if (!empty($typeFilter)) {
                $query->where('treatments.treatment_type', $typeFilter);
            }
            
            // Get filtered count
            $filteredRecords = $query->countAllResults(false);
            
            // Get data with ordering and pagination
            $treatments = $query->orderBy($orderColumnName, $orderDir)
                ->limit($length, $start)
                ->get()
                ->getResultArray();
            
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
                    'description' => $treatment['description'] ?? '',
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

        $data = [
            'title' => 'Create New Treatment',
            'patients' => [], // S4-02f: No bulk preload
            'treatment_types' => $this->getTreatmentTypes(),
            'validation' => null, // Initialize validation as null
            'loadSelect2' => true
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
            'treatment_type' => 'required|in_list[cleaning,extraction,filling,crown,root_canal,orthodontic,implant,other]',
            'tooth_number' => 'permit_empty|string|max_length[10]',
            'description' => 'required|min_length[10]',
            'cost' => 'required|decimal|greater_than[0]',
            'start_date' => 'required|valid_date',
            'estimated_duration' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            $data = [
                'title' => 'Create New Treatment',
                'patients' => [], 
                'treatment_types' => $this->getTreatmentTypes(),
                'validation' => $this->validator,
                'loadSelect2' => true
            ];
            return $this->view('treatment/create', $data);
        }

        $treatmentData = [
            'clinic_id' => $clinicId,
            'patient_id' => $this->request->getPost('patient_id'),
            'treatment_name' => $this->getTreatmentName($this->request->getPost('treatment_type')),
            'treatment_type' => $this->request->getPost('treatment_type'),
            'tooth_number' => $this->request->getPost('tooth_number') ?: null,
            'treatment_description' => $this->request->getPost('description'),
            'cost' => $this->request->getPost('cost'),
            'start_date' => $this->request->getPost('start_date'),
            'estimated_duration' => $this->request->getPost('estimated_duration'),
            'status' => 'active',
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

        $data = [
            'title' => 'Treatment Details',
            'treatment' => $treatment,
        ];

        return $this->view('treatment/show', $data);
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

        $data = [
            'title' => 'Edit Treatment',
            'treatment' => $treatment,
            'patients' => $patient ? [$patient] : [], // Only emit current patient
            'treatment_types' => $this->getTreatmentTypes(),
            'validation' => null,
            'loadSelect2' => true
        ];

        return $this->view('treatment/edit', $data);
    }

    public function update($id)
    {
        $treatment = $this->treatmentModel->find($id);
        
        if (!$treatment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Treatment not found');
        }

        $rules = [
            'patient_id' => 'required|integer',
            'treatment_type' => 'required|in_list[cleaning,extraction,filling,crown,root_canal,orthodontic,implant,other]',
            'tooth_number' => 'permit_empty|string|max_length[10]',
            'description' => 'required|min_length[10]',
            'cost' => 'required|decimal|greater_than[0]',
            'start_date' => 'required|valid_date',
            'estimated_duration' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            $data = [
                'title' => 'Edit Treatment',
                'treatment' => $treatment,
                'patients' => $this->patientModel->findAll(),
                'treatment_types' => $this->getTreatmentTypes(),
                'validation' => $this->validator,
            ];
            return $this->view('treatment/edit', $data);
        }

        $treatmentData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'treatment_name' => $this->getTreatmentName($this->request->getPost('treatment_type')),
            'treatment_type' => $this->request->getPost('treatment_type'),
            'tooth_number' => $this->request->getPost('tooth_number') ?: null,
            'treatment_description' => $this->request->getPost('description'),
            'cost' => $this->request->getPost('cost'),
            'start_date' => $this->request->getPost('start_date'),
            'estimated_duration' => $this->request->getPost('estimated_duration'),
            // Don't set updated_at - it's auto-generated by the database
        ];

        if ($this->treatmentModel->update($id, $treatmentData)) {
            // Get patient name for the activity log
            $patient = $this->patientModel->find($treatmentData['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Log the treatment update activity
            $this->activityLogger->logTreatmentActivity(
                'update',
                $id,
                "Treatment updated for {$patientName} - {$treatmentData['treatment_type']} - Cost: " . settings()->getCurrencySymbol() . number_format($treatmentData['cost'], 2)
            );
            
            return redirect()->to('/treatment')->with('success', 'Treatment has been updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update treatment. Please check your input and try again.');
        }
    }

    public function delete($id)
    {
        $treatment = $this->treatmentModel->find($id);
        
        if (!$treatment) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Treatment not found']);
            } else {
                return redirect()->back()->with('error', 'Treatment not found');
            }
        }

        if ($this->treatmentModel->delete($id)) {
            // Get patient name for the activity log
            $patient = $this->patientModel->find($treatment['patient_id']);
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
        $treatment = $this->treatmentModel->find($id);
        
        if (!$treatment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Treatment not found']);
        }

        $updateData = [
            'status' => 'completed',
            'completion_date' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->treatmentModel->update($id, $updateData)) {
            // Get patient name for the activity log
            $patient = $this->patientModel->find($treatment['patient_id']);
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
        $stats = [
            'total' => $this->treatmentModel->countAllResults(),
            'active' => $this->treatmentModel->where('status', 'active')->countAllResults(),
            'completed' => $this->treatmentModel->where('status', 'completed')->countAllResults(),
            'cancelled' => $this->treatmentModel->where('status', 'cancelled')->countAllResults(),
            'on_hold' => $this->treatmentModel->where('status', 'on_hold')->countAllResults(),
        ];

        return $this->response->setJSON($stats);
    }

    private function getTreatmentTypes()
    {
        return [
            'cleaning' => 'Teeth Cleaning',
            'extraction' => 'Tooth Extraction',
            'filling' => 'Dental Filling',
            'crown' => 'Dental Crown',
            'root_canal' => 'Root Canal Treatment',
            'orthodontic' => 'Orthodontic Treatment',
            'implant' => 'Dental Implant',
            'other' => 'Other Treatment',
        ];
    }

    private function getTreatmentName($treatmentType)
    {
        $treatmentTypes = $this->getTreatmentTypes();
        return $treatmentTypes[$treatmentType] ?? 'Treatment';
    }
}
