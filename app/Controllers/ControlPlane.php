<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\ControlPlaneAuditService;

class ControlPlane extends BaseController
{
    protected $permissionService;
    protected $ionAuth;
    protected $auditService;
    protected $clinicService;

    public function __construct()
    {
        $this->permissionService = service('permission');
        $this->ionAuth = new \App\Libraries\IonAuth();
        $this->auditService = new ControlPlaneAuditService();
        $this->clinicService = new \App\Services\ClinicService();
    }

    /**
     * Enter Control Plane (Global Mode)
     * POST /controlplane/enter
     */
    public function enter()
    {
        if (!$this->ionAuth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $userId = $this->ionAuth->getUserId();

        // Strict Super Admin Check (fallback to IonAuth admin flag)
        $isSuperAdmin = $this->permissionService && $this->permissionService->isSuperAdmin($userId);
        if (!$isSuperAdmin && $this->ionAuth->isAdmin()) {
            $isSuperAdmin = true;
        }
        if (!$isSuperAdmin) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $activeClinicId = session()->get('active_clinic_id');
        if ($activeClinicId) {
            session()->set('controlplane_return_clinic_id', $activeClinicId);
            session()->set('controlplane_return_clinic_role_id', session()->get('active_clinic_role_id'));
            session()->set('controlplane_return_membership_id', session()->get('active_clinic_membership_id'));
        }

        // Set Global Mode
        session()->set('global_mode', true);
        
        // Clear Tenant Context
        session()->remove('active_clinic_id');
        session()->remove('active_clinic_role_id');
        session()->remove('impersonated_clinic_id');
        session()->remove('is_impersonating');

        // Regenerate Session ID for security
        session()->regenerate();

        $this->auditService->logEvent('global_enter', [
            'route' => '/controlplane/enter'
        ]);

        return redirect()->to('/controlplane/dashboard');
    }

    /**
     * Exit Control Plane
     * POST /controlplane/exit
     */
    public function exit()
    {
        if (!$this->ionAuth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $userId = $this->ionAuth->getUserId();

        // Disable Global Mode
        session()->set('global_mode', false);
        session()->remove('impersonated_clinic_id');
        session()->remove('is_impersonating');

        $returnClinicId = (int) (session()->get('controlplane_return_clinic_id') ?? 0);
        $returnMembership = $returnClinicId > 0
            ? $this->clinicService->getMembership($userId, $returnClinicId)
            : null;

        if ($returnMembership) {
            $this->clinicService->setContext($returnMembership);
        } else {
            $this->clinicService->clearContext();
        }

        session()->remove([
            'controlplane_return_clinic_id',
            'controlplane_return_clinic_role_id',
            'controlplane_return_membership_id',
        ]);
        
        // Regenerate Session ID
        session()->regenerate();

        $this->auditService->logEvent('global_exit', [
            'route' => '/controlplane/exit'
        ]);

        if ($returnMembership) {
            return redirect()->to('/dashboard');
        }

        return redirect()->to('/clinic/select');
    }

    /**
     * Exit impersonation and return to Control Plane
     * POST /controlplane/impersonation/exit
     */
    public function exitImpersonation()
    {
        if (!$this->ionAuth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $userId = $this->ionAuth->getUserId();
        $isSuperAdmin = $this->permissionService && $this->permissionService->isSuperAdmin($userId);
        if (!$isSuperAdmin && $this->ionAuth->isAdmin()) {
            $isSuperAdmin = true;
        }
        if (!$isSuperAdmin) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        session()->set('global_mode', true);
        session()->remove('active_clinic_id');
        session()->remove('active_clinic_role_id');
        session()->remove('impersonated_clinic_id');
        session()->remove('is_impersonating');
        session()->regenerate();

        $this->auditService->logEvent('impersonation_end', [
            'route' => '/controlplane/impersonation/exit'
        ]);

        return redirect()->to('/controlplane/dashboard')->with('success', 'Exited impersonation.');
    }
}
