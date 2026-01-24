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
     * Exit impersonation and return to Control Plane
     * POST /controlplane/impersonation/exit
     */
    public function exitImpersonation()
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

        session()->set('global_mode', true);
        session()->remove('active_clinic_id');
        session()->remove('active_clinic_role_id');
        session()->remove('impersonated_clinic_id');
        session()->remove('is_impersonating');
        session()->regenerate();

        $this->auditService->logEvent('impersonation_end', [
            'route' => '/controlplane/impersonation/exit'
        ]);

        return redirect()->to('/controlplane/dashboard')->with('success', 'Exited impersonation.');
    }
}
