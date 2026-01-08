<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\PermissionModel;
use App\Services\PermissionService;
use App\Libraries\IonAuth;

class UserManagementController extends BaseController
{
    protected $userModel;
    protected $roleModel;
    protected $userRoleModel;
    protected $permissionModel;
    protected $permissionService;
    protected $ionAuth;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->userRoleModel = new UserRoleModel();
        $this->permissionModel = new PermissionModel();
        $this->permissionService = new PermissionService();
        $this->ionAuth = new IonAuth(); 
    }

    /**
     * Check if user can manage users (with RBAC fallback to IonAuth)
     */
    private function canManageUsers()
    {
        try {
            // Check if user is logged in first
            if (!$this->isLoggedIn()) {
                log_message('debug', 'UserManagement: User not logged in');
                return false;
            }
            
            $user = $this->getCurrentUser();
            if (!$user) {
                log_message('debug', 'UserManagement: No user data found');
                return false;
            }
            
            log_message('debug', 'UserManagement: Checking permissions for user ID: ' . $user->id);
            
            // Check if RBAC system is ready (has permissions in database)
            $permissionCount = $this->permissionModel->countAll();
            log_message('debug', 'UserManagement: Permission count in database: ' . $permissionCount);
            
            if ($permissionCount == 0) {
                // RBAC not set up yet, use IonAuth
                $isAdmin = $this->ionAuth->isAdmin();
                log_message('debug', 'UserManagement: RBAC not ready, using IonAuth. Is admin: ' . ($isAdmin ? 'Yes' : 'No'));
                return $isAdmin;
            }
            
            // Try RBAC first
            $canManage = $this->permissionService->canManageUsers($user->id);
            log_message('debug', 'UserManagement: RBAC permission check result: ' . ($canManage ? 'Yes' : 'No'));
            return $canManage;
        } catch (\Exception $e) {
            // Fallback to IonAuth during setup
            log_message('error', 'UserManagement: Exception in permission check: ' . $e->getMessage());
            $isAdmin = $this->ionAuth->isAdmin();
            log_message('debug', 'UserManagement: Exception fallback to IonAuth. Is admin: ' . ($isAdmin ? 'Yes' : 'No'));
            return $isAdmin;
        }
    }

    /**
     * Display users list with role management
     */
    public function index()
    {
        // Check if user can manage users (fallback to IonAuth during setup)
        if (!$this->canManageUsers()) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to manage users.');
        }

        $data = [
            'title' => 'User Management',
            'users' => $this->getUsersWithRoles(),
            'roles' => $this->roleModel->getActiveRoles(),
            'permissions' => $this->permissionModel->getGroupedByModule()
        ];

        return $this->view('user_management/index', $data);
    }

    /**
     * Show user creation form
     */
    public function create()
    {
        // Check if user can create users
        if (!$this->permissionService->hasPermission($this->getCurrentUser()->id, 'users', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to create users.');
        }

        $data = [
            'title' => 'Create New User',
            'roles' => $this->roleModel->getActiveRoles(),
            'permissions' => $this->permissionModel->getGroupedByModule(),
            'categories' => \App\Config\Permissions::getCategories()
        ];

        return $this->view('user_management/create', $data);
    }

    /**
     * Store new user
     */
    public function store()
    {
        // Check if user can create users
        if (!$this->permissionService->hasPermission($this->getCurrentUser()->id, 'users', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to create users.');
        }

        $rules = [
            'first_name' => 'required|max_length[50]',
            'last_name' => 'required|max_length[50]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'phone' => 'permit_empty|max_length[20]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'roles' => 'permit_empty',
            'permissions' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('validation', $this->validator);
        }

        $userData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'password' => $this->request->getPost('password'),
            'active' => 1
        ];

        $userId = $this->ionAuth->register($userData['email'], $userData['password'], $userData['first_name'], $userData['last_name'], $userData['phone']);

        if ($userId) {
            // Assign roles
            $roles = $this->request->getPost('roles') ?? [];
            if (!empty($roles)) {
                $this->userRoleModel->assignRoles($userId, $roles, $this->getCurrentUser()->id);
            }

            // Assign individual permissions
            $permissions = $this->request->getPost('permissions') ?? [];
            foreach ($permissions as $permissionId) {
                $this->permissionService->grantUserPermission($userId, $permissionId, $this->getCurrentUser()->id);
            }

            return redirect()->to('/user-management')
                           ->with('success', 'User created successfully.');
        } else {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to create user. Please try again.');
        }
    }

    /**
     * Show user edit form
     */
    public function edit($id)
    {
        // Check if user can edit users
        if (!$this->permissionService->hasPermission($this->getCurrentUser()->id, 'users', 'edit')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to edit users.');
        }

        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/user-management')->with('error', 'User not found.');
        }

        $data = [
            'title' => 'Edit User: ' . $user['first_name'] . ' ' . $user['last_name'],
            'user' => $user,
            'user_roles' => $this->userRoleModel->getUserRolesWithDetails($id),
            'user_permissions' => $this->permissionService->getUserPermissions($id),
            'roles' => $this->roleModel->getActiveRoles(),
            'permissions' => $this->permissionModel->getGroupedByModule(),
            'categories' => \App\Config\Permissions::getCategories()
        ];

        return $this->view('user_management/edit', $data);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        // Check if user can edit users
        if (!$this->permissionService->hasPermission($this->getCurrentUser()->id, 'users', 'edit')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to edit users.');
        }

        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/user-management')->with('error', 'User not found.');
        }

        $rules = [
            'first_name' => 'required|max_length[50]',
            'last_name' => 'required|max_length[50]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'phone' => 'permit_empty|max_length[20]',
            'password' => 'permit_empty|min_length[8]',
            'password_confirm' => 'permit_empty|matches[password]',
            'active' => 'permit_empty|in_list[0,1]',
            'roles' => 'permit_empty',
            'permissions' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('validation', $this->validator);
        }

        $userData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'active' => $this->request->getPost('active') ?? 1
        ];

        // Update password if provided
        if ($this->request->getPost('password')) {
            $userData['password'] = $this->request->getPost('password');
        }

        $success = $this->userModel->update($id, $userData);

        if ($success) {
            // Update roles
            $roles = $this->request->getPost('roles') ?? [];
            $this->userRoleModel->assignRoles($id, $roles, $this->getCurrentUser()->id);

            // Update individual permissions
            $permissions = $this->request->getPost('permissions') ?? [];
            $this->updateUserPermissions($id, $permissions);

            return redirect()->to('/user-management')
                           ->with('success', 'User updated successfully.');
        } else {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to update user. Please try again.');
        }
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        // Check if user can delete users
        if (!$this->permissionService->hasPermission($this->getCurrentUser()->id, 'users', 'delete')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to delete users.');
        }

        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/user-management')->with('error', 'User not found.');
        }

        // Cannot delete super admin
        if ($this->permissionService->isAdmin($id)) {
            return redirect()->to('/user-management')
                           ->with('error', 'Cannot delete super admin user.');
        }

        $success = $this->userModel->delete($id);

        if ($success) {
            return redirect()->to('/user-management')
                           ->with('success', 'User deleted successfully.');
        } else {
            return redirect()->to('/user-management')
                           ->with('error', 'Failed to delete user.');
        }
    }

    /**
     * Show user details
     */
    public function show($id)
    {
        // Check if user can view users
        if (!$this->permissionService->hasPermission($this->getCurrentUser()->id, 'users', 'view')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to view users.');
        }

        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/user-management')->with('error', 'User not found.');
        }

        $data = [
            'title' => 'User Details: ' . $user['first_name'] . ' ' . $user['last_name'],
            'user' => $user,
            'user_roles' => $this->userRoleModel->getUserRolesWithDetails($id),
            'user_permissions' => $this->permissionService->getUserPermissions($id),
            'assignment_history' => $this->userRoleModel->getAssignmentHistory($id),
            'audit_log' => $this->permissionService->getAuditLog($id, 20)
        ];

        return $this->view('user_management/show', $data);
    }

    /**
     * Assign role to user (AJAX)
     */
    public function assignRole()
    {
        // Check if user can manage users
        if (!$this->canManageUsers()) { 
            return $this->response->setJSON(['error' => 'Permission denied']);
        }

        $userId = $this->request->getPost('user_id');
        $roleId = $this->request->getPost('role_id');
        $expiresAt = $this->request->getPost('expires_at');

        $success = $this->userRoleModel->assignRole($userId, $roleId, $this->getCurrentUser()->id, $expiresAt);

        if ($success) {
            return $this->response->setJSON(['success' => true, 'message' => 'Role assigned successfully']);
        } else {
            return $this->response->setJSON(['error' => 'Failed to assign role']);
        }
    }

    /**
     * Remove role from user (AJAX)
     */
    public function removeRole()
    {
        // Check if user can manage users
        if (!$this->canManageUsers()) {
            return $this->response->setJSON(['error' => 'Permission denied']);
        }

        $userId = $this->request->getPost('user_id');
        $roleId = $this->request->getPost('role_id');

        $success = $this->userRoleModel->removeRole($userId, $roleId);

        if ($success) {
            return $this->response->setJSON(['success' => true, 'message' => 'Role removed successfully']);
        } else {
            return $this->response->setJSON(['error' => 'Failed to remove role']);
        }
    }

    /**
     * Grant permission to user (AJAX)
     */
    public function grantPermission()
    {
        // Check if user can manage users
        if (!$this->canManageUsers()) {
            return $this->response->setJSON(['error' => 'Permission denied']);
        }

        $userId = $this->request->getPost('user_id');
        $permissionId = $this->request->getPost('permission_id');
        $expiresAt = $this->request->getPost('expires_at');
        $reason = $this->request->getPost('reason');

        $success = $this->permissionService->grantUserPermission($userId, $permissionId, $this->getCurrentUser()->id, $expiresAt, $reason);

        if ($success) {
            return $this->response->setJSON(['success' => true, 'message' => 'Permission granted successfully']);
        } else {
            return $this->response->setJSON(['error' => 'Failed to grant permission']);
        }
    }

    /**
     * Revoke permission from user (AJAX)
     */
    public function revokePermission()
    {
        // Check if user can manage users
        if (!$this->canManageUsers()) {
            return $this->response->setJSON(['error' => 'Permission denied']);
        }

        $userId = $this->request->getPost('user_id');
        $permissionId = $this->request->getPost('permission_id');
        $reason = $this->request->getPost('reason');

        $success = $this->permissionService->revokeUserPermission($userId, $permissionId, $this->getCurrentUser()->id, $reason);

        if ($success) {
            return $this->response->setJSON(['success' => true, 'message' => 'Permission revoked successfully']);
        } else {
            return $this->response->setJSON(['error' => 'Failed to revoke permission']);
        }
    }

    /**
     * Get users with their roles
     */
    protected function getUsersWithRoles()
    {
        $users = $this->userModel->findAll();
        
        foreach ($users as &$user) {
            $user['roles'] = $this->userRoleModel->getUserRolesWithDetails($user['id']);
            $user['role_count'] = count($user['roles']);
        }

        return $users;
    }

    /**
     * Update user permissions
     */
    protected function updateUserPermissions($userId, $permissions)
    {
        // Get current user permissions
        $currentPermissions = $this->permissionService->getUserSpecificPermissions($userId);
        $currentPermissionIds = [];
        
        foreach ($currentPermissions as $modulePermissions) {
            foreach ($modulePermissions as $permission) {
                $currentPermissionIds[] = $permission['id'];
            }
        }

        // Remove permissions not in the new list
        foreach ($currentPermissionIds as $permissionId) {
            if (!in_array($permissionId, $permissions)) {
                $this->permissionService->revokeUserPermission($userId, $permissionId, $this->getCurrentUser()->id, 'Updated via user management');
            }
        }

        // Add new permissions
        foreach ($permissions as $permissionId) {
            if (!in_array($permissionId, $currentPermissionIds)) {
                $this->permissionService->grantUserPermission($userId, $permissionId, $this->getCurrentUser()->id, null, 'Updated via user management');
            }
        }
    }

    /**
     * Get user permissions (AJAX)
     */
    public function getUserPermissions($userId = null)
    {
        // Check if user can view users
        if (!$this->permissionService->hasPermission($this->getCurrentUser()->id, 'users', 'view')) {
            return $this->response->setJSON(['error' => 'Permission denied']);
        }

        $permissions = $this->permissionService->getUserPermissions($userId);
        
        return $this->response->setJSON([
            'success' => true,
            'permissions' => $permissions
        ]);
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        // Check if user can edit users
        if (!$this->permissionService->hasPermission($this->getCurrentUser()->id, 'users', 'edit')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to edit users.');
        }

        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/user-management')->with('error', 'User not found.');
        }

        // Cannot deactivate super admin
        if ($this->permissionService->isAdmin($id)) {
            return redirect()->to('/user-management')
                           ->with('error', 'Cannot deactivate super admin user.');
        }

        $newStatus = $user['active'] ? 0 : 1;
        
        $success = $this->userModel->update($id, ['active' => $newStatus]);

        if ($success) {
            $status = $newStatus ? 'activated' : 'deactivated';
            return redirect()->to('/user-management')
                           ->with('success', "User {$status} successfully.");
        } else {
            return redirect()->to('/user-management')
                           ->with('error', 'Failed to update user status.');
        }
    }
}
