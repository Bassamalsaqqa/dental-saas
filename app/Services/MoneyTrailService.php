<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

class MoneyTrailService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function forPatient(int $clinicId, int $patientId): array
    {
        $financeRows = $this->financeRows($clinicId, ['f.patient_id' => $patientId]);
        $financeIds = $this->ids($financeRows);

        return $this->buildTrail(
            $this->chargeLineRows($clinicId, ['ccl.patient_id' => $patientId]),
            $financeRows,
            $this->paymentRows($clinicId, $financeIds),
            $this->providerPayoutRows($clinicId, ['pp.patient_id' => $patientId], $financeIds),
            $this->inventoryUsageRowsForPatient($clinicId, $patientId),
            'patient'
        );
    }

    public function forExamination(int $clinicId, int $examinationId): array
    {
        $financeRows = $this->financeRows($clinicId, ['f.examination_id' => $examinationId]);
        $financeIds = $this->ids($financeRows);

        return $this->buildTrail(
            $this->chargeLineRows($clinicId, ['ccl.examination_id' => $examinationId]),
            $financeRows,
            $this->paymentRows($clinicId, $financeIds),
            $this->providerPayoutRows($clinicId, [], $financeIds),
            $this->inventoryUsageRowsForExamination($clinicId, $examinationId),
            'examination'
        );
    }

    public function forTreatment(int $clinicId, int $treatmentId): array
    {
        $treatment = $this->db->table('treatments')
            ->where('clinic_id', $clinicId)
            ->where('id', $treatmentId)
            ->get()
            ->getRowArray();

        $financeRows = [];
        $chargeLineRows = $this->chargeLineRows($clinicId, ['ccl.treatment_id' => $treatmentId]);

        if (!empty($treatment['examination_id'])) {
            $financeRows = $this->financeRows($clinicId, ['f.examination_id' => (int) $treatment['examination_id']]);
            foreach ($financeRows as &$row) {
                $row['context_note'] = 'examination';
            }
            unset($row);
        }

        $chargeLineFinanceIds = [];
        foreach ($chargeLineRows as $row) {
            $financeId = (int) ($row['finance_id'] ?? 0);
            if ($financeId > 0) {
                $chargeLineFinanceIds[] = $financeId;
            }
        }

        foreach ($this->financeRowsByIds($clinicId, $chargeLineFinanceIds) as $row) {
            $row['context_note'] = 'clinical_charge_line';
            $financeRows[] = $row;
        }

        $financeRows = $this->uniqueRowsById($financeRows);

        $financeIds = $this->ids($financeRows);

        return $this->buildTrail(
            $chargeLineRows,
            $financeRows,
            $this->paymentRows($clinicId, $financeIds),
            $this->providerPayoutRows($clinicId, [], $financeIds),
            $this->inventoryUsageRowsForTreatment($clinicId, $treatmentId),
            'treatment'
        );
    }

    public function forChargeLines(int $clinicId, array $chargeLineIds, string $context = 'clinical_context'): array
    {
        $chargeLineIds = array_values(array_unique(array_filter(array_map('intval', $chargeLineIds))));
        if ($clinicId <= 0 || $chargeLineIds === []) {
            return $this->buildTrail([], [], [], [], [], $context);
        }

        $chargeLineRows = $this->chargeLineRowsByIds($clinicId, $chargeLineIds);
        $financeIds = array_values(array_unique(array_filter(array_map(
            static fn(array $row): int => (int) ($row['finance_id'] ?? 0),
            $chargeLineRows
        ))));
        $treatmentIds = array_values(array_unique(array_filter(array_map(
            static fn(array $row): int => (int) ($row['treatment_id'] ?? 0),
            $chargeLineRows
        ))));
        $financeRows = $this->financeRowsByIds($clinicId, $financeIds);

        return $this->buildTrail(
            $chargeLineRows,
            $financeRows,
            $this->paymentRows($clinicId, $financeIds),
            $this->providerPayoutRows($clinicId, [], $financeIds),
            $this->inventoryUsageRowsForTreatments($clinicId, $treatmentIds),
            $context
        );
    }

    private function financeRows(int $clinicId, array $where): array
    {
        $builder = $this->db->table('finances f')
            ->select('f.*, p.patient_id AS patient_number, p.first_name, p.last_name, e.examination_id AS examination_number')
            ->join('patients p', 'p.id = f.patient_id AND p.clinic_id = f.clinic_id', 'left')
            ->join('examinations e', 'e.id = f.examination_id AND e.clinic_id = f.clinic_id', 'left')
            ->where('f.clinic_id', $clinicId);

        foreach ($where as $field => $value) {
            $builder->where($field, $value);
        }

        return $builder
            ->orderBy('COALESCE(f.transaction_date, f.paid_date, DATE(f.created_at))', 'DESC', false)
            ->orderBy('f.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function financeRowsByIds(int $clinicId, array $financeIds): array
    {
        $financeIds = array_values(array_unique(array_filter(array_map('intval', $financeIds))));
        if ($financeIds === []) {
            return [];
        }

        return $this->db->table('finances f')
            ->select('f.*, p.patient_id AS patient_number, p.first_name, p.last_name, e.examination_id AS examination_number')
            ->join('patients p', 'p.id = f.patient_id AND p.clinic_id = f.clinic_id', 'left')
            ->join('examinations e', 'e.id = f.examination_id AND e.clinic_id = f.clinic_id', 'left')
            ->where('f.clinic_id', $clinicId)
            ->whereIn('f.id', $financeIds)
            ->orderBy('COALESCE(f.transaction_date, f.paid_date, DATE(f.created_at))', 'DESC', false)
            ->orderBy('f.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function chargeLineRows(int $clinicId, array $where): array
    {
        if (!$this->db->tableExists('clinical_charge_lines')) {
            return [];
        }

        $builder = $this->db->table('clinical_charge_lines ccl')
            ->select('ccl.*, p.patient_id AS patient_number, p.first_name, p.last_name, e.examination_id AS examination_number, t.treatment_id AS treatment_number, t.treatment_name, f.transaction_id')
            ->join('patients p', 'p.id = ccl.patient_id AND p.clinic_id = ccl.clinic_id', 'left')
            ->join('examinations e', 'e.id = ccl.examination_id AND e.clinic_id = ccl.clinic_id', 'left')
            ->join('treatments t', 't.id = ccl.treatment_id AND t.clinic_id = ccl.clinic_id', 'left')
            ->join('finances f', 'f.id = ccl.finance_id AND f.clinic_id = ccl.clinic_id', 'left')
            ->where('ccl.clinic_id', $clinicId);

        foreach ($where as $field => $value) {
            $builder->where($field, $value);
        }

        return $builder
            ->orderBy('ccl.charge_date', 'DESC')
            ->orderBy('ccl.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function chargeLineRowsByIds(int $clinicId, array $chargeLineIds): array
    {
        if (!$this->db->tableExists('clinical_charge_lines') || $chargeLineIds === []) {
            return [];
        }

        return $this->db->table('clinical_charge_lines ccl')
            ->select('ccl.*, p.patient_id AS patient_number, p.first_name, p.last_name, e.examination_id AS examination_number, t.treatment_id AS treatment_number, t.treatment_name, f.transaction_id')
            ->join('patients p', 'p.id = ccl.patient_id AND p.clinic_id = ccl.clinic_id', 'left')
            ->join('examinations e', 'e.id = ccl.examination_id AND e.clinic_id = ccl.clinic_id', 'left')
            ->join('treatments t', 't.id = ccl.treatment_id AND t.clinic_id = ccl.clinic_id', 'left')
            ->join('finances f', 'f.id = ccl.finance_id AND f.clinic_id = ccl.clinic_id', 'left')
            ->where('ccl.clinic_id', $clinicId)
            ->whereIn('ccl.id', $chargeLineIds)
            ->orderBy('ccl.charge_date', 'DESC')
            ->orderBy('ccl.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function paymentRows(int $clinicId, array $financeIds): array
    {
        if ($financeIds === []) {
            return [];
        }

        return $this->db->table('finance_payments fp')
            ->select('fp.*, f.transaction_id, f.transaction_type')
            ->join('finances f', 'f.id = fp.finance_id AND f.clinic_id = fp.clinic_id', 'inner')
            ->where('fp.clinic_id', $clinicId)
            ->whereIn('fp.finance_id', $financeIds)
            ->orderBy('fp.payment_date', 'DESC')
            ->orderBy('fp.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function providerPayoutRows(int $clinicId, array $where, array $financeIds = []): array
    {
        $builder = $this->db->table('provider_payouts pp')
            ->select('pp.*, f.transaction_id, f.transaction_type, u.first_name AS provider_first_name, u.last_name AS provider_last_name')
            ->join('finances f', 'f.id = pp.finance_id AND f.clinic_id = pp.clinic_id', 'left')
            ->join('users u', 'u.id = pp.provider_user_id', 'left')
            ->where('pp.clinic_id', $clinicId);

        foreach ($where as $field => $value) {
            $builder->where($field, $value);
        }

        if ($financeIds !== []) {
            $builder->whereIn('pp.finance_id', $financeIds);
        } elseif ($where === []) {
            return [];
        }

        return $builder
            ->orderBy('pp.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function inventoryUsageRowsForPatient(int $clinicId, int $patientId): array
    {
        return $this->db->table('inventory_usage iu')
            ->select('iu.*, t.treatment_id AS treatment_number, t.treatment_name, t.examination_id, e.examination_id AS examination_number')
            ->join('treatments t', 't.id = iu.treatment_id AND t.clinic_id = iu.clinic_id', 'inner')
            ->join('examinations e', 'e.id = t.examination_id AND e.clinic_id = t.clinic_id', 'left')
            ->where('iu.clinic_id', $clinicId)
            ->where('t.patient_id', $patientId)
            ->orderBy('iu.usage_date', 'DESC')
            ->orderBy('iu.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function inventoryUsageRowsForExamination(int $clinicId, int $examinationId): array
    {
        return $this->db->table('inventory_usage iu')
            ->select('iu.*, t.treatment_id AS treatment_number, t.treatment_name, t.examination_id')
            ->join('treatments t', 't.id = iu.treatment_id AND t.clinic_id = iu.clinic_id', 'inner')
            ->where('iu.clinic_id', $clinicId)
            ->where('t.examination_id', $examinationId)
            ->orderBy('iu.usage_date', 'DESC')
            ->orderBy('iu.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function inventoryUsageRowsForTreatment(int $clinicId, int $treatmentId): array
    {
        return $this->db->table('inventory_usage iu')
            ->select('iu.*, t.treatment_id AS treatment_number, t.treatment_name, t.examination_id')
            ->join('treatments t', 't.id = iu.treatment_id AND t.clinic_id = iu.clinic_id', 'left')
            ->where('iu.clinic_id', $clinicId)
            ->where('iu.treatment_id', $treatmentId)
            ->orderBy('iu.usage_date', 'DESC')
            ->orderBy('iu.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function inventoryUsageRowsForTreatments(int $clinicId, array $treatmentIds): array
    {
        if ($treatmentIds === []) {
            return [];
        }

        return $this->db->table('inventory_usage iu')
            ->select('iu.*, t.treatment_id AS treatment_number, t.treatment_name, t.examination_id')
            ->join('treatments t', 't.id = iu.treatment_id AND t.clinic_id = iu.clinic_id', 'inner')
            ->where('iu.clinic_id', $clinicId)
            ->whereIn('iu.treatment_id', $treatmentIds)
            ->orderBy('iu.usage_date', 'DESC')
            ->orderBy('iu.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function buildTrail(array $chargeLineRows, array $financeRows, array $paymentRows, array $payoutRows, array $inventoryUsageRows, string $context): array
    {
        $summary = [
            'captured_charges' => 0.0,
            'open_charge_lines' => 0,
            'open_charge_amount' => 0.0,
            'charged' => 0.0,
            'paid' => 0.0,
            'refunded' => 0.0,
            'balance_due' => 0.0,
            'provider_share' => 0.0,
            'provider_paid' => 0.0,
            'provider_remaining' => 0.0,
            'inventory_cost' => 0.0,
        ];

        foreach ($chargeLineRows as $row) {
            $status = strtolower((string) ($row['status'] ?? 'planned'));
            if ($status !== 'voided') {
                $summary['captured_charges'] += max(0, (float) ($row['net_production_amount'] ?? 0));
            }
            if (!in_array($status, ['invoiced', 'voided'], true)) {
                $summary['open_charge_lines']++;
                $summary['open_charge_amount'] += max(0, (float) ($row['net_production_amount'] ?? 0));
            }
        }

        // Display current receivables and credits without altering original charge
        // values, payment history, or provider obligations.
        if ($financeRows !== []) {
            $clinicId = (int) ($financeRows[0]['clinic_id'] ?? 0);
            $model = new \App\Models\FinanceModel($this->db);
            $financeRows = $model->withInvoiceCorrectionBalances($financeRows, $clinicId);
        }
        $summary['credited'] = 0.0;
        $summary['patient_credit'] = 0.0;
        $summary['credit_applied_in'] = 0.0;
        $summary['credit_applied_out'] = 0.0;
        foreach ($financeRows as $row) {
            $type = strtolower((string) ($row['transaction_type'] ?? ''));
            $amount = $this->financeTotal($row);
            if ($type === 'invoice') {
                $summary['charged'] += $amount;
                $summary['credited'] += (float) ($row['correction_position']['credited_base'] ?? 0);
                $summary['patient_credit'] += (float) ($row['correction_position']['patient_credit_base'] ?? 0);
                $summary['credit_applied_in'] += (float) ($row['correction_position']['incoming_base'] ?? 0);
                $summary['credit_applied_out'] += (float) ($row['correction_position']['outgoing_base'] ?? 0);
                $summary['balance_due'] += max(0, (float) ($row['balance_due'] ?? 0));
            } elseif (in_array($type, ['payment', 'adjustment'], true)) {
                $summary['paid'] += $amount;
            } elseif ($type === 'refund') {
                $summary['refunded'] += $amount;
            }
        }

        foreach ($paymentRows as $row) {
            $type = strtolower((string) ($row['type'] ?? 'payment'));
            $amount = max(0, (float) ($row['amount'] ?? 0));
            if (in_array($type, ['payment', 'adjustment'], true)) {
                $summary['paid'] += $amount;
            } elseif (in_array($type, ['refund', 'void'], true)) {
                $summary['refunded'] += $amount;
            }
        }

        $summary['net_charged'] = round($summary['charged'] - $summary['credited'], 2);
        $providerSummary = ProviderCurrencySummary::summarize($payoutRows);
        foreach (['provider_share', 'provider_paid', 'provider_remaining'] as $key) { $summary[$key] = $providerSummary[$key]; }
        $summary['provider_currency_summary'] = $providerSummary;

        foreach ($inventoryUsageRows as $row) {
            $summary['inventory_cost'] += max(0, (float) ($row['total_cost'] ?? 0));
        }

        return [
            'context' => $context,
            'summary' => $summary,
            'charge_lines' => $chargeLineRows,
            'finance_rows' => $financeRows,
            'payment_rows' => $paymentRows,
            'provider_payouts' => $payoutRows,
            'inventory_usage_rows' => $inventoryUsageRows,
            'has_records' => $chargeLineRows !== [] || $financeRows !== [] || $paymentRows !== [] || $payoutRows !== [] || $inventoryUsageRows !== [],
        ];
    }

    private function ids(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function uniqueRowsById(array $rows): array
    {
        $unique = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $unique[$id] = $row;
        }

        return array_values($unique);
    }

    private function financeTotal(array $row): float
    {
        if (isset($row['total_amount'])) {
            return max(0, (float) $row['total_amount']);
        }

        return max(0, (float) ($row['amount'] ?? 0) - (float) ($row['discount_amount'] ?? 0) + (float) ($row['tax_amount'] ?? 0));
    }
}
