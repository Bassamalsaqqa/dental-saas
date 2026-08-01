<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;

final class ClinicalAppointmentContextService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= Database::connect();
    }

    public function resolve(int $clinicId, int $patientId, mixed $appointmentId): ?array
    {
        if ($appointmentId === null || $appointmentId === '') {
            return null;
        }

        if ($clinicId <= 0 || $patientId <= 0 || !is_numeric($appointmentId) || (int) $appointmentId <= 0) {
            throw new DomainException('Invalid appointment context.');
        }

        $appointment = $this->db->table('appointments')
            ->where('clinic_id', $clinicId)
            ->where('patient_id', $patientId)
            ->where('id', (int) $appointmentId)
            ->get()
            ->getRowArray();

        if (!$appointment) {
            throw new DomainException('Appointment context does not match the active clinic and patient.');
        }

        return $appointment;
    }
}
