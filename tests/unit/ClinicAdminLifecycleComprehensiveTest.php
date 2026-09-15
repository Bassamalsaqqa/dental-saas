<?php

namespace Tests\Unit;

use App\Filters\AuthFilter;
use App\Models\ClinicModel;
use App\Models\ClinicUserModel;
use App\Models\RoleModel;
use App\Models\SettingsModel;
use App\Models\UserModel;
use App\Services\ClinicAdminLifecycleService;
use App\Services\PlanGuard;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * Comprehensive, environment-decoupled test suite covering all security,
 * tenant-scoping, audit, concurrency, and lifecycle requirements.
 */
final class ClinicAdminLifecycleComprehensiveTest extends CIUnitTestCase
{
    protected $db;
    private ClinicAdminLifecycleService $service;
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

        $this->service = new ClinicAdminLifecycleService();
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

    private function getAdminRoleId(): int
    {
        $role = $this->roleModel->getBySlug('practice_manager') ?: $this->roleModel->getBySlug('clinic_admin');
        if (!$role) {
            throw new \RuntimeException('No manager role found in system.');
        }
        return (int) $role['id'];
    }

    private function getStaffRoleId(): int
    {
        $role = $this->roleModel->getBySlug('read_only') ?: $this->roleModel->getBySlug('receptionist');
        if (!$role) {
            throw new \RuntimeException('No staff role found in system.');
        }
        return (int) $role['id'];
    }

