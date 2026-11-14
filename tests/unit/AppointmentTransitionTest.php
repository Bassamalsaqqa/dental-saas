<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Appointment;
use App\Models\AppointmentModel;
use App\Services\ActivityLogger;
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
 * Test-side subclass for DB-free unit testing of Appointment controller
 */
class TestableAppointment extends Appointment
{
    public ?array $mockAssessment = null;
    public ?array $mockCompletionDecision = null;
    public ?\Throwable $mockCompletionException = null;

    protected function assessCheckoutCompletion(int $clinicId, int $id): array
    {
        return $this->mockAssessment ?? parent::assessCheckoutCompletion($clinicId, $id);
    }

    protected function authorizeCheckoutCompletion(array $assessment, bool $override, ?string $reason): array
    {
        if ($this->mockCompletionException !== null) {
            throw $this->mockCompletionException;
        }
        return $this->mockCompletionDecision ?? parent::authorizeCheckoutCompletion($assessment, $override, $reason);
    }
}

/**
 * DB-Free Unit Tests for Appointment::transition
 *
 * Verifies concurrency safety, conditional writes, row locking contracts,
 * HTTP 409 conflict detection, audit atomicity, and legacy caller support.
 */
final class AppointmentTransitionTest extends CIUnitTestCase
{
    private TestableAppointment $controller;
    private $mockDb;
    private $mockAppointmentModel;
    private $mockActivityLogger;
    private AppointmentVisitStageService $visitStageService;

    private function setProtected(string $property, $value): void
    {
        $ref = new \ReflectionProperty(Appointment::class, $property);
        $ref->setValue($this->controller, $value);
    }

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
        $this->visitStageService = new AppointmentVisitStageService();

        $this->controller = new TestableAppointment();
        $this->setProtected('db', $this->mockDb);
        $this->setProtected('appointmentModel', $this->mockAppointmentModel);
        $this->setProtected('activityLogger', $this->mockActivityLogger);
        $this->setProtected('visitStageService', $this->visitStageService);

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

