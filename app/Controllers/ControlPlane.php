<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ControlPlane extends BaseController
{
    protected $permissionService;
    protected $ionAuth;

    public function __construct()
    {
        $this->permissionService = service('permission');
        $this->ionAuth = new \App\Libraries\IonAuth();
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
             // 403 Forbidden
             return response()->setStatusCode(403)->setBody('Unauthorized: Super Admin privileges required.');
        }

        // Set Global Mode
        session()->set('global_mode', true);
        
        // Clear Tenant Context
        session()->remove('active_clinic_id');
        session()->remove('impersonated_clinic_id');

        // Regenerate Session ID for security
        session()->regenerate();

        return redirect()->to('/settings');
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

        return redirect()->to('/dashboard');
    }
}
