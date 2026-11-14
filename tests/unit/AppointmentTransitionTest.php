<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Appointment;
use App\Models\AppointmentModel;
use App\Services\ActivityLogger;
use App\Services\AppointmentCheckoutCompletionService;
use App\Services\AppointmentVisitStageService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Services;

/**
 * DB-Free Unit Tests for Appointment::transitionAppointment
 *
 * Verifies concurrency safety, conditional writes, row locking contracts,
 * HTTP 409 conflict detection, audit atomicity, and legacy caller support.
 */
final class AppointmentTransitionTest extends CIUnitTestCase
{
    private Appointment $controller;
    private $mockDb;
    private $mockAppointmentModel;
    private $mockActivityLogger;
    private $mockCheckoutCompletionService;
    private AppointmentVisitStageService $visitStageService;

    protected function setUp(): void
    {
        parent::setUp();
        Services::resetSingle('request');
        Services::resetSingle('response');
        Services::resetSingle('redirectresponse');

        $this->mockDb = new class {
            public int $transDepth = 0;
            public bool $transStarted = false;
            public bool $transCommitted = false;
            public bool $transRolledBack = false;

            public function transBegin(): bool
            {
                $this->transDepth++;
                $this->transStarted = true;
                return true;
            }

            public function transCommit(): bool
            {
                $this->transDepth = max(0, $this->transDepth - 1);
                $this->transCommitted = true;
                return true;
            }

            public function transRollback(): bool
            {
                $this->transDepth = max(0, $this->transDepth - 1);
                $this->transRolledBack = true;
                return true;
            }

            public function transStatus(): bool
            {
                return true;
            }
        };

        $this->mockAppointmentModel = $this->createMock(AppointmentModel::class);
        $this->mockActivityLogger = $this->createMock(ActivityLogger::class);
        $this->mockCheckoutCompletionService = $this->createMock(AppointmentCheckoutCompletionService::class);
        $this->visitStageService = new AppointmentVisitStageService();

        $this->controller = new Appointment();
        $this->controller->setDb($this->mockDb);
        $this->controller->setAppointmentModel($this->mockAppointmentModel);
        $this->controller->setActivityLogger($this->mockActivityLogger);
        $this->controller->setVisitStageService($this->visitStageService);
        $this->controller->setCheckoutCompletionService($this->mockCheckoutCompletionService);

        session()->set([
            'user_id' => 42,
            'active_clinic_id' => 7,
        ]);
    }

    protected function tearDown(): void
    {
        session()->destroy();
        Services::resetSingle('request');
        Services::resetSingle('response');
        Services::resetSingle('redirectresponse');
        parent::tearDown();
    }

    private function setupRequest(array $post = [], bool $isAjax = true): IncomingRequest
    {
        $appConfig = config(App::class);
        $uri = new SiteURI($appConfig, 'http://localhost/appointment/10/transition');
        $userAgent = new UserAgent();

        $request = new class($appConfig, $uri, null, $userAgent) extends IncomingRequest {
            public bool $ajaxFlag = true;
            public array $postData = [];

            public function isAJAX(): bool
            {
                return $this->ajaxFlag;
            }

            public function getPost($index = null, $filter = null, $flags = null)
            {
                if ($index === null) {
                    return $this->postData;
                }
                return $this->postData[$index] ?? null;
            }
        };

        $request->ajaxFlag = $isAjax;
        $request->postData = $post;

        $response = new Response($appConfig);
        $this->controller->initController($request, $response, Services::logger());

        return $request;
    }

