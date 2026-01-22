<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Services\ControlPlaneAuditService;

class Plans extends BaseController
{
    protected $auditService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
    }

    /**
     * GET /controlplane/plans
     */
    public function index()
    {
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/plans',
        ]);

        return view('control_plane/plans', [
            'title' => 'Plans',
        ]);
    }
}
