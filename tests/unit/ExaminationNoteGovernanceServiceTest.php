<?php

namespace Tests\Unit;

use App\Models\ExaminationModel;
use App\Services\ExaminationNoteGovernanceService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use DomainException;

final class ExaminationNoteGovernanceServiceTest extends CIUnitTestCase
{
    protected $db;
    private array $clinicIds = [];
    private array $patientIds = [];
    private array $examinationIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect();
    }

    protected function tearDown(): void
    {
        if ($this->db) {
            $this->deleteRows('examination_note_addenda', 'examination_id', $this->examinationIds);
            $this->deleteRows('examination_note_signatures', 'examination_id', $this->examinationIds);
            $this->deleteRows('examinations', 'id', $this->examinationIds);
            $this->deleteRows('patients', 'id', $this->patientIds);
            $this->deleteRows('clinics', 'id', $this->clinicIds);
        }
        parent::tearDown();
    }

    public function testCompletedExaminationCanBeSignedOnceWithImmutableSnapshot(): void
    {
        [$clinicId, $examinationId] = $this->fixture('completed');
        $service = new ExaminationNoteGovernanceService($this->db);

        $state = $service->sign($clinicId, $examinationId, 901, 'Test Dentist');
        $this->assertTrue($state['signed']);
        $this->assertSame('Test Dentist', $state['signature']['signed_by_name']);
        $this->assertSame(64, strlen($state['signature']['content_hash']));
        $snapshot = json_decode($state['signature']['snapshot_json'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Completed clinical note', $snapshot['clinical_findings']);
        $this->assertSame(1, (int) $snapshot['health_update_current_symptoms']);
        $this->assertSame('Localized swelling reported.', $snapshot['health_update_details']);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('already signed');
        $service->sign($clinicId, $examinationId, 901, 'Test Dentist');
    }

    public function testPendingExaminationCannotBeSigned(): void
    {
        [$clinicId, $examinationId] = $this->fixture('pending');
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Complete the examination');
        (new ExaminationNoteGovernanceService($this->db))->sign($clinicId, $examinationId, 901, 'Test Dentist');
    }

    public function testSignatureLookupIsTenantScoped(): void
    {
        [$clinicId, $examinationId] = $this->fixture('completed');
        [$otherClinicId] = $this->fixture('completed');
        $service = new ExaminationNoteGovernanceService($this->db);
        $service->sign($clinicId, $examinationId, 901, 'Test Dentist');

        $this->assertFalse($service->isSigned($otherClinicId, $examinationId));
        $this->assertFalse($service->state($otherClinicId, $examinationId)['signed']);
    }

    public function testAddendaAreAppendOnlyAndHashChained(): void
    {
        [$clinicId, $examinationId] = $this->fixture('completed');
        $service = new ExaminationNoteGovernanceService($this->db);
        $signed = $service->sign($clinicId, $examinationId, 901, 'Test Dentist');
        $first = $service->addAddendum($clinicId, $examinationId, 902, 'Reviewing Dentist', 'Correction', 'Corrected the tooth reference to tooth 16.');
        $second = $service->addAddendum($clinicId, $examinationId, 902, 'Reviewing Dentist', 'Clarification', 'No change to the diagnosis or treatment plan.');

        $this->assertCount(2, $second['addenda']);
        $this->assertSame($signed['signature']['content_hash'], $first['addenda'][0]['previous_hash']);
        $this->assertSame($second['addenda'][0]['content_hash'], $second['addenda'][1]['previous_hash']);
        $this->assertNotSame($second['addenda'][0]['content_hash'], $second['addenda'][1]['content_hash']);
    }

    public function testSignedExaminationCannotBeMutatedThroughModel(): void
    {
        [$clinicId, $examinationId] = $this->fixture('completed');
        (new ExaminationNoteGovernanceService($this->db))->sign($clinicId, $examinationId, 901, 'Test Dentist');
        session()->set('active_clinic_id', $clinicId);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('immutable');
        (new ExaminationModel())->update($examinationId, ['status' => 'pending']);
    }

    private function fixture(string $status): array
    {
        $token = str_replace('.', '', uniqid('note', true));
        $this->db->table('clinics')->insert([
            'name' => 'Note Clinic ' . $token,
            'slug' => 'note-' . strtolower($token),
            'uuid' => $this->uuid(),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $clinicId = (int) $this->db->insertID();
        $this->clinicIds[] = $clinicId;

        $this->db->table('patients')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => 'NTP' . substr($token, -8),
            'first_name' => 'Clinical',
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

        $this->db->table('examinations')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'examination_id' => 'NTX' . substr($token, -8),
            'examination_date' => '2026-08-02',
            'examination_type' => 'initial',
            'chief_complaint' => 'Clinical note governance test',
            'clinical_findings' => 'Completed clinical note',
            'diagnosis' => 'Test diagnosis',
            'health_update_current_symptoms' => 1,
            'health_update_details' => 'Localized swelling reported.',
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $examinationId = (int) $this->db->insertID();
        $this->examinationIds[] = $examinationId;
        return [$clinicId, $examinationId];
    }

    private function deleteRows(string $table, string $field, array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids !== [] && $this->db->tableExists($table)) {
            $this->db->table($table)->whereIn($field, $ids)->delete();
        }
    }

    private function uuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0x0fff) | 0x4000, random_int(0, 0x3fff) | 0x8000, random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff));
    }
}
