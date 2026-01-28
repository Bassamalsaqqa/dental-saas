<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\ClinicUserModel;
use App\Services\ControlPlaneAuditService;
use App\Libraries\IonAuth;

class Users extends BaseController
{
    protected $auditService;
    protected $ionAuth;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
        $this->ionAuth = new IonAuth();
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
        $db = \Config\Database::connect();

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

        // Pre-fetch clinic counts to avoid N+1 query loop overhead in large sets
        // SELECT user_id, COUNT(*) as count FROM clinic_users GROUP BY user_id
        $clinicCounts = [];
        $countQuery = $db->table('clinic_users')
                         ->select('user_id, COUNT(*) as count')
                         ->where('status', 'active')
                         ->groupBy('user_id')
                         ->get()
                         ->getResultArray();
        foreach ($countQuery as $c) {
            $clinicCounts[$c['user_id']] = $c['count'];
        }

        foreach ($users as $user) {
            $clinicCount = $clinicCounts[$user['id']] ?? 0;
            
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
                'avatar' => strtoupper(substr($user['first_name'] ?? '', 0, 1) . substr($user['last_name'] ?? '', 0, 1)),
                'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                'email' => $user['email'],
                'role' => $roleLabel,
                'clinic_count' => $clinicCount,
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
        $clinicUserModel = new ClinicUserModel();
        $memberships = $clinicUserModel->getMemberships($id);
        
        // Fetch Global Roles
        $userRoleModel = new \App\Models\UserRoleModel();
        $roleModel = new \App\Models\RoleModel();
        $userRoles = $userRoleModel->getUserRoles($id);
        $globalRoles = [];
        foreach ($userRoles as $ur) {
            $role = $roleModel->find($ur['role_id']);
            if ($role) {
                $globalRoles[] = $role;
            }
        }

        return view('control_plane/users/show', [
            'title' => 'User Profile',
            'user' => $user,
            'memberships' => $memberships,
            'globalRoles' => $globalRoles,
        ]);
    }

    /**
     * POST /controlplane/users/{id}/reset-password
     */
    public function resetPassword(int $id)
    {
        $password = $this->request->getPost('password');
        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'Password must be at least 8 characters long.');
        }

        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
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
            return redirect()->back()->with('success', "Password reset for {$user['email']}.");
        } else {
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
            return redirect()->back()->with('error', 'User not found.');
        }

        // Prevent deactivating self
        if ($id == session()->get('user_id')) {
            return redirect()->back()->with('error', 'Cannot deactivate your own account.');
        }

        $newStatus = $user['active'] == 1 ? 0 : 1;
        
        // Use IonAuth for safe status update
        $updateData = ['active' => $newStatus];
        
        // If reactivating, we might need to clear activation code? 
        // IonAuth update handles 'active' column directly.
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
            return redirect()->back()->with('success', "User {$statusLabel}.");
        } else {
            return redirect()->back()->with('error', 'Failed to update user status.');
        }
    }
}
