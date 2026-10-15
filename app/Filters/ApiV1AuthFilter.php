<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ApiTokenModel;
use App\Models\ClinicUserModel;
use App\Models\ClinicModel;
use App\Models\UserModel;
use App\Services\PlanGuard;

class ApiV1AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        \App\Libraries\ApiRuntimeContext::clear();
        helper('api');
        try {
            return $this->authorizeRequest($request);
        } catch (\Throwable $e) {
            \App\Libraries\ApiRuntimeContext::clear();
            log_message('error', 'Bearer authorization unavailable.');
            return api_error(503, 'authorization_unavailable', 'Authorization temporarily unavailable.');
        }
    }

    private function authorizeRequest(RequestInterface $request)
    {

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
            \App\Libraries\ApiRuntimeContext::clear();
            return api_error(401, 'unauthenticated', 'Authentication required.');
        }

        $rawToken = substr($authHeader, 7);
        $tokenModel = new ApiTokenModel();
        $userId = $tokenModel->validateToken($rawToken);

        if (!$userId) {
            \App\Libraries\ApiRuntimeContext::clear();
            return api_error(401, 'unauthenticated', 'Authentication required.');
        }

        // Validate token owner account is active (users.active = 1)
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (!$user || (int) ($user['active'] ?? 0) !== 1) {
            \App\Libraries\ApiRuntimeContext::clear();
            return api_error(401, 'unauthenticated', 'User account is inactive or disabled.');
        }

        // 2. Tenant Header Mandatory (X-Clinic-Id)
        $clinicIdHeader = $request->getHeaderLine('X-Clinic-Id');
        if ($clinicIdHeader === '' || $clinicIdHeader === null) {
            \App\Libraries\ApiRuntimeContext::clear();
            return api_error(422, 'tenant_context_missing', 'X-Clinic-Id header is required.');
        }

        if (!preg_match('/^[1-9]\d{0,9}$/', $clinicIdHeader) || (int) $clinicIdHeader > 2147483647) {
            \App\Libraries\ApiRuntimeContext::clear();
            return api_error(422, 'tenant_context_invalid', 'X-Clinic-Id must be a positive integer.');
        }

        $clinicId = (int) $clinicIdHeader;

        // 3. Validate Clinic Existence and Status
        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($clinicId);
        if (!$clinic) {
            \App\Libraries\ApiRuntimeContext::clear();
            return api_error(422, 'tenant_context_invalid', 'Clinic not found.');
        }

        if (($clinic['status'] ?? '') !== 'active') {
            \App\Libraries\ApiRuntimeContext::clear();
            return api_error(403, 'tenant_context_forbidden', 'Clinic is suspended or inactive.');
        }

        // 4. Tenant Membership Authorization (Strictly stateless: no browser session fallback)
        $permissionService = new \App\Services\PermissionService();
        $roleId = $permissionService->getTenantRoleId($userId, $clinicId);
        if (!$roleId) {
            \App\Libraries\ApiRuntimeContext::clear();
            log_message('warning', 'ApiV1AuthFilter membership or role denied: user_id={userId}, clinic_id={clinicId}', [
                'userId' => $userId,
                'clinicId' => $clinicId,
            ]);
            return api_error(403, 'tenant_context_forbidden', 'Access denied to this clinic.');
        }

        // 5. Set Context (Stateless - Runtime Static)
        \App\Libraries\ApiRuntimeContext::setUserId($userId);
        \App\Libraries\ApiRuntimeContext::setClinicId($clinicId);

        // 6. Subscription Standing
        $planGuard = new PlanGuard();
        $decision = $planGuard->assertSubscriptionActive($clinicId);

        if (is_array($decision) && array_key_exists('allowed', $decision) && $decision['allowed'] === false) {
             \App\Libraries\ApiRuntimeContext::clear();
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
