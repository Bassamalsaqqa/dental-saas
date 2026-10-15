<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ClinicModel;
use App\Models\ClinicUserModel;
use App\Models\RoleModel;
use App\Models\SettingsModel;
use App\Models\UserModel;
use App\Models\IonAuthModel;
use App\Services\ClinicAdminLifecycleService;
use App\Services\ControlPlaneAuditService;
use App\Services\PermissionService;
use App\Services\PlanGuard;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

/**
 * Canonical checked membership mutation service.
 *
 * Implements SEC01, SEC02, and SEC03:
 * - Atomic tenant creation with checked results (identity + group + membership + audit)
 * - Row locking with consistent ordering: clinic row first, then target rows
 * - Protects last active clinic manager and designated administrator
 * - Enforces clinic_users as sole tenant role authority (never mutates global user_roles)
 * - Demotion/deactivation immediately removes tenant authority
 * - Explicit actor/session/clinic/mode authorization and in-transaction revalidation
 * - Single owned transaction wrapper rejecting nesting with checked begin, writes, status, commit
 */
class ClinicMembershipService
{
    protected BaseConnection $db;
    protected ClinicModel $clinicModel;
    protected ClinicUserModel $clinicUserModel;
    protected UserModel $userModel;
    protected RoleModel $roleModel;
    protected SettingsModel $settingsModel;
    protected IonAuthModel $ionAuthModel;
    protected ControlPlaneAuditService $auditService;
    protected PermissionService $permissionService;
    protected PlanGuard $planGuard;

    public function __construct(
        ?BaseConnection $db = null,
        ?ClinicModel $clinicModel = null,
        ?ClinicUserModel $clinicUserModel = null,
        ?UserModel $userModel = null,
        ?RoleModel $roleModel = null,
        ?SettingsModel $settingsModel = null,
        ?IonAuthModel $ionAuthModel = null,
        ?ControlPlaneAuditService $auditService = null,
        ?PermissionService $permissionService = null,
        ?PlanGuard $planGuard = null
    ) {
        $this->db = $db ?? Database::connect();
        $this->clinicModel = $clinicModel ?? new ClinicModel($this->db);
        $this->clinicUserModel = $clinicUserModel ?? (new ClinicUserModel($this->db))->withoutTenantScope();
        $this->userModel = $userModel ?? new UserModel($this->db);
        $this->roleModel = $roleModel ?? new RoleModel($this->db);
        $this->settingsModel = $settingsModel ?? (new SettingsModel($this->db))->withoutTenantScope();
        $this->ionAuthModel = $ionAuthModel ?? new IonAuthModel();
        $this->auditService = $auditService ?? new ControlPlaneAuditService(new \App\Models\ControlPlaneAuditModel($this->db));
        $this->permissionService = $permissionService ?? new PermissionService($this->db);
        $this->planGuard = $planGuard ?? new PlanGuard($this->db);
        foreach ([$this->clinicModel, $this->clinicUserModel, $this->userModel, $this->roleModel,
            $this->settingsModel, $this->auditService->getAuditModel()] as $boundModel) {
            if ($boundModel->db !== $this->db) {
                throw new \InvalidArgumentException('Membership models and audit must share the transaction connection.');
            }
        }
    }

    /**
     * Get active database connection.
     */
    public function getDb(): BaseConnection
    {
        return $this->db;
    }

    /**
     * Concise owned transaction runner.
     * Rejects nesting before any writes; checks begin; checks every write/audit via callback;
     * checks transStatus and commit; rolls back on any failure without claiming success.
     *
     * @param callable $callback function(BaseConnection $db): array
     * @return array
     */
    protected function runInTransaction(callable $callback): array
    {
        $db = $this->db;
        if ($db->transDepth !== 0) {
            return [
                'success' => false,
                'code' => 'nested_transaction_rejected',
                'message' => 'Cannot execute membership mutation inside an existing transaction.',
            ];
        }

        try {
            if ($db->transBegin() === false) {
                return [
                    'success' => false,
                    'code' => 'trans_begin_failed',
                    'message' => 'Failed to initialize database transaction.',
                ];
            }
            $result = $callback($db);

            if (!is_array($result) || empty($result['success'])) {
                $db->transRollback();
                return is_array($result) ? $result : [
                    'success' => false,
                    'code' => 'operation_failed',
                    'message' => 'Operation failed.',
                ];
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return [
                    'success' => false,
                    'code' => 'trans_status_failed',
                    'message' => 'Transaction status indicated failure.',
                ];
            }

            $committed = $db->transCommit();
            if (!$committed) {
                $db->transRollback();
                return [
                    'success' => false,
                    'code' => 'trans_commit_failed',
                    'message' => 'Database transaction commit failed.',
                ];
            }

            return $result;
        } catch (\Throwable $e) {
            if ($db->transDepth > 0) {
                $db->transRollback();
            }
            log_message('error', 'ClinicMembershipService transaction failed: {message}', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'code' => 'transaction_error',
                'message' => 'A database error occurred during membership mutation.',
            ];
        }
    }

