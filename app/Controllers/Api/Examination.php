<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ExaminationModel;
use App\Services\ExaminationNoteGovernanceService;
use DomainException;

class Examination extends ResourceController
{
    protected $modelName = ExaminationModel::class;
    protected $format    = 'json';

    public function index()
    {
        $clinicId = \App\Libraries\ApiRuntimeContext::getClinicId();
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
        $clinicId = \App\Libraries\ApiRuntimeContext::getClinicId();
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

    public function update($id = null)
    {
        $clinicId = \App\Libraries\ApiRuntimeContext::getClinicId();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $examination = $this->model->where('clinic_id', $clinicId)->find($id);
        if (!$examination) {
            return $this->failNotFound('Examination not found');
        }
        if ((new ExaminationNoteGovernanceService())->isSigned((int) $clinicId, (int) $id)) {
            return $this->fail('signed_clinical_note_immutable', 409);
        }

        try {
            return parent::update($id);
        } catch (DomainException $e) {
            return $this->fail('signed_clinical_note_immutable', 409);
        }
    }

    public function delete($id = null)
    {
        $clinicId = \App\Libraries\ApiRuntimeContext::getClinicId();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $examination = $this->model->where('clinic_id', $clinicId)->find($id);
        if (!$examination) {
            return $this->failNotFound('Examination not found');
        }
        if ((new ExaminationNoteGovernanceService())->isSigned((int) $clinicId, (int) $id)) {
            return $this->fail('signed_clinical_note_immutable', 409);
        }

        try {
            return parent::delete($id);
        } catch (DomainException $e) {
            return $this->fail('signed_clinical_note_immutable', 409);
        }
    }
}
