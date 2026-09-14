<?php

namespace App\Services;

use App\Models\ClinicModel;
use App\Models\ClinicUserModel;
use App\Models\RoleModel;
use App\Models\SettingsModel;
use App\Models\UserModel;
use App\Services\ControlPlaneAuditService;
use App\Services\PermissionService;
use Config\Database;

class ClinicAdminLifecycleService
{
    public const STATE_HEALTHY = 'healthy';
    public const STATE_DISABLED_USER = 'disabled_user';
    public const STATE_INACTIVE_MEMBERSHIP = 'inactive_membership';
    public const STATE_MISSING_AUTHORITY = 'missing_authority';
    public const STATE_UNASSIGNED = 'unassigned';

    public const SETTING_KEY_DESIGNATED_ADMIN = 'designated_admin_user_id';

    protected ClinicModel $clinicModel;
    protected ClinicUserModel $clinicUserModel;
    protected UserModel $userModel;
    protected RoleModel $roleModel;
    protected SettingsModel $settingsModel;
    protected ControlPlaneAuditService $auditService;
    protected PermissionService $permissionService;

    public function __construct()
    {
        $this->clinicModel = new ClinicModel();
        $this->clinicUserModel = (new ClinicUserModel())->withoutTenantScope();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->settingsModel = (new SettingsModel())->withoutTenantScope();
        $this->auditService = new ControlPlaneAuditService();
        $this->permissionService = new PermissionService();
    }

    /**
     * Resolve the primary admin role strictly by slug (prefer practice_manager, fallback clinic_admin).
     * Fails closed if absent without using magic numeric IDs.
     */
    public function resolveAdminRole(): array
    {
        $role = $this->roleModel->getBySlug('practice_manager');
        if (!$role) {
            $role = $this->roleModel->getBySlug('clinic_admin');
        }
        if (!$role) {
            throw new \RuntimeException('No clinic administrator role found in the system.');
        }

        return $role;
    }

    /**
     * Check if a given role ID has administrative / management capabilities for a clinic.
     */
    public function isManagerRole(int $roleId): bool
    {
        if ($roleId <= 0) {
            return false;
        }

        $role = $this->roleModel->find($roleId);
        if (!$role) {
            return false;
        }

        return in_array($role['slug'] ?? '', ['practice_manager', 'clinic_admin'], true);
    }

    /**
     * Resolve standard staff role for demotion strictly by slug (prefer read_only, fallback receptionist).
     * Fails closed if absent without using magic numeric IDs.
     */
    public function resolveStaffRole(): array
    {
        $role = $this->roleModel->getBySlug('read_only');
        if (!$role) {
            $role = $this->roleModel->getBySlug('receptionist');
        }
        if (!$role) {
            throw new \RuntimeException('No staff role found for demotion.');
        }

        return $role;
    }

    /**
     * Get the complete administrator status and state for a clinic.
     * Explicit designation is authoritative and NEVER silently replaced with legacy fallback (Finding 1).
     */
    public function getClinicAdministrator(int $clinicId): array
    {
        $clinic = $this->clinicModel->find($clinicId);
        if (!$clinic) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Clinic not found: {$clinicId}");
        }

        $designatedUserId = $this->getDesignatedAdminUserId($clinicId);
        $candidateMembership = null;
        $designationSource = 'none';

        if ($designatedUserId > 0) {
            $candidateMembership = $this->clinicUserModel
                ->where('clinic_id', $clinicId)
                ->where('user_id', $designatedUserId)
                ->first();
            $designationSource = 'designated';
        } else {
            // Legacy oldest-manager fallback ONLY when NO explicit designation exists at all (Finding 1)
            $candidateMembership = $this->findEffectivePrimaryMembership($clinicId);
            if ($candidateMembership) {
                $designationSource = 'oldest_manager';
                $designatedUserId = (int) $candidateMembership['user_id'];
            }
        }

        if (!$candidateMembership && $designatedUserId <= 0) {
            return [
                'has_admin' => false,
                'status' => self::STATE_UNASSIGNED,
                'state' => self::STATE_UNASSIGNED,
                'state_label' => 'Unassigned',
                'is_healthy' => false,
                'account_active' => false,
                'membership_active' => false,
                'has_admin_authority' => false,
                'designation_source' => 'none',
                'assigned_since' => null,
                'issues' => ['No administrator is currently assigned to this clinic.'],
                'admin_user' => null,
                'user' => null,
                'membership' => null,
                'role' => null,
                'admin_name' => 'None',
                'admin_email' => 'None',
                'action_required' => 'assign',
            ];
        }

