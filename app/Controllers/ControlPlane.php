<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\ControlPlaneAuditService;

class ControlPlane extends BaseController
{
    protected $permissionService;
    protected $ionAuth;
    protected $auditService;

    public function __construct()
    {
        $this->permissionService = service('permission');
        $this->ionAuth = new \App\Libraries\IonAuth();
        $this->auditService = new ControlPlaneAuditService();
    }

    /**
     * Enter Control Plane (Global Mode)
     * POST /controlplane/enter
     */
    public function enter()
    {
        if (!$this->ionAuth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $userId = $this->ionAuth->getUserId();

        // Strict Super Admin Check (fallback to IonAuth admin flag)
        $isSuperAdmin = $this->permissionService && $this->permissionService->isSuperAdmin($userId);
        if (!$isSuperAdmin && $this->ionAuth->isAdmin()) {
            $isSuperAdmin = true;
        }
        if (!$isSuperAdmin) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Set Global Mode
        session()->set('global_mode', true);
        
        // Clear Tenant Context
        session()->remove('active_clinic_id');
        session()->remove('active_clinic_role_id');
        session()->remove('impersonated_clinic_id');
        session()->remove('is_impersonating');

        // Regenerate Session ID for security
        session()->regenerate();

        $this->auditService->logEvent('global_enter', [
            'route' => '/controlplane/enter'
        ]);

        return redirect()->to('/controlplane/dashboard');
    }

    /**
     * Exit Control Plane
     * POST /controlplane/exit
     */
    public function exit()
    {
        if (!$this->ionAuth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        // Disable Global Mode
        session()->set('global_mode', false);
        session()->remove('impersonated_clinic_id');
        session()->remove('is_impersonating');
        
        // Regenerate Session ID
        session()->regenerate();

        $this->auditService->logEvent('global_exit', [
            'route' => '/controlplane/exit'
        ]);

        return redirect()->to('/dashboard');
    }

    /**
     * Impersonate a clinic (leave global mode)
     * POST /controlplane/impersonate
     */
    public function impersonate()
    {
        if (!$this->ionAuth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $userId = $this->ionAuth->getUserId();
        $isSuperAdmin = $this->permissionService && $this->permissionService->isSuperAdmin($userId);
        if (!$isSuperAdmin && $this->ionAuth->isAdmin()) {
            $isSuperAdmin = true;
        }
        if (!$isSuperAdmin) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $clinicId = (int) $this->request->getPost('clinic_id');
        $clinicModel = new \App\Models\ClinicModel();
        $clinic = $clinicModel->find($clinicId);
        if (!$clinic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Clinic not found');
        }

        session()->set('global_mode', false);
        session()->set('active_clinic_id', $clinicId);
        session()->set('impersonated_clinic_id', $clinicId);
        session()->set('is_impersonating', true);
        session()->remove('active_clinic_role_id');
        session()->regenerate();

        $this->auditService->logEvent('impersonation_start', [
            'route' => '/controlplane/impersonate',
            'metadata' => [
                'clinic_id' => $clinicId,
            ],
        ]);

        return redirect()->to('/dashboard')->with('success', 'Impersonation active for ' . ($clinic['name'] ?? 'clinic') . '.');
    }
}
