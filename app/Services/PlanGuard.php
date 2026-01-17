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
     * Helper to determine if current execution is a web request.
     */
    private function isWebRequest(): bool
    {
        return is_cli() === false;
    }

    /**
     * Assert that the clinic has an active subscription.
     */
    public function assertSubscriptionActive(int $clinicId)
    {
        $subModel = new ClinicSubscriptionModel();
        // Use getCurrentSubscription with CASE ordering logic
        $sub = $subModel->withoutTenantScope()->getCurrentSubscription($clinicId);

        $isValid = false;
        $reason = 'MISSING_OR_INACTIVE';

        if ($sub) {
            $now = date('Y-m-d H:i:s');
            if ($sub['status'] === 'active') {
                if (empty($sub['end_at']) || $sub['end_at'] > $now) {
                    $isValid = true;
                } else {
                    $reason = 'EXPIRED';
                }
            } elseif ($sub['status'] === 'trial') {
                if (!empty($sub['trial_ends_at']) && $sub['trial_ends_at'] > $now) {
                    $isValid = true;
                } else {
                    $reason = 'TRIAL_EXPIRED';
                }
            }
        }

        if (!$isValid) {
            $this->audit($clinicId, 'subscription_check', self::PLAN_SUBSCRIPTION_INACTIVE, ['status' => ($sub['status'] ?? 'none'), 'reason' => $reason]);
            log_message('error', "PLAN_SUBSCRIPTION_INACTIVE: clinic_id={$clinicId} reason={$reason} status=" . ($sub['status'] ?? 'none') . " end_at=" . ($sub['end_at'] ?? 'null') . " trial_ends_at=" . ($sub['trial_ends_at'] ?? 'null'));
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Subscription required.");
        }
    }

    /**
     * Assert that a feature is enabled in the plan.
     */
    public function assertFeature(int $clinicId, string $featureKey, array $meta = [])
    {
        // 1. Invariant: Require valid clinicId in web context
        if ($this->isWebRequest() && !$clinicId) {
            $req = service('request');
            $method = $req ? $req->getMethod() : 'N/A';
            $path = $req ? $req->getUri()->getPath() : 'N/A';
            $userId = session()->get('user_id') ?: 'N/A';
            log_message('error', "PLAN_GUARD_CONTEXT_MISSING: clinic_id=null method={$method} route={$path} user_id={$userId}");
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Context missing.");
        }

        // 2. Standing check ONLY for non-web entrypoints (CLI/Jobs)
        if (!$this->isWebRequest()) {
            $this->assertSubscriptionActive($clinicId);
        }
        
        $plan = $this->getClinicPlan($clinicId);
        $features = json_decode($plan['features_json'] ?? '{}', true) ?? [];

        if (!$this->checkNestedKey($features, $featureKey)) {
            $this->audit($clinicId, 'feature_check', self::PLAN_FEATURE_DISABLED, array_merge($meta, ['feature' => $featureKey]));
            log_message('error', "PLAN_FEATURE_BLOCK: clinic_id={$clinicId} feature={$featureKey}");
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Feature not in plan.");
        }
    }

    /**
     * Assert quota limits (incrementing usage if passed).
     */
    public function assertQuota(int $clinicId, string $metricKey, int $delta = 1, array $meta = [])
    {
        // 1. Invariant: Require valid clinicId in web context
        if ($this->isWebRequest() && !$clinicId) {
            $req = service('request');
            $method = $req ? $req->getMethod() : 'N/A';
            $path = $req ? $req->getUri()->getPath() : 'N/A';
            $userId = session()->get('user_id') ?: 'N/A';
            log_message('error', "PLAN_GUARD_CONTEXT_MISSING: clinic_id=null method={$method} route={$path} user_id={$userId}");
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Context missing.");
        }

        // 2. Standing check ONLY for non-web entrypoints
        if (!$this->isWebRequest()) {
            $this->assertSubscriptionActive($clinicId);
        }
        
        $plan = $this->getClinicPlan($clinicId);
        $limits = json_decode($plan['limits_json'] ?? '{}', true) ?? [];
        
        // Canonical map
        $canonicalKey = $metricKey;
        if ($metricKey === 'patients') {
            $canonicalKey = 'patients_active_max';
        }

        $limit = $limits[$canonicalKey] ?? $limits[$metricKey] ?? 0;
        
        if ($limit == -1) {
            return; // Unlimited
        }

        // Live Count Quota (Snapshot)
        if ($canonicalKey === 'patients_active_max') {
             $current = $this->patientModel->countActivePatientsByClinic($clinicId);
             if ($current + $delta > $limit) {
                 $this->audit($clinicId, 'quota_check', self::PLAN_QUOTA_EXCEEDED, array_merge($meta, ['metric' => $canonicalKey, 'limit' => $limit, 'current' => $current]));
                 log_message('error', "PLAN_QUOTA_BLOCK: clinic_id={$clinicId} metric={$canonicalKey} limit={$limit} usage={$current}");
                 throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Plan limit reached.");
             }
             return;
        }

        // Monthly Usage Quota
        $periodStart = date('Y-m-01');
        $usage = $this->usageModel->where('clinic_id', $clinicId)
                                  ->where('metric_key', $canonicalKey)
                                  ->where('period_start', $periodStart)
                                  ->first();
                                  
        $currentUsage = $usage ? (int)$usage['metric_value'] : 0;

        if ($currentUsage + $delta > $limit) {
            $this->audit($clinicId, 'quota_check', self::PLAN_QUOTA_EXCEEDED, array_merge($meta, ['metric' => $canonicalKey, 'limit' => $limit, 'current' => $currentUsage]));
            log_message('error', "PLAN_QUOTA_BLOCK: clinic_id={$clinicId} metric={$canonicalKey} limit={$limit} usage={$currentUsage}");
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Plan usage limit reached.");
        }

        // Increment
        if ($usage) {
            $this->usageModel->where('id', $usage['id'])->increment('metric_value', $delta);
        } else {
            $this->usageModel->insert([
                'clinic_id' => $clinicId,
                'metric_key' => $canonicalKey,
                'metric_value' => $delta,
                'period_start' => $periodStart,
                'period_end' => date('Y-m-t')
            ]);
        }
    }

    private function getClinicPlan(int $clinicId)
    {
        $subModel = new ClinicSubscriptionModel();
        // Use getCurrentSubscription with CASE ordering logic
        $sub = $subModel->withoutTenantScope()->getCurrentSubscription($clinicId);

        if (!$sub) {
             log_message('error', "PLAN_SUBSCRIPTION_INACTIVE: clinic_id={$clinicId} reason=MISSING_OR_INACTIVE status=none");
             throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Subscription required.");
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