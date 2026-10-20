<?php
/**
 * Private synthetic fixture; no automatic connection, DDL, notifications or persistent row changes.
 * Approved private-host invocation (fails closed elsewhere):
 * php M1TenantReadContainment.php --bootstrap=/home/u556956644/.denta-stage-20261017/m1-private-bootstrap.php
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(2); }
$options = getopt('', ['bootstrap:']);
$bootstrap = $options['bootstrap'] ?? null;
if (!is_string($bootstrap) || $bootstrap !== '/home/u556956644/.denta-stage-20261017/m1-private-bootstrap.php') {
    throw new RuntimeException('Pass --bootstrap=/home/u556956644/.denta-stage-20261017/m1-private-bootstrap.php explicitly; no automatic database connection is permitted');
}
if (realpath($bootstrap)!==$bootstrap || is_link($bootstrap)) { throw new RuntimeException('Canonical private bootstrap required'); }
require $bootstrap;
if (!isset($db) || $db->database!=='u556956644_dentstage1017' || $db->username!=='u556956644_dstage1017' || $db->hostname!=='127.0.0.1' || (int)$db->port!==3306 || realpath(HOMEPATH)!=='/home/u556956644/.denta-stage-20261017/m1-20261020-base8499042' || (int)$db->transDepth!==0 || !$db->transStatus()) { throw new RuntimeException('Synthetic connection/root/depth rejected'); }
$marker=$db->query('SELECT guid, domain, synthetic_only FROM _denta_stage_identity WHERE id=1')->getRowArray();
$who=$db->query('SELECT DATABASE() db, CURRENT_USER() actor')->getRowArray();
if (($marker['guid']??'')!=='b60809432ec1d915b0b975aef8484e43' || ($marker['domain']??'')!=='dentastaging.palsync.net' || (int)($marker['synthetic_only']??0)!==1 || ($who['db']??'')!==$db->database || !str_starts_with((string)($who['actor']??''),'u556956644_dstage1017@')) { throw new RuntimeException('Synthetic identity rejected'); }
$grants=$db->query('SHOW GRANTS')->getResultArray();
if (!$grants) { throw new RuntimeException('Missing grants'); }
foreach($grants as $row) { $g=str_replace('\\_','_',(string)reset($row)); if (str_contains($g,'WITH GRANT OPTION') || !preg_match('/ ON (`[^`]+`|\*)\.(`[^`]+`|\*) TO /',$g,$m) || ($m[1]==='*' && !str_starts_with($g,'GRANT USAGE ON *.* ')) || ($m[1]!=='*' && trim($m[1],'`')!==$db->database)) { throw new RuntimeException('Excessive grants'); } }
foreach($db->query("SELECT setting_value FROM settings WHERE setting_key='dynamic_doctor_scheduling_enabled'")->getResultArray() as $s) { if(!in_array(strtolower(trim((string)$s['setting_value'])),['','0','false','off'],true)) { throw new RuntimeException('Scheduling must stay OFF'); } }
$tables=['patients','finances','treatments','provider_payouts','provider_payout_payments'];
$hashes=static function()use($db,$tables):array { $r=[]; foreach($tables as $t) { $r[$t]=hash('sha256',json_encode($db->table($t)->orderBy('id')->get()->getResultArray(),JSON_THROW_ON_ERROR)); } return $r; };
$before=$hashes(); $run='M1D'.bin2hex(random_bytes(5)); $checks=[];
$assert=static function(bool $ok,string $label)use(&$checks):void { if(!$ok) { throw new RuntimeException($label); } $checks[]=$label; };
$insert=static function(string $table,array $data)use($db):int { if(!$db->table($table)->insert($data)) { throw new RuntimeException('Insert failed:'.$table); } return(int)$db->insertID(); };
$priorClinic=\App\Libraries\ApiRuntimeContext::getClinicId(); $priorUser=\App\Libraries\ApiRuntimeContext::getUserId();
\App\Libraries\ApiRuntimeContext::setClinicId(1); \App\Libraries\ApiRuntimeContext::setUserId(1);
$started=false;
try {
 if(!$db->transBegin()) { throw new RuntimeException('Transaction begin failed'); } $started=true;
 $patient=static function(int $clinic,string $name)use($insert,$run):int { return $insert('patients',['clinic_id'=>$clinic,'patient_id'=>$run.$clinic,'first_name'=>$name,'last_name'=>'Synthetic','phone'=>'0000000000','date_of_birth'=>'1990-01-01','gender'=>'other']); };
 $ownName=$run.'Own'; $foreignName=$run.'Foreign'; $ownPatient=$patient(1,$ownName); $foreignPatient=$patient(2,$foreignName);
 $finance=static function(int $clinic,?int $patient,string $suffix)use($insert,$run):int { return $insert('finances',['clinic_id'=>$clinic,'patient_id'=>$patient,'transaction_id'=>$run.$suffix,'transaction_type'=>'invoice','amount'=>10,'total_amount'=>10,'amount_paid'=>0,'balance_due'=>10,'payment_method'=>'cash','payment_status'=>'pending','service_type'=>'other','description'=>$run,'transaction_date'=>'2098-07-10','due_date'=>'2000-01-01']); };
 $ownFinance=$finance(1,$ownPatient,'A'); $badFinance=$finance(1,$foreignPatient,'B'); $nullFinance=$finance(1,null,'C'); $foreignFinance=$finance(2,$foreignPatient,'D');
 $explain=[]; foreach(['baseline'=>'patients.id = finances.patient_id','candidate'=>'patients.id = finances.patient_id AND patients.clinic_id = finances.clinic_id'] as $label=>$join) { $explain[$label]=$db->query('EXPLAIN SELECT finances.id,patients.first_name FROM finances LEFT JOIN patients ON '.$join.' WHERE finances.clinic_id=1 AND finances.description=?',[$run])->getResultArray(); }
 $fm=new \App\Models\FinanceModel($db); $rows=$fm->getFinancesByClinic(1,1000,0,$run); $byId=array_column($rows,null,'id');
 $assert(count($rows)===3 && count($byId)===3,'Finance LEFT list preserves own, foreign-reference and NULL-patient document rows');
 $assert(($byId[$ownFinance]['first_name']??null)===$ownName,'Finance own patient display retained');
 $assert(($byId[$badFinance]['first_name']??null)===null && ($byId[$nullFinance]['first_name']??null)===null,'Finance malformed and orphan references expose no patient name');
 $assert($fm->countFinancesByClinic(1,$run)===3,'Finance filtered count preserves LEFT row semantics');
 $assert($fm->countFinancesByClinic(1,$foreignName)===0 && $fm->getFinancesByClinic(1,100,0,$foreignName)===[],'Finance foreign-name search and count reveal no match');
 $assert(count($fm->getFinanceByPatient($ownPatient))===1 && $fm->getFinanceByPatient($foreignPatient)===[],'Patient finance history rejects cross-clinic parent reference');
 $overdue=array_map('intval',array_column($fm->getOverduePayments(1),'id')); 
 $assert(in_array($ownFinance,$overdue,true) && !in_array($badFinance,$overdue,true),'Overdue INNER result contains valid own document only');
 $treatment=static function(int $patient,string $suffix)use($insert,$run):int { return $insert('treatments',['clinic_id'=>1,'patient_id'=>$patient,'treatment_id'=>$run.$suffix,'treatment_name'=>'Synthetic','treatment_type'=>'other','treatment_description'=>$run,'status'=>'active','start_date'=>'2098-07-10','estimated_duration'=>10]); };
 $ownTreatment=$treatment($ownPatient,'T1'); $badTreatment=$treatment($foreignPatient,'T2');
 $tm=new \App\Models\TreatmentModel($db); $tr=$tm->getTreatmentsByClinic(1,100,0,$run); $tb=array_column($tr,null,'id');
 $assert(count($tr)===2 && ($tb[$ownTreatment]['first_name']??null)===$ownName && ($tb[$badTreatment]['first_name']??null)===null,'Treatment LEFT list preserves malformed row without foreign PII');
 $assert($tm->countTreatmentsByClinic(1,$run)===2 && $tm->countTreatmentsByClinic(1,$foreignName)===0,'Treatment counts retain rows but reject foreign-name search');
 $assert($tm->getTreatmentWithPatientInfo($badTreatment)===null && $tm->searchTreatments($foreignName)===[],'Treatment INNER detail/search fail closed on foreign patient');
 foreach(['app/Controllers/Finance.php'=>'finances','app/Controllers/Treatment.php'=>'treatments'] as $file=>$table) {
  $source=file_get_contents(HOMEPATH.$file);
  if(!preg_match_all("/->join\\('patients', '([^']+)', 'left'\\)/",$source,$matches)) { throw new RuntimeException('Controller patient join not found'); }
  $condition=end($matches[1]);
  if(!str_contains($condition,'patients.clinic_id = '.$table.'.clinic_id')) { throw new RuntimeException('Controller join lacks clinic pair'); }
  $reference=$table==='finances'?'description':'treatment_description';
  $result=$db->table($table)->select($table.'.id,patients.first_name')->join('patients',$condition,'left')->where($table.'.clinic_id',1)->where($table.'.'.$reference,$run)->get()->getResultArray();
  $assert(!str_contains(json_encode($result,JSON_THROW_ON_ERROR),$foreignName),'Actual controller '.$table.' join executes without foreign PII');
 }
 $activityModel=new \App\Models\ActivityLogModel($db); $activityReflection=new ReflectionClass($activityModel);
 $language=service('language'); $priorLocale=$language->getLocale();
 try { foreach(['en','ar'] as $locale) { $language->setLocale($locale);
  foreach([['buildTreatmentNotificationMessage',$ownTreatment,$badTreatment],['buildFinanceNotificationMessage',$ownFinance,$badFinance]] as [$method,$validId,$badId]) {
   $reflectionMethod=$activityReflection->getMethod($method);
   $validMessage=$reflectionMethod->invoke($activityModel,['entity_id'=>$validId,'clinic_id'=>1],'Synthetic Operator','create');
   $badMessage=$reflectionMethod->invoke($activityModel,['entity_id'=>$badId,'clinic_id'=>1],'Synthetic Operator','create');
   $assert(is_string($validMessage) && str_contains($validMessage,$ownName),$locale.' '.$method.' preserves own-clinic patient identity');
   $assert(is_string($badMessage) && !str_contains($badMessage,$foreignName) && (str_contains($badMessage,lang('UI.notificationUnknownPatient')) || str_contains($badMessage,lang('UI.notificationUnknownRecordTarget'))),$locale.' '.$method.' displays unknown instead of foreign patient');
  }
 } } finally { $language->setLocale($priorLocale); }
 $payout=static function(int $finance,int $patient,string $name)use($insert):int { return $insert('provider_payouts',['clinic_id'=>1,'finance_id'=>$finance,'patient_id'=>$patient,'provider_type'=>'guest','provider_name_snapshot'=>$name,'gross_amount'=>10,'provider_share_amount'=>3,'provider_balance_due'=>3,'created_at'=>'2098-07-10 12:00:00']); };
 $ownPayout=$payout($ownFinance,$ownPatient,$run.'P1'); $badPayout=$payout($foreignFinance,$foreignPatient,$run.'P2');
 $pm=new \App\Models\ProviderPayoutModel($db); $pr=$pm->getDataTableByClinic(1,0,100,$run); $pb=array_column($pr['rows'],null,'id');
 $assert($pr['recordsFiltered']===2 && count($pr['rows'])===2,'Provider payout LEFT list/count preserve malformed obligations');
 $assert(($pb[$ownPayout]['patient_first_name']??null)===$ownName && ($pb[$badPayout]['patient_first_name']??null)===null && ($pb[$badPayout]['transaction_id']??null)===null,'Provider payout malformed patient and finance fields stay blank');
 $assert($pm->getDataTableByClinic(1,0,100,$foreignName)['recordsFiltered']===0,'Provider payout foreign-name search returns no match');
 $cashBefore=$pm->getStatsByClinic(1,'2098-07-10','2098-07-10')['total_provider_paid_cash'];
 $insert('provider_payout_payments',['clinic_id'=>1,'provider_payout_id'=>$ownPayout,'amount'=>3,'paid_date'=>'2098-07-10']);
 $insert('provider_payout_payments',['clinic_id'=>2,'provider_payout_id'=>$ownPayout,'amount'=>5,'paid_date'=>'2098-07-10']);
 $cashAfter=$pm->getStatsByClinic(1,'2098-07-10','2098-07-10')['total_provider_paid_cash'];
 $assert(abs(($cashAfter-$cashBefore)-3)<0.0001,'Provider cash sum excludes malformed foreign-clinic payment');
 if(!$db->transRollback()) { throw new RuntimeException('Rollback failed'); } $started=false;
 $assert($hashes()===$before,'All five original table contents unchanged after rollback');
 echo json_encode(['status'=>'PASS','synthetic_only'=>true,'explain'=>$explain,'checks'=>$checks,'transactions_rolled_back'=>true,'coverage_limit'=>'Actual model SQL and controller join expressions; no full authenticated browser route execution'],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),PHP_EOL;
} finally { if($started) { $db->transRollback(); } \App\Libraries\ApiRuntimeContext::clear(); if($priorClinic!==null){\App\Libraries\ApiRuntimeContext::setClinicId($priorClinic);} if($priorUser!==null){\App\Libraries\ApiRuntimeContext::setUserId($priorUser);} }