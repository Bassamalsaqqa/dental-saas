<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\ClinicModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\PlanModel;
use App\Services\ControlPlaneAuditService;

class Clinics extends BaseController
{
    protected $auditService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
    }

    /**
     * GET /controlplane/clinics
     */
    public function index()
    {
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/clinics',
        ]);

        $request = service('request');
        $isPost = strtoupper($this->request->getMethod()) === 'POST';
        $statusFilter = strtolower(trim((string) ($request ? ($isPost ? $request->getPost('status') : $request->getGet('status')) : '')));
        $planFilter = (int) ($request ? ($isPost ? $request->getPost('plan_id') : $request->getGet('plan_id')) : 0);
        $search = trim((string) ($request ? ($isPost ? $request->getPost('q') : $request->getGet('q')) : ''));

        if ($isPost) {
            $this->auditService->logEvent('surface_get', [
                'route' => '/controlplane/clinics',
                'method' => 'POST',
            ]);

            $draw = (int) ($request ? $request->getPost('draw') : 0);
            $start = max(0, (int) ($request ? $request->getPost('start') : 0));
            $length = (int) ($request ? $request->getPost('length') : 10);

            $orderPayload = $request ? $request->getPost('order') : null;
            $columnsPayload = $request ? $request->getPost('columns') : null;
            $orderIndex = (int) ($orderPayload[0]['column'] ?? 0);
            $orderDir = strtolower((string) ($orderPayload[0]['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
            $orderColumn = is_array($columnsPayload) && isset($columnsPayload[$orderIndex]['data'])
                ? (string) $columnsPayload[$orderIndex]['data']
                : 'name';

            $clinicModel = new ClinicModel();
            $recordsTotal = $clinicModel->countAllResults();
            $rows = $this->buildClinicRows($statusFilter, $planFilter, $search);

            if ($orderColumn !== '') {
                usort($rows, function ($a, $b) use ($orderColumn, $orderDir) {
                    $aVal = $a[$orderColumn] ?? '';
                    $bVal = $b[$orderColumn] ?? '';

                    if ($orderColumn === 'mrr') {
                        $aVal = (float) $aVal;
                        $bVal = (float) $bVal;
                    }

                    if ($aVal === $bVal) {
                        return 0;
                    }

                    $result = $aVal < $bVal ? -1 : 1;
                    return $orderDir === 'asc' ? $result : -$result;
                });
            }

            $recordsFiltered = count($rows);
            $pageRows = $length > 0 ? array_slice($rows, $start, $length) : $rows;

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $pageRows,
                'csrf_token' => csrf_hash(),
            ]);
        }

        $clinicModel = new ClinicModel();
        $planModel = new PlanModel();
        $totalClinics = $clinicModel->countAllResults();
        $activeClinics = (new ClinicModel())->where('status', 'active')->countAllResults();
        $filteredCount = count($this->buildClinicRows($statusFilter, $planFilter, $search));

        return view('control_plane/clinics', [
            'title' => 'Clinics',
            'stats' => [
                'total' => $totalClinics,
                'active' => $activeClinics,
                'filtered' => $filteredCount,
            ],
            'filters' => [
                'status' => $statusFilter,
                'plan_id' => $planFilter,
                'q' => $search,
            ],
            'plans' => $planModel->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    private function buildClinicRows(string $statusFilter, int $planFilter, string $search): array
    {
        $clinicModel = new ClinicModel();
        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $planModel = new PlanModel();

        $planRows = $planModel->findAll();
        $planMap = [];
        foreach ($planRows as $plan) {
            $planMap[$plan['id']] = $plan;
        }

        $clinicQuery = $clinicModel->orderBy('created_at', 'DESC');
        if (in_array($statusFilter, ['active', 'inactive', 'suspended'], true)) {
            $clinicQuery = $clinicQuery->where('status', $statusFilter);
        }
        if ($search !== '') {
            if (ctype_digit($search)) {
                $clinicQuery = $clinicQuery->groupStart()
                    ->where('id', (int) $search)
                    ->orLike('name', $search)
                    ->orLike('slug', $search)
                    ->groupEnd();
            } else {
                $clinicQuery = $clinicQuery->groupStart()
                    ->like('name', $search)
                    ->orLike('slug', $search)
                    ->groupEnd();
            }
        }

        $clinics = $clinicQuery->findAll();
        $rows = [];

        foreach ($clinics as $clinic) {
            $subscription = $subscriptionModel->getNormalizedSubscription((int) $clinic['id']);
            $planName = 'Unassigned';
            $mrr = 0.0;
            $planId = $subscription['plan_id'] ?? null;

            if ($planId && isset($planMap[$planId])) {
                $plan = $planMap[$planId];
                $planName = $plan['name'] ?? 'Unassigned';
                $limits = json_decode($plan['limits_json'] ?? '{}', true) ?? [];
                $price = (float) ($limits['price'] ?? 0);
                $duration = $limits['duration'] ?? 'monthly';
                if ($duration === 'yearly' && $price > 0) {
                    $mrr = $price / 12;
                } elseif ($duration === 'monthly') {
                    $mrr = $price;
                }
            }

            if ($planFilter > 0 && (int) $planId !== $planFilter) {
                continue;
            }

            $rows[] = [
                'id' => $clinic['id'],
                'name' => $clinic['name'] ?? '',
                'slug' => $clinic['slug'] ?? '',
                'status' => $clinic['status'] ?? 'inactive',
                'plan_name' => $planName,
                'mrr' => $mrr,
            ];
        }

        return $rows;
    }

    /**
     * GET /controlplane/clinics/{id}
     */
    public function show(int $id)
    {
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/clinics/' . $id,
        ]);

        $clinicModel = new ClinicModel();
        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $planModel = new PlanModel();

        $clinic = $clinicModel->find($id);
        if (!$clinic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Clinic not found');
        }

        $subscription = $subscriptionModel->getNormalizedSubscription((int) $clinic['id']);
        $planName = 'Unassigned';
        if ($subscription && !empty($subscription['plan_id'])) {
            $plan = $planModel->find($subscription['plan_id']);
            if (!empty($plan['name'])) {
                $planName = $plan['name'];
            }
        }

        return view('control_plane/clinic_detail', [
            'title' => 'Clinic Overview',
            'clinic' => $clinic,
            'subscription' => $subscription,
            'plan_name' => $planName,
            'plans' => $planModel->where('status', 'active')->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    /**
     * POST /controlplane/clinics/{id}/subscription/start
     */
    public function startSubscription(int $id)
    {
        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($id);
        if (!$clinic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Clinic not found');
        }

        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $planModel = new PlanModel();
        $planId = (int) $this->request->getPost('plan_id');
        $duration = $this->request->getPost('duration') ?? 'monthly';

        if (!$planId || !$planModel->find($planId)) {
            return redirect()->back()->with('error', 'Select a valid plan.');
        }

        $current = $subscriptionModel->getNormalizedSubscription($id);
        if ($current && in_array($current['status'] ?? '', ['active', 'paused'], true)) {
            $now = date('Y-m-d H:i:s');
            $subscriptionModel->update($current['id'], [
                'status' => 'canceled',
                'canceled_at' => $now,
                'end_at' => $now,
                'canceled_reason' => 'superseded_by_new_subscription',
            ]);
        }

        $startAt = date('Y-m-d H:i:s');
        $endAt = $this->resolveEndAt($duration, $startAt);

        $subscriptionModel->insert([
            'clinic_id' => $id,
            'plan_id' => $planId,
            'status' => 'active',
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);

        $this->auditService->logEvent('subscription_start', [
            'route' => '/controlplane/clinics/' . $id . '/subscription/start',
            'method' => 'POST',
            'metadata' => ['clinic_id' => $id, 'plan_id' => $planId, 'duration' => $duration],
        ]);

        return redirect()->to(base_url('controlplane/clinics/' . $id))->with('success', 'Subscription started.');
    }

    /**
     * POST /controlplane/clinics/{id}/subscription/extend
     */
    public function extendSubscription(int $id)
    {
        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $subscription = $subscriptionModel->getNormalizedSubscription($id);
        if (!$subscription || ($subscription['status'] ?? '') !== 'active') {
            return redirect()->back()->with('error', 'No active subscription to extend.');
        }

        $duration = $this->request->getPost('duration') ?? 'monthly';
        $base = $subscription['end_at'] ?? date('Y-m-d H:i:s');
        $endAt = $this->resolveEndAt($duration, $base);

        $subscriptionModel->update($subscription['id'], [
            'end_at' => $endAt,
        ]);

        $this->auditService->logEvent('subscription_extend', [
            'route' => '/controlplane/clinics/' . $id . '/subscription/extend',
            'method' => 'POST',
            'metadata' => ['clinic_id' => $id, 'subscription_id' => $subscription['id'], 'duration' => $duration],
        ]);

        return redirect()->to(base_url('controlplane/clinics/' . $id))->with('success', 'Subscription extended.');
    }

    /**
     * POST /controlplane/clinics/{id}/subscription/cancel
     */
    public function cancelSubscription(int $id)
    {
        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $subscription = $subscriptionModel->getNormalizedSubscription($id);
        if (!$subscription || !in_array(($subscription['status'] ?? ''), ['active', 'paused'], true)) {
            return redirect()->back()->with('error', 'No active or paused subscription to cancel.');
        }

        $now = date('Y-m-d H:i:s');
        $subscriptionModel->update($subscription['id'], [
            'status' => 'canceled',
            'canceled_at' => $now,
            'end_at' => $now,
            'canceled_reason' => 'manual_cancel',
        ]);

        $this->auditService->logEvent('subscription_cancel', [
            'route' => '/controlplane/clinics/' . $id . '/subscription/cancel',
            'method' => 'POST',
            'metadata' => ['clinic_id' => $id, 'subscription_id' => $subscription['id'], 'reason' => 'manual_cancel'],
        ]);

        return redirect()->to(base_url('controlplane/clinics/' . $id))->with('success', 'Subscription canceled.');
    }

    /**
     * POST /controlplane/clinics/{id}/subscription/update-dates
     */
    public function updateSubscriptionDates(int $id)
    {
        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $subscription = $subscriptionModel->getNormalizedSubscription($id);
        if (!$subscription || !in_array(($subscription['status'] ?? ''), ['active', 'paused'], true)) {
            return redirect()->back()->with('error', 'No active or paused subscription to update.');
        }

        $startInput = trim((string) $this->request->getPost('start_at'));
        $endInput = trim((string) $this->request->getPost('end_at'));

        $startAt = $startInput !== '' ? $this->normalizeDateTime($startInput) : ($subscription['start_at'] ?? null);
        $endAt = $endInput !== '' ? $this->normalizeDateTime($endInput) : ($subscription['end_at'] ?? null);

        if ($startInput !== '' && !$startAt) {
            return redirect()->back()->with('error', 'Invalid start date.');
        }

        if ($endInput !== '' && !$endAt) {
            return redirect()->back()->with('error', 'Invalid end date.');
        }

        if ($startAt && $endAt && strtotime($endAt) <= strtotime($startAt)) {
            return redirect()->back()->with('error', 'End date must be after start date.');
        }

        $subscriptionModel->update($subscription['id'], [
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);

        $this->auditService->logEvent('subscription_dates_update', [
            'route' => '/controlplane/clinics/' . $id . '/subscription/update-dates',
            'method' => 'POST',
            'metadata' => ['clinic_id' => $id, 'subscription_id' => $subscription['id']],
        ]);

        return redirect()->to(base_url('controlplane/clinics/' . $id))->with('success', 'Subscription dates updated.');
    }

    /**
     * POST /controlplane/clinics/{id}/terminate
     */
    public function terminate(int $id)
    {
        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($id);
        if (!$clinic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Clinic not found');
        }

        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $subscription = $subscriptionModel->getNormalizedSubscription($id);
        if ($subscription && in_array(($subscription['status'] ?? ''), ['active', 'paused'], true)) {
            $now = date('Y-m-d H:i:s');
            $subscriptionModel->update($subscription['id'], [
                'status' => 'canceled',
                'canceled_at' => $now,
                'end_at' => $now,
                'canceled_reason' => 'clinic_terminated',
            ]);
        }

        $clinicModel->update($id, ['status' => 'inactive']);

        $this->auditService->logEvent('clinic_terminate', [
            'route' => '/controlplane/clinics/' . $id . '/terminate',
            'method' => 'POST',
            'metadata' => ['clinic_id' => $id, 'reason' => 'clinic_terminated'],
        ]);

        return redirect()->to(base_url('controlplane/clinics/' . $id))->with('success', 'Clinic terminated.');
    }

    /**
     * POST /controlplane/clinics/{id}/suspend
     */
    public function suspend(int $id)
    {
        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($id);
        if (!$clinic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Clinic not found');
        }

        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $subscription = $subscriptionModel->getNormalizedSubscription($id);
        if ($subscription && ($subscription['status'] ?? '') === 'active') {
            $now = date('Y-m-d H:i:s');
            $subscriptionModel->update($subscription['id'], [
                'status' => 'paused',
                'paused_at' => $now,
                'paused_reason' => 'clinic_suspended',
            ]);
        }

        $clinicModel->update($id, ['status' => 'suspended']);

        $this->auditService->logEvent('clinic_suspend', [
            'route' => '/controlplane/clinics/' . $id . '/suspend',
            'method' => 'POST',
            'metadata' => ['clinic_id' => $id, 'reason' => 'clinic_suspended'],
        ]);

        return redirect()->to(base_url('controlplane/clinics/' . $id))->with('success', 'Clinic suspended.');
    }

    /**
     * POST /controlplane/clinics/{id}/reactivate
     */
    public function reactivate(int $id)
    {
        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($id);
        if (!$clinic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Clinic not found');
        }

        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $subscription = $subscriptionModel->getNormalizedSubscription($id);
        if ($subscription && ($subscription['status'] ?? '') === 'paused') {
            $subscriptionModel->update($subscription['id'], [
                'status' => 'active',
                'paused_at' => null,
                'paused_reason' => null,
            ]);
        }

        $clinicModel->update($id, ['status' => 'active']);

        $this->auditService->logEvent('clinic_reactivate', [
            'route' => '/controlplane/clinics/' . $id . '/reactivate',
            'method' => 'POST',
            'metadata' => ['clinic_id' => $id],
        ]);

        return redirect()->to(base_url('controlplane/clinics/' . $id))->with('success', 'Clinic reactivated.');
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

    private function normalizeDateTime(string $input): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }

        $dt = \DateTime::createFromFormat('Y-m-d\\TH:i', $input);
        if ($dt instanceof \DateTime) {
            return $dt->format('Y-m-d H:i:s');
        }

        $ts = strtotime($input);
        if ($ts === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $ts);
    }
}
