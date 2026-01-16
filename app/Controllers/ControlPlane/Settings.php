<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Services\ControlPlaneAuditService;

class Settings extends BaseController
{
    protected $auditService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
    }
    /**
     * GET /controlplane/settings
     */
    public function index()
    {
        // Global mode check handled by 'controlplane' filter
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/settings'
        ]);
        return view('control_plane/settings', [
            'title' => 'Settings'
        ]);
    }
}
