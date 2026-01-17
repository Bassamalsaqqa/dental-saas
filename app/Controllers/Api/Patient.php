<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PatientModel;

class Patient extends ResourceController
{
    protected $modelName = PatientModel::class;
    protected $format    = 'json';

    public function index()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        // Explicit allowlist of fields with tenant scoping
        $data = $this->model->select('id, patient_id, first_name, last_name, status')
                            ->where('clinic_id', $clinicId)
                            ->orderBy('created_at', 'DESC')
                            ->findAll(100); // Limit to 100 for safety
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $data = $this->model->select('id, first_name, last_name, status, created_at')
                            ->where('clinic_id', $clinicId)
                            ->find($id);
        
        if (! $data) {
            return $this->failNotFound('Patient not found');
        }

        return $this->respond($data);
    }

    public function create()
    {
        helper('api');
        $clinicId = session()->get('active_clinic_id');
        
        $planGuard = new \App\Services\PlanGuard();
        try {
            $planGuard->assertQuota($clinicId, 'patients_active_max', 1);
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            return api_error(429, 'plan_quota_exceeded', 'Patient quota reached for this plan.');
        }

        $data = $this->request->getPost();
        
        if ($this->model->insert($data)) {
            $id = $this->model->getInsertID();
            return $this->respondCreated(['id' => $id, 'message' => 'Patient created successfully']);
        }

        return $this->fail($this->model->errors());
    }
}
