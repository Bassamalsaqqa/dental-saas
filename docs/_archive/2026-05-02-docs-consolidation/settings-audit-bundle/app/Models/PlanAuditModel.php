<?php

namespace App\Models;

use App\Models\TenantAwareModel;

class PlanAuditModel extends TenantAwareModel
{
    protected $table = 'plan_audits';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'clinic_id',
        'actor_user_id',
        'action_key',
        'reason_code',
        'meta_json',
        'created_at'
    ];
    protected $useTimestamps = false; // We set created_at manually or rely on DB defaults if configured, but CI4 Model expects it if true.
    // Migration said created_at DATETIME.
    // Let's use timestamps for convenience.
    protected $createdField = 'created_at';
    protected $updatedField = ''; 

    protected $beforeInsert = ['setClinicId', 'setCreatedAt'];

    protected function setCreatedAt(array $data)
    {
        if (!isset($data['data']['created_at'])) {
            $data['data']['created_at'] = date('Y-m-d H:i:s');
        }
        return $data;
    }
}
