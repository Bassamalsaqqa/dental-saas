<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Services\ControlPlaneAuditService;

class AdminTools extends BaseController
{
    protected $auditService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
    }

    /**
     * GET /controlplane/admin-tools
     */
    public function index()
    {
        // Global mode check handled by 'controlplane' filter
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/admin-tools',
        ]);

        return view('control_plane/admin_tools', [
            'title' => 'Admin Tools',
        ]);
    }
}
