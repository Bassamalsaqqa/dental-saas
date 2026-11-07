<?php

declare(strict_types=1);

namespace Tests\Unit;

require_once dirname(__DIR__) . '/_support/PhaseBFixture.php';
require_once dirname(__DIR__, 2) . '/scripts/TestDatabasePreflight.php';

use Tests\Support\PhaseBFixture;

/**
 * Real registered web routes; executed only against the isolated synthetic target on port 3307.
 */
final class PatientWorkflowRoutingHttpTest extends PhaseBFixture
{
    protected function setUp(): void
    {
        \Config\Services::resetSingle('settings');
        $config = new \Config\Database();
        $errors = \Scripts\TestDatabasePreflight::validateConfigBeforeConnection($config->tests, $config->default);
        if ($errors || $config->defaultGroup !== 'tests') {
            throw new \RuntimeException('Isolated tests configuration required.');
        }
        $db = \Config\Database::connect('tests');
        $liveErrors = \Scripts\TestDatabasePreflight::validateLiveConnection($db);
        if ($liveErrors) {
            throw new \RuntimeException(implode('; ', $liveErrors) . ' [configured tests port ' . (string) $config->tests['port'] . ']');
        }
        $ownership = \Scripts\TestDatabasePreflight::verifyOwnershipForReset($db);
        if (!$db->tableExists('_test_db_marker') || !$ownership['allowed']) {
            throw new \RuntimeException($ownership['reason']);
        }

        parent::setUp();
        $this->setting('currency', 'ILS');
        $this->login();
    }

    protected function tearDown(): void
    {
        if (isset($this->clinic)) {
            foreach (['examinations', 'treatments'] as $table) {
                if ($this->db->tableExists($table) && $this->db->fieldExists('clinic_id', $table)) {
                    $this->db->table($table)->whereIn('clinic_id', [$this->clinic, $this->otherClinic])->delete();
                }
            }
        }
        parent::tearDown();
    }

    private function grantPermission(string $module, string $action): void
    {
        $permission = $this->db->table('permissions')->where(['module' => $module, 'action' => $action])->get()->getRowArray();
        if (!$permission) {
            $this->db->table('permissions')->insert([
                'name' => ucfirst($action) . ' ' . $module,
                'module' => $module,
                'action' => $action,
            ]);
            $permission = ['id' => (int) $this->db->insertID()];
        }
        $exists = $this->db->table('role_permissions')->where([
            'role_id' => $this->role,
            'permission_id' => $permission['id'],
        ])->get()->getRowArray();
        if (!$exists) {
            $this->db->table('role_permissions')->insert([
                'role_id' => $this->role,
                'permission_id' => $permission['id'],
                'granted' => 1,
            ]);
        }
    }

    private function revokePermission(string $module, string $action): void
    {
        $permission = $this->db->table('permissions')->where(['module' => $module, 'action' => $action])->get()->getRowArray();
        if ($permission) {
            $this->db->table('role_permissions')->where([
                'role_id' => $this->role,
                'permission_id' => $permission['id'],
            ])->delete();
        }
    }

    private function postWithCsrf(string $url, array $data = [])
    {
        $security = service('security');
        $config = config('Security');
        $hash = $security->getHash();
        return $this->withSession(session()->get())
            ->withHeaders([
                'Cookie' => $config->cookieName . '=' . $hash,
                'X-CSRF-TOKEN' => $hash,
            ])
            ->post($url, $data + [$config->tokenName => $hash]);
    }

