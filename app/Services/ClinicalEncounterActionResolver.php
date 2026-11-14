<?php

namespace App\Services;

/**
 * Resolves the direct clinical encounter action for an appointment.
 *
 * Requirements:
 * - Direct clinical encounter action must choose actual clinic+patient-owned examination
 *   explicitly associated with the appointment (not latest unrelated patient exam).
 * - If exact one valid associated examination: route existing examination/{id} under examinations.view.
 * - If none: explicit create?patient_id=..&appointment_id=.. under examinations.create.
 * - If multiple: no arbitrary latest -> route to patient clinical workspace/examinations tab until authoritative rule verified.
 * - If context cannot be proven safely or permissions missing: fallback to existing appointment detail.
 */
final class ClinicalEncounterActionResolver
{
    /**
     * @param int $clinicId The active clinic ID
     * @param array $appointment Appointment data array (must have 'id', 'patient_id', 'clinic_id')
     * @param array<array>|null $associatedExaminations Examination records explicitly associated with this appointment, or null if lookup failed/unavailable
     * @param bool $canViewExaminations Whether the user has examinations.view permission
     * @param bool $canCreateExaminations Whether the user has examinations.create permission
     * @param string $returnTo Context return target (default 'dashboard')
     * @return array{
     *     type: string,          // 'view' | 'create' | 'workspace' | 'fallback'
     *     url: string,
     *     label_key: string,     // Translation key in UI group
     *     default_label: string, // Fallback English label
     *     count: ?int,           // Number of verified associated examinations (null if unknown/unavailable)
     *     examination_id: ?int,  // If exact one, the examination ID
     *     state: string          // 'verified' | 'unavailable' | 'unverified_context' | 'unauthorized' | 'invalid_record'
     * }
     */
     public function resolve(
         int $clinicId,
         array $appointment,
         ?array $associatedExaminations,
         bool $canViewExaminations,
         bool $canCreateExaminations,
         string $returnTo = 'dashboard'
     ): array {
         $appointmentId = (int) ($appointment['id'] ?? 0);
         $patientId = (int) ($appointment['patient_id'] ?? 0);
         $appointmentClinicId = isset($appointment['clinic_id']) ? (int) $appointment['clinic_id'] : null;

         // Fallback generator when context cannot be proven safely, lookup is unavailable, or permission is missing
         $fallback = static function (?int $count = null, string $state = 'unavailable') use ($appointmentId, $returnTo): array {
             $url = $appointmentId > 0
                 ? base_url('appointment/' . $appointmentId . ($returnTo !== '' ? '?return_to=' . rawurlencode($returnTo) : ''))
                 : base_url('dashboard');

             return [
                 'type' => 'fallback',
                 'url' => $url,
                 'label_key' => 'appointmentActionView',
                 'default_label' => 'View',
                 'count' => $count,
                 'examination_id' => null,
                 'state' => $state,
             ];
         };

         // Strictly verify active clinic and appointment association; do NOT silently impute missing clinic_id
         if ($clinicId <= 0 || $appointmentId <= 0 || $patientId <= 0 || $appointmentClinicId === null || $appointmentClinicId !== $clinicId) {
             return $fallback(null, 'unverified_context');
         }

         // Distinguish unavailable/lookup failure from verified empty
         if ($associatedExaminations === null) {
             return $fallback(null, 'unavailable');
         }

         // Filter examinations strictly matching active clinic, patient, and explicit appointment association
         $validExams = array_values(array_filter($associatedExaminations, static function (array $exam) use ($clinicId, $patientId, $appointmentId): bool {
             return (int) ($exam['clinic_id'] ?? 0) === $clinicId
                 && (int) ($exam['patient_id'] ?? 0) === $patientId
                 && (int) ($exam['appointment_id'] ?? 0) === $appointmentId;
         }));

         $count = count($validExams);

         if ($count === 1) {
             if (!$canViewExaminations) {
                 return $fallback(1, 'unauthorized');
             }

             $examId = (int) ($validExams[0]['id'] ?? 0);
             if ($examId <= 0) {
                 return $fallback(1, 'invalid_record');
             }

             $url = base_url('examination/' . $examId . ($returnTo !== '' ? '?return_to=' . rawurlencode($returnTo) : ''));

             return [
                 'type' => 'view',
                 'url' => $url,
                 'label_key' => 'appointmentOpenExamination',
                 'default_label' => 'Open examination',
                 'count' => 1,
                 'examination_id' => $examId,
                 'state' => 'verified',
             ];
         }

         if ($count === 0) {
             if (!$canCreateExaminations) {
                 return $fallback(0, 'unauthorized');
             }

             $params = [
                 'patient_id' => $patientId,
                 'appointment_id' => $appointmentId,
             ];
             if ($returnTo !== '') {
                 $params['return_to'] = $returnTo;
             }

             return [
                 'type' => 'create',
                 'url' => base_url('examination/create?' . http_build_query($params)),
                 'label_key' => 'appointmentStartExamination',
                 'default_label' => 'Start examination',
                 'count' => 0,
                 'examination_id' => null,
                 'state' => 'verified',
             ];
         }

         // Multiple associated examinations: never arbitrarily pick latest.
         // Direct to patient clinical workspace/examinations tab for explicit review.
         if (!$canViewExaminations) {
             return $fallback($count, 'unauthorized');
         }

         $workspaceUrl = base_url('patient/' . $patientId . '?tab=examinations' . ($returnTo !== '' ? '&return_to=' . rawurlencode($returnTo) : ''));

         return [
             'type' => 'workspace',
             'url' => $workspaceUrl,
             'label_key' => 'appointmentPatientExaminations',
             'default_label' => 'Patient examinations',
             'count' => $count,
             'examination_id' => null,
             'state' => 'verified',
         ];
    }
}
