<?php

namespace Tests\Unit;

use App\Services\AppointmentCheckoutService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use DomainException;

final class AppointmentCheckoutServiceTest extends CIUnitTestCase
{
    protected $db;
    private array $clinicIds = [];
    private array $patientIds = [];
    private array $appointmentIds = [];
    private array $examinationIds = [];
    private array $treatmentIds = [];
    private array $chargeLineIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect();
    }

    protected function tearDown(): void
    {
        if ($this->db) {
            $this->deleteRows('clinical_charge_lines', $this->chargeLineIds);
            $this->deleteRows('treatments', $this->treatmentIds);
            $this->deleteRows('examinations', $this->examinationIds);
            $this->deleteRows('appointments', $this->appointmentIds);
            $this->deleteRows('patients', $this->patientIds);
            $this->deleteRows('clinics', $this->clinicIds);
        }

        parent::tearDown();
    }

    public function testReviewAggregatesSameVisitClinicalWorkAndOpenCharges(): void
    {
        [$clinicId, $patientId, $appointmentId] = $this->createVisitFixture();
        $examinationId = $this->createExamination($clinicId, $patientId);
        $treatmentId = $this->createTreatment($clinicId, $patientId, $examinationId);
        $chargeLineId = $this->createChargeLine($clinicId, $patientId, $appointmentId, $examinationId, $treatmentId, '150.00', 'ILS');

        $review = (new AppointmentCheckoutService($this->db))->review($clinicId, $appointmentId);

        $this->assertSame('patient_date_context', $review['link_basis']);
        $this->assertSame([$examinationId], array_map('intval', array_column($review['examinations'], 'id')));
        $this->assertSame([$treatmentId], array_map('intval', array_column($review['treatments'], 'id')));
        $this->assertSame([$chargeLineId], $review['invoiceable_charge_line_ids']);
        $this->assertSame('needs_review', $review['clinical_state']);
        $this->assertSame('ready_to_invoice', $review['billing_state']);
        $this->assertSame('no_invoice', $review['settlement_state']);
        $this->assertSame(1, $review['summary']['open_charge_lines']);
        $this->assertSame(150.0, $review['summary']['open_charge_amount']);
        $this->assertSame('ILS', $review['invoiceable_currency']);
        $this->assertFalse($review['has_mixed_invoiceable_currencies']);
    }

    public function testReviewBlocksCombinedInvoiceWhenOpenChargesUseDifferentCurrencies(): void
    {
        [$clinicId, $patientId, $appointmentId] = $this->createVisitFixture();
        $examinationId = $this->createExamination($clinicId, $patientId);
        $treatmentId = $this->createTreatment($clinicId, $patientId, $examinationId);
        $this->createChargeLine($clinicId, $patientId, $appointmentId, $examinationId, $treatmentId, '100.00', 'ILS');
        $this->createChargeLine($clinicId, $patientId, $appointmentId, $examinationId, null, '25.00', 'USD');

        $review = (new AppointmentCheckoutService($this->db))->review($clinicId, $appointmentId);

        $this->assertTrue($review['has_mixed_invoiceable_currencies']);
        $this->assertNull($review['invoiceable_currency']);
        $this->assertCount(2, $review['invoiceable_charge_line_ids']);
    }

    public function testExplicitAppointmentExaminationOverridesDateOnlyAssociationLabel(): void
    {
        [$clinicId, $patientId, $appointmentId] = $this->createVisitFixture();
        $examinationId = $this->createExamination($clinicId, $patientId, $appointmentId);
        $this->createExamination($clinicId, $patientId);

        $review = (new AppointmentCheckoutService($this->db))->review($clinicId, $appointmentId);

        $this->assertSame('explicit', $review['link_basis']);
        $this->assertSame([$examinationId], array_map('intval', array_column($review['examinations'], 'id')));
    }

    public function testReviewRejectsAppointmentOutsideActiveClinic(): void
    {
        [$clinicId, , $appointmentId] = $this->createVisitFixture();
        [$otherClinicId] = $this->createVisitFixture();
        $this->assertNotSame($clinicId, $otherClinicId);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Appointment not found in the active clinic.');

        (new AppointmentCheckoutService($this->db))->review($otherClinicId, $appointmentId);
    }

    private function createVisitFixture(): array
    {
        $token = $this->token();
        $now = date('Y-m-d H:i:s');
        $this->db->table('clinics')->insert([
            'name' => 'Checkout Clinic ' . $token,
            'slug' => 'checkout-' . strtolower($token),
            'uuid' => $this->uuid(),
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $clinicId = (int) $this->db->insertID();
        $this->clinicIds[] = $clinicId;

        $this->db->table('patients')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => 'CHK' . substr($token, -8),
            'first_name' => 'Checkout',
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

        $this->db->table('appointments')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'appointment_id' => 'APT' . substr($token, -8),
            'appointment_date' => '2026-07-30',
            'appointment_time' => '09:00:00',
            'duration' => 30,
            'appointment_type' => 'treatment',
            'chair_name' => 'Chair 1',
            'status' => 'checkout',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $appointmentId = (int) $this->db->insertID();
        $this->appointmentIds[] = $appointmentId;

        return [$clinicId, $patientId, $appointmentId];
    }

    private function createExamination(int $clinicId, int $patientId, ?int $appointmentId = null): int
    {
        $token = $this->token();
        $this->db->table('examinations')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'appointment_id' => $appointmentId,
            'examination_id' => 'EXC' . substr($token, -8),
            'examination_date' => '2026-07-30 09:05:00',
            'examination_type' => 'follow_up',
            'chief_complaint' => 'Checkout review fixture',
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $this->examinationIds[] = $id;
        return $id;
    }

    private function createTreatment(int $clinicId, int $patientId, int $examinationId): int
    {
        $token = $this->token();
        $this->db->table('treatments')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'examination_id' => $examinationId,
            'treatment_id' => 'TRC' . substr($token, -8),
            'treatment_name' => 'Checkout Treatment',
            'treatment_type' => 'cleaning',
            'status' => 'active',
            'start_date' => '2026-07-30',
            'estimated_duration' => 30,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $this->treatmentIds[] = $id;
        return $id;
    }

    private function createChargeLine(
        int $clinicId,
        int $patientId,
        int $appointmentId,
        int $examinationId,
        ?int $treatmentId,
        string $amount,
        string $currency
    ): int {
        $this->db->table('clinical_charge_lines')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'appointment_id' => $appointmentId,
            'examination_id' => $examinationId,
            'treatment_id' => $treatmentId,
            'source_entity_type' => $treatmentId ? 'treatment' : 'examination',
            'source_entity_id' => $treatmentId ?: $examinationId,
            'provider_type' => 'none',
            'procedure_name' => 'Checkout charge',
            'quantity' => '1.00',
            'unit_price' => $amount,
            'discount_amount' => '0.00',
            'tax_amount' => '0.00',
            'net_production_amount' => $amount,
            'currency' => $currency,
            'service_date' => '2026-07-30',
            'charge_date' => '2026-07-30',
            'status' => 'accepted',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $this->chargeLineIds[] = $id;
        return $id;
    }

    private function deleteRows(string $table, array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids !== [] && $this->db->tableExists($table)) {
            $this->db->table($table)->whereIn('id', $ids)->delete();
        }
    }

    private function token(): string
    {
        return str_replace('.', '', uniqid('checkout', true));
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
