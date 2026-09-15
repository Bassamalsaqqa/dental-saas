<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\ClinicModel;
use App\Models\ClinicUserModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\PlanModel;
use App\Models\ControlPlaneInvoiceModel;
use App\Models\SettingsModel;
use App\Models\WhatsAppTenantPolicyModel;
use App\Services\ControlPlaneAuditService;
use App\Services\PlanGuard;
use App\Services\WhatsAppTemplateRegistryService;
use App\Services\WhatsAppTriggerCatalog;
use App\Services\ClinicAdminLifecycleService;
use App\Libraries\IonAuth;

class Clinics extends BaseController
{
    protected $auditService;
    protected $ionAuth;
    protected ?ControlPlaneInvoiceModel $invoiceModel = null;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
        $this->ionAuth = new IonAuth();
    }

    public function setInvoiceModel(ControlPlaneInvoiceModel $invoiceModel): void
    {
        $this->invoiceModel = $invoiceModel;
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
        $clinicUserModel = (new ClinicUserModel())->withoutTenantScope();

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

        $clinicIds = array_map(static fn($c) => (int) $c['id'], $clinics);
        $adminLifecycleService = new ClinicAdminLifecycleService();
        $adminsMap = $adminLifecycleService->getClinicAdministrators($clinicIds);

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

            $adminInfo = $adminsMap[(int) $clinic['id']] ?? null;
            $adminEmail = $adminInfo && !empty($adminInfo['admin_email']) ? $adminInfo['admin_email'] : 'None';
            $adminState = $adminInfo['state'] ?? ClinicAdminLifecycleService::STATE_UNASSIGNED;
            $adminStateLabel = $adminInfo['state_label'] ?? 'Unassigned';
            $adminIsHealthy = (bool) ($adminInfo['is_healthy'] ?? false);

            $rows[] = [
                'id' => $clinic['id'],
                'name' => $clinic['name'] ?? '',
                'slug' => $clinic['slug'] ?? '',
                'status' => $clinic['status'] ?? 'inactive',
                'plan_name' => $planName,
                'mrr' => $mrr,
                'admin_email' => $adminEmail,
                'admin_name' => $adminInfo['admin_name'] ?? 'None',
                'admin_state' => $adminState,
                'admin_state_label' => $adminStateLabel,
                'admin_is_healthy' => $adminIsHealthy,
                'admin_is_designated' => (bool) ($adminInfo['is_designated'] ?? false),
                'admin_is_fallback' => (bool) ($adminInfo['is_fallback'] ?? false),
                'admin_source_label' => $adminInfo['source_label'] ?? 'Unassigned',
                'admin_designation_source' => $adminInfo['designation_source'] ?? 'none',
            ];
        }

        return $rows;
    }

    /**
     * POST /controlplane/impersonate
     */
    public function impersonate()
    {
        if (!session()->get('global_mode')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Control Plane access required.');
        }

        $clinicId = (int) $this->request->getPost('clinic_id');
        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($clinicId);
        if (!$clinic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Clinic not found.');
        }

        session()->set([
            'global_mode' => false,
            'active_clinic_id' => $clinicId,
            'impersonated_clinic_id' => $clinicId,
            'is_impersonating' => true,
        ]);
        session()->remove('active_clinic_role_id');
        session()->regenerate();

        $this->auditService->logEvent('clinic_impersonate', [
            'route' => '/controlplane/impersonate',
            'method' => 'POST',
            'metadata' => ['clinic_id' => $clinicId],
        ]);

        return redirect()->to('/dashboard')->with('success', 'Impersonation active for ' . ($clinic['name'] ?? 'clinic') . '.');
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
        $clinicUserModel = (new ClinicUserModel())->withoutTenantScope();

        $clinic = $clinicModel->find($id);
        if (!$clinic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Clinic not found');
        }

        // Fetch Admin Info via Lifecycle Service
        $adminLifecycleService = new ClinicAdminLifecycleService();
        $adminInfo = $adminLifecycleService->getClinicAdministrator($id);
        $eligibleAdminUsers = $adminLifecycleService->getEligibleUsersForClinic($id);

        $adminUser = $adminInfo['admin_user'];
        $clinic['admin_email'] = $adminUser ? $adminUser['email'] : 'Unknown';
        $clinic['admin_name'] = $adminUser ? trim(($adminUser['first_name'] ?? '') . ' ' . ($adminUser['last_name'] ?? '')) : 'Unknown';

        $subscription = $subscriptionModel->getNormalizedSubscription((int) $clinic['id']);
        $planName = 'Unassigned';
        if ($subscription && !empty($subscription['plan_id'])) {
            $plan = $planModel->find($subscription['plan_id']);
            if (!empty($plan['name'])) {
                $planName = $plan['name'];
            }
        }

        $invoices = [];
        $invoiceStats = [
            'outstanding' => 0,
            'revenue_month' => 0,
            'overdue' => 0,
        ];
        $invoiceDataAvailable = true;

        try {
            $invoiceModel = $this->invoiceModel ?? new ControlPlaneInvoiceModel();
            $invoices = $invoiceModel->where('clinic_id', $id)
                ->orderBy('issue_date', 'DESC')
                ->findAll() ?: [];

            $currentMonth = date('Y-m');
            foreach ($invoices as $invoiceRow) {
                $totalAmount = (float) ($invoiceRow['total_amount'] ?? 0);
                if (($invoiceRow['status'] ?? '') !== 'paid') {
                    $invoiceStats['outstanding'] += $totalAmount;
                }
                if (($invoiceRow['status'] ?? '') === 'overdue') {
                    $invoiceStats['overdue'] += 1;
                }
                if (!empty($invoiceRow['issue_date']) && str_starts_with($invoiceRow['issue_date'], $currentMonth) && ($invoiceRow['status'] ?? '') === 'paid') {
                    $invoiceStats['revenue_month'] += $totalAmount;
                }
            }
            $invoiceStats['outstanding'] = round($invoiceStats['outstanding'], 2);
            $invoiceStats['revenue_month'] = round($invoiceStats['revenue_month'], 2);
        } catch (\Throwable $e) {
            $invoiceDataAvailable = false;
            log_message('error', sprintf('ControlPlane Clinics::show failed to load invoices for clinic %d [%s]: %s', $id, get_class($e), $e->getMessage()));
        }

        $settingsModel = new SettingsModel();
        $clinicSettingsRows = $settingsModel->withoutTenantScope()
            ->select('setting_key, setting_value, setting_type')
            ->where('clinic_id', $id)
            ->whereIn('setting_key', ['clinic_name', 'clinic_phone', 'clinic_address'])
            ->findAll();
        $clinicSettings = [
            'clinic_name' => '',
            'clinic_phone' => '',
            'clinic_address' => '',
        ];
        foreach ($clinicSettingsRows as $row) {
            $key = (string) ($row['setting_key'] ?? '');
            if (!array_key_exists($key, $clinicSettings)) {
                continue;
            }
            $clinicSettings[$key] = (string) ($row['setting_value'] ?? '');
        }

        $waPolicyModel = new WhatsAppTenantPolicyModel();
        $waTriggerCatalog = new WhatsAppTriggerCatalog();
        $waTemplateRegistry = new WhatsAppTemplateRegistryService();
        $waPolicy = $waPolicyModel->getByClinicForControlPlane($id) ?? [];
        $waPolicyToggles = $waPolicy['trigger_toggles_json'] ?? [];
        if (is_string($waPolicyToggles)) {
            $waPolicyToggles = json_decode($waPolicyToggles, true);
        }
        $waPolicyToggles = is_array($waPolicyToggles) ? array_merge($waTriggerCatalog->defaultTriggerToggles(), $waPolicyToggles) : $waTriggerCatalog->defaultTriggerToggles();
        $waFeatureEnabled = (new PlanGuard())->isFeatureEnabled($id, 'notifications.whatsapp.enabled', false);
        $waGlobalConfig = $waTemplateRegistry->getGlobalConfig();

        return view('control_plane/clinic_detail', [
            'title' => 'Clinic Overview',
            'clinic' => $clinic,
            'admin_info' => $adminInfo,
            'eligible_admin_users' => $eligibleAdminUsers,
            'subscription' => $subscription,
            'plan_name' => $planName,
            'plans' => $planModel->where('status', 'active')->orderBy('name', 'ASC')->findAll(),
            'invoices' => $invoices,
            'invoiceStats' => $invoiceStats,
            'invoice_data_available' => $invoiceDataAvailable,
            'clinic_settings_snapshot' => $clinicSettings,
            'wa_policy' => $waPolicy,
            'wa_policy_toggles' => $waPolicyToggles,
            'wa_trigger_catalog' => $waTriggerCatalog->all(),
            'wa_global_templates' => $waGlobalConfig,
            'wa_feature_enabled' => $waFeatureEnabled,
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
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Clinic not found.']);
            }
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

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Clinic terminated successfully.']);
        }
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
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Clinic not found.']);
            }
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

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Clinic suspended.']);
        }
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
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Clinic not found.']);
            }
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

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Clinic reactivated.']);
        }
        return redirect()->to(base_url('controlplane/clinics/' . $id))->with('success', 'Clinic reactivated.');
    }

    /**
     * POST /controlplane/clinics/{id}/delete
     */
    public function delete(int $id)
    {
        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($id);
        if (!$clinic) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Clinic not found.']);
            }
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Clinic not found');
        }

        // Hard Delete Logic
        try {
            // 1. Delete Subscriptions
            $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
            $subscriptionModel->where('clinic_id', $id)->delete();

            // 2. Delete Memberships (Users remain, but clinic association is gone)
            $clinicUserModel = (new ClinicUserModel())->withoutTenantScope();
            $clinicUserModel->where('clinic_id', $id)->delete();

            // 3. Delete Clinic
            $clinicModel->delete($id);
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Could not delete clinic. Database constraints may be preventing hard deletion.']);
            }
            return redirect()->back()->with('error', 'Could not delete clinic. Database constraints may be preventing hard deletion.');
        }

        $this->auditService->logEvent('clinic_delete', [
            'route' => '/controlplane/clinics/' . $id . '/delete',
            'method' => 'POST',
            'metadata' => ['clinic_id' => $id, 'name' => $clinic['name'] ?? 'Unknown'],
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Clinic deleted successfully.']);
        }
        return redirect()->to(base_url('controlplane/clinics'))->with('success', 'Clinic and associated data deleted.');
    }

    /**
     * POST /controlplane/clinics/{id}/reset-admin-password
     */
    public function resetAdminPassword(int $id)
    {
        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($id);
        if (!$clinic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Clinic not found');
        }

        $password = (string) $this->request->getPost('password');
        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'Password must be at least 8 characters long.');
        }

        // Find Admin User via Lifecycle Service
        $lifecycleService = new ClinicAdminLifecycleService();
        $adminInfo = $lifecycleService->getClinicAdministrator($id);
        $adminUser = $adminInfo['admin_user'];

        if (!$adminUser) {
            return redirect()->back()->with('error', 'No admin user found for this clinic.');
        }

        // Update Password
        if ($this->ionAuth->update($adminUser['id'], ['password' => $password])) {
            $this->auditService->logEvent('admin_password_reset', [
                'route' => '/controlplane/clinics/' . $id . '/reset-admin-password',
                'method' => 'POST',
                'metadata' => [
                    'clinic_id' => $id, 
                    'admin_user_id' => $adminUser['id'],
                    'admin_email' => $adminUser['email']
                ],
            ]);
            return redirect()->back()->with('success', "Password reset for {$adminUser['email']}.");
        } else {
            return redirect()->back()->with('error', 'Failed to reset password.');
        }
    }

    /**
     * POST /controlplane/clinics/{id}/admin/assign
     */
    public function assignAdmin(int $id)
    {
        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($id);
        if (!$clinic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Clinic not found');
        }

        $userId = (int) $this->request->getPost('user_id');
        if ($userId <= 0) {
            return redirect()->back()->with('error', lang('UI.clinicAdminSelectUser'));
        }

        $operatorId = (int) (session()->get('user_id') ?? 0);
        if ($operatorId <= 0) {
            return redirect()->back()->with('error', lang('UI.operatorIdentityRequired'));
        }

        $lifecycleService = new ClinicAdminLifecycleService();
        $result = $lifecycleService->assignAdministrator($id, $userId, $operatorId, reason: 'Assigned via control plane UI');

        $msg = $lifecycleService->formatLocalizedMessage($result, ['clinic' => $clinic['name'] ?? '']);
        if ($result['success']) {
            return redirect()->to(base_url('controlplane/clinics/' . $id))->with('success', $msg);
        }

        return redirect()->back()->with('error', $msg);
    }

    /**
     * POST /controlplane/clinics/{id}/admin/replace
     */
    public function replaceAdmin(int $id)
    {
        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($id);
        if (!$clinic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Clinic not found');
        }

        $newUserId = (int) $this->request->getPost('user_id');
        if ($newUserId <= 0) {
            return redirect()->back()->with('error', lang('UI.clinicAdminSelectUser'));
        }

        $expectedAdminIdRaw = $this->request->getPost('expected_admin_id');
        if ($expectedAdminIdRaw === null || !is_numeric($expectedAdminIdRaw) || (int) $expectedAdminIdRaw <= 0) {
            return redirect()->back()->with('error', lang('UI.clinicAdminErrorExpectedAdminRequired'));
        }
        $expectedAdminUserId = (int) $expectedAdminIdRaw;

        // Strict allowlist for previous admin action: no silent defaulting (Round 2 Finding 2)
        $previousAdminAction = (string) $this->request->getPost('previous_admin_action');
        if (!in_array($previousAdminAction, ['demote_to_staff', 'keep_role', 'deactivate_membership'], true)) {
            return redirect()->back()->with('error', lang('UI.clinicAdminErrorInvalidPreviousAdminAction'));
        }

        $operatorId = (int) (session()->get('user_id') ?? 0);
        if ($operatorId <= 0) {
            return redirect()->back()->with('error', lang('UI.operatorIdentityRequired'));
        }

        $lifecycleService = new ClinicAdminLifecycleService();
        $result = $lifecycleService->replaceAdministrator(
            $id,
            $newUserId,
            $operatorId,
            $previousAdminAction,
            reason: 'Replaced via control plane UI',
            expectedCurrentAdminUserId: $expectedAdminUserId
        );

        $msg = $lifecycleService->formatLocalizedMessage($result, ['clinic' => $clinic['name'] ?? '']);
        if ($result['success']) {
            return redirect()->to(base_url('controlplane/clinics/' . $id))->with('success', $msg);
        }

        return redirect()->back()->with('error', $msg);
    }

    /**
     * POST /controlplane/clinics/{id}/admin/restore
     */
    public function restoreAdmin(int $id)
    {
        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($id);
        if (!$clinic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Clinic not found');
        }

        $userId = (int) $this->request->getPost('user_id');
        $lifecycleService = new ClinicAdminLifecycleService();

        if ($userId <= 0) {
            $currentAdmin = $lifecycleService->getClinicAdministrator($id);
            $userId = (int) ($currentAdmin['admin_user']['id'] ?? 0);
        }

        if ($userId <= 0) {
            return redirect()->back()->with('error', lang('UI.clinicAdminErrorTargetMismatch'));
        }

        $operatorId = (int) (session()->get('user_id') ?? 0);
        if ($operatorId <= 0) {
            return redirect()->back()->with('error', lang('UI.operatorIdentityRequired'));
        }

        $expectedDesignatedIdRaw = $this->request->getPost('expected_designated_admin_id');
        if ($expectedDesignatedIdRaw === null || !is_numeric($expectedDesignatedIdRaw) || (int) $expectedDesignatedIdRaw <= 0) {
            return redirect()->back()->with('error', lang('UI.clinicAdminErrorExpectedAdminRequired'));
        }
        $expectedDesignatedAdminUserId = (int) $expectedDesignatedIdRaw;

        $result = $lifecycleService->restoreAdministrator(
            $id,
            $userId,
            $operatorId,
            reason: 'Restored via control plane UI',
            expectedDesignatedAdminUserId: $expectedDesignatedAdminUserId
        );

        $msg = $lifecycleService->formatLocalizedMessage($result, ['clinic' => $clinic['name'] ?? '']);
        if ($result['success']) {
            return redirect()->to(base_url('controlplane/clinics/' . $id))->with('success', $msg);
        }

        return redirect()->back()->with('error', $msg);
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
