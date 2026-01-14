<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ExaminationModel;

class Examination extends ResourceController
{
    protected $modelName = ExaminationModel::class;
    protected $format    = 'json';

    public function index()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $data = $this->model->select('id, examination_id, patient_id, status, created_at')
                            ->where('clinic_id', $clinicId)
                            ->orderBy('created_at', 'DESC')
                            ->findAll(100);
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $data = $this->model->select('id, examination_id, status, created_at')
                            ->where('clinic_id', $clinicId)
                            ->find($id);
        
        if (! $data) {
            return $this->failNotFound('Examination not found');
        }

        return $this->respond($data);
    }
}
