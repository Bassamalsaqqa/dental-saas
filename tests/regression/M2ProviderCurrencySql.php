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
$checks=[];$run='M2CC'.bin2hex(random_bytes(5));
$check=static function(bool $ok,string $label)use(&$checks):void{if(!$ok)throw new RuntimeException($label);$checks[]=$label;};
$insert=static function(string $table,array $row)use($db):int{if(!$db->table($table)->insert($row))throw new RuntimeException('Fixture insert failed '.$table);return (int)$db->insertID();};
$clone=static function(string $table,array $row,array $overrides)use($insert):int{unset($row['id']);return $insert($table,array_replace($row,$overrides));};
$model=new App\Models\ProviderPayoutModel($db);
$before=[];foreach(['patients','finances','provider_payouts','provider_payout_payments']as$table)$before[$table]=$db->table($table)->countAllResults();
$other=(int)($db->table('clinics')->select('id')->where('id !=',1)->orderBy('id')->get(1)->getRowArray()['id']??0);
if(!$other)throw new RuntimeException('Second synthetic clinic required');
$otherBefore=$model->getStatsByClinic($other);
try{
 if(!$db->transBegin())throw new RuntimeException('Begin failed');
 $db->query('SELECT id FROM clinics WHERE id=1 FOR UPDATE');
 $patient=$db->table('patients')->where('clinic_id',1)->orderBy('id')->get(1)->getRowArray();
 $finance=$db->table('finances')->where('clinic_id',1)->orderBy('id')->get(1)->getRowArray();
 if(!$patient||!$finance||!(new App\Models\ClinicUserModel($db))->findActiveMembershipByClinicAndUser(1,1))throw new RuntimeException('Canonical synthetic parent/actor required');
 $pid=$clone('patients',$patient,['patient_id'=>$run,'first_name'=>'Synthetic','last_name'=>$run,'email'=>null]);
 $date='2098-06-17';$base=$model->getStatsByClinic(1,$date,$date);
 $fids=[];$ids=[];
 foreach(['ILS','USD','']as$i=>$currency){
  $fid=$clone('finances',$finance,['patient_id'=>$pid,'examination_id'=>null,'transaction_id'=>$run.$i,'transaction_type'=>'invoice','amount'=>'200.00','amount_original'=>'50.00','exchange_rate'=>'4.00','currency'=>'USD','transaction_date'=>$date,'created_at'=>$date.' 12:00:00','description'=>$run]);$fids[]=$fid;
  $ids[]=$insert('provider_payouts',['clinic_id'=>1,'finance_id'=>$fid,'patient_id'=>$pid,'provider_type'=>'guest','provider_user_id'=>null,'provider_name_snapshot'=>$run.$i,'split_mode'=>'fixed','split_value'=>'40.00','gross_amount'=>'200.00','provider_share_amount'=>'40.00','clinic_share_amount'=>'160.00','provider_paid_amount'=>'10.00','provider_balance_due'=>'30.00','status'=>$i===1?'void':'partial','currency'=>$currency,'created_by'=>1,'notes'=>$run]);
  $insert('provider_payout_payments',['clinic_id'=>1,'provider_payout_id'=>end($ids),'amount'=>'7.00','payment_method'=>'cash','liquidity_account'=>'safe_cash','paid_date'=>$date,'created_by'=>1,'notes'=>$run]);
 }
 $stats=$model->getStatsByClinic(1,$date,$date);
 $check($stats['total_provider_balance']===null&&$stats['total_provider_paid_cash']===null,'Mixed/unknown flat totals unavailable');
 $check(abs($stats['currency_buckets']['ILS']['total_provider_balance']-($base['currency_buckets']['ILS']['total_provider_balance']??0)-30)<0.00001,'ILS native outstanding bucket');
 $check(abs($stats['currency_buckets']['USD']['total_provider_balance']-($base['currency_buckets']['USD']['total_provider_balance']??0))<0.00001,'Void foreign balance excluded');
 $check(abs($stats['currency_buckets']['USD']['total_provider_paid_cash']-($base['currency_buckets']['USD']['total_provider_paid_cash']??0)-7)<0.00001,'Void foreign historical cash retained in USD');
 $check(isset($stats['currency_buckets']['UNKNOWN'])&&$stats['currency_unsupported'],'Missing currency explicitly unsupported');
 $row=$model->getRowsByFinanceId(1,$fids[0])[0];
 $check($row['currency']==='ILS'&&(float)$row['gross_amount']===200.0,'Provider base denomination is independent of original USD finance and its preconverted amount');
 $single=App\Services\ProviderCurrencySummary::summarize([$row]);
 $check($single['total_provider_balance']===30.0&&$single['currency']==='ILS','Single known currency flat compatibility');
 $collision=$model->getRowsByFinanceId(1,$fids[1])[0];unset($collision['id']);$collision['finance_id']=$fids[0];$collision['provider_name_snapshot']=$run.'collision';$collision['status']='partial';$insert('provider_payouts',$collision);
 $mixed=$model->getStatsByClinic(1,$date,$date);
 $check(in_array((string)$fids[0],array_map('strval',$mixed['inconsistent_finance_ids']),true)&&$mixed['currency_buckets']['ILS']['total_gross_amount']===null&&$mixed['currency_buckets']['USD']['total_clinic_share']===null,'Conflicting currencies on same finance never double claim gross/clinic share');
 $trail=(new App\Services\MoneyTrailService($db))->forPatient(1,$pid);
 $check($trail['summary']['provider_remaining']===null&&isset($trail['summary']['provider_currency_summary']['currency_buckets']['USD']),'MoneyTrail retains native buckets');
 $directory=$model->getDirectoryProvidersByClinic(1,$run);
 $check(count($directory)===4,'Stable directory identities retained across native currencies');
 $check($model->getStatsByClinic($other)===$otherBefore,'Other tenant unchanged');
 $config=config('App');$request=new CodeIgniter\HTTP\IncomingRequest($config,new CodeIgniter\HTTP\SiteURI($config,'finance/reports'),null,new CodeIgniter\HTTP\UserAgent());
 foreach([['Finance','buildFinancialLedger'],['Reports','buildFinanceCashLedger']]as[$class,$method]){
  $controllerName='App\\Controllers\\'.$class;$controller=new $controllerName();$controller->initController($request,new CodeIgniter\HTTP\Response($config),service('logger'));
  $reflection=new ReflectionMethod($controller,$method);$ledger=$reflection->invoke($controller,1,$date,$date);$rows=$class==='Finance'?$ledger['rows']:$ledger;
  $native=array_values(array_filter($rows,static fn($r)=>in_array((int)($r['provider_payout_id']??0),$ids,true)));
  $check(count($native)===3,$class.' retains every owned native cash row');
  $ilsRows=array_values(array_filter($native,static fn($r)=>$r['currency']==='ILS'));$check(count($ilsRows)===1&&(float)$ilsRows[0]['amount_base']===7.0,$class.' known base native cash remains available');
  foreach($native as$r){if($r['currency']==='USD'||$r['currency']==='UNKNOWN')$check($r['amount_base']===null&&(float)$r['amount_original']===7.0,$class.' never relabels foreign/unknown native cash as base');}
 }
 foreach(['en','ar']as$locale){$words=require APPPATH.'Language/'.$locale.'/Finance.php';$check(isset($words['providerCurrencyWarning'],$words['providerCurrencyUnavailable'],$words['providerCurrencyUnknown']),$locale.' currency warning keys');}
 if(!$db->transStatus()||!$db->transRollback())throw new RuntimeException('Rollback failed');
 foreach($before as$table=>$count)$check($db->table($table)->countAllResults()===$count,'No retained fixtures '.$table);
 echo json_encode(['status'=>'PASS','checks'=>$checks,'limits'=>['No HTTP authorization claim; controller ledger methods use actual SQL','No undocumented conversion; unsupported currencies reported separately','Fixtures roll back; AUTO_INCREMENT advancement expected']],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";
}catch(Throwable$e){if((int)$db->transDepth>0)$db->transRollback();echo json_encode(['status'=>'FAIL','error'=>$e->getMessage(),'line'=>$e->getLine(),'checks'=>$checks],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";exit(1);}
