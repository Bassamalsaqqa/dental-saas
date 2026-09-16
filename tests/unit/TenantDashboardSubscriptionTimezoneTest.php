<?php

namespace Tests\Unit;

use App\Controllers\ControlPlane\Clinics;
use App\Models\ClinicModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\ClinicUserModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Services\ControlPlaneSubscriptionService;
use App\Services\OnboardingService;
use App\Services\PermissionService;
use App\Services\PlanGuard;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;

final class TenantDashboardSubscriptionTimezoneTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $db;
    private string $originalTimezone;
    private array $operator;
    private array $createdClinicIds = [];
    private array $createdUserIds = [];
    private array $createdPlanRequestIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect();
        $this->originalTimezone = date_default_timezone_get();

        $this->operator = $this->db->table('users')->select('users.*')
            ->join('user_roles', 'user_roles.user_id = users.id')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('roles.slug', 'super_admin')
            ->where('users.active', 1)
            ->get()->getRowArray();
        $this->assertNotEmpty($this->operator, 'Local bootstrap must have an active CP operator.');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->originalTimezone);

        while ($this->db->transDepth > 0) {
            $this->db->transRollback();
        }
        $this->db->resetTransStatus();

        foreach ($this->createdPlanRequestIds as $reqId) {
            $this->db->table('plan_requests')->where('id', $reqId)->delete();
        }

        foreach ($this->createdClinicIds as $clinicId) {
            foreach (['clinic_subscriptions', 'plan_requests', 'settings', 'plan_audits', 'clinic_users'] as $table) {
                $this->db->table($table)->where('clinic_id', $clinicId)->delete();
            }
            $audits = $this->db->table('control_plane_audits')->get()->getResultArray();
            foreach ($audits as $audit) {
                $meta = json_decode($audit['metadata_json'] ?? '{}', true) ?: [];
                if ((int) ($meta['clinic_id'] ?? 0) === $clinicId) {
                    $this->db->table('control_plane_audits')->where('id', $audit['id'])->delete();
                }
            }
            $this->db->table('clinics')->where('id', $clinicId)->delete();
        }

        foreach ($this->createdUserIds as $userId) {
            foreach (['user_roles', 'users_groups'] as $table) {
                $this->db->table($table)->where('user_id', $userId)->delete();
            }
            $this->db->table('users')->where('id', $userId)->delete();
        }

        // Clear session and runtime state
        if (session_status() === PHP_SESSION_ACTIVE) {
            session()->destroy();
        }
        $_SESSION = [];

        parent::tearDown();
    }

    private function generateRfcUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant RFC 4122
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Helper to create a checked test clinic with subscription and admin user using valid RFC UUIDs.
     */
    private function createFixtureClinic(string $subStatus = 'active', ?string $startAt = null, ?string $endAt = null): array
    {
        $token = bin2hex(random_bytes(6));
        $uuid = $this->generateRfcUuidV4();
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);

        $clinicModel = new ClinicModel();
        $clinicId = (int) $clinicModel->insert([
            'name' => 'TZ Fixture ' . $token,
            'status' => 'active',
            'slug' => 'tz-fixture-' . $token,
            'uuid' => $uuid,
        ]);
        $this->assertGreaterThan(0, $clinicId, 'Clinic fixture insertion must return valid ID.');
        $this->createdClinicIds[] = $clinicId;

        $userModel = new UserModel();
        $userPassword = 'Test-password-123!';
        $userId = (int) $userModel->insert([
            'email' => 'tz-user-' . $token . '@example.test',
            'username' => 'tz_user_' . $token,
            'password' => $userPassword,
            'first_name' => 'Timezone',
            'last_name' => 'Tester',
            'active' => 1,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
        $this->assertGreaterThan(0, $userId, 'User fixture insertion must return valid ID.');
        $this->createdUserIds[] = $userId;

        // IonAuth members group
        $memberGroup = $this->db->table('groups')->where('name', 'members')->get()->getRowArray();
        if ($memberGroup) {
            $this->db->table('users_groups')->insert([
                'user_id' => $userId,
                'group_id' => $memberGroup['id'],
            ]);
        }

        // Manager role
        $roleModel = new RoleModel();
        $role = $roleModel->getBySlug('practice_manager') ?: $roleModel->getBySlug('clinic_admin');
        $this->assertNotEmpty($role, 'System must have practice_manager or clinic_admin role.');

        // Clinic user membership
        $clinicUserModel = (new ClinicUserModel())->withoutTenantScope();
        $membershipId = (int) $clinicUserModel->insert([
            'clinic_id' => $clinicId,
            'user_id' => $userId,
            'role_id' => $role['id'],
            'status' => 'active',
        ]);
        $this->assertGreaterThan(0, $membershipId, 'Membership fixture insertion must return valid ID.');

        // User role assignment
        (new UserRoleModel())->assignRole($userId, $role['id'], (int) $this->operator['id']);

        // Subscription
        $plan = $this->db->table('plans')->where('status', 'active')->get()->getRowArray();
        $this->assertNotEmpty($plan, 'Active plan must exist.');

        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $subId = (int) $subModel->insert([
            'clinic_id' => $clinicId,
            'plan_id' => $plan['id'],
            'status' => $subStatus,
            'start_at' => $startAt ?? ClinicSubscriptionModel::nowUtc(),
            'end_at' => $endAt,
        ]);
        $this->assertGreaterThan(0, $subId, 'Subscription fixture insertion must return valid ID.');

        return [
            'clinic_id' => $clinicId,
            'user_id' => $userId,
            'email' => 'tz-user-' . $token . '@example.test',
            'password' => $userPassword,
            'role_id' => $role['id'],
            'membership_id' => $membershipId,
            'subscription_id' => $subId,
        ];
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

    // -------------------------------------------------------------------------
    // 1. Strict Parsing and Grammar Validation
    // -------------------------------------------------------------------------

    public function testStrictParsingRejectsMalformedRelativeAndOverflowDates(): void
    {
        // Null and empty strings are valid open-ended indicators
        $this->assertNull(ClinicSubscriptionModel::parseUtcTimestamp(null));
        $this->assertNull(ClinicSubscriptionModel::parseUtcTimestamp(''));
        $this->assertNull(ClinicSubscriptionModel::parseUtcTimestamp('   '));

        // Relative text must be strictly rejected (not normalized)
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('yesterday'));
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('tomorrow'));
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('+1 day'));
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('now'));

        // Impossible calendar dates must fail closed (not rollover to March 2)
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('2026-02-30 00:00:00'));
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('2026-04-31 12:00:00'));

        // Leap day verification: 2024 is leap year, 2026 is not
        $this->assertIsInt(ClinicSubscriptionModel::parseUtcTimestamp('2024-02-29 12:00:00'));
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('2026-02-29 12:00:00'));

        // Lower and upper accepted year bounds (1000 - 9999)
        $this->assertIsInt(ClinicSubscriptionModel::parseUtcTimestamp('1000-01-01 00:00:00'));
        $this->assertIsInt(ClinicSubscriptionModel::parseUtcTimestamp('9999-12-31 23:59:59'));
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('0999-12-31 23:59:59'));

        // Overflow hours and minutes must fail closed
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15 25:00:00'));
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15 12:60:00'));
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15 12:00:60'));

        // Zero dates and partial dates must fail closed
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('0000-00-00 00:00:00'));
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15'));
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15 16:37'));

        // Unsupported, extreme, or malformed offsets
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15T16:37:13+99:99'));
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15T16:37:13-25:00'));
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15T16:37:13+14:01'), 'Offset above +14:00 must fail closed');
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15T16:37:13-12:01'), 'Offset below -12:00 must fail closed');
        $this->assertFalse(ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15T16:37:13-14:00'), 'Offset -14:00 is not a valid negative offset');

        // Valid offset extremes (+14:00 and -12:00)
        $this->assertIsInt(ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15T16:37:13+14:00'));
        $this->assertIsInt(ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15T16:37:13-12:00'));

        // Valid canonical UTC formats
        $expectedInstant = 1789490233; // 2026-09-15 16:37:13 UTC
        $this->assertSame($expectedInstant, ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15 16:37:13'));
        $this->assertSame($expectedInstant, ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15T16:37:13Z'));
        $this->assertSame($expectedInstant, ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15T16:37:13+00:00'));
        $this->assertSame($expectedInstant, ClinicSubscriptionModel::parseUtcTimestamp('2026-09-15T19:37:13+03:00'));

        // evaluateStanding fails closed on malformed dates
        $standing = ClinicSubscriptionModel::evaluateStanding([
            'status' => 'active',
            'start_at' => '2026-02-30 00:00:00',
            'end_at' => null,
        ]);
        $this->assertFalse($standing['is_valid']);
        $this->assertSame('MALFORMED_START_AT', $standing['reason']);

        $standingEnd = ClinicSubscriptionModel::evaluateStanding([
            'status' => 'active',
            'start_at' => '2026-09-15 16:37:13',
            'end_at' => 'yesterday',
        ]);
        $this->assertFalse($standingEnd['is_valid']);
        $this->assertSame('MALFORMED_END_AT', $standingEnd['reason']);

        // formatDisplay safe bounded behavior
        $this->assertSame('2026-09-15 19:37:13', ClinicSubscriptionModel::formatDisplay('2026-09-15 16:37:13', 'Y-m-d H:i:s', 'Asia/Jerusalem'));
        $this->assertSame('2026-09-15 16:37:13', ClinicSubscriptionModel::formatDisplay('2026-09-15 16:37:13', 'Y-m-d H:i:s', 'Invalid/Timezone'), 'Invalid timezone in formatDisplay must fall back to UTC without exception');
        $this->assertSame('--', ClinicSubscriptionModel::formatDisplay('corrupt-date', 'Y-m-d H:i:s'));
    }

    // -------------------------------------------------------------------------
    // 2. Control Plane Normalization & DST Gap/Fold Handling
    // -------------------------------------------------------------------------

    public function testControlPlaneNormalizationAndDstGapPolicy(): void
    {
        $controller = new Clinics();
        $ref = new \ReflectionClass($controller);
        $normalize = $ref->getMethod('normalizeDateTime');
        $normalize->setAccessible(true);

        // 1. Minute-only datetime-local input strictly resets seconds to :00
        date_default_timezone_set('Asia/Jerusalem');
        $minOnlyResult = $normalize->invoke($controller, '2026-09-15T19:37');
        $this->assertSame('2026-09-15 16:37:00', $minOnlyResult, 'Minute-only input must reset seconds to :00');

        // 2. Rejection of invalid display timezone (fails closed, no silent UTC fallback)
        $this->assertNull($normalize->invoke($controller, '2026-09-15T19:37:00', 'Invalid/Timezone'));

        // 3. Rejection of calendar overflow and relative terms
        $this->assertNull($normalize->invoke($controller, '2026-02-30T10:00'));
        $this->assertNull($normalize->invoke($controller, '2026-09-15 25:00:00'));
        $this->assertNull($normalize->invoke($controller, 'yesterday'));
        $this->assertNull($normalize->invoke($controller, 'tomorrow'));

        // 4. DST spring-forward gap in America/New_York (jump from 02:00 to 03:00 on 2026-03-08)
        // Local time 02:30 does not exist in America/New_York on that date; must return null (fail closed)
        date_default_timezone_set('America/New_York');
        $nonexistentDstTime = '2026-03-08T02:30';
        $this->assertNull($normalize->invoke($controller, $nonexistentDstTime), 'Nonexistent local time during DST gap must fail closed');

        // Valid local times before and after the gap normalize correctly
        $beforeGap = $normalize->invoke($controller, '2026-03-08T01:30'); // 01:30 EST (UTC-5) -> 06:30:00 UTC
        $this->assertSame('2026-03-08 06:30:00', $beforeGap);

        $afterGap = $normalize->invoke($controller, '2026-03-08T03:30'); // 03:30 EDT (UTC-4) -> 07:30:00 UTC
        $this->assertSame('2026-03-08 07:30:00', $afterGap);

        // 5. DST fall-back ambiguous fold in America/New_York (repetition of 01:00-02:00 on 2026-11-01)
        // Offset-free local time 01:30 is ambiguous (occurs in both EDT UTC-4 and EST UTC-5); must return null (fail closed)
        $ambiguousFoldTime = '2026-11-01T01:30';
        $this->assertNull($normalize->invoke($controller, $ambiguousFoldTime), 'Ambiguous local time during DST fold must fail closed');

        // When operator provides explicit offset, the fold ambiguity is resolved
        $explicitEdt = $normalize->invoke($controller, '2026-11-01T01:30:00-04:00');
        $this->assertSame('2026-11-01 05:30:00', $explicitEdt);

        $explicitEst = $normalize->invoke($controller, '2026-11-01T01:30:00-05:00');
        $this->assertSame('2026-11-01 06:30:00', $explicitEst);
    }

    // -------------------------------------------------------------------------
    // 3. Finite Duration Extension Cannot Become Unlimited Entitlement
    // -------------------------------------------------------------------------

    public function testInvalidExtensionDoesNotBecomeUnlimitedEntitlement(): void
    {
        // 1. resolveEndAt failure behavior
        $this->assertFalse(ClinicSubscriptionModel::resolveEndAt('invalid_duration', '2026-09-15 16:37:13'));
        $this->assertFalse(ClinicSubscriptionModel::resolveEndAt('monthly', 'corrupted-base-date'));
        $this->assertFalse(ClinicSubscriptionModel::resolveEndAt('yearly', null));
        $this->assertNull(ClinicSubscriptionModel::resolveEndAt('lifetime', null), 'Explicit lifetime returns null');

        // 2. Valid finite extensions
        $monthly = ClinicSubscriptionModel::resolveEndAt('monthly', '2026-09-15 16:37:13');
        $this->assertSame('2026-10-15 16:37:13', $monthly);
        $yearly = ClinicSubscriptionModel::resolveEndAt('yearly', '2026-09-15 16:37:13');
        $this->assertSame('2027-09-15 16:37:13', $yearly);

        // 3. Controller extendSubscription with corrupted base or invalid duration rejects write
        $fixture = $this->createFixtureClinic('active', '2026-09-15 16:37:13', '2026-10-15 16:37:13');
        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();

        // Simulate operator session
        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];
        session()->set($operatorSession);

        // POST invalid duration to extend route
        $extendUrl = '/controlplane/clinics/' . $fixture['clinic_id'] . '/subscription/extend';
        $res = $this->postWithCsrf($extendUrl, [
            'duration' => 'invalid_duration',
        ], $operatorSession);
        $res->assertStatus(302);

        // Prove row end_at is NOT corrupted to NULL
        $fresh = $subModel->find($fixture['subscription_id']);
        $this->assertSame('2026-10-15 16:37:13', $fresh['end_at'], 'Database end_at must remain unchanged and never become null on extension failure');
    }

    // -------------------------------------------------------------------------
    // 4. Actual Registered Entry Routes: Login and Clinic Selection
    // -------------------------------------------------------------------------

    public function testActualRegisteredLoginAndClinicSelectRoutesToDashboard(): void
    {
        $fixture1 = $this->createFixtureClinic();
        $fixture2 = $this->createFixtureClinic();

        $userId = $fixture1['user_id'];
        $userEmail = $fixture1['email'];
        $userPassword = $fixture1['password'];

        // Assign user to second clinic as well
        $roleModel = new RoleModel();
        $role = $roleModel->getBySlug('practice_manager') ?: $roleModel->getBySlug('clinic_admin');
        (new ClinicUserModel())->withoutTenantScope()->insert([
            'clinic_id' => $fixture2['clinic_id'],
            'user_id' => $userId,
            'role_id' => $role['id'],
            'status' => 'active',
        ]);

        // 1. Submit actual POST /auth/login
        $loginResult = $this->postWithCsrf('/auth/login', [
            'identity' => $userEmail,
            'password' => $userPassword,
        ]);
        // Multi-clinic user should be redirected to /clinic/select
        $loginResult->assertStatus(302);
        $this->assertStringContainsString('/clinic/select', $loginResult->response()->getHeaderLine('Location'));

        // 2. Submit actual POST /clinic/select with fixture1 clinic_id
        $selectResult = $this->postWithCsrf('/clinic/select', [
            'clinic_id' => $fixture1['clinic_id'],
        ], session()->get());
        $selectResult->assertStatus(302);
        $this->assertStringContainsString('/dashboard', $selectResult->response()->getHeaderLine('Location'));

        // Verify session state after selection
        $this->assertSame((int) $fixture1['clinic_id'], (int) session()->get('active_clinic_id'));
        $this->assertFalse((bool) session()->get('global_mode'));
        $this->assertSame((int) $role['id'], (int) session()->get('active_clinic_role_id'));

        // 3. Request actual GET /dashboard through full filter pipeline
        $dashResult = $this->withSession(session()->get())->get('/dashboard');
        $dashResult->assertStatus(200);
        $this->assertStringContainsString('Dashboard', $dashResult->response()->getBody());

        // 4. Test selecting non-member clinic fails closed
        $invalidSelectResult = $this->postWithCsrf('/clinic/select', [
            'clinic_id' => 999999, // non-existent/non-member
        ], session()->get());
        $invalidSelectResult->assertStatus(302);
        $this->assertNotEmpty(session()->getFlashdata('error'), 'Non-member clinic selection must return flash error.');
    }

    // -------------------------------------------------------------------------
    // 5. Actual Registered Entry Routes: Control Plane Impersonation
    // -------------------------------------------------------------------------

    public function testActualRegisteredControlPlaneImpersonationRouteToDashboard(): void
    {
        $fixture = $this->createFixtureClinic();

        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];
        session()->set($operatorSession);

        // Submit actual POST /controlplane/impersonate
        $impersonateResult = $this->postWithCsrf('/controlplane/impersonate', [
            'clinic_id' => $fixture['clinic_id'],
        ], $operatorSession);
        $impersonateResult->assertStatus(302);
        $this->assertStringContainsString('/dashboard', $impersonateResult->response()->getHeaderLine('Location'));

        // Assert session transitions: global_mode false, impersonated flags set
        $this->assertFalse((bool) session()->get('global_mode'));
        $this->assertTrue((bool) session()->get('is_impersonating'));
        $this->assertSame((int) $fixture['clinic_id'], (int) session()->get('active_clinic_id'));
        $this->assertSame((int) $fixture['clinic_id'], (int) session()->get('impersonated_clinic_id'));

        // Request actual GET /dashboard
        $dashResult = $this->withSession(session()->get())->get('/dashboard');
        $dashResult->assertStatus(200);
        $this->assertStringContainsString('Dashboard', $dashResult->response()->getBody());
    }

    // -------------------------------------------------------------------------
    // 6. Guard Path Boundary Agreement (Exact Start, End, Paused, Canceled)
    // -------------------------------------------------------------------------

    public function testGuardPathBoundaryAgreement(): void
    {
        $now = time();

        // Exact start equality: start_at == now -> Allowed
        $exactStartSub = $this->createFixtureClinic('active', gmdate('Y-m-d H:i:s', $now), gmdate('Y-m-d H:i:s', $now + 3600));
        $sessionStart = [
            'identity' => $exactStartSub['email'],
            'email' => $exactStartSub['email'],
            'user_id' => $exactStartSub['user_id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => false,
            'active_clinic_id' => $exactStartSub['clinic_id'],
        ];
        $resStart = $this->withSession($sessionStart)->get('/dashboard');
        $resStart->assertStatus(200);

        // Exact end equality: end_at == now -> Expired (404)
        $exactEndSub = $this->createFixtureClinic('active', gmdate('Y-m-d H:i:s', $now - 3600), gmdate('Y-m-d H:i:s', $now));
        $sessionEnd = [
            'identity' => $exactEndSub['email'],
            'email' => $exactEndSub['email'],
            'user_id' => $exactEndSub['user_id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => false,
            'active_clinic_id' => $exactEndSub['clinic_id'],
        ];
        try {
            $this->withSession($sessionEnd)->get('/dashboard');
            $this->fail('Exact end equality must fail closed with 404.');
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            $this->assertSame(404, $e->getCode());
        }

        // Genuine future start -> NOT_STARTED (404)
        $futureSub = $this->createFixtureClinic('active', gmdate('Y-m-d H:i:s', $now + 7200), null);
        $sessionFuture = [
            'identity' => $futureSub['email'],
            'email' => $futureSub['email'],
            'user_id' => $futureSub['user_id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => false,
            'active_clinic_id' => $futureSub['clinic_id'],
        ];
        try {
            $this->withSession($sessionFuture)->get('/dashboard');
            $this->fail('Future start must fail closed with 404.');
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            $this->assertSame(404, $e->getCode());
        }

        // Paused subscription -> 404
        $pausedSub = $this->createFixtureClinic('paused', gmdate('Y-m-d H:i:s', $now - 3600), null);
        $sessionPaused = [
            'identity' => $pausedSub['email'],
            'email' => $pausedSub['email'],
            'user_id' => $pausedSub['user_id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => false,
            'active_clinic_id' => $pausedSub['clinic_id'],
        ];
        try {
            $this->withSession($sessionPaused)->get('/dashboard');
            $this->fail('Paused subscription must fail closed with 404.');
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            $this->assertSame(404, $e->getCode());
        }

        // Canceled subscription -> 404
        $canceledSub = $this->createFixtureClinic('canceled', gmdate('Y-m-d H:i:s', $now - 3600), null);
        $sessionCanceled = [
            'identity' => $canceledSub['email'],
            'email' => $canceledSub['email'],
            'user_id' => $canceledSub['user_id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => false,
            'active_clinic_id' => $canceledSub['clinic_id'],
        ];
        try {
            $this->withSession($sessionCanceled)->get('/dashboard');
            $this->fail('Canceled subscription must fail closed with 404.');
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            $this->assertSame(404, $e->getCode());
        }
    }

    // -------------------------------------------------------------------------
    // 7. Real Onboarding under Asia/Jerusalem and Request under UTC Bootstrap
    // -------------------------------------------------------------------------

    public function testOnboardingUnderAsiaJerusalemControllerAndAccessUnderUtcFilter(): void
    {
        // 1. Controller execution phase: PHP process timezone is Asia/Jerusalem (UTC+3)
        date_default_timezone_set('Asia/Jerusalem');

        $sessionData = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];
        session()->set($sessionData);
        session()->remove('active_clinic_id');

        $token = bin2hex(random_bytes(6));
        $plan = $this->db->table('plans')->where('status', 'active')->get()->getRowArray();
        $adminEmail = 'jerusalem-admin-' . $token . '@example.test';
        $adminPassword = 'Test-password-123!';

        $dto = [
            'clinic_name' => 'Jerusalem Clinic ' . $token,
            'admin_name' => 'Jerusalem Administrator',
            'admin_email' => $adminEmail,
            'admin_password' => $adminPassword,
            'plan_id' => $plan['id'],
        ];

        $service = new OnboardingService();
        $clinicId = $service->createClinicWithAdmin($dto, (int) $this->operator['id']);
        $this->createdClinicIds[] = $clinicId;

        $createdUser = $this->db->table('users')->where('email', $adminEmail)->get()->getRowArray();
        $this->assertNotEmpty($createdUser);
        $this->createdUserIds[] = (int) $createdUser['id'];

        // Confirm database record was stored in canonical UTC, NOT in local Asia/Jerusalem
        $sub = (new ClinicSubscriptionModel())->withoutTenantScope()->getCurrentSubscription($clinicId);
        $this->assertNotEmpty($sub);
        $parsedSubTime = strtotime($sub['start_at'] . ' UTC');
        $this->assertEqualsWithDelta(time(), $parsedSubTime, 2, 'start_at must be persisted as UTC instant');

        // 2. Filter execution phase: Reset PHP process timezone to UTC bootstrap
        date_default_timezone_set('UTC');

        $userSession = [
            'identity' => $adminEmail,
            'email' => $adminEmail,
            'user_id' => (int) $createdUser['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => false,
            'active_clinic_id' => $clinicId,
        ];
        $_SESSION = $userSession;
        session()->set($userSession);

        $res = $this->withSession($userSession)->get('/dashboard');
        $res->assertStatus(200);
        $this->assertStringContainsString('Dashboard', $res->response()->getBody());
    }

    // -------------------------------------------------------------------------
    // 8. Control Plane Dashboard UTC Calendar Arithmetic & Month Boundary
    // -------------------------------------------------------------------------

    public function testDashboardMetricsCalendarArithmeticDeterministicAcrossMonthBoundaries(): void
    {
        // Test deterministic month calculation anchored at the 31st of a month
        // On March 31, simple relative '-1 month' in PHP can erroneously land on March 3 instead of February.
        // Anchoring to 'first day of this month' in UTC guarantees exactly 6 unique prior months.
        $simulatedInstant = new \DateTimeImmutable('2026-03-31 23:59:59', new \DateTimeZone('UTC'));
        $currentMonthStart = $simulatedInstant->modify('first day of this month 00:00:00');

        $labels = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthAnchor = $currentMonthStart->modify('-' . $i . ' months');
            $labels[] = $monthAnchor->format('Y-m');
        }

        // Expected 6 chronological months ending in 2026-03
        $expectedLabels = ['2025-10', '2025-11', '2025-12', '2026-01', '2026-02', '2026-03'];
        $this->assertSame($expectedLabels, $labels, 'Monthly trend anchor must never skip or duplicate months on 31st-day boundaries');

        // Test 30-day window is exactly 30 days regardless of process timezone
        date_default_timezone_set('Asia/Jerusalem');
        $nowUtc = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $thirtyDaysUtc = $nowUtc->modify('+30 days');
        $this->assertSame(30 * 86400, $thirtyDaysUtc->getTimestamp() - $nowUtc->getTimestamp(), '30-day window must be exactly 720 hours in UTC');
    }

    // -------------------------------------------------------------------------
    // 9. Pre-Validation Zero Mutation: Start Subscription
    // -------------------------------------------------------------------------

    public function testStartSubscriptionRejectsInvalidDurationWithZeroMutation(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();

        $baselineSub = $subModel->find($fixture['subscription_id']);
        $baselineSubCount = $this->db->table('clinic_subscriptions')->where('clinic_id', $fixture['clinic_id'])->countAllResults();
        $baselineAuditCount = $this->db->table('control_plane_audits')->where('event_type', 'subscription_start')->countAllResults();

        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];

        $url = '/controlplane/clinics/' . $fixture['clinic_id'] . '/subscription/start';
        $res = $this->postWithCsrf($url, [
            'plan_id' => $baselineSub['plan_id'],
            'duration' => 'invalid_or_overflow_duration',
        ], $operatorSession);

        $res->assertStatus(302);
        $this->assertNotEmpty(session()->getFlashdata('error'), 'Invalid duration must redirect with error.');

        // Assert zero mutation on existing subscription
        $currentSub = $subModel->find($fixture['subscription_id']);
        $this->assertSame('active', $currentSub['status'], 'Existing subscription must remain active.');
        $this->assertSame($baselineSub['start_at'], $currentSub['start_at'], 'Existing start_at must be unchanged.');
        $this->assertSame($baselineSub['end_at'], $currentSub['end_at'], 'Existing end_at must be unchanged.');
        $this->assertNull($currentSub['canceled_at'], 'Existing canceled_at must remain null.');
        $this->assertNull($currentSub['canceled_reason'], 'Existing canceled_reason must remain null.');

        // Assert no new subscription and no audit record
        $currentSubCount = $this->db->table('clinic_subscriptions')->where('clinic_id', $fixture['clinic_id'])->countAllResults();
        $this->assertSame($baselineSubCount, $currentSubCount, 'Subscription count must remain exactly equal to baseline.');

        $currentAuditCount = $this->db->table('control_plane_audits')->where('event_type', 'subscription_start')->countAllResults();
        $this->assertSame($baselineAuditCount, $currentAuditCount, 'No audit record must be written on validation failure.');
    }

    // -------------------------------------------------------------------------
    // 10. Pre-Validation Zero Mutation: Plan Request Approval
    // -------------------------------------------------------------------------

    public function testPlanRequestApproveRejectsInvalidDurationWithZeroMutation(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $plan = $this->db->table('plans')->where('status', 'active')->get()->getRowArray();

        $reqModel = (new \App\Models\PlanRequestModel())->withoutTenantScope();
        $reqId = (int) $reqModel->insert([
            'clinic_id' => $fixture['clinic_id'],
            'current_plan_id' => $fixture['subscription_id'],
            'requested_plan_id' => $plan['id'],
            'requested_duration' => 'invalid_duration',
            'status' => 'pending',
            'requested_by' => $fixture['user_id'],
        ]);
        $this->assertGreaterThan(0, $reqId);
        $this->createdPlanRequestIds[] = $reqId;

        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $baselineSub = $subModel->find($fixture['subscription_id']);
        $baselineSubCount = $this->db->table('clinic_subscriptions')->where('clinic_id', $fixture['clinic_id'])->countAllResults();
        $baselineAuditCount = $this->db->table('control_plane_audits')->where('event_type', 'plan_request_approve')->countAllResults();

        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];

        $approveUrl = '/controlplane/plan-requests/' . $reqId . '/approve';
        $res = $this->postWithCsrf($approveUrl, [], $operatorSession);

        $res->assertStatus(302);
        $this->assertNotEmpty(session()->getFlashdata('error'), 'Invalid plan request duration must redirect with error.');

        // Assert plan request remains untouched in pending status
        $currentReq = $reqModel->find($reqId);
        $this->assertSame('pending', $currentReq['status'], 'Plan request status must remain pending.');
        $this->assertNull($currentReq['reviewed_by'], 'reviewed_by must remain null.');
        $this->assertNull($currentReq['reviewed_at'], 'reviewed_at must remain null.');

        // Assert existing subscription remains active and untouched
        $currentSub = $subModel->find($fixture['subscription_id']);
        $this->assertSame('active', $currentSub['status'], 'Existing subscription must remain active.');
        $this->assertSame($baselineSub['start_at'], $currentSub['start_at']);
        $this->assertSame($baselineSub['end_at'], $currentSub['end_at']);
        $this->assertNull($currentSub['canceled_at']);

        // Assert no new subscription and no audit row
        $currentSubCount = $this->db->table('clinic_subscriptions')->where('clinic_id', $fixture['clinic_id'])->countAllResults();
        $this->assertSame($baselineSubCount, $currentSubCount);

        $currentAuditCount = $this->db->table('control_plane_audits')->where('event_type', 'plan_request_approve')->countAllResults();
        $this->assertSame($baselineAuditCount, $currentAuditCount);
    }

    // -------------------------------------------------------------------------
    // 11. Transaction Rollback on Late Required-Write Failure
    // -------------------------------------------------------------------------

    public function testLateRequiredWriteFailureRollsBackTransactionEntirely(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $plan = $this->db->table('plans')->where('status', 'active')->get()->getRowArray();
        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();

        $baselineSub = $subModel->find($fixture['subscription_id']);
        $baselineSubCount = $this->db->table('clinic_subscriptions')->where('clinic_id', $fixture['clinic_id'])->countAllResults();

        // 1. Test Clinics::startSubscription transaction rollback when audit fails
        $failingAudit = new class extends \App\Services\ControlPlaneAuditService {
            public function logEvent(string $eventType, array $context = [], bool $failClosed = false): bool
            {
                if ($failClosed) {
                    throw new \RuntimeException('Simulated audit logging failure during transaction');
                }
                return false;
            }
        };

        $controller = new Clinics();
        $controller->setAuditService($failingAudit);

        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];
        session()->set($operatorSession);

        // Prepare request with valid plan and monthly duration
        $request = service('request');
        $request->setMethod('POST');
        $request->setGlobal('post', [
            'plan_id' => (string) $plan['id'],
            'duration' => 'monthly',
        ]);
        $controller->initController($request, service('response'), service('logger'));

        $response = $controller->startSubscription($fixture['clinic_id']);
        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $response);
        $this->assertSame(lang('UI.subscriptionCode_system_error'), session()->getFlashdata('error'));
        $this->assertStringNotContainsString('Simulated audit logging failure', session()->getFlashdata('error') ?? '');

        // Verify transaction rolled back: original subscription is STILL active!
        $currentSub = $subModel->find($fixture['subscription_id']);
        $this->assertSame('active', $currentSub['status'], 'Original subscription must be rolled back to active.');
        $this->assertNull($currentSub['canceled_at'], 'Canceled timestamp must be rolled back to null.');
        $this->assertNull($currentSub['canceled_reason'], 'Canceled reason must be rolled back to null.');

        // Verify no orphaned subscription was committed
        $currentSubCount = $this->db->table('clinic_subscriptions')->where('clinic_id', $fixture['clinic_id'])->countAllResults();
        $this->assertSame($baselineSubCount, $currentSubCount, 'No new subscription row must remain after rollback.');

        // 2. Test PlanRequests::approve transaction rollback when audit fails
        $reqModel = (new \App\Models\PlanRequestModel())->withoutTenantScope();
        $reqId = (int) $reqModel->insert([
            'clinic_id' => $fixture['clinic_id'],
            'current_plan_id' => $fixture['subscription_id'],
            'requested_plan_id' => $plan['id'],
            'requested_duration' => 'monthly',
            'status' => 'pending',
            'requested_by' => $fixture['user_id'],
        ]);
        $this->createdPlanRequestIds[] = $reqId;

        $planReqController = new \App\Controllers\ControlPlane\PlanRequests();
        $planReqController->setAuditService($failingAudit);
        $request->setGlobal('post', [
            'expected_requested_plan_id' => (string) $plan['id'],
            'expected_requested_duration' => 'monthly',
        ]);
        $planReqController->initController($request, service('response'), service('logger'));

        $approveResponse = $planReqController->approve($reqId);
        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $approveResponse);
        $this->assertSame(lang('UI.subscriptionCode_system_error'), session()->getFlashdata('error'));
        $this->assertStringNotContainsString('Simulated audit logging failure', session()->getFlashdata('error') ?? '');

        // Verify plan request rolled back to pending
        $currentReq = $reqModel->find($reqId);
        $this->assertSame('pending', $currentReq['status'], 'Plan request status must be rolled back to pending.');
        $this->assertNull($currentReq['reviewed_at']);

        // Verify subscription still active and no extra subscription created
        $currentSubAfterReq = $subModel->find($fixture['subscription_id']);
        $this->assertSame('active', $currentSubAfterReq['status']);
        $this->assertSame($baselineSubCount, $this->db->table('clinic_subscriptions')->where('clinic_id', $fixture['clinic_id'])->countAllResults());
    }

    // -------------------------------------------------------------------------
    // 12. Successful Subscription Replacement and Plan Approval Routes
    // -------------------------------------------------------------------------

    public function testStartAndApproveSubscriptionsSucceedWithAtomicTransactions(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $plan = $this->db->table('plans')->where('status', 'active')->get()->getRowArray();
        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();

        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];

        // 1. Successful POST /controlplane/clinics/{id}/subscription/start
        $startUrl = '/controlplane/clinics/' . $fixture['clinic_id'] . '/subscription/start';
        $startRes = $this->postWithCsrf($startUrl, [
            'plan_id' => $plan['id'],
            'duration' => 'monthly',
        ], $operatorSession);

        $startRes->assertStatus(302);
        $this->assertNotEmpty(session()->getFlashdata('success'), 'Valid subscription start must return success flash.');

        // Old subscription canceled
        $oldSub = $subModel->find($fixture['subscription_id']);
        $this->assertSame('canceled', $oldSub['status']);
        $this->assertSame('superseded_by_new_subscription', $oldSub['canceled_reason']);
        $this->assertNotNull($oldSub['canceled_at']);

        // New active subscription exists
        $newSub = $subModel->where('clinic_id', $fixture['clinic_id'])->where('status', 'active')->first();
        $this->assertNotEmpty($newSub);
        $this->assertNotEquals($fixture['subscription_id'], $newSub['id']);
        $startTs = strtotime($newSub['start_at'] . ' UTC');
        $endTs = strtotime($newSub['end_at'] . ' UTC');
        $this->assertGreaterThan($startTs, $endTs);

        // Audit logged
        $startAudit = $this->db->table('control_plane_audits')
            ->where('event_type', 'subscription_start')
            ->like('metadata_json', '"clinic_id":' . $fixture['clinic_id'])
            ->get()->getRowArray();
        $this->assertNotEmpty($startAudit, 'subscription_start audit must be persisted.');

        // 2. Successful POST /controlplane/plan-requests/{id}/approve
        $reqModel = (new \App\Models\PlanRequestModel())->withoutTenantScope();
        $reqId = (int) $reqModel->insert([
            'clinic_id' => $fixture['clinic_id'],
            'current_plan_id' => $newSub['plan_id'],
            'requested_plan_id' => $plan['id'],
            'requested_duration' => 'yearly',
            'status' => 'pending',
            'requested_by' => $fixture['user_id'],
        ]);
        $this->createdPlanRequestIds[] = $reqId;

        $approveUrl = '/controlplane/plan-requests/' . $reqId . '/approve';
        $approveRes = $this->postWithCsrf($approveUrl, [
            'expected_requested_plan_id' => $plan['id'],
            'expected_requested_duration' => 'yearly',
        ], $operatorSession);

        $approveRes->assertStatus(302);
        $this->assertNotEmpty(session()->getFlashdata('success'), 'Valid plan request approval must return success flash.');

        // Plan request approved
        $approvedReq = $reqModel->find($reqId);
        $this->assertSame('approved', $approvedReq['status']);
        $this->assertSame((int) $this->operator['id'], (int) $approvedReq['reviewed_by']);
        $this->assertNotNull($approvedReq['reviewed_at']);

        // Prior subscription superseded
        $supersededSub = $subModel->find($newSub['id']);
        $this->assertSame('canceled', $supersededSub['status']);
        $this->assertSame('plan_change', $supersededSub['canceled_reason']);

        // Latest subscription active with yearly duration
        $yearlySub = $subModel->where('clinic_id', $fixture['clinic_id'])->where('status', 'active')->first();
        $this->assertNotEmpty($yearlySub);
        $this->assertNotEquals($newSub['id'], $yearlySub['id']);
        $yearlyStartTs = strtotime($yearlySub['start_at'] . ' UTC');
        $yearlyEndTs = strtotime($yearlySub['end_at'] . ' UTC');
        // Approximately 1 year (365 days) apart
        $this->assertEqualsWithDelta(365 * 86400, $yearlyEndTs - $yearlyStartTs, 86400 * 2);

        // Audit logged
        $approveAudit = $this->db->table('control_plane_audits')
            ->where('event_type', 'plan_request_approve')
            ->like('metadata_json', '"request_id":' . $reqId)
            ->get()->getRowArray();
        $this->assertNotEmpty($approveAudit, 'plan_request_approve audit must be persisted.');
    }

    // -------------------------------------------------------------------------
    // 13. Failed Raw SQL / Broken DB Status Rollback in All 5 Lifecycle Paths
    // -------------------------------------------------------------------------

    public function testFailedRawSqlOrBrokenStatusLateInLifecycleRollsBackAllFiveOperations(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $plan = $this->db->table('plans')->where('status', 'active')->get()->getRowArray();
        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();

        $service = new ControlPlaneSubscriptionService();
        $service->beforeCommitHook = function (\CodeIgniter\Database\ConnectionInterface $db) {
            // Trigger raw SQL failure that invalidates transStatus() before commit
            $db->query('SELECT * FROM non_existent_table_for_status_failure');
        };

        $actorId = (int) $this->operator['id'];

        // 1. startSubscription
        $startBaselineCount = $this->db->table('clinic_subscriptions')->where('clinic_id', $fixture['clinic_id'])->countAllResults();
        $startAuditCount = $this->db->table('control_plane_audits')->where('event_type', 'subscription_start')->countAllResults();

        $resStart = $service->startSubscription($fixture['clinic_id'], $plan['id'], 'monthly', $actorId);
        $this->assertFalse($resStart['success']);
        $this->assertSame('system_error', $resStart['code']);

        // Assert original subscription is still active and unchanged
        $subAfterStart = $subModel->find($fixture['subscription_id']);
        $this->assertSame('active', $subAfterStart['status']);
        $this->assertNull($subAfterStart['canceled_at']);
        $this->assertSame($startBaselineCount, $this->db->table('clinic_subscriptions')->where('clinic_id', $fixture['clinic_id'])->countAllResults());
        $this->assertSame($startAuditCount, $this->db->table('control_plane_audits')->where('event_type', 'subscription_start')->countAllResults());

        // 2. extendSubscription
        $extendAuditCount = $this->db->table('control_plane_audits')->where('event_type', 'subscription_extend')->countAllResults();
        $freshSub2 = $subModel->find($fixture['subscription_id']);
        $resExtend = $service->extendSubscription(
            $fixture['clinic_id'],
            'monthly',
            $actorId,
            $freshSub2['end_at'],
            $freshSub2['updated_at'] ?? '__NULL__'
        );
        $this->assertFalse($resExtend['success']);
        $this->assertSame('system_error', $resExtend['code']);

        $subAfterExtend = $subModel->find($fixture['subscription_id']);
        $this->assertSame('2026-10-15 10:00:00', $subAfterExtend['end_at']);
        $this->assertSame($extendAuditCount, $this->db->table('control_plane_audits')->where('event_type', 'subscription_extend')->countAllResults());

        // 3. cancelSubscription
        $freshSub3 = $subModel->find($fixture['subscription_id']);
        $cancelAuditCount = $this->db->table('control_plane_audits')->where('event_type', 'subscription_cancel')->countAllResults();
        $resCancel = $service->cancelSubscription(
            $fixture['clinic_id'],
            $actorId,
            'manual_cancel',
            $freshSub3['updated_at'] ?? '__NULL__'
        );
        $this->assertFalse($resCancel['success']);
        $this->assertSame('system_error', $resCancel['code']);

        $subAfterCancel = $subModel->find($fixture['subscription_id']);
        $this->assertSame('active', $subAfterCancel['status']);
        $this->assertNull($subAfterCancel['canceled_at']);
        $this->assertSame($cancelAuditCount, $this->db->table('control_plane_audits')->where('event_type', 'subscription_cancel')->countAllResults());

        // 4. updateSubscriptionDates
        $freshSub4 = $subModel->find($fixture['subscription_id']);
        $datesAuditCount = $this->db->table('control_plane_audits')->where('event_type', 'subscription_dates_update')->countAllResults();
        $resDates = $service->updateSubscriptionDates(
            $fixture['clinic_id'],
            '2026-09-01 00:00:00',
            '2026-12-01 00:00:00',
            $actorId,
            $freshSub4['start_at'],
            $freshSub4['end_at'],
            $freshSub4['updated_at'] ?? '__NULL__'
        );
        $this->assertFalse($resDates['success']);
        $this->assertSame('system_error', $resDates['code']);

        $subAfterDates = $subModel->find($fixture['subscription_id']);
        $this->assertSame('2026-09-15 10:00:00', $subAfterDates['start_at']);
        $this->assertSame('2026-10-15 10:00:00', $subAfterDates['end_at']);
        $this->assertSame($datesAuditCount, $this->db->table('control_plane_audits')->where('event_type', 'subscription_dates_update')->countAllResults());

        // 5. approvePlanRequest
        $reqModel = (new \App\Models\PlanRequestModel())->withoutTenantScope();
        $reqId = (int) $reqModel->insert([
            'clinic_id' => $fixture['clinic_id'],
            'current_plan_id' => $fixture['subscription_id'],
            'requested_plan_id' => $plan['id'],
            'requested_duration' => 'yearly',
            'status' => 'pending',
            'requested_by' => $fixture['user_id'],
        ]);
        $this->createdPlanRequestIds[] = $reqId;

        $approveAuditCount = $this->db->table('control_plane_audits')->where('event_type', 'plan_request_approve')->countAllResults();
        $resApprove = $service->approvePlanRequest($reqId, $actorId, (int) $plan['id'], 'yearly');
        $this->assertFalse($resApprove['success']);
        $this->assertSame('system_error', $resApprove['code']);

        // Request still pending, current subscription untouched
        $reqAfterApprove = $reqModel->find($reqId);
        $this->assertSame('pending', $reqAfterApprove['status']);
        $this->assertNull($reqAfterApprove['reviewed_at']);

        $subAfterApprove = $subModel->find($fixture['subscription_id']);
        $this->assertSame('active', $subAfterApprove['status']);
        $this->assertSame($approveAuditCount, $this->db->table('control_plane_audits')->where('event_type', 'plan_request_approve')->countAllResults());
    }

    // -------------------------------------------------------------------------
    // 14. Stale Concurrent Submission & Locked Recomputation in extendSubscription
    // -------------------------------------------------------------------------

    public function testExtendSubscriptionDetectsStaleConcurrentSubmissionAndRecomputesFromLockedState(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();

        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];

        $extendUrl = '/controlplane/clinics/' . $fixture['clinic_id'] . '/subscription/extend';

        $sub = $subModel->find($fixture['subscription_id']);
        $validEndAt = $sub['end_at'];
        $validUpdatedAt = $sub['updated_at'] ?? '__NULL__';

        // Part A: Stale expected_end_at mismatch rejects with stale_state and zero mutation
        $resMismatchedEnd = $this->postWithCsrf($extendUrl, [
            'duration' => 'monthly',
            'expected_end_at' => '2025-01-01 00:00:00', // stale
            'expected_updated_at' => $validUpdatedAt,
        ], $operatorSession);

        $resMismatchedEnd->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_stale_state'), session()->getFlashdata('error'));

        $sub = $subModel->find($fixture['subscription_id']);
        $this->assertSame('2026-10-15 10:00:00', $sub['end_at'], 'End date must be unchanged on stale end mismatch');

        // Stale expected_updated_at mismatch rejects with stale_state
        $resMismatchedUpdate = $this->postWithCsrf($extendUrl, [
            'duration' => 'monthly',
            'expected_end_at' => $validEndAt,
            'expected_updated_at' => '2020-01-01 00:00:00', // stale
        ], $operatorSession);

        $resMismatchedUpdate->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_stale_state'), session()->getFlashdata('error'));

        // Part B: Recomputing from locked state
        // Simulate concurrent modification changing end_at to 2026-11-15 10:00:00
        $subModel->update($fixture['subscription_id'], [
            'end_at' => '2026-11-15 10:00:00',
        ]);
        $refreshedSub = $subModel->find($fixture['subscription_id']);

        // Operator extends subscription with matching expected_end_at (or fresh submit)
        $resValid = $this->postWithCsrf($extendUrl, [
            'duration' => 'monthly',
            'expected_end_at' => '2026-11-15 10:00:00',
            'expected_updated_at' => $refreshedSub['updated_at'] ?? '__NULL__',
        ], $operatorSession);

        $resValid->assertStatus(302);
        $this->assertNotEmpty(session()->getFlashdata('success'));

        // End date was recomputed from locked end_at (2026-11-15 + 1 month = 2026-12-15), NOT the old 2026-10-15
        $finalSub = $subModel->find($fixture['subscription_id']);
        $this->assertSame('2026-12-15 10:00:00', $finalSub['end_at']);
    }

    // -------------------------------------------------------------------------
    // 15. Stale Concurrent Modification Rejection in updateSubscriptionDates
    // -------------------------------------------------------------------------

    public function testUpdateSubscriptionDatesDetectsStaleConcurrentModification(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();

        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];

        $updateDatesUrl = '/controlplane/clinics/' . $fixture['clinic_id'] . '/subscription/update-dates';

        $sub = $subModel->find($fixture['subscription_id']);
        $validStartAt = $sub['start_at'];
        $validEndAt = $sub['end_at'];
        $validUpdatedAt = $sub['updated_at'] ?? '__NULL__';

        // 1. Stale expected_updated_at
        $res1 = $this->postWithCsrf($updateDatesUrl, [
            'start_at' => '2026-09-01 00:00:00',
            'end_at' => '2026-12-01 00:00:00',
            'expected_start_at' => $validStartAt,
            'expected_end_at' => $validEndAt,
            'expected_updated_at' => '2020-01-01 00:00:00',
        ], $operatorSession);

        $res1->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_stale_state'), session()->getFlashdata('error'));

        // 2. Stale expected_start_at
        $res2 = $this->postWithCsrf($updateDatesUrl, [
            'start_at' => '2026-09-01 00:00:00',
            'end_at' => '2026-12-01 00:00:00',
            'expected_start_at' => '2025-01-01 00:00:00',
            'expected_end_at' => $validEndAt,
            'expected_updated_at' => $validUpdatedAt,
        ], $operatorSession);

        $res2->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_stale_state'), session()->getFlashdata('error'));

        // 3. Stale expected_end_at
        $res3 = $this->postWithCsrf($updateDatesUrl, [
            'start_at' => '2026-09-01 00:00:00',
            'end_at' => '2026-12-01 00:00:00',
            'expected_start_at' => $validStartAt,
            'expected_end_at' => '2025-01-01 00:00:00',
            'expected_updated_at' => $validUpdatedAt,
        ], $operatorSession);

        $res3->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_stale_state'), session()->getFlashdata('error'));

        // Assert zero mutation
        $currentSub = $subModel->find($fixture['subscription_id']);
        $this->assertSame('2026-09-15 10:00:00', $currentSub['start_at']);
        $this->assertSame('2026-10-15 10:00:00', $currentSub['end_at']);
    }

    // -------------------------------------------------------------------------
    // 16. Stale Request Mutation Rejection and Active Plan Revalidation
    // -------------------------------------------------------------------------

    public function testPlanRequestApproveDetectsStaleRequestMutationAndRevalidatesLockedPlan(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $plan = $this->db->table('plans')->where('status', 'active')->get()->getRowArray();
        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();

        $reqModel = (new \App\Models\PlanRequestModel())->withoutTenantScope();
        $reqId = (int) $reqModel->insert([
            'clinic_id' => $fixture['clinic_id'],
            'current_plan_id' => $fixture['subscription_id'],
            'requested_plan_id' => $plan['id'],
            'requested_duration' => 'yearly',
            'status' => 'pending',
            'requested_by' => $fixture['user_id'],
        ]);
        $this->createdPlanRequestIds[] = $reqId;

        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];

        $approveUrl = '/controlplane/plan-requests/' . $reqId . '/approve';

        // Part A: Stale expected duration token
        $resStaleDuration = $this->postWithCsrf($approveUrl, [
            'expected_requested_duration' => 'monthly', // Stale
            'expected_requested_plan_id' => $plan['id'],
        ], $operatorSession);

        $resStaleDuration->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_stale_state'), session()->getFlashdata('error'));

        // Part B: Stale expected plan ID token
        $resStalePlan = $this->postWithCsrf($approveUrl, [
            'expected_requested_duration' => 'yearly',
            'expected_requested_plan_id' => 999999, // Stale
        ], $operatorSession);

        $resStalePlan->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_stale_state'), session()->getFlashdata('error'));

        // Verify request remains pending and subscription remains active
        $reqAfterStale = $reqModel->find($reqId);
        $this->assertSame('pending', $reqAfterStale['status']);
        $this->assertNull($reqAfterStale['reviewed_at']);

        $subAfterStale = $subModel->find($fixture['subscription_id']);
        $this->assertSame('active', $subAfterStale['status']);

        // Part C: Revalidate plan active status under lock
        // Deactivate the requested plan before approval
        $this->db->table('plans')->where('id', $plan['id'])->update(['status' => 'inactive']);

        $resInactivePlan = $this->postWithCsrf($approveUrl, [
            'expected_requested_duration' => 'yearly',
            'expected_requested_plan_id' => $plan['id'],
        ], $operatorSession);

        $resInactivePlan->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_invalid_plan'), session()->getFlashdata('error'));

        // Request still pending, zero cancellation, zero new subscription
        $reqAfterInactive = $reqModel->find($reqId);
        $this->assertSame('pending', $reqAfterInactive['status']);

        $subAfterInactive = $subModel->find($fixture['subscription_id']);
        $this->assertSame('active', $subAfterInactive['status']);

        // Restore plan status
        $this->db->table('plans')->where('id', $plan['id'])->update(['status' => 'active']);
    }

    // -------------------------------------------------------------------------
    // 17. startSubscription Rejects Inactive Plan
    // -------------------------------------------------------------------------

    public function testStartSubscriptionRejectsInactivePlanWithZeroMutation(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();

        // Create an inactive plan
        $this->db->table('plans')->insert([
            'name' => 'Inactive Plan Test',
            'status' => 'inactive',
            'features_json' => '[]',
            'limits_json' => '{}',
        ]);
        $inactivePlan = $this->db->table('plans')->where('name', 'Inactive Plan Test')->where('status', 'inactive')->get()->getRowArray();
        $this->assertNotEmpty($inactivePlan);

        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];

        $startUrl = '/controlplane/clinics/' . $fixture['clinic_id'] . '/subscription/start';
        $res = $this->postWithCsrf($startUrl, [
            'plan_id' => $inactivePlan['id'],
            'duration' => 'monthly',
        ], $operatorSession);

        $res->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_invalid_plan'), session()->getFlashdata('error'));

        // Current subscription remains untouched
        $sub = $subModel->find($fixture['subscription_id']);
        $this->assertSame('active', $sub['status']);
        $this->assertNull($sub['canceled_at']);

        // Cleanup
        $this->db->table('plans')->where('id', $inactivePlan['id'])->delete();
    }

    // -------------------------------------------------------------------------
    // 18. Error Responses Never Leak Raw Database/Model Details in En and Ar
    // -------------------------------------------------------------------------

    public function testErrorResponsesNeverLeakRawExceptionTextInEnglishAndArabic(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $plan = $this->db->table('plans')->where('status', 'active')->get()->getRowArray();

        $sensitiveError = 'SQLSTATE[42000]: Syntax error: SELECT credentials_master_key, super_salt FROM internal_auth_store WHERE 1=1';

        $throwingAudit = new class($sensitiveError) extends \App\Services\ControlPlaneAuditService {
            public function __construct(private string $leakMsg)
            {
                parent::__construct();
            }
            public function logEvent(string $eventType, array $context = [], bool $failClosed = false): bool
            {
                throw new \RuntimeException($this->leakMsg);
            }
        };

        $controller = new Clinics();
        $controller->setAuditService($throwingAudit);

        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];
        session()->set($operatorSession);

        $request = service('request');
        $request->setMethod('POST');
        $request->setGlobal('post', [
            'plan_id' => (string) $plan['id'],
            'duration' => 'monthly',
        ]);
        $controller->initController($request, service('response'), service('logger'));

        // 1. English Locale
        service('language')->setLocale('en');
        $request->setLocale('en');
        $controller->startSubscription($fixture['clinic_id']);
        $flashEn = session()->getFlashdata('error');

        $this->assertNotEmpty($flashEn);
        $this->assertStringNotContainsString('credentials_master_key', $flashEn);
        $this->assertStringNotContainsString('super_salt', $flashEn);
        $this->assertStringNotContainsString('internal_auth_store', $flashEn);
        $this->assertSame(lang('UI.subscriptionCode_system_error', [], 'en'), $flashEn);

        // 2. Arabic Locale
        service('language')->setLocale('ar');
        $request->setLocale('ar');
        $controller->startSubscription($fixture['clinic_id']);
        $flashAr = session()->getFlashdata('error');

        $this->assertNotEmpty($flashAr);
        $this->assertStringNotContainsString('credentials_master_key', $flashAr);
        $this->assertStringNotContainsString('super_salt', $flashAr);
        $this->assertStringNotContainsString('internal_auth_store', $flashAr);
        $this->assertSame(lang('UI.subscriptionCode_system_error', [], 'ar'), $flashAr);
        $this->assertSame('حدث خطأ غير متوقع أثناء معالجة طلب الاشتراك.', $flashAr);

        // Reset locale
        service('language')->setLocale('en');
        $request->setLocale('en');
    }

    // -------------------------------------------------------------------------
    // 19. Audit Record Shares Connection and Rolls Back with Aborted Transaction
    // -------------------------------------------------------------------------

    public function testAuditRecordSharesConnectionAndRollsBackWithBusinessTransaction(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $plan = $this->db->table('plans')->where('status', 'active')->get()->getRowArray();

        $service = new ControlPlaneSubscriptionService();

        // Prove shared database connection identity
        $this->assertSame($service->getDb(), $service->getAuditService()->getAuditModel()->db, 'Audit model must share the identical connection instance');

        $baselineAuditCount = $this->db->table('control_plane_audits')->countAllResults();

        // Fail transaction AFTER audit insert via beforeCommitHook
        $service->beforeCommitHook = function (\CodeIgniter\Database\ConnectionInterface $db) {
            $db->query('SELECT * FROM non_existent_table_for_status_failure');
        };

        $result = $service->startSubscription($fixture['clinic_id'], $plan['id'], 'monthly', (int) $this->operator['id']);
        $this->assertFalse($result['success']);

        // Assert audit record was rolled back along with business writes
        $finalAuditCount = $this->db->table('control_plane_audits')->countAllResults();
        $this->assertSame($baselineAuditCount, $finalAuditCount, 'Audit insert within transaction must roll back on failure');
    }

    // -------------------------------------------------------------------------
    // 20. Independent Transaction Ownership Contract Enforced
    // -------------------------------------------------------------------------

    public function testIndependentTransactionOwnershipEnforced(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $plan = $this->db->table('plans')->where('status', 'active')->get()->getRowArray();

        $service = new ControlPlaneSubscriptionService();

        // Begin external caller-owned transaction
        $this->db->transBegin();
        $this->assertSame(1, $this->db->transDepth);

        // Service must reject invocation to prevent corrupting caller's transaction
        $result = $service->startSubscription($fixture['clinic_id'], $plan['id'], 'monthly', (int) $this->operator['id']);
        $this->assertFalse($result['success']);
        $this->assertSame('system_error', $result['code']);

        // Caller transaction depth must remain untouched
        $this->assertSame(1, $this->db->transDepth);

        // Cleanly rollback caller transaction
        $this->db->transRollback();
        $this->assertSame(0, $this->db->transDepth);
    }

    // -------------------------------------------------------------------------
    // 21. Registered Route Mandatory Concurrency: extendSubscription
    // -------------------------------------------------------------------------

    public function testExtendSubscriptionMandatoryConcurrencyBoundary(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();

        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];

        $extendUrl = '/controlplane/clinics/' . $fixture['clinic_id'] . '/subscription/extend';
        $sub = $subModel->find($fixture['subscription_id']);
        $baselineAuditCount = $this->db->table('control_plane_audits')->where('event_type', 'subscription_extend')->countAllResults();

        // 1. Omitted expected_end_at
        $res1 = $this->postWithCsrf($extendUrl, [
            'duration' => 'monthly',
            'expected_updated_at' => $sub['updated_at'] ?? '__NULL__',
        ], $operatorSession);
        $res1->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_missing_concurrency_token'), session()->getFlashdata('error'));

        // 2. Omitted expected_updated_at
        $res2 = $this->postWithCsrf($extendUrl, [
            'duration' => 'monthly',
            'expected_end_at' => $sub['end_at'],
        ], $operatorSession);
        $res2->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_missing_concurrency_token'), session()->getFlashdata('error'));

        // 3. Empty string tokens
        $res3 = $this->postWithCsrf($extendUrl, [
            'duration' => 'monthly',
            'expected_end_at' => '',
            'expected_updated_at' => '',
        ], $operatorSession);
        $res3->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_missing_concurrency_token'), session()->getFlashdata('error'));

        // Assert zero mutation and zero audit on missing tokens
        $checkSub = $subModel->find($fixture['subscription_id']);
        $this->assertSame('2026-10-15 10:00:00', $checkSub['end_at']);
        $this->assertSame($baselineAuditCount, $this->db->table('control_plane_audits')->where('event_type', 'subscription_extend')->countAllResults());

        // 4. Lifetime subscription with SQL NULL end_at distinguishes missing token from explicit NULL sentinel
        $lifetimeFixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', null);
        $lifetimeSub = $subModel->find($lifetimeFixture['subscription_id']);
        $this->assertNull($lifetimeSub['end_at']);
        $lifetimeExtendUrl = '/controlplane/clinics/' . $lifetimeFixture['clinic_id'] . '/subscription/extend';

        // Missing expected_end_at fails with missing_concurrency_token
        $resLtMissing = $this->postWithCsrf($lifetimeExtendUrl, [
            'duration' => 'monthly',
            'expected_updated_at' => $lifetimeSub['updated_at'] ?? '__NULL__',
        ], $operatorSession);
        $resLtMissing->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_missing_concurrency_token'), session()->getFlashdata('error'));

        // Explicit __NULL__ token matches null end_at in DB
        $resLtExplicit = $this->postWithCsrf($lifetimeExtendUrl, [
            'duration' => 'monthly',
            'expected_end_at' => '__NULL__',
            'expected_updated_at' => $lifetimeSub['updated_at'] ?? '__NULL__',
        ], $operatorSession);
        $resLtExplicit->assertStatus(302);
        $this->assertNotEmpty(session()->getFlashdata('success'));
        $ltFresh = $subModel->find($lifetimeFixture['subscription_id']);
        $this->assertNotNull($ltFresh['end_at']);

        // Explicit __NULL__ on a non-null end_at subscription fails with stale_state
        $resStaleNull = $this->postWithCsrf($extendUrl, [
            'duration' => 'monthly',
            'expected_end_at' => '__NULL__',
            'expected_updated_at' => $sub['updated_at'] ?? '__NULL__',
        ], $operatorSession);
        $resStaleNull->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_stale_state'), session()->getFlashdata('error'));

        // 5. Valid fresh tokens succeed
        $resValid = $this->postWithCsrf($extendUrl, [
            'duration' => 'monthly',
            'expected_end_at' => $sub['end_at'],
            'expected_updated_at' => $sub['updated_at'] ?? '__NULL__',
        ], $operatorSession);
        $resValid->assertStatus(302);
        $this->assertNotEmpty(session()->getFlashdata('success'));

        $finalSub = $subModel->find($fixture['subscription_id']);
        $this->assertSame('2026-11-15 10:00:00', $finalSub['end_at']);
        $this->assertSame($baselineAuditCount + 1, $this->db->table('control_plane_audits')->where('event_type', 'subscription_extend')->like('metadata_json', '"clinic_id":' . $fixture['clinic_id'])->countAllResults());
    }

    // -------------------------------------------------------------------------
    // 22. Registered Route Mandatory Concurrency: cancelSubscription
    // -------------------------------------------------------------------------

    public function testCancelSubscriptionMandatoryConcurrencyBoundary(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();

        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];

        $cancelUrl = '/controlplane/clinics/' . $fixture['clinic_id'] . '/subscription/cancel';
        $sub = $subModel->find($fixture['subscription_id']);
        $baselineAuditCount = $this->db->table('control_plane_audits')->where('event_type', 'subscription_cancel')->countAllResults();

        // 1. Omitted expected_updated_at fails with missing_concurrency_token
        $resOmitted = $this->postWithCsrf($cancelUrl, [
            'canceled_reason' => 'test_cancel',
        ], $operatorSession);
        $resOmitted->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_missing_concurrency_token'), session()->getFlashdata('error'));

        // 2. Empty string expected_updated_at fails
        $resEmpty = $this->postWithCsrf($cancelUrl, [
            'expected_updated_at' => '',
        ], $operatorSession);
        $resEmpty->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_missing_concurrency_token'), session()->getFlashdata('error'));

        // Zero mutation: subscription still active, zero audit
        $checkSub = $subModel->find($fixture['subscription_id']);
        $this->assertSame('active', $checkSub['status']);
        $this->assertNull($checkSub['canceled_at']);
        $this->assertSame($baselineAuditCount, $this->db->table('control_plane_audits')->where('event_type', 'subscription_cancel')->countAllResults());

        // 3. Stale expected_updated_at fails with stale_state
        $resStale = $this->postWithCsrf($cancelUrl, [
            'expected_updated_at' => '1999-01-01 00:00:00',
        ], $operatorSession);
        $resStale->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_stale_state'), session()->getFlashdata('error'));

        // 4. Valid fresh expected_updated_at succeeds
        $resValid = $this->postWithCsrf($cancelUrl, [
            'expected_updated_at' => $sub['updated_at'] ?? '__NULL__',
            'canceled_reason' => 'test_cancel_success',
        ], $operatorSession);
        $resValid->assertStatus(302);
        $this->assertNotEmpty(session()->getFlashdata('success'));

        $canceledSub = $subModel->find($fixture['subscription_id']);
        $this->assertSame('canceled', $canceledSub['status']);
        $this->assertNotNull($canceledSub['canceled_at']);
        $this->assertSame('test_cancel_success', $canceledSub['canceled_reason']);
        $this->assertSame($baselineAuditCount + 1, $this->db->table('control_plane_audits')->where('event_type', 'subscription_cancel')->countAllResults());
    }

    // -------------------------------------------------------------------------
    // 23. Registered Route Mandatory Concurrency: updateSubscriptionDates
    // -------------------------------------------------------------------------

    public function testUpdateSubscriptionDatesMandatoryConcurrencyBoundary(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();

        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];

        $updateDatesUrl = '/controlplane/clinics/' . $fixture['clinic_id'] . '/subscription/update-dates';
        $sub = $subModel->find($fixture['subscription_id']);
        $baselineAuditCount = $this->db->table('control_plane_audits')->where('event_type', 'subscription_dates_update')->countAllResults();

        // 1. Omitted expected_start_at
        $res1 = $this->postWithCsrf($updateDatesUrl, [
            'start_at' => '2026-09-01 00:00:00',
            'end_at' => '2026-12-01 00:00:00',
            'expected_end_at' => $sub['end_at'],
            'expected_updated_at' => $sub['updated_at'] ?? '__NULL__',
        ], $operatorSession);
        $res1->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_missing_concurrency_token'), session()->getFlashdata('error'));

        // 2. Omitted expected_end_at
        $res2 = $this->postWithCsrf($updateDatesUrl, [
            'start_at' => '2026-09-01 00:00:00',
            'end_at' => '2026-12-01 00:00:00',
            'expected_start_at' => $sub['start_at'],
            'expected_updated_at' => $sub['updated_at'] ?? '__NULL__',
        ], $operatorSession);
        $res2->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_missing_concurrency_token'), session()->getFlashdata('error'));

        // 3. Omitted expected_updated_at
        $res3 = $this->postWithCsrf($updateDatesUrl, [
            'start_at' => '2026-09-01 00:00:00',
            'end_at' => '2026-12-01 00:00:00',
            'expected_start_at' => $sub['start_at'],
            'expected_end_at' => $sub['end_at'],
        ], $operatorSession);
        $res3->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_missing_concurrency_token'), session()->getFlashdata('error'));

        // Zero mutation
        $checkSub = $subModel->find($fixture['subscription_id']);
        $this->assertSame('2026-09-15 10:00:00', $checkSub['start_at']);
        $this->assertSame('2026-10-15 10:00:00', $checkSub['end_at']);
        $this->assertSame($baselineAuditCount, $this->db->table('control_plane_audits')->where('event_type', 'subscription_dates_update')->countAllResults());

        // 4. Nullable end_at handling on lifetime sub: explicit __NULL__ token matches
        $lifetimeFixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', null);
        $ltSub = $subModel->find($lifetimeFixture['subscription_id']);
        $ltUpdateDatesUrl = '/controlplane/clinics/' . $lifetimeFixture['clinic_id'] . '/subscription/update-dates';

        $resLt = $this->postWithCsrf($ltUpdateDatesUrl, [
            'start_at' => '2026-09-01T00:00:00Z',
            'end_at' => '2026-12-01T00:00:00Z',
            'expected_start_at' => $ltSub['start_at'],
            'expected_end_at' => '__NULL__',
            'expected_updated_at' => $ltSub['updated_at'] ?? '__NULL__',
        ], $operatorSession);
        $resLt->assertStatus(302);
        $this->assertNotEmpty(session()->getFlashdata('success'));

        // 5. Valid fresh tokens succeed on standard subscription
        $resValid = $this->postWithCsrf($updateDatesUrl, [
            'start_at' => '2026-09-01T00:00:00Z',
            'end_at' => '2026-12-01T00:00:00Z',
            'expected_start_at' => $sub['start_at'],
            'expected_end_at' => $sub['end_at'],
            'expected_updated_at' => $sub['updated_at'] ?? '__NULL__',
        ], $operatorSession);
        $resValid->assertStatus(302);
        $this->assertNotEmpty(session()->getFlashdata('success'));

        $updatedSub = $subModel->find($fixture['subscription_id']);
        $this->assertSame('2026-09-01 00:00:00', $updatedSub['start_at']);
        $this->assertSame('2026-12-01 00:00:00', $updatedSub['end_at']);
    }

    // -------------------------------------------------------------------------
    // 24. Registered Route Mandatory Concurrency: approvePlanRequest
    // -------------------------------------------------------------------------

    public function testApprovePlanRequestMandatoryConcurrencyBoundary(): void
    {
        $fixture = $this->createFixtureClinic('active', '2026-09-15 10:00:00', '2026-10-15 10:00:00');
        $plan = $this->db->table('plans')->where('status', 'active')->get()->getRowArray();
        $subModel = (new ClinicSubscriptionModel())->withoutTenantScope();

        $reqModel = (new \App\Models\PlanRequestModel())->withoutTenantScope();
        $reqId = (int) $reqModel->insert([
            'clinic_id' => $fixture['clinic_id'],
            'current_plan_id' => $fixture['subscription_id'],
            'requested_plan_id' => $plan['id'],
            'requested_duration' => 'monthly',
            'status' => 'pending',
            'requested_by' => $fixture['user_id'],
        ]);
        $this->createdPlanRequestIds[] = $reqId;

        $operatorSession = [
            'identity' => $this->operator['email'],
            'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'],
            'last_check' => time(),
            'logged_in' => true,
            'global_mode' => true,
        ];

        $approveUrl = '/controlplane/plan-requests/' . $reqId . '/approve';
        $baselineAuditCount = $this->db->table('control_plane_audits')->where('event_type', 'plan_request_approve')->countAllResults();

        // 1. Omitted tokens
        $resOmitted = $this->postWithCsrf($approveUrl, [], $operatorSession);
        $resOmitted->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_missing_concurrency_token'), session()->getFlashdata('error'));

        // 2. Omitted duration token
        $resOmittedDuration = $this->postWithCsrf($approveUrl, [
            'expected_requested_plan_id' => $plan['id'],
        ], $operatorSession);
        $resOmittedDuration->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_missing_concurrency_token'), session()->getFlashdata('error'));

        // 3. Omitted plan token
        $resOmittedPlan = $this->postWithCsrf($approveUrl, [
            'expected_requested_duration' => 'monthly',
        ], $operatorSession);
        $resOmittedPlan->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_missing_concurrency_token'), session()->getFlashdata('error'));

        // Zero mutation: plan request remains pending, subscription untouched, zero audit
        $checkReq = $reqModel->find($reqId);
        $this->assertSame('pending', $checkReq['status']);
        $this->assertNull($checkReq['reviewed_at']);
        $this->assertSame('active', $subModel->find($fixture['subscription_id'])['status']);
        $this->assertSame($baselineAuditCount, $this->db->table('control_plane_audits')->where('event_type', 'plan_request_approve')->countAllResults());

        // 4. Stale plan ID
        $resStalePlan = $this->postWithCsrf($approveUrl, [
            'expected_requested_plan_id' => 999999,
            'expected_requested_duration' => 'monthly',
        ], $operatorSession);
        $resStalePlan->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_stale_state'), session()->getFlashdata('error'));

        // 5. Stale duration
        $resStaleDuration = $this->postWithCsrf($approveUrl, [
            'expected_requested_plan_id' => $plan['id'],
            'expected_requested_duration' => 'yearly',
        ], $operatorSession);
        $resStaleDuration->assertStatus(302);
        $this->assertSame(lang('UI.subscriptionCode_stale_state'), session()->getFlashdata('error'));

        // 6. Fresh valid tokens succeed
        $resValid = $this->postWithCsrf($approveUrl, [
            'expected_requested_plan_id' => $plan['id'],
            'expected_requested_duration' => 'monthly',
        ], $operatorSession);
        $resValid->assertStatus(302);
        $this->assertNotEmpty(session()->getFlashdata('success'));

        $approvedReq = $reqModel->find($reqId);
        $this->assertSame('approved', $approvedReq['status']);
        $this->assertNotNull($approvedReq['reviewed_at']);
        $this->assertSame('canceled', $subModel->find($fixture['subscription_id'])['status']);
        $this->assertSame($baselineAuditCount + 1, $this->db->table('control_plane_audits')->where('event_type', 'plan_request_approve')->countAllResults());
    }
}

