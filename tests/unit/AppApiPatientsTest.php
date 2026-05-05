<?php

namespace Tests\Unit;

use App\Models\IonAuthModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class AppApiPatientsTest extends CIUnitTestCase
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
        parent::setUp();
        $this->db = \Config\Database::connect();
        $this->withGuestSession();
    }

    protected function tearDown(): void
    {
        if ($this->db) {
            if ($this->createdUsers !== []) {
                $this->db->table('activity_logs')->whereIn('user_id', $this->createdUsers)->delete();
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

    public function testPatientsListUnauthenticatedReturns401Json(): void
    {
        $response = $this->get('/app-api/v1/patients');

        $response->assertStatus(401);
        $this->assertSame('unauthenticated', $this->decode($response)['error']['code'] ?? null);
        $this->assertFalse($response->isRedirect());
    }

    public function testPatientsListWithoutActiveClinicReturns422Json(): void
    {
        [$user] = $this->createTenantFixture();

        $response = $this->withLoggedInSession($user)->get('/app-api/v1/patients');

        $response->assertStatus(422);
        $this->assertSame('tenant_context_missing', $this->decode($response)['error']['code'] ?? null);
    }

    public function testPatientsListWithGlobalModeReturns403Json(): void
    {
        [$user, $clinic] = $this->createTenantFixture();

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic, 'global_mode' => true])
            ->get('/app-api/v1/patients');

        $response->assertStatus(403);
        $this->assertSame('tenant_plane_required', $this->decode($response)['error']['code'] ?? null);
    }

    public function testPatientsListWithoutPermissionReturns403Json(): void
    {
        [$user, $clinic] = $this->createTenantFixture(false);

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->get('/app-api/v1/patients');

        $response->assertStatus(403);
        $this->assertSame('forbidden', $this->decode($response)['error']['code'] ?? null);
    }

    public function testPatientsListWithInactiveSubscriptionReturns403Json(): void
    {
        [$user, $clinic] = $this->createTenantFixture(true, 'paused');

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->get('/app-api/v1/patients');

        $response->assertStatus(403);
        $this->assertSame('subscription_inactive', $this->decode($response)['error']['code'] ?? null);
    }

    public function testPatientsListReturnsOnlyActiveClinicPatients(): void
    {
        [$user, $clinic] = $this->createTenantFixture();
        $otherClinic = $this->createClinic('active');
        $visible = $this->createPatient($clinic, 'Visible', 'Patient');
        $this->createPatient($otherClinic, 'Hidden', 'Patient');

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->get('/app-api/v1/patients');

        $response->assertStatus(200);
        $json = $this->decode($response);
        $this->assertCount(1, $json['data']['patients']);
        $this->assertSame($visible, (int) $json['data']['patients'][0]['id']);
        $this->assertSame(1, (int) $json['meta']['pagination']['total']);
        $this->assertArrayHasKey('request_id', $json['meta']);
    }

    public function testPatientsListSupportsPaginationEnvelope(): void
    {
        [$user, $clinic] = $this->createTenantFixture();
        $this->createPatient($clinic, 'One', 'Patient');
        $this->createPatient($clinic, 'Two', 'Patient');

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->get('/app-api/v1/patients?per_page=1&page=2&sort=first_name&direction=asc');

        $response->assertStatus(200);
        $json = $this->decode($response);
        $this->assertCount(1, $json['data']['patients']);
        $this->assertSame(2, (int) $json['meta']['pagination']['page']);
        $this->assertSame(1, (int) $json['meta']['pagination']['per_page']);
        $this->assertSame(2, (int) $json['meta']['pagination']['total']);
        $this->assertSame('first_name', $json['meta']['sort']['field']);
    }

    public function testPatientsListRejectsUnsupportedSort(): void
    {
        [$user, $clinic] = $this->createTenantFixture();

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->get('/app-api/v1/patients?sort=clinic_id');

        $response->assertStatus(422);
        $this->assertSame('validation_failed', $this->decode($response)['error']['code'] ?? null);
    }

    public function testPatientsListRejectsUnsupportedFilter(): void
    {
        [$user, $clinic] = $this->createTenantFixture();

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->get('/app-api/v1/patients?filter[clinic_id]=1');

        $response->assertStatus(422);
        $this->assertSame('validation_failed', $this->decode($response)['error']['code'] ?? null);
    }

    public function testPatientDetailReturnsActiveClinicPatient(): void
    {
        [$user, $clinic] = $this->createTenantFixture();
        $patientId = $this->createPatient($clinic, 'Detail', 'Patient', [
            'alert_has_allergies' => 1,
            'alert_allergies_details' => 'Penicillin',
        ]);

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->get('/app-api/v1/patients/' . $patientId);

        $response->assertStatus(200);
        $json = $this->decode($response);
        $this->assertSame($patientId, (int) $json['data']['patient']['id']);
        $this->assertSame('Detail Patient', $json['data']['patient']['display_name']);
        $this->assertSame('allergies', $json['data']['patient']['medical']['alerts'][0]['code'] ?? null);
    }

    public function testPatientDetailCrossTenantReturns404(): void
    {
        [$user, $clinic] = $this->createTenantFixture();
        $otherClinic = $this->createClinic('active');
        $patientId = $this->createPatient($otherClinic, 'Other', 'Patient');

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->get('/app-api/v1/patients/' . $patientId);

        $response->assertStatus(404);
        $this->assertSame('not_found', $this->decode($response)['error']['code'] ?? null);
    }

    public function testPatientDetailDoesNotLeakPrivateFields(): void
    {
        [$user, $clinic] = $this->createTenantFixture();
        $patientId = $this->createPatient($clinic, 'Safe', 'Patient');

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->get('/app-api/v1/patients/' . $patientId);

        $response->assertStatus(200);
        $body = (string) $response->getJSON();
        $this->assertStringNotContainsString('clinic_id', $body);
        $this->assertStringNotContainsString('active_clinic_id', $body);
        $this->assertStringNotContainsString('file_path', $body);
        $this->assertStringNotContainsString('password', $body);
        $this->assertStringNotContainsString('writable/uploads', $body);
        $this->assertStringNotContainsString('public/uploads', $body);
    }

    public function testApiV1PatientsBehaviorRemainsStateless(): void
    {
        [$user, $clinic] = $this->createTenantFixture();

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->get('/api/v1/patients');

        $response->assertStatus(401);
        $this->assertSame('unauthenticated', $this->decode($response)['error']['code'] ?? null);
    }

    public function testWebPatientRouteStillRedirectsForGuest(): void
    {
        $response = $this->get('/patients');

        $this->assertTrue($response->isRedirect());
    }

    public function testPatientCreateWithoutCsrfReturns419Json(): void
    {
        $response = $this->post('/app-api/v1/patients', [
            'first_name' => 'No',
            'last_name' => 'Csrf',
        ]);

        $response->assertStatus(419);
        $this->assertSame('csrf_failed', $this->decode($response)['error']['code'] ?? null);
    }

    public function testPatientCreateUnauthenticatedWithValidCsrfReturns401Json(): void
    {
        $response = $this->postWithCsrf('/app-api/v1/patients', [
            'first_name' => 'Guest',
            'last_name' => 'Patient',
        ]);

        $response->assertStatus(401);
        $this->assertSame('unauthenticated', $this->decode($response)['error']['code'] ?? null);
    }

    public function testPatientCreateWithoutActiveClinicReturns422Json(): void
    {
        [$user] = $this->createTenantFixture(['view', 'create']);

        $response = $this
            ->withLoggedInSession($user)
            ->postWithCsrf('/app-api/v1/patients', $this->validPatientPayload());

        $response->assertStatus(422);
        $this->assertSame('tenant_context_missing', $this->decode($response)['error']['code'] ?? null);
    }

    public function testPatientCreateWithoutCreatePermissionReturns403Json(): void
    {
        [$user, $clinic] = $this->createTenantFixture(['view']);
        $before = (int) $this->db->table('patients')->where('clinic_id', $clinic)->countAllResults();

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->postWithCsrf('/app-api/v1/patients', $this->validPatientPayload());

        $response->assertStatus(403);
        $this->assertSame('forbidden', $this->decode($response)['error']['code'] ?? null);
        $after = (int) $this->db->table('patients')->where('clinic_id', $clinic)->countAllResults();
        $this->assertSame($before, $after);
    }

    public function testPatientCreateValidationFailureReturns422WithFieldErrors(): void
    {
        [$user, $clinic] = $this->createTenantFixture(['view', 'create']);

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->postWithCsrf('/app-api/v1/patients', [
                'first_name' => 'A',
                'email' => 'not-an-email',
            ]);

        $response->assertStatus(422);
        $json = $this->decode($response);
        $this->assertSame('validation_failed', $json['error']['code'] ?? null);
        $this->assertArrayHasKey('first_name', $json['error']['details']['fields'] ?? []);
        $this->assertArrayHasKey('last_name', $json['error']['details']['fields'] ?? []);
        $this->assertArrayHasKey('email', $json['error']['details']['fields'] ?? []);
    }

    public function testPatientCreateForcesActiveClinicAndIgnoresProtectedFields(): void
    {
        [$user, $clinic] = $this->createTenantFixture(['view', 'create']);
        $otherClinic = $this->createClinic('active');
        $payload = $this->validPatientPayload([
            'clinic_id' => $otherClinic,
            'patient_id' => 'CLIENT-SHOULD-NOT-WIN',
            'created_by' => 999999,
            'file_path' => 'writable/uploads/private.pdf',
        ]);

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->postWithCsrf('/app-api/v1/patients', $payload);

        $response->assertStatus(201);
        $json = $this->decode($response);
        $id = (int) ($json['data']['patient']['id'] ?? 0);
        $this->assertGreaterThan(0, $id);
        $this->createdPatients[] = $id;
        $this->assertArrayHasKey('csrf', $json['meta']);

        $row = $this->db->table('patients')->where('id', $id)->get()->getRowArray();
        $this->assertSame($clinic, (int) $row['clinic_id']);
        $this->assertNotSame('CLIENT-SHOULD-NOT-WIN', $row['patient_id']);
        $this->assertSame((int) $user['id'], (int) $row['created_by']);
        $this->assertSame('active', $row['status']);

        $body = (string) $response->getJSON();
        $this->assertStringNotContainsString('clinic_id', $body);
        $this->assertStringNotContainsString('file_path', $body);
        $this->assertStringNotContainsString('password', $body);
        $this->assertStringNotContainsString('writable/uploads', $body);
    }

    public function testPatientPatchWithoutCsrfReturns419Json(): void
    {
        $response = $this->patch('/app-api/v1/patients/1', [
            'first_name' => 'No',
        ]);

        $response->assertStatus(419);
        $this->assertSame('csrf_failed', $this->decode($response)['error']['code'] ?? null);
    }

    public function testPatientPatchWithoutEditPermissionReturns403Json(): void
    {
        [$user, $clinic] = $this->createTenantFixture(['view']);
        $patientId = $this->createPatient($clinic, 'Readonly', 'Patient');
        $before = $this->db->table('patients')->where('id', $patientId)->get()->getRowArray();

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->patchWithCsrf('/app-api/v1/patients/' . $patientId, [
                'first_name' => 'Blocked',
            ]);

        $response->assertStatus(403);
        $this->assertSame('forbidden', $this->decode($response)['error']['code'] ?? null);
        $after = $this->db->table('patients')->where('id', $patientId)->get()->getRowArray();
        $this->assertSame($before['first_name'], $after['first_name']);
    }

    public function testPatientPatchUpdatesOnlyAllowlistedFields(): void
    {
        [$user, $clinic] = $this->createTenantFixture(['view', 'edit']);
        $otherClinic = $this->createClinic('active');
        $patientId = $this->createPatient($clinic, 'Before', 'Patient', [
            'patient_id' => 'PATSAFE001',
            'created_by' => 321,
        ]);

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->patchWithCsrf('/app-api/v1/patients/' . $patientId, [
                'first_name' => 'After',
                'city' => 'Hebron',
                'clinic_id' => $otherClinic,
                'patient_id' => 'CLIENT-SHOULD-NOT-WIN',
                'created_by' => 999999,
                'file_path' => 'writable/uploads/private.pdf',
            ]);

        $response->assertStatus(200);
        $json = $this->decode($response);
        $this->assertSame('After', $json['data']['patient']['first_name'] ?? null);
        $this->assertSame('Hebron', $json['data']['patient']['city'] ?? null);
        $this->assertArrayHasKey('csrf', $json['meta']);

        $row = $this->db->table('patients')->where('id', $patientId)->get()->getRowArray();
        $this->assertSame($clinic, (int) $row['clinic_id']);
        $this->assertSame('PATSAFE001', $row['patient_id']);
        $this->assertSame(321, (int) $row['created_by']);
        $this->assertSame('After', $row['first_name']);
    }

    public function testPatientPatchCrossTenantReturns404(): void
    {
        [$user, $clinic] = $this->createTenantFixture(['view', 'edit']);
        $otherClinic = $this->createClinic('active');
        $patientId = $this->createPatient($otherClinic, 'Other', 'Patient');
        $before = $this->db->table('patients')->where('id', $patientId)->get()->getRowArray();

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->patchWithCsrf('/app-api/v1/patients/' . $patientId, [
                'first_name' => 'Blocked',
            ]);

        $response->assertStatus(404);
        $this->assertSame('not_found', $this->decode($response)['error']['code'] ?? null);
        $after = $this->db->table('patients')->where('id', $patientId)->get()->getRowArray();
        $this->assertSame($before['first_name'], $after['first_name']);
        $this->assertSame($otherClinic, (int) $after['clinic_id']);
    }

    public function testPatientPatchValidationFailureReturns422(): void
    {
        [$user, $clinic] = $this->createTenantFixture(['view', 'edit']);
        $patientId = $this->createPatient($clinic, 'Valid', 'Patient');

        $response = $this
            ->withLoggedInSession($user, ['active_clinic_id' => $clinic])
            ->patchWithCsrf('/app-api/v1/patients/' . $patientId, [
                'email' => 'bad-email',
                'gender' => 'robot',
            ]);

        $response->assertStatus(422);
        $json = $this->decode($response);
        $this->assertSame('validation_failed', $json['error']['code'] ?? null);
        $this->assertArrayHasKey('email', $json['error']['details']['fields'] ?? []);
        $this->assertArrayHasKey('gender', $json['error']['details']['fields'] ?? []);
    }

    private function createTenantFixture($permissions = true, string $subscriptionStatus = 'active'): array
    {
        $user = $this->createUser();
        $clinic = $this->createClinic('active');
        $plan = $this->createPlan();
        $role = $this->createRole();

        $actions = [];
        if ($permissions === true) {
            $actions = ['view'];
        } elseif (is_array($permissions)) {
            $actions = $permissions;
        }

        foreach ($actions as $action) {
            $this->assignPermission($role, $this->permissionId('patients', (string) $action));
        }

        $this->createMembership($user['id'], $clinic, $role);
        $this->createSubscription($clinic, $plan, $subscriptionStatus);

        return [$user, $clinic, $role];
    }

    private function validPatientPayload(array $extra = []): array
    {
        $suffix = bin2hex(random_bytes(3));

        return array_merge([
            'first_name' => 'Create',
            'last_name' => 'Patient',
            'email' => 'create-' . $suffix . '@example.test',
            'phone' => '059' . random_int(1000000, 9999999),
            'date_of_birth' => '1985-05-10',
            'gender' => 'female',
            'city' => 'Ramallah',
            'alert_has_allergies' => true,
            'alert_allergies_details' => 'Penicillin',
            'insurance_provider' => 'Clinic Plan',
            'insurance_number' => 'INS-' . strtoupper($suffix),
            'notes' => 'Created through App API test',
        ], $extra);
    }

    private function createUser(): array
    {
        $suffix = bin2hex(random_bytes(5));
        $email = 'app-api-patient-' . $suffix . '@example.test';
        $password = (new IonAuthModel())->hashPassword('secret123', $email);

        $this->db->table('users')->insert([
            'ip_address' => '127.0.0.1',
            'username' => $email,
            'password' => $password,
            'email' => $email,
            'active' => 1,
            'first_name' => 'Patient',
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
            'name' => 'App API Patients Clinic ' . $suffix,
            'slug' => 'app-api-patients-' . $suffix,
            'uuid' => $suffix . '-patients-test',
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
        $slug = 'app-api-patient-role-' . bin2hex(random_bytes(5));
        $this->db->table('roles')->insert([
            'name' => $slug,
            'slug' => $slug,
            'description' => 'App API patients test role',
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
            'name' => 'App API Patients Plan ' . bin2hex(random_bytes(4)),
            'status' => 'active',
            'features_json' => json_encode([]),
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

    private function createPatient(int $clinicId, string $firstName, string $lastName, array $extra = []): int
    {
        $now = date('Y-m-d H:i:s');
        $suffix = bin2hex(random_bytes(3));
        $data = array_merge([
            'clinic_id' => $clinicId,
            'patient_id' => 'PATTEST' . strtoupper($suffix),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower($firstName) . '-' . $suffix . '@example.test',
            'phone' => '059' . random_int(1000000, 9999999),
            'date_of_birth' => '1990-01-01',
            'gender' => 'other',
            'city' => 'Ramallah',
            'status' => 'active',
            'medical_history' => null,
            'allergies' => null,
            'notes' => null,
            'created_by' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ], $extra);

        $this->db->table('patients')->insert($data);

        $id = (int) $this->db->insertID();
        $this->createdPatients[] = $id;

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

    private function patchWithCsrf(string $path, array $data)
    {
        $security = service('security');
        $securityConfig = config('Security');
        $tokenName = (string) $securityConfig->tokenName;
        $tokenHash = (string) $security->getHash();
        $cookieName = (string) $securityConfig->cookieName;

        return $this
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Cookie' => $cookieName . '=' . $tokenHash,
                'X-CSRF-TOKEN' => $tokenHash,
            ])
            ->withBody(json_encode($data))
            ->patch($path, [$tokenName => $tokenHash]);
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
