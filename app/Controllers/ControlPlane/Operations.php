<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\PlanModel;
use App\Models\ClinicModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\ControlPlaneAuditModel;
use App\Models\PlanAuditModel;
use App\Models\NotificationModel;
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
        $planModel = new PlanModel();
        $clinicModel = new ClinicModel();
        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $controlPlaneAuditModel = new ControlPlaneAuditModel();
        $planAuditModel = (new PlanAuditModel())->withoutTenantScope();
        $notificationModel = (new NotificationModel())->withoutTenantScope();

        $plansActive = $planModel->where('status', 'active')->countAllResults();
        $plansInactive = $planModel->where('status !=', 'active')->countAllResults();
        $clinicsTotal = $clinicModel->countAllResults();
        $now = date('Y-m-d H:i:s');
        $subscriptionsActive = (new ClinicSubscriptionModel())
            ->withoutTenantScope()
            ->where('status', 'active')
            ->groupStart()
                ->where('start_at IS NULL', null, false)
                ->orWhere('start_at <=', $now)
            ->groupEnd()
            ->groupStart()
                ->where('end_at IS NULL', null, false)
                ->orWhere('end_at >', $now)
            ->groupEnd()
            ->countAllResults();

        $controlPlaneAudits = $controlPlaneAuditModel->orderBy('id', 'DESC')->findAll(10);
        $planAudits = $planAuditModel->orderBy('id', 'DESC')->findAll(10);
        $recentNotifications = $notificationModel->orderBy('id', 'DESC')->findAll(10);

        return view('control_plane/operations', [
            'title' => 'Operations',
            'plansActive' => $plansActive,
            'plansInactive' => $plansInactive,
            'clinicsTotal' => $clinicsTotal,
            'subscriptionsActive' => $subscriptionsActive,
            'controlPlaneAudits' => $controlPlaneAudits,
            'planAudits' => $planAudits,
            'recentNotifications' => $recentNotifications,
        ]);
    }
}
