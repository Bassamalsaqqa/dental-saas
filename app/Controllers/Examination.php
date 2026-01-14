<?php

namespace App\Controllers;

use App\Models\ExaminationModel;
use App\Models\PatientModel;
use App\Models\OdontogramModel;
use App\Models\TreatmentModel;
use App\Services\ActivityLogger;

class Examination extends BaseController
{
    protected $examinationModel;
    protected $patientModel;
    protected $odontogramModel;
    protected $treatmentModel;
    protected $activityLogger;
    protected $db;

    public function __construct()
    {
        $this->examinationModel = new ExaminationModel();
        $this->patientModel = new PatientModel();
        $this->odontogramModel = new OdontogramModel();
        $this->treatmentModel = new TreatmentModel();
        $this->activityLogger = new ActivityLogger();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data = [
            'title' => 'Examination Management'
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
        }
        
        $data = [
            'title' => 'New Examination',
            'patients' => $selectedPatient ? [$selectedPatient] : [], // S4-02f: No bulk preload
            'selected_patient_id' => $patientId,
            'selected_patient' => $selectedPatient,
            'validation' => \Config\Services::validation(),
            'loadSelect2' => true
        ];

        // Ensure user data is included
        $userData = $this->getUserDataForView();
        $data = array_merge($data, $userData);

        return $this->view('examination/create', $data);
    }

