<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\PlanModel;
use App\Services\ControlPlaneAuditService;

class Plans extends BaseController
{
    protected $auditService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
    }

    /**
     * GET /controlplane/plans
     */
    public function index()
    {
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/plans',
        ]);

        $request = service('request');
        $statusFilter = strtolower(trim((string) ($request ? $request->getGet('status') : '')));
        $durationFilter = strtolower(trim((string) ($request ? $request->getGet('duration') : '')));
        $search = trim((string) ($request ? $request->getGet('q') : ''));

        $planModel = new PlanModel();
        $planQuery = $planModel->orderBy('id', 'DESC');
        if (in_array($statusFilter, ['active', 'inactive'], true)) {
            $planQuery = $planQuery->where('status', $statusFilter);
        }
        if ($search !== '') {
            $planQuery = $planQuery->groupStart()
                ->like('name', $search)
                ->groupEnd();
        }
        $plans = $planQuery->findAll();
        $totalPlans = $planModel->countAllResults();
        $activePlans = (new PlanModel())->where('status', 'active')->countAllResults();
        $inactivePlans = (new PlanModel())->where('status', 'inactive')->countAllResults();
        $cards = [];

        foreach ($plans as $plan) {
            $limits = json_decode($plan['limits_json'] ?? '{}', true) ?? [];
            $features = json_decode($plan['features_json'] ?? '{}', true) ?? [];
            $duration = strtolower((string) ($limits['duration'] ?? ''));

            if ($durationFilter !== '' && $duration !== $durationFilter) {
                continue;
            }

            $cards[] = [
                'plan' => $plan,
                'limits' => $limits,
                'features' => $features,
            ];
        }

        return view('control_plane/plans', [
            'title' => 'Plans',
            'plans' => $cards,
            'stats' => [
                'total' => $totalPlans,
                'active' => $activePlans,
                'inactive' => $inactivePlans,
                'filtered' => count($cards),
            ],
            'filters' => [
                'status' => $statusFilter,
                'duration' => $durationFilter,
                'q' => $search,
            ],
        ]);
    }

    /**
     * GET /controlplane/plans/create
     */
    public function create()
    {
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/plans/create',
        ]);

        return view('control_plane/plan_form', [
            'title' => 'Create Plan',
            'plan' => null,
            'limits' => $this->defaultLimits(),
            'features' => $this->defaultFeatures(),
            'mode' => 'create',
        ]);
    }

    /**
     * GET /controlplane/plans/{id}/edit
     */
    public function edit(int $id)
    {
        $planModel = new PlanModel();
        $plan = $planModel->find($id);
        if (!$plan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Plan not found');
        }

        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/plans/' . $id . '/edit',
        ]);

        return view('control_plane/plan_form', [
            'title' => 'Edit Plan',
            'plan' => $plan,
            'limits' => json_decode($plan['limits_json'] ?? '{}', true) ?? $this->defaultLimits(),
            'features' => json_decode($plan['features_json'] ?? '{}', true) ?? $this->defaultFeatures(),
            'mode' => 'edit',
        ]);
    }

    /**
     * POST /controlplane/plans
     */
    public function store()
    {
        $payload = $this->buildPlanPayload();
        $planModel = new PlanModel();

        $planId = $planModel->insert($payload);

        $this->auditService->logEvent('plan_create', [
            'route' => '/controlplane/plans',
            'method' => 'POST',
            'metadata' => ['plan_id' => $planId, 'name' => $payload['name'] ?? null],
        ]);

        return redirect()->to(base_url('controlplane/plans'))->with('success', 'Plan created.');
    }

    /**
     * POST /controlplane/plans/{id}
     */
    public function update(int $id)
    {
        $planModel = new PlanModel();
        $plan = $planModel->find($id);
        if (!$plan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Plan not found');
        }

        $payload = $this->buildPlanPayload();
        $planModel->update($id, $payload);

        $this->auditService->logEvent('plan_update', [
            'route' => '/controlplane/plans/' . $id,
            'method' => 'POST',
            'metadata' => ['plan_id' => $id, 'name' => $payload['name'] ?? null],
        ]);

        return redirect()->to(base_url('controlplane/plans'))->with('success', 'Plan updated.');
    }

    /**
     * POST /controlplane/plans/{id}/toggle
     */
    public function toggle(int $id)
    {
        $planModel = new PlanModel();
        $plan = $planModel->find($id);
        if (!$plan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Plan not found');
        }

        $newStatus = ($plan['status'] ?? 'inactive') === 'active' ? 'inactive' : 'active';
        $planModel->update($id, ['status' => $newStatus]);

        $this->auditService->logEvent('plan_toggle', [
            'route' => '/controlplane/plans/' . $id . '/toggle',
            'method' => 'POST',
            'metadata' => ['plan_id' => $id, 'status' => $newStatus],
        ]);

        return redirect()->to(base_url('controlplane/plans'))->with('success', 'Plan status updated.');
    }

    private function defaultLimits(): array
    {
        return [
            'price' => 0,
            'duration' => 'monthly',
            'trial_days' => 0,
            'patients_active_max' => -1,
            'users_max' => -1,
            'storage_gb' => -1,
            'exports' => -1,
            'notifications_email' => -1,
            'description' => '',
        ];
    }

    private function defaultFeatures(): array
    {
        return [
            'appointments' => true,
            'finance' => true,
            'inventory' => true,
            'reports' => true,
            'email_templates' => true,
            'audit_exports' => false,
            'chairside_notes' => false,
            'claims_hub' => false,
            'exports' => ['enabled' => false],
            'notifications' => [
                'email' => ['enabled' => true],
                'sms' => ['enabled' => false],
            ],
        ];
    }

    private function buildPlanPayload(): array
    {
        $request = service('request');
        $post = $request ? $request->getPost() : [];

        $featuresInput = $post['features'] ?? [];
        $features = [
            'appointments' => !empty($featuresInput['appointments']),
            'finance' => !empty($featuresInput['finance']),
            'inventory' => !empty($featuresInput['inventory']),
            'reports' => !empty($featuresInput['reports']),
            'email_templates' => !empty($featuresInput['email_templates']),
            'audit_exports' => !empty($featuresInput['audit_exports']),
            'chairside_notes' => !empty($featuresInput['chairside_notes']),
            'claims_hub' => !empty($featuresInput['claims_hub']),
            'exports' => [
                'enabled' => !empty($featuresInput['exports']['enabled']),
            ],
            'notifications' => [
                'email' => ['enabled' => !empty($featuresInput['notifications']['email']['enabled'])],
                'sms' => ['enabled' => !empty($featuresInput['notifications']['sms']['enabled'])],
            ],
        ];

        $limits = [
            'price' => isset($post['price']) ? (float) $post['price'] : 0,
            'duration' => $post['duration'] ?? 'monthly',
            'trial_days' => isset($post['trial_days']) ? (int) $post['trial_days'] : 0,
            'patients_active_max' => $this->normalizeLimit($post['patient_limit'] ?? null),
            'users_max' => $this->normalizeLimit($post['user_seats'] ?? null),
            'storage_gb' => $this->normalizeLimit($post['storage_gb'] ?? null),
            'exports' => $this->normalizeLimit($post['export_quota'] ?? null),
            'notifications_email' => $this->normalizeLimit($post['email_quota'] ?? null),
            'description' => trim($post['description'] ?? ''),
        ];

        $status = $post['status'] ?? 'active';
        if (!in_array($status, ['active', 'inactive'], true)) {
            $status = 'active';
        }

        return [
            'name' => trim($post['name'] ?? ''),
            'status' => $status,
            'features_json' => json_encode($features),
            'limits_json' => json_encode($limits),
        ];
    }

    private function normalizeLimit($value): int
    {
        if ($value === '' || $value === null) {
            return -1;
        }
        return (int) $value;
    }
}
