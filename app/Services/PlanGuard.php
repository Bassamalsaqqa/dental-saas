<?php

namespace App\Services;

use App\Models\PlanModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\PlanUsageModel;
use App\Models\PlanAuditModel;
use App\Models\PatientModel;

class PlanGuard
{
    protected $planModel;
    protected $subscriptionModel;
    protected $usageModel;
    protected $auditModel;
    protected $patientModel;

    const PLAN_MISSING_SUBSCRIPTION = 'PLAN_MISSING_SUBSCRIPTION';
    const PLAN_SUBSCRIPTION_INACTIVE = 'PLAN_SUBSCRIPTION_INACTIVE';
    const PLAN_FEATURE_DISABLED = 'PLAN_FEATURE_DISABLED';
    const PLAN_QUOTA_EXCEEDED = 'PLAN_QUOTA_EXCEEDED';

    public function __construct()
    {
        $this->planModel = new PlanModel();
        $this->subscriptionModel = new ClinicSubscriptionModel();
        $this->usageModel = new PlanUsageModel();
        $this->auditModel = new PlanAuditModel();
        $this->patientModel = new PatientModel();
    }

    /**
     * Assert that the clinic has an active subscription.
     */
    public function assertSubscriptionActive(int $clinicId)
    {
        $sub = $this->subscriptionModel->where('clinic_id', $clinicId)->first();

        if (!$sub) {
            $this->audit($clinicId, 'subscription_check', self::PLAN_MISSING_SUBSCRIPTION);
            throw new \RuntimeException(self::PLAN_MISSING_SUBSCRIPTION);
        }

        if ($sub['status'] !== 'active') {
            // Check dates? Assuming status is source of truth for now.
            $this->audit($clinicId, 'subscription_check', self::PLAN_SUBSCRIPTION_INACTIVE, ['status' => $sub['status']]);
            throw new \RuntimeException(self::PLAN_SUBSCRIPTION_INACTIVE);
        }
    }

    /**
     * Assert that a feature is enabled in the plan.
     */
    public function assertFeature(int $clinicId, string $featureKey, array $meta = [])
    {
        $this->assertSubscriptionActive($clinicId);
        
        $plan = $this->getClinicPlan($clinicId);
        $features = json_decode($plan['features_json'] ?? '{}', true) ?? [];

        // Check if feature enabled (default to false if missing)
        // Supports nested keys e.g. 'notifications.email.enabled'
        if (!$this->checkNestedKey($features, $featureKey)) {
            $this->audit($clinicId, 'feature_check', self::PLAN_FEATURE_DISABLED, array_merge($meta, ['feature' => $featureKey]));
            throw new \RuntimeException(self::PLAN_FEATURE_DISABLED . ": $featureKey");
        }
    }

    /**
     * Assert quota limits (incrementing usage if passed).
     */
    public function assertQuota(int $clinicId, string $metricKey, int $delta = 1, array $meta = [])
    {
        $this->assertSubscriptionActive($clinicId);
        
        $plan = $this->getClinicPlan($clinicId);
        $limits = json_decode($plan['limits_json'] ?? '{}', true) ?? [];
        
        $limit = $limits[$metricKey] ?? 0; // Default to 0 if not set? Or unlimited? Let's say 0 means none. -1 for unlimited.
        
        if ($limit === -1) {
            return; // Unlimited
        }

        // Special Case: Active Patients Quota (Snapshot, not Monthly)
        if ($metricKey === 'patients') {
             // Use 'patients_active_max' key from limit if mapped, or just 'patients'
             // Requirement says: "Add a plan limit key: patients_active_max"
             // But calls it 'patients' in assertQuota($clinicId,'patients',1).
             // Let's assume metricKey 'patients' maps to 'patients_active_max' limit.
             $limit = $limits['patients_active_max'] ?? $limits['patients'] ?? 0;
             
             if ($limit === -1) return;

             $current = $this->patientModel->countActivePatientsByClinic($clinicId);
             // We are about to add $delta (usually 1)
             if ($current + $delta > $limit) {
                 $this->audit($clinicId, 'quota_check', self::PLAN_QUOTA_EXCEEDED, array_merge($meta, ['metric' => 'patients_active_max', 'limit' => $limit, 'current' => $current]));
                 throw new \RuntimeException(self::PLAN_QUOTA_EXCEEDED);
             }
             return; // No usage increment for snapshot quotas
        }

        // Standard Monthly Quota
        $periodStart = date('Y-m-01');
        
        // Get or Create Usage Row
        $usage = $this->usageModel->where('clinic_id', $clinicId)
                                  ->where('metric_key', $metricKey)
                                  ->where('period_start', $periodStart)
                                  ->first();
                                  
        $currentUsage = $usage ? (int)$usage['metric_value'] : 0;

        if ($currentUsage + $delta > $limit) {
            $this->audit($clinicId, 'quota_check', self::PLAN_QUOTA_EXCEEDED, array_merge($meta, ['metric' => $metricKey, 'limit' => $limit, 'current' => $currentUsage]));
            throw new \RuntimeException(self::PLAN_QUOTA_EXCEEDED);
        }

        // Increment
        if ($usage) {
            $this->usageModel->where('id', $usage['id'])->increment('metric_value', $delta);
        } else {
            $this->usageModel->insert([
                'clinic_id' => $clinicId,
                'metric_key' => $metricKey,
                'metric_value' => $delta,
                'period_start' => $periodStart,
                'period_end' => date('Y-m-t')
            ]);
        }
    }

    private function getClinicPlan(int $clinicId)
    {
        $sub = $this->subscriptionModel->where('clinic_id', $clinicId)->first();
        if (!$sub) {
             throw new \RuntimeException(self::PLAN_MISSING_SUBSCRIPTION);
        }
        return $this->planModel->find($sub['plan_id']);
    }

    private function checkNestedKey($array, $key)
    {
        $keys = explode('.', $key);
        $current = $array;
        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                return false;
            }
            $current = $current[$k];
        }
        return (bool)$current;
    }

    private function audit($clinicId, $action, $reason, $meta = [])
    {
        $userId = session()->get('user_id') ?: 0; // 0 if job
        $this->auditModel->insert([
            'clinic_id' => $clinicId,
            'actor_user_id' => $userId,
            'action_key' => $action,
            'reason_code' => $reason,
            'meta_json' => json_encode($meta),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
