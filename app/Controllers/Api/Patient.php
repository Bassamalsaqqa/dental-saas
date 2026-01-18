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
}