    public function testPatientCreatePageSearchTriggerRequiresViewPermission(): void
    {
        // 1. Granted both patients.create AND patients.view
        $this->grantPermission('patients', 'create');
        $this->grantPermission('patients', 'view');
        $this->login();

        $response = $this->withSession(session()->get())->get('/patient/create');
        $response->assertOK();
        $html = $response->getBody();

        self::assertStringContainsString('<dialog id="search-dialog"', $html, 'Layout command dialog must be rendered when user has patients.view');
        self::assertStringContainsString('data-dialog="search-dialog"', $html, 'Search trigger button must be rendered when user has patients.view');
        self::assertStringContainsString('name="first_name"', $html);
        self::assertStringContainsString('name="last_name"', $html);

        // 2. Granted patients.create ONLY (patients.view revoked)
        $this->revokePermission('patients', 'view');
        \Config\Services::resetSingle('renderer');
        $this->login();

        $responseNoView = $this->withSession(session()->get())->get('/patient/create');
        $responseNoView->assertOK();
        $htmlNoView = $responseNoView->getBody();

        self::assertStringNotContainsString('<dialog id="search-dialog"', $htmlNoView, 'Layout command dialog must NOT be rendered when user lacks patients.view');
        self::assertStringNotContainsString('data-dialog="search-dialog"', $htmlNoView, 'Search trigger button must NOT be rendered when user lacks patients.view');
        self::assertStringContainsString('name="first_name"', $htmlNoView, 'Form inputs remain accessible for create-only');
        self::assertStringContainsString('name="last_name"', $htmlNoView, 'Form inputs remain accessible for create-only');
    }

