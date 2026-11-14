<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ClinicalEncounterActionResolver;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * DB-Free unit tests for ClinicalEncounterActionResolver.
 *
 * Verifies:
 * - Single clinic+patient-owned examination routes to examination/{id} with appointmentOpenExamination.
 * - Zero associated examinations route to examination/create?patient_id=..&appointment_id=.. with appointmentStartExamination.
 * - Multiple associated examinations route to patient/{patient_id}?tab=examinations with appointmentPatientExaminations (no arbitrary latest).
 * - Foreign clinic, foreign patient, and unrelated appointment examinations are excluded.
 * - Missing permissions trigger safe fallback to appointment/{id}.
 * - Invalid or unprovable context returns safe fallback.
 */
final class ClinicalEncounterActionResolverTest extends CIUnitTestCase
{
    private ClinicalEncounterActionResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new ClinicalEncounterActionResolver();
    }

    public function testSingleAssociatedExaminationRoutesToExaminationView(): void
    {
        $appointment = [
            'id' => 105,
            'clinic_id' => 1,
            'patient_id' => 505,
        ];

        $associatedExams = [
            [
                'id' => 801,
                'clinic_id' => 1,
                'patient_id' => 505,
                'appointment_id' => 105,
                'status' => 'pending',
            ],
        ];

        $result = $this->resolver->resolve(1, $appointment, $associatedExams, true, true, 'dashboard');

        $this->assertSame('view', $result['type']);
        $this->assertSame(1, $result['count']);
        $this->assertSame(801, $result['examination_id']);
        $this->assertSame('appointmentOpenExamination', $result['label_key']);
        $this->assertStringContainsString('examination/801', $result['url']);
        $this->assertStringContainsString('return_to=dashboard', $result['url']);
    }

    public function testSingleAssociatedExamWithoutViewPermissionFallsBackToAppointmentDetail(): void
    {
        $appointment = [
            'id' => 105,
            'clinic_id' => 1,
            'patient_id' => 505,
        ];

        $associatedExams = [
            [
                'id' => 801,
                'clinic_id' => 1,
                'patient_id' => 505,
                'appointment_id' => 105,
            ],
        ];

        $result = $this->resolver->resolve(1, $appointment, $associatedExams, false, true, 'dashboard');

        $this->assertSame('fallback', $result['type']);
        $this->assertSame('appointmentActionView', $result['label_key']);
        $this->assertStringContainsString('appointment/105', $result['url']);
    }

    public function testZeroAssociatedExaminationsRouteToExaminationCreate(): void
    {
        $appointment = [
            'id' => 105,
            'clinic_id' => 1,
            'patient_id' => 505,
        ];

        $result = $this->resolver->resolve(1, $appointment, [], true, true, 'dashboard');

        $this->assertSame('create', $result['type']);
        $this->assertSame(0, $result['count']);
        $this->assertNull($result['examination_id']);
        $this->assertSame('appointmentStartExamination', $result['label_key']);
        $this->assertStringContainsString('examination/create', $result['url']);
        $this->assertStringContainsString('patient_id=505', $result['url']);
        $this->assertStringContainsString('appointment_id=105', $result['url']);
        $this->assertStringContainsString('return_to=dashboard', $result['url']);
    }

    public function testZeroAssociatedExamWithoutCreatePermissionFallsBackToAppointmentDetail(): void
    {
        $appointment = [
            'id' => 105,
            'clinic_id' => 1,
            'patient_id' => 505,
        ];

        $result = $this->resolver->resolve(1, $appointment, [], true, false, 'dashboard');

        $this->assertSame('fallback', $result['type']);
        $this->assertSame('appointmentActionView', $result['label_key']);
        $this->assertStringContainsString('appointment/105', $result['url']);
    }

    public function testMultipleAssociatedExaminationsRouteToPatientWorkspaceWithoutArbitraryLatest(): void
    {
        $appointment = [
            'id' => 105,
            'clinic_id' => 1,
            'patient_id' => 505,
        ];

        $associatedExams = [
            [
                'id' => 801,
                'clinic_id' => 1,
                'patient_id' => 505,
                'appointment_id' => 105,
            ],
            [
                'id' => 802,
                'clinic_id' => 1,
                'patient_id' => 505,
                'appointment_id' => 105,
            ],
        ];

        $result = $this->resolver->resolve(1, $appointment, $associatedExams, true, true, 'dashboard');

        $this->assertSame('workspace', $result['type']);
        $this->assertSame(2, $result['count']);
        $this->assertNull($result['examination_id']);
        $this->assertSame('appointmentPatientExaminations', $result['label_key']);
        $this->assertStringContainsString('patient/505', $result['url']);
        $this->assertStringContainsString('tab=examinations', $result['url']);
        $this->assertStringContainsString('return_to=dashboard', $result['url']);
    }

    public function testForeignClinicOrPatientExaminationsAreStrictlyExcluded(): void
    {
        $appointment = [
            'id' => 105,
            'clinic_id' => 1,
            'patient_id' => 505,
        ];

        $associatedExams = [
            // Foreign clinic (clinic_id = 2)
            [
                'id' => 901,
                'clinic_id' => 2,
                'patient_id' => 505,
                'appointment_id' => 105,
            ],
            // Foreign patient (patient_id = 999)
            [
                'id' => 902,
                'clinic_id' => 1,
                'patient_id' => 999,
                'appointment_id' => 105,
            ],
            // Unrelated appointment (appointment_id = 888)
            [
                'id' => 903,
                'clinic_id' => 1,
                'patient_id' => 505,
                'appointment_id' => 888,
            ],
        ];

        // All foreign/unrelated exams are filtered out, leaving 0 valid exams -> routes to create
        $result = $this->resolver->resolve(1, $appointment, $associatedExams, true, true, 'dashboard');

        $this->assertSame('create', $result['type']);
        $this->assertSame(0, $result['count']);
        $this->assertSame('appointmentStartExamination', $result['label_key']);
        $this->assertStringContainsString('examination/create', $result['url']);
        $this->assertStringContainsString('patient_id=505', $result['url']);
        $this->assertStringContainsString('appointment_id=105', $result['url']);
    }

    public function testUnsafeOrCrossClinicAppointmentContextReturnsFallback(): void
    {
        // Appointment has different clinic_id than active tenant
        $appointment = [
            'id' => 105,
            'clinic_id' => 99,
            'patient_id' => 505,
        ];

        $result = $this->resolver->resolve(1, $appointment, [], true, true, 'dashboard');

        $this->assertSame('fallback', $result['type']);
        $this->assertSame('appointmentActionView', $result['label_key']);
    }

    public function testInvalidClinicIdReturnsFallback(): void
    {
        $appointment = [
            'id' => 105,
            'clinic_id' => 0,
            'patient_id' => 505,
        ];

        $result = $this->resolver->resolve(0, $appointment, [], true, true, 'dashboard');

        $this->assertSame('fallback', $result['type']);
        $this->assertNull($result['count']);
        $this->assertSame('unverified_context', $result['state']);
        $this->assertSame('appointmentActionView', $result['label_key']);
    }

    public function testNullAssociatedExaminationsFromLookupFailureReturnsFallbackWithNullCountAndUnavailableState(): void
    {
        $appointment = [
            'id' => 105,
            'clinic_id' => 1,
            'patient_id' => 505,
        ];

        // Passing null simulates a database exception or lookup failure
        $result = $this->resolver->resolve(1, $appointment, null, true, true, 'dashboard');

        $this->assertSame('fallback', $result['type']);
        $this->assertNull($result['count'], 'Unknown lookup failure must NOT be interpreted as count 0.');
        $this->assertNull($result['examination_id']);
        $this->assertSame('unavailable', $result['state']);
        $this->assertSame('appointmentActionView', $result['label_key']);
        $this->assertStringContainsString('appointment/105', $result['url']);
        $this->assertStringNotContainsString('examination/create', $result['url'], 'Must not fabricate create action on lookup failure.');
    }

    public function testMissingAppointmentClinicIdDoesNotImputeClinicAndReturnsFallback(): void
    {
        // Appointment is missing the clinic_id field entirely
        $appointment = [
            'id' => 105,
            'patient_id' => 505,
        ];

        $result = $this->resolver->resolve(1, $appointment, [], true, true, 'dashboard');

        $this->assertSame('fallback', $result['type']);
        $this->assertNull($result['count'], 'Unverified context must not fabricate count 0.');
        $this->assertSame('unverified_context', $result['state']);
        $this->assertSame('appointmentActionView', $result['label_key']);
        $this->assertStringContainsString('appointment/105', $result['url']);
    }

    public function testVerifiedEmptyExaminationsWithCreatePermissionReturnsCreateWithVerifiedStateAndZeroCount(): void
    {
        $appointment = [
            'id' => 105,
            'clinic_id' => 1,
            'patient_id' => 505,
        ];

        // Passing empty array [] represents a successful query that verified 0 examinations exist
        $result = $this->resolver->resolve(1, $appointment, [], true, true, 'dashboard');

        $this->assertSame('create', $result['type']);
        $this->assertSame(0, $result['count'], 'Verified empty must be count 0.');
        $this->assertSame('verified', $result['state']);
        $this->assertSame('appointmentStartExamination', $result['label_key']);
        $this->assertStringContainsString('examination/create', $result['url']);
    }

    public function testVerifiedEmptyExaminationsWithoutCreatePermissionReturnsFallbackWithUnauthorizedStateAndZeroCount(): void
    {
        $appointment = [
            'id' => 105,
            'clinic_id' => 1,
            'patient_id' => 505,
        ];

        $result = $this->resolver->resolve(1, $appointment, [], true, false, 'dashboard');

        $this->assertSame('fallback', $result['type']);
        $this->assertSame(0, $result['count']);
        $this->assertSame('unauthorized', $result['state']);
        $this->assertSame('appointmentActionView', $result['label_key']);
        $this->assertStringContainsString('appointment/105', $result['url']);
    }

    public function testSingleExamWithoutViewPermissionReturnsFallbackWithUnauthorizedStateAndCountOne(): void
    {
        $appointment = [
            'id' => 105,
            'clinic_id' => 1,
            'patient_id' => 505,
        ];
        $associatedExams = [
            [
                'id' => 801,
                'clinic_id' => 1,
                'patient_id' => 505,
                'appointment_id' => 105,
            ],
        ];

        $result = $this->resolver->resolve(1, $appointment, $associatedExams, false, true, 'dashboard');

        $this->assertSame('fallback', $result['type']);
        $this->assertSame(1, $result['count']);
        $this->assertSame('unauthorized', $result['state']);
        $this->assertSame('appointmentActionView', $result['label_key']);
    }

    public function testMultipleExamsWithoutViewPermissionReturnsFallbackWithUnauthorizedStateAndMultipleCount(): void
    {
        $appointment = [
            'id' => 105,
            'clinic_id' => 1,
            'patient_id' => 505,
        ];
        $associatedExams = [
            [
                'id' => 801,
                'clinic_id' => 1,
                'patient_id' => 505,
                'appointment_id' => 105,
            ],
            [
                'id' => 802,
                'clinic_id' => 1,
                'patient_id' => 505,
                'appointment_id' => 105,
            ],
        ];

        $result = $this->resolver->resolve(1, $appointment, $associatedExams, false, true, 'dashboard');

        $this->assertSame('fallback', $result['type']);
        $this->assertSame(2, $result['count']);
        $this->assertSame('unauthorized', $result['state']);
        $this->assertSame('appointmentActionView', $result['label_key']);
    }
}
