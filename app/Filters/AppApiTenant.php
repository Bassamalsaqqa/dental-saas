<?php

namespace App\Filters;

use App\Libraries\IonAuth;
use App\Services\PermissionService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AppApiTenant implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');

        if ($session->get('global_mode') === true) {
            return $this->error('tenant_plane_required', 'Tenant mode is required for this resource.', 403);
        }

        $clinicId = (int) ($session->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return $this->error('tenant_context_missing', 'Select a clinic before continuing.', 422);
        }

        $userId = (int) ((new IonAuth())->getUserId() ?: 0);
        if ($userId <= 0) {
            return $this->error('unauthenticated', 'Authentication required.', 401);
        }

        $db = \Config\Database::connect();
        $userRow = $db->table('users')->where('id', $userId)->where('active', 1)->get()->getRowArray();
        if (!$userRow) {
            $session->remove(['active_clinic_id', 'active_clinic_role_id', 'active_clinic_membership_id']);
            return $this->error('unauthenticated', 'User account is inactive or disabled.', 401);
        }

        $clinicRow = $db->table('clinics')->where('id', $clinicId)->where('status', 'active')->get()->getRowArray();
        if (!$clinicRow) {
            $session->remove(['active_clinic_id', 'active_clinic_role_id', 'active_clinic_membership_id']);
            return $this->error('tenant_context_forbidden', 'Clinic is suspended or inactive.', 403);
        }

        $permissionService = service('permission');
        if (!$permissionService) {
            $permissionService = new PermissionService();
        }

        // App API resources never gain authority from web impersonation,
        // including platform users with an eligible low-privilege membership.
        if ($session->get('is_impersonating') === true || (int) ($session->get('impersonated_clinic_id') ?? 0) > 0) {
            $session->remove(['active_clinic_id', 'active_clinic_role_id', 'active_clinic_membership_id']);
            return $this->error('tenant_context_forbidden', 'Web impersonation is not supported for App API resources.', 403);
        }

        $tenantRoleId = $permissionService->getTenantRoleId($userId, $clinicId);
        if ($tenantRoleId === null) {
            $session->remove(['active_clinic_id', 'active_clinic_role_id', 'active_clinic_membership_id']);

            return $this->error('tenant_context_forbidden', 'Access denied to this clinic.', 403);
        }

        if ($session->get('active_clinic_role_id') !== $tenantRoleId) {
            $session->set('active_clinic_role_id', $tenantRoleId);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function error(string $code, string $message, int $status): ResponseInterface
    {
        return service('response')
            ->setStatusCode($status)
            ->setJSON([
                'error' => [
                    'code' => $code,
                    'message' => $message,
                    'details' => [],
                ],
                'meta' => [
                    'request_id' => $this->requestId(),
                ],
            ]);
    }

    private function requestId(): string
    {
        return 'req_' . bin2hex(random_bytes(8));
    }
}
