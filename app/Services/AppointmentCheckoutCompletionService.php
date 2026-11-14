<?php

namespace App\Services;

use DomainException;

final class AppointmentCheckoutCompletionService
{
    public function __construct(private ?AppointmentCheckoutService $checkout = null)
    {
        $this->checkout ??= new AppointmentCheckoutService();
    }

    public function assess(int $clinicId, int $appointmentId): array
    {
        return $this->assessReview($this->checkout->review($clinicId, $appointmentId));
    }

    public function assessReview(array $review): array
    {
        $issues = [];
        $hasClinicalRecords = !empty($review['examinations'])
            || !empty($review['treatments'])
            || !empty($review['procedures']);

        if (($review['clinical_state'] ?? '') === 'needs_review') {
            $issues[] = ['code' => 'clinical_work_unresolved', 'category' => 'clinical'];
        }

        if (!empty($review['has_mixed_invoiceable_currencies'])) {
            $issues[] = ['code' => 'mixed_currency_charges', 'category' => 'billing'];
        } elseif (($review['billing_state'] ?? '') === 'ready_to_invoice') {
            $issues[] = ['code' => 'charges_uninvoiced', 'category' => 'billing'];
        }

        if (($review['settlement_state'] ?? '') === 'balance_due') {
            $issues[] = ['code' => 'balance_due', 'category' => 'payment'];
        }

        if (($review['link_basis'] ?? '') !== 'explicit' && $hasClinicalRecords) {
            $issues[] = ['code' => 'contextual_link_review', 'category' => 'clinical'];
        }

        return [
            'issues' => $issues,
            'issue_codes' => array_column($issues, 'code'),
            'requires_override' => $issues !== [],
            'can_complete_without_override' => $issues === [],
        ];
    }

    public function authorize(array $assessment, bool $override, ?string $reason): array
    {
        if (empty($assessment['requires_override'])) {
            return ['override' => false, 'reason' => null, 'issue_codes' => []];
        }

        if (!$override) {
            throw new DomainException('Checkout review requires acknowledgement before completion.');
        }

        $reason = trim((string) $reason);
        $length = mb_strlen($reason);
        if ($length < 10 || $length > 500) {
            throw new DomainException('Checkout completion reason must be between 10 and 500 characters.');
        }

        return [
            'override' => true,
            'reason' => $reason,
            'issue_codes' => array_values(array_map('strval', $assessment['issue_codes'] ?? [])),
        ];
    }
}
