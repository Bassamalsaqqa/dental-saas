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

// Actual controller methods and shared model path, synthetic inventory owned by this run only.
// This is not a claim about HTTP filters: route authorization is unchanged and reviewed separately.
function callStockController(string $action,int $id,array $data,bool $post=false):array{
 $config=config('App');$request=new CodeIgniter\HTTP\IncomingRequest($config,new CodeIgniter\HTTP\SiteURI($config,'inventory/'.$id),$post?null:json_encode($data,JSON_THROW_ON_ERROR),new CodeIgniter\HTTP\UserAgent());
 $request=$request->withMethod('POST');$request->setHeader('Accept','application/json');$request->setHeader('Content-Type',$post?'application/x-www-form-urlencoded':'application/json');
 if($post){session()->remove(['error','errors','success']);$request->setGlobal('post',$data);$request->setGlobal('request',$data);$_POST=$data;$_REQUEST=$data;service('validation')->reset();}
 Config\Services::injectMock('request',$request);$response=new CodeIgniter\HTTP\Response($config);$controller=new App\Controllers\Inventory();$controller->initController($request,$response,service('logger'));$r=$controller->$action($id);
 if($post)return ['status'=>$r->getStatusCode(),'error'=>session()->getFlashdata('error'),'errors'=>session()->getFlashdata('errors'),'success'=>session()->getFlashdata('success')];
 return json_decode($r->getBody(),true,512,JSON_THROW_ON_ERROR);
}
function stockState($db,int $id):array{$out=['item'=>$db->table('inventory')->where('id',$id)->get()->getRowArray()];foreach(['inventory_stock_movements','inventory_batches']as$t)$out[$t]=$db->table($t)->where('inventory_id',$id)->orderBy('id')->get()->getResultArray();return $out;}

$run='M1PARENT_'.bin2hex(random_bytes(6));$checks=[];$ids=[];
$check=static function(bool$ok,string$label)use(&$checks):void{if(!$ok)throw new RuntimeException($label);$checks[]=$label;};
try{
 // Existing synthetic treatment is only read/locked. All mutation probes roll back caller-owned transactions.
 $t=$db->table('treatments')->where('clinic_id',1)->orderBy('id')->get(1)->getRowArray();
 if(!$t){$patient=$db->table('patients')->where('clinic_id',1)->orderBy('id')->get(1)->getRowArray();if(!$patient)throw new RuntimeException('Synthetic patient required');$model=new App\Models\TreatmentModel();if(!$model->insert(['clinic_id'=>1,'patient_id'=>$patient['id'],'treatment_type'=>'other','treatment_description'=>$run.' synthetic parent fixture','cost'=>'0.00','start_date'=>date('Y-m-d'),'estimated_duration'=>30,'status'=>'active','created_by'=>1]))throw new RuntimeException('Owned synthetic treatment fixture failed: '.json_encode($model->errors()));$t=$db->table('treatments')->where('id',$model->getInsertID())->get()->getRowArray();}
 $db->table('inventory')->insert(['clinic_id'=>1,'item_name'=>$run,'category'=>'consumables','quantity'=>5,'min_quantity'=>0,'unit_price'=>'2.75','unit'=>'pieces','status'=>'active','created_by'=>1,'notes'=>$run]);$id=(int)$db->insertID();$ids[]=$id;
 $before=stockState($db,$id);$counts=[];foreach(['inventory_usage','finances','treatments']as$table)$counts[$table]=$db->table($table)->countAllResults();
 $db->transBegin();$r=(new App\Services\InventoryUsageService())->record(1,['usage_date'=>date('Y-m-d'),'treatment_id'=>$t['id'],'items'=>[['item_id'=>$id,'quantity'=>1]],'notes'=>$run],1);
 $check(($r['success']??false)&&(int)$db->transDepth===1,'Treatment-linked usage joins caller transaction with parent ownership validated');
 $finance=$db->table('finances')->where('clinic_id',1)->where('source_entity_type','inventory_usage')->where('source_entity_id',$r['usage_id'])->get()->getRowArray();$check($finance&&(int)$finance['patient_id']===(int)$t['patient_id'],'COGS retains correctly scoped treatment patient attribution');
 $db->transRollback();$check(stockState($db,$id)===$before,'Caller rollback preserves item/movement after treatment-linked consume');foreach($counts as$table=>$count)$check($db->table($table)->countAllResults()===$count,'Caller rollback preserves '.$table.' history count');
 foreach(['invalid','2147483647']as$bad){$r=(new App\Services\InventoryUsageService())->record(1,['usage_date'=>date('Y-m-d'),'treatment_id'=>$bad,'items'=>[['item_id'=>$id,'quantity'=>1]]],1);$check(!($r['success']??false)&&stockState($db,$id)===$before,'Malformed/missing treatment cannot mutate stock');}
 // Test real deletion predicate using new unused zero-stock records and usage rows only, rollback all probes.
 foreach(['numeric','string','nonarray','invalid-json','missing-id','valid-other']as$case){
  $db->transBegin();$db->table('inventory')->insert(['clinic_id'=>1,'item_name'=>$run.' '.$case,'category'=>'consumables','quantity'=>0,'min_quantity'=>0,'unit_price'=>'1.00','unit'=>'pieces','status'=>'active','created_by'=>1]);$candidate=(int)$db->insertID();
  $json=match($case){'numeric'=>json_encode([['item_id'=>$candidate]]),'string'=>json_encode([['item_id'=>(string)$candidate]]),'nonarray'=>'{"item_id":1}','invalid-json'=>'broken','missing-id'=>'[{"quantity_used":1}]',default=>json_encode([['item_id'=>$id]])};
  $db->table('inventory_usage')->insert(['clinic_id'=>1,'treatment_id'=>null,'usage_date'=>date('Y-m-d'),'items_used'=>$json,'total_cost'=>'0.00','notes'=>$run,'recorded_by'=>1]);
  $r=callStockController('delete',$candidate,[]);$exists=$db->table('inventory')->where('id',$candidate)->countAllResults();
  $check($case==='valid-other'?(!empty($r['success'])&&$exists===0):(empty($r['success'])&&$exists===1),'Actual delete JSON history predicate '.$case);
  $db->transRollback();
 }
 $check((int)$db->transDepth===0&&$db->transStatus(),'Supplement ends healthy without caller transaction leak');
 echo json_encode(['status'=>'PASS','run'=>$run,'checks'=>$checks,'retained_owned_inventory_ids'=>$ids,'limits'=>'Actual treatment-linked service and controller deletion predicate; no HTTP filter/lot policy certification'],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";
}catch(Throwable$e){if((int)$db->transDepth>0)$db->transRollback();echo json_encode(['status'=>'FAIL','run'=>$run,'error'=>$e->getMessage(),'checks'=>$checks,'retained_owned_inventory_ids'=>$ids],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";exit(1);}
