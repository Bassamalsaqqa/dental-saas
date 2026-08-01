<?php

namespace Tests\Unit;

use App\Services\TreatmentProcedureIntegrationService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use DomainException;

final class TreatmentProcedureIntegrationServiceTest extends CIUnitTestCase
{
    protected $db;
    private array $clinicIds = [];
    private array $patientIds = [];
    private array $treatmentIds = [];
    private array $chargeLineIds = [];
    private array $procedureIds = [];
    private array $appointmentIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect();
    }

    protected function tearDown(): void
    {
        if ($this->db) {
            $this->deleteRows('clinical_procedure_status_history', 'procedure_id', $this->procedureIds);
            $this->deleteRows('clinical_procedures', 'id', $this->procedureIds);
            $this->deleteRows('clinical_charge_lines', 'id', $this->chargeLineIds);
            $this->deleteRows('treatments', 'id', $this->treatmentIds);
            $this->deleteRows('appointments', 'id', $this->appointmentIds);
            $this->deleteRows('patients', 'id', $this->patientIds);
            $this->deleteRows('clinics', 'id', $this->clinicIds);
        }

        parent::tearDown();
    }

    public function testPlannedTreatmentCreatesProposedProcedureWithInitialHistory(): void
    {
        [$clinicId, , $treatment] = $this->createTreatmentFixture();
        $service = new TreatmentProcedureIntegrationService($this->db);

        $procedure = $service->createForTreatment($clinicId, $treatment, 'planned', 901);
        $this->procedureIds[] = (int) $procedure['id'];

        $this->assertSame('proposed', $procedure['status']);
        $this->assertSame((int) $treatment['id'], (int) $procedure['treatment_id']);
        $this->assertSame((int) $treatment['patient_id'], (int) $procedure['patient_id']);

        $history = $this->history((int) $procedure['id']);
        $this->assertCount(1, $history);
        $this->assertNull($history[0]['from_status']);
        $this->assertSame('proposed', $history[0]['to_status']);
    }

    public function testStartNowRecordsExplicitAcceptedAndInProgressMilestones(): void
    {
        [$clinicId, , $treatment] = $this->createTreatmentFixture();
        $service = new TreatmentProcedureIntegrationService($this->db);

        $procedure = $service->createForTreatment($clinicId, $treatment, 'start_now', 902);
        $this->procedureIds[] = (int) $procedure['id'];

        $this->assertSame('in_progress', $procedure['status']);
        $this->assertNotEmpty($procedure['accepted_at']);
        $this->assertNotEmpty($procedure['started_at']);
        $this->assertSame(
            ['proposed', 'accepted', 'in_progress'],
            array_column($this->history((int) $procedure['id']), 'to_status')
        );
    }

    public function testChargeLineLinksToSameProcedureAndTreatmentPatientCannotChange(): void
    {
        [$clinicId, $patientId, $treatment] = $this->createTreatmentFixture();
        $chargeLine = $this->createChargeLine($clinicId, $patientId, (int) $treatment['id']);
        $service = new TreatmentProcedureIntegrationService($this->db);

        $procedure = $service->createForTreatment($clinicId, $treatment, 'planned', 903);
        $this->procedureIds[] = (int) $procedure['id'];
        $this->assertNull($procedure['clinical_charge_line_id']);

        $linked = $service->linkChargeLine($clinicId, (int) $treatment['id'], $chargeLine, 903);
        $this->assertSame((int) $chargeLine['id'], (int) $linked['clinical_charge_line_id']);

        [, $otherPatientId] = $this->createPatient($clinicId);
        $changed = $treatment;
        $changed['patient_id'] = $otherPatientId;

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('cannot be moved');
        $service->syncTreatment($clinicId, $changed, 903);
    }

    public function testTreatmentCompletionAdvancesProcedureAndLegacyTreatmentRemainsCompatible(): void
    {
        [$clinicId, , $treatment] = $this->createTreatmentFixture();
        $service = new TreatmentProcedureIntegrationService($this->db);
        $procedure = $service->createForTreatment($clinicId, $treatment, 'planned', 904);
        $this->procedureIds[] = (int) $procedure['id'];

        $completed = $service->completeForTreatment($clinicId, (int) $treatment['id'], 904);
        $this->assertSame('completed', $completed['status']);
        $this->assertSame(
            ['proposed', 'accepted', 'in_progress', 'completed'],
            array_column($this->history((int) $procedure['id']), 'to_status')
        );

        [, , $legacyTreatment] = $this->createTreatmentFixture($clinicId);
        $this->assertNull($service->completeForTreatment($clinicId, (int) $legacyTreatment['id'], 904));
    }

    public function testAppointmentContextPropagatesToProcedureAndChargeLine(): void
    {
        [$clinicId, $patientId, $treatment] = $this->createTreatmentFixture();
        $appointmentId = $this->createAppointment($clinicId, $patientId);
        $chargeLine = $this->createChargeLine($clinicId, $patientId, (int) $treatment['id']);
        $this->db->table('clinical_charge_lines')->where('id', $chargeLine['id'])->update(['appointment_id' => $appointmentId]);
        $chargeLine['appointment_id'] = $appointmentId;

        $procedure = (new TreatmentProcedureIntegrationService($this->db))->createForTreatment(
            $clinicId,
            $treatment,
            'planned',
            905,
            $chargeLine,
            $appointmentId
        );
        $this->procedureIds[] = (int) $procedure['id'];

        $this->assertSame($appointmentId, (int) $procedure['appointment_id']);
        $storedCharge = $this->db->table('clinical_charge_lines')->where('id', $chargeLine['id'])->get()->getRowArray();
        $this->assertSame($appointmentId, (int) $storedCharge['appointment_id']);
    }

    private function createTreatmentFixture(?int $clinicId = null): array
    {
        if ($clinicId === null) {
            $token = $this->token();
            $this->db->table('clinics')->insert([
                'name' => 'Treatment Procedure Clinic ' . $token,
                'slug' => 'treatment-procedure-' . strtolower($token),
                'uuid' => $this->uuid(),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $clinicId = (int) $this->db->insertID();
            $this->clinicIds[] = $clinicId;
        }

        [, $patientId] = $this->createPatient($clinicId);
        $token = $this->token();
        $this->db->table('treatments')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'treatment_id' => 'TPR' . substr($token, -8),
            'treatment_name' => 'Lifecycle Treatment',
            'treatment_type' => 'cleaning',
            'tooth_number' => '36',
            'treatment_description' => 'Treatment procedure integration test',
            'status' => 'active',
            'start_date' => date('Y-m-d'),
            'estimated_duration' => 30,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $treatmentId = (int) $this->db->insertID();
        $this->treatmentIds[] = $treatmentId;

        $treatment = $this->db->table('treatments')->where('id', $treatmentId)->get()->getRowArray();
        return [$clinicId, $patientId, $treatment];
    }

    private function createPatient(int $clinicId): array
    {
        $token = $this->token();
        $this->db->table('patients')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => 'TPP' . substr($token, -8),
            'first_name' => 'Procedure',
            'last_name' => 'Patient',
            'phone' => (string) random_int(100000000, 999999999),
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $patientId = (int) $this->db->insertID();
        $this->patientIds[] = $patientId;

        return [$clinicId, $patientId];
    }

    private function createChargeLine(int $clinicId, int $patientId, int $treatmentId): array
    {
        $this->db->table('clinical_charge_lines')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'treatment_id' => $treatmentId,
            'source_entity_type' => 'treatment',
            'source_entity_id' => $treatmentId,
            'provider_type' => 'none',
            'procedure_name' => 'Lifecycle charge',
            'quantity' => '1.00',
            'unit_price' => '100.00',
            'discount_amount' => '0.00',
            'tax_amount' => '0.00',
            'net_production_amount' => '100.00',
            'currency' => 'ILS',
            'service_date' => date('Y-m-d'),
            'charge_date' => date('Y-m-d'),
            'status' => 'planned',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $this->chargeLineIds[] = $id;

        return $this->db->table('clinical_charge_lines')->where('id', $id)->get()->getRowArray();
    }

    private function createAppointment(int $clinicId, int $patientId): int
    {
        $token = $this->token();
        $this->db->table('appointments')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'appointment_id' => 'ATP' . substr($token, -8),
            'appointment_date' => '2026-08-01',
            'appointment_time' => '09:00:00',
            'duration' => 30,
            'appointment_type' => 'treatment',
            'chair_name' => 'Chair 1',
            'status' => 'in_chair',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $this->appointmentIds[] = $id;
        return $id;
    }

    private function history(int $procedureId): array
    {
        return $this->db->table('clinical_procedure_status_history')
            ->where('procedure_id', $procedureId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function deleteRows(string $table, string $field, array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids !== []) {
            $this->db->table($table)->whereIn($field, $ids)->delete();
        }
    }

    private function token(): string
    {
        return str_replace('.', '', uniqid('tp', true));
    }

    private function uuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }
}
