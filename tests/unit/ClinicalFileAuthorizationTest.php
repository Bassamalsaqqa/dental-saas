<?php

namespace Tests\Unit;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;

/**
 * ClinicalFileAuthorizationTest
 *
 * Comprehensive routed integration test certifying clinical-file authorization:
 * 1. Fail-closed database isolation safeguard:
 *    Validates Config\Database tests config (127.0.0.1, port 3307, democa_dental_saas_test, MySQLi,
 *    defaultGroup 'tests', non-business DB) BEFORE Database::connect, and verifies SQL SELECT DATABASE().
 * 2. EN/AR locale compatibility for both download and inline endpoints.
 * 3. Same-clinic authorized access vs Cross-clinic denial (404 PageNotFoundException).
 * 4. Module permission enforcement (no-view permission denied 403 Forbidden).
 * 5. Inactive role denial (redirect or 403).
 * 6. Global control-plane mode denial (404 Airlock enforcement).
 * 7. Canonical export permission enforcement:
 *    - Finance exports (entity_id=0, finance_export) require finance:export (view-only denied 403).
 *    - Report exports (entity_id=0, report_excel) require reports:export (view-only denied 403).
 *    - Report with positive entity_id fails 404.
 * 8. Clinic Logo referencing:
 *    - Logo referenced in active clinic settings (clinic_logo_path) allowed for ordinary tenant users (200).
 *    - Orphan logo attachment not referenced in settings fails closed (404).
 * 9. Content headers & detected MIME verification:
 *    - Genuine PDF/PNG binary bytes served inline with CSP sandbox and nosniff.
 *    - Active HTML/SVG uploads and forged MIME types rejected inline (403).
 *    - Downloads force application/octet-stream for active/untrusted types.
 * 10. All valid entity families: patient, examination, appointment, treatment, prescription,
 *     contract, finance record, and inventory_usage.
 * 11. Cross-clinic parent/patient linkage fails closed (404).
 * 12. Filesystem confinement enforcement with verified physical file outside tenant path.
 * 13. Unknown entity type rejection (404).
 * 14. Generated HTML contract compatibility (download as application/octet-stream, inline denied 403).
 * 15. Soft-delete regression covering all seven verified tables (patients, examinations, appointments,
 *     treatments, prescriptions, contracts, finances) and soft-deleted parent patient linkage (all fail 404).
 * 16. Clinic upload directory alias rejection (symlink/junction pointing to foreign clinic directory fails 404).
 */
final class ClinicalFileAuthorizationTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $db;
    public const APPROVED_TEST_DATABASE = 'democa_dental_saas_test';

    // Minimal valid synthetic 1x1 PNG bytes (finfo detects image/png)
    public const SYNTHETIC_PNG_BYTES = "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x06\x00\x00\x00\x1f\x15c4\x00\x00\x00\nIDATx\x9cc\x00\x01\x00\x00\x05\x00\x01\r\n-\xb4\x00\x00\x00\x00IEND\xaeB`\x82";

    // Minimal valid synthetic PDF 1.4 bytes (finfo detects application/pdf)
    public const SYNTHETIC_PDF_BYTES = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 3 3]>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000009 00000 n\n0000000052 00000 n\n0000000101 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n147\n%%EOF\n";

    // Synthetic HTML active upload bytes
    public const SYNTHETIC_HTML_BYTES = "<!DOCTYPE html><html><head><title>Test</title></head><body><script>alert('active')</script></body></html>";

    private array $createdUsers = [];
    private array $createdClinics = [];
    private array $createdPlans = [];
    private array $createdSubscriptions = [];
    private array $createdMemberships = [];
    private array $createdRoles = [];
    private array $createdPermissions = [];
    private array $createdRolePermissions = [];
    private array $createdAttachments = [];
    private array $createdPhysicalFiles = [];
    private array $createdPatients = [];
    private array $createdExaminations = [];
    private array $createdAppointments = [];
    private array $createdTreatments = [];
    private array $createdPrescriptions = [];
    private array $createdContracts = [];
    private array $createdFinances = [];
    private array $createdInventoryUsage = [];
    private array $createdSettings = [];
    private array $createdDirectoryAliases = [];
    private array $createdWindowsJunctions = [];

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Validate Config\Database 'tests' group configuration BEFORE Database::connect:
        $dbConfig = config('Database');
        $testsConfig = $dbConfig->tests ?? [];
        $testsHost = (string) ($testsConfig['hostname'] ?? '');
        $testsPort = (int) ($testsConfig['port'] ?? 0);
        $testsDb = (string) ($testsConfig['database'] ?? '');
        $testsDriver = (string) ($testsConfig['DBDriver'] ?? '');
        $businessDb = (string) ($dbConfig->default['database'] ?? 'democa_dental_saas');
        $defaultGroup = (string) ($dbConfig->defaultGroup ?? '');

        // Fail-closed checks before connecting:
        // Must be exactly 127.0.0.1 (no localhost allowed), port 3307, exact test database,
        // MySQLi driver, non-business DB, and defaultGroup must be 'tests'.
        if (
            $testsHost !== '127.0.0.1'
            || $testsPort !== 3307
            || $testsDb !== self::APPROVED_TEST_DATABASE
            || $testsDriver !== 'MySQLi'
            || $testsDb === $businessDb
            || $defaultGroup !== 'tests'
        ) {
            $this->db = null;
            throw new \RuntimeException(
                "FAIL-CLOSED ISOLATION SAFEGUARD: ClinicalFileAuthorizationTest configuration pre-check failed. " .
                "Requires defaultGroup='tests', tests.hostname='127.0.0.1', tests.port=3307, " .
                "tests.database='" . self::APPROVED_TEST_DATABASE . "', tests.DBDriver='MySQLi', and tests.database!=='{$businessDb}'. " .
                "Got defaultGroup='{$defaultGroup}', host='{$testsHost}', port={$testsPort}, db='{$testsDb}', driver='{$testsDriver}'. " .
                "Execution aborted before Database::connect."
            );
        }

        // 2. Connect to tests group and verify SQL SELECT DATABASE() identity
        $connection = Database::connect('tests');
        $resolvedDb = (string) $connection->getDatabase();

        $sqlDbRow = $connection->query('SELECT DATABASE() as current_db')->getRowArray();
        $sqlCurrentDb = (string) ($sqlDbRow['current_db'] ?? '');

        if ($resolvedDb !== self::APPROVED_TEST_DATABASE || $sqlCurrentDb !== self::APPROVED_TEST_DATABASE) {
            $this->db = null;
            throw new \RuntimeException(
                "FAIL-CLOSED ISOLATION SAFEGUARD: ClinicalFileAuthorizationTest connected DB mismatch. " .
                "Expected resolved and SQL DATABASE() to be '" . self::APPROVED_TEST_DATABASE . "', " .
                "got resolved='{$resolvedDb}', sql='{$sqlCurrentDb}'. Execution aborted before fixture writes."
            );
        }

        $this->db = $connection;
    }

    protected function tearDown(): void
    {
        foreach ($this->createdWindowsJunctions as [$linkPath, $targetPath]) {
            $this->operateOwnedWindowsJunction($linkPath, $targetPath, true);
        }

        // 1. Remove created directory aliases safely without recursing into target
        foreach ($this->createdDirectoryAliases as $linkPath) {
            if (is_link($linkPath)) {
                if (PHP_OS_FAMILY === 'Windows') {
                    @rmdir($linkPath);
                    if (is_link($linkPath)) {
                        @unlink($linkPath);
                    }
                } else {
                    @unlink($linkPath);
                }
            }
        }

        // 2. Remove physical test files
        foreach ($this->createdPhysicalFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        // 2. Delete database rows in reverse foreign key order
        if ($this->db) {
            $this->deleteRows('file_attachments', $this->createdAttachments);
            $this->deleteRows('settings', $this->createdSettings);
            $this->deleteRows('inventory_usage', $this->createdInventoryUsage);
            $this->deleteRows('finances', $this->createdFinances);
            $this->deleteRows('treatment_contracts', $this->createdContracts);
            $this->deleteRows('prescriptions', $this->createdPrescriptions);
            $this->deleteRows('treatments', $this->createdTreatments);
            $this->deleteRows('appointments', $this->createdAppointments);
            $this->deleteRows('examinations', $this->createdExaminations);
            $this->deleteRows('patients', $this->createdPatients);
            $this->deleteRows('clinic_subscriptions', $this->createdSubscriptions);
            $this->deleteRows('plans', $this->createdPlans);
            $this->deleteRows('clinic_users', $this->createdMemberships);
            $this->deleteRows('role_permissions', $this->createdRolePermissions);
            $this->deleteRows('roles', $this->createdRoles);
            $this->deleteRows('permissions', $this->createdPermissions);
            $this->deleteRows('users', $this->createdUsers);
            $this->deleteRows('clinics', $this->createdClinics);
        }

        parent::tearDown();
    }

    // =========================================================================
    // 1. Same-Clinic Authorized Download & Inline (English & Arabic)
    // =========================================================================

    public function testAuthorizedPatientFileDownloadAndInlineEnglish(): void
    {
        [$user, $clinicId, $roleId] = $this->createTenantUserWithPermissions(['patients' => ['view']]);
        $patientId = $this->createPatient($clinicId, $user['id']);
        $attachmentId = $this->createPhysicalAttachment($clinicId, 'patient', $patientId, 'scan.pdf', 'application/pdf', 'attachment', $user['id']);

        $session = $this->buildSession($user, $clinicId, ['ui_locale' => 'en']);

        // Test Download
        $downloadRes = $this->withSession($session)->get('/file/download/' . $attachmentId);
        $downloadRes->assertStatus(200);
        $this->assertInstanceOf(\CodeIgniter\HTTP\DownloadResponse::class, $downloadRes->response());
        $downloadRes->response()->buildHeaders(); // Feature requests defer the framework send phase.
        $downloadRes->assertHeader('Content-Type', 'application/pdf; charset=UTF-8');
        $downloadRes->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('attachment', (string) $downloadRes->response()->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString('scan.pdf', (string) $downloadRes->response()->getHeaderLine('Content-Disposition'));

        // Test Inline
        $inlineRes = $this->withSession($session)->get('/file/inline/' . $attachmentId);
        $inlineRes->assertStatus(200);
        $inlineRes->assertHeader('Content-Type', 'application/pdf');
        $inlineRes->assertHeader('X-Content-Type-Options', 'nosniff');
        $inlineRes->assertHeader('Content-Security-Policy', "default-src 'none'; base-uri 'none'; sandbox");
        $this->assertStringContainsString('inline', (string) $inlineRes->response()->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString('scan.pdf', (string) $inlineRes->response()->getHeaderLine('Content-Disposition'));
    }

    public function testAuthorizedPatientFileDownloadAndInlineArabic(): void
    {
        [$user, $clinicId, $roleId] = $this->createTenantUserWithPermissions(['patients' => ['view']]);
        $patientId = $this->createPatient($clinicId, $user['id']);
        $attachmentId = $this->createPhysicalAttachment($clinicId, 'patient', $patientId, 'xray.png', 'image/png', 'attachment', $user['id']);

        $session = $this->buildSession($user, $clinicId, ['ui_locale' => 'ar']);

        // Test Download in Arabic locale
        $downloadRes = $this->withSession($session)->get('/file/download/' . $attachmentId);
        $downloadRes->assertStatus(200);
        $this->assertInstanceOf(\CodeIgniter\HTTP\DownloadResponse::class, $downloadRes->response());
        $downloadRes->response()->buildHeaders();
        $downloadRes->assertHeader('Content-Type', 'image/png; charset=UTF-8');
        $downloadRes->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('attachment', (string) $downloadRes->response()->getHeaderLine('Content-Disposition'));

        // Test Inline in Arabic locale
        $inlineRes = $this->withSession($session)->get('/file/inline/' . $attachmentId);
        $inlineRes->assertStatus(200);
        $inlineRes->assertHeader('Content-Type', 'image/png');
        $inlineRes->assertHeader('X-Content-Type-Options', 'nosniff');
        $inlineRes->assertHeader('Content-Security-Policy', "default-src 'none'; base-uri 'none'; sandbox");
        $this->assertStringContainsString('inline', (string) $inlineRes->response()->getHeaderLine('Content-Disposition'));
    }

    // =========================================================================
    // 2. Cross-Clinic Access Denial (Tenant Isolation / Fail-Closed 404)
    // =========================================================================

    public function testCrossClinicAttachmentAccessFailsNotFound(): void
    {
        [$userA, $clinicA] = $this->createTenantUserWithPermissions(['patients' => ['view']]);
        [$userB, $clinicB] = $this->createTenantUserWithPermissions(['patients' => ['view']]);

        $patientA = $this->createPatient($clinicA, $userA['id']);
        $attachmentA = $this->createPhysicalAttachment($clinicA, 'patient', $patientA, 'secret_a.pdf', 'application/pdf', 'attachment', $userA['id']);

        // User B attempts to access Attachment A
        $sessionB = $this->buildSession($userB, $clinicB);

        $this->assertRouteFailsNotFound(function () use ($sessionB, $attachmentA) {
            return $this->withSession($sessionB)->get('/file/download/' . $attachmentA);
        });

        $this->assertRouteFailsNotFound(function () use ($sessionB, $attachmentA) {
            return $this->withSession($sessionB)->get('/file/inline/' . $attachmentA);
        });
    }

    // =========================================================================
    // 3. Module Permission Enforcement (No-View Permission Denied 403)
    // =========================================================================

    public function testNoViewPermissionFailsForbidden(): void
    {
        // User has only appointment view permission, attempting to access patient record file
        [$user, $clinicId] = $this->createTenantUserWithPermissions(['appointments' => ['view']]);
        $patientId = $this->createPatient($clinicId, $user['id']);
        $attachmentId = $this->createPhysicalAttachment($clinicId, 'patient', $patientId, 'record.pdf', 'application/pdf', 'attachment', $user['id']);

        $session = $this->buildSession($user, $clinicId);

        $downloadRes = $this->withSession($session)->get('/file/download/' . $attachmentId);
        $downloadRes->assertStatus(403);

        $inlineRes = $this->withSession($session)->get('/file/inline/' . $attachmentId);
        $inlineRes->assertStatus(403);
    }

    // =========================================================================
    // 4. Inactive Role Denial
    // =========================================================================

    public function testInactiveRoleFailsUnauthorizedOrForbidden(): void
    {
        $clinicId = $this->createClinic();
        $user = $this->createUser();
        $roleId = $this->createRole($clinicId, false, $user['id']); // Inactive role

        $permId = $this->permissionId('patients', 'view');
        $this->assignPermission($roleId, $permId);
        $this->createMembership($user['id'], $clinicId, $roleId);

        $patientId = $this->createPatient($clinicId, $user['id']);
        $attachmentId = $this->createPhysicalAttachment($clinicId, 'patient', $patientId, 'file.pdf', 'application/pdf', 'attachment', $user['id']);

        $session = $this->buildSession($user, $clinicId);

        $res = $this->withSession($session)->get('/file/download/' . $attachmentId);
        $statusCode = $res->response()->getStatusCode();
        $this->assertTrue(in_array($statusCode, [403, 302], true), "Inactive role must deny with 403 or redirect, got {$statusCode}");
    }

    // =========================================================================
    // 5. Global Control-Plane Mode Denial (Airlock Doctrine)
    // =========================================================================

    public function testGlobalControlPlaneModeFailsNotFound(): void
    {
        [$user, $clinicId] = $this->createTenantUserWithPermissions(['patients' => ['view']]);
        $patientId = $this->createPatient($clinicId, $user['id']);
        $attachmentId = $this->createPhysicalAttachment($clinicId, 'patient', $patientId, 'file.pdf', 'application/pdf', 'attachment', $user['id']);

        // Session in global control plane mode (airlock violation attempt)
        $session = $this->buildSession($user, $clinicId, [
            'global_mode' => true,
        ]);

        $this->assertRouteFailsNotFound(function () use ($session, $attachmentId) {
            return $this->withSession($session)->get('/file/download/' . $attachmentId);
        });

        $this->assertRouteFailsNotFound(function () use ($session, $attachmentId) {
            return $this->withSession($session)->get('/file/inline/' . $attachmentId);
        });
    }

    // =========================================================================
    // 6. Export Permissions Enforcement (Canonical export check, no view shortcut)
    // =========================================================================

    public function testFinanceExportRequiresExportPermissionNotView(): void
    {
        // 1. User with only finance:view
        [$userViewOnly, $clinicId] = $this->createTenantUserWithPermissions(['finance' => ['view']]);
        $exportId = $this->createPhysicalAttachment($clinicId, 'finance', 0, 'finance_export.csv', 'text/csv', 'finance_export', $userViewOnly['id'], "col1,col2\nval1,val2");

        $sessionView = $this->buildSession($userViewOnly, $clinicId);
        $resDenied = $this->withSession($sessionView)->get('/file/download/' . $exportId);
        $resDenied->assertStatus(403);

        // 2. User with finance:export
        [$userExport, $clinicId2] = $this->createTenantUserWithPermissions(['finance' => ['export']], $clinicId);
        $sessionExport = $this->buildSession($userExport, $clinicId);
        $resAllowed = $this->withSession($sessionExport)->get('/file/download/' . $exportId);
        $resAllowed->assertStatus(200);
    }

    public function testReportExportRequiresExportPermissionNotView(): void
    {
        // 1. User with only reports:view
        [$userViewOnly, $clinicId] = $this->createTenantUserWithPermissions(['reports' => ['view']]);
        $exportId = $this->createPhysicalAttachment($clinicId, 'report', 0, 'report_excel.csv', 'text/csv', 'report_excel', $userViewOnly['id'], "rep1,rep2\n10,20");

        $sessionView = $this->buildSession($userViewOnly, $clinicId);
        $resDenied = $this->withSession($sessionView)->get('/file/download/' . $exportId);
        $resDenied->assertStatus(403);

        // 2. User with reports:export
        [$userExport, $clinicId2] = $this->createTenantUserWithPermissions(['reports' => ['export']], $clinicId);
        $sessionExport = $this->buildSession($userExport, $clinicId);
        $resAllowed = $this->withSession($sessionExport)->get('/file/download/' . $exportId);
        $resAllowed->assertStatus(200);
    }

    public function testReportPositiveEntityIdFailsNotFound(): void
    {
        // Arbitrary positive entity_id on report must fail closed (404)
        [$user, $clinicId] = $this->createTenantUserWithPermissions(['reports' => ['export']]);
        $exportId = $this->createPhysicalAttachment($clinicId, 'report', 999, 'bad_report.csv', 'text/csv', 'report_excel', $user['id'], "data");

        $session = $this->buildSession($user, $clinicId);

        $this->assertRouteFailsNotFound(function () use ($session, $exportId) {
            return $this->withSession($session)->get('/file/download/' . $exportId);
        });
    }

    // =========================================================================
    // 7. Clinic Logo Reference Policy (Referenced Allowed, Orphan Denied)
    // =========================================================================

    public function testReferencedLogoAllowedAndOrphanLogoDenied(): void
    {
        // Ordinary tenant user with basic permissions (no settings edit permission)
        [$user, $clinicId] = $this->createTenantUserWithPermissions(['appointments' => ['view']]);

        // Logo 1: Referenced in active clinic settings
        $logo1Id = $this->createPhysicalAttachment($clinicId, 'logo', null, 'clinic_logo.png', 'image/png', 'attachment', $user['id'], self::SYNTHETIC_PNG_BYTES);
        $this->createLogoSetting($clinicId, $logo1Id);

        // Logo 2: Orphan logo attachment (not referenced in clinic settings)
        $logo2Id = $this->createPhysicalAttachment($clinicId, 'logo', null, 'old_logo.png', 'image/png', 'attachment', $user['id'], self::SYNTHETIC_PNG_BYTES);

        $session = $this->buildSession($user, $clinicId);

        // Referenced logo allows download and inline
        $res1Download = $this->withSession($session)->get('/file/download/' . $logo1Id);
        $res1Download->assertStatus(200);

        $res1Inline = $this->withSession($session)->get('/file/inline/' . $logo1Id);
        $res1Inline->assertStatus(200);
        $res1Inline->assertHeader('Content-Type', 'image/png');

        // Orphan logo denied (404 PageNotFoundException)
        $this->assertRouteFailsNotFound(function () use ($session, $logo2Id) {
            return $this->withSession($session)->get('/file/download/' . $logo2Id);
        });

        $this->assertRouteFailsNotFound(function () use ($session, $logo2Id) {
            return $this->withSession($session)->get('/file/inline/' . $logo2Id);
        });
    }

    // =========================================================================
    // 8. Detected MIME Inspection & Active Upload Prevention
    // =========================================================================

    public function testInlineActiveUploadAndForgedMimeDenied(): void
    {
        [$user, $clinicId] = $this->createTenantUserWithPermissions(['patients' => ['view']]);
        $patientId = $this->createPatient($clinicId, $user['id']);

        // Forged upload: Genuine HTML content declared with DB mime application/pdf
        $forgedId = $this->createPhysicalAttachment(
            $clinicId,
            'patient',
            $patientId,
            'exploit.pdf',
            'application/pdf',
            'attachment',
            $user['id'],
            self::SYNTHETIC_HTML_BYTES
        );

        $session = $this->buildSession($user, $clinicId);

        // Inline viewing must detect actual HTML and reject with 403 Forbidden
        $inlineRes = $this->withSession($session)->get('/file/inline/' . $forgedId);
        $inlineRes->assertStatus(403);

        // Download serves untrusted active type safely as application/octet-stream with nosniff
        $downloadRes = $this->withSession($session)->get('/file/download/' . $forgedId);
        $downloadRes->assertStatus(200);
        $this->assertInstanceOf(\CodeIgniter\HTTP\DownloadResponse::class, $downloadRes->response());
        $downloadRes->response()->buildHeaders();
        $downloadRes->assertHeader('Content-Type', 'application/octet-stream; charset=UTF-8');
        $downloadRes->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    // =========================================================================
    // 9. All Valid Entity Families Authorized
    // =========================================================================

    public function testAllValidEntityFamiliesAuthorized(): void
    {
        [$user, $clinicId] = $this->createTenantUserWithPermissions([
            'examinations' => ['view'],
            'appointments' => ['view'],
            'treatments' => ['view'],
            'prescriptions' => ['view'],
            'patients' => ['view'],
            'finance' => ['view'],
            'inventory' => ['view'],
        ]);

        $patientId = $this->createPatient($clinicId, $user['id']);
        $examId = $this->createExamination($clinicId, $patientId, $user['id']);
        $apptId = $this->createAppointment($clinicId, $patientId, $user['id']);
        $trtId = $this->createTreatment($clinicId, $patientId, $user['id']);
        $rxId = $this->createPrescription($clinicId, $patientId, $user['id']);
        $contractId = $this->createContract($clinicId, $patientId, $user['id']);
        $financeId = $this->createFinanceRecord($clinicId, $patientId, $user['id']);
        $invUsageId = $this->createInventoryUsage($clinicId, $user['id']);

        $entities = [
            ['examination', $examId, 'exam.pdf'],
            ['appointment', $apptId, 'appt.pdf'],
            ['treatment', $trtId, 'trt.pdf'],
            ['prescription', $rxId, 'rx.pdf'],
            ['contract', $contractId, 'contract.pdf'],
            ['finance', $financeId, 'receipt.pdf'],
            ['inventory_usage', $invUsageId, 'inv.pdf'],
        ];

        $session = $this->buildSession($user, $clinicId);

        foreach ($entities as [$type, $eId, $filename]) {
            $attachmentId = $this->createPhysicalAttachment(
                $clinicId,
                $type,
                $eId,
                $filename,
                'application/pdf',
                'attachment',
                $user['id']
            );

            $res = $this->withSession($session)->get('/file/download/' . $attachmentId);
            $res->assertStatus(200);
        }
    }

    // =========================================================================
    // 10. Inconsistent Cross-Clinic Parent Linkage Fails Closed
    // =========================================================================

    public function testCrossClinicParentLinkageFailsNotFound(): void
    {
        [$userA, $clinicA] = $this->createTenantUserWithPermissions(['examinations' => ['view']]);
        $clinicB = $this->createClinic();
        $patientB = $this->createPatient($clinicB, $userA['id']);

        // Inconsistent linkage: Examination belongs to Clinic A, but references Patient B in Clinic B
        $now = date('Y-m-d H:i:s');
        $this->db->table('examinations')->insert([
            'clinic_id' => $clinicA,
            'patient_id' => $patientB,
            'examination_id' => 'EXAM-' . bin2hex(random_bytes(3)),
            'examination_date' => date('Y-m-d'),
            'examination_type' => 'initial',
            'chief_complaint' => 'Cross-clinic patient test',
            'status' => 'completed',
            'created_by' => $userA['id'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $inconsistentExamId = (int) $this->db->insertID();
        $this->createdExaminations[] = $inconsistentExamId;

        $attachmentId = $this->createPhysicalAttachment($clinicA, 'examination', $inconsistentExamId, 'inconsistent.pdf', 'application/pdf', 'attachment', $userA['id']);

        $sessionA = $this->buildSession($userA, $clinicA);

        $this->assertRouteFailsNotFound(function () use ($sessionA, $attachmentId) {
            return $this->withSession($sessionA)->get('/file/download/' . $attachmentId);
        });
    }

    // =========================================================================
    // 11. Filesystem Confinement Enforcement (Verified Physical File Outside Tenant Path)
    // =========================================================================

    public function testFilesystemConfinementViolationFailsNotFound(): void
    {
        [$user, $clinicId] = $this->createTenantUserWithPermissions(['patients' => ['view']]);
        $patientId = $this->createPatient($clinicId, $user['id']);

        // Establish the legitimate tenant directory so this exercises the confinement
        // comparison rather than the missing-directory branch.
        $this->createPhysicalAttachment($clinicId, 'patient', $patientId, 'allowed.pdf', 'application/pdf', 'attachment', $user['id']);
        $this->assertDirectoryExists(WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'clinic_' . $clinicId);

        // Create owned synthetic existing file under this worktree writable outside the active clinic uploads path
        $cacheDir = WRITEPATH . 'cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $confinementFileName = 'confinement_test_' . bin2hex(random_bytes(6)) . '.pdf';
        $confinementFilePath = $cacheDir . DIRECTORY_SEPARATOR . $confinementFileName;
        file_put_contents($confinementFilePath, self::SYNTHETIC_PDF_BYTES);
        $this->createdPhysicalFiles[] = $confinementFilePath;

        // Prove physical target exists before routed request
        $this->assertFileExists($confinementFilePath, 'Physical target file must exist prior to routed request');

        // Supported storage relative path pointing outside the clinic upload directory
        $relPath = '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $confinementFileName;

        $now = date('Y-m-d H:i:s');
        $this->db->table('file_attachments')->insert([
            'clinic_id' => $clinicId,
            'file_name' => $confinementFileName,
            'original_name' => $confinementFileName,
            'file_path' => $relPath,
            'mime_type' => 'application/pdf',
            'file_size' => strlen(self::SYNTHETIC_PDF_BYTES),
            'entity_type' => 'patient',
            'entity_id' => $patientId,
            'purpose' => 'attachment',
            'created_by' => $user['id'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $confinementAttachmentId = (int) $this->db->insertID();
        $this->createdAttachments[] = $confinementAttachmentId;

        $session = $this->buildSession($user, $clinicId);

        // Test must exercise actual confinement instead of merely a missing file 404
        $this->assertRouteFailsNotFound(function () use ($session, $confinementAttachmentId) {
            return $this->withSession($session)->get('/file/download/' . $confinementAttachmentId);
        });

        $this->assertRouteFailsNotFound(function () use ($session, $confinementAttachmentId) {
            return $this->withSession($session)->get('/file/inline/' . $confinementAttachmentId);
        });
    }

    // =========================================================================
    // 12. Unknown Entity Type Rejection
    // =========================================================================

    public function testUnknownEntityTypeFailsNotFound(): void
    {
        [$user, $clinicId] = $this->createTenantUserWithPermissions(['patients' => ['view']]);
        $unknownAttachmentId = $this->createPhysicalAttachment(
            $clinicId,
            'unsupported_entity_type',
            123,
            'unknown.pdf',
            'application/pdf',
            'attachment',
            $user['id']
        );

        $session = $this->buildSession($user, $clinicId);

        $this->assertRouteFailsNotFound(function () use ($session, $unknownAttachmentId) {
            return $this->withSession($session)->get('/file/download/' . $unknownAttachmentId);
        });

        $this->assertRouteFailsNotFound(function () use ($session, $unknownAttachmentId) {
            return $this->withSession($session)->get('/file/inline/' . $unknownAttachmentId);
        });
    }

    // =========================================================================
    // 13. Generated HTML Contract Compatibility (Download as Octet-Stream, Inline Denied 403)
    // =========================================================================

    public function testGeneratedHtmlContractDownloadOctetStreamAndInlineDenied(): void
    {
        [$user, $clinicId] = $this->createTenantUserWithPermissions(['patients' => ['view']]);
        $patientId = $this->createPatient($clinicId, $user['id']);
        $contractId = $this->createContract($clinicId, $patientId, $user['id']);

        $htmlContent = "<!DOCTYPE html><html><body><h1>Treatment Contract #{$contractId}</h1><p>Patient Terms and Conditions</p></body></html>";

        // Contract artifact generated as text/html by TreatmentContracts::print
        $contractAttachmentId = $this->createPhysicalAttachment(
            $clinicId,
            'contract',
            $contractId,
            'contract_print.html',
            'text/html',
            'contract_pdf',
            $user['id'],
            $htmlContent
        );

        $session = $this->buildSession($user, $clinicId);

        // Download: Available safely as application/octet-stream with nosniff
        $downloadRes = $this->withSession($session)->get('/file/download/' . $contractAttachmentId);
        $downloadRes->assertStatus(200);
        $this->assertInstanceOf(\CodeIgniter\HTTP\DownloadResponse::class, $downloadRes->response());
        $downloadRes->response()->buildHeaders();
        $downloadRes->assertHeader('Content-Type', 'application/octet-stream; charset=UTF-8');
        $downloadRes->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('attachment', (string) $downloadRes->response()->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString('contract_print.html', (string) $downloadRes->response()->getHeaderLine('Content-Disposition'));

        // Inline: Preview denied as intentional fail-closed security behavior (403 Forbidden)
        $inlineRes = $this->withSession($session)->get('/file/inline/' . $contractAttachmentId);
        $inlineRes->assertStatus(403);
    }

    // =========================================================================
    // 14. Soft-Deleted Entities and Soft-Deleted Parent Patient Regression
    // =========================================================================

    public function testSoftDeletedEntitiesAndSoftDeletedParentPatientDenied(): void
    {
        [$user, $clinicId] = $this->createTenantUserWithPermissions([
            'patients' => ['view'],
            'examinations' => ['view'],
            'appointments' => ['view'],
            'treatments' => ['view'],
            'prescriptions' => ['view'],
            'finance' => ['view'],
        ]);

        $deletedTimestamp = date('Y-m-d H:i:s');
        $session = $this->buildSession($user, $clinicId);

        // 1. Soft-deleted parent patient: examination itself is active, but its parent patient is soft-deleted
        $deletedPatientId = $this->createPatient($clinicId, $user['id'], $deletedTimestamp);
        $activeExamWithDeletedPatient = $this->createExamination($clinicId, $deletedPatientId, $user['id'], null);
        $orphanParentAttachmentId = $this->createPhysicalAttachment(
            $clinicId,
            'examination',
            $activeExamWithDeletedPatient,
            'exam_orphan_patient.pdf',
            'application/pdf',
            'attachment',
            $user['id']
        );

        $this->assertRouteFailsNotFound(function () use ($session, $orphanParentAttachmentId) {
            return $this->withSession($session)->get('/file/download/' . $orphanParentAttachmentId);
        });
        $this->assertRouteFailsNotFound(function () use ($session, $orphanParentAttachmentId) {
            return $this->withSession($session)->get('/file/inline/' . $orphanParentAttachmentId);
        });

        // 2. Loop across all seven soft-deletable entity families:
        //    patients, examinations, appointments, treatments, prescriptions, contracts, finances
        $activePatientId = $this->createPatient($clinicId, $user['id'], null);

        $softDeletedEntities = [
            'patient' => $this->createPatient($clinicId, $user['id'], $deletedTimestamp),
            'examination' => $this->createExamination($clinicId, $activePatientId, $user['id'], $deletedTimestamp),
            'appointment' => $this->createAppointment($clinicId, $activePatientId, $user['id'], $deletedTimestamp),
            'treatment' => $this->createTreatment($clinicId, $activePatientId, $user['id'], $deletedTimestamp),
            'prescription' => $this->createPrescription($clinicId, $activePatientId, $user['id'], $deletedTimestamp),
            'contract' => $this->createContract($clinicId, $activePatientId, $user['id'], $deletedTimestamp),
            'finance' => $this->createFinanceRecord($clinicId, $activePatientId, $user['id'], $deletedTimestamp),
        ];

        foreach ($softDeletedEntities as $entityType => $entityId) {
            $attachmentId = $this->createPhysicalAttachment(
                $clinicId,
                $entityType,
                $entityId,
                "soft_deleted_{$entityType}.pdf",
                'application/pdf',
                'attachment',
                $user['id']
            );

            $this->assertRouteFailsNotFound(function () use ($session, $attachmentId) {
                return $this->withSession($session)->get('/file/download/' . $attachmentId);
            });

            $this->assertRouteFailsNotFound(function () use ($session, $attachmentId) {
                return $this->withSession($session)->get('/file/inline/' . $attachmentId);
            });
        }
    }

    // =========================================================================
    // 15. Clinic Upload Directory Alias Rejection
    // =========================================================================

    public function testClinicDirectoryAliasCannotAuthorizeFileDelivery(): void
    {
        // 1. Create target clinic (Clinic B) with a valid physical file
        $clinicB = $this->createClinic();
        $clinicBDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'clinic_' . $clinicB;
        if (!is_dir($clinicBDir)) {
            mkdir($clinicBDir, 0755, true);
        }

        $fileName = bin2hex(random_bytes(8)) . '_aliased_target.pdf';
        $targetFile = $clinicBDir . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($targetFile, self::SYNTHETIC_PDF_BYTES);
        $this->createdPhysicalFiles[] = $targetFile;

        // 2. Create aliased clinic (Clinic A) with valid patient and permissions
        [$userA, $clinicA] = $this->createTenantUserWithPermissions(['patients' => ['view']]);
        $patientA = $this->createPatient($clinicA, $userA['id']);

        $clinicADir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'clinic_' . $clinicA;
        if (file_exists($clinicADir) || is_link($clinicADir)) {
            $this->markTestSkipped("Fixture path {$clinicADir} unexpectedly exists; skipping alias test.");
            return;
        }

        // Windows must exercise a junction: PHP is_link() can miss this alias type.
        if (PHP_OS_FAMILY === 'Windows') {
            $this->operateOwnedWindowsJunction($clinicADir, $clinicBDir, false);
            $this->createdWindowsJunctions[] = [$clinicADir, $clinicBDir];
            $this->assertNotFalse(realpath($clinicBDir));
            $this->assertSame(realpath($clinicBDir), realpath($clinicADir));
        } else {
        // 3. Attempt to create directory symlink from Clinic A upload dir to Clinic B upload dir
        $createdLink = false;
        try {
            $createdLink = @symlink($clinicBDir, $clinicADir);
        } catch (\Throwable $e) {
            $createdLink = false;
        }

        if (!$createdLink || !is_link($clinicADir)) {
            $this->markTestSkipped(
                'Symlink creation not permitted or unsupported in this environment (e.g. Windows SeCreateSymbolicLinkPrivilege required). ' .
                'Root can independently verify junction/symlink behavior with an owned disposable path.'
            );
            return;
        }

        // Register link for safe non-recursive teardown removal
        $this->createdDirectoryAliases[] = $clinicADir;
        }

        // 4. Create attachment record for Clinic A pointing to the file path
        $relPath = 'clinic_' . $clinicA . DIRECTORY_SEPARATOR . $fileName;
        $now = date('Y-m-d H:i:s');
        $this->db->table('file_attachments')->insert([
            'clinic_id' => $clinicA,
            'file_name' => $fileName,
            'original_name' => 'aliased_target.pdf',
            'file_path' => $relPath,
            'mime_type' => 'application/pdf',
            'file_size' => strlen(self::SYNTHETIC_PDF_BYTES),
            'entity_type' => 'patient',
            'entity_id' => $patientA,
            'purpose' => 'attachment',
            'file_hash' => md5(self::SYNTHETIC_PDF_BYTES),
            'created_by' => $userA['id'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $attachmentId = (int) $this->db->insertID();
        $this->createdAttachments[] = $attachmentId;

        // 5. Build session for User A in Clinic A and verify both endpoints reject delivery
        $session = $this->buildSession($userA, $clinicA);

        $this->assertRouteFailsNotFound(function () use ($session, $attachmentId) {
            return $this->withSession($session)->get('/file/download/' . $attachmentId);
        });

        $this->assertRouteFailsNotFound(function () use ($session, $attachmentId) {
            return $this->withSession($session)->get('/file/inline/' . $attachmentId);
        });
        $this->assertSame(self::SYNTHETIC_PDF_BYTES, file_get_contents($targetFile), 'Foreign fixture target must remain unchanged');
    }

    private function operateOwnedWindowsJunction(string $linkPath, string $targetPath, bool $remove): void
    {
        foreach ([$linkPath, $targetPath] as $path) {
            $this->assertSame(WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR, dirname($path) . DIRECTORY_SEPARATOR);
            $this->assertSame(1, preg_match('/^clinic_(\d+)$/', basename($path), $matches));
            $this->assertContains((int) $matches[1], $this->createdClinics);
        }
        $quote = static fn (string $path): string => "'" . str_replace("'", "''", $path) . "'";
        $link = $quote($linkPath);
        $target = $quote($targetPath);
        $operation = $remove
            ? '$item=Get-Item -LiteralPath ' . $link . ' -Force; if(($item.Attributes -band [IO.FileAttributes]::ReparsePoint) -eq 0){throw "Expected owned junction"}; [IO.Directory]::Delete(' . $link . ', $false)'
            : 'New-Item -ItemType Junction -Path ' . $link . ' -Target ' . $target . ' -ErrorAction Stop | Out-Null';
        $process = proc_open([
            'C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe',
            '-NoProfile', '-NonInteractive', '-Command', "$" . "ErrorActionPreference='Stop'; " . $operation,
        ], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, null, ['bypass_shell' => true]);
        $this->assertIsResource($process);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), 'Owned junction operation failed: ' . $stdout . $stderr);
        clearstatcache(true, $linkPath);
    }

    // =========================================================================
    // Fixture Helpers
    // =========================================================================

    private function createTenantUserWithPermissions(array $moduleActions, ?int $existingClinicId = null): array
    {
        $clinicId = $existingClinicId ?? $this->createClinic();
        $user = $this->createUser();
        $roleId = $this->createRole($clinicId, true, $user['id']);

        foreach ($moduleActions as $module => $actions) {
            foreach ($actions as $action) {
                $permId = $this->permissionId($module, $action);
                $this->assignPermission($roleId, $permId);
            }
        }

        $this->createMembership($user['id'], $clinicId, $roleId);

        return [$user, $clinicId, $roleId];
    }

    private function buildSession(array $user, int $clinicId, array $extra = []): array
    {
        $values = array_merge([
            'identity' => $user['email'],
            'email' => $user['email'],
            'user_id' => $user['id'],
            'last_check' => time(),
            'global_mode' => false,
            'logged_in' => true,
            'active_clinic_id' => $clinicId,
        ], $extra);

        $_SESSION = $values;
        session()->set($values);

        return $values;
    }

    private function createClinic(): int
    {
        $now = date('Y-m-d H:i:s');
        $suffix = bin2hex(random_bytes(4));

        $this->db->table('clinics')->insert([
            'name' => 'Test Clinic ' . $suffix,
            'slug' => 'clinic-' . $suffix,
            'uuid' => sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            ),
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $clinicId = (int) $this->db->insertID();
        $this->createdClinics[] = $clinicId;

        // Ensure active subscription for SubscriptionFilter
        $planId = $this->getOrCreatePlan();
        $this->db->table('clinic_subscriptions')->insert([
            'clinic_id' => $clinicId,
            'plan_id' => $planId,
            'status' => 'active',
            'start_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'end_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->createdSubscriptions[] = (int) $this->db->insertID();

        return $clinicId;
    }

    private function getOrCreatePlan(): int
    {
        $row = $this->db->table('plans')->where('status', 'active')->get()->getRowArray();
        if ($row) {
            return (int) $row['id'];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('plans')->insert([
            'name' => 'Test Plan ' . bin2hex(random_bytes(3)),
            'status' => 'active',
            'features_json' => json_encode(['exports.enabled' => true]),
            'limits_json' => json_encode([]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $id = (int) $this->db->insertID();
        $this->createdPlans[] = $id;
        return $id;
    }

    private function createUser(): array
    {
        $email = 'user_' . bin2hex(random_bytes(5)) . '@test.local';
        $nowTs = time();
        $this->db->table('users')->insert([
            'email' => $email,
            'username' => $email,
            'password' => '$2y$10$abcdefghijklmnopqrstuv',
            'active' => 1,
            'first_name' => 'Test',
            'last_name' => 'User',
            'ip_address' => '127.0.0.1',
            'created_on' => $nowTs,
            'updated_at' => $nowTs,
            'created_at' => $nowTs,
        ]);
        $id = (int) $this->db->insertID();
        $this->createdUsers[] = $id;
        return ['id' => $id, 'email' => $email];
    }

    private function createRole(int $clinicId, bool $isActive = true, ?int $createdBy = null): int
    {
        $now = date('Y-m-d H:i:s');
        $slug = 'role_' . bin2hex(random_bytes(4));
        $this->db->table('roles')->insert([
            'name' => $slug,
            'slug' => $slug,
            'description' => 'Test role',
            'is_active' => $isActive ? 1 : 0,
            'is_system' => 0,
            'clinic_id' => $clinicId,
            'created_by' => $createdBy,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $id = (int) $this->db->insertID();
        $this->createdRoles[] = $id;
        return $id;
    }

    private function permissionId(string $module, string $action): int
    {
        $row = $this->db->table('permissions')
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
            'name' => "{$module}:{$action}",
            'description' => "Test perm {$module}:{$action}",
            'is_system' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $id = (int) $this->db->insertID();
        $this->createdPermissions[] = $id;
        return $id;
    }

    private function assignPermission(int $roleId, int $permId): void
    {
        $this->db->table('role_permissions')->insert([
            'role_id' => $roleId,
            'permission_id' => $permId,
            'granted' => 1,
        ]);
        $this->createdRolePermissions[] = (int) $this->db->insertID();
    }

    private function createMembership(int $userId, int $clinicId, int $roleId): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('clinic_users')->insert([
            'user_id' => $userId,
            'clinic_id' => $clinicId,
            'role_id' => $roleId,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->createdMemberships[] = (int) $this->db->insertID();
    }

    private function createLogoSetting(int $clinicId, int $attachmentId): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('settings')->insert([
            'clinic_id' => $clinicId,
            'setting_key' => 'clinic_logo_path',
            'setting_value' => 'file/download/' . $attachmentId,
            'setting_type' => 'string',
            'description' => 'Clinic Logo Path',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->createdSettings[] = (int) $this->db->insertID();
    }

    private function createPhysicalAttachment(
        int $clinicId,
        string $entityType,
        ?int $entityId,
        string $originalName,
        string $mimeType,
        string $purpose = 'attachment',
        ?int $userId = null,
        ?string $rawContent = null
    ): int {
        $tenantDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'clinic_' . $clinicId;
        if (!is_dir($tenantDir)) {
            mkdir($tenantDir, 0755, true);
        }

        $randomName = bin2hex(random_bytes(8)) . '_' . $originalName;
        $physicalPath = $tenantDir . DIRECTORY_SEPARATOR . $randomName;

        if ($rawContent !== null) {
            $content = $rawContent;
        } elseif ($mimeType === 'image/png') {
            $content = self::SYNTHETIC_PNG_BYTES;
        } else {
            $content = self::SYNTHETIC_PDF_BYTES;
        }

        file_put_contents($physicalPath, $content);
        $this->createdPhysicalFiles[] = $physicalPath;

        $relPath = 'clinic_' . $clinicId . DIRECTORY_SEPARATOR . $randomName;
        $now = date('Y-m-d H:i:s');

        $this->db->table('file_attachments')->insert([
            'clinic_id' => $clinicId,
            'file_name' => $randomName,
            'original_name' => $originalName,
            'file_path' => $relPath,
            'mime_type' => $mimeType,
            'file_size' => strlen($content),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'purpose' => $purpose,
            'file_hash' => md5($content),
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $id = (int) $this->db->insertID();
        $this->createdAttachments[] = $id;
        return $id;
    }

    private function createPatient(int $clinicId, ?int $userId = null, ?string $deletedAt = null): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('patients')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => 'PAT-' . bin2hex(random_bytes(3)),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '1234567890',
            'date_of_birth' => '1990-01-01',
            'gender' => 'other',
            'status' => 'active',
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => $deletedAt,
        ]);
        $id = (int) $this->db->insertID();
        $this->createdPatients[] = $id;
        return $id;
    }

    private function createExamination(int $clinicId, int $patientId, ?int $userId = null, ?string $deletedAt = null): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('examinations')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'examination_id' => 'EXAM-' . bin2hex(random_bytes(3)),
            'examination_date' => date('Y-m-d'),
            'examination_type' => 'initial',
            'chief_complaint' => 'Routine dental examination',
            'status' => 'completed',
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => $deletedAt,
        ]);
        $id = (int) $this->db->insertID();
        $this->createdExaminations[] = $id;
        return $id;
    }

    private function createAppointment(int $clinicId, int $patientId, ?int $userId = null, ?string $deletedAt = null): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('appointments')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'provider_user_id' => $userId,
            'appointment_id' => 'APPT-' . bin2hex(random_bytes(3)),
            'appointment_date' => date('Y-m-d'),
            'appointment_time' => '10:00:00',
            'duration' => 30,
            'appointment_type' => 'consultation',
            'status' => 'scheduled',
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => $deletedAt,
        ]);
        $id = (int) $this->db->insertID();
        $this->createdAppointments[] = $id;
        return $id;
    }

    private function createTreatment(int $clinicId, int $patientId, ?int $userId = null, ?string $deletedAt = null): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('treatments')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'treatment_id' => 'TRT-' . bin2hex(random_bytes(3)),
            'treatment_name' => 'Routine Cleaning',
            'treatment_type' => 'cleaning',
            'treatment_description' => 'Routine tooth cleaning and polishing',
            'start_date' => date('Y-m-d'),
            'estimated_duration' => 30,
            'status' => 'active',
            'cost' => 100.00,
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => $deletedAt,
        ]);
        $id = (int) $this->db->insertID();
        $this->createdTreatments[] = $id;
        return $id;
    }

    private function createPrescription(int $clinicId, int $patientId, ?int $userId = null, ?string $deletedAt = null): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('prescriptions')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'prescription_id' => 'RX-' . bin2hex(random_bytes(3)),
            'medication_name' => 'Amoxicillin 500mg',
            'dosage' => '500mg',
            'frequency' => '3 times daily',
            'duration' => '7 days',
            'instructions' => 'Take one tablet every 8 hours with water',
            'prescribed_date' => date('Y-m-d'),
            'expiry_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'active',
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => $deletedAt,
        ]);
        $id = (int) $this->db->insertID();
        $this->createdPrescriptions[] = $id;
        return $id;
    }

    private function createContract(int $clinicId, int $patientId, ?int $userId = null, ?string $deletedAt = null): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('treatment_contracts')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'status' => 'draft',
            'total_cost' => 1000.00,
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => $deletedAt,
        ]);
        $id = (int) $this->db->insertID();
        $this->createdContracts[] = $id;
        return $id;
    }

    private function createFinanceRecord(int $clinicId, ?int $patientId = null, ?int $userId = null, ?string $deletedAt = null): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('finances')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'transaction_id' => 'TXN-' . bin2hex(random_bytes(3)),
            'transaction_type' => 'payment',
            'amount' => 200.00,
            'currency' => 'USD',
            'payment_method' => 'cash',
            'service_type' => 'treatment',
            'transaction_date' => date('Y-m-d'),
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => $deletedAt,
        ]);
        $id = (int) $this->db->insertID();
        $this->createdFinances[] = $id;
        return $id;
    }

    private function createInventoryUsage(int $clinicId, int $userId): int
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('Valid positive fixture user ID required for inventory_usage.recorded_by');
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('inventory_usage')->insert([
            'clinic_id' => $clinicId,
            'usage_date' => date('Y-m-d'),
            'items_used' => json_encode([]),
            'total_cost' => 50.00,
            'recorded_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $id = (int) $this->db->insertID();
        $this->createdInventoryUsage[] = $id;
        return $id;
    }

    private function assertRouteFailsNotFound(callable $action): void
    {
        try {
            $response = $action();
            $statusCode = $response ? $response->response()->getStatusCode() : 0;
            $this->assertSame(404, $statusCode, 'Expected 404 response status');
        } catch (PageNotFoundException $e) {
            $this->assertSame(404, $e->getCode(), 'Expected 404 PageNotFoundException');
        }
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
