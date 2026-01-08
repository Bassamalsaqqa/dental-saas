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
        // Explicit allowlist of fields
        $data = $this->model->select('id, patient_id, first_name, last_name, status')
                            ->orderBy('created_at', 'DESC')
                            ->findAll(100); // Limit to 100 for safety
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $data = $this->model->select('id, first_name, last_name, status, created_at')
                            ->find($id);
        
        if (! $data) {
            return $this->failNotFound('Patient not found');
        }

        return $this->respond($data);
    }
}
