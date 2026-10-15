<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\ApiRuntimeContext;
use App\Libraries\IonAuth;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DateTimeImmutable;
use DomainException;
use RuntimeException;

/**
 * Pre-pilot schedule-impact calculation, preview/apply proof handshake,
 * and durable clinic-scoped audit evidence.
 */
class ScheduleImpactService
{
    private const SESSION_KEY = 'schedule_impact_proofs';
    private const TTL = 900; // 15 minutes

    protected ScheduleImpactAuditStore $auditStore;
    protected BaseConnection $db;
    protected ClinicSchedulingSettings $clinicSettings;
    protected ProviderDirectoryService $directoryService;
    protected ProviderScheduleService $scheduleService;

    public function __construct(
        ?BaseConnection $db = null,
        ?ClinicSchedulingSettings $clinicSettings = null,
        ?ProviderDirectoryService $directoryService = null,
        ?ProviderScheduleService $scheduleService = null,
        ?ScheduleImpactAuditStore $auditStore = null
    ) {
        $this->db = $db ?? Database::connect();
        $this->auditStore = $auditStore ?? new ScheduleImpactAuditStore($this->db);
        $this->clinicSettings = $clinicSettings ?? new ClinicSchedulingSettings($this->db);
        $this->directoryService = $directoryService ?? new ProviderDirectoryService($this->db);
        $this->scheduleService = $scheduleService ?? new ProviderScheduleService($this->db);
    }

    /**
     * Shared public normalizer and validator for proposed clinic hours DTO.
     * Matches the exact shape of ClinicSchedulingConfigurationService::configureHours $data.
     */
    public function validateProposedHoursDto(array $input, ?int $clinicId = null): array
    {
        $allowedKeys = [
            'working_hours_start',
            'working_hours_end',
            'appointment_duration',
            'working_days',
            'dynamic_doctor_scheduling_enabled',
            'booking_chair_count',
            'booking_chair_names',
            'impact_proof_token',
            'acknowledged',
            'dispositions',
        ];

        if (array_diff(array_keys($input), $allowedKeys)) {
            throw new DomainException('invalidInput');
        }

        $start = $input['working_hours_start'] ?? null;
        $end = $input['working_hours_end'] ?? null;

        if (!ClinicSchedulingSettings::validTime($start) || !ClinicSchedulingSettings::validTime($end) || $start >= $end) {
            throw new DomainException('invalidInput');
        }

        $duration = filter_var($input['appointment_duration'] ?? null, FILTER_VALIDATE_INT);
        if ($duration === false || $duration < 1 || $duration >= 480) {
            throw new DomainException('invalidInput');
        }

        $days = $input['working_days'] ?? [];
        $allowedDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        if (!is_array($days) || array_diff($days, $allowedDays)) {
            throw new DomainException('invalidInput');
        }

        // Preserve current flag if not passed in input and clinicId is known
        $enabled = false;
        if (array_key_exists('dynamic_doctor_scheduling_enabled', $input)) {
            if (!in_array($input['dynamic_doctor_scheduling_enabled'], ['0', '1', 0, 1, true, false], true)) {
                throw new DomainException('invalidInput');
            }
            $enabled = in_array($input['dynamic_doctor_scheduling_enabled'], ['1', 1, true], true);
        } elseif ($clinicId !== null && $clinicId > 0) {
            $currentSetting = $this->clinicSettings->get($clinicId, 'dynamic_doctor_scheduling_enabled', '0');
            $enabled = in_array($currentSetting, ['1', 'true', 1, true], true);
        }

        $normalized = [
            'working_hours_start' => $start,
            'working_hours_end' => $end,
            'appointment_duration' => (string) $duration,
            'working_days' => array_values(array_unique($days)),
            'dynamic_doctor_scheduling_enabled' => $enabled ? '1' : '0',
        ];

        // Legacy mode chairs: dynamic scheduling never truncates the provider directory to chair capacity
        if (!$enabled) {
            $count = filter_var($input['booking_chair_count'] ?? null, FILTER_VALIDATE_INT);
            $names = $input['booking_chair_names'] ?? [];
            $maxChairs = $this->legacyChairMax($clinicId);

            if ($count === false || $count < 1 || $count > $maxChairs || !is_array($names)) {
                throw new DomainException('invalidInput');
            }

            $cleanNames = [];
            for ($i = 0; $i < $count; $i++) {
                if (!isset($names[$i]) || !is_string($names[$i])) {
                    throw new DomainException('invalidInput');
                }
                $name = trim($names[$i]);
                if ($name === '' || mb_strlen($name) > 100 || in_array($name, $cleanNames, true)) {
                    throw new DomainException('invalidInput');
                }
                $cleanNames[] = $name;
            }

            $normalized['booking_chair_count'] = (string) $count;
            $normalized['booking_chair_names'] = $cleanNames;
        }

        return $normalized;
    }

    /**
     * Legacy chair maximum setting.
     */
    public function legacyChairMax(?int $clinicId = null): int
    {
        $row = $this->db->table('settings')->where('clinic_id', null)->where('setting_key', 'default_booking_chair_max')->get()->getRowArray();
        return max(1, min(3, (int) ($row['setting_value'] ?? 3)));
    }

