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
        \App\Libraries\ApiRuntimeContext::clear();
        helper('api');

        // 1. Bearer Authentication
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader)) {
            $authHeader = $request->getServer('HTTP_AUTHORIZATION');
        }
        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $authHeader = $headers['Authorization'];
            }
        }

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return api_error(401, 'unauthenticated', 'Authentication required.');
        }

        $userId = null;
        $clinicId = null;
        if (!empty($authHeader) && str_starts_with($authHeader, 'Bearer ')) {
            $rawToken = substr($authHeader, 7);
            $tokenModel = new ApiTokenModel();
            $userId = $tokenModel->validateToken($rawToken);

            if (!$userId) {
                return api_error(401, 'unauthenticated', 'Authentication required.');
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
        }

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

        // 5. Set Context (Stateless - Runtime Static)
        \App\Libraries\ApiRuntimeContext::setUserId($userId);
        \App\Libraries\ApiRuntimeContext::setClinicId($clinicId);

        // 6. Subscription Standing
        $planGuard = new PlanGuard();
        // PlanGuard returns decision array in API scope
        $decision = $planGuard->assertSubscriptionActive($clinicId);
        
        if (is_array($decision) && array_key_exists('allowed', $decision) && $decision['allowed'] === false) {
             return api_error(
                 $decision['status_code'] ?? 403,
                 $decision['code'] ?? 'forbidden',
                 $decision['message'] ?? 'Access denied.',
                 $decision['details'] ?? []
             );
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        \App\Libraries\ApiRuntimeContext::clear();
    }
}
