<?php

namespace App\Controllers;

use App\Models\OdontogramModel;
use App\Models\PatientModel;
use App\Models\ExaminationModel;

class Odontogram extends BaseController
{
    protected $odontogramModel;
    protected $patientModel;
    protected $examinationModel;

    public function __construct()
    {
        $this->odontogramModel = new OdontogramModel();
        $this->patientModel = new PatientModel();
        $this->examinationModel = new ExaminationModel();
    }

    public function list()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $data = [
            'title' => 'Odontogram Management',
        ];

        return $this->view('odontogram/list', $data);
    }

    public function getPatientsData()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $request = $this->request;
            
            // DataTables parameters
            $draw = intval($request->getPost('draw'));
            $start = intval($request->getPost('start'));
            $length = intval($request->getPost('length'));
            $searchValue = $request->getPost('search')['value'] ?? '';
            $orderColumn = intval($request->getPost('order')[0]['column'] ?? 0);
            $orderDir = $request->getPost('order')[0]['dir'] ?? 'asc';
            
            // Get all patients with stats (includes last_visit) SCOPED
            // Note: DataTables processing here is manual in-memory which is inefficient but preserving logic.
            // Just ensuring fetch is scoped.
            $allPatients = $this->patientModel->getPatientsWithStats($clinicId);
            $totalRecords = count($allPatients);
            
            // Apply search filter
            $filteredPatients = $allPatients;
            if (!empty($searchValue)) {
                $filteredPatients = array_filter($allPatients, function($patient) use ($searchValue) {
                    $searchLower = strtolower($searchValue);
                    return (
                        strpos(strtolower($patient['first_name'] ?? ''), $searchLower) !== false ||
                        strpos(strtolower($patient['last_name'] ?? ''), $searchLower) !== false ||
                        strpos(strtolower($patient['phone'] ?? ''), $searchLower) !== false ||
                        strpos(strtolower($patient['email'] ?? ''), $searchLower) !== false ||
                        strpos(strtolower($patient['patient_id'] ?? ''), $searchLower) !== false
                    );
                });
            }
            
            $filteredRecords = count($filteredPatients);
            
            // Apply sorting
            $columns = ['first_name', 'phone', 'patient_id', 'date_of_birth', 'last_visit', 'id'];
            $orderBy = $columns[$orderColumn] ?? 'first_name';
            
            usort($filteredPatients, function($a, $b) use ($orderBy, $orderDir) {
                $valueA = $a[$orderBy] ?? '';
                $valueB = $b[$orderBy] ?? '';
                
                if ($orderBy === 'first_name') {
                    $valueA = ($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '');
                    $valueB = ($b['first_name'] ?? '') . ' ' . ($b['last_name'] ?? '');
                }
                
                // Handle missing fields gracefully
                if (empty($valueA)) $valueA = '';
                if (empty($valueB)) $valueB = '';
                
                $result = strcmp($valueA, $valueB);
                return $orderDir === 'desc' ? -$result : $result;
            });
            
            // Apply pagination
            $paginatedPatients = array_slice($filteredPatients, $start, $length);
            
            // Format data for DataTables
            $data = [];
            foreach ($paginatedPatients as $patient) {
                // Use age from stats if available, otherwise calculate
                $age = 'N/A';
                if (isset($patient['age']) && $patient['age']) {
                    $age = $patient['age'] . ' years';
                } elseif (!empty($patient['date_of_birth'])) {
                    try {
                        $dob = new \DateTime($patient['date_of_birth']);
                        $now = new \DateTime();
                        $age = $now->diff($dob)->y . ' years';
                    } catch (\Exception $e) {
                        $age = 'N/A';
                    }
                }
                
                $data[] = [
                    'name' => trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')),
                    'contact' => [
                        'phone' => $patient['phone'] ?? 'N/A',
                        'email' => $patient['email'] ?? 'N/A'
                    ],
                    'patient_id' => $patient['patient_id'] ?? 'N/A',
                    'age' => $age,
                    'last_visit' => isset($patient['last_visit']) && $patient['last_visit'] ? date('M d, Y', strtotime($patient['last_visit'])) : 'Never',
                    'created_at' => isset($patient['created_at']) && $patient['created_at'] ? date('M d, Y', strtotime($patient['created_at'])) : 'Unknown',
                    'id' => $patient['id'] ?? 0
                ];
            }
            
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            // Log the error for debugging
            log_message('error', 'Odontogram getPatientsData error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function index($patientId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
        
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $data = [
            'title' => 'Odontogram - ' . $patient['first_name'] . ' ' . $patient['last_name'],
            'patient' => $patient,
            'odontogram' => $this->odontogramModel->getOdontogramByPatient($patientId),
            'condition_types' => $this->odontogramModel->getConditionTypes(),
            'tooth_positions' => $this->odontogramModel->getToothPositions(),
            'stats' => $this->odontogramModel->getOdontogramStats($patientId)
        ];

        return $this->view('odontogram/index', $data);
    }

    public function updateTooth()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $patientId = $this->request->getPost('patient_id');
        $examinationId = $this->request->getPost('examination_id');
        
        // Verify patient ownership
        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
        if (!$patient) {
             return $this->response->setStatusCode(403)->setJSON(['error' => 'Invalid patient']);
        }

        $toothNumber = $this->request->getPost('tooth_number');
        $conditionType = $this->request->getPost('condition_type');
        $conditionDescription = $this->request->getPost('condition_description');
        $treatmentNotes = $this->request->getPost('treatment_notes');
        $treatmentStatus = $this->request->getPost('treatment_status');

        $conditionData = [
            'examination_id' => $examinationId,
            'condition_type' => $conditionType,
            'condition_description' => $conditionDescription,
            'treatment_notes' => $treatmentNotes,
            'treatment_date' => date('Y-m-d'),
            'treatment_status' => $treatmentStatus,
            'created_by' => 1
        ];

        if ($this->odontogramModel->updateToothCondition($patientId, $toothNumber, $conditionData)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Tooth condition updated successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to update tooth condition']);
        }
    }

    public function getToothCondition()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $patientId = $this->request->getGet('patient_id');
        
        // Verify patient ownership
        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
        if (!$patient) {
             return $this->response->setStatusCode(403)->setJSON(['error' => 'Invalid patient']);
        }

        $toothNumber = $this->request->getGet('tooth_number');

        $condition = $this->odontogramModel->getToothCondition($patientId, $toothNumber);
        
        return $this->response->setJSON($condition);
    }

    public function getOdontogramData($patientId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        // Verify patient ownership
        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
        if (!$patient) {
             return $this->response->setStatusCode(403)->setJSON(['error' => 'Invalid patient']);
        }

        $odontogram = $this->odontogramModel->getOdontogramByPatient($patientId);
        $stats = $this->odontogramModel->getOdontogramStats($patientId);
        
        $data = [
            'odontogram' => $odontogram,
            'stats' => $stats
        ];

        return $this->response->setJSON($data);
    }

    public function resetTooth()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $patientId = $this->request->getPost('patient_id');
        
        // Verify patient ownership
        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
        if (!$patient) {
             return $this->response->setStatusCode(403)->setJSON(['error' => 'Invalid patient']);
        }

        $toothNumber = $this->request->getPost('tooth_number');

        $condition = $this->odontogramModel->getToothCondition($patientId, $toothNumber);
        
        if ($condition) {
            if ($this->odontogramModel->delete($condition['id'])) {
                return $this->response->setJSON(['success' => true, 'message' => 'Tooth condition reset successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to reset tooth condition']);
            }
        } else {
            return $this->response->setJSON(['success' => true, 'message' => 'Tooth is already healthy']);
        }
    }

    public function export($patientId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
             return redirect()->to('/clinic/select');
        }

        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
        
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $odontogram = $this->odontogramModel->getOdontogramByPatient($patientId);
        $stats = $this->odontogramModel->getOdontogramStats($patientId);

        $data = [
            'patient' => $patient,
            'odontogram' => $odontogram,
            'stats' => $stats,
            'condition_types' => $this->odontogramModel->getConditionTypes()
        ];

        return $this->view('odontogram/export', $data);
    }

    public function print($patientId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
             return redirect()->to('/clinic/select');
        }

        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
        
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $odontogram = $this->odontogramModel->getOdontogramByPatient($patientId);
        $stats = $this->odontogramModel->getOdontogramStats($patientId);

        $data = [
            'patient' => $patient,
            'odontogram' => $odontogram,
            'stats' => $stats,
            'condition_types' => $this->odontogramModel->getConditionTypes(),
            'clinic' => settings()->getClinicInfo()
        ];

        return $this->view('odontogram/print', $data);
    }

    public function pdf($patientId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
             return redirect()->to('/clinic/select');
        }

        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
        
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $odontogram = $this->odontogramModel->getOdontogramByPatient($patientId);
        $stats = $this->odontogramModel->getOdontogramStats($patientId);

        $data = [
            'patient' => $patient,
            'odontogram' => $odontogram,
            'stats' => $stats,
            'condition_types' => $this->odontogramModel->getConditionTypes()
        ];

        return $this->view('odontogram/pdf', $data);
    }

    public function downloadPdf($patientId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
             return redirect()->to('/clinic/select');
        }

        $patient = $this->patientModel->where('clinic_id', $clinicId)->find($patientId);
        
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $odontogram = $this->odontogramModel->getOdontogramByPatient($patientId);
        $stats = $this->odontogramModel->getOdontogramStats($patientId);

        $data = [
            'patient' => $patient,
            'odontogram' => $odontogram,
            'stats' => $stats,
            'condition_types' => $this->odontogramModel->getConditionTypes()
        ];

        // Generate HTML content for PDF
        $html = view('odontogram/pdf', $data);
        
        // Set headers for PDF download with proper MIME type
        $filename = 'odontogram_' . $patient['patient_id'] . '_' . date('Y-m-d') . '.pdf';
        
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setBody($html);
    }
}
