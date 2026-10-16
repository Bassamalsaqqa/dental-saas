<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;

class ProviderScheduleService
{
    protected BaseConnection $db;
    protected PermissionService $permissionService;

    public function __construct(?BaseConnection $db = null, ?PermissionService $permissionService = null)
    {
        $this->db = $db ?? Database::connect();
        $this->permissionService = $permissionService ?? new PermissionService($this->db);
    }

    /**
     * Configure provider profile with serialization and atomic lock.
     * Requires active actor with settings edit permission in this clinic.
     *
     * @param int $clinicId
     * @param int $userId
     * @param array $data Validated DTO
     * @param int $actorId
     * @return array
     * @throws DomainException
     */
    public function configureProfile(int $clinicId, int $userId, array $data, int $actorId, ?array $impactProof = null): array
    {
        $deny = static fn(string $key): DomainException => new DomainException(lang('DoctorScheduleSettings.' . $key));
        if ($this->db->transDepth !== 0) throw $deny('saveFailed');
        $auth = new \App\Libraries\IonAuth();
        if ($clinicId <= 0 || $userId <= 0 || $actorId <= 0 || !$auth->loggedIn() || (int) $auth->getUserId() !== $actorId
            || (int) session()->get('active_clinic_id') !== $clinicId || session()->get('global_mode') === true
            || session()->get('is_impersonating') === true || (int) session()->get('impersonated_clinic_id') > 0
            || \App\Libraries\ApiRuntimeContext::getUserId() !== null || \App\Libraries\ApiRuntimeContext::getClinicId() !== null) throw $deny('forbidden');
        try {
            $impactService = new ScheduleImpactService($this->db);
            $dto = $impactService->validateProposedProfileDto($data);
        } catch (\Throwable $e) {
            throw $deny('invalidInput');
        }
        try {
            if (!$this->db->transBegin()) throw $deny('saveFailed');
            $clinic = $this->db->query('SELECT id, status FROM clinics WHERE id = ? FOR UPDATE', [$clinicId])->getRowArray();
            if (!$clinic || $clinic['status'] !== 'active') throw $deny('forbidden');
            $impactService->lockState($clinicId, null, $userId);
            // Lock all actor/target authority rows before resolving canonical permissions.
            foreach (array_unique([$actorId, $userId]) as $id) {
                $user = $this->db->query('SELECT id, active FROM users WHERE id = ? FOR UPDATE', [$id])->getRowArray();
                $membership = $this->db->query('SELECT cu.id, cu.role_id, cu.status, r.slug, r.is_active, r.clinic_id AS role_clinic_id FROM clinic_users cu JOIN roles r ON r.id=cu.role_id WHERE cu.clinic_id=? AND cu.user_id=? FOR UPDATE', [$clinicId, $id])->getRowArray();
                if (!$user || (int) $user['active'] !== 1 || !$membership || $membership['status'] !== 'active'
                    || (int) $membership['is_active'] !== 1 || $membership['slug'] === 'super_admin'
                    || ($membership['role_clinic_id'] !== null && (int) $membership['role_clinic_id'] !== $clinicId)) throw $deny('forbidden');
            }
            $actorMembership=$this->db->query('SELECT role_id FROM clinic_users WHERE clinic_id=? AND user_id=? FOR UPDATE',[$clinicId,$actorId])->getRowArray();
            $grant=$this->db->query("SELECT rp.permission_id FROM role_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE rp.role_id=? AND rp.granted=1 AND p.module='settings' AND p.action IN ('edit','update') FOR UPDATE",[(int)$actorMembership['role_id']])->getRowArray();
            if(!$grant)throw $deny('forbidden');
            if (!$this->db->tableExists('clinic_provider_profiles')) throw $deny('schemaMissing');

            // Schedule Impact Verification Hook under the lock
            $report = $impactService->generateReport($this->db, $clinicId, 'provider_profile', $dto, $userId, false);

            $token = $impactProof['token'] ?? ($data['impact_proof_token'] ?? null);
            $acknowledged = $impactProof['acknowledged'] ?? ($data['acknowledged'] ?? null);
            $dispositions = (array) ($impactProof['dispositions'] ?? ($data['dispositions'] ?? []));
            $hasProofInput = ($token !== null) || ($acknowledged !== null) || !empty($dispositions);

            if ($report['after_conflicts_count'] > 0 || $hasProofInput) {
                $validationError = $impactService->validateProofAndDispositions(
                    $this->db,
                    $clinicId,
                    $actorId,
                    'provider_profile',
                    $dto,
                    $userId,
                    $token,
                    $acknowledged,
                    $dispositions,
                    $report
                );
                if ($validationError !== null) {
                    throw $deny($validationError);
                }
            }

            $existing = $this->db->query('SELECT * FROM clinic_provider_profiles WHERE clinic_id=? AND user_id=? FOR UPDATE', [$clinicId,$userId])->getRowArray();
            $now = date('Y-m-d H:i:s');
            $payload = ['is_bookable'=>$dto['is_bookable']?1:0,'is_clinician'=>$dto['is_clinician']?1:0,
                'weekly_hours_json'=>$dto['weekly_hours'] === null ? null : json_encode($dto['weekly_hours'],JSON_THROW_ON_ERROR),
                'exceptions_json'=>$dto['exceptions'] === null ? null : json_encode($dto['exceptions'],JSON_THROW_ON_ERROR),'updated_at'=>$now];
            if ($existing) {
                $saved=$this->db->table('clinic_provider_profiles')->where(['id'=>$existing['id'],'clinic_id'=>$clinicId])->update($payload);
                $profileId=(int)$existing['id'];
            } else {
                $payload += ['clinic_id'=>$clinicId,'user_id'=>$userId,'created_at'=>$now];
                $saved=$this->db->table('clinic_provider_profiles')->insert($payload);
                $profileId=(int)$this->db->insertID();
            }
            if (!$saved || $profileId<=0) throw $deny('saveFailed');

            $beforeConfig = [
                'is_bookable' => ((int) ($existing['is_bookable'] ?? 1) === 1),
                'is_clinician' => ((int) ($existing['is_clinician'] ?? 0) === 1),
                'weekly_hours' => isset($existing['weekly_hours_json']) ? json_decode((string) $existing['weekly_hours_json'], true) : null,
                'exceptions' => isset($existing['exceptions_json']) ? json_decode((string) $existing['exceptions_json'], true) : null,
            ];

            $impactService->recordAuditEvidence(
                $this->db,
                $clinicId,
                $actorId,
                'provider_profile',
                $userId,
                $beforeConfig,
                $dto,
                $report,
                $dispositions
            );

            $audit=new ControlPlaneAuditService(new \App\Models\ControlPlaneAuditModel($this->db));
            if (!$audit->logEvent('provider_schedule_update',['actor_user_id'=>$actorId,'route'=>'/settings/update-provider-schedule','method'=>'POST',
                'metadata'=>['clinic_id'=>$clinicId,'provider_user_id'=>$userId,'is_bookable'=>$dto['is_bookable'],'is_clinician'=>$dto['is_clinician']]],true)) throw $deny('saveFailed');
            if ($this->db->transDepth !== 1 || !$this->db->transStatus() || !$this->db->transCommit()) throw $deny('saveFailed');

            // Consume proof ONLY AFTER checked transCommit succeeds
            $impactService->consumeProof($clinicId, 'provider_profile', $userId);

            return ['success'=>true,'id'=>$profileId,'profile'=>['id'=>$profileId,'clinic_id'=>$clinicId,'user_id'=>$userId,'updated_at'=>$now]+$dto];
        } catch (\Throwable $e) {
            if ($this->db->transDepth>0) $this->db->transRollback();
            if ($e instanceof DomainException) throw $e;
            log_message('error','Provider schedule transaction failed.');
            throw $deny('saveFailed');
        }
    }

