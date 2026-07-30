<?php

namespace App\Services;

final class AppointmentVisitStageService
{
    public const STAGES = [
        'scheduled', 'confirmed', 'arrived', 'ready', 'in_chair',
        'checkout', 'completed', 'cancelled', 'no_show',
    ];

    private const TRANSITIONS = [
        'scheduled' => ['confirmed', 'arrived', 'cancelled', 'no_show'],
        'confirmed' => ['arrived', 'cancelled', 'no_show'],
        'arrived' => ['ready', 'in_chair', 'cancelled'],
        'ready' => ['in_chair', 'cancelled'],
        'in_chair' => ['checkout', 'completed'],
        'checkout' => ['completed'],
        'completed' => [],
        'cancelled' => [],
        'no_show' => [],
    ];

    public function isKnown(string $stage): bool
    {
        return in_array($stage, self::STAGES, true);
    }

    public function canTransition(string $from, string $to): bool
    {
        return $this->isKnown($from)
            && $this->isKnown($to)
            && in_array($to, self::TRANSITIONS[$from], true);
    }

    public function allowedNext(string $stage): array
    {
        return self::TRANSITIONS[$stage] ?? [];
    }

    public function isTerminal(string $stage): bool
    {
        return in_array($stage, ['completed', 'cancelled', 'no_show'], true);
    }
}
