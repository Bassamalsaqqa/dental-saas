<?php

namespace App\Services;

use App\Models\PlanModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\PlanUsageModel;
use App\Models\PlanAuditModel;
use App\Models\PatientModel;
use App\Models\ClinicUserModel;
use App\Models\FileAttachmentModel;

class PlanGuard
{
    protected \CodeIgniter\Database\BaseConnection $db;
    protected $planModel;
    protected $subscriptionModel;
    protected $usageModel;
    protected $auditModel;
    protected $patientModel;
    protected $clinicUserModel;
    protected $fileAttachmentModel;

    const PLAN_MISSING_SUBSCRIPTION = 'PLAN_MISSING_SUBSCRIPTION';
    const PLAN_SUBSCRIPTION_INACTIVE = 'PLAN_SUBSCRIPTION_INACTIVE';
    const PLAN_FEATURE_DISABLED = 'PLAN_FEATURE_DISABLED';
    const PLAN_QUOTA_EXCEEDED = 'PLAN_QUOTA_EXCEEDED';

    public function __construct(?\CodeIgniter\Database\BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->planModel = new PlanModel($this->db);
        $this->subscriptionModel = new ClinicSubscriptionModel($this->db);
        $this->usageModel = new PlanUsageModel($this->db);
        $this->auditModel = new PlanAuditModel($this->db);
        $this->patientModel = new PatientModel($this->db);
        $this->clinicUserModel = new ClinicUserModel($this->db);
        $this->fileAttachmentModel = new FileAttachmentModel($this->db);
    }

    /**
     * Helper to determine if current execution is a web request.
     */
    private function isWebRequest(): bool
    {
        return is_cli() === false;
    }

    /**
     * Helper to determine if we are in API v1 scope (via RuntimeContext).
     */
    private function isApiScope(): bool
    {
        return !is_null(\App\Libraries\ApiRuntimeContext::getUserId());
    }

    private function resolveActorUserId(): int
    {
        $apiUserId = \App\Libraries\ApiRuntimeContext::getUserId();
        if ($apiUserId) {
            return $apiUserId;
        }

        if ($this->isWebRequest()) {
            $session = session();
            return (int)($session->get('user_id') ?: 0);
        }

        return 0;
    }

    /**
     * Assert that the clinic has an active subscription.
     */
    public function assertSubscriptionActive(int $clinicId)
    {
        $subModel = new ClinicSubscriptionModel($this->db);
        $sub = $subModel->withoutTenantScope()->getCurrentSubscription($clinicId);
        if ($sub) {
            $sub = $subModel->markExpiredIfNeeded($sub);
        }

        $standing = ClinicSubscriptionModel::evaluateStanding($sub);
        $isValid = $standing['is_valid'];
        $reason = $standing['reason'];

        if (!$isValid) {
            $this->audit($clinicId, 'subscription_check', self::PLAN_SUBSCRIPTION_INACTIVE, ['status' => ($sub['status'] ?? 'none'), 'reason' => $reason]);
            log_message('error', "PLAN_SUBSCRIPTION_INACTIVE: clinic_id={$clinicId} reason={$reason} status=" . ($sub['status'] ?? 'none') . " start_at=" . ($sub['start_at'] ?? 'null') . " end_at=" . ($sub['end_at'] ?? 'null'));
            
            if ($this->isApiScope()) {
                return ['allowed' => false, 'code' => 'subscription_inactive', 'message' => 'Subscription required or expired.'];
            }
            
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Subscription required.");
        }
        
        return ['allowed' => true];
    }

