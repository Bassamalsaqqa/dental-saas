<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\ClinicModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\PlanModel;
use App\Models\PlanRequestModel;
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

        $request = service('request');
        $statusFilter = strtolower(trim((string) ($request ? $request->getGet('request_status') : '')));
        $search = trim((string) ($request ? $request->getGet('request_q') : ''));

        $planRequestModel = (new PlanRequestModel())->withoutTenantScope();
        $clinicModel = new ClinicModel();
        $planModel = new PlanModel();

        $requestQuery = $planRequestModel
            ->select('plan_requests.*, clinics.name as clinic_name, requested_plan.name as requested_plan_name, current_plan.name as current_plan_name')
            ->join('clinics', 'clinics.id = plan_requests.clinic_id', 'left')
            ->join('plans as requested_plan', 'requested_plan.id = plan_requests.requested_plan_id', 'left')
            ->join('plans as current_plan', 'current_plan.id = plan_requests.current_plan_id', 'left')
            ->orderBy('plan_requests.created_at', 'DESC');

        if (in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
            $requestQuery = $requestQuery->where('plan_requests.status', $statusFilter);
        }
        if ($search !== '') {
            $requestQuery = $requestQuery->groupStart()
                ->like('clinics.name', $search)
                ->orLike('requested_plan.name', $search)
                ->orLike('current_plan.name', $search)
                ->groupEnd();
        }

        $requests = $requestQuery->findAll();
        $totalRequests = $planRequestModel->countAllResults();
        $pendingRequests = (new PlanRequestModel())->withoutTenantScope()->where('status', 'pending')->countAllResults();
        $approvedRequests = (new PlanRequestModel())->withoutTenantScope()->where('status', 'approved')->countAllResults();
        $rejectedRequests = (new PlanRequestModel())->withoutTenantScope()->where('status', 'rejected')->countAllResults();

        $plans = $planModel->orderBy('id', 'DESC')->findAll();
        $cards = [];
        foreach ($plans as $plan) {
            $limits = json_decode($plan['limits_json'] ?? '{}', true) ?? [];
            $features = json_decode($plan['features_json'] ?? '{}', true) ?? [];

            $cards[] = [
                'plan' => $plan,
                'limits' => $limits,
                'features' => $features,
            ];
        }

        return view('control_plane/plans', [
            'title' => 'Plans & Requests',
            'plans' => $cards,
            'planRequests' => $requests,
            'requestClinics' => $clinicModel->orderBy('name', 'ASC')->findAll(),
            'requestPlans' => $planModel->where('status', 'active')->orderBy('name', 'ASC')->findAll(),
            'requestStats' => [
                'total' => $totalRequests,
                'pending' => $pendingRequests,
                'approved' => $approvedRequests,
                'rejected' => $rejectedRequests,
                'filtered' => count($requests),
            ],
            'requestFilters' => [
                'status' => $statusFilter,
                'q' => $search,
            ],
        ]);
    }

    /**
     * POST /controlplane/plan-requests
     */
    public function store()
    {
        $request = service('request');
        $post = $request ? $request->getPost() : [];

        $planRequestModel = (new PlanRequestModel())->withoutTenantScope();
        $clinicId = (int) ($post['clinic_id'] ?? 0);
        $requestedPlanId = (int) ($post['requested_plan_id'] ?? 0);
        $duration = $post['requested_duration'] ?? 'monthly';

        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $current = $subscriptionModel->getNormalizedSubscription($clinicId);

        $planRequestModel->insert([
            'clinic_id' => $clinicId,
            'current_plan_id' => $current['plan_id'] ?? null,
            'requested_plan_id' => $requestedPlanId,
            'requested_duration' => in_array($duration, ['monthly', 'yearly', 'lifetime'], true) ? $duration : 'monthly',
            'status' => 'pending',
            'requested_by' => session()->get('user_id') ?? null,
        ]);

        $this->auditService->logEvent('plan_request_create', [
            'route' => '/controlplane/plan-requests',
            'method' => 'POST',
            'metadata' => ['clinic_id' => $clinicId, 'requested_plan_id' => $requestedPlanId],
        ]);

        return redirect()->to(base_url('controlplane/plans'))->with('success', 'Plan request created.');
    }

    /**
     * POST /controlplane/plan-requests/{id}/approve
     */
    public function approve(int $id)
    {
        $planRequestModel = (new PlanRequestModel())->withoutTenantScope();
        $request = $planRequestModel->find($id);
        if (!$request || ($request['status'] ?? '') !== 'pending') {
            return redirect()->to(base_url('controlplane/plans'))->with('error', 'Request not found or already handled.');
        }

        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $current = $subscriptionModel->getNormalizedSubscription((int) $request['clinic_id']);
        if ($current && in_array(($current['status'] ?? ''), ['active', 'paused'], true)) {
            $now = date('Y-m-d H:i:s');
            $subscriptionModel->update($current['id'], [
                'status' => 'canceled',
                'canceled_at' => $now,
                'end_at' => $now,
                'canceled_reason' => 'plan_change',
            ]);
        }

        $duration = $request['requested_duration'] ?? 'monthly';
        $startAt = date('Y-m-d H:i:s');
        $endAt = $this->resolveEndAt($duration, $startAt);

        $subscriptionModel->insert([
            'clinic_id' => $request['clinic_id'],
            'plan_id' => $request['requested_plan_id'],
            'status' => 'active',
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);

        $planRequestModel->update($id, [
            'status' => 'approved',
            'reviewed_by' => session()->get('user_id') ?? null,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);

        $this->auditService->logEvent('plan_request_approve', [
            'route' => '/controlplane/plan-requests/' . $id . '/approve',
            'method' => 'POST',
            'metadata' => ['request_id' => $id, 'clinic_id' => $request['clinic_id']],
        ]);

        return redirect()->to(base_url('controlplane/plans'))->with('success', 'Plan request approved.');
    }

    /**
     * POST /controlplane/plan-requests/{id}/reject
     */
    public function reject(int $id)
    {
        $planRequestModel = (new PlanRequestModel())->withoutTenantScope();
        $request = $planRequestModel->find($id);
        if (!$request || ($request['status'] ?? '') !== 'pending') {
            return redirect()->to(base_url('controlplane/plans'))->with('error', 'Request not found or already handled.');
        }

        $reason = trim((string) $this->request->getPost('decision_reason'));

        $planRequestModel->update($id, [
            'status' => 'rejected',
            'reviewed_by' => session()->get('user_id') ?? null,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'decision_reason' => $reason !== '' ? $reason : null,
        ]);

        $this->auditService->logEvent('plan_request_reject', [
            'route' => '/controlplane/plan-requests/' . $id . '/reject',
            'method' => 'POST',
            'metadata' => ['request_id' => $id, 'clinic_id' => $request['clinic_id']],
        ]);

        return redirect()->to(base_url('controlplane/plans'))->with('success', 'Plan request rejected.');
    }

    private function resolveEndAt(string $duration, string $startAt): ?string
    {
        if ($duration === 'lifetime') {
            return null;
        }

        $time = strtotime($startAt);
        if ($duration === 'yearly') {
            return date('Y-m-d H:i:s', strtotime('+1 year', $time));
        }

        return date('Y-m-d H:i:s', strtotime('+1 month', $time));
    }
}
