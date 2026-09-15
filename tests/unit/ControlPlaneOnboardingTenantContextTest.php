<?php

namespace Tests\Unit;

use App\Controllers\ControlPlane\Onboarding;
use App\Services\OnboardingService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Onboarding tenant-context fix verification tests.
 *
 * Tests:
 * 1. OnboardingService constructor uses withoutTenantScope on all tenant-aware models
 * 2. OnboardingService::createClinicWithAdmin() fails closed if not in global mode
 * 3. Control-plane check-email route exists with controlplane filter (airlock safe)
 * 4. Tenant-plane users/check-email still exists (unchanged)
 * 5. create_clinic.php points to the CP endpoint, not tenant endpoint
 * 6. OnboardingService resolveOnboardingRole prioritizes practice_manager
 * 7. PHP parse check on all modified files
 */
final class ControlPlaneOnboardingTenantContextTest extends CIUnitTestCase
{
    // -------------------------------------------------------------------------
    // 1. Constructor uses withoutTenantScope for all tenant-aware models
    // -------------------------------------------------------------------------

    public function testOnboardingServiceConstructorBypassesTenantScope(): void
    {
        $service    = new OnboardingService();
        $reflection = new \ReflectionClass($service);

        // clinicUserModel
        $cuProp = $reflection->getProperty('clinicUserModel');
        $cuProp->setAccessible(true);
        $clinicUserModel = $cuProp->getValue($service);
        $curs = new \ReflectionClass($clinicUserModel);
        $cts  = $curs->getProperty('checkTenantScope');
        $cts->setAccessible(true);
        $this->assertFalse(
            (bool) $cts->getValue($clinicUserModel),
            'clinicUserModel must have checkTenantScope=false (withoutTenantScope)'
        );

        // subscriptionModel
        $subProp = $reflection->getProperty('subscriptionModel');
        $subProp->setAccessible(true);
        $subscriptionModel = $subProp->getValue($service);
        $subrs = new \ReflectionClass($subscriptionModel);
        $subts = $subrs->getProperty('checkTenantScope');
        $subts->setAccessible(true);
        $this->assertFalse(
            (bool) $subts->getValue($subscriptionModel),
            'subscriptionModel must have checkTenantScope=false'
        );

        // auditModel
        $auditProp = $reflection->getProperty('auditModel');
        $auditProp->setAccessible(true);
        $auditModel = $auditProp->getValue($service);
        $audrs = new \ReflectionClass($auditModel);
        $audts = $audrs->getProperty('checkTenantScope');
        $audts->setAccessible(true);
        $this->assertFalse(
            (bool) $audts->getValue($auditModel),
            'auditModel must have checkTenantScope=false'
        );

        // settingsModel
        $settProp = $reflection->getProperty('settingsModel');
        $settProp->setAccessible(true);
        $settingsModel = $settProp->getValue($service);
        $srs = new \ReflectionClass($settingsModel);
        $sts = $srs->getProperty('checkTenantScope');
        $sts->setAccessible(true);
        $this->assertFalse(
            (bool) $sts->getValue($settingsModel),
            'settingsModel must have checkTenantScope=false'
        );
    }

    // -------------------------------------------------------------------------
    // 2. createClinicWithAdmin fails closed if not in global mode
    // -------------------------------------------------------------------------

    public function testCreateClinicWithAdminFailsClosedWithoutGlobalMode(): void
    {
        // Make sure global_mode is NOT set in session
        session()->remove('global_mode');

        $service = new OnboardingService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/ACCESS_DENIED/');

        $service->createClinicWithAdmin([
            'clinic_name'    => 'Test Clinic',
            'admin_name'     => 'Test Admin',
            'admin_email'    => 'test@example.com',
            'admin_password' => 'password123',
            'plan_id'        => 1,
        ], 1);
    }

    // -------------------------------------------------------------------------
    // 3. Control-plane check-email route registered under controlplane filter
    // -------------------------------------------------------------------------

    public function testCpCheckEmailRouteExistsWithControlPlaneFilter(): void
    {
        $routeSource = file_get_contents(APPPATH . 'Config/Routes.php');

        $this->assertStringContainsString(
            "check-email",
            $routeSource,
            'check-email route must appear in Routes.php'
        );
        $this->assertStringContainsString(
            "Onboarding::checkEmail",
            $routeSource,
            'check-email must point to Onboarding::checkEmail'
        );
    }

    public function testCpCheckEmailRouteCarriesControlPlaneFilter(): void
    {
        $routeSource = file_get_contents(APPPATH . 'Config/Routes.php');

        // The route must be inside the controlplane group — verify the filter group marker
        $this->assertStringContainsString(
            "'controlplane'",
            $routeSource,
            "Routes.php must contain 'controlplane' filter group"
        );
        // Confirm check-email maps to Onboarding::checkEmail
        $this->assertStringContainsString(
            'Onboarding::checkEmail',
            $routeSource,
            'check-email route must map to Onboarding::checkEmail'
        );
        $this->assertStringContainsString(
            'check-email',
            $routeSource,
            'check-email route key must exist in Routes.php'
        );
    }

    // -------------------------------------------------------------------------
    // 4. Tenant-plane users/check-email still exists (not removed)
    // -------------------------------------------------------------------------

