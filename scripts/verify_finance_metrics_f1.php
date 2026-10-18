<?php
declare(strict_types=1);

/** Focused no-DB regression: invoke the real controller methods, never copy their arithmetic. */
namespace App\Controllers {
    abstract class BaseController {}
}

namespace {
    require dirname(__DIR__) . '/app/Controllers/Finance.php';
    require dirname(__DIR__) . '/app/Services/FinanceTreasuryService.php';

    $reflection = new ReflectionClass(\App\Controllers\Finance::class);
    $controller = $reflection->newInstanceWithoutConstructor(); // No application/DB bootstrap.
    $cashReport = $reflection->getMethod('summarizeCashReport');
    $series = $reflection->getMethod('buildMonthlyCollectionsSeries');
    $checks = 0;
    $equal = static function ($actual, $expected, string $label) use (&$checks): void {
        if (is_numeric($expected) ? abs((float) $actual - (float) $expected) > 0.0001 : $actual !== $expected) {
            throw new RuntimeException($label . ': expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
        }
        $checks++;
    };
    $row = static fn(string $source, string $type, float $amount, float $signed, string $account = 'safe_cash', string $method = 'cash'): array => [
        'event_source' => $source, 'event_type' => $type, 'amount_base' => $amount,
        'signed_amount_base' => $signed, 'liquidity_account' => $account,
        'payment_method' => $method, 'report_date' => '2099-06-10',
    ];
    $rows = [
        $row('invoice_settlement', 'payment', 100, 100),
        $row('invoice_settlement', 'refund', 10, -10),
        $row('invoice_settlement', 'void', 5, -5),
        $row('invoice_settlement', 'adjustment', 7, 0),
        $row('expense_settlement', 'adjustment', 8, 0),
        $row('direct_transaction', 'payment', 20, 20),
        $row('direct_transaction', 'refund', 2, -2),
        $row('invoice_settlement', 'payment', 35, 35, 'bank', 'card') + ['currency' => 'USD', 'amount_original' => 10, 'exchange_rate' => 3.5],
        $row('expense_settlement', 'expense', 30, -30, 'bank', 'bank_transfer'),
        $row('expense_settlement', 'vendor_refund', 5, 5, 'bank', 'bank_transfer'),
        $row('liquidity_transfer', 'transfer_out', 40, -40),
        $row('liquidity_transfer', 'transfer_in', 40, 40, 'bank'),
    ];
    $cash = $cashReport->invoke($controller, $rows);
    foreach (['gross_collections' => 155, 'refunds_total' => 17, 'expenses_paid' => 25, 'supplier_refunds' => 5,
        'net_clinic_cash' => 113, 'total_transactions' => 8, 'safe_cash_in' => 120,
        'safe_cash_out' => 17, 'safe_cash_net' => 103, 'average_transaction' => 25.88] as $key => $expected) {
        $equal($cash[$key], $expected, $key);
    }
    $equal($cash['gross_collections'] - $cash['refunds_total'], 138, 'Net patient collections');
    $equal($series->invoke($controller, $rows, '2099-06-01', '2099-06-30')['values'], [155.0], 'Chart remains explicitly gross collections');

    $withCogs = $cashReport->invoke($controller, [...$rows, $row('expense_settlement', 'expense', 3, -3)]);
    $equal($withCogs['expenses_paid'], 28, 'Genuine COGS settlement counts once');
    $equal($withCogs['net_clinic_cash'], 110, 'Genuine COGS settlement affects cash');
    $equal($withCogs['gross_collections'] - $withCogs['refunds_total'], 138, 'COGS never becomes patient revenue');
    $equal($withCogs['total_transactions'], 9, 'Genuine COGS settlement is a cash event');

    $withPayout = $cashReport->invoke($controller, [...$rows, $row('provider_payout_payment', 'provider_payout_payment', 4, -4, 'bank')]);
    $equal($withPayout['provider_paid_cash_total'], 4, 'Provider payout remains cash outflow');
    $equal($withPayout['net_clinic_cash'], 109, 'Provider cash reduces net cash');
    $bankCash = $cashReport->invoke($controller, [$row('invoice_settlement', 'payment', 50, 50, 'bank')]);
    $equal($bankCash['safe_cash_in'], 0, 'Explicit bank account wins over cash payment method');
    $legacyCash = $cashReport->invoke($controller, [$row('direct_transaction', 'payment', 20, 20, '')]);
    $equal($legacyCash['safe_cash_in'], 20, 'Legacy missing-account cash fallback preserved');

    $treasury = (new \App\Services\FinanceTreasuryService())->summarizeMovement($rows);
    $equal($treasury['safe_cash']['net'], 63, 'Treasury retains transfer outgoing leg');
    $equal($treasury['bank']['net'], 50, 'Treasury retains transfer incoming leg');
    $equal($treasury['totals']['net'], 113, 'Internal transfer preserves external treasury net');

    // Query-backed ledger arithmetic is verified separately; protect the view-data wiring here.
    $reportsMethod = $reflection->getMethod('reports');
    $controllerLines = file($reportsMethod->getFileName());
    $reportsSource = implode('', array_slice($controllerLines, $reportsMethod->getStartLine() - 1, $reportsMethod->getEndLine() - $reportsMethod->getStartLine() + 1));
    $equal(str_contains($reportsSource, '$moneyIn = (float) ($ledger[\'summary\'][\'inflow_total\'] ?? 0);'), true, 'Owner money in uses gross external ledger inflow');
    $equal(str_contains($reportsSource, '$moneyOut = (float) ($ledger[\'summary\'][\'outflow_total\'] ?? 0);'), true, 'Owner money out uses gross external ledger outflow');

    foreach (['en', 'ar'] as $locale) {
        // Existing language-file BOM is preserved; keep this verifier's JSON output clean.
        ob_start();
        $language = require dirname(__DIR__) . '/app/Language/' . $locale . '/Finance.php';
        ob_end_clean();
        foreach (['vendorRefund', 'adjustment', 'externalCashEvents', 'externalInflow', 'externalOutflow'] as $key) {
            $equal(isset($language[$key]) && trim($language[$key]) !== '', true, $locale . ' label ' . $key);
        }
    }
    echo json_encode(['status' => 'PASS', 'checks' => $checks, 'database_used' => false,
        'sql_verification' => 'Separate isolated real-SQL fixture required for model queries and joins.'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
}