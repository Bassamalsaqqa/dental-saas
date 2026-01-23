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

        $clinicModel = new ClinicModel();
        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $planModel = new PlanModel();

        $clinics = $clinicModel->orderBy('created_at', 'DESC')->findAll();
        $activeClinics = 0;
        $cards = [];

        foreach ($clinics as $clinic) {
            $status = $clinic['status'] ?? 'inactive';
            if ($status === 'active') {
                $activeClinics++;
            }

            $subscription = $subscriptionModel->getCurrentSubscription((int) $clinic['id']);
            $planName = 'Unassigned';
            if ($subscription && !empty($subscription['plan_id'])) {
                $plan = $planModel->find($subscription['plan_id']);
                if (!empty($plan['name'])) {
                    $planName = $plan['name'];
                }
            }

            $cards[] = [
                'clinic' => $clinic,
                'subscription' => $subscription,
                'plan_name' => $planName,
            ];
        }

        return view('control_plane/clinics', [
            'title' => 'Clinics',
            'cards' => $cards,
            'stats' => [
                'total' => count($clinics),
                'active' => $activeClinics,
            ],
        ]);
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

        $subscription = $subscriptionModel->getCurrentSubscription((int) $clinic['id']);
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

        $current = $subscriptionModel->getCurrentSubscription($id);
        if ($current) {
            $current = $subscriptionModel->markExpiredIfNeeded($current);
        }
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
        $subscription = $subscriptionModel->getCurrentSubscription($id);
        if ($subscription) {
            $subscription = $subscriptionModel->markExpiredIfNeeded($subscription);
        }
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
        $subscription = $subscriptionModel->getCurrentSubscription($id);
        if ($subscription) {
            $subscription = $subscriptionModel->markExpiredIfNeeded($subscription);
        }
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
        $subscription = $subscriptionModel->getCurrentSubscription($id);
        if ($subscription) {
            $subscription = $subscriptionModel->markExpiredIfNeeded($subscription);
        }
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
        $subscription = $subscriptionModel->getCurrentSubscription($id);
        if ($subscription) {
            $subscription = $subscriptionModel->markExpiredIfNeeded($subscription);
        }
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
        $subscription = $subscriptionModel->getCurrentSubscription($id);
        if ($subscription) {
            $subscription = $subscriptionModel->markExpiredIfNeeded($subscription);
        }
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
        $subscription = $subscriptionModel->getCurrentSubscription($id);
        if ($subscription) {
            $subscription = $subscriptionModel->markExpiredIfNeeded($subscription);
        }
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
