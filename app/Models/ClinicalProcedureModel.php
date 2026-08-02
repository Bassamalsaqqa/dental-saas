<?php

namespace App\Models;

class ClinicalProcedureModel extends TenantAwareModel
{
    protected $table = 'clinical_procedures';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'patient_id',
        'examination_id',
        'condition_odontogram_id',
        'treatment_id',
        'appointment_id',
        'scheduled_appointment_id',
        'contract_id',
        'contract_item_id',
        'clinical_charge_line_id',
        'provider_type',
        'provider_user_id',
        'provider_name_snapshot',
        'procedure_code',
        'procedure_name',
        'tooth_numbers',
        'surfaces',
        'status',
        'status_reason',
        'proposed_at',
        'presented_at',
        'accepted_at',
        'rejected_at',
        'scheduled_at',
        'started_at',
        'completed_at',
        'referred_at',
        'cancelled_at',
        'created_by',
        'updated_by',
        'status_changed_by',
        'status_changed_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $beforeInsert = ['setClinicId', 'initializeLifecycle'];

    protected $validationRules = [
        'patient_id' => 'required|integer',
        'examination_id' => 'permit_empty|integer',
        'condition_odontogram_id' => 'permit_empty|integer',
        'treatment_id' => 'permit_empty|integer',
        'appointment_id' => 'permit_empty|integer',
        'scheduled_appointment_id' => 'permit_empty|integer',
        'contract_id' => 'permit_empty|integer',
        'contract_item_id' => 'permit_empty|integer',
        'clinical_charge_line_id' => 'permit_empty|integer',
        'provider_type' => 'required|in_list[user,guest,none]',
        'provider_user_id' => 'permit_empty|integer',
        'provider_name_snapshot' => 'permit_empty|max_length[150]',
        'procedure_code' => 'permit_empty|max_length[80]',
        'procedure_name' => 'required|max_length[255]',
        'tooth_numbers' => 'permit_empty|max_length[120]',
        'surfaces' => 'permit_empty|max_length[80]',
        'status' => 'required|in_list[proposed,presented,accepted,rejected,scheduled,in_progress,completed,referred,cancelled]',
        'status_reason' => 'permit_empty|max_length[500]',
    ];

    public function getByPatient(int $clinicId, int $patientId): array
    {
        return $this->where('clinic_id', $clinicId)
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    protected function initializeLifecycle(array $data): array
    {
        if (!isset($data['data']) || !is_array($data['data'])) {
            return $data;
        }

        $status = (string) ($data['data']['status'] ?? 'proposed');
        $now = date('Y-m-d H:i:s');
        $data['data']['status'] = $status;
        $data['data']['status_changed_at'] ??= $now;

        $timestampField = [
            'proposed' => 'proposed_at',
            'presented' => 'presented_at',
            'accepted' => 'accepted_at',
            'rejected' => 'rejected_at',
            'scheduled' => 'scheduled_at',
            'in_progress' => 'started_at',
            'completed' => 'completed_at',
            'referred' => 'referred_at',
            'cancelled' => 'cancelled_at',
        ][$status] ?? null;

        if ($timestampField !== null) {
            $data['data'][$timestampField] ??= $now;
        }

        return $data;
    }
}
