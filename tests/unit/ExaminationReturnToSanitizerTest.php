<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Examination;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * DB-Free unit test for Examination return_to sanitization and resolveCreateReturnTo.
 */
class TestableExaminationReturnSanitizer extends Examination
{
    public function __construct()
    {
        // DB-free constructor override
    }

    public function testSanitizeInternalReturnTo(?string $returnTo, array $allowedPrefixes): ?string
    {
        return $this->sanitizeInternalReturnTo($returnTo, $allowedPrefixes);
    }

    public function testResolveCreateReturnTo(int $patientId, $returnToRaw): ?string
    {
        $ref = new \ReflectionMethod(Examination::class, 'resolveCreateReturnTo');
        return $ref->invoke($this, $patientId, $returnToRaw);
    }
}

final class ExaminationReturnToSanitizerTest extends CIUnitTestCase
{
    private TestableExaminationReturnSanitizer $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new TestableExaminationReturnSanitizer();
    }

    public function testDashboardAndDashboardTabArePermitted(): void
    {
        $result1 = $this->controller->testSanitizeInternalReturnTo('dashboard', ['patient', 'patients', 'examination', 'examinations', 'dashboard']);
        $this->assertSame('dashboard', $result1);

        $result2 = $this->controller->testSanitizeInternalReturnTo('dashboard?tab=today', ['patient', 'patients', 'examination', 'examinations', 'dashboard']);
        $this->assertSame('dashboard?tab=today', $result2);

        $resolved = $this->controller->testResolveCreateReturnTo(501, 'dashboard?tab=today');
        $this->assertSame('dashboard?tab=today', $resolved);
    }

    public function testMatchingPatientAndTabArePermitted(): void
    {
        $resolved = $this->controller->testResolveCreateReturnTo(501, 'patient/501?tab=examinations');
        $this->assertSame('patient/501?tab=examinations', $resolved);

        $resolvedBare = $this->controller->testResolveCreateReturnTo(501, 'patient/501');
        $this->assertSame('patient/501', $resolvedBare);
    }

    public function testMismatchedPatientIsRejected(): void
    {
        $resolved = $this->controller->testResolveCreateReturnTo(501, 'patient/999?tab=examinations');
        $this->assertNull($resolved, 'Cross-patient return target must be rejected.');
    }

    public function testOpenRedirectAndProtocolInjectionAreRejected(): void
    {
        $this->assertNull($this->controller->testSanitizeInternalReturnTo('https://evil.com', ['dashboard', 'patient']));
        $this->assertNull($this->controller->testSanitizeInternalReturnTo('http://evil.com/dashboard', ['dashboard', 'patient']));
        $this->assertNull($this->controller->testSanitizeInternalReturnTo('//evil.com', ['dashboard', 'patient']));
        $this->assertNull($this->controller->testSanitizeInternalReturnTo('javascript:alert(1)', ['dashboard', 'patient']));
        $this->assertNull($this->controller->testResolveCreateReturnTo(501, 'https://evil.com'));
    }

    public function testIllegalCharactersAreRejected(): void
    {
        $this->assertNull($this->controller->testSanitizeInternalReturnTo('dashboard<script>', ['dashboard']));
        $this->assertNull($this->controller->testSanitizeInternalReturnTo('dashboard"onclick="alert(1)', ['dashboard']));
        $this->assertNull($this->controller->testResolveCreateReturnTo(501, 'dashboard\0'));
    }
}
