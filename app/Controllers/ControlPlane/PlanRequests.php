<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\ClinicModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\PlanModel;
use App\Models\PlanRequestModel;
use App\Services\ControlPlaneAuditService;
use App\Services\ControlPlaneSubscriptionService;

class PlanRequests extends BaseController
{
    protected $auditService;
    protected $subscriptionService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
        $this->subscriptionService = new ControlPlaneSubscriptionService();
    }

    public function setSubscriptionService(ControlPlaneSubscriptionService $subscriptionService): void
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function setAuditService(ControlPlaneAuditService $auditService): void
    {
        $this->auditService = $auditService;
        $this->subscriptionService = new ControlPlaneSubscriptionService(auditService: $auditService);
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

        return view('control_plane/plan_requests', [
            'title' => 'Plan Requests',
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
        $expectedPlanId = $this->request->getPost('expected_requested_plan_id') !== null && $this->request->getPost('expected_requested_plan_id') !== ''
            ? (int) $this->request->getPost('expected_requested_plan_id')
            : null;
        $expectedDuration = $this->request->getPost('expected_requested_duration') !== null && $this->request->getPost('expected_requested_duration') !== ''
            ? (string) $this->request->getPost('expected_requested_duration')
            : null;
        $actorUserId = (int) (session()->get('user_id') ?? 0);

        $result = $this->subscriptionService->approvePlanRequest(
            $id,
            $actorUserId,
            $expectedPlanId,
            $expectedDuration,
            '/controlplane/plan-requests/' . $id . '/approve'
        );

        if (!$result['success']) {
            return redirect()->to(base_url('controlplane/plans'))->with('error', $this->formatLocalizedMessage($result['code'], 'Failed to approve plan request.'));
        }

        return redirect()->to(base_url('controlplane/plans'))->with('success', $this->formatLocalizedMessage('plan_request_approved', 'Plan request approved.'));
    }

    private function formatLocalizedMessage(string $code, string $defaultFallback): string
    {
        $langKey = 'UI.subscriptionCode_' . $code;
        $locale = $this->request ? $this->request->getLocale() : (service('request') ? service('request')->getLocale() : null);
        $translated = lang($langKey, [], $locale);
        return ($translated !== $langKey && !empty($translated)) ? $translated : $defaultFallback;
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

    /**
     * POST /controlplane/plan-requests/{id}/delete
     */
    public function delete(int $id)
    {
        $planRequestModel = (new PlanRequestModel())->withoutTenantScope();
        $request = $planRequestModel->find($id);
        if (!$request) {
            return redirect()->to(base_url('controlplane/plan-requests'))->with('error', 'Request not found.');
        }

        if (($request['status'] ?? '') === 'pending') {
            return redirect()->to(base_url('controlplane/plan-requests'))->with('error', 'Cannot delete a pending request. Approve or reject it first.');
        }

        $planRequestModel->delete($id);

        $this->auditService->logEvent('plan_request_delete', [
            'route' => '/controlplane/plan-requests/' . $id . '/delete',
            'method' => 'POST',
            'metadata' => ['request_id' => $id, 'clinic_id' => $request['clinic_id'] ?? null],
        ]);

        return redirect()->to(base_url('controlplane/plan-requests'))->with('success', 'Plan request deleted.');
    }

    private function resolveEndAt(string $duration, ?string $startAt): string|false|null
    {
        return ClinicSubscriptionModel::resolveEndAt($duration, $startAt);
    }
}
