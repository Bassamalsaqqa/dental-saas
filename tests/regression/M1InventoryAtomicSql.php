<?php
declare(strict_types=1);
// Reviewer-owned actual-service SQL harness. Run only through the guarded private M1 bootstrap.
if (PHP_SAPI !== 'cli') exit(2);
$bootstrap=($argv[1]??'')==='--bootstrap'?($argv[2]??''):'';
if($bootstrap==='')throw new RuntimeException('Explicit --bootstrap /canonical/private/bootstrap.php is required');
$privateExpected='/home/u556956644/.denta-stage-20261017';
if(!str_starts_with($bootstrap,$privateExpected.'/')||realpath($bootstrap)!==$bootstrap||is_link($bootstrap))throw new RuntimeException('Canonical private bootstrap required');
require $bootstrap;
if($private!==$privateExpected||realpath(HOMEPATH)!==$privateExpected.'/m1-20261020-base8499042'||$db->database!=='u556956644_dentstage1017'||$db->username!=='u556956644_dstage1017'||$db->hostname!=='127.0.0.1'||(int)$db->port!==3306||$db->getPrefix()!==''||(int)$db->transDepth!==0||!$db->transStatus())throw new RuntimeException('Private M1 synthetic connection rejected');
$identity=$db->query('SELECT guid,domain,synthetic_only FROM _denta_stage_identity WHERE id=1')->getRowArray();
if(($identity['guid']??'')!=='b60809432ec1d915b0b975aef8484e43'||($identity['domain']??'')!=='dentastaging.palsync.net'||(int)($identity['synthetic_only']??0)!==1)throw new RuntimeException('Synthetic identity rejected');
$db->query('SET SESSION innodb_lock_wait_timeout=5');
helper(['url','form']);
App\Libraries\ApiRuntimeContext::clear();session()->set(['user_id'=>1,'active_clinic_id'=>1,'global_mode'=>false]);
if((int)settings()->get('finance_auto_post_inventory_cogs',1)!==1)throw new RuntimeException('Existing synthetic automatic COGS must be ON; no settings changes permitted');
final class InventoryHoldLedger extends App\Services\FinanceLedgerService{
 public function upsertInventoryUsageCogsExpense(int $clinicId,int $usageId,array $usageRow,?int $patientId,?int $actorUserId,array $context=[]):?int{$id=parent::upsertInventoryUsageCogsExpense($clinicId,$usageId,$usageRow,$patientId,$actorUserId,$context);usleep(700000);return $id;}
}
final class InventoryFaultLedger extends App\Services\FinanceLedgerService{
 public function upsertInventoryUsageCogsExpense(int $clinicId,int $usageId,array $usageRow,?int $patientId,?int $actorUserId,array $context=[]):?int{parent::upsertInventoryUsageCogsExpense($clinicId,$usageId,$usageRow,$patientId,$actorUserId,$context);throw new RuntimeException('Owned test fault after actual COGS insert');}
}
final class InventoryFaultMovement extends App\Services\InventoryMovementService{
 public function record(int $clinicId,int $inventoryId,string $movementType,int $quantityChange,int $quantityBefore,int $quantityAfter,?float $unitCost=null,?string $referenceType=null,?int $referenceId=null,?string $notes=null,?int $createdBy=null,?int $batchId=null):int{parent::record($clinicId,$inventoryId,$movementType,$quantityChange,$quantityBefore,$quantityAfter,$unitCost,$referenceType,$referenceId,$notes,$createdBy,$batchId);throw new RuntimeException('Owned test fault after actual movement insert');}
}
function serviceWith(?object $ledger=null,?object $movement=null):App\Services\InventoryUsageService{$service=new App\Services\InventoryUsageService();foreach(['financeLedgerService'=>$ledger,'movementService'=>$movement]as$name=>$value){if($value!==null){$p=new ReflectionProperty($service,$name);$p->setValue($service,$value);}}return $service;}
function payload(int $id,string $notes,int $quantity=4):array{return ['usage_date'=>date('Y-m-d'),'notes'=>$notes,'items'=>[['item_id'=>$id,'quantity'=>$quantity]]];}
function snapshot($db,int $id):array{$s=['item'=>$db->table('inventory')->where('id',$id)->get()->getRowArray()];foreach(['inventory_usage','inventory_stock_movements','finances']as$t)$s[$t]=(int)$db->table($t)->countAllResults();return $s;}
$action=$argv[3]??'run';
if($action==='worker'){
 $statePath=$argv[4]??'';$index=$argv[5]??'';
 if(!preg_match('/^'.preg_quote($privateExpected,'/').'\/m1-inventory-[a-f0-9]{12}\.json$/D',$statePath)||realpath($statePath)!==$statePath||!in_array($index,['0','1'],true))throw new RuntimeException('Invalid owned worker state');
 $state=json_decode(file_get_contents($statePath),true,512,JSON_THROW_ON_ERROR);$barrier=$statePath.'.start';$deadline=microtime(true)+15;
 file_put_contents($statePath.'.ready'.$index,'ready',LOCK_EX);while(!is_file($barrier)){if(microtime(true)>$deadline)throw new RuntimeException('Start barrier timeout');usleep(10000);}
 $started=microtime(true);$result=serviceWith(new InventoryHoldLedger())->record(1,payload((int)$state['race_item'],$state['run'].' race'.$index),1);
 file_put_contents($statePath.'.result'.$index,json_encode(['result'=>$result,'started'=>$started,'finished'=>microtime(true)],JSON_THROW_ON_ERROR),LOCK_EX);exit(0);
}
$run='M1INV_'.bin2hex(random_bytes(6));$checks=[];$owned=[];$processes=[];
$check=static function(bool $ok,string $label)use(&$checks):void{if(!$ok)throw new RuntimeException($label);$checks[]=$label;};
try{
 foreach([1,2]as$clinicId)if(!$db->table('clinics')->where('id',$clinicId)->get()->getRowArray())throw new RuntimeException('Existing synthetic clinics1/2 required');
 $make=static function(int $clinic,string $name)use($db,$run,&$owned):int{if(!$db->table('inventory')->insert(['clinic_id'=>$clinic,'item_name'=>$run.' '.$name,'category'=>'consumables','quantity'=>5,'min_quantity'=>0,'unit_price'=>'2.75','unit'=>'pieces','status'=>'active','created_by'=>1,'notes'=>$run]))throw new RuntimeException('Owned fixture insert failed');$id=(int)$db->insertID();$owned[]=$id;return $id;};
 $race=$make(1,'race');$fault=$make(1,'fault');$foreign=$make(2,'foreign');
 $statePath=$privateExpected.'/m1-inventory-'.substr($run,6).'.json';file_put_contents($statePath,json_encode(['run'=>$run,'race_item'=>$race,'owned_inventory_ids'=>$owned],JSON_THROW_ON_ERROR),LOCK_EX);chmod($statePath,0600);
 foreach(['0','1']as$i){$command=[PHP_BINARY,__FILE__,'--bootstrap',$bootstrap,'worker',$statePath,$i];$p=proc_open($command,[0=>['pipe','r'],1=>['file',$statePath.'.stdout'.$i,'a'],2=>['file',$statePath.'.stderr'.$i,'a']],$pipes);if(!is_resource($p))throw new RuntimeException('Cannot start actual PHP worker');fclose($pipes[0]);$processes[]=$p;}
 $deadline=microtime(true)+15;while(!is_file($statePath.'.ready0')||!is_file($statePath.'.ready1')){if(microtime(true)>$deadline)throw new RuntimeException('Workers not ready');usleep(10000);}file_put_contents($statePath.'.start','go',LOCK_EX);
 foreach($processes as$p)$check(proc_close($p)===0,'Actual independent PHP worker exits successfully');$processes=[];
 $results=[];foreach(['0','1']as$i)$results[]=json_decode(file_get_contents($statePath.'.result'.$i),true,512,JSON_THROW_ON_ERROR);
 $success=array_values(array_filter($results,static fn($r)=>($r['result']['success']??false)===true));$check(count($success)===1,'Two real concurrent consumes of4 against stock5 allow exactly one success');
 $check(max(array_column($results,'started'))<min(array_column($results,'finished')),'Independent service requests actually overlap');
 $usageId=(int)$success[0]['result']['usage_id'];$stored=$db->table('inventory')->where('id',$race)->get()->getRowArray();$check((int)$stored['quantity']===1,'Concurrent consumption leaves exact nonnegative stock1');
 $movements=$db->table('inventory_stock_movements')->where('inventory_id',$race)->where('reference_type','inventory_usage')->get()->getResultArray();$check(count($movements)===1&&(int)$movements[0]['quantity_before']===5&&(int)$movements[0]['quantity_after']===1&&(int)$movements[0]['quantity_change']===-4,'One committed movement reconciles stock exactly');
 $finance=$db->table('finances')->where('clinic_id',1)->where('source_entity_type','inventory_usage')->where('source_entity_id',$usageId)->get()->getResultArray();$check(count($finance)===1&&(float)$finance[0]['amount']===11.0,'Successful usage and noncash COGS commit together once');
 $usageRow=$db->table('inventory_usage')->where('id',$usageId)->get()->getRowArray();$financeBefore=$finance[0];$repost=(new App\Services\FinanceLedgerService())->upsertInventoryUsageCogsExpense(1,$usageId,$usageRow,null,1);$check($repost===(int)$financeBefore['id']&&$db->table('finances')->where('id',$repost)->get()->getRowArray()===$financeBefore,'Exact equivalent COGS replay retains existing ID and every history field unchanged');
 foreach(['ledger','movement']as$failure){$before=snapshot($db,$fault);$service=$failure==='ledger'?serviceWith(new InventoryFaultLedger()):serviceWith(null,new InventoryFaultMovement());$result=$service->record(1,payload($fault,$run.' fault-'.$failure),1);$check(!($result['success']??false),'Injected '.$failure.' error fails closed');$check(snapshot($db,$fault)===$before,'Injected '.$failure.' error preserves stock and all usage/movement/finance rows');$check((int)$db->transDepth===0,'Failure closes only the owned transaction');}
 // Existing treatment callers own a transaction; usage may join but cannot commit or roll it back.
 $before=snapshot($db,$fault);$db->transBegin();$depth=(int)$db->transDepth;$nested=serviceWith()->record(1,payload($fault,$run.' caller-owned',1),1);$check(($nested['success']??false)&&(int)$db->transDepth===$depth,'Usage joins caller transaction without closing or independently committing it');$db->transRollback();$check(snapshot($db,$fault)===$before,'Caller rollback removes successful nested usage, movement, COGS and stock change');
 $before=snapshot($db,$fault);$db->transBegin();$depth=(int)$db->transDepth;$nested=serviceWith(new InventoryFaultLedger())->record(1,payload($fault,$run.' caller-fault',1),1);$check(!($nested['success']??false)&&(int)$db->transDepth===$depth,'Ledger failure leaves caller transaction ownership intact');$db->transRollback();$check(snapshot($db,$fault)===$before,'Caller rollback after injected failure preserves all preexisting SQL state');
 $before=snapshot($db,$foreign);$result=serviceWith()->record(1,payload($foreign,$run.' foreign'),1);$check(!($result['success']??false)&&snapshot($db,$foreign)===$before,'Cross-clinic inventory rejected without writes');
 foreach(['1.5','4oops',-1,0,[],true]as$invalid){$before=snapshot($db,$fault);$p=payload($fault,$run.' malformed');$p['items'][0]['quantity']=$invalid;$r=serviceWith()->record(1,$p,1);$check(!($r['success']??false)&&snapshot($db,$fault)===$before,'Malformed quantity '.get_debug_type($invalid).' is rejected atomically');}
 // Duplicate IDs must aggregate before the stock decision; a partial first-line consume must never persist.
 $before=snapshot($db,$fault);$p=payload($fault,$run.' duplicate-overstock');$p['items']=[['item_id'=>$fault,'quantity'=>3],['item_id'=>$fault,'quantity'=>3]];$r=serviceWith()->record(1,$p,1);$check(!($r['success']??false)&&snapshot($db,$fault)===$before,'Duplicate item lines are aggregated and overstock fails without partial history');
 // A valid duplicate-line request joins the caller transaction and is rolled back after inspection.
 $before=snapshot($db,$fault);$db->transBegin();$p=payload($fault,$run.' duplicate-valid');$p['items']=[['item_id'=>$fault,'quantity'=>1],['item_id'=>$fault,'quantity'=>2]];$r=serviceWith()->record(1,$p,1);$check(($r['success']??false)&&(int)$db->table('inventory')->where('id',$fault)->get()->getRowArray()['quantity']===2&&count($r['usage_data']??[])===1,'Equivalent duplicate item lines become one exact aggregate consume');$db->transRollback();$check(snapshot($db,$fault)===$before,'Duplicate aggregate inspection leaves no usage or financial history mutation');
 foreach(['1.5','4oops',-1,0,[],true,'2147483648']as$invalid){$before=snapshot($db,$fault);$p=payload($fault,$run.' malformed-id');$p['items'][0]['item_id']=$invalid;$r=serviceWith()->record(1,$p,1);$check(!($r['success']??false)&&snapshot($db,$fault)===$before,'Malformed or overflowing item ID '.get_debug_type($invalid).' is rejected atomically');}
 foreach(['2147483648','9223372036854775808']as$invalid){$before=snapshot($db,$fault);$p=payload($fault,$run.' overflow');$p['items'][0]['quantity']=$invalid;$r=serviceWith()->record(1,$p,1);$check(!($r['success']??false)&&snapshot($db,$fault)===$before,'Overflow quantity '.$invalid.' is rejected atomically');}
 $before=snapshot($db,$fault);session()->set('active_clinic_id',2);$r=serviceWith()->record(1,payload($fault,$run.' wrong-context'),1);session()->set('active_clinic_id',1);$check(!($r['success']??false)&&snapshot($db,$fault)===$before,'Explicit clinic must match authoritative active tenant context before writes');
 foreach(['-1.00','99999999.99']as$legacyCost){$before=snapshot($db,$fault);$db->transBegin();$db->table('inventory')->where('id',$fault)->update(['unit_price'=>$legacyCost]);$adjusted=snapshot($db,$fault);$p=payload($fault,$run.' invalid-cost',2);if($legacyCost==='-1.00')$p['items']=[['item_id'=>$fault,'quantity'=>1],['item_id'=>$race,'quantity'=>1]];$r=serviceWith()->record(1,$p,1);$check(!($r['success']??false)&&snapshot($db,$fault)===$adjusted,'Invalid negative or overflowing accounting cost fails closed before ledger/stock writes');$db->transRollback();$check(snapshot($db,$fault)===$before,'Legacy cost inspection preserves original row and all history');}
 $check((int)$db->transDepth===0&&$db->transStatus(),'Caller connection ends healthy with no active transaction');
 echo json_encode(['status'=>'PASS','run'=>$run,'checks'=>$checks,'retained_owned_inventory_ids'=>$owned,'retained_usage_id'=>$usageId,'retained_cogs_id'=>(int)$finance[0]['id'],'worker_results'=>$results,'limits'=>'Real service two-process last-stock race and post-write failure injection. No claim yet for receipt/adjustment/browser writer parity, lot allocation or existing history reconciliation.'],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";
}catch(Throwable$e){if((int)$db->transDepth>0)$db->transRollback();echo json_encode(['status'=>'FAIL','run'=>$run,'error'=>$e->getMessage(),'checks'=>$checks,'retained_owned_inventory_ids'=>$owned],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";exit(1);}finally{App\Libraries\ApiRuntimeContext::clear();}
