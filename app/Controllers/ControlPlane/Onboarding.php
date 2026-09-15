<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\PlanModel;
use App\Models\UserModel;
use App\Models\ClinicUserModel;
use App\Services\OnboardingService;
use App\Services\ControlPlaneAuditService;

class Onboarding extends BaseController
{
    protected $planModel;
    protected $onboardingService;
    protected $auditService;
    protected $userModel;

    public function __construct()
    {
        $this->planModel          = new PlanModel();
        $this->onboardingService  = new OnboardingService();
        $this->auditService       = new ControlPlaneAuditService();
        $this->userModel          = new UserModel();
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

        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/onboarding/clinic/create'
        ]);

        $data = [
            'title' => 'Onboard New Clinic',
            'plans' => $this->planModel->where('status', 'active')->findAll()
        ];

        return view('control_plane/onboarding/create_clinic', $data);
    }

    public function processCreateClinic()
    {
        $this->ensureGlobalMode();

        $this->auditService->logEvent('onboarding_attempt', [
            'route'    => '/controlplane/onboarding/clinic/create',
            'metadata' => [
                'plan_id' => (int) $this->request->getPost('plan_id'),
            ],
        ]);

        $adminEmail   = trim((string) $this->request->getPost('admin_email'));
        $existingUser = (new UserModel())->where('email', $adminEmail)->first();

        $passwordRule = $existingUser ? 'permit_empty|min_length[8]' : 'required|min_length[8]';

        $rules = [
            'clinic_name'    => 'required|min_length[3]|max_length[100]',
            'admin_name'     => 'required|min_length[3]|max_length[100]',
            'admin_email'    => 'required|valid_email',
            'admin_password' => $passwordRule,
            'plan_id'        => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            $errors = array_keys($this->validator->getErrors());
            $this->auditService->logEvent('onboarding_fail', [
                'route'    => '/controlplane/onboarding/clinic/create',
                'metadata' => [
                    'reason_code'  => 'VALIDATION_FAILED',
                    'error_fields' => $errors,
                ],
            ]);
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $dto = [
                'clinic_name'    => $this->request->getPost('clinic_name'),
                'admin_name'     => $this->request->getPost('admin_name'),
                'admin_email'    => $this->request->getPost('admin_email'),
                'admin_password' => $this->request->getPost('admin_password'),
                'plan_id'        => (int) $this->request->getPost('plan_id'),
            ];

            $actorId  = session()->get('user_id');
            $clinicId = $this->onboardingService->createClinicWithAdmin($dto, $actorId);

            return redirect()->to('/controlplane/clinics')->with('success', lang('UI.onboardingCreated', [$clinicId]));

        } catch (\Throwable $e) {
            $reasonCode = 'UNKNOWN_ERROR';
            if (preg_match('/^([A-Z_]+):/', $e->getMessage(), $matches)) {
                $reasonCode = $matches[1];
            }
            $this->auditService->logEvent('onboarding_fail', [
                'route'    => '/controlplane/onboarding/clinic/create',
                'metadata' => ['reason_code' => $reasonCode],
            ]);
            log_message('error', '[Onboarding] ' . $e->getMessage());

            // Surface a user-safe message; never expose raw stack traces
            $key = match ($reasonCode) {
                'INVALID_PLAN' => 'onboardingInvalidPlan',
                'SUPERADMIN_FORBIDDEN' => 'onboardingSuperadminForbidden',
                'USER_INACTIVE' => 'onboardingUserInactive',
                'VALIDATION_FAILED' => 'onboardingInvalidDetails',
                default => 'onboardingFailed',
            };
            $userMessage = lang('UI.' . $key);
            return redirect()->back()->withInput()->with('error', $userMessage);
        }
    }

    /**
     * Control-plane scoped email-availability check.
     *
     * Replaces /users/check-email which is tenant-plane only.
     * Protected by the `controlplane` filter (global_mode required).
     * Returns JSON compatible with the shared email_check_script component.
     */
    public function checkEmail()
    {
        $this->ensureGlobalMode();

        $email = trim((string) ($this->request->getGet('email') ?? $this->request->getPost('email') ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(422)->setJSON(['error' => ['code' => 'invalid_email', 'message' => lang('UI.onboardingInvalidDetails')]]);
        }

        try {
            $user = $this->userModel->where('email', $email)->first();
            if (!$user) {
                return $this->response->setJSON(['exists' => false]);
            }

            $clinicUserModel = (new ClinicUserModel())->withoutTenantScope();
            $memberships = $clinicUserModel
                ->select('clinic_users.clinic_id, clinic_users.status AS membership_status, clinics.name AS clinic_name, clinics.status AS clinic_status')
                ->join('clinics', 'clinics.id = clinic_users.clinic_id', 'left')
                ->where('clinic_users.user_id', $user['id'])
                ->findAll();

            $membershipRows = [];
            foreach ($memberships as $membership) {
                $membershipRows[] = [
                    'clinic_id' => (int) ($membership['clinic_id'] ?? 0),
                    'clinic_name' => $membership['clinic_name'] ?? 'Clinic',
                    'clinic_status' => $membership['clinic_status'] ?? 'unknown',
                    'membership_status' => $membership['membership_status'] ?? 'unknown',
                ];
            }

            return $this->response->setJSON([
                'exists' => true,
                'user_id' => (int) $user['id'],
                'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                'memberships' => $membershipRows,
                'in_current_clinic' => false,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Control-plane onboarding email check failed: {message}', ['message' => $e->getMessage()]);
            return $this->response->setStatusCode(503)->setJSON(['error' => ['code' => 'email_check_unavailable', 'message' => lang('UI.onboardingFailed')]]);
        }
    }
}