    /**
     * Shared public normalizer and validator for proposed provider profile DTO.
     */
    public function validateProposedProfileDto(array $input): array
    {
        $allowedKeys = [
            'is_bookable',
            'is_clinician',
            'weekly_hours',
            'exceptions',
            'weekly_override',
            'weekly',
            'impact_proof_token',
            'acknowledged',
            'dispositions',
        ];

        if (array_diff(array_keys($input), $allowedKeys)) {
            throw new DomainException('invalidInput');
        }

        foreach (['is_bookable', 'is_clinician', 'weekly_override'] as $flag) {
            if (array_key_exists($flag, $input) && !in_array($input[$flag], ['0', '1', 0, 1, true, false], true)) {
                throw new DomainException('invalidInput');
            }
        }

        $windowsValidator = static function ($rows): array {
            if (!is_array($rows) || count($rows) > 12) {
                throw new DomainException('invalidInput');
            }
            $out = [];
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    throw new DomainException('invalidInput');
                }
                $start = $row['start'] ?? '';
                $end = $row['end'] ?? '';
                if ($start === '' && $end === '') {
                    continue;
                }
                if (!is_string($start) || !is_string($end) || !ClinicSchedulingSettings::validTime($start)
                    || !ClinicSchedulingSettings::validTime($end) || $start >= $end) {
                    throw new DomainException('invalidInput');
                }
                $out[] = ['start' => $start, 'end' => $end];
            }
            usort($out, static fn($a, $b) => strcmp($a['start'], $b['start']));
            for ($i = 1; $i < count($out); $i++) {
                if ($out[$i]['start'] < $out[$i - 1]['end']) {
                    throw new DomainException('invalidInput');
                }
            }
            return $out;
        };

        $allowedDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $weekly = null;

        if (array_key_exists('weekly_hours', $input)) {
            if ($input['weekly_hours'] !== null) {
                if (!is_array($input['weekly_hours'])) {
                    throw new DomainException('invalidInput');
                }
                if (array_diff(array_keys($input['weekly_hours']), $allowedDays)) {
                    throw new DomainException('invalidInput');
                }
                $weekly = [];
                foreach ($allowedDays as $day) {
                    $weekly[$day] = $windowsValidator($input['weekly_hours'][$day] ?? []);
                }
            }
        } elseif (($input['weekly_override'] ?? '0') === '1' || ($input['weekly_override'] ?? null) === true) {
            $weeklySource = $input['weekly'] ?? [];
            if (!is_array($weeklySource) || array_diff(array_keys($weeklySource), $allowedDays)) {
                throw new DomainException('invalidInput');
            }
            $weekly = [];
            foreach ($allowedDays as $day) {
                $weekly[$day] = $windowsValidator($weeklySource[$day] ?? []);
            }
        }

        $exceptions = null;
        if (array_key_exists('exceptions', $input) && $input['exceptions'] !== null) {
            if (!is_array($input['exceptions'])) {
                throw new DomainException('invalidInput');
            }
            $exceptions = [];
            $exceptionRows = $input['exceptions'];
            if (count($exceptionRows) > 366) {
                throw new DomainException('invalidInput');
            }

            $isAssoc = array_keys($exceptionRows) !== range(0, count($exceptionRows) - 1);
            if ($isAssoc) {
                foreach ($exceptionRows as $date => $winList) {
                    $cleanDate = trim((string) $date);
                    if (!ClinicSchedulingSettings::validDate($cleanDate) || !is_array($winList)) {
                        throw new DomainException('invalidInput');
                    }
                    $exceptions[$cleanDate] = $windowsValidator($winList);
                }
            } else {
                foreach ($exceptionRows as $row) {
                    if (!is_array($row)) {
                        throw new DomainException('invalidInput');
                    }
                    if (($row['remove'] ?? '') === '1' || ($row['date'] ?? '') === '') {
                        continue;
                    }
                    $date = $row['date'];
                    if (!is_string($date) || !ClinicSchedulingSettings::validDate($date) || isset($exceptions[$date])) {
                        throw new DomainException('invalidInput');
                    }
                    $exceptions[$date] = (($row['leave'] ?? '') === '1' || ($row['leave'] ?? false) === true)
                        ? []
                        : $windowsValidator($row['windows'] ?? []);
                }
            }
        }

        return [
            'is_bookable' => in_array($input['is_bookable'] ?? '1', ['1', 1, true], true),
            'is_clinician' => in_array($input['is_clinician'] ?? '0', ['1', 1, true], true),
            'weekly_hours' => $weekly,
            'exceptions' => $exceptions,
        ];
    }

    /** Lock all policy dependencies before any consistent snapshot read; caller owns the transaction. */
    public function lockState(int $clinicId): void
    {
        if ($clinicId<=0 || $this->db->transDepth!==1) throw new RuntimeException('Owned schedule review transaction required.');
        $this->db->query('SELECT id,status FROM clinics WHERE id=? FOR UPDATE',[$clinicId])->getRowArray();
        foreach (['settings','appointments','patients','examinations','clinical_procedures','clinical_charge_lines','clinic_users'] as $table) {
            $this->db->query("SELECT id FROM {$table} WHERE clinic_id=? ORDER BY id FOR UPDATE",[$clinicId])->getResultArray();
        }
        if ($this->db->tableExists('clinic_provider_profiles')) $this->db->query('SELECT id FROM clinic_provider_profiles WHERE clinic_id=? ORDER BY id FOR UPDATE',[$clinicId])->getResultArray();
        $this->db->query('SELECT cu.id FROM clinic_users cu JOIN users u ON u.id=cu.user_id JOIN roles r ON r.id=cu.role_id WHERE cu.clinic_id=? ORDER BY u.id FOR UPDATE',[$clinicId])->getResultArray();
    }

    /**
     * Compute deterministic state digest from the complete content of future visits,
     * clinical links, settings, provider profiles, and clinic user/role eligibility.
     */
    public function computeStateDigest(BaseConnection $db, int $clinicId): string
    {
        $tzString = $this->clinicSettings->get($clinicId, 'timezone', 'Asia/Jerusalem');
        $tz = $this->clinicSettings->timezone($clinicId);
        $today = (new DateTimeImmutable('now', $tz))->format('Y-m-d');

        $clinicRow = $db->query('SELECT id, status FROM clinics WHERE id = ?', [$clinicId])->getRowArray();

        $hasProvCol = $db->fieldExists('provider_user_id', 'appointments');
        $provColSql = $hasProvCol ? 'provider_user_id' : 'NULL AS provider_user_id';

        $appointments = $db->query(
            "SELECT id, clinic_id, patient_id, appointment_date, appointment_time, duration, status, chair_name, {$provColSql}, notes, created_at, updated_at
             FROM appointments
             WHERE clinic_id = ? AND appointment_date >= ? AND status NOT IN ('cancelled', 'no_show')
             ORDER BY id ASC",
            [$clinicId, $today]
        )->getResultArray();

        $exams = $db->query(
            'SELECT appointment_id FROM examinations WHERE clinic_id = ? AND appointment_id IS NOT NULL ORDER BY appointment_id ASC',
            [$clinicId]
        )->getResultArray();

        $procedures = $db->query(
            'SELECT appointment_id, scheduled_appointment_id FROM clinical_procedures WHERE clinic_id = ? AND (appointment_id IS NOT NULL OR scheduled_appointment_id IS NOT NULL) ORDER BY id ASC',
            [$clinicId]
        )->getResultArray();

        $charges = $db->query(
            'SELECT appointment_id FROM clinical_charge_lines WHERE clinic_id = ? AND appointment_id IS NOT NULL ORDER BY appointment_id ASC',
            [$clinicId]
        )->getResultArray();

        $settings = $db->query("SELECT setting_key, setting_value, setting_type FROM settings WHERE clinic_id=? AND setting_key IN ('timezone','working_hours_start','working_hours_end','working_days','appointment_duration','dynamic_doctor_scheduling_enabled','booking_chair_count','booking_chair_names') ORDER BY setting_key",[$clinicId])->getResultArray();
        $patients = $db->query('SELECT id,clinic_id,status FROM patients WHERE clinic_id=? ORDER BY id',[$clinicId])->getResultArray();

        $profiles = [];
        if ($db->tableExists('clinic_provider_profiles')) {
            $profiles = $db->query(
                'SELECT user_id, is_bookable, is_clinician, weekly_hours_json, exceptions_json, updated_at
                 FROM clinic_provider_profiles
                 WHERE clinic_id = ? ORDER BY user_id ASC',
                [$clinicId]
            )->getResultArray();
        }

        $users = $db->query(
            'SELECT cu.user_id, cu.role_id, cu.status as membership_status, u.active as user_active, r.slug as role_slug, r.is_doctor, r.is_active as role_active, r.clinic_id as role_clinic_id
             FROM clinic_users cu
             JOIN users u ON u.id = cu.user_id
             JOIN roles r ON r.id = cu.role_id
             WHERE cu.clinic_id = ? ORDER BY cu.user_id ASC',
            [$clinicId]
        )->getResultArray();

        $payload = [
            'clinic_id' => $clinicId,
            'clinic_status' => $clinicRow['status'] ?? 'unknown',
            'timezone' => $tzString,
            'today' => $today,
            'appointments' => $appointments,
            'patients' => $patients,
            'exams' => $exams,
            'procedures' => $procedures,
            'charges' => $charges,
            'settings' => $settings,
            'profiles' => $profiles,
            'users' => $users,
        ];

        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Generate full future booking horizon read-only impact report.
     * Evaluates every future visit under both BEFORE and AFTER states.
     */
    public function generateReport(
        BaseConnection $db,
        int $clinicId,
        string $targetType,
        array $proposedDto,
        ?int $targetId = null,
        bool $forUpdate = false
    ): array {
        if (!$this->canReadReview($clinicId)) {
            throw new DomainException('forbidden');
        }

        if ($forUpdate) $this->lockState($clinicId);
        $tz = $this->clinicSettings->timezone($clinicId);
        $today = (new DateTimeImmutable('now', $tz))->format('Y-m-d');

        $hasProvCol = $db->fieldExists('provider_user_id', 'appointments');
        $provColSql = $hasProvCol ? 'provider_user_id' : 'NULL AS provider_user_id';

        $sql = "SELECT id, clinic_id, patient_id, appointment_date, appointment_time, duration, status, chair_name, {$provColSql}, notes
                FROM appointments
                WHERE clinic_id = ? AND appointment_date >= ? AND status NOT IN ('cancelled', 'no_show')
                ORDER BY appointment_date ASC, appointment_time ASC, id ASC";

        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }

        $appointments = $db->query($sql, [$clinicId, $today])->getResultArray();

        // Baseline configuration
        $baselineHours = $this->clinicSettings->hours($clinicId);
        $baselineDynamicEnabled = $this->directoryService->isEnabled($clinicId);
        $configuredChairs = $this->clinicSettings->chairs($clinicId);

        // After configuration
        $afterHours = $baselineHours;
        $afterDynamicEnabled = $baselineDynamicEnabled;
        $afterChairs = $configuredChairs;
        $profileOverride = null;
        $profileOverrideUserId = null;

        if ($targetType === 'clinic_hours') {
            $afterHours = [
                'start' => $proposedDto['working_hours_start'],
                'end' => $proposedDto['working_hours_end'],
                'days' => $proposedDto['working_days'],
            ];
            $afterDynamicEnabled = ($proposedDto['dynamic_doctor_scheduling_enabled'] === '1' || $proposedDto['dynamic_doctor_scheduling_enabled'] === true);
            if (!$afterDynamicEnabled && isset($proposedDto['booking_chair_names'])) {
                $afterChairs = $proposedDto['booking_chair_names'];
            }
        } elseif ($targetType === 'provider_profile' && $targetId !== null) {
            $profileOverride = $proposedDto;
            $profileOverrideUserId = $targetId;
        }

        // Provider names cache
        $providerNames = [];
        if ($db->tableExists('users')) {
            $userRows = $db->query(
                'SELECT u.id, u.first_name, u.last_name, u.email FROM users u JOIN clinic_users cu ON cu.user_id=u.id WHERE cu.clinic_id=?',
                [$clinicId]
            )->getResultArray();
            foreach ($userRows as $ur) {
                $pName = trim(($ur['first_name'] ?? '') . ' ' . ($ur['last_name'] ?? ''));
                $providerNames[(int) $ur['id']] = $pName ?: (string) ($ur['email'] ?? ('Doctor #' . $ur['id']));
            }
        }

        // Permission check for edit links
        $actorId = (int) (new IonAuth())->getUserId();
        $canEditAppointments = $actorId > 0 && (new PermissionService($db))->hasPermission($actorId, 'appointments', 'edit');

        $items = [];
        $affectedIds = [];
        $beforeConflictsCount = 0;
        $afterConflictsCount = 0;
        $newlyConflictedCount = 0;
        $resolvedConflictsCount = 0;

        foreach ($appointments as $apt) {
            $aptId = (int) $apt['id'];
            $date = (string) $apt['appointment_date'];
            $rawTime = substr(trim((string) $apt['appointment_time']), 0, 5);
            $duration = (int) $apt['duration'];
            $startMin = ((int) substr($rawTime, 0, 2) * 60) + (int) substr($rawTime, 3, 2);
            $endMin = $startMin + $duration;
            $endTimeStr = sprintf('%02d:%02d', intdiv($endMin, 60), $endMin % 60);

            $provId = isset($apt['provider_user_id']) && $apt['provider_user_id'] !== null
                ? (int) $apt['provider_user_id']
                : null;

            $provName = $provId !== null ? ($providerNames[$provId] ?? ('Doctor #' . $provId)) : null;

            $beforeOutcome = $this->evaluateAppointmentCompliance(
                $db,
                $clinicId,
                $apt,
                $baselineHours,
                $baselineDynamicEnabled,
                $configuredChairs,
                null,
                null,
                $tz
            );

            $afterOutcome = $this->evaluateAppointmentCompliance(
                $db,
                $clinicId,
                $apt,
                $afterHours,
                $afterDynamicEnabled,
                $afterChairs,
                $profileOverride,
                $profileOverrideUserId,
                $tz
            );

            $isBeforeConflict = !$beforeOutcome['compliant'];
            $isAfterConflict = !$afterOutcome['compliant'];

            if ($isBeforeConflict) {
                $beforeConflictsCount++;
            }
            if ($isAfterConflict) {
                $afterConflictsCount++;
                $affectedIds[] = $aptId;
            }

            $isNewConflict = (!$isBeforeConflict && $isAfterConflict);
            if ($isNewConflict) {
                $newlyConflictedCount++;
            }

            $isResolved = ($isBeforeConflict && !$isAfterConflict);
            if ($isResolved) {
                $resolvedConflictsCount++;
            }

            $reschedulable = AppointmentReschedulabilityService::isReschedulable($db, $clinicId, $apt);

            // PHI Protection: omit patient_id on settings-only outward report items
            $items[] = [
                'appointment_id' => $aptId,
                'appointment_date' => $date,
                'appointment_time' => $rawTime,
                'duration' => $duration,
                'end_time' => $endTimeStr,
                'status' => (string) $apt['status'],
                'provider_user_id' => $provId,
                'provider_name' => $provName,
                'chair_name' => (string) ($apt['chair_name'] ?? ''),
                'before_outcome' => $beforeOutcome,
                'after_outcome' => $afterOutcome,
                'is_affected' => $isAfterConflict,
                'is_new_conflict' => $isNewConflict,
                'is_resolved' => $isResolved,
                'reschedulable' => $reschedulable,
                'edit_url' => ($reschedulable && $canEditAppointments) ? base_url('appointment/' . $aptId . '/edit') : null,
            ];
        }

        $stateDigest = $this->computeStateDigest($db, $clinicId);

        return [
            'clinic_id' => $clinicId,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'total_future_visits' => count($items),
            'before_conflicts_count' => $beforeConflictsCount,
            'after_conflicts_count' => $afterConflictsCount,
            'newly_conflicted_count' => $newlyConflictedCount,
            'resolved_conflicts_count' => $resolvedConflictsCount,
            'affected_appointment_ids' => $affectedIds,
            'state_digest' => $stateDigest,
            'items' => $items,
        ];
    }

    /**
     * Canonical appointment compliance evaluation for before and after states.
     */
    protected function evaluateAppointmentCompliance(
        BaseConnection $db,
        int $clinicId,
        array $apt,
        array $clinicHours,
        bool $dynamicEnabled,
        array $chairOptions,
        ?array $profileOverride,
        ?int $profileOverrideUserId,
        \DateTimeZone $tz
    ): array {
        $date = (string) $apt['appointment_date'];
        $time = substr(trim((string) $apt['appointment_time']), 0, 5);
        $duration = (int) $apt['duration'];

        // Pure interval & DST validation
        $intervalError = ClinicScheduleAvailabilityService::validateAppointmentInterval($date, $time, $duration, $tz);
        if ($intervalError !== null) {
            $intervalError['reason']=lang('DoctorScheduleSettings.invalidIntervalReason');
            return $intervalError;
        }

        $startMinutes = ((int) substr($time, 0, 2) * 60) + (int) substr($time, 3, 2);
        $endMinutes = $startMinutes + $duration;
        $endTimeStr = sprintf('%02d:%02d', intdiv($endMinutes, 60), $endMinutes % 60);

        $dayOfWeek = strtolower((new DateTimeImmutable($date))->format('l'));

        // 1. Clinic working day check
        if (!in_array($dayOfWeek, $clinicHours['days'], true)) {
            return [
                'compliant' => false,
                'code' => 'CLINIC_NON_WORKING_DAY',
                'reason' => lang('DoctorScheduleSettings.nonWorkingDayReason', ['day' => lang('DoctorScheduleSettings.'.$dayOfWeek)]) ?: "Clinic closed on {$dayOfWeek}",
            ];
        }

        // 2. Clinic working hours check
        if ($time < $clinicHours['start'] || $endTimeStr > $clinicHours['end']) {
            return [
                'compliant' => false,
                'code' => 'OUTSIDE_CLINIC_HOURS',
                'reason' => lang('DoctorScheduleSettings.outsideClinicHoursReason', [
                    'start' => $clinicHours['start'],
                    'end' => $clinicHours['end'],
                ]) ?: "Falls outside clinic working hours ({$clinicHours['start']} - {$clinicHours['end']})",
            ];
        }

        // 3. Dynamic doctor mode checks
        if ($dynamicEnabled) {
            $schemaReady = $db->tableExists('clinic_provider_profiles') && $db->fieldExists('provider_user_id', 'appointments');
            if (!$schemaReady) {
                return [
                    'compliant' => false,
                    'code' => 'SCHEMA_NOT_READY',
                    'reason' => 'Dynamic doctor scheduling schema is missing',
                ];
            }

            $provId = isset($apt['provider_user_id']) && $apt['provider_user_id'] !== null
                ? (int) $apt['provider_user_id']
                : null;

            if ($provId === null) {
                return [
                    'compliant' => false,
                    'code' => 'UNRESOLVED_PROVIDER',
                    'reason' => lang('DoctorScheduleSettings.unresolvedProviderReason') ?: 'Unresolved historical doctor assignment',
                ];
            }

            // Check provider membership & canonical clinic role
            $userRow = $db->query(
                'SELECT u.id, u.active, cu.status as membership_status, cu.role_id, r.slug as role_slug, r.is_doctor, r.is_active as role_is_active, r.clinic_id as role_clinic_id
                 FROM users u
                 JOIN clinic_users cu ON cu.user_id = u.id
                 JOIN roles r ON r.id = cu.role_id
                 WHERE u.id = ? AND cu.clinic_id = ?',
                [$provId, $clinicId]
            )->getRowArray();

            if (!$userRow || (int) $userRow['active'] !== 1 || ($userRow['membership_status'] ?? '') !== 'active'
                || (int) ($userRow['role_is_active'] ?? 0) !== 1 || ($userRow['role_slug'] ?? '') === 'super_admin'
                || ($userRow['role_clinic_id'] !== null && (int) $userRow['role_clinic_id'] !== $clinicId)) {
                return [
                    'compliant' => false,
                    'code' => 'PROVIDER_INACTIVE_OR_INVALID',
                    'reason' => lang('DoctorScheduleSettings.providerInactiveReason') ?: 'Doctor account or membership is inactive',
                ];
            }

            // Use the same strict persisted-profile parser as canonical booking, including every day/date.
            try { $profileRow=($profileOverrideUserId===$provId && $profileOverride!==null) ? null : $this->scheduleService->getProfile($clinicId,$provId); }
            catch (\Throwable $e) { return ['compliant'=>false,'code'=>'PROVIDER_PROFILE_CORRUPT','reason'=>lang('DoctorScheduleSettings.invalidIntervalReason')]; }

            // Clinician qualification check
            $roleSlug = (string) ($userRow['role_slug'] ?? '');
            $isCanonicalDoctor = in_array($roleSlug, ['doctor', 'senior_doctor'], true) || ((int) ($userRow['is_doctor'] ?? 0) === 1);
            $hasExplicitClinician = ($profileOverrideUserId === $provId && $profileOverride !== null)
                ? (bool) ($profileOverride['is_clinician'] ?? false)
                : ($profileRow && (int) ($profileRow['is_clinician'] ?? 0) === 1);

            if (!$isCanonicalDoctor && !$hasExplicitClinician) {
                return [
                    'compliant' => false,
                    'code' => 'PROVIDER_NOT_ELIGIBLE',
                    'reason' => lang('DoctorScheduleSettings.providerNotEligibleReason') ?: 'Doctor role is not qualified as clinician',
                ];
            }

            // Windows resolution with corrupt profile fail-closed
            $overrideForThis = ($profileOverrideUserId === $provId) ? $profileOverride : null;
            [$windows, $errorCode] = ClinicScheduleAvailabilityService::resolveEffectiveWindows($date, $clinicHours, $profileRow, $overrideForThis);

            if ($errorCode !== null) {
                $reasonMap = [
                    'PROVIDER_NOT_BOOKABLE' => lang('DoctorScheduleSettings.providerNotBookableReason') ?: 'Doctor is marked unavailable for booking',
                    'PROVIDER_PROFILE_CORRUPT' => 'Doctor schedule profile is corrupted or invalid',
                    'PROVIDER_ON_LEAVE' => lang('DoctorScheduleSettings.providerOnLeaveReason') ?: 'Doctor is on scheduled leave',
                    'OUTSIDE_PROVIDER_HOURS' => lang('DoctorScheduleSettings.outsideProviderHoursReason') ?: 'Doctor is not scheduled to work on this day',
                ];
                return [
                    'compliant' => false,
                    'code' => $errorCode,
                    'reason' => $reasonMap[$errorCode] ?? 'Provider schedule is unavailable',
                ];
            }

            if (empty($windows)) {
                return [
                    'compliant' => false,
                    'code' => 'OUTSIDE_PROVIDER_HOURS',
                    'reason' => lang('DoctorScheduleSettings.outsideProviderHoursReason') ?: 'Doctor is not scheduled to work on this day',
                ];
            }

            $fitsWindow = false;
            foreach ($windows as $w) {
                if ($time >= $w['start'] && $endTimeStr <= $w['end']) {
                    $fitsWindow = true;
                    break;
                }
            }

            if (!$fitsWindow) {
                return [
                    'compliant' => false,
                    'code' => 'OUTSIDE_PROVIDER_HOURS',
                    'reason' => lang('DoctorScheduleSettings.outsideProviderHoursReason') ?: 'Visit interval falls outside doctor hours or into a break',
                ];
            }

            return [
                'compliant' => true,
                'code' => 'COMPLIANT',
                'reason' => lang('DoctorScheduleSettings.compliant') ?: 'Compliant',
            ];
        }

        // 4. Legacy mode checks (Dynamic disabled)
        $chairName = trim((string) ($apt['chair_name'] ?? ''));
        if ($chairName === '' || !in_array($chairName, $chairOptions, true)) {
            return [
                'compliant' => false,
                'code' => 'INVALID_CHAIR',
                'reason' => lang('DoctorScheduleSettings.invalidChairReason', ['chair' => $chairName]) ?: "Booking resource '{$chairName}' is not configured",
            ];
        }

        return [
            'compliant' => true,
            'code' => 'COMPLIANT',
            'reason' => lang('DoctorScheduleSettings.compliant') ?: 'Compliant',
        ];
    }

    /**
     * Issue cryptographically secure session-bound preview proof.
     */
    public function issueProof(
        int $clinicId,
        int $actorId,
        string $targetType,
        array $proposedDto,
        array $report,
        ?int $targetId = null
    ): string {
        $actor = $this->authorizedActor($clinicId);
        if ($actor === null || $actor !== $actorId) {
            throw new DomainException('forbidden');
        }

        $token = bin2hex(random_bytes(32));
        $proofs = session()->get(self::SESSION_KEY);
        $proofs = is_array($proofs) ? $proofs : [];

        // Purge expired proofs
        $proofs = array_filter($proofs, static fn($p): bool => is_array($p) && (int) ($p['expires_at'] ?? 0) > time());

        $key = $clinicId . ':' . $targetType . ':' . ($targetId ?? 0);
        $proofs[$key] = [
            'token_hash' => hash('sha256', $token),
            'clinic_id' => $clinicId,
            'actor_id' => $actorId,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'proposed_dto' => $proposedDto,
            'fingerprint' => $report['state_digest'],
            'affected_appointment_ids' => $report['affected_appointment_ids'],
            'expires_at' => time() + self::TTL,
        ];

        // Cap stored proofs to 20
        $proofs = array_slice($proofs, -20, null, true);
        session()->set(self::SESSION_KEY, $proofs);

        return $token;
    }

    /**
     * Validate preview proof, attributed acknowledgment, and explicit per-visit retain dispositions.
     * Recomputes report under clinic lock to prevent replay, tampering, or stale state.
     *
     * @return string|null Null on valid, error string key on failure.
     */
    public function validateProofAndDispositions(
        BaseConnection $db,
        int $clinicId,
        int $actorId,
        string $targetType,
        array $proposedDto,
        ?int $targetId,
        ?string $token,
        $acknowledged,
        array $dispositions,
        array $recomputedReport
    ): ?string {
        $actor = $this->authorizedActor($clinicId);
        if ($actor === null || $actor !== $actorId) {
            return 'forbidden';
        }

        $affectedIds = $recomputedReport['affected_appointment_ids'];

        // Direct service compatibility: permitted only when no token, no ack, no dispositions, and zero after conflicts
        $noProofInput = ($token === null) && ($acknowledged === false || $acknowledged === null) && empty($dispositions);
        if ($noProofInput) {
            if (empty($affectedIds)) {
                return null;
            }
            return 'missingProof';
        }

        // An explicit apply or proof submission requires strict validation even if after_conflicts is 0
        if ($token === null || !is_string($token) || preg_match('/^[a-f0-9]{64}$/D', $token) !== 1) {
            return 'missingProof';
        }

        // Strict acknowledgment: must be strictly true, '1', or 1 (never loose bool cast)
        if ($acknowledged !== true && $acknowledged !== '1' && $acknowledged !== 1) {
            return 'unacknowledgedImpact';
        }

        $proofs = session()->get(self::SESSION_KEY);
        $proofs = is_array($proofs) ? $proofs : [];
        $key = $clinicId . ':' . $targetType . ':' . ($targetId ?? 0);
        $stored = $proofs[$key] ?? null;

        if (!is_array($stored)) {
            return 'missingProof';
        }

        if ((int) ($stored['expires_at'] ?? 0) <= time()) {
            return 'staleProof';
        }

        if (!hash_equals((string) ($stored['token_hash'] ?? ''), hash('sha256', $token))) {
            return 'missingProof';
        }

        if ((int) ($stored['clinic_id'] ?? 0) !== $clinicId || (int) ($stored['actor_id'] ?? 0) !== $actorId) {
            return 'forbidden';
        }

        if (($stored['target_type'] ?? '') !== $targetType || ($stored['target_id'] ?? null) !== $targetId) {
            return 'missingProof';
        }

        if ($stored['proposed_dto'] !== $proposedDto) {
            return 'staleProof';
        }

        if (!hash_equals((string) ($stored['fingerprint'] ?? ''), (string) ($recomputedReport['state_digest'] ?? ''))) {
            return 'staleProof';
        }

        // Dispositions validation
        if (empty($affectedIds)) {
            // Reject any extraneous dispositions when conflict count is 0
            if (!empty($dispositions)) {
                return 'foreignDisposition';
            }
            return null;
        }

        // Verify disposition IDs match server affected IDs exactly (strict positive decimal integers, no extras, no junk)
        $submittedKeys = array_keys($dispositions);
        $parsedSubmitted = [];
        foreach ($submittedKeys as $k) {
            $strK = (string) $k;
            if (ClinicSchedulingSettings::positiveInt($strK) === null) {
                return 'foreignDisposition';
            }
            $parsedSubmitted[] = (int) $strK;
        }

        sort($parsedSubmitted);
        $sortedAffected = $affectedIds;
        sort($sortedAffected);

        if ($parsedSubmitted !== $sortedAffected) {
            foreach ($parsedSubmitted as $pId) {
                if (!in_array($pId, $affectedIds, true)) {
                    return 'foreignDisposition';
                }
            }
            return 'missingDisposition';
        }

        // Validate each disposition item
        foreach ($affectedIds as $affId) {
            $disp = $dispositions[$affId] ?? null;
            if (!is_array($disp)) {
                return 'missingDisposition';
            }
            if (!is_string($disp['action'] ?? null) || !is_string($disp['reason'] ?? null)) return 'invalidDisposition';
            $action = trim($disp['action']);
            if ($action !== 'retain') {
                return 'invalidDisposition';
            }

            $reason = (string) ($disp['reason'] ?? '');
            if (!mb_check_encoding($reason, 'UTF-8')) {
                return 'reasonRequired';
            }
            $trimmedReason = trim($reason);
            $byteLength = strlen($trimmedReason);
            $charLength = mb_strlen($trimmedReason);

            if ($charLength < 3 || $charLength > 500 || $byteLength > 2000) {
                return 'reasonRequired';
            }
        }

        return null;
    }

    /**
     * Record durable immutable clinic-scoped audit evidence and update retained exceptions.
     * Uses lossless numbered chunked audit bundle (each metadata <= 60000 bytes) in existing tables.
     */
    public function recordAuditEvidence(
        BaseConnection $db,
        int $clinicId,
        int $actorId,
        string $targetType,
        ?int $targetId,
        array $beforeConfig,
        array $proposedDto,
        array $report,
        array $dispositions
    ): void {
        $now = date('Y-m-d H:i:s');
        $reviewId = 'REV-' . date('Ymd-His') . '-' . bin2hex(random_bytes(6));
        $affectedCount = count($report['affected_appointment_ids']);

        // Build normalized dispositions strictly from server affected IDs
        $normalizedDispositions = [];
        foreach ($report['affected_appointment_ids'] as $affId) {
            $reason = trim((string) ($dispositions[$affId]['reason'] ?? ''));
            $normalizedDispositions[(int) $affId] = [
                'action' => 'retain',
                'reason' => $reason,
            ];
        }

        // Capture snapshot of affected before/after items
        $affectedItems = [];
        foreach ($report['items'] as $item) {
            if (in_array($item['appointment_id'], $report['affected_appointment_ids'], true)) {
                $affectedItems[] = [
                    'appointment_id' => $item['appointment_id'],
                    'appointment_date' => $item['appointment_date'],
                    'appointment_time' => $item['appointment_time'],
                    'duration' => $item['duration'],
                    'provider_user_id' => $item['provider_user_id'],
                    'chair_name' => $item['chair_name'],
                    'status' => $item['status'],
                    'before_outcome' => $item['before_outcome'],
                    'after_outcome' => $item['after_outcome'],
                ];
            }
        }

        $fullBundle = [
            'review_id' => $reviewId,
            'clinic_id' => $clinicId,
            'actor_user_id' => $actorId,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'applied_at' => $now,
            'state_digest' => $report['state_digest'],
            'affected_count' => $affectedCount,
            'affected_appointment_ids' => $report['affected_appointment_ids'],
            'dispositions' => $normalizedDispositions,
            'before_configuration' => $beforeConfig,
            'after_configuration' => $proposedDto,
            'affected_items' => $affectedItems,
            'summary' => [
                'total_future_visits' => $report['total_future_visits'],
                'before_conflicts_count' => $report['before_conflicts_count'],
                'after_conflicts_count' => $report['after_conflicts_count'],
                'newly_conflicted_count' => $report['newly_conflicted_count'],
                'resolved_conflicts_count' => $report['resolved_conflicts_count'],
            ],
        ];

        $fullBundle['configuration_digest'] = $this->configurationDigest($clinicId);
        $pointer = $this->auditStore->write($clinicId, $actorId, $fullBundle);
        $key = ['clinic_id'=>$clinicId, 'setting_key'=>'schedule_impact_latest_audit'];
        $existing = $db->table('settings')->where($key)->get()->getRowArray();
        $payload = $key + ['setting_value'=>json_encode($pointer, JSON_THROW_ON_ERROR), 'setting_type'=>'json', 'updated_at'=>$now];
        if ($existing) $saved = $db->table('settings')->where($key)->where('id',$existing['id'])->update($payload);
        else $saved = $db->table('settings')->insert($payload + ['created_at'=>$now]);
        if (!$saved) throw new RuntimeException('Required schedule review pointer write failed.');
    }

    /**
     * Consume session-bound proof ONLY AFTER checked transCommit succeeds.
     */
    public function consumeProof(int $clinicId, string $targetType, ?int $targetId = null): void
    {
        $proofs = session()->get(self::SESSION_KEY);
        if (is_array($proofs)) {
            unset($proofs[$clinicId . ':' . $targetType . ':' . ($targetId ?? 0)]);
            session()->set(self::SESSION_KEY, $proofs);
        }
    }

    /**
     * Read clinic-scoped latest audit record strictly with clinic predicate.
     * Reconstructs full bundle from lossless numbered chunks and verifies digest.
     */
    public function getLatestAuditRecord(int $clinicId): ?array
    {
        if (!$this->canReadReview($clinicId)) return null;
        $row=$this->db->table('settings')->where(['clinic_id'=>$clinicId,'setting_key'=>'schedule_impact_latest_audit'])->get()->getRowArray();
        if (!$row) return null;
        try {
            $pointer=json_decode($row['setting_value'],true,512,JSON_THROW_ON_ERROR);
            if (!is_array($pointer)) return null;
            $bundle=$this->auditStore->read($clinicId,$pointer);
            return $bundle === null ? null : $bundle + ['audit_id'=>$pointer['first_id']];
        } catch (\Throwable $e) { return null; }
    }

    /** Current exceptions come only from the verified latest full-horizon review. */
    public function getRetainedExceptions(int $clinicId): array
    {
        $review=$this->getLatestAuditRecord($clinicId);
        if ($review === null || !hash_equals((string)($review['configuration_digest']??''),$this->configurationDigest($clinicId))) return [];
        $today=(new DateTimeImmutable('now',$this->clinicSettings->timezone($clinicId)))->format('Y-m-d');
        $result=[];
        foreach ($review['affected_items']??[] as $item) {
            $id=ClinicSchedulingSettings::positiveInt($item['appointment_id']??null);
            if ($id===null) continue;
            $row=$this->db->table('appointments')->where(['clinic_id'=>$clinicId,'id'=>$id])->get()->getRowArray();
            if (!$row || $row['appointment_date']<$today || in_array($row['status'],['cancelled','no_show'],true)) continue;
            foreach (['appointment_date','duration','provider_user_id','chair_name','status'] as $field) {
                if ((string)($row[$field]??'')!==(string)($item[$field]??'')) continue 2;
            }
            if (substr($row['appointment_time'],0,5)!==$item['appointment_time']) continue;
            $disposition=$review['dispositions'][$id]??null;
            if (!is_array($disposition) || ($disposition['action']??'')!=='retain') continue;
            $result[$id]=$disposition+['appointment_id'=>$id,'review_id'=>$review['review_id'],
                'actor_id'=>$review['actor_user_id'],'applied_at'=>$review['applied_at']];
        }
        return $result;
    }

    private function canReadReview(int $clinicId): bool
    {
        if ($clinicId<=0 || (int)session('active_clinic_id')!==$clinicId || session('global_mode')===true
            || session('is_impersonating')===true || (int)session('impersonated_clinic_id')>0
            || ApiRuntimeContext::getUserId()!==null || ApiRuntimeContext::getClinicId()!==null) return false;
        $auth=new IonAuth();$actor=(int)$auth->getUserId();$permissions=new PermissionService($this->db);
        return $auth->loggedIn() && $permissions->getTenantRoleId($actor,$clinicId)!==null
            && ($permissions->hasPermission($actor,'settings','view') || $permissions->hasPermission($actor,'settings','edit'));
    }

    private function configurationDigest(int $clinicId): string
    {
        $keys=['timezone','working_hours_start','working_hours_end','working_days','appointment_duration',
            'dynamic_doctor_scheduling_enabled','booking_chair_count','booking_chair_names'];
        $settings=$this->db->table('settings')->select('setting_key,setting_value,setting_type')->where('clinic_id',$clinicId)->whereIn('setting_key',$keys)->orderBy('setting_key')->get()->getResultArray();
        $profiles=$this->db->tableExists('clinic_provider_profiles') ? $this->db->table('clinic_provider_profiles')->select('user_id,is_bookable,is_clinician,weekly_hours_json,exceptions_json')->where('clinic_id',$clinicId)->orderBy('user_id')->get()->getResultArray() : [];
        $roles=$this->db->query('SELECT cu.user_id,cu.role_id,cu.status,u.active,r.slug,r.is_doctor,r.is_active,r.clinic_id FROM clinic_users cu JOIN users u ON u.id=cu.user_id JOIN roles r ON r.id=cu.role_id WHERE cu.clinic_id=? ORDER BY cu.user_id',[$clinicId])->getResultArray();
        return hash('sha256',json_encode([$settings,$profiles,$roles],JSON_THROW_ON_ERROR));
    }

    /**
     * Current read-only clinic health report accessible without proposing changes.
     */
    public function generateCurrentHealthReport(BaseConnection $db, int $clinicId): array
    {
        $currentHours = $this->clinicSettings->hours($clinicId);
        $enabled = $this->directoryService->isEnabled($clinicId);
        $dto = [
            'working_hours_start' => $currentHours['start'],
            'working_hours_end' => $currentHours['end'],
            'appointment_duration' => (string) $this->clinicSettings->get($clinicId, 'appointment_duration', '30'),
            'working_days' => $currentHours['days'],
            'dynamic_doctor_scheduling_enabled' => $enabled ? '1' : '0',
        ];
        if (!$enabled) {
            $chairs = $this->clinicSettings->chairs($clinicId);
            $dto['booking_chair_count'] = (string) count($chairs);
            $dto['booking_chair_names'] = $chairs;
        }

        return $this->generateReport($db, $clinicId, 'clinic_hours', $dto, null, false);
    }

    /**
     * Authorize active session actor in clinic.
     * Denies Bearer context, global mode, impersonation violations.
     */
    protected function authorizedActor(int $clinicId): ?int
    {
        if (ApiRuntimeContext::getUserId() !== null || ApiRuntimeContext::getClinicId() !== null
            || $clinicId <= 0 || (int) session('active_clinic_id') !== $clinicId
            || session('global_mode') === true) {
            return null;
        }

        $auth = new IonAuth();
        $actor = (int) $auth->getUserId();
        if (!$auth->loggedIn() || $actor <= 0) {
            return null;
        }

        $permissions = new PermissionService($this->db);
        $impersonating = session('is_impersonating') === true || (int) session('impersonated_clinic_id') > 0;

        if ($impersonating) {
            $clinic = $this->db->table('clinics')->where(['id' => $clinicId, 'status' => 'active'])->get()->getRowArray();
            if (!$clinic || session('is_impersonating') !== true || (int) session('impersonated_clinic_id') !== $clinicId
                || !$permissions->isSuperAdmin($actor)) {
                return null;
            }
        } elseif ($permissions->getTenantRoleId($actor, $clinicId) === null) {
            return null;
        }

        return $permissions->hasPermission($actor, 'settings', 'edit') ? $actor : null;
    }
}
