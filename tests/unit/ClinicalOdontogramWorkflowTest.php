<?php

namespace Tests\Unit;

use App\Models\OdontogramModel;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class ClinicalOdontogramWorkflowTest extends CIUnitTestCase
{
    protected $db;
    private array $ids = [
        'odontograms' => [],
        'examinations' => [],
        'patients' => [],
        'clinics' => [],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect();
    }

    protected function tearDown(): void
    {
        foreach (['odontograms', 'examinations', 'patients', 'clinics'] as $table) {
            if ($this->ids[$table] !== []) {
                $this->db->table($table)->whereIn('id', $this->ids[$table])->delete();
            }
        }

        session()->remove('active_clinic_id');
        parent::tearDown();
    }

    public function testExaminationChartLayersVisitChangesOverPatientBaseline(): void
    {
        [$clinicId, $patientId, $examinationId] = $this->createFixture();
        session()->set('active_clinic_id', $clinicId);

        $baselineFillingId = $this->insertTooth($clinicId, $patientId, null, 5, 'filling');
        $baselineCrownId = $this->insertTooth($clinicId, $patientId, null, 7, 'crown');
        $visitOverrideId = $this->insertTooth($clinicId, $patientId, $examinationId, 5, 'cavity');

        $model = new OdontogramModel();
        $effective = $model->getEffectiveOdontogramForExamination($patientId, $examinationId);
        $byTooth = [];
        foreach ($effective as $row) {
            $byTooth[(int) $row['tooth_number']] = $row;
        }

        $this->assertCount(2, $effective);
        $this->assertSame($visitOverrideId, (int) $byTooth[5]['id']);
        $this->assertSame('cavity', $byTooth[5]['condition_type']);
        $this->assertFalse((bool) $byTooth[5]['inherited_from_baseline']);
        $this->assertSame($baselineCrownId, (int) $byTooth[7]['id']);
        $this->assertTrue((bool) $byTooth[7]['inherited_from_baseline']);

        $inheritedTooth = $model->getEffectiveToothCondition($patientId, 7, $examinationId);
        $this->assertSame($baselineCrownId, (int) $inheritedTooth['id']);
        $this->assertTrue((bool) $inheritedTooth['inherited_from_baseline']);

        $stats = $model->getOdontogramStats($patientId, $examinationId);
        $this->assertSame(1, $stats['cavities']);
        $this->assertSame(1, $stats['crowns']);
        $this->assertSame(2, $stats['total_conditions']);

        $this->assertSame('filling', $this->db->table('odontograms')->where('id', $baselineFillingId)->get()->getRowArray()['condition_type']);
    }

    private function createFixture(): array
    {
        $token = str_replace('.', '', uniqid('chart', true));
        $this->db->table('clinics')->insert([
            'name' => 'Chart Clinic ' . $token,
            'slug' => 'chart-' . strtolower($token),
            'uuid' => $this->uuid(),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $clinicId = (int) $this->db->insertID();
        $this->ids['clinics'][] = $clinicId;

        $this->db->table('patients')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => 'PCH' . substr($token, -8),
            'first_name' => 'Chart',
            'last_name' => 'Patient',
            'phone' => (string) random_int(100000000, 999999999),
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $patientId = (int) $this->db->insertID();
        $this->ids['patients'][] = $patientId;

        $this->db->table('examinations')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'examination_id' => 'ECH' . substr($token, -8),
            'examination_date' => date('Y-m-d'),
            'examination_type' => 'periodic',
            'chief_complaint' => 'Routine chart workflow test',
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $examinationId = (int) $this->db->insertID();
        $this->ids['examinations'][] = $examinationId;

        return [$clinicId, $patientId, $examinationId];
    }

    private function insertTooth(int $clinicId, int $patientId, ?int $examinationId, int $toothNumber, string $condition): int
    {
        $this->db->table('odontograms')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'examination_id' => $examinationId,
            'tooth_number' => $toothNumber,
            'tooth_position' => 'upper_right',
            'condition_type' => $condition,
            'treatment_status' => 'pending',
            'treatment_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $this->ids['odontograms'][] = $id;

        return $id;
    }

    private function uuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }
}
