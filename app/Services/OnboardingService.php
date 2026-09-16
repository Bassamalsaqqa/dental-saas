<?php

namespace App\Services;

use App\Models\ClinicModel;
use App\Models\UserModel;
use App\Models\ClinicUserModel;
use App\Models\UserRoleModel;
use App\Models\RoleModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\PlanModel;
use App\Models\PlanAuditModel;
use App\Models\SettingsModel;
use App\Services\ClinicAdminLifecycleService;
use App\Services\PermissionService;

class OnboardingService
{
    protected $clinicModel;
    protected $userModel;
    protected $clinicUserModel;
    protected $userRoleModel;
    protected $roleModel;
    protected $subscriptionModel;
    protected $planModel;
    protected $auditModel;
    protected $settingsModel;
    protected $permissionService;
    protected $controlPlaneAuditService;

    public function __construct()
    {
        $this->clinicModel        = new ClinicModel();
        $this->userModel          = new UserModel();
        // All tenant-aware models require explicit bypass for Control Plane global mode
        $this->clinicUserModel    = (new ClinicUserModel())->withoutTenantScope();
        $this->userRoleModel      = new UserRoleModel();
        $this->roleModel          = new RoleModel();
        $this->subscriptionModel  = (new ClinicSubscriptionModel())->withoutTenantScope();
        $this->planModel          = new PlanModel();
        $this->auditModel         = (new PlanAuditModel())->withoutTenantScope();
        $this->settingsModel      = (new SettingsModel())->withoutTenantScope();
        $this->permissionService  = new PermissionService();
        $this->controlPlaneAuditService = new ControlPlaneAuditService();
    }

