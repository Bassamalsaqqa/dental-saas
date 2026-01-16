<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
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
        $activeClinics = $clinicModel->where('status', 'active')->countAllResults();

        $data = [
            'title' => 'Control Plane Dashboard',
            'global_mode' => true,
            'stats' => [
                'active_clinics' => $activeClinics
            ]
        ];

        return view('control_plane/dashboard', $data);
    }
}
