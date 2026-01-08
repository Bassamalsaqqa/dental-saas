<?php

namespace App\Controllers;

use App\Models\IonAuthModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\PermissionModel;
use App\Services\PermissionService;
use App\Libraries\IonAuth;

class Users extends BaseController
{
    protected $ionAuthModel;
    protected $ionAuth;
    protected $roleModel;
    protected $userRoleModel;
    protected $permissionModel;
    protected $permissionService;

    public function __construct()
    {
        $this->ionAuthModel = new IonAuthModel();
        $this->ionAuth = new IonAuth();
        $this->roleModel = new RoleModel();
        $this->userRoleModel = new UserRoleModel();
        $this->permissionModel = new PermissionModel();
        $this->permissionService = new PermissionService();
    }

    public function index()
    {
        try {
            // Get all users using IonAuth
            $users = $this->ionAuth->users()->result();
            
            // Get RBAC roles for each user (prioritize RBAC over IonAuth groups)
            foreach ($users as $user) {
                // Get RBAC roles if system is ready
                try {
                    $userRoles = $this->getUserRoles($user->id);
                    $user->roles = $userRoles;
                    $user->permissions = $this->getUserPermissions($user->id);
                    
                    // If RBAC roles exist, use them; otherwise fall back to IonAuth groups
                    if (!empty($userRoles)) {
                        $user->display_roles = $userRoles;
                    } else {
                        $user->display_roles = $this->ionAuth->getUsersGroups($user->id)->getResult();
                    }
                } catch (\Exception $e) {
                    $user->roles = [];
                    $user->permissions = [];
                    $user->display_roles = $this->ionAuth->getUsersGroups($user->id)->getResult();
                }
            }

            // Get all available groups for the filter
            $availableGroups = $this->ionAuthModel->groups()->result();
            
            // Get RBAC data if system is ready
            $roles = [];
            $permissions = [];
            $diagnostics = [
                'rbac_ready' => false,
                'total_permissions' => 0,
                'total_roles' => 0,
                'total_user_roles' => 0
            ];
            
            try {
                $roles = $this->roleModel->getActiveRoles();
                $permissions = $this->permissionModel->getGroupedByModule();
                
                // Check RBAC system status
                $diagnostics['total_permissions'] = $this->permissionModel->countAll();
                $diagnostics['total_roles'] = $this->roleModel->countAll();
                $diagnostics['total_user_roles'] = $this->userRoleModel->countAll();
                $diagnostics['rbac_ready'] = $diagnostics['total_permissions'] > 0;
            } catch (\Exception $e) {
                // RBAC not ready yet
                log_message('debug', 'RBAC not ready in Users index: ' . $e->getMessage());
            }

            $data = array_merge([
                'title' => 'User Management',
                'users' => $users,
                'available_groups' => $availableGroups,
                'roles' => $roles,
                'permissions' => $permissions,
                'diagnostics' => $diagnostics,
                'total_users' => count($users),
                'active_users' => count(array_filter($users, function($user) { return $user->active == 1; })),
                'inactive_users' => count(array_filter($users, function($user) { return $user->active == 0; })),
            ], $this->getUserDataForView());

            return $this->view('users/index', $data);
        } catch (\Exception $e) {
            // Log the error
            log_message('error', 'Users index error: ' . $e->getMessage());
            
            // Return a simplified version
            $data = array_merge([
                'title' => 'User Management',
                'users' => [],
                'available_groups' => [],
                'total_users' => 0,
                'active_users' => 0,
                'inactive_users' => 0,
                'error' => 'Unable to load user data. Please check the database connection.'
            ], $this->getUserDataForView());

            return $this->view('users/index', $data);
        }
    }

    public function create()
    {
        // Get all available roles from roles table
        $roleModel = new \App\Models\RoleModel();
        $rolesData = $roleModel->getActiveRoles();
        
        // Convert roles to format for the view
        $roles = [];
        foreach ($rolesData as $role) {
            $roles[$role['id']] = $role['name'];
        }
        
        $data = array_merge([
            'title' => 'Create New User',
            'roles' => $roles,
            'departments' => $this->getDepartments(),
        ], $this->getUserDataForView());

        return $this->view('users/create', $data);
    }

    public function store()
    {
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
            'role' => 'required|integer',
            'phone' => 'permit_empty|min_length[10]|max_length[20]',
            'address' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $roleId = $this->request->getPost('role');
        
        // Validate that the role exists and is active
        $roleModel = new \App\Models\RoleModel();
        $role = $roleModel->where('id', $roleId)->where('is_active', 1)->first();
        
        if (!$role) {
            return redirect()->back()->withInput()->with('error', 'Invalid role selected.');
        }

        $additionalData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'phone' => $this->request->getPost('phone') ?: null,
            'address' => $this->request->getPost('address') ?: null,
        ];

