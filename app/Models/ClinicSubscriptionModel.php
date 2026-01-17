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
     * Logic: Latest expiry (end_at or trial_ends_at) then highest ID.
     */
    public function getCurrentSubscription(int $clinicId)
    {
        return $this->where('clinic_id', $clinicId)
                    ->whereIn('status', ['active', 'trial'])
                    ->orderBy("CASE 
                        WHEN status = 'active' THEN COALESCE(end_at, '9999-12-31') 
                        ELSE COALESCE(trial_ends_at, '9999-12-31') 
                    END", 'DESC')
                    ->orderBy('id', 'DESC')
                    ->first();
    }
}
