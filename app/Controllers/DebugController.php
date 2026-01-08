<?php

namespace App\Controllers;

use App\Libraries\IonAuth;
use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Services\PermissionService;

class DebugController extends BaseController
{
    protected $ionAuth;
    protected $permissionModel;
    protected $roleModel;
    protected $userRoleModel;
    protected $permissionService;

    public function __construct()
    {
        $this->ionAuth = new IonAuth();
        $this->permissionModel = new PermissionModel();
        $this->roleModel = new RoleModel();
        $this->userRoleModel = new UserRoleModel();
        $this->permissionService = new PermissionService();
    }

    /**
     * Debug RBAC status
     */
    public function rbac()
    {
        // Check if user is admin using IonAuth
        if (!$this->ionAuth->isAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'You must be an administrator to access debug information.');
        }

        $data = [
            'title' => 'RBAC Debug Information',
            'debug_info' => $this->getDebugInfo()
        ];

        return view('debug/rbac', $data);
    }

    /**
     * Debug role permissions specifically
     */
    public function rolePermissions()
    {
        // Check if user is admin using IonAuth
        if (!$this->ionAuth->isAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'You must be an administrator to access debug information.');
        }

        $data = [
            'title' => 'Role Permissions Debug',
            'debug_info' => $this->getRolePermissionsDebug()
        ];

        return view('debug/role_permissions', $data);
    }

    /**
     * Get comprehensive debug information
     */
    private function getDebugInfo()
    {
        $info = [];

        try {
            // 1. Check if user is logged in
            $info['logged_in'] = $this->ionAuth->loggedIn();
            
            if ($info['logged_in']) {
                $user = $this->ionAuth->user()->row();
                $info['user'] = [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name
                ];

                // 2. Check IonAuth admin status
                $info['ionauth_admin'] = $this->ionAuth->isAdmin();

                // 3. Check IonAuth groups
                $groups = $this->ionAuth->getUsersGroups($user->id)->getResult();
                $info['ionauth_groups'] = [];
                foreach ($groups as $group) {
                    $info['ionauth_groups'][] = [
                        'id' => $group->id,
                        'name' => $group->name,
                        'description' => $group->description
                    ];
                }

                // 4. Check RBAC database status
                $info['rbac_permissions_count'] = $this->permissionModel->countAll();
                $info['rbac_roles_count'] = $this->roleModel->countAll();
                $info['rbac_user_roles_count'] = $this->userRoleModel->countAll();

                // 5. Check if user has Super Admin role
                $info['has_super_admin_role'] = $this->userRoleModel->hasRole($user->id, 'super_admin');

                // 6. Check user roles
                $userRoles = $this->userRoleModel->getUserRoles($user->id, true);
                $info['user_roles'] = [];
                foreach ($userRoles as $userRole) {
                    $role = $this->roleModel->find($userRole['role_id']);
                    if ($role) {
                        $info['user_roles'][] = [
                            'id' => $role['id'],
                            'name' => $role['name'],
                            'slug' => $role['slug'],
                            'description' => $role['description']
                        ];
                    }
                }

                // 7. Check Super Admin role exists
                $superAdminRole = $this->roleModel->getBySlug('super_admin');
                $info['super_admin_role'] = $superAdminRole ? [
                    'id' => $superAdminRole['id'],
                    'name' => $superAdminRole['name'],
                    'slug' => $superAdminRole['slug'],
                    'description' => $superAdminRole['description']
                ] : null;

                // 8. Check permission service
                $info['can_manage_users'] = $this->permissionService->canManageUsers($user->id);
                $info['can_manage_roles'] = $this->permissionService->canManageRoles($user->id);
                $info['is_super_admin'] = $this->permissionService->isSuperAdmin($user->id);

                // 9. Check specific permissions
                $info['has_user_create'] = $this->permissionService->hasPermission($user->id, 'users', 'create');
                $info['has_user_edit'] = $this->permissionService->hasPermission($user->id, 'users', 'edit');
                $info['has_user_delete'] = $this->permissionService->hasPermission($user->id, 'users', 'delete');
                $info['has_user_roles'] = $this->permissionService->hasPermission($user->id, 'users', 'roles');

            } else {
                $info['error'] = 'User not logged in';
            }

        } catch (\Exception $e) {
            $info['error'] = 'Exception: ' . $e->getMessage();
            $info['trace'] = $e->getTraceAsString();
        }

        return $info;
    }

    /**
     * Get role permissions debug information
     */
    private function getRolePermissionsDebug()
    {
        $info = [];

        try {
            // Get all roles
            $roles = $this->roleModel->findAll();
            $info['roles'] = [];

            foreach ($roles as $role) {
                $roleInfo = [
                    'id' => $role['id'],
                    'name' => $role['name'],
                    'slug' => $role['slug'],
                    'is_active' => $role['is_active'],
                    'is_system' => $role['is_system']
                ];

                // Get permissions for this role
                $rolePermissions = $this->roleModel->getPermissionsGrouped($role['id']);
                $roleInfo['permissions'] = $rolePermissions;
                $roleInfo['permission_count'] = 0;
                foreach ($rolePermissions as $modulePermissions) {
                    $roleInfo['permission_count'] += count($modulePermissions);
                }

                // Get user count for this role
                $roleInfo['user_count'] = $this->roleModel->getUserCount($role['id']);

                // Get users assigned to this role
                $roleUsers = $this->roleModel->getUsers($role['id']);
                $roleInfo['users'] = $roleUsers;

                $info['roles'][] = $roleInfo;
            }

            // Get all permissions
            $allPermissions = $this->permissionModel->findAll();
            $info['all_permissions'] = $allPermissions;
            $info['total_permissions'] = count($allPermissions);

            // Get all user-role assignments
            $allUserRoles = $this->userRoleModel->findAll();
            $info['all_user_roles'] = $allUserRoles;
            $info['total_user_roles'] = count($allUserRoles);

            // Get role-permission assignments
            $db = \Config\Database::connect();
            $rolePermissions = $db->table('role_permissions')->get()->getResultArray();
            $info['role_permissions'] = $rolePermissions;
            $info['total_role_permissions'] = count($rolePermissions);

        } catch (\Exception $e) {
            $info['error'] = 'Exception: ' . $e->getMessage();
            $info['trace'] = $e->getTraceAsString();
        }

        return $info;
    }
}