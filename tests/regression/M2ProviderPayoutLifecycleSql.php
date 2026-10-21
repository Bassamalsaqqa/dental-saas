<?php
declare(strict_types=1);
// Actual-model/writer SQL regression. All new fixtures roll back; no schema changes.
if (PHP_SAPI !== 'cli') exit(2);
$bootstrap = ($argv[1] ?? '') === '--bootstrap' ? ($argv[2] ?? '') : '';
$privateExpected = '/home/u556956644/.denta-stage-20261017';
if ($bootstrap !== $privateExpected . '/m2-private-bootstrap.php' || realpath($bootstrap) !== $bootstrap || is_link($bootstrap)) throw new RuntimeException('Canonical M2 bootstrap required');
require $bootstrap;
if ($private !== $privateExpected || realpath(HOMEPATH) !== $privateExpected.'/m2-20261020-base6908df7' || $db->database !== 'u556956644_dentstage1017' || $db->username !== 'u556956644_dstage1017' || $db->hostname !== '127.0.0.1' || (int)$db->port !== 3306 || $db->getPrefix() !== '' || (int)$db->transDepth !== 0 || !$db->transStatus()) throw new RuntimeException('M2 isolated connection required');
$identity = $db->query('SELECT guid,domain,synthetic_only FROM _denta_stage_identity WHERE id=1')->getRowArray();
if (($identity['guid']??'') !== 'b60809432ec1d915b0b975aef8484e43' || ($identity['domain']??'') !== 'dentastaging.palsync.net' || (int)($identity['synthetic_only']??0) !== 1) throw new RuntimeException('Synthetic identity required');
helper(['url','form']);
App\Libraries\ApiRuntimeContext::clear();
session()->set(['user_id'=>1,'active_clinic_id'=>1,'global_mode'=>false]);
$db->query('SET SESSION innodb_lock_wait_timeout=5');
$checks=[];$run='M2PP'.bin2hex(random_bytes(5));
$check=static function(bool $ok,string $label)use(&$checks):void{if(!$ok)throw new RuntimeException($label);$checks[]=$label;};
$near=static fn($a,$b):bool=>abs((float)$a-(float)$b)<0.00001;
$insert=static function(string $table,array $row)use($db):int{if(!$db->table($table)->insert($row))throw new RuntimeException('Fixture insert failed '.$table);return (int)$db->insertID();};
$clone=static function(string $table,array $row,array $overrides)use($insert):int{unset($row['id']);return $insert($table,array_replace($row,$overrides));};
$state=static fn(int $fid):array=>[
 'allocations'=>$db->table('provider_payouts')->where('clinic_id',1)->where('finance_id',$fid)->orderBy('id')->get()->getResultArray(),
 'payments'=>$db->query('SELECT ppp.* FROM provider_payout_payments ppp JOIN provider_payouts pp ON pp.id=ppp.provider_payout_id AND pp.clinic_id=ppp.clinic_id WHERE pp.clinic_id=1 AND pp.finance_id=? ORDER BY ppp.id',[$fid])->getResultArray()
];
$invoke=static function(int $fid,int $pid,array $post):void{
 $config=config('App');$request=new CodeIgniter\HTTP\IncomingRequest($config,new CodeIgniter\HTTP\SiteURI($config,'finance/'.$fid),null,new CodeIgniter\HTTP\UserAgent());
 $request=$request->withMethod('POST');$request->setGlobal('post',$post);$request->setGlobal('request',$post);$_POST=$post;$_REQUEST=$post;
 Config\Services::injectMock('request',$request);service('validation')->reset();
 $controller=new App\Controllers\Finance();$controller->initController($request,new CodeIgniter\HTTP\Response($config),service('logger'));
 $method=new ReflectionMethod($controller,'upsertProviderPayouts');$method->invoke($controller,1,$fid,$pid,200.0);
};
$model=new App\Models\ProviderPayoutModel();
$baseline=$model->getStatsByClinic(1);
$baseUser=$model->getSummaryByProviderUserId(1,1);
$baseDirectory=$model->getDirectoryProviderStats(1);
$sum=static fn(array $rows,string $key):float=>array_sum(array_map(static fn($row)=>(float)($row[$key]??0),$rows));
$baseBreakdown=$sum($model->getProviderBreakdownByClinic(1),'provider_balance_total');
$baseDirectoryRows=$sum($model->getDirectoryProvidersByClinic(1),'provider_balance_total');
$other=(int)($db->table('clinics')->select('id')->where('id !=',1)->orderBy('id')->get(1)->getRowArray()['id']??0);
if(!$other)throw new RuntimeException('Second synthetic clinic required (no silent tenant-test skip)');
$otherBefore=$model->getStatsByClinic($other);
$beforeCounts=[];foreach(['patients','finances','provider_payouts','provider_payout_payments']as$table)$beforeCounts[$table]=$db->table($table)->countAllResults();
try{
 if(!$db->transBegin())throw new RuntimeException('Begin failed');
 $db->query('SELECT id FROM clinics WHERE id=1 FOR UPDATE');
 $p=$db->table('patients')->where('clinic_id',1)->orderBy('id')->get(1)->getRowArray();
 $f=$db->table('finances')->where('clinic_id',1)->orderBy('id')->get(1)->getRowArray();
 if(!$p||!$f||!(new App\Models\ClinicUserModel())->findActiveMembershipByClinicAndUser(1,1))throw new RuntimeException('Synthetic parent/actor fixtures unavailable');
 $pid=$clone('patients',$p,['patient_id'=>$run,'first_name'=>'Synthetic','last_name'=>$run,'email'=>null,'phone'=>'0590000000']);
 $fid=$clone('finances',$f,['patient_id'=>$pid,'examination_id'=>null,'transaction_id'=>$run,'transaction_type'=>'payment','amount'=>'200.00','transaction_date'=>'2026-01-01','currency'=>'ILS','description'=>$run]);
 $db->query('SELECT id FROM patients WHERE id=? AND clinic_id=1 FOR UPDATE',[$pid]);
 $db->query('SELECT id FROM finances WHERE id=? AND clinic_id=1 FOR UPDATE',[$fid]);
 $common=['clinic_id'=>1,'finance_id'=>$fid,'patient_id'=>$pid,'provider_type'=>'user','provider_user_id'=>1,'provider_name_snapshot'=>null,'split_mode'=>'percent','gross_amount'=>'200.00','clinic_share_amount'=>'100.00','currency'=>'ILS','created_by'=>1,'created_at'=>'2026-01-01 12:00:00','updated_at'=>'2026-01-01 12:00:00'];
 $active=$insert('provider_payouts',array_replace($common,['provider_type'=>'guest','provider_user_id'=>null,'provider_name_snapshot'=>$run,'split_value'=>'20.00','provider_share_amount'=>'40.00','provider_paid_amount'=>'20.00','provider_balance_due'=>'20.00','status'=>'partial','notes'=>$run]));
 $void=$insert('provider_payouts',array_replace($common,['split_value'=>'30.00','provider_share_amount'=>'60.00','provider_paid_amount'=>'10.00','provider_balance_due'=>'50.00','status'=>'void','notes'=>$run]));
 foreach([[$active,'5.00','2026-02-01'],[$void,'7.00','2026-03-01']]as[$id,$amount,$date])$insert('provider_payout_payments',['clinic_id'=>1,'provider_payout_id'=>$id,'amount'=>$amount,'payment_method'=>'cash','liquidity_account'=>'cash','paid_date'=>$date,'notes'=>$run,'created_by'=>1]);
 $stats=$model->getStatsByClinic(1);
 foreach(['total_provider_balance'=>20,'total_provider_share'=>100,'total_provider_paid'=>30,'total_provider_paid_cash'=>12,'total_gross_amount'=>200,'total_clinic_share'=>100]as$key=>$delta)$check($near($stats[$key]-$baseline[$key],$delta),'Actual aggregate delta '.$key);
 $user=$model->getSummaryByProviderUserId(1,1);
 $check($near($user['provider_balance_total'],$baseUser['provider_balance_total']),'User outstanding excludes positive legacy void balance');
 $check($near($user['provider_share_total']-$baseUser['provider_share_total'],60)&&$near($user['provider_paid_total']-$baseUser['provider_paid_total'],10),'User historical void share/paid retained');
 $check($near($model->getDirectoryProviderStats(1)['outstanding_balance']-$baseDirectory['outstanding_balance'],20),'Directory summary outstanding');
 $check($near($sum($model->getProviderBreakdownByClinic(1),'provider_balance_total')-$baseBreakdown,20),'Provider breakdown outstanding');
 $check($near($sum($model->getDirectoryProvidersByClinic(1),'provider_balance_total')-$baseDirectoryRows,20),'Directory rows outstanding');
 $voidBucket=array_values(array_filter($model->getStatusSummaryByClinic(1),static fn($row)=>$row['status']==='void'));
 $check(count($voidBucket)===1&&$near($voidBucket[0]['balance_due_total'],0),'Void bucket retains history with zero outstanding');
 $trail=(new App\Services\MoneyTrailService($db))->forPatient(1,$pid);
 $check($near($trail['summary']['provider_remaining'],20)&&$near($trail['summary']['provider_share'],100)&&$near($trail['summary']['provider_paid'],30),'Actual patient MoneyTrail preserves history/excludes void debt');
 $jan=$model->getStatsByClinic(1,'2026-01-01','2026-01-01');
 $feb=$model->getStatsByClinic(1,'2026-02-01','2026-02-01');
 // Use before/after differential for dates, avoiding assumptions about pre-existing synthetic rows.
 $db->query('SAVEPOINT date_probe');
 $db->table('provider_payout_payments')->where('provider_payout_id',$active)->where('clinic_id',1)->update(['paid_date'=>'2026-04-01']);
 $febAfter=$model->getStatsByClinic(1,'2026-02-01','2026-02-01');
 $check($near($feb['total_provider_paid_cash']-$febAfter['total_provider_paid_cash'],5),'Cash follows payment date');
 $check($near($jan['total_provider_share'],$model->getStatsByClinic(1,'2026-01-01','2026-01-01')['total_provider_share']),'Accrual independent from cash date');
 $db->query('ROLLBACK TO SAVEPOINT date_probe');
 $check($model->getStatsByClinic($other)===$otherBefore,'Other clinic exact aggregates unchanged');
 $check((new App\Services\MoneyTrailService($db))->forPatient($other,$pid)['provider_payouts']===[],'Cross-clinic patient MoneyTrail reveals no allocations');
 $post=['provider_split_enabled'=>'1','provider_type'=>['guest','user'],'provider_user_id'=>['0','1'],'provider_name_snapshot'=>[$run,''],'provider_split_mode'=>['percent','percent'],'provider_split_value'=>['20','30'],'provider_mark_paid_now'=>['0','0'],'provider_paid_amount'=>['0','0'],'provider_split_notes'=>[$run,$run],'provider_payout_id'=>[(string)$active,(string)$void]];
 $voidBefore=$db->table('provider_payouts')->where('id',$void)->get()->getRowArray();$paymentsBefore=$state($fid)['payments'];
 $invoke($fid,$pid,$post);
 $check($db->table('provider_payouts')->where('id',$void)->get()->getRowArray()===$voidBefore&&$state($fid)['payments']===$paymentsBefore,'Equivalent form repost leaves exact void row and payment history untouched');
 $activeAfter=$db->table('provider_payouts')->where('id',$active)->get()->getRowArray();
 $check($activeAfter['status']==='partial'&&$near($activeAfter['provider_paid_amount'],20)&&$near($activeAfter['provider_balance_due'],20),'Active allocation still writable, cached historical paid above ledger retained');
 foreach(['share','notes','inline-payment','remove','disable','wrong-parent-id']as$case){
  $candidate=$post;
  if($case==='share')$candidate['provider_split_value'][1]='31';
  if($case==='notes')$candidate['provider_split_notes'][1]='rewrite';
  if($case==='inline-payment'){$candidate['provider_mark_paid_now'][1]='1';$candidate['provider_paid_amount'][1]='1';}
  if($case==='remove')foreach($candidate as$key=>$value)if(is_array($value))$candidate[$key]=array_slice($value,0,1);
  if($case==='disable')$candidate['provider_split_enabled']='0';
  if($case==='wrong-parent-id')$candidate['provider_payout_id'][1]='2147483647';
  $before=$state($fid);$db->query('SAVEPOINT rejected_mutation');$threw=false;
  try{$invoke($fid,$pid,$candidate);}catch(RuntimeException|DomainException $e){$threw=true;}
  $db->query('ROLLBACK TO SAVEPOINT rejected_mutation');
  $check($threw&&$state($fid)===$before,'Rejected '.$case.' rolls back allocation/payment history');
 }
 foreach(['en','ar']as$locale){$words=require APPPATH.'Language/'.$locale.'/Finance.php';foreach(['voidProviderAllocationProtected','providerVoidBalanceHelp']as$key)$check(isset($words[$key])&&strlen($words[$key])>10&& !str_contains($words[$key],'???'),$locale.' translated '.$key);}
 $check((int)$db->transDepth===1&&$db->transStatus(),'Actual writer preserves healthy caller transaction');
 if(!$db->transRollback())throw new RuntimeException('Rollback failed');
 foreach($beforeCounts as$table=>$count)$check($db->table($table)->countAllResults()===$count,'No retained fixture rows '.$table);
 $check((int)$db->transDepth===0&&$db->transStatus(),'No transaction leak');
 echo json_encode(['status'=>'PASS','run'=>$run,'checks'=>$checks,'limits'=>['Actual SQL/model/controller-method tests; no HTTP/filter claim','Single-currency ILS fixture; no FX/refund/reversal policy certification','Fixtures rolled back; AUTO_INCREMENT advancement is expected']],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";
}catch(Throwable $e){if((int)$db->transDepth>0)$db->transRollback();echo json_encode(['status'=>'FAIL','run'=>$run,'error'=>$e->getMessage(),'file'=>basename($e->getFile()),'line'=>$e->getLine(),'checks'=>$checks],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";exit(1);}
