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

    /**
     * Get the current effective subscription for a clinic.
     * Logic: Active-only standing. Schema has no trial_ends_at.
     */
    public function getCurrentSubscription(int $clinicId)
    {
        return $this->where('clinic_id', $clinicId)
                    ->where('status', 'active')
                    ->orderBy("COALESCE(end_at, '9999-12-31')", 'DESC', false)
                    ->orderBy('id', 'DESC')
                    ->first();
    }
}
