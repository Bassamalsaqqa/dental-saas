<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Tenant Filter
 * 
 * Enforces tenant context for Tenant Plane routes.
 * 
 * S2 Implementation:
 * - Checks active_clinic_id
 * - Redirects to /clinic/select if missing (HTML)
 * - Returns 403 JSON if missing (API)
 * - Validates membership against DB
 * - Enforces role_id truth from DB
 */
class TenantFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');
        $path = $request->getUri()->getPath();
        $pathOnly = trim($path, '/');
        $isControlPlane = str_starts_with($pathOnly, 'controlplane');

        $isApi = strpos($path, '/api/') !== false 
            || str_starts_with($path, 'api/') 
            || ($request->hasHeader('Accept') && str_contains($request->getHeaderLine('Accept'), 'application/json'));

        // CP-03: Airlock routing - requests in global_mode fail-closed for ALL tenant resources
        if ($session->get('global_mode') === true) {
            if ($isControlPlane) {
                return null;
            }

            $isApiOrAjax = $request->isAJAX() || $isApi;
            if ($isApiOrAjax) {
                return service('response')->setStatusCode(403)->setJSON([
                    'error' => [
                        'code' => 'forbidden',
                        'message' => 'Global mode cannot access tenant resources.',
                    ],
                ]);
            }

            // UX: Redirect common tenant routes to their control plane equivalents if available
            if ($pathOnly === 'roles') {
                return redirect()->to('/controlplane/roles');
            }

            if ($pathOnly === '') {
                return redirect()->to('/controlplane/dashboard');
            }
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $activeClinicId = $session->get('active_clinic_id');

        // 1. Missing Context
        if (!$activeClinicId || (int) $activeClinicId <= 0) {
            // Detect API/AJAX
            if ($request->isAJAX() || $isApi) {
                 return service('response')->setStatusCode(422)->setJSON([
                     'error' => [
                         'code' => 'tenant_context_missing',
                         'message' => 'Tenant context required.'
                     ]
                 ]);
            }
            return redirect()->to('/clinic/select');
        }

        $activeClinicId = (int) $activeClinicId;

        // 2. Validate Membership
        $db = \Config\Database::connect();
        // Use IonAuth strictly for user ID
        $ionAuth = new \App\Libraries\IonAuth();
        $userId = $ionAuth->getUserId();

        if (!$userId) {
            if ($request->isAJAX() || $isApi) {
                return service('response')->setStatusCode(401)->setJSON([
                    'error' => [
                        'code' => 'unauthenticated',
                        'message' => 'Authentication required.'
                    ]
                ]);
            }
             return redirect()->to('/auth/login');
        }

        $userId = (int) $userId;

        // Validate active global user
        $userRow = $db->table('users')->where('id', $userId)->where('active', 1)->get()->getRowArray();
        if (!$userRow) {
            $session->remove(['active_clinic_id', 'active_clinic_role_id', 'active_clinic_membership_id']);
            if ($request->isAJAX() || $isApi) {
                return service('response')->setStatusCode(401)->setJSON([
                    'error' => [
                        'code' => 'unauthenticated',
                        'message' => 'User account is inactive or disabled.'
                    ]
                ]);
            }
            return redirect()->to('/auth/login');
        }

        // Validate active clinic
        $clinicRow = $db->table('clinics')->where('id', $activeClinicId)->where('status', 'active')->get()->getRowArray();
        if (!$clinicRow) {
            $session->remove(['active_clinic_id', 'active_clinic_role_id', 'active_clinic_membership_id']);
            if ($request->isAJAX() || $isApi) {
                return service('response')->setStatusCode(403)->setJSON([
                    'error' => [
                        'code' => 'tenant_context_forbidden',
                        'message' => 'Clinic is suspended or inactive.'
                    ]
                ]);
            }
            return redirect()->to('/clinic/select');
        }

        $membership = $db->table('clinic_users')
            ->where('user_id', $userId)
            ->where('clinic_id', $activeClinicId)
            ->where('status', 'active')
            ->get()
            ->getRowArray();

        $isImpersonating = $session->get('is_impersonating') === true;
        $impersonatedClinicId = (int) ($session->get('impersonated_clinic_id') ?? 0);
        if (!$membership && $isImpersonating && $impersonatedClinicId > 0 && $impersonatedClinicId === $activeClinicId && $session->get('global_mode') !== true) {
            $permissionService = service('permission');
            if (!$permissionService) {
                $permissionService = new \App\Services\PermissionService();
            }
            if ($permissionService->isSuperAdmin($userId)) {
                $session->remove('active_clinic_role_id');
                log_message('info', 'Impersonation bypass for user_id={userId} clinic_id={clinicId}', [
                    'userId' => $userId,
                    'clinicId' => $activeClinicId,
                ]);
                return null;
            }
        }

        // 3. Invalid Context
        if (!$membership) {
            $session->remove(['active_clinic_id', 'active_clinic_role_id', 'active_clinic_membership_id']);
            
            if ($request->isAJAX() || $isApi) {
                 return service('response')->setStatusCode(403)->setJSON([
                     'error' => [
                         'code' => 'tenant_context_forbidden',
                         'message' => 'Access denied to this clinic.'
                     ]
                 ]);
            }
            
            // HTML fallback
            $session->setFlashdata('error', 'Access denied to this clinic.');
            return redirect()->to('/clinic/select');
        }

        // 4. Enforce Role Consistency
        // We trust the DB over the session for role_id
        if ($session->get('active_clinic_role_id') != $membership['role_id']) {
            $session->set('active_clinic_role_id', $membership['role_id']);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
