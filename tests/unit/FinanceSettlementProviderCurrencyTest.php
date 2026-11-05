<?php

namespace Tests\unit;

use App\Services\FinanceSettlementRequestService;
use App\Services\SettingsService;
use CodeIgniter\Database\MySQLi\Connection;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use PHPUnit\Framework\Attributes\DataProvider;

/** No driver connection is permitted; only the locked-read disclosure is exercised. */
final class ProviderCurrencyFixtureConnection extends Connection
{
    public ?\Closure $queryCallback = null;
    public ?BaseBuilder $builder = null;

    public function __construct()
    {
        parent::__construct(['database' => 'synthetic_unused', 'DBDriver' => 'MySQLi']);
    }

    public function connect(bool $persistent = false)
    {
        throw new \LogicException('This fixture must never connect to a database.');
    }

    public function transBegin(bool $testMode = false): bool { $this->transDepth = 1; return true; }
    public function transStatus(): bool { return true; }
    public function transCommit(): bool { $this->transDepth = 0; return true; }
    public function transRollback(): bool { $this->transDepth = 0; return true; }
    public function tableExists(string $tableName, bool $cached = true): bool { return false; }

    public function table($tableName): BaseBuilder
    {
        if ($tableName !== 'provider_payouts') {
            throw new \LogicException('Unexpected fixture table: ' . $tableName);
        }
        return $this->builder;
    }

    public function query(string $sql, $binds = null, bool $setArgs = true, string $queryClass = ''): BaseResult
    {
        return ($this->queryCallback)($sql, $binds);
    }
}

final class FinanceSettlementProviderCurrencyTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $settings = $this->createMock(SettingsService::class);
        $settings->method('get')->willReturn('GBP');
        Services::injectMock('settings', $settings);
        helper('settings');
    }

    public static function providerCurrencies(): iterable
    {
        foreach (['provider_payment', 'provider_mark_paid'] as $operation) {
            foreach ([
                'different from parent' => ['ILS', 'ILS'],
                'same as parent' => ['USD', 'USD'],
                'normalized native currency' => [' eur ', 'EUR'],
                'empty' => ['', 'UNKNOWN'],
                'whitespace' => ['   ', 'UNKNOWN'],
                'null' => [null, 'UNKNOWN'],
                'invalid' => ['invalid', 'UNKNOWN'],
                'explicit unknown' => ['UNKNOWN', 'UNKNOWN'],
            ] as $label => [$stored, $expected]) {
                yield $operation . ': ' . $label => [$operation, $stored, $expected, false];
            }
            yield $operation . ': missing' => [$operation, null, 'UNKNOWN', true];
        }
    }

    #[DataProvider('providerCurrencies')]
    public function testDisclosesTargetPayoutCurrencyWithoutGuessing(
        string $operation, ?string $storedCurrency, string $expectedCurrency, bool $missing
    ): void {
        $parent = [
            'id' => 10, 'clinic_id' => 1, 'transaction_id' => 'SYNTHETIC-001',
            'currency' => 'USD', 'total_amount' => '100.00', 'amount_paid' => '0.00',
            'balance_due' => '100.00', 'payment_status' => 'pending',
        ];
        $payout = [
            'id' => 42, 'clinic_id' => 1, 'finance_id' => 10, 'currency' => $storedCurrency,
            'provider_share_amount' => '30.00', 'provider_paid_amount' => '5.00',
            'provider_balance_due' => '25.00', 'status' => 'partial',
        ];
        if ($missing) {
            unset($payout['currency']);
        }
        // The first allocation must never supply currency or facts for another target.
        $otherPayout = array_replace($payout, [
            'id' => 41, 'currency' => 'JOD', 'provider_share_amount' => '70.00',
            'provider_balance_due' => '65.00',
        ]);
        $db = new ProviderCurrencyFixtureConnection();
        $builder = $this->createMock(BaseBuilder::class);
        $builder->expects($this->exactly(2))->method('where')
            ->willReturnCallback(function ($key, $value) use ($builder) {
                $this->assertSame(['clinic_id' => 1, 'id' => 42][$key], $value);
                return $builder;
            });
        $lookup = $this->createMock(BaseResult::class);
        $lookup->method('getRowArray')->willReturn($payout);
        $builder->method('get')->willReturn($lookup);
        $db->builder = $builder;
        $db->queryCallback = function (string $sql, array $binds) use ($parent, $payout, $otherPayout): BaseResult {
            $result = $this->createMock(BaseResult::class);
            if (str_contains($sql, 'FROM `finances`')) {
                $this->assertStringContainsString('FOR UPDATE', $sql);
                $this->assertSame([1, 10], $binds);
                $result->method('getRowArray')->willReturn($parent);
            } elseif (str_contains($sql, 'FROM `provider_payouts`')) {
                $this->assertStringContainsString('FOR UPDATE', $sql);
                $this->assertSame([1, 10], $binds);
                $result->method('getResultArray')->willReturn([$otherPayout, $payout]);
            } elseif (str_contains($sql, 'FROM `provider_payout_payments`')) {
                $this->assertContains($binds, [[1, 41], [1, 42]]);
                $result->method('getResultArray')->willReturn([]);
            } elseif (str_contains($sql, 'FROM `finance_payments`')) {
                $this->assertSame([1, 10], $binds);
                $result->method('getResultArray')->willReturn([]);
            } else {
                throw new \LogicException('Unexpected SQL in read-only fixture.');
            }
            return $result;
        };
        $service = new FinanceSettlementRequestService($db);
        // Schema certification is separate; this test only exercises disclosure.
        (new \ReflectionProperty($service, 'schemaChecked'))->setValue($service, true);
        $context = $service->issue(1, 99, $operation, [42]);

        $this->assertSame($expectedCurrency, $context['currency']);
        $this->assertSame(['paid' => '5.00', 'due' => '25.00', 'total' => '30.00'], $context['facts']);
        $this->assertSame(10, $context['finance_id']);
        $this->assertSame(FinanceSettlementRequestService::parentVersion($parent), $context['parent_version']);
        $this->assertSame(0, $db->transDepth);
    }
}
