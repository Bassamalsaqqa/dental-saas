<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\PatientModel;
use App\Models\UserModel;
use App\Models\ExaminationModel;
use App\Models\TreatmentModel;
use App\Models\InventoryModel;
use App\Models\RoleModel;

class Search extends BaseController
{
    protected $patientModel;
    protected $userModel;
    protected $examinationModel;
    protected $treatmentModel;
    protected $inventoryModel;
    protected $roleModel;

    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->userModel = new UserModel();
        $this->examinationModel = new ExaminationModel();
        $this->treatmentModel = new TreatmentModel();
        $this->inventoryModel = new InventoryModel();
        $this->roleModel = new RoleModel();
    }

    /**
     * Search patients for select dropdowns
     */
    public function patients()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $query = $this->request->getGet('q');
        $page = intval($this->request->getGet('page') ?? 1);
        $limit = intval($this->request->getGet('limit') ?? 20);
        $offset = ($page - 1) * $limit;
        
        $patients = $this->patientModel->searchPatientsByClinic($clinicId, $query, $limit, $offset);
        $totalCount = $this->patientModel->countSearchPatientsByClinic($clinicId, $query);

        $results = [];
        foreach ($patients as $patient) {
            $results[] = [
                'id' => $patient['id'],
                'text' => $patient['first_name'] . ' ' . $patient['last_name'] . ' (' . ($patient['phone'] ?? 'No Phone') . ')',
                'value' => $patient['id']
            ];
        }

        return $this->response->setJSON([
            'results' => $results,
            'total' => $totalCount,
            'pagination' => [
                'more' => ($offset + $limit) < $totalCount
            ]
        ]);
    }

    /**
     * Search users for select dropdowns
     */
    public function users()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $query = $this->request->getGet('q');
        $limit = $this->request->getGet('limit') ?? 20;
        $roleId = $this->request->getGet('role_id'); // Changed from role slug to role_id
        
        $builder = $this->userModel
            ->join('clinic_users', 'clinic_users.user_id = users.id')
            ->where('clinic_users.clinic_id', $clinicId)
            ->where('users.active', 1);
        
        if ($roleId) {
            $builder->where('clinic_users.role_id', $roleId);
        }
        
        if (!empty($query) && strlen($query) >= 1) {
            $builder->groupStart()
                ->like('first_name', $query)
                ->orLike('last_name', $query)
                ->orLike('CONCAT(first_name, " ", last_name)', $query)
                ->orLike('email', $query)
                ->orLike('username', $query)
                ->groupEnd();
        }

        $users = $builder
            ->orderBy('first_name', 'ASC')
            ->limit($limit)
            ->find();

        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id' => $user['id'],
                'text' => $user['first_name'] . ' ' . $user['last_name'],
                'value' => $user['id']
            ];
        }

        return $this->response->setJSON([
            'results' => $results,
            'total' => count($results)
        ]);
    }

    /**
     * Search examinations for select dropdowns
     */
    public function examinations()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $query = $this->request->getGet('q');
        $limit = $this->request->getGet('limit') ?? 20;
        $status = $this->request->getGet('status') ?? 'completed';
        
        $examinations = $this->examinationModel->searchExaminationsByClinic($clinicId, $query, $limit, $status);

        $results = [];
        foreach ($examinations as $examination) {
            $results[] = [
                'id' => $examination['id'],
                'text' => $examination['examination_id'],
                'value' => $examination['id']
            ];
        }

        return $this->response->setJSON([
            'results' => $results,
            'total' => count($results)
        ]);
    }

    /**
     * Search treatments for select dropdowns
     */
    public function treatments()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $query = $this->request->getGet('q');
        $limit = $this->request->getGet('limit') ?? 20;
        $status = $this->request->getGet('status');
        
        $treatments = $this->treatmentModel->searchTreatmentsByClinic($clinicId, $query, $limit, $status);

        $results = [];
        foreach ($treatments as $treatment) {
            $results[] = [
                'id' => $treatment['id'],
                'text' => ucfirst(str_replace('_', ' ', $treatment['treatment_type'])),
                'value' => $treatment['id']
            ];
        }

        return $this->response->setJSON([
            'results' => $results,
            'total' => count($results)
        ]);
    }

    /**
     * Search inventory items for select dropdowns
     */
    public function inventory()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $query = $this->request->getGet('q');
        $limit = $this->request->getGet('limit') ?? 20;
        $category = $this->request->getGet('category');
        
        $items = $this->inventoryModel->searchInventoryByClinic($clinicId, $query, $limit, $category);

        $results = [];
        foreach ($items as $item) {
            $results[] = [
                'id' => $item['id'],
                'text' => $item['item_name'] . ' (' . $item['category'] . ')', // item_name instead of name
                'value' => $item['id'],
                'name' => $item['item_name'],
                'category' => $item['category'],
                'quantity' => $item['quantity']
            ];
        }

        return $this->response->setJSON([
            'results' => $results,
            'total' => count($results)
        ]);
    }

    /**
     * Get medications list for prescription forms
     */
    public function medications()
    {
        $query = $this->request->getGet('q');
        $limit = $this->request->getGet('limit') ?? 50;
        
        // This would typically come from a medications table
        // For now, we'll use a predefined list
        $medications = [
            'amoxicillin' => 'Amoxicillin 500mg',
            'ibuprofen' => 'Ibuprofen 400mg',
            'paracetamol' => 'Paracetamol 500mg',
            'diclofenac' => 'Diclofenac 50mg',
            'metronidazole' => 'Metronidazole 400mg',
            'clindamycin' => 'Clindamycin 300mg',
            'cefuroxime' => 'Cefuroxime 250mg',
            'azithromycin' => 'Azithromycin 250mg',
            'doxycycline' => 'Doxycycline 100mg',
            'fluconazole' => 'Fluconazole 150mg',
            'prednisolone' => 'Prednisolone 5mg',
            'loratadine' => 'Loratadine 10mg',
            'omeprazole' => 'Omeprazole 20mg',
            'ranitidine' => 'Ranitidine 150mg',
            'simvastatin' => 'Simvastatin 20mg',
            'atenolol' => 'Atenolol 50mg',
            'amlodipine' => 'Amlodipine 5mg',
            'metformin' => 'Metformin 500mg',
            'insulin' => 'Insulin Regular',
            'warfarin' => 'Warfarin 5mg'
        ];

        $results = [];
        foreach ($medications as $key => $medication) {
            if (empty($query) || stripos($medication, $query) !== false) {
                $results[] = [
                    'id' => $key,
                    'text' => $medication,
                    'value' => $key,
                    'name' => $medication
                ];
            }
        }

        // Limit results
        $results = array_slice($results, 0, $limit);

        return $this->response->setJSON([
            'results' => $results,
            'total' => count($results)
        ]);
    }

    /**
     * Get treatment types for treatment forms
     */
    public function treatmentTypes()
    {
        $query = $this->request->getGet('q');
        
        $treatmentTypes = [
            'cleaning' => 'Teeth Cleaning',
            'extraction' => 'Tooth Extraction',
            'filling' => 'Dental Filling',
            'crown' => 'Dental Crown',
            'root_canal' => 'Root Canal Treatment',
            'orthodontic' => 'Orthodontic Treatment',
            'implant' => 'Dental Implant',
            'bridge' => 'Dental Bridge',
            'veneers' => 'Dental Veneers',
            'whitening' => 'Teeth Whitening',
            'gum_treatment' => 'Gum Treatment',
            'oral_surgery' => 'Oral Surgery',
            'other' => 'Other Treatment'
        ];

        $results = [];
        foreach ($treatmentTypes as $key => $type) {
            if (empty($query) || stripos($type, $query) !== false) {
                $results[] = [
                    'id' => $key,
                    'text' => $type,
                    'value' => $key,
                    'name' => $type
                ];
            }
        }

        return $this->response->setJSON([
            'results' => $results,
            'total' => count($results)
        ]);
    }

    /**
     * Get departments for user forms
     */
    public function departments()
    {
        $query = $this->request->getGet('q');
        
        $departments = [
            'dental' => 'General Dentistry',
            'orthodontics' => 'Orthodontics',
            'oral_surgery' => 'Oral Surgery',
            'periodontics' => 'Periodontics',
            'pediatrics' => 'Pediatric Dentistry',
            'endodontics' => 'Endodontics',
            'prosthodontics' => 'Prosthodontics',
            'general' => 'General Practice',
            'emergency' => 'Emergency Care',
            'preventive' => 'Preventive Care'
        ];

        $results = [];
        foreach ($departments as $key => $department) {
            if (empty($query) || stripos($department, $query) !== false) {
                $results[] = [
                    'id' => $key,
                    'text' => $department,
                    'value' => $key,
                    'name' => $department
                ];
            }
        }

        return $this->response->setJSON([
            'results' => $results,
            'total' => count($results)
        ]);
    }

    /**
     * Get roles for user forms
     */
    public function roles()
    {
        $query = $this->request->getGet('q');
        $limit = $this->request->getGet('limit') ?? 50;
        
        // Get active roles from database
        $builder = $this->roleModel->where('is_active', 1);
        
        if (!empty($query) && strlen($query) >= 1) {
            $builder->groupStart()
                    ->like('name', $query)
                    ->orLike('description', $query)
                    ->groupEnd();
        }
        
        $roles = $builder->orderBy('name', 'ASC')
                        ->limit($limit)
                        ->find();

        $results = [];
        foreach ($roles as $role) {
            $results[] = [
                'id' => $role['id'],
                'text' => $role['name'],
                'value' => $role['id'],
                'name' => $role['name'],
                'description' => $role['description']
            ];
        }

        return $this->response->setJSON([
            'results' => $results,
            'total' => count($results)
        ]);
    }
}
