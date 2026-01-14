<?php

namespace App\Controllers;

use App\Models\PatientModel;
use App\Models\ExaminationModel;
use App\Models\AppointmentModel;
use App\Models\FinanceModel;
use App\Models\OdontogramModel;
use App\Models\TreatmentModel;
use App\Models\PrescriptionModel;
use App\Services\ActivityLogger;

class Patient extends BaseController
{
    protected $patientModel;
    protected $examinationModel;
    protected $appointmentModel;
    protected $financeModel;
    protected $odontogramModel;
    protected $treatmentModel;
    protected $prescriptionModel;
    protected $activityLogger;

    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->examinationModel = new ExaminationModel();
        $this->appointmentModel = new AppointmentModel();
        $this->financeModel = new FinanceModel();
        $this->odontogramModel = new OdontogramModel();
        $this->treatmentModel = new TreatmentModel();
        $this->prescriptionModel = new PrescriptionModel();
        $this->activityLogger = new ActivityLogger();
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
            $baseBuilder = $this->patientModel->builder();
            $baseBuilder->where('clinic_id', $clinicId);

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
            $baseBuilder = $this->patientModel->builder();
            $baseBuilder->where('clinic_id', $clinicId);

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
        $data = [
            'title' => 'Add New Patient',
            'validation' => \Config\Services::validation()
        ];

        return $this->view('patient/create', $data);
    }

    public function store()
    {
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

        $patientData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'gender' => $this->request->getPost('gender'),
            'address' => $this->request->getPost('address'),
            'city' => $this->request->getPost('city'),
            'state' => $this->request->getPost('state'),
            'zip_code' => $this->request->getPost('zip_code'),
            'country' => $this->request->getPost('country'),
            'emergency_contact_name' => $this->request->getPost('emergency_contact_name'),
            'emergency_contact_phone' => $this->request->getPost('emergency_contact_phone'),
            'medical_history' => $this->request->getPost('medical_history'),
            'allergies' => $this->request->getPost('allergies'),
            'insurance_provider' => $this->request->getPost('insurance_provider'),
            'insurance_number' => $this->request->getPost('insurance_number'),
            'notes' => $this->request->getPost('notes'),
            'status' => 'active'
        ];

        try {
            if ($this->patientModel->insert($patientData)) {
                $patientId = $this->patientModel->getInsertID();
                
                // Log the patient creation activity
                $this->activityLogger->logPatientActivity(
                    'create',
                    $patientId,
                    "New patient '{$patientData['first_name']} {$patientData['last_name']}' registered successfully"
                );
                
                // Check if this is an AJAX request
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Patient added successfully',
                        'patient_id' => $patientId
                    ]);
                }
                return redirect()->to('/patient')->with('success', 'Patient has been added successfully!');
            } else {
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
            // Log the error
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
        $patient = $this->patientModel->find($id);
        
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $data = [
            'title' => 'Patient Details - ' . $patient['first_name'] . ' ' . $patient['last_name'],
            'patient' => $patient,
            'examinations' => $this->examinationModel->getExaminationsByPatient($id),
            'appointments' => $this->appointmentModel->getAppointmentsByPatient($id),
            'finances' => $this->financeModel->getFinanceByPatient($id),
            'odontogram' => $this->odontogramModel->getOdontogramByPatient($id),
            'odontogram_stats' => $this->odontogramModel->getOdontogramStats($id),
            'treatments' => $this->treatmentModel->getTreatmentsByPatient($id),
            'prescriptions' => $this->prescriptionModel->getPrescriptionsByPatient($id)
        ];

        return $this->view('patient/show', $data);
    }

    public function edit($id)
    {
        $patient = $this->patientModel->find($id);
        
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $data = [
            'title' => 'Edit Patient - ' . $patient['first_name'] . ' ' . $patient['last_name'],
            'patient' => $patient,
            'validation' => \Config\Services::validation()
        ];

        return $this->view('patient/edit', $data);
    }

    public function update($id)
    {
        $patient = $this->patientModel->find($id);
        
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
        $email = $this->request->getPost('email');
        if (!empty($email)) {
            $existingPatient = $this->patientModel->where('email', $email)->where('id !=', $id)->first();
            if ($existingPatient) {
                return redirect()->back()->withInput()->with('error', 'This email is already registered to another patient');
            }
        }

        $patientData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'gender' => $this->request->getPost('gender'),
            'address' => $this->request->getPost('address'),
            'city' => $this->request->getPost('city'),
            'state' => $this->request->getPost('state'),
            'zip_code' => $this->request->getPost('zip_code'),
            'country' => $this->request->getPost('country'),
            'emergency_contact_name' => $this->request->getPost('emergency_contact_name'),
            'emergency_contact_phone' => $this->request->getPost('emergency_contact_phone'),
            'medical_history' => $this->request->getPost('medical_history'),
            'allergies' => $this->request->getPost('allergies'),
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
        $patient = $this->patientModel->find($id);
        
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        if ($this->patientModel->delete($id)) {
            // Log the patient deletion activity
            $this->activityLogger->logPatientActivity(
                'delete',
                $id,
                "Patient '{$patient['first_name']} {$patient['last_name']}' deleted from the system"
            );
            
            return redirect()->to('/patient')->with('success', 'Patient has been deleted successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to delete patient. Please try again.');
        }
    }

    public function search()
    {
        $searchTerm = $this->request->getGet('q');
        
        if (empty($searchTerm)) {
            return redirect()->to('/patient');
        }

        $data = [
            'title' => 'Search Results for: ' . $searchTerm,
            'patients' => $this->patientModel->searchPatients($searchTerm),
            'search_term' => $searchTerm
        ];

        return $this->view('patient/search', $data);
    }

    public function getPatientData($id)
    {
        $patient = $this->patientModel->find($id);
        
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
