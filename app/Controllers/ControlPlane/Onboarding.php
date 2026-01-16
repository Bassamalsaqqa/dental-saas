<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\PlanModel;
use App\Services\OnboardingService;

class Onboarding extends BaseController
{
    protected $planModel;
    protected $onboardingService;

    public function __construct()
    {
        $this->planModel = new PlanModel();
        $this->onboardingService = new OnboardingService();
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

    public function createClinic()
    {
        $this->ensureGlobalMode();

        $data = [
            'title' => 'Onboard New Clinic',
            'plans' => $this->planModel->where('status', 'active')->findAll()
        ];

        // We use a specific view path
        return view('control_plane/onboarding/create_clinic', $data);
    }

    public function processCreateClinic()
    {
        $this->ensureGlobalMode();

        $rules = [
            'clinic_name' => 'required|min_length[3]|max_length[100]',
            'admin_name' => 'required|min_length[3]|max_length[100]',
            'admin_email' => 'required|valid_email',
            'admin_password' => 'required|min_length[8]',
            'plan_id' => 'required|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $dto = [
                'clinic_name' => $this->request->getPost('clinic_name'),
                'admin_name' => $this->request->getPost('admin_name'),
                'admin_email' => $this->request->getPost('admin_email'),
                'admin_password' => $this->request->getPost('admin_password'),
                'plan_id' => (int)$this->request->getPost('plan_id')
            ];

            $actorId = session()->get('user_id');
            $clinicId = $this->onboardingService->createClinicWithAdmin($dto, $actorId);

            return redirect()->to('/settings')->with('success', "Clinic created successfully (ID: $clinicId).");

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
