<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\I18n\Time;
use DateTimeImmutable;
use DateTimeZone;

/** One clinic-local clock and interval policy for reports, edit proofs and writes. */
final class ClinicTemporalPolicyService
{
    public const STATE_UPCOMING = 'upcoming';
    public const STATE_IN_PROGRESS = 'in_progress';
    public const STATE_ELAPSED = 'elapsed';
    public const STATE_INVALID = 'invalid';

    public static function now(?DateTimeZone $tz = null): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface(Time::now($tz ?? new DateTimeZone('UTC')));
    }

    public static function classifyAppointment(
        string $date,
        string $time,
        int $duration,
        DateTimeZone $tz,
        ?DateTimeImmutable $now = null
    ): string {
        // MySQL TIME adds zero seconds to minute-resolution bookings. Preserve corrupt or
        // unsupported existing times for operator review; never normalize them into eligibility.
        if (preg_match('/^([0-2][0-9]:[0-5][0-9])(?::00)?$/D', $time, $match) !== 1
            || ClinicScheduleAvailabilityService::validateAppointmentInterval($date, $match[1], $duration, $tz) !== null) {
            return self::STATE_INVALID;
        }
        $start = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $date . ' ' . $match[1], $tz);
        $end = $start->modify('+' . $duration . ' minutes');
        $reference = $now ?? self::now($tz);
        if ($start >= $reference) {
            return self::STATE_UPCOMING;
        }
        return $reference < $end ? self::STATE_IN_PROGRESS : self::STATE_ELAPSED;
    }

    public static function isUpcoming(string $date, string $time, int $duration, DateTimeZone $tz, ?DateTimeImmutable $now = null): bool
    {
        return self::classifyAppointment($date, $time, $duration, $tz, $now) === self::STATE_UPCOMING;
    }
}
