<?php
declare(strict_types=1);
$bootstrap=getopt('', ['bootstrap:'])['bootstrap']??'';
if(PHP_SAPI!=='cli'||!is_file($bootstrap))exit(2);
require $bootstrap;
$i=$db->query('SELECT DATABASE() db,@@port port,@@datadir datadir')->getRowArray();
if($db->hostname!=='127.0.0.1'||$i['db']!=='m2c_finance_acceptance'||(int)$i['port']!==3307||!str_contains(str_replace('\\','/',$i['datadir']),'denta-m2c-20261021/disposable-mariadb/data'))throw new RuntimeException('Owned synthetic database required');
$prefix='usagejoin_'.bin2hex(random_bytes(4)).'_';
foreach(['inventory_usage'=>'id INT PRIMARY KEY,clinic_id INT,treatment_id INT NULL','treatments'=>'id INT PRIMARY KEY,clinic_id INT,patient_id INT,treatment_name VARCHAR(60)','patients'=>'id INT PRIMARY KEY,clinic_id INT,first_name VARCHAR(60)'] as$table=>$columns)$db->query('CREATE TEMPORARY TABLE `'.$prefix.$table.'`('.$columns.') ENGINE=InnoDB');
$db->table($prefix.'patients')->insertBatch([['id'=>1,'clinic_id'=>1,'first_name'=>'Own patient'],['id'=>2,'clinic_id'=>2,'first_name'=>'Foreign patient']]);
$db->table($prefix.'treatments')->insertBatch([['id'=>1,'clinic_id'=>1,'patient_id'=>1,'treatment_name'=>'Own treatment'],['id'=>2,'clinic_id'=>2,'patient_id'=>2,'treatment_name'=>'Foreign treatment'],['id'=>3,'clinic_id'=>1,'patient_id'=>2,'treatment_name'=>'Own legacy treatment']]);
$db->table($prefix.'inventory_usage')->insertBatch([['id'=>1,'clinic_id'=>1,'treatment_id'=>1],['id'=>2,'clinic_id'=>1,'treatment_id'=>2],['id'=>3,'clinic_id'=>1,'treatment_id'=>3],['id'=>4,'clinic_id'=>1,'treatment_id'=>null],['id'=>5,'clinic_id'=>2,'treatment_id'=>2]]);
$s=file_get_contents(__DIR__.'/../../app/Controllers/Inventory.php');$checks=0;
function ck(bool$ok,string$why):void{global$checks;if(!$ok)throw new RuntimeException($why);$checks++;}
foreach(['usageHistory','usageDetails','usagePrint','getUsageHistoryData']as$method){
 if(!preg_match('~public function '.$method.'\\([^)]*\\)(.*?)(?=public function|private function|protected function|\\z)~s',$s,$m))throw new RuntimeException('Method missing');
 preg_match_all("~->join\\('((?:treatments|patients))', '([^']+)', 'left'\\)~",$m[1],$joins,PREG_SET_ORDER);
 ck(count($joins)===2,$method.' has both isolated joins');
 $base=' FROM `'.$prefix.'inventory_usage` inventory_usage';$unsafe=$base;
 foreach($joins as$j){$base.=' LEFT JOIN `'.$prefix.$j[1].'` '.$j[1].' ON '.$j[2];$unsafe.=' LEFT JOIN `'.$prefix.$j[1].'` '.$j[1].' ON '.explode(' AND ',$j[2])[0];}
 $select='SELECT inventory_usage.id,treatments.treatment_name,patients.first_name';$where=' WHERE inventory_usage.clinic_id=1 ORDER BY inventory_usage.id';
 $old=$db->query($select.$unsafe.$where)->getResultArray();$rows=$db->query($select.$base.$where)->getResultArray();
 ck($old[1]['first_name']==='Foreign patient'&&$old[1]['treatment_name']==='Foreign treatment',$method.' fixture reproduces prior leak');
 ck(count($rows)===4,$method.' preserves every own history row including broken links');
 ck($rows[0]===$old[0],$method.' preserves legitimate patient/treatment display');
 ck($rows[1]['first_name']===null&&$rows[1]['treatment_name']===null,$method.' foreign treatment and patient redacted');
 ck($rows[2]['first_name']===null&&$rows[2]['treatment_name']==='Own legacy treatment',$method.' own treatment retained but foreign patient redacted');
 ck($rows[3]['first_name']===null&&$rows[3]['treatment_name']===null,$method.' unlinked usage retained');
}
echo json_encode(['status'=>'PASS','checks'=>$checks,'scope'=>'Actual MariaDB joins extracted from all four controller entrypoints; connection-local synthetic temporary tables only, no historical records rewritten.']),PHP_EOL;
