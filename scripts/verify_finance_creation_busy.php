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
// The independent lock holder is explicitly configured, never a shared/default connection.
$db2 = \Config\Database::connect([
    'DSN'=>'', 'hostname'=>'127.0.0.1', 'username'=>$c['user'], 'password'=>$c['password'],
    'database'=>$c['name'], 'DBDriver'=>'MySQLi', 'DBPrefix'=>'', 'pConnect'=>false,
    'DBDebug'=>true, 'charset'=>'utf8mb4', 'DBCollat'=>'utf8mb4_unicode_ci', 'port'=>3306,
], false);
$db2->initialize();
if ($db2 === $db || $db2->database !== $db->database || $db2->username !== $db->username
    || $db2->hostname !== '127.0.0.1' || (int)$db2->port !== 3306 || $db2->getPrefix() !== ''
    || (int)$db2->transDepth !== 0 || !$db2->transStatus()) { throw new RuntimeException('Independent synthetic connection rejected.'); }
$id2=$db2->query('SELECT guid,domain,synthetic_only FROM _denta_stage_identity WHERE id=1')->getRowArray();
if ($id2 !== $identity) { throw new RuntimeException('Independent synthetic identity differs.'); }
foreach($db2->query('SHOW GRANTS')->getResultArray() as $row){
    $grant=str_replace('\\_', '_', (string)reset($row));
    if(str_contains($grant,'WITH GRANT OPTION')||(str_contains($grant,' ON *.* ')&&!str_starts_with($grant,'GRANT USAGE ON *.* '))
        ||(preg_match('/ ON `([^`]+)`\./',$grant,$m)&&$m[1]!==$db2->database)){throw new RuntimeException('Independent grants rejected.');}
}
if((int)$db->query('SELECT CONNECTION_ID() AS id')->getRowArray()['id'] === (int)$db2->query('SELECT CONNECTION_ID() AS id')->getRowArray()['id']){
    throw new RuntimeException('Lock-holder connection is not independent.');
}
$db->query('SET SESSION innodb_lock_wait_timeout=3');
$db2->query('SET SESSION innodb_lock_wait_timeout=3');
helper(['url','form']);
App\Libraries\ApiRuntimeContext::setClinicId(1);App\Libraries\ApiRuntimeContext::setUserId(1);
session()->set(['active_clinic_id'=>1,'user_id'=>1,'global_mode'=>false]);
$run='BUSY_'.bin2hex(random_bytes(5));$checks=[];$retained=[];
$assert=static function(bool $ok,string $label)use(&$checks):void{if(!$ok)throw new RuntimeException($label);$checks[]=$label;};
$busy=static function(callable $operation,string $label,bool $ownsOuterTx)use($db,$db2,$assert,&$checks):void{
    if($ownsOuterTx&&!$db->transBegin())throw new RuntimeException('Could not start reviewed writer transaction.');
    $start=microtime(true);$exception=null;$dbError=[];
    try{$operation();}catch(Throwable $e){$exception=$e;}
    $dbError=$db->error();
    $elapsed=microtime(true)-$start;
    $codes=[];$messages=[];
    for($e=$exception;$e!==null;$e=$e->getPrevious()){$codes[]=(int)$e->getCode();$messages[]=$e->getMessage();}
    $code1205=in_array(1205,$codes,true)||(int)($dbError['code']??0)===1205;
    $lockMessage=(bool)preg_match('/lock wait timeout|could not obtain lock|NOWAIT|busy/i',implode(' ',$messages).' '.($dbError['message']??''));
    try{
        $assert($code1205&&$lockMessage,$label.' fails specifically with MariaDB NOWAIT lock error 1205');
        $assert($elapsed<2.0,$label.' fails promptly below two seconds ('.round($elapsed,4).'s)');
        $assert((int)$db2->transDepth===1&&$db2->transStatus(),$label.' does not disturb lock-holder transaction');
    }finally{
        if((int)$db->transDepth>0)$db->transRollback();
        if((int)$db2->transDepth>0)$db2->transRollback();
    }
    $assert((int)$db->transDepth===0&&$db->transStatus()===false,
        $label.' rolls back its owned transaction while preserving framework strict failure status');
    $assert((int)$db2->transDepth===0&&$db2->transStatus(),
        $label.' leaves the independent lock holder healthy after rollback');
    // Reviewer-only reset permits the next independent test case; production guards must not clear failure status.
    $db->resetTransStatus();
    $assert((int)$db->transDepth===0&&$db->transStatus(),
        $label.' reviewer connection is explicitly reset only after verified owned rollback');
};
try{
    $patient=$db->table('patients')->select('id')->where('clinic_id',1)->like('patient_id','STG_','after')->get()->getRowArray();
    if(!$patient)throw new RuntimeException('Known synthetic patient required.');$patientId=(int)$patient['id'];
    $examRow=['clinic_id'=>1,'patient_id'=>$patientId,'examination_id'=>substr($run.'E',0,20),'examination_date'=>'2097-07-01',
        'examination_type'=>'initial','chief_complaint'=>$run,'status'=>'pending','created_by'=>1];
    if(!$db->table('examinations')->insert($examRow))throw new RuntimeException('Owned exam insertion failed.');
    $examId=(int)$db->insertID();$retained['new_examination_id']=$examId;
    $base=['clinic_id'=>1,'patient_id'=>$patientId,'amount'=>37,'amount_original'=>37,'currency'=>'ILS','exchange_rate'=>1,
        'payment_method'=>'cash','payment_status'=>'paid','service_type'=>'other','description'=>$run,'created_by'=>1];
    $paymentKey=$run.'_P';
    if(!$db2->transBegin())throw new RuntimeException('Could not start patient lock holder.');
    if(!$db2->query('SELECT id FROM patients WHERE clinic_id=1 AND id=? FOR UPDATE',[$patientId])->getRowArray())throw new RuntimeException('Patient lock target absent.');
    $busy(static function()use($db,$base,$paymentKey):void{
        $model=new App\Models\FinanceModel($db);
        if(!$model->insert(array_replace($base,['transaction_id'=>$paymentKey,'transaction_type'=>'payment'])))throw new RuntimeException('Unexpected payment validation failure: '.json_encode($model->errors()));
    },'Actual model payment with independently locked patient',true);
    $assert((int)$db->table('finances')->where('transaction_id',$paymentKey)->countAllResults()===0,'Blocked patient-reference payment inserts no finance row');

    $refundKey=$run.'_R';
    if(!$db2->transBegin())throw new RuntimeException('Could not start exam lock holder.');
    if(!$db2->query('SELECT id FROM examinations WHERE clinic_id=1 AND id=? FOR UPDATE',[$examId])->getRowArray())throw new RuntimeException('Owned exam lock target absent.');
    $busy(static function()use($db,$base,$refundKey,$examId):void{
        $model=new App\Models\FinanceModel($db);
        if(!$model->insert(array_replace($base,['transaction_id'=>$refundKey,'transaction_type'=>'refund','examination_id'=>$examId])))throw new RuntimeException('Unexpected refund validation failure: '.json_encode($model->errors()));
    },'Actual model refund with independently locked examination',true);
    $assert((int)$db->table('finances')->where('transaction_id',$refundKey)->countAllResults()===0,'Blocked examination-reference refund inserts no finance row');

    $ledger=new App\Services\FinanceLedgerService();
    $model=(new ReflectionClass($ledger))->getProperty('financeModel')->getValue($ledger);
    if($model->db!==$db)throw new RuntimeException('Ledger escaped guarded connection.');
    $source=random_int(1500000000,1999999999);
    if($db->table('finances')->where('clinic_id',1)->where('source_entity_type','inventory_usage')->where('source_entity_id',$source)->countAllResults()!==0)throw new RuntimeException('New source collision; no COGS fixture written.');
    $usage=['usage_date'=>'2097-07-02','total_cost'=>13.25,'items_used'=>'[]','notes'=>$run];
    $cogsId=(int)$ledger->upsertInventoryUsageCogsExpense(1,$source,$usage,$patientId,1,['patient_name'=>$run]);
    if($cogsId<=0)throw new RuntimeException('New COGS fixture was not created.');$retained['new_cogs_finance_id']=$cogsId;
    $before=$db->table('finances')->where('id',$cogsId)->get()->getRowArray();
    if(!$db2->transBegin())throw new RuntimeException('Could not start COGS lock holder.');
    if(!$db2->query('SELECT id FROM finances WHERE clinic_id=1 AND id=? FOR UPDATE',[$cogsId])->getRowArray())throw new RuntimeException('New COGS lock target absent.');
    $busy(static function()use($ledger,$source,$usage,$patientId,$run):void{
        $ledger->upsertInventoryUsageCogsExpense(1,$source,$usage,$patientId,1,['patient_name'=>$run]);
    },'Actual ledger identical COGS repost with independently locked finance',false);
    $assert($before===$db->table('finances')->where('id',$cogsId)->get()->getRowArray(),'Busy COGS repost preserves existing posted row exactly');
    $assert((int)$db->table('finances')->where('clinic_id',1)->where('source_entity_type','inventory_usage')->where('source_entity_id',$source)->countAllResults()===1,'Busy COGS repost creates no duplicate finance row');
    echo json_encode(['status'=>'PASS','run'=>$run,'scope'=>'Three deterministic two-connection NOWAIT checks; synthetic only','checks'=>$checks,'check_count'=>count($checks),'retained_new_fixtures'=>$retained],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";
}catch(Throwable $e){
    if((int)$db->transDepth>0)$db->transRollback();if((int)$db2->transDepth>0)$db2->transRollback();
    echo json_encode(['status'=>'FAIL','run'=>$run,'error'=>$e->getMessage(),'checks'=>$checks,'retained_new_fixtures'=>$retained],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";exit(1);
}finally{App\Libraries\ApiRuntimeContext::clear();$db2->close();}
