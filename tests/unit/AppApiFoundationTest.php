<?php

namespace Tests\Unit;

use App\Models\IonAuthModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class AppApiFoundationTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $db;
    private array $createdUsers = [];
    private array $createdClinics = [];
    private array $createdMemberships = [];
    private array $createdPlans = [];
    private array $createdSubscriptions = [];
    private array $createdRoles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withGuestSession();
        $this->db = \Config\Database::connect();
    }

    protected function tearDown(): void
    {
        if ($this->db) {
            $this->deleteRows('clinic_subscriptions', $this->createdSubscriptions);
            $this->deleteRows('clinic_users', $this->createdMemberships);
            $this->deleteRows('plans', $this->createdPlans);
            $this->deleteRows('clinics', $this->createdClinics);
            $this->deleteRows('users', $this->createdUsers);
            $this->deleteRows('roles', $this->createdRoles);
        }
        $this->withGuestSession();
        parent::tearDown();
    }

    public function testCsrfReturnsJsonBeforeLoginAndLeaksNoPrivateData(): void
    {
        $response = $this->get('/app-api/v1/csrf');

        $response->assertStatus(200);

        $json = $this->decode($response);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('csrf', $json['data']);
        $this->assertSame('X-CSRF-TOKEN', $json['data']['csrf']['header'] ?? null);
        $this->assertArrayNotHasKey('user', $json['data']);
        $this->assertArrayNotHasKey('clinic', $json['data']);
        $this->assertStringNotContainsString('password', (string) $response->getJSON());
        $this->assertStringNotContainsString('active_clinic_id', (string) $response->getJSON());
    }

    public function testLoginWithoutCsrfReturns419Json(): void
    {
        $response = $this->post('/app-api/v1/auth/login', [
            'identity' => 'missing@example.test',
            'password' => 'password',
        ]);

        $response->assertStatus(419);
        $this->assertSame('csrf_failed', $this->decode($response)['error']['code'] ?? null);
    }

    public function testInvalidLoginWithValidCsrfReturns401Json(): void
    {
        $response = $this->postWithCsrf('/app-api/v1/auth/login', [
            'identity' => 'missing@example.test',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
        $this->assertSame('unauthenticated', $this->decode($response)['error']['code'] ?? null);
    }

    public function testLoginValidationErrorWithValidCsrfReturns422(): void
    {
        $response = $this->postWithCsrf('/app-api/v1/auth/login', [
            'identity' => '',
            'password' => '',
        ]);

        $response->assertStatus(422);
        $this->assertSame('validation_failed', $this->decode($response)['error']['code'] ?? null);
    }

    public function testBootstrapUnauthenticatedReturns401Json(): void
    {
        $response = $this->get('/app-api/v1/bootstrap');

        $response->assertStatus(401);
        $this->assertSame('unauthenticated', $this->decode($response)['error']['code'] ?? null);
    }

    public function testBootstrapNoClinicState(): void
    {
        $user = $this->createUser();

        $response = $this->withLoggedInSession($user)->get('/app-api/v1/bootstrap');

        $response->assertStatus(200);
        $this->assertSame('no_clinic', $this->decode($response)['data']['app_state'] ?? null);
    }

    public function testBootstrapClinicRequiredState(): void
    {
        $user = $this->createUser();
        $clinicA = $this->createClinic('active');
        $clinicB = $this->createClinic('active');
        $this->createMembership($user['id'], $clinicA);
        $this->createMembership($user['id'], $clinicB);

        $response = $this->withLoggedInSession($user)->get('/app-api/v1/bootstrap');

        $response->assertStatus(200);
        $this->assertSame('clinic_required', $this->decode($response)['data']['app_state'] ?? null);
    }

    public function testBootstrapTenantReadyState(): void
    {
        [$user, $clinic] = $this->createTenantReadyFixture();

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->get('/app-api/v1/bootstrap');

        $response->assertStatus(200);
        $this->assertSame('tenant_ready', $this->decode($response)['data']['app_state'] ?? null);
    }

    public function testBootstrapGlobalModeState(): void
    {
        $user = $this->createUser();

        $response = $this
            ->withLoggedInSession($user, ['global_mode' => true])
            ->get('/app-api/v1/bootstrap');

        $response->assertStatus(200);
        $this->assertSame('controlplane_ready', $this->decode($response)['data']['app_state'] ?? null);
    }

    public function testBootstrapSuspendedClinicState(): void
    {
        $user = $this->createUser();
        $clinic = $this->createClinic('suspended');
        $this->createMembership($user['id'], $clinic);

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->get('/app-api/v1/bootstrap');

        $response->assertStatus(200);
        $this->assertSame('suspended', $this->decode($response)['data']['app_state'] ?? null);
    }

    public function testBootstrapSubscriptionInactiveState(): void
    {
        $user = $this->createUser();
        $clinic = $this->createClinic('active');
        $plan = $this->createPlan();
        $this->createMembership($user['id'], $clinic);
        $this->createSubscription($clinic, $plan, 'paused');

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->get('/app-api/v1/bootstrap');

        $response->assertStatus(200);
        $this->assertSame('subscription_inactive', $this->decode($response)['data']['app_state'] ?? null);
    }

    public function testClinicSwitchValidatesMembership(): void
    {
        $user = $this->createUser();
        $clinic = $this->createClinic('active');

        $response = $this
            ->withLoggedInSession($user)
            ->postWithCsrf('/app-api/v1/clinic/switch', ['clinic_id' => $clinic]);

        $response->assertStatus(403);
        $this->assertSame('forbidden', $this->decode($response)['error']['code'] ?? null);
    }

    public function testClinicSwitchRegeneratesSessionAndReturnsCsrf(): void
    {
        $user = $this->createUser();
        $clinic = $this->createClinic('active');
        $plan = $this->createPlan();
        $this->createMembership($user['id'], $clinic);
        $this->createSubscription($clinic, $plan, 'active');

        $session = session();
        $before = session_id();

        $response = $this
            ->withLoggedInSession($user)
            ->postWithCsrf('/app-api/v1/clinic/switch', ['clinic_id' => $clinic]);

        $response->assertStatus(200);

        $json = $this->decode($response);
        $this->assertSame('tenant_ready', $json['data']['app_state'] ?? null);
        $this->assertSame($clinic, (int) ($json['data']['active_clinic']['id'] ?? 0));
        $this->assertArrayHasKey('csrf', $json['meta']);
        $this->assertArrayHasKey('hash', $json['meta']['csrf']);

        $after = session_id();
        if ($before !== '' && $after !== '') {
            $this->assertNotSame($before, $after);
        } else {
            $this->assertTrue($session->has('active_clinic_id'));
        }
    }

    public function testApiV1StillUsesStatelessAuth(): void
    {
        $response = $this->get('/api/v1/patients');

        $response->assertStatus(401);
        $this->assertSame('unauthenticated', $this->decode($response)['error']['code'] ?? null);
    }

    public function testExistingWebLoginRouteStillResponds(): void
    {
        $response = $this->get('/auth/login');

        $this->assertContains($response->response()->getStatusCode(), [200, 302, 307]);
    }

    private function postWithCsrf(string $path, array $data)
    {
        $security = service('security');
        $securityConfig = config('Security');
        $tokenName = (string) $securityConfig->tokenName;
        $tokenHash = (string) $security->getHash();
        $cookieName = (string) $securityConfig->cookieName;

        return $this
            ->withHeaders([
                'Accept' => 'application/json',
                'Cookie' => $cookieName . '=' . $tokenHash,
                'X-CSRF-TOKEN' => $tokenHash,
            ])
            ->post($path, array_merge($data, [$tokenName => $tokenHash]));
    }

    private function withLoggedInSession(array $user, array $extra = []): self
    {
        $values = array_merge([
            'identity' => $user['email'],
            'email' => $user['email'],
            'user_id' => $user['id'],
            'last_check' => time(),
            'global_mode' => false,
        ], $extra);

        $_SESSION = $values;
        session()->set($values);

        return $this->withSession($values);
    }

    private function withGuestSession(): self
    {
        $_SESSION = [];
        session()->remove([
            'identity',
            'email',
            'user_id',
            'last_check',
            'global_mode',
            'active_clinic_id',
            'active_clinic_role_id',
            'active_clinic_membership_id',
            'user_permissions',
        ]);

        return $this->withSession([]);
    }

    private function createTenantReadyFixture(): array
    {
        $user = $this->createUser();
        $clinic = $this->createClinic('active');
        $plan = $this->createPlan();
        $this->createMembership($user['id'], $clinic);
        $this->createSubscription($clinic, $plan, 'active');

        return [$user, $clinic];
    }

    private function createUser(): array
    {
        $suffix = bin2hex(random_bytes(5));
        $email = 'app-api-' . $suffix . '@example.test';
        $password = (new IonAuthModel())->hashPassword('secret123', $email);

        $this->db->table('users')->insert([
            'ip_address' => '127.0.0.1',
            'username' => $email,
            'password' => $password,
            'email' => $email,
            'active' => 1,
            'first_name' => 'App',
            'last_name' => 'Api',
            'created_on' => time(),
            'last_login' => time(),
        ]);

        $id = (int) $this->db->insertID();
        $this->createdUsers[] = $id;

        return [
            'id' => $id,
            'email' => $email,
        ];
    }

    private function createClinic(string $status): int
    {
        $suffix = bin2hex(random_bytes(5));
        $now = date('Y-m-d H:i:s');

        $this->db->table('clinics')->insert([
            'name' => 'App API Clinic ' . $suffix,
            'slug' => 'app-api-' . $suffix,
            'uuid' => $suffix . '-app-api-test',
            'status' => $status,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $id = (int) $this->db->insertID();
        $this->createdClinics[] = $id;

        return $id;
    }

    private function createMembership(int $userId, int $clinicId): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinicId,
            'user_id' => $userId,
            'role_id' => $this->roleId(),
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $id = (int) $this->db->insertID();
        $this->createdMemberships[] = $id;

        return $id;
    }

    private function roleId(): int
    {
        $row = $this->db->table('roles')
            ->select('id')
            ->orderBy('id', 'ASC')
            ->get(1)
            ->getRowArray();

        if ($row) {
            return (int) $row['id'];
        }

        $now = date('Y-m-d H:i:s');
        $slug = 'app-api-test-role-' . bin2hex(random_bytes(4));
        $this->db->table('roles')->insert([
            'name' => $slug,
            'slug' => $slug,
            'description' => 'App API test role',
            'is_active' => 1,
            'is_system' => 0,
            'is_doctor' => 0,
            'created_by' => 1,
            'created_at' => $now,
            'updated_at' => $now,
            'is_medical' => 0,
            'clinic_id' => null,
        ]);

        $id = (int) $this->db->insertID();
        $this->createdRoles[] = $id;

        return $id;
    }

    private function createPlan(): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('plans')->insert([
            'name' => 'App API Test Plan ' . bin2hex(random_bytes(4)),
            'status' => 'active',
            'features_json' => json_encode(['inventory' => true, 'whatsapp' => true]),
            'limits_json' => json_encode([]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $id = (int) $this->db->insertID();
        $this->createdPlans[] = $id;

        return $id;
    }

    private function createSubscription(int $clinicId, int $planId, string $status): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('clinic_subscriptions')->insert([
            'clinic_id' => $clinicId,
            'plan_id' => $planId,
            'status' => $status,
            'start_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'end_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $id = (int) $this->db->insertID();
        $this->createdSubscriptions[] = $id;

        return $id;
    }

    private function decode($response): array
    {
        $json = json_decode((string) $response->getJSON(), true);
        $this->assertIsArray($json);
        return $json;
    }

    private function deleteRows(string $table, array $ids): void
    {
        $ids = array_values(array_filter(array_unique(array_map('intval', $ids))));
        if ($ids === []) {
            return;
        }

        $this->db->table($table)->whereIn('id', $ids)->delete();
    }
}
