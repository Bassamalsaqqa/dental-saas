<?php
declare(strict_types=1);
$bootstrap=getopt('',['bootstrap:'])['bootstrap']??'';if(PHP_SAPI!=='cli'||!is_file($bootstrap))exit(2);require$bootstrap;
$i=$db->query('SELECT DATABASE() db,@@port port,@@datadir datadir')->getRowArray();if($db->hostname!=='127.0.0.1'||$i['db']!=='m2c_finance_acceptance'||(int)$i['port']!==3307||!str_contains(str_replace('\\','/',$i['datadir']),'denta-m2c-20261021/disposable-mariadb/data'))throw new RuntimeException('Owned synthetic target required');
$db->query('CREATE TEMPORARY TABLE patients(id INT PRIMARY KEY,clinic_id INT,deleted_at DATETIME NULL)');
$db->query("INSERT INTO patients VALUES(1,1,NULL),(2,2,NULL),(3,1,'2026-10-20 12:00:00')");
foreach(['appointments'=>'appointment_date DATE,appointment_time TIME,appointment_type VARCHAR(30)','examinations'=>'examination_date DATE,examination_type VARCHAR(30)','treatments'=>'start_date DATE,treatment_name VARCHAR(30)']as$table=>$columns){
$db->query('CREATE TEMPORARY TABLE '.$table.'(id INT PRIMARY KEY,clinic_id INT,patient_id INT,'.$columns.',status VARCHAR(30),deleted_at DATETIME NULL,KEY history(clinic_id,patient_id,id))');
foreach(range(1,60)as$id)$db->table($table)->insert(['id'=>$id,'clinic_id'=>1,'patient_id'=>1,'status'=>'completed']);
$db->table($table)->insert(['id'=>61,'clinic_id'=>2,'patient_id'=>1,'status'=>'completed']);
$db->table($table)->insert(['id'=>62,'clinic_id'=>1,'patient_id'=>2,'status'=>'completed']);
$db->table($table)->insert(['id'=>63,'clinic_id'=>1,'patient_id'=>1,'status'=>'completed','deleted_at'=>'2026-10-20 12:00:00']);
}
$db->resetDataCache();$s=new App\Services\PatientHistoryReadService($db);$n=0;
function ck(bool$v,string$m):void{global$n;if(!$v)throw new RuntimeException($m);$n++;}
foreach(['appointments','examinations','treatments']as$section){
$first=$s->page(1,1,$section,25);ck(array_column($first['items'],'id')===range(60,36),'First bounded page and tenant/deleted exclusion');ck($first['pagination']['has_more']&&$first['pagination']['next_before_id']===36,'Explicit continuation cursor');
$db->table($section)->insert(['id'=>64,'clinic_id'=>1,'patient_id'=>1,'status'=>'completed']);
$second=$s->page(1,1,$section,25,36);ck(array_column($second['items'],'id')===range(35,11),'Concurrent newer rows do not duplicate or shift cursor');
$last=$s->page(1,1,$section,25,11);ck(array_column($last['items'],'id')===range(10,1)&&!$last['pagination']['has_more']&&$last['pagination']['next_before_id']===null,'Entire history traversable without silent cap');
ck($first===$s->page(1,1,$section,25,61),'Stable repeat of same cursor boundary');
try{$s->page(1,2,$section);throw new RuntimeException('Cross-clinic patient accepted');}catch(DomainException$e){ck(true,'Cross-clinic parent concealed');}
try{$s->page(1,3,$section);throw new RuntimeException('Deleted patient accepted');}catch(DomainException$e){ck(true,'Deleted parent concealed');}
}
foreach([['finance',25,null],['appointments',101,null],['appointments',0,null],['appointments',25,-1]]as[$section,$limit,$cursor]){try{$s->page(1,1,$section,$limit,$cursor);throw new RuntimeException('Bad pagination accepted');}catch(InvalidArgumentException$e){ck(true,'Invalid section/limits rejected');}}
echo json_encode(['status'=>'PASS','checks'=>$n,'scope'=>'Real SQL bounded module history, stable cursor under insert, full traversal, tenant/soft-delete isolation; not routed authorization'],JSON_PRETTY_PRINT),PHP_EOL;