    /**
     * Assert that a feature is enabled in the plan.
     */
    public function assertFeature(int $clinicId, string $featureKey, array $meta = [])
    {
        // 1. Invariant: Require valid clinicId in web context
        if ($this->isWebRequest() && !$this->isApiScope() && !$clinicId) {
            $req = service('request');
            $method = $req ? $req->getMethod() : 'N/A';
            $path = $req ? $req->getUri()->getPath() : 'N/A';
            $userId = $this->resolveActorUserId() ?: 'N/A';
            log_message('error', "PLAN_GUARD_CONTEXT_MISSING: clinic_id=null method={$method} route={$path} user_id={$userId}");
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Context missing.");
        }

        // 2. Standing check ONLY for non-web entrypoints (CLI/Jobs)
        // For API, the AuthFilter handles the standing check, but we can re-assert if cheap?
        // Actually, API calls assertSubscriptionActive in the filter.
        if (!$this->isWebRequest() && !$this->isApiScope()) {
            $this->assertSubscriptionActive($clinicId);
        }
        
        $plan = $this->getClinicPlan($clinicId);
        if (!$plan && $this->isApiScope()) {
             return ['allowed' => false, 'code' => 'subscription_inactive', 'message' => 'Subscription required or expired.'];
        }

        $features = json_decode($plan['features_json'] ?? '{}', true) ?? [];

        if (!$this->checkNestedKey($features, $featureKey)) {
            $this->audit($clinicId, 'feature_check', self::PLAN_FEATURE_DISABLED, array_merge($meta, ['feature' => $featureKey]));
            log_message('error', "PLAN_FEATURE_BLOCK: clinic_id={$clinicId} feature={$featureKey}");
            
            if ($this->isApiScope()) {
                return ['allowed' => false, 'code' => 'feature_disabled', 'message' => 'Feature not available in current plan.', 'details' => ['feature' => $featureKey]];
            }

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Feature not in plan.");
        }
        
        return ['allowed' => true];
    }

