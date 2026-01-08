<?php

namespace App\Controllers;

use App\Models\PrescriptionModel;
use App\Models\PatientModel;
use App\Services\ActivityLogger;

class Prescription extends BaseController
{
    protected $prescriptionModel;
    protected $patientModel;
    protected $activityLogger;

    protected $db;

    public function __construct()
    {
        $this->prescriptionModel = new PrescriptionModel();
        $this->patientModel = new PatientModel();
        $this->activityLogger = new ActivityLogger();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        try {
            $data = [
                'title' => 'Prescription Management',
                'prescriptions' => [], // Will be loaded via AJAX
                'total_prescriptions' => $this->prescriptionModel->countAllResults(),
                'active_prescriptions' => $this->prescriptionModel->where('status', 'active')->countAllResults(),
                'expired_prescriptions' => $this->prescriptionModel->where('status', 'expired')->countAllResults(),
                'loadSelect2' => true  // Load Select2 for patient selection dropdowns
            ];

            return $this->view('prescription/index', $data);
        } catch (\Exception $e) {
            // Log the error
            log_message('error', 'Prescription index error: ' . $e->getMessage());
            
            // Return a simplified version
            $data = [
                'title' => 'Prescription Management',
                'prescriptions' => [],
                'total_prescriptions' => 0,
                'active_prescriptions' => 0,
                'expired_prescriptions' => 0,
                'error' => 'Unable to load prescription data. Please check the database connection.',
                'loadSelect2' => true  // Load Select2 for patient selection dropdowns
            ];

            return $this->view('prescription/index', $data);
        }
    }

    public function testPrescriptionsData()
    {
        try {
            // Test database connection
            $tables = $this->db->listTables();
            
            // Test prescriptions table
            $prescriptionCount = $this->db->table('prescriptions')->countAllResults();
            
            // Get a sample prescription
            $samplePrescription = $this->db->table('prescriptions')->limit(1)->get()->getRowArray();
            
            return $this->response->setJSON([
                'success' => true,
                'tables' => $tables,
                'prescription_count' => $prescriptionCount,
                'sample_prescription' => $samplePrescription,
                'message' => 'Database connection successful'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Database connection failed'
            ]);
        }
    }

