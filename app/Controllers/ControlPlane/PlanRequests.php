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

        $planRequestModel = new PlanRequestModel();
        $clinicModel = new ClinicModel();
        $planModel = new PlanModel();

        $requests = $planRequestModel->orderBy('created_at', 'DESC')->findAll();
        $rows = [];

        foreach ($requests as $request) {
            $clinic = $clinicModel->find($request['clinic_id']);
            $plan = $planModel->find($request['requested_plan_id']);
            $rows[] = [
                'request' => $request,
                'clinic_name' => $clinic['name'] ?? 'Clinic',
                'plan_name' => $plan['name'] ?? 'Plan',
            ];
        }

        $clinics = $clinicModel->orderBy('name', 'ASC')->findAll();
        $plans = $planModel->where('status', 'active')->orderBy('name', 'ASC')->findAll();

        return view('control_plane/plan_requests', [
            'title' => 'Plan Requests',
            'rows' => $rows,
            'clinics' => $clinics,
            'plans' => $plans,
        ]);
    }

    /**
     * POST /controlplane/plan-requests
     */
    public function store()
    {
        $request = service('request');
        $post = $request ? $request->getPost() : [];

        $planRequestModel = new PlanRequestModel();
        $clinicId = (int) ($post['clinic_id'] ?? 0);
        $requestedPlanId = (int) ($post['requested_plan_id'] ?? 0);
        $duration = $post['requested_duration'] ?? 'monthly';

        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $current = $subscriptionModel->getCurrentSubscription($clinicId);

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

        return redirect()->to(base_url('controlplane/plan-requests'))->with('success', 'Plan request created.');
    }

    /**
     * POST /controlplane/plan-requests/{id}/approve
     */
    public function approve(int $id)
    {
        $planRequestModel = new PlanRequestModel();
        $request = $planRequestModel->find($id);
        if (!$request || ($request['status'] ?? '') !== 'pending') {
            return redirect()->to(base_url('controlplane/plan-requests'))->with('error', 'Request not found or already handled.');
        }

        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $current = $subscriptionModel->getCurrentSubscription((int) $request['clinic_id']);
        if ($current) {
            $subscriptionModel->update($current['id'], [
                'status' => 'canceled',
                'canceled_at' => date('Y-m-d H:i:s'),
                'end_at' => date('Y-m-d H:i:s'),
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

        return redirect()->to(base_url('controlplane/plan-requests'))->with('success', 'Plan request approved.');
    }

    /**
     * POST /controlplane/plan-requests/{id}/reject
     */
    public function reject(int $id)
    {
        $planRequestModel = new PlanRequestModel();
        $request = $planRequestModel->find($id);
        if (!$request || ($request['status'] ?? '') !== 'pending') {
            return redirect()->to(base_url('controlplane/plan-requests'))->with('error', 'Request not found or already handled.');
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

        return redirect()->to(base_url('controlplane/plan-requests'))->with('success', 'Plan request rejected.');
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
