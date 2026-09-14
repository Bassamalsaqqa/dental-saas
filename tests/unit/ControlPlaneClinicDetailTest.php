<?php

namespace Tests\Unit;

use App\Models\ClinicModel;
use App\Models\ClinicUserModel;
use App\Models\RoleModel;
use App\Models\SettingsModel;
use App\Models\UserModel;
use App\Services\ClinicAdminLifecycleService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;

final class ControlPlaneClinicDetailTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $db;
    private ClinicModel $clinicModel;
    private ClinicUserModel $clinicUserModel;
    private UserModel $userModel;
    private RoleModel $roleModel;
    private SettingsModel $settingsModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect();
        $this->db->transBegin();

        $this->clinicModel = new ClinicModel();
        $this->clinicUserModel = (new ClinicUserModel())->withoutTenantScope();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->settingsModel = (new SettingsModel())->withoutTenantScope();
    }

    protected function tearDown(): void
    {
        if ($this->db && $this->db->transDepth > 0) {
            $this->db->transRollback();
        }
        parent::tearDown();
    }

    private function createClinic(string $name = 'Detail Test Clinic'): array
    {
        $slug = 'detail-clinic-' . bin2hex(random_bytes(6));
        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        $this->db->table('clinics')->insert([
            'name' => $name,
            'slug' => $slug,
            'uuid' => $uuid,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $clinicId = (int) $this->db->insertID();

        return $this->clinicModel->find($clinicId);
    }

    private function createUser(string $prefix = 'detailuser', int $active = 1): array
    {
        $shortPrefix = substr(preg_replace('/[^a-z0-9]/', '', strtolower($prefix)), 0, 10);
        $token = bin2hex(random_bytes(4));
        $username = $shortPrefix . '_' . $token;
        $this->db->table('users')->insert([
            'ip_address' => '127.0.0.1',
            'username' => $username,
            'password' => 'secret_12345678',
            'email' => $username . '@example.test',
            'first_name' => 'Detail',
            'last_name' => ucfirst($shortPrefix),
            'active' => $active,
            'created_on' => time(),
        ]);
        $userId = (int) $this->db->insertID();

        return $this->userModel->find($userId);
    }

    private function getAdminRoleId(): int
    {
        $role = $this->roleModel->getBySlug('practice_manager') ?: $this->roleModel->getBySlug('clinic_admin');
        return (int) $role['id'];
    }

    private function getStaffRoleId(): int
    {
        $role = $this->roleModel->getBySlug('read_only') ?: $this->roleModel->getBySlug('receptionist');
        return (int) $role['id'];
    }

    private function getOrCreateSuperAdmin(): array
    {
        $superAdminRole = $this->roleModel->getBySlug('super_admin');
        if (!$superAdminRole) {
            $ok = $this->db->table('roles')->insert([
                'name' => 'Super Administrator',
                'slug' => 'super_admin',
                'description' => 'Global control plane super administrator',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $this->assertTrue($ok, 'Fixture: super_admin role insert must succeed');
            $roleId = (int) $this->db->insertID();
            $superAdminRole = $this->roleModel->find($roleId);
        }

        $superAdminRoleId = (int) $superAdminRole['id'];

        $existing = $this->db->table('user_roles')
            ->select('users.*')
            ->join('users', 'users.id = user_roles.user_id')
            ->where('user_roles.role_id', $superAdminRoleId)
            ->where('users.active', 1)
            ->get()
            ->getRowArray();

        if ($existing) {
            return $existing;
        }

        $user = $this->createUser('superadmin_fix', 1);
        $this->db->table('user_roles')->insert([
            'user_id' => $user['id'],
            'role_id' => $superAdminRoleId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $user;
    }

    private function getControlPlaneSession(): array
    {
        $superAdmin = $this->getOrCreateSuperAdmin();
        return [
            'identity' => $superAdmin['email'],
            'email' => $superAdmin['email'],
            'user_id' => (int) $superAdmin['id'],
            'last_check' => time(),
            'global_mode' => true,
            'logged_in' => true,
        ];
    }

    /**
     * Test clinic detail renders 200 with healthy administrator
     */
    public function testGetControlPlaneClinicWithHealthyAdmin(): void
    {
        $clinic = $this->createClinic('Clinic Healthy Detail');
        $user = $this->createUser('healthy_admin', 1);
        $adminRoleId = $this->getAdminRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->table('settings')->insert([
            'clinic_id' => $clinic['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $user['id'],
            'setting_type' => 'number',
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $result = $this->withSession($sessionData)->get('/controlplane/clinics/' . $clinic['id']);
        $result->assertStatus(200);
        $result->assertSee(lang('UI.clinicAdminTitle'));
        $result->assertSee($user['email']);
        $result->assertSee(lang('UI.clinicAdminHealthy'));
    }

    /**
     * Test clinic detail renders 200 with unassigned administrator (no admin)
     */
    public function testGetControlPlaneClinicWithUnassignedAdmin(): void
    {
        $clinic = $this->createClinic('Clinic Unassigned Detail');

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $result = $this->withSession($sessionData)->get('/controlplane/clinics/' . $clinic['id']);
        $result->assertStatus(200);
        $result->assertSee(lang('UI.clinicAdminTitle'));
        $result->assertSee(lang('UI.clinicAdminUnassigned'));
        $result->assertSee(lang('UI.clinicAdminAssignBtn'));
    }

    /**
     * Test clinic detail renders 200 with disabled designated administrator
     */
    public function testGetControlPlaneClinicWithDisabledAdmin(): void
    {
        $clinic = $this->createClinic('Clinic Disabled Detail');
        $user = $this->createUser('disabled_admin', 0); // Deactivated account
        $adminRoleId = $this->getAdminRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->table('settings')->insert([
            'clinic_id' => $clinic['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $user['id'],
            'setting_type' => 'number',
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $result = $this->withSession($sessionData)->get('/controlplane/clinics/' . $clinic['id']);
        $result->assertStatus(200);
        $result->assertSee(lang('UI.clinicAdminTitle'));
        $result->assertSee($user['email']);
        $result->assertSee(lang('UI.clinicAdminDisabledUser'));
    }

    /**
     * Test fail-closed control plane filter without global_mode
     */
    public function testControlPlaneFilterFailsClosedWithoutGlobalMode(): void
    {
        $clinic = $this->createClinic('Filter Test Clinic');

        // Session without global_mode = true
        $sessionData = [
            'identity' => 'user@example.test',
            'user_id' => 999,
            'global_mode' => false,
            'logged_in' => true,
        ];
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $result = $this->withSession($sessionData)->get('/controlplane/clinics/' . $clinic['id']);
        // ControlPlaneFilter redirects or 404s
        $this->assertTrue(in_array($result->response()->getStatusCode(), [302, 403, 404], true));
    }

    private function postWithCsrf(string $path, array $data, array $session = [], array $headers = [])
    {
        $security = service('security');
        $securityConfig = config('Security');
        $tokenName = (string) $securityConfig->tokenName;
        $tokenHash = (string) $security->getHash();
        $cookieName = (string) $securityConfig->cookieName;

        $mergedData = array_merge($data, [$tokenName => $tokenHash]);
        $mergedHeaders = array_merge($headers, [
            'Cookie' => $cookieName . '=' . $tokenHash,
            'X-CSRF-TOKEN' => $tokenHash,
        ]);

        return $this
            ->withSession($session)
            ->withHeaders($mergedHeaders)
            ->post($path, $mergedData);
    }

    /**
     * Test POST /controlplane/clinics/{id}/admin/assign assigns admin and redirects
     */
    public function testPostAssignAdminSucceeds(): void
    {
        $clinic = $this->createClinic('Assign Controller Clinic');
        $user = $this->createUser('assign_ctrl_user', 1);
        $staffRoleId = $this->getStaffRoleId();
        $adminRoleId = $this->getAdminRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user['id'],
            'role_id' => $staffRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $result = $this->postWithCsrf('/controlplane/clinics/' . $clinic['id'] . '/admin/assign', [
            'user_id' => $user['id'],
        ], $sessionData);

        $result->assertStatus(302);
        $result->assertRedirectTo(base_url('controlplane/clinics/' . $clinic['id']));

        // Verify setting and role updated
        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertNotNull($setting);
        $this->assertSame((string) $user['id'], $setting['setting_value']);

        $m = $this->clinicUserModel
            ->where('clinic_id', $clinic['id'])
            ->where('user_id', $user['id'])
            ->first();
        $this->assertSame($adminRoleId, (int) $m['role_id']);
    }

    /**
     * Test POST /controlplane/clinics/{id}/admin/replace with previous_admin_action = demote_to_staff
     */
    public function testPostReplaceAdminWithDemote(): void
    {
        $clinic = $this->createClinic('Replace Ctrl Demote Clinic');
        $user1 = $this->createUser('ctrl_prev_admin', 1);
        $user2 = $this->createUser('ctrl_new_admin', 1);
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user1['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('settings')->insert([
            'clinic_id' => $clinic['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $user1['id'],
            'setting_type' => 'number',
        ]);

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user2['id'],
            'role_id' => $staffRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $result = $this->postWithCsrf('/controlplane/clinics/' . $clinic['id'] . '/admin/replace', [
            'user_id' => $user2['id'],
            'expected_admin_id' => $user1['id'],
            'previous_admin_action' => 'demote_to_staff',
        ], $sessionData);

        $result->assertStatus(302);
        $result->assertRedirectTo(base_url('controlplane/clinics/' . $clinic['id']));

        // Verify user 2 is now admin
        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertSame((string) $user2['id'], $setting['setting_value']);

        // Verify user 1 demoted to staff
        $m1 = $this->clinicUserModel->where('clinic_id', $clinic['id'])->where('user_id', $user1['id'])->first();
        $this->assertSame($staffRoleId, (int) $m1['role_id']);
    }

    /**
     * Test POST /controlplane/clinics/{id}/admin/replace with previous_admin_action = keep_role
     */
    public function testPostReplaceAdminWithKeepRole(): void
    {
        $clinic = $this->createClinic('Replace Ctrl Keep Clinic');
        $user1 = $this->createUser('ctrl_keep_prev', 1);
        $user2 = $this->createUser('ctrl_keep_new', 1);
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user1['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('settings')->insert([
            'clinic_id' => $clinic['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $user1['id'],
            'setting_type' => 'number',
        ]);

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user2['id'],
            'role_id' => $staffRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $result = $this->postWithCsrf('/controlplane/clinics/' . $clinic['id'] . '/admin/replace', [
            'user_id' => $user2['id'],
            'expected_admin_id' => $user1['id'],
            'previous_admin_action' => 'keep_role',
        ], $sessionData);

        $result->assertStatus(302);
        $result->assertRedirectTo(base_url('controlplane/clinics/' . $clinic['id']));

        // Verify user 1 role kept as admin
        $m1 = $this->clinicUserModel->where('clinic_id', $clinic['id'])->where('user_id', $user1['id'])->first();
        $this->assertSame($adminRoleId, (int) $m1['role_id']);
    }

    /**
     * Test POST /controlplane/clinics/{id}/admin/restore succeeds with expected_designated_admin_id
     */
    public function testPostRestoreAdminSucceeds(): void
    {
        $clinic = $this->createClinic('Restore Ctrl Clinic');
        $user = $this->createUser('ctrl_restore_user', 1);
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

        // Degraded membership: inactive status
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user['id'],
            'role_id' => $staffRoleId,
            'status' => 'inactive',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('settings')->insert([
            'clinic_id' => $clinic['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $user['id'],
            'setting_type' => 'number',
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $result = $this->postWithCsrf('/controlplane/clinics/' . $clinic['id'] . '/admin/restore', [
            'user_id' => $user['id'],
            'expected_designated_admin_id' => $user['id'],
        ], $sessionData);

        $result->assertStatus(302);
        $result->assertRedirectTo(base_url('controlplane/clinics/' . $clinic['id']));

        // Membership should now be active with admin role
        $m = $this->clinicUserModel->where('clinic_id', $clinic['id'])->where('user_id', $user['id'])->first();
        $this->assertSame('active', $m['status']);
        $this->assertSame($adminRoleId, (int) $m['role_id']);
    }
}

