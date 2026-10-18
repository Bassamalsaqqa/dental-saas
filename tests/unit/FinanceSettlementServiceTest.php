<?php

namespace Tests\unit;

use App\Services\FinanceSettlementService;
use App\Services\FinanceTreasuryService;
use CodeIgniter\Test\CIUnitTestCase;

class TestableSettlementService extends FinanceSettlementService
{
    public array $mockedPayments = [];

    public function lockSettlementPayments(int $clinicId, int $financeId): array
    {
        return $this->mockedPayments[$financeId] ?? [];
    }
}

class FinanceSettlementServiceTest extends CIUnitTestCase
{
    private FinanceTreasuryService $treasury;

    protected function setUp(): void
    {
        parent::setUp();
        $this->treasury = new FinanceTreasuryService();
    }

    public function test_rejects_invalid_context(): void
    {
        $service = new FinanceSettlementService(\Config\Database::connect(), $this->treasury);

        try {
            $service->bulkSettle(0, [1], 1);
            $this->fail('Expected DomainException for invalid clinicId.');
        } catch (\DomainException $e) {
            $this->assertSame('Invalid request context.', $e->getMessage());
        }

        try {
            $service->bulkSettle(1, [1], 0);
            $this->fail('Expected DomainException for invalid actorId.');
        } catch (\DomainException $e) {
            $this->assertSame('Invalid request context.', $e->getMessage());
        }
    }

