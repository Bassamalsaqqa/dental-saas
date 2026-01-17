<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Control Plane Filter
 * 
 * Enforces strict separation for Control Plane (Super Admin) routes.
 * 
 * Requirements:
 * 1. Authenticated User
 * 2. Session 'global_mode' === true
 * 3. Super Admin Role (isSuperAdmin)
 * 
 * Behavior:
 * - Clears tenant context (active_clinic_id, impersonated_clinic_id)
 * - Denies access (404/403) if requirements are not met
 */
class ControlPlaneFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');
        // In this project, IonAuth is often used via wrapper or direct library.
        // Use the standard IonAuth library as seen in other filters/controllers.
        $ionAuth = new \App\Libraries\IonAuth();

        // 1. Authenticated User
        if (!$ionAuth->loggedIn()) {
            // Fail closed - strict deny or redirect to login
            return redirect()->to('/auth/login');
        }

        $userId = $ionAuth->getUserId();

        // 2. Session 'global_mode' must be truthy
        if (!$session->get('global_mode')) {
            log_message('warning', 'ControlPlaneFilter deny: global_mode missing or false for user_id={userId}', ['userId' => $userId]);
            // Fail closed - do not reveal existence of route (404) or strictly deny (403)
            // Prompt says "Denies (403 or 404 per existing patterns)"
            // Using 404 is safer for hiding control plane
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 3. Super Admin Check
        $permissionService = service('permission');
        if (!$permissionService) {
            $permissionService = new \App\Services\PermissionService();
        }

        if (!$permissionService->isSuperAdmin($userId)) {
             log_message('warning', 'ControlPlaneFilter deny: not superadmin for user_id={userId}', ['userId' => $userId]);
             throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 4. Clear/Ignore Tenant Context
        $session->remove('active_clinic_id');
        $session->remove('active_clinic_role_id');
        $session->remove('impersonated_clinic_id');
        
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
