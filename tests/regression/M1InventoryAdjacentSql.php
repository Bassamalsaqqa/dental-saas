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
$run='M1ADJ_'.bin2hex(random_bytes(6));$checks=[];$ids=[];
$check=static function(bool $ok,string $label)use(&$checks):void{if(!$ok)throw new RuntimeException($label);$checks[]=$label;};
try{
 foreach([1,2]as$clinic){$db->table('inventory')->insert(['clinic_id'=>$clinic,'item_name'=>$run.' clinic'.$clinic,'category'=>'consumables','quantity'=>5,'min_quantity'=>0,'unit_price'=>'2.75','unit'=>'pieces','status'=>'active','created_by'=>1,'notes'=>$run]);$ids[]=(int)$db->insertID();}
 [$id,$foreign]=$ids;
 $r=callStockController('adjust',$id,['adjustment'=>2,'type'=>'subtract','reason'=>$run]);$state=stockState($db,$id);$m=$state['inventory_stock_movements'];$check(($r['success']??false)&&(int)$state['item']['quantity']===3&&count($m)===1&&(int)$m[0]['quantity_before']===5&&(int)$m[0]['quantity_after']===3,'Actual adjust controller serializes delta and reconciles exact movement');
 foreach([['adjustment'=>'1.5','type'=>'add'],['adjustment'=>1,'type'=>'unknown'],['adjustment'=>4,'type'=>'subtract']]as$bad){$before=stockState($db,$id);$r=callStockController('adjust',$id,$bad);$check(!($r['success']??false)&&stockState($db,$id)===$before,'Malformed or unavailable adjustment fails without stock/movement mutation');}
 $r=callStockController('receiveStock',$id,['quantity'=>2,'unit_cost'=>'3.25','reason'=>$run,'lot_number'=>$run,'batch_expiry_date'=>date('Y-m-d',strtotime('+1 year')),'batch_currency'=>'ILS']);$state=stockState($db,$id);$b=$state['inventory_batches'];$m=$state['inventory_stock_movements'];$check(($r['success']??false)&&(int)$state['item']['quantity']===5&&(float)$state['item']['unit_price']===3.25&&count($b)===1&&count($m)===2&&(int)$m[1]['batch_id']===(int)$b[0]['id']&&(int)$b[0]['source_movement_id']===(int)$m[1]['id'],'Actual receipt commits current stock/cost, batch and reciprocal movement link together');
 $before=stockState($db,$id);$r=callStockController('receiveStock',$id,['quantity'=>1,'unit_cost'=>'9.99','lot_number'=>str_repeat('x',101)]);$check(!($r['success']??false)&&stockState($db,$id)===$before,'Actual batch validation failure cannot leave stock/cost or movement partially committed');
 $before=stockState($db,$foreign);$r=callStockController('adjust',$foreign,['adjustment'=>1,'type'=>'add']);$check(!($r['success']??false)&&stockState($db,$foreign)===$before,'Controller stock adjustment cannot mutate foreign-clinic inventory');
 $before=stockState($db,$foreign);$r=callStockController('receiveStock',$foreign,['quantity'=>1]);$check(!($r['success']??false)&&stockState($db,$foreign)===$before,'Controller receipt cannot mutate foreign-clinic inventory');
 $before=stockState($db,$id);$check((new App\Models\InventoryModel())->adjustQuantity($id,1,'add')!==false,'Legacy public model adjustment delegates protected stock mutation');$after=stockState($db,$id);$check((int)$after['item']['quantity']===(int)$before['item']['quantity']+1&&count($after['inventory_stock_movements'])===count($before['inventory_stock_movements'])+1,'Legacy model adjustment includes movement history rather than an unlocked bypass');
 // Original rendered quantity5, now6 after another writer: an old form may not restore5.
 $data=$after['item'];$data['quantity']=5;$data['expected_quantity']=5;$data['supplier_id']='';$data['description']=$data['description']??'';$data['location']=$data['location']??'';
 $before=stockState($db,$id);$r=callStockController('update',$id,$data,true);$check(stockState($db,$id)===$before&&!empty($r['error']),'Stale metadata quantity token fails closed without rewriting a concurrent stock change: '.json_encode($r));
 $data=$before['item'];$data['quantity']=$before['item']['quantity'];unset($data['expected_quantity']);$data['description']=$run.' legitimate metadata';$data['supplier_id']='';$r=callStockController('update',$id,$data,true);$state=stockState($db,$id);$check(!empty($r['success'])&&empty($r['error'])&&(int)$state['item']['quantity']===(int)$before['item']['quantity']&&$state['item']['description']===$data['description'],'Unchanged quantity without token preserves legitimate metadata edits');
 $data=$state['item'];$data['expected_quantity']=$data['quantity'];$data['quantity']=(int)$data['quantity']+1;$data['supplier_id']='';$r=callStockController('update',$id,$data,true);$after=stockState($db,$id);$check(!empty($r['success'])&&(int)$after['item']['quantity']===(int)$state['item']['quantity']+1&&count($after['inventory_stock_movements'])===count($state['inventory_stock_movements'])+1,'Current edit token permits established quantity correction and records movement');
 $before=$after;$data=$after['item'];unset($data['expected_quantity']);$data['quantity']=(int)$data['quantity']+1;$data['supplier_id']='';$r=callStockController('update',$id,$data,true);$check(!empty($r['error'])&&stockState($db,$id)===$before,'Quantity correction without token cannot overwrite current stock');
 $before=stockState($db,$id);$r=callStockController('delete',$id,[]);$check(!($r['success']??false)&&stockState($db,$id)===$before,'Used stock item cannot lose movement/batch history through delete');
 $check((int)$db->transDepth===0&&$db->transStatus(),'Controller/model connection finishes healthy with no transaction leak');
 echo json_encode(['status'=>'PASS','run'=>$run,'checks'=>$checks,'retained_owned_inventory_ids'=>$ids,'limits'=>'Actual controller method/body/model SQL checks, not HTTP filter or browser workflow certification. No lot allocation policy assumed.'],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";
}catch(Throwable$e){if((int)$db->transDepth>0)$db->transRollback();echo json_encode(['status'=>'FAIL','run'=>$run,'error'=>$e->getMessage(),'checks'=>$checks,'retained_owned_inventory_ids'=>$ids],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";exit(1);}finally{App\Libraries\ApiRuntimeContext::clear();}
