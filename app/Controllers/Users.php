<?php

namespace App\Controllers;

use App\Models\IonAuthModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\PermissionModel;
use App\Models\ProviderPayoutModel;
use App\Models\ProviderPayoutPaymentModel;
use App\Models\ClinicUserModel;
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
    protected $clinicUserModel;
    protected $providerPayoutModel;
    protected $providerPayoutPaymentModel;

    public function __construct()
    {
        $this->ionAuthModel = new IonAuthModel();
        $this->ionAuth = new IonAuth();
        $this->roleModel = new RoleModel();
        $this->userRoleModel = new UserRoleModel();
        $this->permissionModel = new PermissionModel();
        $this->permissionService = new PermissionService();
        $this->clinicUserModel = new ClinicUserModel();
        $this->providerPayoutModel = new ProviderPayoutModel();
        $this->providerPayoutPaymentModel = new ProviderPayoutPaymentModel();
    }

    /**
     * Return minimal account eligibility for authorized clinic user creation.
     *
     * Tenant authority is required; profiles, ids and foreign memberships are never returned.
     */
    public function checkEmail()
    {
        $currentClinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($currentClinicId <= 0 || session()->get('global_mode') === true) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $currentUser = $this->getCurrentUser();
        if (!$currentUser || !$this->permissionService->hasPermission($currentUser->id, 'users', 'create')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'PERMISSION_DENIED']);
        }

        $authority = (new \App\Services\ClinicMembershipService())->authorizeActor($currentClinicId, (int) $currentUser->id, 'create');
        if (!$authority['authorized']) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'PERMISSION_DENIED']);
        }

        $email = trim((string) ($this->request->getGet('email') ?? $this->request->getPost('email')));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['exists' => false, 'in_current_clinic' => false]);
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();
        if (!$user) {
            return $this->response->setJSON(['exists' => false, 'in_current_clinic' => false]);
        }

        $inCurrentClinic = false;
        $membership = $this->clinicUserModel->withoutTenantScope()
            ->where('clinic_id', $currentClinicId)
            ->where('user_id', $user['id'])
            ->where('status', 'active')
            ->first();
        if ($membership) {
            $inCurrentClinic = true;
        }

        return $this->response->setJSON([
            'exists' => true,
            'in_current_clinic' => $inCurrentClinic,
        ]);
    }

    public function index()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return redirect()->to('/clinic/select');
            }

            // Get users scoped to clinic
            $userModel = new \App\Models\UserModel();
            $usersArray = $userModel->getUsersByClinic($clinicId);
            
            // Convert to objects to match existing view expectation (if view expects objects)
            // or modify view. IonAuth returns objects. findAll returns arrays.
            // Let's cast to objects to be safe with existing view logic
            $users = array_map(function($user) {
                return (object)$user;
            }, $usersArray);
            
            // Get RBAC roles for each user strictly from clinic membership (no IonAuth fallback)
            foreach ($users as $user) {
                try {
                    $userRoles = $this->getUserRoles($user->id);
                    $user->roles = $userRoles;
                    $user->permissions = $this->getUserPermissions($user->id);
                    $user->display_roles = $userRoles;
                } catch (\Exception $e) {
                    $user->roles = [];
                    $user->permissions = [];
                    $user->display_roles = [];
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
                $clinicId = session()->get('active_clinic_id');
                $roles = $this->roleModel->getAssignableRolesForClinic($clinicId, true);
                $permissions = $this->permissionModel->getGroupedByModule();
                
                // Clinic-scoped counts for UI diagnostics (avoid leaking global directory totals)
                $diagnostics['total_permissions'] = count($permissions);
                $diagnostics['total_roles'] = count($roles);
                $diagnostics['total_user_roles'] = count(array_filter($users, function($u) {
                    return !empty($u->roles);
                }));
                $diagnostics['rbac_ready'] = !empty($roles);
            } catch (\Exception $e) {
                // RBAC not ready yet
                log_message('debug', 'RBAC not ready in Users index: ' . $e->getMessage());
            }

            $totalClinicMembers = count($users);
            $activeMembershipUsers = count(array_filter($users, function($user) {
                return ($user->membership_status ?? '') === 'active';
            }));
            $inactiveMembershipUsers = $totalClinicMembers - $activeMembershipUsers;
            $globallyActiveUsers = count(array_filter($users, function($user) {
                return (int) ($user->active ?? 0) === 1;
            }));
            $globallyDisabledUsers = $totalClinicMembers - $globallyActiveUsers;

            $data = array_merge([
                'title' => 'User Management',
                'users' => $users,
                'available_groups' => $availableGroups,
                'roles' => $roles,
                'permissions' => $permissions,
                'diagnostics' => $diagnostics,
                'total_users' => $totalClinicMembers,
                'active_users' => $activeMembershipUsers,
                'inactive_users' => $inactiveMembershipUsers,
                'globally_active_users' => $globallyActiveUsers,
                'globally_disabled_users' => $globallyDisabledUsers,
                'current_user' => $this->getCurrentUser(),
                'loadSelect2' => true,
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
                'loadSelect2' => true,
                'error' => 'Unable to load user data. Please check the database connection.'
            ], $this->getUserDataForView());

            return $this->view('users/index', $data);
        }
    }

    public function create()
    {
        // Get all available roles from roles table
        $clinicId = session()->get('active_clinic_id');
        $roleModel = new \App\Models\RoleModel();
        $rolesData = $roleModel->getAssignableRolesForClinic($clinicId, true);
        
        // Convert roles to format for the view
        $roles = [];
        foreach ($rolesData as $role) {
            $roles[$role['id']] = $role['name'];
        }
        
        $data = array_merge([
            'title' => 'Create New User',
            'roles' => $roles,
            'departments' => $this->getDepartments(),
            'loadSelect2' => true,
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['users']),
        ], $this->getUserDataForView());

        return $this->view('users/create', $data);
    }

    public function store()
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return redirect()->to('/clinic/select');
        }

        $currentUser = $this->getCurrentUser();
        if (!$currentUser || !$this->permissionService->hasPermission($currentUser->id, 'users', 'create')) {
            return redirect()->back()->withInput()->with('error', 'You do not have permission to create users.');
        }

        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
            'role' => 'required|integer',
            'phone' => 'permit_empty|min_length[10]|max_length[20]',
            'department' => 'permit_empty|max_length[100]',
            'hire_date' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $roleId = (int) $this->request->getPost('role');
        $userData = [
            'first_name' => (string) $this->request->getPost('first_name'),
            'last_name' => (string) $this->request->getPost('last_name'),
            'email' => (string) $this->request->getPost('email'),
            'password' => (string) $this->request->getPost('password'),
            'phone' => (string) ($this->request->getPost('phone') ?: ''),
            'department' => (string) ($this->request->getPost('department') ?: ''),
            'hire_date' => (string) ($this->request->getPost('hire_date') ?: ''),
        ];

        $membershipService = new \App\Services\ClinicMembershipService();
        $result = $membershipService->createMemberWithIdentity($clinicId, $userData, $roleId, (int) $currentUser->id);

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }

        $returnTo = $this->workflowReturnTo(['users']) ?? 'users';
        return $this->redirectAfterWorkflow($returnTo, ['users'])->with('success', $result['message']);
    }

    public function show($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        // Use tenant-aware model fetch
        $userModel = new \App\Models\UserModel();
        $userArray = $userModel->findByClinic($clinicId, $id);
        
        if (!$userArray) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }
        
        $user = (object)$userArray;

        // Get role strictly from clinic_users membership
        $membership = $this->clinicUserModel->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->where('user_id', $id)
            ->first();
        $membershipStatus = $membership['status'] ?? ($user->membership_status ?? 'inactive');
        $user->membership_status = $membershipStatus;
        $user->is_globally_disabled = (int) ($user->active ?? 0) !== 1;
        $currentRoleId = !empty($membership['role_id']) ? (int) $membership['role_id'] : 0;
        $userRole = $currentRoleId > 0 ? $this->roleModel->find($currentRoleId) : null;
        $user->display_roles = $userRole ? [$userRole] : [];

        $assignableRoles = $this->roleModel->getAssignableRolesForClinic($clinicId, true);

        $currentUserData = $this->getUserDataForView();
        $currentLoggedInUser = $currentUserData['user'] ?? null;
        $isSelf = (int) $id === (int) ($currentLoggedInUser->id ?? 0);

        $data = [
            'title' => 'User Details',
            'viewed_user' => $user,
            'viewed_user_roles' => $user->display_roles,
            'assignable_roles' => $assignableRoles,
            'departments' => $this->getDepartments(),
            'current_role_id' => $currentRoleId,
            'is_self' => $isSelf,
            'membership_status' => $membershipStatus,
            'is_globally_disabled' => $user->is_globally_disabled,
            'provider_finance_summary' => $this->providerPayoutModel->getSummaryByProviderUserId((int) $clinicId, (int) $id),
            'provider_payout_rows' => $this->providerPayoutModel->getRowsByProviderUserId((int) $clinicId, (int) $id),
            'provider_payment_rows' => $this->providerPayoutPaymentModel->getRowsByProviderUserId((int) $clinicId, (int) $id),
            'loadSelect2' => true,
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['users', 'finance']),
        ];
        
        // Add current user data for view without overwriting the user being viewed
        $data['current_user'] = $currentLoggedInUser;
        $data['user_groups'] = $currentUserData['user_groups'];

        return $this->view('users/show', $data);
    }

    public function edit($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $userModel = new \App\Models\UserModel();
        $userArray = $userModel->findByClinic($clinicId, $id);
        
        if (!$userArray) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }
        
        $user = (object)$userArray;

        // Get user role strictly from clinic_users
        $membership = $this->clinicUserModel->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->where('user_id', $id)
            ->first();
        $membershipStatus = $membership['status'] ?? ($user->membership_status ?? 'inactive');
        $user->membership_status = $membershipStatus;
        $user->is_globally_disabled = (int) ($user->active ?? 0) !== 1;
        $currentRoleId = !empty($membership['role_id']) ? (int) $membership['role_id'] : 0;
        $userRole = $currentRoleId > 0 ? $this->roleModel->find($currentRoleId) : null;
        $user->roles = $userRole ? [['role_id' => $currentRoleId, 'role_name' => $userRole['name'], 'role_slug' => $userRole['slug']]] : [];
        $user->current_role_id = $currentRoleId;
        
        // Get all available roles from roles table
        $clinicId = session()->get('active_clinic_id');
        $roleModel = new \App\Models\RoleModel();
        $rolesData = $roleModel->getAssignableRolesForClinic($clinicId, true);
        
        // Convert roles to format for the view
        $roles = [];
        foreach ($rolesData as $role) {
            $roles[$role['id']] = $role['name'];
        }

        $currentUser = $this->getCurrentUser();
        $isSelf = (int) $id === (int) ($currentUser->id ?? 0);

        // Prepare data for the view
        $data = [
            'title' => 'Edit User',
            'user' => $user, // The user being edited
            'roles' => $roles,
            'departments' => $this->getDepartments(),
            'is_self' => $isSelf,
            'membership_status' => $membershipStatus,
            'is_globally_disabled' => $user->is_globally_disabled,
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['users']),
        ];
        
        // Add current user data without conflicts
        $data['current_user'] = $currentUser;
        $data['user_groups'] = $this->getUserGroups();

        return view('users/edit', $data);
    }

    public function update($id)
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return redirect()->to('/clinic/select');
        }
        // This endpoint owns one clinic-role intent. Global identity belongs to Profile/recovery.
        foreach (['first_name', 'last_name', 'email', 'phone', 'password', 'password_confirm', 'new_password', 'active', 'status'] as $field) {
            if ($this->request->getPost($field) !== null) {
                return redirect()->back()->with('error', lang('UserRole.identityManagedByOwner'));
            }
        }
        if (!$this->validate(['role' => 'required|is_natural_no_zero'])) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }
        $membershipService = new \App\Services\ClinicMembershipService();
        $result = $membershipService->replaceRole(
            $clinicId, (int) $id, (int) $this->request->getPost('role'), (int) ($this->getCurrentUser()->id ?? 0)
        );
        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }
        return $this->redirectAfterWorkflow('users/' . (int) $id, ['users'])->with('success', $result['message']);
    }

    public function delete($id)
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }
            return redirect()->to('/clinic/select');
        }

        $currentUserId = (int) ($this->getCurrentUser()->id ?? 0);
        $membershipService = new \App\Services\ClinicMembershipService();
        $result = $membershipService->removeMembership($clinicId, (int) $id, $currentUserId);

        if ($this->request->isAJAX()) {
            $status = $result['success'] ? 200 : 422;
            return $this->response->setStatusCode($status)->setJSON([
                'success' => $result['success'],
                'message' => $result['message'],
            ]);
        }

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->to(base_url('users'))->with('success', $result['message']);
    }

    public function changePassword($id)
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return redirect()->to('/clinic/select');
        }

        $currentUserId = (int) ($this->getCurrentUser()->id ?? 0);
        if ((int) $id !== $currentUserId) {
            return redirect()->back()->with('error', lang('UserRole.cannotResetOtherUserPassword'));
        }

        $userModel = new \App\Models\UserModel();
        $userArray = $userModel->findByClinic($clinicId, $id);
        
        if (!$userArray) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }
        
        $user = (object) $userArray;

        $data = array_merge([
            'title' => 'Change Password',
            'user' => $user,
            'return_to' => $this->sanitizeInternalReturnTo($this->request->getGet('return_to'), ['users']),
        ], $this->getUserDataForView());

        return $this->view('users/change_password', $data);
    }

    public function updatePassword($id)
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return redirect()->to('/clinic/select');
        }

        $currentUserId = (int) ($this->getCurrentUser()->id ?? 0);
        if ((int) $id !== $currentUserId) {
            return redirect()->back()->with('error', lang('UserRole.cannotResetOtherUserPassword'));
        }

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]|max_length[255]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($this->ionAuth->changePassword((string) $this->getCurrentUser()->email, $this->request->getPost('current_password'), $this->request->getPost('new_password'))) {
            return $this->redirectAfterWorkflow('users/' . (int) $id, ['users'])->with('success', 'Password updated successfully!');
        } else {
            $errors = $this->ionAuth->errors();
            $errorMessage = is_array($errors) ? implode(', ', $errors) : ($errors ?: 'Failed to update password. Please try again.');
            return redirect()->back()->withInput()->with('error', $errorMessage);
        }
    }

    public function toggleStatus($id)
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $membership = $this->clinicUserModel->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->where('user_id', $id)
            ->first();

        if (!$membership) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'code' => 'membership_not_found', 'message' => lang('UserRole.membershipOperationDenied')]);
        }

        $currentStatus = $membership['status'] ?? 'inactive';
        $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';

        $currentUserId = (int) ($this->getCurrentUser()->id ?? 0);
        $membershipService = new \App\Services\ClinicMembershipService();
        $result = $membershipService->updateStatus($clinicId, (int) $id, $newStatus, $currentUserId);

        if (!$result['success']) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $result['message'],
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => lang('UserRole.membershipSaved'),
            'new_status' => $newStatus,
        ]);
    }

    public function inlineUpdate($id)
    {
        return $this->response->setStatusCode(403)->setJSON([
            'success' => false, 'code' => 'identity_owner_required',
            'message' => lang('UserRole.identityManagedByOwner'),
        ]);
    }

    public function inlineUpdateRole($id)
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $roleId = (int) $this->request->getPost('role_id');
        if ($roleId <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid role selected.']);
        }

        $currentUserId = (int) ($this->getCurrentUser()->id ?? 0);
        $membershipService = new \App\Services\ClinicMembershipService();
        $result = $membershipService->replaceRole($clinicId, (int) $id, $roleId, $currentUserId);

        if (!$result['success']) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $result['message'],
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Role updated successfully',
        ]);
    }

    public function inlineResetPassword($id)
    {
        return $this->response->setStatusCode(403)->setJSON([
            'success' => false,
            'message' => lang('UserRole.cannotResetOtherUserPassword'),
        ]);
    }

    public function getUserStats()
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        return $this->response->setJSON($this->clinicUserModel->getMembershipStatsByClinic($clinicId));
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
            $user = $this->isLoggedIn() ? $this->getCurrentUser() : null;
            return $user && $this->permissionService
                ? $this->permissionService->canManageUsers((int) $user->id) : false;
        } catch (\Throwable $e) {
            log_message('error', 'User management authority unavailable.');
            return false;
        }
    }

    /**
     * Get user roles strictly scoped to active clinic membership
     */
    public function getUserRoles($userId)
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return [];
        }

        try {
            $membership = $this->clinicUserModel->withoutTenantScope()
                ->where('clinic_id', $clinicId)
                ->where('user_id', $userId)
                ->first();

            if (!$membership || empty($membership['role_id'])) {
                return [];
            }

            $role = $this->roleModel->find((int) $membership['role_id']);
            return $role ? [$role] : [];
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
     * Assign role to user (AJAX) - delegates to ClinicMembershipService::replaceRole
     */
    public function assignRole()
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $userId = (int) $this->request->getPost('user_id');
        $roleId = (int) $this->request->getPost('role_id');
        $currentUserId = (int) ($this->getCurrentUser()->id ?? 0);

        $membershipService = new \App\Services\ClinicMembershipService();
        $result = $membershipService->replaceRole($clinicId, $userId, $roleId, $currentUserId);

        $status = $result['success'] ? 200 : 422;
        return $this->response->setStatusCode($status)->setJSON($result);
    }

    /**
     * Remove role from user (AJAX) - delegates to ClinicMembershipService::removeMembership
     */
    public function removeRole()
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $userId = (int) $this->request->getPost('user_id');
        $currentUserId = (int) ($this->getCurrentUser()->id ?? 0);

        $membershipService = new \App\Services\ClinicMembershipService();
        $result = $membershipService->removeMembership($clinicId, $userId, $currentUserId);

        $status = $result['success'] ? 200 : 422;
        return $this->response->setStatusCode($status)->setJSON($result);
    }

    /**
     * Direct permission overrides are denied for tenant users.
     */
    public function grantPermission()
    {
        return $this->response->setStatusCode(403)->setJSON([
            'success' => false,
            'message' => 'Direct permission overrides are denied for tenant users.',
        ]);
    }

    /**
     * Direct permission overrides are denied for tenant users.
     */
    public function revokePermission()
    {
        return $this->response->setStatusCode(403)->setJSON([
            'success' => false,
            'message' => 'Direct permission overrides are denied for tenant users.',
        ]);
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
            $userId = (int) $this->request->getPost('user_id');
        }
        
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if (!$this->clinicUserModel->isUserInClinic($clinicId, $userId)) {
             return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'User not found in this clinic']);
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
