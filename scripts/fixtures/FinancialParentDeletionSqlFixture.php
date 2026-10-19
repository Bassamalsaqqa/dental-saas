<?php

declare(strict_types=1);

use App\Libraries\ApiRuntimeContext;
use App\Services\FinancialParentDeletionGuard;
use App\Services\FinancialParentDeletionException;
use CodeIgniter\Database\BaseConnection;

/** Reviewer-owned fixture. New synthetic rows only; no schema changes or cleanup of prior data. */
final class FinancialParentDeletionSqlFixture
{
    public static function run(BaseConnection $db, int $actorId = 1): array
    {
        if (PHP_SAPI !== 'cli' || $db->database !== 'u556956644_dentstage1017'
            || $db->username !== 'u556956644_dstage1017' || (int) $db->transDepth !== 0) {
            throw new RuntimeException('Owned synthetic staging connection required.');
        }
        $marker = $db->query('SELECT guid, domain, synthetic_only FROM _denta_stage_identity WHERE id=1')->getRowArray();
        if (($marker['guid'] ?? '') !== 'b60809432ec1d915b0b975aef8484e43'
            || ($marker['domain'] ?? '') !== 'dentastaging.palsync.net' || (int) ($marker['synthetic_only'] ?? 0) !== 1) {
            throw new RuntimeException('Synthetic identity mismatch.');
        }
        $checks = []; $owned = []; $run = 'S0D_' . bin2hex(random_bytes(5)); $sequence = 0;
        $assert = static function (bool $ok, string $name) use (&$checks): void {
            if (!$ok) throw new RuntimeException('Assertion failed: ' . $name);
            $checks[] = $name;
        };
        $insert = static function (string $table, array $row) use ($db, &$owned): int {
            if (!$db->table($table)->insert($row)) throw new RuntimeException('Synthetic insert failed: ' . $table);
            $id = (int) $db->insertID(); $owned[$table][] = $id; return $id;
        };
        $clinic = static function () use ($insert, $run, &$sequence): int {
            $name = $run . '_' . ++$sequence; $uuid = bin2hex(random_bytes(16));
            return $insert('clinics', ['name' => $name, 'slug' => strtolower($name),
                'uuid' => substr($uuid,0,8).'-'.substr($uuid,8,4).'-'.substr($uuid,12,4).'-'.substr($uuid,16,4).'-'.substr($uuid,20), 'status' => 'active']);
        };
        $patient = static function (int $cid) use ($insert, $run, &$sequence): int {
            return $insert('patients', ['clinic_id'=>$cid,'patient_id'=>$run.'_'.++$sequence,
                'first_name'=>'Synthetic','last_name'=>'FinancialGuard','phone'=>'0000000000','date_of_birth'=>'1990-01-01','gender'=>'other']);
        };
        $exam = static function (int $cid, int $pid) use ($insert, $run, &$sequence): int {
            return $insert('examinations',['clinic_id'=>$cid,'patient_id'=>$pid,'examination_id'=>$run.'_'.++$sequence,
                'examination_date'=>'2095-08-01','examination_type'=>'initial','chief_complaint'=>'Synthetic guard fixture']);
        };
        $invoice = static function (int $cid, int $pid, ?int $eid) use ($insert, $run, &$sequence, $actorId): int {
            return $insert('finances',['clinic_id'=>$cid,'patient_id'=>$pid,'examination_id'=>$eid,
                'transaction_id'=>$run.'_'.++$sequence,'transaction_type'=>'invoice','amount'=>100,'amount_original'=>100,
                'exchange_rate'=>1,'currency'=>'ILS','discount_amount'=>0,'tax_percentage'=>0,'tax_amount'=>0,'total_amount'=>100,
                'payment_method'=>'cash','liquidity_account'=>'safe_cash','payment_status'=>'pending','amount_paid'=>0,'balance_due'=>100,
                'service_type'=>'other','transaction_date'=>'2095-08-01','created_by'=>$actorId]);
        };
        $row = static fn(string $table, int $id): ?array => $db->table($table)->where('id',$id)->get()->getRowArray();
        $rejected = static function (callable $operation, string $name) use ($assert, $db): void {
            $blocked = false;
            try { $operation(); } catch (FinancialParentDeletionException $error) { $blocked = true; }
            $assert($blocked, $name);
            $assert((int)$db->transDepth===0 && $db->transStatus()!==false, $name.' leaves healthy transaction boundary');
        };
        $guard = new FinancialParentDeletionGuard($db);
        $priorClinic = ApiRuntimeContext::getClinicId(); $priorActor = ApiRuntimeContext::getUserId();
        try {
            $cid=$clinic(); $pid=$patient($cid); $eid=$exam($cid,$pid); $fid=$invoice($cid,$pid,$eid);
            ApiRuntimeContext::setClinicId($cid); ApiRuntimeContext::setUserId($actorId);
            $payid=$insert('finance_payments',['clinic_id'=>$cid,'finance_id'=>$fid,'amount'=>10,'type'=>'payment',
                'payment_method'=>'cash','liquidity_account'=>'safe_cash','payment_date'=>'2095-08-01','created_by'=>$actorId]);
            $before=['clinic'=>$row('clinics',$cid),'patient'=>$row('patients',$pid),'exam'=>$row('examinations',$eid),
                'finance'=>$row('finances',$fid),'payment'=>$row('finance_payments',$payid)];
            foreach (['clinic','patient','examination'] as $kind) {
                $called=false;
                $callback=static function () use (&$called): bool {$called=true; return true;};
                $rejected(static function () use ($guard,$kind,$cid,$pid,$eid,$callback): void {
                    if($kind==='clinic')$guard->deleteClinic($cid,$callback);
                    elseif($kind==='patient')$guard->deletePatient($cid,$pid,$callback);
                    else$guard->deleteExamination($cid,$eid,$callback);
                },$kind.' with invoice blocks physical deletion');
                $assert(!$called,$kind.' rejection occurs before deletion callback');
            }
            $after=['clinic'=>$row('clinics',$cid),'patient'=>$row('patients',$pid),'exam'=>$row('examinations',$eid),
                'finance'=>$row('finances',$fid),'payment'=>$row('finance_payments',$payid)];
            $assert($before===$after,'Rejected deletions preserve parent, invoice and payment history exactly');
            $indirect=$exam($cid,$pid); $combined=$invoice($cid,$pid,null);
            $charge=$insert('clinical_charge_lines',['clinic_id'=>$cid,'patient_id'=>$pid,'examination_id'=>$indirect,
                'finance_id'=>$combined,'source_entity_type'=>'manual','procedure_name'=>'Synthetic guard billed procedure',
                'quantity'=>1,'unit_price'=>100,'gross_amount'=>100,'net_production_amount'=>100,'currency'=>'ILS','charge_date'=>'2095-08-01','status'=>'invoiced']);
            $chargeBefore=$row('clinical_charge_lines',$charge);
            $rejected(static fn()=>$guard->deleteExamination($cid,$indirect,static fn()=>true),'Indirect multi-examination billed charge blocks deletion');
            $assert($row('finances',$combined)['examination_id']===null && $row('clinical_charge_lines',$charge)===$chargeBefore
                && $row('examinations',$indirect)!==null,'Indirect finance identity and charge history remain unchanged');
            $foreign=$clinic(); $foreignPatient=$patient($foreign);
            $rejected(static fn()=>$guard->deletePatient($cid,$foreignPatient,static fn()=>true),'Foreign-clinic patient rejected');
            $assert($row('patients',$foreignPatient)!==null,'Foreign patient preserved');
            if(!$db->transBegin())throw new RuntimeException('Caller transaction failed.');
            try {
                try {$guard->deleteClinic($cid,static fn()=>true);throw new LogicException('Nested caller accepted.');}
                catch(RuntimeException $error){if($error instanceof LogicException)throw$error;}
                $assert((int)$db->transDepth===1 && $db->transStatus()!==false,'Nested guard rejection preserves caller transaction');
            } finally {$db->transRollback();}
            $empty=$patient($cid);$emptyBefore=$row('patients',$empty);
            try {$guard->deletePatient($cid,$empty,static function () use($db,$empty): bool {
                if(!$db->table('patients')->where('id',$empty)->update(['first_name'=>'SHOULD_ROLL_BACK']))throw new RuntimeException('Fixture mutation failed.');
                return false;
            });throw new LogicException('False callback accepted.');}
            catch(RuntimeException $error){if($error instanceof LogicException)throw$error;}
            $assert($row('patients',$empty)===$emptyBefore && (int)$db->transDepth===0,'False callback rolls back new-owned parent mutation');
            $auditCid=$clinic();$auditPid=$patient($auditCid);$auditParentBefore=$row('patients',$auditPid);
            $auditCount=(int)$db->table('activity_logs')->where('clinic_id',$auditCid)->countAllResults();
            try {$guard->deletePatient($auditCid,$auditPid,static function () use($db,$auditPid,$auditCid,$actorId): bool {
                if(!$db->table('patients')->where('id',$auditPid)->delete())throw new RuntimeException('Fixture deletion failed.');
                if(!$db->table('activity_logs')->insert(['clinic_id'=>$auditCid,'user_id'=>$actorId,'action'=>'delete','entity_type'=>'patient','entity_id'=>$auditPid,'description'=>'Synthetic rollback audit']))throw new RuntimeException('Fixture audit insert failed.');
                throw new RuntimeException('Injected audit failure.');
            });throw new LogicException('Audit fault accepted.');}
            catch(RuntimeException $error){if($error->getMessage()!=='Injected audit failure.')throw$error;}
            $assert($row('patients',$auditPid)===$auditParentBefore && (int)$db->table('activity_logs')->where('clinic_id',$auditCid)->countAllResults()===$auditCount,
                'Audit fault rolls back new-owned parent deletion and audit insert');
            $orphanCid=$clinic();
            $aid=$insert('activity_logs',['clinic_id'=>$orphanCid,'user_id'=>$actorId,'action'=>'delete','entity_type'=>'finance','entity_id'=>2147480000,'description'=>'Synthetic orphan finance audit']);
            $auditBefore=$row('activity_logs',$aid);
            $rejected(static fn()=>$guard->deleteClinic($orphanCid,static fn()=>true),'Retained orphan finance audit blocks clinic deletion');
            $assert($row('clinics',$orphanCid)!==null && $row('activity_logs',$aid)===$auditBefore,'Orphan finance audit and clinic preserved exactly');
            $guard->deletePatient($cid,$empty,static function () use($db,$empty): bool {
                return $db->table('patients')->where('id',$empty)->delete() && $db->affectedRows()===1;
            });
            $assert($row('patients',$empty)===null,'New patient without finance history may be physically deleted');
            $cleanExam=$exam($cid,$pid);
            $guard->deleteExamination($cid,$cleanExam,static function () use($db,$cleanExam): bool {
                return $db->table('examinations')->where('id',$cleanExam)->delete() && $db->affectedRows()===1;
            });
            $assert($row('examinations',$cleanExam)===null,'New examination without finance history may be physically deleted');
            $emptyClinic=$clinic();
            $guard->deleteClinic($emptyClinic,static function () use($db,$emptyClinic): bool {
                return $db->table('clinics')->where('id',$emptyClinic)->delete() && $db->affectedRows()===1;
            });
            $assert($row('clinics',$emptyClinic)===null,'New empty clinic may be physically deleted');
            $assert((int)$db->transDepth===0 && $db->transStatus()!==false,'All scenarios finish with healthy owned boundary');
            return ['status'=>'PASS','run'=>$run,'checks'=>$checks,'retained_new_fixture_ids'=>$owned,'existing_data_touched'=>false];
        } finally {
            ApiRuntimeContext::clear();
            if ($priorClinic !== null) { ApiRuntimeContext::setClinicId($priorClinic); }
            if ($priorActor !== null) { ApiRuntimeContext::setUserId($priorActor); }
        }
    }
}