    private function createClinic(string $name = 'Test Clinic'): array
    {
        $slug = 'test-clinic-' . bin2hex(random_bytes(6));
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

    private function createUser(string $prefix = 'testuser', int $active = 1): array
    {
        $shortPrefix = substr(preg_replace('/[^a-z0-9]/', '', strtolower($prefix)), 0, 10);
        $token = bin2hex(random_bytes(4));
        $username = $shortPrefix . '_' . $token;
        $this->db->table('users')->insert([
            'ip_address' => '127.0.0.1',
            'username' => $username,
            'password' => 'secret_12345678',
            'email' => $username . '@example.test',
            'first_name' => 'Test',
            'last_name' => ucfirst($shortPrefix),
            'active' => $active,
            'created_on' => time(),
        ]);
        $userId = (int) $this->db->insertID();

        return $this->userModel->find($userId);
    }

    private function addMembership(int $clinicId, int $userId, int $roleId, string $status = 'active'): int
    {
        return (int) $this->clinicUserModel->insert([
            'clinic_id' => $clinicId,
            'user_id' => $userId,
            'role_id' => $roleId,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function setDesignatedAdmin(int $clinicId, int $userId): void
    {
        $this->settingsModel->insert([
            'clinic_id' => $clinicId,
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $userId,
            'setting_type' => 'number',
            'description' => 'Designated primary clinic administrator user ID',
        ]);
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

    /**
     * Requirement 1: Healthy state detection
     */
    public function testAdminStatusDetectionHealthy(): void
    {
        $clinic = $this->createClinic('Healthy Clinic');
        $user = $this->createUser('healthy_admin', 1);
        $adminRoleId = $this->getAdminRoleId();

        $this->addMembership($clinic['id'], $user['id'], $adminRoleId, 'active');
        $this->setDesignatedAdmin($clinic['id'], $user['id']);

        $admin = $this->service->getClinicAdministrator($clinic['id']);

        $this->assertTrue($admin['has_admin']);
        $this->assertTrue($admin['is_healthy']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_HEALTHY, $admin['state']);
        $this->assertTrue($admin['account_active']);
        $this->assertTrue($admin['membership_active']);
        $this->assertTrue($admin['has_admin_authority']);
        $this->assertSame((int) $user['id'], (int) $admin['admin_user']['id']);
        $this->assertSame('designated', $admin['designation_source']);
    }

    /**
     * Requirement 2: Disabled user state detection
     */
    public function testAdminStatusDetectionDisabledUser(): void
    {
        $clinic = $this->createClinic('Disabled User Clinic');
        $user = $this->createUser('disabled_admin', 0);
        $adminRoleId = $this->getAdminRoleId();

        $this->addMembership($clinic['id'], $user['id'], $adminRoleId, 'active');
        $this->setDesignatedAdmin($clinic['id'], $user['id']);

        $admin = $this->service->getClinicAdministrator($clinic['id']);

        $this->assertTrue($admin['has_admin']);
        $this->assertFalse($admin['is_healthy']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_DISABLED_USER, $admin['state']);
        $this->assertFalse($admin['account_active']);
        $this->assertNotEmpty($admin['issues']);
    }

    /**
     * Requirement 3: Inactive membership state detection
     */
    public function testAdminStatusDetectionInactiveMembership(): void
    {
        $clinic = $this->createClinic('Inactive Membership Clinic');
        $user = $this->createUser('inactive_member_admin', 1);
        $adminRoleId = $this->getAdminRoleId();

        $this->addMembership($clinic['id'], $user['id'], $adminRoleId, 'inactive');
        $this->setDesignatedAdmin($clinic['id'], $user['id']);

        $admin = $this->service->getClinicAdministrator($clinic['id']);

        $this->assertTrue($admin['has_admin']);
        $this->assertFalse($admin['is_healthy']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_INACTIVE_MEMBERSHIP, $admin['state']);
        $this->assertFalse($admin['membership_active']);
    }

    /**
     * Requirement 4: Missing admin authority role state detection
     */
    public function testAdminStatusDetectionMissingAuthorityWithDesignation(): void
    {
        $clinic = $this->createClinic('Missing Authority Clinic');
        $user = $this->createUser('staff_admin', 1);
        $staffRoleId = $this->getStaffRoleId();

        $this->addMembership($clinic['id'], $user['id'], $staffRoleId, 'active');
        $this->setDesignatedAdmin($clinic['id'], $user['id']);

        $admin = $this->service->getClinicAdministrator($clinic['id']);

        $this->assertTrue($admin['has_admin']);
        $this->assertFalse($admin['is_healthy']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_MISSING_AUTHORITY, $admin['state']);
        $this->assertFalse($admin['has_admin_authority']);
    }

    /**
     * Requirement 5: Unassigned state when no manager exists and no designation (Finding 7)
     */
    public function testAdminStatusDetectionUnassignedWhenNoManager(): void
    {
        $clinic = $this->createClinic('No Manager Clinic');
        $user = $this->createUser('regular_staff', 1);
        $staffRoleId = $this->getStaffRoleId();

        // Add member with only staff role, NO designated setting
        $this->addMembership($clinic['id'], $user['id'], $staffRoleId, 'active');

        $admin = $this->service->getClinicAdministrator($clinic['id']);

        // Must NOT infer non-manager as admin!
        $this->assertFalse($admin['has_admin']);
        $this->assertFalse($admin['is_healthy']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_UNASSIGNED, $admin['state']);
        $this->assertNull($admin['admin_user']);
    }

    /**
     * Requirement 6: Unassigned state detection for empty clinic
     */
    public function testAdminStatusDetectionUnassignedEmptyClinic(): void
    {
        $clinic = $this->createClinic('Empty Clinic');

        $admin = $this->service->getClinicAdministrator($clinic['id']);

        $this->assertFalse($admin['has_admin']);
        $this->assertFalse($admin['is_healthy']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_UNASSIGNED, $admin['state']);
        $this->assertNull($admin['admin_user']);
    }

    /**
     * Requirement 7: Legacy fallback strictly to oldest active manager role (Finding 7)
     */
    public function testLegacyFallbackToOldestActiveManager(): void
    {
        $clinic = $this->createClinic('Legacy Fallback Clinic');
        $user1 = $this->createUser('manager_one', 1);
        $user2 = $this->createUser('manager_two', 1);
        $adminRoleId = $this->getAdminRoleId();

        // User 1 added first, User 2 added second; no explicit designated setting
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user1['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => '2026-01-01 10:00:00',
        ]);
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user2['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => '2026-01-01 11:00:00',
        ]);

        $admin = $this->service->getClinicAdministrator($clinic['id']);

        $this->assertTrue($admin['has_admin']);
        $this->assertSame((int) $user1['id'], (int) $admin['admin_user']['id']);
        $this->assertSame('fallback', $admin['designation_source']);
        $this->assertTrue($admin['is_fallback']);
        $this->assertFalse($admin['is_designated']);
    }

    /**
     * Requirement 8: Enforce Clinic Membership Boundary / Reject Cross-Clinic Assignment (Finding 2)
     */
    public function testCrossClinicAssignmentRejected(): void
    {
        $clinicA = $this->createClinic('Clinic Alpha');
        $clinicB = $this->createClinic('Clinic Beta');
        $userB = $this->createUser('user_beta', 1);
        $staffRoleId = $this->getStaffRoleId();
        $actor = $this->getOrCreateSuperAdmin();

        // User B is a member of Clinic B ONLY
        $this->addMembership($clinicB['id'], $userB['id'], $staffRoleId, 'active');

        // Attempt assigning User B to Clinic A
        $result = $this->service->assignAdministrator($clinicA['id'], $userB['id'], actorUserId: (int) $actor['id']);

        $this->assertFalse($result['success']);
        $this->assertSame('cross_clinic_forbidden', $result['code']);

        // Verify User B still has NO membership in Clinic A
        $membershipA = $this->clinicUserModel
            ->where('clinic_id', $clinicA['id'])
            ->where('user_id', $userB['id'])
            ->first();
        $this->assertNull($membershipA);
    }

    /**
     * Requirement 9: Reject Cross-Clinic Replacement (Finding 2)
     */
    public function testCrossClinicReplacementRejected(): void
    {
        $clinicA = $this->createClinic('Clinic Alpha');
        $clinicB = $this->createClinic('Clinic Beta');
        $userA = $this->createUser('user_alpha', 1);
        $userB = $this->createUser('user_beta', 1);
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();
        $actor = $this->getOrCreateSuperAdmin();

        $this->addMembership($clinicA['id'], $userA['id'], $adminRoleId, 'active');
        $this->setDesignatedAdmin($clinicA['id'], $userA['id']);

        $this->addMembership($clinicB['id'], $userB['id'], $staffRoleId, 'active');

        // Attempt replacing Clinic A's admin with User B
        $result = $this->service->replaceAdministrator(
            $clinicA['id'],
            $userB['id'],
            actorUserId: (int) $actor['id'],
            expectedCurrentAdminUserId: (int) $userA['id']
        );

        $this->assertFalse($result['success']);
        $this->assertSame('cross_clinic_forbidden', $result['code']);

        // User A remains designated admin
        $adminA = $this->service->getClinicAdministrator($clinicA['id']);
        $this->assertSame((int) $userA['id'], (int) $adminA['admin_user']['id']);
    }

    /**
     * Requirement 10: Assign administrator to existing clinic member with explicit audit actor (Finding 2 & 3)
     */
    public function testAssignAdministratorExistingMember(): void
    {
        $clinic = $this->createClinic('Assign Clinic');
        $user = $this->createUser('assign_candidate', 1);
        $staffRoleId = $this->getStaffRoleId();
        $adminRoleId = $this->getAdminRoleId();
        $actor = $this->getOrCreateSuperAdmin();
        $actorUserId = (int) $actor['id'];

        $this->addMembership($clinic['id'], $user['id'], $staffRoleId, 'active');

        $result = $this->service->assignAdministrator($clinic['id'], $user['id'], $actorUserId, 'Promotion to admin');

        $this->assertTrue($result['success']);

        // Verify designated admin setting
        $admin = $this->service->getClinicAdministrator($clinic['id']);
        $this->assertTrue($admin['has_admin']);
        $this->assertTrue($admin['is_healthy']);
        $this->assertSame((int) $user['id'], (int) $admin['admin_user']['id']);

        // Verify membership role upgraded
        $membership = $this->clinicUserModel
            ->where('clinic_id', $clinic['id'])
            ->where('user_id', $user['id'])
            ->first();
        $this->assertSame($adminRoleId, (int) $membership['role_id']);

        // Verify audit log record
        $audit = $this->db->table('control_plane_audits')
            ->where('event_type', 'clinic_admin_assigned')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();
        $this->assertNotNull($audit);
        $this->assertSame($actorUserId, (int) $audit['actor_user_id']);
        $meta = json_decode($audit['metadata_json'] ?? '{}', true);
        $this->assertSame((int) $clinic['id'], (int) $meta['clinic_id']);
        $this->assertSame((int) $user['id'], (int) $meta['target_user_id']);
    }

    /**
     * Requirement 11: Replace administrator with demotion preserves unrelated members (Finding 4 & 6)
     */
    public function testReplaceAdministratorWithDemotionPreservesUnrelatedMembers(): void
    {
        $clinic = $this->createClinic('Replace Demote Clinic');
        $user1 = $this->createUser('admin_current', 1);
        $user2 = $this->createUser('admin_replacement', 1);
        $user3 = $this->createUser('unrelated_staff', 1);

        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

        $this->addMembership($clinic['id'], $user1['id'], $adminRoleId, 'active');
        $this->setDesignatedAdmin($clinic['id'], $user1['id']);

        $this->addMembership($clinic['id'], $user2['id'], $staffRoleId, 'active');
        $this->addMembership($clinic['id'], $user3['id'], $staffRoleId, 'active');

        $actor = $this->getOrCreateSuperAdmin();
        $actorUserId = (int) $actor['id'];

        // Replace user 1 with user 2, expecting user 1 as current
        $result = $this->service->replaceAdministrator(
            clinicId: $clinic['id'],
            newUserId: $user2['id'],
            actorUserId: $actorUserId,
            previousAdminAction: 'demote_to_staff',
            reason: 'Leadership change',
            expectedCurrentAdminUserId: $user1['id']
        );

        $this->assertTrue($result['success']);

        // User 2 is now admin
        $admin = $this->service->getClinicAdministrator($clinic['id']);
        $this->assertSame((int) $user2['id'], (int) $admin['admin_user']['id']);

        // User 1 demoted to staff
        $m1 = $this->clinicUserModel->where('clinic_id', $clinic['id'])->where('user_id', $user1['id'])->first();
        $this->assertSame($staffRoleId, (int) $m1['role_id']);

        // User 3 completely untouched
        $m3 = $this->clinicUserModel->where('clinic_id', $clinic['id'])->where('user_id', $user3['id'])->first();
        $this->assertSame($staffRoleId, (int) $m3['role_id']);
        $this->assertSame('active', $m3['status']);
    }

    /**
     * Requirement 12: Replace administrator without demotion
     */
    public function testReplaceAdministratorWithoutDemotion(): void
    {
        $clinic = $this->createClinic('Replace Keep Role Clinic');
        $user1 = $this->createUser('admin_keep', 1);
        $user2 = $this->createUser('admin_new', 1);
        $actor = $this->getOrCreateSuperAdmin();

        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

        $this->addMembership($clinic['id'], $user1['id'], $adminRoleId, 'active');
        $this->setDesignatedAdmin($clinic['id'], $user1['id']);
        $this->addMembership($clinic['id'], $user2['id'], $staffRoleId, 'active');

        $result = $this->service->replaceAdministrator(
            clinicId: $clinic['id'],
            newUserId: $user2['id'],
            actorUserId: (int) $actor['id'],
            previousAdminAction: 'keep_role',
            expectedCurrentAdminUserId: $user1['id']
        );

        $this->assertTrue($result['success']);

        // User 1 still holds admin role
        $m1 = $this->clinicUserModel->where('clinic_id', $clinic['id'])->where('user_id', $user1['id'])->first();
        $this->assertSame($adminRoleId, (int) $m1['role_id']);
    }

    /**
     * Requirement 13: Replace administrator with same user is rejected
     */
    public function testReplaceAdministratorSameUserRejected(): void
    {
        $clinic = $this->createClinic('Same User Clinic');
        $user = $this->createUser('same_user', 1);
        $adminRoleId = $this->getAdminRoleId();
        $actor = $this->getOrCreateSuperAdmin();

        $this->addMembership($clinic['id'], $user['id'], $adminRoleId, 'active');
        $this->setDesignatedAdmin($clinic['id'], $user['id']);

        $result = $this->service->replaceAdministrator(
            $clinic['id'],
            $user['id'],
            actorUserId: (int) $actor['id'],
            expectedCurrentAdminUserId: (int) $user['id']
        );

        $this->assertFalse($result['success']);
        $this->assertSame('already_admin', $result['code']);
    }

    /**
     * Requirement 14: Concurrency / Stale replacement rejected (Finding 4)
     */
    public function testStaleReplacementConcurrencyRejected(): void
    {
        $clinic = $this->createClinic('Stale Concurrency Clinic');
        $user1 = $this->createUser('admin_original', 1);
        $user2 = $this->createUser('admin_concurrent', 1);
        $actor = $this->getOrCreateSuperAdmin();

        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

        $this->addMembership($clinic['id'], $user1['id'], $adminRoleId, 'active');
        $this->setDesignatedAdmin($clinic['id'], $user1['id']);
        $this->addMembership($clinic['id'], $user2['id'], $staffRoleId, 'active');

        // Operator passes wrong expected_admin_id (simulating stale browser tab)
        $wrongExpectedAdminId = 999999;
        $result = $this->service->replaceAdministrator(
            clinicId: $clinic['id'],
            newUserId: $user2['id'],
            actorUserId: (int) $actor['id'],
            expectedCurrentAdminUserId: $wrongExpectedAdminId
        );

        $this->assertFalse($result['success']);
        $this->assertSame('stale_state', $result['code']);

        // User 1 remains administrator
        $admin = $this->service->getClinicAdministrator($clinic['id']);
        $this->assertSame((int) $user1['id'], (int) $admin['admin_user']['id']);
    }

    /**
     * Requirement 15: Global account activation decoupled from restore (Finding 1)
     */
    public function testGlobalAccountActivationDecoupledFromRestore(): void
    {
        $clinic = $this->createClinic('Decoupled Restore Clinic');
        $user = $this->createUser('disabled_user_restore', 0); // Globally disabled
        $staffRoleId = $this->getStaffRoleId();
        $actor = $this->getOrCreateSuperAdmin();

        $this->addMembership($clinic['id'], $user['id'], $staffRoleId, 'inactive');
        $this->setDesignatedAdmin($clinic['id'], $user['id']);

        // Attempting to restore while account is deactivated must FAIL CLOSED
        $result = $this->service->restoreAdministrator(
            $clinic['id'],
            $user['id'],
            actorUserId: (int) $actor['id'],
            expectedDesignatedAdminUserId: (int) $user['id']
        );

        $this->assertFalse($result['success']);
        $this->assertSame('account_disabled', $result['code']);

        // User in DB MUST REMAIN active = 0 (never silently activated!)
        $dbUser = $this->userModel->find($user['id']);
        $this->assertSame(0, (int) $dbUser['active']);
    }

    /**
     * Requirement 16: Restore administrator succeeds when account is active (Finding 1 & 3)
     */
    public function testRestoreAdministratorSucceedsWhenUserActive(): void
    {
        $clinic = $this->createClinic('Restore Active Clinic');
        $user = $this->createUser('active_user_restore', 1);
        $staffRoleId = $this->getStaffRoleId();
        $adminRoleId = $this->getAdminRoleId();
        $actor = $this->getOrCreateSuperAdmin();

        $this->addMembership($clinic['id'], $user['id'], $staffRoleId, 'inactive');
        $this->setDesignatedAdmin($clinic['id'], $user['id']);

        $result = $this->service->restoreAdministrator(
            $clinic['id'],
            $user['id'],
            actorUserId: (int) $actor['id'],
            expectedDesignatedAdminUserId: (int) $user['id']
        );

        $this->assertTrue($result['success']);

        $admin = $this->service->getClinicAdministrator($clinic['id']);
        $this->assertTrue($admin['is_healthy']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_HEALTHY, $admin['state']);

        $membership = $this->clinicUserModel->where('clinic_id', $clinic['id'])->where('user_id', $user['id'])->first();
        $this->assertSame('active', $membership['status']);
        $this->assertSame($adminRoleId, (int) $membership['role_id']);
    }

    /**
     * Requirement 17: Cannot assign Super Admin user (Airlock doctrine)
     */
    public function testCannotAssignSuperAdmin(): void
    {
        $clinic = $this->createClinic('Superadmin Guard Clinic');
        $superAdmin = $this->getOrCreateSuperAdmin();
        $this->addMembership($clinic['id'], (int) $superAdmin['id'], $this->getStaffRoleId(), 'active');

        $result = $this->service->assignAdministrator($clinic['id'], (int) $superAdmin['id'], actorUserId: (int) $superAdmin['id']);

        $this->assertFalse($result['success']);
        $this->assertSame('superadmin_forbidden', $result['code']);
    }

    /**
     * Requirement 18: Cannot assign deactivated user
     */
    public function testCannotAssignDisabledUser(): void
    {
        $clinic = $this->createClinic('Inactive User Assign Clinic');
        $user = $this->createUser('inactive_candidate', 0);
        $actor = $this->getOrCreateSuperAdmin();

        $this->addMembership($clinic['id'], $user['id'], $this->getStaffRoleId(), 'active');

        $result = $this->service->assignAdministrator($clinic['id'], $user['id'], actorUserId: (int) $actor['id']);

        $this->assertFalse($result['success']);
        $this->assertSame('user_inactive', $result['code']);
    }

    /**
     * Requirement 19: PlanGuard fail-closed behavior (Finding 5)
     */
    public function testPlanGuardFailClosedBehavior(): void
    {
        $planGuard = new PlanGuard();

        // 1. Non-existent clinic returns false, never true (no-subscription fail-closed)
        $this->assertFalse($planGuard->isFeatureEnabled(999998, 'notifications.whatsapp.enabled', default: true));

        // 2. Clinic with a plan where the feature is explicitly disabled must return false even when
        //    the caller passed default: true (write-gated authoritative plan check, fail-closed).
        //    Note: MariaDB enforces a JSON CHECK constraint on features_json; corrupt JSON cannot be
        //    inserted at all. We instead insert a plan with valid JSON that explicitly disables the
        //    feature and assert that the guard never returns the caller-supplied default.
        $gatedClinic = $this->createClinic('Gated Plan Clinic');
        $ok = $this->db->table('plans')->insert([
            'name'          => 'Gated Test Plan ' . bin2hex(random_bytes(4)),
            'features_json' => json_encode(['notifications' => ['whatsapp' => ['enabled' => false]]]),
            'limits_json'   => '{}',
            'status'        => 'active',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $this->assertTrue($ok, 'Fixture: gated plan insert must succeed');
        $planId = (int) $this->db->insertID();
        $this->assertGreaterThan(0, $planId, 'Fixture: plan ID must be a valid auto-increment ID');

        $this->db->table('clinic_subscriptions')->insert([
            'clinic_id'  => $gatedClinic['id'],
            'plan_id'    => $planId,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Feature is explicitly false in the plan; must return false even when default: true
        $this->assertFalse(
            $planGuard->isFeatureEnabled($gatedClinic['id'], 'notifications.whatsapp.enabled', default: true),
            'PlanGuard must return false for explicitly disabled features regardless of caller default'
        );

        // 3. Clinic with no subscription returns false even when default: true
        $noSubClinic = $this->createClinic('No Subscription Clinic');
        $this->assertFalse(
            $planGuard->isFeatureEnabled($noSubClinic['id'], 'notifications.whatsapp.enabled', default: true),
            'PlanGuard must return false for clinics with no active subscription'
        );
    }

    /**
     * Requirement 20: Role resolution strictly by slug without magic numeric IDs (Finding 7)
     */
    public function testRoleResolutionStrictlyBySlug(): void
    {
        $adminRole = $this->service->resolveAdminRole();
        $this->assertIsArray($adminRole);
        $this->assertContains($adminRole['slug'], ['practice_manager', 'clinic_admin']);

        $staffRole = $this->service->resolveStaffRole();
        $this->assertIsArray($staffRole);
        $this->assertContains($staffRole['slug'], ['read_only', 'receptionist']);
    }

    /**
     * Requirement 21: Eligible users strictly scoped to clinic members (Finding 2)
     */
    public function testEligibleUsersRestrictedToClinicMembers(): void
    {
        $clinicA = $this->createClinic('Eligible Clinic A');
        $clinicB = $this->createClinic('Eligible Clinic B');

        $userA = $this->createUser('member_a', 1);
        $userB = $this->createUser('member_b', 1);

        $staffRoleId = $this->getStaffRoleId();
        $this->addMembership($clinicA['id'], $userA['id'], $staffRoleId, 'active');
        $this->addMembership($clinicB['id'], $userB['id'], $staffRoleId, 'active');

        $eligibleA = $this->service->getEligibleUsersForClinic($clinicA['id']);
        $eligibleIdsA = array_column($eligibleA, 'id');

        $this->assertContains((int) $userA['id'], $eligibleIdsA);
        $this->assertNotContains((int) $userB['id'], $eligibleIdsA);
    }

    /**
     * Requirement 22: Session revocation on user disablement
     */
    public function testSessionRevocationOnDisable(): void
    {
        $user = $this->createUser('temp_auth_session', 0); // Disabled!

        $session = session();
        $session->set([
            'user_id' => $user['id'],
            'identity' => $user['email'],
            'email' => $user['email'],
            'logged_in' => true,
        ]);

        $request = service('request');
        $authFilter = new AuthFilter();
        $response = $authFilter->before($request);

        // Must deny and terminate access (302 redirect for web or 401 for json/api)
        $this->assertInstanceOf(\CodeIgniter\HTTP\ResponseInterface::class, $response);
        $this->assertTrue(in_array($response->getStatusCode(), [302, 401], true));
        if ($response->getStatusCode() === 302) {
            $this->assertStringContainsString('auth/login', $response->getHeaderLine('Location'));
        }
    }

    /**
     * Requirement 23: Data integrity audit report runs cleanly across clinics
     */
    public function testDataIntegrityAuditReport(): void
    {
        $audit = $this->service->runDataIntegrityAudit();

        $this->assertIsArray($audit);
        $this->assertArrayHasKey('total_clinics', $audit);
        $this->assertArrayHasKey('healthy_count', $audit);
        $this->assertArrayHasKey('disabled_user_count', $audit);
        $this->assertArrayHasKey('inactive_membership_count', $audit);
        $this->assertArrayHasKey('missing_authority_count', $audit);
        $this->assertArrayHasKey('unassigned_count', $audit);
        $this->assertArrayHasKey('clinics', $audit);
    }

    /**
     * Finding 1: Explicit designation missing membership does NOT silently select another manager.
     */
    public function testExplicitDesignationMissingMembershipDoesNotSelectOtherManager(): void
    {
        $clinic = $this->createClinic('Authoritative Designation Clinic');
        $userA = $this->createUser('designated_user_a', 1);
        $userB = $this->createUser('other_manager_b', 1);
        $adminRoleId = $this->getAdminRoleId();

        // User B has active practice manager membership in this clinic
        $this->addMembership($clinic['id'], $userB['id'], $adminRoleId, 'active');

        // User A is explicitly designated in settings, but has NO clinic membership at all
        $this->setDesignatedAdmin($clinic['id'], $userA['id']);

        $admin = $this->service->getClinicAdministrator($clinic['id']);

        // Explicit designation is authoritative: MUST report degraded state for User A
        $this->assertTrue($admin['has_admin']);
        $this->assertFalse($admin['is_healthy']);
        $this->assertSame(ClinicAdminLifecycleService::STATE_INACTIVE_MEMBERSHIP, $admin['state']);
        $this->assertSame('designated', $admin['designation_source']);
        $this->assertSame((int) $userA['id'], (int) $admin['admin_user']['id']);
        $this->assertNull($admin['membership']);
        $this->assertFalse($admin['membership_active']);

        // MUST NOT silently report User B or healthy!
        $this->assertNotSame((int) $userB['id'], (int) $admin['admin_user']['id']);
    }

    /**
     * Finding 2: Inactive memberships are excluded from getEligibleUsersForClinic.
     */
    public function testInactiveMembershipExcludedFromEligibleUsers(): void
    {
        $clinic = $this->createClinic('Eligible Inactive Filter Clinic');
        $userActive = $this->createUser('active_member', 1);
        $userInactive = $this->createUser('inactive_member', 1);
        $staffRoleId = $this->getStaffRoleId();

        $this->addMembership($clinic['id'], $userActive['id'], $staffRoleId, 'active');
        $this->addMembership($clinic['id'], $userInactive['id'], $staffRoleId, 'inactive');

        $eligible = $this->service->getEligibleUsersForClinic($clinic['id']);
        $eligibleIds = array_column($eligible, 'id');

        $this->assertContains((int) $userActive['id'], $eligibleIds);
        $this->assertNotContains((int) $userInactive['id'], $eligibleIds);
    }

    /**
     * Finding 2: assignAdministrator rejects inactive candidate with zero mutation.
     */
    public function testAssignAdministratorRejectsInactiveMembershipWithZeroMutation(): void
    {
        $clinic = $this->createClinic('Assign Inactive Reject Clinic');
        $user = $this->createUser('inactive_assignee', 1);
        $staffRoleId = $this->getStaffRoleId();
        $actor = $this->getOrCreateSuperAdmin();

        $membershipId = $this->addMembership($clinic['id'], $user['id'], $staffRoleId, 'inactive');

        $result = $this->service->assignAdministrator($clinic['id'], $user['id'], (int) $actor['id']);

        $this->assertFalse($result['success']);
        $this->assertSame('inactive_membership_forbidden', $result['code']);

        // Assert ZERO mutation
        $membership = $this->clinicUserModel->find($membershipId);
        $this->assertSame('inactive', $membership['status']);
        $this->assertSame($staffRoleId, (int) $membership['role_id']);

        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertNull($setting);
    }

    /**
     * Finding 2: replaceAdministrator rejects inactive replacement candidate with zero mutation.
     */
    public function testReplaceAdministratorRejectsInactiveCandidateWithZeroMutation(): void
    {
        $clinic = $this->createClinic('Replace Inactive Reject Clinic');
        $user1 = $this->createUser('current_admin', 1);
        $user2 = $this->createUser('inactive_replacement', 1);
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();
        $actor = $this->getOrCreateSuperAdmin();

        $m1Id = $this->addMembership($clinic['id'], $user1['id'], $adminRoleId, 'active');
        $this->setDesignatedAdmin($clinic['id'], $user1['id']);
        $m2Id = $this->addMembership($clinic['id'], $user2['id'], $staffRoleId, 'inactive');

        $result = $this->service->replaceAdministrator(
            clinicId: $clinic['id'],
            newUserId: $user2['id'],
            actorUserId: (int) $actor['id'],
            expectedCurrentAdminUserId: $user1['id']
        );

        $this->assertFalse($result['success']);
        $this->assertSame('inactive_membership_forbidden', $result['code']);

        // Assert ZERO mutation
        $m1 = $this->clinicUserModel->find($m1Id);
        $this->assertSame('active', $m1['status']);
        $this->assertSame($adminRoleId, (int) $m1['role_id']);

        $m2 = $this->clinicUserModel->find($m2Id);
        $this->assertSame('inactive', $m2['status']);
        $this->assertSame($staffRoleId, (int) $m2['role_id']);

        $admin = $this->service->getClinicAdministrator($clinic['id']);
        $this->assertSame((int) $user1['id'], (int) $admin['admin_user']['id']);
    }

    /**
     * Finding 3: assignAdministrator must not double as replace; rejected when already assigned with zero mutation.
     */
    public function testAssignAdministratorRejectsAlreadyAssignedClinicWithZeroMutation(): void
    {
        $clinic = $this->createClinic('Already Assigned Clinic');
        $user1 = $this->createUser('existing_admin', 1);
        $user2 = $this->createUser('new_assignee', 1);
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();
        $actor = $this->getOrCreateSuperAdmin();

        $m1Id = $this->addMembership($clinic['id'], $user1['id'], $adminRoleId, 'active');
        $this->setDesignatedAdmin($clinic['id'], $user1['id']);
        $m2Id = $this->addMembership($clinic['id'], $user2['id'], $staffRoleId, 'active');

        // Assigning to a clinic that already has an admin must return already_assigned
        $result = $this->service->assignAdministrator($clinic['id'], $user2['id'], (int) $actor['id']);

        $this->assertFalse($result['success']);
        $this->assertSame('already_assigned', $result['code']);

        // Assert ZERO mutation
        $m1 = $this->clinicUserModel->find($m1Id);
        $this->assertSame($adminRoleId, (int) $m1['role_id']);

        $m2 = $this->clinicUserModel->find($m2Id);
        $this->assertSame($staffRoleId, (int) $m2['role_id']);

        $admin = $this->service->getClinicAdministrator($clinic['id']);
        $this->assertSame((int) $user1['id'], (int) $admin['admin_user']['id']);
    }

    /**
     * Finding 4: restoreAdministrator rejects when no designated admin setting exists with zero mutation.
     */
    public function testRestoreAdministratorRejectsWhenNoDesignatedAdminWithZeroMutation(): void
    {
        $clinic = $this->createClinic('No Designated Admin Clinic');
        $user = $this->createUser('member_no_designation', 1);
        $staffRoleId = $this->getStaffRoleId();
        $actor = $this->getOrCreateSuperAdmin();

        $mId = $this->addMembership($clinic['id'], $user['id'], $staffRoleId, 'active');

        $result = $this->service->restoreAdministrator(
            $clinic['id'],
            $user['id'],
            (int) $actor['id'],
            expectedDesignatedAdminUserId: (int) $user['id']
        );

        $this->assertFalse($result['success']);
        $this->assertSame('no_designated_admin', $result['code']);

        // Assert ZERO mutation
        $m = $this->clinicUserModel->find($mId);
        $this->assertSame($staffRoleId, (int) $m['role_id']);

        $setting = $this->settingsModel
            ->where('clinic_id', $clinic['id'])
            ->where('setting_key', ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN)
            ->first();
        $this->assertNull($setting);
    }

    /**
     * Finding 4: restoreAdministrator rejects target mismatch with zero mutation.
     */
    public function testRestoreAdministratorRejectsTargetMismatchWithZeroMutation(): void
    {
        $clinic = $this->createClinic('Target Mismatch Clinic');
        $user1 = $this->createUser('designated_user_1', 1);
        $user2 = $this->createUser('arbitrary_user_2', 1);
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();
        $actor = $this->getOrCreateSuperAdmin();

        // User 1 is designated, degraded (inactive membership)
        $m1Id = $this->addMembership($clinic['id'], $user1['id'], $adminRoleId, 'inactive');
        $this->setDesignatedAdmin($clinic['id'], $user1['id']);

        // User 2 is ordinary staff member
        $m2Id = $this->addMembership($clinic['id'], $user2['id'], $staffRoleId, 'active');

        // Operator attempts to restore User 2 on a clinic designated for User 1
        $result = $this->service->restoreAdministrator(
            $clinic['id'],
            $user2['id'],
            (int) $actor['id'],
            expectedDesignatedAdminUserId: (int) $user1['id']
        );

        $this->assertFalse($result['success']);
        $this->assertSame('target_mismatch', $result['code']);

        // Assert ZERO mutation
        $m1 = $this->clinicUserModel->find($m1Id);
        $this->assertSame('inactive', $m1['status']);
        $this->assertSame($adminRoleId, (int) $m1['role_id']);

        $m2 = $this->clinicUserModel->find($m2Id);
        $this->assertSame('active', $m2['status']);
        $this->assertSame($staffRoleId, (int) $m2['role_id']);
    }

    /**
     * Finding 4: restoreAdministrator rejects stale expected designated admin with zero mutation.
     */
    public function testRestoreAdministratorRejectsStaleExpectedDesignatedAdminWithZeroMutation(): void
    {
        $clinic = $this->createClinic('Stale Restore Clinic');
        $user = $this->createUser('degraded_admin_user', 1);
        $adminRoleId = $this->getAdminRoleId();
        $actor = $this->getOrCreateSuperAdmin();

        $mId = $this->addMembership($clinic['id'], $user['id'], $adminRoleId, 'inactive');
        $this->setDesignatedAdmin($clinic['id'], $user['id']);

        // Passing wrong expected designated admin ID
        $wrongExpectedId = 888888;
        $result = $this->service->restoreAdministrator(
            clinicId: $clinic['id'],
            userId: $user['id'],
            actorUserId: (int) $actor['id'],
            expectedDesignatedAdminUserId: $wrongExpectedId
        );

        $this->assertFalse($result['success']);
        $this->assertSame('stale_state', $result['code']);

        // Assert ZERO mutation
        $m = $this->clinicUserModel->find($mId);
        $this->assertSame('inactive', $m['status']);
    }

    /**
     * Finding 5: replaceAdministrator handles previousAdminAction allowlist (demote_to_staff, keep_role, deactivate_membership).
     */
    public function testReplaceAdministratorActionAllowlist(): void
    {
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();
        $actor = $this->getOrCreateSuperAdmin();

        // 1. Action: deactivate_membership
        $clinic = $this->createClinic('Deactivate Prev Admin Clinic');
        $user1 = $this->createUser('prev_admin_deact', 1);
        $user2 = $this->createUser('new_admin_deact', 1);

        $m1Id = $this->addMembership($clinic['id'], $user1['id'], $adminRoleId, 'active');
        $this->setDesignatedAdmin($clinic['id'], $user1['id']);
        $this->addMembership($clinic['id'], $user2['id'], $staffRoleId, 'active');

        $res = $this->service->replaceAdministrator(
            clinicId: $clinic['id'],
            newUserId: $user2['id'],
            actorUserId: (int) $actor['id'],
            previousAdminAction: 'deactivate_membership',
            expectedCurrentAdminUserId: (int) $user1['id']
        );
        $this->assertTrue($res['success']);

        $m1 = $this->clinicUserModel->find($m1Id);
        $this->assertSame('inactive', $m1['status']);

        // 2. Action: keep_role
        $clinic2 = $this->createClinic('Keep Prev Role Clinic');
        $uA = $this->createUser('prev_admin_keep', 1);
        $uB = $this->createUser('new_admin_keep', 1);

        $mAId = $this->addMembership($clinic2['id'], $uA['id'], $adminRoleId, 'active');
        $this->setDesignatedAdmin($clinic2['id'], $uA['id']);
        $this->addMembership($clinic2['id'], $uB['id'], $staffRoleId, 'active');

        $res2 = $this->service->replaceAdministrator(
            clinicId: $clinic2['id'],
            newUserId: $uB['id'],
            actorUserId: (int) $actor['id'],
            previousAdminAction: 'keep_role',
            expectedCurrentAdminUserId: (int) $uA['id']
        );
        $this->assertTrue($res2['success']);

        $mA = $this->clinicUserModel->find($mAId);
        $this->assertSame('active', $mA['status']);
        $this->assertSame($adminRoleId, (int) $mA['role_id']);
    }

    /**
     * Finding 6: Zero mutation on invalid user ID in assignAdministrator.
     */
    public function testAssignAdministratorRejectsNonExistentUserWithZeroMutation(): void
    {
        $clinic = $this->createClinic('Non Existent User Clinic');
        $actor = $this->getOrCreateSuperAdmin();

        $result = $this->service->assignAdministrator($clinic['id'], 999999, (int) $actor['id']);

        $this->assertFalse($result['success']);
        $this->assertSame('user_not_found', $result['code']);

        $admin = $this->service->getClinicAdministrator($clinic['id']);
        $this->assertFalse($admin['has_admin']);
    }

    /**
     * Finding 6: Zero mutation on invalid clinic ID in replaceAdministrator.
     */
    public function testReplaceAdministratorRejectsNonExistentClinicWithZeroMutation(): void
    {
        $user = $this->createUser('orphan_user', 1);
        $actor = $this->getOrCreateSuperAdmin();

        $result = $this->service->replaceAdministrator(999999, $user['id'], (int) $actor['id'], expectedCurrentAdminUserId: 123);

        $this->assertFalse($result['success']);
        $this->assertSame('clinic_not_found', $result['code']);
    }

    /**
     * Delta Finding 2: Replace administrator with demote_to_staff updates role to staff but preserves inactive membership status.
     */
    public function testReplaceAdminDemoteToStaffPreservesInactiveMembershipStatus(): void
    {
        $clinic = $this->createClinic('Demote Inactive Unit Clinic');
        $prevAdmin = $this->createUser('prev_inact_u', 1);
        $newAdmin = $this->createUser('new_act_u', 1);
        $actor = $this->getOrCreateSuperAdmin();
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

        $prevMId = $this->addMembership($clinic['id'], $prevAdmin['id'], $adminRoleId, 'inactive');
        $newMId = $this->addMembership($clinic['id'], $newAdmin['id'], $staffRoleId, 'active');

        $this->db->table('settings')->insert([
            'clinic_id' => $clinic['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $prevAdmin['id'],
            'setting_type' => 'number',
        ]);

        $result = $this->service->replaceAdministrator(
            $clinic['id'],
            $newAdmin['id'],
            (int) $actor['id'],
            'demote_to_staff',
            reason: 'Testing inactive status preservation during demotion',
            expectedCurrentAdminUserId: $prevAdmin['id']
        );

        $this->assertTrue($result['success'], 'Replacement must succeed');
        $this->assertSame('admin_replaced', $result['code']);

        // Assert previous admin role is demoted to staff, BUT status remains inactive
        $prevM = $this->clinicUserModel->find($prevMId);
        $this->assertSame($staffRoleId, (int) $prevM['role_id'], 'Previous admin role must change to staff');
        $this->assertSame('inactive', $prevM['status'], 'Previous admin membership status MUST remain inactive');

        // Assert new admin is promoted to admin role and has active status
        $newM = $this->clinicUserModel->find($newMId);
        $this->assertSame($adminRoleId, (int) $newM['role_id'], 'New admin role must be admin');
        $this->assertSame('active', $newM['status'], 'New admin membership must be active');

        // Assert designation updated
        $designatedId = $this->service->getDesignatedAdminUserId($clinic['id']);
        $this->assertSame((int) $newAdmin['id'], $designatedId);
    }

    /**
     * Delta Finding 2: Replace administrator with keep_role preserves status for both active and inactive previous admins.
     */
    public function testReplaceAdminKeepRolePreservesStatusForInactiveAndActiveAdmins(): void
    {
        $actor = $this->getOrCreateSuperAdmin();
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

        // 1. Inactive previous admin with keep_role
        $clinic1 = $this->createClinic('Keep Role Inactive Clinic');
        $prev1 = $this->createUser('prev_keep_inact', 1);
        $new1 = $this->createUser('new_keep_inact', 1);
        $prev1MId = $this->addMembership($clinic1['id'], $prev1['id'], $adminRoleId, 'inactive');
        $this->addMembership($clinic1['id'], $new1['id'], $staffRoleId, 'active');

        $this->db->table('settings')->insert([
            'clinic_id' => $clinic1['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $prev1['id'],
            'setting_type' => 'number',
        ]);

        $res1 = $this->service->replaceAdministrator(
            $clinic1['id'],
            $new1['id'],
            (int) $actor['id'],
            'keep_role',
            expectedCurrentAdminUserId: $prev1['id']
        );
        $this->assertTrue($res1['success']);
        $prev1M = $this->clinicUserModel->find($prev1MId);
        $this->assertSame($adminRoleId, (int) $prev1M['role_id'], 'Role must remain admin role');
        $this->assertSame('inactive', $prev1M['status'], 'Status must remain inactive');

        // 2. Active previous admin with keep_role
        $clinic2 = $this->createClinic('Keep Role Active Clinic');
        $prev2 = $this->createUser('prev_keep_act', 1);
        $new2 = $this->createUser('new_keep_act', 1);
        $prev2MId = $this->addMembership($clinic2['id'], $prev2['id'], $adminRoleId, 'active');
        $this->addMembership($clinic2['id'], $new2['id'], $staffRoleId, 'active');

        $this->db->table('settings')->insert([
            'clinic_id' => $clinic2['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $prev2['id'],
            'setting_type' => 'number',
        ]);

        $res2 = $this->service->replaceAdministrator(
            $clinic2['id'],
            $new2['id'],
            (int) $actor['id'],
            'keep_role',
            expectedCurrentAdminUserId: $prev2['id']
        );
        $this->assertTrue($res2['success']);
        $prev2M = $this->clinicUserModel->find($prev2MId);
        $this->assertSame($adminRoleId, (int) $prev2M['role_id'], 'Role must remain admin role');
        $this->assertSame('active', $prev2M['status'], 'Status must remain active');
    }

    /**
     * Delta Finding 2: Replace administrator with deactivate_membership sets previous admin status to inactive.
     */
    public function testReplaceAdminDeactivateMembershipSetsInactive(): void
    {
        $clinic = $this->createClinic('Deactivate Prev Clinic');
        $prevAdmin = $this->createUser('prev_deact', 1);
        $newAdmin = $this->createUser('new_deact', 1);
        $actor = $this->getOrCreateSuperAdmin();
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

        $prevMId = $this->addMembership($clinic['id'], $prevAdmin['id'], $adminRoleId, 'active');
        $this->addMembership($clinic['id'], $newAdmin['id'], $staffRoleId, 'active');

        $this->db->table('settings')->insert([
            'clinic_id' => $clinic['id'],
            'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $prevAdmin['id'],
            'setting_type' => 'number',
        ]);

        $result = $this->service->replaceAdministrator(
            $clinic['id'],
            $newAdmin['id'],
            (int) $actor['id'],
            'deactivate_membership',
            expectedCurrentAdminUserId: $prevAdmin['id']
        );

        $this->assertTrue($result['success']);
        $prevM = $this->clinicUserModel->find($prevMId);
        $this->assertSame('inactive', $prevM['status'], 'Previous admin membership must be deactivated to inactive');
    }

    /**
     * Delta Finding 5: Bulk resolver getClinicAdministrators has bounded query count across multiple clinics and memberships.
     */
    public function testBulkResolverHasBoundedQueryCount(): void
    {
        $adminRoleId = $this->getAdminRoleId();
        $staffRoleId = $this->getStaffRoleId();

        // Create 4 clinics, each with 3 candidate memberships (12 members total, all needing fallback resolution)
        $clinicIds = [];
        for ($c = 1; $c <= 4; $c++) {
            $clinic = $this->createClinic("Bulk Clinic {$c}");
            $clinicIds[] = (int) $clinic['id'];

            // 1 active manager
            $userA = $this->createUser("c{$c}_mgr", 1);
            $this->addMembership($clinic['id'], $userA['id'], $adminRoleId, 'active');

            // 2 staff members
            $userB = $this->createUser("c{$c}_stf1", 1);
            $this->addMembership($clinic['id'], $userB['id'], $staffRoleId, 'active');
            $userC = $this->createUser("c{$c}_stf2", 1);
            $this->addMembership($clinic['id'], $userC['id'], $staffRoleId, 'active');
        }

        // Measure query count during bulk resolution using CI4 DBQuery event listener
        $executedQueries = 0;
        $listener = static function () use (&$executedQueries) {
            $executedQueries++;
        };
        \CodeIgniter\Events\Events::on('DBQuery', $listener);

        $results = $this->service->getClinicAdministrators($clinicIds);

        // Bounded verification: 4 clinics with 12 members should execute <= 5 batch queries total
        // (1 settings query, 1 fallback clinic_users query with join, 1 batch roles query, 1 batch users query)
        // Definitely NOT an N+1 query loop per candidate member.
        $this->assertCount(4, $results);
        $this->assertLessThanOrEqual(5, $executedQueries, "Bulk query count ({$executedQueries}) must be strictly bounded");

        foreach ($clinicIds as $cid) {
            $this->assertTrue($results[$cid]['has_admin']);
            $this->assertTrue($results[$cid]['is_fallback']);
            $this->assertNotEmpty($results[$cid]['admin_user']);
        }
    }

    /**
     * Delta Finding 5: Deterministic tie-breaking by clinic_users.id ASC when created_at timestamps are identical.
     */
    public function testBulkResolverDeterministicTieBreakingById(): void
    {
        $clinic = $this->createClinic('Tie Breaker Clinic');
        $adminRoleId = $this->getAdminRoleId();
        $sameTimestamp = '2026-01-01 10:00:00';

        // Candidate 1: inserted first (lower id)
        $user1 = $this->createUser('candidate_one', 1);
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user1['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => $sameTimestamp,
        ]);
        $cand1Id = (int) $this->db->insertID();

        // Candidate 2: inserted second (higher id)
        $user2 = $this->createUser('candidate_two', 1);
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinic['id'],
            'user_id' => $user2['id'],
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => $sameTimestamp,
        ]);
        $cand2Id = (int) $this->db->insertID();

        $this->assertLessThan($cand2Id, $cand1Id, 'Candidate 1 must have lower membership ID');

        // 1. Test getClinicAdministrators (bulk resolver)
        $bulkResults = $this->service->getClinicAdministrators([$clinic['id']]);
        $this->assertTrue($bulkResults[$clinic['id']]['has_admin']);
        $this->assertSame((int) $user1['id'], (int) $bulkResults[$clinic['id']]['admin_user']['id'], 'Bulk resolver must choose lower membership ID');

        // 2. Test getClinicAdministrator (single resolver using findEffectivePrimaryMembership)
        $singleResult = $this->service->getClinicAdministrator($clinic['id']);
        $this->assertTrue($singleResult['has_admin']);
        $this->assertSame((int) $user1['id'], (int) $singleResult['admin_user']['id'], 'Single resolver must choose lower membership ID');
    }
}

