<?php

namespace Tests\unit;

use App\Services\FinanceTreasuryService;
use CodeIgniter\Test\CIUnitTestCase;

class FinanceTreasuryServiceTest extends CIUnitTestCase
{
    public function test_default_liquidity_account_mapping_is_conservative_and_explicit(): void
    {
        $service = new FinanceTreasuryService();

        $this->assertSame('safe_cash', $service->defaultLiquidityAccount('cash', 'in', 'payment'));
        $this->assertSame('bank', $service->defaultLiquidityAccount('card', 'in', 'payment'));
        $this->assertSame('bank', $service->defaultLiquidityAccount('bank_transfer', 'out', 'expense'));
        $this->assertSame('undeposited_checks', $service->defaultLiquidityAccount('check', 'in', 'payment'));
        $this->assertSame('bank', $service->defaultLiquidityAccount('check', 'out', 'provider_payout'));

        $this->assertTrue($service->isCompatibleLiquidityAccount('cash', 'out', 'safe_cash', 'expense'));
        $this->assertTrue($service->isCompatibleLiquidityAccount('cash', 'out', 'bank', 'expense'));
        $this->assertTrue($service->isCompatibleLiquidityAccount('cash', 'out', 'undeposited_checks', 'expense'));
        $this->assertFalse($service->isCompatibleLiquidityAccount('cash', 'out', 'bank', 'provider_payout'));
    }

    public function test_movement_and_closing_summaries_keep_internal_transfers_neutral_on_total_liquid_funds(): void
    {
        $service = new FinanceTreasuryService();
        $rows = [
            ['event_source' => 'direct_transaction', 'liquidity_account' => 'safe_cash', 'signed_amount_base' => 1000.00],
            ['event_source' => 'direct_transaction', 'liquidity_account' => 'safe_cash', 'signed_amount_base' => -200.00],
            ['event_source' => 'direct_transaction', 'liquidity_account' => 'bank', 'signed_amount_base' => 500.00],
            ['event_source' => 'direct_transaction', 'liquidity_account' => 'undeposited_checks', 'signed_amount_base' => 300.00],
            ['event_source' => 'liquidity_transfer', 'liquidity_account' => 'safe_cash', 'signed_amount_base' => -400.00],
            ['event_source' => 'liquidity_transfer', 'liquidity_account' => 'bank', 'signed_amount_base' => 400.00],
        ];

        $movement = $service->summarizeMovement($rows);
        $closing = $service->summarizeClosingBalances($rows);

        $this->assertSame(1000.0, $movement['safe_cash']['in']);
        $this->assertSame(600.0, $movement['safe_cash']['out']);
        $this->assertSame(900.0, $movement['bank']['net']);
        $this->assertSame(1800.0, $movement['totals']['in']);
        $this->assertSame(200.0, $movement['totals']['out']);
        $this->assertSame(1600.0, $movement['totals']['net']);

        $this->assertSame(400.0, $closing['safe_cash']);
        $this->assertSame(900.0, $closing['bank']);
        $this->assertSame(300.0, $closing['undeposited_checks']);
        $this->assertSame(1600.0, $closing['total_liquid_funds']);
    }
}
