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
        $data = $this->model->select('id, patient_id, appointment_date, start_time, status')
                            ->where('appointment_date >=', date('Y-m-d'))
                            ->orderBy('appointment_date', 'ASC')
                            ->findAll(100);
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $data = $this->model->select('id, appointment_date, start_time, end_time, status, type')
                            ->find($id);
        
        if (! $data) {
            return $this->failNotFound('Appointment not found');
        }

        return $this->respond($data);
    }
}
