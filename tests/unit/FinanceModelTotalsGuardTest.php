<?php

namespace Tests\unit;

use App\Models\FinanceModel;
use CodeIgniter\Test\CIUnitTestCase;

class TestableFinanceModel extends FinanceModel
{
    public ?array $mockedPersistedRow = null;
    public array $mockedPersistedRows = [];

    // Expose protected calculateTotals for direct unit testing without database
    public function testCalculateTotals(array $data): array
    {
        return $this->calculateTotals($data);
    }

    public function testComputeGuardedTotals(mixed $rawAmount, mixed $rawDiscount, mixed $rawTax): array
    {
        return $this->computeGuardedTotals($rawAmount, $rawDiscount, $rawTax);
    }

    public function testResolveActiveClinicId(): int
    {
        return $this->resolveActiveClinicId();
    }

    public function getBeforeUpdateCallbacks(): array
    {
        return $this->beforeUpdate;
    }

    protected function hasTotalAmountField(): bool
    {
        return true;
    }

    protected function fetchTargetRowForTotals(int $id): ?array
    {
        if ($this->mockedPersistedRow !== null && ($this->mockedPersistedRow['id'] ?? $id) === $id) {
            return $this->mockedPersistedRow;
        }
        return $this->mockedPersistedRows[$id] ?? null;
    }

    protected function fetchTargetRowsForTotals(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            if (isset($this->mockedPersistedRows[$id])) {
                $result[] = $this->mockedPersistedRows[$id];
            } elseif ($this->mockedPersistedRow !== null && ($this->mockedPersistedRow['id'] ?? $id) === $id) {
                $result[] = $this->mockedPersistedRow;
            }
        }
        return $result;
    }
}

class FinanceModelTotalsGuardTest extends CIUnitTestCase
{
    private TestableFinanceModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new TestableFinanceModel();
    }

    public function test_rejects_negative_and_malformed_amounts(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be a non-negative finite number.');
        $this->model->testComputeGuardedTotals(-10.0, 0, 0);
    }

    public function test_rejects_non_numeric_amount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be a non-negative finite number.');
        $this->model->testComputeGuardedTotals('invalid_num', 0, 0);
    }

    public function test_rejects_negative_discount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Discount amount must be a non-negative finite number.');
        $this->model->testComputeGuardedTotals(100.0, -5.0, 0);
    }

    public function test_rejects_discount_exceeding_amount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Discount amount cannot exceed amount.');
        $this->model->testComputeGuardedTotals(100.0, 150.0, 0);
    }

    public function test_rejects_invalid_tax_percentage_without_clamping(): void
    {
        // Negative tax percent
        try {
            $this->model->testComputeGuardedTotals(100.0, 10.0, -5.0);
            $this->fail('Expected InvalidArgumentException for negative tax.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('Tax percentage must be a finite number between 0 and 100.', $e->getMessage());
        }

        // Tax percent exceeding 100
        try {
            $this->model->testComputeGuardedTotals(100.0, 10.0, 120.0);
            $this->fail('Expected InvalidArgumentException for tax > 100.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('Tax percentage must be a finite number between 0 and 100.', $e->getMessage());
        }
    }

    public function test_computes_totals_with_monetary_precision_rounding(): void
    {
        [$amount, $discount, $taxPercent, $taxAmount, $totalAmount] = $this->model->testComputeGuardedTotals(100.0, 20.0, 17.0);

        $this->assertSame(100.0, $amount);
        $this->assertSame(20.0, $discount);
        $this->assertSame(17.0, $taxPercent);
        $this->assertSame(13.60, $taxAmount); // (100 - 20) * 0.17 = 80 * 0.17 = 13.60
        $this->assertSame(93.60, $totalAmount); // 80 + 13.60 = 93.60
    }

    public function test_status_only_update_preserves_legacy_amounts_untouched(): void
    {
        $input = [
            'id' => 42,
            'data' => [
                'payment_status' => 'paid',
                'paid_date' => '2026-10-18',
                'amount_paid' => 150.00,
                'balance_due' => 0.00,
            ],
        ];

        $output = $this->model->testCalculateTotals($input);

        // Ensures no recalculation happened: total_amount and tax_amount were not added or altered
        $this->assertSame($input, $output);
        $this->assertArrayNotHasKey('total_amount', $output['data']);
        $this->assertArrayNotHasKey('tax_amount', $output['data']);
    }

    public function test_partial_discount_update_merges_persisted_basis(): void
    {
        $this->model->mockedPersistedRow = [
            'id' => 10,
            'amount' => 200.00,
            'discount_amount' => 10.00,
            'tax_percentage' => 10.00,
            'tax_amount' => 19.00,
            'total_amount' => 209.00,
        ];

        $input = [
            'id' => 10,
            'data' => [
                'discount_amount' => 50.00, // Updating discount only
            ],
        ];

        $output = $this->model->testCalculateTotals($input);

        // Taxable base = 200 - 50 = 150. Tax 10% = 15.00. Total = 165.00.
        $this->assertSame(50.00, $output['data']['discount_amount']);
        $this->assertSame(15.00, $output['data']['tax_amount']);
        $this->assertSame(165.00, $output['data']['total_amount']);
    }

    public function test_partial_amount_update_merges_persisted_discount_and_tax(): void
    {
        $this->model->mockedPersistedRow = [
            'id' => 10,
            'amount' => 100.00,
            'discount_amount' => 20.00,
            'tax_percentage' => 5.00,
            'tax_amount' => 4.00,
            'total_amount' => 84.00,
        ];

        $input = [
            'id' => 10,
            'data' => [
                'amount' => 300.00, // Updating amount only
            ],
        ];

        $output = $this->model->testCalculateTotals($input);

        // Taxable base = 300 - 20 = 280. Tax 5% = 14.00. Total = 294.00.
        $this->assertSame(300.00, $output['data']['amount']);
        $this->assertSame(14.00, $output['data']['tax_amount']);
        $this->assertSame(294.00, $output['data']['total_amount']);
    }

    public function test_rejects_ambiguous_mass_update_without_target_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Ambiguous mass update: target transaction ID is required to calculate totals.');

        $this->model->testCalculateTotals([
            'id' => null,
            'data' => [
                'amount' => 100.00,
            ],
        ]);
    }

    public function test_rejects_ambiguous_mass_update_across_different_bases(): void
    {
        $this->model->mockedPersistedRows = [
            1 => ['id' => 1, 'amount' => 100.00, 'discount_amount' => 0.00, 'tax_percentage' => 10.00],
            2 => ['id' => 2, 'amount' => 200.00, 'discount_amount' => 0.00, 'tax_percentage' => 10.00],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Ambiguous mass update: updating monetary basis for multiple rows with different amounts is not supported.');

        $this->model->testCalculateTotals([
            'id' => [1, 2],
            'data' => [
                'discount_amount' => 10.00,
            ],
        ]);
    }

    public function test_before_update_restores_enforce_tenant_scope(): void
    {
        $callbacks = $this->model->getBeforeUpdateCallbacks();
        $this->assertSame(['enforceTenantScope', 'calculateTotals'], $callbacks);
    }

    public function test_resolve_active_clinic_id_fails_closed_without_context(): void
    {
        \App\Libraries\ApiRuntimeContext::clear();
        if (function_exists('session')) {
            try {
                session()->remove('active_clinic_id');
            } catch (\Throwable $e) {}
        }

        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->model->testResolveActiveClinicId();
    }
}
