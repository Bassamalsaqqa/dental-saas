<?php
declare(strict_types=1);
$bootstrap=getopt('', ['bootstrap:'])['bootstrap']??'';if(PHP_SAPI!=='cli'||!is_file($bootstrap))exit(2);require $bootstrap;
$i=$db->query('SELECT DATABASE() db,@@port port,@@datadir datadir')->getRowArray();if($db->hostname!=='127.0.0.1'||$i['db']!=='m3m4_host_schema_20261022_fab33c62'||(int)$i['port']!==3307||!str_contains(str_replace('\\','/',$i['datadir']),'denta-m2c-20261021/disposable-mariadb/data'))throw new RuntimeException('Owned representative synthetic schema required');
App\Libraries\ApiRuntimeContext::clear();session()->set(['user_id'=>1,'active_clinic_id'=>1,'global_mode'=>false]);config('InvoiceCorrections')->enabled=true;
$n=0;$check=static function(bool$ok,string$why)use(&$n){if(!$ok)throw new RuntimeException($why);++$n;};$before=$db->table('finances')->orderBy('id')->get()->getResultArray();if(!$db->transBegin())throw new RuntimeException('Fixture transaction failed');
try{
 $examples=[['id'=>800001,'clinic_id'=>1,'patient_id'=>3,'transaction_type'=>'expense','transaction_id'=>'SYN-REUSE-COGS','amount'=>5,'total_amount'=>5,'expense_category'=>'inventory_cogs','transaction_date'=>'2026-10-20','created_at'=>'2026-10-22 12:00:00'],['id'=>800002,'clinic_id'=>1,'patient_id'=>3,'transaction_type'=>'invoice','transaction_id'=>'SYN-REUSE-OLDER','amount'=>20,'total_amount'=>20,'balance_due'=>20,'transaction_date'=>'2026-10-01','created_at'=>'2026-10-22 13:00:00'],['id'=>800003,'clinic_id'=>2,'patient_id'=>2,'transaction_type'=>'invoice','transaction_id'=>'SYN-REUSE-FOREIGN','amount'=>99,'total_amount'=>99,'balance_due'=>99,'transaction_date'=>'2026-10-22','created_at'=>'2026-10-22 14:00:00']];
 foreach($examples as$row)if(!$db->table('finances')->insert($row))throw new RuntimeException('Synthetic fixture insert failed');
 $warmModel=new App\Models\FinanceModel($db);$warmModel->getFinanceByPatient(3);$warmTrail=(new App\Services\MoneyTrailService($db))->forPatient(1,3);App\Services\MoneyTrailService::patientFinanceRows($warmTrail);
 $queries=0;CodeIgniter\Events\Events::on('DBQuery',static function()use(&$queries){++$queries;});
 $model=new App\Models\FinanceModel($db);$oldStart=$queries;$old=$model->getFinanceByPatient(3);$trail=(new App\Services\MoneyTrailService($db))->forPatient(1,3);$oldQueries=$queries-$oldStart;
 $newStart=$queries;$reusedTrail=(new App\Services\MoneyTrailService($db))->forPatient(1,3);$new=App\Services\MoneyTrailService::patientFinanceRows($reusedTrail);$newQueries=$queries-$newStart;
 $check(count($old)===2&&count($new)===2,'COGS exclusion and complete patient invoices retained');
 $check(array_column($old,'id')===array_column($new,'id'),'Existing explicit transaction/creation date order retained');
 foreach($old as$index=>$row){foreach($row as$field=>$value){$check(array_key_exists($field,$new[$index])&&$new[$index][$field]===$value,'Actual projected field parity '.$field);}}
 $check($oldQueries>$newQueries,'Duplicate finance projection query path removed');
 $check(!in_array(800003,array_map('intval',array_column($new,'id')),true),'Foreign tenant financial record excluded');
 $check(count($reusedTrail['finance_rows'])===3,'Canonical trail retains its separate COGS context');
 $check($new[0]['balance_due']==='50.00'||(float)$new[0]['balance_due']===50.0,'Correction projection preserved while immutable cache remains unchanged');
 $measure=['old_queries'=>$oldQueries,'new_queries'=>$newQueries,'removed_queries'=>$oldQueries-$newQueries];
}finally{$db->transRollback();config('InvoiceCorrections')->enabled=false;}
$check($db->table('finances')->orderBy('id')->get()->getResultArray()===$before,'All original finance rows preserved after fixture rollback');
echo json_encode(['status'=>'PASS','checks'=>$n,'queries'=>$measure,'scope'=>'Actual FinanceModel versus canonical MoneyTrail projection under real captured-schema FKs; synthetic fixture rolled back; no production/history changes']),PHP_EOL;
