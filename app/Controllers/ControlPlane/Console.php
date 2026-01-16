<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\PlanModel;
use App\Models\ClinicModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\PlanAuditModel;
use App\Models\NotificationModel;

class Console extends BaseController
{
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

        $planModel = new PlanModel();
        $clinicModel = new ClinicModel();
        $subscriptionModel = new ClinicSubscriptionModel();
        $planAuditModel = new PlanAuditModel();
        $notificationModel = new NotificationModel();

        // 1. Governance KPIs
        $plansActive = $planModel->where('status', 'active')->countAllResults();
        $plansInactive = $planModel->where('status !=', 'active')->countAllResults();
        
        $clinicsTotal = $clinicModel->countAllResults();
        
        $subscriptionsActive = $subscriptionModel->where('status', 'active')->countAllResults();

        // 2. Recent Governance Events (Last 10 audits)
        $recentAudits = $planAuditModel->orderBy('id', 'DESC')->findAll(10);

        // 3. Notifications Observability (Last 10 ledger entries)
        $recentNotifications = $notificationModel->orderBy('id', 'DESC')->findAll(10);

        return view('control_plane/console', [
            'plansActive' => $plansActive,
            'plansInactive' => $plansInactive,
            'clinicsTotal' => $clinicsTotal,
            'subscriptionsActive' => $subscriptionsActive,
            'recentAudits' => $recentAudits,
            'recentNotifications' => $recentNotifications,
        ]);
    }
}
