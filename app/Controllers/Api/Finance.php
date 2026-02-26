<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FinanceModel;
use App\Models\PatientModel;
use App\Models\ExaminationModel;

class Finance extends ResourceController
{
    protected $modelName = FinanceModel::class;
    protected $format    = 'json';

    private function clinicIdOrForbidden()
    {
        $clinicId = \App\Libraries\ApiRuntimeContext::getClinicId();
        if (!$clinicId) {
            return null;
        }
        return (int) $clinicId;
    }

    private function validateOwnedReferences(int $clinicId, array $payload)
    {
        $errors = [];

        $patientId = isset($payload['patient_id']) && $payload['patient_id'] !== '' ? (int) $payload['patient_id'] : null;
        $examinationId = isset($payload['examination_id']) && $payload['examination_id'] !== '' ? (int) $payload['examination_id'] : null;
        $transactionType = (string) ($payload['transaction_type'] ?? '');

        if ($transactionType !== 'expense') {
            if (!$patientId || $patientId <= 0) {
                $errors['patient_id'] = 'Patient is required.';
            } else {
                $patient = (new PatientModel())->where('clinic_id', $clinicId)->find($patientId);
                if (!$patient) {
                    $errors['patient_id'] = 'Patient does not belong to the active clinic.';
                }
            }
        }

        if ($examinationId && $examinationId > 0) {
            $exam = (new ExaminationModel())->where('clinic_id', $clinicId)->find($examinationId);
            if (!$exam) {
                $errors['examination_id'] = 'Examination does not belong to the active clinic.';
            }
        }

        return $errors;
    }

    public function index()
    {
        $clinicId = \App\Libraries\ApiRuntimeContext::getClinicId();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        // Minimal financial data list (scoped)
        $data = $this->model->select('id, patient_id, examination_id, transaction_id, transaction_type, amount, total_amount, currency, payment_method, payment_status, amount_paid, balance_due, due_date, paid_date, expense_category, counterparty_name, created_at')
                            ->where('clinic_id', $clinicId)
                            ->orderBy('created_at', 'DESC')
                            ->findAll(100);
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $clinicId = $this->clinicIdOrForbidden();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $data = $this->model->select('id, patient_id, examination_id, transaction_id, transaction_type, amount, total_amount, currency, payment_method, payment_status, amount_paid, balance_due, due_date, paid_date, expense_category, counterparty_name, description, notes, created_at')
                            ->where('clinic_id', $clinicId)
                            ->find($id);
        
        if (! $data) {
            return $this->failNotFound('Invoice not found');
        }

        return $this->respond($data);
    }

    public function create()
    {
        $clinicId = $this->clinicIdOrForbidden();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $payload = (array) $this->request->getJSON(true);
        if (empty($payload)) {
            $payload = (array) $this->request->getPost();
        }

        $payload['clinic_id'] = $clinicId;
        $payload['transaction_type'] = $payload['transaction_type'] ?? 'payment';
        $payload['service_type'] = $payload['service_type'] ?? 'other';
        $payload['payment_method'] = $payload['payment_method'] ?? 'cash';
        $payload['payment_status'] = $payload['payment_status'] ?? (($payload['transaction_type'] === 'invoice') ? 'pending' : 'paid');
        if (!isset($payload['amount_original']) && isset($payload['amount'])) {
            $payload['amount_original'] = $payload['amount'];
        }
        if (!isset($payload['exchange_rate'])) {
            $payload['exchange_rate'] = 1;
        }
        if (!isset($payload['amount_paid'])) {
            $payload['amount_paid'] = 0;
        }
        if (!isset($payload['balance_due'])) {
            $payload['balance_due'] = 0;
        }

        $ownershipErrors = $this->validateOwnedReferences($clinicId, $payload);
        if ($ownershipErrors !== []) {
            return $this->failValidationErrors($ownershipErrors);
        }

        if (!$this->model->insert($payload)) {
            return $this->failValidationErrors($this->model->errors());
        }

        $id = (int) $this->model->getInsertID();
        $row = $this->model->where('clinic_id', $clinicId)->find($id);
        return $this->respondCreated($row);
    }

    public function update($id = null)
    {
        $clinicId = $this->clinicIdOrForbidden();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $existing = $this->model->where('clinic_id', $clinicId)->find($id);
        if (!$existing) {
            return $this->failNotFound('Invoice not found');
        }

        $payload = (array) $this->request->getJSON(true);
        if (empty($payload)) {
            $payload = (array) $this->request->getRawInput();
        }

        unset($payload['clinic_id']);
        if (empty($payload)) {
            return $this->failValidationErrors(['payload' => 'No data provided']);
        }

        $candidate = array_merge($existing, $payload);
        $ownershipErrors = $this->validateOwnedReferences($clinicId, $candidate);
        if ($ownershipErrors !== []) {
            return $this->failValidationErrors($ownershipErrors);
        }

        if (!$this->model->update($id, $payload)) {
            return $this->failValidationErrors($this->model->errors());
        }

        $row = $this->model->where('clinic_id', $clinicId)->find($id);
        return $this->respond($row);
    }

    public function delete($id = null)
    {
        $clinicId = $this->clinicIdOrForbidden();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $existing = $this->model->where('clinic_id', $clinicId)->find($id);
        if (!$existing) {
            return $this->failNotFound('Invoice not found');
        }

        if (!$this->model->where('clinic_id', $clinicId)->delete($id)) {
            return $this->failServerError('Failed to delete finance entry');
        }

        return $this->respondDeleted(['id' => (int) $id]);
    }
}
