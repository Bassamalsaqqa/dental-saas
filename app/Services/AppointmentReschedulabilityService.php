<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DateTimeImmutable;

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
     * 3. Clinic-local today or future date.
     * 4. Scheduled or confirmed status.
     * 5. When dynamic doctor scheduling is disabled, populated provider assignment is notes-only.
     * 6. No encounter links: examinations, clinical_procedures.appointment_id, or clinical_charge_lines.
     *    Note: accepted procedures' scheduled_appointment_id alone does NOT lock rescheduling.
     * 7. Provider currently being unbookable or on leave does not deny rescheduling (explicit reassignment is valid).
     */
    public static function isReschedulable(BaseConnection $db, int $clinicId, array|int $appointment): bool
    {
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

        $patient = $db->query(
            'SELECT id, status FROM patients WHERE id = ? AND clinic_id = ?',
            [$patientId, $clinicId]
        )->getRowArray();

        if (!$patient || ($patient['status'] ?? '') !== 'active') {
            return false;
        }

        // Clinic-local today or future date
        $settings = new ClinicSchedulingSettings($db);
        $tz = $settings->timezone($clinicId);
        $today = (new DateTimeImmutable('now', $tz))->format('Y-m-d');
        $appointmentDate = (string) ($row['appointment_date'] ?? '');

        if ($appointmentDate === '' || $appointmentDate < $today) {
            return false;
        }

        // Scheduled or confirmed status
        $status = (string) ($row['status'] ?? '');
        if (!in_array($status, ['scheduled', 'confirmed'], true)) {
            return false;
        }

        // Dynamic disabled: populated provider is notes-only and denies proof/rescheduling
        $directoryService = new ProviderDirectoryService($db);
        $dynamicEnabled = $directoryService->isEnabled($clinicId);
        $hasPopulatedProvider = isset($row['provider_user_id']) && $row['provider_user_id'] !== null && $row['provider_user_id'] !== '';

        if (!$dynamicEnabled && $hasPopulatedProvider) {
            return false;
        }

        // Clinical encounter links
        $examCount = (int) ($db->query(
            'SELECT COUNT(*) AS c FROM examinations WHERE clinic_id = ? AND appointment_id = ?',
            [$clinicId, $appointmentId]
        )->getRow('c') ?? 0);

        if ($examCount > 0) {
            return false;
        }

        // Check encounter procedures (appointment_id), NOT scheduled future links (scheduled_appointment_id)
        $procCount = (int) ($db->query(
            'SELECT COUNT(*) AS c FROM clinical_procedures WHERE clinic_id = ? AND appointment_id = ?',
            [$clinicId, $appointmentId]
        )->getRow('c') ?? 0);

        if ($procCount > 0) {
            return false;
        }

        $chargeCount = (int) ($db->query(
            'SELECT COUNT(*) AS c FROM clinical_charge_lines WHERE clinic_id = ? AND appointment_id = ?',
            [$clinicId, $appointmentId]
        )->getRow('c') ?? 0);

        if ($chargeCount > 0) {
            return false;
        }

        return true;
    }
}
