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
        $activeClinicId = $session->get('active_clinic_id');

        // 1. Missing Context
        if (!$activeClinicId) {
            // Detect API/AJAX
            if ($request->isAJAX() || strpos($request->getUri()->getPath(), 'api') !== false) {
                 $response = service('response');
                 return $response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }
            return redirect()->to('/clinic/select');
        }

        // 2. Validate Membership
        $db = \Config\Database::connect();
        // Use IonAuth strictly for user ID
        $ionAuth = new \App\Libraries\IonAuth();
        $userId = $ionAuth->getUserId();

        if (!$userId) {
             return redirect()->to('/auth/login');
        }

        $membership = $db->table('clinic_users')
            ->where('user_id', $userId)
            ->where('clinic_id', $activeClinicId)
            ->where('status', 'active')
            ->get()
            ->getRowArray();

        // 3. Invalid Context
        if (!$membership) {
            $session->remove(['active_clinic_id', 'active_clinic_role_id']);
            
            if ($request->isAJAX() || strpos($request->getUri()->getPath(), 'api') !== false) {
                 $response = service('response');
                 return $response->setStatusCode(403)->setJSON(['error' => 'INVALID_TENANT_CONTEXT']);
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