    /**
     * Authorize actor before acquiring locks.
     * Must verify:
     * - Actor exists and has active global account
     * - Matches authenticated session actor
     * - Either CP mode (global_mode=true, no impersonation, genuine super_admin)
     *   OR Tenant mode (active_clinic_id matches target clinic, active membership, exact permission)
     */
    public function authorizeActor(int $clinicId, int $actorUserId, string $action): array
    {
        $deny = static fn(string $code): array => ['authorized' => false, 'code' => $code, 'message' => 'Membership operation is not authorized.'];
        if ($clinicId <= 0 || $actorUserId <= 0
            || \App\Libraries\ApiRuntimeContext::getUserId() !== null
            || \App\Libraries\ApiRuntimeContext::getClinicId() !== null) {
            return $deny('unauthenticated');
        }
        $session = service('session');
        $auth = new \App\Libraries\IonAuth();
        if (!$auth->loggedIn() || (int) $auth->getUserId() !== $actorUserId) {
            return $deny('actor_mismatch');
        }
        $actor = $this->userModel->find($actorUserId);
        if (!$actor || (int) ($actor['active'] ?? 0) !== 1) {
            return $deny('actor_inactive');
        }
        $globalMode = $session->get('global_mode');
        if (!in_array($globalMode, [null, false, true], true)) {
            return $deny('invalid_mode');
        }
        $sessionClinicId = (int) ($session->get('active_clinic_id') ?? 0);
        $impersonatedClinicId = (int) ($session->get('impersonated_clinic_id') ?? 0);
        $isImpersonating = $session->get('is_impersonating') === true;
        if ($globalMode === true) {
            if ($sessionClinicId > 0 || $isImpersonating || $impersonatedClinicId > 0
                || !$this->permissionService->isSuperAdmin($actorUserId)) {
                return $deny('cp_authority_required');
            }
            return ['authorized' => true, 'mode' => 'control_plane'];
        }
        if ($sessionClinicId !== $clinicId) {
            return $deny('clinic_context_mismatch');
        }
        $clinic = $this->clinicModel->find($clinicId);
        if (!$clinic || ($clinic['status'] ?? '') !== 'active') {
            return $deny('clinic_inactive');
        }
        if ($isImpersonating || $impersonatedClinicId > 0) {
            if (!$isImpersonating || $impersonatedClinicId !== $clinicId
                || !$this->permissionService->isSuperAdmin($actorUserId)) {
                return $deny('impersonation_not_permitted');
            }
            return ['authorized' => true, 'mode' => 'web_impersonation'];
        }
        if (!$this->permissionService->getTenantRoleId($actorUserId, $clinicId)
            || !$this->permissionService->hasPermission($actorUserId, 'users', $action)) {
            return $deny('permission_denied');
        }
        return ['authorized' => true, 'mode' => 'tenant'];
    }

    /**
     * Revalidate actor authority inside transaction under lock.
     */
    protected function revalidateActorUnderLock(BaseConnection $db, int $clinicId, int $actorUserId, string $mode, string $action): bool
    {
        $actorUser = $db->query('SELECT id, active FROM users WHERE id = ? FOR UPDATE', [$actorUserId])->getRowArray();
        if (!$actorUser || (int) ($actorUser['active'] ?? 0) !== 1) {
            return false;
        }

        if (in_array($mode, ['control_plane', 'web_impersonation'], true)) {
            return $this->hasPlatformAssignmentUnderLock($db, $actorUserId);
        }

        // Tenant mode
        $actorMembership = $db->query(
            'SELECT cu.id, cu.role_id, cu.status, r.is_active, r.slug, r.clinic_id AS role_clinic_id
             FROM clinic_users cu
             JOIN roles r ON r.id = cu.role_id
             WHERE cu.clinic_id = ? AND cu.user_id = ?
             FOR UPDATE',
            [$clinicId, $actorUserId]
        )->getRowArray();

        if (!$actorMembership || ($actorMembership['status'] ?? '') !== 'active' || (int) ($actorMembership['is_active'] ?? 0) !== 1) {
            return false;
        }

        if (($actorMembership['slug'] ?? '') === 'super_admin'
            || ($actorMembership['role_clinic_id'] !== null && (int) $actorMembership['role_clinic_id'] !== $clinicId)) {
            return false;
        }

        return $this->permissionService->hasPermission($actorUserId, 'users', $action);
    }

    private function localizeResult(array $result): array
    {
        $keys = [
            'existing_account_restricted' => 'existingAccountRestricted',
            'cannot_mutate_platform_account' => 'cannotMutatePlatformAccount',
            'cannot_demote_designated_admin' => 'cannotDemoteDesignatedAdmin',
            'cannot_deactivate_designated_admin' => 'cannotDeactivateDesignatedAdmin',
            'cannot_demote_last_admin' => 'cannotDemoteLastAdmin',
            'cannot_deactivate_last_admin' => 'cannotDemoteLastAdmin',
            'cannot_deactivate_last_user' => 'cannotDeactivateLastUser',
            'global_user_inactive' => 'globalUserInactive',
            'plan_quota_exceeded' => 'membershipQuotaExceeded',
            'invalid_role' => 'membershipInvalidRole',
            'membership_inactive' => 'membershipInactiveHelp',
        ];
        $key = $keys[$result['code'] ?? ''] ?? (!empty($result['success']) ? 'membershipSaved' : 'membershipOperationDenied');
        $result['message'] = lang('UserRole.' . $key);
        return $result;
    }

