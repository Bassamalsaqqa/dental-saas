<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;

final class AppointmentCheckoutService
{
    public function __construct(
        private ?BaseConnection $db = null,
        private ?MoneyTrailService $moneyTrail = null
    ) {
        $this->db ??= Database::connect();
        $this->moneyTrail ??= new MoneyTrailService($this->db);
    }

    public function review(int $clinicId, int $appointmentId): array
    {
        if ($clinicId <= 0 || $appointmentId <= 0) {
            throw new DomainException('Clinic and appointment are required.');
        }

        $appointment = $this->db->table('appointments')
            ->where('clinic_id', $clinicId)
            ->where('id', $appointmentId)
            ->get()
            ->getRowArray();
        if (!$appointment) {
            throw new DomainException('Appointment not found in the active clinic.');
        }

        $patientId = (int) ($appointment['patient_id'] ?? 0);
        $appointmentDate = substr((string) ($appointment['appointment_date'] ?? ''), 0, 10);
        if ($patientId <= 0 || $appointmentDate === '') {
            throw new DomainException('Appointment patient and date are required for checkout review.');
        }

        $examinationCandidates = $this->db->table('examinations e')
            ->select('e.*, u.first_name AS doctor_first_name, u.last_name AS doctor_last_name')
            ->join('users u', 'u.id = e.created_by', 'left')
            ->where('e.clinic_id', $clinicId)
            ->where('e.patient_id', $patientId)
            ->groupStart()
            ->where('e.appointment_id', $appointmentId)
            ->orWhere('DATE(e.examination_date)', $appointmentDate)
            ->groupEnd()
            ->orderBy('e.examination_date', 'DESC')
            ->orderBy('e.id', 'DESC')
            ->get()
            ->getResultArray();

        $hasProcedureTable = $this->db->tableExists('clinical_procedures');
        $explicitProcedures = $hasProcedureTable
            ? $this->db->table('clinical_procedures')
                ->where('clinic_id', $clinicId)
                ->where('patient_id', $patientId)
                ->where('appointment_id', $appointmentId)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray()
            : [];
        $explicitProcedureExaminationIds = array_values(array_unique(array_filter(array_map(
            static fn(array $row): int => (int) ($row['examination_id'] ?? 0),
            $explicitProcedures
        ))));
        $hasExplicitLink = $explicitProcedures !== [] || array_filter(
            $examinationCandidates,
            static fn(array $row): bool => (int) ($row['appointment_id'] ?? 0) === $appointmentId
        ) !== [];
        $examinations = $hasExplicitLink
            ? array_values(array_filter(
                $examinationCandidates,
                static fn(array $row): bool => (int) ($row['appointment_id'] ?? 0) === $appointmentId
                    || in_array((int) ($row['id'] ?? 0), $explicitProcedureExaminationIds, true)
            ))
            : $examinationCandidates;
        $examinationIds = $this->ids($examinations);
        $explicitTreatmentIds = array_values(array_unique(array_filter(array_map(
            static fn(array $row): int => (int) ($row['treatment_id'] ?? 0),
            $explicitProcedures
        ))));

        $treatmentBuilder = $this->db->table('treatments')
            ->where('clinic_id', $clinicId)
            ->where('patient_id', $patientId);
        if ($examinationIds !== [] || $explicitTreatmentIds !== []) {
            $treatmentBuilder->groupStart();
            if ($examinationIds !== []) {
                $treatmentBuilder->whereIn('examination_id', $examinationIds);
            }
            if ($explicitTreatmentIds !== []) {
                if ($examinationIds !== []) {
                    $treatmentBuilder->orWhereIn('id', $explicitTreatmentIds);
                } else {
                    $treatmentBuilder->whereIn('id', $explicitTreatmentIds);
                }
            }
            $treatmentBuilder->groupEnd();
            $treatments = $treatmentBuilder
                ->orderBy('start_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
        } else {
            $treatments = [];
        }
        $treatmentIds = $this->ids($treatments);

        $procedures = $explicitProcedures;
        if ($hasProcedureTable && $treatmentIds !== []) {
            $procedureRows = $this->db->table('clinical_procedures')
                ->where('clinic_id', $clinicId)
                ->where('patient_id', $patientId)
                ->whereIn('treatment_id', $treatmentIds)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
            $procedures = $this->uniqueRows(array_merge($procedures, $procedureRows));
        }

        $chargeLineIds = [];
        if ($this->db->tableExists('clinical_charge_lines')) {
            $chargeBuilder = $this->db->table('clinical_charge_lines')
                ->select('id')
                ->where('clinic_id', $clinicId)
                ->where('patient_id', $patientId)
                ->groupStart()
                ->where('appointment_id', $appointmentId);
            if ($examinationIds !== []) {
                $chargeBuilder->orWhereIn('examination_id', $examinationIds);
            }
            if ($treatmentIds !== []) {
                $chargeBuilder->orWhereIn('treatment_id', $treatmentIds);
            }
            $chargeLineIds = array_map(
                static fn(array $row): int => (int) $row['id'],
                $chargeBuilder->groupEnd()->get()->getResultArray()
            );
        }

        $trail = $this->moneyTrail->forChargeLines($clinicId, $chargeLineIds, 'appointment_checkout');
        $invoiceableChargeLines = array_values(array_filter(
            $trail['charge_lines'] ?? [],
            static fn(array $row): bool => !in_array(
                strtolower((string) ($row['status'] ?? 'planned')),
                ['invoiced', 'voided'],
                true
            )
        ));
        $currencies = array_values(array_unique(array_filter(array_map(
            static fn(array $row): string => strtoupper(trim((string) ($row['currency'] ?? ''))),
            $invoiceableChargeLines
        ))));

        $openProcedureStatuses = ['proposed', 'presented', 'accepted', 'scheduled', 'in_progress'];
        $openProcedureCount = count(array_filter(
            $procedures,
            static fn(array $row): bool => in_array((string) ($row['status'] ?? ''), $openProcedureStatuses, true)
        ));
        $legacyOpenTreatmentCount = count(array_filter(
            $treatments,
            static fn(array $row): bool => (string) ($row['status'] ?? '') !== 'completed'
        ));
        $summary = $trail['summary'] ?? [];
        $openChargeCount = (int) ($summary['open_charge_lines'] ?? 0);
        $balanceDue = (float) ($summary['balance_due'] ?? 0);

        return [
            'appointment' => $appointment,
            'link_basis' => $hasExplicitLink ? 'explicit' : 'patient_date_context',
            'examinations' => $examinations,
            'treatments' => $treatments,
            'procedures' => $procedures,
            'money_trail' => $trail,
            'summary' => $summary,
            'invoiceable_charge_lines' => $invoiceableChargeLines,
            'invoiceable_charge_line_ids' => $this->ids($invoiceableChargeLines),
            'invoiceable_currency' => count($currencies) === 1 ? $currencies[0] : null,
            'has_mixed_invoiceable_currencies' => count($currencies) > 1,
            'clinical_state' => $procedures === [] && $treatments === []
                ? 'not_recorded'
                : (($openProcedureCount > 0 || ($procedures === [] && $legacyOpenTreatmentCount > 0)) ? 'needs_review' : 'reviewed'),
            'billing_state' => $chargeLineIds === []
                ? 'not_captured'
                : ($openChargeCount > 0 ? 'ready_to_invoice' : 'invoiced'),
            'settlement_state' => (float) ($summary['charged'] ?? 0) <= 0
                ? 'no_invoice'
                : ($balanceDue > 0.009 ? 'balance_due' : 'settled'),
            'open_procedure_count' => $openProcedureCount,
            'legacy_open_treatment_count' => $legacyOpenTreatmentCount,
        ];
    }

    private function ids(array $rows): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn(array $row): int => (int) ($row['id'] ?? 0),
            $rows
        ))));
    }

    private function uniqueRows(array $rows): array
    {
        $unique = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $unique[$id] = $row;
            }
        }

        return array_values($unique);
    }
}
