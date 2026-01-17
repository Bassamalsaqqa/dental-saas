<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\ClinicSubscriptionModel;

/**
 * Subscription Filter
 * 
 * Enforces subscription standing for tenant routes.
 * Must run AFTER TenantFilter.
 */
class SubscriptionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');
        $clinicId = $session->get('active_clinic_id');
        $userId = $session->get('user_id') ?: 'unauthenticated';

        // 1. Context Check (Forensic)
        if (!$clinicId) {
            log_message('error', "SUBSCRIPTION_CONTEXT_MISSING: user_id={$userId} clinic_id=null route=" . $request->getUri()->getPath() . " method=" . $request->getMethod());
            throw PageNotFoundException::forPageNotFound("Access Denied: Tenant context required.");
        }

        // 2. Standing Check
        $subModel = new ClinicSubscriptionModel();
        // Use withoutTenantScope to ensure we can see the subscription even if scoping is acting up
        $sub = $subModel->withoutTenantScope()->getCurrentSubscription((int)$clinicId);

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
            } else {
                $reason = strtoupper($sub['status']);
            }
        }

        if (!$isValid) {
            log_message('error', "SUBSCRIPTION_STATE_BLOCK: clinic_id={$clinicId} user_id={$userId} status=" . ($sub['status'] ?? 'none') . " end_at=" . ($sub['end_at'] ?? 'null') . " trial_ends_at=" . ($sub['trial_ends_at'] ?? 'null') . " reason={$reason}");
            throw PageNotFoundException::forPageNotFound("Access Denied: Subscription required.");
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
