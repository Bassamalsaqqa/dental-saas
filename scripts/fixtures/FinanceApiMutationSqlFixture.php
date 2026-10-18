<?php

use App\Libraries\ApiRuntimeContext;
use App\Services\FinanceApiMutationService;
use App\Services\FinanceConflictException;
use App\Services\FinanceMutationService;
use App\Services\FinanceNotFoundException;
use App\Services\InvoiceLifecyclePolicy;
use CodeIgniter\Database\BaseConnection;

/** Caller must first validate synthetic database identity, grants, and scheduling-off guard. No DDL. */
final class FinanceApiMutationSqlFixture
{
    public static function run(BaseConnection $db, int $clinicId, int $actorId, int $patientId, int $foreignClinicId): array
    {
        if ((int) $db->transDepth !== 0) {
            throw new RuntimeException('Requires owned transaction boundary.');
        }

        $ids = [];
        $notificationIds = [];
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

        $insert = static function (array $extra = []) use ($db, $clinicId, $patientId, &$ids): int {
            $row = array_replace([
                'clinic_id' => $clinicId,
                'patient_id' => $patientId,
                'transaction_id' => 'CP2_API_' . bin2hex(random_bytes(8)),
                'transaction_type' => 'invoice',
                'amount' => 100,
                'amount_original' => 100,
                'exchange_rate' => 1,
                'currency' => 'ILS',
                'discount_amount' => 10,
                'tax_percentage' => 10,
                'tax_amount' => 9,
                'total_amount' => 99,
                'payment_method' => 'cash',
                'liquidity_account' => 'safe_cash',
                'payment_status' => 'pending',
                'amount_paid' => 0,
                'balance_due' => 99,
                'service_type' => 'treatment',
                'description' => 'Synthetic dental procedure',
                'service_details' => 'Root canal therapy',
                'due_date' => '2096-06-15',
                'transaction_date' => '2096-06-01',
                'notes' => 'Original invoice notes',
                'created_at' => '2096-06-01 12:00:00',
                'updated_at' => '2096-06-01 12:00:00',
            ], $extra);
            if (!$db->table('finances')->insert($row)) {
                throw new RuntimeException('Fixture insert failed.');
            }
            $ids[] = (int) $db->insertID();
            return end($ids);
        };

        try {
            $apiService = new FinanceApiMutationService($db);
            $mutationService = new FinanceMutationService($db);

            // 1. Legacy-unknown pending invoice basis immutability
            $unpaidPending = $insert(['payment_status' => 'pending']);
            $pendingBefore = $db->table('finances')->where('id', $unpaidPending)->get()->getRowArray();

            foreach ([
                ['amount' => 120],
                ['discount_amount' => 20],
                ['tax_percentage' => 15],
                ['currency' => 'EUR', 'amount_original' => 100, 'exchange_rate' => 4],
                ['exchange_rate' => 3.5],
                ['patient_id' => $patientId + 1000],
                ['payment_method' => 'card'],
                ['service_type' => 'consultation'],
                ['notes' => 'Changed printed notes'],
                ['description' => 'Altered service line'],
                ['service_details' => 'Altered procedure details'],
                ['due_date' => '2096-06-30'],
                ['transaction_date' => '2096-06-10'],
            ] as $tamper) {
                try {
                    $apiService->updateFinance($clinicId, $unpaidPending, $tamper, $actorId);
                    throw new RuntimeException('Basis mutation accepted on pending invoice: ' . key($tamper));
                } catch (\DomainException $e) {
                    $checks[] = 'Pending invoice protected from: ' . key($tamper);
                }
            }
            $assert($pendingBefore === $db->table('finances')->where('id', $unpaidPending)->get()->getRowArray(),
                'Rejected mutations preserve entire pending invoice untouched');

            // 2. Overdue invoice basis immutability
            $unpaidOverdue = $insert(['payment_status' => 'overdue', 'due_date' => '2096-05-01']);
            $overdueBefore = $db->table('finances')->where('id', $unpaidOverdue)->get()->getRowArray();
            try {
                $apiService->updateFinance($clinicId, $unpaidOverdue, ['amount' => 150], $actorId);
                throw new RuntimeException('Basis mutation accepted on overdue invoice');
            } catch (\DomainException $e) {
                $checks[] = 'Overdue invoice basis mutation rejected';
            }
            $assert($overdueBefore === $db->table('finances')->where('id', $unpaidOverdue)->get()->getRowArray(),
                'Overdue invoice preserved untouched');

            // 3. Issued unpaid invoice with sent reminder: immutable and cannot delete
            $issuedWithReminder = $insert(['payment_status' => 'pending']);
            $reminderId = null;
            if ($db->tableExists('notifications')) {
                $notifData = [
                    'clinic_id' => $clinicId,
                    'channel_type' => 'whatsapp',
                    'recipient_type' => 'patient',
                    'recipient_id' => $patientId,
                    'payload_json' => json_encode(['workflow_key' => 'payment_reminder:' . $issuedWithReminder . ':test']),
                    'status' => 'sent',
                    'created_at' => '2096-06-01 12:00:00',
                ];
                if ($db->table('notifications')->insert($notifData)) {
                    $reminderId = (int) $db->insertID();
                    $notificationIds[] = $reminderId;
                }
            }
            try {
                $apiService->updateFinance($clinicId, $issuedWithReminder, ['amount' => 200], $actorId);
                throw new RuntimeException('Basis mutation accepted on issued invoice with reminder');
            } catch (\DomainException $e) {
                $checks[] = 'Issued invoice with reminder basis mutation rejected';
            }
            try {
                $mutationService->deleteUnused($clinicId, [$issuedWithReminder], $actorId);
                throw new RuntimeException('Deletion accepted on issued invoice');
            } catch (\DomainException $e) {
                $checks[] = 'Deletion blocked on persisted invoice with reminder';
            }
            if ($reminderId !== null) {
                $reminderRow = $db->table('notifications')->where('id', $reminderId)->get()->getRowArray();
                $assert(($reminderRow['status'] ?? '') === 'sent', 'Reminder history preserved without changes upon blocked deletion');
            }

            // 4. Deletion blocked on all persisted invoices (pending or overdue) without reminder changes
            try {
                $mutationService->deleteUnused($clinicId, [$unpaidPending], $actorId);
                throw new RuntimeException('Deletion accepted on pending invoice');
            } catch (\DomainException $e) {
                $checks[] = 'Deletion blocked on pending invoice with controlled message';
            }
            $assert($db->table('finances')->where('id', $unpaidPending)->get()->getRowArray() !== null,
                'Pending invoice preserved after rejected deletion');

            foreach ([false, true] as $whereOnly) {
                $direct = new \App\Models\FinanceModel($db);
                try {
                    if ($whereOnly) { $direct->where('id',$unpaidPending)->delete(); }
                    else { $direct->delete($unpaidPending); }
                    throw new RuntimeException('Unaudited model deletion accepted.');
                } catch (\DomainException $e) { $checks[]=$whereOnly ? 'Where-only model deletion rejected' : 'Explicit model invoice deletion rejected'; }
                $assert($pendingBefore===$db->table('finances')->where('id',$unpaidPending)->get()->getRowArray(),'Model deletion preserves entire invoice');
            }

            ApiRuntimeContext::setClinicId($foreignClinicId);
            $foreignModel = (new \App\Models\FinanceModel($db))->withoutTenantScope();
            try { $foreignModel->update($unpaidPending,['description'=>'Cross-clinic document rewrite']); throw new RuntimeException('Unscoped foreign invoice update accepted.'); }
            catch (\DomainException $e) { $checks[]='Unscoped model rejects foreign invoice document mutation'; }
            finally { ApiRuntimeContext::setClinicId($clinicId); }
            $assert($pendingBefore===$db->table('finances')->where('id',$unpaidPending)->get()->getRowArray(),'Unscoped model preserves foreign invoice');

            // 5. Safe internal notes update + atomic audit
            $notesRow = $apiService->updateFinance($clinicId, $unpaidPending, ['internal_note' => 'Updated internal collection note'], $actorId);
            $assert($notesRow === $pendingBefore, 'Audit-only internal note preserves entire issued invoice including printed notes');
            $assert((float) $notesRow['amount'] === 100.0 && (float) $notesRow['total_amount'] === 99.0 && (float) $notesRow['balance_due'] === 99.0,
                'Notes update preserves monetary totals and balance exactly');

            $audit = $db->table('activity_logs')->where('clinic_id', $clinicId)->where('entity_type', 'finance')
                ->where('entity_id', $unpaidPending)->where('action', 'update')->get()->getRowArray();
            $assert($audit !== null && str_contains($audit['metadata'], 'Updated internal collection note'),
                'Notes update recorded in atomic activity audit');

            // 6. Atomic audit failure rollback
            $fault = new class($db) extends FinanceApiMutationService {
                protected function recordAudit(int $clinicId, int $actorId, int $id, array $existing, array $finalRow): void {
                    throw new RuntimeException('Injected audit failure.');
                }
            };
            $beforeFault = $db->table('finances')->where('id', $unpaidPending)->get()->getRowArray();
            try {
                $fault->updateFinance($clinicId, $unpaidPending, ['internal_note' => 'Tampered notes without audit'], $actorId);
                throw new RuntimeException('Audit failure accepted.');
            } catch (RuntimeException $e) {
                if ($e->getMessage() !== 'Injected audit failure.') throw $e;
                $checks[] = 'Atomic rollback verified on audit log failure';
            }
            $assert($beforeFault === $db->table('finances')->where('id', $unpaidPending)->get()->getRowArray(),
                'Invoice row rolled back cleanly on audit failure');

            // 7. Identical full-object roundtrip preservation
            $roundtrip = $apiService->updateFinance($clinicId, $unpaidPending,
                array_intersect_key($notesRow, array_flip(FinanceApiMutationService::ALLOWED_PAYLOAD_FIELDS)), $actorId);
            $assert($roundtrip === $notesRow, 'Identical full-object roundtrip preserves exact invoice state');

            // 8. Expense compatibility: unlinked pending expense can update basis and can be deleted
            $expenseId = $insert([
                'transaction_type' => 'expense',
                'payment_status' => 'pending',
                'amount' => 200,
                'amount_original' => 200,
                'exchange_rate' => 1,
                'currency' => 'ILS',
                'discount_amount' => 0,
                'tax_percentage' => 0,
                'tax_amount' => 0,
                'total_amount' => 200,
                'amount_paid' => 0,
                'balance_due' => 200,
                'patient_id' => null,
                'counterparty_name' => 'Original Supplier',
                'expense_category' => 'supplies',
                'description' => 'Medical gloves',
            ]);

            try { (new \App\Models\FinanceModel($db))->update($expenseId,['transaction_type'=>'invoice']); throw new RuntimeException('Expense reclassification bypassed creation identity locks.'); }
            catch (\DomainException $e) { $checks[]='Existing expense cannot become invoice through model update'; }

            $updatedExpense = $apiService->updateFinance($clinicId, $expenseId, [
                'amount' => 250,
                'expense_category' => 'equipment',
                'counterparty_name' => 'Updated Supplier',
            ], $actorId);
            $assert((float) $updatedExpense['amount'] === 250.0 && $updatedExpense['expense_category'] === 'equipment',
                'Unlinked pending expense basis and category update preserved');

            $expenseBeforeFault=$db->table('finances')->where('id',$expenseId)->get()->getRowArray();
            try { $fault->updateFinance($clinicId,$expenseId,['amount'=>260],$actorId); throw new RuntimeException('Expense audit failure accepted.'); }
            catch (RuntimeException $e) { if($e->getMessage()!=='Injected audit failure.')throw$e; }
            $assert($expenseBeforeFault===$db->table('finances')->where('id',$expenseId)->get()->getRowArray(),'Actual expense basis and balance mutation rolls back on audit failure');

            $fx=$apiService->updateFinance($clinicId,$expenseId,['currency'=>'USD','amount'=>350,'amount_original'=>100,'exchange_rate'=>3.5],$actorId);
            $fxBefore=$fx;
            try { $apiService->updateFinance($clinicId,$expenseId,['amount'=>350,'amount_original'=>100,'exchange_rate'=>4],$actorId); throw new RuntimeException('Explicit FX conflict accepted.'); }
            catch (\InvalidArgumentException $e) { $checks[]='Expense explicit FX conflict rejected'; }
            $assert($fxBefore===$db->table('finances')->where('id',$expenseId)->get()->getRowArray(),'Rejected FX conflict preserves expense');
            $fx=$apiService->updateFinance($clinicId,$expenseId,['currency'=>'EUR','amount_original'=>100,'exchange_rate'=>4],$actorId);
            $assert((float)$fx['amount']===400.0 && $fx['currency']==='EUR','Coherent expense currency change preserves explicit native amount');
            $fx=$apiService->updateFinance($clinicId,$expenseId,['currency'=>'USD','amount_original'=>100,'exchange_rate'=>3.500001],$actorId);
            $assert((float)$fx['amount']===350.0 && (float)$fx['exchange_rate']===3.500001,'Six-decimal expense FX compatibility retained');

            $legacy=$insert(['tax_amount'=>2,'total_amount'=>7,'balance_due'=>7]);
            $db->transBegin();
            try {
                $model=new \App\Models\FinanceModel($db);
                if(!$model->update($legacy,['amount'=>100,'discount_amount'=>10,'tax_percentage'=>10,'tax_amount'=>2,'total_amount'=>7]))throw new RuntimeException('Same-value model roundtrip failed.');
                $same=$db->table('finances')->where('id',$legacy)->get()->getRowArray();
                $assert((float)$same['tax_amount']===2.0 && (float)$same['total_amount']===7.0 && (float)$same['balance_due']===7.0,'Same-value model callback preserves inconsistent legacy totals exactly');
            } finally { $db->transRollback(); }

            $deletedExpenseCount = $mutationService->deleteUnused($clinicId, [$expenseId], $actorId);
            $assert($deletedExpenseCount === 1 && $db->table('finances')->where('id', $expenseId)->get()->getRowArray() === null,
                'Unlinked pending expense deletion preserved with history safeguards');

            // 9. Paid expense / invoice with history cannot be deleted
            $paidInvoice = $insert([
                'payment_status' => 'paid',
                'amount_paid' => 99,
                'paid_date' => '2096-06-01',
                'balance_due' => 0,
            ]);
            $paymentRow = [
                'clinic_id' => $clinicId,
                'finance_id' => $paidInvoice,
                'type' => 'payment',
                'amount' => 99,
                'payment_method' => 'cash',
                'liquidity_account' => 'safe_cash',
                'payment_date' => '2096-06-01',
                'created_by' => $actorId,
                'created_at' => '2096-06-01 12:00:00',
            ];
            if (!$db->table('finance_payments')->insert($paymentRow)) {
                throw new RuntimeException('Fixture payment insert failed.');
            }
            try {
                $mutationService->deleteUnused($clinicId, [$paidInvoice], $actorId);
                throw new RuntimeException('Paid invoice deletion accepted');
            } catch (\DomainException $e) {
                $checks[] = 'Paid invoice deletion rejected';
            }

            // 10. Tenant isolation
            ApiRuntimeContext::setClinicId($foreignClinicId);
            try {
                $apiService->updateFinance($foreignClinicId, $unpaidPending, ['notes' => 'cross-tenant'], $actorId);
                throw new RuntimeException('Foreign clinic row update accepted.');
            } catch (FinanceNotFoundException $e) {
                $checks[] = 'Foreign clinic row rejected fail-closed';
            }
            ApiRuntimeContext::setClinicId($clinicId);

            $assert((int) $db->transDepth === 0, 'Transactions closed');
            return ['status' => 'PASS', 'checks' => $checks];
        } finally {
            if ((int) $db->transDepth > 0) {
                $db->transRollback();
            }
            if ($ids !== []) {
                if ($db->tableExists('notifications')) {
                    if ($notificationIds !== []) { $db->table('notifications')->where('clinic_id', $clinicId)->whereIn('id', $notificationIds)->delete(); }
                }
                $db->table('activity_logs')->where('clinic_id', $clinicId)->where('entity_type', 'finance')->whereIn('entity_id', $ids)->delete();
                $db->table('finance_payments')->where('clinic_id', $clinicId)->whereIn('finance_id', $ids)->delete();
                $db->table('finances')->where('clinic_id', $clinicId)->whereIn('id', $ids)->delete();
            }
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
