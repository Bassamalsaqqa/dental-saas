<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use DomainException;

/**
 * Authoritative explicit-clinic invoice reminder formatter.
 * Formats amount and calendar due date directly from target clinic settings without consulting
 * shared session, global settings cache, or shifting calendar DATE values across timezones.
 */
class InvoiceReminderFormatter extends SettingsService
{
    private array $clinicCache = [];

    public function __construct(private BaseConnection $db)
    {
        // Explicitly avoid calling parent::__construct() to prevent loading global session settings.
    }

    /**
     * Resolves and caches authoritative formatting settings for an explicit clinic.
     * Canonical timezone defaults to 'Asia/Jerusalem' matching ClinicSchedulingSettings contract.
     * Missing timezone defaults to safe app default; invalid timezone configuration fails closed with DomainException.
     */
    public function getClinicSettings(int $clinicId): array
    {
        if ($clinicId <= 0) {
            throw new DomainException('Clinic context required.');
        }

        if (isset($this->clinicCache[$clinicId])) {
            return $this->clinicCache[$clinicId];
        }

        $rows = $this->db->table('settings')
            ->select('setting_key, setting_value')
            ->where('clinic_id', $clinicId)
            ->whereIn('setting_key', ['currency', 'date_format', 'timezone'])
            ->get()
            ->getResultArray();

        $settings = [];
        foreach ($rows as $row) {
            $settings[(string) $row['setting_key']] = (string) $row['setting_value'];
        }

        $tzRaw = array_key_exists('timezone', $settings) ? $settings['timezone'] : 'Asia/Jerusalem';
        if (!is_string($tzRaw) || trim($tzRaw) === '') {
            throw new DomainException('Invalid clinic timezone.');
        }

        try {
            $tz = new \DateTimeZone($tzRaw);
        } catch (\Throwable $e) {
            throw new DomainException('Invalid clinic timezone.');
        }

        return $this->clinicCache[$clinicId] = [
            'currency' => $settings['currency'] ?? 'ILS',
            'date_format' => $settings['date_format'] ?? 'd/m/Y',
            'timezone' => $tzRaw,
            'timezone_obj' => $tz,
        ];
    }

    public function getCurrencySymbolForClinic(int $clinicId): string
    {
        $settings = $this->getClinicSettings($clinicId);
        return $this->currencySymbols[$settings['currency']] ?? '$';
    }

    public function getDateFormatForClinic(int $clinicId): string
    {
        $settings = $this->getClinicSettings($clinicId);
        return $settings['date_format'];
    }

    public function getTimezoneForClinic(int $clinicId): \DateTimeZone
    {
        $settings = $this->getClinicSettings($clinicId);
        return $settings['timezone_obj'];
    }

    /**
     * Formats balance due with explicit target clinic currency symbol.
     */
    public function formatAmount(int $clinicId, float $amount): string
    {
        $symbol = $this->getCurrencySymbolForClinic($clinicId);
        return $symbol . number_format($amount, 2);
    }

    /**
     * Formats calendar due date according to target clinic date_format.
     * DATE invoice due dates are pure calendar dates and are not shifted across timezones.
     */
    public function currentDate(int $clinicId, ?\DateTimeImmutable $instant = null): string
    {
        return ($instant ?? new \DateTimeImmutable('now'))->setTimezone($this->getTimezoneForClinic($clinicId))->format('Y-m-d');
    }

    public function formatDueDate(int $clinicId, ?string $dueDate): string
    {
        if ($dueDate === null || trim((string) $dueDate) === '') {
            return '';
        }

        $settings = $this->getClinicSettings($clinicId);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $dueDate, $settings['timezone_obj']);
        if (!$date || $date->format('Y-m-d') !== $dueDate) {
            throw new DomainException('Invalid invoice due date.');
        }
        return parent::formatDate($date, $settings['date_format']);
    }
}
