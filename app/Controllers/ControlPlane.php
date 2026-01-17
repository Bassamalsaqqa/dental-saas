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

        // Strict Super Admin Check
        if (!$this->permissionService->isSuperAdmin($userId)) {
             throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Set Global Mode
        session()->set('global_mode', true);
        
        // Clear Tenant Context
        session()->remove('active_clinic_id');
        session()->remove('impersonated_clinic_id');

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
        
        // Regenerate Session ID
        session()->regenerate();

        $this->auditService->logEvent('global_exit', [
            'route' => '/controlplane/exit'
        ]);

        return redirect()->to('/dashboard');
    }
}
