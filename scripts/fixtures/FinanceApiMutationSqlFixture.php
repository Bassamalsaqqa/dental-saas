<?php

use App\Libraries\ApiRuntimeContext;
use App\Services\FinanceApiMutationService;
use App\Services\FinanceConflictException;
use App\Services\FinanceNotFoundException;
use CodeIgniter\Database\BaseConnection;

/** Caller must first validate synthetic database identity, grants, and scheduling-off guard. No DDL. */
final class FinanceApiMutationSqlFixture
{
    public static function run(BaseConnection $db, int $clinicId, int $actorId, int $patientId, int $foreignClinicId): array
    {
        if ((int) $db->transDepth !== 0) throw new RuntimeException('Requires owned transaction boundary.');
        $ids = [];
        $checks = [];
        $priorClinic = ApiRuntimeContext::getClinicId();
        $priorActor = ApiRuntimeContext::getUserId();
        ApiRuntimeContext::setClinicId($clinicId);
        ApiRuntimeContext::setUserId($actorId);
        $assert = static function (bool $condition, string $name) use (&$checks): void {
            if (!$condition) throw new RuntimeException('Assertion failed: ' . $name);
            $checks[] = $name;
        };
        $insert = static function (array $extra = []) use ($db, $clinicId, $patientId, &$ids): int {
            $row = array_replace(['clinic_id'=>$clinicId, 'patient_id'=>$patientId,
                'transaction_id'=>'CP2_API_'.bin2hex(random_bytes(8)), 'transaction_type'=>'invoice',
                'amount'=>100,'amount_original'=>100,'exchange_rate'=>1,'currency'=>'ILS',
                'discount_amount'=>10,'tax_percentage'=>10,'tax_amount'=>9,'total_amount'=>99,
                'payment_method'=>'cash','liquidity_account'=>'safe_cash','payment_status'=>'pending',
                'amount_paid'=>0,'balance_due'=>99,'transaction_date'=>'2096-06-01',
                'created_at'=>'2096-06-01 12:00:00','updated_at'=>'2096-06-01 12:00:00'], $extra);
            if (!$db->table('finances')->insert($row)) throw new RuntimeException('Fixture insert failed.');
            $ids[] = (int) $db->insertID();
            return end($ids);
        };
        try {
            $service = new FinanceApiMutationService($db);
            $unpaid = $insert();
            $row = $service->updateFinance($clinicId, $unpaid, ['amount'=>120], $actorId);
            $assert((float)$row['tax_amount']===11.0 && (float)$row['total_amount']===121.0 && (float)$row['balance_due']===121.0,'Unpaid partial basis uses persisted discount/tax and model-derived balance');
            $fx = $insert(['amount'=>350,'amount_original'=>100,'exchange_rate'=>3.5,'currency'=>'USD','discount_amount'=>0,'tax_percentage'=>0,'tax_amount'=>0,'total_amount'=>350,'balance_due'=>350]);
            $fxBefore=$db->table('finances')->where('id',$fx)->get()->getRowArray();
            try {
                $service->updateFinance($clinicId,$fx,['amount'=>350,'amount_original'=>100,'exchange_rate'=>4],$actorId);
                throw new RuntimeException('Explicit conflicting FX amount accepted.');
            } catch (\App\Services\FinanceValidationException $e) {
                $checks[]='Explicit unchanged amount remains authoritative during changed FX validation';
            }
            $assert($fxBefore===$db->table('finances')->where('id',$fx)->get()->getRowArray(),'Rejected FX change preserves entire draft');
            $fxRow=$service->updateFinance($clinicId,$fx,['currency'=>'EUR','amount_original'=>100,'exchange_rate'=>4],$actorId);
            $assert($fxRow['currency']==='EUR' && (float)$fxRow['amount_original']===100.0 && (float)$fxRow['exchange_rate']===4.0 && (float)$fxRow['amount']===400.0 && (float)$fxRow['total_amount']===400.0 && (float)$fxRow['balance_due']===400.0,'Explicit unchanged native amount retained for coherent currency and rate change');
            $preciseFx=$service->updateFinance($clinicId,$fx,['currency'=>'USD','amount_original'=>100,'exchange_rate'=>3.500001],$actorId);
            $assert($preciseFx['currency']==='USD' && (float)$preciseFx['exchange_rate']===3.500001 && (float)$preciseFx['amount_original']===100.0 && (float)$preciseFx['amount']===350.0 && (float)$preciseFx['total_amount']===350.0 && (float)$preciseFx['balance_due']===350.0,'Six-decimal FX rate preserved with two-decimal base amount');
            $paid = $insert(['payment_status'=>'paid','amount_paid'=>99,'paid_date'=>'2096-06-01','balance_due'=>7]);
            $payment = ['clinic_id'=>$clinicId,'finance_id'=>$paid,'type'=>'payment','amount'=>99,
                'payment_method'=>'cash','liquidity_account'=>'safe_cash','payment_date'=>'2096-06-01','created_by'=>$actorId,'created_at'=>'2096-06-01 12:00:00'];
            if (!$db->table('finance_payments')->insert($payment)) throw new RuntimeException('Fixture ledger insert failed.');
            $before = $db->table('finances')->where('id',$paid)->get()->getRowArray();
            $ledgerBefore = $db->table('finance_payments')->where('finance_id',$paid)->get()->getResultArray();
            foreach ([['transaction_type'=>'expense'],['amount_paid'=>0],['total_amount'=>1],['payment_status'=>'pending'],['amount'=>101],['source_entity_type'=>'contract']] as $payload) {
                try {$service->updateFinance($clinicId,$paid,$payload,$actorId); throw new RuntimeException('History mutation accepted.');}
                catch (FinanceConflictException $e) {$checks[]='History field rejected: '.key($payload);}
            }
            $row=$service->updateFinance($clinicId,$paid,['notes'=>'Allowed metadata correction'],$actorId);
            $assert((float)$row['balance_due']===7.0 && (float)$row['amount_paid']===99.0 && $row['total_amount']===$before['total_amount'],'Metadata preserves legacy paid totals and balance');
            $roundtrip=$service->updateFinance($clinicId,$paid,array_intersect_key($row,array_flip(FinanceApiMutationService::ALLOWED_PAYLOAD_FIELDS)),$actorId);
            $assert($roundtrip===$row,'Identical full-object roundtrip preserves exact history');
            $assert($ledgerBefore===$db->table('finance_payments')->where('finance_id',$paid)->get()->getResultArray(),'Payment history unchanged');
            ApiRuntimeContext::setClinicId($foreignClinicId);
            try {$service->updateFinance($foreignClinicId,$paid,['notes'=>'foreign'],$actorId); throw new RuntimeException('Foreign row accepted.');}
            catch (FinanceNotFoundException $e) {$checks[]='Foreign clinic missing';}
            ApiRuntimeContext::setClinicId($clinicId);
            $fault = new class($db) extends FinanceApiMutationService {
                protected function recordAudit(int $clinicId,int $actorId,int $id,array $existing,array $finalRow):void {throw new RuntimeException('Injected audit failure.');}
            };
            $before=$db->table('finances')->where('id',$unpaid)->get()->getRowArray();
            try {$fault->updateFinance($clinicId,$unpaid,['amount'=>130],$actorId); throw new RuntimeException('Audit failure accepted.');}
            catch (RuntimeException $e) {if($e->getMessage()!=='Injected audit failure.')throw$e;}
            $assert($before===$db->table('finances')->where('id',$unpaid)->get()->getRowArray(),'Actual model mutation rolled back on injected audit failure');
            $assert((int)$db->transDepth===0,'Transactions closed');
            return ['status'=>'PASS','checks'=>$checks];
        } finally {
            if ((int)$db->transDepth>0) $db->transRollback();
            // Only the IDs inserted above; never a database reset or historical row rewrite.
            if($ids!==[]) {
                $db->table('activity_logs')->where('clinic_id',$clinicId)->where('entity_type','finance')->whereIn('entity_id',$ids)->delete();
                $db->table('finance_payments')->where('clinic_id',$clinicId)->whereIn('finance_id',$ids)->delete();
                $db->table('finances')->where('clinic_id',$clinicId)->whereIn('id',$ids)->delete();
            }
            ApiRuntimeContext::clear();
            if($priorClinic!==null)ApiRuntimeContext::setClinicId($priorClinic);
            if($priorActor!==null)ApiRuntimeContext::setUserId($priorActor);
        }
    }
}