    /**
     * Assert quota limits (incrementing usage if passed).
     */
    public function assertQuota(int $clinicId, string $metricKey, int $delta = 1, array $meta = [])
    {
        // 1. Invariant: Require valid clinicId in web context
        if ($this->isWebRequest() && !$this->isApiScope() && !$clinicId) {
            $req = service('request');
            $method = $req ? $req->getMethod() : 'N/A';
            $path = $req ? $req->getUri()->getPath() : 'N/A';
            $userId = $this->resolveActorUserId() ?: 'N/A';
            log_message('error', "PLAN_GUARD_CONTEXT_MISSING: clinic_id=null method={$method} route={$path} user_id={$userId}");
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Context missing.");
        }

        // 2. Standing check ONLY for non-web entrypoints
        if (!$this->isWebRequest() && !$this->isApiScope()) {
            $this->assertSubscriptionActive($clinicId);
        }
        
        $plan = $this->getClinicPlan($clinicId);
        if (!$plan && $this->isApiScope()) {
             return ['allowed' => false, 'code' => 'subscription_inactive', 'message' => 'Subscription required or expired.'];
        }

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

        // Live Count Quota (Snapshot) - Patients
        if ($canonicalKey === 'patients_active_max') {
             $current = $this->patientModel->countActivePatientsByClinic($clinicId);
             if ($current + $delta > $limit) {
                 $this->audit($clinicId, 'quota_check', self::PLAN_QUOTA_EXCEEDED, array_merge($meta, ['metric' => $canonicalKey, 'limit' => $limit, 'current' => $current]));
                 log_message('error', "PLAN_QUOTA_BLOCK: clinic_id={$clinicId} metric={$canonicalKey} limit={$limit} usage={$current}");
                 
                 if ($this->isApiScope()) {
                     helper('api');
                     // Note: Contract says 429 for quotas
                     return ['allowed' => false, 'code' => 'plan_quota_exceeded', 'message' => 'Plan limit reached.', 'status_code' => 429, 'details' => ['metric' => $canonicalKey, 'limit' => $limit, 'usage' => $current]];
                 }

                 throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Plan limit reached.");
             }
             return;
        }

        // Live Count Quota (Snapshot) - Users
        if ($canonicalKey === 'users_max') {
            $current = $this->clinicUserModel->countActiveByClinic($clinicId);
            if ($current + $delta > $limit) {
                $this->audit($clinicId, 'quota_check', self::PLAN_QUOTA_EXCEEDED, array_merge($meta, ['metric' => $canonicalKey, 'limit' => $limit, 'current' => $current]));
                log_message('error', "PLAN_QUOTA_BLOCK: clinic_id={$clinicId} metric={$canonicalKey} limit={$limit} usage={$current}");

                if ($this->isApiScope()) {
                    return ['allowed' => false, 'code' => 'plan_quota_exceeded', 'message' => 'Plan user limit reached.', 'status_code' => 429, 'details' => ['metric' => $canonicalKey, 'limit' => $limit, 'usage' => $current]];
                }

                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Plan user limit reached.");
            }
            return;
        }

        // Storage Quota (Snapshot)
        if ($canonicalKey === 'storage_gb') {
            $currentBytes = $this->fileAttachmentModel->getTotalSizeByClinic($clinicId);
            $limitBytes = $limit > 0 ? (int) ($limit * 1024 * 1024 * 1024) : 0;

            if ($limitBytes > 0 && ($currentBytes + $delta) > $limitBytes) {
                $details = [
                    'metric' => $canonicalKey,
                    'limit_gb' => $limit,
                    'usage_gb' => round($currentBytes / (1024 * 1024 * 1024), 3),
                ];
                $this->audit($clinicId, 'quota_check', self::PLAN_QUOTA_EXCEEDED, array_merge($meta, $details));
                log_message('error', "PLAN_QUOTA_BLOCK: clinic_id={$clinicId} metric={$canonicalKey} limit_gb={$limit} usage_bytes={$currentBytes}");

                if ($this->isApiScope()) {
                    return ['allowed' => false, 'code' => 'plan_quota_exceeded', 'message' => 'Plan storage limit reached.', 'status_code' => 429, 'details' => $details];
                }

                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Plan storage limit reached.");
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
            
            if ($this->isApiScope()) {
                 return ['allowed' => false, 'code' => 'plan_quota_exceeded', 'message' => 'Plan usage limit reached.', 'status_code' => 429, 'details' => ['metric' => $canonicalKey, 'limit' => $limit, 'usage' => $currentUsage]];
            }

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
        
        return ['allowed' => true];
    }

    private function getClinicPlan(int $clinicId)
    {
        $subModel = new ClinicSubscriptionModel($this->db);
        $sub = $subModel->withoutTenantScope()->getCurrentSubscription($clinicId);

        if (!$sub) {
             log_message('error', "PLAN_SUBSCRIPTION_INACTIVE: clinic_id={$clinicId} reason=no_valid_subscription status=none");
             
             if ($this->isApiScope()) {
                 return null;
             }

             throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Access Denied: Subscription required.");
        }
        return $this->planModel->find($sub['plan_id']);
    }

    /**
     * Read a plan limit value without enforcing or incrementing usage.
     */
    public function getLimitValue(int $clinicId, string $metricKey, $default = null)
    {
        $plan = $this->getClinicPlan($clinicId);
        $limits = json_decode($plan['limits_json'] ?? '{}', true) ?? [];

        $canonicalKey = $metricKey;
        if ($metricKey === 'patients') {
            $canonicalKey = 'patients_active_max';
        }

        if (array_key_exists($canonicalKey, $limits)) {
            return $limits[$canonicalKey];
        }

        if (array_key_exists($metricKey, $limits)) {
            return $limits[$metricKey];
        }

        return $default;
    }

    /**
     * Check if a feature is enabled without emitting audits or throwing.
     */
    public function isFeatureEnabled(int $clinicId, string $featureKey, bool $default = false): bool
    {
        try {
            $plan = $this->getClinicPlan($clinicId);
        } catch (\Throwable $e) {
            log_message('error', "PlanGuard::isFeatureEnabled storage/context failure for clinic {$clinicId}: {message}", ['message' => $e->getMessage()]);
            return false;
        }

        if (!is_array($plan)) {
            return false;
        }

        $features = json_decode($plan['features_json'] ?? '{}', true);
        if (!is_array($features)) {
            log_message('error', "PlanGuard::isFeatureEnabled malformed features JSON for clinic {$clinicId}");
            return false;
        }

        $keys = explode('.', $featureKey);
        $current = $features;
        foreach ($keys as $k) {
            if (!is_array($current) || !array_key_exists($k, $current)) {
                return $default;
            }
            $current = $current[$k];
        }

        return (bool) $current;
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
        $userId = $this->resolveActorUserId();
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
