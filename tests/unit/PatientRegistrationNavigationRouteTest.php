<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\IonAuthModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

require_once dirname(__DIR__, 2) . '/scripts/TestDatabasePreflight.php';

/** SQL-backed registered-route tests; run only through the guarded isolated test runner. */
final class PatientRegistrationNavigationRouteTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $db;
    private array $createdUsers = [];
    private array $createdClinics = [];
    private array $createdMemberships = [];
    private array $createdPlans = [];
    private array $createdSubscriptions = [];
    private array $createdRoles = [];
    private array $createdPermissions = [];
    private array $createdRolePermissions = [];
    private array $createdPatients = [];

    protected function setUp(): void
    {
        $config = new \Config\Database();
        $errors = \Scripts\TestDatabasePreflight::validateConfigBeforeConnection($config->tests, $config->default);
        if ($errors || $config->defaultGroup !== 'tests') {
            throw new \RuntimeException('Isolated tests configuration required.');
        }
        $db = \Config\Database::connect('tests');
        $liveErrors = \Scripts\TestDatabasePreflight::validateLiveConnection($db);
        if ($liveErrors) throw new \RuntimeException(implode('; ', $liveErrors));
        $ownership = \Scripts\TestDatabasePreflight::verifyOwnershipForReset($db);
        if (!$db->tableExists('_test_db_marker') || !$ownership['allowed']) {
            throw new \RuntimeException($ownership['reason']);
        }
        parent::setUp();
        $this->db = \Config\Database::connect('tests');
        $this->withGuestSession();
    }

    protected function tearDown(): void
    {
        if ($this->db) {
            if ($this->createdUsers !== []) {
                $this->db->table('activity_logs')->whereIn('user_id', $this->createdUsers)->delete();
            }
            // A failed assertion may precede recording the newly inserted patient ID.
            if ($this->createdClinics !== []) {
                $this->db->table('patients')->whereIn('clinic_id', $this->createdClinics)->delete();
            }
            $this->deleteRows('patients', $this->createdPatients);
            $this->deleteRows('clinic_subscriptions', $this->createdSubscriptions);
            $this->deleteRows('clinic_users', $this->createdMemberships);
            $this->deleteRows('role_permissions', $this->createdRolePermissions);
            $this->deleteRows('plans', $this->createdPlans);
            $this->deleteRows('clinics', $this->createdClinics);
            $this->deleteRows('users', $this->createdUsers);
            $this->deleteRows('roles', $this->createdRoles);
            $this->deleteRows('permissions', $this->createdPermissions);
        }

        $this->withGuestSession();
        parent::tearDown();
    }

    public function testInvalidNativeSubmissionReturnsRedirectAndInsertsZeroRows(): void
    {
        [$user, $clinicId] = $this->createTenantFixture();
        $this->withLoggedInSession($user, [
            'active_clinic_id' => $clinicId,
            'logged_in' => true,
        ]);

        $beforeCount = $this->db->table('patients')->where('clinic_id', $clinicId)->countAllResults();

        // Submit form with missing required fields (empty first_name, last_name, phone)
        $invalidPayload = [
            'first_name' => '',
            'last_name' => '',
            'phone' => 'invalid-phone',
            'date_of_birth' => 'not-a-date',
            'gender' => '',
            'post_submit_action' => 'save_only',
        ];

        $response = $this->postWithCsrf('/patient/store', $invalidPayload);

        // Native submission failure returns 302 redirect back
        $this->assertTrue($response->isRedirect(), 'Invalid native submission must redirect back');
        $response->assertStatus(302);
        $response->assertSessionHas('errors');
        $response->assertSessionHas('error');

        $errors = session('errors');
        $this->assertIsArray($errors);
        $this->assertArrayHasKey('first_name', $errors);
        $this->assertArrayHasKey('last_name', $errors);
        $this->assertArrayHasKey('phone', $errors);

        // Must insert ZERO patient rows
        $afterCount = $this->db->table('patients')->where('clinic_id', $clinicId)->countAllResults();
        $this->assertSame($beforeCount, $afterCount, 'Zero patient rows must be inserted on validation failure');
    }

    public function testValidSaveOnlyCreatesExactlyOnePatientAndRedirects(): void
    {
        [$user, $clinicId] = $this->createTenantFixture();
        $this->withLoggedInSession($user, [
            'active_clinic_id' => $clinicId,
            'logged_in' => true,
        ]);

        $beforeCount = $this->db->table('patients')->where('clinic_id', $clinicId)->countAllResults();
        $suffix = bin2hex(random_bytes(3));
        $validPayload = [
            'first_name' => 'Synthetic' . $suffix,
            'last_name' => 'SaveOnly' . $suffix,
            'email' => 'saveonly-' . $suffix . '@example.test',
            'phone' => '0599' . random_int(100000, 999999),
            'date_of_birth' => '1990-05-15',
            'gender' => 'male',
            'city' => 'Ramallah',
            'post_submit_action' => 'save_only',
        ];

        $response = $this->postWithCsrf('/patient/store', $validPayload);

        $this->assertTrue($response->isRedirect(), 'Valid save_only submission must redirect');
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Verify patient table row count increased by exactly 1
        $afterCount = $this->db->table('patients')->where('clinic_id', $clinicId)->countAllResults();
        $this->assertSame($beforeCount + 1, $afterCount, 'Exactly 1 patient row must be created');

        // Verify created record matches submitted intent
        $created = $this->db->table('patients')
            ->where('clinic_id', $clinicId)
            ->where('email', $validPayload['email'])
            ->get()
            ->getRowArray();

        $this->assertNotNull($created, 'Created patient record must exist in database');
        $this->assertSame($validPayload['first_name'], $created['first_name']);
        $this->assertSame($validPayload['last_name'], $created['last_name']);
        $this->assertSame($validPayload['phone'], $created['phone']);
        $this->createdPatients[] = (int) $created['id'];
    }

    public function testValidSaveAndBookCreatesExactlyOnePatientAndRedirectsToAppointmentCreate(): void
    {
        [$user, $clinicId] = $this->createTenantFixture();
        $this->withLoggedInSession($user, [
            'active_clinic_id' => $clinicId,
            'logged_in' => true,
        ]);

        $beforeCount = $this->db->table('patients')->where('clinic_id', $clinicId)->countAllResults();
        $suffix = bin2hex(random_bytes(3));
        $validPayload = [
            'first_name' => 'Synthetic' . $suffix,
            'last_name' => 'SaveBook' . $suffix,
            'email' => 'savebook-' . $suffix . '@example.test',
            'phone' => '0598' . random_int(100000, 999999),
            'date_of_birth' => '1988-10-20',
            'gender' => 'female',
            'city' => 'Nablus',
            'post_submit_action' => 'save_and_book',
        ];

        $response = $this->postWithCsrf('/patient/store', $validPayload);

        $this->assertTrue($response->isRedirect(), 'Valid save_and_book submission must redirect');
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Destination must route to appointment booking for this specific patient
        $created = $this->db->table('patients')
            ->where('clinic_id', $clinicId)
            ->where('email', $validPayload['email'])
            ->get()
            ->getRowArray();

        $this->assertNotNull($created, 'Created patient record must exist in database');
        $this->createdPatients[] = (int) $created['id'];

        $expectedPatientId = 'patient_id=' . (int) $created['id'];
        $expectedReturnTo = 'return_to=' . urlencode('patient/' . (int) $created['id']);
        $redirectUrl = (string) $response->getRedirectUrl();
        $this->assertStringContainsString('appointment/create', $redirectUrl, 'Must redirect to appointment creation');
        $this->assertStringContainsString($expectedPatientId, $redirectUrl, 'Must include patient_id query param');
        $this->assertStringContainsString($expectedReturnTo, $redirectUrl, 'Must include encoded return_to param');

        $afterCount = $this->db->table('patients')->where('clinic_id', $clinicId)->countAllResults();
        $this->assertSame($beforeCount + 1, $afterCount, 'Exactly 1 patient row must be created');
    }

    public function testQuickIntakeAjaxValidationFailureReturnsJson(): void
    {
        [$user, $clinicId] = $this->createTenantFixture();
        $this->withLoggedInSession($user, [
            'active_clinic_id' => $clinicId,
            'logged_in' => true,
        ]);

        $security = service('security');
        $securityConfig = config('Security');
        $tokenName = (string) $securityConfig->tokenName;
        $tokenHash = (string) $security->getHash();
        $cookieName = (string) $securityConfig->cookieName;

        // AJAX POST to /patient/store with missing required fields
        $response = $this
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
                'Cookie' => $cookieName . '=' . $tokenHash,
                'X-CSRF-TOKEN' => $tokenHash,
            ])
            ->post('/patient/store', [$tokenName => $tokenHash, 'first_name' => '']);

        $response->assertStatus(200);
        $json = json_decode((string) $response->getJSON(), true);
        $this->assertIsArray($json);
        $this->assertFalse($json['success']);
        $this->assertSame('Validation failed', $json['message']);
        $this->assertArrayHasKey('first_name', $json['errors']);
    }

    public function testAppApiV1ValidationFailureReturns422Json(): void
    {
        [$user, $clinicId] = $this->createTenantFixture();

        // API POST with invalid payload returns 422 Unprocessable Entity
        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinicId])
            ->postWithCsrf('/app-api/v1/patients', [
                'first_name' => 'A',
                'email' => 'not-an-email',
            ]);

        $response->assertStatus(422);
        $json = json_decode((string) $response->getJSON(), true);
        $this->assertIsArray($json);
        $this->assertSame('validation_failed', $json['error']['code'] ?? null);
        $this->assertArrayHasKey('first_name', $json['error']['details']['fields'] ?? []);
        $this->assertArrayHasKey('last_name', $json['error']['details']['fields'] ?? []);
    }

    private function createTenantFixture(): array
    {
        $user = $this->createUser();
        $clinic = $this->createClinic('active');
        $plan = $this->createPlan();
        $role = $this->createRole();

        // Grant patients:create, patients:view, appointments:create
        $this->assignPermission($role, $this->permissionId('patients', 'create'));
        $this->assignPermission($role, $this->permissionId('patients', 'view'));
        $this->assignPermission($role, $this->permissionId('appointments', 'create'));

        $this->createMembership($user['id'], $clinic, $role);
        $this->createSubscription($clinic, $plan, 'active');

        return [$user, $clinic, $role];
    }

    private function createUser(): array
    {
        $suffix = bin2hex(random_bytes(5));
        $email = 'nav-route-test-' . $suffix . '@example.test';
        $password = (new IonAuthModel())->hashPassword('secret123', $email);

        $this->db->table('users')->insert([
            'ip_address' => '127.0.0.1',
            'username' => $email,
            'password' => $password,
            'email' => $email,
            'active' => 1,
            'first_name' => 'Nav',
            'last_name' => 'Tester',
            'created_on' => time(),
            'last_login' => time(),
        ]);

        $id = (int) $this->db->insertID();
        $this->createdUsers[] = $id;

        return ['id' => $id, 'email' => $email];
    }

    private function createClinic(string $status): int
    {
        $suffix = bin2hex(random_bytes(5));
        $now = date('Y-m-d H:i:s');

        $this->db->table('clinics')->insert([
            'name' => 'Nav Route Clinic ' . $suffix,
            'slug' => 'nav-route-' . $suffix,
            'uuid' => $suffix . '-nav-test',
            'status' => $status,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $id = (int) $this->db->insertID();
        $this->createdClinics[] = $id;

        return $id;
    }

    private function createRole(): int
    {
        $now = date('Y-m-d H:i:s');
        $slug = 'nav-role-' . bin2hex(random_bytes(5));
        $this->db->table('roles')->insert([
            'name' => $slug,
            'slug' => $slug,
            'description' => 'Nav route test role',
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

    private function createMembership(int $userId, int $clinicId, int $roleId): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('clinic_users')->insert([
            'clinic_id' => $clinicId,
            'user_id' => $userId,
            'role_id' => $roleId,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $id = (int) $this->db->insertID();
        $this->createdMemberships[] = $id;

        return $id;
    }

    private function createPlan(): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('plans')->insert([
            'name' => 'Nav Route Plan ' . bin2hex(random_bytes(4)),
            'status' => 'active',
            'features_json' => json_encode([]),
            'limits_json' => json_encode(['patients_active_max' => 1000]),
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

    private function permissionId(string $module, string $action): int
    {
        $row = $this->db->table('permissions')
            ->select('id')
            ->where('module', $module)
            ->where('action', $action)
            ->get()
            ->getRowArray();

        if ($row) {
            return (int) $row['id'];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('permissions')->insert([
            'module' => $module,
            'action' => $action,
            'name' => $module . ':' . $action,
            'description' => 'Test permission',
            'category' => $module,
            'is_system' => 0,
            'sort_order' => 999,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $id = (int) $this->db->insertID();
        $this->createdPermissions[] = $id;

        return $id;
    }

    private function assignPermission(int $roleId, int $permissionId): int
    {
        $this->db->table('role_permissions')->insert([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'granted' => 1,
        ]);

        $id = (int) $this->db->insertID();
        $this->createdRolePermissions[] = $id;

        return $id;
    }

    private function withLoggedInSession(array $user, array $extra = []): self
    {
        $values = array_merge([
            'identity' => $user['email'],
            'email' => $user['email'],
            'user_id' => $user['id'],
            'last_check' => time(),
            'global_mode' => false,
            'logged_in' => true,
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
            'logged_in',
        ]);

        return $this->withSession([]);
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
                'Cookie' => $cookieName . '=' . $tokenHash,
                'X-CSRF-TOKEN' => $tokenHash,
            ])
            ->post($path, array_merge($data, [$tokenName => $tokenHash]));
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
