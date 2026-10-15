<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\ApiRuntimeContext;
use App\Services\PermissionService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;

/**
 * AGY-implemented PR1 correction regressions, independently reviewed by Codex:
 * 1. ControlPlane\Users::addMembership strict scalar positive integer parsing,
 *    controlled redirect/flash behavior, and routed execution.
 * 2. TenantFilter and AppApiTenant canonical getTenantRoleId enforcement, fail-closed
 *    invalid role blocking before controller, and preserved web impersonation.
 */
final class PR1ReviewCorrectionRegressionTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $db;
    private string $prefix;
    private int $platformAdminId;
    private string $platformAdminEmail;
    private int $targetUserId;
    private string $targetUserEmail;
    private int $clinicA;
    private int $clinicB;
    private int $planId;
    private int $validRoleClinicA;
    private int $validRoleClinicB;
    private int $inactiveRole;
    private int $spoofedSuperAdminRole;

    protected function setUp(): void
    {
        parent::setUp();
        ApiRuntimeContext::clear();
        session()->destroy();

        $this->db = Database::connect();
        $this->assertSame(0, $this->db->transDepth);

        $this->prefix = 'pr1corr_' . bin2hex(random_bytes(5));

        // Create Platform Super Admin
        $this->platformAdminEmail = $this->prefix . '_admin@example.test';
        $this->db->table('users')->insert([
            'ip_address' => '127.0.0.1',
            'username' => $this->prefix . '_admin',
            'email' => $this->platformAdminEmail,
            'password' => password_hash('Synthetic-pass-42', PASSWORD_BCRYPT),
            'first_name' => 'Platform',
            'last_name' => 'Admin',
            'active' => 1,
            'created_on' => time(),
        ]);
        $this->platformAdminId = (int) $this->db->insertID();

        $adminGroup = $this->db->table('groups')->where('name', 'admin')->get()->getRowArray();
        if ($adminGroup) {
            $this->db->table('users_groups')->insert([
                'user_id' => $this->platformAdminId,
                'group_id' => (int) $adminGroup['id'],
            ]);
        }

        $superAdminRole = (new PermissionService($this->db))->getSuperAdminRoleId();
        if ($superAdminRole) {
            $this->db->table('user_roles')->insert([
                'user_id' => $this->platformAdminId,
                'role_id' => $superAdminRole,
                'is_active' => 1,
            ]);
        }

        // Create Target User
        $this->targetUserEmail = $this->prefix . '_target@example.test';
        $this->db->table('users')->insert([
            'ip_address' => '127.0.0.1',
            'username' => $this->prefix . '_target',
            'email' => $this->targetUserEmail,
            'password' => password_hash('Synthetic-pass-42', PASSWORD_BCRYPT),
            'first_name' => 'Target',
            'last_name' => 'User',
            'active' => 1,
            'created_on' => time(),
        ]);
        $this->targetUserId = (int) $this->db->insertID();

        $membersGroup = $this->db->table('groups')->where('name', 'members')->get()->getRowArray();
        if ($membersGroup) {
            $this->db->table('users_groups')->insert([
                'user_id' => $this->targetUserId,
                'group_id' => (int) $membersGroup['id'],
            ]);
        }

        // Create Clinics A and B
        $this->db->table('clinics')->insert([
            'name' => $this->prefix . ' Clinic A',
            'slug' => $this->prefix . '-clinic-a',
            'uuid' => bin2hex(random_bytes(16)),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->clinicA = (int) $this->db->insertID();

        $this->db->table('clinics')->insert([
            'name' => $this->prefix . ' Clinic B',
            'slug' => $this->prefix . '-clinic-b',
            'uuid' => bin2hex(random_bytes(16)),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->clinicB = (int) $this->db->insertID();

        // Create Plan and Subscriptions
        $this->db->table('plans')->insert([
            'name' => $this->prefix . ' Plan',
            'status' => 'active',
            'limits_json' => '{"users_max":50}',
            'features_json' => '{}',
        ]);
        $this->planId = (int) $this->db->insertID();

        foreach ([$this->clinicA, $this->clinicB] as $cid) {
            $this->db->table('clinic_subscriptions')->insert([
                'clinic_id' => $cid,
                'plan_id' => $this->planId,
                'status' => 'active',
                'start_at' => date('Y-m-d H:i:s', time() - 3600),
                'end_at' => null,
            ]);
        }

        // Create Roles
        // Valid custom role for Clinic A
        $this->db->table('roles')->insert([
            'name' => $this->prefix . ' Role A',
            'slug' => $this->prefix . '_role_a',
            'is_active' => 1,
            'is_system' => 0,
            'clinic_id' => $this->clinicA,
            'created_by' => $this->platformAdminId,
        ]);
        $this->validRoleClinicA = (int) $this->db->insertID();

        // Valid custom role for Clinic B (foreign to Clinic A)
        $this->db->table('roles')->insert([
            'name' => $this->prefix . ' Role B',
            'slug' => $this->prefix . '_role_b',
            'is_active' => 1,
            'is_system' => 0,
            'clinic_id' => $this->clinicB,
            'created_by' => $this->platformAdminId,
        ]);
        $this->validRoleClinicB = (int) $this->db->insertID();

        // Inactive role
        $this->db->table('roles')->insert([
            'name' => $this->prefix . ' Inactive Role',
            'slug' => $this->prefix . '_role_inactive',
            'is_active' => 0,
            'is_system' => 0,
            'clinic_id' => $this->clinicA,
            'created_by' => $this->platformAdminId,
        ]);
        $this->inactiveRole = (int) $this->db->insertID();

        // Spoofed super_admin role
        $this->db->table('roles')->insert([
            'name' => $this->prefix . ' Spoofed SuperAdmin',
            'slug' => 'super_admin',
            'is_active' => 1,
            'is_system' => 0,
            'clinic_id' => $this->clinicA,
            'created_by' => $this->platformAdminId,
        ]);
        $this->spoofedSuperAdminRole = (int) $this->db->insertID();
    }

    protected function tearDown(): void
    {
        ApiRuntimeContext::clear();
        session()->destroy();

        if ($this->db) {
            while ($this->db->transDepth > 0) {
                $this->db->transRollback();
            }
            $this->db->resetTransStatus();

            if (isset($this->clinicA, $this->clinicB)) {
                $clinicIds = [$this->clinicA, $this->clinicB];
                foreach (['settings', 'clinic_subscriptions', 'clinic_users', 'patients'] as $t) {
                    $this->db->table($t)->whereIn('clinic_id', $clinicIds)->delete();
                }
            }

            if (isset($this->prefix)) {
                $ownedUsers = $this->db->table('users')->select('id')->like('username', $this->prefix, 'after')->get()->getResultArray();
                $userIds = array_map(static fn($r) => (int) $r['id'], $ownedUsers);
                if ($userIds) {
                    foreach (['api_tokens', 'user_roles', 'users_groups', 'user_permissions', 'control_plane_audits'] as $t) {
                        if ($t === 'control_plane_audits') {
                            $this->db->table($t)->whereIn('actor_user_id', $userIds)->delete();
                        } else {
                            $this->db->table($t)->whereIn('user_id', $userIds)->delete();
                        }
                    }
                    $roles = $this->db->table('roles')->select('id')->whereIn('created_by', $userIds)->get()->getResultArray();
                    $roleIds = array_column($roles, 'id');
                    if ($roleIds) {
                        $this->db->table('role_permissions')->whereIn('role_id', $roleIds)->delete();
                        $this->db->table('roles')->whereIn('id', $roleIds)->delete();
                    }
                    $this->db->table('users')->whereIn('id', $userIds)->delete();
                }
                if (isset($this->clinicA, $this->clinicB)) {
                    $this->db->table('clinics')->whereIn('id', [$this->clinicA, $this->clinicB])->delete();
                }
                if (isset($this->planId)) {
                    $this->db->table('plans')->where('id', $this->planId)->delete();
                }
            }
        }

        parent::tearDown();
    }

    private function platformSession(): array
    {
        return [
            'identity' => $this->platformAdminEmail,
            'user_id' => $this->platformAdminId,
            'email' => $this->platformAdminEmail,
            'global_mode' => true,
            'active_clinic_id' => null,
            'logged_in' => true,
            'last_check' => time(),
        ];
    }

    private function postWithCsrf(string $path, array $data, array $sessionData = [])
    {
        $security = service('security');
        $securityConfig = config('Security');
        $tokenName = (string) $securityConfig->tokenName;
        $tokenHash = (string) $security->getHash();
        $cookieName = (string) $securityConfig->cookieName;

        $mergedData = array_merge($data, [$tokenName => $tokenHash]);
        $mergedHeaders = [
            'Cookie' => $cookieName . '=' . $tokenHash,
            'X-CSRF-TOKEN' => $tokenHash,
            'HTTP_REFERER' => base_url('controlplane/users/' . $this->targetUserId),
        ];

        return $this
            ->withSession($sessionData)
            ->withHeaders($mergedHeaders)
            ->post($path, $mergedData);
    }

    // =========================================================================
    // Item 1: ControlPlane\Users::addMembership Routed POST Tests
    // =========================================================================

    public function testAddMembershipSuccessfullyLinksUserToClinicWithValidInputs(): void
    {
        $route = '/controlplane/users/' . $this->targetUserId . '/memberships/add';
        $payload = [
            'clinic_id' => (string) $this->clinicA,
            'role_id' => (string) $this->validRoleClinicA,
        ];

        $response = $this->postWithCsrf($route, $payload, $this->platformSession());

        $response->assertStatus(302);
        $response->assertRedirectTo(base_url('controlplane/users/' . $this->targetUserId));
        $this->assertSame(lang('UserRole.membershipSaved'), session()->getFlashdata('success'));

        // Verify database state
        $row = $this->db->table('clinic_users')
            ->where('clinic_id', $this->clinicA)
            ->where('user_id', $this->targetUserId)
            ->get()
            ->getRowArray();

        $this->assertNotNull($row);
        $this->assertSame('active', $row['status']);
        $this->assertSame($this->validRoleClinicA, (int) $row['role_id']);
    }

    public function testAddMembershipRejectsMalformedClinicIdAndRoleId(): void
    {
        $route = '/controlplane/users/' . $this->targetUserId . '/memberships/add';

        $malformedCases = [
            'array clinic_id' => ['clinic_id' => [$this->clinicA], 'role_id' => (string) $this->validRoleClinicA],
            'array role_id' => ['clinic_id' => (string) $this->clinicA, 'role_id' => [$this->validRoleClinicA]],
            'leading whitespace' => ['clinic_id' => ' ' . $this->clinicA, 'role_id' => (string) $this->validRoleClinicA],
            'trailing whitespace' => ['clinic_id' => (string) $this->clinicA, 'role_id' => $this->validRoleClinicA . ' '],
            'whitespace garbage' => ['clinic_id' => '   ', 'role_id' => (string) $this->validRoleClinicA],
            'decimal clinic_id' => ['clinic_id' => $this->clinicA . '.5', 'role_id' => (string) $this->validRoleClinicA],
            'decimal role_id' => ['clinic_id' => (string) $this->clinicA, 'role_id' => $this->validRoleClinicA . '.0'],
            'scientific notation' => ['clinic_id' => '1e2', 'role_id' => (string) $this->validRoleClinicA],
            'positive sign' => ['clinic_id' => '+' . $this->clinicA, 'role_id' => (string) $this->validRoleClinicA],
            'negative sign' => ['clinic_id' => '-' . $this->clinicA, 'role_id' => (string) $this->validRoleClinicA],
            'overflow integer' => ['clinic_id' => '999999999999999999999999999999999999', 'role_id' => (string) $this->validRoleClinicA],
            'alphabetic suffix' => ['clinic_id' => $this->clinicA . 'abc', 'role_id' => (string) $this->validRoleClinicA],
            'zero clinic_id' => ['clinic_id' => '0', 'role_id' => (string) $this->validRoleClinicA],
            'zero role_id' => ['clinic_id' => (string) $this->clinicA, 'role_id' => '0'],
            'missing clinic_id' => ['role_id' => (string) $this->validRoleClinicA],
            'missing role_id' => ['clinic_id' => (string) $this->clinicA],
            'empty strings' => ['clinic_id' => '', 'role_id' => ''],
        ];

        foreach ($malformedCases as $label => $payload) {
            $response = $this->postWithCsrf($route, $payload, $this->platformSession());

            $response->assertStatus(302, 'Failed for case: ' . $label);
            $this->assertSame(lang('UserRole.membershipClinicRoleRequired'), session()->getFlashdata('error'), 'Expected error flash for case: ' . $label);

            // Ensure no membership was inserted
            $count = $this->db->table('clinic_users')
                ->where('user_id', $this->targetUserId)
                ->countAllResults();
            $this->assertSame(0, $count, 'No database mutation expected for case: ' . $label);
        }
    }

    public function testAddMembershipPreventsDuplicateActiveMembershipWithoutIdentityChange(): void
    {
        // Setup initial active membership
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $this->clinicA,
            'user_id' => $this->targetUserId,
            'role_id' => $this->validRoleClinicA,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $route = '/controlplane/users/' . $this->targetUserId . '/memberships/add';
        $payload = [
            'clinic_id' => (string) $this->clinicA,
            'role_id' => (string) $this->validRoleClinicA,
        ];

        $response = $this->postWithCsrf($route, $payload, $this->platformSession());

        $response->assertStatus(302);
        $this->assertSame(lang('UserRole.membershipOperationDenied'), session()->getFlashdata('error'));

        // Verify count remains 1 and user fields are untouched
        $memberships = $this->db->table('clinic_users')
            ->where('clinic_id', $this->clinicA)
            ->where('user_id', $this->targetUserId)
            ->get()
            ->getResultArray();
        $this->assertCount(1, $memberships);

        $targetUser = $this->db->table('users')->where('id', $this->targetUserId)->get()->getRowArray();
        $this->assertSame($this->targetUserEmail, $targetUser['email']);
        $this->assertSame('Target', $targetUser['first_name']);
    }

    public function testAddMembershipReactivatesInactiveMembership(): void
    {
        // Setup existing inactive membership
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $this->clinicA,
            'user_id' => $this->targetUserId,
            'role_id' => $this->validRoleClinicA,
            'status' => 'inactive',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $route = '/controlplane/users/' . $this->targetUserId . '/memberships/add';
        $payload = [
            'clinic_id' => (string) $this->clinicA,
            'role_id' => (string) $this->validRoleClinicA,
        ];

        $response = $this->postWithCsrf($route, $payload, $this->platformSession());

        $response->assertStatus(302);
        $response->assertRedirectTo(base_url('controlplane/users/' . $this->targetUserId));
        $this->assertSame(lang('UserRole.membershipSaved'), session()->getFlashdata('success'));

        $memberships = $this->db->table('clinic_users')
            ->where('clinic_id', $this->clinicA)
            ->where('user_id', $this->targetUserId)
            ->get()
            ->getResultArray();

        $this->assertCount(1, $memberships, 'Must reactivate existing row without creating duplicate');
        $this->assertSame('active', $memberships[0]['status']);
    }

    public function testAddMembershipRejectsWrongScopeAndInactiveRoles(): void
    {
        $route = '/controlplane/users/' . $this->targetUserId . '/memberships/add';

        // 1. Wrong scope role: validRoleClinicB belongs to clinicB, not clinicA
        $wrongScopePayload = [
            'clinic_id' => (string) $this->clinicA,
            'role_id' => (string) $this->validRoleClinicB,
        ];
        $response1 = $this->postWithCsrf($route, $wrongScopePayload, $this->platformSession());
        $response1->assertStatus(302);
        $this->assertSame(lang('UserRole.membershipInvalidRole'), session()->getFlashdata('error'));

        // 2. Inactive role
        $inactiveRolePayload = [
            'clinic_id' => (string) $this->clinicA,
            'role_id' => (string) $this->inactiveRole,
        ];
        $response2 = $this->postWithCsrf($route, $inactiveRolePayload, $this->platformSession());
        $response2->assertStatus(302);
        $this->assertSame(lang('UserRole.membershipInvalidRole'), session()->getFlashdata('error'));

        // No memberships inserted
        $count = $this->db->table('clinic_users')->where('user_id', $this->targetUserId)->countAllResults();
        $this->assertSame(0, $count);
    }

    // =========================================================================
    // Item 2: TenantFilter and AppApiTenant Canonical getTenantRoleId Tests
    // =========================================================================

    public function testTenantFilterBlocksInvalidRoleBeforeControllerOnTenantOnlyRoutes(): void
    {
        // Create active membership with INACTIVE role
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $this->clinicA,
            'user_id' => $this->targetUserId,
            'role_id' => $this->inactiveRole,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $tenantSession = [
            'identity' => $this->targetUserEmail,
            'user_id' => $this->targetUserId,
            'active_clinic_id' => $this->clinicA,
            'active_clinic_role_id' => $this->inactiveRole,
            'global_mode' => false,
            'logged_in' => true,
            'last_check' => time(),
        ];

        // 1. Route /profile (tenant-only, no permission filter)
        $resProfile = $this->withSession($tenantSession)->get('/profile');
        $resProfile->assertStatus(302);
        $resProfile->assertRedirectTo(base_url('clinic/select'));
        $this->assertSame('Access denied to this clinic.', session()->getFlashdata('error'));
        $this->assertNull(session()->get('active_clinic_id'), 'Stale clinic ID must be cleared');
        $this->assertNull(session()->get('active_clinic_role_id'), 'Stale role ID must be cleared');

        // 2. Route /file/download/99999 (must block in TenantFilter before FileController returns 404!)
        $resDownload = $this->withSession($tenantSession)->get('/file/download/99999');
        $resDownload->assertStatus(302);
        $resDownload->assertRedirectTo(base_url('clinic/select'));
        $this->assertSame('Access denied to this clinic.', session()->getFlashdata('error'));

        // 3. Route /file/inline/99999 (must block in TenantFilter before FileController)
        $resInline = $this->withSession($tenantSession)->get('/file/inline/99999');
        $resInline->assertStatus(302);
        $resInline->assertRedirectTo(base_url('clinic/select'));
        $this->assertSame('Access denied to this clinic.', session()->getFlashdata('error'));

        // 4. Test Foreign Clinic Role
        $this->db->table('clinic_users')
            ->where('clinic_id', $this->clinicA)
            ->where('user_id', $this->targetUserId)
            ->update(['role_id' => $this->validRoleClinicB]);

        $resForeign = $this->withSession($tenantSession)->get('/file/download/99999');
        $resForeign->assertStatus(302);
        $resForeign->assertRedirectTo(base_url('clinic/select'));

        // 5. Test Spoofed super_admin role
        $this->db->table('clinic_users')
            ->where('clinic_id', $this->clinicA)
            ->where('user_id', $this->targetUserId)
            ->update(['role_id' => $this->spoofedSuperAdminRole]);

        $resSpoofed = $this->withSession($tenantSession)->get('/file/download/99999');
        $resSpoofed->assertStatus(302);
        $resSpoofed->assertRedirectTo(base_url('clinic/select'));
    }

    public function testTenantFilterAllowsValidRoleWithNoPermissionsOnTenantOnlyRoutes(): void
    {
        // Valid role for Clinic A with ZERO granted permissions
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $this->clinicA,
            'user_id' => $this->targetUserId,
            'role_id' => $this->validRoleClinicA,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $tenantSession = [
            'identity' => $this->targetUserEmail,
            'user_id' => $this->targetUserId,
            'active_clinic_id' => $this->clinicA,
            'global_mode' => false,
            'logged_in' => true,
            'last_check' => time(),
        ];

        // 1. /profile has no permission filter: should pass TenantFilter gate and reach controller
        $resProfile = $this->withSession($tenantSession)->get('/profile');
        $resProfile->assertStatus(200);

        // 2. /file/download/99999 has no permission filter: passes TenantFilter and reaches FileController,
        // which returns 404 because file 99999 does not exist. (Proves it crossed the tenant gate!)
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->expectExceptionMessage('File not found');
        $this->withSession($tenantSession)->get('/file/download/99999');
    }

    public function testAppApiTenantBlocksInvalidRoleBeforeControllerOnPatientsRoute(): void
    {
        // Inactive role in clinic_users
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $this->clinicA,
            'user_id' => $this->targetUserId,
            'role_id' => $this->inactiveRole,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = [
            'identity' => $this->targetUserEmail,
            'user_id' => $this->targetUserId,
            'active_clinic_id' => $this->clinicA,
            'active_clinic_role_id' => $this->inactiveRole,
            'global_mode' => false,
            'logged_in' => true,
            'last_check' => time(),
        ];

        $response = $this->withSession($sessionData)->get('/app-api/v1/patients');
        $response->assertStatus(403);
        $json = json_decode($response->getJSON(), true);
        $this->assertSame('tenant_context_forbidden', $json['error']['code'] ?? null);
        $this->assertNull(session()->get('active_clinic_id'));
        $this->assertNull(session()->get('active_clinic_role_id'));
    }

    public function testAppApiTenantAllowsValidRoleWithNoPermissionsCrossingTenantGate(): void
    {
        // Valid role for Clinic A with ZERO granted permissions
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $this->clinicA,
            'user_id' => $this->targetUserId,
            'role_id' => $this->validRoleClinicA,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = [
            'identity' => $this->targetUserEmail,
            'user_id' => $this->targetUserId,
            'active_clinic_id' => $this->clinicA,
            'global_mode' => false,
            'logged_in' => true,
            'last_check' => time(),
        ];

        // Crosses AppApiTenant, but AppApiPermission returns 403 'forbidden' (NOT 'tenant_context_forbidden')
        $response = $this->withSession($sessionData)->get('/app-api/v1/patients');
        $response->assertStatus(403);
        $json = json_decode($response->getJSON(), true);
        $this->assertSame('forbidden', $json['error']['code'] ?? null);
    }

    public function testGlobalModeFailsClosedOnTenantRoutesAndAppApi(): void
    {
        $globalSession = [
            'identity' => $this->platformAdminEmail,
            'user_id' => $this->platformAdminId,
            'global_mode' => true,
            'active_clinic_id' => $this->clinicA,
            'logged_in' => true,
            'last_check' => time(),
        ];

        // App-API in global mode: returns 403 tenant_plane_required
        $resApi = $this->withSession($globalSession)->get('/app-api/v1/patients');
        $resApi->assertStatus(403);
        $json = json_decode($resApi->getJSON(), true);
        $this->assertSame('tenant_plane_required', $json['error']['code'] ?? null);
        // CI FeatureTestTrait exposes the web 404 as a PageNotFoundException.
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->withSession($globalSession)->get('/profile');
    }

    public function testInactiveUserAndInactiveClinicFailClosed(): void
    {
        // Inactive target user
        $this->db->table('users')->where('id', $this->targetUserId)->update(['active' => 0]);
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $this->clinicA,
            'user_id' => $this->targetUserId,
            'role_id' => $this->validRoleClinicA,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $sessionData = [
            'identity' => $this->targetUserEmail,
            'user_id' => $this->targetUserId,
            'active_clinic_id' => $this->clinicA,
            'global_mode' => false,
            'logged_in' => true,
            'last_check' => time(),
        ];

        // Inactive user on web: redirects to login
        $resWeb = $this->withSession($sessionData)->get('/profile');
        $resWeb->assertStatus(302);
        $resWeb->assertRedirectTo(base_url('auth/login'));

        // Inactive user on app-api: returns 401 unauthenticated
        $resApi = $this->withSession($sessionData)->get('/app-api/v1/patients');
        $resApi->assertStatus(401);
        $json = json_decode($resApi->getJSON(), true);
        $this->assertSame('unauthenticated', $json['error']['code'] ?? null);

        // Reactivate user, but inactivate clinic
        $this->db->table('users')->where('id', $this->targetUserId)->update(['active' => 1]);
        $this->db->table('clinics')->where('id', $this->clinicA)->update(['status' => 'inactive']);

        $resWebClinic = $this->withSession($sessionData)->get('/profile');
        $resWebClinic->assertStatus(302);
        $resWebClinic->assertRedirectTo(base_url('clinic/select'));

        $resApiClinic = $this->withSession($sessionData)->get('/app-api/v1/patients');
        $resApiClinic->assertStatus(403);
        $jsonClinic = json_decode($resApiClinic->getJSON(), true);
        $this->assertSame('tenant_context_forbidden', $jsonClinic['error']['code'] ?? null);
    }

    public function testWebImpersonationBehaviorPreservedAndBearerNeverTrustsIt(): void
    {
        // Genuine platform super admin impersonating clinic A in tenant mode (global_mode = false)
        $impersonationSession = [
            'identity' => $this->platformAdminEmail,
            'user_id' => $this->platformAdminId,
            'active_clinic_id' => $this->clinicA,
            'is_impersonating' => true,
            'impersonated_clinic_id' => $this->clinicA,
            'global_mode' => false,
            'logged_in' => true,
            'last_check' => time(),
        ];

        // 1. /profile should allow genuine web impersonation
        $resProfile = $this->withSession($impersonationSession)->get('/profile');
        $resProfile->assertStatus(200);

        // 2. App API resource gates reject web impersonation independently of Bearer headers
        $resApi = $this->withSession($impersonationSession)->get('/app-api/v1/patients');
        $resApi->assertStatus(403);
        $this->assertSame('tenant_context_forbidden', json_decode($resApi->getJSON(), true)['error']['code'] ?? null);

        // 3. Bearer header never trusts impersonation: must fail closed
        $resBearer = $this->withSession($impersonationSession)
            ->withHeaders(['Authorization' => 'Bearer untrusted_token'])
            ->get('/app-api/v1/patients');
        $resBearer->assertStatus(403);
        $jsonBearer = json_decode($resBearer->getJSON(), true);
        $this->assertSame('tenant_context_forbidden', $jsonBearer['error']['code'] ?? null);

        // 4. Forged impersonation by non-super-admin user: must fail closed
        $forgedSession = [
            'identity' => $this->targetUserEmail,
            'user_id' => $this->targetUserId,
            'active_clinic_id' => $this->clinicA,
            'is_impersonating' => true,
            'impersonated_clinic_id' => $this->clinicA,
            'global_mode' => false,
            'logged_in' => true,
            'last_check' => time(),
        ];

        $resForged = $this->withSession($forgedSession)->get('/profile');
        $resForged->assertStatus(302);
        $resForged->assertRedirectTo(base_url('clinic/select'));
    }
}
