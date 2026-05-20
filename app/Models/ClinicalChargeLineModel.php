<?php

namespace App\Models;

class ClinicalChargeLineModel extends TenantAwareModel
{
    protected $table = 'clinical_charge_lines';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'patient_id',
        'examination_id',
        'treatment_id',
        'appointment_id',
        'contract_id',
        'contract_item_id',
        'finance_id',
        'source_entity_type',
        'source_entity_id',
        'provider_type',
        'provider_user_id',
        'provider_name_snapshot',
        'procedure_code',
        'procedure_name',
        'tooth_numbers',
        'quantity',
        'unit_price',
        'gross_amount',
        'discount_amount',
        'adjustment_amount',
        'tax_amount',
        'net_production_amount',
        'currency',
        'service_date',
        'charge_date',
        'status',
        'notes',
        'created_by',
        'voided_by',
        'voided_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['setClinicId', 'normalizeChargeLine'];
    protected $beforeUpdate = ['normalizeChargeLine'];

    protected $validationRules = [
        'patient_id' => 'required|integer',
        'examination_id' => 'permit_empty|integer',
        'treatment_id' => 'permit_empty|integer',
        'appointment_id' => 'permit_empty|integer',
        'contract_id' => 'permit_empty|integer',
        'contract_item_id' => 'permit_empty|integer',
        'finance_id' => 'permit_empty|integer',
        'source_entity_type' => 'required|max_length[50]',
        'source_entity_id' => 'permit_empty|integer',
        'provider_type' => 'permit_empty|in_list[user,guest,none]',
        'provider_user_id' => 'permit_empty|integer',
        'procedure_code' => 'permit_empty|max_length[80]',
        'procedure_name' => 'required|max_length[255]',
        'tooth_numbers' => 'permit_empty|max_length[120]',
        'quantity' => 'required|decimal|greater_than[0]',
        'unit_price' => 'required|decimal|greater_than_equal_to[0]',
        'gross_amount' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'discount_amount' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'adjustment_amount' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'tax_amount' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'net_production_amount' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'currency' => 'required|in_list[ILS,USD,JOD,EUR,GBP]',
        'service_date' => 'permit_empty|valid_date',
        'charge_date' => 'required|valid_date',
        'status' => 'required|in_list[planned,accepted,completed,invoiced,voided]',
    ];

    public function getRowsByPatient(int $clinicId, int $patientId, int $limit = 100): array
    {
        return $this->baseDirectoryQuery($clinicId)
            ->where('clinical_charge_lines.patient_id', $patientId)
            ->orderBy('clinical_charge_lines.charge_date', 'DESC')
            ->orderBy('clinical_charge_lines.id', 'DESC')
            ->limit(max(1, min($limit, 250)))
            ->findAll();
    }

    public function getRowsByExamination(int $clinicId, int $examinationId, int $limit = 100): array
    {
        return $this->baseDirectoryQuery($clinicId)
            ->where('clinical_charge_lines.examination_id', $examinationId)
            ->orderBy('clinical_charge_lines.charge_date', 'DESC')
            ->orderBy('clinical_charge_lines.id', 'DESC')
            ->limit(max(1, min($limit, 250)))
            ->findAll();
    }

    public function getRowsByTreatment(int $clinicId, int $treatmentId, int $limit = 100): array
    {
        return $this->baseDirectoryQuery($clinicId)
            ->where('clinical_charge_lines.treatment_id', $treatmentId)
            ->orderBy('clinical_charge_lines.charge_date', 'DESC')
            ->orderBy('clinical_charge_lines.id', 'DESC')
            ->limit(max(1, min($limit, 250)))
            ->findAll();
    }

    public function summarize(array $rows): array
    {
        $summary = [
            'planned' => 0.0,
            'accepted' => 0.0,
            'completed' => 0.0,
            'invoiced' => 0.0,
            'voided' => 0.0,
            'open_net_production' => 0.0,
        ];

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? 'planned');
            $amount = max(0, (float) ($row['net_production_amount'] ?? 0));
            if (array_key_exists($status, $summary)) {
                $summary[$status] += $amount;
            }
            if (!in_array($status, ['invoiced', 'voided'], true)) {
                $summary['open_net_production'] += $amount;
            }
        }

        return $summary;
    }

    protected function normalizeChargeLine(array $data): array
    {
        if (!isset($data['data']) || !is_array($data['data'])) {
            return $data;
        }

        $row = &$data['data'];
        $quantity = max(0.01, (float) ($row['quantity'] ?? 1));
        $unitPrice = max(0, (float) ($row['unit_price'] ?? 0));
        $gross = array_key_exists('gross_amount', $row)
            ? max(0, (float) $row['gross_amount'])
            : round($quantity * $unitPrice, 2);
        $discount = max(0, (float) ($row['discount_amount'] ?? 0));
        $adjustment = max(0, (float) ($row['adjustment_amount'] ?? 0));
        $tax = max(0, (float) ($row['tax_amount'] ?? 0));
        $net = max(0, $gross - $discount - $adjustment + $tax);

        $row['quantity'] = number_format($quantity, 2, '.', '');
        $row['unit_price'] = number_format($unitPrice, 2, '.', '');
        $row['gross_amount'] = number_format($gross, 2, '.', '');
        $row['discount_amount'] = number_format($discount, 2, '.', '');
        $row['adjustment_amount'] = number_format($adjustment, 2, '.', '');
        $row['tax_amount'] = number_format($tax, 2, '.', '');
        $row['net_production_amount'] = number_format($net, 2, '.', '');

        if (empty($row['charge_date'])) {
            $row['charge_date'] = date('Y-m-d');
        }
        if (empty($row['source_entity_type'])) {
            $row['source_entity_type'] = 'manual';
        }
        if (empty($row['provider_type'])) {
            $row['provider_type'] = 'none';
        }
        if (($row['provider_type'] ?? 'none') !== 'user') {
            $row['provider_user_id'] = null;
        }
        if (empty($row['status'])) {
            $row['status'] = 'planned';
        }

        return $data;
    }

    private function baseDirectoryQuery(int $clinicId): self
    {
        return $this->select('clinical_charge_lines.*, patients.patient_id AS patient_number, patients.first_name, patients.last_name, examinations.examination_id AS examination_number, treatments.treatment_id AS treatment_number, treatments.treatment_name, finances.transaction_id')
            ->join('patients', 'patients.id = clinical_charge_lines.patient_id AND patients.clinic_id = clinical_charge_lines.clinic_id', 'left')
            ->join('examinations', 'examinations.id = clinical_charge_lines.examination_id AND examinations.clinic_id = clinical_charge_lines.clinic_id', 'left')
            ->join('treatments', 'treatments.id = clinical_charge_lines.treatment_id AND treatments.clinic_id = clinical_charge_lines.clinic_id', 'left')
            ->join('finances', 'finances.id = clinical_charge_lines.finance_id AND finances.clinic_id = clinical_charge_lines.clinic_id', 'left')
            ->where('clinical_charge_lines.clinic_id', $clinicId);
    }
}
