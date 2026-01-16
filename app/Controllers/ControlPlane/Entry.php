<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Services\ControlPlaneAuditService;

class Entry extends BaseController
{
    protected $auditService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
    }
    /**
     * Canonical Control Plane Entry
     * GET /controlplane
     */
    public function index()
    {
        $ionAuth = new \App\Libraries\IonAuth();

        // 1. Auth Check
        if (!$ionAuth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        // 2. Super Admin Check (Fail Closed 404)
        $permissionService = service('permission');
        $userId = $ionAuth->getUserId();
        
        // Handle potential service null (defensive)
        if (!$permissionService) {
             $permissionService = new \App\Services\PermissionService();
        }

        if (!$permissionService->isSuperAdmin($userId)) {
             throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 3. Global Mode Redirect
        if (session()->get('global_mode')) {
            $this->auditService->logEvent('surface_get', [
                'route' => '/controlplane'
            ]);
            return redirect()->to('/controlplane/dashboard');
        }

        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane'
        ]);

        // 4. Render Entry Page
        return view('control_plane/entry', [
            'title' => 'Entry',
            'hide_nav' => true
        ]);
    }
}
