<?php

namespace App\Controllers;

use App\Models\OdontogramModel;
use App\Models\PatientModel;
use App\Models\ExaminationModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Odontogram extends BaseController
{
    protected $odontogramModel;
    protected $patientModel;
    protected $examinationModel;
    protected $storageService;

    public function __construct()
    {
        $this->odontogramModel = new OdontogramModel();
        $this->patientModel = new PatientModel();
        $this->examinationModel = new ExaminationModel();
        $this->storageService = new \App\Services\StorageService();
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
            $orderDir = strtolower($request->getPost('order')[0]['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

            $patients = $this->patientModel->getOdontogramPatients(
                $clinicId,
                $searchValue,
                $orderColumn,
                $orderDir,
                $start,
                $length
            );

            $totalRecords = $this->patientModel->countOdontogramPatients($clinicId);
            $filteredRecords = $this->patientModel->countOdontogramPatients($clinicId, $searchValue);

            // Format data for DataTables
            $data = [];
            foreach ($patients as $patient) {
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

        $currentUser = $this->getCurrentUser();
        $conditionData = [
            'examination_id' => $examinationId,
            'condition_type' => $conditionType,
            'condition_description' => $conditionDescription,
            'treatment_notes' => $treatmentNotes,
            'treatment_date' => date('Y-m-d'),
            'treatment_status' => $treatmentStatus,
            'created_by' => $currentUser ? $currentUser->id : 1
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
            'condition_types' => $this->odontogramModel->getConditionTypes(),
            'clinic' => settings()->getClinicInfo()
        ];

        $html = view('odontogram/export', $data);
        
        // Persist artifact
        $fileName = 'odontogram_export_' . ($patient['patient_id'] ?? $patientId) . '.html';
        $this->storageService->storeExport(
            $html, 
            $fileName, 
            'text/html', 
            $clinicId, 
            'patient', 
            $patientId, 
            'odontogram_export'
        );

        return $this->response
            ->setHeader('Content-Type', 'text/html')
            ->setBody($html);
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

        $html = view('odontogram/print', $data);
        
        // Persist artifact
        $fileName = 'odontogram_print_' . ($patient['patient_id'] ?? $patientId) . '.html';
        $this->persistPrintArtifact($html, $fileName, 'text/html', $clinicId, $patientId);

        return $this->response
            ->setHeader('Content-Type', 'text/html')
            ->setBody($html);
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
            'condition_types' => $this->odontogramModel->getConditionTypes(),
            'clinic' => settings()->getClinicInfo()
        ];

        $html = view('odontogram/pdf', $data);
        
        // Persist artifact
        $fileName = 'odontogram_' . ($patient['patient_id'] ?? $patientId) . '.pdf';
        $this->storageService->storeExport(
            $html, 
            $fileName, 
            'application/pdf', 
            $clinicId, 
            'patient', 
            $patientId, 
            'odontogram_pdf'
        );

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($html);
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
            'condition_types' => $this->odontogramModel->getConditionTypes(),
            'clinic' => settings()->getClinicInfo()
        ];

        // Generate HTML content for PDF
        $html = view('odontogram/pdf', $data);
        
        // Persist artifact
        $filename = 'odontogram_' . $patient['patient_id'] . '_' . date('Y-m-d') . '.pdf';
        $attachment = $this->storageService->storeExport(
            $html, 
            $filename, 
            'application/pdf', 
            $clinicId, 
            'patient', 
            $patientId, 
            'odontogram_pdf_download'
        );
        
        return redirect()->to(base_url('file/download/' . $attachment['id']));
    }

    private function persistPrintArtifact(string $html, string $fileName, string $mimeType, int $clinicId, int $patientId)
    {
        try {
            $this->storageService->storeExport(
                $html,
                $fileName,
                $mimeType,
                $clinicId,
                'patient',
                $patientId,
                'odontogram_print'
            );
        } catch (PageNotFoundException $e) {
            log_message('warning', 'Odontogram print export blocked: ' . $e->getMessage());
        } catch (\Exception $e) {
            log_message('error', 'Odontogram print export failed: ' . $e->getMessage());
        }
    }
}
