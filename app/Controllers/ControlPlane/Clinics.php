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
        ]);
    }
}
