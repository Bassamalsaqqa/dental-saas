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
    protected $clinicUserModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->userRoleModel = new UserRoleModel();
        $this->permissionModel = new PermissionModel();
        $this->permissionService = new PermissionService();
        $this->ionAuth = new IonAuth(); 
        $this->clinicUserModel = new \App\Models\ClinicUserModel();
    }

    /**
     * Helper to find user scoped to current clinic if set
     */
    protected function findScopedUser($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if ($clinicId) {
            // Use UserModel's findByClinic helper
            return $this->userModel->findByClinic($clinicId, $id);
        }
        // If no clinic context, allow global find (Control Plane)
        return $this->userModel->find($id);
    }

    /**
     * Check if user can manage users (with RBAC fallback to IonAuth)
     */
    private function canManageUsers()
    {
        try {
            if (!$this->isLoggedIn()) {
                return false;
            }
            
            $user = $this->getCurrentUser();
            if (!$user) {
                return false;
            }
            
            $permissionCount = $this->permissionModel->countAll();
            
            if ($permissionCount == 0) {
                return $this->ionAuth->isAdmin();
            }
            
            return $this->permissionService->canManageUsers($user->id);
        } catch (\Exception $e) {
            return $this->ionAuth->isAdmin();
        }
    }

    /**
     * Display users list with role management
     */
    public function index()
    {
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
            'roles' => 'required', // Enforce role selection for clinic membership validity
            'permissions' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $roles = $this->request->getPost('roles') ?? [];
        $primaryRoleId = isset($roles[0]) ? (int) $roles[0] : 0;
        if (empty($roles) || $primaryRoleId <= 0) {
            return redirect()->back()->withInput()->with('error', 'At least one valid role is required.');
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
            // Assign roles to system
            if (!empty($roles)) {
                $this->userRoleModel->assignRoles($userId, $roles, $this->getCurrentUser()->id);
            }

            // Assign to clinic with primary role
            $clinicId = session()->get('active_clinic_id');
            if ($clinicId) {
                $this->clinicUserModel->insert([
                    'clinic_id' => $clinicId,
                    'user_id' => $userId,
                    'role_id' => $primaryRoleId,
                    'status' => 'active'
                ]);
            }

            $permissions = $this->request->getPost('permissions') ?? [];
            foreach ($permissions as $permissionId) {
                $this->permissionService->grantUserPermission($userId, $permissionId, $this->getCurrentUser()->id);
            }

            return redirect()->to('/user-management')->with('success', 'User created successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to create user. Please try again.');
        }
    }

    /**
     * Show user edit form
     */
    public function edit($id)
    {
        if (!$this->permissionService->hasPermission($this->getCurrentUser()->id, 'users', 'edit')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to edit users.');
        }

        $user = $this->findScopedUser($id);
        
        if (!$user) {
            return redirect()->to('/user-management')->with('error', 'User not found or access denied.');
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
        if (!$this->permissionService->hasPermission($this->getCurrentUser()->id, 'users', 'edit')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to edit users.');
        }

        $user = $this->findScopedUser($id);
        
        if (!$user) {
            return redirect()->to('/user-management')->with('error', 'User not found or access denied.');
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
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $userData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'active' => $this->request->getPost('active') ?? 1
        ];

        if ($this->request->getPost('password')) {
            $userData['password'] = $this->request->getPost('password');
        }

        $success = $this->userModel->update($id, $userData);

        if ($success) {
            $roles = $this->request->getPost('roles') ?? [];
            $this->userRoleModel->assignRoles($id, $roles, $this->getCurrentUser()->id);

            $permissions = $this->request->getPost('permissions') ?? [];
            $this->updateUserPermissions($id, $permissions);

            return redirect()->to('/user-management')->with('success', 'User updated successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update user. Please try again.');
        }
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        if (!$this->permissionService->hasPermission($this->getCurrentUser()->id, 'users', 'delete')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to delete users.');
        }

        $user = $this->findScopedUser($id);
        
        if (!$user) {
            return redirect()->to('/user-management')->with('error', 'User not found or access denied.');
        }

        if ($this->permissionService->isAdmin($id)) {
            return redirect()->to('/user-management')->with('error', 'Cannot delete super admin user.');
        }

        $success = $this->userModel->delete($id);

        if ($success) {
            return redirect()->to('/user-management')->with('success', 'User deleted successfully.');
        } else {
            return redirect()->to('/user-management')->with('error', 'Failed to delete user.');
        }
    }

    /**
     * Show user details
     */
    public function show($id)
    {
        if (!$this->permissionService->hasPermission($this->getCurrentUser()->id, 'users', 'view')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to view users.');
        }

        $user = $this->findScopedUser($id);
        
        if (!$user) {
            return redirect()->to('/user-management')->with('error', 'User not found or access denied.');
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
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        if (!$this->permissionService->hasPermission($this->getCurrentUser()->id, 'users', 'edit')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to edit users.');
        }

        $user = $this->findScopedUser($id);
        
        if (!$user) {
            return redirect()->to('/user-management')->with('error', 'User not found or access denied.');
        }

        if ($this->permissionService->isAdmin($id)) {
            return redirect()->to('/user-management')->with('error', 'Cannot deactivate super admin user.');
        }

        $newStatus = $user['active'] ? 0 : 1;
        
        $success = $this->userModel->update($id, ['active' => $newStatus]);

        if ($success) {
            $status = $newStatus ? 'activated' : 'deactivated';
            return redirect()->to('/user-management')->with('success', "User {$status} successfully.");
        } else {
            return redirect()->to('/user-management')->with('error', 'Failed to update user status.');
        }
    }

    /**
     * Get users with their roles
     */
    protected function getUsersWithRoles()
    {
        $clinicId = session()->get('active_clinic_id');
        
        if ($clinicId) {
            $users = $this->userModel->getUsersByClinic($clinicId);
        } else {
            $users = $this->userModel->findAll();
        }
        
        foreach ($users as &$user) {
            $user['roles'] = $this->userRoleModel->getUserRolesWithDetails($user['id']);
            $user['role_count'] = count($user['roles']);
        }

        return $users;
    }

    // AJAX Methods (No duplicates needed)

    public function assignRole()
    {
        if (!$this->canManageUsers()) { 
            return $this->response->setJSON(['error' => 'Permission denied']);
        }
        $userId = $this->request->getPost('user_id');
        $roleId = $this->request->getPost('role_id');
        $expiresAt = $this->request->getPost('expires_at');
        $success = $this->userRoleModel->assignRole($userId, $roleId, $this->getCurrentUser()->id, $expiresAt);
        return $this->response->setJSON($success ? ['success' => true, 'message' => 'Role assigned successfully'] : ['error' => 'Failed to assign role']);
    }

    public function removeRole()
    {
        if (!$this->canManageUsers()) {
            return $this->response->setJSON(['error' => 'Permission denied']);
        }
        $userId = $this->request->getPost('user_id');
        $roleId = $this->request->getPost('role_id');
        $success = $this->userRoleModel->removeRole($userId, $roleId);
        return $this->response->setJSON($success ? ['success' => true, 'message' => 'Role removed successfully'] : ['error' => 'Failed to remove role']);
    }

    public function grantPermission()
    {
        if (!$this->canManageUsers()) {
            return $this->response->setJSON(['error' => 'Permission denied']);
        }
        $userId = $this->request->getPost('user_id');
        $permissionId = $this->request->getPost('permission_id');
        $expiresAt = $this->request->getPost('expires_at');
        $reason = $this->request->getPost('reason');
        $success = $this->permissionService->grantUserPermission($userId, $permissionId, $this->getCurrentUser()->id, $expiresAt, $reason);
        return $this->response->setJSON($success ? ['success' => true, 'message' => 'Permission granted successfully'] : ['error' => 'Failed to grant permission']);
    }

    public function revokePermission()
    {
        if (!$this->canManageUsers()) {
            return $this->response->setJSON(['error' => 'Permission denied']);
        }
        $userId = $this->request->getPost('user_id');
        $permissionId = $this->request->getPost('permission_id');
        $reason = $this->request->getPost('reason');
        $success = $this->permissionService->revokeUserPermission($userId, $permissionId, $this->getCurrentUser()->id, $reason);
        return $this->response->setJSON($success ? ['success' => true, 'message' => 'Permission revoked successfully'] : ['error' => 'Failed to revoke permission']);
    }

    public function getUserPermissions($userId = null)
    {
        if (!$this->permissionService->hasPermission($this->getCurrentUser()->id, 'users', 'view')) {
            return $this->response->setJSON(['error' => 'Permission denied']);
        }
        $permissions = $this->permissionService->getUserPermissions($userId);
        return $this->response->setJSON(['success' => true, 'permissions' => $permissions]);
    }

    protected function updateUserPermissions($userId, $permissions)
    {
        $currentPermissions = $this->permissionService->getUserSpecificPermissions($userId);
        $currentPermissionIds = [];
        foreach ($currentPermissions as $modulePermissions) {
            foreach ($modulePermissions as $permission) {
                $currentPermissionIds[] = $permission['id'];
            }
        }
        foreach ($currentPermissionIds as $permissionId) {
            if (!in_array($permissionId, $permissions)) {
                $this->permissionService->revokeUserPermission($userId, $permissionId, $this->getCurrentUser()->id, 'Updated via user management');
            }
        }
        foreach ($permissions as $permissionId) {
            if (!in_array($permissionId, $currentPermissionIds)) {
                $this->permissionService->grantUserPermission($userId, $permissionId, $this->getCurrentUser()->id, null, 'Updated via user management');
            }
        }
    }
}
