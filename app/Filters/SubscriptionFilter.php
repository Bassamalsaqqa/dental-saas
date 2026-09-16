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
        if ($sub) {
            $sub = $subModel->markExpiredIfNeeded($sub);
        }

        $standing = ClinicSubscriptionModel::evaluateStanding($sub);
        $isValid = $standing['is_valid'];
        $reason = $standing['reason'];

        if (!$isValid) {
            log_message('error', "SUBSCRIPTION_STATE_BLOCK: clinic_id={$clinicId} user_id={$userId} status=" . ($sub['status'] ?? 'none') . " start_at=" . ($sub['start_at'] ?? 'null') . " end_at=" . ($sub['end_at'] ?? 'null') . " reason={$reason}");
            throw PageNotFoundException::forPageNotFound("Access Denied: Subscription required.");
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
