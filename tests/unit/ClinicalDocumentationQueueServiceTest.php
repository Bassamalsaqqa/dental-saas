<?php

namespace Tests\Unit;

use App\Models\ExaminationModel;
use App\Services\ClinicalDocumentationQueueService;
use App\Services\ExaminationNoteGovernanceService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class ClinicalDocumentationQueueServiceTest extends CIUnitTestCase
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
        $this->deleteRows('examination_note_addenda', 'examination_id', $this->examinationIds);
        $this->deleteRows('examination_note_signatures', 'examination_id', $this->examinationIds);
        $this->deleteRows('examinations', 'id', $this->examinationIds);
        $this->deleteRows('patients', 'id', $this->patientIds);
        $this->deleteRows('clinics', 'id', $this->clinicIds);
        parent::tearDown();
    }

    public function testQueueClassifiesUnsignedCompletedAndOverdueDrafts(): void
    {
        [$clinicId, $patientId] = $this->clinicAndPatient('primary');
        $readyId = $this->examination($clinicId, $patientId, 'completed', date('Y-m-d'));
        $signedId = $this->examination($clinicId, $patientId, 'completed', date('Y-m-d'));
        $overdueId = $this->examination($clinicId, $patientId, 'in_progress', date('Y-m-d', strtotime('-2 days')));
        $this->examination($clinicId, $patientId, 'pending', date('Y-m-d'));
        (new ExaminationNoteGovernanceService($this->db))->sign($clinicId, $signedId, 901, 'Queue Dentist');

        $summary = (new ClinicalDocumentationQueueService($this->db))->summary($clinicId, 10);

        $this->assertSame(1, $summary['ready_to_sign_count']);
        $this->assertSame(1, $summary['overdue_draft_count']);
        $this->assertSame([$readyId], array_map('intval', array_column($summary['ready_to_sign'], 'id')));
        $this->assertSame([$overdueId], array_map('intval', array_column($summary['overdue_drafts'], 'id')));
    }

    public function testQueueIsTenantScoped(): void
    {
        [$clinicId, $patientId] = $this->clinicAndPatient('visible');
        [$otherClinicId, $otherPatientId] = $this->clinicAndPatient('hidden');
        $visibleId = $this->examination($clinicId, $patientId, 'completed', date('Y-m-d'));
        $this->examination($otherClinicId, $otherPatientId, 'completed', date('Y-m-d'));

        $summary = (new ClinicalDocumentationQueueService($this->db))->summary($clinicId, 10);

        $this->assertSame(1, $summary['ready_to_sign_count']);
        $this->assertSame([$visibleId], array_map('intval', array_column($summary['ready_to_sign'], 'id')));
    }

    public function testRegistryDocumentationFiltersUseTheSameClassification(): void
    {
        [$clinicId, $patientId] = $this->clinicAndPatient('registry');
        $readyId = $this->examination($clinicId, $patientId, 'completed', date('Y-m-d'));
        $signedId = $this->examination($clinicId, $patientId, 'completed', date('Y-m-d'));
        $overdueId = $this->examination($clinicId, $patientId, 'pending', date('Y-m-d', strtotime('-1 day')));
        (new ExaminationNoteGovernanceService($this->db))->sign($clinicId, $signedId, 901, 'Queue Dentist');
        session()->set('active_clinic_id', $clinicId);
        $model = new ExaminationModel();

        $this->assertSame([$readyId], array_map('intval', array_column($model->getExaminationsByClinic($clinicId, 10, 0, '', 'examinations.id', 'asc', 'ready_to_sign'), 'id')));
        $this->assertSame([$signedId], array_map('intval', array_column($model->getExaminationsByClinic($clinicId, 10, 0, '', 'examinations.id', 'asc', 'signed'), 'id')));
        $this->assertSame([$overdueId], array_map('intval', array_column($model->getExaminationsByClinic($clinicId, 10, 0, '', 'examinations.id', 'asc', 'overdue_draft'), 'id')));
    }

    private function clinicAndPatient(string $label): array
    {
        $token = str_replace('.', '', uniqid($label, true));
        $this->db->table('clinics')->insert([
            'name' => 'Documentation Clinic ' . $token,
            'slug' => 'documentation-' . strtolower($token),
            'uuid' => $this->uuid(),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $clinicId = (int) $this->db->insertID();
        $this->clinicIds[] = $clinicId;

        $this->db->table('patients')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => 'DOC' . substr($token, -8),
            'first_name' => 'Queue',
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

    private function examination(int $clinicId, int $patientId, string $status, string $date): int
    {
        $token = substr(str_replace('.', '', uniqid('', true)), -8);
        $this->db->table('examinations')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'examination_id' => 'DQ' . $token,
            'examination_date' => $date,
            'examination_type' => 'initial',
            'chief_complaint' => 'Documentation queue test',
            'status' => $status,
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
        if ($ids && $this->db->tableExists($table)) {
            $this->db->table($table)->whereIn($field, $ids)->delete();
        }
    }

    private function uuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0x0fff) | 0x4000, random_int(0, 0x3fff) | 0x8000, random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff));
    }
}
