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
            
            return $this->permissionService->canManageRoles($user->id);
        } catch (\Throwable $e) {
            log_message('error', 'canManageRoles error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ensure the request is coming from the Control Plane (Super Admin)
     */
    private function ensureControlPlane()
    {
        $session = session();
        $isSuperAdmin = false;
        
        try {
            $user = $this->getCurrentUser();
            if ($user) {
                $isSuperAdmin = $this->permissionService->isSuperAdmin($user->id);
            }
        } catch (\Throwable $e) {
            log_message('error', 'ensureControlPlane error: ' . $e->getMessage());
            $isSuperAdmin = false;
        }

        // Must be Super Admin AND in global_mode (or just Super Admin for strict enforcement)
        if (!$isSuperAdmin || $session->get('global_mode') !== true) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'success' => false, 
                    'message' => 'This action is restricted to system administrators in control plane mode.'
                ]);
            }
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        return null;
    }

    protected function getRolesRoute()
    {
        return session()->get('global_mode') ? 'controlplane/roles' : 'roles';
    }

    /**
     * Display roles list (System + Clinic Roles)
     */
    public function index()
    {
        // Check if user can manage roles
        if (!$this->canManageRoles()) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to manage roles.');
        }

        $clinicId = session()->get('active_clinic_id');
        $globalMode = session()->get('global_mode') === true;

        // Fetch roles based on context
        if ($globalMode) {
            // Global Mode: Show only System Roles
            $roles = $this->roleModel->where('clinic_id', null)
                                     ->orderBy('name', 'ASC')
                                     ->findAll();
        } else {
            // Tenant Mode: Show System Roles + Clinic Roles
            $roles = $this->roleModel->getSelectableRoles($clinicId); // Use getSelectableRoles to get both
            // Note: getSelectableRoles returns active roles only by default? No, let's check model.
            // RoleModel::getActiveRoles does where('is_active', 1).
            // We want ALL roles for management (even inactive).
            
            // Custom query to get all (active & inactive) for this clinic + global
            $builder = $this->roleModel->builder();
            $builder->groupStart()
                    ->where('clinic_id', null)
                    ->orWhere('clinic_id', $clinicId)
                    ->groupEnd();
            $roles = $builder->orderBy('name', 'ASC')->get()->getResultArray();
        }
        
        // Enhance each role with additional data
        foreach ($roles as &$role) {
            try {
                $role['user_count'] = $this->roleModel->getUserCount($role['id'], $clinicId);
                
                $rolePermissions = $this->roleModel->getPermissionsGrouped($role['id']);
                $role['permission_count'] = 0;
                foreach ($rolePermissions as $modulePermissions) {
                    $role['permission_count'] += count($modulePermissions);
                }
            } catch (\Exception $e) {
                $role['user_count'] = 0;
                $role['permission_count'] = 0;
            }
        }

        $data = [
            'title' => 'Role Management',
            'roles' => $roles,
            'permissions' => $this->permissionModel->getGroupedByModule(),
            'categories' => \App\Config\Permissions::getCategories(),
            'global_mode' => $globalMode,
            'rolesRoute' => $this->getRolesRoute(),
            'diagnostics' => [
                'total_permissions' => $this->permissionModel->countAll(),
                'total_user_roles' => $this->userRoleModel->countAll(),
                'rbac_ready' => true
            ]
        ];

        return $this->view('roles/index', $data);
    }

    /**
     * Show role creation form
     */
    public function create()
    {
        // Permission check
        if (!$this->canManageRoles()) {
            return redirect()->to('/dashboard')->with('error', 'Permission denied.');
        }

        $data = [
            'title' => 'Create New Role',
            'permissions' => $this->permissionModel->getGroupedByModule(),
            'categories' => \App\Config\Permissions::getCategories(),
            'action_descriptions' => \App\Config\Permissions::getActionDescriptions(),
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['roles', 'controlplane/roles']),
        ];
        $data['rolesRoute'] = $this->getRolesRoute();

        return $this->view('roles/create', $data);
    }

    /**
     * Store new role
     */
    public function store()
    {
        if (!$this->canManageRoles()) {
            return redirect()->to('/dashboard')->with('error', 'Permission denied.');
        }

        $globalMode = session()->get('global_mode') === true;
        $clinicId = session()->get('active_clinic_id');

        // Validation (System roles need unique name globally, Clinic roles unique per clinic)
        // For simplicity, we use the model's slugExists check
        $slug = $this->request->getPost('slug');
        $targetClinicId = $globalMode ? null : $clinicId;

        if ($this->roleModel->slugExists($slug, $targetClinicId)) {
            return redirect()->back()->withInput()->with('error', 'Role slug already exists in this scope.');
        }

        $rules = [
            'name' => 'required|max_length[100]',
            'slug' => 'required|max_length[100]',
            'description' => 'permit_empty',
            'permissions' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $roleData = [
            'name' => $this->request->getPost('name'),
            'slug' => $slug,
            'description' => $this->request->getPost('description'),
            'is_active' => $this->request->getPost('is_active') ?? 1,
            'is_system' => 0, // Always 0 for UI created roles? Or 1 if Global?
            // If Global Mode, we might want to create System Roles.
            // Let's assume Global Mode = System Role.
            'is_system' => $globalMode ? 0 : 0, // Wait, existing code said is_system=0. System roles usually seeded.
            // But if Super Admin creates a role, is it "System"? 
            // The previous code set is_system=0. Let's stick to that unless it's a seed.
            'is_medical' => $this->request->getPost('is_medical') ?? 0,
            'clinic_id' => $targetClinicId,
            'created_by' => $this->getCurrentUser()->id
        ];

        $permissions = $this->request->getPost('permissions') ?? [];

        $roleId = $this->roleModel->createWithPermissions($roleData, $permissions);

        if ($roleId) {
            return $this->redirectAfterWorkflow($this->getRolesRoute(), ['roles', 'controlplane/roles'])
                           ->with('success', 'Role created successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to create role.');
        }
    }

    /**
     * Show role edit form
     */
    public function edit($id)
    {
        if (!$this->canManageRoles()) {
            return redirect()->to('/dashboard')->with('error', 'Permission denied.');
        }

        $role = $this->roleModel->getWithPermissions($id);
        
        if (!$role) {
            return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Role not found.');
        }

        // Access Control:
        // 1. Global Admin can edit Global Roles (clinic_id=null) AND Clinic Roles? (Maybe, but let's restrict to global for now to keep it clean, or allow everything)
        // 2. Clinic Admin can ONLY edit THEIR Clinic Roles (clinic_id=active_clinic_id).
        
        $globalMode = session()->get('global_mode') === true;
        $clinicId = session()->get('active_clinic_id');

        if ($globalMode) {
            // Global Admin in Control Plane
            // Should probably only edit Global Roles or access specific clinic to edit their roles.
            if ($role['clinic_id'] !== null) {
                 return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Cannot edit clinic-specific role from Control Plane.');
            }
        } else {
            // Clinic Admin
            // CANNOT edit Global Roles (clinic_id=null)
            if ($role['clinic_id'] === null) {
                return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Cannot edit System Roles.');
            }
            // MUST own the role
            if ($role['clinic_id'] != $clinicId) {
                return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Access denied to this role.');
            }
        }

        // Prevent editing Super Admin
        if ($role['slug'] === 'super_admin') {
            return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Cannot edit the Super Admin role.');
        }

        $data = [
            'title' => 'Edit Role: ' . $role['name'],
            'role' => $role,
            'permissions' => $this->permissionModel->getGroupedByModule(),
            'categories' => \App\Config\Permissions::getCategories(),
            'action_descriptions' => \App\Config\Permissions::getActionDescriptions(),
            'role_permissions' => array_column($role['permissions'], 'id'),
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['roles', 'controlplane/roles']),
        ];
        $data['rolesRoute'] = $this->getRolesRoute();

        return $this->view('roles/edit', $data);
    }

    /**
     * Update role
     */
    public function update($id)
    {
        if (!$this->canManageRoles()) {
            return redirect()->to('/dashboard')->with('error', 'Permission denied.');
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
            return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Role not found.');
        }

        // Access Control (Same as edit)
        $globalMode = session()->get('global_mode') === true;
        $clinicId = session()->get('active_clinic_id');

        if ($globalMode) {
            if ($role['clinic_id'] !== null) return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Access denied.');
        } else {
            if ($role['clinic_id'] === null) return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Cannot edit System Roles.');
            if ($role['clinic_id'] != $clinicId) return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Access denied.');
        }

        if ($role['slug'] === 'super_admin') {
            return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Cannot modify the Super Admin role.');
        }

        // Validation
        $targetClinicId = $globalMode ? null : $clinicId;
        $slug = $this->request->getPost('slug');
        if ($this->roleModel->slugExists($slug, $targetClinicId, $id)) {
             return redirect()->back()->withInput()->with('error', 'Role slug already exists.');
        }

        $rules = [
            'name' => "required|max_length[100]",
            'slug' => "required|max_length[100]",
            'description' => 'permit_empty',
            'is_medical' => 'permit_empty|in_list[0,1]',
            'is_active' => 'permit_empty|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $roleData = [
            'name' => $this->request->getPost('name'),
            'slug' => $slug,
            'description' => $this->request->getPost('description'),
            'is_medical' => $this->request->getPost('is_medical') ?? 0,
            'is_active' => $this->request->getPost('is_active') ?? 1
        ];

        $permissions = $this->request->getPost('permissions') ?? [];

        $success = $this->roleModel->updateWithPermissions($id, $roleData, $permissions);

        if ($success) {
            return $this->redirectAfterWorkflow($this->getRolesRoute(), ['roles', 'controlplane/roles'])
                           ->with('success', 'Role updated successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update role.');
        }
    }

    /**
     * Delete role
     */
    public function delete($id)
    {
        if (!$this->canManageRoles()) {
             if ($this->request->isAJAX()) return $this->response->setJSON(['success' => false, 'message' => 'Permission denied']);
             return redirect()->to('/dashboard')->with('error', 'Permission denied');
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
             if ($this->request->isAJAX()) return $this->response->setJSON(['success' => false, 'message' => 'Not found']);
             return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Not found');
        }

        // Access Control
        $globalMode = session()->get('global_mode') === true;
        $clinicId = session()->get('active_clinic_id');

        if ($globalMode) {
            if ($role['clinic_id'] !== null) {
                if ($this->request->isAJAX()) return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
                return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Access denied');
            }
        } else {
            if ($role['clinic_id'] === null) {
                if ($this->request->isAJAX()) return $this->response->setJSON(['success' => false, 'message' => 'Cannot delete System Roles']);
                return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Cannot delete System Roles');
            }
            if ($role['clinic_id'] != $clinicId) {
                if ($this->request->isAJAX()) return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
                return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Access denied');
            }
        }

        if ($role['slug'] === 'super_admin') {
             if ($this->request->isAJAX()) return $this->response->setJSON(['success' => false, 'message' => 'Cannot delete Super Admin']);
             return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Cannot delete Super Admin');
        }

        $success = $this->roleModel->deleteRole($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => $success, 'message' => $success ? 'Deleted' : 'Failed to delete']);
        }

        if ($success) {
            return redirect()->to('/' . $this->getRolesRoute())->with('success', 'Role deleted.');
        } else {
            return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Failed to delete role (may have users assigned).');
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
        
        if (!$role || $role['clinic_id'] !== null) {
            return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Role not found.');
        }

        $clinicId = session()->get('active_clinic_id');

        $data = [
            'title' => 'Role Details: ' . $role['name'],
            'role' => $role,
            'users' => $this->roleModel->getUsers($id, $clinicId),
            'permissions_grouped' => $this->roleModel->getPermissionsGrouped($id)
        ];

        return $this->view('roles/show', $data);
    }

    /**
     * Sync permissions from config
     */
    public function sync()
    { 
        if ($response = $this->ensureControlPlane()) return $response;

        try {
            $result = $this->syncService->fullSync();
            $message = "Successfully synced {$result['permissions_synced']} permissions and {$result['roles_synced']} roles.";
            
            if (isset($result['admin_assigned']) && $result['admin_assigned'] > 0) {
                $message .= " Assigned {$result['admin_assigned']} admin user(s) to Super Admin role.";
            }
            
            return redirect()->to('/' . $this->getRolesRoute())->with('success', $message);
        } catch (\Exception $e) {
            log_message('error', 'Permission sync failed: ' . $e->getMessage());
            return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Failed to sync permissions: ' . $e->getMessage());
        }
    }

    /**
     * Get role permissions (AJAX)
     */
    public function getPermissions($id)
    {
        // View-only permissions fetch is okay for tenant admins to see what roles do
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
        // Statistics view is okay for tenant admins
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
        if ($response = $this->ensureControlPlane()) return $response;

        $role = $this->roleModel->find($id);
        
        if (!$role || $role['clinic_id'] !== null) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Role not found.']);
            }
            return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Role not found.');
        }

        // Prevent toggling Super Admin role status
        if ($role['slug'] === 'super_admin') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Cannot modify the Super Admin role status.']);
            }
            return redirect()->to('/' . $this->getRolesRoute())->with('error', 'Cannot modify the Super Admin role status.');
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
            return redirect()->to('/' . $this->getRolesRoute())
                           ->with('success', "Role {$status} successfully.");
        } else {
            return redirect()->to('/' . $this->getRolesRoute())
                           ->with('error', 'Failed to update role status.');
        }
    }
}
