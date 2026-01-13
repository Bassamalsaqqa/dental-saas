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
        $auth = service('authentication'); // or however auth is accessed, commonly 'ionAuth' in this project
        // In this project, IonAuth is often used via wrapper or direct library.
        // Let's use the standard IonAuth library as seen in AdminFilter
        $ionAuth = new \App\Libraries\IonAuth();

        // 1. Authenticated User
        if (!$ionAuth->loggedIn()) {
            // Fail closed - strict deny or redirect to login
            return redirect()->to('/auth/login');
        }

        // 2. Session 'global_mode' === true
        if ($session->get('global_mode') !== true) {
            // Fail closed - do not reveal existence of route (404) or strictly deny (403)
            // Prompt says "Denies (403 or 404 per existing patterns)"
            // Using 404 is safer for hiding control plane
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 3. Super Admin Check
        $userId = $ionAuth->getUserId();
        $permissionService = service('permission');
        
        if (!$permissionService->isSuperAdmin($userId)) {
             throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 4. Clear/Ignore Tenant Context
        $session->remove('active_clinic_id');
        $session->remove('impersonated_clinic_id');
        
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
