<?php

namespace App\Models;

class PlanRequestModel extends TenantAwareModel
{
    protected $table = 'plan_requests';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'clinic_id',
        'current_plan_id',
        'requested_plan_id',
        'requested_duration',
        'status',
        'requested_by',
        'reviewed_by',
        'reviewed_at',
        'decision_reason',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
