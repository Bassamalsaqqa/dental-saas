<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\ApiRuntimeContext;
use App\Libraries\IonAuth;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Throwable;

class AppointmentSchedulingService
{
    protected const ALLOWED_TYPES = [
        'consultation',
        'treatment',
        'follow_up',
        'emergency',
        'cleaning',
        'checkup',
    ];

    protected BaseConnection $db;
    protected PermissionService $permissionService;
    protected PlanGuard $planGuard;
    protected TreatmentProcedureIntegrationService $procedureIntegration;
    protected ActivityLogger $activityLogger;
    protected WhatsAppWorkflowService $whatsAppWorkflowService;
    protected ProviderDirectoryService $directoryService;
    protected ProviderScheduleService $scheduleService;
    protected ClinicSchedulingSettings $clinicSettings;

    public function __construct(
        ?BaseConnection $db = null,
        ?PermissionService $permissionService = null,
        ?PlanGuard $planGuard = null,
        ?TreatmentProcedureIntegrationService $procedureIntegration = null,
        ?ActivityLogger $activityLogger = null,
        ?WhatsAppWorkflowService $whatsAppWorkflowService = null,
        ?ProviderDirectoryService $directoryService = null,
        ?ProviderScheduleService $scheduleService = null
    ) {
        $this->db = $db ?? Database::connect();
        $this->permissionService = $permissionService ?? new PermissionService($this->db);
        $this->planGuard = $planGuard ?? new PlanGuard($this->db);
        $this->procedureIntegration = $procedureIntegration ?? new TreatmentProcedureIntegrationService($this->db);
        $this->activityLogger = $activityLogger ?? new ActivityLogger();
        $this->whatsAppWorkflowService = $whatsAppWorkflowService ?? new WhatsAppWorkflowService();
        $this->directoryService = $directoryService ?? new ProviderDirectoryService($this->db);
        $this->scheduleService = $scheduleService ?? new ProviderScheduleService($this->db, $this->permissionService);
        $this->clinicSettings = new ClinicSchedulingSettings($this->db);
    }

    /**
     * Create an appointment for a clinic with serialized locking.
     */
    public function createForClinic(int $clinicId, array $input, int $actorId, string $context = 'session'): array
    {
        return $this->localizeResult($this->processBooking($clinicId, null, $input, $actorId, $context));
    }

    /**
     * Update an appointment for a clinic with serialized locking.
     */
    public function updateForClinic(int $clinicId, int $id, array $input, int $actorId, string $context = 'session'): array
    {
        $validId = ClinicSchedulingSettings::positiveInt($id);
        if ($validId === null) {
            return ['success' => false, 'code' => 'INVALID_ID', 'message' => 'Valid appointment ID required.'];
        }
        return $this->localizeResult($this->processBooking($clinicId, $validId, $input, $actorId, $context));
    }

    private function localizeResult(array $result): array
    {
        $code=$result['code']??'';
        if ($result['success']??false) $key=$code==='CREATED'?'created':'updated';
        elseif (in_array($code,['FORBIDDEN','SUBSCRIPTION_INACTIVE','CLINIC_INACTIVE'],true)) $key='forbidden';
        elseif ($code==='SCHEMA_NOT_READY') $key='schema';
        elseif (in_array($code,['PAST_APPOINTMENT_LOCKED','PATIENT_IMMUTABLE','STATUS_TRANSITION_FORBIDDEN'],true)) $key='locked';
        elseif (in_array($code,['SLOT_UNAVAILABLE','PROVIDER_NOT_AVAILABLE','PROVIDER_NOT_WORKING','OUTSIDE_WORKING_HOURS','NON_WORKING_DAY'],true)) $key='unavailable';
        elseif (str_starts_with($code,'INVALID_') || str_ends_with($code,'_REQUIRED') || $code==='CROSS_MIDNIGHT' || $code==='AMBIGUOUS_DST_INTERVAL') $key='invalid';
        else $key='failed';
        $result['message']=lang('DoctorSchedulingOutcome.'.$key);
        return $result;
    }

