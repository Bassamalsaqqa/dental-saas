<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Services\PermissionService;
use CodeIgniter\Exceptions\PageNotFoundException;

class ClinicRolesController extends BaseController
{
    protected $roleModel;
    protected $permissionModel;
    protected $permissionService;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
        $this->permissionService = new PermissionService();
    }

    private function getClinicId(): int
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            throw PageNotFoundException::forPageNotFound();
        }
        return (int) $clinicId;
    }

    private function ensureCanManageRoles()
    {
        $user = $this->getCurrentUser();
        if (!$user || !$this->permissionService->hasPermission($user->id, 'users', 'roles')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to manage clinic roles.');
        }
        return null;
    }

    /**
     * Show clinic roles list
     */
    public function index()
    {
        if ($response = $this->ensureCanManageRoles()) {
            return $response;
        }

        $clinicId = $this->getClinicId();
        $roles = $this->roleModel->getClinicRoles($clinicId, false);

        foreach ($roles as &$role) {
            $grouped = $this->roleModel->getPermissionsGrouped($role['id']);
            $role['permission_count'] = array_sum(array_map('count', $grouped));
        }

        return $this->view('clinic_roles/index', [
            'title' => 'Clinic Roles',
            'roles' => $roles
        ]);
    }

    /**
     * Show form to create clinic-specific role
     */
    public function create()
    {
        if ($response = $this->ensureCanManageRoles()) {
            return $response;
        }

        return $this->view('clinic_roles/form', [
            'title' => 'Create Clinic Role',
            'action' => base_url('clinic/roles/store'),
            'method' => 'post',
            'role' => null,
            'submitLabel' => 'Create Role',
            'permissions' => $this->permissionModel->getGroupedByModule(),
            'categories' => \App\Config\Permissions::getCategories(),
            'selectedPermissions' => []
        ]);
    }

    /**
     * Store clinic role
     */
    public function store()
    {
        if ($response = $this->ensureCanManageRoles()) {
            return $response;
        }

        $clinicId = $this->getClinicId();

        $rules = [
            'name' => 'required|max_length[100]',
            'slug' => 'required|max_length[100]',
            'description' => 'permit_empty',
            'is_medical' => 'permit_empty|in_list[0,1]',
            'permissions' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('validation', $this->validator);
        }

        $slug = $this->request->getPost('slug');
        if ($this->roleModel->slugExists($slug, $clinicId)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'A role with this slug already exists in the clinic.');
        }

        $roleData = [
            'name' => $this->request->getPost('name'),
            'slug' => $slug,
            'description' => $this->request->getPost('description'),
            'is_active' => 1,
            'is_system' => 0,
            'is_medical' => $this->request->getPost('is_medical') ?? 0,
            'clinic_id' => $clinicId,
            'created_by' => $this->getCurrentUser()->id
        ];

        $permissions = $this->request->getPost('permissions') ?? [];
        $roleId = $this->roleModel->createWithPermissions($roleData, $permissions);

        if ($roleId) {
            return redirect()->to('/clinic/roles')->with('success', 'Clinic role created successfully.');
        }

        return redirect()->back()
                       ->withInput()
                       ->with('error', 'Unable to create clinic role. Please try again.');
    }

    /**
     * Show clinic role edit form
     */
    public function edit($id)
    {
        if ($response = $this->ensureCanManageRoles()) {
            return $response;
        }

        $clinicId = $this->getClinicId();
        $role = $this->roleModel->getWithPermissions($id);

        if (!$role || (int) $role['clinic_id'] !== $clinicId) {
            return redirect()->to('/clinic/roles')->with('error', 'Clinic role not found.');
        }

        $rolePermissions = array_column($role['permissions'], 'id');

        return $this->view('clinic_roles/form', [
            'title' => 'Edit Clinic Role',
            'action' => base_url('clinic/roles/' . $id . '/update'),
            'method' => 'post',
            'role' => $role,
            'submitLabel' => 'Update Role',
            'permissions' => $this->permissionModel->getGroupedByModule(),
            'categories' => \App\Config\Permissions::getCategories(),
            'selectedPermissions' => $rolePermissions
        ]);
    }

    /**
     * Update clinic role
     */
    public function update($id)
    {
        if ($response = $this->ensureCanManageRoles()) {
            return $response;
        }

        $clinicId = $this->getClinicId();
        $role = $this->roleModel->find($id);

        if (!$role || (int) $role['clinic_id'] !== $clinicId) {
            return redirect()->to('/clinic/roles')->with('error', 'Clinic role not found.');
        }

        $rules = [
            'name' => 'required|max_length[100]',
            'slug' => "required|max_length[100]",
            'description' => 'permit_empty',
            'is_medical' => 'permit_empty|in_list[0,1]',
            'permissions' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('validation', $this->validator);
        }

        $slug = $this->request->getPost('slug');
        if ($this->roleModel->slugExists($slug, $clinicId, $id)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'A role with this slug already exists in the clinic.');
        }

        $roleData = [
            'name' => $this->request->getPost('name'),
            'slug' => $slug,
            'description' => $this->request->getPost('description'),
            'is_medical' => $this->request->getPost('is_medical') ?? 0,
            'clinic_id' => $clinicId
        ];

        $permissions = $this->request->getPost('permissions') ?? [];
        $success = $this->roleModel->updateWithPermissions($id, $roleData, $permissions);

        if ($success) {
            return redirect()->to('/clinic/roles')->with('success', 'Clinic role updated successfully.');
        }

        return redirect()->back()
                       ->withInput()
                       ->with('error', 'Unable to update clinic role.');
    }

    /**
     * Delete clinic role
     */
    public function delete($id)
    {
        if ($response = $this->ensureCanManageRoles()) {
            return $response;
        }

        $clinicId = $this->getClinicId();
        $role = $this->roleModel->find($id);

        if (!$role || (int) $role['clinic_id'] !== $clinicId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Clinic role not found.']);
        }

        $success = $this->roleModel->deleteRole($id);

        if ($success) {
            return $this->response->setJSON(['success' => true, 'message' => 'Role deleted successfully.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Cannot delete role. It may be in use.']);
    }

    /**
     * Toggle clinic role status
     */
    public function toggleStatus($id)
    {
        if ($response = $this->ensureCanManageRoles()) {
            return $response;
        }

        $clinicId = $this->getClinicId();
        $role = $this->roleModel->find($id);

        if (!$role || (int) $role['clinic_id'] !== $clinicId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Clinic role not found.']);
        }

        $newStatus = $role['is_active'] ? 0 : 1;
        $this->roleModel->update($id, ['is_active' => $newStatus]);

        return $this->response->setJSON(['success' => true, 'message' => 'Role status updated.']);
    }
}
