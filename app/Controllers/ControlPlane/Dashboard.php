<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\ClinicSubscriptionModel;
use App\Models\ControlPlaneAuditModel;
use App\Models\PlanModel;
use App\Models\PlanRequestModel;
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
        $totalClinics = $clinicModel->countAllResults();
        $activeClinics = $clinicModel->where('status', 'active')->countAllResults();
        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $activeSubscriptions = $subscriptionModel->where('status', 'active')->countAllResults();
        $planModel = new PlanModel();
        $activePlans = $planModel->where('status', 'active')->countAllResults();
        $userModel = new UserModel();
        $activeUsers = $userModel->where('active', 1)->countAllResults();

        $recentClinics = $clinicModel
            ->select('id, name, status, created_at')
            ->orderBy('created_at', 'DESC')
            ->limit(3)
            ->find();

        $auditModel = new ControlPlaneAuditModel();
        $startDate = date('Y-m-d 00:00:00', strtotime('-13 days'));
        $activityRows = $auditModel
            ->select('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at >=', $startDate)
            ->groupBy('DATE(created_at)')
            ->orderBy('DATE(created_at)', 'ASC')
            ->findAll();

        $activityTotals = [];
        foreach ($activityRows as $row) {
            $activityTotals[$row['day']] = (int) $row['total'];
        }

        $activitySeries = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime('-' . $i . ' days'));
            $activitySeries[] = [
                'day' => $day,
                'total' => $activityTotals[$day] ?? 0,
            ];
        }

        $planRequestModel = new PlanRequestModel();
        $pendingRequests = $planRequestModel->where('status', 'pending')->countAllResults();
        $recentRequestsRaw = $planRequestModel->orderBy('created_at', 'DESC')->limit(3)->findAll();
        $recentRequests = [];

        if (!empty($recentRequestsRaw)) {
            $planModel = new PlanModel();
            foreach ($recentRequestsRaw as $request) {
                $clinic = $clinicModel->find($request['clinic_id']);
                $plan = $planModel->find($request['requested_plan_id']);
                $recentRequests[] = [
                    'clinic_name' => $clinic['name'] ?? 'Clinic',
                    'summary' => ($plan['name'] ?? 'Plan') . ' • ' . ucfirst($request['requested_duration'] ?? 'monthly'),
                    'status' => $request['status'] ?? 'pending',
                ];
            }
        }

        $data = [
            'title' => 'Control Plane Dashboard',
            'global_mode' => true,
            'stats' => [
                'total_clinics' => $totalClinics,
                'active_clinics' => $activeClinics,
                'active_subscriptions' => $activeSubscriptions,
                'active_plans' => $activePlans,
                'active_users' => $activeUsers,
                'pending_requests' => $pendingRequests,
            ],
            'recent_clinics' => $recentClinics,
            'recent_plan_requests' => $recentRequests,
            'activity_series' => $activitySeries,
        ];

        return view('control_plane/dashboard', $data);
    }
}
