<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\PlanModel;
use App\Models\ClinicModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\PlanAuditModel;
use App\Models\NotificationModel;
use App\Services\ControlPlaneAuditService;

class Console extends BaseController
{
    protected $auditService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
    }
    /**
     * Display Operator Console
     * GET /controlplane/console
     *
     * @return string
     */
    public function index()
    {
        // Global mode is strictly enforced by the 'controlplane' filter.
        // If not in global mode, the filter throws a 404.
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/console'
        ]);

        return redirect()->to(base_url('controlplane/operations'));
    }
}
