<?php

namespace Tests\Unit;

use App\Models\PlanAuditModel;
use App\Models\SettingsModel;
use App\Models\UserRoleModel;
use App\Services\OnboardingService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;

final class ControlPlaneOnboardingBehaviorTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $db;
    private array $operator;
    private array $dto;
    private array $sessionData;
    private array $tables = ['clinics', 'users', 'users_groups', 'clinic_users', 'user_roles', 'clinic_subscriptions', 'settings', 'plan_audits', 'control_plane_audits', 'roles', 'permissions', 'role_permissions'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect();
        $this->assertSame(0, $this->db->transDepth);
        $this->operator = $this->db->table('users')->select('users.*')
            ->join('user_roles', 'user_roles.user_id = users.id')->join('roles', 'roles.id = user_roles.role_id')
            ->where('roles.slug', 'super_admin')->where('users.active', 1)->get()->getRowArray();
        $this->assertNotEmpty($this->operator, 'Local bootstrap must have an active CP operator.');
        $plan = $this->db->table('plans')->where('status', 'active')->get()->getRowArray();
        $this->assertNotEmpty($plan);
        $token = bin2hex(random_bytes(8));
        $this->dto = ['clinic_name' => 'Onboarding ' . $token, 'admin_name' => 'Onboarding Administrator',
            'admin_email' => 'onboarding-' . $token . '@example.test', 'admin_password' => 'Test-only-password-123', 'plan_id' => $plan['id']];
        $this->sessionData = ['identity' => $this->operator['email'], 'email' => $this->operator['email'],
            'user_id' => (int) $this->operator['id'], 'last_check' => time(), 'logged_in' => true, 'global_mode' => true];
        $_SESSION = $this->sessionData;
        session()->set($this->sessionData);
        session()->remove('active_clinic_id');
    }

    protected function tearDown(): void
    {
        while ($this->db->transDepth > 0) {
            $this->db->transRollback();
        }
        $this->db->resetTransStatus();
        // Success tests intentionally commit without an enclosing transaction, then remove only their own fixtures.
        $clinics = $this->db->table('clinics')->where('name', $this->dto['clinic_name'])->get()->getResultArray();
        foreach ($clinics as $clinic) {
            foreach (['clinic_subscriptions', 'settings', 'plan_audits', 'clinic_users'] as $table) {
                $this->db->table($table)->where('clinic_id', $clinic['id'])->delete();
            }
            $audits = $this->db->table('control_plane_audits')->where('event_type', 'onboarding_success')->get()->getResultArray();
            foreach ($audits as $audit) {
                if ((int) (json_decode($audit['metadata_json'], true)['clinic_id'] ?? 0) === (int) $clinic['id']) {
                    $this->db->table('control_plane_audits')->where('id', $audit['id'])->delete();
                }
            }
            $this->db->table('clinics')->where('id', $clinic['id'])->delete();
        }
        $user = $this->db->table('users')->where('email', $this->dto['admin_email'])->get()->getRowArray();
        if ($user) {
            foreach (['user_roles', 'users_groups'] as $table) {
                $this->db->table($table)->where('user_id', $user['id'])->delete();
            }
            $this->db->table('users')->where('id', $user['id'])->delete();
        }
        parent::tearDown();
    }

    private function counts(): array
    {
        $counts = [];
        foreach ($this->tables as $table) {
            $counts[$table] = $this->db->table($table)->countAllResults();
        }
        return $counts;
    }

    public function testCreationCommitsCompleteOnboardingWithoutTenantContext(): void
    {
        $id = (new OnboardingService())->createClinicWithAdmin($this->dto, (int) $this->operator['id']);
        $this->assertSame(0, $this->db->transDepth);
        $user = $this->db->table('users')->where('email', $this->dto['admin_email'])->get()->getRowArray();
        $this->assertTrue(password_verify($this->dto['admin_password'], $user['password']));
        $this->assertGreaterThan(0, $this->db->table('users_groups')->where('user_id', $user['id'])->countAllResults());
        $admin = (new \App\Services\ClinicAdminLifecycleService())->getClinicAdministrator($id);
        $this->assertTrue($admin['is_healthy']);
        $this->assertSame((int) $user['id'], (int) $admin['user']['id']);
        $this->assertSame(1, $this->db->table('clinic_subscriptions')->where('clinic_id', $id)->countAllResults());
        $this->assertSame(11, $this->db->table('settings')->where('clinic_id', $id)->countAllResults());
        $this->assertSame(1, $this->db->table('plan_audits')->where('clinic_id', $id)->countAllResults());
        $this->assertTrue((bool) session()->get('global_mode'));
        $this->assertNull(session()->get('active_clinic_id'));
    }

    public function testRequiredModelFailuresRollBackAllWrites(): void
    {
        foreach (['userRoleModel' => UserRoleModel::class, 'auditModel' => PlanAuditModel::class,
            'controlPlaneAuditService' => \App\Services\ControlPlaneAuditService::class] as $property => $class) {
            $before = $this->counts();
            $service = new OnboardingService();
            $mock = $this->createMock($class);
            $method = match ($property) {
                'userRoleModel' => 'assignRole', 'controlPlaneAuditService' => 'logEvent', default => 'insert',
            };
            $mock->method($method)->willReturn(false);
            (new \ReflectionProperty($service, $property))->setValue($service, $mock);
            try {
                $service->createClinicWithAdmin($this->dto, (int) $this->operator['id']);
                $this->fail('A required write failure must reject onboarding.');
            } catch (\RuntimeException $e) {
                $this->assertStringContainsString('FAILED:', $e->getMessage());
            }
            $this->assertSame(0, $this->db->transDepth);
            $this->assertSame($before, $this->counts());
        }
    }

    public function testFailedDatabaseStatementCannotCommitEarlierWrites(): void
    {
        $before = $this->counts();
        $service = new OnboardingService();
        $mock = $this->createMock(SettingsModel::class);
        $mock->method('getGlobalSetting')->willReturnCallback(function ($key, $default) {
            $property = new \ReflectionProperty($this->db, 'DBDebug');
            $debug = $property->getValue($this->db);
            $property->setValue($this->db, false);
            try {
                $this->db->query('SELECT * FROM onboarding_nonexistent_test_table');
            } finally {
                $property->setValue($this->db, $debug);
            }
            return $default;
        });
        (new \ReflectionProperty($service, 'settingsModel'))->setValue($service, $mock);
        try {
            $service->createClinicWithAdmin($this->dto, (int) $this->operator['id']);
            $this->fail('A failed statement must prevent commit.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('TRANSACTION_FAILED:', $e->getMessage());
        }
        $this->db->resetTransStatus();
        $this->assertSame($before, $this->counts());
    }

    public function testNestedCallRejectsWithoutCommittingCallerWork(): void
    {
        $this->db->transBegin();
        try {
            (new OnboardingService())->createClinicWithAdmin($this->dto, (int) $this->operator['id']);
            $this->fail('Nested onboarding is forbidden.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('TRANSACTION_FAILED:', $e->getMessage());
        }
        $this->assertSame(1, $this->db->transDepth);
        $this->db->transRollback();
    }

    public function testEmailCheckUsesAuthorizedControlPlaneRoute(): void
    {
        $result = $this->withSession($this->sessionData)->withHeaders(['Accept' => 'application/json'])
            ->get('/controlplane/onboarding/check-email?email=' . urlencode($this->operator['email']));
        $result->assertStatus(200);
        $result->assertJSONFragment(['exists' => true, 'user_id' => (int) $this->operator['id']]);
        $this->withSession($this->sessionData)->withHeaders(['Accept' => 'application/json'])
            ->get('/controlplane/onboarding/check-email?email=invalid')->assertStatus(422);
    }

    public function testUnauthenticatedEmailCheckDoesNotDiscloseUsers(): void
    {
        session()->remove(['identity', 'user_id', 'logged_in', 'global_mode']);
        $_SESSION = [];
        $this->withSession([])->withHeaders(['Accept' => 'application/json'])
            ->get('/controlplane/onboarding/check-email?email=' . urlencode($this->operator['email']))->assertStatus(401);
    }

    public function testExistingUserIsReusedWithoutPasswordOrProfileChanges(): void
    {
        $userModel = new \App\Models\UserModel();
        $id = $userModel->insert(['email' => $this->dto['admin_email'], 'username' => 'reuse_' . bin2hex(random_bytes(6)),
            'first_name' => 'Original', 'last_name' => 'Profile', 'password' => 'Original-password-123', 'active' => 1]);
        $this->assertNotFalse($id);
        $before = $userModel->find($id);
        (new OnboardingService())->createClinicWithAdmin($this->dto, (int) $this->operator['id']);
        $this->assertSame($before, $userModel->find($id));
        $this->assertSame(1, $this->db->table('users')->where('email', $this->dto['admin_email'])->countAllResults());
    }

    public function testDisabledUserAndSuperadminTargetsAreRejectedWithoutWrites(): void
    {
        $model = new \App\Models\UserModel();
        $this->assertNotFalse($model->insert(['email' => $this->dto['admin_email'], 'username' => 'disabled_' . bin2hex(random_bytes(5)),
            'first_name' => 'Disabled', 'last_name' => 'User', 'password' => 'Test-password-123', 'active' => 0]));
        foreach ([$this->dto['admin_email'] => 'USER_INACTIVE:', $this->operator['email'] => 'SUPERADMIN_FORBIDDEN:'] as $email => $code) {
            $before = $this->counts();
            $dto = array_replace($this->dto, ['admin_email' => $email]);
            try {
                (new OnboardingService())->createClinicWithAdmin($dto, (int) $this->operator['id']);
                $this->fail('Ineligible administrator must be rejected.');
            } catch (\RuntimeException $e) {
                $this->assertStringContainsString($code, $e->getMessage());
            }
            $this->assertSame($before, $this->counts());
        }
    }

    public function testInvalidEmailIsRejectedServerSideWithoutWrites(): void
    {
        $before = $this->counts();
        try {
            (new OnboardingService())->createClinicWithAdmin(array_replace($this->dto, ['admin_email' => 'invalid']), (int) $this->operator['id']);
            $this->fail('Invalid email must be rejected.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('VALIDATION_FAILED:', $e->getMessage());
        }
        $this->assertSame($before, $this->counts());
    }

    public function testAuthenticatedNonSuperadminCannotUseGlobalEmailCheck(): void
    {
        $model = new \App\Models\UserModel();
        $id = $model->insert(['email' => $this->dto['admin_email'], 'username' => 'tenant_' . bin2hex(random_bytes(6)),
            'first_name' => 'Tenant', 'last_name' => 'User', 'password' => 'Test-password-123', 'active' => 1]);
        $data = array_replace($this->sessionData, ['identity' => $this->dto['admin_email'], 'email' => $this->dto['admin_email'], 'user_id' => $id]);
        $_SESSION = $data;
        session()->set($data);
        try {
            $this->withSession($data)->withHeaders(['Accept' => 'application/json'])
                ->get('/controlplane/onboarding/check-email?email=' . urlencode($this->operator['email']));
            $this->fail('Non-superadmin must be denied by the registered route filter.');
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            $this->assertSame(404, $e->getCode());
        }
        $before = $this->counts();
        try {
            (new OnboardingService())->createClinicWithAdmin($this->dto, (int) $id);
            $this->fail('Global mode alone must not authorize onboarding.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('ACCESS_DENIED:', $e->getMessage());
        }
        $this->assertSame($before, $this->counts());
    }

    public function testAuthorizedCreatePostCommitsAndRedirects(): void
    {
        $security = service('security');
        $config = config('Security');
        $hash = $security->getHash();
        $result = $this->withSession($this->sessionData)->withHeaders([
            'Cookie' => $config->cookieName . '=' . $hash, 'X-CSRF-TOKEN' => $hash,
        ])->post('/controlplane/onboarding/clinic/create', array_merge($this->dto, [$config->tokenName => $hash]));
        $result->assertRedirectTo('/controlplane/clinics');
        $clinic = $this->db->table('clinics')->where('name', $this->dto['clinic_name'])->get()->getRowArray();
        $this->assertNotEmpty($clinic);
        $this->assertTrue((new \App\Services\ClinicAdminLifecycleService())->getClinicAdministrator((int) $clinic['id'])['is_healthy']);
    }

    public function testEmailCheckDatabaseFailureReturnsSanitizedJson(): void
    {
        $controller = new \App\Controllers\ControlPlane\Onboarding();
        $request = \Config\Services::incomingrequest(null, false);
        $request->setGlobal('get', ['email' => $this->dto['admin_email']]);
        $controller->initController($request, \Config\Services::response(null, false), service('logger'));
        $mock = $this->getMockBuilder(\App\Models\UserModel::class)->onlyMethods(['first'])->getMock();
        $mock->method('first')->willThrowException(new \RuntimeException('secret database diagnostic'));
        (new \ReflectionProperty($controller, 'userModel'))->setValue($controller, $mock);
        $response = $controller->checkEmail();
        $this->assertSame(503, $response->getStatusCode());
        $this->assertStringNotContainsString('secret', $response->getBody());
        $this->assertSame('email_check_unavailable', json_decode($response->getBody(), true)['error']['code']);
    }

    public function testLocalizedFailureAndRenderedOnboardingJavaScript(): void
    {
        foreach (['en', 'ar'] as $locale) {
            $data = array_replace($this->sessionData, ['cp_ui_locale' => $locale]);
            session()->set($data);
            service('language')->setLocale($locale);
            $security = service('security');
            $config = config('Security');
            $hash = $security->getHash();
            $result = $this->withSession($data)->withHeaders(['Cookie' => $config->cookieName . '=' . $hash, 'X-CSRF-TOKEN' => $hash])
                ->post('/controlplane/onboarding/clinic/create', array_replace($this->dto, ['plan_id' => 0, $config->tokenName => $hash]));
            $result->assertStatus(302);
            $this->assertSame(lang('UI.onboardingInvalidPlan', [], $locale), session()->getFlashdata('error'));
            $page = $this->withSession($data)->get('/controlplane/onboarding/clinic/create');
            $page->assertStatus(200);
            $html = $page->response()->getBody();
            $this->assertStringContainsString('controlplane/onboarding/check-email', $html);
            preg_match_all('/<script(?![^>]*\bsrc=)[^>]*>(.*?)<\/script>/is', $html, $scripts);
            $this->assertNotEmpty($scripts[1]);
            $path = WRITEPATH . 'onboarding-js-' . bin2hex(random_bytes(8)) . '.js';
            try {
                foreach ($scripts[1] as $script) {
                    file_put_contents($path, $script);
                    $output = [];
                    exec('node --check ' . escapeshellarg($path) . ' 2>&1', $output, $status);
                    $this->assertSame(0, $status, implode("\n", $output));
                }
            } finally {
                unlink($path);
            }
        }
        service('language')->setLocale('en');
    }
}
