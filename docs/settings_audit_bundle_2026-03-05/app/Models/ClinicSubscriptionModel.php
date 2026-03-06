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
        'canceled_reason',
        'paused_at',
        'paused_reason',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['setClinicId'];

    /**
     * Get the most recent subscription record for a clinic.
     * Ordering prefers latest end_at (or open-ended), then newest id.
     */
    public function getCurrentSubscription(int $clinicId)
    {
        return $this->where('clinic_id', $clinicId)
                    ->orderBy("COALESCE(end_at, '9999-12-31')", 'DESC', false)
                    ->orderBy('id', 'DESC')
                    ->first();
    }

    /**
     * Get the latest subscription and normalize expired status.
     */
    public function getNormalizedSubscription(int $clinicId): ?array
    {
        $sub = $this->getCurrentSubscription($clinicId);
        if (!$sub) {
            return null;
        }

        return $this->markExpiredIfNeeded($sub);
    }

    /**
     * Mark a subscription as expired if its end_at has passed.
     */
    public function markExpiredIfNeeded(array $subscription): array
    {
        if (empty($subscription['id']) || empty($subscription['status'])) {
            return $subscription;
        }

        if ($subscription['status'] !== 'active') {
            return $subscription;
        }

        if (empty($subscription['end_at'])) {
            return $subscription;
        }

        $endTs = strtotime($subscription['end_at']);
        if ($endTs !== false && $endTs <= time()) {
            $this->update($subscription['id'], [
                'status' => 'expired',
            ]);
            $subscription['status'] = 'expired';
        }

        return $subscription;
    }
}
