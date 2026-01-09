<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Models\UserRoleModel;
use App\Services\PermissionService;
use App\Services\PermissionSyncService;
use App\Libraries\IonAuth;

class RoleController extends BaseController
{
    protected $roleModel;
    protected $permissionModel;
    protected $userRoleModel;
    protected $permissionService;
    protected $syncService;
    protected $ionAuth;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
        $this->userRoleModel = new UserRoleModel();
        $this->permissionService = new PermissionService();
        $this->syncService = new PermissionSyncService();
        $this->ionAuth = new IonAuth(); 
    }

    /**
     * Check if user can manage roles (with RBAC fallback to IonAuth)
     */
    private function canManageRoles()
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
            return $this->permissionService->canManageRoles($user->id);
        } catch (\Exception $e) {
            // Fallback to IonAuth during setup
            return $this->ionAuth->isAdmin();
        }
    }

    /**
     * Display roles list
     */
    public function index()
    {
        // Check if user can manage roles (fallback to IonAuth during setup)
        if (!$this->canManageRoles()) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to manage roles.');
        }

        // Get all roles (active and inactive)
        $roles = $this->roleModel->findAll();
        
        // Enhance each role with additional data
        foreach ($roles as &$role) {
            try {
                // Get user count for this role
                $role['user_count'] = $this->roleModel->getUserCount($role['id']);
                
                // Get permission count for this role
                $rolePermissions = $this->roleModel->getPermissionsGrouped($role['id']);
                $role['permission_count'] = 0;
                foreach ($rolePermissions as $modulePermissions) {
                    $role['permission_count'] += count($modulePermissions);
                }
                
                // Debug logging
                log_message('debug', "Role {$role['name']} (ID: {$role['id']}): {$role['user_count']} users, {$role['permission_count']} permissions");
                
            } catch (\Exception $e) {
                // Fallback values if there's an error
                $role['user_count'] = 0;
                $role['permission_count'] = 0;
                log_message('error', "Error getting data for role {$role['id']}: " . $e->getMessage());
            }
        }

        // Get diagnostic information
        $totalPermissions = $this->permissionModel->countAll();
        $totalUserRoles = $this->userRoleModel->countAll();
        
        $data = [
            'title' => 'Role Management',
            'roles' => $roles,
            'permissions' => $this->permissionModel->getGroupedByModule(),
            'categories' => \App\Config\Permissions::getCategories(),
            'diagnostics' => [
                'total_permissions' => $totalPermissions,
                'total_user_roles' => $totalUserRoles,
                'rbac_ready' => $totalPermissions > 0
            ]
        ];

        return $this->view('roles/index', $data);
    }

    /**
     * Show role creation form
     */
    public function create()
    {
        // Check if user can manage roles
        if (!$this->canManageRoles()) { 
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to manage roles.');
        }

        $data = [
            'title' => 'Create New Role',
            'permissions' => $this->permissionModel->getGroupedByModule(),
            'categories' => \App\Config\Permissions::getCategories(),
            'action_descriptions' => \App\Config\Permissions::getActionDescriptions()
        ];

        return $this->view('roles/create', $data);
    }

    /**
     * Store new role
     */
    public function store()
    {
        // Check if user can manage roles
        if (!$this->canManageRoles()) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to manage roles.');
        }

        $rules = [
            'name' => 'required|max_length[100]|is_unique[roles.name]',
            'slug' => 'required|max_length[100]|is_unique[roles.slug]',
            'description' => 'permit_empty',
            'permissions' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('validation', $this->validator);
        }

        $roleData = [
            'name' => $this->request->getPost('name'),
            'slug' => $this->request->getPost('slug'),
            'description' => $this->request->getPost('description'),
            'is_active' => 1,
            'is_system' => 0,
            'is_medical' => $this->request->getPost('is_medical') ?? 0,
            'created_by' => $this->getCurrentUser()->id
        ];

        $permissions = $this->request->getPost('permissions') ?? [];

        $roleId = $this->roleModel->createWithPermissions($roleData, $permissions);

        if ($roleId) {
            return redirect()->to('/roles')
                           ->with('success', 'Role created successfully.');
        } else {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to create role. Please try again.');
        }
    }

    /**
     * Show role edit form
     */
    public function edit($id)
    {
        // Check if user can manage roles
        if (!$this->canManageRoles()) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to manage roles.');
        }

        $role = $this->roleModel->getWithPermissions($id);
        
        if (!$role) {
            return redirect()->to('/roles')->with('error', 'Role not found.');
        }

        // Prevent editing of Super Admin role
        if ($role['slug'] === 'super_admin') {
            return redirect()->to('/roles')->with('error', 'Cannot edit the Super Admin role.');
        }

        $data = [
            'title' => 'Edit Role: ' . $role['name'],
            'role' => $role,
            'permissions' => $this->permissionModel->getGroupedByModule(),
            'categories' => \App\Config\Permissions::getCategories(),
            'action_descriptions' => \App\Config\Permissions::getActionDescriptions(),
            'role_permissions' => array_column($role['permissions'], 'id')
        ];

        return $this->view('roles/edit', $data);
    }

    /**
     * Update role
     */
    public function update($id)
    {
        // Check if user can manage roles
        if (!$this->canManageRoles()) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to manage roles.');
        }

        $role = $this->roleModel->find($id);
        
        if (!$role) {
            return redirect()->to('/roles')->with('error', 'Role not found.');
        }

        // Prevent updating Super Admin role
        if ($role['slug'] === 'super_admin') {
            return redirect()->to('/roles')->with('error', 'Cannot modify the Super Admin role.');
        }

        $rules = [
            'name' => "required|max_length[100]|is_unique[roles.name,id,{$id}]",
            'slug' => "required|max_length[100]|is_unique[roles.slug,id,{$id}]",
            'description' => 'permit_empty',
            'is_medical' => 'permit_empty|in_list[0,1]',
            'permissions' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('validation', $this->validator);
        }

        $roleData = [
            'name' => $this->request->getPost('name'),
            'slug' => $this->request->getPost('slug'),
            'description' => $this->request->getPost('description'),
            'is_medical' => $this->request->getPost('is_medical') ?? 0
        ];

        $permissions = $this->request->getPost('permissions') ?? [];

        $success = $this->roleModel->updateWithPermissions($id, $roleData, $permissions);

        if ($success) {
            return redirect()->to('/roles')
                           ->with('success', 'Role updated successfully.');
        } else {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to update role. Please try again.');
        }
    }

    /**
     * Delete role
     */
    public function delete($id)
    {
        // Check if user can manage roles
        if (!$this->canManageRoles()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'You do not have permission to manage roles.']);
            }
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to manage roles.');
        }

        $role = $this->roleModel->find($id);
        
        if (!$role) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Role not found.']);
            }
            return redirect()->to('/roles')->with('error', 'Role not found.');
        }

        // Prevent deletion of Super Admin role
        if ($role['slug'] === 'super_admin') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Cannot delete the Super Admin role.']);
            }
            return redirect()->to('/roles')->with('error', 'Cannot delete the Super Admin role.');
        }

        $success = $this->roleModel->deleteRole($id);

        if ($this->request->isAJAX()) {
            if ($success) {
                return $this->response->setJSON(['success' => true, 'message' => 'Role deleted successfully.']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Cannot delete role. It may be a system role or has users assigned.']);
            }
        }

        if ($success) {
            return redirect()->to('/roles')
                           ->with('success', 'Role deleted successfully.');
        } else {
            return redirect()->to('/roles')
                           ->with('error', 'Cannot delete role. It may be a system role or has users assigned.');
        }
    }

    /**
     * Show role details
     */
    public function show($id)
    {
        // Check if user can manage roles
        if (!$this->canManageRoles()) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to manage roles.');
        }

        $role = $this->roleModel->getWithPermissions($id);
        
        if (!$role) {
            return redirect()->to('/roles')->with('error', 'Role not found.');
        }

        $data = [
            'title' => 'Role Details: ' . $role['name'],
            'role' => $role,
            'users' => $this->roleModel->getUsers($id),
            'permissions_grouped' => $this->roleModel->getPermissionsGrouped($id)
        ];

        return $this->view('roles/show', $data);
    }

    /**
     * Sync permissions from config
     */
    public function sync()
    { 
        // Check if user can manage roles
        if (!$this->canManageRoles()) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to manage roles.');
        }

        return redirect()->to('/rbac/sync');
    }

    /**
     * Get role permissions (AJAX)
     */
    public function getPermissions($id)
    {
        // Check if user can manage roles
        if (!$this->canManageRoles()) {
            return $this->response->setJSON(['error' => 'Permission denied']);
        }

        $role = $this->roleModel->getWithPermissions($id);
        
        if (!$role) {
            return $this->response->setJSON(['error' => 'Role not found']);
        }

        return $this->response->setJSON([
            'success' => true,
            'permissions' => array_column($role['permissions'], 'id')
        ]);
    }

    /**
     * Get role statistics
     */
    public function stats()
    {
        // Check if user can manage roles
        if (!$this->canManageRoles()) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to manage roles.');
        }

        $data = [
            'title' => 'Role Statistics',
            'role_stats' => $this->userRoleModel->getAssignmentStats(),
            'total_roles' => $this->roleModel->countAll(),
            'total_permissions' => $this->permissionModel->countAll(),
            'sync_status' => $this->syncService->getSyncStatus()
        ];

        return $this->view('roles/stats', $data);
    }

    /**
     * Toggle role status
     */
    public function toggleStatus($id)
    {
        // Check if user can manage roles
        if (!$this->canManageRoles()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'You do not have permission to manage roles.']);
            }
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to manage roles.');
        }

        $role = $this->roleModel->find($id);
        
        if (!$role) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Role not found.']);
            }
            return redirect()->to('/roles')->with('error', 'Role not found.');
        }

        // Prevent toggling Super Admin role status
        if ($role['slug'] === 'super_admin') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Cannot modify the Super Admin role status.']);
            }
            return redirect()->to('/roles')->with('error', 'Cannot modify the Super Admin role status.');
        }

        $newStatus = $role['is_active'] ? 0 : 1;
        
        $success = $this->roleModel->update($id, ['is_active' => $newStatus]);

        if ($this->request->isAJAX()) {
            if ($success) {
                $status = $newStatus ? 'activated' : 'deactivated';
                return $this->response->setJSON(['success' => true, 'message' => "Role {$status} successfully."]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to update role status.']);
            }
        }

        if ($success) {
            $status = $newStatus ? 'activated' : 'deactivated';
            return redirect()->to('/roles')
                           ->with('success', "Role {$status} successfully.");
        } else {
            return redirect()->to('/roles')
                           ->with('error', 'Failed to update role status.');
        }
    }
}
