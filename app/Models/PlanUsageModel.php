<?php

namespace App\Models;

use App\Models\TenantAwareModel;

class PlanUsageModel extends TenantAwareModel
{
    protected $table = 'plan_usage';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'clinic_id',
        'metric_key',
        'metric_value',
        'period_start',
        'period_end',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['setClinicId'];
}
