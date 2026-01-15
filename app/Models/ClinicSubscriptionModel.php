<?php

namespace App\Models;

use App\Models\TenantAwareModel;

class ClinicSubscriptionModel extends TenantAwareModel
{
    protected $table = 'clinic_subscriptions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'clinic_id',
        'plan_id',
        'status',
        'start_at',
        'end_at',
        'canceled_at',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['setClinicId'];
}
