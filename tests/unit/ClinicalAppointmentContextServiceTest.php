<?php

namespace Tests\Unit;

use App\Services\ClinicalAppointmentContextService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use DomainException;

final class ClinicalAppointmentContextServiceTest extends CIUnitTestCase
{
    protected $db;
    private array $clinicIds = [];
    private array $patientIds = [];
    private array $appointmentIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect();
    }

    protected function tearDown(): void
    {
        $this->deleteRows('appointments', $this->appointmentIds);
        $this->deleteRows('patients', $this->patientIds);
        $this->deleteRows('clinics', $this->clinicIds);
        parent::tearDown();
    }

    public function testEmptyContextRemainsOptional(): void
    {
        $this->assertNull((new ClinicalAppointmentContextService($this->db))->resolve(1, 1, null));
    }

    public function testValidContextRequiresSameClinicAndPatient(): void
    {
        [$clinicId, $patientId, $appointmentId] = $this->fixture();
        $appointment = (new ClinicalAppointmentContextService($this->db))->resolve($clinicId, $patientId, $appointmentId);

        $this->assertSame($appointmentId, (int) $appointment['id']);
        $this->assertSame($clinicId, (int) $appointment['clinic_id']);
        $this->assertSame($patientId, (int) $appointment['patient_id']);
    }

    public function testMismatchedPatientIsRejected(): void
    {
        [$clinicId, , $appointmentId] = $this->fixture();
        [, $otherPatientId] = $this->createPatient($clinicId);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('does not match');
        (new ClinicalAppointmentContextService($this->db))->resolve($clinicId, $otherPatientId, $appointmentId);
    }

    public function testCrossTenantAppointmentIsConcealed(): void
    {
        [, $patientId, $appointmentId] = $this->fixture();
        [$otherClinicId] = $this->fixture();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('does not match');
        (new ClinicalAppointmentContextService($this->db))->resolve($otherClinicId, $patientId, $appointmentId);
    }

    private function fixture(): array
    {
        $token = $this->token();
        $now = date('Y-m-d H:i:s');
        $this->db->table('clinics')->insert([
            'name' => 'Appointment Context ' . $token,
            'slug' => 'appointment-context-' . strtolower($token),
            'uuid' => $this->uuid(),
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $clinicId = (int) $this->db->insertID();
        $this->clinicIds[] = $clinicId;
        [, $patientId] = $this->createPatient($clinicId);

        $this->db->table('appointments')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'appointment_id' => 'APC' . substr($token, -8),
            'appointment_date' => '2026-08-01',
            'appointment_time' => '10:00:00',
            'duration' => 30,
            'appointment_type' => 'treatment',
            'chair_name' => 'Chair 1',
            'status' => 'in_chair',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $appointmentId = (int) $this->db->insertID();
        $this->appointmentIds[] = $appointmentId;

        return [$clinicId, $patientId, $appointmentId];
    }

    private function createPatient(int $clinicId): array
    {
        $token = $this->token();
        $now = date('Y-m-d H:i:s');
        $this->db->table('patients')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => 'ACP' . substr($token, -8),
            'first_name' => 'Context',
            'last_name' => 'Patient',
            'phone' => (string) random_int(100000000, 999999999),
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $patientId = (int) $this->db->insertID();
        $this->patientIds[] = $patientId;
        return [$clinicId, $patientId];
    }

    private function deleteRows(string $table, array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids !== []) {
            $this->db->table($table)->whereIn('id', $ids)->delete();
        }
    }

    private function token(): string
    {
        return str_replace('.', '', uniqid('ac', true));
    }

    private function uuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000, random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }
}