    /**
     * Get profile for a clinic and user.
     */
    public function getProfile(int $clinicId, int $userId): ?array
    {
        if ($clinicId <= 0 || $userId <= 0) {
            return null;
        }

        if (!$this->db->tableExists('clinic_provider_profiles')) {
            return null;
        }

        $row = $this->db->table('clinic_provider_profiles')
            ->where('clinic_id', $clinicId)
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        if (!$row) {
            return null;
        }

        return $this->parsePersistedProfileRow($row);
    }

    /** Strict pure persisted-profile parser shared by booking and batched impact reports. */
    public function parsePersistedProfileRow(array $row): array
    {
        try {
            foreach(['is_bookable','is_clinician'] as $flag) if(!in_array($row[$flag],[0,1,'0','1'],true))throw new DomainException('Invalid flag');
            $weekly=$row['weekly_hours_json']===null ? null : json_decode($row['weekly_hours_json'],true,512,JSON_THROW_ON_ERROR);
            $exceptions=$row['exceptions_json']===null ? null : json_decode($row['exceptions_json'],true,512,JSON_THROW_ON_ERROR);
            if(($row['weekly_hours_json']!==null && !is_array($weekly)) || ($row['exceptions_json']!==null && !is_array($exceptions)))throw new DomainException('Invalid windows');
            $validated=$this->validateProfileDto(['is_bookable'=>$row['is_bookable'],'is_clinician'=>$row['is_clinician'],'weekly_hours'=>$weekly,'exceptions'=>$exceptions]);
        } catch(\Throwable $e) { throw new DomainException(lang('DoctorScheduleSettings.invalidInput')); }

        return [
            'id'           => (int) $row['id'],
            'clinic_id'    => (int) $row['clinic_id'],
            'user_id'      => (int) $row['user_id'],
            'is_bookable'  => ((int) ($row['is_bookable'] ?? 1) === 1),
            'is_clinician' => ((int) ($row['is_clinician'] ?? 0) === 1),
            'weekly_hours' => $validated['weekly_hours'],
            'exceptions'   => $validated['exceptions'],
            'created_at'   => $row['created_at'],
            'updated_at'   => $row['updated_at'],
        ];
    }

