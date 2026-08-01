<?php

namespace Tests\Unit;

use App\Services\AppointmentCheckoutCompletionService;
use CodeIgniter\Test\CIUnitTestCase;
use DomainException;

final class AppointmentCheckoutCompletionServiceTest extends CIUnitTestCase
{
    private AppointmentCheckoutCompletionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AppointmentCheckoutCompletionService();
    }

    public function testConsultationWithoutClinicalOrFinancialWorkCompletesNormally(): void
    {
        $assessment = $this->service->assessReview($this->review());

        $this->assertFalse($assessment['requires_override']);
        $this->assertTrue($assessment['can_complete_without_override']);
        $this->assertSame([], $assessment['issue_codes']);
        $this->assertSame(
            ['override' => false, 'reason' => null, 'issue_codes' => []],
            $this->service->authorize($assessment, false, null)
        );
    }

    public function testAssessmentReportsEveryUnresolvedCheckoutConcern(): void
    {
        $assessment = $this->service->assessReview($this->review([
            'clinical_state' => 'needs_review',
            'billing_state' => 'ready_to_invoice',
            'settlement_state' => 'balance_due',
            'link_basis' => 'patient_date_context',
            'examinations' => [['id' => 12]],
        ]));

        $this->assertTrue($assessment['requires_override']);
        $this->assertFalse($assessment['can_complete_without_override']);
        $this->assertSame([
            'clinical_work_unresolved',
            'charges_uninvoiced',
            'balance_due',
            'contextual_link_review',
        ], $assessment['issue_codes']);
    }

    public function testMixedCurrenciesReplaceTheGeneralUninvoicedChargeIssue(): void
    {
        $assessment = $this->service->assessReview($this->review([
            'billing_state' => 'ready_to_invoice',
            'has_mixed_invoiceable_currencies' => true,
        ]));

        $this->assertSame(['mixed_currency_charges'], $assessment['issue_codes']);
    }

    public function testUnresolvedCheckoutCannotCompleteWithoutAcknowledgement(): void
    {
        $assessment = $this->service->assessReview($this->review([
            'billing_state' => 'ready_to_invoice',
        ]));

        $this->expectException(DomainException::class);
        $this->service->authorize($assessment, false, null);
    }

    public function testAcknowledgementRequiresMeaningfulReason(): void
    {
        $assessment = $this->service->assessReview($this->review([
            'billing_state' => 'ready_to_invoice',
        ]));

        $this->expectException(DomainException::class);
        $this->service->authorize($assessment, true, 'Too short');
    }

    public function testValidAcknowledgementPreservesReasonAndIssueCodes(): void
    {
        $assessment = $this->service->assessReview($this->review([
            'billing_state' => 'ready_to_invoice',
        ]));

        $this->assertSame([
            'override' => true,
            'reason' => 'Patient will settle the invoice tomorrow.',
            'issue_codes' => ['charges_uninvoiced'],
        ], $this->service->authorize(
            $assessment,
            true,
            '  Patient will settle the invoice tomorrow.  '
        ));
    }

    private function review(array $overrides = []): array
    {
        return array_replace([
            'clinical_state' => 'not_recorded',
            'billing_state' => 'not_captured',
            'settlement_state' => 'no_invoice',
            'link_basis' => 'patient_date_context',
            'has_mixed_invoiceable_currencies' => false,
            'examinations' => [],
            'treatments' => [],
            'procedures' => [],
        ], $overrides);
    }
}
