<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\ApiRuntimeContext;
use App\Services\PermissionService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/** Independent review regressions for authority carried between tenant and API contexts. */
final class P0AuthorityReviewTest extends CIUnitTestCase
{
    protected $db;
    private int $userId;
    private int $clinicA;
    private int $clinicB;
    private int $managerRole;
    private int $staffRole;
    private int $platformRole;

    protected function setUp(): void
    {
        parent::setUp();
        ApiRuntimeContext::clear();
        session()->remove(['user_id', 'identity', 'active_clinic_id', 'global_mode', 'is_impersonating', 'impersonated_clinic_id']);
        $this->db = Database::connect();
        $this->db->transBegin();
        $token = bin2hex(random_bytes(6));
        $this->db->table('users')->insert([
            'ip_address' => '127.0.0.1', 'username' => 'review_' . $token,
            'email' => 'review_' . $token . '@example.test',
            'password' => password_hash('Synthetic-password-42', PASSWORD_BCRYPT),
            'first_name' => 'Review', 'last_name' => 'Fixture', 'active' => 1, 'created_on' => time(),
        ]);
        $this->userId = (int) $this->db->insertID();
        $this->clinicA = $this->clinic($token . '-a');
        $this->clinicB = $this->clinic($token . '-b');
        $this->managerRole = $this->role('practice_manager');
        $this->db->table('roles')->insert(['name' => 'Review staff ' . $token, 'slug' => 'review_staff_' . $token, 'is_active' => 1, 'is_system' => 0, 'clinic_id' => null, 'created_by' => $this->userId]);
        $this->staffRole = (int) $this->db->insertID();
        $this->platformRole = $this->role('super_admin');
        $permissionRow = $this->db->table('permissions')->where('module', 'users')->where('action', 'edit')->get()->getRowArray();
        if (!$permissionRow) {
            $this->db->table('permissions')->insert(['module' => 'users', 'action' => 'edit', 'name' => 'Edit review users']);
            $permissionId = (int) $this->db->insertID();
        } else {
            $permissionId = (int) $permissionRow['id'];
        }
        $grant = $this->db->table('role_permissions')->where('role_id', $this->managerRole)->where('permission_id', $permissionId)->countAllResults();
        if (!$grant) {
            $this->db->table('role_permissions')->insert(['role_id' => $this->managerRole, 'permission_id' => $permissionId]);
        }
        foreach ([$this->clinicA => $this->managerRole, $this->clinicB => $this->staffRole] as $clinic => $role) {
            $this->db->table('clinic_users')->insert([
                'clinic_id' => $clinic, 'user_id' => $this->userId, 'role_id' => $role,
                'status' => 'active', 'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        session()->set(['user_id' => $this->userId, 'identity' => 'review_' . $token . '@example.test',
            'active_clinic_id' => $this->clinicA, 'global_mode' => false]);
    }

    protected function tearDown(): void
    {
        ApiRuntimeContext::clear();
        while ($this->db && $this->db->transDepth > 0) {
            $this->db->transRollback();
        }
        session()->remove(['user_id', 'identity', 'active_clinic_id', 'global_mode', 'is_impersonating', 'impersonated_clinic_id']);
        parent::tearDown();
    }

    public function testHistoricalGlobalManagerCannotExtendAuthorityToOtherClinic(): void
    {
        $this->assignment($this->managerRole);
        $permission = new PermissionService();
        $this->assertTrue($permission->hasPermission($this->userId, 'users', 'edit'));
        session()->set('active_clinic_id', $this->clinicB);
        $this->assertSame([$this->staffRole], $permission->getEffectiveRoleIds($this->userId));
        $this->assertFalse($permission->hasPermission($this->userId, 'users', 'edit'));
        $this->assertFalse($permission->hasRole($this->userId, 'practice_manager'));
        $this->assertFalse($permission->hasAnyRole($this->userId, ['practice_manager']));
        $this->assertFalse($permission->canManageUsers($this->userId));
    }

    public function testApiAuthorityUsesHeaderContextEvenWhenWebSessionHasManagerRole(): void
    {
        $this->assignment($this->managerRole);
        ApiRuntimeContext::setUserId($this->userId);
        ApiRuntimeContext::setClinicId($this->clinicB);
        $permission = new PermissionService();
        $this->assertSame($this->clinicA, (int) session('active_clinic_id'));
        $this->assertSame([$this->staffRole], $permission->getEffectiveRoleIds($this->userId));
        $this->assertFalse($permission->hasPermission($this->userId, 'users', 'edit'));
        $this->db->table('clinic_users')->where('clinic_id', $this->clinicB)->where('user_id', $this->userId)->update(['status' => 'inactive']);
        $this->assertSame([], $permission->getEffectiveRoleIds($this->userId));
    }

    public function testInactiveOrExpiredPlatformAssignmentNeverAuthorizes(): void
    {
        session()->set(['global_mode' => true, 'active_clinic_id' => null]);
        $this->assignment($this->platformRole, 0);
        $permission = new PermissionService();
        $this->assertFalse($permission->isSuperAdmin($this->userId));
        $this->assertFalse($permission->hasPermission($this->userId, 'users', 'delete'));
        $this->db->table('user_roles')->where('user_id', $this->userId)->update(['is_active' => 1, 'expires_at' => '2000-01-01 00:00:00']);
        $this->assertFalse($permission->isSuperAdmin($this->userId));
        $this->db->table('user_roles')->where('user_id', $this->userId)->update(['expires_at' => null]);
        $this->assertTrue($permission->isSuperAdmin($this->userId));
        $this->db->table('users')->where('id', $this->userId)->update(['active' => 0]);
        $this->assertFalse($permission->isSuperAdmin($this->userId));
    }

    public function testSuperAdminRoleOnMembershipDoesNotConferPlatformAuthority(): void
    {
        $this->db->table('clinic_users')->where('user_id', $this->userId)->where('clinic_id', $this->clinicB)->update(['role_id' => $this->platformRole]);
        session()->set('active_clinic_id', $this->clinicB);
        $permission = new PermissionService();
        $this->assertFalse($permission->isSuperAdmin($this->userId));
        $this->assertFalse($permission->hasRole($this->userId, 'super_admin'));
        $this->assertSame([], $permission->getEffectiveRoleIds($this->userId));
        $this->assertFalse($permission->hasPermission($this->userId, 'users', 'edit'));
    }

    public function testImpersonationFlagsWithNoPositiveClinicCannotBypassPermissions(): void
    {
        $this->assignment($this->platformRole);
        session()->set(['active_clinic_id' => null, 'impersonated_clinic_id' => null, 'is_impersonating' => true]);
        $permission = new PermissionService();
        $this->assertTrue($permission->isSuperAdmin($this->userId));
        $this->assertFalse($permission->hasPermission($this->userId, 'users', 'edit'));
        $this->assertSame([], $permission->getEffectiveRoleIds($this->userId));
    }

    public function testUnscopedUserPermissionCannotGrantTenantAuthority(): void
    {
        session()->set('active_clinic_id', $this->clinicB);
        $permissionRow = $this->db->table('permissions')->where('module', 'users')->where('action', 'edit')->get()->getRowArray();
        $this->db->table('user_permissions')->insert([
            'user_id' => $this->userId, 'permission_id' => $permissionRow['id'], 'granted' => 1,
        ]);
        $permission = new PermissionService();
        $this->assertFalse($permission->hasPermission($this->userId, 'users', 'edit'));
        $this->assertArrayNotHasKey('edit', $permission->getUserPermissions($this->userId)['users'] ?? []);
        $this->assertSame(1, $this->db->table('user_permissions')->where('user_id', $this->userId)->countAllResults());
    }

    public function testWrongClinicOrInactiveRoleCannotSupplyMembershipAuthority(): void
    {
        session()->set('active_clinic_id', $this->clinicB);
        $permissionRow = $this->db->table('permissions')->where('module', 'users')->where('action', 'edit')->get()->getRowArray();
        $this->db->table('role_permissions')->insert(['role_id' => $this->staffRole, 'permission_id' => $permissionRow['id']]);
        $permission = new PermissionService();
        $this->assertTrue($permission->hasPermission($this->userId, 'users', 'edit'));
        $this->db->table('roles')->where('id', $this->staffRole)->update(['clinic_id' => $this->clinicA]);
        $this->assertFalse($permission->hasPermission($this->userId, 'users', 'edit'));
        $this->db->table('roles')->where('id', $this->staffRole)->update(['clinic_id' => $this->clinicB, 'is_active' => 0]);
        $this->assertFalse($permission->hasPermission($this->userId, 'users', 'edit'));
    }
    public function testWebImpersonationCannotAuthorizeAnInactiveClinic(): void
    {
        $this->assignment($this->platformRole);
        session()->set(['is_impersonating' => true, 'impersonated_clinic_id' => $this->clinicA]);
        $this->db->table('clinics')->where('id', $this->clinicA)->update(['status' => 'suspended']);
        $permission = new PermissionService();
        $this->assertTrue($permission->isSuperAdmin($this->userId));
        $this->assertFalse($permission->hasPermission($this->userId, 'users', 'edit'));
        $this->assertSame([], $permission->getEffectiveRoleIds($this->userId));
    }
    private function clinic(string $token): int
    {
        $this->db->table('clinics')->insert([
            'name' => 'Synthetic review clinic', 'slug' => 'review-' . $token,
            'uuid' => bin2hex(random_bytes(16)), 'status' => 'active', 'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->db->insertID();
    }

    private function role(string $slug): int
    {
        $row = $this->db->table('roles')->where('slug', $slug)->where('clinic_id', null)->get()->getRowArray();
        $this->assertNotEmpty($row, 'Disposable canonical role fixture missing: ' . $slug);
        return (int) $row['id'];
    }

    private function assignment(int $role, int $active = 1): void
    {
        $this->db->table('user_roles')->insert([
            'user_id' => $this->userId, 'role_id' => $role, 'assigned_by' => $this->userId,
            'assigned_at' => date('Y-m-d H:i:s'), 'is_active' => $active,
        ]);
    }
}