    /**
     * Create a new clinic with an admin user and active subscription.
     *
     * Must be called in Control Plane global mode (no active_clinic_id in session).
     * All writes are explicit-clinic-scoped; no TenantAwareModel auto-inject is relied upon.
     * Persists the canonical designated_admin_user_id setting per ClinicAdminLifecycleService contract.
     *
     * @param array $dto [clinic_name, admin_name, admin_email, admin_password, plan_id]
     * @param int   $actorUserId ID of the superadmin performing the action
     * @return int  The ID of the created clinic
     * @throws \RuntimeException on any failure (transaction is rolled back before throwing)
     */
    public function createClinicWithAdmin(array $dto, int $actorUserId): int
    {
        // 1. Fail-closed: require global mode
        $actor = $this->userModel->find($actorUserId);
        if (!session()->get('global_mode') || (int) session()->get('user_id') !== $actorUserId
            || !$actor || (int) $actor['active'] !== 1 || !$this->permissionService->isSuperAdmin($actorUserId)) {
            throw new \RuntimeException('ACCESS_DENIED: Onboarding requires Control Plane Global Mode.');
        }

        $db = \Config\Database::connect();
        // This workflow owns its commit; never commit or partially roll back a caller's work.
        if ($db->transDepth !== 0) {
            throw new \RuntimeException('TRANSACTION_FAILED: Onboarding requires an independent transaction.');
        }
        $dto['clinic_name'] = trim((string) ($dto['clinic_name'] ?? ''));
        $dto['admin_name'] = trim((string) ($dto['admin_name'] ?? ''));
        if (mb_strlen($dto['clinic_name']) < 3 || mb_strlen($dto['clinic_name']) > 100
            || mb_strlen($dto['admin_name']) < 3 || mb_strlen($dto['admin_name']) > 100
            || !filter_var(trim((string) ($dto['admin_email'] ?? '')), FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('VALIDATION_FAILED: Invalid onboarding details.');
        }

        // 2. Validate plan
        $plan = $this->planModel->find((int) ($dto['plan_id'] ?? 0));
        if (!$plan || $plan['status'] !== 'active') {
            throw new \RuntimeException('INVALID_PLAN: Selected plan is missing or inactive.');
        }

        // 3. Pre-validate admin user if they already exist
        $adminEmail   = trim((string) ($dto['admin_email'] ?? ''));
        $existingUser = $this->userModel->where('email', $adminEmail)->first();
        if (!$existingUser && strlen((string) ($dto['admin_password'] ?? '')) < 8) {
            throw new \RuntimeException('VALIDATION_FAILED: A password of at least eight characters is required.');
        }
        if ($existingUser) {
            $existingUserId = (int) $existingUser['id'];
            if ($this->permissionService->isSuperAdmin($existingUserId)) {
                throw new \RuntimeException('SUPERADMIN_FORBIDDEN: Super Admin accounts cannot be assigned as clinic administrators.');
            }
            if ((int) ($existingUser['active'] ?? 0) !== 1) {
                throw new \RuntimeException('USER_INACTIVE: Cannot assign a deactivated user. Activate the user account first.');
            }
        }

        // 4. Resolve the manager role (mirrors ClinicAdminLifecycleService priority)
        $role = $this->resolveOnboardingRole();

        helper('text');

        if (!$db->transBegin()) {
            throw new \RuntimeException('TRANSACTION_FAILED: Could not begin onboarding.');
        }

        try {
            if ($existingUser) {
                $existingUser = $db->query('SELECT * FROM users WHERE id = ? FOR UPDATE', [$existingUser['id']])->getRowArray();
                if (!$existingUser || (int) $existingUser['active'] !== 1) {
                    throw new \RuntimeException('USER_INACTIVE: Administrator account is no longer active.');
                }
                if ($this->permissionService->isSuperAdmin((int) $existingUser['id'])) {
                    throw new \RuntimeException('SUPERADMIN_FORBIDDEN: Super Admin accounts cannot be clinic administrators.');
                }
            }
            // A. Create clinic
            $clinicData = [
                'name'   => $dto['clinic_name'],
                'status' => 'active',
                'slug'   => url_title($dto['clinic_name'], '-', true) . '-' . bin2hex(random_bytes(6)),
                'uuid'   => $this->generateUuid(),
            ];
            $clinicId = $this->clinicModel->insert($clinicData);
            if (!$clinicId) {
                throw new \RuntimeException('CLINIC_CREATE_FAILED: ' . json_encode($this->clinicModel->errors()));
            }
            $clinicId = (int) $clinicId;

            // B. Resolve or create admin user
            if ($existingUser) {
                $userId = (int) $existingUser['id'];
            } else {
                $nameParts = explode(' ', (string) ($dto['admin_name'] ?? 'Admin'), 2);
                $firstName = trim($nameParts[0]) ?: 'Clinic';
                $lastName  = isset($nameParts[1]) && trim($nameParts[1]) !== '' ? trim($nameParts[1]) : 'Admin';

                $userData = [
                    'email'      => $adminEmail,
                    'username'   => substr(explode('@', $adminEmail)[0], 0, 17) . '_' . bin2hex(random_bytes(6)),
                    'password'   => (string) ($dto['admin_password'] ?? ''),
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'active'     => 1,
                    'created_on' => time(),
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                $userId = $this->userModel->insert($userData);
                if (!$userId) {
                    throw new \RuntimeException('USER_CREATE_FAILED: ' . json_encode($this->userModel->errors()));
                }
                $userId = (int) $userId;

                // Seed IonAuth members group for new users
                $memberGroup = $db->table('groups')->where('name', 'members')->get()->getRowArray();
                if (!$memberGroup) {
                    throw new \RuntimeException('BOOTSTRAP_MISSING: Required members group is missing.');
                }
                if ($memberGroup) {
                    $alreadyInGroup = $db->table('users_groups')
                        ->where('user_id', $userId)
                        ->where('group_id', $memberGroup['id'])
                        ->countAllResults();
                    if ($alreadyInGroup === 0) {
                        if (!$db->table('users_groups')->insert([
                            'user_id'  => $userId,
                            'group_id' => $memberGroup['id'],
                        ])) {
                            throw new \RuntimeException('USER_CREATE_FAILED: Could not assign authentication group.');
                        }
                    }
                }
            }

            // C. Create or refresh clinic membership with explicit clinic_id.
            $membershipPayload = [
                'clinic_id' => $clinicId,
                'user_id'   => $userId,
                'role_id'   => $role['id'],
                'status'    => 'active',
            ];
            $existingMembership = $this->clinicUserModel
                ->where('clinic_id', $clinicId)
                ->where('user_id', $userId)
                ->first();
            if ($existingMembership) {
                $updated = $this->clinicUserModel->update($existingMembership['id'], $membershipPayload);
                if ($updated === false) {
                    throw new \RuntimeException('MEMBERSHIP_UPDATE_FAILED: ' . json_encode($this->clinicUserModel->errors()));
                }
            } else {
                $membershipId = $this->clinicUserModel->insert($membershipPayload);
                if (!$membershipId) {
                    throw new \RuntimeException('MEMBERSHIP_CREATE_FAILED: ' . json_encode($this->clinicUserModel->errors()));
                }
            }

            // D. Assign RBAC role globally so permission checks pass
            if (!$this->userRoleModel->assignRole($userId, $role['id'], $actorUserId)) {
                throw new \RuntimeException('MEMBERSHIP_CREATE_FAILED: Could not assign administrator role.');
            }

            // E. Create subscription with explicit clinic_id.
            $subId = $this->subscriptionModel->insert([
                'clinic_id' => $clinicId,
                'plan_id'   => $plan['id'],
                'status'    => 'active',
                // Subscription start_at must be UTC so SubscriptionFilter's strtotime()
                // (runs before BaseController::applyRuntimeTimezone) compares the correct instant.
                'start_at'  => gmdate('Y-m-d H:i:s'),
            ]);
            if (!$subId) {
                throw new \RuntimeException('SUBSCRIPTION_CREATE_FAILED: ' . json_encode($this->subscriptionModel->errors()));
            }

            // F. Persist canonical designated-admin setting (ClinicAdminLifecycleService contract)
            $now              = date('Y-m-d H:i:s');
            $settingsBuilder  = $db->table('settings');
            $existingAdminRow = $settingsBuilder
                ->where('clinic_id', $clinicId)
                ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
                ->get()
                ->getRowArray();

            $adminSettingData = [
                'clinic_id'     => $clinicId,
                'setting_key'   => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
                'setting_value' => (string) $userId,
                'setting_type'  => 'number',
                'description'   => 'Designated primary clinic administrator user ID',
                'updated_at'    => $now,
            ];
            if ($existingAdminRow) {
                if ($settingsBuilder->where('id', $existingAdminRow['id'])->update($adminSettingData) === false) {
                    throw new \RuntimeException('SETTINGS_UPDATE_FAILED: Could not update designated admin setting.');
                }
            } else {
                $adminSettingData['created_at'] = $now;
                if ($settingsBuilder->insert($adminSettingData) === false) {
                    throw new \RuntimeException('SETTINGS_INSERT_FAILED: Could not insert designated admin setting.');
                }
            }

            // G. Seed clinic defaults
            $this->seedClinicDefaults($clinicId, $clinicData['name']);

            // H. Audit
            $auditId = $this->auditModel->insert([
                'clinic_id'    => $clinicId,
                'actor_user_id'=> $actorUserId,
                'action_key'   => 'clinic_onboard',
                'reason_code'  => 'MANUAL_ONBOARDING',
                'meta_json'    => json_encode([
                    'admin_user_id' => $userId,
                    'plan_id'       => $plan['id'],
                    'clinic_name'   => $clinicData['name'],
                    'timestamp'     => time(),
                ]),
            ]);
            if (!$auditId) {
                throw new \RuntimeException('AUDIT_CREATE_FAILED: ' . json_encode($this->auditModel->errors()));
            }

            if (!$this->controlPlaneAuditService->logEvent('onboarding_success', [
                'actor_user_id' => $actorUserId,
                'route' => '/controlplane/onboarding/clinic/create',
                'metadata' => ['clinic_id' => $clinicId, 'admin_user_id' => $userId, 'plan_id' => (int) $plan['id']],
            ], true)) {
                throw new \RuntimeException('AUDIT_CREATE_FAILED: Could not record control-plane success audit.');
            }
            if ($db->transStatus() === false || !$db->transCommit()) {
                throw new \RuntimeException('TRANSACTION_FAILED: Database commit failed.');
            }

            return $clinicId;

        } catch (\Throwable $e) {
            if ($db->transDepth > 0) {
                $db->transRollback();
            }
            log_message('error', 'Onboarding Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function seedClinicDefaults(int $clinicId, string $clinicName): void
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('settings');

        $defaults = [
            'clinic_name'          => $clinicName,
            'timezone'             => $this->settingsModel->getGlobalSetting('default_timezone', 'Asia/Jerusalem'),
            'date_format'          => $this->settingsModel->getGlobalSetting('default_date_format', 'd/m/Y'),
            'time_format'          => $this->settingsModel->getGlobalSetting('default_time_format', '12'),
            'currency'             => $this->settingsModel->getGlobalSetting('default_currency', 'ILS'),
            'currency_position'    => $this->settingsModel->getGlobalSetting('default_currency_position', 'after'),
            'appointment_duration' => (int) $this->settingsModel->getGlobalSetting('default_appointment_duration', 30),
            'working_hours_start'  => $this->settingsModel->getGlobalSetting('default_working_hours_start', '09:00'),
            'working_hours_end'    => $this->settingsModel->getGlobalSetting('default_working_hours_end', '17:00'),
            'working_days'         => $this->settingsModel->getGlobalSetting('default_working_days', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
        ];

        $now = date('Y-m-d H:i:s');

        foreach ($defaults as $key => $value) {
            $exists = $builder->where('clinic_id', $clinicId)->where('setting_key', $key)->countAllResults();
            if ($exists) {
                continue;
            }

            $type        = 'string';
            $storedValue = $value;
            if ($key === 'working_days') {
                $type        = 'json';
                $storedValue = json_encode($value);
            } elseif (is_numeric($value)) {
                $type = 'number';
            }

            if (!$builder->insert([
                'clinic_id'     => $clinicId,
                'setting_key'   => $key,
                'setting_value' => (string) $storedValue,
                'setting_type'  => $type,
                'description'   => 'Auto-seeded from control plane defaults',
                'created_at'    => $now,
                'updated_at'    => $now,
            ])) {
                throw new \RuntimeException('SETTINGS_INSERT_FAILED: Could not seed clinic defaults.');
            }
        }
    }

    private function resolveOnboardingRole(): array
    {
        // Mirror ClinicAdminLifecycleService::resolveAdminRole() priority
        $role = $this->roleModel->getBySlug('practice_manager');
        if (!$role) {
            $role = $this->roleModel->getBySlug('clinic_admin');
        }

        if (!$role || !(int) ($role['is_active'] ?? 1)) {
            throw new \RuntimeException('BOOTSTRAP_MISSING: Required administrator role is missing or inactive.');
        }

        return $role;
    }

    private function generateUuid(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        $hex     = bin2hex($data);

        return sprintf('%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }
}