    public function store()
    {
        $rules = [
            'patient_id' => 'required|integer',
            'examination_date' => 'required|valid_date',
            'examination_type' => 'required|in_list[initial,periodic,emergency,follow_up]',
            'chief_complaint' => 'required|min_length[10]|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Get examination date (database field is date, not datetime)
        $examinationDate = $this->request->getPost('examination_date');

        $examinationData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'examination_date' => $examinationDate,
            'examination_type' => $this->request->getPost('examination_type'),
            'chief_complaint' => $this->request->getPost('chief_complaint'),
            'history_of_present_illness' => $this->request->getPost('history_of_present_illness'),
            'medical_history' => $this->request->getPost('medical_history'),
            'dental_history' => $this->request->getPost('dental_history'),
            'clinical_findings' => $this->request->getPost('clinical_findings'),
            'diagnosis' => $this->request->getPost('diagnosis'),
            'treatment_plan' => $this->request->getPost('treatment_plan'),
            'prognosis' => $this->request->getPost('prognosis'),
            'recommendations' => $this->request->getPost('recommendations'),
            'next_appointment' => $this->request->getPost('next_appointment'),
            'examination_notes' => $this->request->getPost('examination_notes'),
            'status' => 'pending',
            'created_by' => 1
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
            
            log_message('info', 'Examination created successfully with ID: ' . $examinationId);
            return redirect()->to(base_url('examination/' . $examinationId))->with('success', 'Examination created successfully');
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

        $examination = $this->examinationModel->select('examinations.*, patients.first_name, patients.last_name, patients.patient_id as patient_number')
            ->join('patients', 'patients.id = examinations.patient_id')
            ->where('examinations.clinic_id', $clinicId)
            ->where('patients.clinic_id', $clinicId)
            ->where('examinations.id', $id)
            ->first();
        
        if (!$examination) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Examination not found');
        }

        $data = [
            'title' => 'Examination Details - ' . $examination['examination_id'],
            'examination' => $examination,
            'odontogram' => $this->odontogramModel->where('clinic_id', $clinicId)->getOdontogramByExamination($id),
            'treatments' => $this->treatmentModel->where('clinic_id', $clinicId)->getTreatmentsByExamination($id),
            'condition_types' => $this->odontogramModel->getConditionTypes(),
            'tooth_positions' => $this->odontogramModel->getToothPositions()
        ];

        return $this->view('examination/show', $data);
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

        // Only load the specific patient for this examination (scoped)
        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($examination['patient_id']);

        $data = [
            'title' => 'Edit Examination - ' . ($examination['examination_id'] ?? $examination['id']),
            'examination' => $examination,
            'patients' => $patient ? [$patient] : [], // Only emit current patient
            'validation' => \Config\Services::validation(),
            'loadSelect2' => true
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

        $rules = [
            'patient_id' => 'required|integer',
            'examination_date' => 'required|valid_date',
            'examination_type' => 'required|in_list[initial,periodic,emergency,follow_up]',
            'chief_complaint' => 'required|min_length[10]|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $examinationData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'examination_date' => $this->request->getPost('examination_date'),
            'examination_type' => $this->request->getPost('examination_type'),
            'chief_complaint' => $this->request->getPost('chief_complaint'),
            'history_of_present_illness' => $this->request->getPost('history_of_present_illness'),
            'medical_history' => $this->request->getPost('medical_history'),
            'dental_history' => $this->request->getPost('dental_history'),
            'clinical_findings' => $this->request->getPost('clinical_findings'),
            'diagnosis' => $this->request->getPost('diagnosis'),
            'treatment_plan' => $this->request->getPost('treatment_plan'),
            'prognosis' => $this->request->getPost('prognosis'),
            'recommendations' => $this->request->getPost('recommendations'),
            'next_appointment' => $this->request->getPost('next_appointment'),
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
            
            return redirect()->to(base_url('examination/' . $id))->with('success', 'Examination updated successfully');
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

        $conditionData = [
            'clinic_id' => $clinicId,
            'examination_id' => $examinationId,
            'condition_type' => $conditionType,
            'condition_description' => $conditionDescription,
            'treatment_notes' => $treatmentNotes,
            'treatment_date' => date('Y-m-d'),
            'treatment_status' => 'completed',
            'created_by' => 1
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
        $examinations = $this->examinationModel->where('clinic_id', $clinicId)->getExaminationsByDateRange($date, $date);
        
        return $this->response->setJSON($examinations);
    }

    public function complete($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $examination = $this->examinationModel->where('clinic_id', $clinicId)->find($id);
        
        if (!$examination) {
            return $this->response->setJSON(['success' => false, 'message' => 'Examination not found']);
        }

        $updateData = [ 
            'status' => 'completed',
            'examination_notes' => $this->request->getPost('examination_notes')
        ];

        if ($this->examinationModel->update($id, $updateData)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Examination completed successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to complete examination']);
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
            $filteredRecords = $this->examinationModel->countExaminationsByClinic($clinicId, $searchValue);
            
            // Get data (scoped)
            $examinations = $this->examinationModel->getExaminationsByClinic($clinicId, $length, $start, $searchValue, $orderColumnName, $orderDir);
            
            // Format data
            $data = [];
            foreach ($examinations as $examination) {
                $data[] = [
                    'examination_id' => $examination['examination_id'] ?? '',
                    'patient_name' => trim(($examination['first_name'] ?? '') . ' ' . ($examination['last_name'] ?? '')),
                    'patient_number' => $examination['patient_number'] ?? '',
                    'examination_date' => $examination['examination_date'] ?? '',
                    'examination_date_formatted' => formatDate($examination['examination_date'] ?? ''),
                    'examination_time_formatted' => formatTime($examination['examination_date'] ?? ''),
                    'examination_type' => $examination['examination_type'] ?? '',
                    'status' => $examination['status'] ?? '',
                    'chief_complaint' => $examination['chief_complaint'] ?? '',
                    'id' => intval($examination['id'] ?? 0),
                    'patient_id' => intval($examination['patient_id'] ?? 0)
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
            foreach ($patients as $index => $patient) {
                $sampleExaminations[] = [
                    'patient_id' => $patient['id'],
                    'examination_id' => 'EXM' . date('Ymd') . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'examination_date' => date('Y-m-d H:i:s', strtotime('-' . $index . ' days')),
                    'examination_type' => ['initial', 'periodic', 'emergency'][$index % 3],
                    'chief_complaint' => 'Sample complaint ' . ($index + 1),
                    'status' => ['pending', 'completed', 'in_progress'][$index % 3],
                    'created_by' => 1,
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

        $examination = $this->examinationModel->select('examinations.*, patients.first_name, patients.last_name, patients.patient_id as patient_number')
            ->join('patients', 'patients.id = examinations.patient_id')
            ->where('examinations.clinic_id', $clinicId)
            ->where('patients.clinic_id', $clinicId)
            ->where('examinations.id', $id)
            ->first();
        
        if (!$examination) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Examination not found');
        }

        $data = [
            'title' => 'Examination Report - ' . $examination['examination_id'],
            'examination' => $examination,
            'odontogram' => $this->odontogramModel->where('clinic_id', $clinicId)->getOdontogramByExamination($id),
            'treatments' => $this->treatmentModel->where('clinic_id', $clinicId)->getTreatmentsByExamination($id),
            'clinic' => settings()->getClinicInfo()
        ];

        return $this->view('examination/print', $data);
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
            
            if ($this->examinationModel->delete($id)) {
                return $this->response->setJSON(['success' => true, 'message' => 'Examination deleted successfully']);
            } else {
                $errors = $this->examinationModel->errors();
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete examination: ' . implode(', ', $errors)]);
            }
        } catch (\Exception $e) {
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
            $examinations = $this->examinationModel->where('clinic_id', $clinicId)->getExaminationsByDateRange($start, $end);
            
            log_message('debug', 'Found ' . count($examinations) . ' examinations for calendar');
            
            $events = [];
            foreach ($examinations as $examination) {
                $events[] = [
                    'id' => $examination['id'],
                    'title' => $examination['patient_name'] . ' - Examination',
                    'start' => $examination['examination_date'] . 'T' . $examination['examination_time'],
                    'end' => $examination['examination_date'] . 'T' . date('H:i', strtotime($examination['examination_time'] . ' +30 minutes')),
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
