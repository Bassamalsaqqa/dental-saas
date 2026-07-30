<?php

namespace Tests\Unit;

use App\Services\ClinicalProcedureLifecycleService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use DateTimeImmutable;
use DomainException;

final class ClinicalProcedureLifecycleServiceTest extends CIUnitTestCase
{
    protected $db;
    private array $clinicIds = [];
    private array $patientIds = [];
    private array $examinationIds = [];
    private array $procedureIds = [];

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
            $this->deleteRows('examinations', 'id', $this->examinationIds);
            $this->deleteRows('patients', 'id', $this->patientIds);
            $this->deleteRows('clinics', 'id', $this->clinicIds);
        }

        parent::tearDown();
    }

    public function testNormalProcedureCanAdvanceWithoutSkipping(): void
    {
        $service = new ClinicalProcedureLifecycleService($this->db);
        $this->assertTrue($service->canTransition('proposed', 'presented'));
        $this->assertTrue($service->canTransition('presented', 'accepted'));
        $this->assertTrue($service->canTransition('accepted', 'scheduled'));
        $this->assertTrue($service->canTransition('scheduled', 'in_progress'));
        $this->assertTrue($service->canTransition('in_progress', 'completed'));
        $this->assertFalse($service->canTransition('proposed', 'completed'));
        $this->assertFalse($service->canTransition('completed', 'in_progress'));
    }

    public function testTransitionWritesCurrentStateTimestampAndHistory(): void
    {
        [$clinicId, $procedureId] = $this->createProcedureFixture();
        $service = new ClinicalProcedureLifecycleService($this->db);
        $changed = $service->transition(
            $clinicId,
            $procedureId,
            'presented',
            912,
            null,
            new DateTimeImmutable('2026-07-30 14:30:00')
        );

        $this->assertSame('presented', $changed['status']);
        $this->assertSame('2026-07-30 14:30:00', $changed['presented_at']);
        $this->assertSame(912, (int) $changed['status_changed_by']);

        $history = $this->db->table('clinical_procedure_status_history')
            ->where('clinic_id', $clinicId)
            ->where('procedure_id', $procedureId)
            ->get()
            ->getRowArray();

        $this->assertSame('proposed', $history['from_status']);
        $this->assertSame('presented', $history['to_status']);
        $this->assertSame(912, (int) $history['changed_by']);
    }

    public function testCreateStartsProposedAndRecordsInitialHistory(): void
    {
        [$clinicId, $patientId] = $this->createClinicAndPatient();
        $service = new ClinicalProcedureLifecycleService($this->db);
        $procedure = $service->create($clinicId, [
            'patient_id' => $patientId,
            'procedure_code' => 'D1110',
            'procedure_name' => 'Adult prophylaxis',
            'tooth_numbers' => '11,12',
            'provider_type' => 'guest',
            'provider_name_snapshot' => 'Visiting Hygienist',
        ], 915, new DateTimeImmutable('2026-07-30 12:00:00'));
        $this->procedureIds[] = (int) $procedure['id'];

        $this->assertSame('proposed', $procedure['status']);
        $this->assertSame($clinicId, (int) $procedure['clinic_id']);
        $this->assertSame($patientId, (int) $procedure['patient_id']);
        $this->assertSame('2026-07-30 12:00:00', $procedure['proposed_at']);

        $history = $this->db->table('clinical_procedure_status_history')
            ->where('procedure_id', $procedure['id'])
            ->get()
            ->getRowArray();
        $this->assertNull($history['from_status']);
        $this->assertSame('proposed', $history['to_status']);
        $this->assertSame(915, (int) $history['changed_by']);
    }

    public function testCreateRejectsRelationshipFromAnotherPatient(): void
    {
        [$clinicId, $patientId] = $this->createClinicAndPatient();
        [, $otherPatientId] = $this->createPatientInClinic($clinicId);
        $examinationId = $this->createExamination($clinicId, $otherPatientId);
        $service = new ClinicalProcedureLifecycleService($this->db);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('does not belong to the selected patient');
        $service->create($clinicId, [
            'patient_id' => $patientId,
            'examination_id' => $examinationId,
            'procedure_name' => 'Patient mismatch',
        ], 915);
    }

    public function testExceptionTransitionsRequireReason(): void
    {
        [$clinicId, $procedureId] = $this->createProcedureFixture();
        $service = new ClinicalProcedureLifecycleService($this->db);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('reason is required');
        $service->transition($clinicId, $procedureId, 'rejected', 912);
    }

    public function testProcedureLookupIsTenantScoped(): void
    {
        [, $procedureId] = $this->createProcedureFixture();
        [$otherClinicId] = $this->createClinicAndPatient();
        $service = new ClinicalProcedureLifecycleService($this->db);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('active clinic');
        $service->transition($otherClinicId, $procedureId, 'presented', 912);
    }

    public function testTerminalProcedureCannotReopen(): void
    {
        [$clinicId, $procedureId] = $this->createProcedureFixture('completed');
        $service = new ClinicalProcedureLifecycleService($this->db);

        $this->assertTrue($service->isTerminal('completed'));
        $this->assertSame([], $service->allowedNext('completed'));

        $this->expectException(DomainException::class);
        $service->transition($clinicId, $procedureId, 'in_progress', 912);
    }

    private function createProcedureFixture(string $status = 'proposed'): array
    {
        [$clinicId, $patientId] = $this->createClinicAndPatient();
        $timestampField = [
            'proposed' => 'proposed_at',
            'completed' => 'completed_at',
        ][$status] ?? 'status_changed_at';

        $this->db->table('clinical_procedures')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'provider_type' => 'none',
            'procedure_code' => 'D0150',
            'procedure_name' => 'Comprehensive oral evaluation',
            'tooth_numbers' => '11',
            'status' => $status,
            $timestampField => '2026-07-30 10:00:00',
            'created_by' => 901,
            'status_changed_by' => 901,
            'status_changed_at' => '2026-07-30 10:00:00',
            'created_at' => '2026-07-30 10:00:00',
            'updated_at' => '2026-07-30 10:00:00',
        ]);
        $procedureId = (int) $this->db->insertID();
        $this->procedureIds[] = $procedureId;

        return [$clinicId, $procedureId];
    }

    private function createClinicAndPatient(): array
    {
        $token = str_replace('.', '', uniqid('procedure', true));
        $this->db->table('clinics')->insert([
            'name' => 'Procedure Clinic ' . $token,
            'slug' => 'procedure-' . strtolower($token),
            'uuid' => $this->uuid(),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $clinicId = (int) $this->db->insertID();
        $this->clinicIds[] = $clinicId;

        $this->db->table('patients')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => 'PCP' . substr($token, -8),
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

    private function createPatientInClinic(int $clinicId): array
    {
        $token = str_replace('.', '', uniqid('linked', true));
        $this->db->table('patients')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => 'PLK' . substr($token, -8),
            'first_name' => 'Linked',
            'last_name' => 'Patient',
            'phone' => (string) random_int(100000000, 999999999),
            'date_of_birth' => '1988-01-01',
            'gender' => 'male',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $patientId = (int) $this->db->insertID();
        $this->patientIds[] = $patientId;

        return [$clinicId, $patientId];
    }

    private function createExamination(int $clinicId, int $patientId): int
    {
        $token = str_replace('.', '', uniqid('exam', true));
        $this->db->table('examinations')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'examination_id' => 'EXP' . substr($token, -8),
            'examination_date' => '2026-07-30',
            'examination_type' => 'initial',
            'chief_complaint' => 'Lifecycle relationship test',
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $this->examinationIds[] = $id;
        return $id;
    }

    private function deleteRows(string $table, string $field, array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids !== []) {
            $this->db->table($table)->whereIn($field, $ids)->delete();
        }
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
