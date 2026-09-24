<?php

namespace Tests\Unit;

use App\Models\ClinicModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\ClinicUserModel;
use App\Models\IonAuthModel;
use App\Models\PlanModel;
use App\Models\RoleModel;
use App\Models\SettingsModel;
use App\Models\UserModel;
use App\Services\SettingsService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Exceptions\BadRequestException;
use CodeIgniter\Router\DefinedRouteCollector;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;
use ReflectionClass;
use RuntimeException;

/**
 * TenantBackupContainmentTest
 *
 * Verifies P0 containment of tenant database backup endpoints:
 * 1. Route registration and mapping
 * 2. Unauthenticated access denial / redirect
 * 3. Absent tenant context denial / redirect
 * 4. Control Plane / Global Mode airlock enforcement (404)
 * 5. Ordinary member without settings permission denial (403/redirect)
 * 6. Authorized clinic manager hard-denial (404 PageNotFoundException) on all 4 endpoints:
 *    - GET /settings/backup
 *    - POST /settings/create-backup
 *    - GET /settings/download-backup/(:segment)
 *    - POST /settings/restore
 * 7. Verification of file contents (synthetic canary hash) and database row non-mutation
 * 8. Two-clinic cross-tenant isolation enforcement
 * 9. Privileged tenant impersonation containment (impersonating admin also denied 404)
 * 10. Path traversal rejection
 * 11. SettingsService::generateDatabaseBackup() direct call throws RuntimeException
 * 12. Settings::getBackups() returns empty array without scanning disk
 * 13. Ordinary settings viewing and updating remain functional in English (explicit assertions)
 * 14. Ordinary settings viewing remains functional in Arabic (explicit assertions)
 */