    /**
     * Shared booking engine for create and update.
     */
    protected function processBooking(int $clinicId, ?int $id, array $input, int $actorId, string $context): array
    {
        // 1. Strict context check
        if ($context !== 'session' && $context !== 'bearer') {
            return ['success' => false, 'code' => 'INVALID_CONTEXT', 'message' => 'Unknown context.'];
        }

        // 2. Validate IDs
        $validClinicId = ClinicSchedulingSettings::positiveInt($clinicId);
        $validActorId = ClinicSchedulingSettings::positiveInt($actorId);
        if ($validClinicId === null || $validActorId === null) {
            return ['success' => false, 'code' => 'INVALID_CONTEXT', 'message' => 'Clinic ID and Actor ID required.'];
        }

        // 3. Validate context matching and impersonation invariants
        $contextError = $this->validateContextAuth($validClinicId, $validActorId, $context);
        if ($contextError !== null) {
            return $contextError;
        }

        // 4. Initial permission check (before lock)
        $action = ($id !== null) ? 'edit' : 'create';
        if (!$this->permissionService->hasPermission($validActorId, 'appointments', $action)) {
            return ['success' => false, 'code' => 'FORBIDDEN', 'message' => "Actor does not have permission to {$action} appointments."];
        }

        // 5. Subscription standing check (PlanGuard void/throws wrapped)
        $subError = $this->checkSubscriptionStanding($validClinicId);
        if ($subError !== null) {
            return $subError;
        }

        // 6. Pre-validate scalar inputs before casts
        $preValidationError = $this->preValidateBookingInput($validClinicId, $id, $input);
        if ($preValidationError !== null) {
            return $preValidationError;
        }

        // 7. Ensure transDepth property is 0 before starting owned transaction
        if ($this->db->transDepth !== 0) {
            return [
                'success' => false,
                'code'    => 'TRANSACTION_IN_PROGRESS',
                'message' => 'Cannot start booking: another transaction is already in progress.',
            ];
        }

        try {
            if (!$this->db->transBegin()) { throw new \RuntimeException('Booking transaction could not start.'); }
            // 8. Safe coarse clinic serialization: Lock active clinic row FOR UPDATE FIRST
            $clinic = $this->db->query(
                'SELECT id, status FROM clinics WHERE id = ? FOR UPDATE',
                [$validClinicId]
            )->getRowArray();

            if (!$clinic || ($clinic['status'] ?? '') !== 'active') {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'CLINIC_INACTIVE', 'message' => 'Clinic is not active.'];
            }

            // 9. Recheck PlanGuard subscription under clinic lock
            $subErrorUnderLock = $this->checkSubscriptionStanding($validClinicId);
            if ($subErrorUnderLock !== null) {
                $this->db->transRollback();
                return $subErrorUnderLock;
            }

            // 10. Revalidate actor and canonical active membership/role under lock
            $actorError = $this->revalidateActorUnderLock($validClinicId, $validActorId, $action);
            if ($actorError !== null) {
                $this->db->transRollback();
                return $actorError;
            }

            // 11. If updating, retrieve and lock existing appointment
            $existing = null;
            if ($id !== null) {
                $existing = $this->db->query(
                    'SELECT * FROM appointments WHERE id = ? AND clinic_id = ? FOR UPDATE',
                    [$id, $validClinicId]
                )->getRowArray();

                if (!$existing) {
                    $this->db->transRollback();
                    return ['success' => false, 'code' => 'APPOINTMENT_NOT_FOUND', 'message' => 'Appointment not found.'];
                }

                // Patient immutability rule: updates preserve patient always
                if (isset($input['patient_id'])) {
                    $inputPatientId = ClinicSchedulingSettings::positiveInt($input['patient_id']);
                    if ($inputPatientId === null || $inputPatientId !== (int) $existing['patient_id']) {
                        $this->db->transRollback();
                        return ['success' => false, 'code' => 'PATIENT_IMMUTABLE', 'message' => 'Appointment patient cannot be changed.'];
                    }
                }

                // Status owned by visit stage: reject attempted changed status
                if (isset($input['status']) && (string) $input['status'] !== (string) $existing['status']) {
                    $this->db->transRollback();
                    return [
                        'success' => false,
                        'code'    => 'STATUS_TRANSITION_FORBIDDEN',
                        'message' => 'Appointment status can only be modified through visit stage transitions.',
                    ];
                }
            }

            // 12. Validate patient under lock
            $patientId = ($id !== null)
                ? (int) $existing['patient_id']
                : (ClinicSchedulingSettings::positiveInt($input['patient_id'] ?? null) ?? 0);

            if ($patientId <= 0) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'INVALID_PATIENT', 'message' => 'Patient is required.'];
            }

            $patient = $this->db->query(
                'SELECT id, first_name, last_name, status FROM patients WHERE id = ? AND clinic_id = ? FOR UPDATE',
                [$patientId, $validClinicId]
            )->getRowArray();

            if (!$patient || ($patient['status'] ?? '') !== 'active') {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'PATIENT_NOT_FOUND', 'message' => 'Active patient not found in this clinic.'];
            }

            // 13. Reschedulability and locked-visit assessment
            $today = (new DateTimeImmutable('now', $this->clinicSettings->timezone($validClinicId)))->format('Y-m-d');
            $dynamicEnabled = $this->directoryService->isEnabled($validClinicId);
            $schemaReady = $this->directoryService->isSchemaReady();

            if ($id !== null) {
                $isFutureDate = ($existing['appointment_date'] >= $today);
                $isScheduledOrConfirmed = in_array((string) $existing['status'], ['scheduled', 'confirmed'], true);

                // Check encounter and charge links
                $examLinkCount = (int) ($this->db->query(
                    'SELECT COUNT(*) AS c FROM examinations WHERE clinic_id = ? AND appointment_id = ?',
                    [$validClinicId, $id]
                )->getRow('c') ?? 0);

                $chargeLinkCount = (int) ($this->db->query(
                    'SELECT COUNT(*) AS c FROM clinical_charge_lines WHERE clinic_id = ? AND appointment_id = ?',
                    [$validClinicId, $id]
                )->getRow('c') ?? 0);

                $procEncounterCount = (int) ($this->db->query(
                    'SELECT COUNT(*) AS c FROM clinical_procedures WHERE clinic_id = ? AND appointment_id = ?',
                    [$validClinicId, $id]
                )->getRow('c') ?? 0);

                $hasClinicalLinks = ($examLinkCount > 0 || $chargeLinkCount > 0 || $procEncounterCount > 0);
                $isReschedulable = ($isFutureDate && $isScheduledOrConfirmed && !$hasClinicalLinks);

                // Evaluate requested changes
                $reqDate = isset($input['appointment_date']) ? (string) $input['appointment_date'] : $existing['appointment_date'];
                $reqTime = isset($input['appointment_time']) ? substr(trim((string) $input['appointment_time']), 0, 5) : substr((string) $existing['appointment_time'], 0, 5);
                $reqDuration = isset($input['duration']) ? (int) $input['duration'] : (int) $existing['duration'];
                $reqType = isset($input['appointment_type']) ? (string) $input['appointment_type'] : (string) $existing['appointment_type'];
                $reqNotes = isset($input['notes']) ? (string) $input['notes'] : (string) ($existing['notes'] ?? '');

                $existingProviderId = isset($existing['provider_user_id']) && $existing['provider_user_id'] !== null
                    ? (int) $existing['provider_user_id']
                    : null;
                $reqProviderId = array_key_exists('provider_user_id', $input)
                    ? ClinicSchedulingSettings::positiveInt($input['provider_user_id'])
                    : $existingProviderId;

                $existingChairName = trim((string) ($existing['chair_name'] ?? ''));
                $reqChairName = array_key_exists('chair_name', $input)
                    ? trim((string) $input['chair_name'])
                    : $existingChairName;

                $scheduleChanged = ($reqDate !== $existing['appointment_date']
                    || $reqTime !== substr((string) $existing['appointment_time'], 0, 5)
                    || $reqDuration !== (int) $existing['duration']
                    || $reqProviderId !== $existingProviderId);

                $metaChanged = ($reqType !== (string) $existing['appointment_type']
                    || (!$dynamicEnabled && $reqChairName !== $existingChairName));

                if (!$isReschedulable || (!$scheduleChanged && !$metaChanged) || (!$dynamicEnabled && $existingProviderId !== null)) {
                    // Locked visit: reject changed schedule, type, provider, or chair
                    if ($scheduleChanged || $metaChanged) {
                        $this->db->transRollback();
                        return [
                            'success' => false,
                            'code'    => 'PAST_APPOINTMENT_LOCKED',
                            'message' => 'Schedule and details for past or clinically locked visits cannot be modified.',
                        ];
                    }

                    // Notes-only update: preserves provider even if inactive, bypasses availability checks
                    $now = date('Y-m-d H:i:s');
                    $notesSaved = $this->db->table('appointments')
                        ->where('id', $id)
                        ->where('clinic_id', $validClinicId)
                        ->update([
                            'notes'      => $reqNotes,
                            'updated_at' => $now,
                        ]);

                    if (!$notesSaved || $this->db->transStatus() === false) {
                        $this->db->transRollback();
                        return ['success' => false, 'code' => 'PERSISTENCE_FAILED', 'message' => 'Failed to update appointment.'];
                    }

                    // Mandatory activity audit in transaction
                    $patientName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')) ?: 'Patient';
                    $logMsg = "Appointment notes updated for {$patientName} on {$existing['appointment_date']}";
                    $this->writeActivityLog($validClinicId, $validActorId, 'update', $id, $logMsg, $now);

                    if ($this->db->transStatus() === false || $this->db->transDepth !== 1) {
                        $this->db->transRollback();
                        return ['success' => false, 'code' => 'COMMIT_FAILED', 'message' => 'Transaction commit failed.'];
                    }

                    if (!$this->db->transCommit()) { throw new \RuntimeException('Booking commit failed.'); }

                    $freshAppointment = $this->db->table('appointments')->where('id', $id)->get()->getRowArray();
                    return [
                        'success'     => true,
                        'code'        => 'UPDATED',
                        'message'     => 'Appointment updated successfully.',
                        'id'          => $id,
                        'appointment' => $freshAppointment,
                    ];
                }
            }

            // 14. Booking target fields (Create or Reschedulable Update)
            $date = ($id !== null && !isset($input['appointment_date']))
                ? (string) $existing['appointment_date']
                : (string) $input['appointment_date'];

            $rawTime = ($id !== null && !isset($input['appointment_time']))
                ? (string) $existing['appointment_time']
                : (string) $input['appointment_time'];
            $time = substr(trim($rawTime), 0, 5);

            $duration = ($id !== null && !isset($input['duration']))
                ? (int) $existing['duration']
                : (int) $input['duration'];

            $appointmentType = ($id !== null && !isset($input['appointment_type']))
                ? (string) $existing['appointment_type']
                : (string) $input['appointment_type'];

            $notes = ($id !== null && !isset($input['notes']))
                ? (string) ($existing['notes'] ?? '')
                : (string) ($input['notes'] ?? '');

            // 15. Strict date, time, duration, DST validation
            $dateTimeError = $this->validateDateTimeDurationDst($validClinicId, $date, $time, $duration);
            if ($dateTimeError !== null) {
                $this->db->transRollback();
                return $dateTimeError;
            }

            // 16. Clinic working hours validation
            $clinicHours = $this->clinicSettings->hours($validClinicId);
            $dayOfWeek = strtolower(date('l', strtotime($date)));
            if (!in_array($dayOfWeek, $clinicHours['days'], true)) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NON_WORKING_DAY', 'message' => 'Appointments cannot be scheduled on non-working days.'];
            }

            $startMinutes = ((int) substr($time, 0, 2) * 60) + (int) substr($time, 3, 2);
            $endMinutes = $startMinutes + $duration;
            $endTimeStr = sprintf('%02d:%02d', intdiv($endMinutes, 60), $endMinutes % 60);

            if ($time < $clinicHours['start'] || $endTimeStr > $clinicHours['end']) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'code'    => 'OUTSIDE_WORKING_HOURS',
                    'message' => "Appointments can only be scheduled during clinic working hours ({$clinicHours['start']} - {$clinicHours['end']}).",
                ];
            }

            // 17. Dynamic scheduling vs Legacy mode validation
            $providerUserId = null;
            $chairNameSnapshot = '';

            if ($dynamicEnabled) {
                if (!$schemaReady) {
                    $this->db->transRollback();
                    return [
                        'success' => false,
                        'code'    => 'SCHEMA_NOT_READY',
                        'message' => 'Dynamic scheduling is enabled but database schema is not installed.',
                    ];
                }

                $rawProvId = $input['provider_user_id'] ?? ($existing['provider_user_id'] ?? null);
                $providerUserId = ClinicSchedulingSettings::positiveInt($rawProvId);
                if ($providerUserId === null) {
                    $this->db->transRollback();
                    return [
                        'success' => false,
                        'code'    => 'PROVIDER_REQUIRED',
                        'message' => 'A doctor/provider selection is required.',
                    ];
                }

                // Preserve existing snapshot when same provider ID; otherwise query bookable provider
                $existingProvId = ($existing !== null && isset($existing['provider_user_id']) && $existing['provider_user_id'] !== null)
                    ? (int) $existing['provider_user_id']
                    : null;

                $provider = $this->directoryService->findBookable($validClinicId, $providerUserId, true);
                if (!$provider) {
                    $this->db->transRollback();
                    return [
                        'success' => false,
                        'code'    => 'PROVIDER_NOT_AVAILABLE',
                        'message' => 'The selected doctor is not available or not bookable.',
                    ];
                }

                // Denormalized name snapshot: never overwritten on identity rename when same provider
                if ($existingProvId === $providerUserId && !empty($existing['chair_name'])) {
                    $chairNameSnapshot = (string) $existing['chair_name'];
                } else {
                    $chairNameSnapshot = mb_substr((string) $provider['name'], 0, 100);
                }

                // Provider working windows intersection
                $effectiveWindows = $this->scheduleService->getEffectiveSchedule($validClinicId, $providerUserId, $date);
                if (empty($effectiveWindows)) {
                    $this->db->transRollback();
                    return [
                        'success' => false,
                        'code'    => 'PROVIDER_NOT_WORKING',
                        'message' => 'The selected doctor is not scheduled to work on this date.',
                    ];
                }

                $fitsWindow = false;
                foreach ($effectiveWindows as $w) {
                    if ($time >= $w['start'] && $endTimeStr <= $w['end']) {
                        $fitsWindow = true;
                        break;
                    }
                }

                if (!$fitsWindow) {
                    $this->db->transRollback();
                    return [
                        'success' => false,
                        'code'    => 'OUTSIDE_WORKING_HOURS',
                        'message' => 'The requested appointment time falls outside doctor working hours.',
                    ];
                }

                // Overlap check FOR UPDATE: includes same doctor + NULL provider legacy appointments
                $hasConflict = $this->checkDynamicOverlapsForUpdate($validClinicId, $date, $time, $duration, $providerUserId, $id);
                if ($hasConflict) {
                    $this->db->transRollback();
                    return [
                        'success' => false,
                        'code'    => 'SLOT_UNAVAILABLE',
                        'message' => 'The selected doctor is not available for this time slot.',
                    ];
                }

            } else {
                // Legacy Mode
                $rawChairName = trim((string) ($input['chair_name'] ?? ($existing['chair_name'] ?? '')));
                if ($rawChairName === '') {
                    $this->db->transRollback();
                    return [
                        'success' => false,
                        'code'    => 'CHAIR_REQUIRED',
                        'message' => 'Doctor/chair name is required in legacy mode.',
                    ];
                }

                $configuredChairs = $this->clinicSettings->chairs($validClinicId);
                if (!in_array($rawChairName, $configuredChairs, true)) {
                    $this->db->transRollback();
                    return [
                        'success' => false,
                        'code'    => 'INVALID_CHAIR',
                        'message' => 'Selected doctor/chair is not configured for this clinic.',
                    ];
                }

                $chairNameSnapshot = $rawChairName;

                // Never erase populated provider on feature off!
                $providerUserId = ($existing !== null && isset($existing['provider_user_id']) && $existing['provider_user_id'] !== null)
                    ? (int) $existing['provider_user_id']
                    : null;

                // Legacy overlap check FOR UPDATE
                $hasLegacyConflict = $this->checkLegacyOverlapsForUpdate($validClinicId, $date, $time, $duration, $chairNameSnapshot, $id);
                if ($hasLegacyConflict) {
                    $this->db->transRollback();
                    return [
                        'success' => false,
                        'code'    => 'SLOT_UNAVAILABLE',
                        'message' => 'The selected doctor is not available for this time slot.',
                    ];
                }
            }

            // 18. Persist Appointment
            $now = date('Y-m-d H:i:s');
            $appointmentData = [
                'clinic_id'        => $validClinicId,
                'patient_id'       => $patientId,
                'appointment_date' => $date,
                'appointment_time' => $time,
                'duration'         => $duration,
                'appointment_type' => $appointmentType,
                'chair_name'       => $chairNameSnapshot,
                'notes'            => $notes,
                'updated_at'       => $now,
            ];

            if ($schemaReady) {
                $appointmentData['provider_user_id'] = $providerUserId;
            }

            $appointmentId = null;

            if ($id === null) {
                // Insert new appointment
                $appointmentData['appointment_id'] = 'APT' . date('Ymd') . bin2hex(random_bytes(4));
                $appointmentData['status'] = 'scheduled';
                $appointmentData['created_by'] = $validActorId;
                $appointmentData['created_at'] = $now;

                $inserted = $this->db->table('appointments')->insert($appointmentData);
                if (!$inserted || $this->db->transStatus() === false) {
                    $this->db->transRollback();
                    return ['success' => false, 'code' => 'PERSISTENCE_FAILED', 'message' => 'Failed to save appointment.'];
                }

                $appointmentId = (int) $this->db->insertID();

                // 19. Accepted procedure handoff atomically in same checked transaction
                $rawProcId = $input['clinical_procedure_id'] ?? null;
                if (!empty($rawProcId)) {
                    $clinicalProcedureId = ClinicSchedulingSettings::positiveInt($rawProcId);
                    if ($clinicalProcedureId !== null) {
                        $this->procedureIntegration->scheduleAcceptedProcedure(
                            $validClinicId,
                            $clinicalProcedureId,
                            $appointmentId,
                            $patientId,
                            $validActorId
                        );
                    }
                }
            } else {
                // Update existing appointment
                $updated = $this->db->table('appointments')
                    ->where('id', $id)
                    ->where('clinic_id', $validClinicId)
                    ->update($appointmentData);

                if (!$updated || $this->db->transStatus() === false) {
                    $this->db->transRollback();
                    return ['success' => false, 'code' => 'PERSISTENCE_FAILED', 'message' => 'Failed to update appointment.'];
                }

                $appointmentId = $id;
            }

            // 20. Mandatory activity audit injected DB in transaction
            $actionLog = ($id === null) ? 'create' : 'update';
            $patientName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')) ?: 'Patient';
            $logMsg = ($id === null)
                ? "New {$appointmentType} appointment scheduled for {$patientName} on {$date} at {$time}"
                : "Appointment updated for {$patientName} - {$appointmentType} on {$date} at {$time}";

            $this->writeActivityLog($validClinicId, $validActorId, $actionLog, $appointmentId, $logMsg, $now);

            // 21. Check transaction status and transDepth exactly 1 before commit
            if ($this->db->transStatus() === false || $this->db->transDepth !== 1) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'COMMIT_FAILED', 'message' => 'Transaction commit failed.'];
            }

            if (!$this->db->transCommit()) { throw new \RuntimeException('Booking commit failed.'); }

            // 22. Post-commit operations (only after actual commit success)
            $freshAppointment = $this->db->table('appointments')->where('id', $appointmentId)->get()->getRowArray();

            try {
                $this->whatsAppWorkflowService->queueAppointmentConfirmation($validClinicId, $appointmentId, $validActorId);
            } catch (\Throwable $e) {
                log_message('warning', 'WhatsApp confirmation enqueue failed: ' . $e->getMessage());
            }

            return [
                'success'     => true,
                'code'        => ($id === null) ? 'CREATED' : 'UPDATED',
                'message'     => ($id === null) ? 'Appointment created successfully.' : 'Appointment updated successfully.',
                'id'          => $appointmentId,
                'appointment' => $freshAppointment,
            ];

        } catch (\Throwable $e) {
            log_message('error', 'Appointment scheduling transaction failed: {type}', ['type' => get_class($e)]);
            while ($this->db->transDepth > 0) {
                $this->db->transRollback();
            }
            return [
                'success' => false,
                'code'    => 'EXECUTION_ERROR',
                'message' => 'The appointment could not be saved. Please reload and try again.',
            ];
        }
    }

    /**
     * Compute available time slots for a clinic, date, and doctor/chair.
     * Shares validation without reservation.
     */
    public function availableSlots(
        int $clinicId,
        string $date,
        int $duration,
        ?int $providerUserId = null,
        ?int $excludeId = null,
        ?string $chairName = null
    ): array {
        $validClinicId = ClinicSchedulingSettings::positiveInt($clinicId);
        $validDuration = ClinicSchedulingSettings::positiveInt($duration, 480);

        if ($validClinicId === null || $validDuration === null || !ClinicSchedulingSettings::validDate($date)) {
            return [];
        }

        $validExcludeId = ($excludeId !== null) ? ClinicSchedulingSettings::positiveInt($excludeId) : null;
        $dynamicEnabled = $this->directoryService->isEnabled($validClinicId);
        $schemaReady = $this->directoryService->isSchemaReady();

        if ($dynamicEnabled && !$schemaReady) return [];

        if ($dynamicEnabled && $schemaReady) {
            $validProvId = ($providerUserId !== null) ? ClinicSchedulingSettings::positiveInt($providerUserId) : null;
            if ($validProvId === null) {
                return [];
            }

            $provider = $this->directoryService->findBookable($validClinicId, $validProvId);
            if (!$provider) {
                return [];
            }

            $effectiveWindows = $this->scheduleService->getEffectiveSchedule($validClinicId, $validProvId, $date);
            if (empty($effectiveWindows)) {
                return [];
            }

            // Fetch blocking appointments for this provider + legacy unassigned
            $builder = $this->db->table('appointments')
                ->select('appointment_time, duration')
                ->where('clinic_id', $validClinicId)
                ->where('appointment_date', $date)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->groupStart()
                    ->where('provider_user_id', $validProvId)
                    ->orWhere('provider_user_id IS NULL', null, false)
                ->groupEnd();

            if ($validExcludeId !== null) {
                $builder->where('id !=', $validExcludeId);
            }

            $appointments = $builder->get()->getResultArray();
            $slotIncrement = 15;
            $timeSlots = [];

            foreach ($effectiveWindows as $window) {
                $wStartMin = ((int) substr($window['start'], 0, 2) * 60) + (int) substr($window['start'], 3, 2);
                $wEndMin = ((int) substr($window['end'], 0, 2) * 60) + (int) substr($window['end'], 3, 2);

                for ($currMin = $wStartMin; ($currMin + $validDuration) <= $wEndMin; $currMin += $slotIncrement) {
                    $slotTime = sprintf('%02d:%02d', intdiv($currMin, 60), $currMin % 60);

                    // Skip DST gap invalid intervals
                    if ($this->validateDateTimeDurationDst($validClinicId, $date, $slotTime, $validDuration) !== null) {
                        continue;
                    }

                    $reqStartSec = $currMin * 60;
                    $reqEndSec = ($currMin + $validDuration) * 60;

                    $hasOverlap = false;
                    foreach ($appointments as $apt) {
                        $aptMin = ((int) substr($apt['appointment_time'], 0, 2) * 60) + (int) substr($apt['appointment_time'], 3, 2);
                        $aptStartSec = $aptMin * 60;
                        $aptEndSec = $aptStartSec + (((int) $apt['duration']) * 60);

                        // Half-open interval collision: [start, end)
                        if ($reqStartSec < $aptEndSec && $reqEndSec > $aptStartSec) {
                            $hasOverlap = true;
                            break;
                        }
                    }

                    if (!$hasOverlap) {
                        $timeSlots[] = [
                            'value'              => $slotTime,
                            'display'            => function_exists('formatTime') ? formatTime($slotTime) : $slotTime,
                            'remaining_capacity' => 1,
                            'chair_capacity'     => 1,
                            'chair_name'         => $provider['name'],
                            'provider_user_id'   => $validProvId,
                            'doctor_name'        => $provider['name'],
                        ];
                    }
                }
            }

            return $timeSlots;
        }

        // Legacy mode slot generation
        $cleanChairName = trim((string) $chairName);
        $clinicHours = $this->clinicSettings->hours($validClinicId);
        $dayOfWeek = strtolower(date('l', strtotime($date)));

        if (!in_array($dayOfWeek, $clinicHours['days'], true)) {
            return [];
        }

        $wStartMin = ((int) substr($clinicHours['start'], 0, 2) * 60) + (int) substr($clinicHours['start'], 3, 2);
        $wEndMin = ((int) substr($clinicHours['end'], 0, 2) * 60) + (int) substr($clinicHours['end'], 3, 2);
        $slotIncrement = 15;

        $configuredChairs = $this->clinicSettings->chairs($validClinicId);
        $chairCount = ($cleanChairName !== '') ? 1 : max(1, count($configuredChairs));

        $builder = $this->db->table('appointments')
            ->select('appointment_time, duration, chair_name')
            ->where('clinic_id', $validClinicId)
            ->where('appointment_date', $date)
            ->whereNotIn('status', ['cancelled', 'no_show']);

        if ($cleanChairName !== '') {
            $builder->where('chair_name', $cleanChairName);
        }

        if ($validExcludeId !== null) {
            $builder->where('id !=', $validExcludeId);
        }

        $appointments = $builder->get()->getResultArray();
        $timeSlots = [];

        for ($currMin = $wStartMin; ($currMin + $validDuration) <= $wEndMin; $currMin += $slotIncrement) {
            $slotTime = sprintf('%02d:%02d', intdiv($currMin, 60), $currMin % 60);

            if ($this->validateDateTimeDurationDst($validClinicId, $date, $slotTime, $validDuration) !== null) {
                continue;
            }

            $reqStartSec = $currMin * 60;
            $reqEndSec = ($currMin + $validDuration) * 60;
            $overlapCount = 0;

            foreach ($appointments as $apt) {
                $aptMin = ((int) substr($apt['appointment_time'], 0, 2) * 60) + (int) substr($apt['appointment_time'], 3, 2);
                $aptStartSec = $aptMin * 60;
                $aptEndSec = $aptStartSec + (((int) $apt['duration']) * 60);

                if ($reqStartSec < $aptEndSec && $reqEndSec > $aptStartSec) {
                    $overlapCount++;
                }
            }

            $remaining = max(0, $chairCount - $overlapCount);
            if ($remaining > 0) {
                $timeSlots[] = [
                    'value'              => $slotTime,
                    'display'            => function_exists('formatTime') ? formatTime($slotTime) : $slotTime,
                    'remaining_capacity' => $remaining,
                    'chair_capacity'     => $chairCount,
                    'chair_name'         => $cleanChairName,
                ];
            }
        }

        return $timeSlots;
    }

    /**
     * Check dynamic mode conflicts with half-open intervals [start, end) FOR UPDATE.
     */
    protected function checkDynamicOverlapsForUpdate(
        int $clinicId,
        string $date,
        string $time,
        int $duration,
        int $providerUserId,
        ?int $excludeId
    ): bool {
        $startMin = ((int) substr($time, 0, 2) * 60) + (int) substr($time, 3, 2);
        $reqStartSec = $startMin * 60;
        $reqEndSec = ($startMin + $duration) * 60;

        $sql = 'SELECT id, appointment_time, duration, provider_user_id
                FROM appointments
                WHERE clinic_id = ?
                  AND appointment_date = ?
                  AND status NOT IN (\'cancelled\', \'no_show\')
                  AND (provider_user_id = ? OR provider_user_id IS NULL)';

        $params = [$clinicId, $date, $providerUserId];

        if ($excludeId !== null && $excludeId > 0) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $sql .= ' FOR UPDATE';

        $appointments = $this->db->query($sql, $params)->getResultArray();

        foreach ($appointments as $apt) {
            $aptMin = ((int) substr($apt['appointment_time'], 0, 2) * 60) + (int) substr($apt['appointment_time'], 3, 2);
            $aptStartSec = $aptMin * 60;
            $aptEndSec = $aptStartSec + (((int) $apt['duration']) * 60);

            if ($reqStartSec < $aptEndSec && $reqEndSec > $aptStartSec) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check legacy mode chair overlaps FOR UPDATE.
     */
    protected function checkLegacyOverlapsForUpdate(
        int $clinicId,
        string $date,
        string $time,
        int $duration,
        string $chairName,
        ?int $excludeId
    ): bool {
        $startMin = ((int) substr($time, 0, 2) * 60) + (int) substr($time, 3, 2);
        $reqStartSec = $startMin * 60;
        $reqEndSec = ($startMin + $duration) * 60;

        $sql = 'SELECT id, appointment_time, duration, chair_name
                FROM appointments
                WHERE clinic_id = ?
                  AND appointment_date = ?
                  AND chair_name = ?
                  AND status NOT IN (\'cancelled\', \'no_show\')';

        $params = [$clinicId, $date, $chairName];

        if ($excludeId !== null && $excludeId > 0) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $sql .= ' FOR UPDATE';

        $appointments = $this->db->query($sql, $params)->getResultArray();

        foreach ($appointments as $apt) {
            $aptMin = ((int) substr($apt['appointment_time'], 0, 2) * 60) + (int) substr($apt['appointment_time'], 3, 2);
            $aptStartSec = $aptMin * 60;
            $aptEndSec = $aptStartSec + (((int) $apt['duration']) * 60);

            if ($reqStartSec < $aptEndSec && $reqEndSec > $aptStartSec) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate date, time, duration and DST transitions.
     * Rejects nonexistent local times (gaps) and ambiguous local times (folds).
     */
    protected function validateDateTimeDurationDst(int $clinicId, string $date, string $time, int $duration): ?array
    {
        if (!ClinicSchedulingSettings::validDate($date)) {
            return ['success' => false, 'code' => 'INVALID_DATE', 'message' => 'Valid ISO date (YYYY-MM-DD) required.'];
        }

        if (!ClinicSchedulingSettings::validTime($time)) {
            return ['success' => false, 'code' => 'INVALID_TIME', 'message' => 'Valid appointment time (HH:MM) required.'];
        }

        if ($duration < 1 || $duration > 480) {
            return ['success' => false, 'code' => 'INVALID_DURATION', 'message' => 'Duration must be between 1 and 480 minutes.'];
        }

        $startMinutes = ((int) substr($time, 0, 2) * 60) + (int) substr($time, 3, 2);
        $endMinutes = $startMinutes + $duration;

        if ($endMinutes > 1440) {
            return ['success' => false, 'code' => 'CROSS_MIDNIGHT', 'message' => 'Appointments cannot cross midnight.'];
        }

        // Canonical clinic timezone lookup; fail closed if invalid
        try {
            $tz = $this->clinicSettings->timezone($clinicId);
        } catch (\Throwable $e) {
            return ['success' => false, 'code' => 'INVALID_TIMEZONE', 'message' => 'Invalid clinic timezone configuration.'];
        }

        $endTimeStr = sprintf('%02d:%02d', intdiv($endMinutes, 60), $endMinutes % 60);

        // Gap test: check whether start and end local timestamps exist in local time
        $dtStart = DateTimeImmutable::createFromFormat('!Y-m-d H:i', "{$date} {$time}", $tz);
        if (!$dtStart || $dtStart->format('Y-m-d H:i') !== "{$date} {$time}") {
            return [
                'success' => false,
                'code'    => 'INVALID_DST_INTERVAL',
                'message' => 'The selected appointment time does not exist in local time (daylight saving time gap).',
            ];
        }

        $dtEnd = DateTimeImmutable::createFromFormat('!Y-m-d H:i', "{$date} {$endTimeStr}", $tz);
        if (!$dtEnd || $dtEnd->format('Y-m-d H:i') !== "{$date} {$endTimeStr}") {
            return [
                'success' => false,
                'code'    => 'INVALID_DST_INTERVAL',
                'message' => 'The appointment interval falls into a local daylight saving time gap.',
            ];
        }

        // Transition scan: inspect transitions crossing this local day
        // Skip baseline entry at index 0 to avoid false positives!
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
                            'success' => false,
                            'code'    => 'INVALID_DST_INTERVAL',
                            'message' => 'The selected appointment time crosses a local daylight saving time gap.',
                        ];
                    }
                } else {
                    // Fold (Fall back): [localAfter, localBefore] occurs twice (ambiguous)
                    if ($apptStartNaiveSec < $localBefore && $apptEndNaiveSec > $localAfter) {
                        return [
                            'success' => false,
                            'code'    => 'AMBIGUOUS_DST_INTERVAL',
                            'message' => 'The selected appointment time is ambiguous due to daylight saving time transition.',
                        ];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Validate context authentication and session/bearer invariants.
     */
    protected function validateContextAuth(int $clinicId, int $actorId, string $context): ?array
    {
        if ($context === 'bearer') {
            $apiClinicId = ApiRuntimeContext::getClinicId();
            $apiUserId = ApiRuntimeContext::getUserId();

            if ($apiClinicId !== $clinicId || $apiUserId !== $actorId) {
                return ['success' => false, 'code' => 'FORBIDDEN', 'message' => 'API context mismatch.'];
            }
            return null;
        }

        $auth = new IonAuth();
        if (ApiRuntimeContext::getUserId() !== null || ApiRuntimeContext::getClinicId() !== null
            || !$auth->loggedIn() || (int)$auth->getUserId() !== $actorId
            || (int)session('active_clinic_id') !== $clinicId || session('global_mode') === true) {
            return ['success'=>false,'code'=>'FORBIDDEN','message'=>'Session context mismatch.'];
        }
        if (session('is_impersonating') === true || (int)session('impersonated_clinic_id') > 0) {
            if (session('is_impersonating') !== true || (int)session('impersonated_clinic_id') !== $clinicId
                || !$this->permissionService->isSuperAdmin($actorId)) {
                return ['success'=>false,'code'=>'FORBIDDEN','message'=>'Invalid impersonation context.'];
            }
        }

        return null;
    }

    /**
     * Revalidate actor and active membership under locked transaction.
     */
    protected function revalidateActorUnderLock(int $clinicId, int $actorId, string $action): ?array
    {
        $actorUser = $this->db->query(
            'SELECT id, active FROM users WHERE id = ? FOR UPDATE',
            [$actorId]
        )->getRowArray();

        if (!$actorUser || (int) $actorUser['active'] !== 1) {
            return ['success' => false, 'code' => 'FORBIDDEN', 'message' => 'Actor is inactive or not found.'];
        }

        if (ApiRuntimeContext::getUserId() === null && ApiRuntimeContext::getClinicId() === null
            && session('is_impersonating') === true && (int)session('impersonated_clinic_id') === $clinicId) {
            $platform = $this->db->query("SELECT ur.id FROM user_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=? AND ur.is_active=1 AND r.slug='super_admin' AND r.clinic_id IS NULL AND r.is_active=1 AND (ur.expires_at IS NULL OR ur.expires_at>NOW()) FOR UPDATE",[$actorId])->getRowArray();
            return $platform ? null : ['success'=>false,'code'=>'FORBIDDEN','message'=>'Platform authority no longer active.'];
        }
        $membership = $this->db->query(
            'SELECT cu.id, cu.status, cu.role_id, r.slug AS role_slug, r.is_active AS role_active, r.clinic_id AS role_clinic_id
             FROM clinic_users cu
             JOIN roles r ON r.id = cu.role_id
             WHERE cu.clinic_id = ? AND cu.user_id = ?
             FOR UPDATE',
            [$clinicId, $actorId]
        )->getRowArray();

        if (!$membership || $membership['status'] !== 'active' || (int) $membership['role_active'] !== 1 || $membership['role_slug'] === 'super_admin') {
            return ['success' => false, 'code' => 'FORBIDDEN', 'message' => 'Actor has no active membership in this clinic.'];
        }

        if ($membership['role_clinic_id'] !== null && (int) $membership['role_clinic_id'] !== $clinicId) {
            return ['success' => false, 'code' => 'FORBIDDEN', 'message' => 'Actor role does not belong to this clinic.'];
        }

        $actions=$action==='edit'?['edit','update']:[$action];
        $placeholders=implode(',',array_fill(0,count($actions),'?'));
        $grant=$this->db->query("SELECT rp.permission_id FROM role_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE rp.role_id=? AND rp.granted=1 AND p.module='appointments' AND p.action IN ($placeholders) FOR UPDATE",array_merge([(int)$membership['role_id']],$actions))->getRowArray();
        if (!$grant) return ['success'=>false,'code'=>'FORBIDDEN','message'=>'Appointment permission is no longer active.'];

        return null;
    }

    /**
     * Pre-validate booking input before casts.
     */
    protected function preValidateBookingInput(int $clinicId, ?int $id, array $input): ?array
    {
        if ($id === null) {
            if (!isset($input['patient_id'])) {
                return ['success' => false, 'code' => 'INVALID_PATIENT', 'message' => 'Patient is required.'];
            }
            if (ClinicSchedulingSettings::positiveInt($input['patient_id']) === null) {
                return ['success' => false, 'code' => 'INVALID_PATIENT', 'message' => 'Valid patient ID required.'];
            }
        } elseif (isset($input['patient_id']) && ClinicSchedulingSettings::positiveInt($input['patient_id']) === null) {
            return ['success' => false, 'code' => 'INVALID_PATIENT', 'message' => 'Valid patient ID required.'];
        }

        if ($id === null || isset($input['appointment_date'])) {
            $rawDate = $input['appointment_date'] ?? null;
            if (!ClinicSchedulingSettings::validDate($rawDate)) {
                return ['success' => false, 'code' => 'INVALID_DATE', 'message' => 'Valid ISO date (YYYY-MM-DD) required.'];
            }
        }

        if ($id === null || isset($input['appointment_time'])) {
            $rawTime = $input['appointment_time'] ?? null;
            if (!is_string($rawTime) || preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::00)?$/D', $rawTime) !== 1) {
                return ['success' => false, 'code' => 'INVALID_TIME', 'message' => 'Valid appointment time (HH:MM) required.'];
            }
        }

        if ($id === null || isset($input['duration'])) {
            $rawDuration = $input['duration'] ?? null;
            if (ClinicSchedulingSettings::positiveInt($rawDuration, 480) === null) {
                return ['success' => false, 'code' => 'INVALID_DURATION', 'message' => 'Duration must be between 1 and 480 minutes.'];
            }
        }

        if ($id === null || isset($input['appointment_type'])) {
            $rawType = $input['appointment_type'] ?? '';
            if (!in_array($rawType, self::ALLOWED_TYPES, true)) {
                return ['success' => false, 'code' => 'INVALID_APPOINTMENT_TYPE', 'message' => 'Invalid appointment type.'];
            }
        }

        if (isset($input['chair_name']) && !is_string($input['chair_name'])) return ['success'=>false,'code'=>'INVALID_CHAIR','message'=>'Invalid booking resource.'];
        if (isset($input['notes']) && (!is_string($input['notes']) || strlen($input['notes'])>65535)) {
            return ['success' => false, 'code' => 'INVALID_NOTES', 'message' => 'Notes must be a string.'];
        }

        if (array_key_exists('provider_user_id', $input) && $input['provider_user_id'] !== null && $input['provider_user_id'] !== '') {
            if (ClinicSchedulingSettings::positiveInt($input['provider_user_id']) === null) {
                return ['success' => false, 'code' => 'INVALID_PROVIDER', 'message' => 'Valid doctor ID required.'];
            }
        }

        if (array_key_exists('clinical_procedure_id', $input) && $input['clinical_procedure_id'] !== null && $input['clinical_procedure_id'] !== '') {
            if (ClinicSchedulingSettings::positiveInt($input['clinical_procedure_id']) === null) {
                return ['success' => false, 'code' => 'INVALID_PROCEDURE', 'message' => 'Valid procedure ID required.'];
            }
        }

        return null;
    }

    /**
     * PlanGuard subscription standing wrapper.
     */
    protected function checkSubscriptionStanding(int $clinicId): ?array
    {
        try {
            $standing = $this->planGuard->assertSubscriptionActive($clinicId);
            if (is_array($standing) && isset($standing['allowed']) && !$standing['allowed']) {
                return [
                    'success' => false,
                    'code'    => 'SUBSCRIPTION_INACTIVE',
                    'message' => $standing['message'] ?? 'Subscription required or expired.',
                ];
            }
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'code'    => 'SUBSCRIPTION_INACTIVE',
                'message' => 'Subscription required or expired.',
            ];
        }

        return null;
    }

    /**
     * Mandatory audit activity log written directly in transaction with explicit clinic and actor.
     */
    protected function writeActivityLog(
        int $clinicId,
        int $actorId,
        string $action,
        int $appointmentId,
        string $description,
        string $now
    ): void {
        $saved = $this->db->table('activity_logs')->insert([
            'clinic_id'   => $clinicId,
            'user_id'     => $actorId,
            'action'      => $action,
            'entity_type' => 'appointment',
            'entity_id'   => $appointmentId,
            'description' => mb_substr($description, 0, 255),
            'created_at'  => $now,
        ]);
        if (!$saved) { throw new \RuntimeException('Required appointment audit failed.'); }
    }
}
