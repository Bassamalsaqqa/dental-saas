<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\InvoiceReminderFormatter;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Test\CIUnitTestCase;
use DomainException;

final class InvoiceReminderFormatterTest extends CIUnitTestCase
{
    private function createMockDb(array $clinicSettings): BaseConnection
    {
        $db = $this->createMock(BaseConnection::class);
        $builder = $this->createMock(BaseBuilder::class);
        $result = $this->createMock(BaseResult::class);

        $currentClinicId = null;

        $db->method('table')->with('settings')->willReturn($builder);
        $builder->method('select')->willReturnSelf();
        $builder->method('where')->willReturnCallback(function ($key, $val = null) use (&$currentClinicId, $builder) {
            if ($key === 'clinic_id') {
                $currentClinicId = $val;
            } elseif (is_array($key) && isset($key['clinic_id'])) {
                $currentClinicId = $key['clinic_id'];
            }
            return $builder;
        });
        $builder->method('whereIn')->willReturnSelf();
        $builder->method('get')->willReturn($result);
        $result->method('getResultArray')->willReturnCallback(function () use (&$currentClinicId, $clinicSettings) {
            $settings = $clinicSettings[$currentClinicId] ?? [];
            $rows = [];
            foreach ($settings as $k => $v) {
                $rows[] = ['setting_key' => $k, 'setting_value' => $v];
            }
            return $rows;
        });

        return $db;
    }

    public function testFormatAmountUsesExplicitClinicCurrency(): void
    {
        $db = $this->createMockDb([
            1 => ['currency' => 'ILS', 'date_format' => 'd/m/Y', 'timezone' => 'Asia/Jerusalem'],
            2 => ['currency' => 'USD', 'date_format' => 'Y-m-d', 'timezone' => 'America/New_York'],
            3 => ['currency' => 'EUR', 'date_format' => 'd.m.Y', 'timezone' => 'Europe/Berlin'],
            4 => ['currency' => 'UNKNOWN', 'date_format' => 'd/m/Y', 'timezone' => 'UTC'],
        ]);

        $formatter = new InvoiceReminderFormatter($db);

        $this->assertSame('₪100.00', $formatter->formatAmount(1, 100.0));
        $this->assertSame('$100.00', $formatter->formatAmount(2, 100.0));
        $this->assertSame('€100.00', $formatter->formatAmount(3, 100.0));
        $this->assertSame('$100.00', $formatter->formatAmount(4, 100.0));
    }

    public function testFormatDueDatePreservesCalendarDate(): void
    {
        $db = $this->createMockDb([
            1 => ['currency' => 'ILS', 'date_format' => 'd/m/Y', 'timezone' => 'Asia/Jerusalem'],
            2 => ['currency' => 'USD', 'date_format' => 'Y-m-d', 'timezone' => 'America/New_York'],
            3 => ['currency' => 'USD', 'date_format' => 'm/d/Y', 'timezone' => 'America/Chicago'],
        ]);

        $formatter = new InvoiceReminderFormatter($db);

        $this->assertSame('10/06/2097', $formatter->formatDueDate(1, '2097-06-10'));
        $this->assertSame('2097-06-10', $formatter->formatDueDate(2, '2097-06-10'));
        $this->assertSame('06/10/2097', $formatter->formatDueDate(3, '2097-06-10'));
        $this->assertSame('', $formatter->formatDueDate(1, null));
        $this->assertSame('', $formatter->formatDueDate(1, ''));
    }

    public function testClinicLocalTodayAndCalendarDateTimezone(): void
    {
        $db = $this->createMockDb([
            1 => ['currency' => 'ILS', 'date_format' => 'Y-m-d T', 'timezone' => 'Asia/Jerusalem'],
            2 => ['currency' => 'USD', 'date_format' => 'Y-m-d T', 'timezone' => 'America/New_York'],
        ]);
        $formatter = new InvoiceReminderFormatter($db);
        $instant = new \DateTimeImmutable('2026-06-10T01:00:00Z');
        $this->assertSame('2026-06-10', $formatter->currentDate(1, $instant));
        $this->assertSame('2026-06-09', $formatter->currentDate(2, $instant));
        $this->assertSame('2097-06-10 EDT', $formatter->formatDueDate(2, '2097-06-10'));
    }

    public function testInvalidCalendarDateFailsClosed(): void
    {
        $formatter = new InvoiceReminderFormatter($this->createMockDb([1 => ['timezone' => 'UTC']]));
        $this->expectException(DomainException::class);
        $formatter->formatDueDate(1, '2026-02-30');
    }

    public function testDefaultTimezoneWhenMissing(): void
    {
        $db = $this->createMockDb([
            1 => ['currency' => 'ILS', 'date_format' => 'd/m/Y'],
        ]);

        $formatter = new InvoiceReminderFormatter($db);
        $tz = $formatter->getTimezoneForClinic(1);

        $this->assertSame('Asia/Jerusalem', $tz->getName());
    }

    public function testInvalidTimezoneFailsClosed(): void
    {
        $db = $this->createMockDb([
            1 => ['currency' => 'ILS', 'date_format' => 'd/m/Y', 'timezone' => 'Invalid/Zone'],
        ]);

        $formatter = new InvoiceReminderFormatter($db);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid clinic timezone.');
        $formatter->formatAmount(1, 50.0);
    }

    public function testEmptyTimezoneFailsClosed(): void
    {
        $db = $this->createMockDb([
            1 => ['currency' => 'ILS', 'date_format' => 'd/m/Y', 'timezone' => ''],
        ]);

        $formatter = new InvoiceReminderFormatter($db);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid clinic timezone.');
        $formatter->formatDueDate(1, '2097-06-10');
    }

    public function testInvalidClinicIdFailsClosed(): void
    {
        $db = $this->createMockDb([]);
        $formatter = new InvoiceReminderFormatter($db);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Clinic context required.');
        $formatter->formatAmount(0, 100.0);
    }
}
