<?php

namespace App\Services;

use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\UserModel;
use App\Models\ClinicUserModel;

use CodeIgniter\Database\BaseConnection;

class PermissionService
{
    protected ?BaseConnection $db = null;
    protected $permissionModel;
    protected $roleModel;
    protected $userRoleModel;
    protected $userModel;
    protected $clinicUserModel;
    protected array $actionAliases = [
        'view' => ['read'],
        'read' => ['view'],
        'update' => ['edit'],
        'edit' => ['update'],
    ];

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->permissionModel = new PermissionModel($this->db);
        $this->roleModel = new RoleModel($this->db);
        $this->userRoleModel = new UserRoleModel($this->db);
        $this->userModel = new UserModel($this->db);
        $this->clinicUserModel = new ClinicUserModel($this->db);
    }

    /**
     * Resolve active clinic ID respecting context:
     * - Bearer API: exclusively from ApiRuntimeContext
     * - Web: from session active_clinic_id when not in global_mode
     */
    public function resolveCurrentClinicId(): ?int
    {
        if (\App\Libraries\ApiRuntimeContext::getUserId() !== null || \App\Libraries\ApiRuntimeContext::getClinicId() !== null) {
            return \App\Libraries\ApiRuntimeContext::getClinicId();
        }

        $session = service('session');
        if (!$session || $session->get('global_mode') === true) {
            return null;
        }

        $clinicId = (int) ($session->get('active_clinic_id') ?? 0);
        return $clinicId > 0 ? $clinicId : null;
    }

    /**
     * Get validated tenant role ID for a user in a clinic.
     * Enforces active global user, active clinic, active membership,
     * and role clinic_id NULL or matching clinic. Excludes super_admin spoofing.
     */
    public function getTenantRoleId(int $userId, int $clinicId): ?int
    {
        if ($userId <= 0 || $clinicId <= 0) {
            return null;
        }

        $user = $this->userModel->find($userId);
        if (!$user || (int) ($user['active'] ?? 0) !== 1) {
            return null;
        }

        $clinicModel = new \App\Models\ClinicModel($this->db);
        $clinic = $clinicModel->find($clinicId);
        if (!$clinic || ($clinic['status'] ?? '') !== 'active') {
            return null;
        }

        $membership = (new \App\Models\ClinicUserModel($this->db))->withoutTenantScope()
            ->where('user_id', $userId)
            ->where('clinic_id', $clinicId)
            ->where('status', 'active')
            ->first();

        if (!$membership || empty($membership['role_id'])) {
            return null;
        }

        $roleId = (int) $membership['role_id'];
        $role = $this->roleModel->find($roleId);
        if (!$role || (int) ($role['is_active'] ?? 0) !== 1) {
            return null;
        }

        // Exclude super_admin membership spoofing
        if (($role['slug'] ?? '') === 'super_admin') {
            return null;
        }

        $roleClinicId = $role['clinic_id'] !== null ? (int) $role['clinic_id'] : null;
        if ($roleClinicId !== null && $roleClinicId !== $clinicId) {
            return null;
        }

        return $roleId;
    }

    private function isActiveClinic(int $clinicId): bool
    {
        return $clinicId > 0 && $this->db->table('clinics')->where('id', $clinicId)->where('status', 'active')->countAllResults() === 1;
    }

    public function getSuperAdminRoleId(): ?int
    {
        $role = $this->roleModel
            ->where('slug', 'super_admin')
            ->where('clinic_id IS NULL', null, false)
            ->where('is_active', 1)
            ->first();

        return $role ? (int) $role['id'] : null;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($userId, $module, $action)
    {
        $isApi = (\App\Libraries\ApiRuntimeContext::getUserId() !== null || \App\Libraries\ApiRuntimeContext::getClinicId() !== null);

        // Platform Super Admin in Global Mode or validated WEB impersonation
        if (!$isApi) {
            $session = service('session');
            $isGlobal = $session && $session->get('global_mode') === true;
            $clinicId = $session ? (int) ($session->get('active_clinic_id') ?? 0) : 0;
            $isImpersonating = $session && $session->get('is_impersonating') === true;
            $impersonatedClinicId = $session ? (int) ($session->get('impersonated_clinic_id') ?? 0) : 0;

            if ($isGlobal && $this->isSuperAdmin($userId)) {
                return true;
            }

            if ($clinicId > 0 && $isImpersonating && $impersonatedClinicId === $clinicId && !$isGlobal && $this->isActiveClinic($clinicId) && $this->isSuperAdmin($userId)) {
                return true;
            }
        }

        foreach ($this->getActionCandidates($action) as $candidate) {
            if ($this->hasPermissionForAction($userId, $module, $candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check user-specific permission override.
     * NOTE: Retained for documentation. Legacy user_permissions table lacks clinic_id
     * and is denied as tenant authority.
     */
    protected function hasUserPermission($userId, $module, $action)
    {
        // Legacy user_permissions has no clinic_id: deny use as tenant authority
        return false;
    }

    protected function hasPermissionForAction($userId, $module, $action)
    {
        foreach ($this->getEffectiveRoleIds($userId) as $roleId) {
            if ($this->roleModel->hasPermission($roleId, $module, $action)) {
                return true;
            }
        }

        return false;
    }

    protected function getActionCandidates($action)
    {
        $candidates = [$action];
        if (isset($this->actionAliases[$action])) {
            $candidates = array_merge($candidates, $this->actionAliases[$action]);
        }

        return array_unique($candidates);
    }

    protected function getActiveClinicRoleId($userId)
    {
        $clinicId = $this->resolveCurrentClinicId();
        if (!$clinicId) {
            return null;
        }

        return $this->getTenantRoleId((int) $userId, $clinicId);
    }

    public function getEffectiveRoleIds($userId)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return [];
        }

        $isApi = (\App\Libraries\ApiRuntimeContext::getUserId() !== null || \App\Libraries\ApiRuntimeContext::getClinicId() !== null);

        if ($isApi) {
            $clinicId = \App\Libraries\ApiRuntimeContext::getClinicId();
            if (!$clinicId) {
                return [];
            }
            $tenantRoleId = $this->getTenantRoleId($userId, $clinicId);
            return $tenantRoleId ? [$tenantRoleId] : [];
        }

        $session = service('session');
        $isGlobalMode = $session && $session->get('global_mode') === true;

        if ($isGlobalMode) {
            if ($this->isSuperAdmin($userId)) {
                $superAdminRoleId = $this->getSuperAdminRoleId();
                return $superAdminRoleId ? [$superAdminRoleId] : [];
            }
            return [];
        }

        $clinicId = $session ? (int) ($session->get('active_clinic_id') ?? 0) : 0;
        if ($clinicId <= 0) {
            return [];
        }

        // Explicit validated WEB impersonation
        $isImpersonating = $session->get('is_impersonating') === true;
        $impersonatedClinicId = (int) ($session->get('impersonated_clinic_id') ?? 0);
        if ($isImpersonating && $impersonatedClinicId === $clinicId && !$isGlobalMode && $this->isActiveClinic($clinicId) && $this->isSuperAdmin($userId)) {
            $superAdminRoleId = $this->getSuperAdminRoleId();
            return $superAdminRoleId ? [$superAdminRoleId] : [];
        }

        // Sole tenant authority: clinic_users.role_id
        $tenantRoleId = $this->getTenantRoleId($userId, $clinicId);
        return $tenantRoleId ? [$tenantRoleId] : [];
    }

    /**
     * Get user permissions
     */
    public function getUserPermissions($userId)
    {
        $permissions = [];
        
        // Get role permissions (strictly from effective tenant role or global platform role)
        foreach ($this->getEffectiveRoleIds($userId) as $roleId) {
            $rolePermissions = $this->roleModel->getPermissionsGrouped($roleId);
            foreach ($rolePermissions as $module => $modulePermissions) {
                if (!isset($permissions[$module])) {
                    $permissions[$module] = [];
                }
                foreach ($modulePermissions as $permission) {
                    $permissions[$module][$permission['action']] = $permission;
                }
            }
        }

        return $permissions;
    }

    /**
     * Get user-specific permission overrides.
     * NOTE: Retained for documentation. Legacy user_permissions table lacks clinic_id
     * and is denied as tenant authority.
     */
    protected function getUserSpecificPermissions($userId)
    {
        return [];
    }

    /**
     * Get user roles with permissions for effective authority.
     */
    public function getUserRolesWithPermissions($userId)
    {
        $roles = [];
        foreach ($this->getEffectiveRoleIds($userId) as $roleId) {
            $role = $this->roleModel->find($roleId);
            if ($role) {
                $role['permissions'] = $this->roleModel->getPermissionsGrouped($roleId);
                $roles[] = $role;
            }
        }

        return $roles;
    }

    /**
     * Check if user has role.
     * In tenant context: clinic_users.role_id is sole authority.
     * For super_admin: requires genuine global NULL-clinic assignment.
     */
    public function hasRole($userId, $roleSlug)
    {
        if ($roleSlug === 'super_admin') {
            return $this->isSuperAdmin($userId);
        }

        foreach ($this->getEffectiveRoleIds($userId) as $roleId) {
            $role = $this->roleModel->find($roleId);
            if ($role && ($role['slug'] ?? '') === $roleSlug) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole($userId, $roleSlugs)
    {
        if (empty($roleSlugs)) {
            return false;
        }

        if (in_array('super_admin', $roleSlugs, true) && $this->isSuperAdmin($userId)) {
            return true;
        }

        foreach ($this->getEffectiveRoleIds($userId) as $roleId) {
            $role = $this->roleModel->find($roleId);
            if ($role && in_array($role['slug'] ?? '', $roleSlugs, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is admin (delegates to genuine super_admin check).
     */
    public function isAdmin($userId)
    {
        return $this->isSuperAdmin($userId);
    }

    /**
     * Check if user can manage users
     */
    public function canManageUsers($userId)
    {
        return $this->hasPermission($userId, 'users', 'create') || 
               $this->hasPermission($userId, 'users', 'edit') ||
               $this->hasPermission($userId, 'users', 'delete');
    }

    /**
     * Check if user can manage roles
     */
    public function canManageRoles($userId)
    {
        return $this->hasPermission($userId, 'users', 'roles');
    }

    /**
     * Check if user is genuine platform Super Admin.
     * Requires:
     * 1. Active global user (users.active = 1)
     * 2. Active, unexpired assignment in user_roles
     * 3. Role must be slug='super_admin', clinic_id IS NULL, status='active'
     * No recursion, no IonAuth group fallback, exclude spoofed clinic roles.
     */
    public function isSuperAdmin($userId): bool
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return false;
        }

        $user = $this->userModel->find($userId);
        if (!$user || (int) ($user['active'] ?? 0) !== 1) {
            return false;
        }

        $db = $this->db ?? \Config\Database::connect();
        $builder = $db->table('user_roles ur')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->where('ur.is_active', 1)
            ->where('r.slug', 'super_admin')
            ->where('r.clinic_id IS NULL', null, false)
            ->where('r.is_active', 1)
            ->where('(ur.expires_at IS NULL OR ur.expires_at > NOW())');

        return $builder->countAllResults() > 0;
    }

    /**
     * Get accessible modules for user
     */
    public function getAccessibleModules($userId)
    {
        $permissions = $this->getUserPermissions($userId);
        return array_keys($permissions);
    }

    /**
     * Get accessible actions for module
     */
    public function getAccessibleActions($userId, $module)
    {
        $permissions = $this->getUserPermissions($userId);
        return isset($permissions[$module]) ? array_keys($permissions[$module]) : [];
    }

    /**
     * Grant user-specific permission override.
     * NOTE: Legacy user_permissions table lacks clinic_id.
     * Per SEC01, rows are retained in the database but denied their use or granting as tenant authority.
     */
    public function grantUserPermission($userId, $permissionId, $assignedBy, $expiresAt = null, $reason = '')
    {
        // Deny granting as tenant authority
        if ($this->resolveCurrentClinicId() !== null) {
            log_message('warning', 'Direct user_permissions grant denied in tenant context for user_id={userId}', ['userId' => $userId]);
            return false;
        }

        $db = $this->db ?? \Config\Database::connect();
        
        // Check if already exists
        $existing = $db->table('user_permissions')
                       ->where('user_id', $userId)
                       ->where('permission_id', $permissionId)
                       ->get()
                       ->getRow();

        if ($existing) {
            // Update existing
            return $db->table('user_permissions')
                     ->where('user_id', $userId)
                     ->where('permission_id', $permissionId)
                     ->update([
                         'granted' => 1,
                         'assigned_by' => $assignedBy,
                         'expires_at' => $expiresAt,
                         'reason' => $reason
                     ]);
        } else {
            // Insert new
            return $db->table('user_permissions')
                     ->insert([
                         'user_id' => $userId,
                         'permission_id' => $permissionId,
                         'granted' => 1,
                         'assigned_by' => $assignedBy,
                         'expires_at' => $expiresAt,
                         'reason' => $reason
                     ]);
        }
    }

    /**
     * Revoke user-specific permission override.
     * NOTE: Legacy user_permissions table lacks clinic_id.
     * Per SEC01, rows are retained in the database but denied their use or granting as tenant authority.
     */
    public function revokeUserPermission($userId, $permissionId, $assignedBy, $reason = '')
    {
        // Deny modifying in tenant context
        if ($this->resolveCurrentClinicId() !== null) {
            log_message('warning', 'Direct user_permissions revoke denied in tenant context for user_id={userId}', ['userId' => $userId]);
            return false;
        }

        $db = $this->db ?? \Config\Database::connect();
        
        // Check if already exists
        $existing = $db->table('user_permissions')
                       ->where('user_id', $userId)
                       ->where('permission_id', $permissionId)
                       ->get()
                       ->getRow();

        if ($existing) {
            // Update existing
            return $db->table('user_permissions')
                     ->where('user_id', $userId)
                     ->where('permission_id', $permissionId)
                     ->update([
                         'granted' => 0,
                         'assigned_by' => $assignedBy,
                         'reason' => $reason
                     ]);
        } else {
            // Insert new (denied)
            return $db->table('user_permissions')
                     ->insert([
                         'user_id' => $userId,
                         'permission_id' => $permissionId,
                         'granted' => 0,
                         'assigned_by' => $assignedBy,
                         'reason' => $reason
                     ]);
        }
    }

    /**
     * Log permission change
     */
    public function logPermissionChange($userId, $action, $performedBy, $roleId = null, $permissionId = null, $ipAddress = null, $userAgent = null)
    {
        $db = $this->db ?? \Config\Database::connect();
        
        return $db->table('permission_audit_log')
                 ->insert([
                     'user_id' => $userId,
                     'action' => $action,
                     'role_id' => $roleId,
                     'permission_id' => $permissionId,
                     'performed_by' => $performedBy,
                     'ip_address' => $ipAddress,
                     'user_agent' => $userAgent
                 ]);
    }

    /**
     * Get permission audit log
     */
    public function getAuditLog($userId = null, $limit = 50)
    {
        $db = $this->db ?? \Config\Database::connect();
        
        $builder = $db->table('permission_audit_log pal')
                     ->select('pal.*, u.first_name, u.last_name, p.performed_by_name')
                     ->join('users u', 'u.id = pal.user_id')
                     ->join('users p', 'p.id = pal.performed_by', 'left');

        if ($userId) {
            $builder->where('pal.user_id', $userId);
        }

        return $builder->orderBy('pal.created_at', 'DESC')
                      ->limit($limit)
                      ->get()
                      ->getResultArray();
    }
}
