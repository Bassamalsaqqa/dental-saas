<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Shared reschedulability policy for canonical booking mutations and advisory edit proof.
 * Notes-only editing remains possible on locked appointments, but rescheduling is denied.
 */
final class AppointmentReschedulabilityService
{
    /**
     * Determine if an appointment is eligible for canonical rescheduling.
     *
     * Invariants:
     * 1. Belongs to active clinic.
     * 2. Active patient in clinic (required for proof and rescheduling).
     * 3. Upcoming temporal state: start >= now in clinic-local timezone.
     *    In-progress (start < now < end) and elapsed (end <= now) visits are locked and NEVER reschedulable.
     * 4. Scheduled or confirmed status.
     * 5. When dynamic doctor scheduling is disabled, populated provider assignment is notes-only.
     * 6. No encounter links: examinations, clinical_procedures.appointment_id, or clinical_charge_lines.
     *    Note: accepted procedures' scheduled_appointment_id alone does NOT lock rescheduling.
     * 7. Provider currently being unbookable or on leave does not deny rescheduling (explicit reassignment is valid).
     *
     * @param BaseConnection $db
     * @param int $clinicId
     * @param array|int $appointment Appointment row or ID
     * @param array|null $batchContext Optional pre-loaded batch lookups for O(1) evaluation
     * @param DateTimeImmutable|null $now Optional explicit reference time
     * @return bool
     */
    public static function isReschedulable(
        BaseConnection $db,
        int $clinicId,
        array|int $appointment,
        ?array $batchContext = null,
        ?DateTimeImmutable $now = null
    ): bool {
        if ($clinicId <= 0) {
            return false;
        }

        $row = is_array($appointment)
            ? $appointment
            : $db->table('appointments')->where(['id' => $appointment, 'clinic_id' => $clinicId])->get()->getRowArray();

        if (!$row || (int) ($row['clinic_id'] ?? 0) !== $clinicId) {
            return false;
        }

        $appointmentId = (int) ($row['id'] ?? 0);
        if ($appointmentId <= 0) {
            return false;
        }

        // Active patient requirement
        $patientId = (int) ($row['patient_id'] ?? 0);
        if ($patientId <= 0) {
            return false;
        }

        if ($batchContext !== null && isset($batchContext['patientActiveMap'])) {
            if (!($batchContext['patientActiveMap'][$patientId] ?? false)) {
                return false;
            }
        } else {
            $patient = $db->query(
                'SELECT id, status FROM patients WHERE id = ? AND clinic_id = ?',
                [$patientId, $clinicId]
            )->getRowArray();

            if (!$patient || ($patient['status'] ?? '') !== 'active') {
                return false;
            }
        }

        // Temporal boundary check: MUST be upcoming (start >= now in clinic timezone)
        /** @var DateTimeZone $tz */
        $tz = $batchContext['timezone'] ?? (new ClinicSchedulingSettings($db))->timezone($clinicId);
        $appointmentDate = (string) ($row['appointment_date'] ?? '');
        $appointmentTime = (string) ($row['appointment_time'] ?? '');
        $duration = (int) ($row['duration'] ?? 30);

        if ($appointmentDate === '' || $appointmentTime === '' || $duration <= 0) {
            return false;
        }

        $temporalState = ClinicTemporalPolicyService::classifyAppointment($appointmentDate, $appointmentTime, $duration, $tz, $now);
        if ($temporalState !== ClinicTemporalPolicyService::STATE_UPCOMING) {
            return false;
        }

        // Scheduled or confirmed status
        $status = (string) ($row['status'] ?? '');
        if (!in_array($status, ['scheduled', 'confirmed'], true)) {
            return false;
        }

        // Dynamic disabled: populated provider is notes-only and denies proof/rescheduling
        $dynamicEnabled = $batchContext['dynamicEnabled'] ?? (new ProviderDirectoryService($db))->isEnabled($clinicId);
        $hasPopulatedProvider = isset($row['provider_user_id']) && $row['provider_user_id'] !== null && $row['provider_user_id'] !== '';

        if (!$dynamicEnabled && $hasPopulatedProvider) {
            return false;
        }

        // Clinical encounter links
        if ($batchContext !== null && isset($batchContext['hasExamMap'])) {
            if (!empty($batchContext['hasExamMap'][$appointmentId])) {
                return false;
            }
        } else {
            $examCount = (int) ($db->query(
                'SELECT COUNT(*) AS c FROM examinations WHERE clinic_id = ? AND appointment_id = ?',
                [$clinicId, $appointmentId]
            )->getRow('c') ?? 0);

            if ($examCount > 0) {
                return false;
            }
        }

        // Check encounter procedures (appointment_id), NOT scheduled future links (scheduled_appointment_id)
        if ($batchContext !== null && isset($batchContext['hasProcMap'])) {
            if (!empty($batchContext['hasProcMap'][$appointmentId])) {
                return false;
            }
        } else {
            $procCount = (int) ($db->query(
                'SELECT COUNT(*) AS c FROM clinical_procedures WHERE clinic_id = ? AND appointment_id = ?',
                [$clinicId, $appointmentId]
            )->getRow('c') ?? 0);

            if ($procCount > 0) {
                return false;
            }
        }

        if ($batchContext !== null && isset($batchContext['hasChargeMap'])) {
            if (!empty($batchContext['hasChargeMap'][$appointmentId])) {
                return false;
            }
        } else {
            $chargeCount = (int) ($db->query(
                'SELECT COUNT(*) AS c FROM clinical_charge_lines WHERE clinic_id = ? AND appointment_id = ?',
                [$clinicId, $appointmentId]
            )->getRow('c') ?? 0);

            if ($chargeCount > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Batch evaluate reschedulability for multiple appointments in O(1) query count.
     *
     * @param BaseConnection $db
     * @param int $clinicId
     * @param array<array> $appointments
     * @param DateTimeImmutable|null $now
     * @return array<int, bool> Keyed by appointment ID
     */
    public static function batchEvaluate(
        BaseConnection $db,
        int $clinicId,
        array $appointments,
        ?DateTimeImmutable $now = null
    ): array {
        if ($clinicId <= 0 || empty($appointments)) {
            return [];
        }

        $aptIds = [];
        $patientIds = [];

        foreach ($appointments as $apt) {
            $aId = (int) ($apt['id'] ?? 0);
            $pId = (int) ($apt['patient_id'] ?? 0);
            if ($aId > 0) {
                $aptIds[] = $aId;
            }
            if ($pId > 0) {
                $patientIds[] = $pId;
            }
        }

        $aptIds = array_values(array_unique($aptIds));
        $patientIds = array_values(array_unique($patientIds));

        // 1. Patient status lookup
        $patientActiveMap = [];
        if (!empty($patientIds)) {
            $pPlaceholders = implode(',', array_fill(0, count($patientIds), '?'));
            $patRows = $db->query(
                "SELECT id, status FROM patients WHERE clinic_id = ? AND id IN ({$pPlaceholders})",
                array_merge([$clinicId], $patientIds)
            )->getResultArray();
            foreach ($patRows as $p) {
                $patientActiveMap[(int) $p['id']] = (($p['status'] ?? '') === 'active');
            }
        }

        // 2. Clinical encounter lookups
        $hasExamMap = [];
        $hasProcMap = [];
        $hasChargeMap = [];

        if (!empty($aptIds)) {
            $aPlaceholders = implode(',', array_fill(0, count($aptIds), '?'));
            $params = array_merge([$clinicId], $aptIds);

            $examRows = $db->query(
                "SELECT DISTINCT appointment_id FROM examinations WHERE clinic_id = ? AND appointment_id IN ({$aPlaceholders})",
                $params
            )->getResultArray();
            foreach ($examRows as $r) {
                if ($r['appointment_id'] !== null) {
                    $hasExamMap[(int) $r['appointment_id']] = true;
                }
            }

            $procRows = $db->query(
                "SELECT DISTINCT appointment_id FROM clinical_procedures WHERE clinic_id = ? AND appointment_id IN ({$aPlaceholders})",
                $params
            )->getResultArray();
            foreach ($procRows as $r) {
                if ($r['appointment_id'] !== null) {
                    $hasProcMap[(int) $r['appointment_id']] = true;
                }
            }

            $chargeRows = $db->query(
                "SELECT DISTINCT appointment_id FROM clinical_charge_lines WHERE clinic_id = ? AND appointment_id IN ({$aPlaceholders})",
                $params
            )->getResultArray();
            foreach ($chargeRows as $r) {
                if ($r['appointment_id'] !== null) {
                    $hasChargeMap[(int) $r['appointment_id']] = true;
                }
            }
        }

        $settings = new ClinicSchedulingSettings($db);
        $tz = $settings->timezone($clinicId);
        $directoryService = new ProviderDirectoryService($db);
        $dynamicEnabled = $directoryService->isEnabled($clinicId);

        $batchContext = [
            'patientActiveMap' => $patientActiveMap,
            'hasExamMap' => $hasExamMap,
            'hasProcMap' => $hasProcMap,
            'hasChargeMap' => $hasChargeMap,
            'timezone' => $tz,
            'dynamicEnabled' => $dynamicEnabled,
        ];

        $results = [];
        foreach ($appointments as $apt) {
            $aId = (int) ($apt['id'] ?? 0);
            if ($aId <= 0) {
                continue;
            }
            $results[$aId] = self::isReschedulable($db, $clinicId, $apt, $batchContext, $now);
        }

        return $results;
    }
}
