<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\Controller;
use App\Models\PatientModel;
use App\Models\UserModel;
use App\Models\ExaminationModel;
use App\Models\TreatmentModel;
use App\Models\InventoryModel;
use App\Models\RoleModel;
use App\Libraries\TreatmentCatalog;

class Search extends Controller
{
    protected $patientModel;
    protected $userModel;
    protected $examinationModel;
    protected $treatmentModel;
    protected $inventoryModel;
    protected $roleModel;

    public function __construct()
    {
        helper('api');
        $this->patientModel = new PatientModel();
        $this->userModel = new UserModel();
        $this->examinationModel = new ExaminationModel();
        $this->treatmentModel = new TreatmentModel();
        $this->inventoryModel = new InventoryModel();
        $this->roleModel = new RoleModel();
    }

    protected function getClinicIdOrError()
    {
        $clinicId = \App\Libraries\ApiRuntimeContext::getClinicId();
        if (!$clinicId) {
            return [null, api_error(403, 'tenant_context_forbidden', 'Tenant context required.')];
        }

        return [$clinicId, null];
    }

    public function patients()
    {
        [$clinicId, $error] = $this->getClinicIdOrError();
        if ($error) {
            return $error;
        }

        $query = $this->request->getGet('q');
        $page = (int)($this->request->getGet('page') ?? 1);
        $limit = (int)($this->request->getGet('limit') ?? 20);
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

    public function users()
    {
        [$clinicId, $error] = $this->getClinicIdOrError();
        if ($error) {
            return $error;
        }

        $query = $this->request->getGet('q');
        $limit = (int)($this->request->getGet('limit') ?? 20);
        $roleId = $this->request->getGet('role_id');

        $builder = $this->userModel
            ->select('users.*')
            ->join('clinic_users', 'clinic_users.user_id = users.id')
            ->where('clinic_users.clinic_id', $clinicId)
            ->where('clinic_users.status', 'active')
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

    public function examinations()
    {
        [$clinicId, $error] = $this->getClinicIdOrError();
        if ($error) {
            return $error;
        }

        $query = $this->request->getGet('q');
        $limit = (int)($this->request->getGet('limit') ?? 20);
        $status = $this->request->getGet('status') ?? 'completed';
        if (is_string($status)) {
            $status = trim($status);
            if ($status === '' || strtolower($status) === 'all') {
                $status = null;
            }
        }
        $patientId = $this->request->getGet('patient_id');
        $patientId = is_numeric($patientId) ? (int)$patientId : null;

        $examinations = $this->examinationModel->searchExaminationsByClinic($clinicId, $query, $limit, $status, $patientId);

        $results = [];
        foreach ($examinations as $examination) {
            $date = !empty($examination['examination_date']) ? date('M d, Y', strtotime($examination['examination_date'])) : 'No date';
            $type = !empty($examination['examination_type']) ? ucfirst(str_replace('_', ' ', $examination['examination_type'])) : 'Unknown';
            $patientName = trim(($examination['first_name'] ?? '') . ' ' . ($examination['last_name'] ?? ''));
            $examId = $examination['examination_id'] ?? $examination['id'] ?? '';
            $parts = [];
            if ($examId !== '') {
                $parts[] = $examId;
            }
            $parts[] = $date;
            $parts[] = $type;
            if ($patientName !== '') {
                $parts[] = $patientName;
            }
            $text = implode(' • ', $parts);
            $results[] = [
                'id' => $examination['id'],
                'text' => $text,
                'value' => $examination['id']
            ];
        }

        return $this->response->setJSON([
            'results' => $results,
            'total' => count($results)
        ]);
    }

    public function treatments()
    {
        [$clinicId, $error] = $this->getClinicIdOrError();
        if ($error) {
            return $error;
        }

        $query = $this->request->getGet('q');
        $limit = (int)($this->request->getGet('limit') ?? 20);
        $status = $this->request->getGet('status');

        $treatments = $this->treatmentModel->getTreatmentsByClinic($clinicId, $limit, 0, $query, $status);

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

    public function inventory()
    {
        [$clinicId, $error] = $this->getClinicIdOrError();
        if ($error) {
            return $error;
        }

        $query = $this->request->getGet('q');
        $limit = (int)($this->request->getGet('limit') ?? 20);
        $category = $this->request->getGet('category');

        $items = $this->inventoryModel->searchInventoryByClinic($clinicId, $query, $limit, $category);

        $results = [];
        foreach ($items as $item) {
            $results[] = [
                'id' => $item['id'],
                'text' => $item['item_name'] . ' (' . $item['category'] . ')',
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

    public function medications()
    {
        [$clinicId, $error] = $this->getClinicIdOrError();
        if ($error) {
            return $error;
        }

        $query = $this->request->getGet('q');
        $limit = (int)($this->request->getGet('limit') ?? 50);

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

        $results = array_slice($results, 0, $limit);

        return $this->response->setJSON([
            'results' => $results,
            'total' => count($results)
        ]);
    }

    public function treatmentTypes()
    {
        [$clinicId, $error] = $this->getClinicIdOrError();
        if ($error) {
            return $error;
        }

        $query = $this->request->getGet('q');

        $treatmentTypes = TreatmentCatalog::forSelection();

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

    public function departments()
    {
        [$clinicId, $error] = $this->getClinicIdOrError();
        if ($error) {
            return $error;
        }

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

    public function roles()
    {
        [$clinicId, $error] = $this->getClinicIdOrError();
        if ($error) {
            return $error;
        }

        $query = $this->request->getGet('q');
        $limit = (int)($this->request->getGet('limit') ?? 50);
        $roles = $this->roleModel->getAssignableRolesForClinic($clinicId, true);
        if (!empty($query) && strlen($query) >= 1) {
            $queryLower = mb_strtolower($query);
            $roles = array_values(array_filter($roles, static function ($role) use ($queryLower) {
                $name = mb_strtolower((string) ($role['name'] ?? ''));
                $description = mb_strtolower((string) ($role['description'] ?? ''));
                return str_contains($name, $queryLower) || str_contains($description, $queryLower);
            }));
        }
        $roles = array_slice($roles, 0, $limit);

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
