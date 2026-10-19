<?php
declare(strict_types=1);
/** Reviewer-owned focused creator compatibility; actual controllers, no routed-auth claim. */
if (PHP_SAPI !== 'cli') { exit(2); }
$bootstrap = $argv[1] ?? '';
$privateExpected = '/home/u556956644/.denta-stage-20261017';
if (!is_string($bootstrap) || !str_starts_with($bootstrap, $privateExpected . '/') || !is_file($bootstrap)
    || is_link($bootstrap) || realpath($bootstrap) !== $bootstrap) { throw new RuntimeException('Canonical private bootstrap required.'); }
require $bootstrap;
if ($private !== $privateExpected || realpath($private) !== $private || !defined('HOMEPATH')
    || !str_starts_with((string) realpath(HOMEPATH), $private . '/remediation-20261018-')
    || $db->database !== 'u556956644_dentstage1017' || $db->username !== 'u556956644_dstage1017'
    || $db->hostname !== '127.0.0.1' || (int) $db->port !== 3306 || $db->getPrefix() !== ''
    || (int) $db->transDepth !== 0 || !$db->transStatus()) { throw new RuntimeException('Private synthetic connection rejected.'); }
$identity = $db->query('SELECT guid,domain,synthetic_only FROM _denta_stage_identity WHERE id=1')->getRowArray();
if (($identity['guid'] ?? '') !== 'b60809432ec1d915b0b975aef8484e43'
    || ($identity['domain'] ?? '') !== 'dentastaging.palsync.net' || (int) ($identity['synthetic_only'] ?? 0) !== 1) { throw new RuntimeException('Synthetic identity rejected.'); }
