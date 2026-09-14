<?php

namespace Tests\Unit;

use App\Models\ClinicModel;
use App\Models\ClinicUserModel;
use App\Models\RoleModel;
use App\Models\SettingsModel;
use App\Models\UserModel;
use App\Services\ClinicAdminLifecycleService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class ClinicAdminLifecycleServiceTest extends CIUnitTestCase
{
    protected $db;
    private ClinicAdminLifecycleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect();
        $this->db->transBegin();
        $this->service = new ClinicAdminLifecycleService();
    }

    protected function tearDown(): void
    {
        if ($this->db && $this->db->transDepth > 0) {
            $this->db->transRollback();
        }
        parent::tearDown();
    }

    public function testGetClinicAdministratorWithDynamicFixture(): void
    {
        $slug = 'service-test-' . bin2hex(random_bytes(6));
        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        $this->db->table('clinics')->insert([
            'name' => 'Service Test Clinic',
            'slug' => $slug,
            'uuid' => $uuid,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $clinicId = (int) $this->db->insertID();

        $username = 'admin_' . bin2hex(random_bytes(4));
        $this->db->table('users')->insert([
            'ip_address' => '127.0.0.1',
            'username' => $username,
            'password' => 'secret_12345678',
            'email' => $username . '@example.test',
            'first_name' => 'Service',
            'last_name' => 'Admin',
            'active' => 1,
            'created_on' => time(),
        ]);
        $userId = (int) $this->db->insertID();

        $adminRole = (new RoleModel())->getBySlug('practice_manager') ?: (new RoleModel())->getBySlug('clinic_admin');

        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinicId,
            'user_id' => $userId,
            'role_id' => $adminRole['id'],
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->table('settings')->insert([
            'clinic_id' => $clinicId,
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $userId,
            'setting_type' => 'number',
        ]);

        $admin = $this->service->getClinicAdministrator($clinicId);

        $this->assertIsArray($admin);
        $this->assertTrue($admin['has_admin']);
        $this->assertSame($userId, (int) $admin['user']['id']);
        $this->assertSame($username . '@example.test', $admin['admin_email']);
        $this->assertTrue($admin['is_healthy']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_HEALTHY, $admin['state']);
    }

    public function testDataIntegrityAuditStructure(): void
    {
        $audit = $this->service->runDataIntegrityAudit();

        $this->assertIsArray($audit);
        $this->assertArrayHasKey('total_clinics', $audit);
        $this->assertArrayHasKey('healthy_count', $audit);
        $this->assertArrayHasKey('disabled_user_count', $audit);
        $this->assertArrayHasKey('inactive_membership_count', $audit);
        $this->assertArrayHasKey('missing_authority_count', $audit);
        $this->assertArrayHasKey('unassigned_count', $audit);
        $this->assertArrayHasKey('multiple_managers_count', $audit);
        $this->assertArrayHasKey('clinics', $audit);
    }
}
