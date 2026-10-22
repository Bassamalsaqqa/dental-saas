<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(2);$bootstrap=getenv('M2C_TEST_BOOTSTRAP');if(!$bootstrap||!is_file($bootstrap))throw new RuntimeException('Synthetic bootstrap required');require $bootstrap;
$i=$db->query('SELECT DATABASE() db,@@port port,@@datadir datadir')->getRowArray();if(!str_starts_with($i['db'],'m2c_')||(int)$i['port']!==3307||!str_contains(str_replace('\\','/',$i['datadir']),'denta-m2c-20261021/disposable-mariadb/data'))throw new RuntimeException('Owned instance only');
$db->query('CREATE TEMPORARY TABLE appointments(id INT PRIMARY KEY,clinic_id INT,patient_id INT,appointment_date DATE,appointment_time TIME,status VARCHAR(20),deleted_at DATETIME NULL)');$db->query('CREATE TEMPORARY TABLE patients(id INT PRIMARY KEY,clinic_id INT,first_name VARCHAR(50),last_name VARCHAR(50),deleted_at DATETIME NULL)');$db->query('CREATE TEMPORARY TABLE settings(id INT PRIMARY KEY,clinic_id INT NULL,setting_key VARCHAR(100),setting_value TEXT)');
$db->query("INSERT INTO patients VALUES(1,1,'Synthetic','A',NULL),(2,2,'Foreign','B',NULL)");
foreach([[1,1,1,'2026-10-22','no_show'],[2,1,1,'2026-09-23','cancelled'],[3,1,1,'2026-09-22','no_show'],[4,1,1,'2026-10-23','cancelled'],[5,2,2,'2026-10-22','no_show'],[6,1,2,'2026-10-22','cancelled'],[7,1,1,'2026-10-22','completed']]as$r)$db->query('INSERT INTO appointments(id,clinic_id,patient_id,appointment_date,appointment_time,status) VALUES(?,?,?,?,?,?)',[$r[0],$r[1],$r[2],$r[3],'09:00:00',$r[4]]);
$s=new App\Services\AppointmentRecoveryQueueService($db);$r=$s->summary(1,1,new DateTimeImmutable('2026-10-22T12:00:00Z'));if($r['count']!==2||count($r['items'])!==1||(int)$r['items'][0]['id']!==1||$r['from']!=='2026-09-23'||$r['through']!=='2026-10-22')throw new RuntimeException('Window/order/tenant/join/count regression');
$db->query("INSERT INTO appointments VALUES(8,1,1,'2026-10-22','10:00:00','no_show','2026-10-22 12:00:00')");
$db->query("INSERT INTO patients VALUES(3,1,'Deleted','Patient','2026-10-22 12:00:00')");
$db->query("INSERT INTO appointments VALUES(9,1,3,'2026-10-22','10:00:00','no_show',NULL)");
if($s->summary(1,1000,new DateTimeImmutable('2026-10-22T12:00:00Z'))['count']!==2)throw new RuntimeException('Deleted patient or visit leaked');
$en=require $root.'/app/Language/en/AppointmentRecovery.php';$ar=require $root.'/app/Language/ar/AppointmentRecovery.php';if(array_keys($en)!==array_keys($ar)||str_contains(implode('',$ar),'??'))throw new RuntimeException('Locale mismatch');
echo json_encode(['status'=>'PASS','checks'=>8,'scope'=>'Temporary synthetic window boundaries, tenant join isolation, status, count/limit, ordering, localization']),PHP_EOL;
