<?php

namespace Tests\Unit;

use App\Services\ClinicalChargeLineService;
use App\Services\MoneyTrailService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class ClinicalFinanceBridgeTest extends CIUnitTestCase
{
    protected $db;
    private array $clinicIds = [];
    private array $patientIds = [];
    private array $treatmentIds = [];
    private array $financeIds = [];
    private array $paymentIds = [];
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
            $this->deleteRows('finance_payments', $this->paymentIds);
            $this->deleteRows('finances', $this->financeIds);
            $this->deleteRows('treatments', $this->treatmentIds);
            $this->deleteRows('patients', $this->patientIds);
            $this->deleteRows('clinics', $this->clinicIds);
        }

        session()->remove('active_clinic_id');
        parent::tearDown();
    }

    public function testTreatmentChargeCaptureCreatesNormalizedTenantScopedLine(): void
    {
        [$clinicId, $patientId, $treatmentId] = $this->createTreatmentFixture();
        $service = new ClinicalChargeLineService();

        $result = $service->syncTreatmentChargeLine($clinicId, $this->treatment($treatmentId), [
            'charge_enabled' => '1',
            'charge_provider_type' => 'guest',
            'charge_provider_name_snapshot' => 'Outside Doctor',
            'charge_procedure_name' => 'Root canal charge',
            'charge_quantity' => '2',
            'charge_unit_price' => '200',
            'charge_discount_amount' => '25',
            'charge_tax_amount' => '5',
            'charge_currency' => 'ILS',
            'charge_status' => 'accepted',
            'charge_notes' => 'Bridge test',
        ], 321);

        $this->assertTrue($result['success']);
        $line = $result['line'];
        $this->chargeLineIds[] = (int) $line['id'];

        $this->assertSame($clinicId, (int) $line['clinic_id']);
        $this->assertSame($patientId, (int) $line['patient_id']);
        $this->assertSame($treatmentId, (int) $line['treatment_id']);
        $this->assertSame('treatment', $line['source_entity_type']);
        $this->assertSame($treatmentId, (int) $line['source_entity_id']);
        $this->assertSame('guest', $line['provider_type']);
        $this->assertSame('Outside Doctor', $line['provider_name_snapshot']);
        $this->assertSame('Root canal charge', $line['procedure_name']);
        $this->assertSame('2.00', $line['quantity']);
        $this->assertSame('200.00', $line['unit_price']);
        $this->assertSame('400.00', $line['gross_amount']);
        $this->assertSame('25.00', $line['discount_amount']);
        $this->assertSame('5.00', $line['tax_amount']);
        $this->assertSame('380.00', $line['net_production_amount']);
        $this->assertSame('accepted', $line['status']);
        $this->assertSame(321, (int) $line['created_by']);
    }

    public function testTreatmentChargeDisableVoidsOpenLineButInvoicedLineCannotBeEdited(): void
    {
        [$clinicId, , $treatmentId] = $this->createTreatmentFixture();
        $service = new ClinicalChargeLineService();
        $line = $this->captureTreatmentCharge($service, $clinicId, $treatmentId, 150.00);

        $disabled = $service->syncTreatmentChargeLine($clinicId, $this->treatment($treatmentId), [
            'charge_enabled' => '0',
        ], 654);

        $this->assertTrue($disabled['success']);
        $voided = $this->db->table('clinical_charge_lines')->where('id', $line['id'])->get()->getRowArray();
        $this->assertSame('voided', $voided['status']);
        $this->assertSame(654, (int) $voided['voided_by']);

        $secondLine = $this->captureTreatmentCharge($service, $clinicId, $treatmentId, 175.00);
        $financeId = $this->createInvoice($clinicId, (int) $secondLine['patient_id'], 175.00);
        $this->assertTrue($service->markInvoiced($clinicId, [(int) $secondLine['id']], $financeId));

        $blocked = $service->syncTreatmentChargeLine($clinicId, $this->treatment($treatmentId), [
            'charge_enabled' => '1',
            'charge_unit_price' => '225',
            'charge_currency' => 'ILS',
        ], 654);

        $this->assertFalse($blocked['success']);
        $this->assertStringContainsString('already invoiced', $blocked['message']);
        $unchanged = $this->db->table('clinical_charge_lines')->where('id', $secondLine['id'])->get()->getRowArray();
        $this->assertSame('175.00', $unchanged['net_production_amount']);
        $this->assertSame($financeId, (int) $unchanged['finance_id']);
    }

    public function testInvoiceSelectionAcceptsOnlyAvailableSamePatientSameCurrencyChargeLines(): void
    {
        [$clinicId, $patientId, $treatmentId] = $this->createTreatmentFixture();
        $secondTreatmentId = $this->createTreatmentForPatient($clinicId, $patientId);
        [, $otherPatientId, $otherTreatmentId] = $this->createTreatmentFixture($clinicId);
        $service = new ClinicalChargeLineService();

        $lineA = $this->captureTreatmentCharge($service, $clinicId, $treatmentId, 100.00);
        $lineB = $this->captureTreatmentCharge($service, $clinicId, $secondTreatmentId, 50.00);
        $otherPatientLine = $this->captureTreatmentCharge($service, $clinicId, $otherTreatmentId, 75.00);

        $selection = $service->prepareInvoiceSelection($clinicId, $patientId, [(int) $lineA['id'], (int) $lineB['id']]);

        $this->assertTrue($selection['success']);
        $this->assertSame(150.0, $selection['total']);
        $this->assertSame('ILS', $selection['currency']);
        $this->assertCount(2, $selection['rows']);
        $this->assertStringContainsString('Bridge charge', $selection['service_details']);

        $crossPatient = $service->prepareInvoiceSelection($clinicId, $patientId, [(int) $lineA['id'], (int) $otherPatientLine['id']]);
        $this->assertFalse($crossPatient['success']);
        $this->assertStringContainsString('no longer available', $crossPatient['message']);

        $financeId = $this->createInvoice($clinicId, $patientId, 100.00);
        $this->assertTrue($service->markInvoiced($clinicId, [(int) $lineA['id']], $financeId));

        $alreadyInvoiced = $service->prepareInvoiceSelection($clinicId, $patientId, [(int) $lineA['id']]);
        $this->assertFalse($alreadyInvoiced['success']);
        $this->assertStringContainsString('no longer available', $alreadyInvoiced['message']);

        $this->assertSame($otherPatientId, (int) $this->db->table('clinical_charge_lines')->where('id', $otherPatientLine['id'])->get()->getRowArray()['patient_id']);
    }

    public function testBilledChargeLinesRemainTraceableFromInvoiceAndTreatmentMoneyTrail(): void
    {
        [$clinicId, $patientId, $treatmentId] = $this->createTreatmentFixture();
        $service = new ClinicalChargeLineService();
        $line = $this->captureTreatmentCharge($service, $clinicId, $treatmentId, 240.00);
        $financeId = $this->createInvoice($clinicId, $patientId, 240.00, 'INV-BRIDGE-TRACE');
        $this->createPayment($clinicId, $financeId, 100.00);

        $this->assertTrue($service->markInvoiced($clinicId, [(int) $line['id']], $financeId));

        $billedRows = $service->getRowsForFinance($clinicId, $financeId);
        $this->assertCount(1, $billedRows);
        $this->assertSame((int) $line['id'], (int) $billedRows[0]['id']);
        $this->assertSame('INV-BRIDGE-TRACE', $this->db->table('finances')->where('id', $financeId)->get()->getRowArray()['transaction_id']);
        $this->assertSame('Bridge Treatment', $billedRows[0]['treatment_name']);

        $trail = (new MoneyTrailService($this->db))->forTreatment($clinicId, $treatmentId);

        $this->assertTrue($trail['has_records']);
        $this->assertSame(240.0, $trail['summary']['captured_charges']);
        $this->assertSame(240.0, $trail['summary']['charged']);
        $this->assertSame(100.0, $trail['summary']['paid']);
        $this->assertSame(140.0, $trail['summary']['balance_due']);
        $this->assertSame(0, $trail['summary']['open_charge_lines']);
        $this->assertCount(1, $trail['charge_lines']);
        $this->assertCount(1, $trail['finance_rows']);
        $this->assertCount(1, $trail['payment_rows']);
        $this->assertSame('invoiced', $trail['charge_lines'][0]['status']);
        $this->assertSame('INV-BRIDGE-TRACE', $trail['charge_lines'][0]['transaction_id']);
        $this->assertSame('INV-BRIDGE-TRACE', $trail['finance_rows'][0]['transaction_id']);
        $this->assertSame('100.00', $trail['payment_rows'][0]['amount']);
    }

    private function createTreatmentFixture(?int $clinicId = null): array
    {
        if ($clinicId === null) {
            $token = $this->token();
            $this->db->table('clinics')->insert([
                'name' => 'Bridge Clinic ' . $token,
                'slug' => 'bridge-' . strtolower($token),
                'uuid' => $this->uuid(),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $clinicId = (int) $this->db->insertID();
            $this->clinicIds[] = $clinicId;
        }

        session()->set('active_clinic_id', $clinicId);

        $token = $this->token();
        $this->db->table('patients')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => 'PBR' . substr($token, -8),
            'first_name' => 'Bridge',
            'last_name' => 'Patient',
            'phone' => (string) random_int(100000000, 999999999),
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $patientId = (int) $this->db->insertID();
        $this->patientIds[] = $patientId;

        $treatmentId = $this->createTreatmentForPatient($clinicId, $patientId, $token);

        return [$clinicId, $patientId, $treatmentId];
    }

    private function createTreatmentForPatient(int $clinicId, int $patientId, ?string $token = null): int
    {
        $token ??= $this->token();

        $this->db->table('treatments')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'treatment_id' => 'TBR' . substr($token, -8),
            'treatment_name' => 'Bridge Treatment',
            'treatment_type' => 'cleaning',
            'tooth_number' => '36',
            'treatment_description' => 'Bridge treatment description',
            'status' => 'active',
            'start_date' => date('Y-m-d'),
            'estimated_duration' => 30,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $treatmentId = (int) $this->db->insertID();
        $this->treatmentIds[] = $treatmentId;

        return $treatmentId;
    }

    private function captureTreatmentCharge(ClinicalChargeLineService $service, int $clinicId, int $treatmentId, float $amount): array
    {
        $result = $service->syncTreatmentChargeLine($clinicId, $this->treatment($treatmentId), [
            'charge_enabled' => '1',
            'charge_provider_type' => 'none',
            'charge_procedure_name' => 'Bridge charge',
            'charge_quantity' => '1',
            'charge_unit_price' => (string) $amount,
            'charge_discount_amount' => '0',
            'charge_tax_amount' => '0',
            'charge_currency' => 'ILS',
            'charge_status' => 'completed',
        ], 123);

        $this->assertTrue($result['success'], $result['message'] ?? 'Charge capture failed');
        $this->chargeLineIds[] = (int) $result['line']['id'];

        return $result['line'];
    }

    private function createInvoice(int $clinicId, int $patientId, float $amount, ?string $transactionId = null): int
    {
        $transactionId ??= 'INV-BRIDGE-' . substr($this->token(), -8);
        $this->db->table('finances')->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'transaction_id' => $transactionId,
            'transaction_type' => 'invoice',
            'amount' => number_format($amount, 2, '.', ''),
            'amount_original' => number_format($amount, 2, '.', ''),
            'exchange_rate' => '1.000000',
            'currency' => 'ILS',
            'payment_method' => 'cash',
            'liquidity_account' => 'safe_cash',
            'payment_status' => 'pending',
            'description' => 'Bridge invoice',
            'service_type' => 'treatment',
            'service_details' => 'Bridge charge',
            'discount_amount' => '0.00',
            'tax_percentage' => '0.00',
            'tax_amount' => '0.00',
            'total_amount' => number_format($amount, 2, '.', ''),
            'amount_paid' => '0.00',
            'balance_due' => number_format($amount, 2, '.', ''),
            'transaction_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $financeId = (int) $this->db->insertID();
        $this->financeIds[] = $financeId;

        return $financeId;
    }

    private function createPayment(int $clinicId, int $financeId, float $amount): int
    {
        $this->db->table('finance_payments')->insert([
            'clinic_id' => $clinicId,
            'finance_id' => $financeId,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => 'ILS',
            'amount_original' => number_format($amount, 2, '.', ''),
            'exchange_rate' => '1.000000',
            'type' => 'payment',
            'payment_method' => 'cash',
            'liquidity_account' => 'safe_cash',
            'payment_date' => date('Y-m-d'),
            'notes' => 'Bridge payment',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $paymentId = (int) $this->db->insertID();
        $this->paymentIds[] = $paymentId;

        $finance = $this->db->table('finances')->where('id', $financeId)->get()->getRowArray();
        $total = (float) ($finance['total_amount'] ?? $finance['amount'] ?? 0);
        $paid = round((float) ($finance['amount_paid'] ?? 0) + $amount, 2);
        $balance = round(max(0, $total - $paid), 2);
        $this->db->table('finances')
            ->where('id', $financeId)
            ->where('clinic_id', $clinicId)
            ->update([
                'amount_paid' => number_format($paid, 2, '.', ''),
                'balance_due' => number_format($balance, 2, '.', ''),
                'payment_status' => $balance <= 0 ? 'paid' : 'partial',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $paymentId;
    }

    private function treatment(int $treatmentId): array
    {
        return $this->db->table('treatments')->where('id', $treatmentId)->get()->getRowArray();
    }

    private function deleteRows(string $table, array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return;
        }

        $this->db->table($table)->whereIn('id', $ids)->delete();
    }

    private function token(): string
    {
        return str_replace('.', '', uniqid('t', true));
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
