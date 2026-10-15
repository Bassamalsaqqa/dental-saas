<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Shared pure availability helper and interval validator used by canonical booking
 * and pre-pilot schedule impact evaluation.
 */
final class ClinicScheduleAvailabilityService
{
    /**
     * Pure validation of appointment date, time, duration, midnight crossing, and DST intervals.
     * Rejects gaps (spring forward non-existent time) and folds (fall back ambiguous time).
     *
     * @return array{compliant: bool, code: string, reason: string}|null Null if valid, error array if invalid.
     */
    public static function validateAppointmentInterval(
        string $date,
        string $time,
        int $duration,
        DateTimeZone $tz
    ): ?array {
        if (!ClinicSchedulingSettings::validDate($date)) {
            return ['compliant' => false, 'code' => 'INVALID_DATE', 'reason' => 'Invalid date format (YYYY-MM-DD required)'];
        }

        if (!ClinicSchedulingSettings::validTime($time)) {
            return ['compliant' => false, 'code' => 'INVALID_TIME', 'reason' => 'Invalid time format (HH:MM required)'];
        }

        if ($duration < 1 || $duration > 480) {
            return ['compliant' => false, 'code' => 'INVALID_DURATION', 'reason' => 'Duration must be between 1 and 480 minutes'];
        }

        $startMinutes = ((int) substr($time, 0, 2) * 60) + (int) substr($time, 3, 2);
        $endMinutes = $startMinutes + $duration;

        if ($endMinutes > 1440) {
            return ['compliant' => false, 'code' => 'CROSS_MIDNIGHT', 'reason' => 'Appointments cannot cross midnight'];
        }

        $endTimeStr = sprintf('%02d:%02d', intdiv($endMinutes, 60), $endMinutes % 60);

        // Gap test: check whether start and end local timestamps exist in local time
        $dtStart = DateTimeImmutable::createFromFormat('!Y-m-d H:i', "{$date} {$time}", $tz);
        if (!$dtStart || $dtStart->format('Y-m-d H:i') !== "{$date} {$time}") {
            return [
                'compliant' => false,
                'code' => 'INVALID_DST_INTERVAL',
                'reason' => 'The selected appointment time does not exist in local time (daylight saving time gap)',
            ];
        }

        $dtEnd = DateTimeImmutable::createFromFormat('!Y-m-d H:i', "{$date} {$endTimeStr}", $tz);
        if (!$dtEnd || $dtEnd->format('Y-m-d H:i') !== "{$date} {$endTimeStr}") {
            return [
                'compliant' => false,
                'code' => 'INVALID_DST_INTERVAL',
                'reason' => 'The appointment interval falls into a local daylight saving time gap',
            ];
        }

        // Transition scan: inspect transitions crossing this local day
        $dayStartUtc = strtotime("{$date} 00:00:00 UTC") - 86400;
        $dayEndUtc = strtotime("{$date} 23:59:59 UTC") + 86400;
        $transitions = $tz->getTransitions($dayStartUtc, $dayEndUtc);

        if (is_array($transitions) && count($transitions) > 1) {
            $apptStartNaiveSec = strtotime("{$date} {$time}:00 UTC");
            $apptEndNaiveSec = strtotime("{$date} {$endTimeStr}:00 UTC");

            for ($i = 1; $i < count($transitions); $i++) {
                $tr = $transitions[$i];
                $prev = $transitions[$i - 1];
                $offsetDelta = $tr['offset'] - $prev['offset'];

                if ($offsetDelta === 0) {
                    continue;
                }

                $transTs = $tr['ts'];
                $localBefore = $transTs + $prev['offset'];
                $localAfter = $transTs + $tr['offset'];

                if ($offsetDelta > 0) {
                    // Gap (Spring forward): [localBefore, localAfter) does not exist
                    if ($apptStartNaiveSec < $localAfter && $apptEndNaiveSec > $localBefore) {
                        return [
                            'compliant' => false,
                            'code' => 'INVALID_DST_INTERVAL',
                            'reason' => 'The selected appointment time crosses a local daylight saving time gap',
                        ];
                    }
                } else {
                    // Fold (Fall back): [localAfter, localBefore] occurs twice (ambiguous)
                    if ($apptStartNaiveSec < $localBefore && $apptEndNaiveSec > $localAfter) {
                        return [
                            'compliant' => false,
                            'code' => 'AMBIGUOUS_DST_INTERVAL',
                            'reason' => 'The selected appointment time is ambiguous due to daylight saving time transition',
                        ];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Resolve effective working intervals on a specific date for a provider profile.
     * Returns array of windows or error code if profile is corrupt/non-compliant.
     *
     * @return array{0: array<array{start: string, end: string}>|null, 1: string|null} [windows, errorCode]
     */
    public static function resolveEffectiveWindows(
        string $date,
        array $clinicHours,
        ?array $profileData,
        ?array $profileOverride = null
    ): array {
        if ($profileOverride !== null) {
            $isBookable = (bool) ($profileOverride['is_bookable'] ?? true);
            if (!$isBookable) {
                return [null, 'PROVIDER_NOT_BOOKABLE'];
            }
            $exceptions = $profileOverride['exceptions'] ?? null;
            $weeklyHours = $profileOverride['weekly_hours'] ?? null;
        } elseif ($profileData !== null) {
            $isBookable = ((int) ($profileData['is_bookable'] ?? 1) === 1);
            if (!$isBookable) {
                return [null, 'PROVIDER_NOT_BOOKABLE'];
            }
            $weeklyHours = null;
            if (!empty($profileData['weekly_hours_json'])) {
                try {
                    $decoded = json_decode((string) $profileData['weekly_hours_json'], true, 512, JSON_THROW_ON_ERROR);
                    if (!is_array($decoded)) {
                        return [null, 'PROVIDER_PROFILE_CORRUPT'];
                    }
                    $weeklyHours = $decoded;
                } catch (\Throwable $e) {
                    return [null, 'PROVIDER_PROFILE_CORRUPT'];
                }
            } elseif (isset($profileData['weekly_hours']) && is_array($profileData['weekly_hours'])) {
                $weeklyHours = $profileData['weekly_hours'];
            }

            $exceptions = null;
            if (!empty($profileData['exceptions_json'])) {
                try {
                    $decoded = json_decode((string) $profileData['exceptions_json'], true, 512, JSON_THROW_ON_ERROR);
                    if (!is_array($decoded)) {
                        return [null, 'PROVIDER_PROFILE_CORRUPT'];
                    }
                    $exceptions = $decoded;
                } catch (\Throwable $e) {
                    return [null, 'PROVIDER_PROFILE_CORRUPT'];
                }
            } elseif (isset($profileData['exceptions']) && is_array($profileData['exceptions'])) {
                $exceptions = $profileData['exceptions'];
            }
        } else {
            // Inherits clinic working hours
            return [[['start' => $clinicHours['start'], 'end' => $clinicHours['end']]], null];
        }

        $dayOfWeek = strtolower((new DateTimeImmutable($date))->format('l'));

        $windows = null;
        if (is_array($exceptions) && array_key_exists($date, $exceptions)) {
            $windows = $exceptions[$date];
            if (!is_array($windows)) {
                return [null, 'PROVIDER_PROFILE_CORRUPT'];
            }
            if (empty($windows)) {
                return [[], 'PROVIDER_ON_LEAVE'];
            }
        } elseif ($weeklyHours !== null) {
            if (!is_array($weeklyHours)) {
                return [null, 'PROVIDER_PROFILE_CORRUPT'];
            }
            $windows = $weeklyHours[$dayOfWeek] ?? [];
            if (!is_array($windows)) {
                return [null, 'PROVIDER_PROFILE_CORRUPT'];
            }
            if (empty($windows)) {
                return [[], 'OUTSIDE_PROVIDER_HOURS'];
            }
        } else {
            $windows = [['start' => $clinicHours['start'], 'end' => $clinicHours['end']]];
        }

        $cleanWindows = [];
        foreach ($windows as $w) {
            if (!is_array($w) || !isset($w['start'], $w['end']) || !is_string($w['start']) || !is_string($w['end'])) {
                return [null, 'PROVIDER_PROFILE_CORRUPT'];
            }
            if (!ClinicSchedulingSettings::validTime($w['start']) || !ClinicSchedulingSettings::validTime($w['end']) || $w['start'] >= $w['end']) {
                return [null, 'PROVIDER_PROFILE_CORRUPT'];
            }
            $start = max($w['start'], $clinicHours['start']);
            $end = min($w['end'], $clinicHours['end']);
            if ($start < $end) {
                $cleanWindows[] = ['start' => $start, 'end' => $end];
            }
        }

        return [$cleanWindows, null];
    }
}
