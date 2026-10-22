<?php
declare(strict_types=1);
$options=getopt('',['bootstrap:']);$bootstrap=$options['bootstrap']??'';
if(PHP_SAPI!=='cli'||!is_file($bootstrap))exit(2);require $bootstrap;
$identity=$db->query('SELECT DATABASE() db,@@port port,@@datadir datadir,CURRENT_USER() actor')->getRowArray();
if($db->hostname!=='127.0.0.1'||$identity['db']!=='m2c_synthetic_acceptance'||(int)$identity['port']!==3307||!str_contains(str_replace('\\','/',$identity['datadir']),'denta-m2c-20261021/disposable-mariadb/data')||!str_starts_with($identity['actor'],'m2c_test@'))throw new RuntimeException('Disposable identity required.');
$n=0;function ck(bool$v,string$m):void{global$n;if(!$v)throw new RuntimeException($m);$n++;}
function denial(callable$f,string$m):void{try{$f();}catch(DomainException$e){ck(true,$m);return;}throw new RuntimeException($m);}
function ins(string$t,array$r):int{global$db;if(!$db->table($t)->insert($r))throw new RuntimeException('Fixture insert '.$t);return(int)$db->insertID();}
function inv():int{return ins('finances',['clinic_id'=>1,'patient_id'=>1,'transaction_id'=>'CE-'.bin2hex(random_bytes(8)),'transaction_type'=>'invoice','amount'=>'100.00','total_amount'=>'100.00','amount_original'=>'100.00','currency'=>'ILS','exchange_rate'=>'1.00000000','amount_paid'=>'0.00','balance_due'=>'100.00','payment_status'=>'pending','created_at'=>'2026-01-01 00:00:00']);}
function cancel(int$id):void{global$db;if(!$db->table('finances')->where('id',$id)->update(['payment_status'=>'cancelled']))throw new RuntimeException('Owned cancellation fixture failed');}
function payload():array{return ['amount_original'=>'10.00','reason'=>'Owned cancellation/expiry acceptance','idempotency_key'=>bin2hex(random_bytes(16))];}
$original=[];foreach(['finances','finance_payments','provider_payouts','provider_payout_payments','invoice_corrections','invoice_credit_applications','invoice_credit_application_reversals','activity_logs']as$t)$original[$t]=$db->table($t)->orderBy('id')->get()->getResultArray();
$s=new App\Services\InvoiceCorrectionService($db);
$cancelled=inv();cancel($cancelled);denial(fn()=>$s->preview(1,$cancelled,1,payload()),'Cancelled source preview denied');
$source=inv();ins('finance_payments',['clinic_id'=>1,'finance_id'=>$source,'amount'=>'100.00','type'=>'payment','payment_date'=>'2026-01-01','created_at'=>'2026-01-01 12:00:00']);
$p=payload();$p['source_hash']=$s->preview(1,$source,1,$p)['source_hash'];$credit=$s->issue(1,$source,1,$p);
$target=inv();cancel($target);denial(fn()=>$s->linkReplacement(1,$source,$target,1),'Cancelled replacement link denied');
$target2=inv();$s->linkReplacement(1,$source,$target2,1);cancel($target2);
$a=['target_finance_id'=>$target2,'amount_original'=>'10.00','idempotency_key'=>bin2hex(random_bytes(16)),'source_hash'=>$s->getDocument(1,$credit['id'])['source_hash']];
denial(fn()=>$s->allocate(1,$credit['id'],1,$a),'Cancelled replacement funding denied');
$target3=inv();$s->linkReplacement(1,$source,$target3,1);$a['target_finance_id']=$target3;$a['idempotency_key']=bin2hex(random_bytes(16));$a['source_hash']=$s->getDocument(1,$credit['id'])['source_hash'];$application=$s->allocate(1,$credit['id'],1,$a);cancel($target3);
ck((int)$s->getDocument(1,$credit['id'])['id']===(int)$credit['id'],'Historical document readable');
$return=$s->reverseApplication(1,$application['id'],1,['reason'=>'Explicit recovery from owned cancelled replacement','idempotency_key'=>bin2hex(random_bytes(16))]);
ck($return['position']['patient_credit_base']==='10.00','Explicit return restores original liability');ck($db->table('finances')->where('id',$target3)->get()->getRowArray()['payment_status']==='cancelled','Return does not revive target');
// A fresh provider-linked invoice owns its approval history; no existing grant is changed.
foreach([['2026-10-24T23:09:59Z',true],['2026-10-24T23:10:00Z',false],['2026-10-24T23:10:01Z',false]]as[$issueAt,$allowed]){
 $id=inv();ins('provider_payouts',['clinic_id'=>1,'finance_id'=>$id,'provider_share_amount'=>'20.00','provider_paid_amount'=>'5.00','provider_balance_due'=>'15.00','status'=>'partial']);
 $s->clock=static fn()=>new DateTimeImmutable('2026-10-24T22:55:00Z');$p=payload();$p['source_hash']=$s->preview(1,$id,1,$p)['source_hash'];$approval=$s->approve(1,$id,1,$p);$p['approval_id']=$approval['approval_id'];
 $stored=$db->table('invoice_correction_approvals')->where('id',$approval['approval_id'])->get()->getRowArray();ck($stored['expires_at']==='2026-10-24 23:10:00','Expiry persisted in UTC across fall-back');
 $s->clock=static fn()=>new DateTimeImmutable($issueAt);
 if($allowed){$issued=$s->issue(1,$id,1,$p);ck($issued['id']>0,'Approval valid before expiry');}
 else{denial(fn()=>$s->issue(1,$id,1,$p),'Expired approval denied including exact expiry');ck($db->table('invoice_corrections')->where('finance_id',$id)->countAllResults()===0,'Expired approval creates no credit');}
}
foreach([['2026-12-31T22:30:00Z','2027-01-01 00:30:00','OCN-2027-'],['2026-03-27T00:30:00Z','2026-03-27 03:30:00','OCN-2026-'],['2026-10-24T23:30:00Z','2026-10-25 01:30:00','OCN-2026-']]as[$instant,$local,$prefix]){
 $id=inv();$s->clock=static fn()=>new DateTimeImmutable($instant);$p=payload();$p['source_hash']=$s->preview(1,$id,1,$p)['source_hash'];$issued=$s->issue(1,$id,1,$p);$doc=$s->getDocument(1,$issued['id']);ck($doc['issued_at']===$local&&str_starts_with($doc['document_number'],$prefix),'Clinic-local issuance timestamp/year');
 $day=substr($local,0,10);$prior=(new DateTimeImmutable($day))->modify('-1 day')->format('Y-m-d');ck($s->position(1,$id,false,$prior)['credited_base']==='0.00','Prior day excludes later credit');ck($s->position(1,$id,false,$day)['credited_base']==='10.00','Posting day includes credit');
}
foreach($original as$t=>$rows){if(!$rows)continue;$after=$db->table($t)->whereIn('id',array_column($rows,'id'))->orderBy('id')->get()->getResultArray();ck($after===$rows,'Prior history unchanged '.$t);}
echo json_encode(['status'=>'PASS','checks'=>$n,'synthetic_only'=>true,'scope'=>'Cancelled mutations/history/return, UTC approval boundary and DST, clinic-local issuance/year/as-of; fixtures retained']),PHP_EOL;
