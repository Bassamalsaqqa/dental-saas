<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Api\Search as ApiSearch;
use App\Filters\PermissionFilter;
use App\Models\PatientModel;
use App\Services\PermissionService;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use DOMDocument;
use DOMXPath;

/**
 * Tests template permissions, registered filter declarations and controller/filter units.
 *
 * DB-free presenters and mocks; no routed request, SQL isolation or CSRF acceptance.
 */
final class PatientWorkflowBoundaryTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::resetSingle('permission');
        Services::resetSingle('routes');
        Services::resetSingle('request');
        Services::resetSingle('response');
        Services::resetSingle('redirectresponse');
        session()->destroy();
        parent::tearDown();
    }

    private function renderPresenter(string $locale, string $mode, string $canView, string $canCreate): DOMDocument
    {
        $fixture = dirname(__DIR__) . '/_support/fixtures/RegistrationSearchPresenter.php';
        $cmd = escapeshellarg(PHP_BINARY) . ' '
            . escapeshellarg($fixture) . ' '
            . escapeshellarg($locale) . ' '
            . escapeshellarg($mode) . ' '
            . escapeshellarg($canView) . ' '
            . escapeshellarg($canCreate);

        $html = shell_exec($cmd);
        self::assertIsString($html, 'Presenter fixture execution must succeed');
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        return $dom;
    }

    public function testPatientCreateShellWithViewAndCreateShowsGlobalDialogAndSearchTrigger(): void
    {
        foreach (['en', 'ar'] as $locale) {
            $dom = $this->renderPresenter($locale, 'fullshell', 'allowed', 'allowed');
            $xp = new DOMXPath($dom);

            // Global search dialog exists in layout shell
            self::assertSame(1, $xp->query('//dialog[@id="search-dialog"]')->length);

            // Shell top search trigger exists
            self::assertSame(1, $xp->query('//button[contains(@class,"search-trigger") and @data-dialog="search-dialog"]')->length);

            // Patient form search trigger exists in patient identity header
            self::assertSame(1, $xp->query('//h2[@id="patient-basics"]/..//button[@data-dialog="search-dialog"]')->length);
        }
    }

    public function testPatientCreateShellWithCreateOnlyNoViewHidesGlobalDialogAndSearchTrigger(): void
    {
        foreach (['en', 'ar'] as $locale) {
            $dom = $this->renderPresenter($locale, 'fullshell', 'denied', 'allowed');
            $xp = new DOMXPath($dom);

            // Global search dialog is absent from layout shell
            self::assertSame(0, $xp->query('//dialog[@id="search-dialog"]')->length);

            // Shell top search trigger is absent
            self::assertSame(0, $xp->query('//button[contains(@class,"search-trigger") and @data-dialog="search-dialog"]')->length);

            // Patient form search trigger is absent when user lacks view permission
            self::assertSame(0, $xp->query('//h2[@id="patient-basics"]/..//button[@data-dialog="search-dialog"]')->length);

            // Patient create form inputs remain available for creation
            self::assertSame(1, $xp->query('//input[@name="first_name"]')->length);
            self::assertSame(1, $xp->query('//input[@name="last_name"]')->length);
        }
    }

    public function testPatientEditModeOmitsSearchTriggerEvenWhenAuthorizedToViewAndCreate(): void
    {
        foreach (['en', 'ar'] as $locale) {
            $dom = $this->renderPresenter($locale, 'fullshell-edit', 'allowed', 'allowed');
            $xp = new DOMXPath($dom);

            // Layout still has global search dialog
            self::assertSame(1, $xp->query('//dialog[@id="search-dialog"]')->length);

            // But the form itself does NOT have the search trigger in edit mode
            self::assertSame(0, $xp->query('//h2[@id="patient-basics"]/..//button[@data-dialog="search-dialog"]')->length);
        }
    }

    public function testQuickIntakeContextRespectsViewAndCreatePermissions(): void
    {
        // View + Create: Intake dialog is rendered AND form search trigger is present
        $domAllowed = $this->renderPresenter('en', 'dialog', 'allowed', 'allowed');
        $xpAllowed = new DOMXPath($domAllowed);
        self::assertSame(1, $xpAllowed->query('//dialog[@id="patient-intake-dialog"]')->length);
        self::assertSame(1, $xpAllowed->query('//dialog[@id="patient-intake-dialog"]//button[@data-dialog="search-dialog"]')->length);

        // Create-only / No-view: Intake dialog is rendered, but search trigger is strictly absent
        $domNoView = $this->renderPresenter('en', 'dialog', 'denied', 'allowed');
        $xpNoView = new DOMXPath($domNoView);
        self::assertSame(1, $xpNoView->query('//dialog[@id="patient-intake-dialog"]')->length);
        self::assertSame(0, $xpNoView->query('//dialog[@id="patient-intake-dialog"]//button[@data-dialog="search-dialog"]')->length);

        // View-only / No-create: Intake dialog is completely absent
        $domNoCreate = $this->renderPresenter('en', 'dialog', 'allowed', 'denied');
        $xpNoCreate = new DOMXPath($domNoCreate);
        self::assertSame(0, $xpNoCreate->query('//dialog[@id="patient-intake-dialog"]')->length);
    }

    public function testActualRouteDeclarationsEnforceRequiredPermissions(): void
    {
        $routes = service('routes')->loadRoutes();

        // 1. /api/search/patients requires permission:patients,view
        $apiSearchFilter = $this->findRouteFilter($routes, 'api/search/patients', 'GET');
        self::assertNotNull($apiSearchFilter, 'api/search/patients route must exist');
        self::assertContains('permission:patients,view', (array) $apiSearchFilter);

        // 2. /patient/create and /patients/create require permission:patients,create
        $patientCreateFilter = $this->findRouteFilter($routes, 'patient/create', 'GET');
        self::assertNotNull($patientCreateFilter, 'patient/create route must exist');
        self::assertContains('permission:patients,create', (array) $patientCreateFilter);

        // 3. /treatment/create and /treatment/store require permission:treatments,create
        $treatmentCreateFilter = $this->findRouteFilter($routes, 'treatment/create', 'GET');
        self::assertNotNull($treatmentCreateFilter, 'treatment/create route must exist');
        self::assertContains('permission:treatments,create', (array) $treatmentCreateFilter);

        $treatmentStoreFilter = $this->findRouteFilter($routes, 'treatment/store', 'POST');
        self::assertNotNull($treatmentStoreFilter, 'treatment/store route must exist');
        self::assertContains('permission:treatments,create', (array) $treatmentStoreFilter);
    }

    private function findRouteFilter($routes, string $path, string $method)
    {
        foreach ($routes->getRoutes($method) as $pattern => $handler) {
            if ($pattern === $path || $pattern === '/' . $path) {
                $opts = $routes->getRoutesOptions($pattern, $method);
                return $opts['filter'] ?? null;
            }
        }
        return null;
    }

    public function testPermissionFilterRejectsDeniedRolesForApiAndWeb(): void
    {
        $serviceRequest = new IncomingRequest(config('App'), new \CodeIgniter\HTTP\SiteURI(config('App')), null, new \CodeIgniter\HTTP\UserAgent());
        $serviceRequest->setProtocolVersion('1.1');
        Services::injectMock('request', $serviceRequest);
        Services::resetSingle('response');
        Services::resetSingle('redirectresponse');
        $spy = new class extends PermissionService {
            public bool $allowView = false;
            public bool $allowCreate = false;
            public function __construct() {}
            public function hasPermission($userId, $module, $action)
            {
                if ($action === 'view') return $this->allowView;
                if ($action === 'create') return $this->allowCreate;
                return false;
            }
        };
        Services::injectMock('permission', $spy);

        // Set session user with identity and last_check so IonAuth::loggedIn() succeeds
        session()->set([
            'user_id' => 10,
            'identity' => 'operator@denta.local',
            'last_check' => time(),
            'active_clinic_id' => 1
        ]);

        // 1. API request to /api/search/patients with view permission denied -> 403 JSON
        $apiUri = new URI('http://example.com/api/search/patients');
        $apiRequest = $this->createMock(IncomingRequest::class);
        $apiRequest->method('getUri')->willReturn($apiUri);
        $apiRequest->method('hasHeader')->willReturnCallback(static fn($h) => strtolower($h) === 'accept');
        $apiRequest->method('getHeaderLine')->willReturnCallback(static fn($h) => strtolower($h) === 'accept' ? 'application/json' : '');

        $filter = new PermissionFilter();
        $response = $filter->before($apiRequest, ['patients', 'view']);

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(403, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        self::assertSame('forbidden', $body['error']['code'] ?? null);

        // 2. Web request to /treatment/create with create permission denied -> Redirect with error
        $webUri = new URI('http://example.com/treatment/create');
        $webRequest = $this->createMock(IncomingRequest::class);
        $webRequest->method('getUri')->willReturn($webUri);
        $webRequest->method('hasHeader')->willReturn(false);
        $webRequest->method('getHeaderLine')->willReturn('');

        $webResponse = $filter->before($webRequest, ['treatments', 'create']);
        self::assertTrue($webResponse instanceof \CodeIgniter\HTTP\RedirectResponse || ($webResponse instanceof Response && $webResponse->isRedirect()));
    }

    public function testPatientSearchForwardsActiveClinicAndRejectsMissingContext(): void
    {
        $patientModel = $this->createMock(PatientModel::class);
        $patientModel->expects(self::once())->method('searchPatientsByClinic')
            ->with(1, 'Patient', 20, 0)->willReturn([
                ['id' => 101, 'first_name' => 'ClinicOne', 'last_name' => 'Patient', 'phone' => '0599000001'],
            ]);
        $patientModel->expects(self::once())->method('countSearchPatientsByClinic')
            ->with(1, 'Patient')->willReturn(1);

        $controller = new ApiSearch();
        $refPatientModel = new \ReflectionProperty(ApiSearch::class, 'patientModel');
        $refPatientModel->setValue($controller, $patientModel);

        $request = $this->createMock(IncomingRequest::class);
        $request->method('getGet')->willReturnCallback(static fn($k = null) => match($k) {
            'q' => 'Patient',
            'page' => 1,
            'limit' => 20,
            default => null,
        });
        $refRequest = new \ReflectionProperty(ApiSearch::class, 'request');
        $refRequest->setValue($controller, $request);

        $response = new Response(config('App'));
        $refResponse = new \ReflectionProperty(ApiSearch::class, 'response');
        $refResponse->setValue($controller, $response);

        // Scenario A: Active clinic is 1
        session()->set(['active_clinic_id' => 1]);
        $res1 = $controller->patients();
        self::assertSame(200, $res1->getStatusCode());
        $data1 = json_decode($res1->getBody(), true);
        self::assertCount(1, $data1['results']);
        self::assertSame(101, $data1['results'][0]['id']);
        self::assertStringContainsString('ClinicOne', $data1['results'][0]['text']);

        // Scenario B: Active clinic is missing / 0 -> 403 tenant_context_forbidden
        session()->set(['active_clinic_id' => 0]);
        $resForbidden = $controller->patients();
        self::assertSame(403, $resForbidden->getStatusCode());
        $dataForbidden = json_decode($resForbidden->getBody(), true);
        self::assertSame('tenant_context_forbidden', $dataForbidden['error']['code']);
    }

}