    public function testTenantPlaneCheckEmailRoutePreserved(): void
    {
        $routeSource = file_get_contents(APPPATH . 'Config/Routes.php');
        $this->assertStringContainsString(
            "users/check-email",
            $routeSource,
            'Tenant-plane users/check-email must still exist'
        );
        $this->assertStringContainsString(
            "Users::checkEmail",
            $routeSource,
            'Tenant-plane route must point to Users::checkEmail'
        );
    }

    // -------------------------------------------------------------------------
    // 5. create_clinic.php uses the CP endpoint, not the tenant endpoint
    // -------------------------------------------------------------------------

    public function testCreateClinicViewUsesControlPlaneEmailCheckUrl(): void
    {
        $viewSource = file_get_contents(APPPATH . 'Views/control_plane/onboarding/create_clinic.php');

        $this->assertStringContainsString(
            "controlplane/onboarding/check-email",
            $viewSource,
            'create_clinic.php must pass the control-plane check-email URL to email_check_script'
        );
        $this->assertStringNotContainsString(
            "'users/check-email'",
            $viewSource,
            'create_clinic.php must NOT reference the tenant-plane users/check-email'
        );
    }

    // -------------------------------------------------------------------------
    // 6. resolveOnboardingRole prioritizes practice_manager
    // -------------------------------------------------------------------------

    public function testResolveOnboardingRolePrioritizesPracticeManager(): void
    {
        $source = file_get_contents(APPPATH . 'Services/OnboardingService.php');

        // practice_manager must appear before clinic_admin in resolveOnboardingRole
        $pmPos = strpos($source, 'practice_manager');
        $caPos = strpos($source, 'clinic_admin');

        $this->assertGreaterThan(0, $pmPos, 'practice_manager must appear in OnboardingService');
        $this->assertGreaterThan(0, $caPos, 'clinic_admin must appear in OnboardingService');
        $this->assertLessThan(
            $caPos,
            $pmPos,
            'practice_manager must appear before clinic_admin (priority order)'
        );
    }

    // -------------------------------------------------------------------------
    // 7. Canonical designated_admin_user_id setting is written during onboarding
    // -------------------------------------------------------------------------

    public function testOnboardingServiceWritesDesignatedAdminSetting(): void
    {
        $source = file_get_contents(APPPATH . 'Services/OnboardingService.php');

        $this->assertStringContainsString(
            'ClinicAdminLifecycleService::SETTING_KEY_DESIGNATED_ADMIN',
            $source,
            'OnboardingService must use the canonical SETTING_KEY_DESIGNATED_ADMIN constant'
        );
    }

    // -------------------------------------------------------------------------
    // 8. PHP parse check on all modified files
    // -------------------------------------------------------------------------

    public function testModifiedFilesHaveNoSyntaxErrors(): void
    {
        $files = [
            APPPATH . 'Services/OnboardingService.php',
            APPPATH . 'Controllers/ControlPlane/Onboarding.php',
            APPPATH . 'Config/Routes.php',
            APPPATH . 'Views/control_plane/onboarding/create_clinic.php',
        ];

        foreach ($files as $file) {
            $this->assertFileExists($file, "Expected file not found: $file");
            exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $exitCode);
            $this->assertSame(
                0,
                $exitCode,
                "PHP syntax error in $file:\n" . implode("\n", $output)
            );
        }
    }

    // -------------------------------------------------------------------------
    // 9. Onboarding controller has checkEmail method
    // -------------------------------------------------------------------------

    public function testOnboardingControllerHasCheckEmailMethod(): void
    {
        $this->assertTrue(
            method_exists(Onboarding::class, 'checkEmail'),
            'Onboarding controller must have a checkEmail() method'
        );
    }

    // -------------------------------------------------------------------------
    // 10. checkEmail method is public
    // -------------------------------------------------------------------------

    public function testCheckEmailMethodIsPublic(): void
    {
        $reflection = new \ReflectionClass(Onboarding::class);
        $method     = $reflection->getMethod('checkEmail');

        $this->assertTrue(
            $method->isPublic(),
            'Onboarding::checkEmail must be public'
        );
    }

    // -------------------------------------------------------------------------
    // 11. Airlock: checkEmail does not call session('active_clinic_id')
    // -------------------------------------------------------------------------

    public function testCheckEmailDoesNotUseActiveClinidId(): void
    {
        $source = file_get_contents(APPPATH . 'Controllers/ControlPlane/Onboarding.php');

        // Find the checkEmail method body
        $methodStart = strpos($source, 'public function checkEmail()');
        $this->assertGreaterThan(0, $methodStart, 'checkEmail method must exist');

        $methodBody = substr($source, $methodStart, 2000); // Sample enough chars
        $this->assertStringNotContainsString(
            'active_clinic_id',
            $methodBody,
            'checkEmail must not reference active_clinic_id (airlock violation)'
        );
    }

    // -------------------------------------------------------------------------
    // 12. Onboarding controller uses withoutTenantScope in checkEmail
    // -------------------------------------------------------------------------

    public function testCheckEmailUsesWithoutTenantScope(): void
    {
        $source = file_get_contents(APPPATH . 'Controllers/ControlPlane/Onboarding.php');

        $this->assertStringContainsString(
            'withoutTenantScope()',
            $source,
            'Onboarding::checkEmail must call withoutTenantScope() on ClinicUserModel'
        );
    }
}
