<?php
namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use CodeIgniter\Database\BaseConnection;

/** Pure target-clinic temporal policy; no session settings or server-local dates. */
final class ClinicWorkflowFormatter
{
    public readonly DateTimeZone $timezone;
    private string $dateFormat;
    private string $timeFormat;

    public function __construct(array $settings)
    {
        $zone = $settings['timezone'] ?? 'Asia/Jerusalem';
        $this->dateFormat = $settings['date_format'] ?? 'd/m/Y';
        $this->timeFormat = $settings['time_format'] ?? '12';
        if (!is_string($zone) || $zone === '' || !in_array($this->dateFormat, ['Y-m-d', 'm/d/Y', 'd/m/Y'], true)
            || !in_array($this->timeFormat, ['12', '24'], true)) {
            throw new DomainException('Invalid target-clinic notification settings.');
        }
        try { $this->timezone = new DateTimeZone($zone); }
        catch (\Throwable $e) { throw new DomainException('Invalid target-clinic notification timezone.', 0, $e); }
    }

    public static function forClinic(BaseConnection $db, int $clinicId): self
    {
        if ($clinicId <= 0) { throw new DomainException('Clinic context required.'); }
        $rows = $db->table('settings')->select('setting_key, setting_value')->where('clinic_id', $clinicId)
            ->whereIn('setting_key', ['date_format', 'time_format', 'timezone'])->get()->getResultArray();
        return new self(array_column($rows, 'setting_value', 'setting_key'));
    }

    public function currentDate(?DateTimeImmutable $now = null): string
    {
        return ($now ?? new DateTimeImmutable('now'))->setTimezone($this->timezone)->format('Y-m-d');
    }

    public function date(string $date): string
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date, new DateTimeZone('UTC'));
        if (!$parsed || $parsed->format('Y-m-d') !== $date) { throw new DomainException('Invalid calendar date.'); }
        return $parsed->format($this->dateFormat);
    }

    /** A local appointment must identify exactly one instant, rejecting DST gaps/folds. */
    public function instant(string $date, string $time): DateTimeImmutable
    {
        $this->date($date);
        if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?$/D', $time)) {
            throw new DomainException('Invalid appointment time.');
        }
        $wall = $date . ' ' . (strlen($time) === 5 ? $time . ':00' : $time);
        $naive = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $wall, new DateTimeZone('UTC'));
        $offsets = [];
        foreach ($this->timezone->getTransitions($naive->getTimestamp() - 172800, $naive->getTimestamp() + 172800) ?: [] as $transition) {
            $offsets[(int) $transition['offset']] = true;
        }
        $offsets[$this->timezone->getOffset($naive)] = true;
        $matches = [];
        foreach (array_keys($offsets) as $offset) {
            $candidate = $naive->setTimestamp($naive->getTimestamp() - $offset)->setTimezone($this->timezone);
            if ($candidate->format('Y-m-d H:i:s') === $wall) { $matches[$candidate->getTimestamp()] = $candidate; }
        }
        if (count($matches) !== 1) { throw new DomainException('Nonexistent or ambiguous appointment time.'); }
        return array_values($matches)[0];
    }

    public function appointmentVars(array $patient, array $source, array $clinicVars): array
    {
        $instant = $this->instant((string) ($source['appointment_date'] ?? ''), (string) ($source['appointment_time'] ?? ''));
        return ['patient_name' => self::patientName($patient), 'appointment_date' => $this->date($source['appointment_date']),
            'appointment_time' => $instant->format($this->timeFormat === '24' ? 'H:i' : 'g:i A')] + $clinicVars;
    }

    public function examinationVars(array $patient, array $source, array $clinicVars): array
    {
        return ['patient_name' => self::patientName($patient), 'examination_date' => $this->date((string) ($source['examination_date'] ?? ''))] + $clinicVars;
    }

    public function reminderEligible(array $source, int $leadMinutes, ?DateTimeImmutable $now = null): bool
    {
        $now ??= new DateTimeImmutable('now');
        $seconds = $this->instant((string) $source['appointment_date'], (string) $source['appointment_time'])->getTimestamp() - $now->getTimestamp();
        return ($source['status'] ?? '') === 'scheduled' && $seconds >= 0 && $seconds <= max(15, min($leadMinutes, 10080)) * 60;
    }

    /** Date-index discovery is deliberately a superset; exact elapsed eligibility is checked afterwards. */
    public function discoveryDates(int $leadMinutes, ?DateTimeImmutable $now = null): array
    {
        $now ??= new DateTimeImmutable('now');
        $end = $now->setTimestamp($now->getTimestamp() + max(15, min($leadMinutes, 10080)) * 60);
        return [$now->setTimezone($this->timezone)->modify('-1 day')->format('Y-m-d'),
            $end->setTimezone($this->timezone)->modify('+1 day')->format('Y-m-d')];
    }

    private static function patientName(array $patient): string
    {
        return trim((string) (($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')));
    }
}
