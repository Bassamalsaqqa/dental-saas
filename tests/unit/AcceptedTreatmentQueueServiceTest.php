<?php

namespace Tests\Unit;

use App\Services\AcceptedTreatmentQueueService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class AcceptedTreatmentQueueServiceTest extends CIUnitTestCase
{
    protected $db;
    private array $clinics = [];
    private array $patients = [];
    private array $treatments = [];
    private array $procedures = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect();
    }

    protected function tearDown(): void
    {
        $this->delete('clinical_procedure_status_history', 'procedure_id', $this->procedures);
        $this->delete('clinical_procedures', 'id', $this->procedures);
        $this->delete('treatments', 'id', $this->treatments);
        $this->delete('patients', 'id', $this->patients);
        $this->delete('clinics', 'id', $this->clinics);
        parent::tearDown();
    }

    public function testQueueReturnsOnlyAcceptedUnscheduledWorkFromActiveClinic(): void
    {
        [$clinicId, $patientId, $treatmentId] = $this->fixture('Visible');
        $visible = $this->procedure($clinicId, $patientId, $treatmentId, 'accepted', null);
        $token = strtoupper(bin2hex(random_bytes(4)));
        $this->db->table('treatments')->insert(['clinic_id' => $clinicId, 'patient_id' => $patientId, 'treatment_id' => 'QS' . $token, 'treatment_name' => 'Scheduled treatment', 'treatment_type' => 'cleaning', 'treatment_description' => 'Scheduled queue exclusion test', 'status' => 'active', 'start_date' => date('Y-m-d'), 'estimated_duration' => 30, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        $scheduledTreatmentId = (int) $this->db->insertID();
        $this->treatments[] = $scheduledTreatmentId;
        $this->procedure($clinicId, $patientId, $scheduledTreatmentId, 'scheduled', 7001);
        [$otherClinic, $otherPatient, $otherTreatment] = $this->fixture('Other');
        $this->procedure($otherClinic, $otherPatient, $otherTreatment, 'accepted', null);

        $summary = (new AcceptedTreatmentQueueService($this->db))->summary($clinicId, 5);

        $this->assertSame(1, $summary['count']);
        $this->assertCount(1, $summary['items']);
        $this->assertSame($visible, (int) $summary['items'][0]['procedure_id']);
        $this->assertSame($patientId, (int) $summary['items'][0]['patient_id']);
    }

    private function fixture(string $label): array
    {
        $token = strtoupper(bin2hex(random_bytes(4)));
        $this->db->table('clinics')->insert(['name' => $label . ' Clinic', 'slug' => strtolower($label) . '-' . strtolower($token), 'uuid' => $this->uuid(), 'status' => 'active', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        $clinicId = (int) $this->db->insertID();
        $this->clinics[] = $clinicId;
        $this->db->table('patients')->insert(['clinic_id' => $clinicId, 'patient_id' => 'QP' . $token, 'first_name' => $label, 'last_name' => 'Patient', 'phone' => (string) random_int(100000000, 999999999), 'date_of_birth' => '1990-01-01', 'gender' => 'female', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        $patientId = (int) $this->db->insertID();
        $this->patients[] = $patientId;
        $this->db->table('treatments')->insert(['clinic_id' => $clinicId, 'patient_id' => $patientId, 'treatment_id' => 'QT' . $token, 'treatment_name' => $label . ' treatment', 'treatment_type' => 'cleaning', 'treatment_description' => 'Queue service test treatment', 'status' => 'active', 'start_date' => date('Y-m-d'), 'estimated_duration' => 30, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        $treatmentId = (int) $this->db->insertID();
        $this->treatments[] = $treatmentId;
        return [$clinicId, $patientId, $treatmentId];
    }

    private function procedure(int $clinicId, int $patientId, int $treatmentId, string $status, ?int $scheduledAppointmentId): int
    {
        $this->db->table('clinical_procedures')->insert(['clinic_id' => $clinicId, 'patient_id' => $patientId, 'treatment_id' => $treatmentId, 'scheduled_appointment_id' => $scheduledAppointmentId, 'provider_type' => 'none', 'procedure_name' => 'Queue procedure ' . $status, 'status' => $status, 'proposed_at' => date('Y-m-d H:i:s'), 'accepted_at' => $status === 'accepted' ? date('Y-m-d H:i:s') : null, 'scheduled_at' => $status === 'scheduled' ? date('Y-m-d H:i:s') : null, 'created_by' => 1, 'updated_by' => 1, 'status_changed_by' => 1, 'status_changed_at' => date('Y-m-d H:i:s'), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        $id = (int) $this->db->insertID();
        $this->procedures[] = $id;
        return $id;
    }

    private function delete(string $table, string $field, array $ids): void
    {
        if ($ids !== []) {
            $this->db->table($table)->whereIn($field, array_values(array_unique($ids)))->delete();
        }
    }

    private function uuid(): string
    {
        $hex = bin2hex(random_bytes(16));
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-4' . substr($hex, 13, 3) . '-a' . substr($hex, 17, 3) . '-' . substr($hex, 20, 12);
    }
}
