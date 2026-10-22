<?php
declare(strict_types=1);
$bootstrap=getopt('',['bootstrap:'])['bootstrap']??'';
if(PHP_SAPI!=='cli'||!is_file($bootstrap))exit(2);require $bootstrap;
$identity=$db->query('SELECT DATABASE() db,@@port port,@@datadir datadir')->getRowArray();
if($db->hostname!=='127.0.0.1'||$identity['db']!=='m2c_finance_acceptance'||(int)$identity['port']!==3307||!str_contains(str_replace('\\','/',$identity['datadir']),'denta-m2c-20261021/disposable-mariadb/data'))throw new RuntimeException('Owned disposable target required');
$prefix='email_'.bin2hex(random_bytes(5)).'_';$cfg=config('Database')->default;$cfg['DBPrefix']=$prefix;
$test=Config\Database::connect($cfg,false);$test->initialize();$table=$prefix.'patients';
$test->query('CREATE TABLE `'.$table.'`(id INT PRIMARY KEY AUTO_INCREMENT,clinic_id INT NOT NULL,email VARCHAR(100) NULL,first_name VARCHAR(50), UNIQUE KEY email(email)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
$test->query('INSERT INTO `'.$table.'`(clinic_id,email,first_name) VALUES(1,?,?),(2,NULL,?)',['shared@example.invalid','Original','Other']);

$before=$test->table('patients')->orderBy('id')->get()->getResultArray();
require $root.'/app/Database/Migrations/2026-10-22-150000_ScopePatientEmailUniqueness.php';
$migration=new App\Database\Migrations\ScopePatientEmailUniqueness(Config\Database::forge($test));
foreach(['db'=>$test,'forge'=>Config\Database::forge($test)]as$name=>$value)(new ReflectionProperty(CodeIgniter\Database\Migration::class,$name))->setValue($migration,$value);
$n=0;function ck(bool$v,string$why):void{global$n;if(!$v)throw new RuntimeException($why);$n++;}
$migration->up();ck($before===$test->table('patients')->orderBy('id')->get()->getResultArray(),'Original rows preserved');
$migration->up();ck($before===$test->table('patients')->orderBy('id')->get()->getResultArray(),'Repeat up idempotent and preserves rows');
$model=new App\Models\PatientModel($test);
ck($model->emailExistsInClinic(' shared@example.invalid ',1),'Trim and same-clinic lookup');
ck(!$model->emailExistsInClinic('shared@example.invalid',2),'Cross-clinic identity not disclosed');
ck(!$model->emailExistsInClinic('shared@example.invalid',1,1),'Identical update excludes own ID');
ck(!$model->emailExistsInClinic(null,1)&&!$model->emailExistsInClinic(' ',1),'Empty optional email');
ck($test->table('patients')->insert(['clinic_id'=>2,'email'=>'shared@example.invalid','first_name'=>'Separate']),'Cross-clinic equal email accepted');
ck($test->table('patients')->insert(['clinic_id'=>1,'email'=>null])&&$test->table('patients')->insert(['clinic_id'=>1,'email'=>null]),'Optional null emails remain supported');
ck($test->table('patients')->insert(['clinic_id'=>1,'email'=>'SHARED@example.invalid'])===false,'Database collation rejects same-clinic case-equivalent race');
// Two independent real connections contend on the same unique clinic/email key.
$other=Config\Database::connect($cfg,false);$other->initialize();$other->query('SET SESSION innodb_lock_wait_timeout=1');
$test->transBegin();$test->table('patients')->insert(['clinic_id'=>1,'email'=>'race@example.invalid']);
try {ck($other->table('patients')->insert(['clinic_id'=>1,'email'=>'race@example.invalid'])===false,'Concurrent same-clinic insert cannot bypass unique key');
ck($other->table('patients')->insert(['clinic_id'=>2,'email'=>'race@example.invalid']),'Concurrent other-clinic same email independent');}
finally{$test->transRollback();}
ck($other->table('patients')->insert(['clinic_id'=>1,'email'=>'race@example.invalid']),'Retry after rolled-back competing insert succeeds');
$rows=$test->table('patients')->orderBy('id')->get()->getResultArray();
try{$migration->down();throw new RuntimeException('Unsafe down accepted');}catch(RuntimeException$e){ck(str_contains($e->getMessage(),'cannot be automatically reversed'),'Down refuses destructive global constraint restoration');}
ck($rows===$test->table('patients')->orderBy('id')->get()->getResultArray(),'Down preserves all rows');
ck(!str_contains(file_get_contents($root.'/app/Controllers/Patient.php'),'is_unique[patients.email]'),'Browser validation no longer probes all clinics');
// Failed preflight must preserve history and existing constraints before DDL.
$badPrefix='email_bad_'.bin2hex(random_bytes(5)).'_';$badCfg=$cfg;$badCfg['DBPrefix']=$badPrefix;$bad=Config\Database::connect($badCfg,false);$bad->initialize();
$bad->query('CREATE TABLE `'.$badPrefix.'patients`(id INT PRIMARY KEY,clinic_id INT,email VARCHAR(100)) ENGINE=InnoDB');
$bad->query("INSERT INTO `".$badPrefix."patients` VALUES(1,1,'duplicate@example.invalid'),(2,1,'duplicate@example.invalid')");
$badMigration=new App\Database\Migrations\ScopePatientEmailUniqueness(Config\Database::forge($bad));
$badBefore=$bad->table('patients')->orderBy('id')->get()->getResultArray();
try{$badMigration->up();throw new RuntimeException('Duplicate history accepted');}catch(RuntimeException$e){ck(str_contains($e->getMessage(),'requires reconciliation'),'Duplicate history fails preflight');}
ck($badBefore===$bad->table('patients')->orderBy('id')->get()->getResultArray(),'Failed preflight preserves all existing rows');
ck(count($bad->query('SHOW INDEX FROM `'.$badPrefix.'patients`')->getResultArray())===1,'Failed preflight creates no constraint');
echo json_encode(['status'=>'PASS','checks'=>$n,'fixture_table'=>$table,'fixtures_retained'=>true,'scope'=>'Actual migration/SQL tenant uniqueness, optional email, collation, two-connection contention and rollback preservation']),PHP_EOL;
