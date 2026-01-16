<?php

namespace App\Services;

use App\Models\ClinicModel;
use App\Models\UserModel;
use App\Models\ClinicUserModel;
use App\Models\RoleModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\PlanModel;
use App\Models\PlanAuditModel;

class OnboardingService
{
    protected $clinicModel;
    protected $userModel;
    protected $clinicUserModel;
    protected $roleModel;
    protected $subscriptionModel;
    protected $planModel;
    protected $auditModel;

    public function __construct()
    {
        $this->clinicModel = new ClinicModel();
        $this->userModel = new UserModel();
        $this->clinicUserModel = new ClinicUserModel();
        $this->roleModel = new RoleModel();
        $this->subscriptionModel = new ClinicSubscriptionModel();
        $this->planModel = new PlanModel();
        $this->auditModel = new PlanAuditModel();
    }

    /**
     * Create a new clinic with an admin user and active subscription.
     * 
     * @param array $dto [clinic_name, admin_name, admin_email, admin_password, plan_id]
     * @param int $actorUserId ID of the superadmin performing the action
     * @return int The ID of the created clinic
     * @throws \RuntimeException on failure
     */
    public function createClinicWithAdmin(array $dto, int $actorUserId): int
    {
        // 1. Assert Global Mode (Fail-Closed)
        if (!session()->get('global_mode')) {
            throw new \RuntimeException("ACCESS_DENIED: Onboarding requires Control Plane Global Mode.");
        }

        // 2. Validate Plan availability
        $activePlansCount = $this->planModel->where('status', 'active')->countAllResults();
        if ($activePlansCount === 0) {
            throw new \RuntimeException("NO_ACTIVE_PLAN: No plans are currently active in the system.");
        }

        $plan = $this->planModel->find($dto['plan_id']);
        if (!$plan || $plan['status'] !== 'active') {
            throw new \RuntimeException("INVALID_PLAN: Selected plan is missing or inactive.");
        }

        // 3. Resolve Admin Role
        // Trying 'clinic_admin' or fallback to 'practice_manager'
        $role = $this->roleModel->getBySlug('clinic_admin');
        if (!$role) {
            $role = $this->roleModel->getBySlug('practice_manager');
        }
        if (!$role) {
            throw new \RuntimeException("CONFIGURATION_ERROR: Required role (clinic_admin/practice_manager) not found.");
        }

        helper('text'); // Load text helper for url_title

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // A. Create Clinic
            $clinicData = [
                'name' => $dto['clinic_name'],
                'status' => 'active', // Active by default? Or pending? Requirement says "active subscription", implies active clinic.
                'slug' => url_title($dto['clinic_name'], '-', true) . '-' . time() // Ensure uniqueness
            ];
            
            // Using DB builder if model has specific validations I might trip over, 
            // but Model should be used. Assuming ClinicModel has standard insert.
            $clinicId = $this->clinicModel->insert($clinicData);
            if (!$clinicId) {
                throw new \RuntimeException("Failed to create clinic: " . json_encode($this->clinicModel->errors()));
            }

            // B. Create Admin User
            // Check if user exists? "Prevent email duplication if user table enforces unique email globally (handle gracefully)."
            // If user exists, do we link them? Requirement: "Create a normal tenant user...". 
            // Implies new user. If email exists, maybe fail? Or reuse?
            // "Initial clinic admin user is Option 1... Create a normal tenant user".
            // I'll fail if email exists to be safe and simple.
            $existingUser = $this->userModel->where('email', $dto['admin_email'])->first();
            if ($existingUser) {
                throw new \RuntimeException("USER_EXISTS: Email {$dto['admin_email']} is already in use.");
            }

            // Prepare User Data
            // Splitting name assuming "First Last"
            $nameParts = explode(' ', $dto['admin_name'], 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? 'Admin';

            $userData = [
                'email' => $dto['admin_email'],
                'username' => explode('@', $dto['admin_email'])[0] . '_' . rand(100,999), // Unique-ish handle
                'password' => $dto['admin_password'], // Plaintext, model handles hashing
                'first_name' => $firstName,
                'last_name' => $lastName,
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $userId = $this->userModel->insert($userData);
            if (!$userId) {
                throw new \RuntimeException("Failed to create user: " . json_encode($this->userModel->errors()));
            }

            // C. Create Membership & Role
            $membershipData = [
                'clinic_id' => $clinicId,
                'user_id' => $userId,
                'role_id' => $role['id'],
                'status' => 'active'
            ];
            
            // Bypass TenantAwareModel session check for this system action? 
            // ClinicUserModel extends Model (not TenantAwareModel in my previous read? Let me check).
            // Wait, I read ClinicUserModel.php earlier.
            // "class ClinicUserModel extends Model". It does NOT extend TenantAwareModel.
            // So insert is direct.
            $this->clinicUserModel->insert($membershipData);

            // D. Create Subscription
            // ClinicSubscriptionModel extends TenantAwareModel?
            // "class ClinicSubscriptionModel extends TenantAwareModel" (from P5-10).
            // TenantAwareModel enforces session('active_clinic_id') in beforeInsert ('setClinicId').
            // We are in Global Mode (clinic_id might be 0 or null).
            // We need to bypass this or manually set data['clinic_id'] which TenantAwareModel accepts if present?
            // "if (isset($data['data']) && !isset($data['data'][$this->tenantField]))"
            // So if we pass clinic_id, it respects it.
            $subData = [
                'clinic_id' => $clinicId,
                'plan_id' => $plan['id'],
                'status' => 'active',
                'start_at' => date('Y-m-d H:i:s')
            ];
            $this->subscriptionModel->insert($subData);

            // E. Audit
            // PlanAuditModel also extends TenantAwareModel.
            $this->auditModel->insert([
                'clinic_id' => $clinicId,
                'actor_user_id' => $actorUserId,
                'action_key' => 'clinic_onboard',
                'reason_code' => 'MANUAL_ONBOARDING',
                'meta_json' => json_encode([
                    'admin_user_id' => $userId,
                    'plan_id' => $plan['id'],
                    'clinic_name' => $clinicData['name'],
                    'timestamp' => time()
                ])
            ]);

            $db->transCommit();
            return $clinicId;

        } catch (\Exception $e) {
            $db->transRollback();
            
            // Audit Failure
            try {
                $reason = 'UNKNOWN_ERROR';
                if (strpos($e->getMessage(), 'NO_ACTIVE_PLAN') !== false) $reason = 'NO_ACTIVE_PLAN';
                elseif (strpos($e->getMessage(), 'INVALID_PLAN') !== false) $reason = 'INVALID_PLAN';
                elseif (strpos($e->getMessage(), 'USER_EXISTS') !== false) $reason = 'USER_EXISTS';
                
                // Clinic ID is 0 for onboarding failures before clinic creation succeeds
                $this->auditModel->insert([
                    'clinic_id' => 0,
                    'actor_user_id' => $actorUserId,
                    'action_key' => 'clinic_onboard_failed',
                    'reason_code' => $reason,
                    'meta_json' => json_encode(['error' => $e->getMessage()]),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $auditEx) {
                log_message('error', 'Failed to log onboarding failure: ' . $auditEx->getMessage());
            }

            log_message('error', "Onboarding Failed: " . $e->getMessage());
            throw $e;
        }
    }
}
