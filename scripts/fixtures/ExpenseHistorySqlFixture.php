<?php

declare(strict_types=1);

use App\Libraries\ApiRuntimeContext;
use App\Models\FinanceModel;
use App\Services\FinanceApiMutationService;
use App\Services\FinanceNotFoundException;
use App\Services\InvoiceLifecyclePolicy;
use CodeIgniter\Database\BaseConnection;

/**
 * Synthetic-only SQL fixture verifying expense history-sensitive field protection.
 * Strict explicitly injected isolated BaseConnection and run() result.
 * No DDL, no schema changes, operates only on new synthetic owned records.
 */
final class ExpenseHistorySqlFixture
{
    public static function run(BaseConnection $db, int $clinicId, int $actorId, int $patientId, int $foreignClinicId): array
    {
        if ($clinicId <= 0 || $actorId <= 0 || $patientId <= 0 || $foreignClinicId <= 0 || $clinicId === $foreignClinicId) {
            throw new RuntimeException('Explicit distinct synthetic clinic, actor and patient identities are required.');
        }
        if ((int) $db->transDepth !== 0) {
            throw new RuntimeException('Requires owned transaction boundary.');
        }

        $ids = [];
        $checks = [];
        $priorClinic = ApiRuntimeContext::getClinicId();
        $priorActor = ApiRuntimeContext::getUserId();
        ApiRuntimeContext::setClinicId($clinicId);
        ApiRuntimeContext::setUserId($actorId);

        $assert = static function (bool $condition, string $name) use (&$checks): void {
            if (!$condition) {
                throw new RuntimeException('Assertion failed: ' . $name);
            }
            $checks[] = $name;
        };

        $insertExpense = static function (array $extra = []) use ($db, $clinicId, $patientId, &$ids): int {
            $row = array_replace([
                'clinic_id' => $clinicId,
                'patient_id' => $patientId,
                'transaction_id' => 'EXP_HIST_' . bin2hex(random_bytes(8)),
                'transaction_type' => 'expense',
                'amount' => 500.0,
                'amount_original' => 500.0,
                'exchange_rate' => 1.0,
                'currency' => 'ILS',
                'discount_amount' => 0.0,
                'tax_percentage' => 0.0,
                'tax_amount' => 0.0,
                'total_amount' => 500.0,
                'payment_method' => 'bank_transfer',
                'liquidity_account' => 'bank',
                'payment_status' => 'pending',
                'amount_paid' => 0.0,
                'balance_due' => 500.0,
                'expense_category' => 'supplies',
                'counterparty_name' => 'Original Dental Supplier Ltd',
                'description' => 'Original Dental Material Purchase',
                'service_details' => 'Composite resin pack A2/A3',
                'due_date' => '2096-07-15',
                'transaction_date' => '2096-07-01',
                'notes' => 'Synthetic expense initial notes',
                'created_at' => '2096-07-01 12:00:00',
                'updated_at' => '2096-07-01 12:00:00',
            ], $extra);

            if (!$db->table('finances')->insert($row)) {
                throw new RuntimeException('ExpenseHistorySqlFixture insert failed.');
            }
            $id = (int) $db->insertID();
            $ids[] = $id;
            return $id;
        };

        try {
            $apiService = new FinanceApiMutationService($db);

            // 1. Paid four fields rejected + row unchanged via API
            $paidExpense = $insertExpense([
                'payment_status' => 'paid',
                'amount_paid' => 500.0,
                'balance_due' => 0.0,
                'paid_date' => '2096-07-02',
            ]);
            $paidBefore = $db->table('finances')->where('id', $paidExpense)->get()->getRowArray();

            $fourFieldsTampers = [
                'counterparty_name' => ['counterparty_name' => 'Unauthorized Payee Tamper'],
                'due_date' => ['due_date' => '2096-08-30'],
                'description' => ['description' => 'Rewritten Expense Description'],
                'service_details' => ['service_details' => 'Rewritten Service Details'],
                'all_four' => [
                    'counterparty_name' => 'Unauthorized Payee Tamper All',
                    'due_date' => '2096-09-15',
                    'description' => 'Rewritten All Description',
                    'service_details' => 'Rewritten All Details',
                ],
            ];

            foreach ($fourFieldsTampers as $tamperName => $tamper) {
                try {
                    $apiService->updateFinance($clinicId, $paidExpense, $tamper, $actorId);
                    throw new RuntimeException('Paid expense mutation accepted for: ' . $tamperName);
                } catch (\DomainException $e) {
                    $checks[] = 'Paid expense rejects mutation of ' . $tamperName;
                }
            }

            $assert(
                $paidBefore === $db->table('finances')->where('id', $paidExpense)->get()->getRowArray(),
                'Paid expense row unchanged after rejected four fields mutations'
            );

            // 2. Pending with real finance_payment history rejected via API
            $pendingLinked = $insertExpense([
                'payment_status' => 'pending',
                'amount_paid' => 0.0,
                'balance_due' => 500.0,
            ]);

            $paymentRow = [
                'clinic_id' => $clinicId,
                'finance_id' => $pendingLinked,
                'type' => 'payment',
                'amount' => 150.0,
                'payment_method' => 'bank_transfer',
                'liquidity_account' => 'bank',
                'payment_date' => '2096-07-03',
                'created_by' => $actorId,
                'created_at' => '2096-07-03 12:00:00',
            ];
            if (!$db->table('finance_payments')->insert($paymentRow)) {
                throw new RuntimeException('ExpenseHistorySqlFixture payment insert failed.');
            }
            $pendingLinkedBefore = $db->table('finances')->where('id', $pendingLinked)->get()->getRowArray();

            foreach ($fourFieldsTampers as $tamperName => $tamper) {
                try {
                    $apiService->updateFinance($clinicId, $pendingLinked, $tamper, $actorId);
                    throw new RuntimeException('Pending linked expense mutation accepted for: ' . $tamperName);
                } catch (\DomainException $e) {
                    $checks[] = 'Pending expense with real finance_payment rejects ' . $tamperName;
                }
            }

            $assert(
                $pendingLinkedBefore === $db->table('finances')->where('id', $pendingLinked)->get()->getRowArray(),
                'Pending expense with real finance_payment unchanged after rejected mutations'
            );

            // 3. Pending unlinked edits four fields accepted via API
            $pendingUnlinked = $insertExpense([
                'payment_status' => 'pending',
                'amount_paid' => 0.0,
                'balance_due' => 500.0,
                'counterparty_name' => 'Original Vendor',
                'due_date' => '2096-07-10',
                'description' => 'Original Description',
                'service_details' => 'Original Specification',
            ]);

            $acceptedEdits = [
                'counterparty_name' => 'Legitimate Updated Vendor',
                'due_date' => '2096-07-20',
                'description' => 'Legitimate Updated Description',
                'service_details' => 'Legitimate Updated Specification',
            ];

            $updatedRow = $apiService->updateFinance($clinicId, $pendingUnlinked, $acceptedEdits, $actorId);

            $assert(
                $updatedRow['counterparty_name'] === 'Legitimate Updated Vendor'
                && $updatedRow['due_date'] === '2096-07-20'
                && $updatedRow['description'] === 'Legitimate Updated Description'
                && $updatedRow['service_details'] === 'Legitimate Updated Specification',
                'Pending unlinked expense accepted edits on all four fields'
            );

            $persistedUnlinked = $db->table('finances')->where('id', $pendingUnlinked)->get()->getRowArray();
            $assert(
                $persistedUnlinked === $updatedRow,
                'Database row reflects pending unlinked edits exactly'
            );

            // 4. Identical roundtrip paid unchanged via API
            $roundtripPayload = [
                'counterparty_name' => $paidBefore['counterparty_name'],
                'due_date' => $paidBefore['due_date'],
                'description' => $paidBefore['description'],
                'service_details' => $paidBefore['service_details'],
            ];

            $roundtripResult = $apiService->updateFinance($clinicId, $paidExpense, $roundtripPayload, $actorId);
            $assert(
                $roundtripResult === $paidBefore,
                'Identical roundtrip on paid expense returns existing row'
            );
            $assert(
                $paidBefore === $db->table('finances')->where('id', $paidExpense)->get()->getRowArray(),
                'Identical roundtrip paid expense remains completely unchanged in database'
            );

            // 5. Cross-clinic reject via API
            ApiRuntimeContext::setClinicId($foreignClinicId);
            try {
                $apiService->updateFinance($foreignClinicId, $pendingUnlinked, [
                    'counterparty_name' => 'Foreign Clinic Hijacker',
                ], $actorId);
                throw new RuntimeException('Foreign clinic expense update accepted.');
            } catch (FinanceNotFoundException $e) {
                $checks[] = 'Cross-clinic expense mutation rejected fail-closed';
            } finally {
                ApiRuntimeContext::setClinicId($clinicId);
            }
            $assert(
                $persistedUnlinked === $db->table('finances')->where('id', $pendingUnlinked)->get()->getRowArray(),
                'Cross-clinic attempt leaves target expense unchanged'
            );

            // 6. internal_note audited permitted via API
            $noteText = 'Audited internal addendum for expense';
            $notedRow = $apiService->updateFinance($clinicId, $paidExpense, [
                'internal_note' => $noteText,
            ], $actorId);

            $assert(
                $notedRow === $paidBefore,
                'Internal note update preserves all expense fields exactly'
            );

            $audit = $db->table('activity_logs')
                ->where('clinic_id', $clinicId)
                ->where('entity_type', 'finance')
                ->where('entity_id', $paidExpense)
                ->where('action', 'update')
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            $assert(
                $audit !== null && str_contains((string) ($audit['metadata'] ?? ''), $noteText),
                'internal_note audited and permitted on expense'
            );

            // Direct model calls exercise the browser writer callback under a real owned transaction.
            foreach ([$paidExpense => $paidBefore, $pendingLinked => $pendingLinkedBefore] as $targetId => $before) {
                foreach ($fourFieldsTampers as $name => $tamper) {
                    if (!$db->transBegin()) { throw new RuntimeException('Unable to start model test transaction.'); }
                    try {
                        try {
                            (new FinanceModel($db))->update($targetId, $tamper);
                            throw new RuntimeException('Model accepted protected expense history mutation: ' . $name);
                        } catch (\DomainException $e) {
                            $checks[] = 'Transactional model rejects protected expense ' . $targetId . ': ' . $name;
                        }
                        $assert($before === $db->table('finances')->where('id', $targetId)->get()->getRowArray(),
                            'Rejected model edit preserves the complete expense row');
                    } finally { $db->transRollback(); }
                }
            }

            if (!$db->transBegin()) { throw new RuntimeException('Unable to start pending model transaction.'); }
            try {
                $modelEdits = ['counterparty_name' => 'Accepted Model Payee', 'due_date' => '2096-07-28',
                    'description' => 'Accepted Model Description', 'service_details' => 'Accepted Model Details'];
                if (!(new FinanceModel($db))->update($pendingUnlinked, $modelEdits)) {
                    throw new RuntimeException('Pending unlinked model update failed.');
                }
                $actual = $db->table('finances')->where('id', $pendingUnlinked)->get()->getRowArray();
                $assert(array_intersect_key($actual, $modelEdits) == $modelEdits,
                    'Pending unlinked model edits remain legitimate');
            } finally { $db->transRollback(); }

            // Legacy inconsistent totals must survive identical browser/model monetary roundtrips.
            $legacyId = $insertExpense(['payment_status' => 'paid', 'amount_paid' => 7,
                'balance_due' => 0, 'tax_amount' => 2, 'total_amount' => 7, 'paid_date' => '2096-07-02']);
            $legacy = $db->table('finances')->where('id', $legacyId)->get()->getRowArray();
            if (!$db->transBegin()) { throw new RuntimeException('Unable to start legacy roundtrip transaction.'); }
            try {
                $sameFields = array_intersect_key($legacy, array_flip(InvoiceLifecyclePolicy::EXPENSE_HISTORY_SENSITIVE_FIELDS));
                if (!(new FinanceModel($db))->update($legacyId, $sameFields)) {
                    throw new RuntimeException('Identical legacy expense model roundtrip failed.');
                }
                $after = $db->table('finances')->where('id', $legacyId)->get()->getRowArray();
                unset($legacy['updated_at'], $after['updated_at']);
                $assert($legacy === $after, 'Identical paid model roundtrip preserves legacy monetary/document basis');
            } finally { $db->transRollback(); }

            ApiRuntimeContext::setClinicId($foreignClinicId);
            if (!$db->transBegin()) { throw new RuntimeException('Unable to start foreign model transaction.'); }
            try {
                try {
                    (new FinanceModel($db))->withoutTenantScope()->update($pendingUnlinked, ['description' => 'Foreign rewrite']);
                    throw new RuntimeException('Unscoped foreign model edit accepted.');
                } catch (\DomainException $e) { $checks[] = 'Current transaction rejects foreign model expense mutation'; }
                $assert($persistedUnlinked === $db->table('finances')->where('id', $pendingUnlinked)->get()->getRowArray(),
                    'Foreign model edit preserves owned expense');
            } finally { $db->transRollback(); ApiRuntimeContext::setClinicId($clinicId); }

            // Preserve established direct-payment metadata editing while monetary history stays immutable.
            $directPayment = $insertExpense(['transaction_type' => 'payment', 'payment_status' => 'paid',
                'amount_paid' => 500, 'balance_due' => 0, 'paid_date' => '2096-07-02', 'expense_category' => null]);
            $paymentMetadata = ['counterparty_name' => 'Verified payment contact', 'due_date' => '2096-07-29',
                'description' => 'Payment administration detail', 'service_details' => 'Administrative detail', 'service_type' => 'other'];
            $updatedPayment = $apiService->updateFinance($clinicId, $directPayment, $paymentMetadata, $actorId);
            $assert(array_intersect_key($updatedPayment, $paymentMetadata) == $paymentMetadata,
                'Direct payment metadata editing remains available');
            $assert($updatedPayment === $db->table('finances')->where('id', $directPayment)->get()->getRowArray(),
                'Direct payment metadata edits persist exactly');
            try {
                $apiService->updateFinance($clinicId, $directPayment, ['amount' => 600], $actorId);
                throw new RuntimeException('Direct payment monetary mutation accepted.');
            } catch (\DomainException $e) { $checks[] = 'Direct payment monetary basis remains protected'; }
            $assert($updatedPayment === $db->table('finances')->where('id', $directPayment)->get()->getRowArray(),
                'Rejected monetary mutation preserves direct payment including metadata');

            $assert((int) $db->transDepth === 0, 'Transactions closed cleanly');
            return ['status' => 'PASS', 'checks' => $checks, 'synthetic_finance_ids' => $ids];
        } finally {
            if ((int) $db->transDepth > 0) {
                $db->transRollback();
            }
            // Retain newly owned synthetic rows and their audits as certification evidence.
            ApiRuntimeContext::clear();
            if ($priorClinic !== null) {
                ApiRuntimeContext::setClinicId($priorClinic);
            }
            if ($priorActor !== null) {
                ApiRuntimeContext::setUserId($priorActor);
            }
        }
    }
}