        // Create user using IonAuth (without groups for now)
        $userId = $this->ionAuth->register(
            $this->request->getPost('email'),
            $this->request->getPost('password'),
            $this->request->getPost('email'),
            $additionalData
        );

        if ($userId) {
            // Assign role to user using UserRoleModel
            $userRoleModel = new \App\Models\UserRoleModel();
            $currentUserId = $this->getCurrentUser()->id;
            
            $roleAssigned = $userRoleModel->assignRole($userId, $roleId, $currentUserId);
            
            if ($roleAssigned) {
                return redirect()->to(base_url('users'))->with('success', 'User created successfully!');
            } else {
                // If role assignment fails, delete the user and show error
                $this->ionAuth->deleteUser($userId);
                return redirect()->back()->withInput()->with('error', 'Failed to assign role to user. Please try again.');
            }
        } else {
            $errors = $this->ionAuth->errors();
            $errorMessage = is_array($errors) ? implode(', ', $errors) : ($errors ?: 'Failed to create user. Please try again.');
            return redirect()->back()->withInput()->with('error', $errorMessage);
        }
    }

    public function show($id)
    {
        $user = $this->ionAuth->user($id)->row();
        
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }

        // Get RBAC roles for display (prioritize RBAC over IonAuth groups)
        try {
            $userRoles = $this->getUserRoles($id);
            if (!empty($userRoles)) {
                $user->display_roles = $userRoles;
            } else {
                $user->display_roles = $this->ionAuth->getUsersGroups($id)->getResult();
            }
        } catch (\Exception $e) {
            $user->display_roles = $this->ionAuth->getUsersGroups($id)->getResult();
        }

        $data = [
            'title' => 'User Details',
            'user' => $user,
        ];
        
        // Add current user data for view without overwriting the user being viewed
        $currentUserData = $this->getUserDataForView();
        $data['current_user'] = $currentUserData['user'];
        $data['user_groups'] = $currentUserData['user_groups'];

        return $this->view('users/show', $data);
    }

    public function edit($id)
    {
        $user = $this->ionAuth->user($id)->row();
        
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }

        // Get user roles from user_roles table
        $userRoleModel = new \App\Models\UserRoleModel();
        $userRoles = $userRoleModel->getUserRoles($id);
        $user->roles = $userRoles;
        
        // Get all available roles from roles table
        $roleModel = new \App\Models\RoleModel();
        $rolesData = $roleModel->getActiveRoles();
        
        // Convert roles to format for the view
        $roles = [];
        foreach ($rolesData as $role) {
            $roles[$role['id']] = $role['name'];
        }

        // Prepare data for the view
        $data = [
            'title' => 'Edit User',
            'user' => $user, // The user being edited
            'roles' => $roles,
            'departments' => $this->getDepartments(),
        ];
        
        // Add current user data without conflicts
        $currentUser = $this->getCurrentUser();
        $data['current_user'] = $currentUser;
        $data['user_groups'] = $this->getUserGroups();

        return view('users/edit', $data);
    }

    public function update($id)
    {
        $user = $this->ionAuth->user($id)->row();
        
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }

        // Prevent role changes for admin user (ID: 1)
        if ($user->id == 1) {
            return redirect()->back()->withInput()->with('error', 'Cannot modify the role of the system administrator account.');
        }

        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => 'required|valid_email',
            'phone' => 'permit_empty|min_length[10]|max_length[20]',
            'address' => 'permit_empty|max_length[500]',
            'role' => 'required|integer',
        ];

        // Only validate password if it's provided
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[8]|max_length[255]';
            $rules['password_confirm'] = 'matches[password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $roleId = $this->request->getPost('role');
        
        // Validate that the role exists and is active
        $roleModel = new \App\Models\RoleModel();
        $role = $roleModel->where('id', $roleId)->where('is_active', 1)->first();
        
        if (!$role) {
            return redirect()->back()->withInput()->with('error', 'Invalid role selected.');
        }

        $additionalData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'phone' => $this->request->getPost('phone') ?: null,
            'address' => $this->request->getPost('address') ?: null,
        ];

        // Update user using IonAuth
        $updateData = [
            'email' => $this->request->getPost('email'),
            'additional_data' => $additionalData,
        ];

        // Update password if provided
        if ($this->request->getPost('password')) {
            $updateData['password'] = $this->request->getPost('password');
        }

        if ($this->ionAuth->update($id, $updateData)) {
            // Update user role
            $userRoleModel = new \App\Models\UserRoleModel();
            $currentUserId = $this->getCurrentUser()->id;
            
            // Remove all existing roles and assign new one
            $roleUpdated = $userRoleModel->assignRoles($id, [$roleId], $currentUserId);
            
            if ($roleUpdated) {
                return redirect()->to(base_url('users'))->with('success', 'User updated successfully!');
            } else {
                return redirect()->back()->withInput()->with('error', 'User updated but failed to update role. Please try again.');
            }
        } else {
            $errors = $this->ionAuth->errors();
            $errorMessage = is_array($errors) ? implode(', ', $errors) : ($errors ?: 'Failed to update user. Please try again.');
            return redirect()->back()->withInput()->with('error', $errorMessage);
        }
    }

    public function delete($id)
    {
        $user = $this->ionAuth->user($id)->row();
        
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User not found']);
        }

        // Prevent deletion of admin user (ID: 1)
        if ($user->id == 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot delete the system administrator account']);
        }

        // Prevent deletion of the last admin
        if ($this->ionAuth->inGroup('admin', $id)) {
            $adminUsers = $this->ionAuth->users('admin')->result();
            if (count($adminUsers) <= 1) {
                return $this->response->setJSON(['success' => false, 'message' => 'Cannot delete the last admin user']);
            }
        }

        if ($this->ionAuth->deleteUser($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            $errors = $this->ionAuth->errors();
            return $this->response->setJSON(['success' => false, 'message' => is_array($errors) ? implode(', ', $errors) : ($errors ?: 'Failed to delete user')]);
        }
    }

    public function changePassword($id)
    {
        $user = $this->ionAuth->user($id)->row();
        
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }

        $data = array_merge([
            'title' => 'Change Password',
            'user' => $user,
        ], $this->getUserDataForView());

        return $this->view('users/change_password', $data);
    }

    public function updatePassword($id)
    {
        $user = $this->ionAuth->user($id)->row();
        
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]|max_length[255]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Use IonAuth to change password
        if ($this->ionAuth->changePassword($id, $this->request->getPost('current_password'), $this->request->getPost('new_password'))) {
            return redirect()->to(base_url('users'))->with('success', 'Password updated successfully!');
        } else {
            $errors = $this->ionAuth->errors();
            $errorMessage = is_array($errors) ? implode(', ', $errors) : ($errors ?: 'Failed to update password. Please try again.');
            return redirect()->back()->withInput()->with('error', $errorMessage);
        }
    }

    public function toggleStatus($id)
    {
        $user = $this->ionAuth->user($id)->row();
        
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User not found']);
        }

        $newStatus = $user->active == 1 ? 0 : 1;
        
        if ($this->ionAuth->update($id, ['active' => $newStatus])) {
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'User status updated successfully',
                'new_status' => $newStatus == 1 ? 'active' : 'inactive'
            ]);
        } else {
            $errors = $this->ionAuth->errors();
            return $this->response->setJSON(['success' => false, 'message' => is_array($errors) ? implode(', ', $errors) : ($errors ?: 'Failed to update user status')]);
        }
    }

    public function getUserStats()
    {
        $users = $this->ionAuth->users()->result();
        $groups = $this->ionAuthModel->groups()->result();
        
        $stats = [
            'total' => count($users),
            'active' => count(array_filter($users, function($user) { return $user->active == 1; })),
            'inactive' => count(array_filter($users, function($user) { return $user->active == 0; })),
            'by_group' => []
        ];

        // Get user count by group
        foreach ($groups as $group) {
            $groupUsers = $this->ionAuth->users($group->id)->result();
            $stats['by_group'][] = [
                'name' => $group->name,
                'count' => count($groupUsers)
            ];
        }

        return $this->response->setJSON($stats);
    }

    private function getDepartments()
    {
        return [
            'dental' => 'General Dentistry',
            'orthodontics' => 'Orthodontics',
            'oral_surgery' => 'Oral Surgery',
            'periodontics' => 'Periodontics',
            'pediatrics' => 'Pediatric Dentistry',
            'general' => 'General Practice',
        ];
    }

    // ==================== RBAC METHODS ====================

    /**
     * Check if user can manage users (with RBAC fallback to IonAuth)
     */
    private function canManageUsers()
    {
        try {
            // Check if user is logged in first
            if (!$this->isLoggedIn()) {
                return false;
            }
            
            $user = $this->getCurrentUser();
            if (!$user) {
                return false;
            }
            
            // Check if RBAC system is ready (has permissions in database)
            $permissionCount = $this->permissionModel->countAll();
            
            if ($permissionCount == 0) {
                // RBAC not set up yet, use IonAuth
                return $this->ionAuth->isAdmin();
            }
            
            // Try RBAC first
            return $this->permissionService->canManageUsers($user->id);
        } catch (\Exception $e) {
            // Fallback to IonAuth during setup
            return $this->ionAuth->isAdmin();
        }
    }

    /**
     * Get user roles
     */
    public function getUserRoles($userId)
    {
        try {
            $userRoles = $this->userRoleModel->getUserRoles($userId, true);
            $roles = [];
            
            foreach ($userRoles as $userRole) {
                $role = $this->roleModel->find($userRole['role_id']);
                if ($role) {
                    $roles[] = $role;
                }
            }
            
            return $roles;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get user permissions
     */
    public function getUserPermissions($userId = null)
    {
        try {
            if (!$userId) {
                $user = $this->getCurrentUser();
                if (!$user) {
                    return [];
                }
                $userId = $user->id;
            }
            
            return $this->permissionService->getUserPermissions($userId);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Assign role to user (AJAX)
     */
    public function assignRole()
    {
        if (!$this->canManageUsers()) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have permission to manage users.']);
        }

        $userId = $this->request->getPost('user_id');
        $roleId = $this->request->getPost('role_id');
        $expiresAt = $this->request->getPost('expires_at');

        // Prevent role changes for admin user (ID: 1)
        $user = $this->ionAuth->user($userId)->row();
        if ($user && $user->id == 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot modify the role of the system administrator account.']);
        }

        $success = $this->userRoleModel->assignRole($userId, $roleId, $this->getCurrentUser()->id, $expiresAt);

        if ($success) {
            return $this->response->setJSON(['success' => true, 'message' => 'Role assigned successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to assign role']);
        }
    }

    /**
     * Remove role from user (AJAX)
     */
    public function removeRole()
    {
        if (!$this->canManageUsers()) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have permission to manage users.']);
        }

        $userId = $this->request->getPost('user_id');
        $roleId = $this->request->getPost('role_id');

        // Prevent role changes for admin user (ID: 1)
        $user = $this->ionAuth->user($userId)->row();
        if ($user && $user->id == 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot modify the role of the system administrator account.']);
        }

        $success = $this->userRoleModel->removeRole($userId, $roleId);

        if ($success) {
            return $this->response->setJSON(['success' => true, 'message' => 'Role removed successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to remove role']);
        }
    }

    /**
     * Grant permission to user (AJAX)
     */
    public function grantPermission()
    {
        if (!$this->canManageUsers()) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have permission to manage users.']);
        }

        $userId = $this->request->getPost('user_id');
        $permissionId = $this->request->getPost('permission_id');
        $expiresAt = $this->request->getPost('expires_at');

        // Prevent permission changes for admin user (ID: 1)
        $user = $this->ionAuth->user($userId)->row();
        if ($user && $user->id == 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot modify the permissions of the system administrator account.']);
        }

        $success = $this->permissionService->grantUserPermission($userId, $permissionId, $this->getCurrentUser()->id, $expiresAt);

        if ($success) {
            return $this->response->setJSON(['success' => true, 'message' => 'Permission granted successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to grant permission']);
        }
    }

    /**
     * Revoke permission from user (AJAX)
     */
    public function revokePermission()
    {
        if (!$this->canManageUsers()) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have permission to manage users.']);
        }

        $userId = $this->request->getPost('user_id');
        $permissionId = $this->request->getPost('permission_id');

        // Prevent permission changes for admin user (ID: 1)
        $user = $this->ionAuth->user($userId)->row();
        if ($user && $user->id == 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot modify the permissions of the system administrator account.']);
        }

        $success = $this->permissionService->revokeUserPermission($userId, $permissionId, $this->getCurrentUser()->id);

        if ($success) {
            return $this->response->setJSON(['success' => true, 'message' => 'Permission revoked successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to revoke permission']);
        }
    }

    /**
     * Get user permissions for display (AJAX)
     */
    public function getUserPermissionsAjax($userId = null)
    {
        if (!$this->canManageUsers()) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have permission to manage users.']);
        }

        if (!$userId) {
            $userId = $this->request->getPost('user_id');
        }

        $permissions = $this->getUserPermissions($userId);
        $roles = $this->getUserRoles($userId);

        return $this->response->setJSON([
            'success' => true,
            'permissions' => $permissions,
            'roles' => $roles
        ]);
    }
}
