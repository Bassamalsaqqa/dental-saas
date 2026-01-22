<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\ClinicSubscriptionModel;
use App\Models\PlanModel;
use App\Models\UserModel;
use App\Services\ControlPlaneAuditService;

class Dashboard extends BaseController
{
    protected $auditService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
    }
    /**
     * Enforce Superadmin Global Mode check
     */
    private function ensureGlobalMode()
    {
        if (!session()->get('global_mode')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access Denied');
        }
    }

    public function index()
    {
        $this->ensureGlobalMode();

        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/dashboard'
        ]);

        $clinicModel = new \App\Models\ClinicModel();
        $activeClinics = $clinicModel->where('status', 'active')->countAllResults();
        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $activeSubscriptions = $subscriptionModel->where('status', 'active')->countAllResults();
        $planModel = new PlanModel();
        $activePlans = $planModel->where('status', 'active')->countAllResults();
        $userModel = new UserModel();
        $activeUsers = $userModel->where('active', 1)->countAllResults();

        $data = [
            'title' => 'Control Plane Dashboard',
            'global_mode' => true,
            'stats' => [
                'active_clinics' => $activeClinics,
                'active_subscriptions' => $activeSubscriptions,
                'active_plans' => $activePlans,
                'active_users' => $activeUsers
            ]
        ];

        return view('control_plane/dashboard', $data);
    }
}