    /**
     * Resolve effective working intervals for a provider on a specific date.
     * Intersects exceptions, weekly hours, and clinic working hours strictly via explicit clinic DB.
     *
     * @return array Array of ['start' => 'HH:MM', 'end' => 'HH:MM'] intervals
     */
    public function getEffectiveSchedule(int $clinicId, int $userId, string $date): array
    {
        if ($clinicId<=0 || $userId<=0 || !ClinicSchedulingSettings::validDate($date)) return [];
        $hours=(new ClinicSchedulingSettings($this->db))->hours($clinicId);
        $day=strtolower((new \DateTimeImmutable($date))->format('l'));
        if (!in_array($day,$hours['days'],true)) return [];
        $profile=$this->getProfile($clinicId,$userId);
        [$windows,$error] = ClinicScheduleAvailabilityService::resolveEffectiveWindows($date,$hours,$profile);
        if ($error==='PROVIDER_PROFILE_CORRUPT') throw new DomainException(lang('DoctorScheduleSettings.invalidInput'));
        return $windows ?? [];
    }

    /**
     * Validate and normalize incoming profile DTO.
     */
    protected function validateProfileDto(array $data): array
    {
        foreach (['is_bookable','is_clinician'] as $flag) if (array_key_exists($flag,$data) && !in_array($data[$flag],[true,false,0,1,'0','1'],true)) throw new DomainException('Invalid profile flag.');
        $isBookable = (bool) ($data['is_bookable'] ?? true);
        $isClinician = (bool) ($data['is_clinician'] ?? false);

        $weeklyHours = null;
        if (isset($data['weekly_hours'])) {
            if (!is_array($data['weekly_hours'])) {
                throw new DomainException('weekly_hours must be an array or null.');
            }
            $weeklyHours = $this->validateWeeklyHours($data['weekly_hours']);
        }

        $exceptions = null;
        if (isset($data['exceptions'])) {
            if (!is_array($data['exceptions'])) {
                throw new DomainException('exceptions must be an array or null.');
            }
            $exceptions = $this->validateExceptions($data['exceptions']);
        }

        return [
            'is_bookable'  => $isBookable,
            'is_clinician' => $isClinician,
            'weekly_hours' => $weeklyHours,
            'exceptions'   => $exceptions,
        ];
    }

    protected function validateWeeklyHours(array $days): array
    {
        $validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $normalized = [];

        foreach ($days as $day => $intervals) {
            $cleanDay = strtolower(trim((string) $day));
            if (!in_array($cleanDay, $validDays, true)) {
                throw new DomainException("Invalid weekday '{$day}'.");
            }

            if (!is_array($intervals)) {
                throw new DomainException("Intervals for '{$cleanDay}' must be an array.");
            }

            $normalized[$cleanDay] = $this->validateIntervals($intervals, $cleanDay);
        }

        return $normalized;
    }

    protected function validateExceptions(array $exceptions): array
    {
        $normalized = [];
        if (count($exceptions)>366) throw new DomainException('Too many exceptions.');

        foreach ($exceptions as $date => $intervals) {
            $cleanDate = trim((string) $date);
            if (!ClinicSchedulingSettings::validDate($cleanDate)) {
                throw new DomainException("Invalid exception date '{$date}', must be YYYY-MM-DD.");
            }

            if (!is_array($intervals)) {
                throw new DomainException("Exception intervals for '{$cleanDate}' must be an array.");
            }

            $normalized[$cleanDate] = $this->validateIntervals($intervals, $cleanDate);
        }

        return $normalized;
    }

    protected function validateIntervals(array $intervals, string $context): array
    {
        $validated = [];
        if (count($intervals)>12) throw new DomainException('Too many windows.');

        foreach ($intervals as $interval) {
            if (!is_array($interval) || !isset($interval['start']) || !isset($interval['end']) || !is_string($interval['start']) || !is_string($interval['end'])) {
                throw new DomainException("Each interval in '{$context}' must have 'start' and 'end' keys.");
            }

            $start = trim((string) $interval['start']);
            $end = trim((string) $interval['end']);

            if (!ClinicSchedulingSettings::validTime($start) || !ClinicSchedulingSettings::validTime($end)) {
                throw new DomainException("Interval times in '{$context}' must be in HH:MM format.");
            }

            $startTime = strtotime("1970-01-01 {$start}:00");
            $endTime = strtotime("1970-01-01 {$end}:00");

            if ($startTime >= $endTime) {
                throw new DomainException("Start time '{$start}' must be earlier than end time '{$end}' in '{$context}'.");
            }

            $validated[] = [
                'start' => $start,
                'end'   => $end,
            ];
        }

        // Sort by start time
        usort($validated, static fn($a, $b) => strcmp($a['start'], $b['start']));

        // Verify non-overlapping intervals within same day
        for ($i = 0; $i < count($validated) - 1; $i++) {
            if (strcmp($validated[$i]['end'], $validated[$i + 1]['start']) > 0) {
                throw new DomainException("Overlapping intervals detected in '{$context}': {$validated[$i]['end']} overlaps {$validated[$i + 1]['start']}.");
            }
        }

        return $validated;
    }

    /**
     * Explicit clinic DB read for working hours.
     */
}
