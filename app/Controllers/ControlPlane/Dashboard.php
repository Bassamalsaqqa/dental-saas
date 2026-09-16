<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\ClinicSubscriptionModel;
use App\Models\ControlPlaneAuditModel;
use App\Models\FileAttachmentModel;
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
        $nowUtc = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $now = $nowUtc->format('Y-m-d H:i:s');
        $activeSubscriptions = (new ClinicSubscriptionModel())
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
        $activeSubscriptionRows = (new ClinicSubscriptionModel())
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
            ->findAll();
        $planModel = new PlanModel();
        $activePlans = $planModel->where('status', 'active')->countAllResults();
        $userModel = new UserModel();
        $activeUsers = $userModel->where('active', 1)->countAllResults();
        $thirtyDaysThreshold = $nowUtc->modify('+30 days')->format('Y-m-d H:i:s');
        $expiringCount = (new ClinicSubscriptionModel())
            ->withoutTenantScope()
            ->where('status', 'active')
            ->groupStart()
                ->where('start_at IS NULL', null, false)
                ->orWhere('start_at <=', $now)
            ->groupEnd()
            ->where('end_at >=', $now)
            ->where('end_at <=', $thirtyDaysThreshold)
            ->countAllResults();

        $mrr = 0.0;
        foreach ($activeSubscriptionRows as $subRow) {
            if (empty($subRow['plan_id'])) {
                continue;
            }
            $plan = $planModel->find($subRow['plan_id']);
            if (!$plan) {
                continue;
            }
            $limits = json_decode($plan['limits_json'] ?? '{}', true) ?? [];
            $price = (float) ($limits['price'] ?? 0);
            $duration = $limits['duration'] ?? 'monthly';
            if ($duration === 'yearly' && $price > 0) {
                $mrr += ($price / 12);
            } elseif ($duration === 'monthly') {
                $mrr += $price;
            }
        }

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

        $clinicStatusRows = $clinicModel
            ->select('status, COUNT(*) as total')
            ->groupBy('status')
            ->findAll();
        $statusCounts = array_column($clinicStatusRows, 'total', 'status');

        $storageRows = (new FileAttachmentModel())
            ->withoutTenantScope()
            ->select('file_attachments.clinic_id, SUM(file_attachments.file_size) as total_size, clinics.name as clinic_name')
            ->join('clinics', 'clinics.id = file_attachments.clinic_id', 'left')
            ->groupBy('file_attachments.clinic_id')
            ->orderBy('total_size', 'DESC')
            ->findAll();
        $storageTotalBytes = 0;
        foreach ($storageRows as $row) {
            $storageTotalBytes += (int) ($row['total_size'] ?? 0);
        }
        $storageTop = array_slice($storageRows, 0, 5);

        $events7d = $auditModel->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))->countAllResults();
        $events30d = $auditModel->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))->countAllResults();
        $lastEvent = $auditModel->selectMax('created_at', 'last_event')->first();
        $lastEventAt = $lastEvent['last_event'] ?? null;

        $planMrrCache = [];
        $mrrHistory = [];
        $currentMonthStart = $nowUtc->modify('first day of this month 00:00:00');
        for ($i = 5; $i >= 0; $i--) {
            $monthAnchor = $currentMonthStart->modify('-' . $i . ' months');
            $monthStart = $monthAnchor->format('Y-m-01 00:00:00');
            $monthEnd = $monthAnchor->format('Y-m-t 23:59:59');

            $monthlySubscriptions = (new ClinicSubscriptionModel())
                ->withoutTenantScope()
                ->where('status', 'active')
                ->groupStart()
                    ->where('start_at IS NULL', null, false)
                    ->orWhere('start_at <=', $monthEnd)
                ->groupEnd()
                ->groupStart()
                    ->where('end_at IS NULL', null, false)
                    ->orWhere('end_at >', $monthStart)
                ->groupEnd()
                ->findAll();

            $monthlyTotal = 0.0;
            foreach ($monthlySubscriptions as $subRow) {
                $planId = (int) ($subRow['plan_id'] ?? 0);
                if ($planId <= 0) {
                    continue;
                }

                if (!isset($planMrrCache[$planId])) {
                    $plan = $planModel->find($planId);
                    if (!$plan) {
                        $planMrrCache[$planId] = 0.0;
                        continue;
                    }
                    $limits = json_decode($plan['limits_json'] ?? '{}', true) ?? [];
                    $price = (float) ($limits['price'] ?? 0);
                    $duration = $limits['duration'] ?? 'monthly';
                    $monthlyAmount = ($duration === 'yearly' && $price > 0) ? ($price / 12) : $price;
                    $planMrrCache[$planId] = round($monthlyAmount, 2);
                }

                $monthlyTotal += $planMrrCache[$planId];
            }

            $mrrHistory[] = [
                'label' => $monthAnchor->format('M'),
                'value' => round($monthlyTotal, 2),
            ];
        }

        $planRequestModel = (new PlanRequestModel())->withoutTenantScope();
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

        $planDistributionRows = (new ClinicSubscriptionModel())
            ->withoutTenantScope()
            ->select('plan_id, COUNT(*) as total')
            ->where('status', 'active')
            ->groupStart()
                ->where('start_at IS NULL', null, false)
                ->orWhere('start_at <=', $now)
            ->groupEnd()
            ->groupStart()
                ->where('end_at IS NULL', null, false)
                ->orWhere('end_at >', $now)
            ->groupEnd()
            ->groupBy('plan_id')
            ->findAll();
        $planDistribution = [];
        foreach ($planDistributionRows as $row) {
            $planId = (int) ($row['plan_id'] ?? 0);
            if ($planId <= 0) {
                continue;
            }
            $plan = $planModel->find($planId);
            $planDistribution[] = [
                'label' => $plan['name'] ?? ('Plan #' . $planId),
                'value' => (int) ($row['total'] ?? 0),
            ];
        }

        $clinicGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = date('Y-m-01 00:00:00', strtotime('-' . $i . ' months'));
            $monthEnd = date('Y-m-t 23:59:59', strtotime('-' . $i . ' months'));
            $count = (new \App\Models\ClinicModel())
                ->where('created_at >=', $monthStart)
                ->where('created_at <=', $monthEnd)
                ->countAllResults();
            $clinicGrowth[] = [
                'label' => date('M', strtotime('-' . $i . ' months')),
                'value' => $count,
            ];
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
                'expiring_subscriptions' => $expiringCount,
                'mrr' => $mrr,
                'pending_requests' => $pendingRequests,
                'storage_total_bytes' => $storageTotalBytes,
                'events_7d' => $events7d,
                'events_30d' => $events30d,
                'last_event_at' => $lastEventAt,
            ],
            'recent_clinics' => $recentClinics,
            'recent_plan_requests' => $recentRequests,
            'activity_series' => $activitySeries,
            'storage_top' => $storageTop,
            'chart_data' => [
                'mrr_series' => $mrrHistory,
                'activity_series' => $activitySeries,
                'plan_distribution' => $planDistribution,
                'clinic_growth' => $clinicGrowth,
                'status_breakdown' => [
                    'active' => (int) ($statusCounts['active'] ?? 0),
                    'suspended' => (int) ($statusCounts['suspended'] ?? 0),
                    'trial' => (int) ($statusCounts['trial'] ?? 0),
                ],
            ],
        ];

        return view('control_plane/dashboard', $data);
    }
}
