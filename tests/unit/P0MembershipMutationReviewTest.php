<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\ApiRuntimeContext;
use App\Models\ControlPlaneAuditModel;
use App\Models\IonAuthModel;
use App\Services\ClinicAdminLifecycleService;
use App\Services\ClinicMembershipService;
use App\Services\ControlPlaneAuditService;
use App\Services\PermissionService;
use CodeIgniter\Database\MySQLi\Connection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\DataProvider;

/** Reviewer-owned exploit and real transaction regressions. Fixtures commit only in the disposable DB. */
final class P0MembershipMutationReviewTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $db;
    private string $prefix;
    private int $actor;
    private int $target;
    private int $clinicA;
    private int $clinicB;
    private int $actorRole;
    private int $staffRole;
    private int $plan;
    private array $users = [];

    protected function setUp(): void
    {
        parent::setUp();
        ApiRuntimeContext::clear();
        $this->db = Database::connect();
        $this->assertSame(0, $this->db->transDepth);
        $this->prefix = 'membership-review-' . bin2hex(random_bytes(6));
        $this->actor = $this->user('actor');
        $this->target = $this->user('target');
        $this->clinicA = $this->clinic('a');
        $this->clinicB = $this->clinic('b');
        $this->actorRole = $this->customRole('manager', $this->clinicA);
        $this->staffRole = $this->customRole('staff', null);
        foreach (['create', 'edit', 'delete'] as $action) {
            $permission = $this->db->table('permissions')->where('module', 'users')->where('action', $action)->get()->getRowArray();
            $this->assertNotEmpty($permission, 'Canonical disposable permission missing: users:' . $action);
            $this->db->table('role_permissions')->insert(['role_id' => $this->actorRole, 'permission_id' => $permission['id']]);
        }
        $this->member($this->clinicA, $this->actor, $this->actorRole);
        $this->member($this->clinicA, $this->target, $this->staffRole);
        $this->member($this->clinicB, $this->target, $this->staffRole);
        $this->db->table('plans')->insert(['name' => $this->prefix, 'status' => 'active', 'limits_json' => '{"users_max":100}', 'features_json' => '{}']);
        $this->plan = (int) $this->db->insertID();
        foreach ([$this->clinicA, $this->clinicB] as $clinic) {
            $this->db->table('clinic_subscriptions')->insert(['clinic_id' => $clinic, 'plan_id' => $this->plan,
                'status' => 'active', 'start_at' => date('Y-m-d H:i:s', time() - 3600), 'end_at' => null]);
        }
        $this->login();
    }

    protected function tearDown(): void
    {
        ApiRuntimeContext::clear();
        while ($this->db && $this->db->transDepth > 0) {
            $this->db->transRollback();
        }
        $this->db->resetTransStatus();
        if (isset($this->clinicA, $this->clinicB)) {
            foreach (['settings', 'plan_audits', 'clinic_subscriptions', 'clinic_users'] as $table) {
                $this->db->table($table)->whereIn('clinic_id', [$this->clinicA, $this->clinicB])->delete();
            }
        }
        if (isset($this->actor)) {
            $this->db->table('control_plane_audits')->where('actor_user_id', $this->actor)->delete();
        }
        if (isset($this->prefix)) {
            $ownedUsers = $this->db->table('users')->select('id')->like('email', $this->prefix . '-', 'after')->get()->getResultArray();
            $userIds = array_map(static fn(array $row): int => (int) $row['id'], $ownedUsers);
            if ($userIds) {
                foreach (['api_tokens', 'user_roles', 'users_groups', 'user_permissions'] as $table) {
                    $this->db->table($table)->whereIn('user_id', $userIds)->delete();
                }
                $roles = $this->db->table('roles')->select('id')->whereIn('created_by', $userIds)->get()->getResultArray();
                $roleIds = array_column($roles, 'id');
                if ($roleIds) {
                    $this->db->table('role_permissions')->whereIn('role_id', $roleIds)->delete();
                    $this->db->table('roles')->whereIn('id', $roleIds)->delete();
                }
                $this->db->table('users')->whereIn('id', $userIds)->delete();
            }
        }
        if (isset($this->clinicA, $this->clinicB)) {
            $this->db->table('clinics')->whereIn('id', [$this->clinicA, $this->clinicB])->delete();
        }
        if (isset($this->plan)) {
            $this->db->table('plans')->where('id', $this->plan)->delete();
        }
        session()->remove(['identity', 'email', 'user_id', 'last_check', 'active_clinic_id', 'active_clinic_role_id',
            'active_clinic_membership_id', 'ui_locale', 'global_mode', 'is_impersonating', 'impersonated_clinic_id']);
        parent::tearDown();
    }

    public function testNewIdentityMembershipGroupAndAuditCommitTogetherWithCompatiblePassword(): void
    {
        $result = $this->service()->createMemberWithIdentity($this->clinicA, $this->newIdentity(), $this->staffRole, $this->actor);
        $this->assertTrue($result['success'], json_encode($result));
        $created = $this->createdIdentity();
        $this->assertNotEmpty($created);
        $this->assertTrue((new IonAuthModel())->verifyPassword('Synthetic-password-42', $created['password'], $created['email']));
        $this->assertSame(1, $this->db->table('users_groups ug')->join('groups g', 'g.id = ug.group_id')->where('ug.user_id', $created['id'])->where('g.name', 'members')->countAllResults());
        $this->assertSame($this->staffRole, (int) $this->membership($this->clinicA, (int) $created['id'])['role_id']);
        $this->assertSame(1, $this->auditCount());
        $this->assertSame(0, $this->db->transDepth);
    }

    public function testExistingCrossClinicIdentityAndEveryGrantRemainUnchanged(): void
    {
        $this->db->table('clinic_users')->where('clinic_id', $this->clinicA)->where('user_id', $this->target)->delete();
        $this->db->table('user_roles')->insert(['user_id' => $this->target, 'role_id' => $this->staffRole, 'assigned_by' => $this->actor, 'is_active' => 1]);
        $group = $this->db->table('groups')->where('name', 'members')->get()->getRowArray();
        $this->db->table('users_groups')->insert(['user_id' => $this->target, 'group_id' => $group['id']]);
        $beforeGroups = $this->db->table('users_groups')->where('user_id', $this->target)->get()->getResultArray();
        $beforeUser = $this->db->table('users')->where('id', $this->target)->get()->getRowArray();
        $beforeMemberships = $this->db->table('clinic_users')->where('user_id', $this->target)->orderBy('id')->get()->getResultArray();
        $beforeGrants = $this->db->table('user_roles')->where('user_id', $this->target)->get()->getResultArray();
        $input = $this->newIdentity();
        $input['email'] = $beforeUser['email'];
        $input['first_name'] = 'Unilateral overwrite';
        $result = $this->service()->createMemberWithIdentity($this->clinicA, $input, $this->actorRole, $this->actor);
        $this->assertFalse($result['success']);
        $this->assertSame('existing_account_restricted', $result['code']);
        $this->assertSame($beforeUser, $this->db->table('users')->where('id', $this->target)->get()->getRowArray());
        $this->assertSame($beforeMemberships, $this->db->table('clinic_users')->where('user_id', $this->target)->orderBy('id')->get()->getResultArray());
        $this->assertSame($beforeGrants, $this->db->table('user_roles')->where('user_id', $this->target)->get()->getResultArray());
        $this->assertSame($beforeGroups, $this->db->table('users_groups')->where('user_id', $this->target)->get()->getResultArray());
        $this->assertSame(0, $this->auditCount());
    }

    public function testRoleDemotionImmediatelyRevokesOnlyThisClinicAndPreservesHistoricalGrant(): void
    {
        $manager = $this->systemRole('practice_manager');
        $this->db->table('user_roles')->insert(['user_id' => $this->target, 'role_id' => $manager, 'assigned_by' => $this->actor, 'is_active' => 1]);
        $grantId = (int) $this->db->insertID();
        $service = $this->service();
        $this->assertTrue($service->replaceRole($this->clinicA, $this->target, $this->actorRole, $this->actor)['success']);
        $this->assertTrue((new PermissionService())->hasPermission($this->target, 'users', 'edit'));
        $this->assertTrue($service->replaceRole($this->clinicA, $this->target, $this->staffRole, $this->actor)['success']);
        $this->assertFalse((new PermissionService())->hasPermission($this->target, 'users', 'edit'));
        $this->assertSame($this->staffRole, (int) $this->membership($this->clinicB, $this->target)['role_id']);
        $this->assertSame(1, $this->db->table('user_roles')->where('id', $grantId)->where('is_active', 1)->countAllResults());
    }

    public function testClinicRemovalPreservesGlobalAccountAndOtherMembershipAndCannotReactivateDisabledIdentity(): void
    {
        $service = $this->service();
        $this->assertTrue($service->removeMembership($this->clinicA, $this->target, $this->actor)['success']);
        $this->assertSame('inactive', $this->membership($this->clinicA, $this->target)['status']);
        $this->assertSame('active', $this->membership($this->clinicB, $this->target)['status']);
        $this->assertSame(1, (int) $this->db->table('users')->where('id', $this->target)->get()->getRowArray()['active']);
        $this->db->table('users')->where('id', $this->target)->update(['active' => 0]);
        $result = $service->updateStatus($this->clinicA, $this->target, 'active', $this->actor);
        $this->assertFalse($result['success']);
        $this->assertSame('global_user_inactive', $result['code']);
        $this->assertSame('inactive', $this->membership($this->clinicA, $this->target)['status']);
    }

    public function testMissingMismatchedAndWrongClinicActorsCannotMutate(): void
    {
        $before = $this->membership($this->clinicA, $this->target);
        session()->remove(['identity', 'user_id']);
        $this->assertFalse($this->service()->replaceRole($this->clinicA, $this->target, $this->actorRole, $this->actor)['success']);
        $this->login();
        $this->assertFalse($this->service()->replaceRole($this->clinicA, $this->target, $this->actorRole, $this->target)['success']);
        session()->set('active_clinic_id', $this->clinicB);
        $this->assertFalse($this->service()->replaceRole($this->clinicA, $this->target, $this->actorRole, $this->actor)['success']);
        $this->assertSame($before, $this->membership($this->clinicA, $this->target));
        $this->assertSame(0, $this->auditCount());
    }

    public function testStaleApiContextCannotAuthorizeWebMembershipWrites(): void
    {
        ApiRuntimeContext::setUserId($this->actor);
        ApiRuntimeContext::setClinicId($this->clinicA);
        $this->assertFalse($this->service()->replaceRole($this->clinicA, $this->target, $this->actorRole, $this->actor)['success']);
        $this->assertSame($this->staffRole, (int) $this->membership($this->clinicA, $this->target)['role_id']);
    }

    public function testNestedTransactionIsRejectedWithoutCommittingOrRollingBackCaller(): void
    {
        $this->db->transBegin();
        $result = $this->service()->replaceRole($this->clinicA, $this->target, $this->actorRole, $this->actor);
        $this->assertFalse($result['success']);
        $this->assertSame('nested_transaction_rejected', $result['code']);
        $this->assertSame(1, $this->db->transDepth);
        $this->assertSame($this->staffRole, (int) $this->membership($this->clinicA, $this->target)['role_id']);
    }

    public function testLastEligibleManagerAndDesignatedAdministratorCannotBeDemoted(): void
    {
        $manager = $this->systemRole('practice_manager');
        $this->db->table('clinic_users')->where('clinic_id', $this->clinicA)->where('user_id', $this->target)->update(['role_id' => $manager]);
        $disabled = $this->user('disabled-manager', 0);
        $this->member($this->clinicA, $disabled, $manager);
        $result = $this->service()->replaceRole($this->clinicA, $this->target, $this->staffRole, $this->actor);
        $this->assertFalse($result['success']);
        $this->assertSame('cannot_demote_last_admin', $result['code']);
        $this->db->table('users')->where('id', $disabled)->update(['active' => 1]);
        $this->db->table('settings')->insert(['clinic_id' => $this->clinicA, 'setting_key' => ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN,
            'setting_value' => (string) $this->target, 'setting_type' => 'number']);
        $result = $this->service()->replaceRole($this->clinicA, $this->target, $this->staffRole, $this->actor);
        $this->assertFalse($result['success']);
        $this->assertSame('cannot_demote_designated_admin', $result['code']);
        $this->assertSame($manager, (int) $this->membership($this->clinicA, $this->target)['role_id']);
    }

    public function testGenuinePlatformTargetIsProtected(): void
    {
        $this->db->table('user_roles')->insert(['user_id' => $this->target, 'role_id' => $this->systemRole('super_admin'), 'assigned_by' => $this->actor, 'is_active' => 1]);
        $result = $this->service()->updateStatus($this->clinicA, $this->target, 'inactive', $this->actor);
        $this->assertFalse($result['success']);
        $this->assertSame('cannot_mutate_platform_account', $result['code']);
        $this->assertSame('active', $this->membership($this->clinicA, $this->target)['status']);
    }

    public function testUserQuotaDeniesCreationButAlreadyActiveNoopDoesNotConsumeSeat(): void
    {
        $this->db->table('plans')->where('id', $this->plan)->update(['limits_json' => '{"users_max":2}']);
        $result = $this->service()->createMemberWithIdentity($this->clinicA, $this->newIdentity(), $this->staffRole, $this->actor);
        $this->assertFalse($result['success']);
        $this->assertSame('plan_quota_exceeded', $result['code']);
        $this->assertNull($this->createdIdentity());
        $this->assertTrue($this->service()->updateStatus($this->clinicA, $this->target, 'active', $this->actor)['success']);
    }

    public function testAuditFailureRollsBackIdentityGroupMembershipAndAuditInsert(): void
    {
        $audit = new class(new ControlPlaneAuditModel($this->db)) extends ControlPlaneAuditService {
            public function logEvent(string $eventType, array $context = [], bool $failClosed = false): bool
            {
                parent::logEvent($eventType, $context, $failClosed);
                throw new \RuntimeException('Synthetic injected failure after audit write.');
            }
        };
        $result = (new ClinicMembershipService(db: $this->db, auditService: $audit))->createMemberWithIdentity($this->clinicA, $this->newIdentity(), $this->staffRole, $this->actor);
        $this->assertFalse($result['success']);
        $this->assertNull($this->createdIdentity());
        $this->assertSame(0, $this->auditCount());
        $this->assertSame(2, $this->db->table('clinic_users')->where('clinic_id', $this->clinicA)->countAllResults());
        $this->assertSame(0, $this->db->transDepth);
    }

    #[DataProvider('transactionFailures')]
    public function testTransactionFailuresNeverClaimSuccessOrLeavePartialIdentity(string $method, bool $throws, string $code): void
    {
        $connection = $this->getMockBuilder(Connection::class)->setConstructorArgs([config('Database')->tests])->onlyMethods([$method])->getMock();
        // CI4.6 resolves its builder/result from the concrete connection class name.
        // Keep real driver behavior for this partial mock; only the transaction result is injected.
        foreach (['Builder', 'Result'] as $driverType) {
            $alias = str_replace('Connection', $driverType, get_class($connection));
            if (!class_exists($alias, false)) {
                $driverClass = $driverType === 'Builder' ? \CodeIgniter\Database\MySQLi\Builder::class : \CodeIgniter\Database\MySQLi\Result::class;
                class_alias($driverClass, $alias);
            }
        }
        if ($throws) {
            $connection->method($method)->willThrowException(new \RuntimeException('Synthetic begin failure.'));
        } else {
            $connection->method($method)->willReturn(false);
        }
        try {
            $result = (new ClinicMembershipService(db: $connection))->createMemberWithIdentity($this->clinicA, $this->newIdentity(), $this->staffRole, $this->actor);
            $this->assertFalse($result['success']);
            $this->assertSame($code, $result['code']);
            $this->assertSame(0, $connection->transDepth);
            $this->assertNull($this->createdIdentity());
            $this->assertSame(0, $this->auditCount());
        } finally {
            while ($connection->transDepth > 0) {
                $connection->transRollback();
            }
            $connection->close();
        }
    }

    public static function transactionFailures(): array
    {
        return ['begin false' => ['transBegin', false, 'trans_begin_failed'],
            'begin exception' => ['transBegin', true, 'transaction_error'],
            'failed status' => ['transStatus', false, 'trans_status_failed'],
            'failed commit' => ['transCommit', false, 'trans_commit_failed']];
    }

    public function testExistingIdentityLinkingRequiresGenuineGlobalControlPlaneAndPreservesIdentity(): void
    {
        $invitee = $this->user('existing-account');
        $before = $this->db->table('users')->where('id', $invitee)->get()->getRowArray();
        $denied = $this->service()->addExistingUserMembership($this->clinicA, $invitee, $this->staffRole, $this->actor);
        $this->assertFalse($denied['success']);
        $this->db->table('user_roles')->insert(['user_id' => $this->actor, 'role_id' => $this->systemRole('super_admin'), 'assigned_by' => $this->actor, 'is_active' => 1]);
        session()->set('global_mode', true);
        session()->remove('active_clinic_id');
        $result = $this->service()->addExistingUserMembership($this->clinicA, $invitee, $this->staffRole, $this->actor);
        $this->assertTrue($result['success'], json_encode($result));
        $this->assertSame($before, $this->db->table('users')->where('id', $invitee)->get()->getRowArray());
        $this->assertSame($this->staffRole, (int) $this->membership($this->clinicA, $invitee)['role_id']);
    }

    public function testValidatedWebImpersonationWorksButForgedFlagsDoNotAuthorizeMutation(): void
    {
        session()->set(['is_impersonating' => true, 'impersonated_clinic_id' => $this->clinicA]);
        $this->assertFalse($this->service()->replaceRole($this->clinicA, $this->target, $this->actorRole, $this->actor)['success']);
        $this->db->table('user_roles')->insert(['user_id' => $this->actor, 'role_id' => $this->systemRole('super_admin'), 'assigned_by' => $this->actor, 'is_active' => 1]);
        $this->db->table('clinic_users')->where('clinic_id', $this->clinicA)->where('user_id', $this->actor)->delete();
        $result = $this->service()->replaceRole($this->clinicA, $this->target, $this->actorRole, $this->actor);
        $this->assertTrue($result['success'], json_encode($result));
        $this->assertSame($this->actorRole, (int) $this->membership($this->clinicA, $this->target)['role_id']);
        session()->set('impersonated_clinic_id', $this->clinicB);
        $this->assertFalse($this->service()->replaceRole($this->clinicA, $this->target, $this->staffRole, $this->actor)['success']);
    }

    public function testMismatchedAuditConnectionIsRejectedBeforeAnyWrites(): void
    {
        $other = Database::connect('tests', false);
        try {
            $this->expectException(\InvalidArgumentException::class);
            new ClinicMembershipService(db: $this->db, auditService: new ControlPlaneAuditService(new ControlPlaneAuditModel($other)));
        } finally {
            $other->close();
        }
    }

    public function testRuntimeUsesReviewedWorktreeControllerAndFilters(): void
    {
        $repoRoot = dirname(__DIR__, 2);
        $this->assertSame(realpath($repoRoot . '/app'), realpath(APPPATH));
        foreach ([\App\Controllers\Users::class => 'Controllers/Users.php',
            \App\Filters\PermissionFilter::class => 'Filters/PermissionFilter.php',
            PermissionService::class => 'Services/PermissionService.php'] as $class => $path) {
            $this->assertSame(realpath($repoRoot . '/app/' . $path), realpath((new \ReflectionClass($class))->getFileName()));
        }
    }
    public function testIdentityMutationAliasesDenyForSelfAndColleagueWhileRoleRouteStillWorks(): void
    {
        foreach ([$this->actor, $this->target] as $id) {
            $before = $this->db->table('users')->where('id', $id)->get()->getRowArray();
            $this->postWithCsrf('/users/' . $id . '/update', ['first_name' => 'Overwrite', 'password' => 'Unverified-change-42', 'role' => $this->staffRole])->assertStatus(302);
            $this->assertSame($before, $this->db->table('users')->where('id', $id)->get()->getRowArray());
            $inline = $this->postWithCsrf('/users/' . $id . '/inline-update', ['first_name' => 'Overwrite', 'last_name' => 'Other']);
            $this->assertSame(403, $inline->response()->getStatusCode(), 'Location: ' . $inline->response()->getHeaderLine('Location') . ' Error: ' . (string) session()->getFlashdata('error'));
            $reset = $this->postWithCsrf('/users/' . $id . '/inline-reset-password', ['new_password' => 'Unverified-change-42', 'confirm_password' => 'Unverified-change-42']);
            $reset->assertStatus(403);
            $this->assertSame($before, $this->db->table('users')->where('id', $id)->get()->getRowArray());
        }
        $this->postWithCsrf('/users/' . $this->target . '/update', ['role' => $this->actorRole])->assertStatus(302);
        $this->assertSame($this->actorRole, (int) $this->membership($this->clinicA, $this->target)['role_id']);
        $this->assertSame($this->staffRole, (int) $this->membership($this->clinicB, $this->target)['role_id']);
    }

    public function testVerifiedSelfPasswordChangeRequiresCorrectOldPassword(): void
    {
        $before = $this->db->table('users')->where('id', $this->actor)->get()->getRowArray()['password'];
        $payload = ['current_password' => 'Incorrect-old-password', 'new_password' => 'Verified-new-password-42', 'confirm_password' => 'Verified-new-password-42'];
        $this->postWithCsrf('/users/' . $this->actor . '/update-password', $payload)->assertStatus(302);
        $this->assertSame($before, $this->db->table('users')->where('id', $this->actor)->get()->getRowArray()['password']);
        $payload['current_password'] = 'Synthetic-password-42';
        $this->postWithCsrf('/users/' . $this->actor . '/update-password', $payload)->assertStatus(302);
        $after = $this->db->table('users')->where('id', $this->actor)->get()->getRowArray()['password'];
        $this->assertNotSame($before, $after);
        $this->assertTrue(password_verify('Verified-new-password-42', $after));
    }

    public function testEmailLookupRequiresCreateAuthorityAndReturnsNoForeignProfile(): void
    {
        $this->db->table('clinic_users')->where('clinic_id', $this->clinicA)->where('user_id', $this->target)->delete();
        $path = '/users/check-email?email=' . rawurlencode($this->prefix . '-target@example.test');
        $response = $this->withSession(session()->get())->withHeaders(['Accept' => 'application/json'])->get($path);
        $response->assertStatus(200);
        $body = (string) $response->getJSON();
        foreach (['first_name', 'last_name', 'memberships', 'clinics', 'user_id', 'password'] as $privateField) {
            $this->assertStringNotContainsString($privateField, $body);
        }
        $permission = $this->db->table('permissions')->where('module', 'users')->where('action', 'create')->get()->getRowArray();
        $this->db->table('role_permissions')->where('role_id', $this->actorRole)->where('permission_id', $permission['id'])->delete();
        $this->withSession(session()->get())->withHeaders(['Accept' => 'application/json'])->get($path)->assertStatus(403);
    }

    public function testAllNineSessionSearchRoutesRetainJsonPayloadAndIgnoreBearerHeaders(): void
    {
        $this->grantSearchPermissions();
        foreach (['patients', 'users', 'examinations', 'treatments', 'inventory', 'medications', 'treatment-types', 'departments', 'roles'] as $type) {
            ApiRuntimeContext::setUserId($this->target);
            ApiRuntimeContext::setClinicId($this->clinicB);
            $response = $this->withSession(session()->get())->withHeaders(['Accept' => 'application/json',
                'X-Clinic-Id' => (string) $this->clinicB, 'Authorization' => 'Bearer unrelated-invalid-token'])->get('/api/search/' . $type);
            $response->assertStatus(200);
            $body = json_decode((string) $response->getJSON(), true);
            $this->assertIsArray($body['results'] ?? null, $type);
            $this->assertArrayHasKey('total', $body, $type);
            $this->assertNull(ApiRuntimeContext::getUserId());
            $this->assertNull(ApiRuntimeContext::getClinicId());
        }
    }

    public function testSessionUserSearchNeverReturnsForeignOrInactiveMembers(): void
    {
        $this->grantSearchPermissions();
        $foreign = $this->user('foreign');
        $inactive = $this->user('inactive');
        $this->member($this->clinicB, $foreign, $this->staffRole);
        $this->member($this->clinicA, $inactive, $this->staffRole);
        $this->db->table('clinic_users')->where('clinic_id', $this->clinicA)->where('user_id', $inactive)->update(['status' => 'inactive']);
        $response = $this->withSession(session()->get())->withHeaders(['Accept' => 'application/json'])->get('/api/search/users');
        $response->assertStatus(200);
        $ids = array_map('intval', array_column(json_decode((string) $response->getJSON(), true)['results'], 'id'));
        $this->assertContains($this->actor, $ids);
        $this->assertNotContains($foreign, $ids);
        $this->assertNotContains($inactive, $ids);
    }

    public function testSessionSearchSupportsOnlyGenuineMatchingWebImpersonationAndPublicBearerStillRequiresMembership(): void
    {
        $this->db->table('user_roles')->insert(['user_id' => $this->actor, 'role_id' => $this->systemRole('super_admin'), 'assigned_by' => $this->actor, 'is_active' => 1]);
        $this->db->table('clinic_users')->where('clinic_id', $this->clinicA)->where('user_id', $this->actor)->delete();
        session()->set(['global_mode' => false, 'is_impersonating' => true, 'impersonated_clinic_id' => $this->clinicA]);
        $this->withSession(session()->get())->withHeaders(['Accept' => 'application/json'])->get('/api/search/departments')->assertStatus(200);
        $token = (new \App\Models\ApiTokenModel())->generateToken($this->actor, 'synthetic-review');
        $response = $this->withSession(session()->get())->withHeaders(['Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token, 'X-Clinic-Id' => (string) $this->clinicA])->get('/api/v1/search/departments');
        $response->assertStatus(403);
        $this->assertStringContainsString('application/json', $response->response()->getHeaderLine('Content-Type'));
        $this->assertFalse($response->isRedirect());
        $this->assertNull(ApiRuntimeContext::getUserId());
        session()->set('impersonated_clinic_id', $this->clinicB);
        $this->withSession(session()->get())->withHeaders(['Accept' => 'application/json'])->get('/api/search/departments')->assertStatus(403);
    }

    public function testSessionSearchDeniedForGuestGlobalModeInactiveClinicAndExpiredSubscriptionWithJson(): void
    {
        $this->grantSearchPermissions();
        foreach (['guest', 'global', 'clinic', 'subscription'] as $state) {
            $this->login();
            $this->db->table('clinics')->where('id', $this->clinicA)->update(['status' => 'active']);
            $this->db->table('clinic_subscriptions')->where('clinic_id', $this->clinicA)->update(['status' => 'active', 'end_at' => null]);
            if ($state === 'guest') { session()->remove(['user_id', 'identity']); }
            if ($state === 'global') { session()->set('global_mode', true); }
            if ($state === 'clinic') { $this->db->table('clinics')->where('id', $this->clinicA)->update(['status' => 'suspended']); }
            if ($state === 'subscription') { $this->db->table('clinic_subscriptions')->where('clinic_id', $this->clinicA)->update(['end_at' => '2000-01-01 00:00:00']); }
            $response = $this->withSession(session()->get())->withHeaders([])->get('/api/search/departments');
            $response->assertStatus($state === 'guest' ? 401 : 403);
            $this->assertStringContainsString('application/json', $response->response()->getHeaderLine('Content-Type'));
            $this->assertFalse($response->isRedirect());
        }
    }

    public function testPublicBearerUsesItsOwnClinicWhenSessionPointsElsewhereAndRejectsMalformedClinicHeaders(): void
    {
        $this->grantSearchPermissions();
        $token = (new \App\Models\ApiTokenModel())->generateToken($this->actor, 'synthetic-review');
        session()->set('active_clinic_id', $this->clinicB);
        $this->withSession(session()->get())->withHeaders(['Authorization' => 'Bearer ' . $token,
            'X-Clinic-Id' => (string) $this->clinicA])->get('/api/v1/search/departments')->assertStatus(200);
        foreach (['0', '-1', '+1', '1.0', '1e2', '9999999999999999999999', ' 1'] as $header) {
            $response = $this->withSession(session()->get())->withHeaders(['Authorization' => 'Bearer ' . $token,
                'X-Clinic-Id' => $header])->get('/api/v1/search/departments');
            $response->assertStatus(422);
            $this->assertNull(ApiRuntimeContext::getUserId());
        }
        $this->db->table('users')->where('id', $this->actor)->update(['active' => 0]);
        $this->withSession(session()->get())->withHeaders(['Authorization' => 'Bearer ' . $token,
            'X-Clinic-Id' => (string) $this->clinicA])->get('/api/v1/search/departments')->assertStatus(401);
    }

    public function testGlobalModeAjaxCannotCrossTenantAirlock(): void
    {
        session()->set('global_mode', true);
        $response = $this->withSession(session()->get())->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->get('/users/stats');
        $response->assertStatus(403);
        $this->assertStringContainsString('application/json', $response->response()->getHeaderLine('Content-Type'));
        $this->assertFalse($response->isRedirect());
    }

    public function testLegacyAuthIdentityAndGroupAliasesCannotMutateForTenantManager(): void
    {
        $before = $this->db->table('users')->where('id', $this->target)->get()->getRowArray();
        $groups = $this->db->table('groups')->orderBy('id')->get()->getResultArray();
        foreach (['edit-user/' . $this->target, 'deactivate/' . $this->target,
            'create-user', 'create-group', 'edit-group/1'] as $alias) {
            $this->postWithCsrf('/auth/' . $alias, ['id' => $this->target, 'confirm' => 'yes',
                'email' => 'overwrite@example.test', 'password' => 'Unverified-change-42', 'group_name' => 'Injected'])->assertStatus(302);
        }
        $this->assertSame($before, $this->db->table('users')->where('id', $this->target)->get()->getRowArray());
        $this->assertSame($groups, $this->db->table('groups')->orderBy('id')->get()->getResultArray());
    }

    public function testAuthenticationAdminGroupCannotEnterPlatformWithoutGlobalRbacAssignment(): void
    {
        $group = $this->db->table('groups')->where('name', 'admin')->get()->getRowArray();
        if (!$group) { $group = $this->db->table('groups')->where('id', 1)->get()->getRowArray(); }
        $this->db->table('users_groups')->insert(['user_id' => $this->actor, 'group_id' => $group['id']]);
        $this->assertFalse((new PermissionService())->isSuperAdmin($this->actor));
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->withSession(session()->get())->withHeaders([])->get('/controlplane');
    }

    public function testRemovingClinicAccessRequiresDeleteWhileStatusEditingRequiresEdit(): void
    {
        $permission = $this->db->table('permissions')->where('module', 'users')->where('action', 'edit')->get()->getRowArray();
        $this->db->table('role_permissions')->where('role_id', $this->actorRole)->where('permission_id', $permission['id'])->delete();
        $this->assertFalse($this->service()->updateStatus($this->clinicA, $this->target, 'inactive', $this->actor)['success']);
        $this->assertTrue($this->service()->removeMembership($this->clinicA, $this->target, $this->actor)['success']);
        $this->assertSame('inactive', $this->membership($this->clinicA, $this->target)['status']);
        $this->assertSame('active', $this->membership($this->clinicB, $this->target)['status']);
    }
    public function testMembershipReactivationCannotEnableAnInvalidRole(): void
    {
        $this->db->table('clinic_users')->where('clinic_id', $this->clinicA)->where('user_id', $this->target)->update(['status' => 'inactive']);
        $this->db->table('roles')->where('id', $this->staffRole)->update(['is_active' => 0]);
        $result = $this->service()->updateStatus($this->clinicA, $this->target, 'active', $this->actor);
        $this->assertFalse($result['success']);
        $this->assertSame('invalid_role', $result['code']);
        $this->assertSame('inactive', $this->membership($this->clinicA, $this->target)['status']);
    }

    public function testMembershipStatisticsStayWithinCurrentClinic(): void
    {
        $this->grantSearchPermissions();
        $response = $this->withSession(session()->get())->withHeaders(['Accept' => 'application/json'])->get('/users/stats');
        $response->assertStatus(200);
        $body = json_decode((string) $response->getJSON(), true);
        $this->assertSame(2, $body['total']);
        $this->assertSame(2, $body['active']);
        $this->assertSame(0, $body['inactive']);
        $this->assertSame(2, array_sum(array_column($body['by_group'], 'count')));
    }

    public function testUserManagementRendersMembershipOnlyFormsInEnglishAndArabic(): void
    {
        $this->grantSearchPermissions();
        $this->db->table('clinic_users')->where('clinic_id', $this->clinicA)->where('user_id', $this->target)->update(['status' => 'inactive']);
        foreach (['en', 'ar'] as $locale) {
            $this->login();
            session()->set('ui_locale', $locale);
            $edit = $this->withSession(session()->get())->withHeaders([])->get('/users/' . $this->target . '/edit');
            $edit->assertStatus(200);
            $html = $edit->response()->getBody();
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
            $xpath = new \DOMXPath($dom);
            $form = $xpath->query('//form[contains(@action,"/users/' . $this->target . '/update")]')->item(0);
            $this->assertNotNull($form);
            $this->assertSame(1, $xpath->query('.//select[@name="role"]', $form)->length);
            $this->assertSame(0, $xpath->query('.//*[@name="password" or @name="email" or @name="first_name" or @name="status"]', $form)->length);
            $this->assertStringContainsString(lang('UserRole.identityManagedByOwner', [], $locale), html_entity_decode($html));
            $show = $this->withSession(session()->get())->get('/users/' . $this->target);
            $show->assertStatus(200);
            $showHtml = $show->response()->getBody();
            $this->assertStringNotContainsString('id="inlinePasswordForm"', $showHtml);
            $this->assertStringNotContainsString('/inline-reset-password', $showHtml);
            $this->assertStringContainsString(lang('UserRole.removeClinicAccess', [], $locale), html_entity_decode($showHtml));
            $list = $this->withSession(session()->get())->get('/users');
            $list->assertStatus(200);
            $this->assertStringContainsString('data-user-status="inactive"', $list->response()->getBody());
            $this->assertStringContainsString('const allowExistingLink = false;', $list->response()->getBody());
            $this->assertStringContainsString(json_encode(lang('UserRole.existingAccountRestricted', [], $locale), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), $list->response()->getBody());
            foreach ([$html, $showHtml, $list->response()->getBody()] as $rendered) {
                preg_match_all('/<script(?![^>]*\bsrc=)[^>]*>(.*?)<\/script>/is', $rendered, $scripts);
                foreach ($scripts[1] as $js) {
                    $file = ROOTPATH . 'tmp/rendered-user-script.js';
                    file_put_contents($file, $js);
                    $output = [];
                    exec('node --check ' . escapeshellarg($file) . ' 2>&1', $output, $status);
                    $this->assertSame(0, $status, implode("\n", $output));
                }
            }
        }
    }

    private function grantSearchPermissions(): void
    {
        foreach (['users', 'patients', 'examinations', 'treatments', 'inventory', 'prescriptions'] as $module) {
            $permission = $this->db->table('permissions')->where('module', $module)->where('action', 'view')->get()->getRowArray();
            if (!$permission) {
                $this->db->table('permissions')->insert(['module' => $module, 'action' => 'view', 'name' => 'View ' . $module]);
                $id = (int) $this->db->insertID();
            } else { $id = (int) $permission['id']; }
            if (!$this->db->table('role_permissions')->where('role_id', $this->actorRole)->where('permission_id', $id)->countAllResults()) {
                $this->db->table('role_permissions')->insert(['role_id' => $this->actorRole, 'permission_id' => $id]);
            }
        }
    }

    private function postWithCsrf(string $path, array $data)
    {
        $this->assertTrue((new PermissionService())->hasPermission($this->actor, 'users', 'edit'), 'Actor edit authority before route call.');
        $security = service('security');
        $config = config('Security');
        $hash = (string) $security->getHash();
        return $this->withSession(session()->get())->withHeaders([
            'Cookie' => $config->cookieName . '=' . $hash, 'X-CSRF-TOKEN' => $hash,
        ])->post($path, array_merge($data, [$config->tokenName => $hash]));
    }

    private function service(): ClinicMembershipService { return new ClinicMembershipService(db: $this->db); }
    private function login(): void
    {
        session()->remove(['is_impersonating', 'impersonated_clinic_id', 'active_clinic_role_id', 'active_clinic_membership_id']);
        session()->set(['identity' => $this->prefix . '-actor@example.test', 'user_id' => $this->actor,
            'last_check' => time(), 'global_mode' => false, 'active_clinic_id' => $this->clinicA]);
    }
    private function newIdentity(): array
    {
        return ['email' => $this->prefix . '-new@example.test', 'password' => 'Synthetic-password-42', 'first_name' => 'Synthetic', 'last_name' => 'New'];
    }
    private function createdIdentity(): ?array { return $this->db->table('users')->where('email', $this->prefix . '-new@example.test')->get()->getRowArray(); }
    private function auditCount(): int { return $this->db->table('control_plane_audits')->where('actor_user_id', $this->actor)->countAllResults(); }
    private function membership(int $clinic, int $user): array { return $this->db->table('clinic_users')->where('clinic_id', $clinic)->where('user_id', $user)->get()->getRowArray(); }
    private function user(string $name, int $active = 1): int
    {
        $this->db->table('users')->insert(['ip_address' => '127.0.0.1', 'username' => $this->prefix . '-' . $name,
            'email' => $this->prefix . '-' . $name . '@example.test', 'password' => password_hash('Synthetic-password-42', PASSWORD_BCRYPT),
            'first_name' => 'Synthetic', 'last_name' => 'Fixture', 'active' => $active, 'created_on' => time()]);
        return (int) $this->db->insertID();
    }
    private function clinic(string $suffix): int
    {
        $this->db->table('clinics')->insert(['name' => $this->prefix . '-' . $suffix, 'slug' => $this->prefix . '-' . $suffix,
            'uuid' => bin2hex(random_bytes(16)), 'status' => 'active']);
        return (int) $this->db->insertID();
    }
    private function customRole(string $name, ?int $clinic): int
    {
        $this->db->table('roles')->insert(['name' => $this->prefix . '-' . $name, 'slug' => $this->prefix . '-' . $name,
            'clinic_id' => $clinic, 'created_by' => $this->actor, 'is_active' => 1, 'is_system' => 0]);
        return (int) $this->db->insertID();
    }
    private function member(int $clinic, int $user, int $role): void
    {
        $this->db->table('clinic_users')->insert(['clinic_id' => $clinic, 'user_id' => $user, 'role_id' => $role, 'status' => 'active']);
    }
    private function systemRole(string $slug): int
    {
        $row = $this->db->table('roles')->where('slug', $slug)->where('clinic_id', null)->get()->getRowArray();
        $this->assertNotEmpty($row);
        return (int) $row['id'];
    }
}
