<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\ClinicUserModel;
use App\Models\ClinicModel;
use App\Services\ControlPlaneAuditService;
use App\Services\ClinicAdminLifecycleService;
use App\Libraries\IonAuth;

class Users extends BaseController
{
    protected $auditService;
    protected $ionAuth;
    protected $roleModel;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
        $this->ionAuth = new IonAuth();
        $this->roleModel = new RoleModel();
    }

    /**
     * Control-plane queries on clinic_users must not require tenant session context.
     */
    private function clinicUserModel(): ClinicUserModel
    {
        return (new ClinicUserModel())->withoutTenantScope();
    }

    /**
     * GET/POST /controlplane/users
     */
    public function index()
    {
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/users',
        ]);

        $request = service('request');
        $isPost = strtoupper($this->request->getMethod()) === 'POST';
        $statusFilter = strtolower(trim((string) ($request ? ($isPost ? $request->getPost('status') : $request->getGet('status')) : '')));
        $search = trim((string) ($request ? ($isPost ? $request->getPost('q') : $request->getGet('q')) : ''));

        if ($isPost) {
            $this->auditService->logEvent('surface_get', [
                'route' => '/controlplane/users',
                'method' => 'POST',
            ]);

            $draw = (int) ($request ? $request->getPost('draw') : 0);
            $start = max(0, (int) ($request ? $request->getPost('start') : 0));
            $length = (int) ($request ? $request->getPost('length') : 10);

            $orderPayload = $request ? $request->getPost('order') : null;
            $columnsPayload = $request ? $request->getPost('columns') : null;
            $orderIndex = (int) ($orderPayload[0]['column'] ?? 0);
            $orderDir = strtolower((string) ($orderPayload[0]['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
            $orderColumn = is_array($columnsPayload) && isset($columnsPayload[$orderIndex]['data'])
                ? (string) $columnsPayload[$orderIndex]['data']
                : 'email'; // Default sort

            $userModel = new UserModel();
            $recordsTotal = $userModel->countAllResults();
            $rows = $this->buildUserRows($statusFilter, $search);

            // Client-side sorting of the built rows (simplest for now given complex joins)
            if ($orderColumn !== '') {
                usort($rows, function ($a, $b) use ($orderColumn, $orderDir) {
                    $aVal = $a[$orderColumn] ?? '';
                    $bVal = $b[$orderColumn] ?? '';

                    if ($orderColumn === 'clinic_count') {
                        $aVal = (int) $aVal;
                        $bVal = (int) $bVal;
                    }

                    if ($aVal === $bVal) {
                        return 0;
                    }

                    $result = $aVal < $bVal ? -1 : 1;
                    return $orderDir === 'asc' ? $result : -$result;
                });
            }

            $recordsFiltered = count($rows);
            $pageRows = $length > 0 ? array_slice($rows, $start, $length) : $rows;

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $pageRows,
                'csrf_token' => csrf_hash(),
            ]);
        }

        $userModel = new UserModel();
        $totalUsers = $userModel->countAllResults();
        $activeUsers = $userModel->where('active', 1)->countAllResults();
        $newUsers = $userModel->where('created_on >=', strtotime('first day of this month'))->countAllResults();

        return view('control_plane/users/index', [
            'title' => 'Users',
            'stats' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'new' => $newUsers,
            ],
            'filters' => [
                'status' => $statusFilter,
                'q' => $search,
            ],
        ]);
    }

    private function buildUserRows(string $statusFilter, string $search): array
    {
        $userModel = new UserModel();
        $clinicUserModel = $this->clinicUserModel();

        $builder = $userModel->orderBy('created_on', 'DESC');

        if ($statusFilter === 'active') {
            $builder->where('active', 1);
        } elseif ($statusFilter === 'inactive') {
            $builder->where('active', 0);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('email', $search)
                ->orLike('first_name', $search)
                ->orLike('last_name', $search)
                ->groupEnd();
        }

        $users = $builder->findAll();
        $rows = [];

        // Pre-fetch clinic memberships to avoid N+1 query loop overhead in large sets
        $clinicCounts = [];
        $clinicNames = [];
        $membershipRows = $clinicUserModel
            ->select('clinic_users.user_id, clinics.name as clinic_name, clinics.slug as clinic_slug')
            ->join('clinics', 'clinics.id = clinic_users.clinic_id', 'left')
            ->where('clinic_users.status', 'active')
            ->findAll();

        foreach ($membershipRows as $row) {
            $userId = (int) ($row['user_id'] ?? 0);
            if ($userId <= 0) {
                continue;
            }

            $clinicCounts[$userId] = ($clinicCounts[$userId] ?? 0) + 1;

            if (!isset($clinicNames[$userId])) {
                $clinicNames[$userId] = [];
            }

            $clinicLabel = trim((string) ($row['clinic_name'] ?? ''));
            if ($clinicLabel === '') {
                $clinicLabel = 'Clinic';
            }
            $clinicNames[$userId][] = $clinicLabel;
        }

        foreach ($users as $user) {
            $clinicCount = $clinicCounts[$user['id']] ?? 0;
            $clinicList = $clinicNames[$user['id']] ?? [];
            
            // Determine primary role (Global > Clinic) - simplified
            // We could join user_roles, but for list view, clinic count is more relevant for CP.
            $roleLabel = 'User';
            if ($this->ionAuth->inGroup('admin', $user['id'])) {
                $roleLabel = 'Super Admin';
            } elseif ($clinicCount > 0) {
                $roleLabel = 'Clinic Staff';
            }

            $rows[] = [
                'id' => $user['id'],
                'avatar' => ui_initials($user['first_name'] ?? '', $user['last_name'] ?? '', 'U'),
                'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                'email' => $user['email'],
                'role' => $roleLabel,
                'clinic_count' => $clinicCount,
                'clinic_names' => $clinicList,
                'status' => $user['active'] == 1 ? 'active' : 'inactive',
                'created_on' => date('Y-m-d', $user['created_on']),
            ];
        }

        return $rows;
    }

    /**
     * GET /controlplane/users/{id}
     */
    public function show(int $id)
    {
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/users/' . $id,
        ]);

        $userModel = new UserModel();
        $user = $userModel->find($id);
        
        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('User not found');
        }

        // Fetch Clinic Memberships
        $clinicUserModel = $this->clinicUserModel();
        $memberships = $clinicUserModel->getMemberships($id);
        
        // Fetch Global Roles
        $userRoleModel = new \App\Models\UserRoleModel();
        $userRoles = $userRoleModel->getUserRoles($id);
        $globalRoles = [];
        foreach ($userRoles as $ur) {
            $role = $this->roleModel->find($ur['role_id']);
            if ($role) {
                $globalRoles[] = $role;
            }
        }

        $clinics = (new ClinicModel())
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->findAll();

        // Build per-clinic role options once for both current and new memberships.
        $rolesByClinic = [];
        foreach ($clinics as $clinic) {
            $clinicId = (int) ($clinic['id'] ?? 0);
            if ($clinicId > 0) {
                $rolesByClinic[$clinicId] = $this->roleModel->getAssignableRolesForClinic($clinicId, true);
            }
        }

        $clinicAdminService = new ClinicAdminLifecycleService();
        $userClinicIds = array_map(static fn($m) => (int) ($m['clinic_id'] ?? 0), $memberships);
        $clinicAdmins = $clinicAdminService->getClinicAdministrators($userClinicIds);

        // Attach role labels and administrator status for membership table.
        foreach ($memberships as &$membership) {
            $clinicId = (int) ($membership['clinic_id'] ?? 0);
            $currentRole = $this->roleModel->find((int) ($membership['role_id'] ?? 0));
            $membership['role_name'] = $currentRole['name'] ?? 'Unknown';
            $membership['role_slug'] = $currentRole['slug'] ?? null;

            $adminInfo = $clinicAdmins[$clinicId] ?? null;
            $isDesignatedAdmin = $adminInfo && ($adminInfo['designation_source'] ?? '') === 'designated' && (int) ($adminInfo['admin_user']['id'] ?? 0) === $id;
            $isFallbackCandidate = $adminInfo && in_array(($adminInfo['designation_source'] ?? ''), ['fallback', 'oldest_manager'], true) && (int) ($adminInfo['admin_user']['id'] ?? 0) === $id;

            $membership['admin_info'] = $adminInfo;
            $membership['is_designated_admin'] = $isDesignatedAdmin;
            $membership['is_fallback_candidate'] = $isFallbackCandidate;
            $membership['admin_state'] = $adminInfo['state'] ?? 'unassigned';
            $membership['admin_is_healthy'] = (bool) ($adminInfo['is_healthy'] ?? false);
        }
        unset($membership);

        // Best-practice UX: add flow only shows clinics where user is not already an active member.
        $activeMembershipClinicIds = array_map(static function ($membership) {
            return (int) ($membership['clinic_id'] ?? 0);
        }, $memberships);
        $activeMembershipClinicIds = array_values(array_unique(array_filter($activeMembershipClinicIds)));
        $assignableClinics = array_values(array_filter($clinics, static function ($clinic) use ($activeMembershipClinicIds) {
            return !in_array((int) ($clinic['id'] ?? 0), $activeMembershipClinicIds, true);
        }));

        return view('control_plane/users/show', [
            'title' => 'User Profile',
            'userProfile' => $user,
            'memberships' => $memberships,
            'globalRoles' => $globalRoles,
            'clinics' => $clinics,
            'assignableClinics' => $assignableClinics,
            'rolesByClinic' => $rolesByClinic,
        ]);
    }

    /**
     * POST /controlplane/users/{id}/admin/designate
     * Designates this user as primary administrator for a clinic where they are already a member.
     */
    public function designateClinicAdmin(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', lang('UI.userNotFound'));
        }

        // Account must be globally active
        if ((int) ($user['active'] ?? 0) !== 1) {
            return redirect()->back()->with('error', lang('UI.clinicAdminAccountDeactivatedHelp'));
        }

        $clinicId = (int) $this->request->getPost('clinic_id');
        if ($clinicId <= 0) {
            return redirect()->back()->with('error', lang('UI.clinicAdminSelectClinic'));
        }

        $clinic = (new ClinicModel())->find($clinicId);
        if (!$clinic) {
            return redirect()->back()->with('error', lang('UI.clinicNotFound'));
        }

        // Must be an existing member of this clinic (no implicit cross-clinic membership)
        $clinicUserModel = $this->clinicUserModel();
        $membership = $clinicUserModel
            ->where('clinic_id', $clinicId)
            ->where('user_id', $id)
            ->first();

        if (!$membership) {
            return redirect()->back()->with('error', lang('UI.clinicAdminCrossClinicForbidden'));
        }

        $operatorId = (int) (session()->get('user_id') ?? 0);
        if ($operatorId <= 0) {
            return redirect()->back()->with('error', lang('UI.operatorIdentityRequired'));
        }

        $reason = trim((string) $this->request->getPost('reason'));
        $reason = $reason !== '' ? $reason : 'Designated via user profile UI';

        $lifecycleService = new ClinicAdminLifecycleService();
        $currentAdmin = $lifecycleService->getClinicAdministrator($clinicId);

        // Case 1: User is already explicitly designated for this clinic
        if (!empty($currentAdmin['is_designated']) && (int) ($currentAdmin['admin_user']['id'] ?? 0) === $id) {
            if (!$currentAdmin['is_healthy']) {
                // Explicit restore intent is strictly required (Delta Finding 1)
                return redirect()->to(base_url('controlplane/users/' . $id))
                    ->with('error', lang('UI.clinicAdminAlreadyDesignatedDegraded'));
            }
            return redirect()->to(base_url('controlplane/users/' . $id))->with('info', lang('UI.userAlreadyPrimaryAdmin'));
        }

        // Case 2: User is the unconfirmed legacy fallback candidate -> explicitly assign without replacing
        if (!empty($currentAdmin['is_fallback']) && (int) ($currentAdmin['admin_user']['id'] ?? 0) === $id) {
            $result = $lifecycleService->assignAdministrator($clinicId, $id, $operatorId, reason: $reason);
            $msg = $lifecycleService->formatLocalizedMessage($result, ['clinic' => $clinic['name'] ?? '']);
            if ($result['success']) {
                return redirect()->to(base_url('controlplane/users/' . $id))->with('success', $msg);
            }
            return redirect()->back()->with('error', $msg);
        }

        // Case 3: Clinic currently has another administrator (designated or fallback candidate) -> invoke replacement
        if ($currentAdmin['has_admin'] && !empty($currentAdmin['admin_user']['id'])) {
            $expectedAdminIdRaw = $this->request->getPost('expected_admin_id');
            if ($expectedAdminIdRaw === null || !is_numeric($expectedAdminIdRaw) || (int) $expectedAdminIdRaw <= 0) {
                return redirect()->back()->with('error', lang('UI.clinicAdminErrorExpectedAdminRequired'));
            }
            $expectedAdminUserId = (int) $expectedAdminIdRaw;

            $previousAdminAction = (string) $this->request->getPost('previous_admin_action');
            if (!in_array($previousAdminAction, ['demote_to_staff', 'keep_role', 'deactivate_membership'], true)) {
                return redirect()->back()->with('error', lang('UI.clinicAdminErrorInvalidPreviousAdminAction'));
            }

            $result = $lifecycleService->replaceAdministrator(
                $clinicId,
                $id,
                $operatorId,
                $previousAdminAction,
                reason: $reason,
                expectedCurrentAdminUserId: $expectedAdminUserId
            );

            $msg = $lifecycleService->formatLocalizedMessage($result, ['clinic' => $clinic['name'] ?? '']);
            if ($result['success']) {
                return redirect()->to(base_url('controlplane/users/' . $id))->with('success', $msg);
            }
            return redirect()->back()->with('error', $msg);
        }

        // Case 4: Clinic is truly unassigned -> assign administrator
        $result = $lifecycleService->assignAdministrator($clinicId, $id, $operatorId, reason: $reason);
        $msg = $lifecycleService->formatLocalizedMessage($result, ['clinic' => $clinic['name'] ?? '']);
        if ($result['success']) {
            return redirect()->to(base_url('controlplane/users/' . $id))->with('success', $msg);
        }
        return redirect()->back()->with('error', $msg);
    }

    /**
     * POST /controlplane/users/{id}/admin/restore
     * Restores clinic administrator authority for this user in a clinic where they are designated.
     */
    public function restoreClinicAdmin(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', lang('UI.userNotFound'));
        }

        if ((int) ($user['active'] ?? 0) !== 1) {
            return redirect()->back()->with('error', lang('UI.clinicAdminAccountDeactivatedHelp'));
        }

        $clinicId = (int) $this->request->getPost('clinic_id');
        if ($clinicId <= 0) {
            return redirect()->back()->with('error', lang('UI.clinicAdminSelectClinic'));
        }

        $operatorId = (int) (session()->get('user_id') ?? 0);
        if ($operatorId <= 0) {
            return redirect()->back()->with('error', lang('UI.operatorIdentityRequired'));
        }

        $expectedDesignatedId = $this->request->getPost('expected_designated_admin_id');
        if ($expectedDesignatedId === null || !is_numeric($expectedDesignatedId) || (int) $expectedDesignatedId <= 0) {
            return redirect()->back()->with('error', lang('UI.clinicAdminErrorExpectedAdminRequired'));
        }
        $expectedDesignatedAdminUserId = (int) $expectedDesignatedId;
        if ($expectedDesignatedAdminUserId !== $id) {
            return redirect()->back()->with('error', lang('UI.clinicAdminErrorTargetMismatch'));
        }

        $reason = trim((string) $this->request->getPost('reason'));
        $reason = $reason !== '' ? $reason : 'Restored via user profile UI';

        $lifecycleService = new ClinicAdminLifecycleService();
        $result = $lifecycleService->restoreAdministrator(
            $clinicId,
            $id,
            $operatorId,
            reason: $reason,
            expectedDesignatedAdminUserId: $expectedDesignatedAdminUserId
        );

        $msg = $lifecycleService->formatLocalizedMessage($result, ['clinic' => $clinicId]);
        if ($result['success']) {
            return redirect()->to(base_url('controlplane/users/' . $id))->with('success', $msg);
        }

        return redirect()->back()->with('error', $msg);
    }

    /**
     * POST /controlplane/users/{id}/memberships/add
     */
    public function addMembership(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $actorUserId = (int) ($this->ionAuth->getUserId() ?? 0);
        $membershipService = new \App\Services\ClinicMembershipService();
        $result = $membershipService->addExistingUserMembership($clinicId, $id, $roleId, $actorUserId);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->to(base_url('controlplane/users/' . $id))->with('success', 'Membership saved successfully.');
    }

    /**
     * POST /controlplane/users/{id}/memberships/{membershipId}/update-role
     */
    public function updateMembershipRole(int $id, int $membershipId)
    {
        $clinicUserModel = $this->clinicUserModel();
        $membership = $clinicUserModel->find($membershipId);
        if (!$membership || (int) $membership['user_id'] !== $id) {
            return redirect()->back()->with('error', 'Membership not found.');
        }

        $roleId = (int) $this->request->getPost('role_id');
        if ($roleId <= 0) {
            return redirect()->back()->with('error', 'Role is required.');
        }

        $clinicId = (int) $membership['clinic_id'];
        $actorUserId = (int) ($this->ionAuth->getUserId() ?? 0);

        $membershipService = new \App\Services\ClinicMembershipService();
        $result = $membershipService->replaceRole($clinicId, $id, $roleId, $actorUserId);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->to(base_url('controlplane/users/' . $id))->with('success', 'Membership role updated.');
    }

    /**
     * POST /controlplane/users/{id}/memberships/{membershipId}/remove
     */
    public function removeMembership(int $id, int $membershipId)
    {
        $clinicUserModel = $this->clinicUserModel();
        $membership = $clinicUserModel->find($membershipId);
        if (!$membership || (int) $membership['user_id'] !== $id) {
            return redirect()->back()->with('error', 'Membership not found.');
        }

        $clinicId = (int) ($membership['clinic_id'] ?? 0);
        $actorUserId = (int) ($this->ionAuth->getUserId() ?? 0);

        $membershipService = new \App\Services\ClinicMembershipService();
        $result = $membershipService->removeMembership($clinicId, $id, $actorUserId);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->to(base_url('controlplane/users/' . $id))->with('success', 'Membership removed.');
    }

    private function isClinicManagerRole(int $roleId): bool
    {
        if ($roleId <= 0) {
            return false;
        }

        return $this->roleModel->hasPermission($roleId, 'users', 'create')
            || $this->roleModel->hasPermission($roleId, 'users', 'edit')
            || $this->roleModel->hasPermission($roleId, 'users', 'delete')
            || $this->roleModel->hasPermission($roleId, 'users', 'roles');
    }

    private function countActiveClinicManagers(int $clinicId): int
    {
        $memberships = $this->clinicUserModel()
            ->where('clinic_id', $clinicId)
            ->where('status', 'active')
            ->findAll();

        $count = 0;
        foreach ($memberships as $membership) {
            if ($this->isClinicManagerRole((int) ($membership['role_id'] ?? 0))) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * POST /controlplane/users/{id}/reset-password
     */
    public function resetPassword(int $id)
    {
        $password = $this->request->getPost('password');
        if (strlen($password) < 8) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
            }
            return redirect()->back()->with('error', 'Password must be at least 8 characters long.');
        }

        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'User not found.']);
            }
            return redirect()->back()->with('error', 'User not found.');
        }

        if ($this->ionAuth->update($id, ['password' => $password])) {
            $this->auditService->logEvent('user_password_reset', [
                'route' => '/controlplane/users/' . $id . '/reset-password',
                'method' => 'POST',
                'metadata' => [
                    'target_user_id' => $id,
                    'target_email' => $user['email']
                ],
            ]);
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => "Password reset for {$user['email']}."]);
            }
            return redirect()->back()->with('success', "Password reset for {$user['email']}.");
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to reset password.']);
            }
            return redirect()->back()->with('error', 'Failed to reset password.');
        }
    }

    /**
     * POST /controlplane/users/{id}/toggle-status
     */
    public function toggleStatus(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'User not found.']);
            }
            return redirect()->back()->with('error', 'User not found.');
        }

        // Prevent deactivating self
        if ($id == session()->get('user_id')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Cannot deactivate your own account.']);
            }
            return redirect()->back()->with('error', 'Cannot deactivate your own account.');
        }

        $newStatus = $user['active'] == 1 ? 0 : 1;
        
        // Use IonAuth for safe status update
        $updateData = ['active' => $newStatus];
        
        if ($this->ionAuth->update($id, $updateData)) {
            $statusLabel = $newStatus ? 'activated' : 'deactivated';
            $this->auditService->logEvent('user_status_toggle', [
                'route' => '/controlplane/users/' . $id . '/toggle-status',
                'method' => 'POST',
                'metadata' => [
                    'target_user_id' => $id,
                    'new_status' => $statusLabel
                ],
            ]);
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => "User {$statusLabel}."]);
            }
            return redirect()->back()->with('success', "User {$statusLabel}.");
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to update user status.']);
            }
            return redirect()->back()->with('error', 'Failed to update user status.');
        }
    }

    /**
     * POST /controlplane/users/{id}/delete
     */
    public function delete(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'User not found.']);
            }
            return redirect()->back()->with('error', 'User not found.');
        }

        // Prevent deleting self
        if ($id == session()->get('user_id')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Cannot delete your own account.']);
            }
            return redirect()->back()->with('error', 'Cannot delete your own account.');
        }

        // Prevent deleting Super Admin (ID 1)
        if ($id == 1) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Cannot delete the primary Super Admin.']);
            }
            return redirect()->back()->with('error', 'Cannot delete the primary Super Admin.');
        }

        if ($this->ionAuth->deleteUser($id)) {
            $this->auditService->logEvent('user_delete', [
                'route' => '/controlplane/users/' . $id . '/delete',
                'method' => 'POST',
                'metadata' => [
                    'target_user_id' => $id,
                    'target_email' => $user['email']
                ],
            ]);
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'User deleted successfully.']);
            }
            return redirect()->to('/controlplane/users')->with('success', 'User deleted successfully.');
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete user.']);
            }
            return redirect()->back()->with('error', 'Failed to delete user.');
        }
    }
}