    public function testPatientSearchRealModelClinicScopingAndConcealment(): void
    {
        $this->grantPermission('patients', 'view');
        $this->login();

        $token = bin2hex(random_bytes(5));
        $uniqueQuery = 'ScopeCheck_' . $token;

        // Patient in Clinic A (authorized)
        $this->db->table('patients')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => 'P_A_' . $token,
            'first_name' => $uniqueQuery,
            'last_name' => 'ClinicA_Patient',
            'phone' => '0599000111',
            'gender' => 'female',
            'status' => 'active',
            'created_by' => $this->actor,
        ]);
        $patientAId = (int) $this->db->insertID();

        // Patient in Clinic B (other clinic, should be concealed)
        $this->db->table('patients')->insert([
            'clinic_id' => $this->otherClinic,
            'patient_id' => 'P_B_' . $token,
            'first_name' => $uniqueQuery,
            'last_name' => 'ClinicB_Patient',
            'phone' => '0599000222',
            'gender' => 'male',
            'status' => 'active',
            'created_by' => $this->actor,
        ]);
        $patientBId = (int) $this->db->insertID();

        $response = $this->withSession(session()->get())->get('/api/search/patients?q=' . urlencode($uniqueQuery));
        $response->assertOK();

        $data = json_decode($response->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('results', $data);
        self::assertSame(1, $data['total'], 'Search must strictly scope results to active clinic count');

        $resultIds = array_map('intval', array_column($data['results'], 'id'));
        self::assertContains($patientAId, $resultIds, 'Authorized Clinic A patient must be returned');
        self::assertNotContains($patientBId, $resultIds, 'Foreign Clinic B patient must be concealed');
    }

    public function testPatientSearchDeniedViewAndMissingTenantReturn403(): void
    {
        // 1. Denied view permission returns 403 JSON
        $this->revokePermission('patients', 'view');
        \Config\Services::resetSingle('renderer');
        $this->login();

        $responseDenied = $this->withSession(session()->get())->get('/api/search/patients?q=synthetic');
        $responseDenied->assertStatus(403);
        $deniedData = json_decode($responseDenied->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('forbidden', $deniedData['error']['code'] ?? null);

        // 2. Re-grant view permission
        $this->grantPermission('patients', 'view');
        \Config\Services::resetSingle('renderer');

        // Missing tenant context in session (active_clinic_id = 0)
        $missingTenantSession = [
            'user_id' => $this->actor,
            'identity' => $this->email,
            'active_clinic_id' => 0,
            'global_mode' => false,
            'ui_locale' => 'en',
            'is_impersonating' => false,
        ];
        $responseMissing = $this->withSession($missingTenantSession)->get('/api/search/patients?q=synthetic');
        self::assertContains($responseMissing->response()->getStatusCode(), [422, 403]);
        $missingData = json_decode($responseMissing->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        self::assertContains($missingData['error']['code'] ?? null, ['tenant_context_missing', 'tenant_context_forbidden']);

        // Invalid tenant context in session (unauthorized clinic ID)
        $invalidTenantSession = [
            'user_id' => $this->actor,
            'identity' => $this->email,
            'active_clinic_id' => 999999,
            'global_mode' => false,
            'ui_locale' => 'en',
            'is_impersonating' => false,
        ];
        $responseInvalid = $this->withSession($invalidTenantSession)->get('/api/search/patients?q=synthetic');
        $responseInvalid->assertStatus(403);
        $invalidData = json_decode($responseInvalid->getJSON(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('tenant_context_forbidden', $invalidData['error']['code'] ?? null);
    }

    public function testTreatmentCreateAndStoreDeniedWithoutPermission(): void
    {
        $this->revokePermission('treatments', 'create');
        $this->revokePermission('treatments', 'view');
        $this->login();

        // 1. GET /treatment/create is denied by route filter
        $responseGet = $this->withSession(session()->get())->get('/treatment/create');
        $responseGet->assertRedirect();
        $responseGet->assertSessionHas('error', 'You do not have permission to access this resource.');

        // 2. POST /treatment/store with valid CSRF is denied by route filter
        $initialTreatmentsCount = $this->db->table('treatments')->where('clinic_id', $this->clinic)->countAllResults();
        $responsePost = $this->postWithCsrf('/treatment/store', [
            'patient_id' => $this->patient,
            'treatment_type' => 'cleaning',
            'description' => 'Denied unauthorized treatment attempt',
            'start_date' => date('Y-m-d'),
            'estimated_duration' => 30,
        ]);
        $responsePost->assertRedirect();
        $responsePost->assertSessionHas('error', 'You do not have permission to access this resource.');

        $afterCount = $this->db->table('treatments')->where('clinic_id', $this->clinic)->countAllResults();
        self::assertSame($initialTreatmentsCount, $afterCount, 'Zero treatment records must be inserted on denied permission');
    }

    public function testTreatmentCreateResolvesOwnedExaminationAndConcealsForeign(): void
    {
        $this->grantPermission('treatments', 'create');
        $this->grantPermission('treatments', 'view');
        $this->grantPermission('patients', 'view');
        $this->login();

        // Owned examination in Clinic A for synthetic patient
        $this->db->table('examinations')->insert([
            'clinic_id' => $this->clinic,
            'patient_id' => $this->patient,
            'examination_id' => 'EXAM_OWNED_' . bin2hex(random_bytes(4)),
            'examination_date' => date('Y-m-d H:i:s'),
            'examination_type' => 'routine',
            'chief_complaint' => 'Owned clinic examination',
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $ownedExamId = (int) $this->db->insertID();

        // Foreign examination in Clinic B for other patient
        $this->db->table('patients')->insert([
            'clinic_id' => $this->otherClinic,
            'patient_id' => 'P_OTHER_' . bin2hex(random_bytes(4)),
            'first_name' => 'ForeignClinic',
            'last_name' => 'OtherPatient',
            'gender' => 'other',
            'status' => 'active',
            'created_by' => $this->actor,
        ]);
        $otherPatientId = (int) $this->db->insertID();

        $this->db->table('examinations')->insert([
            'clinic_id' => $this->otherClinic,
            'patient_id' => $otherPatientId,
            'examination_id' => 'EXAM_FOREIGN_' . bin2hex(random_bytes(4)),
            'examination_date' => date('Y-m-d H:i:s'),
            'examination_type' => 'routine',
            'chief_complaint' => 'Foreign clinic examination',
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $foreignExamId = (int) $this->db->insertID();

        // Foreign examination must be concealed: redirect to /treatment with error
        $responseForeign = $this->withSession(session()->get())->get('/treatment/create?examination_id=' . $foreignExamId);
        $responseForeign->assertRedirectTo(base_url('/treatment'));
        $responseForeign->assertSessionHas('error', 'Examination not found.');

        // Owned examination must resolve canonical patient and preserve examination reference
        $responseOwned = $this->withSession(session()->get())->get('/treatment/create?examination_id=' . $ownedExamId);
        $responseOwned->assertOK();
        $ownedHtml = $responseOwned->getBody();

        // Canonical patient details present in selected patient dropdown
        self::assertStringContainsString('name="patient_id"', $ownedHtml);
        self::assertStringContainsString('Synthetic Patient', $ownedHtml);
        // Examination reference preserved in form input
        self::assertStringContainsString('name="examination_id"', $ownedHtml);
        self::assertStringContainsString('value="' . $ownedExamId . '"', $ownedHtml);
    }
}