    public function test_rejects_empty_or_excessive_selection(): void
    {
        $service = new FinanceSettlementService(\Config\Database::connect(), $this->treasury);

        try {
            $service->bulkSettle(1, [], 1);
            $this->fail('Expected DomainException for empty IDs.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('Select between 1 and 500', $e->getMessage());
        }

        $tooMany = range(1, 501);
        try {
            $service->bulkSettle(1, $tooMany, 1);
            $this->fail('Expected DomainException for > 500 IDs.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('Select between 1 and 500', $e->getMessage());
        }
    }

    public function test_rejects_malformed_and_overflow_ids(): void
    {
        $service = new FinanceSettlementService(\Config\Database::connect(), $this->treasury);

        $invalidCases = [
            [0],
            [-1],
            ['abc'],
            ['1.5'],
            ['2147483648'], // 32-bit INT overflow
            [null],
            [['nested']],
        ];

        foreach ($invalidCases as $case) {
            try {
                $service->bulkSettle(1, $case, 1);
                $this->fail('Expected DomainException for invalid ID: ' . json_encode($case));
            } catch (\DomainException $e) {
                $this->assertStringContainsString('Invalid financial entry selection', $e->getMessage());
            }
        }
    }

    public function test_rejects_invalid_overrides(): void
    {
        $service = new FinanceSettlementService(\Config\Database::connect(), $this->treasury);

        // Invalid method
        try {
            $service->bulkSettle(1, [1], 1, ['payment_method' => 'crypto']);
            $this->fail('Expected DomainException for invalid method.');
        } catch (\DomainException $e) {
            $this->assertSame('Invalid payment method.', $e->getMessage());
        }

        // Invalid account
        try {
            $service->bulkSettle(1, [1], 1, ['liquidity_account' => 'petty_cash']);
            $this->fail('Expected DomainException for invalid account.');
        } catch (\DomainException $e) {
            $this->assertSame('Invalid liquidity account.', $e->getMessage());
        }

        // Invalid currency
        try {
            $service->bulkSettle(1, [1], 1, ['currency' => 'XAU']);
            $this->fail('Expected DomainException for invalid currency.');
        } catch (\DomainException $e) {
            $this->assertSame('Invalid settlement currency.', $e->getMessage());
        }

        // Nonpositive exchange rate
        try {
            $service->bulkSettle(1, [1], 1, ['exchange_rate' => 0]);
            $this->fail('Expected DomainException for exchange rate = 0.');
        } catch (\DomainException $e) {
            $this->assertSame('Exchange rate must be greater than zero.', $e->getMessage());
        }

        // Non-numeric exchange rate
        try {
            $service->bulkSettle(1, [1], 1, ['exchange_rate' => 'abc']);
            $this->fail('Expected DomainException for non-numeric exchange rate.');
        } catch (\DomainException $e) {
            $this->assertSame('Exchange rate must be a valid positive number.', $e->getMessage());
        }

        // Invalid payment date
        try {
            $service->bulkSettle(1, [1], 1, ['payment_date' => '2026-02-30']);
            $this->fail('Expected DomainException for invalid calendar date.');
        } catch (\DomainException $e) {
            $this->assertSame('Invalid payment date.', $e->getMessage());
        }

        // Non-string notes (e.g. array)
        try {
            $service->bulkSettle(1, [1], 1, ['notes' => ['nested' => 'val']]);
            $this->fail('Expected DomainException for array notes.');
        } catch (\DomainException $e) {
            $this->assertSame('Invalid notes.', $e->getMessage());
        }
    }

    public function test_get_locked_settlement_state_authoritative_ledger(): void
    {
        $service = new TestableSettlementService(\Config\Database::connect(), $this->treasury);

        // Clean document with 0 paid
        $cleanDoc = [
            'id' => 101,
            'total_amount' => 100.00,
            'amount_paid' => 0.00,
            'payment_status' => 'pending',
        ];
        $stateClean = $service->getLockedSettlementState(1, $cleanDoc);
        $this->assertSame(100.00, $stateClean['total_amount']);
        $this->assertSame(100.00, $stateClean['remaining_base']);
        $this->assertFalse($stateClean['has_ledger']);

        // Fully discounted zero-total clean invoice
        $zeroDoc = [
            'id' => 102,
            'amount' => 100.00,
            'discount_amount' => 100.00,
            'tax_amount' => 0.00,
            'total_amount' => 0.00,
            'amount_paid' => 0.00,
            'payment_status' => 'pending',
        ];
        $stateZero = $service->getLockedSettlementState(1, $zeroDoc);
        $this->assertSame(0.00, $stateZero['remaining_base']);

        // Legacy full settled (no ledger)
        $legacyFull = [
            'id' => 103,
            'total_amount' => 150.00,
            'amount_paid' => 150.00,
            'payment_status' => 'paid',
        ];
        $stateLegacyFull = $service->getLockedSettlementState(1, $legacyFull);
        $this->assertSame(0.00, $stateLegacyFull['remaining_base']);
        $this->assertFalse($stateLegacyFull['has_ledger']);

        // Legacy ambiguous partial (no ledger) -> throws DomainException
        $legacyPartial = [
            'id' => 104,
            'total_amount' => 200.00,
            'amount_paid' => 50.00,
            'payment_status' => 'partial',
        ];
        try {
            $service->getLockedSettlementState(1, $legacyPartial);
            $this->fail('Expected DomainException for unledgered partial history.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('unledgered legacy payment history requiring manual reconciliation', $e->getMessage());
        }

        // Row with prior payment (100) and refund (40) -> authoritative net ledger is 60, remaining is 40
        $refundDoc = [
            'id' => 105,
            'total_amount' => 100.00,
            'amount_paid' => 100.00,
            'payment_status' => 'paid',
        ];
        $service->mockedPayments[105] = [
            ['amount' => 100.00, 'type' => 'payment'],
            ['amount' => 40.00, 'type' => 'refund'],
        ];
        $stateRefund = $service->getLockedSettlementState(1, $refundDoc);
        $this->assertSame(60.00, $stateRefund['ledger_settled']);
        $this->assertSame(40.00, $stateRefund['remaining_base']);
        $this->assertTrue($stateRefund['has_ledger']);
    }

    public function test_treasury_service_endorsed_check_compatibility_for_expense(): void
    {
        // Expenses allow undeposited_checks (endorsed check outgoing workflow)
        $this->assertTrue(
            $this->treasury->isCompatibleLiquidityAccount('check', 'out', 'undeposited_checks', 'expense')
        );
        $this->assertTrue(
            $this->treasury->isCompatibleLiquidityAccount('cash', 'out', 'safe_cash', 'expense')
        );
        $this->assertTrue(
            $this->treasury->isCompatibleLiquidityAccount('card', 'out', 'bank', 'expense')
        );

        // Inflow checks must use undeposited_checks
        $this->assertTrue(
            $this->treasury->isCompatibleLiquidityAccount('check', 'in', 'undeposited_checks', 'invoice')
        );
        $this->assertFalse(
            $this->treasury->isCompatibleLiquidityAccount('check', 'in', 'safe_cash', 'invoice')
        );
    }

    public function test_treasury_defaults_by_direction_and_type(): void
    {
        $this->assertSame('safe_cash', $this->treasury->defaultLiquidityAccount('cash', 'in', 'invoice'));
        $this->assertSame('undeposited_checks', $this->treasury->defaultLiquidityAccount('check', 'in', 'invoice'));
        $this->assertSame('bank', $this->treasury->defaultLiquidityAccount('card', 'in', 'invoice'));
        $this->assertSame('bank', $this->treasury->defaultLiquidityAccount('bank_transfer', 'in', 'invoice'));

        $this->assertSame('safe_cash', $this->treasury->defaultLiquidityAccount('cash', 'out', 'expense'));
        $this->assertSame('bank', $this->treasury->defaultLiquidityAccount('check', 'out', 'expense'));
        $this->assertSame('bank', $this->treasury->defaultLiquidityAccount('card', 'out', 'expense'));
    }
}
