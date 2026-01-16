<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Services\ControlPlaneAuditService;

class Operations extends BaseController
{
    protected $auditService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
    }
    /**
     * GET /controlplane/operations
     */
    public function index()
    {
        // Global mode check handled by 'controlplane' filter
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/operations'
        ]);
        return view('control_plane/operations', [
            'title' => 'Operations'
        ]);
    }
}
