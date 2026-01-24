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
            // Check if it's an API request
            $path = $request->getUri()->getPath();
            $isApi = strpos($path, '/api/') !== false
                || str_starts_with($path, 'api/')
                || ($request->hasHeader('Accept') && str_contains($request->getHeaderLine('Accept'), 'application/json'));

            if ($isApi) {
                return service('response')->setStatusCode(401)->setJSON([
                    'error' => [
                        'code' => 'unauthenticated',
                        'message' => 'Authentication required.'
                    ]
                ]);
            }

            // Fail closed - strict deny or redirect to login
            return redirect()->to('/auth/login');
        }

        $userId = $ionAuth->getUserId();

        // 2. Super Admin Check (Moved up)
        $permissionService = service('permission');
        if (!$permissionService) {
            $permissionService = new \App\Services\PermissionService();
        }

        $isSuperAdmin = $permissionService->isSuperAdmin($userId);

        // 3. Session 'global_mode' check
        if (!$session->get('global_mode')) {
            // If Super Admin, redirect to Entry Gate for re-entry
            if ($isSuperAdmin) {
                return redirect()->to('/controlplane');
            }
            
            // Otherwise, Fail Closed (404)
            log_message('warning', 'ControlPlaneFilter deny: global_mode missing for user_id={userId}', ['userId' => $userId]);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 4. Double check Super Admin (redundant but safe)
        if (!$isSuperAdmin) {
             log_message('warning', 'ControlPlaneFilter deny: not superadmin for user_id={userId}', ['userId' => $userId]);
             throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 5. Clear/Ignore Tenant Context
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
