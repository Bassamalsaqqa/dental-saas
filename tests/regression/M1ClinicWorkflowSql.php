<?php
declare(strict_types=1);
// Focused owned-fixture SQL harness. Does not send notifications or modify preexisting rows.
if(PHP_SAPI!=='cli')exit(2);
$expected='/home/u556956644/.denta-stage-20261017';$options=getopt('', ['bootstrap:']);$bootstrap=$options['bootstrap']??'';
if(!str_starts_with($bootstrap,$expected.'/')||realpath($bootstrap)!==$bootstrap||is_link($bootstrap))throw new RuntimeException('Canonical private bootstrap required');
require $bootstrap;
if($private!==$expected||realpath(HOMEPATH)!==$expected.'/m1-20261020-base8499042'||$db->database!=='u556956644_dentstage1017'||$db->username!=='u556956644_dstage1017'||$db->hostname!=='127.0.0.1'||(int)$db->port!==3306||$db->getPrefix()!==''||(int)$db->transDepth!==0||!$db->transStatus())throw new RuntimeException('Synthetic private database rejected');
$identity=$db->query('SELECT guid,domain,synthetic_only FROM _denta_stage_identity WHERE id=1')->getRowArray();
if(($identity['guid']??'')!=='b60809432ec1d915b0b975aef8484e43'||($identity['domain']??'')!=='dentastaging.palsync.net'||(int)($identity['synthetic_only']??0)!==1)throw new RuntimeException('Synthetic identity rejected');
$db->query('SET SESSION innodb_lock_wait_timeout=5');
$sourceFile=APPPATH.'Services/ClinicWorkflowClaimService.php';$source=file_get_contents($sourceFile);
$expectedSourceHash='a14a0540ac457c9f674fd7859581b6999545c1d1a20124ebea2c3cefbcb8773f';
if(hash('sha256',$source)!==$expectedSourceHash)throw new RuntimeException('Frozen claim source differs');
// Only the registry read dependency is a fixture; actual application claim/dispatch source is loaded unchanged.
if(class_exists('App\\Services\\WhatsAppTemplateRegistryService',false))throw new RuntimeException('Registry already loaded; cannot isolate synthetic config');
final class M1FixtureTemplateRegistry {
 public function getGlobalTriggerTemplates():array{$r=[];foreach(['appointment_confirmation','appointment_reminder','no_show_follow_up','follow_up']as$t)$r[$t]=['name'=>'m1_synthetic_'.$t,'language'=>'ar','header_param_key'=>'clinic_name','body_param_keys'=>$t==='follow_up'?['patient_name','examination_date']:['patient_name','appointment_date','appointment_time'],'button_type'=>'url','button_param_key'=>'clinic_phone','button_index'=>'0'];return $r;}
}
if(!class_alias(M1FixtureTemplateRegistry::class,'App\\Services\\WhatsAppTemplateRegistryService'))throw new RuntimeException('Registry fixture alias failed');
$run='M1WA_'.bin2hex(random_bytes(6));$checks=[];$ownedAppointments=[];$ownedNotes=[];
$check=static function(bool $ok,string $label)use(&$checks):void{if(!$ok)throw new RuntimeException($label);$checks[]=$label;};
$snapshot=static function()use($db):array{$s=[];foreach(['patients','appointments','notifications','notification_attempts','settings','whatsapp_tenant_policies']as$t){$s[$t]=[];foreach($db->table($t)->orderBy('id')->get()->getResultArray()as$row)$s[$t][(string)$row['id']]=hash('sha256',json_encode($row,JSON_THROW_ON_ERROR));}return $s;};
$baseline=$snapshot();
try{
 $formatter=App\Services\ClinicWorkflowFormatter::forClinic($db,1);
 $seed=$db->table('appointments')->where('clinic_id',1)->orderBy('id')->get(1)->getRowArray();if(!$seed)throw new RuntimeException('Synthetic clinic1 appointment seed required');
 $patient=$db->table('patients')->where('id',$seed['patient_id'])->where('clinic_id',1)->get()->getRowArray();
 $foreign=$db->table('patients')->where('clinic_id',2)->orderBy('id')->get(1)->getRowArray();if(!$patient||!$foreign)throw new RuntimeException('Two synthetic clinics with patients required');
 $appointment=$seed;unset($appointment['id']);if(array_key_exists('appointment_id',$appointment)){$appointment['appointment_id']='MW'.bin2hex(random_bytes(6));if($db->table('appointments')->where('appointment_id',$appointment['appointment_id'])->countAllResults()!==0)throw new RuntimeException('Owned appointment identifier collision');}$appointment['status']='scheduled';$appointment['appointment_date']='2035-06-10';$appointment['appointment_time']='13:05:00';$appointment['notes']=$run;
 if(!$db->table('appointments')->insert($appointment))throw new RuntimeException('Owned appointment fixture failed');$appointment['id']=(int)$db->insertID();$ownedAppointments[]=$appointment['id'];
 $clinic=$db->table('clinics')->where('id',1)->get()->getRowArray();$clinicVars=['clinic_name'=>'','clinic_phone'=>'','clinic_address'=>''];foreach($db->table('settings')->select('setting_key,setting_value')->where('clinic_id',1)->whereIn('setting_key',array_keys($clinicVars))->get()->getResultArray()as$row)$clinicVars[$row['setting_key']]=trim((string)$row['setting_value']);if($clinicVars['clinic_name']==='')$clinicVars['clinic_name']=trim((string)$clinic['name']);
 $configs=(new App\Services\WhatsAppTemplateRegistryService())->getGlobalTriggerTemplates();
 $make=static function(string $trigger,array $source,?callable $tamper=null,?string $keyOverride=null)use($db,$patient,$formatter,$clinicVars,$configs,&$ownedNotes):array{
  $vars=$formatter->appointmentVars($patient,$source,$clinicVars);$config=$configs[$trigger];
  $payload=['workflow_trigger'=>$trigger,'workflow_key'=>$keyOverride??($trigger.':'.$source['id'].':'.$source['appointment_date'].':'.$source['appointment_time']),'template_source'=>'workflow_trigger','template_name'=>$config['name'],'template_language'=>$config['language'],'template_vars'=>$vars,'template_components'=>App\Services\WorkflowTemplatePayload::components($config,$vars)];if($tamper)$payload=$tamper($payload);
  $row=['clinic_id'=>1,'channel_type'=>'whatsapp','recipient_type'=>'patient','recipient_id'=>$patient['id'],'recipient_address'=>null,'payload_json'=>json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),'status'=>'pending','locked_at'=>null,'locked_by'=>null,'created_at'=>date('Y-m-d H:i:s')];
  if(!$db->table('notifications')->insert($row))throw new RuntimeException('Owned notification insert failed');$row['id']=(int)$db->insertID();$ownedNotes[]=$row['id'];return $db->table('notifications')->where('id',$row['id'])->get()->getRowArray();
 };
 $get=static fn(int$id)=>$db->table('notifications')->where('id',$id)->get()->getRowArray();
 $fixedNow=new DateTimeImmutable('2035-06-09T00:00:00Z');$claim=new App\Services\ClinicWorkflowClaimService($db,$fixedNow);
 $note=$make('appointment_confirmation',$appointment);$claimed=$claim->claim($note,1,$run,'whatsapp',15);$stored=$get((int)$note['id']);
 $check(is_array($claimed)&&$stored['status']==='processing'&&$claimed['payload_json']===$stored['payload_json']&&$claimed['locked_by']===$run,'Current source claim commits processing and returns exact locked payload');
 $check($claim->claim($note,1,$run.'second','whatsapp',15)===false,'A second dispatcher cannot claim an active owned lock');
 $check((int)$db->transDepth===0&&$db->transStatus(),'Claim closes its owned transaction healthy');
 foreach(['stale-date','stale-patient','cancelled','cross-clinic-note','component-tamper','malformed-key']as$case){
  $note=$make('appointment_confirmation',$appointment,$case==='component-tamper'?static function($p){$p['template_components'][1]['parameters'][1]['text']='WRONG';return $p;}:null,$case==='malformed-key'?'appointment_confirmation:bad':null);
  if($case==='stale-date')$db->table('appointments')->where('id',$appointment['id'])->update(['appointment_date'=>'2035-06-11']);
  if($case==='stale-patient')$db->table('appointments')->where('id',$appointment['id'])->update(['patient_id'=>$foreign['id']]);
  if($case==='cancelled')$db->table('appointments')->where('id',$appointment['id'])->update(['status'=>'cancelled']);
  if($case==='cross-clinic-note'){$db->table('notifications')->where('id',$note['id'])->update(['clinic_id'=>2]);$note=$get((int)$note['id']);}
  $result=$claim->claim($note,$case==='cross-clinic-note'?2:1,$run,'whatsapp',15);$stored=$get((int)$note['id']);
  $check($result===false&&$stored['status']==='rejected'&&$stored['failure_reason']==='WORKFLOW_STALE_OR_INVALID',$case.' rejects at actual SQL claim');
  $check($stored['payload_json']===$note['payload_json'],'Rejected '.$case.' preserves queued payload/history');
  $db->table('appointments')->where('id',$appointment['id'])->update(['appointment_date'=>$appointment['appointment_date'],'patient_id'=>$appointment['patient_id'],'status'=>'scheduled']);
 }
 // Real second connection holds each contested row; NOWAIT must preserve pending state and connection reuse.
 $blocker=new mysqli('127.0.0.1',$c['user'],$c['password'],$c['name'],3306);
 if($blocker->query('SELECT DATABASE() AS db')->fetch_assoc()['db']!==$c['name'])throw new RuntimeException('Blocker escaped synthetic database');
 foreach(['clinics'=>1,'patients'=>(int)$patient['id'],'appointments'=>(int)$appointment['id'],'notifications'=>0]as$table=>$lockId){
  $note=$make('appointment_confirmation',$appointment);if($table==='notifications')$lockId=(int)$note['id'];$before=$get((int)$note['id']);
  $blocker->begin_transaction();
  try{
   $statement=$blocker->prepare('SELECT id FROM `'.$table.'` WHERE id = ? FOR UPDATE');$statement->bind_param('i',$lockId);$statement->execute();$statement->get_result()->fetch_assoc();$statement->close();
   $start=microtime(true);$result=$claim->claim($note,1,$run,'whatsapp',15);$elapsed=microtime(true)-$start;
   $check($result===false&&$get((int)$note['id'])===$before,'Held '.$table.' lock leaves pending payload and lock state untouched');
   $check($elapsed<2.0&&(int)$db->transDepth===0&&$db->transStatus(),'Held '.$table.' skips promptly and restores owned connection health');
  }finally{$blocker->rollback();}
  $check(is_array($claim->claim($note,1,$run,'whatsapp',15)),'Same connection claims successfully after '.$table.' contention clears');
 }
 $blocker->close();
 $policy=$db->table('whatsapp_tenant_policies')->where('clinic_id',1)->get()->getRowArray();$lead=max(15,min((int)($policy['reminder_lead_time_minutes']??1440),10080));$instant=$formatter->instant($appointment['appointment_date'],$appointment['appointment_time']);
 foreach([0=>true,-1=>false]as$delta=>$eligible){$now=$instant->setTimestamp($instant->getTimestamp()-$lead*60+$delta);$note=$make('appointment_reminder',$appointment);$r=(new App\Services\ClinicWorkflowClaimService($db,$now))->claim($note,1,$run,'whatsapp',15);$check(is_array($r)===$eligible,'Actual SQL reminder elapsed lead edge delta'.$delta);}
 $db->table('appointments')->where('id',$appointment['id'])->update(['status'=>'no_show']);$noShow=$appointment;$noShow['status']='no_show';
 $day=$formatter->currentDate($fixedNow);$prior=(new DateTimeImmutable($day.' 12:00:00',$formatter->timezone))->modify('-1 day')->format('Y-m-d');
 $note=$make('no_show_follow_up',$noShow,null,'no_show_follow_up:'.$appointment['id'].':'.$prior);$check(is_array($claim->claim($note,1,$run,'whatsapp',15)),'Prior-day no-show enqueue key is not an implicit TTL');
 // Verify actual production dispatcher cache wiring; no batch dispatch/driver call.
 $db->table('appointments')->where('id',$appointment['id'])->update(['status'=>'scheduled']);$note=$make('appointment_confirmation',$appointment);$dispatcher=new App\Services\DispatchService();$check($dispatcher->claimNotification((int)$note['id'],1,$run,'whatsapp'),'Dispatcher calls targeted actual SQL claim successfully');
 $cache=(new ReflectionProperty($dispatcher,'claimedReminderRows'))->getValue($dispatcher);$check(($cache[(int)$note['id']]['payload_json']??null)===$get((int)$note['id'])['payload_json'],'Dispatcher retains exact claimed current payload for its existing handoff');
 $after=$snapshot();$preserved=0;foreach($baseline as$table=>$rows)foreach($rows as$id=>$hash){if(($after[$table][$id]??null)!==$hash)throw new RuntimeException('Preexisting synthetic row changed in '.$table);$preserved++;}$check(true,'All '.$preserved.' preexisting protected rows remain unchanged');
 $check((int)$db->transDepth===0&&$db->transStatus(),'SQL harness ends with no active transaction');
 echo json_encode(['status'=>'PASS','run'=>$run,'checks'=>$checks,'retained_owned_appointment_ids'=>$ownedAppointments,'retained_owned_notification_ids'=>$ownedNotes,'source_sha256'=>$expectedSourceHash,'limits'=>'Real MariaDB claim transactions commit. Only the registry dependency is fixture-aliased because synthetic Meta templates are unconfigured; actual frozen claim/dispatch sources remain unchanged. Dispatcher cache wiring checked; no batch/driver invocation, transport, existing settings changes or source freeze across transport.'],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";
}catch(Throwable$e){if((int)$db->transDepth>0)$db->transRollback();echo json_encode(['status'=>'FAIL','run'=>$run,'error'=>$e->getMessage(),'checks'=>$checks,'retained_owned_appointment_ids'=>$ownedAppointments,'retained_owned_notification_ids'=>$ownedNotes],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";exit(1);}
