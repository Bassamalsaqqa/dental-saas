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

$run='COGSF_'.bin2hex(random_bytes(5)); $checks=[]; $ids=[];
$check=static function(bool $ok,string $label)use(&$checks):void{if(!$ok)throw new RuntimeException($label);$checks[]=$label;};
$uuid=bin2hex(random_bytes(16));
if(!$db->table('clinics')->insert(['name'=>$run,'slug'=>strtolower($run),'status'=>'active','uuid'=>substr($uuid,0,8).'-'.substr($uuid,8,4).'-'.substr($uuid,12,4).'-'.substr($uuid,16,4).'-'.substr($uuid,20)]))throw new RuntimeException('New clinic insert failed.');
$clinic=(int)$db->insertID();if($clinic<=0)throw new RuntimeException('New owned clinic required.');
if(!$db->table('patients')->insert(['clinic_id'=>$clinic,'patient_id'=>$run,'first_name'=>'Synthetic','last_name'=>'COGS','phone'=>'0000000000','date_of_birth'=>'1990-01-01','gender'=>'other']))throw new RuntimeException('New patient insert failed.');
$patient=(int)$db->insertID();if($patient<=0)throw new RuntimeException('New owned patient required.');
session()->set(['active_clinic_id'=>$clinic,'user_id'=>1,'global_mode'=>false]);
App\Libraries\ApiRuntimeContext::setClinicId($clinic);App\Libraries\ApiRuntimeContext::setUserId(1);
$ledger=new App\Services\FinanceLedgerService();$reflection=new ReflectionClass($ledger);$model=$reflection->getProperty('financeModel')->getValue($ledger);
if($model->db!==$db)throw new RuntimeException('Ledger connection differs.');
$usage=['usage_date'=>'2097-06-02','total_cost'=>13.25,'items_used'=>'[]','notes'=>$run];$context=['patient_name'=>$run];
$row=static fn(int $id):array=>$db->table('finances')->where('clinic_id',$clinic)->where('id',$id)->get()->getRowArray()??[];
$history=static function()use($db):array{$out=[];foreach(['finances','finance_payments','provider_payouts','provider_payout_payments','activity_logs','notifications','notification_attempts']as$t){if($db->tableExists($t))$out[$t]=$db->table($t)->orderBy('id')->get()->getResultArray();}return $out;};
$source=random_int(1500000000,1900000000);
$new=static function()use($db,$ledger,$clinic,$patient,$usage,$context,&$source,&$ids):int{
 ++$source;if($db->table('finances')->where('clinic_id',$clinic)->where('source_entity_type','inventory_usage')->where('source_entity_id',$source)->countAllResults()!==0)throw new RuntimeException('Owned source collision.');
 $id=(int)$ledger->upsertInventoryUsageCogsExpense($clinic,$source,$usage,$patient,1,$context);if($id<=0)throw new RuntimeException('COGS creation failed.');$ids[]=$id;return $id;
};
try{
 $id=$new();$before=$row($id);$all=$history();
 $same=(int)$ledger->upsertInventoryUsageCogsExpense($clinic,$source,$usage,$patient,2,$context);
 $check($same===$id&&$row($id)===$before&&$history()===$all,'Equivalent SQL decimal representations and actor retry retain exact row and history');
 $check((float)$before['total_amount']===13.25&&$before['payment_status']==='paid'&&(float)$before['amount_paid']===13.25&&(float)$before['balance_due']===0.0,'New canonical model total and settlement state agree');
 $check($before['transaction_date']===$usage['usage_date']&&$before['paid_date']===$usage['usage_date']&&$before['payment_method']==='other'&&$before['liquidity_account']===null,'New COGS uses explicit accounting date and noncash null treasury');
 $mutations=['total_amount'=>12.25,'payment_status'=>'pending','amount_paid'=>12.25,'balance_due'=>1,'paid_date'=>'2097-06-03','payment_method'=>'cash','liquidity_account'=>'bank','transaction_date'=>'2097-06-03','exchange_rate'=>1.000001];
 if($db->fieldExists('deleted_at','finances'))$mutations['deleted_at']='2097-06-03 00:00:00';
 foreach($mutations as$field=>$value){
  $id=$new();$owned=$row($id);if((int)$owned['source_entity_id']!==$source||!str_contains($owned['notes'],$run))throw new RuntimeException('Unowned mutation target.');
  if(!$db->table('finances')->where('clinic_id',$clinic)->where('id',$id)->update([$field=>$value]))throw new RuntimeException('Owned field fixture failed: '.$field);
  $before=$row($id);$all=$history();$rejected=false;
  try{$ledger->upsertInventoryUsageCogsExpense($clinic,$source,$usage,$patient,1,$context);}catch(DomainException $expected){$rejected=true;}
  $check($rejected&&$row($id)===$before&&$history()===$all,'Reject differing '.$field.' without any finance/payment/audit/history rewrite');
  $check((int)$db->transDepth===0&&$db->transStatus(),'Rejected '.$field.' closes owned transaction healthily');
 }
 // The actual synthetic total column is nullable; exercise real SQL NULL, not a missing-schema waiver.
 $id=$new();if(!$db->table('finances')->where('clinic_id',$clinic)->where('id',$id)->update(['total_amount'=>null]))throw new RuntimeException('Owned NULL-total fixture failed.');
 $before=$row($id);$all=$history();$rejected=false;
 try{$ledger->upsertInventoryUsageCogsExpense($clinic,$source,$usage,$patient,1,$context);}catch(DomainException $expected){$rejected=true;}
 $check($rejected&&$row($id)===$before&&$history()===$all,'Actual SQL present-NULL total rejects without row or history rewrite');
 // Empty treasury representation is equivalent only for this canonical noncash COGS state.
 $id=$new();if(!$db->table('finances')->where('clinic_id',$clinic)->where('id',$id)->update(['liquidity_account'=>'']))throw new RuntimeException('Owned empty-account fixture failed.');
 $before=$row($id);$all=$history();$same=(int)$ledger->upsertInventoryUsageCogsExpense($clinic,$source,$usage,$patient,2,$context);
 $check($same===$id&&$row($id)===$before&&$history()===$all,'Actual SQL noncash empty and null account representations are equivalent without rewriting');
 // These malformed/foreign-identity comparator inputs need no invalid FK or SQL mode changes.
 $id=$new();$canonical=$row($id);$payload=$canonical;$method=$reflection->getMethod('assertCogsPayloadMatchesExisting');$method->setAccessible(true);
 foreach(['exchange_rate'=>'NaN','supplier_id'=>123456789,'transaction_date'=>'0000-00-00']as$field=>$value){
  $bad=$canonical;$bad[$field]=$value;$all=$history();$rejected=false;
  try{$method->invoke($ledger,$bad,$payload);}catch(DomainException $expected){$rejected=true;}
  $check($rejected&&$history()===$all,'Comparator rejects '.$field.' malformed/noncanonical input without SQL mutation');
 }
 echo json_encode(['status'=>'PASS','run'=>$run,'check_count'=>count($checks),'checks'=>$checks,'retained_new_finance_ids'=>$ids,'new_clinic'=>$clinic,'new_patient'=>$patient,'optional_total_absence_tested'=>false,'production_changes'=>false],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";
}catch(Throwable $e){if((int)$db->transDepth>0)$db->transRollback();echo json_encode(['status'=>'FAIL','error'=>$e->getMessage(),'checks'=>$checks,'run'=>$run,'retained_new_finance_ids'=>$ids],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";exit(1);}finally{App\Libraries\ApiRuntimeContext::clear();}