    public function testMissingTenantContextRedirectsToLogin(): void
    {
        session()->remove('active_clinic_id');
        $this->setupRequest(['status' => 'arrived'], true);

        $response = $this->controller->transition(10);
        $this->assertSame(403, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertSame('TENANT_CONTEXT_REQUIRED', $body['error']);
    }

    public function testConcealmentThrows404ForNonAjaxWhenNotFound(): void
    {
        $this->setupRequest(['status' => 'arrived'], false);

        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn(null);

        $this->expectException(PageNotFoundException::class);
        try {
            $this->controller->transition(10);
        } finally {
            $this->assertTrue($this->mockDb->transRolledBack);
        }
    }

    public function testConcealmentReturns404JsonForAjaxWhenNotFound(): void
    {
        $this->setupRequest(['status' => 'arrived'], true);

        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn(null);

        $response = $this->controller->transition(10);
        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertSame('Appointment not found', $body['message']);
        $this->assertTrue($this->mockDb->transRolledBack);
    }

    public function testInvalidTargetStageReturns422(): void
    {
        $this->setupRequest(['status' => 'invalid_stage'], true);

        $response = $this->controller->transition(10);
        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertSame('This visit stage change is not allowed.', $body['message']);
    }

    public function testInvalidScalarExpectedStatusReturns422(): void
    {
        $this->setupRequest([
            'status' => 'arrived',
            'expected_status' => 'nonexistent_stage',
        ], true);

        $response = $this->controller->transition(10);
        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertSame('Invalid expected stage.', $body['message']);
    }

    public function testStaleExpectedStatusReturns409ConflictForAjax(): void
    {
        $this->setupRequest([
            'status' => 'arrived',
            'expected_status' => 'scheduled',
        ], true);

        // Database actual stage has moved to 'ready'
        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn([
                'id' => 10,
                'clinic_id' => 7,
                'patient_id' => 55,
                'status' => 'ready',
            ]);

        // conditionalStatusUpdate must NOT be called
        $this->mockAppointmentModel->expects($this->never())
            ->method('conditionalStatusUpdate');

        // Audit logger must NOT be called on conflict
        $this->mockActivityLogger->expects($this->never())
            ->method('logAppointmentActivity');

        $response = $this->controller->transition(10);
        $this->assertSame(409, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);

        $this->assertFalse($body['success']);
        $this->assertSame('CONFLICT', $body['error']);
        $this->assertSame('ready', $body['current_status']);
        $this->assertContains('in_chair', $body['allowed_statuses']);
        $this->assertContains('cancelled', $body['allowed_statuses']);
        $this->assertTrue($this->mockDb->transRolledBack);
        $this->assertFalse($this->mockDb->transCommitted);
    }

    public function testDisallowedTransitionReturns422(): void
    {
        $this->setupRequest([
            'status' => 'completed',
            'expected_status' => 'scheduled',
        ], true);

        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn([
                'id' => 10,
                'clinic_id' => 7,
                'patient_id' => 55,
                'status' => 'scheduled',
            ]);

        $this->mockAppointmentModel->expects($this->never())
            ->method('conditionalStatusUpdate');

        $response = $this->controller->transition(10);
        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);

        $this->assertFalse($body['success']);
        $this->assertSame('This visit stage change is not allowed.', $body['message']);
        $this->assertTrue($this->mockDb->transRolledBack);
    }

    public function testAllowedTransitionCommitsAndLogsAudit(): void
    {
        $this->setupRequest([
            'status' => 'arrived',
            'expected_status' => 'scheduled',
        ], true);

        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn([
                'id' => 10,
                'clinic_id' => 7,
                'patient_id' => 55,
                'status' => 'scheduled',
            ]);

        $this->mockAppointmentModel->expects($this->once())
            ->method('conditionalStatusUpdate')
            ->with(7, 10, 'scheduled', 'arrived')
            ->willReturn(true);

        $this->mockActivityLogger->expects($this->once())
            ->method('logAppointmentActivity')
            ->with(
                'status',
                10,
                $this->stringContains('Appointment visit stage changed from scheduled to arrived')
            )
            ->willReturn(true);

        $response = $this->controller->transition(10);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);