        $userId = $candidateMembership ? (int) $candidateMembership['user_id'] : $designatedUserId;
        $user = $this->userModel->find($userId);

        if (!$user) {
            return [
                'has_admin' => false,
                'status' => self::STATE_UNASSIGNED,
                'state' => self::STATE_UNASSIGNED,
                'state_label' => 'User Missing',
                'is_healthy' => false,
                'account_active' => false,
                'membership_active' => false,
                'has_admin_authority' => false,
                'designation_source' => $designationSource,
                'assigned_since' => null,
                'issues' => ['The designated user record no longer exists in the database.'],
                'admin_user' => null,
                'user' => null,
                'membership' => $candidateMembership,
                'role' => null,
                'admin_name' => 'Unknown',
                'admin_email' => 'Unknown',
                'action_required' => 'assign',
            ];
        }

        $roleId = $candidateMembership ? (int) ($candidateMembership['role_id'] ?? 0) : 0;
        $role = $roleId > 0 ? $this->roleModel->find($roleId) : null;
        $isManager = $roleId > 0 && $this->isManagerRole($roleId);

        $userActive = (int) ($user['active'] ?? 0) === 1;
        $membershipActive = $candidateMembership && ($candidateMembership['status'] ?? '') === 'active';

        $issues = [];
        if (!$userActive) {
            $issues[] = 'User account is deactivated in the system.';
        }
        if (!$candidateMembership) {
            $issues[] = 'Designated administrator membership in this clinic is missing or revoked.';
        } elseif (!$membershipActive) {
            $issues[] = 'User membership in this clinic is inactive or revoked.';
        }
        if (!$isManager) {
            $issues[] = 'User does not possess the Practice Manager role in this clinic.';
        }

        // Determine State
        if (!$userActive) {
            $state = self::STATE_DISABLED_USER;
            $stateLabel = 'Account Disabled Globally';
            $actionRequired = 'restore_or_replace';
        } elseif (!$membershipActive) {
            $state = self::STATE_INACTIVE_MEMBERSHIP;
            $stateLabel = 'Membership Inactive';
            $actionRequired = 'restore_or_replace';
        } elseif (!$isManager) {
            $state = self::STATE_MISSING_AUTHORITY;
            $stateLabel = 'Missing Admin Authority';
            $actionRequired = 'grant_authority_or_replace';
        } else {
            $state = self::STATE_HEALTHY;
            $stateLabel = 'Healthy';
            $actionRequired = null;
        }

        $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        if ($fullName === '') {
            $fullName = $user['username'] ?? $user['email'];
        }

        $userData = [
            'id' => (int) $user['id'],
            'email' => $user['email'] ?? '',
            'username' => $user['username'] ?? '',
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'phone' => $user['phone'] ?? '',
            'name' => $fullName,
            'active' => (int) ($user['active'] ?? 0),
            'is_active' => $userActive,
        ];

