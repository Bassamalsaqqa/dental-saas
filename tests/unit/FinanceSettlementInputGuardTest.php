<?php

namespace Tests\unit;

use App\Controllers\Finance;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIUnitTestCase;

final class FinanceSettlementInputGuardTest extends CIUnitTestCase
{
    private function invokeSettlementInput(array $posted): void
    {
        $request = $this->createMock(IncomingRequest::class);
        $request->method('getPost')->willReturnCallback(static fn ($key = null) => $posted[$key] ?? null);
        $controller = (new \ReflectionClass(Finance::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty($controller, 'request'))->setValue($controller, $request);
        $method = new \ReflectionMethod($controller, 'normalizeFinanceSettlementInput');
        $method->invoke($controller, ['payment_method' => 'cash'], 100.0);
    }

    public function testMalformedAmountsAreRejectedBeforeAnySettingsQuery(): void
    {
        foreach (['10garbage', '1e999', INF, NAN, ['nested'], -1, 0] as $amount) {
            try {
                $this->invokeSettlementInput(['amount_original' => $amount]);
                $this->fail('Malformed or nonpositive cash input was accepted.');
            } catch (\DomainException $e) {
                $this->assertStringContainsString('Settlement amount', $e->getMessage());
            }
        }
    }

    public function testMalformedMethodsAreRejectedBeforeAnySettingsQuery(): void
    {
        foreach (['crypto', ['cash'], [], '0', 0, false] as $method) {
            try {
                $this->invokeSettlementInput(['amount_original' => '10.00', 'payment_method' => $method]);
                $this->fail('Unsupported payment method was accepted.');
            } catch (\DomainException $e) {
                $this->assertSame('Invalid payment method.', $e->getMessage());
            }
        }
    }

    public function testMalformedExchangeRatesAreRejectedBeforeAnySettingsQuery(): void
    {
        foreach (['3garbage', '1e999', INF, ['rate'], -1, 0] as $rate) {
            try {
                $this->invokeSettlementInput(['amount_original' => '10.00', 'exchange_rate' => $rate]);
                $this->fail('Malformed or nonpositive rate was accepted.');
            } catch (\DomainException $e) {
                $this->assertSame('Exchange rate must be a finite positive number.', $e->getMessage());
            }
        }
    }
}