    protected function assertUserSeatAvailable(int $clinicId, string $action): void
    {
        $decision = $this->planGuard->assertQuota($clinicId, 'users_max', 1, ['action' => $action]);
        if (is_array($decision) && ($decision['allowed'] ?? true) === false) {
            throw new \RuntimeException('Clinic user quota exceeded.');
        }
    }

    protected function hasPlatformAssignmentUnderLock(BaseConnection $db, int $userId): bool
    {
        return $db->query(
            "SELECT ur.id FROM user_roles ur JOIN roles r ON r.id = ur.role_id
             WHERE ur.user_id = ? AND ur.is_active = 1 AND r.slug = 'super_admin'
               AND r.clinic_id IS NULL AND r.is_active = 1
               AND (ur.expires_at IS NULL OR ur.expires_at > NOW()) FOR UPDATE",
            [$userId]
        )->getRowArray() !== null;
    }

    protected function mutableTargetUnderLock(BaseConnection $db, int $targetUserId): ?array
    {
        $user = $db->query('SELECT id, active FROM users WHERE id = ? FOR UPDATE', [$targetUserId])->getRowArray();
        if (!$user) {
            return ['success' => false, 'code' => 'user_not_found', 'message' => 'User not found.'];
        }
        if ((int) $user['active'] === 1 && $this->hasPlatformAssignmentUnderLock($db, $targetUserId)) {
            return ['success' => false, 'code' => 'cannot_mutate_platform_account', 'message' => 'Platform account membership is protected.'];
        }
        return null;
    }

    /**
     * Check if a role is a clinic management role.
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
     * Count eligible ACTIVE clinic managers with active global accounts.
     */
    public function countActiveManagers(int $clinicId, int $excludeUserId = 0, ?BaseConnection $db = null): int
    {
        $conn = $db ?? $this->db;
        $builder = $conn->table('clinic_users cu')
            ->join('roles r', 'r.id = cu.role_id')
            ->join('users u', 'u.id = cu.user_id')
            ->where('cu.clinic_id', $clinicId)
            ->where('cu.status', 'active')
            ->where('u.active', 1)
            ->where('r.is_active', 1)
            ->whereIn('r.slug', ['practice_manager', 'clinic_admin'])
            ->groupStart()->where('r.clinic_id', null)->orWhere('r.clinic_id', $clinicId)->groupEnd();

        if ($excludeUserId > 0) {
            $builder->where('cu.user_id !=', $excludeUserId);
        }

        return $builder->countAllResults();
    }

    /**
     * Count eligible ACTIVE users with active global accounts in clinic.
     */
    public function countActiveUsers(int $clinicId, ?BaseConnection $db = null): int
    {
        $conn = $db ?? $this->db;
        return $conn->table('clinic_users cu')
            ->join('users u', 'u.id = cu.user_id')
            ->join('roles r', 'r.id = cu.role_id')
            ->where('r.is_active', 1)->where('r.slug !=', 'super_admin')
            ->groupStart()->where('r.clinic_id', null)->orWhere('r.clinic_id', $clinicId)->groupEnd()
            ->where('cu.clinic_id', $clinicId)
            ->where('cu.status', 'active')
            ->where('u.active', 1)
            ->countAllResults();
    }

    /**
     * Atomically create a new identity + members group + clinic membership.
     * Refuses existing global identity without modifying any fields (SEC02).
     */
    public function createMemberWithIdentity(int $clinicId, array $userData, int $roleId, int $actorUserId): array
    {
        if ($this->db->transDepth !== 0) {
            return $this->localizeResult(['success' => false, 'code' => 'nested_transaction_rejected']);
        }
        return $this->localizeResult($this->performCreateMemberWithIdentity($clinicId, $userData, $roleId, $actorUserId));
    }

