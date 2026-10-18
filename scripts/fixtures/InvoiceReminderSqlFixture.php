<?php
/** Independent reviewer fixture. Load only after root's guarded private synthetic CLI bootstrap. */
final class ReminderIndependentSqlFixture
{
 public static function run(\CodeIgniter\Database\BaseConnection $db): array
 {
  if(PHP_SAPI!=='cli'||!defined('HOMEPATH')||!str_contains(HOMEPATH,'/.denta-stage-20261017/remediation-20261018-'))throw new RuntimeException('Private CLI only');
  $who=$db->query('SELECT DATABASE() db,CURRENT_USER() actor')->getRowArray();$marker=$db->query('SELECT guid,synthetic_only FROM _denta_stage_identity WHERE id=1')->getRowArray();
  if($who['db']!=='u556956644_dentstage1017'||!str_starts_with($who['actor'],'u556956644_dstage1017@')||$marker['guid']!=='b60809432ec1d915b0b975aef8484e43'||(int)$marker['synthetic_only']!==1)throw new RuntimeException('Synthetic owner rejected');
  if((int)$db->transDepth!==0||$db->transStatus()===false)throw new RuntimeException('Clean connection required');
  session()->set(['active_clinic_id'=>1,'user_id'=>1,'global_mode'=>false]);\App\Libraries\ApiRuntimeContext::setClinicId(1);\App\Libraries\ApiRuntimeContext::setUserId(1);
  $run='R2Q_'.bin2hex(random_bytes(5));$checks=[];$ids=[];$notes=[];
  $assert=static function(bool $ok,string $label)use(&$checks){if(!$ok)throw new RuntimeException($label);$checks[]=$label;};
  $insert=static function(string $table,array $row)use($db):int{if(!$db->table($table)->insert($row))throw new RuntimeException('insert failed '.$table);return(int)$db->insertID();};
  $patient=[];foreach([1,2]as$c){$p=$db->table('patients')->select('id')->where('clinic_id',$c)->like('patient_id','STG_','after')->get()->getRowArray();if(!$p)throw new RuntimeException('Synthetic patient missing');$patient[$c]=(int)$p['id'];}
  $finance=static function(array $extra=[])use($insert,$run,&$ids,$patient):int{$c=(int)($extra['clinic_id']??1);$id=$insert('finances',array_merge(['clinic_id'=>$c,'patient_id'=>$patient[$c],'transaction_id'=>$run.'_'.count($ids),'transaction_type'=>'invoice','amount'=>100,'total_amount'=>100,'amount_original'=>100,'currency'=>'ILS','exchange_rate'=>1,'payment_method'=>'cash','liquidity_account'=>'safe_cash','payment_status'=>'pending','description'=>$run,'service_type'=>'other','discount_amount'=>0,'tax_percentage'=>0,'tax_amount'=>0,'amount_paid'=>0,'balance_due'=>100,'due_date'=>'2097-06-10','transaction_date'=>'2097-06-01','created_by'=>1,'created_at'=>'2097-06-01 12:00:00','updated_at'=>'2097-06-01 12:00:00'],$extra));$ids[]=$id;return$id;};
  $note=static function(int $fid,array $extra=[],?array $vars=null)use($db,$insert,$run,$patient,&$notes):int{$f=$db->table('finances')->where('id',$fid)->get()->getRowArray();$v=$vars??['amount'=>settings()->getCurrencySymbol().number_format((float)($f['balance_due']??100),2),'due_date'=>formatDate((string)($f['due_date']??'2097-06-10')),'invoice_number'=>$f['transaction_id']??'missing'];$data=array_merge(['clinic_id'=>1,'channel_type'=>'whatsapp','recipient_type'=>'patient','recipient_id'=>$patient[1],'payload_json'=>json_encode(['workflow_key'=>'payment_reminder:'.$fid.':'.$run,'workflow_trigger'=>'payment_reminder','template_source'=>'workflow_trigger','template_name'=>'synthetic_payment','template_vars'=>$v]),'status'=>'pending','created_at'=>'2097-06-01 12:00:00'],$extra);$id=$insert('notifications',$data);$notes[]=$id;return$id;};
  $row=static fn(int$id)=>$db->table('notifications')->where('id',$id)->get()->getRowArray();
  $present=static fn(int$id)=>$db->table('finances')->where('id',$id)->get()->getRowArray();
  $mutation=new \App\Services\FinanceMutationService($db);$dispatch=new \App\Services\DispatchService();
  $attemptsBefore=(int)$db->table('notification_attempts')->countAllResults();
  try{
   $f=$finance();$n=$note($f);$foreignNote=$note($f,['clinic_id'=>2,'recipient_id'=>$patient[2]]);$unrelated=$note($f,['payload_json'=>json_encode(['workflow_key'=>'payment_reminder:'.$f.'0:other','message'=>'unrelated'])]);$snapshot=$row($n)['payload_json'];
   $assert($mutation->deleteUnused(1,[$f],1)===1&&!$present($f),'Unused invoice deletion remains supported');
   $assert($row($n)['status']==='rejected'&&$row($n)['payload_json']===$snapshot,'Queued demand terminally reconciled without rewriting payload');
   $assert($row($foreignNote)['status']==='pending'&&$row($unrelated)['status']==='pending','Cross-clinic and numeric-prefix lookalikes preserved');
   $assert(!$dispatch->claimNotification($n,1,$run,'whatsapp'),'Cancelled reminder cannot claim');
   $audit=$db->table('activity_logs')->where('clinic_id',1)->where('entity_type','finance')->where('entity_id',$f)->where('action','delete')->get()->getRowArray();
   $assert($audit!==null&&str_contains($audit['metadata'],(string)$f),'Deletion keeps retained finance audit');
   $f=$finance();$n=$note($f,['status'=>'processing','locked_at'=>date('Y-m-d H:i:s'),'locked_by'=>$run]);
   try{$mutation->deleteUnused(1,[$f],1);$assert(false,'Processing demand must prevent deletion');}catch(DomainException $e){$checks[]='Actively processing reminder blocks deletion';}
   $assert($present($f)!==null&&$row($n)['status']==='processing','Processing rejection preserves both records');
   $f=$finance();$n=$note($f,['status'=>'sent']);try{$mutation->deleteUnused(1,[$f],1);$assert(false,'Sent history must prevent deletion');}catch(DomainException $e){$checks[]='Sent reminder history blocks deletion';}
   $f=$finance();$n=$note($f,['status'=>'failed']);$attempt=$insert('notification_attempts',['clinic_id'=>1,'notification_id'=>$n,'channel_type'=>'whatsapp','status'=>'failed_permanent','idempotency_key'=>hash('sha256',$run.'history'),'retry_count'=>0,'max_retries'=>3,'created_at'=>'2097-06-01 12:00:00']);
   try{$mutation->deleteUnused(1,[$f],1);$assert(false,'Attempt history must prevent deletion');}catch(DomainException $e){$checks[]='Failed-attempt history preserved and deletion blocked';}
   $first=$finance();$n1=$note($first);$second=$finance(['payment_status'=>'paid','amount_paid'=>100,'balance_due'=>0]);
   try{$mutation->deleteUnused(1,[$first,$second],1);$assert(false,'Mixed protected batch must fail');}catch(DomainException $e){$checks[]='Mixed protected batch rejected atomically';}
   $assert($present($first)!==null&&$row($n1)['status']==='pending','Failed batch does not cancel or delete first invoice');
   $missing=2147483000;$n=$note($missing);$assert(!$dispatch->claimNotification($n,1,$run,'whatsapp')&&$row($n)['status']==='rejected','Orphan cached demand rejected before outbound');
   foreach([['payment_status'=>'paid','amount_paid'=>100,'balance_due'=>0],['transaction_type'=>'expense'],['clinic_id'=>2]]as$change){$f=$finance($change);$n=$note($f);$assert(!$dispatch->claimNotification($n,1,$run,'whatsapp')&&$row($n)['status']==='rejected','Settled/noninvoice/foreign parent rejected before outbound');}
   $f=$finance();$n=$note($f);$db->table('finances')->where('id',$f)->update(['balance_due'=>90]);$assert(!$dispatch->claimNotification($n,1,$run,'whatsapp')&&$row($n)['status']==='rejected','Changed balance invalidates queued amount');
   foreach([['transaction_id'=>$run.'_changed'],['due_date'=>'2097-06-11'],['patient_id'=>$patient[2]]]as$change){$f=$finance();$n=$note($f);$db->table('finances')->where('id',$f)->update($change);$assert(!$dispatch->claimNotification($n,1,$run,'whatsapp')&&$row($n)['status']==='rejected','Changed identity/due date/patient invalidates cached demand');}
   $f=$finance();$n=$note($f);$db->transBegin();try{$dispatch->claimNotification($n,1,$run,'whatsapp');$assert(false,'Nested reminder claim must reject caller transaction');}catch(RuntimeException $e){$checks[]='Nested claim rejected without owning caller transaction';}finally{$assert((int)$db->transDepth===1,'Nested rejection preserves caller ownership');$db->transRollback();}$assert($row($n)['status']==='pending','Nested rejected claim leaves queue unchanged');
   $f=$finance();$n=$note($f);$assert($dispatch->claimNotification($n,1,$run,'whatsapp')&&$row($n)['status']==='processing','Valid unchanged invoice reminder remains claimable');
   $assert(!$dispatch->claimNotification($n,1,$run.'other','whatsapp'),'Already-processing valid reminder cannot double claim');
   $clinicValues=[];foreach($db->query("SELECT setting_key,setting_value FROM settings WHERE clinic_id=2 AND setting_key IN ('currency','date_format')")->getResultArray()as$setting){$clinicValues[$setting['setting_key']]=$setting['setting_value'];}$clinicFormatter=new class($clinicValues)extends \App\Services\SettingsService{public function __construct(array$v){$this->settings=$v;}};
   $f=$finance(['clinic_id'=>2]);$frow=$present($f);$n=$note($f,['clinic_id'=>2,'recipient_id'=>$patient[2]],['amount'=>$clinicFormatter->getCurrencySymbol().number_format(100,2),'due_date'=>$clinicFormatter->formatDate('2097-06-10'),'invoice_number'=>$frow['transaction_id']]);session()->set('active_clinic_id',1);$assert($dispatch->claimNotification($n,2,$run,'whatsapp')&&$row($n)['status']==='processing','Clinic2 reminder uses explicit clinic formatting despite cached clinic1 session');
   $f=$finance();$n=$note($f,['payload_json'=>json_encode(['workflow_key'=>'payment_reminder:not-an-id:'.$run,'workflow_trigger'=>'payment_reminder'])]);$assert(!$dispatch->claimNotification($n,1,$run,'whatsapp')&&$row($n)['status']==='rejected','Malformed payment reminder identity fails closed');
   $n=$note($f,['channel_type'=>'email','recipient_type'=>'external','recipient_id'=>null,'recipient_address'=>'synthetic@example.invalid','payload_json'=>json_encode(['subject'=>'synthetic','message'=>'unrelated'])]);$assert($dispatch->claimNotification($n,1,$run,'email'),'Unrelated email claim behavior preserved');
   $assert((int)$db->transDepth===0&&$db->transStatus()!==false,'All success and rejection paths release owned transaction');
   $assert((int)$db->table('notification_attempts')->countAllResults()===$attemptsBefore+1,'Claims never invoke external transport or create outbound attempts');
   return['status'=>'PASS','synthetic_only'=>true,'run'=>$run,'retained_new_finance_ids'=>$ids,'retained_new_notification_ids'=>$notes,'checks'=>$checks];
  }catch(Throwable$e){throw new RuntimeException(json_encode(['status'=>'FAIL','run'=>$run,'retained_new_finance_ids'=>$ids,'retained_new_notification_ids'=>$notes,'checks'=>$checks,'failure'=>$e->getMessage()],JSON_THROW_ON_ERROR),0,$e);}
 }
}
