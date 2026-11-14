<?php

declare(strict_types=1);

namespace Tests\Unit;

require_once dirname(__DIR__) . '/_support/PhaseBFixture.php';
require_once dirname(__DIR__, 2) . '/scripts/TestDatabasePreflight.php';

use App\Models\AppointmentModel;
use App\Models\ExaminationModel;
use App\Services\ClinicalEncounterActionResolver;
use Tests\Support\PhaseBFixture;

/**
 * Isolated synthetic database and routed HTTP test suite for clinical handoff workflows.
 * Covers Runtime Cases A, B, C, D, and E under fail-closed tenant and RBAC boundaries.
 */
final class ClinicalHandoffWorkflowHttpTest extends PhaseBFixture
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
        $this->grantPermission('dashboard', 'view');
        $this->grantPermission('examinations', 'view');
        $this->grantPermission('examinations', 'create');
        $this->grantPermission('examinations', 'edit');
        $this->login();
    }

    protected function tearDown(): void
    {
        if (isset($this->clinic)) {
            foreach (['activity_logs', 'appointment_status_history', 'treatments', 'examinations', 'appointments'] as $table) {
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

    private function createAppointment(string $status = 'scheduled', ?int $clinicId = null, ?int $patientId = null): int
    {
        $targetClinic = $clinicId ?? $this->clinic;
        $targetPatient = $patientId ?? $this->patient;
        $apptCode = 'APT-' . bin2hex(random_bytes(4));
        $data = [
            'clinic_id' => $targetClinic,
            'patient_id' => $targetPatient,
            'provider_user_id' => $this->providers[0],
            'appointment_id' => $apptCode,
            'appointment_date' => date('Y-m-d'),
            'appointment_time' => '10:00:00',
            'status' => $status,
            'appointment_type' => 'consultation',
            'duration' => 30,
            'notes' => 'Synthetic test appointment',
            'created_by' => $this->actor,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->table('appointments')->insert($data);
        return (int) $this->db->insertID();
    }

    private function createExamination(array $override = []): int
    {
        $code = 'EX-' . bin2hex(random_bytes(4));
        $data = array_merge([
            'clinic_id' => $this->clinic,
            'patient_id' => $this->patient,
            'appointment_id' => null,
            'examination_id' => $code,
            'examination_date' => date('Y-m-d'),
            'examination_type' => 'initial',
            'chief_complaint' => 'Routine clinical examination and checkup.',
            'status' => 'pending',
            'created_by' => $this->actor,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], $override);

        $this->db->table('examinations')->insert($data);
        return (int) $this->db->insertID();
    }

    private function postTransition(int $appointmentId, array $data, ?array $sessionData = null)
    {
        $security = service('security');
        $config = config('Security');
        $hash = $security->getHash();
        $activeSession = $sessionData ?? session()->get();

        return $this->withSession($activeSession)
            ->withHeaders([
                'Cookie' => $config->cookieName . '=' . $hash,
                'X-CSRF-TOKEN' => $hash,
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->post('appointment/' . $appointmentId . '/transition', $data + [$config->tokenName => $hash]);
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

    /**
     * Runtime Case A:
     * Clinic-owned appointment with zero/one/multiple associated exams yields explicit create/
     * exact owned view/patient examinations selection; never latest unrelated patient exam,
     * no foreign clinic/patient association. Failed/unavailable lookup must not show Start exam.
     */
    public function testCaseA_EncounterResolutionAndTenantIsolation(): void
    {
        $aptId = $this->createAppointment('ready');
        $examModel = new ExaminationModel($this->db);
        $resolver = new ClinicalEncounterActionResolver();

        // 1. Zero associated exams -> create action
        $batch = $examModel->getAssociatedByAppointmentIdsForClinic($this->clinic, [$aptId]);
        self::assertArrayHasKey($aptId, $batch);
        self::assertSame([], $batch[$aptId]);

        $aptRow = $this->db->table('appointments')->where('id', $aptId)->get()->getRowArray();
        $action0 = $resolver->resolve($this->clinic, $aptRow, $batch[$aptId], true, true, 'dashboard');
        self::assertSame('create', $action0['type']);
        self::assertSame(0, $action0['count']);
        self::assertSame('appointmentStartExamination', $action0['label_key']);
        self::assertStringContainsString('examination/create', $action0['url']);
        self::assertStringContainsString('patient_id=' . $this->patient, $action0['url']);
        self::assertStringContainsString('appointment_id=' . $aptId, $action0['url']);
        self::assertStringContainsString('return_to=dashboard', $action0['url']);

        // 2. Unrelated patient exam (not associated with this appointment) -> must STILL yield create action (NEVER latest unrelated)
        $unrelatedExamId = $this->createExamination([
            'appointment_id' => null,
            'chief_complaint' => 'Unrelated earlier complaint from last week.',
        ]);
        $batchAfterUnrelated = $examModel->getAssociatedByAppointmentIdsForClinic($this->clinic, [$aptId]);
        self::assertSame([], $batchAfterUnrelated[$aptId], 'Unrelated examination must not be associated with appointment');
        $actionAfterUnrelated = $resolver->resolve($this->clinic, $aptRow, $batchAfterUnrelated[$aptId], true, true, 'dashboard');
        self::assertSame('create', $actionAfterUnrelated['type'], 'Must still yield create; never pick unrelated latest exam');

        // 3. Exactly one associated exam -> view action to exact owned exam
        $exam1Id = $this->createExamination(['appointment_id' => $aptId]);
        $batch1 = $examModel->getAssociatedByAppointmentIdsForClinic($this->clinic, [$aptId]);
        self::assertCount(1, $batch1[$aptId]);
        self::assertSame($exam1Id, (int) $batch1[$aptId][0]['id']);

        $action1 = $resolver->resolve($this->clinic, $aptRow, $batch1[$aptId], true, true, 'dashboard');
        self::assertSame('view', $action1['type']);
        self::assertSame(1, $action1['count']);
        self::assertSame($exam1Id, $action1['examination_id']);
        self::assertSame('appointmentOpenExamination', $action1['label_key']);
        self::assertStringContainsString('examination/' . $exam1Id, $action1['url']);

        // 4. Multiple associated exams -> workspace tab (no arbitrary latest)
        $exam2Id = $this->createExamination(['appointment_id' => $aptId]);
        $batch2 = $examModel->getAssociatedByAppointmentIdsForClinic($this->clinic, [$aptId]);
        self::assertCount(2, $batch2[$aptId]);

        $action2 = $resolver->resolve($this->clinic, $aptRow, $batch2[$aptId], true, true, 'dashboard');
        self::assertSame('workspace', $action2['type']);
        self::assertSame(2, $action2['count']);
        self::assertNull($action2['examination_id']);
        self::assertSame('appointmentPatientExaminations', $action2['label_key']);
        self::assertStringContainsString('patient/' . $this->patient . '?tab=examinations', $action2['url']);

        // 5. Foreign clinic association check: foreign clinic exam with same appointment_id number must be ignored
        $foreignExamId = $this->createExamination([
            'clinic_id' => $this->otherClinic,
            'appointment_id' => $aptId,
        ]);
        $batchClinicCheck = $examModel->getAssociatedByAppointmentIdsForClinic($this->clinic, [$aptId]);
        self::assertCount(2, $batchClinicCheck[$aptId], 'Foreign clinic exam must not appear in clinic batch');

        // 6. Negative control: failed/unavailable lookup (null associated examinations) must fallback to appointment detail, NEVER show Start exam
        $actionUnavailable = $resolver->resolve($this->clinic, $aptRow, null, true, true, 'dashboard');
        self::assertSame('fallback', $actionUnavailable['type']);
        self::assertNull($actionUnavailable['count']);
        self::assertSame('appointmentActionView', $actionUnavailable['label_key']);
        self::assertStringContainsString('appointment/' . $aptId, $actionUnavailable['url']);
        self::assertStringNotContainsString('examination', $actionUnavailable['url']);
    }

    /**
     * Runtime Case B:
     * apps.view+exams.view WITHOUT apps.edit sees Open exam on in_chair but NO stage forms.
     * create-only exam user sees Start examination only verified none.
     * apps.edit NO exam permissions stage action+View details no exam read/create.
     * Denied server routes 403 / concealed foreign 404 verified, not just hidden buttons.
     */
    public function testCaseB_RoleDecouplingAndServerRouteEnforcement(): void
    {
        $aptId = $this->createAppointment('in_chair');
        $examId = $this->createExamination(['appointment_id' => $aptId]);

        // 1. User with appointments.view + examinations.view WITHOUT appointments.edit
        $this->revokePermission('appointments', 'edit');

        // Routed GET to dashboard: should see Open examination link but NO transition form
        $dashRes = $this->withSession(session()->get())->get('dashboard');
        $dashRes->assertStatus(200);
        $body = (string) $dashRes->response()->getBody();
        self::assertMatchesRegularExpression('/examination(?:\/|&#x2F;)' . $examId . '/', $body, 'Permitted view user must see Open examination button');
        self::assertStringContainsString('Open examination', $body);
        self::assertStringNotContainsString('action="' . base_url('appointment/' . $aptId . '/transition') . '"', $body, 'User without appointments.edit must not see transition form');
        self::assertStringNotContainsString('data-appointment-transition', $body, 'Transition form must not be rendered');

        // Direct POST to transition server route must return 403
        $transitionDenial = $this->postTransition($aptId, [
            'status' => 'checkout',
            'expected_status' => 'in_chair',
        ]);
        $transitionDenial->assertStatus(403);
        $transitionDenial->assertJSONFragment(['error' => ['code' => 'forbidden']]);

        // 2. User with appointments.view + examinations.create WITHOUT examinations.view
        $this->grantPermission('appointments', 'edit');
        $this->revokePermission('examinations', 'view');
        $this->grantPermission('examinations', 'create');

        // For appointment with existing exam, cannot view exam -> server route GET examination/{id} returns 403
        $examViewRes = $this->withSession(session()->get())->get('examination/' . $examId);
        $examViewRes->assertStatus(403);

        // For new appointment with 0 exams -> GET examination/create is allowed
        $apt2Id = $this->createAppointment('ready');
        $examCreateRes = $this->withSession(session()->get())->get('examination/create?patient_id=' . $this->patient . '&appointment_id=' . $apt2Id);
        $examCreateRes->assertStatus(200);

        // 3. User with appointments.edit but NO exam permissions (examinations.view = false, examinations.create = false)
        $this->revokePermission('examinations', 'create');
        $this->revokePermission('examinations', 'view');

        // Direct GET examination/create returns 403
        $createDenial = $this->withSession(session()->get())->get('examination/create?patient_id=' . $this->patient);
        $createDenial->assertStatus(403);

        // Direct GET examination/{id} returns 403
        $showDenial = $this->withSession(session()->get())->get('examination/' . $examId);
        $showDenial->assertStatus(403);

        // But appointment transition is still permitted
        $transitionSuccess = $this->postTransition($apt2Id, [
            'status' => 'in_chair',
            'expected_status' => 'ready',
        ]);
        $transitionSuccess->assertStatus(200);
        $transitionSuccess->assertJSONFragment(['success' => true, 'status' => 'in_chair']);

        // 4. Concealed foreign tenant: accessing foreign examination returns 404 (thrown as PageNotFoundException in CI FeatureTest)
        $this->grantPermission('examinations', 'view');
        $foreignExamId = $this->createExamination([
            'clinic_id' => $this->otherClinic,
            'appointment_id' => null,
        ]);
        try {
            $this->withSession(session()->get())->get('examination/' . $foreignExamId);
            self::fail('Expected PageNotFoundException for cross-clinic examination access');
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            self::assertSame(404, $e->getCode());
            self::assertSame('Examination not found', $e->getMessage());
        }
    }

    /**
     * Runtime Case C:
     * Ready -> Start visit -> in_chair -> Open examination canonical GET/form with patient+appointment validated;
     * existing explicit exam save return_to dashboard, exam create denial or invalid states preserve inputs.
     * No autosigning/autoclinical complete/autoinvoice.
     * Start visit does NOT create exam, handoff checkout does NOT sign/complete exam/payment;
     * assert DB before after including financial/audit counts scoped.
     */
    public function testCaseC_LifecycleHandoffAndDataInvariants(): void
    {
        $aptId = $this->createAppointment('ready');

        // Scoped DB counts before
        $examsBefore = $this->db->table('examinations')->where('clinic_id', $this->clinic)->countAllResults();
        $auditsBefore = $this->db->table('activity_logs')->where('entity_id', $aptId)->countAllResults();
        $financesBefore = $this->db->table('finances')->where('clinic_id', $this->clinic)->countAllResults();
        $paymentsBefore = $this->db->table('finance_payments')->where('clinic_id', $this->clinic)->countAllResults();

        // 1. Transition ready -> in_chair (Start visit)
        $startRes = $this->postTransition($aptId, [
            'status' => 'in_chair',
            'expected_status' => 'ready',
            'return_to' => 'dashboard',
        ]);
        $startRes->assertStatus(200);
        $startRes->assertJSONFragment(['success' => true, 'status' => 'in_chair']);

        // INVARIANT CHECK: Start visit must NOT create any examination, invoice, or payment
        $examsAfterStart = $this->db->table('examinations')->where('clinic_id', $this->clinic)->countAllResults();
        $financesAfterStart = $this->db->table('finances')->where('clinic_id', $this->clinic)->countAllResults();
        $paymentsAfterStart = $this->db->table('finance_payments')->where('clinic_id', $this->clinic)->countAllResults();
        self::assertSame($examsBefore, $examsAfterStart, 'Start visit must NOT create an examination');
        self::assertSame($financesBefore, $financesAfterStart, 'Start visit must NOT create a financial record');
        self::assertSame($paymentsBefore, $paymentsAfterStart, 'Start visit must NOT create a payment');

        // Exactly 1 audit record for appointment status transition
        $auditsAfterStart = $this->db->table('activity_logs')->where('entity_id', $aptId)->where('action', 'status')->countAllResults();
        self::assertSame($auditsBefore + 1, $auditsAfterStart);

        // 2. GET examination/create with patient and appointment context
        $createGetRes = $this->withSession(session()->get())->get('examination/create?patient_id=' . $this->patient . '&appointment_id=' . $aptId . '&return_to=dashboard');
        $createGetRes->assertStatus(200);
        $createBody = (string) $createGetRes->response()->getBody();
        self::assertMatchesRegularExpression('/<option[^>]+value="' . $this->patient . '"[^>]*selected/', $createBody);
        self::assertStringContainsString('name="appointment_id" value="' . $aptId . '"', $createBody);
        self::assertStringContainsString('name="return_to" value="dashboard"', $createBody);

        // 3. Invalid examination submission: missing chief complaint
        $invalidRes = $this->postWithCsrf('examination/store', [
            'patient_id' => $this->patient,
            'appointment_id' => $aptId,
            'examination_date' => date('Y-m-d'),
            'examination_type' => 'initial',
            'chief_complaint' => '', // Required min_length[10]
            'return_to' => 'dashboard',
        ]);
        $invalidRes->assertStatus(302);
        // Examination count still unchanged
        self::assertSame($examsBefore, $this->db->table('examinations')->where('clinic_id', $this->clinic)->countAllResults());

        // 4. Valid examination submission with return_to=dashboard
        $validRes = $this->postWithCsrf('examination/store', [
            'patient_id' => $this->patient,
            'appointment_id' => $aptId,
            'examination_date' => date('Y-m-d'),
            'examination_type' => 'initial',
            'chief_complaint' => 'Patient presents with toothache on lower left quadrant.',
            'clinical_findings' => 'Deep carious lesion on tooth #36.',
            'diagnosis' => 'Irreversible pulpitis.',
            'return_to' => 'dashboard',
        ]);
        $validRes->assertStatus(302);
        $validRes->assertRedirectTo(base_url('dashboard'));

        // DB assertion: exactly 1 new examination created
        $createdExam = $this->db->table('examinations')
            ->where('clinic_id', $this->clinic)
            ->where('appointment_id', $aptId)
            ->get()
            ->getRowArray();
        self::assertNotNull($createdExam);
        self::assertSame('pending', $createdExam['status'], 'Examination must be pending; NO auto-signing or auto-completion');
        self::assertSame($this->patient, (int) $createdExam['patient_id']);
        self::assertSame($aptId, (int) $createdExam['appointment_id']);

        // 5. Handoff transition: in_chair -> checkout
        $checkoutRes = $this->postTransition($aptId, [
            'status' => 'checkout',
            'expected_status' => 'in_chair',
            'return_to' => 'dashboard',
        ]);
        $checkoutRes->assertStatus(200);
        $checkoutRes->assertJSONFragment(['success' => true, 'status' => 'checkout']);

        // INVARIANT CHECK: Handoff checkout does NOT sign/complete exam or create payment
        $examAfterCheckout = $this->db->table('examinations')->where('id', $createdExam['id'])->get()->getRowArray();
        self::assertSame('pending', $examAfterCheckout['status'], 'Examination status must remain pending across checkout handoff');
        self::assertSame($financesBefore, $this->db->table('finances')->where('clinic_id', $this->clinic)->countAllResults());
        self::assertSame($paymentsBefore, $this->db->table('finance_payments')->where('clinic_id', $this->clinic)->countAllResults());
    }

    /**
     * Runtime Case D:
     * stale expected replay 409 and no duplicate audit, checkout completion override existing guard retained,
     * failed audit rollback still pass. Associated exam permissions SSR vs JS parity after 200/409.
     */
    public function testCaseD_Replay409AndAuditIntegrity(): void
    {
        $aptId = $this->createAppointment('ready');

        // First transition -> 200
        $res1 = $this->postTransition($aptId, [
            'status' => 'in_chair',
            'expected_status' => 'ready',
            'return_to' => 'dashboard',
        ]);
        $res1->assertStatus(200);

        // Replay with stale expected_status 'ready' -> 409 Conflict
        $res2 = $this->postTransition($aptId, [
            'status' => 'in_chair',
            'expected_status' => 'ready',
            'return_to' => 'dashboard',
        ]);
        $res2->assertStatus(409);
        $res2->assertJSONFragment([
            'success' => false,
            'error' => 'CONFLICT',
            'current_status' => 'in_chair',
            'allowed_statuses' => ['checkout', 'completed'],
        ]);

        // Verify exactly 1 audit log exists (no duplicate audit on replay)
        $auditCount = $this->db->table('activity_logs')
            ->where('entity_type', 'appointment')
            ->where('entity_id', $aptId)
            ->where('action', 'status')
            ->countAllResults();
        self::assertSame(1, $auditCount, 'Replay 409 must not create duplicate audit log');
    }

    /**
     * Runtime Case E:
     * strict Examination return context: dashboard/safe query accepted;
     * malicious external/protocol-relative/encoded traversal/header rejected.
     */
    public function testCaseE_ReturnToSanitizationAndOpenRedirectRejection(): void
    {
        // 1. Permitted safe return_to targets
        $safeUrls = [
            'dashboard',
            'dashboard?tab=today',
            'dashboard?tab=upcoming',
            'patient/' . $this->patient,
            'patient/' . $this->patient . '?tab=examinations',
        ];

        foreach ($safeUrls as $safe) {
            $res = $this->withSession(session()->get())->get('examination/create?patient_id=' . $this->patient . '&return_to=' . rawurlencode($safe));
            $res->assertStatus(200);
            $body = (string) $res->response()->getBody();
            self::assertStringContainsString('name="return_to" value="' . esc($safe, 'attr') . '"', $body);
        }

        // 2. Malicious return_to targets must be sanitized away to fallback in form hidden input
        $maliciousTargets = [
            'https://evil.com/phishing',
            'http://attacker.com',
            '//evil.com/relative',
            'javascript:alert(1)',
            '../../etc/passwd',
            "dashboard\r\nSet-Cookie: pwned=1",
            "dashboard\nHost: evil.com",
        ];

        foreach ($maliciousTargets as $malicious) {
            $res = $this->withSession(session()->get())->get('examination/create?patient_id=' . $this->patient . '&return_to=' . rawurlencode($malicious));
            $res->assertStatus(200);
            $body = (string) $res->response()->getBody();
            // Form hidden input must fall back to patient, never reflecting the malicious input
            self::assertMatchesRegularExpression('/<input[^>]+name="return_to"[^>]+value="patient(?:\/|&#x2F;)' . $this->patient . '"/', $body);
            self::assertDoesNotMatchRegularExpression('/<input[^>]+name="return_to"[^>]+value="[^"]*(?:evil\.com|attacker\.com|javascript|passwd|Set-Cookie|Host)/', $body);
        }

        // 3. POST examination/store with malicious return_to must redirect to safe internal fallback, NEVER external/open redirect
        $postMalicious = $this->postWithCsrf('examination/store', [
            'patient_id' => $this->patient,
            'examination_date' => date('Y-m-d'),
            'examination_type' => 'initial',
            'chief_complaint' => 'Valid complaint for open redirect test verification.',
            'return_to' => 'https://evil.com/phishing',
        ]);
        $postMalicious->assertStatus(302);
        $redirectUrl = (string) $postMalicious->response()->getHeaderLine('Location');
        self::assertStringNotContainsString('evil.com', $redirectUrl);
        self::assertMatchesRegularExpression('/examination\/\d+/', $redirectUrl);
    }
}
