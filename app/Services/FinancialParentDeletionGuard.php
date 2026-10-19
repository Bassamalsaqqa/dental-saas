<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

class FinancialParentDeletionException extends \DomainException {}
class FinancialParentHasHistoryException extends FinancialParentDeletionException {}
class FinancialParentNotFoundException extends FinancialParentDeletionException {}

class FinancialParentDeletionGuard
{
    private ConnectionInterface $db;
    private array $tableExistsCache = [];
    private bool $ownsTransaction = false;

    public function __construct(?ConnectionInterface $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Executes guarded deletion for a clinic.
     * Takes clinic row lock FOR UPDATE before ANY subscription, membership or clinic deletion.
     * Fails closed if the clinic has ANY linked finance history.
     * Rolls back all operations if any step fails or audit returns false.
     */
    public function deleteClinic(int $clinicId, callable $operation): mixed
    {
        if ($clinicId <= 0) {
            throw new FinancialParentNotFoundException('Invalid clinic ID.');
        }

        $this->begin();

        try {
            $this->assertClinicCanBeDeleted($clinicId);

            $result = $operation();
            if ($result === false) {
                throw new \RuntimeException('Clinic deletion operation failed.');
            }

            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Executes guarded deletion for a patient.
     * Takes clinic lock then target patient lock with clinic_id FOR UPDATE.
     * Fails closed if the patient has direct finance records or linked clinical charge lines.
     * Rolls back all operations if any step fails or audit returns false.
     */
    public function deletePatient(int $clinicId, int $patientId, callable $operation): mixed
    {
        if ($clinicId <= 0 || $patientId <= 0) {
            throw new FinancialParentNotFoundException('Invalid clinic or patient ID.');
        }

        $this->begin();

        try {
            $this->assertPatientCanBeDeleted($clinicId, $patientId);

            $result = $operation();
            if ($result === false) {
                throw new \RuntimeException('Patient deletion operation failed.');
            }

            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Executes guarded deletion for an examination.
     * Takes clinic lock then target examination lock with clinic_id FOR UPDATE.
     * Fails closed if examination has direct finance records or clinical charge lines with finance_id IS NOT NULL.
     * Rolls back all operations if any step fails.
     */
    public function deleteExamination(int $clinicId, int $examinationId, callable $operation): mixed
    {
        if ($clinicId <= 0 || $examinationId <= 0) {
            throw new FinancialParentNotFoundException('Invalid clinic or examination ID.');
        }

        $this->begin();

        try {
            $this->assertExaminationCanBeDeleted($clinicId, $examinationId);

            $result = $operation();
            if ($result === false) {
                throw new \RuntimeException('Examination deletion operation failed.');
            }

            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Asserts that a clinic can be physically deleted without violating financial history constraints.
     * Lock ordering: clinic row FOR UPDATE -> first nonlocking financial-history probe.
     */
    public function assertClinicCanBeDeleted(int $clinicId): void
    {
        $this->requireOwnedTransaction();
        if ($clinicId <= 0) {
            throw new FinancialParentNotFoundException('Invalid clinic ID.');
        }

        // 1. Lock clinic row first
        $clinic = $this->db->query(
            'SELECT id FROM ' . $this->table('clinics') . ' WHERE id = ? FOR UPDATE',
            [$clinicId]
        )->getRowArray();

        if (!$clinic) {
            throw new FinancialParentNotFoundException("Clinic {$clinicId} not found.");
        }

        // This must be the first consistent read after the clinic lock. Invoice creators
        // take the same clinic lock, so history cannot appear before this deletion commits.
        // Do not lock finance rows here: settlement holds finance then acquires the clinic FK
        // when inserting its audit; the reverse order would create a lock cycle.
        // 2. Fail closed if linked finance/history: clinic ANY finances
        $finance = $this->db->query(
            'SELECT id FROM ' . $this->table('finances') . ' WHERE clinic_id = ? ORDER BY id LIMIT 1',
            [$clinicId]
        )->getRowArray();

        if ($finance !== null) {
            throw new FinancialParentHasHistoryException("Cannot delete clinic {$clinicId} with existing financial records.");
        }

        // Legacy orphan ledgers and retained finance audits are history even without a parent.
        foreach (['finance_payments', 'provider_payouts', 'provider_payout_payments', 'finance_liquidity_transfers', 'activity_logs'] as $historyTable) {
            if (!$this->hasTable($historyTable)) {
                continue;
            }
            $sql = 'SELECT id FROM ' . $this->table($historyTable) . ' WHERE clinic_id = ?';
            if ($historyTable === 'activity_logs') {
                $sql .= " AND entity_type = 'finance'";
            }
            if ($this->db->query($sql . ' ORDER BY id LIMIT 1', [$clinicId])->getRowArray() !== null) {
                throw new FinancialParentHasHistoryException("Cannot delete clinic {$clinicId} with retained financial history.");
            }
        }

        // 3. Fail closed if any linked clinical charge lines with finance_id
        if ($this->hasTable('clinical_charge_lines')) {
            $chargeLine = $this->db->query(
                'SELECT id FROM ' . $this->table('clinical_charge_lines') . ' WHERE clinic_id = ? AND finance_id IS NOT NULL ORDER BY id LIMIT 1',
                [$clinicId]
            )->getRowArray();

            if ($chargeLine !== null) {
                throw new FinancialParentHasHistoryException("Cannot delete clinic {$clinicId} with existing financial charge lines.");
            }
        }
    }

    /**
     * Asserts that a patient can be physically deleted without violating financial history constraints.
     * Lock ordering: clinic row FOR UPDATE -> target patient FOR UPDATE -> first nonlocking financial-history probe.
     */
    public function assertPatientCanBeDeleted(int $clinicId, int $patientId): void
    {
        $this->requireOwnedTransaction();
        if ($clinicId <= 0 || $patientId <= 0) {
            throw new FinancialParentNotFoundException('Invalid clinic or patient ID.');
        }

        // 1. Lock clinic row first
        $clinic = $this->db->query(
            'SELECT id FROM ' . $this->table('clinics') . ' WHERE id = ? FOR UPDATE',
            [$clinicId]
        )->getRowArray();

        if (!$clinic) {
            throw new FinancialParentNotFoundException("Clinic {$clinicId} not found.");
        }

        // 2. Lock target patient with clinic_id
        $patient = $this->db->query(
            'SELECT id, clinic_id FROM ' . $this->table('patients') . ' WHERE id = ? AND clinic_id = ? FOR UPDATE',
            [$patientId, $clinicId]
        )->getRowArray();

        if (!$patient || (int) ($patient['clinic_id'] ?? 0) !== $clinicId) {
            throw new FinancialParentNotFoundException("Patient {$patientId} not found in clinic {$clinicId}.");
        }

        // 3. Direct finances.patient_id
        $directFinance = $this->db->query(
            'SELECT id FROM ' . $this->table('finances') . ' WHERE clinic_id = ? AND patient_id = ? ORDER BY id LIMIT 1',
            [$clinicId, $patientId]
        )->getRowArray();

        if ($directFinance !== null) {
            throw new FinancialParentHasHistoryException("Cannot delete patient {$patientId} with linked financial records.");
        }

        // 4. Linked clinical charge finance_id
        if ($this->hasTable('clinical_charge_lines')) {
            $linkedCharge = $this->db->query(
                'SELECT id FROM ' . $this->table('clinical_charge_lines') . ' WHERE clinic_id = ? AND patient_id = ? AND finance_id IS NOT NULL ORDER BY id LIMIT 1',
                [$clinicId, $patientId]
            )->getRowArray();

            if ($linkedCharge !== null) {
                throw new FinancialParentHasHistoryException("Cannot delete patient {$patientId} with clinical charge lines linked to financial records.");
            }
        }
    }

    /**
     * Asserts that an examination can be physically deleted without violating financial history constraints.
     * Lock ordering: clinic row FOR UPDATE -> target examination FOR UPDATE -> first nonlocking financial-history probe.
     */
    public function assertExaminationCanBeDeleted(int $clinicId, int $examinationId): void
    {
        $this->requireOwnedTransaction();
        if ($clinicId <= 0 || $examinationId <= 0) {
            throw new FinancialParentNotFoundException('Invalid clinic or examination ID.');
        }

        // 1. Lock clinic row first
        $clinic = $this->db->query(
            'SELECT id FROM ' . $this->table('clinics') . ' WHERE id = ? FOR UPDATE',
            [$clinicId]
        )->getRowArray();

        if (!$clinic) {
            throw new FinancialParentNotFoundException("Clinic {$clinicId} not found.");
        }

        // 2. Lock target examination with clinic_id
        $examination = $this->db->query(
            'SELECT id, clinic_id FROM ' . $this->table('examinations') . ' WHERE id = ? AND clinic_id = ? FOR UPDATE',
            [$examinationId, $clinicId]
        )->getRowArray();

        if (!$examination || (int) ($examination['clinic_id'] ?? 0) !== $clinicId) {
            throw new FinancialParentNotFoundException("Examination {$examinationId} not found in clinic {$clinicId}.");
        }

        // 3. Direct finances.examination_id
        $directFinance = $this->db->query(
            'SELECT id FROM ' . $this->table('finances') . ' WHERE clinic_id = ? AND examination_id = ? ORDER BY id LIMIT 1',
            [$clinicId, $examinationId]
        )->getRowArray();

        if ($directFinance !== null) {
            throw new FinancialParentHasHistoryException("Cannot delete examination {$examinationId} with linked financial records.");
        }

        // 4. clinical_charge_lines.examination_id where finance_id IS NOT NULL (multi-exam invoice parent may have NULL examination_id)
        if ($this->hasTable('clinical_charge_lines')) {
            $linkedCharge = $this->db->query(
                'SELECT id FROM ' . $this->table('clinical_charge_lines') . ' WHERE clinic_id = ? AND examination_id = ? AND finance_id IS NOT NULL ORDER BY id LIMIT 1',
                [$clinicId, $examinationId]
            )->getRowArray();

            if ($linkedCharge !== null) {
                throw new FinancialParentHasHistoryException("Cannot delete examination {$examinationId} with clinical charge lines linked to financial records.");
            }
        }
    }

    private function begin(): void
    {
        if ($this->ownsTransaction || (int) $this->db->transDepth !== 0 || $this->db->transStatus() === false) {
            throw new \RuntimeException('Financial parent deletion guard requires an independent, healthy transaction.');
        }

        if (!$this->db->transBegin()) {
            throw new \RuntimeException('Unable to begin transaction for financial parent deletion guard.');
        }
        $this->ownsTransaction = true;
    }

    private function commit(): void
    {
        $this->requireOwnedTransaction();
        if ((int) $this->db->transDepth !== 1 || $this->db->transStatus() === false || !$this->db->transCommit()) {
            $this->rollback();
            throw new \RuntimeException('Unable to commit transaction for financial parent deletion guard.');
        }
        $this->ownsTransaction = false;
    }

    private function rollback(): void
    {
        if ($this->ownsTransaction && (int) $this->db->transDepth > 0) {
            $this->db->transRollback();
        }
        $this->ownsTransaction = false;
    }

    private function requireOwnedTransaction(): void
    {
        if (!$this->ownsTransaction || (int) $this->db->transDepth !== 1 || $this->db->transStatus() === false) {
            throw new \RuntimeException('Deletion assertion requires the guard-owned healthy transaction.');
        }
    }

    public function table(string $name): string
    {
        return $this->db->escapeIdentifiers($this->db->prefixTable($name));
    }

    public function hasTable(string $table): bool
    {
        return $this->tableExistsCache[$table] ??= $this->db->tableExists($table);
    }
}
