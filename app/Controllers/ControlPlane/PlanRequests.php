<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Services\ControlPlaneAuditService;

class PlanRequests extends BaseController
{
    protected $auditService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
    }

    /**
     * GET /controlplane/plan-requests
     */
    public function index()
    {
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/plan-requests',
        ]);

        return view('control_plane/plan_requests', [
            'title' => 'Plan Requests',
        ]);
    }
}
