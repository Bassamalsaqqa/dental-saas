<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FinanceModel;

class Finance extends ResourceController
{
    protected $modelName = FinanceModel::class;
    protected $format    = 'json';

    public function index()
    {
        // Minimal financial data list
        $data = $this->model->select('id, patient_id, invoice_number, amount, status, created_at')
                            ->orderBy('created_at', 'DESC')
                            ->findAll(100);
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $data = $this->model->select('id, invoice_number, amount, status, due_date, created_at')
                            ->find($id);
        
        if (! $data) {
            return $this->failNotFound('Invoice not found');
        }

        return $this->respond($data);
    }
}
