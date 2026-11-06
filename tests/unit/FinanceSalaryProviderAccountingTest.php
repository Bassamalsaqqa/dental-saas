<?php

namespace Tests\unit;

use App\Controllers\Finance;
use App\Models\ClinicUserModel;
use App\Services\SettingsService;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

final class FinanceSalaryProviderAccountingTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $settings = $this->createMock(SettingsService::class);
        $settings->method('get')->willReturn('ILS');
        Services::injectMock('settings', $settings);
        helper('settings');
    }

    private function createFinanceControllerWithGetAndPost(array $getData = [], array $postData = []): Finance
    {
        $request = $this->createMock(IncomingRequest::class);
        $request->method('getGet')->willReturnCallback(static fn ($key = null) => $key === null ? $getData : ($getData[$key] ?? null));
        $request->method('getPost')->willReturnCallback(static fn ($key = null) => $key === null ? $postData : ($postData[$key] ?? null));

        $controller = (new \ReflectionClass(Finance::class))->newInstanceWithoutConstructor();
        $refRequest = new \ReflectionProperty(Finance::class, 'request');
        $refRequest->setValue($controller, $request);

        return $controller;
    }

    private function createFinanceControllerWithPost(array $postData): Finance
    {
        $request = $this->createMock(IncomingRequest::class);
        $request->method('getPost')->willReturnCallback(static function ($key = null) use ($postData) {
            if ($key === null) {
                return $postData;
            }
            return $postData[$key] ?? null;
        });

        $controller = (new \ReflectionClass(Finance::class))->newInstanceWithoutConstructor();
        $refRequest = new \ReflectionProperty(Finance::class, 'request');
        $refRequest->setValue($controller, $request);

        return $controller;
    }

    /**
     * Journey (a): Salary expense payment-state normalization only, not persisted lifecycle.
     * Scenario: 1,000 total expense with 400 paid leaves 600 balance due.
     */
    public function testSalaryExpensePartialPaymentCalculation(): void
    {
        $dueDate = date('Y-m-d', strtotime('+30 days'));
        $controller = $this->createFinanceControllerWithPost([
            'payment_status' => 'partial',
            'amount_paid' => '400.00',
            'due_date' => $dueDate,
            'paid_date' => '2026-11-05',
        ]);

        $method = new \ReflectionMethod(Finance::class, 'buildExpensePaymentState');
        $state = $method->invoke($controller, 1000.00);

        $this->assertSame('partial', $state['payment_status']);
        $this->assertSame(400.00, $state['amount_paid']);
        $this->assertSame(600.00, $state['balance_due']);
        $this->assertSame('2026-11-05', $state['paid_date']);
        $this->assertSame($dueDate, $state['due_date']);
    }

    public function testSalaryExpenseFullPaymentCalculation(): void
    {
        $controller = $this->createFinanceControllerWithPost([
            'payment_status' => 'paid',
            'paid_date' => '2026-11-05',
        ]);

        $method = new \ReflectionMethod(Finance::class, 'buildExpensePaymentState');
        $state = $method->invoke($controller, 1000.00);

        $this->assertSame('paid', $state['payment_status']);
        $this->assertSame(1000.00, $state['amount_paid']);
        $this->assertSame(0.0, $state['balance_due']);
        $this->assertNull($state['due_date']);
        $this->assertSame('2026-11-05', $state['paid_date']);
    }

    public function testSalaryExpensePendingPaymentCalculation(): void
    {
        $controller = $this->createFinanceControllerWithPost([
            'payment_status' => 'pending',
            'amount_paid' => '0.00',
            'due_date' => date('Y-m-d', strtotime('+30 days')),
        ]);

        $method = new \ReflectionMethod(Finance::class, 'buildExpensePaymentState');
        $state = $method->invoke($controller, 1000.00);

        $this->assertSame('pending', $state['payment_status']);
        $this->assertSame(0.00, $state['amount_paid']);
        $this->assertSame(1000.00, $state['balance_due']);
        $this->assertNull($state['paid_date']);
    }

    public function testSalaryExpenseRejectsPaidExceedingTotal(): void
    {
        $controller = $this->createFinanceControllerWithPost([
            'payment_status' => 'partial',
            'amount_paid' => '1001.00',
        ]);

        $method = new \ReflectionMethod(Finance::class, 'buildExpensePaymentState');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Amount paid cannot exceed the total expense amount.');
        $method->invoke($controller, 1000.00);
    }

    public function testSalaryExpenseRejectsNonPositiveForPartial(): void
    {
        $controller = $this->createFinanceControllerWithPost([
            'payment_status' => 'partial',
            'amount_paid' => '-50.00',
        ]);

        $method = new \ReflectionMethod(Finance::class, 'buildExpensePaymentState');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Partial expenses must include an amount already paid.');
        $method->invoke($controller, 1000.00);
    }

    /**
     * Journey (b): Provider allocation normalization, not a later payment/ledger write.
     * Scenario: 30% of supplied 1,000 basis predicts 300 share; 100 inline payment predicts 200 due.
     */
    public function testProviderSplitPercentageAllocationWithPartialPayment(): void
    {
        $controller = $this->createFinanceControllerWithPost([
            'provider_split_enabled' => '1',
            'provider_type' => ['user'],
            'provider_user_id' => ['7'],
            'provider_split_mode' => ['percent'],
            'provider_split_value' => ['30.00'],
            'provider_mark_paid_now' => ['1'],
            'provider_paid_amount' => ['100.00'],
            'provider_paid_method' => ['cash'],
            'provider_paid_date' => ['2026-11-05'],
        ]);

        // Mock clinicUserModel to confirm user 7 is active
        $mockClinicUser = $this->createMock(ClinicUserModel::class);
        $mockClinicUser->method('findActiveMembershipByClinicAndUser')->willReturn(['user_id' => 7, 'role_id' => 2]);
        $refClinicUser = new \ReflectionProperty(Finance::class, 'clinicUserModel');
        $refClinicUser->setValue($controller, $mockClinicUser);

        $method = new \ReflectionMethod(Finance::class, 'normalizeProviderSplitPayloads');
        $payloads = $method->invoke($controller, 1, 1000.00);

        $this->assertCount(1, $payloads);
        $p = $payloads[0];
        $this->assertSame('user', $p['provider_type']);
        $this->assertSame(7, $p['provider_user_id']);
        $this->assertSame('percent', $p['split_mode']);
        $this->assertSame(30.00, $p['split_value']);
        $this->assertSame(1000.00, $p['gross_amount']);
        $this->assertSame(300.00, $p['provider_share_amount']);
        $this->assertSame(700.00, $p['clinic_share_amount']);
        $this->assertSame(100.00, $p['provider_paid_amount']);
        $this->assertSame(200.00, $p['provider_balance_due']);
        $this->assertSame('partial', $p['status']);
    }

    public function testProviderSplitFixedAmountAllocation(): void
    {
        $controller = $this->createFinanceControllerWithPost([
            'provider_split_enabled' => '1',
            'provider_type' => ['guest'],
            'provider_name_snapshot' => ['Dr. External Specialist'],
            'provider_split_mode' => ['fixed'],
            'provider_split_value' => ['250.00'],
            'provider_mark_paid_now' => ['0'],
        ]);

        $method = new \ReflectionMethod(Finance::class, 'normalizeProviderSplitPayloads');
        $payloads = $method->invoke($controller, 1, 1000.00);

        $this->assertCount(1, $payloads);
        $p = $payloads[0];
        $this->assertSame('guest', $p['provider_type']);
        $this->assertSame('Dr. External Specialist', $p['provider_name_snapshot']);
        $this->assertSame('fixed', $p['split_mode']);
        $this->assertSame(250.00, $p['provider_share_amount']);
        $this->assertSame(750.00, $p['clinic_share_amount']);
        $this->assertSame(0.00, $p['provider_paid_amount']);
        $this->assertSame(250.00, $p['provider_balance_due']);
        $this->assertSame('unpaid', $p['status']);
    }

    public function testProviderSplitRejectsTotalExceedingGrossAmount(): void
    {
        $controller = $this->createFinanceControllerWithPost([
            'provider_split_enabled' => '1',
            'provider_type' => ['user', 'guest'],
            'provider_user_id' => ['7', '0'],
            'provider_name_snapshot' => ['', 'Dr. External'],
            'provider_split_mode' => ['percent', 'percent'],
            'provider_split_value' => ['60.00', '50.00'], // 110% total
        ]);

        $mockClinicUser = $this->createMock(ClinicUserModel::class);
        $mockClinicUser->method('findActiveMembershipByClinicAndUser')->willReturn(['user_id' => 7]);
        $refClinicUser = new \ReflectionProperty(Finance::class, 'clinicUserModel');
        $refClinicUser->setValue($controller, $mockClinicUser);

        $method = new \ReflectionMethod(Finance::class, 'normalizeProviderSplitPayloads');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Total clinical provider compensation cannot exceed the collected amount.');
        $method->invoke($controller, 1, 1000.00);
    }

    public function testProviderSplitRejectsDuplicateProviderInSameTransaction(): void
    {
        $controller = $this->createFinanceControllerWithPost([
            'provider_split_enabled' => '1',
            'provider_type' => ['user', 'user'],
            'provider_user_id' => ['7', '7'],
            'provider_split_mode' => ['percent', 'percent'],
            'provider_split_value' => ['20.00', '20.00'],
        ]);

        $mockClinicUser = $this->createMock(ClinicUserModel::class);
        $mockClinicUser->method('findActiveMembershipByClinicAndUser')->willReturn(['user_id' => 7]);
        $refClinicUser = new \ReflectionProperty(Finance::class, 'clinicUserModel');
        $refClinicUser->setValue($controller, $mockClinicUser);

        $method = new \ReflectionMethod(Finance::class, 'normalizeProviderSplitPayloads');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Each clinical provider can only appear once per transaction.');
        $method->invoke($controller, 1, 1000.00);
    }

    public function testProviderSplitRejectsInactiveClinicUser(): void
    {
        $controller = $this->createFinanceControllerWithPost([
            'provider_split_enabled' => '1',
            'provider_type' => ['user'],
            'provider_user_id' => ['99'],
            'provider_split_mode' => ['percent'],
            'provider_split_value' => ['30.00'],
        ]);

        $mockClinicUser = $this->createMock(ClinicUserModel::class);
        $mockClinicUser->method('findActiveMembershipByClinicAndUser')->willReturn(null); // Inactive
        $refClinicUser = new \ReflectionProperty(Finance::class, 'clinicUserModel');
        $refClinicUser->setValue($controller, $mockClinicUser);

        $method = new \ReflectionMethod(Finance::class, 'normalizeProviderSplitPayloads');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Selected provider user is not active in this clinic.');
        $method->invoke($controller, 1, 1000.00);
    }

    public function testFixedShareAboveTransactionTotalIsCapped(): void
    {
        $controller = $this->createFinanceControllerWithPost([
            'provider_split_enabled' => '1', 'provider_type' => ['guest'],
            'provider_name_snapshot' => ['Synthetic Outside Provider'],
            'provider_split_mode' => ['fixed'], 'provider_split_value' => ['1250'],
        ]);
        $rows = (new \ReflectionMethod(Finance::class, 'normalizeProviderSplitPayloads'))
            ->invoke($controller, 1, 1000.0);
        $this->assertSame(1000.0, $rows[0]['provider_share_amount']);
        $this->assertSame(0.0, $rows[0]['clinic_share_amount']);
        $this->assertSame(1000.0, $rows[0]['provider_balance_due']);
    }

    public function testMultipleSharesRetainOneTransactionLevelClinicRemainder(): void
    {
        $controller = $this->createFinanceControllerWithPost([
            'provider_split_enabled' => '1', 'provider_type' => ['guest', 'guest'],
            'provider_name_snapshot' => ['Synthetic Provider A', 'Synthetic Provider B'],
            'provider_split_mode' => ['percent', 'fixed'], 'provider_split_value' => ['30', '200'],
        ]);
        $rows = (new \ReflectionMethod(Finance::class, 'normalizeProviderSplitPayloads'))
            ->invoke($controller, 1, 1000.0);
        $this->assertSame([300.0, 200.0], array_column($rows, 'provider_share_amount'));
        $this->assertSame([500.0, 500.0], array_column($rows, 'clinic_share_amount'));
        // The repeated clinic remainder is transaction-level, not additive per allocation.
        $this->assertSame(1000.0, array_sum(array_column($rows, 'provider_share_amount')) + $rows[0]['clinic_share_amount']);
    }

    private function summarizeCash(array $rows): array
    {
        $controller = (new \ReflectionClass(Finance::class))->newInstanceWithoutConstructor();
        return (new \ReflectionMethod(Finance::class, 'summarizeCashReport'))->invoke($controller, $rows);
    }

    private function cashRow(string $type, string $source, float $amount, int $sign): array
    {
        return [
            'event_type' => $type, 'event_source' => $source,
            'amount_base' => $amount, 'signed_amount_base' => $sign * $amount,
            'amount_original' => $amount, 'currency' => 'ILS',
            'payment_method' => 'cash', 'liquidity_account' => 'safe_cash',
        ];
    }

    public function testActiveCashSummarySeparatesExpensePayoutAndRefundOnce(): void
    {
        $stats = $this->summarizeCash([
            $this->cashRow('payment', 'finance_payment', 1000.0, 1),
            $this->cashRow('expense', 'finance_payment', 400.0, -1),
            $this->cashRow('provider_payout_payment', 'provider_payout_payment', 100.0, -1),
            $this->cashRow('refund', 'finance_payment', 50.0, -1),
        ]);
        $this->assertSame(1000.0, $stats['gross_collections']);
        $this->assertSame(400.0, $stats['expenses_paid']);
        $this->assertSame(100.0, $stats['provider_paid_cash_total']);
        $this->assertSame(50.0, $stats['refunds_total']);
        $this->assertSame(450.0, $stats['net_clinic_cash']);
        $this->assertSame(450.0, $stats['safe_cash_net']);
        $this->assertSame(4, $stats['total_transactions']);
    }

    public function testInternalTransfersAndNoncashAdjustmentsDoNotChangeCashResult(): void
    {
        $stats = $this->summarizeCash([
            $this->cashRow('payment', 'finance_payment', 1000.0, 1),
            $this->cashRow('transfer_out', 'liquidity_transfer', 300.0, -1),
            $this->cashRow('transfer_in', 'liquidity_transfer', 300.0, 1),
            $this->cashRow('adjustment', 'finance_payment', 200.0, -1),
        ]);
        $this->assertSame(1000.0, $stats['net_clinic_cash']);
        $this->assertSame(1000.0, $stats['safe_cash_net']);
        $this->assertSame(1, $stats['total_transactions']);
    }

    public function testUnconvertedProviderCurrencyWithholdsBaseCashTotals(): void
    {
        $nativePayout = $this->cashRow('provider_payout_payment', 'provider_payout_payment', 100.0, -1);
        $nativePayout['currency'] = 'USD';
        $nativePayout['amount_base'] = null;
        $nativePayout['signed_amount_base'] = null;
        $stats = $this->summarizeCash([$nativePayout]);
        $this->assertTrue($stats['provider_currency_unavailable']);
        $this->assertNull($stats['provider_paid_cash_total']);
        $this->assertNull($stats['net_clinic_cash']);
        $this->assertSame(100.0, $stats['provider_cash_summary']['currency_buckets']['USD']['total_provider_paid_cash']);
    }

    public function testViewSalaryCategoryPresetResolutionAndPrecedence(): void
    {
        helper(['asset', 'url', 'settings', 'form', 'navigation']);

        // Case 1: GET category=salaries sets categoryValue to salaries and isSalary to true
        $requestMock = $this->createMock(IncomingRequest::class);
        $requestMock->method('getGet')->with('category')->willReturn('salaries');
        $oldInput = [];
        $requestMock->method('getOldInput')->willReturnCallback(static function ($key) use (&$oldInput) {
            return $oldInput[$key] ?? null;
        });
        $siteUri = new \CodeIgniter\HTTP\SiteURI(config('App'), 'finance/expenses/create');
        $requestMock->method('getUri')->willReturn($siteUri);
        Services::injectMock('request', $requestMock);

        $data = [
            'studioExpense' => true,
            'studioInDialog' => true,
            'base_currency' => 'ILS',
            'expense_category_options' => ['salaries' => 'Salaries', 'rent' => 'Rent'],
            'supplier_directory' => [['id' => 1, 'name' => 'Synthetic Vendor']],
            'currency_options' => ['ILS'],
        ];

        $html = view('finance/partials/studio_transaction_form', $data);
        $this->assertStringContainsString('finance-expense-layout', $html);
        $this->assertStringContainsString('selected>Salaries</option>', $html);
        $this->assertStringContainsString('data-label-salary="', $html);
        $this->assertStringContainsString('<label for="expenseSupplierSelect">', $html);
        $this->assertStringContainsString('"salarySubmit":"' . lang('Finance.recordSalary') . '"', $html);
        $this->assertStringContainsString('"expenseSubmit":"' . lang('Finance.recordExpense') . '"', $html);
        $this->assertStringContainsString('"salaryTitle":"' . lang('Finance.recordSalary') . '"', $html);
        $this->assertStringContainsString('"expenseTitle":"' . lang('Finance.newExpense') . '"', $html);

        // The actual view must retain a corrected category/payee after failed validation.
        $oldInput = ['expense_category' => 'rent', 'counterparty_name' => 'Synthetic landlord'];
        $retryHtml = view('finance/partials/studio_transaction_form', $data);
        $this->assertStringContainsString('selected>Rent</option>', $retryHtml);
        $this->assertStringNotContainsString('selected>Salaries</option>', $retryHtml);
        $retryDoc = new \DOMDocument();
        @$retryDoc->loadHTML($retryHtml);
        $this->assertSame('Synthetic landlord', (new \DOMXPath($retryDoc))->query('//input[@name="counterparty_name"]')->item(0)->getAttribute('value'));
        $oldInput = [];

        // Case 2: Non-expense invoice retains finance-entry-layout and provider sections
        $invoiceData = [
            'studioExpense' => false,
            'studioInDialog' => true,
            'base_currency' => 'ILS',
            'currency_options' => ['ILS'],
            'transaction_type_preset' => 'invoice',
        ];
        $invoiceHtml = view('finance/partials/studio_transaction_form', $invoiceData);
        $this->assertStringContainsString('finance-entry-layout', $invoiceHtml);
        $this->assertStringContainsString('finance-entry-main', $invoiceHtml);
        $this->assertStringNotContainsString('finance-expense-layout', $invoiceHtml);

        // Case 3: Linked expense edit locks amount_paid to readonly
        $lockedExpenseData = [
            'studioExpense' => true,
            'studioEditing' => true,
            'expense_has_settlement_entries' => true,
            'finance' => [
                'id' => 101,
                'transaction_id' => 'EXP-101',
                'transaction_type' => 'expense',
                'expense_category' => 'rent',
                'amount' => 1000.00,
                'amount_paid' => 1000.00,
                'payment_status' => 'paid',
                'currency' => 'ILS',
            ],
            'base_currency' => 'ILS',
            'currency_options' => ['ILS'],
            'expense_category_options' => ['salaries' => 'Salaries', 'rent' => 'Rent'],
        ];
        $lockedHtml = view('finance/partials/studio_transaction_form', $lockedExpenseData);
        $this->assertStringContainsString('name="amount_paid" id="entry-amount-paid"', $lockedHtml);
        $this->assertStringContainsString('readonly', $lockedHtml);
        $this->assertStringContainsString('selected>Rent</option>', $lockedHtml, 'A salary query preset must not replace the existing record category.');
        $doc = new \DOMDocument();
        @$doc->loadHTML($lockedHtml);
        $paid = (new \DOMXPath($doc))->query('//input[@name="amount_paid"]')->item(0);
        $this->assertNotNull($paid);
        $this->assertTrue($paid->hasAttribute('readonly'));
        $this->assertSame('1000', $paid->getAttribute('value'));
    }

    private function mockRequestWithSiteUri(string $path = 'finance', array $oldInput = []): IncomingRequest
    {
        $requestMock = $this->createMock(IncomingRequest::class);
        $requestMock->method('getOldInput')->willReturnCallback(static fn ($key) => $oldInput[$key] ?? null);
        $siteUri = new \CodeIgniter\HTTP\SiteURI(config('App'), $path);
        $requestMock->method('getUri')->willReturn($siteUri);
        Services::injectMock('request', $requestMock);
        return $requestMock;
    }

    public function testProviderAllocationsViewRequiresAnExplicitRateWithoutArbitraryDefault(): void
    {
        helper(['asset', 'url', 'settings', 'form']);
        $this->mockRequestWithSiteUri();
        $html = view('finance/partials/studio_provider_allocations', [
            'clinic_providers' => [
                ['id' => 12, 'first_name' => 'Doctor', 'last_name' => 'One'],
            ],
            'provider_payouts' => [],
            'base_currency' => 'ILS',
        ]);

        $decoded = html_entity_decode($html, ENT_QUOTES);
        $this->assertStringContainsString('data-provider-split-section', $html);
        $this->assertStringContainsString('"split_value":""', $decoded);
        $this->assertStringNotContainsString('"split_value":"70"', $decoded);
        $this->assertStringContainsString(lang('Finance.providerBasisHelp'), $decoded);
        $this->assertStringContainsString(lang('Finance.transactionBasis'), $decoded);
        $this->assertStringNotContainsString(lang('Finance.grossCollected'), $decoded);
    }

    public function testProviderAllocationsPreservesPersistedAndOldInputRates(): void
    {
        helper(['asset', 'url', 'settings', 'form']);
        $this->mockRequestWithSiteUri();

        // Case 1: Persisted payouts preserve stored rates
        $htmlPersisted = view('finance/partials/studio_provider_allocations', [
            'clinic_providers' => [
                ['id' => 12, 'first_name' => 'Doctor', 'last_name' => 'One'],
            ],
            'provider_payouts' => [
                [
                    'id' => 88,
                    'provider_type' => 'user',
                    'provider_user_id' => 12,
                    'split_mode' => 'percent',
                    'split_value' => '45.00',
                ],
                [
                    'id' => 89,
                    'provider_type' => 'guest',
                    'provider_name_snapshot' => 'Outside Specialist',
                    'split_mode' => 'fixed',
                    'split_value' => '250.00',
                ],
            ],
            'base_currency' => 'ILS',
        ]);

        $decodedPersisted = html_entity_decode($htmlPersisted, ENT_QUOTES);
        $this->assertStringContainsString('"split_value":"45.00"', $decodedPersisted);
        $this->assertStringContainsString('"split_value":"250.00"', $decodedPersisted);
        $this->assertStringNotContainsString('"split_value":"70"', $decodedPersisted);

        // Case 2: Old input from validation retry is preserved
        $oldInput = [
            'provider_type' => ['user'],
            'provider_user_id' => ['12'],
            'provider_split_mode' => ['percent'],
            'provider_split_value' => ['33.50'],
        ];
        $this->mockRequestWithSiteUri('finance', $oldInput);

        $htmlOldInput = view('finance/partials/studio_provider_allocations', [
            'clinic_providers' => [
                ['id' => 12, 'first_name' => 'Doctor', 'last_name' => 'One'],
            ],
            'provider_payouts' => [],
            'base_currency' => 'ILS',
        ]);

        $decodedOldInput = html_entity_decode($htmlOldInput, ENT_QUOTES);
        $this->assertStringContainsString('"split_value":"33.50"', $decodedOldInput);
    }

    public function testStudioMoneyConfigProvidesPaymentHistoryAndProviderLabels(): void
    {
        $fixture = dirname(__DIR__) . '/_support/fixtures/MoneyConfigPresenter.php';
        $html = shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($fixture));
        $this->assertIsString($html);
        $doc = new \DOMDocument();
        @$doc->loadHTML($html);
        $config = json_decode($doc->getElementsByTagName('script')->item(0)->textContent, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(['view'=>false,'edit'=>false,'delete'=>false,'export'=>false,'supplier'=>false], $config['permissions']);

        $this->assertStringContainsString('"paymentHistory":"' . lang('Finance.paymentHistory') . '"', $html);
        $this->assertStringContainsString('"clinicUser":"' . lang('Finance.clinicUser') . '"', $html);
        $this->assertStringContainsString('"guestProvider":"' . lang('Finance.guestProvider') . '"', $html);
        $this->assertStringContainsString('"transactionBasis":"' . lang('Finance.transactionBasis') . '"', $html);
    }

    public function testPayablesPresenterConfigProvidesLocalizedExpenseCategories(): void
    {
        $fixture = dirname(__DIR__) . '/_support/fixtures/MoneyConfigPresenter.php';
        $html = shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($fixture) . ' ar payables');
        $this->assertIsString($html);
        $doc = new \DOMDocument();
        @$doc->loadHTML($html);
        $config = json_decode($doc->getElementsByTagName('script')->item(0)->textContent, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('payables', $config['mode']);
        $this->assertArrayHasKey('expenseCategories', $config);
        $this->assertSame('رواتب', $config['expenseCategories']['salaries'] ?? null);
        $this->assertCount(1, $config['localRows']);
        $this->assertSame('salaries', $config['localRows'][0]['expense_category']);
    }
}
