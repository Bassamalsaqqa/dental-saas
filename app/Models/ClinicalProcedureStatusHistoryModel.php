<?php

namespace App\Models;

class ClinicalProcedureStatusHistoryModel extends TenantAwareModel
{
    protected $table = 'clinical_procedure_status_history';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'procedure_id',
        'from_status',
        'to_status',
        'reason',
        'changed_by',
        'created_at',
    ];

    protected $useTimestamps = false;
    protected $beforeInsert = ['setClinicId', 'setCreatedAt'];
    protected $validationRules = [
        'procedure_id' => 'required|integer',
        'from_status' => 'permit_empty|max_length[30]',
        'to_status' => 'required|max_length[30]',
        'reason' => 'permit_empty|max_length[500]',
        'changed_by' => 'required|integer',
    ];

    protected function setCreatedAt(array $data): array
    {
        if (isset($data['data']) && is_array($data['data'])) {
            $data['data']['created_at'] ??= date('Y-m-d H:i:s');
        }

        return $data;
    }
}
