<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Pure formatter for clinician/provider display labels and references.
 * Visibly distinguishes same-named doctors using stable unique user IDs (#user_id).
 */
final class ProviderDisplayLabel
{
    /**
     * Format a doctor reference code (e.g. "#123").
     */
    public static function formatReference(int|string|null $userId): string
    {
        $id = (int) $userId;
        return $id > 0 ? '#' . $id : '';
    }

    /**
     * Format an active doctor option text with reference FIRST (#123 - Name).
     * Ensures narrow controls do not hide doctor identity after a long name.
     */
    public static function forOption(int|string|null $userId, ?string $name): string
    {
        $id = (int) $userId;
        $cleanName = trim((string) $name);

        if ($id <= 0) {
            return $cleanName;
        }

        if ($cleanName === '') {
            return '#' . $id;
        }

        return '#' . $id . ' - ' . $cleanName;
    }

    /**
     * Format an inactive doctor option text retaining stored snapshot with visible stable ID.
     * e.g., "#123 - Dr. Jane (Currently Inactive)"
     */
    public static function forInactiveOption(int|string|null $userId, ?string $storedName, ?string $inactiveLabel = null): string
    {
        $id = (int) $userId;
        $name = trim((string) $storedName);
        if ($name === '') {
            $name = $id > 0 ? ('Doctor #' . $id) : 'Doctor';
        }

        $tag = trim((string) ($inactiveLabel ?? (function_exists('lang') ? lang('DoctorScheduling.inactiveProviderLabel') : 'Currently Inactive')));
        $suffix = $tag !== '' ? ' (' . $tag . ')' : '';

        if ($id > 0) {
            return '#' . $id . ' - ' . $name . $suffix;
        }

        return $name . $suffix;
    }

    /**
     * Format a legacy unassigned option (NULL provider_user_id).
     * Clearly unassigned, not fabricated.
     * e.g., "Unassigned - Chair 1 (Previously recorded chair)"
     */
    public static function forUnassignedOption(?string $storedChairName, ?string $legacyLabel = null, ?string $unassignedPrefix = null): string
    {
        $chair = trim((string) $storedChairName);
        $prefix = trim((string) ($unassignedPrefix ?? (function_exists('lang') ? lang('DoctorScheduling.unassignedDoctor') : 'Unassigned')));
        if ($prefix === '') {
            $prefix = 'Unassigned';
        }

        $tag = trim((string) ($legacyLabel ?? (function_exists('lang') ? lang('DoctorScheduling.legacyStoredChairOption') : 'Previously recorded chair')));
        $suffix = $tag !== '' ? ' (' . $tag . ')' : '';

        if ($chair !== '') {
            return $prefix . ' - ' . $chair . $suffix;
        }

        return $prefix . $suffix;
    }
}
