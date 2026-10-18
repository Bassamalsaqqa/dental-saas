<?php

namespace Tests\Unit;

use App\Services\FinancialParentDeletionException;
use App\Services\FinancialParentDeletionGuard;
use App\Services\FinancialParentHasHistoryException;
use App\Services\FinancialParentNotFoundException;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Test\CIUnitTestCase;

final class FinancialParentDeletionGuardTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        class_exists(FinancialParentDeletionGuard::class);
    }

    private function setDepth(BaseConnection $db, int $depth): void
    {
        (new \ReflectionProperty(BaseConnection::class, 'transDepth'))->setValue($db, $depth);
    }

    private function createMockDb(): BaseConnection
    {
        $mockDb = $this->createMock(BaseConnection::class);
        $this->setDepth($mockDb, 0);
        $mockDb->method('__get')->willReturnCallback(static function (string $key) use ($mockDb) {
            return $key === 'transDepth' ? (new \ReflectionProperty(BaseConnection::class, 'transDepth'))->getValue($mockDb) : null;
        });
        $mockDb->method('prefixTable')->willReturnCallback(static fn (string $table) => $table);
        $mockDb->method('escapeIdentifiers')->willReturnCallback(static fn (string $table) => "`{$table}`");
        $mockDb->method('transStatus')->willReturn(true);
        $mockDb->method('transBegin')->willReturnCallback(function () use ($mockDb) { $this->setDepth($mockDb, 1); return true; });
        $mockDb->method('transCommit')->willReturnCallback(function () use ($mockDb) { $this->setDepth($mockDb, 0); return true; });
        $mockDb->method('transRollback')->willReturnCallback(function () use ($mockDb) { $this->setDepth($mockDb, 0); return true; });
        $mockDb->method('tableExists')->willReturn(true);

        return $mockDb;
    }

    private function createResultMock(?array $row): BaseResult
    {
        $mockResult = $this->createMock(BaseResult::class);
        $mockResult->method('getRowArray')->willReturn($row);
        return $mockResult;
    }

    public function testExceptionHierarchy(): void
    {
        $base = new FinancialParentDeletionException('base');
        $this->assertInstanceOf(\DomainException::class, $base);

        $history = new FinancialParentHasHistoryException('history');
        $this->assertInstanceOf(FinancialParentDeletionException::class, $history);
        $this->assertInstanceOf(\DomainException::class, $history);

        $notFound = new FinancialParentNotFoundException('not found');
        $this->assertInstanceOf(FinancialParentDeletionException::class, $notFound);
        $this->assertInstanceOf(\DomainException::class, $notFound);
    }

    public function testRejectsInvalidIds(): void
    {
        $mockDb = $this->createMockDb();
        $guard = new FinancialParentDeletionGuard($mockDb);

        $this->expectException(FinancialParentNotFoundException::class);
        $guard->deleteClinic(0, static fn () => true);
    }

    public function testRejectsInvalidPatientIds(): void
    {
        $mockDb = $this->createMockDb();
        $guard = new FinancialParentDeletionGuard($mockDb);

        $this->expectException(FinancialParentNotFoundException::class);
        $guard->deletePatient(1, 0, static fn () => true);
    }

    public function testRejectsInvalidExaminationIds(): void
    {
        $mockDb = $this->createMockDb();
        $guard = new FinancialParentDeletionGuard($mockDb);

        $this->expectException(FinancialParentNotFoundException::class);
        $guard->deleteExamination(0, 1, static fn () => true);
    }

    public function testRejectsNestedTransaction(): void
    {
        $mockDb = $this->createMockDb();
        $this->setDepth($mockDb, 1);

        $guard = new FinancialParentDeletionGuard($mockDb);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('independent, healthy transaction');
        $guard->deleteClinic(1, static fn () => true);
    }

    public function testRejectsFailedTransactionStatus(): void
    {
        $mockDb = $this->createMock(BaseConnection::class);
        $this->setDepth($mockDb, 0);
        $mockDb->method('transStatus')->willReturn(false);

        $guard = new FinancialParentDeletionGuard($mockDb);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('independent, healthy transaction');
        $guard->deleteClinic(1, static fn () => true);
    }

    public function testRollsBackWhenCallbackReturnsFalse(): void
    {
        $mockDb = $this->createMockDb();
        $clinicResult = $this->createResultMock(['id' => 1]);
        $emptyResult = $this->createResultMock(null);

        $mockDb->method('query')->willReturnCallback(function (string $sql) use ($clinicResult, $emptyResult) {
            if (str_contains($sql, '`clinics`')) {
                return $clinicResult;
            }
            return $emptyResult;
        });

        $mockDb->expects($this->once())->method('transRollback');
        $mockDb->expects($this->never())->method('transCommit');

        $guard = new FinancialParentDeletionGuard($mockDb);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Clinic deletion operation failed.');
        $guard->deleteClinic(1, static fn () => false);
    }

    public function testRollsBackWhenCallbackThrows(): void
    {
        $mockDb = $this->createMockDb();
        $clinicResult = $this->createResultMock(['id' => 1]);
        $emptyResult = $this->createResultMock(null);

        $mockDb->method('query')->willReturnCallback(function (string $sql) use ($clinicResult, $emptyResult) {
            if (str_contains($sql, '`clinics`')) {
                return $clinicResult;
            }
            return $emptyResult;
        });

        $mockDb->expects($this->once())->method('transRollback');
        $mockDb->expects($this->never())->method('transCommit');

        $guard = new FinancialParentDeletionGuard($mockDb);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Audit service write failed.');
        $guard->deleteClinic(1, function () {
            throw new \LogicException('Audit service write failed.');
        });
    }

    public function testFailsClosedOnMissingClinic(): void
    {
        $mockDb = $this->createMockDb();
        $mockDb->method('query')->willReturn($this->createResultMock(null));
        $mockDb->expects($this->once())->method('transRollback');

        $guard = new FinancialParentDeletionGuard($mockDb);

        $this->expectException(FinancialParentNotFoundException::class);
        $this->expectExceptionMessage('Clinic 5 not found.');
        $guard->deleteClinic(5, static fn () => true);
    }

    public function testFailsClosedOnClinicWithFinances(): void
    {
        $mockDb = $this->createMockDb();
        $clinicResult = $this->createResultMock(['id' => 5]);
        $financeResult = $this->createResultMock(['id' => 99]);

        $mockDb->method('query')->willReturnCallback(function (string $sql) use ($clinicResult, $financeResult) {
            if (str_contains($sql, '`clinics`')) {
                return $clinicResult;
            }
            if (str_contains($sql, '`finances`')) {
                return $financeResult;
            }
            return $this->createResultMock(null);
        });

        $mockDb->expects($this->once())->method('transRollback');

        $guard = new FinancialParentDeletionGuard($mockDb);

        $this->expectException(FinancialParentHasHistoryException::class);
        $this->expectExceptionMessage('Cannot delete clinic 5 with existing financial records.');
        $guard->deleteClinic(5, static fn () => true);
    }

    public function testFailsClosedOnPatientDirectFinances(): void
    {
        $mockDb = $this->createMockDb();
        $clinicResult = $this->createResultMock(['id' => 2]);
        $patientResult = $this->createResultMock(['id' => 10, 'clinic_id' => 2]);
        $financeResult = $this->createResultMock(['id' => 101]);

        $mockDb->method('query')->willReturnCallback(function (string $sql) use ($clinicResult, $patientResult, $financeResult) {
            if (str_contains($sql, '`clinics`')) {
                return $clinicResult;
            }
            if (str_contains($sql, '`patients`')) {
                return $patientResult;
            }
            if (str_contains($sql, '`finances`')) {
                return $financeResult;
            }
            return $this->createResultMock(null);
        });

        $mockDb->expects($this->once())->method('transRollback');

        $guard = new FinancialParentDeletionGuard($mockDb);

        $this->expectException(FinancialParentHasHistoryException::class);
        $this->expectExceptionMessage('Cannot delete patient 10 with linked financial records.');
        $guard->deletePatient(2, 10, static fn () => true);
    }

    public function testFailsClosedOnPatientClinicalChargeLinesWithFinance(): void
    {
        $mockDb = $this->createMockDb();
        $clinicResult = $this->createResultMock(['id' => 2]);
        $patientResult = $this->createResultMock(['id' => 10, 'clinic_id' => 2]);
        $cclResult = $this->createResultMock(['id' => 301]);

        $mockDb->method('query')->willReturnCallback(function (string $sql) use ($clinicResult, $patientResult, $cclResult) {
            if (str_contains($sql, '`clinics`')) {
                return $clinicResult;
            }
            if (str_contains($sql, '`patients`')) {
                return $patientResult;
            }
            if (str_contains($sql, '`finances`')) {
                return $this->createResultMock(null);
            }
            if (str_contains($sql, '`clinical_charge_lines`')) {
                return $cclResult;
            }
            return $this->createResultMock(null);
        });

        $mockDb->expects($this->once())->method('transRollback');

        $guard = new FinancialParentDeletionGuard($mockDb);

        $this->expectException(FinancialParentHasHistoryException::class);
        $this->expectExceptionMessage('Cannot delete patient 10 with clinical charge lines linked to financial records.');
        $guard->deletePatient(2, 10, static fn () => true);
    }

    public function testFailsClosedOnExaminationDirectFinances(): void
    {
        $mockDb = $this->createMockDb();
        $clinicResult = $this->createResultMock(['id' => 3]);
        $examResult = $this->createResultMock(['id' => 20, 'clinic_id' => 3]);
        $financeResult = $this->createResultMock(['id' => 501]);

        $mockDb->method('query')->willReturnCallback(function (string $sql) use ($clinicResult, $examResult, $financeResult) {
            if (str_contains($sql, '`clinics`')) {
                return $clinicResult;
            }
            if (str_contains($sql, '`examinations`')) {
                return $examResult;
            }
            if (str_contains($sql, '`finances`')) {
                return $financeResult;
            }
            return $this->createResultMock(null);
        });

        $mockDb->expects($this->once())->method('transRollback');

        $guard = new FinancialParentDeletionGuard($mockDb);

        $this->expectException(FinancialParentHasHistoryException::class);
        $this->expectExceptionMessage('Cannot delete examination 20 with linked financial records.');
        $guard->deleteExamination(3, 20, static fn () => true);
    }

    public function testFailsClosedOnExaminationClinicalChargeLinesWithFinance(): void
    {
        $mockDb = $this->createMockDb();
        $clinicResult = $this->createResultMock(['id' => 3]);
        $examResult = $this->createResultMock(['id' => 20, 'clinic_id' => 3]);
        $cclResult = $this->createResultMock(['id' => 601]);

        $mockDb->method('query')->willReturnCallback(function (string $sql) use ($clinicResult, $examResult, $cclResult) {
            if (str_contains($sql, '`clinics`')) {
                return $clinicResult;
            }
            if (str_contains($sql, '`examinations`')) {
                return $examResult;
            }
            if (str_contains($sql, '`finances`')) {
                return $this->createResultMock(null);
            }
            if (str_contains($sql, '`clinical_charge_lines`')) {
                return $cclResult;
            }
            return $this->createResultMock(null);
        });

        $mockDb->expects($this->once())->method('transRollback');

        $guard = new FinancialParentDeletionGuard($mockDb);

        $this->expectException(FinancialParentHasHistoryException::class);
        $this->expectExceptionMessage('Cannot delete examination 20 with clinical charge lines linked to financial records.');
        $guard->deleteExamination(3, 20, static fn () => true);
    }

    public function testSuccessfulClinicDeleteCommitsTransaction(): void
    {
        $mockDb = $this->createMockDb();
        $clinicResult = $this->createResultMock(['id' => 7]);

        $mockDb->method('query')->willReturnCallback(function (string $sql) use ($clinicResult) {
            if (str_contains($sql, '`clinics`')) {
                return $clinicResult;
            }
            return $this->createResultMock(null);
        });

        $mockDb->expects($this->once())->method('transCommit')->willReturn(true);
        $mockDb->expects($this->never())->method('transRollback');

        $guard = new FinancialParentDeletionGuard($mockDb);
        $executed = false;
        $result = $guard->deleteClinic(7, function () use (&$executed) {
            $executed = true;
            return true;
        });

        $this->assertTrue($executed);
        $this->assertTrue($result);
    }

    public function testSuccessfulPatientDeleteCommitsTransaction(): void
    {
        $mockDb = $this->createMockDb();
        $clinicResult = $this->createResultMock(['id' => 7]);
        $patientResult = $this->createResultMock(['id' => 15, 'clinic_id' => 7]);

        $mockDb->method('query')->willReturnCallback(function (string $sql) use ($clinicResult, $patientResult) {
            if (str_contains($sql, '`clinics`')) {
                return $clinicResult;
            }
            if (str_contains($sql, '`patients`')) {
                return $patientResult;
            }
            return $this->createResultMock(null);
        });

        $mockDb->expects($this->once())->method('transCommit')->willReturn(true);
        $mockDb->expects($this->never())->method('transRollback');

        $guard = new FinancialParentDeletionGuard($mockDb);
        $executed = false;
        $result = $guard->deletePatient(7, 15, function () use (&$executed) {
            $executed = true;
            return true;
        });

        $this->assertTrue($executed);
        $this->assertTrue($result);
    }

    public function testSuccessfulExaminationDeleteCommitsTransaction(): void
    {
        $mockDb = $this->createMockDb();
        $clinicResult = $this->createResultMock(['id' => 7]);
        $examResult = $this->createResultMock(['id' => 25, 'clinic_id' => 7]);

        $mockDb->method('query')->willReturnCallback(function (string $sql) use ($clinicResult, $examResult) {
            if (str_contains($sql, '`clinics`')) {
                return $clinicResult;
            }
            if (str_contains($sql, '`examinations`')) {
                return $examResult;
            }
            return $this->createResultMock(null);
        });

        $mockDb->expects($this->once())->method('transCommit')->willReturn(true);
        $mockDb->expects($this->never())->method('transRollback');

        $guard = new FinancialParentDeletionGuard($mockDb);
        $executed = false;
        $result = $guard->deleteExamination(7, 25, function () use (&$executed) {
            $executed = true;
            return true;
        });

        $this->assertTrue($executed);
        $this->assertTrue($result);
    }
}
