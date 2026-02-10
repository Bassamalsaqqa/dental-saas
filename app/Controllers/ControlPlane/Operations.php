<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\ClinicModel;
use App\Models\PlanAuditModel;
use App\Services\ControlPlaneAuditService;

class Operations extends BaseController
{
    protected $auditService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
    }
    /**
     * GET /controlplane/operations
     */
    public function index()
    {
        // Global mode check handled by 'controlplane' filter
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/operations'
        ]);
        $healthChecks = [];
        $dbOk = true;
        $dbDetail = 'Connected';
        try {
            $db = \Config\Database::connect();
            $db->query('SELECT 1');
        } catch (\Throwable $e) {
            $dbOk = false;
            $dbDetail = 'Error: ' . $e->getMessage();
        }
        $healthChecks[] = [
            'label' => 'Database',
            'status' => $dbOk,
            'detail' => $dbDetail,
        ];

        $paths = [
            'Writable Root' => WRITEPATH,
            'Uploads' => WRITEPATH . 'uploads',
            'Sessions' => WRITEPATH . 'session',
            'Cache' => WRITEPATH . 'cache',
        ];

        foreach ($paths as $label => $path) {
            $exists = is_dir($path);
            $writable = $exists && is_writable($path);
            $healthChecks[] = [
                'label' => $label,
                'status' => $exists && $writable,
                'detail' => $exists
                    ? ($writable ? 'Writable' : 'Not writable')
                    : 'Missing directory',
            ];
        }

        $planAudits = (new PlanAuditModel())
            ->withoutTenantScope()
            ->orderBy('id', 'DESC')
            ->findAll(10);

        $clinicDirectory = [];
        if (! empty($planAudits)) {
            $clinicIds = array_unique(array_filter(array_column($planAudits, 'clinic_id')));
            if (! empty($clinicIds)) {
                $clinicRows = (new ClinicModel())
                    ->whereIn('id', $clinicIds)
                    ->findAll();
                foreach ($clinicRows as $clinic) {
                    $clinicDirectory[$clinic['id']] = $clinic;
                }
            }
        }

        return view('control_plane/operations', [
            'title' => 'Operations',
            'healthChecks' => $healthChecks,
            'environment' => ENVIRONMENT,
            'phpVersion' => PHP_VERSION,
            'planAudits' => $planAudits,
            'clinicDirectory' => $clinicDirectory,
        ]);
    }
}