foreach ($db->query('SHOW GRANTS')->getResultArray() as $row) {
    $grant = str_replace('\\_', '_', (string) reset($row));
    if (str_contains($grant, 'WITH GRANT OPTION') || (str_contains($grant, ' ON *.* ') && !str_starts_with($grant, 'GRANT USAGE ON *.* '))
        || (preg_match('/ ON `([^`]+)`\./', $grant, $match) && $match[1] !== $db->database)) { throw new RuntimeException('Excessive synthetic grants.'); }
}
helper(['url','form']);
$db->query('SET SESSION innodb_lock_wait_timeout=5');
session()->set(['active_clinic_id'=>1,'user_id'=>1,'global_mode'=>false]);
$run='CFIN_'.bin2hex(random_bytes(5)); $checks=[]; $created=[];
$check=static function(bool $ok,string $label)use(&$checks):void{if(!$ok)throw new RuntimeException($label);$checks[]=$label;};
$api=static function(array $payload)use($db):App\Controllers\Api\Finance{
    App\Libraries\ApiRuntimeContext::setClinicId(1);App\Libraries\ApiRuntimeContext::setUserId(1);
    $request=new CodeIgniter\HTTP\IncomingRequest(config('App'),Config\Services::uri(null,false),json_encode($payload,JSON_THROW_ON_ERROR),new CodeIgniter\HTTP\UserAgent());
    $request->setHeader('Content-Type','application/json');Config\Services::injectMock('request',$request);
    $r=new ReflectionClass(App\Controllers\Api\Finance::class);$controller=$r->newInstanceWithoutConstructor();
    foreach(['model'=>new App\Models\FinanceModel($db),'request'=>$request,'response'=>Config\Services::response(null,false)]as$key=>$value)$r->getProperty($key)->setValue($controller,$value);
    return $controller;
};
$row=static fn(int $id):array=>$db->table('finances')->where('id',$id)->where('clinic_id',1)->get()->getRowArray()??[];
try{
    $patients=[];
    foreach([1,2]as$clinic){$p=$db->table('patients')->select('id')->where('clinic_id',$clinic)->like('patient_id','STG_','after')->get()->getRowArray();if(!$p)throw new RuntimeException('Owned synthetic patients required in clinics 1 and 2.');$patients[$clinic]=(int)$p['id'];}
    $attempts=(int)$db->table('notification_attempts')->countAllResults();$notifications=(int)$db->table('notifications')->countAllResults();
    $base=['patient_id'=>$patients[1],'amount'=>37,'amount_original'=>37,'currency'=>'ILS','exchange_rate'=>1,'payment_method'=>'cash','service_type'=>'other','description'=>$run,'created_by'=>2147483000];
    foreach(['payment','refund','expense']as$type){
        $payload=array_replace($base,['transaction_type'=>$type,'payment_status'=>$type==='expense'?'pending':'paid']);
        $response=$api($payload)->create();$body=json_decode($response->getBody(),true,512,JSON_THROW_ON_ERROR);
        $id=(int)($body['id']??0);if($id>0)$created[]=$id;
        $check($response->getStatusCode()===201&&$id>0,'Actual API accepts patient-linked '.$type);
        $persisted=$row($id);$check((int)$persisted['patient_id']===$patients[1]&&(int)$persisted['clinic_id']===1&&$persisted['transaction_type']===$type,'Persisted '.$type.' retains validated tenant/parent/type');
        $check((int)$persisted['created_by']===1,'API '.$type.' uses server actor rather than payload actor');
        $check((int)$db->transDepth===0&&$db->transStatus(),'API '.$type.' completes its owned healthy transaction');
    }
    // A new, explicitly owned foreign examination makes the ownership check non-vacuous.
    $exam=['clinic_id'=>2,'patient_id'=>$patients[2],'examination_id'=>substr($run.'E',0,20),'examination_date'=>'2097-06-01','examination_type'=>'initial','chief_complaint'=>$run,'status'=>'pending','created_by'=>1];
    if(!$db->table('examinations')->insert($exam))throw new RuntimeException('Owned foreign examination fixture insert failed.');
    $foreignExam=(int)$db->insertID();
    foreach(['payment','refund','expense']as$type){
        foreach(['foreign-patient'=>['patient_id'=>$patients[2]],'foreign-exam'=>['examination_id'=>$foreignExam]]as$case=>$override){
            $count=(int)$db->table('finances')->countAllResults();
            $response=$api(array_replace($base,['transaction_type'=>$type,'payment_status'=>'pending'],$override))->create();
            $json=json_decode($response->getBody(),true,512,JSON_THROW_ON_ERROR);
            $check($response->getStatusCode()>=400&&$response->getStatusCode()<500&&is_array($json),'Actual API rejects '.$type.' '.$case.' with JSON 4xx');
            $check((int)$db->table('finances')->countAllResults()===$count&&(int)$db->transDepth===0&&$db->transStatus(),'Rejected '.$type.' '.$case.' inserts nothing and leaves no transaction');
        }
    }
    $count=(int)$db->table('finances')->countAllResults();
    if(!$db->transBegin())throw new RuntimeException('Unable to start caller transaction.');
    $response=$api(array_replace($base,['transaction_type'=>'payment']))->create();
    $check($response->getStatusCode()>=400&&(int)$db->transDepth===1&&$db->transStatus(),'Nested API creation rejects without committing/rolling back caller transaction');
    $check((int)$db->table('finances')->countAllResults()===$count,'Nested API creation inserts no finance');
    $db->transRollback();
    $ledger=new App\Services\FinanceLedgerService();
    $ledgerReflection=new ReflectionClass($ledger);$ledgerModel=$ledgerReflection->getProperty('financeModel')->getValue($ledger);
    if($ledgerModel->db!==$db)throw new RuntimeException('Ledger did not use the guarded synthetic connection.');
    // No source FK exists by inspected schema; ownership prefix plus collision precheck protects source identity.
    $sourceId=random_int(1500000000,1999999999);
    if($db->table('finances')->where('clinic_id',1)->where('source_entity_type','inventory_usage')->where('source_entity_id',$sourceId)->countAllResults()!==0)throw new RuntimeException('Source collision; no fixture written.');
    $usage=['usage_date'=>'2097-06-02','total_cost'=>13.25,'items_used'=>'[]','notes'=>$run];
    $id=(int)$ledger->upsertInventoryUsageCogsExpense(1,$sourceId,$usage,$patients[1],1,['patient_name'=>$run]);
    if($id>0)$created[]=$id;$before=$row($id);
    $check($id>0&&$before['expense_category']==='inventory_cogs'&&(int)$before['patient_id']===$patients[1],'COGS service creates patient-linked finance in its owned transaction');
    $again=$ledger->upsertInventoryUsageCogsExpense(1,$sourceId,$usage,$patients[1],1,['patient_name'=>$run]);
    $check((int)$again===$id&&$row($id)===$before,'Identical COGS repost returns same ID without rewriting existing history');
    $check((int)$db->table('finances')->where('clinic_id',1)->where('source_entity_type','inventory_usage')->where('source_entity_id',$sourceId)->countAllResults()===1,'COGS idempotency retains exactly one row');
    $rejected=false;try{$ledger->upsertInventoryUsageCogsExpense(1,$sourceId,array_replace($usage,['total_cost'=>14.25]),$patients[1],1,['patient_name'=>$run]);}catch(DomainException|RuntimeException $e){$rejected=true;}
    $check($rejected&&$row($id)===$before,'Conflicting COGS repost fails closed and preserves posted history');
    $check((int)$db->transDepth===0&&$db->transStatus(),'Owned COGS operations leave healthy connection outside transaction');
    $outerSource=$sourceId+1;
    if($db->table('finances')->where('clinic_id',1)->where('source_entity_type','inventory_usage')->where('source_entity_id',$outerSource)->countAllResults()!==0)throw new RuntimeException('Outer source collision.');
    if(!$db->transBegin())throw new RuntimeException('Unable to start healthy caller transaction.');
    $outerId=(int)$ledger->upsertInventoryUsageCogsExpense(1,$outerSource,$usage,$patients[1],1,['patient_name'=>$run]);
    $check($outerId>0&&(int)$db->transDepth===1&&$db->transStatus(),'COGS joins healthy caller transaction without nested begin/commit');
    $db->transRollback();
    $check($row($outerId)===[],'Caller rollback removes its new COGS finance completely');
    $check((int)$db->table('notification_attempts')->countAllResults()===$attempts&&(int)$db->table('notifications')->countAllResults()===$notifications,'Creator checks enqueue/send no notifications');
    $check((int)$db->transDepth===0&&$db->transStatus(),'Compatibility checks finish with healthy transaction state');
    echo json_encode(['status'=>'PASS','scope'=>'Actual controller and ledger compatibility; not routed auth or concurrency proof','run'=>$run,'check_count'=>count($checks),'checks'=>$checks,'retained_new_finance_ids'=>$created,'retained_new_foreign_exam_id'=>$foreignExam],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";
}catch(Throwable $e){if((int)$db->transDepth>0)$db->transRollback();echo json_encode(['status'=>'FAIL','run'=>$run,'error'=>$e->getMessage(),'checks'=>$checks,'retained_new_finance_ids'=>$created],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";exit(1);}finally{App\Libraries\ApiRuntimeContext::clear();}