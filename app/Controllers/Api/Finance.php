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
            } elseif ($patientId && $patientId > 0 && (int) ($exam['patient_id'] ?? 0) !== $patientId) {
                $errors['examination_id'] = 'Selected examination does not belong to the selected patient.';
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

        // An invoice cannot acquire fabricated settlement history through document creation.
        // Paid expenses and direct-payment creation retain their existing workflow.
        if ($payload['transaction_type'] === 'invoice'
            && (!in_array($payload['payment_status'], ['pending', 'overdue'], true)
                || !is_numeric($payload['amount_paid']) || (float) $payload['amount_paid'] !== 0.0
                || !empty($payload['paid_date']))) {
            return $this->failValidationErrors(['payment_status' => 'Create an unpaid invoice, then use the settlement workflow.']);
        }

        $ownershipErrors = $this->validateOwnedReferences($clinicId, $payload);
        if ($ownershipErrors !== []) {
            return $this->failValidationErrors($ownershipErrors);
        }

        $db = $this->model->db;
        $ownedTransaction = false;
        try {
            $invoice = $payload['transaction_type'] === 'invoice';
            $actorId = (int) (\App\Libraries\ApiRuntimeContext::getUserId() ?? 0);
            if ($actorId <= 0) { return $this->failUnauthorized('Authentication required'); }
            if ((int) $db->transDepth !== 0 || !$db->transStatus() || !$db->transBegin()) {
                throw new \RuntimeException($invoice ? 'Unable to start invoice creation transaction.' : 'Unable to start finance creation transaction.');
            }
            $ownedTransaction = true;
            $payload['created_by'] = $actorId;

            if (!$this->model->insert($payload)) {
                if ($ownedTransaction && (int) $db->transDepth > 0) { $db->transRollback(); $ownedTransaction = false; }
                return $this->failValidationErrors($this->model->errors());
            }
            $id = (int) $this->model->getInsertID();
            $row = $this->model->where('clinic_id', $clinicId)->find($id);
            if ($invoice) {
                // No historical reclassification: this is the just-inserted, unpaid invoice.
                if (!$db->table('finances')->where('clinic_id', $clinicId)->where('id', $id)
                    ->update(['balance_due' => $row['total_amount']])) {
                    throw new \RuntimeException('Unable to persist new invoice balance.');
                }
                $row = $this->model->where('clinic_id', $clinicId)->find($id);
                if (!$db->table('activity_logs')->insert([
                    'clinic_id'=>$clinicId,'user_id'=>$actorId,'action'=>'create','entity_type'=>'finance','entity_id'=>$id,
                    'description'=>'Invoice created through API; issuance is not proven unissued.',
                    'metadata'=>json_encode(['finance_after'=>$row], JSON_THROW_ON_ERROR),'created_at'=>date('Y-m-d H:i:s'),
                ])) {
                    throw new \RuntimeException('Unable to audit invoice creation.');
                }
            }
            if (!$db->transStatus() || !$db->transCommit()) {
                throw new \RuntimeException($invoice ? 'Unable to commit audited invoice creation.' : 'Unable to commit finance creation.');
            }
            $ownedTransaction = false;
            return $this->respondCreated($row);
        } catch (\InvalidArgumentException|\DomainException $e) {
            if ($ownedTransaction && (int) $db->transDepth > 0) { $db->transRollback(); }
            $key = $invoice ? 'invoice' : 'finance';
            return $this->failValidationErrors([$key => $e->getMessage()]);
        } catch (\Throwable $e) {
            if ($ownedTransaction && (int) $db->transDepth > 0) { $db->transRollback(); }
            log_message('error', 'API Finance creation failed: ' . $e->getMessage());
            return $this->failServerError('Failed to create finance entry');
        }
    }

    public function update($id = null)
    {
        $clinicId = $this->clinicIdOrForbidden();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $actorId = (int) (\App\Libraries\ApiRuntimeContext::getUserId() ?? 0);
        if ($actorId <= 0) {
            return $this->failUnauthorized('Authentication required');
        }

        if ($id === null || (!is_int($id) && !is_string($id)) || !preg_match('/^[1-9][0-9]*$/D', (string) $id) || strlen((string) $id) > 10 || (float) $id > 2147483647) {
            return $this->failValidationErrors(['id' => 'Invalid transaction ID.']);
        }
        $id = (int) $id;

        $payload = (array) $this->request->getJSON(true);
        if (empty($payload)) {
            $payload = (array) $this->request->getRawInput();
        }

        if (empty($payload)) {
            return $this->failValidationErrors(['payload' => 'No data provided']);
        }

        try {
            $service = new \App\Services\FinanceApiMutationService($this->model->db, $this->model);
            $row = $service->updateFinance($clinicId, $id, $payload, $actorId);
            return $this->respond($row);
        } catch (\App\Services\FinanceNotFoundException $e) {
            return $this->failNotFound($e->getMessage());
        } catch (\App\Services\FinanceConflictException $e) {
            return $this->fail($e->getMessage(), 409, 'CONFLICT');
        } catch (\App\Services\FinanceValidationException $e) {
            $errors = $e->getErrors();
            return !empty($errors) ? $this->failValidationErrors($errors) : $this->fail($e->getMessage(), 400);
        } catch (\App\Services\FinanceUnauthorizedException $e) {
            return $this->failUnauthorized($e->getMessage());
        } catch (\App\Services\FinanceForbiddenException $e) {
            return $this->failForbidden($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return $this->failValidationErrors(['amount' => $e->getMessage()]);
        } catch (\DomainException $e) {
            return $this->fail($e->getMessage(), 409);
        } catch (\Throwable $e) {
            log_message('error', 'API Finance update error: ' . $e->getMessage());
            return $this->failServerError('Failed to update finance entry');
        }
    }

    public function delete($id = null)
    {
        $clinicId = $this->clinicIdOrForbidden();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $actorId = (int) (\App\Libraries\ApiRuntimeContext::getUserId() ?? 0);
        if ($actorId <= 0) {
            return $this->failUnauthorized('Authentication required');
        }

        if ($id === null || (!is_int($id) && !is_string($id)) || !preg_match('/^[1-9][0-9]*$/D', (string) $id) || strlen((string) $id) > 10 || (float) $id > 2147483647) {
            return $this->failNotFound('Invoice not found');
        }

        try {
            $service = new \App\Services\FinanceMutationService(\Config\Database::connect());
            $service->deleteUnused((int) $clinicId, [(int) $id], $actorId);
            return $this->respondDeleted(['id' => (int) $id]);
        } catch (\DomainException $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'not found')) {
                return $this->failNotFound('Invoice not found');
            }
            return $this->fail($message, 400);
        } catch (\Throwable $e) {
            log_message('error', 'API Finance delete error: ' . $e->getMessage());
            return $this->failServerError('Failed to delete finance entry');
        }
    }
}
