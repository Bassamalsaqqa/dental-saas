<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

class FinanceCreationException extends \DomainException {}
class FinanceCreationTransactionException extends FinanceCreationException {}
class FinanceCreationContextException extends FinanceCreationException {}
class FinanceCreationParentException extends FinanceCreationException {}

class FinanceCreationGuard
{
    private const MAX_INT_ID = 2147483647;

    private ConnectionInterface $db;

    public function __construct(?ConnectionInterface $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Validates and locks parents for finance creation.
     * Lock ordering: Clinic row FOR UPDATE -> Patient row FOR UPDATE (if referenced) -> Examination row FOR UPDATE (if referenced).
     * Enforces active healthy transaction and explicit tenant context match.
     * Normalizes optional empty patient/examination references to null in returned row.
     *
     * @param array $row Raw finance insert data
     * @param int $activeClinicId Current active clinic ID context
     * @param bool $isInvoice Whether this is an invoice (patient is required for invoice)
     * @return array Normalized finance data
     */
    public function validateAndLock(array $row, int $activeClinicId, bool $isInvoice = false): array
    {
        $type = (string) ($row['transaction_type'] ?? '');
        $isInvoice = $isInvoice || ($type === 'invoice');

        // 1. Transaction health check
        if ((int) $this->db->transDepth < 1 || $this->db->transStatus() === false) {
            throw new FinanceCreationTransactionException(
                $isInvoice ? 'Invoice creation requires an active transaction.' : 'Finance creation requires an active transaction.'
            );
        }

        // 2. Strict clinic ID validation before any cast
        $clinicIdRaw = $row['clinic_id'] ?? null;
        if ((!is_int($clinicIdRaw) && !is_string($clinicIdRaw))
            || !preg_match('/^[1-9][0-9]*$/D', (string) $clinicIdRaw)
            || (is_string($clinicIdRaw) && strlen($clinicIdRaw) > 10)
            || (float) $clinicIdRaw > self::MAX_INT_ID
            || (int) $clinicIdRaw <= 0
            || (int) $clinicIdRaw > self::MAX_INT_ID) {
            throw new FinanceCreationContextException(
                $isInvoice ? 'Invoice creation clinic does not match the active context.' : 'Finance creation clinic does not match the active context.'
            );
        }
        $clinicId = (int) $clinicIdRaw;

        if ($activeClinicId <= 0 || $clinicId !== $activeClinicId) {
            throw new FinanceCreationContextException(
                $isInvoice ? 'Invoice creation clinic does not match the active context.' : 'Finance creation clinic does not match the active context.'
            );
        }

        // 3. Referenced patient ID validation and normalization
        $patientIdRaw = $row['patient_id'] ?? null;
        $patientId = null;
        if ($patientIdRaw !== null && $patientIdRaw !== '') {
            if ((!is_int($patientIdRaw) && !is_string($patientIdRaw))
                || !preg_match('/^[1-9][0-9]*$/D', (string) $patientIdRaw)
                || (is_string($patientIdRaw) && strlen((string) $patientIdRaw) > 10)
                || (float) $patientIdRaw > self::MAX_INT_ID
                || (int) $patientIdRaw <= 0
                || (int) $patientIdRaw > self::MAX_INT_ID) {
                throw new FinanceCreationParentException(
                    $isInvoice ? 'Invoice patient and clinic are required.' : 'Invalid finance patient.'
                );
            }
            $patientId = (int) $patientIdRaw;
        } elseif ($isInvoice) {
            throw new FinanceCreationParentException('Invoice patient and clinic are required.');
        }

        // 4. Referenced examination ID validation and normalization
        $examinationIdRaw = $row['examination_id'] ?? null;
        $examinationId = null;
        if ($examinationIdRaw !== null && $examinationIdRaw !== '') {
            if ((!is_int($examinationIdRaw) && !is_string($examinationIdRaw))
                || !preg_match('/^[1-9][0-9]*$/D', (string) $examinationIdRaw)
                || (is_string($examinationIdRaw) && strlen((string) $examinationIdRaw) > 10)
                || (float) $examinationIdRaw > self::MAX_INT_ID
                || (int) $examinationIdRaw <= 0
                || (int) $examinationIdRaw > self::MAX_INT_ID) {
                throw new FinanceCreationParentException(
                    $isInvoice ? 'Invalid invoice examination.' : 'Invalid finance examination.'
                );
            }
            $examinationId = (int) $examinationIdRaw;
        }

        // Examination requires patient for ALL types, no automatic derive
        if ($examinationId !== null && $patientId === null) {
            throw new FinanceCreationParentException(
                $isInvoice ? 'Invoice examination does not belong to its patient.' : 'Finance examination does not belong to its patient.'
            );
        }

        // 5. Consistent parent locking: Clinic -> Patient (if referenced) -> Examination (if referenced)
        $this->lockParents($clinicId, $patientId, $examinationId, $isInvoice);

        // Normalize optional empty refs to NULL in returned row
        $row['clinic_id'] = $clinicId;
        $row['patient_id'] = $patientId;
        $row['examination_id'] = $examinationId;

        return $row;
    }

    /**
     * Authoritatively acquires parent locks in consistent canonical order:
     * Clinic row FOR UPDATE -> Patient row FOR UPDATE -> Examination row FOR UPDATE.
     * Validates clinic scope, parent existence, and exam-patient match.
     * Requires active healthy transaction.
     */
    public function lockParents(int $clinicId, ?int $patientId = null, ?int $examinationId = null, bool $isInvoice = false): void
    {
        if ((int) $this->db->transDepth < 1 || $this->db->transStatus() === false) {
            throw new FinanceCreationTransactionException(
                $isInvoice ? 'Invoice creation requires an active transaction.' : 'Finance creation requires an active transaction.'
            );
        }

        if ($clinicId <= 0 || $clinicId > self::MAX_INT_ID) {
            throw new FinanceCreationParentException($isInvoice ? 'Invoice patient and clinic are required.' : 'Invalid clinic identity.');
        }

        // 1. Lock clinic row
        $clinic = $this->lockedParent(
            'SELECT id FROM ' . $this->table('clinics') . ' WHERE id = ? FOR UPDATE',
            [$clinicId]
        );

        if (!$clinic) {
            throw new FinanceCreationParentException(
                $isInvoice ? 'Invoice parent identity is not available in this clinic.' : 'Finance parent identity is not available in this clinic.'
            );
        }

        // Never wait on child parents while holding clinic: existing finance/clinical
        // writers can hold a child and acquire the clinic FK during audit. NOWAIT
        // fails closed so the transaction owner rolls back and may retry.
        // 2. Lock patient row (if referenced)
        if ($patientId !== null && $patientId > 0) {
            if ($patientId > self::MAX_INT_ID) {
                throw new FinanceCreationParentException($isInvoice ? 'Invoice patient and clinic are required.' : 'Invalid finance patient.');
            }
            $patient = $this->lockedParent(
                'SELECT id, clinic_id FROM ' . $this->table('patients') . ' WHERE id = ? AND clinic_id = ? FOR UPDATE NOWAIT',
                [$patientId, $clinicId]
            );

            if (!$patient) {
                throw new FinanceCreationParentException(
                    $isInvoice ? 'Invoice parent identity is not available in this clinic.' : 'Finance parent identity is not available in this clinic.'
                );
            }
        }

        // 3. Lock examination row (if referenced)
        if ($examinationId !== null && $examinationId > 0) {
            if ($examinationId > self::MAX_INT_ID) {
                throw new FinanceCreationParentException($isInvoice ? 'Invalid invoice examination.' : 'Invalid finance examination.');
            }
            if ($patientId === null) {
                throw new FinanceCreationParentException(
                    $isInvoice ? 'Invoice examination does not belong to its patient.' : 'Finance examination does not belong to its patient.'
                );
            }
            $exam = $this->lockedParent(
                'SELECT id, clinic_id, patient_id FROM ' . $this->table('examinations') . ' WHERE clinic_id = ? AND id = ? FOR UPDATE NOWAIT',
                [$clinicId, $examinationId]
            );

            if (!$exam) {
                throw new FinanceCreationParentException(
                    $isInvoice ? 'Invoice parent identity is not available in this clinic.' : 'Finance parent identity is not available in this clinic.'
                );
            }

            if ((int) ($exam['patient_id'] ?? 0) !== $patientId) {
                throw new FinanceCreationParentException(
                    $isInvoice ? 'Invoice examination does not belong to its patient.' : 'Finance examination does not belong to its patient.'
                );
            }
        }
    }

    private function lockedParent(string $sql, array $params): ?array
    {
        $result = $this->db->query($sql, $params);
        if ($result === false) {
            // Keep the failed transaction state: only its owner may roll back.
            $sqlErrorCode = (int) ($this->db->error()['code'] ?? 0);
            throw new FinanceCreationTransactionException('Finance parent is busy or unavailable. Roll back and retry the operation.', $sqlErrorCode);
        }
        return $result->getRowArray();
    }

    public function table(string $name): string
    {
        return $this->db->escapeIdentifiers($this->db->prefixTable($name));
    }
}
