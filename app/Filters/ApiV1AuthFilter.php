<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ApiTokenModel;
use App\Models\ClinicUserModel;
use App\Models\ClinicModel;
use App\Services\PlanGuard;

class ApiV1AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('api');

        // 1. Bearer Authentication
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return api_error(401, 'unauthenticated', 'Authentication required.');
        }

        $rawToken = substr($authHeader, 7);
        $tokenModel = new ApiTokenModel();
        $userId = $tokenModel->validateToken($rawToken);

        if (!$userId) {
            return api_error(401, 'unauthenticated', 'Invalid or expired token.');
        }

        // 2. Tenant Header Mandatory (X-Clinic-Id)
        $clinicIdHeader = $request->getHeaderLine('X-Clinic-Id');
        if (empty($clinicIdHeader)) {
            return api_error(422, 'tenant_context_missing', 'X-Clinic-Id header is required.');
        }

        if (!is_numeric($clinicIdHeader)) {
            return api_error(422, 'tenant_context_invalid', 'X-Clinic-Id must be an integer.');
        }

        $clinicId = (int)$clinicIdHeader;

        // 3. Validate Clinic Existence
        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($clinicId);
        if (!$clinic) {
            return api_error(422, 'tenant_context_invalid', 'Clinic not found.');
        }

        // 4. Tenant Membership Authorization
        $membershipModel = new ClinicUserModel();
        $membership = $membershipModel->where('user_id', $userId)
                                      ->where('clinic_id', $clinicId)
                                      ->where('status', 'active')
                                      ->first();

        if (!$membership) {
            return api_error(403, 'tenant_context_forbidden', 'Access denied to this clinic.');
        }

        if ($clinic['status'] !== 'active') {
            return api_error(403, 'tenant_context_forbidden', 'Clinic is suspended or inactive.');
        }

        // 5. Subscription Standing
        $planGuard = new PlanGuard();
        try {
            // Note: assertSubscriptionActive currently throws PageNotFoundException (404)
            // We must catch and map to 403 per API contract.
            $planGuard->assertSubscriptionActive($clinicId);
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            return api_error(403, 'subscription_inactive', 'Subscription required or expired.');
        }

        // 6. Injection into Session (Minimal bridge for TenantAwareModel)
        // Since TenantAwareModel relies on session()->get('active_clinic_id'), 
        // we set it here but DO NOT trust cookies.
        $session = service('session');
        $session->set('active_clinic_id', $clinicId);
        $session->set('user_id', $userId);
        $session->set('active_clinic_role_id', $membership['role_id']);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
