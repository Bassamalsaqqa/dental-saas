<?php

declare(strict_types=1);

namespace App\Filters;

use App\Libraries\ApiRuntimeContext;
use App\Libraries\IonAuth;
use App\Models\ClinicModel;
use App\Models\ClinicUserModel;
use App\Models\UserModel;
use App\Services\PermissionService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SessionSearchFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Clear stale ApiRuntimeContext BEFORE models/permissions
        ApiRuntimeContext::clear();
        try {
            return $this->authorizeSession($request);
        } catch (\Throwable $e) {
            log_message('error', 'Session search authorization unavailable.');
            return $this->error('authorization_unavailable', 'Authorization temporarily unavailable.', 503);
        }
    }

    private function authorizeSession(RequestInterface $request)
    {

        // 1. Authenticated global user
        $ionAuth = new IonAuth();
        if (!$ionAuth->loggedIn()) {
            return $this->error('unauthenticated', 'Authentication required.', 401);
        }

        $userId = (int) ($ionAuth->getUserId() ?: 0);
        if ($userId <= 0) {
            return $this->error('unauthenticated', 'Authentication required.', 401);
        }

        $user = (new UserModel())->find($userId);
        if (!$user || (int) ($user['active'] ?? 0) !== 1) {
            return $this->error('unauthenticated', 'User account is inactive or disabled.', 401);
        }

        $session = service('session');

        // 2. global_mode must be false
        if ($session->get('global_mode') === true) {
            return $this->error('forbidden', 'Control plane global mode cannot access tenant search.', 403);
        }

        // 3. Positive active server-session clinic
        $clinicId = (int) ($session->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return $this->error('tenant_context_missing', 'Active clinic context required.', 422);
        }

        // 4. Validate clinic is active
        $clinic = (new ClinicModel())->find($clinicId);
        if (!$clinic || ($clinic['status'] ?? '') !== 'active') {
            return $this->error('tenant_context_forbidden', 'Clinic is inactive or not found.', 403);
        }

        // 5. Authorization: Genuine platform assignment with explicit matching WEB impersonation OR active membership with eligible role
        $isImpersonating = $session->get('is_impersonating') === true;
        $impersonatedClinicId = (int) ($session->get('impersonated_clinic_id') ?? 0);
        $permissionService = new PermissionService();

        if ($isImpersonating && $impersonatedClinicId === $clinicId && $permissionService->isSuperAdmin($userId)) {
            // Validated WEB impersonation authorized
            return null;
        }

        $membership = (new ClinicUserModel())->withoutTenantScope()
            ->where('user_id', $userId)
            ->where('clinic_id', $clinicId)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            return $this->error('tenant_context_forbidden', 'Access denied to this clinic.', 403);
        }

        $roleId = $permissionService->getTenantRoleId($userId, $clinicId);
        if (!$roleId) {
            return $this->error('tenant_context_forbidden', 'Active membership role required.', 403);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Clear stale ApiRuntimeContext AFTER failures/success
        ApiRuntimeContext::clear();
    }

    private function error(string $code, string $message, int $status): ResponseInterface
    {
        ApiRuntimeContext::clear();

        return service('response')
            ->setStatusCode($status)
            ->setJSON([
                'error' => [
                    'code' => $code,
                    'message' => $message,
                    'details' => [],
                ],
                'meta' => [
                    'request_id' => 'req_' . bin2hex(random_bytes(8)),
                ],
            ]);
    }
}