final class TenantBackupContainmentTest extends CIUnitTestCase
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
    private array $createdSettings = [];
    private ?string $syntheticCanaryPath = null;

    public const APPROVED_TEST_DATABASE = 'democa_dental_saas_test';
    private bool $isolationVerified = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Fail-closed database isolation safeguard:
        // Must verify that tests connect strictly to the approved disposable test database
        // and NEVER to the business database (democa_dental_saas).
        $connection = Database::connect('tests');
        $resolvedDb = (string) $connection->getDatabase();
        $dbConfig = config('Database');
        $businessDb = (string) ($dbConfig->default['database'] ?? 'democa_dental_saas');

        if ($resolvedDb === '' || $resolvedDb === $businessDb || $resolvedDb !== self::APPROVED_TEST_DATABASE) {
            $this->db = null; // Ensure tearDown cannot mutate anything
            $this->isolationVerified = false;
            throw new \RuntimeException(
                "FAIL-CLOSED ISOLATION SAFEGUARD: TenantBackupContainmentTest requires approved test database '" .
                self::APPROVED_TEST_DATABASE . "', but active connection resolved to '{$resolvedDb}' (business database: '{$businessDb}'). Execution aborted to prevent writing test fixtures to business data."
            );
        }

        $this->db = $connection;
        $this->isolationVerified = true;
        $this->withGuestSession();
    }

    protected function tearDown(): void
    {
        if ($this->syntheticCanaryPath && file_exists($this->syntheticCanaryPath)) {
            @unlink($this->syntheticCanaryPath);
            $this->syntheticCanaryPath = null;
        }

        // Only perform database cleanups if isolation was verified and db connection exists
        if ($this->isolationVerified && $this->db !== null) {
            if ($this->createdUsers !== []) {
                $this->db->table('activity_logs')->whereIn('user_id', $this->createdUsers)->delete();
            }
            if ($this->createdSettings !== []) {
                $this->deleteRows('settings', $this->createdSettings);
            }
            if ($this->createdClinics !== []) {
                $this->db->table('settings')->whereIn('clinic_id', $this->createdClinics)->delete();
            }
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

    // -------------------------------------------------------------------------
    // 1. Route Registration Verification
    // -------------------------------------------------------------------------

    public function testBackupRoutesAreRegisteredInRouteCollection(): void
    {
        $collector = new DefinedRouteCollector(service('routes')->loadRoutes());
        $routes = iterator_to_array($collector->collect());

        $hasGetBackup = false;
        $hasPostCreate = false;
        $hasGetDownload = false;
        $hasPostRestore = false;

        foreach ($routes as $route) {
            $method = strtolower($route['method']);
            $pattern = $route['route'];

            if ($method === 'get' && $pattern === 'settings/backup') {
                $hasGetBackup = true;
            }
            if ($method === 'post' && $pattern === 'settings/create-backup') {
                $hasPostCreate = true;
            }
            if ($method === 'get' && str_starts_with($pattern, 'settings/download-backup/')) {
                $hasGetDownload = true;
            }
            if ($method === 'post' && $pattern === 'settings/restore') {
                $hasPostRestore = true;
            }
        }

        $this->assertTrue($hasGetBackup, 'GET settings/backup route must be registered');
        $this->assertTrue($hasPostCreate, 'POST settings/create-backup route must be registered');
        $this->assertTrue($hasGetDownload, 'GET settings/download-backup/(:segment) route must be registered');
        $this->assertTrue($hasPostRestore, 'POST settings/restore route must be registered');
    }

    // -------------------------------------------------------------------------
    // 2. Unauthenticated Access Denial
    // -------------------------------------------------------------------------

    public function testUnauthenticatedAccessToBackupEndpointsRedirectsToLogin(): void
    {
        $this->withGuestSession();

        $responseGetBackup = $this->get('/settings/backup');
        $this->assertTrue($responseGetBackup->isRedirect(), 'GET /settings/backup must redirect when unauthenticated');

        $responsePostCreate = $this->postWithCsrf('/settings/create-backup', []);
        $this->assertTrue($responsePostCreate->isRedirect(), 'POST /settings/create-backup must redirect when unauthenticated');

        $responseGetDownload = $this->get('/settings/download-backup/test_dump.sql');
        $this->assertTrue($responseGetDownload->isRedirect(), 'GET /settings/download-backup/(:segment) must redirect when unauthenticated');

        $responsePostRestore = $this->postWithCsrf('/settings/restore', []);
        $this->assertTrue($responsePostRestore->isRedirect(), 'POST /settings/restore must redirect when unauthenticated');
    }

    // -------------------------------------------------------------------------
    // 3. Absent Tenant Context Denial
    // -------------------------------------------------------------------------

    public function testMissingTenantContextRedirectsToClinicSelect(): void
    {
        $user = $this->createUser();

        // Logged in user without active_clinic_id
        $this->withLoggedInSession($user, ['active_clinic_id' => null]);

        $responseGetBackup = $this->get('/settings/backup');
        $this->assertTrue($responseGetBackup->isRedirect(), 'GET /settings/backup must redirect when tenant context missing');
        $responseGetBackup->assertRedirectTo('/clinic/select');

        $responsePostCreate = $this->postWithCsrf('/settings/create-backup', []);
        $this->assertTrue($responsePostCreate->isRedirect(), 'POST /settings/create-backup must redirect when tenant context missing');
        $responsePostCreate->assertRedirectTo('/clinic/select');

        $responseGetDownload = $this->get('/settings/download-backup/test_dump.sql');
        $this->assertTrue($responseGetDownload->isRedirect(), 'GET /settings/download-backup must redirect when tenant context missing');
        $responseGetDownload->assertRedirectTo('/clinic/select');

        $responsePostRestore = $this->postWithCsrf('/settings/restore', []);
        $this->assertTrue($responsePostRestore->isRedirect(), 'POST /settings/restore must redirect when tenant context missing');
        $responsePostRestore->assertRedirectTo('/clinic/select');
    }

    // -------------------------------------------------------------------------
    // 4. Control Plane / Global Mode Airlock Enforcement (404)
    // -------------------------------------------------------------------------

    public function testGlobalModeControlPlaneAccessToTenantBackupRoutesFailsClosedWith404(): void
    {
        $user = $this->createUser();
        $clinic = $this->createClinic('active');

        // Global mode active - airlock denies tenant plane web routes with 404
        $this->withLoggedInSession($user, [
            'active_clinic_id' => $clinic,
            'global_mode' => true,
        ]);

        $this->assertRouteFailsNotFound(fn() => $this->get('/settings/backup'));
        $this->assertRouteFailsNotFound(fn() => $this->postWithCsrf('/settings/create-backup', []));
        $this->assertRouteFailsNotFound(fn() => $this->get('/settings/download-backup/test_dump.sql'));
        $this->assertRouteFailsNotFound(fn() => $this->postWithCsrf('/settings/restore', []));
    }

    // -------------------------------------------------------------------------
    // 5. Ordinary Tenant Member Without Settings Permission Denied (403/Redirect)
    // -------------------------------------------------------------------------

    public function testOrdinaryTenantMemberWithoutSettingsPermissionIsDenied(): void
    {
        // Fixture with empty permissions (no settings:view or settings:edit)
        [$user, $clinic] = $this->createTenantFixture([]);

        $this->withLoggedInSession($user, [
            'active_clinic_id' => $clinic,
            'global_mode' => false,
        ]);

        $responseGetBackup = $this->get('/settings/backup');
        $this->assertTrue($responseGetBackup->isRedirect(), 'GET /settings/backup must deny user without settings:view');
        $responseGetBackup->assertRedirectTo('/');

        $responsePostCreate = $this->postWithCsrf('/settings/create-backup', []);
        $this->assertTrue($responsePostCreate->isRedirect(), 'POST /settings/create-backup must deny user without settings:edit');
        $responsePostCreate->assertRedirectTo('/');

        $responseGetDownload = $this->get('/settings/download-backup/test_dump.sql');
        $this->assertTrue($responseGetDownload->isRedirect(), 'GET /settings/download-backup must deny user without settings:view');
        $responseGetDownload->assertRedirectTo('/');

        $responsePostRestore = $this->postWithCsrf('/settings/restore', []);
        $this->assertTrue($responsePostRestore->isRedirect(), 'POST /settings/restore must deny user without settings:edit');
        $responsePostRestore->assertRedirectTo('/');
    }

    // -------------------------------------------------------------------------
    // 6. Authorized Clinic Manager Hard-Denial & File/Row Non-Mutation Proof
    // -------------------------------------------------------------------------

    public function testAuthorizedClinicManagerIsDeniedWith404OnAllBackupEndpoints(): void
    {
        // Fixture with settings:view and settings:edit permissions
        [$user, $clinic] = $this->createTenantFixture(['view', 'edit']);

        $this->withLoggedInSession($user, [
            'active_clinic_id' => $clinic,
            'global_mode' => false,
        ]);

        $backupsDir = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;
        if (!is_dir($backupsDir)) {
            mkdir($backupsDir, 0755, true);
        }

        // Create a synthetic existing backup canary file
        $canaryFilename = 'synthetic_canary_' . bin2hex(random_bytes(6)) . '.sql';
        $canaryPath = $backupsDir . $canaryFilename;
        $canaryContent = "-- SYNTHETIC TEST CANARY DUMP - DO NOT EXECUTE - " . bin2hex(random_bytes(32));
        file_put_contents($canaryPath, $canaryContent);
        $this->syntheticCanaryPath = $canaryPath;

        $canarySizeBefore = filesize($canaryPath);
        $canaryHashBefore = hash_file('sha256', $canaryPath);
        $filesBefore = scandir($backupsDir);

        // Create a synthetic setting row to verify database row-level non-mutation
        $canaryKey = 'canary_test_key_' . bin2hex(random_bytes(4));
        $this->db->table('settings')->insert([
            'clinic_id' => $clinic,
            'setting_key' => $canaryKey,
            'setting_value' => 'canary_unmutated_value',
            'setting_type' => 'string',
        ]);
        $canarySettingId = (int) $this->db->insertID();
        $this->createdSettings[] = $canarySettingId;

        $tablesBefore = $this->db->listTables();

        // 1. GET /settings/backup
        $this->assertRouteFailsNotFound(function () {
            $res = $this->get('/settings/backup');
            $this->assertNoDumpBytesInResponse($res->getBody());
            return $res;
        });

        // 2. POST /settings/create-backup
        $this->assertRouteFailsNotFound(function () {
            $res = $this->postWithCsrf('/settings/create-backup', []);
            $this->assertNoDumpBytesInResponse($res->getBody());
            return $res;
        });

        // 3. GET /settings/download-backup/(:segment) targeting the REAL synthetic existing file
        $this->assertRouteFailsNotFound(function () use ($canaryFilename, $canaryContent) {
            $res = $this->get('/settings/download-backup/' . $canaryFilename);
            $body = $res->getBody();
            $this->assertStringNotContainsString($canaryContent, $body, 'Response must never leak synthetic canary dump bytes');
            $this->assertNoDumpBytesInResponse($body);
            return $res;
        });

        // 4. POST /settings/restore targeting the synthetic canary file
        $this->assertRouteFailsNotFound(function () use ($canaryFilename) {
            $res = $this->postWithCsrf('/settings/restore', [
                'backup_file' => $canaryFilename,
            ]);
            $this->assertNoDumpBytesInResponse($res->getBody());
            return $res;
        });

        // Verify synthetic canary file was NOT mutated, overwritten, or truncated
        $this->assertFileExists($canaryPath, 'Synthetic canary file must remain on disk');
        $this->assertSame($canarySizeBefore, filesize($canaryPath), 'Synthetic canary file size must be unchanged');
        $this->assertSame($canaryHashBefore, hash_file('sha256', $canaryPath), 'Synthetic canary SHA-256 hash must be identical');

        // Verify zero new files created in writable/backups/
        $filesAfter = scandir($backupsDir);
        $this->assertSame($filesBefore, $filesAfter, 'No new backup files must be created in writable/backups/');

        // Verify synthetic database row was NOT altered
        $rowAfter = $this->db->table('settings')->where('id', $canarySettingId)->get()->getRowArray();
        $this->assertNotNull($rowAfter);
        $this->assertSame('canary_unmutated_value', $rowAfter['setting_value'], 'Database rows must not be mutated by backup/restore calls');

        // Verify zero database schema mutations
        $tablesAfter = $this->db->listTables();
        $this->assertSame($tablesBefore, $tablesAfter, 'Database schema must not have been mutated');
    }

    // -------------------------------------------------------------------------
    // 7. Two-Clinic Isolation Enforcement
    // -------------------------------------------------------------------------

    public function testTwoClinicIsolationCannotAccessOtherClinicBackups(): void
    {
        [$userA, $clinicA] = $this->createTenantFixture(['view', 'edit']);
        [$userB, $clinicB] = $this->createTenantFixture(['view', 'edit']);

        // User A operating in Clinic A context: denied (404)
        $this->withLoggedInSession($userA, [
            'active_clinic_id' => $clinicA,
            'global_mode' => false,
        ]);
        $this->assertRouteFailsNotFound(fn() => $this->get('/settings/backup'));
        $this->assertRouteFailsNotFound(fn() => $this->postWithCsrf('/settings/create-backup', []));

        // User A attempting to operate in Clinic B context (no membership in Clinic B):
        // TenantFilter detects membership mismatch and redirects to /clinic/select
        $this->withLoggedInSession($userA, [
            'active_clinic_id' => $clinicB,
            'global_mode' => false,
        ]);

        $resGet = $this->get('/settings/backup');
        $this->assertTrue($resGet->isRedirect(), 'Cross-clinic unauthorized session must redirect');
        $resGet->assertRedirectTo('/clinic/select');

        $resPost = $this->postWithCsrf('/settings/create-backup', []);
        $this->assertTrue($resPost->isRedirect(), 'Cross-clinic unauthorized POST must redirect');
        $resPost->assertRedirectTo('/clinic/select');
    }

    // -------------------------------------------------------------------------
    // 8. Privileged Tenant Impersonation Containment
    // -------------------------------------------------------------------------

    public function testPrivilegedTenantImpersonationCannotAccessBackupEndpoints(): void
    {
        // Setup super admin user and a target clinic
        $superAdmin = $this->createUser();
        $clinic = $this->createClinic('active');
        $plan = $this->createPlan();
        $this->createSubscription($clinic, $plan, 'active');

        // Assign super_admin role to superAdmin user
        $superAdminRole = (new RoleModel())->getBySlug('super_admin');
        if ($superAdminRole) {
            $this->db->table('user_roles')->insert([
                'user_id' => $superAdmin['id'],
                'role_id' => $superAdminRole['id'],
                'assigned_by' => 1,
                'is_active' => 1,
            ]);
        }

        // Impersonating session in tenant plane: is_impersonating = true, active_clinic_id = $clinic
        $this->withLoggedInSession($superAdmin, [
            'active_clinic_id' => $clinic,
            'is_impersonating' => true,
            'impersonated_clinic_id' => $clinic,
            'global_mode' => false,
        ]);

        // Even an impersonating administrator in tenant plane MUST be hard-denied (404)
        $this->assertRouteFailsNotFound(fn() => $this->get('/settings/backup'));
        $this->assertRouteFailsNotFound(fn() => $this->postWithCsrf('/settings/create-backup', []));
        $this->assertRouteFailsNotFound(fn() => $this->get('/settings/download-backup/democa_dump_test.sql'));
        $this->assertRouteFailsNotFound(fn() => $this->postWithCsrf('/settings/restore', [
            'backup_file' => 'democa_dump_test.sql',
        ]));
    }

    // -------------------------------------------------------------------------
    // 9. Path Traversal Rejection
    // -------------------------------------------------------------------------

    public function testPathTraversalAttemptReturns404AndDoesNotExposeFiles(): void
    {
        [$user, $clinic] = $this->createTenantFixture(['view', 'edit']);

        $this->withLoggedInSession($user, [
            'active_clinic_id' => $clinic,
            'global_mode' => false,
        ]);

        $traversalPayloads = [
            '..%2F..%2F..%2F.env',
            '..%2F..%2Findex.php',
            '..%5C..%5Cspark',
        ];

        foreach ($traversalPayloads as $payload) {
            $this->assertRouteFailsNotFoundOrBadRequest(function () use ($payload) {
                $response = $this->get('/settings/download-backup/' . $payload);
                $body = $response->getBody();
                $this->assertStringNotContainsString('database.default.password', $body);
                $this->assertStringNotContainsString('CI_ENVIRONMENT', $body);
                $this->assertNoDumpBytesInResponse($body);
                return $response;
            });
        }
    }

    // -------------------------------------------------------------------------
    // 10. Service-Level Exception Verification
    // -------------------------------------------------------------------------

    public function testSettingsServiceGenerateDatabaseBackupDirectlyThrowsRuntimeException(): void
    {
        $service = new SettingsService();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Database backup generation is disabled.');

        $service->generateDatabaseBackup();
    }

    // -------------------------------------------------------------------------
    // 11. Controller getBackups() Empty Array Contract
    // -------------------------------------------------------------------------

    public function testSettingsControllerGetBackupsReturnsEmptyArrayWithoutScanningDisk(): void
    {
        $controller = new \App\Controllers\Settings();
        $reflector = new ReflectionClass($controller);
        $method = $reflector->getMethod('getBackups');
        $method->setAccessible(true);

        $backups = $method->invoke($controller);
        $this->assertIsArray($backups);
        $this->assertSame([], $backups, 'getBackups() must unconditionally return an empty array');
    }

    // -------------------------------------------------------------------------
    // 12. Ordinary Settings Viewing & Editing in English (Explicit Assertions)
    // -------------------------------------------------------------------------

    public function testOrdinarySettingsViewEnglishRendersManagedAdvisoryAndNoLegacyControls(): void
    {
        [$user, $clinic] = $this->createTenantFixture(['view', 'edit']);

        $this->withLoggedInSession($user, [
            'active_clinic_id' => $clinic,
            'global_mode' => false,
            'ui_locale' => 'en',
        ]);

        $response = $this->get('/settings');
        $response->assertStatus(200);

        $body = (string) $response->response()->getBody();

        // Explicit English advisory assertions without fallback
        $enTitle = lang('TenantMisc.backupManagedPlatformTitle', [], 'en');
        $this->assertNotEmpty($enTitle);
        $this->assertStringContainsString($enTitle, $body, 'Settings view must display the English managed platform backup title');

        $enDesc = lang('TenantMisc.backupManagedPlatformDesc', [], 'en');
        $this->assertNotEmpty($enDesc);
        $this->assertStringContainsString($enDesc, $body, 'Settings view must display the English managed platform backup description');

        // Verify old backup triggers/forms are NOT present
        $this->assertStringNotContainsString('/settings/create-backup', $body);
        $this->assertStringNotContainsString('/settings/restore', $body);
        $this->assertStringNotContainsString('/settings/download-backup', $body);
        $this->assertStringNotContainsString('downloadBackup(', $body);
        $this->assertStringNotContainsString('restoreBackup(', $body);

        // Verify normal settings update remains functional
        $clinicData = [
            'clinic_name' => 'Renamed Test Clinic EN',
            'clinic_address' => '123 Test Ave',
            'clinic_phone' => '0599000000',
            'clinic_email' => 'updated-clinic-en@example.test',
            'clinic_website' => 'https://clinic-en.example.test',
            'clinic_tagline' => 'Quality Dental Care',
        ];

        $updateResponse = $this->postWithCsrf('/settings/updateClinic', $clinicData);
        $this->assertTrue($updateResponse->isRedirect(), 'POST /settings/updateClinic must redirect on success');
        $updateResponse->assertRedirectTo('/settings');

        // Verify database value updated
        $settingsModel = new SettingsModel();
        $savedName = $settingsModel->withoutTenantScope()
            ->where('clinic_id', $clinic)
            ->where('setting_key', 'clinic_name')
            ->first();

        $this->assertNotNull($savedName);
        $this->assertSame('Renamed Test Clinic EN', $savedName['setting_value']);
    }

    // -------------------------------------------------------------------------
    // 13. Ordinary Settings Viewing in Arabic (Explicit Assertions)
    // -------------------------------------------------------------------------

    public function testOrdinarySettingsViewArabicRendersManagedAdvisoryAndNoLegacyControls(): void
    {
        [$user, $clinic] = $this->createTenantFixture(['view', 'edit']);

        $this->withLoggedInSession($user, [
            'active_clinic_id' => $clinic,
            'global_mode' => false,
            'ui_locale' => 'ar',
        ]);

        $response = $this->get('/settings');
        $response->assertStatus(200);

        $body = (string) $response->response()->getBody();

        // Explicit Arabic advisory assertions without fallback
        $arTitle = lang('TenantMisc.backupManagedPlatformTitle', [], 'ar');
        $this->assertNotEmpty($arTitle);
        $this->assertStringContainsString($arTitle, $body, 'Settings view must display the Arabic managed platform backup title');

        $arDesc = lang('TenantMisc.backupManagedPlatformDesc', [], 'ar');
        $this->assertNotEmpty($arDesc);
        $this->assertStringContainsString($arDesc, $body, 'Settings view must display the Arabic managed platform backup description');

        // Verify old backup triggers/forms are NOT present
        $this->assertStringNotContainsString('/settings/create-backup', $body);
        $this->assertStringNotContainsString('/settings/restore', $body);
        $this->assertStringNotContainsString('/settings/download-backup', $body);
        $this->assertStringNotContainsString('downloadBackup(', $body);
        $this->assertStringNotContainsString('restoreBackup(', $body);
    }

    // -------------------------------------------------------------------------
    // Helper Methods
    // -------------------------------------------------------------------------

    private function assertRouteFailsNotFound(callable $action): void
    {
        try {
            $response = $action();
            $response->assertStatus(404);
        } catch (PageNotFoundException $e) {
            $this->assertSame(404, $e->getCode(), 'Expected 404 PageNotFoundException');
        }
    }

    private function assertRouteFailsNotFoundOrBadRequest(callable $action): void
    {
        try {
            $response = $action();
            $this->assertContains($response->getStatusCode(), [400, 404]);
        } catch (PageNotFoundException $e) {
            $this->assertSame(404, $e->getCode(), 'Expected 404 PageNotFoundException');
        } catch (BadRequestException $e) {
            $this->assertSame(400, $e->getCode(), 'Expected 400 BadRequestException for disallowed URI characters');
        }
    }

    private function assertNoDumpBytesInResponse(string $body): void
    {
        $signatures = [
            '-- MySQL dump',
            '-- Host:',
            '-- Table structure for table',
            'CREATE TABLE `',
            'INSERT INTO `',
            '/*!40101 SET @OLD_CHARACTER_SET_CLIENT',
        ];

        foreach ($signatures as $signature) {
            $this->assertStringNotContainsString($signature, $body, 'Response must not contain database dump bytes');
        }
    }

    private function createTenantFixture(array $settingsActions = ['view', 'edit'], string $subscriptionStatus = 'active'): array
    {
        $user = $this->createUser();
        $clinic = $this->createClinic('active');
        $plan = $this->createPlan();
        $role = $this->createRole();

        foreach ($settingsActions as $action) {
            $this->assignPermission($role, $this->permissionId('settings', (string) $action));
        }

        $this->createMembership($user['id'], $clinic, $role);
        $this->createSubscription($clinic, $plan, $subscriptionStatus);

        return [$user, $clinic, $role];
    }

    private function createUser(): array
    {
        $suffix = bin2hex(random_bytes(5));
        $email = 'containment-user-' . $suffix . '@example.test';
        $password = (new IonAuthModel())->hashPassword('secret123', $email);

        $this->db->table('users')->insert([
            'ip_address' => '127.0.0.1',
            'username' => $email,
            'password' => $password,
            'email' => $email,
            'active' => 1,
            'first_name' => 'Containment',
            'last_name' => 'Tester',
            'created_on' => time(),
            'last_login' => time(),
        ]);

        $id = (int) $this->db->insertID();
        $this->createdUsers[] = $id;

        return ['id' => $id, 'email' => $email];
    }

    private function createClinic(string $status = 'active'): int
    {
        $suffix = bin2hex(random_bytes(5));
        $now = date('Y-m-d H:i:s');

        $this->db->table('clinics')->insert([
            'name' => 'Containment Clinic ' . $suffix,
            'slug' => 'containment-clinic-' . $suffix,
            'uuid' => $suffix . '-containment-test',
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
        $slug = 'containment-role-' . bin2hex(random_bytes(5));
        $this->db->table('roles')->insert([
            'name' => $slug,
            'slug' => $slug,
            'description' => 'Tenant backup containment test role',
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
            'name' => 'Containment Plan ' . bin2hex(random_bytes(4)),
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

    private function createSubscription(int $clinicId, int $planId, string $status = 'active'): int
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
            'logged_in',
            'active_clinic_id',
            'active_clinic_role_id',
            'active_clinic_membership_id',
            'user_permissions',
            'ui_locale',
            'is_impersonating',
            'impersonated_clinic_id',
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
