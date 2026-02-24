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
}
