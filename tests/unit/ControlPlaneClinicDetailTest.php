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
        $this->db?->resetTransStatus();
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

    /**
     * Blocking Finding 1: EnsureControlPlaneInvoicesTables down() is non-destructive
     * Invoking down() must not drop either table or delete existing rows.
     */
    public function testEnsureControlPlaneInvoicesTablesDownIsNonDestructive(): void
    {
        require_once APPPATH . 'Database/Migrations/2026-09-14-200000_EnsureControlPlaneInvoicesTables.php';
        $migration = new \App\Database\Migrations\EnsureControlPlaneInvoicesTables();

        $this->assertTrue($this->db->tableExists('control_plane_invoices'));
        $this->assertTrue($this->db->tableExists('control_plane_invoice_payments'));

        $clinic = $this->createClinic('Migration Down Clinic');
        $invoiceNumber = 'TEST-DOWN-' . bin2hex(random_bytes(4));
        $this->db->table('control_plane_invoices')->insert([
            'clinic_id' => $clinic['id'],
            'invoice_number' => $invoiceNumber,
            'status' => 'draft',
            'subtotal' => 100.00,
            'tax_amount' => 0.00,
            'total_amount' => 100.00,
            'currency' => 'USD',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $insertedId = $this->db->insertID();

        // Invoke down()
        $migration->down();

        // Tables must still physically exist
        $this->assertTrue($this->db->tableExists('control_plane_invoices'));
        $this->assertTrue($this->db->tableExists('control_plane_invoice_payments'));

        // Row must still exist
        $row = $this->db->table('control_plane_invoices')->where('id', $insertedId)->get()->getRowArray();
        $this->assertNotNull($row);
        $this->assertSame($invoiceNumber, $row['invoice_number']);
    }

    /**
     * Blocking Finding 2: When invoice query fails, page renders successfully,
     * surfaces unavailable warning, renders dashes for stats, and does NOT render "No invoices found."
     */
    public function testShowRecoversGracefullyFromInvoiceQueryFailure(): void
    {
        $clinic = $this->createClinic('Clinic Graceful Invoice Fail');

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $controller = new \App\Controllers\ControlPlane\Clinics();
        $request = \Config\Services::request();
        $response = \Config\Services::response();
        $logger = \Config\Services::logger();
        $controller->initController($request, $response, $logger);

        // Inject an invoice model that simulates query failure
        $failingInvoiceModel = new class extends \App\Models\ControlPlaneInvoiceModel {
            public function where($key, $value = null, ?bool $escape = null)
            {
                throw new \Error('Call to a member function getResult() on bool');
            }
        };
        $controller->setInvoiceModel($failingInvoiceModel);

        $output = $controller->show((int) $clinic['id']);
        $this->assertIsString($output);
        $this->assertStringContainsString($clinic['name'], $output);
        $this->assertStringContainsString(lang('UI.billingDataUnavailable'), $output);
        $this->assertStringNotContainsString('No invoices found.', $output);
        $this->assertStringContainsString('--', $output);
    }

    /**
     * Blocking Finding 2: When query succeeds and is genuinely empty,
     * page renders normal empty state "No invoices found." without unavailable warning.
     */
    public function testShowWithEmptyInvoicesRendersNormalEmptyState(): void
    {
        $clinic = $this->createClinic('Clinic Empty Invoices Normal');

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $controller = new \App\Controllers\ControlPlane\Clinics();
        $request = \Config\Services::request();
        $response = \Config\Services::response();
        $logger = \Config\Services::logger();
        $controller->initController($request, $response, $logger);

        $output = $controller->show((int) $clinic['id']);
        $this->assertIsString($output);
        $this->assertStringContainsString('No invoices found.', $output);
        $this->assertStringNotContainsString(lang('UI.billingDataUnavailable'), $output);
    }

    /**
     * Parity Scenario 1: One explicit active designation: registry and detail show the same user.
     */
    public function testRegistryAndDetailParityExplicitActiveDesignation(): void
    {
        $clinic = $this->createClinic('Parity Explicit Active');
        $user = $this->createUser('parity_admin', 1);
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

        $service = new ClinicAdminLifecycleService();
        $adminInfo = $service->getClinicAdministrator((int) $clinic['id']);
        $this->assertTrue($adminInfo['has_admin']);
        $this->assertSame($user['email'], $adminInfo['admin_email']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_HEALTHY, $adminInfo['state']);

        $bulkMap = $service->getClinicAdministrators([(int) $clinic['id']]);
        $this->assertArrayHasKey((int) $clinic['id'], $bulkMap);
        $this->assertSame($user['email'], $bulkMap[(int) $clinic['id']]['admin_email']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_HEALTHY, $bulkMap[(int) $clinic['id']]['state']);
    }

    /**
     * Parity Scenario 2: Explicit designated user globally disabled:
     * both surfaces retain that identity and show disabled/degraded; no other manager is promoted.
     */
    public function testRegistryAndDetailParityDisabledAdminRetainsIdentity(): void
    {
        $clinic = $this->createClinic('Parity Disabled Admin');
        $disabledAdmin = $this->createUser('disabled_desig', 0);
        $activeManager = $this->createUser('active_other_mgr', 1);
        $adminRoleId = $this->getAdminRoleId();

        // Both belong to clinic with manager role
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $disabledAdmin['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ]);
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $activeManager['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ]);

        // Explicit designation is disabledAdmin
        $this->db->table('settings')->insert([
            'clinic_id' => $clinic['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $disabledAdmin['id'],
            'setting_type' => 'number',
        ]);

        $service = new ClinicAdminLifecycleService();
        $adminInfo = $service->getClinicAdministrator((int) $clinic['id']);
        $this->assertTrue($adminInfo['has_admin']);
        $this->assertSame($disabledAdmin['email'], $adminInfo['admin_email']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_DISABLED_USER, $adminInfo['state']);

        $bulkMap = $service->getClinicAdministrators([(int) $clinic['id']]);
        $this->assertSame($disabledAdmin['email'], $bulkMap[(int) $clinic['id']]['admin_email']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_DISABLED_USER, $bulkMap[(int) $clinic['id']]['state']);
    }

    /**
     * Parity Scenario 3: Designated user globally reactivated but membership inactive:
     * both surfaces show the same designated user and restoration is available.
     */
    public function testRegistryAndDetailParityReactivatedUserWithInactiveMembership(): void
    {
        $clinic = $this->createClinic('Parity Inactive Membership');
        $user = $this->createUser('reactivated_admin', 1);
        $adminRoleId = $this->getAdminRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user['id'],
            'role_id' => $adminRoleId,
            'status' => 'inactive',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('settings')->insert([
            'clinic_id' => $clinic['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $user['id'],
            'setting_type' => 'number',
        ]);

        $service = new ClinicAdminLifecycleService();
        $adminInfo = $service->getClinicAdministrator((int) $clinic['id']);
        $this->assertSame($user['email'], $adminInfo['admin_email']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_INACTIVE_MEMBERSHIP, $adminInfo['state']);

        $bulkMap = $service->getClinicAdministrators([(int) $clinic['id']]);
        $this->assertSame($user['email'], $bulkMap[(int) $clinic['id']]['admin_email']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_INACTIVE_MEMBERSHIP, $bulkMap[(int) $clinic['id']]['state']);
    }

    /**
     * Parity Scenario 5: Clinic with no designation and multiple managers:
     * legacy fallback is deterministic and consistent on both surfaces, zero mutation on viewing.
     */
    public function testRegistryAndDetailParityNoDesignationFallbackDeterministic(): void
    {
        $clinic = $this->createClinic('Parity Fallback Multi Mgr');
        $adminRoleId = $this->getAdminRoleId();

        $managerA = $this->createUser('fallback_a', 1);
        $managerB = $this->createUser('fallback_b', 1);

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $managerA['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
        ]);
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $managerB['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ]);

        $service = new ClinicAdminLifecycleService();
        $single = $service->getClinicAdministrator((int) $clinic['id']);
        $bulk = $service->getClinicAdministrators([(int) $clinic['id']])[(int) $clinic['id']];

        $this->assertSame($managerA['email'], $single['admin_email']);
        $this->assertSame($managerA['email'], $bulk['admin_email']);
        $this->assertSame('fallback', $single['designation_source']);
        $this->assertSame('fallback', $bulk['designation_source']);
        $this->assertTrue($single['is_fallback']);
        $this->assertFalse($single['is_designated']);

        // Zero mutation: settings table must not have received a written designation
        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertNull($setting, 'Viewing clinic with fallback must not persist designation to settings');
    }

    /**
     * User Detail Contextual Action: Designate admin for an unassigned clinic where user is member.
     */
    public function testUserDetailContextualDesignateAdminSucceeds(): void
    {
        $clinic = $this->createClinic('User Detail Assign Clinic');
        $user = $this->createUser('user_ctx_assign', 1);
        $staffRoleId = $this->getStaffRoleId();

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

        $result = $this->postWithCsrf('/controlplane/users/' . $user['id'] . '/admin/designate', [
            'clinic_id' => $clinic['id'],
            'reason' => 'Designating from user profile',
        ], $sessionData);

        $result->assertStatus(302);
        $result->assertRedirectTo(base_url('controlplane/users/' . $user['id']));

        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertNotNull($setting);
        $this->assertSame((string) $user['id'], $setting['setting_value']);
    }

    /**
     * User Detail Contextual Action: Replace existing admin with demote_to_staff.
     */
    public function testUserDetailContextualDesignateAdminReplacesExistingAdminWithDemote(): void
    {
        $clinic = $this->createClinic('User Detail Replace Clinic');
        $adminA = $this->createUser('user_ctx_admin_a', 1);
        $userB = $this->createUser('user_ctx_admin_b', 1);
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $adminA['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('settings')->insert([
            'clinic_id' => $clinic['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $adminA['id'],
            'setting_type' => 'number',
        ]);
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $userB['id'],
            'role_id' => $staffRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $result = $this->postWithCsrf('/controlplane/users/' . $userB['id'] . '/admin/designate', [
            'clinic_id' => $clinic['id'],
            'previous_admin_action' => 'demote_to_staff',
            'expected_admin_id' => $adminA['id'],
            'reason' => 'Replacing administrator from user profile',
        ], $sessionData);

        $result->assertStatus(302);

        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertSame((string) $userB['id'], $setting['setting_value']);

        // User A demoted to staff
        $mA = $this->clinicUserModel->where('clinic_id', $clinic['id'])->where('user_id', $adminA['id'])->first();
        $this->assertSame($staffRoleId, (int) $mA['role_id']);
    }

    /**
     * User Detail Contextual Action: Rejects cross-clinic target with zero mutation.
     */
    public function testUserDetailContextualDesignateAdminRejectsCrossClinic(): void
    {
        $clinic = $this->createClinic('User Detail Cross Clinic');
        $user = $this->createUser('user_ctx_nonmember', 1);
        // User is NOT added to clinic_users

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $result = $this->postWithCsrf('/controlplane/users/' . $user['id'] . '/admin/designate', [
            'clinic_id' => $clinic['id'],
        ], $sessionData);

        $result->assertStatus(302);
        // Setting must remain absent
        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertNull($setting);
    }

    /**
     * User Detail Contextual Action: Rejects globally disabled user with zero mutation.
     */
    public function testUserDetailContextualDesignateAdminRejectsDisabledUser(): void
    {
        $clinic = $this->createClinic('User Detail Disabled Clinic');
        $user = $this->createUser('user_ctx_disabled', 0);
        $staffRoleId = $this->getStaffRoleId();

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

        $result = $this->postWithCsrf('/controlplane/users/' . $user['id'] . '/admin/designate', [
            'clinic_id' => $clinic['id'],
        ], $sessionData);

        $result->assertStatus(302);
        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertNull($setting);
    }

    /**
     * User Detail Contextual Action: Restore admin authority from user profile.
     */
    public function testUserDetailContextualRestoreAdminSucceeds(): void
    {
        $clinic = $this->createClinic('User Detail Restore Clinic');
        $user = $this->createUser('user_ctx_restore', 1);
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

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

        $result = $this->postWithCsrf('/controlplane/users/' . $user['id'] . '/admin/restore', [
            'clinic_id' => $clinic['id'],
            'expected_designated_admin_id' => $user['id'],
        ], $sessionData);

        $result->assertStatus(302);

        $m = $this->clinicUserModel->where('clinic_id', $clinic['id'])->where('user_id', $user['id'])->first();
        $this->assertSame('active', $m['status']);
        $this->assertSame($adminRoleId, (int) $m['role_id']);
    }

    /**
     * Invariant: Suspension and reactivation alone never alter designated_admin_user_id.
     */
    public function testSuspensionAndReactivationNeverAltersDesignatedAdminSetting(): void
    {
        $clinic = $this->createClinic('Invariant Suspend Reactivate');
        $user = $this->createUser('invariant_user', 1);
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

        // 1. Suspend user
        $this->db->table('users')->where('id', $user['id'])->update(['active' => 0]);

        $settingAfterSuspend = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertNotNull($settingAfterSuspend);
        $this->assertSame((string) $user['id'], $settingAfterSuspend['setting_value']);

        // 2. Reactivate user
        $this->db->table('users')->where('id', $user['id'])->update(['active' => 1]);

        $settingAfterReactivate = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertNotNull($settingAfterReactivate);
        $this->assertSame((string) $user['id'], $settingAfterReactivate['setting_value']);
    }

    /**
     * Finding 1: Fallback candidate user profile shows confirm-designation action, not Primary Admin.
     */
    public function testFallbackCandidateUserProfileShowsConfirmDesignationNotPrimaryAdmin(): void
    {
        $clinic = $this->createClinic('Fallback Candidate Clinic');
        $user = $this->createUser('fallback_candidate', 1);
        $adminRoleId = $this->getAdminRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Ensure NO setting exists in settings table
        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertNull($setting);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $result = $this->withSession($sessionData)->get('/controlplane/users/' . $user['id']);
        $result->assertStatus(200);

        // Must display Legacy Administrator Candidate badge
        $result->assertSee(lang('UI.legacyAdminCandidate'));

        // Must provide Confirm Designation / Make Administrator action button
        $result->assertSee(lang('UI.confirmDesignation'));

        // Must NOT display Primary Administrator badge for this user/clinic
        $result->assertDontSee(lang('UI.primaryAdmin'));
    }

    /**
     * Finding 1: Posting confirm designation persists setting, logs audit, and does not duplicate membership.
     */
    public function testPostingConfirmDesignationPersistsSettingAndAuditWithoutDuplicatingMembership(): void
    {
        $clinic = $this->createClinic('Confirm Fallback Clinic');
        $user = $this->createUser('confirm_candidate', 1);
        $adminRoleId = $this->getAdminRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $result = $this->postWithCsrf('/controlplane/users/' . $user['id'] . '/admin/designate', [
            'clinic_id' => $clinic['id'],
        ], $sessionData);

        $result->assertStatus(302);
        $result->assertSessionHas('success');

        // 1. Persisted in settings table
        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertNotNull($setting);
        $this->assertSame((string) $user['id'], $setting['setting_value']);

        // 2. Audit log entry recorded
        $audit = $this->db->table('control_plane_audits')
            ->where('event_type', 'clinic_admin_assigned')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();
        $this->assertNotNull($audit);
        $this->assertSame((int) $sessionData['user_id'], (int) $audit['actor_user_id']);

        // 3. Exactly 1 membership row exists (no duplication)
        $memberships = $this->clinicUserModel
            ->where('clinic_id', $clinic['id'])
            ->where('user_id', $user['id'])
            ->findAll();
        $this->assertCount(1, $memberships);

        // 4. Registry and detail report explicit designation
        $lifecycleService = new ClinicAdminLifecycleService();
        $adminInfo = $lifecycleService->getClinicAdministrator($clinic['id']);
        $this->assertTrue($adminInfo['is_designated']);
        $this->assertTrue($adminInfo['has_explicit_admin']);
        $this->assertFalse($adminInfo['is_fallback']);
        $this->assertSame('designated', $adminInfo['designation_source']);

        $bulk = $lifecycleService->getClinicAdministrators([$clinic['id']])[$clinic['id']];
        $this->assertTrue($bulk['is_designated']);
        $this->assertTrue($bulk['has_explicit_admin']);
        $this->assertFalse($bulk['is_fallback']);
        $this->assertSame('designated', $bulk['designation_source']);

        // 5. User profile view now displays Primary Administrator
        $userView = $this->withSession($sessionData)->get('/controlplane/users/' . $user['id']);
        $userView->assertStatus(200);
        $userView->assertSee(lang('UI.primaryAdmin'));
    }

    /**
     * Finding 1: Viewing fallback candidate performs zero mutation on settings table.
     */
    public function testViewingFallbackCandidatePerformsZeroMutationOnSettings(): void
    {
        $clinic = $this->createClinic('Zero Mutation Fallback Clinic');
        $user = $this->createUser('zero_mutate_candidate', 1);
        $adminRoleId = $this->getAdminRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        // View clinic detail page
        $detailRes = $this->withSession($sessionData)->get('/controlplane/clinics/' . $clinic['id']);
        $detailRes->assertStatus(200);

        $settingAfterDetail = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertNull($settingAfterDetail, 'Clinic detail view must perform zero settings mutation');

        // View user profile page
        $userRes = $this->withSession($sessionData)->get('/controlplane/users/' . $user['id']);
        $userRes->assertStatus(200);

        $settingAfterUser = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertNull($settingAfterUser, 'User profile view must perform zero settings mutation');
    }

    /**
     * Finding 1: Inactive membership fallback candidate cannot be designated.
     */
    public function testInactiveMembershipFallbackCandidateCannotBeDesignated(): void
    {
        $clinic = $this->createClinic('Inactive Candidate Clinic');
        $user = $this->createUser('inactive_candidate', 1);
        $adminRoleId = $this->getAdminRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user['id'],
            'role_id' => $adminRoleId,
            'status' => 'inactive', // Inactive membership
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $result = $this->postWithCsrf('/controlplane/users/' . $user['id'] . '/admin/designate', [
            'clinic_id' => $clinic['id'],
        ], $sessionData);

        $result->assertStatus(302);
        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertNull($setting);
    }

    /**
     * Finding 2: Replace administrator fails closed with zero mutation on omitted, empty, zero, or stale expected_admin_id.
     */
    public function testReplaceAdminFailsClosedOnOmittedEmptyZeroOrStaleExpectedAdminId(): void
    {
        $clinic = $this->createClinic('Stale Concurrency Replace Clinic');
        $adminA = $this->createUser('admin_cur', 1);
        $adminB = $this->createUser('admin_rep', 1);
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $adminA['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('settings')->insert([
            'clinic_id' => $clinic['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $adminA['id'],
            'setting_type' => 'number',
        ]);
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $adminB['id'],
            'role_id' => $staffRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $initialAuditCount = $this->db->table('activity_logs')->countAllResults();

        // 1. Omitted expected_admin_id
        $res1 = $this->postWithCsrf('/controlplane/clinics/' . $clinic['id'] . '/admin/replace', [
            'user_id' => $adminB['id'],
            'previous_admin_action' => 'demote_to_staff',
        ], $sessionData);
        $res1->assertStatus(302);
        $res1->assertSessionHas('error', lang('UI.clinicAdminErrorExpectedAdminRequired'));

        // 2. Empty string expected_admin_id
        $res2 = $this->postWithCsrf('/controlplane/clinics/' . $clinic['id'] . '/admin/replace', [
            'user_id' => $adminB['id'],
            'expected_admin_id' => '',
            'previous_admin_action' => 'demote_to_staff',
        ], $sessionData);
        $res2->assertStatus(302);
        $res2->assertSessionHas('error', lang('UI.clinicAdminErrorExpectedAdminRequired'));

        // 3. Zero expected_admin_id
        $res3 = $this->postWithCsrf('/controlplane/clinics/' . $clinic['id'] . '/admin/replace', [
            'user_id' => $adminB['id'],
            'expected_admin_id' => '0',
            'previous_admin_action' => 'demote_to_staff',
        ], $sessionData);
        $res3->assertStatus(302);
        $res3->assertSessionHas('error', lang('UI.clinicAdminErrorExpectedAdminRequired'));

        // 4. Stale / wrong expected_admin_id
        $res4 = $this->postWithCsrf('/controlplane/clinics/' . $clinic['id'] . '/admin/replace', [
            'user_id' => $adminB['id'],
            'expected_admin_id' => '999999',
            'previous_admin_action' => 'demote_to_staff',
        ], $sessionData);
        $res4->assertStatus(302);
        $res4->assertSessionHas('error');

        // Zero mutation assertions
        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertSame((string) $adminA['id'], $setting['setting_value'], 'Designated admin must not change');

        $mA = $this->clinicUserModel->where('clinic_id', $clinic['id'])->where('user_id', $adminA['id'])->first();
        $this->assertSame($adminRoleId, (int) $mA['role_id'], 'Admin A must still have admin role');

        $this->assertSame($initialAuditCount, $this->db->table('activity_logs')->countAllResults(), 'No audit logs written');
    }

    /**
     * Finding 2: Replace administrator fails closed with zero mutation on missing or invalid previous_admin_action.
     */
    public function testReplaceAdminFailsClosedOnMissingOrInvalidPreviousAdminAction(): void
    {
        $clinic = $this->createClinic('Invalid Prev Action Clinic');
        $adminA = $this->createUser('admin_act_a', 1);
        $adminB = $this->createUser('admin_act_b', 1);
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $adminA['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('settings')->insert([
            'clinic_id' => $clinic['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $adminA['id'],
            'setting_type' => 'number',
        ]);
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $adminB['id'],
            'role_id' => $staffRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        // 1. Missing previous_admin_action
        $res1 = $this->postWithCsrf('/controlplane/clinics/' . $clinic['id'] . '/admin/replace', [
            'user_id' => $adminB['id'],
            'expected_admin_id' => $adminA['id'],
        ], $sessionData);
        $res1->assertStatus(302);
        $res1->assertSessionHas('error', lang('UI.clinicAdminErrorInvalidPreviousAdminAction'));

        // 2. Invalid previous_admin_action
        $res2 = $this->postWithCsrf('/controlplane/clinics/' . $clinic['id'] . '/admin/replace', [
            'user_id' => $adminB['id'],
            'expected_admin_id' => $adminA['id'],
            'previous_admin_action' => 'delete_account_hack',
        ], $sessionData);
        $res2->assertStatus(302);
        $res2->assertSessionHas('error', lang('UI.clinicAdminErrorInvalidPreviousAdminAction'));

        // Zero mutation
        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertSame((string) $adminA['id'], $setting['setting_value']);
    }

    /**
     * Finding 2: Restore administrator fails closed with zero mutation on omitted, empty, or mismatched expected_designated_admin_id.
     */
    public function testRestoreAdminFailsClosedOnOmittedOrMismatchedExpectedDesignatedAdminId(): void
    {
        $clinic = $this->createClinic('Restore Concurrency Clinic');
        $user = $this->createUser('restore_user', 1);
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

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

        // 1. Omitted expected_designated_admin_id on clinic admin restore
        $res1 = $this->postWithCsrf('/controlplane/clinics/' . $clinic['id'] . '/admin/restore', [
            'user_id' => $user['id'],
        ], $sessionData);
        $res1->assertStatus(302);
        $res1->assertSessionHas('error', lang('UI.clinicAdminErrorExpectedAdminRequired'));

        // 2. Mismatched expected_designated_admin_id on user admin restore
        $res2 = $this->postWithCsrf('/controlplane/users/' . $user['id'] . '/admin/restore', [
            'clinic_id' => $clinic['id'],
            'expected_designated_admin_id' => '999999',
        ], $sessionData);
        $res2->assertStatus(302);
        $res2->assertSessionHas('error', lang('UI.clinicAdminErrorTargetMismatch'));

        // Zero mutation: membership remains inactive staff
        $m = $this->clinicUserModel->where('clinic_id', $clinic['id'])->where('user_id', $user['id'])->first();
        $this->assertSame('inactive', $m['status']);
        $this->assertSame($staffRoleId, (int) $m['role_id']);
    }

    /**
     * Finding 3: Localized UI and accessible dialog contract in English and Arabic.
     */
    public function testLocalizedUIAndAccessibleDialogContractInEnglishAndArabic(): void
    {
        $clinic = $this->createClinic('Localization Clinic');
        $user = $this->createUser('loc_candidate', 1);
        $adminRoleId = $this->getAdminRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        // 1. English rendering
        service('request')->setLocale('en');
        $enRes = $this->withSession($sessionData)->get('/controlplane/users/' . $user['id']);
        $enRes->assertStatus(200);
        $this->assertStringContainsString('role="dialog"', $enRes->getBody());
        $this->assertStringContainsString('aria-modal="true"', $enRes->getBody());
        $this->assertStringContainsString('aria-labelledby="designateModalTitle"', $enRes->getBody());
        $this->assertStringContainsString(lang('UI.legacyAdminCandidate'), $enRes->getBody());
        $this->assertStringContainsString(lang('UI.confirmDesignation'), $enRes->getBody());

        // 2. Arabic rendering
        service('request')->setLocale('ar');
        service('language')->setLocale('ar');
        $sessionData['cp_ui_locale'] = 'ar';
        $sessionData['ui_locale'] = 'ar';
        $_SESSION['cp_ui_locale'] = 'ar';
        $_SESSION['ui_locale'] = 'ar';
        $_GET['lang'] = 'ar';
        session()->set('cp_ui_locale', 'ar');
        session()->set('ui_locale', 'ar');

        $arRes = $this->withSession($sessionData)->get('/controlplane/users/' . $user['id'] . '?lang=ar');
        $arRes->assertStatus(200);
        $this->assertStringContainsString('role="dialog"', $arRes->getBody());
        $this->assertStringContainsString('aria-modal="true"', $arRes->getBody());
        $this->assertStringContainsString('aria-labelledby="designateModalTitle"', $arRes->getBody());
        $this->assertStringContainsString('مرشح مسؤول غير مؤكد', $arRes->response()->getBody());
        $this->assertStringContainsString('تأكيد التعيين كمسؤول', $arRes->response()->getBody());

        // Reset locale back to en
        unset($_GET['lang']);
        service('request')->setLocale('en');
        service('language')->setLocale('en');
        session()->set('cp_ui_locale', 'en');
        session()->set('ui_locale', 'en');
    }

    /**
     * Delta Finding 1: Posting /admin/designate for an explicitly designated inactive-member user
     * without expected state rejects with already-designated error and does not restore authority.
     */
    public function testDesignateEndpointRejectsAlreadyDesignatedDegradedUserWithoutRestoring(): void
    {
        $clinic = $this->createClinic('Designated Degraded Clinic');
        $user = $this->createUser('deg_admin', 1);
        $adminRoleId = $this->getAdminRoleId();

        // Membership is inactive
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user['id'],
            'role_id' => $adminRoleId,
            'status' => 'inactive',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Explicitly designated
        $this->db->table('settings')->insert([
            'clinic_id' => $clinic['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $user['id'],
            'setting_type' => 'number',
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $initialAuditCount = $this->db->table('activity_logs')->countAllResults();

        // POST /controlplane/users/{id}/admin/designate with only clinic_id (no expected state / not a restore request)
        $result = $this->postWithCsrf('/controlplane/users/' . $user['id'] . '/admin/designate', [
            'clinic_id' => $clinic['id'],
        ], $sessionData);

        $result->assertStatus(302);
        $result->assertSessionHas('error', lang('UI.clinicAdminAlreadyDesignatedDegraded'));

        // Assert membership remains inactive
        $m = $this->clinicUserModel->where('clinic_id', $clinic['id'])->where('user_id', $user['id'])->first();
        $this->assertSame('inactive', $m['status'], 'Membership status must remain inactive');
        $this->assertSame($adminRoleId, (int) $m['role_id'], 'Role must remain unchanged');

        // Assert setting unchanged
        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertSame((string) $user['id'], $setting['setting_value'], 'Designation setting must remain unchanged');

        // Assert zero audit log mutation
        $this->assertSame($initialAuditCount, $this->db->table('activity_logs')->countAllResults(), 'Zero audit events logged');
    }

    /**
     * Delta Finding 2: Replace administrator with demote_to_staff preserves inactive membership status of previous admin.
     */
    public function testReplaceAdminDemoteToStaffPreservesInactiveMembershipStatusViaController(): void
    {
        $clinic = $this->createClinic('Demote Inactive Admin Clinic');
        $prevAdmin = $this->createUser('prev_inactive_admin', 1);
        $newAdmin = $this->createUser('new_active_admin', 1);
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

        // Previous admin has inactive membership but was designated
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $prevAdmin['id'],
            'role_id' => $adminRoleId,
            'status' => 'inactive',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('settings')->insert([
            'clinic_id' => $clinic['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $prevAdmin['id'],
            'setting_type' => 'number',
        ]);

        // New admin is active staff member
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $newAdmin['id'],
            'role_id' => $staffRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        $result = $this->postWithCsrf('/controlplane/clinics/' . $clinic['id'] . '/admin/replace', [
            'user_id' => $newAdmin['id'],
            'expected_admin_id' => $prevAdmin['id'],
            'previous_admin_action' => 'demote_to_staff',
        ], $sessionData);

        $result->assertStatus(302);
        $result->assertSessionHas('success');

        // Previous admin role changed to staff, BUT status remains inactive!
        $prevM = $this->clinicUserModel->where('clinic_id', $clinic['id'])->where('user_id', $prevAdmin['id'])->first();
        $this->assertSame($staffRoleId, (int) $prevM['role_id'], 'Previous admin must be demoted to staff role');
        $this->assertSame('inactive', $prevM['status'], 'Previous admin membership status MUST remain inactive (no implicit access reactivation)');

        // New admin promoted and designated
        $newM = $this->clinicUserModel->where('clinic_id', $clinic['id'])->where('user_id', $newAdmin['id'])->first();
        $this->assertSame($adminRoleId, (int) $newM['role_id'], 'New admin must have admin role');
        $this->assertSame('active', $newM['status'], 'New admin must have active status');

        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertSame((string) $newAdmin['id'], $setting['setting_value'], 'New admin is designated');
    }

    /**
     * Delta Finding 3: Localized service outcome flash messages in English and Arabic.
     */
    public function testLocalizedServiceOutcomeFlashMessagesInEnglishAndArabic(): void
    {
        $lifecycleService = new ClinicAdminLifecycleService();

        // English check
        service('request')->setLocale('en');
        service('language')->setLocale('en');

        $enAssignRes = $lifecycleService->formatLocalizedMessage([
            'success' => true,
            'code' => 'admin_assigned',
            'message' => 'The selected user has been assigned as the clinic administrator.',
        ]);
        $this->assertSame('The selected user has been assigned as the clinic administrator.', $enAssignRes);

        $enReplaceRes = $lifecycleService->formatLocalizedMessage([
            'success' => true,
            'code' => 'admin_replaced',
            'message' => 'The clinic administrator has been successfully replaced.',
        ]);
        $this->assertSame('The clinic administrator has been successfully replaced.', $enReplaceRes);

        $enRestoreRes = $lifecycleService->formatLocalizedMessage([
            'success' => true,
            'code' => 'admin_restored',
            'message' => 'Administrator authority has been successfully restored.',
        ]);
        $this->assertSame('Administrator authority has been successfully restored.', $enRestoreRes);

        // Arabic check
        service('request')->setLocale('ar');
        service('language')->setLocale('ar');

        $arAssignRes = $lifecycleService->formatLocalizedMessage([
            'success' => true,
            'code' => 'admin_assigned',
            'message' => 'The selected user has been assigned as the clinic administrator.',
        ]);
        $this->assertSame('تم تعيين المستخدم المختار مسؤولاً عن العيادة بنجاح.', $arAssignRes);

        $arReplaceRes = $lifecycleService->formatLocalizedMessage([
            'success' => true,
            'code' => 'admin_replaced',
            'message' => 'The clinic administrator has been successfully replaced.',
        ]);
        $this->assertSame('تم استبدال مسؤول العيادة بنجاح.', $arReplaceRes);

        $arRestoreRes = $lifecycleService->formatLocalizedMessage([
            'success' => true,
            'code' => 'admin_restored',
            'message' => 'Administrator authority has been successfully restored.',
        ]);
        $this->assertSame('تم استعادة صلاحيات مسؤول العيادة بنجاح.', $arRestoreRes);

        // Reset locale back to en
        service('request')->setLocale('en');
        service('language')->setLocale('en');
    }

    /**
     * Requirement 4: Rendered user detail and clinic detail pages have valid JavaScript syntax
     * and zero duplicate lexical declarations (verified via node --check).
     */
    public function testRenderedPagesHaveValidJavaScriptSyntax(): void
    {
        $clinic = $this->createClinic('JS Check Clinic');
        $user = $this->createUser('js_check_user', 1);
        $adminRoleId = $this->getAdminRoleId();

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = $this->getControlPlaneSession();
        $_SESSION = $sessionData;
        session()->set($sessionData);

        // 1. Check User Detail page JavaScript
        $userRes = $this->withSession($sessionData)->get('/controlplane/users/' . $user['id']);
        $userRes->assertStatus(200);
        $this->assertJavaScriptParsesWithoutErrors($userRes->response()->getBody(), 'controlplane/users/show');

        // 2. Check Clinic Detail page JavaScript
        $clinicRes = $this->withSession($sessionData)->get('/controlplane/clinics/' . $clinic['id']);
        $clinicRes->assertStatus(200);
        $this->assertJavaScriptParsesWithoutErrors($clinicRes->response()->getBody(), 'controlplane/clinics/show');

        // 3. Negative check: verify that assertJavaScriptParsesWithoutErrors fails on duplicate lexical declarations
        $invalidHtml = '<script>let duplicateBinding = 1; let duplicateBinding = 2;</script>';
        $caught = false;
        try {
            $this->assertJavaScriptParsesWithoutErrors($invalidHtml, 'invalidHtml');
        } catch (\PHPUnit\Framework\AssertionFailedError $e) {
            $caught = true;
            $this->assertStringContainsString('has already been declared', $e->getMessage());
        }
        $this->assertTrue($caught, 'Parser check must detect duplicate lexical declarations');
    }

    private function assertJavaScriptParsesWithoutErrors(string $html, string $contextName): void
    {
        preg_match_all('/<script(?![^>]*\bsrc=)[^>]*>(.*?)<\/script>/is', $html, $matches);
        $this->assertNotEmpty($matches[1], "No inline script blocks found in {$contextName}");

        $tempFile = WRITEPATH . 'temp_js_check_' . bin2hex(random_bytes(4)) . '.js';

        foreach ($matches[1] as $idx => $scriptContent) {
            $trimmed = trim($scriptContent);
            if ($trimmed === '') {
                continue;
            }

            file_put_contents($tempFile, $trimmed);

            $command = 'node --check ' . escapeshellarg($tempFile) . ' 2>&1';
            $output = [];
            $exitCode = 0;
            exec($command, $output, $exitCode);

            $outputStr = implode("\n", $output);
            @unlink($tempFile);

            $this->assertSame(
                0,
                $exitCode,
                "JavaScript syntax error in {$contextName} (script #{$idx}):\n{$outputStr}"
            );
        }
    }
}

