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
        $clinicId = \App\Libraries\ApiRuntimeContext::getClinicId();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        // Minimal financial data list (scoped)
        $data = $this->model->select('id, patient_id, transaction_id, transaction_type, amount, total_amount, currency, payment_status, amount_paid, balance_due, due_date, created_at')
                            ->where('clinic_id', $clinicId)
                            ->orderBy('created_at', 'DESC')
                            ->findAll(100);
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $clinicId = \App\Libraries\ApiRuntimeContext::getClinicId();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $data = $this->model->select('id, patient_id, transaction_id, transaction_type, amount, total_amount, currency, payment_status, amount_paid, balance_due, due_date, created_at')
                            ->where('clinic_id', $clinicId)
                            ->find($id);
        
        if (! $data) {
            return $this->failNotFound('Invoice not found');
        }

        return $this->respond($data);
    }
}
