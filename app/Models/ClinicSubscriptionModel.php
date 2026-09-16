<?php

namespace App\Models;

use App\Models\TenantAwareModel;

class ClinicSubscriptionModel extends TenantAwareModel
{
    protected $table = 'clinic_subscriptions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'clinic_id',
        'plan_id',
        'status',
        'start_at',
        'end_at',
        'canceled_at',
        'canceled_reason',
        'paused_at',
        'paused_reason',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['setClinicId'];

    /**
     * Get the most recent subscription record for a clinic.
     * Ordering prefers latest end_at (or open-ended), then newest id.
     */
    public function getCurrentSubscription(int $clinicId)
    {
        return $this->where('clinic_id', $clinicId)
                    ->orderBy("COALESCE(end_at, '9999-12-31')", 'DESC', false)
                    ->orderBy('id', 'DESC')
                    ->first();
    }

    /**
     * Get the latest subscription and normalize expired status.
     */
    public function getNormalizedSubscription(int $clinicId): ?array
    {
        $sub = $this->getCurrentSubscription($clinicId);
        if (!$sub) {
            return null;
        }

        return $this->markExpiredIfNeeded($sub);
    }

    /**
     * Return the current instant formatted as canonical UTC DATETIME.
     */
    public static function nowUtc(): string
    {
        return gmdate('Y-m-d H:i:s');
    }

    /**
     * Parse a subscription timestamp (stored in canonical UTC) to an integer Unix timestamp.
     * Returns null if input is null or empty string.
     * Returns false if input is malformed/invalid (failing closed).
     * Behavior is strictly independent of mutable process timezone.
     */
    public static function parseUtcTimestamp(?string $datetime): int|false|null
    {
        if ($datetime === null || trim($datetime) === '') {
            return null;
        }

        $trimmed = trim($datetime);

        // Bounded grammar: strict date-time structure only (no natural language words, relative offsets, zero dates, or overflow hours/offsets)
        if (!preg_match('/^\d{4}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[12]\d|3[01])[ T](?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d(?:[Zz]|(?:\+(?:(?:0\d|1[0-3]):[0-5]\d|14:00)|-(?:(?:0\d|1[0-1]):[0-5]\d|12:00)))?$/', $trimmed)) {
            return false;
        }

        $utcTz = new \DateTimeZone('UTC');
        $formats = [
            '!Y-m-d H:i:s',
            '!Y-m-d\TH:i:s',
            '!Y-m-d\TH:i:s\Z',
            '!Y-m-d\TH:i:sP',
            '!Y-m-d H:i:sP',
        ];

        foreach ($formats as $fmt) {
            $dt = \DateTimeImmutable::createFromFormat($fmt, $trimmed, $utcTz);
            if ($dt === false) {
                continue;
            }

            $errors = \DateTimeImmutable::getLastErrors();
            if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
                continue;
            }

            // Strict round-trip validation against normalized representation
            $testFmt = str_replace('!', '', $fmt);
            $formatted = $dt->format($testFmt);
            if ($formatted !== $trimmed && strcasecmp($formatted, $trimmed) !== 0) {
                continue;
            }

            $year = (int) $dt->format('Y');
            if ($year < 1000 || $year > 9999) {
                return false;
            }

            return $dt->getTimestamp();
        }

        return false;
    }

    /**
     * Calculate end_at instant from duration and start_at instant in canonical UTC.
     * Returns null strictly for explicit lifetime subscriptions.
     * Returns false on calculation failure or invalid finite duration base time (never silently default to null).
     */
    public static function resolveEndAt(string $duration, ?string $startAtUtc): string|false|null
    {
        $validDurations = ['monthly', 'yearly', 'lifetime'];
        if (!in_array($duration, $validDurations, true)) {
            return false;
        }

        if ($duration === 'lifetime') {
            return null; // Explicit intentional open-ended lifetime subscription
        }

        if ($startAtUtc === null || trim($startAtUtc) === '') {
            return false; // Finite duration requires a valid base instant
        }

        $ts = self::parseUtcTimestamp($startAtUtc);
        if ($ts === null || $ts === false) {
            return false; // Calculation failed, fail closed rather than grant unlimited access
        }

        $dt = (new \DateTimeImmutable('@' . $ts))->setTimezone(new \DateTimeZone('UTC'));
        if ($duration === 'yearly') {
            return $dt->modify('+1 year')->format('Y-m-d H:i:s');
        }

        return $dt->modify('+1 month')->format('Y-m-d H:i:s');
    }

    /**
     * Format a stored UTC subscription timestamp for display in a specific timezone (defaults to process timezone).
     * Falls back to UTC safely if timezone is invalid.
     */
    public static function formatDisplay(?string $utcDatetime, string $format = 'M j, Y', ?string $timezone = null): string
    {
        if (empty($utcDatetime)) {
            return '--';
        }

        $ts = self::parseUtcTimestamp($utcDatetime);
        if ($ts === null || $ts === false) {
            return '--';
        }

        $tzName = $timezone ?: date_default_timezone_get();
        try {
            $tz = new \DateTimeZone($tzName);
        } catch (\Throwable $e) {
            $tz = new \DateTimeZone('UTC');
        }
        $dt = (new \DateTimeImmutable('@' . $ts))->setTimezone($tz);

        return $dt->format($format);
    }

    /**
     * Evaluate subscription standing canonically against UTC time.
     * Fails closed on malformed dates, future starts, or expired ends.
     *
     * @return array{is_valid: bool, reason: string}
     */
    public static function evaluateStanding(?array $sub, ?int $nowUtc = null): array
    {
        if (!$sub) {
            return ['is_valid' => false, 'reason' => 'no_valid_subscription'];
        }

        $status = (string) ($sub['status'] ?? '');
        if ($status !== 'active') {
            return ['is_valid' => false, 'reason' => strtoupper($status ?: 'NONE')];
        }

        $now = $nowUtc ?? time();

        // Validate start_at
        $startTs = self::parseUtcTimestamp($sub['start_at'] ?? null);
        if ($startTs === false) {
            return ['is_valid' => false, 'reason' => 'MALFORMED_START_AT'];
        }
        if ($startTs !== null && $startTs > $now) {
            return ['is_valid' => false, 'reason' => 'NOT_STARTED'];
        }

        // Validate end_at
        $endTs = self::parseUtcTimestamp($sub['end_at'] ?? null);
        if ($endTs === false) {
            return ['is_valid' => false, 'reason' => 'MALFORMED_END_AT'];
        }
        if ($endTs !== null && $endTs <= $now) {
            return ['is_valid' => false, 'reason' => 'EXPIRED'];
        }

        return ['is_valid' => true, 'reason' => 'ACTIVE'];
    }

    /**
     * Mark a subscription as expired if its end_at has passed.
     */
    public function markExpiredIfNeeded(array $subscription): array
    {
        if (empty($subscription['id']) || empty($subscription['status'])) {
            return $subscription;
        }

        if ($subscription['status'] !== 'active') {
            return $subscription;
        }

        if (empty($subscription['end_at'])) {
            return $subscription;
        }

        $endTs = self::parseUtcTimestamp($subscription['end_at']);
        if ($endTs !== false && $endTs !== null && $endTs <= time()) {
            $this->update($subscription['id'], [
                'status' => 'expired',
            ]);
            $subscription['status'] = 'expired';
        }

        return $subscription;
    }
}