        $this->assertTrue($body['success']);
        $this->assertSame('arrived', $body['current_status']);
        $this->assertContains('ready', $body['allowed_statuses']);
        $this->assertTrue($this->mockDb->transCommitted);
        $this->assertFalse($this->mockDb->transRolledBack);
    }

    public function testLegacyCallerWithoutExpectedStatusSucceeds(): void
    {
        // expected_status is omitted
        $this->setupRequest([
            'status' => 'arrived',
        ], true);

        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn([
                'id' => 10,
                'clinic_id' => 7,
                'patient_id' => 55,
                'status' => 'scheduled',
            ]);

        $this->mockAppointmentModel->expects($this->once())
            ->method('conditionalStatusUpdate')
            ->with(7, 10, 'scheduled', 'arrived')
            ->willReturn(true);

        $this->mockActivityLogger->expects($this->once())
            ->method('logAppointmentActivity')
            ->willReturn(true);

        $response = $this->controller->transition(10);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);

        $this->assertTrue($body['success']);
        $this->assertSame('arrived', $body['current_status']);
        $this->assertTrue($this->mockDb->transCommitted);
    }

    public function testConditionalStatusUpdateFailureCausesRollbackAnd500(): void
    {
        $this->setupRequest([
            'status' => 'arrived',
            'expected_status' => 'scheduled',
        ], true);

        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn([
                'id' => 10,
                'clinic_id' => 7,
                'patient_id' => 55,
                'status' => 'scheduled',
            ]);

        // Concurrent modification caused conditionalStatusUpdate to affect 0 rows
        $this->mockAppointmentModel->expects($this->once())
            ->method('conditionalStatusUpdate')
            ->willReturn(false);

        $this->mockActivityLogger->expects($this->never())
            ->method('logAppointmentActivity');

        $response = $this->controller->transition(10);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertTrue($this->mockDb->transRolledBack);
        $this->assertFalse($this->mockDb->transCommitted);
    }

    public function testAuditLogFailureCausesRollbackAnd500(): void
    {
        $this->setupRequest([
            'status' => 'arrived',
            'expected_status' => 'scheduled',
        ], true);

        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn([
                'id' => 10,
                'clinic_id' => 7,
                'patient_id' => 55,
                'status' => 'scheduled',
            ]);

        $this->mockAppointmentModel->expects($this->once())
            ->method('conditionalStatusUpdate')
            ->willReturn(true);

        // Audit logger returns false/null indicating failure
        $this->mockActivityLogger->expects($this->once())
            ->method('logAppointmentActivity')
            ->willReturn(false);

        $response = $this->controller->transition(10);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertTrue($this->mockDb->transRolledBack);
        $this->assertFalse($this->mockDb->transCommitted);
    }

    public function testCheckoutCompletionOverrideRequiredForCompleted(): void
    {
        $this->setupRequest([
            'status' => 'completed',
            'expected_status' => 'checkout',
        ], true);

        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn([
                'id' => 10,
                'clinic_id' => 7,
                'patient_id' => 55,
                'status' => 'checkout',
            ]);

        $this->mockCheckoutCompletionService->expects($this->once())
            ->method('assess')
            ->with(7, 10)
            ->willReturn([
                'requires_override' => true,
                'issue_codes' => ['balance_due'],
            ]);

        $this->mockCheckoutCompletionService->expects($this->once())
            ->method('authorize')
            ->willThrowException(new \DomainException('Override required to complete appointment.'));

        $this->mockAppointmentModel->expects($this->never())
            ->method('conditionalStatusUpdate');

        $response = $this->controller->transition(10);
        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);

        $this->assertFalse($body['success']);
        $this->assertTrue($body['requires_override']);
        $this->assertSame(['balance_due'], $body['checkout_issues']);
        $this->assertTrue($this->mockDb->transRolledBack);
    }

    public function testCheckoutCompletionWithValidOverrideSucceeds(): void
    {
        $this->setupRequest([
            'status' => 'completed',
            'expected_status' => 'checkout',
            'checkout_override' => '1',
            'checkout_override_reason' => 'Patient promised to pay tomorrow',
        ], true);

        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn([
                'id' => 10,
                'clinic_id' => 7,
                'patient_id' => 55,
                'status' => 'checkout',
            ]);

        $this->mockCheckoutCompletionService->expects($this->once())
            ->method('assess')
            ->with(7, 10)
            ->willReturn([
                'requires_override' => true,
                'issue_codes' => ['balance_due'],
            ]);

        $this->mockCheckoutCompletionService->expects($this->once())
            ->method('authorize')
            ->willReturn([
                'override' => true,
                'reason' => 'Patient promised to pay tomorrow',
                'issue_codes' => ['balance_due'],
            ]);

        $this->mockAppointmentModel->expects($this->once())
            ->method('conditionalStatusUpdate')
            ->with(7, 10, 'checkout', 'completed')
            ->willReturn(true);

        $this->mockActivityLogger->expects($this->once())
            ->method('logAppointmentActivity')
            ->with(
                'status',
                10,
                $this->stringContains('checkout acknowledged [balance_due]')
            )
            ->willReturn(true);

        $response = $this->controller->transition(10);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($this->mockDb->transCommitted);
    }

    public function testNonAjaxRedirectSanitizesReturnToDashboard(): void
    {
        $this->setupRequest([
            'status' => 'arrived',
            'expected_status' => 'scheduled',
            'return_to' => 'dashboard',
        ], false);

        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn([
                'id' => 10,
                'clinic_id' => 7,
                'patient_id' => 55,
                'status' => 'scheduled',
            ]);

        $this->mockAppointmentModel->expects($this->once())
            ->method('conditionalStatusUpdate')
            ->willReturn(true);

        $this->mockActivityLogger->expects($this->once())
            ->method('logAppointmentActivity')
            ->willReturn(true);

        $response = $this->controller->transition(10);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/dashboard', $response->getHeaderLine('Location'));
    }
}
