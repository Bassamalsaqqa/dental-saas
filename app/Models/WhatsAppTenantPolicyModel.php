<?php

namespace App\Models;

class WhatsAppTenantPolicyModel extends TenantAwareModel
{
    protected $table = 'whatsapp_tenant_policies';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'daily_limit',
        'monthly_limit',
        'per_patient_daily_limit',
        'per_hour_limit',
        'suspended',
        'suspended_reason',
        'trigger_toggles_json',
        'reminder_lead_time_minutes',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['setClinicId'];

    public function getByClinicForControlPlane(int $clinicId): ?array
    {
        return $this->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->first();
    }

    public function upsertForControlPlane(int $clinicId, array $data)
    {
        $existing = $this->getByClinicForControlPlane($clinicId);

        $payload = array_intersect_key($data, array_flip($this->allowedFields));
        $payload['clinic_id'] = $clinicId;

        if ($existing) {
            return $this->withoutTenantScope()->update($existing['id'], $payload);
        }

        return $this->withoutTenantScope()->insert($payload);
    }

    public function getControlPlanePoliciesWithClinic(int $limit = 100): array
    {
        $limit = max(1, min($limit, 500));

        return $this->withoutTenantScope()
            ->select('whatsapp_tenant_policies.*, clinics.name AS clinic_name')
            ->join('clinics', 'clinics.id = whatsapp_tenant_policies.clinic_id', 'left')
            ->orderBy('whatsapp_tenant_policies.updated_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