    public function testMissingTenantContextReturns403ForBothAjaxAndNonAjax(): void
    {
        session()->remove('active_clinic_id');
        $this->setupRequest(['status' => 'arrived'], true);

        $response = $this->controller->transition(10);
        $this->assertSame(403, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertSame('TENANT_CONTEXT_REQUIRED', $body['error']);

        $this->setupRequest(['status' => 'arrived'], false);
        $responseNonAjax = $this->controller->transition(10);
        $this->assertSame(403, $responseNonAjax->getStatusCode());
        $bodyNonAjax = json_decode($responseNonAjax->getBody(), true);
        $this->assertSame('TENANT_CONTEXT_REQUIRED', $bodyNonAjax['error']);
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

    public function testFindForUpdateDatabaseQueryFailureRollsBackAndReturns500(): void
    {
        $this->setupRequest(['status' => 'arrived'], true);

        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willThrowException(new \RuntimeException('Database query failed during row lock selection'));

        $response = $this->controller->transition(10);
        $this->assertSame(500, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertSame('An error occurred during visit stage transition.', $body['message']);
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

    public function testNonScalarArrayTargetStatusReturns422WithoutNotice(): void
    {
        $this->setupRequest(['status' => ['invalid' => 'array']], true);

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

    public function testStaleExpectedStatusReturns409ConflictWithAllowedNextStages(): void
    {
        $this->setupRequest([
            'status' => 'arrived',
            'expected_status' => 'scheduled',
        ], true);

        // Actual DB row is already 'arrived' (modified concurrently)
        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn([
                'id' => 10,
                'clinic_id' => 7,
                'patient_id' => 55,
                'status' => 'arrived',
            ]);

        // Must NOT attempt conditional update or audit log
        $this->mockAppointmentModel->expects($this->never())
            ->method('conditionalStatusUpdate');
        $this->mockActivityLogger->expects($this->never())
            ->method('logAppointmentActivity');

        $response = $this->controller->transition(10);
        $this->assertSame(409, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);

        $this->assertFalse($body['success']);
        $this->assertSame('CONFLICT', $body['error']);
        $this->assertSame('The appointment status has changed since the page was loaded.', $body['message']);
        $this->assertSame('arrived', $body['current_status']);
        $this->assertSame(['ready', 'in_chair', 'cancelled'], $body['allowed_statuses']);
        $this->assertTrue($this->mockDb->transRolledBack);
    }

    public function testIllegalTransitionReturns422(): void
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
        $this->assertSame('scheduled', $body['current_status']);
        $this->assertSame(['confirmed', 'arrived', 'cancelled', 'no_show'], $body['allowed_statuses']);
        $this->assertTrue($this->mockDb->transRolledBack);
    }

    public function testLegacyCallerWithoutExpectedStatusUsesActualDbStatusConditionally(): void
    {
        $this->setupRequest([
            'status' => 'arrived',
            // No expected_status parameter
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
            ->with('status', 10, 'Appointment visit stage changed from scheduled to arrived', null)
            ->willReturn(true);

        $response = $this->controller->transition(10);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);

        $this->assertTrue($body['success']);
        $this->assertSame('arrived', $body['status']);
        $this->assertSame('arrived', $body['current_status']);
        $this->assertSame(['ready', 'in_chair', 'cancelled'], $body['allowed_statuses']);
        $this->assertTrue($this->mockDb->transCommitted);
    }

    public function testSuccessfulTransitionCommitsAtomically(): void
    {
        $this->setupRequest([
            'status' => 'ready',
            'expected_status' => 'arrived',
        ], true);

        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn([
                'id' => 10,
                'clinic_id' => 7,
                'patient_id' => 55,
                'status' => 'arrived',
            ]);

        $this->mockAppointmentModel->expects($this->once())
            ->method('conditionalStatusUpdate')
            ->with(7, 10, 'arrived', 'ready')
            ->willReturn(true);

        $this->mockActivityLogger->expects($this->once())
            ->method('logAppointmentActivity')
            ->with('status', 10, 'Appointment visit stage changed from arrived to ready', null)
            ->willReturn(true);

        $response = $this->controller->transition(10);
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);

        $this->assertTrue($body['success']);
        $this->assertSame('ready', $body['status']);
        $this->assertTrue($this->mockDb->transCommitted);
    }

    public function testConditionalUpdateFailureRollsBackTransaction(): void
    {
        $this->setupRequest([
            'status' => 'ready',
            'expected_status' => 'arrived',
        ], true);

        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn([
                'id' => 10,
                'clinic_id' => 7,
                'patient_id' => 55,
                'status' => 'arrived',
            ]);

        // Simulating conditionalStatusUpdate returning false (e.g. 0 affected rows or query failure)
        $this->mockAppointmentModel->expects($this->once())
            ->method('conditionalStatusUpdate')
            ->with(7, 10, 'arrived', 'ready')
            ->willReturn(false);

        $this->mockActivityLogger->expects($this->never())
            ->method('logAppointmentActivity');

        $response = $this->controller->transition(10);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertTrue($this->mockDb->transRolledBack);
        $this->assertFalse($this->mockDb->transCommitted);
    }

    public function testAuditLogFailureRollsBackTransaction(): void
    {
        $this->setupRequest([
            'status' => 'ready',
            'expected_status' => 'arrived',
        ], true);

        $this->mockAppointmentModel->expects($this->once())
            ->method('findForUpdateByClinic')
            ->with(7, 10)
            ->willReturn([
                'id' => 10,
                'clinic_id' => 7,
                'patient_id' => 55,
                'status' => 'arrived',
            ]);

        $this->mockAppointmentModel->expects($this->once())
            ->method('conditionalStatusUpdate')
            ->with(7, 10, 'arrived', 'ready')
            ->willReturn(true);

        $this->mockActivityLogger->expects($this->once())
            ->method('logAppointmentActivity')
            ->willReturn(false);

        $response = $this->controller->transition(10);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertTrue($this->mockDb->transRolledBack);
        $this->assertFalse($this->mockDb->transCommitted);
    }

    public function testCheckoutCompletionWithoutRequiredOverrideFails(): void
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

        $this->controller->mockAssessment = [
            'requires_override' => true,
            'issue_codes' => ['balance_due'],
        ];
        $this->controller->mockCompletionException = new \DomainException('Override required to complete appointment.');

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

        $this->controller->mockAssessment = [
            'requires_override' => true,
            'issue_codes' => ['balance_due'],
        ];
        $this->controller->mockCompletionDecision = [
            'override' => true,
            'reason' => 'Patient promised to pay tomorrow',
            'issue_codes' => ['balance_due'],
        ];

        $this->mockAppointmentModel->expects($this->once())
            ->method('conditionalStatusUpdate')
            ->with(7, 10, 'checkout', 'completed')
            ->willReturn(true);

        $this->mockActivityLogger->expects($this->once())
            ->method('logAppointmentActivity')
            ->with(
                'status',
                10,
                $this->stringContains('checkout acknowledged [balance_due]'),
                $this->callback(function ($metadata): bool {
                    $this->assertIsArray($metadata);
                    $this->assertTrue($metadata['checkout_override']);
                    $this->assertSame('Patient promised to pay tomorrow', $metadata['reason']);
                    return true;
                })
            )
            ->willReturn(true);

        $response = $this->controller->transition(10);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($this->mockDb->transCommitted);
    }

    public function testLongCheckoutOverrideReasonPreservedInAuditMetadataAndSummaryWithoutLosingIssues(): void
    {
        $longReason = str_repeat('Clinical treatment plan verified with patient; payment scheduled. ', 5); // 325 characters
        $this->assertGreaterThan(255, mb_strlen($longReason));
        $this->assertLessThanOrEqual(500, mb_strlen($longReason));

        $this->setupRequest([
            'status' => 'completed',
            'expected_status' => 'checkout',
            'checkout_override' => '1',
            'checkout_override_reason' => $longReason,
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

        $this->controller->mockAssessment = [
            'requires_override' => true,
            'issue_codes' => ['balance_due'],
        ];
        $this->controller->mockCompletionDecision = [
            'override' => true,
            'reason' => $longReason,
            'issue_codes' => ['balance_due'],
        ];

        $this->mockAppointmentModel->expects($this->once())
            ->method('conditionalStatusUpdate')
            ->with(7, 10, 'checkout', 'completed')
            ->willReturn(true);

        $this->mockActivityLogger->expects($this->once())
            ->method('logAppointmentActivity')
            ->with(
                'status',
                10,
                $this->callback(function (string $description): bool {
                    $this->assertLessThanOrEqual(255, mb_strlen($description), 'Description must not exceed 255 chars');
                    $this->assertStringContainsString('checkout acknowledged [balance_due]', $description);
                    $this->assertStringEndsWith('...', $description);
                    return true;
                }),
                $this->callback(function ($metadata) use ($longReason): bool {
                    $this->assertIsArray($metadata);
                    $this->assertTrue($metadata['checkout_override']);
                    $this->assertSame(['balance_due'], $metadata['issue_codes']);
                    $this->assertSame($longReason, $metadata['reason'], 'Full long reason must be preserved in metadata');
                    $this->assertStringContainsString($longReason, $metadata['full_description']);
                    return true;
                })
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