    public function getPrescriptionsData()
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
                'prescriptions.id',
                'patients.first_name',
                'prescriptions.prescribed_date',
                'prescriptions.medication_name',
                'prescriptions.status'
            ];
            
            $orderColumnName = $columns[$orderColumn] ?? 'prescriptions.id';
            
            // Log the request for debugging
            log_message('debug', 'Prescription DataTables Request - Draw: ' . $draw . ', Start: ' . $start . ', Length: ' . $length . ', Search: ' . $searchValue . ', OrderColumn: ' . $orderColumnName . ', OrderDir: ' . $orderDir);
            
            // Validate sort order
            $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? strtolower($orderDir) : 'desc';
            
            // Check if tables exist
            $tables = $this->db->listTables();
            log_message('debug', 'Available tables: ' . implode(', ', $tables));
            
            if (!in_array('prescriptions', $tables) || !in_array('patients', $tables)) {
                log_message('error', 'Required tables missing. Prescriptions: ' . (in_array('prescriptions', $tables) ? 'exists' : 'missing') . ', Patients: ' . (in_array('patients', $tables) ? 'exists' : 'missing'));
                return $this->response->setJSON([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Required tables do not exist'
                ]);
            }
            
            // Get total records count
            $totalRecords = $this->db->table('prescriptions')->countAllResults();
            
            // If no prescriptions exist, return empty result
            if ($totalRecords == 0) {
                return $this->response->setJSON([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]);
            }
            
            // Build query
            $query = $this->db->table('prescriptions')
                ->select('prescriptions.*, patients.first_name, patients.last_name, patients.phone')
                ->join('patients', 'patients.id = prescriptions.patient_id', 'left');
            
            // Apply search filter
            if (!empty($searchValue)) {
                $query->groupStart()
                    ->like('prescriptions.id', $searchValue)
                    ->orLike('patients.first_name', $searchValue)
                    ->orLike('patients.last_name', $searchValue)
                    ->orLike('patients.phone', $searchValue)
                    ->orLike('prescriptions.medication_name', $searchValue)
                    ->orLike('prescriptions.notes', $searchValue)
                    ->groupEnd();
            }
            
            // Get filtered count
            $filteredRecords = $query->countAllResults(false);
            
            // Get data with ordering and pagination
            $prescriptions = $query->orderBy($orderColumnName, $orderDir)
                ->limit($length, $start)
                ->get()
                ->getResultArray();
            
            // Format data
            $data = [];
            foreach ($prescriptions as $prescription) {
                // Log the raw prescription data for debugging
                log_message('debug', 'Raw prescription data: ' . json_encode($prescription));
                
                $data[] = [
                    'id' => intval($prescription['id'] ?? 0),
                    'prescription_id' => 'RX-' . str_pad($prescription['id'], 6, '0', STR_PAD_LEFT),
                    'patient_name' => trim(($prescription['first_name'] ?? '') . ' ' . ($prescription['last_name'] ?? '')),
                    'phone' => $prescription['phone'] ?? '',
                    'medication_name' => $prescription['medication_name'] ?? '',
                    'notes' => $prescription['notes'] ?? '',
                    'status' => $prescription['status'] ?? '',
                    'prescribed_date' => $prescription['prescribed_date'] ?? $prescription['created_at'] ?? '',
                    'prescribed_date_formatted' => formatDate($prescription['prescribed_date'] ?? $prescription['created_at'] ?? ''),
                    'patient_id' => intval($prescription['patient_id'] ?? 0)
                ];
            }
            
            // Calculate pagination
            $totalPages = ceil($filteredRecords / $length);
            
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Prescription DataTables error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'draw' => $draw,
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
            'title' => 'Create New Prescription',
            'patients' => $this->patientModel->findAll(),
            'medications' => $this->getMedications(),
            'loadSelect2' => true  // Load Select2 for patient selection dropdowns
        ];

        return $this->view('prescription/create', $data);
    }

    public function store()
    {
        $rules = [
            'patient_id' => 'required|integer',
            'instructions' => 'permit_empty|min_length[10]',
            'prescribed_date' => 'required|valid_date',
            'expiry_date' => 'permit_empty|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get medicines data
        $medicines = $this->request->getPost('medicines');
        
        if (empty($medicines) || !is_array($medicines)) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one medicine to the prescription.');
        }

        // Validate medicines
        foreach ($medicines as $index => $medicine) {
            if (empty($medicine['name']) || empty($medicine['dosage']) || empty($medicine['frequency']) || empty($medicine['duration'])) {
                return redirect()->back()->withInput()->with('error', 'Please fill in all medicine details completely.');
            }
        }

        // Create prescription with medicines as JSON
        $medicinesJson = json_encode($medicines);
        
        $prescriptionData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'medication_name' => $medicinesJson, // Store as JSON
            'dosage' => '', // Not used in new structure
            'frequency' => '', // Not used in new structure
            'duration' => '', // Not used in new structure
            'instructions' => $this->request->getPost('instructions') ?: '',
            'prescribed_date' => $this->request->getPost('prescribed_date'),
            'expiry_date' => $this->request->getPost('expiry_date') ?: null,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->prescriptionModel->insert($prescriptionData)) {
            $prescriptionId = $this->prescriptionModel->getInsertID();
            
            // Get patient name for the activity log
            $patient = $this->patientModel->find($prescriptionData['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Count medicines for description
            $medicineCount = count($medicines);
            
            // Log the prescription creation activity
            $this->activityLogger->logPrescriptionActivity(
                'create',
                $prescriptionId,
                "New prescription created for {$patientName} with {$medicineCount} medication(s)"
            );
            
            return redirect()->to(base_url('prescription'))->with('success', 'Prescription has been created successfully!');
        } else {
            // Get detailed error information
            $errors = $this->prescriptionModel->errors();
            $errorMessage = 'Failed to create prescription. Please try again.';
            
            if (!empty($errors)) {
                $errorMessage .= ' Errors: ' . implode(', ', $errors);
            }
            
            return redirect()->back()->withInput()->with('error', 'Failed to create prescription. Please check your input and try again.');
        }
    }

    public function show($id)
    {
        $prescription = $this->prescriptionModel->getPrescriptionWithPatientInfo($id);
        
        if (!$prescription) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Prescription not found');
        }

        $data = [
            'title' => 'Prescription Details',
            'prescription' => $prescription,
        ];

        return $this->view('prescription/show', $data);
    }

    public function edit($id)
    {
        $prescription = $this->prescriptionModel->find($id);
        
        if (!$prescription) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Prescription not found');
        }

        $data = [
            'title' => 'Edit Prescription',
            'prescription' => $prescription,
            'patients' => $this->patientModel->findAll(),
            'medications' => $this->getMedications(),
        ];

        return $this->view('prescription/edit', $data);
    }

    public function update($id)
    {
        $prescription = $this->prescriptionModel->find($id);
        
        if (!$prescription) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Prescription not found');
        }

        $rules = [
            'patient_id' => 'required|integer',
            'instructions' => 'permit_empty|min_length[10]',
            'prescribed_date' => 'required|valid_date',
            'expiry_date' => 'permit_empty|valid_date',
            'status' => 'required|in_list[active,expired,cancelled,pending]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get medicines data
        $medicines = $this->request->getPost('medicines');
        
        if (empty($medicines) || !is_array($medicines)) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one medicine to the prescription.');
        }

        // Validate medicines
        foreach ($medicines as $index => $medicine) {
            if (empty($medicine['name']) || empty($medicine['dosage']) || empty($medicine['frequency']) || empty($medicine['duration'])) {
                return redirect()->back()->withInput()->with('error', 'Please fill in all medicine details completely.');
            }
        }

        // Create prescription with medicines as JSON
        $medicinesJson = json_encode($medicines);
        
        $prescriptionData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'medication_name' => $medicinesJson, // Store as JSON
            'dosage' => '', // Not used in new structure
            'frequency' => '', // Not used in new structure
            'duration' => '', // Not used in new structure
            'instructions' => $this->request->getPost('instructions'),
            'prescribed_date' => $this->request->getPost('prescribed_date'),
            'expiry_date' => $this->request->getPost('expiry_date'),
            'status' => $this->request->getPost('status'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->prescriptionModel->update($id, $prescriptionData)) {
            // Get patient name for the activity log
            $patient = $this->patientModel->find($prescriptionData['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Count medicines for description
            $medicineCount = count($medicines);
            
            // Log the prescription update activity
            $this->activityLogger->logPrescriptionActivity(
                'update',
                $id,
                "Prescription updated for {$patientName} with {$medicineCount} medication(s) - Status: {$prescriptionData['status']}"
            );
            
            return redirect()->to(base_url('prescription'))->with('success', 'Prescription has been updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update prescription. Please check your input and try again.');
        }
    }

    public function delete($id)
    {
        $prescription = $this->prescriptionModel->find($id);
        
        if (!$prescription) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Prescription not found']);
            }
            return redirect()->to(base_url('prescription'))->with('error', 'Prescription not found');
        }

        if ($this->prescriptionModel->delete($id)) {
            // Get patient name for the activity log
            $patient = $this->patientModel->find($prescription['patient_id']);
            $patientName = $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown Patient';
            
            // Log the prescription deletion activity
            $this->activityLogger->logPrescriptionActivity(
                'delete',
                $id,
                "Prescription deleted for {$patientName} - Status was: {$prescription['status']}"
            );
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Prescription deleted successfully']);
            }
            return redirect()->to(base_url('prescription'))->with('success', 'Prescription has been deleted successfully!');
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete prescription']);
            }
            return redirect()->to(base_url('prescription'))->with('error', 'Failed to delete prescription. Please try again.');
        }
    }

    public function remove($id)
    {
        return $this->delete($id);
    }

    public function print($id)
    {
        $prescription = $this->prescriptionModel->getPrescriptionWithPatientInfo($id);
        
        if (!$prescription) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Prescription not found');
        }

        $data = [
            'title' => 'Print Prescription',
            'prescription' => $prescription,
            'clinic' => settings()->getClinicInfo()
        ];

        return $this->view('prescription/print', $data);
    }

    public function getPrescriptionStats()
    {
        $stats = [
            'total' => $this->prescriptionModel->countAllResults(),
            'active' => $this->prescriptionModel->where('status', 'active')->countAllResults(),
            'expired' => $this->prescriptionModel->where('status', 'expired')->countAllResults(),
            'cancelled' => $this->prescriptionModel->where('status', 'cancelled')->countAllResults(),
        ];

        return $this->response->setJSON($stats);
    }

    private function getMedications()
    {
        return [
            'amoxicillin' => 'Amoxicillin',
            'ibuprofen' => 'Ibuprofen',
            'acetaminophen' => 'Acetaminophen',
            'clindamycin' => 'Clindamycin',
            'metronidazole' => 'Metronidazole',
            'diclofenac' => 'Diclofenac',
            'naproxen' => 'Naproxen',
            'other' => 'Other Medication',
        ];
    }
}
