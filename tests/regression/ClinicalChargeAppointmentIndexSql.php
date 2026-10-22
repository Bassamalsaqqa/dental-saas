<?php
declare(strict_types=1);
$bootstrap=getopt('', ['bootstrap:'])['bootstrap']??'';
if(PHP_SAPI!=='cli'||!is_file($bootstrap))exit(2);require $bootstrap;
$identity=$db->query('SELECT DATABASE() db,@@port port,@@datadir datadir')->getRowArray();
if($db->hostname!=='127.0.0.1'||$identity['db']!=='m2c_finance_acceptance'||(int)$identity['port']!==3307||!str_contains(str_replace('\\','/',$identity['datadir']),'denta-m2c-20261021/disposable-mariadb/data'))throw new RuntimeException('Owned disposable database required');
require $root.'/app/Database/Migrations/2026-10-22-190000_AddClinicalChargeAppointmentIndex.php';
$prefix='chargeidx_'.bin2hex(random_bytes(4)).'_';$checks=0;
function ixCheck(bool$ok,string$label):void{global$checks;if(!$ok)throw new RuntimeException($label);++$checks;}
function ixFixture(string$tag,string$extra='',string$appointment='INT NULL',string$engine='InnoDB'):array{
 global$prefix;$cfg=config('Database')->default;$cfg['DBPrefix']=$prefix.$tag.'_';$test=Config\Database::connect($cfg,false);$test->initialize();$table=$test->escapeIdentifiers($test->prefixTable('clinical_charge_lines'));
 $definition='id INT PRIMARY KEY,clinic_id INT UNSIGNED NOT NULL'.($appointment===''?'':',appointment_id '.$appointment).',note VARCHAR(30)'.($extra===''?'':','.$extra);
 if(!$test->query('CREATE TABLE '.$table.'('.$definition.') ENGINE='.$engine))throw new RuntimeException('Fixture creation failed');
 $test->table('clinical_charge_lines')->insert(['id'=>1,'clinic_id'=>1,'note'=>'retained original']);
 return [$test,new App\Database\Migrations\AddClinicalChargeAppointmentIndex(Config\Database::forge($test)),$table];
}
[$test,$migration,$table]=ixFixture('good');$rows=$test->table('clinical_charge_lines')->get()->getResultArray();$migration->up();$indexes=$test->query('SHOW INDEX FROM '.$table)->getResultArray();$candidate=array_values(array_filter($indexes,static fn($r)=>$r['Key_name']==='idx_ccl_clinic_appointment'));
ixCheck(array_column($candidate,'Column_name')===['clinic_id','appointment_id'],'Exact measured composite index created');ixCheck((int)$candidate[0]['Non_unique']===1,'No uniqueness/accounting constraint introduced');ixCheck($test->table('clinical_charge_lines')->get()->getResultArray()===$rows,'Original rows preserved');$migration->up();ixCheck($test->query('SHOW INDEX FROM '.$table)->getResultArray()===$indexes,'Exact reentry no index churn');$migration->down();ixCheck($test->table('clinical_charge_lines')->get()->getResultArray()===$rows,'Index-only down preserves populated rows');ixCheck(count($test->query('SHOW INDEX FROM '.$table)->getResultArray())===1,'Only owned index removed');
[$test,$migration,$table]=ixFixture('cover','INDEX existing_cover (clinic_id,appointment_id,id)');$indexes=$test->query('SHOW INDEX FROM '.$table)->getResultArray();$migration->up();ixCheck($test->query('SHOW INDEX FROM '.$table)->getResultArray()===$indexes,'Existing covering index reused without duplicates');$migration->down();ixCheck($test->query('SHOW INDEX FROM '.$table)->getResultArray()===$indexes,'Rollback never removes alternative index');
foreach([['collision','INDEX idx_ccl_clinic_appointment (appointment_id,clinic_id)','INT NULL','InnoDB'],['unique','UNIQUE INDEX idx_ccl_clinic_appointment (clinic_id,appointment_id)','INT NULL','InnoDB'],['missing','','','InnoDB'],['wrongtype','','VARCHAR(10)','InnoDB'],['engine','','INT NULL','MyISAM']]as[$tag,$extra,$type,$engine]){
 [$test,$migration,$table]=ixFixture($tag,$extra,$type,$engine);$ddl=$test->query('SHOW CREATE TABLE '.$table)->getRowArray();$rows=$test->table('clinical_charge_lines')->get()->getResultArray();
 try{$migration->up();throw new LogicException('Unsafe migration accepted '.$tag);}catch(RuntimeException$e){ixCheck(true,'Preflight rejects '.$tag);}
 ixCheck($test->query('SHOW CREATE TABLE '.$table)->getRowArray()===$ddl,'Preflight leaves schema unchanged '.$tag);ixCheck($test->table('clinical_charge_lines')->get()->getResultArray()===$rows,'Preflight preserves rows '.$tag);
}
echo json_encode(['status'=>'PASS','checks'=>$checks,'prefix'=>$prefix,'scope'=>'Actual targeted migration on new owned prefixed tables only; online index DDL, preservation, reentry and fail-closed collisions. No scheduling activation or existing-table alteration.']),PHP_EOL;
