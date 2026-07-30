<?php

namespace App\Services;

use DateTimeImmutable;

final class TodayRoutingService
{
    private const GROUPS = [
        'expected' => ['scheduled', 'confirmed'],
        'waiting' => ['arrived', 'ready'],
        'chairside' => ['in_chair'],
        'checkout' => ['checkout'],
        'exceptions' => ['cancelled', 'no_show'],
    ];

    private const PRIMARY_NEXT_STAGE = [
        'scheduled' => 'arrived',
        'confirmed' => 'arrived',
        'arrived' => 'ready',
        'ready' => 'in_chair',
        'in_chair' => 'checkout',
        'checkout' => 'completed',
    ];

    public function build(array $appointments, ?DateTimeImmutable $now = null): array
    {
        $now ??= new DateTimeImmutable();
        $groups = array_fill_keys(array_keys(self::GROUPS), []);
        $completedCount = 0;

        foreach ($appointments as $appointment) {
            $status = (string) ($appointment['status'] ?? 'scheduled');
            if ($status === 'completed') {
                $completedCount++;
                continue;
            }

            $group = $this->groupFor($status);
            if ($group === null) {
                continue;
            }

            $scheduledAt = $this->scheduledAt($appointment);
            $isLate = in_array($status, ['scheduled', 'confirmed'], true)
                && $scheduledAt !== null
                && $scheduledAt < $now;

            $appointment['routing_group'] = $group;
            $appointment['primary_next_stage'] = self::PRIMARY_NEXT_STAGE[$status] ?? null;
            $appointment['is_late'] = $isLate;
            $appointment['late_minutes'] = $isLate
                ? max(1, (int) floor(($now->getTimestamp() - $scheduledAt->getTimestamp()) / 60))
                : 0;
            $groups[$group][] = $appointment;
        }

        foreach ($groups as &$rows) {
            usort($rows, static function (array $left, array $right): int {
                return strcmp(
                    (string) (($left['appointment_date'] ?? '') . ' ' . ($left['appointment_time'] ?? '')),
                    (string) (($right['appointment_date'] ?? '') . ' ' . ($right['appointment_time'] ?? ''))
                );
            });
        }
        unset($rows);

        return [
            'groups' => $groups,
            'counts' => array_map('count', $groups),
            'completed_count' => $completedCount,
            'open_count' => count($groups['expected'])
                + count($groups['waiting'])
                + count($groups['chairside'])
                + count($groups['checkout']),
        ];
    }

    private function groupFor(string $status): ?string
    {
        foreach (self::GROUPS as $group => $statuses) {
            if (in_array($status, $statuses, true)) {
                return $group;
            }
        }

        return null;
    }

    private function scheduledAt(array $appointment): ?DateTimeImmutable
    {
        $date = trim((string) ($appointment['appointment_date'] ?? ''));
        $time = trim((string) ($appointment['appointment_time'] ?? ''));
        if ($date === '' || $time === '') {
            return null;
        }

        $value = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time)
            ?: DateTimeImmutable::createFromFormat('Y-m-d H:i', $date . ' ' . $time);

        return $value ?: null;
    }
}
