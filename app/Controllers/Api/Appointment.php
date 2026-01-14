<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\AppointmentModel;

class Appointment extends ResourceController
{
    protected $modelName = AppointmentModel::class;
    protected $format    = 'json';

    public function index()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $data = $this->model->select('id, patient_id, appointment_date, start_time, status')
                            ->where('clinic_id', $clinicId)
                            ->where('appointment_date >=', date('Y-m-d'))
                            ->orderBy('appointment_date', 'ASC')
                            ->findAll(100);
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $data = $this->model->select('id, appointment_date, start_time, end_time, status, type')
                            ->where('clinic_id', $clinicId)
                            ->find($id);
        
        if (! $data) {
            return $this->failNotFound('Appointment not found');
        }

        return $this->respond($data);
    }
}
