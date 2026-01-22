<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Services\ControlPlaneAuditService;

class EmailTemplates extends BaseController
{
    protected $auditService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
    }

    /**
     * GET /controlplane/email-templates
     */
    public function index()
    {
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/email-templates',
        ]);

        return view('control_plane/email_templates', [
            'title' => 'Email Templates',
        ]);
    }
}