        return [
            'has_admin' => true,
            'status' => $state,
            'state' => $state,
            'state_label' => $stateLabel,
            'is_healthy' => $state === self::STATE_HEALTHY,
            'account_active' => $userActive,
            'membership_active' => $membershipActive,
            'has_admin_authority' => $isManager,
            'designation_source' => $designationSource,
            'assigned_since' => $candidateMembership['created_at'] ?? null,
            'issues' => $issues,
            'admin_user' => $userData,
            'user' => $userData,
            'membership' => $candidateMembership ? [
                'id' => (int) $candidateMembership['id'],
                'clinic_id' => (int) $candidateMembership['clinic_id'],
                'user_id' => (int) $candidateMembership['user_id'],
                'role_id' => (int) $candidateMembership['role_id'],
                'status' => $candidateMembership['status'] ?? 'inactive',
                'is_active' => $membershipActive,
                'created_at' => $candidateMembership['created_at'] ?? null,
            ] : null,
            'role' => $role ? [
                'id' => (int) $role['id'],
                'name' => $role['name'] ?? '',
                'slug' => $role['slug'] ?? '',
                'is_manager' => $isManager,
            ] : null,
            'admin_name' => $fullName,
            'admin_email' => $user['email'] ?? '',
            'action_required' => $actionRequired,
        ];
    }

    /**
     * Get designated admin user ID from settings table.
     */
    private function getDesignatedAdminUserId(int $clinicId): int
    {
        $setting = $this->settingsModel
            ->where('clinic_id', $clinicId)
            ->where('setting_key', self::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();

        return $setting ? (int) ($setting['setting_value'] ?? 0) : 0;
    }

    /**
     * Store designated admin user ID in settings table.
     */
    private function setDesignatedAdminUserId(int $clinicId, int $userId): void
    {
        $existing = $this->settingsModel
            ->where('clinic_id', $clinicId)
            ->where('setting_key', self::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();

        $data = [
            'clinic_id' => $clinicId,
            'setting_key' => self::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $userId,
            'setting_type' => 'number',
            'description' => 'Designated primary clinic administrator user ID',
        ];

        if ($existing) {
            if ((string) ($existing['setting_value'] ?? '') === (string) $userId) {
                return;
            }
            $updated = $this->settingsModel->update($existing['id'], $data);
            if ($updated === false) {
                $errors = $this->settingsModel->errors();
                throw new \RuntimeException('Failed to update designated admin setting: ' . json_encode($errors));
            }
        } else {
            $inserted = $this->settingsModel->insert($data);
            if ($inserted === false) {
                $errors = $this->settingsModel->errors();
                throw new \RuntimeException('Failed to insert designated admin setting: ' . json_encode($errors));
            }
        }
    }

    /**
     * Fallback lookup: find effective primary admin membership for a clinic.
     * Strictly requires a valid manager role (Finding 7).
     * Non-manager memberships are never inferred as administrators.
     */
    private function findEffectivePrimaryMembership(int $clinicId): ?array
    {
        $allMemberships = $this->clinicUserModel
            ->select('clinic_users.*, users.active as user_active')
            ->join('users', 'users.id = clinic_users.user_id')
            ->where('clinic_users.clinic_id', $clinicId)
            ->orderBy('clinic_users.created_at', 'ASC')
            ->findAll();

        if (empty($allMemberships)) {
            return null;
        }

        // Priority 1: Active user, Active membership, Manager role
        foreach ($allMemberships as $m) {
            if ((int) $m['user_active'] === 1 && $m['status'] === 'active' && $this->isManagerRole((int) $m['role_id'])) {
                return $m;
            }
        }

        // Priority 2: Any Manager role
        foreach ($allMemberships as $m) {
            if ($this->isManagerRole((int) $m['role_id'])) {
                return $m;
            }
        }

        // Without a manager role, administrator state is unassigned
        return null;
    }

    /**
     * Assign a user as designated clinic administrator for an unassigned clinic.
     * Assign is for unassigned clinics ONLY; it must not double as replace (Finding 3).
     * Rejects cross-clinic candidates and inactive memberships (Finding 2).
     * Enforces row-level concurrency lock on clinic and membership rows (Fix 1 & 3).
     */
    public function assignAdministrator(int $clinicId, int $userId, int $actorUserId, ?string $reason = null): array
    {
        if ($actorUserId <= 0) {
            throw new \InvalidArgumentException('A valid authenticated actor user ID is required.');
        }

        $clinic = $this->clinicModel->find($clinicId);
        if (!$clinic) {
            return ['success' => false, 'code' => 'clinic_not_found', 'message' => 'Clinic not found.'];
        }

        $targetUser = $this->userModel->find($userId);
        if (!$targetUser) {
            return ['success' => false, 'code' => 'user_not_found', 'message' => 'Selected user not found.'];
        }

        if ((int) ($targetUser['active'] ?? 0) !== 1) {
            return ['success' => false, 'code' => 'user_inactive', 'message' => 'Cannot assign a deactivated user. Activate the user account first.'];
        }

        // Cross-plane security: Super Admin accounts must not be assigned as clinic admins
        if ($this->permissionService->isSuperAdmin($userId)) {
            return ['success' => false, 'code' => 'superadmin_forbidden', 'message' => 'Super Admin accounts cannot be assigned as clinic administrators.'];
        }

        $adminRole = $this->resolveAdminRole();
        $db = Database::connect();
        $db->transBegin();

        try {
            // Row-level lock on the clinic record to serialize administrative mutations (Finding 3)
            $db->query('SELECT id FROM clinics WHERE id = ? FOR UPDATE', [$clinicId]);

            // Lock and re-read settings row (Fix 3)
            $db->query('SELECT id FROM settings WHERE clinic_id = ? AND setting_key = ? FOR UPDATE', [
                $clinicId,
                self::SETTING_KEY_DESIGNATED_ADMIN,
            ]);

            // Assign is strictly for an unassigned clinic only (Finding 3: Assign must not double as Replace)
            $currentAdmin = $this->getClinicAdministrator($clinicId);
            if ($currentAdmin['has_admin']) {
                $db->transRollback();
                return [
                    'success' => false,
                    'code' => 'already_assigned',
                    'message' => 'An administrator is already assigned to this clinic. Use the Replace Administrator action to change administrators.',
                ];
            }

            // Lock and re-read target membership row inside transaction (Fix 3)
            $targetMembership = $db->query(
                'SELECT * FROM clinic_users WHERE clinic_id = ? AND user_id = ? FOR UPDATE',
                [$clinicId, $userId]
            )->getRowArray();

            if (!$targetMembership) {
                $db->transRollback();
                return [
                    'success' => false,
                    'code' => 'cross_clinic_forbidden',
                    'message' => 'The selected user is not a member of this clinic. Cross-clinic administrator assignment is forbidden.',
                ];
            }

            // Revalidate active status on locked row inside transaction (Fix 3)
            if (($targetMembership['status'] ?? '') !== 'active') {
                $db->transRollback();
                return [
                    'success' => false,
                    'code' => 'inactive_membership_forbidden',
                    'message' => 'The selected user has an inactive or revoked membership in this clinic. Inactive memberships cannot be assigned as administrator.',
                ];
            }

            // Promote target user membership to administrator role (do not alter membership status)
            $updated = $this->clinicUserModel->update($targetMembership['id'], [
                'role_id' => $adminRole['id'],
            ]);
            if ($updated === false) {
                $errors = $this->clinicUserModel->errors();
                throw new \RuntimeException('Failed to update clinic user membership role: ' . json_encode($errors));
            }

            // Record designated admin in clinic settings
            $this->setDesignatedAdminUserId($clinicId, $userId);

            // Audit with explicit actor and transactional fail-closed enforcement (Finding 3)
            $this->auditService->logEvent('clinic_admin_assigned', [
                'actor_user_id' => $actorUserId,
                'route' => "/controlplane/clinics/{$clinicId}/admin/assign",
                'method' => 'POST',
                'metadata' => [
                    'clinic_id' => $clinicId,
                    'actor_user_id' => $actorUserId,
                    'target_user_id' => $userId,
                    'target_email' => $targetUser['email'] ?? '',
                    'previous_user_id' => null,
                    'role_id' => $adminRole['id'],
                    'reason' => $reason ?? 'Assigned by control-plane operator',
                ],
            ], failClosed: true);

            $db->transCommit();

            return [
                'success' => true,
                'message' => "Administrator successfully assigned to {$clinic['name']}.",
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', "Failed to assign clinic admin for clinic {$clinicId}: " . $e->getMessage(), ['exception' => $e]);
            return [
                'success' => false,
                'code' => 'system_error',
                'message' => 'An unexpected error occurred while assigning the clinic administrator. Please try again.',
            ];
        }
    }

    /**
     * Replace an existing clinic administrator.
     * Enforces clinic membership boundary and active status (Finding 2).
     * Enforces row-level concurrency lock and stale-state checks (Finding 3 & 4).
     */
    public function replaceAdministrator(
        int $clinicId,
        int $newUserId,
        int $actorUserId,
        string|bool $previousAdminAction = 'demote_to_staff',
        ?string $reason = null,
        ?int $expectedCurrentAdminUserId = null
    ): array {
        if ($actorUserId <= 0) {
            throw new \InvalidArgumentException('A valid authenticated actor user ID is required.');
        }

        if (is_bool($previousAdminAction)) {
            $previousAdminAction = $previousAdminAction ? 'demote_to_staff' : 'keep_role';
        }

        $clinic = $this->clinicModel->find($clinicId);
        if (!$clinic) {
            return ['success' => false, 'code' => 'clinic_not_found', 'message' => 'Clinic not found.'];
        }

        $newUser = $this->userModel->find($newUserId);
        if (!$newUser) {
            return ['success' => false, 'code' => 'user_not_found', 'message' => 'Replacement user not found.'];
        }

        if ((int) ($newUser['active'] ?? 0) !== 1) {
            return ['success' => false, 'code' => 'user_inactive', 'message' => 'Replacement user account is deactivated.'];
        }

        if ($this->permissionService->isSuperAdmin($newUserId)) {
            return ['success' => false, 'code' => 'superadmin_forbidden', 'message' => 'Super Admin accounts cannot be assigned as clinic administrators.'];
        }

        $adminRole = $this->resolveAdminRole();
        $staffRole = $this->resolveStaffRole();

        $db = Database::connect();
        $db->transBegin();

        try {
            // Row-level lock on the clinic record to serialize administrative mutations (Finding 3 & 4)
            $db->query('SELECT id FROM clinics WHERE id = ? FOR UPDATE', [$clinicId]);

            // Lock and re-read settings row (Fix 3)
            $db->query('SELECT id FROM settings WHERE clinic_id = ? AND setting_key = ? FOR UPDATE', [
                $clinicId,
                self::SETTING_KEY_DESIGNATED_ADMIN,
            ]);

            // Lock and re-read replacement user membership row inside transaction (Fix 3)
            $targetMembership = $db->query(
                'SELECT * FROM clinic_users WHERE clinic_id = ? AND user_id = ? FOR UPDATE',
                [$clinicId, $newUserId]
            )->getRowArray();

            if (!$targetMembership) {
                $db->transRollback();
                return [
                    'success' => false,
                    'code' => 'cross_clinic_forbidden',
                    'message' => 'The selected replacement user is not a member of this clinic. Cross-clinic administrator assignment is forbidden.',
                ];
            }

            // Revalidate active status on locked row inside transaction (Fix 3)
            if (($targetMembership['status'] ?? '') !== 'active') {
                $db->transRollback();
                return [
                    'success' => false,
                    'code' => 'inactive_membership_forbidden',
                    'message' => 'The selected replacement user has an inactive or revoked membership in this clinic. Inactive memberships cannot be appointed as administrator.',
                ];
            }

            $currentAdmin = $this->getClinicAdministrator($clinicId);
            $previousUserId = $currentAdmin['has_admin'] && $currentAdmin['user'] ? (int) $currentAdmin['user']['id'] : null;

            // Concurrency / Stale Replacement Check (Finding 4)
            if ($expectedCurrentAdminUserId !== null) {
                $expectedId = (int) $expectedCurrentAdminUserId;
                $actualId = $previousUserId !== null ? (int) $previousUserId : 0;
                if ($expectedId !== $actualId) {
                    $db->transRollback();
                    return [
                        'success' => false,
                        'code' => 'stale_state',
                        'message' => 'The clinic administrator has been modified by another operator. Please refresh the page and try again.',
                    ];
                }
            }

            if ($previousUserId === $newUserId) {
                $db->transRollback();
                return ['success' => false, 'code' => 'already_admin', 'message' => 'The selected user is already the administrator of this clinic.'];
            }

            // Handle previous administrator
            if ($previousUserId) {
                // Lock and re-read previous administrator membership row (Fix 3)
                $prevMembership = $db->query(
                    'SELECT * FROM clinic_users WHERE clinic_id = ? AND user_id = ? FOR UPDATE',
                    [$clinicId, $previousUserId]
                )->getRowArray();

                if ($prevMembership) {
                    if ($previousAdminAction === 'deactivate_membership') {
                        $updatedPrev = $this->clinicUserModel->update($prevMembership['id'], [
                            'status' => 'inactive',
                        ]);
                        if ($updatedPrev === false) {
                            $errors = $this->clinicUserModel->errors();
                            throw new \RuntimeException('Failed to deactivate previous admin membership: ' . json_encode($errors));
                        }
                    } elseif ($previousAdminAction === 'keep_role') {
                        // Keep current role and membership
                    } else {
                        // Default: demote to staff role, keep membership active
                        $updatedPrev = $this->clinicUserModel->update($prevMembership['id'], [
                            'role_id' => $staffRole['id'],
                            'status' => 'active',
                        ]);
                        if ($updatedPrev === false) {
                            $errors = $this->clinicUserModel->errors();
                            throw new \RuntimeException('Failed to demote previous admin membership: ' . json_encode($errors));
                        }
                    }
                }
            }

            // Promote new administrator membership to administrator role (preserve active status)
            $updatedTarget = $this->clinicUserModel->update($targetMembership['id'], [
                'role_id' => $adminRole['id'],
            ]);
            if ($updatedTarget === false) {
                $errors = $this->clinicUserModel->errors();
                throw new \RuntimeException('Failed to promote replacement admin membership: ' . json_encode($errors));
            }

            $this->setDesignatedAdminUserId($clinicId, $newUserId);

            // Audit with explicit actor and transactional fail-closed enforcement (Finding 3)
            $this->auditService->logEvent('clinic_admin_replaced', [
                'actor_user_id' => $actorUserId,
                'route' => "/controlplane/clinics/{$clinicId}/admin/replace",
                'method' => 'POST',
                'metadata' => [
                    'clinic_id' => $clinicId,
                    'actor_user_id' => $actorUserId,
                    'new_user_id' => $newUserId,
                    'new_email' => $newUser['email'] ?? '',
                    'previous_user_id' => $previousUserId,
                    'previous_admin_action' => $previousAdminAction,
                    'role_id' => $adminRole['id'],
                    'reason' => $reason ?? 'Replaced by control-plane operator',
                ],
            ], failClosed: true);

            $db->transCommit();

            return [
                'success' => true,
                'message' => "Administrator successfully replaced for {$clinic['name']}.",
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', "Failed to replace clinic admin for clinic {$clinicId}: " . $e->getMessage(), ['exception' => $e]);
            return [
                'success' => false,
                'code' => 'system_error',
                'message' => 'An unexpected error occurred while replacing the clinic administrator. Please try again.',
            ];
        }
    }

    /**
     * Restore an administrator whose clinic membership or authority is degraded.
     * Restore repairs ONLY the designated administrator (Finding 4).
     * Rejects target mismatches and missing designated settings.
     * Decouples global account activation from clinic restore (Finding 1).
     * Enforces row-level concurrency lock on clinic and membership rows (Finding 3 & Fix 3).
     */
    public function restoreAdministrator(
        int $clinicId,
        int $userId,
        int $actorUserId,
        ?string $reason = null,
        ?int $expectedDesignatedAdminUserId = null
    ): array {
        if ($actorUserId <= 0) {
            throw new \InvalidArgumentException('A valid authenticated actor user ID is required.');
        }

        $clinic = $this->clinicModel->find($clinicId);
        if (!$clinic) {
            return ['success' => false, 'code' => 'clinic_not_found', 'message' => 'Clinic not found.'];
        }

        $adminRole = $this->resolveAdminRole();
        $db = Database::connect();
        $db->transBegin();

        try {
            // Row-level lock on the clinic record to serialize administrative mutations (Finding 3 & 4)
            $db->query('SELECT id FROM clinics WHERE id = ? FOR UPDATE', [$clinicId]);

            // Lock and re-read settings row (Fix 3)
            $settingRow = $db->query(
                'SELECT * FROM settings WHERE clinic_id = ? AND setting_key = ? FOR UPDATE',
                [$clinicId, self::SETTING_KEY_DESIGNATED_ADMIN]
            )->getRowArray();

            $designatedUserId = $settingRow ? (int) ($settingRow['setting_value'] ?? 0) : 0;
            if ($designatedUserId <= 0) {
                $db->transRollback();
                return [
                    'success' => false,
                    'code' => 'no_designated_admin',
                    'message' => 'No designated administrator is recorded for this clinic. Use Assign Administrator instead.',
                ];
            }

            // Restore must repair ONLY the designated administrator (Finding 4)
            if ($designatedUserId !== $userId) {
                $db->transRollback();
                return [
                    'success' => false,
                    'code' => 'target_mismatch',
                    'message' => 'The selected user does not match the designated administrator for this clinic. Use Replace Administrator to appoint a different administrator.',
                ];
            }

            // Concurrency / Stale Designated Admin Check (Finding 4)
            if ($expectedDesignatedAdminUserId !== null && (int) $expectedDesignatedAdminUserId !== $designatedUserId) {
                $db->transRollback();
                return [
                    'success' => false,
                    'code' => 'stale_state',
                    'message' => 'The designated administrator has changed. Please refresh and try again.',
                ];
            }

            // Lock and re-read user account status inside transaction (Fix 3)
            $userRow = $db->query('SELECT * FROM users WHERE id = ? FOR UPDATE', [$userId])->getRowArray();
            if (!$userRow) {
                $db->transRollback();
                return ['success' => false, 'code' => 'user_not_found', 'message' => 'User not found.'];
            }

            // Global Account Activation Decoupling (Finding 1)
            if ((int) ($userRow['active'] ?? 0) !== 1) {
                $db->transRollback();
                return [
                    'success' => false,
                    'code' => 'account_disabled',
                    'message' => 'The user account is deactivated globally. Reactivate the user account in User Management before restoring clinic administrator authority.',
                ];
            }

            if ($this->permissionService->isSuperAdmin($userId)) {
                $db->transRollback();
                return ['success' => false, 'code' => 'superadmin_forbidden', 'message' => 'Super Admin cannot be assigned as clinic administrator.'];
            }

            // Lock and re-read membership row inside transaction (Fix 3)
            $membership = $db->query(
                'SELECT * FROM clinic_users WHERE clinic_id = ? AND user_id = ? FOR UPDATE',
                [$clinicId, $userId]
            )->getRowArray();

            if ($membership) {
                $updatedMem = $this->clinicUserModel->update($membership['id'], [
                    'role_id' => $adminRole['id'],
                    'status' => 'active',
                ]);
                if ($updatedMem === false) {
                    $errors = $this->clinicUserModel->errors();
                    throw new \RuntimeException('Failed to update restored admin membership: ' . json_encode($errors));
                }
            } else {
                // If membership was revoked/missing for the designated admin, repair it
                $insertedMem = $this->clinicUserModel->insert([
                    'clinic_id' => $clinicId,
                    'user_id' => $userId,
                    'role_id' => $adminRole['id'],
                    'status' => 'active',
                ]);
                if ($insertedMem === false) {
                    $errors = $this->clinicUserModel->errors();
                    throw new \RuntimeException('Failed to insert restored admin membership: ' . json_encode($errors));
                }
            }

            // Re-assert designated admin in clinic settings
            $this->setDesignatedAdminUserId($clinicId, $userId);

            // Audit with explicit actor and transactional fail-closed enforcement (Finding 3)
            $this->auditService->logEvent('clinic_admin_restored', [
                'actor_user_id' => $actorUserId,
                'route' => "/controlplane/clinics/{$clinicId}/admin/restore",
                'method' => 'POST',
                'metadata' => [
                    'clinic_id' => $clinicId,
                    'actor_user_id' => $actorUserId,
                    'target_user_id' => $userId,
                    'target_email' => $userRow['email'] ?? '',
                    'role_id' => $adminRole['id'],
                    'reason' => $reason ?? 'Restored by control-plane operator',
                ],
            ], failClosed: true);

            $db->transCommit();

            return [
                'success' => true,
                'message' => "Administrator successfully restored for {$clinic['name']}.",
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', "Failed to restore clinic admin for clinic {$clinicId}: " . $e->getMessage(), ['exception' => $e]);
            return [
                'success' => false,
                'code' => 'system_error',
                'message' => 'An unexpected error occurred while restoring the clinic administrator. Please try again.',
            ];
        }
    }

    /**
     * Get active eligible users who already belong to this clinic with active membership (Finding 2).
     */
    public function getEligibleUsersForClinic(int $clinicId): array
    {
        $memberships = $this->clinicUserModel
            ->select('clinic_users.*, users.id as uid, users.email, users.username, users.first_name, users.last_name, users.active as user_active, roles.name as role_name')
            ->join('users', 'users.id = clinic_users.user_id')
            ->join('roles', 'roles.id = clinic_users.role_id', 'left')
            ->where('clinic_users.clinic_id', $clinicId)
            ->where('clinic_users.status', 'active')
            ->where('users.active', 1)
            ->orderBy('users.email', 'ASC')
            ->findAll();

        $eligible = [];
        foreach ($memberships as $m) {
            $userId = (int) $m['uid'];
            // Exclude superadmin
            if ($this->permissionService->isSuperAdmin($userId)) {
                continue;
            }

            $name = trim(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? ''));
            if ($name === '') {
                $name = $m['username'] ?? $m['email'];
            }

            $eligible[] = [
                'id' => $userId,
                'email' => $m['email'] ?? '',
                'username' => $m['username'] ?? '',
                'first_name' => $m['first_name'] ?? '',
                'last_name' => $m['last_name'] ?? '',
                'name' => $name,
                'is_member' => true,
                'membership_status' => $m['status'] ?? null,
                'current_role' => $m['role_name'] ?? null,
                'clinic_role_title' => $m['role_name'] ?? 'Staff',
            ];
        }

        return $eligible;
    }

    /**
     * Run read-only data integrity audit on clinic administrators across all clinics.
     */
    public function runDataIntegrityAudit(): array
    {
        $clinics = $this->clinicModel->orderBy('id', 'ASC')->findAll();

        $stats = [
            'total_clinics' => count($clinics),
            'healthy_count' => 0,
            'disabled_user_count' => 0,
            'inactive_membership_count' => 0,
            'missing_authority_count' => 0,
            'unassigned_count' => 0,
            'multiple_managers_count' => 0,
            'clinics' => [],
            'anomalies' => [],
        ];

        foreach ($clinics as $c) {
            $clinicId = (int) $c['id'];
            $adminInfo = $this->getClinicAdministrator($clinicId);

            // Count active managers in clinic
            $memberships = $this->clinicUserModel
                ->where('clinic_id', $clinicId)
                ->where('status', 'active')
                ->findAll();

            $managerCount = 0;
            foreach ($memberships as $m) {
                if ($this->isManagerRole((int) ($m['role_id'] ?? 0))) {
                    $managerCount++;
                }
            }

            if ($managerCount > 1) {
                $stats['multiple_managers_count']++;
            }

            $stats['clinics'][$clinicId] = [
                'clinic_id' => $clinicId,
                'clinic_name' => $c['name'] ?? '',
                'state' => $adminInfo['state'],
                'is_healthy' => $adminInfo['is_healthy'],
                'admin_user_id' => $adminInfo['admin_user']['id'] ?? null,
                'admin_email' => $adminInfo['admin_email'] ?? null,
                'multiple_managers' => $managerCount > 1,
            ];

            switch ($adminInfo['state']) {
                case self::STATE_HEALTHY:
                    $stats['healthy_count']++;
                    break;
                case self::STATE_DISABLED_USER:
                    $stats['disabled_user_count']++;
                    $stats['anomalies'][] = [
                        'clinic_id' => $clinicId,
                        'clinic_name' => $c['name'],
                        'issue' => 'Designated administrator account is deactivated globally',
                        'admin_user_id' => $adminInfo['user']['id'] ?? null,
                    ];
                    break;
                case self::STATE_INACTIVE_MEMBERSHIP:
                    $stats['inactive_membership_count']++;
                    $stats['anomalies'][] = [
                        'clinic_id' => $clinicId,
                        'clinic_name' => $c['name'],
                        'issue' => 'Administrator membership is marked inactive in clinic',
                        'admin_user_id' => $adminInfo['user']['id'] ?? null,
                    ];
                    break;
                case self::STATE_MISSING_AUTHORITY:
                    $stats['missing_authority_count']++;
                    $stats['anomalies'][] = [
                        'clinic_id' => $clinicId,
                        'clinic_name' => $c['name'],
                        'issue' => 'Assigned user does not hold an administrative role',
                        'admin_user_id' => $adminInfo['user']['id'] ?? null,
                    ];
                    break;
                case self::STATE_UNASSIGNED:
                default:
                    $stats['unassigned_count']++;
                    $stats['anomalies'][] = [
                        'clinic_id' => $clinicId,
                        'clinic_name' => $c['name'],
                        'issue' => 'No administrator assigned to this clinic',
                        'admin_user_id' => null,
                    ];
                    break;
            }
        }

        return $stats;
    }
}
