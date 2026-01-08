<?php

namespace App\Services;

use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\UserModel;

class PermissionService
{
    protected $permissionModel;
    protected $roleModel;
    protected $userRoleModel;
    protected $userModel;

    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
        $this->roleModel = new RoleModel();
        $this->userRoleModel = new UserRoleModel();
        $this->userModel = new UserModel();
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($userId, $module, $action)
    {
        // Super Admin has all permissions
        if ($this->isSuperAdmin($userId)) {
            return true;
        }
        
        // Get user roles
        $userRoles = $this->userRoleModel->getUserRoles($userId, true);
        
        if (empty($userRoles)) {
            return false;
        }

        // Check role permissions
        foreach ($userRoles as $userRole) {
            if ($this->roleModel->hasPermission($userRole['role_id'], $module, $action)) {
                return true;
            }
        }

        // Check user-specific permission overrides
        return $this->hasUserPermission($userId, $module, $action);
    }

    /**
     * Check user-specific permission override
     */
    protected function hasUserPermission($userId, $module, $action)
    {
        $db = \Config\Database::connect();
        
        $permission = $this->permissionModel->getByModuleAction($module, $action);
        if (!$permission) {
            return false;
        }

        $result = $db->table('user_permissions up')
                    ->where('up.user_id', $userId)
                    ->where('up.permission_id', $permission['id'])
                    ->where('up.granted', 1)
                    ->where('(up.expires_at IS NULL OR up.expires_at > NOW())')
                    ->countAllResults();

        return $result > 0;
    }

    /**
     * Get user permissions
     */
    public function getUserPermissions($userId)
    {
        $permissions = [];
        
        // Get role permissions
        $userRoles = $this->userRoleModel->getUserRoles($userId, true);
        foreach ($userRoles as $userRole) {
            $rolePermissions = $this->roleModel->getPermissionsGrouped($userRole['role_id']);
            foreach ($rolePermissions as $module => $modulePermissions) {
                if (!isset($permissions[$module])) {
                    $permissions[$module] = [];
                }
                foreach ($modulePermissions as $permission) {
                    $permissions[$module][$permission['action']] = $permission;
                }
            }
        }

        // Add user-specific permissions
        $userPermissions = $this->getUserSpecificPermissions($userId);
        foreach ($userPermissions as $module => $modulePermissions) {
            if (!isset($permissions[$module])) {
                $permissions[$module] = [];
            }
            foreach ($modulePermissions as $permission) {
                $permissions[$module][$permission['action']] = $permission;
            }
        }

        return $permissions;
    }

    /**
     * Get user-specific permission overrides
     */
    protected function getUserSpecificPermissions($userId)
    {
        $db = \Config\Database::connect();
        
        $permissions = $db->table('user_permissions up')
                         ->select('p.*, up.granted, up.expires_at')
                         ->join('permissions p', 'p.id = up.permission_id')
                         ->where('up.user_id', $userId)
                         ->where('up.granted', 1)
                         ->where('(up.expires_at IS NULL OR up.expires_at > NOW())')
                         ->get()
                         ->getResultArray();

        $grouped = [];
        foreach ($permissions as $permission) {
            $grouped[$permission['module']][] = $permission;
        }

        return $grouped;
    }

    /**
     * Get user roles with permissions
     */
    public function getUserRolesWithPermissions($userId)
    {
        $userRoles = $this->userRoleModel->getUserRolesWithDetails($userId, true);
        
        foreach ($userRoles as &$userRole) {
            $userRole['permissions'] = $this->roleModel->getPermissionsGrouped($userRole['id']);
        }

        return $userRoles;
    }

    /**
     * Check if user has role
     */
    public function hasRole($userId, $roleSlug)
    {
        return $this->userRoleModel->hasRole($userId, $roleSlug);
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole($userId, $roleSlugs)
    {
        return $this->userRoleModel->hasAnyRole($userId, $roleSlugs);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin($userId)
    {
        return $this->hasRole($userId, 'super_admin');
    }

    /**
     * Check if user can manage users
     */
    public function canManageUsers($userId)
    {
        // Super Admin can manage users
        if ($this->isSuperAdmin($userId)) {
            return true;
        }
        
        return $this->hasPermission($userId, 'users', 'create') || 
               $this->hasPermission($userId, 'users', 'edit') ||
               $this->hasPermission($userId, 'users', 'delete');
    }

    /**
     * Check if user can manage roles
     */
    public function canManageRoles($userId)
    {
        // Super Admin can manage roles
        if ($this->isSuperAdmin($userId)) {
            return true;
        }
        
        return $this->hasPermission($userId, 'users', 'roles');
    }

    /**
     * Check if user is Super Admin
     */
    public function isSuperAdmin($userId)
    {
        $userRoles = $this->userRoleModel->getUserRoles($userId, true);
        
        foreach ($userRoles as $userRole) {
            $role = $this->roleModel->find($userRole['role_id']);
            if ($role && $role['slug'] === 'super_admin') {
                return true;
            }
        }
        
        return false;
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
     * Grant user-specific permission
     */
    public function grantUserPermission($userId, $permissionId, $assignedBy, $expiresAt = null, $reason = '')
    {
        $db = \Config\Database::connect();
        
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
     * Revoke user-specific permission
     */
    public function revokeUserPermission($userId, $permissionId, $assignedBy, $reason = '')
    {
        $db = \Config\Database::connect();
        
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
        $db = \Config\Database::connect();
        
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
        $db = \Config\Database::connect();
        
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