    protected function performCreateMemberWithIdentity(int $clinicId, array $userData, int $roleId, int $actorUserId): array
    {
        $email = strtolower(trim((string) ($userData['email'] ?? '')));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'code' => 'invalid_email',
                'message' => 'A valid email address is required.',
            ];
        }

        $password = (string) ($userData['password'] ?? '');
        if (strlen($password) < 8) {
            return [
                'success' => false,
                'code' => 'invalid_password',
                'message' => 'Password must be at least 8 characters long.',
            ];
        }

        $firstName = trim((string) ($userData['first_name'] ?? ''));
        $lastName = trim((string) ($userData['last_name'] ?? ''));
        if ($firstName === '' || $lastName === '') {
            return [
                'success' => false,
                'code' => 'invalid_name',
                'message' => 'First and last name are required.',
            ];
        }

        // 1. Authorize actor
        $auth = $this->authorizeActor($clinicId, $actorUserId, 'create');
        if (!$auth['authorized']) {
            return [
                'success' => false,
                'code' => $auth['code'] ?? 'unauthorized',
                'message' => $auth['message'] ?? 'Unauthorized.',
            ];
        }

        // 2. Validate Clinic
        $clinic = $this->clinicModel->find($clinicId);
        if (!$clinic || ($clinic['status'] ?? '') !== 'active') {
            return [
                'success' => false,
                'code' => 'invalid_clinic',
                'message' => 'Clinic not found or inactive.',
            ];
        }

        // 3. Preliminary Role Check
        $role = $this->roleModel->findAssignableRoleById($roleId, $clinicId, true);
        if (!$role || ($role['slug'] ?? '') === 'super_admin') {
            return [
                'success' => false,
                'code' => 'invalid_role',
                'message' => 'Invalid role selected for this clinic.',
            ];
        }

        // 4. Pre-transaction check: refuse existing global identity
        $existing = $this->userModel->where('email', $email)->first();
        if ($existing) {
            return [
                'success' => false,
                'code' => 'existing_account_restricted',
                'message' => lang('UserRole.existingAccountRestricted'),
            ];
        }

        // 5. Pre-transaction Quota Check
        try {
            $this->assertUserSeatAvailable($clinicId, 'user_create');
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'code' => 'plan_quota_exceeded',
                'message' => 'Clinic user quota exceeded.',
            ];
        }

        return $this->runInTransaction(function (BaseConnection $db) use ($clinicId, $userData, $roleId, $actorUserId, $auth, $email, $password, $firstName, $lastName) {
            // Lock clinic row first
            $lockedClinic = $db->query('SELECT id, status, name FROM clinics WHERE id = ? FOR UPDATE', [$clinicId])->getRowArray();
            if (!$lockedClinic || ($lockedClinic['status'] ?? '') !== 'active') {
                return [
                    'success' => false,
                    'code' => 'clinic_inactive',
                    'message' => 'Clinic is not active.',
                ];
            }

            // Revalidate actor under lock
            if (!$this->revalidateActorUnderLock($db, $clinicId, $actorUserId, $auth['mode'], 'create')) {
                return [
                    'success' => false,
                    'code' => 'actor_authority_revoked',
                    'message' => 'Actor authority could not be verified under lock.',
                ];
            }

            // Revalidate role under lock
            $lockedRole = $db->query('SELECT id, slug, is_active, clinic_id FROM roles WHERE id = ? FOR UPDATE', [$roleId])->getRowArray();
            if (!$lockedRole || (int) ($lockedRole['is_active'] ?? 0) !== 1 || ($lockedRole['slug'] ?? '') === 'super_admin') {
                return [
                    'success' => false,
                    'code' => 'invalid_role',
                    'message' => 'Role is not assignable or inactive.',
                ];
            }
            if ($lockedRole['clinic_id'] !== null && (int) $lockedRole['clinic_id'] !== $clinicId) {
                return [
                    'success' => false,
                    'code' => 'invalid_role_clinic',
                    'message' => 'Role belongs to another clinic.',
                ];
            }

            // Revalidate quota under clinic serialization
            try {
                $this->assertUserSeatAvailable($clinicId, 'user_create');
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'code' => 'plan_quota_exceeded',
                    'message' => 'Clinic user quota exceeded.',
                ];
            }

            // Revalidate email under lock to eliminate race conditions
            $lockedExisting = $db->query('SELECT id FROM users WHERE email = ? FOR UPDATE', [$email])->getRowArray();
            if ($lockedExisting) {
                return [
                    'success' => false,
                    'code' => 'existing_account_restricted',
                    'message' => lang('UserRole.existingAccountRestricted'),
                ];
            }

            // Hash password compatible with IonAuth
            $passwordHash = $this->ionAuthModel->hashPassword($password, $email);
            if ($passwordHash === false) {
                return [
                    'success' => false,
                    'code' => 'password_hashing_failed',
                    'message' => 'Failed to securely hash password.',
                ];
            }

            $userPayload = [
                'ip_address' => '127.0.0.1',
                'username' => $email,
                'password' => $passwordHash,
                'email' => $email,
                'created_on' => time(),
                'active' => 1,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'company' => $lockedClinic['name'] ?? null,
                'phone' => $userData['phone'] ?? null,
                'department' => $userData['department'] ?? null,
                'hire_date' => $userData['hire_date'] ?? null,
            ];

            $userInserted = $db->table('users')->insert($userPayload);
            if (!$userInserted) {
                return [
                    'success' => false,
                    'code' => 'user_creation_failed',
                    'message' => 'Failed to create user account.',
                ];
            }
            $newUserId = (int) $db->insertID();

            // Insert into default 'members' group
            $membersGroup = $db->table('groups')->where('name', 'members')->get()->getRowArray();
            if (!$membersGroup) {
                return [
                    'success' => false,
                    'code' => 'missing_default_group',
                    'message' => 'Default authentication group (members) is missing.',
                ];
            }

            $groupInserted = $db->table('users_groups')->insert([
                'user_id' => $newUserId,
                'group_id' => (int) $membersGroup['id'],
            ]);
            if (!$groupInserted) {
                return [
                    'success' => false,
                    'code' => 'group_assignment_failed',
                    'message' => 'Failed to assign default authentication group.',
                ];
            }

            // Insert membership into clinic_users
            $membershipInserted = $db->table('clinic_users')->insert([
                'clinic_id' => $clinicId,
                'user_id' => $newUserId,
                'role_id' => $roleId,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            if (!$membershipInserted) {
                return [
                    'success' => false,
                    'code' => 'membership_creation_failed',
                    'message' => 'Failed to create clinic membership.',
                ];
            }

            // Checked audit log
            $auditSuccess = $this->auditService->logEvent('clinic_member_create', [
                'actor_user_id' => $actorUserId,
                'route' => '/users/store',
                'method' => 'POST',
                'metadata' => [
                    'target_user_id' => $newUserId,
                    'clinic_id' => $clinicId,
                    'role_id' => $roleId,
                    'role_slug' => $lockedRole['slug'] ?? null,
                ],
            ], true);

            if (!$auditSuccess) {
                return [
                    'success' => false,
                    'code' => 'audit_failed',
                    'message' => 'Failed to record audit log.',
                ];
            }

            return [
                'success' => true,
                'user_id' => $newUserId,
                'message' => 'User created successfully.',
            ];
        });
    }

    /**
     * Replace a member's role in clinic_users.
     * Protects last active clinic manager and designated administrator (SEC03).
     * Never mutates global user_roles.
     */
    public function replaceRole(int $clinicId, int $targetUserId, int $newRoleId, int $actorUserId): array
    {
        if ($this->db->transDepth !== 0) {
            return $this->localizeResult(['success' => false, 'code' => 'nested_transaction_rejected']);
        }
        return $this->localizeResult($this->performReplaceRole($clinicId, $targetUserId, $newRoleId, $actorUserId));
    }

    protected function performReplaceRole(int $clinicId, int $targetUserId, int $newRoleId, int $actorUserId): array
    {
        // 1. Authorize actor
        $auth = $this->authorizeActor($clinicId, $actorUserId, 'edit');
        if (!$auth['authorized']) {
            return [
                'success' => false,
                'code' => $auth['code'] ?? 'unauthorized',
                'message' => $auth['message'] ?? 'Unauthorized.',
            ];
        }

        // 2. Protect genuine platform accounts
        if ($this->permissionService->isSuperAdmin($targetUserId)) {
            return [
                'success' => false,
                'code' => 'cannot_mutate_platform_account',
                'message' => 'Cannot modify membership of a platform super administrator account.',
            ];
        }

        // 3. Preliminary Role Check
        $role = $this->roleModel->findAssignableRoleById($newRoleId, $clinicId, true);
        if (!$role || ($role['slug'] ?? '') === 'super_admin') {
            return [
                'success' => false,
                'code' => 'invalid_role',
                'message' => 'Invalid role selected for this clinic.',
            ];
        }

        return $this->runInTransaction(function (BaseConnection $db) use ($clinicId, $targetUserId, $newRoleId, $actorUserId, $auth, $role) {
            // Lock clinic row first
            $lockedClinic = $db->query('SELECT id, status FROM clinics WHERE id = ? FOR UPDATE', [$clinicId])->getRowArray();
            if (!$lockedClinic || ($lockedClinic['status'] ?? '') !== 'active') {
                return [
                    'success' => false,
                    'code' => 'clinic_inactive',
                    'message' => 'Clinic is not active.',
                ];
            }

            // Revalidate actor under lock
            if (!$this->revalidateActorUnderLock($db, $clinicId, $actorUserId, $auth['mode'], 'edit')) {
                return [
                    'success' => false,
                    'code' => 'actor_authority_revoked',
                    'message' => 'Actor authority could not be verified under lock.',
                ];
            }

            if ($failure = $this->mutableTargetUnderLock($db, $targetUserId)) {
                return $failure;
            }

            // Revalidate new role under lock
            $lockedRole = $db->query('SELECT id, slug, is_active, clinic_id FROM roles WHERE id = ? FOR UPDATE', [$newRoleId])->getRowArray();
            if (!$lockedRole || (int) ($lockedRole['is_active'] ?? 0) !== 1 || ($lockedRole['slug'] ?? '') === 'super_admin'
                || ($lockedRole['clinic_id'] !== null && (int) $lockedRole['clinic_id'] !== $clinicId)) {
                return [
                    'success' => false,
                    'code' => 'invalid_role',
                    'message' => 'Role is not assignable or inactive.',
                ];
            }

            // Lock membership row
            $membership = $db->query('SELECT * FROM clinic_users WHERE clinic_id = ? AND user_id = ? FOR UPDATE', [$clinicId, $targetUserId])->getRowArray();
            if (!$membership) {
                return [
                    'success' => false,
                    'code' => 'membership_not_found',
                    'message' => 'User membership not found in this clinic.',
                ];
            }

            if (($membership['status'] ?? '') !== 'active') {
                return [
                    'success' => false,
                    'code' => 'membership_inactive',
                    'message' => 'Cannot modify role on an inactive membership.',
                ];
            }

            $currentRoleId = (int) ($membership['role_id'] ?? 0);
            $isCurrentManager = $this->isManagerRole($currentRoleId);
            $isNewManager = $this->isManagerRole($newRoleId);

            // Demoting from manager to non-manager
            if ($isCurrentManager && !$isNewManager) {
                // Correct column: setting_value
                $setting = $db->query(
                    'SELECT setting_value FROM settings WHERE clinic_id = ? AND setting_key = ? FOR UPDATE',
                    [$clinicId, ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN]
                )->getRowArray();

                if ($setting && (int) ($setting['setting_value'] ?? 0) === $targetUserId) {
                    return [
                        'success' => false,
                        'code' => 'cannot_demote_designated_admin',
                        'message' => lang('UserRole.cannotDemoteLastAdmin'),
                    ];
                }

                $otherManagers = $this->countActiveManagers($clinicId, $targetUserId, $db);
                if ($otherManagers <= 0) {
                    return [
                        'success' => false,
                        'code' => 'cannot_demote_last_admin',
                        'message' => lang('UserRole.cannotDemoteLastAdmin'),
                    ];
                }
            }

            $updated = $db->table('clinic_users')
                ->where('id', $membership['id'])
                ->update([
                    'role_id' => $newRoleId,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            if (!$updated) {
                return [
                    'success' => false,
                    'code' => 'update_failed',
                    'message' => 'Failed to update membership role.',
                ];
            }

            $auditSuccess = $this->auditService->logEvent('clinic_member_role_update', [
                'actor_user_id' => $actorUserId,
                'route' => '/users/' . $targetUserId . '/role',
                'method' => 'POST',
                'metadata' => [
                    'target_user_id' => $targetUserId,
                    'clinic_id' => $clinicId,
                    'previous_role_id' => $currentRoleId,
                    'new_role_id' => $newRoleId,
                    'new_role_slug' => $lockedRole['slug'] ?? null,
                ],
            ], true);

            if (!$auditSuccess) {
                return [
                    'success' => false,
                    'code' => 'audit_failed',
                    'message' => 'Audit logging failed.',
                ];
            }

            return [
                'success' => true,
                'role_id' => $newRoleId,
                'message' => 'Role updated successfully.',
            ];
        });
    }

    /**
     * Update membership status ('active' or 'inactive').
     * Protects last active clinic manager, designated administrator, and last active user.
     */
    public function updateStatus(int $clinicId, int $targetUserId, string $newStatus, int $actorUserId): array
    {
        if ($this->db->transDepth !== 0) {
            return $this->localizeResult(['success' => false, 'code' => 'nested_transaction_rejected']);
        }
        return $this->localizeResult($this->performUpdateStatus($clinicId, $targetUserId, $newStatus, $actorUserId));
    }

    protected function performUpdateStatus(int $clinicId, int $targetUserId, string $newStatus, int $actorUserId): array
    {
        return $this->mutateMembershipStatus($clinicId, $targetUserId, $newStatus, $actorUserId, 'edit');
    }

    protected function mutateMembershipStatus(int $clinicId, int $targetUserId, string $newStatus, int $actorUserId, string $requiredAction): array
    {
        if (!in_array($newStatus, ['active', 'inactive'], true)) {
            return [
                'success' => false,
                'code' => 'invalid_status',
                'message' => 'Invalid status specified.',
            ];
        }

        // 1. Authorize actor
        $auth = $this->authorizeActor($clinicId, $actorUserId, $requiredAction);
        if (!$auth['authorized']) {
            return [
                'success' => false,
                'code' => $auth['code'] ?? 'unauthorized',
                'message' => $auth['message'] ?? 'Unauthorized.',
            ];
        }

        // 2. Protect platform account
        if ($this->permissionService->isSuperAdmin($targetUserId)) {
            return [
                'success' => false,
                'code' => 'cannot_mutate_platform_account',
                'message' => 'Cannot modify membership of a platform super administrator account.',
            ];
        }

        return $this->runInTransaction(function (BaseConnection $db) use ($clinicId, $targetUserId, $newStatus, $actorUserId, $auth, $requiredAction) {
            // Lock clinic row first
            $lockedClinic = $db->query('SELECT id, status FROM clinics WHERE id = ? FOR UPDATE', [$clinicId])->getRowArray();
            if (!$lockedClinic || ($lockedClinic['status'] ?? '') !== 'active') {
                return [
                    'success' => false,
                    'code' => 'clinic_inactive',
                    'message' => 'Clinic is not active.',
                ];
            }

            // Revalidate actor under lock
            if (!$this->revalidateActorUnderLock($db, $clinicId, $actorUserId, $auth['mode'], $requiredAction)) {
                return [
                    'success' => false,
                    'code' => 'actor_authority_revoked',
                    'message' => 'Actor authority could not be verified under lock.',
                ];
            }

            if ($failure = $this->mutableTargetUnderLock($db, $targetUserId)) {
                return $failure;
            }

            // Lock membership row
            $membership = $db->query('SELECT * FROM clinic_users WHERE clinic_id = ? AND user_id = ? FOR UPDATE', [$clinicId, $targetUserId])->getRowArray();
            if (!$membership) {
                return [
                    'success' => false,
                    'code' => 'membership_not_found',
                    'message' => 'User membership not found in this clinic.',
                ];
            }

            if (($membership['status'] ?? '') === $newStatus) {
                return ['success' => true, 'code' => 'membership_unchanged', 'status' => $newStatus, 'message' => 'Clinic access is unchanged.'];
            }

            if ($newStatus === 'inactive') {
                // Correct column: setting_value
                $setting = $db->query(
                    'SELECT setting_value FROM settings WHERE clinic_id = ? AND setting_key = ? FOR UPDATE',
                    [$clinicId, ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN]
                )->getRowArray();

                if ($setting && (int) ($setting['setting_value'] ?? 0) === $targetUserId) {
                    return [
                        'success' => false,
                        'code' => 'cannot_deactivate_designated_admin',
                        'message' => lang('UserRole.cannotDemoteLastAdmin'),
                    ];
                }

                $currentRoleId = (int) ($membership['role_id'] ?? 0);
                if ($this->isManagerRole($currentRoleId)) {
                    $otherManagers = $this->countActiveManagers($clinicId, $targetUserId, $db);
                    if ($otherManagers <= 0) {
                        return [
                            'success' => false,
                            'code' => 'cannot_deactivate_last_admin',
                            'message' => lang('UserRole.cannotDemoteLastAdmin'),
                        ];
                    }
                }

                $activeUsersCount = $this->countActiveUsers($clinicId, $db);
                if ($activeUsersCount <= 1) {
                    return [
                        'success' => false,
                        'code' => 'cannot_deactivate_last_user',
                        'message' => lang('UserRole.cannotDeactivateLastUser'),
                    ];
                }
            } else {
                // Activating: ensure global account is active
                $targetUser = $db->query('SELECT id, active FROM users WHERE id = ? FOR UPDATE', [$targetUserId])->getRowArray();
                if (!$targetUser || (int) ($targetUser['active'] ?? 0) !== 1) {
                    return [
                        'success' => false,
                        'code' => 'global_user_inactive',
                        'message' => 'Cannot activate membership for an inactive user account.',
                    ];
                }

                $targetRole = $db->query('SELECT id, slug, clinic_id, is_active FROM roles WHERE id = ? FOR UPDATE', [(int) $membership['role_id']])->getRowArray();
                if (!$targetRole || (int) $targetRole['is_active'] !== 1 || $targetRole['slug'] === 'super_admin'
                    || ($targetRole['clinic_id'] !== null && (int) $targetRole['clinic_id'] !== $clinicId)) {
                    return ['success' => false, 'code' => 'invalid_role', 'message' => 'Membership role must be active and valid for this clinic.'];
                }

                // Check quota
                try {
                    $this->assertUserSeatAvailable($clinicId, 'user_activate');
                } catch (\Throwable $e) {
                    return [
                        'success' => false,
                        'code' => 'plan_quota_exceeded',
                        'message' => 'Plan user limit reached.',
                    ];
                }
            }

            $updated = $db->table('clinic_users')
                ->where('id', $membership['id'])
                ->update([
                    'status' => $newStatus,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            if (!$updated) {
                return [
                    'success' => false,
                    'code' => 'update_failed',
                    'message' => 'Failed to update membership status.',
                ];
            }

            $auditSuccess = $this->auditService->logEvent('clinic_member_status_update', [
                'actor_user_id' => $actorUserId,
                'route' => '/users/' . $targetUserId . '/status',
                'method' => 'POST',
                'metadata' => [
                    'target_user_id' => $targetUserId,
                    'clinic_id' => $clinicId,
                    'new_status' => $newStatus,
                ],
            ], true);

            if (!$auditSuccess) {
                return [
                    'success' => false,
                    'code' => 'audit_failed',
                    'message' => 'Audit logging failed.',
                ];
            }

            return [
                'success' => true,
                'status' => $newStatus,
                'message' => 'Status updated successfully.',
            ];
        });
    }

    /**
     * Remove membership from clinic (sets status = 'inactive').
     * Fail closed: immediate revocation of tenant authority.
     */
    public function removeMembership(int $clinicId, int $targetUserId, int $actorUserId): array
    {
        if ($this->db->transDepth !== 0) {
            return $this->localizeResult(['success' => false, 'code' => 'nested_transaction_rejected']);
        }
        return $this->localizeResult($this->performRemoveMembership($clinicId, $targetUserId, $actorUserId));
    }

    protected function performRemoveMembership(int $clinicId, int $targetUserId, int $actorUserId): array
    {
        return $this->mutateMembershipStatus($clinicId, $targetUserId, 'inactive', $actorUserId, 'delete');
    }

    /**
     * Canonical checked CP method to link/add an existing user to a clinic.
     * Restricted to Control Plane mode (global_mode=true, platform superadmin).
     */
    public function addExistingUserMembership(int $clinicId, int $targetUserId, int $roleId, int $actorUserId): array
    {
        if ($this->db->transDepth !== 0) {
            return $this->localizeResult(['success' => false, 'code' => 'nested_transaction_rejected']);
        }
        return $this->localizeResult($this->performAddExistingUserMembership($clinicId, $targetUserId, $roleId, $actorUserId));
    }

    protected function performAddExistingUserMembership(int $clinicId, int $targetUserId, int $roleId, int $actorUserId): array
    {
        // 1. Authorize actor: must be platform superadmin in CP mode
        $auth = $this->authorizeActor($clinicId, $actorUserId, 'create');
        if (!$auth['authorized'] || ($auth['mode'] ?? '') !== 'control_plane') {
            return [
                'success' => false,
                'code' => 'cp_authority_required',
                'message' => 'Direct user linking to clinics is restricted to platform administrators.',
            ];
        }

        // 2. Validate clinic and role
        $clinic = $this->clinicModel->find($clinicId);
        if (!$clinic || ($clinic['status'] ?? '') !== 'active') {
            return [
                'success' => false,
                'code' => 'invalid_clinic',
                'message' => 'Invalid clinic selected.',
            ];
        }

        $role = $this->roleModel->findAssignableRoleById($roleId, $clinicId, true);
        if (!$role || ($role['slug'] ?? '') === 'super_admin') {
            return [
                'success' => false,
                'code' => 'invalid_role',
                'message' => 'Invalid role selected for this clinic.',
            ];
        }

        return $this->runInTransaction(function (BaseConnection $db) use ($clinicId, $targetUserId, $roleId, $actorUserId, $auth, $role) {
            // Lock clinic row
            $lockedClinic = $db->query('SELECT id, status FROM clinics WHERE id = ? FOR UPDATE', [$clinicId])->getRowArray();
            if (!$lockedClinic || ($lockedClinic['status'] ?? '') !== 'active') {
                return [
                    'success' => false,
                    'code' => 'clinic_inactive',
                    'message' => 'Clinic is not active.',
                ];
            }

            // Revalidate actor under lock
            if (!$this->revalidateActorUnderLock($db, $clinicId, $actorUserId, $auth['mode'], 'create')) {
                return [
                    'success' => false,
                    'code' => 'actor_authority_revoked',
                    'message' => 'Platform administrator authority revoked.',
                ];
            }

            if ($failure = $this->mutableTargetUnderLock($db, $targetUserId)) {
                return $failure;
            }

            // Lock target user
            $targetUser = $db->query('SELECT id, active FROM users WHERE id = ? FOR UPDATE', [$targetUserId])->getRowArray();
            if (!$targetUser || (int) ($targetUser['active'] ?? 0) !== 1) {
                return [
                    'success' => false,
                    'code' => 'target_user_inactive',
                    'message' => 'Target user does not exist or is inactive.',
                ];
            }

            // Lock role
            $lockedRole = $db->query('SELECT id, slug, is_active, clinic_id FROM roles WHERE id = ? FOR UPDATE', [$roleId])->getRowArray();
            if (!$lockedRole || (int) ($lockedRole['is_active'] ?? 0) !== 1 || ($lockedRole['slug'] ?? '') === 'super_admin') {
                return [
                    'success' => false,
                    'code' => 'invalid_role',
                    'message' => 'Role is not assignable or inactive.',
                ];
            }

            // Check quota under lock using canonical users_max
            try {
                $this->assertUserSeatAvailable($clinicId, 'user_add_membership');
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'code' => 'plan_quota_exceeded',
                    'message' => 'Clinic user quota reached.',
                ];
            }

            // Check existing membership under lock
            $existing = $db->query('SELECT id, status FROM clinic_users WHERE clinic_id = ? AND user_id = ? FOR UPDATE', [$clinicId, $targetUserId])->getRowArray();
            if ($existing && ($existing['status'] ?? '') === 'active') {
                return [
                    'success' => false,
                    'code' => 'already_active_member',
                    'message' => 'User is already an active member of this clinic.',
                ];
            }

            if ($existing) {
                $saved = $db->table('clinic_users')->where('id', $existing['id'])->update([
                    'role_id' => $roleId,
                    'status' => 'active',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $action = 'membership_reactivate';
            } else {
                $saved = $db->table('clinic_users')->insert([
                    'clinic_id' => $clinicId,
                    'user_id' => $targetUserId,
                    'role_id' => $roleId,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $action = 'membership_add';
            }

            if (!$saved) {
                return [
                    'success' => false,
                    'code' => 'save_failed',
                    'message' => 'Failed to save membership.',
                ];
            }

            $auditSuccess = $this->auditService->logEvent($action, [
                'actor_user_id' => $actorUserId,
                'route' => '/controlplane/users/' . $targetUserId . '/memberships/add',
                'method' => 'POST',
                'metadata' => [
                    'target_user_id' => $targetUserId,
                    'clinic_id' => $clinicId,
                    'role_id' => $roleId,
                    'role_slug' => $lockedRole['slug'] ?? null,
                ],
            ], true);

            if (!$auditSuccess) {
                return [
                    'success' => false,
                    'code' => 'audit_failed',
                    'message' => 'Audit logging failed for membership addition.',
                ];
            }

            return [
                'success' => true,
                'message' => 'Membership saved successfully.',
            ];
        });
    }
}
