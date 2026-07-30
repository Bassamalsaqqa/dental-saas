<?php

namespace Tests\Support;

use App\Services\TodayRoutingService;
use CodeIgniter\Test\CIUnitTestCase;
use DateTimeImmutable;

final class TodayRoutingServiceTest extends CIUnitTestCase
{
    public function testGroupsVisitsAndDerivesOnePrimaryHandoff(): void
    {
        $result = (new TodayRoutingService())->build([
            $this->appointment(1, 'scheduled', '08:30:00'),
            $this->appointment(2, 'arrived', '09:00:00'),
            $this->appointment(3, 'ready', '09:15:00'),
            $this->appointment(4, 'in_chair', '09:30:00'),
            $this->appointment(5, 'checkout', '10:00:00'),
            $this->appointment(6, 'completed', '10:30:00'),
            $this->appointment(7, 'no_show', '11:00:00'),
        ], new DateTimeImmutable('2026-07-30 09:00:00'));

        $this->assertSame(1, $result['counts']['expected']);
        $this->assertSame(2, $result['counts']['waiting']);
        $this->assertSame(1, $result['counts']['chairside']);
        $this->assertSame(1, $result['counts']['checkout']);
        $this->assertSame(1, $result['counts']['exceptions']);
        $this->assertSame(1, $result['completed_count']);
        $this->assertSame(5, $result['open_count']);
        $this->assertSame('arrived', $result['groups']['expected'][0]['primary_next_stage']);
        $this->assertSame('ready', $result['groups']['waiting'][0]['primary_next_stage']);
        $this->assertSame('in_chair', $result['groups']['waiting'][1]['primary_next_stage']);
    }

    public function testOnlyExpectedPatientsBecomeLate(): void
    {
        $result = (new TodayRoutingService())->build([
            $this->appointment(1, 'confirmed', '08:00:00'),
            $this->appointment(2, 'arrived', '08:00:00'),
            $this->appointment(3, 'scheduled', '10:00:00'),
        ], new DateTimeImmutable('2026-07-30 09:15:00'));

        $this->assertTrue($result['groups']['expected'][0]['is_late']);
        $this->assertSame(75, $result['groups']['expected'][0]['late_minutes']);
        $this->assertFalse($result['groups']['expected'][1]['is_late']);
        $this->assertFalse($result['groups']['waiting'][0]['is_late']);
    }

    public function testTerminalAndUnknownRowsHaveNoPrimaryAction(): void
    {
        $result = (new TodayRoutingService())->build([
            $this->appointment(1, 'cancelled', '08:00:00'),
            $this->appointment(2, 'no_show', '09:00:00'),
            $this->appointment(3, 'legacy_unknown', '10:00:00'),
        ], new DateTimeImmutable('2026-07-30 09:15:00'));

        $this->assertCount(2, $result['groups']['exceptions']);
        $this->assertNull($result['groups']['exceptions'][0]['primary_next_stage']);
        $this->assertNull($result['groups']['exceptions'][1]['primary_next_stage']);
        $this->assertSame(0, $result['open_count']);
    }

    private function appointment(int $id, string $status, string $time): array
    {
        return [
            'id' => $id,
            'status' => $status,
            'appointment_date' => '2026-07-30',
            'appointment_time' => $time,
        ];
    }
}
