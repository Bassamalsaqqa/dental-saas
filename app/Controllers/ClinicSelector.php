<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\ClinicService;

class ClinicSelector extends BaseController
{
    protected $ionAuth;
    protected $clinicService;

    public function __construct()
    {
        $this->ionAuth = new \App\Libraries\IonAuth();
        $this->clinicService = new ClinicService();
    }

    /**
     * GET /clinic/select
     * Show clinic selection screen or auto-redirect
     */
    public function select()
    {
        if (!$this->ionAuth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $userId = $this->ionAuth->getUserId();

        // Query memberships via Service
        $memberships = $this->clinicService->getMemberships($userId);

        $count = count($memberships);

        if ($count === 0) {
            return redirect()->to('/clinic/no-clinic');
        }

        if ($count === 1) {
            // Auto-select
            $this->clinicService->setContext($memberships[0]);
            return redirect()->to('/dashboard');
        }

        // Render selection view
        $data = [
            'title' => 'Select Clinic',
            'clinics' => $memberships
        ];
        
        return view('clinic/select', $data);
    }

    /**
     * POST /clinic/select
     * Process clinic selection
     */
    public function processSelect()
    {
        if (!$this->ionAuth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $clinicId = $this->request->getPost('clinic_id');
        $userId = $this->ionAuth->getUserId();

        if (!$clinicId) {
            return redirect()->back()->with('error', 'Please select a clinic.');
        }

        // Validate membership
        $membership = $this->clinicService->getMembership($userId, $clinicId);

        if (!$membership) {
            return redirect()->back()->with('error', 'Invalid clinic selection.');
        }

        $this->clinicService->setContext($membership);

        return redirect()->to('/dashboard');
    }

    /**
     * POST /clinic/switch
     * Switch active clinic
     */
    public function switchClinic()
    {
        if (!$this->ionAuth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $clinicId = $this->request->getPost('clinic_id');
        $userId = $this->ionAuth->getUserId();

        if (!$clinicId) {
            return redirect()->back();
        }

        // Validate membership
        $membership = $this->clinicService->getMembership($userId, $clinicId);

        if (!$membership) {
            // Invalid switch attempt
            $this->clinicService->clearContext();
            return redirect()->to('/clinic/select')->with('error', 'Invalid clinic switch attempt.');
        }

        $this->clinicService->setContext($membership);

        return redirect()->to('/dashboard')->with('success', 'Switched to new clinic.');
    }

    /**
     * GET /clinic/no-clinic
     * Wall page for users with no clinics
     */
    public function noClinic()
    {
        if (!$this->ionAuth->loggedIn()) {
            return redirect()->to('/auth/login');
        }
        
        return view('clinic/no_clinic', ['title' => 'No Clinic Assigned']);
    }
}